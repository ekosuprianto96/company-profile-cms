<?php

namespace App\Services;

use App\Facades\PageFacade;
use Illuminate\Http\Request;

class FormCustomMetaService
{
    public function __construct(
        protected Request $request,
        protected string $page,
        protected string $type,
        protected $currentPage = null
    ) {
        $this->setPage($this->page);
    }

    public function setPage(string $page = ''): self
    {
        $this->currentPage = PageFacade::page($page);
        return $this;
    }

    public function save()
    {
        if (isset($this->request->custom_meta)) {
            $metaTag = $this->validateMeta();
            if (count($metaTag) > 0) {
                $this->currentPage->setCustomMeta($metaTag);
            }
        } else {
            $this->currentPage->setCustomMeta([]);
        }

        $pages = $this->readConfig();
        if (count($pages) > 0) {
            foreach ($pages as $key => $page) {
                if ($page['id'] != $this->currentPage->id) {
                    continue;
                }

                $pages[$key] = $this->currentPage->getConfig();
                app(\App\Services\PageConfigStore::class)->writePage($pages);
            }
        }
    }

    public function validateMeta()
    {
        $this->request->validate([
            'custom_meta' => [
                'string',
                function ($attribute, $value, $fail) {
                    $this->serializeMeta($attribute, $value, $fail);
                }
            ]
        ]);

        $cleanMeta = [];
        preg_match_all('/<meta\b[^>]*>/i', str_replace(["\n", "\r", "\t"], '', $this->request->custom_meta), $metaTags);
        $metaTags = array_values(array_filter($metaTags[0], function ($item) {
            return !empty(trim($item));
        }));

        return $metaTags;
    }

    public function serializeMeta($attribute, $value, $fail)
    {
        // Cek apakah ada tag <meta>
        preg_match_all('/<meta\b[^>]*>/i', $value, $matches);

        if (count($matches[0]) <= 0) {
            $fail('Tag meta tidak valid.');
        }

        foreach ($matches[0] as $metaTag) {
            // Cek apakah meta memiliki atribut name atau property yang valid
            if (!preg_match('/(name|property)=["\'][^"\']+["\']/i', $metaTag)) {
                $fail('Setiap meta tag harus memiliki atribut name atau property yang valid.');
            }

            // Cek apakah meta memiliki atribut content yang valid
            if (!preg_match('/content=["\'][^"\']+["\']/i', $metaTag)) {
                $fail('Setiap meta tag harus memiliki atribut content.');
            }
        }
    }

    public function readConfig()
    {
        $pageConfig = app(\App\Services\PageConfigStore::class)->pageJsonString();
        $toArray = json_decode($pageConfig, true);
        return $toArray;
    }
}
