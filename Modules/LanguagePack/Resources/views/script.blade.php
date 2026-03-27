<script>
    $('body').on('click', '.languagePackPublish', function() {

        console.log('languagePackPublish');
        var languageCode = $(this).data('language-code');

        var isRepublish = $(this).data('republish');

        var alertMessage = isRepublish ? `@lang('languagepack::app.republishConfirm')` : `@lang('languagepack::app.publishConfirm')`;

        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: alertMessage,
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('app.yes')",
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

                var url = "{{ route('language-pack.publish') }}";
                window.apiHttp.postUrlEncoded(url, {
                    '_token': "{{ csrf_token() }}",
                    'languageCode': languageCode,
                    'isRepublish': isRepublish,
                })
                    .then(function(response) {
                        if (response.status == "success") {
                            window.location.reload();
                        }
                    })
                    .catch(function (err) {
                        $.handleApiFormError(err);
                    });
            }
        });
    });

    $('body').on('click', '#languagePackPublishAll', function() {

        var alertMessage = `@lang('languagepack::app.publishAllConfirm')`;

        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: alertMessage,
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('app.yes')",
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

                var url = "{{ route('language-pack.publish-all') }}";
                window.apiHttp.postUrlEncoded(url, {
                    '_token': "{{ csrf_token() }}",
                })
                    .then(function(response) {
                        if (response.status == "success") {
                            window.location.reload();
                        }
                    })
                    .catch(function (err) {
                        $.handleApiFormError(err);
                    });
            }
        });
    });

    $('body').on('click', '#languagePackSyncKeys', function() {
        var url = "{{ route('language-pack.sync-keys') }}";
        window.apiHttp.postUrlEncoded(url, { '_token': "{{ csrf_token() }}" })
            .then(function(response) {
                if (response.message) {
                    Swal.fire({
                        icon: 'success',
                        title: response.message,
                        buttonsStyling: false,
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                }
            })
            .catch(function (err) {
                $.handleApiFormError(err);
            });
    });
</script>
