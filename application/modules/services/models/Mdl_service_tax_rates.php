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
 * Class Mdl_Service_Tax_Rates
 */
class Mdl_Service_Tax_Rates extends Response_Model
{
    public $table = 'ip_service_tax_rates';
    public $primary_key = 'ip_service_tax_rates.service_tax_rate_id';

    public function default_select()
    {
        $this->db->select('ip_tax_rates.tax_rate_name AS service_tax_rate_name');
        $this->db->select('ip_tax_rates.tax_rate_percent AS service_tax_rate_percent');
        $this->db->select('ip_service_tax_rates.*');
    }

    public function default_join()
    {
        $this->db->join('ip_tax_rates', 'ip_tax_rates.tax_rate_id = ip_service_tax_rates.tax_rate_id');
    }

    /**
     * @param null $id
     * @param null $db_array
     * @return void
     */
    public function save($id = null, $db_array = null)
    {
        parent::save($id, $db_array);

        $this->load->model('services/mdl_service_amounts');

        $service_id = $this->input->post('service_id');

        if ($service_id) {
            $this->mdl_service_amounts->calculate($service_id);
        }
    }

    /**
     * @return array
     * @return void
     */
    public function validation_rules()
    {
        return array(
            'service_id' => array(
                'field' => 'service_id',
                'label' => trans('service'),
                'rules' => 'required'
            ),
            'tax_rate_id' => array(
                'field' => 'tax_rate_id',
                'label' => trans('tax_rate'),
                'rules' => 'required'
            ),
            'include_item_tax' => array(
                'field' => 'include_item_tax',
                'label' => trans('tax_rate_placement'),
                'rules' => 'required'
            )
        );
    }

}
