@extends('layouts.app')

@section('title', 'New quote')
@section('breadcrumb', 'Quotes / New')

@section('content')
    <x-page-header title="New quote" kicker="Work" />
    <form method="POST" action="{{ route('quotes.store') }}" class="max-w-4xl">
        @csrf
        <x-card title="Details">
            @include('invoicing::quotes._form')
            <div class="mt-6 flex justify-start gap-2">
                <x-button type="submit">Save</x-button>
                <x-button variant="secondary" href="{{ route('quotes.index') }}">Cancel</x-button>
            </div>
        </x-card>
    </form>
@endsection
