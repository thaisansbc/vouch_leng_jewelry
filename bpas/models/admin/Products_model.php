<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Products_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add_products($products = [], $arr_products_units = [])
    {
        if (!empty($products)) {
            foreach ($products as $key => $product) {
                $variants = explode('|', $product['variants']);
                unset($product['variants']);
                if ($this->db->insert('products', $product)) {
                    $product_id = $this->db->insert_id();
                    if (isset($arr_products_units[$key])) {
                        foreach($arr_products_units[$key] as $p_unit) {
                            $p_unit['product_id'] = $product_id;
                            $this->db->insert('cost_price_by_units', $p_unit);
                        }
                    }
                    foreach ($variants as $variant) {
                        if ($variant && trim($variant) != '') {
                            $vat = ['product_id' => $product_id, 'name' => trim($variant)];
                            $this->db->insert('product_variants', $vat);
                        }
                    }
                    $this->site->syncQuantity(null, null, null, $product_id);
                    $this->db->update('warehouses_products', ['qty_alert' => $product['alert_quantity']], ['product_id' => $product_id]);
                }
            }
            return true;
        }
        return false;
    }

    public function addAdjustment($data, $products, $stockmoves = false, $accTrans = array())
    {
        $this->db->trans_start();
        if ($this->db->insert('adjustments', $data)) {
            $adjustment_id = $this->db->insert_id();
            //=======Add accounting =======//
            if ($accTrans) {
                foreach ($accTrans as $accTran) {
                    $accTran['tran_no'] = $adjustment_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            //=======End accounting =======//
            foreach ($products as $product) {
                $product['adjustment_id'] = $adjustment_id;
                $this->db->insert('adjustment_items', $product);
                if ($data['count_id']) {
                    $query = $this->db->update('stock_count_items', array('status' => 1), array('stock_count_id' => $data['count_id'], 'product_id' => $product['product_id']));
                }
            }
            if ($stockmoves) {
                foreach ($stockmoves as $stockmove) {
                    if (isset($stockmove['reactive']) && $stockmove['reactive'] != 1) {
                        unset($stockmove['serial_no']);
                    }
                    unset($stockmove['reactive']);
                    $stockmove['transaction_id'] = $adjustment_id;
                    $this->db->insert('stock_movement', $stockmove);
                    if ($this->site->stockMovement_isOverselling($stockmove)) {
                        return false;
                    }
                }
            }
            if ($this->site->getReference('qa') == $data['reference_no']) {
                $this->site->updateReference('qa');
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the adjustment (Add:Products_model.php)');
        } else {
            return $adjustment_id;
        }
        return false;
    }

    public function addAjaxProduct($data)
    {
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();
            return $this->getProductByID($product_id);
        }
        return false;
    }

    public function addProduct($data, $items, $warehouse_qty, $product_attributes, $photos, $addOn_items, $product_options, $unit_datas = null, $product_account = array(),$bom_items = false, $formulation_items = false, $product_units = null, $warehouse_racks = null) 
    {
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();
            if($product_account){
                $product_account['product_id'] = $product_id;
                $this->db->insert('account_product', $product_account);
            }
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
            if ($product_options) {
                foreach ($product_options as $option) {
                    $options = $this->site->getOptionByID($option['option_id']);
                    $option['product_id'] = $product_id;
                    $option['name'] = $options->name;
                    $this->db->insert('product_options', $option);
                }
            }
            if ($unit_datas) {
                foreach($unit_datas as $datas) {
                    $datas['product_id'] = $product_id;
                    $this->db->insert('cost_price_by_units',$datas);
                }
            }
            if ($product_units){
				foreach ($product_units as $pr_unit){
					$pr_unit['product_id'] = $product_id;
					$this->db->insert('product_units', $pr_unit);
				}
			}
            if ($bom_items) {
                foreach ($bom_items as $bom_item) {
                    $bom_item['standard_product_id'] = $product_id;
                    $this->db->insert('bom_products', $bom_item);
                }
            }
            if ($formulation_items) {
                foreach ($formulation_items as $formulation_item) {
                    $formulation_item['main_product_id'] = $product_id;
                    $formulation_item['for_product_id']  = $product_id;
                    $this->db->insert('formulation_products', $formulation_item);
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
                        $this->db->insert('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $wh_qty['warehouse_id'], 'quantity' => $wh_qty['quantity'], 'rack' => $wh_qty['rack'], 'avg_cost' => $data['cost'], 'qty_alert' => $data['alert_quantity']]);
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
            if (!empty($warehouse_racks)) {
                foreach ($warehouse_racks as $warehouse_rack) {
                    $this->site->setWarehouseProductRack($warehouse_rack['warehouse_id'], $product_id, $warehouse_rack['rack_id']);
                }
            }
            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', ['product_id' => $product_id, 'photo' => $photo]);
                }
            }
            $this->site->syncQuantity(null, null, null, $product_id);
            $this->db->update('warehouses_products', ['qty_alert' => $data['alert_quantity']], ['product_id' => $product_id]);
            return true;
        }
        return false;
    }

    public function addProperty($data, $items, $warehouse_qty, $product_attributes, $photos)
    {
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();
            $this->site->updateReference('inventory');
            if ($items) {
                foreach ($items as $item) {
                    $item['product_id'] = $product_id;
                    $this->db->insert('combo_items', $item);
                }
            }

            if(isset($addOn_items)){
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
                        $this->db->insert('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $wh_qty['warehouse_id'], 'quantity' => $wh_qty['quantity'], 'rack' => $wh_qty['rack'], 'avg_cost' => $data['cost'], 'qty_alert' => $data['alert_quantity']]);

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

    public function addStockCount($data, $items)
    {
        $this->db->insert('stock_counts', $data);
        $id = $this->db->insert_id();
        if ($this->site->getReference('stc') == $data['reference_no']) {
            $this->site->updateReference('stc');
        }
        if ($id != 0 and !empty($items)) {
            foreach ($items as $item) {
                $stock[] = array(
                    'stock_count_id'   => $id,
                    'product_id'       => $item['product_id'],
                    'product_code'     => $item['product_code'],
                    'product_name'     => $item['product_name'],
                    'product_variant ' => $item['variant'],
                    'expected '        => $item['expected'],
                    'counted '         => $item['counted'],
                    'cost'             => $item['cost'],
                    'expiry'           => $item['expiry'],
                ); 
            }
            $query = $this->db->insert_batch('stock_count_items', $stock);
        }
        if ($query) {
            return true;
        }
        return false;
    }

    public function deleteAdjustment($id)
    {
        if ($id && $id > 0) {
            if ($this->db->delete('adjustments', ['id' => $id])) {
                //---account ----
                $this->site->delete_stock_movement('QuantityAdjustment', $id);
                $this->site->deleteAccTran('adjustment', $id);
                //---end account---
                $items = $this->getAdjustmentItems($id);
                if ($this->Settings->accounting_method == '0') {
                    foreach ($items as $item) {
                        $this->site->updateFifoCost($item->product_id);
                    }
                } else if ($this->Settings->accounting_method == '1') {
                    foreach ($items as $item) {
                        $this->site->updateLifoCost($item->product_id);
                    }               
                } else if ($this->Settings->accounting_method == '3') {
                    foreach ($items as $item) {
                        $this->site->updateProductMethod($item->product_id);
                    }               
                }
                $this->db->delete('adjustment_items', ['adjustment_id' => $id]);
                return true;
            }
        }
        return false;
    }
    public function unBookingProduct($id)
    {   
        if ($this->db->update('products',['quantity' => 1], ['id' => $id])){
          return true;
        }
        return false;
    }
    public function realiseProduct($id)
    {
        if ($this->db->update('products',['quantity' => 1], ['id' => $id])){
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
        if ($this->db->delete('products', ['id' => $id]) && $this->db->delete('warehouses_products', ['product_id' => $id])) {
            $this->db->delete('warehouses_products_variants', ['product_id' => $id]);
            $this->db->delete('product_variants', ['product_id' => $id]);
            $this->db->delete('cost_price_by_units', ['product_id' => $id]);
            $this->db->delete('product_photos', ['product_id' => $id]);
            $this->db->delete('product_prices', ['product_id' => $id]);
            $this->db->delete('addon_items', ['product_id' => $id]);
            $this->db->delete('account_product', array('product_id' => $id));
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
        $query = $this->db->get('products');

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
            $this->db->where('id', $id);
            $query = $this->db->update('stock_counts');
        } else {
            $this->db->set('status', 0);
            $this->db->set('date', $date);
            $this->db->where('id', $id);
            $query = $this->db->update('stock_counts');
        } 
        $product_id = $this->input->post('product');
        $expiry     = $this->input->post('expiry');
        $counted    = $this->input->post('quantity'); 
        if ($query) {
            for ($i=0; $i < count($product_id); $i++) { 
                $item_expiry = ((isset($expiry[$i]) && $expiry[$i] != '0000-00-00' && $expiry[$i] != 'null' && $expiry[$i] != 'NULL' && $expiry[$i] != false) ? $expiry[$i] : null);
                $this->db->set('date_time', $date);
                $this->db->set('counted', $counted[$i]);
                $this->db->where('stock_count_id', $id);
                $this->db->where('product_id', $product_id[$i]);
                $this->db->where('expiry', $item_expiry);
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
                $this->db->update('stock_count_items', $product, ['stock_count_id' => $id,'product_id' => $product['product_id'],'expiry' => $product['expiry']]);
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
        $this->db->select('adjustment_items.*, products.code as product_code, products.name as product_name, products.image, products.details as details, product_variants.name as variant, units.code as unit_code, units.name as unit_name')
            ->join('products', 'products.id=adjustment_items.product_id', 'left')
            ->join('units', 'units.id=products.unit', 'left')
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
        $this->db->select('
            ' . $this->db->dbprefix('warehouses') . '.*, 
            ' . $this->db->dbprefix('warehouses_products') . '.quantity, 
            ' . $this->db->dbprefix('warehouses_products') . '.weight, 
            ' . $this->db->dbprefix('product_rack') . '.name as rack
        ')
            ->join('warehouses_products', 'warehouses_products.warehouse_id=warehouses.id', 'left')
            ->join('product_rack', 'warehouses_products.rack_id=product_rack.id', 'left')
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
    
    public function getBrandByCode($code)
    {
        $q = $this->db->get_where('brands', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function getProductComboItems($pid)
    {
        $this->db->select(
            $this->db->dbprefix('products') . '.id as id, ' . 
            $this->db->dbprefix('products') . '.code as code, ' . 
            $this->db->dbprefix('products') . '.name as name, ' . 
            $this->db->dbprefix('combo_items') . '.quantity as qty, ' . 
            $this->db->dbprefix('combo_items') . '.quantity as width, ' . 
            $this->db->dbprefix('combo_items') . '.unit_price as unit_price, ' . 
            $this->db->dbprefix('combo_items') . '.unit_price as price,
            1 as height
        ');
        $this->db->join('products', 'products.code=combo_items.item_code', 'left');
        $this->db->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', ['product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getProductAddOnItems($pid)
    {
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.code as code, ' . $this->db->dbprefix('addon_items') . '.price as price, ' . $this->db->dbprefix('products') . '.name as name, description') 
            ->join('products', 'products.code = addon_items.item_code', 'left')
            ->group_by('addon_items.id');
        $q = $this->db->get_where('addon_items', ['product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
  
    public function getProductAddOnItem($pid)
    {
        $this->db->select('*');
        $q = $this->db->get_where('addon_items', ['id' => $pid]);
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function getProductNames($term, $limit = 100)
    {
        $this->db->select("
                {$this->db->dbprefix('products')}.id, 
                {$this->db->dbprefix('products')}.code, 
                {$this->db->dbprefix('products')}.name, 
                {$this->db->dbprefix('products')}.price, 
                {$this->db->dbprefix('product_variants')}.name as vname
            ");
        $this->db->where("type != 'combo' AND 
                (
                    {$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR 
                    {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR
                    CONCAT({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%'
                )
            ");
        $this->db->join('product_variants', 'product_variants.product_id=products.id', 'left');
        $this->db->where("{$this->db->dbprefix('product_variants')}.name", null);
        $this->db->group_by('products.id')->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getBomProductNames($term, $limit = 50)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, 
            ' . $this->db->dbprefix('products') . '.code as code,
            ' . $this->db->dbprefix('products') . '.name as name, 
            ' . $this->db->dbprefix('products') . '.price as price,
            ' . $this->db->dbprefix('units') . '.name as unit, 
            ' . $this->db->dbprefix('products') . '.cost as cost');

        $this->db->join('units', 'units.id = products.unit', 'left');

        $this->db->where("type != 'combo' AND "
                . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR 
                ".$this->db->dbprefix('products').".code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', ".$this->db->dbprefix('products').".code, ')') LIKE '%" . $term . "%')")
            ->group_by('products.id')->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
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

    public function getProductsForCount($term, $id, $limit = 5)
    {
        $this->db->select('*')
            ->where('(' . $this->db->dbprefix('stock_count_items') . ".product_name LIKE '%" . $term . "%' OR product_code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('stock_count_items') . ".product_name, ' (', product_code, ')') LIKE '%" . $term . "%')")
            ->where('stock_count_id', $id)
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

    public function getQASuggestions($term, $warehouse_id = null, $limit = 5)
    {
        $this->db->select('products.id, code, name, warehouses_products.quantity, cost, tax_rate, type, unit, purchase_unit, tax_method')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
        $this->db->where("type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
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

    public function getStockMovement_StockCountProducts($warehouse_id, $type, $categories = null, $brands = null)
    {
        $this->db->select("
                {$this->db->dbprefix('products')}.id as id, 
                {$this->db->dbprefix('products')}.code as code, 
                {$this->db->dbprefix('products')}.name as name,
                {$this->db->dbprefix('stock_movement')}.expiry as expiry, 
                {$this->db->dbprefix('products')}.cost as cost, 
                COALESCE(SUM({$this->db->dbprefix('stock_movement')}.quantity), 0) as quantity
            ")
            ->join('stock_movement', 'stock_movement.product_id = products.id')
            ->where('products.type', 'standard')
            ->where('stock_movement.warehouse_id', $warehouse_id)
            ->group_by('stock_movement.expiry')
            ->group_by('stock_movement.product_id')
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

    public function getStockCountProducts($warehouse_id, $type, $categories = null, $brands = null)
    {
        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name,{$this->db->dbprefix('purchase_items')}.expiry as expiry, {$this->db->dbprefix('products')}.cost as cost, COALESCE(SUM({$this->db->dbprefix('purchase_items')}.quantity_balance), 0) as quantity")
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->join('purchase_items' , 'purchase_items.product_id = products.id')
            ->where('warehouses_products.warehouse_id', $warehouse_id)
            ->where('products.type', 'standard')
            ->where('purchase_items.status', 'received')
            ->where('purchase_items.warehouse_id', $warehouse_id)
            ->group_by('purchase_items.expiry')
            ->group_by('purchase_items.product_id')
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

    public function setRack($data)
    {
        if ($this->db->update('warehouses_products', ['rack_id' => $data['rack_id']], ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']])) {
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
            $clause = ['product_id' => $data['product_id'], 'option_id' => $data['option_id'], 'warehouse_id' => $data['warehouse_id'], 'expiry' => $data['expiry'], 'status' => 'received'];
            $qty    = $data['type'] == 'subtraction' ? 0 - $data['quantity'] : 0 + $data['quantity'];
            $this->site->setPurchaseItem_($clause, $qty);
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

    public function updatePrice($data = [])
    {
        if ($this->db->update_batch('products', $data, 'code')) {
            return true;
        }
        return false;
    }
    public function UpdateProductBYID($id, $data = [])
    {
        $data = [ 'quantity' => 2 ];
        if ($this->db->update('products', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function BlockProductBYID($id, $data = [])
    {
        $data = [ 'quantity' => -2 ];
        if ($this->db->update('products', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants, $addOn_items, $product_options, $unit_arr, $product_account = array(), $bom_items = false, $formulation_items = false, $product_units = null, $warehouse_racks = null)
    {
        if ($this->db->update('products', $data, ['id' => $id])) {
            if ($product_account) {
                if ($this->getProductAccByProductId($id)) {
                    $this->db->update('account_product', $product_account, array('product_id' => $id));
                } else {
                    $product_account['product_id'] = $id;
                    $this->db->insert("account_product",$product_account);
                }
            }
            if($bom = $this->getBomByProductID($id)){
                $this->db->delete('boms', array('product_id' => $id));
                $this->db->delete('bom_items', array('bom_id' => $bom->id));
            }
            if ($bom_items) {
                $this->db->delete('bom_products', array('standard_product_id' => $id));
                foreach ($bom_items as $item) {
                    $item['standard_product_id'] = $id;
                    $this->db->insert('bom_products', $item);
                }
            }
            
            if ($formulation_items) {
                foreach ($formulation_items as $item) {
                    $item['main_product_id'] = $id;
                    $item['for_product_id'] = $id;
                    $this->db->insert('formulation_products', $item);
                }
            }
            if ($items) {
                $this->db->delete('combo_items', ['product_id' => $id]);
                foreach ($items as $item) {
                    $item['product_id'] = $id;
                    $this->db->insert('combo_items', $item);
                }
            }
            if ($unit_arr) {
                $this->db->delete('cost_price_by_units',['product_id'=>$id]);
                foreach($unit_arr as $unit){
                    $unit['product_id']=$id;
                    $this->db->insert('cost_price_by_units',$unit);
                }
            }
            if ($product_units) {
				$this->db->delete('product_units', array('product_id' => $id));
				foreach ($product_units as $pr_unit) {
					$pr_unit['product_id'] = $id;
					$this->db->insert('product_units', $pr_unit);
				}
			}
            if ($product_options) {
                $this->db->delete('product_options', ['product_id' => $id]);
                foreach ($product_options as $option) {
                    $options = $this->site->getOptionByID($option['option_id']);
                    $last = $this->site->getLastNum($id,$option['option_id']);
                    $option['product_id'] = $id;
                    $option['name'] = $options->name;
                    if ($last) {
                        $option['start_no'] = $last->max_serial;
                        $option['last_no'] = $last->max_serial;
                    }
                    $this->db->insert('product_options', $option);
                }
            }
            $this->db->delete('addon_items', ['product_id' => $id]);
            foreach ($addOn_items as $item) {
                $item['product_id'] = $id;
                $this->db->insert('addon_items', $item);
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
            if (!empty($warehouse_racks)) {
                foreach ($warehouse_racks as $warehouse_rack) {
                    $this->site->setWarehouseProductRack($warehouse_rack['warehouse_id'], $id, $warehouse_rack['rack_id']);
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
            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', ['product_id' => $id, 'photo' => $photo]);
                }
            }
            // $this->site->syncQuantity(null, null, null, $id);
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
    public function getBoms(){
        $q = $this->db->get("boms");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getBomByID($id = false){
        $q = $this->db->get_where("boms",array("id"=>$id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getBomItems($bom_id = false){
        $this->db->select("bom_items.*, products.code as product_code,products.name as product_name,products.cost as product_cost");
        $this->db->join("products","products.id = bom_items.product_id","inner");
        $q = $this->db->get_where("bom_items",array("bom_items.bom_id"=>$bom_id));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
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
        $this->db->select('units.name as unit,bom.*, bom_items.*, COALESCE(WP.qty , 0) as qoh')
                 ->join('bom_items', 'bom.id = bom_items.bom_id')
                 ->join('products', 'bom_items.product_id = products.id')
                 ->join($wp,'WP.product_id = bom_items.product_id', 'left')
                 ->join('units', 'units.id = products.unit', 'left')
                 ->where(array('bom.id'=>$id));
        $q = $this->db->get('bom');
        if ($q->num_rows() > 0) {
            return $q->result();
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


    public function getConvertItems($convert_id = false)
    {
        $this->db->select("products.code,products.name,convert_items.*");
        $this->db->join("products","products.id = convert_items.product_id","left");
        $q = $this->db->get_where("convert_items",array("convert_id"=>$convert_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
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
        $q = $this->db->get_where('convert_items', array('convert_id' => $id, 'bpas_convert_items.status' => 'add'));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function addConvert($data = false, $raw_materials = false, $finished_goods = false, $stockmoves = false, $accTrans =false)
    {
        if ($this->db->insert('converts', $data)) {
            $convert_id = $this->db->insert_id();
            foreach ($raw_materials as $raw_material) {
                $raw_material['convert_id'] = $convert_id;
                $this->db->insert('convert_items', $raw_material);
            }
            foreach ($finished_goods as $finished_good) {
                $finished_good['convert_id'] = $convert_id;
                $this->db->insert('convert_items', $finished_good);
            }
            foreach($stockmoves as $stockmove){
                $stockmove['transaction_id'] = $convert_id;
                $this->db->insert('stock_movement', $stockmove);
                if($stockmove['quantity'] > 0){
                    $cal_cost = $this->site->updateAVGCost($stockmove['product_id'],"Convert",$convert_id);
                }
            }
            if($accTrans){
                foreach($accTrans as $accTran){
                    $accTran['tran_no'] = $convert_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }           
            return true;
        }
        return false;
    }
    
    public function updateConvert($id = false, $data = false, $raw_materials = false, $finished_goods = false, $stockmoves = false, $accTrans =false)
    { 
        if ($this->db->update('converts', $data, array('id' => $id))) {
            $this->db->delete('convert_items', array('convert_id' => $id));
            $this->site->deleteStockmoves('Convert',$id);
            $this->site->deleteAccTran('Convert',$id);
            if($raw_materials){
                $this->db->insert_batch('convert_items', $raw_materials);
            }
            if($finished_goods){
                $this->db->insert_batch('convert_items', $finished_goods);
            }
            foreach($stockmoves as $stockmove){
                $this->db->insert('stock_movement', $stockmove);
                if($stockmove['quantity'] > 0 ){
                    $cal_cost = $this->site->updateAVGCost($stockmove['product_id'],"Convert",$id);
                }
            }
            if($accTrans){
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            return true;
        }
        return false;
    }
    public function deleteConvert($id = false)
    {
        if($id && $id > 0){ 
            $convertItems = $this->getConvertItems($id);
            if ($this->db->delete('converts', array('id' => $id)) && 
                $this->db->delete('convert_items', array('convert_id' => $id))) {
                $this->site->deleteStockmoves('Convert',$id);
                $this->site->deleteAccTran('Convert',$id);
                if($convertItems){
                    foreach($convertItems as $convertItem){
                        $this->site->updateAVGCost($convertItem->product_id);
                    }               
                }
                return true;
            }
        }
        return FALSE;
    }
    public function getFinishGoodBomQty($bom_id = false){
        $this->db->select("sum(quantity) as quantity");
        $this->db->where("bom_id",$bom_id);
        $this->db->where("type","finished_good");
        $q = $this->db->get("bom_items");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function ConvertDeduct($id)
    {
       $this->db->select('product_name, product_code,unit,'.
            $this->db->dbprefix('convert_items').'.quantity AS Cquantity,'.
            $this->db->dbprefix('convert_items').'.cost AS Ccost,'.
            $this->db->dbprefix('products').'.cost AS Pcost, product_variants.name as variant, product_variants.qty_unit, convert.noted')
                ->join('products', 'products.id=convert_items.product_id', 'left')
                ->join('product_variants', 'product_variants.id=convert_items.option_id', 'left')
                ->join('convert', 'convert_items.convert_id = convert.id', 'left');
        $q = $this->db->get_where('convert_items', array('convert_id' => $id, 'bpas_convert_items.status' => 'deduct'));
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
        $q = $this->db->get("projects_plan");
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
        $this->db->where('id', $id);
        $q = $this->db->get('enter_using_stock');
        if ($q) {
            return $q->row();
        } else {
            return false;
        }
    }

    public function deleteUsingStock($id = false)
    {
        if ($id && $id > 0) {
            if ($this->db->delete('enter_using_stock', array('id' => $id))) {
                $return_using_stocks = $this->getUsingStockByUsingID($id);
                if ($return_using_stocks) {
                    foreach ($return_using_stocks as $return_using_stock) {
                        $return_id = $return_using_stock->id;
                        if ($this->db->delete('enter_using_stock', array('id' => $return_id))) {
                            $this->db->delete('enter_using_stock_items', array('using_stock_id' => $return_id));
                            $this->site->deleteStockmoves('UsingStock', $return_id);
                            $this->site->deleteAccTran('UsingStock', $return_id);
                        }
                    }
                }
                $this->site->deleteStockmoves('UsingStock', $id);
                $this->site->deleteAccTran('UsingStock', $id);
                if ($this->Settings->accounting_method == '0') {
                    $items = $this->getUsingStockItems($id);
                    foreach ($items as $item) {
                        $this->site->updateFifoCost($item->product_id);
                    }
                } else if ($this->Settings->accounting_method == '1') {
                    $items = $this->getUsingStockItems($id);
                    foreach ($items as $item) {
                        $this->site->updateLifoCost($item->product_id);
                    }               
                } else if ($this->Settings->accounting_method == '3') {
                    $items = $this->getUsingStockItems($id);
                    foreach ($items as $item) {
                        $this->site->updateProductMethod($item->product_id);
                    }               
                }
                $this->db->delete('enter_using_stock_items', array('using_stock_id' => $id));
                return true;
            }
        }
        return FALSE;
    }

    public function getUsingStockItems($using_id = false)
    {
        $this->db->select('enter_using_stock_items.*, product_variants.name as variant, products.code, products.name, units.name as unit_name')
            ->from('enter_using_stock_items')
            ->join('products', 'products.id=enter_using_stock_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=enter_using_stock_items.option_id', 'left')
            ->join('units','units.id = enter_using_stock_items.product_unit_id','left')
            ->group_by('enter_using_stock_items.id')
            ->where('using_stock_id', $using_id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getUsingStockByUsingID($using_id = false)
    {
        $q = $this->db->get_where('enter_using_stock', array('using_id' => $using_id));
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
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
        $q = $this->db->get();
        if ($q) {
            return $q->result();
        } else {
            return false;
        }
    }
    public function getUsingStockProducts($term, $warehouse_id, $plan = NULL, $address = NULL, $limit = 100)
    {
        $this->db->where("(type = 'standard' OR type = 'asset') AND (bpas_products.name LIKE '%" . $term . "%' OR bpas_products.code LIKE '%" . $term . "%' OR  concat(bpas_products.name, ' (', bpas_products.code, ')') LIKE '%" . $term . "%')");
        if ($plan) {
            $project_plan_qty = "(
                SELECT 
                    product_id, 
                    COALESCE(quantity_balance, 0) as project_qty 
                FROM 
                    bpas_projects_plan_items 
                WHERE 
                    bpas_projects_plan_items.project_plan_id = $plan
                AND (bpas_projects_plan_items.product_code LIKE '%$term%' OR bpas_projects_plan_items.product_name LIKE '%$term%' OR CONCAT(bpas_projects_plan_items.product_name, ' (', bpas_projects_plan_items.product_code, ')') LIKE '%$term%')
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
                AND (bpas_enter_using_stock_items.code LIKE '%$term%' OR bpas_enter_using_stock_items.product_name LIKE '%$term%' OR CONCAT(bpas_enter_using_stock_items.product_name, ' (', bpas_enter_using_stock_items.code, ')') LIKE '%$term%')
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
                AND (bpas_enter_using_stock_items.code LIKE '%$term%' OR bpas_enter_using_stock_items.product_name LIKE '%$term%' OR CONCAT(bpas_enter_using_stock_items.product_name, ' (', bpas_enter_using_stock_items.code, ')') LIKE '%$term%')
                AND bpas_enter_using_stock.type = 'return'
            ) return_using_stock";
        }
        $this->db->where("warehouses_products.warehouse_id", $warehouse_id);
        $this->db->limit($limit);
        if ($plan) {
            $this->db->select('bpas_products.*, bpas_products.id as id, bpas_warehouses_products.quantity as qoh, bpas_units.name as unit_name, (COALESCE('.$this->db->dbprefix("projects_plan").'.project_qty,0) - COALESCE(using_stock.using_qty, 0) + COALESCE(return_using_stock.using_qty, 0) ) as project_qty, projects_plan.product_id as in_plan');
        } else {
            $this->db->select('bpas_products.*, bpas_products.id as id, bpas_warehouses_products.quantity as qoh, bpas_units.name as unit_name');
        }
        $this->db->join('warehouses_products', 'warehouses_products.product_id = products.id', 'left');
        $this->db->join('units', 'units.id = products.unit', 'left');
        if ($plan) {
            $this->db->join($project_plan_qty, 'projects_plan.product_id = products.id', 'left');
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
        $variant = $this->db->select("products.*, '1' as measure_qty, product_variants.name as unit_variant, COALESCE(bpas_product_variants.quantity, 0) as qty_unit ")
                            ->from("products")
                            ->where("products.id",$id)
                            ->join("product_variants","products.id=product_variants.product_id","left")
                            ->order_by('product_variants.name', 'ASC')
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
            
        $this->db->select('projects_plan_items.*, using_stock.using_qty, return_using_stock.reutn_using_qty')
                 ->from('projects_plan_items')
                 ->join($using_qty, 'using_stock.code = projects_plan_items.product_code', 'left')
                 ->join($return_using_qty, 'using_stock.code = projects_plan_items.product_code', 'left')
                 ->where(array('projects_plan_items.project_plan_id' => $plan_id, 'projects_plan_items.product_code' => $product_code));
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function get_enter_using_stock_by_id($id)
    {
        $this->db->select('enter_using_stock.*, warehouses.name, users.first_name, users.last_name, projects_plan.title, CONCAT(bpas_products.cf4, "", bpas_products.cf3) as address');
        $this->db->from('enter_using_stock');
        $this->db->join('projects_plan','enter_using_stock.plan_id=projects_plan.id', 'left');
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

        $this->db->select('bpas_companies.*');
        $this->db->from('bpas_enter_using_stock');
        $this->db->join('bpas_companies','bpas_enter_using_stock.biller_id = bpas_companies.id','left');
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
        $this->db->join('bpas_companies','bpas_enter_using_stock.biller_id = bpas_companies.id','left');
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
    public function getAuInfoByref($ref)
    {
        $this->db->select('bpas_users.username')
                 ->from('bpas_enter_using_stock')
                 ->join('bpas_users', 'bpas_enter_using_stock.authorize_id = bpas_users.id','left')
                 ->where('bpas_enter_using_stock.reference_no',$ref);
        $q = $this->db->get();
        if ($q->num_rows() > 0 ) {
            return $q->row();
        }
        return FALSE;
    }
    public function get_enter_using_stock_by_ref($ref)
    {
        $this->db->select('enter_using_stock.*,warehouses.name,users.first_name,users.last_name, projects_plan.title, concat(bpas_products.cf3, " ", bpas_products.cf4) as address');
        $this->db->from('enter_using_stock');
        $this->db->join('warehouses','warehouses.id=enter_using_stock.warehouse_id','left');
        $this->db->join('projects_plan','projects_plan.id=enter_using_stock.plan_id','left');
        $this->db->join('products','products.id = enter_using_stock.address_id','left');
        $this->db->join('users','users.id=enter_using_stock.employee_id','left');
        $this->db->where('enter_using_stock.reference_no',$ref);
        $q=$this->db->get();
        if($q){
            return $q->row();
        }else{
            return false;
        }
    } 
    public function getUsingStockByCustomerID($id)
    {
        $this->db->select('bpas_companies.*');
        $this->db->from('bpas_enter_using_stock');
        $this->db->join('bpas_companies','bpas_enter_using_stock.customer_id = bpas_companies.id','left');
        $this->db->where('bpas_enter_using_stock.id', $id);
        $q=$this->db->get();
        if($q){
            return $q->row();
        }else{return false;}
    }
    public function get_all_enter_using_stock($id) 
    {
        $this->db->select('enter_using_stock.*, users.username, companies.company, warehouses.name as warehouse_name, users.first_name, users.last_name, projects_plan.title as plan');
        $this->db->join('warehouses', 'warehouses.id=enter_using_stock.warehouse_id', 'left');
        $this->db->join('users', 'users.id=enter_using_stock.employee_id', 'inner');
        $this->db->join('companies', 'companies.id = enter_using_stock.biller_id', 'inner');
        $this->db->join('projects_plan', 'projects_plan.id = enter_using_stock.plan_id', 'left');
        $q = $this->db->get_where('enter_using_stock', array('enter_using_stock.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function updateProperty($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants)
    {
        if ($this->db->update('products', $data, ['id' => $id])) {
      
            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', ['product_id' => $id, 'photo' => $photo]);
                }
            }

            $this->site->syncQuantity(null, null, null, $id);
            return true;
        } else {
            return false;
        }
    }
    public function getAllPrice_Groups()
    {
        $this->db->select('*')->order_by('name', 'ASC')->where('type', 'price_group');
        $q = $this->db->get('price_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getPriceGroupsByProductID($id)
    {
        $this->db->select('*')->where('product_id', $id)->join('price_groups', 'price_groups.id=product_prices.price_group_id', 'left')->order_by('price_groups.name', 'ASC');
        $q = $this->db->get('product_prices');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllProduct_Prices($product = null)
    {
        $this->db->select("
            {$this->db->dbprefix('product_prices')}.product_id, 
            {$this->db->dbprefix('products')}.code, 
            {$this->db->dbprefix('products')}.name,
            {$this->db->dbprefix('products')}.cost,             
            {$this->db->dbprefix('products')}.price as ori_price, 
            GROUP_CONCAT({$this->db->dbprefix('product_prices')}.price_group_id SEPARATOR '__') AS groups, 
            GROUP_CONCAT({$this->db->dbprefix('product_prices')}.price SEPARATOR '__') AS price_groups", false
        );
        $this->db->join('products', 'products.id=product_prices.product_id', 'left');
        if($product){
            $this->db->where('products.id', $product);
        }
        $this->db->group_by('product_id');
        $this->db->order_by('products.code', 'ASC');
        $q = $this->db->get('product_prices');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    //------------
    public function getAllOptions()
    {
        $q = $this->db->get('options');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getOptionProduct($pid)
    {
        $q = $this->db->get_where('product_options', ['product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getConvertItemsId($convert_id)
    {
        $q = $this->db->get_where('convert_items', array('convert_id' => $convert_id) );
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getConvertItemsByIDPID($convert_id, $product_id = NULL)
    {
        if($product_id){
            $this->db->where('product_id', $product_id);
        }
        $this->db->where('convert_id', $convert_id);
        $query = $this->db->get('convert_items');
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }
    public function get_purchase_items_by_conId($id)
    {
        $q = $this->db->get_where('purchase_items', array('transaction_id' => $id, 'transaction_type' => 'CONVERT') );
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function delete_purchase_items_by_conId($id)
    {
        $this->db->where(array('transaction_type' => 'CONVERT', 'transaction_id' => $id));
        $d=$this->db->delete('purchase_items');
        if($d){
            return true;
        }return false;
    }
    public function getAddressById($plan = null)
    {
        $this->db->select('id, CONCAT(cf4, " ", cf3) AS text')
                 ->where('cf1', $plan);
        $q = $this->db->get("products");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }

        return FALSE;
    }

    public function insert_enter_using_stock($data, $items, $stockmoves = array(), $accTrans = array())
    {
        $this->db->trans_start();
        if ($this->db->insert('enter_using_stock', $data)) {
            $using_stock_id = $this->db->insert_id(); 
            foreach ($items as $item) {
                $item['using_stock_id'] = $using_stock_id;
                $this->db->insert('enter_using_stock_items', $item);
                if (!empty($data['plan_id'])) {
                    $pro_item    = $this->getProjectPlanItem($data['plan_id'], $item['product_id']);
                    $new_qty_use = $pro_item->quantity_used + $item['qty_use'];
                    $this->db->update("projects_plan_items", array("projects_plan_items.quantity_used" => $new_qty_use), array("project_plan_id" => $data['plan_id'], "product_id" => $item['product_id']));
                }
            }
            foreach ($stockmoves as $stockmove) {
				$stockmove['transaction_id'] = $using_stock_id;
				$this->db->insert('stock_movement', $stockmove);
                if ($this->site->stockMovement_isOverselling($stockmove)) {
                    return false;
                }
			}
            if ($accTrans) {
                foreach ($accTrans as $accTran) {
                    $accTran['tran_no'] = $using_stock_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            if ($this->site->getReference('es') == $data['reference_no']) {
                $this->site->updateReference('es');
            } else if ($this->site->getReference('esr') == $data['reference_no']) {
                $this->site->updateReference('esr');
            } 
        } 
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the using stock (Add:Products_model.php)');
        } else {
            return $using_stock_id;
        }
        return false;
    }
    
    public function insert_enter_using_stock_item($data)
    {
        if($data) {
            $i=$this->db->insert('enter_using_stock_items', $data);
            if($i){
                return $this->db->insert_id();
            }
        }
        return false;
    }

    public function getProjectPlanItem($plan_id, $product_id)
    {
        $q = $this->db->get_where('projects_plan_items', array('project_plan_id' => $plan_id, 'product_id' => $product_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function update_enter_using_stock($id, $data, $items, $stockmoves = [], $accTrans = [])
    {
        // var_dump($stockmoves[0]['expiry']);
        // $this->bpas->print_arrays($stockmoves[0]['expiry']);
        $this->db->trans_start();
        $oitems = $this->site->getUsingStockItemsByUsingID($id);
        $this->syncProjectPlanUsedItems($id, $data['plan_id']);
        if ($this->db->update('enter_using_stock', $data, array('id' => $id))) {
            $this->db->delete('enter_using_stock_items', array('using_stock_id' => $id));
            $this->site->deleteStockmoves('UsingStock', $id);
            $this->site->deleteAccTran('UsingStock', $id);
            if ($accTrans) {
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            foreach ($items as $item) {
                $this->db->insert('enter_using_stock_items', $item);
                if (!empty($data['plan_id'])) {
                    $pro_item     = $this->getProjectPlanItem($data['plan_id'], $item['product_id']);
                    $new_qty_use  = $pro_item['quantity_used'] + $item['qty_use'];                
                    $this->db->update("projects_plan_items", array("projects_plan_items.quantity_used" => $new_qty_use), array("project_plan_id" => $data['plan_id'], "product_id" => $item['product_id'] ));
                }
            }
            foreach ($stockmoves as $stockmove) {
                $this->db->insert('stock_movement', $stockmove);
                if ($this->site->stockMovement_isOverselling($stockmove)) {
                    return false;
                }
            }
            // foreach ($oitems as $oitem) {
            //     if ($this->site->stockMovement_isOverselling($oitem)) {
            //         return false;
            //     }
            // }
        } 
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while updating the using stock (Update:Products_model.php)');
        } else {
            return $id;
        }
        return false;
    }

    public function returnUsingStock($data, $items, $stockmoves = [], $accTrans = [])
    {
        if ($this->db->insert('enter_using_stock', $data)) {
            $using_stock_id = $this->db->insert_id(); 
            if ($this->site->getReference('esr', $data['biller_id']) == $data['reference_no']) {
                $this->site->updateReference('esr', $data['biller_id']);
            }
            if ($accTrans) {
                foreach($accTrans as $accTran){
                    $accTran['tran_no']         = $using_stock_id;
                    $accTran['reference_no']    = $data['reference_no'];
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            foreach($items as $item){
                $product_id     = $item['product_id'];
                $product_name   = $item['product_name'];
                $item['using_stock_id'] = $using_stock_id;
                $this->db->insert('enter_using_stock_items', $item);
                $using_stock_item = $this->db->insert_id(); 
                $pur_data = array(
                    'product_id'        => $product_id,
                    'product_code'      => $item['code'],
                    'product_name'      => $product_name,
                    'net_unit_cost'     => $item['cost'],
                    'option_id'         => $item['option_id'],
                    'quantity'          => abs($item['qty_use']),
                    'reference'         => $data['using_reference_no'],
                    'warehouse_id'      => $item['warehouse_id'],
                    'date'              => $data['date'],
                    'expiry'            => $item['expiry'],
                    'status'            => 'received',
                    'quantity_balance'  => abs($item['qty_use']),
                );
                $qty_use = $item['qty_use'];
                $this->db->insert('purchase_items', $pur_data);
                $this->site->syncProductQty($product_id, $item['warehouse_id']);

                if($item['option_id']) {
                    $this->site->syncVariantQty($item['option_id'], $item['warehouse_id'], $product_id);
                }
            }  
            return true;
        } else {
            return false;
        }
    }
    
    public function syncProjectPlanUsedItems($stock_id, $plan_id) 
    {
        if (!empty($plan_id)) {
            $using_stock = $this->site->getUsingStockById($stock_id);
            $using_item  = $this->site->getUsingStockItemsByUsingID($stock_id);
            foreach ($using_item as $item) {
                $product     = $this->site->getProductByCode($item->code);
                $pro_item    = $this->getProjectPlanItem($plan_id, $product->id);
                $new_qty_use = (isset($pro_item->quantity_used) ? $pro_item->quantity_used : 0) - (isset($item->qty_use) ? $item->qty_use : 0);
                $this->db->update("projects_plan_items", array("projects_plan_items.quantity_used" => $new_qty_use), array("project_plan_id" => $plan_id, "product_id" => $product->id ));
            }
        }
    }

    private function syncUsingStock($ref, $stock_id, $plan_id)
    {
        $using_stock = $this->site->getUsingStockById($stock_id);
        $using_item  = $this->site->getUsingStockByRef($ref);
        $del_pu_item = $this->delete_purchase_items_by_ref($ref);
        $del_en_item = $this->delete_enter_items_by_ref($ref);
        foreach($using_item as $item){
            $product     = $this->site->getProductByCode($item->code);
            $pro_item    = $this->getProjectPlanItem($plan_id, $product->id);
            $new_qty_use = (isset($pro_item->quantity_used) ? $pro_item->quantity_used : 0) - (isset($item->qty_use) ? $item->qty_use : 0);
            $this->db->update("projects_plan_items", array("projects_plan_items.quantity_used" => $new_qty_use), array("project_plan_id" => $plan_id, "product_id" => $product->id ));
            $this->db->delete("inventory_valuation_details",array('field_id'=>$item->id, 'type' => 'USING STOCK'));
            $this->site->syncQuantity(NULL, NULL, NULL, $product->id);
        }
    }

    public function delete_purchase_items_by_ref($reference_no)
    {
        $this->db->where('reference', $reference_no);
        $d=$this->db->delete('purchase_items');
        if($d){
            return true;
        }return false;
    }

    public function delete_enter_items_by_ref($reference_no)
    {
        $this->db->where('reference_no', $reference_no);
        $d=$this->db->delete('enter_using_stock_items');
        if($d){
            return true;
        }return false;
    }
    
    public function update_enter_using_stock_item($data,$item_id)
    {
        $this->db->where('id',$item_id);
        $i = $this->db->update('enter_using_stock_items', $data);
        if($i){
            return true;
        }else{return false;}
    }
    
    public function delete_update_stock_item($id)
    {
        $d = $this->db->delete('enter_using_stock_items', array('id' => $id));
    }

    public function getUsingStockItem($item_code,$reference_no)
    {
        $this->db->where('code',$item_code);
        $this->db->where('reference_no',$reference_no);
        $q=$this->db->get('enter_using_stock_items');
        if($q){
            return $q->row();
        }return false;
    }
    
    public function getUsingStockReturnItemByRef($ref,$wh_id=NULL)
    {
        $this->db->select('enter_using_stock_items.id as e_id,
                                    enter_using_stock_items.code as product_code,
                                    enter_using_stock_items.description,
                                    enter_using_stock_items.reason,
                                    enter_using_stock_items.qty_use,
                                    enter_using_stock_items.qty_by_unit,
                                    enter_using_stock_items.unit,
                                    enter_using_stock_items.warehouse_id as wh_id,
                                    products.name,
                                    products.cost,
                                    products.code as product_code,
                                    products.id as product_id,
                                    sum(bpas_warehouses_products.quantity) as quantity,
                                    products.unit as unit_type,
                                    ,bpas_enter_using_stock_items.qty_use as qty_use_from_using_stock
                                    
                        ');
        $this->db->from('enter_using_stock_items');
        $this->db->join('products','enter_using_stock_items.code=products.code');
        $this->db->join('warehouses_products','products.id=warehouses_products.product_id');
        $this->db->join('enter_using_stock','enter_using_stock_items.reference_no=enter_using_stock.reference_no');
        $this->db->where('enter_using_stock_items.reference_no',$ref);
    
        $this->db->group_by('e_id');
        $q=$this->db->get();
        if($q){
            return $q->result();
        } else { 
            return false; 
        }
    }

    public function getProductByType($type = null, $categories = null, $brands = null) 
    {
        $this->db->select(" {$this->db->dbprefix('products')}.* ");
        $this->db->from('products');
        if ($type == 'partial') {
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
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductCost_Price_By_Unit($type = null, $categories = null, $brands = null) 
    {
        $this->db->select(" 
            {$this->db->dbprefix('products')}.id as product_id, 
            {$this->db->dbprefix('products')}.code,
            {$this->db->dbprefix('products')}.name,
            {$this->db->dbprefix('units')}.id as unit_id,
            {$this->db->dbprefix('units')}.code as unit_code,
            {$this->db->dbprefix('units')}.name as unit_name,
            COALESCE({$this->db->dbprefix('cost_price_by_units')}.cost, 0) AS cost,
            COALESCE({$this->db->dbprefix('cost_price_by_units')}.price, 0) AS price
        ");
        $this->db->from('products');
        $this->db->join('units', 'products.unit=units.id OR products.unit=units.base_unit', 'left');
        $this->db->join('cost_price_by_units', 'products.id=cost_price_by_units.product_id AND units.id=cost_price_by_units.unit_id', 'left');
        $this->db->where('products.type !=', 'asset');
        if ($type == 'partial') {
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
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }

        return false;
    }

    public function addProducts_Cost_Price($data) 
    {
        if (!empty($data)) {
            foreach ($data as $item) {
                $product = $this->site->getProductByID($item['product_id']);
                $q = $this->db->get_where('cost_price_by_units', ['product_id' => $item['product_id'], 'unit_id' => $item['unit_id']], 1);
                if ($q->num_rows() > 0) {
                    $this->db->update('cost_price_by_units', $item, ['id' => $q->row()->id]);
                } else {
                    $this->db->insert('cost_price_by_units', $item);
                }

                if ($product->unit == $item['unit_id']) {
                    $this->db->update('products', ['cost' => $item['cost'], 'price' => $item['price']], ['id' => $product->id]);
                }
            }
            return true;
        }
         return false;
    }

    public function getAllProductsExpityDate($product_id)
    {
        $this->db->select('' . $this->db->dbprefix('warehouses') . '.*, ' . $this->db->dbprefix('purchase_items') . '.id, purchase_id, transfer_id, product_id, product_code, product_name, option_id, SUM(quantity_balance) as quantity_balance, subtotal, expiry, unit_quantity, quantity_received ');
        $this->db->join('purchase_items', 'warehouses.id = purchase_items.warehouse_id', 'left');
        $this->db->where('product_id', $product_id)->group_by("expiry")->group_by("warehouse_id")->order_by('warehouses.id','desc');
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function check_valid_expiry($product_id, $expiry) 
    {
        $q = $this->db->get_where('purchase_items', ['product_id' => $product_id, 'expiry' => $expiry], 1);
        if ($q->num_rows() > 0) {
            return true;
        }
        return false;
    }

    public function get_stock_expiry($product_id, $warehouse_id, $expiry) 
    {
        $this->db->select("
                {$this->db->dbprefix('purchase_items')}.product_id, 
                {$this->db->dbprefix('purchase_items')}.product_code, 
                {$this->db->dbprefix('purchase_items')}.product_name, 
                {$this->db->dbprefix('purchase_items')}.product_unit_id, 
                SUM({$this->db->dbprefix('purchase_items')}.quantity_balance) as quantity_balance,
            ");
        $this->db->from('warehouses');
        $this->db->join('purchase_items', 'purchase_items.warehouse_id=warehouses.id', 'left');
        $this->db->where('purchase_items.product_id', $product_id);
        $this->db->where('warehouses.id', $warehouse_id);
        $this->db->where('purchase_items.expiry', $expiry);
        $this->db->where('purchase_items.status', 'received');
        $this->db->limit(1);

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getProductAccByProductId($product_id = false)
    {
        $q = $this->db->get_where('account_product', array('product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
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
    public function deleteBook($id)
    {
        if ($this->db->delete('products', ['type'=>'book','id' => $id]) && $this->db->delete('warehouses_products', ['product_id' => $id])) {
            $this->db->delete('warehouses_products_variants', ['product_id' => $id]);
            $this->db->delete('product_variants', ['product_id' => $id]);
            $this->db->delete('product_photos', ['product_id' => $id]);
            $this->db->delete('product_prices', ['product_id' => $id]);
            return true;
        }
        return false;
    }

    public function getBorrowBooks($term, $warehouse_id,$limit = 100)
    {
        $this->db->where("(type = 'book') AND (bpas_products.name LIKE '%" . $term . "%' OR bpas_products.code LIKE '%" . $term . "%' OR  concat(bpas_products.name, ' (', bpas_products.code, ')') LIKE '%" . $term . "%')");

        $this->db->where("warehouses_products.warehouse_id", $warehouse_id);
        $this->db->limit($limit);
        $this->db->select('bpas_products.*, bpas_warehouses_products.quantity as qoh, bpas_units.name as unit_name');
        $this->db->join('warehouses_products', 'warehouses_products.product_id = products.id', 'left');
        $this->db->join('units', 'units.id = products.unit', 'left');
        $this->db->group_by('products.id');
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function add_borrowBook($data)
    {
        if($data) {
            $i=$this->db->insert('enter_using_stock_items', $data);
            if($i){
                return $this->db->insert_id();
            }
        }
        return false;
    }

    public function getProductRewardNames($term, $warehouse_id, $reward_category, $reward_type, $limit = 15)
    {
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";
        $this->db->select("
                rewards.id AS reward_id, 
                exchange_products.*,
                exchange_products.id AS id, 
                FWP.quantity as quantity
            ");
        $this->db->from('rewards');
        $this->db->join('products exchange_products', 'exchange_products.id = rewards.exchange_product_id', 'inner');
        $this->db->join('products receive_products', 'receive_products.id = rewards.receive_product_id', 'left');
        $this->db->join($wp, 'FWP.product_id = rewards.exchange_product_id', 'left');   
        if ($this->Settings->overselling && (!empty($warehouse) && $warehouse->overselling)) {
            $this->db->where("
                (
                    exchange_products.name LIKE '%" . $term . "%' OR 
                    exchange_products.code LIKE '%" . $term . "%' OR  
                    CONCAT(exchange_products.name, ' (', exchange_products.code, ')') LIKE '%" . $term . "%'
                )
            ");
        } else {
            if ($reward_category == 'customer') {
                $this->db->where("
                    (
                        exchange_products.name LIKE '%" . $term . "%' OR 
                        exchange_products.code LIKE '%" . $term . "%' OR  
                        CONCAT(exchange_products.name, ' (', exchange_products.code, ')') LIKE '%" . $term . "%'
                    )
                ");
            } else {
                $this->db->where("
                    (exchange_products.track_quantity = 0 OR FWP.quantity > 0) AND FWP.warehouse_id = '" . $warehouse_id . "' AND 
                    (
                        exchange_products.name LIKE '%" . $term . "%' OR 
                        exchange_products.code LIKE '%" . $term . "%' OR  
                        CONCAT(exchange_products.name, ' (', exchange_products.code, ')') LIKE '%" . $term . "%'        
                    )
                ");
            }
        }
        $this->db->where('rewards.category', $reward_category);
        $this->db->where('rewards.type', $reward_type);
        $this->db->where("exchange_products.type !=", 'asset');
        $this->db->group_by('exchange_products.id');
        $this->db->order_by('exchange_products.name ASC');
        $this->db->limit($limit);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getRewardByID($id)
    {
        $this->db->select("
                {$this->db->dbprefix('rewards')}.id AS id, 
                {$this->db->dbprefix('rewards')}.category AS category, 
                {$this->db->dbprefix('rewards')}.type AS type, 
                CONCAT(
                    {$this->db->dbprefix('exchange_products')}.name, ' (', 
                    {$this->db->dbprefix('exchange_products')}.code, ')'
                ) AS exchange_label, 
                {$this->db->dbprefix('exchange_products')}.name AS exchange_product, 
                {$this->db->dbprefix('rewards')}.exchange_product_id, 
                {$this->db->dbprefix('rewards')}.exchange_quantity, 
                {$this->db->dbprefix('rewards')}.amount, 
                CONCAT(
                    {$this->db->dbprefix('receive_products')}.name, ' (', 
                    {$this->db->dbprefix('receive_products')}.code, ' )'
                ) AS receive_label, 
                {$this->db->dbprefix('receive_products')}.name AS receive_product, 
                {$this->db->dbprefix('rewards')}.receive_product_id, 
                {$this->db->dbprefix('rewards')}.receive_quantity,
                {$this->db->dbprefix('rewards')}.interest
            ");
        $this->db->from('rewards');
        $this->db->join('products bpas_exchange_products', 'exchange_products.id = rewards.exchange_product_id', 'left');
        $this->db->join('products bpas_receive_products', 'receive_products.id = rewards.receive_product_id', 'left');
        $this->db->where('rewards.id', $id);
        $this->db->limit(1);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addRewardExchange($data = [], $items = [], $payment = [], $si_return = [], $accTrans = array(), $accTranPayments = array(), $payment_status = null)
    {  
        $this->db->trans_start();
        if ($this->db->insert('rewards_exchange', $data)) {
            $reward_exchange_id = $this->db->insert_id();
            $stock_received = [
                'date'               => $data['date'],
                'reward_exchange_id' => $reward_exchange_id,
                'reference_no'       => $this->site->getReference('str'),
                'created_by'         => $data['created_by'],
                'warehouse_id'       => $data['warehouse_id'],
                'note'               => null
            ];
            //=========Add Accounting =========//
            if ($accTrans) {
                foreach ($accTrans as $accTran) {
                    $accTran['tran_no'] = $reward_exchange_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            if ((isset($payment['amount']) && $payment['amount'] == '')) {
                if ($accTranPayments) {
                    foreach($accTranPayments as $accTranPayment) {
                        $accTranPayment['tran_no']    = $reward_exchange_id;
                        $accTranPayment['payment_id'] = $reward_exchange_id;
                        $this->db->insert('gl_trans', $accTranPayment);
                    }
                }
            }
            //=========End Accounting =========//
            if ($this->site->getReference('crw') == $data['reference_no']) {
                $this->site->updateReference('crw');
            } elseif ($this->site->getReference('srw') == $data['reference_no']) {
                $this->site->updateReference('srw');
            }
            $total_qty = 0;
            foreach ($items as $item) {
                $item['reward_exchange_id'] = $reward_exchange_id;
                $this->db->insert('reward_exchange_items', $item);
                $reward_exchange_item_id = $this->db->insert_id();
                $exchange_clause = [
                    'warehouse_id' => $data['warehouse_id'], 
                    'product_id'   => $item['exchange_product_id'], 
                    'option_id'    => $item['option_id'], 
                    'expiry'       => $item['expiry'], 
                    'status'       => 'received'
                ];
                $stock_received_items[] = array(
                    'reward_exchange_item_id' => $reward_exchange_item_id,
                    'product_id'    => $item['receive_product_id'],
                    'quantity'      => $item['receive_quantity'],
                    'option_id'     => $item['option_id'],
                    'expiry'        => $item['expiry'],
                    'addition_type' => $item['addition_type'],
                );
                $total_qty += $item['receive_quantity'];
                $exchange_quantity = $data['category'] == 'customer' ? 0 + $item['exchange_quantity'] : 0 - $item['exchange_quantity'];
                $this->syncRewardExchangeQuantity($exchange_clause, $exchange_quantity);
            }
            if ($data['status'] == 'completed' && $data['type'] == 'product') {
                $stock_received['total_quantity'] = $total_qty;
                if ($this->db->insert('stock_received', $stock_received)) {
                    $stock_received_id = $this->db->insert_id();
                    $whOverselling = $this->site->getWarehouseByID($data['warehouse_id'])->overselling;
                    foreach ($stock_received_items as $stock_received_item) {
                        if ($data['category'] == 'customer') {
                            $product_stock = $this->getProductStockBalance($stock_received_item['product_id'], $stock_received_item['option_id'], $stock_received_item['expiry'], $data['warehouse_id']);
                            if (empty($product_stock) || (!empty($product_stock) && $product_stock->quantity < $stock_received_item['quantity'])) {
                               
                                if ($this->Settings->overselling != 1 || ($this->Settings->overselling == 1 && $whOverselling != 1)) {
                                    $this->session->set_flashdata('error', 'Please check out of stock.');
                                    redirect($_SERVER['HTTP_REFERER']);
                                }
                            }
                        }
                        $stock_received_item['stock_received_id'] = $stock_received_id;
                        $this->db->insert('stock_received_items', $stock_received_item);
                        $receive_clause = [
                            'warehouse_id' => $data['warehouse_id'], 
                            'product_id'   => $stock_received_item['product_id'], 
                            'option_id'    => $stock_received_item['option_id'], 
                            'expiry'       => $stock_received_item['expiry'], 
                            'status'       => 'received'
                        ];
                        $receive_quantity = $data['category'] == 'customer' ? 0 - $stock_received_item['quantity'] : 0 + $stock_received_item['quantity'];
                        $this->syncRewardExchangeQuantity($receive_clause, $receive_quantity);
                    }
                    $this->site->updateReference('str');
                }
            }
            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                $payment['reward_exchange_id'] = $reward_exchange_id;
                if ($payment['paid_by'] == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($payment['cc_no']); 
                    $this->db->update('gift_cards', ['balance' => $gc->balance-$payment['amount']], ['card_no' => $payment['cc_no']]);
                    unset($payment['gc_balance']);
                    $this->db->insert('payments', $payment);
                } else {
                    if ($payment['paid_by'] == 'deposit') {
                        $company = $this->site->getCompanyByID($data['company_id']);
                        $this->db->update(
                            'companies', 
                            [
                                'deposit_amount' => ($company->deposit_amount - $payment['amount']),
                                'deposit_amount_usd' => ($company->deposit_amount_usd - $payment['amount_usd']),
                                'deposit_amount_khr' => ($company->deposit_amount_khr - $payment['amount_khr']),
                                'deposit_amount_thb' => ($company->deposit_amount_thb - $payment['amount_thb']),
                            ], 
                            ['id' => $company->id]);
                    }
                    $this->db->insert('payments', $payment);
                }
                //=========Add Accounting =========//
                $payment_id = $this->db->insert_id();
                if($accTranPayments) {
                    foreach($accTranPayments as $accTranPayment){
                        $accTranPayment['tran_no']    = $reward_exchange_id;
                        $accTranPayment['payment_id'] = $payment_id;
                        if (empty($accTranPayment['reference_no'])) {
                            $accTranPayment['reference_no'] = $payment['reference_no'];
                        }
                        $this->db->insert('gl_trans', $accTranPayment);
                    }
                }
                //=========End Accounting =========//
                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
                if ($this->site->getReference('pp') == $payment['reference_no']) {
                    $this->site->updateReference('pp');
                }
                $this->site->syncSalePayments(null, null, $reward_exchange_id);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Sales_model.php)');
        } else {
            return $reward_exchange_id;
        }
        return false;
    }

    public function updateRewardExchange($id, $data, $items = [], $accTrans = array(), $accTranPayments = array(), $commission_product = null, $module = null)
    {  
        $this->db->trans_start();
        $this->reverseRewardExchange($id);
        if ($this->db->update('rewards_exchange', $data, ['id' => $id]) && $this->db->delete('reward_exchange_items', ['reward_exchange_id' => $id])) {
            $total_qty = 0 ;
            foreach ($items as $item) {
                $this->db->update('product_options', ['start_no' => $item['max_serial'], 'last_no' => $item['max_serial']], ['option_id' => $item['option_id']]);
                $item['reward_exchange_id'] = $id;
                $this->db->insert('reward_exchange_items', $item);
                $reward_exchange_item_id = $this->db->insert_id();
                $exchange_clause = [
                    'warehouse_id'  => $data['warehouse_id'], 
                    'product_id'    => $item['exchange_product_id'], 
                    'option_id'     => $item['option_id'], 
                    'expiry'        => $item['expiry'], 
                    'status'        => 'received'
                ];
                $stock_received_items[] = array(
                    'reward_exchange_item_id' => $reward_exchange_item_id,
                    'product_id'    => $item['receive_product_id'],
                    'quantity'      => $item['receive_quantity'],
                    'option_id'     => $item['option_id'],
                    'expiry'        => $item['expiry'],
                    'addition_type' => $item['addition_type'],
                );
                $total_qty += $item['receive_quantity'];
                $exchange_quantity = $data['category'] == 'customer' ? 0 + $item['exchange_quantity'] : 0 - $item['exchange_quantity'];
                $this->syncRewardExchangeQuantity($exchange_clause, $exchange_quantity);
            } 
            if ($data['status'] == 'completed' && $data['type'] == 'product') {
                $stock_received['total_quantity'] = $total_qty;
                if ($this->db->insert('stock_received', $stock_received)) {
                    $stock_received_id = $this->db->insert_id();
                    $whOverselling = $this->site->getWarehouseByID($data['warehouse_id'])->overselling;
                    foreach ($stock_received_items as $stock_received_item) {
                        if ($data['category'] == 'customer') {
                            $product_stock = $this->getProductStockBalance($stock_received_item['product_id'], $stock_received_item['option_id'], $stock_received_item['expiry'], $data['warehouse_id']);
                            if (empty($product_stock) || (!empty($product_stock) && $product_stock->quantity < $stock_received_item['quantity'])) {
                                if ($this->Settings->overselling != 1 || ($this->Settings->overselling == 1 && $whOverselling != 1)) {
                                    $this->session->set_flashdata('error', 'Please check out of stock.');
                                    redirect($_SERVER['HTTP_REFERER']);
                                }
                            }
                        }
                        $stock_received_item['stock_received_id'] = $stock_received_id;
                        $this->db->insert('stock_received_items', $stock_received_item);
                        $receive_clause = [
                            'warehouse_id' => $data['warehouse_id'], 
                            'product_id'   => $stock_received_item['product_id'], 
                            'option_id'    => $stock_received_item['option_id'], 
                            'expiry'       => $stock_received_item['expiry'], 
                            'status'       => 'received'
                        ];
                        $receive_quantity = $data['category'] == 'customer' ? 0 - $stock_received_item['quantity'] : 0 + $stock_received_item['quantity'];
                        $this->syncRewardExchangeQuantity($receive_clause, $receive_quantity);
                    }
                }
            }
        }  
        $this->db->trans_complete(); 
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Sales_model.php)');
        } else {
            return $id;
        }
        return false;
    }

    public function deleteRewardExchange($id)
    {
        $this->db->trans_start();
        $this->reverseRewardExchange($id);
        $this->db->trans_complete();
        if ($this->db->delete('reward_exchange_items', ['reward_exchange_id' => $id]) && $this->db->delete('rewards_exchange', ['id' => $id])) {
            $this->db->delete('payments', ['reward_exchange_id' => $id]);
            //---add account
            $this->site->deleteAccTran('Exchange', $id);
            $this->site->deleteAccTran('Payment', $id);
            //---end account---
        }
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Delete:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function syncRewardExchange($reward_category, $reward_type, $data = [])
    {
        if (!empty($data)) {
            $exchange_product_clause = [
                'warehouse_id'  => $data['warehouse_id'], 
                'product_id'    => $data['exchange_product_id'], 
                'quantity'      => $data['exchange_quantity'], 
                'option_id'     => null, 
                'expiry'        => null, 
                'status'        => 'received'
            ];
            $exchange_quantity = $reward_category == 'customer' ? 0 + $data['exchange_quantity'] : 0 - $data['exchange_quantity'];
            $this->site->setPurchaseItem_($exchange_product_clause, $exchange_quantity);
            $this->site->syncProductQty($exchange_product_clause['product_id'], $data['warehouse_id']);
            if ($data['option_id']) {
                $this->site->syncVariantQty($data['option_id'], $data['warehouse_id'], $data['product_id']);
            }
            if ($reward_type == 'product') {
                $receive_product_clause  = ['warehouse_id' => $data['warehouse_id'], 'product_id' => $data['receive_product_id'], 'quantity' => $data['receive_quantity'], 'option_id' => null, 'expiry' => null, 'status' => 'received'];
                $receive_quantity        = $reward_category == 'customer' ? 0 - $data['receive_quantity'] : 0 + $data['receive_quantity'];
                $this->site->setPurchaseItem_($receive_product_clause, $receive_quantity);
                $this->site->syncProductQty($receive_product_clause['product_id'], $data['warehouse_id']);
                if ($data['option_id']) {
                    $this->site->syncVariantQty($data['option_id'], $data['warehouse_id'], $data['product_id']);
                }
            }
        }
    }

    public function syncRewardExchangeQuantity($data = [], $quantity) 
    {   
        $this->site->setPurchaseItem_($data, $quantity);
        $this->site->syncProductQty($data['product_id'], $data['warehouse_id']);
        if ($data['option_id']) {
            $this->site->syncVariantQty($data['option_id'], $data['warehouse_id'], $data['product_id']);
        }
    }

    public function getAllRewardExchangeItems($id)
    {
        $this->db->select('
                reward_exchange_items.*, 
                exchange_products.code AS exchange_product_code,
                exchange_products.name AS exchange_product_name,
                exchange_units.code    AS exchange_unit_code,
                exchange_units.name    AS exchange_unit_name,
                receive_products.code  AS receive_product_code,
                receive_products.name  AS receive_product_name,
                receive_units.code     AS receive_unit_code,
                receive_units.name     AS receive_unit_name
            ')
        ->join('products bpas_exchange_products', 'exchange_products.id=reward_exchange_items.exchange_product_id',   'left')
        ->join('products bpas_receive_products',  'receive_products.id=reward_exchange_items.receive_product_id',      'left')
        ->join('units bpas_exchange_units',       'exchange_units.id=reward_exchange_items.exchange_product_unit_id', 'left')
        ->join('units bpas_receive_units',        'receive_units.id=reward_exchange_items.receive_product_unit_id',   'left')
        ->where('reward_exchange_items.reward_exchange_id', $id)
        ->group_by('reward_exchange_items.id')
        ->order_by('id', 'asc');
        $q = $this->db->get('reward_exchange_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
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

    public function addRewardPayment($data = [], $customer_id = null, $accTranPayments = array())
    {
        if ($this->db->insert('payments', $data)) {
            $payment_id = $this->db->insert_id();
            $sales = $this->getRewardExchangeByID($data['reward_exchange_id']);
            //=========Add Accounting =========//
            if($accTranPayments){
                foreach($accTranPayments as $accTranPayment){
                    $accTranPayment['payment_id']= $payment_id;
                    $this->db->insert('gl_trans', $accTranPayment);
                }
            }
            //=========End Accounting =========//
            if ($this->site->getReference('pay') == $data['reference_no']) {
                $this->site->updateReference('pay');
            }
            $this->site->syncRewardPayments($data['reward_exchange_id']);
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', ['balance' => ($gc->balance - $data['amount'])], ['card_no' => $data['cc_no']]);
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update(
                    'companies', 
                    [
                        'deposit_amount'     => ($customer->deposit_amount - $data['amount']),
                        'deposit_amount_usd' => ($customer->deposit_amount_usd - $data['amount_usd']),
                        'deposit_amount_khr' => ($customer->deposit_amount_khr - $data['amount_khr']),
                        'deposit_amount_thb' => ($customer->deposit_amount_thb - $data['amount_thb']),
                    ], 
                    ['id' => $customer_id]);
            }

            return true;
        }
        return false;
    }

    public function updateRewardPayment($id, $data = [], $customer_id = null, $accTranPayments = array())
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, ['id' => $id])) {
            $this->site->syncRewardPayments($data['reward_exchange_id']);
            // $this->site->deleteAccTran('Payment',$id);
            $this->site->deleteAccTranPayment('Payment', $id);
            if ($accTranPayments) {
                $this->db->insert_batch('gl_trans', $accTranPayments);
            }
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', ['balance' => ($gc->balance + $opay->amount)], ['card_no' => $opay->cc_no]);
            } elseif ($opay->paid_by == 'deposit') {
                if (!$customer_id) {
                    $sale        = $this->getRewardExchangeByID($opay->reward_exchange_id);
                    $customer_id = $sale->company_id;
                }
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update(
                    'companies',
                    [
                        'deposit_amount' => ($customer->deposit_amount + $opay->amount),
                        'deposit_amount_usd' => ($customer->deposit_amount_usd + $opay->amount_usd),
                        'deposit_amount_khr' => ($customer->deposit_amount_khr + $opay->amount_khr),
                        'deposit_amount_thb' => ($customer->deposit_amount_thb + $opay->amount_thb)
                    ], 
                    ['id' => $customer->id]);
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', ['balance' => ($gc->balance - $data['amount'])], ['card_no' => $data['cc_no']]);
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update(
                    'companies', 
                    [
                        'deposit_amount' => ($customer->deposit_amount - $data['amount']),
                        'deposit_amount_usd' => ($customer->deposit_amount_usd - $data['amount_usd']),
                        'deposit_amount_khr' => ($customer->deposit_amount_khr - $data['amount_khr']),
                        'deposit_amount_thb' => ($customer->deposit_amount_thb - $data['amount_thb'])
                    ], 
                    ['id' => $customer_id]);
            }
            return true;
        }
        return false;
    }

    public function deleteRewardPayment($id)
    {
        $opay = $this->site->getPaymentByID($id);
        if ($this->db->delete('payments', ['id' => $id])) {
            $this->site->syncRewardPayments($opay->reward_exchange_id);
            // account---
            // $this->site->deleteAccTran('Payment', $id);
            $this->site->deleteAccTranPayment('Payment', $id);
            //---end account
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                // $this->db->update('gift_cards', ['balance' => ($gc->balance + $opay->amount)], ['card_no' => $opay->cc_no]);
                $gc = $this->site->getGiftCardByNO($payment['cc_no']); 
                $this->db->update('gift_cards', ['balance' => $gc->balance-$payment['amount']], ['card_no' => $payment['cc_no']]);
            } elseif ($opay->paid_by == 'deposit') {
                $sale     = $this->getRewardExchangeByID($opay->reward_exchange_id);
                $customer = $this->site->getCompanyByID($sale->company_id);
                $this->db->update(
                    'companies', 
                    [
                        'deposit_amount' => ($customer->deposit_amount + $opay->amount),
                        'deposit_amount_usd' => ($customer->deposit_amount_usd + $opay->amount_usd),
                        'deposit_amount_khr' => ($customer->deposit_amount_khr + $opay->amount_khr),
                        'deposit_amount_thb' => ($customer->deposit_amount_thb + $opay->amount_thb),
                    ], 
                    ['id' => $customer->id]);
            }
            return true;
        }
        return false;
    }

    public function getAllRewardItems($id)
    {
        $this->db->select('reward_exchange_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.subcategory_id,products.category_id,products.other_cost, products.currency, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name,currencies.symbol as symbol')
            ->join('products', 'products.id=reward_exchange_items.receive_product_id', 'left')
            ->join('product_variants', 'product_variants.id=reward_exchange_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=reward_exchange_items.tax_rate_id', 'left')
            ->join('currencies', 'currencies.code=products.currency', 'left')
            ->group_by('reward_exchange_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('reward_exchange_items', ['reward_exchange_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getWarehouseProduct($pid, $wid)
    {
        $this->db->select(" {$this->db->dbprefix('products')}.*, {$this->db->dbprefix('warehouses_products')}.quantity as quantity ")
        ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left');
        $q = $this->db->get_where('products', ['warehouses_products.product_id' => $pid, 'warehouses_products.warehouse_id' => $wid]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function reverseAdjustment($id)
    {
        if ($products = $this->getAdjustmentItems($id)) {
            foreach ($products as $adjustment) {
                $clause = ['product_id' => $adjustment->product_id, 'warehouse_id' => $adjustment->warehouse_id, 'expiry' => $adjustment->expiry, 'option_id' => $adjustment->option_id, 'status' => 'received'];
                $qty    = $adjustment->type == 'subtraction' ? (0 + $adjustment->quantity) : (0 - $adjustment->quantity);
                $this->site->setPurchaseItem_($clause, $qty);
                $this->site->syncProductQty($adjustment->product_id, $adjustment->warehouse_id);
                if ($adjustment->option_id) {
                    $this->site->syncVariantQty($adjustment->option_id, $adjustment->warehouse_id, $adjustment->product_id);
                }
            }
        }
    }
    
    public function updateAdjustment($id, $data, $products, $stockmoves = false, $accTrans = array(), $product_serials = false)
    {
        $this->db->trans_start();
        $oitems = $this->site->getStockMovementByTransactionID($id);
        if ($this->db->update('adjustments', $data, ['id' => $id]) && $this->db->delete('adjustment_items', ['adjustment_id' => $id])) {
            $this->db->delete('product_serials', array('adjustment_id' => $id));
            //=======Add accounting =======//
            $this->site->delete_stock_movement('QuantityAdjustment', $id);
            $this->site->deleteAccTran('adjustment', $id);
            if ($accTrans) {
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            //=======End accounting =======//
            foreach ($products as $product) {
                $this->db->insert('adjustment_items', $product);
            }
            if ($stockmoves) {
                foreach ($stockmoves as $stockmove) {
                    if (isset($stockmove['reactive']) && $stockmove['reactive'] != 1) {
                        unset($stockmove['serial_no']);
                    }
                    unset($stockmove['reactive']);
                    $this->db->insert('stock_movement', $stockmove);
                    if ($this->site->stockMovement_isOverselling($stockmove)) {
                        return false;
                    }
                }
                foreach ($oitems as $oitem) {
                    if ($this->site->stockMovement_isOverselling($oitem)) {
                        return false;
                    }
                }
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the adjustment (Add:Products_model.php)');
        } else {
            return $id;
        }
        return false;
    }

    public function reverseRewardExchange($id) 
    {
        if ($reward_change_items = $this->getAllRewardExchangeItems($id)) {
            $reward_exchange = $this->getRewardExchangeByID($id);
            foreach ($reward_change_items as $item) {
                $exchange_clause = [
                    'product_id'    => $item->exchange_product_id,
                    'warehouse_id'  => $item->warehouse_id,
                    'expiry'        => $item->expiry,
                    'option_id'     => $item->option_id,
                    'status'        => 'received'
                ];
                $exchange_quantity  =  $reward_exchange->category == 'customer' ? 0 - $item->exchange_quantity : 0 + $item->exchange_quantity;
                $this->syncRewardExchangeQuantity($exchange_clause, $exchange_quantity); 
            }
        }
    }

    public function getAllRewardItems_x_Balance($id)
    {
        $this->db->select('
                reward_exchange_items.*, reward_exchange_items.id AS reward_exchange_item_id, products.code AS receive_product_code, products.name AS receive_product_name, units.id AS receive_unit_id, units.code AS receive_unit_code, units.name AS receive_unit_name, 
                tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.subcategory_id, products.category_id, products.other_cost, products.currency, 
                products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name, currencies.symbol as symbol,
                COALESCE(bpas_received.qunatity, 0) AS received_quantity
            ')
            ->join("
                    (SELECT 
                        {$this->db->dbprefix('stock_received')}.reward_exchange_id, 
                        {$this->db->dbprefix('stock_received_items')}.reward_exchange_item_id, 
                        {$this->db->dbprefix('stock_received_items')}.product_id, 
                        SUM(COALESCE({$this->db->dbprefix('stock_received_items')}.quantity, 0)) AS qunatity 
                    FROM {$this->db->dbprefix('stock_received')}
                    LEFT JOIN {$this->db->dbprefix('stock_received_items')} ON {$this->db->dbprefix('stock_received_items')}.stock_received_id = {$this->db->dbprefix('stock_received')}.id
                    GROUP BY {$this->db->dbprefix('stock_received_items')}.reward_exchange_item_id) bpas_received
                ", "{$this->db->dbprefix('received')}.reward_exchange_item_id = {$this->db->dbprefix('reward_exchange_items')}.id", "left")
            ->join('products', 'products.id=reward_exchange_items.receive_product_id', 'left')
            ->join('units', 'units.id=reward_exchange_items.receive_product_unit_id', 'left')
            ->join('product_variants', 'product_variants.id=reward_exchange_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=reward_exchange_items.tax_rate_id', 'left')
            ->join('currencies', 'currencies.code=products.currency', 'left')
            ->group_by('reward_exchange_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('reward_exchange_items', ['reward_exchange_items.reward_exchange_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductStockBalance($product_id, $option_id = null, $expiry = null, $warehouse_id = null)
    {
        $this->db->select("product_id, option_id, expiry, SUM(quantity_balance) AS quantity, warehouse_id");
        $this->db->from('purchase_items');
        $this->db->where('product_id', $product_id);
        $this->db->where('status', 'received');
        if ($expiry) { 
            $this->db->where('expiry', $expiry);
        } else {
            $this->db->where('expiry', null);
        }
        if ($option_id) { 
            $this->db->where('option_id', $option_id);
        }
        if ($warehouse_id) { 
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->group_by('product_id');
        $this->db->limit(1);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addRewardStockReceived($id, $data, $items) 
    {
        if (!empty($data)) {
            $reward_exchange = $this->getRewardExchangeByID($id);
            if ($this->db->insert('stock_received', $data)) {
                $insert_id = $this->db->insert_id();
                $this->site->updateReference('str');
                foreach($items as $item) {
                    $item['stock_received_id'] = $insert_id;
                    $this->db->insert('stock_received_items', $item);
                    $clause   = [
                        'warehouse_id' => $data['warehouse_id'], 
                        'product_id'   => $item['product_id'], 
                        'option_id'    => $item['option_id'], 
                        'expiry'       => $item['expiry'], 
                        'status'       => 'received'
                    ];
                    $quantity = $reward_exchange->category == 'customer' ? 0 - $item['quantity'] : 0 + $item['quantity'];
                    $this->syncRewardExchangeQuantity($clause, $quantity);
                }
                $this->syncRewardExchangeStatus($id);
            }
            return true;
        }
        return false;
    }

    public function syncRewardExchangeStatus($id) 
    {
        $this->db->select("COALESCE({$this->db->dbprefix('exchanged')}.quantity, 0) AS quantity, COALESCE({$this->db->dbprefix('received')}.quantity, 0) AS received");
        $this->db->from('rewards_exchange');
        $this->db->join("
            (
                SELECT {$this->db->dbprefix('rewards_exchange')}.id AS reward_exchange_id, SUM(COALESCE({$this->db->dbprefix('reward_exchange_items')}.receive_quantity, 0)) AS quantity
                FROM {$this->db->dbprefix('rewards_exchange')}
                LEFT JOIN {$this->db->dbprefix('reward_exchange_items')} ON {$this->db->dbprefix('reward_exchange_items')}.reward_exchange_id = {$this->db->dbprefix('rewards_exchange')}.id
                WHERE {$this->db->dbprefix('rewards_exchange')}.id = {$id}
                GROUP BY {$this->db->dbprefix('rewards_exchange')}.id
                LIMIT 1
            ) bpas_exchanged
        ", "{$this->db->dbprefix('exchanged')}.reward_exchange_id = {$this->db->dbprefix('rewards_exchange')}.id", "left");
        $this->db->join("
            (
                SELECT {$this->db->dbprefix('stock_received')}.reward_exchange_id, SUM(COALESCE({$this->db->dbprefix('stock_received_items')}.quantity, 0)) AS quantity
                FROM {$this->db->dbprefix('stock_received')}
                LEFT JOIN {$this->db->dbprefix('stock_received_items')} ON {$this->db->dbprefix('stock_received_items')}.stock_received_id = {$this->db->dbprefix('stock_received')}.id
                WHERE {$this->db->dbprefix('stock_received')}.reward_exchange_id = {$id}
                GROUP BY {$this->db->dbprefix('stock_received')}.reward_exchange_id
                LIMIT 1
            ) bpas_received
        ", "{$this->db->dbprefix('received')}.reward_exchange_id = {$this->db->dbprefix('rewards_exchange')}.id", "left");
        $this->db->where('rewards_exchange.id', $id);
        $this->db->group_by('rewards_exchange.id');
        $this->db->limit(1);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            $result   = $q->row();
            $quantity = $result->quantity;
            $received = $result->received;
            if ($received == 0) {
                $status = 'pending';
            } elseif ($quantity > $received && $received != 0) {
                $status = 'partial';
            } else {
                $status = 'completed';
            }
            $this->db->update('rewards_exchange', ['status' => $status], ['id' => $id]);
        }
    }

    public function getRewardStockReceivedByID($id) 
    {
        $q = $this->db->get_where('stock_received', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRewardStockReceivedItems($id) 
    {
        $this->db->select('
                stock_received_items.*, stock_received_items.quantity AS stock_received_qty, units.id AS unit, units.code as unit_code, units.name as unit_name, products.code as product_code, products.name as product_name,
                products.other_cost, products.currency, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name,currencies.symbol as symbol')
            ->join('products', 'products.id=stock_received_items.product_id', 'left')
            ->join('units', 'units.id=products.unit', 'left')
            ->join('product_variants', 'product_variants.id=stock_received_items.option_id', 'left')
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

    public function checkStockReceived($id) 
    {
        $q = $this->db->get_where('stock_received', ['reward_exchange_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return true;
        }
        return false;
    }

    public function updateRewardStockReceived($reward_exchange_id, $stock_received_id, $data, $items)
    {
        $this->db->trans_start();
        if (!empty($data)) {
            $reward_exchange = $this->getRewardExchangeByID($reward_exchange_id);
            $o_str  = $this->getRewardStockReceivedByID($stock_received_id);
            $o_stri = $this->getRewardStockReceivedItems($stock_received_id);
            if ($this->db->update('stock_received', $data, ['id' => $stock_received_id]) && $this->db->delete('stock_received_items', ['stock_received_id' => $stock_received_id])) {
                foreach($o_stri as $oitem) {
                    $o_clause   = [
                        'warehouse_id' => $data['warehouse_id'], 
                        'product_id'   => $oitem->product_id, 
                        'option_id'    => $oitem->option_id, 
                        'expiry'       => $oitem->expiry, 
                        'status'       => 'received'
                    ];
                    $o_quantity = $reward_exchange->category == 'customer' ? 0 + $oitem->quantity : 0 - $oitem->quantity;
                    $this->syncRewardExchangeQuantity($o_clause, $o_quantity);
                }
                foreach($items as $item) {
                    if ($reward_exchange->category == 'customer') {
                        $product_stock = $this->getProductStockBalance($item['product_id'], $item['option_id'], $item['expiry'], $data['warehouse_id']);
                        if (empty($product_stock) || (!empty($product_stock) && $product_stock->quantity < $item['quantity'])) {
                            if ($this->Settings->overselling != 1 || ($this->Settings->overselling == 1 && $whOverselling != 1)) {
                                $this->session->set_flashdata('error', 'Please check out of stock.');
                                redirect($_SERVER['HTTP_REFERER']);
                            }
                        }
                    }
                    $item['stock_received_id'] = $stock_received_id;
                    $this->db->insert('stock_received_items', $item);
                    $receive_clause = [
                        'warehouse_id' => $data['warehouse_id'], 
                        'product_id'   => $item['product_id'], 
                        'option_id'    => $item['option_id'], 
                        'expiry'       => $item['expiry'], 
                        'status'       => 'received'
                    ];
                    $receive_quantity = $reward_exchange->category == 'customer' ? 0 - $item['quantity'] : 0 + $item['quantity'];
                    $this->syncRewardExchangeQuantity($receive_clause, $receive_quantity);
                }
                $this->syncRewardExchangeStatus($reward_exchange_id);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Sales_model.php)');
        } else {
            return $stock_received_id;
        }
        return false;
    }

    public function deleteRewardStockReceived($id) 
    {
        if (!empty($id)) {
            $stock_received       = $this->getRewardStockReceivedByID($id);
            $stock_received_items = $this->getRewardStockReceivedItems($id);
            $reward_exchange      = $this->getRewardExchangeByID($stock_received->reward_exchange_id);
            if ($this->db->delete('stock_received', ['id' => $id]) && $this->db->delete('stock_received_items', ['stock_received_id' => $id])) {
                foreach($stock_received_items as $item) {
                    $clause   = [
                        'warehouse_id' => $stock_received->warehouse_id, 
                        'product_id'   => $item->product_id, 
                        'option_id'    => $item->option_id, 
                        'expiry'       => $item->expiry, 
                        'status'       => 'received'
                    ];
                    $quantity = $reward_exchange->category == 'customer' ? 0 + $item->quantity : 0 - $item->quantity;
                    $this->syncRewardExchangeQuantity($clause, $quantity);
                }
                $this->syncRewardExchangeStatus($reward_exchange->id);
                return true;
            }
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

    public function addConsignment($data = false, $items = false, $stockmoves = false, $accTrans = false)
    {
        $this->db->trans_start();
        if ($this->db->insert('consignments', $data)) {
            $consignment_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['consignment_id'] = $consignment_id;
                $this->db->insert('consignment_items', $item);
            }
            if ($stockmoves) {
                foreach ($stockmoves as $stockmove) {
                    $stockmove['transaction_id'] = $consignment_id;
                    $this->db->insert('stock_movement', $stockmove);
                    if ($this->site->stockMovement_isOverselling($stockmove)) {
                        return false;
                    }
                }
            }
            if ($accTrans) {
                foreach ($accTrans as $accTran) {
                    $accTran['tran_no'] = $consignment_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            if ($data['consignment_id'] > 0) {
                $this->site->syncConsignment($data['consignment_id']);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the consignment (Add:Products_model.php)');
        } else {
            return $consignment_id;
        }
        return false;
    }

    public function updateConsignment($id = false, $data = false, $items = false, $stockmoves = false, $accTrans = false)
    {
        $this->db->trans_start();
        $oitems = $this->site->getStockMovementByTransactionID($id);
        if ($id && $id > 0 && $this->db->update('consignments', $data, array('id' => $id))) {
            $this->db->delete('consignment_items', array('consignment_id' => $id));
            $this->site->deleteAccTran('Consignment', $id);
            $this->site->delete_stock_movement('Consignment', $id);
            if ($items) {
                $this->db->insert_batch('consignment_items', $items);
            }
            if ($stockmoves) {
                foreach ($stockmoves as $stockmove) {
                    $this->db->insert('stock_movement', $stockmove);
                    if ($this->site->stockMovement_isOverselling($stockmove)) {
                        return false;
                    }
                }
                foreach ($oitems as $oitem) {
                    if ($this->site->stockMovement_isOverselling($oitem)) {
                        return false;
                    }
                }
            }
            if ($accTrans) {
                $this->db->insert_batch('gl_trans', $accTrans);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the adjustment (Add:Products_model.php)');
        } else {
            return $id;
        }
        return false;
    }

    public function getConsignmentByID($id = false)
    {
        $q = $this->db->get_where('consignments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getConsigmentItems($consignment_id = false)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('consignment_items', array('consignment_id' => $consignment_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function deleteConsignment($id = false) 
    {
        $consignment = $this->getConsignmentByID($id);
        $consignment_returns = $this->getConsignmentByConsignID($id);
        if($id && $id > 0 && $this->db->delete('consignments',array('id' => $id))){
            $this->db->delete('consignment_items', array('consignment_id' => $id));
            $this->site->deleteAccTran('Consignment', $id);
            $this->site->delete_stock_movement('Consignment', $id);
            if ($consignment_returns) {
                foreach ($consignment_returns as $consignment_returns) {
                    $this->db->delete('consignments', array('id' => $consignment_returns->id));
                    $this->db->delete('consignment_items', array('consignment_id' => $consignment_returns->id));
                    $this->site->deleteAccTran('Consignment', $consignment_returns->id);
                    $this->site->delete_stock_movement('Consignment', $consignment_returns->id);
                }
            }
            if ($consignment->consignment_id > 0) {
                $consignment_id = $consignment->consignment_id;
            } else {
                $consignment_id = $id;
            }
            $this->site->syncConsignment($consignment_id);
            return true;
        }
        return false;
    }

    public function getConsignmentByConsignID($consignment_id = false) 
    {
        $q = $this->db->get_where('consignments',array('consignment_id'=>$consignment_id));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getRawProductNames($term = false, $limit = false)
    {
        $limit = $this->Settings->rows_per_page;
        $this->db->where('products.status',1);
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, unit, price, cost,' . $this->db->dbprefix('products') . '.name as name')
            ->where("type = 'raw_material' AND "
                . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->group_by('products.id')->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUnitbyProduct($pid = false, $baseunit = false)
    {
        if ($baseunit == '') {
            $baseunit = $this->getProductByID($pid)->unit;
        }
        $q = $this->db->query("SELECT
                                {$this->db->dbprefix('units')}.id,
                                {$this->db->dbprefix('units')}.name,
                                {$this->db->dbprefix('product_units')}.unit_qty,
                                {$this->db->dbprefix('product_units')}.unit_price
                            FROM
                                `bpas_units`
                            LEFT JOIN bpas_product_units ON bpas_product_units.unit_id = bpas_units.id
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

    public function getProductBomItems($pid = false)
    {
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where_in('IFNULL('.$this->db->dbprefix("bom_products").'.biller_id,0)',array(0,$this->session->userdata('biller_id')));
        }
        $this->db->where('bom_products.standard_product_id',$pid);
        $this->db->select('bom_products.*,products.name,products.code')
        ->join('products','products.id= bom_products.product_id','inner')
        ->order_by('bom_products.biller_id');
        $q = $this->db->get('bom_products');
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getBomByProductID($product_id = false)
    {
        $q = $this->db->get_where('boms',array('product_id'=>$product_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getActiveProductSerialID($product_id = false, $warehouse_id = false, $serial = false)
    {       
        if ($warehouse_id) {
            $this->db->where("warehouse_id", $warehouse_id);
        }
        if ($serial) {
            $this->db->where("(serial='" . $serial . "' OR inactive='0' OR ISNULL(inactive))");
        } else {
            $this->db->where("(inactive='0' OR ISNULL(inactive))");
        }
        $products_detail = $this->db->where("product_id", $product_id)->get("product_serials")->result();
        return $products_detail;
    }

    public function getAllProductNames($term = false, $limit = false)
    {
        $limit = $this->Settings->rows_per_page;
        $allow_category = $this->site->getCategoryByProject();
        if ($allow_category) {
            $this->db->where_in("products.category_id", $allow_category);
        }
        $this->db->where('products.status', 1);
        $this->db->select('products.id, code, name, warehouses_products.quantity, cost, tax_rate, type, unit, sale_unit, tax_method, purchase_unit')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id')
            ->where("track_quantity = '1' AND type != 'combo' AND "
                . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getProductRacks(){
        $q = $this->db->get('product_rack');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getProductUnits(){
        $this->db->select("product_units.product_id,product_units.unit_qty,units.name as unit_name");
        $this->db->join("units","units.id = product_units.unit_id","inner");
        $this->db->order_by('product_units.unit_qty','desc');
        $q = $this->db->get("product_units");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->product_id][] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getProductByOptions($pid)
    {
        $q = $this->db->get_where('product_options', ['product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getProductRingNames($term, $warehouse_id, $limit = 15)
    {
        $warehouse = $this->site->getWarehouseByID($warehouse_id);

        $this->db->select("*");
        $this->db->from('products');
        $this->db->where("products.cf1", 'ring');
        $this->db->group_by('products.id');
        $this->db->order_by('products.name ASC');
        $this->db->limit($limit);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
}