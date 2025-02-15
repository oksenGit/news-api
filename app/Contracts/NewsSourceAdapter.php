<?php

namespace App\Contracts;


interface NewsSourceAdapter
{
    public function parse(array $data): array;
}