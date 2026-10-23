<div class="mb-6 grid grid-cols-1 gap-0.5 border-2 border-line bg-line sm:grid-cols-2">
    <a href="{{ route('quotes.index') }}" class="block text-ink hover:text-ink"><x-stat label="Open quotes" :value="$openQuotes" /></a>
    <a href="{{ route('invoices.index') }}" class="block text-ink hover:text-ink"><x-stat label="Unpaid invoices" :value="$unpaidInvoices" /></a>
</div>
