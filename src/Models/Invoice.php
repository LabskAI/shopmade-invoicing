<?php

namespace Shopmade\Invoicing\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Shopmade\Invoicing\Database\Factories\InvoiceFactory;
use Shopmade\Invoicing\Enums\ClientPaymentMark;
use Shopmade\Invoicing\Enums\InvoiceStatus;
use Shopmade\Invoicing\Support\JobLink;

#[UseFactory(InvoiceFactory::class)]
#[Fillable(['customer_id', 'job_id', 'quote_id', 'status', 'total', 'issued_at', 'due_at', 'notes', 'client_payment_mark'])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'client_payment_mark' => ClientPaymentMark::class,
            'total' => 'decimal:2',
            'issued_at' => 'date',
            'due_at' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Only usable when shopmade-jobs is installed (see JobLink).
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(JobLink::MODEL);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function recalculateTotal(): void
    {
        $this->total = $this->items->sum(fn (InvoiceItem $item) => $item->qty * $item->unit_price);
        $this->save();
    }
}
