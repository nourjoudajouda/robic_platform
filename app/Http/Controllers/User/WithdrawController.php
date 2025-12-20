<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\AdminNotification;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\WithdrawMethod;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{

    public function withdrawMoney()
    {
        $pageTitle = 'Withdraw Money';
        return view('Template::user.withdraw.methods', compact('pageTitle'));
    }

    public function withdrawStore(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|gt:0'
        ]);

        $user = auth()->user();

        // Check if user has sufficient balance
        if ($request->amount > $user->balance) {
            $notify[] = ['error', 'Insufficient balance for withdrawal'];
            return back()->withNotify($notify)->withInput($request->all());
        }

        // Create withdrawal request
        $withdraw = new Withdrawal();
        $withdraw->user_id = $user->id;
        $withdraw->method_id = 1; // Default method ID (can be changed later)
        $withdraw->amount = $request->amount;
        $withdraw->currency = gs('cur_text');
        $withdraw->rate = 1;
        $withdraw->charge = 0; // No charge for now
        $withdraw->final_amount = $request->amount;
        $withdraw->after_charge = $request->amount;
        $withdraw->trx = getTrx();
        $withdraw->status = Status::PAYMENT_PENDING;
        $withdraw->save();

        // Deduct amount from user balance
        $user->balance -= $request->amount;
        $user->save();

        // Deduct amount from wallet balance
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet) {
            // Create wallet if it doesn't exist
            $wallet = new Wallet();
            $wallet->user_id = $user->id;
            $wallet->balance = 0;
            $wallet->status = Status::ENABLE;
            $wallet->save();
        }
        $wallet->balance -= $request->amount;
        $wallet->save();

        // Create transaction
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $request->amount;
        $transaction->post_balance = $user->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '-';
        $transaction->details = 'Withdraw request - Bank Transfer';
        $transaction->trx = $withdraw->trx;
        $transaction->remark = 'withdraw';
        $transaction->save();

        // Send notification to admin
        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New withdraw request - ' . showAmount($request->amount);
        $adminNotification->click_url = urlPath('admin.withdraw.data.details', $withdraw->id);
        $adminNotification->save();

        // Notify user
        notify($user, 'WITHDRAW_REQUEST', [
            'amount' => showAmount($request->amount),
            'trx' => $withdraw->trx,
            'message' => 'Your withdrawal request is pending admin approval'
        ]);

        $notify[] = ['success', 'Withdrawal request submitted successfully. Waiting for admin approval.'];
        return redirect()->route('user.withdraw.history')->withNotify($notify);
    }

    public function withdrawLog(Request $request)
    {
        $pageTitle = "Withdrawal Log";
        $withdraws = Withdrawal::where('user_id', auth()->id())->where('status', '!=', Status::PAYMENT_INITIATE);
        if ($request->search) {
            $withdraws = $withdraws->where('trx',$request->search);
        }
        $withdraws = $withdraws->with('method')->orderBy('id','desc')->paginate(getPaginate());
        return view('Template::user.withdraw.log', compact('pageTitle','withdraws'));
    }
}
