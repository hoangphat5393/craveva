<?php

namespace App\Http\Controllers;

use App\DataTables\UserAuditReportDataTable;
use App\Models\User;

class AuditReportController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.auditReport';
    }

    public function index(UserAuditReportDataTable $dataTable)
    {
        abort_403(user()->permission('view_audit_report') != 'all');

        if (! request()->ajax()) {
            $this->employees = User::allEmployees();
        }

        return $dataTable->render('reports.audit.index', $this->data);
    }
}
