<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
  public function boot(): void
    {
        // Check if the settings table exists
        if (Schema::hasTable('settings')) {
            $webSetting = DB::table('settings')->where('slug', 'web_setting')->value('contents');
            $decodedSetting = $webSetting ? json_decode($webSetting, true) : [];

            if (json_last_error() !== JSON_ERROR_NONE) {
                $decodedSetting = []; // Fallback to an empty array if decoding fails
            }

            view()->share('webSetting', $decodedSetting);
        } else {
            // Fallback to an empty array if the table does not exist
            view()->share('webSetting', []);
        }
    }
}
