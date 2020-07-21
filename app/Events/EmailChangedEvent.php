<?php

namespace App\Events;

use App\Entities\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailChangedEvent
{
    use Dispatchable, SerializesModels;

    public User $user;
    public string $newEmail;
    public ?string $oldEmail;

    public function __construct(User $user, string $newEmail, ?string $oldEmail = null)
    {
        $this->user = $user;
        $this->newEmail = $newEmail;
        $this->oldEmail = $oldEmail;
    }
}
