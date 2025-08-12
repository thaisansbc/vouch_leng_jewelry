<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Contracts_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function addContract($data = array(), $items = array())
    {
        if ($this->db->insert('contracts', $data)) {
            return true;
        }
        return false;
    }

    public function editContract($id, $data, $items = array())
    {
        // $this->resetSaleActions($id, FALSE, TRUE);

        if ($this->db->update('contracts', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        if ($this->db->delete('contracts', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
    
    public function getAllContractById($id){
        $this->db->select('contracts.*')
            ->where('id', $id);
        $q = $this->db->get('contracts');
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }


}