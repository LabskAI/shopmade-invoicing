<?php

use Illuminate\Support\Facades\Route;
use Shopmade\Invoicing\Http\Controllers\InvoiceController;
use Shopmade\Invoicing\Http\Controllers\Portal\PortalInvoiceController;
use Shopmade\Invoicing\Http\Controllers\Portal\PortalInvoicePdfController;
use Shopmade\Invoicing\Http\Controllers\QuoteController;

Route::middleware(['web', 'auth', 'can:staff.access'])->group(function () {
    Route::get('quotes', [QuoteController::class, 'index'])->name('quotes.index');

    Route::middleware('can-manage-core:quotes')->group(function () {
        Route::get('quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
        Route::post('quotes', [QuoteController::class, 'store'])->name('quotes.store');
        Route::get('quotes/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit');
        Route::put('quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
        Route::delete('quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');
    });

    Route::get('quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');

    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');

    Route::middleware('can-manage-core:invoices')->group(function () {
        Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::post('quotes/{quote}/invoice', [InvoiceController::class, 'storeFromQuote'])->name('quotes.invoice.store');
    });

    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
});

Route::middleware(['web', 'auth', 'can:portal.access'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('invoices', [PortalInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [PortalInvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/pdf', PortalInvoicePdfController::class)->name('invoices.pdf');
    Route::post('invoices/{invoice}/mark', [PortalInvoiceController::class, 'mark'])->middleware('throttle:10,1')->name('invoices.mark');
});
