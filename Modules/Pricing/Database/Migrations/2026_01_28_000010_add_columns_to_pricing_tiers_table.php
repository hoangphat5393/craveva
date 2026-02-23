<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pricing_tiers')) {
            Schema::table('pricing_tiers', function (Blueprint $table) {
                if (!Schema::hasColumn('pricing_tiers', 'discount_type')) {
                    $table->enum('discount_type', ['percentage', 'fixed', 'specific_price'])->nullable()->after('description');
                }

                if (!Schema::hasColumn('pricing_tiers', 'discount_value')) {
                    $table->decimal('discount_value', 15, 4)->nullable()->after('discount_type');
                }

                if (!Schema::hasColumn('pricing_tiers', 'priority')) {
                    $table->integer('priority')->default(0)->after('discount_value');
                }

                if (!Schema::hasColumn('pricing_tiers', 'valid_from')) {
                    $table->date('valid_from')->nullable()->after('priority');
                }

                if (!Schema::hasColumn('pricing_tiers', 'valid_to')) {
                    $table->date('valid_to')->nullable()->after('valid_from');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pricing_tiers')) {
            Schema::table('pricing_tiers', function (Blueprint $table) {
                if (Schema::hasColumn('pricing_tiers', 'valid_to')) {
                    $table->dropColumn('valid_to');
                }
                if (Schema::hasColumn('pricing_tiers', 'valid_from')) {
                    $table->dropColumn('valid_from');
                }
                if (Schema::hasColumn('pricing_tiers', 'priority')) {
                    $table->dropColumn('priority');
                }
                if (Schema::hasColumn('pricing_tiers', 'discount_value')) {
                    $table->dropColumn('discount_value');
                }
                if (Schema::hasColumn('pricing_tiers', 'discount_type')) {
                    $table->dropColumn('discount_type');
                }
            });
        }
    }
};
