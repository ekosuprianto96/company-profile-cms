<?php

namespace App\Services;

class SectionPageService
{
    public function __construct(
        protected array $sections = [],
        protected array $result = [],
        private string $pathJSON = 'config/sections.json'
    ) {}

    public function getFile()
    {
        return app(\App\Services\PageConfigStore::class)->sectionsJsonString();
    }

    public function decode(bool $assoc = false)
    {
        return json_decode($this->getFile(), $assoc);
    }

    public function get()
    {
        $this->result = array_filter($this->decode(true), function ($item) {
            return in_array($item['id'], $this->sections);
        });

        return array_values($this->result);
    }

    public function save(array $sections)
    {
        try {

            $getSections = collect($this->decode(true));

            foreach ($sections as $key => $value) {
                $getSections->transform(function ($item, $key) use ($value) {
                    if ($item['id'] === $value['id']) {
                        $item = $value;
                    }

                    return $item;
                });
            }

            app(\App\Services\PageConfigStore::class)->writeSections($getSections->toArray());
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
