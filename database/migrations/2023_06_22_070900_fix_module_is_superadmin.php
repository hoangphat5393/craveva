<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('modules', 'is_superadmin')) {
            $modules = Module::withoutGlobalScopes()->get();

            $this->setIsSuperadminDefault();

            $superAdminModules = $modules->where('is_superadmin', 1)->pluck('id')->toArray();

            Module::withoutGlobalScopes()->update(['is_superadmin' => 0]);

            Module::withoutGlobalScopes()->whereIn('id', $superAdminModules)->update(['is_superadmin' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    private function setIsSuperadminDefault(): void
    {
        if (! Schema::hasColumn('modules', 'is_superadmin')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `modules` MODIFY `is_superadmin` TINYINT(1) NOT NULL DEFAULT 0");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"modules\" ALTER COLUMN \"is_superadmin\" TYPE BOOLEAN USING (\"is_superadmin\"::text IN ('1','true','t'))");
            DB::statement("ALTER TABLE \"modules\" ALTER COLUMN \"is_superadmin\" SET DEFAULT FALSE");
            DB::statement("ALTER TABLE \"modules\" ALTER COLUMN \"is_superadmin\" SET NOT NULL");
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [modules] ALTER COLUMN [is_superadmin] BIT NOT NULL");
            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
