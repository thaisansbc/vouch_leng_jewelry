<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addBrand($data)
    {
        if ($this->db->insert('brands', $data)) {
            return true;
        }
        return false;
    }
    
    public function getCustomerPackageByID($id)
    {
        $q = $this->db->get_where('customer_package', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addBrands($data)
    {
        if ($this->db->insert_batch('brands', $data)) {
            return true;
        }
        return false;
    }

    public function addCategories($categories, $subcategories)
    {
        $result = false;
        if (!empty($categories)) {
            foreach ($categories as $category) {
                if ($pcategory = $this->getCategoryByCode($category['parent_id'])) {
                    $category['parent_id'] = $pcategory->id;
                    $this->db->insert('categories', $category);
                }else{
                    $this->db->insert('categories', $category); 
                }
            }
            $result = true;
        }
        if (!empty($subcategories)) {
            foreach ($subcategories as $category) {
                if (is_int($category['parent_id'])) {
                    $this->db->insert('categories', $category);
                } else {
                    if ($pcategory = $this->getCategoryByCode($category['parent_id'])) {
                        $category['parent_id'] = $pcategory->id;
                        $this->db->insert('categories', $category);
                    }
                }
            }
            $result = true;
        }
        return $result;
    }
    public function deleteCustomerpackage($id)
    {
        if ($this->db->delete('customer_package', ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function addCategory($data)
    {
        if ($this->db->insert('categories', $data)) {
            return true;
        }
        return false;
    }
    public function addCurrency($data)
    {
        if ($this->db->insert('currencies', $data)) {
            return true;
        }
        return false;
    }
    
    public function addCustomerGroup($data)
    {
        if ($this->db->insert('customer_groups', $data)) {
            return true;
        }
        return false;
    }

    public function addExpenseCategories($data)
    {
        if ($this->db->insert_batch('expense_categories', $data)) {
            return true;
        }
        return false;
    }

    public function addExpenseCategory($data)
    {
        if ($this->db->insert('expense_categories', $data)) {
            return true;
        }
        return false;
    }

    public function addGroup($data)
    {
        if ($this->db->insert('groups', $data)) {
            $gid = $this->db->insert_id();
            $this->db->insert('permissions', ['group_id' => $gid]);
            return $gid;
        }
        return false;
    }

    public function addPriceGroup($data)
    {
    
        if ($this->db->insert('price_groups', $data)) {   
            return true;
        }
        return false;
    }

    public function addTaxRate($data)
    {
        if ($this->db->insert('tax_rates', $data)) {
            return true;
        }
        return false;
    }

    public function addUnit($data)
    {
        if ($this->db->insert('units', $data)) {
            return true;
        }
        return false;
    }

    public function addVariant($data)
    {
        if ($this->db->insert('variants', $data)) {
            return true;
        }
        return false;
    }

    public function addWarehouse($data)
    {
        if ($this->db->insert('warehouses', $data)) {
            return true;
        }
        return false;
    }

    public function brandHasProducts($brand_id)
    {
        $q = $this->db->get_where('products', ['brand' => $brand_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function checkGroupUsers($id)
    {
        $q = $this->db->get_where('users', ['group_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function deleteBrand($id)
    {
        if ($this->db->delete('brands', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteCategory($id)
    {
        if ($this->db->delete('categories', ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function deleteCurrency($id)
    {
        if ($this->db->delete('currencies', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteCustomerGroup($id)
    {
        if ($this->db->delete('customer_groups', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteExpenseCategory($id)
    {
        if ($this->db->delete('expense_categories', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteGroup($id)
    {
        if ($this->db->delete('groups', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteInvoiceType($id)
    {
        if ($this->db->delete('invoice_types', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deletePriceGroup($id)
    {
        if ($this->db->delete('price_groups', ['id' => $id]) && $this->db->delete('product_prices', ['price_group_id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteTaxRate($id)
    {
        if ($this->db->delete('tax_rates', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteUnit($id)
    {
        if ($this->db->delete('units', ['id' => $id])) {
            $this->db->delete('units', ['base_unit' => $id]);
            return true;
        }
        return false;
    }

    public function deleteVariant($id)
    {
        if ($this->db->delete('variants', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteWarehouse($id)
    {
        if ($this->db->delete('warehouses', ['id' => $id]) && $this->db->delete('warehouses_products', ['warehouse_id' => $id])) {
            return true;
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
    public function getPaymentCurrencies($usd,$khm)
    {   
        $this->db->where('code',$usd);
        $this->db->Or_where('code',$khm);
        $q = $this->db->get('currencies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllCustomerGroups()
    {
        $q = $this->db->get('customer_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllPriceGroups()
    {
        $q = $this->db->get('price_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function getAllWarehouses()
    {
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
    public function getCategoryByID($id)
    {
        $q = $this->db->get_where('categories', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getCurrencyByID($id)
    {
        $q = $this->db->get_where('currencies', ['id' => $id], 1);
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

    public function getDateFormats()
    {
        $q = $this->db->get('date_format');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getExpenseCategoryByCode($code)
    {
        $q = $this->db->get_where('expense_categories', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getExpenseCategoryByID($id)
    {
        $q = $this->db->get_where('expense_categories', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGroupByID($id)
    {
        $q = $this->db->get_where('groups', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGroupPermissions($id)
    {
        $q = $this->db->get_where('permissions', ['group_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    // public function getGroupPrice($group_id, $product_id)
    // {
    //     $q = $this->db->get_where('product_prices', ['price_group_id' => $group_id, 'product_id' => $product_id], 1);
    //     if ($q->num_rows() > 0) {
    //         return $q->row();
    //     }
    //     return false;
    // }
    public function getGroupPrice($group_id, $product_id, $unitId)
    {
        $q = $this->db->get_where('product_prices', ['price_group_id' => $group_id, 'product_id' => $product_id, 'unit_id' => $unitId], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getGroups()
    {
        $this->db->order_by('id','DESC');
        $this->db->where('id >', 1);
        $q = $this->db->get('groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getParentCategories()
    {
       // $this->db->where('parent_id', null);
        //->or_where('parent_id', 0);
        $q = $this->db->get('categories');
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
        $q = $this->db->get('paypal');
        if ($q->num_rows() > 0) {
            return $q->row();
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
     public function getProductGroupmultiPriceByPID($product_id, $group_id)
    {
        $pg = "(SELECT {$this->db->dbprefix('product_prices')}.price as price,{$this->db->dbprefix('product_prices')}.qty_from as qty_from,{$this->db->dbprefix('product_prices')}.qty_to as qty_to, {$this->db->dbprefix('product_prices')}.product_id as product_id FROM {$this->db->dbprefix('product_prices')} WHERE {$this->db->dbprefix('product_prices')}.product_id = {$product_id} AND {$this->db->dbprefix('product_prices')}.price_group_id = {$group_id}) GP";

        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, GP.price,GP.qty_from,GP.qty_to", false)
        // ->join('products', 'products.id=product_prices.product_id', 'left')
        ->join($pg, 'GP.product_id=products.id', 'left');
        $q = $this->db->get_where('products', ['products.id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getProductGroupPriceByPID($product_id, $group_id)
    {
        $pg = "(SELECT {$this->db->dbprefix('product_prices')}.price as price, {$this->db->dbprefix('product_prices')}.product_id as product_id FROM {$this->db->dbprefix('product_prices')} WHERE {$this->db->dbprefix('product_prices')}.product_id = {$product_id} AND {$this->db->dbprefix('product_prices')}.price_group_id = {$group_id}) GP";

        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, GP.price", false)
        // ->join('products', 'products.id=product_prices.product_id', 'left')
        ->join($pg, 'GP.product_id=products.id', 'left');
        $q = $this->db->get_where('products', ['products.id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
     public function updateFloor($id, $data = [])
    {
        if ($this->db->update('floors', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function addFloor($data)
    {
        if ($this->db->insert('floors', $data)) {
            return true;
        }
        return false;
    }
     public function floorHasRoom($floor_id)
    {
        $q = $this->db->get_where('suspended_note', ['floor' => $floor_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
public function deleteFloor($id)
    {
        if ($this->db->delete('floors', ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function getSettings()
    {
        $q = $this->db->get('settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSkrillSettings()
    {
        $q = $this->db->get('skrill');
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function getUnitChildren($base_unit)
    {
        $this->db->where('base_unit', $base_unit);
        $q = $this->db->get('units');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getVariantByID($id)
    {
        $q = $this->db->get_where('variants', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseByID($id)
    {
        $q = $this->db->get_where('warehouses', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function GroupPermissions($id)
    {
        $q = $this->db->get_where('permissions', ['group_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return false;
    }

    public function hasExpenseCategoryRecord($id)
    {
        $this->db->where('category_id', $id);
        return $this->db->count_all_results('expenses');
    }

    // public function setProductmultiPriceForPriceGroup($product_id, $group_id, $price, $from, $to)
    // {
    //     if ($this->getGroupPrice($group_id, $product_id)) {
    //         if ($this->db->update('product_prices', ['price' => $price,'qty_from' => $from, 'qty_to' => $to], ['price_group_id' => $group_id, 'product_id' => $product_id])) {
    //             return true;
    //         }
    //     } else {
    //         if ($this->db->insert('product_prices', ['price' => $price, 'qty_from' => $from, 'qty_to' => $to,'price_group_id' => $group_id, 'product_id' => $product_id])) {
    //             return true;
    //         }
    //     }
    //     return false;
    // }
    public function setProductmultiPriceForPriceGroup($product_id, $group_id, $price, $from, $to, $unitId)
    {
        if ($this->getGroupPrice($group_id, $product_id, $unitId)) {
            if ($this->db->update('product_prices', ['price' => $price,'qty_from' => $from, 'qty_to' => $to,'unit_id' => $unitId ] , ['price_group_id' => $group_id, 'product_id' => $product_id, 'unit_id' => $unitId])) {
                return true;
            }
        } else {
            if ($this->db->insert('product_prices', ['price' => $price, 'qty_from' => $from, 'qty_to' => $to,'price_group_id' => $group_id, 'product_id' => $product_id,'unit_id' => $unitId])) {
                return true;
            }
        }
        return false;
    }
    public function setProductPriceForMultiBuyPriceGroup($product_id,$qty_from,$qty_to,$group_id, $price)
    {
        if ($this->getGroupPrice($group_id, $product_id)) {
            if ($this->db->update('multi_buys_prices', [
                'price' => $price,
                'qty_from' => $qty_from,
                'qty_to' => $qty_to,
                ], ['price_group_id' => $group_id, 'product_id' => $product_id])) {
                return true;
            }
        } else {
            if ($this->db->insert('multi_buys_prices', [
                'price' => $price,
                'qty_from' => $qty_from,
                'qty_to' => $qty_to,
                'price_group_id' => $group_id, 'product_id' => $product_id])) {
                return true;
            }
        }
        return false;
    }
    public function updateBrand($id, $data = [])
    {
        if ($this->db->update('brands', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateCategory($id, $data = [])
    {
        if ($this->db->update('categories', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
 

    public function updateCurrency($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('currencies', $data)) {
            return true;
        }
        return false;
    }

    public function updateCustomerGroup($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('customer_groups', $data)) {
            return true;
        }
        return false;
    }

    public function updateExpenseCategory($id, $data = [])
    {
        if ($this->db->update('expense_categories', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateGroup($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('groups', $data)) {
            return true;
        }
        return false;
    }

    public function updateLoginLogo($photo)
    {
        $logo = ['logo2' => $photo];
        if ($this->db->update('settings', $logo)) {
            return true;
        }
        return false;
    }

    public function updateLogo($photo)
    {
        $logo = ['logo' => $photo];
        if ($this->db->update('settings', $logo)) {
            return true;
        }
        return false;
    }

    public function updatePaypal($data)
    {
        $this->db->where('id', '1');
        if ($this->db->update('paypal', $data)) {
            return true;
        }
        return false;
    }

    public function updatePermissions($id, $data = [])
    {
        if ($this->db->update('permissions', $data, ['group_id' => $id]) && $this->db->update('users', ['show_price' => $data['products-price'], 'show_cost' => $data['products-cost']], ['group_id' => $id])) {
            return true;
        }
        return false;
    }

    public function updatePriceGroup($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('price_groups', $data)) {
            return true;
        }
        return false;
    }

    public function updateSetting($data)
    {
        $this->db->where('setting_id', '1');
        
        if ($this->db->update('settings', $data)) {
            return true;
        }
        return false;
    }
    public function updateModules($data)
    {
        $this->db->where('id', '1');
        if ($this->db->update('modules', $data)) {
            return true;
        }
        return false;
    }
    public function updateSkrill($data)
    {
        $this->db->where('id', '1');
        if ($this->db->update('skrill', $data)) {
            return true;
        }
        return false;
    }

    public function updateTaxRate($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('tax_rates', $data)) {
            return true;
        }
        return false;
    }

    public function updateUnit($id, $data = [])
    {
        if ($this->db->update('units', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateVariant($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('variants', $data)) {
            return true;
        }
        return false;
    }

    public function updateWarehouse($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('warehouses', $data)) {
            return true;
        }
        return false;
    }
    public function getAllProductNames($term = false, $limit = false)
    {
        $limit = $this->Settings->rows_per_page;
        $allow_category = $this->site->getCategoryByProject();
        if($allow_category){
            $this->db->where_in("products.category_id",$allow_category);
        }
        $this->db->select('products.id, code, name,unit, cost')
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
    //--------add bom----
    public function getBomByID($id = false){
        $q = $this->db->get_where("boms",array("id"=>$id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;   
    }
    public function getBomItems($bom_id = false){
        $q = $this->db->get_where("bom_items",array("bom_id"=>$bom_id));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addBom($data = false, $row_meterials = false, $finished_goods = false){
        if($this->db->insert("boms",$data)){
            $bom_id = $this->db->insert_id();
            if($row_meterials){
                foreach($row_meterials as $row_meterial){
                    $row_meterial["bom_id"] = $bom_id;
                    $this->db->insert("bom_items",$row_meterial);
                }
            }
            if($finished_goods){
                foreach($finished_goods as $finished_good){
                    $finished_good["bom_id"] = $bom_id;
                    $this->db->insert("bom_items",$finished_good);
                }
            }
            return true;
        }
        return false;
    }
    public function updateBom($id = false, $data = false, $row_meterials = false, $finished_goods = false){
        if($id && $this->db->update("boms",$data,array("id"=>$id))){
            $this->db->delete("bom_items",array("bom_id"=>$id));
            if($row_meterials){
                $this->db->insert_batch("bom_items",$row_meterials);
            }
            if($finished_goods){
                $this->db->insert_batch("bom_items",$finished_goods);
            }
            return true;
        }
        return false;
    }
    public function deleteBom($id = false){
        if($id && $this->db->delete("boms",array("id"=>$id))){
            $this->db->delete("bom_items",array("bom_id"=>$id));
            return true;
        }
        return false;
    }
    public function getConvertItemsById($bom_id){
        $this->db->select('bom_items.product_id, bom_items.bom_id, bom_items.quantity AS c_quantity ,(bpas_products.cost * bpas_bom_items.quantity) AS tcost, bom_items.status, products.cost AS p_cost, (bpas_products.price * bpas_bom_items.quantity) as tprice, bom_items.option_id');
        $this->db->join('products', 'products.id = bom_items.product_id', 'INNER');
        $this->db->where('bom_items.bom_id', $bom_id);
        $query = $this->db->get('bom_items');
        
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getConvertItemsDeduct($bom_id){
        $this->db->select('SUM(bpas_products.cost * bpas_bom_items.quantity) AS tcost, bom_items.status');
        $this->db->join('products', 'products.id = bom_items.product_id', 'INNER');
        $this->db->where('bom_items.bom_id', $bom_id);
        $this->db->where('bom_items.status', 'deduct');
        $query = $this->db->get('bom_items');
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }
    
    public function getConvertItemsAdd($bom_id){
        $this->db->select('bom_items.product_id, bom_items.bom_id, bom_items.quantity AS c_quantity ,bpas_products.cost AS tcost, bom_items.status, bpas_products.price as tprice, bom_items.option_id');
        $this->db->join('products', 'products.id = bom_items.product_id', 'INNER');
        $this->db->where('bom_items.bom_id', $bom_id);
        $this->db->where('bom_items.status', 'add');
        $query = $this->db->get('bom_items');
        
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    
    public function getBOmByIDs($id)
    {
        $this->db->select('date, bom.name, bom_items.quantity, bom_items.cost, noted, created_by, bom_items.status, product_name, product_code, product_variants.name as var_name, products.quantity as qoh');
        $this->db->from('bom');
        $this->db->join('bom_items', 'bom_items.bom_id = bom.id');
        $this->db->join('products', 'products.id = bom_items.product_id', 'left');
        $this->db->join('product_variants', 'bom_items.option_id = product_variants.id', 'left');
        $this->db->where('bom.id',$id);
        $this->db->group_by('bom_items.product_id');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
             return $query->result_array();
        }
        return false;
    }
    
    public function selectBomItems($bom_id, $product_id)
    {
        $q = $this->db->get_where("bom_items", array('bom_id' => $bom_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getRoomByID($id){
        $this->db->select('id,floor,name,ppl_number,description,inactive,warehouse_id');
        $this->db->from('suspended');
        $this->db->where('id' , $id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
    public function getVariants($id){
        $this->db->select('id, name');
        $this->db->from('product_variants');
        $this->db->where('product_id', $id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    //-------end bom-------
    //----------start stock type -------------------
    public function updateStockType($id, $data = [])
    {
      
        if ($this->db->update('stock_type', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function addStockType($data)
    {
        if ($this->db->insert('stock_type', $data)) {
            return true;
        }
        return false;
    }
    public function stocktypeHasProduct(){
        $q = $this->db->get_where('products', ['stock_type' => $stock_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function deleteStocktype($id)
    {
        if ($this->db->delete('stock_type', ['id' => $id])) {
            return true;
        }
        return false;
    }
    //--------------------end stock type -----------------------
    public function deletepayment_term($id)
    {
        if ($this->db->delete("payment_term", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
    public function addPaymentTerm($data){
        if ($this->db->insert('payment_term', $data)) {
            return true;
        }
        return false;
    }
    public function getPaymentTermById($id)
    {
        $q = $this->db->get_where('payment_term', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function updatePaymentTerm($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('payment_term', $data)) {
            return true;
        }
        return false;
    }
    public function deletePaymentTerm($id){
        $this->db->delete('payment_term', array('id' => $id));
    }
    public function addPromotion($data,$categories)
    {
        
        if ($this->db->insert('promotions', $data)) {
            $promotion_id = $this->db->insert_id();
            foreach($categories as $cate)
            {
                $cate['promotion_id'] = $promotion_id;
                $this->db->insert('promotion_categories', $cate);
            }
            
            
            return true;
        }
        return false;
    }
    public function getPromotion($id)
    {
        $q = $this->db->get_where('promotions', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function deletePromotion($id)
    {
        if ($this->db->delete('promotions', array('id' => $id)) && $this->db->delete('promotion_categories', ['promotion_id' => $id])) {
            return true;
        }
        return FALSE;
    }
    public function Old_promotions($id=null)
    {
        
        $this->db->select("categories.id,promotion_categories.discount,categories.name")
            ->join('categories', 'categories.id = promotion_categories.category_id', 'left');
            
        $this->db->where('promotion_id', $id);
        $q = $this->db->get('promotion_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function updatePromotion($id, $data = array(),$categories)
    {
        
        $this->db->where('id', $id);
        if ($this->db->update('promotions', $data)) {
            $promotion_id = $id;
            if($this->db->delete('promotion_categories', array('promotion_id' => $id)))
            {
                foreach($categories as $cate)
                {
                    $cate['promotion_id'] = $promotion_id;
                    $this->db->insert('promotion_categories', $cate);
                }
                return true;
            }
            
        }
        return false;
    }
    public function addOption($data)
    {
        if ($this->db->insert('options', $data)) {
            return true;
        }
        return false;
    }
    public function deleteOption($id)
    {
        if ($this->db->delete('options', ['id' => $id])) {
            return true;
        }
        return false;
    }
     public function getOptionyID($id)
    {
        $q = $this->db->get_where('options', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function updateOption($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('options', $data)) {
            return true;
        }
        return false;
    }
    public function addMenu($data)
    {
        if ($this->db->insert('menu', $data)) {
            return true;
        }
        return false;
    }
    public function UpdateMenu($id, $data = [])
    {
        if ($this->db->update('menu', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function deleteMenu($id)
    {
        if ($this->db->delete('menu', ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function getMenuByID($id)
    {
        $q = $this->db->get_where('menu', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getParentMenus()
    {
        $this->db->where('parent_id', null)->or_where('parent_id', 0);
        $q = $this->db->get('menu');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addZone($data)
    {
        if ($this->db->insert('zones', $data)) {
            return true;
        }
        return false;
    }
    public function updateZone($id, $data = [])
    {
        if ($this->db->update('zones', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function deleteZone($id)
    {
        if ($this->db->delete('zones', ['id' => $id])) {
            return true;
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
    public function getZoneByID($id = false)
    {
        $this->db->select("zones.*,cities.zone_name as city, districts.zone_name as district, commune.zone_name as commune")
                ->join("(SELECT id,zone_name FROM ".$this->db->dbprefix('zones')." WHERE IFNULL(city_id,0) = 0) as cities","cities.id = zones.city_id","left")
                ->join("(SELECT id,zone_name FROM ".$this->db->dbprefix('zones')." WHERE city_id > 0 AND IFNULL(district_id,0) = 0) as districts","districts.id = zones.district_id","left")
                ->join("(SELECT id,zone_name FROM ".$this->db->dbprefix('zones')." WHERE district_id > 0 AND IFNULL(commune_id,0) = 0) as commune","commune.id = zones.commune_id","left");
        $q = $this->db->get_where('zones', array('zones.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getParentExpenseCategories()
    {
        $this->db->where('parent_id', null)->or_where('parent_id', 0);
        $q = $this->db->get('expense_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addSaleTarget($data)
    {
        if ($this->db->insert('sale_targets', $data)) {
            return true;
        }
        return false;
    }
    public function updateSaleTarget($id, $data = [])
    {
        if ($this->db->update('sale_targets', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function deleteSaleTarget($id)
    {
        if ($this->db->delete('sale_targets', ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function getSaleTargetByID($id)
    {
        $q = $this->db->get_where('sale_targets', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAuditTrailByID($id)
    {
        $q = $this->db->get_where('user_audit_trails', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function updateProductPriceGroup($price_groups)
    {
        if($price_groups){
            foreach ($price_groups as $key => $new_price_group) {
                if ($this->getGroupPrice($new_price_group['price_group_id'], $new_price_group['product_id'])) {
                    $this->db->update('product_prices', ['price' => $new_price_group['price']], ['price_group_id' => $new_price_group['price_group_id'], 'product_id' => $new_price_group['product_id']]);
                } else {
                    $this->db->update('products', ['price' => $new_price_group['price'], 'other_price' => $new_price_group['price']], ['id' => $new_price_group['product_id']]);
                }
            }
            return true;
        }
        return false;
    }
    public function add_language($data)
    {
        if ($this->db->insert('language', $data)) {
            return true;
        }
        return false;
    }
    public function getlanguageByID($id)
    {
        $q = $this->db->get_where('language', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function updatelanguage($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('language', $data)) {
            return true;
        }
        return false;
    }
    public function deleteLanguage($id)
    {
        if ($this->db->delete('language', ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function addcustom_field($data)
    {
        if ($this->db->insert('custom_field', $data)) {
            return true;
        }
        return false;
    }
    public function getParentCustomField()
    {
        $this->db->where('parent_id', null)->or_where('parent_id', 0);
        $q = $this->db->get('custom_field');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getCustomeFieldByID($id)
    {
        $q = $this->db->get_where('custom_field', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updatecustomField($id, $data = [])
    {
        if ($this->db->update('custom_field', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteCustomField($id)
    {
        if ($this->db->delete('custom_field', ['id' => $id,'parent_id !='=>0])) {
            return true;
        }
        return false;
    }
    public function setProductQtyAlert($product_id, $warehouse_id, $qty_alert)
    {
        if (empty($this->getWHProduct($product_id, $warehouse_id))) {
            $this->site->syncQuantity(null, null, null, $product_id);
        }
        if ($this->db->update('warehouses_products', ['qty_alert' => $qty_alert], ['product_id' => $product_id, 'warehouse_id' => $warehouse_id])) {
            return true;
        }
        
        return false;
    }
    public function getWHProduct($product_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function updateProducstQtyAlert($data)
    {
        foreach ($data as $arr) {
            foreach ($arr as $row) {
                $this->setProductQtyAlert($row['product_id'], $row['warehouse_id'], $row['qty_alert']);
            }
        }
    }
    public function getTelegramByID($id = false){
        $q = $this->db->get_where("telegram_bots",array("id"=>$id));
         if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    
    public function addTelegram($data = false){
        if($data){
            $this->db->insert("telegram_bots",$data);
            return true;
        }
        return false;
    }
    
    public function updateTelegram($id = false, $data = false){
        if($id && $this->db->update("telegram_bots",$data,array("id"=>$id))){
            return true;
        }
        return false;
    }
    
    public function deleteTelegram($id = false){
        if($id && $this->db->delete("telegram_bots",array("id"=>$id))){
            return true;
        }
        return false;
    }
    public function updateMultiApproved($id, $data = [])
    {
        if ($this->db->update('approved_by', $data, ['group_id' => $id])) {
            return true;
        }
        return false;
    }
    public function getLanguageBycode($code)
    {
        $q = $this->db->get_where('language', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function addBathchLanguage($data = [])
    {
        if ($this->db->insert_batch('language', $data)) {
            return true;
        }
        return false;
    }
    public function getPrograms(){
        $q = $this->db->get("sh_programs");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getFrequencyByID($id = false)
    {
        $q = $this->db->get_where('frequency', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addFrequency($data = false)
    {
        if ($this->db->insert("frequency", $data)) {
            return true;
        }
        return false;
    }

    public function addFrequencies($data = false)
    {
        if($this->db->insert_batch("frequency", $data)){
            return true;
        }
        return false;
    }
    public function updateFrequency($id = false, $data = array())
    {
        if ($this->db->update("frequency", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteFrequency($id = false)
    {
        if ($this->db->delete("frequency", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
    public function getInterest_periodByID($id = false)
    {
        $q = $this->db->get_where('interest_period', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addInterest_period($data = false)
    {
        if ($this->db->insert("interest_period", $data)) {
            return true;
        }
        return false;
    }

    public function updateInterest_period($id = false, $data = array())
    {
        if ($this->db->update("interest_period", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteInterest_period($id = false)
    {
        if ($this->db->delete("interest_period", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function addMultiInterest_period($data = false)
    {
        if($this->db->insert_batch("interest_period", $data)){
            return true;
        }
        return false;
    }

    //////////////////////// Update Price Group 01_07_2022 ////////////////////////////////////////
    public function setProductPriceForPriceGroup($product_id, $group_id, $price)
    {
        if ($this->getGroupPrice($group_id, $product_id)) {
            if ($this->db->update('product_prices', ['price' => $price], ['price_group_id' => $group_id, 'product_id' => $product_id])) {
                return true;
            }
        } else {
       
            if ($this->db->insert('product_prices', ['price' => $price,'price_group_id' => $group_id, 'product_id' => $product_id])) {
                return true;
            }
        }
        return false;
    }

    public function deleteProductGroupPrice($product_id, $group_id)
    {
        if ($this->db->delete('product_prices', ['price_group_id' => $group_id, 'product_id' => $product_id])) {
            return true;
        }
        return false;
    }

    public function updateGroupPrices_23_06_2022($data = [])
    {
        foreach ($data as $row) {
            if ($this->getGroupPrice($row['price_group_id'], $row['product_id'])) {
                $this->db->update('product_prices', ['price' => $row['price']], ['product_id' => $row['product_id'], 'price_group_id' => $row['price_group_id']]);
            } else {
                $this->db->insert('product_prices', $row);
            }
        }
        return true;
    }

    public function getProductsPriceByPriceGroupID($id, $academic_year = null) 
    {
        $condition = ($academic_year != null ? (" AND product_prices.academic_year = " . $academic_year) : '');

        $this->db->order_by('unit');
        $q = $this->db->get_where('products', ['type !=' => 'asset']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $product     = $row;
                $units       = null;
                $price_group = $this->getPriceGroupByID($id);

                $this->db->select(" 
                    {$this->db->dbprefix('units')}.id as unit_id,
                    {$this->db->dbprefix('units')}.code as unit_code,
                    {$this->db->dbprefix('units')}.name as unit_name,
                    COALESCE({$this->db->dbprefix('product_prices')}.price, 0) AS price
                ");
                $this->db->from('products');
                $this->db->join('units', 'products.unit=units.id OR products.unit=units.base_unit', 'left');
                $this->db->join("product_prices", "products.id=product_prices.product_id AND units.id=product_prices.unit_id AND product_prices.price_group_id = {$id} {$condition} ", "left");
                $this->db->where('products.id', $row->id);
                $this->db->order_by('units.code', 'ASC');
                $sub_q = $this->db->get();
                if ($sub_q->num_rows() > 0) {
                    foreach (($sub_q->result()) as $price_unit) {
                        $units[] = $price_unit;
                    }
                }
                $data[$product->unit][] = [ 'product' => $product, 'price_group' => $price_group, 'units' => $units ];
            }
            return $data;
        }
        return false;
    }

    public function getAllBaseUnitOfProducts() 
    {
        $this->db->select(" {$this->db->dbprefix('units')}.* ");
        $this->db->from('products');
        $this->db->join('units', 'products.unit = units.id', 'inner');
        $this->db->group_by('units.id');
        $this->db->order_by('units.code', 'ASC');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) AS $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function setProductPriceForPriceGroupByUnit($group_id, $product_id, $unit_id, $price, $academic_year = null)
    {
        if ($this->getProductPriceGroupByUnit($group_id, $product_id, $unit_id, $academic_year)) {
            if ($this->db->update('product_prices', ['price' => $price], ['price_group_id' => $group_id, 'product_id' => $product_id, 'unit_id' => $unit_id, 'academic_year' => $academic_year])) {
                return true;
            }
        } else {
            if ($this->db->insert('product_prices', ['price' => $price, 'price_group_id' => $group_id, 'product_id' => $product_id, 'unit_id' => $unit_id, 'academic_year' => $academic_year])) {
                return true;
            }
        }
        return false;
    }

    public function getProductPriceGroupByUnit($group_id, $product_id, $unit_id, $academic_year = null)
    {
        $q = $this->db->get_where('product_prices', ['price_group_id' => $group_id, 'product_id' => $product_id, 'unit_id' => $unit_id, 'academic_year' => $academic_year], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductGroupPriceByPIDUnitID($group_id, $product_id, $unit_id, $academic_year = null)
    {
        $condition = ($academic_year != null ? (" AND product_prices.academic_year = " . $academic_year) : '');
        $this->db->select(" 
            {$this->db->dbprefix('products')}.id AS id, {$this->db->dbprefix('products')}.code AS code, {$this->db->dbprefix('products')}.name AS name, 
            {$this->db->dbprefix('units')}.id AS unit_id, 
            {$this->db->dbprefix('units')}.code AS unit_code, 
            {$this->db->dbprefix('units')}.name AS unit_name, 
            COALESCE({$this->db->dbprefix('product_prices')}.price, 0) AS price ", false);
        $this->db->from('products');
        $this->db->join("product_prices", "products.id = product_prices.product_id AND product_prices.price_group_id = {$group_id} AND product_prices.product_id = {$product_id} AND product_prices.unit_id = {$unit_id} {$condition}", "left");
        $this->db->join('units', "units.id = {$unit_id}", 'left');
        $this->db->where('products.id', $product_id);
        $this->db->limit(1);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function deleteProductGroupPriceByUnit($group_id, $product_id, $unit_id, $academic_year = null)
    {
        if ($this->db->delete('product_prices', ['price_group_id' => $group_id, 'product_id' => $product_id, 'unit_id' => $unit_id, 'academic_year' => $academic_year])) {
            return true;
        }
        return false;
    }

    public function updateGroupPrices($data = [])
    {
        foreach ($data as $row) {
            $this->setProductPriceForPriceGroupByUnit(
                $row['price_group_id'], 
                $row['product_id'], 
                (isset($row['unit_id']) ? $row['unit_id'] : null), 
                $row['price'], 
                $row['academic_year']
            );
        }
        return true;
    }

    public function getAllUnitsOfProduct($id) 
    {
        $this->db->select('units.*');
        $this->db->from('products');
        $this->db->join('units', 'units.id = products.unit OR units.base_unit = products.unit', 'left');
        $this->db->where('products.id', $id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function valid_UnitProduct($product_id, $unit_id)
    {
        $units = $this->getAllUnitsOfProduct($product_id);
        foreach ($units as $unit) {
            if ($unit->id == $unit_id) return true;
        }
        return false;
    }
    //////////////////////// Update Price Group 01_07_2022 ////////////////////////////////////////

    public function addCurrencyCalender($data = false)
    {
        $this->db->delete("currency_calenders",array("currency_id" => $data["currency_id"],"date" => $data["date"]));
        if ($this->db->insert("currency_calenders", $data)) {
            return true;
        }
        return false;
    }
    
    public function updateCurrencyCalender($id = false, $data = false)
    {
        if ($id && $this->db->update("currency_calenders", $data,array("id"=>$id))) {
            return true;
        }
        return false;
    }
    
    public function getCurrencyCalender(){
        $q = $this->db->get('currency_calenders');
        if ($q->num_rows() > 0) {
            foreach (($q->result_array()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function addCashAccount($data = false)
    {
        if ($this->db->insert("cash_accounts", $data)) {
            return true;
        }
        return false;
    }

    public function updateCashAccount($id = false, $data = array())
    {
        if ($this->db->update("cash_accounts", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteCashAccount($id = false)
    {
        if ($this->db->delete("cash_accounts", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
    // ======================= skins =====================
    public function addSkins($data = false)
    {
        if ($this->db->insert("skins", $data)) {
            return true;
        }
        return false;
    }
    public function deleteSkins($id = false)
    {
        if ($this->db->delete("skins", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
    public function getSkins()
    {
        $q = $this->db->get('skins');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getSkinsByID($id = false)
    {
        $q = $this->db->get_where('skins', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    } 
    public function updateSkins($id = false, $data = array())
    { 
        $this->db->where('id', $id);
        if ($this->db->update("skins", $data)) {
            return true;
        }
        return false;
    }
    public function getPriceByUnit($product_id, $unit_id, $group_id)
    {
        $this->db->select('product_prices.*,cost_price_by_units.price as unit_price');
        $this->db->join('cost_price_by_units', 'product_prices.product_id = cost_price_by_units.product_id AND product_prices.unit_id = cost_price_by_units.unit_id','left');
        $q = $this->db->get_where('product_prices', ['product_prices.product_id' => $product_id, 'product_prices.price_group_id' => $group_id, 'product_prices.unit_id' => $unit_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function Else_GetPriceByUnit($product_id, $unit_id)
    {
        $this->db->select('cost_price_by_units.price as unit_price'); 
        $this->db->join('cost_price_by_units', 'products.id = cost_price_by_units.product_id','left');
        $q = $this->db->get_where('products', ['products.id' => $product_id, 'cost_price_by_units.unit_id' => $unit_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getProductNamesByMultiunit($term, $limit = 10)
    {
        $sub_q = " ( 
            SELECT 
                mcp.product_id, 
                mcp.product_code,  
                mcp.unit_id,  
                mcp.cost,  
                mcp.price  
            FROM {$this->db->dbprefix('cost_price_by_units')} mcp
            WHERE 
                mcp.product_code IS NOT NULL AND
                mcp.product_code != '' AND
                mcp.product_code != 0 AND
                mcp.product_code LIKE '%" . $term . "%'
            GROUP BY mcp.product_id
        ) bpas_CP ";

        $this->db->select('id,COALESCE(bpas_CP.product_code , bpas_products.code) as code, name')
            // ->like('name', $term, 'both')->or_like('code', $term, 'both')
            ->join($sub_q, 'CP.product_id = products.id', 'left')
            ->where(" ( 
                {$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR
                {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR
                {$this->db->dbprefix('CP')}.product_code LIKE '%" . $term . "%'
            ) ")
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
    public function Old_promotionsByProductCode($id=null)
    {
        $this->db->select("products.id,promotion_categories.product_code as code,promotion_categories.discount,concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('promotion_categories')}.product_code, ')') as name ")
            ->join('products', 'products.id = promotion_categories.product_id', 'left');
        $this->db->where('promotion_id', $id);
        $q = $this->db->get('promotion_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function import_promotions($datas , $promotion)
    {
        
        if ($this->db->insert('promotions', $promotion)) {
            $promotion_id = $this->db->insert_id();
            foreach($datas as $data)
            {
                $data['promotion_id'] = $promotion_id;
                $this->db->insert('promotion_categories', $data);
            }
            return true;
        }
        return false;
    }

    public function addReward($data)
    {
        if ($this->db->insert('rewards', $data)) {
            return true;
        }
        return false;
    }

    public function updateReward($id, $data = [])
    {
        if ($this->db->update('rewards', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteReward($id)
    {
        if ($this->db->delete('rewards', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getAllRewards()
    {
        $q = $this->db->get('rewards');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getRewardsByGroup($category, $type)
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
        $this->db->where('rewards.category', $category);
        $this->db->where('rewards.type', $type);
        $this->db->order_by('id', 'ASC');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach(($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
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

    public function table_compare()
    {
        $q = $this->db->get_where('sales_rank_commission');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    
    public function deleteSalesRank($id)
    {
        if ($this->db->delete('sales_rank_commission', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getSaleRankByID($id)
    {
        $q = $this->db->get_where('sales_rank_commission', ['id' => $id], 1);
        if ($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }

    public function update_sale_rank_commission($data = [], $sale_rank_id)
    {
        if ($this->db->update('sales_rank_commission', $data, ['id' => $sale_rank_id])) {
            return true;
        }
        return false;
    }

    public function insert_sale_rank_commission($data = [])
    {
        if ($this->db->insert('sales_rank_commission', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }
     public function getRackByID($id)
    {
        $q = $this->db->get_where('product_rack', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getParentRacks(){
        $q = $this->db->get('product_rack');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addRack($data)
    {
        if ($this->db->insert('product_rack', $data)) {
            return true;
        }
        return false;
    }
    public function updateRack($id, $data = [])
    {
        if ($this->db->update('product_rack', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function deleteRack($id)
    {
        if ($this->db->delete('product_rack', ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function getSubRacks($parent_id)
    {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get('product_rack');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function deleteTank($id = false)
    {
        if($this->db->where("id", $id)->delete("tanks")){
            $this->db->where("tank_id", $id)->delete("tank_nozzles");
            $this->db->delete("tank_nozzle_salesmans",array("tank_id"=>$id));
            return true;
        }
        return false;
    }

    public function updateTank($id = false, $data = array())
    {
        if ($this->db->update("tanks", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function addTank($data = false)
    {
        if ($this->db->insert("tanks", $data)) {
            return true;
        }
        return false;
    }

    public function getTankByID($id = false)
    {
        $q = $this->db->get_where('tanks', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function addNozzleStartNo($data = false,$salesmans = false)
    {
       if ($this->db->insert("tank_nozzles", $data)) {
            $nozzle_id = $this->db->insert_id();
            if($salesmans){
                foreach($salesmans as $salesman){
                    $salesman['nozzle_id'] = $nozzle_id;
                    $this->db->insert("tank_nozzle_salesmans",$salesman);
                }
            }
            return true;
        }
        return false;
    }

    public function updateNozzleStartNo($id = false, $data = false, $salesman = false)
    {
       if ($this->db->where("id", $id)->update("tank_nozzles", $data)) {
            $this->db->delete("tank_nozzle_salesmans",array("tank_id"=>$data["tank_id"],"nozzle_id"=>$id));
            if($salesman){
                $this->db->insert_batch("tank_nozzle_salesmans",$salesman);
            }
            return true;
        }
        return false;
    }

    public function getNozzleStartNoByID($id = false)
    {
        $q = $this->db->where("id", $id)->get("tank_nozzles");
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
    }

    public function deleteNozzleStartNo($id =false)
    {
        if($this->db->where("id",$id)->delete("tank_nozzles")){
            $this->db->delete("tank_nozzle_salesmans",array("nozzle_id"=>$id));
            return true;
        }
        return false;
    }
    public function addFuelTime($data = false)
    {
         if ($this->db->insert("fuel_times", $data)) {
            return true;
        }
        return false;
    }

    public function getFuelTimesByID($id = false)
    {
        $q = $this->db->get_where('fuel_times', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteFuelTime($id = false)
    {
        if($this->db->where("id", $id)->delete("fuel_times")){
            return true;
        }
        return false;
    }

    public function updateFuelTime($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('fuel_times', $data)) {
            return true;
        }
        return false;
    }
    //------------ticket---------
    public function addEvent($data)
    {
        if ($this->db->insert('events', $data)) {
            return true;
        }
        return false;
    }
     public function updateEvent($id, $data = [])
    {
        if ($this->db->update('events', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function getEventByID($id)
    {
        $q = $this->db->get_where('events', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function deleteTicket($id)
    {
        if ($this->db->delete('events', ['id' => $id])) {
            return true;
        }
        return false;
    }
    
}