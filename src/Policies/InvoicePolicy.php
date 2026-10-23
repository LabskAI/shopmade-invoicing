<?php

namespace Shopmade\Invoicing\Policies;

use App\Models\User;
use Shopmade\Invoicing\Enums\InvoiceStatus;
use Shopmade\Invoicing\Models\Invoice;

class InvoicePolicy
{
    /**
     * Portal access: the user must belong to the invoice's customer, and drafts stay with staff.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return $invoice->status !== InvoiceStatus::Draft && $this->isMember($user, $invoice);
    }

    /**
     * Only a client attached to the customer may leave a payment mark.
     */
    public function mark(User $user, Invoice $invoice): bool
    {
        return $user->isClient() && $this->view($user, $invoice);
    }

    private function isMember(User $user, Invoice $invoice): bool
    {
        return $user->customers()->where('customers.id', $invoice->customer_id)->exists();
    }
}
