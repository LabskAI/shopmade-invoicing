<?php

namespace Shopmade\Invoicing\Policies;

use App\Models\User;
use Shopmade\Invoicing\Models\Invoice;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        return ! $user->isClient() || $this->isMember($user, $invoice);
    }

    /**
     * Only a client attached to the customer may leave a payment mark.
     */
    public function mark(User $user, Invoice $invoice): bool
    {
        return $user->isClient() && $this->isMember($user, $invoice);
    }

    private function isMember(User $user, Invoice $invoice): bool
    {
        return $user->customers()->where('customers.id', $invoice->customer_id)->exists();
    }
}
