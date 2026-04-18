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

                                    <form id="devtools-credential-form" action="{{ route('developertools.store') }}" method="POST" class="mb-4">
                                        @csrf
                                        <div class="card">
                                            <div class="card-header">
                                                <strong>Generate New Credential</strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <div class="mb-2 font-weight-bold">Allowed modules</div>
                                                    <div class="d-flex flex-wrap">
                                                        @foreach ($availableModules ?? [] as $moduleKey => $moduleDef)
                                                            <div class="mr-4 mb-2">
                                                                <label class="mb-0">
                                                                    <input type="checkbox" name="modules[]" value="{{ $moduleKey }}" @if (in_array($moduleKey, old('modules', $defaultModules ?? []))) checked @endif>
                                                                    {{ $moduleDef['label'] ?? $moduleKey }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="text-muted">
                                                        Tables mapped to the selected modules are exposed as <strong>views</strong> in your company gateway database (<code>api_gateway_&lt;company_id&gt;</code>), scoped to your company.
                                                        <strong>Custom field</strong> tables are merged automatically (implicit module); they cannot be unchecked when customizing tables.
                                                        The generated MySQL user receives <strong>ALL PRIVILEGES</strong> on that gateway database only (not on the main application schema).
                                                    </div>
                                                </div>
                                                <div class="mb-3 border-top pt-3">
                                                    <label class="mb-2 d-block font-weight-bold">
                                                        <input type="checkbox" name="customize_tables" value="1" id="devtools-customize-tables">
                                                        Customize exposed tables (optional)
                                                    </label>
                                                    <div id="devtools-table-picker" class="d-none">
                                                        <div class="d-flex flex-wrap align-items-center mb-2">
                                                            <input type="text" class="form-control form-control-xl mr-2 mb-2" style="max-width: 280px" id="devtools-table-filter" placeholder="Filter table names..." autocomplete="off">
                                                            <button type="button" class="btn btn-sm btn-outline-secondary mb-2 mr-1" id="devtools-tables-select-all">Select all</button>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary mb-2" id="devtools-tables-select-none">Deselect all</button>
                                                            <span class="text-muted small mb-2 ml-2" id="devtools-table-count"></span>
                                                        </div>
                                                        <div class="border rounded p-2 bg-white" style="max-height: 320px; overflow-y: auto;" id="devtools-tables-list"></div>
                                                        <div class="text-muted small mt-1">If customization is off, all tables for the selected modules (plus implicit custom fields) are exposed. Turn on to limit which views are created.</div>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-plus"></i> Generate Credential
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0 pl-3">
                                                @foreach ($errors->all() as $err)
                                                    <li>{{ $err }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <script>
                                        (function() {
                                            var previewUrl = @json(route('developertools.preview_tables'));
                                            var token = document.querySelector('meta[name="csrf-token"]');
                                            var csrf = token ? token.getAttribute('content') : '';
                                            var form = document.getElementById('devtools-credential-form');
                                            var moduleInputs = function() {
                                                return Array.prototype.slice.call(document.querySelectorAll('input[name="modules[]"]:checked')).map(function(i) {
                                                    return i.value;
                                                });
                                            };
                                            var customize = document.getElementById('devtools-customize-tables');
                                            var picker = document.getElementById('devtools-table-picker');
                                            var listEl = document.getElementById('devtools-tables-list');
                                            var filterEl = document.getElementById('devtools-table-filter');
                                            var countEl = document.getElementById('devtools-table-count');
                                            var implicitSet = {};

                                            function setImplicitSet(arr) {
                                                implicitSet = {};
                                                (arr || []).forEach(function(t) {
                                                    implicitSet[t] = true;
                                                });
                                            }

                                            function renderTables(names, implicitTables) {
                                                setImplicitSet(implicitTables || []);
                                                listEl.innerHTML = '';
                                                (names || []).forEach(function(name) {
                                                    var isImplicit = !!implicitSet[name];
                                                    var row = document.createElement('div');
                                                    row.className = 'devtools-table-row mb-1';
                                                    row.setAttribute('data-table-name', name);
                                                    var lab = document.createElement('label');
                                                    lab.className = 'mb-0 d-block';
                                                    var cb = document.createElement('input');
                                                    cb.type = 'checkbox';
                                                    cb.name = 'tables[]';
                                                    cb.value = name;
                                                    cb.checked = true;
                                                    if (isImplicit) {
                                                        cb.checked = true;
                                                        cb.disabled = true;
                                                        var h = document.createElement('input');
                                                        h.type = 'hidden';
                                                        h.name = 'tables[]';
                                                        h.value = name;
                                                        lab.appendChild(h);
                                                    }
                                                    lab.appendChild(cb);
                                                    lab.appendChild(document.createTextNode(' ' + name + (isImplicit ? ' (always included)' : '')));
                                                    row.appendChild(lab);
                                                    listEl.appendChild(row);
                                                });
                                                applyFilter();
                                                updateCount();
                                            }

                                            function applyFilter() {
                                                var q = (filterEl && filterEl.value) ? filterEl.value.toLowerCase() : '';
                                                Array.prototype.forEach.call(listEl.querySelectorAll('.devtools-table-row'), function(row) {
                                                    var n = (row.getAttribute('data-table-name') || '').toLowerCase();
                                                    row.style.display = !q || n.indexOf(q) !== -1 ? '' : 'none';
                                                });
                                            }

                                            function updateCount() {
                                                var total = listEl.querySelectorAll('input[type=checkbox][name="tables[]"]').length;
                                                var checked = listEl.querySelectorAll('input[type=checkbox][name="tables[]"]:checked').length;
                                                if (countEl) {
                                                    countEl.textContent = total ? (checked + ' / ' + total + ' selected') : '';
                                                }
                                            }

                                            function loadPreview() {
                                                if (!listEl) return;
                                                listEl.innerHTML = '<span class="text-muted">Loading tables…</span>';
                                                var body = new URLSearchParams();
                                                moduleInputs().forEach(function(m) {
                                                    body.append('modules[]', m);
                                                });
                                                fetch(previewUrl, {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/x-www-form-urlencoded',
                                                        'X-CSRF-TOKEN': csrf,
                                                        'Accept': 'application/json'
                                                    },
                                                    body: body.toString(),
                                                    credentials: 'same-origin'
                                                }).then(function(r) {
                                                    return r.json();
                                                }).then(function(data) {
                                                    if (!data || !Array.isArray(data.tables)) {
                                                        listEl.innerHTML = '<span class="text-danger">Could not load table list.</span>';
                                                        return;
                                                    }
                                                    renderTables(data.tables, data.implicit_tables || []);
                                                }).catch(function() {
                                                    listEl.innerHTML = '<span class="text-danger">Could not load table list.</span>';
                                                });
                                            }

                                            if (customize) {
                                                customize.addEventListener('change', function() {
                                                    if (customize.checked) {
                                                        picker.classList.remove('d-none');
                                                        loadPreview();
                                                    } else {
                                                        picker.classList.add('d-none');
                                                        listEl.innerHTML = '';
                                                    }
                                                });
                                            }
                                            document.querySelectorAll('input[name="modules[]"]').forEach(function(inp) {
                                                inp.addEventListener('change', function() {
                                                    if (customize && customize.checked) {
                                                        loadPreview();
                                                    }
                                                });
                                            });
                                            if (filterEl) {
                                                filterEl.addEventListener('input', applyFilter);
                                            }
                                            document.getElementById('devtools-tables-select-all') && document.getElementById('devtools-tables-select-all').addEventListener('click', function() {
                                                listEl.querySelectorAll('input[type=checkbox][name="tables[]"]:not(:disabled)').forEach(function(c) {
                                                    c.checked = true;
                                                });
                                                updateCount();
                                            });
                                            document.getElementById('devtools-tables-select-none') && document.getElementById('devtools-tables-select-none').addEventListener('click', function() {
                                                listEl.querySelectorAll('input[type=checkbox][name="tables[]"]:not(:disabled)').forEach(function(c) {
                                                    c.checked = false;
                                                });
                                                updateCount();
                                            });
                                            listEl && listEl.addEventListener('change', function(e) {
                                                if (e.target && e.target.name === 'tables[]') {
                                                    updateCount();
                                                }
                                            });
                                            if (form) {
                                                form.addEventListener('submit', function() {
                                                    if (!customize || !customize.checked) {
                                                        listEl.querySelectorAll('input[name="tables[]"]').forEach(function(i) {
                                                            i.remove();
                                                        });
                                                    }
                                                });
                                            }
                                            if (customize && customize.checked) {
                                                loadPreview();
                                            }
                                        })();
                                    </script>

                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    @if (session('warning'))
                                        <div class="alert alert-warning">{{ session('warning') }}</div>
                                    @endif
                                    @if (session('error'))
                                        <div class="alert alert-danger">{{ session('error') }}</div>
                                    @endif

                                    @if (session('new_db_password'))
                                        <div class="alert alert-warning" id="devtools-credential-box">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                                                <h4 class="mb-0"><i class="icon fa fa-warning"></i>IMPORTANT: Save these credentials now!</h4>
                                                <button type="button" class="btn btn-sm btn-primary devtools-copy-all-btn" title="Copy all credentials">
                                                    <i class="fa fa-copy"></i> Copy all
                                                </button>
                                            </div>
                                            <p><strong>Database Host:</strong>
                                                <span class="credential-value" data-copy="{{ $credentialDisplayHost }}">{{ $credentialDisplayHost }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-dark ml-1 devtools-copy-btn" data-copy="{{ $credentialDisplayHost }}" title="Copy"><i class="fa fa-copy"></i></button>
                                            </p>
                                            <p><strong>Database Name:</strong>
                                                <span class="credential-value" data-copy="{{ session('new_db_name', config('developertools.gateway_db', 'api_gateway_db')) }}">{{ session('new_db_name', config('developertools.gateway_db', 'api_gateway_db')) }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-dark ml-1 devtools-copy-btn" data-copy="{{ session('new_db_name', config('developertools.gateway_db', 'api_gateway_db')) }}" title="Copy"><i class="fa fa-copy"></i></button>
                                            </p>
                                            <p><strong>Username:</strong>
                                                <span class="credential-value" data-copy="{{ session('new_db_username') }}">{{ session('new_db_username') }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-dark ml-1 devtools-copy-btn" data-copy="{{ session('new_db_username') }}" title="Copy"><i class="fa fa-copy"></i></button>
                                            </p>
                                            <p><strong>Password:</strong>
                                                <span class="badge badge-warning credential-value" style="font-size: 1.2em" data-copy="{{ session('new_db_password') }}">{{ session('new_db_password') }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-dark ml-1 devtools-copy-btn" data-copy="{{ session('new_db_password') }}" title="Copy"><i class="fa fa-copy"></i></button>
                                            </p>
                                            @if (session('new_db_modules'))
                                                @php $modulesStr = is_array(session('new_db_modules')) ? implode(', ', session('new_db_modules')) : session('new_db_modules'); @endphp
                                                <p><strong>Allowed Modules:</strong>
                                                    <span class="credential-value" data-copy="{{ $modulesStr }}">{{ $modulesStr }}</span>
                                                    <button type="button" class="btn btn-sm btn-outline-dark ml-1 devtools-copy-btn" data-copy="{{ $modulesStr }}" title="Copy"><i class="fa fa-copy"></i></button>
                                                </p>
                                            @endif
                                            @if (session('new_db_tables_count'))
                                                <p><strong>Exposed tables (views):</strong>
                                                    <span class="credential-value" data-copy="{{ session('new_db_tables_count') }}">{{ session('new_db_tables_count') }}</span>
                                                    <button type="button" class="btn btn-sm btn-outline-dark ml-1 devtools-copy-btn" data-copy="{{ session('new_db_tables_count') }}" title="Copy"><i class="fa fa-copy"></i></button>
                                                </p>
                                            @endif
                                            @if (session('new_db_views_count'))
                                                <p><strong>Created Views:</strong>
                                                    <span class="credential-value" data-copy="{{ session('new_db_views_count') }}">{{ session('new_db_views_count') }}</span>
                                                    <button type="button" class="btn btn-sm btn-outline-dark ml-1 devtools-copy-btn" data-copy="{{ session('new_db_views_count') }}" title="Copy"><i class="fa fa-copy"></i></button>
                                                </p>
                                            @endif
                                            <p class="mb-0">The password will not be shown again.</p>
                                            @php
                                                $copyAllLines = ['Database Host: ' . $credentialDisplayHost, 'Database Name: ' . session('new_db_name', config('developertools.gateway_db', 'api_gateway_db')), 'Username: ' . session('new_db_username'), 'Password: ' . session('new_db_password')];
                                                if (session('new_db_modules')) {
                                                    $copyAllLines[] = 'Allowed Modules: ' . (is_array(session('new_db_modules')) ? implode(', ', session('new_db_modules')) : session('new_db_modules'));
                                                }
                                                if (session('new_db_tables_count')) {
                                                    $copyAllLines[] = 'Exposed tables (count): ' . session('new_db_tables_count');
                                                }
                                                if (session('new_db_views_count')) {
                                                    $copyAllLines[] = 'Created Views: ' . session('new_db_views_count');
                                                }
                                                $copyAllText = implode("\n", $copyAllLines);
                                            @endphp
                                            <textarea id="devtools-copy-all-text" class="d-none" readonly>{{ $copyAllText }}</textarea>
                                        </div>
                                        <script>
                                            (function() {
                                                var copyAllBtn = document.querySelector('.devtools-copy-all-btn');
                                                if (copyAllBtn) {
                                                    copyAllBtn.addEventListener('click', function() {
                                                        var ta = document.getElementById('devtools-copy-all-text');
                                                        var text = ta ? ta.value : '';
                                                        if (!text) return;
                                                        if (navigator.clipboard && navigator.clipboard.writeText) {
                                                            navigator.clipboard.writeText(text).then(function() {
                                                                var icon = copyAllBtn.querySelector('i');
                                                                var label = copyAllBtn.childNodes[1] && copyAllBtn.childNodes[1].textContent ? copyAllBtn.childNodes[1] : null;
                                                                var oldHtml = copyAllBtn.innerHTML;
                                                                copyAllBtn.innerHTML = '<i class="fa fa-check"></i> Copied!';
                                                                copyAllBtn.classList.add('btn-success');
                                                                copyAllBtn.classList.remove('btn-primary');
                                                                setTimeout(function() {
                                                                    copyAllBtn.innerHTML = oldHtml;
                                                                    copyAllBtn.classList.remove('btn-success');
                                                                    copyAllBtn.classList.add('btn-primary');
                                                                }, 1500);
                                                            });
                                                        } else {
                                                            var tmp = document.createElement('textarea');
                                                            tmp.value = text;
                                                            tmp.style.position = 'fixed';
                                                            tmp.style.opacity = '0';
                                                            document.body.appendChild(tmp);
                                                            tmp.select();
                                                            document.execCommand('copy');
                                                            document.body.removeChild(tmp);
                                                            var oldHtml = copyAllBtn.innerHTML;
                                                            copyAllBtn.innerHTML = '<i class="fa fa-check"></i> Copied!';
                                                            copyAllBtn.classList.add('btn-success');
                                                            copyAllBtn.classList.remove('btn-primary');
                                                            setTimeout(function() {
                                                                copyAllBtn.innerHTML = oldHtml;
                                                                copyAllBtn.classList.remove('btn-success');
                                                                copyAllBtn.classList.add('btn-primary');
                                                            }, 1500);
                                                        }
                                                    });
                                                }
                                                document.querySelectorAll('.devtools-copy-btn').forEach(function(btn) {
                                                    btn.addEventListener('click', function() {
                                                        var text = this.getAttribute('data-copy') || '';
                                                        if (navigator.clipboard && navigator.clipboard.writeText) {
                                                            navigator.clipboard.writeText(text).then(function() {
                                                                var icon = btn.querySelector('i');
                                                                var oldClass = icon.className;
                                                                icon.className = 'fa fa-check text-success';
                                                                setTimeout(function() {
                                                                    icon.className = oldClass;
                                                                }, 1200);
                                                            });
                                                        } else {
                                                            var ta = document.createElement('textarea');
                                                            ta.value = text;
                                                            ta.style.position = 'fixed';
                                                            ta.style.opacity = '0';
                                                            document.body.appendChild(ta);
                                                            ta.select();
                                                            document.execCommand('copy');
                                                            document.body.removeChild(ta);
                                                            var icon = btn.querySelector('i');
                                                            var oldClass = icon.className;
                                                            icon.className = 'fa fa-check text-success';
                                                            setTimeout(function() {
                                                                icon.className = oldClass;
                                                            }, 1200);
                                                        }
                                                    });
                                                });
                                            })();
                                        </script>
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
                                                    <th>Tables</th>
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
                                                        <td>{{ $credentialDisplayHost }}</td>
                                                        <td>
                                                            @if (is_array($cred->allowed_modules))
                                                                {{ implode(', ', $cred->allowed_modules) }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (is_array($cred->allowed_tables))
                                                                {{ count($cred->allowed_tables) }}
                                                            @else
                                                                —
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
                                                        <td colspan="9" class="text-center">No credentials found.</td>
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
                                                    <th>Tables</th>
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
                                                        <td>
                                                            @if (is_array($log->allowed_tables))
                                                                {{ count($log->allowed_tables) }}
                                                            @elseif ($log->allowed_tables_count !== null)
                                                                {{ $log->allowed_tables_count }}
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                        <td>{{ $log->created_views_count }}</td>
                                                        <td>{{ $log->duration_ms }}</td>
                                                        <td>{{ $log->created_at }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center">No logs found.</td>
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
                                                <li><strong>Host:</strong> {{ $credentialDisplayHost }}</li>
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
                                                <i class="fa fa-info-circle"></i> Note: You connect to the <strong>gateway</strong> database only. Data is exposed as views filtered to your company.
                                                The MySQL user has <strong>full privileges (ALL PRIVILEGES)</strong> on that gateway schema (e.g. create temporary tables); it cannot access other companies or the raw main DB outside those views.
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
