<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;
use App\Facades\PageFacade;
use Illuminate\Http\Request;


class FormScriptService
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
        if (isset($this->request->scripts)) {
            $scripts = $this->validateScript();
            if (count($scripts) > 0) {
                $this->currentPage->setScripts($scripts);
            }
        } else {
            $this->currentPage->setScripts([]);
        }

        $pages = $this->readConfig();
        if (count($pages) > 0) {
            foreach ($pages as $key => $page) {
                if ($page['id'] != $this->currentPage->id) {
                    continue;
                }

                $pages[$key] = $this->currentPage->getConfig();
                file_put_contents(base_path('config/page.json'), json_encode($pages, JSON_PRETTY_PRINT));
            }
        }
    }

    public function validateScript()
    {
        $this->request->validate([
            'scripts' => [
                'string',
                function ($attribute, $value, $fail) {
                    $this->serializeScript($attribute, $value, $fail);
                }
            ]
        ]);

        $cleanScripts = [];
        preg_match_all('/<script\b[^>]*>.*?<\/script>/is', str_replace(["\n", "\r", "\t"], '', $this->request->scripts), $scripts);
        $scripts = array_values(array_filter($scripts[0], function ($item) {
            return !empty(trim($item));
        }));

        return $scripts;
    }

    public function serializeScript($attribute, $value, $fail)
    {
        // Ambil daftar domain yang diizinkan dari config
        $allowedDomains = config('purifier.allowed_script_sources', []);

        // Gabungkan domain dalam regex yang dinamis
        $allowedDomainsRegex = implode('|', array_map('preg_quote', $allowedDomains));

        // Cek apakah ada tag <script>
        preg_match_all('/<script\b[^>]*>/i', $value, $matches);

        if (count($matches[0]) <= 0) {
            $fail('Tag script yang dimasukkan tidak valid');
        }

        foreach ($matches[0] as $scriptTag) {
            // Cek apakah tag <script> memiliki atribut src yang valid
            if (!preg_match('/src=["\']?(https?:)?\/\/(' . $allowedDomainsRegex . ')\/[^"\']*["\']?/i', $scriptTag)) {
                $fail('Hanya script dari sumber terpercaya yang diizinkan.');
            }
        }
    }

    public function readConfig()
    {
        $pageConfig = file_get_contents(base_path('config/page.json'));
        $toArray = json_decode($pageConfig, true);
        return $toArray;
    }
}
