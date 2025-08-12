<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Companies_api extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function countCompanies($filters = [])
    {
        if ($filters['group']) {
            $this->db->where('group_name', $filters['group']);
        }
        $this->db->from('companies');
        return $this->db->count_all_results();
    }

    public function getCompanies($filters = [])
    {
        // $uploads_url = base_url('assets/uploads/');
        // $this->db->select("id, code, name, type, slug, price, CONCAT('{$uploads_url}', image) as image_url, tax_method, tax_rate, unit");

        if ($filters['group']) {
            $this->db->where('group_name', $filters['group']);
        }

        if ($filters['name']) {
            $this->db->where('name', $filters['name']);
        } else {
            $this->db->order_by($filters['order_by'][0], $filters['order_by'][1] ? $filters['order_by'][1] : 'asc');
            $this->db->limit($filters['limit'], ($filters['start'] - 1));
        }

        return $this->db->get('companies')->result();
    }

    public function getCompany($filters)
    {
        if (!empty($companies = $this->getCompanies($filters))) {
            return array_values($companies)[0];
        }
        return false;
    }

    public function getCompanyUsers($company_id)
    {
        return $this->db->get_where('users', ['company_id' => $company_id])->result();
    }
    public function getAllCustomerGroups()
    {
        $q = $this->db->get('customer_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;


    }
    public function getAllPriceGroups()
    {
        $q = $this->db->get('price_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function addCompany($data = [],$locations=[])
    {
        if ($this->db->insert('companies', $data)) {
            $cid = $this->db->insert_id();
            $acc_setting = [
                'biller_id'  => $cid,
            ];
            if ($data['group_name'] == 'biller') {
                $this->db->insert('account_settings', $acc_setting);
            }
            if($locations){
                foreach($locations as $adr){
                    $adr['company_id'] = $cid;
                    $this->db->insert('addresses', $adr);
                }
            }
            return $cid;
        }
        return false;
    }
    public function getCompanyByPhone($phone = false,$group_name = false)
    {
        $q = $this->db->get_where('companies', array('phone' => $phone,'group_name'=> $group_name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getCompanyByCode($code = false)
    {
        $q = $this->db->get_where('companies', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
}
