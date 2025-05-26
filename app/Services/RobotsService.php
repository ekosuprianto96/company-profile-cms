<?php

namespace App\Services;

class RobotsService
{
    public function __construct(
        protected $robots = null
    ) {
        $this->getRobot();
    }

    public function read()
    {
        return $this->robots;
    }

    public function update($robots)
    {
        file_put_contents(public_path('robots.txt'), $robots);
        return true;
    }

    protected function getRobot(): self
    {
        $getFile = file_get_contents(public_path('robots.txt'));
        $this->robots = $getFile;
        return $this;
    }
}
