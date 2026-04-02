<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MenuItem extends Component
{
    public $icon;

    public $text;

    public $link;

    public $active;

    public $addon;

    public $count;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($icon, $text, $link = null, $active = false, $addon = false, $count = 0)
    {
        $this->text = $text;
        $this->icon = $icon;
        $this->link = $link;
        $this->active = $this->resolveActiveState((bool) $active, $link);
        $this->addon = $addon;
        $this->count = $count;
    }

    protected function resolveActiveState(bool $active, ?string $link): bool
    {
        if ($active || empty($link) || ! request()) {
            return $active;
        }

        $linkPath = (string) parse_url($link, PHP_URL_PATH);
        $normalizedLinkPath = trim($linkPath, '/');
        $currentPath = trim((string) request()->path(), '/');

        if ($normalizedLinkPath === '') {
            return $currentPath === '';
        }

        return $currentPath === $normalizedLinkPath
            || str_starts_with($currentPath, $normalizedLinkPath . '/');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.menu-item');
    }
}
