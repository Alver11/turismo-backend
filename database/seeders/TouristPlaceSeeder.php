<?php

namespace Database\Seeders;

use App\Models\TouristPlace;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TouristPlaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Lugar turístico 1',
                'address' => 'Dirección 1',
                'district_id' => 1,
                'description' => 'Descripción del lugar turístico 1',
                'lng' => -70.12345,
                'lat' => -15.12345,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Lugar turístico 2',
                'address' => 'Dirección 2',
                'district_id' => 2,
                'description' => 'Descripción del lugar turístico 2',
                'lng' => -70.67890,
                'lat' => -15.67890,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Lugar turístico 3',
                'address' => 'Dirección 3',
                'district_id' => 3,
                'description' => 'Descripción del lugar turístico 3',
                'lng' => -70.54321,
                'lat' => -15.54321,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]
        ];
        TouristPlace::insert($data);
    }
}
