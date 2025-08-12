<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Projects_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }
    function delete($id)
    {
        if ($this->db->delete('projects', ['project_id' => $id])) {
            return true;
        }
        return false;
    }
	public function delete_project($id)
    {
    
        if ($this->db->delete('projects', array('project_id' => $id))) {
            return true;
        }
        return false;
    }
    public function delete_task($id)
    {
        if ( $this->db->delete('projects_tasks', array('id' => $id))) {
            return true;
        }
        return false;
    }
    public function delete_milstone($id)
    {
        if ( $this->db->delete('projects_milestones', array('id' => $id))) {
            return true;
        }
        return false;
    }
	public function add_style($data){
       
        if ($this->db->insert("projects", $data)) {
			
			return true;
		}
        return false;
    }
    public function add_milestone($data){
        
        if ($this->db->insert("projects_milestones", $data)) {
            
            return true;
        }
        return false;
    }
    public function edit_milestone($id, $data = array()){
        
        $this->db->where('id', $id);
        if ($this->db->update("projects_milestones", $data)) {
            return true;
        }
        return false;
    }
     public function add_task($data){
        
        if ($this->db->insert("projects_tasks", $data)) {
            
            return true;
        }
        return false;
    }
    public function edit_task($id, $data = array()){
        $this->db->where('id', $id);
        if ($this->db->update("projects_tasks", $data)) {
            return true;
        }
        return false;
    }
	public function update($id, $data = array()){
        $this->db->where('project_id', $id);
        if ($this->db->update("projects", $data)) {
            return true;
        }
        return false;
    }
	public function getProjectByID($id)
    {

        $q = $this->db->get_where('projects', array('project_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
       
    }
    public function select_warehouse($id_w){
        $q = $this->db->get_where('warehouses', array('id' => $id_w), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function select_client($id_w)
    {
        
        $q = $this->db->get_where('companies', array('id' => $id_w), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getProjectVendorbyID($id)
    {
        $q = $this->db->get_where('projects_vendors', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function get_task_edit($id)
    {
        $q = $this->db->get_where('projects_tasks', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function get_task_detail($id)
    {
        $this->db->select('projects_tasks.*,projects.project_name as project_name,projects_milestones.title as milstone');
        $this->db->from('projects_tasks');
        $this->db->join('projects','projects.project_id = projects_tasks.project_id','left');
        $this->db->join('projects_milestones','projects_milestones.id = projects_tasks.milestone_id','left');
        $this->db->where('projects_tasks.id',$id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function get_mileston_edit($id)
    {
        $q = $this->db->get_where('projects_milestones', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function get_task_ByID($id, $user_id)
    {
        $this->db->select('projects_tasks.*,projects_milestones.title as milstone');
        $this->db->from('projects_tasks');
        $this->db->join('projects_milestones','projects_milestones.id = projects_tasks.milestone_id','left');
        $this->db->where('projects_tasks.project_id',$id);
        if ($user_id != null) {
            $this->db->where("FIND_IN_SET(".$user_id.", bpas_projects_tasks.user_id)");
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function get_all_task($user_id)
    {
        $this->db->select('projects_tasks.*,projects.project_name as project_name');
        $this->db->from('projects_tasks');
        $this->db->join('projects','projects.project_id = projects_tasks.project_id','left');
        //$this->db->where('projects_tasks.project_id',$id);
        if ($user_id != null) {
            $this->db->where("FIND_IN_SET(".$user_id.", bpas_projects_tasks.user_id)");
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getmilestone($id)
    {
        $q = $this->db->get_where('projects_milestones', array('project_id' => $id));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getAllProjects($limit,$start,$user_id)
    {
        
        $this->db->select('projects.*,project_id as id,companies.company,companies.name,b.name as biller')
        ->from('projects')
        ->join('companies b','b.id = projects.biller_id','left')
        ->join('companies','companies.id = projects.clients_id','left');
        if ($user_id != null) {
            $this->db->where("FIND_IN_SET(".$user_id.", bpas_projects.customer_id)");
        }
        $this->db->order_by('project_id','desc');
        
        $this->db->limit($limit, $start);
        $q = $this->db->get();
        return $q->result();
    }
    public function getAllProjects1($limit,$start,$user_id, $where)
    {
        
        $this->db->select('projects.*,project_id as id,companies.company,companies.name,b.name as biller')
        ->from('projects')
        ->join('companies b','b.id = projects.biller_id','left')
        ->join('companies', 'companies.id = projects.clients_id', 'left')
        ->where($where);
        if ($user_id != null) {
            $this->db->where("FIND_IN_SET(" . $user_id . ", bpas_projects.customer_id)");
        }
        $this->db->order_by('project_id', 'desc');

        $this->db->limit($limit, $start);
        $q = $this->db->get();
     
        return $q->result();
    }
    public function showCategories()
    {
        //data is retrive from this query  
        // $query = $this->db->get('categories');
        // return $query;
        if ($this->db->get('projects')) {
            return true;
        }
        return false;
    }  
    public function get_task_progress($id)
    {
        
        $this->db->select('projects_tasks.*,COUNT(project_id) as project, SUM(progress) as result');
        if ($id != null) {
            $this->db->where('project_id', $id);
        }
        $this->db->group_by('project_id');  
        $q = $this->db->get('projects_tasks');
        return $q->result();
    }
    public function get_count() {
        return $this->db->count_all($this->table);
    }

    public function countProjects($user_id)
    {
        $this->db->select('project_id as id,project_code,project_name,warehouses.name,description,customer_id');
        $this->db->from('projects');
        $this->db->join('warehouses','projects.warehouse_id = warehouses.id','left');
        if ($user_id != null) {
            $this->db->where("FIND_IN_SET(".$user_id.", bpas_projects.customer_id)");
        }
        return $this->db->count_all_results();
        
    }
    public function countProjects1($user_id)
    {
        $this->db->select('project_id as id,project_code,project_name,warehouses.name,description,customer_id');
        $this->db->from('projects');
        $this->db->join('warehouses', 'projects.warehouse_id = warehouses.id', 'left');
        $this->db->where(1);
        if ($user_id != null) {
            $this->db->where("FIND_IN_SET(" . $user_id . ", bpas_projects.customer_id)");
        }
        return $this->db->count_all_results();
    }
	//----------style--------
	public function addconsumption($data,$payments = array())
    {
		
        if ($this->db->insert("consumption", $data)) {
			$id= $this->db->insert_id();
		
			
			if (!empty($payments)) {
	
				foreach ($payments as $payment) {
					
						$data2 = array(
							'consump_id' => $id,
							'color_code' => $payment['color_code'],
							'qty' => $payment['qty'],
							'color_name' => $payment['color_name']
						);
						$this->db->insert('consumption_detail', $data2);
				
				}
			}
			return true;
		}
        return false;
    }
		public function get_consumption($id) 
	{
        $this->db->select()
                 ->from('consumption')
                 ->where("consum_id", $id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function getcolor_detail($id){
        $this->db->select()
                 ->from('consumption_detail')
                 ->where("consump_id", $id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
	public function getAllAtyle() {
        $this->db->select();
        $q = $this->db->get("projects");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAll() {
        $this->db->select();
        $q = $this->db->get("projects");
        if ($q->num_rows() > 0) {
            
            return $q->result();
        }
        return FALSE;
    }
	public function delete_consumption($id)
    {
		$this->db->where('consum_id', $id);
		$result= $this->db->delete('consumption');
        if ($result) {
			$this->db->where('consump_id', $id);
			$this->db->delete('consumption_detail');
            return true;
        }
        return false;
    }
	public function update_consumption($id, $data = array())
    {
        $this->db->where('consum_id', $id);
        if ($this->db->update("consumption", $data)) {
            return true;
        }
        return false;
    }
	public function get_consumption_ByID($id)
    {
        $q = $this->db->get_where('consumption', array('consum_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function get_varian_style($para) {
        $this->db->select();
        $q = $this->db->get_where("varian_projects",array(
			'parent_id'=>$para
		
			));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function get_milestone_ByID($id)
    {
        $q = $this->db->get_where('projects_milestones', array('project_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
     public function getPaymentsForPurchase($purchase_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getProjectPlanByID($id)
    {
        $this->db->select('*, projects_plan.biller_id as biller_id, projects_plan.warehouse_id as warehouse_id')
            ->join('projects', 'projects.project_id = projects_plan.project_id', 'left');
        $q = $this->db->get_where('projects_plan', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllProjectPlanItems($purchase_id)
    {
        $this->db->select('projects_plan_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name')
            ->join('products', 'products.id=projects_plan_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=projects_plan_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=projects_plan_items.tax_rate_id', 'left')
            ->group_by('projects_plan_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('projects_plan_items', array('project_plan_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function addProjectPlan($data, $items)
    {
        //$this->erp->print_arrays($data, $items);
        if ($this->db->insert('projects_plan', $data)) {
            $purchase_id = $this->db->insert_id();
            if ($this->site->getReference('pr') == $data['reference_no']) {
                $this->site->updateReference('pr');
            }
            foreach ($items as $item) {
                $item['project_plan_id'] = $purchase_id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                $this->db->insert('projects_plan_items', $item);
               
            }
           
            return true;
        }
        return false;
    }
    public function UpdateProjectPlan($id, $data, $items = array())
    {
        if ($this->db->update('projects_plan', $data, array('id' => $id)) && $this->db->delete('projects_plan_items', array('project_plan_id' => $id))) {
            $purchase_id = $id;
            foreach ($items as $item) {
                $item['project_plan_id'] = $id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                $this->db->insert('projects_plan_items', $item);
          
            }
            return true;
        }

        return false;
    }
    public function deletePlan($id)
    {
        if ($this->db->delete('projects_plan_items', array('project_plan_id' => $id)) && $this->db->delete('projects_plan', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
    public function add_project_vendor($data){
        
        if ($this->db->insert("projects_vendors", $data)) {
            
            return true;
        }
        return false;
    }
    public function edit_project_vendor($id, $data = array()){
        if ($this->db->update("projects_vendors", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
    public function delete_projects_vendors($id)
    {
        if ( $this->db->delete('projects_vendors', array('id' => $id))) {
            return true;
        }
        return false;
    }
    //-----------
    public function getProjectNotebyID($id)
    {
        $q = $this->db->get_where('projects_note', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function add_project_note($data){
        
        if ($this->db->insert("projects_note", $data)) {
            
            return true;
        }
        return false;
    }
    public function edit_project_note($id, $data = array()){
        if ($this->db->update("projects_note", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
    public function delete_projects_note($id)
    {
        if ( $this->db->delete('projects_note', array('id' => $id))) {
            return true;
        }
        return false;
    }
    //---------------
     public function getProjectMemberbyID($id)
    {
        $q = $this->db->get_where('projects_members', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function add_project_member($data){
        
        if ($this->db->insert("projects_members", $data)) {
            
            return true;
        }
        return false;
    }
    public function edit_project_member($id, $data = array()){
        if ($this->db->update("projects_members", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
    public function delete_projects_member($id)
    {
        if ( $this->db->delete('projects_members', array('id' => $id))) {
            return true;
        }
        return false;
    }
    public function updateStatus($id, $status, $note)
    {
        if ($this->db->update('projects', ['approve_status' => $status, 'approve_note' => $note], ['project_id' => $id]) ) {
            return true;
        }
        return false;
    }
    public function getBudgetByProjectID($id)
    {
        $this->db->select('sum(amount) as amount');
        $q = $this->db->get_where('budgets', array('project_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getExpenseByProjectID($id)
    {
        $this->db->select('sum(amount) as amount');
        $q = $this->db->get_where('expenses', array('project_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getinfluencerByProjectID($id)
    {
        $this->db->select('sum(price) as amount');
        $q = $this->db->get_where('projects_vendors', array('project_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getInfluencerPayments($expense_id = false)
    {
        $this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
        $this->db->order_by('id', 'desc');
        $this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('expense_id' => $expense_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
}
