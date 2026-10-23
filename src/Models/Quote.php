<?php

namespace Shopmade\Invoicing\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Shopmade\Invoicing\Database\Factories\QuoteFactory;
use Shopmade\Invoicing\Enums\QuoteStatus;
use Shopmade\Invoicing\Support\JobLink;

#[UseFactory(QuoteFactory::class)]
#[Fillable(['customer_id', 'job_id', 'status', 'total'])]
class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => QuoteStatus::class,
            'total' => 'decimal:2',
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

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function recalculateTotal(): void
    {
        $this->total = $this->items->sum(fn (QuoteItem $item) => $item->qty * $item->unit_price);
        $this->save();
    }
}
