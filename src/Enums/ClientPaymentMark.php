<?php

namespace Shopmade\Invoicing\Enums;

enum ClientPaymentMark: string
{
    case Paid = 'paid';
    case WillPay = 'will_pay';

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Paid',
            self::WillPay => 'Will pay',
        };
    }
}
