<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    public function __construct(
        protected $sitemap = null,
        protected $sitemapPath = null,
        protected array $models = [],
        protected array $sites = []
    ) {
        $this->sitemapPath = public_path('sitemap.xml');
    }

    public function addModel(callable $route, float $priority = 0.8, $modelClass = null)
    {
        $this->models[] = [
            'route' => $route,
            'class' => app($modelClass),
            'priority' => $priority
        ];

        return $this;
    }

    public function addSite(string $url, float $priority = 0.8)
    {
        $this->sites[] = [
            'url' => $url,
            'priority' => $priority,
        ];

        return $this;
    }

    public function getFile()
    {
        $this->existsFile();
        return $this->sitemapPath;
    }

    public function getURL()
    {
        $xml = simplexml_load_file($this->getFile());

        // Ambil semua URL dari sitemap
        $urls = [];
        foreach ($xml->url as $url) {
            $urls[] = (string) $url->loc; // Ambil tag <loc> untuk URL
        }

        return $urls;
    }

    public function getSitemapInfo()
    {
        $xml = simplexml_load_file($this->getFile());

        // Ambil semua URL dan lastmod
        $sitemapData = [];
        foreach ($xml->url as $url) {
            $sitemapData[] = [
                'loc'     => (string) $url->loc, // URL
                'lastmod' => isset($url->lastmod) ? (string) $url->lastmod : 'Tidak tersedia', // Last Modified (jika ada)
            ];
        }

        return $sitemapData;
    }

    public function lastModified()
    {
        // Ambil waktu modifikasi file
        $lastModified = File::lastModified($this->getFile());

        // Format waktu ke human-readable
        $formattedDate = Carbon::parse($lastModified, 'Asia/Jakarta')->format('d F Y');

        return $formattedDate;
    }

    public function existsFile()
    {
        // Pastikan file ada
        if (!File::exists($this->sitemapPath)) {
            throw new \Exception('Sitemap tidak ditemukan!', 404);
        }
    }

    public function generate()
    {
        $sitemap = Sitemap::create();

        if (count($this->sites) > 0) {
            foreach ($this->sites as $site) {
                $sitemap->add(
                    Url::create($site['url'])
                        ->setLastModificationDate(now())
                        ->setPriority($site['priority'])
                );
            }
        }

        if (count($this->models) > 0) {
            foreach ($this->models as $model) {
                foreach ($model['class']->where('an', 1)->get() as $value) {
                    $sitemap->add(
                        Url::create($model['route']($value))
                            ->setLastModificationDate(Carbon::create($value->updated_at))
                            ->setPriority($model['priority'])
                    );
                }
            }
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        return $this;
    }

    public function appendStyleSheet()
    {
        if (File::exists($this->getFile())) {
            $sitemapContent = File::get($this->getFile());
            $xslHeader = '<?xml-stylesheet type="text/xsl" href="' . config('app.url') . '/sitemap.xsl"?>' . "\n";

            // Masukkan setelah deklarasi XML
            $sitemapContent = preg_replace('/(<\?xml[^>]+\?>)/', "$1\n$xslHeader", $sitemapContent, 1);

            // Simpan kembali sitemap.xml
            File::put($this->getFile(), $sitemapContent);
        }

        return $this;
    }
}
