@extends('layouts.app')

@section('content')
    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        @include('sections.setting-sidebar')

        <div class="settings-box bg-additional-grey rounded">
            <a class="mb-0 d-block d-lg-none text-dark-grey s-b-mob-sidebar" onclick="openSettingsSidebar()"><i class="fa fa-ellipsis-v"></i></a>
            <div class="s-b-inner s-b-notifications bg-white b-shadow-4 rounded">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                        @lang($pageTitle)
                    </h2>
                </div>
                <div class="s-b-n-content">
                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="nav-email" role="tabpanel" aria-labelledby="nav-email-tab">
                            <div class="d-flex flex-wrap justify-content-between">
                                <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="f-16 font-weight-bold">Credentials</h4>
                                    </div>

                                    <form action="{{ route('developertools.store') }}" method="POST" class="mb-4">
                                        @csrf
                                        <div class="card">
                                            <div class="card-header">
                                                <strong>Generate New Credential</strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <div class="mb-2 font-weight-bold">Allowed modules (read-only)</div>
                                                    <div class="d-flex flex-wrap">
                                                        @foreach(($availableModules ?? []) as $moduleKey => $moduleDef)
                                                            <div class="mr-4 mb-2">
                                                                <label class="mb-0">
                                                                    <input type="checkbox" name="modules[]" value="{{ $moduleKey }}"
                                                                        @if(in_array($moduleKey, old('modules', $defaultModules ?? []))) checked @endif>
                                                                    {{ $moduleDef['label'] ?? $moduleKey }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="text-muted">Only tables mapped to selected modules are exposed through database views.</div>
                                                </div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-plus"></i> Generate Credential
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    @if (session('error'))
                                        <div class="alert alert-danger">{{ session('error') }}</div>
                                    @endif

                                    @if (session('new_db_password'))
                                        <div class="alert alert-warning">
                                            <h4><i class="icon fa fa-warning"></i>IMPORTANT: Save these credentials now!</h4>
                                            <p><strong>Database Host:</strong> {{ request()->getHost() }}</p>
                                            <p><strong>Database Name:</strong> {{ session('new_db_name', config('developertools.gateway_db', 'api_gateway_db')) }}</p>
                                            <p><strong>Username:</strong> {{ session('new_db_username') }}</p>
                                            <p><strong>Password:</strong> <span class="badge badge-warning" style="font-size: 1.2em">{{ session('new_db_password') }}</span></p>
                                            @if (session('new_db_modules'))
                                                <p><strong>Allowed Modules:</strong> {{ is_array(session('new_db_modules')) ? implode(', ', session('new_db_modules')) : session('new_db_modules') }}</p>
                                            @endif
                                            @if (session('new_db_views_count'))
                                                <p><strong>Created Views:</strong> {{ session('new_db_views_count') }}</p>
                                            @endif
                                            <p class="mb-0">The password will not be shown again.</p>
                                        </div>
                                    @endif

                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>DB Username</th>
                                                    <th>DB Name</th>
                                                    <th>Host</th>
                                                    <th>Modules</th>
                                                    <th>Views</th>
                                                    <th>Created At</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($credentials as $cred)
                                                    <tr>
                                                        <td>{{ $cred->id }}</td>
                                                        <td>{{ $cred->db_username }}</td>
                                                        <td>{{ $cred->db_database }}</td>
                                                        <td>{{ $cred->db_host }}</td>
                                                        <td>
                                                            @if (is_array($cred->allowed_modules))
                                                                {{ implode(', ', $cred->allowed_modules) }}
                                                            @endif
                                                        </td>
                                                        <td>{{ $cred->created_views_count }}</td>
                                                        <td>{{ $cred->created_at }}</td>
                                                        <td>
                                                            <form action="{{ route('developertools.destroy', $cred->id) }}" method="POST" onsubmit="return confirm('Are you sure? This will revoke access immediately.');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">Revoke</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center">No credentials found.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="table-responsive mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="mb-0">Access Logs</h5>
                                        </div>
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Status</th>
                                                    <th>DB Username</th>
                                                    <th>DB Name</th>
                                                    <th>Modules</th>
                                                    <th>Views</th>
                                                    <th>Duration (ms)</th>
                                                    <th>Created At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse(($accessLogs ?? []) as $log)
                                                    <tr>
                                                        <td>{{ $log->status }}</td>
                                                        <td>{{ $log->db_username }}</td>
                                                        <td>{{ $log->db_database }}</td>
                                                        <td>
                                                            @if (is_array($log->requested_modules))
                                                                {{ implode(', ', $log->requested_modules) }}
                                                            @endif
                                                        </td>
                                                        <td>{{ $log->created_views_count }}</td>
                                                        <td>{{ $log->duration_ms }}</td>
                                                        <td>{{ $log->created_at }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center">No logs found.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="card bg-light">
                                        <div class="card-header">
                                            <h5 class="mb-0">Connection Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <p>Use these settings to connect your AI or external tools:</p>
                                            <ul>
                                                <li><strong>Driver:</strong> MySQL / MariaDB</li>
                                                <li><strong>Host:</strong> {{ request()->getHost() }}</li>
                                                <li><strong>Port:</strong> 3306 (Default)</li>
                                                <li><strong>Database:</strong>
                                                    @if (isset($credentials) && $credentials->count() > 0)
                                                        {{ $credentials->first()->db_database }}
                                                    @else
                                                        {{ config('developertools.gateway_db', 'api_gateway_db') }} (Generate credential first)
                                                    @endif
                                                </li>
                                                <li><strong>Username:</strong> (Generated above)</li>
                                                <li><strong>Password:</strong> (Generated above)</li>
                                            </ul>
                                            <p class="text-muted mb-0">
                                                <i class="fa fa-info-circle"></i> Note: This connection is restricted to <strong>READ-ONLY</strong> access only for your company's data and selected modules.
                                                You cannot see or modify other companies' data.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
