<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountHead;
use App\Models\AccountTransfer;
use App\Models\CustomerLedger;
use App\Models\CustomerPayment;
use App\Models\ExpenseVoucher;
use App\Models\OtherIncome;
use App\Models\TransferVoucher;
use App\Models\VendorPayment;
use App\Models\Voucher;
use Illuminate\Http\Request;
use App\Models\Narration;
use App\Models\PaymentVoucher;
use App\Models\ReceiptsVoucher;
use App\Models\VendorLedger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{

    public function createVoucher()
    {
        $AccountHeads = AccountHead::get();
        $accounts = Account::orderBy('title')->get();
        $ExpenseAccounts = \App\Models\ExpenseCategory::orderBy('title')->get();
        $customers = \App\Models\Customer::all();
        $vendors = \App\Models\Vendor::all();
        $narrations = \App\Models\Narration::pluck('narration', 'id');

        $lastRvid = \App\Models\ReceiptsVoucher::latest('id')->first();
        $nextRvid = 'RVID-' . str_pad(($lastRvid ? $lastRvid->id + 1 : 1), 3, '0', STR_PAD_LEFT);

        $lastPvid = \App\Models\PaymentVoucher::latest('id')->first();
        $nextPvid = 'PVID-' . str_pad(($lastPvid ? $lastPvid->id + 1 : 1), 3, '0', STR_PAD_LEFT);

        $lastEvid = \App\Models\ExpenseVoucher::latest('id')->first();
        $nextEvid = 'EVID-' . str_pad(($lastEvid ? $lastEvid->id + 1 : 1), 3, '0', STR_PAD_LEFT);

        $nextTvid = \App\Models\TransferVoucher::generateInvoiceNo();
        $nextAtvid = \App\Models\AccountTransfer::generateInvoiceNo();

        return view('admin_panel.vochers.create_voucher', compact(
            'AccountHeads', 'accounts', 'ExpenseAccounts',
            'customers', 'vendors', 'narrations',
            'nextRvid', 'nextPvid', 'nextEvid', 'nextTvid', 'nextAtvid'
        ));
    }

    public function all_recepit_vochers()
    {
        $receipts = \App\Models\ReceiptsVoucher::orderBy('id', 'DESC')->get();

        foreach ($receipts as $voucher) {
            $partyName = '-';
            $typeLabel = '-';

            // 🧩 Check if type is numeric → account-based
            if (is_numeric($voucher->type)) {
                $accountHead = DB::table('account_heads')->where('id', $voucher->type)->first();
                $account = DB::table('accounts')->where('id', $voucher->party_id)->first();

                $typeLabel = $accountHead->name ?? 'Account';
                $partyName = $account->title ?? '-';
            } elseif ($voucher->type === 'vendor') {
                $vendor = DB::table('vendors')->where('id', $voucher->party_id)->first();
                $typeLabel = 'Vendor';
                $partyName = $vendor->name ?? '-';
            } elseif ($voucher->type === 'customer') {
                $customer = DB::table('customers')->where('id', $voucher->party_id)->first();
                $typeLabel = 'Customer';
                $partyName = $customer->customer_name ?? '-';
            } elseif ($voucher->type === 'walkin') {
                $walkin = DB::table('customers')
                    ->where('id', $voucher->party_id)
                    ->where('customer_type', 'Walking Customer')
                    ->first();
                $typeLabel = 'Walk-in';
                $partyName = $walkin->customer_name ?? '-';
            }

            // Attach new properties to the object
            $voucher->type_label = $typeLabel;
            $voucher->party_name = $partyName;
        }

        return view('admin_panel.vochers.all_recepit_vochers', compact('receipts'));
    }

    public function recepit_vochers()
    {
        $narrations = \App\Models\Narration::where('expense_head', 'Receipts Voucher')
            ->pluck('narration', 'id');
        $AccountHeads = AccountHead::get();

        // Last RVID nikalna
        $lastVoucher = \App\Models\ReceiptsVoucher::latest('id')->first();

        // Next ID generate karna
        $nextId = $lastVoucher ? $lastVoucher->id + 1 : 1;
        $nextRvid = 'RVID-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.reciepts_vouchers', compact('narrations', 'AccountHeads', 'nextRvid'));
    }

    public function store_rec_vochers(Request $request)
    {
        DB::beginTransaction();
        try {
            $rvid = ReceiptsVoucher::generateInvoiceNo();

            $narrationIds = [];

            foreach ($request->narration_id as $index => $narrId) {
                $manualText = $request->narration_text[$index] ?? null;
                $manualType = $request->narration_type_text[$index] ?? 'Manual';

                if (empty($narrId) && !empty($manualText)) {
                    // Auto expense_head set based on voucher type
                    $expenseHead = 'Receipts Voucher';
                    if (stripos($manualType, 'Receipt') !== false || $request->voucher_type == 'receipt') {
                        $expenseHead = 'Receipts Voucher';
                    }

                    $new = \App\Models\Narration::create([
                        'expense_head' => $expenseHead,
                        'narration'    => $manualText,
                    ]);

                    $narrationIds[] = (string)$new->id; // store as string → ["7"]
                } else {
                    $narrationIds[] = (string)$narrId; // force string format
                }
            }

            $voucherData = [
                'rvid'             => $rvid,
                'receipt_date'     => $request->receipt_date,
                'entry_date'       => $request->entry_date,
                'type'             => $request->vendor_type,
                'party_id'         => $request->vendor_id,
                'tel'              => $request->tel,
                'remarks'          => $request->remarks,

                'narration_id' => json_encode($narrationIds),
                'reference_no'     => json_encode($request->reference_no),
                'row_account_head' => json_encode($request->row_account_head),
                'row_account_id'   => json_encode($request->row_account_id),
                'discount_value'   => json_encode($request->discount_value),
                'kg'               => json_encode($request->kg),
                'rate'             => json_encode($request->rate),
                'amount'           => json_encode($request->amount),
                'total_amount'     => $request->total_amount,
            ];

            ReceiptsVoucher::create($voucherData);

            // ✅ Ledger update logic
            $amount = (float)$request->total_amount;

            if ($request->vendor_type === 'vendor') {
                $ledger = VendorLedger::where('vendor_id', $request->vendor_id)->latest()->first();

                if ($ledger) {
                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance  = $ledger->closing_balance - $amount;
                    $ledger->save();
                } else {
                    VendorLedger::create([
                        'vendor_id'        => $request->vendor_id,
                        'admin_or_user_id' => auth()->id(),
                        'date'             => now(),
                        'description'      => "Receipt Voucher #$rvid",
                        'opening_balance'  => 0,
                        'debit'            => 0,
                        'credit'           => $amount,
                        'previous_balance' => 0,
                        'closing_balance'  => -$amount,
                    ]);
                }
            } elseif ($request->vendor_type === 'customer') {
                $ledger = CustomerLedger::where('customer_id', $request->vendor_id)->latest()->first();
                if ($ledger) {
                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance  = $ledger->closing_balance - $amount;
                    $ledger->save();
                } else {
                    CustomerLedger::create([
                        'customer_id'      => $request->vendor_id,
                        'admin_or_user_id' => auth()->id(),
                        'previous_balance' => 0,
                        'opening_balance'  => 0,
                        'closing_balance'  => -$amount,
                    ]);
                }
            } else {
                // Bank/Head case → pehle vendor/account side minus
                $account = Account::find($request->vendor_id);
                if ($account) {
                    $account->opening_balance = $account->opening_balance - $amount;
                    $account->save();
                }
            }

            // ✅ Har case me row_account_id ka + hona zaroori hai
            if ($request->row_account_id && $request->amount) {
                foreach ($request->row_account_id as $index => $accId) {
                    $rowAmount = isset($request->amount[$index]) ? (float)$request->amount[$index] : 0;

                    if ($rowAmount > 0) {
                        $rowAccount = Account::find($accId);
                        if ($rowAccount) {
                            $rowAccount->opening_balance = $rowAccount->opening_balance + $rowAmount;
                            $rowAccount->save();
                        }
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Receipt Voucher saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function print($id)
    {
        // Find voucher or fail
        $voucher = ReceiptsVoucher::findOrFail($id);

        // --- Setup display datetime (single source for date & time) ---
        $tz = config('app.timezone') ?: 'Asia/Karachi';

        $receipt = $voucher->receipt_date;
        $created = $voucher->created_at;
        $updated = $voucher->updated_at;
        $entry = $voucher->entry_date; // might be date-only (Y-m-d)

        if (!empty($receipt)) {
            try {
                $date = Carbon::parse($receipt)->toDateString(); // only date
                $time = !empty($created)
                    ? Carbon::parse($created)->toTimeString()
                    : '00:00:00';

                $dt = Carbon::parse("$date $time");
            } catch (\Exception $e) {
                $dt = !empty($created) ? Carbon::parse($created) : Carbon::now();
            }
        } elseif (!empty($created)) {
            $dt = Carbon::parse($created);
        } elseif (!empty($updated)) {
            $dt = Carbon::parse($updated);
        } elseif (!empty($entry)) {
            // entry_date may be date-only. Try to combine with created_at time if available.
            try {
                if (!empty($created)) {
                    $datePart = Carbon::parse($entry)->toDateString();
                    $timePart = Carbon::parse($created)->toTimeString();
                    $dt = Carbon::parse($datePart . ' ' . $timePart);
                } else {
                    // will be start of day 00:00:00
                    $dt = Carbon::parse($entry);
                }
            } catch (\Exception $e) {
                $dt = Carbon::now();
            }
        } else {
            $dt = Carbon::now();
        }

        // Convert to app timezone for display
        try {
            $dt = $dt->setTimezone($tz);
        } catch (\Exception $e) {
            // fallback
            $dt = $dt->setTimezone('Asia/Karachi');
        }

        // Attach for view use
        $voucher->display_datetime = $dt;

        // --- Decode JSON arrays safely ---
        $narrations   = is_string($voucher->narration_id) ? json_decode($voucher->narration_id, true) : ($voucher->narration_id ?? []);
        $references   = is_string($voucher->reference_no) ? json_decode($voucher->reference_no, true) : ($voucher->reference_no ?? []);
        $accountHeads = is_string($voucher->row_account_head) ? json_decode($voucher->row_account_head, true) : ($voucher->row_account_head ?? []);
        $accounts     = is_string($voucher->row_account_id) ? json_decode($voucher->row_account_id, true) : ($voucher->row_account_id ?? []);
        $amounts      = is_string($voucher->amount) ? json_decode($voucher->amount, true) : ($voucher->amount ?? []);

        // Ensure arrays
        $narrations   = is_array($narrations) ? $narrations : [];
        $references   = is_array($references) ? $references : [];
        $accountHeads = is_array($accountHeads) ? $accountHeads : [];
        $accounts     = is_array($accounts) ? $accounts : [];
        $amounts      = is_array($amounts) ? $amounts : [];

        // --- Build rows for items table ---
        $rows = [];
        foreach ($narrations as $index => $narrId) {
            $narration = null;
            if (!empty($narrId)) {
                $narration = DB::table('narrations')->where('id', $narrId)->value('narration');
            }

            $ref = $references[$index] ?? null;

            $accountHeadName = null;
            if (!empty($accountHeads[$index])) {
                $accountHeadName = DB::table('account_heads')->where('id', $accountHeads[$index])->value('name');
            }

            $accountObj = null;
            if (!empty($accounts[$index])) {
                $accountObj = DB::table('accounts')->where('id', $accounts[$index])->first();
            }

            $amount = (float) ($amounts[$index] ?? 0);

            $rows[] = [
                'narration'     => $narration,
                'reference'     => $ref,
                'account_head'  => $accountHeadName,
                'account_name'  => $accountObj->title ?? null,
                'account_code'  => $accountObj->account_code ?? null,
                'amount'        => $amount,
            ];
        }

        // --- Party (depends on voucher->type) ---
        $party = null;
        $previousBalance = 0;

        // If type is numeric — treat as account head id (legacy pattern you used)
        if (is_numeric($voucher->type)) {
            $accountHead = DB::table('account_heads')->where('id', $voucher->type)->first();
            $account = DB::table('accounts')->where('id', $voucher->party_id)->first();

            if ($account) {
                $party = (object)[
                    'name' => $account->title ?? '—',
                    'address' => $account->address ?? '—',
                    'mobile' => $account->mobile ?? $account->phone ?? $account->account_code ?? '—',
                    'phone' => $account->phone ?? $account->mobile ?? $account->account_code ?? '—',
                    'head_name' => $accountHead->name ?? '—',
                ];
            }
        } elseif ($voucher->type === 'vendor') {
            $party = DB::table('vendors')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('vendor_ledgers')
                ->where('vendor_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;
        } elseif ($voucher->type === 'customer') {
            $party = DB::table('customers')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('customer_ledgers')
                ->where('customer_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;
        } elseif ($voucher->type === 'walkin') {
            $party = DB::table('customers')
                ->where('id', $voucher->party_id)
                ->where('customer_type', 'Walking Customer')
                ->first();
        } else {
            // fallback: try to lookup as customer or vendor
            $tryCustomer = DB::table('customers')->where('id', $voucher->party_id)->first();
            if ($tryCustomer) {
                $party = $tryCustomer;
                $previousBalance = DB::table('customer_ledgers')
                    ->where('customer_id', $voucher->party_id)
                    ->orderByDesc('id')
                    ->value('closing_balance') ?? 0;
            } else {
                $tryVendor = DB::table('vendors')->where('id', $voucher->party_id)->first();
                if ($tryVendor) {
                    $party = $tryVendor;
                    $previousBalance = DB::table('vendor_ledgers')
                        ->where('vendor_id', $voucher->party_id)
                        ->orderByDesc('id')
                        ->value('closing_balance') ?? 0;
                }
            }
        }

        // Make sure previousBalance is numeric
        $previousBalance = is_numeric($previousBalance) ? (float)$previousBalance : 0.0;

        // Pass everything to view
        return view('admin_panel.vochers.print', compact(
            'voucher',
            'rows',
            'party',
            'previousBalance'
        ));
    }
    // Payment vocher
    public function Payment_vochers()
    {
        $narrations = \App\Models\Narration::where('expense_head', 'Payment voucher')
            ->pluck('narration', 'id');
        $AccountHeads = AccountHead::get();

        // Last RVID nikalna
        $lastVoucher = \App\Models\PaymentVoucher::latest('id')->first();

        // Next ID generate karna
        $nextId = $lastVoucher ? $lastVoucher->id + 1 : 1;
        $nextPVID = 'PVID-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.payment_vochers.payment_vouchers', compact('narrations', 'AccountHeads', 'nextPVID'));
    }

    public function store_Pay_vochers(Request $request)
    {
        DB::beginTransaction();
        try {
            $pvid = PaymentVoucher::generateInvoiceNo();
            $narrationIds = [];

            foreach ($request->narration_id as $index => $narrId) {
                $manualText = $request->narration_text[$index] ?? null;
                $manualType = $request->narration_type_text[$index] ?? 'Manual';

                if (empty($narrId) && !empty($manualText)) {
                    // Auto expense_head set based on voucher type
                    $expenseHead = 'Payment voucher';
                    if (stripos($manualType, 'Receipt') !== false || $request->voucher_type == 'receipt') {
                        $expenseHead = 'Payment voucher';
                    }

                    $new = \App\Models\Narration::create([
                        'expense_head' => $expenseHead,
                        'narration'    => $manualText,
                    ]);

                    $narrationIds[] = (string)$new->id; // store as string → ["7"]
                } else {
                    $narrationIds[] = (string)$narrId; // force string format
                }
            }
            $voucherData = [
                'pvid'             => $pvid,
                'receipt_date'     => $request->receipt_date,
                'entry_date'       => $request->entry_date,
                'type'             => $request->vendor_type,
                'party_id'         => $request->vendor_id,
                'tel'              => $request->tel,
                'remarks'          => $request->remarks,
                'narration_id' => json_encode($narrationIds),
                'reference_no'     => json_encode($request->reference_no),
                'row_account_head' => json_encode($request->row_account_head),
                'row_account_id'   => json_encode($request->row_account_id),
                'discount_value'   => json_encode($request->discount_value),
                'kg'               => json_encode($request->kg),
                'rate'             => json_encode($request->rate),
                'amount'           => json_encode($request->amount),
                'total_amount'     => $request->total_amount,
            ];

            PaymentVoucher::create($voucherData);

            $amount = (float)$request->total_amount;
            /**
             * STEP 1: Row accounts → MINUS (opposite of receipt voucher)
             */
            if ($request->row_account_id && $request->amount) {
                foreach ($request->row_account_id as $index => $accId) {
                    $rowAmount = isset($request->amount[$index]) ? (float)$request->amount[$index] : 0;

                    if ($rowAmount > 0) {
                        $rowAccount = Account::find($accId);
                        if ($rowAccount) {
                            $rowAccount->opening_balance = $rowAccount->opening_balance - $rowAmount;
                            $rowAccount->save();
                        }
                    }
                }
            }

            /**
             * STEP 2: Party side (Vendor / Customer / Account Head) → PLUS
             */
            if ($request->vendor_type === 'vendor') {
                $ledger = VendorLedger::where('vendor_id', $request->vendor_id)->latest()->first();
                if ($ledger) {
                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance  = $ledger->closing_balance + $amount;
                    $ledger->save();
                } else {
                    VendorLedger::create([
                        'vendor_id'        => $request->vendor_id,
                        'admin_or_user_id' => auth()->id(),
                        'date'             => now(),
                        'description'      => "Payment Voucher #$pvid",
                        'opening_balance'  => 0,
                        'debit'            => $amount,
                        'credit'           => 0,
                        'previous_balance' => 0,
                        'closing_balance'  => $amount,
                    ]);
                }
            } elseif ($request->vendor_type === 'customer') {
                $ledger = CustomerLedger::where('customer_id', $request->vendor_id)->latest()->first();
                if ($ledger) {
                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance  = $ledger->closing_balance + $amount;
                    $ledger->save();
                } else {
                    CustomerLedger::create([
                        'customer_id'      => $request->vendor_id,
                        'admin_or_user_id' => auth()->id(),
                        'previous_balance' => 0,
                        'opening_balance'  => 0,
                        'closing_balance'  => $amount,
                    ]);
                }
            } else {
                // agar vendor_type me account head/account ki id ayi
                $account = Account::find($request->vendor_id);
                if ($account) {
                    $account->opening_balance = $account->opening_balance + $amount;
                    $account->save();
                }
            }

            DB::commit();
            return back()->with('success', 'Payment Voucher saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function all_Payment_vochers()
    {
        $receipts = PaymentVoucher::orderBy('id', 'DESC')->get();
        return view('admin_panel.vochers.payment_vochers.all_payment_vochers', compact('receipts'));
    }

    public function Paymentprint($id)
    {
        $voucher = \App\Models\PaymentVoucher::findOrFail($id);

        // Decode JSON arrays
        $narrations = json_decode($voucher->narration_id, true) ?? [];
        $references = json_decode($voucher->reference_no, true) ?? [];
        $accountHeads = json_decode($voucher->row_account_head, true) ?? [];
        $accounts = json_decode($voucher->row_account_id, true) ?? [];
        $amounts = json_decode($voucher->amount, true) ?? [];

        // 🧾 Build detailed rows
        $rows = [];
        foreach ($narrations as $index => $narrId) {
            $narration = DB::table('narrations')->where('id', $narrId)->value('narration');
            $ref = $references[$index] ?? null;
            $accountHead = DB::table('account_heads')->where('id', $accountHeads[$index] ?? null)->value('name');
            $account = DB::table('accounts')->where('id', $accounts[$index] ?? null)->first();
            $amount = (float)($amounts[$index] ?? 0);

            $rows[] = [
                'narration' => $narration,
                'reference' => $ref,
                'account_head' => $accountHead,
                'account_name' => $account->title ?? null,
                'account_code' => $account->account_code ?? null,
                'amount' => $amount,
            ];
        }

        // 🧩 Party setup — dynamic based on type
        $party = null;
        $previousBalance = 0;

        // ✅ Account Head type (numeric)
        if (is_numeric($voucher->type)) {
            $accountHead = DB::table('account_heads')->where('id', $voucher->type)->first();
            $account = DB::table('accounts')->where('id', $voucher->party_id)->first();

            if ($account) {
                $party = (object)[
                    'name' => $account->title ?? '—',
                    'address' => '—',
                    'phone' => $account->account_code ?? '—',
                    'head_name' => $accountHead->name ?? '—',
                ];
            }

            $previousBalance = $account->opening_balance ?? 0;

            // ✅ Vendor
        } elseif ($voucher->type === 'vendor') {
            $party = DB::table('vendors')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('vendor_ledgers')
                ->where('vendor_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;

            // ✅ Customer
        } elseif ($voucher->type === 'customer') {
            $party = DB::table('customers')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('customer_ledgers')
                ->where('customer_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;

            // ✅ Walking customer
        } elseif ($voucher->type === 'walkin') {
            $party = DB::table('customers')
                ->where('id', $voucher->party_id)
                ->where('customer_type', 'Walking Customer')
                ->first();
        }

        return view('admin_panel.vochers.payment_vochers.print', compact('voucher', 'rows', 'party', 'previousBalance'));
    }

    public function expense_vochers()
    {
        $AccountHeads = AccountHead::get();
        
        // Fetch source accounts (non-expense accounts, or just all accounts for flexibility)
        $SourceAccounts = Account::orderBy('title')->get();
        
        // Fetch expense categories separately
        $ExpenseAccounts = \App\Models\ExpenseCategory::orderBy('title')->get();

        // Last EVID nikalna
        $lastVoucher = \App\Models\ExpenseVoucher::latest('id')->first();

        // Next ID generate karna
        $nextId = $lastVoucher ? $lastVoucher->id + 1 : 1;
        $nextRvid = 'EVID-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.expense_vochers.expense_vouchers', compact('AccountHeads', 'nextRvid', 'SourceAccounts', 'ExpenseAccounts'));
    }

    public function store_expense_category(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);
        \App\Models\ExpenseCategory::create(['title' => $request->title]);
        return back()->with('success', 'Expense Category Added Successfully!');
    }

    public function store_expense_vochers(Request $request)
    {
        DB::beginTransaction();
        try {
            $evid = ExpenseVoucher::generateInvoiceNo();
            
            // Filter out empty rows
            $filtered_accounts = [];
            $filtered_amounts = [];
            if ($request->row_account_id && $request->amount) {
                foreach ($request->row_account_id as $index => $accId) {
                    $amt = $request->amount[$index] ?? 0;
                    if (!empty($accId) && !empty($amt) && $amt > 0) {
                        $filtered_accounts[] = $accId;
                        $filtered_amounts[] = (float)$amt;
                    }
                }
            }

            if (empty($filtered_accounts)) {
                throw new \Exception("Please select at least one expense account and enter an amount.");
            }

            $total_amount = array_sum($filtered_amounts);

            // Fetch heads for filtered accounts
            $rowAccountHeads = [];
            foreach ($filtered_accounts as $accId) {
                $acc = Account::find($accId);
                $rowAccountHeads[] = $acc->head_id ?? 1;
            }

            $voucherData = [
                'evid'             => $evid,
                'entry_date'       => $request->entry_date ?? now()->toDateString(),
                'type'             => 'account', 
                'party_id'         => $request->vendor_id, 
                'remarks'          => $request->remarks,
                'narration_id'     => json_encode([]), 
                'row_account_head' => json_encode($rowAccountHeads),
                'row_account_id'   => json_encode($filtered_accounts),
                'amount'           => json_encode($filtered_amounts),
                'total_amount'     => $total_amount,
            ];

            ExpenseVoucher::create($voucherData);

            /**
             * STEP 1: Expense Accounts (Debit side)
             */
            foreach ($filtered_accounts as $index => $accId) {
                $rowAmount = $filtered_amounts[$index];
                $rowAccount = \App\Models\ExpenseCategory::find($accId);
                if ($rowAccount) {
                    $rowAccount->update([
                        'total_amount' => $rowAccount->total_amount + $rowAmount
                    ]);
                }
            }

            /**
             * STEP 2: Source Account (Credit side)
             */
            $sourceAccount = Account::find($request->vendor_id);
            if ($sourceAccount) {
                $sourceAccount->update([
                    'opening_balance' => $sourceAccount->opening_balance - $total_amount
                ]);
            }

            DB::commit();
            return back()->with('success', 'Expense Voucher saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function all_expense_vochers()
    {
        $vouchers = ExpenseVoucher::with(['accountHeadType', 'partyAccount', 'vendor', 'customer'])->orderBy('id', 'desc')->get();
        return view('admin_panel.vochers.expense_vochers.all_expense_vochers', compact('vouchers'));
    }

    public function expenseprint($id)
    {
        $voucher = \App\Models\ExpenseVoucher::findOrFail($id);

        // Decode JSON arrays safely
        $narrations = json_decode($voucher->narration_id, true) ?? [];
        $references = json_decode($voucher->reference_no, true) ?? [];
        $accountHeads = json_decode($voucher->row_account_head, true) ?? [];
        $accounts = json_decode($voucher->row_account_id, true) ?? [];
        $amounts = json_decode($voucher->amount, true) ?? [];

        // 🧾 Prepare detailed rows
        $rows = [];
        foreach ($narrations as $index => $narrId) {
            $narration = DB::table('narrations')->where('id', $narrId)->value('narration');
            $ref = $references[$index] ?? null;
            $accountHead = 'Expense Category';
            $category = DB::table('expense_categories')->where('id', $accounts[$index] ?? null)->first();
            $amount = (float)($amounts[$index] ?? 0);

            $rows[] = [
                'narration' => $narration,
                'reference' => $ref,
                'account_head' => $accountHead,
                'account_name' => $category->title ?? null,
                'account_code' => '-',
                'amount' => $amount,
            ];
        }

        // 🧩 Party Setup Based on Type
        $party = null;
        $previousBalance = 0;

        if (is_numeric($voucher->type)) {
            // ✅ Account Head type (numeric)
            $accountHead = DB::table('account_heads')->where('id', $voucher->type)->first();
            $account = DB::table('accounts')->where('id', $voucher->party_id)->first();

            if ($account) {
                $party = (object)[
                    'name' => $account->title ?? '—',
                    'address' => '—',
                    'phone' => $account->account_code ?? '—',
                    'head_name' => $accountHead->name ?? '—',
                ];
            }

            $previousBalance = $account->opening_balance ?? 0;
        } elseif ($voucher->type === 'vendor') {
            // ✅ Vendor Type
            $party = DB::table('vendors')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('vendor_ledgers')
                ->where('vendor_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;
        } elseif ($voucher->type === 'customer') {
            // ✅ Customer Type
            $party = DB::table('customers')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('customer_ledgers')
                ->where('customer_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;
        } elseif ($voucher->type === 'walkin') {
            // ✅ Walking Customer
            $party = DB::table('customers')
                ->where('id', $voucher->party_id)
                ->where('customer_type', 'Walking Customer')
                ->first();
        }

        return view('admin_panel.vochers.expense_vochers.print', compact('voucher', 'rows', 'party', 'previousBalance'));
    }

    // Journal Voucher (Day Book)
    public function journalVouchers(Request $request)
    {
        $selectedDate = $request->get('date', now()->toDateString());
        
        // Get all transactions for the selected date
        $sales = DB::table('sales')
            ->whereDate('created_at', $selectedDate)
            ->select('created_at as date', 'total_net as amount', 'id as reference', DB::raw("'Sale' as type"))
            ->get();

        $receipts = DB::table('receipts_vouchers')
            ->whereDate('receipt_date', $selectedDate)
            ->select('receipt_date as date', 'total_amount as amount', 'rvid as reference', DB::raw("'Receipt' as type"))
            ->get();

        $payments = DB::table('payment_vouchers')
            ->whereDate('receipt_date', $selectedDate)
            ->select('receipt_date as date', 'total_amount as amount', 'pvid as reference', DB::raw("'Payment' as type"))
            ->get();

        $expenses = DB::table('expense_vouchers')
            ->whereDate('entry_date', $selectedDate)
            ->select('entry_date as date', 'total_amount as amount', 'evid as reference', DB::raw("'Expense' as type"))
            ->get();

        // Calculate totals
        $totalSales = $sales->sum('amount');
        $totalReceipts = $receipts->sum('amount');
        $totalPayments = $payments->sum('amount');
        $totalExpenses = $expenses->sum('amount');

        $totalIn = $totalSales + $totalReceipts;
        $totalOut = $totalPayments + $totalExpenses;

        // Get opening balance (previous day's closing or 0)
        $previousDay = Carbon::parse($selectedDate)->subDay()->toDateString();
        $opening = DB::table('day_closings')
            ->where('date', $previousDay)
            ->value('closing_balance') ?? 0;

        $closing = $opening + $totalIn - $totalOut;

        // Check if day is closed
        $dayClosed = DB::table('day_closings')
            ->where('date', $selectedDate)
            ->where('is_closed', true)
            ->exists();

        return view('admin_panel.vochers.journal_vouchers', compact(
            'selectedDate',
            'sales',
            'receipts',
            'payments',
            'expenses',
            'totalSales',
            'totalReceipts',
            'totalPayments',
            'totalExpenses',
            'totalIn',
            'totalOut',
            'opening',
            'closing',
            'dayClosed'
        ));
    }

    public function closeDay(Request $request)
    {
        $date = $request->date;
        
        // Validate date
        if (!$date || $date > now()->toDateString()) {
            return back()->with('error', 'Invalid date. Cannot close future dates.');
        }

        // Check if already closed
        $existingClosure = DB::table('day_closings')
            ->where('date', $date)
            ->where('is_closed', true)
            ->first();

        if ($existingClosure) {
            return back()->with('error', 'This day has already been closed!');
        }

        // Recalculate totals from actual records
        $totalSales = DB::table('sales')
            ->whereDate('created_at', $date)
            ->sum(DB::raw('CAST(total_net as DECIMAL(15,2))'));

        $totalReceipts = DB::table('receipts_vouchers')
            ->whereDate('receipt_date', $date)
            ->sum(DB::raw('CAST(total_amount as DECIMAL(15,2))'));

        $totalPayments = DB::table('payment_vouchers')
            ->whereDate('receipt_date', $date)
            ->sum(DB::raw('CAST(total_amount as DECIMAL(15,2))'));

        $totalExpenses = DB::table('expense_vouchers')
            ->whereDate('entry_date', $date)
            ->sum(DB::raw('CAST(total_amount as DECIMAL(15,2))'));

        $totalIn = $totalSales + $totalReceipts;
        $totalOut = $totalPayments + $totalExpenses;

        // Get opening balance from previous day
        $previousDay = Carbon::parse($date)->subDay()->toDateString();
        $opening = DB::table('day_closings')
            ->where('date', $previousDay)
            ->value('closing_balance') ?? 0;

        $closing = $opening + $totalIn - $totalOut;

        // Save the day closing record
        DB::table('day_closings')->updateOrInsert(
            ['date' => $date],
            [
                'opening_balance' => $opening,
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'closing_balance' => $closing,
                'is_closed' => true,
                'closed_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Check if we should auto-open next day
        $nextDay = Carbon::parse($date)->addDay()->toDateString();
        if ($nextDay <= now()->toDateString()) {
            $nextDayClosure = DB::table('day_closings')->where('date', $nextDay)->first();
            
            if (!$nextDayClosure) {
                DB::table('day_closings')->insert([
                    'date' => $nextDay,
                    'opening_balance' => $closing,
                    'total_in' => 0,
                    'total_out' => 0,
                    'closing_balance' => $closing,
                    'is_closed' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        return back()->with('success', 'Day closed successfully! Closing Balance: ₨ ' . number_format($closing, 2));
    }
    public function edit_expense_vocher($id)
    {
        $voucher = ExpenseVoucher::findOrFail($id);
        $AccountHeads = AccountHead::get();
        $SourceAccounts = Account::orderBy('title')->get();
        $ExpenseAccounts = \App\Models\ExpenseCategory::orderBy('title')->get();

        // Prepare rows for the view
        $rowAccountIds = is_array($voucher->row_account_id) ? $voucher->row_account_id : json_decode($voucher->row_account_id, true);
        $amounts = is_array($voucher->amount) ? $voucher->amount : json_decode($voucher->amount, true);
        
        $rows = [];
        if ($rowAccountIds && $amounts) {
            foreach ($rowAccountIds as $index => $accId) {
                $rows[] = [
                    'account_id' => $accId,
                    'amount' => $amounts[$index] ?? 0
                ];
            }
        } else {
            // Default row if none exist
            $rows[] = ['account_id' => '', 'amount' => 0];
        }

        return view('admin_panel.vochers.expense_vochers.edit_expense_vouchers', compact('voucher', 'AccountHeads', 'SourceAccounts', 'ExpenseAccounts', 'rows'));
    }

    public function update_expense_vocher(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $voucher = ExpenseVoucher::findOrFail($id);

            // --- STEP 1: Reverse OLD effects ---
            // Reverse Source Account
            $oldSourceAccount = Account::find($voucher->party_id);
            if ($oldSourceAccount) {
                $oldSourceAccount->update([
                    'opening_balance' => $oldSourceAccount->opening_balance + $voucher->total_amount
                ]);
            }

            // Reverse Expense Categories
            $oldRowAccountIds = is_array($voucher->row_account_id) ? $voucher->row_account_id : json_decode($voucher->row_account_id, true);
            $oldAmounts = is_array($voucher->amount) ? $voucher->amount : json_decode($voucher->amount, true);

            if ($oldRowAccountIds && $oldAmounts) {
                foreach ($oldRowAccountIds as $index => $accId) {
                    $rowAmount = $oldAmounts[$index] ?? 0;
                    $category = \App\Models\ExpenseCategory::find($accId);
                    if ($category) {
                        $category->update([
                            'total_amount' => $category->total_amount - $rowAmount
                        ]);
                    }
                }
            }

            // --- STEP 2: Apply NEW effects ---
            $filtered_accounts = [];
            $filtered_amounts = [];
            if ($request->row_account_id && $request->amount) {
                foreach ($request->row_account_id as $index => $accId) {
                    $amt = $request->amount[$index] ?? 0;
                    if (!empty($accId) && !empty($amt) && $amt > 0) {
                        $filtered_accounts[] = $accId;
                        $filtered_amounts[] = (float)$amt;
                    }
                }
            }

            if (empty($filtered_accounts)) {
                throw new \Exception("Please select at least one expense account and enter an amount.");
            }

            $total_amount = array_sum($filtered_amounts);

            // Fetch heads
            $rowAccountHeads = [];
            foreach ($filtered_accounts as $accId) {
                $acc = Account::find($accId);
                $rowAccountHeads[] = $acc->head_id ?? 1;
            }

            $voucher->update([
                'entry_date'       => $request->entry_date ?? $voucher->entry_date,
                'party_id'         => $request->vendor_id, 
                'remarks'          => $request->remarks,
                'row_account_head' => $rowAccountHeads, 
                'row_account_id'   => $filtered_accounts,
                'amount'           => $filtered_amounts,
                'total_amount'     => $total_amount,
            ]);

            // Apply new Expense Categories balance
            foreach ($filtered_accounts as $index => $accId) {
                $rowAmount = $filtered_amounts[$index];
                $category = \App\Models\ExpenseCategory::find($accId);
                if ($category) {
                    $category->update([
                        'total_amount' => $category->total_amount + $rowAmount
                    ]);
                }
            }

            // Apply new Source Account balance
            $newSourceAccount = Account::find($request->vendor_id);
            if ($newSourceAccount) {
                $newSourceAccount->update([
                    'opening_balance' => $newSourceAccount->opening_balance - $total_amount
                ]);
            }

            DB::commit();
            return redirect()->route('voucher.history')->with('success', 'Expense Voucher updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy_expense_vocher($id)
    {
        DB::beginTransaction();
        try {
            $voucher = ExpenseVoucher::findOrFail($id);

            // 1. Reverse Source Account
            $sourceAccount = Account::find($voucher->party_id);
            if ($sourceAccount) {
                $sourceAccount->update([
                    'opening_balance' => $sourceAccount->opening_balance + $voucher->total_amount
                ]);
            }

            // 2. Reverse Expense Categories
            $rowAccountIds = is_array($voucher->row_account_id) ? $voucher->row_account_id : json_decode($voucher->row_account_id, true);
            $amounts = is_array($voucher->amount) ? $voucher->amount : json_decode($voucher->amount, true);

            if ($rowAccountIds && $amounts) {
                foreach ($rowAccountIds as $index => $accId) {
                    $rowAmount = $amounts[$index] ?? 0;
                    $category = \App\Models\ExpenseCategory::find($accId);
                    if ($category) {
                        $category->update([
                            'total_amount' => $category->total_amount - $rowAmount
                        ]);
                    }
                }
            }

            $voucher->delete();

            DB::commit();
            return back()->with('success', 'Expense Voucher deleted and balances reversed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ======================== VOUCHER HISTORY ========================

    public function voucherHistory()
    {
        $accounts = Account::where('status', 1)->orderBy('title')->get();
        return view('admin_panel.vochers.voucher_history', compact('accounts'));
    }

    public function voucherHistoryData(Request $request)
    {
        $type = $request->input('type', 'all');
        $search = is_array($s = $request->input('search', '')) ? ($s['value'] ?? '') : $s;
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $minAmount = $request->input('min_amount');
        $maxAmount = $request->input('max_amount');
        $partyFilter = $request->input('party_type');
        $accountId = $request->input('account_id');
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $all = collect();
        $dateSortKey = 'date';

        // ---------- EXPENSE ----------
        if (in_array($type, ['all', 'expense'])) {
            $q = ExpenseVoucher::with('vendor', 'customer', 'partyAccount');
            if ($search) $q->where(function($s) use ($search) { $s->where('evid', 'LIKE', "%$search%")->orWhere('remarks', 'LIKE', "%$search%"); });
            if ($fromDate) $q->whereDate('entry_date', '>=', $fromDate);
            if ($toDate) $q->whereDate('entry_date', '<=', $toDate);
            if ($minAmount) $q->where('total_amount', '>=', $minAmount);
            if ($maxAmount) $q->where('total_amount', '<=', $maxAmount);
            foreach ($q->get() as $r) {
                $partyType = $r->type_name;
                $party = $r->type === 'vendor' ? ($r->vendor->name ?? '') : ($r->type === 'customer' ? ($r->customer->customer_name ?? '') : ($r->partyAccount->title ?? ''));
                $cats = collect();
                $rowAccIds = is_array($r->row_account_id) ? $r->row_account_id : (json_decode($r->row_account_id, true) ?? []);
                foreach ($rowAccIds as $aid) {
                    $cat = \App\Models\ExpenseCategory::find($aid);
                    if ($cat) $cats->push($cat->title);
                }
                $detail = $cats->take(3)->implode(', ') . ($cats->count() > 3 ? ' +' . ($cats->count() - 3) : '');
                $all->push(['id' => $r->id, 'source' => 'expense', 'type_label' => 'Expense', 'voucher_no' => $r->evid, 'date' => $r->entry_date, 'party_name' => $party, 'party_type_label' => $partyType, 'detail' => $detail, 'account' => $r->tel, 'amount' => (float)($r->total_amount ?? 0), 'created_by' => '', 'remarks' => $r->remarks, 'created_at' => $r->created_at, 'updated_at' => $r->updated_at, 'edit_url' => route('expense-vocher.edit', $r->id), 'print_url' => route('expenseVoucher.print', $r->id), 'delete_url' => route('expense-vocher.delete', $r->id), 'delete_method' => 'GET']);
            }
        }

        // ---------- PAYMENT IN (CustomerPayment adj=minus + VendorPayment adj=plus) ----------
        if (in_array($type, ['all', 'payment_in'])) {
            $cp = CustomerPayment::with('customer', 'account')->where('adjustment_type', 'minus');
            $vp = VendorPayment::with('vendor', 'account')->where('adjustment_type', 'plus');
            if ($search) {
                $cp->where(function($s) use ($search) { $s->where('id', 'LIKE', "%$search%"); });
                $vp->where(function($s) use ($search) { $s->where('id', 'LIKE', "%$search%"); });
            }
            if ($fromDate) { $cp->whereDate('payment_date', '>=', $fromDate); $vp->whereDate('payment_date', '>=', $fromDate); }
            if ($toDate) { $cp->whereDate('payment_date', '<=', $toDate); $vp->whereDate('payment_date', '<=', $toDate); }
            if ($minAmount) { $cp->where('amount', '>=', $minAmount); $vp->where('amount', '>=', $minAmount); }
            if ($maxAmount) { $cp->where('amount', '<=', $maxAmount); $vp->where('amount', '<=', $maxAmount); }
            if ($partyFilter === 'customer') $vp->whereRaw('1=0');
            if ($partyFilter === 'vendor') $cp->whereRaw('1=0');
            if ($accountId) { $cp->where('account_id', $accountId); $vp->where('account_id', $accountId); }
            foreach ($cp->get() as $r) {
                $all->push(['id' => $r->id, 'source' => 'payment_in', 'type_label' => 'Payment In', 'voucher_no' => 'PIN-' . str_pad($r->id, 4, '0', STR_PAD_LEFT), 'date' => $r->payment_date, 'party_name' => $r->customer->customer_name ?? '', 'party_type_label' => 'Customer', 'detail' => $r->payment_method ? ('Method: ' . $r->payment_method) : '', 'account' => $r->account->title ?? '', 'amount' => (float)($r->amount ?? 0), 'created_by' => '', 'remarks' => $r->note, 'created_at' => $r->created_at, 'updated_at' => $r->updated_at, 'edit_url' => route('payment.in.edit', [$r->id, 'customer']), 'print_url' => route('payment.in.print', [$r->id, 'customer']), 'delete_url' => route('payment.in.delete', [$r->id, 'customer']), 'delete_method' => 'DELETE']);
            }
            foreach ($vp->get() as $r) {
                $all->push(['id' => $r->id, 'source' => 'payment_in', 'type_label' => 'Payment In', 'voucher_no' => 'PIN-' . str_pad($r->id, 4, '0', STR_PAD_LEFT), 'date' => $r->payment_date, 'party_name' => $r->vendor->name ?? '', 'party_type_label' => 'Vendor', 'detail' => $r->payment_method ? ('Method: ' . $r->payment_method) : '', 'account' => $r->account->title ?? '', 'amount' => (float)($r->amount ?? 0), 'created_by' => '', 'remarks' => $r->note, 'created_at' => $r->created_at, 'updated_at' => $r->updated_at, 'edit_url' => route('payment.in.edit', [$r->id, 'vendor']), 'print_url' => route('payment.in.print', [$r->id, 'vendor']), 'delete_url' => route('payment.in.delete', [$r->id, 'vendor']), 'delete_method' => 'DELETE']);
            }
        }

        // ---------- PAYMENT OUT (VendorPayment adj=minus + CustomerPayment adj=plus) ----------
        if (in_array($type, ['all', 'payment_out'])) {
            $vp = VendorPayment::with('vendor', 'account')->where('adjustment_type', 'minus');
            $cp = CustomerPayment::with('customer', 'account')->where('adjustment_type', 'plus');
            if ($search) {
                $vp->where(function($s) use ($search) { $s->where('id', 'LIKE', "%$search%"); });
                $cp->where(function($s) use ($search) { $s->where('id', 'LIKE', "%$search%"); });
            }
            if ($fromDate) { $vp->whereDate('payment_date', '>=', $fromDate); $cp->whereDate('payment_date', '>=', $fromDate); }
            if ($toDate) { $vp->whereDate('payment_date', '<=', $toDate); $cp->whereDate('payment_date', '<=', $toDate); }
            if ($minAmount) { $vp->where('amount', '>=', $minAmount); $cp->where('amount', '>=', $minAmount); }
            if ($maxAmount) { $vp->where('amount', '<=', $maxAmount); $cp->where('amount', '<=', $maxAmount); }
            if ($partyFilter === 'vendor') $cp->whereRaw('1=0');
            if ($partyFilter === 'customer') $vp->whereRaw('1=0');
            if ($accountId) { $vp->where('account_id', $accountId); $cp->where('account_id', $accountId); }
            foreach ($vp->get() as $r) {
                $all->push(['id' => $r->id, 'source' => 'payment_out', 'type_label' => 'Payment Out', 'voucher_no' => 'POUT-' . str_pad($r->id, 4, '0', STR_PAD_LEFT), 'date' => $r->payment_date, 'party_name' => $r->vendor->name ?? '', 'party_type_label' => 'Vendor', 'detail' => $r->payment_method ? ('Method: ' . $r->payment_method) : '', 'account' => $r->account->title ?? '', 'amount' => (float)($r->amount ?? 0), 'created_by' => '', 'remarks' => $r->note, 'created_at' => $r->created_at, 'updated_at' => $r->updated_at, 'edit_url' => route('payment.out.edit', [$r->id, 'vendor']), 'print_url' => route('payment.out.print', [$r->id, 'vendor']), 'delete_url' => route('payment.out.delete', [$r->id, 'vendor']), 'delete_method' => 'DELETE']);
            }
            foreach ($cp->get() as $r) {
                $all->push(['id' => $r->id, 'source' => 'payment_out', 'type_label' => 'Payment Out', 'voucher_no' => 'POUT-' . str_pad($r->id, 4, '0', STR_PAD_LEFT), 'date' => $r->payment_date, 'party_name' => $r->customer->customer_name ?? '', 'party_type_label' => 'Customer', 'detail' => $r->payment_method ? ('Method: ' . $r->payment_method) : '', 'account' => $r->account->title ?? '', 'amount' => (float)($r->amount ?? 0), 'created_by' => '', 'remarks' => $r->note, 'created_at' => $r->created_at, 'updated_at' => $r->updated_at, 'edit_url' => route('payment.out.edit', [$r->id, 'customer']), 'print_url' => route('payment.out.print', [$r->id, 'customer']), 'delete_url' => route('payment.out.delete', [$r->id, 'customer']), 'delete_method' => 'DELETE']);
            }
        }

        // ---------- INCOME ----------
        if (in_array($type, ['all', 'income'])) {
            $q = OtherIncome::with('vendor', 'customer', 'account');
            if ($search) $q->where(function($s) use ($search) { $s->where('title', 'LIKE', "%$search%")->orWhere('remarks', 'LIKE', "%$search%"); });
            if ($fromDate) $q->whereDate('date', '>=', $fromDate);
            if ($toDate) $q->whereDate('date', '<=', $toDate);
            if ($minAmount) $q->where('amount', '>=', $minAmount);
            if ($maxAmount) $q->where('amount', '<=', $maxAmount);
            if ($partyFilter === 'customer') $q->where('party_type', 'customer');
            elseif ($partyFilter === 'vendor') $q->where('party_type', 'vendor');
            if ($accountId) $q->where('account_id', $accountId);
            foreach ($q->get() as $r) {
                $party = $r->party_type === 'vendor' ? ($r->vendor->name ?? '') : ($r->party_type === 'customer' ? ($r->customer->customer_name ?? '') : ($r->account->title ?? ''));
                $ptLabel = $r->party_type === 'vendor' ? 'Vendor' : ($r->party_type === 'customer' ? 'Customer' : 'Account');
                $all->push(['id' => $r->id, 'source' => 'income', 'type_label' => 'Income', 'voucher_no' => 'INC-' . str_pad($r->id, 4, '0', STR_PAD_LEFT), 'date' => $r->date, 'party_name' => $party, 'party_type_label' => $ptLabel, 'detail' => '', 'account' => $r->account->title ?? '', 'amount' => (float)($r->amount ?? 0), 'created_by' => '', 'remarks' => $r->title, 'created_at' => $r->created_at, 'updated_at' => $r->updated_at, 'edit_url' => route('other.income.edit', $r->id), 'print_url' => route('other.income.print', $r->id), 'delete_url' => route('other.income.delete', $r->id), 'delete_method' => 'DELETE']);
            }
        }

        // ---------- PARTY TRANSFER ----------
        if (in_array($type, ['all', 'party_transfer'])) {
            $q = TransferVoucher::query();
            if ($search) $q->where(function($s) use ($search) { $s->where('tvid', 'LIKE', "%$search%")->orWhere('remarks', 'LIKE', "%$search%"); });
            if ($fromDate) $q->whereDate('transfer_date', '>=', $fromDate);
            if ($toDate) $q->whereDate('transfer_date', '<=', $toDate);
            if ($minAmount) $q->where('amount', '>=', $minAmount);
            if ($maxAmount) $q->where('amount', '<=', $maxAmount);
            if ($accountId) $q->whereRaw('1=0');
            foreach ($q->get() as $r) {
                $srcName = '';
                if ($r->source_party_type === 'customer') $srcName = \App\Models\Customer::find($r->source_party_id)->customer_name ?? '';
                elseif ($r->source_party_type === 'vendor') $srcName = \App\Models\Vendor::find($r->source_party_id)->name ?? '';
                $dstName = '';
                if ($r->destination_party_type === 'customer') $dstName = \App\Models\Customer::find($r->destination_party_id)->customer_name ?? '';
                elseif ($r->destination_party_type === 'vendor') $dstName = \App\Models\Vendor::find($r->destination_party_id)->name ?? '';
                $srcLabel = $r->source_party_type === 'customer' ? 'C' : 'V';
                $dstLabel = $r->destination_party_type === 'customer' ? 'C' : 'V';
                $party = "$srcName ($srcLabel) \u{2192} $dstName ($dstLabel)";
                $detail = "Source: {$r->source_party_type} / Dest: {$r->destination_party_type}";
                $all->push(['id' => $r->id, 'source' => 'party_transfer', 'type_label' => 'Party to Party', 'voucher_no' => $r->tvid, 'date' => $r->transfer_date, 'party_name' => $party, 'party_type_label' => '', 'detail' => $detail, 'account' => '', 'amount' => (float)($r->amount ?? 0), 'created_by' => '', 'remarks' => $r->remarks, 'created_at' => $r->created_at, 'updated_at' => $r->updated_at, 'edit_url' => route('transfer-vouchers.edit', $r->id), 'print_url' => route('transfer-vouchers.print', $r->id), 'delete_url' => route('transfer-vouchers.delete', $r->id), 'delete_method' => 'DELETE']);
            }
        }

        // ---------- ACCOUNT TRANSFER ----------
        if (in_array($type, ['all', 'account_transfer'])) {
            $q = AccountTransfer::with('fromAccount', 'toAccount');
            if ($search) $q->where(function($s) use ($search) { $s->where('atvid', 'LIKE', "%$search%")->orWhere('remarks', 'LIKE', "%$search%"); });
            if ($fromDate) $q->whereDate('transfer_date', '>=', $fromDate);
            if ($toDate) $q->whereDate('transfer_date', '<=', $toDate);
            if ($minAmount) $q->where('amount', '>=', $minAmount);
            if ($maxAmount) $q->where('amount', '<=', $maxAmount);
            if ($accountId) $q->where(function($a) use ($accountId) { $a->where('from_account_id', $accountId)->orWhere('to_account_id', $accountId); });
            foreach ($q->get() as $r) {
                $from = $r->fromAccount->title ?? '';
                $to = $r->toAccount->title ?? '';
                $all->push(['id' => $r->id, 'source' => 'account_transfer', 'type_label' => 'Account Transfer', 'voucher_no' => $r->atvid, 'date' => $r->transfer_date, 'party_name' => $from . ' → ' . $to, 'party_type_label' => 'Transfer', 'detail' => $from . ' → ' . $to, 'account' => $from . ' → ' . $to, 'amount' => (float)($r->amount ?? 0), 'created_by' => '', 'remarks' => $r->remarks, 'created_at' => $r->created_at, 'updated_at' => $r->updated_at, 'edit_url' => route('account-transfers.edit', $r->id), 'print_url' => route('account-transfers.print', $r->id), 'delete_url' => route('account-transfers.delete', $r->id), 'delete_method' => 'DELETE']);
            }
        }

        // Additional search on merged data for party name / voucher no / detail
        if ($search) {
            $all = $all->filter(function($v) use ($search) {
                return stripos($v['voucher_no'] ?? '', $search) !== false
                    || stripos($v['party_name'] ?? '', $search) !== false
                    || stripos($v['detail'] ?? '', $search) !== false
                    || stripos($v['remarks'] ?? '', $search) !== false;
            })->values();
        }

        // Sort by date desc
        $all = $all->sortByDesc('date')->values();

        $recordsTotal = $all->count();

        // Paginate
        $page = $length > 0 ? ($start / $length) + 1 : 1;
        $paginated = $all->forPage($page, $length);
        $recordsFiltered = $recordsTotal;

        // Summary based on filtered results
        $summary = [
            'total_vouchers' => $recordsFiltered,
            'total_amount'   => $all->sum('amount'),
            'total_expense'  => $all->where('source', 'expense')->sum('amount'),
            'total_income'   => $all->where('source', 'income')->sum('amount'),
            'total_payment_in'  => $all->where('source', 'payment_in')->sum('amount'),
            'total_payment_out' => $all->where('source', 'payment_out')->sum('amount'),
        ];

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $paginated->values(),
            'summary'         => $summary,
        ]);
    }
}
