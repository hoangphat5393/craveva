<?php

use App\Models\LanguageSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Các bảng có FK language_setting_id → language_settings */
    private const TABLES_WITH_LANG_FK = [
        'tr_front_details',
        'front_features',
        'features',
        'footer_menu',
        'seo_details',
        'front_clients',
        'testimonials',
        'front_faqs',
        'sign_up_settings',
        'front_menu_buttons',
    ];

    /**
     * Chuẩn hóa locale: eng → en (ISO 639-1)
     * Xem FUNC_BUG/ENG_TO_EN_STANDARDIZATION.md
     */
    public function up(): void
    {
        $enRow = LanguageSetting::where('language_code', 'en')->first();
        $engRow = LanguageSetting::where('language_code', 'eng')->first();

        if (! $engRow) {
            return;
        }

        if (! $enRow) {
            DB::table('language_settings')->where('language_code', 'eng')->update(['language_code' => 'en']);

            return;
        }

        // Cả eng và en tồn tại: chuyển FK từ eng → en rồi xóa eng
        $engId = $engRow->id;
        $enId = $enRow->id;

        foreach (self::TABLES_WITH_LANG_FK as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'language_setting_id')) {
                DB::table($table)->where('language_setting_id', $engId)->update(['language_setting_id' => $enId]);
            }
        }

        DB::table('language_settings')->where('id', $engId)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $enRow = LanguageSetting::where('language_code', 'en')->first();
        if ($enRow && ! LanguageSetting::where('language_code', 'eng')->exists()) {
            DB::table('language_settings')->where('id', $enRow->id)->update(['language_code' => 'eng']);
        }
    }
};
