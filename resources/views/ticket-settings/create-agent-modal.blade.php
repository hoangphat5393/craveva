<div class="modal-header">
    <h5 class="modal-title">@lang('app.addNewTicketAgents')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="createMethods" method="POST" class="ajax-form">
            <div class="row">
                <div class="col-md-6">
                    <x-forms.select fieldId="agent_id" :fieldLabel="__('modules.tickets.chooseAgents')"
                        fieldName="user_id" search="true" fieldRequired="true">
                        @foreach ($employees as $emp)
                            <x-user-option :user="$emp" />
                        @endforeach
                    </x-forms.select>
                </div>
                <div class="col-md-6">
                    <x-forms.select fieldId="ticket_group_id" :fieldLabel="__('modules.tickets.assignGroup')"
                        fieldName="group_id[]" search="true" fieldRequired="true" multiple="true">
                        @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                        @endforeach
                    </x-forms.select>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-agent" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $(".select-picker").selectpicker();

    // save agent
    $('#save-agent').click(function() {
        $.easyBlockUI('#createMethods');

        window.apiHttp.postUrlEncoded("{{ route('ticket-agents.store') }}", $('#createMethods').serialize())
            .then(function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            })
            .catch(function(err) {
                $.handleApiFormError(err);
            })
            .finally(function() {
                $.easyUnblockUI('#createMethods');
            });
    });

    $('#manage-groups').click(function() {
        var url = "{{ route('ticket-groups.create') }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    var id = $('#agent_id').val();
    agentGroups(id);

    $(document).on('change', '#agent_id', function() {
        var agentId = $(this).val();
        agentGroups(agentId);
    });

        function agentGroups(agentId) {
            $.easyBlockUI('#createMethods');

            window.apiHttp.get("{{ route('ticket_agents.agent_groups') }}", {
                    params: {agent_id:agentId}
                })
                .then(function(response) {
                        var options = [];
                        var rData = [];
                        rData = response.data;
                        $.each(rData, function(index, value) {
                            var selectData = '';
                            selectData = '<option value="' + value.id + '">' +
                                value
                                .group_name + '</option>';
                                options.push(selectData);
                        });
                        $('#ticket_group_id').html(options);
                        $('#ticket_group_id').selectpicker('refresh');

                })
                .catch(function(err) {
                    $.handleApiFormError(err);
                })
                .finally(function() {
                    $.easyUnblockUI('#createMethods');
                });
        }

</script>
