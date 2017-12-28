<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * InvoicePlane
 *
 * @author		InvoicePlane Developers & Contributors
 * @copyright	Copyright (c) 2012 - 2017 InvoicePlane.com
 * @license		https://invoiceplane.com/license.txt
 * @link		https://invoiceplane.com
 */

/**
 * Class Services
 */
class Services extends Admin_Controller
{
    /**
     * Services constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('mdl_services');
    }

    public function index()
    {
        // Display all services by default
        redirect('services/status/all');
    }

    /**
     * @param string $status
     * @param int $page
     */
    public function status($status = 'all', $page = 0)
    {
        // Determine which group of services to load
        switch ($status) {
            case 'draft':
                $this->mdl_services->is_draft();
                break;
            case 'sent':
                $this->mdl_services->is_sent();
                break;
            case 'approved':
                $this->mdl_services->is_approved();
                break;
            case 'rejected':
                $this->mdl_services->is_rejected();
                break;
            case 'canceled':
                $this->mdl_services->is_canceled();
                break;
        }

        $this->mdl_services->paginate(site_url('services/status/' . $status), $page);
        $services = $this->mdl_services->result();

        $this->layout->set(
            array(
                'services' => $services,
                'status' => $status,
                'filter_display' => true,
                'filter_placeholder' => trans('filter_services'),
                'filter_method' => 'filter_services',
                'service_statuses' => $this->mdl_services->statuses()
            )
        );

        $this->layout->buffer('content', 'services/index');
        $this->layout->render();
    }

    /**
     * @param $service_id
     */
    public function view($service_id)
    {
        $this->load->helper('custom_values');
        $this->load->model('mdl_service_items');
        $this->load->model('tax_rates/mdl_tax_rates');
        $this->load->model('units/mdl_units');
        $this->load->model('mdl_service_tax_rates');
        $this->load->model('custom_fields/mdl_custom_fields');
        $this->load->model('custom_values/mdl_custom_values');
        $this->load->model('custom_fields/mdl_service_custom');

        $fields = $this->mdl_service_custom->by_id($service_id)->get()->result();
        $this->db->reset_query();

        $service_custom = $this->mdl_service_custom->where('service_id', $service_id)->get();

        if ($service_custom->num_rows()) {
            $service_custom = $service_custom->row();

            unset($service_custom->service_id, $service_custom->service_custom_id);

            foreach ($service_custom as $key => $val) {
                $this->mdl_services->set_form_value('custom[' . $key . ']', $val);
            }
        }

        $service = $this->mdl_services->get_by_id($service_id);


        if (!$service) {
            show_404();
        }

        $custom_fields = $this->mdl_custom_fields->by_table('ip_service_custom')->get()->result();
        $custom_values = [];
        foreach ($custom_fields as $custom_field) {
            if (in_array($custom_field->custom_field_type, $this->mdl_custom_values->custom_value_fields())) {
                $values = $this->mdl_custom_values->get_by_fid($custom_field->custom_field_id)->result();
                $custom_values[$custom_field->custom_field_id] = $values;
            }
        }

        foreach ($custom_fields as $cfield) {
            foreach ($fields as $fvalue) {
                if ($fvalue->service_custom_fieldid == $cfield->custom_field_id) {
                    // TODO: Hackish, may need a better optimization
                    $this->mdl_services->set_form_value(
                        'custom[' . $cfield->custom_field_id . ']',
                        $fvalue->service_custom_fieldvalue
                    );
                    break;
                }
            }
        }

        $this->layout->set(
            array(
                'service' => $service,
                'items' => $this->mdl_service_items->where('service_id', $service_id)->get()->result(),
                'service_id' => $service_id,
                'tax_rates' => $this->mdl_tax_rates->get()->result(),
                'units' => $this->mdl_units->get()->result(),
                'service_tax_rates' => $this->mdl_service_tax_rates->where('service_id', $service_id)->get()->result(),
                'custom_fields' => $custom_fields,
                'custom_values' => $custom_values,
                'custom_js_vars' => array(
                    'currency_symbol' => get_setting('currency_symbol'),
                    'currency_symbol_placement' => get_setting('currency_symbol_placement'),
                    'decimal_point' => get_setting('decimal_point')
                ),
                'service_statuses' => $this->mdl_services->statuses()
            )
        );

        $this->layout->buffer(
            array(
                array('modal_delete_service', 'services/modal_delete_service'),
                array('modal_add_service_tax', 'services/modal_add_service_tax'),
                array('content', 'services/view')
            )
        );

        $this->layout->render();
    }

    /**
     * @param $service_id
     */
    public function delete($service_id)
    {
        // Delete the service
        $this->mdl_services->delete($service_id);

        // Redirect to service index
        redirect('services/index');
    }

    /**
     * @param $service_id
     * @param $item_id
     */
    public function delete_item($service_id, $item_id)
    {
        // Delete service item
        $this->load->model('mdl_service_items');
        $this->mdl_service_items->delete($item_id);

        // Redirect to service view
        redirect('services/view/' . $service_id);
    }

    /**
     * @param $service_id
     * @param bool $stream
     * @param null $service_template
     */
    public function generate_pdf($service_id, $stream = true, $service_template = null)
    {
        $this->load->helper('pdf');

        if (get_setting('mark_services_sent_pdf') == 1) {
            $this->mdl_services->mark_sent($service_id);
        }

        generate_service_pdf($service_id, $stream, $service_template);
    }

    /**
     * @param $service_id
     * @param $service_tax_rate_id
     */
    public function delete_service_tax($service_id, $service_tax_rate_id)
    {
        $this->load->model('mdl_service_tax_rates');
        $this->mdl_service_tax_rates->delete($service_tax_rate_id);

        $this->load->model('mdl_service_amounts');
        $this->mdl_service_amounts->calculate($service_id);

        redirect('services/view/' . $service_id);
    }

    public function recalculate_all_services()
    {
        $this->db->select('service_id');
        $service_ids = $this->db->get('ip_services')->result();

        $this->load->model('mdl_service_amounts');

        foreach ($service_ids as $service_id) {
            $this->mdl_service_amounts->calculate($service_id->service_id);
        }
    }

}
