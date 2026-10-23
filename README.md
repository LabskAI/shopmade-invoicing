# shopmade-invoicing

Quotes, invoices and invoice PDFs for [Shopmade core](https://github.com/LabskAI/shopmade-core). MIT licensed.

## What it adds

- **Staff:** Quotes and Invoices in the sidebar (Work group). Line-item quotes and invoices, turning an accepted quote into an invoice once, and PDF download. Writes follow each tool's `manage_roles` (invoices: admins).
- **Dashboard:** open quotes and unpaid invoices.
- **Customer page:** the customer's invoices and a New invoice button.
- **Client portal:** an Invoices tab, the PDF, and a "paid / will pay" mark the client can leave (staff still set the status).
- **MCP:** `invoices_list` (read-only), filterable by status or unpaid.

With [shopmade-jobs](https://github.com/LabskAI/shopmade-jobs) installed, quotes and invoices can link to a job and the job page gets New quote / New invoice buttons. Without it the job field and links stay hidden.

## Install

```bash
composer require labskai/shopmade-invoicing
php artisan migrate
npm run build
```

PDFs use `barryvdh/laravel-dompdf`; the header shows `APP_ORG_NAME`.

## Tests

```bash
cd ../shopmade-core
composer config repositories.invoicing '{"type":"path","url":"../shopmade-invoicing"}'
composer require "labskai/shopmade-invoicing:*@dev"
vendor/bin/pest vendor/labskai/shopmade-invoicing/tests
```
