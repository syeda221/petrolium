<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SalesReturn;
use Illuminate\Http\Request;

use App\Models\CustomerLedger;
use App\Models\ProductBooking;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::with(['customer_relation', 'product_relation'])->orderBy('id', 'desc')->get();
        return view('admin_panel.sale.index', compact('sales'));
    }

    public function addsale()
    {
        $products = Product::get();
        $Customer = Customer::get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $accounts = \App\Models\Account::where('status', 1)->orderBy('title')->get();
        return view('admin_panel.sale.add_sale', compact('products', 'Customer', 'brands', 'accounts'));
    }

    public function getCustomerBalance($id)
    {
        if ($id == 0 || $id === 'Walk-in Customer') {
            return response()->json(['previous_balance' => 0, 'closing_balance' => 0]);
        }

        $customer = \App\Models\Customer::find($id);
        
        $drBalance = 0;
        $crBalance = 0;

        $ledger = \App\Models\CustomerLedger::where('customer_id', $id)->latest('id')->first();
        if ($ledger) {
            $drBalance = (float) $ledger->closing_balance;
        }

        if ($customer && ($customer->customer_type === 'Dual Party' || str_starts_with($customer->customer_id, 'VC-'))) {
            $vendor = \App\Models\Vendor::where('name', $customer->customer_name)->first();
            if ($vendor) {
                $vendorLedger = \App\Models\VendorLedger::where('vendor_id', $vendor->id)->latest('id')->first();
                if ($vendorLedger) {
                    $crBalance = (float) $vendorLedger->closing_balance;
                }
            }
        }

        $netBalance = $drBalance - $crBalance;

        return response()->json([
            'previous_balance' => $netBalance,
            'closing_balance'  => $netBalance,
        ]);
    }

    public function searchpname(Request $request)
    {
        $q = trim($request->q ?? '');

        // 🔹 If empty query, return all products (for frontend caching)
        if (strlen($q) === 0) {
            $products = Product::with(['brand', 'activeDiscount'])
                ->limit(5000)
                ->get()
                ->map(function ($product) {
                    $price = (float) $product->price;
                    if ($product->activeDiscount) {
                        $price = (float) $product->activeDiscount->final_price;
                    }
                    return [
                        'id'               => $product->id,
                        'item_name'        => $product->item_name,
                        'item_code'        => $product->item_code,
                        'brand'            => $product->brand?->name,
                        'unit_id'          => $product->unit_id,
                        'note'             => $product->note,
                        'wholesale_price'  => $product->wholesale_price,
                        'price'            => $price,
                        'original_price'   => $product->price,
                        'discount_percent' => $product->activeDiscount?->discount_percentage ?? 0,
                        'discount_amount'  => $product->activeDiscount?->total_discount ?? 0,
                        'has_discount'     => $product->activeDiscount ? true : false,
                    ];
                });
            return response()->json($products);
        }

        // 🔹 Step 1: Brand search
        $brandIds = \App\Models\Brand::where('name', 'like', "%{$q}%")
            ->pluck('id')
            ->toArray();

        // 🔹 Step 2: Product search (item + brand based)
        $products = Product::with(['brand', 'activeDiscount'])
            ->where(function ($query) use ($q, $brandIds) {

                // Normal product search
                $query->where('item_code', 'like', "{$q}%")
                    ->orWhere('barcode_path', 'like', "{$q}%")
                    ->orWhere('item_name', 'like', "%{$q}%");

                // 🔥 Brand based product search
                if (!empty($brandIds)) {
                    $query->orWhereIn('brand_id', $brandIds);
                }
            })
            ->limit(50)
            ->get()
            ->map(function ($product) {

                // 🔹 Price handling
                $price = (float) $product->price;

                if ($product->activeDiscount) {
                    $price = (float) $product->activeDiscount->final_price;
                }

                return [
                    'id'               => $product->id,
                    'item_name'        => $product->item_name,
                    'item_code'        => $product->item_code,
                    'brand'            => $product->brand?->name,
                    'unit_id'          => $product->unit_id,
                    'note'              => $product->note,
                    'wholesale_price'              => $product->wholesale_price,
                    'price'            => $price,
                    'original_price'   => $product->price,
                    'discount_percent' => $product->activeDiscount?->discount_percentage ?? 0,
                    'discount_amount'  => $product->activeDiscount?->total_discount ?? 0,
                    'has_discount'     => $product->activeDiscount ? true : false,
                ];
            });

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $action = $request->input('action'); // 'booking' or 'sale'
        $booking_id = $request->booking_id; // <-- existing booking ID if editing

        // --- Basic validation: require customer, reference, and at least one valid product row ---
        $validator = \Validator::make($request->all(), [
            'customer' => 'required',
            // We'll validate products manually below (because arrays mixed)
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        // Validate there's at least one filled product row
        $product_ids = is_array($request->product_id) ? $request->product_id : [];
        $qtys = is_array($request->qty) ? $request->qty : [];
        $prices = is_array($request->price) ? $request->price : [];

        $hasRow = false;
        foreach ($product_ids as $i => $pid) {
            $q = isset($qtys[$i]) ? floatval($qtys[$i]) : 0;
            $p = isset($prices[$i]) ? floatval($prices[$i]) : 0;
            if (!empty($pid) && $q > 0 && $p > 0) {
                $hasRow = true;
                break;
            }
        }

        if (! $hasRow) {
            return back()->withInput()->with('error', 'Please add at least one product with quantity and price.');
        }

        // Walk-in Customer full payment validation
        if ($action === 'sale' && $request->customer === 'Walk-in Customer') {
            $totalNet = floatval($request->total_net);
            $paidAmt = floatval($request->cash ?? 0) + floatval($request->card ?? 0);
            if ($paidAmt < $totalNet) {
                return back()->withInput()->with('error', 'For Walk-in Customer, full payment is required. Total Bill: ' . number_format($totalNet, 2) . ', Paid: ' . number_format($paidAmt, 2));
            }
        }

        DB::beginTransaction();

        try {
            // --- Input arrays (safely handle missing keys) ---
            $product_ids     = is_array($request->product_id) ? $request->product_id : [];
            $product_names   = is_array($request->product_name) ? $request->product_name : [];
            $product_codes   = is_array($request->item_code) ? $request->item_code : [];
            $brands          = is_array($request->uom) ? $request->uom : [];
            $units           = is_array($request->unit) ? $request->unit : [];
            $prices          = is_array($request->price) ? $request->price : [];
            $discounts       = is_array($request->item_disc) ? $request->item_disc : [];
            $quantities      = is_array($request->qty) ? $request->qty : [];
            $totals          = is_array($request->total) ? $request->total : [];
            $colors          = is_array($request->color) ? $request->color : [];

            // Arrays to be saved
            $combined_product_ids   = [];
            $combined_product_names = [];
            $combined_codes         = [];
            $combined_brands        = [];
            $combined_units         = [];
            $combined_prices        = [];
            $combined_discounts     = [];
            $combined_qtys          = [];
            $combined_totals        = [];
            $combined_colors        = [];

            $total_items = 0;

            foreach ($product_ids as $index => $product_id) {
                $qty   = isset($quantities[$index]) ? $quantities[$index] : 0;
                $price = isset($prices[$index]) ? $prices[$index] : 0;

                // skip incomplete rows
                if (empty($product_id) || $qty <= 0 || $price <= 0) {
                    continue;
                }

                $combined_product_ids[] = $product_id;

                $pname = $product_names[$index] ?? null;
                if (empty($pname)) {
                    $prodModel = \App\Models\Product::find($product_id);
                    $pname = $prodModel ? $prodModel->item_name : '';
                }
                $combined_product_names[] = $pname;

                $combined_codes[]      = $product_codes[$index] ?? '';
                $combined_brands[]     = $brands[$index] ?? '';
                $combined_units[]      = $units[$index] ?? '';
                $combined_prices[]     = $prices[$index] ?? 0;
                $combined_discounts[]  = $discounts[$index] ?? 0;
                $combined_qtys[]       = $quantities[$index] ?? 0;
                $combined_totals[]     = $totals[$index] ?? 0;
                $combined_colors[]     = json_encode($colors[$index] ?? []);

                // Only Sale updates stock
                if ($action === 'sale') {

                    $stock = \App\Models\Stock::where('product_id', $product_id)->first();

                    if ($stock) {
                        // stock kam ho to bhi minus me jane do
                        $stock->qty = $stock->qty - $qty;
                        $stock->save();
                    } else {
                        // agar stock record hi nahi hai to naya bana do minus qty ke sath
                        \App\Models\Stock::create([
                            'product_id' => $product_id,
                            'qty'        => -$qty,
                        ]);
                    }
                }

                $total_items += $qty;
            }

            // --- Choose model ---
            if ($action === 'booking') {
                $model = $booking_id ? \App\Models\ProductBooking::findOrFail($booking_id) : new \App\Models\ProductBooking();
            } else {
                $model = new \App\Models\Sale();
                $model->invoice_no = \App\Models\Sale::generateInvoiceNo();
            }

            // --- Fill common fields ---
            $model->customer             = $request->customer;
            $model->reference            = $request->reference;
            $model->remarks              = $request->remarks;
            $model->account_id           = $request->account_id;
            $model->product              = implode(',', $combined_product_ids);
            $model->product_code         = implode(',', $combined_codes);
            $model->brand                = implode(',', $combined_brands);
            $model->unit                 = implode(',', $combined_units);
            $model->per_price            = implode(',', $combined_prices);
            $model->per_discount         = implode(',', $combined_discounts);
            $model->qty                  = implode(',', $combined_qtys);
            $model->per_total            = implode(',', $combined_totals);
            $model->color                = json_encode($combined_colors);
            $model->total_amount_Words   = $request->total_amount_Words;
            $model->total_bill_amount    = $request->total_subtotal;
            $model->total_extradiscount  = $request->total_extra_cost;
            $model->labour_charges       = floatval($request->labour_charges ?? 0);
            $model->total_net            = $request->total_net;
            $model->cash                 = $request->cash;
            $model->card                 = $request->card;
            $model->change               = $request->change;
            $model->total_items          = $total_items;
            $model->total_pieces          = $request->total_pieces;
            $model->total_yard          = $request->total_yard;
            $model->total_meter          = $request->total_meter;

            // Booking-specific field
            if ($action === 'booking') {
                $model->advance_payment = isset($request->advance_payment) ? floatval($request->advance_payment) : 0;
                if (empty($model->booking_date)) {
                    $model->booking_date = now();
                }
            }

            $model->save();

            // If this request is confirming a booking (we came from bookings -> Confirm)
            // and action is 'sale' and booking_id present, mark the original booking as sold.
            if ($action === 'sale' && !empty($booking_id)) {
                $booking = \App\Models\ProductBooking::find($booking_id);
                if ($booking) {
                    $booking->sale_date = now();
                    // keep any previously stored advance_payment but allow overriding from request.cash or request.advance_payment
                    if ($request->has('advance_payment')) {
                        $booking->advance_payment = floatval($request->advance_payment);
                    } elseif ($request->has('cash') && floatval($request->cash) > 0) {
                        // if user put cash in confirm form and booking had advance, you may want to add or replace.
                        // here we set booking cash to the cash given at confirm (simple approach)
                        $booking->advance_payment = floatval($request->cash);
                    }
                    $booking->save();
                }
            }

            // ledger update for sale
            if ($action === 'sale') {
                $customer_id = $request->customer;
                if ($customer_id !== 'Walk-in Customer') {
                    // Net amount is bill - extra_discount + labour_charges - cash paid (credit/due amount)
                    $totalNet    = floatval($request->total_net);
                    $cashPaid    = floatval($request->cash ?? 0) + floatval($request->card ?? 0);
                    $dueAmount   = max(0, $totalNet - $cashPaid); // what customer still owes

                    $ledger = \App\Models\CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();
                    if ($ledger) {
                        $prevBalance = $ledger->closing_balance;
                        $ledger->previous_balance = $prevBalance;
                        // Add the full net sale amount to closing balance (customer owes more)
                        // Then subtract what they paid
                        $ledger->closing_balance = $prevBalance + $totalNet - $cashPaid;
                        $ledger->save();
                    } else {
                        \App\Models\CustomerLedger::create([
                            'customer_id'      => $customer_id,
                            'admin_or_user_id' => auth()->id(),
                            'previous_balance' => 0,
                            'closing_balance'  => $totalNet - $cashPaid,
                            'opening_balance'  => $totalNet - $cashPaid,
                        ]);
                    }
                }

                // Update Account Balances from multi-account payment rows
                $payAccountIds = $request->input('pay_account_id', []);
                $payAmounts    = $request->input('pay_amount', []);
                foreach ($payAccountIds as $i => $accId) {
                    $paidAmt = floatval($payAmounts[$i] ?? 0);
                    if ($paidAmt > 0 && !empty($accId)) {
                        $account = \App\Models\Account::find($accId);
                        if ($account) {
                            $account->opening_balance = $account->opening_balance + $paidAmt;
                            $account->save();
                        }
                    }
                }
            }

            DB::commit();

            // Redirect logic
            if ($action === 'sale') {
                $returnTo = route('sale.add');
                $invoiceUrl = route('sales.invoice', $model->id) . '?return_to=' . urlencode($returnTo) . '&autoprint=1';
                return redirect()->to($invoiceUrl)->with('success', 'Sale completed.');
            }

            if ($action === 'booking') {
                $returnTo = route('sale.add'); // agar booking add ka page hai
                $receiptUrl = route('booking.receipt', $model->id)
                    . '?return_to=' . urlencode($returnTo)
                    . '&autoprint=1';

                return redirect()->to($receiptUrl)->with('success', 'Booking created successfully.');
            }

            return back()->with('success', 'Saved.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }




    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */


    public function convertFromBooking($id)
    {
        $booking = ProductBooking::findOrFail($id);
        $customers = Customer::all();

        // Decode fields
        $products     = explode(',', $booking->product);
        $codes        = explode(',', $booking->product_code);
        $brands       = explode(',', $booking->brand);
        $units        = explode(',', $booking->unit);
        $prices       = explode(',', $booking->per_price);
        $discounts    = explode(',', $booking->per_discount);
        $qtys         = explode(',', $booking->qty);
        $totals       = explode(',', $booking->per_total);

        // Colors: double JSON decode fix
        $colors_json  = json_decode($booking->color, true); // this gives array of encoded strings

        $items = [];

        foreach ($products as $index => $p) {
            // Get product details
            $product = Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->first();

            // Fix color decoding
            $rawColor = $colors_json[$index] ?? null;
            $availableColors = [];

            if (is_string($rawColor)) {
                $decoded = json_decode($rawColor, true);

                if (is_array($decoded)) {
                    $availableColors = $decoded;
                } elseif (!is_null($decoded)) {
                    $availableColors = [$decoded];
                }
            } elseif (is_array($rawColor)) {
                $availableColors = $rawColor;
            }

            $items[] = [
                'product_id'        => $product->id ?? '',
                'item_name'         => $product->item_name ?? $p,
                'item_code'         => $product->item_code ?? ($codes[$index] ?? ''),
                'uom'               => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit'              => $product->unit_id ?? ($units[$index] ?? ''),
                'price'             => floatval($prices[$index] ?? 0),
                'discount'          => floatval($discounts[$index] ?? 0),
                'qty'               => intval($qtys[$index] ?? 1),
                'total'             => floatval($totals[$index] ?? 0),
                'available_colors'  => $availableColors,                  // 👈 list of all dropdown options
                'color'             => $availableColors[0] ?? null,       // 👈 selected option
            ];
        }

        return view('admin_panel.sale.booking_edit', [
            'Customer' => $customers,
            'booking' => $booking,
            'bookingItems' => $items,
        ]);
    }


    // sale return start
    public function saleretun($id)
    {
        $sale = \App\Models\Sale::findOrFail($id);
        $customers = \App\Models\Customer::all();

        // Split comma-based fields from the sale row
        $products  = array_map('trim', explode(',', $sale->product ?? ''));
        $codes     = array_map('trim', explode(',', $sale->product_code ?? ''));
        $brands    = array_map('trim', explode(',', $sale->brand ?? ''));
        $units     = array_map('trim', explode(',', $sale->unit ?? ''));
        $prices    = array_map('trim', explode(',', $sale->per_price ?? ''));
        $discounts = array_map('trim', explode(',', $sale->per_discount ?? ''));
        $qtys      = array_map('trim', explode(',', $sale->qty ?? ''));
        $totals    = array_map('trim', explode(',', $sale->per_total ?? ''));

        // decode color JSON array (if stored like ["[]","[]"])
        $colors_json = json_decode($sale->color ?? '[]', true);
        if (!is_array($colors_json)) {
            $colors_json = [];
        }

        // Fetch all previous returns for this sale from sales_returns table
        $previousReturns = \DB::table('sales_returns')->where('sale_id', $sale->id)->get();

        // Build an aggregated map: returnedQtyByProductIdOrCode[productId_or_code] = totalReturnedQty
        $returnedQtyMap = [];

        foreach ($previousReturns as $ret) {
            // ret->product and ret->qty are comma separated strings, same shape as sale
            $retProducts = array_map('trim', explode(',', $ret->product ?? ''));
            $retQtys     = array_map('trim', explode(',', $ret->qty ?? ''));

            // loop indices and accumulate
            foreach ($retProducts as $ri => $rprod) {
                $rqty = isset($retQtys[$ri]) ? floatval($retQtys[$ri]) : 0;
                if ($rqty <= 0) continue;

                if (is_numeric($rprod)) {
                    // Store by product ID
                    $keyId = 'id_' . intval($rprod);
                    if (!isset($returnedQtyMap[$keyId])) $returnedQtyMap[$keyId] = 0;
                    $returnedQtyMap[$keyId] += $rqty;
                    
                    // Also resolve to name for cross-matching
                    $rProd = \App\Models\Product::find(intval($rprod));
                    if ($rProd) {
                        $keyName = 'name_' . trim($rProd->item_name);
                        if (!isset($returnedQtyMap[$keyName])) $returnedQtyMap[$keyName] = 0;
                        $returnedQtyMap[$keyName] += $rqty;
                    }
                } else {
                    // Store by name string
                    $keyName = 'name_' . $rprod;
                    if (!isset($returnedQtyMap[$keyName])) $returnedQtyMap[$keyName] = 0;
                    $returnedQtyMap[$keyName] += $rqty;
                    
                    // Also try to find product by name and store by ID too
                    $rProd = \App\Models\Product::where('item_name', $rprod)->first();
                    if ($rProd) {
                        $keyId = 'id_' . $rProd->id;
                        if (!isset($returnedQtyMap[$keyId])) $returnedQtyMap[$keyId] = 0;
                        $returnedQtyMap[$keyId] += $rqty;
                    }
                }
            }
        }

        $items = [];

        foreach ($products as $index => $p) {
            // try to find product model by id (if value numeric) or by code fallback
            $product = null;
            $productIdCandidate = null;
            $itemCodeCandidate = $codes[$index] ?? '';

            if (is_numeric($p) && intval($p) > 0) {
                $productIdCandidate = intval($p);
                $product = \App\Models\Product::find($productIdCandidate);
            }

            if (!$product && !empty($itemCodeCandidate)) {
                $product = \App\Models\Product::where('item_code', trim($itemCodeCandidate))->first();
                if ($product) {
                    $productIdCandidate = $product->id;
                }
            }

            // ---------- NOTE parsing (previously color) ----------
            $note_value = '';
            if (isset($colors_json[$index])) {
                $maybe = $colors_json[$index];
                if (is_string($maybe)) {
                    $try = json_decode($maybe, true);
                    if ($try !== null) {
                        if (is_array($try)) {
                            $note_value = implode("\n", $try);
                        } else {
                            $note_value = (string)$try;
                        }
                    } else {
                        $note_value = $maybe;
                    }
                } elseif (is_array($maybe)) {
                    $note_value = implode("\n", $maybe);
                } else {
                    $note_value = (string)$maybe;
                }
            }
            // ---------- end note parsing ----------

            $currentQtyInDb = isset($qtys[$index]) && is_numeric($qtys[$index]) ? floatval($qtys[$index]) : 0;

            // compute returned qty using our map:
            $returnedQty = 0;
            if ($productIdCandidate) {
                $k = 'id_' . $productIdCandidate;
                if (isset($returnedQtyMap[$k])) $returnedQty = max($returnedQty, floatval($returnedQtyMap[$k]));
            }
            if ($product && !empty($product->item_name)) {
                $kn = 'name_' . trim($product->item_name);
                if (isset($returnedQtyMap[$kn])) $returnedQty = max($returnedQty, floatval($returnedQtyMap[$kn]));
            }

            // The current DB qty is the original sold qty since it isn't actually reduced by returns
            $originalSoldQty = $currentQtyInDb;
            $available = max(0, $currentQtyInDb - $returnedQty);

            $items[] = [
                'product_id'    => $product->id ?? ($productIdCandidate ?? ''),
                'item_name'     => $product->item_name ?? (string)($p),
                'item_code'     => $product->item_code ?? ($itemCodeCandidate ?? ''),
                'brand'         => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit'          => $product->unit ?? ($units[$index] ?? ''),
                'price'         => floatval($prices[$index] ?? 0),
                'discount'      => floatval($discounts[$index] ?? 0),
                'qty'           => $originalSoldQty,  // show the original full amount
                'total'         => floatval($totals[$index] ?? 0),
                // send note (plain text) so blade can show it
                'note'          => $note_value,
                'available_qty' => $available,        // show what's left after DB reduction
            ];
        }

        $accounts = \App\Models\Account::all();

        return view('admin_panel.sale.return.create', [
            'sale' => $sale,
            'Customer' => $customers,
            'saleItems' => $items,
            'accounts' => $accounts,
        ]);
    }



    // public function storeSaleReturn(Request $request)
    // {
    //     // dd($request->all());
    //     DB::beginTransaction();

    //     try {
    //         $product_ids     = $request->product_id;
    //         $product_names   = $request->product;
    //         $product_codes   = $request->item_code;
    //         $brands          = $request->uom;
    //         $units           = $request->unit;
    //         $prices          = $request->price;
    //         $discounts       = $request->item_disc;
    //         $quantities      = $request->qty;
    //         $totals          = $request->total;
    //         $colors          = $request->color;

    //         $combined_products   = [];
    //         $combined_codes      = [];
    //         $combined_brands     = [];
    //         $combined_units      = [];
    //         $combined_prices     = [];
    //         $combined_discounts  = [];
    //         $combined_qtys       = [];
    //         $combined_totals     = [];
    //         $combined_colors     = [];

    //         $total_items = 0;

    //         foreach ($product_ids as $index => $product_id) {
    //             $qty   = $quantities[$index] ?? 0;
    //             $price = $prices[$index] ?? 0;

    //             if (!$product_id || !$qty || !$price) continue;

    //             $combined_products[]   = $product_names[$index] ?? '';
    //             $combined_codes[]      = $product_codes[$index] ?? '';
    //             $combined_brands[]     = $brands[$index] ?? '';
    //             $combined_units[]      = $units[$index] ?? '';
    //             $combined_prices[]     = $price;
    //             $combined_discounts[]  = $discounts[$index] ?? 0;
    //             $combined_qtys[]       = $qty;
    //             $combined_totals[]     = $totals[$index] ?? 0;

    //             // Convert color to valid JSON array
    //             $decodedColor = $colors[$index] ?? [];
    //             if (is_array($decodedColor)) {
    //                 $combined_colors[] = json_encode($decodedColor);
    //             } else {
    //                 $decoded = json_decode($decodedColor, true);
    //                 $combined_colors[] = json_encode(is_array($decoded) ? $decoded : []);
    //             }

    //             // ➕ Restore stock
    //             $stock = \App\Models\Stock::where('product_id', $product_id)->first();
    //             if ($stock) {
    //                 $stock->qty += $qty;
    //                 $stock->save();
    //             }

    //             $total_items += $qty;
    //         }

    //         // ➕ Create Sale Return
    //         $saleReturn = new \App\Models\SalesReturn();
    //         $saleReturn->sale_id              = $request->sale_id;
    //         $saleReturn->customer             = $request->customer;
    //         $saleReturn->reference            = $request->reference;

    //         $saleReturn->product              = implode(',', $combined_products);
    //         $saleReturn->product_code         = implode(',', $combined_codes);
    //         $saleReturn->brand                = implode(',', $combined_brands);
    //         $saleReturn->unit                 = implode(',', $combined_units);
    //         $saleReturn->per_price            = implode(',', $combined_prices);
    //         $saleReturn->per_discount         = implode(',', $combined_discounts);
    //         $saleReturn->qty                  = implode(',', $combined_qtys);
    //         $saleReturn->per_total            = implode(',', $combined_totals);
    //         $saleReturn->color                = json_encode($combined_colors);

    //         $saleReturn->total_amount_Words   = $request->total_amount_Words;
    //         $saleReturn->total_bill_amount    = $request->total_subtotal;
    //         $saleReturn->total_extradiscount  = $request->total_extra_cost;
    //         $saleReturn->total_net            = $request->total_net;

    //         $saleReturn->cash                 = $request->cash;
    //         $saleReturn->card                 = $request->card;
    //         $saleReturn->change               = $request->change;

    //         $saleReturn->total_items          = $total_items;
    //         $saleReturn->return_note          = $request->return_note;

    //         $saleReturn->save();

    //         DB::commit();

    //         return redirect()->route('sale.index')->with('success', 'Sale return saved successfully.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Sale return failed: ' . $e->getMessage());
    //     }
    // }
    public function storeSaleReturn(Request $request)
    {
        // Get the sale to check customer type
        $sale = \App\Models\Sale::findOrFail($request->sale_id);
        
        // Check if walking customer or regular customer
        $isWalkingCustomer = false;
        if (!is_numeric($sale->customer)) {
            // Check if it's the "Walk-in Customer" string
            $isWalkingCustomer = (strpos($sale->customer, 'Walk') !== false);
        } else {
            // It's a customer ID, check the customer type
            $customer = \App\Models\Customer::find($sale->customer);
            $isWalkingCustomer = $customer && $customer->customer_type === 'Walking Customer';
        }
        
        // Payment validation rules
        $paymentRules = [
            'cash'  => 'nullable|numeric|min:0',
            'card'  => 'nullable|numeric|min:0',
        ];
        
        if ($isWalkingCustomer) {
            // Walking customer: must provide cash refund
            $paymentRules['cash'] = 'required|numeric|min:0.01';
            $paymentRules['card'] = 'nullable|numeric|min:0';
        } else {
            // Regular customer: cash refund allowed, but will be subtracted from ledger credit
            $paymentRules['cash'] = 'nullable|numeric|min:0';
            $paymentRules['card'] = 'nullable|numeric|min:0';
        }
        
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'product'    => 'required|array',
            'product.*'  => 'nullable|string',
            'item_code'  => 'required|array',
            'item_code.*' => 'nullable|string',
            'unit'       => 'required|array',
            'unit.*'     => 'nullable|string',
            'price'      => 'required|array',
            'price.*'    => 'nullable|numeric',
            'item_disc'  => 'required|array',
            'item_disc.*' => 'nullable|numeric',
            'qty'        => 'required|array',
            'qty.*'      => 'nullable|numeric|min:0',
            'total'      => 'required|array',
            'total.*'    => 'nullable|numeric',
            'color'      => 'nullable|array',
            ...$paymentRules,
        ], [
            'cash.required' => 'Payment amount is required for walking customer returns.',
            'cash.max' => 'Regular customers cannot receive cash. Only ledger is maintained.',
            'card.max' => 'Regular customers cannot receive card payment. Only ledger is maintained.',
        ]);

        DB::beginTransaction();
        try {
            $saleId = $request->sale_id;
            $sale = \App\Models\Sale::findOrFail($saleId);

            // Incoming arrays (may contain only selected return rows)
            $product_names = $request->input('product', []);     // product name or id text
            $product_ids   = $request->input('product_id', []);  // may be empty strings for some rows
            $product_codes = $request->input('item_code', []);
            $brands        = $request->input('brand', $request->input('uom', [])); // some forms call it uom
            $units         = $request->input('unit', []);
            $prices        = $request->input('price', []);
            $discounts     = $request->input('item_disc', []);
            $quantities    = $request->input('qty', []);
            $totals        = $request->input('total', []);
            $colors        = $request->input('color', []); // may be array of single selected color value or json

            // We'll build combined arrays to save in sales_returns
            $combined_products   = [];
            $combined_codes      = [];
            $combined_brands     = [];
            $combined_units      = [];
            $combined_prices     = [];
            $combined_discounts  = [];
            $combined_qtys       = [];
            $combined_totals     = [];
            $combined_colors     = [];

            $total_items = 0;

            // Use length of product_names (should match other arrays); be defensive
            $rows = max(
                count($product_names),
                count($product_codes),
                count($quantities),
                count($prices)
            );

            // Build a map of returns by code for updating original sale quantities
            $returnByCode = []; // code => qty to reduce

            for ($i = 0; $i < $rows; $i++) {
                $name = isset($product_names[$i]) ? trim($product_names[$i]) : '';
                $pid  = isset($product_ids[$i]) ? trim($product_ids[$i]) : '';
                $code = isset($product_codes[$i]) ? trim($product_codes[$i]) : '';
                $brand = isset($brands[$i]) ? trim($brands[$i]) : '';
                $unit  = isset($units[$i]) ? trim($units[$i]) : '';
                $price = isset($prices[$i]) ? floatval($prices[$i]) : 0;
                $disc  = isset($discounts[$i]) ? floatval($discounts[$i]) : 0;
                $qty   = isset($quantities[$i]) ? floatval($quantities[$i]) : 0;
                $total = isset($totals[$i]) ? floatval($totals[$i]) : ($price * $qty);
                $colorRaw = $colors[$i] ?? null;

                // Skip rows with zero qty (not selected)
                if ($qty <= 0) continue;

                // Push to combined arrays (preserve name/code even if id missing)
                $combined_products[]  = $name;
                $combined_codes[]     = $code;
                $combined_brands[]    = $brand;
                $combined_units[]     = $unit;
                $combined_prices[]    = (string)$price;
                $combined_discounts[] = (string)$disc;
                $combined_qtys[]      = (string)$qty;
                $combined_totals[]    = (string)$total;

                // Normalize color: if array => json_encode; if string that's JSON => try decode
                if (is_array($colorRaw)) {
                    $combined_colors[] = json_encode($colorRaw);
                } else {
                    // try parse string -> if valid json array keep, else wrap single value
                    $decoded = null;
                    if (is_string($colorRaw)) {
                        $decoded = json_decode($colorRaw, true);
                    }
                    if (is_array($decoded)) {
                        $combined_colors[] = json_encode($decoded);
                    } elseif (!empty($colorRaw)) {
                        $combined_colors[] = json_encode([$colorRaw]);
                    } else {
                        $combined_colors[] = json_encode([]);
                    }
                }

                // Stock update: increase stock for returned items if we can find product by ID or code
                $foundProduct = null;
                if ($pid !== '') {
                    // If numeric id provided, try find by id
                    if (is_numeric($pid)) {
                        $foundProduct = \App\Models\Product::find(intval($pid));
                    } else {
                        // sometimes product_id may come as name; try to find by id or code fallback
                        $maybe = \App\Models\Product::find($pid);
                        if (!$maybe && $code) {
                            $maybe = \App\Models\Product::where('item_code', $code)->first();
                        }
                        $foundProduct = $maybe;
                    }
                } else if (!empty($code)) {
                    $foundProduct = \App\Models\Product::where('item_code', $code)->first();
                } else if (!empty($name)) {
                    $foundProduct = \App\Models\Product::where('item_name', $name)->first();
                }

                if ($foundProduct) {
                    // Update stock: find stock row for product in same branch/warehouse (use sale's warehouse if available)
                    // If you use branch_id or auth()->id() use appropriate field
                    $stockQuery = \App\Models\Stock::where('product_id', $foundProduct->id);
                    // if your sale has warehouse info use that, else we skip warehouse filter
                    if (!empty($sale->warehouse_id)) {
                        $stockQuery->where('warehouse_id', $sale->warehouse_id);
                    }
                    // optionally branch filter
                    // $stockQuery->where('branch_id', auth()->id());

                    $stock = $stockQuery->first();
                    if ($stock) {
                        $stock->qty = $stock->qty + $qty;
                        $stock->save();
                    }
                }

                // accumulate for sale update
                $key = $code ?: ($foundProduct ? ('ID_' . $foundProduct->id) : $name);
                if (!isset($returnByCode[$key])) $returnByCode[$key] = 0;
                $returnByCode[$key] += $qty;

                $total_items += $qty;
            }

            if (empty($combined_products)) {
                return redirect()->back()->with('error', 'No items selected for return.');
            }

            // Save sales_returns row (CSV arrays + json color array)
            $saleReturn = new \App\Models\SalesReturn();
            $saleReturn->sale_id = $saleId;
            $saleReturn->customer = $request->customer;
            $saleReturn->reference = $request->reference;
            $saleReturn->product = implode(',', $combined_products);
            $saleReturn->product_code = implode(',', $combined_codes);
            $saleReturn->brand = implode(',', $combined_brands);
            $saleReturn->unit = implode(',', $combined_units);
            $saleReturn->per_price = implode(',', $combined_prices);
            $saleReturn->per_discount = implode(',', $combined_discounts);
            $saleReturn->qty = implode(',', $combined_qtys);
            $saleReturn->per_total = implode(',', $combined_totals);
            // colors as JSON array of json-encoded color-arrays (to keep compatible with your existing format)
            $saleReturn->color = json_encode($combined_colors);
            $saleReturn->total_amount_Words = $request->total_amount_Words ?? '';
            $saleReturn->total_bill_amount = $request->total_subtotal ?? array_sum($combined_totals);
            $saleReturn->total_extradiscount = $request->total_extra_cost ?? 0;
            $saleReturn->total_net = $request->total_net ?? array_sum($combined_totals);
            $saleReturn->cash = $request->cash ?? 0;
            $saleReturn->card = $request->card ?? 0;
            $saleReturn->change = $request->change ?? 0;
            $saleReturn->total_items = $total_items;
            $saleReturn->return_note = $request->return_note ?? null;
            $saleReturn->save();

            // The original Sale quantities are intentionally NOT modified here
            // to preserve the original invoice state and ensure Item Stock Report tracks "Original - Return" correctly.
            // (If product_code was empty, the loop previously did nothing anyway).
            
            // optionally update sale_status: mark as partially returned (1) or fully returned
            $sale->sale_status = 1;
            $sale->save();

            // -----------------------
            // Multi-account refund logic
            // -----------------------
            $payAccountIds = $request->input('pay_account_id', []);
            $payAmounts    = $request->input('pay_amount', []);
            $totalRefunded = 0;
            
            foreach ($payAccountIds as $i => $accId) {
                $refundAmt = floatval($payAmounts[$i] ?? 0);
                if ($refundAmt > 0 && !empty($accId)) {
                    $account = \App\Models\Account::find($accId);
                    if ($account) {
                        $account->opening_balance = $account->opening_balance - $refundAmt;
                        $account->save();
                        $totalRefunded += $refundAmt;
                    }
                }
            }

            // -----------------------
            // Customer ledger update (simple) - Skip for Walk-in Customer
            // -----------------------
            $customer_id = $request->customer;
            $netAmount = $saleReturn->total_net;

            if ($customer_id !== 'Walk-in Customer') {
                $ledgerCredit = $netAmount - $totalRefunded; // only credit ledger for portion not paid directly back

                $ledger = \App\Models\CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();
                if ($ledger) {
                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance = $ledger->closing_balance - $ledgerCredit;
                    $ledger->save();
                } else {
                    \App\Models\CustomerLedger::create([
                        'customer_id' => $customer_id,
                        'admin_or_user_id' => auth()->id(),
                        'previous_balance' => 0,
                        'opening_balance' => 0 - $ledgerCredit,
                        'closing_balance' => 0 - $ledgerCredit,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('sale.index')->with('success', 'Sale return saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Sale return failed: ' . $e->getMessage());
        }
    }


    public function salereturnview()
    {
        // Fetch all sale returns with the original sale and customer info
        $salesReturns = SalesReturn::with('sale.customer_relation')->orderBy('created_at', 'desc')->get();
        return view('admin_panel.sale.return.index', [
            'salesReturns' => $salesReturns,
        ]);
    }

    public function saleinvoice($id)
    {
        $sale = Sale::with('customer_relation')->findOrFail($id);

        $saleReturn = \App\Models\SalesReturn::where('sale_id', $sale->id)->first();

        // 🔥 IMPORTANT: decide source
        $bill = $saleReturn ? $saleReturn : $sale;
        // items
        $products = explode(',', $bill->product);
        $codes    = explode(',', $bill->product_code);
        $brands   = explode(',', $bill->brand);
        $units    = explode(',', $bill->unit);
        $prices   = explode(',', $bill->per_price);
        $discounts = explode(',', $bill->per_discount);
        $qtys     = explode(',', $bill->qty);
        $totals   = explode(',', $bill->per_total);
        
        // Decode color/note JSON
        $colors_json = json_decode($bill->color, true);
        if (!is_array($colors_json)) {
            $colors_json = [];
        }

        $items = [];
        $productIds = array_unique($products);
        $productMap = Product::whereIn('id', $productIds)
        ->pluck('item_name', 'id'); // [id => item_name]
        
        foreach ($products as $index => $p) {
            
            // Note parsing
            $note_value = '';
            if (isset($colors_json[$index])) {
                $maybe = $colors_json[$index];
                if (is_string($maybe)) {
                    $try = json_decode($maybe, true);
                    if ($try !== null) {
                        if (is_array($try)) $note_value = implode("\n", $try);
                        else $note_value = (string)$try;
                    } else {
                        $note_value = $maybe;
                    }
                } elseif (is_array($maybe)) {
                    $note_value = implode("\n", $maybe);
                } else {
                    $note_value = (string)$maybe;
                }
            }

            $items[] = [
                'item_name' => $productMap[$p] ?? $p, // ✅ real product name
                'item_code' => $codes[$index] ?? '',
                'brand'     => $brands[$index] ?? '',
                'unit'      => $units[$index] ?? '',
                'price'     => (float) ($prices[$index] ?? 0),
                'discount'  => (float) ($discounts[$index] ?? 0),
                'qty'       => (float) ($qtys[$index] ?? 0),
                'total'     => (float) ($totals[$index] ?? 0),
                'note'      => $note_value,
            ];
        }
        
        // Customer closing balance (skip Walk-in Customer)
        $customerClosingBalance = null;
        if ($sale->customer && $sale->customer !== 'Walk-in Customer' && is_numeric($sale->customer)) {
            $ledger = \App\Models\CustomerLedger::where('customer_id', $sale->customer)->latest('id')->first();
            $customerClosingBalance = $ledger ? (float) $ledger->closing_balance : 0;
        }

        return view('admin_panel.sale.saleinvoice', [
            'sale'                   => $sale,
            'bill'                   => $bill,
            'saleItems'              => $items,
            'saleReturn'             => $saleReturn,
            'customerClosingBalance' => $customerClosingBalance,
        ]);
    }

    public function saleinvoiceA4($id)
    {
        $sale = Sale::with('customer_relation')->findOrFail($id);

        $saleReturn = \App\Models\SalesReturn::where('sale_id', $sale->id)->first();

        // 🔥 IMPORTANT: decide source
        $bill = $saleReturn ? $saleReturn : $sale;
        // items
        $products = explode(',', $bill->product);
        $codes    = explode(',', $bill->product_code);
        $brands   = explode(',', $bill->brand);
        $units    = explode(',', $bill->unit);
        $prices   = explode(',', $bill->per_price);
        $discounts = explode(',', $bill->per_discount);
        $qtys     = explode(',', $bill->qty);
        $totals   = explode(',', $bill->per_total);

        // Decode color/note JSON
        $colors_json = json_decode($bill->color, true);
        if (!is_array($colors_json)) {
            $colors_json = [];
        }

        $items = [];
        $productIds = array_unique($products);
        $productMap = Product::whereIn('id', $productIds)
        ->pluck('item_name', 'id'); // [id => item_name]

        foreach ($products as $index => $p) {
            
            // Note parsing
            $note_value = '';
            if (isset($colors_json[$index])) {
                $maybe = $colors_json[$index];
                if (is_string($maybe)) {
                    $try = json_decode($maybe, true);
                    if ($try !== null) {
                        if (is_array($try)) $note_value = implode("\n", $try);
                        else $note_value = (string)$try;
                    } else {
                        $note_value = $maybe;
                    }
                } elseif (is_array($maybe)) {
                    $note_value = implode("\n", $maybe);
                } else {
                    $note_value = (string)$maybe;
                }
            }

            $items[] = [
                'item_name' => $productMap[$p] ?? $p, // ✅ real product name
                'item_code' => $codes[$index] ?? '',
                'brand'     => $brands[$index] ?? '',
                'unit'      => $units[$index] ?? '',
                'price'     => (float) ($prices[$index] ?? 0),
                'discount'  => (float) ($discounts[$index] ?? 0),
                'qty'       => (float) ($qtys[$index] ?? 0),
                'total'     => (float) ($totals[$index] ?? 0),
                'note'      => $note_value,
            ];
        }
        
        // Customer closing balance (skip Walk-in Customer)
        $customerClosingBalance = null;
        if ($sale->customer && $sale->customer !== 'Walk-in Customer' && is_numeric($sale->customer)) {
            $ledger = \App\Models\CustomerLedger::where('customer_id', $sale->customer)->latest('id')->first();
            $customerClosingBalance = $ledger ? (float) $ledger->closing_balance : 0;
        }

        return view('admin_panel.sale.saleinvoice_a4', [
            'sale'                   => $sale,
            'bill'                   => $bill,
            'saleItems'              => $items,
            'saleReturn'             => $saleReturn,
            'customerClosingBalance' => $customerClosingBalance,
        ]);
    }
    public function saleedit($id)
    {
        $sale = Sale::findOrFail($id);
        $customers = Customer::all();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $accounts = \App\Models\Account::where('status', 1)->orderBy('title')->get();

        $products   = explode(',', $sale->product);
        $codes      = explode(',', $sale->product_code);
        $brands_list = explode(',', $sale->brand);
        $units      = explode(',', $sale->unit);
        $prices     = explode(',', $sale->per_price);
        $discounts  = explode(',', $sale->per_discount);
        $qtys       = explode(',', $sale->qty);
        $totals     = explode(',', $sale->per_total);

        // Expecting sale->color to be JSON array (each element a JSON-encoded note or plain string)
        $colors_json = json_decode($sale->color, true);
        if (!is_array($colors_json)) {
            $colors_json = [];
        }

        $items = [];

        foreach ($products as $index => $p) {
            $product = Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->orWhere('id', trim($p))
                ->first();

            // Get note safely:
            $note_value = '';
            if (isset($colors_json[$index])) {
                // If stored as JSON-encoded string, decode; else use directly
                $maybe = $colors_json[$index];

                if (is_string($maybe)) {
                    // try json decode in case it's JSON string
                    $try = json_decode($maybe, true);
                    if ($try !== null) {
                        // decoded OK (could be array or string)
                        if (is_array($try)) {
                            // join array into newline-separated text
                            $note_value = implode("\n", $try);
                        } else {
                            $note_value = (string)$try;
                        }
                    } else {
                        // plain string note
                        $note_value = $maybe;
                    }
                } elseif (is_array($maybe)) {
                    // join into text
                    $note_value = implode("\n", $maybe);
                } else {
                    $note_value = (string)$maybe;
                }
            }

            $items[] = [
                'product_id' => $product->id ?? '',
                'item_name'  => $product->item_name ?? $p,
                'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                'brand'      => $product->brand->name ?? ($brands_list[$index] ?? ''),
                'unit'       => $product->unit ?? ($units[$index] ?? ''),
                'price'      => floatval($prices[$index] ?? 0),
                'discount'   => floatval($discounts[$index] ?? 0),
                'qty'        => intval($qtys[$index] ?? 1),
                'total'      => floatval($totals[$index] ?? 0),
                'note'       => $note_value, // <-- note instead of color array
            ];
        }

        return view('admin_panel.sale.saleedit', [
            'sale' => $sale,
            'Customer' => $customers,
            'saleItems' => $items,
            'brands' => $brands,
            'accounts' => $accounts,
        ]);
    }

    public function updatesale(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $sale = Sale::findOrFail($id);
            $old_total_net = floatval($sale->total_net);
            $old_paid = floatval($sale->cash) + floatval($sale->card);
            $old_customer_id = $sale->customer;

            // --- 1. Revert Old Stock ---
            $old_products = explode(',', $sale->product);
            $old_qtys     = explode(',', $sale->qty);
            foreach ($old_products as $index => $old_p_id) {
                if (empty($old_p_id)) continue;
                $old_q = isset($old_qtys[$index]) ? floatval($old_qtys[$index]) : 0;
                $stock = \App\Models\Stock::where('product_id', $old_p_id)->first();
                if ($stock) {
                    $stock->qty += $old_q;
                    $stock->save();
                }
            }

            // --- 2. Revert Old Account Balance (from sale->account_id) ---
            if ($sale->account_id && $old_paid > 0) {
                $old_account = \App\Models\Account::find($sale->account_id);
                if ($old_account) {
                    $old_account->opening_balance -= $old_paid;
                    $old_account->save();
                }
            }

            // --- 3. Revert Old Ledger Impact ---
            if ($old_customer_id !== 'Walk-in Customer') {
                $ledger = CustomerLedger::where('customer_id', $old_customer_id)->latest('id')->first();
                if ($ledger) {
                    $ledger->closing_balance -= ($old_total_net - $old_paid);
                    $ledger->save();
                }
            }

            // --- 4. Process New Data ---
            $product_ids    = $request->product_id ?? [];
            $product_codes  = $request->item_code ?? [];
            $brands         = $request->uom ?? []; // brand is passed as uom in form
            $units          = $request->unit ?? [];
            $prices         = $request->price ?? [];
            $discounts      = $request->item_disc ?? [];
            $quantities     = $request->qty ?? [];
            $totals         = $request->total ?? [];

            $combined_products  = [];
            $combined_codes     = [];
            $combined_brands    = [];
            $combined_units     = [];
            $combined_prices    = [];
            $combined_discounts = [];
            $combined_qtys      = [];
            $combined_totals    = [];
            $combined_notes     = [];

            $total_items = 0;

            foreach ($product_ids as $index => $p_id) {
                $qty   = isset($quantities[$index]) ? floatval($quantities[$index]) : 0;
                $price = isset($prices[$index]) ? floatval($prices[$index]) : 0;

                if (!$p_id || $qty <= 0) continue;

                $combined_products[]  = $p_id;
                $combined_codes[]     = $product_codes[$index] ?? '';
                $combined_brands[]    = $brands[$index] ?? '';
                $combined_units[]     = $units[$index] ?? '';
                $combined_prices[]    = $price;
                $combined_discounts[] = $discounts[$index] ?? 0;
                $combined_qtys[]      = $qty;
                $combined_totals[]    = $totals[$index] ?? 0;
                $combined_notes[]     = ''; // Placeholder for notes if needed

                $total_items += $qty;

                // Subtract new stock
                $stock = \App\Models\Stock::where('product_id', $p_id)->first();
                if ($stock) {
                    $stock->qty -= $qty;
                    $stock->save();
                } else {
                    \App\Models\Stock::create([
                        'product_id' => $p_id,
                        'qty' => -$qty,
                    ]);
                }
            }

            // --- 5. Update Sale Record ---
            $sale->customer            = $request->customer;
            $sale->remarks             = $request->remarks;
            $sale->product             = implode(',', $combined_products);
            $sale->product_code        = implode(',', $combined_codes);
            $sale->brand               = implode(',', $combined_brands);
            $sale->unit                = implode(',', $combined_units);
            $sale->per_price           = implode(',', $combined_prices);
            $sale->per_discount        = implode(',', $combined_discounts);
            $sale->qty                 = implode(',', $combined_qtys);
            $sale->per_total           = implode(',', $combined_totals);
            $sale->color               = json_encode($combined_notes);
            $sale->total_amount_Words  = $request->total_amount_Words;
            $sale->total_bill_amount   = $request->total_subtotal;
            $sale->total_extradiscount = $request->total_extra_cost;
            $sale->labour_charges       = floatval($request->labour_charges ?? 0);
            $sale->total_net           = $request->total_net;
            
            // Handle Payments (multi-account)
            $payAccountIds = $request->input('pay_account_id', []);
            $payAmounts    = $request->input('pay_amount', []);
            $total_paid = 0;
            
            foreach ($payAccountIds as $i => $accId) {
                $paidAmt = floatval($payAmounts[$i] ?? 0);
                if ($paidAmt > 0 && !empty($accId)) {
                    $total_paid += $paidAmt;
                    $account = \App\Models\Account::find($accId);
                    if ($account) {
                        $account->opening_balance += $paidAmt;
                        $account->save();
                    }
                    // Update the primary account_id of the sale to the first account used
                    if ($i === 0) {
                        $sale->account_id = $accId;
                    }
                }
            }

            $sale->cash                = $total_paid; // Store total paid in cash field for simplicity
            $sale->card                = 0;
            $sale->change              = $request->change;
            $sale->total_items         = $total_items;
            $sale->save();

            // --- 6. New Ledger Impact ---
            if ($request->customer !== 'Walk-in Customer') {
                $ledger = CustomerLedger::where('customer_id', $request->customer)->latest('id')->first();
                $impact = floatval($request->total_net) - $total_paid;

                if ($ledger) {
                    $ledger->closing_balance += $impact;
                    $ledger->save();
                } else {
                    CustomerLedger::create([
                        'customer_id'      => $request->customer,
                        'admin_or_user_id' => auth()->id(),
                        'previous_balance' => 0,
                        'closing_balance'  => $impact,
                        'opening_balance'  => $impact,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('sale.index')->with('success', 'Sale updated successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }



    public function saledc($id)
    {
        $sale = Sale::with('customer_relation')->findOrFail($id);

        // Decode sale pivot or comma fields
        $products = explode(',', $sale->product);
        $codes    = explode(',', $sale->product_code);
        $brands   = explode(',', $sale->brand);
        $units    = explode(',', $sale->unit);
        $prices   = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys     = explode(',', $sale->qty);
        $totals   = explode(',', $sale->per_total);
        $colors_json = json_decode($sale->color, true);

        $items = [];

        foreach ($products as $index => $p) {
            $product = Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->first();

            $items[] = [
                'product_id' => $product->id ?? '',
                'item_name'  => $product->item_name ?? $p,
                'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                'brand'      => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit'       => $product->unit ?? ($units[$index] ?? ''),
                'price'      => floatval($prices[$index] ?? 0),
                'discount'   => floatval($discounts[$index] ?? 0),
                'qty'        => intval($qtys[$index] ?? 1),
                'total'      => floatval($totals[$index] ?? 0),
                'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
            ];
        }

        return view('admin_panel.sale.saledc', [
            'sale' => $sale,
            'saleItems' => $items,
        ]);
    }

    public function salerecepit($id)
    {
        $sale = Sale::with('customer_relation')->findOrFail($id);

        // Decode sale pivot or comma fields
        $products = explode(',', $sale->product);
        $codes    = explode(',', $sale->product_code);
        $brands   = explode(',', $sale->brand);
        $units    = explode(',', $sale->unit);
        $prices   = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys     = explode(',', $sale->qty);
        $totals   = explode(',', $sale->per_total);
        $colors_json = json_decode($sale->color, true);

        $items = [];

        foreach ($products as $index => $p) {
            $product = Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->first();

            $items[] = [
                'product_id' => $product->id ?? '',
                'item_name'  => $product->item_name ?? $p,
                'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                'brand'      => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit'       => $product->unit ?? ($units[$index] ?? ''),
                'price'      => floatval($prices[$index] ?? 0),
                'discount'   => floatval($discounts[$index] ?? 0),
                'qty'        => intval($qtys[$index] ?? 1),
                'total'      => floatval($totals[$index] ?? 0),
                'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
            ];
        }

        return view('admin_panel.sale.salerecepit', [
            'sale' => $sale,
            'saleItems' => $items,
        ]);
    }

    public function retrninvoice($id)
    {
        $return = \App\Models\SalesReturn::with('sale.customer_relation')->findOrFail($id);

        $products   = explode(',', $return->product);
        $codes      = explode(',', $return->product_code);
        $brands     = explode(',', $return->brand);
        $units      = explode(',', $return->unit);
        $prices     = explode(',', $return->per_price);
        $discounts  = explode(',', $return->per_discount);
        $qtys       = explode(',', $return->qty);
        $totals     = explode(',', $return->per_total);
        $colors_json = json_decode($return->color, true);

        $items = [];

        foreach ($products as $index => $p) {
            $product = \App\Models\Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->first();

            $items[] = [
                'product_id' => $product->id ?? '',
                'item_name'  => $product->item_name ?? $p,
                'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                'brand'      => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit'       => $product->unit ?? ($units[$index] ?? ''),
                'price'      => floatval($prices[$index] ?? 0),
                'discount'   => floatval($discounts[$index] ?? 0),
                'qty'        => intval($qtys[$index] ?? 1),
                'total'      => floatval($totals[$index] ?? 0),
                'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
            ];
        }

        return view('admin_panel.sale.return.salereturnrecepit', [
            'return' => $return,
            'returnItems' => $items,
        ]);
    }
}
