@extends('layouts.app')

@section('content')
<div class="w-100 d-flex">
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
                                    <div class="d-flex">
                                        <form method="GET" action="{{ route('developertools.codemap') }}" class="d-flex">
                                            <input type="text" name="q" class="form-control mr-2" placeholder="Search by name/path/role" value="{{ request('q') }}">
                                            <select name="language" class="form-control mr-2">
                                                <option value="">Language</option>
                                                <option value="PHP" @selected(request('language')=='PHP')>PHP</option>
                                                <option value="JavaScript" @selected(request('language')=='JavaScript')>JavaScript</option>
                                                <option value="CSS" @selected(request('language')=='CSS')>CSS</option>
                                                <option value="JSON" @selected(request('language')=='JSON')>JSON</option>
                                                <option value="Markdown" @selected(request('language')=='Markdown')>Markdown</option>
                                            </select>
                                            <input type="text" name="module" class="form-control mr-2" placeholder="Module" value="{{ request('module') }}">
                                            <button class="btn btn-secondary" type="submit">Filter</button>
                                        </form>
                                    </div>
                                    <div class="d-flex">
                                        <form action="{{ route('developertools.codemap.scan') }}" method="POST" class="mr-2">
                                            @csrf
                                            <button class="btn btn-primary" type="submit"><i class="fa fa-sync"></i> Scan & Save</button>
                                        </form>
                                    </div>
                                </div>
                    
                                @if (session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif
                    
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>File Name</th>
                                                <th>Path</th>
                                                <th>Language</th>
                                                <th>Framework</th>
                                                <th>Role</th>
                                                <th>Module</th>
                                                <th>Version</th>
                                                <th>Updated</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($records as $file)
                                                <tr>
                                                    <td>{{ $file->name }}</td>
                                                    <td>
                                                        <a href="file:///{{ $file->path }}" target="_blank">{{ $file->path }}</a>
                                                    </td>
                                                    <td>{{ $file->language }}</td>
                                                    <td>{{ $file->framework }}</td>
                                                    <td>{{ $file->role }}</td>
                                                    <td>{{ $file->module }}</td>
                                                    <td>{{ $file->version }}</td>
                                                    <td>{{ $file->last_modified_at }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">No data found. Please click "Scan & Save".</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                    
                                <div class="mt-3">
                                    {{ $records->withQueryString()->links() }}
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
