<?php

namespace App\Observers;

use App\Models\GroupProduct;
use App\Models\Stock;

class GroupProductObserver
{
    /**
     * Handle the GroupProduct "updated" event.
     */
    public function updated(GroupProduct $groupProduct): void
    {
        // Sync stock with the linked product when group product stock changes
        if ($groupProduct->isDirty('current_stock') && $groupProduct->product_id) {
            $stock = Stock::where('product_id', $groupProduct->product_id)->first();
            if ($stock) {
                $stock->update(['qty' => $groupProduct->current_stock]);
            }
        }

        // Auto-deactivate when stock reaches zero
        if ($groupProduct->current_stock <= 0 && $groupProduct->is_active) {
            $groupProduct->update(['is_active' => false]);
        }
    }

    /**
     * Handle the GroupProduct "deleting" event.
     */
    public function deleting(GroupProduct $groupProduct): void
    {
        // When deleting a group product, also delete the linked product and its stock
        if ($groupProduct->product_id) {
            $product = $groupProduct->product;
            if ($product) {
                // Delete stock first
                Stock::where('product_id', $product->id)->delete();
                // Then delete product
                $product->delete();
            }
        }
    }
}
