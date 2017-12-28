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
 * Class Ajax
 */
class Ajax extends Admin_Controller
{
    public $ajax_controller = true;

    public function save()
    {
        $this->load->model('services/mdl_service_items');
        $this->load->model('services/mdl_services');
        $this->load->model('units/mdl_units');

        $service_id = $this->input->post('service_id');

        $this->mdl_services->set_id($service_id);

        if ($this->mdl_services->run_validation('validation_rules_save_service')) {
            $items = json_decode($this->input->post('items'));

            foreach ($items as $item) {
                if ($item->item_name) {
                    $item->item_quantity = ($item->item_quantity ? standardize_amount($item->item_quantity) : floatval(0));
                    $item->item_price = ($item->item_quantity ? standardize_amount($item->item_price) : floatval(0));
                    $item->item_discount_amount = ($item->item_discount_amount) ? standardize_amount($item->item_discount_amount) : null;
                    $item->item_product_id = ($item->item_product_id ? $item->item_product_id : null);
                    $item->item_product_unit_id = ($item->item_product_unit_id ? $item->item_product_unit_id : null);
                    $item->item_product_unit = $this->mdl_units->get_name($item->item_product_unit_id, $item->item_quantity);

                    $item_id = ($item->item_id) ?: null;
                    unset($item->item_id);

                    $this->mdl_service_items->save($item_id, $item);
                }
            }

            if ($this->input->post('service_discount_amount') === '') {
                $service_discount_amount = floatval(0);
            } else {
                $service_discount_amount = $this->input->post('service_discount_amount');
            }

            if ($this->input->post('service_discount_percent') === '') {
                $service_discount_percent = floatval(0);
            } else {
                $service_discount_percent = $this->input->post('service_discount_percent');
            }

            // Generate new service number if needed
            $service_number = $this->input->post('service_number');
            $service_status_id = $this->input->post('service_status_id');

            if (empty($service_number) && $service_status_id != 1) {
                $service_group_id = $this->mdl_services->get_invoice_group_id($service_id);
                $service_number = $this->mdl_services->get_service_number($service_group_id);
            }

            $db_array = array(
                'service_number' => $service_number,
                'service_date_created' => date_to_mysql($this->input->post('service_date_created')),
                'service_date_expires' => date_to_mysql($this->input->post('service_date_expires')),
                'service_status_id' => $service_status_id,
                'notes' => $this->input->post('notes'),
                'service_discount_amount' => standardize_amount($service_discount_amount),
                'service_discount_percent' => standardize_amount($service_discount_percent),
            );

            $this->mdl_services->save($service_id, $db_array);

            // Recalculate for discounts
            $this->load->model('services/mdl_service_amounts');
            $this->mdl_service_amounts->calculate($service_id);

            $response = array(
                'success' => 1
            );
        } else {
            $this->load->helper('json_error');
            $response = array(
                'success' => 0,
                'validation_errors' => json_errors()
            );
        }


        // Save all custom fields
        if ($this->input->post('custom')) {
            $db_array = array();

            $values = [];
            foreach ($this->input->post('custom') as $custom) {
                if (preg_match("/^(.*)\[\]$/i", $custom['name'], $matches)) {
                    $values[$matches[1]][] = $custom['value'];
                } else {
                    $values[$custom['name']] = $custom['value'];
                }
            }

            foreach ($values as $key => $value) {
                preg_match("/^custom\[(.*?)\](?:\[\]|)$/", $key, $matches);
                if ($matches) {
                    $db_array[$matches[1]] = $value;
                }
            }
            $this->load->model('custom_fields/mdl_service_custom');
            $result = $this->mdl_service_custom->save_custom($service_id, $db_array);
            if ($result !== true) {
                $response = array(
                    'success' => 0,
                    'validation_errors' => $result
                );

                echo json_encode($response);
                exit;
            }
        }

        echo json_encode($response);
    }

    public function save_service_tax_rate()
    {
        $this->load->model('services/mdl_service_tax_rates');

        if ($this->mdl_service_tax_rates->run_validation()) {
            $this->mdl_service_tax_rates->save();

            $response = array(
                'success' => 1
            );
        } else {
            $response = array(
                'success' => 0,
                'validation_errors' => $this->mdl_service_tax_rates->validation_errors
            );
        }

        echo json_encode($response);
    }

    public function create()
    {
        $this->load->model('services/mdl_services');

        if ($this->mdl_services->run_validation()) {
            $service_id = $this->mdl_services->create();

            $response = array(
                'success' => 1,
                'service_id' => $service_id
            );
        } else {
            $this->load->helper('json_error');
            $response = array(
                'success' => 0,
                'validation_errors' => json_errors()
            );
        }

        echo json_encode($response);
    }

    public function modal_change_client()
    {
        $this->load->module('layout');
        $this->load->model('clients/mdl_clients');

        $data = array(
            'client_id' => $this->input->post('client_id'),
            'service_id' => $this->input->post('service_id'),
            'clients' => $this->mdl_clients->get_latest(),
        );

        $this->layout->load_view('services/modal_change_client', $data);
    }

    public function change_client()
    {
        $this->load->model('services/mdl_services');
        $this->load->model('clients/mdl_clients');

        // Get the client ID
        $client_id = $this->input->post('client_id');
        $client = $this->mdl_clients->where('ip_clients.client_id', $client_id)
            ->get()->row();

        if (!empty($client)) {
            $service_id = $this->input->post('service_id');

            $db_array = array(
                'client_id' => $client_id,
            );
            $this->db->where('service_id', $service_id);
            $this->db->update('ip_services', $db_array);

            $response = array(
                'success' => 1,
                'service_id' => $service_id
            );
        } else {
            $this->load->helper('json_error');
            $response = array(
                'success' => 0,
                'validation_errors' => json_errors()
            );
        }

        echo json_encode($response);
    }

    public function get_item()
    {
        $this->load->model('services/mdl_service_items');

        $item = $this->mdl_service_items->get_by_id($this->input->post('item_id'));

        echo json_encode($item);
    }

    public function modal_create_service()
    {
        $this->load->module('layout');
        $this->load->model('invoice_groups/mdl_invoice_groups');
        $this->load->model('tax_rates/mdl_tax_rates');
        $this->load->model('clients/mdl_clients');

        $data = array(
            'invoice_groups' => $this->mdl_invoice_groups->get()->result(),
            'tax_rates' => $this->mdl_tax_rates->get()->result(),
            'client' => $this->mdl_clients->get_by_id($this->input->post('client_id')),
            'clients' => $this->mdl_clients->get_latest(),
        );

        $this->layout->load_view('services/modal_create_service', $data);
    }

    public function modal_copy_service()
    {
        $this->load->module('layout');

        $this->load->model('services/mdl_services');
        $this->load->model('invoice_groups/mdl_invoice_groups');
        $this->load->model('tax_rates/mdl_tax_rates');
        $this->load->model('clients/mdl_clients');

        $data = array(
            'invoice_groups' => $this->mdl_invoice_groups->get()->result(),
            'tax_rates' => $this->mdl_tax_rates->get()->result(),
            'service_id' => $this->input->post('service_id'),
            'service' => $this->mdl_services->where('ip_services.service_id', $this->input->post('service_id'))->get()->row(),
            'client' => $this->mdl_clients->get_by_id($this->input->post('client_id')),
        );

        $this->layout->load_view('services/modal_copy_service', $data);
    }

    public function copy_service()
    {
        $this->load->model('services/mdl_services');
        $this->load->model('services/mdl_service_items');
        $this->load->model('services/mdl_service_tax_rates');

        if ($this->mdl_services->run_validation()) {
            $target_id = $this->mdl_services->save();
            $source_id = $this->input->post('service_id');

            $this->mdl_services->copy_service($source_id, $target_id);

            $response = array(
                'success' => 1,
                'service_id' => $target_id
            );
        } else {
            $this->load->helper('json_error');
            $response = array(
                'success' => 0,
                'validation_errors' => json_errors()
            );
        }

        echo json_encode($response);
    }

    public function modal_service_to_invoice($service_id)
    {
        $this->load->model('invoice_groups/mdl_invoice_groups');
        $this->load->model('services/mdl_services');

        $data = array(
            'invoice_groups' => $this->mdl_invoice_groups->get()->result(),
            'service_id' => $service_id,
            'service' => $this->mdl_services->where('ip_services.service_id', $service_id)->get()->row()
        );

        $this->load->view('services/modal_service_to_invoice', $data);
    }

    public function service_to_invoice()
    {
        $this->load->model(
            array(
                'invoices/mdl_invoices',
                'invoices/mdl_items',
                'services/mdl_services',
                'services/mdl_service_items',
                'invoices/mdl_invoice_tax_rates',
                'services/mdl_service_tax_rates'
            )
        );

        if ($this->mdl_invoices->run_validation()) {
            // Get the service
            $service = $this->mdl_services->get_by_id($this->input->post('service_id'));

            $invoice_id = $this->mdl_invoices->create(null, false);

            // Update the discounts
            $this->db->where('invoice_id', $invoice_id);
            $this->db->set('invoice_discount_amount', $service->service_discount_amount);
            $this->db->set('invoice_discount_percent', $service->service_discount_percent);
            $this->db->update('ip_invoices');

            // Save the invoice id to the service
            $this->db->where('service_id', $this->input->post('service_id'));
            $this->db->set('invoice_id', $invoice_id);
            $this->db->update('ip_services');

            $service_items = $this->mdl_service_items->where('service_id', $this->input->post('service_id'))->get()->result();

            foreach ($service_items as $service_item) {
                $db_array = array(
                    'invoice_id' => $invoice_id,
                    'item_tax_rate_id' => $service_item->item_tax_rate_id,
                    'item_product_id' => $service_item->item_product_id,
                    'item_name' => $service_item->item_name,
                    'item_description' => $service_item->item_description,
                    'item_quantity' => $service_item->item_quantity,
                    'item_price' => $service_item->item_price,
                    'item_product_unit_id' => $service_item->item_product_unit_id,
                    'item_product_unit' => $service_item->item_product_unit,
                    'item_discount_amount' => $service_item->item_discount_amount,
                    'item_order' => $service_item->item_order
                );

                $this->mdl_items->save(null, $db_array);
            }

            $service_tax_rates = $this->mdl_service_tax_rates->where('service_id', $this->input->post('service_id'))->get()->result();

            foreach ($service_tax_rates as $service_tax_rate) {
                $db_array = array(
                    'invoice_id' => $invoice_id,
                    'tax_rate_id' => $service_tax_rate->tax_rate_id,
                    'include_item_tax' => $service_tax_rate->include_item_tax,
                    'invoice_tax_rate_amount' => $service_tax_rate->service_tax_rate_amount
                );

                $this->mdl_invoice_tax_rates->save(null, $db_array);
            }

            $response = array(
                'success' => 1,
                'invoice_id' => $invoice_id
            );
        } else {
            $this->load->helper('json_error');
            $response = array(
                'success' => 0,
                'validation_errors' => json_errors()
            );
        }

        echo json_encode($response);
    }

}
