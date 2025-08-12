<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Leads_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function deleteLead($id)
    {
        if ($this->db->delete('companies', ['id' => $id, 'group_name' => 'lead'])) {
            return true;
        }
        return false;
    }
    public function getAllGroup()
    {
        $q = $this->db->get('containers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getContainers() {

        $this->db->order_by('container_order','ASC');
        $q = $this->db->get('containers');
        if ($q->num_rows() > 0) {
          
            return $q->result_array();
        }
        return FALSE;
    }
    public function addGroup($data)
    {
        if ($this->db->insert('containers', $data)) {
            return true;
        }
        return false;
    }
    public function updateGroup($id, $data = [])
    {
        if ($this->db->update('containers', $data, ['container_id' => $id])) {
            return true;
        }
        return false;
    }
    public function leadHasGroups($group_id)
    {
        $q = $this->db->get_where('companies', ['group_name' => 'lead','lead_group' => $group_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function deleteGroup($id)
    {
        if ($this->db->delete('containers', ['container_id' => $id])) {
            return true;
        }
        return false;
    }
    public function getGroupByID($id)
    {
        $q = $this->db->get_where('containers', ['container_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
}
