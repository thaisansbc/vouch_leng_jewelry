<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Payrolls extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->methods['index_get']['limit'] = 500;
        $this->load->api_model('sales_api');
        $this->load->api_model('payroll_api');
    }


    public function get_salary_get()
    {
        $employee_id = $this->get('employee_id');
        $filters = [
            'employee_id' => $employee_id,
            'branch'      => $this->get('branch') ? $this->get('branch') : null,
            'month'       => $this->get('month') ? $this->get('month') : '',
            'year'        => $this->get('year') ? $this->get('year') : '',
            'limit'       => $this->get('limit') ? $this->get('limit') : 10,
            'start'       => $this->get('start') && is_numeric($this->get('start')) ? $this->get('start') : 1,
            'order_by'    => $this->get('order_by') ? explode(',', $this->get('order_by')) : [''.$this->db->dbprefix("pay_salaries").'.id', 'decs'],
        ];
        if ($employee_id == '') {
            if ($sales = $this->payroll_api->getSalalies($filters)) {
                $data = [
                    'data'   => [$sales],
                    'branch' => (int) $filters['branch'],
                    'month'  => (int) $filters['month'],
                    'total'  => $this->payroll_api->countSalaries($filters),
                ];
                $this->response($data, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No salary record found.',
                    'status'  => false,
                    'data'    => false
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            if ($sale = $this->payroll_api->getSalary($filters)) {
                //$sale->created_by = $this->sales_api->getUser($sale->created_by);
                $this->set_response([
                    'status'  => TRUE,
                    'message' => "Success",
                    'data'    => $sale
                ], REST_Controller::HTTP_OK);
            } else {
                $this->set_response([
                    'message' => 'Salary could not be found for employee_id ',
                    'status'  => false,
                    'data'    => false
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
}
