<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClubCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('club_categories')->insert([
            ['name' => 'Sports'],
            ['name' => 'Arts'],
            ['name' => 'Technology'],
            ['name' => 'Music'],
            ['name' => 'Literature'],
        ]);
    }
  
}
