<?php

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Scopes\CompanyScope;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoice_settings') && ! Schema::hasColumn('invoice_settings', 'phase1_min_gross_margin_percent')) {
            Schema::table('invoice_settings', function (Blueprint $table) {
                $table->decimal('phase1_min_gross_margin_percent', 8, 2)->nullable()->after('estimate_terms');
            });
        }

        if (! Schema::hasTable('module_settings')) {
            return;
        }

        $pilotCompanyIds = [];

        if (Schema::hasTable('estimates') && Schema::hasColumn('estimates', 'president_review_status')) {
            $pilotCompanyIds = DB::table('estimates')
                ->whereNotNull('president_review_status')
                ->distinct()
                ->pluck('company_id')
                ->map(static fn ($id): int => (int) $id)
                ->all();
        }

        Company::withoutGlobalScopes()
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($companies) use ($pilotCompanyIds): void {
                foreach ($companies as $company) {
                    $companyId = (int) $company->id;
                    $activate = in_array($companyId, $pilotCompanyIds, true);

                    foreach (['admin', 'employee'] as $type) {
                        ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
                            [
                                'company_id' => $companyId,
                                'module_name' => 'estimates_phase1_review',
                                'type' => $type,
                            ],
                            [
                                'status' => $activate ? 'active' : 'deactive',
                                'is_allowed' => 1,
                            ],
                        );
                    }
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('invoice_settings') && Schema::hasColumn('invoice_settings', 'phase1_min_gross_margin_percent')) {
            Schema::table('invoice_settings', function (Blueprint $table) {
                $table->dropColumn('phase1_min_gross_margin_percent');
            });
        }

        if (Schema::hasTable('module_settings')) {
            ModuleSetting::withoutGlobalScope(CompanyScope::class)
                ->where('module_name', 'estimates_phase1_review')
                ->delete();
        }
    }
};
