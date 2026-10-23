<x-card class="lg:col-span-3" title="Invoices">
    @if (auth()->user()->canManageCoreTool('invoices'))
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}">New invoice</x-button>
        </x-slot:actions>
    @endif
    @forelse ($invoices as $invoice)
        <div class="flex items-center justify-between border-b border-line-soft py-2 last:border-0">
            <a class="text-control" href="{{ route('invoices.show', $invoice) }}">${{ number_format($invoice->total, 2) }} · {{ $invoice->due_at?->format('M j, Y') ?? 'No due date' }}</a>
            <x-status :state="$invoice->status->value" />
        </div>
    @empty
        <x-empty title="No invoices yet" body="Bill this customer when the work is done." />
    @endforelse
</x-card>
