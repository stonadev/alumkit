<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table): void {
            // Deleting a page deletes its content rows (documented behavior);
            // globals have no parent row, so their owner strings stay non-FK.
            $table->foreignUuid('page_id')->nullable()->after('id')->constrained('pages')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('page_id');
        });
    }
};
