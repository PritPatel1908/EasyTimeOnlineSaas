<?php

namespace App\Traits;

trait HasSettingPolicy
{
    public static function canAccess(): bool
    {
        $canAccess = static::canViewAny();
        if (! $canAccess) {
            return false;
        }
        /*
            * Check if the model is in the list of models that are controlled by settings
            * If it is, check if the setting is enabled
        */
        switch (static::$model) {
            case 'App\Models\Tenant\ApprovalFlow':
                $canAccess = setting('approvals', 0) == '1';
                break;
            case 'App\Models\Tenant\ApprovalStatusDetails':
                $canAccess = setting('approval_status_details', 0) == '1';
                break;
            default:
                $canAccess = static::canViewAny();
        }

        /*
            * Settings Mentioned outside this class
            * attendance_system   --> this is intented to be used enable or disable entire attendance system
        */
        return $canAccess;
    }
}
