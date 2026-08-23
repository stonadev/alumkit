<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Content;

class PageSchema
{
    /** @var array<string, SectionSchema> */
    protected array $sections = [];

    public function section(string $type, callable $callback): self
    {
        $section = new SectionSchema;
        $callback($section);
        $this->sections[$type] = $section;

        return $this;
    }

    /** @return array<string, SectionSchema> */
    public function sections(): array
    {
        return $this->sections;
    }

    /** @return array<string, array<string, mixed>> */
    public function toArray(): array
    {
        return array_map(
            fn (SectionSchema $section) => [
                'fields' => array_map(fn (FieldSchema $f) => $f->toArray(), $section->fields()),
            ],
            $this->sections,
        );
    }
}
