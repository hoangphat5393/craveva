<?php

namespace App\Services\Company;

/**
 * One purge target table. Scope:
 * - company: WHERE company_id = ?
 * - child_of_company: child.fk IN (SELECT parent.id FROM parent WHERE company_id = ?)
 */
final class CompanyTransactionPurgeStep
{
    public function __construct(
        public readonly string $phase,
        public readonly string $table,
        public readonly string $scope = 'company',
        public readonly ?string $childColumn = null,
        public readonly ?string $parentTable = null,
        public readonly ?string $parentLinkColumn = null,
    ) {}
}
