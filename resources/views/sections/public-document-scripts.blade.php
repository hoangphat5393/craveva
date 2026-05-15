<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/sweetalert/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('js/main.js') }}"></script>
<script src="{{ asset('js/custom.js') }}"></script>
<script src="{{ asset('vendor/helper/helper.js') }}"></script>

<script>
    if (typeof document.loading === 'undefined') {
        document.loading = @json(__('app.loading'));
    }

    function initPublicDocumentPage() {
        if (typeof init === 'function' && typeof $.fn.selectpicker === 'function') {
            init();

            return;
        }

        if (typeof dropifyMessages !== 'undefined' && typeof $.fn.dropify === 'function') {
            $('.dropify').dropify({
                messages: dropifyMessages,
            });
        }
    }

    $(window).on('load', function() {
        initPublicDocumentPage();
        $('.preloader-container').fadeOut('slow', function() {
            $(this).removeClass('d-flex');
        });
    });
</script>
