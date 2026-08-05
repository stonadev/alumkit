<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('level');
            $table->string('institution');
            $table->string('subject')->nullable();
            $table->year('start_year')->nullable();
            $table->unsignedTinyInteger('start_month')->nullable();
            $table->year('end_year')->nullable();
            $table->unsignedTinyInteger('end_month')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educations');
    }
};
