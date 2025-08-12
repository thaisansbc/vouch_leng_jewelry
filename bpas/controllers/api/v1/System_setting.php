<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class System_setting extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->methods['index_get']['limit'] = 500;
        $this->load->api_model('setting_api');
    }
    public function index_get()
    {
        
   
        if ($getsetting = $this->setting_api->getSystem_Setting()) {

            $this->response($getsetting, REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'message' => 'No expense record found.',
                'status'  => false,
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        
    }
    public function categories_get()
    {
        if ($this->input->server('REQUEST_METHOD') == 'GET') {
            if ($getsetting = $this->setting_api->getAllCategories()) {
                $this->response($getsetting, REST_Controller::HTTP_OK);
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

    public function brands_get()
    {
        if ($this->input->server('REQUEST_METHOD') == 'GET') {
            if ($getbrand = $this->setting_api->getAllbrands()) {

                $this->response($getbrand, REST_Controller::HTTP_OK);
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
    public function warehouse_get()
    {
        if ($this->input->server('REQUEST_METHOD') == 'GET') {
            if ($getwarehouse = $this->setting_api->getAllWarehouse()) {

                $this->response($getwarehouse, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No warehouse record found.',
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
    public function exchange_rate_get()
    {
        if ($this->input->server('REQUEST_METHOD') == 'GET') {
            if ($getwarehouse = $this->setting_api->getAllCurrencies()) {

                $this->response($getwarehouse, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No currencies record found.',
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
}
