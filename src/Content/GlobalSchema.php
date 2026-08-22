<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Content;

class GlobalSchema
{
    /** @var array<FieldSchema> */
    protected array $fields = [];

    public function text(string $name): FieldSchema
    {
        return $this->addField($name, 'text');
    }

    public function textarea(string $name): FieldSchema
    {
        return $this->addField($name, 'textarea');
    }

    public function select(string $name): FieldSchema
    {
        return $this->addField($name, 'select');
    }

    public function image(string $name): FieldSchema
    {
        return $this->addField($name, 'image');
    }

    public function checkbox(string $name): FieldSchema
    {
        return $this->addField($name, 'checkbox');
    }

    public function editor(string $name): FieldSchema
    {
        return $this->addField($name, 'editor');
    }

    public function repeater(string $name): FieldSchema
    {
        return $this->addField($name, 'repeater');
    }

    /** @return array<FieldSchema> */
    public function fields(): array
    {
        return $this->fields;
    }

    protected function addField(string $name, string $type): FieldSchema
    {
        $field = new FieldSchema($name, $type);
        $this->fields[] = $field;

        return $field;
    }
}
