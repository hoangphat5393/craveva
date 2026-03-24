<?php

use App\Models\GlobalSetting;
use App\Models\PaymentGatewayCredentials;
use App\Models\SmtpSetting;
use App\Models\SocialAuthSetting;
use App\Models\StorageSetting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        // SOCIAL AUTH SETTING
        $this->changeToTextAndEncrypt(new SmtpSetting);

        // SOCIAL AUTH SETTING
        $this->changeToTextAndEncrypt(new SocialAuthSetting);

        // Payment Gateway Setting
        $this->changeToTextAndEncrypt(new PaymentGatewayCredentials);

        // Payment Gateway Setting
        $this->changeToTextAndEncrypt(new GlobalSetting);

        // STORAGE
        $storages = StorageSetting::all();

        foreach ($storages as $storage) {
            $this->saveEncrypt($storage, ['auth_keys']);
        }

        $globalSetting = GlobalSetting::first();

        if ($globalSetting) {
            DB::table($globalSetting->getTable())->where('google_map_key', '')->update(['google_map_key' => null]);
        }
    }

    private function changeToTextAndEncrypt($model)
    {

        $columns = $this->getColumns($model);

        foreach ($columns as $column) {
            $this->setTextNullable($model->getTable(), $column, true);
        }

        $dataObject = $model->all();

        foreach ($dataObject as $data) {
            $this->saveEncrypt($data, $columns);
        }
    }

    private function getColumns($model): array
    {
        $casts = $model->getCasts();

        $encryptedFields = array_keys(array_filter($casts, function ($value) {
            return $value === 'encrypted';
        }));

        return $encryptedFields;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    private function saveEncrypt($model, $columns)
    {

        if (! $model) {
            return true;
        }

        $fieldsToUpdate = [];

        foreach ($columns as $fieldItem) {

            $rawValue = $model->getRawOriginal($fieldItem);

            if (! is_null($rawValue) && $rawValue !== '') {
                $fieldsToUpdate[$fieldItem] = $rawValue;
            }
        }

        try {
            Crypt::decryptString(head($fieldsToUpdate));
        } catch (DecryptException $e) {
            $encryptedValues = [];

            if (count($fieldsToUpdate) == 0) {
                return true;
            }

            foreach ($fieldsToUpdate as $fieldName => $fieldValue) {
                $encryptedValues[$fieldName] = Crypt::encryptString($fieldValue);
            }

            DB::table($model->getTable())->where('id', $model->id)->update($encryptedValues);
        }
    }

    private function setTextNullable(string $table, string $column, bool $nullable): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $nullSql = $nullable ? 'NULL' : 'NOT NULL';

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` TEXT {$nullSql}");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE TEXT");
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" " . ($nullable ? 'DROP NOT NULL' : 'SET NOT NULL'));
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [{$column}] NVARCHAR(MAX) {$nullSql}");
            return;
        }

        throw new \RuntimeException('change() fallback is disabled to avoid doctrine/dbal dependency in this migration.');
    }
};
