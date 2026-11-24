<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** Essential data only: countries, scoring criteria, settings, first super admin. No packages or prices are invented. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([CountrySeeder::class, EssentialSeeder::class]);
    }
}
