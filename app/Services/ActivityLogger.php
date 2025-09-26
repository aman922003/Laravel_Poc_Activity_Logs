<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{

    /**
     * Fields we should never log details for.
     */
    protected array $sensitive = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

     /**
     * Check if a field is sensitive and should not be logged.
     */
    protected function isSensitive(string $field): bool
    {
        return in_array($field, $this->sensitive, true)
            || str_contains($field, 'password')
            || str_contains($field, 'secret')
            || str_contains($field, 'token');
    }

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

     /**
     * Special log method for user updates with dynamic messages.
     */
    public function logUserUpdate(Model $user, array $original, array $changes, $causer = null): void
    {
        $causer = $causer ?? auth()->user();

        $actor = $causer ? $causer->name : 'System';
        $target = $original['name'] ?? $user->name;

        $details = [];

        foreach ($changes as $field => $newValue) {
            if ($this->isSensitive($field)) continue;

            // Skip technical fields
            if (in_array($field, ['updated_at', 'updated_by'])) continue;

            $oldValue = $original[$field] ?? null;

            if ($oldValue !== $newValue) {
                // shorten long values for readability
                $oldShort = strlen((string) $oldValue) > 25 ? substr($oldValue, 0, 25) . '...' : $oldValue;
                $newShort = strlen((string) $newValue) > 25 ? substr($newValue, 0, 25) . '...' : $newValue;

                $details[] = sprintf('%s (%s → %s)', $field, $oldShort, $newShort);
            }
        }

        if (!empty($details)) {
            // Always 2 lines: who updated + changes
            $message = sprintf(
                "%s updated user %s\nChanged: %s",
                $actor,
                $target,
                implode(', ', $details)
            );

            activity()
                ->causedBy($causer)
                ->performedOn($user)
                ->withProperties([
                    'ip' => request()->ip(),
                    'url' => request()->fullUrl(),
                    'changes' => $changes,
                    'old' => $original,
                ])
                ->log($message);
        }
    }

}
