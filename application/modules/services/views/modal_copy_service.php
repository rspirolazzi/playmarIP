<script>
    $(function () {
        $('#modal_copy_service').modal('show');

        // Select2 for all select inputs
        $(".simple-select").select2();

        <?php $this->layout->load_view('clients/script_select2_client_id.js'); ?>

        // Creates the service
        $('#copy_service_confirm').click(function () {
            $.post("<?php echo site_url('services/ajax/copy_service'); ?>", {
                    service_id: <?php echo $service_id; ?>,
                    client_id: $('#create_service_client_id').val(),
                    service_date_created: $('#service_date_created').val(),
                    invoice_group_id: $('#invoice_group_id').val(),
                    user_id: $('#user_id').val()
                },
                function (data) {
                    <?php echo(IP_DEBUG ? 'console.log(data);' : ''); ?>
                    var response = JSON.parse(data);
                    if (response.success === 1) {
                        window.location = "<?php echo site_url('services/view'); ?>/" + response.service_id;
                    }
                    else {
                        // The validation was not successful
                        $('.control-group').removeClass('has-error');
                        for (var key in response.validation_errors) {
                            $('#' + key).parent().parent().addClass('has-error');
                        }
                    }
                });
        });
    });

</script>

<div id="modal_copy_service" class="modal modal-lg" role="dialog" aria-labelledby="modal_copy_service" aria-hidden="true">
    <form class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><i class="fa fa-close"></i></button>
            <h4 class="panel-title"><?php _trans('copy_service'); ?></h4>
        </div>
        <div class="modal-body">

            <input type="hidden" name="user_id" id="user_id" value="<?php echo $service->user_id; ?>">

            <div class="form-group">
                <label for="create_service_client_id"><?php _trans('client'); ?></label>
                <select name="client_id" id="create_service_client_id" class="client-id-select form-control"
                        autofocus="autofocus">
                    <?php if (!empty($client)) : ?>
                        <option value="<?php echo $client->client_id; ?>"><?php _htmlsc(format_client($client)); ?></option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group has-feedback">
                <label for="service_date_created">
                    <?php _trans('service_date'); ?>
                </label>

                <div class="input-group">
                    <input name="service_date_created" id="service_date_created"
                           class="form-control datepicker"
                           value="<?php echo date_from_mysql($service->service_date_created, true); ?>">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar fa-fw"></i>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="invoice_group_id">
                    <?php _trans('invoice_group'); ?>
                </label>
                <select name="invoice_group_id" id="invoice_group_id" class="form-control simple-select">
                    <?php foreach ($invoice_groups as $invoice_group) { ?>
                        <option value="<?php echo $invoice_group->invoice_group_id; ?>"
                            <?php echo get_setting('default_service_group') != $invoice_group->invoice_group_id ? '' : 'selected="selected"' ?>>
                            <?php _htmlsc($invoice_group->invoice_group_name); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

        </div>

        <div class="modal-footer">
            <div class="btn-group">
                <button class="btn btn-success" id="copy_service_confirm" type="button">
                    <i class="fa fa-check"></i> <?php _trans('submit'); ?>
                </button>
                <button class="btn btn-danger" type="button" data-dismiss="modal">
                    <i class="fa fa-times"></i> <?php _trans('cancel'); ?>
                </button>
            </div>
        </div>

    </form>

</div>
