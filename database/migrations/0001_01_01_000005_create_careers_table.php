<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('careers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('job_title');
            $table->string('company');
            $table->string('employment_type');
            $table->string('industry')->nullable();
            $table->string('location')->nullable();
            $table->year('start_year');
            $table->unsignedTinyInteger('start_month')->nullable();
            $table->boolean('is_current')->default(false);
            $table->year('end_year')->nullable();
            $table->unsignedTinyInteger('end_month')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('careers');
    }
};
