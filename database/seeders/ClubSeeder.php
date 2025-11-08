<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('clubs')->insert([
            [
                'name' => 'Chess Club',
                'description' => 'A club for chess enthusiasts to play and learn.',
                'owner_id' => 1,
                'avatar_url' => 'https://example.com/default-avatar.png',
                'invite_code' => Str::upper(Str::random(6)), // ✅ Mã mời ngẫu nhiên
                'is_public' => true,
            ],
            [
                'name' => 'Robotics Club',
                'description' => 'Building and programming robots for competitions.',
                'owner_id' => 1,
                'avatar_url' => 'https://example.com/default-avatar.png',
                'invite_code' => Str::upper(Str::random(6)),
                'is_public' => true,
            ],
            [
                'name' => 'Photography Club',
                'description' => 'Exploring the art of photography through outings and workshops.',
                'owner_id' => 1,
                'avatar_url' => 'https://example.com/default-avatar.png',
                'invite_code' => Str::upper(Str::random(6)),
                'is_public' => false,
            ],
        ]);

    }
}
