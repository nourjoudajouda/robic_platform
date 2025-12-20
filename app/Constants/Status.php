<?php

namespace App\Constants;

class Status
{

    const ENABLE  = 1;
    const DISABLE = 0;

    const YES = 1;
    const NO  = 0;

    const VERIFIED   = 1;
    const UNVERIFIED = 0;

    const PAYMENT_INITIATE = 0;
    const PAYMENT_SUCCESS  = 1;
    const PAYMENT_PENDING  = 2;
    const PAYMENT_REJECT   = 3;

    const TICKET_OPEN   = 0;
    const TICKET_ANSWER = 1;
    const TICKET_REPLY  = 2;
    const TICKET_CLOSE  = 3;

    const PRIORITY_LOW    = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH   = 3;

    const USER_ACTIVE = 1;
    const USER_BAN    = 0;

    const KYC_UNVERIFIED = 0;
    const KYC_PENDING    = 2;
    const KYC_VERIFIED   = 1;

    const GOOGLE_PAY = 5001;

    const CUR_BOTH = 1;
    const CUR_TEXT = 2;
    const CUR_SYM  = 3;

    const BUY_HISTORY    = 1;
    const SELL_HISTORY   = 2;
    const REDEEM_HISTORY = 3;
    const GIFT_HISTORY   = 4;

    const REDEEM_UNIT_BAR  = 1;
    const REDEEM_UNIT_COIN = 2;

    const REDEEM_STATUS_PROCESSING = 1;
    const REDEEM_STATUS_SHIPPED    = 2;
    const REDEEM_STATUS_DELIVERED  = 3;
    const REDEEM_STATUS_CANCELLED  = 4;

    // Batch Sell Order Status
    const SELL_ORDER_ACTIVE   = 1;
    const SELL_ORDER_INACTIVE = 0;
    const SELL_ORDER_SOLD     = 2;
    const SELL_ORDER_CANCELLED = 3;

    // Pending Buy Order Status
    const PENDING_BUY_ORDER   = 1;
    const PENDING_BUY_FULFILLED = 2;
    const PENDING_BUY_CANCELLED = 3;
    const PENDING_BUY_EXPIRED = 4;
}
