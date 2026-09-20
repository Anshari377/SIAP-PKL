<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        // Clean up dummy demo applications & users to ensure real data consistency across all roles.
        $oldIds = User::where('email', 'like', 'demo.%@pkl.test')->pluck('id');

        if ($oldIds->isNotEmpty()) {
            $appIds = Application::whereIn('user_id', $oldIds)->pluck('id');
            ApplicationMember::whereIn('application_id', $appIds)->delete();
            Application::whereIn('id', $appIds)->delete();

            DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->whereIn('model_id', $oldIds)
                ->delete();

            User::whereIn('id', $oldIds)->delete();
        }
    }
}