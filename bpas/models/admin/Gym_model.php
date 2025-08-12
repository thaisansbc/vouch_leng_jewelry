<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Gym_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
   
    public function generate_membership_invoice($data = [], $items = [], $payment = [], $si_return = [], $accTrans = array(), $accTranPayments = array(), $payment_status = null, $commission_product = null)
    {  
        $this->db->trans_start();
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();

            //=========Add Accounting =========//
            if($accTrans){
                foreach($accTrans as $accTran){
                    $accTran['tran_no'] = $sale_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            if ((isset($payment['amount']) && $payment['amount'] == '')) {
                if($accTranPayments){
                    foreach($accTranPayments as $accTranPayment){
                        $accTranPayment['tran_no'] = $sale_id;
                        $accTranPayment['payment_id'] = $sale_id;
                        $this->db->insert('gl_trans', $accTranPayment);
                    }
                }
            }
            //=========End Accounting =========//
            if ($this->site->getReference('so') == $data['reference_no']) {
                $this->site->updateReference('so');
            } elseif ($this->site->getReference('st') == $data['reference_no']) {
                $this->site->updateReference('st');
            }
            
            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();

                if ($this->Settings->product_option && isset($item['max_serial'])) {
                    $this->db->update('product_options', ['start_no' => $item['serial_no'], 'last_no' => $item['max_serial']], ['option_id' => $item['option_id']]);
                }
                $aprroved['purchase_request_id'] = $sale_id;
                $this->db->insert('approved', $aprroved);
                if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment' && empty($si_return)) {
                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id']      = $sale_id;
                            $item_cost['date']         = date('Y-m-d H:i:s', strtotime($data['date']));
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id']      = $sale_id;
                                $ic['date']         = date('Y-m-d H:i:s', strtotime($data['date']));
                                if (!isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }
                }
            }

            if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment') {
                $this->site->syncPurchaseItems($cost);
            }

            if (!empty($si_return)) {
            
                foreach ($si_return as $return_item) {
                    $product = $this->site->getProductByID($return_item['product_id']);

                    if ($product->type == 'combo' && $product->module_type != "property") {
                        $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                        foreach ($combo_items as $combo_item) {
                            $this->UpdateCostingAndPurchaseItem($return_item, $combo_item->id, ($return_item['quantity'] * $combo_item->qty));
                        }
                    } else{
                        $this->UpdateCostingAndPurchaseItem($return_item, $return_item['product_id'], $return_item['quantity'],$return_item['expiry']);
                    }
                }

                $q=$this->db->get_where('sales', ['id' => $data['sale_id']],1);
                if ($q->num_rows() > 0) {
                    $return_sale_total_ = ($q->row()->return_sale_total ? $q->row()->return_sale_total : 0);
                }
                
                $this->db->update('sales', ['return_sale_ref' => $data['return_sale_ref'], 'surcharge' => $data['surcharge'],'return_sale_total' => ($data['grand_total'] + $return_sale_total_), 'return_id' => $sale_id], ['id' => $data['sale_id']]);

                $customer = $this->site->getCompanyByID($data['customer_id']);

                if(isset($data['saleman_by'])){
                    $staff = $this->site->getUser($data['saleman_by']);
                }

            }
            if(isset($data['saleman_by'])){
                $staff = $this->site->getUser($data['saleman_by']);
            }
               

            if ($data['payment_status'] == 'paid') {
                $this->site->update_property_status($sale_id,'sold');
            }
            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                $payment['sale_id'] = $sale_id;
          
                    if ($payment['paid_by'] == 'deposit') {
                        $customer = $this->site->getCompanyByID($data['customer_id']);
                        $this->db->update(
                            'companies', 
                            [
                                'deposit_amount' => ($customer->deposit_amount - $payment['amount']),
                                'deposit_amount_usd' => ($customer->deposit_amount_usd - $payment['amount_usd']),
                                'deposit_amount_khr' => ($customer->deposit_amount_khr - $payment['amount_khr']),
                                'deposit_amount_thb' => ($customer->deposit_amount_thb - $payment['amount_thb']),
                            ], 
                            ['id' => $customer->id]);
                    }
                    $this->db->insert('payments', $payment);
                
                //=========Add Accounting =========//
                $payment_id = $this->db->insert_id();

                if($accTranPayments){
                    foreach($accTranPayments as $accTranPayment){
                        $accTranPayment['tran_no'] = $sale_id;
                        $accTranPayment['payment_id']= $payment_id;
                        if (empty($accTranPayment['reference_no'])) {
                            $accTranPayment['reference_no'] = $payment['reference_no'];
                        }
                        $this->db->insert('gl_trans', $accTranPayment);
                    }
                }
                 //=========End Accounting =========//

                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
                if ($this->site->getReference('pp') == $payment['reference_no']) {
                    $this->site->updateReference('pp');
                }
                $this->site->syncSalePayments($sale_id);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Sales_model.php)');
        } else {
            return $sale_id;
        }

        return false;
    }
       public function update_membership_invoice($id, $data, $items = [], $accTrans = array(), $accTranPayments = array(), $commission_product = null)
    {  
       
        // var_dump($items);
        // exit(0);
        $this->db->trans_start(); 
        $this->resetSaleActions($id, false, true);
        if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment') {
            // $this->Settings->overselling = true;
            $cost                        = $this->site->costing($items, true);
        }
        $saleman_commission = $this->site->getCommissionsBySaleID($id);
        $saleman = $this->site->getUser($data['saleman_by']);

        if($data['reference_no'] != $this->getSaleById($id)->reference_no){
            if($data['order_tax_id'] == 1){
                $this->site->updateReference('so');
            } else {
                $this->site->updateReference('st');
            }
        }

        if ($this->db->update('sales', $data, ['id' => $id]) && $this->db->delete('sale_items', ['sale_id' => $id]) && $this->db->delete('costing', ['sale_id' => $id])) {
            $total_commissions = $saleman->commission_product - $saleman_commission->commission;
            $this->db->update('users', ['commission_product' => $total_commissions], ['id' => $data['saleman_by']]);
            $this->site->deleteAccTran('Sale',$id);

            //if($data['paid'] == 0){
                $this->site->deleteSalePayment('Payment',$id);
            //}
            
            if($accTrans){
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            if($accTranPayments){
                $this->db->insert_batch('gl_trans', $accTranPayments);
            }
            $commission_product = 0;
            foreach ($items as $item) {
                $this->db->update('product_options', ['start_no' => $item['max_serial'], 'last_no' => $item['max_serial']], ['option_id' => $item['option_id']]);
                $commission_product += $item['commission'];
                $item['sale_id'] = $id;

                $this->db->insert('sale_items', $item);
                
                $sale_item_id = $this->db->insert_id(); 
                if (($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment') && $this->site->getProductByID($item['product_id'])) {
                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id']      = $id;
                            $item_cost['date']         = date('Y-m-d H:i:s', strtotime($data['date']));
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id']      = $id;
                                $item_cost['date']  = date('Y-m-d H:i:s', strtotime($data['date']));
                                if (!isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }
                }
            } 
            if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment') {
                $this->site->syncPurchaseItems($cost);
            }
            $this->site->syncSalePayments($id); 
            $this->site->syncQuantity($id);
            $sale = $this->getInvoiceByID($id);
            $customer = $this->site->getCompanyByID($data['customer_id']);
            if($customer->save_point){
                $this->bpas->update_award_points($data['grand_total'], $data['customer_id'], null);
            }
            $staff = $this->site->getUser($sale->saleman_by);
            if($staff->save_point){
                $this->bpas->update_award_points($data['grand_total'], null, $sale->saleman_by);
            }
            $ucommission = $staff->commission_product + $commission_product;
            $this->db->update('users', ['commission_product' => $ucommission], ['id' => $sale->saleman_by]);
        } 

        $this->db->trans_complete(); 
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Sales_model.php)');
        } else {
            $request_sale = $this->getEditSaleRequestBySaleID($id);
            if($request_sale == false){ 
                return true;
            }else{
                $this->db->update('sales_edit_request', ['active' => 0,'created_by' => $this->session->userdata('user_id')], ['id' => $request_sale->id]);
                return true;
            }
            
        }
        return false;
    }
    public function getEditSaleRequestBySaleID($id)
    {
        $q = $this->db->get_where('sales_edit_request', ['active'=>1, 'sale_id' => $id, 'created_by' => $this->session->userdata('user_id')], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
        public function getPurchaseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
     public function getInvoiceByID($id)
    {
        $q = $this->db->get_where('sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
      public function getSaleCosting($sale_id)
    {
        $q = $this->db->get_where('costing', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

      public function getAllInvoiceItems($sale_id, $return_id = null)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, 
                product_variants.name as variant, 
                units.code as base_unit_code,
                products.slug,
                products.price, 
                sale_units.name as name_unit,
                products.code, 
                products.image, 
                IF('.$this->db->dbprefix('products').'.currency ="KHR", "៛", "$") as currency,
                products.details as details, 
                products.hsn_code as hsn_code, 
                products.second_name as second_name, 
                products.unit as base_unit_id, 
                products.category_id,
                products.subcategory_id,
                products.cf1 as width,
                products.second_name,
                products.weight,
                products.product_details,
                products.cf2 as length,
                products.cf3 as product_cf3,
                products.cf4 as product_cf4,
                products.cf5 as product_cf5,
                products.cf6 as product_cf6,
                options.name as option_name
            ')
        ->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
        ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
        ->join('units', 'units.id=products.unit', 'left')
        ->join('units sale_units', 'sale_units.id=sale_items.product_unit_id', 'left')
        ->join('options', 'options.id=sale_items.option_id', 'left')
        ->group_by('sale_items.id')
        ->order_by('id', 'asc');
        if ($sale_id && !$return_id) {
            $this->db->where('sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_id', $return_id);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
     public function getSaleById($id)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function resetSaleActions($id, $return_id = null, $check_return = null)
    {
        if ($sale = $this->getInvoiceByID($id)) {
            if (($sale->sale_status == 'returned') || ($sale->return_id != null)) {
                $this->session->set_flashdata('warning', lang('sale_x_action'));
                redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
            } else if ($sale->sale_status == 'completed' || $sale->sale_status == 'consignment') {
                if ($costings = $this->getSaleCosting($id)) {
                    foreach ($costings as $costing) {
                        if ($pi = $this->getPurchaseItemByID($costing->purchase_item_id)) {
                            $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                            // $this->db->update('purchase_items', ['quantity_balance' => ($costing->quantity_balance + $costing->quantity)], ['id' => $pi->id]);
                        } else {
                            // $sale_item = $this->getSaleItemByID($costing->sale_item_id);
                            $pi = $this->site->getPurchasedItem(['product_id' => $costing->product_id, 'option_id' => $costing->option_id ? $costing->option_id : null, 'purchase_id' => null, 'transfer_id' => null, 'warehouse_id' => $sale->warehouse_id]);
                                $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                        }
                    }
                    $this->db->delete('costing', ['id' => $costing->id]);
                }
                $items = $this->getAllInvoiceItems($id);
                $this->site->syncQuantity(null, null, $items);

                $customer = $this->site->getCompanyByID($sale->customer_id);
                if($customer->save_point){
                    if (!empty($this->Settings->each_spent)) {
                        $points       = floor(($sale->grand_total / $this->Settings->each_spent) * $this->Settings->ca_point);
                        $total_points = $sale->grand_total >= 0?$customer->award_points - $points:$customer->award_points + (-1*$points);
                        $this->db->update('companies', ['award_points' => $total_points], ['id' => $sale->customer_id]);
                    }
                }
                $staff = $this->site->getUser($sale->saleman_by);
                if($staff->save_point){
                    if (!empty($this->Settings->each_sale)) {
                        $points       = floor(($sale->grand_total / $this->Settings->each_sale) * $this->Settings->sa_point);
                        $total_points = $sale->grand_total >= 0?$staff->award_points - $points:$staff->award_points + (-1*$points);
                        $this->db->update('users', ['award_points' => $total_points], ['id' => $sale->saleman_by]);
                    }
                }
                return $items;
            }
        }
    }
    public function get_package_by_customer($biller_id,$customer_id = false)
    {
        $this->db->select("
            customer_package.id as id, 
            customer_package.code as code, 
            customer_package.name as name, 
            customer_package.period as period, 
            customer_package.period_type as period_type,
            customer_package.price as price, 
            customer_package.description as description
        ");
        $this->db->join("companies", "companies.service_package=customer_package.id", "left");
        if ($customer_id) {
            $this->db->where("companies.id", $customer_id);
        }
        $this->db->where("companies.group_name",'customer');
        
        $this->db->from("customer_package");
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getActivityCategories(){
        $q = $this->db->get_where("sh_skills",['type'=>'gym']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getActivities(){
        $q = $this->db->get_where('sh_subjects',array('type'=>'gym'));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getActivityByID($id = false)
    {
        $q = $this->db->get_where('sh_subjects', array('id' => $id,'type'=>'gym'), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function deleteActivityByID($id = false)
    {
        if($id && $this->db->where(array("id"=>$id,'type'=>'gym'))->delete('sh_subjects')){
            return true;
        }
        return false;
    }
    public function addTimeTable($data = false){
        if($data && $this->db->insert('sh_table_times',$data)){
            return true;
        }
        return false;
    }
    public function deleteTimeTableByID($id = false)
    {
        if($id && $this->db->where("id",$id)->delete('sh_table_times')){
            return true;
        }
        return false;
    }
    public function getAllTrainers()
    {
        $q = $this->db->get('sh_teachers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllTrainees()
    {
        $q = $this->db->get_where('companies', array('group_name' => 'customer'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getTraineeByID($id = false)
    {
        $q = $this->db->get_where('companies', array('group_name' => 'customer','id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function addWorkout($data = false, $items = false){
        if ($this->db->insert('gym_workouts', $data)) {
            $workout_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['workout_id'] = $workout_id;
                $this->db->insert('gym_workout_time', $item);      
            }
            return $workout_id;
        }
        return false;
    }
    public function UpdateWorkout($id, $data = false, $items = false){

        if ($this->db->update('gym_workouts', $data, array('id' => $id)) &&
            $this->db->delete('gym_workout_time', array('workout_id' => $id))) {
            foreach ($items as $item) {
                $item['workout_id'] = $id;
                $this->db->insert('gym_workout_time', $item);      
            }
            return true;
        }
        return false;
    }
    public function getWorkoutByID($id = false)
    {
        $q = $this->db->get_where('gym_workouts', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getWorkoutItemByID($id = false)
    {
        $q = $this->db->get_where('gym_workout_time', array('workout_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function deleteWorkoutByID($id = false)
    {
        if($id && $this->db->where("id",$id)->delete('gym_workouts')){
            $this->db->delete('gym_workout_time',array('workout_id'=>$id));
            return true;
        }
        return false;
    }

    public function getWorkoutSchedule_($id = false)
    {
        $this->db->select("
            gym_workouts.trainee_id, 
            gym_workouts.level_id,
            gym_workouts.start_date,
            gym_workouts.end_date,
            workout_id,
            gym_workout_time.day, 
            gym_workout_time.activity_id,
            gym_workout_time.kg,gym_workout_time.sets,
            gym_workout_time.reps,gym_workout_time.rest_time
        ");
        $this->db->join("gym_workouts", "gym_workouts.id=gym_workout_time.workout_id", "left");
        /*if ($customer_id) {
            $this->db->where("companies.id", $customer_id);
        }
        $this->db->where("gym_workouts.id",'customer');
*/
        $q = $this->db->get('gym_workout_time');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getWorkoutSchedule($activity_id = false,$date=null,$day=null)
    {
        $this->db->select("
            companies.name as trainee, 
            gym_workouts.level_id,
            gym_workouts.start_date,
            gym_workouts.end_date,
            workout_id,
            gym_workout_time.day, 
            gym_workout_time.activity_id,
            gym_workout_time.kg,gym_workout_time.sets,
            gym_workout_time.reps,gym_workout_time.rest_time
        ");
        $this->db->join("gym_workouts", "gym_workouts.id=gym_workout_time.workout_id", "left");
        $this->db->join("companies", "companies.id=gym_workouts.trainee_id", "left");
        if ($activity_id) {
            $this->db->where("gym_workout_time.activity_id", $activity_id);
        }
        if ($date) {
            $this->db->where("gym_workouts.start_date <=", $date);
            $this->db->where("gym_workouts.end_date >=", $date);
        }
        if ($day) {
            $this->db->where("gym_workout_time.day", $day);
        }
        $q = $this->db->get('gym_workout_time');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }

        return FALSE;
    }
    public function addClass($data =array())
    {
        if($data && $this->db->insert('sh_classes',$data)){
            return true;
        }
        return false;
    }
    public function getPrograms(){
        $q = $this->db->get_where('sh_programs',array('status'=>'active'));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getLevelByID($id = false)
    {
        $q = $this->db->get_where('sh_grades', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getClassByID($id = false)
    {
        $q = $this->db->get_where('sh_classes', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function deleteClassByID($id = false)
    {
        if($id && $this->db->where("id",$id)->delete('sh_classes')){
            $this->db->delete('sh_table_times',array('class_id'=>$id));
            return true;
        }
        return false;
    }
    public function getSkills(){
        $q = $this->db->get('sh_skills');
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getGrades()
    {
        $this->db->order_by("(".$this->db->dbprefix('sh_grades').".name + 0)");
        $q = $this->db->get_where('sh_grades',array('status'=>'active'));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function updateClass($id = false, $data = array())
    {
        if($id && $data && $this->db->where("id",$id)->update('sh_classes', $data)){
            return true;
        }
        return false;
    }
    public function getProgramByID($id = false)
    {
        $q = $this->db->get_where('sh_programs', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getGradeByID($id = false)
    {
        $q = $this->db->get_where('sh_grades', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function addSkill($data = false)
    {
        if($data && $this->db->insert("sh_skills",$data)){
            return true;
        }
        return false;
    }
    public function getcolleges(){
        $q = $this->db->get('sh_colleges');
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getSkillByID($id = false)
    {
        $q = $this->db->get_where("sh_skills", array("sh_skills.id" => $id));
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
    public function deleteTeacherByID($id = false){
        if($id && $this->db->delete('sh_teachers',array('id'=>$id))){
            $this->db->delete('sh_teach_infos',array('teacher_id'=>$id));
            $this->db->delete('sh_teacher_documents',array('teacher_id'=>$id));
            $this->db->delete('sh_teacher_qualifications',array('teacher_id'=>$id));
            $this->db->delete('sh_teacher_working_histories',array('teacher_id'=>$id));
            $this->db->delete('sh_teachers_families',array('teacher_id'=>$id));
            return true;
        }
        return false;
    }
    public function updateTeacher($id = false, $data = false){
        if($id && $data && $this->db->update('sh_teachers',$data,array('id'=>$id))){
            return true;
        }
        return false;
    }
    public function updateSkill($id = false, $data = false){
        if($id && $this->db->update("sh_skills",$data,array("id"=>$id))){
            return true;
        }
        return false;
    }
    public function deleteSkill($id = false){
        if($id && $this->db->delete("sh_skills",array("id"=>$id))){
            return true;
        }
        return false;
    }
    public function getCollegeByID($id = false){
        $q = $this->db->get_where("sh_colleges", array("sh_colleges.id"=>$id));
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
    public function addSubject($data =array())
    {
        if($data && $this->db->insert('sh_subjects',$data)){
            return true;
        }
        return false;
    }
    public function updateSubject($id = false, $data = array())
    {
        if($id && $data && $this->db->where("id",$id)->update('sh_subjects', $data)){
            return true;
        }
        return false;
    }
    public function deleteSubjectByID($id = false)
    {
        if($id && $this->db->where("id",$id)->delete('sh_subjects')){
            return true;
        }
        return false;
    }
    public function getSubjectByID($id = false)
    {
        $q = $this->db->get_where('sh_subjects', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getRooms($biller_id = false){
        if($biller_id){
            $this->db->where('sh_rooms.biller_id',$biller_id);
        }
        $q = $this->db->get_where('sh_rooms',array('status'=>'active'));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getActivityTime($activity_id = false,$day=null)
    {
        $this->db->select("
            start_time,
            end_time
        ");
        if ($activity_id) {
            $this->db->where("sh_table_times.subject_id", $activity_id);
        }
        if ($day) {
            $this->db->where("sh_table_times.day_name", $day);
        }
        $q = $this->db->get('sh_table_times');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getClasses($biller_id = false, $program_id = false, $grade_id = false){
        if($biller_id){
            $this->db->where('sh_classes.biller_id',$biller_id);
        }
        if($program_id){
            $this->db->where('sh_classes.program_id',$program_id);
        }
        if($grade_id){
            $this->db->where('sh_classes.grade_id',$grade_id);
        }
        $q = $this->db->get_where('sh_classes',array('status'=>'active'));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getClassStudentAttendances($biller_id,$class,$activity,$day,$dayname)
    {

        $this->db->select("
            gym_workouts.*,
            {$this->db->dbprefix("companies")}.code as code,
            {$this->db->dbprefix("companies")}.name as trainee,
            {$this->db->dbprefix("gym_workout_time")}.day as day_name,
            {$this->db->dbprefix("gym_workout_time")}.activity_id as actid,
          
        ");
        $this->db->join("companies","companies.id = gym_workouts.trainee_id");
        $this->db->join("gym_workout_time","gym_workout_time.workout_id = gym_workouts.id");

        if ($biller_id) {
            $this->db->where("gym_workouts.biller_id", $biller_id);
        }
        if($activity!=null){
            $this->db->where("gym_workout_time.activity_id",$activity);
        }
        $date = $this->bpas->fsd(trim($day));
        $this->db->where("gym_workout_time.day",$dayname);
        
        $this->db->where("gym_workouts.start_date <=", $date);
        $this->db->where("gym_workouts.end_date >=", $date);
        
        $this->db->from("gym_workouts");
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addbooking($data)
    {
        $this->db->insert('budgets', $data);
        return true;
    }
}
