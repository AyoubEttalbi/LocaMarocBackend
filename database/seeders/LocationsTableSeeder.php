<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'city' => 'Casablanca',
                'address' => 'Avenue Mohammed V, Casablanca',
                'latitude' => 33.5899,
                'longitude' => -7.6033,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'city' => 'Marrakech',
                'address' => 'Pl. de la Liberté, Marrakech',
                'latitude' => 31.6295,
                'longitude' => -7.9811,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'city' => 'Rabat',
                'address' => 'Boulevard Mohammed V, Rabat',
                'latitude' => 34.0184,
                'longitude' => -6.8426,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'city' => 'Fes',
                'address' => 'Place R\'cif, Fes',
                'latitude' => 33.9417,
                'longitude' => -5.0000,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'city' => 'Tangier',
                'address' => 'Place de la Marine, Tangier',
                'latitude' => 35.7553,
                'longitude' => -5.8333,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'city' => 'Agadir',
                'address' => 'Pl. Hassan II, Agadir',
                'latitude' => 30.4395,
                'longitude' => -9.5827,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('locations')->insert($locations);
    }
}
