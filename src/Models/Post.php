<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Alumkit\Alumkit\Traits\RendersEditorContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $user_id
 * @property string|null $title
 * @property string|null $body
 * @property string|null $thumbnail
 * @property Carbon|null $published_at
 */
class Post extends Model
{
    use LogsActivity;
    use RendersEditorContent;

    /** @var string */
    protected $table = 'posts';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at']);
    }

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'thumbnail',
        'published_at',
    ];

    public function thumbnailUrl(): ?string
    {
        return $this->thumbnail
            ? route('alumkit.posts.thumbnail', basename($this->thumbnail))
            : null;
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    /**
     * Convert Editor.js JSON body to HTML for display.
     */
    public function bodyHtml(): string
    {
        return $this->renderEditorHtml($this->body);
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * @phpstan-ignore missingType.generics
     */
    public function user(): BelongsTo
    {
        /** @phpstan-ignore argument.templateType */
        return $this->belongsTo(config('alumkit.auth.user_model'));
    }
}
