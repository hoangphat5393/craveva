<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\CustomField\StoreCustomField;
use App\Http\Requests\CustomField\UpdateCustomField;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomFieldController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.customFields';
        $this->activeSettingMenu = 'custom_fields';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_custom_field_setting') !== 'all');

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $this->customFields = CustomField::join('custom_field_groups', 'custom_field_groups.id', '=', 'custom_fields.custom_field_group_id')
            ->select('custom_fields.id', 'custom_fields.custom_field_group_id', 'custom_field_groups.name as module', 'custom_fields.label', 'custom_fields.type', 'custom_fields.values', 'custom_fields.required', 'custom_fields.export', 'custom_fields.visible')
            ->orderBy('sort_order', 'asc')
            ->get();
        $this->groupedCustomFields = $this->customFields->groupBy('module');

        return view('custom-fields.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $canonicalGroupIds = CustomFieldGroup::query()
            ->selectRaw('MIN(id) as id')
            ->groupBy('name', 'model')
            ->pluck('id');

        $this->customFieldGroups = CustomFieldGroup::query()
            ->whereIn('id', $canonicalGroupIds)
            ->orderBy('name')
            ->get();
        $this->types = ['text', 'number', 'password', 'textarea', 'select', 'radio', 'date', 'checkbox', 'file'];

        return view('custom-fields.create-custom-field-modal', $this->data);
    }

    /**
     * @return array
     */
    public function store(StoreCustomField $request)
    {
        $targetGroup = CustomFieldGroup::findOrFail((int) $request->module);
        $canonicalGroupId = CustomFieldGroup::query()
            ->where('name', $targetGroup->name)
            ->where('model', $targetGroup->model)
            ->min('id') ?: $targetGroup->id;

        $name = CustomField::generateUniqueSlug($request->get('label'), $canonicalGroupId);
        $group = [
            'fields' => [
                [
                    'name' => $name,
                    'custom_field_group_id' => $canonicalGroupId,
                    'label' => $request->get('label'),
                    'type' => $request->get('type'),
                    'required' => $request->get('required'),
                    'values' => $request->get('value'),
                    'export' => $request->get('export'),
                    'visible' => $request->get('visible'),
                ],
            ],

        ];

        $this->addCustomField($group);

        return Reply::success('messages.recordSaved');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $this->field = CustomField::findOrFail($id);
        $this->field->values = json_decode($this->field->values);

        if (is_null($this->field->values)) {
            $this->field->values = [];
        }

        return view('custom-fields.edit-custom-field-modal', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomField $request, $id)
    {
        $field = CustomField::findOrFail($id);

        $name = CustomField::generateUniqueSlug($request->label, $field->custom_field_group_id);
        $field->label = $request->label;
        $field->name = $name;
        $field->values = json_encode($request->value);
        $field->required = $request->required;
        $field->export = $request->export;
        $field->visible = $request->visible;
        $field->save();

        return Reply::success('messages.updateSuccess');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        // Find the custom field
        $field = CustomField::findOrFail($id);
        $module = $field->fieldGroup->name;

        // Delete the custom field
        $field->delete();

        // Fetch the updated count for the module
        $updatedCount = CustomField::whereHas('fieldGroup', function ($query) use ($module) {
            $query->where('name', $module);
        })->count();

        return Reply::successWithData(__('messages.deleteSuccess'), ['updatedCount' => $updatedCount]);
    }

    public function changeOrder(Request $request)
    {
        $order = $request->order;
        if ($order) {
            foreach ($order as $key => $id) {
                if ($id) {
                    CustomField::where('id', $id)->update(['sort_order' => $key + 1]);
                }
            }
        }

        return Reply::success(__('messages.recordSaved'));
    }

    public function ajaxUpdateLabel(Request $request, $id)
    {
        $field = CustomField::findOrFail($id);
        $request->validate([
            'label' => 'required',
        ]);

        // Basic duplicate check for same module
        $exists = CustomField::where('custom_field_group_id', $field->custom_field_group_id)
            ->where('label', $request->label)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return Reply::error('The label already exists in this module.');
        }

        $name = CustomField::generateUniqueSlug($request->label, $field->custom_field_group_id);
        $field->label = $request->label;
        $field->name = $name;
        $field->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    private function addCustomField($group)
    {
        // Add Custom Fields for this group
        foreach ($group['fields'] as $field) {
            $insertData = [
                'custom_field_group_id' => $field['custom_field_group_id'],
                'label' => $field['label'],
                'name' => $field['name'],
                'type' => $field['type'],
                'export' => $field['export'],
                'visible' => $field['visible'],
            ];

            if (isset($field['required']) && (in_array($field['required'], ['yes', 'on', 1]))) {
                $insertData['required'] = 'yes';
            } else {
                $insertData['required'] = 'no';
            }

            // Single value should be stored as text (multi value JSON encoded)
            if (isset($field['values'])) {
                if (is_array($field['values'])) {
                    $insertData['values'] = \GuzzleHttp\json_encode($field['values']);
                } else {
                    $insertData['values'] = $field['values'];
                }
            }

            CustomField::create($insertData);
        }
    }
}
