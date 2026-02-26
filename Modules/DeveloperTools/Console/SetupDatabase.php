<?php

namespace Modules\DeveloperTools\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'developer-tools:setup-db';

    /**
     * The console command description.
     */
    protected $description = 'Create the API Gateway Database and Secure Views for all tables with company_id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mainDb = config('database.connections.mysql.database');
        $gatewayDb = config('developertools.gateway_db', 'api_gateway_db');

        $this->info("Setting up Gateway Database: $gatewayDb based on Main DB: $mainDb");

        try {
            // Create Database
            DB::statement("CREATE DATABASE IF NOT EXISTS `$gatewayDb`");
            $this->info("Database $gatewayDb checked/created.");

            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $dbKey = "Tables_in_" . $mainDb;

            $count = 0;

            foreach ($tables as $table) {
                $tableName = $table->$dbKey;
                
                // Skip migrations table and other system tables
                if (in_array($tableName, ['migrations', 'jobs', 'failed_jobs', 'password_resets', 'sessions', 'cache', 'db_user_mapping', 'developer_tools_credentials'])) {
                    continue;
                }

                // Check if it has company_id
                if (Schema::hasColumn($tableName, 'company_id')) {
                    $this->createSecureView($gatewayDb, $mainDb, $tableName);
                    $count++;
                } elseif ($tableName === 'companies') {
                    $this->createCompanyView($gatewayDb, $mainDb);
                    $count++;
                }
            }

            $this->info("Successfully created/updated $count secure views in $gatewayDb.");

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }

    private function createSecureView($gatewayDb, $mainDb, $tableName)
    {
        // Dynamic View Logic: Join with db_user_mapping on the current MySQL user
        $sql = "CREATE OR REPLACE VIEW `$gatewayDb`.`$tableName` AS
                SELECT t.*
                FROM `$mainDb`.`$tableName` t
                JOIN `$mainDb`.`db_user_mapping` m ON m.db_username = SUBSTRING_INDEX(USER(), '@', 1)
                WHERE t.company_id = m.company_id
                WITH CHECK OPTION";
        
        DB::statement($sql);
        $this->line("Created view for: $tableName", 'v');
    }

    private function createCompanyView($gatewayDb, $mainDb)
    {
        // Special case for 'companies' table, users can only see their own company row
        $sql = "CREATE OR REPLACE VIEW `$gatewayDb`.`companies` AS
                SELECT t.*
                FROM `$mainDb`.`companies` t
                JOIN `$mainDb`.`db_user_mapping` m ON m.db_username = SUBSTRING_INDEX(USER(), '@', 1)
                WHERE t.id = m.company_id";
        
        DB::statement($sql);
        $this->line("Created view for: companies", 'v');
    }
}
