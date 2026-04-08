<?php

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
        if (! Schema::hasTable('purchase_vendors') || ! Schema::hasTable('purchase_vendor_categories')) {
            return;
        }

        if (! Schema::hasColumn('purchase_vendors', 'category_id')) {
            Schema::table('purchase_vendors', function (Blueprint $table) {
                $table->integer('category_id')->unsigned()->nullable();
            });
        }

        if ($this->foreignKeyExistsOnColumn('purchase_vendors', 'category_id')) {
            return;
        }

        try {
            Schema::table('purchase_vendors', function (Blueprint $table) {
                $table->foreign('category_id')->references('id')->on('purchase_vendor_categories')->onDelete('set null');
            });
        } catch (Throwable $e) {
            if ($this->isDuplicateForeignKeyOrColumnMessage($e->getMessage())) {
                return;
            }

            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('purchase_vendors') || ! Schema::hasColumn('purchase_vendors', 'category_id')) {
            return;
        }

        try {
            Schema::table('purchase_vendors', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
            });
        } catch (Throwable $e) {
            if (! $this->isMissingForeignKeyMessage($e->getMessage())) {
                throw $e;
            }
        }

        Schema::table('purchase_vendors', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
    }

    private function foreignKeyExistsOnColumn(string $table, string $column): bool
    {
        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return false;
        }

        $database = Schema::getConnection()->getDatabaseName();

        $row = DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
             AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, $table, $column]
        );

        return $row !== null && (int) $row->c > 0;
    }

    private function isDuplicateForeignKeyOrColumnMessage(string $message): bool
    {
        return str_contains($message, 'Duplicate column')
            || str_contains($message, 'Duplicate key name')
            || str_contains($message, 'already exists')
            || str_contains($message, '1826'); // MySQL duplicate FK
    }

    private function isMissingForeignKeyMessage(string $message): bool
    {
        return str_contains($message, "Can't DROP")
            || str_contains($message, 'check that column/key exists')
            || str_contains($message, 'Unknown key')
            || str_contains($message, '1091'); // MySQL can't drop
    }
};
