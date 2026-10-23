@extends('layouts.app')

@section('title', 'Invoices')
@section('breadcrumb', 'Invoices')

@section('content')
    <x-page-header title="Invoices" kicker="Work">
        <x-slot:actions>
            @if (auth()->user()->canManageCoreTool('invoices'))
                <x-button href="{{ route('invoices.create') }}"><i data-lucide="plus" class="size-4"></i>New invoice</x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="border-2 border-line bg-surface">
        <form method="GET">
            <x-toolbar title="Invoices" :count="$invoices->total()">
                <x-slot:right>
                    <x-select name="status" :options="collect($statuses)->mapWithKeys(fn ($status) => [$status->value => $status->label()])" :selected="request('status')" placeholder="All statuses" onchange="this.form.submit()" />
                </x-slot:right>
            </x-toolbar>
        </form>
        <x-table :columns="[['label' => 'Customer'], ['label' => 'Total', 'align' => 'right'], ['label' => 'Status'], ['label' => 'Due']]">
            @foreach ($invoices as $invoice)
                <tr class="border-b border-line-soft">
                    <td class="px-4 py-3 text-control"><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->customer->name }}</a></td>
                    <td class="px-4 py-3 text-right text-control tabular-nums">${{ number_format($invoice->total, 2) }}</td>
                    <td class="px-4 py-3"><x-status :state="$invoice->status->value" /></td>
                    <td class="px-4 py-3 text-control text-ink/70">{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</td>
                </tr>
            @endforeach
            <x-slot:empty>
                <x-empty title="No invoices yet" body="Turn an accepted quote into an invoice, or create one from scratch.">
                    @if (auth()->user()->canManageCoreTool('invoices'))
                        <x-slot:action>
                            <x-button variant="secondary" href="{{ route('invoices.create') }}">New invoice</x-button>
                        </x-slot:action>
                    @endif
                </x-empty>
            </x-slot:empty>
        </x-table>
        {{ $invoices->links() }}
    </div>
@endsection
