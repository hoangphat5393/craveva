r<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.updateTicketType')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="editTicketChannel" method="PUT" class="ajax-form">
            <div class="form-body">
                <div class="row">
                    <div class="col-lg-12">
                        <x-forms.text fieldId="channel_name" :fieldLabel="__('modules.tickets.ticketChannel')"
                            fieldName="channel_name" fieldRequired="true" :fieldPlaceholder="__('placeholders.tickets.ticketChannel')" :fieldValue="$channel->channel_name">
                        </x-forms.text>
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-ticket-channel" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    // save ticket channnel
    $('#save-ticket-channel').click(function () {
        $.easyBlockUI('#editTicketChannel');

        window.apiHttp.postUrlEncoded("{{route('ticketChannels.update', $channel->id)}}", $('#editTicketChannel').serialize())
            .then(function (response) {
                if(response.status == 'success'){
                    window.location.reload();
                }
            })
            .catch(function(err) {
                $.handleApiFormError(err);
            })
            .finally(function() {
                $.easyUnblockUI('#editTicketChannel');
            });
    });
</script>
