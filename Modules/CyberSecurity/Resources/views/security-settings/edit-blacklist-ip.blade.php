<div class="modal-header">
    <h5 class="modal-title">@lang('cybersecurity::app.editBlacklistIp')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<x-form id="editCategory" method="POST" class="ajax-form">
    <div class="modal-body">
        <div class="portlet-body">
            <div class="row">

                <div class="col-sm-12">
                    <x-forms.text :fieldLabel="__('cybersecurity::app.blacklistIp')"
                                  fieldName="ip_address"
                                  fieldId="ip_address"
                                  fieldRequired="true"
                                  :fieldValue="$blacklistIp->ip_address"/>
                </div>

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="edit-category" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>


<script>
    $('#edit-category').click(function () {
        window.apiHttp.put(
            "{{ route('cybersecurity.blacklist-ip.update', $blacklistIp->id) }}",
            $('#editCategory').serialize(),
            {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
            }
        )
            .then(function (response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            })
            .catch(function (err) {
                $.handleApiFormError(err);
            });
    });
</script>
