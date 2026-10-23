@extends('layouts.app')

@section('title', 'Quote')
@section('breadcrumb', 'Quotes / '.$quote->customer->name)

@section('content')
    <x-page-header title="Quote for {{ $quote->customer->name }}" kicker="Work">
        <x-slot:actions>
            <x-status :state="$quote->status->value" />
            @if (auth()->user()->canManageCoreTool('invoices'))
                @if ($quote->invoice)
                    <x-button variant="secondary" href="{{ route('invoices.show', $quote->invoice) }}">View invoice</x-button>
                @elseif ($quote->status->value !== 'draft')
                    <form method="POST" action="{{ route('quotes.invoice.store', $quote) }}">
                        @csrf
                        <x-button type="submit">Create invoice</x-button>
                    </form>
                @endif
            @endif
            <x-button href="{{ route('quotes.edit', $quote) }}">Edit</x-button>
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
                    @foreach ($quote->items as $item)
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
                        <td class="px-4 pt-4 text-right text-card-title tabular-nums">${{ number_format($quote->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </x-card>
        <div class="space-y-4">
            <x-card title="Customer">
                <p class="text-body"><a href="{{ route('customers.show', $quote->customer) }}">{{ $quote->customer->name }}</a></p>
                <p class="mt-2 text-label text-ink/70">{{ $quote->created_at->format('M j, Y') }}</p>
            </x-card>
            @if (\Shopmade\Invoicing\Support\JobLink::installed())
                <x-card title="Linked job">
                    @if ($quote->job)
                        <a class="text-body" href="{{ route('jobs.show', $quote->job) }}">{{ $quote->job->customer->name }} · {{ $quote->job->scheduled_at?->format('M j') ?? 'Unscheduled' }}</a>
                    @else
                        <p class="text-body text-ink/55">No job linked.</p>
                    @endif
                </x-card>
            @endif
        </div>
    </div>
@endsection
