<?php

namespace Database\Seeders;

use App\Models\Priority;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priorities = [
            [
                'name' => 'Alta',
                'color' => '#EF4444',
                'days' => 1,
                'status' => true,
            ],
            [
                'name' => 'Media',
                'color' => '#F97316',
                'days' => 5,
                'status' => true,
            ],
            [
                'name' => 'Baja',
                'color' => '#22C55E',
                'days' => 15,
                'status' => true,
            ],
        ];

        foreach ($priorities as $priority) {
            Priority::create($priority);
        }
    }
}
