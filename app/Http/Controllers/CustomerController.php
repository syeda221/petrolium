<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerPayment;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::where('customer_type', '!=', 'Dual Party')->orWhereNull('customer_type')->latest()->get();
        return view('admin_panel.customers.index', compact('customers'));
    }

    public function toggleStatus($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->status = $customer->status === 'active' ? 'inactive' : 'active';
        $customer->save();

        return redirect()->back()->with('success', 'Customer status updated.');
    }

    // Add this in CustomerController
    public function getCustomerLedger($id)
    {
        $ledger = CustomerLedger::where('customer_id', $id)->latest()->first();
        return response()->json([
            'closing_balance' => $ledger->closing_balance
        ]);
    }


    public function markInactive($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->status = 'inactive';
        $customer->save();

        return redirect()->route('customers.index')->with('success', 'Customer marked as inactive.');
    }

    public function inactiveCustomers()
    {
        $customers = Customer::where('status', 'inactive')->latest()->get();
        return view('admin_panel.customers.inactive', compact('customers'));
    }

    public function create()
    {
        $latestId = 'CUST-' . str_pad(Customer::max('id') + 1, 4, '0', STR_PAD_LEFT);
        return view('admin_panel.customers.create', compact('latestId'));
    }

    public function createDual()
    {
        $latestId = 'VC-' . str_pad(Customer::max('id') + 1, 3, '0', STR_PAD_LEFT);
        $dualParties = Customer::where('customer_type', 'Dual Party')->orWhere('customer_id', 'LIKE', 'VC-%')->latest()->get();
        return view('admin_panel.dual_party.create', compact('latestId', 'dualParties'));
    }

    public function dualPartyLedger($id)
    {
        $customer = Customer::findOrFail($id);
        
        // Find matching vendor by name (since Dual Parties are created with the exact same name)
        $vendor = \App\Models\Vendor::where('name', $customer->customer_name)->first();

        // Standardize Opening Balance (Dr is positive, Cr is negative)
        // If customer has opening_balance > 0, it means Dr.
        // If vendor has opening_balance > 0, it means Cr (for dual party context).
        $opening = ($customer->opening_balance ?? 0) - ($vendor ? ($vendor->opening_balance ?? 0) : 0);

        // Fetch CUSTOMER side transactions (Asset side)
        // ---------------------------------------------
        $sales = \Illuminate\Support\Facades\DB::table('sales')
            ->where('customer', $customer->id)
            ->get()
            ->map(function ($s) {
                return [
                    'date' => $s->created_at,
                    'invoice' => 'INV-' . $s->id,
                    'description' => 'Sale to Party' . (($s->cash + $s->card > 0) ? ' (Payment Received: Rs.' . ($s->cash + $s->card) . ')' : ''),
                    'debit' => $s->total_net,
                    'credit' => (float) ($s->cash + $s->card), // Cash received pays off part of debit
                ];
            });

        $customerPayments = \Illuminate\Support\Facades\DB::table('customer_payments')
            ->where('customer_id', $customer->id)
            ->get()
            ->map(function ($p) {
                $isPlus = $p->adjustment_type === 'plus'; // Plus = Debit
                return [
                    'date' => $p->payment_date . ' 23:59:59',
                    'invoice' => '-',
                    'description' => 'Customer Adj/Receipt: ' . ($p->note ?? ''),
                    'debit' => $isPlus ? (float) $p->amount : 0,
                    'credit' => $isPlus ? 0 : (float) $p->amount,
                ];
            });

        $saleReturns = \Illuminate\Support\Facades\DB::table('sales_returns')
            ->where('customer', $customer->id)
            ->get()
            ->map(function ($r) {
                return [
                    'date' => $r->created_at,
                    'invoice' => 'RET-' . $r->id,
                    'description' => 'Sale Return (Ref: ' . $r->sale_id . ')',
                    'debit' => 0,
                    'credit' => (float) $r->total_net,
                ];
            });


        // Fetch VENDOR side transactions (Liability side) (Reverse the Dr/Cr meaning!)
        // ---------------------------------------------
        $purchases = collect([]);
        $purchaseReturns = collect([]);
        $vendorPayments = collect([]);

        if ($vendor) {
            $purchases = \Illuminate\Support\Facades\DB::table('purchases')
                ->where('vendor_id', $vendor->id)
                ->get()
                ->map(function ($p) {
                    return [
                        'date' => $p->purchase_date . ' 23:59:59', // Usually purchases just have dates
                        'invoice' => $p->invoice_no,
                        'description' => 'Purchase from Party',
                        'debit' => 0,
                        'credit' => $p->net_amount, // Liability increases
                    ];
                });

            $purchaseReturns = \Illuminate\Support\Facades\DB::table('purchase_returns')
                ->where('vendor_id', $vendor->id)
                ->get()
                ->map(function ($r) {
                    return [
                        'date' => $r->return_date . ' 23:59:59',
                        'invoice' => $r->return_invoice,
                        'description' => 'Purchase Return',
                        'debit' => $r->net_amount, // Reduces liability
                        'credit' => 0,
                    ];
                });

            $vendorPayments = \Illuminate\Support\Facades\DB::table('vendor_payments')
                ->where('vendor_id', $vendor->id)
                ->get()
                ->map(function ($v) {
                    return [
                        'date' => $v->payment_date . ' 23:59:59',
                        'invoice' => '-',
                        'description' => 'Vendor Adj/Payment: ' . ($v->note ?? ''),
                        'debit' => $v->amount, // We paid them, reduces liability
                        'credit' => 0,
                    ];
                });
        }

        // Merge all!
        $transactions = $sales
            ->merge($customerPayments)
            ->merge($saleReturns)
            ->merge($purchases)
            ->merge($purchaseReturns)
            ->merge($vendorPayments)
            ->sortBy('date') // Sort by Date
            ->values()
            ->all();

        // Calculate running balance
        $balance = $opening;
        foreach ($transactions as $key => $t) {
            $balance = $balance + $t['debit'] - $t['credit'];
            $transactions[$key]['balance'] = $balance;
        }

        return view('admin_panel.dual_party.ledger', [
            'customer' => $customer,
            'opening_balance' => $opening,
            'closing_balance' => $balance,
            'transactions' => $transactions
        ]);
    }

    public function storeDual(Request $request)
    {
        $data = $request->validate([
            'customer_id'        => 'required|unique:customers',
            'name'               => 'required',
            'mobile'             => 'nullable',
            'address'            => 'nullable',
            'opening_balance'    => 'nullable|numeric',
            'balance_type'       => 'required|in:dr,cr',
        ]);

        $opening = $data['opening_balance'] ?? 0;
        
        $customerOpening = $data['balance_type'] === 'dr' ? $opening : 0;
        $vendorOpening = $data['balance_type'] === 'cr' ? $opening : 0;

        // Create Customer
        $customer = Customer::create([
            'customer_id' => $data['customer_id'],
            'customer_name' => $data['name'],
            'mobile' => $data['mobile'],
            'address' => $data['address'],
            'opening_balance' => $customerOpening,
            'customer_type' => 'Dual Party',
        ]);

        if ($customerOpening != 0) {
            CustomerLedger::create([
                'customer_id'      => $customer->id,
                'admin_or_user_id' => Auth::id(),
                'previous_balance' => 0,
                'opening_balance'  => $customerOpening,
                'closing_balance'  => $customerOpening,
            ]);
        }

        // Create Vendor
        $vendor = \App\Models\Vendor::create([
            'name' => $data['name'],
            'phone' => $data['mobile'],
            'address' => $data['address'],
            'opening_balance' => $vendorOpening,
        ]);

        if ($vendorOpening != 0) {
            \App\Models\VendorLedger::create([
                'vendor_id' => $vendor->id,
                'admin_or_user_id' => Auth::id(),
                'opening_balance' => $vendorOpening,
                'closing_balance' => $vendorOpening,
                'previous_balance' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'Dual Party (Customer & Vendor) created successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'        => 'required|unique:customers',
            'customer_name'      => 'required',
            'customer_name_ur'   => 'nullable',
            'cnic'               => 'nullable',
            'filer_type'         => 'nullable',
            'zone'               => 'nullable',
            'contact_person'     => 'nullable',
            'mobile'             => 'nullable',
            'email_address'      => 'nullable',
            'contact_person_2'   => 'nullable',
            'mobile_2'           => 'nullable',
            'email_address_2'    => 'nullable',
            'opening_balance'    => 'nullable',
            'address'            => 'nullable',
            'customer_type'      => 'nullable',
        ]);

        // Customer create
        $customer = Customer::create($data);

        // Ledger me entry agar opening balance dia gaya ho
        $opening = $data['opening_balance'] ?? 0;

        if ($opening > 0) {
            CustomerLedger::create([
                'customer_id'      => $customer->id,
                'admin_or_user_id' => Auth::id(),
                'previous_balance' => 0,
                'opening_balance'  => $opening,           // ✅ yahan set karna zaroori hai
                'closing_balance'  => $opening,
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }


    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin_panel.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $data = $request->except('_token');

        $customer->update($data);
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }


    // customer ledger start

    // Customer Ledger View
    public function customer_ledger()
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $CustomerLedgers = CustomerLedger::with('customer')
                ->where('admin_or_user_id', $userId)
                ->get();
            return view('admin_panel.customers.customer_ledger', compact('CustomerLedgers'));
        } else {
            return redirect()->back();
        }
    }
    // customer payment start


    // View all customer payments
    public function customer_payments()
    {
        $payments = CustomerPayment::with('customer')->orderByDesc('id')->get();
        $customers = Customer::all();
        return view('admin_panel.customers.customer_payments', compact('payments', 'customers'));
    }

    // Store a customer payment
    public function store_customer_payment(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0',
            'adjustment_type' => 'required|in:plus,minus',
            'payment_method' => 'nullable|string',
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $userId = Auth::id();

        // Save the payment
        CustomerPayment::create([
            'customer_id'      => $request->customer_id,
            'admin_or_user_id' => $userId,
            'amount'           => $request->amount,
            'adjustment_type'  => $request->adjustment_type,
            'payment_method'   => $request->payment_method,
            'payment_date'     => $request->payment_date,
            'note'             => $request->note,
        ]);

        // Get latest ledger record
        $ledger = CustomerLedger::where('customer_id', $request->customer_id)->latest()->first();

        if ($ledger) {
        //   $previous_b= $ledger->previous_balance = $ledger->closing_balance;
            // Calculate new balance
            $newBalance = $request->adjustment_type === 'plus'
                ? $ledger->closing_balance + $request->amount
                : $ledger->closing_balance - $request->amount;

            // Update existing ledger record only
            $ledger->update([
                'closing_balance' => $newBalance,
                'previous_balance' => $ledger->closing_balance,
            ]);
        }

        return back()->with('success', 'Payment adjusted and ledger updated.');
    }

    public function customer_payment_receipt($id)
    {
        $payment = CustomerPayment::with('customer')->findOrFail($id);
        return view('admin_panel.customers.customer_payment_receipt', compact('payment'));
    }

    public function destroy_payment($id)
    {
        $payment = CustomerPayment::findOrFail($id);

        $customerId = $payment->customer_id;
        $amount     = $payment->amount;

        // Latest ledger record for that customer
        $ledger = CustomerLedger::where('customer_id', $customerId)
            ->orderBy('id', 'desc')
            ->first();
        if ($ledger) {
            $ledger->closing_balance += $amount;
            $ledger->save();
        }

        // Delete the payment entry
        $payment->delete();

        return redirect()->back()->with('success', 'Payment deleted and customer ledger updated successfully.');
    }


    public function getByType(Request $request)
    {
        $type = $request->get('type');

        $customers = Customer::where('customer_type', $type)->get(['id', 'customer_name']);

        return response()->json(['customers' => $customers]);
    }
}
