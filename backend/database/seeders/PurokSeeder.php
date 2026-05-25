<?php

namespace Database\Seeders;

use App\Models\Purok;
use Illuminate\Database\Seeder;

class PurokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $puroks = [
            [
                'purok_name' => 'Purok 1',
                'zone' => 'Zone A',
                'description' => 'Covering the northern residential boundary.',
            ],
            [
                'purok_name' => 'Purok 2',
                'zone' => 'Zone B',
                'description' => 'Covering the central marketplace and business district.',
            ],
            [
                'purok_name' => 'Purok 3',
                'zone' => 'Zone C',
                'description' => 'Covering the western farming agricultural region.',
            ],
            [
                'purok_name' => 'Purok 4',
                'zone' => 'Zone D',
                'description' => 'Covering the southern riverfront area.',
            ],
            [
                'purok_name' => 'Purok 5',
                'zone' => 'Zone E',
                'description' => 'Covering the eastern high-density housing blocks.',
            ],
        ];

        foreach ($puroks as $purok) {
            Purok::firstOrCreate(['purok_name' => $purok['purok_name']], $purok);
        }
    }
}
