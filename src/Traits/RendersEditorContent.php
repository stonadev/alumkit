<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Traits;

/**
 * Converts Editor.js JSON (as stored by the alumkit editor field) to HTML for
 * display. Plain-text values are escaped and shown as-is.
 */
trait RendersEditorContent
{
    /**
     * Convert Editor.js JSON content to HTML for display.
     */
    protected function renderEditorHtml(?string $json): string
    {
        $content = $json ?? '';

        if ($content === '' || $content[0] !== '{') {
            return e($content);
        }

        $data = json_decode($content, true);

        if (! is_array($data) || ! isset($data['blocks'])) {
            return e($content);
        }

        $html = '';
        foreach ($data['blocks'] as $block) {
            $html .= match ($block['type'] ?? '') {
                'paragraph' => '<p>'.e($block['data']['text'] ?? '').'</p>',
                'header' => self::renderEditorHeader($block['data'] ?? []),
                'list' => self::renderEditorList($block['data'] ?? []),
                'table' => self::renderEditorTable($block['data'] ?? []),
                'image' => '<img src="'.e($block['data']['file']['url'] ?? '').'" alt="'
                    .e($block['data']['caption'] ?? '').'">',
                default => '',
            };
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function renderEditorHeader(array $data): string
    {
        $level = max(1, min(6, (int) ($data['level'] ?? 2)));
        $text = e($data['text'] ?? '');

        return "<h{$level}>{$text}</h{$level}>";
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function renderEditorList(array $data): string
    {
        $tag = ($data['style'] ?? '') === 'ordered' ? 'ol' : 'ul';
        $items = '';
        foreach ($data['items'] ?? [] as $item) {
            $items .= '<li>'.e($item).'</li>';
        }

        return "<{$tag}>{$items}</{$tag}>";
    }

    /**
     * @param  array<string, mixed>  $data
     */
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
                $html .= "<{$tag}>".e($cell)."</{$tag}>";
            }
            $html .= '</tr>';
        }

        return $html.'</table>';
    }
}
