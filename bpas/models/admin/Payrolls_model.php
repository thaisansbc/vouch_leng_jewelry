<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Payrolls_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
	 public function getPayrollByParam($id,$month,$year)
    {
        $q = $this->db->get_where('staff_payslip', ['staff_id' => $id,'month' => $month, 'year' => $year], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	public function deleteAddition($id = false)
	{
		if($this->db->where("id",$id)->delete("pay_additions")){
			return true;
		}
		return false;
	}
	public function getAdditionByID($id = false)
	{
		$q = $this->db->get_where('pay_additions', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	
	public function addAddition($data = array())
	{
		if($this->db->insert("pay_additions", $data)){
			return true;
		}
		return false;
	}
	public function updateAddition($id = false, $data = array())
	{
		if($this->db->where("id",$id)->update("pay_additions", $data))
		{
			return true;
		}
		return false;
	}
	public function deleteDeduction($id = false)
	{
		if($this->db->where("id",$id)->delete("pay_deductions")){
			return true;
		}
		return false;
	}
	public function getDeductionByID($id = false)
	{
		$q = $this->db->get_where('pay_deductions', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	public function addDeduction($data = array())
	{
		if($this->db->insert("pay_deductions", $data)){
			return true;
		}
		return false;
	}
	public function updateDeduction($id = false, $data = array())
	{
		if($this->db->where("id",$id)->update("pay_deductions", $data))
		{
			return true;
		}
		return false;
	}
	public function getPositionByID($id = false){
		$q = $this->db->get_where("hr_positions",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getPositions($biller_id = false){
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
	public function getDepartmentByID($id = false){
		$q = $this->db->get_where("hr_departments",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getDepartments($biller_id = false){
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
	public function getGroupByID($id = false){
		$q = $this->db->get_where("hr_groups",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getGroups($department_id = false){
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
	public function checkBenefitSalaried($benefit_id = false){
		$this->db->where("pay_benefits.id",$benefit_id);
		$this->db->join("pay_benefit_items","pay_benefit_items.benefit_id = pay_benefits.id","inner");
		$this->db->join("pay_salaries","pay_salaries.month = pay_benefits.month AND pay_salaries.year = pay_benefits.year","inner");
		$this->db->join("pay_salary_items","pay_salary_items.salary_id = pay_salaries.id AND pay_benefit_items.employee_id = pay_salary_items.employee_id","inner");
		$q = $this->db->get("pay_benefits");
		if ($q->num_rows() > 0) {
			return true;
		}
		return false;
	}
	public function getBenefitedEmployee($month = false, $year = false, $status = false, $edit_id = false){
		if($status){
			$this->db->where("pay_benefits.status",$status);
		}
		if($edit_id){
			$this->db->where("pay_benefits.id !=",$edit_id);
		}
		$this->db->select("pay_benefit_items.*");
		$this->db->where("pay_benefits.month",$month);
		$this->db->where("pay_benefits.year",$year);
		$this->db->join("pay_benefits","pay_benefits.id = pay_benefit_items.benefit_id","inner");
		$q = $this->db->get("pay_benefit_items");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function getBenefitEmployee($biller_id = false,$position_id = false, $department_id = false, $group_id = false, $month = false, $year = false, $edit_id = false){
		$benefit_employees = $this->getBenefitedEmployee($month, $year, false, $edit_id);
		if($benefit_employees){
			foreach($benefit_employees as $benefit_employee){
				$benefited_employee[] = $benefit_employee->employee_id;
			}
			$this->db->where_not_in("att_approve_attedances.employee_id",$benefited_employee);
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
		if($month){
			$this->db->where("att_approve_attedances.month",$month);
		}
		if($year){
			$this->db->where("att_approve_attedances.year",$year);
		}
		$this->db->select("att_approve_attedances.employee_id,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_employees_working_info.additions,
							hr_employees_working_info.deductions,
							hr_employees_working_info.net_salary,
							(IFNULL(pay_cash_advances.amount,0) - IFNULL(pay_cash_advances.paid,0)) as cash_advanced
							");
		$this->db->join("hr_employees","hr_employees.id = att_approve_attedances.employee_id","inner");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = att_approve_attedances.employee_id","inner");
		$this->db->join("(SELECT 
								requested_by,
								sum(amount) as amount, 
								sum(paid) as paid 
							FROM 
								".$this->db->dbprefix('pay_cash_advances')."
							WHERE 
								status = 'approved'
							GROUP BY
								requested_by
						) as pay_cash_advances","pay_cash_advances.requested_by = att_approve_attedances.employee_id","left");
		$q = $this->db->get("att_approve_attedances");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}

	public function getDeductions($biller_id = false)
	{
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$biller_id = $this->session->userdata('biller_id');
		}
		if($biller_id){
			$this->db->where_in("IFNULL(biller_id,0)",array(0,$biller_id));
		}
		$q = $this->db->get_where("pay_deductions");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getAdditions($biller_id = false)
	{
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$biller_id = $this->session->userdata('biller_id');
		}
		if($biller_id){
			$this->db->where_in("IFNULL(biller_id,0)",array(0,$biller_id));
		}
		$q = $this->db->get_where("pay_additions");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function addBenefit($data = false, $items = false){
		if($data && $this->db->insert("pay_benefits",$data)){
			$benefit_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["benefit_id"] = $benefit_id;
					$this->db->insert("pay_benefit_items",$item);
				}
			}
			return true;
		}
		return false;
	}
	public function updateBenefit($id = false, $data = false, $items = false){
		if($data && $this->db->update("pay_benefits",$data,array("id"=>$id))){
			$this->db->delete("pay_benefit_items",array("benefit_id"=>$id));
			$this->db->insert_batch("pay_benefit_items",$items);
			return true;
		}
		return false;
	}
	public function deleteBenefit($id = false){
		if($id && $this->db->delete("pay_benefits",array("id"=>$id))){
			$this->db->delete("pay_benefit_items",array("benefit_id"=>$id));
			return true;
		}
		return false;
	}
	public function getBenefitByID($id = false){
		$q = $this->db->get_where("pay_benefits",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getBenefitItems($benefit_id = false){
		$this->db->select("pay_benefit_items.employee_id,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							pay_benefit_items.additions,
							pay_benefit_items.deductions,
							pay_benefit_items.cash_advanced,
							pay_benefit_items.cash_advance_ids
						");
		$this->db->join("hr_employees","hr_employees.id = pay_benefit_items.employee_id","LEFT");
		$this->db->where("benefit_id",$benefit_id);
		$q = $this->db->get_where("pay_benefit_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getEmployeeCashAdvanced($employee_id = false, $status = false){
		if($status){
			$this->db->where("status",$status);
		}
		$this->db->where("requested_by",$employee_id);
		$this->db->select("sum(IFNULL(amount,0) - IFNULL(paid,0)) as cash_advanced");
		$q = $this->db->get("pay_cash_advances");
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function updateBenefitStatus($id = false, $status = false){
		if($id && $this->db->update("pay_benefits",array("status"=>$status),array("id"=>$id))){
			$benefit = $this->getBenefitByID($id);
			if($status=="approved" && $benefit->grand_cash_advanced > 0){
				$benefit_items = $this->getBenefitItems($id);
				if($benefit_items){
					foreach($benefit_items as $benefit_item){
						if($benefit_item->cash_advance_ids){
							$cash_advance_ids = json_decode($benefit_item->cash_advance_ids);
							foreach($cash_advance_ids as $cash_advance_id){
								if($cash_advance_id->payback_amount > 0){
									$payment= array(
										'benefit_id' => $id,
										'date' => $benefit->date,
										'cash_advance_id' => $cash_advance_id->cash_advance_id,
										'amount' => $cash_advance_id->payback_amount,
										'paid_by' => 'cash',
										'note' => $benefit->note,
										'account_code' => $this->Settings->default_cash,
										'created_by' => $this->session->userdata('user_id'),
									);
									$this->db->insert("pay_cash_advance_paybacks",$payment);
									$this->synceCashAdvance($cash_advance_id->cash_advance_id);
								}
								
							}
						}
					}
				}
			}else if($benefit->grand_cash_advanced > 0){
				$this->db->delete("pay_cash_advance_paybacks",array("benefit_id"=>$id));
			}
			return true;
		}
		return false;
	}
	
	public function getSalariedEmployee($month = false, $year = false, $status = false, $edit_id = false){
		if($status){
			$this->db->where("pay_salaries.status",$status);
		}
		if($edit_id){
			$this->db->where("pay_salaries.id !=",$edit_id);
		}
		$this->db->select("pay_salary_items.*");
		$this->db->where("pay_salaries.month",$month);
		$this->db->where("pay_salaries.year",$year);
		$this->db->join("pay_salaries","pay_salaries.id = pay_salary_items.salary_id","inner");
		$q = $this->db->get("pay_salary_items");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	
	public function getSalaryEmployee($biller_id = false,$position_id = false, $department_id = false, $group_id = false, $month = false, $year = false, $edit_id = false){
		$salary_employees = $this->getSalariedEmployee($month, $year, false , $edit_id);
		if($salary_employees){
			foreach($salary_employees as $salary_employee){
				$salaried_employee[] = $salary_employee->employee_id;
			}
			$this->db->where_not_in("att_approve_attedances.employee_id",$salaried_employee);
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
		if($month){
			$this->db->where("att_approve_attedances.month",$month);
		}
		if($year){
			$this->db->where("att_approve_attedances.year",$year);
		}
		$this->db->select("	
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_employees.gender,
							TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age,
							hr_employees.nssf as nssf,
							hr_employees_working_info.payment_type,
							hr_employees_working_info.employee_date,
							hr_employees_working_info.status,
							hr_employees_working_info.department_id,
							hr_employees_working_info.group_id,
							hr_employees_working_info.position_id,
							hr_employees_working_info.seniority,
							hr_employees_working_info.net_salary as basic_salary,
							hr_employees_working_info.absent_rate,
							hr_employees_working_info.permission_rate,
							hr_employees_working_info.late_early_rate,
							hr_employees_working_info.normal_ot_rate,
							hr_employees_working_info.weekend_ot_rate,
							hr_employees_working_info.holiday_ot_rate,
							hr_employees_working_info.salary_tax,
							hr_employees_working_info.self_tax,
							hr_employees_working_info.seniority,
							hr_employees_working_info.pension,
							att_approve_attedances.employee_id,
							MIN(".$this->db->dbprefix('att_approve_attedances').".start_date) as start_date,
							MAX(".$this->db->dbprefix('att_approve_attedances').".end_date) as end_date,
							SUM(".$this->db->dbprefix('att_approve_attedances').".working_day) as working_day,
							SUM(".$this->db->dbprefix('att_approve_attedances').".present) as present,
							SUM(".$this->db->dbprefix('att_approve_attedances').".absent) as absent,
							SUM(".$this->db->dbprefix('att_approve_attedances').".permission) as permission,
							SUM(".$this->db->dbprefix('att_approve_attedances').".normal_ot) as normal_ot,
							SUM(".$this->db->dbprefix('att_approve_attedances').".weekend) as weekend,
							SUM(".$this->db->dbprefix('att_approve_attedances').".weekend_ot) as weekend_ot,
							SUM(".$this->db->dbprefix('att_approve_attedances').".holiday) as holiday,
							SUM(".$this->db->dbprefix('att_approve_attedances').".holiday_ot) as holiday_ot,
							SUM(".$this->db->dbprefix('att_approve_attedances').".annual_leave) as annual_leave,
							SUM(".$this->db->dbprefix('att_approve_attedances').".special_leave) as special_leave,
							SUM(".$this->db->dbprefix('att_approve_attedances').".sick_leave) as sick_leave,
							SUM(".$this->db->dbprefix('att_approve_attedances').".other_leave) as other_leave,
							SUM(".$this->db->dbprefix('att_approve_attedances').".half_pay_leave) as half_pay_leave,
							SUM(IFNULL(".$this->db->dbprefix('att_approve_attedances').".late,0) + IFNULL(".$this->db->dbprefix('att_approve_attedances').".leave_early,0)) as late,
							IFNULL(pay_pre_salaries.pre_salary,0) as pre_salary

						");
		$this->db->join("hr_employees","hr_employees.id = att_approve_attedances.employee_id","inner");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = att_approve_attedances.employee_id","inner");
		$this->db->join("(SELECT
							".$this->db->dbprefix('pay_pre_salaries').".`month`,
							".$this->db->dbprefix('pay_pre_salaries').".`year`,
							".$this->db->dbprefix('pay_pre_salary_items').".employee_id,
							SUM( ".$this->db->dbprefix('pay_pre_salary_items').".gross_salary ) AS pre_salary 
						FROM
							`".$this->db->dbprefix('pay_pre_salaries')."`
							INNER JOIN ".$this->db->dbprefix('pay_pre_salary_items')." ON ".$this->db->dbprefix('pay_pre_salary_items').".pre_salary_id = ".$this->db->dbprefix('pay_pre_salaries').".id 
						GROUP BY
							".$this->db->dbprefix('pay_pre_salaries').".`year`,
							".$this->db->dbprefix('pay_pre_salaries').".`month`,
							".$this->db->dbprefix('pay_pre_salary_items').".employee_id) as pay_pre_salaries","pay_pre_salaries.year = att_approve_attedances.year AND pay_pre_salaries.month = att_approve_attedances.month AND pay_pre_salaries.employee_id = att_approve_attedances.employee_id","LEFT");
		$this->db->group_by("att_approve_attedances.employee_id");
		$this->db->order_by("hr_employees.empcode");
		$q = $this->db->get("att_approve_attedances");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}

	public function getSalaryEmployeeDaily($biller_id = false,$position_id = false, $department_id = false, $group_id = false,$project_id=false,$month = false, $year = false, $edit_id = false){

			
		if($edit_id){
			$this->db->where("(IFNULL(".$this->db->dbprefix("att_approve_attedances").".status,0) = 0 OR ".$this->db->dbprefix("att_approve_attedances").".id IN (SELECT approved_att_id FROM ".$this->db->dbprefix('pay_salary_items')." WHERE salary_id = ".$edit_id."))");
		}else{
			$this->db->where("IFNULL(".$this->db->dbprefix("att_approve_attedances").".status,0)",0);
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
		if($month){
			$this->db->where("att_approve_attedances.month",$month);
		}
		if($year){
			$this->db->where("att_approve_attedances.year",$year);
		}

		$this->db->select("	
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_employees.gender,
							TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age,
							hr_employees.nssf as nssf,
							hr_employees_working_info.payment_type,
							hr_employees_working_info.employee_date,
							hr_employees_working_info.status,
							hr_employees_working_info.department_id,
							hr_employees_working_info.group_id,
							hr_employees_working_info.position_id,
							hr_employees_working_info.seniority,
							hr_employees_working_info.net_salary as basic_salary,
							hr_employees_working_info.absent_rate,
							hr_employees_working_info.permission_rate,
							hr_employees_working_info.late_early_rate,
							hr_employees_working_info.normal_ot_rate,
							hr_employees_working_info.weekend_ot_rate,
							hr_employees_working_info.holiday_ot_rate,
							hr_employees_working_info.salary_tax,
							hr_employees_working_info.self_tax,
							hr_employees_working_info.seniority,
							hr_employees_working_info.pension,
							group_concat(".$this->db->dbprefix('att_approve_attedances').".id) as approved_att_id,
							att_approve_attedances.employee_id,
							MIN(".$this->db->dbprefix('att_approve_attedances').".start_date) as start_date,
							MAX(".$this->db->dbprefix('att_approve_attedances').".end_date) as end_date,
							SUM(".$this->db->dbprefix('att_approve_attedances').".working_day) as working_day,
							SUM(".$this->db->dbprefix('att_approve_attedances').".present) as present,
							SUM(".$this->db->dbprefix('att_approve_attedances').".absent) as absent,
							SUM(".$this->db->dbprefix('att_approve_attedances').".permission) as permission,
							SUM(".$this->db->dbprefix('att_approve_attedances').".normal_ot) as normal_ot,
							SUM(".$this->db->dbprefix('att_approve_attedances').".weekend) as weekend,
							SUM(".$this->db->dbprefix('att_approve_attedances').".weekend_ot) as weekend_ot,
							SUM(".$this->db->dbprefix('att_approve_attedances').".holiday) as holiday,
							SUM(".$this->db->dbprefix('att_approve_attedances').".holiday_ot) as holiday_ot,
							SUM(".$this->db->dbprefix('att_approve_attedances').".annual_leave) as annual_leave,
							SUM(".$this->db->dbprefix('att_approve_attedances').".special_leave) as special_leave,
							SUM(".$this->db->dbprefix('att_approve_attedances').".sick_leave) as sick_leave,
							SUM(".$this->db->dbprefix('att_approve_attedances').".other_leave) as other_leave,
							SUM(".$this->db->dbprefix('att_approve_attedances').".half_pay_leave) as half_pay_leave,
							SUM(IFNULL(".$this->db->dbprefix('att_approve_attedances').".late,0) + IFNULL(".$this->db->dbprefix('att_approve_attedances').".leave_early,0)) as late,
							IFNULL(pay_pre_salaries.pre_salary,0) as pre_salary

						");
		$this->db->join("hr_employees","hr_employees.id = att_approve_attedances.employee_id","inner");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = att_approve_attedances.employee_id","inner");
		$this->db->join("(SELECT
							".$this->db->dbprefix('pay_pre_salaries').".`month`,
							".$this->db->dbprefix('pay_pre_salaries').".`year`,
							".$this->db->dbprefix('pay_pre_salary_items').".employee_id,
							SUM( ".$this->db->dbprefix('pay_pre_salary_items').".gross_salary ) AS pre_salary 
						FROM
							`".$this->db->dbprefix('pay_pre_salaries')."`
							INNER JOIN ".$this->db->dbprefix('pay_pre_salary_items')." ON ".$this->db->dbprefix('pay_pre_salary_items').".pre_salary_id = ".$this->db->dbprefix('pay_pre_salaries').".id 
						GROUP BY
							".$this->db->dbprefix('pay_pre_salaries').".`year`,
							".$this->db->dbprefix('pay_pre_salaries').".`month`,
							".$this->db->dbprefix('pay_pre_salary_items').".employee_id) as pay_pre_salaries","pay_pre_salaries.year = att_approve_attedances.year AND pay_pre_salaries.month = att_approve_attedances.month AND pay_pre_salaries.employee_id = att_approve_attedances.employee_id","LEFT");
		$this->db->group_by("att_approve_attedances.employee_id");
		$this->db->order_by("hr_employees.empcode");
		$this->db->where("hr_employees_working_info.payment_type","daily");

		$q = $this->db->get("att_approve_attedances");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function getSalaryByID($id = false){
		$q = $this->db->get_where("pay_salaries",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getSalaryItems($salary_id = false){
		$this->db->select("pay_salary_items.*,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_employees.nssf,
							hr_employees_working_info.self_tax

						");
		$this->db->join("hr_employees","hr_employees.id = pay_salary_items.employee_id","LEFT");

		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","INNER");

		$this->db->where("salary_id",$salary_id);
		$q = $this->db->get_where("pay_salary_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getSalaryBankItems($salary_id = false){
		$this->db->select("pay_salary_items.*,
							hr_employees_bank.bank_account,
							hr_employees_bank.account_no,
							hr_employees_bank.account_name
						");
		$this->db->join("hr_employees_bank","hr_employees_bank.employee_id = pay_salary_items.employee_id","INNER");
		$this->db->where("salary_id",$salary_id);
		$q = $this->db->get_where("pay_salary_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getSalaryItemByID($id = false){
		if($id){
			$this->db->where("pay_salary_items.id",$id);
		}
		$this->db->select("pay_salary_items.*,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_positions.name as position,
							hr_departments.name as department,
							hr_groups.name as group,
							pay_benefit_items.additions,
							pay_benefit_items.deductions
						");
		$this->db->join("pay_salaries","pay_salaries.id = pay_salary_items.salary_id","inner");		
		$this->db->join("pay_benefits","pay_benefits.month = pay_salaries.month AND pay_benefits.year = pay_salaries.year","left");
		$this->db->join("pay_benefit_items","pay_benefit_items.benefit_id = pay_benefits.id AND pay_benefit_items.employee_id = pay_salary_items.employee_id","left");
		$this->db->join("hr_employees","hr_employees.id = pay_salary_items.employee_id","left");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_salary_items.employee_id","left");
		$this->db->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left");
		$this->db->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left");
		$this->db->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left");
		$this->db->group_by("pay_salary_items.id");
		$q = $this->db->get_where("pay_salary_items");
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getPayslips($year = false, $month = false, $biller_id = false, $position_id = false, $department_id = false, $group_id = false, $employee_id = false){
		if($year){
			$this->db->where("pay_salaries.year",$year);
		}
		if($month){
			$this->db->where("pay_salaries.month",$month);
		}
		if($biller_id){
			$this->db->where("pay_salaries.biller_id",$biller_id);
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
		if($employee_id){
			$this->db->where("pay_salary_items.employee_id",$employee_id);
		}
		$this->db->select("
							pay_salary_items.*,
							pay_salaries.year,
							pay_salaries.month,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_positions.name as position,
							hr_departments.name as department,
							hr_groups.name as group,
							pay_benefit_items.additions,
							pay_benefit_items.deductions,
							companies.logo,
							companies.name,
							companies.city,
							companies.email,
							companies.address,
							companies.phone
						");
		$this->db->join("pay_salaries","pay_salaries.id = pay_salary_items.salary_id","inner");		
		$this->db->join("pay_benefits","pay_benefits.month = pay_salaries.month AND pay_benefits.year = pay_salaries.year","left");
		$this->db->join("pay_benefit_items","pay_benefit_items.benefit_id = pay_benefits.id AND pay_benefit_items.employee_id = pay_salary_items.employee_id","left");
		$this->db->join("hr_employees","hr_employees.id = pay_salary_items.employee_id","left");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_salary_items.employee_id","left");
		$this->db->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left");
		$this->db->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left");
		$this->db->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left");
		$this->db->join("companies","companies.id = pay_salaries.biller_id","left");
		$this->db->group_by("pay_salary_items.id");
		$q = $this->db->get_where("pay_salary_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function addSalary($data = false, $items = false){
		if($this->db->insert("pay_salaries",$data)){
			$salary_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["salary_id"] = $salary_id;
					$this->db->insert("pay_salary_items",$item);
				}
			}
			$this->synceAttendance($data['month'],$data['year']);
			return true;
		}
		return false;
	}
	public function updateSalary($id = false, $data = false, $items = false){
		if($data && $this->db->update("pay_salaries",$data,array("id"=>$id))){
			$this->db->delete("pay_salary_items",array("salary_id"=>$id));
			$this->db->insert_batch("pay_salary_items",$items);
			$this->synceAttendance($data['month'],$data['year']);
			return true;
		}
		return false;
	}
	public function deleteSalary($id = false){
		$salary = $this->getSalaryByID($id);
		if($id && $this->db->delete("pay_salaries",array("id"=>$id))){
			$this->db->delete("pay_salary_items",array("salary_id"=>$id));
			$this->synceAttendance($salary->month,$salary->year);
			return true;
		}
		return false;
	}
	public function getAdditionDetailsBySalary($salary_id = false){
		$this->db->select("pay_salary_items.addition,pay_benefit_items.additions");
		$this->db->where("pay_salaries.id",$salary_id);
		$this->db->join("pay_salary_items","pay_salary_items.salary_id = pay_salaries.id","inner");
		$this->db->join("pay_benefits","pay_benefits.month = pay_salaries.month AND pay_benefits.year = pay_salaries.year","left");
		$this->db->join("pay_benefit_items","pay_benefit_items.benefit_id = pay_benefits.id AND pay_benefit_items.employee_id = pay_salary_items.employee_id","left");
		$this->db->group_by("pay_salary_items.id");
		$q = $this->db->get("pay_salaries");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function updateSalaryStatus($id = false, $status = false){
		if($id && $this->db->update("pay_salaries",array("status"=>$status),array("id"=>$id))){
			if($this->Settings->module_account == 1 && $status=="approved"){
				$accTrans = false;
				$salary = $this->getSalaryByID($id);
				$salaryAcc = $this->site->getAccountSettingByBiller($salary->biller_id);
				if($salary->total_gross_salary > 0){
					$accTrans[] = array(
						'tran_no' => $id,
						'tran_type' => 'Salary',
						'tran_date' => $salary->date,
						'reference_no' =>  $salary->month."/".$salary->year,
						'account_code' => $salaryAcc->salary_expense_acc,
						'amount' => $salary->total_gross_salary,
						'narrative' => 'Staff Salary for '.$salary->month."/".$salary->year,
						'description' => $salary->note,
						'biller_id' => $salary->biller_id,
						'created_by' => $salary->created_by
					);
				}
				if($salary->total_overtime > 0){
					$accTrans[] = array(
						'tran_no' => $id,
						'tran_type' => 'Salary',
						'tran_date' => $salary->date,
						'reference_no' =>  $salary->month."/".$salary->year,
						'account_code' => $salaryAcc->overtime_acc,
						'amount' => $salary->total_overtime,
						'narrative' => 'Staff Overtime for '.$salary->month."/".$salary->year,
						'description' => $salary->note,
						'biller_id' => $salary->biller_id,
						'created_by' => $salary->created_by
					);
				}
				if($salary->total_tax_payment > 0){
					$accTrans[] = array(
						'tran_no' => $id,
						'tran_type' => 'Salary',
						'tran_date' => $salary->date,
						'reference_no' =>  $salary->month."/".$salary->year,
						'account_code' => $salaryAcc->salary_expense_acc,
						'amount' => $salary->total_tax_payment,
						'narrative' => 'Staff Tax for '.$salary->month."/".$salary->year,
						'description' => $salary->note,
						'biller_id' => $salary->biller_id,
						'created_by' => $salary->created_by
					);
					
					$accTrans[] = array(
						'tran_no' => $id,
						'tran_type' => 'Salary',
						'tran_date' => $salary->date,
						'reference_no' =>  $salary->month."/".$salary->year,
						'account_code' => $salaryAcc->vat_output,
						'amount' => $salary->total_tax_payment * (-1),
						'narrative' => 'Staff Tax for '.$salary->month."/".$salary->year,
						'description' => $salary->note,
						'biller_id' => $salary->biller_id,
						'created_by' => $salary->created_by
					);
				}
				if($salary->total_cash_advanced > 0){
					$accTrans[] = array(
						'tran_no' => $id,
						'tran_type' => 'Salary',
						'tran_date' => $salary->date,
						'reference_no' =>  $salary->month."/".$salary->year,
						'account_code' => $salaryAcc->cash_advance_acc,
						'amount' => $salary->total_cash_advanced * (-1),
						'narrative' => 'Staff Payback Cash Advanced for '.$salary->month."/".$salary->year,
						'description' => $salary->note,
						'biller_id' => $salary->biller_id,
						'created_by' => $salary->created_by
					);
				}
				
				if($salary->total_addition > 0){
					$acc_addtions = false;
					$additions = $this->getAdditionDetailsBySalary($id);
					if($additions){
						foreach($additions as $addition){
							if($addition->addition > 0){
								$d_addtions = json_decode($addition->additions);
								if($d_addtions){
									foreach($d_addtions as $d_addtion){
										if(isset($acc_addtions[$d_addtion->id])){
											$acc_addtions[$d_addtion->id] += $d_addtion->value;
										}else{
											$acc_addtions[$d_addtion->id] = $d_addtion->value;
										}
									}
								}
							}
						}
						if($acc_addtions){
							foreach($acc_addtions as $addition_id => $addition_value){
								$info_addition = $this->getAdditionByID($addition_id);
								$accTrans[] = array(
									'tran_no' => $id,
									'tran_type' => 'Salary',
									'tran_date' => $salary->date,
									'reference_no' =>  $salary->month."/".$salary->year,
									'account_code' => $info_addition->account,
									'amount' => $addition_value,
									'narrative' => 'Staff '.$info_addition->name.' for '.$salary->month."/".$salary->year,
									'description' => $salary->note,
									'biller_id' => $salary->biller_id,
									'created_by' => $salary->created_by
								);
							}
						}
					}
				}
				
				
				$accTrans[] = array(
						'tran_no' => $id,
						'tran_type' => 'Salary',
						'tran_date' => $salary->date,
						'reference_no' =>  $salary->month."/".$salary->year,
						'account_code' => $salaryAcc->default_salary_expense,
						'amount' => $salary->total_net_pay * (-1),
						'narrative' => 'Staff Salary for '.$salary->month."/".$salary->year,
						'description' => $salary->note,
						'biller_id' => $salary->biller_id,
						'created_by' => $salary->created_by
					);
				$this->db->insert_batch("gl_trans",$accTrans);				
			}else if($this->Settings->module_account == 1 && $status=="pending"){
				$this->site->deleteAccTran('Salary',$id);
			}
			return true;
		}
		return false;
	}
	
	public function getPaymentEmployee($biller_id = false,$position_id = false, $department_id = false, $group_id = false, $month = false, $year = false){
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
		if($month){
			$this->db->where("pay_salaries.month",$month);
		}
		if($year){
			$this->db->where("pay_salaries.year",$year);
		}
		$this->db->where("pay_salary_items.payment_status !=","paid");
		$this->db->where("pay_salaries.status","approved");
		$this->db->select("	
							pay_salary_items.employee_id,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							(IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_payment,0) - IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_paid,0)) as tax_payment,
							(IFNULL(".$this->db->dbprefix('pay_salary_items').".net_salary,0) - IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0)) as net_salary
						");
		$this->db->join("pay_salaries","pay_salaries.id = pay_salary_items.salary_id","inner");				
		$this->db->join("hr_employees","hr_employees.id = pay_salary_items.employee_id","inner");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_salary_items.employee_id","inner");
		$q = $this->db->get("pay_salary_items");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function getPaymentByID($id = false){
		$q = $this->db->get_where("pay_payments",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getPaymentItems($payment_id = false){
		$this->db->select("pay_payment_items.*,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							IFNULL(pay_salaries.tax_payment,0) as tax_payment,
							IFNULL(pay_salaries.net_salary,0) as net_salary
						");
		$this->db->join("pay_payments","pay_payments.id = pay_payment_items.payment_id","INNER");		
		$this->db->join("hr_employees","hr_employees.id = pay_payment_items.employee_id","LEFT");
		$this->db->join("(SELECT 
								".$this->db->dbprefix('pay_salaries').".month,
								".$this->db->dbprefix('pay_salaries').".year,
								".$this->db->dbprefix('pay_salary_items').".employee_id,
								(IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_payment,0) - IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_paid,0)) as tax_payment,
								(IFNULL(".$this->db->dbprefix('pay_salary_items').".net_salary,0) - IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0)) as net_salary
							FROM 
								".$this->db->dbprefix('pay_salaries')."
							INNER JOIN ".$this->db->dbprefix('pay_salary_items')." ON ".$this->db->dbprefix('pay_salary_items').".salary_id = ".$this->db->dbprefix('pay_salaries').".id
							GROUP BY 
								".$this->db->dbprefix('pay_salaries').".month,
								".$this->db->dbprefix('pay_salaries').".year,
								".$this->db->dbprefix('pay_salary_items').".employee_id
						) as pay_salaries","pay_salaries.month = pay_payments.month AND pay_salaries.year = pay_payments.year AND pay_salaries.employee_id = pay_payment_items.employee_id","LEFT");
		$this->db->where("pay_payment_items.payment_id",$payment_id);
		$q = $this->db->get_where("pay_payment_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function addPaymentghjfgh_($data = false, $items = false){
		if($items){
			foreach($items as $item){
				$this->email_payslip($data,$item);
			}
			return true;
		}
		return false;
	}
	public function addPayment($data = false, $items = false){
		if($this->db->insert("pay_payments",$data)){
			$payment_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["payment_id"] = $payment_id;
					$this->db->insert("pay_payment_items",$item);
					$this->email_payslip($data,$item);
				}
			}
			if($this->Settings->module_account == 1){
				$accTrans = false;
				$paymentAcc = $this->site->getAccountSettingByBiller($data['biller_id']);
				$total_paid = $data['total_tax_paid'] + $data['total_salary_paid'];
				$accTrans[] = array(
					'tran_no' 	=> $payment_id,
					'tran_type' => 'Salary Payment',
					'tran_date' => $data['date'],
					'reference_no' =>  $data['month']."/".$data['year'],
					'account_code' => $data['account_code'],
					'amount' => $total_paid * (-1),
					'narrative' => 'Staff Payment for '.$data['month']."/".$data['year'],
					'description' => $data['note'],
					'biller_id' => $data['biller_id'],
					'created_by' => $data['created_by']
				);
				$accTrans[] = array(
					'tran_no' => $payment_id,
					'tran_type' => 'Salary Payment',
					'tran_date' => $data['date'],
					'reference_no' =>  $data['month']."/".$data['year'],
					'account_code' => $paymentAcc->default_salary_expense,
					'amount' => $total_paid,
					'narrative' => 'Staff Payment for '.$data['month']."/".$data['year'],
					'description' => $data['note'],
					'biller_id' => $data['biller_id'],
					'created_by' => $data['created_by']
				);
				$this->db->insert_batch("gl_trans",$accTrans);
			}
			//$this->synceSalaryPayment($data["salary_id"]);
			$this->synceSalaryPayment($data['month'],$data['year']);
			return true;
		}
		return false;
	}
	public function email_payslip($data=null,$item = null)
    {
    	$salary_id  	= $data['salary_id'];
    	$employee_id  	= $item['employee_id'];
        $salary 		= $this->payrolls_model->getSalaryByID($salary_id);        
        $employee 		= $this->payrolls_model->getEmployeeByID($employee_id);
        $biller   		= $this->site->getCompanyByID($salary->biller_id);

        $to      		= $employee->email;//'mrkimpheng@gmail.com';
        if($to){
	        $subject = lang('salary').' '.$data['month'].'/'.$data['year'];
	        $cc = null;  
	        $bcc = null;

            $this->load->library('parser');
            $parse_data = [
                'month' 		   => $data['month'].'/'.$data['year'],
                'staff_name'       => $employee->firstname,
                'salary'           => $this->bpas->formatDecimal($item['salary_paid']),
                'company'		   => ($biller->company && $biller->company != '-' ? $biller->company : $biller->name),
                'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company && $biller->company != '-' ? $biller->company : $biller->name) . '"/>',
            ];
            
            $msg      = file_get_contents('./themes/'.$this->Settings->theme.'/admin/views/email_templates/payslip.html');
       
            $message  = $this->parser->parse_string($msg, $parse_data);

            $btn_code = '<div id="payment_buttons" class="text-center margin010">';
            if ($item['salary_paid'] != '0.00') {
                $btn_code .= $item['salary_paid'];
            }

            $btn_code 	.= '<div class="clearfix"></div></div>';
            $message    = $message;
            $attachment = null;//$this->pdf($id, null, 'S');
            try {
                if ($this->bpas->send_email($to, $subject, $message, null, null, null, $cc, $bcc)) {
                    delete_files($attachment);
                }
            } catch (Exception $e) {
                $this->session->set_flashdata('error', $e->getMessage());
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
	public function updatePayment($id = false, $data = false, $items = false){
		if($data && $this->db->update("pay_payments",$data,array("id"=>$id))){
			$this->db->delete("pay_payment_items",array("payment_id"=>$id));
			$this->db->insert_batch("pay_payment_items",$items);
			if($this->Settings->module_account == 1){
				$accTrans = false;
				$this->site->deleteAccTran('Salary Payment',$id);
				$paymentAcc = $this->site->getAccountSettingByBiller($data['biller_id']);
				$total_paid = $data['total_tax_paid'] + $data['total_salary_paid'];
				$accTrans[] = array(
					'tran_no' => $id,
					'tran_type' => 'Salary Payment',
					'tran_date' => $data['date'],
					'reference_no' =>  $data['month']."/".$data['year'],
					'account_code' => $data['account_code'],
					'amount' => $total_paid * (-1),
					'narrative' => 'Staff Payment for '.$data['month']."/".$data['year'],
					'description' => $data['note'],
					'biller_id' => $data['biller_id'],
					'created_by' => $data['updated_by']
				);
				$accTrans[] = array(
					'tran_no' => $id,
					'tran_type' => 'Salary Payment',
					'tran_date' => $data['date'],
					'reference_no' =>  $data['month']."/".$data['year'],
					'account_code' => $paymentAcc->default_salary_expense,
					'amount' => $total_paid,
					'narrative' => 'Staff Payment for '.$data['month']."/".$data['year'],
					'description' => $data['note'],
					'biller_id' => $data['biller_id'],
					'created_by' => $data['updated_by']
				);
				$this->db->insert_batch("gl_trans",$accTrans);
			}
			$this->synceSalaryPayment($data['month'],$data['year']);
			return true;
		}
		return false;
	}
	public function deletePayment($id = false){
		$payment = $this->getPaymentByID($id);
		if($id && $this->db->delete("pay_payments",array("id"=>$id))){
			$this->db->delete("pay_payment_items",array("payment_id"=>$id));
			$this->site->deleteAccTran('Salary Payment',$id);
			$this->synceSalaryPayment($payment->month,$payment->year);
			return true;
		}
		return false;
	}
	public function synceSalaryPaymentDaily($salary_id = false){
		if($salary_id){

			$this->db->query("UPDATE ".$this->db->dbprefix('pay_salary_items')."
				LEFT JOIN ( SELECT pre_salary_item_id, sum( amount ) AS amount FROM ".$this->db->dbprefix('pay_pre_salary_payment_items')." GROUP BY pre_salary_item_id ) AS pay_pre_salary_payment_items ON pay_pre_salary_payment_items.pre_salary_item_id = ".$this->db->dbprefix('pay_salary_items').".id 
				SET ".$this->db->dbprefix('pay_salary_items').".net_paid = IFNULL(pay_pre_salary_payment_items.amount,0),
				".$this->db->dbprefix('pay_salary_items').".payment_status = IF(IFNULL( pay_pre_salary_payment_items.amount, 0 ) = 0,'pending',IF( ROUND(".$this->db->dbprefix('pay_salary_items').".gross_salary,".$this->Settings->decimals.") = ROUND(pay_pre_salary_payment_items.amount,".$this->Settings->decimals."), 'paid', 'partial' )) 
				WHERE
					".$this->db->dbprefix('pay_salary_items').".salary_id = ".$salary_id." ");

			$this->db->query("UPDATE ".$this->db->dbprefix('pay_salaries')."
				INNER JOIN ( SELECT salary_id, sum( net_paid ) AS total_paid FROM ".$this->db->dbprefix('pay_salary_items')." WHERE salary_id = ".$salary_id." ) AS pay_salary_items ON pay_salary_items.salary_id = ".$this->db->dbprefix('pay_salaries').".id 
				SET 
					".$this->db->dbprefix('pay_salaries').".total_paid = IFNULL( pay_salary_items.total_paid, 0 ),
					".$this->db->dbprefix('pay_salaries').".payment_status = IF(IFNULL( pay_salary_items.total_paid, 0 ) = 0,'pending',IF( ROUND(pay_salary_items.total_paid,".$this->Settings->decimals.") = ROUND(".$this->db->dbprefix('pay_salaries').".total_gross_salary,".$this->Settings->decimals."), 'paid', 'partial' )) 
				WHERE
					".$this->db->dbprefix('pay_salaries').".id = ".$salary_id.";
					
			");	
		}
		
	}
	public function synceSalaryPayment__($salary_id){
		$this->db->trans_start();
		$this->db->query("UPDATE ".$this->db->dbprefix('pay_salary_items')."
					INNER JOIN ".$this->db->dbprefix('pay_salaries')." ON ".$this->db->dbprefix('pay_salaries').".id = ".$this->db->dbprefix('pay_salary_items').".salary_id
					LEFT JOIN (
						SELECT
							".$this->db->dbprefix('pay_payments').".`year`,
							".$this->db->dbprefix('pay_payments').".`month`,
							".$this->db->dbprefix('pay_payment_items').".employee_id,
							SUM(IFNULL( ".$this->db->dbprefix('pay_payment_items').".tax_paid, 0 )) AS tax_paid,
							SUM(IFNULL( ".$this->db->dbprefix('pay_payment_items').".salary_paid, 0 )) AS salary_paid 
						FROM
							".$this->db->dbprefix('pay_payment_items')."
							INNER JOIN ".$this->db->dbprefix('pay_payments')." ON ".$this->db->dbprefix('pay_payments').".id = ".$this->db->dbprefix('pay_payment_items').".payment_id 
						WHERE ".$this->db->dbprefix('pay_payments').".`salary_id` = $salary_id

						) AS bpas_pay_payments ON bpas_pay_payments.`month` = ".$this->db->dbprefix('pay_salaries').".`month` 
						AND bpas_pay_payments.`year` = ".$this->db->dbprefix('pay_salaries').".`year` 
						AND bpas_pay_payments.employee_id = ".$this->db->dbprefix('pay_salary_items').".employee_id 
						SET ".$this->db->dbprefix('pay_salary_items').".tax_paid = IFNULL( bpas_pay_payments.tax_paid, 0 ),
						".$this->db->dbprefix('pay_salary_items').".salary_paid = IFNULL( bpas_pay_payments.salary_paid, 0 )
					WHERE ".$this->db->dbprefix('pay_salaries').".`id` = ".$salary_id."
				");
									
		$this->db->query("UPDATE " . $this->db->dbprefix('pay_salary_items')."
					INNER JOIN ".$this->db->dbprefix('pay_salaries')." ON ".$this->db->dbprefix('pay_salaries').".id = ".$this->db->dbprefix('pay_salary_items').".salary_id 
						SET ".$this->db->dbprefix('pay_salary_items').".payment_status = IF(round((IFNULL(tax_paid,0) + IFNULL(salary_paid,0)),".$this->Settings->decimals.") = 0,'pending',IF(net_pay = round(IFNULL(salary_paid,0),".$this->Settings->decimals."),'paid','partial')) 
					WHERE ".$this->db->dbprefix('pay_salaries').".`id` = ".$salary_id."");
									
		$this->db->query("UPDATE " . $this->db->dbprefix('pay_salaries')."
								INNER JOIN (
									SELECT
										".$this->db->dbprefix('pay_salary_items')." .salary_id,
										SUM(IFNULL(".$this->db->dbprefix('pay_salary_items')." .tax_paid, 0 )) as total_tax_paid,
										SUM(IFNULL(".$this->db->dbprefix('pay_salary_items')." .salary_paid, 0 )) as total_salary_paid,
										round(SUM(IFNULL( ".$this->db->dbprefix('pay_salary_items')." .salary_paid,0)),".$this->Settings->decimals.") AS total_paid 
									FROM
										".$this->db->dbprefix('pay_salary_items')." 
									GROUP BY
										".$this->db->dbprefix('pay_salary_items')." .salary_id 
									) AS bpas_pay_salary_items ON bpas_pay_salary_items.salary_id = ".$this->db->dbprefix('pay_salaries').".id 
									SET ".$this->db->dbprefix('pay_salaries').".total_paid = bpas_pay_salary_items.total_paid,
										".$this->db->dbprefix('pay_salaries').".total_tax_paid = bpas_pay_salary_items.total_tax_paid,
										".$this->db->dbprefix('pay_salaries').".total_salary_paid = bpas_pay_salary_items.total_salary_paid 
								WHERE ".$this->db->dbprefix('pay_salaries').".`id` = ".$salary_id."");
									
		$this->db->query("UPDATE " . $this->db->dbprefix('pay_salaries')." 
								SET ".$this->db->dbprefix('pay_salaries').".payment_status = IF(IFNULL(".$this->db->dbprefix('pay_salaries').".total_paid,0) = 0,'pending',IF(IFNULL( ".$this->db->dbprefix('pay_salaries').".total_net_pay,0) = IFNULL(".$this->db->dbprefix('pay_salaries').".total_paid,0), 'paid', 'partial')) 
								WHERE ".$this->db->dbprefix('pay_salaries').".`id` = ".$salary_id."");
		
		$this->db->trans_complete(); 
		return true;				
	}
	public function synceSalaryPayment($month = false, $year = false){
		$this->db->trans_start();
		$this->db->query("UPDATE ".$this->db->dbprefix('pay_salary_items')."
					INNER JOIN ".$this->db->dbprefix('pay_salaries')." ON ".$this->db->dbprefix('pay_salaries').".id = ".$this->db->dbprefix('pay_salary_items').".salary_id
					LEFT JOIN (
						SELECT
							".$this->db->dbprefix('pay_payments').".`year`,
							".$this->db->dbprefix('pay_payments').".`month`,
							".$this->db->dbprefix('pay_payment_items').".employee_id,
							SUM(IFNULL( ".$this->db->dbprefix('pay_payment_items').".tax_paid, 0 )) AS tax_paid,
							SUM(IFNULL( ".$this->db->dbprefix('pay_payment_items').".salary_paid, 0 )) AS salary_paid 
						FROM
							".$this->db->dbprefix('pay_payment_items')."
							INNER JOIN ".$this->db->dbprefix('pay_payments')." ON ".$this->db->dbprefix('pay_payments').".id = ".$this->db->dbprefix('pay_payment_items').".payment_id 
						GROUP BY
							".$this->db->dbprefix('pay_payments').".`year`,
							".$this->db->dbprefix('pay_payments').".`month`,
							".$this->db->dbprefix('pay_payment_items').".employee_id 
						) AS bpas_pay_payments ON bpas_pay_payments.`month` = ".$this->db->dbprefix('pay_salaries').".`month` 
						AND bpas_pay_payments.`year` = ".$this->db->dbprefix('pay_salaries').".`year` 
						AND bpas_pay_payments.employee_id = ".$this->db->dbprefix('pay_salary_items').".employee_id 
						SET ".$this->db->dbprefix('pay_salary_items').".tax_paid = IFNULL( bpas_pay_payments.tax_paid, 0 ),
						".$this->db->dbprefix('pay_salary_items').".salary_paid = IFNULL( bpas_pay_payments.salary_paid, 0 )
					WHERE
						".$this->db->dbprefix('pay_salaries').".`month` = '".$month."'
						AND ".$this->db->dbprefix('pay_salaries').".`year` = '".$year."'");
									
		$this->db->query("UPDATE " . $this->db->dbprefix('pay_salary_items')."
					INNER JOIN ".$this->db->dbprefix('pay_salaries')." ON ".$this->db->dbprefix('pay_salaries').".id = ".$this->db->dbprefix('pay_salary_items').".salary_id 
						SET ".$this->db->dbprefix('pay_salary_items').".payment_status = IF(round((IFNULL(tax_paid,0) + IFNULL(salary_paid,0)),".$this->Settings->decimals.") = 0,'pending',IF(net_pay = round(IFNULL(salary_paid,0),".$this->Settings->decimals."),'paid','partial')) 
					WHERE
						".$this->db->dbprefix('pay_salaries').".`month` = '".$month."' 
						AND ".$this->db->dbprefix('pay_salaries').".`year` = '".$year."'");
									
		$this->db->query("UPDATE " . $this->db->dbprefix('pay_salaries')."
								INNER JOIN (
									SELECT
										".$this->db->dbprefix('pay_salary_items')." .salary_id,
										SUM(IFNULL(".$this->db->dbprefix('pay_salary_items')." .tax_paid, 0 )) as total_tax_paid,
										SUM(IFNULL(".$this->db->dbprefix('pay_salary_items')." .salary_paid, 0 )) as total_salary_paid,
										round(SUM(IFNULL( ".$this->db->dbprefix('pay_salary_items')." .salary_paid,0)),".$this->Settings->decimals.") AS total_paid 
									FROM
										".$this->db->dbprefix('pay_salary_items')." 
									GROUP BY
										".$this->db->dbprefix('pay_salary_items')." .salary_id 
									) AS bpas_pay_salary_items ON bpas_pay_salary_items.salary_id = ".$this->db->dbprefix('pay_salaries').".id 
									SET ".$this->db->dbprefix('pay_salaries').".total_paid = bpas_pay_salary_items.total_paid,
										".$this->db->dbprefix('pay_salaries').".total_tax_paid = bpas_pay_salary_items.total_tax_paid,
										".$this->db->dbprefix('pay_salaries').".total_salary_paid = bpas_pay_salary_items.total_salary_paid 
								WHERE
									".$this->db->dbprefix('pay_salaries').".`month` = '".$month."' 
									AND ".$this->db->dbprefix('pay_salaries').".`year` = '".$year."'");
									
		$this->db->query("UPDATE " . $this->db->dbprefix('pay_salaries')." 
								SET ".$this->db->dbprefix('pay_salaries').".payment_status = IF(IFNULL(".$this->db->dbprefix('pay_salaries').".total_paid,0) = 0,'pending',IF(IFNULL( ".$this->db->dbprefix('pay_salaries').".total_net_pay,0) = IFNULL(".$this->db->dbprefix('pay_salaries').".total_paid,0), 'paid', 'partial')) 
								WHERE
									".$this->db->dbprefix('pay_salaries').".`month` = '".$month."' 
									AND ".$this->db->dbprefix('pay_salaries').".`year` = '".$year."'");
		
		$this->db->trans_complete(); 
		return true;				
	}
	public function getEmployeeByID($id = false){
		$this->db->where("hr_employees.id",$id);
		$this->db->select("hr_employees.*,hr_employees_working_info.*");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","left");
		$q = $this->db->get("hr_employees");
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getCashAdvanceByID($id = false){
		$q = $this->db->get_where("pay_cash_advances",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function addCashAdvance($data = false){
		if($data && $this->db->insert("pay_cash_advances",$data)){
			return true;
		}
		return false;
	}
	public function updateCashAdvance($id = false, $data = false){
		if($id && $this->db->update("pay_cash_advances",$data,array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function deleteCashAdavnce($id = false){
		if($id && $this->db->delete("pay_cash_advances",array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function updateCashAdvanceStatus($id = false, $status = false){
		if($id && $this->db->update("pay_cash_advances",array("status"=>$status),array("id"=>$id))){
			if($this->Settings->module_account == 1){
				if($status=="approved"){
					$cash_advance = $this->getCashAdvanceByID($id);
					$employee_detail = $this->getEmployeeByID($cash_advance->requested_by);
					$employee_name = ($employee_detail->firstname.' '.$employee_detail->lastname);
					$accTrans = false;
					$cashAdvanceAcc = $this->site->getAccountSettingByBiller($cash_advance->biller_id);
					$accTrans[] = array(
						'tran_no' => $id,
						'tran_type' => 'CashAdvance',
						'tran_date' => $cash_advance->date,
						'reference_no' =>  $cash_advance->reference_no,
						'account_code' => $cash_advance->account_code,
						'amount' => $cash_advance->amount * (-1),
						'narrative' => 'Cash Advance '.$employee_name,
						'description' => $cash_advance->description,
						'biller_id' => $cash_advance->biller_id,
						'created_by' => $cash_advance->created_by
					);
					$accTrans[] = array(
						'tran_no' => $id,
						'tran_type' => 'CashAdvance',
						'tran_date' => $cash_advance->date,
						'reference_no' =>  $cash_advance->reference_no,
						'account_code' => $cashAdvanceAcc->cash_advance_acc,
						'amount' => $cash_advance->amount,
						'narrative' => 'Cash Advance '.$employee_name,
						'description' => $cash_advance->description,
						'biller_id' => $cash_advance->biller_id,
						'created_by' => $cash_advance->created_by
					);
					$this->db->insert_batch("gl_trans",$accTrans);
				}else{
					$this->site->deleteAccTran('CashAdvance',$id);
				}
			}
			return true;
		}
		return false;
	}
	public function getPaybackByID($id = false){
		$q = $this->db->get_where("pay_cash_advance_paybacks",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getPaybacksByCashAdvance($cash_advance_id = false){
		$q = $this->db->get_where("pay_cash_advance_paybacks",array("cash_advance_id"=>$cash_advance_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function addPayback($payment = false){
		if($this->db->insert("pay_cash_advance_paybacks",$payment)){
			$payback_id = $this->db->insert_id();
			if($this->Settings->module_account == 1){
				$cash_advance = $this->getCashAdvanceByID($payment["cash_advance_id"]);
				$employee_detail = $this->getEmployeeByID($cash_advance->requested_by);
				$employee_name = ($employee_detail->firstname.' '.$employee_detail->lastname);
				$accTrans = false;
				$cashAdvanceAcc = $this->site->getAccountSettingByBiller($cash_advance->biller_id);
				$accTrans[] = array(
					'tran_no' => $payback_id,
					'tran_type' => 'PaybackCashAdvance',
					'tran_date' => $payment["date"],
					'reference_no' =>  $cash_advance->reference_no,
					'account_code' => $payment["account_code"],
					'amount' => $payment["amount"],
					'narrative' => 'Payback Cash Advance '.$employee_name,
					'description' => $payment["note"],
					'biller_id' => $cash_advance->biller_id,
					'created_by' => $payment["created_by"],
				);
				$accTrans[] = array(
					'tran_no' => $payback_id,
					'tran_type' => 'PaybackCashAdvance',
					'tran_date' => $payment["date"],
					'reference_no' =>  $cash_advance->reference_no,
					'account_code' => $cashAdvanceAcc->cash_advance_acc,
					'amount' => $payment["amount"] * (-1),
					'narrative' => 'Payback Cash Advance '.$employee_name,
					'description' => $payment["note"],
					'biller_id' => $cash_advance->biller_id,
					'created_by' => $payment["created_by"],
				);
				$this->db->insert_batch("gl_trans",$accTrans);
			}
			$this->synceCashAdvance($payment["cash_advance_id"]);
			return true;
		}
		return false;
	}
	public function updatePayback($id = false, $payment = false){
		if($id && $this->db->update("pay_cash_advance_paybacks",$payment, array("id"=>$id))){
			$payback = $this->getPaybackByID($id);
			if($this->Settings->module_account == 1){
				$this->site->deleteAccTran('PaybackCashAdvance',$id);
				$cash_advance = $this->getCashAdvanceByID($payback->cash_advance_id);
				$employee_detail = $this->getEmployeeByID($cash_advance->requested_by);
				$employee_name = ($employee_detail->firstname.' '.$employee_detail->lastname);
				$accTrans = false;
				$cashAdvanceAcc = $this->site->getAccountSettingByBiller($cash_advance->biller_id);
				$accTrans[] = array(
					'tran_no' => $id,
					'tran_type' => 'PaybackCashAdvance',
					'tran_date' => $payment["date"],
					'reference_no' =>  $cash_advance->reference_no,
					'account' => $payment["account_code"],
					'amount' => $payment["amount"],
					'narrative' => 'Payback Cash Advance '.$employee_name,
					'description' => $payment["note"],
					'biller_id' => $cash_advance->biller_id,
					'created_by' => $payment["created_by"],
				);
				$accTrans[] = array(
					'tran_no' => $id,
					'tran_type' => 'PaybackCashAdvance',
					'tran_date' => $payment["date"],
					'reference_no' =>  $cash_advance->reference_no,
					'account' => $cashAdvanceAcc->cash_advance_acc,
					'amount' => $payment["amount"] * (-1),
					'narrative' => 'Payback Cash Advance '.$employee_name,
					'description' => $payment["note"],
					'biller_id' => $cash_advance->biller_id,
					'created_by' => $payment["created_by"],
				);
				$this->db->insert_batch("gl_trans",$accTrans);
			}
			$this->synceCashAdvance($payback->cash_advance_id);
			return true;
		}
		return false;
	}
	
	public function deletePayback($id = false){
		$payback = $this->getPaybackByID($id);
		if($id && $this->db->delete("pay_cash_advance_paybacks",array("id"=>$id))){
			$this->synceCashAdvance($payback->cash_advance_id);
			$this->site->deleteAccTran('PaybackCashAdvance',$id);
			return true;
		}
		return false;
	}
	public function synceCashAdvance($cash_advance_id = false){
		if($cash_advance_id){
			$cash_advance = $this->getCashAdvanceByID($cash_advance_id);
			$this->db->select("sum(amount) as amount");
			$this->db->where("cash_advance_id",$cash_advance_id);
			$q = $this->db->get("pay_cash_advance_paybacks");
			if($q->num_rows() > 0){
				$payment = $q->row();
				$total_paid = $this->bpas->formatDecimal($payment->amount);
				$total_amount = $this->bpas->formatDecimal($cash_advance->amount);
				if($total_paid == $total_amount){
					$status = "paid";
				}else if($total_paid == 0){
					$status = "pending";
				}else{
					$status = "partial";
				}
				$data = array(
							"paid" => $total_paid,
							"payment_status" => $status
						);
			}else{
				$data = array(
							"paid" => 0,
							"payment_status" => "pending"
						);
			}
			$this->db->update("pay_cash_advances",$data, array("id"=>$cash_advance_id));
			return true;
		}
		return false;
	}
	public function getCashAdvanceByEmployee($employee_id = false, $status = false){
		if($employee_id){
			$this->db->where("requested_by",$employee_id);
		}
		if($status){
			$this->db->where("status",$status);
		}
		$this->db->where("payment_status !=","paid");
		$q = $this->db->get("pay_cash_advances");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function synceAttendance($month = false, $year = false){
		$this->db->query("UPDATE ".$this->db->dbprefix('att_approve_attedances')."
							LEFT JOIN ".$this->db->dbprefix('pay_salaries')." ON ".$this->db->dbprefix('pay_salaries').".`month` = ".$this->db->dbprefix('att_approve_attedances').".`month` 
							AND ".$this->db->dbprefix('pay_salaries').".`year` = ".$this->db->dbprefix('att_approve_attedances').".`year`
							LEFT JOIN ".$this->db->dbprefix('pay_salary_items')." ON ".$this->db->dbprefix('pay_salary_items').".salary_id = ".$this->db->dbprefix('pay_salaries').".id 
							AND ".$this->db->dbprefix('pay_salary_items').".employee_id = ".$this->db->dbprefix('att_approve_attedances').".employee_id 
							SET ".$this->db->dbprefix('att_approve_attedances').".`status` =
							IF
								( IFNULL( ".$this->db->dbprefix('pay_salary_items').".employee_id, 0 ) > 0, 1, 0 ) 
							WHERE
								".$this->db->dbprefix('att_approve_attedances').".`month` = '".$month."' 
								AND ".$this->db->dbprefix('att_approve_attedances').".`year` = '".$year."' 
						");
		return true;				
	}

	public function getSalaried13Employee($year = false, $edit_id = false){
		if($edit_id){
			$this->db->where("pay_salaries_13.id !=", $edit_id);
		}
		$this->db->select("pay_salary_items_13.*");
		$this->db->where("pay_salaries_13.year",$year);
		$this->db->join("pay_salaries_13","pay_salaries_13.id = pay_salary_items_13.salary_id","inner");
		$q = $this->db->get("pay_salary_items_13");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	
	public function getSalary13Employee($biller_id = false,$position_id = false, $department_id = false, $group_id = false, $year = false, $edit_id = false){
		$salary_employees = $this->getSalaried13Employee($year, $edit_id);
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
		if($year){
			$this->db->where("YEAR(".$this->db->dbprefix('hr_employees_working_info').".employee_date) <=",$year);
		}
		$this->db->select("	
							hr_employees.id as employee_id,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_employees_working_info.employee_date,
							hr_employees_working_info.net_salary,
							(".$this->db->dbprefix('hr_employees_working_info').".annual_leave - IFNULL( annual_leave.total_leave, 0 )) AS annual_leave
						");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","inner");
		$this->db->join('(SELECT '.$this->db->dbprefix('att_take_leave_details').'.employee_id,
							SUM(
							IF
								(
									timeshift = "full",
									( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ),
								(( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ) / 2 ))) AS total_leave 
						FROM
							'.$this->db->dbprefix('att_take_leave_details').'
							INNER JOIN '.$this->db->dbprefix('att_take_leaves').' ON '.$this->db->dbprefix('att_take_leaves').'.id = '.$this->db->dbprefix('att_take_leave_details').'.take_leave_id
							INNER JOIN '.$this->db->dbprefix('hr_leave_types').' ON '.$this->db->dbprefix('hr_leave_types').'.id = '.$this->db->dbprefix('att_take_leave_details').'.leave_type
							INNER JOIN '.$this->db->dbprefix('hr_leave_categories').' ON '.$this->db->dbprefix('hr_leave_categories').'.id = '.$this->db->dbprefix('hr_leave_types').'.category_id 
						WHERE
							'.$this->db->dbprefix('att_take_leaves').'.status = 1 
							AND '.$this->db->dbprefix('hr_leave_categories').'.code = "annual_leave"
							AND YEAR('.$this->db->dbprefix('att_take_leave_details').'.start_date) = "'.$year.'"
						GROUP BY
							'.$this->db->dbprefix('att_take_leave_details').'.employee_id) as annual_leave','hr_employees.id = annual_leave.employee_id','LEFT');
		$this->db->where("hr_employees_working_info.status !=","inactive");
		$q = $this->db->get("hr_employees");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function getSalary13ByID($id = false){
		$q = $this->db->get_where("pay_salaries_13",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getSalary13Items($salary_id = false){
		$this->db->select("pay_salary_items_13.*,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname
						");
		$this->db->join("hr_employees","hr_employees.id = pay_salary_items_13.employee_id","LEFT");
		$this->db->where("salary_id",$salary_id);
		$this->db->order_by("pay_salary_items_13.id","desc");
		$q = $this->db->get("pay_salary_items_13");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getSalary13ItemsByID($id = false){
		$this->db->select("pay_salary_items_13.*,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname
						");
		$this->db->join("hr_employees","hr_employees.id = pay_salary_items_13.employee_id","LEFT");
		$this->db->where("pay_salary_items_13.id",$id);
		$q = $this->db->get("pay_salary_items_13");
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function addSalary13($data = false, $items = false){
		if($this->db->insert("pay_salaries_13",$data)){
			$salary_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["salary_id"] = $salary_id;
					$this->db->insert("pay_salary_items_13",$item);
				}
			}
			return true;
		}
		return false;
	}
	public function updateSalary13($id = false, $data = false, $items = false){
		if($data && $this->db->update("pay_salaries_13",$data,array("id"=>$id))){
			$this->db->delete("pay_salary_items_13",array("salary_id"=>$id));
			$this->db->insert_batch("pay_salary_items_13",$items);
			return true;
		}
		return false;
	}
	public function deleteSalary13($id = false){
		if($id && $this->db->delete("pay_salaries_13",array("id"=>$id))){
			$this->db->delete("pay_salary_items_13",array("salary_id"=>$id));
			return true;
		}
		return false;
	}
	
	public function updateSalary13Status($id = false, $status = false){
		if($id && $this->db->update("pay_salaries_13",array("status"=>$status),array("id"=>$id))){
			if($this->Settings->module_account == 1 && $status=="approved"){
				$accTrans = false;
				$salary = $this->getSalary13ByID($id);
				$salaryAcc = $this->site->getAccountSettingByBiller($salary->biller_id);
				if($salary->gross_salary > 0){
					$accTrans[] = array(
						'tran_no' => $id,
						'tran_type' => '13th Salary',
						'tran_date' => $salary->date,
						'reference_no' =>  $salary->year,
						'account_code' => $salaryAcc->salary_13_acc,
						'amount' => $salary->gross_salary,
						'narrative' => 'Staff 13th Salary for '.$salary->year,
						'description' => $salary->note,
						'biller_id' => $salary->biller_id,
						'created_by' => $salary->created_by
					);
				}
				if($salary->annual_amount > 0){
					$accTrans[] = array(
						'tran_no' => $id,
						'tran_type' => '13th Salary',
						'tran_date' => $salary->date,
						'reference_no' =>  $salary->year,
						'account_code' => $salaryAcc->compensate_acc,
						'amount' => $salary->annual_amount,
						'narrative' => 'AL Competsate for '.$salary->year,
						'description' => $salary->note,
						'biller_id' => $salary->biller_id,
						'created_by' => $salary->created_by
					);
				}
				$accTrans[] = array(
						'tran_no' => $id,
						'tran_type' => '13th Salary',
						'tran_date' => $salary->date,
						'reference_no' =>  $salary->year,
						'account' => $salaryAcc->default_salary_expense,
						'account_code' => $salary->net_amount * (-1),
						'narrative' => 'Staff 13th Salary for '.$salary->year,
						'description' => $salary->note,
						'biller_id' => $salary->biller_id,
						'created_by' => $salary->created_by
					);
				$this->db->insert_batch("gl_trans",$accTrans);				
			}else if($this->Settings->module_account == 1 && $status=="pending"){
				$this->site->deleteAccTran('13th Salary',$id);
			}
			return true;
		}
		return false;
	}
	
	public function getPayments13BySalaryID($salary_id = false){
		$q = $this->db->get_where("pay_payments_13",array("salary_id"=>$salary_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getPayment13ByID($id = false){
		$q = $this->db->get_where("pay_payments_13",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getPayments13ItemByPaymentID($payment_id = false){
		
		$this->db->select("pay_payment_items_13.*,
							pay_salary_items_13.subtotal,
							pay_salary_items_13.paid,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname
						");
		$this->db->join("pay_payments_13","pay_payments_13.id = pay_payment_items_13.payment_id","INNER");
		$this->db->join("pay_salary_items_13","pay_salary_items_13.salary_id = pay_payments_13.salary_id AND pay_payment_items_13.employee_id = pay_salary_items_13.employee_id","LEFT");
		$this->db->join("hr_employees","hr_employees.id = pay_payment_items_13.employee_id","LEFT");
		$this->db->where("pay_payment_items_13.payment_id",$payment_id);
		$q = $this->db->get("pay_payment_items_13");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function addPayment13($data = false, $items = false){
		if($this->db->insert("pay_payments_13",$data)){
			$payment_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["payment_id"] = $payment_id;
					$this->db->insert("pay_payment_items_13",$item);
				}
			}
			if($this->Settings->module_account == 1){
				$accTrans = false;
				$paymentAcc = $this->site->getAccountSettingByBiller($data['biller_id']);
				$accTrans[] = array(
					'tran_no' => $payment_id,
					'tran_type' => '13th Payment',
					'tran_date' => $data['date'],
					'reference_no' => $data['year'],
					'account_code' => $data['account_code'],
					'amount' => $data['amount'] * (-1),
					'narrative' => 'Staff 13th Payment for '.$data['year'],
					'description' => $data['note'],
					'biller_id' => $data['biller_id'],
					'created_by' => $data['created_by']
				);
				$accTrans[] = array(
					'tran_no' => $payment_id,
					'tran_type' => '13th Payment',
					'tran_date' => $data['date'],
					'reference_no' => $data['year'],
					'account_code' => $paymentAcc->default_salary_expense,
					'amount' => $data['amount'],
					'narrative' => 'Staff 13th Payment for '.$data['year'],
					'description' => $data['note'],
					'biller_id' => $data['biller_id'],
					'created_by' => $data['created_by']
				);
				$this->db->insert_batch("gl_trans",$accTrans);
			}
			$this->sysnceSalary13($data['salary_id']);
			return true;
		}
		return false;
	}
	
	public function updatePayment13($id = false, $data = false, $items = false){
		if($this->db->update("pay_payments_13",$data,array("id"=>$id))){
			$this->db->delete("pay_payment_items_13",array("payment_id"=>$id));
			$this->site->deleteAccTran('13th Payment',$id);
			if($items){
				$this->db->insert_batch("pay_payment_items_13",$items);
			}
			if($this->Settings->module_account == 1){
				$accTrans = false;
				$paymentAcc = $this->site->getAccountSettingByBiller($data['biller_id']);
				$accTrans[] = array(
					'tran_no' => $id,
					'tran_type' => '13th Payment',
					'tran_date' => $data['date'],
					'reference_no' => $data['year'],
					'account_code' => $data['account_code'],
					'amount' => $data['amount'] * (-1),
					'narrative' => 'Staff 13th Payment for '.$data['year'],
					'description' => $data['note'],
					'biller_id' => $data['biller_id'],
					'created_by' => $data['updated_by']
				);
				$accTrans[] = array(
					'tran_no' => $id,
					'tran_type' => '13th Payment',
					'tran_date' => $data['date'],
					'reference_no' => $data['year'],
					'account_code' => $paymentAcc->default_salary_expense,
					'amount' => $data['amount'],
					'narrative' => 'Staff 13th Payment for '.$data['year'],
					'description' => $data['note'],
					'biller_id' => $data['biller_id'],
					'created_by' => $data['updated_by']
				);
				$this->db->insert_batch("gl_trans",$accTrans);
			}
			$this->sysnceSalary13($data['salary_id']);
			return true;
		}
		return false;
	}
	public function deletePayment13($id = false){
		$payment = $this->getPayment13ByID($id);
		if($payment && $this->db->delete("pay_payments_13",array("id"=>$id))){
			$this->db->delete("pay_payment_items_13",array("payment_id"=>$id));
			$this->site->deleteAccTran('13th Payment',$id);
			$this->sysnceSalary13($payment->salary_id);
			return true;
		}
		return false;
	}
	
	public function sysnceSalary13($salary_id = false){
		if($salary_id){
			$this->db->multi_query("UPDATE ".$this->db->dbprefix('pay_salary_items_13')."
							LEFT JOIN (
								SELECT
									".$this->db->dbprefix('pay_payments_13').".salary_id,
									".$this->db->dbprefix('pay_payment_items_13').".employee_id,
									sum( ".$this->db->dbprefix('pay_payment_items_13').".amount ) AS paid 
								FROM
									".$this->db->dbprefix('pay_payments_13')."
									INNER JOIN ".$this->db->dbprefix('pay_payment_items_13')." ON ".$this->db->dbprefix('pay_payment_items_13').".payment_id = ".$this->db->dbprefix('pay_payments_13').".id 
								GROUP BY
									".$this->db->dbprefix('pay_payments_13').".salary_id,
									".$this->db->dbprefix('pay_payment_items_13').".employee_id 
								) AS pay_payments_13 ON pay_payments_13.salary_id = ".$this->db->dbprefix('pay_salary_items_13').".salary_id 
								AND pay_payments_13.employee_id = ".$this->db->dbprefix('pay_salary_items_13').".employee_id 
								SET ".$this->db->dbprefix('pay_salary_items_13').".paid = IFNULL(pay_payments_13.paid, 0 ) 
							WHERE
								".$this->db->dbprefix('pay_salary_items_13').".salary_id = ".$salary_id.";
							UPDATE ".$this->db->dbprefix('pay_salary_items_13')."
							SET payment_status = IF( paid = 0, 'pending', IF ( paid = subtotal, 'paid', 'partial' ) ) 
							WHERE
								salary_id =  ".$salary_id.";
							UPDATE ".$this->db->dbprefix('pay_salaries_13')."
							INNER JOIN (SELECT salary_id,sum(paid) AS paid FROM ".$this->db->dbprefix('pay_salary_items_13')." GROUP BY ".$this->db->dbprefix('pay_salary_items_13').".salary_id ) AS pay_salary_items_13 ON pay_salary_items_13.salary_id = ".$this->db->dbprefix('pay_salaries_13').".id 
							SET ".$this->db->dbprefix('pay_salaries_13').".paid = pay_salary_items_13.paid 
							WHERE
								".$this->db->dbprefix('pay_salaries_13').".id = ".$salary_id.";
							UPDATE ".$this->db->dbprefix('pay_salaries_13')."
							SET payment_status = IF( paid = 0, 'pending', IF ( paid = net_amount, 'paid', 'partial' ) ) 
							WHERE
								id =  ".$salary_id.";
							");
		}
		
	}
	public function getSalaryTeacher($biller_id = false,$position_id = false, $department_id = false, $group_id = false, $month = false, $year = false, $edit_id = false){
		
		$salary_employees = $this->getSalariedEmployee($month, $year, false , $edit_id);
		if($salary_employees){
			foreach($salary_employees as $salary_employee){
				$salaried_employee[] = $salary_employee->employee_id;
			}
			$this->db->where_not_in("PAA.teacher_id", $salaried_employee);
		}
		
		$this->db->where("MONTH(PAA.att_day)", $month);
		$this->db->where("YEAR(PAA.att_day)", $year);

		if($biller_id){
			$this->db->where("sh_teacher_working_info.biller_id",$biller_id);
		}
		if($position_id){
			$this->db->where("sh_teacher_working_info.position_id",$position_id);
		}
		if($department_id){
			$this->db->where("sh_teacher_working_info.department_id",$department_id);
		}
		if($group_id){
			$this->db->where("sh_teacher_working_info.group_id",$group_id);
		}
		$att = '( SELECT 
					ati.teacher_id, ta.year, ta.month, ta.day as att_day,
					SUM(ati.present) working_hour 
				FROM ' . $this->db->dbprefix('sh_teacher_attendances') . ' ta 
				JOIN ' . $this->db->dbprefix('sh_teacher_attendance_items') . ' ati 
				ON ta.id = ati.attendance_id 
				GROUP BY ati.teacher_id ) PAA';

		$this->db->select("	
							sh_teachers.code as empcode,
							sh_teachers.firstname,
							sh_teachers.lastname,
							sh_teacher_working_info.net_salary as basic_salary,
							sh_teacher_working_info.absent_rate,
							sh_teacher_working_info.permission_rate,
							sh_teacher_working_info.late_early_rate,
							sh_teacher_working_info.normal_ot_rate,
							sh_teacher_working_info.weekend_ot_rate,
							sh_teacher_working_info.holiday_ot_rate,
							sh_teacher_working_info.salary_tax,
							sh_teacher_working_info.self_tax,
							sh_teacher_working_info.employee_id,
							PAA.working_hour

						");
		$this->db->join("sh_teacher_working_info","sh_teacher_working_info.employee_id = sh_teachers.id","inner");
		$this->db->join($att, 'sh_teachers.id = PAA.teacher_id', 'inner');

		$q = $this->db->get("sh_teachers");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function getSalaryTeacherItems($salary_id = false){
		$this->db->select("pay_salary_items.*,
							sh_teachers.code,
							sh_teachers.firstname,
							sh_teachers.lastname
						");
		$this->db->join("sh_teachers","sh_teachers.id = pay_salary_items.employee_id","LEFT");
		$this->db->where("salary_id",$salary_id);
		$q = $this->db->get_where("pay_salary_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function addNssfPayment($data = false, $items = false){
		if($this->db->insert("pay_nssf",$data)){
			$payment_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["nssf_id"] = $payment_id;
					$this->db->insert("pay_nssf_items",$item);
				}
			}
			// if($this->Settings->module_account == 1){
			// 	$accTrans = false;
			// 	$paymentAcc = $this->site->getAccountSettingByBiller($data['biller_id']);
			// 	$total_paid = $data['total_tax_paid'] + $data['total_salary_paid'];
			// 	$accTrans[] = array(
			// 		'tran_no' => $payment_id,
			// 		'tran_type' => 'Salary Payment',
			// 		'tran_date' => $data['date'],
			// 		'reference_no' =>  $data['month']."/".$data['year'],
			// 		'account_code' => $data['account_code'],
			// 		'amount' => $total_paid * (-1),
			// 		'narrative' => 'Staff Payment for '.$data['month']."/".$data['year'],
			// 		'description' => $data['note'],
			// 		'biller_id' => $data['biller_id'],
			// 		'created_by' => $data['created_by']
			// 	);
			// 	$accTrans[] = array(
			// 		'tran_no' => $payment_id,
			// 		'tran_type' => 'Salary Payment',
			// 		'tran_date' => $data['date'],
			// 		'reference_no' =>  $data['month']."/".$data['year'],
			// 		'account_code' => $paymentAcc->default_salary_expense,
			// 		'amount' => $total_paid,
			// 		'narrative' => 'Staff Payment for '.$data['month']."/".$data['year'],
			// 		'description' => $data['note'],
			// 		'biller_id' => $data['biller_id'],
			// 		'created_by' => $data['created_by']
			// 	);
			// 	$this->db->insert_batch("gl_trans",$accTrans);
			// }
			// $this->synceSalaryPayment($data['month'],$data['year']);
			return true;
		}
		return false;
	}
    //------------presalary--------------
    public function getPreSalaryEmployee($biller_id = false,$position_id = false, $department_id = false, $group_id = false, $month = false, $year = false, $edit_id = false){
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
		if($month){
			$this->db->where("att_approve_attedances.month",$month);
		}
		if($year){
			$this->db->where("att_approve_attedances.year",$year);
		}
		
		if($edit_id){
			$this->db->where("(IFNULL(".$this->db->dbprefix("att_approve_attedances").".status,0) = 0 OR ".$this->db->dbprefix("att_approve_attedances").".id IN (SELECT att_id FROM ".$this->db->dbprefix('pay_pre_salary_items')." WHERE pre_salary_id = ".$edit_id."))");
		}else{
			$this->db->where("IFNULL(".$this->db->dbprefix("att_approve_attedances").".status,0)",0);
		}


		$this->db->select("	
							att_approve_attedances.id,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_departments.name as department,
							hr_groups.name as group,
							hr_positions.name as position,
							hr_employees_working_info.employee_date,
							hr_employees_working_info.net_salary as basic_salary,
							att_approve_attedances.employee_id,
							companies.working_day,
							att_approve_attedances.present,
							att_approve_attedances.holiday,
							att_approve_attedances.weekend,
							att_approve_attedances.annual_leave,
							att_approve_attedances.special_leave,
							att_approve_attedances.sick_leave,
							att_approve_attedances.start_date,
							att_approve_attedances.end_date

						");
		$this->db->join("hr_employees","hr_employees.id = att_approve_attedances.employee_id","inner");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = att_approve_attedances.employee_id","inner");
		$this->db->join("companies","companies.id = hr_employees_working_info.biller_id","inner");
		$this->db->join("hr_departments","hr_employees_working_info.department_id = hr_departments.id","left");
		$this->db->join("hr_groups","hr_employees_working_info.group_id = hr_groups.id","left");
		$this->db->join("hr_positions","hr_employees_working_info.position_id = hr_positions.id","left");
		$this->db->where("hr_employees_working_info.status","active");
		$this->db->group_by("att_approve_attedances.id");
		$this->db->order_by("hr_employees.empcode");
		$q = $this->db->get("att_approve_attedances");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function getContractSalary($employee_id = false, $start_date = false, $end_date = false){
		$average_salary = 0;
		if($employee_id && $start_date && $end_date){
			$this->db->where("hr_employees_contract.employee_id",$employee_id);
			$this->db->select("
								hr_employees_contract.date,
								hr_employees_contract.start_date,
								hr_employees_contract.end_date,
								hr_employees_contract.basic_salary,
								IFNULL(DATEDIFF(".$this->db->dbprefix('hr_employees_contract').".end_date, '".$start_date."'),DATEDIFF('".$end_date."', ".$this->db->dbprefix('hr_employees_contract').".start_date)) as contract_day,
								DATEDIFF('".$end_date."', '".$start_date."') as attendance_day
							");
			$this->db->where("(
								(
									'".$start_date."' <= ".$this->db->dbprefix('hr_employees_contract').".`end_date` AND '".$end_date."' >= ".$this->db->dbprefix('hr_employees_contract').".`start_date` 
									AND (
										('".$start_date."' <= ".$this->db->dbprefix('hr_employees_contract').".`start_date` AND '".$end_date."' >= ".$this->db->dbprefix('hr_employees_contract').".start_date ) 
										OR ('".$start_date."' >= ".$this->db->dbprefix('hr_employees_contract').".`start_date` AND '".$start_date."' <= ".$this->db->dbprefix('hr_employees_contract').".end_date ) 
									)
								) 
								OR 
								(
									IFNULL(".$this->db->dbprefix('hr_employees_contract').".end_date,'0000-00-00') = '0000-00-00' 
									AND ".$this->db->dbprefix('hr_employees_contract').".start_date >= '".$start_date."'
									AND ".$this->db->dbprefix('hr_employees_contract').".start_date <= '".$end_date."'
								)
							)");
			
			$this->db->order_by("hr_employees_contract.date");
			$q = $this->db->get("hr_employees_contract");
			if($q->num_rows() > 0){
				if($q->num_rows() == 1){
					$average_salary = $q->result()[0]->basic_salary;
				}else{
					$t_contract_day = 0;
					foreach($q->result() as $row){
						$t_contract_day += $row->contract_day;
					}
					foreach($q->result() as $row){
						$percent = $row->contract_day * 100 / $t_contract_day;
						$salary = $row->basic_salary * $percent / 100;
						$average_salary += $salary;
					}
				}
				
			}
		}
		return $average_salary;
	}
	public function getSummaryPreSalaryItems($pre_salary_id = false){
		$this->db->select("
							SUM(".$this->db->dbprefix('pay_pre_salary_items').".gross_salary) as gross_salary,
							pay_pre_salary_items.att_id,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_departments.name as department,
							hr_groups.name as group,
							hr_positions.name as position,
							hr_employees_working_info.employee_date,
							companies.working_day
						");
		$this->db->join("hr_employees","hr_employees.id = pay_pre_salary_items.employee_id","INNER");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_pre_salary_items.employee_id","INNER");
		$this->db->join("companies","companies.id = hr_employees_working_info.biller_id","INNER");
		$this->db->join("hr_departments","hr_employees_working_info.department_id = hr_departments.id","left");
		$this->db->join("hr_groups","hr_employees_working_info.group_id = hr_groups.id","left");
		$this->db->join("hr_positions","hr_employees_working_info.position_id = hr_positions.id","left");
		$this->db->where("pay_pre_salary_items.pre_salary_id",$pre_salary_id);
		$this->db->group_by("pay_pre_salary_items.employee_id");
		$q = $this->db->get("pay_pre_salary_items");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function addPreSalary($data = false, $items = false){
		if($this->db->insert("pay_pre_salaries",$data)){
			$pre_salary_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["pre_salary_id"] = $pre_salary_id;
					$this->db->insert("pay_pre_salary_items",$item);
				}
			}
			$this->syncePreAttendance($pre_salary_id);
			return true;
		}
		return false;
	}
	public function syncePreAttendance($pre_salary_id = false){
		$q = $this->db->query("UPDATE 
				".$this->db->dbprefix('att_approve_attedances')." 
			SET 
				pre_salary_id = ".$pre_salary_id.",
				`status` = 1
			WHERE
				id IN (SELECT att_id FROM ".$this->db->dbprefix('pay_pre_salary_items')." WHERE pre_salary_id = ".$pre_salary_id.")
		");
	}
	public function updatePreSalary($id = false, $data = false,$items = false){
		if($id && $this->db->update("pay_pre_salaries",$data,array("id"=>$id))){
			$this->db->update("att_approve_attedances",array("status"=>0,"pre_salary_id"=>0),array("pre_salary_id"=>$id));
			$this->db->delete("pay_pre_salary_items",array("pre_salary_id"=>$id));
			if($items){
				$this->db->insert_batch("pay_pre_salary_items",$items);
			}
			$this->syncePreAttendance($id);
			return true;
		}
		return false;
	}
	public function deletePreSalary($id = false){
		if($id && $this->db->delete("pay_pre_salaries",array("id"=>$id))){
			$this->db->update("att_approve_attedances",array("status"=>0,"pre_salary_id"=>0),array("pre_salary_id"=>$id));
			$this->db->delete("pay_pre_salary_items",array("pre_salary_id"=>$id));
			return true;
		}
		return false;
	}
	public function getPreSalaryByID($id = false){
		$q = $this->db->get_where("pay_pre_salaries",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getPreSalaryItems($pre_salary_id = false){
		$this->db->select("pay_pre_salary_items.*,
							pay_pre_salary_items.att_id,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_departments.name as department,
							hr_groups.name as group,
							hr_positions.name as position,
							hr_employees_working_info.employee_date,
							companies.working_day
						");
		$this->db->join("hr_employees","hr_employees.id = pay_pre_salary_items.employee_id","INNER");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_pre_salary_items.employee_id","INNER");
		$this->db->join("companies","companies.id = hr_employees_working_info.biller_id","INNER");
		$this->db->join("hr_departments","hr_employees_working_info.department_id = hr_departments.id","left");
		$this->db->join("hr_groups","hr_employees_working_info.group_id = hr_groups.id","left");
		$this->db->join("hr_positions","hr_employees_working_info.position_id = hr_positions.id","left");
		$this->db->where("pay_pre_salary_items.pre_salary_id",$pre_salary_id);
		$q = $this->db->get("pay_pre_salary_items");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function updatePreSalaryStatus($id = false, $status = false){
		if($id && $this->db->update("pay_pre_salaries",array("status"=>$status),array("id"=>$id))){
			/*if($this->Settings->module_account == 1 && $status=="approved"){
				$accTrans = false;
				$salary = $this->getPreSalaryByID($id);
				$salaryAcc = $this->site->getAccountSettingByBiller($salary->biller_id);
				if($salary->total_gross_salary > 0){
					$accTrans[] = array(
						'tran_no' 		=> $id,
						'tran_type' 	=> 'PreSalary',
						'tran_date' 	=> $salary->date,
						'reference_no' 	=>  $salary->month."/".$salary->year,
						'account_code' 	=> $salaryAcc->salary_expense_acc,
						'amount' 		=> $salary->total_gross_salary,
						'narrative' 	=> 'Pre Salary for '.$salary->month."/".$salary->year,
						'description' 	=> $salary->note,
						'biller_id' 	=> $salary->biller_id,
						'created_by' 	=> $salary->created_by
					);
				}
				$accTrans[] = array(
						'tran_no' 		=> $id,
						'tran_type' 	=> 'PreSalary',
						'tran_date' 	=> $salary->date,
						'reference_no' 	=>  $salary->month."/".$salary->year,
						'account_code' 	=> $salaryAcc->salary_payable_acc,
						'amount' 		=> $salary->total_gross_salary * (-1),
						'narrative' 	=> 'Pre Salary for '.$salary->month."/".$salary->year,
						'description' 	=> $salary->note,
						'biller_id' 	=> $salary->biller_id,
						'created_by' 	=> $salary->created_by
					);
				$this->db->insert_batch("gl_trans",$accTrans);				
			}else if($this->Settings->module_account == 1 && $status=="pending"){
				$this->site->deleteAccTran('PreSalary',$id);
			}*/
			return true;
		}
		return false;
	}
	public function getPrePaymentByPreSalaryID($pre_salary_id = false){
		$q = $this->db->get_where("pay_pre_salary_payments",array("pre_salary_id"=>$pre_salary_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function addPreSalaryPayment($data = false, $items = false){
		if($this->db->insert("pay_pre_salary_payments",$data)){
			$payment_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["payment_id"] = $payment_id;
					$this->db->insert("pay_pre_salary_payment_items",$item);
				}
			}
			if($this->Settings->module_account == 1){
				$accTrans = false;
				$paymentAcc = $this->site->getAccountSettingByBiller($data['biller_id']);
				$cash_account = $this->site->getCashAccountByID($data['paid_by']);
				$accTrans[] = array(
					'tran_no' 		=> $payment_id,
					'tran_type' 	=> 'PreSalaryPayment',
					'tran_date' 	=> $data['date'],
					'reference_no' 	=>  $data['month']."/".$data['year'],
					'account_code' 	=> $cash_account->account_code,
					'amount' 		=> $data['amount'] * (-1),
					'narrative' 	=> 'Pre Salary Payment for '.$data['month']."/".$data['year'],
					'description' 	=> $data['note'],
					'biller_id' 	=> $data['biller_id'],
					'created_by' 	=> $data['created_by']
				);
				$accTrans[] = array(
					'tran_no' 		=> $payment_id,
					'tran_type' 	=> 'PreSalaryPayment',
					'tran_date' 	=> $data['date'],
					'reference_no' 	=>  $data['month']."/".$data['year'],
					'account_code' 	=> $paymentAcc->default_salary_payable,
					'amount' 		=> $data['amount'],
					'narrative' 	=> 'Pre Salary Payment for '.$data['month']."/".$data['year'],
					'description' 	=> $data['note'],
					'biller_id' 	=> $data['biller_id'],
					'created_by' 	=> $data['created_by']
				);
				$this->db->insert_batch("gl_trans",$accTrans);
			}
			$this->syncePreSalary($data["pre_salary_id"]);
			return true;
		}
		return false;
	}

	public function syncePreSalary($pre_salary_id = false){
		if($pre_salary_id){

			$this->db->query("UPDATE ".$this->db->dbprefix('pay_pre_salary_items')."
				LEFT JOIN ( SELECT pre_salary_item_id, sum( amount ) AS amount FROM ".$this->db->dbprefix('pay_pre_salary_payment_items')." GROUP BY pre_salary_item_id ) AS pay_pre_salary_payment_items ON pay_pre_salary_payment_items.pre_salary_item_id = ".$this->db->dbprefix('pay_pre_salary_items').".id 
				SET ".$this->db->dbprefix('pay_pre_salary_items').".net_paid = IFNULL(pay_pre_salary_payment_items.amount,0),
				".$this->db->dbprefix('pay_pre_salary_items').".payment_status = IF(IFNULL( pay_pre_salary_payment_items.amount, 0 ) = 0,'pending',IF( ROUND(".$this->db->dbprefix('pay_pre_salary_items').".gross_salary,".$this->Settings->decimals.") = ROUND(pay_pre_salary_payment_items.amount,".$this->Settings->decimals."), 'paid', 'partial' )) 
				WHERE
					".$this->db->dbprefix('pay_pre_salary_items').".pre_salary_id = ".$pre_salary_id." ");

			$this->db->query("UPDATE ".$this->db->dbprefix('pay_pre_salaries')."
				INNER JOIN ( SELECT pre_salary_id, sum( net_paid ) AS total_paid FROM ".$this->db->dbprefix('pay_pre_salary_items')." WHERE pre_salary_id = ".$pre_salary_id." ) AS pay_pre_salary_items ON pay_pre_salary_items.pre_salary_id = ".$this->db->dbprefix('pay_pre_salaries').".id 
				SET 
					".$this->db->dbprefix('pay_pre_salaries').".total_paid = IFNULL( pay_pre_salary_items.total_paid, 0 ),
					".$this->db->dbprefix('pay_pre_salaries').".payment_status = IF(IFNULL( pay_pre_salary_items.total_paid, 0 ) = 0,'pending',IF( ROUND(pay_pre_salary_items.total_paid,".$this->Settings->decimals.") = ROUND(".$this->db->dbprefix('pay_pre_salaries').".total_gross_salary,".$this->Settings->decimals."), 'paid', 'partial' )) 
				WHERE
					".$this->db->dbprefix('pay_pre_salaries').".id = ".$pre_salary_id.";
					
			");	
		}
		
	}
	public function getPreSalaryPaymentByID($id = false){
		$q = $this->db->get_where("pay_pre_salary_payments",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
		
	}
	public function updatePreSalaryPayment($id = false, $data = false, $items = false){
		if($this->db->update("pay_pre_salary_payments",$data,array("id"=>$id))){
			$this->db->delete("pay_pre_salary_payment_items",array("payment_id"=>$id));
			$this->site->deleteAccTran('Pre Salary Payment',$id);
			if($items){
				$this->db->insert_batch("pay_pre_salary_payment_items",$items);
			}
			if($this->Settings->module_account == 1){
				$accTrans = false;
				$paymentAcc = $this->site->getAccountSettingByBiller($data['biller_id']);
				$cash_account = $this->site->getCashAccountByID($data['paid_by']);
				$accTrans[] = array(
					'tran_no' 		=> $id,
					'tran_type' 	=> 'Pre Salary Payment',
					'tran_date' 	=> $data['date'],
					'reference_no' 	=>  $data['month']."/".$data['year'],
					'account_code' 	=> $cash_account->account_code,
					'amount' 		=> $data['amount'] * (-1),
					'narrative' 	=> 'Pre Salary Payment for '.$data['month']."/".$data['year'],
					'description' 	=> $data['note'],
					'biller_id' 	=> $data['biller_id'],
					'created_by' 	=> $data['updated_by']
				);
				$accTrans[] = array(
					'tran_no' 		=> $id,
					'tran_type' 	=> 'Pre Salary Payment',
					'tran_date' 	=> $data['date'],
					'reference_no' 	=>  $data['month']."/".$data['year'],
					'account_code' 	=> $paymentAcc->default_salary_payable,
					'amount' 		=> $data['amount'],
					'narrative' 	=> 'Pre Salary Payment for '.$data['month']."/".$data['year'],
					'description' 	=> $data['note'],
					'biller_id' 	=> $data['biller_id'],
					'created_by' 	=> $data['updated_by']
				);
				$this->db->insert_batch("gl_trans",$accTrans);
			}
			$this->syncePreSalary($data['pre_salary_id']);
			return true;
		}
		return false;
	}
	public function deletePreSalaryPayment($id = false){
		$payment = $this->getPreSalaryPaymentByID($id);
		if($payment && $this->db->delete("pay_pre_salary_payments",array("id"=>$id))){
			$this->db->delete("pay_pre_salary_payment_items",array("payment_id"=>$id));
			$this->site->deleteAccTran('PreSalaryPayment',$id);
			$this->syncePreSalary($payment->pre_salary_id);
			return true;
		}
		return false;
	}
	public function getPreSalaryPaymentItems($payment_id = false){
		
		$this->db->select("pay_pre_salary_payment_items.*,
							pay_pre_salary_items.id,
							pay_pre_salary_items.gross_salary,
							pay_pre_salary_items.net_paid,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname
						");
		$this->db->join("hr_employees","hr_employees.id = pay_pre_salary_payment_items.employee_id","LEFT");
		$this->db->join("pay_pre_salary_items","pay_pre_salary_items.id = pay_pre_salary_payment_items.pre_salary_item_id","LEFT");
		$this->db->where("pay_pre_salary_payment_items.payment_id",$payment_id);
		$this->db->order_by("hr_employees.empcode");
		$q = $this->db->get("pay_pre_salary_payment_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	public function cal_seniority($biller_id,$employee_id,$month,$year,$last_gross_salary)
	{
		if ($month ==6 OR $month == 12){
            $this->db->select("SUM(IFNULL(".$this->db->dbprefix('pay_salary_items').".gross_salary,0)) as gross_salary")
				->from("pay_salary_items")
				->join("pay_salaries","pay_salaries.id = pay_salary_items.salary_id","inner")
				->group_by("pay_salary_items.employee_id");
			$this->db->where(array("biller_id"=>$biller_id,'year'=>$year));
			$this->db->where('pay_salary_items.employee_id',$employee_id);

			if($month <=6){
				$this->db->where("({$this->db->dbprefix('pay_salaries')}.month = 1 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 2 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 3 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 4 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 5 )");
				//$this->db->or_where('pay_salaries.month',6);
			}else{
				$this->db->where("({$this->db->dbprefix('pay_salaries')}.month = 7 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 8 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 9 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 10 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 11 )");
				//$this->db->or_where('pay_salaries.month',12);
			}
			$q = $this->db->get();
			if($q->num_rows() > 0){
				$data = $q->row();
			}
			$avg_per_month  	= $this->bpas->formatDecimal(($data->gross_salary + $last_gross_salary)/6);
			$avg_per_day		= $this->bpas->formatDecimal($avg_per_month/24);
			$seniority_semester = $this->bpas->formatDecimal($avg_per_day * 7.5);

		}else{
			$seniority_semester = 0;
		}
		return $seniority_semester;
	}
		public function getSeveranceEmployee($biller_id = false,$position_id = false, $department_id = false, $group_id = false, $month = false, $year = false, $edit_id = false){
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
		$where_severance = "";
		if($edit_id){
			$where_severance = " AND pay_severances.id != '".$edit_id."'";
		}
		
		$first_month = "";
		$second_month = "";
		$third_month = "";
		if($month && $year){
			$date = $year."-".$month."-01";
			$this->db->where("hr_employees_contract.end_date < ",$date);
			if($month - 3 <= 0){
				$first_month = (12 - (3-$month))."-".($year - 1);
			}else{
				$first_month = ($month - 3)."-".$year;
			}
			if($month - 2 <= 0){
				$second_month = (12 - (2-$month))."-".($year - 1);
			}else{
				$second_month = ($month - 2)."-".$year;
			}
			if($month - 1 <= 0){
				$third_month = (12 - (1-$month))."-".($year - 1);
			}else{
				$third_month = ($month - 1)."-".$year;
			}
		}
		$this->db->where("IFNULL(".$this->db->dbprefix('hr_employees_contract').".severance,0) !=",0);
		$this->db->where("IFNULL(".$this->db->dbprefix('pay_severances').".id,0)",0);
		
		$this->db->select("
							hr_employees_working_info.employee_id,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_employees_contract.start_date,
							hr_employees_contract.end_date,
							hr_employees_contract.severance,
							IFNULL(first_salary.gross_salary,0) as first_salary,
							IFNULL(second_salary.gross_salary,0) as second_salary,
							IFNULL(third_salary.gross_salary,0) as third_salary,
							IFNULL(first_salary.gross_salary,0) + IFNULL(second_salary.gross_salary,0) + IFNULL(third_salary.gross_salary,0) as total_salary
						");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","inner");
		$this->db->join("hr_employees_contract","hr_employees_contract.employee_id = hr_employees.id","inner");
		$this->db->join("pay_severance_items","pay_severance_items.employee_id = hr_employees.id","LEFT");
		$this->db->join("pay_severances","pay_severances.id = pay_severance_items.severance_id AND pay_severances.month = '".$month."' AND pay_severances.year = '".$year."'".$where_severance,"LEFT");
		$this->db->join("(SELECT
							".$this->db->dbprefix("pay_salary_items").".employee_id,
							(
								IFNULL(".$this->db->dbprefix("pay_salary_items").".net_pay,0)+
								IFNULL(".$this->db->dbprefix("pay_salary_items").".cash_advanced,0)-
								IFNULL(".$this->db->dbprefix("pay_salary_items").".normal_ot_amount,0)-
								IFNULL(".$this->db->dbprefix("pay_salary_items").".weekend_ot_amount,0)-
								IFNULL(".$this->db->dbprefix("pay_salary_items").".holiday_ot_amount,0)-
								IFNULL(".$this->db->dbprefix("pay_salary_items").".net_commission,0)
							) as gross_salary
						FROM
							".$this->db->dbprefix("pay_salary_items")."
						INNER JOIN ".$this->db->dbprefix("pay_salaries")." ON ".$this->db->dbprefix("pay_salaries").".id = ".$this->db->dbprefix("pay_salary_items").".salary_id
						WHERE
							(CONCAT(".$this->db->dbprefix('pay_salaries').".month,'-',".$this->db->dbprefix('pay_salaries').".year)) = '".$first_month."'
						GROUP BY
							".$this->db->dbprefix("pay_salary_items").".employee_id
						) as first_salary","hr_employees.id = first_salary.employee_id","left");
		$this->db->join("(SELECT
							".$this->db->dbprefix("pay_salary_items").".employee_id,
							(
								IFNULL(".$this->db->dbprefix("pay_salary_items").".net_pay,0)+
								IFNULL(".$this->db->dbprefix("pay_salary_items").".cash_advanced,0)-
								IFNULL(".$this->db->dbprefix("pay_salary_items").".normal_ot_amount,0)-
								IFNULL(".$this->db->dbprefix("pay_salary_items").".weekend_ot_amount,0)-
								IFNULL(".$this->db->dbprefix("pay_salary_items").".holiday_ot_amount,0)-
								IFNULL(".$this->db->dbprefix("pay_salary_items").".net_commission,0)
							) as gross_salary
						FROM
							".$this->db->dbprefix("pay_salary_items")."
						INNER JOIN ".$this->db->dbprefix("pay_salaries")." ON ".$this->db->dbprefix("pay_salaries").".id = ".$this->db->dbprefix("pay_salary_items").".salary_id
						WHERE
							(CONCAT(".$this->db->dbprefix('pay_salaries').".month,'-',".$this->db->dbprefix('pay_salaries').".year)) = '".$second_month."'
						GROUP BY
							".$this->db->dbprefix("pay_salary_items").".employee_id
						) as second_salary","hr_employees.id = second_salary.employee_id","left");				
		$this->db->join("(SELECT
							".$this->db->dbprefix("pay_salary_items").".employee_id,
							(
								IFNULL(".$this->db->dbprefix("pay_salary_items").".net_pay,0)+
								IFNULL(".$this->db->dbprefix("pay_salary_items").".cash_advanced,0)-
								IFNULL(".$this->db->dbprefix("pay_salary_items").".normal_ot_amount,0)-
								IFNULL(".$this->db->dbprefix("pay_salary_items").".weekend_ot_amount,0)-
								IFNULL(".$this->db->dbprefix("pay_salary_items").".holiday_ot_amount,0)-
								IFNULL(".$this->db->dbprefix("pay_salary_items").".net_commission,0)
							) as gross_salary
						FROM
							".$this->db->dbprefix("pay_salary_items")."
						INNER JOIN ".$this->db->dbprefix("pay_salaries")." ON ".$this->db->dbprefix("pay_salaries").".id = ".$this->db->dbprefix("pay_salary_items").".salary_id
						WHERE
							(CONCAT(".$this->db->dbprefix('pay_salaries').".month,'-',".$this->db->dbprefix('pay_salaries').".year)) = '".$third_month."'
						GROUP BY
							".$this->db->dbprefix("pay_salary_items").".employee_id
						) as third_salary","hr_employees.id = third_salary.employee_id","left");						
		$this->db->join("pay_salary_items","pay_salary_items.employee_id = hr_employees.id","left");
		$this->db->join("pay_salaries","pay_salaries.id = pay_salary_items.salary_id","left");
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
	
	public function addSeverance($data = false, $items = false){
		if($data && $this->db->insert("pay_severances",$data)){
			$severance_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["severance_id"] = $severance_id;
					$this->db->insert("pay_severance_items",$item);
				}
			}
			return true;
		}
		return false;
	}
	
	public function updateSeverance($id = false, $data = false, $items = false){
		if($data && $this->db->update("pay_severances",$data,array("id"=>$id))){
			$this->db->delete("pay_severance_items",array("severance_id"=>$id));
			$this->db->insert_batch("pay_severance_items",$items);
			return true;
		}
		return false;
	}
	
	public function deleteSeverance($id = false){
		if($id && $this->db->delete("pay_severances",array("id"=>$id))){
			$this->db->delete("pay_severance_items",array("severance_id"=>$id));
			return true;
		}
		return false;
	}
	
	public function getSeveranceByID($id = false){
		$q = $this->db->get_where("pay_severances",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getSeveranceItems($severance_id = false){
		$this->db->select("
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							pay_severance_items.*
						");
		$this->db->join("hr_employees","hr_employees.id = pay_severance_items.employee_id","LEFT");
		$this->db->where("pay_severance_items.severance_id",$severance_id);
		$q = $this->db->get_where("pay_severance_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function updateSeveranceStatus($id = false, $status = false){
		if($id && $this->db->update("pay_severances",array("status"=>$status),array("id"=>$id))){
			if($this->Settings->module_account == 1){
				$this->site->deleteAccTran('Severance',$id);
				if($status=="approved"){
					$severance = $this->getSeveranceByID($id);
					$severanceAcc = $this->site->getAccountSettingByBiller($severance->biller_id);
					$cash_account = $this->site->getCashAccountByID($severance->paid_by);
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'Severance',
						'transaction_date' => $severance->date,
						'reference' =>  $severance->month."/".$severance->year,
						'account' => $severanceAcc->severance_expense_acc,
						'amount' => $severance->total,
						'narrative' => 'Severance Payment for '.$severance->month."/".$severance->year,
						'description' => $severance->note,
						'biller_id' => $severance->biller_id,
						'user_id' => $severance->created_by
					);
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'Severance',
						'transaction_date' => $severance->date,
						'reference' =>  $severance->month."/".$severance->year,
						'account' => $cash_account->account_code,
						'amount' => $severance->total * (-1),
						'narrative' => 'Severance Payment for '.$severance->month."/".$severance->year,
						'description' => $severance->note,
						'biller_id' => $severance->biller_id,
						'user_id' => $severance->created_by
					);
					$this->db->insert_batch("acc_tran",$accTrans);
				}
			}
			return true;
		}
		return false;
	}
	
	
	public function getALCompensateEmployee($biller_id = false,$position_id = false, $department_id = false, $group_id = false, $year = false, $edit_id = false){
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
		
		$where_al_compensate = "";
		if($edit_id){
			$where_al_compensate = " AND pay_al_compensates.id != '".$edit_id."'";
		}
		$this->db->where("IFNULL(".$this->db->dbprefix('pay_al_compensates').".id,0)",0);
		$this->db->select("
							hr_employees_working_info.employee_id,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_employees_working_info.position_id,
							hr_employees_working_info.department_id,
							hr_employees_working_info.group_id,
							hr_employees_working_info.employee_date,
							hr_positions.name as position,
							hr_departments.name as department,
							hr_groups.name as group,
							hr_employees_working_info.net_salary as basic_salary,
							hr_employees_working_info.annual_leave,
							IFNULL(annual_leave.al_day,0) as al_day,
							companies.working_day
						");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","inner");
		$this->db->join("companies","hr_employees_working_info.biller_id = companies.id","inner");
		$this->db->join("hr_positions","hr_employees_working_info.position_id = hr_positions.id","left");
		$this->db->join("hr_departments","hr_employees_working_info.department_id = hr_departments.id","left");
		$this->db->join("hr_groups","hr_employees_working_info.group_id = hr_groups.id","left");
		$this->db->join("pay_al_compensate_items","pay_al_compensate_items.employee_id = hr_employees.id","LEFT");
		$this->db->join("pay_al_compensates","pay_al_compensates.id = pay_al_compensate_items.al_compensate_id AND pay_al_compensates.year = '".$year."'".$where_al_compensate,"LEFT");
		$this->db->join('(SELECT '.$this->db->dbprefix('att_take_leave_details').'.employee_id,
							SUM(
							IF
								(
									timeshift = "full",
									( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ),
								(( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ) / 2 ))) AS al_day 
						FROM
							'.$this->db->dbprefix('att_take_leave_details').'
							INNER JOIN '.$this->db->dbprefix('att_take_leaves').' ON '.$this->db->dbprefix('att_take_leaves').'.id = '.$this->db->dbprefix('att_take_leave_details').'.take_leave_id
							INNER JOIN '.$this->db->dbprefix('hr_leave_types').' ON '.$this->db->dbprefix('hr_leave_types').'.id = '.$this->db->dbprefix('att_take_leave_details').'.leave_type
							INNER JOIN '.$this->db->dbprefix('hr_leave_categories').' ON '.$this->db->dbprefix('hr_leave_categories').'.id = '.$this->db->dbprefix('hr_leave_types').'.category_id 
						WHERE
							'.$this->db->dbprefix('att_take_leaves').'.status = 1 
							AND '.$this->db->dbprefix('hr_leave_categories').'.code = "annual_leave"
							AND YEAR('.$this->db->dbprefix('att_take_leave_details').'.start_date) = "'.$year.'"
						GROUP BY
							'.$this->db->dbprefix('att_take_leave_details').'.employee_id) as annual_leave','hr_employees.id = annual_leave.employee_id','LEFT');
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
	
	public function getALCompensateByID($id = false){
		$q = $this->db->get_where("pay_al_compensates",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getALCompensateItems($al_compensate_id = false){
		$this->db->select("	pay_al_compensate_items.*,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_positions.name as position,
							hr_departments.name as department,
							hr_groups.name as group,
						");
		$this->db->join("hr_employees","hr_employees.id = pay_al_compensate_items.employee_id","LEFT");
		$this->db->join("hr_positions","pay_al_compensate_items.position_id = hr_positions.id","left");
		$this->db->join("hr_departments","pay_al_compensate_items.department_id = hr_departments.id","left");
		$this->db->join("hr_groups","pay_al_compensate_items.group_id = hr_groups.id","left");
		$this->db->where("pay_al_compensate_items.al_compensate_id",$al_compensate_id);
		$q = $this->db->get_where("pay_al_compensate_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function addALCompensate($data = false, $items = false){
		if($data && $this->db->insert("pay_al_compensates",$data)){
			$al_compensate_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["al_compensate_id"] = $al_compensate_id;
					$this->db->insert("pay_al_compensate_items",$item);
				}
			}
			return true;
		}
		return false;
	}
	
	public function updateALCompensate($id = false, $data = false, $items = false){
		if($data && $this->db->update("pay_al_compensates",$data,array("id"=>$id))){
			$this->db->delete("pay_al_compensate_items",array("al_compensate_id"=>$id));
			$this->db->insert_batch("pay_al_compensate_items",$items);
			return true;
		}
		return false;
	}
	
	public function deleteALCompensate($id = false){
		if($id && $this->db->delete("pay_al_compensates",array("id"=>$id))){
			$this->db->delete("pay_al_compensate_items",array("al_compensate_id"=>$id));
			return true;
		}
		return false;
	}
	
	public function updateALCompensateStatus($id = false, $status = false){
		if($id && $this->db->update("pay_al_compensates",array("status"=>$status),array("id"=>$id))){
			if($this->Settings->module_account == 1){
				$this->site->deleteAccTran('AL Compensate',$id);
				if($status=="approved"){
					$al_compensate = $this->getALCompensateByID($id);
					$alCompensateAcc = $this->site->getAccountSettingByBiller($al_compensate->biller_id);
					$cash_account = $this->site->getCashAccountByID($al_compensate->paid_by);
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'AL Compensate',
						'transaction_date' => $al_compensate->date,
						'reference' =>  $al_compensate->year,
						'account' => $alCompensateAcc->compensate_acc,
						'amount' => $al_compensate->total,
						'narrative' => 'AL Compensate Payment for '.$al_compensate->year,
						'description' => $al_compensate->note,
						'biller_id' => $al_compensate->biller_id,
						'user_id' => $al_compensate->created_by
					);
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'AL Compensate',
						'transaction_date' => $al_compensate->date,
						'reference' =>  $al_compensate->year,
						'account' => $cash_account->account_code,
						'amount' => $al_compensate->total * (-1),
						'narrative' => 'AL Compensate Payment for '.$al_compensate->year,
						'description' => $al_compensate->note,
						'biller_id' => $al_compensate->biller_id,
						'user_id' => $al_compensate->created_by
					);
					$this->db->insert_batch("acc_tran",$accTrans);
				}
			}
			return true;
		}
		return false;
	}
	public function getPrePayslips($year = false, $month = false, $biller_id = false, $position_id = false, $department_id = false, $group_id = false, $employee_id = false){
		if($year){
			$this->db->where("pay_pre_salaries.year",$year);
		}
		if($month){
			$this->db->where("pay_pre_salaries.month",$month);
		}
		if($biller_id){
			$this->db->where("pay_pre_salaries.biller_id",$biller_id);
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
		if($employee_id){
			$this->db->where("pay_pre_salary_items.employee_id",$employee_id);
		}
		$this->db->select("
							pay_pre_salary_items.*,
							pay_pre_salaries.year,
							pay_pre_salaries.month,
							hr_employees.empcode,
							hr_employees.firstname,
							hr_employees.lastname,
							hr_employees.gender,
							hr_positions.name as position,
							hr_departments.name as department,
							hr_groups.name as group,
							hr_employees_working_info.employee_date,
							companies.working_day,
							companies.logo,
							companies.name,
							companies.city,
							companies.email,
							companies.address,
							companies.phone
						");
		$this->db->join("pay_pre_salaries","pay_pre_salaries.id = pay_pre_salary_items.pre_salary_id","inner");		
		$this->db->join("hr_employees","hr_employees.id = pay_pre_salary_items.employee_id","left");
		$this->db->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_pre_salary_items.employee_id","left");
		$this->db->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left");
		$this->db->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left");
		$this->db->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left");
		$this->db->join("companies","companies.id = pay_pre_salaries.biller_id","left");
		$this->db->group_by("pay_pre_salary_items.id");
		$q = $this->db->get_where("pay_pre_salary_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getContractByEmployeeID($employee_id)
	{
		$this->db->order_by('id','DESC');
		$q = $this->db->get_where("hr_employees_contract",array("employee_id"=>$employee_id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getContractBeforeByEmployeeID($employee_id,$date)
	{
		$this->db->order_by("end_date",'DESC');
		$q = $this->db->get_where("hr_employees_contract",array(
				"employee_id"	=> $employee_id,
				"end_date <="	=> $date
			));
		
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function calculate_seniority($biller_id,$employee_id,$month,$year,$last_gross_salary)
	{
		$workinginfo 		= $this->getWorkingInfoByEmployeeID($employee_id);
		$employee_contract 	= $this->getContractByEmployeeID($employee_id);
		$employee_resign 	= $this->getResignationByEmployeeID($employee_id);
		$resignation_date 	= $employee_resign ? date('Y-m-d', strtotime($employee_resign->resignation_date)):'';
		$last_day 			= date("Y-m-t", strtotime($year.'-'.$month));
		$working_day 		= $workinginfo->monthly_working_day;
		$checking_resign = ($resignation_date=='') ? 1 : (($resignation_date >=$last_day)? 1:0);

		if ($checking_resign && ($employee_contract->contract_type =='udc') && ($month ==6 OR $month == 12)){
            $this->db->select("SUM(IFNULL(".$this->db->dbprefix('pay_salary_items').".nssf_salary_usd,0)) as gross_salary")
				->from("pay_salary_items")
				->join("pay_salaries","pay_salaries.id = pay_salary_items.salary_id","inner")
				->group_by("pay_salary_items.employee_id");
			$this->db->where(array("biller_id"=>$biller_id,'year'=>$year));
			$this->db->where('pay_salary_items.employee_id',$employee_id);

			if($month <=6){
				$this->db->where("({$this->db->dbprefix('pay_salaries')}.month = 1 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 2 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 3 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 4 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 5 )");
				//$this->db->or_where('pay_salaries.month',6);
			}else{
				$this->db->where("({$this->db->dbprefix('pay_salaries')}.month = 7 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 8 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 9 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 10 OR 
				{$this->db->dbprefix('pay_salaries')}.month = 11 )");
				//$this->db->or_where('pay_salaries.month',12);
			}
			$q = $this->db->get();
			if($q->num_rows() > 0){
				$data = $q->row();
			}
			//if($workinginfo->employee_type == 'Permanent'){
				$avg_per_month  	= $this->bpas->formatDecimal(($data->gross_salary + $last_gross_salary)/6);
				$avg_per_day		= $this->bpas->formatDecimal($avg_per_month/$working_day);
				$seniority_semester = $this->bpas->formatDecimal($avg_per_day * 7.5);
			/*}else{
				$avg_per_month  	= $this->bpas->formatDecimal($data->gross_salary + $last_gross_salary);
				$avg_per_day		= $this->bpas->formatDecimal($avg_per_month * 5);
				$seniority_semester = $this->bpas->formatDecimal($avg_per_day / 100);
			}*/
		}else{
			$seniority_semester = 0;
		}
		return $seniority_semester;
	}
	public function calculate_severance($biller_id,$employee_id,$month,$year,$last_gross_salary)
	{
		//$employee_id = 86;
		
		$con_duration 		= date("Y-m-t", strtotime($year.'-'.$month));
		$employee_contract 	= $this->getContractBeforeByEmployeeID($employee_id,$con_duration);
		$severance			= $employee_contract->severance;//5%
		$contract_start		= date('Y-m',strtotime($employee_contract->start_date));

		$count_day 			= cal_days_in_month(CAL_GREGORIAN,2,1965);

		$contract_end		= date('Y-m',strtotime($employee_contract->end_date));

		$last_con_day 		= date("Y-m-d",strtotime($employee_contract->end_date));

		$count_last_day 	= cal_days_in_month(CAL_GREGORIAN,date("m",strtotime($employee_contract->end_date)),date("Y",strtotime($employee_contract->end_date)));

		$workingday = date("d",strtotime($employee_contract->end_date));
		if($workingday < $count_last_day){
			$salary_per_day 	= $this->bpas->formatDecimal($last_gross_salary/$count_last_day);
			$last_gross_salary	= $this->bpas->formatDecimal($salary_per_day * $workingday);
		}
		//echo $last_gross_salary;
		
		$employee_resign 	= $this->getResignationByEmployeeID($employee_id);
		$resignation_date 	= $employee_resign ? date('Y-m-d', strtotime($employee_resign->resignation_date)):'';
		$checking_resign 	= ($resignation_date=='') ? 1 : (($resignation_date >=$last_con_day) ? 1:0);

		if (($employee_contract->contract_type =='fdc') && ($contract_end==$year.'-'.$month) && $checking_resign){

			$contract_end = date('Y-m', strtotime('-1 months', strtotime($contract_end))); 
			$this->db->select("
	        	pay_salaries.month,
	        	pay_salaries.year,
	        	SUM(IFNULL({$this->db->dbprefix('pay_salary_items')}.nssf_salary_usd,0)) as gross_salary,
	        	CONCAT({$this->db->dbprefix('pay_salaries')}.year, '-',LPAD({$this->db->dbprefix('pay_salaries')}.month,2, 0)) as salary_month
	        ")
			->from("pay_salary_items")
			->join("pay_salaries","pay_salaries.id = pay_salary_items.salary_id","left");
			$this->db->where(array(
					"pay_salary_items.employee_id" =>$employee_id,
					"biller_id"	=>$biller_id,
					'year'		=>$year));
			$this->db->where('CONCAT('.$this->db->dbprefix('pay_salaries').'.year,"-",LPAD('.$this->db->dbprefix('pay_salaries').'.month,2, 0)) >=',''.$contract_start.'');
			$this->db->where('CONCAT('.$this->db->dbprefix('pay_salaries').'.year, "-",LPAD('.$this->db->dbprefix('pay_salaries').'.month,2, 0)) <=',''.$contract_end.'');
			$this->db->group_by("pay_salary_items.employee_id");
			$q = $this->db->get();
			if($q->num_rows() > 0){
				$data = $q->row();
			}
			$avg_per_month  	= $this->bpas->formatDecimal($data->gross_salary + $last_gross_salary);
			$avg_per_day		= $this->bpas->formatDecimal($avg_per_month * 5);
			$severance_pay 		= $this->bpas->formatDecimal($avg_per_day / 100);
		}else{
			$severance_pay = 0;
		}
		return $severance_pay;
	}
	public function getWorkingInfoByEmployeeID($employee_id = false,$biller_id = false){
		$this->db->select('att_policies.monthly_working_day,hr_employees_types.name as employee_type');
		if($employee_id){
			$this->db->where('hr_employees_working_info.employee_id',$employee_id);
		}
		if($biller_id){
			$this->db->where('hr_employees_working_info.biller_id',$biller_id);
		}
		$this->db->join('att_policies','att_policies.id = hr_employees_working_info.policy_id','inner');
		$this->db->join('hr_employees_types','hr_employees_types.id = hr_employees_working_info.employee_type_id','inner');
		
		$q = $this->db->get('hr_employees_working_info');
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getResignationByEmployeeID($id = false){
		$q = $this->db->get_where("hr_resignation",array("employee_id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function addDailySalary($data = false, $items = false){
	
		if($this->db->insert("pay_salaries",$data)){
			$salary_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["salary_id"] = $salary_id;
					$approved_att_id   = $item['approved_att_id'];
					$this->db->insert("pay_salary_items",$item);
					$this->synceApproveAttendance($data['month'],$data['year'],1,$salary_id,$approved_att_id);
				}
				
			}
			
			return true;
		}
		return false;
	}
	public function updateDailySalary($id = false, $data = false, $items = false){
		if($data && $this->db->update("pay_salaries",$data,array("id"=>$id))){
			$this->db->delete("pay_salary_items",array("salary_id"=>$id));
			//$this->db->insert_batch("pay_salary_items",$items);
			if($items){
				foreach($items as $item){
					$item["salary_id"] = $id;
					$approved_att_id   = $item['approved_att_id'];
					$this->db->insert("pay_salary_items",$item);
					$this->synceApproveAttendance($data['month'],$data['year'],1,$id,$approved_att_id);
				}
				
			}
			return true;
		}
		return false;
	}
	public function deleteDailySalary($id = false){
		$salary = $this->getSalaryByID($id);

		if($id && $this->db->delete("pay_salaries",array("id"=>$id))){
			$this->db->delete("pay_salary_items",array("salary_id"=>$id));

			$this->synceDeleteApproveAttendance($salary->month,$salary->year,0,$id);
			return true;
		}
		return false;
	}
	public function synceApproveAttendance($month = false, $year = false,$status = false,$salary_id = false,$approved_att_id=false){
		$this->db->query("UPDATE ".$this->db->dbprefix('att_approve_attedances')."
							LEFT JOIN ".$this->db->dbprefix('pay_salaries')." ON ".$this->db->dbprefix('pay_salaries').".`month` = ".$this->db->dbprefix('att_approve_attedances').".`month` 
							AND ".$this->db->dbprefix('pay_salaries').".`year` = ".$this->db->dbprefix('att_approve_attedances').".`year`
							LEFT JOIN ".$this->db->dbprefix('pay_salary_items')." ON ".$this->db->dbprefix('pay_salary_items').".salary_id = ".$this->db->dbprefix('pay_salaries').".id 
							AND ".$this->db->dbprefix('pay_salary_items').".employee_id = ".$this->db->dbprefix('att_approve_attedances').".employee_id 
							SET ".$this->db->dbprefix('att_approve_attedances').".salary_id = ".$salary_id."
								, ".$this->db->dbprefix('att_approve_attedances').".`status` = $status

							WHERE
								".$this->db->dbprefix('att_approve_attedances').".`month` = '".$month."' 
								AND ".$this->db->dbprefix('att_approve_attedances').".`year` = '".$year."' 
								AND ".$this->db->dbprefix('att_approve_attedances').".`id` IN (".$approved_att_id.")
						");
		return true;				
	}
	public function synceDeleteApproveAttendance($month = false, $year = false,$status = false,$salary_id = false){
		$this->db->query("UPDATE ".$this->db->dbprefix('att_approve_attedances')."
							LEFT JOIN ".$this->db->dbprefix('pay_salaries')." ON ".$this->db->dbprefix('pay_salaries').".`month` = ".$this->db->dbprefix('att_approve_attedances').".`month` 
							AND ".$this->db->dbprefix('pay_salaries').".`year` = ".$this->db->dbprefix('att_approve_attedances').".`year`
							LEFT JOIN ".$this->db->dbprefix('pay_salary_items')." ON ".$this->db->dbprefix('pay_salary_items').".salary_id = ".$this->db->dbprefix('pay_salaries').".id 
							AND ".$this->db->dbprefix('pay_salary_items').".employee_id = ".$this->db->dbprefix('att_approve_attedances').".employee_id 
							SET 
								".$this->db->dbprefix('att_approve_attedances').".salary_id = NULL
								, ".$this->db->dbprefix('att_approve_attedances').".`status` = ".$status."
							WHERE
								".$this->db->dbprefix('att_approve_attedances').".`month` = '".$month."' 
								AND ".$this->db->dbprefix('att_approve_attedances').".`year` = '".$year."'
								AND ".$this->db->dbprefix('att_approve_attedances').".`salary_id` = ".$salary_id."
						");
		return true;				
	}
}
?>