<?php

namespace Shopmade\Invoicing\Mcp;

use App\Core\Mcp\McpContext;
use App\Core\Mcp\Tools\Tool;
use App\Core\Mcp\Tools\ToolResult;
use App\Enums\ConnectorScope;
use Shopmade\Invoicing\Enums\InvoiceStatus;
use Shopmade\Invoicing\Models\Invoice;

class InvoicesListTool extends Tool
{
    public function name(): string
    {
        return 'invoices_list';
    }

    public function title(): string
    {
        return 'List invoices';
    }

    public function description(): string
    {
        return 'Invoices, newest first, with customer, total, issue and due dates, status and what the client said about payment. Filter by status or unpaid.';
    }

    public function scope(): ConnectorScope
    {
        return ConnectorScope::Operator;
    }

    public function inputSchema(): array
    {
        return $this->objectSchema([
            'status' => ['type' => 'string', 'enum' => array_map(fn (InvoiceStatus $status): string => $status->value, InvoiceStatus::cases())],
            'unpaid' => ['type' => 'boolean', 'description' => 'Only invoices that are not paid or void.'],
            'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 25],
        ]);
    }

    public function handle(array $arguments, McpContext $context): ToolResult
    {
        $invoices = Invoice::query()
            ->with('customer')
            ->when($arguments['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($arguments['unpaid'] ?? false, fn ($query) => $query->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Void]))
            ->latest('id')
            ->limit($arguments['limit'] ?? 25)
            ->get();

        return new ToolResult([
            'invoices' => $invoices->map(fn (Invoice $invoice): array => [
                'id' => $invoice->id,
                'customer' => $invoice->customer->name,
                'total' => $invoice->total,
                'issued_at' => $invoice->issued_at?->toDateString(),
                'due_at' => $invoice->due_at?->toDateString(),
                'status' => $invoice->status->value,
                'client_payment_mark' => $invoice->client_payment_mark?->value,
                'url' => route('invoices.show', $invoice),
            ])->all(),
        ]);
    }
}
