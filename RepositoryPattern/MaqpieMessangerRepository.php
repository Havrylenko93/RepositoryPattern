<?php

namespace App\Repositories;

use Illuminate\Support\Collection,
    App\Repositories\Interfaces\MessengerRepositoryInterface,
    App\Models\User,
    GuzzleHttp\Client;

class MaqpieMessengerRepository implements MessengerRepositoryInterface
{
    const BASE_URL = 'https://api.maqpie.com';

    public $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Because maqpie use one endpoint for creating and updating users
     *
     * @param User $user
     * @return Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createUser(User $user): Collection
    {
        return $this->updateUser($user);
    }

    /**
     * @param User $user
     * @return Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateUser(User $user): Collection
    {
        $response = $this->client->request('PUT', self::BASE_URL . '/integration/users?appId=' . env('MESSENGER_APP_ID'),
            [
                'body' => $this->convertUserData($user)->toJson(),
                'headers' => [
                    'Authorization' => env('MESSENGER_API_TOKEN'),
                    'Content-type' => 'application/json'
                ],
            ])->getBody()->getContents();

        return collect($response);
    }

    public function deleteUser(User $user): Collection
    {
        $response = $this->client->request('POST', self::BASE_URL . '/integration/users?appId=' . env('MESSENGER_APP_ID'),
            [
                'body' => $this->convertUserData($user)->toJson(),
                'headers' => [
                    'Authorization' => env('MESSENGER_API_TOKEN'),
                    'Content-type' => 'application/json'
                ],
            ])->getBody()->getContents();

        return collect($response);
    }

    protected function convertUserData(User $user): Collection
    {
        return collect([
            'vendorUserId' => "$user->id",
            'email' => $user->email,
            'firstName ' => $user->first_name,
            'lastName ' => $user->last_name,
            'username' => $user->first_name . ' ' .  $user->last_name,
            'avatarUrl ' => $user->getImageProfileAttribute($user->image_profile),
        ]);
    }
}

