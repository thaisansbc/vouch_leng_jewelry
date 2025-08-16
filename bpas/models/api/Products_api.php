<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Products_api extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function countProducts($filters = [])
    {
        if ($filters['category']) {
            $category = $this->getCategoryByCode($filters['category']);
            $this->db->where('category_id', $category->id);
        }
        if ($filters['brand']) {
            $brand = $this->getBrandByCode($filters['brand']);
            $this->db->where('brand', $brand->id);
        }
        $this->db->from('products');
        return $this->db->count_all_results();
    }

    public function getBrandByCode($code)
    {
        return $this->db->get_where('brands', ['code' => $code], 1)->row();
    }

    public function getBrandByID($id)
    {
        return $this->db->get_where('brands', ['id' => $id], 1)->row();
    }

    public function getCategoryByCode($code)
    {
        return $this->db->get_where('categories', ['code' => $code], 1)->row();
    }

    public function getCategoryByID($id)
    {
        return $this->db->get_where('categories', ['id' => $id], 1)->row();
    }

    public function getProduct($filters)
    {
        if (!empty($products = $this->getProducts($filters))) {
            return array_values($products)[0];
        }
        return false;
    }

    public function getProductPhotos($product_id)
    {
        $uploads_url = base_url('assets/uploads/');
        $this->db->select("CONCAT('{$uploads_url}', photo) as photo_url");
        return $this->db->get_where('product_photos', ['product_id' => $product_id])->result();
    }

    public function getProducts($filters = [])
    {
        $uploads_url = base_url('assets/uploads/');
        $this->db->select("{$this->db->dbprefix('products')}.id, {$this->db->dbprefix('products')}.code, {$this->db->dbprefix('products')}.name, {$this->db->dbprefix('products')}.type, {$this->db->dbprefix('products')}.slug, price, CONCAT('{$uploads_url}', {$this->db->dbprefix('products')}.image) as image_url, tax_method, tax_rate, unit");

        if (!empty($filters['include'])) {
            foreach ($filters['include'] as $include) {
                if ($include == 'brand') {
                    $this->db->select('brand');
                } elseif ($include == 'category') {
                    $this->db->select('category_id as category');
                }
            }
        }
        if ($filters['category']) {
            $this->db->join('categories', 'categories.id=products.category_id', 'left');
            $this->db->where("{$this->db->dbprefix('categories')}.code", $filters['category']);
        }
        if ($filters['brand']) {
            $this->db->join('brands', 'brands.id=products.brand', 'left');
            $this->db->where("{$this->db->dbprefix('brands')}.code", $filters['brand']);
        }
        if ($filters['code']) {
            $this->db->where('code', $filters['code']);
        } else {
            $this->db->order_by($filters['order_by'][0], $filters['order_by'][1] ? $filters['order_by'][1] : 'asc');
            $this->db->limit($filters['limit'], ($filters['start'] - 1));
        }

        return $this->db->get('products')->result();
    }

    public function getProductUnit($id)
    {
        return $this->db->get_where('units', ['id' => $id], 1)->row();
    }

    public function getSubUnits($base_unit)
    {
        return $this->db->get_where('units', ['base_unit' => $base_unit])->result();
    }

    public function getTaxRateByID($id)
    {
        return $this->db->get_where('tax_rates', ['id' => $id], 1)->row();
    }
    public function getProductAddOnItems($pid){
        $this->db->select(
            $this->db->dbprefix('products') . '.id as id, 
            ' . $this->db->dbprefix('products') . '.code as code, 
            ' . $this->db->dbprefix('addon_items') . '.price as price, 
            ' . $this->db->dbprefix('products') . '.name as name,
             description
        ') 
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
    public function getAllStockCount($warehouse_id=null)
    {

        $this->db
            ->select("{$this->db->dbprefix('stock_counts')}.id as id, date, reference_no, {$this->db->dbprefix('warehouses')}.name as wh_name, type, status, brand_names, category_names, initial_file, final_file")
            ->join('warehouses', 'warehouses.id=stock_counts.warehouse_id', 'left');
        if ($warehouse_id) {
            $this->db->where("FIND_IN_SET(bpas_stock_counts.warehouse_id, '".$warehouse_id."')");
        }
        return $this->db->get_where('stock_counts')->result();
    }
    public function addStockCount($data, $items){
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
}
