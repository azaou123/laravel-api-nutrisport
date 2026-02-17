<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Site;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        Site::insert([
            ['domain' => 'nutri-sport.fr', 'name' => 'France'],
            ['domain' => 'nutri-sport.it', 'name' => 'Italy'],
            ['domain' => 'nutri-sport.be', 'name' => 'Belgium'],
        ]);
    }
}

