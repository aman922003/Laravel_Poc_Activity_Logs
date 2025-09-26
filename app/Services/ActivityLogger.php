<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Log an activity in a common way.
     *
     * @param string $message
     * @param \Illuminate\Database\Eloquent\Model|null $subject
     * @param array $properties
     * @param \Illuminate\Foundation\Auth\User|null $causer
     */
    public function log(string $message, ?Model $subject = null, array $properties = [], $causer = null): void
    {
        $causer = $causer ?? auth()->user();

        activity()
            ->causedBy($causer)
            ->performedOn($subject)
            ->withProperties(array_merge([
                'ip' => request()->ip(),
                'url' => request()->fullUrl(),
            ], $properties))
            ->log($message);
    }
    //  public function log(string $message, ?Model $subject = null, array $properties = [], $causer = null): void
    // {
    //     $causer = $causer ?? auth()->user();

    //     $activity = activity()
    //         ->causedBy($causer);

    //     // Only call performedOn() if subject is not null
    //     if ($subject !== null) {
    //         $activity->performedOn($subject);
    //     }

    //     $activity
    //         ->withProperties(array_merge([
    //             'ip' => request()->ip(),
    //             'url' => request()->fullUrl(),
    //         ], $properties))
    //         ->log($message);
    // }
}
