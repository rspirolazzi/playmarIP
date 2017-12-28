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
 * Class Mdl_Service_Amounts
 */
class Mdl_Service_Amounts extends CI_Model
{
    /**
     * IP_SERVICE_AMOUNTS
     * service_amount_id
     * service_id
     * service_item_subtotal      SUM(item_subtotal)
     * service_item_tax_total     SUM(item_tax_total)
     * service_tax_total
     * service_total              service_item_subtotal + service_item_tax_total + service_tax_total
     *
     * IP_SERVICE_ITEM_AMOUNTS
     * item_amount_id
     * item_id
     * item_tax_rate_id
     * item_subtotal             item_quantity * item_price
     * item_tax_total            item_subtotal * tax_rate_percent
     * item_total                item_subtotal + item_tax_total
     *
     * @param $service_id
     */
    public function calculate($service_id)
    {
        // Get the basic totals
        $query = $this->db->query("
            SELECT SUM(item_subtotal) AS service_item_subtotal,
		        SUM(item_tax_total) AS service_item_tax_total,
		        SUM(item_subtotal) + SUM(item_tax_total) AS service_total,
		        SUM(item_discount) AS service_item_discount
		    FROM ip_service_item_amounts
		    WHERE item_id
		        IN (SELECT item_id FROM ip_service_items WHERE service_id = " . $this->db->escape($service_id) . ")
            ");

        $service_amounts = $query->row();

        $service_item_subtotal = $service_amounts->service_item_subtotal - $service_amounts->service_item_discount;
        $service_subtotal = $service_item_subtotal + $service_amounts->service_item_tax_total;
        $service_total = $this->calculate_discount($service_id, $service_subtotal);

        // Create the database array and insert or update
        $db_array = array(
            'service_id' => $service_id,
            'service_item_subtotal' => $service_item_subtotal,
            'service_item_tax_total' => $service_amounts->service_item_tax_total,
            'service_total' => $service_total,
        );

        $this->db->where('service_id', $service_id);
        if ($this->db->get('ip_service_amounts')->num_rows()) {
            // The record already exists; update it
            $this->db->where('service_id', $service_id);
            $this->db->update('ip_service_amounts', $db_array);
        } else {
            // The record does not yet exist; insert it
            $this->db->insert('ip_service_amounts', $db_array);
        }

        // Calculate the service taxes
        $this->calculate_service_taxes($service_id);
    }

    /**
     * @param $service_id
     * @param $service_total
     * @return float
     */
    public function calculate_discount($service_id, $service_total)
    {
        $this->db->where('service_id', $service_id);
        $service_data = $this->db->get('ip_services')->row();

        $total = (float)number_format($service_total, 2, '.', '');
        $discount_amount = (float)number_format($service_data->service_discount_amount, 2, '.', '');
        $discount_percent = (float)number_format($service_data->service_discount_percent, 2, '.', '');

        $total = $total - $discount_amount;
        $total = $total - round(($total / 100 * $discount_percent), 2);

        return $total;
    }

    /**
     * @param $service_id
     */
    public function calculate_service_taxes($service_id)
    {
        // First check to see if there are any service taxes applied
        $this->load->model('services/mdl_service_tax_rates');
        $service_tax_rates = $this->mdl_service_tax_rates->where('service_id', $service_id)->get()->result();

        if ($service_tax_rates) {
            // There are service taxes applied
            // Get the current service amount record
            $service_amount = $this->db->where('service_id', $service_id)->get('ip_service_amounts')->row();

            // Loop through the service taxes and update the amount for each of the applied service taxes
            foreach ($service_tax_rates as $service_tax_rate) {
                if ($service_tax_rate->include_item_tax) {
                    // The service tax rate should include the applied item tax
                    $service_tax_rate_amount = ($service_amount->service_item_subtotal + $service_amount->service_item_tax_total) * ($service_tax_rate->service_tax_rate_percent / 100);
                } else {
                    // The service tax rate should not include the applied item tax
                    $service_tax_rate_amount = $service_amount->service_item_subtotal * ($service_tax_rate->service_tax_rate_percent / 100);
                }

                // Update the service tax rate record
                $db_array = array(
                    'service_tax_rate_amount' => $service_tax_rate_amount
                );
                $this->db->where('service_tax_rate_id', $service_tax_rate->service_tax_rate_id);
                $this->db->update('ip_service_tax_rates', $db_array);
            }

            // Update the service amount record with the total service tax amount
            $this->db->query("
                UPDATE ip_service_amounts SET service_tax_total =
                (
                    SELECT SUM(service_tax_rate_amount)
                    FROM ip_service_tax_rates
                    WHERE service_id = " . $this->db->escape($service_id) . "
                )
                WHERE service_id = " . $this->db->escape($service_id)
            );

            // Get the updated service amount record
            $service_amount = $this->db->where('service_id', $service_id)->get('ip_service_amounts')->row();

            // Recalculate the service total
            $service_total = $service_amount->service_item_subtotal + $service_amount->service_item_tax_total + $service_amount->service_tax_total;

            $service_total = $this->calculate_discount($service_id, $service_total);

            // Update the service amount record
            $db_array = array(
                'service_total' => $service_total
            );

            $this->db->where('service_id', $service_id);
            $this->db->update('ip_service_amounts', $db_array);
        } else {
            // No service taxes applied

            $db_array = array(
                'service_tax_total' => '0.00'
            );

            $this->db->where('service_id', $service_id);
            $this->db->update('ip_service_amounts', $db_array);
        }
    }

    /**
     * @param null $period
     * @return mixed
     */
    public function get_total_serviced($period = null)
    {
        switch ($period) {
            case 'month':
                return $this->db->query("
					SELECT SUM(service_total) AS total_serviced 
					FROM ip_service_amounts
					WHERE service_id IN 
					(SELECT service_id FROM ip_services
					WHERE MONTH(service_date_created) = MONTH(NOW()) 
					AND YEAR(service_date_created) = YEAR(NOW()))")->row()->total_serviced;
            case 'last_month':
                return $this->db->query("
					SELECT SUM(service_total) AS total_serviced 
					FROM ip_service_amounts
					WHERE service_id IN 
					(SELECT service_id FROM ip_services
					WHERE MONTH(service_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)
					AND YEAR(service_date_created) = YEAR(NOW() - INTERVAL 1 MONTH))")->row()->total_serviced;
            case 'year':
                return $this->db->query("
					SELECT SUM(service_total) AS total_serviced 
					FROM ip_service_amounts
					WHERE service_id IN 
					(SELECT service_id FROM ip_services WHERE YEAR(service_date_created) = YEAR(NOW()))")->row()->total_serviced;
            case 'last_year':
                return $this->db->query("
					SELECT SUM(service_total) AS total_serviced 
					FROM ip_service_amounts
					WHERE service_id IN 
					(SELECT service_id FROM ip_services WHERE YEAR(service_date_created) = YEAR(NOW() - INTERVAL 1 YEAR))")->row()->total_serviced;
            default:
                return $this->db->query("SELECT SUM(service_total) AS total_serviced FROM ip_service_amounts")->row()->total_serviced;
        }
    }

    /**
     * @param string $period
     * @return array
     */
    public function get_status_totals($period = '')
    {
        switch ($period) {
            default:
            case 'this-month':
                $results = $this->db->query("
					SELECT service_status_id,
					    SUM(service_total) AS sum_total,
					    COUNT(*) AS num_total
					FROM ip_service_amounts
					JOIN ip_services ON ip_services.service_id = ip_service_amounts.service_id
                        AND MONTH(ip_services.service_date_created) = MONTH(NOW())
                        AND YEAR(ip_services.service_date_created) = YEAR(NOW())
					GROUP BY ip_services.service_status_id")->result_array();
                break;
            case 'last-month':
                $results = $this->db->query("
					SELECT service_status_id,
					    SUM(service_total) AS sum_total,
					    COUNT(*) AS num_total
					FROM ip_service_amounts
					JOIN ip_services ON ip_services.service_id = ip_service_amounts.service_id
                        AND MONTH(ip_services.service_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)
                        AND YEAR(ip_services.service_date_created) = YEAR(NOW())
					GROUP BY ip_services.service_status_id")->result_array();
                break;
            case 'this-quarter':
                $results = $this->db->query("
					SELECT service_status_id,
					    SUM(service_total) AS sum_total,
					    COUNT(*) AS num_total
					FROM ip_service_amounts
					JOIN ip_services ON ip_services.service_id = ip_service_amounts.service_id
                        AND QUARTER(ip_services.service_date_created) = QUARTER(NOW())
                        AND YEAR(ip_services.service_date_created) = YEAR(NOW())
					GROUP BY ip_services.service_status_id")->result_array();
                break;
            case 'last-quarter':
                $results = $this->db->query("
					SELECT service_status_id,
					    SUM(service_total) AS sum_total,
					    COUNT(*) AS num_total
					FROM ip_service_amounts
					JOIN ip_services ON ip_services.service_id = ip_service_amounts.service_id
                        AND QUARTER(ip_services.service_date_created) = QUARTER(NOW() - INTERVAL 1 QUARTER)
                        AND YEAR(ip_services.service_date_created) = YEAR(NOW())
					GROUP BY ip_services.service_status_id")->result_array();
                break;
            case 'this-year':
                $results = $this->db->query("
					SELECT service_status_id,
					    SUM(service_total) AS sum_total,
					    COUNT(*) AS num_total
					FROM ip_service_amounts
					JOIN ip_services ON ip_services.service_id = ip_service_amounts.service_id
                        AND YEAR(ip_services.service_date_created) = YEAR(NOW())
					GROUP BY ip_services.service_status_id")->result_array();
                break;
            case 'last-year':
                $results = $this->db->query("
					SELECT service_status_id,
					    SUM(service_total) AS sum_total,
					    COUNT(*) AS num_total
					FROM ip_service_amounts
					JOIN ip_services ON ip_services.service_id = ip_service_amounts.service_id
                        AND YEAR(ip_services.service_date_created) = YEAR(NOW() - INTERVAL 1 YEAR)
					GROUP BY ip_services.service_status_id")->result_array();
                break;
        }

        $return = array();

        foreach ($this->mdl_services->statuses() as $key => $status) {
            $return[$key] = array(
                'service_status_id' => $key,
                'class' => $status['class'],
                'label' => $status['label'],
                'href' => $status['href'],
                'sum_total' => 0,
                'num_total' => 0
            );
        }

        foreach ($results as $result) {
            $return[$result['service_status_id']] = array_merge($return[$result['service_status_id']], $result);
        }

        return $return;
    }

}
