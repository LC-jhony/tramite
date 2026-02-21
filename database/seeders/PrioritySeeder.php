<?php

namespace Database\Seeders;

use App\Models\Priority;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            [
                'name' => 'Alta',
                'color' => 'red',
                'days' => 1,
                'status' => true,
            ],
            [
                'name' => 'Media',
                'color' => 'orange',
                'days' => 5,
                'status' => true,
            ],
            [
                'name' => 'Baja',
                'color' => 'green',
                'days' => 15,
                'status' => true,
            ],
        ];

        foreach ($priorities as $priority) {
            Priority::create($priority);
        }
    }
}
