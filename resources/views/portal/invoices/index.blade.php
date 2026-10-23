@extends('layouts.portal')

@section('title', 'Invoices')

@section('content')
    <x-page-header title="Invoices" kicker="Client portal" />
    <div class="border-2 border-line bg-surface">
        <x-table :columns="[['label' => 'Invoice'], ['label' => 'Site'], ['label' => 'Issued'], ['label' => 'Due'], ['label' => 'Status'], ['label' => 'Total', 'align' => 'right']]">
            @foreach ($invoices as $invoice)
                <tr class="border-b border-line-soft">
                    <td class="px-4 py-3 text-control">
                        <a href="{{ route('portal.invoices.show', $invoice) }}">#{{ $invoice->id }}</a>
                    </td>
                    <td class="px-4 py-3 text-control text-ink/70">{{ $invoice->customer?->name }}</td>
                    <td class="px-4 py-3 text-control text-ink/70">{{ $invoice->issued_at?->format('M j, Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-control text-ink/70">{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</td>
                    <td class="px-4 py-3"><x-status :state="$invoice->status->value" /></td>
                    <td class="px-4 py-3 text-right text-control tabular-nums">${{ number_format((float) $invoice->total, 2) }}</td>
                </tr>
            @endforeach
            <x-slot:empty>
                <x-empty title="No invoices" body="Invoices for your sites will show up here." />
            </x-slot:empty>
        </x-table>
        {{ $invoices->links() }}
    </div>
@endsection
