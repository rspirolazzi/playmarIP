<div class="table-responsive">
    <table class="table table-striped">

        <thead>
        <tr>
            <th><?php _trans('status'); ?></th>
            <th><?php _trans('service'); ?></th>
            <th><?php _trans('created'); ?></th>
            <th><?php _trans('due_date'); ?></th>
            <th><?php _trans('client_name'); ?></th>
            <th style="text-align: right; padding-right: 25px;"><?php _trans('amount'); ?></th>
            <th><?php _trans('options'); ?></th>
        </tr>
        </thead>

        <tbody>
        <?php
        $service_idx = 1;
        $service_count = count($services);
        $service_list_split = $service_count > 3 ? $service_count / 2 : 9999;

        foreach ($services as $service) {
            // Convert the dropdown menu to a dropup if service is after the invoice split
            $dropup = $service_idx > $service_list_split ? true : false;
            ?>
            <tr>
                <td>
                    <span class="label <?php echo $service_statuses[$service->service_status_id]['class']; ?>">
                        <?php echo $service_statuses[$service->service_status_id]['label']; ?>
                    </span>
                </td>
                <td>
                    <a href="<?php echo site_url('services/view/' . $service->service_id); ?>"
                       title="<?php _trans('edit'); ?>">
                        <?php echo($service->service_number ? $service->service_number : $service->service_id); ?>
                    </a>
                </td>
                <td>
                    <?php echo date_from_mysql($service->service_date_created); ?>
                </td>
                <td>
                    <?php echo date_from_mysql($service->service_date_expires); ?>
                </td>
                <td>
                    <a href="<?php echo site_url('clients/view/' . $service->client_id); ?>"
                       title="<?php _trans('view_client'); ?>">
                        <?php _htmlsc(format_client($service)); ?>
                    </a>
                </td>
                <td style="text-align: right; padding-right: 25px;">
                    <?php echo format_currency($service->service_total); ?>
                </td>
                <td>
                    <div class="options btn-group<?php echo $dropup ? ' dropup' : ''; ?>">
                        <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown"
                           href="#">
                            <i class="fa fa-cog"></i> <?php _trans('options'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="<?php echo site_url('services/view/' . $service->service_id); ?>">
                                    <i class="fa fa-edit fa-margin"></i> <?php _trans('edit'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo site_url('services/generate_pdf/' . $service->service_id); ?>"
                                   target="_blank">
                                    <i class="fa fa-print fa-margin"></i> <?php _trans('download_pdf'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo site_url('mailer/service/' . $service->service_id); ?>">
                                    <i class="fa fa-send fa-margin"></i> <?php _trans('send_email'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo site_url('services/delete/' . $service->service_id); ?>"
                                   onclick="return confirm('<?php _trans('delete_service_warning'); ?>');">
                                    <i class="fa fa-trash-o fa-margin"></i> <?php _trans('delete'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            <?php
            $service_idx++;
        } ?>
        </tbody>

    </table>
</div>
