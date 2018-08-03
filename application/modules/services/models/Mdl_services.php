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
 * Class Mdl_Services
 */
class Mdl_Services extends Response_Model
{
    public $table = 'ip_services';
    public $primary_key = 'ip_services.service_id';
    public $date_modified_field = 'service_date_modified';

    /**
     * @return array
     */
    public function statuses()
    {
        return array(
            '1' => array(
                'label' => trans('pending'),
                'class' => 'draft',
                'href' => 'services/status/draft'
            ),
            /*'2' => array(
                'label' => trans('sent'),
                'class' => 'sent',
                'href' => 'services/status/sent'
            ),*/
            '4' => array(
                'label' => trans('finished'),
                'class' => 'approved',
                'href' => 'services/status/approved'
            ),
            /*'5' => array(
                'label' => trans('rejected'),
                'class' => 'rejected',
                'href' => 'services/status/rejected'
            ),*/
            '6' => array(
                'label' => trans('canceled'),
                'class' => 'canceled',
                'href' => 'services/status/canceled'
            )
        );
    }

    public function default_select()
    {
        $this->db->select("
            SQL_CALC_FOUND_ROWS
            ip_users.user_name,
			ip_users.user_company,
			ip_users.user_address_1,
			ip_users.user_address_2,
			ip_users.user_city,
			ip_users.user_state,
			ip_users.user_zip,
			ip_users.user_country,
			ip_users.user_phone,
			ip_users.user_fax,
			ip_users.user_mobile,
			ip_users.user_email,
			ip_users.user_web,
			ip_users.user_vat_id,
			ip_users.user_tax_code,
			ip_clients.*,
			ip_service_amounts.service_amount_id,
			IFnull(ip_service_amounts.service_item_subtotal, '0.00') AS service_item_subtotal,
			IFnull(ip_service_amounts.service_item_tax_total, '0.00') AS service_item_tax_total,
			IFnull(ip_service_amounts.service_tax_total, '0.00') AS service_tax_total,
			IFnull(ip_service_amounts.service_total, '0.00') AS service_total,
            ip_invoices.invoice_number,
			ip_services.*", false);
    }

    public function default_order_by()
    {
        $this->db->order_by('ip_services.service_id DESC');
    }
   

    public function default_join()
    {
        $this->db->join('ip_clients', 'ip_clients.client_id = ip_services.client_id');
        $this->db->join('ip_users', 'ip_users.user_id = ip_services.user_id');
        $this->db->join('ip_service_amounts', 'ip_service_amounts.service_id = ip_services.service_id', 'left');
        $this->db->join('ip_invoices', 'ip_invoices.invoice_id = ip_services.invoice_id', 'left');
    }

    /**
     * @return array
     */
    public function validation_rules()
    {
        return array(
            'client_id' => array(
                'field' => 'client_id',
                'label' => trans('client'),
                'rules' => 'required'
            ),
            'service_date_created' => array(
                'field' => 'service_date_created',
                'label' => trans('service_date'),
                'rules' => 'required'
            ),
            'invoice_group_id' => array(
                'field' => 'invoice_group_id',
                'label' => trans('service_group'),
                'rules' => 'required'
            ),
            'user_id' => array(
                'field' => 'user_id',
                'label' => trans('user'),
                'rule' => 'required'
            )
        );
    }

    /**
     * @return array
     */
    public function validation_rules_save_service()
    {
        return array(
            'service_number' => array(
                'field' => 'service_number',
                'label' => trans('service') . ' #',
                'rules' => 'is_unique[ip_services.service_number' . (($this->id) ? '.service_id.' . $this->id : '') . ']'
            ),
            'service_date_created' => array(
                'field' => 'service_date_created',
                'label' => trans('date'),
                'rules' => 'required'
            ),
            'service_date_expires' => array(
                'field' => 'service_date_expires',
                'label' => trans('due_date'),
                'rules' => 'required'
            )
        );
    }

    /**
     * @param null $db_array
     * @return int|null
     */
    public function create($db_array = null)
    {
        $service_id = parent::save(null, $db_array);

        // Create an service amount record
        $db_array = array(
            'service_id' => $service_id
        );

        $this->db->insert('ip_service_amounts', $db_array);

        // Create the default invoice tax record if applicable
        if (get_setting('default_invoice_tax_rate')) {
            $db_array = array(
                'service_id' => $service_id,
                'tax_rate_id' => get_setting('default_invoice_tax_rate'),
                'include_item_tax' => get_setting('default_include_item_tax'),
                'service_tax_rate_amount' => 0
            );

            $this->db->insert('ip_service_tax_rates', $db_array);
        }

        return $service_id;
    }

    /**
     * Copies service items, tax rates, etc from source to target
     * @param int $source_id
     * @param int $target_id
     */
    public function copy_service($source_id, $target_id)
    {
        $this->load->model('services/mdl_service_items');

        $service_items = $this->mdl_service_items->where('service_id', $source_id)->get()->result();

        foreach ($service_items as $service_item) {
            $db_array = array(
                'service_id' => $target_id,
                'item_tax_rate_id' => $service_item->item_tax_rate_id,
                'item_name' => $service_item->item_name,
                'item_description' => $service_item->item_description,
                'item_quantity' => $service_item->item_quantity,
                'item_price' => $service_item->item_price,
                'item_order' => $service_item->item_order
            );

            $this->mdl_service_items->save(null, $db_array);
        }

        $service_tax_rates = $this->mdl_service_tax_rates->where('service_id', $source_id)->get()->result();

        foreach ($service_tax_rates as $service_tax_rate) {
            $db_array = array(
                'service_id' => $target_id,
                'tax_rate_id' => $service_tax_rate->tax_rate_id,
                'include_item_tax' => $service_tax_rate->include_item_tax,
                'service_tax_rate_amount' => $service_tax_rate->service_tax_rate_amount
            );

            $this->mdl_service_tax_rates->save(null, $db_array);
        }

        // Copy the custom fields
        $this->load->model('custom_fields/mdl_service_custom');
        $db_array = $this->mdl_service_custom->where('service_id', $source_id)->get()->row_array();

        if (count($db_array) > 2) {
            unset($db_array['service_custom_id']);
            $db_array['service_id'] = $target_id;
            $this->mdl_service_custom->save_custom($target_id, $db_array);
        }
    }

    /**
     * @return array
     */
    public function db_array()
    {
        $db_array = parent::db_array();

        // Get the client id for the submitted service
        $this->load->model('clients/mdl_clients');
        $cid = $this->mdl_clients->where('ip_clients.client_id', $db_array['client_id'])->get()->row()->client_id;
        $db_array['client_id'] = $cid;

        $db_array['service_date_created'] = date_to_mysql($db_array['service_date_created']);
        $db_array['service_date_expires'] = $this->get_date_due($db_array['service_date_created']);

        $db_array['notes'] = get_setting('default_service_notes');

        if (!isset($db_array['service_status_id'])) {
            $db_array['service_status_id'] = 1;
        }

        $generate_service_number = get_setting('generate_service_number_for_draft');

        if ($db_array['service_status_id'] === 1 && $generate_service_number == 1) {
            $db_array['service_number'] = $this->get_service_number($db_array['invoice_group_id']);
        } elseif ($db_array['service_status_id'] != 1) {
            $db_array['service_number'] = $this->get_service_number($db_array['invoice_group_id']);
        } else {
            $db_array['service_number'] = '';
        }

        // Generate the unique url key
        $db_array['service_url_key'] = $this->get_url_key();

        return $db_array;
    }

    /**
     * @param string $service_date_created
     */
    public function get_date_due($service_date_created)
    {
        $service_date_expires = new DateTime($service_date_created);
        $service_date_expires->add(new DateInterval('P' . get_setting('services_expire_after') . 'D'));
        return $service_date_expires->format('Y-m-d');
    }

    /**
     * @param $invoice_group_id
     * @return mixed
     */
    public function get_service_number($invoice_group_id)
    {
        $this->load->model('invoice_groups/mdl_invoice_groups');
        return $this->mdl_invoice_groups->generate_invoice_number($invoice_group_id);
    }

    /**
     * @return string
     */
    public function get_url_key()
    {
        $this->load->helper('string');
        return random_string('alnum', 15);
    }

    /**
     * @param $invoice_id
     * @return mixed
     */
    public function get_invoice_group_id($invoice_id)
    {
        $invoice = $this->get_by_id($invoice_id);
        return $invoice->invoice_group_id;
    }

    /**
     * @param int $service_id
     */
    public function delete($service_id)
    {
        parent::delete($service_id);

        $this->load->helper('orphan');
        delete_orphans();
    }

    /**
     * @return $this
     */
    public function is_draft()
    {
        $this->filter_where('service_status_id', 1);
        return $this;
    }

    /**
     * @return $this
     */
    public function is_sent()
    {
        $this->filter_where('service_status_id', 2);
        return $this;
    }

    /**
     * @return $this
     */
    public function is_approved()
    {
        $this->filter_where('service_status_id', 4);
        return $this;
    }

    /**
     * @return $this
     */
    public function is_rejected()
    {
        $this->filter_where('service_status_id', 5);
        return $this;
    }

    /**
     * @return $this
     */
    public function is_canceled()
    {
        $this->filter_where('service_status_id', 6);
        return $this;
    }

    /**
     * Used by guest module; includes only sent and viewed
     *
     * @return $this
     */
    public function is_open()
    {
        $this->filter_where_in('service_status_id', array(2, 3));
        return $this;
    }

    /**
     * @return $this
     */
    public function guest_visible()
    {
        $this->filter_where_in('service_status_id', array(2, 3, 4, 5));
        return $this;
    }

    /**
     * @param $client_id
     * @return $this
     */
    public function by_client($client_id)
    {
        $this->filter_where('ip_services.client_id', $client_id);
        return $this;
    }

    /**
     * @param $service_url_key
     */
    public function approve_service_by_key($service_url_key)
    {
        $this->db->where_in('service_status_id', array(2, 3));
        $this->db->where('service_url_key', $service_url_key);
        $this->db->set('service_status_id', 4);
        $this->db->update('ip_services');
    }

    /**
     * @param $service_url_key
     */
    public function reject_service_by_key($service_url_key)
    {
        $this->db->where_in('service_status_id', array(2, 3));
        $this->db->where('service_url_key', $service_url_key);
        $this->db->set('service_status_id', 5);
        $this->db->update('ip_services');
    }

    /**
     * @param $service_id
     */
    public function approve_service_by_id($service_id)
    {
        $this->db->where_in('service_status_id', array(2, 3));
        $this->db->where('service_id', $service_id);
        $this->db->set('service_status_id', 4);
        $this->db->update('ip_services');
    }

    /**
     * @param $service_id
     */
    public function reject_service_by_id($service_id)
    {
        $this->db->where_in('service_status_id', array(2, 3));
        $this->db->where('service_id', $service_id);
        $this->db->set('service_status_id', 5);
        $this->db->update('ip_services');
    }

    /**
     * @param $service_id
     */
    public function mark_viewed($service_id)
    {
        $this->db->select('service_status_id');
        $this->db->where('service_id', $service_id);

        $service = $this->db->get('ip_services');

        if ($service->num_rows()) {
            if ($service->row()->service_status_id == 2) {
                $this->db->where('service_id', $service_id);
                $this->db->set('service_status_id', 3);
                $this->db->update('ip_services');
            }
        }
    }

    /**
     * @param $service_id
     */
    public function mark_sent($service_id)
    {
        $this->db->select('service_status_id');
        $this->db->where('service_id', $service_id);

        $service = $this->db->get('ip_services');

        if ($service->num_rows()) {
            if ($service->row()->service_status_id == 1) {
                $this->db->where('service_id', $service_id);
                $this->db->set('service_status_id', 2);
                $this->db->update('ip_services');
            }
        }
    }

}
