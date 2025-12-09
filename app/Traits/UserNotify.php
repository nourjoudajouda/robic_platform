<?php
namespace App\Traits;

use App\Constants\Status;

trait UserNotify
{
    public static function notifyToUser()
    {
        return [
            'allUsers'               => 'All Users',
            'selectedUsers'          => 'Selected Users',

            'buyGoldUsers'           => 'Buy Gold Users',
            'sellGoldUsers'          => 'Sell Gold Users',
            'giftGoldUsers'          => 'Gift Gold Users',
            'redeemGoldUsers'        => 'All Redeem Gold Users',
            'processingRedeemUsers'  => 'Processing Redeem Users',
            'deliveredRedeemUsers'   => 'Delivered Redeem Users',
            'cancelledRedeemUsers'   => 'Cancelled Redeem Users',

            'kycUnverified'          => 'Kyc Unverified Users',
            'kycVerified'            => 'Kyc Verified Users',
            'kycPending'             => 'Kyc Pending Users',
            'withBalance'            => 'With Balance Users',
            'emptyBalanceUsers'      => 'Empty Balance Users',
            'twoFaDisableUsers'      => '2FA Disable User',
            'twoFaEnableUsers'       => '2FA Enable User',
            'hasDepositedUsers'      => 'Deposited Users',
            'notDepositedUsers'      => 'Not Deposited Users',
            'pendingDepositedUsers'  => 'Pending Deposited Users',
            'rejectedDepositedUsers' => 'Rejected Deposited Users',
            'topDepositedUsers'      => 'Top Deposited Users',
            'hasWithdrawUsers'       => 'Withdraw Users',
            'pendingWithdrawUsers'   => 'Pending Withdraw Users',
            'rejectedWithdrawUsers'  => 'Rejected Withdraw Users',
            'pendingTicketUser'      => 'Pending Ticket Users',
            'answerTicketUser'       => 'Answer Ticket Users',
            'closedTicketUser'       => 'Closed Ticket Users',
            'notLoginUsers'          => 'Last Few Days Not Login Users',
        ];
    }

    public function scopeSelectedUsers($query)
    {
        return $query->whereIn('id', request()->user ?? []);
    }

    public function scopeAllUsers($query)
    {
        return $query;
    }

    public function scopeBuyGoldUsers($query)
    {
        return $query->whereHas('goldHistories', function ($query) {
            $query->buy();
        });
    }

    public function scopeSellGoldUsers($query)
    {
        return $query->whereHas('goldHistories', function ($query) {
            $query->sell();
        });
    }

    public function scopeGiftGoldUsers($query)
    {
        return $query->whereHas('goldHistories', function ($query) {
            $query->gift();
        });
    }

    public function scopeRedeemGoldUsers($query)
    {
        return $query->whereHas('goldHistories', function ($q) {
            $q->redeem();
        });
    }

    public function scopeProcessingRedeemUsers($query)
    {
        return $query->whereHas('goldHistories', function ($query) {
            $query->redeem()->whereHas('redeemData', function ($q) {
                $q->where('status', Status::REDEEM_STATUS_PROCESSING);
            });
        });
    }

    public function scopeDeliveredRedeemUsers($query)
    {
        return $query->whereHas('goldHistories', function ($query) {
            $query->redeem()->whereHas('redeemData', function ($q) {
                $q->where('status', Status::REDEEM_STATUS_DELIVERED);
            });
        });
    }

    public function scopeCancelledRedeemUsers($query)
    {
        return $query->whereHas('goldHistories', function ($query) {
            $query->redeem()->whereHas('redeemData', function ($q) {
                $q->where('status', Status::REDEEM_STATUS_CANCELLED);
            });
        });
    }

    public function scopeEmptyBalanceUsers($query)
    {
        return $query->where('balance', '<=', 0);
    }

    public function scopeTwoFaDisableUsers($query)
    {
        return $query->where('ts', Status::DISABLE);
    }

    public function scopeTwoFaEnableUsers($query)
    {
        return $query->where('ts', Status::ENABLE);
    }

    public function scopeHasDepositedUsers($query)
    {
        return $query->whereHas('deposits', function ($deposit) {
            $deposit->successful();
        });
    }

    public function scopeNotDepositedUsers($query)
    {
        return $query->whereDoesntHave('deposits', function ($q) {
            $q->successful();
        });
    }

    public function scopePendingDepositedUsers($query)
    {
        return $query->whereHas('deposits', function ($deposit) {
            $deposit->pending();
        });
    }

    public function scopeRejectedDepositedUsers($query)
    {
        return $query->whereHas('deposits', function ($deposit) {
            $deposit->rejected();
        });
    }

    public function scopeTopDepositedUsers($query)
    {
        return $query->whereHas('deposits', function ($deposit) {
            $deposit->successful();
        })->withSum(['deposits' => function ($q) {
            $q->successful();
        }], 'amount')->orderBy('deposits_sum_amount', 'desc')->take(request()->number_of_top_deposited_user ?? 10);
    }

    public function scopeHasWithdrawUsers($query)
    {
        return $query->whereHas('withdrawals', function ($q) {
            $q->approved();
        });
    }

    public function scopePendingWithdrawUsers($query)
    {
        return $query->whereHas('withdrawals', function ($q) {
            $q->pending();
        });
    }

    public function scopeRejectedWithdrawUsers($query)
    {
        return $query->whereHas('withdrawals', function ($q) {
            $q->rejected();
        });
    }

    public function scopePendingTicketUser($query)
    {
        return $query->whereHas('tickets', function ($q) {
            $q->whereIn('status', [Status::TICKET_OPEN, Status::TICKET_REPLY]);
        });
    }

    public function scopeClosedTicketUser($query)
    {
        return $query->whereHas('tickets', function ($q) {
            $q->where('status', Status::TICKET_CLOSE);
        });
    }

    public function scopeAnswerTicketUser($query)
    {
        return $query->whereHas('tickets', function ($q) {

            $q->where('status', Status::TICKET_ANSWER);
        });
    }

    public function scopeNotLoginUsers($query)
    {
        return $query->whereDoesntHave('loginLogs', function ($q) {
            $q->whereDate('created_at', '>=', now()->subDays(request()->number_of_days ?? 10));
        });
    }

    public function scopeKycVerified($query)
    {
        return $query->where('kv', Status::KYC_VERIFIED);
    }

}
