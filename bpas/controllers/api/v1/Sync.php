
<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
class Sync extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->methods['index_get']['limit'] = 500;
        $this->load->admin_model('sync_model');
    }

    function getsales_post() 
    {

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $data = json_decode(file_get_contents('php://input'),true);

            $pos                = $data['pos'];
            $pos_items          = $data['pos_items'];
            $pos_payments       = $data['pos_payments'];
            $pos_acc_trans      = $data['pos_acc_trans'];
            $pos_acc_pay_trans  = $data['pos_acc_pay_trans'];
            $pos_registers      = $data['pos_registers'];
            $pos_register_items = $data['pos_register_items'];

            $result             = $this->sync_model->addPOS($pos,$pos_items,$pos_payments,$pos_acc_trans,$pos_acc_pay_trans,$pos_registers,$pos_register_items);
            if($result){
                echo json_encode($result);
            }else{
                echo json_encode(false);
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