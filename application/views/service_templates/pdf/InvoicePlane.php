<!DOCTYPE html>
<html lang="<?php _trans('cldr'); ?>">
<head>
    <meta charset="utf-8">
    <title><?php _trans('service'); ?></title>
    <link rel="stylesheet"
          href="<?php echo base_url(); ?>assets/<?php echo get_setting('system_theme', 'invoiceplane'); ?>/css/templates.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/core/css/custom-pdf.css">
</head>
<body>
<header class="clearfix">

    <div id="logo">
        <?php echo invoice_logo_pdf(); ?>
    </div>

    <div id="client">
        <div>
            <b><?php _htmlsc($service->client_name); ?></b>
        </div>
        <?php if ($service->client_vat_id) {
            echo '<div>' . trans('vat_id_short') . ': ' . $service->client_vat_id . '</div>';
        }
        if ($service->client_tax_code) {
            echo '<div>' . trans('tax_code_short') . ': ' . $service->client_tax_code . '</div>';
        }
        if ($service->client_address_1) {
            echo '<div>' . htmlsc($service->client_address_1) . '</div>';
        }
        if ($service->client_address_2) {
            echo '<div>' . htmlsc($service->client_address_2) . '</div>';
        }
        if ($service->client_city || $service->client_state || $service->client_zip) {
            echo '<div>';
            if ($service->client_city) {
                echo htmlsc($service->client_city) . ' ';
            }
            if ($service->client_state) {
                echo htmlsc($service->client_state) . ' ';
            }
            if ($service->client_zip) {
                echo htmlsc($service->client_zip);
            }
            echo '</div>';
        }
        if ($service->client_state) {
            echo '<div>' . htmlsc($service->client_state) . '</div>';
        }
        if ($service->client_country) {
            echo '<div>' . get_country_name(trans('cldr'), $service->client_country) . '</div>';
        }

        echo '<br/>';

        if ($service->client_phone) {
            echo '<div>' . trans('phone_abbr') . ': ' . htmlsc($service->client_phone) . '</div>';
        } ?>

    </div>
    <div id="company">
        <div><b><?php _htmlsc($service->user_name); ?></b></div>
        <?php if ($service->user_vat_id) {
            echo '<div>' . trans('vat_id_short') . ': ' . $service->user_vat_id . '</div>';
        }
        if ($service->user_tax_code) {
            echo '<div>' . trans('tax_code_short') . ': ' . $service->user_tax_code . '</div>';
        }
        if ($service->user_address_1) {
            echo '<div>' . htmlsc($service->user_address_1) . '</div>';
        }
        if ($service->user_address_2) {
            echo '<div>' . htmlsc($service->user_address_2) . '</div>';
        }
        if ($service->user_city || $service->user_state || $service->user_zip) {
            echo '<div>';
            if ($service->user_city) {
                echo htmlsc($service->user_city) . ' ';
            }
            if ($service->user_state) {
                echo htmlsc($service->user_state) . ' ';
            }
            if ($service->user_zip) {
                echo htmlsc($service->user_zip);
            }
            echo '</div>';
        }
        if ($service->user_country) {
            echo '<div>' . get_country_name(trans('cldr'), $service->user_country) . '</div>';
        }

        echo '<br/>';

        if ($service->user_phone) {
            echo '<div>' . trans('phone_abbr') . ': ' . htmlsc($service->user_phone) . '</div>';
        }
        if ($service->user_fax) {
            echo '<div>' . trans('fax_abbr') . ': ' . htmlsc($service->user_fax) . '</div>';
        }
        ?>
    </div>

</header>

<main>

    <div class="invoice-details clearfix">
        <table>
            <tr>
                <td><?php echo trans('service_date') . ':'; ?></td>
                <td><?php echo date_from_mysql($service->service_date_created, true); ?></td>
            </tr>
            <tr>
                <td><?php echo trans('expires') . ': '; ?></td>
                <td><?php echo date_from_mysql($service->service_date_expires, true); ?></td>
            </tr>
            <tr>
                <td><?php echo trans('total') . ': '; ?></td>
                <td><?php echo format_currency($service->service_total); ?></td>
            </tr>
        </table>
    </div>

    <h1 class="invoice-title"><?php echo trans('service') . ' ' . $service->service_number; ?></h1>

    <table class="item-table">
        <thead>
        <tr>
            <th class="item-name"><?php _trans('item'); ?></th>
            <th class="item-desc"><?php _trans('description'); ?></th>
            <th class="item-amount text-right"><?php _trans('qty'); ?></th>
            <th class="item-price text-right"><?php _trans('price'); ?></th>
            <?php if ($show_item_discounts) : ?>
                <th class="item-discount text-right"><?php _trans('discount'); ?></th>
            <?php endif; ?>
            <th class="item-total text-right"><?php _trans('total'); ?></th>
        </tr>
        </thead>
        <tbody>

        <?php
        foreach ($items as $item) { ?>
            <tr>
                <td><?php _htmlsc($item->item_name); ?></td>
                <td><?php echo nl2br(htmlsc($item->item_description)); ?></td>
                <td class="text-right">
                    <?php echo format_amount($item->item_quantity); ?>
                    <?php if ($item->item_product_unit) : ?>
                        <br>
                        <small><?php _htmlsc($item->item_product_unit); ?></small>
                    <?php endif; ?>
                </td>
                <td class="text-right">
                    <?php echo format_currency($item->item_price); ?>
                </td>
                <?php if ($show_item_discounts) : ?>
                    <td class="text-right">
                        <?php echo format_currency($item->item_discount); ?>
                    </td>
                <?php endif; ?>
                <td class="text-right">
                    <?php echo format_currency($item->item_total); ?>
                </td>
            </tr>
        <?php } ?>

        </tbody>
        <tbody class="invoice-sums">

        <tr>
            <td <?php echo($show_item_discounts ? 'colspan="5"' : 'colspan="4"'); ?>
                    class="text-right"><?php _trans('subtotal'); ?></td>
            <td class="text-right"><?php echo format_currency($service->service_item_subtotal); ?></td>
        </tr>

        <?php if ($service->service_item_tax_total > 0) { ?>
            <tr>
                <td <?php echo($show_item_discounts ? 'colspan="5"' : 'colspan="4"'); ?> class="text-right">
                    <?php _trans('item_tax'); ?>
                </td>
                <td class="text-right">
                    <?php echo format_currency($service->service_item_tax_total); ?>
                </td>
            </tr>
        <?php } ?>

        <?php foreach ($service_tax_rates as $service_tax_rate) : ?>
            <tr>
                <td <?php echo($show_item_discounts ? 'colspan="5"' : 'colspan="4"'); ?> class="text-right">
                    <?php echo $service_tax_rate->service_tax_rate_name . ' (' . format_amount($service_tax_rate->service_tax_rate_percent) . '%)'; ?>
                </td>
                <td class="text-right">
                    <?php echo format_currency($service_tax_rate->service_tax_rate_amount); ?>
                </td>
            </tr>
        <?php endforeach ?>

        <?php if ($service->service_discount_percent != '0.00') : ?>
            <tr>
                <td <?php echo($show_item_discounts ? 'colspan="5"' : 'colspan="4"'); ?> class="text-right">
                    <?php _trans('discount'); ?>
                </td>
                <td class="text-right">
                    <?php echo format_amount($service->service_discount_percent); ?>%
                </td>
            </tr>
        <?php endif; ?>
        <?php if ($service->service_discount_amount != '0.00') : ?>
            <tr>
                <td <?php echo($show_item_discounts ? 'colspan="5"' : 'colspan="4"'); ?> class="text-right">
                    <?php _trans('discount'); ?>
                </td>
                <td class="text-right">
                    <?php echo format_currency($service->service_discount_amount); ?>
                </td>
            </tr>
        <?php endif; ?>

        <tr>
            <td <?php echo($show_item_discounts ? 'colspan="5"' : 'colspan="4"'); ?> class="text-right">
                <b><?php _trans('total'); ?></b>
            </td>
            <td class="text-right">
                <b><?php echo format_currency($service->service_total); ?></b>
            </td>
        </tr>
        </tbody>
    </table>

</main>

<footer>
    <?php if ($service->notes) : ?>
        <div class="notes">
            <b><?php _trans('notes'); ?></b><br/>
            <?php echo nl2br(htmlsc($service->notes)); ?>
        </div>
    <?php endif; ?>
</footer>

</body>
</html>
