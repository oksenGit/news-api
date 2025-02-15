<?php

namespace App\Contracts;

interface NewsSource
{
    public function fetch(string $fromDate): array;
}
