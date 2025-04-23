<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("SET foreign_key_checks=0");
        $databaseName = DB::getDatabaseName();
        $tables = DB::select("SELECT * FROM information_schema.tables WHERE table_schema = '$databaseName'");
        foreach ($tables as $table) {
           $name = $table->TABLE_NAME;
            // if you don't want to truncate migrations
            if ($name == 'migrations' || $name == 'settings' || $name == 'admins' || $name == 'rooms') {
              continue;
            }
           DB::table($name)->truncate();
        }
        DB::statement("SET foreign_key_checks=1");
        User::factory(200)->create();

        $this->call(PermissionSeeder::class);

    }
}
