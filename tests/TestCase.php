<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;


    protected function makeUser(array $params = []): User
    {
        $params['factory'] = $params['factory'] ?? [];
        $user = $this->findIfUserExist($params['factory']);

        if (! $user) {
            $user = User::factory($params['factory'])->create();
        }

        if ($params['permission'] ?? false) {
            $user->givePermissionTo($params['permission']);
        }

        if ($params['roles'] ?? false) {
            $user->assignRole($params['roles']);
        }

        return $user;
    }

    protected function findIfUserExist(array $factory)
    {
        return User::where('email', $factory['email'] ?? '')->first();
    }
}
