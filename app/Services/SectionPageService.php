<?php

namespace App\Services;

class SectionPageService
{
    public function __construct(
        protected array $sections = [],
        protected array $result = [],
        private string $pathJSON = 'config\\sections.json'
    ) {}

    public function getFile()
    {
        return file_get_contents(base_path($this->pathJSON));
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

            file_put_contents(base_path($this->pathJSON), json_encode($getSections->toArray(), JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
