<?php

declare(strict_types=1);

use Alumkit\Alumkit\Enums\UserState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('state', 'registered')->update(['state' => UserState::Unverified->value]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('state')->default(UserState::Unverified->value)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('state')->default('registered')->change();
        });

        DB::table('users')->where('state', UserState::Unverified->value)->update(['state' => 'registered']);
    }
};
