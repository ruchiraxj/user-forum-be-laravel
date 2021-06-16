<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertRolesAndUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert user roles
        DB::table('products')->insert(
            [
                [
                    'id' => 1,
                    'name' => 'Product 1',
                    'image' => 'https://via.placeholder.com/200/09ffff'
                ],
                [
                    'id' => 2,
                    'name' => 'Product 2',
                    'image' => 'https://via.placeholder.com/200/982178'
                ],
                
            ]
        );

        // Insert user roles
        DB::table('roles')->insert(
            [
                [
                    'id' => 1,
                    'name' => 'Administrator'
                ],
                [
                    'id' => 2,
                    'name' => 'User'
                ]
            ]
        );

        // Insert user roles
        DB::table('users')->insert(
            [
                [
                    'id' => 1,
                    'name' => 'Admin',
                    'email' => 'admin@localhost',
                    'password' => bcrypt('1234'),
                    'role_id' => 1
                ]                
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('users')->truncate();
        DB::table('roles')->truncate();
        DB::table('products')->truncate();
    }
}
