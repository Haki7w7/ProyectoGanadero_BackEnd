<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Potrero;
use App\Models\Raza;
use Illuminate\Database\Seeder;

class AnimalSeeder extends Seeder
{
    public function run(): void
    {
        $potreroId = Potrero::first()->potrero_id;
        $razaId = Raza::first()->raza_id;

        Animal::factory(20)->create([
            'potrero_id' => $potreroId,
            'raza_id'    => $razaId,
        ]);
    }
}