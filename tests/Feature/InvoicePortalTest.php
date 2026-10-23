<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Shopmade\Invoicing\Enums\ClientPaymentMark;
use Shopmade\Invoicing\Enums\InvoiceStatus;
use Shopmade\Invoicing\Models\Invoice;
use Shopmade\Invoicing\Models\InvoiceItem;
use Shopmade\Invoicing\Models\Quote;
use Shopmade\Invoicing\Support\JobLink;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class);

it('records a client payment mark and shows it to staff', function () {
    $customer = Customer::factory()->create();
    $invoice = Invoice::factory()->for($customer)->create(['status' => InvoiceStatus::Sent]);

    $this->actingAs(portalClient($customer))
        ->post(route('portal.invoices.mark', $invoice), ['mark' => 'will_pay'])
        ->assertRedirect(route('portal.invoices.show', $invoice));

    expect($invoice->refresh()->client_payment_mark)->toBe(ClientPaymentMark::WillPay);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('invoices.show', $invoice))
        ->assertOk()
        ->assertSee('Client says')
        ->assertSee('Will pay');
});

it('keeps another customer\'s invoice from a client', function () {
    $invoice = Invoice::factory()->for(Customer::factory())->create(['status' => InvoiceStatus::Sent]);
    $client = portalClient(Customer::factory()->create());

    $this->actingAs($client)->get(route('portal.invoices.show', $invoice))->assertForbidden();
    $this->actingAs($client)->get(route('portal.invoices.pdf', $invoice))->assertForbidden();
    $this->actingAs($client)->post(route('portal.invoices.mark', $invoice), ['mark' => 'paid'])->assertForbidden();

    expect($invoice->refresh()->client_payment_mark)->toBeNull();
});

it('hides drafts from the portal list, page and open count', function () {
    $customer = Customer::factory()->create();
    $draft = Invoice::factory()->for($customer)->create(['status' => InvoiceStatus::Draft, 'notes' => 'Internal draft note']);
    $client = portalClient($customer);

    $this->actingAs($client)->get(route('portal.invoices.index'))->assertOk()->assertDontSee(route('portal.invoices.show', $draft), false);
    $this->actingAs($client)->get(route('portal.invoices.show', $draft))->assertForbidden();
    $this->actingAs($client)->get(route('portal.home'))->assertOk()->assertDontSee('1 open invoice.');
});

it('rounds each line before summing the total', function () {
    $invoice = Invoice::factory()->create();
    InvoiceItem::factory()->for($invoice)->create(['qty' => 1.11, 'unit_price' => 1.11]);
    InvoiceItem::factory()->for($invoice)->create(['qty' => 1.11, 'unit_price' => 1.12]);

    $invoice->load('items')->recalculateTotal();

    expect($invoice->items->map->lineTotal()->all())->toBe([1.23, 1.24])
        ->and($invoice->refresh()->total)->toBe('2.47');
});

it('refuses a quote from another customer on an invoice', function () {
    $quote = Quote::factory()->create();
    $customer = Customer::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('invoices.store'), [
            'customer_id' => $customer->id,
            'quote_id' => $quote->id,
            'status' => 'draft',
            'items' => [['description' => 'Visit', 'qty' => 1, 'unit_price' => 90]],
        ])
        ->assertSessionHasErrors('quote_id');
});

it('accepts a job from the same customer and refuses one from another', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();
    $ownJob = (JobLink::MODEL)::factory()->for($customer)->create();
    $otherJob = (JobLink::MODEL)::factory()->create();
    $quote = fn (int $jobId) => [
        'customer_id' => $customer->id,
        'job_id' => $jobId,
        'status' => 'draft',
        'items' => [['description' => 'Visit', 'qty' => 1, 'unit_price' => 90]],
    ];

    $this->actingAs($admin)->post(route('quotes.store'), $quote($ownJob->id))->assertSessionHasNoErrors();
    $this->actingAs($admin)->post(route('quotes.store'), $quote($otherJob->id))->assertSessionHasErrors('job_id');
})->skip(! JobLink::installed(), 'Runs when shopmade-jobs is installed next to invoicing.');
