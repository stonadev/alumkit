<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Content;

use Alumkit\Alumkit\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FieldExtractor
{
    public function __construct(
        private readonly Request $request,
    ) {}

    /**
     * Field values for one schema from the request. Existing stored values
     * (images especially, which are only replaced when a new file is uploaded)
     * are kept when the request sends nothing for a field. Select and repeater
     * values are whitelisted against the schema.
     *
     * @param  array<FieldSchema>  $fields
     * @return array<string, mixed>
     */
    public function extract(array $fields, string $owner, string $type): array
    {
        $existing = Content::where('owner', $owner)->where('type', $type)->first();

        $values = $existing->fields ?? [];

        foreach ($fields as $field) {
            if ($field->type === 'checkbox') {
                $values[$field->name] = $this->request->boolean("fields.{$field->name}");

                continue;
            }

            if ($field->type === 'image') {
                if ($this->request->hasFile("fields.{$field->name}")) {
                    $path = $this->request->file("fields.{$field->name}")->store('content-images', 'public');

                    if (is_string($path)) {
                        if (isset($values[$field->name]) && is_string($values[$field->name])) {
                            Storage::disk('public')->delete($values[$field->name]);
                        }

                        $values[$field->name] = $path;
                    }
                }

                continue;
            }

            if ($field->type === 'select') {
                if ($this->request->has("fields.{$field->name}")) {
                    $value = (string) $this->request->input("fields.{$field->name}");
                    $values[$field->name] = $field->options !== null && array_key_exists($value, $field->options) ? $value : '';
                }

                continue;
            }

            if ($field->type === 'repeater') {
                $rows = $this->request->input("fields.{$field->name}", []);

                $values[$field->name] = array_map(
                    fn (array $row): array => $this->cleanRepeaterRow($row, $field->fields ?? []),
                    is_array($rows) ? $rows : [],
                );

                continue;
            }

            $values[$field->name] = (string) $this->request->input("fields.{$field->name}", '');
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<FieldSchema>  $subFields
     * @return array<string, mixed>
     */
    private function cleanRepeaterRow(array $row, array $subFields): array
    {
        $clean = [];

        foreach ($subFields as $sub) {
            if (! array_key_exists($sub->name, $row)) {
                continue;
            }

            $value = $row[$sub->name];

            $clean[$sub->name] = match ($sub->type) {
                'checkbox' => (bool) $value,
                'select' => $sub->options !== null && array_key_exists((string) $value, $sub->options) ? $value : '',
                default => is_string($value) ? $value : '',
            };
        }

        return $clean;
    }
}
