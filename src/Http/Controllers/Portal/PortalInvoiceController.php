<?php

namespace Shopmade\Invoicing\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Shopmade\Invoicing\Enums\ClientPaymentMark;
use Shopmade\Invoicing\Enums\InvoiceStatus;
use Shopmade\Invoicing\Models\Invoice;
use Shopmade\Invoicing\Support\JobLink;

class PortalInvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $customerIds = $request->user()->customers()->pluck('customers.id');

        $invoices = Invoice::query()
            ->with('customer')
            ->whereIn('customer_id', $customerIds)
            ->where('status', '!=', InvoiceStatus::Draft)
            ->latest('id')
            ->paginate(20);

        return view('invoicing::portal.invoices.index', compact('invoices'));
    }

    public function show(Request $request, Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(JobLink::with(['customer', 'items']));

        return view('invoicing::portal.invoices.show', compact('invoice'));
    }

    /**
     * Records what the client says about payment. Staff confirm; status never changes here.
     */
    public function mark(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('mark', $invoice);

        $data = $request->validate([
            'mark' => ['required', Rule::enum(ClientPaymentMark::class)],
        ]);

        $invoice->update(['client_payment_mark' => ClientPaymentMark::from($data['mark'])]);

        return redirect()->route('portal.invoices.show', $invoice)->with('status', 'Thanks, we have noted that.');
    }
}
