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
                                        <form action="{{ route('developertools.store') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-plus"></i> Generate New Credential
                                            </button>
                                        </form>
                                    </div>

                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    @if (session('error'))
                                        <div class="alert alert-danger">{{ session('error') }}</div>
                                    @endif

                                    @if (session('new_db_password'))
                                        <div class="alert alert-warning">
                                            <h4><i class="icon fa fa-warning"></i> IMPORTANT: Save these credentials now!</h4>
                                            <p><strong>Database Host:</strong> {{ request()->getHost() }}</p>
                                            <p><strong>Database Name:</strong> {{ config('developertools.gateway_db', 'api_gateway_db') }}</p>
                                            <p><strong>Username:</strong> {{ session('new_db_username') }}</p>
                                            <p><strong>Password:</strong> <span class="badge badge-warning" style="font-size: 1.2em">{{ session('new_db_password') }}</span></p>
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
                                                        <td colspan="6" class="text-center">No credentials found.</td>
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
                                                <li><strong>Database:</strong> {{ config('developertools.gateway_db', 'api_gateway_db') }}</li>
                                                <li><strong>Username:</strong> (Generated above)</li>
                                                <li><strong>Password:</strong> (Generated above)</li>
                                            </ul>
                                            <p class="text-muted mb-0">
                                                <i class="fa fa-info-circle"></i> Note: This connection is restricted to <strong>READ/WRITE</strong> access only for your company's data.
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
