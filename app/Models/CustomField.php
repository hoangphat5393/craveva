<?php

namespace App\Models;

use App\Traits\HasCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\CustomField
 *
 * @property int $id
 * @property int|null $custom_field_group_id
 * @property string $label
 * @property string $name
 * @property bool $export
 * @property string $type
 * @property string $required
 * @property string|null $values
 * @property string|null $visible
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereCustomFieldGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereValues($value)
 *
 * @property int|null $company_id
 * @property-read Company|null $company
 * @property-read LeadCustomForm|null $leadCustomForm
 * @property-read TicketCustomForm|null $ticketCustomForm
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereExport($value)
 *
 * @property-read CustomFieldGroup|null $customFieldGroup
 * @property-read CustomFieldGroup|null $fieldGroup
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereVisible($value)
 *
 * @mixin \Eloquent
 */
class CustomField extends BaseModel
{
    use HasCompany;

    public $timestamps = false;

    protected $guarded = ['id'];

    public function leadCustomForm(): HasOne
    {
        return $this->hasOne(LeadCustomForm::class, 'custom_fields_id');
    }

    public function ticketCustomForm(): HasOne
    {
        return $this->hasOne(TicketCustomForm::class, 'custom_fields_id');
    }

    public function customFieldGroup(): HasOne
    {
        return $this->hasOne(CustomFieldGroup::class, 'custom_field_group_id');
    }

    public function fieldGroup(): BelongsTo
    {
        return $this->belongsTo(CustomFieldGroup::class, 'custom_field_group_id');
    }

    public static function exportCustomFields($model)
    {
        $customFieldsGroupsId = CustomFieldGroup::where('model', $model::CUSTOM_FIELD_MODEL)->select('id')->first();
        $customFields = collect();

        if ($customFieldsGroupsId) {
            $customFields = CustomField::where('custom_field_group_id', $customFieldsGroupsId->id)->where(function ($q) {
                return $q->where('export', 1)->orWhere('visible', 'true');
            })->get();
        }

        return $customFields;
    }

    /**
     * Resolve the display label for a select-type custom field from stored JSON option definitions and the stored value.
     *
     * Handles numeric indices, string keys, and legacy values that match option labels case-insensitively (avoids undefined array key when DB holds a label but options are 0-indexed).
     */
    public static function resolveSelectFieldDisplayValue(?string $valuesJson, mixed $storedValue): string
    {
        if ($storedValue === null || $storedValue === '') {
            return '--';
        }

        $decoded = json_decode($valuesJson ?? '', true);
        if (! is_array($decoded)) {
            $decoded = [];
        }

        if ($decoded === []) {
            return (string) $storedValue;
        }

        $candidates = [$storedValue];
        if (is_numeric($storedValue)) {
            $candidates[] = (int) $storedValue;
            $candidates[] = (string) (int) $storedValue;
        }

        foreach ($candidates as $key) {
            if (array_key_exists($key, $decoded)) {
                return (string) $decoded[$key];
            }
        }

        foreach ($decoded as $optionLabel) {
            if (is_string($optionLabel) && strcasecmp((string) $optionLabel, (string) $storedValue) === 0) {
                return $optionLabel;
            }
        }

        return (string) $storedValue;
    }

    /**
     * @param  array<int, string>|null  $orderColumnMap  Map column index => orderBy column (e.g. [2 => 'users.id', 3 => 'client_details.client_code']) to match DataTable order
     */
    public static function customFieldData($datatables, $model, $relation = null, $idsQuery = null, $modelIdColumn = null, $orderColumnMap = null)
    {
        $customFields = CustomField::exportCustomFields($model);
        $customFieldNames = [];
        $customFieldsId = $customFields->pluck('id');

        $modelConstant = is_string($model) ? $model : $model::CUSTOM_FIELD_MODEL;

        if ($customFieldsId->isEmpty()) {
            return $customFieldNames;
        }

        $fieldData = collect();
        if ($idsQuery !== null && $modelIdColumn !== null && request()->ajax() && request()->has('start') && request()->has('length')) {
            $start = (int) request()->input('start', 0);
            $length = (int) request()->input('length', 10);
            $length = min(max($length, 1), 500);
            $defaultOrderCol = $modelIdColumn ?: 'users.id';
            $orderCol = $defaultOrderCol;
            $orderDir = 'asc';
            if (is_array($orderColumnMap) && ! empty($orderColumnMap)) {
                $orderIndex = (int) request()->input('order.0.column', 2);
                $orderDir = strtolower((string) request()->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';
                $orderCol = $orderColumnMap[$orderIndex] ?? $defaultOrderCol;
            }
            $idsQueryClone = (clone $idsQuery)
                ->select(DB::raw($modelIdColumn . ' as _model_id'))
                ->skip($start)
                ->take($length);
            $idsQueryClone->orderBy($orderCol, $orderDir);
            $ids = $idsQueryClone->pluck('_model_id')
                ->filter()
                ->unique()
                ->values();
            if ($ids->isNotEmpty()) {
                $fieldData = DB::table('custom_fields_data')
                    ->where('model', $modelConstant)
                    ->whereIn('custom_field_id', $customFieldsId)
                    ->whereIn('model_id', $ids)
                    ->select('id', 'custom_field_id', 'model_id', 'value')
                    ->get();
            }
        } else {
            $fieldData = DB::table('custom_fields_data')
                ->where('model', $modelConstant)
                ->whereIn('custom_field_id', $customFieldsId)
                ->select('id', 'custom_field_id', 'model_id', 'value')
                ->get();
        }

        foreach ($customFields as $customField) {
            $datatables->addColumn($customField->name, function ($row) use ($fieldData, $customField, $relation) {

                $finalData = $fieldData->filter(function ($value) use ($customField, $row, $relation) {
                    return ($value->custom_field_id == $customField->id) && ($value->model_id == ($relation ? $row?->{$relation}?->id : $row->id));
                })->first();

                if ($customField->type == 'select') {
                    return $finalData
                        ? self::resolveSelectFieldDisplayValue($customField->values, $finalData->value)
                        : '--';
                }

                if ($customField->type == 'date') {
                    $dateValue = $finalData?->value;
                    if (! empty($dateValue)) {
                        try {
                            $formattedDate = Carbon::parse($dateValue)->translatedFormat(company()->date_format);

                            return $formattedDate;
                        } catch (\Exception $e) {
                            return '<span class="text-danger">' . __('Invalid Date') . '</span>';
                        }
                    }

                    return '--';
                }

                if ($customField->type == 'file') {
                    return $finalData ? '<a href="' . asset_url_local_s3('custom_fields/' . $finalData->value) . '" target="__blank" class="text-dark-grey">' . __('app.storageSetting.viewFile') . '</a>' : '--';
                }

                return $finalData ? $finalData->value : '--';
            });

            // This will use for datatable raw column
            if ($customField->type == 'file') {
                $customFieldNames[] = $customField->name;
            }
        }

        return $customFieldNames;
    }

    public static function generateUniqueSlug($label, $moduleId)
    {
        $slug = str_slug($label);
        $count = CustomField::where('name', $slug)->where('custom_field_group_id', $moduleId)->count();

        if ($count > 0) {
            $i = 1;

            while (CustomField::where('name', $slug . '-' . $i)->where('custom_field_group_id', $moduleId)->count() > 0) {
                $i++;
            }

            $slug .= '-' . $i;
        }

        return $slug;
    }
}
