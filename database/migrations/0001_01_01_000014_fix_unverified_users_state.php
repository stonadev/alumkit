<?php

declare(strict_types=1);

use Alumkit\Alumkit\Enums\UserState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->where('state', '!=', UserState::Unverified->value)
            ->update(['state' => UserState::Unverified->value]);
    }
};
