<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SubMenuItem extends Component
{
    public $text;

    public $link;

    public $permission;

    public $addon;

    public $active;

    public ?string $target;

    public ?string $rel;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($text, $link, $permission = true, $addon = false, $active = false, ?string $target = null, ?string $rel = null)
    {
        $this->text = $text;
        $this->link = $link;
        $this->addon = $addon;
        $this->active = $this->resolveActiveState((bool) $active, (string) $link);
        $this->target = $target;
        $this->rel = $rel ?? ($target === '_blank' ? 'noopener noreferrer' : null);
        // Show icon only when permission is true
        $this->permission = $permission;
    }

    protected function resolveActiveState(bool $active, string $link): bool
    {
        if ($active || $link === '' || ! request()) {
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
        return view('components.sub-menu-item');
    }
}
