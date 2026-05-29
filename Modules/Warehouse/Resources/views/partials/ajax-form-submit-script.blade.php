@php
    /** @var string $formId */
    /** @var string $buttonId */
    /** @var string $submitUrl */
    $namespace = preg_replace('/[^a-zA-Z0-9_-]/', '', $buttonId);
@endphp
<script>
    (function() {
        const formSelector = @json('#' . $formId);
        const buttonSelector = @json('#' . $buttonId);
        const submitUrl = @json($submitUrl);

        if (typeof $.fn.selectpicker === 'function') {
            $(formSelector).find('.select-picker').selectpicker('refresh');
        }

        $('body').off('click.warehouseAjax{{ $namespace }}', buttonSelector).on('click.warehouseAjax{{ $namespace }}', buttonSelector, function() {
            const $btn = $(buttonSelector);
            $btn.prop('disabled', true);
            $.easyBlockUI(formSelector);

            window.apiHttp.postUrlEncoded(submitUrl, $(formSelector).serialize())
                .then(function(response) {
                    if (response.status !== 'success') {
                        return;
                    }

                    if (typeof closeTaskDetail === 'function' && typeof RIGHT_MODAL_CONTENT !== 'undefined' && $(RIGHT_MODAL_CONTENT).length) {
                        closeTaskDetail();
                    }

                    if (typeof showTable === 'function') {
                        showTable();
                    } else if (response.action === 'redirect' && response.url) {
                        window.location.href = response.url;
                    }

                    const message = response.message || '';
                    if (message && typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            text: message,
                            toast: true,
                            position: 'top-end',
                            timer: 3500,
                            showConfirmButton: false,
                            timerProgressBar: true,
                        });
                    }
                })
                .catch(function(err) {
                    $.handleApiFormError(err);
                })
                .finally(function() {
                    $btn.prop('disabled', false);
                    $.easyUnblockUI(formSelector);
                });
        });
    })();
</script>
