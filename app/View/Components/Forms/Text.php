<?php

namespace App\View\Components\Forms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Text extends Component
{
    public $fieldLabel;

    public $fieldRequired;

    public $fieldPlaceholder;

    public $fieldValue;

    public $fieldName;

    public $fieldId;

    public $fieldHelp;

    public $fieldReadOnly;

    public $popover;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($fieldLabel, $fieldName, $fieldId, $fieldRequired = false, $fieldPlaceholder = null, $fieldValue = null, $fieldHelp = null, $fieldReadOnly = false, $popover = null)
    {
        $this->fieldLabel = $this->toDisplayString($fieldLabel);
        $this->fieldRequired = $this->normalizeRequired($fieldRequired);
        $this->fieldPlaceholder = $this->toDisplayString($fieldPlaceholder);
        $this->fieldValue = $this->toDisplayString($fieldValue);
        $this->fieldName = $this->toDisplayString($fieldName);
        $this->fieldId = $this->toDisplayString($fieldId);
        $this->fieldHelp = $fieldHelp === null || $fieldHelp === '' ? $fieldHelp : $this->toDisplayString($fieldHelp);
        $this->fieldReadOnly = $this->normalizeReadOnly($fieldReadOnly);
        $this->popover = $popover === null || is_string($popover) ? $popover : $this->toDisplayString($popover);
    }

    /**
     * Laravel __() with a key matching a lang *file* group (e.g. __('Certification')) can return an array.
     */
    protected function toDisplayString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_string($value)) {
            return $value;
        }
        if (is_array($value)) {
            $parts = [];
            array_walk_recursive($value, function ($v) use (&$parts) {
                if (is_scalar($v) || $v === null) {
                    $parts[] = (string) $v;
                }
            });

            return implode(' ', array_filter($parts));
        }
        if (is_bool($value)) {
            return $value ? '1' : '';
        }

        return (string) $value;
    }

    protected function normalizeRequired(mixed $fieldRequired): string
    {
        if (is_bool($fieldRequired)) {
            return $fieldRequired ? 'true' : 'false';
        }
        if (is_array($fieldRequired)) {
            return 'false';
        }

        return (string) ($fieldRequired ?: 'false');
    }

    protected function normalizeReadOnly(mixed $fieldReadOnly): string
    {
        if (is_bool($fieldReadOnly)) {
            return $fieldReadOnly ? 'true' : 'false';
        }
        if (is_array($fieldReadOnly)) {
            return 'false';
        }

        return (string) ($fieldReadOnly ?: 'false');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.forms.text');
    }
}
