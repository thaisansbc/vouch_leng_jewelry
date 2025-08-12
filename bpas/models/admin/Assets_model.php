<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Assets_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function addExpense($data, $accTrans = array()){

        foreach ($data as $item) {
            $this->db->insert('expenses', $item);
            //========Add Accounting=========//
            $expense_id = $this->db->insert_id();
            //========End Accounting=========//
 
            $this->syncAssetQty($item['product_id'],$item['warehouse_id'],$item['qty']);
        }

        if ($accTrans) {
            foreach ($accTrans as $accTran) {
                $accTran['tran_no'] = $expense_id;
                $this->db->insert('gl_trans', $accTran);
            }
        }
        $this->site->updateReference('ex');
        return true;
    }
    public function syncAssetQty($product_id, $warehouse_id,$new_qty){

 
        $product = $this->getAssetsByID($product_id);

        $wh_balance_qty = $new_qty;
        
        if ($this->db->update('assets', ['quantity' => $wh_balance_qty +$product->quantity], ['id' => $product_id])) {
            
            if ($this->site->getWarehouseProducts($product_id, $warehouse_id)) {
                $this->db->update('warehouses_products', ['quantity' => $wh_balance_qty], ['product_id' => $product_id, 'warehouse_id' => $warehouse_id]);
            } else {
                if (!$wh_balance_qty) {
                    $wh_balance_qty = 0;
                }
                
                $this->db->insert('warehouses_products', ['quantity' => $wh_balance_qty, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'avg_cost' => 0]);
               
            }

            return true;
        }
        return false;
    }
    public function updateExpense__($id, $data, $accTrans = array())
    {
        foreach ($data as $item) {
            $this->db->insert('expenses', $item);
            $expense_id = $this->db->insert_id();
        }
        if($accTrans) {
        foreach ($accTrans as $accTran) {
        $accTran['tran_no'] = $expense_id;
                $this->db->insert('gl_trans', $accTran); }
        }
        return true;
    }

    public function updateExpense($id, $data,$accTrans = array())
    {
    
        if ($this->db->update('expenses', $data, ['id' => $id])) {
            //==========Add Accounting =======//
            $this->site->deleteAccTran('ExpenseAsset',$id);
            $this->deleteDPByPurchaseID($id);
            if($accTrans){
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            //==========End Accounting =======//
            return true;
        }
        return false;
    }
    public function add_products($products = [])
    {
        if (!empty($products)) {
            foreach ($products as $product) {
                $variants = explode('|', $product['variants']);
                unset($product['variants']);
                if ($this->db->insert('assets', $product)) {
                    $product_id = $this->db->insert_id();
                    foreach ($variants as $variant) {
                        if ($variant && trim($variant) != '') {
                            $vat = ['product_id' => $product_id, 'name' => trim($variant)];
                            $this->db->insert('product_variants', $vat);
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function addAdjustment($data, $products)
    {
        if ($this->db->insert('adjustments', $data)) {
            $adjustment_id = $this->db->insert_id();
            foreach ($products as $product) {
                $product['adjustment_id'] = $adjustment_id;
                $this->db->insert('adjustment_items', $product);
                $this->syncAdjustment($product);
                $this->db->set('status', 1);
                $this->db->where(  array('stock_count_id' => $data['count_id'],
                        'product_id'=> $product['product_id'])
                );
                $query = $this->db->update('stock_count_items');
            }
            if ($this->site->getReference('qa') == $data['reference_no']) {
                $this->site->updateReference('qa');
            }
            return true;
        }
        return false;
    }
    public function getStockCountSomeItems($stock_count_id, $id)
    {
        $q = $this->db->get_where('stock_count_items', ['counted >' => 0, 'stock_count_id' => $stock_count_id, 'id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return null;
    }
    public function addAjaxProduct($data)
    {
        if ($this->db->insert('assets', $data)) {
            $product_id = $this->db->insert_id();
            return $this->getProductByID($product_id);
        }
        return false;
    }

    public function addProduct($data, $items, $warehouse_qty, $product_attributes, $photos)
    {
        if ($this->db->insert('assets', $data)) {
            $product_id = $this->db->insert_id();
            $this->site->updateReference('inventory');
            if ($items) {
                foreach ($items as $item) {
                    $item['product_id'] = $product_id;
                    $this->db->insert('combo_items', $item);
                }
            }

            $warehouses = $this->site->getAllWarehouses();
            if ($data['type'] != 'standard') {
                foreach ($warehouses as $warehouse) {
                    $this->db->insert('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0]);
                }
            }

            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);

            if ($warehouse_qty && !empty($warehouse_qty)) {
                foreach ($warehouse_qty as $wh_qty) {
                    if (isset($wh_qty['quantity']) && !empty($wh_qty['quantity'])) {
                        $this->db->insert('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $wh_qty['warehouse_id'], 'quantity' => $wh_qty['quantity'], 'rack' => $wh_qty['rack'], 'avg_cost' => $data['cost']]);

                        if (!$product_attributes) {
                            $tax_rate_id = $tax_rate ? $tax_rate->id : null;
                            $tax         = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . '%' : $tax_rate->rate) : null;
                            $unit_cost   = $data['cost'];
                            if ($tax_rate) {
                                if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                    if ($data['tax_method'] == '0') {
                                        $pr_tax_val    = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                        $net_item_cost = $data['cost'] - $pr_tax_val;
                                        $item_tax      = $pr_tax_val * $wh_qty['quantity'];
                                    } else {
                                        $net_item_cost = $data['cost'];
                                        $pr_tax_val    = ($data['cost'] * $tax_rate->rate) / 100;
                                        $unit_cost     = $data['cost'] + $pr_tax_val;
                                        $item_tax      = $pr_tax_val * $wh_qty['quantity'];
                                    }
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $item_tax      = $tax_rate->rate;
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax      = 0;
                            }

                            $subtotal = (($net_item_cost * $wh_qty['quantity']) + $item_tax);

                            $item = [
                                'product_id'        => $product_id,
                                'product_code'      => $data['code'],
                                'product_name'      => $data['name'],
                                'net_unit_cost'     => $net_item_cost,
                                'unit_cost'         => $unit_cost,
                                'real_unit_cost'    => $unit_cost,
                                'quantity'          => $wh_qty['quantity'],
                                'quantity_balance'  => $wh_qty['quantity'],
                                'quantity_received' => $wh_qty['quantity'],
                                'item_tax'          => $item_tax,
                                'tax_rate_id'       => $tax_rate_id,
                                'tax'               => $tax,
                                'subtotal'          => $subtotal,
                                'warehouse_id'      => $wh_qty['warehouse_id'],
                                'date'              => date('Y-m-d'),
                                'status'            => 'received',
                            ];
                            $this->db->insert('purchase_items', $item);
                            $this->site->syncProductQty($product_id, $wh_qty['warehouse_id']);
                        }
                    }
                }
            }

            if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {
                    $pr_attr_details = $this->getPrductVariantByPIDandName($product_id, $pr_attr['name']);

                    $pr_attr['product_id'] = $product_id;
                    $variant_warehouse_id  = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    if ($pr_attr_details) {
                        $option_id = $pr_attr_details->id;
                    } else {
                        $this->db->insert('product_variants', $pr_attr);
                        $option_id = $this->db->insert_id();
                    }
                    if ($pr_attr['quantity'] != 0) {
                        $this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']]);

                        $tax_rate_id = $tax_rate ? $tax_rate->id : null;
                        $tax         = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . '%' : $tax_rate->rate) : null;
                        $unit_cost   = $data['cost'];
                        if ($tax_rate) {
                            if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                if ($data['tax_method'] == '0') {
                                    $pr_tax_val    = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                    $net_item_cost = $data['cost'] - $pr_tax_val;
                                    $item_tax      = $pr_tax_val * $pr_attr['quantity'];
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $pr_tax_val    = ($data['cost'] * $tax_rate->rate) / 100;
                                    $unit_cost     = $data['cost'] + $pr_tax_val;
                                    $item_tax      = $pr_tax_val * $pr_attr['quantity'];
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax      = $tax_rate->rate;
                            }
                        } else {
                            $net_item_cost = $data['cost'];
                            $item_tax      = 0;
                        }

                        $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
                        $item     = [
                            'product_id'        => $product_id,
                            'product_code'      => $data['code'],
                            'product_name'      => $data['name'],
                            'net_unit_cost'     => $net_item_cost,
                            'unit_cost'         => $unit_cost,
                            'quantity'          => $pr_attr['quantity'],
                            'option_id'         => $option_id,
                            'quantity_balance'  => $pr_attr['quantity'],
                            'quantity_received' => $pr_attr['quantity'],
                            'item_tax'          => $item_tax,
                            'tax_rate_id'       => $tax_rate_id,
                            'tax'               => $tax,
                            'subtotal'          => $subtotal,
                            'warehouse_id'      => $variant_warehouse_id,
                            'date'              => date('Y-m-d'),
                            'status'            => 'received',
                        ];
                        $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                        $this->db->insert('purchase_items', $item);
                    }

                    foreach ($warehouses as $warehouse) {
                        if (!$this->getWarehouseProductVariant($warehouse->id, $product_id, $option_id)) {
                            $this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0]);
                        }
                    }

                    $this->site->syncVariantQty($option_id, $variant_warehouse_id);
                }
            }

            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', ['product_id' => $product_id, 'photo' => $photo]);
                }
            }

            $this->site->syncQuantity(null, null, null, $product_id);
            return true;
        }
        return false;
    }
    public function addProperty($data, $items, $warehouse_qty, $product_attributes, $photos)
    {
        if ($this->db->insert('assets', $data)) {
            $product_id = $this->db->insert_id();
            $this->site->updateReference('inventory');
            if ($items) {
                foreach ($items as $item) {
                    $item['product_id'] = $product_id;
                    $this->db->insert('combo_items', $item);
                }
            }

            if($addOn_items){
                foreach ($addOn_items as $item) {
                    $item['product_id'] = $product_id;
                    $this->db->insert('addon_items', $item);
                }
            }

            $warehouses = $this->site->getAllWarehouses();
            if ($data['type'] != 'standard') {
                foreach ($warehouses as $warehouse) {
                    $this->db->insert('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0]);
                }
            }

            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);

            if ($warehouse_qty && !empty($warehouse_qty)) {
                foreach ($warehouse_qty as $wh_qty) {
                    if (isset($wh_qty['quantity']) && !empty($wh_qty['quantity'])) {
                        $this->db->insert('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $wh_qty['warehouse_id'], 'quantity' => $wh_qty['quantity'], 'rack' => $wh_qty['rack'], 'avg_cost' => $data['cost']]);

                        if (!$product_attributes) {
                            $tax_rate_id = $tax_rate ? $tax_rate->id : null;
                            $tax         = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . '%' : $tax_rate->rate) : null;
                            $unit_cost   = $data['cost'];
                            if ($tax_rate) {
                                if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                    if ($data['tax_method'] == '0') {
                                        $pr_tax_val    = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                        $net_item_cost = $data['cost'] - $pr_tax_val;
                                        $item_tax      = $pr_tax_val * $wh_qty['quantity'];
                                    } else {
                                        $net_item_cost = $data['cost'];
                                        $pr_tax_val    = ($data['cost'] * $tax_rate->rate) / 100;
                                        $unit_cost     = $data['cost'] + $pr_tax_val;
                                        $item_tax      = $pr_tax_val * $wh_qty['quantity'];
                                    }
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $item_tax      = $tax_rate->rate;
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax      = 0;
                            }

                            $subtotal = (($net_item_cost * $wh_qty['quantity']) + $item_tax);

                            $item = [
                                'product_id'        => $product_id,
                                'product_code'      => $data['code'],
                                'product_name'      => $data['name'],
                                'net_unit_cost'     => $net_item_cost,
                                'unit_cost'         => $unit_cost,
                                'real_unit_cost'    => $unit_cost,
                                'quantity'          => $wh_qty['quantity'],
                                'quantity_balance'  => $wh_qty['quantity'],
                                'quantity_received' => $wh_qty['quantity'],
                                'item_tax'          => $item_tax,
                                'tax_rate_id'       => $tax_rate_id,
                                'tax'               => $tax,
                                'subtotal'          => $subtotal,
                                'warehouse_id'      => $wh_qty['warehouse_id'],
                                'date'              => date('Y-m-d'),
                                'status'            => 'received',
                            ];
                            $this->db->insert('purchase_items', $item);
                            $this->site->syncProductQty($product_id, $wh_qty['warehouse_id']);
                        }
                    }
                }
            }

            if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {
                    $pr_attr_details = $this->getPrductVariantByPIDandName($product_id, $pr_attr['name']);

                    $pr_attr['product_id'] = $product_id;
                    $variant_warehouse_id  = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    if ($pr_attr_details) {
                        $option_id = $pr_attr_details->id;
                    } else {
                        $this->db->insert('product_variants', $pr_attr);
                        $option_id = $this->db->insert_id();
                    }
                    if ($pr_attr['quantity'] != 0) {
                        $this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']]);

                        $tax_rate_id = $tax_rate ? $tax_rate->id : null;
                        $tax         = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . '%' : $tax_rate->rate) : null;
                        $unit_cost   = $data['cost'];
                        if ($tax_rate) {
                            if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                if ($data['tax_method'] == '0') {
                                    $pr_tax_val    = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                    $net_item_cost = $data['cost'] - $pr_tax_val;
                                    $item_tax      = $pr_tax_val * $pr_attr['quantity'];
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $pr_tax_val    = ($data['cost'] * $tax_rate->rate) / 100;
                                    $unit_cost     = $data['cost'] + $pr_tax_val;
                                    $item_tax      = $pr_tax_val * $pr_attr['quantity'];
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax      = $tax_rate->rate;
                            }
                        } else {
                            $net_item_cost = $data['cost'];
                            $item_tax      = 0;
                        }

                        $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
                        $item     = [
                            'product_id'        => $product_id,
                            'product_code'      => $data['code'],
                            'product_name'      => $data['name'],
                            'net_unit_cost'     => $net_item_cost,
                            'unit_cost'         => $unit_cost,
                            'quantity'          => $pr_attr['quantity'],
                            'option_id'         => $option_id,
                            'quantity_balance'  => $pr_attr['quantity'],
                            'quantity_received' => $pr_attr['quantity'],
                            'item_tax'          => $item_tax,
                            'tax_rate_id'       => $tax_rate_id,
                            'tax'               => $tax,
                            'subtotal'          => $subtotal,
                            'warehouse_id'      => $variant_warehouse_id,
                            'date'              => date('Y-m-d'),
                            'status'            => 'received',
                        ];
                        $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                        $this->db->insert('purchase_items', $item);
                    }

                    foreach ($warehouses as $warehouse) {
                        if (!$this->getWarehouseProductVariant($warehouse->id, $product_id, $option_id)) {
                            $this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0]);
                        }
                    }

                    $this->site->syncVariantQty($option_id, $variant_warehouse_id);
                }
            }

            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', ['product_id' => $product_id, 'photo' => $photo]);
                }
            }

            $this->site->syncQuantity(null, null, null, $product_id);
            return true;
        }
        return false;
    }
    public function addQuantity($product_id, $warehouse_id, $quantity, $rack = null)
    {
        if ($this->getProductQuantity($product_id, $warehouse_id)) {
            if ($this->updateQuantity($product_id, $warehouse_id, $quantity, $rack)) {
                return true;
            }
        } else {
            if ($this->insertQuantity($product_id, $warehouse_id, $quantity, $rack)) {
                return true;
            }
        }

        return false;
    }

    public function addStockCount($data,$items)
    {
        $this->db->insert('stock_counts', $data);
        $id = $this->db->insert_id();

        if ($id != 0 and !empty($items)) {
            foreach ($items as $item) {
                $stock[] = array(
                    'stock_count_id' => $id,
                    'product_id' => $item['product_id'],
                    'product_code' => $item['product_code'],
                    'product_name' => $item['product_name'],
                    'product_variant ' => $item['variant'],
                    'expected ' => $item['expected'],
                    'counted ' => $item['counted'],
                    'cost' => $item['cost'],
                ); 
            }
            $query = $this->db->insert_batch( 'stock_count_items', $stock);
        }
        if ($query) {
            return true;
        }
        return false;
    }

    public function deleteAdjustment($id)
    {
        $this->reverseAdjustment($id);
        if ($this->db->delete('adjustments', ['id' => $id]) && $this->db->delete('adjustment_items', ['adjustment_id' => $id])) {
            return true;
        }
        return false;
    }
    public function unBookingProduct($id)
    {   
        if ($this->db->update('assets',['quantity' => 1], ['id' => $id])){
          return true;
        }
        return false;
    }
    public function realiseProduct($id)
    {
        if ($this->db->update('assets',['quantity' => 1], ['id' => $id])){
             $q = $this->db->get_where('blocking', ['product_id' => $id], 1);
            if ($q->num_rows() > 0) {
                return $q->row();
            }
            return false;
           
        }
        return false;
    }
    public function deleteProduct($id)
    {
        if ($this->db->delete('products', ['asset'=>1,'id' => $id]) && $this->db->delete('warehouses_products', ['product_id' => $id])) {
            $this->db->delete('warehouses_products_variants', ['product_id' => $id]);
            $this->db->delete('product_variants', ['product_id' => $id]);
            $this->db->delete('product_photos', ['product_id' => $id]);
            $this->db->delete('product_prices', ['product_id' => $id]);
            $this->db->delete('asset_evaluation', ['product_id' => $id]);
            return true;
        }
        return false;
    }

    public function fetch_products($category_id, $limit, $start, $subcategory_id = null)
    {
        $this->db->limit($limit, $start);
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->order_by('id', 'asc');
        $query = $this->db->get('assets');

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function updateStockCount($id)
    {
        $status = $this->input->post('status');
        $date    = $this->bpas->fld($this->input->post('date'));
        if ($status == 1) {
            $this->db->set('finalized', 1);
            $this->db->set('status', 1);
            $this->db->set('date', $date);
            $this->db->where('id',$id);
            $query = $this->db->update('stock_counts');
        } else {
            $this->db->set('status', 0);
            $this->db->set('date', $date);
            $this->db->where('id',$id);
            $query = $this->db->update('stock_counts');
        }
        
        $product_id = $this->input->post('product');
        $counted    = $this->input->post('quantity');

        if ($query) {
            for ($i=0; $i < count($product_id); $i++) { 
                $this->db->set('date_time', $date);
                $this->db->set('counted', $counted[$i]);
                $this->db->where('stock_count_id',$id);
                $this->db->where('product_id',$product_id[$i]);
                $this->db->update('stock_count_items');
            }
            return true;
        }
        return false;
    }

    public function finalizeStockCount($id, $data, $products)
    {
        if ($this->db->update('stock_counts', $data, ['id' => $id])) {
            foreach ($products as $product) {
                $this->db->update('stock_count_items', $product, ['stock_count_id' => $id,'product_id' => $product['product_id']]);
            }
            return true;
        }
        return false;
    }

    public function getAdjustmentByCountID($count_id)
    {
        $q = $this->db->get_where('adjustments', ['count_id' => $count_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAdjustmentByID($id)
    {
        $q = $this->db->get_where('adjustments', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAdjustmentItems($adjustment_id)
    {
        $this->db->select('adjustment_items.*, products.code as product_code, products.name as product_name, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=adjustment_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=adjustment_items.option_id', 'left')
            ->group_by('adjustment_items.id')
            ->order_by('id', 'asc');

        $this->db->where('adjustment_id', $adjustment_id);

        $q = $this->db->get('adjustment_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllProducts()
    {
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllVariants()
    {
        $q = $this->db->get('variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllWarehousesWithPQ($product_id)
    {
        $this->db->select('' . $this->db->dbprefix('warehouses') . '.*, ' . $this->db->dbprefix('warehouses_products') . '.quantity, ' . $this->db->dbprefix('warehouses_products') . '.rack')
            ->join('warehouses_products', 'warehouses_products.warehouse_id=warehouses.id', 'left')
            ->where('warehouses_products.product_id', $product_id)
            ->group_by('warehouses.id');
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getBrandByName($name)
    {
        $q = $this->db->get_where('brands', ['name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getCategoryByCode($code)
    {
        $q = $this->db->get_where('categories', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCategoryProducts($category_id)
    {
        $q = $this->db->get_where('products', ['category_id' => $category_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPrductVariantByPIDandName($product_id, $name)
    {
        $q = $this->db->get_where('product_variants', ['product_id' => $product_id, 'name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByCategoryID($id)
    {
        $q = $this->db->get_where('products', ['category_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return true;
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
    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getAssetsByID($id)
    {
        $q = $this->db->get_where('products', ['type'=>'asset','id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductComboItems($pid)
    {
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.code as code, ' . $this->db->dbprefix('combo_items') . '.quantity as qty, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('combo_items') . '.unit_price as price')->join('products', 'products.code=combo_items.item_code', 'left')->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', ['product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }


    public function getProductDetail($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('tax_rates') . '.name as tax_rate_name, ' . $this->db->dbprefix('tax_rates') . '.code as tax_rate_code, c.code as category_code, sc.code as subcategory_code', false)
            ->join('tax_rates', 'tax_rates.id=products.tax_rate', 'left')
            ->join('categories c', 'c.id=products.category_id', 'left')
            ->join('categories sc', 'sc.id=products.subcategory_id', 'left');
        $q = $this->db->get_where('products', ['products.id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductDetails($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.code, ' . $this->db->dbprefix('products') . '.name, ' . $this->db->dbprefix('categories') . '.code as category_code, cost, price, quantity, alert_quantity')
            ->join('categories', 'categories.id=products.category_id', 'left');
        $q = $this->db->get_where('products', ['products.id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getAllBlock()
    {
       $q = $this->db->get('blocking');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllBooking()
    {
       $q = $this->db->get('booking');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
   public function getBooking($id)
    {
        $this->db->select("products.*,booking.create_by as booking_by,booking.note as description,booking.expiry_date as expiry,booking.current_date as booking_date, booking.customer as booker,booking.booking_price as booking_price" )
            ->join('booking', 'booking.product_id = products.id', 'left');
        $q = $this->db->get_where('products',['products.id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getBlocking($id)
    {
        $this->db->select("products.*,blocking.create_by as block_by,blocking.note as description,blocking.expiry_date as expiry,blocking.current_date as booking_date," )
            ->join('blocking', 'blocking.product_id = products.id', 'left');
        $q = $this->db->get_where('products',['products.id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getBookingByID($id)
    {
        $this->db->select("products.*,booking.create_by as booking_by,booking.note as description,booking.expiry_date as expiry,booking.current_date as booking_date, booking.customer as booker,booking.booking_price as booking_price" )
            ->join('products', 'products.id = booking.product_id', 'left');
        $q = $this->db->get_where('booking',['booking.id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getBookingByPID($id)
    {
        $this->db->select("products.*,booking.create_by as booking_by,booking.note as description,booking.note as sale_note,booking.expiry_date as expiry,booking.current_date as booking_date, companies.name as booker,projects.project_name as project_name,booking.booking_price as booking_price" )
            ->join('booking', 'products.id = booking.product_id', 'left')
            ->join('projects', 'projects.project_id = products.project_id', 'left')
        ->join('companies', 'companies.id = booking.customer', 'left');
        $q = $this->db->get_where('products',['products.id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getProductNames($term, $limit = 5)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price, ' . $this->db->dbprefix('product_variants') . '.name as vname')
            ->where("type != 'combo' AND "
                . '(' . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->join('product_variants', 'product_variants.product_id=products.id', 'left')
            ->where('' . $this->db->dbprefix('product_variants') . '.name', null)
            ->group_by('products.id')->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductOptions($pid)
    {
        $q = $this->db->get_where('product_variants', ['product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductOptionsWithWH($pid)
    {
        $this->db->select($this->db->dbprefix('product_variants') . '.*, ' . $this->db->dbprefix('warehouses') . '.name as wh_name, ' . $this->db->dbprefix('warehouses') . '.id as warehouse_id, ' . $this->db->dbprefix('warehouses_products_variants') . '.quantity as wh_qty')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->join('warehouses', 'warehouses.id=warehouses_products_variants.warehouse_id', 'left')
            ->group_by(['' . $this->db->dbprefix('product_variants') . '.id', '' . $this->db->dbprefix('warehouses_products_variants') . '.warehouse_id'])
            ->order_by('product_variants.id');
        $q = $this->db->get_where('product_variants', ['product_variants.product_id' => $pid, 'warehouses_products_variants.quantity !=' => null]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductPhotos($id)
    {
        $q = $this->db->get_where('product_photos', ['product_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductQuantity($product_id, $warehouse)
    {
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse], 1);
        if ($q->num_rows() > 0) {
            return $q->row_array();
        }
        return false;
    }

    public function getProductsForPrinting($term, $limit = 5)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price')
            ->where('(' . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductsForCount($term,$id, $limit = 5)
    {
        $this->db->select('*')
            ->where('(' . $this->db->dbprefix('stock_count_items') . ".product_name LIKE '%" . $term . "%' OR product_code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('stock_count_items') . ".product_name, ' (', product_code, ')') LIKE '%" . $term . "%')")
            ->where('stock_count_id',$id)
            ->limit($limit);
        $q = $this->db->get('stock_count_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductVariantByID($product_id, $id)
    {
        $q = $this->db->get_where('product_variants', ['product_id' => $product_id, 'id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductVariantByName($product_id, $name)
    {
        $q = $this->db->get_where('product_variants', ['product_id' => $product_id, 'name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductVariantID($product_id, $name)
    {
        $q = $this->db->get_where('product_variants', ['product_id' => $product_id, 'name' => $name], 1);
        if ($q->num_rows() > 0) {
            $variant = $q->row();
            return $variant->id;
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

    public function getProductWarehouseOptions($option_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', ['option_id' => $option_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductWithCategory($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('categories') . '.name as category')
        ->join('categories', 'categories.id=products.category_id', 'left');
        $q = $this->db->get_where('products', ['products.id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchasedQty($id)
    {
        $this->db->select('date_format(' . $this->db->dbprefix('purchases') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('purchase_items') . '.quantity ) as purchased, SUM( ' . $this->db->dbprefix('purchase_items') . '.subtotal ) as amount')
            ->from('purchases')
            ->join('purchase_items', 'purchases.id=purchase_items.purchase_id', 'left')
            ->group_by('date_format(' . $this->db->dbprefix('purchases') . ".date, '%Y-%m')")
            ->where($this->db->dbprefix('purchase_items') . '.product_id', $id)
            //->where('DATE(NOW()) - INTERVAL 1 MONTH')
            ->where('DATE_ADD(curdate(), INTERVAL 1 MONTH)')
            ->order_by('date_format(' . $this->db->dbprefix('purchases') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPurchaseItems($purchase_id)
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

    public function getQASuggestions($term, $limit = 5)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name')
            ->where("type != 'combo' AND "
                . '(' . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSoldQty($id)
    {
        $this->db->select('date_format(' . $this->db->dbprefix('sales') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('sale_items') . '.quantity ) as sold, SUM( ' . $this->db->dbprefix('sale_items') . '.subtotal ) as amount')
            ->from('sales')
            ->join('sale_items', 'sales.id=sale_items.sale_id', 'left')
            ->group_by('date_format(' . $this->db->dbprefix('sales') . ".date, '%Y-%m')")
            ->where($this->db->dbprefix('sale_items') . '.product_id', $id)
            //->where('DATE(NOW()) - INTERVAL 1 MONTH')
            ->where('DATE_ADD(curdate(), INTERVAL 1 MONTH)')
            ->order_by('date_format(' . $this->db->dbprefix('sales') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStockCountItems($stock_count_id)
    {
        $q = $this->db->get_where('stock_count_items', [
            'counted >' => 0, 
            'stock_count_id' => $stock_count_id,
            'status' => 0,
        ]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return null;
    }
    public function getStockProductCountItems($stock_count_id,$product_id =null)
    {
        $q = $this->db->get_where('stock_count_items', [
            'counted >' => 0, 
            'stock_count_id' => $stock_count_id,
            'product_id' => $product_id,
            ]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return null;
    }
    public function getStockCountProducts($warehouse_id, $type, $categories = null, $brands = null)
    {
        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.cost as cost, {$this->db->dbprefix('warehouses_products')}.quantity as quantity")
        ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
        ->where('warehouses_products.warehouse_id', $warehouse_id)
        ->where('products.type', 'standard')
        ->order_by('products.code', 'asc');
        if ($categories) {
            $r = 1;
            $this->db->group_start();
            foreach ($categories as $category) {
                if ($r == 1) {
                    $this->db->where('products.category_id', $category);
                } else {
                    $this->db->or_where('products.category_id', $category);
                }
                $r++;
            }
            $this->db->group_end();
        }
        if ($brands) {
            $r = 1;
            $this->db->group_start();
            foreach ($brands as $brand) {
                if ($r == 1) {
                    $this->db->where('products.brand', $brand);
                } else {
                    $this->db->or_where('products.brand', $brand);
                }
                $r++;
            }
            $this->db->group_end();
        }

        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStockCountProductVariants($warehouse_id, $product_id)
    {
        $this->db->select("{$this->db->dbprefix('product_variants')}.name, {$this->db->dbprefix('warehouses_products_variants')}.quantity as quantity")
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left');
        $q = $this->db->get_where('product_variants', ['product_variants.product_id' => $product_id, 'warehouses_products_variants.warehouse_id' => $warehouse_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getStouckCountByID($id)
    {
        $q = $this->db->get_where('stock_counts', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSubCategories($parent_id)
    {
        $this->db->select('id as id, name as text')
        ->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSubCategoryProducts($subcategory_id)
    {
        $q = $this->db->get_where('products', ['subcategory_id' => $subcategory_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSupplierByName($name)
    {
        $q = $this->db->get_where('companies', ['name' => $name, 'group_name' => 'supplier' ], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function getTransferItems($transfer_id)
    {
        $q = $this->db->get_where('purchase_items', ['transfer_id' => $transfer_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function getWarehouseProductVariant($warehouse_id, $product_id, $option_id = null)
    {
        $q = $this->db->get_where('warehouses_products_variants', ['product_id' => $product_id, 'option_id' => $option_id, 'warehouse_id' => $warehouse_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function has_purchase($product_id, $warehouse_id = null)
    {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('purchase_items', ['product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return true;
        }
        return false;
    }

    public function insertQuantity($product_id, $warehouse_id, $quantity, $rack = null)
    {
        $product = $this->site->getProductByID($product_id);
        if ($this->db->insert('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity, 'rack' => $rack, 'avg_cost' => $product->cost])) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function products_count($category_id, $subcategory_id = null)
    {
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->from('products');
        return $this->db->count_all_results();
    }

    public function reverseAdjustment($id)
    {
        if ($products = $this->getAdjustmentItems($id)) {
            foreach ($products as $adjustment) {
                $clause = ['product_id' => $adjustment->product_id, 'warehouse_id' => $adjustment->warehouse_id, 'option_id' => $adjustment->option_id, 'status' => 'received'];
                $qty    = $adjustment->type == 'subtraction' ? (0 + $adjustment->quantity) : (0 - $adjustment->quantity);
                $this->site->setPurchaseItem($clause, $qty);
                $this->site->syncProductQty($adjustment->product_id, $adjustment->warehouse_id);
                if ($adjustment->option_id) {
                    $this->site->syncVariantQty($adjustment->option_id, $adjustment->warehouse_id, $adjustment->product_id);
                }
            }
        }
    }

    public function setRack($data)
    {
        if ($this->db->update('warehouses_products', ['rack' => $data['rack']], ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']])) {
            return true;
        }
        return false;
    }
    public function setBooking($data)
    {
        if ($this->db->insert('booking', $data)) {
            return true;
        }
        return false;
    }
    public function setBlocking($data)
    {
        if ($this->db->insert('blocking', $data)) {
            return true;
        }
        return false;
    }
    public function syncAdjustment($data = [])
    {
        if (!empty($data)) {
            $clause = ['product_id' => $data['product_id'], 'option_id' => $data['option_id'], 'warehouse_id' => $data['warehouse_id'], 'status' => 'received'];
            $qty    = $data['type'] == 'subtraction' ? 0 - $data['quantity'] : 0 + $data['quantity'];
            $this->site->setPurchaseItem($clause, $qty);

            $this->site->syncProductQty($data['product_id'], $data['warehouse_id']);
            if ($data['option_id']) {
                $this->site->syncVariantQty($data['option_id'], $data['warehouse_id'], $data['product_id']);
            }
        }
    }

    public function syncVariantQty($option_id)
    {
        $wh_pr_vars = $this->getProductWarehouseOptions($option_id);
        $qty        = 0;
        foreach ($wh_pr_vars as $row) {
            $qty += $row->quantity;
        }
        if ($this->db->update('product_variants', ['quantity' => $qty], ['id' => $option_id])) {
            return true;
        }
        return false;
    }

    public function totalCategoryProducts($category_id)
    {
        $q = $this->db->get_where('products', ['category_id' => $category_id]);
        return $q->num_rows();
    }

    public function updateAdjustment($id, $data, $products)
    {
        $this->reverseAdjustment($id);
        if ($this->db->update('adjustments', $data, ['id' => $id]) && $this->db->delete('adjustment_items', ['adjustment_id' => $id])) {
            foreach ($products as $product) {
                $product['adjustment_id'] = $id;
                $this->db->insert('adjustment_items', $product);
                $this->syncAdjustment($product);
            }
            return true;
        }
        return false;
    }

    public function updatePrice($data = [])
    {
        if ($this->db->update_batch('products', $data, 'code')) {
            return true;
        }
        return false;
    }
    public function UpdateProductBYID($id, $data = [])
    {
        $data = [
            'quantity' => 2
        ];
        if ($this->db->update('products', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function BlockProductBYID($id, $data = [])
    {
        $data = [
            'quantity' => -2
        ];
//         var_dump($id);
//         var_dump($this->db->update('products', $data, ['id' => $id]));
// exit();
        if ($this->db->update('products', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants)
    {
        if ($this->db->update('assets', $data, ['id' => $id])) {
            if ($items) {
                $this->db->delete('combo_items', ['product_id' => $id]);
                foreach ($items as $item) {
                    $item['product_id'] = $id;
                    $this->db->insert('combo_items', $item);
                }
            }
            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);

            if ($warehouse_qty && !empty($warehouse_qty)) {
                foreach ($warehouse_qty as $wh_qty) {
                    $this->db->update('warehouses_products', ['rack' => $wh_qty['rack']], ['product_id' => $id, 'warehouse_id' => $wh_qty['warehouse_id']]);
                }
            }

            if (!empty($update_variants)) {
                foreach ($update_variants as $variant) {
                    $vr = $this->getProductVariantByName($id, $variant['name']);
                    if ($vr) {
                        $this->db->update('product_variants', $variant, ['id' => $vr->id]);
                    } else {
                        $this->db->insert('product_variants', $variant);
                    }
                }
            }

            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', ['product_id' => $id, 'photo' => $photo]);
                }
            }

            if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {
                    $pr_attr['product_id'] = $id;
                    $variant_warehouse_id  = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    $this->db->insert('product_variants', $pr_attr);
                    $option_id = $this->db->insert_id();

                    if ($pr_attr['quantity'] != 0) {
                        $this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']]);

                        $tax_rate_id = $tax_rate ? $tax_rate->id : null;
                        $tax         = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . '%' : $tax_rate->rate) : null;
                        $unit_cost   = $data['cost'];
                        if ($tax_rate) {
                            if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                if ($data['tax_method'] == '0') {
                                    $pr_tax_val    = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                    $net_item_cost = $data['cost'] - $pr_tax_val;
                                    $item_tax      = $pr_tax_val * $pr_attr['quantity'];
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $pr_tax_val    = ($data['cost'] * $tax_rate->rate) / 100;
                                    $unit_cost     = $data['cost'] + $pr_tax_val;
                                    $item_tax      = $pr_tax_val * $pr_attr['quantity'];
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax      = $tax_rate->rate;
                            }
                        } else {
                            $net_item_cost = $data['cost'];
                            $item_tax      = 0;
                        }

                        $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
                        $item     = [
                            'product_id'        => $id,
                            'product_code'      => $data['code'],
                            'product_name'      => $data['name'],
                            'net_unit_cost'     => $net_item_cost,
                            'unit_cost'         => $unit_cost,
                            'quantity'          => $pr_attr['quantity'],
                            'option_id'         => $option_id,
                            'quantity_balance'  => $pr_attr['quantity'],
                            'quantity_received' => $pr_attr['quantity'],
                            'item_tax'          => $item_tax,
                            'tax_rate_id'       => $tax_rate_id,
                            'tax'               => $tax,
                            'subtotal'          => $subtotal,
                            'warehouse_id'      => $variant_warehouse_id,
                            'date'              => date('Y-m-d'),
                            'status'            => 'received',
                        ];
                        $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                        $this->db->insert('purchase_items', $item);
                    }
                }
            }

            $this->site->syncQuantity(null, null, null, $id);
            return true;
        } else {
            return false;
        }
    }

    public function updateProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            if ($this->db->update('warehouses_products_variants', ['quantity' => $quantity], ['option_id' => $option_id, 'warehouse_id' => $warehouse_id])) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        } else {
            if ($this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity])) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        }
        return false;
    }

    public function updateQuantity($product_id, $warehouse_id, $quantity, $rack = null)
    {
        $data = $rack ? ['quantity' => $quantity, 'rack' => $rack] : $data = ['quantity' => $quantity];
        if ($this->db->update('warehouses_products', $data, ['product_id' => $product_id, 'warehouse_id' => $warehouse_id])) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }
    //-------room-------
    public function addRoom($data)
    {
        if ($this->db->insert("suspended_note", $data)) {
            return true;
        }
        return false;
    }

    public function updateRoom($id, $data = array())
    {
        $this->db->where('note_id', $id);
        if ($this->db->update("suspended_note", $data)) {
            return true;
        }
        return false;
    }
   public function delete_room($id)
    {
        if ( $this->db->delete('suspended_note', array('note_id' => $id))) {
            return true;
        }
        return false;
    } 
    public function deleteBlockProperty($id)
    {
        if ( $this->db->update('Blocking',['status'=> 0], array('product_id' => $id))) {
            return true;
        }
        return false;
    }
    public function deleteBookingProperty($id)
    {
        if ( $this->db->update('Booking',['status'=> 0], array('product_id' => $id))) {
            return true;
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
    public function getAllWarehousesByUser($warehouse_id) 
    {
        $wid = explode(',', $warehouse_id);
        $this->db->select('warehouses.*')
                 ->from('warehouses')
                 ->where_in("id", $wid);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getAllBoms()
    {
        $q = $this->db->get('bom');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getAllBom_id($id, $ware_id)
    {
        $wp = '(SELECT product_id, SUM(quantity) as qty FROM bpas_warehouses_products WHERE warehouse_id = '.$ware_id.' GROUP BY product_id) as WP';
        $this->db->select('bom.*, bom_items.*, COALESCE(WP.qty , 0) as qoh')
                 ->join('bom_items', 'bom.id = bom_items.bom_id')
                 ->join('products', 'bom_items.product_id = products.id')
                 ->join($wp,'WP.product_id = bom_items.product_id', 'left')
                 ->where(array('bom.id'=>$id));
        $q = $this->db->get('bom');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function insertConvert($data)
    {
        if ($this->db->insert('convert', $data)) {
            $convert_id = $this->db->insert_id();
            
            if ($this->site->getReference('con', $data['biller_id']) == $data['reference_no']) {
                $this->site->updateReference('con', $data['biller_id']);
            }
            return $convert_id;
        }
    }
    
    public function updateConvert($id, $data) 
    {
        if ($this->db->update('convert', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
    public function getConvertByID($id) 
    {
        $l_qty = "( SELECT
                        con_item.convert_id,
                        SUM(con_item.cost) as cost,
                        SUM(con_item.quantity) as qty
                    FROM
                        bpas_convert_items con_item
                    WHERE
                        con_item.`status` = 'add'
                    GROUP BY
                        con_item.convert_id
                    ) Quantity";
        $this->db
            ->select($this->db->dbprefix('convert') . ".id as id,
                    ".$this->db->dbprefix('convert').".date as Date,
                    ".$this->db->dbprefix('convert').".reference_no as Reference, Quantity.cost, Quantity.qty,
                    ".$this->db->dbprefix('convert').".noted as Note,
                    ".$this->db->dbprefix('warehouses').".name as na,
                    ".$this->db->dbprefix('convert').".warehouse_id as warehouse_id,
                    ".$this->db->dbprefix('convert').".bom_id as bom_id,
                    ".$this->db->dbprefix('convert').".biller_id as biller_id,
                    ".$this->db->dbprefix('users') . ".username ", false)
            ->join('users', 'users.id               = convert.created_by', 'left')
            ->join('warehouses', 'warehouses.id     = convert.warehouse_id', 'left')
            ->join($l_qty, ' Quantity.convert_id    = bpas_convert.id', 'left')
            ->group_by('convert.id');
        $q = $this->db->get_where('convert', array('convert.id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function ConvertAdd($id)
    {
       $this->db->select('product_name, product_code,unit,'.
            $this->db->dbprefix('convert_items').'.quantity AS Cquantity,'.
            $this->db->dbprefix('convert_items').'.cost AS Ccost,'.
            $this->db->dbprefix('products').'.cost AS Pcost, product_variants.name as variant, product_variants.qty_unit, convert.noted')
                ->join('products', 'products.id=convert_items.product_id', 'left')
                ->join('product_variants', 'product_variants.id=convert_items.option_id', 'left')
                ->join('convert', 'convert_items.convert_id = convert.id', 'left');
        $q = $this->db->get_where('convert_items', array('convert_id' => $id, 'status' => 'add'));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    
    public function convertHeader($id)
    {
        $this->db->select('convert.*,users.username,warehouses.name')
             ->join('users', 'users.id = convert.created_by', 'left')
             ->join('warehouses', 'warehouses.id = convert.warehouse_id', 'left');
        $q = $this->db->get_where("convert", array('convert.id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getConvert_ItemByID($id, $ware_id = NULL)
    {
        $wp = '(SELECT product_id, SUM(quantity) as qty FROM bpas_warehouses_products WHERE warehouse_id = '.$ware_id.' GROUP BY product_id) as WP';
        $this->db->select('convert_items.id, 
                            convert_items.convert_id, 
                            convert_items.product_id, 
                            convert_items.product_code, 
                            convert_items.product_name, 
                            convert_items.quantity, 
                            convert_items.cost, 
                            convert_items.status,
                            convert_items.option_id,
                            COALESCE(WP.qty , 0) as qoh
                            ');
        $this->db->join($wp, 'WP.product_id = convert_items.product_id', 'left');
        $q = $this->db->get_where("convert_items", array('convert_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    
    public function deleteConvert($id)
    {
        if ($this->db->delete('convert', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
    
    public function deleteConvert_items($id)
    {
        if ($this->db->delete('convert_items', array('convert_id' => $id))) {
            return true;
        }
        return FALSE;
    }
    
    public function deleteConvert_itemsByPID($id, $product_id)
    {
        if ($this->db->delete('convert_items', array('convert_id' => $id, 'product_id' => $product_id))) {
            return true;
        }
        return FALSE;
    }
    public function deleteConvert_itemsInventory_detail($convert_items_id)
    {
        if ($this->db->delete('inventory_valuation_details', array('type' => 'CONVERT', 'field_id' => $convert_items_id))) {
            return true;
        }
        return FALSE;
    }
    public function getProductName_code($w_id=null)
    {   
        
        $this->db->where('warehouses_products.warehouse_id',$w_id);
        $this->db->select('concat(name," ( ",code," ) ") as label,code as value,bpas_warehouses_products.quantity as quantity,products.cost as cost,bpas_warehouses_products.quantity as qqh');
        $this->db->from('products');
        $this->db->join('warehouses_products' ,'warehouses_products.product_id=products.id', 'left');
         $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getGLChart()
    {
        $this->db->select()
                 ->from('gl_charts');
        $q = $this->db->get();
        if($q->num_rows() > 0){
            return $q->result();
        }
        return false;
    }
    function getReferno()
    {
        $q=$this->db->get('enter_using_stock');
        return $q->result();
    }
    function getEmpno()
    {
        $q=$this->db->get('bpas_users');
        return $q->result();
    }
    public function getPlan()
    {
        $this->db->select('*');
        $q = $this->db->get("project_plan");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }

        return FALSE;
    }
    public function getAllExpenseCategory()
    {
        $q = $this->db->get('expense_categories');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getAllPositionData() 
    {
        $q = $this->db->get('position');
        if ($q->num_rows() > 0 ) {
            $data = $q->result();
            return $data;
        }
        return FALSE;
    }
    public function getUsingStockById($id)
    {
        $this->db->where('id',$id);
        $q=$this->db->get('enter_using_stock');
        if($q){
            return $q->row();
        }else{return false;}
    }
    public function getUsingStockItemsByRef($ref)
    {
        $this->db->select('enter_using_stock_items.id as e_id,
                            enter_using_stock_items.code as code,
                            enter_using_stock_items.code as code,
                            enter_using_stock_items.description,
                            enter_using_stock_items.qty_use,
                            enter_using_stock_items.qty_by_unit,
                            enter_using_stock_items.unit,
                            enter_using_stock_items.expiry,
                            enter_using_stock_items.warehouse_id as wh_id,
                            enter_using_stock_items.option_id as option_id,
                            products.name,
                            products.cost,
                            products.quantity,
                            products.code as product_code,
                            products.id as id,
                            warehouses_products.quantity as qoh,
                            products.unit as unit_type,
                            units.name as unit_name
                        ');
        $this->db->from('enter_using_stock_items');
        $this->db->join('products', 'enter_using_stock_items.code = products.code', 'left');
        $this->db->join('units', 'units.id = products.unit', 'left');
        $this->db->join('warehouses_products', 'enter_using_stock_items.warehouse_id = warehouses_products.warehouse_id and products.id = warehouses_products.product_id', 'left');
        $this->db->where('enter_using_stock_items.reference_no', $ref);
            
        $this->db->group_by('e_id');
        $q=$this->db->get();
        if($q){
            return $q->result();
        }else{
            return false;
        }
    }
    public function getUsingStockProducts($term, $warehouse_id, $plan = NULL, $address = NULL, $limit = 100)
    {
        
        $this->db->where("type = 'standard' AND (bpas_products.name LIKE '%" . $term . "%' OR bpas_products.code LIKE '%" . $term . "%' OR  concat(bpas_products.name, ' (', bpas_products.code, ')') LIKE '%" . $term . "%')");
        if ($plan) {
            $project_plan_qty = "(
                SELECT 
                    product_id, 
                    COALESCE(quantity_balance, 0) as project_qty 
                FROM 
                    bpas_project_plan_items 
                WHERE 
                    bpas_project_plan_items.project_plan_id = $plan
                AND bpas_project_plan_items.product_code LIKE '%$term%' 
            ) project_plan";
            
            $using_qty = "(
                SELECT
                    code,
                    COALESCE(SUM(qty_use), 0) as using_qty
                FROM
                    bpas_enter_using_stock_items 
                LEFT JOIN bpas_enter_using_stock ON bpas_enter_using_stock_items.reference_no = bpas_enter_using_stock.reference_no 
                WHERE 
                    bpas_enter_using_stock.plan_id = $plan 
                AND bpas_enter_using_stock.address_id = '$address' 
                AND bpas_enter_using_stock_items.code LIKE '%$term%' 
                AND bpas_enter_using_stock.type = 'use'
            ) using_stock";
            
            $return_using_qty = "(
                SELECT
                    code,
                    COALESCE(SUM(qty_use), 0) as using_qty
                FROM
                    bpas_enter_using_stock_items 
                LEFT JOIN bpas_enter_using_stock ON bpas_enter_using_stock_items.reference_no = bpas_enter_using_stock.reference_no 
                WHERE 
                    bpas_enter_using_stock.plan_id = $plan 
                AND bpas_enter_using_stock.address_id = '$address '
                AND bpas_enter_using_stock_items.code LIKE '%$term%' 
                AND bpas_enter_using_stock.type = 'return'
            ) return_using_stock";
        }
        $this->db->where("warehouses_products.warehouse_id", $warehouse_id);
        $this->db->limit($limit);
        if ($plan) {
            $this->db->select('bpas_products.*, bpas_warehouses_products.quantity as qoh, bpas_units.name as unit_name, (COALESCE(project_plan.project_qty,0) - COALESCE(using_stock.using_qty, 0) + COALESCE(return_using_stock.using_qty, 0) ) as project_qty, project_plan.product_id as in_plan');
        } else {
            $this->db->select('bpas_products.*, bpas_warehouses_products.quantity as qoh, bpas_units.name as unit_name');
        }
        
        $this->db->join('warehouses_products', 'warehouses_products.product_id = products.id', 'left');
        $this->db->join('units', 'units.id = products.unit', 'left');
        if ($plan) {
            $this->db->join($project_plan_qty, 'project_plan.product_id = products.id', 'left');
            $this->db->join($using_qty, 'using_stock.code = products.code', 'left');
            $this->db->join($return_using_qty, 'return_using_stock.code = products.code', 'left');
        }
        $this->db->group_by('products.id');
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getUnitAndVaraintByProductId($id)
    {
        $variant = $this->db->select("products.*, '1' as measure_qty, product_variants.name as unit_variant, product_variants.qty_unit as qty_unit ")
                            ->from("products")
                            ->where("products.id",$id)
                            ->join("product_variants","products.id=product_variants.product_id","left")
                            ->get();                    
        $unit_of_measure = $this->getUnitOfMeasureByProductId($id);
        if($variant->num_rows() > 0 && $variant->row()->unit_variant != null){
            return $variant->result();
        }else{
            return $unit_of_measure;
        }           
    }
    public function getUnitOfMeasureByProductId($id,$unit_desc=null)
    {
        if($unit_desc!=null){
            $this->db->where('units.name',$unit_desc);
        }
        $this->db->where('products.id',$id);
        $this->db->select('products.*,units.name as unit_variant, "1" as measure_qty');
        $this->db->from('products');
        $this->db->join('units','products.unit=units.id','left');
        $q=$this->db->get();
        if($q){
            if($unit_desc!=null){
                return $q->row();
            }else{
                return $q->result();
            }
            
        }
        return false;
    }
    public function getProductVariantByOptionID($option_id)
    {
        $q = $this->db->get_where('product_variants', array('id' => $option_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
    public function getPlanUsing($plan_id, $product_code, $address)
    {
        
        $using_qty = "(
                SELECT
                    code,
                    COALESCE(SUM(qty_use), 0) as using_qty
                FROM
                    bpas_enter_using_stock_items 
                LEFT JOIN bpas_enter_using_stock ON bpas_enter_using_stock_items.reference_no = bpas_enter_using_stock.reference_no 
                WHERE 
                    bpas_enter_using_stock.plan_id = $plan_id 
                AND bpas_enter_using_stock.address_id = '$address'  
                AND bpas_enter_using_stock_items.code = '$product_code'
                AND bpas_enter_using_stock.type = 'use'
            ) using_stock";
            
        $return_using_qty = "(
                SELECT
                    code,
                    COALESCE(SUM(qty_use), 0) as reutn_using_qty
                FROM
                    bpas_enter_using_stock_items 
                LEFT JOIN bpas_enter_using_stock ON bpas_enter_using_stock_items.reference_no = bpas_enter_using_stock.reference_no 
                WHERE 
                    bpas_enter_using_stock.plan_id = $plan_id 
                AND bpas_enter_using_stock.address_id = '$address'  
                AND bpas_enter_using_stock_items.code = '$product_code'
                AND bpas_enter_using_stock.type = 'return'
            ) return_using_stock";
            
        $this->db->select('project_plan_items.*, using_stock.using_qty, return_using_stock.reutn_using_qty')
                 ->from('project_plan_items')
                 ->join($using_qty, 'using_stock.code = project_plan_items.product_code', 'left')
                 ->join($return_using_qty, 'using_stock.code = project_plan_items.product_code', 'left')
                 ->where(array('project_plan_items.project_plan_id' => $plan_id, 'project_plan_items.product_code' => $product_code));
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function get_enter_using_stock_by_id($id)
    {
        $this->db->select('enter_using_stock.*, warehouses.name, users.first_name, users.last_name, project_plan.plan, CONCAT(bpas_products.cf4, "", bpas_products.cf3) as address');
        $this->db->from('enter_using_stock');
        $this->db->join('project_plan','enter_using_stock.plan_id=project_plan.id', 'left');
        $this->db->join('warehouses','warehouses.id=enter_using_stock.warehouse_id', 'left');
        $this->db->join('products','products.id=enter_using_stock.address_id', 'left');
        $this->db->join('users','users.id=enter_using_stock.employee_id', 'left');
        $this->db->where('enter_using_stock.id',$id);
        $q=$this->db->get();
        if($q){
            return $q->row();
        }else{
            return false;
        }
    }
    public function get_enter_using_stock_item_by_ref($ref)
    {
        $this->db->select('enter_using_stock_items.*, products.name as product_name, expense_categories.name as exp_cate_name, enter_using_stock_items.unit as unit_name, products.cost, position.name as pname,reasons.description as rdescription, product_variants.qty_unit as variant_qty');
        $this->db->from('enter_using_stock_items');
        $this->db->join('products','products.code=enter_using_stock_items.code','left');
        $this->db->join('position','enter_using_stock_items.description = position.id','left');
        $this->db->join('reasons','enter_using_stock_items.reason = reasons.id','left');
        $this->db->join('product_variants','enter_using_stock_items.option_id = product_variants.id','left');
        $this->db->join('expense_categories','enter_using_stock_items.exp_cate_id = expense_categories.id','left');
        $this->db->where('enter_using_stock_items.reference_no',$ref);
        
        $q=$this->db->get();
        if($q){
            return $q->result();
        }else{
            return false;
        }
    }
    public function get_enter_using_stock_info()
    {
        $this->db->select('bpas_companies.*')
                 ->from('bpas_settings')
                 ->join('bpas_companies', 'bpas_settings.default_biller = bpas_companies.id','left');
        $q = $this->db->get();
        if ($q->num_rows() > 0 ) {
            return $q->row();
        }
        return FALSE;
    }
    public function getUsingStockProjectByRef($ref)
    {

        $this->db->select('bpas_companies.company');
        $this->db->from('bpas_enter_using_stock');
        $this->db->join('bpas_companies','bpas_enter_using_stock.shop = bpas_companies.id','left');
        $this->db->where('bpas_enter_using_stock.reference_no', $ref);
        $q=$this->db->get();
        if($q){
            return $q->row();
        }else{return false;}
    }
     public function getUsingStockProject($id)
    {
        $this->db->select('bpas_companies.company, bpas_companies.logo, bpas_companies.address, bpas_companies.phone, bpas_companies.email');
        $this->db->from('bpas_enter_using_stock');
        $this->db->join('bpas_companies','bpas_enter_using_stock.shop = bpas_companies.id','left');
        $this->db->where('bpas_enter_using_stock.id', $id);
        $q=$this->db->get();
        if($q){
            return $q->row();
        }else{return false;}
    }
    public function getAuInfo($id)
    {
        $this->db->select('bpas_users.username')
                 ->from('bpas_enter_using_stock')
                 ->join('bpas_users', 'bpas_enter_using_stock.authorize_id = bpas_users.id','left')
                 ->where('bpas_enter_using_stock.id',$id);
        $q = $this->db->get();
        if ($q->num_rows() > 0 ) {
            return $q->row();
        }
        return FALSE;
    }
    function getEvaluationTable($product_id){
        
        $this->db->select("
                    {$this->db->dbprefix('asset_evaluation')}.id as id,
                    {$this->db->dbprefix('asset_evaluation')}.evaluation_date, 
                    current_cost, 
                    accumulated, 
                    net_value,
                    is_expense
                    ", false)
                ->from('asset_evaluation')
                ->group_by('asset_evaluation.id')
                ->order_by('id','ASC');
            if ($product_id) {
                $this->db->where("{$this->db->dbprefix('asset_evaluation')}.product_id",$product_id);
            }
        $q= $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function evaluation($data)
    {
		//asset_evaluation
        if ($this->db->update('assets', 
				['price' => $data['current_cost']], 
				['id' => $data['product_id']])) {
					
			$this->db->insert(
					'asset_evaluation', 
					[
					'product_id' => $data['product_id'], 
					'evaluation_date' => $data['evaluation_date'],
					'current_cost' => $data['current_cost'],
					'created_by' => $data['created_by'],
				]);
			return true;
        }
        return false;
    }
    public function checked_evaluation($expense_id){

        $q = $this->db->get_where('asset_evaluation', array('expense_id' => $expense_id), 1);
        if ($q->num_rows() > 0) {
            return true;
        }
        return FALSE;
    }
    public function deleteevaluation($id)
    {   
        if ($this->db->update('asset_evaluation', 
                ['is_expense' => 0 ], 
                ['id' => $id ])) {

            $getEv = $this->site->getevaluationByID($id);
            $reference_no = $getEv->reference_no;

            //$this->site->deleteAccTran('Payment',$id);
            return true;
            
        }
        
        return false;
    }
    public function deleteDPByPurchaseID($id)
    {   
        if ($this->db->delete('asset_evaluation', ['expense_id' => $id])) {
            $this->site->deleteDpByexpense('Depreciation',$id);
            return true;
        }
        return false;
    }
    function getScheduleByExpenseId($expense_id=null){
        
        $this->db->select("
                    {$this->db->dbprefix('asset_evaluation')}.id as id,
                    {$this->db->dbprefix('asset_evaluation')}.expense_id as expense_id,
                    {$this->db->dbprefix('asset_evaluation')}.evaluation_date, 
                    current_cost, 
                    accumulated, 
                    net_value,
                    is_expense
                    ", false)
                ->from('asset_evaluation')
                ->group_by('asset_evaluation.id')
                ->order_by('id','ASC');
            if ($expense_id) {
                $this->db->where("{$this->db->dbprefix('asset_evaluation')}.expense_id",$expense_id);
            }else{
                $this->db->limit(100);
            }
        $q= $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
}
