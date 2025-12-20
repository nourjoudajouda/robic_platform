<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function pending($userId = null)
    {
        $pageTitle   = 'Pending Withdrawals';
        $withdrawals = $this->withdrawalData('pending', userId: $userId);
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function approved($userId = null)
    {
        $pageTitle   = 'Approved Withdrawals';
        $withdrawals = $this->withdrawalData('approved', userId: $userId);
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function rejected($userId = null)
    {
        $pageTitle   = 'Rejected Withdrawals';
        $withdrawals = $this->withdrawalData('rejected', userId: $userId);
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function all($userId = null)
    {
        $pageTitle      = 'All Withdrawals';
        $withdrawalData = $this->withdrawalData($scope = null, $summary = true, userId: $userId);
        $withdrawals    = $withdrawalData['data'];
        $summary        = $withdrawalData['summary'];
        $successful     = $summary['successful'];
        $pending        = $summary['pending'];
        $rejected       = $summary['rejected'];

        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals', 'successful', 'pending', 'rejected'));
    }

    protected function withdrawalData($scope = null, $summary = false, $userId = null)
    {
        if ($scope) {
            $withdrawals = Withdrawal::$scope();
        } else {
            $withdrawals = Withdrawal::where('status', '!=', Status::PAYMENT_INITIATE);
        }

        if ($userId) {
            $withdrawals = $withdrawals->where('user_id', $userId);
        }

        $withdrawals = $withdrawals->searchable(['trx', 'user:username'])->dateFilter();

        $request = request();
        if ($request->method) {
            $withdrawals = $withdrawals->where('method_id', $request->method);
        }
        if (!$summary) {
            return $withdrawals->with('user')->orderBy('id', 'desc')->paginate(getPaginate());
        } else {

            $successful = clone $withdrawals;
            $pending    = clone $withdrawals;
            $rejected   = clone $withdrawals;

            $successfulSummary = $successful->where('status', Status::PAYMENT_SUCCESS)->sum('amount');
            $pendingSummary    = $pending->where('status', Status::PAYMENT_PENDING)->sum('amount');
            $rejectedSummary   = $rejected->where('status', Status::PAYMENT_REJECT)->sum('amount');

            return [
                'data'    => $withdrawals->with('user')->orderBy('id', 'desc')->paginate(getPaginate()),
                'summary' => [
                    'successful' => $successfulSummary,
                    'pending'    => $pendingSummary,
                    'rejected'   => $rejectedSummary,
                ],
            ];
        }
    }

    public function details($id)
    {
        $withdrawal = Withdrawal::where('id', $id)->where('status', '!=', Status::PAYMENT_INITIATE)->with('user')->firstOrFail();
        $pageTitle  = 'Withdrawal Details';
        
        // Safely handle withdraw_information
        $details = null;
        if ($withdrawal->withdraw_information) {
            try {
                $details = json_encode($withdrawal->withdraw_information);
            } catch (\Exception $e) {
                $details = null;
            }
        }

        return view('admin.withdraw.detail', compact('pageTitle', 'withdrawal', 'details'));
    }

    public function approve(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'transfer_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'details' => 'nullable|string|max:500'
        ]);
        
        $withdraw = Withdrawal::where('id', $request->id)->where('status', Status::PAYMENT_PENDING)->with('user')->firstOrFail();
        
        // Handle transfer image upload
        $transferImage = null;
        if ($request->hasFile('transfer_image')) {
            try {
                $file = $request->file('transfer_image');
                
                // Validate file
                if (!$file->isValid()) {
                    $notify[] = ['error', 'Invalid file uploaded.'];
                    return back()->withNotify($notify);
                }
                
                // Get file extension
                $extension = $file->getClientOriginalExtension();
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (!in_array(strtolower($extension), $allowedExtensions)) {
                    $notify[] = ['error', 'Invalid file type. Only JPG, PNG, and GIF are allowed.'];
                    return back()->withNotify($notify);
                }
                
                // Upload file using fileUploader (size = null means no resizing)
                $transferImage = fileUploader($file, getFilePath('transfers'), null, null, null, null);
            } catch (\Exception $e) {
                $notify[] = ['error', 'Failed to upload transfer image: ' . $e->getMessage()];
                return back()->withNotify($notify);
            }
        }
        
        $withdraw->status = Status::PAYMENT_SUCCESS;
        $withdraw->admin_feedback = $request->details;
        $withdraw->transfer_image = $transferImage;
        $withdraw->save();

        notify($withdraw->user, 'WITHDRAW_APPROVE', [
            'amount' => showAmount($withdraw->amount, currencyFormat: false),
            'trx' => $withdraw->trx,
            'admin_details' => $request->details,
        ]);

        $notify[] = ['success', 'Withdrawal approved successfully'];
        return to_route('admin.withdraw.data.pending')->withNotify($notify);
    }

    public function reject(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $withdraw = Withdrawal::where('id', $request->id)->where('status', Status::PAYMENT_PENDING)->with('user')->firstOrFail();

        $withdraw->status         = Status::PAYMENT_REJECT;
        $withdraw->admin_feedback = $request->details;
        $withdraw->save();

        $user = $withdraw->user;
        $user->balance += $withdraw->amount;
        $user->save();

        // Refund to wallet
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet) {
            // Create wallet if it doesn't exist
            $wallet = new Wallet();
            $wallet->user_id = $user->id;
            $wallet->balance = 0;
            $wallet->status = Status::ENABLE;
            $wallet->save();
        }
        $wallet->balance += $withdraw->amount;
        $wallet->save();

        $transaction               = new Transaction();
        $transaction->user_id      = $withdraw->user_id;
        $transaction->amount       = $withdraw->amount;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '+';
        $transaction->remark       = 'withdraw_reject';
        $transaction->details      = 'Refunded for withdrawal rejection';
        $transaction->trx          = $withdraw->trx;
        $transaction->save();

        notify($user, 'WITHDRAW_REJECT', [
            'method_name'     => 'Bank Transfer',
            'method_currency' => $withdraw->currency,
            'method_amount'   => showAmount($withdraw->final_amount, currencyFormat: false),
            'amount'          => showAmount($withdraw->amount, currencyFormat: false),
            'charge'          => showAmount($withdraw->charge, currencyFormat: false),
            'rate'            => showAmount($withdraw->rate, currencyFormat: false),
            'trx'             => $withdraw->trx,
            'post_balance'    => showAmount($user->balance, currencyFormat: false),
            'admin_details'   => $request->details,
        ]);

        $notify[] = ['success', 'Withdrawal rejected successfully'];
        return to_route('admin.withdraw.data.pending')->withNotify($notify);
    }

}
