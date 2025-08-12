<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales_order_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addDelivery($data = [])
    {
        if ($this->db->insert('deliveries', $data)) {
            if ($this->site->getReference('do') == $data['do_reference_no']) {
                $this->site->updateReference('do');
            }
            return true;
        }
        return false;
    }

    /* ----------------- Gift Cards --------------------- */

    public function addGiftCard($data = [], $ca_data = [], $sa_data = [])
    {
        if ($this->db->insert('gift_cards', $data)) {
            if (!empty($ca_data)) {
                $this->db->update('companies', ['award_points' => $ca_data['points']], ['id' => $ca_data['customer']]);
            } elseif (!empty($sa_data)) {
                $this->db->update('users', ['award_points' => $sa_data['points']], ['id' => $sa_data['user']]);
            }
            return true;
        }
        return false;
    }

    public function addOptionQuantity($option_id, $quantity)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('product_variants', ['quantity' => $nq], ['id' => $option_id])) {
                return true;
            }
        }
        return false;
    }

    public function addPayment($data = [], $customer_id = null)
    {
        if ($this->db->insert('payments', $data)) {
            if ($this->site->getReference('pay') == $data['reference_no']) {
                $this->site->updateReference('pay');
            }
            $this->site->syncSalePayments($data['sale_id']);
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', ['balance' => ($gc->balance - $data['amount'])], ['card_no' => $data['cc_no']]);
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount - $data['amount'])], ['id' => $customer_id]);
            }
            return true;
        }
        return false;
    }

    public function addSale($data = [], $items = [], $payment = [], $si_return = [])
    {
        $this->db->trans_start();
        if ($this->db->insert('sales_order', $data)) {
            $sale_id = $this->db->insert_id();
            $item['option_id']   = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
            if ($this->site->getReference('sr') == $data['reference_no']) {
                $this->site->updateReference('sr');
            }
            if (isset($data['bed_id']) && !empty($data['bed_id'])) {
                $this->db->update('suspended_note', ['status' => 1], ['note_id' => $data['bed_id']]);
            }
            foreach ($items as $item) {
                $item['sale_order_id'] = $sale_id;
                $this->db->insert('sale_order_items', $item);   
            }
            // $this->site->syncQuantity($sale_id);
            // $this->bpas->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Sales_model.php)');
        } else {
            return $sale_id;
        }

        return false;
    }

    public function deleteDelivery($id)
    {
        if ($this->db->delete('deliveries', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteGiftCard($id)
    {
        if ($this->db->delete('gift_cards', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deletePayment($id)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->delete('payments', ['id' => $id])) {
            $this->site->syncSalePayments($opay->sale_id);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', ['balance' => ($gc->balance + $opay->amount)], ['card_no' => $opay->cc_no]);
            } elseif ($opay->paid_by == 'deposit') {
                $sale     = $this->getInvoiceByID($opay->sale_id);
                $customer = $this->site->getCompanyByID($sale->customer_id);
                $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount + $opay->amount)], ['id' => $customer->id]);
            }
            return true;
        }
        return false;
    }

    public function deleteSale($id)
    {
        $this->db->trans_start();
        $sale_items = $this->resetSaleActions($id);
        if ($this->db->delete('sale_order_items', ['sale_order_id' => $id]) && 
            $this->db->delete('sales_order', ['id' => $id])) {

           $this->db->delete('sales_order', ['sale_id' => $id]);
        //    $this->db->delete('payments', ['sale_id' => $id]);
        //    $this->site->syncQuantity(null, null, $sale_items);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Delete:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function getAllGCTopups($card_id)
    {
        $this->db->select("{$this->db->dbprefix('gift_card_topups')}.*, {$this->db->dbprefix('users')}.first_name, {$this->db->dbprefix('users')}.last_name, {$this->db->dbprefix('users')}.email")
        ->join('users', 'users.id=gift_card_topups.created_by', 'left')
        ->order_by('id', 'desc')->limit(10);
        $q = $this->db->get_where('gift_card_topups', ['card_id' => $card_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getsuspendNoteByID($room_id)
    {
        $this->db->select('suspended_note.*,suspended_note.note_id as id,floors.name as floor_name', false)
            ->join('floors', 'floors.id=suspended_note.floor', 'left')
            ->where('suspended_note.status', 0)
            ->where('note_id', $room_id);
        $q = $this->db->get('suspended_note');
        if ($q->num_rows() > 0) {
            $data[] = $q->row();
            return $data;
        }
        return false;
    }

    public function getTicket($ticket)
    {
        $j = sizeof($ticket);
        $arr_0 = [];
        $arr   = [];
        $arr_1 = [];
        for ($i = 0; $i < $j; $i++) { 
            $arr = $ticket[$i];
            $arr_0[] = $arr[0];
            $arr_1[] = $arr[1]; 
        }
        $this->db->select('suspended_note.*, suspended_note.note_id as id, floors.name as floor_name', false)
            ->join('floors', 'floors.id = suspended_note.floor') 
            ->where('suspended_note.status', 0) 
            ->where_in('note_id', $ticket); 
        $q = $this->db->get('suspended_note');
        $data = [];
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                foreach ($ticket as $key => $value) {
                    $h = explode('__', $value);
                    if (($h[1] . "@" . $h[0]) == ($h[1] . "@" . $row->id)) { 
                        $row->timeout_id = $h[1];
                        $data[] = $row;
                    }
                }
            }
            return $data;
        }
        return false;
    }

    public function getTicket_($ticket)
    { 
        $this->db->select('suspended__note.*,suspended_note.note_id as id,floors.name as floor_name', false)
            // ->join('floors', 'floors.id=suspended_note.floor')
            // ->where('suspended_note.status', 0)
            ->where_in('note_id', $ticket);
        $q = $this->db->get('suspended_note');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            } var_dump($ticket);
        exit();
            return $data;
        }
        return false;
    }

    public function getsuspend_note___($term, $warehouse_id, $pos = false, $limit = 15)
    {
        $this->db->select('suspended_note.*,suspended_note.note_id as id,floors.name as floor_name', false)
            ->join('floors', 'floors.id=suspended_note.floor', 'left')
            ->where('suspended_note.status', 0)
            ->where("{$this->db->dbprefix('suspended_note')}.name LIKE '%" . $term . "%'");
            if($warehouse_id){
                $this->db->where('suspended_note.warehouse_id',$warehouse_id);
            }
            
            $this->db->group_by('suspended_note.note_id');

        $this->db->limit($limit);
        $q = $this->db->get('suspended_note');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getAllInvoiceItems($sale_id, $return_id = null)
    {
        $this->db->select('sale_order_items.*,
            tax_rates.code as tax_code,
            tax_rates.name as tax_name,
            tax_rates.rate as tax_rate,
            product_variants.name as variant, 
            units.code as base_unit_code,
            products.slug,
            products.price, 
            sale_units.name as name_unit,
            products.code, 
            products.image, 
            IF('.$this->db->dbprefix('products').'.currency ="KHR", "៛", "$") as currency,
            products.details as details, 
            products.hsn_code as hsn_code, 
            products.second_name as second_name, 
            products.unit as base_unit_id, 
            products.category_id,
            products.subcategory_id,
            products.cf1 as width,
            products.second_name,
            products.weight,
            products.product_details,
            products.cf2 as length,
            products.cf3 as product_cf3,
            products.cf4 as product_cf4,
            products.cf5 as product_cf5,
            products.cf6 as product_cf6,
            options.name as option_name,
            products.type as product_type,
            units.code as base_unit_code,
            sale_units.name as product_unit_name
        ')
        ->join('products', 'products.id = sale_order_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id = sale_order_items.option_id', 'left')
        ->join('tax_rates', 'tax_rates.id = sale_order_items.tax_rate_id', 'left')
        ->join('units', 'units.id = products.unit', 'left')
        ->join('units sale_units', 'sale_units.id = sale_order_items.product_unit_id', 'left')
        ->join('options', 'options.id = sale_order_items.option_id', 'left')
        ->group_by('sale_order_items.id')
        ->order_by('id', 'asc');
        if ($sale_id && !$return_id) {
            $this->db->where('sale_order_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_order_id', $return_id);
        }
        $q = $this->db->get('sale_order_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllInvoiceItems_GroupProduct($sale_id, $return_id = null)
    {
        $this->db->select('sale_order_items.*,
            tax_rates.code as tax_code,
            tax_rates.name as tax_name,
            tax_rates.rate as tax_rate,
            product_variants.name as variant, 
            units.code as base_unit_code,
            products.slug,
            products.price, 
            sale_units.name as name_unit,
            products.code, 
            products.image, 
            IF('.$this->db->dbprefix('products').'.currency ="KHR", "៛", "$") as currency,
            products.details as details, 
            products.hsn_code as hsn_code, 
            products.second_name as second_name, 
            products.unit as base_unit_id, 
            products.category_id,
            products.subcategory_id,
            products.cf1 as width,
            products.second_name,
            products.weight,
            products.product_details,
            products.cf2 as length,
            products.cf3 as product_cf3,
            products.cf4 as product_cf4,
            products.cf5 as product_cf5,
            products.cf6 as product_cf6,
            options.name as option_name,
            products.type as product_type,
            units.code as base_unit_code,
            sale_units.name as product_unit_name,
            "" as expiry 
        ')
        ->join('products', 'products.id = sale_order_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id = sale_order_items.option_id', 'left')
        ->join('tax_rates', 'tax_rates.id = sale_order_items.tax_rate_id', 'left')
        ->join('units', 'units.id = products.unit', 'left')
        ->join('units sale_units', 'sale_units.id = sale_order_items.product_unit_id', 'left')
        ->join('options', 'options.id = sale_order_items.option_id', 'left')
        ->group_by('sale_order_items.product_id')
        ->order_by('id', 'asc');
        if ($sale_id && !$return_id) {
            $this->db->where('sale_order_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_order_id', $return_id);
        }
        $q = $this->db->get('sale_order_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllInvoiceItemsWithDetails($sale_id)
    {
        $this->db->select('sale_order_items.*, products.details, product_variants.name as variant');
        $this->db->join('products', 'products.id=sale_order_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=sale_order_items.option_id', 'left')
        ->group_by('sale_order_items.id');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('sale_order_items', ['sale_order_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getAllQuoteItems($quote_id)
    {
        $q = $this->db->get_where('quote_items', ['quote_id' => $quote_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCostingLines($sale_item_id, $product_id, $sale_id = null)
    {
        if ($sale_id) {
            $this->db->where('sale_id', $sale_id);
        }
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->order_by('id', $orderby);
        $q = $this->db->get_where('costing', ['sale_item_id' => $sale_item_id, 'product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDeliveryByID($id)
    {
        $q = $this->db->get_where('deliveries', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDeliveryBySaleID($sale_id)
    {
        $q = $this->db->get_where('deliveries', ['sale_id' => $sale_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getInvoiceByID($id)
    {   
        $this->db->select('sales_order.*,IFNULL(payments.deposit,0) as deposit');
        $this->db->join('(select sum(amount) as deposit,sale_order_id from '.$this->db->dbprefix('payments').' where sale_order_id > 0 GROUP BY sale_order_id) as payments','payments.sale_order_id = sales_order.id','left');

        $q = $this->db->get_where('sales_order', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getInvoicePayments($sale_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getItemByID($id)
    {
        $q = $this->db->get_where('sale_order_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getItemRack($product_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse_id], 1);
        if ($q->num_rows() > 0) {
            $wh = $q->row();
            return $wh->rack;
        }
        return false;
    }

    public function getPaymentByID($id)
    {
        $q = $this->db->get_where('payments', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPaymentsForSale($sale_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.cc_no, payments.cheque_no, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPaypalSettings()
    {
        $q = $this->db->get_where('paypal', ['id' => 1]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByName($name)
    {
        $q = $this->db->get_where('products', ['name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductComboItems($pid, $warehouse_id = null)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name,products.type as type, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('combo_items.id');
        if ($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('combo_items', ['combo_items.product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getProductNames($term, $warehouse_id, $pos = false, $limit = 5)
    {
        $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";
        $this->db->select('products.*, FWP.quantity as quantity, categories.id as category_id, categories.name as category_name', false)
            ->join($wp, 'FWP.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('products.id');
            $this->db->where("(
                {$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR 
                {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR 
                {$this->db->dbprefix('products')}.item_code LIKE '%" . $term . "%' OR 
                concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
 
        // $this->db->order_by('products.name ASC');
        if ($pos) {
            $this->db->where('hide_pos !=', 1);
        }
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductOptions($product_id, $warehouse_id, $all = null)
    {
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $wpv = "( SELECT option_id, warehouse_id, quantity from {$this->db->dbprefix('warehouses_products_variants')} WHERE product_id = {$product_id}) FWPV";
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.cost as cost, product_variants.price as price, product_variants.quantity as total_quantity, FWPV.quantity as quantity', false)
            ->join($wpv, 'FWPV.option_id=product_variants.id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->group_by('product_variants.id');
        if ((!$this->Settings->overselling || ($this->Settings->overselling && !$warehouse->overselling)) && !$all) {
            $this->db->where('FWPV.warehouse_id', $warehouse_id);
            $this->db->where('FWPV.quantity >', 0);
        }
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductQuantity($product_id, $warehouse)
    {
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse], 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); 
        }
        return false;
    }

    public function getProductVariantByName($name, $product_id)
    {
        $q = $this->db->get_where('product_variants', ['name' => $name, 'product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductVariants($product_id)
    {
        $q = $this->db->get_where('product_variants', ['product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductWarehouseOptionQty($option_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', ['option_id' => $option_id, 'warehouse_id' => $warehouse_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchaseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getQuoteByID($id)
    {
        $q = $this->db->get_where('quotes', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturnByID($id)
    {
        $q = $this->db->get_where('sales_order', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturnBySID($sale_id)
    {
        $q = $this->db->get_where('sales_order', ['sale_id' => $sale_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSaleCosting($sale_id)
    {
        $q = $this->db->get_where('costing', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSaleItemByID($id)
    {
        $q = $this->db->get_where('sale_order_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSkrillSettings()
    {
        $q = $this->db->get_where('skrill', ['id' => 1]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStaff()
    {
        if (!$this->Owner) {
            $this->db->where('group_id !=', 1);
        }
        $this->db->where('group_id !=', 3)->where('group_id !=', 4);
        $q = $this->db->get('users');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getTaxRateByName($name)
    {
        $q = $this->db->get_where('tax_rates', ['name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseProduct($pid, $wid)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('warehouses_products') . '.quantity as quantity')
            ->join('warehouses_products', 'warehouses_products.product_id = products.id', 'left');
        // $q = $this->db->get_where('products', ['warehouses_products.product_id' => $pid, 'warehouses_products.id' => $wid]);
        $q = $this->db->get_where('products', ['warehouses_products.product_id' => $pid, 'warehouses_products.warehouse_id' => $wid]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseProductQuantity($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', ['warehouse_id' => $warehouse_id, 'product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function resetSaleActions($id, $return_id = null, $check_return = null)
    {
        if ($sale = $this->getInvoiceByID($id)) {
            if ($check_return && $sale->sale_status == 'returned') {
                $this->session->set_flashdata('warning', lang('sale_x_action'));
                redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
            }

            if ($sale->sale_status == 'completed') {
                if ($costings = $this->getSaleCosting($id)) {
                    foreach ($costings as $costing) {
                        if ($pi = $this->getPurchaseItemByID($costing->purchase_item_id)) {
                            $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                        // $this->db->update('purchase_items', ['quantity_balance' => ($costing->quantity_balance + $costing->quantity)], ['id' => $pi->id]);
                        } else {
                            // $sale_item = $this->getSaleItemByID($costing->sale_item_id);
                            $pi = $this->site->getPurchasedItem(['product_id' => $costing->product_id, 'option_id' => $costing->option_id ? $costing->option_id : null, 'purchase_id' => null, 'transfer_id' => null, 'warehouse_id' => $sale->warehouse_id]);
                            $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                        }
                    }
                    $this->db->delete('costing', ['id' => $costing->id]);
                }
                $items = $this->getAllInvoiceItems($id);
                $this->site->syncQuantity(null, null, $items);
                $this->bpas->update_award_points($sale->grand_total, $sale->customer_id, $sale->created_by, true);
                return $items;
            }
        }
    }

    public function syncQuantity($sale_id)
    {
        if ($sale_items = $this->getAllInvoiceItems($sale_id)) {
            foreach ($sale_items as $item) {
                $this->site->syncProductQty($item->product_id, $item->warehouse_id);
                if (isset($item->option_id) && !empty($item->option_id)) {
                    $this->site->syncVariantQty($item->option_id, $item->warehouse_id);
                }
            }
        }
    }


    public function UpdateCostingAndPurchaseItem($return_item, $product_id, $quantity)
    {
        $bln_quantity = $quantity;
        if ($costings = $this->getCostingLines($return_item['id'], $product_id)) {
            foreach ($costings as $costing) {
                if ($costing->quantity > $bln_quantity && $bln_quantity != 0) {
                    $qty = $costing->quantity                                                                                     - $bln_quantity;
                    $bln = $costing->quantity_balance && $costing->quantity_balance >= $bln_quantity ? $costing->quantity_balance - $bln_quantity : 0;
                    $this->db->update('costing', ['quantity' => $qty, 'quantity_balance' => $bln], ['id' => $costing->id]);
                    $bln_quantity = 0;
                    break;
                } elseif ($costing->quantity <= $bln_quantity && $bln_quantity != 0) {
                    $this->db->delete('costing', ['id' => $costing->id]);
                    $bln_quantity = ($bln_quantity - $costing->quantity);
                }
            }
        }
        $clause = ['product_id' => $product_id, 'warehouse_id' => $return_item['warehouse_id'], 'purchase_id' => null, 'transfer_id' => null, 'option_id' => $return_item['option_id']];
        $this->site->setPurchaseItem($clause, $quantity);
        $this->site->syncQuantity(null, null, null, $product_id);
    }

    public function updateDelivery($id, $data = [])
    {
        if ($this->db->update('deliveries', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateGiftCard($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('gift_cards', $data)) {
            return true;
        }
        return false;
    }

    public function updateOptionQuantity($option_id, $quantity)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('product_variants', ['quantity' => $nq], ['id' => $option_id])) {
                return true;
            }
        }
        return false;
    }

    public function updatePayment($id, $data = [], $customer_id = null)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, ['id' => $id])) {
            $this->site->syncSalePayments($data['sale_id']);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', ['balance' => ($gc->balance + $opay->amount)], ['card_no' => $opay->cc_no]);
            } elseif ($opay->paid_by == 'deposit') {
                if (!$customer_id) {
                    $sale        = $this->getInvoiceByID($opay->sale_id);
                    $customer_id = $sale->customer_id;
                }
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount + $opay->amount)], ['id' => $customer->id]);
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', ['balance' => ($gc->balance - $data['amount'])], ['card_no' => $data['cc_no']]);
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount - $data['amount'])], ['id' => $customer_id]);
            }
            return true;
        }
        return false;
    }

    public function updateProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', ['quantity' => $nq], ['option_id' => $option_id, 'warehouse_id' => $warehouse_id])) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq])) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        }
        return false;
    }

    public function updateSale($id, $data, $items = [])
    {
        $this->db->trans_start();

        // $this->bpas->print_arrays($cost);

        if ($this->db->update('sales_order', $data, ['id' => $id]) && 
            $this->db->delete('sale_order_items', ['sale_order_id' => $id])) {
            foreach ($items as $item) {
                $item['sale_order_id'] = $id;
                $this->db->insert('sale_order_items', $item);
               
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function updateStatus($id, $status, $note)
    {
        $this->db->trans_start();
        $sale  = $this->getInvoiceByID($id);
        $items = $this->getAllInvoiceItems($id);
        $cost  = [];
        if ($status == 'completed' && $sale->sale_status != 'completed') {
            foreach ($items as $item) {
                $items_array[] = (array) $item;
            }
            $cost = $this->site->costing($items_array);
        }
        if ($status != 'completed' && $sale->sale_status == 'completed') {
            $this->resetSaleActions($id);
        }

        if ($this->db->update('sales_order', ['sale_status' => $status, 'note' => $note], ['id' => $id]) && $this->db->delete('costing', ['sale_order_id' => $id])) {
            if ($status == 'completed' && $sale->sale_status != 'completed') {
                foreach ($items as $item) {
                    $item = (array) $item;
                    if ($this->site->getProductByID($item['product_id'])) {
                        $item_costs = $this->site->item_costing($item);
                        foreach ($item_costs as $item_cost) {
                            $item_cost['sale_item_id'] = $item['id'];
                            $item_cost['sale_order_id']      = $id;
                            $item_cost['date']         = date('Y-m-d', strtotime($sale->date));
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        }
                    }
                }
            }

            if (!empty($cost)) {
                $this->site->syncPurchaseItems($cost);
            }
            $this->site->syncQuantity($id);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (UpdataStatus:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function getSaleOrder($sale_order_id=null)
    {
        $q = $this->db->get_where('sales_order',array('id'=>$sale_order_id));
        if($q->num_rows()>0){
            return $q->row();
        }
        return null;
    }

    public function getSaleOrderByID($id = false)
    {
        $q = $this->db->get_where('sales_order', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAuthorizeSaleOrder($id) {
        if($id) {
            $this->db->update('sales_order', array('order_status' => 'approved'), array('id' => $id));
            return true;
        }
        return false;
    }
    public function getunapproved($id) {
        if($id) {
            $this->db->update('sales_order', array('order_status' => 'pending'), array('id' => $id));
            return true;
        }
        return false;
    }
    public function getrejected($id) {
        if($id) {
            $this->db->update('sales_order', array('order_status' => 'rejected'), array('id' => $id));
            return true;
        }
        return false;
    }
    public function getSaleOrderRefByID($sale_order_id = null){
        $q = $this->db->get_where('sales_order', array('id' => $sale_order_id));
        if($q->num_rows()>0){
            return $q->row();
        }
        return null;
    }
    public function getLastDrawImage($id, $customer_id)
    {   
        $this->db->order_by('id', 'DESC');
        $q = $this->db->get_where('sales_order', [
            'customer_id' => $customer_id,
            'image !=' =>''
        ], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function addPrescription($data = [], $items = [], $payment = [], $si_return = []){
        $this->db->trans_start();
        if ($this->db->insert('prescription', $data)) {
            $sale_id = $this->db->insert_id();
            $item['option_id']   = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;

            if ($this->site->getReference('sr') == $data['reference_no']) {
                $this->site->updateReference('sr');
            }
            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $this->db->insert('prescription_items', $item);
                
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
    public function updatePrescription($id, $data, $items = [])
    {
        $this->db->trans_start();
        if ($this->db->update('prescription', $data, ['id' => $id]) && 
            $this->db->delete('prescription_items', ['sale_id' => $id])) {
            foreach ($items as $item) {
                $item['sale_id'] = $id;
                $this->db->insert('prescription_items', $item);
               
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }
     public function deletePrescription($id)
    {
        $this->db->trans_start();
        if ($this->db->delete('prescription_items', ['sale_id' => $id]) && 
            $this->db->delete('prescription', ['id' => $id])) {
            $this->db->delete('prescription', ['sale_id' => $id]);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Delete:Sales_model.php)');
        } else {
            return true;
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
    public function getAllPrescriptionItems($sale_id, $return_id = null)
    {
        
        $this->db->select('prescription_items.*,
            tax_rates.code as tax_code,
            tax_rates.name as tax_name,
            tax_rates.rate as tax_rate,
            product_variants.name as variant, 
            units.code as base_unit_code,
            products.slug,
            products.price, 
            sale_units.name as name_unit,
            products.code, 
            products.image, 
            IF('.$this->db->dbprefix('products').'.currency ="KHR", "៛", "$") as currency,
            products.details as details, 
            products.hsn_code as hsn_code, 
            products.second_name as second_name, 
            products.unit as base_unit_id, 
            products.category_id,
            products.subcategory_id,
            products.cf1 as width,
            products.second_name,
            products.weight,
            products.product_details,
            products.cf2 as length,
            products.cf3 as product_cf3,
            products.cf4 as product_cf4,
            products.cf5 as product_cf5,
            products.cf6 as product_cf6,
            options.name as option_name,

            products.type as product_type,
            units.code as base_unit_code
            ')
        ->join('products', 'products.id = prescription_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id = prescription_items.option_id', 'left')
        ->join('tax_rates', 'tax_rates.id = prescription_items.tax_rate_id', 'left')
        ->join('units', 'units.id = products.unit', 'left')
        ->join('units sale_units', 'sale_units.id = prescription_items.product_unit_id', 'left')
        ->join('options', 'options.id = prescription_items.option_id', 'left')
        ->group_by('prescription_items.id')
        ->order_by('id', 'asc');
        if ($sale_id && !$return_id) {
            $this->db->where('sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_id', $return_id);
        }
        $q = $this->db->get('prescription_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getDepositByID($id = false)
    {
        $this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as cash_account");
        $this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('payments.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getTotalDeposit($sale_order_id = false){
        $q = $this->db->select('sum(amount) as amount')
                    ->where('sale_order_id',$sale_order_id)
                    ->get('payments');
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
    public function getSODeposits($sale_order_id = false){
        $this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
        $this->db->order_by('id', 'desc');
        $this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('sale_order_id' => $sale_order_id));
        if($q->num_rows() > 0 ){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addDeposit($data = array(), $customer_id = null, $accTranPayments = array())
    {
        if ($this->db->insert('payments', $data)) {
            $payment_id = $this->db->insert_id();
            if($accTranPayments){
                foreach($accTranPayments as $accTranPayment){
                    $accTranPayment['tran_no']= $payment_id;
                    $this->db->insert('gl_trans', $accTranPayment);
                }
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
            }
            return true;
        }
        return false;
    }
    
    public function updateDeposit($id = false, $data = array(), $customer_id = null, $accTranPayments = array())
    {
        $opay = $this->getDepositByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
            $this->site->deleteAccTran('SODeposit',$id);
            if($accTranPayments){
                $this->db->insert_batch('gl_trans', $accTranPayments);
            }

            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                if (!$customer_id) {
                    $sale_order = $this->getInvoiceByID($opay->sale_order_id);
                    $customer_id = $sale_order->customer_id;
                }
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
            }
            return true;
        }
        return false;
    }
    public function deleteDeposit($id = false)
    {
        $opay = $this->getDepositByID($id);
        if ($this->db->delete('payments', array('id' => $id))) {
            $this->site->deleteAccTran('SODeposit',$id);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                $sale_order = $this->getSaleOrderByID($opay->sale_order_id);
                $customer = $this->site->getCompanyByID($sale_order->customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
            }
            return true;
        }
        return FALSE;
    }
}
