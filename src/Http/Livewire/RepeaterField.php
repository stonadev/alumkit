<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Livewire;

use Alumkit\Alumkit\Content\FieldSchema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RepeaterField extends Component
{
    public string $name;

    /** @var array<int, array<string, mixed>> */
    public array $fields = [];

    /** @var array<int, array<string, mixed>> */
    public array $rows = [];

    /**
     * @param  array<int, FieldSchema|array<string, mixed>>  $fields
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function mount(string $name, array $fields = [], array $rows = []): void
    {
        $this->name = $name;
        $this->fields = array_map(
            fn (FieldSchema|array $field) => $field instanceof FieldSchema ? $field->toArray() : $field,
            $fields,
        );
        $this->rows = $rows ?: [];
    }

    public function addRow(): void
    {
        $this->rows[] = [];
    }

    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    public function render(): View
    {
        return view('alumkit::livewire.repeater-field');
    }
}
