<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use App\Traits\HasCompany;
use Illuminate\Support\Carbon;

/**
 * App\Models\ThemeSetting
 *
 * @property int $id
 * @property string $panel
 * @property string $header_color
 * @property string $sidebar_color
 * @property string $sidebar_text_color
 * @property int $restrict_admin_theme_change
 * @property string $link_color
 * @property string|null $user_css
 * @property string $sidebar_theme
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $icon
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereHeaderColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereLinkColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting wherePanel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereSidebarColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereSidebarTextColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereSidebarTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereUserCss($value)
 *
 * @property int|null $company_id
 * @property-read Company|null $company
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereCompanyId($value)
 *
 * @mixin \Eloquent
 *
 * @property int $enable_rounded_theme
 * @property string|null $login_background
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereEnableRoundedTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereLoginBackground($value)
 */
class ThemeSetting extends BaseModel
{
    use HasCompany;

    /**
     * Superadmin front/global theme row uses panel=superadmin and company_id NULL.
     * Falls back to any legacy superadmin row (e.g. observer previously set company_id).
     */
    public static function forSuperadminGlobalTheme(): self
    {
        $base = static::withoutGlobalScope(CompanyScope::class)->where('panel', 'superadmin');

        $theme = (clone $base)->whereNull('company_id')->first();

        if ($theme !== null) {
            return $theme;
        }

        $theme = $base->orderBy('id')->first();

        if ($theme !== null) {
            return $theme;
        }

        $theme = new static;
        $theme->panel = 'superadmin';
        $theme->company_id = null;
        $theme->header_color = '#ed4040';
        $theme->sidebar_color = '#292929';
        $theme->sidebar_text_color = '#cbcbcb';
        $theme->link_color = '#ffffff';
        $theme->sidebar_theme = 'dark';
        $theme->enable_rounded_theme = 0;
        $theme->save();

        return $theme;
    }
}
