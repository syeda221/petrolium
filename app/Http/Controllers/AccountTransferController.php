<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\AccountTransfer;
use Illuminate\Support\Facades\DB;

class AccountTransferController extends Controller
{
    public function index()
    {
        $accounts = Account::orderBy('title')->get();
        $nextAtvid = AccountTransfer::generateInvoiceNo();
        return view('admin_panel.vochers.account_transfers', compact('accounts', 'nextAtvid'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transfer_date' => 'required|date',
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            $atvid = AccountTransfer::generateInvoiceNo();
            $amount = (float) $request->amount;

            AccountTransfer::create([
                'atvid' => $atvid,
                'transfer_date' => $request->transfer_date,
                'from_account_id' => $request->from_account_id,
                'to_account_id' => $request->to_account_id,
                'amount' => $amount,
                'remarks' => $request->remarks,
                'created_by' => auth()->id(),
            ]);

            // Deduction from Source
            $fromAcc = Account::find($request->from_account_id);
            $fromAcc->opening_balance -= $amount;
            $fromAcc->save();

            // Addition to Target
            $toAcc = Account::find($request->to_account_id);
            $toAcc->opening_balance += $amount;
            $toAcc->save();

            DB::commit();
            return redirect()->back()->with('success', 'Transfer Successful! ' . $fromAcc->title . ' balance reduced, ' . $toAcc->title . ' balance increased.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function all_account_transfers()
    {
        $vouchers = AccountTransfer::with(['fromAccount', 'toAccount'])->orderBy('id', 'desc')->get();
        return view('admin_panel.vochers.all_account_transfers', compact('vouchers'));
    }

    public function edit($id)
    {
        $voucher = AccountTransfer::with(['fromAccount', 'toAccount'])->findOrFail($id);
        $accounts = Account::orderBy('title')->get();
        return view('admin_panel.vochers.edit_account_transfer', compact('voucher', 'accounts'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'transfer_date' => 'required|date',
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:1',
        ]);

        $voucher = AccountTransfer::findOrFail($id);
        $oldAmount = (float) $voucher->amount;
        $newAmount = (float) $request->amount;

        DB::beginTransaction();
        try {
            // Reverse old effects
            $oldFrom = Account::find($voucher->from_account_id);
            $oldFrom->opening_balance += $oldAmount;
            $oldFrom->save();

            $oldTo = Account::find($voucher->to_account_id);
            $oldTo->opening_balance -= $oldAmount;
            $oldTo->save();

            // Apply new effects
            $newFrom = Account::find($request->from_account_id);
            $newFrom->opening_balance -= $newAmount;
            $newFrom->save();

            $newTo = Account::find($request->to_account_id);
            $newTo->opening_balance += $newAmount;
            $newTo->save();

            // Update voucher
            $voucher->update([
                'transfer_date' => $request->transfer_date,
                'from_account_id' => $request->from_account_id,
                'to_account_id' => $request->to_account_id,
                'amount' => $newAmount,
                'remarks' => $request->remarks,
            ]);

            DB::commit();
            return redirect()->route('voucher.history')->with('success', 'Account Transfer updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $voucher = AccountTransfer::findOrFail($id);
        $amount = (float) $voucher->amount;

        DB::beginTransaction();
        try {
            // Reverse account balances
            $fromAcc = Account::find($voucher->from_account_id);
            $fromAcc->opening_balance += $amount;
            $fromAcc->save();

            $toAcc = Account::find($voucher->to_account_id);
            $toAcc->opening_balance -= $amount;
            $toAcc->save();

            $voucher->delete();

            DB::commit();
            return redirect()->route('voucher.history')->with('success', 'Account Transfer deleted and balances reversed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function printTransfer($id)
    {
        $voucher = AccountTransfer::with(['fromAccount', 'toAccount'])->findOrFail($id);
        return view('admin_panel.vochers.account_transfer_print', compact('voucher'));
    }
}
