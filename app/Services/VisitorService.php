<?php

namespace App\Services;

use App\Repositories\VisitorRepository;

class VisitorService
{
    public function __construct(
        private VisitorRepository $visitor
    ) {}

    public function createVisitor(array $param = [])
    {
        return $this->visitor->create($param);
    }

    public function getVisitorByIp($ip)
    {
        return $this->visitor->getVisitorByIp($ip);
    }

    public function getAllVisitor()
    {
        return $this->visitor->getAllVisitor();
    }
}
