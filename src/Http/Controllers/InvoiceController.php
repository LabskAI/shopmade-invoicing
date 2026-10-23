<?php

namespace Shopmade\Invoicing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Shopmade\Invoicing\Enums\InvoiceStatus;
use Shopmade\Invoicing\Enums\QuoteStatus;
use Shopmade\Invoicing\Models\Invoice;
use Shopmade\Invoicing\Models\InvoiceItem;
use Shopmade\Invoicing\Models\Quote;
use Shopmade\Invoicing\Support\JobLink;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $invoices = Invoice::query()
            ->with(JobLink::with(['customer']))
            ->when($request->string('status')->toString(), fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('invoicing::invoices.index', [
            'invoices' => $invoices,
            'statuses' => InvoiceStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('invoicing::invoices.create', $this->formData($request));
    }

    public function store(Request $request): RedirectResponse
    {
        $invoice = DB::transaction(function () use ($request) {
            $data = $this->validated($request);
            $items = $data['items'];
            unset($data['items']);

            $invoice = Invoice::query()->create($data);
            $this->syncItems($invoice, $items);
            $invoice->recalculateTotal();

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('status', 'Invoice created.');
    }

    public function storeFromQuote(Quote $quote): RedirectResponse
    {
        if ($quote->invoice) {
            return redirect()->route('invoices.show', $quote->invoice)
                ->with('status', 'This quote already has an invoice.');
        }

        if ($quote->status === QuoteStatus::Draft) {
            return redirect()->route('quotes.show', $quote)
                ->with('status', 'Send or accept the quote before invoicing it.');
        }

        $invoice = DB::transaction(function () use ($quote) {
            $quote->load('items');

            $invoice = Invoice::query()->create([
                'customer_id' => $quote->customer_id,
                'job_id' => $quote->job_id,
                'quote_id' => $quote->id,
                'status' => $quote->status === QuoteStatus::Paid ? InvoiceStatus::Paid : InvoiceStatus::Sent,
                'issued_at' => now()->toDateString(),
                'due_at' => now()->addDays(14)->toDateString(),
            ]);

            $this->syncItems($invoice, $quote->items->map(fn ($item) => [
                'description' => $item->description,
                'qty' => $item->qty,
                'unit_price' => $item->unit_price,
            ])->all());

            $invoice->recalculateTotal();

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('status', 'Invoice created from quote.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(JobLink::with(['customer', 'quote', 'items']));

        return view('invoicing::invoices.show', compact('invoice'));
    }

    public function pdf(Invoice $invoice): Response
    {
        $invoice->load(JobLink::with(['customer', 'items']));

        return Pdf::loadView('invoicing::invoices.pdf', [
            'invoice' => $invoice,
            'orgName' => config('app.org_name'),
        ])->setPaper('letter')->download('invoice-'.$invoice->id.'.pdf');
    }

    public function edit(Invoice $invoice): View
    {
        $invoice->load('items');

        return view('invoicing::invoices.edit', array_merge($this->formData(), compact('invoice')));
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        DB::transaction(function () use ($request, $invoice) {
            $data = $this->validated($request, $invoice);
            $items = $data['items'];
            unset($data['items']);

            $invoice->update($data);
            $invoice->items()->delete();
            $this->syncItems($invoice, $items);
            $invoice->recalculateTotal();
        });

        return redirect()->route('invoices.show', $invoice)->with('status', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return redirect()->route('invoices.index')->with('status', 'Invoice deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Invoice $invoice = null): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'job_id' => JobLink::rules($request->integer('customer_id')),
            'quote_id' => [
                'nullable',
                Rule::exists('quotes', 'id')->where('customer_id', $request->integer('customer_id')),
                Rule::unique('invoices', 'quote_id')->ignore($invoice),
            ],
            'status' => ['required', Rule::enum(InvoiceStatus::class)],
            'issued_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);
    }

    /**
     * @param  list<array{description: string, qty: mixed, unit_price: mixed}>  $items
     */
    private function syncItems(Invoice $invoice, array $items): void
    {
        foreach ($items as $item) {
            InvoiceItem::query()->create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?Request $request = null): array
    {
        return [
            'customers' => Customer::query()->orderBy('name')->get(),
            'jobs' => JobLink::options(),
            'quotes' => Quote::query()->with('customer')->latest()->get(),
            'statuses' => InvoiceStatus::cases(),
            'selectedCustomerId' => $request?->integer('customer_id') ?: null,
            'selectedJobId' => $request?->integer('job_id') ?: null,
            'selectedQuoteId' => $request?->integer('quote_id') ?: null,
        ];
    }
}
