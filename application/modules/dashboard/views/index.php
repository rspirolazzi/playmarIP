<div id="content" class="btn-bigs">
    <?php echo $this->layout->load_view('layout/alerts'); ?>
    <div class="row <?php if (get_setting('disable_quickactions') == 1) echo 'hidden'; ?>">
        <div class="col-xs-6">

            <a href="<?php echo site_url('clients/form'); ?>" class="btn btn-default btn-lg btn-block">
                <i class="fa fa-user fa-margin"></i>
                <span class="hidden-xs"><?php _trans('add_client'); ?></span>
            </a>
        </div>
        <div class="col-xs-6">
            <a href="javascript:void(0)" class="create-quote btn btn-default  btn-lg btn-block">
                <i class="fa fa-file fa-margin"></i>
                <span class="hidden-xs"><?php _trans('create_quote'); ?></span>
            </a>
        </div>
        <div class="col-xs-6">
            <a href="javascript:void(0)" class="create-service btn btn-default  btn-lg btn-block">
                <i class="fa fa-file fa-margin"></i>
                <span class="hidden-xs"><?php _trans('create_service'); ?></span>
            </a>
        </div>
        <div class="col-xs-6">
            <?php if (get_setting('invoices_enabled', '1') == 1) : ?>
                <a href="javascript:void(0)" class="create-invoice btn btn-default  btn-lg btn-block">
                    <i class="fa fa-file-text fa-margin"></i>
                    <span class="hidden-xs"><?php _trans('create_invoice'); ?></span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
