<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\GeneralSetting;
use App\Constants\Status;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $general = GeneralSetting::first();
        if ($general && empty($general->socialite_credentials)) {
            $defaultCredentials = (object)[
                'google' => (object)[
                    'client_id' => '',
                    'client_secret' => '',
                    'status' => Status::DISABLE,
                ],
            ];
            $general->socialite_credentials = $defaultCredentials;
            $general->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration
    }
};
