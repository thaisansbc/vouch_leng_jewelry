<?php defined('BASEPATH') or exit('No direct script access allowed');

class Approved_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        // $this->default_biller_id = $this->site->default_biller_id();
    }
     public function getApprovedStatus($id)
    {
        $this->db->select('approved_status, checked_status, rejected_status, delivery_status, unapproved_status, preparation_status, issued_status, acknowledged_status, received_status, stock_received_status, quality_checked_status, procurement_status');
        $q = $this->db->get_where('approved', $id, 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getApprovedByID_($id){
        $this->db->select('approved_by, checked_by, rejected_by, delivery_by, unapproved_by, preparation_by, issued_by, acknowledged_by, received_by, stock_received_by, quality_checked_by, procurement_by, approved_status, checked_status, rejected_status, delivery_status, unapproved_status, preparation_status, issued_status, acknowledged_status, received_status, stock_received_status, quality_checked_status, procurement_status, approved_date, checked_date, rejected_date, delivery_date, unapproved_date, preparation_date, issued_date, acknowledged_date, received_date, stock_received_date, quality_checked_date, procurement_date');
        $q = $this->db->get_where('approved', $id, 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getApprovedByID($id)
    {
        $q = $this->db->get_where('approved', $id, 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function changeStatus($id, $array, $request, $datas)
    {
        $q = $this->db->get('approved');
        $data[] = 0;
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $data[] = $row->$array;
            }
            $result = true;
            foreach($data as $i){
                if($id == $i){ 
                    $result = false;
                } 
            }
            if($result) {
                if ($this->db->insert('approved', $datas)) {
                    return true;
                }
                return false;
            }
            if ($this->db->update('approved', $datas, $request)) {
                return true;
            }
            return false;
        }else{
            if ($this->db->insert('approved', $datas)) {
                return true;
            }
            return false;
        }
        return false;
    }
    public function change_Status($id, $col, $datas = array(), $request, $note = null, $post = array(), $table = null)
    {
        $size   = sizeof($datas);
        $p      = $this->db->get_where('approved', $request);
        $data[] = 0;
        if ($p->num_rows() == 0) {
            $this->db->insert('approved', $request);
        }
        $q      = $this->db->get('approved');
        if ($q->num_rows() > 0) {
            for ($x = 0; $x < $size; $x++) {
                $est = $datas[$x];
                $this->db->update('approved', $est, $request);
            }
            $this->syncStatus($post, $request, $table);
            return true;
        }
        return false;
    }   
    public function syncStatus($post, $request = null, $table = null)
    {
        $status = 'approved';
        foreach($post as $key => $value){
            if($value != 'approved' && $key != 'update'){
                $status = 'requested';
            }
        }
        foreach($request as $key => $value) {
            $id = $value;
        }
        if($table == "sales_order"){  
            if($this->db->update($table, ['order_status' => $status], ['id' => $id])){
                return true;
            }
        }else{
            if($this->db->update($table, ['status' => $status], ['id' => $id])){
                return true;
            }
        }
        return false;
    }
    // public function updateApproved($request,$data)
    // {
    //     if ($this->db->update('approved', $data, $request)) {
    //         return true;
    //     }
    //     return false;
    // }
    // public function addApporved($data){

    //     if($this->db->insert('approved', $data)){
    //          return true;
    //     }
    //     return false;
    // }
    public function change_Status_($id, $array = array(), $datas = array(), $note = null)
    {
        $sizeof_key = sizeof($array);
        $q = $this->db->get('approved');
        $data[] = 0;
        if ($q->num_rows() > 0) { 
                    foreach($q->result() as $row){
                        for ($i = 0; $i < $sizeof_key; $i++) { 
                            $data[] = $row->$array[$i];
                        }
                    } 
                    $result = true;
                    foreach($data as $i){
                        if($id == $i){ 
                            $result = false;
                        } 
                    } 
                    if($result) {
                        if ($this->db->insert('approved', $datas)) {
                            return true;
                        }
                        return false;
                    } 
                    if ($this->db->update('approved', $datas, $request)) {
                        return true;
                    }
                    return false; 
                }else{  
                    if ($this->db->insert('approved', $datas)) {
                        return true;
                    }
                    return false;
        }
        return false;

    }  
    public function getSignbox($group_id)
    {
        $this->db->select('approved_by, preparation_by, 
            issued_by, acknowledged_by, 
            received_by, stock_received_by,
            quality_checked_by, procurement_by');
        $q = $this->db->get_where('approved_by', ['group_id' => $group_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
}
