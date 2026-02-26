@extends('layouts.app')

@section('content')
<div class="w-100 d-flex">
    @include('sections.setting-sidebar')

    <x-setting-card>
        <x-slot name="header">
            <div class="s-b-n-header" id="tabs">
                <h2 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang($pageTitle)
                </h2>
            </div>
        </x-slot>

        <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex">
                    <form method="GET" action="{{ route('funcnews.index') }}" class="d-flex">
                        <input type="text" name="q" class="form-control mr-2" placeholder="Tìm kiếm theo tên/đường dẫn/role" value="{{ request('q') }}">
                        <select name="language" class="form-control mr-2">
                            <option value="">Ngôn ngữ</option>
                            <option value="PHP" @selected(request('language')=='PHP')>PHP</option>
                            <option value="JavaScript" @selected(request('language')=='JavaScript')>JavaScript</option>
                            <option value="CSS" @selected(request('language')=='CSS')>CSS</option>
                            <option value="JSON" @selected(request('language')=='JSON')>JSON</option>
                            <option value="Markdown" @selected(request('language')=='Markdown')>Markdown</option>
                        </select>
                        <input type="text" name="module" class="form-control mr-2" placeholder="Module" value="{{ request('module') }}">
                        <button class="btn btn-secondary" type="submit">Lọc</button>
                    </form>
                </div>
                <div class="d-flex">
                    <form action="{{ route('funcnews.scan') }}" method="POST" class="mr-2">
                        @csrf
                        <button class="btn btn-primary" type="submit"><i class="fa fa-sync"></i> Quét & Lưu</button>
                    </form>
                    <a href="{{ route('funcnews.export') }}" class="btn btn-success mr-2"><i class="fa fa-download"></i> Export JSON</a>
                    <form action="{{ route('funcnews.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" class="form-control-file" accept=".json,.txt" required>
                        <button class="btn btn-info mt-1" type="submit"><i class="fa fa-upload"></i> Import JSON</button>
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
                            <th>Tên file</th>
                            <th>Đường dẫn</th>
                            <th>Ngôn ngữ</th>
                            <th>Framework</th>
                            <th>Vai trò</th>
                            <th>Module</th>
                            <th>Phiên bản</th>
                            <th>Cập nhật</th>
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
                                <td colspan="8" class="text-center">Chưa có dữ liệu. Hãy bấm "Quét & Lưu".</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $records->withQueryString()->links() }}
            </div>
        </div>
    </x-setting-card>
</div>
@endsection
