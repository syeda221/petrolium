<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixProductItemCodesSeeder extends Seeder
{
    /**
     * Run the database seeds to standardize all item codes to ITEM-XXXX format
     */
    public function run(): void
    {
        echo "\n🔄 Standardizing all product item codes to ITEM-XXXX format...\n\n";

        // 🔹 Get all products ordered by ID
        $products = DB::table('products')
            ->orderBy('id')
            ->get();

        $updated = 0;

        // 🔹 Assign consistent ITEM-XXXX format based on product ID
        foreach ($products as $product) {
            $itemCode = 'ITEM-' . str_pad($product->id, 4, '0', STR_PAD_LEFT);
            
            DB::table('products')
                ->where('id', $product->id)
                ->update(['item_code' => $itemCode]);
            
            echo "✅ ID {$product->id}\t→ {$itemCode}\n";
            $updated++;
        }

        echo "\n🎉 Successfully updated {$updated} products with consistent ITEM-XXXX format!\n";
    }
}
