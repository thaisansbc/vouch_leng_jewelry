<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Calendar_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function addEvent($data = [])
    {
        // var_dump($data);
        // exit(0);
        if ($this->db->insert('calendar', $data)) {
            $calendar_id = $this->db->insert_id();
            return true;
        }
        return false;
    }
    public function deleteEvent($id)
    {
        if ($this->db->delete('calendar', ['id' => $id])) {
            return true;
        }
        return false;
    }

    // public function getEventByID($id)
    // {
    //     $this->db->select('calendar.*, custom_field.*');
    //         $this->db->from('calendar');
    //         $this->db->join('custom_field', 'calendar.event_type = custom_field.id', 'left');
    //         $this->db->where('calendar.id', $id);
    //         $this->db->limit(1);
    //         $query = $this->db->get();

    //         if ($query->num_rows() > 0) {
    //             return $query->row();
    //         }
    //         return false;
    // }

    public function updatePhoto($id)
    {

        $data = array('photo' => NULL);
        if ($this->db->update('calendar', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getEventByID($id){
        $this->db->select('calendar.*, custom_field.name');
        $this->db->from('calendar');
        $this->db->join('custom_field', 'calendar.event_type = custom_field.id', 'left');
        $this->db->where('calendar.id', $id);
        // $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }


    public function getCalendarScheduleByID($id)
    {
        $q = $this->db->get_where('event_schedule', ['id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    
    public function getscheduleByStatus($status)
    {
        $q = $this->db->get_where('event_schedule', ['status' => $status]);
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }


    public function getTicketByStatus($status)
    {
        $q = $this->db->get_where('event_tickets', ['status' => $status]);
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getAllTicket()
    {
        $this->db->where('status !=', 'used');
        $q = $this->db->get_where('event_tickets');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }


        
    public function getEvents($start, $end)
    {
        $this->db->select('id, title, start, end, description, color,customer,assign_to,status');
        $this->db->where('start >=', $start)->where('start <=', $end);
        if ($this->Settings->restrict_calendar) {
            $this->db->where('user_id', $this->session->userdata('user_id'));
        }

        $q = $this->db->get('calendar');

        if ($q->num_rows() > 0) {
            foreach (($q->result_array()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getEventByIDUpdate($id)
    {
        $q = $this->db->get_where('calendar', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getscheduleByID($id)
    {
        $q = $this->db->get_where('event_schedule', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
   


    public function updateEvent($id, $data = [])
    {
        if ($this->db->update('calendar', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateschedule($id, $data = [])
    {
       
        if ($this->db->update('event_schedule', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function updatescheduleStatus($id, $status)
    {
        $data = array('status' => $status);
        if ($this->db->update('event_schedule', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateTicketStatus($id, $status)
    {
        $data = array('status' => $status);
        if ($this->db->update('event_tickets', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }


    public function addEventToDo($data = array())
    {
        if ($this->db->insert('calendar', $data)) {
            return true;
        }
        return false;
    }
    public function addCalendar($data = array())
    {
        if($data){
            foreach($data as $row){
                $row['holiday'] = 1;
                $row['user_id'] = $this->session->userdata("user_id");
                $this->db->insert("calendar", $row);
            }
            return true;
        }
        return false;
    }
    public function updateEventStatus($id = false, $status = false){
        if($id && $this->db->update("calendar",array("status"=>$status),array("id"=>$id))){
            return true;
        }
        return false;
    }

    public function addEventSchedule($data = [])
    {
        // var_dump($data);
        // exit(0);
        if ($this->db->insert('event_schedule', $data)) {
            return true;
        }
        return false;
    }
    public function deleteScheduleEvent($id)
    {
        if ($this->db->delete('event_schedule', ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function getAllSchedule()
    {
        $this->db->where('status', 'pending'); 
        $q = $this->db->get('event_schedule');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllScheduleEdit()
    {
        $q = $this->db->get('event_schedule');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllCalendar()
    {
       
        $q = $this->db->get('calendar');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function addTicket($data = array())
    {
        // var_dump($data);exit(0);
        if ($this->db->insert('event_tickets', $data)) {
            return true;
        }
        return false;
    }
    public function updateTicket($id, $data = [])
    {
       
        if ($this->db->update('event_tickets', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function deleteTicket($id)
    {
        if ($this->db->delete('event_tickets', ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function getTicketByID($no)
    {
        $q = $this->db->get_where('event_tickets', ['id' => $no], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getTicketByCode($no)
    {
        $q = $this->db->get_where('event_tickets', ['code' => $no], 1);

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
}
