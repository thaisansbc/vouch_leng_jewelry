<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Clinic_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function addProgressNote($data, $accTrans = array())
    {
        foreach ($data as $item) {
            $this->db->insert('progress_note', $item);
            $expense_id = $this->db->insert_id();
        }
        //$this->site->updateReference('ex');
        return true;
    }
    public function getProgressNoteByID($id)
    {
        $q = $this->db->get_where('progress_note', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function deleteProgressNoteByID($id)
    {
        if ($this->db->delete('progress_note', ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function updateProgressNote($id, $data)
    {
        if ($this->db->update('progress_note', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function addBirthRecord($data = [])
    {
        if ($this->db->insert('companies', $data)) {
            $cid = $this->db->insert_id();
            return $cid;
        }
        return false;
    }
    public function deleteBirth($id)
    {
        if ($this->db->delete('companies', ['id' => $id, 'group_name' => 'birth'])) {
            return true;
        }
        return false;
    }
    public function deletedeath($id)
    {
        if ($this->db->delete('companies', ['id' => $id, 'group_name' => 'death'])) {
            return true;
        }
        return false;
    }
    public function getParentOperationCategories()
    {
        $this->db->where('clinic_op_categories.status', 'show');
        $this->db->group_start();
        $this->db->where('parent_id', null);
        $this->db->or_where('parent_id', 0);
        $this->db->group_end();
        $q = $this->db->get('clinic_op_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addOperationCategory($data)
    {
        if ($this->db->insert('clinic_op_categories', $data)) {
            return true;
        }
        return false;
    }
    public function updateOperationCategory($id, $data = [])
    {
        if ($this->db->update('clinic_op_categories', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function deleteOperationCategory($id)
    {
        if (!empty($id) && $id != '') {
            if ($this->db->delete('clinic_op_categories', ['id' => $id])) {
                return true;
            }
        }
        return false;
    }
    public function getOperationCategoryByID($id)
    {
        $q = $this->db->get_where('clinic_op_categories', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getOperationCategoriesByParent($id) 
    {
        $q = $this->db->get_where('clinic_op_categories', ['parent_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addOperation($data)
    {
        if ($this->db->insert('clinic_operations', $data)) {
            return true;
        }
        return false;
    }
    public function updateOperation($id, $data = [])
    {
        if ($this->db->update('clinic_operations', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function deleteOperation($id)
    {
        if (!empty($id) && $id != '') {
            if ($this->db->delete('clinic_operations', ['id' => $id])) {
                return true;
            }
        }
        return false;
    }
    public function getOperationByID($id)
    {
        $q = $this->db->get_where('clinic_operations', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getMedicationNameByCategory($id) 
    {
        $q = $this->db->get_where('products', ['category_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addMedicationDose($data)
    {
        if ($this->db->insert('clinic_medication_dose', $data)) {
            return true;
        }
        return false;
    }
    public function updateMedicationDose($id, $data = [])
    {
        if ($this->db->update('clinic_medication_dose', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function deleteMedicationDose($id)
    {
        if (!empty($id) && $id != '') {
            if ($this->db->delete('clinic_medication_dose', ['id' => $id])) {
                return true;
            }
        }
        return false;
    }
    public function getMedicationDoseByID($id)
    {
        $q = $this->db->get_where('clinic_medication_dose', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getMedicineCategoryByID($id)
    {
        $q = $this->db->get_where('categories', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getPathologyByID($id)
    {
        $q = $this->db->get_where('clinic_pathology', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function addPathology($data)
    {
        if ($this->db->insert('clinic_pathology', $data)) {
            return true;
        }
        return false;
    }
    public function updatePathology($id, $data = [])
    {
        if ($this->db->update('clinic_pathology', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function deletePathology($id)
    {
        if (!empty($id) && $id != '') {
            if ($this->db->delete('clinic_pathology', ['id' => $id])) {
                return true;
            }
        }
        return false;
    }
    public function getIpdOpdByID($id,$type=null)
    {
        if($type){
            $this->db->where('clinic_ipd_opd.patience_type',$type);
        }
        $q = $this->db->get_where('clinic_ipd_opd', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function addOPD($data = [], $items = [])
    {
        $this->db->trans_start();
        if ($this->db->insert('clinic_ipd_opd', $data)) {
            $sale_id = $this->db->insert_id();
            if ($this->site->getReference('sr') == $data['reference_no']) {
                $this->site->updateReference('sr');
            }
            // if (isset($data['bed_id']) && !empty($data['bed_id'])) {
            //     $this->db->update('suspended_note', ['status' => 1], ['note_id' => $data['bed_id']]);
            // }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Sales_model.php)');
        } else {
            return $sale_id;
        }

        return false;
    }
    public function deleteOpd($id)
    {
        if (!empty($id) && $id != '') {
            if ($this->db->delete('clinic_ipd_opd', ['id' => $id])) {
                return true;
            }
        }
        return false;
    }
    public function getOpdByID($id)
    {
        $q = $this->db->get_where('clinic_ipd_opd', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function updateOPD($id, $data)
    {
        if ($this->db->update('clinic_ipd_opd', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function addConsult($data = [], $items = [], $payment = [], $si_return = [])
    {
        $this->db->trans_start();
        if ($this->db->insert('sales_order', $data)) {
            $sale_id = $this->db->insert_id();
            if ($this->site->getReference('sr') == $data['reference_no']) {
                $this->site->updateReference('sr');
            }
            foreach ($items as $item) {
                $item['sale_order_id'] = $sale_id;
                $this->db->insert('sale_order_items', $item);   
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
    public function getPrescriptionByID($id)
    {
        $q = $this->db->get_where('prescription', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getPrescriptionItems($quote_id)
    {
        $this->db->select('prescription_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.image, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name, products.type as product_type')
            ->join('products', 'products.id=prescription_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=prescription_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=prescription_items.tax_rate_id', 'left')
            ->group_by('prescription_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('prescription_items', ['sale_id' => $quote_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
}