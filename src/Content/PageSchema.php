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
}
