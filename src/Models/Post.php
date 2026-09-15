<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $user_id
 * @property string|null $thumbnail
 * @property Carbon|null $published_at
 */
class Post extends Model
{
    use LogsActivity;

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
        $body = $this->body ?? '';

        if ($body === '' || $body[0] !== '{') {
            return e($body);
        }

        $data = json_decode($body, true);
        if (! is_array($data) || ! isset($data['blocks'])) {
            return e($body);
        }

        $html = '';
        foreach ($data['blocks'] as $block) {
            $html .= match ($block['type'] ?? '') {
                'paragraph' => '<p>' . ($block['data']['text'] ?? '') . '</p>',
                'header' => '<h' . ($block['data']['level'] ?? 2) . '>'
                    . ($block['data']['text'] ?? '') . '</h' . ($block['data']['level'] ?? 2) . '>',
                'list' => self::renderEditorList($block['data'] ?? []),
                'table' => self::renderEditorTable($block['data'] ?? []),
                'image' => '<img src="' . e($block['data']['file']['url'] ?? '') . '" alt="'
                    . e($block['data']['caption'] ?? '') . '">',
                default => '',
            };
        }

        return $html;
    }

    private static function renderEditorList(array $data): string
    {
        $tag = ($data['style'] ?? '') === 'ordered' ? 'ol' : 'ul';
        $items = '';
        foreach ($data['items'] ?? [] as $item) {
            $items .= '<li>' . $item . '</li>';
        }

        return "<{$tag}>{$items}</{$tag}>";
    }

    private static function renderEditorTable(array $data): string
    {
        $rows = $data['content'] ?? [];
        if ($rows === []) {
            return '';
        }

        $html = '<table>';
        foreach ($rows as $i => $cells) {
            $html .= '<tr>';
            foreach ($cells as $cell) {
                $tag = $i === 0 ? 'th' : 'td';
                $html .= "<{$tag}>{$cell}</{$tag}>";
            }
            $html .= '</tr>';
        }

        return $html . '</table>';
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
