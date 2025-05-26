<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class StylesConfig
{

    public function __construct(
        private array $config = [],
        public string $name = '',
        private string $result = ''
    ) {
        $this->__init();
    }

    public function __init(): void
    {
        $this->config = config('styles');
    }

    public function variant(string $type = ''): self
    {
        $this->result .= ' ' . $this->config[$this->name]['variant'][$type];
        return $this;
    }

    public function size(string $type = ''): self
    {
        $this->result .=  ' ' . $this->config[$this->name]['size'][$type];
        return $this;
    }

    public function rounded(string $type = ''): self
    {
        $this->result .=  ' ' . $this->config[$this->name]['rounded'][$type];
        return $this;
    }

    public function style(string $type = ''): self
    {
        $this->result .=  ' ' . $this->config[$this->name]['style'][$type];
        return $this;
    }

    public function get(): string
    {
        return $this->result;
    }
}
