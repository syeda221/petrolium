<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\CustomerPayment;
use App\Models\VendorPayment;
use App\Models\CustomerLedger;
use App\Models\VendorLedger;
use App\Models\OtherIncome;
use Illuminate\Support\Facades\Auth;

class SimpleFinanceController extends Controller
{
    // ==========================================
    // PAYMENT IN (From Customer)
    // ==========================================
    public function paymentIn()
    {
        $customers = Customer::all();
        $vendors = Vendor::all();
        $accounts = \App\Models\Account::where('status', 1)->orderBy('title')->get();
        
        $c_payments = CustomerPayment::with('customer')->where('adjustment_type', 'minus')->latest()->take(30)->get()->map(function($p) {
            $p->party_name = $p->customer->customer_name ?? 'Unknown';
            $p->party_type = 'customer';
            return $p;
        });

        $v_payments = VendorPayment::with('vendor')->where('adjustment_type', 'plus')->latest()->take(30)->get()->map(function($p) {
            $p->party_name = $p->vendor->name ?? 'Unknown';
            $p->party_type = 'vendor';
            return $p;
        });

        $payments = $c_payments->concat($v_payments)->sortByDesc('created_at')->take(50);
        
        return view('admin_panel.vochers.payment_in', compact('customers', 'vendors', 'payments', 'accounts'));
    }

    public function storePaymentIn(Request $request)
    {
        $request->validate([
            'party_type'  => 'required|in:customer,vendor',
            'account_id'  => 'required',
            'amount'      => 'required|numeric|min:1',
            'payment_date' => 'required|date',
        ]);

        if ($request->party_type == 'customer') {
            $request->validate(['customer_id' => 'required']);
            CustomerPayment::create([
                'customer_id'      => $request->customer_id,
                'account_id'       => $request->account_id,
                'admin_or_user_id' => Auth::id(),
                'amount'           => $request->amount,
                'adjustment_type'  => 'minus',
                'payment_method'   => 'Account',
                'payment_date'     => $request->payment_date,
                'note'             => $request->remarks,
            ]);

            // Update Customer Ledger
            $ledger = CustomerLedger::where('customer_id', $request->customer_id)->latest()->first();
            if ($ledger) {
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance - $request->amount,
                ]);
            }
        } else {
            $request->validate(['vendor_id' => 'required']);
            VendorPayment::create([
                'vendor_id'        => $request->vendor_id,
                'account_id'       => $request->account_id,
                'admin_or_user_id' => Auth::id(),
                'amount'           => $request->amount,
                'adjustment_type'  => 'plus', // Money received FROM vendor (refund/return)
                'payment_method'   => 'Account',
                'payment_date'     => $request->payment_date,
                'note'             => $request->remarks,
            ]);

            // Update Vendor Ledger (Liability increases because they gave us money)
            $ledger = VendorLedger::where('vendor_id', $request->vendor_id)->latest()->first();
            if ($ledger) {
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance + $request->amount,
                ]);
            }
        }

        // Update Account Balance (Money coming IN)
        $account = \App\Models\Account::find($request->account_id);
        if ($account) {
            $account->update([
                'opening_balance' => $account->opening_balance + $request->amount
            ]);
        }

        return redirect()->back()->with('success', 'Payment In completed! Amount received and balance adjusted.');
    }

    public function updatePaymentIn(Request $request, $id, $type)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
        ]);
        
        if ($type == 'customer') {
            $payment = CustomerPayment::findOrFail($id);
            $old_amount = $payment->amount;
            $new_amount = $request->amount;

            $payment->update([
                'amount'       => $new_amount,
                'payment_date' => $request->payment_date,
                'note'         => $request->remarks,
            ]);

            // Re-adjust ledger
            $ledger = CustomerLedger::where('customer_id', $payment->customer_id)->latest()->first();
            if ($ledger) {
                $difference = $old_amount - $new_amount; 
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance + $difference,
                ]);
            }
        } else {
            $payment = VendorPayment::findOrFail($id);
            $old_amount = $payment->amount;
            $new_amount = $request->amount;

            $payment->update([
                'amount'       => $new_amount,
                'payment_date' => $request->payment_date,
                'note'         => $request->remarks,
            ]);

            // Re-adjust ledger
            $ledger = VendorLedger::where('vendor_id', $payment->vendor_id)->latest()->first();
            if ($ledger) {
                $difference = $old_amount - $new_amount; 
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance - $difference,
                ]);
            }
        }
        return redirect()->back()->with('success', 'Payment In updated successfully.');
    }

    public function destroyPaymentIn($id, $type)
    {
        if ($type == 'customer') {
            $payment = CustomerPayment::findOrFail($id);
            
            // Revert Customer Ledger
            $ledger = CustomerLedger::where('customer_id', $payment->customer_id)->latest()->first();
            if ($ledger) {
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance + $payment->amount,
                ]);
            }
        } else {
            $payment = VendorPayment::findOrFail($id);
            
            // Revert Vendor Ledger
            $ledger = VendorLedger::where('vendor_id', $payment->vendor_id)->latest()->first();
            if ($ledger) {
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance - $payment->amount,
                ]);
            }
        }

        // Revert Account Balance
        if ($payment->account_id) {
            $account = \App\Models\Account::find($payment->account_id);
            if ($account) {
                $account->update([
                    'opening_balance' => $account->opening_balance - $payment->amount
                ]);
            }
        }

        $payment->delete();
        return redirect()->back()->with('success', 'Payment In deleted & balances reverted.');
    }


    // ==========================================
    // PAYMENT OUT (To Vendor)
    // ==========================================
    public function paymentOut()
    {
        $vendors = Vendor::all();
        $customers = Customer::all();
        $accounts = \App\Models\Account::where('status', 1)->orderBy('title')->get();
        
        $v_payments = VendorPayment::with('vendor')->latest()->take(30)->get()->map(function($p) {
            $p->party_name = $p->vendor->name ?? 'Unknown';
            $p->party_type = 'vendor';
            return $p;
        });

        $c_payments = CustomerPayment::with('customer')->where('adjustment_type', 'plus')->latest()->take(30)->get()->map(function($p) {
            $p->party_name = $p->customer->customer_name ?? 'Unknown';
            $p->party_type = 'customer';
            return $p;
        });

        $payments = $v_payments->concat($c_payments)->sortByDesc('created_at')->take(50);
        
        return view('admin_panel.vochers.payment_out', compact('vendors', 'customers', 'payments', 'accounts'));
    }

    public function storePaymentOut(Request $request)
    {
        $request->validate([
            'party_type' => 'required|in:vendor,customer',
            'account_id' => 'required',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
        ]);

        if ($request->party_type == 'vendor') {
            $request->validate(['vendor_id' => 'required']);
            VendorPayment::create([
                'vendor_id' => $request->vendor_id,
                'account_id' => $request->account_id,
                'admin_or_user_id' => Auth::id(),
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'adjustment_type' => 'minus',
                'payment_method' => 'Account',
                'note' => $request->remarks,
            ]);
            
            // Update Vendor Ledger
            $ledger = VendorLedger::where('vendor_id', $request->vendor_id)->latest()->first();
            if ($ledger) {
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance - $request->amount,
                ]);
            }
        } else {
            $request->validate(['customer_id' => 'required']);
            CustomerPayment::create([
                'customer_id'      => $request->customer_id,
                'account_id'       => $request->account_id,
                'admin_or_user_id' => Auth::id(),
                'amount'           => $request->amount,
                'adjustment_type'  => 'plus',
                'payment_method'   => 'Account',
                'payment_date'     => $request->payment_date,
                'note'             => $request->remarks,
            ]);

            // Update Customer Ledger
            $ledger = CustomerLedger::where('customer_id', $request->customer_id)->latest()->first();
            if ($ledger) {
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance + $request->amount,
                ]);
            }
        }
        
        // Update Account Balance (Money going OUT)
        $account = \App\Models\Account::find($request->account_id);
        if ($account) {
            $account->update([
                'opening_balance' => $account->opening_balance - $request->amount
            ]);
        }

        return redirect()->back()->with('success', 'Payment Out completed successfully!');
    }

    public function updatePaymentOut(Request $request, $id, $type)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
        ]);
        
        if ($type == 'vendor') {
            $payment = VendorPayment::findOrFail($id);
            $old_amount = $payment->amount;
            $new_amount = $request->amount;

            $payment->update([
                'amount'       => $new_amount,
                'payment_date' => $request->payment_date,
                'note'         => $request->remarks,
            ]);

            $ledger = VendorLedger::where('vendor_id', $payment->vendor_id)->latest()->first();
            if ($ledger) {
                $difference = $old_amount - $new_amount; 
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance + $difference,
                ]);
            }
        } else {
            $payment = CustomerPayment::findOrFail($id);
            $old_amount = $payment->amount;
            $new_amount = $request->amount;

            $payment->update([
                'amount'       => $new_amount,
                'payment_date' => $request->payment_date,
                'note'         => $request->remarks,
            ]);

            $ledger = CustomerLedger::where('customer_id', $payment->customer_id)->latest()->first();
            if ($ledger) {
                $difference = $old_amount - $new_amount; 
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance - $difference,
                ]);
            }
        }
        
        return redirect()->back()->with('success', 'Payment Out updated successfully.');
    }

    public function destroyPaymentOut($id, $type)
    {
        if ($type == 'vendor') {
            $payment = VendorPayment::findOrFail($id);
            
            // Revert Vendor Ledger
            $ledger = VendorLedger::where('vendor_id', $payment->vendor_id)->latest()->first();
            if ($ledger) {
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance + $payment->amount,
                ]);
            }
        } else {
            $payment = CustomerPayment::findOrFail($id);

            // Revert Customer Ledger
            $ledger = CustomerLedger::where('customer_id', $payment->customer_id)->latest()->first();
            if ($ledger) {
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance - $payment->amount,
                ]);
            }
        }
        
        // Revert Account Balance
        if ($payment->account_id) {
            $account = \App\Models\Account::find($payment->account_id);
            if ($account) {
                $account->update([
                    'opening_balance' => $account->opening_balance + $payment->amount
                ]);
            }
        }

        $payment->delete();
        return redirect()->back()->with('success', 'Payment Out deleted & balances reverted.');
    }


    // ==========================================
    // OTHER INCOME
    // ==========================================
    public function otherIncome()
    {
        $accounts = \App\Models\Account::where('status', 1)->orderBy('title')->get();
        $vendors = Vendor::all();
        $customers = Customer::all();
        $incomes = OtherIncome::orderByDesc('id')->take(50)->get()->map(function($inc) {
            if ($inc->party_type == 'vendor') {
                $inc->deposit_to = Vendor::find($inc->vendor_id)->name ?? 'Unknown Vendor';
            } elseif ($inc->party_type == 'customer') {
                $inc->deposit_to = Customer::find($inc->customer_id)->customer_name ?? 'Unknown Customer';
            } else {
                $inc->deposit_to = \App\Models\Account::find($inc->account_id)->title ?? 'Unknown Account';
            }
            return $inc;
        });
        return view('admin_panel.vochers.other_income', compact('incomes', 'accounts', 'vendors', 'customers'));
    }

    public function storeOtherIncome(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'title' => 'required|string',
            'party_type' => 'required|in:account,vendor,customer',
            'amount' => 'required|numeric|min:1',
        ]);

        $data = [
            'date' => $request->date,
            'title' => $request->title,
            'party_type' => $request->party_type,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
            'admin_or_user_id' => Auth::id(),
        ];

        if ($request->party_type == 'account') {
            $request->validate(['account_id' => 'required']);
            $data['account_id'] = $request->account_id;

            // Update Account Balance
            $account = \App\Models\Account::find($request->account_id);
            if ($account) {
                $account->update([
                    'opening_balance' => $account->opening_balance + $request->amount
                ]);
            }
        } elseif ($request->party_type == 'vendor') {
            $request->validate(['vendor_id' => 'required']);
            $data['vendor_id'] = $request->vendor_id;

            // Update Vendor Ledger (Debit - Liability decreases)
            $ledger = VendorLedger::where('vendor_id', $request->vendor_id)->latest()->first();
            if ($ledger) {
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance - $request->amount,
                ]);
            }
        } else {
            $request->validate(['customer_id' => 'required']);
            $data['customer_id'] = $request->customer_id;

            // Update Customer Ledger (Debit - Receivable increases)
            $ledger = CustomerLedger::where('customer_id', $request->customer_id)->latest()->first();
            if ($ledger) {
                $ledger->update([
                    'previous_balance' => $ledger->closing_balance,
                    'closing_balance' => $ledger->closing_balance + $request->amount,
                ]);
            }
        }

        OtherIncome::create($data);

        return redirect()->back()->with('success', 'Other Income recorded successfully.');
    }

    public function updateOtherIncome(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
        ]);
        OtherIncome::findOrFail($id)->update($request->all());
        return redirect()->back()->with('success', 'Other Income updated successfully.');
    }

    public function destroyOtherIncome($id)
    {
        $income = OtherIncome::findOrFail($id);
        
        // Revert Balance
        if ($income->party_type == 'account') {
            if ($income->account_id) {
                $account = \App\Models\Account::find($income->account_id);
                if ($account) {
                    $account->update([
                        'opening_balance' => $account->opening_balance - $income->amount
                    ]);
                }
            }
        } elseif ($income->party_type == 'vendor') {
            if ($income->vendor_id) {
                $ledger = VendorLedger::where('vendor_id', $income->vendor_id)->latest()->first();
                if ($ledger) {
                    $ledger->update([
                        'previous_balance' => $ledger->closing_balance,
                        'closing_balance' => $ledger->closing_balance + $income->amount,
                    ]);
                }
            }
        } elseif ($income->party_type == 'customer') {
            if ($income->customer_id) {
                $ledger = CustomerLedger::where('customer_id', $income->customer_id)->latest()->first();
                if ($ledger) {
                    $ledger->update([
                        'previous_balance' => $ledger->closing_balance,
                        'closing_balance' => $ledger->closing_balance - $income->amount,
                    ]);
                }
            }
        }

        $income->delete();
        return redirect()->back()->with('success', 'Other Income deleted.');
    }
}

