<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $faker = Faker::create();
        // $this->call(UsersTableSeeder::class);
        $id = \DB::table('users')->insertGetId(array(

            'name' => $faker->name,
            'email' => 'jmgarciacarrasco@gmail.com',
            'password' => \Hash::make('123456'),
            'phone' => $faker->phoneNumber));



        for ($j = 0;$j < 50; $j ++) {
            \DB::table('photos')->insert(array(
                'user_id' => $id,
                'data' => $faker->text(4000)
            ));
        }

    }
}
