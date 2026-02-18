<?php

namespace App\Services\FeedGenerators;

use Illuminate\Support\Collection;

interface FeedGenerator
{
    public function generate(Collection $products): string;
}