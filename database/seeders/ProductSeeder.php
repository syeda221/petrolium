<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds - Load all 56 products with ITEM-XXXX format
     */
    public function run(): void
    {
        // 🔹 Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // 🔹 Clear existing records
        DB::table('products')->truncate();
        
        // 🔹 Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $products = [
            ['id' => 1, 'creater_id' => 7, 'category_id' => 2, 'item_code' => 'ITEM-0001', 'unit_id' => 'Katta', 'item_name' => 'Namak chota', 'price' => 180, 'alert_quantity' => 5, 'wholesale_price' => 140],
            ['id' => 2, 'creater_id' => 7, 'category_id' => 2, 'item_code' => 'ITEM-0002', 'unit_id' => 'Katta', 'item_name' => 'namak bara', 'price' => 280, 'alert_quantity' => 5, 'wholesale_price' => 240],
            ['id' => 3, 'creater_id' => 7, 'category_id' => 16, 'item_code' => 'ITEM-0003', 'unit_id' => 'CTN', 'item_name' => 'Anum 10 kg', 'price' => 3020, 'alert_quantity' => 5, 'wholesale_price' => 0],
            ['id' => 4, 'creater_id' => 7, 'category_id' => 16, 'item_code' => 'ITEM-0004', 'unit_id' => 'CTN', 'item_name' => 'Anum 6 kg', 'price' => 1750, 'alert_quantity' => 10, 'wholesale_price' => 1650],
            ['id' => 5, 'creater_id' => 7, 'category_id' => 16, 'item_code' => 'ITEM-0005', 'unit_id' => 'CTN', 'item_name' => 'anum 8 kg', 'price' => 2350, 'alert_quantity' => 2, 'wholesale_price' => 2280],
            ['id' => 6, 'creater_id' => 7, 'category_id' => 17, 'item_code' => 'ITEM-0006', 'unit_id' => 'KG', 'item_name' => 'bajra filter punjab', 'price' => 93, 'alert_quantity' => 150, 'wholesale_price' => 90],
            ['id' => 7, 'creater_id' => 7, 'category_id' => 18, 'item_code' => 'ITEM-0007', 'unit_id' => 'KG', 'item_name' => 'Channa daal', 'price' => 210, 'alert_quantity' => 250, 'wholesale_price' => 190],
            ['id' => 8, 'creater_id' => 7, 'category_id' => 18, 'item_code' => 'ITEM-0008', 'unit_id' => 'KG', 'item_name' => 'daal masoor nugget', 'price' => 220, 'alert_quantity' => 125, 'wholesale_price' => 195],
            ['id' => 9, 'creater_id' => 7, 'category_id' => 18, 'item_code' => 'ITEM-0009', 'unit_id' => 'KG', 'item_name' => 'daal masoor crimson', 'price' => 210, 'alert_quantity' => 125, 'wholesale_price' => 185],
            ['id' => 10, 'creater_id' => 7, 'category_id' => 18, 'item_code' => 'ITEM-0010', 'unit_id' => 'KG', 'item_name' => 'mong dal polish choti', 'price' => 215, 'alert_quantity' => 250, 'wholesale_price' => 190],
            ['id' => 11, 'creater_id' => 7, 'category_id' => 18, 'item_code' => 'ITEM-0011', 'unit_id' => 'KG', 'item_name' => 'mong dal kori bari', 'price' => 355, 'alert_quantity' => 50, 'wholesale_price' => 330],
            ['id' => 12, 'creater_id' => 7, 'category_id' => 3, 'item_code' => 'ITEM-0012', 'unit_id' => 'Katta', 'item_name' => 'cheeni', 'price' => 6850, 'alert_quantity' => 10, 'wholesale_price' => 6800],
            ['id' => 13, 'creater_id' => 7, 'category_id' => 24, 'item_code' => 'ITEM-0013', 'unit_id' => 'Katta', 'item_name' => 'jao filter 40kg', 'price' => 4400, 'alert_quantity' => 2, 'wholesale_price' => 4200],
            ['id' => 14, 'creater_id' => 7, 'category_id' => 15, 'item_code' => 'ITEM-0014', 'unit_id' => 'Katta', 'item_name' => 'bhutta chawal ata 40 kg', 'price' => 3750, 'alert_quantity' => 5, 'wholesale_price' => 3650],
            ['id' => 15, 'creater_id' => 7, 'category_id' => 15, 'item_code' => 'ITEM-0015', 'unit_id' => 'Katta', 'item_name' => 'PAKISTAN CHAWAL ATA 40 kg', 'price' => 3700, 'alert_quantity' => 3, 'wholesale_price' => 3600],
            ['id' => 16, 'creater_id' => 7, 'category_id' => 23, 'item_code' => 'ITEM-0016', 'unit_id' => 'Katta', 'item_name' => 'maida 50 kg', 'price' => 0, 'alert_quantity' => 2, 'wholesale_price' => 0],
            ['id' => 17, 'creater_id' => 7, 'category_id' => 22, 'item_code' => 'ITEM-0017', 'unit_id' => 'Katta', 'item_name' => 'Sharukh besan 37 kg', 'price' => 5100, 'alert_quantity' => 5, 'wholesale_price' => 5000],
            ['id' => 18, 'creater_id' => 7, 'category_id' => 22, 'item_code' => 'ITEM-0018', 'unit_id' => 'Katta', 'item_name' => 'mama besan 37 kg', 'price' => 4900, 'alert_quantity' => 3, 'wholesale_price' => 4750],
            ['id' => 19, 'creater_id' => 7, 'category_id' => 21, 'item_code' => 'ITEM-0019', 'unit_id' => 'Katta', 'item_name' => 'kati 40 kg', 'price' => 950, 'alert_quantity' => 40, 'wholesale_price' => 900],
            ['id' => 20, 'creater_id' => 7, 'category_id' => 20, 'item_code' => 'ITEM-0020', 'unit_id' => 'Katta', 'item_name' => 'master b10 feed 37 kg', 'price' => 3850, 'alert_quantity' => 10, 'wholesale_price' => 3700],
            ['id' => 21, 'creater_id' => 7, 'category_id' => 20, 'item_code' => 'ITEM-0021', 'unit_id' => 'Katta', 'item_name' => 'master b7 feed', 'price' => 3250, 'alert_quantity' => 5, 'wholesale_price' => 3100],
            ['id' => 22, 'creater_id' => 7, 'category_id' => 20, 'item_code' => 'ITEM-0022', 'unit_id' => 'Katta', 'item_name' => 'Jatoi feed', 'price' => 3550, 'alert_quantity' => 10, 'wholesale_price' => 3400],
            ['id' => 23, 'creater_id' => 7, 'category_id' => 19, 'item_code' => 'ITEM-0023', 'unit_id' => 'KG', 'item_name' => 'gur 25 kgs', 'price' => 175, 'alert_quantity' => 125, 'wholesale_price' => 155],
            ['id' => 24, 'creater_id' => 7, 'category_id' => 16, 'item_code' => 'ITEM-0024', 'unit_id' => 'CTN', 'item_name' => 'pam 36', 'price' => 3100, 'alert_quantity' => 3, 'wholesale_price' => 3000],
            ['id' => 25, 'creater_id' => 7, 'category_id' => 14, 'item_code' => 'ITEM-0025', 'unit_id' => 'CTN', 'item_name' => 'Sun 16 LTR', 'price' => 8500, 'alert_quantity' => 2, 'wholesale_price' => 8400],
            ['id' => 26, 'creater_id' => 7, 'category_id' => 14, 'item_code' => 'ITEM-0026', 'unit_id' => 'KG', 'item_name' => 'Naz 16 LTR', 'price' => 0, 'alert_quantity' => 0, 'wholesale_price' => 0],
            ['id' => 27, 'creater_id' => 7, 'category_id' => 13, 'item_code' => 'ITEM-0027', 'unit_id' => 'CTN', 'item_name' => 'Sun 2.5 kg', 'price' => 1285, 'alert_quantity' => 18, 'wholesale_price' => 1270],
            ['id' => 28, 'creater_id' => 7, 'category_id' => 13, 'item_code' => 'ITEM-0028', 'unit_id' => 'CTN', 'item_name' => 'naz 2.5 kg', 'price' => 1390, 'alert_quantity' => 12, 'wholesale_price' => 1370],
            ['id' => 29, 'creater_id' => 7, 'category_id' => 13, 'item_code' => 'ITEM-0029', 'unit_id' => 'CTN', 'item_name' => 'Parcham dabi', 'price' => 1000, 'alert_quantity' => 6, 'wholesale_price' => 950],
            ['id' => 30, 'creater_id' => 7, 'category_id' => 12, 'item_code' => 'ITEM-0030', 'unit_id' => 'CTN', 'item_name' => 'Pak 900 ml', 'price' => 2400, 'alert_quantity' => 10, 'wholesale_price' => 2350],
            ['id' => 31, 'creater_id' => 7, 'category_id' => 12, 'item_code' => 'ITEM-0031', 'unit_id' => 'CTN', 'item_name' => 'Tohfa 900 ml', 'price' => 2300, 'alert_quantity' => 5, 'wholesale_price' => 2200],
            ['id' => 32, 'creater_id' => 7, 'category_id' => 11, 'item_code' => 'ITEM-0032', 'unit_id' => 'KG', 'item_name' => 'Khyber 170gm', 'price' => 3750, 'alert_quantity' => 10, 'wholesale_price' => 3700],
            ['id' => 33, 'creater_id' => 7, 'category_id' => 11, 'item_code' => 'ITEM-0033', 'unit_id' => 'KG', 'item_name' => 'KHYBER 340 GM', 'price' => 3750, 'alert_quantity' => 10, 'wholesale_price' => 3700],
            ['id' => 34, 'creater_id' => 7, 'category_id' => 11, 'item_code' => 'ITEM-0034', 'unit_id' => 'CTN', 'item_name' => 'KHYBER 680 GM', 'price' => 3750, 'alert_quantity' => 10, 'wholesale_price' => 3700],
            ['id' => 35, 'creater_id' => 7, 'category_id' => 10, 'item_code' => 'ITEM-0035', 'unit_id' => 'Katta', 'item_name' => 'gandum daliya 40 kg', 'price' => 3900, 'alert_quantity' => 5, 'wholesale_price' => 3800],
            ['id' => 36, 'creater_id' => 7, 'category_id' => 10, 'item_code' => 'ITEM-0036', 'unit_id' => 'Katta', 'item_name' => 'gandum daliya 35 kg', 'price' => 3400, 'alert_quantity' => 10, 'wholesale_price' => 3325],
            ['id' => 37, 'creater_id' => 7, 'category_id' => 10, 'item_code' => 'ITEM-0037', 'unit_id' => 'Katta', 'item_name' => 'makai daliyaa', 'price' => 0, 'alert_quantity' => 0, 'wholesale_price' => 0],
            ['id' => 38, 'creater_id' => 7, 'category_id' => 9, 'item_code' => 'ITEM-0038', 'unit_id' => 'Katta', 'item_name' => 'Silky choona 35 kg', 'price' => 0, 'alert_quantity' => 5, 'wholesale_price' => 0],
            ['id' => 39, 'creater_id' => 7, 'category_id' => 7, 'item_code' => 'ITEM-0039', 'unit_id' => 'Katta', 'item_name' => 'papdi', 'price' => 0, 'alert_quantity' => 0, 'wholesale_price' => 0],
            ['id' => 40, 'creater_id' => 7, 'category_id' => 6, 'item_code' => 'ITEM-0040', 'unit_id' => 'Katta', 'item_name' => 'Khal 34 kg', 'price' => 3200, 'alert_quantity' => 100, 'wholesale_price' => 3100],
            ['id' => 41, 'creater_id' => 7, 'category_id' => 5, 'item_code' => 'ITEM-0041', 'unit_id' => 'Katta', 'item_name' => 'Ataa 40 kg sada', 'price' => 3700, 'alert_quantity' => 30, 'wholesale_price' => 3600],
            ['id' => 42, 'creater_id' => 7, 'category_id' => 4, 'item_code' => 'ITEM-0042', 'unit_id' => 'Katta', 'item_name' => 'Ataa 40 kg', 'price' => 3900, 'alert_quantity' => 30, 'wholesale_price' => 3750],
            ['id' => 43, 'creater_id' => 7, 'category_id' => 1, 'item_code' => 'ITEM-0043', 'unit_id' => 'KG', 'item_name' => 'gulab naaz sella', 'price' => 320, 'alert_quantity' => 500, 'wholesale_price' => 310],
            ['id' => 44, 'creater_id' => 7, 'category_id' => 1, 'item_code' => 'ITEM-0044', 'unit_id' => 'KG', 'item_name' => 'south punjab sella', 'price' => 290, 'alert_quantity' => 375, 'wholesale_price' => 278],
            ['id' => 45, 'creater_id' => 7, 'category_id' => 1, 'item_code' => 'ITEM-0045', 'unit_id' => 'KG', 'item_name' => 'NR sella', 'price' => 0, 'alert_quantity' => 250, 'wholesale_price' => 0],
            ['id' => 46, 'creater_id' => 7, 'category_id' => 1, 'item_code' => 'ITEM-0046', 'unit_id' => 'KG', 'item_name' => 'R.T', 'price' => 130, 'alert_quantity' => 300, 'wholesale_price' => 127],
            ['id' => 47, 'creater_id' => 7, 'category_id' => 1, 'item_code' => 'ITEM-0047', 'unit_id' => 'KG', 'item_name' => 'chambeli', 'price' => 135, 'alert_quantity' => 100, 'wholesale_price' => 127],
            ['id' => 48, 'creater_id' => 7, 'category_id' => 1, 'item_code' => 'ITEM-0048', 'unit_id' => 'KG', 'item_name' => 'b/2 aeri', 'price' => 88, 'alert_quantity' => 500, 'wholesale_price' => 85],
            ['id' => 49, 'creater_id' => 7, 'category_id' => 1, 'item_code' => 'ITEM-0049', 'unit_id' => 'KG', 'item_name' => 'Dawat', 'price' => 210, 'alert_quantity' => 500, 'wholesale_price' => 205],
            ['id' => 50, 'creater_id' => 7, 'category_id' => 1, 'item_code' => 'ITEM-0050', 'unit_id' => 'KG', 'item_name' => 'AN sella', 'price' => 320, 'alert_quantity' => 150, 'wholesale_price' => 305],
            ['id' => 51, 'creater_id' => 7, 'category_id' => 1, 'item_code' => 'ITEM-0051', 'unit_id' => 'KG', 'item_name' => 'zarafa tota', 'price' => 115, 'alert_quantity' => 150, 'wholesale_price' => 110],
            ['id' => 52, 'creater_id' => 7, 'category_id' => 1, 'item_code' => 'ITEM-0052', 'unit_id' => 'KG', 'item_name' => 'gold dawat', 'price' => 140, 'alert_quantity' => 250, 'wholesale_price' => 125],
            ['id' => 53, 'creater_id' => 7, 'category_id' => 1, 'item_code' => 'ITEM-0053', 'unit_id' => 'KG', 'item_name' => 'WHITE PARI', 'price' => 0, 'alert_quantity' => 250, 'wholesale_price' => 0],
            ['id' => 54, 'creater_id' => 7, 'category_id' => 1, 'item_code' => 'ITEM-0054', 'unit_id' => 'Katta', 'item_name' => 'xyz', 'price' => 0, 'alert_quantity' => 10, 'wholesale_price' => 0],
            ['id' => 55, 'creater_id' => 7, 'category_id' => 12, 'item_code' => 'ITEM-0055', 'unit_id' => 'KG', 'item_name' => 'sdfghj', 'price' => 0, 'alert_quantity' => 0, 'wholesale_price' => 11],
            ['id' => 56, 'creater_id' => 7, 'category_id' => 13, 'item_code' => 'ITEM-0056', 'unit_id' => 'KG', 'item_name' => '12345', 'price' => 0, 'alert_quantity' => 0, 'wholesale_price' => 0],
        ];

        // 🔹 Insert all products
        foreach ($products as $product) {
            DB::table('products')->insert(array_merge($product, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        echo "\n✅ ProductSeeder: Successfully seeded 56 products with ITEM-XXXX format!\n";
    }
}

