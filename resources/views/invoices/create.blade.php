@extends('layouts.app')

@section('title', 'New invoice')
@section('breadcrumb', 'Invoices / New')

@section('content')
    <x-page-header title="New invoice" kicker="Work" />
    <form method="POST" action="{{ route('invoices.store') }}" class="max-w-4xl">
        @csrf
        <x-card title="Details">
            @include('invoicing::invoices._form')
            <div class="mt-6 flex justify-start gap-2">
                <x-button type="submit">Save</x-button>
                <x-button variant="secondary" href="{{ route('invoices.index') }}">Cancel</x-button>
            </div>
        </x-card>
    </form>
@endsection
