<script>
    $('body').on('click', '#save-form', function() {
        window.apiHttp.postUrlEncoded("{{ route('einvoice.settings.save') }}", $('#editSettings').serialize())
            .then(function(response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            })
            .catch(function (err) {
                $.handleApiFormError(err);
            });
    });
</script>
