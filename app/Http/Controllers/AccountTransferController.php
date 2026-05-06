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
}
