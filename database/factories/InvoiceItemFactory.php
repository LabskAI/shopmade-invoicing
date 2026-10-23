<?php

namespace Shopmade\Invoicing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shopmade\Invoicing\Models\Invoice;
use Shopmade\Invoicing\Models\InvoiceItem;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'description' => fake()->randomElement([
                'Standard clean',
                'Deep clean kitchen',
                'Move-out clean',
                'Window wash',
                'Carpet shampoo',
            ]),
            'qty' => fake()->numberBetween(1, 4),
            'unit_price' => fake()->randomElement([45, 65, 85, 120, 150]),
        ];
    }
}
