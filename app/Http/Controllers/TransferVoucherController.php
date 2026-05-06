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
                'customer_id'            => $request->source_type == 'customer' ? $request->source_id : null,
                'vendor_id'              => $request->destination_type == 'vendor' ? $request->destination_id : null,
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
}
