<?php

namespace App\Models;

use App\Helper\NumberFormat;
use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\Estimate
 *
 * @property int $id
 * @property int $client_id
 * @property string|null $estimate_number
 * @property \Illuminate\Support\Carbon $valid_till
 * @property float $sub_total
 * @property float $discount
 * @property string $discount_type
 * @property float $total
 * @property int|null $currency_id
 * @property string $status
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $send_status
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read User $client
 * @property-read Currency|null $currency
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $total_amount
 * @property-read mixed $valid_date
 * @property-read Collection|EstimateItem[] $items
 * @property-read int|null $items_count
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read AcceptEstimate|null $sign
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate query()
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereEstimateNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereSendStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereValidTill($value)
 *
 * @property string|null $hash
 * @property int|null $unit_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereHash($value)
 *
 * @property string $calculate_tax
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCalculateTax($value)
 *
 * @property string|null $description
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereDescription($value)
 *
 * @property int|null $company_id
 * @property-read ClientDetails $clientdetails
 * @property-read Company|null $company
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCompanyId($value)
 *
 * @property \Illuminate\Support\Carbon|null $last_viewed
 * @property string|null $ip_address
 * @property-read UnitType|null $unit
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereLastViewed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereUnitId($value)
 *
 * @property string|null $original_estimate_number
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereOriginalEstimateNumber($value)
 *
 * @mixin \Eloquent
 */
class Estimate extends BaseModel
{
    use CustomFieldsTrait, HasCompany, Notifiable;

    public const INTERNAL_REVIEW_PENDING = 'pending';

    public const INTERNAL_REVIEW_APPROVED = 'approved';

    public const INTERNAL_REVIEW_REJECTED = 'rejected';

    protected $casts = [
        'valid_till' => 'datetime',
        'last_viewed' => 'datetime',
        'quotation_date' => 'date',
        'document_date' => 'date',
        'exchange_rate' => 'decimal:6',
        'header_quotation_amount' => 'decimal:2',
        'header_tax_amount' => 'decimal:2',
        'header_total_quantity' => 'decimal:4',
        'total_gross_weight_kg' => 'decimal:4',
        'total_volume' => 'decimal:4',
    ];

    protected $appends = ['total_amount', 'valid_date'];

    protected $with = ['currency'];

    const CUSTOM_FIELD_MODEL = 'App\Models\Estimate';

    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class, 'estimate_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function clientdetails(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client_id', 'user_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    public function sign(): HasOne
    {
        return $this->hasOne(AcceptEstimate::class, 'estimate_id');
    }

    public function getTotalAmountAttribute()
    {
        return (! is_null($this->total) && isset($this->currency) && ! is_null($this->currency->currency_symbol)) ? $this->currency->currency_symbol.$this->total : '';
    }

    public function getValidDateAttribute()
    {
        return ! is_null($this->valid_till) ? Carbon::parse($this->valid_till)->format('d F, Y') : '';
    }

    public function formatEstimateNumber()
    {
        $invoiceSettings = (company()) ? company()->invoiceSetting : $this->company->invoiceSetting;

        return NumberFormat::estimate($this->estimate_number, $invoiceSettings);
    }

    public static function lastEstimateNumber()
    {
        return (int) Estimate::orderBy('id', 'desc')->first()?->original_estimate_number ?? 0;
    }

    public function estimateRequest(): BelongsTo
    {
        return $this->belongsTo(EstimateRequest::class, 'estimate_request_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'estimate_id');
    }

    public function hasLegacyInternalReviewState(): bool
    {
        return $this->president_review_status === null && $this->vp_pricing_review_status === null;
    }

    public function hasPresidentApproved(): bool
    {
        if ($this->hasLegacyInternalReviewState()) {
            return true;
        }

        return $this->president_review_status === self::INTERNAL_REVIEW_APPROVED;
    }

    public function hasVpPricingApproved(): bool
    {
        if ($this->hasLegacyInternalReviewState()) {
            return true;
        }

        return $this->vp_pricing_review_status === self::INTERNAL_REVIEW_APPROVED;
    }

    public function isReadyForCommercialConversion(): bool
    {
        return $this->hasPresidentApproved() && $this->hasVpPricingApproved();
    }
}
