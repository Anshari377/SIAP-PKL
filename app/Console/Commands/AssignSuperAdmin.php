<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignSuperAdmin extends Command
{
    protected $signature = 'role:super-admin {email? : Email user yang diberi role super_admin}';

    protected $description = 'Assign role super_admin ke user berdasarkan email, atau ke user pertama jika email tidak diberikan';

    public function handle(): int
    {
        $email = $this->argument('email');

        if ($email) {
            $user = User::where('email', $email)->first();
            if (! $user) {
                $this->error("User dengan email '{$email}' tidak ditemukan.");

                return self::FAILURE;
            }
        } else {
            $user = User::orderBy('id')->first();
            if (! $user) {
                $this->error('Belum ada user di database.');

                return self::FAILURE;
            }
        }

        Role::findOrCreate('super_admin');
        $user->syncRoles(['super_admin']);

        $this->info("Role 'super_admin' berhasil di-assign ke {$user->name} ({$user->email}).");

        return self::SUCCESS;
    }
}