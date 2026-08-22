<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $owner
 * @property string $type
 * @property array<string, mixed> $fields
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $owner_type
 */
class Content extends Model
{
    /** @var string */
    protected $table = 'contents';

    /** @var list<string> */
    protected $fillable = [
        'owner',
        'type',
        'fields',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'fields' => 'array',
        ];
    }

    /**
     * @param  Builder<Content>  $query
     * @return Builder<Content>
     */
    public function scopeForPage(Builder $query, string $slug): Builder
    {
        return $query->where('owner', "page:{$slug}");
    }

    /**
     * @param  Builder<Content>  $query
     * @return Builder<Content>
     */
    public function scopeForGlobal(Builder $query, string $key): Builder
    {
        return $query->where('owner', "global:{$key}");
    }

    public function getOwnerTypeAttribute(): string
    {
        return str_contains($this->owner, ':')
            ? explode(':', $this->owner, 2)[0]
            : 'unknown';
    }
}
