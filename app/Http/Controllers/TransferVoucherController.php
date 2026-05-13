<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransferVoucherController extends Controller
{
    public function index()
    {
        $customers = \App\Models\Customer::all();
        $vendors = \App\Models\Vendor::all();
        $nextTvid = \App\Models\TransferVoucher::generateInvoiceNo();
        return view('admin_panel.vochers.transfer_vouchers', compact('customers', 'vendors', 'nextTvid'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transfer_date'     => 'required|date',
            'source_type'       => 'required|in:customer,vendor',
            'source_id'         => 'required',
            'destination_type'  => 'required|in:customer,vendor',
            'destination_id'    => 'required',
            'amount'            => 'required|numeric|min:1',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $tvid = \App\Models\TransferVoucher::generateInvoiceNo();
            $amount = (float) $request->amount;

            $transfer = \App\Models\TransferVoucher::create([
                'tvid'                   => $tvid,
                'transfer_date'          => $request->transfer_date,
                'source_party_type'      => $request->source_type,
                'source_party_id'        => $request->source_id,
                'destination_party_type' => $request->destination_type,
                'destination_party_id'   => $request->destination_id,
                'amount'                 => $amount,
                'remarks'                => $request->remarks,
                'created_by'             => auth()->id(),
                // Keep old columns for compatibility if needed, though we will use new ones mostly
                'customer_id'            => $request->source_type == 'customer' ? $request->source_id : ($request->destination_type == 'customer' ? $request->destination_id : null),
                'vendor_id'              => $request->source_type == 'vendor' ? $request->source_id : ($request->destination_type == 'vendor' ? $request->destination_id : null),
            ]);

            // --- 1. Source Party (Effected) ---
            if ($request->source_type == 'customer') {
                $ledger = \App\Models\CustomerLedger::where('customer_id', $request->source_id)->latest()->first();
                $prev = $ledger ? (float)$ledger->closing_balance : 0;
                $closing = $prev - $amount; // Credit (Paid us)
                \App\Models\CustomerLedger::create([
                    'customer_id'      => $request->source_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => $prev,
                    'opening_balance'  => 0,
                    'closing_balance'  => $closing,
                ]);
            } else {
                $ledger = \App\Models\VendorLedger::where('vendor_id', $request->source_id)->latest()->first();
                $prev = $ledger ? (float)$ledger->closing_balance : 0;
                $closing = $prev + $amount; // Debit (Increased liability - we took from them)
                \App\Models\VendorLedger::create([
                    'vendor_id'        => $request->source_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => $prev,
                    'opening_balance'  => 0,
                    'closing_balance'  => $closing,
                ]);
            }

            // --- 2. Destination Party (Effected) ---
            if ($request->destination_type == 'customer') {
                $ledger = \App\Models\CustomerLedger::where('customer_id', $request->destination_id)->latest()->first();
                $prev = $ledger ? (float)$ledger->closing_balance : 0;
                $closing = $prev + $amount; // Debit (Increased receivable - we gave to them)
                \App\Models\CustomerLedger::create([
                    'customer_id'      => $request->destination_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => $prev,
                    'opening_balance'  => 0,
                    'closing_balance'  => $closing,
                ]);
            } else {
                $ledger = \App\Models\VendorLedger::where('vendor_id', $request->destination_id)->latest()->first();
                $prev = $ledger ? (float)$ledger->closing_balance : 0;
                $closing = $prev - $amount; // Credit (Payment - we paid them)
                \App\Models\VendorLedger::create([
                    'vendor_id'        => $request->destination_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => $prev,
                    'opening_balance'  => 0,
                    'closing_balance'  => $closing,
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->back()->with('success', 'Transfer Voucher created successfully! Both parties ledgers have been updated.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function all_transfer_vouchers()
    {
        $vouchers = \App\Models\TransferVoucher::orderBy('id', 'desc')->get();
        return view('admin_panel.vochers.all_transfer_vouchers', compact('vouchers'));
    }

    public function edit($id)
    {
        $voucher = \App\Models\TransferVoucher::findOrFail($id);
        $customers = \App\Models\Customer::all();
        $vendors = \App\Models\Vendor::all();
        return view('admin_panel.vochers.edit_transfer_voucher', compact('voucher', 'customers', 'vendors'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'transfer_date'     => 'required|date',
            'source_type'       => 'required|in:customer,vendor',
            'source_id'         => 'required',
            'destination_type'  => 'required|in:customer,vendor',
            'destination_id'    => 'required',
            'amount'            => 'required|numeric|min:1',
        ]);

        $voucher = \App\Models\TransferVoucher::findOrFail($id);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $oldAmount = (float) $voucher->amount;
            $newAmount = (float) $request->amount;

            // 1. REVERSE OLD IMPACTS
            // Reverse Old Source
            if ($voucher->source_party_type == 'customer') {
                $ledger = \App\Models\CustomerLedger::where('customer_id', $voucher->source_party_id)->latest()->first();
                if ($ledger) { $ledger->update(['closing_balance' => $ledger->closing_balance + $oldAmount]); }
            } else {
                $ledger = \App\Models\VendorLedger::where('vendor_id', $voucher->source_party_id)->latest()->first();
                if ($ledger) { $ledger->update(['closing_balance' => $ledger->closing_balance - $oldAmount]); }
            }

            // Reverse Old Destination
            if ($voucher->destination_party_type == 'customer') {
                $ledger = \App\Models\CustomerLedger::where('customer_id', $voucher->destination_party_id)->latest()->first();
                if ($ledger) { $ledger->update(['closing_balance' => $ledger->closing_balance - $oldAmount]); }
            } else {
                $ledger = \App\Models\VendorLedger::where('vendor_id', $voucher->destination_party_id)->latest()->first();
                if ($ledger) { $ledger->update(['closing_balance' => $ledger->closing_balance + $oldAmount]); }
            }

            // 2. APPLY NEW IMPACTS
            // Apply New Source
            if ($request->source_type == 'customer') {
                $ledger = \App\Models\CustomerLedger::where('customer_id', $request->source_id)->latest()->first();
                $prev = $ledger ? (float)$ledger->closing_balance : 0;
                $closing = $prev - $newAmount;
                \App\Models\CustomerLedger::create([
                    'customer_id'      => $request->source_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => $prev,
                    'opening_balance'  => 0,
                    'closing_balance'  => $closing,
                ]);
            } else {
                $ledger = \App\Models\VendorLedger::where('vendor_id', $request->source_id)->latest()->first();
                $prev = $ledger ? (float)$ledger->closing_balance : 0;
                $closing = $prev + $newAmount;
                \App\Models\VendorLedger::create([
                    'vendor_id'        => $request->source_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => $prev,
                    'opening_balance'  => 0,
                    'closing_balance'  => $closing,
                ]);
            }

            // Apply New Destination
            if ($request->destination_type == 'customer') {
                $ledger = \App\Models\CustomerLedger::where('customer_id', $request->destination_id)->latest()->first();
                $prev = $ledger ? (float)$ledger->closing_balance : 0;
                $closing = $prev + $newAmount;
                \App\Models\CustomerLedger::create([
                    'customer_id'      => $request->destination_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => $prev,
                    'opening_balance'  => 0,
                    'closing_balance'  => $closing,
                ]);
            } else {
                $ledger = \App\Models\VendorLedger::where('vendor_id', $request->destination_id)->latest()->first();
                $prev = $ledger ? (float)$ledger->closing_balance : 0;
                $closing = $prev - $newAmount;
                \App\Models\VendorLedger::create([
                    'vendor_id'        => $request->destination_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => $prev,
                    'opening_balance'  => 0,
                    'closing_balance'  => $closing,
                ]);
            }

            // 3. UPDATE VOUCHER
            $voucher->update([
                'transfer_date'          => $request->transfer_date,
                'source_party_type'      => $request->source_type,
                'source_party_id'        => $request->source_id,
                'destination_party_type' => $request->destination_type,
                'destination_party_id'   => $request->destination_id,
                'amount'                 => $newAmount,
                'remarks'                => $request->remarks,
                // Update old columns for compatibility
                'customer_id'            => $request->source_type == 'customer' ? $request->source_id : ($request->destination_type == 'customer' ? $request->destination_id : null),
                'vendor_id'              => $request->source_type == 'vendor' ? $request->source_id : ($request->destination_type == 'vendor' ? $request->destination_id : null),
            ]);

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('transfer-vouchers.all')->with('success', 'Transfer Voucher updated successfully! Ledger balances have been adjusted.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
