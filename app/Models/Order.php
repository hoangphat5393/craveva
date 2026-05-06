<?php

namespace App\Models;

use App\Helper\NumberFormat;
use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Modules\Purchase\Entities\SalesShipment;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int|null $client_id
 * @property string $order_date
 * @property float $sub_total
 * @property float $total
 * @property float $due_amount
 * @property string $status
 * @property int|null $currency_id
 * @property string $show_shipping_address
 * @property string|null $note
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $client
 * @property-read ClientDetails|null $clientdetails
 * @property-read Currency|null $currency
 * @property-read Collection|OrderItems[] $items
 * @property-read Collection|Invoice[] $invoice
 * @property-read int|null $items_count
 * @property-read Collection|Payment[] $payment
 * @property-read int|null $payment_count
 * @property-read Project $project
 * @property-read Collection|Invoice[] $recurrings
 * @property-read int|null $recurrings_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDueAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShowShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 *
 * @property mixed $order_number
 * @property float $discount
 * @property string $discount_type
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDiscountType($value)
 *
 * @property int|null $company_id
 * @property int|null $company_address_id
 * @property-read CompanyAddress|null $address
 * @property-read Company|null $company
 * @property int|null $unit_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCompanyAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderNumber($value)
 *
 * @property-read UnitType $unit
 * @property int|null $unit_id
 * @property string|null $custom_order_number
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCustomOrderNumber($value)
 *
 * @property string|null $original_order_number
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOriginalOrderNumber($value)
 *
 * @mixin \Eloquent
 */
class Order extends BaseModel
{
    use CustomFieldsTrait, HasCompany;

    const CUSTOM_FIELD_MODEL = 'App\Models\Order';

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function clientdetails(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client_id', 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    public function payment(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id')->orderByDesc('paid_on');
    }

    public function invoice(): HasOne
    {
        // Backward-compatible single access point: always return latest invoice of this order.
        return $this->hasOne(Invoice::class, 'order_id')->latestOfMany('id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'order_id')->orderByDesc('id');
    }

    public function salesShipments(): HasMany
    {
        return $this->hasMany(SalesShipment::class, 'order_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(CompanyAddress::class, 'company_address_id');
    }

    public static function lastOrderNumber()
    {
        return (int) Order::latest()->first()?->original_order_number ?? 0;
    }

    /*
    public function getOrderNumberAttribute()
    {
        return Str::upper(__('app.order')) . '#' .$this->attributes['order_number'];
    }
    */

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    public function formatOrderNumber()
    {
        $orderSettings = (company()) ? company()->invoiceSetting : $this->company->invoiceSetting;

        return NumberFormat::order($this->order_number, $orderSettings);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class, 'estimate_id');
    }
}
