@push('scripts')
    <script>
        if (typeof window.showSettingsSaveSuccessToast !== 'function') {
            window.showSettingsSaveSuccessToast = function(message) {
                if (typeof Swal === 'undefined') {
                    return;
                }
                Swal.fire({
                    icon: 'success',
                    text: message || @json(__('messages.updateSuccess')),
                    toast: true,
                    position: 'top-end',
                    timer: 2500,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    showClass: {
                        popup: 'swal2-noanimation',
                        backdrop: 'swal2-noanimation'
                    },
                });
            };
        }
    </script>
@endpush
