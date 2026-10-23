<?php

namespace Shopmade\Invoicing\Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Shopmade\Invoicing\Enums\QuoteStatus;
use Shopmade\Invoicing\Models\Quote;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'job_id' => null,
            'status' => fake()->randomElement(QuoteStatus::cases()),
            'total' => 0,
        ];
    }
}
