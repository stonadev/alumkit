<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table): void {
            $table->id();
            $table->string('owner')->index();  // "page:{slug}" for page-bound content, "global:{key}" for global singletons
            $table->string('type')->index(); // Content type alias (e.g. 'text', 'textarea', 'select', 'repeater')
            $table->json('fields'); // Field values as a JSON blob
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
