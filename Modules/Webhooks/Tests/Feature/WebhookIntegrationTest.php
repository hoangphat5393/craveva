<?php

namespace Modules\Webhooks\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Modules\Webhooks\Jobs\SendWebhook;
use Modules\Webhooks\Entities\WebhooksSetting;
use App\Models\Contract;
use App\Models\Company;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\EmployeeLeaveQuota;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\Estimate;
use App\Models\Task;
use App\Models\Payment;
use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\LeadPipeline;
use App\Models\Currency;
use App\Models\ClientDetails;
use App\Models\EmployeeDetails;
use App\Models\Event;
use App\Models\Order;
use App\Models\CreditNotes;
use App\Models\Expense;
use Modules\Purchase\Entities\PurchaseVendor;
use Modules\Purchase\Entities\PurchaseVendorCategory;
use Modules\Purchase\Entities\PurchaseInventory;
use Modules\Purchase\Entities\PurchaseBill;
use Modules\Purchase\Entities\PurchasePaymentBill;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Biometric\Entities\BiometricDevice;
use Modules\Affiliate\Entities\Affiliate;
use Modules\Letter\Entities\Letter;
use Modules\Zoom\Entities\ZoomMeeting;
use Modules\Asset\Entities\Asset;
use Modules\Recruit\Entities\RecruitJob;
use Modules\Onboarding\Entities\OnboardingTask;
use Modules\Payroll\Entities\SalarySlip;
use Modules\Performance\Entities\Objective;

use App\Models\UserAuth;

class WebhookIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected $company;
    protected $user;
    protected $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = new Company();
        $this->company->company_name = 'Test Company';
        $this->company->company_email = 'test@example.com';
        $this->company->status = 'active';
        $this->company->date_format = 'Y-m-d'; // Fix for CreditNotes
        $this->company->save();

        $this->currency = Currency::first();
        if (!$this->currency) {
            $this->currency = Currency::create([
                'currency_name' => 'Dollars',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'usd_price' => 1,
                'is_cryptocurrency' => 'no',
                'exchange_rate' => 1,
            ]);
        }

        $userAuth = UserAuth::create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
            'name' => 'Test User',
        ]);

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'user_auth_id' => $userAuth->id,
            'email' => $userAuth->email,
        ]);

        // Create ClientDetails for the user to avoid InvoiceObserver error
        ClientDetails::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            // 'name' => 'Test Client', // Removed as it does not exist in client_details
            'email' => 'testclient@example.com',
            'company_name' => 'Test Client Company',
        ]);

        // Create InvoiceSetting to avoid InvoiceObserver error
        \App\Models\InvoiceSetting::create([
            'company_id' => $this->company->id,
            'invoice_prefix' => 'INV',
            'invoice_digit' => 3,
            'estimate_prefix' => 'EST',
            'estimate_digit' => 3,
            'credit_note_prefix' => 'CN',
            'credit_note_digit' => 3,
            'template' => 'invoice-1',
            'due_after' => 15,
            'invoice_terms' => 'Test Terms',
            'send_reminder' => 0,
            'hsn_sac_code_show' => 0,
            'tax_calculation_msg' => 0,
            'contract_prefix' => 'CON',
            'contract_digit' => 3,
            'contract_number_separator' => '#',
            'show_status' => 0,
            'authorised_signatory' => 0,
        ]);

        session(['company' => $this->company]);
        $this->actingAs($userAuth);
    }

    /** @test */
    public function it_triggers_webhook_for_project()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Project Webhook',
            'webhook_for' => 'Project',
            'url' => 'http://example.com/webhook-project',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $project = \App\Models\Project::factory()->create(['company_id' => $this->company->id]);

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Project';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_lead()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Lead Webhook',
            'webhook_for' => 'Lead',
            'url' => 'http://example.com/webhook-lead',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $lead = \App\Models\Lead::factory()->create(['company_id' => $this->company->id]);

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Lead';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_product()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Product Webhook',
            'webhook_for' => 'Product',
            'url' => 'http://example.com/webhook-product',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $product = \App\Models\Product::factory()->create(['company_id' => $this->company->id]);

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Product';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_contract()
    {
        Queue::fake();

        // 1. Setup Webhook Setting for Contract
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Contract Webhook',
            'webhook_for' => 'Contract',
            'url' => 'http://example.com/webhook-contract',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        // 2. Trigger Event (Create Contract)
        $contract = new Contract();
        $contract->company_id = $this->company->id;
        $contract->subject = 'Test Contract Webhook';
        $contract->original_amount = 1000;
        $contract->start_date = now();
        $contract->client_id = $this->user->id;
        $contract->save();

        // 3. Assert Job Dispatched
        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Contract';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_invoice()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Invoice Webhook',
            'webhook_for' => 'Invoice',
            'url' => 'http://example.com/webhook-invoice',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $invoice = new Invoice();
        $invoice->company_id = $this->company->id;
        $invoice->client_id = $this->user->id;
        $invoice->invoice_number = 'INV-001';
        $invoice->issue_date = now();
        $invoice->due_date = now()->addDays(7);
        $invoice->sub_total = 100;
        $invoice->total = 100;
        $invoice->currency_id = $this->currency->id;
        $invoice->status = 'unpaid';
        $invoice->send_status = 1;
        $invoice->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Invoice';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_proposal()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Proposal Webhook',
            'webhook_for' => 'Proposal',
            'url' => 'http://example.com/webhook-proposal',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $lead = \App\Models\Lead::factory()->create(['company_id' => $this->company->id]);

        $deal = new Deal();
        $deal->company_id = $this->company->id;
        $deal->name = 'Test Deal';
        $deal->lead_id = $lead->id;
        $deal->save();

        $proposal = new Proposal();
        $proposal->company_id = $this->company->id;
        $proposal->valid_till = now()->addDays(7);
        $proposal->sub_total = 100;
        $proposal->total = 100;
        $proposal->currency_id = $this->currency->id;
        $proposal->status = 'waiting';
        $proposal->deal_id = $deal->id;
        $proposal->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Proposal';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_estimate()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Estimate Webhook',
            'webhook_for' => 'Estimate',
            'url' => 'http://example.com/webhook-estimate',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $estimate = new Estimate();
        $estimate->company_id = $this->company->id;
        $estimate->client_id = $this->user->id;
        $estimate->valid_till = now()->addDays(7);
        $estimate->sub_total = 100;
        $estimate->total = 100;
        $estimate->currency_id = $this->currency->id;
        $estimate->status = 'waiting';
        $estimate->estimate_number = 'EST-001';
        $estimate->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Estimate';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_task()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Task Webhook',
            'webhook_for' => 'Task',
            'url' => 'http://example.com/webhook-task',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $project = \App\Models\Project::factory()->create(['company_id' => $this->company->id]);

        $task = new Task();
        $task->company_id = $this->company->id;
        $task->heading = 'Test Task';
        $task->project_id = $project->id;
        $task->status = 'incomplete';
        $task->priority = 'medium';
        $task->due_date = now()->addDays(3);
        $task->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Task';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_payment()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Payment Webhook',
            'webhook_for' => 'Payment',
            'url' => 'http://example.com/webhook-payment',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $invoice = new Invoice();
        $invoice->company_id = $this->company->id;
        $invoice->client_id = $this->user->id;
        $invoice->invoice_number = 'INV-002';
        $invoice->issue_date = now();
        $invoice->due_date = now()->addDays(7);
        $invoice->sub_total = 100;
        $invoice->total = 100;
        $invoice->currency_id = $this->currency->id;
        $invoice->status = 'unpaid';
        $invoice->send_status = 1;
        $invoice->save();

        $payment = new Payment();
        $payment->company_id = $this->company->id;
        $payment->invoice_id = $invoice->id;
        $payment->amount = 100;
        $payment->gateway = 'Offline';
        $payment->status = 'complete';
        $payment->paid_on = now();
        $payment->currency_id = $this->currency->id;
        $payment->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Payment';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_ticket()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Ticket Webhook',
            'webhook_for' => 'Ticket',
            'url' => 'http://example.com/webhook-ticket',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $ticket = new Ticket();
        $ticket->company_id = $this->company->id;
        $ticket->user_id = $this->user->id;
        $ticket->subject = 'Test Ticket';
        $ticket->status = 'open';
        $ticket->priority = 'high';
        $ticket->agent_id = $this->user->id;
        $ticket->channel_id = 1; // Assuming channel exists or not strictly required by observer
        $ticket->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Ticket';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_client()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Client Webhook',
            'webhook_for' => 'Client',
            'url' => 'http://example.com/webhook-client',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        // Create a new user for client
        $userAuth = UserAuth::create([
            'email' => 'newclient@example.com',
            'password' => bcrypt('password'),
            'name' => 'New Client',
        ]);

        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'user_auth_id' => $userAuth->id,
            'email' => $userAuth->email,
        ]);

        $clientDetails = new ClientDetails();
        $clientDetails->user_id = $user->id;
        $clientDetails->company_id = $this->company->id;
        // $clientDetails->name = 'New Client Name'; // Removed as it does not exist
        // $clientDetails->email = 'newclient@example.com'; // Removed as it does not exist
        $clientDetails->company_name = 'New Client Company';
        $clientDetails->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Client';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_employee()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Employee Webhook',
            'webhook_for' => 'Employee',
            'url' => 'http://example.com/webhook-employee',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        // Create a new user for employee
        $userAuth = UserAuth::create([
            'email' => 'newemployee@example.com',
            'password' => bcrypt('password'),
            'name' => 'New Employee',
        ]);

        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'user_auth_id' => $userAuth->id,
            'email' => $userAuth->email,
        ]);

        $employeeDetails = new EmployeeDetails();
        $employeeDetails->user_id = $user->id;
        $employeeDetails->employee_id = 'EMP-NEW-001';
        $employeeDetails->company_id = $this->company->id;
        $employeeDetails->joining_date = now();
        $employeeDetails->address = 'Test Address';
        $employeeDetails->hourly_rate = 10;
        $employeeDetails->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Employee';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_event()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Event Webhook',
            'webhook_for' => 'Event',
            'url' => 'http://example.com/webhook-event',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $event = new Event();
        $event->company_id = $this->company->id;
        $event->event_name = 'Test Event';
        $event->start_date_time = now();
        $event->end_date_time = now()->addHour();
        $event->where = 'Online';
        $event->description = 'Test Description';
        $event->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Event';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_order()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Order Webhook',
            'webhook_for' => 'Order',
            'url' => 'http://example.com/webhook-order',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $order = new Order();
        $order->company_id = $this->company->id;
        $order->client_id = $this->user->id;
        $order->order_date = now();
        $order->sub_total = 100;
        $order->total = 100;
        $order->due_amount = 100;
        $order->currency_id = $this->currency->id;
        $order->status = 'pending';
        $order->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Order';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_credit_note()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Credit Note Webhook',
            'webhook_for' => 'CreditNotes',
            'url' => 'http://example.com/webhook-credit-note',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $creditNote = new CreditNotes();
        $creditNote->company_id = $this->company->id;
        $creditNote->client_id = $this->user->id;
        $creditNote->cn_number = 'CN-001';
        $creditNote->issue_date = now();
        $creditNote->due_date = now()->addDays(7);
        $creditNote->sub_total = 100;
        $creditNote->total = 100;
        $creditNote->currency_id = $this->currency->id;
        $creditNote->status = 'open';
        $creditNote->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'CreditNotes';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_expense()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Expense Webhook',
            'webhook_for' => 'Expense',
            'url' => 'http://example.com/webhook-expense',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $expense = new Expense();
        $expense->company_id = $this->company->id;
        $expense->user_id = $this->user->id;
        $expense->item_name = 'Test Expense';
        $expense->purchase_date = now();
        $expense->price = 100;
        $expense->currency_id = $this->currency->id;
        $expense->status = 'approved';
        $expense->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Expense';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_leave()
    {
        Queue::fake();

        // 1. Setup Webhook Setting for Leave
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Leave Webhook',
            'webhook_for' => 'Leave',
            'url' => 'http://example.com/webhook-leave',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        // Create Leave Type if not exists
        $leaveType = LeaveType::first();
        if (!$leaveType) {
            $leaveType = new LeaveType();
            $leaveType->company_id = $this->company->id;
            $leaveType->type_name = 'Test Leave Type';
            $leaveType->color = '#000000';
            $leaveType->save();
        }

        // Create Employee Leave Quota
        EmployeeLeaveQuota::create([
            'user_id' => $this->user->id,
            'leave_type_id' => $leaveType->id,
            'no_of_leaves' => 10,
            'leaves_remaining' => 10,
            'leaves_used' => 0,
        ]);

        // 2. Trigger Event (Create Leave)
        $leave = new Leave();
        $leave->company_id = $this->company->id;
        $leave->user_id = $this->user->id;
        $leave->leave_type_id = $leaveType->id;
        $leave->leave_date = now();
        $leave->status = 'pending';
        $leave->duration = 'single_day';
        $leave->save();

        // 3. Assert Job Dispatched
        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            $webhookFor = $prop->getValue($job);

            return $webhookFor === 'Leave';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_purchase_vendor()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Purchase Vendor Webhook',
            'webhook_for' => 'PurchaseVendor',
            'url' => 'http://example.com/webhook-purchase-vendor',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $vendor = new PurchaseVendor();
        $vendor->company_id = $this->company->id;
        $vendor->primary_name = 'Test Vendor';
        $vendor->email = 'vendor@example.com';
        $vendor->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'PurchaseVendor';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_warehouse()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Warehouse Webhook',
            'webhook_for' => 'Warehouse',
            'url' => 'http://example.com/webhook-warehouse',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $warehouse = new Warehouse();
        $warehouse->company_id = $this->company->id;
        $warehouse->name = 'Test Warehouse';
        $warehouse->code = 'WH001';
        $warehouse->address = 'Test Address';
        $warehouse->status = 'active';
        $warehouse->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'Warehouse';
        });
    }

    /** @test */
    public function it_triggers_webhook_for_purchase_inventory()
    {
        Queue::fake();
        WebhooksSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Test Purchase Inventory Webhook',
            'webhook_for' => 'PurchaseInventory',
            'url' => 'http://example.com/webhook-purchase-inventory',
            'request_method' => 'POST',
            'status' => 'active',
        ]);

        $inventory = new PurchaseInventory();
        $inventory->company_id = $this->company->id;
        // Assuming minimal required fields, checking model
        // dates = ['date'], table = purchase_inventory_adjustment
        // No strict required fields in model except implicit db schema constraints
        // Let's assume 'type' or similar might be needed if it was there, but checking schema from previous reads:
        // Schema for purchase_inventory_adjustment not read fully, but let's try minimal
        $inventory->save();

        Queue::assertPushed(SendWebhook::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('webhookFor');
            $prop->setAccessible(true);
            return $prop->getValue($job) === 'PurchaseInventory';
        });
    }
}
