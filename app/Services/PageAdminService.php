<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

class PageAdminService extends PageService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __get($name)
    {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }

        throw new \Exception('Undefined property: ' . $name);
    }

    public function page(string $id = ''): self
    {
        parent::page($id);
        return $this;
    }

    public function initialRequest(Request $request, $type)
    {
        return match ($type) {
            'scripts' => new FormScriptService(request: $request, page: $this->config['id'], type: $type),
            'styles' => new FormStyleService(request: $request, page: $this->config['id'], type: $type),
            'meta' => new FormMetaService(request: $request, page: $this->config['id'], type: $type),
            'sections' => new FormSectionService(request: $request, page: $this->config['id'], type: $type),
            'custom_meta' => new FormCustomMetaService(request: $request, page: $this->config['id'], type: $type),
            default => null
        };
    }

    public function getTabs()
    {
        $tabs = [];
        $data = [];

        if (count($this->config) > 0) {
            foreach ($this->config as $key => $value) {
                $tabs[] = $key;
                // if (!empty($this->config[$key]) || count($this->config[$key]) > 0) {
                // }
            }
        }

        if (count($tabs) > 0) {
            $data['targets'] = array_map(function ($item) {
                if (in_array($item, ['meta', 'scripts', 'styles', 'sections', 'custom_meta'])) {
                    return 'tab_' . $item;
                }
            }, $tabs);

            $data['tabs'] = array_map(function ($item) {
                if (in_array($item, ['meta', 'scripts', 'styles', 'sections', 'custom_meta'])) {
                    $this->config['sections'] = $this->getSections();
                    return [
                        'title' => Str::title(str_replace('_', ' ', $item)),
                        'id' => 'tab_' . $item,
                        'view' => view(
                            'admin.components.page-forms.' . $item,
                            ['item' => $this->config[$item], 'id' => 'tab_' . $item, 'config' => $this->config]
                        )->render()
                    ];
                }
            }, $tabs);

            $data['tabs'] = array_values(array_filter($data['tabs'], function ($item) {
                return !is_null($item);
            }));

            $data['targets'] = array_values(array_filter($data['targets'], function ($item) {
                return !is_null($item);
            }));
        }

        return $data;
    }

    public function getSections()
    {
        if (is_string($this->config['sections'][0])) {
            return (new SectionPageService(sections: $this->config['sections']))->get();
        }

        return $this->config['sections'];
    }
}
