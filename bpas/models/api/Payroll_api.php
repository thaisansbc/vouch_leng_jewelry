<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Payroll_api extends CI_Model
{
	public function getSalaryByEmployee($employee_id=false)
	{
		$biller 	= $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group 		= $this->input->get('group') ? $this->input->get('group') : NULL;
		$position 	= $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee 	= $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $y_month 	= $this->input->get('month') ? $this->input->get('month') : NULL;
		if($y_month){
			$y_month = explode("/",$y_month);
			$month = $y_month[0];
			$year = $y_month[1];
		}
		if ($biller) {
            $this->db->where('pay_salaries.biller_id', $biller);
        }
		if ($department) {
            $this->db->where('hr_employees_working_info.department_id', $department);
        }
		if ($position) {
            $this->db->where('hr_employees_working_info.position_id', $position);
        }
		if ($group) {
            $this->db->where('hr_employees_working_info.group_id', $group);
        }
		if ($employee) {
            $this->db->where('pay_salary_items.employee_id', $employee);
        }
		if($y_month){
			$this->db->where('pay_salaries.year', $year);
			$this->db->where('pay_salaries.month', $month);
		}
       	$q = $this->db->select("	
			DATE_FORMAT(".$this->db->dbprefix('pay_salaries').".date, '%Y-%m-%d %T') as date,
			CONCAT(".$this->db->dbprefix('pay_salaries').".month,'/',".$this->db->dbprefix('pay_salaries').".year) as month,
			hr_employees.empcode,
			CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
			hr_positions.name as position,
			hr_departments.name as department,
			hr_groups.name as group,
			IFNULL(".$this->db->dbprefix('pay_salary_items').".gross_salary,0) as gross_salary,
			IFNULL(".$this->db->dbprefix('pay_salary_items').".overtime,0) as overtime,
			IFNULL(".$this->db->dbprefix('pay_salary_items').".addition_amount,0) as addition,
			IFNULL(".$this->db->dbprefix('pay_salary_items').".deduction_amount,0) as deduction,
			IFNULL(".$this->db->dbprefix('pay_salary_items').".cash_advanced,0) as cash_advanced,
			IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_payment,0) as tax_payment,
			IFNULL(".$this->db->dbprefix('pay_salary_items').".net_salary,0) as net_salary,
			IFNULL(".$this->db->dbprefix('pay_salary_items').".net_pay,0) as net_pay,
			IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_paid,0) as tax_paid,
			IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0) as salary_paid,
			(IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0)) as total_paid,
			(IFNULL(".$this->db->dbprefix('pay_salary_items').".net_pay,0) - (IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0) )) as balance,
			pay_salaries.status,
			pay_salary_items.payment_status,
			pay_salary_items.id as id")
						->from("pay_salaries")
						->join("pay_salary_items","pay_salary_items.salary_id = pay_salaries.id","inner")
						->join("hr_employees","hr_employees.id = pay_salary_items.employee_id","left")
						->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_salary_items.employee_id","left")
						->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
						->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
						->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
						->group_by("pay_salary_items.id");
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getSalalies($filters = [])
    {
        if ($filters['employee_id']) {
            $this->db->where('employee_id', $filters['employee_id']);
        } else {
            $this->db->order_by($filters['order_by'][0], $filters['order_by'][1] ? $filters['order_by'][1] : 'desc');
            $this->db->limit($filters['limit'], ($filters['start'] - 1));
        }
		if ($filters['month']) {
			$this->db->where('pay_salaries.month', $filters['month']);
		}
		if ($filters['year']) {
			$this->db->where('pay_salaries.year', $filters['year']);
		}
		$this->db->from('pay_salary_items');
		$this->db->join("hr_employees","hr_employees.id = pay_salary_items.employee_id","inner");
		$this->db->join('pay_salaries', 'pay_salaries.id = pay_salary_items.salary_id', 'inner');
        return $this->db->get()->result();
    }

    public function getSalary($filters)
    {
        if (!empty($sales = $this->getSalalies($filters))) {
            return array_values($sales)[0];
        }
        return false;
    }

    public function getUser($id)
    {
        $uploads_url = base_url('assets/uploads/');
        $this->db->select("CONCAT('{$uploads_url}', avatar) as avatar_url, email, first_name, gender, id, last_name, username");
        return $this->db->get_where('users', ['id' => $id], 1)->row();
    }

    public function countSalaries($filters = [], $ref = null)
    {
        if ($filters['employee_id']) {
            $this->db->where('employee_id', $filters['employee_id']);
        }   
        $this->db->from('pay_salary_items');
        return $this->db->count_all_results();
    }
}