<?php

namespace Shopmade\Invoicing\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shopmade\Invoicing\Database\Factories\QuoteItemFactory;

#[UseFactory(QuoteItemFactory::class)]
#[Fillable(['quote_id', 'description', 'qty', 'unit_price'])]
class QuoteItem extends Model
{
    /** @use HasFactory<QuoteItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'unit_price' => 'decimal:2',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function lineTotal(): float
    {
        return (float) $this->qty * (float) $this->unit_price;
    }
}
