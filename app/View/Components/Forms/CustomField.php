<?php

namespace App\View\Components\Forms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CustomField extends Component
{
    public $fields;

    public $model;

    /** When true: no outer p-20; grid matches purchase product form (col-lg-4). */
    public bool $compact;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($fields, $model = false, bool $compact = false)
    {
        $this->fields = $fields;
        $this->model = $model;
        $this->compact = $compact;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.forms.custom-field');
    }
}
