<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $title
 * @property string $slug
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property bool $is_published
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Page extends Model
{
    /** @var string */
    protected $table = 'pages';

    /** @var list<string> */
    protected $fillable = [
        'title',
        'slug',
        'meta_title',
        'meta_description',
        'is_published',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * @phpstan-ignore missingType.generics
     */
    public function contents(): HasMany
    {
        /** @phpstan-ignore argument.templateType */
        return $this->hasMany(Content::class, 'owner', 'slug')
            ->where('owner', 'like', 'page:%');
    }
}
