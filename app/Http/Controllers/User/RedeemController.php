<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\ChargeLimit;
use App\Models\BeanHistory;
use App\Models\Product;
use App\Models\RedeemData;
use App\Models\RedeemUnit;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RedeemController extends Controller
{
    public function __construct()
    {
        if (!gs('redeem_option')) {
            abort(404);
        }
    }

    public function redeemForm()
    {
        $pageTitle   = 'Shipping and receiving';
        
        // جلب جميع الأصول وتجميعها حسب المنتج
        $userAssets = Asset::where('user_id', auth()->id())
            ->with(['product.unit', 'product.currency', 'batch.warehouse'])
            ->get();
        
        // تجميع الأصول حسب product_id
        $assets = $userAssets->groupBy('product_id')->map(function ($groupedAssets) {
            $firstAsset = $groupedAssets->first();
            $totalQuantity = $groupedAssets->sum('quantity');
            
            // حساب سعر السوق للمنتج
            $marketPrice = \App\Models\Batch::calculateMarketPrice($firstAsset->product_id) ?? 0;
            
            // إنشاء object يحتوي على المعلومات المجمعة
            return (object) [
                'product_id' => $firstAsset->product_id,
                'product' => $firstAsset->product,
                'quantity' => $totalQuantity,
                'unit' => $firstAsset->product->unit ?? null,
                'price' => $marketPrice,
                'warehouse' => $firstAsset->batch->warehouse ?? null,
                'asset_ids' => $groupedAssets->pluck('id')->toArray(), // للاستخدام عند الحفظ
            ];
        })->values();
        
        $redeemUnits = RedeemUnit::active()->get();
        $chargeLimit = ChargeLimit::where('slug', 'redeem')->first();
        $shippingMethods = \App\Models\ShippingMethod::active()->get();

        return view('Template::user.redeem.form', compact('pageTitle', 'assets', 'redeemUnits', 'chargeLimit', 'shippingMethods'));
    }

    public function redeemStore(Request $request)
    {
        $request->validate([
            'product_id'     => 'required|integer|gt:0',
            'asset_ids'      => 'required|json',
            'quantity'       => 'required|numeric|gt:0',
            'delivery_type'  => 'required|in:pickup,shipping',
            'shipping_lat'   => 'required_if:delivery_type,shipping|nullable|numeric',
            'shipping_lng'   => 'required_if:delivery_type,shipping|nullable|numeric',
            'shipping_method_id' => 'required_if:delivery_type,shipping|nullable|integer|exists:shipping_methods,id',
            'shipping_cost'  => 'required_if:delivery_type,shipping|nullable|numeric|min:0',
            'distance'       => 'nullable|numeric|min:0',
        ]);

        $user = auth()->user();
        $product = Product::with('unit', 'currency')->findOrFail($request->product_id);
        $assetIds = json_decode($request->asset_ids, true);
        $quantity = $request->quantity;
        
        // جلب جميع الأصول المطلوبة والتحقق من الملكية
        $assets = Asset::where('user_id', $user->id)
            ->whereIn('id', $assetIds)
            ->where('product_id', $product->id)
            ->get();
        
        $totalAvailableQuantity = $assets->sum('quantity');
        
        if ($totalAvailableQuantity < $quantity) {
            $notify[] = ['error', 'Insufficient quantity. Available: ' . showAmount($totalAvailableQuantity, 4, currencyFormat: false)];
            return back()->withNotify($notify);
        }

        // المستخدم يستلم من مخزونه - لا توجد تكلفة منتج
        $amount = 0;
        $shippingCost = $request->delivery_type === 'shipping' ? $request->shipping_cost : 0;
        $totalAmount = $shippingCost; // التكلفة الكلية = تكلفة الشحن فقط
        
        // التحقق من الرصيد (فقط لتكلفة الشحن)
        if ($shippingCost > 0 && $user->balance < $shippingCost) {
            $notify[] = ['error', 'Insufficient balance for shipping cost. Your balance: ' . showAmount($user->balance) . ', Required: ' . showAmount($shippingCost)];
            return back()->withNotify($notify);
        }

        // معالجة الطلب في transaction
        DB::transaction(function () use ($user, $assets, $product, $quantity, $amount, $shippingCost, $request) {
            $remainingQuantity = $quantity;
            
            // خصم الكمية من الأصول
            foreach ($assets as $asset) {
                if ($remainingQuantity <= 0) break;
                
                $qtyToDeduct = min($remainingQuantity, $asset->quantity);
                $asset->quantity -= $qtyToDeduct;
                $asset->save();
                
                $remainingQuantity -= $qtyToDeduct;
                
                // حذف الأصل إذا أصبحت الكمية 0
                if ($asset->quantity <= 0) {
                    $asset->delete();
                }
            }
            
            // خصم تكلفة الشحن من رصيد المستخدم (إذا كان شحن)
            if ($shippingCost > 0) {
                $user->balance -= $shippingCost;
                $user->save();
                
                // تحديث wallet
                $wallet = Wallet::where('user_id', $user->id)->first();
                if ($wallet) {
                    $wallet->balance -= $shippingCost;
                    $wallet->save();
                }
                
                // إنشاء Transaction للشحن
                $transaction = new Transaction();
                $transaction->user_id = $user->id;
                $transaction->amount = $shippingCost;
                $transaction->post_balance = $user->balance;
                $transaction->charge = 0;
                $transaction->trx_type = '-';
                $transaction->details = 'Shipping cost for ' . ($request->delivery_type === 'shipping' ? 'delivery' : 'pickup');
                $transaction->trx = getTrx();
                $transaction->remark = 'shipping_cost';
                $transaction->save();
        }

            // إنشاء سجل Redeem في BeanHistory
            $redeemHistory = new BeanHistory();
            $redeemHistory->user_id = $user->id;
            $redeemHistory->product_id = $product->id;
            $redeemHistory->quantity = $quantity;
            $redeemHistory->item_unit_id = $product->unit_id;
            $redeemHistory->amount = $amount;
            $redeemHistory->currency_id = $product->currency_id;
            $redeemHistory->charge = $shippingCost; // استخدام حقل charge لحفظ تكلفة الشحن
            $redeemHistory->trx = getTrx();
            $redeemHistory->type = Status::REDEEM_HISTORY;
            $redeemHistory->save();
            
            // إنشاء سجل RedeemData
            $redeemData = new RedeemData();
            $redeemData->bean_history_id = $redeemHistory->id;
            $redeemData->delivery_type = $request->delivery_type;

            if ($request->delivery_type === 'shipping') {
                $shippingMethod = \App\Models\ShippingMethod::findOrFail($request->shipping_method_id);
                $redeemData->delivery_address = 'Location: ' . $request->shipping_lat . ', ' . $request->shipping_lng;
                $redeemData->shipping_method_id = $request->shipping_method_id;
                $redeemData->shipping_cost = $shippingCost;
                $redeemData->distance = $request->distance;
            } else {
                $redeemData->delivery_address = 'Pickup from warehouse';
            }
            
            $redeemData->status = Status::REDEEM_STATUS_PROCESSING;
            $redeemData->save();
        });
        
        $successMessage = 'Shipping request submitted successfully!';
        if ($shippingCost > 0) {
            $successMessage .= ' Shipping cost of ' . showAmount($shippingCost) . ' has been deducted from your balance.';
        }
        $notify[] = ['success', $successMessage];
        return to_route('user.redeem.success.page')->withNotify($notify);
    }

    public function address()
    {
        $pageTitle = 'Shipping Address';
        $redeemData = session()->get('redeem_data');

        if (!$redeemData) {
            $notify[] = ['error', 'Invalid session data'];
            return to_route('user.redeem.form')->withNotify($notify);
        }

        return view('Template::user.redeem.address', compact('pageTitle','redeemData'));
    }

    public function addressStore(Request $request)
    {
        $redeemData = session()->get('redeem_data');

        if (!$redeemData) {
            $notify[] = ['error', 'Invalid session data'];
            return to_route('user.redeem.form')->withNotify($notify);
        }

        $request->validate([
            'address' => 'required|string',
        ]);        

        $user  = auth()->user();
        $asset = Asset::findOrFail($redeemData->asset_id);

        if ($asset->quantity < $redeemData->total_quantity) {
            session()->forget('redeem_data');
            $notify[] = ['error', 'Insufficient gold asset'];
            return back()->withNotify($notify);
        }

        if ($user->balance < $redeemData->charge) {
            session()->forget('redeem_data');
            $notify[] = ['error', 'Insufficient balance for charge'];
            return back()->withNotify($notify);
        }

        $asset->quantity -= $redeemData->total_quantity;
        $asset->save();

        $user->balance -= $redeemData->charge;
        $user->save();

        $trx = getTrx();

        $redeemHistory              = new BeanHistory();
        $redeemHistory->user_id     = $user->id;
        $redeemHistory->asset_id    = $asset->id;
        $redeemHistory->batch_id    = $asset->batch_id;
        $redeemHistory->quantity    = $redeemData->total_quantity;
        $redeemHistory->item_unit_id = $asset->batch && $asset->batch->product ? $asset->batch->product->unit_id : null;
        $redeemHistory->amount      = $redeemData->amount;
        $redeemHistory->currency_id = $asset->batch && $asset->batch->product ? $asset->batch->product->currency_id : null;
        $redeemHistory->charge      = $redeemData->charge;
        $redeemHistory->trx         = $trx;
        $redeemHistory->type        = Status::REDEEM_HISTORY;
        $redeemHistory->save();

        $orderData = [
            'asset_id' => $asset->id,
            'items'    => $redeemData->order_details,
        ];

        $address = 'Address: ' . $request->address;
        $address .= $request->city ? ', City: ' . $request->city : '';
        $address .= $request->state ? ', State: ' . $request->state : '';
        $address .= $request->zip_code ? ', Zip Code: ' . $request->zip_code : '';

        $redeemDataLog                   = new RedeemData();
        $redeemDataLog->bean_history_id  = $redeemHistory->id;
        $redeemDataLog->delivery_address = $address;
        $redeemDataLog->order_details    = $orderData;
        $redeemDataLog->status           = Status::REDEEM_STATUS_PROCESSING;
        $redeemDataLog->save();

        $transaction               = new Transaction();
        $transaction->user_id      = $user->id;
        $transaction->amount       = $redeemData->charge;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '-';
        $transaction->details      = 'Redeem Green Coffee';
        $transaction->trx          = $trx;
        $transaction->remark       = 'redeem_gold';
        $transaction->save();

        $sentence = collect($orderData['items'])->pluck('text')->toArray();
        $sentence = count($sentence) > 1 ? implode(', ', array_slice($sentence, 0, -1)) . ' and ' . end($sentence) : $sentence[0];

        notify($user, 'REDEEM_GOLD', [
            'category' => $asset->category->name,
            'quantity' => showAmount($redeemHistory->total_quantity, 4, currencyFormat: false),
            'amount'   => showAmount($redeemHistory->amount),
            'charge'   => showAmount($redeemHistory->charge),
            'trx'      => $transaction->trx,
            'details'  => $sentence,
        ]);

        $notify[] = ['success', 'Gold redeemed successfully'];
        return to_route('user.redeem.success.page')->withNotify($notify)->with('redeem_history', $redeemHistory);
    }

    public function successPage()
    {
        $pageTitle     = 'Shipping Success';
        $redeemHistory = session()->get('redeem_history');
        if ($redeemHistory) {
            $redeemHistory = BeanHistory::with('product.unit', 'product.currency', 'redeemData.shippingMethod')->find($redeemHistory->id);
        } else {
            $redeemHistory = BeanHistory::with('product.unit', 'product.currency', 'redeemData.shippingMethod')->latest()->first();
        }

        if (!$redeemHistory) {
            $notify[] = ['error', 'Invalid session data'];
            return to_route('user.redeem.form')->withNotify($notify);
        }
        return view('Template::user.redeem.success', compact('pageTitle', 'redeemHistory'));
    }

    public function history()
    {
        $pageTitle       = 'Shipping History';
        $redeemHistories = BeanHistory::redeem()->where('user_id', auth()->id())->with('product.unit', 'redeemData.shippingMethod', 'currency')->orderBy('id', 'desc')->paginate(getPaginate());

        return view('Template::user.redeem.history', compact('pageTitle', 'redeemHistories'));
    }

}
