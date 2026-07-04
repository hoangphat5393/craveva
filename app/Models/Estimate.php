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

    public const STATUS_REVISION_REQUIRED = 'revision_required';

    protected $casts = [
        'valid_till' => 'datetime',
        'last_viewed' => 'datetime',
        'president_reviewed_at' => 'datetime',
        'vp_pricing_reviewed_at' => 'datetime',
        'recipe_moq' => 'integer',
    ];

    protected $appends = ['total_amount', 'valid_date'];

    protected $with = ['currency'];

    const CUSTOM_FIELD_MODEL = 'App\Models\Estimate';

    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class, 'estimate_id');
    }

    public function bomLines(): HasMany
    {
        return $this->hasMany(EstimateBomLine::class, 'estimate_id')->orderBy('sort_order');
    }

    public function approvalEvents(): HasMany
    {
        return $this->hasMany(EstimateApprovalEvent::class, 'estimate_id')->orderBy('created_at');
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

    public function presidentReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'president_reviewed_by')->withoutGlobalScope(ActiveScope::class);
    }

    public function vpPricingReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vp_pricing_reviewed_by')->withoutGlobalScope(ActiveScope::class);
    }

    public function getTotalAmountAttribute()
    {
        return (! is_null($this->total) && isset($this->currency) && ! is_null($this->currency->currency_symbol)) ? $this->currency->currency_symbol . $this->total : '';
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

    public function isCommercialConversionAllowed(): bool
    {
        if (! estimates_phase1_review_enabled()) {
            return true;
        }

        return $this->isReadyForCommercialConversion();
    }

    public function isRevisionRequired(): bool
    {
        return $this->status === self::STATUS_REVISION_REQUIRED;
    }

    public function resetInternalReviewForResubmission(): void
    {
        $this->president_review_status = self::INTERNAL_REVIEW_PENDING;
        $this->president_reviewed_by = null;
        $this->president_reviewed_at = null;
        $this->president_review_note = null;
        $this->vp_pricing_review_status = self::INTERNAL_REVIEW_PENDING;
        $this->vp_pricing_reviewed_by = null;
        $this->vp_pricing_reviewed_at = null;
        $this->vp_pricing_review_note = null;
    }

    /**
     * @return list<array{at: \Illuminate\Support\Carbon, label: string, by: string|null, note: string|null}>
     */
    public function approvalTimelineEntries(): array
    {
        if (estimates_phase1_review_enabled() && ($this->relationLoaded('approvalEvents') ? $this->approvalEvents->isNotEmpty() : $this->approvalEvents()->exists())) {
            $events = $this->relationLoaded('approvalEvents')
                ? $this->approvalEvents
                : $this->approvalEvents()->with('actor')->get();

            return $events
                ->map(static fn(EstimateApprovalEvent $event): array => $event->toTimelineEntry())
                ->values()
                ->all();
        }

        $entries = [];

        if ($this->president_reviewed_at !== null) {
            $presidentKey = 'modules.estimates.timelinePresident_' . (in_array((string) $this->president_review_status, ['approved', 'rejected'], true)
                ? $this->president_review_status
                : 'pending');
            $entries[] = [
                'at' => $this->president_reviewed_at,
                'label' => __($presidentKey),
                'by' => $this->presidentReviewer?->name,
                'note' => $this->president_review_note,
            ];
        }

        if ($this->vp_pricing_reviewed_at !== null) {
            $vpKey = 'modules.estimates.timelineVp_' . (in_array((string) $this->vp_pricing_review_status, ['approved', 'rejected'], true)
                ? $this->vp_pricing_review_status
                : 'pending');
            $entries[] = [
                'at' => $this->vp_pricing_reviewed_at,
                'label' => __($vpKey),
                'by' => $this->vpPricingReviewer?->name,
                'note' => $this->vp_pricing_review_note,
            ];
        }

        usort($entries, fn(array $a, array $b): int => $a['at']->getTimestamp() <=> $b['at']->getTimestamp());

        return $entries;
    }

    /**
     * @return array{label: string, badge_class: string}
     */
    public function workflowStagePresentation(): array
    {
        if (! estimates_phase1_review_enabled()) {
            return [
                'label' => __('modules.estimates.workflowStage_standard'),
                'badge_class' => 'badge-secondary',
            ];
        }

        if ($this->isRevisionRequired()) {
            return [
                'label' => __('modules.estimates.workflowStage_revision_required'),
                'badge_class' => 'badge-info',
            ];
        }

        if ($this->hasLegacyInternalReviewState()) {
            return [
                'label' => __('modules.estimates.workflowStage_legacy'),
                'badge_class' => 'badge-secondary',
            ];
        }

        if ($this->president_review_status === self::INTERNAL_REVIEW_REJECTED) {
            return [
                'label' => __('modules.estimates.workflowStage_president_rejected'),
                'badge_class' => 'badge-danger',
            ];
        }

        if ($this->president_review_status !== self::INTERNAL_REVIEW_APPROVED) {
            return [
                'label' => __('modules.estimates.workflowStage_pending_president'),
                'badge_class' => 'badge-warning',
            ];
        }

        if ($this->vp_pricing_review_status === self::INTERNAL_REVIEW_REJECTED) {
            return [
                'label' => __('modules.estimates.workflowStage_vp_rejected'),
                'badge_class' => 'badge-danger',
            ];
        }

        if ($this->vp_pricing_review_status !== self::INTERNAL_REVIEW_APPROVED) {
            return [
                'label' => __('modules.estimates.workflowStage_pending_vp'),
                'badge_class' => 'badge-warning',
            ];
        }

        return [
            'label' => __('modules.estimates.workflowStage_ready_for_so'),
            'badge_class' => 'badge-success',
        ];
    }
}
