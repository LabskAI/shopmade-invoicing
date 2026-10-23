@extends('layouts.app')

@section('title', 'Invoice')
@section('breadcrumb', 'Invoices / '.$invoice->customer->name)

@section('content')
    <x-page-header title="Invoice for {{ $invoice->customer->name }}" kicker="Work">
        <x-slot:actions>
            <x-status :state="$invoice->status->value" />
            <x-button variant="secondary" href="{{ route('invoices.pdf', $invoice) }}">Download PDF</x-button>
            @if (auth()->user()->canManageCoreTool('invoices'))
                <x-button href="{{ route('invoices.edit', $invoice) }}">Edit</x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 lg:grid-cols-3">
        <x-card class="lg:col-span-2" title="Line items">
            <table class="min-w-full text-left">
                <thead>
                    <tr class="border-b-2 border-line">
                        <th class="px-4 py-3 text-micro uppercase text-ink/70">Description</th>
                        <th class="px-4 py-3 text-micro uppercase text-ink/70">Qty</th>
                        <th class="px-4 py-3 text-micro uppercase text-ink/70">Price</th>
                        <th class="px-4 py-3 text-right text-micro uppercase text-ink/70">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr class="border-b border-line-soft">
                            <td class="px-4 py-3 text-control">{{ $item->description }}</td>
                            <td class="px-4 py-3 text-control tabular-nums">{{ $item->qty }}</td>
                            <td class="px-4 py-3 text-control tabular-nums">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-4 py-3 text-right text-control tabular-nums">${{ number_format($item->lineTotal(), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="px-4 pt-4 text-right text-control">Total</td>
                        <td class="px-4 pt-4 text-right text-card-title tabular-nums">${{ number_format($invoice->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </x-card>
        <div class="space-y-4">
            <x-card title="Customer">
                <p class="text-body"><a href="{{ route('customers.show', $invoice->customer) }}">{{ $invoice->customer->name }}</a></p>
                <dl class="mt-3 space-y-2 text-body">
                    <div>
                        <dt class="text-label text-ink/70">Issued</dt>
                        <dd>{{ $invoice->issued_at?->format('M j, Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-label text-ink/70">Due</dt>
                        <dd>{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</dd>
                    </div>
                    @if ($invoice->client_payment_mark)
                        <div>
                            <dt class="text-label text-ink/70">Client says</dt>
                            <dd>{{ $invoice->client_payment_mark->label() }}</dd>
                        </div>
                    @endif
                </dl>
                @if ($invoice->notes)
                    <p class="mt-3 text-body text-ink/70">{{ $invoice->notes }}</p>
                @endif
            </x-card>
            @if (\Shopmade\Invoicing\Support\JobLink::installed())
                <x-card title="Linked job">
                    @if ($invoice->job)
                        <a class="text-body" href="{{ route('jobs.show', $invoice->job) }}">{{ $invoice->job->customer->name }} · {{ $invoice->job->scheduled_at?->format('M j') ?? 'Unscheduled' }}</a>
                    @else
                        <p class="text-body text-ink/55">No job linked.</p>
                    @endif
                </x-card>
            @endif
            <x-card title="Quote">
                @if ($invoice->quote)
                    <a class="text-body" href="{{ route('quotes.show', $invoice->quote) }}">Quote #{{ $invoice->quote->id }}</a>
                @else
                    <p class="text-body text-ink/55">Created without a quote.</p>
                @endif
            </x-card>
        </div>
    </div>
@endsection
