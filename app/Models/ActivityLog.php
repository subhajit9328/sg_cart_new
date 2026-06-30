<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class ActivityLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'event',
        'causer_type',
        'causer_id',
        'attribute_changes',
        'properties',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attribute_changes' => 'array',
        'properties' => 'array',
    ];

    /**
     * Get the subject model that the activity is associated with.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the causer model that triggered the activity.
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get a human-readable description of what changed or what occurred.
     */
    public function getFormattedActionAttribute(): string
    {
        // 1. Delegate payment events to Payment model
        if (Str::startsWith($this->event, 'order.payment_')) {
            if (class_exists(\App\Models\Payment::class) && method_exists(\App\Models\Payment::class, 'formatActivityLog')) {
                return \App\Models\Payment::formatActivityLog($this);
            }
        }

        // 2. Delegate logistics events to the logistics package action
        if ($this->event === 'order.logistics_updated') {
            $logisticsAction = '\SGCart\LogisticTracking\Actions\UpdateLogisticTrackingAction';
            if (class_exists($logisticsAction) && method_exists($logisticsAction, 'formatActivityLog')) {
                return $logisticsAction::formatActivityLog($this);
            }
        }

        // 3. Delegate order events (or fallback) to Order model
        if (Str::startsWith($this->event, 'order.')) {
            if (class_exists(\App\Models\Order::class) && method_exists(\App\Models\Order::class, 'formatActivityLog')) {
                return \App\Models\Order::formatActivityLog($this);
            }
        }

        return $this->description;
    }

    /**
     * Get the human-readable name of who performed the action.
     */
    public function getCauserLabelAttribute(): string
    {
        if (!$this->causer_type) {
            return 'System';
        }

        $causer = $this->causer;
        if (!$causer) {
            $type = class_basename($this->causer_type);
            return "{$type} (ID: {$this->causer_id})";
        }

        $type = class_basename($this->causer_type);
        if ($type === 'User') {
            return "Admin: {$causer->name}";
        }

        if ($type === 'Customer') {
            return "Customer: {$causer->name}";
        }

        return "{$type}: {$causer->name}";
    }

    /**
     * Get timeline-specific data for the activity.
     */
    public function getTimelineAttribute(): array
    {
        return $this->getTimelineData();
    }

    /**
     * Get timeline-specific data for the activity.
     */
    public function getTimelineData(): array
    {
        $data = [
            'title' => $this->description,
            'description' => null,
            'icon' => 'fa-circle-info',
            'icon_color' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
            'extra_details' => null,
        ];

        // 1. Delegate payment events to Payment model
        if (Str::startsWith($this->event, 'order.payment_')) {
            if (class_exists(\App\Models\Payment::class) && method_exists(\App\Models\Payment::class, 'getTimelineData')) {
                return array_merge($data, \App\Models\Payment::getTimelineData($this));
            }
        }

        // 2. Delegate logistics events to the logistics package action
        if ($this->event === 'order.logistics_updated') {
            $logisticsAction = '\SGCart\LogisticTracking\Actions\UpdateLogisticTrackingAction';
            if (class_exists($logisticsAction) && method_exists($logisticsAction, 'getTimelineData')) {
                return array_merge($data, $logisticsAction::getTimelineData($this));
            }
        }

        // 3. Delegate order events to Order model
        if (Str::startsWith($this->event, 'order.')) {
            if (class_exists(\App\Models\Order::class) && method_exists(\App\Models\Order::class, 'getTimelineData')) {
                return array_merge($data, \App\Models\Order::getTimelineData($this));
            }
        }

        return $data;
    }
}

