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
class Services extends Guest_Controller
{
    /**
     * Services constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('services/mdl_services');
    }

    public function index()
    {
        // Display open services by default
        redirect('guest/services/status/open');
    }

    /**
     * @param string $status
     * @param int $page
     */
    public function status($status = 'open', $page = 0)
    {
        redirect_to_set();

        // Determine which group of services to load
        switch ($status) {
            case 'approved':
                $this->mdl_services
                    ->is_approved()
                    ->where_in('ip_services.client_id', $this->user_clients);
                break;
            case 'rejected':
                $this->mdl_services
                    ->is_rejected()
                    ->where_in('ip_services.client_id', $this->user_clients);
                $this->layout->set('show_invoice_column', true);
                break;
            default:
                $this->mdl_services
                    ->is_open()
                    ->where_in('ip_services.client_id', $this->user_clients);
                break;
        }

        $this->mdl_services->paginate(site_url('guest/services/status/' . $status), $page);
        $services = $this->mdl_services->result();

        $this->layout->set('services', $services);
        $this->layout->set('status', $status);
        $this->layout->buffer('content', 'guest/services_index');
        $this->layout->render('layout_guest');
    }

    /**
     * @param $service_id
     */
    public function view($service_id)
    {
        redirect_to_set();

        $this->load->model('services/mdl_service_items');
        $this->load->model('services/mdl_service_tax_rates');

        $service = $this->mdl_services->guest_visible()
            ->where('ip_services.service_id', $service_id)
            ->where_in('ip_services.client_id', $this->user_clients)
            ->get()->row();

        if (!$service) {
            show_404();
        }

        $this->mdl_services->mark_viewed($service->service_id);

        $this->layout->set(
            array(
                'service' => $service,
                'items' => $this->mdl_service_items
                    ->where('service_id', $service_id)
                    ->get()->result(),
                'service_tax_rates' => $this->mdl_service_tax_rates
                    ->where('service_id', $service_id)
                    ->get()->result(),
                'service_id' => $service_id
            )
        );

        $this->layout->buffer('content', 'guest/services_view');
        $this->layout->render('layout_guest');
    }

    /**
     * @param $service_id
     * @param bool $stream
     * @param null $service_template
     */
    public function generate_pdf($service_id, $stream = true, $service_template = null)
    {
        $this->load->helper('pdf');

        $this->mdl_services->mark_viewed($service_id);

        $service = $this->mdl_services->guest_visible()
            ->where('ip_services.service_id', $service_id)
            ->where_in('ip_services.client_id', $this->user_clients)
            ->get()->row();

        if (!$service) {
            show_404();
        } else {
            generate_service_pdf($service_id, $stream, $service_template);
        }
    }

    /**
     * @param $service_id
     */
    public function approve($service_id)
    {
        $this->load->model('services/mdl_services');
        $this->load->helper('mailer');

        $this->mdl_services->approve_service_by_id($service_id);
        email_service_status($service_id, "approved");

        redirect_to('guest/services');
    }

    /**
     * @param $service_id
     */
    public function reject($service_id)
    {
        $this->load->model('services/mdl_services');
        $this->load->helper('mailer');

        $this->mdl_services->reject_service_by_id($service_id);
        email_service_status($service_id, "rejected");

        redirect_to('guest/services');
    }

}
