<?php

namespace App\Services;

class HomePageService
{
    public function __construct(
        protected array $blocks = []
    ) {
        $this->blocks = config('blocks', []);
    }

    public function getBlocks()
    {
        return $this->blocks;
    }
}
