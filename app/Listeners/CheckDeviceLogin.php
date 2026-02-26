<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use App\Notifications\NewDeviceLogin;
use Illuminate\Support\Facades\Request;

class CheckDeviceLogin
{
    public function handle(Login $event)
    {
        $user = $event->user;
        $settings = $user->notification_settings;
        if (empty($settings['notif_login']) || $settings['notif_login'] == false) {
            return;
        }

        $currentIp = Request::ip();
        $userAgent = Request::header('User-Agent');

        $deviceExists = DB::table('known_devices')
            ->where('user_id', $user->id)
            ->where('ip_address', $currentIp)
            ->where('user_agent', $userAgent)
            ->exists();

        if (!$deviceExists) {

            /** @var User $user */
            $details = [
                'ip' => $currentIp,
                'browser' => $userAgent,
                'time' => now()->format('d M Y H:i'),
            ];
            $user->notify(new NewDeviceLogin($details));

            DB::table('known_devices')->insert([
                'user_id' => $user->id,
                'ip_address' => $currentIp,
                'user_agent' => $userAgent,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
