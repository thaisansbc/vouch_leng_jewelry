<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Companies_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addAddress($data)
    {
        if ($this->db->insert('addresses', $data)) {
            return true;
        }
        return false;
    }
    public function getAddresses($id = false, $company_id = false, $color_marker = false){
        if($id && $id != "false"){
            $this->db->where("addresses.id",$id);
        }
        if($company_id && $company_id != "false"){
            $this->db->where("company_id",$company_id);
        }
        if($color_marker && $color_marker != "false"){
            $this->db->where("color_marker",$color_marker);
        }
        $this->db->select("addresses.*,
                            companies.name,
                            companies.company,
                            addresses.name as address_name,
                            addresses.phone as address_phone
                        ");
        $this->db->join("companies","companies.id = addresses.company_id","inner");
        $q = $this->db->get("addresses");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addConsumer($data)
    {
        if ($this->db->insert('consumers', $data)) {
            return true;
        }
        return false;
    }

    public function addCompanies($data = [])
    {
        if ($this->db->insert_batch('companies', $data)) {
            return true;
        }
        return false;
    }

    public function addCompany($data = [])
    {
        if ($this->db->insert('companies', $data)) {
            $cid = $this->db->insert_id();
            $acc_setting = [
                'biller_id'  => $cid,
            ];
            if ($data['group_name'] == 'biller') {
                $this->db->insert('account_settings', $acc_setting);
            }
            return $cid;
        }
        return false;
    }

    public function addDeposit($data, $cdata, $accTranPayments = array())
    {
        if ($this->db->insert('deposits', $data)) {
            $payment_id = $this->db->insert_id();
            $this->db->update('companies', $cdata, ['id' => $data['company_id']]);

            
            if($accTranPayments){
                foreach($accTranPayments as $accTranPayment){
                    $accTranPayment['tran_no']= $payment_id;
                    $this->db->insert('gl_trans', $accTranPayment);
                }
            }

            if ($this->site->getReference('sd') == $data['reference']) {
                $this->site->updateReference('sd');
            }

            return true;
        }
        return false;
    }

    public function deleteAddress($id)
    {
        if ($this->db->delete('addresses', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteBiller($id)
    {
        if ($this->getBillerSales($id)) {
            return false;
        }
        if ($this->db->delete('companies', ['id' => $id, 'group_name' => 'biller'])) {
            $this->db->delete('order_ref', array('bill_id' => $id));
            return true;
        }
        return false;
    }
    public function deleteCustomer($id)
    {
        if ($this->getCustomerSales($id)) {
            return false;
        }
        if ($this->db->delete('companies', ['id' => $id, 'group_name' => 'customer']) && $this->db->delete('users', ['company_id' => $id])) {
            return true;
        }
        return false;
    }
    public function deleteDriver($id)
    {
        
        if ($this->db->delete('companies', ['id' => $id, 'group_name' => 'driver']) && $this->db->delete('users', ['company_id' => $id])) {
            return true;
        }
        return false;
    }
    public function deleteDeposit($id)
    {
        $deposit = $this->getDepositByID($id);
        $company = $this->getCompanyByID($deposit->company_id);
        $cdata   = [
            'deposit_amount' => ($company->deposit_amount - $deposit->amount),
        ];
        if ($this->db->update('companies', $cdata, ['id' => $deposit->company_id]) && $this->db->delete('deposits', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteSupplier($id)
    {
        if ($this->getSupplierPurchases($id)) {
            return false;
        }
        if ($this->db->delete('companies', ['id' => $id, 'group_name' => 'supplier']) && $this->db->delete('users', ['company_id' => $id])) {
            return true;
        }
        return false;
    }

    public function getAddressByID($id)
    {
        $q = $this->db->get_where('addresses', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllBillerCompanies()
    {
        $q = $this->db->get_where('companies', ['group_name' => 'biller']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllCustomerCompanies()
    {
        $q = $this->db->get_where('companies', ['group_name' => 'customer']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
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
        return false;
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
        return false;
    }

    public function getAllSupplierCompanies()
    {
        $q = $this->db->get_where('companies', ['group_name' => 'supplier']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getBillerSales($id)
    {
        $this->db->where('biller_id', $id)->from('sales');
        return $this->db->count_all_results();
    }

    public function getBillerSuggestions($term, $limit = 10)
    {
        $this->db->select('id, company as text');
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%') ");
        $q = $this->db->get_where('companies', ['group_name' => 'biller'], $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getStoreSuggestions($term, $limit = 10)
    {
        $this->db->select('id, company as text');
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%') ");
        $q = $this->db->get_where('companies', ['group_name' => 'biller'], $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getCompanyAddresses($company_id)
    {
        $q = $this->db->get_where('addresses', ['company_id' => $company_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCompanyByEmail($email)
    {
        $q = $this->db->get_where('companies', ['email' => $email], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
   public function getProductMultiByID($id)
    {   
       
        $this->db->select('products.*');
       $this->db->where_in('id',explode(',', $id));
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getCompanyID($id)
    {   
       
        $this->db->select('companies.*, '.$this->db->dbprefix('projects') . '.project_name as project_name');
        $this->db->join('projects', 'projects.project_id=companies.projects', 'left');
        $q = $this->db->get_where('companies', ['companies.id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
     public function getCompanyByID($id)
    {
        $q = $this->db->get_where('companies', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getConsumerByID($id)
    {
        $q = $this->db->get_where('consumers', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getCompanyConsumers($company_id)
        {
            $q = $this->db->get_where('consumers', ['company_id' => $company_id]);
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
                return $data;
            }
            return false;
        }
    public function getCompanyUsers($company_id)
    {
        $q = $this->db->get_where('users', ['company_id' => $company_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCustomerSales($id)
    {
        $this->db->where('customer_id', $id)->from('sales');
        return $this->db->count_all_results();
    }

    public function getCustomerSuggestions_08_08_2022($term, $limit = 10)
    {
        $this->db->select("id, (CASE WHEN company = '-' THEN name ELSE CONCAT(company, ' (', name,' | ',phone, ')') END) as text, (CASE WHEN company = '-' THEN name ELSE CONCAT(company, ' (', name, ')') END) as value, phone", false);
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR phone LIKE '%" . $term . "%') ");
        $q = $this->db->get_where('companies', ['group_name' => 'customer'], $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getCustomerSuggestions($term, $limit = 10)
    {
        $this->db->select("
            {$this->db->dbprefix('companies')}.id, 
            (CASE WHEN 
                {$this->db->dbprefix('companies')}.company = '-' THEN 
                {$this->db->dbprefix('companies')}.name ELSE CONCAT({$this->db->dbprefix('companies')}.company, ' (', {$this->db->dbprefix('companies')}.name, ' | ', {$this->db->dbprefix('companies')}.phone, ')') END) as text, 
            (CASE WHEN 
                {$this->db->dbprefix('companies')}.company = '-' THEN {$this->db->dbprefix('companies')}.name ELSE CONCAT({$this->db->dbprefix('companies')}.company, ' (', {$this->db->dbprefix('companies')}.name, ')') END) as value, 
                {$this->db->dbprefix('companies')}.phone", 
        false);
        if ($this->Settings->module_school) {
            $this->db->where(" 
                (
                    {$this->db->dbprefix('companies')}.id LIKE '%" . $term . "%' OR 
                    {$this->db->dbprefix('companies')}.name LIKE '%" . $term . "%' OR 
                    {$this->db->dbprefix('companies')}.company LIKE '%" . $term . "%' OR 
                    {$this->db->dbprefix('companies')}.email LIKE '%" . $term . "%' OR 
                    {$this->db->dbprefix('companies')}.phone LIKE '%" . $term . "%' OR 
                    {$this->db->dbprefix('sh_students')}.code LIKE '%" . $term . "%' OR
                    {$this->db->dbprefix('sh_students')}.number LIKE '%" . $term . "%'
                ) 
            ");
            $this->db->join('sh_students', 'sh_students.id=companies.student_id', 'left');
        } else {
            $this->db->where(" 
                (
                    {$this->db->dbprefix('companies')}.id LIKE '%" . $term . "%' OR 
                    {$this->db->dbprefix('companies')}.name LIKE '%" . $term . "%' OR 
                    {$this->db->dbprefix('companies')}.company LIKE '%" . $term . "%' OR 
                    {$this->db->dbprefix('companies')}.email LIKE '%" . $term . "%' OR 
                    {$this->db->dbprefix('companies')}.phone LIKE '%" . $term . "%'
                ) 
            ");
        }
        $q = $this->db->get_where('companies', ['group_name' => 'customer'], $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }
   
    public function getPaymentByDepositID($deposit_id)
    {
        $q = $this->db->get_where('payments', array('deposit_id' => $deposit_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function deleteSupplierDeposit($id){
        $deposit = $this->getDepositByID($id);
        
        if($this->db->delete('deposits',array('id'=>$id))){
            $this->db->update('companies', array('deposit_amount' => 0), array('id' => $deposit->company_id));
            return true;
        }
            return false;
    }
    public function updateSupplierDeposit($id, $data, $cdata, $payment)
    {
        // $this->erp->print_arrays($data, $cdata, $payment);
        if ($this->db->update('deposits', $data, array('id' => $id)) && $this->db->update('companies', $cdata, array('id' => $data['company_id']))) {
            $this->db->update('payments', $payment , array('purchase_deposit_id' => $id));
            return true;
        }
        return false;
    }
    public function getPaymentBySupplierDeposit($purchase_deposit_id)
    {
        $q = $this->db->get_where('payments', array('purchase_deposit_id' => $purchase_deposit_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getDepositByID($id)
    {
        $q = $this->db->get_where('deposits', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function ReturnDeposit($data, $cdata, $payment)
    {
        if ($this->db->insert('deposits', $data)) {
                $deposit_id = $this->db->insert_id();
                $this->db->update('companies', $cdata, array('id' => $data['company_id']));
                if($payment){
                    $payment['return_deposit_id'] = $deposit_id;
                    if ($this->db->insert('payments', $payment)) {
                        if ($this->site->getReference('pp') == $payment['reference_no']) {
                            $this->site->updateReference('pp');
                        }
                        if ($payment['paid_by'] == 'gift_card') {
                            $gc = $this->site->getGiftCardByNO($payment['cc_no']);
                            $this->db->update('gift_cards', array('balance' => ($gc->balance - $payment['amount'])), array('card_no' => $payment['cc_no']));
                        }
                        return true;
                    }
                }
            return true;
        }
        return false;
    }
    public function getDepositItems($id)
    {
        $q = $this->db->get_where('deposits', array('company_id' => $id, 'paid_by' => 'deposit'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getSupplierPurchases($id)
    {
        $this->db->where('supplier_id', $id)->from('purchases');
        return $this->db->count_all_results();
    } 
    public function getSupplierSuggestions($term, $limit = 10)
    {
        $this->db->select("id, (CASE WHEN company = '-' THEN name ELSE CONCAT(company, ' (', name, ')') END) as text", false);
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR phone LIKE '%" . $term . "%') ");
        $q = $this->db->get_where('companies', ['group_name' => 'supplier'], $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    } 
    public function updateAddress($id, $data)
    {
        if ($this->db->update('addresses', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function updateCompany($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('companies', $data)) {
            return true;
        }
        return false;
    }
    // public function updateCompany_($id, $data = [])
    // {
    //     $this->db->where('id', $id); 
    //    $C = $this->db->query("SHOW COLUMNS FROM {$this->db->dbprefix('order_ref')} WHERE `Field`= '" . $data["code"] . "'");
    //    $A[] = NULL;
    //    if ($C->num_rows() > 0) {
    //         foreach (($C->result()) as $row) {
    //             $A[] = $row;
    //         }
    //     } 
    //     if ($this->db->update('companies', $data)) {
    //         if ($C->num_rows() > 0) {
    //             $this->db->query("UPDATE {$this->db->dbprefix('order_ref')} SET  " . $data["code"] . " = asasasa");
    //         }else{ 
    //             $this->db->query("ALTER TABLE {$this->db->dbprefix('order_ref')} ADD " . $data["code"] . " INT(11) NOT NULL DEFAULT '1'");
    //         } 
    //         return true;
    //     }
    //     return false;
    // }
    public function updateConsumer($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('consumers', $data)) {
            return true;
        }
        return false;
    }
     public function updateCustomerDeposit($id, $data, $cdata, $accTranPayments = array())
    {
        if ($this->db->update('deposits', $data, ['id' => $id])){
             //account---
            $this->site->deleteAccTran('CustomerDeposit',$id);
            //---end account       
            if($accTranPayments){
                foreach($accTranPayments as $accTranPayment){
                    $this->db->insert('gl_trans', $accTranPayment);
                }
            }
            $this->db->update('companies', $cdata, ['id' => $data['company_id']]);
            return true;
        }
        return false;
    }
    public function updateDeposit($id, $data, $cdata, $accTranPayments = array())
    {
        if ($this->db->update('deposits', $data, ['id' => $id])){
             //account---
            $this->site->deleteAccTran('Deposit',$id);
            //---end account       
            if($accTranPayments){
                foreach($accTranPayments as $accTranPayment){
                    $this->db->insert('gl_trans', $accTranPayment);
                }
            }
            $this->db->update('companies', $cdata, ['id' => $data['company_id']]);
            return true;
        }
        return false;
    }
    public function getDefaults( $id=null)
    {
        // var_dump($id);exit();
        $this->db->select('*');
        $this->db->from('account_settings');
        $this->db->join('gl_charts', 'account_settings.default_open_balance=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getPOReference(){
        $this->db->where('payment_status',NULL);
        $this->db->select('reference_no');
        $this->db->from('purchases_order');
        $q=$this->db->get();
            if($q){
                return $q->result();
            }else{
                return false;
            }
    }
    public function createDriver($data = array()) {
        if($data) {
            if($this->db->insert('companies', $data)) {
                return true;
            }
        }
        return false;
    }
    public function delete_driver($id=null){
        if($this->db->delete('companies',array('id'=>$id))){
            return true;
        }
            return false;
    }
    public function deleteConsumer($id=null){
        if($this->db->delete('consumers',array('id'=>$id))){
            return true;
        }
            return false;
    }
    public function saveDriver($id=null,$data = array()) {
       
        if($data) {
            if($this->db->update('companies', $data,array('id'=>$id)))  {
                return true;
            }
        }
        return false;
    }
    public function addSupplierDeposit($data, $cdata, $payment = array(),$po,$reference_no)
    {
        //$this->erp->print_arrays($data, $cdata, $payment);
        if ($this->db->insert('deposits', $data)) {
            $deposit_id = $this->db->insert_id();
    
            if ($this->site->getReference('pp') == $data['reference']) {
                $this->site->updateReference('pp');
            }else{}
            
            //$this->db->update('purchases_order', $po, array('reference_no' => $reference_no));
            
            $this->db->update('companies', $cdata, array('id' => $data['company_id']));
            if($payment){
                $payment['purchase_deposit_id'] = $deposit_id;
                if ($this->db->insert('payments', $payment)) {
                    if ($this->site->getReference('pp') == $payment['reference_no']) {
                        $this->site->updateReference('pp');
                    }
                    if ($payment['paid_by'] == 'gift_card') {
                        $gc = $this->site->getGiftCardByNO($payment['cc_no']);
                        $this->db->update('gift_cards', array('balance' => ($gc->balance - $payment['amount'])), array('card_no' => $payment['cc_no']));
                    }
                    return true;
                }
            }
            return true;
        }
        return false;
    }
    public function clear_award_points($id)
    {
        if ($this->db->update('companies', ['award_points' => 0], ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function getCustomerGroupByName($name = false) 
    {
        $q = $this->db->get_where('customer_groups', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getPriceGroupByName($name = false)
    {
        $q = $this->db->get_where('price_groups', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getCompanyByCodeGroupName($code = false,$group_name = false)
    {
        $q = $this->db->get_where('companies', array('code' => $code,'group_name'=> $group_name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getCompanyByPhone($phone = false,$group_name = false)
    {
        $q = $this->db->get_where('companies', array('phone' => $phone,'group_name'=> $group_name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addCustomerPackage($data = false){
        if($data && $this->db->insert("customer_package",$data)){
            return true;
        }
        return false;
    }
    public function getCustomerPackageByID($id = false){
        $q = $this->db->get_where("customer_package", array("customer_package.id"=>$id));
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
    
    public function updateCustomerPackage($id = false, $data = false){
        if($id && $this->db->update("customer_package",$data,array("id"=>$id))){
            return true;
        }
        return false;
    }
    public function deleteCustomerPackage($id = false){
        if($id && $this->db->delete("customer_package", array("id"=>$id))){
            return true;
        }
        return false;
    }
    public function getAllCustomerPackage()
    {
        $q = $this->db->get('customer_package');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getCustomerInfoByID($id)
    {   
        $this->db->select('companies.*,customer_package.name as package,CONCAT('.$this->db->dbprefix('users').'.last_name," ",'.$this->db->dbprefix('users').'.first_name) as username');
        $this->db->join('customer_package', 'customer_package.id=companies.service_package','LEFT');
        $this->db->join('users', 'users.id=companies.agent','LEFT');
        $q = $this->db->get_where('companies', ['companies.id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getLastCompanies($group_name)
    {
        $this->db->order_by('id','DESC');
        $q = $this->db->get_where('companies', ['group_name' => $group_name]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getCompaniesbyID($id = false)
    {
        $q = $this->db->get_where('companies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    //-------
    public function getDriverByID($id = false)
    {
        $q = $this->db->get_where('companies', array('group_name' => 'driver','id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getDrivers()
    {
        $q = $this->db->get_where('companies', ['group_name' => 'driver']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getVehicleByID($id = false){
        $q = $this->db->get_where('vehicles',array('id'=>$id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function addVehicle($data = false){
        if($data && $this->db->insert('vehicles',$data)){
            return true;
        }
        return false;
    }
    public function updateVehicle($id = false, $data = false){
        if($id && $data && $this->db->update('vehicles',$data,array('id'=>$id))){
            return true;
        }
        return false;
    }
    public function deleteVehicle($id = false){
        if($id && $this->db->delete('vehicles',array('id'=>$id))){
            return true;
        }
        return false;
    }

    public function addPrefix($bill_id = false, $prefix = false)
    {
        $curDate    = date('Y-m-d');
        $query      = $this->db->get('order_ref');
        $fields     = $query->list_fields();
        $list_field = 'date= "'.$curDate.'", bill_id='.$bill_id.', bill_prefix = "'.$prefix.'"';
        $i = 0;
        foreach ($fields as $f) {
            if ($i > 3) {
                $list_field .= ',`'.$f.'`="1"';
            } else {
                $i++;
            }
        }
        if ($this->db->query("INSERT INTO ".$this->db->dbprefix('order_ref')." SET ".$list_field."")) {
            return true;
        }
        return FALSE;
    }
    
    public function updatePrefix($bill_id = false, $prefix = false)
    {
        $this->db->where('bill_id', $bill_id);
        if ($this->db->update('order_ref', ['bill_prefix' => $prefix])) {
            return true;
        }
        return false;
    }
    
    public function getPrefixByBill($bill_id = false)
    {
        $q = $this->db->get_where('order_ref', array('bill_id' => $bill_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getAllLevel()
    {
        $q = $this->db->get('sh_grades');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
}
