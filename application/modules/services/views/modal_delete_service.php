<script>
    $(function () {
        $('#modal_delete_service_confirm').click(function () {
            service_id = $(this).data('service-id');
            window.location = '<?php echo site_url('services/delete'); ?>/' + service_id;
        });
    });
</script>

<div id="delete-service" class="modal modal-lg" role="dialog" aria-labelledby="modal_delete_service" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><i class="fa fa-close"></i></button>
            <h4 class="panel-title"><?php _trans('delete_service'); ?></h4>
        </div>
        <div class="modal-body">

            <div class="alert alert-danger"><?php _trans('delete_service_warning'); ?></div>

        </div>
        <div class="modal-footer">
            <div class="btn-group">
                <button id="modal_delete_service_confirm" class="btn btn-danger"
                        data-service-id="<?php echo $service->service_id; ?>">
                    <i class="fa fa-trash-o"></i> <?php _trans('yes'); ?>
                </button>
                <button class="btn btn-success" data-dismiss="modal">
                    <i class="fa fa-times"></i> <?php _trans('no'); ?>
                </button>
            </div>
        </div>
    </div>

</div>
