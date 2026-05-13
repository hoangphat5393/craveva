<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SettingCard extends Component
{
    public function __construct(
        public string $method = 'PUT',
        public bool $withoutForm = false,
    ) {}

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.setting-card');
    }
}
