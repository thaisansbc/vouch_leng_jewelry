<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Synchronize extends MY_Controller{
    function __construct(){
        parent::__construct();
		if (!$this->config->item('server_local')) {
			admin_redirect("welcome");
        }
		$this->load->library('form_validation');
        $this->load->admin_model('sync_model');
		
    }
	//-----------local-----------------
	function push_pos(){
		if(site_url() == $this->config->item('server_url')){
			admin_redirect("welcome");
		}
		$pos = $this->sync_model->getPOS();
		if($pos){
			$pos_acc_trans 		= false;
			$pos_acc_pay_trans 	= false;
			$pos_items 			= $this->sync_model->getPOSItems();
			$pos_payments 		= $this->sync_model->getPOSPayments();
			if($this->Settings->module_account == 2){
				$pos_acc_trans 		= $this->sync_model->getPOSAccTrans();
				$pos_acc_pay_trans 	= $this->sync_model->getPOSPaymentAccs();
			}
			$pos_registers 		= $this->sync_model->getPOSRegisters();
			//$pos_register_items = $this->sync_model->getPOSRegisterItems();
			$q = json_encode(array(
					'pos'				=>$pos,
					'pos_items'			=>$pos_items,
					//'pos_stockmoves'	=>$pos_stockmoves,
					'pos_payments'		=>$pos_payments,
					'pos_acc_trans'		=>$pos_acc_trans,
					'pos_acc_pay_trans'	=>$pos_acc_pay_trans,
					'pos_registers'		=>$pos_registers
					//'pos_register_items'=>$pos_register_items
				));
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->config->item("server_url")."synchronize/get_pos");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('data' => $q)));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			if($result != 'false' && $result){
				$data = json_decode($result);
				$this->sync_model->updatePushed($data);
			}
			curl_close ($ch);
		}
		admin_redirect("welcome");
	}
}
