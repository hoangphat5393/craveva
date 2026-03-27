<!-- ROW START -->
<div class="row pb-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
</div>

@push('scripts')
    @include('sections.datatable_js')

    <script>
"use strict";  // Enforces strict mode for the entire script
        $('#referrals-table').on('preXhr.dt', function(e, settings, data) {
            var searchText = $('#search-text-field').val();
            data['searchText'] = searchText;
            data['affiliateId'] = "{{ $affiliate->id }}";
        });

        const showTable = () => {
            window.LaravelDataTables["referrals-table"].draw();
        }

        $('#search-text-field').on('change keyup',
        function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else {
                $('#reset-filters').addClass('d-none');
                showTable();
            }
        });

        $('body').on('click', '#reset-filters', function () {
            $('#filter-form')[0].reset();

            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('body').on('click', '#reset-filters-2', function () {
            $('#filter-form')[0].reset();

            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('payout-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('payout.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    $.easyBlockUI();
                    window.apiHttp.delete(url, "{{ csrf_token() }}")
                        .then(function(response) {
                            if (response.status == "success") {
                                showTable();
                            }
                        })
                        .catch(function(err) {
                            $.handleApiFormError(err);
                        })
                        .finally(function() {
                            $.easyUnblockUI();
                        });
                }
            });
        });

        $('body').on('change', '.change-payout-status', function() {

            let id = $(this).data('payout-id');
            let url = "{{ route('payouts.change_status') }}";
            let status = $(this).val();

            if (status) {
                window.apiHttp.postUrlEncoded(url, {
                    '_token': "{{ csrf_token() }}",
                    id: id,
                    status: status
                })
                    .then(function(response) {
                        if (response.status == "success") {
                            console.log(response);
                            showTable();
                        }
                    })
                    .catch(function(err) {
                        $.handleApiFormError(err);
                    });
            }
        });
    </script>
@endpush
