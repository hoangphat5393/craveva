<?php

namespace App\View\Components\Forms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Label extends Component
{
    public $fieldId;

    public $fieldLabel;

    public $popover;

    public $fieldRequired;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $fieldId,
        $fieldRequired = false,
        $fieldLabel = null,
        $popover = null
    ) {
        $this->fieldLabel = $this->normalizeFieldLabel($fieldLabel);
        $this->fieldId = $fieldId;
        $this->popover = is_array($popover) ? null : $popover;
        $this->fieldRequired = $this->normalizeFieldRequired($fieldRequired);
    }

    /**
     * Blade may pass booleans from :fieldRequired="... ? true : false"; bad data can rarely pass arrays.
     */
    protected function normalizeFieldRequired(mixed $fieldRequired): string
    {
        if (is_array($fieldRequired)) {
            return 'false';
        }
        if (is_bool($fieldRequired)) {
            return $fieldRequired ? 'true' : 'false';
        }

        return (string) ($fieldRequired ?: 'false');
    }

    protected function normalizeFieldLabel(mixed $fieldLabel): ?string
    {
        if ($fieldLabel === null) {
            return null;
        }
        if (is_string($fieldLabel)) {
            return $fieldLabel;
        }
        if (is_array($fieldLabel)) {
            return implode(' ', array_filter(array_map(static function ($v) {
                return is_scalar($v) || $v === null ? (string) $v : '';
            }, $fieldLabel)));
        }

        return is_scalar($fieldLabel) ? (string) $fieldLabel : '';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.forms.label');
    }
}
