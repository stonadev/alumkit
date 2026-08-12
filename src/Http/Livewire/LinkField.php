<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Livewire\Component;

class LinkField extends Component
{
    public string $name;

    public ?string $fieldLabel = null;

    public ?string $label = null;

    public ?string $url = null;

    public bool $showModal = false;

    /** @var array<int, array{name: string, url: string}> */
    public array $suggestions = [];

    /** @var array<int, Route>|null */
    protected ?array $routes = null;

    public function mount(string $name, ?string $fieldLabel = null, ?string $url = null): void
    {
        $this->name = $name;
        $this->fieldLabel = $fieldLabel;
        $this->url = $url;
    }

    public function updatedUrl(): void
    {
        $q = strtolower(trim((string) $this->url));

        if ($q === '') {
            $this->suggestions = [];

            return;
        }

        $this->suggestions = collect($this->routes())
            ->filter(fn (Route $r) => $r->getName() !== null)
            ->filter(fn (Route $r) => str_contains(strtolower($r->getName().' '.$r->uri()), $q))
            ->map(fn (Route $r) => ['name' => $r->getName(), 'url' => $this->urlFor($r)])
            ->filter(fn (array $item) => $item['url'] !== null)
            ->take(8)
            ->values()
            ->all();
    }

    public function pickRoute(string $name): void
    {
        $this->url = route($name);
        $this->suggestions = [];

        if (blank($this->label)) {
            $this->label = Str::of($name)->replace('.', ' ')->headline()->toString();
        }
    }

    public function save(): void
    {
        if (! empty($this->url)) {
            $this->showModal = false;
        }
    }

    public function clear(): void
    {
        $this->label = null;
        $this->url = null;
    }

    public function render(): View
    {
        return view('alumkit::livewire.link-field');
    }

    /** @return array<int, Route> */
    protected function routes(): array
    {
        return $this->routes ??= app('router')->getRoutes()->getRoutes();
    }

    protected function urlFor(Route $r): ?string
    {
        try {
            return route($r->getName());
        } catch (UrlGenerationException) {
            return null;
        }
    }
}
