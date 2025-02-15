<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NewsRepository
{
    public function store(array $data): void;
    public function getFiltered(array $filters): LengthAwarePaginator;
}
