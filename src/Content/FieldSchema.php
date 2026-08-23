<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Content;

use InvalidArgumentException;

class FieldSchema
{
    public readonly string $name;

    public readonly string $type;

    public ?string $label = null;

    public bool $required = false;

    /** @var array<string, string>|null */
    public ?array $options = null;

    /** @var array<FieldSchema>|null */
    public ?array $fields = null;

    public ?string $help = null;

    private const VALID_TYPES = [
        'text',
        'textarea',
        'select',
        'image',
        'checkbox',
        'editor',
        'repeater',
    ];

    public function __construct(string $name, string $type)
    {
        if (! in_array($type, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException("Invalid field type '{$type}'. Must be one of: ".implode(', ', self::VALID_TYPES));
        }

        $this->name = $name;
        $this->type = $type;
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function required(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    /** @param array<string, string> $options */
    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /** @param array<FieldSchema> $fields */
    public function fields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function help(string $help): self
    {
        $this->help = $help;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'label' => $this->label,
            'required' => $this->required,
        ];

        if ($this->options !== null) {
            $data['options'] = $this->options;
        }

        if ($this->fields !== null) {
            $data['fields'] = array_map(fn (FieldSchema $f) => $f->toArray(), $this->fields);
        }

        if ($this->help !== null) {
            $data['help'] = $this->help;
        }

        return $data;
    }
}
