<div id="headerbar">
    <h1 class="headerbar-title"><?php _trans('service'); ?> #<?php echo $service->service_number; ?></h1>

    <div class="pull-right">
        <div class="btn-group btn-group-sm">
            <?php if (in_array($service->service_status_id, array(2, 3))) { ?>
                <a href="<?php echo site_url('guest/services/approve/' . $service->service_id); ?>"
                   class="btn btn-success">
                    <i class="fa fa-check"></i>
                    <?php _trans('approve_this_service'); ?>
                </a>
                <a href="<?php echo site_url('guest/services/reject/' . $service->service_id); ?>"
                   class="btn btn-danger">
                    <i class="fa fa-times-circle"></i>
                    <?php _trans('reject_this_service'); ?>
                </a>
            <?php } elseif ($service->service_status_id == 4) { ?>
                <a href="#" class="btn btn-success disabled">
                    <i class="fa fa-check"></i>
                    <?php _trans('service_approved'); ?>
                </a>
            <?php } elseif ($service->service_status_id == 5) { ?>
                <a href="#" class="btn btn-danger disabled">
                    <i class="fa fa-times-circle"></i>
                    <?php _trans('service_rejected'); ?>
                </a>
            <?php } ?>

            <a href="<?php echo site_url('guest/services/generate_pdf/' . $service_id); ?>"
               class="btn btn-default" id="btn_generate_pdf">
                <i class="fa fa-print"></i> <?php _trans('download_pdf'); ?>
            </a>
        </div>

    </div>

</div>

<div id="content">

    <?php echo $this->layout->load_view('layout/alerts'); ?>

    <div class="service">

        <div class="row">
            <div class="col-xs-12 col-md-9">

                <h2><?php echo format_client($service); ?></h2><br>
                <div class="client-address">
                    <?php $this->layout->load_view('clients/partial_client_address', array('client' => $service)); ?>
                </div>
                <br><br>
                <?php if ($service->client_phone) { ?>
                    <span><strong><?php _trans('phone'); ?>:</strong> <?php _htmlsc($service->client_phone); ?></span>
                    <br>
                <?php } ?>
                <?php if ($service->client_email) { ?>
                    <span><strong><?php _trans('email'); ?>:</strong> <?php _htmlsc($service->client_email); ?></span>
                <?php } ?>

            </div>

            <div class="col-xs-12 col-md-3">

                <table class="table table-bordered">
                    <tr>
                        <td><?php _trans('service'); ?> #</td>
                        <td><?php echo $service->service_number; ?></td>
                    </tr>
                    <tr>
                        <td><?php _trans('date'); ?></td>
                        <td><?php echo date_from_mysql($service->service_date_created); ?></td>
                    </tr>
                    <tr>
                        <td><?php _trans('due_date'); ?></td>
                        <td><?php echo date_from_mysql($service->service_date_expires); ?></td>
                    </tr>
                </table>

            </div>

        </div>

        <br/>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th style="width:20px;"></th>
                    <th><?php _trans('item'); ?> / <?php echo lang('description'); ?></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <?php
                $i = 1;
                foreach ($items as $item) { ?>
                    <tbody>
                    <tr>
                        <td rowspan="2" style="width:20px;" class="text-center">
                            <?php echo $i;
                            $i++; ?>
                        </td>
                        <td><?php _htmlsc($item->item_name); ?></td>
                        <td>
                            <span class="pull-left"><?php _trans('quantity'); ?></span>
                            <span class="pull-right amount"><?php echo $item->item_quantity; ?></span>
                        </td>
                        <td>
                            <span class="pull-left"><?php _trans('discount'); ?></span>
                            <span class="pull-right amount"><?php echo format_currency($item->item_discount); ?></span>
                        </td>
                        <td>
                            <span class="pull-left"><?php _trans('subtotal'); ?></span>
                            <span class="pull-right amount"><?php echo format_currency($item->item_subtotal); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted"><?php echo nl2br(htmlsc($item->item_description)); ?></td>
                        <td>
                            <span class="pull-left"><?php _trans('price'); ?></span>
                            <span class="pull-right amount"><?php format_amount($item->item_price); ?></span>
                        </td>
                        <td>
                            <span class="pull-left"><?php _trans('tax'); ?></span>
                            <span class="pull-right amount"><?php echo format_amount($item->item_tax_total); ?></span>
                        </td>
                        <td>
                            <span class="pull-left"><?php _trans('total'); ?></span>
                            <span class="pull-right amount"><?php echo format_currency($item->item_total); ?></span>
                        </td>
                    </tr>
                    </tbody>
                <?php } ?>

            </table>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="text-right"><?php _trans('subtotal'); ?></th>
                    <th class="text-right"><?php _trans('item_tax'); ?></th>
                    <th class="text-right"><?php _trans('service_tax'); ?></th>
                    <th class="text-right"><?php _trans('discount'); ?></th>
                    <th class="text-right"><?php _trans('total'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="amount"><?php echo format_currency($service->service_item_subtotal); ?></td>
                    <td class="amount"><?php echo format_currency($service->service_item_tax_total); ?></td>
                    <td class="amount">
                        <?php if ($service_tax_rates) {
                            foreach ($service_tax_rates as $service_tax_rate) { ?>
                                <?php echo $service_tax_rate->service_tax_rate_name . ' ' . $service_tax_rate->service_tax_rate_percent; ?>%:
                                <?php echo format_currency($service_tax_rate->service_tax_rate_amount); ?><br/>
                            <?php }
                        } else {
                            echo format_currency('0');
                        } ?>
                    </td>
                    <td class="amount"><?php
                        if ($service->service_discount_percent == floatval(0)) {
                            echo $service->service_discount_percent . '%';
                        } else {
                            echo format_currency($service->service_discount_amount);
                        }
                        ?>
                    </td>
                    <td class="amount"><b><?php echo format_currency($service->service_total); ?></b></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
