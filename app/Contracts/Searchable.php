<?php

namespace App\Contracts;

interface Searchable
{
    public function search(string $query, array $filters = []): iterable;
}
