<?php

namespace Modules\Webhooks\Providers;

use App\Events\NewCompanyCreatedEvent;
use App\Models\ClientDetails;
use App\Models\EmployeeDetails;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Task;
use App\Models\Contract;
use App\Models\ProjectTimeLog;
use App\Models\Event;
use App\Models\Deal;
use App\Models\Estimate;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CreditNotes;
use App\Models\Expense;
use App\Models\BankAccount;
use App\Models\Leave;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Appreciation;
use App\Models\Ticket;
use Modules\Purchase\Entities\PurchaseOrder;
use Modules\Purchase\Entities\PurchaseBill;
use Modules\Pricing\Entities\PricingTier;
use Modules\Payroll\Entities\SalarySlip;
use Modules\Performance\Entities\Objective;
use Modules\Recruit\Entities\RecruitJob;
use Modules\Recruit\Entities\RecruitJobApplication;
use Modules\Letter\Entities\Letter;
use Modules\Zoom\Entities\ZoomMeeting;
use Modules\Biometric\Entities\BiometricAttendance;
use Modules\Asset\Entities\Asset;
use Modules\Affiliate\Entities\Referral;
use Modules\Onboarding\Entities\OnboardingTask;
use Modules\Purchase\Entities\PurchaseVendor;
use Modules\Purchase\Entities\PurchaseInventory;
use Modules\Purchase\Entities\PurchasePaymentBill;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Biometric\Entities\BiometricDevice;
use Modules\Affiliate\Entities\Affiliate;
use Modules\Purchase\Entities\PurchaseVendorPayment;
use Modules\Purchase\Entities\PurchaseVendorCredit;
use Modules\Purchase\Entities\PurchaseVendorContact;
use Modules\Pricing\Entities\VolumeDiscountRule;
use Modules\Pricing\Entities\ClientProductPricing;
use Modules\Recruit\Entities\RecruitInterviewSchedule;
use Modules\Payroll\Entities\PayrollCycle;
use Modules\Payroll\Entities\EmployeeMonthlySalary;
use Modules\Performance\Entities\KeyResults;
use Modules\Performance\Entities\Meeting;
use App\Models\EmployeeShift;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Webhooks\Listeners\CompanyCreatedListener;
use Modules\Webhooks\Observers\ClientDetailsObserver;
use Modules\Webhooks\Observers\EmployeeDetailsObserver;
use Modules\Webhooks\Observers\InvoiceObserver;
use Modules\Webhooks\Observers\LeadObserver;
use Modules\Webhooks\Observers\ProductObserver;
use Modules\Webhooks\Observers\ProjectObserver;
use Modules\Webhooks\Observers\ProposalObserver;
use Modules\Webhooks\Observers\TaskObserver;
use Modules\Webhooks\Observers\GenericObserver;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
    ];

    protected $observers = [
        ClientDetails::class => [ClientDetailsObserver::class],
        EmployeeDetails::class => [EmployeeDetailsObserver::class],
        Invoice::class => [InvoiceObserver::class],
        Lead::class => [LeadObserver::class],
        Product::class => [ProductObserver::class],
        Project::class => [ProjectObserver::class],
        Proposal::class => [ProposalObserver::class],
        Task::class => [TaskObserver::class],
        Contract::class => [GenericObserver::class],
        ProjectTimeLog::class => [GenericObserver::class],
        Event::class => [GenericObserver::class],
        Deal::class => [GenericObserver::class],
        Estimate::class => [GenericObserver::class],
        Order::class => [GenericObserver::class],
        Payment::class => [GenericObserver::class],
        CreditNotes::class => [GenericObserver::class],
        Expense::class => [GenericObserver::class],
        BankAccount::class => [GenericObserver::class],
        Leave::class => [GenericObserver::class],
        Attendance::class => [GenericObserver::class],
        Holiday::class => [GenericObserver::class],
        Appreciation::class => [GenericObserver::class],
        Ticket::class => [GenericObserver::class],
        PurchaseOrder::class => [GenericObserver::class],
        PurchaseBill::class => [GenericObserver::class],
        PricingTier::class => [GenericObserver::class],
        SalarySlip::class => [GenericObserver::class],
        Objective::class => [GenericObserver::class],
        RecruitJob::class => [GenericObserver::class],
        RecruitJobApplication::class => [GenericObserver::class],
        Letter::class => [GenericObserver::class],
        ZoomMeeting::class => [GenericObserver::class],
        BiometricAttendance::class => [GenericObserver::class],
        Asset::class => [GenericObserver::class],
        Referral::class => [GenericObserver::class],
        OnboardingTask::class => [GenericObserver::class],
        PurchaseVendor::class => [GenericObserver::class],
        PurchaseInventory::class => [GenericObserver::class],
        PurchasePaymentBill::class => [GenericObserver::class],
        Warehouse::class => [GenericObserver::class],
        BiometricDevice::class => [GenericObserver::class],
        Affiliate::class => [GenericObserver::class],
        PurchaseVendorPayment::class => [GenericObserver::class],
        PurchaseVendorCredit::class => [GenericObserver::class],
        PurchaseVendorContact::class => [GenericObserver::class],
        VolumeDiscountRule::class => [GenericObserver::class],
        ClientProductPricing::class => [GenericObserver::class],
        RecruitInterviewSchedule::class => [GenericObserver::class],
        PayrollCycle::class => [GenericObserver::class],
        EmployeeMonthlySalary::class => [GenericObserver::class],
        KeyResults::class => [GenericObserver::class],
        Meeting::class => [GenericObserver::class],
        EmployeeShift::class => [GenericObserver::class],
    ];
}
