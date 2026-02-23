<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('company_customer_pricing')) {
            
            // Step 1: Rename and Cleanup
            if (Schema::hasColumn('company_customer_pricing', 'customer_company_id')) {
                Schema::table('company_customer_pricing', function (Blueprint $table) {
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    $indexes = $sm->listTableForeignKeys('company_customer_pricing');
                    
                    foreach ($indexes as $index) {
                        if (str_contains($index->getName(), 'customer_company_id')) {
                            $table->dropForeign($index->getName());
                        }
                    }
                    
                    try {
                        $table->dropUnique(['company_id', 'customer_company_id']);
                    } catch (\Exception $e) {}

                    $table->renameColumn('customer_company_id', 'client_id');
                });
            }

            // Step 2: Ensure correct type and add constraints
            if (Schema::hasColumn('company_customer_pricing', 'client_id')) {
                Schema::table('company_customer_pricing', function (Blueprint $table) {
                     // Force type to unsignedInteger to match users.id
                     // This fixes the issue if it was accidentally changed to BigInt
                     $table->unsignedInteger('client_id')->change();

                     $table->foreign('client_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
                     
                     try {
                        $table->unique(['company_id', 'client_id']);
                     } catch (\Exception $e) {}
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('company_customer_pricing')) {
             if (Schema::hasColumn('company_customer_pricing', 'client_id')) {
                Schema::table('company_customer_pricing', function (Blueprint $table) {
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    $indexes = $sm->listTableForeignKeys('company_customer_pricing');
                    foreach ($indexes as $index) {
                        if (str_contains($index->getName(), 'client_id')) {
                            $table->dropForeign($index->getName());
                        }
                    }

                    try {
                        $table->dropUnique(['company_id', 'client_id']);
                    } catch (\Exception $e) {}
                    
                    $table->renameColumn('client_id', 'customer_company_id');
                });
             }
            
            if (Schema::hasColumn('company_customer_pricing', 'customer_company_id')) {
                Schema::table('company_customer_pricing', function (Blueprint $table) {
                    $table->unsignedInteger('customer_company_id')->change();
                    $table->foreign('customer_company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
                    $table->unique(['company_id', 'customer_company_id']);
                });
            }
        }
    }
};
