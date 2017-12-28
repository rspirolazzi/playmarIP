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
 * Class Mdl_Service_Custom
 */
class Mdl_Service_Custom extends Validator
{
    public static $positions = array(
        'custom_fields',
        'properties'
    );
    public $table = 'ip_service_custom';
    public $primary_key = 'ip_service_custom.service_custom_id';

    public function default_select()
    {
        $this->db->select('SQL_CALC_FOUND_ROWS ip_service_custom.*, ip_custom_fields.*', false);
    }

    public function default_join()
    {
        $this->db->join('ip_custom_fields', 'ip_service_custom.service_custom_fieldid = ip_custom_fields.custom_field_id');
    }

    public function default_order_by()
    {
        $this->db->order_by('custom_field_table ASC, custom_field_order ASC, custom_field_label ASC');
    }


    /**
     * @param $service_id
     * @param $db_array
     * @return bool|string
     */
    public function save_custom($service_id, $db_array)
    {
        $result = $this->validate($db_array);

        if ($result === true) {
            $form_data = isset($this->_formdata) ? $this->_formdata : null;

            if (is_null($form_data)) {
                return true;
            }

            $service_custom_id = null;

            foreach ($form_data as $key => $value) {
                $db_array = array(
                    'service_id' => $service_id,
                    'service_custom_fieldid' => $key,
                    'service_custom_fieldvalue' => $value
                );

                $service_custom = $this->where('service_id', $service_id)->where('service_custom_fieldid', $key)->get();

                if ($service_custom->num_rows()) {
                    $service_custom_id = $service_custom->row()->service_custom_id;
                }

                parent::save($service_custom_id, $db_array);
            }

            return true;
        }

        return $result;
    }

    public function by_id($service_id)
    {
        $this->db->where('ip_service_custom.service_id', $service_id);
        return $this;
    }

}
