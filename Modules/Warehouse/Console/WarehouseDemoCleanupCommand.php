<?php

namespace Modules\Warehouse\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Purchase\Support\SalesDoRuntime;

class WarehouseDemoCleanupCommand extends Command
{
    protected $signature = 'warehouse:demo-cleanup
        {--apply : Apply deletion (default is dry-run)}
        {--company_id=1 : Company scope}
        {--order_no=ODR#004 : Sales order number to cleanup}
        {--do_no=SS-000008 : Sales DO number to cleanup}
        {--invoice_no=INV#028 : Invoice number to cleanup}
        {--batch_no=DEMO-ODR004-B1 : Demo batch number to cleanup}';

    protected $description = 'Cleanup demo SO/DO/Invoice/Batch data with dry-run safety';

    public function handle(): int
    {
        $companyId = (int) $this->option('company_id');
        $orderNo = (string) $this->option('order_no');
        $doNo = (string) $this->option('do_no');
        $invoiceNo = (string) $this->option('invoice_no');
        $batchNo = (string) $this->option('batch_no');
        $apply = (bool) $this->option('apply');

        $doHeaderTable = SalesDoRuntime::headerTable();
        $doItemTable = SalesDoRuntime::itemTable();
        $doNumberColumn = SalesDoRuntime::numberColumn();

        $order = DB::table('orders')
            ->where('company_id', $companyId)
            ->where('order_number', $orderNo)
            ->first();

        $salesDo = DB::table($doHeaderTable)
            ->where('company_id', $companyId)
            ->where($doNumberColumn, $doNo)
            ->first();

        $invoice = DB::table('invoices')
            ->where('company_id', $companyId)
            ->where('invoice_number', $invoiceNo)
            ->first();

        $batchRows = DB::table('warehouse_product_batches')
            ->where('company_id', $companyId)
            ->where('batch_number', $batchNo)
            ->get();

        $this->line('Dry-run summary:');
        $this->line('- Order: '.($order ? '#'.$order->id.' '.$order->order_number : 'not found'));
        $this->line('- Sales DO: '.($salesDo ? '#'.$salesDo->id.' '.$salesDo->{$doNumberColumn} : 'not found'));
        $this->line('- Invoice: '.($invoice ? '#'.$invoice->id.' '.$invoice->invoice_number : 'not found'));
        $this->line('- Demo batch rows: '.$batchRows->count());

        if ($salesDo) {
            $doItemCount = DB::table($doItemTable)->where(SalesDoRuntime::itemForeignKey(), $salesDo->id)->count();
            $this->line('- Sales DO items: '.$doItemCount);
        }

        if ($invoice) {
            $invoiceItemCount = DB::table('invoice_items')->where('invoice_id', $invoice->id)->count();
            $paymentCount = DB::table('payments')->where('invoice_id', $invoice->id)->count();
            $this->line('- Invoice items: '.$invoiceItemCount);
            $this->line('- Invoice payments: '.$paymentCount);
        }

        if (! $apply) {
            $this->warn('Dry-run only. Re-run with --apply to execute cleanup.');

            return self::SUCCESS;
        }

        DB::transaction(function () use (
            $order,
            $salesDo,
            $invoice,
            $batchRows,
            $doHeaderTable,
            $doItemTable,
            $companyId,
            $batchNo
        ): void {
            if ($invoice) {
                DB::table('invoice_item_images')->whereIn('invoice_item_id', function ($q) use ($invoice) {
                    $q->select('id')->from('invoice_items')->where('invoice_id', $invoice->id);
                })->delete();
                DB::table('invoice_items')->where('invoice_id', $invoice->id)->delete();
                DB::table('payments')->where('invoice_id', $invoice->id)->delete();
                DB::table('credit_notes')->where('invoice_id', $invoice->id)->update(['invoice_id' => null]);
                DB::table('invoices')->where('id', $invoice->id)->delete();
            }

            if ($salesDo) {
                DB::table($doItemTable)->where(SalesDoRuntime::itemForeignKey(), $salesDo->id)->delete();
                DB::table($doHeaderTable)->where('id', $salesDo->id)->delete();
            }

            if ($order) {
                DB::table('order_items')->where('order_id', $order->id)->delete();
                DB::table('orders')->where('id', $order->id)->delete();
            }

            if ($batchRows->isNotEmpty()) {
                DB::table('warehouse_product_batches')
                    ->where('company_id', $companyId)
                    ->where('batch_number', $batchNo)
                    ->delete();
            }
        });

        $this->info('Demo cleanup completed successfully.');

        return self::SUCCESS;
    }
}
