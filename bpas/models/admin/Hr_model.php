<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Hr_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }
	
	public function getEmployeeNames($term = false, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
        $this->db->select('hr_employees.id, hr_employees.empcode, hr_employees.firstname, hr_employees.lastname')
			->where("(".$this->db->dbprefix('hr_employees') . ".lastname LIKE '%" . $term . "%' OR firstname LIKE '%" . $term . "%' OR
                CONCAT(".$this->db->dbprefix('hr_employees') . ".lastname, ".$this->db->dbprefix('hr_employees') . ".firstname) LIKE '%" . $term . "%')");
		$this->db->where("hr_employees_working_info.status","active");
		 $this->db->join('hr_employees_working_info','hr_employees_working_info.employee_id = hr_employees.id',"inner");
		$this->db->limit($limit);
        $q = $this->db->get('hr_employees');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function getAllEmployee($term = false, $biller_id = false, $limit = false)
    {
		if(!$limit){
			$limit = $this->Settings->rows_per_page;
		}
		if($biller_id){
			$this->db->where("hr_employees_working_info.biller_id",$biller_id);
			$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","inner");
		}
		
        $this->db->select('hr_employees.*')
            ->group_by('hr_employees.id')
			->where("(".$this->db->dbprefix('hr_employees').".lastname LIKE '%" . $term . "%' OR ".$this->db->dbprefix('hr_employees').".empcode LIKE '%" . $term . "%' OR ".$this->db->dbprefix('hr_employees').".firstname LIKE '%" . $term . "%')")
            ->limit($limit);
        $q = $this->db->get('hr_employees');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	public function addEmployee($data = array())
	{
		if($this->db->insert('hr_employees',$data)){
			return true;
		}
		return false;
	}
	public function updateEmployee($id = false, $data = array())
	{
		if($this->db->where("id",$id)->update('hr_employees',$data)){
			return true;
		}
		return false;
	}
	
	public function getLastEmployee()
	{
		$q = $this->db->query('SELECT * FROM '.$this->db->dbprefix("hr_employees").' ORDER BY id DESC LIMIT 1;');
		if($q->num_rows()>0){
			return $q->row();
		}
		return false;
	}
	
	public function getEmployees($biller_id = false,$position_id = false, $department_id = false, $group_id = false, $status = false)
	{
		$this->db->select("hr_employees.*");
		if($biller_id){
			$this->db->where("hr_employees_working_info.biller_id",$biller_id);
		}
		if($position_id){
			$this->db->where("hr_employees_working_info.position_id",$position_id);
		}
		if($department_id){
			$this->db->where("hr_employees_working_info.department_id",$department_id);
		}
		if($group_id){
			$this->db->where("hr_employees_working_info.group_id",$group_id);
		}
		if($status){
			$this->db->where("hr_employees_working_info.status",$status);
		}
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","LEFT");
		$q = $this->db->get('hr_employees');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getEmployeeById($id = false)
	{
		$result = $this->db->get_where("hr_employees", array("id"=>$id))->row();
		return $result;
	}
	
	public function deleteEmployeeByID($id = false)
	{
		if($id){
			$this->db->delete('hr_employees', array('id' => $id));
			return true;
		}
		return false;
	}
		
	public function getDepartments()
	{
		$results = $this->db->get("hr_departments")->result();
		return $results;
	}
	
	public function getDepartmentById($id= NULL)
	{
		$result = $this->db->where("id",$id)->get("hr_departments")->row();
		return $result;
	}
	
	public function getCompanies($id = false)
	{
		$data = array(
				"group_name" => "biller"
			);
		$results = $this->db->where($data)->get("companies")->result();
		return $results;
	}
	
	public function getCompany($id = false)
	{
		$data = array(
				"group_name" => "biller",
				"id" => 2,
			);
		$results = $this->db->where($data)->get("companies")->row();
		return $results;
	}
	
	public function addDepartment($data=array())
	{
		if ($this->db->insert('hr_departments',$data)) {
            return true;
        }
        return false;
	}
	
	public function updateDepartment($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('hr_departments', $data)) {
            return true;
        }
        return false;
    }
	
	public function deleteDepartmentByID($id = false)
	{
		if($id){
			$this->db->delete('hr_departments', array('id' => $id));
			return true;
		}
		return false;
	}
	public function getGroups(){
		$q = $this->db->get("hr_groups");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function addGroup($data = array())
	{
		if($this->db->insert('hr_groups',$data)){
			return true;
		}
		return false;
	}
	
	public function updateGroup($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('hr_groups', $data)) {
            return true;
        }
        return false;
    }
	
	public function deleteGroupByID($id = false)
	{
		if($id){
			$this->db->delete('hr_groups', array('id' => $id));
			return true;
		}
		return false;
	}
	
	public function getGroupById($id= NULL)
	{
		$q = $this->db->get_where('hr_groups', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getAllEmployees()
	{
		$q = $this->db->get('hr_employees');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getAllPositions()
	{
		$q = $this->db->get('hr_positions');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function addPosition($data = array())
	{
		if($this->db->insert('hr_positions',$data)){
			return true;
		}
		return false;
	}
	
	public function updatePosition($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('hr_positions', $data)) {
            return true;
        }
        return false;
    }
	
	public function deletePositionByID($id = false)
	{
		if($id){
			$this->db->delete('hr_positions', array('id' => $id));
			return true;
		}
		return false;
	}
	
	public function getPositionById($id= NULL)
	{
		$q = $this->db->get_where('hr_positions', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getDepartmentByBiller($biller = false)
	{
		$q = $this->db->get_where('hr_departments',array('biller_id' => $biller));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getPositionByBiller($biller = false)
	{
		$q = $this->db->get_where('hr_positions',array('biller_id' => $biller));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getGroupByDepartment($department = false)
	{
		$q = $this->db->get_where('hr_groups',array('department_id' => $department));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getEmployeeTypes()
	{
		$q = $this->db->get('hr_employees_types');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function addFamily($data =array())
	{
		if($this->db->insert('hr_employees_family',$data)){
			return true;
		}
		return false;
	}
	
	public function deleteFamily($id = false)
	{
		if($this->db->where("id",$id)->delete('hr_employees_family')){
			return true;
		}
		return false;
	}
	
	public function updateFamily($id = false, $data = array())
	{
		if($this->db->where("id",$id)->update('hr_employees_family', $data)){
			return true;
		}
		return false;
	}
	
	public function getFamilyByID($id= NULL)
	{
		$q = $this->db->get_where('hr_employees_family', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addQualification($data =array())
	{
		if($this->db->insert('hr_employees_qualification',$data)){
			return true;
		}
		return false;
	}
	
	public function updateQualification($id = false, $data = array())
	{
		if($this->db->where("id",$id)->update('hr_employees_qualification', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteQualification($id = false)
	{
		if($this->db->where("id",$id)->delete('hr_employees_qualification')){
			return true;
		}
		return false;
	}
	
	public function getQualificationByID($id= NULL)
	{
		$q = $this->db->get_where('hr_employees_qualification', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addWorkingHistory($data =array())
	{
		if($this->db->insert('hr_employees_working_history',$data)){
			return true;
		}
		return false;
	}
	
	public function getWorkingHistoryByID($id= NULL)
	{
		$q = $this->db->get_where('hr_employees_working_history', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function updateWorkingHistory($id = false, $data = array())
	{
		if($this->db->where("id",$id)->update('hr_employees_working_history', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteWorkingHistory($id = false)
	{
		if($this->db->where("id",$id)->delete('hr_employees_working_history')){
			return true;
		}
		return false;
	}
	
	public function addBankAccount($data = array())
	{
		if($this->db->insert('hr_employees_bank',$data)){
			return true;
		}
		return false;
	}
					
	public function deleteBankAccount($id = false)
	{
		if($this->db->where("id",$id)->delete('hr_employees_bank')){
			return true;
		}
		return false;
	}
	
	public function getBankAccountByID($id = false)
	{
		$q = $this->db->get_where('hr_employees_bank', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function updateBankAccount($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('hr_employees_bank', $data)){
			return true;
		}
		return false;
	}

	public function addContract($data = array())
	{
		if($this->db->insert('hr_employees_contract',$data)){
			return true;
		}
		return false;
	}
	
	public function updateContract($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('hr_employees_contract', $data)){
			return true;
		}
		return false;
	}

	public function getContractByID($id = false)
	{
		$q = $this->db->get_where('hr_employees_contract', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function deleteContract($id = false)
	{
		if($this->db->where("id",$id)->delete('hr_employees_contract')){
			return true;
		}
		return false;
	}
	
	public function deleteEmergencyContact($id = false)
	{
		if($this->db->where("id",$id)->delete('hr_employees_emergency')){
			return true;
		}
		return false;
	}
	
	public function addEmergencyContact($data = array())
	{
		if($this->db->insert('hr_employees_emergency',$data)){
			return true;
		}
		return false;
	}

	public function updateEmergencyContact($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('hr_employees_emergency', $data)){
			return true;
		}
		return false;
	}

	public function getEmergencyContactByID($id = false)
	{
		$q = $this->db->get_where('hr_employees_emergency', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	
	public function addDocument($data = array())
	{
		if($this->db->insert('hr_employees_document',$data)){
			return true;
		}
		return false;
	}

	public function updateDocument($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('hr_employees_document', $data)){
			return true;
		}
		return false;
	}

	public function getDocumentByID($id = false)
	{
		$q = $this->db->get_where('hr_employees_document', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	
	public function deleteDocument($id = false)
	{
		if($this->db->where("id",$id)->delete('hr_employees_document')){
			return true;
		}
		return false;
	}

	public function getEmployeesWorkingInfoByEmployeeID($id = false)
	{
		$q = $this->db->get_where('hr_employees_working_info', array('employee_id' => $id), 1);
		
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addEmployeesWorkingInfo($id = false, $data = array())
	{
		$q = $this->getEmployeesWorkingInfoByEmployeeID($id);
		if($q){
			if($this->db->where("employee_id",$id)->update('hr_employees_working_info',$data)){
				return true;
			}
		}else{
			if($this->db->insert('hr_employees_working_info',$data)){
				return true;
			}
		}
		
		return false;
	}
	
	public function getAllRelationShips()
	{
		$q = $this->db->get('hr_employees_relationship');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getRelationshipByID($id = false)
	{
		$q = $this->db->get_where('hr_employees_relationship', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	
	public function updateRelationship($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('hr_employees_relationship', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteRelationship($id = false)
	{
		if($this->db->where("id",$id)->delete('hr_employees_relationship')){
			return true;
		}
		return false;
	}
	
	public function addRelationship($data = array())
	{
		if($this->db->insert('hr_employees_relationship',$data)){
			return true;
		}
		return false;
	}
	
	public function getSalaryTaxCondition()
	{
		$q = $this->db->get('hr_tax_condition');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getChildrenMemberByEmployeeID($employee_id = false)
	{
		//$this->db->select('TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age');
		$q = $this->db->join('hr_employees_relationship','hr_employees_relationship.id=relationship','left')
					  ->where('employee_id',$employee_id)
					  ->where('is_children',1)->where('TIMESTAMPDIFF(YEAR, dob, CURDATE()) <',18)
					  ->get('hr_employees_family');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getSpouseMemberByEmployeeID($employee_id = false)
	{
		$q = $this->db->join('hr_employees_relationship','hr_employees_relationship.id=relationship','left')
					  ->where('employee_id',$employee_id)
					  ->where('is_spouse',1)
					  ->get('hr_employees_family');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function addEmployeeType($data = array())
	{
		if($this->db->insert('hr_employees_types',$data)){
			return true;
		}
		return false;
	}
	
	public function updateEmployeeType($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('hr_employees_types', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteEmployeeType($id = false)
	{
		if($this->db->where("id",$id)->delete('hr_employees_types')){
			return true;
		}
		return false;
	}
	
	public function getEmployeeTypeByID($id = false)
	{
		$q = $this->db->get_where('hr_employees_types', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	
	public function addTaxCondition($data = array())
	{
		if($this->db->insert('hr_tax_condition',$data)){
			return true;
		}
		return false;
	}
	
	public function updateTaxCondition($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('hr_tax_condition', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteTaxCondition($id = false)
	{
		if($this->db->where("id",$id)->delete('hr_tax_condition')){
			return true;
		}
		return false;
	}
	
	public function getTaxConditionByID($id = false)
	{
		$q = $this->db->get_where('hr_tax_condition', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	
	public function addPromotion($data = array())
	{
		if($this->db->insert('hr_employees_working_promote',$data)){
			$this->db->update('hr_employees_working_info',['employee_level'=>$data['employee_level']],array('employee_id'=>$data['employee_id']));

			/*------------Record History------------*/
			$array_data =[
				'transaction_type' 	=> 'promotion',
				'position_id'   	=> $data['position_id'],
				'promoted_date'		=> $data['promoted_date'],
			];
			$this->RecordEmployeeHistory('promotion',$data['position_id'],$data['employee_id'],$array_data);
			/*------------close Record History------------*/
			return true;
		}
		return false;
	}
	
	public function updatePromotion($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('hr_employees_working_promote', $data)){
			$this->db->update('hr_employees_working_info',['employee_level'=>$data['employee_level']],array('employee_id'=>$data['employee_id']));

			/*------------Record History------------*/
			$array_data =[
				'transaction_type' 	=> 'promotion',
				'position_id'   	=> $data['position_id'],
				'promoted_date'		=> $data['promoted_date'],
			];
			$this->RecordEmployeeHistory('promotion',$data['position_id'],$data['employee_id'],$array_data);
			/*------------close Record History------------*/
			return true;
		}
		return false;
	}
	public function getDataByTransaction($transaction = false, $transaction_id = false,$employee_id = false){
        $q = false;

        if($transaction=="promotion"){
        	$this->db->where('transaction_type',$transaction);
        	$this->db->where('position_id',$transaction_id);
        }else if($transaction=="resignation"){
        	$this->db->where('transaction_type',$transaction);
        	$this->db->where('resignation_id',$transaction_id);
        }
        

        $this->db->where('employee_id',$employee_id);
        $this->db->select();
        $q = $this->db->get("hr_employees_history");
        if ($q && $q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function RecordEmployeeHistory($transaction = false, $transaction_id = false,$employee_id=false,$array_data=[]){
       $checking_data = $this->getDataByTransaction($transaction, $transaction_id,$employee_id);

        if(!$checking_data) {
        	$array_data['employee_id'] = $employee_id;
            $this->db->insert('hr_employees_history', $array_data);

            return TRUE;
        }
        return TRUE;
	}

	public function getPromotionByID($id = false)
	{
		$q = $this->db->get_where('hr_employees_working_promote', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	
	public function deletePromotion($id = false)
	{
		if($this->db->where("id",$id)->delete('hr_employees_working_promote')){
			return true;
		}
		return false;
	}

	public function getAllDeductions($type = false)
	{
		$q = $this->db->get("pay_deductions");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	public function getAllAdditions($type = false)
	{
		$q = $this->db->get("pay_additions");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getLeaveCategories(){
		$q = $this->db->get('hr_leave_categories');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getAllLeaveTypes(){
		$q = $this->db->get('hr_leave_types');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function addLeaveType($data = false){
		if($data){
			$this->db->insert('hr_leave_types',$data);
			return true;
		}
		return false;
	}
	
	public function getLeaveTypeByID($id = false){
		$q = $this->db->get_where('hr_leave_types',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function updateLeaveType($id = false, $data = false){
		if($id && $data){
			$this->db->update('hr_leave_types',$data,array('id'=>$id));
			return true;
		}
		return false;
	}
	
	public function deleteLeaveTypeByID($id = false){
		if($id){
			$this->db->delete('hr_leave_types',array('id'=>$id));
			return true;
		}
		return false;
	}
	
	public function getLeaveCategoryByID($id = false){
		$q = $this->db->get_where('hr_leave_categories',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}

	public function getEmployeesByDepartment($id = false){
		$q = $this->db->join("hr_employees_working_info","hr_employees.id=hr_employees_working_info.employee_id","left")
					  ->get_where("hr_employees", array("department_id" => $id ));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[]= $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getPolicies(){
		$q = $this->db->get('att_policies');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getKPITypes(){
		$q = $this->db->get('hr_kpi_types');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function addKPIType($data = false){
		if($data){
			$this->db->insert('hr_kpi_types',$data);
			return true;
		}
		return false;
	}

	public function getKPITypeByID($id = false){
		$q = $this->db->get_where('hr_kpi_types',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}

	public function updateKPIType($id = false, $data = false){
		if($id && $data){
			$this->db->update('hr_kpi_types',$data,array('id'=>$id));
			return true;
		}
		return false;
	}

	public function deleteKPITypeByID($id = false){
		if($id){
			$this->db->delete('hr_kpi_types',array('id'=>$id));
			return true;
		}
		return false;
	}
	
	
	public function addKPIMeasure($data = false){
		if($data){
			$this->db->insert('hr_kpi_measures',$data);
			return true;
		}
		return false;
	}

	public function getKPIMeasureByID($id = false){
		$q = $this->db->get_where('hr_kpi_measures',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}

	public function updateKPIMeasure($id = false, $data = false){
		if($id && $data){
			$this->db->update('hr_kpi_measures',$data,array('id'=>$id));
			return true;
		}
		return false;
	}

	public function deleteKPIMeasureByID($id = false){
		if($id){
			$this->db->delete('hr_kpi_measures',array('id'=>$id));
			return true;
		}
		return false;
	}
	
	public function addKPIQuestion($data = false){
		if($data){
			$this->db->insert('hr_kpi_questions',$data);
			return true;
		}
		return false;
	}

	public function getKPIQuestionByID($id = false){
		$q = $this->db->get_where('hr_kpi_questions',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}

	public function updateKPIQuestion($id = false, $data = false){
		if($id && $data){
			$this->db->update('hr_kpi_questions',$data,array('id'=>$id));
			return true;
		}
		return false;
	}

	public function deleteKPIQuestionByID($id = false){
		if($id){
			$this->db->delete('hr_kpi_questions',array('id'=>$id));
			return true;
		}
		return false;
	}
	
	public function getKPIQuestionByKPIType($kpi_type = false){
		$this->db->where('hr_kpi_questions.kpi_type',$kpi_type);
		$q = $this->db->get('hr_kpi_questions');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getKPIByID($id = false){
		$this->db->select("hr_kpi.*,hr_kpi_types.name as kpi_type,hr_employees.lastname,hr_employees.firstname,hr_kpi.kpi_type as kpi_type_id")
				->join("hr_employees","hr_employees.id = hr_kpi.employee_id","left")
				->join("hr_kpi_types","hr_kpi_types.id = hr_kpi.kpi_type","left")
				->where("hr_kpi.id",$id);
		$q = $this->db->get("hr_kpi");
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getKPIItems($kpi_id = false){
		$q = $this->db->get_where('hr_kpi_items',array('kpi_id'=>$kpi_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function addKPI($data = false, $items= false){
		if($this->db->insert('hr_kpi',$data)){
			$kpi_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item['kpi_id'] = $kpi_id;
					$this->db->insert('hr_kpi_items',$item);
				}
			}
			return true;
		}
		return false;
	}
	public function updateKPI($id= false, $data = false, $items= false){
		if($this->db->update('hr_kpi',$data,array('id'=>$id))){
			$this->db->delete('hr_kpi_items',array('kpi_id'=>$id));
			if($items){
				$this->db->insert_batch('hr_kpi_items',$items);
			}
			return true;
		}
		return false;
	}
	
	public function deleteKPI($id = false){
		if($id && $this->db->delete('hr_kpi',array('id'=>$id))){
			$this->db->delete('hr_kpi_items',array('kpi_id'=>$id));
			return true;
		}
		return false;
	}
	
	public function getKPIMeasureByResult($kpi_type = false, $result = false){
		if($kpi_type && $result){
			$this->db->where('kpi_type',$kpi_type);
			$this->db->where('(IFNULL('.$result.',0)) >= min_percentage');
			$this->db->where('(IFNULL('.$result.',0)) <= max_percentage');
			$q = $this->db->get('hr_kpi_measures');
			if($q->num_rows() > 0){
				return $q->row();
			}
		}
		return false;
	}
	
	public function getPositionsByBiller($biller_id = false){
		if($biller_id){
			$this->db->where("hr_positions.biller_id",$biller_id);
		}
		$q = $this->db->get("hr_positions");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function getDepartmentsByBilller($biller_id = false){
		if($biller_id){
			$this->db->where("hr_departments.biller_id",$biller_id);
		}
		$q = $this->db->get("hr_departments");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function getGroupsByBiller($department_id = false){
		if($department_id){
			$this->db->where("hr_groups.department_id",$department_id);
		}
		$q = $this->db->get("hr_groups");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	
	public function addSampleIDCard($data = false){
		if($data){
			$this->db->insert('hr_sample_id_cards',$data);
			return true;
		}
		return false;
	}

	public function getSampleIDCardByID($id = false){
		$q = $this->db->get_where('hr_sample_id_cards',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}

	public function updateSampleIDCard($id = false, $data = false){
		if($id && $data){
			$this->db->update('hr_sample_id_cards',$data,array('id'=>$id));
			return true;
		}
		return false;
	}

	public function deleteSampleIDCardByID($id = false){
		if($id){
			$this->db->delete('hr_sample_id_cards',array('id'=>$id));
			return true;
		}
		return false;
	}
	
	public function getIDCardSamples(){
		$q = $this->db->get_where("hr_sample_id_cards");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	
	public function updateIDCardDemension($id = false, $data = false){
		if($id && $this->db->update("hr_sample_id_cards",$data,array("id"=>$id))){
			return true;
		}
		return false;
	}
	
	public function getIDCardByID($id = false){
		$q = $this->db->get_where("hr_id_cards",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getIDCardItemByID($id = false){
		$q = $this->db->get_where("hr_id_card_items",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getIDCardItems($id_card_id = false, $id = false){
		if($id){
			$this->db->where("hr_id_card_items.id",$id);
		}
		$this->db->select("hr_id_card_items.*,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_employees.gender,
							hr_employees.photo,
							hr_employees.finger_id,
							hr_departments.name as department,
							hr_positions.name as position
						");
		$this->db->join("hr_employees","hr_employees.id = hr_id_card_items.employee_id","inner");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_id_card_items.employee_id","left");
		$this->db->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left");
		$this->db->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left");
		$this->db->where("hr_id_card_items.id_card_id",$id_card_id);
		$q = $this->db->get("hr_id_card_items");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function addIDCard($data = false, $items = false){
		if($data && $this->db->insert("hr_id_cards",$data)){
			$id_card_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["id_card_id"] = $id_card_id;
					$this->db->insert("hr_id_card_items",$item);
				}
			}
			return true;
		}
		return false;
	}
	public function updateIDCard($id = false, $data = false, $items = false){
		if($id && $this->db->update("hr_id_cards",$data,array("id"=>$id))){
			$this->db->delete("hr_id_card_items",array("id_card_id"=>$id));
			if($items){
				$this->db->insert_batch("hr_id_card_items",$items);
			}
			return true;
		}
		return false;
	}
	public function deleteIDCard($id = false){
		if($id && $this->db->delete("hr_id_cards",array("id"=>$id))){
			$this->db->delete("hr_id_card_items",array("id_card_id"=>$id));
			return true;
		}
		return false;
	}
	
	public function updateIDCardStatus($id = false, $status = false){
		if($id && $this->db->update("hr_id_cards",array("status"=>$status),array("id"=>$id))){
			return true;
		}
		return false;
	}
	
	
	public function getKPIEmployee($biller_id = false, $position_id = false, $department_id = false, $group_id = false, $employee_id = false, $kpi_type = false, $year = false, $status = false){
		if($biller_id){
			$this->db->where("hr_employees_working_info.biller_id",$biller_id);
		}
		if($position_id){
			$this->db->where("hr_employees_working_info.position_id",$position_id);
		}
		if($department_id){
			$this->db->where("hr_employees_working_info.department_id",$department_id);
		}
		if($group_id){
			$this->db->where("hr_employees_working_info.group_id",$group_id);
		}
		if($kpi_type){
			$this->db->where("hr_employees_working_info.kpi_type",$kpi_type);
		}
		if($status){
			$this->db->where("hr_employees_working_info.status",$status);
		}
		if($employee_id){
			$this->db->where("hr_employees.id",$employee_id);
		}
		$this->db->select("
							hr_employees.id,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_positions.name as position,
							hr_departments.name as department,
							hr_groups.name as group,
							hr_employees_working_info.kpi_type,
							companies.logo,
							companies.name,
							companies.city,
							companies.email,
							companies.address,
							companies.phone,
							att_approve_attedances.absent,
							att_approve_attedances.late
						");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","INNER");
		$this->db->join("hr_kpi_types","hr_kpi_types.id = hr_employees_working_info.kpi_type","INNER");
		$this->db->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left");
		$this->db->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left");
		$this->db->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left");
		$this->db->join("companies","companies.id = hr_employees_working_info.biller_id","left");
		$this->db->join("(SELECT
							sum( absent + permission ) AS absent,
							sum( late ) AS late,
							employee_id 
						FROM
							".$this->db->dbprefix('att_approve_attedances ')."
						WHERE
							`year` = '".$year."' 
						GROUP BY
							employee_id) as att_approve_attedances","att_approve_attedances.employee_id = hr_employees.id","LEFT");
		$this->db->group_by("hr_employees.id");
		$q = $this->db->get_where("hr_employees");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getKPIQuestions(){
		$this->db->order_by("hr_kpi_questions.type,hr_kpi_questions.id");
		$q = $this->db->get("hr_kpi_questions");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function leaveCategoryByCode($code = false){
		$q = $this->db->get_where("hr_leave_categories",array("code"=>$code));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getSalaryReviewByID($id = false){
		$q = $this->db->get_where("hr_salary_reviews",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getSalaryReviewItems($salary_id = false){
		$this->db->select("hr_salary_review_items.*, hr_employees.empcode,hr_employees.firstname,hr_employees.lastname");
		$this->db->join("hr_employees","hr_employees.id = hr_salary_review_items.employee_id","left");
		$this->db->where("hr_salary_review_items.salary_id",$salary_id);
		$this->db->order_by("hr_salary_review_items.id","desc");
		$q = $this->db->get("hr_salary_review_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function addSalaryReview($data = false, $items = false){
		if($this->db->insert("hr_salary_reviews",$data)){
			$salary_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["salary_id"] = $salary_id;
					$this->db->insert("hr_salary_review_items",$item);
				}
			}
			return true;
		}
		return false;
	}
	
	public function updateSalaryReview($id = false, $data = false, $items = false){
		if($id && $this->db->update("hr_salary_reviews",$data,array("id"=>$id))){
			$this->db->delete("hr_salary_review_items",array("salary_id"=>$id));
			if($items){
				$this->db->insert_batch("hr_salary_review_items",$items);
			}
			return true;
		}
		return false;
	}
	
	public function deleteSalaryReview($id = false){
		if($id && $this->db->delete("hr_salary_reviews",array("id"=>$id))){
			$this->db->delete("hr_salary_review_items",array("salary_id"=>$id));
			return true;
		}
		return false;
	}
	
	public function updateSalaryReviewStatus($id = false, $status = false){
		if($id && $this->db->update("hr_salary_reviews",array("status"=>$status),array("id"=>$id))){
			$salary_employees = $this->getSalaryReviewItems($id);
			if($salary_employees){
				$currency = $this->site->getCurrencyByCode("KHR");
				foreach($salary_employees as $salary_employee){
					if($status=="approved"){
						$net_salary = $salary_employee->new_salary;
						$additions = $salary_employee->new_addition;
					}else{
						$net_salary = $salary_employee->old_salary;
						$additions = $salary_employee->old_addition;
					}
					//====tax caluclate====//
						$employee_id  = $salary_employee->employee_id;
						$salary_tax   = $net_salary * $currency->rate;
						$employee 	  = $this->getEmployeeById($employee_id);
						$salary_taxs  = $this->getSalaryTaxCondition();
						$spouses 	  = $this->getSpouseMemberByEmployeeID($employee_id);
						$childs 	  = $this->getChildrenMemberByEmployeeID($employee_id);
						$tax_rate = 0;
						foreach($salary_taxs as $tax){
							if($employee->non_resident==0){
								$allowance = (($spouses?count($spouses):0) + ($childs?count($childs):0)) * 150000;
								$base_salary_tax = $salary_tax - $allowance;
								if($base_salary_tax <= $tax->max_salary && $base_salary_tax >= $tax->min_salary){
									$tax_rate = ($base_salary_tax * $tax->tax_percent) - $tax->reduce_tax;
									$tax_rate = ($tax_rate / $currency->rate);
								}
							}else{
								$tax_rate = ($salary_tax * 0.2);
								$tax_rate = ($tax_rate / $currency->rate);
							}
						}
						$monthly_rate = $net_salary + $tax_rate;
					//====end tax caluclate====//
					
					$data = array(
									"net_salary" => $net_salary,
									"additions" => $additions,
									"salary_tax" => ($salary_tax / $currency->rate),
									"tax_rate" => $tax_rate,
									"monthly_rate" => $monthly_rate
								);
					$this->db->update("hr_employees_working_info",$data,array("employee_id"=>$employee_id));
				}
			}
			return true;
		}
		return false;
	}
	
	public function getSalaryReviewedEmployee($month = false, $edit_id = false){
		if($edit_id){
			$this->db->where("hr_salary_reviews.id !=", $edit_id);
		}
		$this->db->select("hr_salary_review_items.*");
		$this->db->where("hr_salary_reviews.month",$month);
		$this->db->join("hr_salary_reviews","hr_salary_reviews.id = hr_salary_review_items.salary_id","inner");
		$q = $this->db->get("hr_salary_review_items");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	
	public function getSalaryReviewEmployee($biller_id = false,$position_id = false, $department_id = false, $group_id = false, $month = false, $edit_id = false){
		$salary_employees = $this->getSalaryReviewedEmployee($month, $edit_id);
		if($salary_employees){
			foreach($salary_employees as $salary_employee){
				$salaried_employee[] = $salary_employee->employee_id;
			}
			$this->db->where_not_in("hr_employees_working_info.employee_id",$salaried_employee);
		}
		if($biller_id){
			$this->db->where("hr_employees_working_info.biller_id",$biller_id);
		}
		if($position_id){
			$this->db->where("hr_employees_working_info.position_id",$position_id);
		}
		if($department_id){
			$this->db->where("hr_employees_working_info.department_id",$department_id);
		}
		if($group_id){
			$this->db->where("hr_employees_working_info.group_id",$group_id);
		}

		$this->db->select("	
							hr_employees.id as employee_id,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_employees_working_info.net_salary,
							hr_employees_working_info.additions,
							IFNULL(".$this->db->dbprefix('hr_kpi').".result,0) as result,
							IFNULL(".$this->db->dbprefix('hr_kpi_measures').".increase_salary,0) as increase_salary
						");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","inner");
		$this->db->join("hr_kpi","hr_kpi.employee_id = hr_employees.id AND hr_kpi.month = '".$month."'","left");
		$this->db->join("hr_kpi_measures","hr_kpi_measures.kpi_type = hr_kpi.kpi_type AND hr_kpi.result >= hr_kpi_measures.min_percentage AND hr_kpi.result <= max_percentage","left");
		$this->db->where("hr_employees_working_info.status !=","inactive");
		$this->db->group_by("hr_employees.id");
		$q = $this->db->get("hr_employees");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function addAward($data = false, $items = false){
		if($data && $this->db->insert("hr_award",$data)){
			$id_card_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["award_id"] = $id_card_id;
					$this->db->insert("hr_award_items",$item);
				}
			}
			return true;
		}
		return false;
	}
	public function updateAward($id = false, $data = false, $items = false){
		if($id && $this->db->update("hr_award",$data,array("id"=>$id))){
			$this->db->delete("hr_award_items",array("award_id"=>$id));
			if($items){
				$this->db->insert_batch("hr_award_items",$items);
			}
			return true;
		}
		return false;
	}
	public function deleteAward($id = false){
		if($id && $this->db->delete("hr_award",array("id"=>$id))){
			$this->db->delete("hr_award_items",array("award_id"=>$id));
			return true;
		}
		return false;
	}
	public function getAwardByID($id = false){
		$q = $this->db->get_where("hr_award",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getWardItems($id_card_id = false, $id = false){
		if($id){
			$this->db->where("hr_award_items.id",$id);
		}
		$this->db->select("hr_award_items.*,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_employees.gender,
							hr_employees.photo,
							hr_employees.finger_id,
							hr_departments.name as department,
							hr_positions.name as position
						");
		$this->db->join("hr_employees","hr_employees.id = hr_award_items.employee_id","inner");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_award_items.employee_id","left");
		$this->db->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left");
		$this->db->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left");
		$this->db->where("hr_award_items.award_id",$id_card_id);
		$q = $this->db->get("hr_award_items");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}

	public function addWarning($data = false, $items = false){
		if($data && $this->db->insert("hr_warning",$data)){
			return true;
		}
		return false;
	}
	public function updateWarning($id = false, $data = false, $items = false){
		if($id && $this->db->update("hr_warning",$data,array("id"=>$id))){
		
			return true;
		}
		return false;
	}
	public function deleteWarning($id = false){
		if($id && $this->db->delete("hr_warning",array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function getWarningByID($id = false){
		$q = $this->db->get_where("hr_warning",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}

	public function addResignation($data = false, $items = false){
		if($data && $this->db->insert("hr_resignation",$data)){
			$this->sysnEmployeeStatus($data["employee_id"]);
			return true;
		}
		return false;
	}
	public function updateResignation($id = false, $data = false, $items = false){
		$resignation = $this->getResignationByID($id);
		if($id && $this->db->update("hr_resignation",$data,array("id"=>$id))){
			$this->sysnEmployeeStatus($data["employee_id"]);
			if($resignation->employee_id != $data["employee_id"]){
				$this->sysnEmployeeStatus($resignation->employee_id);
			}
			return true;
		}
		return false;
	}
	public function deleteResignation($id = false){
		$resignation = $this->getResignationByID($id);
		if($id && $this->db->delete("hr_resignation",array("id"=>$id))){
			$this->sysnEmployeeStatus($resignation->employee_id);
			return true;
		}
		return false;
	}
	public function getResignatIOnByEmployeeID($employee_id = false){
		$this->db->select("MAX(resignation_date) as last_date");
		$this->db->group_by("hr_resignation.employee_id");
		$q = $this->db->get_where("hr_resignation",array("employee_id"=>$employee_id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function sysnEmployeeStatus($employee_id = false){
		$resignation = $this->getResignatIOnByEmployeeID($employee_id);
		$status = "active";
		$resigned_date = "";
		if($resignation){
			if($resignation->last_date <= date("Y-m-d")){
				$status = "inactive";
			}
			$resigned_date = $resignation->last_date;
		}
		$data = array("status"=>$status,"resigned_date"=>$resigned_date);
		$this->db->update("hr_employees_working_info",$data,array("employee_id"=>$employee_id));
	}

	public function getResignationByID($id = false){
		$q = $this->db->get_where("hr_resignation",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function deleteConPaymentCondition($id = false)
	{
		if($this->db->where("id",$id)->delete('hr_nssf_condition')){
			return true;
		}
		return false;
	}
	public function updateConPaymentCondition($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('hr_nssf_condition', $data)){
			return true;
		}
		return false;
	}
	public function getConPaymentConditionByID($id = false)
	{
		$q = $this->db->get_where('hr_nssf_condition', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	public function getSalaryNSSFCondition(){
		$q = $this->db->get('hr_nssf_condition');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function importEmployee($employees = false){
		if($employees && $this->db->insert_batch('hr_employees',$employees)){
			return true;
		}
		return false;
	}
	public function getEmployeeByCode($code = false){
		if($code){
			$q = $this->db->get_where('hr_employees',array('empcode'=>$code));
			if($q->num_rows() > 0){
				return $q->row();
			}
		}
		return false;
	}
	public function getGroupsByDepartment($department_id = false){
		if($department_id){
			$this->db->where("hr_groups.department_id",$department_id);
		}
		$q = $this->db->get("hr_groups");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function getPositionsByDepartment($department_id = false){
		if($department_id){
			$this->db->where_in("IFNULL(department_id,0)",array("0",$department_id));
		}
		$q = $this->db->get("hr_positions");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function getIndexTemplates(){
		$q = $this->db->get("hr_templates");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[$row->type][$row->employee_type][$row->biller_id] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getContracts($biller = false,$department = false,$group = false,$position = false,$employee = false,$from_date = false,$to_date = false){
		
		if ($biller) {
			$this->db->where('hr_employees_working_info.biller_id', $biller);
		}
		if ($department) {
			$this->db->where('hr_employees_working_info.department_id', $department);
		}
		if ($group) {
			$this->db->where('hr_employees_working_info.group_id', $group);
		}
		if ($position) {
			$this->db->where('hr_employees_contract.position_id', $position);
		}
		if ($employee) {
			$this->db->where('hr_employees.id', $employee);
		}
		if ($from_date) {
			$this->db->where('hr_employees_contract.date >=', $from_date);
		}
		if ($to_date) {
			$this->db->where('hr_employees_contract.date <=', $to_date);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
		}
		
		$this->db->select("hr_employees.empcode,
							hr_employees.nric_no,
							hr_employees.lastname_kh,
							hr_employees.lastname,
							hr_employees.firstname,
							hr_employees.firstname_kh,
							hr_employees.gender,
							hr_employees.dob,
							hr_employees.pob,
							hr_employees.address,
							hr_employees.nationality,
							hr_employees.marital_status,
							hr_employees.type,
							hr_employees_contract.start_date,
							hr_employees_contract.end_date,
							hr_employees_contract.month,
							hr_positions.name as position,
							hr_employees_contract.basic_salary,
							hr_employees_contract.biller_id
						");
		$this->db->join("hr_employees","hr_employees.id = hr_employees_contract.employee_id","inner");
		$this->db->join("hr_employees_working_info","hr_employees.id = hr_employees_working_info.employee_id","left");
		$this->db->join("hr_positions","hr_positions.id = hr_employees_contract.position_id","left");
		$this->db->join("hr_salary_reviews","hr_salary_reviews.id = hr_employees_contract.salary_review_id","left");
		$this->db->where("IFNULL(".$this->db->dbprefix('hr_salary_reviews').".status,'') !=","pending");
		$q = $this->db->get("hr_employees_contract");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getTemplateByID($id = false){
		$q = $this->db->get_where("hr_templates",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	
	public function addTemplate($data=false)
	{
		if ($this->db->insert('hr_templates',$data)) {
            return true;
        }
        return false;
	}
	
	public function updateTemplate($id = false, $data = false)
    {
        $this->db->where('id', $id);
        if ($this->db->update('hr_templates', $data)) {
            return true;
        }
        return false;
    }
	
	public function deleteTemplateByID($id = false)
	{
		if($id && $this->db->delete('hr_templates', array('id' => $id))){
			return true;
		}
		return false;
	}
	
	public function getTemplates(){
		$q = $this->db->get("hr_templates");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getTemplateByEmployeeType($employee_type = false, $type = false, $biller_id = false){
		if($type){
			$this->db->where("hr_templates.type",$type);
		}
		if($biller_id){
			$this->db->where("hr_templates.biller_id",$biller_id);
		}
		$q = $this->db->get_where("hr_templates",array("employee_type"=>$employee_type));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function updateResignationStatus($id = false, $status = false){
		if($id && $this->db->update("hr_resignation",array("status"=>$status),array("id"=>$id))){
			if($status == "approved"){
				$data = $this->getResignationByID($id);
				/*------------Record History------------*/
			    $array_data =[
			        'transaction_type'   => 'resignation',
			        'resignation_date'   => $data->resignation_date,
			        'resignation_id'     => $id
			    ];
			    $this->RecordEmployeeHistory('resignation',$id,$data->employee_id,$array_data);
			    /*------------close Record History------------*/
			}
			return true;
		}
		return false;
	}
	public function getCandidates($biller_id = false)
	{
		$this->db->select("hr_employees.*");
		if($biller_id){
			$this->db->where("hr_employees_working_info.biller_id",$biller_id);
		}
		$this->db->where("hr_employees.candidate",1);

		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","LEFT");
		$q = $this->db->get('hr_employees');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getShortlistById($id= NULL)
	{
		$q = $this->db->get_where('hr_shortlist', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function add_shortlist($data = array())
	{
		if($this->db->insert('hr_shortlist',$data)){
			$this->db->update('hr_employees', ['candidate_status' => 'shortlist'], ['id'=> $data['candidate_id']]);
			return true;
		}
		return false;
	}
	public function updateInterview($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('hr_interview', $data)) {
            return true;
        }
        return false;
    }
	public function updateShortlist($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('hr_shortlist', $data)) {
            return true;
        }
        return false;
    }
    public function getShortlists($biller_id = false)
	{
		$this->db->select("hr_employees.*");
		if($biller_id){
			$this->db->where("hr_employees_working_info.biller_id",$biller_id);
		}
		$this->db->where("hr_employees.candidate",1);
		$this->db->join("hr_employees","hr_employees.id = hr_shortlist.candidate_id","LEFT");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","LEFT");

		$q = $this->db->get('hr_shortlist');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function add_interview($data = array())
	{
		if($this->db->insert('hr_interview',$data)){
			$this->db->update('hr_employees', ['candidate_status' => 'interview'], ['id'=> $data['shortlist_id']]);
			return true;
		}
		return false;
	}
	public function getInterviewById($id= NULL)
	{
		$q = $this->db->get_where('hr_interview', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getCandidateShortlist($id){
        $this->db->where('candidate_id', $id)->from('hr_shortlist');
        return $this->db->count_all_results();
    }
	public function getShortlistInterview($id){
        $this->db->where('shortlist_id', $id)->from('hr_interview');
        return $this->db->count_all_results();
    }
	public function getInterviewEmployee($id){
		$this->db->where('candidate', 0);
        $this->db->where('id', $id)->from('hr_employees');
        return $this->db->count_all_results();
    }
    public function getEmployeeByIdemp($id = false)
    {
	    $this->db->select("users.*,")
	    // ->join("hr_employees","hr_employees.id = hr_kpi.employee_id","left")
	    // ->join("users","users.emp_id = hr_employees.id","left")
	    ->where("emp_id",$id);
	    $q = $this->db->get("users");
	    if($q->num_rows() > 0){
		    return $q->row();
	    }
	    return false;
    }
    public function getEmployeesInfoByEmployeeID($id = false){
		$q = $this->db->join("hr_employees_working_info","hr_employees.id=hr_employees_working_info.employee_id","left")
		->get_where("hr_employees", array("hr_employees.id" => $id ));
		if($q->num_rows() > 0){
		    return $q->row();
	    }
		return false;
	}
	public function getTravelByID($id = false){
		$q = $this->db->get_where("hr_travels",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getTravelItems($travel_id = false){
		if($travel_id){
			$this->db->where('hr_travel_items.travel_id',$travel_id);
		}
		$this->db->select('hr_travel_items.*,hr_employees.empcode,hr_employees.firstname,hr_employees.lastname');
		$this->db->join('hr_employees','hr_employees.id = hr_travel_items.employee_id','inner');
		$q = $this->db->get('hr_travel_items');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function addTravel($data = false, $items = false){
		if($this->db->insert("hr_travels",$data)){
			$travel_id = $this->db->insert_id();
			foreach($items as $item){
				$item['travel_id'] = $travel_id;
				$this->db->insert("hr_travel_items",$item);
			}
			return true;
		}
		return false;
	}
	public function updateTravel($id = false, $data = false, $items = false){
		if($this->db->update('hr_travels',$data,array('id'=>$id))){
			$this->db->delete('hr_travel_items',array('travel_id'=>$id));
			if($items){
				$this->db->insert_batch('hr_travel_items',$items);
			}
			return true;
		}
		return false;
	}
	public function deleteTravel($id = false){
		if($id && $this->db->delete("hr_travels",array("id"=>$id))){
			$this->db->delete("hr_travel_items",array("travel_id"=>$id));
			return true;
		}
		return false;
	}
	public function updateTravelStatus($id = false, $status = false){
		if($id && $this->db->update("hr_travels",array("status"=>$status),array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function getNestedByCategories($limit = null, $start = null)
    {
        return $this->getCategoriesByParents(0, $limit, $start);
    }
    public function getCategoriesByParents($id, $limit = null, $start = null)
    {        
        $items = array();  
        if ($id === 0) {
            $this->db->where('parent_id', $id);    
            $this->db->or_where('parent_id', null);    
        } else {
            $this->db->where('parent_id', $id);    
        }
        $this->db->limit($start, $limit);
        $q = $this->db->get('hr_employees_level');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $row->children = $this->getCategoriesByParents($row->id);
                $items[] = $row;
            }
        }
        return $items;
    }
    public function addTransfer($data = false, $items = false){
		if($this->db->insert("hr_transfers",$data)){
			$transfer_id = $this->db->insert_id();
			foreach($items as $item){
				$item['transfer_id'] = $transfer_id;
				$this->db->insert("hr_transfer_items",$item);
				$this->db->update('hr_employees_working_info',['department_id'=>$data['to_department']],array('employee_id'=>$item->employee_id));
			}
			return true;
		}
		return false;
	}
	public function updateTransfer($id = false, $data = false, $items = false){
		if($this->db->update('hr_transfers',$data,array('id'=>$id))){
			$this->db->delete('hr_transfer_items',array('transfer_id'=>$id));

			foreach($items as $item){
				$this->db->insert('hr_transfer_items',$items);
				$this->db->update('hr_employees_working_info',['department_id'=>$data['to_department']],array('employee_id'=>$item->employee_id));
			}
			return true;
		}
		return false;
	}
	public function deleteTransfer($id = false){
		if($id && $this->db->delete("hr_transfers",array("id"=>$id))){
			$this->db->delete("hr_transfer_items",array("transfer_id"=>$id));
			return true;
		}
		return false;
	}
	public function updateTransferStatus($id = false, $status = false){
		if($id && $this->db->update("hr_transfers",array("status"=>$status),array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function getTransferByID($id = false){
		$q = $this->db->get_where("hr_transfers",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getTransferItems($travel_id = false){
		if($travel_id){
			$this->db->where('hr_transfer_items.transfer_id',$travel_id);
		}
		$this->db->select('hr_transfer_items.*,hr_employees.empcode,hr_employees.firstname,hr_employees.lastname');
		$this->db->join('hr_employees','hr_employees.id = hr_transfer_items.employee_id','inner');
		$q = $this->db->get('hr_transfer_items');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getTrainingByID($id = false){
		$q = $this->db->get_where("hr_training",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getTrainingItems($training_id = false){
		if($training_id){
			$this->db->where('hr_training_items.training_id',$training_id);
		}
		$this->db->select('hr_training_items.*,hr_employees.empcode,hr_employees.firstname,hr_employees.lastname');
		$this->db->join('hr_employees','hr_employees.id = hr_training_items.employee_id','inner');
		$q = $this->db->get('hr_training_items');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function addTraining($data = false, $items = false){
		if($this->db->insert("hr_training",$data)){
			$training_id = $this->db->insert_id();
			foreach($items as $item){
				$item['training_id'] = $training_id;
				$this->db->insert("hr_training_items",$item);
			}
			return true;
		}
		return false;
	}
	
	public function UpdateTraining($id = false, $data = false, $items = false){
		if($this->db->update('hr_training',$data,array('id'=>$id))){
			$this->db->delete('hr_training_items',array('training_id'=>$id));
			if($items){
				$this->db->insert_batch('hr_training_items',$items);
			}
			return true;
		}
		return false;
	}
	public function deleteTraining($id = false){
		if($this->db->delete('hr_training',array('id'=>$id))){
			$this->db->delete('hr_training_items',array('training_id'=>$id));
			return true;
		}
		return false;
	}
	public function getAllTrainers() {
        $q = $this->db->get_where('hr_trainers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getTrainerByID($id = false){
		$q = $this->db->get_where("hr_trainers",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function addTrainers($data = false){
		if($data && $this->db->insert('hr_trainers',$data)){
			return true;
		}
		return false;
	}
	public function updateTrainers($id = false, $data = false){
		if($id && $data && $this->db->update('hr_trainers',$data,array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function deleteTrainers($id = false){
		if($id && $this->db->delete('hr_trainers',array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function addEmployeeOnBoarding($data =array())
	{
		if($this->db->insert('hr_employees_on_boardng',$data)){
			return true;
		}
		return false;
	}
	public function updateOnBoardng($id = false, $data = array())
	{
		if($this->db->where("id",$id)->update('hr_employees_on_boardng', $data)){
			return true;
		}
		return false;
	}
	public function getOnBoardngByID($id= NULL)
	{
		$q = $this->db->get_where('hr_employees_on_boardng', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function deleteOnBoardng($id = false)
	{
		if($this->db->where("id",$id)->delete('hr_employees_on_boardng')){
			return true;
		}
		return false;
	}



}
?>