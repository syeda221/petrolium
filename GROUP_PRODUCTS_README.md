# Group Products Feature - Complete Documentation

## Overview
The Group Products feature allows you to combine multiple existing products to create a new assembled product that appears in your regular products list and can be sold like any other product.

## Key Features

### 1. Product Assembly
- Combine multiple existing products into one group product
- Automatically deducts stock from component products
- Creates a regular Product entry in your products list
- Group product is immediately available for sale

### 2. Automatic Integration
- **Products List**: Group products appear in the main products list
- **Stock Management**: Automatically creates stock entry
- **Sales**: Can be sold just like regular products
- **Synchronization**: Stock stays in sync between group product and product entry

### 3. Cost & Pricing
- Auto-calculates total cost from components
- Uses latest purchase prices
- Set custom sale price
- Track profit margins

## How It Works

### When You Create a Group Product:

1. **Regular Product Created**
   - Item Code: `GP-{timestamp}` (e.g., GP-1705487921)
   - Item Name: Your custom name
   - Price: Your sale price
   - Appears in Products list

2. **Stock Created**
   - Added to default warehouse/branch
   - Quantity = your production quantity
   - Available for sale immediately

3. **GroupProduct Record Created**
   - Linked to the regular product
   - Tracks all component details
   - Records production costs

4. **Component Stock Deducted**
   - Each component product loses stock
   - For example: Product A: -100, Product B: -50

### When You Sell a Group Product:

- Sale happens from the Products list (like any product)
- Stock reduces automatically
- GroupProduct `current_stock` syncs with Product stock
- Auto-deactivates when stock reaches 0

## Step-by-Step Usage

### Creating a Group Product

1. **Navigate**
   - Go to: Management → Inventory Setup → Group Products
   - Click: "Create Group Product"

2. **Fill Details**
   ```
   Product Name: Mixed Spice Pack
   Description: 5 different spices mixed
   Quantity Produced: 20 bags
   Sale Price: Rs 150
   ```

3. **Add Components**
   - Click "Add Component"
   - Select Product: Spice A (Stock shows available)
   - Quantity: 100 units
   - Repeat for each component

4. **Review Costs**
   - Total Cost: Auto-calculated
   - Cost per unit: Total ÷ Quantity

5. **Create**
   - Click "Create Group Product"
   - System validates stock
   - Creates product & stock
   - Deducts component stock

### Selling a Group Product

1. **Go to Sale Page**
   - Sales → Create Sale
   - Or click "Sale" in top menu

2. **Select Product**
   - Search for your group product by name
   - It appears with code: `GP-{timestamp}`
   - Select like any other product

3. **Complete Sale**
   - Add to cart
   - Set quantity
   - Complete sale normally

4. **Stock Updates**
   - Product stock reduces
   - GroupProduct current_stock syncs
   - Auto-deactivates at 0 stock

## Example Scenario

### Creating "Premium Mix Bag"

**Components:**
- Product A (Almonds): 100 units × Rs 10 = Rs 1,000
- Product B (Cashews): 50 units × Rs 15 = Rs 750
- Product C (Pistachios): 30 units × Rs 20 = Rs 600
- **Total Cost: Rs 2,350**

**Production:**
- Making 25 bags
- Cost per bag: Rs 2,350 ÷ 25 = Rs 94/bag
- Sale Price: Rs 150/bag
- **Profit: Rs 56/bag**

**Result:**
- ✅ Creates Product: "Premium Mix Bag" (GP-1705487921)
- ✅ Stock: 25 bags in warehouse
- ✅ Component stocks reduced:
  - Almonds: -100
  - Cashews: -50
  - Pistachios: -30
- ✅ Appears in Products list
- ✅ Ready to sell

### Selling the Product

1. Create new sale
2. Search: "Premium Mix Bag"
3. Add to cart: 5 bags
4. Price: Rs 150 each = Rs 750
5. Complete sale

**After Sale:**
- Product stock: 25 → 20 bags
- GroupProduct current_stock: 20 bags
- Still active and sellable

## Stock Synchronization

The system automatically keeps stock in sync:

- **When Created**: Product stock = GroupProduct current_stock
- **When Sold**: Product stock reduces → GroupProduct syncs
- **When Returns**: Product stock increases → GroupProduct syncs
- **At Zero**: GroupProduct deactivates, but product remains

## Important Notes

✅ **Automatic Product Creation**
- Every group product becomes a regular product
- No manual product creation needed
- Searchable and sellable immediately

✅ **Stock Accuracy**
- Component stock deducted on creation
- Group product stock managed like any product
- Real-time synchronization

✅ **Sales Integration**
- No special handling needed
- Sell through normal sale process
- All reports include group products

⚠️ **Deletion Rules**
- Can only delete if stock = 0
- Deleting removes both group product AND regular product
- Component stock NOT restored

⚠️ **Stock Deduction**
- Component stock deduction is permanent
- Cannot auto-reverse (create new group if needed)

⚠️ **One-Time Production**
- Cannot edit after creation
- Create new group product for new batch
- Each batch = separate product (different GP code)

## Database Structure

### Tables

**group_products**
- Tracks group product metadata
- Links to regular Product via `product_id`
- Stores cost and production details

**group_product_components**
- Records which products were used
- Tracks quantities and costs
- Historical record of assembly

**products**
- Regular product entry (GP-{timestamp})
- Appears in all product lists
- Used for sales

**stocks**
- Warehouse/branch stock tracking
- Synced with group_product.current_stock
- Updated on sales/returns

## Routes

- **List**: `/group-products`
- **Create**: `/group-products/create`
- **View**: `/group-products/{id}`
- **Toggle**: `PUT /group-products/{id}/toggle`
- **Delete**: `DELETE /group-products/{id}`

## Troubleshooting

**Q: Group product not appearing in sale?**
A: Check Products list - it should be there with GP- code

**Q: Stock not syncing?**
A: Observer handles this automatically. Check AppServiceProvider registration.

**Q: Want to create more of same product?**
A: Create new group product - each batch is separate with unique GP code

**Q: Component stock not deducting?**
A: Check component has sufficient stock before creating

**Q: Can I change price after creation?**
A: Edit the Product (not GroupProduct) to change sale price
