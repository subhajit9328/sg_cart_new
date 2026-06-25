<?php

namespace App\Actions;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class LogActivity
{
    /**
     * Create an activity log record.
     *
     * @param  string  $description  Brief description of what happened.
     * @param  string|null  $event  The name of the event (e.g. login.success, login.failed).
     * @param  Model|null  $subject  The model being acted upon.
     * @param  array  $properties  Additional diagnostic metadata (IP, user agent, etc).
     * @param  Model|null  $causer  The user or customer who caused the action.
     * @param  array|null  $attributeChanges  Old and new attributes during update.
     * @param  string  $logName  The classification group of the log (defaults to 'security').
     * @return ActivityLog
     */
    public function capture(
        string $description,
        ?string $event = null,
        ?Model $subject = null,
        array $properties = [],
        ?Model $causer = null,
        ?array $attributeChanges = null,
        string $logName = 'security'
    ): ActivityLog {
        // Resolve causer automatically if not explicitly provided
        if (! $causer) {
            if (Auth::guard('customer')->check()) {
                $causer = Auth::guard('customer')->user();
            } elseif (Auth::check()) {
                $causer = Auth::user();
            }
        }

        // Merge standard request properties (IP and User Agent) if not already provided
        $properties['ip'] = $properties['ip'] ?? Request::ip();
        $properties['user_agent'] = $properties['user_agent'] ?? Request::userAgent();

        return ActivityLog::create([
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'event' => $event,
            'causer_type' => $causer ? get_class($causer) : null,
            'causer_id' => $causer ? $causer->getKey() : null,
            'attribute_changes' => $attributeChanges,
            'properties' => $properties,
        ]);
    }
}
