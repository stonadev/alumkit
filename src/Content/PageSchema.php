<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Content;

class PageSchema
{
    /** @var array<string, SectionSchema> */
    protected array $sections = [];

    protected ?string $view = null;

    public function section(string $type, callable $callback): self
    {
        $section = new SectionSchema;
        $callback($section);
        $this->sections[$type] = $section;

        return $this;
    }

    /**
     * The Blade view that publicly renders this page.
     */
    public function view(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /** @return array<string, SectionSchema> */
    public function sections(): array
    {
        return $this->sections;
    }

    /**
     * The Blade view that publicly renders this page, or null when unset.
     */
    public function viewName(): ?string
    {
        return $this->view;
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
