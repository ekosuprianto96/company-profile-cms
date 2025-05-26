<?php

namespace App\Services;

use App\Facades\PageFacade;
use Illuminate\Http\Request;

class FormStyleService
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
        if (isset($this->request->styles)) {
            $styles = $this->validateCustomStyle();
            if (count($styles) > 0) {
                $this->currentPage->setStyles($styles);
            }
        } else {
            $this->currentPage->setStyles([]);
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

    public function validateCustomStyle()
    {
        $this->request->validate([
            'styles' => [
                'string',
                function ($attribute, $value, $fail) {
                    $this->serializeStyle($attribute, $value, $fail);
                }
            ]
        ]);

        $cleanStyles = [];
        preg_match_all('/<(style|link)\b[^>]*>.*?<\/style>/is', str_replace(["\n", "\r", "\t"], '', $this->request->styles), $styleTags);
        preg_match_all('/<link\b[^>]*>/i', str_replace(["\n", "\r", "\t"], '', $this->request->styles), $linkTags);
        $styleTags = array_values(array_filter([...$styleTags[0], ...$linkTags[0]] ?? [], function ($item) {
            return !empty(trim($item));
        }));

        return $styleTags;
    }

    public function serializeStyle($attribute, $value, $fail)
    {
        // Ambil daftar domain yang diizinkan dari config
        $allowedDomains = config('purifier.allowed_css_sources', []);
        $allowedDomainsRegex = implode('|', array_map('preg_quote', $allowedDomains));

        // Cek tag <style>
        preg_match_all('/<style\b[^>]*>.*?<\/style>/is', $value, $styleMatches);
        // Cek tag <link>
        preg_match_all('/<link\b[^>]*>/i', $value, $linkMatches);

        if (count($styleMatches[0]) <= 0 && count($linkMatches[0]) <= 0) {
            $fail('Kode CSS atau tag link tidak valid');
        }

        foreach ($styleMatches[0] as $styleTag) {
            // Cek apakah mengandung @import, javascript, atau expression (CSS berbahaya)
            if (preg_match('/@import|expression\s*\(|javascript\s*:/i', $styleTag)) {
                $fail('Kode CSS mengandung sintaks yang berbahaya.');
            }
        }

        foreach ($linkMatches[0] as $linkTag) {
            // Pastikan tag <link> memiliki atribut rel="stylesheet"
            if (!preg_match('/rel=["\']stylesheet["\']/i', $linkTag)) {
                $fail('Hanya tag <link> dengan rel="stylesheet" yang diperbolehkan.');
            }

            // Cek apakah tag <link> memiliki href yang berasal dari domain yang diizinkan
            if (!preg_match('/href=["\']?(https?:)?\/\/(' . $allowedDomainsRegex . ')\/[^"\']*["\']?/i', $linkTag)) {
                $fail('Hanya link dari sumber terpercaya yang diizinkan.');
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
