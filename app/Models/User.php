<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    // Add API token support, notifications, and soft delete functionality
    use HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * 
     * These are the fields that can be set via
     * create() or update() methods to prevent mass assignment vulnerabilities.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'contact_number',
        'address',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for arrays or JSON.
     * 
     * These attributes will not be visible when returning
     * the user model as JSON (e.g., in API responses).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * 
     * Creator
     */
    public function creator() {
    return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 
     * Editor
     */
    public function editor() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Spatie config for this model
    // protected static $logAttributes = ['name','email','contact_number','address'];
    // protected static $logOnlyDirty = true;   // only store changed fields on update
    // protected static $logName = 'user';      // nice name in activity logs
     /**
     * Boot model events for automatic activity logging
     */
    protected static function booted()
    {
        // Created
        static::created(function ($user) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->withProperties(['ip' => request()->ip(), 'url' => request()->fullUrl()])
                ->log("User {$user->name} created");
        });

        // Updated
        static::updated(function ($user) {
            $changes = $user->getChanges();
            $original = $user->getOriginal();

            $details = [];
            foreach ($changes as $field => $newValue) {
                if (in_array($field, ['updated_at', 'updated_by', 'password'])) {
                    continue;
                }
                $oldValue = $original[$field] ?? null;
                if ($oldValue !== $newValue) {
                    $details[] = sprintf("%s (%s → %s)", $field, $oldValue, $newValue);
                }
            }

            if (!empty($details)) {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($user)
                    ->withProperties([
                        'ip' => request()->ip(),
                        'url' => request()->fullUrl(),
                        'old' => $original,
                        'changes' => $changes,
                    ])
                    ->log("User {$user->name} updated: " . implode(', ', $details));
            }
        });

        // Soft deleted
        static::deleted(function ($user) {
            if ($user->isForceDeleting()) {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($user)
                    ->withProperties(['ip' => request()->ip(), 'url' => request()->fullUrl()])
                    ->log("User {$user->name} permanently deleted");
            } else {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($user)
                    ->withProperties(['ip' => request()->ip(), 'url' => request()->fullUrl()])
                    ->log("User {$user->name} soft deleted");
            }
        });

        // Restored
        static::restored(function ($user) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->withProperties(['ip' => request()->ip(), 'url' => request()->fullUrl()])
                ->log("User {$user->name} restored");
        });
    }

}
