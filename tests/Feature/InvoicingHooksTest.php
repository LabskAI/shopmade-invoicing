<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Shopmade\Invoicing\Enums\InvoiceStatus;
use Shopmade\Invoicing\Models\Invoice;
use Shopmade\Invoicing\Support\JobLink;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class);

it('adds invoice counts to the dashboard and invoices to the customer page', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();
    Invoice::factory()->for($customer)->create(['status' => InvoiceStatus::Sent, 'total' => 240]);
    Invoice::factory()->create(['status' => InvoiceStatus::Paid]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Unpaid invoices', '1'])
        ->assertSee(route('quotes.index'), false);

    $this->actingAs($admin)
        ->get(route('customers.show', $customer))
        ->assertOk()
        ->assertSee('$240.00')
        ->assertSee(route('invoices.create', ['customer_id' => $customer->id]), false);
});

it('gives clients an Invoices tab and counts only their open invoices', function () {
    $customer = Customer::factory()->create();
    Invoice::factory()->for($customer)->create(['status' => InvoiceStatus::Sent]);
    Invoice::factory()->for($customer)->create(['status' => InvoiceStatus::Void]);
    Invoice::factory()->create(['status' => InvoiceStatus::Sent]);

    $this->actingAs(portalClient($customer))
        ->get(route('portal.home'))
        ->assertOk()
        ->assertSee('1 open invoice.')
        ->assertSee(route('portal.invoices.index'), false);
});

it('hides the job field when the jobs package is not installed', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $page = $this->actingAs($admin)->get(route('invoices.create'))->assertOk();

    JobLink::installed() ? $page->assertSee('Job (optional)') : $page->assertDontSee('Job (optional)');

    $this->actingAs($admin)
        ->post(route('quotes.store'), [
            'customer_id' => $customer->id,
            'job_id' => 999,
            'status' => 'draft',
            'items' => [['description' => 'Visit', 'qty' => 1, 'unit_price' => 90]],
        ])
        ->assertSessionHasErrors('job_id');
});

it('lists unpaid invoices over MCP', function () {
    require_once base_path('tests/Feature/Agents/Mcp/McpHelpers.php');

    Invoice::factory()->create(['status' => InvoiceStatus::Overdue]);
    Invoice::factory()->create(['status' => InvoiceStatus::Paid]);

    [$bearer, $session] = mcpDirector(['operator']);

    expect(mcpCall('invoices_list', ['unpaid' => true], $bearer, $session)->assertOk()->json('result.structuredContent.invoices'))
        ->toHaveCount(1)
        ->sequence(fn ($invoice) => $invoice->status->toBe('overdue'));
});
