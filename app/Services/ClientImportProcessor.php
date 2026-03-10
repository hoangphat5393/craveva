<?php

namespace App\Services;

use App\Models\ClientDetails;
use App\Models\Country;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAuth;
use App\Traits\UniversalSearchTrait;
use Exception;

class ClientImportProcessor
{
    use UniversalSearchTrait;

    /**
     * Process a single row of client import. Throws on error; returns null when duplicate client_code (skip).
     *
     * @param  array  $row  Row data (array of cell values)
     * @param  array  $columns  Map of column index => field id (e.g. [0 => 'name', 1 => 'email'])
     * @param  \App\Models\Company|null  $company
     * @return \App\Models\User|null Created user, or null if row skipped (duplicate client_code)
     *
     * @throws Exception
     */
    public static function processRow(array $row, array $columns, $company): ?User
    {
        if (empty(self::columnKeys($columns, 'name'))) {
            throw new Exception(__('messages.invalidData'));
        }

        $nameValue = self::getValue($row, $columns, 'name');
        $nameTrimmed = $nameValue !== null ? trim((string) $nameValue) : '';
        // Fallback: when name column is empty but client_code exists, use client_code as display name
        // (common in Miaolin/ERP exports where last row or some rows have code only)
        if ($nameTrimmed === '' && self::columnExists($columns, 'client_code')) {
            $codeValue = self::getValue($row, $columns, 'client_code');
            $nameTrimmed = $codeValue !== null ? trim((string) $codeValue) : '';
        }
        if ($nameTrimmed === '') {
            throw new Exception(__('messages.clientNameRequired'));
        }

        $companyId = $company?->id;
        $user = null;
        $duplicateByClientCode = false;

        if (self::columnExists($columns, 'email') && self::isEmailValid(self::getValue($row, $columns, 'email'))) {
            $user = User::where('company_id', $companyId)->where('email', self::getValue($row, $columns, 'email'))->first();
        }

        if (! $user && self::columnExists($columns, 'client_code') && self::getValue($row, $columns, 'client_code')) {
            $existingDetails = ClientDetails::where('company_id', $companyId)
                ->where('client_code', self::getValue($row, $columns, 'client_code'))
                ->first();
            if ($existingDetails) {
                $user = $existingDetails->user;
                $duplicateByClientCode = true;
            }
        }

        if ($user) {
            if ($duplicateByClientCode) {
                return null;
            }
            $msg = __('messages.duplicateEntryForEmail') . self::getValue($row, $columns, 'email');
            throw new Exception($msg);
        }

        $countryID = self::columnExists($columns, 'country_id')
            ? Country::where('name', self::getValue($row, $columns, 'country_id'))->first()?->id
            : null;

        $user = new User;
        $user->company_id = $companyId;
        $user->name = $nameTrimmed;
        $user->email = self::columnExists($columns, 'email') && self::isEmailValid(self::getValue($row, $columns, 'email'))
            ? self::getValue($row, $columns, 'email')
            : null;
        $user->mobile = self::columnExists($columns, 'mobile') ? self::getValue($row, $columns, 'mobile') : null;
        $user->gender = self::columnExists($columns, 'gender') ? strtolower(self::getValue($row, $columns, 'gender')) : null;
        $user->country_id = $countryID;

        $emailKey = self::columnKeys($columns, 'email');
        $emailValue = ! empty($emailKey) ? ($row[$emailKey[0]] ?? null) : null;
        if ($emailValue && filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
            $userAuth = UserAuth::createUserAuthCredentials($emailValue);
            $user->user_auth_id = $userAuth->id;
        }

        $user->save();

        $clientDetails = new ClientDetails;
        $clientDetails->company_id = $companyId;
        $clientDetails->user_id = $user->id;
        $clientDetails->client_code = self::columnExists($columns, 'client_code') ? self::getValue($row, $columns, 'client_code') : null;
        $clientDetails->company_name = self::columnExists($columns, 'company_name') ? self::getValue($row, $columns, 'company_name') : null;
        $clientDetails->address = self::columnExists($columns, 'address') ? self::getValue($row, $columns, 'address') : null;
        $clientDetails->city = self::columnExists($columns, 'city') ? self::getValue($row, $columns, 'city') : null;
        $clientDetails->state = self::columnExists($columns, 'state') ? self::getValue($row, $columns, 'state') : null;
        $clientDetails->postal_code = self::columnExists($columns, 'postal_code') ? self::getValue($row, $columns, 'postal_code') : null;
        $clientDetails->office = self::columnExists($columns, 'company_phone') ? self::getValue($row, $columns, 'company_phone') : null;
        $clientDetails->website = self::columnExists($columns, 'company_website') ? self::getValue($row, $columns, 'company_website') : null;
        $clientDetails->gst_number = self::columnExists($columns, 'gst_number') ? self::getValue($row, $columns, 'gst_number') : null;
        $clientDetails->save();

        self::saveCustomFieldsFromRow($clientDetails, $row, $columns, $companyId);

        if (self::columnExists($columns, 'business_closure_date') && self::getValue($row, $columns, 'business_closure_date')) {
            $user->status = 'inactive';
            $user->save();
        }

        $role = Role::where('name', 'client')->where('company_id', $companyId)->select('id')->first();
        $user->attachRole($role->id);
        $user->assignUserRolePermission($role->id);

        $processor = new self;
        if ($user->email) {
            $processor->logSearchEntry($user->id, $user->email, 'clients.show', 'client', $user->company_id);
        }
        if ($user->clientDetails && $user->clientDetails->company_name) {
            $processor->logSearchEntry($user->id, $user->clientDetails->company_name, 'clients.show', 'client', $user->company_id);
        }

        return $user;
    }

    protected static function getValue(array $row, array $columns, string $column)
    {
        $keys = self::columnKeys($columns, $column);

        return ! empty($keys) ? ($row[$keys[0]] ?? null) : null;
    }

    protected static function columnExists(array $columns, string $column): bool
    {
        return ! empty(self::columnKeys($columns, $column));
    }

    protected static function columnKeys(array $columns, string $column): array
    {
        return array_keys($columns, $column);
    }

    protected static function isEmailValid(?string $email): bool
    {
        return $email !== null && $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    protected static function getClientCustomFieldNames(): array
    {
        return [
            'salesperson',
            'department',
            'sales_assistant_name',
            'channel_type',
            'business_type',
            'last_transaction_at',
            'payment_terms',
            'business_closure_date',
        ];
    }

    protected static function saveCustomFieldsFromRow(ClientDetails $clientDetails, array $row, array $columns, $companyId): void
    {
        $group = CustomFieldGroup::where('name', 'Client')
            ->where('model', ClientDetails::CUSTOM_FIELD_MODEL)
            ->where('company_id', $companyId)
            ->first();

        if (! $group) {
            return;
        }

        $customNames = self::getClientCustomFieldNames();
        $fields = CustomField::where('custom_field_group_id', $group->id)
            ->whereIn('name', $customNames)
            ->get()
            ->keyBy('name');

        $data = [];
        foreach ($customNames as $name) {
            if (! self::columnExists($columns, $name)) {
                continue;
            }
            $field = $fields->get($name);
            if (! $field) {
                continue;
            }
            $value = self::getValue($row, $columns, $name);
            if ($value === null || $value === '') {
                continue;
            }
            $data['field_' . $field->id] = $value;
        }

        if (! empty($data)) {
            $clientDetails->updateCustomFieldData($data, $companyId);
        }
    }
}
