<?php

namespace Shopmade\Invoicing;

use App\Core\Packages\Hooks;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Shopmade\Invoicing\Enums\InvoiceStatus;
use Shopmade\Invoicing\Enums\QuoteStatus;
use Shopmade\Invoicing\Mcp\InvoicesListTool;
use Shopmade\Invoicing\Models\Invoice;
use Shopmade\Invoicing\Models\Quote;
use Shopmade\Invoicing\Policies\InvoicePolicy;
use Shopmade\Invoicing\Support\JobLink;

/**
 * Quotes, invoices, invoice PDFs and the client portal invoices tab, plugged into core through Hooks.
 */
class InvoicingServiceProvider extends ServiceProvider
{
    public function boot(Hooks $hooks): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'invoicing');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        Gate::policy(Invoice::class, InvoicePolicy::class);

        Customer::resolveRelationUsing('quotes', fn (Customer $customer) => $customer->hasMany(Quote::class));
        Customer::resolveRelationUsing('invoices', fn (Customer $customer) => $customer->hasMany(Invoice::class));

        if (JobLink::installed()) {
            (JobLink::MODEL)::resolveRelationUsing('quotes', fn (Model $job) => $job->hasMany(Quote::class));
            (JobLink::MODEL)::resolveRelationUsing('invoices', fn (Model $job) => $job->hasMany(Invoice::class));
        }

        $hooks->tool([
            'slug' => 'quotes',
            'label' => 'Quotes',
            'group' => 'Work',
            'route' => 'quotes.index',
            'pattern' => 'quotes.*',
            'view_roles' => ['super_admin', 'admin', 'user'],
            'manage_roles' => ['super_admin', 'admin', 'user'],
        ]);

        $hooks->tool([
            'slug' => 'invoices',
            'label' => 'Invoices',
            'group' => 'Work',
            'route' => 'invoices.index',
            'pattern' => 'invoices.*',
            'view_roles' => ['super_admin', 'admin', 'user'],
            'manage_roles' => ['super_admin', 'admin'],
        ]);

        $hooks->status('sent', like: 'open', label: 'Sent');
        $hooks->status('accepted', like: 'approved', label: 'Accepted');
        $hooks->status('void', like: 'cancelled', label: 'Void');

        $hooks->dashboardCard('invoicing::hooks.dashboard-card', fn (User $user): array => [
            'openQuotes' => Quote::query()->where('status', '!=', QuoteStatus::Paid)->count(),
            'unpaidInvoices' => Invoice::query()->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Void])->count(),
        ]);
        $hooks->customerPanel('invoicing::hooks.customer-panel', fn (Customer $customer): array => [
            'customer' => $customer,
            'invoices' => $customer->invoices()->latest('id')->limit(10)->get(),
        ]);

        $hooks->portalTab('Invoices', 'portal.invoices.index', 'portal.invoices.*', icon: 'receipt');
        $hooks->portalSection('invoicing::hooks.portal-section', fn (User $user): array => [
            'openInvoices' => Invoice::query()
                ->whereIn('customer_id', $user->customers()->pluck('customers.id'))
                ->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Void])
                ->count(),
        ]);

        $hooks->mcpTool('invoices_list', InvoicesListTool::class);
    }
}
