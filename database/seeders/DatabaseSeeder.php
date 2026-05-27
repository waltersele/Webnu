<?php

namespace Database\Seeders;

use App\Allergen;
use App\Company;
use App\Product;
use App\Section;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('users')->delete();
        $users = [
            ['name' => 'Webnu', 'email' => 'info@webnu.es', 'password' => Hash::make('123456')],
        ];
        foreach ($users as $user) {
            User::create($user);
        }

        DB::table('companies')->delete();
        $companies = [
            ['name' => 'Los casanueva', 'chef_name' => 'Dani', 'slug' => 'los-casanueva', 'address' => 'Avda. L\'almassera', 'postal_code' => '03690', 'city' => 'San Vicente del Raspeig', 'province' => 'Alicante', 'country' => 'España', 'phone' => '966307664', 'mobile_phone' => '627797373', 'email' => 'ruben@winamic.es', 'web' => 'https://www.mesonloscasanueva.com/', 'whatsapp' => '627797373', 'menu_type' => 1, 'enabled' => true, 'user_id' => 1, 'reservation' => true],
        ];
        foreach ($companies as $company) {
            Company::create($company);
        }

        DB::table('sections')->delete();
        $sections = [
            ['name' => 'Entrantes', 'order' => 0, 'enabled' => 1, 'company_id' => 1],
            ['name' => 'Para compartir', 'order' => 1, 'enabled' => 1, 'company_id' => 1],
            ['name' => 'Carnes', 'order' => 2, 'enabled' => 1, 'company_id' => 1],
            ['name' => 'Pescados', 'order' => 3, 'enabled' => 1, 'company_id' => 1],
            ['name' => 'Postres', 'order' => 4, 'enabled' => 1, 'company_id' => 1],
        ];
        foreach ($sections as $section) {
            Section::create($section);
        }

        DB::table('allergens')->delete();
        $allergens = [
            ['name' => 'Gluten', 'image' => 'alergenos/gluten.svg'],
            ['name' => 'Frutos secos', 'image' => 'alergenos/frutos-secos.svg'],
            ['name' => 'Crustáceos', 'image' => 'alergenos/crustaceos.svg'],
            ['name' => 'Pescados', 'image' => 'alergenos/pescados.svg'],
            ['name' => 'Lácteos', 'image' => 'alergenos/lacteos.svg'],
            ['name' => 'Moluscos', 'image' => 'alergenos/moluscos.svg'],
            ['name' => 'Huevos', 'image' => 'alergenos/huevos.svg'],
            ['name' => 'Cacahuetes', 'image' => 'alergenos/cacahuetes.svg'],
            ['name' => 'Soja', 'image' => 'alergenos/soja.svg'],
            ['name' => 'Apio', 'image' => 'alergenos/apio.svg'],
            ['name' => 'Mostaza', 'image' => 'alergenos/mostaza.svg'],
            ['name' => 'Sésamo', 'image' => 'alergenos/sesamo.svg'],
            ['name' => 'Altramuz', 'image' => 'alergenos/altramuz.svg'],
            ['name' => 'Sulfitos', 'image' => 'alergenos/sulfitos.svg'],
        ];
        foreach ($allergens as $allergen) {
            Allergen::create($allergen);
        }

        DB::table('products')->delete();
        $products = [
            ['name' => 'Ensalada de la casa', 'description' => '', 'price_unit' => '4.50', 'individual_sale' => 1, 'image' => '', 'section_id' => 1, 'order' => 0, 'enabled' => 1],
            ['name' => 'Ensalada de pepino', 'description' => '', 'price_unit' => '3.70', 'individual_sale' => 1, 'image' => '', 'section_id' => 1, 'order' => 1, 'enabled' => 1],
            ['name' => 'Croquetas', 'description' => '', 'price_unit' => '1.20', 'individual_sale' => 1, 'image' => '', 'section_id' => 1, 'order' => 2, 'enabled' => 1],
            ['name' => 'Arroz con pulpo', 'description' => 'Arroz meloso con nuestro toque especial', 'price_unit' => '8.00', 'individual_sale' => 0, 'image' => '', 'section_id' => 2, 'order' => 3, 'enabled' => 1],
            ['name' => 'Solomillo de cerdo', 'description' => '', 'price_unit' => '10.00', 'individual_sale' => 0, 'image' => '', 'section_id' => 2, 'order' => 4, 'enabled' => 1],
        ];
        foreach ($products as $product) {
            Product::create($product);
        }

        Schema::enableForeignKeyConstraints();
    }
}
