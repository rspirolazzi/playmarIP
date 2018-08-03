<div class="table-responsive">
    <table id="item_table" class="items table table-condensed table-bordered no-margin">
        <thead style="display: none">
        <tr>
            <th></th>
            <th><?php _trans('item'); ?></th>
            <th><?php _trans('description'); ?></th>
            <th></th>
        </tr>
        </thead>

        <tbody id="new_row" style="display: none;">
        <tr>
            <td style="width:1%"><i class="fa fa-arrows cursor-move"></i></td>
            <td class="td-text">
                <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                <input type="hidden" name="item_id" value="">
                <input type="hidden" name="item_product_id" value="">

                <div class="input-group">
                    <span class="input-group-addon"><?php _trans('item'); ?></span>
                    <input type="text" name="item_name" class="input-sm form-control" value="">
                </div>
            </td>
            <td class="td-textarea">
                <div class="input-group">
                    <span class="input-group-addon"><?php _trans('description'); ?></span>
                    <textarea name="item_description" class="input-sm form-control"></textarea>
                </div>
            </td>
            <td class="td-icon text-right td-vert-middle"></td>
        </tr>
        </tbody>

        <?php foreach ($items as $item) { ?>
            <tbody class="item">
            <tr>
                <td><i class="fa fa-arrows cursor-move"></i></td>
                <td class="td-text">
                    <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                    <input type="hidden" name="item_id" value="<?php echo $item->item_id; ?>">
                    <input type="hidden" name="item_product_id" value="<?php echo $item->item_product_id; ?>">

                    <div class="input-group">
                        <span class="input-group-addon"><?php _trans('item'); ?></span>
                        <input type="text" name="item_name" class="input-sm form-control"
                               value="<?php _htmlsc($item->item_name); ?>">
                    </div>
                </td>
                <td class="td-textarea">
                    <div class="input-group">
                        <span class="input-group-addon"><?php _trans('description'); ?></span>
                        <textarea name="item_description" class="input-sm form-control"
                        ><?php echo htmlsc($item->item_description); ?></textarea>
                    </div>
                </td>

                <td class="td-icon text-right td-vert-middle">
                    <a href="<?php echo site_url('services/delete_item/' . $service->service_id . '/' . $item->item_id); ?>"
                       title="<?php _trans('delete'); ?>">
                        <i class="fa fa-trash-o text-danger"></i>
                    </a>
                </td>
            </tr>
            </tbody>
        <?php } ?>

    </table>
</div>

<br>

<div class="row">
    <div class="col-xs-12 col-md-4">
        <div class="btn-group">
            <a href="#" class="btn_add_row btn btn-sm btn-default">
                <i class="fa fa-plus"></i>
                <?php _trans('add_new_row'); ?>
            </a>
            <a href="#" class="btn_add_product btn btn-sm btn-default">
                <i class="fa fa-database"></i>
                <?php _trans('add_product'); ?>
            </a>
        </div>
    </div>

    <div class="col-xs-12 visible-xs visible-sm"><br></div>

    <div class="col-xs-12 col-md-6 col-md-offset-2 col-lg-4 col-lg-offset-4">
        <table class="table table-bordered text-right">
            <tr>
                <td style="width: 40%;"><?php _trans('subtotal'); ?></td>
                <td style="width: 60%;" class="amount"><?php echo format_currency($service->service_item_subtotal); ?></td>
            </tr>
            <tr>
                <td><?php _trans('item_tax'); ?></td>
                <td class="amount"><?php echo format_currency($service->service_item_tax_total); ?></td>
            </tr>
            <tr>
                <td><?php _trans('service_tax'); ?></td>
                <td>
                    <?php if ($service_tax_rates) {
                        foreach ($service_tax_rates as $service_tax_rate) { ?>
                            <span class="text-muted">
                            <?php echo anchor('services/delete_service_tax/' . $service->service_id . '/' . $service_tax_rate->service_tax_rate_id, '<i class="fa fa-trash-o"></i>');
                            echo ' ' . htmlsc($service_tax_rate->service_tax_rate_name) . ' ' . format_amount($service_tax_rate->service_tax_rate_percent); ?>
                                %</span>&nbsp;
                            <span class="amount">
                                <?php echo format_currency($service_tax_rate->service_tax_rate_amount); ?>
                            </span>
                        <?php }
                    } else {
                        echo format_currency('0');
                    } ?>
                </td>
            </tr>
            <tr>
                <td class="td-vert-middle"><?php _trans('discount'); ?></td>
                <td class="clearfix">
                    <div class="discount-field">
                        <div class="input-group input-group-sm">
                            <input id="service_discount_amount" name="service_discount_amount"
                                   class="discount-option form-control input-sm amount"
                                   value="<?php echo format_amount($service->service_discount_amount != 0 ? $service->service_discount_amount : ''); ?>">

                            <div
                                    class="input-group-addon"><?php echo get_setting('currency_symbol'); ?></div>
                        </div>
                    </div>
                    <div class="discount-field">
                        <div class="input-group input-group-sm">
                            <input id="service_discount_percent" name="service_discount_percent"
                                   value="<?php echo format_amount($service->service_discount_percent != 0 ? $service->service_discount_percent : ''); ?>"
                                   class="discount-option form-control input-sm amount">
                            <div class="input-group-addon">&percnt;</div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td><b><?php _trans('total'); ?></b></td>
                <td class="amount"><b><?php echo format_currency($service->service_total); ?></b></td>
            </tr>
        </table>
    </div>

</div>
