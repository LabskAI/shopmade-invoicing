<?php

namespace Shopmade\Invoicing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Shopmade\Invoicing\Enums\QuoteStatus;
use Shopmade\Invoicing\Models\Quote;
use Shopmade\Invoicing\Models\QuoteItem;
use Shopmade\Invoicing\Support\JobLink;

class QuoteController extends Controller
{
    public function index(Request $request): View
    {
        $quotes = Quote::query()
            ->with(JobLink::with(['customer']))
            ->when($request->string('status')->toString(), fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('invoicing::quotes.index', [
            'quotes' => $quotes,
            'statuses' => QuoteStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('invoicing::quotes.create', $this->formData($request));
    }

    public function store(Request $request): RedirectResponse
    {
        $quote = DB::transaction(function () use ($request) {
            $data = $this->validated($request);
            $items = $data['items'];
            unset($data['items']);

            $quote = Quote::query()->create($data);
            $this->syncItems($quote, $items);
            $quote->recalculateTotal();

            return $quote;
        });

        return redirect()->route('quotes.show', $quote)->with('status', 'Quote created.');
    }

    public function show(Quote $quote): View
    {
        $quote->load(JobLink::with(['customer', 'items', 'invoice']));

        return view('invoicing::quotes.show', compact('quote'));
    }

    public function edit(Quote $quote): View
    {
        $quote->load('items');

        return view('invoicing::quotes.edit', array_merge($this->formData(), compact('quote')));
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        DB::transaction(function () use ($request, $quote) {
            $data = $this->validated($request);
            $items = $data['items'];
            unset($data['items']);

            $quote->update($data);
            $quote->items()->delete();
            $this->syncItems($quote, $items);
            $quote->recalculateTotal();
        });

        return redirect()->route('quotes.show', $quote)->with('status', 'Quote updated.');
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        return redirect()->route('quotes.index')->with('status', 'Quote deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'job_id' => JobLink::rules($request->integer('customer_id')),
            'status' => ['required', Rule::enum(QuoteStatus::class)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function syncItems(Quote $quote, array $items): void
    {
        foreach ($items as $item) {
            QuoteItem::query()->create([
                'quote_id' => $quote->id,
                'description' => $item['description'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
            ]);
        }
    }

    private function formData(?Request $request = null): array
    {
        return [
            'customers' => Customer::query()->orderBy('name')->get(),
            'jobs' => JobLink::options(),
            'statuses' => QuoteStatus::cases(),
            'selectedCustomerId' => $request?->integer('customer_id') ?: null,
            'selectedJobId' => $request?->integer('job_id') ?: null,
        ];
    }
}
