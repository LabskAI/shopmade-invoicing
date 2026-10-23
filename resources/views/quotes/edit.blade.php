@extends('layouts.app')

@section('title', 'Edit quote')
@section('breadcrumb', 'Quotes / Edit')

@section('content')
    <x-page-header title="Edit quote" kicker="Work" />
    <form method="POST" action="{{ route('quotes.update', $quote) }}" class="max-w-4xl">
        @csrf
        @method('PUT')
        <x-card title="Details">
            @include('invoicing::quotes._form')
            <div class="mt-6 flex justify-start gap-2">
                <x-button type="submit">Save</x-button>
                <x-button variant="secondary" href="{{ route('quotes.show', $quote) }}">Cancel</x-button>
            </div>
        </x-card>
    </form>
@endsection
