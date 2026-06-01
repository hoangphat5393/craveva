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
        public ?string $href = null,
        public ?string $menu = null,
        public mixed $active = false,
    ) {}

    public function isHeadingActive(): bool
    {
        return $this->menu !== null && $this->menu === $this->active;
    }

    public function render(): View
    {
        return view('components.setting-menu-accordion');
    }
}
