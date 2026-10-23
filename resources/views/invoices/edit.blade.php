@extends('layouts.app')

@section('title', 'Edit invoice')
@section('breadcrumb', 'Invoices / Edit')

@section('content')
    <x-page-header title="Edit invoice" kicker="Work" />
    <form method="POST" action="{{ route('invoices.update', $invoice) }}" class="max-w-4xl">
        @csrf
        @method('PUT')
        <x-card title="Details">
            @include('invoicing::invoices._form')
            <div class="mt-6 flex justify-start gap-2">
                <x-button type="submit">Save</x-button>
                <x-button variant="secondary" href="{{ route('invoices.show', $invoice) }}">Cancel</x-button>
            </div>
        </x-card>
    </form>
@endsection
