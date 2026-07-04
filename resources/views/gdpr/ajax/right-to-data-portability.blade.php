<div class="col-lg-12 col-md-12 w-100 p-4 ">
    <div class="row">
        <div class="col-lg-4">
            {{-- <x-forms.button-primary id="save-right-to-data-portability" icon="check">Export Data</x-forms.button-primary> --}}
            <a href="{{ route('gdpr.export_data') }}" class="btn-primary rounded f-15">Export Data</a>
        </div>
    </div>
</div>

<script>
    $(body).on('click', '#save-right-to-data-portability', function() {
        $('#save-right-to-data-portability').prop('disabled', true);

        window.apiHttp.get("{{ route('gdpr.export_data') }}")
            .then(function(response) {
                if (response.status == "success") {
                    location.reload();
                }
            })
            .catch(function(error) {
                $.handleApiFormError(error);
            })
            .finally(function() {
                $('#save-right-to-data-portability').prop('disabled', false);
            });
    })
</script>
