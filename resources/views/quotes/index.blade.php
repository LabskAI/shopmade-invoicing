@extends('layouts.app')

@section('title', 'Quotes')
@section('breadcrumb', 'Quotes')

@section('content')
    <x-page-header title="Quotes" kicker="Work">
        <x-slot:actions>
            <x-button href="{{ route('quotes.create') }}"><i data-lucide="plus" class="size-4"></i>New quote</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="border-2 border-line bg-surface">
        <form method="GET">
            <x-toolbar title="Quotes" :count="$quotes->total()">
                <x-slot:right>
                    <x-select name="status" :options="collect($statuses)->mapWithKeys(fn ($status) => [$status->value => $status->label()])" :selected="request('status')" placeholder="All statuses" onchange="this.form.submit()" />
                </x-slot:right>
            </x-toolbar>
        </form>
        <x-table :columns="[['label' => 'Customer'], ['label' => 'Total', 'align' => 'right'], ['label' => 'Status'], ['label' => 'Created']]">
            @foreach ($quotes as $quote)
                <tr class="border-b border-line-soft">
                    <td class="px-4 py-3 text-control"><a href="{{ route('quotes.show', $quote) }}">{{ $quote->customer->name }}</a></td>
                    <td class="px-4 py-3 text-right text-control tabular-nums">${{ number_format($quote->total, 2) }}</td>
                    <td class="px-4 py-3"><x-status :state="$quote->status->value" /></td>
                    <td class="px-4 py-3 text-control text-ink/70">{{ $quote->created_at->format('M j, Y') }}</td>
                </tr>
            @endforeach
            <x-slot:empty>
                <x-empty title="No quotes yet" body="Create a quote to send an estimate.">
                    <x-slot:action>
                        <x-button variant="secondary" href="{{ route('quotes.create') }}">New quote</x-button>
                    </x-slot:action>
                </x-empty>
            </x-slot:empty>
        </x-table>
        {{ $quotes->links() }}
    </div>
@endsection
