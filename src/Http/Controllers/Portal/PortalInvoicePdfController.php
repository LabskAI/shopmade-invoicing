<?php

namespace Shopmade\Invoicing\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Shopmade\Invoicing\Models\Invoice;
use Shopmade\Invoicing\Support\JobLink;
use Symfony\Component\HttpFoundation\Response;

class PortalInvoicePdfController extends Controller
{
    public function __invoke(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load(JobLink::with(['customer', 'items']));

        return Pdf::loadView('invoicing::invoices.pdf', [
            'invoice' => $invoice,
            'orgName' => config('app.org_name'),
        ])->setPaper('letter')->download('invoice-'.$invoice->id.'.pdf');
    }
}
