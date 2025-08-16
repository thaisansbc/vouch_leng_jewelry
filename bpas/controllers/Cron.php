<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        ///$this->lang->admin_load('bpas');
    }

    

    public function setautocheckout()
    {  
      
       $query = $this->db->get_where('bpas_attendances', array('shift_status' => 1, 'shift_date'=> date('Y-m-d')));
       $daylasttime = date('H:i',strtotime('+1 minutes'));
       if($daylasttime < '23:58' && $daylasttime >= '23:59' ){
            $result = $query->result_array();
            if(!empty($result)){ 
                foreach ($result as $key => $value) { 
                    $data = array('shift_status'=>2);
                    $this->db->where('id', $value['id']);
                    $this->db->update('bpas_attendances', $data); 
                }
            }
        }
    }

}
