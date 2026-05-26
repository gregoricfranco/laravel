<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        News::factory()->published()->count(8)->create();
        News::factory()->draft()->count(4)->create();
    }
}
