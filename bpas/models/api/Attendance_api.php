<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Attendance_api extends CI_Model
{
    public function get_shifts($user_id,$from_date,$to_date)
    {
        $this->db->select('*');
        $this->db->where('user_id',$user_id);
        $this->db->where('shift_status',2);
        $this->db->where('shift_date >= ',$from_date);
        $this->db->where('shift_date <= ',$to_date);
        $query = $this->db->get('attendances');
		return $result = $query->result_array();
    }

    public function get_nonclosed_shift(){ 
        $starttime  = date("Y-m-d H:i:s", strtotime("-14 hours"));
        $this->db->select('*');
        $this->db->where('shift_endtime','0000-00-00 00:00:00');
        $this->db->where('shift_starttime >= ',$starttime);
        $this->db->where('shift_status = 1');
        $query = $this->db->get('attendances'); 
        $result = $query->result_array();
        return $result;         
    }


    public function api_get_shifts_by_date($user_id,$date)
    {
        $this->db->select("*");
        $this->db->where('user_id',$user_id);
        $this->db->where('shift_date',$date);
        $this->db->where('shift_status','2');
        $query = $this->db->get('attendances');
		return $result = $query->result_array();
        
    }

    public function api_get_current_shift($user_id,$date)
    {
        $this->db->select("*");
        $this->db->where('user_id',$user_id);
        $this->db->where('shift_date',$date);
        $this->db->where('shift_status','1');
        $query = $this->db->get('attendances');
		return (array) $result = $query->row();
    }

    public function api_get_active_shift($user_id)
    {
        $this->db->select("*");
        $this->db->where('user_id',$user_id); 
        $this->db->where('shift_status','1');
        $query = $this->db->get('attendances');
        return (array) $result = $query->row();
    }

    public function add_shift($data)
    {
        $add = $this->db->insert('attendances', $data);
        if($add)
        {
            $add_shift = $this->db->insert_id();
            $this->db->insert('att_check_in_out', ['employee_id' => $data['employee_id'], 'check_time' => $data['shift_starttime']]);
            return $add_shift;
        }
    }

    public function get_shift_by_id($id)
    {
        $this->db->select("*");
        $this->db->where('id',$id);
        $query = $this->db->get('attendances');
		return (array) $result = $query->row();
    }

    public function update_shift($shift_id, $data)
    {
        $this->db->trans_start();
            $this->db->where('id', $shift_id);
            $this->db->insert('att_check_in_out', ['employee_id' => $data['employee_id'], 'check_time' => $data['shift_endtime']]);
            $this->db->update('attendances', $data, ['id'=> $shift_id]);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the attedance (update_shift:Attendance_api.php)');
        } else {
            return true;
        }
        return false;
    }

    public function get_checked_in_shift($user_id)
    {
        if($user_id!=null)
        {
            $this->db->select("*");
            $this->db->where('shift_status','1');
            $this->db->where('user_id',$user_id);
            $query = $this->db->get('attendances');
            return (array) $result = $query->row();
        }
    }

    public function get_minutes_sum($start_date,$end_date,$user_id)
    {
        if($start_date!=null && $end_date!=null)
        {
            $this->db->select_sum('shift_total_time');
            $this->db->where('shift_date >= ',date('Y-m-d',strtotime($start_date)));
            $this->db->where('shift_date <= ',date('Y-m-d',strtotime($end_date)));
            $this->db->where('user_id >= ',$user_id);
            $query = $this->db->get('attendances');
            $result = (array) $query->row();
            
            return $result['shift_total_time']??0;
        }
    }

    public function get_mothly_data($start_date,$end_date,$user_id)
    {
        if($start_date!=null && $end_date!=null)
        {  
            $this->db->select("*");
            $this->db->where('shift_date >=',$start_date);
            $this->db->where('shift_date <=',$end_date);
            $this->db->where('user_id',$user_id);
            $this->db->order_by('shift_date asc, shift_starttime asc');
            $query = $this->db->get('attendances');
            return $query->result_array();
        }
    }

    public function get_shift_by_date($user_id, $date, $end_date=null)
    {
        $this->db->select("*");
        $this->db->where('user_id',$user_id);
        $this->db->order_by('shift_date asc, shift_starttime asc');
        if($end_date==null)
        {

            $this->db->where('shift_date',date('Y-m-d',strtotime($date)));
        }
        else
        {
        $this->db->where('shift_date >=',date('Y-m-d',strtotime($date)));
        $this->db->where('shift_date <=',date('Y-m-d',strtotime($end_date)));
        }
        $query = $this->db->get('attendances');
        return $query->result_array();

    }
    public function addTakeLeave($data = false, $dataDetails = false){
        if($data){
            $this->db->insert('att_take_leaves',$data);
            $take_leave_id = $this->db->insert_id();
            if($dataDetails){
                $dataDetails['take_leave_id'] = $take_leave_id;
                $this->db->insert('att_take_leave_details',$dataDetails);
                
            }
            return true;
        }
        return false;
    }
    public function addDayOff($data = false, $items = false){
        if($this->db->insert("att_day_off",$data)){
            $day_off_id = $this->db->insert_id();
        
            $items['day_off_id'] = $day_off_id;
            $this->db->insert("att_day_off_items",$items);
            
            return true;
        }
        return false;
    }

    public function getEmployeeAllTakeLeave($employee_id = false,$start_date = false,$end_date = false){
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
        } else if ($start_date) {
            $where .=" AND '".$start_date."' >= ".$this->db->dbprefix('att_take_leave_details').".start_date AND '".$start_date."' <= ".$this->db->dbprefix('att_take_leave_details').".end_date";
        } else if ($end_date) {
            $where .=" AND '".$end_date."' >= ".$this->db->dbprefix('att_take_leave_details').".start_date AND '".$end_date."' <= ".$this->db->dbprefix('att_take_leave_details').".end_date";
        }
        $q = $this->db->query("
                SELECT
                    {$this->db->dbprefix('hr_employees')}.id as employee_id,
                    concat({$this->db->dbprefix('hr_employees')}.lastname,' ',{$this->db->dbprefix('hr_employees')}.firstname) as employee,
                    concat({$this->db->dbprefix('hr_employees')}.lastname_kh,' ',{$this->db->dbprefix('hr_employees')}.firstname_kh) as employee_kh,
                    {$this->db->dbprefix('att_take_leave_details')}.leave_type,
                    {$this->db->dbprefix('att_take_leave_details')}.start_date,
                    {$this->db->dbprefix('att_take_leave_details')}.end_date,
                    {$this->db->dbprefix('att_take_leave_details')}.timeshift,
                    {$this->db->dbprefix('hr_leave_types')}.name as leave_name,
                    {$this->db->dbprefix('att_take_leave_details')}.reason as reason,
                    IF (
                        {$this->db->dbprefix('att_take_leaves')}.status = 0, 'Pending',
                        IF (
                            {$this->db->dbprefix('att_take_leaves')}.status = 1, 'Approved', 'Rejected'
                        )
                    ) AS status
                FROM
                    ".$this->db->dbprefix('att_take_leaves')."
                INNER JOIN ".$this->db->dbprefix('att_take_leave_details')." ON ".$this->db->dbprefix('att_take_leave_details').".take_leave_id = ".$this->db->dbprefix('att_take_leaves').".id
                INNER JOIN ".$this->db->dbprefix('hr_leave_types')." ON ".$this->db->dbprefix('hr_leave_types').".id = ".$this->db->dbprefix('att_take_leave_details').".leave_type
                INNER JOIN ".$this->db->dbprefix('hr_employees')." ON ".$this->db->dbprefix('hr_employees').".id = ".$this->db->dbprefix('att_take_leave_details').".employee_id

        
                WHERE
                    ".$this->db->dbprefix('att_take_leaves').".`id` > 0
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

    public function getEmployeeAllDayOffs($employee_id = false, $start_date = false, $end_date = false)
    {
        if($employee_id){
            $this->db->where("att_day_off_items.employee_id",$employee_id);
        }
        if($start_date){
            $this->db->where("att_day_off_items.day_off >=",$start_date);
        }
        if($end_date){
            $this->db->where("att_day_off_items.day_off <=",$end_date);
        }
		$this->db->join('hr_employees', 'hr_employees.id = att_day_off_items.employee_id', 'inner');

        $q = $this->db->get("att_day_off_items");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;        
    }

    public function getLeaveType()
    {
        $q = $this->db->get('hr_leave_types');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getCheckIn_CheckOut($employee_id = null)
    {

        $this->db->select('
            att_check_in_out.id as id,
            hr_employees.id as employee_id,
            empcode,
            concat('.$this->db->dbprefix('hr_employees').'.lastname," ",'.$this->db->dbprefix('hr_employees').'.firstname) as full_name, check_time')
            ->join('hr_employees','hr_employees.id = att_check_in_out.employee_id','inner')
            ->group_by('att_check_in_out.id');
        if ($employee_id) {
            $this->db->where('hr_employees.id', $employee_id);
        }
        $q = $this->db->get('att_check_in_out');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
}