<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Site extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function yesno($status)
    {
        return ($status > 0) ? lang('yes'): lang('no');
    }

    public function getProductQty($product_id = false, $warehouse_id = false)
    {
        if ($product_id) {
            if ($warehouse_id) { 
                $this->db->where("warehouse_id",$warehouse_id);
            }
            $this->db->where("product_id",$product_id);
            $this->db->select("sum(quantity) as quantity");
            $q = $this->db->get("warehouses_products");
            if ($q->num_rows() > 0) {
                return $q->row();
            }
        }
        return false;
    }

    public function calculateAVCost($product_id, $warehouse_id, $net_unit_price, $unit_price, $quantity, $product_name, $option_id, $item_quantity, $expiry = null)
    {
        $warehouse     = $this->site->getWarehouseByID($warehouse_id);
        $product       = $this->getProductByID($product_id);
        $real_item_qty = $quantity;
        $item_quantity_2 = $item_quantity;
        $wp_details    = $this->getWarehouseProduct($warehouse_id, $product_id);
        $con           = $wp_details ? $wp_details->avg_cost : $product->cost;
        $tax_rate      = $this->getTaxRateByID($product->tax_rate);
        $ctax          = $this->calculateTax($product, $tax_rate, $con);
        if ($product->tax_method) {
            $avg_net_unit_cost = $con;
            $avg_unit_cost     = ($con + $ctax['amount']);
        } else {
            $avg_unit_cost     = $con;
            $avg_net_unit_cost = ($con - $ctax['amount']);
        }
        if ($pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id, null, $expiry)) {
            $cost_row    = [];
            $quantity    = $item_quantity;
            $balance_qty = $quantity;
            foreach ($pis as $pi) {
                // if (!empty($pi) && $pi->quantity > 0 && $balance_qty <= $quantity && $quantity != 0) {
                if (!empty($pi) && $balance_qty <= $quantity && $quantity != 0) {
                    if ($pi->quantity_balance >= $quantity && $quantity != 0) {
                        $balance_qty = $pi->quantity_balance - $quantity;
                        $cost_row    = ['date' => date('Y-m-d'), 'product_id' => $product_id,'expiry'=>$expiry,  'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $quantity, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id];
                        $quantity    = 0;
                    } elseif ($quantity != 0) {
                        $quantity    = $quantity - $pi->quantity_balance;
                        $balance_qty = $quantity;
                        $cost_row    = ['date' => date('Y-m-d'), 'product_id' => $product_id,'expiry'=>$expiry,  'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => 0, 'inventory' => 1, 'option_id' => $option_id];
                    }
                }
                if (empty($cost_row)) {
                    break;
                }
                $cost[] = $cost_row;
                if ($quantity == 0) {
                    break;
                }
            }
        }
        if ($pisi = $this->getPurchasedItemstoSales($product_id, $warehouse_id, $option_id, null, $expiry)) {
            $quantity_2    = $item_quantity_2;
            $balance_qty_2 = $quantity_2;
            foreach ($pisi as $pi) {
                if (!empty($pi) && $balance_qty_2 <= $quantity_2 && $quantity_2 != 0) {
                    if ($pi->quantity_balance >= $quantity_2 && $quantity_2 != 0) {
                        $balance_qty_2 = $pi->quantity_balance - $quantity_2;
                        $quantity_2    = 0;
                    } elseif ($quantity_2 != 0) {
                        $quantity_2    = $quantity_2 - $pi->quantity_balance;
                        $balance_qty_2 = $quantity_2;
                    }
                }
                if ($quantity_2 == 0) {
                    break;
                }
            }
        }
        if (isset($quantity_2) && $quantity_2 > 0 && (!$this->Settings->overselling || ($this->Settings->overselling && !$warehouse->overselling)) && $product->module_type != "property" && $product->type != 'service') {
            $this->session->set_flashdata('error', sprintf(lang('quantity_out_of_stock_for_%s'), $product_name));
            redirect($_SERVER['HTTP_REFERER']);
        } elseif ($quantity != 0) {
            $cost[] = ['date' => date('Y-m-d'), 'product_id' => $product_id, 'expiry'=> $expiry, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => $quantity, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => (0 - $quantity), 'overselling' => 1, 'inventory' => 1];
            $cost[] = ['pi_overselling' => 1, 'product_id' => $product_id, 'quantity_balance' => (0 - $quantity), 'warehouse_id' => $warehouse_id, 'option_id' => $option_id];
        }
        return $cost;
    }

    public function getCostPriceUnit($id)
    {
        $this->db->select();
        $this->db->where_in('product_code', $id);
        $q = $this->db->get('cost_price_by_units');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }

    public function getProductCostPriceByID($unit,$id)
    {
        $this->db->select("*");
        $q = $this->db->get_where('cost_price_by_units' , ['product_id'=> $id ,'unit_id'=>$unit]);
        if ($q->num_rows() > 0) {
            return $q->row();
       }
       return false;
    }

    public function getDriverByID($id)
    {
        $this->db->select('companies.id,' . $this->db->dbprefix('companies') . '.name,' . $this->db->dbprefix('companies') . '.email,' . $this->db->dbprefix('companies') . '.phone');

        $q = $this->db->get_where('companies', array('id' => $id, 'companies.group_name' => 'driver'));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getStaffById($user_id)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', false)
            ->where('saleman_by', $user_id);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAlertInstallmentMissedRepayments()
    {
        $remind = ($this->Settings->installment_alert_days?$this->Settings->installment_alert_days:0);
        $q = $this->db->select('COUNT(bpas_installment_items.id) as alert_num')
                       ->join('installments', 'installment_items.installment_id=installments.id', 'left')
                       ->where('DATE_SUB(bpas_installment_items.`deadline`, INTERVAL '.$remind.' DAY) <=', date("Y-m-d"))
                       ->where('installment_items.status !=','paid')
                       ->where('installment_items.status !=','payoff')
                       ->where('installments.status !=','payoff')
                       ->where('installments.status !=','completed')
                       ->where('installments.status !=','inactive')
                       ->where('installments.status !=','voiced')
                       ->get('installment_items');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return (INT) $res->alert_num;
        }
        return FALSE;
    }

    public function get_maintenance_alerts()
    {
        // $this->db->select('*');
        // $this->db->where('maintenance_status !=','completed');
        // $q = $this->db->get('maintenance');
        // if($q->num_rows() > 0 ){
        //     $q = $q->row();
        //     $sales = $this->getSaleByID($q->sale_id);
        // }
        // echo $sales->date;
        $this->db->select('COUNT(*) AS count');
        $this->db->where('DATE_SUB(maintenance_date, INTERVAL 7 DAY) < CURDATE()');
        $this->db->where('maintenance_status !=','completed');
        $q = $this->db->get('maintenance');
        if($q->num_rows() > 0 ){
            $q = $q->row();
            return $q->count;
        }
        return false;
    }
     public function getAllSuspendAndSaleItem($year=null, $month=null)
    {

        $MY = ($month . '-' . $year);
        $resultsarray=array();
        $this->db->select("suspended_note.*,
            {$this->db->dbprefix('sales')}.id as sale_id,
            {$this->db->dbprefix('sale_items')}.product_id,
            {$this->db->dbprefix('reservation')}.duration as days,
            {$this->db->dbprefix('reservation')}.checkIn,
            {$this->db->dbprefix('reservation')}.checkOut,
            {$this->db->dbprefix('companies')}.name as customer,
            {$this->db->dbprefix('companies')}.phone");
        $this->db->from('suspended_note');
        $this->db->join('sale_items','sale_items.product_id=suspended_note.note_id','left');
        $this->db->join('sales','sales.id=sale_items.sale_id','left');
        $this->db->join('companies','companies.id=sales.customer_id','left');
        $this->db->join('reservation','reservation.sale_id = sale_items.sale_id AND reservation.note_id = suspended_note.note_id','left');
        $this->db->where("{$this->db->dbprefix('sale_items')}.product_type",'room');
        $this->db->group_start();

            $this->db->where(" CONCAT(MONTH({$this->db->dbprefix('reservation')}.checkIn), '-', YEAR({$this->db->dbprefix('reservation')}.checkIn)) = '{$MY}' ");
            
            $this->db->or_where("
                CONCAT(
                    MONTH(DATE_ADD({$this->db->dbprefix('reservation')}.checkIn, INTERVAL ({$this->db->dbprefix('reservation')}.duration - 1) DAY)), '-', 
                    YEAR(DATE_ADD({$this->db->dbprefix('reservation')}.checkIn, INTERVAL ({$this->db->dbprefix('reservation')}.duration - 1) DAY))
                ) = '{$MY}' "
                );

        $this->db->group_end();

        $this->db->where('reservation.checkOut', null); 
        $query = $this->db->get();
        if($query->result_array()>0){
            foreach($query->result_array() as $val){
                
                $date_staying = date('Y-m-d', strtotime($val['checkIn']));
                $resultsarray[$val['note_id']][$date_staying]  = $val;
           
                for($i=1 ; $i < $val['days'] ; $i++){
                    $date_staying = date('Y-m-d', strtotime($val['checkIn']. ' + '.$i.'days'));
                    $resultsarray[$val['note_id']][$date_staying]  = $val;
                }    
            }
            return $resultsarray;
        }
        return FALSE;
    }

    public function calculateCost($product_id, $warehouse_id, $net_unit_price, $unit_price, $quantity, $product_name, $option_id, $item_quantity, $expiry)
    {
        $pis           = $this->getPurchasedItems($product_id, $warehouse_id, $option_id, null, $expiry);
        $product       = $this->getProductByID($product_id);
        $real_item_qty = $quantity;
        $quantity      = $item_quantity;
        $balance_qty   = $quantity;
        $item_quantity_2 = $item_quantity;
        foreach ($pis as $pi) {
            $cost_row = null;
            if (!empty($pi) && $balance_qty <= $quantity && $quantity != 0) {
                if($pi->base_unit_cost == null && $pi->unit_cost == null && $pi->net_unit_cost == 0) {
                    $purchase_unit_cost = $this->getProductByID($product_id) ? $this->getProductByID($product_id)->cost : 0;
                } else {
                    $purchase_unit_cost = $pi->base_unit_cost ? $pi->base_unit_cost : ($pi->unit_cost ? $pi->unit_cost : ($pi->net_unit_cost + ($pi->quantity != 0 ? ($pi->item_tax / $pi->quantity) : 0)));
                    $purchase_unit_cost = (is_nan($purchase_unit_cost) || $purchase_unit_cost == 0) ? ($this->getPurchasedItem(['id' => $pi->id])->purchase_id != null ? 0 : $product->cost) : $purchase_unit_cost;
                }
                if ($pi->quantity_balance >= $quantity && $quantity != 0) {
                    $balance_qty = $pi->quantity_balance - $quantity;
                    $cost_row    = ['date' => date('Y-m-d'), 'product_id' => $product_id, 'expiry' => $expiry, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $quantity, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id];
                    $quantity    = 0;
                } elseif ($quantity != 0) {
                    $quantity    = $quantity - $pi->quantity_balance;
                    $balance_qty = $quantity;
                    $cost_row    = ['date' => date('Y-m-d'), 'product_id' => $product_id, 'expiry' => $expiry, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => 0, 'inventory' => 1, 'option_id' => $option_id];
                }
            }
            $cost[] = $cost_row;
            if ($quantity == 0) {
                break;
            }
        }
        if ($pisi = $this->getPurchasedItemstoSales($product_id, $warehouse_id, $option_id, null, $expiry)) {
            $quantity_2    = $item_quantity_2;
            $balance_qty_2 = $quantity_2;                
            foreach ($pisi as $pi) {
                if (!empty($pi) && $balance_qty_2 <= $quantity_2 && $quantity_2 != 0) {
                    if ($pi->quantity_balance >= $quantity_2 && $quantity_2 != 0) {
                        $balance_qty_2 = $pi->quantity_balance - $quantity_2;
                        $quantity_2    = 0;
                    } elseif ($quantity_2 != 0) {
                        $quantity_2    = $quantity_2 - $pi->quantity_balance;
                        $balance_qty_2 = $quantity_2;
                    }
                }
                if ($quantity_2 == 0) {
                    break;
                }
            }
        }
        if ($quantity_2 > 0 && $product->module_type != "property") {
            $this->session->set_flashdata('error', sprintf(lang('quantity_out_of_stock_for_%s'), (isset($pi->product_name) ? $pi->product_name : $product_name)));
            redirect($_SERVER['HTTP_REFERER']);
        } elseif ($quantity != 0) {
            $cost[] = [
                'date'                      => date('Y-m-d'), 
                'product_id'                => $product_id,
                'expiry'                    =>$expiry,
                'sale_item_id'              => 'sale_items.id', 
                'purchase_item_id'          => null, 
                'quantity'                  => $quantity, 
                'purchase_net_unit_cost'    => $pi->net_unit_cost, 
                'purchase_unit_cost'        => isset($purchase_unit_cost)?$purchase_unit_cost:0, 
                'sale_net_unit_price'       => $net_unit_price, 
                'sale_unit_price'           => $unit_price, 
                'quantity_balance'          => (0 - $quantity), 
                'overselling'               => 1, 
                'inventory'                 => 1
            ];
            $cost[] = [
                'pi_overselling' => 1, 
                'product_id' => $product_id, 'quantity_balance' => (0 - $quantity), 
                'warehouse_id' => $warehouse_id, 
                'option_id' => $option_id
            ];
        }
        return $cost;
    }

    public function calculateDiscount($discount = null, $amount)
    {
        if ($discount && $this->Settings->product_discount) {
            $dpos = strpos($discount, '%');
            if ($dpos !== false) {
                $pds = explode('%', $discount);
                return $this->bpas->formatDecimal(((($this->bpas->formatDecimal($amount)) * (float) ($pds[0])) / 100));
            } else {
                return $this->bpas->formatDecimal($discount);
            }
        }
        return 0;
    }
    public function getSettings()
    {
        $q = $this->db->get('settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function GetUserModuleSetting()
    {
        $q = $this->db->get('module_user');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function calculateOrderTax($order_tax_id = null, $amount)
    {
        if ($this->Settings->tax2 != 0 && $order_tax_id) {
            if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                if ($order_tax_details->type == 1) {
                    return $this->bpas->formatDecimal((($amount * $order_tax_details->rate) / 100));
                } else {
                    return $this->bpas->formatDecimal($order_tax_details->rate);
                }
            }
        }
        return 0;
    }

    public function calculateTax($product_details = null, $tax_details, $custom_value = null, $c_on = null)
    {
        $value      = $custom_value ? $custom_value : (($c_on == 'cost') ? $product_details->cost : $product_details->price);
        $tax_amount = 0;
        $tax        = 0;
        if ($tax_details && $tax_details->type == 1 && $tax_details->rate != 0) {
            if ($product_details && $product_details->tax_method == 1) {
                $tax_amount = $this->bpas->formatDecimal((($value) * $tax_details->rate) / 100);
                $tax        = $this->bpas->formatDecimal($tax_details->rate, 0) . '%';
            } else {
                $tax_amount = $this->bpas->formatDecimal((($value) * $tax_details->rate) / (100 + $tax_details->rate));
                $tax        = $this->bpas->formatDecimal($tax_details->rate, 0) . '%';
            }
        } elseif ($tax_details && $tax_details->type == 2) {
            $tax_amount = $this->bpas->formatDecimal($tax_details->rate);
            $tax        = $this->bpas->formatDecimal($tax_details->rate, 0);
        }

        return ['id' => (isset($tax_details->id) ? $tax_details->id : 1), 'tax' => $tax, 'amount' => $tax_amount];
    }

    public function check_customer_deposit($customer_id, $amount_usd = null, $amount_khr = null, $amount_thb = null)
    {
        $customer = $this->getDepositByCompanyID($customer_id);
        if(($customer->amount_usd < $amount_usd) || ($customer->amount_khr < $amount_khr) || ($customer->amount_thb < $amount_thb)) return false;
        else return true;
    }

    public function checkOverSold($product_id, $warehouse_id, $option_id = null)
    {
        $clause = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'option_id' => $option_id];
        if ($os = $this->getPurchasedItem($clause)) {
            if ($os->quantity_balance < 0) {
                if ($pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id, true)) {
                    $quantity = $os->quantity_balance;
                    foreach ($pis as $pi) {
                        if ($pi->quantity_balance >= (0 - $quantity) && $quantity != 0) {
                            $balance = $pi->quantity_balance + $quantity;
                            $this->db->update('purchase_items', ['quantity_balance' => $balance], ['id' => $pi->id]);
                            $quantity = 0;
                            break;
                        } elseif ($quantity != 0) {
                            $quantity = $quantity + $pi->quantity_balance;
                            $this->db->update('purchase_items', ['quantity_balance' => 0], ['id' => $pi->id]);
                        }
                    }
                    $this->db->update('purchase_items', ['quantity_balance' => $quantity], ['id' => $os->id]);
                }
            }
        }
    }

    public function checkPermissions()
    {
        $q = $this->db->get_where('permissions', ['group_id' => $this->session->userdata('group_id')], 1);
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return false;
    }

    public function checkSlug($slug, $type = null)
    {
        if (!$type) {
            return $this->db->get_where('products', ['slug' => $slug], 1)->row();
        } elseif ($type == 'category') {
            return $this->db->get_where('categories', ['slug' => $slug], 1)->row();
        } elseif ($type == 'brand') {
            return $this->db->get_where('brands', ['slug' => $slug], 1)->row();
        }
        return false;
    }
    public function getAllStockTypes()
    {
        $q = $this->db->get('stock_type');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function convertToBase($unit, $value)
    {  
        switch ($unit->operator) {
            case '*':
                return $value / $unit->operation_value;
                break;
            case '/':
                return $value * $unit->operation_value;
                break;
            case '+':
                return $value - $unit->operation_value;
                break;
            case '-':
                return $value + $unit->operation_value;
                break;
            default:
                return $value;
        }
    }
    public function convertCostingToBase($value,$unit){
        switch ($unit->operator) {
            case '*':
                return $value * $unit->operation_value;
                break;
            case '/':
                return $value / $unit->operation_value;
                break;
            case '+':
                return $value + $unit->operation_value;
                break;
            case '-':
                return $value - $unit->operation_value;
                break;
            default:
                return $value;
        }
    }
    public function unitToBaseQty($qty, $unit) {
        switch ($unit->operator) {
            case '*':
                return $qty * $unit->operation_value;
                break;
            case '/':
                return $qty / $unit->operation_value;
                break;
            case '+':
                return $qty + $unit->operation_value;
                break;
            case '-':
                return $qty - $unit->operation_value;
                break;
            default:
                return $qty;
        }
    }
    public function baseToUnitQty($qty, $unit) {
        switch ($unit->operator) {
            case '*':
                return $qty / $unit->operation_value;
                break;
            case '/':
                return $qty * $unit->operation_value;
                break;
            case '+':
                return $qty - $unit->operation_value;
                break;
            case '-':
                return $qty + $unit->operation_value;
                break;
            default:
                return $qty;
        }
    }
    public function costing($items)
    {
        $citems = [];
       
        foreach ($items as $item) {
            $warehouse = $this->site->getWarehouseByID($item['warehouse_id']);
            $option            = (isset($item['option_id']) && !empty($item['option_id']) && $item['option_id'] != 'null' && $item['option_id'] != 'false') ? $item['option_id'] : '';
            $pr                = $this->getProductByID($item['product_id']);
            $item['option_id'] = $option;

            if (($pr && $pr->type == 'standard') || ($pr && $pr->type == 'service')) {
                // if (isset($citems['p' . $item['product_id'] . 'o' . $item['option_id']])) {
                //     $citems['p' . $item['product_id'] . 'o' . $item['option_id'] . 'e' . (isset($item['expiry']) ? $item['expiry'] : '')]['aquantity'] += $item['quantity'];
                // } else {
                //     $citems['p' . $item['product_id'] . 'o' . $item['option_id'] . 'e' . (isset($item['expiry']) ? $item['expiry'] : '')]              = $item;
                //     $citems['p' . $item['product_id'] . 'o' . $item['option_id'] . 'e' . (isset($item['expiry']) ? $item['expiry'] : '')]['aquantity'] = $item['quantity'];
                // }
                if (isset($citems['p' . $item['product_id'] . 'o' . $item['option_id'] . 'e' . (isset($item['expiry']) ? $item['expiry'] : '')])) {
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id'] . 'e' . (isset($item['expiry']) ? $item['expiry'] : '')]['aquantity'] += $item['quantity'];
                } else {
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id'] . 'e' . (isset($item['expiry']) ? $item['expiry'] : '')]              = $item;
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id'] . 'e' . (isset($item['expiry']) ? $item['expiry'] : '')]['aquantity'] = $item['quantity'];
                }
            } elseif ($pr && $pr->type == 'combo') {
                $wh          = ($this->Settings->overselling && (!empty($warehouse) && $warehouse->overselling)) ? null : $item['warehouse_id'];
                $combo_items = $this->getProductComboItems($item['product_id'], $wh);
             
                foreach ($combo_items as $combo_item) {
                    if ($combo_item->type == 'standard') {
                        if (isset($citems['p' . $combo_item->id . 'o' . $item['option_id']. 'e' . (isset($item['expiry']) ? $item['expiry'] : '')])) {
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']. 'e' . (isset($item['expiry']) ? $item['expiry'] : '')]['aquantity'] += ($combo_item->qty * $item['quantity']);
                        } else {
                            $cpr = $this->getProductByID($combo_item->id);

                            if ($cpr->tax_rate) {
                                $cpr_tax = $this->getTaxRateByID($cpr->tax_rate);
                                if ($cpr->tax_method) {
                                    $item_tax       = $this->bpas->formatDecimal((($combo_item->unit_price) * $cpr_tax->rate) / (100 + $cpr_tax->rate));
                                    $net_unit_price = $combo_item->unit_price - $item_tax;
                                    $unit_price     = $combo_item->unit_price;
                                } else {
                                    $item_tax       = $this->bpas->formatDecimal((($combo_item->unit_price) * $cpr_tax->rate) / 100);
                                    $net_unit_price = $combo_item->unit_price;
                                    $unit_price     = $combo_item->unit_price + $item_tax;
                                }
                            } else {
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price     = $combo_item->unit_price;
                            }
                            $cproduct                                                              = ['product_id' => $combo_item->id, 'product_name' => $cpr->name, 'product_type' => $combo_item->type, 'quantity' => ($combo_item->qty * $item['quantity']), 'net_unit_price' => $net_unit_price, 'unit_price' => $unit_price, 'warehouse_id' => $item['warehouse_id'], 'item_tax' => $item_tax, 'tax_rate_id' => $cpr->tax_rate, 'tax' => ($cpr_tax->type == 1 ? $cpr_tax->rate . '%' : $cpr_tax->rate), 'option_id' => null, 'product_unit_id' => $cpr->unit];
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']. 'e' . (isset($item['expiry']) ? $item['expiry'] : '')]              = $cproduct;
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']. 'e' . (isset($item['expiry']) ? $item['expiry'] : '')]['aquantity'] = ($this->bpas->formatDecimal($combo_item->qty) * $item['quantity']);
            //                  var_dump($citems['p' . $combo_item->id . 'o' . $item['option_id']. 'e' . (isset($item['expiry']) ? $item['expiry'] : '')]);
            // exit();
                        }
                    }
                }
                 
            }
        }
       
        $cost = [];
        foreach ($citems as $citem) {
            $item['aquantity'] = $citems['p' . $citem['product_id'] . 'o' . $citem['option_id']. 'e' . (isset($citem['expiry']) ? $citem['expiry'] : '')]['aquantity'];
            $cost[]            = $this->item_costing($citem, true);
        }
        return $cost;
    }

    public function get_expiring_qty_alerts()
    {
        // $date = date('Y-m-d', strtotime('+3 months'));
        // $this->db->select('SUM(quantity_balance) as alert_num')
        //     ->where('expiry !=', null)->where('expiry !=', '0000-00-00')
        //     ->where('expiry <', $date);
        // $q = $this->db->get('purchase_items');
        // if ($q->num_rows() > 0) {
        //     $res = $q->row();
        //     return (int) $res->alert_num;
        // }
        // return false;

        $expiry_alert_days    = $this->Settings->expiry_alert_days;
        $settings_expiry_date = (!empty($expiry_alert_days) && $expiry_alert_days != '' && $expiry_alert_days !== 0) ? date('Y-m-d', strtotime(" +{$expiry_alert_days} days ")) : null;

        $this->db->select("SUM({$this->db->dbprefix('purchase_items')}.quantity_balance) as alert_num")
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->where('expiry !=', null)->where('expiry !=', '0000-00-00')
            ->where('purchase_items.quantity_balance >', 0);

        if ($this->Settings->expiry_alert_by == 1) {
            if ($settings_expiry_date) {
                $this->db->where($this->db->dbprefix('purchase_items') . '.expiry <=', $settings_expiry_date);
            }
        } else {
            $this->db->where("
                {$this->db->dbprefix('products')}.expiry_alert_days IS NOT NULL AND 
                {$this->db->dbprefix('products')}.expiry_alert_days != '' AND
                {$this->db->dbprefix('products')}.expiry_alert_days != 0 AND
                {$this->db->dbprefix('purchase_items')}.expiry <= DATE_ADD(CURDATE(), INTERVAL {$this->db->dbprefix('products')}.expiry_alert_days DAY)  
            ");
        }
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return (int) $res->alert_num;
        }
        return false;
    }
    public function getEmployeeByID($id) {
        $q = $this->db->get_where('users', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function get_public_charge_alerts() 
    {

        $this->db->select('bpas_companies.id,
                           bpas_companies.name,
                           bpas_define_public_charge.description,
                            SUM(
                                bpas_customer_public_charge.amount
                            ) AS total_amount,
                            SUM(
                                bpas_customer_public_charge.paid
                            ) AS paid')
        ->join('companies','companies.id = customer_public_charge.customer_id','left')
        ->join('define_public_charge','define_public_charge.id = customer_public_charge.pub_id','left')
        ->group_by('customer_public_charge.customer_id')
        ->group_by('customer_public_charge.pub_id');
        $q = $this->db->get('customer_public_charge');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getStaff()
    {
        if ($this->Admin) {
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
        return FALSE;
    }
    
    public function getemployeetyp($id)
    {
        $q=$this->db->get('bpas_employee_type');
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function get_sale_suspend_alerts()
    {
        $q = $this->db->query('
                SELECT COUNT(n.date) AS alert_num, MIN(n.date) AS date
                FROM 
                (
                    SELECT date
                    FROM bpas_suspended_bills 
                ) AS n
                WHERE
                DATE_SUB(n.date, INTERVAL 1 DAY) <= CURDATE()
        ');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getDriverByGroupId()
    {
        $this->db->select('id,name');
        $this->db->where(array('group_name' => 'driver'));
        $q = $this->db->get('companies');
        if($q->num_rows() > 0) {
            foreach($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function get_setting()
    {
        $q = $this->db->get('settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function module($name=null)
    {
        $this->db->where(array('module' => $name,'status' => 1));
        $q = $this->db->get('modules');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getPosSetting()
    {
        $q = $this->db->get('pos_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function get_account_setting()
    {
        $q = $this->db->get('account_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function get_shop_payment_alerts()
    {
        $this->db->where('shop', 1)->where('attachment !=', null)->where('payment_status !=', 'paid');
        return $this->db->count_all_results('sales');
    }
    public function edit_sales_request_count(){
        return $this->db->count_all_results('sales_edit_request');
    }
    public function get_shop_sale_alerts()
    {
        $this->db->join('deliveries', 'deliveries.sale_id=sales.id', 'left')
        ->where('sales.shop', 1)->where('sales.sale_status', 'completed')->where('sales.payment_status', 'paid')
        ->group_start()->where('deliveries.status !=', 'delivered')->or_where('deliveries.status IS NULL', null)->group_end();
        return $this->db->count_all_results('sales');
    }
    //------addnew--
    public function get_delivery_alerts()
    {
        $this->db->select('COUNT(*) AS count');
        $this->db->where('DATE_SUB(delivery_date, INTERVAL (SELECT alert_day FROM bpas_settings) DAY) < CURDATE()');
        $this->db->where('sales_order.order_status', 'approved');
        $this->db->where('sales_order.sale_status', 'order');
        $q = $this->db->get('sales_order');
        if($q->num_rows() > 0 ){
            $q = $q->row();
            return $q->count;
        }
        return false;
    }
    public function get_quote_alerts()
    {
        $this->db->select('COUNT(*) AS count');
        $this->db->where('quotes.status', 'pending');
        $q = $this->db->get('quotes');
        if($q->num_rows() > 0 ){
            $q = $q->row();
            return $q->count;
        }
        return false;
    }
    public function get_purchases_request_alerts()
    {
        $this->db->select('COUNT(*) AS count');
        $this->db->where('purchases_request.status', 'requested');
        $q = $this->db->get('purchases_request');
        if($q->num_rows() > 0 ){
            $q = $q->row();
            return $q->count;
        }
        return false;
    }
    public function get_purchases_order_alerts()
    {
        /*$v1 = "(
            SELECT
                purchase_id,
                CASE
            WHEN sum(quantity) <= sum(quantity_po) THEN
                'received'
            ELSE
                CASE
            WHEN (
                sum(quantity_po) > 0 && sum(quantity_po) < sum(quantity)
            ) THEN
                'partial'
            ELSE
                'ordered'
            END
            END AS `status`
            FROM
                bpas_purchase_order_items
            GROUP BY
                purchase_id
        ) AS bpas_purchase_order_items ";
        $this->db->select('COUNT(*) AS count');
        $this->db->join($v1, 'purchase_order_items.purchase_id = bpas_purchases_order.id');
        $this->db->where('bpas_purchases_order.status', 'pending');
        $q = $this->db->get('bpas_purchases_order');
        if($q->num_rows() > 0 ){
            $q = $q->row();
            return $q->count;
        }
        return false;*/
        $this->db->select('COUNT(*) AS count');
        $this->db->where('purchases_order.status', 'pending');
        $q = $this->db->get('purchases_order');
        if($q->num_rows() > 0 ){
            $q = $q->row();
            return $q->count;
        }
        return false;

    }
    public function get_purchases_request_deadline_alerts()
    {
        $futureDate=date('Y-m-d');
        $this->db->select('COUNT(*) AS count')
        ->where('purchases_request.deadline !=', NULL)
        ->where('purchases_request.deadline !=', '0000-00-00')
        ->where('purchases_request.deadline !=', '0000-00-00 00:00:00')
        ->where('purchases_request.deadline !=', '1970-01-01')
        ->where('purchases_request.deadline <=', $futureDate);
        $q=$this->db->get('purchases_request');
        if ($q->num_rows() > 0 ){
            $q=$q->row();
            return $q->count;
        }
        return false;

    }
    public function get_purchases_order_deadline_alerts()
    {   
        $futureDate=date('Y-m-d');
        $this->db->select('COUNT(*) AS count')
        ->where('purchases_order.deadline !=', NULL)
        ->where('purchases_order.deadline !=', '0000-00-00')
        ->where('purchases_order.deadline !=', '0000-00-00 00:00:00')
        ->where('purchases_order.deadline !=', '1970-01-01')
        ->where('purchases_order.deadline <=', $futureDate);
        $q=$this->db->get('purchases_order');
        if ($q->num_rows() > 0) {
            $q=$q->row();
            return $q->count;
        }
        return false;

    }
    public function get_sale_order_order_alerts()
    {
        $this->db->select('COUNT(*) AS count');
        $this->db->group_start();
        $this->db->where('DATE_SUB(due_date , INTERVAL(SELECT alert_day FROM bpas_settings) DAY) < CURDATE()');
        $this->db->or_where('due_date IS NULL AND DATE_ADD(date, INTERVAL(SELECT alert_day - 1 FROM bpas_settings) DAY) < CURDATE()');
        $this->db->group_end();
        $this->db->where('sales_order.order_status', 'pending');
        $q = $this->db->get('sales_order');
        if($q->num_rows() > 0 ){
            $q = $q->row();
            return $q->count;
        }
        return false;
    }
    public function get_customer_alerts()
    {
        // $this->db->select('COUNT(*) AS count');
        // $this->db->where('CURDATE() >= DATE_SUB(end_date, INTERVAL (SELECT alert_day FROM bpas_settings) DAY)');
        // $q = $this->db->get('companies');


        $this->db->select('COUNT(*) AS count');
        $this->db->where('DATE_SUB(due_date, INTERVAL(SELECT alert_day FROM bpas_settings) DAY) < CURDATE()');
        $this->db->where(array('payment_status !=' => 'paid', 'sale_status !=' => 'returned', 'hide' => 1));

        $q = $this->db->get('sales');
        if($q->num_rows() > 0 ){
            $q = $q->row();
            return $q->count;
        }
        return false;
    }
    public function get_customer_payments_alerts()
    {
        // $this->db->select('COUNT(*) AS count, GROUP_CONCAT(CONCAT(bpas_sales.id) SEPARATOR "-") as id');
        // $this->db->where('due_date !=', NULL)->where('due_date !=', '0000-00-00');
        // $this->db->where('DATE_SUB(due_date , INTERVAL (SELECT alert_day FROM bpas_settings) DAY) < CURDATE()');
        // $this->db->where(array('payment_status !=' => 'paid', 'sale_status !=' => 'returned', 'hide' => 1));

        $this->db->select('COUNT(*) AS count, GROUP_CONCAT(CONCAT(bpas_sales.id) SEPARATOR "-") as id');
        $this->db->group_start();
        $this->db->where('DATE_SUB(due_date , INTERVAL(SELECT alert_day FROM bpas_settings) DAY) < CURDATE()');
        $this->db->or_where('due_date IS NULL AND DATE_ADD(date, INTERVAL(SELECT alert_day - 1 FROM bpas_settings) DAY) < CURDATE()');
        $this->db->group_end();
        $this->db->where(array('payment_status !=' => 'paid', 'sale_status !=' => 'returned', 'hide' => 1));

        $q = $this->db->get('sales');
        if($q->num_rows() > 0 ){
            $q = $q->row();
            return $q;
        }
        return false;
    }
    public function get_purchase_payments_alerts()
    {
        // $this->db->select('COUNT(*) AS count, GROUP_CONCAT(CONCAT(bpas_purchases.id) SEPARATOR "-") as id');
        // $this->db->where('due_date !=', NULL)->where('due_date !=', '0000-00-00');
        // $this->db->where('DATE_SUB(due_date , INTERVAL (SELECT alert_day FROM bpas_settings) DAY) < CURDATE()');
        // $this->db->where(array('purchases.payment_status !=' => 'paid', 'purchases.status !=' => 'returned', 'purchases.total !=' => 0));

        $this->db->select('COUNT(*) AS count, GROUP_CONCAT(CONCAT(bpas_purchases.id) SEPARATOR "-") as id');
        $this->db->group_start();
        $this->db->where('DATE_SUB(due_date , INTERVAL(SELECT alert_day FROM bpas_settings) DAY) < CURDATE()');
        $this->db->or_where('due_date IS NULL AND DATE_ADD(date, INTERVAL(SELECT alert_day - 1 FROM bpas_settings) DAY) < CURDATE()');
        $this->db->group_end();
        $this->db->where(array('payment_status !=' => 'paid', 'status !=' => 'returned', 'total !=' => 0));

        $q = $this->db->get('purchases');
        if($q->num_rows() > 0 ){
            $q = $q->row();
            return $q;
        }
        return false;

        /*$q = $this->db->query('
            SELECT COUNT(n.date) AS alert_num, MIN(n.date) AS date
                FROM 
                (
                    SELECT payment_term , date
                    FROM bpas_purchases
                    WHERE
                    `payment_term` <> 0
                    ORDER BY date DESC
                ) AS n
                WHERE
                DATE_SUB(n.date, INTERVAL 1 DAY) <= CURDATE()
        ');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;*/
    }
    ///--end--
    function getArea()
    {
        $q = $this->db->get('group_areas');        
        if ($q->num_rows() > 0 ){           
            
            return $q->result();
        }        
        return FALSE;
    }
    
    public function getProducts($type=null)
    {
        $this->db->select('id, code,name');
        if($type){
            $this->db->where('module_type',$type);
        }
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function get_Acc_setting() 
    {
        $q = $this->db->get('account_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function get_total_qty_alerts()
    {
        $this->db->where('quantity < alert_quantity', null, false)->where('track_quantity', 1);
        return $this->db->count_all_results('products');
    }
    public function get_total_edit_sale_request()
    {
        $this->db->select('sales_edit_request.*')->join('sales', 'sales.id=sales_edit_request.sale_id', 'left');
        $q = $this->db->get_where('sales_edit_request', ['active' => 1,'sales_edit_request.status'=>'request']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false; 
    }
    public function get_result_edit_sale_approved()
    {
        $this->db->select('sales_edit_request.*')->join('sales', 'sales.id=sales_edit_request.sale_id', 'left');
        $q = $this->db->get_where('sales_edit_request', ['active' => 1,'sales_edit_request.status' => 'approved', 'sales_edit_request.created_by' => $this->session->userdata('user_id')]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false; 
    }
     public function get_edit_sale_request_padding()
    {
        $this->db->select('sales_edit_request.*')->join('sales', 'sales.id=sales_edit_request.sale_id', 'left');
        $q = $this->db->get_where('sales_edit_request', ['active' => 1,'sales_edit_request.status' => 'request', 'sales_edit_request.created_by' => $this->session->userdata('user_id')]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false; 
    }
    public function get_edit_sale_request_rejects()
    {
        $this->db->select('sales_edit_request.*')->join('sales', 'sales.id=sales_edit_request.sale_id', 'left');
        $q = $this->db->get_where('sales_edit_request', ['active' => 1,'sales_edit_request.status' => 'rejected', 'sales_edit_request.created_by' => $this->session->userdata('user_id')]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false; 
    }
    public function getAddressByID($id)
    {
        return $this->db->get_where('addresses', ['id' => $id], 1)->row();
    }

    public function getAllBaseUnits()
    {
        $q = $this->db->get_where('units', ['base_unit' => null]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getBillerByUser($id)
    {
        $q = $this->db->get_where('users', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllBrands()
    {
        $q = $this->db->get('brands');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllStockType()
    {
        $q = $this->db->get('stock_type');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllCategories()
    {
        $this->db->where('parent_id', null)->or_where('parent_id', 0)->order_by('name');
        $q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getCompanyByArray($id) 
    {
        $this->db->select();
        $this->db->where_in('id', $id);
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getAllCompanies($group_name)
    {
        $q = $this->db->get_where('companies', ['group_name' => $group_name]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
            
        }
        return false;
    }
    public function getAllCompaniesByBiller($group_name,$id)
    {
        // $q = $this->db->get_where('companies', ['group_name' => $group_name]);
        $this->db->select();
        $this->db->where(['group_name' => $group_name]);
        $this->db->where_in('id', $id);
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
public function getCompanyByBillers($group_name)
    {
        $q = $this->db->get_where('companies', ['group_name' => $group_name]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllCurrencies()
    {
        $q = $this->db->get('currencies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllPurchaseItems($purchase_id)
    {
        $q = $this->db->get_where('purchase_items', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllStoreItems($store_sale_id)
    {
        $q = $this->db->get_where('purchase_items', ['store_sale_id' => $store_sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllSaleItems($sale_id)
    {
        $q = $this->db->get_where('sale_items', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllSaleItemsByCus($customer_id) {
        $this->db->select('sale_items.*');
        $this->db->from('sales');
        $this->db->join('sale_items', 'sales.id = sale_items.sale_id');
        $this->db->where('sales.customer_id', $customer_id);
    
        $q = $this->db->get();
    
        if ($q->num_rows() > 0) {
            return $q->result();
        }
    
        return false;
    }


    public function getAllAddonSaleItems($sale_id)
    {
        $q = $this->db->get_where('sale_addon_items', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSaleItemsBySaleOrderID($id)
    {
        $q = $this->db->get_where('sales', ['so_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $ids[] = $row->id;
            }
            $this->db->select('product_code, product_name, SUM(quantity) as quantity');
            $this->db->from('sale_items');
            $this->db->where_in('sale_id', $ids);
            $this->db->group_by('product_code');
            $query =  $this->db->get();
            if($query->num_rows() > 0){
                foreach (($query->result()) as $row) {
                    $data[] =  $row;        
                }

                return $data;
            }
        }
        return false;
    }  

    public function getSaleOrderItemsBySaleID($sale_id)
    {
        $q = $this->db->get_where('sale_order_items', ['sale_order_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }  

    public function getSaleItemsByDeliverySaleOrderID($sale_order_id)
    {
        $this->db->from('deliveries');
        $this->db->join('sales', 'sales.delivery_id=deliveries.id', 'left');
        $this->db->where('deliveries.sale_order_id', $sale_order_id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $ids[] = $row->id;
            }
            $this->db->select('product_code, product_name, SUM(quantity) as quantity');
            $this->db->from('sale_items');
            $this->db->where_in('sale_id', $ids);
            $this->db->group_by('product_code');
            $query =  $this->db->get();
            if($query->num_rows() > 0){
                foreach (($query->result()) as $row) {
                    $data[] =  $row;        
                }
                return $data;
            }
        }
        return false;
    }   

    public function getAllTaxRates()
    {
        $q = $this->db->get('tax_rates');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDriver()
    {
        $q = $this->db->get_where('companies', array('group_name' => 'driver'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllWarehouses()
    {
        $this->db->from('warehouses')->order_by('id','DESC');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllUser()
    {
        $q = $this->db->get('users');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllUserById($id)
    {
        $q = $this->db->get_where('users', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getBrandByID($id)
    {
        $q = $this->db->get_where('brands', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCategoryByID($id)
    {
        $q = $this->db->get_where('categories', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getCompanyByID($id)
    {
        $q = $this->db->get_where('companies', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCurrencyByCode($code)
    {
        $q = $this->db->get_where('currencies', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCustomerGroupByID($id)
    {
        $q = $this->db->get_where('customer_groups', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    
    public function getDateFormat($id)
    {
        $q = $this->db->get_where('date_format', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGiftCardByID($id)
    {
        $q = $this->db->get_where('gift_cards', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGiftCardByNO($no)
    {
        $q = $this->db->get_where('gift_cards', ['card_no' => $no], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getNotifications()
    {
        $date = date('Y-m-d H:i:s', time());
        $this->db->where('from_date <=', $date);
        $this->db->where('till_date >=', $date);
        if (!$this->Owner) {
            if ($this->Supplier) {
                $this->db->where('scope', 4);
            } elseif ($this->Customer) {
                $this->db->where('scope', 1)->or_where('scope', 3);
            } elseif (!$this->Customer && !$this->Supplier) {
                $this->db->where('scope', 2)->or_where('scope', 3);
            }
        }
        $q = $this->db->get('notifications');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getPriceGroupsByType($type)
    {
        $q = $this->db->get_where('price_groups', ['type' => $type]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPriceGroupByID($id)
    {
        $q = $this->db->get_where('price_groups', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPromosItemByID($id, $type)
    {
        $this->db->select('*');
        $this->db->where(array('promos_id'=>$id,'type'=> $type));
        $q = $this->db->get('promos_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getProductCommissionByID($id, $type = "commission")
    {
        
        $this->db->select('*')
            ->join('product_prices', 'price_groups.id=product_prices.price_group_id', 'left');
        
        $q = $this->db->get_where('price_groups', ['product_id' => $id,'type'=>$type]);
        if ($q->num_rows() > 0) {
             return $q->row();
        }
        return false;
    }
    public function getProductByStockItemId($id, $scan = null)
    {
        
        $this->db->select('stock_count_items.*')
            ->join('products', 'products.id=stock_count_items.product_id', 'left');
        
        $q = $this->db->get_where('stock_count_items', ['stock_count_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getProductByStockItem($id, $scan = null)
    {
        if (!empty($scan)) {
            $this->db->where('counted !=', 0);
        }
        
        $q = $this->db->get_where('stock_count_items', ['stock_count_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getStockById($id)
    {
        $q = $this->db->get_where('stock_counts', ['id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductComboItems($pid, $warehouse_id = null)
    {
        $this->db->select('
                products.id as id, combo_items.item_code as code, products.name as name, products.type as type,
                combo_items.quantity as qty, 
                combo_items.quantity as width, 
                combo_items.unit_price as price,  
                combo_items.unit_price as unit_price,
                warehouses_products.quantity as quantity,
                1 as height
            ')
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

    public function getProductGroupPrice($product_id, $group_id)
    {
        $q = $this->db->get_where('product_prices', ['price_group_id' => $group_id, 'product_id' => $product_id], 1);
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

    public function getAllProductVariants(){
        $this->db->select('product_variants.*');
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPurchaseByID($id)
    {
        $q = $this->db->get_where('purchases', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchasedItem($clause, $expiry = null, $expiry_date = null)
    {
        $orderby = empty($this->Settings->accounting_method) ? 'asc' : 'desc';
        if ($this->Settings->product_expiry && $this->Settings->fefo) {
            $this->db->order_by('expiry', 'asc');
        } else {
            $this->db->order_by('date', $orderby);
            $this->db->order_by('purchase_id', $orderby);
        }
        if (!isset($clause['option_id']) || empty($clause['option_id'])) {
            $this->db->group_start()->where('option_id', null)->or_where('option_id', 0)->group_end();
        }
        if(isset($clause["expiry"]) && $clause["expiry"] != "0000-00-00" && $clause["expiry"] == null){
            unset($clause['purchase_id']);
            unset($clause['transfer_id']);
        } else {
            unset($clause['purchase_id']);
            unset($clause['transfer_id']);
        }
        if($expiry_date != null && $expiry_date != "0000-00-00"){
            $clause['expiry'] = $expiry_date;
        }
        $q = $this->db->get_where('purchase_items', $clause);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchasedItems($product_id, $warehouse_id, $option_id = null, $nonPurchased = false, $expiry = null)
    {
        $orderby = empty($this->Settings->accounting_method) ? 'asc' : 'desc';
        if ($this->Settings->product_expiry && $this->Settings->fefo) {
            $this->db->order_by('expiry', 'asc');
        } else {
            $this->db->order_by('date', $orderby);
            $this->db->order_by('purchase_id', $orderby);
        }
        $this->db->select('id, quantity, quantity_balance, net_unit_cost, base_unit_cost, unit_cost, item_tax');
        $this->db->where('product_id', $product_id)->where('warehouse_id', $warehouse_id)->where('quantity_balance !=', 0);
        if($expiry != '' && $expiry != 'null' && $expiry != '0000-00-00' && $expiry != NULL){
            $this->db->where('expiry', $expiry);
        }  
        if (!isset($option_id) || empty($option_id)) {
            $this->db->group_start()->where('option_id', null)->or_where('option_id', 0)->group_end();
        } else {
            $this->db->where('option_id', $option_id);
        }
        if ($nonPurchased) {
            $this->db->group_start()->where('purchase_id !=', null)->or_where('transfer_id !=', null)->group_end();
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $this->db->group_by('id');
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPurchasePayments($purchase_id)
    {
        $q = $this->db->get_where('payments', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getRandomReference($len = 12)
    {
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            $result .= mt_rand(0, 9);
        }

        if ($this->getSaleByReference($result)) {
            $this->getRandomReference();
        }
        return $result;
    }

    public function getCustomerSale()
    {
        $this->db->select("customer_id as id, customer as name");
        $this->db->where('sales.payment_status <>', 'paid');
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getReference($field, $biller_id = false)
    {
        if ($biller_id) {
            $biller = $this->site->getCompanyByID($biller_id);
        }
        $q = $this->db->get_where('order_ref', ['ref_id' => $this->Settings->order_ref_id], 1);
        // $q = $this->db->get_where('order_ref', ['ref_id' => '1'], 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            switch ($field) {
                case 'tax_sale':
                    $prefix = isset($biller->code) ? 'TAX'.$biller->code: 'Tax';
                    break;
                case 'main':
                    $prefix = $this->Settings->mainten_prefix;
                    break;
                case 'so':
                    $prefix = $this->Settings->sales_prefix;
                    break;
                case 'st':
                    $prefix = $this->Settings->sales_tax_prefix;
                    break;
                case 'sr':
                    $prefix = $this->Settings->sales_order_prefix;
                    break;
                case 'pos':
                //    $prefix = isset($this->Settings->sales_prefix) ? $this->Settings->sales_prefix . '/POS' : '';
                    $prefix = isset($this->Settings->sales_prefix) ? 'POS' : '';
                    break;
                case 'qu':
                    $prefix = $this->Settings->quote_prefix;
                    break;
                case 'pr':
                    $prefix = $this->Settings->purchase_request_prefix;
                    break;
                case 'po':
                    $prefix = $this->Settings->purchase_order_prefix;
                    break;
                case 'p':
                    $prefix = $this->Settings->purchase_prefix;
                    break;
                case 'to':
                    $prefix = $this->Settings->transfer_prefix;
                    break;
                case 'do':
                    $prefix = $this->Settings->delivery_prefix;
                    break;
                case 'pay':
                    $prefix = $this->Settings->payment_prefix;
                    break;
                case 'ppay':
                    $prefix = $this->Settings->ppayment_prefix;
                    break;
                case 'exb':
                    $prefix = $this->Settings->expense_budget_prefix;
                    break;
                case 'ex':
                    $prefix = $this->Settings->expense_prefix;
                    break;
                case 'bg':
                    $prefix = $this->Settings->budget_prefix;
                    break;
                case 're':
                    $prefix = $this->Settings->return_prefix;
                    break;
                case 'rep':
                    $prefix = $this->Settings->returnp_prefix;
                    break;
                case 'qa':
                    $prefix = $this->Settings->qa_prefix;
                    break;
                case 'loan':
                    $prefix = $this->Settings->loan_prefix;
                    break;
                case 'jr':
                    $prefix = $this->Settings->journal_prefix;
                    break;
                case 'pp':
                    $prefix = $this->Settings->purchase_prefix;
                    break;
                case 'bill':
                    $prefix = $this->Settings->bill_prefix;
                    break;
                case 'asset':
                    $prefix = $this->Settings->asset_prefix;
                    break;
                case 'inventory':
                    $prefix = $this->Settings->inventory_prefix;
                    break;
                case 'sd':
                    $prefix = $this->Settings->supplier_deposit;
                    break;
                case 'tl':
                    $prefix = $this->Settings->take_leave_prefix;
                    break;
                case 'dp':
                    $prefix = $this->Settings->depreciation_prefix;
                    break;
                case 'borrower':
                    $prefix = $this->Settings->depreciation_prefix;
                    break;
                case 'es':
                    $prefix = $this->Settings->stock_using_prefix;
                    break;
                case 'esr':
                    $prefix = $this->Settings->stock_using_return_prefix;
                    break;
                case 'pn':
                    $prefix = $this->Settings->project_plan_prefix;
                    break;
                case 'str':
                    $prefix = $this->Settings->stock_received_prefix;
                    break;
                case 'pw':
                    $prefix = $this->Settings->pawn_prefix;
                    break;
                case 'app':
                    $prefix = $this->Settings->app_prefix;
                    break;
                case 'ln':
                    $prefix = $this->Settings->loan_prefix;
                    break;
                case 'sp':
                    $prefix = $this->Settings->sales_store_prefix;
                    break;
                case 'spr':
                    $prefix = $this->Settings->sales_store_prefix;
                    break;
                case 'sav':
                    $prefix = $this->Settings->sav_prefix;
                    break;
                case 'sav_tr':
                    $prefix = $this->Settings->sav_tr_prefix;
                    break;
                 case 'esq':
                    $prefix = $this->Settings->edit_sale_request_prefix;
                    break;
                 case 'inst':
                    $prefix = $this->Settings->installment_prefix;
                    break;
                case 'crn':
                    $prefix = $this->Settings->credit_note_prefix;
                    break; 
                case 'csm':
                    $prefix = $this->Settings->csm_prefix;
                    break;
                case 'sh_exm':
                    $prefix = $this->Settings->examination_prefix;
                    break;
                case 'sh_std_code':
                    $prefix = $this->Settings->student_prefix;
                    break;
                case 'sh_std_adm':
                    $prefix = $this->Settings->student_admission_prefix;
                    break;
                case 'pw':
                    $prefix = 'PW';
                    break;  
                case 'pwr':
                    $prefix = 'PWR';
                    break;
                case 'pwp':
                     $prefix = 'PWP';
                     break;  
                case 'pwpr':
                     $prefix = 'PWPR';
                     break;
                case 'pwps':
                     $prefix = 'PWS';
                     break;
                case 'ren':
                     $prefix = 'Ren';
                     break;
                case 'stc':
                     $prefix = 'STC';
                     break;
                case 'crw':
                     $prefix = $this->Settings->customer_reward_prefix;
                     break;
                case 'srw':
                     $prefix = $this->Settings->supplier_reward_prefix;
                     break;
                case 'cs':
                    $prefix = $this->Settings->customer_stock_prefix;
                    break;
                case 'repair':
                    $prefix = $this->Settings->repair_prefix;
                    break;
                case 'check':
                    $prefix = $this->Settings->check_prefix;
                    break;
                case 'cdn':
                    $prefix = $this->Settings->cdn_prefix;
                    break;
                case 'fuel':
                    $prefix = $this->Settings->fuel_prefix;
                    break;
                case 'csale':
                    $prefix = $this->Settings->csale_prefix;
                    break;  
                case 'cfuel':
                    $prefix = $this->Settings->cfuel_prefix;
                    break;  
                case 'cerror':
                    $prefix = $this->Settings->cer_prefix;
                    break;
                case 'gen':
                    $prefix = $this->Settings->generate_prefix;
                    break;
                case 'cre':
                    $prefix = $this->Settings->credit_note_prefix;
                    break;
                case 'deb':
                    $prefix = $this->Settings->debit_note_prefix;
                    break;
                    
                default:
                    $prefix = '';
            }
            if ($this->Settings->reference_reset == 1) {
                if (date("Y",strtotime($ref->date)) !== date("Y")) {
                    $order_ref = array();
                    foreach($ref as $index => $value){
                        $order_ref[$index] = 1;
                    }
                    unset($order_ref['prefix']);
                    unset($order_ref['bill_id']);
                    unset($order_ref['ref_id']);
                    unset($order_ref['bill_prefix']);
                    unset($order_ref['supplier']);
                    unset($order_ref['customer']);
                    $order_ref['date'] = date("Y-m-d");
                    $this->db->update('order_ref', $order_ref, array('bill_id' => $biller_id));
                    $ref->{$field} = 1;
                }
            } elseif ($this->Settings->reference_reset == 2) {
                if (date("Y-m",strtotime($ref->date)) !== date("Y-m")) {
                    $order_ref = array();
                    foreach($ref as $index => $value){
                        $order_ref[$index] = 1;
                    }
                    unset($order_ref['prefix']);
                    unset($order_ref['bill_id']);
                    unset($order_ref['ref_id']);
                    unset($order_ref['bill_prefix']);
                    unset($order_ref['supplier']);
                    unset($order_ref['customer']);
                    $order_ref['date'] = date("Y-m-d");
                    $this->db->update('order_ref', $order_ref, array('bill_id' => $biller_id));
                    $ref->{$field} = 1;
                }
            }
            $ref_no = $prefix;
            if ($this->Settings->reference_format == 1) {
                $ref_no .= date('y') . '/' . sprintf('%04s', $ref->{$field});
            } elseif ($this->Settings->reference_format == 2) {
                $ref_no .= date('y') . '/' . date('m') . '/' . sprintf('%04s', $ref->{$field});
            } elseif ($this->Settings->reference_format == 3) {
                $ref_no .= sprintf('%04s', $ref->{$field});
            } else {
                $ref_no .= $this->getRandomReference();
            }
            if ($biller_id) {
                $q = $this->db->get_where('order_ref', array('bill_id' => $biller_id), 1);
                if ($q->num_rows() > 0) {
                    if ($q->row()->bill_prefix!='') {
                        $ref_no =$q->row()->bill_prefix.'/'.$ref_no;
                    }
                }
                $this->updateReference($field, $biller_id);
            }
            return $ref_no;
        }
        return false;
    }

    public function updateReference($field, $bill_id = false)
    {
        if (!empty($bill_id)) {
            $q = $this->db->get_where('order_ref', array('bill_id' => $bill_id), 1);
            if ($q->num_rows() > 0) {
                $ref = $q->row();
                $this->db->update('order_ref', array($field => $ref->{$field} + 1), array('bill_id' => $bill_id));
                return TRUE;
            }
        } else {
            $settings = $this->getSettings();
            $q = $this->db->get_where('order_ref', ['ref_id' => $settings->order_ref_id], 1);
            if ($q->num_rows() > 0) {
                $ref = $q->row();
                $this->db->update('order_ref', [$field => $ref->{$field} + 1], ['ref_id' => $settings->order_ref_id]);
                return true;
            }
        }
        return false;
    }

    public function getAllInterestmethod() {
        $q = $this->db->get('interest_method');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllInterestperiod() {
        $q = $this->db->get('interest_period');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    /*
    public function auto_number($field){
        
        $q = $this->db->get_where('order_ref', ['ref_id' => '1'], 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            switch ($field) {
                case 'asset':
                    $prefix = $this->Settings->asset_prefix;
                    break;
                case 'inventory':
                    $prefix = $this->Settings->inventory_prefix ;
                    break;
                default:
                    $prefix = '';
            }
            $ref_no = $prefix;
            $ref_no .= sprintf('%04s', $ref->{$field});
           
            return $ref_no;
        }
        return false;
    }*/

    public function getSaleByID($id)
    {
        $q = $this->db->get_where('sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSaleByReference($ref)
    {
        $this->db->like('reference_no', $ref, 'both');
        $q = $this->db->get('sales', 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function CheckedSaleReference($ref, $auto_ref)
    {
        $this->db->where('reference_no', $ref);
        $q = $this->db->get('sales', 1);
        if ($q->num_rows() > 0) {
            return $auto_ref;
        } else {
            return $ref;
        }
        return false;
    }
    public function CheckedPaymentReference($ref, $auto_ref)
    {
        $this->db->where('reference_no', $ref);
        $q = $this->db->get('payments', 1);
        if ($q->num_rows() > 0) {
            return $auto_ref;
        } else {
            return $ref;
        }
        return false;
    }

    public function getSalePayments($sale_id)
    {
        $this->db->where('transaction is NULL');
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getRewardExchangePayments($reward_exchange_id)
    {
        $this->db->where('transaction is NULL');
        $q = $this->db->get_where('payments', ['reward_exchange_id' => $reward_exchange_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSmsSettings()
    {
        $q = $this->db->get('sms_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSubCategories($parent_id)
    {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function testGetSubCategories($parent_id)
    {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get('test_category');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getTestSubCategories($parent_id)
    {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get('test_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUnitByProId_($id, $base_unit = null)
    {   
        $unit = $this->getAllBaseUnits();
        $this->db->select('products.id as product_id,units.id as unit_id,cost,price,units.code as code,units.name as name,products.code as pro_code,products.unit as pro_unit')
            ->join('products', 'units.id=products.unit', 'left');
        $this->db->where('products.id', $id);
        if($base_unit){
            $this->db->or_where('units.base_unit', $base_unit); 
        }else{
            $this->db->or_where('units.base_unit', $unit[0]->id); 
        }
        $this->db->group_by('units.id')->order_by('units.id asc');
        $q = $this->db->get('units');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getUnitByProId($id)
    {
        $this->db->select('
                cost_price_by_units.*, 
                cost_price_by_units.price as unit_price,
                units.*, 
                products.code as pro_code, 
                products.unit as pro_unit
            ')
            ->join('units', 'units.id=cost_price_by_units.unit_id', 'left')
            ->join('products', 'products.id = cost_price_by_units.product_id', 'left');
        $this->db->where('product_id', $id)->order_by('units.id asc');
        // $q = $this->db->get_where('cost_price_by_units', ['product_id'=> $id]);
        $q = $this->db->get('cost_price_by_units');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getUnitByProId_PG($id, $price_group_id)
    {
        $this->db->select("
                cost_price_by_units.*, units.*,
                COALESCE({$this->db->dbprefix('product_prices')}.price, {$this->db->dbprefix('cost_price_by_units')}.price) AS price,
                products.code as pro_code, 
                products.unit as pro_unit
            ")
            ->join('units', 'units.id=cost_price_by_units.unit_id', 'left')
            ->join('products', 'products.id = cost_price_by_units.product_id', 'left')
            ->join('product_prices', 
            "
                product_prices.product_id = cost_price_by_units.product_id AND 
                product_prices.unit_id = cost_price_by_units.unit_id AND 
                product_prices.price_group_id =  '{$price_group_id}' ", "left");
        $this->db->where('cost_price_by_units.product_id', $id)->order_by('units.id asc');
        $q = $this->db->get('cost_price_by_units');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getTaxRateByID($id)
    {
        $q = $this->db->get_where('tax_rates', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUnitByCode($code)
    {
        $q = $this->db->get_where('units', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUnitByID($id)
    {
        $q = $this->db->get_where('units', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUnitProductByID($unite_id, $product_id)
    {
        $q = $this->db->get_where('cost_price_by_units', ['unit_id' => $unite_id, 'product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUnits()
    {
         $q = $this->db->get("units");
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }

    public function getUnitsByBUID($base_unit)
    {
        $this->db->where('id', $base_unit)->or_where('base_unit', $base_unit)
        ->group_by('id')->order_by('id asc');
        $q = $this->db->get('units');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUnitsByProductID($id)
    {
        if (!empty($id)) {
            if ($product = $this->getProductByID($id)) {
                return $this->getUnitsByBUID($product->unit);
            }
        }
        return false;
    }

    public function getOrderByProID($pro_id)
    {
        $this->db->where('item_id', $pro_id)->order_by('id asc');
        $q = $this->db->get('audit_order_item');
        if ($q->num_rows() > 0) {
            
            return $q->row();
        }
        return false;
    }

    public function getUpcomingEvents()
    {
        $dt = date('Y-m-d');
        $this->db->where('start >=', $dt)->order_by('start')->limit(5);
        if ($this->Settings->restrict_calendar) {
            $this->db->where('user_id', $this->session->userdata('user_id'));
        }

        $q = $this->db->get('calendar');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUser($id = null)
    {
        if ($id==null) {
            $id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('users', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getcustomfield($fiel) 
    {
        $this->db->select(" * FROM bpas_custom_field 
            WHERE active = 1 AND parent_id = (SELECT id FROM bpas_custom_field WHERE name = '".$fiel."' OR code ='".$fiel."') ");
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }

  

    public function getschedules() 
    {
        $this->db->select('*'); 
        $this->db->from('bpas_event_schedule'); 
        $this->db->where('status', 'pending');
        $q = $this->db->get(); 
        if ($q->num_rows() > 0) {
            return $q->result(); 
        }
        return FALSE; 
    }
    public function getschedulesedit() 
    {
        $this->db->select('*'); 
        $this->db->from('bpas_event_schedule'); 
        $q = $this->db->get(); 
        if ($q->num_rows() > 0) {
            return $q->result(); 
        }
        return FALSE; 
    }
    

    // public function  
    // {
        
    //     $this->db->select('*'); // Select all columns
    //     $this->db->from('bpas_event_schedule'); // Specify the table
    //     $q = $this->db->get(); // Execute the query
    //     if ($q->num_rows() > 0) {
    //         return $q->result(); // Return the result if there are rows
    //     }
    //     return FALSE; // Return FALSE if there are no rows
    // }

  public function getschedulesByID($id) 
    {
        $this->db->select(" * FROM bpas_event_schedule 
            WHERE id = $id ");
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }


    public function getcustomfieldBycode($fiel)
    {
        $this->db->select(" * FROM bpas_custom_field 
            WHERE active = 1 AND code = '".$fiel."'");
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getcustomfieldByName($fiel)
    {
        $this->db->select(" * FROM bpas_custom_field 
            WHERE active = 1 AND name = '".$fiel."'");
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getcustomfieldById($id)
    {
        $q = $this->db->get_where('custom_field', ['active'=>1,'id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUserGroup($user_id = false)
    {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $group_id = $this->getUserGroupID($user_id);
        $q        = $this->db->get_where('groups', ['id' => $group_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllGroup()
    {
        $q = $this->db->get('groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUserGroupID($user_id = false)
    {
        $user = $this->getUser($user_id);
        return $user->group_id;
    }

    public function getWarehouseByID($id)
    {
        $q = $this->db->get_where('warehouses', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getMultiWarehouseByID($id)
    {
        $q = $this->db->query(' SELECT * FROM bpas_warehouses WHERE id IN (' . $id . ') ');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;        
            }
            return $data;
        }
        return FALSE;
    }

    public function getWarehouseProduct($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseProducts($product_id, $warehouse_id = null)
    {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getWarehouseProductsVariants($option_id, $warehouse_id = null)
    {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('warehouses_products_variants', ['option_id' => $option_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function item_costing($item, $pi = null)
    {
        $item_quantity = $pi ? $item['aquantity'] : $item['quantity'];
        if (!isset($item['option_id']) || empty($item['option_id']) || $item['option_id'] == 'null') {
            $item['option_id'] = null;
        }
        $data = $this->getProductByID($item['product_id']);
        if ($this->Settings->accounting_method != 2 && !$this->Settings->overselling) {
            if ($this->getProductByID($item['product_id'])) {
                if ($item['product_type'] == 'standard') {
                    $unit                   = $this->getUnitByID($item['product_unit_id']);
                    $item['net_unit_price'] = $this->convertToBase($unit, $item['net_unit_price']);
                    $item['unit_price']     = $this->convertToBase($unit, $item['unit_price']);
                    $cost                   = $this->calculateCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity, (isset($item['expiry']) ? $item['expiry'] : null));
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $pr = $this->getProductByCode($combo_item->code);
                        if ($pr->tax_rate) {
                            $pr_tax = $this->getTaxRateByID($pr->tax_rate);
                            if ($pr->tax_method) {
                                $item_tax       = $this->bpas->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / (100 + $pr_tax->rate));
                                $net_unit_price = $combo_item->unit_price - $item_tax;
                                $unit_price     = $combo_item->unit_price;
                            } else {
                                $item_tax       = $this->bpas->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / 100);
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price     = $combo_item->unit_price + $item_tax;
                            }
                        } else {
                            $net_unit_price = $combo_item->unit_price;
                            $unit_price     = $combo_item->unit_price;
                        }
                        if ($pr->type == 'standard') {
                            $cost[] = $this->calculateCost($pr->id, $item['warehouse_id'], $net_unit_price, $unit_price, ($combo_item->qty * $item['quantity']), $pr->name, null, $item_quantity, (isset($item['expiry']) ? $item['expiry'] : null));
                        } else {
                            $cost[] = [['date' => date('Y-m-d'), 'product_id' => $pr->id, 'expiry'=>$item['expiry'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => ($combo_item->qty * $item['quantity']), 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $combo_item->unit_price, 'sale_unit_price' => $combo_item->unit_price, 'quantity_balance' => (0 - $item_quantity), 'inventory' => null]];
                        }
                    }
                } else {
                    $cost = [
                        [
                            'date' => date('Y-m-d'), 
                            'product_id' => $item['product_id'], 
                            'expiry' => isset($item['expiry'])?$item['expiry']:null, 
                            'sale_item_id' => 'sale_items.id', 
                            'purchase_item_id' => null, 
                            'quantity' => $item['quantity'], 
                            'purchase_net_unit_cost' => 0, 
                            'purchase_unit_cost' => 0, 
                            'sale_net_unit_price' => $item['net_unit_price'], 
                            'sale_unit_price' => $item['unit_price'], 
                            'quantity_balance' => (0 - $item_quantity), 
                            'inventory' => null,
                            'service_cost' => $data->cost,
                        ]
                    ];
               }
            } elseif ($item['product_type'] == 'manual') {
                $cost = [['date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => null, 'inventory' => null]];
            }
        } else {
            if ($this->getProductByID($item['product_id'])) {
                if ($item['product_type'] == 'standard') {
                    $cost = $this->calculateAVCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity, (isset($item['expiry']) ? $item['expiry'] : null));
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $pr = $this->getProductByCode($combo_item->code);
                        if ($pr->tax_rate) {
                            $pr_tax = $this->getTaxRateByID($pr->tax_rate);
                            if ($pr->tax_method) {
                                $item_tax       = $this->bpas->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / (100 + $pr_tax->rate));
                                $net_unit_price = $combo_item->unit_price - $item_tax;
                                $unit_price     = $combo_item->unit_price;
                            } else {
                                $item_tax       = $this->bpas->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / 100);
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price     = $combo_item->unit_price + $item_tax;
                            }
                        } else {
                            $net_unit_price = $combo_item->unit_price;
                            $unit_price     = $combo_item->unit_price;
                        }
                        $cost[] = $this->calculateAVCost($combo_item->id, $item['warehouse_id'], $net_unit_price, $unit_price, ($combo_item->qty * $item['quantity']), $item['product_name'], $item['option_id'], $item_quantity, (isset($item['expiry']) ? $item['expiry'] : null));
                    }
                } else {
                    $cost = [['date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'expiry'=>$item['expiry'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => null, 
                        'inventory' => null,
                        'service_cost' => $data->cost,
                    ]];
                }
            } elseif ($item['product_type'] == 'manual') {
                $cost = [['date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => null, 'inventory' => null]];
            }
        }
        // $this->syncQuantity_13_05_21($item['product_id']);
        // var_dump($cost);
        return $cost;
    }
    public function getStockTypeByID($id)
    {
        $q = $this->db->get_where('stock_type', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function modal_js()
    {
        return '<script type="text/javascript">' . file_get_contents($this->data['assets'] . 'js/modal.js') . '</script>';
    }
   
    public function setPurchaseItem($clause, $qty)
    {
        if ($product = $this->getProductByID($clause['product_id'])) {
            if ($pi = $this->getPurchasedItem($clause)) {
                if ($pi->quantity_balance > 0) {
                    $quantity_balance = $pi->quantity_balance + $qty;
                    log_message('error', 'More than zero: ' . $quantity_balance . ' = ' . $pi->quantity_balance . ' + ' . $qty . ' PI: ' . print_r($pi, true));
                } else {
                    $quantity_balance = $pi->quantity_balance + $qty;
                    log_message('error', 'Less than zero: ' . $quantity_balance . ' = ' . $pi->quantity_balance . ' + ' . $qty . ' PI: ' . print_r($pi, true));
                }
                return $this->db->update('purchase_items', ['quantity_balance' => $quantity_balance], ['id' => $pi->id]);
            } else {
                $unit                        = $this->getUnitByID($product->unit);
                $clause['product_unit_id']   = $product->unit;
                $clause['product_unit_code'] = $unit->code;
                $clause['product_code']      = $product->code;
                $clause['product_name']      = $product->name;
                $clause['purchase_id']       = $clause['transfer_id']        = $clause['item_tax']      = null;
                $clause['net_unit_cost']     = $clause['real_unit_cost']     = $clause['unit_cost']     = $product->cost;
                $clause['quantity_balance']  = $clause['quantity']  = $clause['unit_quantity']  = $clause['quantity_received']  = $qty;
                $clause['subtotal']          = ($product->cost * $qty);
                if (isset($product->tax_rate) && $product->tax_rate != 0) {
                    $tax_details           = $this->site->getTaxRateByID($product->tax_rate);
                    $ctax                  = $this->calculateTax($product, $tax_details, $product->cost);
                    $item_tax              = $clause['item_tax']              = $ctax['amount'];
                    $tax                   = $clause['tax']                   = $ctax['tax'];
                    $clause['tax_rate_id'] = $tax_details->id;
                    if ($product->tax_method != 1) {
                        $clause['net_unit_cost'] = $product->cost - $item_tax;
                        $clause['unit_cost']     = $product->cost;
                    } else {
                        $clause['net_unit_cost'] = $product->cost;
                        $clause['unit_cost']     = $product->cost + $item_tax;
                    }
                    $pr_item_tax = $this->bpas->formatDecimal($item_tax * $clause['unit_quantity']);
                    if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                        $clause['gst']  = $gst_data['gst'];
                        $clause['cgst'] = $gst_data['cgst'];
                        $clause['sgst'] = $gst_data['sgst'];
                        $clause['igst'] = $gst_data['igst'];
                    }
                    $clause['subtotal'] = (($clause['net_unit_cost'] * $clause['unit_quantity']) + $pr_item_tax);
                }
                $clause['status']    = 'received';
                $clause['date']      = date('Y-m-d');
                $clause['option_id'] = !empty($clause['option_id']) && is_numeric($clause['option_id']) ? $clause['option_id'] : null;
                log_message('error', 'Why else: ' . print_r($clause, true));
                return $this->db->insert('purchase_items', $clause);
            }
        }
        return false;
    }

    public function getItemBySaleID($id)
    {
        $q = $this->db->get_where('sale_items', ['sale_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getReferenceByID($id)
    {
        $q = $this->db->get_where('order_ref', ['ref_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReferenceByYear($year)
    {
        $q = $this->db->get_where('order_ref', ['YEAR(date)' => $year], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addReference($data = [])
    {
        if ($this->db->insert('order_ref', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function syncProductQty($product_id, $warehouse_id, $status = null) 
    {
        $balance_qty    = $this->getBalanceQuantity($product_id);
        $module_type    = $this->getProductByID($product_id);
        $wh_balance_qty = $this->getBalanceQuantity($product_id, $warehouse_id);
        if(!empty($module_type) && $module_type->module_type == 'property'){
            if($balance_qty == null){
                $balance_qty = 1;   
            }
            if($status == 'booking'){
                $balance_qty = 2;   
            }
        }
        if ($this->db->update('products', ['quantity' => $balance_qty], ['id' => $product_id])) {
            if ($this->getWarehouseProducts($product_id, $warehouse_id)) {
                $this->db->update('warehouses_products', ['quantity' => $wh_balance_qty], ['product_id' => $product_id, 'warehouse_id' => $warehouse_id]);
            } else {
                if (!$wh_balance_qty) {
                    $wh_balance_qty = 0;
                }
                $product = $this->site->getProductByID($product_id);
                if ($product) {
                    $this->db->insert('warehouses_products', ['quantity' => $wh_balance_qty, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'avg_cost' => $product->cost]);
                } else {
                    $this->db->insert('warehouses_products', ['quantity' => $wh_balance_qty, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id]);
                }
            }

            return true;
        }
        return false;
    }
    public function getCommissionsBySaleID($id)
    {   $this->db->select('SUM(COALESCE(commission, 0)) as commission', false);
        $q = $this->db->get_where('sale_items', ['sale_id' => $id]);
        if ($q->num_rows() > 0) {
             return $q->row();
        }

        return false;
    }
    public function getGroupCustomerByCustomerID($id)
    {   $this->db->select('customer_group_id,price_group_id', false);
        $q = $this->db->get_where('companies', ['id' => $id]);
        if ($q->num_rows() > 0) {
             return $q->row();
        }

        return false;
    }
    public function getAllSales()
    {
        $q = $this->db->get_where('sales',['buy_status'=>'booking']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllGroupPrice()
    {   
        $this->db->order_by('price','DESC');
        $q = $this->db->get('product_prices');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function syncPurchaseItems($data = [])
    {
        
        if (!empty($data)) {
            foreach ($data as $items) {
                foreach ($items as $item) {
                    if (isset($item['pi_overselling'])) {
                        unset($item['pi_overselling']);
                        $option_id = (isset($item['option_id']) && !empty($item['option_id'])) ? $item['option_id'] : null;
                        $clause    = [
                            'purchase_id'  => null, 
                            'transfer_id'  => null, 
                            'product_id'   => $item['product_id'], 
                            'warehouse_id' => $item['warehouse_id'], 
                            'option_id'    => $option_id, 
                            'expiry'       => isset($item['expiry']) ? $item['expiry'] : null
                        ];
                        if ($pi = $this->getPurchasedItem($clause)) {
                            $quantity_balance = $pi->quantity_balance + $item['quantity_balance'];
                            $this->db->update('purchase_items', ['quantity_balance' => $quantity_balance], ['id' => $pi->id]);
                        } else {
                            $product = $this->getProductByID($clause['product_id']);
                            $unit    = $this->getUnitByID($product->unit);
                            $clause['product_unit_id']   = $product->unit;
                            $clause['product_unit_code'] = $unit->code;
                            $clause['product_code']      = $product->code;
                            $clause['product_name']      = $product->name;
                            $clause['purchase_id']       = $clause['transfer_id']        = $clause['item_tax']       = null;
                            $clause['net_unit_cost']     = $clause['real_unit_cost']     = $clause['unit_cost']      = $product->cost;
                            $clause['quantity_balance']  = $clause['quantity']           = $clause['unit_quantity']  = $clause['quantity_received'] = $item['quantity_balance'];
                            $clause['option_id']         = !empty($clause['option_id']) && is_numeric($clause['option_id']) ? $clause['option_id'] : null;
                            $clause['subtotal']          = ($product->cost * $item['quantity_balance']);
                            if (isset($product->tax_rate) && $product->tax_rate != 0) {
                                $tax_details           = $this->site->getTaxRateByID($product->tax_rate);
                                $ctax                  = $this->calculateTax($product, $tax_details, $product->cost);
                                $item_tax              = $clause['item_tax']              = $ctax['amount'];
                                $tax                   = $clause['tax']                   = $ctax['tax'];
                                $clause['tax_rate_id'] = $tax_details->id;
                                if ($product->tax_method != 1) {
                                    $clause['net_unit_cost'] = $product->cost - $item_tax;
                                    $clause['unit_cost']     = $product->cost;
                                } else {
                                    $clause['net_unit_cost'] = $product->cost;
                                    $clause['unit_cost']     = $product->cost + $item_tax;
                                }
                                $pr_item_tax = $this->bpas->formatDecimal($item_tax * $clause['unit_quantity']);
                                if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                                    $clause['gst']  = $gst_data['gst'];
                                    $clause['cgst'] = $gst_data['cgst'];
                                    $clause['sgst'] = $gst_data['sgst'];
                                    $clause['igst'] = $gst_data['igst'];
                                }
                                $clause['subtotal'] = (($clause['net_unit_cost'] * $clause['unit_quantity']) + $pr_item_tax);
                            }
                            $clause['status']    = 'received';
                            $clause['date']      = date('Y-m-d');
                            $clause['option_id'] = !empty($clause['option_id']) && is_numeric($clause['option_id']) ? $clause['option_id'] : null;
                            $this->db->insert('purchase_items', $clause);
                        }
                    } elseif (!isset($item['overselling']) || empty($item['overselling'])) {
                        if ($item['inventory']) {
                            $this->db->update('purchase_items', ['quantity_balance' => $item['quantity_balance']], ['id' => $item['purchase_item_id']]);
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function syncPurchasePayments($id)
    {
        $purchase = $this->getPurchaseByID($id);
        $paid     = 0;
        if ($payments = $this->getPurchasePayments($id)) {
            foreach ($payments as $payment) {
                $paid += $payment->amount + $payment->discount;
            }
        }
        $payment_status = $paid <= 0 ? 'pending' : $purchase->payment_status;
        if ($this->bpas->formatDecimal($purchase->grand_total) > $this->bpas->formatDecimal($paid) && $paid > 0) {
            $payment_status = 'partial';
        // } elseif ($this->bpas->formatDecimal($purchase->grand_total) <= $this->bpas->formatDecimal($paid)) {
        } elseif (($this->bpas->formatDecimal($purchase->grand_total) < $this->bpas->formatDecimal($paid) && $purchase->status != 'returned') || ($this->bpas->formatDecimal($purchase->grand_total) == $this->bpas->formatDecimal($paid))) {
            $payment_status = 'paid';
        }
        if ($this->db->update('purchases', ['paid' => $paid, 'payment_status' => $payment_status], ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function syncQuantity($sale_id = null, $purchase_id = null, $oitems = null, $product_id = null, $sales_status = null, $store_sale_id = null, $purchase_item_id = null)
    {
        if ($sale_id) {
            $tmsale_items       = $this->getAllSaleItems($sale_id);
            $tmsale_addon_items = $this->getAllAddonSaleItems($sale_id);
            $sale_items         = $tmsale_items;
            if (!empty($tmsale_addon_items)) {
                $sale_items = array_merge($tmsale_items, $tmsale_addon_items);
            }
           
            foreach ($sale_items as $item) {
                $warehouse = $this->site->getWarehouseByID($item->warehouse_id);
                if ($item->product_type == 'standard') {
                    $this->syncProductQty($item->product_id, $item->warehouse_id , $sales_status);
                    if (isset($item->option_id) && !empty($item->option_id)) {
                        $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    }
                } elseif ($item->product_type == 'combo') {
                    $wh          = ($this->Settings->overselling && (!empty($warehouse) && $warehouse->overselling)) ? null : $item->warehouse_id;
                    $combo_items = $this->getProductComboItems($item->product_id, $wh);
                    foreach ($combo_items as $combo_item) {
                        if ($combo_item->type == 'standard') {
                            $this->syncProductQty($combo_item->id, $item->warehouse_id, $sales_status);
                        }
                    }
                }
            }
        } elseif ($purchase_id) {
            if ($purchase_item_id) {
                $purchase_items[0] = $this->getPurchaseItemByID($purchase_item_id);
            }
            $purchase_items = $this->getAllPurchaseItems($purchase_id);
            foreach ($purchase_items as $item) {
                $this->syncProductQty($item->product_id, $item->warehouse_id);
                $this->checkOverSold($item->product_id, $item->warehouse_id);
                if (isset($item->option_id) && !empty($item->option_id)) {
                    $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    $this->checkOverSold($item->product_id, $item->warehouse_id, $item->option_id);
                }
            }
        } elseif ($oitems) {
            foreach ($oitems as $item) {
                if (isset($item->product_type)) {
                    if ($item->product_type == 'standard') {
                        $this->syncProductQty($item->product_id, $item->warehouse_id);
                        if (isset($item->option_id) && !empty($item->option_id)) {
                            $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                        }
                    } elseif ($item->product_type == 'combo') {
                        $combo_items = $this->getProductComboItems($item->product_id, $item->warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            if ($combo_item->type == 'standard') {
                                $this->syncProductQty($combo_item->id, $item->warehouse_id);
                            }
                        }
                    }
                } else {
                    $this->syncProductQty($item->product_id, $item->warehouse_id);
                    if (isset($item->option_id) && !empty($item->option_id)) {
                        $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    }
                }
            }
        } elseif ($product_id) {
            $warehouses = $this->getAllWarehouses();
            foreach ($warehouses as $warehouse) {
                $this->syncProductQty($product_id, $warehouse->id);
                $this->checkOverSold($product_id, $warehouse->id);
                if ($product_variants = $this->getProductVariants($product_id)) {
                    foreach ($product_variants as $pv) {
                        $this->syncVariantQty($pv->id, $warehouse->id, $product_id);
                        $this->checkOverSold($product_id, $warehouse->id, $pv->id);
                    }
                }
            }
        } elseif ($store_sale_id) {
            $purchase_items = $this->getAllStoreItems($store_sale_id);
            foreach ($purchase_items as $item) {
                $this->syncProductQty($item->product_id, $item->warehouse_id);
                $this->checkOverSold($item->product_id, $item->warehouse_id);
                if (isset($item->option_id) && !empty($item->option_id)) {
                    $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    $this->checkOverSold($item->product_id, $item->warehouse_id, $item->option_id);
                }
            }
        }
    }

    public function syncSalePayments($id = null, $store_sale_id = null, $reward_exchange_id = null)
    {
        if ($id) {
            $sale = $this->getSaleByID($id);
            if ($payments = $this->getSalePayments($id)) {
                $paid        = 0;
                $grand_total = $sale->grand_total + $sale->rounding;
                foreach ($payments as $payment) {
                    $paid += $payment->amount + $payment->discount;
                }
                $payment_status = $paid == 0 ? 'pending' : $sale->payment_status;
                if ($this->bpas->formatDecimal($grand_total) == 0 || $this->bpas->formatDecimal($grand_total) == $this->bpas->formatDecimal($paid)) {
                    $payment_status = 'paid';
                } elseif ($sale->due_date <= date('Y-m-d') && !$sale->sale_id) {
                    $payment_status = 'due';
                } elseif ($paid != 0) {
                    $payment_status = 'partial';
                }
                if ($this->db->update('sales', ['paid' => $paid, 'payment_status' => $payment_status], ['id' => $id])) {
                    return true;
                }
            } else {
                $payment_status = ($sale->due_date <= date('Y-m-d')) ? 'due' : 'pending';
                if ($this->db->update('sales', ['paid' => 0, 'payment_status' => $payment_status], ['id' => $id])) {
                    return true;
                }
            }
            return false;
        } elseif ($store_sale_id) {
            $sale = $this->getStoreSaleByID($id);
            if ($payments = $this->getStoreSalePayments($id)) {
                $paid        = 0;
                $grand_total = $sale->grand_total + $sale->rounding;
                foreach ($payments as $payment) {
                    $paid += $payment->amount+$payment->discount;
                }
                $payment_status = $paid == 0 ? 'pending' : $sale->payment_status;
                if ($this->bpas->formatDecimal($grand_total) == 0 || $this->bpas->formatDecimal($grand_total) == $this->bpas->formatDecimal($paid)) {
                    $payment_status = 'paid';
                } elseif ($sale->due_date <= date('Y-m-d') && !$sale->sale_id) {
                    $payment_status = 'due';
                } elseif ($paid != 0) {
                    $payment_status = 'partial';
                }
                if ($this->db->update('sales_store', ['paid' => $paid, 'payment_status' => $payment_status], ['id' => $id])) {
                    return true;
                }
            } else {
                $payment_status = ($sale->due_date <= date('Y-m-d')) ? 'due' : 'pending';
                if ($this->db->update('sales_store', ['paid' => 0, 'payment_status' => $payment_status], ['id' => $id])) {
                    return true;
                }
            }
            return false;
        } elseif ($reward_exchange_id) {
            $sale = $this->products_model->getRewardExchangeByID($reward_exchange_id);
            if ($payments = $this->getRewardExchangePayments($reward_exchange_id)) {
                $paid        = 0;
                $grand_total = $sale->grand_total + $sale->rounding;
                foreach ($payments as $payment) {
                    $paid += $payment->amount + $payment->discount;
                }
                $payment_status = $paid == 0 ? 'pending' : $sale->payment_status;
                if ($this->bpas->formatDecimal($grand_total) == 0 || $this->bpas->formatDecimal($grand_total) == $this->bpas->formatDecimal($paid)) {
                    $payment_status = 'paid';
                } elseif ($sale->due_date <= date('Y-m-d')) {
                    $payment_status = 'due';
                } elseif ($paid != 0) {
                    $payment_status = 'partial';
                }
                if ($this->db->update('rewards_exchange', ['paid' => $paid, 'payment_status' => $payment_status], ['id' => $reward_exchange_id])) {
                    return true;
                }
            } else {
                $payment_status = ($sale->due_date <= date('Y-m-d')) ? 'due' : 'pending';
                if ($this->db->update('rewards_exchange', ['paid' => 0, 'payment_status' => $payment_status], ['id' => $reward_exchange_id])) {
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    public function syncVariantQty($variant_id, $warehouse_id, $product_id = null)
    {
        $balance_qty    = $this->getBalanceVariantQuantity($variant_id);
        $wh_balance_qty = $this->getBalanceVariantQuantity($variant_id, $warehouse_id);
        if ($this->db->update('product_variants', ['quantity' => $balance_qty], ['id' => $variant_id])) {
            if ($this->getWarehouseProductsVariants($variant_id, $warehouse_id)) {
                $this->db->update('warehouses_products_variants', ['quantity' => $wh_balance_qty], ['option_id' => $variant_id, 'warehouse_id' => $warehouse_id]);
            } else {
                if ($wh_balance_qty) {
                    $this->db->insert('warehouses_products_variants', ['quantity' => $wh_balance_qty, 'option_id' => $variant_id, 'warehouse_id' => $warehouse_id, 'product_id' => $product_id]);
                }
            }
            return true;
        }
        return false;
    }

    public function getPromosByID($id)
        {
        $this->db->select('promos.*');        
        $q = $this->db->get_where('promos', array('promos.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function updateInvoiceStatus()
    {
        $date = date('Y-m-d');
        $q    = $this->db->get_where('invoices', ['status' => 'unpaid']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if ($row->due_date < $date) {
                    $this->db->update('invoices', ['status' => 'due'], ['id' => $row->id]);
                }
            }
            $this->db->update('settings', ['update' => $date], ['setting_id' => '1']);
            return true;
        }
    }

    private function getBalanceQuantity($product_id, $warehouse_id = null)
    {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', false);
        $this->db->where('product_id', $product_id)->where('quantity_balance !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

    private function getBalanceVariantQuantity($variant_id, $warehouse_id = null)
    {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', false);
        $this->db->where('option_id', $variant_id)->where('quantity_balance !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }
    public function getAllProject($biller_id = null) {
        if ($biller_id) {
            $this->db->where('biller_id', $biller_id);
        }
        $this->db->where('status', 'pending');
        $this->db->from('projects')->order_by('project_id', "DESC");
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getCustomers()
    {
        $this->db->select("id,name as text");
        $this->db->where('group_name', 'customer');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getCustomerInvoices($customer = NULL)
    {
        
        if($customer){
            $this->db->select("id as id, reference_no as text");
            $q = $this->db->get_where("sales", array('customer_id' => $customer));
        }else{
            $this->db->select("id as id, reference_no as text");
            $q = $this->db->get("sales");
        }
        
        return $q->result();

        return FALSE;
    }
   
    //---------pos\
    public function getCurrencyWarehouseByUserID($id) {
        if (!$id) {
            $id = $this->session->userdata('user_id');
        }
        $this->db->select('warehouses.default_currency as currency');
        $this->db->join('warehouses', 'users.warehouse_id=warehouses.id');
        $q = $this->db->get_where('users',array('users.id' => $id));
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return (INT) $res->currency;
        }
        return FALSE;
    }
    public function getCurrencyByID($id) {
        $q = $this->db->get_where('currencies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getProductDefaultPrice($product_id) {
        $q = $this->db->get_where('products', array('id' => $product_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    function getCompanyByName($name = null, $type)
    {
        $q = $this->db->get_where('companies',array('name'=>$name, 'group_id' => $type),1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    //----------account----------
    public function getAccountByID($id) 
    {
        $this->db->select("bpas_gl_charts.accountcode, bpas_gl_charts.accountname, bpas_gl_charts.parent_acc, bpas_gl_sections.sectionname")
                ->from("bpas_gl_charts")
                ->join("bpas_gl_sections","bpas_gl_charts.sectionid=bpas_gl_sections.sectionid","INNER")
                ->where(array('bpas_gl_charts.accountcode' => $id));
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getCashAccountByCode($code = false){
        $q = $this->db->get_where("cash_accounts", array("code"=>$code));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getCashAccountByID($id = false){
        $q = $this->db->get_where("cash_accounts", array("id"=>$id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getCashAccounts(){
        $this->db->order_by("order");
        $q = $this->db->get("cash_accounts");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getTaxByID($id) 
    {
        $this->db->select("gl_charts_tax.accountcode, gl_charts_tax.accountname, gl_charts_tax.accountname_kh, bpas_gl_sections.sectionname")
                ->from("gl_charts_tax")
                ->join("bpas_gl_sections","gl_charts_tax.sectionid=bpas_gl_sections.sectionid","INNER")
                ->where(array('gl_charts_tax.accountcode' => $id));
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getJournalByID($id=null) 
    {
        // echo $biller_id;exit();
        // echo 'hello'; exit();
        $this->db->select("gt.tran_no,gt.tran_no AS g_tran_no, 
                        bpas_companies.company,
                        gt.tran_type, gt.tran_date, 
                        gt.reference_no, gt.account_code, 
                        gt.narrative, gt.description,(
                        CASE
                            WHEN gt.tran_type = 'SALES' THEN
                                (
                                    SELECT
                                        bpas_sales.customer
                                    FROM
                                        bpas_sales
                                    WHERE
                                        gt.reference_no = bpas_sales.reference_no
                                    LIMIT 0,
                                    1
                                )
                            WHEN gt.tran_type = 'PURCHASES' THEN
                                (
                                    SELECT
                                        bpas_purchases.supplier
                                    FROM
                                        bpas_purchases
                                    WHERE
                                        gt.reference_no = bpas_purchases.reference_no
                                    LIMIT 0,
                                    1
                                )
                            WHEN gt.tran_type = 'SALES-RETURN' THEN
                                (
                                    SELECT
                                        bpas_return_sales.customer
                                    FROM
                                        bpas_return_sales
                                    WHERE
                                        bpas_return_sales.reference_no = gt.reference_no
                                    LIMIT 0,
                                    1
                                )
                            WHEN gt.tran_type = 'PURCHASES-RETURN' THEN
                                (
                                    SELECT
                                        bpas_return_purchases.supplier
                                    FROM
                                        bpas_return_purchases
                                    WHERE
                                        bpas_return_purchases.reference_no = gt.reference_no
                                    LIMIT 0,
                                    1
                                )
                            WHEN gt.tran_type = 'DELIVERY' THEN
                                (
                                    SELECT
                                        bpas_deliveries.customer
                                    FROM
                                        bpas_deliveries
                                    WHERE
                                        bpas_deliveries.do_reference_no = gt.reference_no
                                    LIMIT 0,
                                    1
                                )
                            ELSE
                                ''
                            END
                        ) AS NAME, 
                        (IF(gt.amount > 0, gt.amount, IF(gt.amount = 0, 0, null))) as debit, 
                        (IF(gt.amount < 0, abs(gt.amount), null)) as credit")
                ->from("bpas_gl_trans gt")
                ->join("bpas_companies","gt.biller_id=bpas_companies.id","left" )
                ->where('gt.tran_id', $id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getReceivableByID($id=null, $wh=null)
    {
        $this->db
                ->select("sales.id, sales.date, sales.reference_no, sales.biller, companies.company as customer, sales.sale_status, sales.grand_total, sales.paid, (grand_total-paid) as balance, sales.payment_status")
                ->from('sales')
                ->join('companies', 'sales.customer_id = companies.id', 'left')
                ->where(array('payment_status !=' => 'Returned', 'payment_status !='=>'paid', '(grand_total-paid) <>' =>0, 'sales.id' =>$id));
                if($wh){
                    $this->db->where_in('sales.warehouse_id',$wh);
                }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getReceivable_DescriptionByID($id=null, $wh=null)
    {
        $this->db
                ->select("sales.id,sale_items.product_name,sale_items.product_noted")
                ->from('sales')
                ->join('sale_items','sales.id = sale_items.sale_id')
                ->where(array('payment_status !=' => 'Returned', 'payment_status !='=>'paid', '(grand_total-paid) <>' =>0, 'sales.id' =>$id));
                if($wh){
                    $this->db->where_in('sales.warehouse_id',$wh);
                }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    
    public function getRecieptByID($id)
    {
        $this->db
                ->select($this->db->dbprefix('payments') . ".id,
                " . $this->db->dbprefix('payments') . ".date AS date,
                " . $this->db->dbprefix('sales') . ".date AS inv_date,
                " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, 
                " . $this->db->dbprefix('sales') . ".reference_no as sale_ref, customer,
                (
                CASE 
                WHEN " . $this->db->dbprefix('payments') . ".note = ' ' THEN 
                ".$this->db->dbprefix('sales') . ".suspend_note 
                WHEN " . $this->db->dbprefix('sales') . ".suspend_note != ''  THEN 
                CONCAT(".$this->db->dbprefix('sales') . ".suspend_note, ' - ',  " . $this->db->dbprefix('payments') . ".note) 
                ELSE " . $this->db->dbprefix('payments') . ".note END
                ) AS noted, 
                " . $this->db->dbprefix('payments') . ".paid_by, IF(bpas_payments.type = 'returned', CONCAT('-', bpas_payments.amount), bpas_payments.amount) as amount, " . $this->db->dbprefix('payments') . ".type, bpas_sales.sale_status")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.id')
                ->order_by('payments.date desc')
                ->where(array('payments.type !='=>"sent", 'sales.customer !='=>'', 'payments.id'=>$id));
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getPayableByID($id=null, $wh=null)
    {
        $this->db
                ->select("id,date,reference_no,order_ref,request_ref,supplier,status,grand_total,
                    paid, (grand_total - paid) AS balance,payment_status")
                ->from('purchases')
                ->where(array('payment_status !='=>'paid', 'id'=>$id));
                if($wh){
                    $this->db->where_in('purchases.warehouse_id',$warehouse_id);
                }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getARaging($id=null,$ware=null,$created=null,$biller=null,$Sdate=null,$Edate=null,$wh=null)
    {
        $this->db->select("
                customer_id, companies.name as customer,
                SUM(IFNULL(grand_total, 0)) AS grand_total, 
                SUM(IFNULL(paid, 0)) AS paid,
                SUM(IFNULL(grand_total - paid, 0)) AS balance, 
                COUNT(bpas_sales.id) AS ar_number")
            ->from('sales')
            ->join('companies','sales.customer_id = companies.id', 'left')
            ->where('payment_status !=', 'Returned')
            ->where('payment_status !=', 'paid')
            ->where('customer_id', $id)        
            ->where('DATE_SUB('. $this->db->dbprefix('sales')  .'.date, INTERVAL 1 DAY) <= CURDATE()')
            ->where('(grand_total-paid) <> ', 0)
            ->group_by('companies.id');

        if($ware){
            $this->db->where('bpas_sales.warehouse_id',$ware);
        }
        if($created){
            $this->db->where('bpas_sales.created_by',$created);
        }
        if($biller){
            $this->db->where('bpas_sales.biller_id',$biller);
        }
        if ($Sdate) {
            $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $Sdate . '" and "' . $Edate . '"');
        }
        if($wh){
            $this->db->where_in('sales.warehouse_id', $wh);
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getARaging1($id)
    {

        $this->db->select("companies.id, customer, 
            SUM(IFNULL(grand_total, 0)) as grand_total, 
            SUM(IFNULL(paid, 0)) as paid, 
            SUM(IFNULL(grand_total-paid, 0)) as balance,
            COUNT(bpas_sales.id) as ar_number
            ")
        ->from('sales')
        ->join ('companies', 'sales.customer_id = companies.id', 'left')
        ->where('payment_status !=', 'Returned')
        ->where('payment_status !=', 'paid')
        ->where('DATE_SUB('. $this->db->dbprefix('sales')  .'.date, INTERVAL 1 DAY) <= CURDATE()')
        ->where('(grand_total-paid) <> ', 0)
        ->where('companies.id =',$id);
    
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getARaging2($id)
    {
        $this->db->select("companies.id, customer, 
            SUM(IFNULL(grand_total, 0)) as grand_total, 
            SUM(IFNULL(paid, 0)) as paid, 
            SUM(IFNULL(grand_total-paid, 0)) as balance,
            COUNT(bpas_sales.id) as ar_number
            ")
        ->from('sales')
        ->join ('companies', 'sales.customer_id = companies.id', 'left')
        ->where('payment_status !=', 'Returned')
        ->where('payment_status !=', 'paid')
        ->where('DATE(bpas_sales.date) BETWEEN curdate() - INTERVAL 30 DAY AND curdate() - INTERVAL 0 DAY')
        ->where('(grand_total-paid) <> ', 0)
        ->where('companies.id =',$id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getARaging3($id)
    {
        $this->db->select("companies.id, customer, 
            SUM(IFNULL(grand_total, 0)) as grand_total, 
            SUM(IFNULL(paid, 0)) as paid, 
            SUM(IFNULL(grand_total-paid, 0)) as balance,
            COUNT(bpas_sales.id) as ar_number
            ")
        ->from('sales')
        ->join ('companies', 'sales.customer_id = companies.id', 'left')
        ->where('payment_status !=', 'Returned')
        ->where('payment_status !=', 'paid')
        ->where('DATE(bpas_sales.date) BETWEEN curdate() - INTERVAL 60 DAY AND curdate() - INTERVAL 30 DAY')
        ->where('(grand_total-paid) <> ', 0)
        ->where('companies.id', $id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getARaging4($id)
    {
        $this->db->select("companies.id, customer, 
            SUM(IFNULL(grand_total, 0)) as grand_total, 
            SUM(IFNULL(paid, 0)) as paid, 
            SUM(IFNULL(grand_total-paid, 0)) as balance,
            COUNT(bpas_sales.id) as ar_number
            ")
        ->from('sales')
        ->join ('companies', 'sales.customer_id = companies.id', 'left')
        ->where('payment_status !=', 'Returned')
        ->where('payment_status !=', 'paid')
        ->where('DATE(bpas_sales.date) BETWEEN curdate() - INTERVAL 90 DAY AND curdate() - INTERVAL 60 DAY')
        ->where('(grand_total-paid) <> ', 0)
        ->where('companies.id', $id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getARaging5($id)
    {
        $this->db->select("companies.id, customer, 
            SUM(IFNULL(grand_total, 0)) as grand_total, 
            SUM(IFNULL(paid, 0)) as paid, 
            SUM(IFNULL(grand_total-paid, 0)) as balance,
            COUNT(bpas_sales.id) as ar_number
            ")
        ->from('sales')
        ->join ('companies', 'sales.customer_id = companies.id', 'left')
        ->where('payment_status !=', 'Returned')
        ->where('payment_status !=', 'paid')
        ->where('DATE(bpas_sales.date) BETWEEN curdate() - INTERVAL 10000 DAY AND curdate() - INTERVAL 90 DAY')
        ->where('(grand_total-paid) <> ', 0)
        ->where('companies.id', $id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAPaging($id=null, $ware=null, $created=null, $biller=null, $Sdate=null, $Edate=null, $wh=null, $condition=null)
    {
        $this->db->select("companies.id, companies.name as supplier,
                SUM(IFNULL(grand_total, 0)) AS grand_total,
                SUM(IFNULL(paid, 0)) AS paid,
                SUM(IFNULL(grand_total - paid, 0)) AS balance,
                COUNT(bpas_purchases.id) as ap_number
                ")

            ->from('purchases')
            ->join('companies', 'companies.id = purchases.supplier_id', 'inner')
            ->where('payment_status !=','paid')
            ->where('companies.id', $id);   

            if ($condition == '0_30') {
                $this->db->where('DATE(bpas_purchases.date) BETWEEN curdate() - INTERVAL 30 DAY AND curdate() - INTERVAL 0 DAY');
            } elseif ($condition == '30_60') {
                $this->db->where('DATE(bpas_purchases.date) BETWEEN curdate() - INTERVAL 60 DAY AND curdate() - INTERVAL 30 DAY');
            } elseif ($condition == '60_90') {
                $this->db->where('DATE(bpas_purchases.date) BETWEEN curdate() - INTERVAL 90 DAY AND curdate() - INTERVAL 60 DAY');
            } elseif ($condition == '90_over') {
                $this->db->where('DATE(bpas_purchases.date) BETWEEN curdate() - INTERVAL 10000 DAY AND curdate() - INTERVAL 90 DAY');
            } else {
                $this->db->where('DATE_SUB('. $this->db->dbprefix('purchases')  .'.date, INTERVAL 1 DAY) <= CURDATE()');  
            } 
            
            if($ware){
                $this->db->where('bpas_purchases.warehouse_id',$ware);
            }
            if($created){
                $this->db->where('bpas_purchases.created_by',$created);
            }
            if($biller){
                $this->db->where('bpas_purchases.biller_id',$biller);
            }
            if ($Sdate) {
                $this->db->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $Sdate . '" and "' . $Edate . '"');
            }
            if($wh){
                $this->db->where_in('bpas_purchases.warehouse_id',$wh);
            }
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                return $q->row();
            }
            return FALSE;
    }
    
    public function getBillerPrefix($id)
    {
        $this->db->select('*');
        $this->db->from("bpas_companies");
        $this->db->where('id',$id);
        $q = $this->db->get();
        if($q->num_rows()>0){
            return $q->row();           
        }
    }
    
    public function getPamentTermbyID($id)
    {
        $q = $this->db->get_where('payment_term', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    /* Bank Accounts */
    public function getAllBankAccounts() 
    {
        $q = $this->db->get_where('gl_charts', array('bank' => 1));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    // Bank Accounts For User
    public function getAllBankAccountsByUserID() 
    {
        $this->db
             ->select('gl_charts.accountcode, gl_charts.accountname')
             ->from('gl_charts')
             ->join('bpas_users_bank_account', 'gl_charts.accountcode = bpas_users_bank_account.bankaccount_code', 'left')
             ->where('bpas_users_bank_account.user_id', $this->session->userdata('user_id'));
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllBankAccountsByUserID2() 
    {
        $this->db
             ->select('gl_charts.accountcode, gl_charts.accountname')
             ->from('gl_charts')
             ->join('bpas_users_bank_account', 'gl_charts.accountcode = bpas_users_bank_account.bankaccount_code', 'left')
             ->where('bpas_users_bank_account.id', $this->session->userdata('user_id'));
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getCurrency()
    {
        $this->db->select()
                 ->from('currencies')
                 ->order_by('id', 'ASC');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getAllBiller() {
        $this->db->where('group_name','biller');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function get_Sub_Biller($id) {
        $this->db->select('id as bill_id');
        $this->db->where('id', $id);
        $this->db->or_where('leader', $id);
        $q = $this->db->get('companies');
        
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

        //=========Add Accounting =========//

    public function getProductAccByProductId($product_id)
    {
        $q = $this->db->get_where('account_product', array('product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getFloorByID($id)
    {
        $q = $this->db->get_where('floors', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
     public function getAllFloors()
    {
        $this->db->from('floors')->order_by('id','DESC');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAccountSettingByBiller($biller_id =null)
    {
        if($biller_id){
            $q = $this->db->get_where('account_settings', array('biller_id' => $biller_id), 1);
        }else{
            $q = $this->db->get('account_settings');
        }
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function AccountByBiller($column,$biller_id=null)
    {
        $column = (string) $column;
        if ($biller_id) {
            $q = $this->db->get_where('account_settings', array('biller_id' => $biller_id), 1);
        } else {
            $q = $this->db->get_where('account_settings', 1);
        }
        if ($q->num_rows() > 0) {
            $row = $q->row();
            return $row->$column;
        }
        return FALSE;
    }

    public function deleteAccTran($transaction,$transaction_id)
    {
        $this->db->delete('gl_trans', array('tran_type' => $transaction, 'tran_no' => $transaction_id));
    }

    public function removeAccTran($transaction, $transaction_id)
    {
        $data = [];
        $data['hide'] = 0;
        $this->db->update('gl_trans',$data, array('tran_type' => $transaction, 'tran_no' => $transaction_id));
    }

    public function getAccountName($code)
    {
        $q = $this->db->get_where('gl_charts', array('accountcode' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row()->accountname;
        }
        return FALSE;
    }

    public function sectionid($code)
    {
        $q = $this->db->get_where('gl_charts', array('sectionid' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row()->sectionid;
        }
        return FALSE;
    }
    //=========End Accounting =========//
    public function getAllBom($id)
    {
        $this->db->select('*');
        $this->db->where('id', $id);
        $q = $this->db->get('bom');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getBom_itemsTop($id)
    {
        $this->db->select('*');
        $this->db->where(array('bom_id'=> $id, 'status'=> 'deduct'));
        $q = $this->db->get('bom_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getBom_itemsBottom($id)
    {
        $this->db->select('*');
        $this->db->where(array('bom_id'=> $id, 'status'=> 'add'));
        $q = $this->db->get('bom_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getProductVariantByOptionID($option_id){
        $q = $this->db->get_where('product_variants', array('id' => $option_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function calculateCONAVCost($product_id, $total_raw_cost, $total_fin_qty, $unit_qty) {
        $percent        = 0;
        $qty            = 0;
        $total_new_cost = 0;
        $total_qty      = 0;
        $total_old_cost = 0;
        $old_product    = $this->getProductAllByID($product_id);
        
        $total_qty      = $unit_qty;
        /*
        if($finish_cost){
            $percent    = $each_cost/$finish_cost;
        }else{
            $percent    = 1;
        }
        $qty            = $quantity;
        if(!$qty_unit){
            $qty_unit   = 1;
        }
        */
        //========================== AVG Cost ============================//

        if($old_product->cost > 0){
            $total_qty      = $unit_qty + $old_product->quantity;
            $total_old_cost = $old_product->quantity * $old_product->cost;
        }
        
        $total_new_cost = ($total_raw_cost * $unit_qty)/$total_fin_qty;
        echo 'TRC '. $total_raw_cost .' UQTY '. $unit_qty .' TFQ '. $total_fin_qty .' TNC '. $total_new_cost .' TOC '. $total_old_cost .' TQTY '. $total_qty .'<br/>';      
        $average_cost   = ($total_new_cost + $total_old_cost) / $total_qty;
        
        //============================ End ===============================//
        
        return array('avg'=>$average_cost, 'cost' => $total_new_cost);
    }
    
    public function editcalculateCONAVCost($product_id, $total_raw_cost, $total_fin_qty, $unit_qty, $combo_item) {
        $percent        = 0;
        $qty            = 0;
        $total_new_cost = 0;
        $total_qty      = 0;
        $total_old_cost = 0;
        $old_product    = $this->getPurchaseItemByTranId($combo_item);
        $total_qty      = $unit_qty;
        /*
        if($finish_cost){
            $percent    = $each_cost/$finish_cost;
        }else{
            $percent    = 1;
        }
        $qty            = $quantity;
        if(!$qty_unit){
            $qty_unit   = 1;
        }
        */
        //========================== AVG Cost ============================//
        if(isset($old_product->cost)){
            if($old_product->cost > 0){
                $total_qty      = $unit_qty + $old_product->cb_qty;
                $total_old_cost = $old_product->cb_qty * $old_product->cb_cost;
            }
        }
        $total_new_cost = ($total_raw_cost * $unit_qty)/$total_fin_qty;       
        $average_cost   = ($total_new_cost + $total_old_cost) / $total_qty;
        
        //============================ End ===============================//
        
        return array('avg'=>$average_cost, 'cost' => $total_new_cost);
    }
    public function getProductAllByID($id) {
        $this->db->select('products.*');        
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllProducts()
    {
        $q = $this->db->get("products");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getpaid_by($paid,$date,$swh)
    {
        $sdate = $date . ' 00:00:00';
        $edate = $date . ' 23:59:59';
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->group_start()
            ->where('payments.type', 'received')
            ->or_where('payments.type', 'returned')
            ->group_end()
            ->where('payments.date >=', $sdate)->where('payments.date <=', $edate)
            ->where('paid_by',$paid);
        if($swh){
            $this->db->where('sales.warehouse_id',$swh);
        }
        
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getSaleByIDByProId($id) {

        $this->db->select('sale_id');
        $q = $this->db->get_where('sale_items', array('product_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllUsers() {
        $q = $this->db->get('users');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getBillerByID($id)
    {
        $this->db->where_in('id', $id);
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function get_room_name($id) {
        $q = $this->db->get_where('suspended_note', array('note_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getSuppliers()
    {
        $this->db->select("id,name as text");
        $this->db->where('group_name', 'supplier');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllCategoriesMakeup() 
    {
        $q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function getAllCategoriesMakeupSub() 
    {
        $q = $this->db->get('subcategories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function approved_edit_bill($pincode) {
        $q = $this->db->get_where('suspended_note', array('note_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function fetch_Categories($limit, $start)
    {
        $this->db->select('*');
        $this->db->from('categories');
        $this->db->limit($start, $limit);
        $query = $this->db->get();

        return $query->result_array();
        // $this->db->limit($limit, $start);
        // $query = $this->db->get("categories");

        // if ($query->num_rows() > 0) {
        //     foreach ($query->result() as $row) {
        //         $data[] = $row;
        //     }
        //     return $data;
        // }
        // return false;
    }
    public function getCategoriesByParent($id, $limit = null, $start = null)
    {        
        $items = array();
        // show by categories
         $this->db->where('status', 'show');    
        if ($id === 0) {
            $this->db->where('parent_id', $id);    
            $this->db->or_where('parent_id', null);    
        } else {
            $this->db->where('parent_id', $id);    
        }
        $this->db->order_by('order_number','ASC');
        $this->db->limit($start, $limit);
        $q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $row->children = $this->getCategoriesByParent($row->id);
                $items[] = $row;
            }
        }
        return $items;
    }

    // public function getCategoriesByParent($id, $limit = null, $start = null)
    // {        
    //     $items = array();
    //     if ($id === 0) {
    //         $this->db->where('parent_id', $id);    
    //         $this->db->or_where('parent_id', null);    
    //     } else {
    //         $this->db->where('parent_id', $id);    
    //     }
    //     $this->db->limit($start, $limit);
    //     $q = $this->db->get('categories');
    //     if ($q->num_rows() > 0) {
    //         foreach (($q->result()) as $row) {
    //             $row->children = $this->getCategoriesByParent($row->id);
    //             $items[] = $row;
    //         }
    //     }
    //     return $items;
    // }

    public function getCategoriesByParents($id, $limit = null, $start = null)
    {        
        $items = array();
        // show by categories
        // $this->db->where('status', 'hide');    
        if ($id === 0) {
            $this->db->where('parent_id', $id);    
            $this->db->or_where('parent_id', null);    
        } else {
            $this->db->where('parent_id', $id);    
        }
        $this->db->limit($start, $limit);
        $q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $row->children = $this->getCategoriesByParents($row->id);
                $items[] = $row;
            }
        }
        return $items;
    }

    public function getNestedByCategories($limit = null, $start = null)
    {
        return $this->getCategoriesByParents(0, $limit, $start);
    }
    
    public function getNestedCategories($limit = null, $start = null)
    {
        return $this->getCategoriesByParent(0, $limit, $start);
    }

    public function getExpenseCategoriesByParent($id, $limit = null, $start = null)
    {        
        $items = array();
        if ($id === 0) {
            $this->db->where('parent_id', $id);    
            $this->db->or_where('parent_id', null);    
        } else {
            $this->db->where('parent_id', $id);    
        }
        $this->db->limit($start, $limit);
        $q = $this->db->get('expense_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $row->children = $this->getExpenseCategoriesByParent($row->id);
                $items[] = $row;
            }
        }
        return $items;
    }

    public function getNestedExpenseCategories($limit = null, $start = null)
    {
        return $this->getExpenseCategoriesByParent(0, $limit, $start);
    }

    public function hasChildCategory($id) 
    {
        $q = $this->db->get_where('categories', ['parent_id' => $id]);
        if ($q->num_rows() > 0) {
            return true;
        }
        return false;
    }

    public function getAllCategories_()
    {
        $q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function countCategories()
    {
        $this->db->select('count(*) as allcount');
        $this->db->from('categories');

        $this->db->where('parent_id', 0);
        $this->db->where('status', 'show');
        $this->db->or_where('parent_id', NULL);
        $query = $this->db->get();
        $result = $query->result_array();

        return $result[0]['allcount'];

        // return $this->db->count_all("categories");
    }

    
    public function getUnitUOM($product_id=NULL)
    {
        $this->db->select("product_variants.*,products.cost as pcost");
        $this->db->from("product_variants");
        $this->db->join("products","products.id=product_variants.product_id","left");
        $this->db->where('product_id', $product_id);
        $this->db->order_by('qty_unit', 'DESC');
        $q = $this->db->get();
        
        if ($q->num_rows() > 0) {
        foreach (($q->result()) as $row) {
            $data[] = $row;
        }
            return $data;
        }
        

        return FALSE;
    }
    public function getUnitNameByProId($product_id = NULL)
    {
        $this->db->select("units.name as unit_name");
        $this->db->from("units");
        $this->db->join("products","products.unit = units.id","left");
        $this->db->where('products.id', $product_id);
        $q = $this->db->get();
        
        if ($q->num_rows() > 0) {
            return $q->row()->unit_name;
        }
        
        return FALSE;
    }
    public function getAddressCompanybyID($company_id)
    {
        $q = $this->db->get_where('addresses', array('company_id' => $company_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getSuspendbyID($id)
    {
        $q = $this->db->get_where('suspended_bills', array('id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllProjectByID($id) {
        $q = $this->db->get_where('projects', array('project_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllitemByID($id) {
        $q = $this->db->get_where('sale_items', array('sale_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getMenuByID($parent_id)
    {
        $q = $this->db->get_where('menu', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getSubMenus($parent_id)
    {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get('menu');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllPaymentTerm() 
    {
        $q = $this->db->get('payment_term');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllPaymentTermByID($pt_id) 
    {
        $q = $this->db->get_where('payment_term', array('id' => $pt_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    function get_transfer_alerts(){
         $this->db->select('COUNT(*) AS count');
        $this->db->where('status', 'pending');
        $q = $this->db->get('transfers');
        if($q->num_rows() > 0 ){
            $q = $q->row()->count;
            return $q;
        }
        return false;
    }
    public function get_loan_alert(){
        $date = date('Y-m-d', strtotime('+7 day'));
       $this->db->select('count(pay_date) as date_exp')->where('pay_date !=',NULL)->where('pay_date <=', $date);
        $q = $this->db->get('loan_payment');
        if($q->num_rows() > 0){
            $re = $q->row();
        return $re->date_exp;
        }
        return false;

    }
    public function get_loan_exp_day_alert(){
        $date = date('Y-m-d');
       $this->db->select('count(pay_date) as date_exp')->where('pay_date !=',NULL)->where('pay_date', $date);
        $q = $this->db->get('loan_payment');
        if($q->num_rows() > 0){
            $re = $q->row();
        return $re->date_exp;
        }
        return false;
    }
    public function get_loan_exp_late_day_alert(){
        $date = date('Y-m-d');
       $this->db->select('count(pay_date) as date_exp')->where('pay_date !=',NULL)->where('pay_date <', $date);
        $q = $this->db->get('loan_payment');
        if($q->num_rows() > 0){
            $re = $q->row();
        return $re->date_exp;
        }
        return false;
    }
    public function getChartByID($id) {
        $q = $this->db->get_where('gl_charts', array('accountcode' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllEmployee() {
        $this->db->select($this->db->dbprefix('users').".id as id, " . $this->db->dbprefix('users') . ".emp_code, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ' ," . $this->db->dbprefix('users') . ".last_name) AS fullname, " . $this->db->dbprefix('users') . ".gender, nationality, position, employeed_date, phone, company, active");
        $this->db->from("users");
        $this->db->join('groups', 'users.group_id=groups.id', 'left');
        $this->db->group_by('users.id');
        $this->db->where('company_id', NULL);
        $this->db->order_by('id', 'DESC');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getAllModules()
    {
        $q = $this->db->where('status',1)->get('modules');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getModuleByID($module=null)
    {
        $this->db->where('id', $module);
        $q = $this->db->where('status',1)->get('modules');
        if ($q->num_rows() > 0) {
            return $q->row();        
        }
        return false;
    }

    function activeModule($module){
        $q = $this->db->get_where('settings', array(''.$module.'' =>1), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function promobycategory($warehouse_id,$category_id)
    {
        $today = date('Y-m-d');
        $this->db->select(
            $this->db->dbprefix('promotions').".warehouse_id,
            " . $this->db->dbprefix('promotions') . ".start_date as start_date, 
            " . $this->db->dbprefix('promotions') . ".end_date as end_date, 
            " . $this->db->dbprefix('promotion_categories') . ".category_id as category_id,
            " . $this->db->dbprefix('promotion_categories') . ".discount AS discount
        ");
        $this->db->from("promotions");
        $this->db->join('promotion_categories', 'promotions.id=promotion_categories.promotion_id', 'left');
        $this->db->where(array('promotions.warehouse_id' => $warehouse_id,'promotion_categories.category_id' =>$category_id ));
        $this->db->where("'".$today."'"." BETWEEN ". $this->db->dbprefix('promotions').'.start_date AND 
            '.$this->db->dbprefix('promotions').'.end_date');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getProductExpireDate($product_id, $warehouse_id){
        
        $condition = array('product_id'=>$product_id,'warehouse_id'=>$warehouse_id, 'expiry !=' => NULL );
        $this->db->select('
            bpas_purchase_items.id, 
            bpas_purchase_items.product_id, 
            bpas_purchase_items.expiry,
            bpas_purchase_items.warehouse_id,
            SUM(quantity_balance) as quantity_balance'
        );
        $this->db->from('bpas_purchase_items');
        $this->db->where($condition);
        
        $this->db->group_by('bpas_purchase_items.expiry');
        $this->db->having('quantity_balance > ', 0);
        $q = $this->db->get();
        if($q->num_rows()>0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getZoneByID($id)
    {
        $q = $this->db->get_where('zones', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getAllZones()
    {
        $q = $this->db->get('zones');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getMultiZonesByID($ids)
    {
        $arr_ids = explode(',', $ids);
        $this->db->where_in('id', $arr_ids);
        $q = $this->db->get('zones');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllZones_Order_Group()
    {
        $q_child = " (
                SELECT * FROM bpas_zones AS z
                ORDER BY z.zone_name
            ) AS bpas_child ";

        $this->db->select(" 
                zones.id AS p_id, zones.zone_name AS p_name, zones.parent_id AS p_of_p, 
                GROUP_CONCAT(bpas_child.id SEPARATOR '___') AS c_id, 
                GROUP_CONCAT(bpas_child.zone_name SEPARATOR '___') AS c_name, 
                GROUP_CONCAT(bpas_child.parent_id SEPARATOR '___') AS p_of_c 
            ");
        $this->db->from('zones');
        $this->db->join($q_child, 'zones.id = child.parent_id', 'left');
        $this->db->where('zones.parent_id', 0);
        $this->db->group_by('zones.zone_name');
        $this->db->order_by('zones.zone_name');
        $this->db->order_by('child.zone_name');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getChildZonesByID($id)
    {
        $q = $this->db->get_where('zones', ['parent_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getCities(){
        $this->db->where("IFNULL(city_id,0)",0);
        $q = $this->db->get("zones");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getDistricts($city_id = false){
        $this->db->where("IFNULL(district_id,0)",0);
        $this->db->where("IFNULL(city_id,0)",$city_id);
        $q = $this->db->get("zones");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getCommunes($district_id = false){
        $this->db->where("IFNULL(commune_id,0)",0);
        $this->db->where("IFNULL(district_id,0)",$district_id);
        $q = $this->db->get("zones");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getZoneByUser($id)
    {
        $q = $this->db->get_where('users', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllUserByGroupID($id)
    {
        $q = $this->db->get_where('users', ['group_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllSalemans($id)
    {
        $q = $this->db->get_where('users', ['group_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getDepositByCompanyID($id) 
    {
        $this->db->select("company_id, COALESCE(SUM(amount), 0) AS amount, COALESCE(SUM(amount_usd), 0) AS amount_usd, COALESCE(SUM(amount_khr), 0) AS amount_khr, COALESCE(SUM(amount_thb), 0) AS amount_thb")
            ->from('deposits')
            ->where('deposits.company_id', $id)
            ->group_by('company_id')
            ->limit('1');

        $q = $this->db->get();
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
    public function getUserName($id)
    {
        $this->db->select("id")
        ->from('users')
        ->where('username', $id);
        $q = $this->db->get();
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }

    function update_property_status($id, $status)
    {
        $this->db->select('sales.reference_no, sale_items.product_id');
        $this->db->join('sale_items', 'sales.id=sale_items.sale_id', 'left');
        $q = $this->db->get_where('sales', array('sale_items.sale_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $this->db->update('products', ['quantity' => '-1'], ['id' => $row->product_id]);
            }
            return true;
        }
        return FALSE;
    }
    public function getTaxReference($field)
    {
        $q = $this->db->get_where('tax_ref', ['ref_id' => '1'], 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            switch ($field) {
              
                case 'tax':
                    $prefix = $this->Settings->pos_prefix_tax;
                    break;
                case 'hq':
                    $prefix = isset($this->Settings->sales_prefix) ? 'HQ' : '';
                    break;
                default:
                    $prefix = '';
            }

            $ref_no = $prefix;

            if ($this->Settings->reference_format == 1) {
                $ref_no .= date('y') . '/' . sprintf('%06s', $ref->{$field});
            } elseif ($this->Settings->reference_format == 2) {
                $ref_no .= date('y') . '/' . date('m') . '/' . sprintf('%04s', $ref->{$field});
            } elseif ($this->Settings->reference_format == 3) {
                $ref_no .= sprintf('%04s', $ref->{$field});
            } else {
                $ref_no .= $this->getRandomReference();
            }

            return $ref_no;
        }
        return false;
    }
    public function updateTaxReference($field)
    {
        $q = $this->db->get_where('tax_ref', ['ref_id' => '1'], 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            $this->db->update('tax_ref', [$field => $ref->{$field} + 1], ['ref_id' => '1']);
            return true;
        }
        return false;
    }
    public function getPermission() {
        $q = $this->db->get_where('permissions', array('group_id' => $this->session->userdata('group_id')), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getExchangeRate($code = null)
    {
        $q = $this->db->get_where('currencies', ['code' => $code], 1);
        if($q->num_rows() > 0){
            return $q->row()->rate;
        }
        return false;
    }

    //---------option--------
    public function getOptionByID($id)
    {
        $q = $this->db->get_where('options', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getLastNum($id, $option_id)
    {
        $this->db->select('MAX(max_serial) as max_serial');
        $q = $this->db->get_where('sale_items', ['product_id' => $id, 'option_id' => $option_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getAllProductOption($id)
    {
        $q = $this->db->get_where('product_options', ['product_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getOptionRowByID($id)
    {
        $q = $this->db->get_where('product_options', ['option_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllOptions()
    {
        $q = $this->db->get('product_options');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    //--------customer due---------
    public function getSalesLastBalanceByDate($date, $customer)
    {
        $this->db->select('SUM(grand_total - paid) as total_balance')
            ->where('customer_id', $customer)
            ->where('date <', $date);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res->total_balance;
        }
        return false;
    }
    public function getSalesTotalBalanceByDate($date, $customer)
    {
        $this->db->select('SUM(grand_total - paid) as total_balance')
            ->where('customer_id', $customer)
            ->where('date <=', $date);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res->total_balance;
        }
        return false;
    }
    public function getsaleman($id = null){
        $q = $this->db->get_where('users', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getCustomeFieldByID($id = null){
        $q = $this->db->get_where('custom_field ', ['active'=>1,'id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getCustomeFieldByParentID($id = null){
        $q = $this->db->get_where('custom_field', ['active'=>1,'parent_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getCustomeFieldByCode($id = null){
        $q = $this->db->get_where('custom_field ', ['active'=>1,'code' =>'route'], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function deleteAccTranPayment($transaction,$transaction_id)
    {
        $this->db->delete('gl_trans', array('tran_type' => $transaction, 'payment_id' => $transaction_id));
    }
    public function deleteAccTranSale($transaction,$transaction_id)
    {
        $this->db->where('payment_id is NULL');
        $this->db->delete('gl_trans', array(
            'tran_type' => $transaction, 
            'tran_no' => $transaction_id,
        ));        
    }
    public function deleteSalePayment($transaction,$transaction_id){
        $this->db->delete('gl_trans', array('tran_type' => $transaction, 'sale_id' => $transaction_id));
    }
   
    public function getCustomerDeposit($id) 
    {
        $this->db->select("id, COALESCE(deposit_amount, 0) AS amount, COALESCE(deposit_amount_usd, 0) AS amount_usd, COALESCE(deposit_amount_khr, 0) AS amount_khr, COALESCE(deposit_amount_thb, 0) AS amount_thb")
            ->from('companies')
            ->where('companies.id', $id)
            ->limit('1');

        $q = $this->db->get();
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }

    //////////////// Sync Quantity 13_05_21 ////////////////
    
    public function syncQuantity_13_05_21($product_id = null)
    {
        if ($product_id) {
            $warehouses  = $this->getAllWarehouses();
            foreach ($warehouses as $warehouse) {
                $this->syncProductQty_($product_id, $warehouse->id);
                $this->syncPurchaseItemsBalanceQty($product_id, $warehouse->id);
                $this->checkOverSold_($product_id, $warehouse->id);
                // if ($product_variants = $this->getProductVariants($product_id)) {
                //     foreach ($product_variants as $pv) {
                //         $this->syncVariantQty($pv->id, $warehouse->id, $product_id);
                //         $this->checkOverSold($product_id, $warehouse->id, $pv->id);
                //     }
                // }
            }
            return true;
        }
        return false;
    }

    public function syncProductQty_($product_id, $warehouse_id, $status = null)
    {
        $module_type    = $this->getProductByID($product_id);
        $balance_qty    = $this->getProductBalanceQty($product_id)['balanceQty'];
        $wh_balance_qty = $this->getProductBalanceQty($product_id, $warehouse_id)['balanceQty'];
        if (!empty($module_type) && $module_type->module_type == 'property') {
            if($balance_qty == null){
                $balance_qty = 1;   
            }
            if($status == 'booking'){
                $balance_qty = 2;   
            }
        }
        if ($this->db->update('products', ['quantity' => $balance_qty], ['id' => $product_id])) {
            if ($this->getWarehouseProducts($product_id, $warehouse_id)) {
                $this->db->update('warehouses_products', ['quantity' => $wh_balance_qty], ['product_id' => $product_id, 'warehouse_id' => $warehouse_id]);
            } else {
                if (!$wh_balance_qty) {
                    $wh_balance_qty = 0;
                }
                $product = $this->site->getProductByID($product_id);
                if ($product) {
                    $this->db->insert('warehouses_products', ['quantity' => $wh_balance_qty, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'avg_cost' => ($product->cost ? $product->cost : 0)]);
                } else {
                    $this->db->insert('warehouses_products', ['quantity' => $wh_balance_qty, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id]);
                }
            }
            return true;
        }
        return false;
    }

    public function syncPurchaseItemsBalanceQty($product_id, $warehouse_id)
    {
        $purchase_items_balance_qty                     = $this->getPurchasedItemBalanceQty($product_id, $warehouse_id);
        $purchases['purchases']                         = $this->getPurchases($product_id, $warehouse_id);
        $sp_store['sp_store']                           = $this->getSalesPurchasesStore($product_id, $warehouse_id);
        $sales['sales']                                 = $this->getSales($product_id, $warehouse_id);
        $pos_sales['pos_sales']                         = $this->getPOSSales($product_id, $warehouse_id);
        $addonsales['addonsales']                       = $this->getaddonSales($product_id, $warehouse_id);
        $combosales['combosales']                       = $this->getcomboSales($product_id, $warehouse_id);
        $returns['returns']                             = $this->getReturns($product_id, $warehouse_id);
        $transfers_in['transfers_in']                   = $this->getTransfers_IN($product_id, $warehouse_id);
        $transfers_out['transfers_out']                 = $this->getTransfers_OUT($product_id, $warehouse_id);
        $adjustments_add['adjustments_add']             = $this->getAdjustments_ADD($product_id, $warehouse_id);
        $adjustments_sub['adjustments_sub']             = $this->getAdjustments_SUB($product_id, $warehouse_id);
        $rewards_exchanged_in['rewards_exchanged_in']   = $this->getRewardsExchanged_IN($product_id, $warehouse_id);
        $rewards_received_in['rewards_received_in']     = $this->getRewardsReceived_IN($product_id, $warehouse_id);
        $rewards_exchanged_out['rewards_exchanged_out'] = $this->getRewardsExchanged_OUT($product_id, $warehouse_id);
        $rewards_received_out['rewards_received_out']   = $this->getRewardsReceived_OUT($product_id, $warehouse_id);
        $stock     = array();
        $clause    = array();
        $group_exp = array_unique(
            array_merge(
                $purchases['purchases']                         ? array_keys($purchases['purchases'])                         : array(), 
                $sp_store['sp_store']                           ? array_keys($sp_store['sp_store'])                           : array(), 
                $sales['sales']                                 ? array_keys($sales['sales'])                                 : array(), 
                $combosales['combosales']                       ? array_keys($combosales['combosales'])                       : array(), 
                $pos_sales['pos_sales']                         ? array_keys($pos_sales['pos_sales'])                         : array(), 
                $addonsales['addonsales']                       ? array_keys($addonsales['addonsales'])                       : array(), 
                $returns['returns']                             ? array_keys($returns['returns'])                             : array(), 
                $transfers_in['transfers_in']                   ? array_keys($transfers_in['transfers_in'])                   : array(), 
                $transfers_out['transfers_out']                 ? array_keys($transfers_out['transfers_out'])                 : array(), 
                $adjustments_add['adjustments_add']             ? array_keys($adjustments_add['adjustments_add'])             : array(), 
                $adjustments_sub['adjustments_sub']             ? array_keys($adjustments_sub['adjustments_sub'])             : array(),
                $rewards_exchanged_in['rewards_exchanged_in']   ? array_keys($rewards_exchanged_in['rewards_exchanged_in'])   : array(),
                $rewards_received_in['rewards_received_in']     ? array_keys($rewards_received_in['rewards_received_in'])     : array(),
                $rewards_exchanged_out['rewards_exchanged_out'] ? array_keys($rewards_exchanged_out['rewards_exchanged_out']) : array(),
                $rewards_received_out['rewards_received_out']   ? array_keys($rewards_received_out['rewards_received_out'])   : array()
            )
        );
        if (!empty($group_exp)) {
            foreach ($group_exp as $key => $exp) {
                $cal = 0; $qty = 0;
                if (isset($purchases['purchases'][$exp])) {
                    $cal           = $purchases['purchases'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $purchases['purchases'][$exp]['product_id'], 
                                    'product_code' => $purchases['purchases'][$exp]['product_code'],
                                    'warehouse_id' => $purchases['purchases'][$exp]['warehouse_id'],
                                    'expiry'       => $purchases['purchases'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($sp_store['sp_store'][$exp])) {
                    $cal           += $sp_store['sp_store'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $sp_store['sp_store'][$exp]['product_id'], 
                                    'product_code' => $sp_store['sp_store'][$exp]['product_code'],
                                    'warehouse_id' => $sp_store['sp_store'][$exp]['warehouse_id'],
                                    'expiry'       => $sp_store['sp_store'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($combosales['combosales'][$exp])) {
                    $cal           -= $combosales['combosales'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $combosales['combosales'][$exp]['product_id'], 
                                    'product_code' => $combosales['combosales'][$exp]['product_code'],
                                    'warehouse_id' => $combosales['combosales'][$exp]['warehouse_id'],
                                    'expiry'       => $combosales['combosales'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($sales['sales'][$exp])) { 
                    $cal           -= $sales['sales'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $sales['sales'][$exp]['product_id'], 
                                    'product_code' => $sales['sales'][$exp]['product_code'],
                                    'warehouse_id' => $sales['sales'][$exp]['warehouse_id'],
                                    'expiry'       => $sales['sales'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($pos_sales['pos_sales'][$exp])) {  
                    $cal           -= $pos_sales['pos_sales'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $pos_sales['pos_sales'][$exp]['product_id'], 
                                    'product_code' => $pos_sales['pos_sales'][$exp]['product_code'],
                                    'warehouse_id' => $pos_sales['pos_sales'][$exp]['warehouse_id'],
                                    'expiry'       => $pos_sales['pos_sales'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($addonsales['addonsales'][$exp])) {
                    $cal           -= $addonsales['addonsales'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $addonsales['addonsales'][$exp]['product_id'], 
                                    'product_code' => $addonsales['addonsales'][$exp]['product_code'],
                                    'warehouse_id' => $addonsales['addonsales'][$exp]['warehouse_id'],
                                    'expiry'       => $addonsales['addonsales'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($returns['returns'][$exp])) { 
                    $cal           += $returns['returns'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $returns['returns'][$exp]['product_id'], 
                                    'product_code' => $returns['returns'][$exp]['product_code'],
                                    'warehouse_id' => $returns['returns'][$exp]['warehouse_id'],
                                    'expiry'       => $returns['returns'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($transfers_in['transfers_in'][$exp])) {
                    $cal           += $transfers_in['transfers_in'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $transfers_in['transfers_in'][$exp]['product_id'], 
                                    'product_code' => $transfers_in['transfers_in'][$exp]['product_code'],
                                    'warehouse_id' => $transfers_in['transfers_in'][$exp]['warehouse_id'],
                                    'expiry'       => $transfers_in['transfers_in'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($transfers_out['transfers_out'][$exp])) {
                    $cal           -= $transfers_out['transfers_out'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $transfers_out['transfers_out'][$exp]['product_id'], 
                                    'product_code' => $transfers_out['transfers_out'][$exp]['product_code'],
                                    'warehouse_id' => $transfers_out['transfers_out'][$exp]['warehouse_id'],
                                    'expiry'       => $transfers_out['transfers_out'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($adjustments_add['adjustments_add'][$exp])) {
                    $cal           += $adjustments_add['adjustments_add'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $adjustments_add['adjustments_add'][$exp]['product_id'], 
                                    'product_code' => $adjustments_add['adjustments_add'][$exp]['product_code'],
                                    'warehouse_id' => $adjustments_add['adjustments_add'][$exp]['warehouse_id'],
                                    'expiry'       => $adjustments_add['adjustments_add'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($adjustments_sub['adjustments_sub'][$exp])) {
                    $cal           -= $adjustments_sub['adjustments_sub'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $adjustments_sub['adjustments_sub'][$exp]['product_id'], 
                                    'product_code' => $adjustments_sub['adjustments_sub'][$exp]['product_code'],
                                    'warehouse_id' => $adjustments_sub['adjustments_sub'][$exp]['warehouse_id'],
                                    'expiry'       => $adjustments_sub['adjustments_sub'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($rewards_exchanged_in['rewards_exchanged_in'][$exp])) {
                    $cal           += $rewards_exchanged_in['rewards_exchanged_in'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $rewards_exchanged_in['rewards_exchanged_in'][$exp]['product_id'], 
                                    'product_code' => $rewards_exchanged_in['rewards_exchanged_in'][$exp]['product_code'],
                                    'warehouse_id' => $rewards_exchanged_in['rewards_exchanged_in'][$exp]['warehouse_id'],
                                    'expiry'       => $rewards_exchanged_in['rewards_exchanged_in'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($rewards_received_in['rewards_received_in'][$exp])) {
                    $cal           += $rewards_received_in['rewards_received_in'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $rewards_received_in['rewards_received_in'][$exp]['product_id'], 
                                    'product_code' => $rewards_received_in['rewards_received_in'][$exp]['product_code'],
                                    'warehouse_id' => $rewards_received_in['rewards_received_in'][$exp]['warehouse_id'],
                                    'expiry'       => $rewards_received_in['rewards_received_in'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($rewards_exchanged_out['rewards_exchanged_out'][$exp])) {
                    $cal           -= $rewards_exchanged_out['rewards_exchanged_out'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $rewards_exchanged_out['rewards_exchanged_out'][$exp]['product_id'], 
                                    'product_code' => $rewards_exchanged_out['rewards_exchanged_out'][$exp]['product_code'],
                                    'warehouse_id' => $rewards_exchanged_out['rewards_exchanged_out'][$exp]['warehouse_id'],
                                    'expiry'       => $rewards_exchanged_out['rewards_exchanged_out'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($rewards_received_out['rewards_received_out'][$exp])) {
                    $cal           -= $rewards_received_out['rewards_received_out'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $rewards_received_out['rewards_received_out'][$exp]['product_id'], 
                                    'product_code' => $rewards_received_out['rewards_received_out'][$exp]['product_code'],
                                    'warehouse_id' => $rewards_received_out['rewards_received_out'][$exp]['warehouse_id'],
                                    'expiry'       => $rewards_received_out['rewards_received_out'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (!empty($stock)) {
                    if (isset($purchase_items_balance_qty[$exp])) {
                        if ($stock[$exp]['quantity'] != $purchase_items_balance_qty[$exp]['quantity']) {
                            $qty = $stock[$exp]['quantity'] - $purchase_items_balance_qty[$exp]['quantity'];
                        } else {
                            continue;
                        }
                    } else {
                        $qty = $stock[$exp]['quantity'];
                    }
                    $clause = ['product_id' => $stock[$exp]['product_id'], 'expiry' => $stock[$exp]['expiry'], 'option_id' => null, 'warehouse_id' => $stock[$exp]['warehouse_id'], 'status' => 'received'];
                }
                if (!empty($clause)) {
                    $this->setPurchaseItem_($clause, $qty);
                }
            }
        }
    }

    public function checkPurchaseItemsBalanceQty($product_id, $warehouse_id)
    {
        $purchase_items_balance_qty                     = $this->getPurchasedItemBalanceQty($product_id, $warehouse_id);
        $purchases['purchases']                         = $this->getPurchases($product_id, $warehouse_id); 
        $sales['sales']                                 = $this->getSales($product_id, $warehouse_id); 
        $returns['returns']                             = $this->getReturns($product_id, $warehouse_id);
        $transfers_in['transfers_in']                   = $this->getTransfers_IN($product_id, $warehouse_id);
        $transfers_out['transfers_out']                 = $this->getTransfers_OUT($product_id, $warehouse_id);
        $adjustments_add['adjustments_add']             = $this->getAdjustments_ADD($product_id, $warehouse_id);
        $adjustments_sub['adjustments_sub']             = $this->getAdjustments_SUB($product_id, $warehouse_id);
        $rewards_exchanged_in['rewards_exchanged_in']   = $this->getRewardsExchanged_IN($product_id, $warehouse_id);
        $rewards_received_in['rewards_received_in']     = $this->getRewardsReceived_IN($product_id, $warehouse_id);
        $rewards_exchanged_out['rewards_exchanged_out'] = $this->getRewardsExchanged_OUT($product_id, $warehouse_id);
        $rewards_received_out['rewards_received_out']   = $this->getRewardsReceived_OUT($product_id, $warehouse_id);
        $stock     = array();
        $group_exp = array_unique(
            array_merge(
                $purchases['purchases']                         ? array_keys($purchases['purchases']) : array(), 
                $sales['sales']                                 ? array_keys($sales['sales']) : array(), 
                $returns['returns']                             ? array_keys($returns['returns']) : array(), 
                $transfers_in['transfers_in']                   ? array_keys($transfers_in['transfers_in']) : array(), 
                $transfers_out['transfers_out']                 ? array_keys($transfers_out['transfers_out']) : array(), 
                $adjustments_add['adjustments_add']             ? array_keys($adjustments_add['adjustments_add']) : array(), 
                $adjustments_sub['adjustments_sub']             ? array_keys($adjustments_sub['adjustments_sub']) : array(),
                $rewards_exchanged_in['rewards_exchanged_in']   ? array_keys($rewards_exchanged_in['rewards_exchanged_in']) : array(),
                $rewards_received_in['rewards_received_in']     ? array_keys($rewards_received_in['rewards_received_in']) : array(),
                $rewards_exchanged_out['rewards_exchanged_out'] ? array_keys($rewards_exchanged_out['rewards_exchanged_out']) : array(),
                $rewards_received_out['rewards_received_out']   ? array_keys($rewards_received_out['rewards_received_out']) : array()
            )
        );
        if(!empty($group_exp)){
            foreach ($group_exp as $key => $exp) {
                $cal = 0;
                if(isset($purchases['purchases'][$exp])){
                    $cal           = $purchases['purchases'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $purchases['purchases'][$exp]['product_id'], 
                                    'product_code' => $purchases['purchases'][$exp]['product_code'],
                                    'warehouse_id' => $purchases['purchases'][$exp]['warehouse_id'],
                                    'expiry'       => $purchases['purchases'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
               
                if(isset($sales['sales'][$exp])){
                    $cal           -= $sales['sales'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $sales['sales'][$exp]['product_id'], 
                                    'product_code' => $sales['sales'][$exp]['product_code'],
                                    'warehouse_id' => $sales['sales'][$exp]['warehouse_id'],
                                    'expiry'       => $sales['sales'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if(isset($returns['returns'][$exp])){
                    $cal           += $returns['returns'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $returns['returns'][$exp]['product_id'], 
                                    'product_code' => $returns['returns'][$exp]['product_code'],
                                    'warehouse_id' => $returns['returns'][$exp]['warehouse_id'],
                                    'expiry'       => $returns['returns'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if(isset($transfers_in['transfers_in'][$exp])){
                    $cal           += $transfers_in['transfers_in'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $transfers_in['transfers_in'][$exp]['product_id'], 
                                    'product_code' => $transfers_in['transfers_in'][$exp]['product_code'],
                                    'warehouse_id' => $transfers_in['transfers_in'][$exp]['warehouse_id'],
                                    'expiry'       => $transfers_in['transfers_in'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if(isset($transfers_out['transfers_out'][$exp])){
                    $cal           -= $transfers_out['transfers_out'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $transfers_out['transfers_out'][$exp]['product_id'], 
                                    'product_code' => $transfers_out['transfers_out'][$exp]['product_code'],
                                    'warehouse_id' => $transfers_out['transfers_out'][$exp]['warehouse_id'],
                                    'expiry'       => $transfers_out['transfers_out'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if(isset($adjustments_add['adjustments_add'][$exp])){
                    $cal           += $adjustments_add['adjustments_add'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $adjustments_add['adjustments_add'][$exp]['product_id'], 
                                    'product_code' => $adjustments_add['adjustments_add'][$exp]['product_code'],
                                    'warehouse_id' => $adjustments_add['adjustments_add'][$exp]['warehouse_id'],
                                    'expiry'       => $adjustments_add['adjustments_add'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if(isset($adjustments_sub['adjustments_sub'][$exp])){
                    $cal           -= $adjustments_sub['adjustments_sub'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $adjustments_sub['adjustments_sub'][$exp]['product_id'], 
                                    'product_code' => $adjustments_sub['adjustments_sub'][$exp]['product_code'],
                                    'warehouse_id' => $adjustments_sub['adjustments_sub'][$exp]['warehouse_id'],
                                    'expiry'       => $adjustments_sub['adjustments_sub'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($rewards_exchanged_in['rewards_exchanged_in'][$exp])) {
                    $cal           += $rewards_exchanged_in['rewards_exchanged_in'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $rewards_exchanged_in['rewards_exchanged_in'][$exp]['product_id'], 
                                    'product_code' => $rewards_exchanged_in['rewards_exchanged_in'][$exp]['product_code'],
                                    'warehouse_id' => $rewards_exchanged_in['rewards_exchanged_in'][$exp]['warehouse_id'],
                                    'expiry'       => $rewards_exchanged_in['rewards_exchanged_in'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($rewards_received_in['rewards_received_in'][$exp])) {
                    $cal           += $rewards_received_in['rewards_received_in'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $rewards_received_in['rewards_received_in'][$exp]['product_id'], 
                                    'product_code' => $rewards_received_in['rewards_received_in'][$exp]['product_code'],
                                    'warehouse_id' => $rewards_received_in['rewards_received_in'][$exp]['warehouse_id'],
                                    'expiry'       => $rewards_received_in['rewards_received_in'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($rewards_exchanged_out['rewards_exchanged_out'][$exp])) {
                    $cal           -= $rewards_exchanged_out['rewards_exchanged_out'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $rewards_exchanged_out['rewards_exchanged_out'][$exp]['product_id'], 
                                    'product_code' => $rewards_exchanged_out['rewards_exchanged_out'][$exp]['product_code'],
                                    'warehouse_id' => $rewards_exchanged_out['rewards_exchanged_out'][$exp]['warehouse_id'],
                                    'expiry'       => $rewards_exchanged_out['rewards_exchanged_out'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if (isset($rewards_received_out['rewards_received_out'][$exp])) {
                    $cal           -= $rewards_received_out['rewards_received_out'][$exp]['quantity'];
                    $stock[$exp] = array(
                                    'product_id'   => $rewards_received_out['rewards_received_out'][$exp]['product_id'], 
                                    'product_code' => $rewards_received_out['rewards_received_out'][$exp]['product_code'],
                                    'warehouse_id' => $rewards_received_out['rewards_received_out'][$exp]['warehouse_id'],
                                    'expiry'       => $rewards_received_out['rewards_received_out'][$exp]['expiry'],
                                    'quantity'     => $cal,
                                );
                }
                if(!empty($stock)){
                    if(isset($purchase_items_balance_qty[$exp])){
                        if($stock[$exp]['quantity'] != $purchase_items_balance_qty[$exp]['quantity']){
                            return true;
                        } else {
                            return false;
                        }
                    } 
                    return true;
                }
                return true;
            }
        }
    }

    public function checkOverSold_($product_id, $warehouse_id, $option_id = null)
    {
        $clause = ['product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'option_id' => $option_id];
        if ($os_items = $this->getPurchasedItems_checkOverSold($clause, 'minus')) {
            foreach ($os_items as $os) {
                if ($os->quantity_balance < 0) {
                    $clause = ['product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'option_id' => $option_id, 'expiry' => $os->expiry];
                    if ($pis = $this->getPurchasedItems_checkOverSold($clause, 'plus')) {
                        $quantity = $os->quantity_balance;
                        foreach ($pis as $pi) {
                            if ($pi->quantity_balance >= (0 - $quantity) && $quantity != 0) {
                                $balance = $pi->quantity_balance + $quantity;
                                $this->db->update('purchase_items', ['quantity_balance' => $balance], ['id' => $pi->id]);
                                $quantity = 0;
                                break;
                            } elseif ($quantity != 0) {
                                $quantity = $quantity + $pi->quantity_balance;
                                $this->db->update('purchase_items', ['quantity_balance' => 0], ['id' => $pi->id]);
                            }
                        }
                        $this->db->update('purchase_items', ['quantity_balance' => $quantity], ['id' => $os->id]);
                    }
                }   
            }
        }
    }

    public function getPurchasedItems_checkOverSold($clause = null, $condition = null)
    {
        $orderby = empty($this->Settings->accounting_method) ? 'asc' : 'desc';
        if ($this->Settings->product_expiry && $this->Settings->fefo) {
            $this->db->order_by('expiry', 'asc');
        } else {
            $this->db->order_by('date', $orderby);
            $this->db->order_by('purchase_id', $orderby);
        }
        $this->db->where($clause);
        if ($condition == 'minus') {
            $this->db->where('quantity_balance <', 0);
        } else {
            $this->db->where('quantity_balance >', 0);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $this->db->group_by('id');
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function setPurchaseItem_($clause, $qty)
    {
        if ($product = $this->getProductByID($clause['product_id'])) {
            if ($pi = $this->getPurchasedItem_($clause, $qty)) {
                if ($pi->quantity_balance > 0) {
                    $quantity_balance = $pi->quantity_balance + $qty;
                    log_message('error', 'More than zero: ' . $quantity_balance . ' = ' . $pi->quantity_balance . ' + ' . $qty . ' PI: ' . print_r($pi, true));
                } else {
                    $quantity_balance = $pi->quantity_balance + $qty;
                    log_message('error', 'Less than zero: ' . $quantity_balance . ' = ' . $pi->quantity_balance . ' + ' . $qty . ' PI: ' . print_r($pi, true));
                }
                if ($quantity_balance != 0 || ($quantity_balance == 0 && ($pi->purchase_id != null || $pi->transfer_id != null))) {
                     return $this->db->update('purchase_items', ['quantity_balance' => $quantity_balance], ['id' => $pi->id]);
                } else {
                    if ($pi->store_sale_id == null) {
                        return $this->db->delete('purchase_items', ['id' => $pi->id]);    
                    }
                }
            } else {
                $unit                        = $this->getUnitByID($product->unit);
                $clause['product_unit_id']   = $product->unit;
                $clause['product_unit_code'] = $unit->code;
                $clause['product_code']      = $product->code;
                $clause['product_name']      = $product->name;
                $clause['purchase_id']       = $clause['transfer_id']        = $clause['item_tax']      = null;
                $clause['net_unit_cost']     = $clause['real_unit_cost']     = $clause['unit_cost']     = $product->cost;
                $clause['quantity_balance']  = $clause['quantity']  = $clause['unit_quantity']  = $clause['quantity_received']  = $qty;
                $clause['subtotal']          = ($product->cost * $qty);
                if (isset($product->tax_rate) && $product->tax_rate != 0) {
                    $tax_details           = $this->site->getTaxRateByID($product->tax_rate);
                    $ctax                  = $this->calculateTax($product, $tax_details, $product->cost);
                    $item_tax              = $clause['item_tax']              = $ctax['amount'];
                    $tax                   = $clause['tax']                   = $ctax['tax'];
                    $clause['tax_rate_id'] = $tax_details->id;
                    if ($product->tax_method != 1) {
                        $clause['net_unit_cost'] = $product->cost - $item_tax;
                        $clause['unit_cost']     = $product->cost;
                    } else {
                        $clause['net_unit_cost'] = $product->cost;
                        $clause['unit_cost']     = $product->cost + $item_tax;
                    }
                    $pr_item_tax = $this->bpas->formatDecimal($item_tax * $clause['unit_quantity']);
                    if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                        $clause['gst']  = $gst_data['gst'];
                        $clause['cgst'] = $gst_data['cgst'];
                        $clause['sgst'] = $gst_data['sgst'];
                        $clause['igst'] = $gst_data['igst'];
                    }
                    $clause['subtotal'] = (($clause['net_unit_cost'] * $clause['unit_quantity']) + $pr_item_tax);
                }
                $clause['status']    = 'received';
                $clause['date']      = date('Y-m-d');
                $clause['option_id'] = !empty($clause['option_id']) && is_numeric($clause['option_id']) ? $clause['option_id'] : null;
                log_message('error', 'Why else: ' . print_r($clause, true));
                
                return $this->db->insert('purchase_items', $clause);
            }
        }
        return false;
    }

    public function getPurchasedItem_($clause, $qty = null, $expiry = null, $expiry_date = null)
    {
        $orderby = empty($this->Settings->accounting_method) ? 'asc' : 'desc';
        if ($this->Settings->product_expiry && $this->Settings->fefo) {
            $this->db->order_by('expiry', 'asc');
        } else {
            $this->db->order_by('date', $orderby);
            $this->db->order_by('purchase_id', $orderby);
        }
        if (!isset($clause['option_id']) || empty($clause['option_id'])) {
            $this->db->group_start()->where('option_id', null)->or_where('option_id', 0)->group_end();
        }
        $this->db->where($clause);
        if ($clause["expiry"] != "0000-00-00" && $clause["expiry"] == null) {
            unset($clause['purchase_id']);
            unset($clause['transfer_id']);
        } else {
            unset($clause['purchase_id']);
            unset($clause['transfer_id']);
        }
        if($expiry_date != null && $expiry_date != "0000-00-00"){
            $clause['expiry'] = $expiry_date;
        }
        // $this->db->where('purchase_id', null);
        // $this->db->where('transfer_id', null);
        if ($qty < 0) {
            $this->db->where('quantity_balance >', 0);  
        } 
        $q = $this->db->get('purchase_items');
        // $q = $this->db->get_where('purchase_items', $clause);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchasedItemBalanceQty($product_id, $warehouse_id)
    {
        $this->db->select("{$this->db->dbprefix('purchase_items')}.id, product_id, product_code, warehouse_id, expiry, SUM(quantity_balance) as quantity ");
        $this->db->from('purchase_items');
        $this->db->where('purchase_items.product_id', $product_id);
        $this->db->where('purchase_items.warehouse_id', $warehouse_id);
        $this->db->where('purchase_items.status', 'received');
        $this->db->group_by('purchase_items.expiry');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductBalanceQty($product_id = null, $warehouse_id = null)
    {
        if ($product_id) {
            $purchases            = " ( SELECT product_id, pi.warehouse_id, pi.expiry, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(quantity_balance) as balacneQty, SUM( unit_cost * quantity_balance ) balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id WHERE pi.status = 'received' ";
            $store_sp             = " ( SELECT product_id, pi.warehouse_id, pi.expiry, SUM(CASE WHEN pi.store_sale_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(quantity_balance) as balacneQty, SUM( unit_cost * quantity_balance ) balacneValue, SUM( (CASE WHEN pi.store_sale_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('sales')} p on p.id = pi.store_sale_id WHERE pi.status = 'received' ";
            $sales                = " ( SELECT product_id, si.expiry, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale, SUM( si.item_discount ) totalItemDiscount, SUM( si.total_weight ) totalWeight, s.order_discount as order_discount from " . $this->db->dbprefix('sales') . " s LEFT JOIN " . $this->db->dbprefix('sale_items') . " si on s.id = si.sale_id WHERE s.sale_status != 'pending' AND s.pos != 1 ";
            $pos_sales            = " ( SELECT product_id, ci.expiry, SUM( ci.quantity ) soldQty  FROM " . $this->db->dbprefix('sales') . " s LEFT JOIN " . $this->db->dbprefix('costing') . " ci on s.id = ci.sale_id WHERE s.sale_status != 'pending' AND s.pos = 1 ";
            $addonsales           = " ( SELECT product_id, osi.expiry, SUM( osi.quantity ) soldQty, SUM( osi.subtotal ) totalSale, s.order_discount as order_discount from " . $this->db->dbprefix('sales') . " s LEFT JOIN " . $this->db->dbprefix('sale_addon_items') . " osi on s.id = osi.sale_id WHERE s.sale_status != 'pending'";
            $combosales           = " ( SELECT product_id, csi.expiry, SUM( csi.quantity ) soldQty, SUM( csi.subtotal ) totalSale, s.order_discount as order_discount from " . $this->db->dbprefix('sales') . " s LEFT JOIN " . $this->db->dbprefix('sale_combo_items') . " csi on s.id = csi.sale_id WHERE s.sale_status != 'pending'"; 
            $returns              = " ( SELECT product_id, sri.expiry, SUM(sri.quantity) as returnQty, SUM(sri.subtotal) returnTotalSale from {$this->db->dbprefix('returns')} sr LEFT JOIN {$this->db->dbprefix('return_items')} sri on sr.id = sri.return_id ";
            $transfers_in         = " ( SELECT product_id, pi.expiry, SUM(quantity) as transferQty, SUM(quantity_balance) as balacneQty, SUM(unit_cost * quantity_balance) balacneValue, SUM(pi.subtotal) totalTransfer from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('transfers')} t on t.id = pi.transfer_id WHERE pi.status = 'received' ";
            $transfers_out        = " ( SELECT product_id, pi.expiry, SUM(quantity) as transferQty, SUM(quantity_balance) as balacneQty, SUM(unit_cost * quantity_balance) balacneValue, SUM(pi.subtotal) totalTransfer from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('transfers')} t on t.id = pi.transfer_id WHERE pi.status = 'received' ";
            $adjustments_add      = " ( SELECT product_id, aji.expiry, SUM(aji.quantity) adjustmentQty FROM {$this->db->dbprefix('adjustment_items')} aji LEFT JOIN {$this->db->dbprefix('adjustments')} aj ON aj.id = aji.adjustment_id WHERE aji.type = 'addition' ";
            $adjustments_sub      = " ( SELECT product_id, aji.expiry, SUM(aji.quantity) adjustmentQty FROM {$this->db->dbprefix('adjustment_items')} aji LEFT JOIN {$this->db->dbprefix('adjustments')} aj ON aj.id = aji.adjustment_id WHERE aji.type = 'subtraction' ";
            $rewards_exchanged_in = " ( SELECT rexi.exchange_product_id AS product_id, SUM(rexi.exchange_quantity) AS exchangedQty FROM {$this->db->dbprefix('rewards_exchange')} rex LEFT JOIN {$this->db->dbprefix('reward_exchange_items')} rexi ON rexi.reward_exchange_id = rex.id WHERE rex.category = 'customer' "; 
            $rewards_received_in  = " ( 
                                        SELECT rexi.receive_product_id AS product_id, SUM(sti.quantity) AS receivedQty
                                        FROM {$this->db->dbprefix('rewards_exchange')} rex 
                                        LEFT JOIN {$this->db->dbprefix('reward_exchange_items')} rexi ON rexi.reward_exchange_id = rex.id 
                                        LEFT JOIN (
                                            SELECT 
                                                {$this->db->dbprefix('stock_received')}.date,
                                                {$this->db->dbprefix('stock_received')}.warehouse_id,
                                                {$this->db->dbprefix('stock_received_items')}.product_id, 
                                                {$this->db->dbprefix('stock_received_items')}.reward_exchange_item_id, 
                                                SUM(COALESCE({$this->db->dbprefix('stock_received_items')}.quantity, 0)) AS quantity
                                            FROM {$this->db->dbprefix('stock_received')} 
                                            LEFT JOIN 
                                                {$this->db->dbprefix('stock_received_items')} ON 
                                                {$this->db->dbprefix('stock_received_items')}.stock_received_id = {$this->db->dbprefix('stock_received')}.id AND 
                                                {$this->db->dbprefix('stock_received')}.reward_exchange_id IS NOT NULL
                                            GROUP BY {$this->db->dbprefix('stock_received_items')}.reward_exchange_item_id
                                        ) sti ON sti.reward_exchange_item_id = rexi.id AND sti.product_id = rexi.receive_product_id
                                        WHERE rex.category = 'supplier' AND rex.type = 'product' ";
            $rewards_exchanged_out   = " ( SELECT rexi.exchange_product_id AS product_id, SUM(rexi.exchange_quantity) AS exchangedQty FROM {$this->db->dbprefix('rewards_exchange')} rex LEFT JOIN {$this->db->dbprefix('reward_exchange_items')} rexi ON rexi.reward_exchange_id = rex.id WHERE rex.category = 'supplier' ";
            $rewards_received_out    = " ( 
                                        SELECT rexi.receive_product_id AS product_id, SUM(sti.quantity) AS receivedQty
                                        FROM {$this->db->dbprefix('rewards_exchange')} rex 
                                        LEFT JOIN {$this->db->dbprefix('reward_exchange_items')} rexi ON rexi.reward_exchange_id = rex.id 
                                        LEFT JOIN (
                                            SELECT 
                                                {$this->db->dbprefix('stock_received')}.date,
                                                {$this->db->dbprefix('stock_received')}.warehouse_id,
                                                {$this->db->dbprefix('stock_received_items')}.product_id, 
                                                {$this->db->dbprefix('stock_received_items')}.reward_exchange_item_id, 
                                                SUM(COALESCE({$this->db->dbprefix('stock_received_items')}.quantity, 0)) AS quantity
                                            FROM {$this->db->dbprefix('stock_received')} 
                                            LEFT JOIN 
                                                {$this->db->dbprefix('stock_received_items')} ON 
                                                {$this->db->dbprefix('stock_received_items')}.stock_received_id = {$this->db->dbprefix('stock_received')}.id AND 
                                                {$this->db->dbprefix('stock_received')}.reward_exchange_id IS NOT NULL
                                            GROUP BY {$this->db->dbprefix('stock_received_items')}.reward_exchange_item_id
                                        ) sti ON sti.reward_exchange_item_id = rexi.id AND sti.product_id = rexi.receive_product_id
                                        WHERE rex.category = 'customer' AND rex.type = 'product' ";

            if ($warehouse_id) {
                $purchases            .= " AND pi.warehouse_id     = '{$warehouse_id}' ";
                $store_sp             .= " AND pi.warehouse_id     = '{$warehouse_id}' ";
                $sales                .= " AND si.warehouse_id     = '{$warehouse_id}' ";
                $pos_sales            .= " AND s.warehouse_id      = '{$warehouse_id}' ";
                $addonsales           .= " AND osi.warehouse_id    = '{$warehouse_id}' ";
                $combosales           .= " AND csi.warehouse_id    = '{$warehouse_id}' ";
                $returns              .= " WHERE sr.warehouse_id   = '{$warehouse_id}' ";
                $transfers_in         .= " AND t.to_warehouse_id   = '{$warehouse_id}' ";
                $transfers_out        .= " AND t.from_warehouse_id = '{$warehouse_id}' ";
                $adjustments_add      .= " AND aji.warehouse_id    = '{$warehouse_id}' ";
                $adjustments_sub      .= " AND aji.warehouse_id    = '{$warehouse_id}' ";
                $rewards_exchanged_in .= " AND rex.warehouse_id    = '{$warehouse_id}' ";
                $rewards_received_in  .= " AND sti.warehouse_id    = '{$warehouse_id}' ";
                $rewards_exchanged_out .= " AND rex.warehouse_id    = '{$warehouse_id}' ";
                $rewards_received_out  .= " AND sti.warehouse_id    = '{$warehouse_id}' ";
            }
            $purchases             .= ' GROUP BY pi.product_id            ) bpas_Purchases ';
            $store_sp              .= ' GROUP BY pi.product_id            ) bpas_Store_SP ';
            $sales                 .= ' GROUP BY si.product_id            ) bpas_Sales ';
            $pos_sales             .= ' GROUP BY ci.product_id            ) bpas_POS_Sales ';
            $addonsales            .= ' GROUP BY osi.product_id           ) bpas_Osales ';
            $combosales            .= ' GROUP BY csi.product_id           ) bpas_Csales ';
            $returns               .= ' GROUP BY sri.product_id           ) bpas_Returns ';
            $transfers_in          .= ' GROUP BY pi.product_id            ) bpas_Transfers_IN ';
            $transfers_out         .= ' GROUP BY pi.product_id            ) bpas_Transfers_OUT ';
            $adjustments_add       .= ' GROUP BY aji.product_id           ) bpas_Adjustments_ADD ';
            $adjustments_sub       .= ' GROUP BY aji.product_id           ) bpas_Adjustments_SUB ';
            $rewards_exchanged_in  .= ' GROUP BY rexi.exchange_product_id ) bpas_RwEx_IN ';
            $rewards_received_in   .= ' GROUP BY sti.product_id           ) bpas_RwRe_IN ';
            $rewards_exchanged_out .= ' GROUP BY rexi.exchange_product_id ) bpas_RwEx_OUT ';
            $rewards_received_out  .= ' GROUP BY sti.product_id           ) bpas_RwRe_OUT ';
            $this->db->select(
                    $this->db->dbprefix('products') . '.id as product_id, ' . $this->db->dbprefix('products') . ".code as product_code,
                    bpas_Purchases.warehouse_id,     
                    bpas_Purchases.purchasedQty as ppurchasedQty,
                    bpas_Store_SP.purchasedQty as spurchasedQty,
                    bpas_Sales.soldQty as ssoldQty,
                    bpas_POS_Sales.soldQty as possoldQty,
                    bpas_Osales.soldQty as addonsoldQty,
                    bpas_Csales.soldQty as combosoldQty,
                    bpas_Returns.returnQty,
                    bpas_Transfers_IN.transferQty as In_transferQty,
                    bpas_Transfers_OUT.transferQty as Out_transferQty,
                    bpas_Adjustments_ADD.adjustmentQty as Add_adjustmentQty,
                    bpas_Adjustments_SUB.adjustmentQty as Sub_adjustmentQty,
                    bpas_RwEx_IN.exchangedQty as In_exchangedQty,
                    bpas_RwRe_IN.receivedQty as In_receivedQty,
                    bpas_RwEx_OUT.exchangedQty as Out_exchangedQty,
                    bpas_RwRe_OUT.receivedQty as Out_receivedQty,
                    (   
                        COALESCE( bpas_Purchases.purchasedQty, 0 ) +
                        COALESCE( bpas_Store_SP.purchasedQty, 0 ) -
                        COALESCE( bpas_Sales.soldQty, 0 ) -
                        COALESCE( bpas_POS_Sales.soldQty, 0 ) -
                        COALESCE( bpas_Osales.soldQty, 0 ) -
                        COALESCE( bpas_Csales.soldQty, 0 ) + 
                        COALESCE( bpas_Returns.returnQty, 0 ) + 
                        COALESCE( bpas_Transfers_IN.transferQty, 0 ) -
                        COALESCE( bpas_Transfers_OUT.transferQty, 0 ) +
                        COALESCE( bpas_Adjustments_ADD.adjustmentQty, 0 ) -
                        COALESCE( bpas_Adjustments_SUB.adjustmentQty, 0 ) +
                        COALESCE( bpas_RwEx_IN.exchangedQty, 0 ) +
                        COALESCE( bpas_RwRe_IN.receivedQty, 0 ) -
                        COALESCE( bpas_RwEx_OUT.exchangedQty, 0 ) -
                        COALESCE( bpas_RwRe_OUT.receivedQty, 0 )
                    ) as balanceQty ", false)
                ->from('products')
                ->join($purchases,             'products.id = Purchases.product_id',       'left')
                ->join($store_sp,              'products.id = Store_SP.product_id',        'left')
                ->join($sales,                 'products.id = Sales.product_id',           'left')
                ->join($pos_sales,             'products.id = POS_Sales.product_id',       'left')
                ->join($addonsales,            'products.id = Sales.product_id',           'left') 
                ->join($combosales,            'products.id = bpas_Csales.product_id',     'left')
                ->join($returns,               'products.id = Returns.product_id',         'left')
                ->join($transfers_in,          'products.id = Transfers_IN.product_id',    'left')
                ->join($transfers_out,         'products.id = Transfers_OUT.product_id',   'left')
                ->join($adjustments_add,       'products.id = Adjustments_ADD.product_id', 'left')
                ->join($adjustments_sub,       'products.id = Adjustments_SUB.product_id', 'left')
                ->join($rewards_exchanged_in,  'products.id = RwEx_IN.product_id',         'left')
                ->join($rewards_received_in,   'products.id = RwRe_IN.product_id',         'left')
                ->join($rewards_exchanged_out, 'products.id = RwEx_OUT.product_id',        'left')
                ->join($rewards_received_out,  'products.id = RwRe_OUT.product_id',        'left')
                ->where('products.id', $product_id);
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data = (array) $row;
                }
                return $data;
            }
            return false;
        }
        return false;
    }

    public function getPurchases($product_id = null, $warehouse_id)
    {
        $this->db->select("{$this->db->dbprefix('purchase_items')}.id, product_id, product_code, warehouse_id, expiry, SUM(CASE WHEN purchase_id IS NOT NULL THEN quantity ELSE 0 END) as quantity");
        $this->db->from('purchase_items');
        $this->db->where('purchase_items.product_id', $product_id);
        $this->db->where('purchase_items.warehouse_id', $warehouse_id);
        $this->db->where('purchase_items.status', 'received');
        $this->db->group_by('purchase_items.expiry');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getSales($product_id = null, $warehouse_id)
    {
        $this->db->select("{$this->db->dbprefix('sale_items')}.id, product_id, product_code, {$this->db->dbprefix('sale_items')}.warehouse_id, expiry, SUM(quantity) as quantity");
        $this->db->from('sale_items');
        $this->db->join('sales', 'sales.id=sale_items.sale_id', 'left');
        $this->db->where('sale_items.product_id', $product_id);
        $this->db->where('sale_items.warehouse_id', $warehouse_id);
        $this->db->where('sales.sale_status !=', 'pending');
        $this->db->where('sales.pos !=', 1);
        $this->db->group_by('sale_items.expiry');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getPOSSales($product_id = null, $warehouse_id)
    {
        $this->db->select("{$this->db->dbprefix('costing')}.id, product_id, {$this->db->dbprefix('products')}.code as product_code, {$this->db->dbprefix('sales')}.warehouse_id, {$this->db->dbprefix('costing')}.expiry, SUM({$this->db->dbprefix('costing')}.quantity) as quantity");
        $this->db->from('costing');
        $this->db->join('products', 'products.id=costing.product_id', 'left');
        $this->db->join('sales', 'sales.id=costing.sale_id', 'left');
        $this->db->where('costing.product_id', $product_id);
        $this->db->where('sales.warehouse_id', $warehouse_id);
        $this->db->where('sales.sale_status !=', 'pending');
        $this->db->where('sales.pos', 1);
        $this->db->group_by('costing.expiry');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getSalesPurchasesStore($product_id = null, $warehouse_id)
    {
        $this->db->select("{$this->db->dbprefix('sale_items')}.id, product_id, product_code, {$this->db->dbprefix('sale_items')}.to_warehouse_id as warehouse_id, expiry, SUM(quantity) as quantity");
        $this->db->from('sale_items');
        $this->db->join('sales', 'sales.id=sale_items.sale_id', 'left');
        $this->db->where('sale_items.product_id', $product_id);
        $this->db->where('sale_items.to_warehouse_id', $warehouse_id);
        $this->db->where('sales.sale_status !=', 'pending');
        $this->db->where('sales.to_warehouse_id !=', NULL);
        $this->db->where('sales.store_sale', 1);
        $this->db->where('sales.pos !=', 1);
        $this->db->group_by('sale_items.expiry');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getaddonSales($product_id = null, $warehouse_id)
    {
        $this->db->select("{$this->db->dbprefix('sale_addon_items')}.id, product_id, product_code, {$this->db->dbprefix('sale_addon_items')}.warehouse_id, expiry, SUM(quantity) as quantity");
        $this->db->from('sale_addon_items');
        $this->db->join('sales', 'sales.id=sale_addon_items.sale_id', 'left');
        $this->db->where('sale_addon_items.product_id', $product_id);
        $this->db->where('sale_addon_items.warehouse_id', $warehouse_id);
        $this->db->where('sales.sale_status !=', 'pending');
        $this->db->where('sales.pos !=', 1);
        $this->db->group_by('sale_addon_items.expiry');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getcomboSales($product_id = null, $warehouse_id )
    { 
        $this->db->select("{$this->db->dbprefix('sale_combo_items')}.id, product_id, product_code, {$this->db->dbprefix('sale_combo_items')}.warehouse_id, expiry, SUM(quantity) as quantity");
        $this->db->from('sale_combo_items');
        $this->db->join('sales', 'sales.id=sale_combo_items.sale_id', 'left');
        $this->db->where('sale_combo_items.product_id', $product_id);
        $this->db->where('sale_combo_items.warehouse_id', $warehouse_id);
        $this->db->where('sales.sale_status !=', 'pending');
        $this->db->group_by('sale_combo_items.expiry');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getReturns($product_id = null, $warehouse_id)
    {
        $this->db->select("{$this->db->dbprefix('return_items')}.id, product_id, product_code, {$this->db->dbprefix('return_items')}.warehouse_id, expiry, SUM(quantity) as quantity");
        $this->db->from('return_items');
        $this->db->where('return_items.product_id', $product_id);
        $this->db->where('return_items.warehouse_id', $warehouse_id);
        $this->db->group_by('return_items.expiry');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getTransfers_IN($product_id = null, $warehouse_id)
    {
        $this->db->select("{$this->db->dbprefix('purchase_items')}.id, product_id, product_code, {$this->db->dbprefix('transfers')}.to_warehouse_id as warehouse_id, expiry, SUM(quantity) as quantity");
        $this->db->from('purchase_items');
        $this->db->join('transfers', 'purchase_items.transfer_id=transfers.id', 'left');
        $this->db->where('purchase_items.product_id', $product_id);
        $this->db->where('purchase_items.status', 'received');
        $this->db->where('transfers.to_warehouse_id', $warehouse_id);
        $this->db->group_by('purchase_items.expiry');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getTransfers_OUT($product_id = null, $warehouse_id)
    {
        $this->db->select("{$this->db->dbprefix('purchase_items')}.id, product_id, product_code, {$this->db->dbprefix('transfers')}.from_warehouse_id as warehouse_id, expiry, SUM(quantity) as quantity");
        $this->db->from('purchase_items');
        $this->db->join('transfers', 'purchase_items.transfer_id=transfers.id', 'left');
        $this->db->where('purchase_items.product_id', $product_id);
        $this->db->where('purchase_items.status', 'received');
        $this->db->where('transfers.from_warehouse_id', $warehouse_id);
        $this->db->group_by('purchase_items.expiry');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getAdjustments_ADD($product_id = null, $warehouse_id)
    {
        $this->db->select("{$this->db->dbprefix('adjustment_items')}.id, {$this->db->dbprefix('adjustment_items')}.product_id, {$this->db->dbprefix('products')}.code as product_code, {$this->db->dbprefix('adjustment_items')}.warehouse_id, {$this->db->dbprefix('adjustment_items')}.expiry, SUM({$this->db->dbprefix('adjustment_items')}.quantity) as quantity");
        $this->db->from('adjustment_items');
        $this->db->join('products', 'products.id=adjustment_items.product_id', 'left');
        $this->db->where('adjustment_items.product_id', $product_id);
        $this->db->where('adjustment_items.warehouse_id', $warehouse_id);
        $this->db->where('adjustment_items.type', 'addition');
        $this->db->group_by('adjustment_items.expiry');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getAdjustments_SUB($product_id = null, $warehouse_id)
    {
        $this->db->select("
            {$this->db->dbprefix('adjustment_items')}.id, 
            {$this->db->dbprefix('adjustment_items')}.product_id, 
            {$this->db->dbprefix('products')}.code as product_code, 
            {$this->db->dbprefix('adjustment_items')}.warehouse_id, 
            {$this->db->dbprefix('adjustment_items')}.expiry, 
            SUM({$this->db->dbprefix('adjustment_items')}.quantity) as quantity
        ");
        $this->db->from('adjustment_items');
        $this->db->join('products', 'products.id=adjustment_items.product_id', 'left');
        $this->db->where('adjustment_items.product_id', $product_id);
        $this->db->where('adjustment_items.warehouse_id', $warehouse_id);
        $this->db->where('adjustment_items.type', 'subtraction');
        $this->db->group_by('adjustment_items.expiry');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getRewardsExchanged_IN($product_id = null, $warehouse_id)
    {
        $this->db->select("
            {$this->db->dbprefix('reward_exchange_items')}.id,
            {$this->db->dbprefix('reward_exchange_items')}.exchange_product_id AS product_id,
            {$this->db->dbprefix('products')}.code AS product_code,
            {$this->db->dbprefix('reward_exchange_items')}.expiry,
            SUM({$this->db->dbprefix('reward_exchange_items')}.exchange_quantity) AS quantity,
            {$this->db->dbprefix('rewards_exchange')}.warehouse_id
        ");
        $this->db->from('rewards_exchange');
        $this->db->join('reward_exchange_items', 'reward_exchange_items.reward_exchange_id = rewards_exchange.id', 'left');
        $this->db->join('products', 'products.id = reward_exchange_items.exchange_product_id', 'left');
        $this->db->where('reward_exchange_items.exchange_product_id', $product_id);
        $this->db->where('rewards_exchange.warehouse_id', $warehouse_id);
        $this->db->where('rewards_exchange.category', 'customer');
        $this->db->group_by('reward_exchange_items.expiry');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getRewardsReceived_IN($product_id = null, $warehouse_id)
    {
        $this->db->select("
            {$this->db->dbprefix('reward_exchange_items')}.id,
            {$this->db->dbprefix('reward_exchange_items')}.receive_product_id AS product_id,
            {$this->db->dbprefix('products')}.code AS product_code,
            {$this->db->dbprefix('sti')}.expiry,
            SUM({$this->db->dbprefix('sti')}.quantity) AS quantity,
            {$this->db->dbprefix('rewards_exchange')}.warehouse_id
        ");
        $this->db->from("rewards_exchange");
        $this->db->join('reward_exchange_items', 'reward_exchange_items.reward_exchange_id = rewards_exchange.id', 'left');
        $this->db->join('products', 'products.id = reward_exchange_items.receive_product_id', 'left');
        $this->db->join("
                (
                    SELECT 
                        {$this->db->dbprefix('stock_received')}.date,
                        {$this->db->dbprefix('stock_received')}.warehouse_id,
                        {$this->db->dbprefix('stock_received_items')}.product_id, 
                        {$this->db->dbprefix('stock_received_items')}.expiry, 
                        {$this->db->dbprefix('stock_received_items')}.reward_exchange_item_id, 
                        SUM(COALESCE({$this->db->dbprefix('stock_received_items')}.quantity, 0)) AS quantity
                    FROM {$this->db->dbprefix('stock_received')} 
                    LEFT JOIN 
                        {$this->db->dbprefix('stock_received_items')} ON 
                        {$this->db->dbprefix('stock_received_items')}.stock_received_id = {$this->db->dbprefix('stock_received')}.id AND 
                        {$this->db->dbprefix('stock_received')}.reward_exchange_id IS NOT NULL
                    GROUP BY {$this->db->dbprefix('stock_received_items')}.reward_exchange_item_id
                ) bpas_sti
            ", "sti.reward_exchange_item_id = reward_exchange_items.id AND sti.product_id = reward_exchange_items.receive_product_id", "left");
        $this->db->where('reward_exchange_items.receive_product_id', $product_id);
        $this->db->where('rewards_exchange.warehouse_id', $warehouse_id);
        $this->db->where('rewards_exchange.category', 'supplier');
        $this->db->where('rewards_exchange.type', 'product');
        $this->db->group_by('sti.expiry');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getRewardsExchanged_OUT($product_id = null, $warehouse_id)
    {
        $this->db->select("
            {$this->db->dbprefix('reward_exchange_items')}.id,
            {$this->db->dbprefix('reward_exchange_items')}.exchange_product_id AS product_id,
            {$this->db->dbprefix('products')}.code AS product_code,
            {$this->db->dbprefix('reward_exchange_items')}.expiry,
            SUM({$this->db->dbprefix('reward_exchange_items')}.exchange_quantity) AS quantity,
            {$this->db->dbprefix('rewards_exchange')}.warehouse_id
        ");
        $this->db->from('rewards_exchange');
        $this->db->join('reward_exchange_items', 'reward_exchange_items.reward_exchange_id = rewards_exchange.id', 'left');
        $this->db->join('products', 'products.id = reward_exchange_items.exchange_product_id', 'left');
        $this->db->where('reward_exchange_items.exchange_product_id', $product_id);
        $this->db->where('rewards_exchange.warehouse_id', $warehouse_id);
        $this->db->where('rewards_exchange.category', 'supplier');
        $this->db->group_by('reward_exchange_items.expiry');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function getRewardsReceived_OUT($product_id = null, $warehouse_id)
    {
        $this->db->select("
            {$this->db->dbprefix('reward_exchange_items')}.id,
            {$this->db->dbprefix('reward_exchange_items')}.receive_product_id AS product_id,
            {$this->db->dbprefix('products')}.code AS product_code,
            {$this->db->dbprefix('sti')}.expiry,
            SUM({$this->db->dbprefix('sti')}.quantity) AS quantity,
            {$this->db->dbprefix('rewards_exchange')}.warehouse_id
        ");
        $this->db->from("rewards_exchange");
        $this->db->join('reward_exchange_items', 'reward_exchange_items.reward_exchange_id = rewards_exchange.id', 'left');
        $this->db->join('products', 'products.id = reward_exchange_items.receive_product_id', 'left');
        $this->db->join("
                (
                    SELECT 
                        {$this->db->dbprefix('stock_received')}.date,
                        {$this->db->dbprefix('stock_received')}.warehouse_id,
                        {$this->db->dbprefix('stock_received_items')}.product_id, 
                        {$this->db->dbprefix('stock_received_items')}.expiry, 
                        {$this->db->dbprefix('stock_received_items')}.reward_exchange_item_id, 
                        SUM(COALESCE({$this->db->dbprefix('stock_received_items')}.quantity, 0)) AS quantity
                    FROM {$this->db->dbprefix('stock_received')} 
                    LEFT JOIN 
                        {$this->db->dbprefix('stock_received_items')} ON 
                        {$this->db->dbprefix('stock_received_items')}.stock_received_id = {$this->db->dbprefix('stock_received')}.id AND 
                        {$this->db->dbprefix('stock_received')}.reward_exchange_id IS NOT NULL
                    GROUP BY {$this->db->dbprefix('stock_received_items')}.reward_exchange_item_id
                ) bpas_sti
            ", "sti.reward_exchange_item_id = reward_exchange_items.id AND sti.product_id = reward_exchange_items.receive_product_id", "left");
        $this->db->where('reward_exchange_items.receive_product_id', $product_id);
        $this->db->where('rewards_exchange.warehouse_id', $warehouse_id);
        $this->db->where('rewards_exchange.category', 'customer');
        $this->db->where('rewards_exchange.type', 'product');
        $this->db->group_by('sti.expiry');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->expiry ? $row->expiry : 'NULL'] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function updateExpirydate()
    {
        $this->db->update('return_items', ['expiry' => null], ['expiry' => 0000-00-00]);
        $this->db->update('purchase_items', ['expiry' => null], ['expiry' => 0000-00-00]);
        $this->db->update('sale_items', ['expiry' => null], ['expiry' => 0000-00-00]);
        $this->db->update('transfer_items', ['expiry' => null], ['expiry' => 0000-00-00]);
        $this->db->update('adjustment_items', ['expiry' => null], ['expiry' => 0000-00-00]);
        $this->db->update('costing', ['expiry' => null], ['expiry' => 0000-00-00]);
    }

    public function checkSyncStock($product_id, $warehouse_id = null)
    {
        $q_minus = " ( 
                        SELECT COALESCE(SUM(COALESCE(pi_minus.quantity_balance, 0)), 0)
                        FROM {$this->db->dbprefix('purchase_items')} pi_minus 
                        WHERE pi_minus.product_id = {$product_id} AND pi_minus.warehouse_id = {$warehouse_id} AND pi_minus.status = 'received' AND pi_minus.quantity_balance < 0
                        GROUP BY pi_minus.product_id
                        LIMIT 1
                    ) ";

        $q_plus = " ( 
                        SELECT COALESCE(SUM(COALESCE(pi_plus.quantity_balance, 0)), 0)
                        FROM {$this->db->dbprefix('purchase_items')} pi_plus 
                        WHERE pi_plus.product_id = {$product_id} AND pi_plus.warehouse_id = {$warehouse_id} AND pi_plus.status = 'received' AND pi_plus.quantity_balance > 0
                        GROUP BY pi_plus.product_id
                        LIMIT 1
                    ) ";

        $q_pq  = " (
                        SELECT COALESCE(SUM(COALESCE(q_pq.quantity_balance, 0)), 0)
                        FROM {$this->db->dbprefix('purchase_items')} q_pq 
                        WHERE q_pq.product_id = {$product_id} AND q_pq.status = 'received' 
                        GROUP BY q_pq.product_id
                        LIMIT 1
                    ) ";
        
        $q_wq  = " (
                        SELECT COALESCE(SUM(COALESCE(q_wq.quantity, 0)), 0)
                        FROM {$this->db->dbprefix('warehouses_products')} q_wq 
                        WHERE q_wq.product_id = {$product_id} 
                        GROUP BY q_wq.product_id
                        LIMIT 1
                    ) ";

        $this->db->select("
                COALESCE({$q_minus}, 0) AS quantity_minus,
                COALESCE({$q_plus}, 0) AS quantity_plus,
                COALESCE((COALESCE({$this->db->dbprefix('products')}.quantity, 0) - COALESCE({$q_pq}, 0)), 0) AS quantity_1,
                COALESCE((COALESCE({$this->db->dbprefix('products')}.quantity, 0) - COALESCE({$q_wq}, 0)), 0) AS quantity_2,
                COALESCE(({$this->db->dbprefix('warehouses_products')}.quantity - COALESCE(SUM(COALESCE({$this->db->dbprefix('purchase_items')}.quantity_balance, 0)), 0)), 0) AS quantity_3
            ");
        $this->db->join('warehouses_products', 'products.id = warehouses_products.product_id', 'left');
        $this->db->join('purchase_items', 'products.id = purchase_items.product_id', 'left');
        $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        $this->db->where('purchase_items.warehouse_id', $warehouse_id);
        $this->db->where('purchase_items.status', 'received');
        $this->db->where('products.id', $product_id);
        $this->db->group_by('products.id');

        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            if($q->row()->quantity_1 == 0 && $q->row()->quantity_2 == 0 && $q->row()->quantity_3 == 0 && (($q->row()->quantity_minus < 0 && $q->row()->quantity_plus == 0) || ($q->row()->quantity_minus == 0))) {
                $warehouses = $this->getAllWarehouses();
                foreach ($warehouses as $warehouse) {
                    if ($this->checkPurchaseItemsBalanceQty($product_id, $warehouse->id)) return true;
                }
                return false;
            }
            return true;
        }
        return false;
    }

    //////////////// Sync Quantity 13_05_21 ////////////////

    //--------------assets------------
    public function getevaluationByID($id)
    {
        $q = $this->db->get_where('asset_evaluation', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAssets()
    {
        $q = $this->db->get_where('products',array('type' =>'asset'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function deleteDepreciation($transaction,$transaction_id)
    {
        $this->db->delete('gl_trans', array('tran_type' => $transaction, 'depreciation_id' => $transaction_id));
    }

    public function deleteDpByexpense($transaction,$transaction_id)
    {
        $this->db->delete('gl_trans', array('tran_type' => $transaction, 'expense_id' => $transaction_id));
    }

    public function deleteAccPayroll($transaction,$transaction_id)
    {
        $this->db->delete('gl_trans', array('tran_type' => $transaction, 'payroll_id' => $transaction_id));
    }

    public function get_activity($code)
    {
        $q = $this->db->get_where('gl_charts', array('accountcode' => $code), 1);
        if($q->num_rows() > 0){
            $re = $q->row();
            return $re->type;
        }
        return false;
    }

    public function getPurchaseItemByTranId($combo_item)
    {
        $q = $this->db->get_where('purchase_items', array('transaction_id' => $combo_item));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getUserById($id){
        $q = $this->db->get_where('users', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getProductVariantByID($id, $uom = null) {
        if($uom) {
            $q = $this->db->get_where('product_variants', array('product_id' => $id, 'name' => $uom), 1);
        }else{
            $q = $this->db->get_where('product_variants', array('product_id' => $id), 1);
        }
        
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getWarehouseQty($product_id, $warehouse_id){
        $this->db->select("warehouses_products.*");
        $this->db->from("warehouses_products");
        $this->db->where(array("product_id" => $product_id,"warehouse_id" => $warehouse_id));
        $q = $this->db->get();
        if($q->num_rows()>0){
            return $q->row();
        }
        return false;
    }
    public function getWarehouse_VariantQty($product_id, $warehouse_id, $option_id = null){
        $this->db->select("warehouses_products_variants.*");
        $this->db->from("warehouses_products_variants");
        $this->db->where(array("product_id" => $product_id,"warehouse_id" => $warehouse_id));
        if ($option_id) {
            $this->db->where('option_id', $option_id);
        }
        $q = $this->db->get();
        if($q->num_rows()>0){
            return $q->row();
        }
        return false;
    }

    public function checkExpiryDate($product_id, $expiry, $warehouse_id)
    {
        $this->db->select('sum(quantity_balance) as expiry_qty')
                 ->from('purchase_items')
                 ->where(array('product_id' => $product_id, 'warehouse_id' => $warehouse_id));
        if($expiry != '' && $expiry != 0) {
            $this->db->where('expiry', $expiry);
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getUsingStockById($id)
    {
        $q = $this->db->get_where('enter_using_stock', array('id' => $id) );
        if($q->num_rows()>0){
            return $q->row();
        }
        return false;
    }

    public function getUsingStockByRef($ref)
    {
        $q = $this->db->get_where('enter_using_stock_items', array('reference_no' => $ref) );
        if($q->num_rows()>0){
            return $q->result();
        }
        return false;
    }

    public function getUsingStockItemsByUsingID($id)
    {
        $q = $this->db->get_where('enter_using_stock_items', array('using_stock_id' => $id) );
        if($q->num_rows()>0){
            return $q->result();
        }
        return false;
    }

    public function getPOI_By_PRID($id)
    {
        $this->db->select('purchases_order.id');
        $this->db->where('purchases_request.id', $id);
        $this->db->join('purchases_request', 'purchases_request.reference_no=purchases_order.purchase_ref', 'left');
        $q = $this->db->get('purchases_order');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $ids[] = $row->id;
            }

            $this->db->select('product_id, product_code, product_name, SUM(quantity) as quantity, expiry, pri_id');
            $this->db->from('purchase_order_items');
            $this->db->where_in('purchase_id', $ids);
            $this->db->where('pri_id !=', null)->where('pri_id !=', 0);
            $this->db->group_by('pri_id');
            $query =  $this->db->get();
            if($query->num_rows() > 0){
                foreach (($query->result()) as $row) {
                    $data[] =  $row;        
                }

                return $data;
            }
        }
        return false;
    }  

    public function getPRI_By_PRID($id)
    {
        $q = $this->db->get_where('purchase_request_items', ['purchase_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    function active_module($module){
        $q = $this->db->get_where('modules', array('name' => $module));
        if($q->num_rows()>0){
            return $q->row()->active;
        }
        return false;
    }
    public function getBillers() {
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where('id',$this->session->userdata('biller_id'));
        }
        $this->db->where('group_name','biller');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getRefSaleOrders($status = false)
    {
        $this->db->select('id,reference_no');
        
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('sales_order.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where('sales_order.biller_id',$this->session->userdata('biller_id'));
        }
        
        if($status){
            if($status=="approved"){
                $this->db->where('sales_order.order_status IN("approved","partial")');
            }else{
                $this->db->where('sales_order.order_status',$status);
            }
        }
        $this->db->order_by('id','desc');
        $q = $this->db->get('sales_order');
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getRefSales($delivery_status = false, $sale_status= false)
    {
        $user = $this->site->getUser($this->session->userdata('user_id'));
        if ($this->Settings->multi_biller) {
            $biller_ids = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
        } else {
            $biller_ids = $user->biller_id ? ((array) $user->biller_id) : null;
        }
        $warehouse_ids = explode(',', $this->session->userdata('warehouse_id'));
        $this->db->select('sales.*');
        if ($sale_status) {
            $this->db->where('sales.sale_status',$sale_status);
        }        
        if ((!$this->Owner && !$this->Admin) && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('sales.warehouse_id', $warehouse_ids);
        }
        if ((!$this->Owner && !$this->Admin) && !empty($biller_ids)) {
            $this->db->where_in('sales.biller_id', $biller_ids);
        }
        $this->db->where('sale_status !=', 'draft');
        $this->db->where('sale_status !=', 'returned');
        $this->db->where('sales.module_type', 'inventory');
        $this->db->where('sales.store_sale !=', 1);
        $this->db->where('sales.hide', 1);
        $this->db->order_by('id','desc');
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getRefSalesOrder($delivery_status = false, $sale_status = false)
    {
        $user = $this->site->getUser($this->session->userdata('user_id'));
        if ($this->Settings->multi_biller) {
            $biller_ids = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
        } else {
            $biller_ids = $user->biller_id ? ((array) $user->biller_id) : null;
        }
        $warehouse_ids = explode(',', $this->session->userdata('warehouse_id'));
        $this->db->select('id, reference_no');
        if($sale_status) {
            $this->db->where('sales_order.order_status',$sale_status);
        }        
        if ((!$this->Owner && !$this->Admin) && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('sales_order.warehouse_id', $warehouse_ids);
        }
        if ((!$this->Owner && !$this->Admin) && !empty($biller_ids)) {
            $this->db->where_in('sales_order.biller_id', $biller_ids);
        }
        $this->db->where('order_status !=', 'draft');
        $this->db->where('sales_order.store_sale !=', 1);
        $this->db->order_by('id','desc');
        $q = $this->db->get('sales_order');
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductUnit($product_id = false,$unit_id = false)
    {
        $this->db->select('product_units.unit_qty, units.code, units.name');
        $this->db->join('units','units.id=product_units.unit_id','left');
        $this->db->where('product_units.product_id', $product_id);
        $this->db->where('product_units.unit_id', $unit_id);
        $q = $this->db->get('product_units');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductBaseUnit($unit_id) 
    {
        $q = $this->db->get_where('units', ['id' => $unit_id], 1);
        if ($q->num_rows() > 0) {
            $unit     = $q->row();
            $unit_qty = $this->convertToBase($unit, 1);
            $product_unit = array(
                'code'     => $unit->code,
                'name'     => $unit->name,
                'unit_qty' => $unit_qty
            );
            return (object) $product_unit;
        }
        return false;
    }
    
    public function getProductUnitByProduct($product_id = false, $sort = "desc")
    {
        $this->db->select('product_units.unit_id,product_units.unit_qty,units.code,units.name');
        $this->db->join('units','units.id=product_units.unit_id','left');
        $this->db->where('product_units.product_id', $product_id);
        $this->db->order_by('product_units.unit_qty',$sort);
        $q = $this->db->get('product_units');
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAccount($accountType = array(), $selectAcc = '', $isCash='')
    {
        $selectedAccount = $selectAcc;
        $option = '';
        $this->db->select('gl_sections.sectionid,gl_sections.sectionname');
        if($accountType){
            $this->db->where_in('gl_sections.code', $accountType);
        }
        if($isCash!=''){
            $this->db->join('gl_charts','gl_sections.sectionid=gl_charts.sectionid', 'inner');
            $this->db->where('gl_charts.bank', 1);
            $this->db->where('gl_charts.inactive', 0);
        }
        $this->db->group_by('gl_sections.sectionid');
        $se = $this->db->get('gl_sections');
        if ($se->num_rows() > 0) {
            foreach (($se->result()) as $se_row) {
                if($se_row->sectionid!=''){
                    $option .='<optgroup label="'.$se_row->sectionname.'">';
                    $this->db->select('accountcode,accountname,lineage');
                    $this->db->where('gl_charts.inactive', 0);
                    $this->db->where('gl_charts.sectionid', $se_row->sectionid);
                    if($isCash!=''){
                        $this->db->where('gl_charts.bank', 1);
                    }
                    $this->db->where('gl_charts.parent_acc', 0);
                    $this->db->order_by('gl_charts.accountcode','asc');
                    $aa = $this->db->get('gl_charts');
                    if ($aa->num_rows() > 0) {
                        foreach($aa->result() as $aa_row){
                            $a = $this->db->get_where("gl_charts", array("parent_code"=>$aa_row->accountcode));
                            if($a->num_rows() > 0){
                                $option .='<option '.($aa_row->accountcode==$selectedAccount?'selected':'').' value="'.$aa_row->accountcode.'">'.$aa_row->accountcode.' - '.$aa_row->accountname.'</option>';
                                $option .= $this->accSub($aa_row->accountcode,$selectedAccount,$isCash);
                            }else{
                                $option .='<option '.($aa_row->accountcode==$selectedAccount?'selected':'').' value="'.$aa_row->accountcode.'">'.$aa_row->accountcode.' - '.$aa_row->accountname.'</option>';
                            }
                        }
                    }
                    $option .='</optgroup>';
                }
            }
        }
        return $option;
    }
    public function getWarehouses() {
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('warehouses.id',json_decode($this->session->userdata('warehouse_id')));
        }
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getUserByGroup($group_id = false)
    {
        $q = $this->db->get_where('users', ['group_id' => $group_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProjectByID($id)
    {
        $q = $this->db->get_where('projects', ['project_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getMultiApproved($group_id = false,$form=false)
    {
        if($group_id){
            $this->db->where('group_id',$group_id);
        }
        if($form){
            $this->db->where('form', $form);
        }
        $this->db->select('approved_by, preparation_by, 
            issued_by, acknowledged_by, 
            received_by, stock_received_by,
            quality_checked_by, procurement_by');
        // $this->db->join('approved', 'approved.');
        $q        = $this->db->get('approved_by',1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getAllMultiApproved($group_id = false)
    {
        $q        = $this->db->get_where('approved_by', ['group_id' => $group_id],1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function sendTelegram($transaction = false, $transaction_id = false, $type = false, $data = false){
        if ($this->Settings->enable_telegram && $transaction && $transaction_id && $type){ 
            $table_items = array("quotation","sale_order","sale","purchase","expense","delivery","transfer","take_leave");
            $message = "";
            if($type != "deleted"){
                $data = $this->getDataByTransaction($transaction,$transaction_id);
            }
            
            if($data){
                $message .= lang($transaction).' '.lang($type).":\n";
                if(isset($data->reference_no) && $data->reference_no){
                    $message .= lang("reference").": ".$data->reference_no."\n";
                }
                if(isset($data->do_reference_no) && $data->do_reference_no){
                    $message .= lang("dn_reference").": ".$data->do_reference_no."\n";
                }
                if(isset($data->sale_reference_no) && $data->sale_reference_no){
                    $message .= lang("si_reference").": ".$data->sale_reference_no."\n";
                }
                if(isset($data->so_reference_no) && $data->so_reference_no){
                    $message .= lang("so_reference").": ".$data->so_reference_no."\n";
                }
                if(isset($data->date) && $data->date){
                    $message .= lang("date").": ".$this->bpas->hrld($data->date)."\n";
                }
                if(isset($data->customer) && $data->customer){
                    $message .= lang("customer").": ".$data->customer."\n";
                }
                if(isset($data->supplier) && $data->supplier){
                    $message .= lang("supplier").": ".$data->supplier."\n";
                }
                if(isset($data->phone) && $data->phone){
                    $message .= lang("phone").": ".$data->phone."\n";
                }
                if(isset($data->dn_address) && $data->dn_address){
                    $delivery_address = explode('Tel:',(strip_tags($data->dn_address)));
                    $delivery_phone_emial = explode('Email:',$delivery_address[1]);
                    $address = $delivery_address[0];
                    $phone = $delivery_phone_emial[0];
                    if($address){
                        $message .= lang("address").": ".$address."\n";
                    }
                    if($phone){
                        $message .= lang("phone").": ".$phone."\n";
                    }
                }
                if(isset($data->warehouse) && $data->warehouse){
                    $message .= lang("warehouse").": ".$data->warehouse."\n";
                }
                if(isset($data->from_warehouse_name) && $data->from_warehouse_name){
                    $message .= lang("from_warehouse").": ".$data->from_warehouse_name."\n";
                }
                if(isset($data->to_warehouse_name) && $data->to_warehouse_name){
                    $message .= lang("to_warehouse").": ".$data->to_warehouse_name."\n";
                }
                if(isset($data->amount) && $data->amount){
                    $message .= lang("amount").": ".$this->bpas->formatMoney($data->amount)."\n";
                }
                if(isset($data->discount) && $data->discount){
                    $message .= lang("discount").": ".$this->bpas->formatMoney($data->discount)."\n";
                }
                if(isset($data->order_discount_id) && $data->order_discount_id){
                    if(strpos($data->order_discount_id, "%")){
                        $message .= lang("discount").": ".$data->order_discount_id."\n";
                    }else{
                        $message .= lang("discount").": ".$this->bpas->formatMoney($data->order_discount_id)."\n";
                    }
                }
                if(isset($data->grand_total) && $data->grand_total){
                    $message .= lang("grand_total").": ".$this->bpas->formatMoney($data->grand_total)."\n";
                }
                if(isset($data->paid_by) && $data->paid_by){
                    $message .= lang("paid_by").": ".$data->paid_by."\n";
                }
                if(isset($data->driver) && $data->driver){
                    $message .= lang("driver").": ".$data->driver."\n";
                }
                
                if($transaction=="notification"){
                    if(isset($data->from_date) && $data->from_date){
                        $message .= lang("from_date").": ".$this->bpas->hrsd($data->from_date).' ';
                    }
                    if(isset($data->till_date) && $data->till_date){
                        $message .= lang("to_date").": ".$this->bpas->hrsd($data->till_date)."\n";
                    }
                    if(isset($data->comment) && $data->comment){
                        $message .= lang("note").": ".$this->bpas->remove_tag($this->bpas->decode_html($data->comment))."\n";
                    }
                }

                if($transaction=="trucking"){
                    if(isset($data->truck_no) && $data->truck_no){
                        $message .= lang("truck_no").": ".$data->truck_no."\n";
                    }
                    if(isset($data->driver_name) && $data->driver_name){
                        $message .= lang("driver").": ".$data->driver_name."\n";
                    }
                    if(isset($data->product_name) && $data->product_name){
                        $message .= lang("product_name").": ".$data->product_name."\n";
                    }
                    if(isset($data->container_no) && $data->container_no){
                        $message .= lang("container_no").": ".$data->container_no."\n";
                    }
                    if(isset($data->container_size) && $data->container_size){
                        $message .= lang("container_size").": ".$data->container_size."\n";
                    }
                    if(isset($data->factory_name) && $data->factory_name){
                        $message .= lang("factory").": ".$data->factory_name."\n";
                    }
                    if(isset($data->dry_port_name) && $data->dry_port_name){
                        $message .= lang("dry_port").": ".$data->dry_port_name."\n";
                    }
                    if(isset($data->fee) && $data->fee > 0){
                        $message .= lang("fee").": ".$this->bpas->formatMoney($data->fee)."\n";
                    }
                    if(isset($data->extra) && $data->extra > 0){
                        $message .= lang("extra").": ".$this->bpas->formatMoney($data->extra)."\n";
                    }
                    if(isset($data->stand_by) && $data->stand_by > 0){
                        $message .= lang("stand_by").": ".$this->bpas->formatMoney($data->stand_by)."\n";
                    }
                    if(isset($data->lolo) && $data->lolo > 0){
                        $message .= lang("lolo").": ".$this->bpas->formatMoney($data->lolo)."\n";
                    }
                    if(isset($data->commission) && $data->commission > 0){
                        $message .= lang("commission").": ".$this->bpas->formatMoney($data->commission)."\n";
                    }
                    if(isset($data->fuel) && $data->fuel > 0){
                        $message .= lang("fuel").": ".$data->fuel."\n";
                    }
                    
                }
                
                if($type != "deleted" && in_array($transaction, $table_items)){
                    $products = $this->getItemsByTransaction($transaction,$transaction_id);
                    if($products){
                        $i = 1;
                        $message .= lang("description").":\n";
                        foreach($products as $product){
                            if($transaction=="take_leave"){
                                $message .= "_____________________________\n";
                                if(isset($product->employee_name) && $product->employee_name){
                                $message .= lang("full_name").": ".$product->employee_name."\n";
                                }
                                if(isset($product->department) && $product->department){
                                    $message .= lang("department").": ".$product->department."\n";
                                }
                                if(isset($product->position) && $product->position){
                                    $message .= lang("position").": ".$product->position."\n";
                                }
                                if(isset($product->reason) && $product->reason){
                                    $message .= lang("reason").": ".$product->reason."\n";
                                }
                                if(isset($product->leave_type) && $product->leave_type){
                                    $message .= lang("leave_type").": ".$product->leave_type."\n";
                                }
                                if(isset($product->start_date) && $product->start_date){
                                    $message .= lang("start_date").": ".$this->bpas->hrsd($product->start_date)."\n";
                                }
                                if(isset($product->end_date) && $product->end_date){
                                    $message .= lang("end_date").": ".$this->bpas->hrsd($product->end_date);
                                }
                            }else{
                                if(isset($product->foc) && $product->foc > 0){
                                    $product->quantity = $product->quantity."+".$product->foc;
                                }
                                $message.= $i++.".";
                                if(isset($product->name) && $product->name){
                                    $message.= " [<i><b>".$product->name."</b></i>]";
                                }
                                if(isset($product->expiry) && $product->expiry && $product->expiry != "0000-00-00"){
                                    $message.= " [".$this->bpas->hrsd($product->expiry)."]";
                                }
                                if(isset($product->quantity) && $product->quantity && isset($product->price) && $product->price){
                                    $message.= " [".$product->quantity."x".$this->bpas->formatMoney($product->price)."]";
                                }else{
                                    if(isset($product->price) && $product->price){
                                        $message.= " [".$this->bpas->formatMoney($product->price)."]";
                                    }
                                    if(isset($product->quantity) && $product->quantity){
                                        $message.= " [<i><b>".$product->quantity."</b></i>]";
                                    }
                                }
                                if($this->Settings->show_unit && isset($product->product_unit_code) && $product->product_unit_code){
                                    $message.= " [".$product->product_unit_code."]";
                                }
                                
                            }
                            $message.="\n";
                        }
                    }
                }
                if(isset($data->note) && $data->note){
                    $message .= lang("note").": ".$this->bpas->remove_tag($this->bpas->decode_html($data->note))."\n";
                }
                //$message .= lang("more_information").": ".$this->session->userdata('username');
                $message .= "\n\n-------------".lang("auto_message")."-------------\n".
                    lang("created_by")." : ".$this->session->userdata('username')."\n".
                    lang("power_by").":  SBC Solution CO., LTD\n".lang("tel").": 016 78 78 75";
                $this->load->library('telegrambot');
                $telegram_bots = $this->getTelegramBots();
                if($telegram_bots){
                    foreach($telegram_bots as $telegram_bot){
                        $al_tran= $telegram_bot->transaction ? json_decode($telegram_bot->transaction) : false;
                        $al_bill = $telegram_bot->biller ? json_decode($telegram_bot->biller) : false;
                        $al_ware = $telegram_bot->warehouse ? json_decode($telegram_bot->warehouse) : false;
                        if((!$data->warehouse_id || !$al_ware || in_array($data->warehouse_id, $al_ware) || (isset($data->to_warehouse_id) && in_array($data->to_warehouse_id, $al_ware))) && (!$data->biller_id || !$al_bill || in_array($data->biller_id, $al_bill)) && (!$al_tran || in_array($transaction, $al_tran)) && $telegram_bot->token_id && $telegram_bot->chat_id){
                            $this->telegrambot->sendmsg($telegram_bot->token_id,$telegram_bot->chat_id,$message);
                        }
                    }
                }
            }
            
        }
    }
    public function getDataByTransaction($transaction = false, $transaction_id = false){
        $q = false;
        if($transaction=="notification"){
            $this->db->select("notifications.*,
                            ");
            //$this->db->join("companies","companies.id = quotes.customer_id","left");
            $q = $this->db->get_where("notifications", array('notifications.id' => $transaction_id), 1);
        }else if($transaction=="quotation"){
            $this->db->select("quotes.reference_no,
                                quotes.date,
                                quotes.customer,
                                quotes.grand_total,
                                quotes.biller_id,
                                companies.phone,
                                quotes.warehouse_id,
                                quotes.note,
                                quotes.order_discount_id,
                            ");
            $this->db->join("companies","companies.id = quotes.customer_id","left");
            $q = $this->db->get_where("quotes", array('quotes.id' => $transaction_id), 1);
        }else if($transaction=="sale_order"){
            $this->db->select("sale_orders.reference_no,
                                sale_orders.date,
                                sale_orders.customer,
                                sale_orders.grand_total,
                                sale_orders.biller_id,
                                companies.phone,
                                sale_orders.warehouse_id,
                                sale_orders.note,
                                sale_orders.order_discount_id,
                            ");
            $this->db->join("companies","companies.id = sale_orders.customer_id","left");
            $q = $this->db->get_where("sale_orders", array('sale_orders.id' => $transaction_id), 1);
        }else if($transaction=="sale"){
            $this->db->select("sales.reference_no,
                                sales.date,
                                sales.customer,
                                sales.grand_total,
                                sales.biller_id,
                                companies.phone,
                                sales.warehouse_id,
                                sales.note,
                                sales.order_discount_id,
                            ");
            $this->db->join("companies","companies.id = sales.customer_id","left");
            $q = $this->db->get_where("sales", array('sales.id' => $transaction_id), 1);
        }else if($transaction=="payment"){
            $this->db->select("payments.reference_no,
                                payments.date,
                                sales.customer,
                                payments.amount, 
                                payments.discount,
                                IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by,
                                sales.biller_id,
                                companies.phone,
                                sales.warehouse_id,
                                payments.note,
                            ");
            $this->db->join("sales","sales.id = payments.sale_id","left");
            $this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
            $this->db->join("companies","companies.id = sales.customer_id","left");
            $q = $this->db->get_where("payments", array('payments.id' => $transaction_id), 1);
        }else if($transaction=="purchase"){
            $this->db->select("purchases.reference_no,
                                purchases.date,
                                purchases.supplier,
                                purchases.grand_total,
                                purchases.biller_id,
                                companies.phone,
                                purchases.warehouse_id,
                                purchases.note,
                                purchases.order_discount_id,
                            ");
            $this->db->join("companies","companies.id = purchases.supplier_id","left");
            $q = $this->db->get_where("purchases", array('purchases.id' => $transaction_id), 1);
        }else if($transaction=="expense"){
            $this->db->select("expenses.reference as reference_no,
                                expenses.date,
                                expenses.supplier,
                                expenses.grand_total,
                                expenses.biller_id,
                                companies.phone,
                                expenses.warehouse_id,
                                expenses.note,
                                expenses.order_discount_id,
                            ");
            $this->db->join("companies","companies.id = expenses.supplier_id","left");
            $q = $this->db->get_where("expenses", array('expenses.id' => $transaction_id), 1);
        }else if($transaction=="delivery"){
            $this->db->select("deliveries.do_reference_no,
                                deliveries.sale_reference_no,
                                deliveries.so_reference_no,
                                deliveries.date,
                                deliveries.customer,
                                deliveries.biller_id,
                                deliveries.address as dn_address,
                                deliveries.warehouse_id,
                                warehouses.name as warehouse,
                                CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as driver
                            ");
            $this->db->join("users","users.id = deliveries.delivered_by","left");
            $this->db->join("warehouses","warehouses.id = deliveries.warehouse_id","left");
            $q = $this->db->get_where("deliveries", array('deliveries.id' => $transaction_id), 1);
        }else if($transaction=="transfer"){
            $this->db->select("transfers.transfer_no as reference_no,
                                transfers.date,
                                transfers.from_warehouse_id as warehouse_id,
                                transfers.from_warehouse_name,
                                transfers.to_warehouse_id,
                                transfers.to_warehouse_name,
                                transfers.note,
                            ");
            $q = $this->db->get_where("transfers", array('transfers.id' => $transaction_id), 1);
        }else if($transaction=="take_leave"){
            $this->db->select("att_take_leaves.reference_no,
                                att_take_leaves.date,
                                att_take_leaves.note,
                            ");
            $q = $this->db->get_where("att_take_leaves", array('att_take_leaves.id' => $transaction_id), 1);
        }else if($transaction=="trucking"){
            $this->db->select("tru_truckings.reference_no,
                                tru_truckings.date,
                                tru_truckings.container_no,
                                tru_truckings.fee,
                                tru_truckings.lolo,
                                tru_truckings.extra,
                                tru_truckings.stand_by,
                                tru_truckings.booking,
                                tru_truckings.commission,
                                tru_truckings.other,
                                tru_truckings.fuel,
                                tru_truckings.note,
                                companies.company as customer,
                                companies.phone as phone,
                                tru_containers.name as container_size,
                                tru_factories.name as factory_name,
                                tru_dry_ports.name as dry_port_name,
                                products.name as product_name,
                                tru_drivers.full_name as driver_name,
                                tru_trucks.plate as truck_no
                            ");
            $this->db->join("products","products.id = tru_truckings.service_id","left");
            $this->db->join("companies","companies.id = tru_truckings.customer_id","left");
            $this->db->join("tru_containers","tru_containers.id = tru_truckings.container_id","left");
            $this->db->join("tru_factories","tru_factories.id = tru_truckings.factory_id","left");
            $this->db->join("tru_dry_ports","tru_dry_ports.id = tru_truckings.dry_port_id","left");
            $this->db->join("tru_trucks","tru_trucks.id = tru_truckings.truck_id","left");
            $this->db->join("tru_drivers","tru_drivers.id = tru_truckings.driver_id","left");
            $q = $this->db->get_where('tru_truckings',array('tru_truckings.id'=>$transaction_id));
        }else if($transaction=="cash_advance"){
            $this->db->select("
                                tru_cash_advances.reference_no,
                                tru_cash_advances.date,
                                tru_cash_advances.amount,
                                tru_cash_advances.service,
                                tru_cash_advances.note,
                                IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('tru_cash_advances').".paid_by) as paid_by,
                                tru_cash_advances.driver_name,
                            ");
            $this->db->join("cash_accounts","cash_accounts.id = tru_cash_advances.paid_by","left");
            $q = $this->db->get_where('tru_cash_advances',array('tru_cash_advances.id'=>$transaction_id));
        }else if($transaction=="pos_register"){
            $this->db->select("pos_register.date as opened_at,
                                pos_register.closed_at,
                                pos_register.cash_in_hand,
                                pos_register.total_cash,
                                pos_register.total_cash_submitted,
                                pos_register.user_id,
                                CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as user
                            ");
            $this->db->join("users","users.id = pos_register.user_id","inner");
            $q = $this->db->get_where('pos_register',array('pos_register.id'=>$transaction_id));
        }
        if ($q && $q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getItemsByTransaction($transaction = false, $transaction_id = false){
        $q = false;
        if($transaction=="quotation"){
            $this->db->select("product_name as name,unit_quantity as quantity,unit_price as price,product_unit_code");
            $q = $this->db->get_where("quote_items", array('quote_id' => $transaction_id));
        }else if($transaction=="sale_order"){
            $this->db->select("product_name as name,unit_quantity as quantity,unit_price as price,foc,product_unit_code");
            $q = $this->db->get_where("sale_order_items", array('sale_order_id' => $transaction_id));
        }else if($transaction=="sale"){
            $this->db->select("product_name as name,unit_quantity as quantity,unit_price as price,foc,expiry,product_unit_code");
            $q = $this->db->get_where("sale_items", array('sale_id' => $transaction_id));
        }else if($transaction=="purchase"){
            $this->db->select("product_name as name,unit_quantity as quantity,unit_cost as price,product_unit_code");
            $q = $this->db->get_where("purchase_items", array('purchase_id' => $transaction_id));
        }else if($transaction=="expense"){
            $this->db->select("description as name, quantity, unit_cost as price");
            $q = $this->db->get_where("expense_items", array('expense_id' => $transaction_id));
        }else if($transaction=="delivery"){
            $this->db->select("product_name as name,(unit_quantity - foc_qty) as quantity, foc_qty as foc, expiry,product_unit_code");
            $q = $this->db->get_where("delivery_items", array('delivery_id' => $transaction_id));
        }else if($transaction=="transfer"){
            $this->db->select("product_name as name,unit_quantity as quantity, expiry,product_unit_code");
            $q = $this->db->get_where("transfer_items", array('transfer_id' => $transaction_id));
        }else if($transaction=="take_leave"){
            $this->db->select("CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as employee_name,
                                att_take_leave_details.start_date,
                                att_take_leave_details.end_date,
                                hr_departments.name as department,
                                hr_positions.name as position,
                                hr_leave_types.name as leave_type,
                                att_take_leave_details.reason,
                            ")
                        ->join("hr_employees","att_take_leave_details.employee_id = hr_employees.id","inner")
                        ->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","left")
                        ->join("hr_departments","hr_employees_working_info.department_id = hr_departments.id","left")
                        ->join("hr_leave_types","hr_leave_types.id = att_take_leave_details.leave_type","left")
                        ->join("hr_positions","hr_employees_working_info.position_id = hr_positions.id","left");
            $q = $this->db->get_where("att_take_leave_details", array('att_take_leave_details.take_leave_id' => $transaction_id));
        }
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getTelegramBots(){
        $this->db->where("telegram_bots.status !=","inactive");
        $q = $this->db->get("telegram_bots");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllProjectByBillerID($id = false)
    {
        $q = $this->db->get_where('projects', array('biller_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getCategoryByProject(){
        $data = false;
        if (!$this->Owner && !$this->Admin){
            $where_biller = "";
            $where_project = "";
            // if($this->session->userdata('biller_id')){
            //     $where_biller = "(".$this->db->dbprefix('category_projects').".biller_id IN (".$this->session->userdata('biller_id').") OR IFNULL(".$this->db->dbprefix('categories').".biller,'')='' OR ".$this->db->dbprefix('categories').".biller = 'null')";
            // }
            if($this->Settings->project == 1 && $this->session->userdata('project_ids') && $this->session->userdata('project_ids') != "null"){
                $project_ids = json_decode($this->session->userdata("project_ids"));
                if($project_ids[0] != "all"){
                    $pro_ids = "";
                    $i = 1;
                    foreach($project_ids as $project_id){
                        if($i==1){
                            $pro_ids .= "'".$project_id."'";
                            $i = 2;
                        }else{
                            $pro_ids .= ", '".$project_id."'";
                        }
                    }
                    $where_project = "(".$this->db->dbprefix('category_projects').".project_id IN (".$pro_ids.") OR IFNULL(".$this->db->dbprefix('categories').".project,'')='' OR ".$this->db->dbprefix('categories').".project = 'null')";
                }
            }
            if($where_biller != "" || $where_project != "" ){
                if($where_biller !=""){
                    $this->db->where($where_biller);
                }
                if($where_project !=""){
                    $this->db->where($where_project);
                }
                $this->db->select("categories.id")
                            ->join("category_projects","category_projects.category_id = categories.id","LEFT")
                            ->group_by("categories.id");
                $q = $this->db->get("categories");          
                if($q->num_rows() > 0){
                    foreach (($q->result()) as $row) {
                        $data[] = $row->id;
                    }
                }
            }
        }
        
        if($this->config->item("user_by_category") && !$this->Owner && !$this->Admin){
            $categories = false;
            if($this->bpas->GP['categories'] != "null" && $this->bpas->GP['categories']){
                $categories = json_decode($this->bpas->GP['categories']);
            }
            if($data && $categories){
                $data=array_intersect($data,$categories);
            }else if($categories){
                $data = $categories;
            }
        }
        return $data;
        
    }
    public function getUnitbyProduct($pid = false, $baseunit = false)
    {
        $q = $this->db->query("SELECT
                                    bpas_units.id,
                                    bpas_units.code,
                                    bpas_units.name,
                                    bpas_units.base_unit,
                                    bpas_units.operator,
                                    bpas_units.unit_value,
                                    bpas_product_units.unit_qty AS operation_value,
                                    bpas_product_units.unit_price
                                FROM
                                    bpas_units
                                INNER JOIN bpas_product_units ON bpas_product_units.unit_id = bpas_units.id
                                AND bpas_product_units.product_id = '".$pid."'
                                WHERE
                                    base_unit = '".$baseunit."'
                                OR bpas_units.id = '".$baseunit."'
                    ");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getUnitsDetailsByProduct($product_id, $unit_id = false, $sort = 'DESC')
    {
        if ($unit_id) {
            $unit = $this->getUnitByID($unit_id);
        }
        $this->db->select("
                {$this->db->dbprefix('units')}.id,
                {$this->db->dbprefix('units')}.code,
                {$this->db->dbprefix('units')}.name,
                {$this->db->dbprefix('units')}.base_unit,
                {$this->db->dbprefix('units')}.operator,
                {$this->db->dbprefix('units')}.unit_value,
                {$this->db->dbprefix('units')}.operation_value,
                CAST({$this->db->dbprefix('units')}.operation_value as SIGNED) AS casted_column,
                COALESCE({$this->db->dbprefix('units')}.operation_value, 1) AS unit_qty,
                COALESCE({$this->db->dbprefix('products')}.price, COALESCE({$this->db->dbprefix('cost_price_by_units.price')}, 0)) AS unit_price
            ");
        $this->db->from('units');
        $this->db->join("products", "products.id = {$product_id}", "inner");
        $this->db->join("cost_price_by_units", "cost_price_by_units.unit_id = units.id AND cost_price_by_units.product_id = {$product_id}", "left");
        $this->db->where("units.id = products.unit OR units.base_unit = products.unit");
        if (isset($unit) && !empty($unit)) {
            if (!empty($unit->operation_value)) {
                $this->db->where("units.operation_value <= {$unit->operation_value} OR (units.id = products.unit AND units.operation_value IS NULL)");
            } else {
                $this->db->where("units.id = products.unit AND units.operation_value IS NULL");
            }
        }
        $this->db->order_by('casted_column', $sort);
        $this->db->order_by('units.operation_value', $sort);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductCostPriceByUnit($id, $unit) 
    {
        $q = $this->db->get_where('cost_price_by_units', ['product_id' => $id, 'unit_id' => $unit], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getMaintenanceByID($id)
    {
        $q = $this->db->get_where('maintenance', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getGroupPermission($group_name = false)
    {
        $q = $this->db->get_where('groups', ['name' => $group_name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getApprovedByuser($group,$id,$key,$user_id)
    {
        if($group == 'pr'){
            $this->db->where(array('purchase_request_id'=> $id,
                ''.$key.'_by'=> $user_id,
                ''.$key.'_status'=> 'approved',
            ));
        }else if($group == 'so'){
            $this->db->where(array('sale_order_id'=> $id,
                ''.$key.'_by'=> $user_id,
                ''.$key.'_status'=> 'approved',
            ));
        }else if($group == 'po'){
            $this->db->where(array('purchase_order_id'=> $id,
                ''.$key.'_by'=> $user_id,
                ''.$key.'_status'=> 'approved',
            ));
        }else{
            return false;
        }
        $this->db->select('*');
        $q = $this->db->get('approved', 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    
    public function getBank_reconcileByCode($code =null)
    {
        $this->db->select('*, max(end_date) as last_recincile');
        if($code){
            $this->db->where('account_code', $code);
        }
        $q = $this->db->get('bank_reconsile');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    // public function getAlertCustomerpayments()
    // {
    //     $remind = ($this->Settings->installment_alert_days ? $this->Settings->installment_alert_days:0);
    //     $q = $this->db->select('COUNT('.$this->db->dbprefix("sales").'.id) as alert_num')
    //                    ->where('DATE_SUB('.$this->db->dbprefix("sales").'.`due_date`, INTERVAL '.$remind.' DAY) <=', date("Y-m-d"))
    //                    ->where('sales.payment_status !=','paid')
    //                    ->where('sales.payment_status !=','return')
    //                    ->get('sales');
    //     if ($q->num_rows() > 0) {
    //         $res = $q->row();
    //         return (INT) $res->alert_num;
    //     }
    //     return FALSE;
    // }
    // public function getAlertInstallmentMissedRepayments()
    // {
    //     $remind = ($this->Settings->installment_alert_days?$this->Settings->installment_alert_days:0);
    //     $q = $this->db->select('COUNT(bpas_installment_items.id) as alert_num')
    //                    ->join('installments', 'installment_items.installment_id=installments.id', 'left')
    //                    ->where('DATE_SUB(bpas_installment_items.`deadline`, INTERVAL '.$remind.' DAY) <=', date("Y-m-d"))
    //                    ->where('installment_items.status !=','paid')
    //                    ->where('installment_items.status !=','payoff')
    //                    ->where('installments.status !=','payoff')
    //                    ->where('installments.status !=','completed')
    //                    ->where('installments.status !=','inactive')
    //                    ->where('installments.status !=','voiced')
    //                    ->get('installment_items');
    //     if ($q->num_rows() > 0) {
    //         $res = $q->row();
    //         return (INT) $res->alert_num;
    //     }
    //     return FALSE;
    // }

    public function getPurchaseByProductId($product_id){
        $this->db->select('purchase_items.*,purchases.grand_total as cost, products.price as price, purchases.total as total_cost')
            ->where('product_id', $product_id)
            ->join('purchases', 'purchases.id=purchase_items.purchase_id')
            ->join('products', 'products.id=purchase_items.product_id');
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllSuspended_note() {
        $q = $this->db->get('suspended_note');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTicketsbyTimeout($array_field = array(), $timeout = null) 
    {   
        $sql_query = "(
            SELECT 
                {$this->db->dbprefix('suspended_note')}.*,
                {$this->db->dbprefix('suspended_note')}.name AS table_bus,
                {$this->db->dbprefix('custom_field')}.id AS timeout_id,
                {$this->db->dbprefix('custom_field')}.name AS timeout,
                IF({$this->db->dbprefix('reservation')}.id != 'NULL', 'not available', 'available') AS status
                FROM {$this->db->dbprefix('suspended_note')}
                LEFT JOIN {$this->db->dbprefix('custom_field')} ON {$this->db->dbprefix('custom_field')}.id = '{$timeout}'
                LEFT JOIN {$this->db->dbprefix('reservation')} ON 
                {$this->db->dbprefix('reservation')}.note_id = {$this->db->dbprefix('suspended_note')}.note_id AND 
                DATE_FORMAT({$this->db->dbprefix('reservation')}.checkIn, '%Y-%m-%d') = '" . $this->bpas->fld($array_field['date']) . "' AND
                {$this->db->dbprefix('reservation')}.timeout = '{$timeout}' AND 
                {$this->db->dbprefix('reservation')}.from = '{$array_field['from']}' AND
                {$this->db->dbprefix('reservation')}.destination = '{$array_field['destination']}'
        )";
        $q = $this->db->query($sql_query);
        if ($q->num_rows() > 0) {
            foreach(($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllTickets($array_field = array())
    {
        $tickets = [];
        if($array_field['time_out']) {
            $get_fields_byId = $this->site->getcustomfieldById($array_field['time_out']);
            $tickets[$get_fields_byId->name] = $this->getTicketsbyTimeout($array_field, $array_field['time_out']);
        } else {
            $get_fields = $this->site->getcustomfield('time_out');
            if (!empty($get_fields)) {
                foreach($get_fields as $time) {
                    $tickets[$time->name] = $this->getTicketsbyTimeout($array_field, $time->id);
                }
            }
        }
        return $tickets;
    }


    public function getAllSuspended_note_Room() {
        $this->db->select('suspended_note.*');
        $this->db->from('suspended_note');
        $this->db->where('suspended_note.type','room');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }


    public function getSuspended_noteByID($id) {
        $q = $this->db->get_where('suspended_note', array('note_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
        
    public function addPrint($data = false)
    {
        if($this->db->insert('print_histories',$data)){
            return true;
        }
        return false;
    }
    
    public function checkPrint($transaction = false,$transaction_id = false)
    {
        $q = $this->db->get_where('print_histories',array('transaction'=>$transaction,'transaction_id'=>$transaction_id));
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
    public function Assgin_Print($transaction = false,$transaction_id = false)
    {
        if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']){
            $print =0;
        }else{
            if($this->Settings->limit_print=='1' && $this->site->checkPrint($transaction,$transaction_id)){
                $print = 1;
            }else if($this->Settings->limit_print=='2' && $this->site->checkPrint($transaction,$transaction_id)){
                $print = 2;
            }else{
                $print = 0;
            }
        }
        return $print;
    }
    public function RePrint($print_type){
        if($print_type==2){
            if($this->Settings->watermark){
                echo '<p class="bg-text" style="transform:rotate(600deg) !important">';
                    for($b=0; $b < 7; $b++){
                        echo lang('re-print').'<br>';
                    }
                echo '</p>';    
            }else{
                echo '<p class="bg-text">';
                    for($b=0; $b < 7; $b++){
                        echo lang('re-print').'<br>';
                    }
                echo '</p>';
            }
        }
    }
    
    public function checkExpiry($stockmoves = false, $items = false, $type = false, $transaction = false, $transaction_id = false)
    {
        if ($stockmoves) {
            $expiry_items = false;
            if (!empty($items)) :
            foreach ($items as $item) {
                $product_detail = $this->getProductByID($item['product_id']);
                $unit   = $this->getUnitByID($product_detail->unit);
                $s_unit = $this->getProductUnit($product_detail->id, $item['product_unit_id']);
                if ($type == 'Sale' || $type == 'Delivery' || $type == 'POS') {
                    $price         = 0;
                    $item_discount = 0;
                    $discount      = 0;
                    if ($item['subtotal'] != 0 && $item['subtotal'] != '') {
                        $price = $item['subtotal'] / $item['unit_quantity'];
                        if($item['item_discount'] > 0){
                            $item_discount = $item['item_discount'] / $item['unit_quantity'];
                        }
                    }
                    if ($s_unit->unit_qty > 1) {
                        $price = $price / $s_unit->unit_qty;
                    }
                    if ($item_discount > 0) {
                        if (strpos($a, '%') !== false) {
                            $discount = $item_discount;
                        } else {
                            $discount = $item['discount'];
                        }
                    }
                    $sale_foc = 0;
                    $delivery_foc = 0;
                    if (($type == 'Sale' || $type == 'POS') && $item['foc'] > 0) {
                        $sale_foc = $item['foc'];
                    } else if ($type=='Delivery') {
                        $delivery_foc = (isset($item['foc_qty']) ? $item['foc_qty'] : null);
                    }
                }
                if ($item['expiry'] != '') {
                    $expiry_quantity = abs($item['quantity']) + $sale_foc;
                    $product_expiry = $this->getProductExpiredByProductID($item['product_id'], $item['warehouse_id'], $transaction, $transaction_id, $item['expiry']);
                    if ($product_expiry) {
                        foreach ($product_expiry as $row_expiry) {
                            $row_expiry->expiry = $this->bpas->fsd($row_expiry->expiry);
                            if ($expiry_items) {
                                foreach ($expiry_items as $expiry_item) {
                                    if ($expiry_item['product_id'] == $item['product_id'] && $expiry_item['expiry'] == $row_expiry->expiry) {
                                        $row_expiry->quantity -= abs($expiry_item['quantity']);
                                    }
                                }
                            }
                            if ($row_expiry->quantity < (abs($item['quantity']) + $sale_foc)) {
                                $item['quantity'] = $row_expiry->quantity;
                                if ($type=='Sale' || $type=='Delivery' || $type=='POS') { 
                                    $item['unit_quantity'] = $row_expiry->quantity; 
                                    $item['product_unit_id'] = $product_detail->unit;
                                    $item['product_unit_code'] = $unit->code;
                                    $item['subtotal'] = $row_expiry->quantity * $price;
                                    $item['discount'] = $discount;
                                    $item['item_discount'] = $row_expiry->quantity * $item_discount;
                                    if ($type=='Delivery') {
                                        $item['foc_qty'] = 0;
                                    }
                                } else {
                                    $item['unit_quantity'] = $row_expiry->quantity;
                                    $item['product_unit_id'] = $product_detail->unit;
                                    $item['product_unit_code'] = $unit->code;
                                }
                                $expiry_items[] = $item;
                                if ($type=='Delivery') {
                                    $item['foc_qty'] = $delivery_foc;
                                } else if ($type=='Sale' || $type=='POS') {
                                    $item['foc'] = 0;
                                }
                                $expiry_quantity = $expiry_quantity - $row_expiry->quantity;
                                $product_expiries = $this->getProductExpiredByProductID($item['product_id'], $item['warehouse_id'], $transaction, $transaction_id);
                                if ($product_expiries) {
                                    $con = 1;
                                    foreach ($product_expiries as $product_expirie) {
                                        $expiry_date = $this->bpas->fsd($product_expirie->expiry);
                                        if ($expiry_items) {
                                            foreach ($expiry_items as $expiry_item) {
                                                if ($expiry_item['product_id'] == $item['product_id'] && $expiry_item['expiry'] == $expiry_date) {
                                                    $product_expirie->quantity -= abs($expiry_item['quantity']);
                                                }
                                            }
                                        }
                                        if ($con==1 && $expiry_date != $row_expiry->expiry && $expiry_date >= date('Y-m-d') && $product_expirie->quantity > 0) {    
                                            if ($product_expirie->quantity >= $expiry_quantity) {
                                                $item['quantity'] = $expiry_quantity;
                                                $item['expiry'] = $expiry_date;
                                                if ($type == 'Sale' || $type == 'Delivery' || $type == 'POS') {
                                                    $item['unit_quantity'] = $expiry_quantity;
                                                    $item['product_unit_id'] = $product_detail->unit;
                                                    $item['product_unit_code'] = $unit->code;
                                                    $item['subtotal'] = $expiry_quantity * $price;
                                                    $item['discount'] = $discount;
                                                    $item['item_discount'] = $expiry_quantity * $item_discount;
                                                } else {
                                                    $item['unit_quantity'] = $expiry_quantity;
                                                    $item['product_unit_id'] = $product_detail->unit;
                                                    $item['product_unit_code'] = $unit->code;
                                                }
                                                $con = 0;
                                            } else {
                                                $expiry_quantity = $expiry_quantity - $product_expirie->quantity;
                                                $item['quantity'] = $product_expirie->quantity;
                                                $item['expiry'] = $expiry_date;
                                                if ($type == 'Sale' || $type == 'Delivery' || $type == 'POS') {
                                                    $item['unit_quantity'] = $product_expirie->quantity;
                                                    $item['product_unit_id'] = $product_detail->unit;
                                                    $item['product_unit_code'] = $unit->code;
                                                    $item['subtotal'] = $product_expirie->quantity * $price;
                                                    $item['discount'] = $discount;
                                                    $item['item_discount'] = $product_expirie->quantity * $item_discount;
                                                } else {
                                                    $item['unit_quantity'] = $product_expirie->quantity;
                                                    $item['product_unit_id'] = $product_detail->unit;
                                                    $item['product_unit_code'] = $unit->code;
                                                }
                                            }   
                                            $expiry_items[] = $item;
                                            if ($type=='Delivery') {
                                                $item['foc_qty'] = 0;
                                            } else if ($type=='Sale' || $type=='POS') {
                                                $item['foc'] = 0;
                                            }   
                                        }
                                    }
                                    if($expiry_quantity > 0 && $con==1){
                                        unset($item['expiry']);
                                        $item['quantity'] = $expiry_quantity;
                                        if ($type=='Sale' || $type=='Delivery' || $type=='Delivery' || $type=='POS') {
                                            $item['unit_quantity'] = $expiry_quantity;
                                            $item['product_unit_id'] = $product_detail->unit;
                                            $item['product_unit_code'] = $unit->code;
                                            $item['subtotal'] = $expiry_quantity * $price;
                                            $item['discount'] = $discount;
                                            $item['item_discount'] = $expiry_quantity * $item_discount;
                                        } else {
                                            $item['unit_quantity'] = $expiry_quantity;
                                            $item['product_unit_id'] = $product_detail->unit;
                                            $item['product_unit_code'] = $unit->code;
                                        }
                                        $expiry_items[] = $item;
                                        if ($type=='Delivery') {
                                            $item['foc_qty'] = 0;
                                        } else if ($type=='Sale' || $type=='POS') {
                                            $item['foc'] = 0;
                                        }
                                    }
                                } else {
                                    unset($item['expiry']);
                                    $item['quantity'] = $expiry_quantity;  
                                    if ($type=='Sale' || $type=='Delivery' || $type=='POS') {
                                        $item['unit_quantity'] = $expiry_quantity;
                                        $item['product_unit_id'] = $product_detail->unit;
                                        $item['product_unit_code'] = $unit->code;
                                        $item['subtotal'] = $expiry_quantity * $price;
                                        $item['discount'] = $discount;
                                        $item['item_discount'] = $expiry_quantity * $item_discount;
                                    } else {
                                        $item['unit_quantity'] = $expiry_quantity;
                                        $item['product_unit_id'] = $product_detail->unit;
                                        $item['product_unit_code'] = $unit->code;
                                    }
                                    $expiry_items[] = $item;
                                    if ($type=='Delivery') {
                                        $item['foc_qty'] = 0;
                                    } else if($type=='Sale' || $type=='POS') {
                                        $item['foc'] = 0;
                                    }
                                }
                            } else {
                                $expiry_items[] = $item;
                                if ($type == 'Delivery') {
                                    $item['foc_qty'] = 0;
                                } else if ($type == 'Sale' || $type == 'POS') {
                                    $item['foc'] = 0;
                                }
                            }
                        }                       
                    } else {
                        $expiry_items[] = $item;
                    }
                } else if ($type == 'POS') {
                    $product_expiries = $this->getProductExpiredByProductID($item['product_id'], $item['warehouse_id'], $transaction, $transaction_id);
                    if ($product_expiries) {
                        $con = 1;
                        $expiry_quantity = 0;
                        foreach($product_expiries as $product_expirie){
                            if($expiry_quantity == 0){
                                $expiry_quantity = abs($item['quantity']) + $sale_foc;
                            }
                            $expiry_date = $this->bpas->fsd($product_expirie->expiry);
                            if ($expiry_items) {
                                foreach($expiry_items as $expiry_item){
                                    if ($expiry_item['product_id'] == $item['product_id'] && $expiry_item['expiry'] == $expiry_date) {
                                        $product_expirie->quantity -= abs($expiry_item['quantity']);
                                    }
                                }
                            }
                            if ($con == 1 && $expiry_date >= date('Y-m-d') && $product_expirie->quantity > 0) {
                                if ($product_expirie->quantity >= $expiry_quantity) {
                                    $item['quantity'] = $expiry_quantity;
                                    $item['expiry'] = $expiry_date;
                                    if($type=='Sale' || $type=='Delivery' || $type=='POS'){
                                        $item['unit_quantity'] = $expiry_quantity;
                                        $item['product_unit_id'] = $product_detail->unit;
                                        $item['product_unit_code'] = $unit->code;
                                        $item['unit_price'] =  $price;
                                        $item['net_unit_price'] =  $price;
                                        $item['subtotal'] = $expiry_quantity * $price;
                                        $item['discount'] = $discount;
                                        $item['item_discount'] = $expiry_quantity * $item_discount;
                                    } else {
                                        $item['unit_quantity'] = $expiry_quantity;
                                        $item['product_unit_id'] = $product_detail->unit;
                                        $item['product_unit_code'] = $unit->code;
                                    }
                                    $con = 0;
                                } else {
                                    $expiry_quantity = $expiry_quantity - $product_expirie->quantity;
                                    $item['quantity'] = $product_expirie->quantity;
                                    $item['expiry'] = $expiry_date;
                                    if ($type == 'Sale' || $type == 'Delivery' || $type == 'POS') {
                                        $item['unit_quantity'] = $product_expirie->quantity;
                                        $item['product_unit_id'] = $product_detail->unit;
                                        $item['product_unit_code'] = $unit->code;
                                        $item['unit_price'] =  $price;
                                        $item['net_unit_price'] =  $price;
                                        $item['subtotal'] = $product_expirie->quantity * $price;
                                        $item['discount'] = $discount;
                                        $item['item_discount'] = $product_expirie->quantity * $item_discount;
                                    } else {
                                        $item['unit_quantity'] = $product_expirie->quantity;
                                        $item['product_unit_id'] = $product_detail->unit;
                                        $item['product_unit_code'] = $unit->code;
                                    }
                                }   
                                $expiry_items[] = $item;
                            }
                        }
                        if ($expiry_quantity > 0 && $con==1) {
                            unset($item['expiry']);
                            $item['quantity'] = $expiry_quantity;
                            if($type == 'Sale' || $type == 'Delivery' || $type == 'POS'){
                                $item['unit_quantity'] = $expiry_quantity;
                                $item['product_unit_id'] = $product_detail->unit;
                                $item['product_unit_code'] = $unit->code;
                                $item['subtotal'] = $expiry_quantity * $price;
                                $item['discount'] = $discount;
                                $item['unit_price'] =  $price;
                                $item['net_unit_price'] =  $price;
                                $item['item_discount'] = $expiry_quantity * $item_discount;
                            } else {
                                $item['unit_quantity'] = $expiry_quantity;
                                $item['product_unit_id'] = $product_detail->unit;
                                $item['product_unit_code'] = $unit->code;
                            } 
                            $expiry_items[] = $item;
                        }
                    } else {
                        $expiry_items[] = $item;
                    }
                } else {
                    $expiry_items[] = $item;
                }
            }
            endif;
            $expiry_stockmoves = false;
            foreach ($stockmoves as $stockmove) {
                if ($stockmove['expiry'] != '') {
                    $expiry_quantity = abs($stockmove['quantity']);
                    $product_expiry  = $this->getProductExpiredByProductID($stockmove['product_id'], $stockmove['warehouse_id'], $transaction, $transaction_id, $stockmove['expiry']);
                    if ($product_expiry && $stockmove['quantity'] < 0) {
                        foreach ($product_expiry as $row_expiry) {
                            $row_expiry->expiry = $this->bpas->fsd($row_expiry->expiry);
                            if ($expiry_stockmoves) {
                                foreach ($expiry_stockmoves as $expiry_stockmove) {
                                    if ($expiry_stockmove['product_id'] == $stockmove['product_id'] && $expiry_stockmove['expiry'] == $row_expiry->expiry){
                                        $row_expiry->quantity -= abs($expiry_stockmove['quantity']);
                                    }
                                }
                            }
                            if ($row_expiry->quantity < abs($stockmove['quantity'])) {
                                $stockmove['quantity'] = $row_expiry->quantity * (-1);
                                $expiry_stockmoves[] = $stockmove;
                                $expiry_quantity  = $expiry_quantity - $row_expiry->quantity;
                                $product_expiries = $this->getProductExpiredByProductID($stockmove['product_id'], $stockmove['warehouse_id'], $transaction, $transaction_id);
                                if ($product_expiries) {
                                    $con = 1;
                                    foreach($product_expiries as $product_expirie) {
                                        $expiry_date = $this->bpas->fsd($product_expirie->expiry);
                                        if ($expiry_stockmoves) {
                                            foreach($expiry_stockmoves as $expiry_stockmove) {
                                                if ($expiry_stockmove['product_id'] == $stockmove['product_id'] && $expiry_stockmove['expiry'] == $expiry_date){
                                                    $product_expirie->quantity -= abs($expiry_stockmove['quantity']);
                                                }
                                            }
                                        }
                                        if ($con==1 && $expiry_date != $row_expiry->expiry && $expiry_date >= date('Y-m-d') && $product_expirie->quantity > 0) {
                                            if ($product_expirie->quantity >= $expiry_quantity) {
                                                $stockmove['quantity'] = $expiry_quantity * (-1);
                                                $stockmove['expiry'] = $expiry_date;
                                                $con = 0;
                                            } else {
                                                $expiry_quantity = $expiry_quantity - $product_expirie->quantity;
                                                $stockmove['quantity'] = $product_expirie->quantity * (-1);
                                                $stockmove['expiry'] = $expiry_date;
                                            }   
                                            $expiry_stockmoves[] = $stockmove;
                                        }
                                    }
                                    if ($expiry_quantity > 0 && $con==1) {
                                        unset($stockmove['expiry']);
                                        $stockmove['quantity'] = $expiry_quantity * (-1);
                                        $expiry_stockmoves[] = $stockmove;
                                    }
                                } else {
                                    unset($stockmove['expiry']);
                                    $stockmove['quantity'] = $expiry_quantity * (-1);
                                    $expiry_stockmoves[] = $stockmove;
                                }
                            } else {
                                $expiry_stockmoves[] = $stockmove;
                            }
                        }                       
                    } else {
                        $expiry_stockmoves[] = $stockmove;
                    }  
                } else if ($type=='POS') {
                    $product_expiries = $this->getProductExpiredByProductID($stockmove['product_id'], $stockmove['warehouse_id'], $transaction, $transaction_id);
                    if ($product_expiries) {
                        $con = 1;
                        $expiry_quantity = 0;
                        foreach($product_expiries as $product_expirie) {
                            if ($expiry_quantity == 0) {
                                $expiry_quantity = abs($stockmove['quantity']);
                            }
                            $expiry_date = $this->bpas->fsd($product_expirie->expiry);
                            if ($expiry_stockmoves) {
                                foreach($expiry_stockmoves as $expiry_stockmove) {
                                    if ($expiry_stockmove['product_id'] == $stockmove['product_id'] && $expiry_stockmove['expiry'] == $expiry_date) {
                                        $product_expirie->quantity -= abs($expiry_stockmove['quantity']);
                                    }
                                }
                            }
                            if ($con==1 && $expiry_date >= date('Y-m-d') && $product_expirie->quantity > 0) {
                                if ($product_expirie->quantity >= $expiry_quantity) {
                                    $stockmove['quantity'] = $expiry_quantity * (-1);
                                    $stockmove['expiry'] = $expiry_date;
                                    $con = 0;
                                } else {
                                    $expiry_quantity = $expiry_quantity - $product_expirie->quantity;
                                    $stockmove['quantity'] = $product_expirie->quantity * (-1);
                                    $stockmove['expiry'] = $expiry_date;
                                }   
                                $expiry_stockmoves[] = $stockmove;
                            }
                        }
                        if ($expiry_quantity > 0 && $con==1) {
                            unset($stockmove['expiry']);
                            $stockmove['quantity'] = $expiry_quantity * (-1);
                            $expiry_stockmoves[] = $stockmove;
                        }
                    } else {
                        $expiry_stockmoves[] = $stockmove;
                    }
                } else {
                    $expiry_stockmoves[] = $stockmove;
                }
            }
            return array('expiry_stockmoves' => $expiry_stockmoves , 'expiry_items' => $expiry_items);
        }
        return false;
    }

    public function delete_stock_movement($transaction = false, $transaction_id = false)
    {
        $this->db->delete('stock_movement', array('transaction' => $transaction, 'transaction_id' => $transaction_id));
    }

    public function getDownPaymentByID($id) 
    {
        $q = $this->db->get_where('down_payments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductExpiredByProductID($pid = false, $warehouse_id = false, $transaction = false, $transaction_id = false, $expiry = false)
    {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($expiry) {
            $this->db->where('expiry', $expiry);
        }
        if ($transaction && $transaction_id) {
            $this->db->where('(transaction != "'.$transaction.'" OR (transaction = "'.$transaction.'" AND transaction_id != '.$transaction_id.'))');
        }
        $this->db->select('sum('.$this->db->dbprefix("stock_movement").'.quantity) as quantity, expiry')->where('product_id', $pid)->order_by('expiry')->group_by('expiry');
        $q = $this->db->get('stock_movement');
        if ($q->num_rows() > 0) {
            $quantity = 0;
            foreach ($q->result() as $row) {
                if ($row->expiry && $row->expiry!='0000-00-00') {
                    $row->expiry = $this->bpas->hrsd($row->expiry);
                    $data[$row->expiry] = $row;
                } else {
                    $quantity += $row->quantity;
                    $row->quantity = $quantity;
                    $row->expiry = '00/00/0000';
                    $data['0000-00-00'] = $row;
                }
            }
            return $data;
        }
        return false;
    }

    public function getBrands()
    {
        $q = $this->db->get('brands');
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getMemberCardByID($id)
    {
        $q = $this->db->get_where('member_cards', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getMemberCardByNO($no)
    {
        $q = $this->db->get_where('member_cards', ['card_no' => $no], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getMultiBillerByID($id)
    {
        $q = $this->db->query(' SELECT * FROM bpas_companies WHERE id IN (' . $id . ') ');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;        
            }
            return $data;
        }
        return FALSE;
    }

    public function getBillerDetails($biller_id = null)
    {
        if ($biller_id) {
            $biller = $this->site->getCompanyByID($biller_id);
        } else {
            if ($this->Owner || $this->Admin) {
                $biller = $this->site->getCompanyByID($this->Settings->default_biller);
            } else {
                $user_log = $this->site->getUser($this->session->userdata('user_id'));
                if ($this->Settings->multi_biller) {
                    $user_biller_id = $user_log->multi_biller;
                } else {
                    $user_biller_id = $user_log->biller_id;
                }
                if ($user_biller_id) {
                    $multi_biller = explode(',', $user_biller_id);
                    if (count($multi_biller) > 1) {
                        if ($this->Settings->default_biller) {
                            $key = array_search($this->Settings->default_biller, $multi_biller);
                            if ($key !== false) {
                                $biller = $this->site->getCompanyByID($multi_biller[$key]);
                            } else {
                                $biller = $this->site->getCompanyByID($multi_biller[0]);
                            }
                        }
                    } else {
                        $biller = $this->site->getCompanyByID($multi_biller[0]);
                    }
                } else {
                    $biller = $this->site->getCompanyByID($this->Settings->default_biller);
                }
            }
        }

        return $biller;
    }
    public function getSaleBalanceByID($sale_id = false) {
        if($sale_id){
            $this->db->where("sales.id",$sale_id);
            $this->db->select("
                                sales.*,
                                bpas_payments.paid,
                                bpas_payments.discount,
                                bpas_return.total_return,
                                bpas_return.total_return_paid,
                                0 as balance
                            ")
                        ->join('(SELECT
                                    sale_id,
                                    SUM(ABS(grand_total)) AS total_return,
                                    SUM(paid) AS total_return_paid
                                FROM
                                    '.$this->db->dbprefix('sales').' WHERE sale_status = "returned"
                                AND
                                    sale_id = '.$sale_id.') as bpas_return', 'bpas_return.sale_id=sales.id', 'left')
                        ->join('(SELECT
                                    sale_id,
                                    IFNULL(SUM(amount),0) AS paid,
                                    IFNULL(SUM(discount),0) AS discount
                                FROM
                                    '.$this->db->dbprefix('payments').'
                                WHERE
                                    transaction is NULL AND
                                    sale_id = '.$sale_id.') as bpas_payments', 'bpas_payments.sale_id=sales.id', 'left');
            $q = $this->db->get("sales");
            if ($q->num_rows() > 0) {
                $data = $q->row();
                $grand_total = $data->grand_total;
                $paid = $data->paid;
                $discount = $data->discount;
                $total_return = $data->total_return;
                $total_return_paid = $data->total_return_paid;
                $balance = $this->bpas->formatDecimal($grand_total - $paid - $discount - ($total_return + $total_return_paid));
                $data->balance = $balance;
                return $data;
            }
        }
        return FALSE;
    }
    public function getSalaryTax($employee_id = false, $param_salary_tax = false)
    {
        $this->load->admin_model("hr_model");
        $data         = array();
        $currency     = $this->getCurrencyByCode("KHR");
        $salary_tax   = $param_salary_tax;
        $employee     = $this->hr_model->getEmployeeById($employee_id);
        $salary_taxs  = $this->hr_model->getSalaryTaxCondition();
        
        $spouses           = $this->hr_model->getSpouseMemberByEmployeeID($employee_id);
        $spouses           = $spouses?count($spouses):0;
        $spouses_reduction = $spouses * 150000;

        $childs            = $this->hr_model->getChildrenMemberByEmployeeID($employee_id);
        $childs            = $childs?count($childs):0;
        $childs_reduction  = $childs * 150000;

        foreach($salary_taxs as $tax){

            if($employee->non_resident==0){
                $base_salary_tax = $salary_tax;
                if(($base_salary_tax >= $tax->min_salary) && ($base_salary_tax <= $tax->max_salary)){
                    $tax_on_salary = ($base_salary_tax * $tax->tax_percent) - $tax->reduce_tax;
                    $data = array(
                            "tax_percent"       => $tax->tax_percent,
                            "reduce_tax"        => $tax->reduce_tax,
                            "tax_on_salary"     => ($tax_on_salary / $currency->rate),
                            "tax_on_salary_riel"=> $tax_on_salary,
                            "spouses_reduction" => $spouses_reduction,
                            "childs_reduction"  => $childs_reduction,
                            "spouses"           => $spouses,
                            "childs"            => $childs,
                        ); 
                }
              
            }else{
                // mutiply with 20% non-resident
                $tax_on_salary = ($salary_tax * 0.2);
                $data = array(
                        "tax_percent"   => 0,
                        "reduce_tax"    => 0,
                        "tax_on_salary" => ($tax_on_salary / $currency->rate),
                        "tax_on_salary_riel"=> 0,
                        "spouses_reduction" => 0,
                        "childs_reduction"  => 0,
                        "spouses"           => 0,
                        "childs"            => 0,
                    );
            }
        }
        return $data;
    }

    public function getTeacherSalaryTax($employee_id = false, $param_salary_tax = false)
    {
        $this->load->admin_model("hr_model");
        $this->load->admin_model("schools_model");
        $data         = array();
        $currency     = $this->getCurrencyByCode("KHR");
        $salary_tax   = $param_salary_tax * $currency->rate;
        $employee     = $this->schools_model->getTeacherByID($employee_id);
        $salary_taxs  = $this->hr_model->getSalaryTaxCondition();
        $spouses      = $this->hr_model->getSpouseMemberByEmployeeID($employee_id);
        $childs       = $this->hr_model->getChildrenMemberByEmployeeID($employee_id);
        foreach($salary_taxs as $tax){

            if($employee->non_resident==0){
                $allowance       = (($spouses?count($spouses):0) + ($childs?count($childs):0)) * 150000;
                $base_salary_tax = $salary_tax - $allowance;
                if(($base_salary_tax <= $tax->max_salary) && ($base_salary_tax >= $tax->min_salary)){
                    $tax_on_salary = ($base_salary_tax * $tax->tax_percent) - $tax->reduce_tax;
                    $data = array(
                        "tax_percent"   => $tax->tax_percent,
                        "reduce_tax"    => $tax->reduce_tax,
                        "tax_on_salary" => ($tax_on_salary / $currency->rate),
                    ); 
                }
              
            }else{
                // mutiply with 20% non-resident
                $tax_on_salary = ($salary_tax * 0.2);
                $data = array(
                        "tax_percent"   => 0,
                        "reduce_tax"    => 0,
                        "tax_on_salary" => ($tax_on_salary / $currency->rate),
                    );
            }
        }
        return $data;
    }
    
    public function getAllPaymentTerms() {
        $q = $this->db->get('payment_term');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStockInItems($id)
    {
        $this->db->select('
            stock_received_items.*, stock_received_items.quantity AS stock_received_qty, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, 
            products.unit, products.other_cost, products.currency, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name,
            currencies.symbol as symbol')
            ->join('products', 'products.id=stock_received_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=stock_received_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=stock_received_items.tax_rate_id', 'left')
            ->join('currencies', 'currencies.code=products.currency', 'left')
            ->where('stock_received_id', $id)
            ->order_by('id', 'asc');

        $q = $this->db->get('stock_received_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPaymentTermsByID($id = NULL)
    {
        $q = $this->db->where('id', $id)->get('payment_term');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getStudentByID($id)
    {
        $q = $this->db->get_where('sh_students', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchasedItemstoSales($product_id, $warehouse_id, $option_id = null, $nonPurchased = false, $expiry = null)
    {
        $orderby = empty($this->Settings->accounting_method) ? 'asc' : 'desc';
        if ($this->Settings->product_expiry && $this->Settings->fefo) {
            $this->db->order_by('expiry', 'asc');
        } else {
            $this->db->order_by('date', $orderby);
            $this->db->order_by('purchase_id', $orderby);
        }
        $this->db->select('id, product_id, quantity, SUM(quantity_balance) as quantity_balance, base_unit_cost, net_unit_cost, unit_cost, item_tax, expiry');
        $this->db->where('product_id', $product_id)->where('warehouse_id', $warehouse_id);
        if($expiry != '' && $expiry != 'null' && $expiry != '0000-00-00' && $expiry != NULL){
            $this->db->where('expiry', $expiry);
        }   
        if (!isset($option_id) || empty($option_id)) {
            $this->db->group_start()->where('option_id', null)->or_where('option_id', 0)->group_end();
        } else {
            $this->db->where('option_id', $option_id);
        }
        if ($nonPurchased) {
            $this->db->group_start()->where('purchase_id !=', null)->or_where('transfer_id !=', null)->group_end();
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $this->db->group_by("expiry");
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPurchasedItemstoTransfers($product_id, $warehouse_id, $option_id = null, $nonPurchased = false, $expiry = null)
    {
        $orderby = empty($this->Settings->accounting_method) ? 'asc' : 'desc';
        if ($this->Settings->product_expiry && $this->Settings->fefo) {
            $this->db->order_by('expiry', 'asc');
        } else {
            $this->db->order_by('date', $orderby);
            $this->db->order_by('purchase_id', $orderby);
        }
        $this->db->select('id, product_id, quantity, SUM(quantity_balance) as quantity_balance, net_unit_cost, base_unit_cost, unit_cost, item_tax, expiry');
        $this->db->where('product_id', $product_id)->where('warehouse_id', $warehouse_id);
        if($expiry != '' && $expiry != 'null' && $expiry != '0000-00-00' && $expiry != NULL){
             $this->db->where('expiry', $expiry);
        }   
        if (!isset($option_id) || empty($option_id)) {
            $this->db->group_start()->where('option_id', null)->or_where('option_id', 0)->group_end();
        } else {
            $this->db->where('option_id', $option_id);
        }
        // if ($nonPurchased) {
        //     $this->db->group_start()->where('purchase_id !=', null)->or_where('transfer_id !=', null)->group_end();
        // }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $this->db->group_by("expiry");
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductBalanceQuantity($product_id, $warehouse_id = false, $option_id = null, $nonPurchased = false, $expiry = null)
    {
        $orderby = empty($this->Settings->accounting_method) ? 'asc' : 'desc';
        if ($this->Settings->product_expiry && $this->Settings->fefo) {
            $this->db->order_by('expiry', 'asc');
        } else {
            $this->db->order_by('date', $orderby);
            $this->db->order_by('purchase_id', $orderby);
        }
        $this->db->select('id, quantity, quantity_balance, net_unit_cost, base_unit_cost, unit_cost, item_tax');
        $this->db->where('product_id', $product_id)->where('quantity_balance !=', 0);
        if($warehouse_id){
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if($expiry != '' && $expiry != 'null' && $expiry != '0000-00-00' && $expiry != NULL){
            $this->db->where('expiry', $expiry);
        }  
        if (!isset($option_id) || empty($option_id)) {
            $this->db->group_start()->where('option_id', null)->or_where('option_id', 0)->group_end();
        } else {
            $this->db->where('option_id', $option_id);
        }
        if ($nonPurchased) {
            $this->db->group_start()->where('purchase_id !=', null)->or_where('transfer_id !=', null)->group_end();
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $this->db->group_by('id');
        $q = $this->db->get('purchase_items');
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

    public function getAllPriceGroups($type = null)
    {
        if ($type) {
            $this->db->where('type', $type);
        }
        $q = $this->db->get('price_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductPriceMultiUnits($id, $group_id = null, $academic_year = null)
    {
        $condition = (!empty($academic_year) ? (" AND product_prices.academic_year = {$academic_year} ") : "");
        if ($group_id) {
            $this->db->select("
                units.*, cost_price_by_units.*, units.id AS id,
                COALESCE({$this->db->dbprefix('product_prices')}.price, {$this->db->dbprefix('cost_price_by_units')}.price) AS price ");
        } else {
            $this->db->select('units.*, cost_price_by_units.*, units.id AS id');
        }
        $this->db->join('units', 'units.id = cost_price_by_units.unit_id', 'left');
        if ($group_id) {
            $this->db->join(
                "product_prices", 
                "
                    product_prices.product_id = cost_price_by_units.product_id AND 
                    product_prices.unit_id = cost_price_by_units.unit_id AND 
                    product_prices.price_group_id = {$group_id} {$condition}
                ", "left");
        }
        $this->db->where('cost_price_by_units.product_id', $id);
        $this->db->order_by('units.id asc');
        $q = $this->db->get('cost_price_by_units');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductGroupPrice_New($product_id, $group_id, $unit_id, $academic_year = null)
    {
        $this->db->select('*');
        $this->db->where('product_id', $product_id);
        $this->db->where('price_group_id', $group_id);
        $this->db->where('unit_id', $unit_id);
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        $this->db->limit(1);
        $q = $this->db->get('product_prices');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    
    public function getCouponByID($id)
    {
        $q = $this->db->get_where('coupon', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getpaymentIn_by($paid, $start_date = false, $end_date = false, $biller_id = false)
    {
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS amount', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.sale_id IS NOT null')
            ->where('payments.type','received')
            ->where('paid_by',$paid);
        if($start_date){
            $this->db->where('payments.date >=', $start_date)->where('payments.date <=', $end_date);
        }
        if($biller_id){
            $this->db->where('sales.biller_id',$biller_id);
        }
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getpaymentInReturn_by($paid,$start_date=false,$end_date=false,$biller_id=false)
    {
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS amount', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.sale_id IS NOT null')
            ->where('payments.type','returned')
            ->where('paid_by',$paid);
        if($start_date){
            $this->db->where('payments.date >=', $start_date)->where('payments.date <=', $end_date);
        }
        if($biller_id){
            $this->db->where('sales.biller_id',$biller_id);
        }
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getpaymentOut_by($paid,$start_date=false,$end_date=false,$biller_id=false)
    {
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS amount', false)
            ->join('purchases', 'purchases.id = payments.purchase_id', 'left')
            ->where('payments.purchase_id IS NOT null')
            ->where('paid_by',$paid);

        if($start_date){
            $this->db->where('payments.date >=', $start_date)->where('payments.date <=', $end_date);
        }
        if($biller_id){
            $this->db->where('purchases.biller_id',$biller_id);
        }
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getpaymentExpense_by($paid,$start_date=false,$end_date=false,$biller_id=false)
    {

        $cash_account = $this->getCashAccountByCode($paid);
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS amount', false);
        if($start_date){
            $this->db->where('expenses.date >=', $start_date)->where('expenses.date <=', $end_date);
        }
        
        if($cash_account->account_code){
            $this->db->where('expenses.bank_code',$cash_account->account_code);
        }else{
            $this->db->where('paid_by',$paid);
        }
        
        if($biller_id){
            $this->db->where('expenses.biller_id',$biller_id);
        }
        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTaxItem($type,$id = false)
    {
        $q = $this->db->get_where("tax_items",
            array(
                "transaction"=>$type,
                "transaction_id"=>$id
            ));
        if($q->num_rows() > 0){
             return $q->row();
        }
        return false;
    }

    public function getPosCloseRegiter($id)
    {
        $q = $this->db->get_where('pos_register', array('id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSalemans()
    {
        $saleman = $this->Settings->group_saleman_id;
        $q = $this->db->get_where('users',array('group_id'=>$saleman));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getNSSFPayment($employee_id = false, $param_salary_tax = false)
    {
        $this->load->admin_model("hr_model");
        $data         = array();
        //$currency     = $this->getCurrencyByCode("KHR");
        $employee     = $this->hr_model->getEmployeeById($employee_id);
        $OR_salary  = $this->hr_model->getSalaryNSSFCondition();
        foreach($OR_salary as $tax){
            if($employee->nssf==1){
                $base_salary_tax = $param_salary_tax;
                if(($base_salary_tax <= $tax->max_salary) && ($base_salary_tax >= $tax->min_salary)){
                    $payment_or = ($tax->contributory_wage*$tax->or_rate)/100;
                    $payment_hc = ($tax->contributory_wage*$tax->hc_rate)/100;
                    $data = array(
                            "contributory_wage" => $tax->contributory_wage,
                            "contributory_or"   => $payment_or,
                            "contributory_hc"   => $payment_hc,
                        ); 
                }
              
            }
        }
        return $data;
    }

    public function getPenaltyRang()
    {
        $q = $this->db->get('installments_penalty');
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPenaltyByID($id)
    {
        if (!empty($id) && $id != '') {
            $q = $this->db->get_where('installments_penalty', ['id' => $id], 1);
            if ($q->num_rows() > 0) {
                return $q->row();
            }
        }
        return false;
    }

    public function getPenaltyPayment_11_04_2023($payment_amount, $day = false)
    {
        $data     = 0;
        $amounts  = $this->getPenaltyRang();
        foreach($amounts as $penalty) {
            if ($day > 0) {
                if(($day >= $penalty->from_day)&& ($day <= $penalty->to_day)){
                    if($penalty->type =='%'){
                        $payment_amount= ($payment_amount*$penalty->amount)/100;
                    }else{
                        $payment_amount= $penalty->amount;
                    }
                    $data = $payment_amount; 
                }
            } else {
                $data = 0;
            }

        }
        return $data;
    }

    public function getPenaltyPayment($payment_amount, $day = false, $penalty_id = null)
    {
        $data = 0;
        if ($penalty_id) {
            $penalty = $this->getPenaltyByID($penalty_id);
            if ($day > 0 && !empty($penalty)) {
                if ($penalty->type =='%') {
                    $payment_amount = ($payment_amount * $penalty->amount) / 100;
                } else {
                    $payment_amount = $penalty->amount;
                }
                $data = ($payment_amount * $day); 
            } else {
                $data = 0;
            }
        } else {
            $amounts = $this->getPenaltyRang();
            foreach($amounts as $penalty) {
                if ($day > 0) {
                    if (($day >= $penalty->from_day) && ($day <= $penalty->to_day)) {
                        if ($penalty->type =='%') {
                            $payment_amount = ($payment_amount * $penalty->amount) / 100;
                        } else {
                            $payment_amount = $penalty->amount;
                        }
                        $data = $payment_amount; 
                    }
                } else {
                    $data = 0;
                }
            }
        }
        return $data;
    }

    public function get_total_payment_alerts()
    {
        $installment_alert_days = ($this->Settings->installment_alert_days?$this->Settings->installment_alert_days:0);
        $this->db->select('COUNT(*) AS count');
        $this->db->where('DATE_SUB('. $this->db->dbprefix('down_payments')  .'.payment_date, INTERVAL '.$installment_alert_days.' DAY) <= CURDATE()');
        $this->db->where('status =',0);
        $q = $this->db->get('down_payments');
        if($q->num_rows() > 0 ){
            $q = $q->row();
            return $q->count;
        }
        return false;
    }

    public function syncHRData()
    {
        if ($this->Settings->module_hr) {
            $current_date = date("Y-m-d");
                //Sysn Employee Status
                $this->db->where("resigned_date <=",$current_date)->where("IFNULL(resigned_date,'0000-00-00') !=", "0000-00-00")->update("hr_employees_working_info",array("status"=>"inactive")); 
                //Sync Annual Leave
                $annual_leave = $this->db->get_where("hr_leave_categories",array("code" => "annual_leave"));
                if ($annual_leave->num_rows() > 0) {
                    $increase_year = 3;
                    $annual_leave = $annual_leave->row()->days;     
                    $this->db->query("UPDATE ".$this->db->dbprefix('hr_employees_working_info')." 
                                        LEFT JOIN ".$this->db->dbprefix('att_policies')." ON ".$this->db->dbprefix('att_policies').".id = ".$this->db->dbprefix('hr_employees_working_info')." .policy_id 
                                        SET ".$this->db->dbprefix('hr_employees_working_info')." .annual_leave =
                                        IF
                                            (
                                                IFNULL( ".$this->db->dbprefix('att_policies').".yearly_annual_leave, 0 ) > 0,
                                                ".$this->db->dbprefix('att_policies').".yearly_annual_leave,
                                            IF
                                                (
                                                    TIMESTAMPDIFF( YEAR, ".$this->db->dbprefix('hr_employees_working_info')." .employee_date, '".$current_date."' ) > ".$increase_year.",
                                                    FLOOR( TIMESTAMPDIFF( YEAR, ".$this->db->dbprefix('hr_employees_working_info')." .employee_date, '".$current_date."' ) / ".$increase_year." ) + ".$annual_leave.",
                                                IF
                                                    (
                                                        TIMESTAMPDIFF( YEAR, ".$this->db->dbprefix('hr_employees_working_info')." .employee_date, '".$current_date."' ) < IFNULL( ".$this->db->dbprefix('att_policies').".working_year_annual_leave, 0 ),
                                                        TIMESTAMPDIFF( MONTH, ".$this->db->dbprefix('hr_employees_working_info')." .employee_date, '".$current_date."' ) * ( ".$annual_leave." / 12 ),
                                                        ".$annual_leave." 
                                                    )
                                                )
                                            ),
                                        ".$this->db->dbprefix('hr_employees_working_info')." .monthly_annual_leave = IFNULL( ".$this->db->dbprefix('att_policies').".monthly_annual_leave, 0 )
                                        WHERE
                                            ".$this->db->dbprefix('hr_employees_working_info')." .`status` = 'active'");
                } 
                //Sync Seniority
                $first_seniority = 2;
                $last_seniority = 11;
                $this->db->query("UPDATE ".$this->db->dbprefix('hr_employees_working_info')." 
                                    SET seniority = 
                                    IF(no_seniority = 1,0,  
                                    IF(
                                        TIMESTAMPDIFF( YEAR, employee_date, '".$current_date."' ) -
                                    IF
                                        ( TIMESTAMPDIFF( MONTH, employee_date, '".$current_date."' ) / 12 > TIMESTAMPDIFF( YEAR, employee_date, '".$current_date."' ), 0, 1 ) > 0,
                                    IF
                                        (
                                            TIMESTAMPDIFF( YEAR, employee_date, '".$current_date."' ) -
                                        IF
                                            ( TIMESTAMPDIFF( MONTH, employee_date, '".$current_date."' ) / 12 > TIMESTAMPDIFF( YEAR, employee_date, '".$current_date."' ), 0, 1 ) >= ".$last_seniority.",
                                            ".$last_seniority.",
                                            TIMESTAMPDIFF( YEAR, employee_date, '".$current_date."' ) -
                                        IF
                                            ( TIMESTAMPDIFF( MONTH, employee_date, '".$current_date."' ) / 12 > TIMESTAMPDIFF( YEAR, employee_date, '".$current_date."' ), 0, 1 ) 
                                        ) - 1 + ".$first_seniority.",
                                        0 
                                    ) )
                                ");
        }
    }
    public function getCustomerByCode($code = false){
        if($code){
            $q = $this->db->get_where('companies',array('code' => $code,'group_name' => 'customer'));
            if($q->num_rows() > 0){
                return $q->row();
            }
        }
        return false;
    }
    public function getSupplierByCode($code = false){
        if($code){
            $q = $this->db->get_where('companies',array('code'=>$code,'group_name'=>'supplier'));
            if($q->num_rows() > 0){
                return $q->row();
            }
        }
        return false;
    }
    public function getProductUnitByCodeName($product_id = false, $unit = false){
        $this->db->where("product_units.product_id",$product_id);
        $this->db->where("(".$this->db->dbprefix("units").".code = ".$this->db->escape($unit)." OR ".$this->db->dbprefix("units").".name = ".$this->db->escape($unit).")");
        $this->db->select("product_units.unit_id, product_units.product_id, product_units.unit_qty, units.code, units.name");
        $this->db->join("units","units.id = product_units.unit_id","INNER");
        $q = $this->db->get("product_units");
        if($q->num_rows() > 0){
            return $q->row();
        }       
        return false;
    }
    public function getTaxRateByCode($code = false) {
        $q = $this->db->get_where('tax_rates', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    //------------shop------
    public function getbusiness() {
        $q = $this->db->get("bussiness_type");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function showbusiness($id) {
        if($id){
            $q = $this->db->get_where('bussiness_type', array('id' => $id), 1);
            if ($q->num_rows() > 0) {
                return $q->row()->name;
            }
            return FALSE;
        }
        return FALSE;
    }
    function notfound(){
        $data = '<h2>OOPS! 1NO RESULTS FOUND</h2>';
        return $data;
    }
    function page_user($user_id){
        $ci =& get_instance();
        $ci->db->select('*')->from('companies');
      //  $ci->db->where('id',$user_id);
        $query = $ci->db->get();
        $get_query=$query->first_row('array');
        $user_logged=base_url().'page/portfolio/'.$get_query['id'];
        return $user_logged;
    }
     //------users---
    function checked_user($name){
        $ci =& get_instance();
        $ci->db->select('id,company,name')->from('companies')->where('id',$name);
        $query = $ci->db->get();
        $get_query=$query->first_row('array');
        if($get_query['company']){
            return true;
        }else{
            return false;
        }
    }
    public function getAllProjects() {
        $q = $this->db->get("projects");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
      public function get_lead_status() 
    { 
        $this->db->select("COUNT({$this->db->dbprefix('containers')}.container_name) as count, {$this->db->dbprefix('containers')}.container_name as group_name")
        ->join('containers', 'containers.container_id = companies.lead_group', 'left')
        ->where('companies.group_name', 'lead')->group_by('containers.container_id'); 
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function get_source_lead() 
    { 
        $this->db->select("COUNT({$this->db->dbprefix('custom_field')}.name) as count, {$this->db->dbprefix('custom_field')}.name as source")
        ->join('custom_field', 'custom_field.id = companies.source', 'left')->where('companies.group_name', 'lead')->group_by('custom_field.id'); 
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getCheck_in_out_ByIDwithPolicies($id= NULL)
	{
        $user = $this->getuser();
        $emp = $this->getEmployeeByCode($user->emp_code);
		$q = $this->db->get_where('att_check_in_out', array('employee_id' => $emp->id), 1);
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
    public function getEmployeeByCode($code = false){
		if($code){
			$q = $this->db->get_where('hr_employees',array('empcode'=>$code));
			if($q->num_rows() > 0){
				return $q->row();
			}
		}
		return false;
	}
    public function getAllUserByEmp($id)
    {
        $q = $this->db->get_where('users', ['emp_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function syncExpensePayments($id = false) {
        $expense = $this->getExpenseByID($id);
        if($expense){
            $payments = $this->getExpensePayments($id);
            $paid = 0;
            if($payments){
                foreach ($payments as $payment) {
                    $paid += $payment->amount + $payment->discount;
                }
            }
            $payment_status = $paid <= 0 ? 'pending' : $expense->payment_status;
            if ($this->bpas->formatDecimal($expense->grand_total) > $this->bpas->formatDecimal($paid) && $paid > 0) {
                $payment_status = 'partial';
            } elseif ($this->bpas->formatDecimal($expense->grand_total) <= $this->bpas->formatDecimal($paid)) {
                $payment_status = 'paid';
            }
            if ($this->db->update('expenses', array('paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
                return true;
            }
            return FALSE;
        }
        return FALSE;
    }
    public function getExpenseByID($id = false) {
        $q = $this->db->get_where('expenses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getExpensePayments($expense_id = false) {
        $q = $this->db->get_where('payments', array('expense_id' => $expense_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getRewardExchangeByID($id)
    {
        $q = $this->db->get_where('rewards_exchange', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRewardInvoicePayments($sale_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', ['reward_exchange_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function syncRewardPayments($id = null)
    {
        if ($id) {

            $sale = $this->getRewardExchangeByID($id);
            if ($payments = $this->getRewardInvoicePayments($id)) {
                $paid        = 0;
                $grand_total = $sale->grand_total + $sale->rounding;
                foreach ($payments as $payment) {
                    $paid += $payment->amount + $payment->discount;
                }
                $payment_status = $paid == 0 ? 'pending' : $sale->payment_status;
                if ($this->bpas->formatDecimal($grand_total) == 0 || $this->bpas->formatDecimal($grand_total) == $this->bpas->formatDecimal($paid)) {
                    $payment_status = 'paid';
                } elseif ($sale->due_date <= date('Y-m-d') && !$sale->company_id) {
                    $payment_status = 'due';
                } elseif ($paid != 0) {
                    $payment_status = 'partial';
                }
                if ($this->db->update('rewards_exchange', ['paid' => $paid, 'payment_status' => $payment_status], ['id' => $id])) {
                    return true;
                }
            } else {
                $payment_status = ($sale->due_date <= date('Y-m-d')) ? 'due' : 'pending';
                if ($this->db->update('rewards_exchange', ['paid' => 0, 'payment_status' => $payment_status], ['id' => $id])) {
                    return true;
                }
            }
            return false;
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
    public function GUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
    public function getTransactionsId($id){
        $q = $this->db->get_where('tax_items', ['tax_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row->transaction_id;
            }
            return $data;
        }
    }
    public function get_employee_doc_expired(){
        $date = date('Y-m-d', strtotime('+7 day'));
       $this->db->select('count(expired_date) as date_exp')->where('expired_date !=',NULL)->where('expired_date <=', $date);
        $q = $this->db->get('hr_employees_document');
        if($q->num_rows() > 0){
            $re = $q->row();
        return $re->date_exp;
        }
        return false;

    } 
    public function getEmpRosterWorkingDay($id = false,$date){
        $this->db->join('att_policies','att_policies.id = att_roster.policy_id','left');
        $q = $this->db->get_where('att_roster',array(
                'att_roster.employee_id'=> $id,
                'att_roster.working_day'=> ''.$date.''));
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
    public function getEmpTakeleaveByEmpID_Date($id = false,$date){
        $this->db->join('att_take_leaves','att_take_leaves.id = att_take_leave_details.take_leave_id','left');
        $this->db->join('hr_leave_types','hr_leave_types.id = att_take_leave_details.leave_type','left');
        $this->db->where('att_take_leaves.status', 1);
        $this->db->where('att_take_leave_details.employee_id', $id);
        $this->db->where('att_take_leave_details.start_date <=', $date);
        $this->db->where('att_take_leave_details.end_date >=', $date);
		// $this->db->where('"'.DATE($date).'" BETWEEN start_date and `end_date_`');
        $q = $this->db->get('att_take_leave_details', 1);
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
    public function getEmpDayoffByEmpID_Date($id = false,$date){
        // $this->db->join('att_policies','att_policies.id = att_roster.policy_id','left');
        $this->db->where('att_day_off_items.employee_id', $id);
        $this->db->where('att_day_off_items.day_off', $date);
        $q = $this->db->get('att_day_off_items', 1);
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }

    public function getProductByCategoryID($id = false)
    {

        $q = $this->db->get_where('products', array('category_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncConsignment($consignment_id = false)
    {
        if ($consignment_id && $consignment_id > 0) {
            $this->db->query("
                    UPDATE ".$this->db->dbprefix("consignment_items")."
                    LEFT JOIN ( SELECT consignment_item_id, sum( quantity ) AS return_qty FROM ".$this->db->dbprefix("consignment_items")." WHERE consignment_item_id > 0 GROUP BY consignment_item_id ) AS consignment_returns ON consignment_returns.consignment_item_id = ".$this->db->dbprefix("consignment_items").".id
                    LEFT JOIN ( SELECT consignment_item_id, sum( quantity ) AS sale_qty FROM ".$this->db->dbprefix("sale_items")." WHERE consignment_item_id > 0 GROUP BY consignment_item_id ) AS consignment_sales ON consignment_sales.consignment_item_id = ".$this->db->dbprefix("consignment_items").".id 
                    SET ".$this->db->dbprefix("consignment_items").".return_qty = IFNULL( abs( consignment_returns.return_qty ), 0 ),
                    ".$this->db->dbprefix("consignment_items").".sale_qty = IFNULL( abs( consignment_sales.sale_qty ), 0 ) 
                    WHERE consignment_id = '".$consignment_id."'
                ");
            $this->db->select("sum(quantity) as quantity, sum(IFNULL(return_qty,0) + IFNULL(sale_qty,0)) as return_qty")->where("consignment_id",$consignment_id);
            $q = $this->db->get("consignment_items");       
            if ($q->num_rows() > 0) {
                $quantity = $q->row()->quantity;
                $return_qty = $q->row()->return_qty;
                if ($return_qty == $quantity) {
                    $status = "completed";
                } else if ($return_qty > 0) {
                    $status = "partial";
                } else {
                    $status = "pending";
                }
                $this->db->update('consignments', array('status' => $status), array('id' => $consignment_id));
            }
        }
    }

    public function getStockMovement($product_id = false, $warehouse_id = false, $option_id = false, $transaction = false, $transaction_id = false)
    {
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->select('id, quantity, quantity as quantity_balance');
        
        if($product_id){
            $this->db->where("product_id",$product_id);
        }
        if($warehouse_id){
            $this->db->where("warehouse_id",$warehouse_id);
        }
        if ($option_id) {
            $this->db->where('option_id', $option_id);
        }
        if($transaction && $transaction_id){
            $this->db->where('(transaction != "'.$transaction.'" OR (transaction = "'.$transaction.'" AND transaction_id != '.$transaction_id.'))');
        }
        $this->db->group_by('id');
        $this->db->order_by('date', $orderby);
        $q = $this->db->get('stock_movement');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStockMovementByTransactionID($transaction_id)
    {
        $q = $this->db->get_where('stock_movement', ['transaction_id' => $transaction_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPendingDelivery() 
    {
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('deliveries.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where('deliveries.biller_id',$this->session->userdata('biller_id'));
        }
        $q = $this->db->get_where('deliveries', array('status' => 'pending'));
        if ($q->num_rows() > 0) {
            return $q->num_rows();
        }
        return FALSE;
    }

    public function getAllStockmoves($transaction = false, $transaction_id = false)
    {
        $q = $this->db->get_where('stock_movement', array('transaction' => $transaction, 'transaction_id' => $transaction_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductMethod($product_id = false, $quantity = false, $tmp_stockmoves = false, $transaction = false, $transaction_id = false)
    {
        $product = $this->getProductByID($product_id);
        if ($product) {
            if ($product->accounting_method == 0) {
                return $this->getFifoCost($product_id, $quantity, $tmp_stockmoves, $transaction, $transaction_id);
            } else if ($product->accounting_method == 1) {
                return $this->getLifoCost($product_id, $quantity, $tmp_stockmoves, $transaction, $transaction_id);
            } else {
                return false;
            }
        }
        return false;
    }

    public function getAVGCost($product_id = false, $data = false, $transaction = false, $transaction_id = false)
    {
        if ($product = $this->getProductByID($product_id)) {
            if ($transaction && $transaction_id) {
                $this->db->where("((transaction = '" . $transaction . "' AND transaction_id != " . $transaction_id . ") OR transaction != '" . $transaction . "')");
            }
            $this->db->select('quantity, real_unit_cost, transaction, transaction_id, date');
            $this->db->where("stock_movement.date <=", $data);
            $this->db->order_by('date', 'asc');
            $this->db->order_by('quantity', 'desc');
            $this->db->order_by('id', 'asc');
            $q = $this->db->get_where("stock_movement", array('product_id' => $product_id));
            if ($q->num_rows() > 0 && $this->Settings->update_cost) {
                $old_qty  = 0;
                $old_cost = 0;
                foreach (($q->result_array()) as $row) {
                    if ($row['transaction'] == 'OpeningBalance' || $row['transaction'] == 'CostAdjustment' || $row['transaction'] == 'Pawns' || $row['transaction']=='Purchases' || $row['transaction']=='Receives' || ($row['transaction']=='QuantityAdjustment' && $row['quantity'] > 0) || ($row['transaction']=='Convert' && $row['quantity'] > 0)) {
                        $new_cost  = $row['real_unit_cost'];
                        $new_qty   = $row['quantity'];
                        $total_qty = $new_qty + $old_qty;
                        if ($old_qty >= 0) {
                            $total_old_cost = $old_qty * $old_cost;
                            $total_new_cost = $new_qty * $new_cost; 
                            $total_cost = $total_old_cost + $total_new_cost;
                            if ($total_cost != 0) {
                                $old_cost = $total_cost / $total_qty;
                            } else {
                                $old_cost = 0;
                            }
                        } else {
                            if ($total_qty > 0) {
                                $old_cost = $new_cost;
                            } else {
                                $old_cost = $product->cost;
                            }
                        }
                    }
                    $old_qty += $row['quantity'];    
                }
                if ($old_cost > 0) {
                    $old_cost = $old_cost;
                } else {
                    $old_cost = 0;
                }
                return $old_cost;
            }
        }
    }

    public function getFifoCost($product_id = false, $quantity = false, $tmp_stockmoves = false, $transaction = false, $transaction_id = false)
    {
        $this->db->select('product_id,quantity,real_unit_cost,transaction');
        $this->db->order_by('date', 'asc');
        $this->db->order_by('id', 'asc');
        if ($transaction && $transaction_id) {
            $this->db->where("NOT (`transaction_id` = " . $transaction_id . " AND `transaction` = '" . $transaction . "')");
        }
        $q = $this->db->get_where('stock_movement', array('product_id' => $product_id));
        if ($tmp_stockmoves) {
            $array_result = array_merge($q->result_array(), $tmp_stockmoves);
        } else {
            $array_result = $q->result_array();
        }
        $stock_ins = array();
        $stock_out = array();
        $total_qty = 0;
        if ($q->num_rows() > 0) {
            foreach ($array_result as $row) {
                if ($row['product_id'] == $product_id) {
                    $total_qty += $row['quantity'];
                    $cost = $row['real_unit_cost'] - 0;
                    if ($row['quantity'] < 0 ) {
                        $total_deduct = (isset($stock_out[$cost]) ? $stock_out[$cost] : 0) +  $row['quantity'];
                        $stock_out[$cost] = $total_deduct - 0;
                    } else {
                        $stock_ins[] = array('cost' => $cost, 'quantity' => ($row['quantity'])-0);
                    }
                }
            }
            if (!empty($stock_ins)) {
                foreach ($stock_ins as $stock_in) {
                    if (isset($stock_out[$stock_in['cost']]) && abs($stock_out[$stock_in['cost']] - 0) > $stock_in['quantity']) {
                        $stock_in['out_quantity'] = ($stock_in['quantity'] * -1);
                        $stock_out[$stock_in['cost']] = $stock_in['quantity'] + $stock_out[$stock_in['cost']];
                    } else {
                        $stock_in['out_quantity'] = (isset($stock_out[$stock_in['cost']]) ? $stock_out[$stock_in['cost']] : 0) - 0;
                        $stock_out[$stock_in['cost']] = 0 ;
                    }
                    if ($stock_in['quantity'] > abs($stock_in['out_quantity'])) {
                        $datas[] = $stock_in;
                    }
                }
            }
            if (!empty($datas)) {
                foreach ($datas as $data) {  
                    $out_quantity = abs($data['out_quantity']) + $quantity;
                    if ($data['quantity'] > $out_quantity) {
                        if ($quantity > 0) {
                            $item_cost[] = array('cost' => $data['cost'], 'quantity' => $quantity);
                        }
                        $this->db->update('products', array('cost' => $data['cost']), array('id' => $product_id));
                        break;
                    } else {
                        $balance_quantity = $data['quantity'] + $data['out_quantity'];
                        $item_cost[] = array('cost' => $data['cost'], 'quantity' => $balance_quantity);
                        $quantity = $quantity - $balance_quantity;
                    }
                }
            }
            return (isset($item_cost) ? $item_cost : FALSE);
        }
        return FALSE;
    }

    public function getLifoCost($product_id = false, $quantity = false, $tmp_stockmoves = false, $transaction = false, $transaction_id = false)
    {
        $this->db->select('product_id, quantity, real_unit_cost, transaction');
        $this->db->order_by('date', 'desc');
        $this->db->order_by('id', 'desc');
        if ($transaction && $transaction_id) {
            $this->db->where("NOT (`transaction_id` = ".$transaction_id." AND `transaction` = '".$transaction."')");
        }
        $q = $this->db->get_where('stock_movement', array('product_id' => $product_id));
        if ($tmp_stockmoves) {
            $array_result = array_merge($q->result_array(), $tmp_stockmoves);
        } else {
            $array_result = $q->result_array();
        }
        $stock_ins = array();
        $stock_out = array();
        $total_qty = 0;
        if ($q->num_rows() > 0) {
            foreach ($array_result as $row) {
                if ($row['product_id'] == $product_id) {
                    $total_qty += $row['quantity'];
                    $cost = $row['real_unit_cost'] - 0;
                    if ($row['quantity'] < 0 ) {
                        $total_deduct = (isset($stock_out[$cost]) ? $stock_out[$cost] : 0) +  $row['quantity'];
                        $stock_out[$cost] = $total_deduct - 0;
                    } else {
                        $stock_ins[] = array('cost' => $cost, 'quantity' => ($row['quantity'])-0);
                    }
                }
            }
            if (!empty($stock_ins)) {
                foreach ($stock_ins as $stock_in) {
                    if (isset($stock_out[$stock_in['cost']]) && abs($stock_out[$stock_in['cost']]-0) > $stock_in['quantity']) {
                        $stock_in['out_quantity'] = ($stock_in['quantity'] * -1);
                        $stock_out[$stock_in['cost']] = $stock_in['quantity'] + $stock_out[$stock_in['cost']];
                    } else {
                        $stock_in['out_quantity'] = (isset($stock_out[$stock_in['cost']]) ? $stock_out[$stock_in['cost']] : 0) - 0;
                        $stock_out[$stock_in['cost']] = 0 ;
                    }
                    if ($stock_in['quantity'] > abs($stock_in['out_quantity'])) {
                        $datas[] = $stock_in;
                    }
                }
            }
            if (!empty($datas)) {
                foreach ($datas as $data) {
                    $out_quantity = abs($data['out_quantity']) + $quantity;
                    if ($data['quantity'] > $out_quantity) {
                        if ($quantity > 0) {
                            $item_cost[] = array('cost' => $data['cost'], 'quantity' => $quantity);
                        }
                        $this->db->update('products', array('cost' => $data['cost']), array('id' => $product_id));
                        break;
                    } else {
                        $balance_quantity = $data['quantity'] + $data['out_quantity'];
                        $item_cost[] = array('cost' => $data['cost'],'quantity' => $balance_quantity);
                        $quantity = $quantity - $balance_quantity;
                    }
                }
            }
            return (isset($item_cost) ? $item_cost : FALSE);
        }
        return FALSE;
    }

    public function updateAVGCost($product_id = false, $transaction = false, $transaction_id = false)
    {
        $data = false;
        if ($this->Settings->accounting == 1) {
            $productAcc = $this->getProductAccByProductId($product_id);
            if ($transaction=="Purchases") {
                $data = $this->getPurchaseByID($transaction_id);
            } else if ($transaction=="Receives") {
                $data = $this->getReceiveItemByID($transaction_id);
            } else if ($transaction=="OpeningBalance") {
                $data = $this->getOpeningBalanceByID($transaction_id);
            } else if ($transaction=="Convert") {
                $data = $this->getConvertByID($transaction_id);
            } else if ($transaction=="Pawns") {
                $data = $this->getPurchasePawnByID($transaction_id);
            }
        }
        $this->db->select('id, product_code, quantity, real_unit_cost, transaction, transaction_id, date, reference_no');
        $this->db->order_by('date', 'asc');
        $this->db->order_by('quantity', 'desc');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where("stock_movement", array('product_id' => $product_id));
        if ($q->num_rows() > 0 && $this->Settings->update_cost) {
            $average_cost = 0;
            $old_qty      = 0;
            $old_cost     = 0;
            foreach (($q->result_array()) as $row) {
                if ($row['quantity'] > 0) {
                    $new_cost  = $row['real_unit_cost'];
                    $new_qty   = $row['quantity'];
                    $total_qty = $new_qty + $old_qty;
                    if ($old_qty < 0) {                       
                        if ($total_qty > 0) {
                            $old_cost = $new_cost;
                        }
                    } else {
                        $total_old_cost = $old_qty * $old_cost;
                        $total_new_cost = $new_qty * $new_cost; 
                        $total_cost = $total_old_cost + $total_new_cost;
                        if ($total_cost != 0) {
                            $old_cost = $total_cost / $total_qty;
                        } else {
                            $old_cost = 0;
                        }
                    }
                } else {
                    if ($row["transaction"] == "Sale" && $row["real_unit_cost"] != $old_cost) {
                        $this->db->update("stock_movement", array("real_unit_cost" => $old_cost), array("id" => $row["id"]));
                        $amount    = $row["quantity"] * $old_cost;
                        $narrative = 'Product Code: ' . $row["product_code"] . '#' . 'Qty: ' . abs($row["quantity"]) . '#' . 'Cost: ' . $old_cost;
                        $this->db->where("tran_type", $row["transaction"])
                            ->where("tran_no", $row["transaction_id"])
                            ->where("account_code", (isset($productAcc->stock_acc) ? $productAcc->stock_acc : $this->accounting_setting->default_stock))
                            ->where("narrative LIKE '%Product Code: " . $row["product_code"] . "#Qty: " . abs($row["quantity"]) . "%'")
                            ->update("gl_trans", array("amount" => $amount, "narrative" => $narrative));
                        $this->db->where("tran_type", $row["transaction"])
                            ->where("tran_no", $row["transaction_id"])
                            ->where("account_code", (isset($productAcc->cost_acc) ? $productAcc->cost_acc : $this->accounting_setting->default_cost))
                            ->where("narrative LIKE '%Product Code: " . $row["product_code"] . "#Qty: " . abs($row["quantity"]) . "%'")
                            ->update("gl_trans", array("amount" => $amount * (-1), "narrative" => $narrative));
                        $this->db->where("sale_id", $row["transaction_id"])
                            ->where("product_id", $product_id)
                            ->where("quantity", $row["quantity"] * (-1))
                            ->update("sale_items", array("cost" => $old_cost));
                    }
                }
                $old_qty += $row['quantity'];
            }
            if ($old_cost > 0) {
                $average_cost = $old_cost;
            } else {
                $average_cost = 0;
            }
            $this->db->update('products', array('cost' => $average_cost), array('id' => $product_id));
            return $average_cost;
        }
    }

    public function updateFifoCost($product_id = false) 
    {
        if ($this->getProductByID($product_id)) {
            $this->db->select('quantity, real_unit_cost, transaction, date');
            $this->db->order_by('date', 'asc');
            $this->db->order_by('id', 'asc');
            $q = $this->db->get_where("stock_movement", array('product_id' => $product_id));
            $stock_ins = array();
            $stock_out = array();
            $total_qty = 0;
            if ($q->num_rows() > 0 && $this->Settings->update_cost) {
                foreach (($q->result_array()) as $row) {
                    $total_qty += $row['quantity'];
                    $cost = $row['real_unit_cost'] - 0;
                    if ($row['quantity'] < 0 ) {
                        $total_deduct = (isset($stock_out[$cost]) ? $stock_out[$cost] : 0) +  $row['quantity'];
                        $stock_out[$cost] = $total_deduct - 0;
                    } else {
                        $stock_ins[] = array('cost' => $cost, 'quantity' => ($row['quantity']) - 0);
                    }
                }
                foreach ($stock_ins as $stock_in) {
                    if (isset($stock_out[$stock_in['cost']]) && abs($stock_out[$stock_in['cost']] - 0) > $stock_in['quantity']) {
                        $stock_in['out_quantity'] = ($stock_in['quantity'] * -1);
                        $stock_out[$stock_in['cost']] = $stock_in['quantity'] + $stock_out[$stock_in['cost']];
                    } else {
                        $stock_in['out_quantity'] = (isset($stock_out[$stock_in['cost']]) ? $stock_out[$stock_in['cost']] : 0) - 0;
                        $stock_out[$stock_in['cost']] = 0 ;
                    }
                    if ($stock_in['quantity'] > abs($stock_in['out_quantity'])) {
                        $this->db->update('products', array('cost' => $stock_in['cost']), array('id' => $product_id));
                        return $stock_in['cost'];
                        break;
                    }
                }
            }
            return FALSE;
        }
        return FALSE;
    }
    
    public function updateLifoCost($product_id = false) 
    {
        if ($this->getProductByID($product_id)) {
            $this->db->select('quantity,real_unit_cost,transaction,date');
            $this->db->order_by('date', 'desc');
            $this->db->order_by('id', 'desc');
            $q = $this->db->get_where("stock_movement", array('product_id' => $product_id));
            $stock_ins = array();
            $stock_out = array();
            $total_qty = 0;
            if ($q->num_rows() > 0 && $this->Settings->update_cost) {
                foreach (($q->result_array()) as $row) {
                    $total_qty += $row['quantity'];
                    $cost = $row['real_unit_cost'] - 0;
                    if ($row['quantity'] < 0 ) {
                        $total_deduct = (isset($stock_out[$cost]) ? $stock_out[$cost] : 0) +  $row['quantity'];
                        $stock_out[$cost] = $total_deduct - 0;
                    } else {
                        $stock_ins[] = array('cost' => $cost, 'quantity' => ($row['quantity']) - 0);
                    }
                }
                foreach ($stock_ins as $stock_in) {
                    if (isset($stock_out[$stock_in['cost']]) && abs($stock_out[$stock_in['cost']]-0) > $stock_in['quantity']) { 
                        $stock_in['out_quantity'] = ($stock_in['quantity'] * -1);
                        $stock_out[$stock_in['cost']] = $stock_in['quantity'] + $stock_out[$stock_in['cost']];
                    } else {
                        $stock_in['out_quantity'] = (isset($stock_out[$stock_in['cost']]) ? $stock_out[$stock_in['cost']] : 0) - 0;
                        $stock_out[$stock_in['cost']] = 0 ;
                    }
                    if ($stock_in['quantity'] > abs($stock_in['out_quantity'])) {
                        $this->db->update('products', array('cost' => $stock_in['cost']), array('id' => $product_id));
                        return $stock_in['cost'];
                        break;
                    }
                }
            }
            return FALSE;
        }
        return FALSE;
    }

    public function getReceiveItemByID($id = false)
    {
        $this->db->select("biller_id, project_id, re_reference_no as reference_no");
        $q = $this->db->get_where('stock_received', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getOpeningBalanceByID($id = false)
    {
        $q = $this->db->get_where('inventory_opening_balances', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getConvertByID($id = false)
    {
        $q = $this->db->get_where('converts', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchasePawnByID($id = false)
    {
        $q = $this->db->get_where('pawn_purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteStockmoves($transaction = false, $transaction_id = false) 
    {
        $this->db->delete('stock_movement', array('transaction' => $transaction, 'transaction_id' => $transaction_id));
    }

    public function getRefPurchases($receive_status = false)
    {
        $this->db->select('id,reference_no');
        if($receive_status){
            $this->db->where('purchases.status !=',$receive_status);
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('purchases.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where('purchases.biller_id',$this->session->userdata('biller_id'));
        }
        $this->db->where('purchases.status !=', 'returned');
        $this->db->where('status !=', 'draft');
        $this->db->where('status !=', 'freight');
        $this->db->order_by('id','desc');
        $q = $this->db->get('purchases');
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductOptions($product_id, $warehouse_id, $zero_check = true)
    {
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.cost as cost, product_variants.price as price, product_variants.quantity as total_quantity, warehouses_products_variants.quantity as quantity')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->where('warehouses_products_variants.warehouse_id', $warehouse_id)
            ->group_by('product_variants.id');
        if ($zero_check) {
            $this->db->where('warehouses_products_variants.quantity >', 0);
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

    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStockMovementByProductID_13_12_2023($product_id, $warehouse_id, $option_id = null, $expiry = null, $transaction = false, $transaction_id = false)
    {
        $orderby = empty($this->Settings->accounting_method) ? 'asc' : 'desc';
        if ($this->Settings->product_expiry && $this->Settings->fefo) {
            $this->db->order_by('expiry', 'asc');
        } else {
            $this->db->order_by('date', $orderby);
            $this->db->order_by('id', $orderby);
        }
        $this->db->select('id, product_id, SUM(quantity) as quantity, SUM(quantity) as quantity_balance, expiry');
        if ($product_id) {
            $this->db->where('product_id', $product_id);
        }
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($expiry != '' && $expiry != 'null' && $expiry != '0000-00-00' && $expiry != NULL) {
            $this->db->where('expiry', $expiry);
        }   
        if ($option_id) {
            $this->db->where('option_id', $option_id);
        }
        if ($transaction && $transaction_id) {
            $this->db->where('(transaction != "'.$transaction.'" OR (transaction = "'.$transaction.'" AND transaction_id != '.$transaction_id.'))');
        }
        $this->db->group_by("expiry");
        $q = $this->db->get('stock_movement');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStockMovementByProductID($product_id, $warehouse_id, $option_id = null, $expiry = null, $transaction = false, $transaction_id = false)
    {
        $orderby = empty($this->Settings->accounting_method) ? 'asc' : 'desc';
        if ($this->Settings->product_expiry && $this->Settings->fefo) {
            $this->db->order_by('stock_movement.expiry', 'asc');
        } else {
            $this->db->order_by('stock_movement.date', $orderby);
            $this->db->order_by('stock_movement.id', $orderby);
        }
        $this->db->select("
            {$this->db->dbprefix('stock_movement')}.id, 
            {$this->db->dbprefix('stock_movement')}.product_id, 
            SUM({$this->db->dbprefix('stock_movement')}.quantity) as quantity, 
            SUM({$this->db->dbprefix('stock_movement')}.quantity) as quantity_balance, 
            {$this->db->dbprefix('stock_movement')}.expiry
        ");
        if ($product_id) {
            $this->db->where('stock_movement.product_id', $product_id);
        }
        if ($warehouse_id) {
            $this->db->where('stock_movement.warehouse_id', $warehouse_id);
        }
        if ($expiry != '' && $expiry != 'null' && $expiry != '0000-00-00' && $expiry != NULL) {
            $this->db->where('stock_movement.expiry', $expiry);
        }   
        if ($option_id) {
            $this->db->where('stock_movement.option_id', $option_id);
        }
        if ($transaction && $transaction_id) {
            $this->db->where('(stock_movement.transaction != "'.$transaction.'" OR (stock_movement.transaction = "'.$transaction.'" AND stock_movement.transaction_id != '.$transaction_id.'))');
        }
        $this->db->where('products.type !=', 'service');
        $this->db->join('products', 'products.id = stock_movement.product_id');
        $this->db->group_by("stock_movement.expiry");
        $q = $this->db->get('stock_movement');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStockMovement_ProductBalanceQuantity($product_id = false, $warehouse_id = false, $option_id = false, $transaction = false, $transaction_id = false)
    {
        if ($product_id) {
            $this->db->where('product_id', $product_id);
        }
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($option_id) {
            $this->db->where('option_id', $option_id);
        }
        if ($transaction && $transaction_id) {
            $this->db->where('(transaction != "'.$transaction.'" OR (transaction = "'.$transaction.'" AND transaction_id != '.$transaction_id.'))');
        }
        $this->db->select("sum(quantity) as quantity_balance");
        $q = $this->db->get('stock_movement');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getStockMovement_ProductQty($product_id = false, $warehouse_id = false, $option_id = false, $expiry = false, $transaction = false, $transaction_id = false)
    {
        if ($product_id) {
            $this->db->where('stock_movement.product_id', $product_id);
        }
        if ($warehouse_id) {
            $this->db->where('stock_movement.warehouse_id', $warehouse_id);
        }
        if ($option_id) {
            $this->db->where('stock_movement.option_id', $option_id);
        } else {
            $this->db->where('stock_movement.option_id', null);
        }
        if ($expiry != '' && $expiry != 'null' && $expiry != '0000-00-00') {
            $this->db->where('stock_movement.expiry', $expiry);
        } else {
            $this->db->where('stock_movement.expiry', null);
        }
        if ($transaction && $transaction_id) {
            $this->db->where('
                ('.$this->db->dbprefix('stock_movement').'.transaction != "'.$transaction.'" OR 
                ('.$this->db->dbprefix('stock_movement').'.transaction = "'.$transaction.'" AND '.$this->db->dbprefix('stock_movement').'.transaction_id != '.$transaction_id.'))
            ');
        }
        $this->db->where(" {$this->db->dbprefix('products')}.type != 'service' && {$this->db->dbprefix('products')}.type != 'combo' && {$this->db->dbprefix('products')}.type != 'bom'");
        $this->db->select(" SUM({$this->db->dbprefix('stock_movement')}.quantity) as quantity_balance ");
        $this->db->join('products', 'products.id = stock_movement.product_id');
        $q = $this->db->get('stock_movement');
        if ($q->num_rows() > 0) {
            return $q->row()->quantity_balance;
        }
        return 0;
    }

    public function getStockMovement_ExpiryQuantityByProduct($product_id = false)
    {
        if ($product_id) {
            $this->db->select('product_id, expiry, sum('.$this->db->dbprefix("stock_movement").'.quantity) as quantity, warehouses.name as warehouse_name');
            if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
                $this->db->where_in('stock_movement.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
            }
            $this->db->join('warehouses','warehouses.id = stock_movement.warehouse_id','inner');
            $this->db->where('product_id', $product_id);
            // $this->db->where('expiry !=','0000-00-00');
            // $this->db->where('IFNULL(expiry,"") !=',"");
            $this->db->group_by('product_id, warehouse_id, expiry');
            $this->db->order_by('warehouse_id, expiry');
            $q = $this->db->get('stock_movement');
            if ($q->num_rows() > 0) {
                foreach ($q->result() as $row) {
                    $data[] = $row;
                }
                return $data;
            }
        }
        return false;
    }

    public function stockMovement_isOverselling($data)
    {
        $this->checkStockMovement_DataFormat();
        $data = (object) $data;
        $warehouse = $this->getWarehouseByID($data->warehouse_id);
        $product   = $this->getProductByID($data->product_id);
        if ((!$this->Settings->overselling || ($this->Settings->overselling && !$warehouse->overselling)) && $product->type != 'service') {
            $this->db->select(' SUM(COALESCE(quantity, 0)) AS quantity, SUM(COALESCE(weight, 0)) AS weight');
            if (isset($data->option_id) && !empty($data->option_id)) {
                $this->db->where('option_id', $data->option_id);
            } else {
                $this->db->where('option_id', null);
            }
            if (isset($data->serial_no) && !empty($data->serial_no)) {
                $this->db->where('serial_no', $data->serial_no);
            } else {
                $this->db->where('serial_no', null);
            }
            if (isset($data->expiry) && !empty($data->expiry)) {
                $this->db->where('expiry', $data->expiry);
            } else {
                $this->db->where('expiry', null);
            }
            $this->db->where('warehouse_id', $data->warehouse_id);
            $this->db->where('product_id', $data->product_id);
            $this->db->limit(1);
            $q = $this->db->get('stock_movement');
            if ($q->num_rows() > 0) {
                if ($q->row()->quantity < 0) {
                    $this->session->set_flashdata('error', sprintf(lang('quantity_out_of_stock_for_%s'), $product->name));
                    return true;
                }
            }
        }
        return false;
    }

    public function checkStockMovement_DataFormat()
    {
        $this->db->where("(option_id = '' OR option_id = 0)")->update("stock_movement", array("option_id" => null));
        $this->db->where("(serial_no = '' OR serial_no = 'NULL' OR serial_no = 'null')")->update("stock_movement", array("serial_no" => null));
        // $this->db->where("(expiry = '' OR expiry = '0000-00-00')")->update("stock_movement", array("expiry" => null));
    }

    public function getProductRacks($biller_id = null)
    {
        if ($biller_id) {
            $this->db->where('biller_id', $biller_id);
        }
        $q = $this->db->get('product_rack');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getWarehouseProductRacks($product_id)
    {
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function setWarehouseProductRack($warehouse_id, $product_id, $rack_id) 
    {
        if ($whp = $this->getWarehouseProduct($warehouse_id, $product_id)) {
            $this->db->update('warehouses_products', ['rack_id' => $rack_id], ['id' => $whp->id]);
        } else {
            $this->db->insert('warehouses_products', ['warehouse_id' => $warehouse_id, 'product_id' => $product_id, 'rack_id' => $rack_id]);
        }
        return true;
    }
    public function getTanks()
    {
        $q = $this->db->get("tanks");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getStockmoves($product_id = false, $warehouse_id = false, $option_id = false, $transaction = false, $transaction_id = false)
    {
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->select('id, quantity, quantity as quantity_balance');
        
        if($product_id){
            $this->db->where("product_id",$product_id);
        }
        if($warehouse_id){
            $this->db->where("warehouse_id",$warehouse_id);
        }
        if ($option_id) {
            $this->db->where('option_id', $option_id);
        }
        if($transaction && $transaction_id){
            $this->db->where('(transaction != "'.$transaction.'" OR (transaction = "'.$transaction.'" AND transaction_id != '.$transaction_id.'))');
        }
        $this->db->group_by('id');
        $this->db->order_by('date', $orderby);
        $q = $this->db->get('stock_movement');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
}