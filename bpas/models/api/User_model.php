<?php
	class User_model extends CI_Model{

		// Api start

		// Get user detial by ID
		public function get_user_by_id($id){
			$this->db->select('users.id, users.emp_id ,users.first_name, users.last_name, users.username, users.gender, users.company, users.phone, users.email, users.biller_id, users.active, users.avatar, users.nationality, users.position, users.employeed_date, users.user_type, users.basic_salary, users.is_remote_allow, hr_employees.candidate, hr_employees.photo, companies.name as company_name, hr_departments.name as department_name ,hr_positions.name as position_name, att_policies.policy as policy_name, users.avatar as image')
			->join('companies', 'companies.id=users.biller_id', 'left') 
			->join('hr_employees', 'hr_employees.id=users.emp_id', 'left') 
			->join('hr_employees_working_info', 'hr_employees_working_info.employee_id=users.emp_id', 'left') 
			->join('hr_departments', 'hr_departments.id=hr_employees_working_info.department_id', 'left') 
			->join('hr_positions', 'hr_positions.id=hr_employees_working_info.position_id', 'left')
			->join('att_policies', 'att_policies.id=hr_employees_working_info.policy_id', 'left'); 
			
			$query = $this->db->get_where('users', array('users.id' => $id));
			return $result = $query->row_array();
		}

		// Verify user by mobile
		public function verify_user($phone){
			$query = $this->db->get_where('users', array('phone' => $phone,'active' =>1));
			return $result = $query->row_array();
		}

		public function edit_user($data, $id){
			$this->db->where('id', $id);
			$this->db->update('users', $data);
			return true;
		}	
		
		public function get_company_qrcode($qr_code, $biller_id){
			$query = $this->db->get_where('companies', array('id' => $biller_id,'qr_code' => $qr_code));
			return $result = $query->row_array();
		}
		
		public function get_biller_by_id($id){
			$query = $this->db->get_where('companies', array('id' => $id));
			return $result = $query->row_array();
		}
 
		

	}
?>