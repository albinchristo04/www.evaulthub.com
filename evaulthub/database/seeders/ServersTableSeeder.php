<?php

namespace Database\Seeders;

use App\Models\Server;
use Illuminate\Database\Seeder;

class ServersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servers = [
            [
                'name' => 'Server 1',
                'json_url' => 'https://raw.githubusercontent.com/albinchristo04/ptv/refs/heads/main/futbollibre.json',
                'is_active' => true,
            ],
            [
                'name' => 'Server 2',
                'json_url' => 'https://raw.githubusercontent.com/albinchristo04/ptv/refs/heads/main/events_with_m3u8.json',
                'is_active' => true,
            ],
        ];

        foreach ($servers as $server) {
            Server::query()->updateOrCreate(
                ['name' => $server['name']],
                $server
            );
        }
    }
}
