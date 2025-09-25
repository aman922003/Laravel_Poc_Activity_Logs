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
    protected static $logAttributes = ['name','email','contact_number','address'];
    protected static $logOnlyDirty = true;   // only store changed fields on update
    protected static $logName = 'user';      // nice name in activity logs

}
