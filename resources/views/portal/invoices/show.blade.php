@extends('layouts.portal')

@section('title', 'Invoice')

@section('content')
    <x-page-header title="Invoice #{{ $invoice->id }}" kicker="Client portal">
        <x-slot:actions>
            <x-status :state="$invoice->status->value" />
            <x-button variant="secondary" href="{{ route('portal.invoices.pdf', $invoice) }}"><i data-lucide="download" class="size-4"></i>Download PDF</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 lg:grid-cols-3">
        <x-card class="lg:col-span-2" title="Line items">
            <div class="overflow-x-auto">
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
                        @forelse ($invoice->items as $item)
                            <tr class="border-b border-line-soft">
                                <td class="px-4 py-3 text-control">{{ $item->description }}</td>
                                <td class="px-4 py-3 text-control tabular-nums">{{ $item->qty }}</td>
                                <td class="px-4 py-3 text-control tabular-nums">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-right text-control tabular-nums">${{ number_format($item->lineTotal(), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-control text-ink/55">No line items.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="px-4 pt-4 text-right text-control">Total</td>
                            <td class="px-4 pt-4 text-right text-card-title tabular-nums">${{ number_format((float) $invoice->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-card>

        <div class="space-y-4">
            <x-card title="Details">
                <dl class="space-y-2 text-body">
                    <div>
                        <dt class="text-label text-ink/70">Site</dt>
                        <dd>{{ $invoice->customer?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-label text-ink/70">Issued</dt>
                        <dd>{{ $invoice->issued_at?->format('M j, Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-label text-ink/70">Due</dt>
                        <dd>{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</dd>
                    </div>
                    @if (\Shopmade\Invoicing\Support\JobLink::installed())
                        <div>
                            <dt class="text-label text-ink/70">Visit</dt>
                            <dd>
                                @if ($invoice->job)
                                    <a href="{{ route('portal.jobs.show', $invoice->job) }}">{{ $invoice->job->windowLabel() }}</a>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            <x-card title="Payment">
                <p class="mb-3 text-control text-ink/70">Let us know about payment. Your note is shared with the office; we confirm on our side.</p>
                @if ($invoice->client_payment_mark)
                    <p class="mb-3 text-control">Your note: <span class="font-semibold">{{ $invoice->client_payment_mark->label() }}</span></p>
                @endif
                <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('portal.invoices.mark', $invoice) }}">
                        @csrf
                        <input type="hidden" name="mark" value="paid">
                        <x-button type="submit"><i data-lucide="check" class="size-4"></i>I've paid</x-button>
                    </form>
                    <form method="POST" action="{{ route('portal.invoices.mark', $invoice) }}">
                        @csrf
                        <input type="hidden" name="mark" value="will_pay">
                        <x-button type="submit" variant="secondary">I'll pay later</x-button>
                    </form>
                </div>
            </x-card>
        </div>
    </div>
@endsection
