<?php

namespace Shopmade\Invoicing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shopmade\Invoicing\Models\Quote;
use Shopmade\Invoicing\Models\QuoteItem;

/**
 * @extends Factory<QuoteItem>
 */
class QuoteItemFactory extends Factory
{
    protected $model = QuoteItem::class;

    public function definition(): array
    {
        return [
            'quote_id' => Quote::factory(),
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
