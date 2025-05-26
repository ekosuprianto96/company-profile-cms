<?php

namespace App\Services;

use App\Models\Banner;
use Illuminate\Support\Str;
use Artesaos\SEOTools\Traits\SEOTools;
use Closure;

class PageService
{
    use SEOTools;
    public function __construct(
        protected mixed $config = [],
        protected bool $isSerialize = false
    ) {
        $this->setConfigJSON();
    }

    // public function __call($name, $arguments)
    // {
    //     if (!method_exists($this, $name)) {
    //         throw new \Exception("Method $name tidak ditemukan.");
    //     }

    //     if (count($arguments) > 0) {
    //         return $this->{$name}(...$arguments);
    //     }

    //     return $this->{$name}();
    // }

    public function __get($name)
    {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }

        return null;
    }

    public function __destruct()
    {
        $this->config = collect([]);
    }

    public function page(string $id = ''): self
    {
        $this->setCurrent($id);

        if (isset($this->config['meta']) && count($this->config['meta']) > 0) {
            $this->seo()->setTitle(config('settings.value.app_name') . ' | ' . $this->meta()?->title ?? '', false);
            $this->seo()->setDescription($this->meta()->description ?? '');
            $this->seo()->opengraph()->setUrl(url()->current());
            $this->seo()->opengraph()->addProperty('type', $this->meta()->type ?? 'website');
            $this->seo()->twitter();
            $this->seo()->jsonLd()->setType($this->meta()->type ?? 'WebPage');
        }

        return $this;
    }

    public function serialize(bool $value): self
    {
        $this->isSerialize = $value;
        return $this;
    }

    public function title(): string
    {
        return $this->config['title'];
    }

    public function customMeta()
    {
        return $this->config['custom_meta'];
    }

    public function meta(): object
    {
        return (object) $this->config['meta'];
    }

    public function scripts(): array
    {
        return $this->config['scripts'];
    }

    public function styles(): array
    {
        return $this->config['styles'];
    }

    public function setTitle(string $title = '')
    {
        $this->config['title'] = $title;
        return $this;
    }

    public function addMeta(string $meta = '')
    {
        $this->config['meta'][] = $meta;
        return $this;
    }

    public function addScript(string $script = '')
    {
        $this->config['scripts'][] = $script;
        return $this;
    }

    public function addStyle(string $style = '')
    {
        $this->config['styles'][] = $style;
        return $this;
    }

    public function setScripts(array $value = [])
    {
        $this->config['scripts'] = $value;
        return $this;
    }

    public function setCustomMeta(array $value = [])
    {
        $this->config['custom_meta'] = $value;
        return $this;
    }

    public function setStyles(array $value = [])
    {
        $this->config['styles'] = $value;
        return $this;
    }

    public function setMeta(array $value = [])
    {
        $this->config['meta'] = [
            ...$this->config['meta'] ?? [],
            ...$value,
            'image' => $value['url_image'] ?? '',
            'title' => Str::title($value['title'] ?? ''),
            'keywords' => is_array($value['keywords']) ? join(',', $value['keywords']) : $value['keywords']
        ];

        $this->config['title'] = $value['title'] ?? $this->config['title'];

        return $this;
    }

    public function createPage(array $value = [])
    {
        $existsPage = $this->config->where('id', $value['id'])->first();

        if ($existsPage) {
            $this->config->transform(function ($item) use ($value) {
                if ($item['id'] === $value['id']) {
                    return $value;
                }

                return $item;
            });
        } else {
            $this->config->push($value);
        }

        return $this;
    }

    public function generateMetaLibrary(Closure $callback)
    {

        $callback($this->seo(), $this);

        return $this;
    }

    public function setCollectionSection($idSection, mixed $value)
    {
        collect($this->config['sections'])->map(function ($item, $key) use ($value, $idSection) {
            if ($item['id'] === $idSection) {
                $this->config['sections'][$key]['collection'] = $value;
            }
        });

        return $this;
    }

    public function registerSections($idPage, array $value = [])
    {
        $this->config->transform(function ($item) use ($value, $idPage) {
            if ($item['id'] === $idPage) {
                $item['sections'] = (
                    new SectionPageService(sections: $value)
                )->get();
            }

            return $item;
        });

        return $this;
    }


    public function setFormSection(string $section, array $value = [])
    {
        $section = $this->sections(true)->where('id', $section)->first();

        array_walk($value, function (&$val, $key) use (&$section) {
            $section['forms'][$key]['value'] = is_array($val)
                ? array_values($val)
                : $val;
        });

        foreach ($this->config['sections'] as $key => $value) {
            if ($section['id'] !== $value['id']) continue;

            $this->config['sections'][$key] = $section;
        }

        return $this;
    }

    public function existsSection(): bool
    {
        return count($this->config['sections'] ?? []) > 0;
    }

    public function notSection(array $section = [])
    {
        return $this->config['sections']->whereNotIn('id', $section);
    }

    public function getConfig()
    {
        if ($this->isSerialize) {
            $this->sections(true);
        }

        return $this->config;
    }

    public function sections(bool $isSerialize = false)
    {
        if (is_string($this->config['sections'][0] ?? [])) {
            $this->config['sections'] = (new SectionPageService(sections: $this->config['sections']))->get();
        }

        foreach (($this->config['sections'] ?? []) as $key => $section) {
            $modelClass = $section['collection']; // Pastikan nama class valid
            $checkView = is_array($section['view'])
                ? $section['view']['file']
                : $section['view'];

            $viewFile = resource_path('views/components/frontend/sections/' . $checkView . '.blade.php');

            if (!$isSerialize && !$this->isSerialize) {
                if (class_exists($modelClass)) {
                    $this->config['sections'][$key]['collection'] = app($modelClass);
                } else {
                    if (is_null($this->config['sections'][$key]['collection'])) {
                        $this->config['sections'][$key]['collection'] = null;
                    }
                }

                if (file_exists($viewFile)) {
                    $collection = $this->config['sections'][$key]['collection'];
                    $forms = (object) $this->config['sections'][$key]['forms'] ?? null;
                    $this->config['sections'][$key]['view'] = view(
                        'components.frontend.sections.' . $checkView,
                        compact('collection', 'forms')
                    );
                }
            }
        }

        return collect($this->config['sections'] ?? [])->sortBy('order');
    }

    public function collction(): mixed
    {
        return (
            count($this->config['collection'] ?? []) > 0 ?
            app($this->config['collection'])
            : null
        );
    }

    public function forms(?string $setion = null)
    {
        return new FormService(
            page: $this,
            section: $setion
        );
    }

    protected function setCurrent(string $id = '')
    {
        $this->config = $this->config->where('id', $id)->first();
        return $this;
    }

    protected function setConfigJSON(): void
    {
        if (!file_exists(base_path('config/page.json'))) {
            $this->config = collect([]);
        } else {
            $getFile = file_get_contents(base_path('config/page.json'));
            $config = json_decode($getFile, true);
            $this->config = collect($config);
        }
    }
}
