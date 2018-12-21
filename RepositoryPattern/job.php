<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable,
    Illuminate\Queue\SerializesModels,
    Illuminate\Queue\InteractsWithQueue,
    Illuminate\Contracts\Queue\ShouldQueue,
    Illuminate\Foundation\Bus\Dispatchable,
    App\Models\User,
    App\Repositories\Interfaces\MessengerRepositoryInterface;

class SyncUserDataWithMessenger implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $messengerRepository;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(MessengerRepositoryInterface $messengerRepository)
    {
        if ($this->user->active !== User::USER_ACTIVE) {
            return;
        }

        if ($this->user->deleted_at !== null) {
            $messengerRepository->deleteUser($this->user);
            return;
        }

        $messengerRepository->updateUser($this->user);
    }
}
