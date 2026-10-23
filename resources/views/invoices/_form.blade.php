@php
    $existing = old('items');
    if (! $existing && isset($invoice)) {
        $existing = $invoice->items->map(fn ($item) => [
            'description' => $item->description,
            'qty' => $item->qty,
            'unit_price' => $item->unit_price,
        ])->all();
    }
    $existing = $existing ?: [['description' => '', 'qty' => 1, 'unit_price' => 0]];
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <x-field label="Customer" for="customer_id" :error="$errors->first('customer_id')">
        <x-select
            id="customer_id"
            name="customer_id"
            required
            :options="$customers->pluck('name', 'id')"
            :selected="old('customer_id', $invoice->customer_id ?? $selectedCustomerId)"
            :error="$errors->has('customer_id')"
        />
    </x-field>
    @if (\Shopmade\Invoicing\Support\JobLink::installed())
        <x-field label="Job (optional)" for="job_id" :error="$errors->first('job_id')">
            <x-select
                id="job_id"
                name="job_id"
                :options="$jobs->mapWithKeys(fn ($jobOption) => [$jobOption->id => $jobOption->customer->name.' — '.($jobOption->scheduled_at?->format('M j') ?? 'unscheduled')])"
                :selected="old('job_id', $invoice->job_id ?? $selectedJobId)"
                placeholder="None"
                :error="$errors->has('job_id')"
            />
        </x-field>
    @endif
    <x-field label="Quote (optional)" for="quote_id" :error="$errors->first('quote_id')">
        <x-select
            id="quote_id"
            name="quote_id"
            :options="$quotes->mapWithKeys(fn ($quoteOption) => [$quoteOption->id => $quoteOption->customer->name.' — $'.number_format($quoteOption->total, 2)])"
            :selected="old('quote_id', $invoice->quote_id ?? $selectedQuoteId)"
            placeholder="None"
            :error="$errors->has('quote_id')"
        />
    </x-field>
    <x-field label="Status" for="status" :error="$errors->first('status')">
        <x-select
            id="status"
            name="status"
            required
            :options="collect($statuses)->mapWithKeys(fn ($status) => [$status->value => $status->label()])"
            :selected="old('status', $invoice->status->value ?? 'draft')"
            :error="$errors->has('status')"
        />
    </x-field>
    <x-field label="Issued" for="issued_at" :error="$errors->first('issued_at')">
        <x-input id="issued_at" name="issued_at" type="date" :value="old('issued_at', isset($invoice) ? $invoice->issued_at?->format('Y-m-d') : now()->format('Y-m-d'))" :error="$errors->has('issued_at')" />
    </x-field>
    <x-field label="Due" for="due_at" :error="$errors->first('due_at')">
        <x-input id="due_at" name="due_at" type="date" :value="old('due_at', isset($invoice) ? $invoice->due_at?->format('Y-m-d') : now()->addDays(14)->format('Y-m-d'))" :error="$errors->has('due_at')" />
    </x-field>
    <x-field class="sm:col-span-2" label="Notes" for="notes" :error="$errors->first('notes')">
        <x-textarea id="notes" name="notes" :error="$errors->has('notes')">{{ old('notes', $invoice->notes ?? '') }}</x-textarea>
    </x-field>
</div>

<div class="mt-6" x-data="invoiceItems({{ Illuminate\Support\Js::from($existing) }})">
    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-card-title">Line items</h2>
        <x-button variant="ghost" type="button" @click="add()">Add line</x-button>
    </div>
    <template x-for="(item, index) in items" :key="index">
        <div class="mb-3 grid gap-3 sm:grid-cols-12">
            <input :name="`items[${index}][description]`" x-model="item.description" placeholder="Description" class="h-10 border-2 border-line bg-surface px-3 text-control text-ink sm:col-span-6">
            <input type="number" step="0.01" min="0.01" :name="`items[${index}][qty]`" x-model="item.qty" placeholder="Qty" class="h-10 border-2 border-line bg-surface px-3 text-control tabular-nums text-ink sm:col-span-2">
            <input type="number" step="0.01" min="0" :name="`items[${index}][unit_price]`" x-model="item.unit_price" placeholder="Price" class="h-10 border-2 border-line bg-surface px-3 text-control tabular-nums text-ink sm:col-span-3">
            <button type="button" class="ds-btn inline-flex h-10 items-center justify-start border-2 border-line px-3 text-control text-accent-700 sm:col-span-1" @click="remove(index)" x-show="items.length > 1" aria-label="Remove line">
                <i data-lucide="x" class="size-4"></i>
            </button>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('invoiceItems', (items) => ({
            items,
            add() { this.items.push({ description: '', qty: 1, unit_price: 0 }); },
            remove(index) { this.items.splice(index, 1); },
        }));
    });
</script>
