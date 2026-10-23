<?php

namespace Shopmade\Invoicing\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shopmade\Invoicing\Database\Factories\InvoiceItemFactory;

#[UseFactory(InvoiceItemFactory::class)]
#[Fillable(['invoice_id', 'description', 'qty', 'unit_price'])]
class InvoiceItem extends Model
{
    /** @use HasFactory<InvoiceItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'unit_price' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function lineTotal(): float
    {
        return round((float) $this->qty * (float) $this->unit_price, 2);
    }
}
