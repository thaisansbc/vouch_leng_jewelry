<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Companies extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->methods['index_get']['limit'] = 500;
        $this->load->api_model('companies_api');
    }

    protected function setCompany($company)
    {
        $company->company = !empty($company->company) && $company->company != '-' ? $company->company : null;
      
        if ($company->group_name == 'customer') {
            unset($company->group_id, $company->group_name, $company->invoice_footer, $company->logo);
        } elseif ($company->group_name == 'supplier') {
            unset($company->group_id, $company->group_name, $company->invoice_footer, $company->logo, $company->customer_group_id, $company->customer_group_name, $company->deposit_amount, $company->payment_term, $company->price_group_id, $company->price_group_name, $company->award_points, $company->name);
        } elseif ($company->group_name == 'biller') {
            $company->logo = base_url('assets/uploads/logos/' . $company->logo);
            unset($company->group_id, $company->group_name, $company->customer_group_id, $company->customer_group_name, $company->deposit_amount, $company->payment_term, $company->price_group_id, $company->price_group_name, $company->award_points);
        }
        $company = (array) $company;
        ksort($company);
        return $company;
    }

    public function index_get()
    {
        $name = $this->get('name');

        $filters = [
            'name'     => $name,
            'include'  => $this->get('include') ? explode(',', $this->get('include')) : null,
            'group'    => $this->get('group') ? $this->get('group') : 'customer',
            'start'    => $this->get('start') && is_numeric($this->get('start')) ? $this->get('start') : 1,
            'limit'    => $this->get('limit') && is_numeric($this->get('limit')) ? $this->get('limit') : 10,
            'order_by' => $this->get('order_by') ? explode(',', $this->get('order_by')) : ['company', 'acs'],
        ];

        if ($name === null) {
            if ($companies = $this->companies_api->getCompanies($filters)) {
                $pr_data = [];
                foreach ($companies as $company) {
                    if (!empty($filters['include'])) {
                        foreach ($filters['include'] as $include) {
                            if ($include == 'user') {
                                $company->users = $this->companies_api->getCompanyUser($company->id);
                            }
                        }
                    }

                    $pr_data[] = $this->setCompany($company);
                }

                $data = [
                    'data'  => $pr_data,
                    'limit' => $filters['limit'],
                    'start' => $filters['start'],
                    'total' => $this->companies_api->countCompanies($filters),
                ];
                $this->response($data, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No company were found.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            if ($company = $this->companies_api->getCompany($filters)) {
                if (!empty($filters['include'])) {
                    foreach ($filters['include'] as $include) {
                        if ($include == 'user') {
                            $company->users = $this->companies_api->getCompanyUser($company->id);
                        }
                    }
                }

                $company = $this->setCompany($company);
                $this->set_response($company, REST_Controller::HTTP_OK);
            } else {
                $this->set_response([
                    'message' => 'Company could not be found for name ' . $name . '.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
    public function getCustomerGroups_get()
    {
        if ($this->input->server('REQUEST_METHOD') == 'GET') {
            if ($company = $this->companies_api->getAllCustomerGroups()) {
                $this->response($company, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No customer group record found.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status' => FALSE,
                'message' =>  "Invalid Request",
                'data' => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function getPriceGroups_get()
    {
        if ($this->input->server('REQUEST_METHOD') == 'GET') {
            if ($company = $this->companies_api->getAllPriceGroups()) {
                $this->response($company, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No expense record found.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status' => FALSE,
                'message' =>  "Invalid Request",
                'data' => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function add_customer_post()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $jdata = json_decode(file_get_contents('php://input'),true);//json_decode($this->input->raw_input_stream, true);
            if(json_last_error() === JSON_ERROR_NONE) {

                $customer_group = $jdata['customer_group'];   
                $price_group    = $jdata['price_group'];             
                $customer_code  = isset($jdata['customer_code']) ? $jdata['customer_code'] : null ;
                $name           = isset($jdata['name']) ? $jdata['name'] : null ;
                $phone          = isset($jdata['phone']) ? $jdata['phone'] : null ;
                $address        = isset($jdata['address']) ? $jdata['address'] : null ;
                $note           = isset($jdata['note']) ? $jdata['note'] : null ;
                $company_phone = $this->companies_api->getCompanyByPhone($phone);
                $company_code = $this->companies_api->getCompanyByCode($customer_code);

                if($name == NULL){
                    $this->response([
                        'status' => false,
                        'message' => 'Customer name is required',
                    ], REST_Controller::HTTP_BAD_REQUEST);
                    return false;
                }else{
                    $cg   = $this->site->getCustomerGroupByID($this->post('customer_group'));
                    $pg   = $this->site->getPriceGroupByID($this->post('price_group'));
                    $data = [
                        'date'                      => date('Y-m-d H:i:s'),
                        'code'                      => $this->post('code'),
                        'company'                   => $this->Settings->customer_detail ? '-' : '-',
                        'name'                      => $this->post('name'),
                        'phone'                     => $this->post('phone'),
                        'street_no'                 => $this->post('street_no') ? $this->post('street_no'): null,
                        'commune'                   => $this->post('commune') ? $this->post('commune') : $this->post('commune'),
                        'village'                   => $this->post('village') ? $this->post('village') : null,
                        'email'                     => $this->post('email'),
                        'vat_no'                    => $this->post('vat_no') ? $this->post('vat_no') : null,
                        'contact_person'            => $this->post('contact_person') ? $this->post('contact_person') : null,
                        'group_id'                  => '4',
                        'group_name'                => 'customer',
                        'customer_group_id'         => $this->post('customer_group') ? $this->post('customer_group') : 1,
                        'customer_group_name'       => $cg->name,
                        'price_group_id'            => $this->post('price_group') ? $this->post('price_group') : null,
                        'price_group_name'          => $this->post('price_group') ? $pg->name : null,
                        'address'                   => $this->post('address'),
                        'vat_no'                    => $this->post('vat_no'),
                        'city'                      => $this->post('city'),
                        'state'                     => $this->post('state'),
                        'country'                   => $this->post('country'),
                        'gender'                    => $this->post('gender'),
                        'age'                       => $this->post('age'),
                        'cf1'                       => $this->post('cf1'),
                        'cf2'                       => $this->post('cf2'),
                        'cf3'                       => $this->post('cf3'),
                        'cf4'                       => $this->post('cf4'),
                        'cf5'                       => $this->post('cf5'),
                        'cf6'                       => $this->post('cf6'),
                        'gst_no'                    => $this->post('gst_no'),
                        'zone_id'                   => $this->post('zone_id') ? $this->post('zone_id') : null,
                        'save_point'                => trim($this->post('save_point')),
                    ];
                    if(isset($jdata['locations'])){
                        foreach($jdata['locations'] as $item) {
                        // var_dump($item);

                            $locations[] = [
                                'name'              => $this->post('name'),
                                'phone'             => $this->post('phone'),
                                'address'           => $this->post('address'),
                                'city'              => $this->post('city'),
                                'latitude'          => $item['latitude'],
                                'longitude'         => $item['longitude'],
                            ];
                        }
                        // var_dump($locations);
                        // exit();
                    }else{
                        $locations =[];
                    }
                    
                }

                if(!empty($company_phone) || !empty($company_code)){
                    // Insert user data
                    $insert = $this->companies_api->addCompany($data,$locations);
                    if($insert){
                        $this->response([
                            'status' => TRUE,
                            'message' => 'Customer has been added successfully.',
                            'data' => $insert
                        ], REST_Controller::HTTP_OK);
                    }else{
                        $this->response("Some problems occurred, please try again.", REST_Controller::HTTP_BAD_REQUEST);
                    }
                }else{
                    // Set the response and exit
                    $this->response("Provide complete user ".$customer." info to add.", REST_Controller::HTTP_BAD_REQUEST);
                }
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Invalid JSON data',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        } else {
            $this->response([
                'status' => false,
                'message' => 'Invalid Request',
                'data' => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}
