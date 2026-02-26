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

            // Create Access Control Function in Main DB
            $this->createAccessFunction($mainDb);

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

    private function createAccessFunction($mainDb)
    {
        $funcName = "get_developer_tools_company_id";

        // Drop if exists
        DB::statement("DROP FUNCTION IF EXISTS `$mainDb`.`$funcName`");

        // Create Function
        // Note: We use SUBSTRING_INDEX(USER(), '@', 1) to get the username part.
        // This relies on db_user_mapping having the exact username.
        $sql = "
            CREATE FUNCTION `$mainDb`.`$funcName`() RETURNS INT
            READS SQL DATA
            DETERMINISTIC
            BEGIN
                DECLARE c_id INT;
                SELECT company_id INTO c_id FROM `$mainDb`.`db_user_mapping`
                WHERE db_username = SUBSTRING_INDEX(USER(), '@', 1)
                LIMIT 1;
                RETURN c_id;
            END
        ";

        try {
            DB::unprepared($sql);
            $this->info("Created access control function: $funcName");
        } catch (\Exception $e) {
            $this->warn("Could not create function. Ensure you have SUPER privileges or log_bin_trust_function_creators is on. Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function createSecureView($gatewayDb, $mainDb, $tableName)
    {
        // Use the function for filtering.
        // This avoids JOIN in the VIEW, making it updatable (INSERT/UPDATE/DELETE).
        $funcName = "`$mainDb`.`get_developer_tools_company_id`";

        $sql = "CREATE OR REPLACE VIEW `$gatewayDb`.`$tableName` AS
                SELECT *
                FROM `$mainDb`.`$tableName`
                WHERE company_id = $funcName()
                WITH CHECK OPTION";

        DB::statement($sql);
        $this->line("Created secure view for: $tableName", 'v');
    }

    private function createCompanyView($gatewayDb, $mainDb)
    {
        $funcName = "`$mainDb`.`get_developer_tools_company_id`";

        $sql = "CREATE OR REPLACE VIEW `$gatewayDb`.`companies` AS
                SELECT *
                FROM `$mainDb`.`companies`
                WHERE id = $funcName()
                WITH CHECK OPTION";

        DB::statement($sql);
        $this->line("Created secure view for: companies", 'v');
    }
}
