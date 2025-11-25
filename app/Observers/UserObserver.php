<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityLogger;

class UserObserver
{
    protected $logger;

    public function __construct(ActivityLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * When user created
     */
    public function created(User $user)
    {
        $this->logger->log('User created', $user);
    }

    /**
     * When user updated
     */
    public function updated(User $user)
    {
        $original = $user->getOriginal();
        $changes = $user->getChanges();

        $this->logger->logUserUpdate($user, $original, $changes);
    }

    /**
     * Soft deleted
     */
    public function deleted(User $user)
    {
        if ($user->isForceDeleting()) {
            $this->logger->log('User permanently deleted', $user);
        } else {
            $this->logger->log('User soft deleted', $user);
        }
    }

    /**
     * Restored from soft delete
     */
    public function restored(User $user)
    {
        $this->logger->log('User restored', $user);
    }

    /**
     * Force delete
     */
    public function forceDeleted(User $user)
    {
        $this->logger->log('User permanently deleted', $user);
    }
}
