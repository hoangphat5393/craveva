<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SettingMenuAccordion extends Component
{
    public bool $inAccordion = true;

    public function __construct(
        public string $title,
        public bool $open = false,
    ) {}

    public function render(): View
    {
        return view('components.setting-menu-accordion');
    }
}
