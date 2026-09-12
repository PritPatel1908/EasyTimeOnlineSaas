<?php

namespace App\Dms;

use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\User;
use App\Models\Visitor;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class EmpDms extends DmsRequest
{
    public function syncDataWithDms()
    {
        $response = $this->sendRequest(uri: static::$employee_uri);
        $this->saveFetchedData($response);
        $this->uploadAllEmployeeToDms();
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
        $this->fetchAllEmployeeDmsID($response['message']);
        if (array_key_exists('next', $response['message']) && $response['message']['next'] != null) {
            $new_response = $this->sendRequest(url: $response['message']['next']);
            $this->saveFetchedData($new_response);
        } else {
            Notification::make()
                ->title('Success')
                ->success()
                ->body('Syncing Employees Completed')
                ->send();
        }
    }

    public function fetchAllEmployeeDmsID($response)
    {
        if (array_key_exists('data', $response)) {
            foreach ($response['data'] as $emp) {
                $numrows = User::where('code', $emp['emp_code'])->update(['dms_user_id' => $emp['id']]);
                // if ($numrows < 1) {
                //     Visitor::where('visitor_code', $emp['emp_code'])->update(['dms_user_id' => $emp['id']]);
                // }
            }
        }
    }

    public function uploadAllEmployeeToDms()
    {
        $employees = User::withoutGlobalScopes()->where('id', '!=', 1)->get();
        foreach ($employees as $employee) {
            if ($employee->dms_user_id == null) {
                $this->uploadNewEmployeeToDms($employee);
            } else {
                $this->updateEmployeeToDms($employee);
            }
        }
    }

    public function uploadNewEmployeeToDms(User $employee)
    {
        if ($employee->left_date != null && $employee->left_date->isPast()) {
            $areas = [1];
        } else {
            $areas = $employee->areas()->pluck('dms_area_id')->toArray();
            if (! $areas) {
                $areas = [1];
            }
        }
        $post_data = [
            'emp_code' => $employee->code,
            'first_name' => $employee->fname,
            'last_name' => $employee->lname,
            'nickname' => $employee->name,
            'verify_mode' => '-1',
            'card_no' => $employee->card,
            'department' => 1,
            'position' => 1,
            'hire_date' => $employee->join_date?->format('Y-m-d'),
            'gender' => match ($employee->gender) {
                'male' => 'M',
                'female' => 'F',
                default => null
            },
            'birthday' => $employee->dob?->format('Y-m-d'),
            'emp_type' => '1', // (1, 'Permanent');(2, 'Temporary')
            'mobile' => $employee->number,
            'enable_att' => $employee->is_inactive ?? true,
            'enable_overtime' => false,
            'enable_holiday' => false,
            'area' => $areas,
        ];

        $response = $this->sendRequest(uri: static::$employee_uri, type: static::$post, data: $post_data);

        if ($response['error']) {
            Notification::make()
                ->title('Error')
                ->body('Error: '.$response['message'])
                ->persistent()
                ->send();

            return false;
        }

        $employee->dms_user_id = $response['message']['id'];
        $employee->save();

        return true;
    }

    public function updateEmployeeToDms($employee)
    {
        if ($employee->dms_user_id == null) {
            $this->uploadNewEmployeeToDms($employee);

            return;
        }
        if ($employee->left_date != null && $employee->left_date->isPast()) {
            $areas = [1];
        } else {
            $areas = $employee->areas()->pluck('dms_area_id')->toArray();
            if (! $areas) {
                $areas = [1];
            }
        }
        $post_data = [
            'id' => $employee->dms_user_id,
            'emp_code' => $employee->code,
            'first_name' => $employee->fname,
            'last_name' => $employee->lname,
            'nickname' => $employee->name,
            'verify_mode' => '-1',
            'card_no' => $employee->card,
            'department' => 1,
            'position' => 1,
            'hire_date' => $employee->join_date?->format('Y-m-d'),
            'gender' => match ($employee->gender) {
                'male' => 'M',
                'female' => 'F',
                default => null
            },
            'birthday' => $employee->dob?->format('Y-m-d'),
            'emp_type' => '1', // (1, 'Permanent');(2, 'Temporary')
            'mobile' => $employee->number,
            'enable_att' => $employee->is_inactive ?? true,
            'enable_overtime' => false,
            'enable_holiday' => false,
            'area' => $areas,
        ];
        $response = $this->sendRequest(uri: static::$employee_uri, args: "{$employee->dms_user_id}/", type: static::$put, data: $post_data);
        // dd($response['message']);
        if ($response['error'] && $response['message'] == 'Not found.') {
            $employee->dms_user_id = null;
            $employee->save();
            $this->uploadNewEmployeeToDms($employee);

            return;
        }

        if ($response['error']) {
            Notification::make()
                ->title('Error')
                ->body('Error: '.$response['message'])
                ->persistent()
                ->send();

            return false;
        }

        return true;
    }

    public function makeInactiveLeftEmployee()
    {
        $employee = User::where('id', '!=', 1)->where('left_date', '<', now())->where('is_inactive', false)->update(['is_inactive' => true]);
        $inactive_employees = User::where('is_inactive', true)->get()->pluck('dms_user_id')->toArray();
        $post_data = [
            'employees' => $inactive_employees, // "employees": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], // "employees": "all
            'areas' => [1],
        ];
        $response = $this->sendRequest(uri: static::$adjust_area, type: static::$post, data: $post_data);
        if ($response['error']) {
            return false;
        }
        foreach ($inactive_employees as $user) {
            $employee = User::where('dms_user_id', $user)->first();
            $employee->is_inactive = true;
            $employee->save();
        }

        return true;
    }

    // TODO:remove comment after make AttendanceLog
    public function makeInactiveByLastActiveTime()
    {
        User::where('is_inactive', false)->where('id', '!=', 1)->where('inactive_days', '>', 0)->chunk(100, function ($users) {
            foreach ($users as $user) {
                $last_log = AttendanceLog::where('user_code', $user->code)->orderByDesc('datetime')->limit(1)->first();
                if ($last_log == null) {
                    if ($user->created_at->diffInDays(now()) >= $user->inactive_days) {
                        $user->is_inactive = true;
                        $user->inactive_date = now();
                        $user->save();
                        $this->sendInactiveNotification($user);
                    }
                } elseif ($last_log->datetime->diffInDays(now()) >= $user->inactive_days) {
                    $user->is_inactive = true;
                    $user->inactive_date = now();
                    $user->save();
                    $this->sendInactiveNotification($user);
                }
            }
        });
    }

    public function sendInactiveNotification($user)
    {
        $expiry_date = 'Never';
        $changed_date = $user->password_changed_at;
        if ($changed_date != null) {
            $password_policy = $user->password_policy;
            if ($password_policy != null) {
                $expiry_date = $changed_date->copy()->addDays($password_policy->password_expiry_days)->format(config('date_format', 'Y-m-d'));
                $content = [
                    'name' => $user->name,
                    'emp_code' => $user->code,
                    'department_name' => $user->department->name,
                    'expiry_date' => $expiry_date,
                ];

                // TODO:remove comment after make password notification email
                // Mail::to('tgb2006@gmail.com')->send(new PasswordNotifiationMail($content));
            }
        }
    }

    public function adjustEmployeeArea($employee)
    {
        if ($employee->dms_user_id == null) {
            $this->uploadNewEmployeeToDms($employee);
        }
        if ($employee->left_date != null && $employee->left_date->isPast()) {
            $areas = [1];
        } else {
            $areas = $employee->areas()->pluck('dms_area_id')->toArray();
        }

        $post_data = [
            'employees' => [$employee->dms_user_id], // "employees": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], // "employees": "all
            'areas' => $areas,
        ];
        $response = $this->sendRequest(uri: static::$adjust_area, type: static::$post, data: $post_data);
        if ($response['error']) {
            Notification::make()
                ->title('Error')
                ->body('Error: '.$response['message'])
                ->persistent()
                ->send();

            return false;
        }

        return true;
    }
}
