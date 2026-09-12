<?php

namespace App\Dms;

use App\Jobs\Tenant\ActivityLog;
use App\Models\Tenant\Area;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\Machine;
use Filament\Notifications\Notification;
use Jenssegers\Agent\Agent;

class MachineDms extends DmsRequest
{
    public function syncDataWithDms()
    {
        (new AreaDms)->syncDataWithDms();
        $response = $this->sendRequest(uri: static::$terminal_uri);
        $this->saveFetchedData($response);
        $this->addAllMachineToDms();
    }

    public function saveFetchedData($response)
    {
        if ($response['error']) {
            Notification::make()
                ->title('Error')
                ->danger()
                ->body($response['message'])
                ->persistent()
                ->send();

            return;
        }
        $this->syncAllMachine($response['message']);
        if (array_key_exists('next', $response['message']) && $response['message']['next'] != null) {
            $new_response = $this->sendRequest(url: $response['message']['next']);
            $this->saveFetchedData($new_response);
        } else {
            Notification::make()
                ->title('Success')
                ->success()
                ->body('Syncing Machines Completed')
                ->send();
        }
    }

    protected function syncAllMachine($response): void
    {
        if (array_key_exists('data', $response)) {
            foreach ($response['data'] as $machine) {
                $area = null;
                $machine_obj = Machine::withoutGlobalScopes()->where('machine_serial_number', $machine['sn'])->first();

                if (array_key_exists('area', $machine) && array_key_exists('area_name', $machine['area'])) {
                    $area = Area::withoutGlobalScopes()->where(['dms_area_id' => $machine['area']['id']])
                        ->orWhere('code', $machine['area']['area_code'])->first();
                    if ($area == null) {
                        $area = Area::create(
                            [
                                'code' => $machine['area']['area_code'],
                                'name' => $machine['area']['area_name'],
                                'location_id' => Location::first()?->id,
                                'company_id' => Company::first()?->id,
                                'dms_area_id' => $machine['area']['id'],
                            ]
                        );

                        $area_old_record = [];
                        $agent = new Agent;
                        $browser = $agent->browser().' '.$agent->version($agent->browser());
                        $os = $agent->platform();

                        $area_new_record = [
                            'ip' => request()->ip(),
                            'browser' => $os.' > '.$browser,
                            'name' => $area->name,
                            'code' => $area->code,
                            'location' => $area->location?->name,
                            'company' => $area->company?->name,
                            'dms_area_id' => $area->dms_area_id,
                        ];
                        ActivityLog::dispatch(auth()->user(), $area, $area_old_record, $area_new_record, 'area_sync_created')->onQueue('processing');
                    }
                }

                if ($area == null) {
                    $area = Area::firstOrCreate(
                        ['id' => 1],
                        [
                            'code' => 'N/A',
                            'name' => 'No Access',
                            'location_id' => Location::first()?->id,
                        ]
                    );
                }

                if ($machine_obj == null) {
                    $machine_obj = new Machine;
                    $machine_obj->access_direction = 'both';
                    $machine_obj->name = $machine['alias'] ?? $machine['sn'];
                    $machine_obj->location_id = $area->location_id;
                    $machine_obj->dms_area_id = $area->dms_area_id;
                    $machine_obj->machine_serial_number = $machine['sn'];
                    $machine_obj->area_id = $area->id;
                    $machine_obj->created_by = auth()->user()?->id ?? 1;
                }
                $machine_obj->dms_device_id = $machine['id'];
                $machine_obj->machine_ip = $machine['ip_address'];
                $machine_obj->save();

                $machine_old_record = [];
                $agent = new Agent;
                $browser = $agent->browser().' '.$agent->version($agent->browser());
                $os = $agent->platform();

                $machine_new_record = [
                    'ip' => request()->ip(),
                    'browser' => $os.' > '.$browser,
                    'name' => $machine_obj->name,
                    'machine_ip' => $machine_obj->machine_ip,
                    'machine_serial_number' => $machine_obj->machine_serial_number,
                    'dms_device_id' => $machine_obj->dms_device_id,
                    'dms_area_id' => $machine_obj->dms_area_id,
                    'access_direction' => $machine_obj->access_direction,
                    'location' => $machine_obj->location?->name,
                    'company' => $machine_obj->company?->name,
                    'area' => $machine_obj->area?->name,
                ];
                ActivityLog::dispatch(auth()->user(), $area, $machine_old_record, $machine_new_record, 'machine_sync_created')->onQueue('processing');
            }
        }
    }

    public function checkMachineRegisterdOnDms($machine)
    {
        $response = $this->sendRequest(uri: static::$terminal_uri, type: static::$get, params: ['sn' => $machine->machine_serial_number]);
        if ($response['error']) {
            Notification::make()
                ->title('Error')
                ->danger()
                ->body($response['message'])
                ->send();

            return false;
        }
        if (array_key_exists('data', $response['message']) && count($response['message']['data']) > 0) {
            $machine->dms_device_id = $response['message']['data'][0]['id'];
            $machine->machine_ip = $response['message']['data'][0]['ip_address'];
            $machine->save();

            return true;
        }

        return false;
    }

    public function addAllMachineToDms()
    {
        $machines = Machine::all();
        foreach ($machines as $machine) {
            if ($machine->dms_device_id == null || $machine->dms_device_id == '') {
                $this->addMachineToDms($machine);
            } else {
                $this->updateMachineToDms($machine);
            }
        }
    }

    public function addMachineToDms($machine)
    {
        if ($this->checkMachineRegisterdOnDms($machine)) {
            $this->updateMachineToDms($machine);

            return;
        }

        $data = [
            'sn' => $machine->machine_serial_number,
            'alias' => $machine->name,
            'ip_address' => $machine->machine_ip,
            'device_direction' => match ($machine->access_direction) {
                'in' => 1,
                'out' => 2,
                default => 0,
            },
            'heartbeat' => 10,
            'transfer_interval' => 1,
            'area' => $machine->area?->dms_area_id ?? 1,
        ];
        $response = $this->sendRequest(uri: static::$terminal_uri, type: static::$post, data: $data);

        if ($response['error']) {
            Notification::make()
                ->title('Error')
                ->danger()
                ->body($response['message'])
                ->persistent()
                ->send();

            return;
        }
        if (array_key_exists('id', $response['message'])) {
            $machine->dms_device_id = $response['message']['id'];
            $machine->save();
        }
    }

    public function updateMachineToDms($machine)
    {
        if ($machine->dms_device_id == null || $machine->dms_device_id == '') {
            $this->addMachineToDms($machine);

            return;
        }
        $data = [
            'sn' => $machine->machine_serial_number,
            'alias' => $machine->name,
            // "ip_address" => $machine->machine_ip,
            'device_direction' => match ($machine->access_direction) {
                'in' => 1,
                'out' => 2,
                default => 0,
            },
            'heartbeat' => 10,
            'transfer_interval' => 1,
            'area' => $machine->area?->dms_area_id ?? 1,
        ];
        $response = $this->sendRequest(uri: static::$terminal_uri, args: "{$machine->dms_device_id}/", type: static::$put, data: $data);

        if ($response['error']) {
            Notification::make()
                ->title('Error')
                ->danger()
                ->body($response['message'])
                ->persistent()
                ->send();

            return;
        }
        if (array_key_exists('id', $response['message'])) {
            $machine->dms_device_id = $response['message']['id'];
            $machine->save();
        }
    }
}
