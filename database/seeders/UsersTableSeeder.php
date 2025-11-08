<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'username' => 'quangnguyen',
                'hashedPassword' => password_hash('123456', PASSWORD_DEFAULT),
                'email' => 'quang@example.com',
                'displayName' => 'Quang Nguyễn',
                'avatarUrl' => 'https://i.pravatar.cc/150?img=1',
                'avatarId' => 'A1',
                'bio' => 'Lập trình viên ReactJS & Lumen.',
                'phone' => '0901234567',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'trananh',
                'hashedPassword' => password_hash('abcdef', PASSWORD_DEFAULT),
                'email' => 'trananh@example.com',
                'displayName' => 'Trần Anh',
                'avatarUrl' => 'https://i.pravatar.cc/150?img=2',
                'avatarId' => 'A2',
                'bio' => 'Yêu thích công nghệ backend.',
                'phone' => '0907654321',
                'created_at' => \Illuminate\Support\Carbon::now(),
                'updated_at' => \Illuminate\Support\Carbon::now(),
            ],
        ]);
    }
}
