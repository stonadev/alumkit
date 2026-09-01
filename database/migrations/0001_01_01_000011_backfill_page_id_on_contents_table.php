<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Content rows created before the page FK existed have a NULL page_id.
        // Link them so cascade-on-delete covers pre-existing installs too.
        DB::table('contents')
            ->whereNull('page_id')
            ->where('owner', 'like', 'page:%')
            ->update([
                'page_id' => DB::raw(
                    '(select id from pages where pages.slug = substr(contents.owner, 6))',
                ),
            ]);
    }

    public function down(): void
    {
        DB::table('contents')->whereNotNull('page_id')->update(['page_id' => null]);
    }
};
