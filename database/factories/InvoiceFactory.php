<?php

namespace Shopmade\Invoicing\Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Shopmade\Invoicing\Enums\InvoiceStatus;
use Shopmade\Invoicing\Models\Invoice;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'job_id' => null,
            'quote_id' => null,
            'status' => InvoiceStatus::Draft,
            'total' => 0,
            'issued_at' => now()->toDateString(),
            'due_at' => now()->addDays(14)->toDateString(),
            'notes' => null,
        ];
    }
}
