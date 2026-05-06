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
        $accounts = \App\Models\Account::where('status', 1)->orderBy('title')->get();
        $payments = CustomerPayment::with('customer')->where('adjustment_type', 'minus')->orderByDesc('id')->take(50)->get();
        return view('admin_panel.vochers.payment_in', compact('customers', 'payments', 'accounts'));
    }

    public function storePaymentIn(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'account_id'  => 'required',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
        ]);

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

        // Update Account Balance
        $account = \App\Models\Account::find($request->account_id);
        if ($account) {
            $account->update([
                'opening_balance' => $account->opening_balance + $request->amount
            ]);
        }

        return redirect()->back()->with('success', 'Payment In completed! Amount received and customer balance reduced.');
    }

    public function updatePaymentIn(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
        ]);
        
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
            // First revert the old reduction (add it back), then apply the new reduction (subtract it)
            $difference = $old_amount - $new_amount; 
            $ledger->update([
                'previous_balance' => $ledger->closing_balance,
                'closing_balance' => $ledger->closing_balance + $difference,
            ]);
        }
        return redirect()->back()->with('success', 'Payment In updated successfully.');
    }

    public function destroyPaymentIn($id)
    {
        $payment = CustomerPayment::findOrFail($id);
        
        // Revert Customer Ledger
        $ledger = CustomerLedger::where('customer_id', $payment->customer_id)->latest()->first();
        if ($ledger) {
            $ledger->update([
                'previous_balance' => $ledger->closing_balance,
                'closing_balance' => $ledger->closing_balance + $payment->amount,
            ]);
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
        $accounts = \App\Models\Account::where('status', 1)->orderBy('title')->get();
        // Vendors dont explicitly use adjustment_type column strictly, but let's query raw payments we saved
        $payments = VendorPayment::with('vendor')->orderByDesc('id')->take(50)->get();
        return view('admin_panel.vochers.payment_out', compact('vendors', 'payments', 'accounts'));
    }

    public function storePaymentOut(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required',
            'account_id' => 'required',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
        ]);

        VendorPayment::create([
            'vendor_id' => $request->vendor_id,
            'account_id' => $request->account_id,
            'admin_or_user_id' => Auth::id(),
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
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

        // Update Account Balance (Money going OUT)
        $account = \App\Models\Account::find($request->account_id);
        if ($account) {
            $account->update([
                'opening_balance' => $account->opening_balance - $request->amount
            ]);
        }

        return redirect()->back()->with('success', 'Payment Out completed! Amount paid and vendor liability reduced.');
    }

    public function updatePaymentOut(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
        ]);
        
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
        return redirect()->back()->with('success', 'Payment Out updated successfully.');
    }

    public function destroyPaymentOut($id)
    {
        $payment = VendorPayment::findOrFail($id);
        
        // Revert Vendor Ledger
        $ledger = VendorLedger::where('vendor_id', $payment->vendor_id)->latest()->first();
        if ($ledger) {
            $ledger->update([
                'previous_balance' => $ledger->closing_balance,
                'closing_balance' => $ledger->closing_balance + $payment->amount,
            ]);
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
        $incomes = OtherIncome::orderByDesc('id')->take(50)->get();
        return view('admin_panel.vochers.other_income', compact('incomes', 'accounts'));
    }

    public function storeOtherIncome(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'account_id' => 'required',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
        ]);

        OtherIncome::create([
            'date' => $request->date,
            'title' => $request->title,
            'account_id' => $request->account_id,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
            'admin_or_user_id' => Auth::id(),
        ]);

        // Update Account Balance
        $account = \App\Models\Account::find($request->account_id);
        if ($account) {
            $account->update([
                'opening_balance' => $account->opening_balance + $request->amount
            ]);
        }

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
        
        // Revert Account Balance
        if ($income->account_id) {
            $account = \App\Models\Account::find($income->account_id);
            if ($account) {
                $account->update([
                    'opening_balance' => $account->opening_balance - $income->amount
                ]);
            }
        }

        $income->delete();
        return redirect()->back()->with('success', 'Other Income deleted.');
    }
}

