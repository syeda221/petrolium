<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class GenerateProductCodes extends Command
{
    protected $signature = 'product:generate-codes';

    protected $description = 'Generate item codes for products that don\'t have one';

    public function handle()
    {
        $this->info('🔄 Generating item codes for products without codes...');

        $productsWithoutCodes = Product::whereNull('item_code')
            ->orWhere('item_code', '')
            ->get();

        $count = $productsWithoutCodes->count();

        if ($count === 0) {
            $this->info('✅ All products already have item codes!');
            return;
        }

        foreach ($productsWithoutCodes as $product) {
            $itemCode = 'P' . str_pad($product->id, 6, '0', STR_PAD_LEFT);
            $product->update(['item_code' => $itemCode]);
        }

        $this->info("✅ Generated $count item codes successfully!");
        $this->info('Sample: Product ID 5 → Item Code: P000005');
    }
}
