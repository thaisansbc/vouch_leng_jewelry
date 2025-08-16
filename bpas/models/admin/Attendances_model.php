<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Attendances_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }	
	function current_clock_in_record($user_id) {
        $attendnace_table = $this->db->dbprefix('attendance');
        $sql = "SELECT $attendnace_table.*
        FROM $attendnace_table
        WHERE $attendnace_table.deleted=0 AND $attendnace_table.user_id=$user_id AND $attendnace_table.status='incomplete'";
        $result = $this->db->query($sql);
        if ($result->num_rows()) {
            return $result->row();
        } else {
            return false;
        }
    }
	function log_time($user_id, $note = "") {

        $current_clock_record = $this->current_clock_in_record($user_id);
		
        $now = date('Y-m-d H:i:s');

        if ($current_clock_record && $current_clock_record->id) {
            $data = array(
                "out_time" => $now,
                "status" => "pending",
                "note" => $note
            );
			return $this->db->insert('attendance',$data);
            // return $this->save($data, $current_clock_record->id);
        } else {
            $data = array(
                "in_time" => $now,
                "status" => "incomplete",
                "user_id" => $user_id
            );
			return $this->db->insert('attendance',$data);

            // return $this->save($data);
        }
    }
	public function get_log_time($id= NULL)
	{
		$q = $this->db->get_where('attendance', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getAttendanceByID($id= NULL)
	{
		$q = $this->db->get_where('att_attedances', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getApproveAttendanceByID($id= NULL)
	{
		$q = $this->db->get_where('att_approve_attedances', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function approveAttendance($data = array())
	{
		if($data){
			foreach($data as $row){
				
				// Update Attendance status = 1
				$this->db->where($this->db->dbprefix('att_attedances').'.date BETWEEN "' . $this->bpas->fld($row['start_date']) . '" and "' . $this->bpas->fld($row['end_date']) . '"');
				$this->db->where("employee_id", $row['employee_id']);
				$this->db->update("att_attedances", array("year"=>$row['year'], "month"=> $row['month'], "status"=>1));
				
				// Delete Approve Attendance
				
				$this->db->where("employee_id", $row['employee_id']);
				$this->db->where("year", $row['year']);
				$this->db->where("month", $row['month']);
				$this->db->delete("att_approve_attedances");
				
				// Insert Approve Attendance
				$row['start_date'] = $this->bpas->fld($row['start_date']);
				$row['end_date'] = $this->bpas->fld($row['end_date']);
				$this->db->insert("att_approve_attedances", $row);
			}
			return true;
		}
		return false;
	}
	
	public function cancelAttendance($data = array())
	{
		if($data){
			foreach($data as $id){
				$approve = $this->getApproveAttendanceByID($id);
				if($approve){
					// Clear Attendances
					$this->db->where('month', $approve->month);
					$this->db->where('year', $approve->year);
					$this->db->where('employee_id', $approve->employee_id);
					$this->db->delete("att_attedances");
				}
				$this->db->where("id",$id)->delete('att_approve_attedances');
			}
			
			return true;
		}
		return false;
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
	
	public function getTakeLeaveByID($id = false){
		$q = $this->db->get_where('att_take_leaves',array('id' => $id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getTakeLeaveDetail($take_leave_id = false){
		$user = $this->site->getUser($this->session->userdata('user_id'));
		if($take_leave_id){
			$this->db->where('att_take_leave_details.take_leave_id',$take_leave_id);
		}
	
		$this->db->select('att_take_leave_details.*,hr_employees.empcode,hr_employees.firstname,hr_employees.lastname,hr_leave_types.name as leave_type,hr_leave_types.id as leave_id')
		->join('hr_employees','hr_employees.id = att_take_leave_details.employee_id','inner')
		->join('hr_leave_types','hr_leave_types.id = att_take_leave_details.leave_type','left');
		// var_dump($this->session->userdata('view_right'));exit();
		if ( !$this->Owner && !$this->Admin && !$user->view_right) {
			$this->db->where('att_take_leave_details.employee_id',$user->emp_id);
		}
		$q = $this->db->get('att_take_leave_details');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function addTakeLeave($data = false, $dataDetails = false, $dataEmployees = false){
		if($data){
			$this->db->insert('att_take_leaves',$data);
			$take_leave_id = $this->db->insert_id();
			if($dataDetails){
				foreach($dataDetails as $row){
					$row['take_leave_id'] = $take_leave_id;
					$this->db->insert('att_take_leave_details',$row);
				}
			}
			if($dataEmployees){
				foreach($dataEmployees as $dataEmployee){
					$dataEmployee['take_leave_id'] = $take_leave_id;
					$this->db->insert('att_take_leave_employees',$dataEmployee);
				}
			}
			$this->site->sendTelegram("take_leave",$take_leave_id,"added");
			return true;
		}
		return false;
	}
	
	
	public function updateTakeLeave($id = false, $data = false, $dataDetails = false, $dataEmployees = false){
		if($this->db->update('att_take_leaves',$data,array('id'=>$id))){
			$this->db->delete('att_take_leave_details',array('take_leave_id'=>$id));
			$this->db->delete('att_take_leave_employees',array('take_leave_id'=>$id));
			if($dataDetails){
				$this->db->insert_batch('att_take_leave_details',$dataDetails);
			}
			if($dataEmployees){
				$this->db->insert_batch('att_take_leave_employees',$dataEmployees);
			}
			$this->site->sendTelegram("take_leave",$id,"updated");
			return true;
		}
		return false;
	}
	
	public function deleteTakeLeave($id = false){
		$take_leave = $this->getTakeLeaveByID($id);
		if($this->db->delete('att_take_leaves',array('id'=>$id))){
			$data = $this->getTakeLeaveDetail($id);
			$this->db->delete('att_take_leave_details',array('take_leave_id'=>$id));
			$this->db->delete('att_take_leave_employees',array('take_leave_id'=>$id));
			if($data){
				foreach($data as $row){
					$employee_id = $row->employee_id;
					$begin = strtotime($row->start_date);
					$end = strtotime($row->end_date);
					for($i = $begin; $i <= $end; $i = $i + 86400 ) {
						$date = date('Y-m-d', $i );
						$this->generateAttendance($employee_id,$date,$date);
					}
				}
			}
			$this->site->sendTelegram("take_leave",$id,"deleted",$take_leave);
			return true;
		}
		return false;
	}
	
	
	public function approveTakeLeave($id = false, $data = false){
		if($this->db->update('att_take_leaves',$data,array('id'=>$id))){
			$data = $this->getTakeLeaveDetail($id);
			if($data){
				foreach($data as $row){
					$employee_id = $row->employee_id;
					$begin = strtotime($row->start_date);
					$end = strtotime($row->end_date);
					for($i = $begin; $i <= $end; $i = $i + 86400 ) {
						$date = date('Y-m-d', $i );
						$this->generateAttendance($employee_id,$date,$date);
					}
				}
			}
			return true;
		}
		return false;
	}
	
	public function clearAttLog($clear = false, $data = false){
		if($clear){
			if($data){
				if($this->db->insert_batch('att_check_in_out',$data)){
					foreach($data as $row){
						$this->generateAttendance($row['employee_id'],$row['check_time'],$row['check_time']);
					}
				}
			}
			$zk = new ZKLib($clear['ip_address'], $clear['port']);
			if($zk->connect()){
				$zk->clearAttendance();
			}
		}
	}
	
	public function addCheckInOut($data,$clear = false){
		if($data){
			if($this->db->insert_batch('att_check_in_out',$data)){
				foreach($data as $row){
					$this->generateAttendance($row['employee_id'],$row['check_time'],$row['check_time']);
				}
				//=============clear finger acc log===========//
					if($clear){
						$zk = new ZKLib($clear['ip_address'], $clear['port']);
						if($zk->connect()){
							$attendances = count($zk->getAttendance());
							if($attendances==$clear['count_attendance']){
								$zk->clearAttendance();
							}
						}
					}
				//=============end clear finger acc log=========//
			}
			return true;
		}
		return false;
	}
	public function deleteCheckInOut($id = false){
		if($id){
			$data = $this->getCheckInOutByID($id);
			if($this->db->delete('att_check_in_out',array('id'=>$id))){
				$this->generateAttendance($data->employee_id,$data->check_time,$data->check_time);
			}
			return true;
		}
		return false;
	}
	public function getCheckInOutByID($id = false){
		$q = $this->db->get_where('att_check_in_out',array('id'=>$id));
		if($q->num_rows () > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getPolicyByID($id = false){
		$q = $this->db->get_where('att_policies',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function addPolicy($data = false){
		if($data){
			$this->db->insert('att_policies',$data);
			return true;
		}
		return false;
	}
	public function updatePolicy($id = false,$data = false){
		if($this->db->update('att_policies',$data,array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function deletePolicy($id = false){
		if($this->db->delete('att_policies',array('id'=>$id))){
			return true;
		}
		return false;
	}
	
	public function getPolicyWorkingDay($id = false){
		$q = $this->db->get_where('att_policy_working_days',array('policy_id'=>$id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function updatePolicyWorkingDay($id = false, $data = false, $ot_data = false){
		if($data){
			$this->db->delete('att_policy_working_days',array('policy_id'=>$id));
			$this->db->delete('att_ot_policy_working_days',array('policy_id'=>$id));
			$this->db->insert_batch('att_policy_working_days',$data);
			if($ot_data){
				$this->db->insert_batch('att_ot_policy_working_days',$ot_data);
			}
			return true;
		}
		return false;
	}
	
	public function getEmployeeByFingerID($finger_id = false)
	{
		$q = $this->db->get_where("hr_employees", array("finger_id" => $finger_id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getEmployeeByCode($empcode = false)
	{
		$q = $this->db->get_where("hr_employees", array("empcode" => $empcode));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}

	public function getOTPolicyByID($id = false){
		$q = $this->db->get_where('att_ot_policies',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function addOTPolicy($data = false){
		if($data){
			$this->db->insert('att_ot_policies',$data);
			return true;
		}
		return false;
	}
	
	
	public function updateOTPolicy($id = false,$data = false){
		if($this->db->update('att_ot_policies',$data,array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function deleteOTPolicy($id = false){
		if($this->db->delete('att_ot_policies',array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function getOTPolices(){
		$this->db->order_by('ot_policy');
		$q = $this->db->get('att_ot_policies');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getOTPolicyWorkingDay($id = false){
		$q = $this->db->get_where('att_ot_policy_working_days',array('policy_id'=>$id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getEmployeeOT(){
		$where = '';
		$post = $this->input->post();
		if($post){
			if($post['biller']){
				$where .= " AND ".$this->db->dbprefix('hr_employees_working_info').".biller_id = '".$post['biller']."'";
			}
			if($post['department']){
				$where .= " AND ".$this->db->dbprefix('hr_employees_working_info').".department_id = '".$post['department']."'";
			}
			if($post['group']){
				$where .= " AND ".$this->db->dbprefix('hr_employees_working_info').".group_id = '".$post['group']."'";
			}
			if($post['position']){
				$where .= " AND ".$this->db->dbprefix('hr_employees_working_info').".position_id = '".$post['position']."'";
			}
			if($post['start_date']){
				$where .= " AND date(".$this->db->dbprefix('att_check_in_out').".check_time) >= '".$this->bpas->fld($post['start_date'])."'";
			}
			if($post['end_date']){
				$where .= " AND date(".$this->db->dbprefix('att_check_in_out').".check_time) <= '".$this->bpas->fld($post['end_date'])."'";
			}
		}else{
			$where .= " AND date(".$this->db->dbprefix('att_check_in_out').".check_time) = '".date('Y-m-d')."'";
		}
		
		
		$q = $this->db->query("SELECT
								".$this->db->dbprefix('hr_employees').".id,
								".$this->db->dbprefix('hr_employees').".empcode,
								".$this->db->dbprefix('hr_employees').".firstname,
								".$this->db->dbprefix('hr_employees').".lastname,
								".$this->db->dbprefix('hr_positions').".`name` AS position,
								".$this->db->dbprefix('hr_departments').".`name` AS department,
								".$this->db->dbprefix('hr_groups').".`name` AS `group`,
								GROUP_CONCAT(TIME_TO_SEC(TIME(".$this->db->dbprefix('att_check_in_out').".check_time))) as check_time,
								DATE(".$this->db->dbprefix('att_check_in_out').".check_time) as check_date,
								".$this->db->dbprefix('att_ot_policies').".id as policy_ot_id,
								".$this->db->dbprefix('att_ot_policies').".ot_policy,
								TIME_TO_SEC(".$this->db->dbprefix('att_ot_policies').".time_in) as time_in,
								TIME_TO_SEC(".$this->db->dbprefix('att_ot_policies').".time_out) as time_out,
								".$this->db->dbprefix('att_ot_policies').".start_in,
								".$this->db->dbprefix('att_ot_policies').".end_in,
								".$this->db->dbprefix('att_ot_policies').".start_out,
								".$this->db->dbprefix('att_ot_policies').".end_out,
								".$this->db->dbprefix('att_ot_policies').".minimum_min,
								".$this->db->dbprefix('att_ot_policies').".round_min,
								".$this->db->dbprefix('att_ot_policies').".type,
								IFNULL(".$this->db->dbprefix('att_dailies_ot').".id, 0) AS `status`
							FROM
								".$this->db->dbprefix('att_check_in_out')."
							INNER JOIN ".$this->db->dbprefix('hr_employees')." ON ".$this->db->dbprefix('hr_employees').".id = ".$this->db->dbprefix('att_check_in_out').".employee_id
							INNER JOIN ".$this->db->dbprefix('hr_employees_working_info')." ON ".$this->db->dbprefix('hr_employees_working_info').".employee_id = ".$this->db->dbprefix('hr_employees').".id
							INNER JOIN ".$this->db->dbprefix('att_ot_policy_working_days')." ON ".$this->db->dbprefix('att_ot_policy_working_days').".policy_id = ".$this->db->dbprefix('hr_employees_working_info').".policy_id
							INNER JOIN ".$this->db->dbprefix('att_ot_policies')." ON ".$this->db->dbprefix('att_ot_policies').".id = ".$this->db->dbprefix('att_ot_policy_working_days').".ot_policy_id
							LEFT JOIN ".$this->db->dbprefix('hr_departments')." ON ".$this->db->dbprefix('hr_departments').".id = ".$this->db->dbprefix('hr_employees_working_info').".department_id
							LEFT JOIN ".$this->db->dbprefix('hr_groups')." ON ".$this->db->dbprefix('hr_groups').".id = ".$this->db->dbprefix('hr_employees_working_info').".group_id
							LEFT JOIN ".$this->db->dbprefix('hr_positions')." ON ".$this->db->dbprefix('hr_positions').".id = ".$this->db->dbprefix('hr_employees_working_info').".position_id
							LEFT JOIN ".$this->db->dbprefix('att_dailies_ot')." ON ".$this->db->dbprefix('att_dailies_ot').".employee_id = ".$this->db->dbprefix('att_check_in_out').".employee_id
							AND ".$this->db->dbprefix('att_dailies_ot').".date = date(".$this->db->dbprefix('att_check_in_out').".check_time)
							AND ".$this->db->dbprefix('att_ot_policies').".id = ".$this->db->dbprefix('att_dailies_ot').".policy_ot_id
							WHERE
							".$this->db->dbprefix('att_ot_policy_working_days').".`day` = (
								CASE
								WHEN (
									SELECT
										COUNT(id)
									FROM
										".$this->db->dbprefix('calendar')."
									WHERE
										holiday = 1 
									AND DATE(
											".$this->db->dbprefix('att_check_in_out').".check_time
										) >= DATE(`start`)
									AND DATE(
										".$this->db->dbprefix('att_check_in_out').".check_time
									) <= DATE(`end`)
								) > 0 THEN
									'Hol'
								ELSE
									DATE_FORMAT(
										".$this->db->dbprefix('att_check_in_out').".check_time,
										'%a'
									)
								END
							)	
							".$where."
							AND (
								(
									time(".$this->db->dbprefix('att_check_in_out').".check_time) >= time(
										".$this->db->dbprefix('att_ot_policies').".start_in
									)
									AND time(".$this->db->dbprefix('att_check_in_out').".check_time) <= time(".$this->db->dbprefix('att_ot_policies').".end_in)
								)
								OR (
									time(".$this->db->dbprefix('att_check_in_out').".check_time) >= time(
										".$this->db->dbprefix('att_ot_policies').".start_out
									)
									AND time(".$this->db->dbprefix('att_check_in_out').".check_time) <= time(
										".$this->db->dbprefix('att_ot_policies').".end_out
									)
								)
							)
							GROUP BY
								".$this->db->dbprefix('hr_employees').".id,
								DATE(".$this->db->dbprefix('att_check_in_out').".check_time),
								".$this->db->dbprefix('att_ot_policies').".id
							HAVING `status` = 0			
							");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function approveOT($data = false){
		if($data){
			$this->db->insert_batch('att_dailies_ot',$data);
			foreach($data as $row){
				$this->generateAttendance($row['employee_id'],$row['date'],$row['date']);
			}
			return true;
		}
		return false;
	}
	
	public function getEmployeeInfoByID($id = false){
		$this->db->select('hr_employees.*,hr_employees_working_info.*')
			->join('hr_employees_working_info','hr_employees_working_info.employee_id = hr_employees.id','inner')
			->where('hr_employees.id',$id);
		$q = $this->db->get('hr_employees');	
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getEmployeeWorkingInfo($employee_id = false,$biller_id = false,$position_id = false,$department_id = false,$group_id = false,$status = false, $date = false){
		$this->db->select('hr_employees_working_info.*,hr_employees.empcode,hr_employees.firstname,hr_employees.lastname,hr_positions.name as position, hr_departments.name as department, hr_groups.name as group');
		if($employee_id){
			$this->db->where('hr_employees_working_info.employee_id',$employee_id);
		}
		if($biller_id){
			$this->db->where('hr_employees_working_info.biller_id',$biller_id);
		}
		if($position_id){
			$this->db->where('hr_employees_working_info.position_id',$position_id);
		}
		if($department_id){
			$this->db->where('hr_employees_working_info.department_id',$department_id);
		}
		if($group_id){
			$this->db->where('hr_employees_working_info.group_id',$group_id);
		}
		if($status){
			$this->db->where('hr_employees_working_info.status',$status);
		}
		if($date){
			$this->db->where('('.$this->db->dbprefix("hr_employees_working_info").'.status != "inactive" OR '.$this->db->dbprefix("hr_employees_working_info").'.resigned_date > "'.$date.'")');
		}
		$this->db->join('hr_employees','hr_employees.id = hr_employees_working_info.employee_id','inner')
				->join('hr_positions','hr_positions.id = hr_employees_working_info.position_id','left')
				->join('hr_departments','hr_departments.id = hr_employees_working_info.department_id','left')
				->join('hr_groups','hr_groups.id = hr_employees_working_info.group_id','left');
		$q = $this->db->get('hr_employees_working_info');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getEmployeeTakeLeave($employee_id = false,$start_date = false,$end_date = false){
		$where = "";
		if($employee_id){
			$where .=" AND ".$this->db->dbprefix('att_take_leave_details').".employee_id = '".$employee_id."'";
		}
		if($start_date && $end_date){
			$where .=" AND (
							(
								".$this->db->dbprefix('att_take_leave_details').".start_date >= '".$start_date."'
								AND ".$this->db->dbprefix('att_take_leave_details').".start_date <= '".$end_date."'
							)
							OR (
								".$this->db->dbprefix('att_take_leave_details').".end_date >= '".$start_date."'
								AND ".$this->db->dbprefix('att_take_leave_details').".end_date <= '".$end_date."'
							)
						)";
		}else if($start_date){
			$where .=" AND '".$start_date."' >= ".$this->db->dbprefix('att_take_leave_details').".start_date AND '".$start_date."' <= ".$this->db->dbprefix('att_take_leave_details').".end_date";
		}else if($end_date){
			$where .=" AND '".$end_date."' >= ".$this->db->dbprefix('att_take_leave_details').".start_date AND '".$end_date."' <= ".$this->db->dbprefix('att_take_leave_details').".end_date";
		}

		$q = $this->db->query("SELECT
							".$this->db->dbprefix('att_take_leave_details').".employee_id,
							".$this->db->dbprefix('att_take_leave_details').".leave_type,
							".$this->db->dbprefix('att_take_leave_details').".start_date,
							".$this->db->dbprefix('att_take_leave_details').".end_date,
							".$this->db->dbprefix('att_take_leave_details').".timeshift,
							".$this->db->dbprefix('hr_leave_types').".name as leave_name
						FROM
							".$this->db->dbprefix('att_take_leaves')."
						INNER JOIN ".$this->db->dbprefix('att_take_leave_details')." ON ".$this->db->dbprefix('att_take_leave_details').".take_leave_id = ".$this->db->dbprefix('att_take_leaves').".id
						INNER JOIN ".$this->db->dbprefix('hr_leave_types')." ON ".$this->db->dbprefix('hr_leave_types').".id = ".$this->db->dbprefix('att_take_leave_details').".leave_type
						WHERE
							".$this->db->dbprefix('att_take_leaves').".`status` = 1
							".$where."	
						");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getEmpoyeeOT($employee_id = false,$start_date = false,$end_date = false){
		$this->db->select('att_dailies_ot.*,TIME_TO_SEC('.$this->db->dbprefix("att_dailies_ot").'.ot) as ot');
		if($employee_id){
			$this->db->where('att_dailies_ot.employee_id',$employee_id);
		}
		if($start_date){
			$this->db->where('att_dailies_ot.date >=',$start_date);
		}
		if($end_date){
			$this->db->where('att_dailies_ot.date <=',$end_date);
		}	
		$q = $this->db->get('att_dailies_ot');
		
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getEmployeeAttedance($employee_id = false,$start_date = false,$end_date = false){
		$this->db->select('att_dailies.*,
							date('.$this->db->dbprefix("att_dailies").'.check_time) as check_date, 
							time_to_sec('.$this->db->dbprefix("att_dailies").'.before_time) as before_time, 
							time_to_sec('.$this->db->dbprefix("att_dailies").'.after_time) as after_time, 
							DATE_FORMAT('.$this->db->dbprefix("att_dailies").'.check_time,"%H:%i") as time_only');
		if($employee_id){
			$this->db->where('att_dailies.employee_id',$employee_id);
		}
		if($start_date){
			$this->db->where('date('.$this->db->dbprefix("att_dailies").'.check_time) >=',$start_date);
		}
		if($end_date){
			$this->db->where('date('.$this->db->dbprefix("att_dailies").'.check_time) <=',$end_date);
		}
		$q = $this->db->get('att_dailies');
		
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getEmployeePolicyWorkingDay($employee_id = false,$biller_id = false,$position_id = false,$department_id = false,$group_id = false){
		$this->db->select('
			hr_employees_working_info.employee_id,
			att_policies.round_min,
			att_policies.minimum_min,
			att_policy_working_days.day,
			att_policy_working_days.time_one,
			att_policy_working_days.time_two,
			att_policies.type');
		if($employee_id){
			$this->db->where('hr_employees_working_info.employee_id',$employee_id);
		}
		if($biller_id){
			$this->db->where('hr_employees_working_info.biller_id',$biller_id);
		}
		if($position_id){
			$this->db->where('hr_employees_working_info.position_id',$position_id);
		}
		if($department_id){
			$this->db->where('hr_employees_working_info.department_id',$department_id);
		}
		if($group_id){
			$this->db->where('hr_employees_working_info.group_id',$group_id);
		}
		// $this->db->where('att_policy_working_days.policy_id', 3);
		$this->db->join('att_policies','att_policies.id = hr_employees_working_info.policy_id','inner');
		$this->db->join('att_policy_working_days','att_policies.id = att_policy_working_days.policy_id','inner');
		$q = $this->db->get('hr_employees_working_info');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			// var_dump($data);
			// exit();
			return $data;
		}
		return false;
	}
	
	public function getApprovedAttendance($employee_id = false,$start_date = false,$end_date = false){
		if($employee_id){
			$this->db->where('att_attedances.employee_id',$employee_id);
		}
		if($start_date){
			$this->db->where('att_attedances.date >=',$start_date);
		}
		if($end_date){
			$this->db->where('att_attedances.date <=',$end_date);
		}
		$this->db->where('att_attedances.status',1);
		$q = $this->db->get('att_attedances');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getHolidays($start_date = false, $end_date = false){
		if($start_date && $end_date){
			$this->db->where("(('".$start_date."'>= date(".$this->db->dbprefix('calendar').".start) AND '".$start_date."' <= date(".$this->db->dbprefix('calendar').".end)) OR ('".$end_date."' >= date(".$this->db->dbprefix('calendar').".start) AND '".$end_date."' <= date(".$this->db->dbprefix('calendar').".end)))");
		}
		$q = $this->db->get_where("calendar",array("holiday"=>1));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getEmployeeDayOffs($employee_id = false, $start_date = false, $end_date = false){
		if($employee_id){
			$this->db->where("att_day_off_items.employee_id",$employee_id);
		}
		if($start_date){
			$this->db->where("att_day_off_items.day_off >=",$start_date);
		}
		if($end_date){
			$this->db->where("att_day_off_items.day_off <=",$end_date);
		}
		$q = $this->db->get("att_day_off_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false; 
		
	}
	public function getEmployeeWorkingDay($employee_id = false,$start_date = false,$end_date = false){
		$working_day = 0;
		$employee = $this->getEmployeeInfoByID($employee_id);
		$employee_working_policies = $this->getEmployeePolicyWorkingDay($employee_id);
		$holidays = $this->getHolidays();
		$day_offs = $this->getEmployeeDayOffs($employee_id,$start_date,$end_date);
		if($employee_working_policies){
			foreach($employee_working_policies as $employee_working_policy){
				$t_employee_policies[$employee_working_policy->employee_id][$employee_working_policy->day] = array('round_min'=>$employee_working_policy->round_min,'minimum_min'=>$employee_working_policy->minimum_min,'time_one'=>$employee_working_policy->time_one,'time_two'=>$employee_working_policy->time_two);
			}
		}
		if($holidays){
			foreach($holidays as $holiday){
				$begin = strtotime($holiday->start);
				$end = strtotime($holiday->end);
				for($i = $begin; $i <= $end; $i = $i + 86400 ) {
					$date = date('Y-m-d', $i );
					$t_holiday[$date] = $date;
				}
			}
		}
		if($day_offs){
			foreach($day_offs as $day_off){
				$t_day_offs[$day_off->employee_id][$day_off->day_off] = $day_off;
			}
		}
		$begin = strtotime($start_date);
		$end = strtotime($end_date);
		for($i = $begin; $i <= $end; $i = $i + 86400 ) {
			$date = date('Y-m-d', $i );
			$day = date('D', $i);
			$employee_policy = isset($t_employee_policies[$employee_id][$day])? $t_employee_policies[$employee_id][$day]: false;
			if($employee_policy){
				if(!isset($t_day_offs[$employee_id][$date]) && ((!isset($t_holiday[$date])) || ($t_holiday[$date] && isset($t_employee_policies[$employee_id]['Hol']))) && $date >= $employee->employee_date){
					if($employee_policy['time_one']=='1'){
						$working_day += 0.5;
					}
					if($employee_policy['time_two']=='1'){
						$working_day += 0.5;
					}
				}
			}
		}
		return $working_day;
	}
	
	public function generateAttendance($employee_id = false,$start_date = false,$end_date = false,$biller_id = false,$position_id = false,$department_id = false,$group_id = false, $status = false){
		$data = array();
		if($start_date){
			$start_date = date("Y-m-d", strtotime($start_date));
		}
		if($end_date){
			$end_date = date("Y-m-d", strtotime($end_date));
		}

		$employees = $this->getEmployeeWorkingInfo($employee_id,$biller_id,$position_id,$department_id,$group_id,$status);
		
		if($employees){
			$employee_working_policies = $this->getEmployeePolicyWorkingDay($employee_id,$biller_id,$position_id,$department_id,$group_id);

			$take_leaves = $this->getEmployeeTakeLeave($employee_id,$start_date,$end_date);
			$over_times  = $this->getEmpoyeeOT($employee_id,$start_date,$end_date);
			$attendances = $this->getEmployeeAttedance($employee_id,$start_date,$end_date);

			$approved_attendances = $this->getApprovedAttendance($employee_id,$start_date,$end_date);
			$holidays = $this->getHolidays($start_date,$end_date);
			$day_offs = $this->getEmployeeDayOffs($employee_id,$start_date,$end_date);
			$t_attedances = array();
			$t_employee_roster_policies = array();
			$t_employee_policies = array();
			$t_take_leaevs = array();
			$t_over_times = array();
			$t_approved_attedances = array();
			$t_holiday = array();
			$t_day_offs = array();
			$t_attedances_cross_day = array();
			
			if($approved_attendances){
				foreach($approved_attendances as $approved_attendance){
					$t_approved_attedances[$approved_attendance->employee_id][$approved_attendance->date]=true;;
				}
			}
			// var_dump($employee_working_policies);
			// exit();
			if($employee_working_policies){
				foreach($employee_working_policies as $employee_working_policy){
					$t_employee_policies[$employee_working_policy->employee_id][$employee_working_policy->day] = array(
						'round_min'		=>	$employee_working_policy->round_min,
						'minimum_min'	=>	$employee_working_policy->minimum_min,
						'time_one'		=>	$employee_working_policy->time_one,
						'time_two'		=>	$employee_working_policy->time_two,
						'type'			=>	$employee_working_policy->type,
						'working_day'	=> 	$employee_working_policy->day,
					);
				}
			}

			if($over_times){
				foreach($over_times as $over_time){
					$t_over_times[$over_time->employee_id][$over_time->date][$over_time->type] = $over_time->ot + $t_over_times[$over_time->employee_id][$over_time->date][$over_time->type];
				}
			}

			if ($take_leaves) {
				foreach($take_leaves as $take_leave){
					$begin = strtotime($take_leave->start_date);
					$end = strtotime($take_leave->end_date);
					for($i = $begin; $i <= $end; $i = $i + 86400 ) {
						$date = date('Y-m-d', $i );
						$t_take_leaevs[$take_leave->employee_id][$date] = $take_leave->timeshift;
					}
				}
			}
			if($attendances){
				foreach($attendances as $attendance){
					$t_attedances[$attendance->employee_id][$attendance->check_date][$attendance->timeshift][$attendance->check_type] = array(
						'check_time'	=> $attendance->check_time,
						'before_time'	=> $attendance->before_time,
						'after_time'	=> $attendance->after_time);
				}
			}
			if($holidays){
				foreach($holidays as $holiday){
					$begin = strtotime($holiday->start);
					$end = strtotime($holiday->end);
					for($i = $begin; $i <= $end; $i = $i + 86400 ) {
						$date = date('Y-m-d', $i );
						$t_holiday[$date] = $date;
					}
				}
			}

			if($day_offs){
				foreach($day_offs as $day_off){
					$t_day_offs[$day_off->employee_id][$day_off->day_off] = $day_off;
				}
			}
			foreach($employees as $employee){
				
				$employee_roster_working_policies= $this->getEmployeeRosterPolicyWorkingDay($employee->employee_id,$biller_id,$position_id,$department_id,$group_id);

				if($employee_roster_working_policies){
					foreach($employee_roster_working_policies as $employee_roster_working_policy){
						$t_employee_roster_policies[$employee_roster_working_policy->employee_id][$employee_roster_working_policy->date] = array(
							'working_day'	=> 	$employee_roster_working_policy->date,
							'round_min'		=>	$employee_roster_working_policy->round_min,
							'minimum_min'	=>	$employee_roster_working_policy->minimum_min,
							'time_one'		=>	$employee_roster_working_policy->time_one,
							'time_two'		=>	$employee_roster_working_policy->time_two,
							'type'			=>	$employee_roster_working_policy->type,
							'employee_id' 	=> 	$employee_roster_working_policy->employee_id,
						);
					}
				}

				


				$begin = strtotime($start_date);
				$end = strtotime($end_date);
			
				
				for($i = $begin; $i <= $end; $i = $i + 86400 ) {					
					$working_day = 0;
					$present = 0;
					$permission = 0;
					$absent = 0;
					$coming_late = 0;
					$leave_early = 0;
					$weekend_ot = 0;
					$normal_ot = 0;
					$holiday_ot = 0;
					$one_in = false;
					$one_out = false;
					$two_in = false;
					$two_out = false;
					$date = date('Y-m-d', $i );
					$day = date('D', $i);
					$cross_date = date('Y-m-d', strtotime("+1 day", strtotime($date)));
					$cross_day = date('Y-m-d', strtotime("+1 day", strtotime($start_date)));

					$attendances_cross_day = $this->getEmployeeAttedance($employee_id,$cross_day,$cross_day);
				
					if($attendances_cross_day){
						foreach($attendances_cross_day as $attendance){
							$t_attedances_cross_day[$attendance->employee_id][$attendance->check_date][$attendance->timeshift][$attendance->check_type] = array(
								'check_time'	=> $attendance->check_time,
								'before_time'	=> $attendance->before_time,
								'after_time'	=> $attendance->after_time);
						}
					}

					$employee_policy= isset($t_employee_policies[$employee->employee_id][$day])?$t_employee_policies[$employee->employee_id][$day]: '';
					$employee_roster_policy = isset($t_employee_roster_policies[$employee->employee_id][$date])? $t_employee_roster_policies[$employee->employee_id][$date]: '';

					

					if(!isset($t_approved_attedances[$employee->employee_id][$date])){
						if(!isset($t_day_offs[$employee->employee_id][$date]) && (!isset($t_holiday[$date])) || ($t_holiday[$date] && isset($t_employee_policies[$employee->employee_id]['Hol']))){

							$employee_info = $this->getEmployeeInfoByID($employee->employee_id);

							if($employee_info->roster && $employee_roster_policy){

								$policy_type = $employee_roster_policy['type'];
								if($policy_type =='cross_shift'){
									if($employee_roster_policy['time_one']=='1'){
										$working_day += 0.5;
										$one_in = isset($t_attedances[$employee->employee_id][$date]['one']['in'])? $t_attedances[$employee->employee_id][$date]['one']['in']: '';
										$one_out = '';
									}
									if($employee_roster_policy['time_two']=='1'){
										$working_day += 0.5;
										$two_in 	= '';
										$gettwo_out = $this->getEmployeeCrossAtt($employee->employee_id,$cross_date,'two','out');
										$two_out = isset($t_attedances_cross_day[$employee->employee_id][$cross_date]['two']['out'])? $t_attedances_cross_day[$employee->employee_id][$cross_date]['two']['out']: '';
										//$two_out 	= $gettwo_out ? (array)$gettwo_out:'';
									}
								}else{
									if($employee_roster_policy['time_one']=='1'){
										$working_day += 0.5;
										$one_in = isset($t_attedances[$employee->employee_id][$date]['one']['in'])? $t_attedances[$employee->employee_id][$date]['one']['in']: '';
										$one_out = isset($t_attedances[$employee->employee_id][$date]['one']['out'])? $t_attedances[$employee->employee_id][$date]['one']['out'] : '';
									}
									if($employee_roster_policy['time_two']=='1'){
										$working_day += 0.5;
										$two_in = isset($t_attedances[$employee->employee_id][$date]['two']['in'])? $t_attedances[$employee->employee_id][$date]['two']['in']: '';
										$two_out = isset($t_attedances[$employee->employee_id][$date]['two']['out'])? $t_attedances[$employee->employee_id][$date]['two']['out']: '';
									}
								}
								if($this->Settings->scan_per_shift ==2){
									if($one_in && $two_out){
										$present = 1;
									}else if($one_in || $two_out){
										$present = 0.5;
									}
								}else{
									if($one_in && $two_out){
										$present = 1;
									}else if(($one_in || $two_out) && ($one_out || $two_in)){
										$present = 0.5;
									}
								}
								//========LATE AND EARLY========//
								$coming_late += $this->bpas->round_time($employee_roster_policy['round_min'], $employee_roster_policy['minimum_min'], isset($one_in['after_time']) ? $one_in['after_time'] : 0);
								$coming_late += $this->bpas->round_time($employee_roster_policy['round_min'], $employee_roster_policy['minimum_min'], isset($two_in['after_time']) ? $two_in['after_time'] : 0);
								  
								$leave_early += $this->bpas->round_time($employee_roster_policy['round_min'], $employee_roster_policy['minimum_min'], isset($one_out['before_time']) ? $one_out['before_time'] : 0);
								$leave_early += $this->bpas->round_time($employee_roster_policy['round_min'], $employee_roster_policy['minimum_min'], isset($two_out['before_time']) ? $two_out['before_time'] : 0);
							} 

							if(($employee_info->roster==0) && $employee_policy){

								$policy_type = $employee_policy['type'];
								if($policy_type =='cross_shift'){
									if($employee_policy['time_one']=='1'){
										$working_day += 0.5;
										$one_in = isset($t_attedances[$employee->employee_id][$date]['one']['in'])? $t_attedances[$employee->employee_id][$date]['one']['in']: '';
										$one_out = '';
									}
									if($employee_policy['time_two']=='1'){
										$working_day += 0.5;
										$two_in 	= '';

										$gettwo_out = $this->getEmployeeCrossAtt($employee->employee_id,$cross_date,'two','out');
										$two_out 	= $gettwo_out ? (array)$gettwo_out:'';
									}
								}else{
									//==========PRESENT=========//
									if($employee_policy['time_one']=='1'){
										
										$working_day += 0.5;
										$one_in = isset($t_attedances[$employee->employee_id][$date]['one']['in'])? $t_attedances[$employee->employee_id][$date]['one']['in']: '';

										$one_out = isset($t_attedances[$employee->employee_id][$date]['one']['out'])? $t_attedances[$employee->employee_id][$date]['one']['out'] : '';
									}
									if($employee_policy['time_two']=='1'){
									
										$working_day += 0.5;
										$two_in = isset($t_attedances[$employee->employee_id][$date]['two']['in'])? $t_attedances[$employee->employee_id][$date]['two']['in']: '';

										$two_out = isset($t_attedances[$employee->employee_id][$date]['two']['out'])? $t_attedances[$employee->employee_id][$date]['two']['out']: '';
									}
								}

								if($this->Settings->scan_per_shift == 2){
									if($one_in && $two_out){
										$present = 1;
									}else if($one_in || $two_out){
										$present = 0.5;
									}
								}else{
									if($one_in && $two_out){
										$present = 1;
									}else if(($one_in || $two_out) && ($one_out || $two_in)){
										$present = 0.5;
									}
								}
								
								//========LATE AND EARLY========//
								$coming_late += $this->bpas->round_time($employee_policy['round_min'],$employee_policy['minimum_min'],isset($one_in['after_time'])? $one_in['after_time']: 0);
								$coming_late += $this->bpas->round_time($employee_policy['round_min'],$employee_policy['minimum_min'],isset($two_in['after_time'])? $two_in['after_time']: 0);
								
								$leave_early += $this->bpas->round_time($employee_policy['round_min'],$employee_policy['minimum_min'],isset($one_out['before_time'])? $one_out['before_time']: 0);
								$leave_early += $this->bpas->round_time($employee_policy['round_min'],$employee_policy['minimum_min'],isset($two_out['before_time'])? $two_out['before_time']: 0);
							}

							$coming_late = (int)($coming_late);
							$leave_early = (int)($leave_early);
							//==========PERMISSIOn==========//	
								if (count($t_take_leaevs) > 0 && array_key_exists($employee->employee_id, $t_take_leaevs)) {
										$emp_take_leave = isset($t_take_leaevs[$employee->employee_id][$date])? $t_take_leaevs[$employee->employee_id][$date]: '';
										if($present < $working_day && $emp_take_leave){
										if($t_take_leaevs[$employee->employee_id][$date]=='full'){
											$permission = $working_day - $present;
										}else{
											$permission = 0.5;
										}
									}
								}
							//============ABSENT===========//	
							$absent = $working_day - $present - $permission;
						}else if($date >= $employee->employee_date && ((isset($t_holiday[$date]) && $t_holiday[$date]) || isset($t_day_offs[$employee->employee_id][$date]))){
							$holiday = 1;
						}
					

						//===============OT================//
						if (array_key_exists($employee->employee_id, $t_over_times)) {
							$weekend_ot = $t_over_times[$employee->employee_id][$date]['weekend'];
							$holiday_ot = $t_over_times[$employee->employee_id][$date]['holiday'];
							$normal_ot = $t_over_times[$employee->employee_id][$date]['normal'];
						}else{
							$weekend_ot = '';
							$holiday_ot = '';
							$normal_ot = '';
						}
						$data[] = array('employee_id'	=> $employee->employee_id,
										'date'			=> $date,
										'working_day'	=> $working_day,
										'present'		=> $present,
										'permission'	=> $permission,	
										'absent'		=> $absent,
										'weekend_ot'	=> $weekend_ot,
										'normal_ot'		=> $normal_ot,
										'holiday_ot'	=> $holiday_ot,
										'late'			=> $coming_late,
										'leave_early'	=> $leave_early,
								);
					}
					
				}
			}
			// var_dump($data);
			// exit();
			if($data){
				foreach($data as $row){
					$this->db->delete('att_attedances',array('employee_id'=>$row["employee_id"],'date'=>$row["date"],'status'=>0));
				}
				$this->db->insert_batch('att_attedances',$data);
				return true;
			}
			return false;
		}

		return false;
	}
	
	
	public function getEmployeeLeaveCategory(){
		$post = $this->input->post();
		$where = '';
		if($post){
			if($post['biller']){
				$where .= " AND ".$this->db->dbprefix('hr_employees_working_info').".biller_id = '".$post['biller']."'";
			}
			if($post['department']){
				$where .= " AND ".$this->db->dbprefix('hr_employees_working_info').".department_id = '".$post['department']."'";
			}
			if($post['group']){
				$where .= " AND ".$this->db->dbprefix('hr_employees_working_info').".group_id = '".$post['group']."'";
			}
			if($post['position']){
				$where .= " AND ".$this->db->dbprefix('hr_employees_working_info').".position_id = '".$post['position']."'";
			}
			if($post['employee']){
				$where .= " AND ".$this->db->dbprefix('att_take_leave_details').".employee_id = '".$post['employee']."'";
			}
			if($post['start_date']){
				$start_date = $this->bpas->fsd($post['start_date']);
			}
			if($post['end_date']){
				$end_date = $this->bpas->fsd($post['end_date']);
			}
			if($start_date && $end_date){
				$where .=" AND (
								(
									".$this->db->dbprefix('att_take_leave_details').".start_date >= '".$start_date."'
									AND ".$this->db->dbprefix('att_take_leave_details').".start_date <= '".$end_date."'
								)
								OR (
									".$this->db->dbprefix('att_take_leave_details').".end_date >= '".$start_date."'
									AND ".$this->db->dbprefix('att_take_leave_details').".end_date <= '".$end_date."'
								)
							)";
			}else if($start_date){
				$where .=" AND '".$start_date."' >= ".$this->db->dbprefix('att_take_leave_details').".start_date AND '".$start_date."' <= ".$this->db->dbprefix('att_take_leave_details').".end_date";
			}else if($end_date){
				$where .=" AND '".$end_date."' >= ".$this->db->dbprefix('att_take_leave_details').".start_date AND '".$end_date."' <= ".$this->db->dbprefix('att_take_leave_details').".end_date";
			}
		}else{
			$start_date = date('Y-m-d');
			$end_date = date('Y-m-d');
			$where .=" AND (
								(
									".$this->db->dbprefix('att_take_leave_details').".start_date >= '".$start_date."'
									AND ".$this->db->dbprefix('att_take_leave_details').".start_date <= '".$end_date."'
								)
								OR (
									".$this->db->dbprefix('att_take_leave_details').".end_date >= '".$start_date."'
									AND ".$this->db->dbprefix('att_take_leave_details').".end_date <= '".$end_date."'
								)
							)";
		}
		$q = $this->db->query("SELECT
								".$this->db->dbprefix('att_take_leave_details').".employee_id,
								".$this->db->dbprefix('hr_employees').".empcode,
								".$this->db->dbprefix('hr_employees').".firstname,
								".$this->db->dbprefix('hr_employees').".lastname,
								".$this->db->dbprefix('hr_departments').".`name` AS department,
								".$this->db->dbprefix('hr_groups').".`name` AS `group`,
								".$this->db->dbprefix('hr_positions').".`name` AS position,
								".$this->db->dbprefix('hr_leave_categories').".id AS leave_category_id,
								SUM(IF(timeshift = 'full', (DATEDIFF(".$this->db->dbprefix('att_take_leave_details').".end_date, ".$this->db->dbprefix('att_take_leave_details').".start_date) + 1), ((DATEDIFF(".$this->db->dbprefix('att_take_leave_details').".end_date, ".$this->db->dbprefix('att_take_leave_details').".start_date) + 1) / 2))) as total_leave
							FROM
								`".$this->db->dbprefix('att_take_leave_details')."`
							INNER JOIN ".$this->db->dbprefix('att_take_leaves')." ON ".$this->db->dbprefix('att_take_leaves').".id = ".$this->db->dbprefix('att_take_leave_details').".take_leave_id
							INNER JOIN ".$this->db->dbprefix('hr_employees')." ON ".$this->db->dbprefix('hr_employees').".id = ".$this->db->dbprefix('att_take_leave_details').".employee_id
							INNER JOIN ".$this->db->dbprefix('hr_leave_types')." ON ".$this->db->dbprefix('hr_leave_types').".id = ".$this->db->dbprefix('att_take_leave_details').".leave_type
							INNER JOIN ".$this->db->dbprefix('hr_leave_categories')." ON ".$this->db->dbprefix('hr_leave_categories').".id = ".$this->db->dbprefix('hr_leave_types').".category_id
							LEFT JOIN ".$this->db->dbprefix('hr_employees_working_info')." ON ".$this->db->dbprefix('hr_employees_working_info').".employee_id = ".$this->db->dbprefix('hr_employees').".id
							LEFT JOIN ".$this->db->dbprefix('hr_departments')." ON ".$this->db->dbprefix('hr_departments').".id = ".$this->db->dbprefix('hr_employees_working_info').".department_id
							LEFT JOIN ".$this->db->dbprefix('hr_groups')." ON bpas_hr_groups.id = ".$this->db->dbprefix('hr_employees_working_info').".group_id
							LEFT JOIN ".$this->db->dbprefix('hr_positions')." ON ".$this->db->dbprefix('hr_positions').".id = ".$this->db->dbprefix('hr_employees_working_info').".position_id
							WHERE
								".$this->db->dbprefix('att_take_leaves').".`status` = 1
							".$where."	
							GROUP BY
								".$this->db->dbprefix('att_take_leave_details').".employee_id,
								".$this->db->dbprefix('hr_leave_categories').".id");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}				
		return false;
		
	}
	
	public function addDevice($data = null )
	{
		if($this->db->insert('att_devices',$data)){
			return true;
		}
		return false;
		
	}
	public function getDeviceByID($id = null)
	{
		$q = $this->db->get_where('att_devices',array('id' => $id));
		if($q->num_rows() > 0){
			return $q->row();
		} 
		return false;
	}
	public function updateDevice($id = null ,$data = null)
	{
		$this->db->where('id',$id);
		if($this->db->update('att_devices',$data)){
			return true;
		}
		return false;
	}
	public function deleteDevice($id = false)
	{
		$this->db->where('id',$id);
		if($this->db->delete('att_devices')){
			return true;
		}
		return false;
	}
	public function getDevices($active = false){
		if($active){
			$this->db->where('att_devices.inactive',0)->or_where('att_devices.inactive IS NULL');
		}
		$q = $this->db->get('att_devices');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getCheckInOutByEmployeeCheckTimeInt($employee_id = false, $check_time_int = false){
		$q = $this->db->get_where('att_check_in_out',array('employee_id'=>$employee_id,'check_time_int'=>$check_time_int),1);
		if($q->num_rows() > 0){
			return true;
		}
		return false;
		
	}
	
	public function getDepartment(){
		
		if($department = $this->input->post('department')){
			$this->db->where('id',$department);
		}
		if($biller = $this->input->post('biller')){
			$this->db->where('biller_id',$biller);
		}
		
		$q = $this->db->get('hr_departments');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getGroup(){
		
		if($group = $this->input->post('group')){
			$this->db->where('id',$group);
		}
		
		$q = $this->db->get('hr_groups');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getDailyAttendanceDepartmentGroup(){
		
		if($this->input->post('start_date')){
			$start_date = $this->bpas->fsd($this->input->post('start_date'));
		}else{
			$start_date = date('Y-m-d');
		}
		
		$this->db->where('att_attedances.date',$start_date);

		
		if($department = $this->input->post('department')){
			$this->db->where('hr_employees_working_info.department_id',$department);
		}
		
		if($group = $this->input->post('group')){
			$this->db->where('hr_employees_working_info.group_id',$group);
		}
		
		if($biller = $this->input->post('biller')){
			$this->db->where('hr_employees_working_info.biller_id',$biller);
		}
		
		if($position = $this->input->post('position')){
			$this->db->where('hr_employees_working_info.position_id',$position);
		}
		
		$this->db->select('hr_employees_working_info.department_id,
							hr_employees_working_info.group_id,
							sum('.$this->db->dbprefix("att_attedances.working_day").') as working_day,
							sum('.$this->db->dbprefix("att_attedances.present").') as present,
							sum('.$this->db->dbprefix("att_attedances.permission").') as permission,
							sum('.$this->db->dbprefix("att_attedances.absent").') as absent
						')
				->join('hr_employees_working_info','hr_employees_working_info.employee_id = bpas_att_attedances.employee_id','inner')
				->group_by('hr_employees_working_info.department_id,hr_employees_working_info.group_id');
			
		$q = $this->db->get('bpas_att_attedances');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getPolicyByEmployeeID($id = false){
		$this->db->select('att_policies.*')
		->join('hr_employees_working_info','hr_employees_working_info.policy_id = att_policies.id','inner')
		->where('hr_employees_working_info.employee_id',$id);
		$q = $this->db->get('att_policies');	
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}

	public function getEmployeeCrossAtt($employee_id = false,$check_date = false,$timeshift = false,$check_type = false){
		$this->db->select('
						date('.$this->db->dbprefix("att_dailies").'.check_time) as check_time, 
							time_to_sec('.$this->db->dbprefix("att_dailies").'.before_time) as before_time, 
							time_to_sec('.$this->db->dbprefix("att_dailies").'.after_time) as after_time, 
							DATE_FORMAT('.$this->db->dbprefix("att_dailies").'.check_time,"%H:%i") as time_only');

		$this->db->where('att_dailies.employee_id',$employee_id);
		$this->db->where('date('.$this->db->dbprefix("att_dailies").'.check_time)',$check_date);
		$this->db->where(''.$this->db->dbprefix("att_dailies").'.timeshift',$timeshift);
		$this->db->where(''.$this->db->dbprefix("att_dailies").'.check_type',$check_type);
		$q = $this->db->get('att_dailies');
		
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getDayOffByID($id = false){
		$q = $this->db->get_where("att_day_off",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		} 
		return false;
	}
	public function getDayOffItems($day_off_id = false){
		if($day_off_id){
			$this->db->where('att_day_off_items.day_off_id',$day_off_id);
		}
		$this->db->select('att_day_off_items.*,hr_employees.empcode,hr_employees.firstname,hr_employees.lastname');
		$this->db->join('hr_employees','hr_employees.id = att_day_off_items.employee_id','inner');
		$q = $this->db->get('att_day_off_items');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function addDayOff($data = false, $items = false){
		if($this->db->insert("att_day_off",$data)){
			$day_off_id = $this->db->insert_id();
			foreach($items as $item){
				$item['day_off_id'] = $day_off_id;
				$this->db->insert("att_day_off_items",$item);
			}
			return true;
		}
		return false;
	}
	
	public function updateDayOff($id = false, $data = false, $items = false){
		if($this->db->update('att_day_off',$data,array('id'=>$id))){
			$this->db->delete('att_day_off_items',array('day_off_id'=>$id));
			if($items){
				$this->db->insert_batch('att_day_off_items',$items);
			}
			return true;
		}
		return false;
	}
	
	public function deleteDayOff($id = false){
		if($this->db->delete('att_day_off',array('id'=>$id))){
			$this->db->delete('att_day_off_items',array('day_off_id'=>$id));
			return true;
		}
		return false;
	}
	public function getIndexPolicyWorkingHour(){
		$this->db->select("	
							id,
							(time_to_sec( TIMEDIFF( time_out_one, time_in_one )) / 3600 ) + ( time_to_sec( TIMEDIFF( time_out_two, time_in_two )) / 3600 ) AS working_hour 
						");
		$q = $this->db->get("att_policies");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[$row->id] = $row->working_hour;
            }
            return $data;
        }
		return false;
	}
	public function getMonthlyAttendances($biller_id = false,$position_id = false,$department_id = false,$group_id = false,$employee_id = false,$month = false){
		if ($biller_id) {
			$this->db->where('hr_employees_working_info.biller_id', $biller_id);
		}
		if ($position_id) {
			$this->db->where('hr_employees_working_info.position_id', $position_id);
		}
		if ($department_id) {
			$this->db->where('hr_employees_working_info.department_id', $department_id);
		}
		if ($group_id) {
			$this->db->where('hr_employees_working_info.group_id', $group_id);
		}
		if ($employee_id) {
			$this->db->where('hr_employees.id', $employee_id);
		}
		if ($month) {
			$this->db->where('DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y")' , $month);
		}else{
			$this->db->where('DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y")' , date('m/Y'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
		}
		$this->db->select('	
							hr_employees.id,
							hr_employees.empcode,
							hr_employees.lastname,
							hr_employees.firstname,
							hr_employees.gender,
							hr_positions.name as position,
							hr_departments.name as department,
							hr_groups.name as group,
							companies.logo,
							companies.name,
							companies.city,
							companies.email,
							companies.address,
							companies.phone,
							hr_employees_working_info.policy_id,
							DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y") as month,
							SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.working_day,0)) as working_day,
							SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.present,0)) as present,
							SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.permission,0)) as permission,
							SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.absent,0)) as absent,
							SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.late,0)) as late,
							SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.leave_early,0)) as early,
						')
				->join('att_attedances','att_attedances.employee_id = hr_employees.id','inner')
				->join('hr_employees_working_info','hr_employees_working_info.employee_id = hr_employees.id','left')
				->join('hr_departments','hr_departments.id = hr_employees_working_info.department_id','left')
				->join('hr_groups','hr_groups.id = hr_employees_working_info.group_id','left')
				->join('hr_positions','hr_positions.id = hr_employees_working_info.position_id','left')
				->join('companies','companies.id = hr_employees_working_info.biller_id','left')
				->group_by('hr_employees.id');
		$q = $this->db->get("hr_employees");
		
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getIndexEmployeeAttedances($biller_id = false,$position_id = false,$department_id = false,$group_id = false,$employee_id = false,$month = false){
		if ($biller_id) {
			$this->db->where('hr_employees_working_info.biller_id', $biller_id);
		}
		if ($position_id) {
			$this->db->where('hr_employees_working_info.position_id', $position_id);
		}
		if ($department_id) {
			$this->db->where('hr_employees_working_info.department_id', $department_id);
		}
		if ($group_id) {
			$this->db->where('hr_employees_working_info.group_id', $group_id);
		}
		if ($employee_id) {
			$this->db->where('att_attedances.employee_id', $employee_id);
		}
		if ($month) {
			$this->db->where('DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y")' , $month);
		}else{
			$this->db->where('DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y")' , date('m/Y'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
		}
		$this->db->select('
							att_attedances.*,
							att_attedances.date as check_date,
							att_dailies.check_type,
							att_dailies.timeshift,
							DATE_FORMAT('.$this->db->dbprefix("att_dailies").'.check_time,"%H:%i") as check_time,
							time_to_sec('.$this->db->dbprefix("att_dailies").'.before_time) as before_time, 
							time_to_sec('.$this->db->dbprefix("att_dailies").'.after_time) as after_time, 
						');		
		$this->db->join('hr_employees_working_info','hr_employees_working_info.employee_id = att_attedances.employee_id','inner');
		$this->db->join("att_dailies","att_attedances.employee_id = att_dailies.employee_id AND att_attedances.date = date(".$this->db->dbprefix("att_dailies").".check_time)","LEFT");
		$q = $this->db->get('att_attedances');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data["check_in_out"][$row->employee_id][$row->check_date][$row->timeshift][$row->check_type] = $row->check_time;
				$data["attendances"][$row->employee_id][$row->check_date] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getIndexEmployeeOTAttedances($biller_id = false,$position_id = false,$department_id = false,$group_id = false,$employee_id = false,$month = false){
		if ($biller_id) {
			$this->db->where('hr_employees_working_info.biller_id', $biller_id);
		}
		if ($position_id) {
			$this->db->where('hr_employees_working_info.position_id', $position_id);
		}
		if ($department_id) {
			$this->db->where('hr_employees_working_info.department_id', $department_id);
		}
		if ($group_id) {
			$this->db->where('hr_employees_working_info.group_id', $group_id);
		}
		if ($employee_id) {
			$this->db->where('att_dailies_ot.employee_id', $employee_id);
		}
		if ($month) {
			$this->db->where('DATE_FORMAT('.$this->db->dbprefix("att_dailies_ot").'.date,"%m/%Y")' , $month);
		}else{
			$this->db->where('DATE_FORMAT('.$this->db->dbprefix("att_dailies_ot").'.date,"%m/%Y")' , date('m/Y'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
		}
		$this->db->select('
							att_dailies_ot.*,
							att_ot_policies.shift
						');		
		$this->db->join('hr_employees_working_info','hr_employees_working_info.employee_id = att_dailies_ot.employee_id','INNER');
		$this->db->join("att_ot_policies","att_ot_policies.id = att_dailies_ot.policy_ot_id","INNER");
		$q = $this->db->get('att_dailies_ot');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[$row->employee_id][$row->date][$row->shift] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getRoster(){
		$this->db->select('
			att_roster_calendar.employee_id,
			hr_employees.empcode,
			hr_employees.firstname,
			hr_employees.lastname,
			hr_employees.gender,
			hr_departments.name as department,
			hr_positions.name as position,
			att_roster_calendar.year,
			att_roster_calendar.month,
			att_roster.from_date,
			att_roster.to_date
		');
		$this->db->join('hr_employees','hr_employees.id = att_roster_calendar.employee_id','inner');
		$this->db->join('hr_employees_working_info','hr_employees_working_info.employee_id =hr_employees.id','INNER');
		$this->db->join('hr_positions','hr_positions.id =hr_employees_working_info.position_id','INNER');
		$this->db->join('hr_departments','hr_departments.id =hr_employees_working_info.department_id','INNER');

		$this->db->join('att_roster','att_roster.id =att_roster_calendar.roster_id','LEFT');
		

		$this->db->group_by('att_roster_calendar.employee_id,att_roster_calendar.month');
		$q = $this->db->get('att_roster_calendar');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	public function getEmployeeRoster($id,$year,$month){
		$this->db->select('att_roster_calendar.employee_id,
			att_roster_calendar.roster_code
		');
		$this->db->join('hr_employees','hr_employees.id = att_roster_calendar.employee_id','inner');
		$q = $this->db->get_where('att_roster_calendar',array("att_roster_calendar.employee_id"=>$id,'year'=>$year,'month'=>$month));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getRosterCode(){

		$q = $this->db->get('att_roster_code');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getEmployeeRosterPolicyWorkingDay($employee_id = false,$biller_id = false,$position_id = false,$department_id = false,$group_id = false){
		$this->db->select('
			hr_employees_working_info.employee_id,
			att_policies.round_min,
			att_policies.minimum_min,
			att_roster.working_day as date,
			att_roster.time_one,
			att_roster.time_two,
			att_policies.type');
		if($employee_id){
			$this->db->where('hr_employees_working_info.employee_id',$employee_id);
			$this->db->where('att_roster.employee_id',$employee_id);
		}
		if($biller_id){
			$this->db->where('hr_employees_working_info.biller_id',$biller_id);
		}
		if($position_id){
			$this->db->where('hr_employees_working_info.position_id',$position_id);
		}
		if($department_id){
			$this->db->where('hr_employees_working_info.department_id',$department_id);
		}
		if($group_id){
			$this->db->where('hr_employees_working_info.group_id',$group_id);
		}
		

		$this->db->join('att_policies','att_policies.id = att_roster.policy_id','inner');
		
		$this->db->join('hr_employees_working_info','hr_employees_working_info.id = att_roster.employee_id','inner');

	
		$q = $this->db->get('att_roster');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function addRosterCode($data = false){
		if($data){
			$this->db->insert('att_roster_code',$data);
			return true;
		}
		return false;
	}
	public function getRosterCodeByID($id = false){
		$q = $this->db->get_where('att_roster_code',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function updateRoster($id = false,$data = false){
		if($this->db->update('att_roster',$data,array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function updateRosterCode($id = false,$data = false){
		if($this->db->update('att_roster_code',$data,array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function addRoster($data = false){
		if($data){
			$this->db->insert('att_roster',$data);
			return true;
		}
		return false;
	}
	public function deleteRosterByID($id = false){
		if($this->db->delete('att_roster',array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function getRosterByID($id = false){
		$q = $this->db->get_where('att_roster',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function ImportRoster($data,$clear = false){
		if($data){
			$this->db->insert_batch('att_roster',$data);
			return true;
		}
		return false;
	}
	public function getTodayAttendance()
    {
        $sdate = !empty($this->input->post('start_date'))?$this->input->post('start_date'):date('d/m/Y');
        $ndate = explode('/',$sdate);
        $search_date = $ndate[2].'-'.$ndate[1].'-'.$ndate[0];

       //  echo '<pre>'; print_r($this->input->post()); exit;

        $this->db->select('
            bpas_users.id,
            bpas_users.emp_id,
            bpas_users.emp_code,
            bpas_users.first_name,
            bpas_users.last_name,
            bpas_users.biller_id,
            bpas_users.group_id,
            bpas_groups.name as group_name,
            bpas_attendances.shift_date,
            bpas_attendances.shift_starttime,
            bpas_attendances.shift_endtime,
            bpas_attendances.shift_total_time,
            bpas_attendances.shift_intype,
            bpas_attendances.shift_outtype,
            bpas_attendances.checkin_location,
            bpas_attendances.checkout_location');
        $this->db->join('bpas_users','bpas_users.id = bpas_attendances.user_id');
        $this->db->join('bpas_groups','bpas_groups.id = bpas_users.group_id','left');
        $this->db->where(array('bpas_attendances.shift_date' => $search_date));
      
        if (!empty($this->input->post('employee_id'))) {
            $this->db->where( "(bpas_users.first_name LIKE '%".$this->input->post('employee_id')."'  OR bpas_users.last_name LIKE '%".$this->input->post('employee_id')."')");
        }
        if (!empty($this->input->post('biller'))) {
            $this->db->where(array('bpas_users.biller_id' => $this->input->post('biller')));
        }
        if (!empty($this->input->post('group'))) {
            $this->db->where(array('bpas_users.group_id' => $this->input->post('group')));
        }
        $data = $this->db->get('bpas_attendances');

        // echo '<pre>'; print_r($data->result_array()); exit;
        $today_attendance = array();

        if($data->num_rows() > 0) {
            foreach ($data->result_array() as $key => $value) {
                $total_shift_time = 0;
                $today_attendance[$value['id']] = [
                    'id'             => $value['id'],
                    'employee_id'    => $value['emp_id'],
                    'employee_code'  => $value['emp_code'],
                    'employee_name'  => $value['first_name'].' '.$value['last_name'],
                    'group_name'     => $value['group_name'],
                    'shift_date'     => $value['shift_date'],
                ];
                foreach ($data->result_array() as $dkey => $dvalue) {
                    if ($dvalue['id'] == $value['id']) {
                        if($dvalue['shift_total_time'] > 0) {
                            $hours = floor($dvalue['shift_total_time'] / 60);
                            $min = $dvalue['shift_total_time'] - ($hours * 60);
                            $total_work = $hours.'hr '.$min.'min';
                            
                        } else {
                            $total_work = '00hr 00min';
                        }
                        $today_attendance[$value['id']][$value['shift_date']][$dkey] = [
                            'shift_start_time'  => $dvalue['shift_starttime'],
                            'checkin_location'  => json_decode($dvalue['checkin_location']),
                            'shift_end_time'    => ($dvalue['shift_endtime']!='0000-00-00 00:00:00')?$dvalue['shift_endtime']:'',
                            'checkout_location'  => json_decode($dvalue['checkout_location']),
                            'shift_total_time'  => $total_work,
                            'shift_intype'      => $dvalue['shift_intype'],
                            'shift_outtype'      => $dvalue['shift_outtype'],
                        ];
                        $total_shift_time += $dvalue['shift_total_time']??0;
                    }
                }
                if($total_shift_time > 0) {
                    $hours = floor($total_shift_time / 60);
                    $min = $total_shift_time - ($hours * 60);
                    $total_shift_work = $hours.'hr '.$min.'min';
                    
                } else {
                    $total_shift_work = '00hr 00min';
                }
                $today_attendance[$value['id']]['total_work_time'] = $total_shift_work;
            }
        }

        // echo '<pre>'; print_r($today_attendance); exit;

        if($data->num_rows() > 0){
            return $today_attendance;
		}
		return false;
    }

    public function deleteOT($id = false){
		if($this->db->delete('att_apply_ot',array('id'=>$id))){
			//$this->db->delete('att_day_off_items',array('day_off_id'=>$id));
			return true;
		}
		return false;
	}
	public function getApplyOtByID($id = false){
		$q = $this->db->get_where("att_apply_ot",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		} 
		return false;
	}
	public function getApplyOtItems($ot_id = false){
		if($ot_id){
			$this->db->where('att_apply_ot.id',$ot_id);
		}
		//$this->db->select('att_day_off_items.*,hr_employees.empcode,hr_employees.firstname,hr_employees.lastname');
		$this->db->join('hr_employees','hr_employees.id = att_apply_ot.employee_id','inner');
		$q = $this->db->get('att_apply_ot');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	//-------------------take leave----------------
	public function getUsedLeaveByEmployee($employee_id = false, $category_id = false, $year = false){		
		$this->db->where("hr_employees_working_info.employee_id",$employee_id);
		$this->db->where("hr_leave_categories.id",$category_id);
		$this->db->select("
							hr_leave_categories.code as category_code,
							IF(".$this->db->dbprefix('hr_leave_categories').".`code` = 'annual_leave', ".$this->db->dbprefix('hr_employees_working_info').".`annual_leave`,
							IF(".$this->db->dbprefix('hr_leave_categories').".`code` = 'special_leave', ".$this->db->dbprefix('hr_employees_working_info').".`special_leave`,
							IF(".$this->db->dbprefix('hr_leave_categories').".`code` = 'sick_leave', ".$this->db->dbprefix('hr_employees_working_info').".`sick_leave`,".$this->db->dbprefix('hr_employees_working_info').".`other_leave`))) AS total_leave,
							IFNULL(employee_leaves.used_leave,0) as used_leave
						");
		$this->db->join("hr_leave_categories","hr_leave_categories.id = ".$category_id,"INNER");
		$this->db->join('(SELECT 
							'.$this->db->dbprefix('att_take_leave_employees').'.employee_id,
							SUM(IF('.$this->db->dbprefix('att_take_leave_employees').'.timeshift = "full",1,0.5)) as used_leave
						FROM
							'.$this->db->dbprefix('att_take_leave_employees').'
							INNER JOIN '.$this->db->dbprefix('att_take_leaves').' ON '.$this->db->dbprefix('att_take_leaves').'.id = '.$this->db->dbprefix('att_take_leave_employees').'.take_leave_id
						WHERE
							'.$this->db->dbprefix('att_take_leaves').'.status = 1 
							AND '.$this->db->dbprefix('att_take_leave_employees').'.leave_category_id = '.$category_id.'
							AND '.$this->db->dbprefix('att_take_leave_employees').'.employee_id = '.$employee_id.'
							AND YEAR('.$this->db->dbprefix('att_take_leave_employees').'.date) = "'.$year.'"
						) as employee_leaves','hr_employees_working_info.employee_id = employee_leaves.employee_id','LEFT');
		$q = $this->db->get("hr_employees_working_info");
		if($q->num_rows() > 0){
			return $q->row();
		} 
		return false;
	}
	
	public function getMonthlyUsedLeaveByEmployee($employee_id = false, $category_id = false, $year = false, $month = false){		
		$this->db->where("hr_employees_working_info.employee_id",$employee_id);
		$this->db->where("hr_leave_categories.id",$category_id);
		$this->db->select("
							hr_leave_categories.code as category_code,
							IF(".$this->db->dbprefix('hr_leave_categories').".`code` = 'annual_leave', ".$this->db->dbprefix('hr_employees_working_info').".`annual_leave`,
							IF(".$this->db->dbprefix('hr_leave_categories').".`code` = 'special_leave', ".$this->db->dbprefix('hr_employees_working_info').".`special_leave`,
							IF(".$this->db->dbprefix('hr_leave_categories').".`code` = 'sick_leave', ".$this->db->dbprefix('hr_employees_working_info').".`sick_leave`,".$this->db->dbprefix('hr_employees_working_info').".`other_leave`))) AS total_leave,
							IFNULL(employee_leaves.used_leave,0) as used_leave
						");
		$this->db->join("hr_leave_categories","hr_leave_categories.id = ".$category_id,"INNER");
		$this->db->join('(SELECT 
							'.$this->db->dbprefix('att_take_leave_employees').'.employee_id,
							SUM(IF('.$this->db->dbprefix('att_take_leave_employees').'.timeshift = "full",1,0.5)) as used_leave
						FROM
							'.$this->db->dbprefix('att_take_leave_employees').'
							INNER JOIN '.$this->db->dbprefix('att_take_leaves').' ON '.$this->db->dbprefix('att_take_leaves').'.id = '.$this->db->dbprefix('att_take_leave_employees').'.take_leave_id
						WHERE
							'.$this->db->dbprefix('att_take_leaves').'.status = 1 
							AND '.$this->db->dbprefix('att_take_leave_employees').'.leave_category_id = '.$category_id.'
							AND '.$this->db->dbprefix('att_take_leave_employees').'.employee_id = '.$employee_id.'
							AND YEAR('.$this->db->dbprefix('att_take_leave_employees').'.date) = "'.$year.'"
							AND MONTH('.$this->db->dbprefix('att_take_leave_employees').'.date) = "'.$month.'"
						) as employee_leaves','hr_employees_working_info.employee_id = employee_leaves.employee_id','LEFT');
		$q = $this->db->get("hr_employees_working_info");
		if($q->num_rows() > 0){
			return $q->row();
		} 
		return false;
	}
}
?>