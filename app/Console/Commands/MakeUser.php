<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sdk:user {email=admin@sdkconsultoria.com} {--username=admin} {--name=default} {--lastname=default} {--role=super-admin} {--token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear un usuario';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): void
    {
        $user_class = config('auth.providers.users.model');

        $user = $this->findUserIfExist($user_class);

        if (!$user) {
            $user = $this->createUser($user_class);
        }

        $this->asingRolesToUser($user);
    }

    private function findUserIfExist($user_class)
    {
        $user = $this->findByEmail($user_class);

        if ($user) {
            return $user;
        }

        return $this->findByUsername($user_class);
    }

    private function findByEmail($user_class)
    {
        $user = $user_class::where('email', $this->argument('email'))->first();

        if ($user) {
            $this->info("El usuario {$this->argument('email')} ya existe");
            return $user;
        }
    }

    private function findByUsername($user_class)
    {
        $user = $user_class::where('username', $this->option('username'))->first();

        if ($user) {
            $this->info("El usuario {$this->option('username')} ya existe");
            return $user;
        }
    }

    private function createUser($user_class)
    {
        $user = new ($user_class);
        $user->name = $this->option('name');
        $user->username = $this->option('username');
        $user->lastname = $this->option('lastname');
        $user->email = $this->argument('email');
        $user->password = Hash::make('password',);
        $user->save();

        $this->info("Se creo el usuario $user->email");

        return $user;
    }

    private function asingRolesToUser($user)
    {
        $role = $this->option('role');

        $user->assignRole([$role]);
    }
}
