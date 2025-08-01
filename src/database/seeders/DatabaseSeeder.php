<?php

namespace Database\Seeders;

use App\Http\Controllers\AttendanceController;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            AttendanceSeeder::class,
            AttendanceCorrectionListSeeder::class,
        ]);
    }
}
