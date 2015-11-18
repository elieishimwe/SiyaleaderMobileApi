<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Category;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call(UserTableSeeder::class);

        Model::reguard();

    # =========================================================================
    # CATEGORIES SEEDS
    # =========================================================================

        DB::table('categories')->delete();

         $categories = [
                [   'name'          => 'Wildlife',
                    'created_at'    =>\Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString()
                ],
                [   'name'          => 'Pollution',
                    'created_at'    =>\Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString()
                ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
