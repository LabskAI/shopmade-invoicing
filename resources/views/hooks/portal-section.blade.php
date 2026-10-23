<x-card title="Invoices" class="mb-4">
    <x-slot:actions>
        <x-button variant="ghost" href="{{ route('portal.invoices.index') }}"><i data-lucide="receipt" class="size-4"></i>All invoices</x-button>
    </x-slot:actions>
    <p class="text-body">
        @if ($openInvoices === 0)
            Nothing to pay right now.
        @else
            {{ $openInvoices }} open {{ \Illuminate\Support\Str::plural('invoice', $openInvoices) }}.
        @endif
    </p>
</x-card>
