<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Content;

class ContentRegistry
{
    /** @var array<string, PageSchema> */
    protected array $pages = [];

    /** @var array<string, GlobalSchema> */
    protected array $globals = [];

    public function registerPage(string $slug, callable $callback): self
    {
        $page = new PageSchema;
        $callback($page);
        $this->pages[$slug] = $page;

        return $this;
    }

    public function registerGlobal(string $key, callable $callback): self
    {
        $global = new GlobalSchema;
        $callback($global);
        $this->globals[$key] = $global;

        return $this;
    }

    public function getPage(string $slug): ?PageSchema
    {
        return $this->pages[$slug] ?? null;
    }

    public function getGlobal(string $key): ?GlobalSchema
    {
        return $this->globals[$key] ?? null;
    }

    /** @return array<string, PageSchema> */
    public function getPages(): array
    {
        return $this->pages;
    }

    /** @return array<string, GlobalSchema> */
    public function getGlobals(): array
    {
        return $this->globals;
    }
}
