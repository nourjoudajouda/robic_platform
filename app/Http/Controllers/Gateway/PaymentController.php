<?php

namespace App\Http\Controllers\Gateway;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\AdminNotification;
use App\Models\Asset;
use App\Models\Batch;
use App\Models\Deposit;
use App\Models\GatewayCurrency;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserSellOrder;
use App\Models\Wallet;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function deposit()
    {
        $pageTitle = 'Deposit Balance';
        $bankTransfer        = config('robic.bank_transfer', []);
        $depositInstructions = config('robic.deposit_instructions', []);

        return view('Template::user.payment.deposit', compact('pageTitle', 'bankTransfer', 'depositInstructions'));
    }

    public function depositInsert(Request $request)
    {
        $request->validate([
            'amount'   => 'required|numeric|gt:0',
            'transfer_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        
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

        // Create deposit with transfer image
        $deposit = new Deposit();
        $deposit->user_id = $user->id;
        $deposit->method_code = 1000; // Manual transfer method code
        $deposit->method_currency = gs('cur_text');
        $deposit->amount = $request->amount;
        $deposit->charge = 0;
        $deposit->rate = 1;
        $deposit->final_amount = $request->amount;
        $deposit->btc_amount = 0;
        $deposit->btc_wallet = "";
        $deposit->trx = getTrx();
        $deposit->status = Status::PAYMENT_PENDING;
        $deposit->transfer_image = $transferImage;
        $deposit->description = $request->description;
        $deposit->success_url = route('user.deposit.history');
        $deposit->failed_url = route('user.deposit.history');
        $deposit->save();

        // Send notification to admin
        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New deposit request - ' . showAmount($request->amount);
        $adminNotification->click_url = urlPath('admin.deposit.details', $deposit->id);
        $adminNotification->save();

        // Notify user
        notify($user, 'DEPOSIT_REQUEST', [
            'amount' => showAmount($request->amount),
            'trx' => $deposit->trx,
            'message' => 'Your deposit request is pending admin approval'
        ]);

        $notify[] = ['success', 'Deposit request submitted successfully. Waiting for admin approval.'];
        return redirect()->route('user.deposit.history')->withNotify($notify);
    }

    public static function insertDeposit($gate, $amount, $buyInfo = null)
    {
        $charge      = $gate->fixed_charge + ($amount * $gate->percent_charge / 100);
        $payable     = $amount + $charge;
        $finalAmount = $payable * $gate->rate;

        $data                  = new Deposit();
        $data->user_id         = auth()->id();
        $data->method_code     = $gate->method_code;
        $data->method_currency = strtoupper($gate->currency);
        $data->amount          = $amount;
        $data->charge          = $charge;
        $data->rate            = $gate->rate;
        $data->final_amount    = $finalAmount;
        $data->btc_amount      = 0;
        $data->btc_wallet      = "";
        $data->trx             = getTrx();

        if ($buyInfo) {
            $data->category_id = $buyInfo['other']['category_id'] ?? null;
            $data->buy_info    = $buyInfo['data'];
            $data->other       = $buyInfo['other'] ?? null; // حفظ other data للشراء من عدة orders
            $data->success_url = $buyInfo['other']['success_url'] ?? route('user.deposit.history');
            $data->failed_url  = $buyInfo['other']['failed_url'] ?? route('user.deposit.history');
        } else {
            $data->success_url = route('user.deposit.history');
            $data->failed_url  = route('user.deposit.history');
        }

        $data->save();

        session()->put('Track', $data->trx);
    }

    public function appDepositConfirm($hash)
    {
        try {
            $id = decrypt($hash);
        } catch (\Exception $ex) {
            abort(404);
        }
        $data = Deposit::where('id', $id)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->firstOrFail();
        $user = User::findOrFail($data->user_id);
        auth()->login($user);
        session()->put('Track', $data->trx);
        return to_route('user.deposit.confirm');
    }

    public function depositConfirm()
    {
        $track   = session()->get('Track');
        $deposit = Deposit::where('trx', $track)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->with('gateway')->firstOrFail();

        if ($deposit->method_code >= 1000) {
            return to_route('user.deposit.manual.confirm');
        }

        $dirName = $deposit->gateway->alias;
        $new     = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

        $data = $new::process($deposit);
        $data = json_decode($data);

        if (isset($data->error)) {
            $notify[] = ['error', $data->message];
            return back()->withNotify($notify);
        }
        if (isset($data->redirect)) {
            return redirect($data->redirect_url);
        }

        // for Stripe V3
        if (@$data->session) {
            $deposit->btc_wallet = $data->session->id;
            $deposit->save();
        }

        $pageTitle = 'Payment Confirm';
        return view("Template::$data->view", compact('data', 'pageTitle', 'deposit'));
    }

    public static function userDataUpdate($deposit, $isManual = null)
    {
        if ($deposit->status == Status::PAYMENT_INITIATE || $deposit->status == Status::PAYMENT_PENDING) {
            $deposit->status = Status::PAYMENT_SUCCESS;
            $deposit->save();

            $user = User::find($deposit->user_id);
            $user->balance += $deposit->amount;
            $user->save();

            // Update wallet balance
            $wallet = Wallet::where('user_id', $user->id)->first();
            if (!$wallet) {
                // Create wallet if it doesn't exist
                $wallet = new Wallet();
                $wallet->user_id = $user->id;
                $wallet->balance = 0;
                $wallet->status = Status::ENABLE;
                $wallet->save();
            }
            $wallet->balance += $deposit->amount;
            $wallet->save();

            $methodName = $deposit->methodName();

            $transaction               = new Transaction();
            $transaction->user_id      = $deposit->user_id;
            $transaction->amount       = $deposit->amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge       = $deposit->charge;
            $transaction->trx_type     = '+';
            $transaction->details      = 'Deposit Via ' . $methodName;
            $transaction->trx          = $deposit->trx;
            $transaction->remark       = 'deposit';
            $transaction->save();

            // معالجة الشراء من عدة orders أو order واحد
            if ($deposit->category_id) {
                // الطريقة القديمة - شراء من batch واحد
                Asset::buyBean($user, $deposit->category, $deposit->buy_info->amount, $deposit->amount, $deposit->buy_info->quantity, $deposit->buy_info->charge, $deposit->buy_info->vat, $methodName);
            } elseif (isset($deposit->other->order_type) && $deposit->other->order_type == 'multiple' && isset($deposit->other->multiple_orders)) {
                // الشراء من عدة orders
                Asset::buyFromMultipleOrders(
                    $user,
                    $deposit->other->product_id,
                    $deposit->other->multiple_orders,
                    $deposit->buy_info->amount,
                    $deposit->buy_info->quantity,
                    $deposit->buy_info->charge,
                    $deposit->buy_info->vat,
                    $methodName
                );
            } elseif (isset($deposit->other->order_type) && $deposit->other->order_type == 'user' && isset($deposit->other->sell_order_id)) {
                // الشراء من user_sell_order
                $userSellOrder = \App\Models\UserSellOrder::find($deposit->other->sell_order_id);
                if ($userSellOrder) {
                    Asset::buyFromUserSellOrder(
                        $user,
                        $userSellOrder,
                        $deposit->buy_info->amount,
                        $deposit->amount,
                        $deposit->buy_info->quantity,
                        $deposit->buy_info->charge,
                        $deposit->buy_info->vat,
                        $methodName
                    );
                }
            } elseif (isset($deposit->other->order_type) && $deposit->other->order_type == 'batch' && isset($deposit->other->sell_order_id)) {
                // الشراء من batch_sell_order
                $batch = Batch::find($deposit->other->batch_id);
                $sellOrder = \App\Models\BatchSellOrder::find($deposit->other->sell_order_id);
                if ($batch && $sellOrder) {
                    Asset::buyBean(
                        $user,
                        $batch,
                        $sellOrder,
                        $deposit->buy_info->amount,
                        $deposit->amount,
                        $deposit->buy_info->quantity,
                        $deposit->buy_info->charge,
                        $deposit->buy_info->vat,
                        $methodName
                    );
                }
            }

            if (!$isManual) {
                $adminNotification            = new AdminNotification();
                $adminNotification->user_id   = $user->id;
                $adminNotification->title     = 'Deposit successful via ' . $methodName;
                $adminNotification->click_url = urlPath('admin.deposit.successful');
                $adminNotification->save();
            }

            notify($user, $isManual ? 'DEPOSIT_APPROVE' : 'DEPOSIT_COMPLETE', [
                'method_name'     => $methodName,
                'method_currency' => $deposit->method_currency,
                'method_amount'   => showAmount($deposit->final_amount, currencyFormat: false),
                'amount'          => showAmount($deposit->amount, currencyFormat: false),
                'charge'          => showAmount($deposit->charge, currencyFormat: false),
                'rate'            => showAmount($deposit->rate, currencyFormat: false),
                'trx'             => $deposit->trx,
                'post_balance'    => showAmount($user->balance),
            ]);
        }
    }

    public function manualDepositConfirm()
    {
        $track = session()->get('Track');
        $data  = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        if ($data->method_code > 999) {
            $pageTitle = 'Confirm Deposit';
            $method    = $data->gatewayCurrency();
            $gateway   = $method->method;
            return view('Template::user.payment.manual', compact('data', 'pageTitle', 'method', 'gateway'));
        }
        abort(404);
    }

    public function manualDepositUpdate(Request $request)
    {
        $track = session()->get('Track');
        $data  = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        $gatewayCurrency = $data->gatewayCurrency();
        $gateway         = $gatewayCurrency->method;
        $formData        = $gateway->form->form_data;

        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);

        $data->detail = $userData;
        $data->status = Status::PAYMENT_PENDING;
        $data->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $data->user->id;
        $adminNotification->title     = 'Deposit request from ' . $data->user->username;
        $adminNotification->click_url = urlPath('admin.deposit.details', $data->id);
        $adminNotification->save();

        notify($data->user, 'DEPOSIT_REQUEST', [
            'method_name'     => $data->gatewayCurrency()->name,
            'method_currency' => $data->method_currency,
            'method_amount'   => showAmount($data->final_amount, currencyFormat: false),
            'amount'          => showAmount($data->amount, currencyFormat: false),
            'charge'          => showAmount($data->charge, currencyFormat: false),
            'rate'            => showAmount($data->rate, currencyFormat: false),
            'trx'             => $data->trx,
        ]);

        $notify[] = ['success', 'You have deposit request has been taken'];
        return to_route('user.deposit.history')->withNotify($notify);
    }

}
