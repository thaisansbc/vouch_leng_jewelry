<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Pos_setting extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->methods['index_get']['limit'] = 500;
        $this->load->api_model('setting_api');
    }
    public function index_get()
    {
        
   
        if ($getsetting = $this->setting_api->getPOS_Setting()) {

            $this->response($getsetting, REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'message' => 'No expense record found.',
                'status'  => false,
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        
    }
}
