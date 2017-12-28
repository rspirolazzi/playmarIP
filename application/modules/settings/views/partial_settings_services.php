<div class="col-xs-12 col-md-8 col-md-offset-2">

    <div class="panel panel-default">
        <div class="panel-heading">
            <?php _trans('general_settings'); ?>
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-xs-12 col-md-6">

                    <div class="form-group">
                        <label for="settings[services_expire_after]">
                            <?php _trans('services_expire_after'); ?>
                        </label>
                        <input type="number" name="settings[services_expire_after]" id="settings[services_expire_after]"
                               class="form-control"
                               value="<?php echo get_setting('services_expire_after'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="settings[default_service_group]">
                            <?php _trans('default_service_group'); ?>
                        </label>
                        <select name="settings[default_service_group]" id="settings[default_service_group]"
                                class="form-control simple-select">
                            <option value=""><?php _trans('none'); ?></option>
                            <?php foreach ($invoice_groups as $invoice_group) { ?>
                                <option value="<?php echo $invoice_group->invoice_group_id; ?>"
                                    <?php check_select(get_setting('default_service_group'), $invoice_group->invoice_group_id); ?>>
                                    <?php echo $invoice_group->invoice_group_name; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="settings[mark_services_sent_pdf]">
                            <?php _trans('mark_services_sent_pdf'); ?>
                        </label>
                        <select name="settings[mark_services_sent_pdf]" id="settings[mark_services_sent_pdf]"
                                class="form-control simple-select">
                            <option value="0">
                                <?php _trans('no'); ?>
                            </option>
                            <option value="1" <?php check_select(get_setting('mark_services_sent_pdf'), '1'); ?>>
                                <?php _trans('yes'); ?>
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="settings[default_service_notes]">
                            <?php _trans('default_notes'); ?>
                        </label>
                        <textarea name="settings[default_service_notes]" id="settings[default_service_notes]" rows="3"
                                  class="form-control"><?php echo get_setting('default_service_notes', '', true); ?></textarea>
                    </div>

                </div>
                <div class="col-xs-12 col-md-6">

                    <div class="form-group">
                        <label for="settings[service_pre_password]">
                            <?php _trans('service_pre_password'); ?>
                        </label>
                        <input type="text" name="settings[service_pre_password]" id="settings[service_pre_password]"
                               class="form-control" value="<?php echo get_setting('service_pre_password', '', true); ?>">
                    </div>

                    <div class="form-group">
                        <label for="settings[generate_service_number_for_draft]">
                            <?php _trans('generate_service_number_for_draft'); ?>
                        </label>
                        <select name="settings[generate_service_number_for_draft]" class="form-control simple-select"
                                id="settings[generate_service_number_for_draft]">
                            <option value="0">
                                <?php _trans('no'); ?>
                            </option>
                            <option value="1" <?php check_select(get_setting('generate_service_number_for_draft'), '1'); ?>>
                                <?php _trans('yes'); ?>
                            </option>
                        </select>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <?php _trans('service_templates'); ?>
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-xs-12 col-md-6">

                    <div class="form-group">
                        <label for="settings[pdf_service_template]">
                            <?php _trans('default_pdf_template'); ?>
                        </label>
                        <select name="settings[pdf_service_template]" id="settings[pdf_service_template]"
                                class="form-control simple-select">
                            <option value=""><?php _trans('none'); ?></option>
                            <?php foreach ($pdf_quote_templates as $service_template) { ?>
                                <option value="<?php echo $service_template; ?>"
                                    <?php check_select(get_setting('pdf_service_template'), $service_template); ?>>
                                    <?php echo $service_template; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="settings[public_service_template]">
                            <?php _trans('default_public_template'); ?>
                        </label>
                        <select name="settings[public_service_template]" id="settings[public_service_template]"
                                class="form-control simple-select">
                            <option value=""><?php _trans('none'); ?></option>
                            <?php foreach ($public_quote_templates as $service_template) { ?>
                                <option value="<?php echo $service_template; ?>"
                                    <?php check_select(get_setting('public_service_template'), $service_template); ?>>
                                    <?php echo $service_template; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                </div>
                <div class="col-xs-12 col-md-6">

                    <div class="form-group">
                        <label for="settings[email_service_template]">
                            <?php _trans('default_email_template'); ?>
                        </label>
                        <select name="settings[email_service_template]" id="settings[email_service_template]"
                                class="form-control simple-select">
                            <option value=""><?php _trans('none'); ?></option>
                            <?php foreach ($email_templates_quote as $email_template) { ?>
                                <option value="<?php echo $email_template->email_template_id; ?>"
                                    <?php check_select(get_setting('email_service_template'), $email_template->email_template_id); ?>>
                                    <?php echo $email_template->email_template_title; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>
