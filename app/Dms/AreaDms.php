<?php

namespace App\Dms;

use App\Support\ActivityLogger;
use App\Models\Tenant\Area;
use App\Models\Tenant\Location;
use Filament\Notifications\Notification;
use Jenssegers\Agent\Agent;

class AreaDms extends DmsRequest
{
    public function syncDataWithDms()
    {
        $response = $this->sendRequest(uri: static::$area_uri);
        $this->saveFetchedData($response);
        $this->addNewAreasToDms();
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
        $this->syncAllAreas($response['message']);
        if (array_key_exists('next', $response['message']) && $response['message']['next'] != null) {
            $new_response = $this->sendRequest(url: $response['message']['next']);
            $this->saveFetchedData($new_response);
        } else {
            Notification::make()
                ->title('Success')
                ->success()
                ->body('Syncing Areas Completed')
                ->send();
        }
    }

    protected function syncAllAreas($response): void
    {
        if (array_key_exists('data', $response)) {
            foreach ($response['data'] as $area) {
                $area_obj = Area::withoutGlobalScopes()->where('dms_area_id', $area['id'])
                    ->orWhere('code', $area['area_code'])->first();
                if ($area_obj != null) {
                    if ($area_obj->dms_area_id == null) {
                        $old_record = [
                            'ip' => '-',
                            'browser' => '-',
                            'dms_area_id' => $area_obj->dms_area_id ? $area_obj->dms_area_id : '-',
                        ];

                        $area_obj->dms_area_id = $area['id'];
                        $area_obj->save();

                        $agent = new Agent;
                        $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
                        $os = $agent->platform();

                        $new_record = [
                            'ip' => request()->ip(),
                            'browser' => $os . ' > ' . $browser,
                            'dms_area_id' => $area_obj->dms_area_id ? $area_obj->dms_area_id : '-',
                        ];
                        ActivityLogger::log(auth()->user(), $area_obj, $old_record, $new_record, 'area_sync_updated');
                    } else {
                        $this->updateAreaToDms($area_obj);
                    }
                } else {
                    $area_obj = Area::create([
                        'code' => $area['area_code'],
                        'name' => $area['area_name'],
                        'location_id' => Location::first()->id,
                        'dms_area_id' => $area['id'],
                    ]);

                    $old_record = [];
                    $agent = new Agent;
                    $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
                    $os = $agent->platform();

                    $new_record = [
                        'ip' => request()->ip(),
                        'browser' => $os . ' > ' . $browser,
                        'name' => $area_obj->name,
                        'code' => $area_obj->code,
                        'location' => $area_obj->location_id
                            ? implode(', ', Location::whereIn('id', (array) $area_obj->location_id)->pluck('name')->toArray())
                            : null,
                        'dms_area_id' => $area_obj->dms_area_id,
                    ];
                    ActivityLogger::log(auth()->user(), $area_obj, $old_record, $new_record, 'area_sync_created');
                }
            }
        }
    }

    protected function addNewAreasToDms()
    {
        $areas = Area::where('dms_area_id', null)->get();
        foreach ($areas as $area) {
            $this->addAreaToDms($area);
        }
    }

    public function addAreaToDms($area_obj)
    {
        if ($this->checkAreaRegisterdOnDms($area_obj)) {
            $this->updateAreaToDms($area_obj);

            return;
        }
        $post_data = [
            'area_code' => $area_obj->code,
            'area_name' => $area_obj->name,
        ];
        $response = $this->sendRequest(uri: static::$area_uri, args: "{$area_obj->dms_area_id}/", type: static::$post, data: $post_data);

        if ($response['error']) {
            Notification::make()
                ->title('Error')
                ->body('Error: ' . $response['message'])
                ->persistent()
                ->send();

            return;
        }

        $old_record = [
            'dms_area_id' => $area_obj->dms_area_id,
        ];

        $area_obj->dms_area_id = $response['message']['id'];
        $area_obj->save();

        $agent = new Agent;
        $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
        $os = $agent->platform();

        $new_record = [
            'ip' => request()->ip(),
            'browser' => $os . ' > ' . $browser,
            'dms_area_id' => $area_obj->dms_area_id,
        ];
        ActivityLogger::log(auth()->user(), $area_obj, $old_record, $new_record, 'updated');
    }

    public function checkAreaRegisterdOnDms($area_obj)
    {
        $response = $this->sendRequest(uri: static::$area_uri, type: static::$get, params: ['area_code' => $area_obj->code]);
        if ($response['error']) {
            Notification::make()
                ->title('Error')
                ->danger()
                ->body($response['message'])
                ->send();

            return false;
        }
        // dd($area_obj, $response['message']['data']);
        if (array_key_exists('data', $response['message']) && count($response['message']['data']) == 1) {
            // $area_obj->dms_area_id = $response['message']['data'][0]['id'];
            // $area_obj->save();
            return true;
        }

        return false;
    }

    public function updateAreaToDms($area_obj)
    {
        if ($area_obj->dms_area_id == null || $area_obj->dms_area_id == '') {
            $this->addAreaToDms($area_obj);

            return;
        }
        $post_data = [
            'area_code' => $area_obj->code,
            'area_name' => $area_obj->name,
        ];
        $response = $this->sendRequest(uri: static::$area_uri, args: "{$area_obj->dms_area_id}/", type: static::$patch, data: $post_data);
        if ($response['error'] && $response['message'] == 'Not found.') {
            $area_obj->dms_area_id = null;
            $area_obj->save();
            $this->updateAreaToDms($area_obj);

            return;
        }
        if ($response['error']) {
            Notification::make()
                ->title('Error')
                ->body('Error: ' . $response['message'])
                ->persistent()
                ->send();

            return;
        }
        $area_obj->dms_area_id = $response['message']['id'];
        $area_obj->save();

        $old_record = [
            'dms_area_id' => $area_obj->dms_area_id,
        ];

        $agent = new Agent;
        $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
        $os = $agent->platform();

        $new_record = [
            'ip' => request()->ip(),
            'browser' => $os . ' > ' . $browser,
            'dms_area_id' => $area_obj->dms_area_id,
        ];
        ActivityLogger::log(auth()->user(), $area_obj, $old_record, $new_record, 'updated');
    }
}
