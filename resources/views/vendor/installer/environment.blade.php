@extends('vendor.installer.layouts.master')

@section('title', trans('installer_messages.environment.title'))
@section('style')
    <link href="{{ asset('installer/helper/helper.css') }}" rel="stylesheet"/>
    <style>
        .has-error {
            color: red;
        }

        .help-block {
            font-size: 12px;
        }

        .has-error input {
            color: black;
            border: 1px solid red;
        }

    </style>
@endsection
@section('container')

    <p class="text-center mb-2">Please enter your database connection details</p>

    <form method="post" action="{{ route('LaravelInstaller::environmentSave') }}" id="env-form">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label class="control-label">Hostname</label>
                    <input type="text" name="hostname" class="form-control" value="localhost">
                </div>

                <div class="form-group">
                    <label class="control-label">Database username</label>
                    <input type="text" name="username" class="form-control">
                </div>
                <div class="form-group">
                    <label class="control-label">Database password</label>
                    <div class="col-sm-12">
                        <input type="password" class="form-control" name="password">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label">Database name</label>
                    <div class="col-sm-12">
                        <input type="text" name="database" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="buttons">
                        <button class="button" onclick="checkEnv();return false">
                            {{ trans('installer_messages.next') }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
    <script>
        function checkEnv() {
            var form = document.getElementById('env-form');
            var button = form.querySelector('.button');
            var previousText = button.innerHTML;
            var query = new URLSearchParams(new FormData(form)).toString();
            var url = "{!! route('LaravelInstaller::environmentSave') !!}" + '?' + query;

            clearInstallerErrors(form);
            $.easyBlockUI('#env-form');
            button.disabled = true;
            button.innerHTML = 'Submitting...';

            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function(response) {
                return response.json();
            }).then(function(response) {
                if (response.status === 'success') {
                    if (response.message) {
                        showInstallerMessage(form, response.message, 'success');
                    }

                    if (response.action === 'redirect' && response.url) {
                        window.location.href = response.url;
                    }

                    return;
                }

                showInstallerFailure(form, response);
            }).catch(function() {
                showInstallerMessage(form, 'A server side error occurred. Please try again after sometime.', 'danger');
            }).finally(function() {
                $.easyUnblockUI('#env-form');
                button.disabled = false;
                button.innerHTML = previousText;
            });
        }

        function clearInstallerErrors(form) {
            form.querySelectorAll('.has-error').forEach(function(group) {
                group.classList.remove('has-error');
                group.querySelectorAll('.help-block').forEach(function(error) {
                    error.remove();
                });
            });

            var alert = form.querySelector('#alert');

            if (alert) {
                alert.remove();
            }
        }

        function showInstallerMessage(form, message, type) {
            var alert = form.querySelector('#alert');
            var html = '<div class="alert alert-' + type + '">' + message + '</div>';

            if (!alert) {
                var firstGroup = form.querySelector('.form-group');
                alert = document.createElement('div');
                alert.id = 'alert';
                firstGroup.parentNode.insertBefore(alert, firstGroup);
            }

            alert.innerHTML = html;
        }

        function showInstallerFailure(form, response) {
            if (response.message) {
                showInstallerMessage(form, response.message, 'danger');
            }

            if (!response.errors) {
                return;
            }

            Object.keys(response.errors).forEach(function(key) {
                var input = form.querySelector('[name="' + key + '"]') || form.querySelector('#' + key);

                if (!input) {
                    return;
                }

                var group = input.closest('.form-group');
                var errorContainer = group.querySelector('div') || group;
                var help = document.createElement('div');
                help.className = 'help-block';
                help.textContent = Array.isArray(response.errors[key]) ? response.errors[key].join(' ') : response.errors[key];
                errorContainer.appendChild(help);
                group.classList.add('has-error');
            });
        }
    </script>
@stop
@section('scripts')
    <script src="{{ asset('installer/js/jQuery-2.2.0.min.js') }}"></script>
    <script src="{{ asset('installer/helper/helper.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    </script>
@endsection
