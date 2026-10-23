<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Shopmade\Invoicing\Enums\InvoiceStatus;
use Shopmade\Invoicing\Enums\QuoteStatus;
use Shopmade\Invoicing\Models\Invoice;
use Shopmade\Invoicing\Models\InvoiceItem;
use Shopmade\Invoicing\Models\Quote;
use Shopmade\Invoicing\Models\QuoteItem;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class);

it('redirects guests away from invoices', function () {
    $this->get(route('invoices.index'))->assertRedirect(route('login'));
});

it('lets a user view invoices but not create them', function () {
    $user = User::factory()->user()->create();
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatus::Sent,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Invoices');

    $this->actingAs($user)
        ->get(route('invoices.index'))
        ->assertOk()
        ->assertSee($invoice->customer->name)
        ->assertDontSee('New invoice');

    $this->actingAs($user)
        ->get(route('invoices.show', $invoice))
        ->assertOk()
        ->assertSee(route('invoices.pdf', $invoice), false)
        ->assertSee('Download PDF')
        ->assertDontSee(route('invoices.edit', $invoice), false);

    $this->actingAs($user)
        ->get(route('invoices.create'))
        ->assertForbidden();

    $this->actingAs($user)
        ->post(route('invoices.store'), [
            'customer_id' => $invoice->customer_id,
            'status' => InvoiceStatus::Draft->value,
            'items' => [['description' => 'Kitchen', 'qty' => 1, 'unit_price' => 100]],
        ])
        ->assertForbidden();
});

it('lets a builder create an invoice', function () {
    $builder = User::factory()->superAdmin()->create();
    $customer = Customer::factory()->create();

    $this->actingAs($builder)
        ->get(route('invoices.index'))
        ->assertOk()
        ->assertSee('New invoice');

    $this->actingAs($builder)
        ->post(route('invoices.store'), [
            'customer_id' => $customer->id,
            'status' => InvoiceStatus::Draft->value,
            'issued_at' => now()->toDateString(),
            'due_at' => now()->addDays(14)->toDateString(),
            'items' => [
                ['description' => 'Kitchen deep clean', 'qty' => 1, 'unit_price' => 160],
            ],
        ])
        ->assertRedirect();

    $invoice = Invoice::query()->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->customer_id)->toBe($customer->id)
        ->and((float) $invoice->total)->toBe(160.0)
        ->and($invoice->items)->toHaveCount(1);
});

it('converts an accepted quote into an invoice and will not duplicate it', function () {
    $builder = User::factory()->superAdmin()->create();
    $quote = Quote::factory()->create([
        'status' => QuoteStatus::Accepted,
    ]);
    QuoteItem::factory()->create([
        'quote_id' => $quote->id,
        'description' => 'Move-out clean',
        'qty' => 1,
        'unit_price' => 280,
    ]);

    $this->actingAs($builder)
        ->post(route('quotes.invoice.store', $quote))
        ->assertRedirect();

    $invoice = $quote->fresh()->invoice;

    expect($invoice)->not->toBeNull()
        ->and($invoice->quote_id)->toBe($quote->id)
        ->and((float) $invoice->total)->toBe(280.0);

    $this->actingAs($builder)
        ->post(route('quotes.invoice.store', $quote))
        ->assertRedirect(route('invoices.show', $invoice));

    expect(Invoice::query()->count())->toBe(1);

    $user = User::factory()->user()->create();
    $other = Quote::factory()->create([
        'status' => QuoteStatus::Sent,
    ]);

    $this->actingAs($user)
        ->post(route('quotes.invoice.store', $other))
        ->assertForbidden();
});

it('lets a user download an invoice PDF', function () {
    $user = User::factory()->user()->create();
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatus::Sent,
    ]);
    InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'description' => 'Kitchen deep clean',
        'qty' => 1,
        'unit_price' => 160,
    ]);
    $invoice->load('items')->recalculateTotal();

    $response = $this->actingAs($user)
        ->get(route('invoices.pdf', $invoice))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect($response->getContent())->toStartWith('%PDF')
        ->and($response->headers->get('content-disposition'))->toContain('invoice-'.$invoice->id.'.pdf');
});

it('redirects guests away from the invoice PDF', function () {
    $invoice = Invoice::factory()->create();

    $this->get(route('invoices.pdf', $invoice))->assertRedirect(route('login'));
});

it('requires line items when creating an invoice', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $this->actingAs($admin)
        ->post(route('invoices.store'), [
            'customer_id' => $customer->id,
            'status' => InvoiceStatus::Draft->value,
            'items' => [],
        ])
        ->assertSessionHasErrors('items');
});
