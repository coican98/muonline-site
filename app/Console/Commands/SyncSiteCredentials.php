<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SyncSiteCredentials extends Command
{
    protected $signature = 'sync:site-credentials';
    protected $description = 'Sync site credentials with MEMB_INFO';

    public function handle()
    {
        $users = DB::table('MEMB_INFO')->get();
        foreach ($users as $user) {
            if (!is_null($user->memb_guid)) {
                User::updateOrCreate(
                    ['id' => $user->memb_guid],
                    [
                        'id' => $user->memb_guid,
                        'username' => $user->memb___id,
                        'name' => $user->memb_name,
                        'email' => $user->mail_addr,
                        'password' => Hash::make($user->memb__pwd)
                    ]
                );
            } else {
                dd('ID is null for user: ', $user);
            }
        }

        $this->info('Site credentials synced successfully.');
    }
}
