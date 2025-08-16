<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Table_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

	public function getAll_suspend_note($floor_id = null, $warehouse_id = null)
    {
        $this->db->select()
            ->join('suspended_bills', 'suspended_note.note_id = suspended_bills.suspend_note', 'left');
		$this->db->order_by('suspended_note.floor','ASC');
        if($warehouse_id){
            if($floor_id != -1){
                $q = $this->db->get_where('suspended_note',array('suspended_note.warehouse_id'=>$warehouse_id,'suspended_note.floor'=>$floor_id));
            }else{
                $q = $this->db->get_where('suspended_note',array('suspended_note.warehouse_id'=>$warehouse_id));
            }
		}else{
            if($floor_id != -1){
			    $q = $this->db->get_where('suspended_note',array('suspended_note.floor'=>$floor_id ));
            }else{
			    $q = $this->db->get('suspended_note');
            }
		}
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function available_room($warehouse_id=null)
    {
        $this->db->select()->join('suspended_bills', 'suspended_note.note_id = suspended_bills.suspend_note', 'left');
        $this->db->order_by('suspended_note.note_id','ASC');
        $this->db->where('suspended_note.status',0);
        if ($warehouse_id) {
            $q = $this->db->get_where('suspended_note',array(
            //  'id'=>null,
                'suspended_note.warehouse_id'=>$warehouse_id
            //  'booking' => ""
                )
            );
        } else {
            $q = $this->db->get('suspended_note');
            /*
            $q = $this->db->get_where('suspended_note',array(
                'id'=>null,
                'booking' => "")
            );*/
        }

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	/*public function getAll_suspend_note() {
    //    $this->db->order_by('date','ASC');
		$q = $this->db->get("suspended_note");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }*/

    public function addRoom($data, $options = null)
    {   
        if ($this->db->insert("suspended_note", $data)) {
            $suspended_note_id = $this->db->insert_id();
            if (!empty($options)) {
                foreach ($options as $option) {
                    $option['suspended_note_id'] = $suspended_note_id;
                    $this->db->insert('suspended_note_options', $option);
                }
            }
            return true;
        }
        return false;
    }

    public function updateRoom($id, $data = array(), $options = null)
    {
        $this->db->where('note_id', $id);
        if ($this->db->update("suspended_note", $data)) {
            if (!empty($options)) {
                foreach ($options as $option) {
                    if ($room_option = $this->getRoomOption($id, $option['custom_field_id'])) {
                        $this->db->update('suspended_note_options', ['price' => $option['price']], ['id' => $room_option->id]);
                    } else {
                        $this->db->insert('suspended_note_options', $option);
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function delete_room($id)
    {
        if ($this->db->delete('suspended_note', array('note_id' => $id))) {
            $this->db->delete('suspended_note_options', array('suspended_note_id' => $id));
            return true;
        }
        return false;
    } 

	public function getAll_suspend_bill() 
    {
        $q = $this->db->get("suspended_bills");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function get_room_number($sus_id)
    {
        $this->db->select('suspended_note.note_id,suspended_note.name,suspended_note.customer_qty,suspended_note.tmp');
        $this->db->join('suspended_bills','suspended_note.note_id = suspended_bills.suspend_note');
        $q = $this->db->get_where('suspended_note',array('suspended_bills.id' => $sus_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function get_sus_id_byroom($sus_id)
    {
        $this->db->select('id');
		$q = $this->db->get_where('suspended_bills',array('suspend_note' => $sus_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
     public function getSuspendnoteById($sus_id)
    {
        $this->db->select('name');
		$q = $this->db->get_where('suspended_note',array('note_id' => $sus_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAll_suspend_notetmp()
    {
        $this->db->select('suspended_note.note_id,suspended_note.name');
        $this->db->join('suspended_bills','suspended_note.note_id = suspended_bills.suspend_note');
        $q = $this->db->get_where('suspended_note');
        
        if ($q->num_rows() > 0) {
             foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getsuspend_note()
    {
        $this->db->select('suspended_note.*,suspended_note.note_id as id,floors.name as floor_name', false)
            ->join('floors', 'floors.id=suspended_note.floor', 'left')
            ->where('suspended_note.status', 0);        
            $this->db->group_by('suspended_note.note_id');

        $q = $this->db->get('suspended_note');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getSuspend_NoteByID($id)
    {
        $this->db->select('suspended_note.*,suspended_note.note_id as id,floors.name as floor_name', false);
        $this->db->join('floors', 'floors.id=suspended_note.floor', 'left');
        $this->db->where('suspended_note.note_id', $id);
        $this->db->group_by('suspended_note.note_id');
        $this->db->limit(1);
        $q = $this->db->get('suspended_note');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getroomByID($id)
    {
        $q = $this->db->get_where('suspended_note', array('note_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAssignByID($id)
    {
        $q = $this->db->get_where('suspended_assign', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function addAssignRoom($data) 
    {
        if ($this->db->insert("suspended_assign", $data)) {
            return true;
        }
        return false;
    }
    public function updateAssign($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update("suspended_assign", $data)) {
            return true;
        }
        return false;
    }
    public function delete_Assign($id){
        if ( $this->db->delete('suspended_assign', array('id' => $id))) {
            return true;
        }
        return false;
    } 

    public function getRoomOptionsByRoomID($id) 
    {
        $this->db->select('suspended_note_options.*, custom_field.name as custom_field_name');
        $this->db->from('suspended_note_options');
        $this->db->join('custom_field', 'custom_field.id = suspended_note_options.custom_field_id', 'inner');
        $this->db->where('suspended_note_id', $id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }    

    public function getRoomOption($room_id, $option_id) 
    {
        $this->db->select('suspended_note_options.*, custom_field.name as custom_field_name');
        $this->db->from('suspended_note_options');
        $this->db->join('custom_field', 'custom_field.id = suspended_note_options.custom_field_id', 'inner');
        $this->db->where('suspended_note_id', $room_id);
        $this->db->where('custom_field_id', $option_id);
        $this->db->limit(1);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getAllAvailbleCards()
    {
        $this->db->select();
        $q = $this->db->get('member_cards');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllCheckinCard()
    {
        $this->db->select();
        
        $this->db->where('suspend_note',1);
        $q = $this->db->get('member_cards');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function getMemberCardByID($id)
    {
        $q = $this->db->get_where('member_cards', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllSuspendtable()
    {
        $this->db->select();
        $q = $this->db->get('suspended_note');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
}