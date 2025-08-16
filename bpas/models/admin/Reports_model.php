<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reports_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getBestSeller($start_date, $end_date, $warehouse_id = null)
    {
        $this->db
            ->select('product_name, product_code')->select_sum('quantity')
            ->join('sales', 'sales.id = sale_items.sale_id', 'left')
            ->where('date >=', $start_date)->where('date <=', $end_date)
            ->group_by('product_name, product_code')->order_by('sum(quantity)', 'desc')->limit(10);
        if ($warehouse_id) {
            $this->db->where('sale_items.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getAllTransferItems_new($transfer_id, $status)
    {
        if ($status == 'completed') {
            $this->db->select('purchase_items.*, product_variants.name as variant, products.unit, products.hsn_code as hsn_code, products.second_name as second_name')
                ->from('purchase_items')
                ->join('products', 'products.id=purchase_items.product_id', 'left')
                ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
                ->group_by('purchase_items.id')
                ->where('transfer_id', $transfer_id);
        } else {
            $this->db->select('transfer_items.*, product_variants.name as variant, products.unit, products.hsn_code as hsn_code, products.second_name as second_name')
                ->from('transfer_items')
                ->join('products', 'products.id=transfer_items.product_id', 'left')
                ->join('product_variants', 'product_variants.id=transfer_items.option_id', 'left')
                ->group_by('transfer_items.id')
                ->where('transfer_id', $transfer_id);
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getSaleDiscount($category, $warehouse, $start_date, $end_date)
    {
     
        // $sp = '( SELECT SUM(order_discount) order_discount from ' . $this->db->dbprefix('sales') . ')';
       
            $this->db->select("SUM(order_discount) as order_discount");
             if ($start_date || $warehouse) {
            if ($start_date) {
                $this->db->where('date>=', $start_date);
                 $this->db->where('date<', $end_date);
  
            }
            if ($warehouse) {
                 $this->db->where('warehouse_id', $warehouse);
            }
        }
            if (!$this->Owner && !$this->Admin) {
                $this->db->where('created_by', $this->session->userdata('user_id'));
            }
            if (0) {
                $this->db->where('category_id', $category);
            }
        $q = $this->db->get("sales");

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    // public function getmonthlyPurchases()
    // {
    //     $myQuery = "SELECT (CASE WHEN date_format( date, '%b' ) Is Null THEN 0 ELSE date_format( date, '%b' ) END) as month, SUM( COALESCE( total, 0 ) ) AS purchases FROM purchases WHERE date >= date_sub( now( ) , INTERVAL 12 MONTH ) GROUP BY date_format( date, '%b' ) ORDER BY date_format( date, '%m' ) ASC";
    //     $q = $this->db->query($myQuery);
    //     if ($q->num_rows() > 0) {
    //         foreach (($q->result()) as $row) {
    //             $data[] = $row;
    //         }
    //         return $data;
    //     }
    //     return FALSE;
    // }

    public function getChartData()
    {
        $myQuery = "SELECT S.month,
        COALESCE(S.sales, 0) as sales,
        COALESCE( P.purchases, 0 ) as purchases,
        COALESCE(S.tax1, 0) as tax1,
        COALESCE(S.tax2, 0) as tax2,
        COALESCE( P.ptax, 0 ) as ptax
        FROM (  SELECT  date_format(date, '%Y-%m') Month,
                SUM(total) Sales,
                SUM(product_tax) tax1,
                SUM(order_tax) tax2
                FROM " . $this->db->dbprefix('sales') . "
                WHERE date >= date_sub( now( ) , INTERVAL 12 MONTH )
                GROUP BY date_format(date, '%Y-%m')) S
            LEFT JOIN ( SELECT  date_format(date, '%Y-%m') Month,
                        SUM(product_tax) ptax,
                        SUM(order_tax) otax,
                        SUM(total) purchases
                        FROM " . $this->db->dbprefix('purchases') . "
                        GROUP BY date_format(date, '%Y-%m')) P
            ON S.Month = P.Month
            ORDER BY S.Month";
        $q = $this->db->query($myQuery);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCosting($date, $warehouse_id = null, $year = null, $month = null)
    {
        $this->db->select('SUM( COALESCE( purchase_unit_cost, 0 ) * quantity ) AS cost, SUM( COALESCE( sale_unit_price, 0 ) * quantity ) AS sales, SUM( COALESCE( purchase_net_unit_cost, 0 ) * quantity ) AS net_cost, SUM( COALESCE( sale_net_unit_price, 0 ) * quantity ) AS net_sales', false);
        if ($date) {
            $this->db->where('costing.date', $date);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('costing.date >=', $year . '-' . $month . '-01 00:00:00');
            $this->db->where('costing.date <=', $year . '-' . $month . '-' . $last_day . ' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->join('sales', 'sales.id=costing.sale_id')
            ->where('sales.warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('costing');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getCustomerOpenReturns($customer_id)
    {
        $this->db->from('returns')->where('customer_id', $customer_id);
        return $this->db->count_all_results();
    }

    public function getCustomerQuotes($customer_id)
    {
        $this->db->from('quotes')->where('customer_id', $customer_id);
        return $this->db->count_all_results();
    }

    public function getCustomerReturns($customer_id)
    {
        return $this->getCustomerSaleReturns($customer_id) + $this->getCustomerOpenReturns($customer_id);
    }

    public function getCustomerSaleReturns($customer_id)
    {
        $this->db->from('sales')->where('customer_id', $customer_id)->where('sale_status', 'returned');
        return $this->db->count_all_results();
    }

    public function getCustomerSales($customer_id)
    {
        $this->db->from('sales')->where('customer_id', $customer_id);
        return $this->db->count_all_results();
    }

    public function getDailyPurchases($year, $month, $warehouse_id = null, $biller_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id IN ({$warehouse_id}) AND ";
        }
        if ($biller_id) {
            $myQuery .= " biller_id IN ({$biller_id}) AND ";
        }
        $myQuery .= " DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getExpenseCategories()
    {
        $q = $this->db->get('expense_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getChildsExpenseCategoryByID($id)
    {
        $q = $this->db->get_where('expense_categories', ['parent_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getExpenses($date, $warehouse_id = null, $year = null, $month = null)
    {
        $sdate = $date . ' 00:00:00';
        $edate = $date . ' 23:59:59';
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS total', false);
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year . '-' . $month . '-01 00:00:00');
            $this->db->where('date <=', $year . '-' . $month . '-' . $last_day . ' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getMonthlyPurchases($year, $warehouse_id = null, $biller_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id IN ({$warehouse_id}) AND ";
        }
        if ($biller_id) {
            $myQuery .= " biller_id IN ({$biller_id}) AND ";
        }
        $myQuery .= " DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getOrderDiscount($date, $warehouse_id = null, $year = null, $month = null)
    {
        $sdate = $date . ' 00:00:00';
        $edate = $date . ' 23:59:59';
        $this->db->select('SUM( COALESCE( order_discount, 0 ) ) AS order_discount', false);
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year . '-' . $month . '-01 00:00:00');
            $this->db->where('date <=', $year . '-' . $month . '-' . $last_day . ' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    
    public function total_discount($date, $warehouse_id = null, $year = null, $month = null)
    {
        $sdate = $date . ' 00:00:00';
        $edate = $date . ' 23:59:59';
        $this->db->select('SUM( COALESCE( total_discount, 0 ) ) AS total_discount', false);
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year . '-' . $month . '-01 00:00:00');
            $this->db->where('date <=', $year . '-' . $month . '-' . $last_day . ' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getPOSSetting()
    {
        $q = $this->db->get('pos_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductNames($term, $limit = 5)
    {
        $this->db->select('id, code, name')
            ->like('name', $term, 'both')->or_like('code', $term, 'both');
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPurchasesTax($start_date = null, $end_date = null)
    {
        $this->db->select_sum('igst')->select_sum('cgst')->select_sum('sgst')
            ->select_sum('product_tax')->select_sum('order_tax')
            ->select_sum('grand_total')->select_sum('paid');
        if ($start_date) {
            $this->db->where('date >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('date <=', $end_date);
        }
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchasesTotals($supplier_id)
    {
        $this->db->select('SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', false)
            ->where('supplier_id', $supplier_id);
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturns($date, $warehouse_id = null, $year = null, $month = null)
    {
        $sdate = $date . ' 00:00:00';
        $edate = $date . ' 23:59:59';
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total', false)
        ->where('sale_status', 'returned');
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year . '-' . $month . '-01 00:00:00');
            $this->db->where('date <=', $year . '-' . $month . '-' . $last_day . ' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSalesTax($start_date = null, $end_date = null)
    {
        $this->db->select_sum('igst')->select_sum('cgst')->select_sum('sgst')
            ->select_sum('product_tax')->select_sum('order_tax')
            ->select_sum('grand_total')->select_sum('paid');
        if ($start_date) {
            $this->db->where('date >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('date <=', $end_date);
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSalesTotals($customer_id)
    {
        $this->db->select('SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', false)
            ->where('customer_id', $customer_id);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    // public function getSalesTotalsByProduct($product)
    // {
    //     $this->db->select('SUM(COALESCE(subtotal, 0)) as total_amount', false)
    //         ->where('product_id', $proudct);
    //         ->join('sales','sales.id = sale_items.sale_id')
    //     $q = $this->db->get('sale_items');
    //     if ($q->num_rows() > 0) {
    //         return $q->row();
    //     }
    //     return false;
    // }
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
        return false;
    }

    public function getStaffDailyPurchases($user_id, $year, $month, $warehouse_id = null, $biller_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id IN ({$warehouse_id}) AND ";
        }
        if ($biller_id) {
            $myQuery .= " biller_id IN ({$biller_id}) AND ";
        }
        $myQuery .= " created_by = {$user_id} AND DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaffDailySales($user_id, $year, $month, $warehouse_id = null, $biller_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id IN ({$warehouse_id}) AND ";
        }
        if ($biller_id) {
            $myQuery .= " biller_id IN ({$biller_id}) AND ";
        }
        $myQuery .= " store_sale != 1 AND ";
        $myQuery .= " created_by = {$user_id} AND DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}' GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDailySales($year, $month, $warehouse_id = null, $biller_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id IN ({$warehouse_id}) AND ";
        }
        if ($biller_id) {
            $myQuery .= " biller_id IN ({$biller_id}) AND ";
        }
        $myQuery .= " store_sale != 1 AND ";
        $myQuery .= " DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}' GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaffMonthlySales($user_id, $year, $warehouse_id = null, $biller_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id IN ({$warehouse_id}) AND ";
        }
        if ($biller_id) {
            $myQuery .= " biller_id IN ({$biller_id}) AND ";
        }
        $myQuery .= " store_sale != 1 AND ";
        $myQuery .= " created_by = {$user_id} AND DATE_FORMAT( date,  '%Y' ) =  '{$year}' GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getMonthlySales($year, $warehouse_id = null, $biller_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id IN ({$warehouse_id}) AND ";
        }
        if ($biller_id) {
            $myQuery .= " biller_id IN ({$biller_id}) AND ";
        }
        $myQuery .= " store_sale != 1 AND ";
        $myQuery .= " DATE_FORMAT( date,  '%Y' ) =  '{$year}' GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaffMonthlyPurchases($user_id, $year, $warehouse_id = null, $biller_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id IN ({$warehouse_id}) AND ";
        }
        if ($biller_id) {
            $myQuery .= " biller_id IN ({$biller_id}) AND ";
        }
        $myQuery .= " created_by = {$user_id} AND DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaffPurchases($user_id)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', false)
            ->where('created_by', $user_id);
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStaffSales($user_id)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', false)
            ->where('created_by', $user_id);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStockValue()
    {
        $q = $this->db->query('SELECT SUM(by_price) as stock_by_price, SUM(by_cost) as stock_by_cost FROM ( Select COALESCE(sum(' . $this->db->dbprefix('warehouses_products') . '.quantity), 0)*price as by_price, COALESCE(sum(' . $this->db->dbprefix('warehouses_products') . '.quantity), 0)*cost as by_cost FROM ' . $this->db->dbprefix('products') . ' JOIN ' . $this->db->dbprefix('warehouses_products') . ' ON ' . $this->db->dbprefix('warehouses_products') . '.product_id=' . $this->db->dbprefix('products') . '.id GROUP BY ' . $this->db->dbprefix('products') . '.id )a');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSupplierPurchases($supplier_id)
    {
        $this->db->from('purchases')->where('supplier_id', $supplier_id);
        return $this->db->count_all_results();
    }

    //====================11/11/22==========//
    public function getTotalUsers($start, $end, $biller_id = null)
    {
       
        $this->db->select("count({$this->db->dbprefix('users')}.id) as total,"
             , false);
        if ($biller_id) {
            $this->db->where("biller_id", $biller_id);
        }
        $q = $this->db->get('users');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalEmployeesDepartment($start, $end, $biller_id = null )
    {
       
        $this->db->select("code ,name ,count(employee_id) as total", false);
        if ($biller_id) {
            $this->db->where("biller_id", $biller_id);
        }
        $this->db->join('hr_employees_working_info','hr_employees_working_info.department_id = hr_departments.id' ,'left');
        $this->db->group_by('hr_departments.id');
        $q = $this->db->get('hr_departments');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getTotalEmployeesPosition($start, $end, $biller_id = null )
    {
       
        $this->db->select("code ,name ,count(employee_id) as total", false);
        if ($biller_id) {
            $this->db->where("biller_id", $biller_id);
        }
        $this->db->join('hr_employees_working_info','hr_employees_working_info.position_id = hr_positions.id' ,'left');
        $this->db->group_by('hr_positions.id');
        $q = $this->db->get('hr_positions');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getTotalEmployees($start, $end, $biller_id = null , $empcode=null ){
    
        $this->db->select("
            count({$this->db->dbprefix('hr_employees')}.id) as total , 
            SUM(status = 'active') AS active_users, 
            SUM(status = 'inactive') AS inactive_users,
        ", false);


        $this->db->join('hr_employees_working_info','hr_employees_working_info.employee_id = hr_employees.id' ,'inner');
        if ($biller_id) {
            $this->db->where("biller_id", $biller_id);
        }
        $q = $this->db->get('hr_employees');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    //===================end================//

    public function getTotalExpenses($start, $end, $category = null, $warehouse_id = null, $biller_id = null)
    {
        $this->db->select('count(bpas_expenses.id) as total, sum(COALESCE(amount, 0)) as total_amount', false)
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if($category){
            $this->db->where('expense_categories.code', $category);
        }
        if ($biller_id) {
            $this->db->where("biller_id", $biller_id);
        }
        $this->db->join('expense_categories','expense_categories.id = expenses.category_id' ,'left');
        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalExpensesBudget($start, $end, $category = null, $warehouse_id = null, $biller_id = null)
    {
        $this->db->select('count(bpas_expenses_budget.id) as total, sum(COALESCE(amount, 0)) as total_amount', false)
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if($category){
            $this->db->where('expense_categories.code', $category);
        }
        if ($biller_id) {
            $this->db->where("biller_id", $biller_id);
        }
        $this->db->join('expense_categories','expense_categories.id = expenses_budget.category_id', 'left');
        $q = $this->db->get('expenses_budget');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalPayroll($start, $end, $biller_id = null)
    {
        $this->db->select("
            COUNT({$this->db->dbprefix('staff_payslip')}.id) AS total, 
            SUM(COALESCE({$this->db->dbprefix('staff_payslip')}.basic, 0)) AS total_salary, 
            SUM(COALESCE({$this->db->dbprefix('staff_payslip')}.commission, 0)) AS total_commission, 
            SUM(COALESCE({$this->db->dbprefix('staff_payslip')}.tax, 0)) AS total_tax, 
            SUM(COALESCE({$this->db->dbprefix('staff_payslip')}.net_salary, 0)) AS total_amount", false)
        ->join('users', 'users.id=staff_payslip.staff_id', 'left')
        ->where('staff_payslip.status = 1')
        ->where('payment_date BETWEEN ' . $start . ' and ' . $end);

        if ($biller_id) {
            $this->db->where("staff_payslip.biller_id", $biller_id);
        }
        $q = $this->db->get('staff_payslip');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalPaidAmount($start, $end, $biller_id = null)
    {
        $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'sent')
            ->where('payments.date BETWEEN ' . $start . ' and ' . $end);
        if ($biller_id) {
            $this->db->where("sales.biller_id", $biller_id);
        }
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalPurchases($start, $end, $warehouse_id = null, $biller_id = null)
    {
        $this->db->select('count(id) as total, 
            sum(COALESCE(total, 0)) as subtotal_amount,
            sum(COALESCE(grand_total, 0)) as total_amount,  
            SUM(COALESCE(paid, 0)) as paid, 
            SUM(COALESCE(total_discount, 0)) as total_discount,
            SUM(COALESCE(shipping, 0)) as shipping,
            SUM(COALESCE(total_tax, 0)) as tax', false)
            ->where('status !=', 'pending')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($biller_id) {
            $this->db->where('biller_id', $biller_id);
        }
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedAmount($start, $end, $biller_id = null)
    {
        $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')
            ->where('payments.date BETWEEN ' . $start . ' and ' . $end);

        if ($biller_id) {
            $this->db->where("sales.biller_id", $biller_id);
        }
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedCashAmount($start, $end, $biller_id = null)
    {
        $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('paid_by', 'cash')
            ->where('payments.date BETWEEN ' . $start . ' and ' . $end);
        if ($biller_id) {
            $this->db->where("sales.biller_id", $biller_id);
        }
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedCCAmount($start, $end, $biller_id = null)
    {
        $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('paid_by', 'CC')
            ->where('payments.date BETWEEN ' . $start . ' and ' . $end);
        if ($biller_id) {
            $this->db->where("sales.biller_id", $biller_id);
        }
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedChequeAmount($start, $end, $biller_id = null)
    {
        $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('paid_by', 'Cheque')
            ->where('payments.date BETWEEN ' . $start . ' and ' . $end);
        if ($biller_id) {
            $this->db->where("sales.biller_id", $biller_id);
        }
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedPPPAmount($start, $end, $biller_id = null)
    {
        $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('paid_by', 'ppp')
            ->where('payments.date BETWEEN ' . $start . ' and ' . $end);
        if ($biller_id) {
            $this->db->where("sales.biller_id", $biller_id);
        }
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedStripeAmount($start, $end, $biller_id = null)
    {
        $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('paid_by', 'stripe')
            ->where('payments.date BETWEEN ' . $start . ' and ' . $end);
        if ($biller_id) {
            $this->db->where("sales.biller_id", $biller_id);
        }
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReturnedAmount($start, $end, $biller_id = null)
    {
        $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'returned')
            ->where('payments.date BETWEEN ' . $start . ' and ' . $end);
        if ($biller_id) {
            $this->db->where("sales.biller_id", $biller_id);
        }
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReturnSales($start, $end, $warehouse_id = null, $biller_id = null)
    {
        $this->db->select('count(id) as total, 
            sum(COALESCE(total, 0)) as stotal_amount, 
            sum(COALESCE(grand_total, 0)) as total_amount, 
            SUM(COALESCE(paid, 0)) as paid, 
            SUM(COALESCE(total_discount, 0)) as total_discount, 
            SUM(COALESCE(shipping, 0)) as shipping, 
            SUM(COALESCE(surcharge, 0)) as surcharge, 
            SUM(COALESCE(total_tax, 0)) as tax', false)
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($biller_id) {
            $this->db->where("biller_id", $biller_id);
        }
        $q = $this->db->get('returns');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalSales($start, $end, $warehouse_id = null, $biller_id = null)
    {
        $this->db->select('count(id) as total, 
            sum(COALESCE(grand_total, 0)) as total_amount, 
            sum(COALESCE(total, 0)) as sTotal_amount, 
            SUM(COALESCE(paid, 0)) as paid,
            SUM(COALESCE(total_discount, 0)) as total_discount,
            SUM(COALESCE(shipping, 0)) as shipping,
            SUM(COALESCE(total_tax, 0)) as tax', false)
            // ->where('sale_status !=', 'pending')
            ->where('store_sale !=', 1)
            ->where('sale_status !=', 'returned')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($biller_id) {
            $this->db->where("biller_id", $biller_id);
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalStore_Sales($start, $end, $warehouse_id = null, $biller_id = null)
    {
        $this->db->select('count(id) as total, 
            sum(COALESCE(grand_total, 0)) as total_amount, 
            sum(COALESCE(total, 0)) as sTotal_amount, 
            SUM(COALESCE(paid, 0)) as paid,
            SUM(COALESCE(total_discount, 0)) as total_discount,
            SUM(COALESCE(shipping, 0)) as shipping,
            SUM(COALESCE(total_tax, 0)) as tax', false)
            // ->where('sale_status !=', 'pending')
            ->where('store_sale', 1)
            ->where('date BETWEEN ' . $start . ' and ' . $end);

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($biller_id) {
            $this->db->where("biller_id", $biller_id);
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalStore_Purchases($start, $end, $warehouse_id = null, $biller_id = null)
    {
        $this->db->select('count(id) as total, 
            sum(COALESCE(grand_total, 0)) as total_amount, 
            sum(COALESCE(total, 0)) as sTotal_amount, 
            SUM(COALESCE(paid, 0)) as paid,
            SUM(COALESCE(total_discount, 0)) as total_discount,
            SUM(COALESCE(shipping, 0)) as shipping,
            SUM(COALESCE(total_tax, 0)) as tax', false)
            // ->where('sale_status !=', 'pending')
            ->where('store_sale', 1)
            ->where('date BETWEEN ' . $start . ' and ' . $end);

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($biller_id) {
            $this->db->where("customer_id", $biller_id);
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalPaymentReceived($start, $end, $biller_id = null)
    {
        $this->db->select("COUNT({$this->db->dbprefix('payments')}.id) AS total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) AS total_amount")
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('sales.store_sale !=', 1)
            ->where('payments.sale_id !=', null)
            ->where('payments.date >= ' . $start . ' AND payments.date <=' . $end);
        if ($biller_id) {
            $this->db->where("sales.biller_id", $biller_id);
        }
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturnOnSales($start, $end, $warehouse_id = null, $biller_id = null)
    {
        $this->db->select('count(id) as total, 
            sum(COALESCE(grand_total, 0)) as total_amount, 
            sum(COALESCE(total, 0)) as sTotal_amount, 
            SUM(COALESCE(paid, 0)) as paid,
            SUM(COALESCE(total_discount, 0)) as total_discount,
            SUM(COALESCE(shipping, 0)) as shipping,
            SUM(COALESCE(total_tax, 0)) as tax', false)
            ->where('store_sale !=', 1)
            ->where('sale_status', 'returned')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($biller_id) {
            $this->db->where('biller_id', $biller_id);
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseStockValue($id)
    {
        $q = $this->db->query('SELECT SUM(by_price) as stock_by_price, SUM(by_cost) as stock_by_cost FROM ( Select sum(COALESCE(' . $this->db->dbprefix('warehouses_products') . '.quantity, 0))*price as by_price, sum(COALESCE(' . $this->db->dbprefix('warehouses_products') . '.quantity, 0))*cost as by_cost FROM ' . $this->db->dbprefix('products') . ' JOIN ' . $this->db->dbprefix('warehouses_products') . ' ON ' . $this->db->dbprefix('warehouses_products') . '.product_id=' . $this->db->dbprefix('products') . '.id WHERE ' . $this->db->dbprefix('warehouses_products') . '.warehouse_id = ? GROUP BY ' . $this->db->dbprefix('products') . '.id )a', [$id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseTotals($warehouse_id = null)
    {
        $this->db->select('sum(quantity) as total_quantity, count(id) as total_items', false);
        $this->db->where('quantity !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('warehouses_products');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotaldiscounts($start, $end, $warehouse_id = NULL, $biller_id = null)
    {
        $this->db->select('count(id) as total, sum(COALESCE(total_discount, 0)) as total_amount', FALSE)
            ->where('store_sale !=', 1)
            ->where('sale_status !=', 'pending')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($biller_id) {
            $this->db->where("biller_id", $biller_id);
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getTotalCost_($start, $end, $warehouse_id = NULL, $biller_id = null)
    {
        $this->db->select("
            SUM( COALESCE( {$this->db->dbprefix('costing')}.purchase_unit_cost, 0 ) * {$this->db->dbprefix('costing')}.quantity ) AS cost, 
            SUM( COALESCE( {$this->db->dbprefix('costing')}.sale_unit_price, 0 ) * {$this->db->dbprefix('costing')}.quantity ) AS sales, 
            SUM( COALESCE( {$this->db->dbprefix('costing')}.purchase_net_unit_cost, 0 ) * {$this->db->dbprefix('costing')}.quantity ) AS net_cost, 
            SUM( COALESCE( {$this->db->dbprefix('costing')}.sale_net_unit_price, 0 ) * {$this->db->dbprefix('costing')}.quantity ) AS net_sales", FALSE);

        $this->db->join('sales', 'sales.id=costing.sale_id');
        $this->db->where('sales.date BETWEEN ' . $start . ' and ' . $end); 
        if ($warehouse_id) {
            $this->db->where("{$this->db->dbprefix('sales')}.warehouse_id", $warehouse_id);
        }
        if ($biller_id) {
            $this->db->where("{$this->db->dbprefix('sales')}.biller_id", $biller_id);
        }

        $q = $this->db->get('costing');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getTotalCost($start_date = false, $end_date = false, $warehouse = false, $biller = false, $project = false)
    {
        $this->db->select('
            sum(COALESCE(total_cost, 0)) AS cost,
            sum(COALESCE(grand_total, 0)) AS grand_total', FALSE);
        
        $this->db->join('(SELECT sum(COALESCE((quantity + IFNULL(foc,0)) * cost + IFNULL(extract_cost,0), 0)) AS total_cost,sale_id FROM '.$this->db->dbprefix('sale_items').' GROUP BY sale_id) as sale_items', 'sale_items.sale_id=sales.id','left');

        $this->db->where('sales.date BETWEEN ' . $start_date . ' and ' . $end_date); 

        if ($warehouse) {
            $this->db->where('sales.warehouse_id', $warehouse);
        }
        if ($biller) {
            $this->db->where('sales.biller_id', $biller);
        }
        if ($project) {
            $this->db->where('sales.project_id', $project);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->db->where('sales.created_by', $this->session->userdata('user_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where('sales.biller_id',$this->session->userdata('biller_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
        $q = $this->db->get('sales');
        
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getTotalReturnedwarehouse($start, $end, $warehouse_id = NULL)
    {
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total_return', FALSE)->where('sale_status', 'returned');
        $this->db->where('date BETWEEN ' . $start . ' and ' . $end);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getAllSales($sDate, $eDate, $biller_id = null){
        $this->db->select('sale_items.*,')
            ->where('date BETWEEN ' . $sDate . ' and ' . $eDate)
            ->where('sales.store_sale !=', 1)
            ->join('sale_items', 'sale_items.sale_id=sales.id');
        if ($biller_id) {
            $this->db->where("biller_id", $biller_id);
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }
    public function getLastDate($table,$field){
        $this->db->select("MAX(date_format($field,'%Y-%m-%d')) as datt");
        $q = $this->db->get("$table");
        if($q->num_rows()>0){
            return $q->row()->datt;
        }
        return false;
    }
    public function getBillers(){
        $this->db->select('bpas_companies.*');
        $this->db->where('group_name',"biller");
        $q=$this->db->get('bpas_companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
              
    }
    public function getWareByUserID(){
        $q = $this->db->get_where("users",array("id"=>$this->session->userdata('user_id')),1);
        if ($q->num_rows() > 0) {
            return $q->row()->warehouse_id;
        }
        return FALSE;
    }
    ///
    public function getDailySale($date, $warehouse_id = null)
    {
        $sdate = $date . ' 00:00:00';
        $edate = $date . ' 23:59:59';
        $this->db->select('SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping', false);
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        }
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getSaleVat($date, $warehouse_id = null, $year = null, $month = null)
    {
        $sdate = $date . ' 00:00:00';
        $edate = $date . ' 23:59:59';
        $this->db->select('SUM( COALESCE( total_tax, 0 ) ) AS total_tax', false);
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year . '-' . $month . '-01 00:00:00');
            $this->db->where('date <=', $year . '-' . $month . '-' . $last_day . ' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getconvert($product,$warehouse,$start_date,$end_date,$page,$offset){
        if($product){
            $this->db->where('bpas_convert_items.product_id',$product);
        }
        if($warehouse){
            $this->db->where('converts.warehouse_id',$warehouse);
        }
        if($start_date){
            $this->db->where("converts.date BETWEEN '$start_date' AND '$end_date'");
        }
         
        $this->db->select("bpas_convert_items.id,bpas_convert_items.product_id,bpas_convert_items.product_code,bpas_convert_items.product_name,SUM(bpas_convert_items.quantity) as con_qty,bpas_product_variants.name as var_name,bpas_units.name as unit")
        ->join('bpas_product_variants','bpas_product_variants.id = bpas_convert_items.option_id','LEFT')
        ->join('bpas_products','bpas_products.id=bpas_convert_items.product_id','LEFT')
        ->join('bpas_units','bpas_units.id=bpas_products.unit','left') 
        ->join('converts','converts.id=bpas_convert_items.convert_id','left')
        ->where("bpas_convert_items.status","add")
        ->group_by("bpas_convert_items.product_id")
        ->limit($page,$offset);
        $q = $this->db->get('convert_items');
        if ($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
       
    }
    public function getConvertDetailByID($id,$start,$end){
        
        $this->db->select("
            converts.reference_no,converts.date,converts.id,
            bpas_convert_items.product_id,bpas_warehouses.name as warehouse ,bpas_users.username")
        ->join('bpas_convert_items','bpas_convert_items.convert_id=converts.id','LEFT')
        ->join('bpas_warehouses','bpas_warehouses.id=converts.warehouse_id','LEFT')
        ->join('bpas_users','bpas_users.id=converts.created_by','LEFT')
        ->where('convert_items.product_id',$id);
        if($start){             
            $this->db->where("date_format({$this->db->dbprefix('converts')}.date,'%Y-%m-%d')  BETWEEN '$start' AND '$end'");
        }
        $q = $this->db->get('converts');
        if($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
        
    }
    public function getconvertDetail($id,$start,$end,$offset,$page,$reference_no,$warehouse,$created_by,$wid){
        if($start){
            $this->db->where("date_format({$this->db->dbprefix('converts')}.date,'%Y-%m-%d') BETWEEN '".$this->bpas->fsd($start)."' AND '".$this->bpas->fsd($end)."'");
        }
        if($reference_no){
            $this->db->where("{$this->db->dbprefix('converts')}.reference_no",$reference_no);
        }
        if($warehouse){
            $this->db->where("{$this->db->dbprefix('converts')}.warehouse_id",$warehouse);
        }else{
            if($wid){
                $this->db->where("{$this->db->dbprefix('converts')}.warehouse_id IN ($wid) ");
            }
        }
        if($created_by){
            $this->db->where("{$this->db->dbprefix('converts')}.created_by",$created_by);
        }
        $this->db->select("converts.reference_no,converts.date,{$this->db->dbprefix('converts')}.id,bpas_warehouses.name as warehouse ,bpas_users.username,converts.bom_id")
        ->join('bpas_warehouses','bpas_warehouses.id=converts.warehouse_id','LEFT')
        ->join('bpas_users','bpas_users.id=converts.created_by','LEFT')
        ->where('converts.bom_id',$id)
        ->limit($page,$offset);
        $q = $this->db->get('converts');
        if ($q->num_rows() > 0){
            foreach (($q->result()) as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
        
    }
    public function getBiilerByUserID(){
        $q = $this->db->get_where("users",array("id"=>$this->session->userdata('user_id')),1);
        if ($q->num_rows() > 0) {
            return $q->row()->biller_id;
        }
        return FALSE;
    }
    public function getWareFullByUSER($id){
        if (!$this->Owner && !$this->Admin) {
            $this->db->where("id",$this->session->userdata('warehouse_id'));
        }
        $q = $this->db->get("warehouses");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getConvertExportByID($id){
        $this->db->select("bpas_convert_items.id,bpas_convert_items.product_id,bpas_convert_items.product_code,bpas_convert_items.product_name,SUM(bpas_convert_items.quantity) as con_qty,bpas_units.name as unit")
        ->join('bpas_products','bpas_products.id=bpas_convert_items.product_id','LEFT')
        ->join('bpas_units','bpas_units.id = bpas_products.unit','LEFT')
        ->where("bpas_convert_items.status","add")
        ->where("bpas_convert_items.product_id",$id)
        ->group_by("bpas_convert_items.product_id")
        ->limit($page,$offset);
        $q = $this->db->get('bpas_convert_items');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE; 
    }
    
    function getConvertExportDetails($id){
        $this->db->select("converts.reference_no,converts.date,converts.id,bpas_convert_items.product_id,bpas_warehouses.name as warehouse ,bpas_users.username")
        ->join('bpas_convert_items','bpas_convert_items.convert_id=converts.id','LEFT')
        ->join('bpas_warehouses','bpas_warehouses.id=converts.warehouse_id','LEFT')
        ->join('bpas_users','bpas_users.id=converts.created_by','LEFT')
        ->where('bpas_convert_items.convert_id',$id)
        ->group_by('bpas_convert_items.convert_id')
        ->limit($page,$offset);
        $q = $this->db->get('converts');
        if ($q->num_rows() > 0){
            foreach (($q->result()) as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function get_detail($id) {
        $this->db->select("*")
               
                ->from('audit_bill_item')
                ->where("audit_id", $id)
                ->order_by('audit_bill_item.print_index');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
 
    public function getWarePur($wid,$warehouse,$product,$category,$biller){
        $this->db->select("warehouses.id,warehouses.name")
                 ->join("warehouses","warehouses.id=purchase_items.warehouse_id","LEFT")
                 ->join("products","products.id=purchase_items.product_id","LEFT")
                 //->join("bpas_purchases","bpas_purchases.id=purchase_items.purchase_id","LEFT")
               //  ->where(array('bpas_purchase_items.status !='=> 'pending', 'bpas_purchase_items.product_type !=' => 'service'));
                 ->where(array('purchase_items.status !='=> 'pending'));
        if ($warehouse) {
            $this->db->where("purchase_items.warehouse_id",$warehouse);
        } else {
            if($wid){
                $this->db->where("purchase_items.warehouse_id IN ($wid)");
            }
        }
        if ($product) {
            $this->db->where("purchase_items.product_id",$product);
        }
        if($biller){
            //$this->db->where("bpas_purchases.biller_id",$biller);
        }
        if($category){
            $this->db->where("products.category_id",$category);
        }
        //$this->db->where('purchase_items.date >="'.$start.'" AND purchase_items.date<="'.$end.'"');
        $this->db->group_by("purchase_items.warehouse_id");
        $q = $this->db->get("purchase_items");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getTransuctionsPurIN($product2,$warehouse2,$start,$end,$biller){
        $this->db->select("transaction_type");
        $this->db->join("bpas_purchases","bpas_purchases.id=purchase_items.purchase_id","LEFT");
        $this->db->where("quantity_balance > ",0);
        if($product2){
            $this->db->where("purchase_items.product_id",$product2);
        }
        if($warehouse2){
            $this->db->where("purchase_items.warehouse_id IN ($warehouse2)");
        }
        if($biller){
            $this->db->where("bpas_purchases.biller_id",$biller);
        }
        $this->db->where('purchase_items.date >="'.$start.'" AND purchase_items.date<="'.$end.'"');
        $this->db->where("transaction_type!=",null);
        $this->db->order_by("transaction_type","ASC");
        $this->db->group_by("transaction_type");
        $q = $this->db->get("purchase_items");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getTransuctionsPurOUT($product2,$warehouse2,$start,$end,$biller){
        $this->db->select("transaction_type");
        $this->db->join("bpas_purchases","bpas_purchases.id=purchase_items.purchase_id","LEFT");
        $this->db->where("quantity_balance<",0);
        if($product2){
            $this->db->where("purchase_items.product_id",$product2);
        }
        if($warehouse2){
            $this->db->where("purchase_items.warehouse_id IN ($warehouse2)");
        }
        if($biller){
            $this->db->where("bpas_purchases.biller_id",$biller);
        }
        $this->db->where('purchase_items.date >="'.$start.'" AND purchase_items.date<="'.$end.'"');
        $this->db->where("transaction_type!=",null);
        $this->db->order_by("transaction_type","ASC");
        $this->db->group_by("transaction_type");
        $q = $this->db->get("purchase_items");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getProCat($wid,$category2,$product2,$biller){
        $this->db->select("bpas_categories.id,bpas_categories.name")
                 ->join("bpas_categories","bpas_categories.id=products.category_id","LEFT")
                 ->join("purchase_items","purchase_items.product_id=products.id","LEFT")
                 //->join("bpas_purchases","bpas_purchases.id=purchase_items.purchase_id","LEFT")
                 ///->where(array('bpas_purchase_items.status !='=> 'pending', 'bpas_purchase_items.product_type !=' => 'service'));
                 ->where(array('bpas_purchase_items.status !='=> 'pending'));
        if($category2){
            $this->db->where(array("products.category_id"=>$category2));
        }
        if($product2){
            $this->db->where(array("products.id"=>$product2));
        }
        if($biller){
            //$this->db->where("bpas_purchases.biller_id",$biller);
        }
        //$this->db->where('purchase_items.date >="'.$start.'" AND purchase_items.date<="'.$end.'"');
        $this->db->where(array("purchase_items.warehouse_id"=>$wid));
        $this->db->group_by("products.category_id");
        $q = $this->db->get("products");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getProPur($wid,$cid,$product2,$biller){
        $this->db->select("product_id,products.code,products.name,units.name as name_unit,products.category_id,purchase_items.warehouse_id")
        ->join("products","products.id=purchase_items.product_id","LEFT")
        ->join("units","units.id=products.unit","LEFT")
        //->join("bpas_purchases","bpas_purchases.id=purchase_items.purchase_id","LEFT")
        ->where('bpas_purchase_items.status !=', 'pending')
        ->where('purchase_items.quantity_balance !=', 0);
        if($product2){
            $this->db->where(array("purchase_items.product_id"=>$product2));
        }
        if($biller){
            //$this->db->where("bpas_purchases.biller_id",$biller);
        }
        //$this->db->where('purchase_items.date >="'.$start.'" AND purchase_items.date<="'.$end.'"');
        $this->db->where(array("purchase_items.warehouse_id"=>$wid,"products.category_id"=>$cid));
        $this->db->group_by("purchase_items.product_id");
        $q = $this->db->get("purchase_items");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getBeginQtyINALL($id,$wid,$start,$end,$biller){
        $numMonth=1;
        $startDate=date('Y-m-01',strtotime($start . " - $numMonth month"));
        $endDate=date('Y-m-t',strtotime($start . " - $numMonth month"));
        $this->db->select("SUM(COALESCE(quantity_balance,0)) as bqty");
        $this->db->join("bpas_purchases","bpas_purchases.id=purchase_items.purchase_id","LEFT");
        $this->db->where("quantity_balance>",0);
        if($biller){
            $this->db->where("bpas_purchases.biller_id",$biller);
        }
        $this->db->where('purchase_items.date >="'.$startDate.'" AND purchase_items.date<="'.$endDate.'"');
        $this->db->where(array("product_id"=>$id,"purchase_items.warehouse_id"=>$wid));
        $q = $this->db->get("purchase_items");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getBeginQtyOUTALL($id,$wid,$start,$end,$biller){
        $numMonth=1;
        $startDate=date('Y-m-01',strtotime($start . " - $numMonth month"));
        $endDate=date('Y-m-t',strtotime($start . " - $numMonth month"));
        $this->db->select("SUM(COALESCE((-1)*quantity_balance,0)) as bqty");
        $this->db->join("bpas_purchases","bpas_purchases.id=purchase_items.purchase_id","LEFT");
        $this->db->where("quantity_balance<",0);
        if($biller){
            $this->db->where("bpas_purchases.biller_id",$biller);
        }
        $this->db->where('purchase_items.date >="'.$startDate.'" AND purchase_items.date<="'.$endDate.'"');
        $this->db->where(array("product_id"=>$id,"purchase_items.warehouse_id"=>$wid));
        $q = $this->db->get("purchase_items");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getBeginQtyALL($id,$wid,$start,$end,$biller){
        $numMonth=1;
        $startDate=date('Y-m-01',strtotime($start . " - $numMonth month"));
        $endDate=date('Y-m-t',strtotime($start . " - $numMonth month"));
        $this->db->select("SUM(COALESCE(quantity_balance,0)) as bqty");
        $this->db->join("bpas_purchases","bpas_purchases.id=purchase_items.purchase_id","LEFT");
        if($biller){
            $this->db->where("bpas_purchases.biller_id",$biller);
        }
        $this->db->where('purchase_items.date >="'.$startDate.'" AND purchase_items.date<="'.$endDate.'"');
        $this->db->where(array("bpas_purchase_items.product_id"=>$id,"purchase_items.warehouse_id"=>$wid));
    
        $q = $this->db->get("purchase_items");
        if ($q->num_rows() > 0) {
           return $q->row();
        }
        return FALSE;
    }
    public function getBookingByID($id)
    {
        $this->db->select("products.*,audit_booking.create_by as booking_by,audit_booking.note as description,audit_booking.expiry_date as expiry,audit_booking.current_date as booking_date, audit_booking.customer as booker,audit_booking.booking_price as booking_price" )
            ->join('products', 'products.id = audit_booking.product_id', 'left');
        $q = $this->db->get_where('audit_booking',['audit_booking.id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getCategoryStockValue($biller= NULL,$customer= NULL,$start_date= NULL,$end_date= NULL)
    {
        if($biller != NULL){
            $where_biller = " AND bpas_sales.biller_id=".$biller;
        }else{
            $where_biller = "";
        }
        if($customer != NULL){
            $where_customer = " AND bpas_sales.customer_id=".$customer;
        }else{
            $where_customer = "";
        }
        if($start_date != NULL && $end_date != NULL){
            $where_between_date = " AND bpas_sales.date between '$start_date' AND '$end_date'";
        }else{
            $where_between_date = "";
        }
        
        $q = $this->db->query("
            SELECT
                COALESCE (
                    sum(
                        bpas_sale_items.subtotal
                    ),
                    0
                ) AS by_price,
                bpas_categories.name AS category_name
            FROM
                bpas_products
            JOIN bpas_warehouses_products ON bpas_warehouses_products.product_id = bpas_products.id
            JOIN bpas_categories ON bpas_categories.id = bpas_products.category_id
            JOIN bpas_sale_items ON bpas_sale_items.product_id = bpas_products.id
            JOIN bpas_sales ON bpas_sales.id = bpas_sale_items.sale_id WHERE 1=1 $where_biller $where_customer $where_between_date
            GROUP BY
                bpas_categories.id");
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getCategoryStockValueById($id, $biller= NULL,$customer= NULL,$start_date= NULL,$end_date= NULL)
    {
        if($biller != NULL){
            $where_biller = " AND bpas_sales.biller_id=".$biller;
        }else{
            $where_biller = "";
        }
        if($customer != NULL){
            $where_customer = " AND bpas_sales.customer_id=".$customer;
        }else{
            $where_customer = "";
        }
        
        if($start_date != NULL && $end_date != NULL){
            $where_between_date = " AND bpas_sales.date between '$start_date' AND '$end_date'";
        }else{
            $where_between_date = "";
        }
        
        $q = $this->db->query("
            SELECT
                COALESCE (
                    sum(
                        bpas_sale_items.subtotal
                    ),
                    0
                ) AS by_price,
                bpas_categories.name AS category_name
            FROM
                bpas_products
            JOIN bpas_warehouses_products ON bpas_warehouses_products.product_id = bpas_products.id
            JOIN bpas_categories ON bpas_categories.id = bpas_products.category_id
            JOIN bpas_sale_items ON bpas_sale_items.product_id = bpas_products.id
            JOIN bpas_sales ON bpas_sales.id = bpas_sale_items.sale_id
            WHERE bpas_sale_items.warehouse_id = $id $where_biller $where_customer $where_between_date
            GROUP BY
                bpas_categories.id");
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getChartValue()
    {
        $q = $this->db->query("
            SELECT
                accountcode,
                accountname,
                COALESCE (
                    sum(
                        amount
                    ),
                    0
                ) AS total_amount
            FROM
                bpas_gl_charts
            LEFT JOIN bpas_gl_trans ON bpas_gl_trans.account_code = bpas_gl_charts.accountcode
            WHERE
                bpas_gl_charts.bank = 1
            GROUP BY
                accountcode");
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getChartValueById($id)
    {
        $q = $this->db->query("SELECT
                accountcode,
                accountname,
                COALESCE (
                    sum(
                        amount
                    ),
                    0
                ) AS total_amount
            FROM
                bpas_gl_charts
            LEFT JOIN bpas_gl_trans ON bpas_gl_trans.account_code = bpas_gl_charts.accountcode
            WHERE
                bpas_gl_charts.bank = 1 and bpas_gl_trans.account_code= $id
            GROUP BY
                accountcode");
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getAllProductsDetails_($product_id, $cid, $per_page, $ob_set, $start_date, $end_date)
    {
        $user_warehouses = $this->session->userdata('warehouse_id');
        if ($user_warehouses) {
            $this->db->select("products.*,units.name as uname,purchase_items.expiry");
            $this->db->join("units","units.id=products.unit","left");
            $this->db->join("purchase_items","products.id = purchase_items.product_id","left");
            $this->db->join('warehouses_products', 'purchase_items.product_id = warehouses_products.product_id', 'left');
            $this->db->order_by('purchase_items.id', 'desc');

            if ($this->Settings->product_expiry == 1) {
                $this->db->group_by('purchase_items.expiry');
            } else {
                $this->db->group_by('products.id');
                $this->db->where("warehouses_products.warehouse_id",$user_warehouses);
                $this->db->where("warehouses_products.quantity <>", 0);
            }
            if($cid){
                $this->db->where("category_id", $cid);
            }

            $this->db->where("products.type !=", "service");
            

            if($product_id){
                $this->db->where('purchase_items.product_id', $product_id);
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('purchase_items').'.expiry BETWEEN "' . $start_date . '" AND "' . $end_date . '"');
            }
            
            $this->db->limit($per_page, $ob_set); 
            $q = $this->db->get('products');
            if($q->num_rows()>0){
                foreach($q->result() as $row){
                    $data[] = $row;
                }
                return $data;
            }
            return false;

        } else {
            $this->db->select("products.*,units.name as uname,purchase_items.expiry");
            $this->db->join("units","units.id=products.unit","left");
            $this->db->join("purchase_items","products.id = purchase_items.product_id","left");
            $this->db->order_by('purchase_items.id', 'desc');

            if ($this->Settings->product_expiry == 1) {
                $this->db->group_by('purchase_items.expiry');
            } else {
                $this->db->group_by('products.id');
            }
            if($cid){
                $this->db->where("category_id", $cid);
            }
            $this->db->where("products.type !=", "service");

            if($product_id){
                $this->db->where('purchase_items.product_id', $product_id);
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('purchase_items').'.expiry BETWEEN "' . $start_date . '" AND "' . $end_date . '"');
            }
            
            $this->db->limit($per_page, $ob_set); 
            $q = $this->db->get('products');
            if($q->num_rows()>0){
                foreach($q->result() as $row){
                    $data[] = $row;
                }
                return $data;
            }
            return false;
        }
    }

    public function getAllProductsDetails($product_id, $cid, $per_page, $ob_set, $start_date, $end_date, $xls = false)
    {
        $user_warehouses = $this->session->userdata('warehouse_id');
        if ($user_warehouses) {
            $this->db->select("products.*,units.name as uname,purchase_items.expiry");
            $this->db->join("units","units.id=products.unit","left");
            $this->db->join("purchase_items","products.id = purchase_items.product_id","left");
            $this->db->join('warehouses_products', 'purchase_items.product_id = warehouses_products.product_id', 'left');
            // $this->db->order_by('purchase_items.id', 'desc');
            $this->db->order_by('products.code', 'ASC');

            if ($this->Settings->product_expiry == 1) {
                $this->db->group_by('purchase_items.expiry');
            }
            
            $this->db->group_by('products.id');
            // $this->db->where("warehouses_products.warehouse_id", $user_warehouses);
            // $this->db->where("warehouses_products.quantity <>", 0);
            
            if($cid){
                $this->db->where("category_id", $cid);
            }

            $this->db->where("products.type !=", "service");
            // $this->db->where('products.asset', 0);
            if($product_id){
                $this->db->where('purchase_items.product_id', $product_id);
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('purchase_items').'.expiry BETWEEN "' . $start_date . '" AND "' . $end_date . '"');
            }
            if(!$xls) {
                $this->db->limit($per_page, $ob_set);     
            }
            $q = $this->db->get('products');
            if($q->num_rows() > 0){
                foreach($q->result() as $row){
                    $data[] = $row;
                }
                return $data;
            }
            return false;
        } else {
            $this->db->select("products.*,units.name as uname,purchase_items.expiry");
            $this->db->join("units","units.id=products.unit","left");
            $this->db->join("purchase_items","products.id = purchase_items.product_id","left");
            $this->db->order_by('purchase_items.id', 'desc');

            if ($this->Settings->product_expiry == 1) {
                $this->db->group_by('purchase_items.expiry');
            }
                
            $this->db->group_by('products.id');
            $this->db->where("products.type !=", "service");
            // $this->db->where('products.asset', 0);

            if($cid){
                $this->db->where("category_id", $cid);
            }

            if($product_id){
                $this->db->where('purchase_items.product_id', $product_id);
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('purchase_items').'.expiry BETWEEN "' . $start_date . '" AND "' . $end_date . '"');
            }
            
            if(!$xls) {
                $this->db->limit($per_page, $ob_set);     
            }
            $q = $this->db->get('products');
            if($q->num_rows()>0){
                foreach($q->result() as $row){
                    $data[] = $row;
                }
                return $data;
            }
            return false;
        }
    }

    public function getQtyByWare_($pid,$wid,$product2,$category2,$biller2, $expiry, $wid1, $start_date1, $end_date1)
    {
        $user_warehouses = $this->session->userdata('warehouse_id');
        if ($user_warehouses) {

            if ($this->Settings->product_expiry == 1) {
                $this->db->select("SUM(COALESCE(bpas_purchase_items.quantity_balance,0)) as wqty");
                $this->db->join('products', 'purchase_items.product_id = products.id', 'left');
            } else {
                $this->db->select("bpas_warehouses_products.quantity as wqty");
                $this->db->join('products', 'purchase_items.product_id = products.id', 'left');
            }

            $this->db->where("purchase_items.status =", "received");
            $this->db->join('warehouses_products', 'purchase_items.product_id = warehouses_products.product_id', 'left');

            if ($this->Settings->product_expiry == 1) {
                $this->db->group_by('purchase_items.expiry');
                $this->db->where("purchase_items.expiry",$expiry);
            } else {
                $this->db->group_by('purchase_items.product_id');
            }
            
            $this->db->where("warehouses_products.product_id",$pid);
            $this->db->where("warehouses_products.warehouse_id",$wid);
            $this->db->where("warehouses_products.quantity <>", 0);

            if($product2){
                $this->db->where('purchase_items.product_id', $product2);
            }
            if($category2){
                $this->db->where('products.category_id', $category2);
            }
            if($biller2){
             //   $this->db->where('purchases.biller_id', $biller2);
            }
            if ($start_date1) {
                $this->db->where($this->db->dbprefix('products').'.start_date BETWEEN "' . $start_date1 . '" AND "' . $end_date1 . '"');
            }

            $q = $this->db->get("purchase_items");
            if ($q->num_rows() > 0) {
                return $q->row();
            }
            return FALSE;
        } else {
            $this->db->select("SUM(COALESCE(bpas_purchase_items.quantity_balance,0)) as wqty");
            $this->db->join('products', 'purchase_items.product_id = products.id', 'left');
            $this->db->join('purchases', 'purchase_items.purchase_id = purchases.id', 'left');
            $this->db->where("purchase_items.status =", "received");

            if ($this->Settings->product_expiry == 1) {
                $this->db->group_by('purchase_items.expiry');
                $this->db->where("purchase_items.expiry",$expiry);
            } else {
                $this->db->group_by('purchase_items.product_id');
            }

            $this->db->where("purchase_items.product_id",$pid);
            $this->db->where("purchase_items.warehouse_id",$wid);

            if($product2){
                $this->db->where('purchase_items.product_id', $product2);
            }
            if($category2){
                $this->db->where('products.category_id', $category2);
            }
            if($biller2){
                $this->db->where('purchases.biller_id', $biller2);
            }
            if ($start_date1) {
                $this->db->where($this->db->dbprefix('purchase_items').'.expiry BETWEEN "' . $start_date1 . '" AND "' . $end_date1 . '"');
            }

            $q = $this->db->get("purchase_items");
            if ($q->num_rows() > 0) {
                return $q->row();
            }
            return FALSE;
        }
    }

    public function getQtyByWare($pid, $wid, $product2, $category2, $biller2, $expiry, $wid1, $start_date1, $end_date1)
    {
        $user_warehouses = $this->session->userdata('warehouse_id');
        if ($user_warehouses) {
            if($this->Settings->product_expiry == 1) {
                $this->db->select("SUM(COALESCE({$this->db->dbprefix('purchase_items')}.quantity_balance, 0)) AS wqty");
                $this->db->join('purchase_items', 'purchase_items.product_id=products.id', 'left');    
                $this->db->where("purchase_items.status", "received");
                $this->db->where("purchase_items.warehouse_id", $wid);
                $this->db->where("purchase_items.expiry", $expiry);
                $this->db->group_by('purchase_items.expiry');
                $this->db->group_by('purchase_items.product_id');
                if($product2){
                    $this->db->where('purchase_items.product_id', $product2);
                }
                if ($start_date1) {
                    $this->db->where('purchase_items.expiry BETWEEN "' . $start_date1 . '" AND "' . $end_date1 . '"');
                }
            } else {
                $this->db->select("{$this->db->dbprefix('warehouses_products')}.quantity AS wqty");
                $this->db->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left');
                $this->db->where("warehouses_products.warehouse_id", $wid);
                $this->db->where("warehouses_products.quantity <>", 0);
                $this->db->group_by('warehouses_products.product_id');
            }

            if($pid){
                $this->db->where('products.id', $pid);
            }
            if($category2){
                $this->db->where('products.category_id', $category2);
            }

            $q = $this->db->get('products');
            if ($q->num_rows() > 0) {
                return $q->row();
            }
            return FALSE;
        } else {
            $this->db->select("SUM(COALESCE(bpas_purchase_items.quantity_balance,0)) as wqty");
            $this->db->join('products', 'purchase_items.product_id = products.id', 'left');
            $this->db->join('purchases', 'purchase_items.purchase_id = purchases.id', 'left');
            $this->db->where("purchase_items.status =", "received");

            if ($this->Settings->product_expiry == 1) {
                $this->db->group_by('purchase_items.expiry');
                $this->db->where("purchase_items.expiry",$expiry);
            }

            $this->db->group_by('purchase_items.product_id');
            $this->db->where("purchase_items.product_id",$pid);
            $this->db->where("purchase_items.warehouse_id",$wid);

            if($product2){
                $this->db->where('purchase_items.product_id', $product2);
            }
            if($category2){
                $this->db->where('products.category_id', $category2);
            }
            if($biller2){
                $this->db->where('purchases.biller_id', $biller2);
            }
            if ($start_date1) {
                $this->db->where($this->db->dbprefix('purchase_items').'.expiry BETWEEN "' . $start_date1 . '" AND "' . $end_date1 . '"');
            }

            $q = $this->db->get("purchase_items");
            if ($q->num_rows() > 0) {
                return $q->row();
            }
            return FALSE;
        }
    }

    public function getAllProductsDetailsNUM($pid,$cid)
    {
        $this->db->select("products.*,units.name as uname");
        $this->db->join("units","units.id=products.unit","LEFT");
        if($pid){
            $this->db->where("products.id",$pid);
        }
        if($cid){
            $this->db->where("category_id",$cid);
        }
        
        $q = $this->db->get('products');
        if($q->num_rows()>0){
            return $q->num_rows();
        }
        return false;
    }

    public function getsale_top_export($id,$start_date,$end_date)
    {
        $this->db->select("bpas_sale_items.id, 
            sale_items.product_code, 
            sale_items.product_name, 
            bpas_categories.name as category,  
            SUM(COALESCE(bpas_sale_items.quantity,0)) as quantity,           
            (bpas_units.name)")
            ->from('bpas_sale_items') 
            ->join('bpas_sales','bpas_sales.id=bpas_sale_items.sale_id','left')
            ->join('bpas_products','bpas_products.id=bpas_sale_items.product_id','left')
            ->join('bpas_categories','bpas_categories.id=bpas_products.category_id','left')
            ->join('bpas_units','bpas_units.id=bpas_products.unit','left')
            ->where('bpas_sale_items.product_id', $id)
            ->group_by('bpas_sale_items.product_id')
            ->order_by('quantity','DESC'); 
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            //    $this->db->where("DATE_FORMAT(bpas_sales.date,'%Y-%m-%d') >='{$start_date}' AND DATE_FORMAT(bpas_sales.date,'%Y-%m-%d') <= '{$end_date}'");
            }
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                return $q->row();
            }
            return false;   
    }
    public function getTransfersReport($reference_no,$start_date,$end_date,$from_warehouse,$to_warehouse,$product_id,$offset,$limit,$wid){ 
        if($reference_no){
            $this->db->where("bpas_transfers.transfer_no",$reference_no);
        } 
         if($product_id ){
           $this->db->where("bpas_purchase_items.product_id", $product_id);
        } 
        if($start_date){
            $this->db->where("date_format(bpas_transfers.date,'%Y-%m-%d')  BETWEEN '$start_date' AND '$end_date'");
        }
        if($from_warehouse){
            $this->db->where("bpas_transfers.from_warehouse_id",$from_warehouse);
        }
        if($to_warehouse){
            $this->db->where("bpas_transfers.to_warehouse_id",$to_warehouse);
        }
        if (!$this->Owner && !$this->Admin) {
            if($wid){
                //$this->db->where("bpas_transfers.from_warehouse_id IN ($wid)");
                $this->db->where("bpas_transfers.to_warehouse_id IN ($wid)");
            }
        }
        $this->db->select("bpas_transfers.*");
        if($product_id){ 
            $this->db->join("bpas_purchase_items", "bpas_purchase_items.transfer_id = bpas_transfers.id", "left");
        }
        $this->db->order_by("bpas_transfers.id","DESC");
        $this->db->limit($limit,$offset);
        
        $q =$this->db->get('bpas_transfers');
        if ($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getUsingStock($reference_no,$employee,$biller,$warehouse,$wid,$start_date,$end_date,$offset,$limit)
    {
        $this->db->select("
                bpas_enter_using_stock.id as id, bpas_enter_using_stock.reference_no as refno,
                bpas_companies.company, bpas_warehouses.name as warehouse_name, bpas_users.username, bpas_enter_using_stock.note, bpas_enter_using_stock.type as type, bpas_enter_using_stock.date, bpas_enter_using_stock.total_cost", FALSE)
            ->join('bpas_companies', 'bpas_companies.id=bpas_enter_using_stock.shop', 'left')
            ->join('bpas_warehouses', 'bpas_enter_using_stock.warehouse_id=bpas_warehouses.id', 'left')
            ->join('bpas_users', 'bpas_users.id=bpas_enter_using_stock.employee_id', 'inner');

        $this->db->limit($limit, $offset);
        if ($reference_no) {
            $this->db->where('bpas_enter_using_stock.reference_no',$reference_no);
        }
        if ($employee) {
            $this->db->where('bpas_users.id',$employee);
        }
        if ($biller) {
            $this->db->where('bpas_companies.id',$biller);
        }
        if ($warehouse) {
            $this->db->where('bpas_enter_using_stock.warehouse_id',$warehouse);
        } else {
            if ($wid) {
                $this->db->where("bpas_enter_using_stock.warehouse_id IN ($wid)");
            }
        }
        if ($start_date) {
            $this->db->where("date_format(bpas_enter_using_stock.date,'%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'");
        }       
        $q =$this->db->get('bpas_enter_using_stock');
        if ($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
                     
    }
    public function getUsingStockDetails($id){
        $this->db->select("bpas_enter_using_stock.*");
        $this->db->order_by("bpas_enter_using_stock.id","DESC");
        //$this->db->limit($limit,$offset);
        $this->db->where("bpas_enter_using_stock.id",$id);
        $q =$this->db->get('bpas_enter_using_stock');
        if ($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getUsingStockReport($id,$offset,$limit){
        $this->db->select("bpas_enter_using_stock.id as id,    bpas_enter_using_stock.reference_no as refno,
        bpas_companies.company, bpas_warehouses.name as warehouse_name, bpas_users.username, bpas_enter_using_stock.note, bpas_enter_using_stock.type as type, bpas_enter_using_stock.date, bpas_enter_using_stock.total_cost", FALSE)
        ->join('bpas_companies', 'bpas_companies.id=bpas_enter_using_stock.shop', 'inner')
        ->join('bpas_warehouses', 'bpas_enter_using_stock.warehouse_id=bpas_warehouses.id', 'left')
        ->join('bpas_users', 'bpas_users.id=bpas_enter_using_stock.employee_id', 'inner')
        ->where('bpas_enter_using_stock.id', $id);
        $this->db->limit($limit,$offset);       
        $q =$this->db->get('bpas_enter_using_stock');
        if ($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;            
    }
    public function getRoomPurchases($room_id)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('suspend_note', $room_id);
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getRoomSales($room_id)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('suspend_note', $room_id);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getRoomDailySales($room_id, $year, $month)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . "
            WHERE suspend_note = {$room_id} AND DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getRoomMonthlySales($room_id, $year)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . "
            WHERE suspend_note = {$room_id} AND DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function showing($data,$per_page){
        $count_all = count($data);      
        return showing($count_all,$data,$per_page);
    }

    public function getCustomersPending($condition = null, $sale_id = null, $customer = null, $product = null, $reference = null, $biller = null, $warehouse = null, $saleman = null, $start_date = null, $end_date = null, $sale_type = null, $payment_status = null)
    {
        if($condition == 'customer') {
            $this->db->select('sales.*, sales.id as sale_id, companies.*'); 
        } elseif($condition == 'sale') {
            $this->db->select('sales.*, COALESCE(bpas_deli.total_delivery, 0) as total_delivery'); 
        } elseif($condition == 'item') {
            $this->db->select('
                sale_items.*, 
                sale_unit.code AS product_unit_code, sale_unit.name AS product_unit_name,
                tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, 
                products.image, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name, 
                products.unit as base_unit_id, units.code as base_unit_code, products.category_id, products.subcategory_id, products.cf1 as width,
                products.cf2 as length,
                products.cf3 as product_cf3,
                products.cf4 as product_cf4,
                products.cf5 as product_cf5,
                products.cf6 as product_cf6'); 
        } else {
            $this->db->select('payments.*');
        }

        $this->db->from('sales')
            ->join('sale_items', 'sales.id = sale_items.sale_id', 'left')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('units', 'units.id=products.unit', 'left')
            ->join('units sale_unit', 'sale_unit.id=sale_items.product_unit_id', 'left')
            ->join('companies', 'sales.customer_id = companies.id', 'left')
            ->join('payments', 'sales.id = payments.sale_id', 'left')
            ->join(" ( SELECT COUNT(*) as total_delivery, d.sale_id FROM {$this->db->dbprefix('deliveries')} d GROUP BY d.sale_id ) bpas_deli ", "deli.sale_id=sales.id", 'left');
            // ->where('sales.payment_status !=', 'paid')
            // ->where('sales.sale_status !=', 'returned')
            // ->where("({$this->db->dbprefix('sales')}.grand_total - {$this->db->dbprefix('sales')}.paid) >", 0)
            // ->where("({$this->db->dbprefix('sales')}.grand_total + {$this->db->dbprefix('sales')}.return_sale_total) > " , 0);
        if ($sale_id) {
            $this->db->where('sales.id', $sale_id);
        }
        if ($customer) {
            $this->db->where('sales.customer_id', $customer);
        }
        if ($product) {
            $this->db->where('sale_items.product_id', $product);
        }
        if ($reference) {
            $this->db->where('sales.reference_no', $reference);
        }
        if ($biller) {
            $this->db->where('sales.biller_id', $biller);
        }
        if ($warehouse) {
            $this->db->where('sales.warehouse_id', $warehouse);
        }
        if ($saleman) {
            $this->db->where('sales.saleman_by', $saleman);
        }
        if ($start_date) {
            $this->db->where("{$this->db->dbprefix('sales')}.date BETWEEN '" . $start_date . " 00:00:00' and '" . $end_date . " 23:59:00'");
        }
        if ($sale_type !='') {
            $this->db->where('sales.pos', $sale_type);
        }
        if ($payment_status) {
            if ($payment_status == 'paid') {
                $this->db->where('sales.payment_status', 'paid');
            } elseif ($payment_status == 'unpaid') {
                $this->db->where('sales.payment_status !=', 'paid');
            }
        }

        if($condition == 'customer') {
            $this->db->group_by('sales.customer_id');    
        } elseif($condition == 'sale') {
            $this->db->group_by('sales.id');
        } elseif($condition == 'item') {
            $this->db->group_by('sale_items.id');    
        } else {
            $this->db->group_by('payments.id'); 
        }
        
        $this->db->order_by('sales.date');
        $this->db->order_by('payments.date');
        $this->db->order_by('companies.name');

        $q = $this->db->get();
        if ($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE; 
    }

    public function getPRByPayRef($ref) 
    {
        $this->db->select('payments.*')
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.reference_no', $ref)
            ->order_by('sales.date', 'asc');
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE; 
    }

    public function getPVByPayRef($ref) 
    {
        $this->db->select('payments.*')
            ->join('purchases', 'purchases.id=payments.purchase_id', 'left')
            ->where('payments.reference_no', $ref)
            ->order_by('purchases.date', 'asc');
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE; 
    }

    public function getSalesByPayRef($ref) 
    {
        $sub_q = " ( SELECT COALESCE(SUM(COALESCE(p.amount, 0)), 0) FROM {$this->db->dbprefix('payments')} p WHERE p.sale_id = {$this->db->dbprefix('sales')}.id AND p.date < {$this->db->dbprefix('payments')}.date GROUP BY p.sale_id LIMIT 1 ) ";
        $this->db->select("{$this->db->dbprefix('sales')}.*, {$this->db->dbprefix('sales')}.grand_total - COALESCE({$sub_q}, 0) AS amount_before_paid")
            ->join('payments', 'sales.id=payments.sale_id', 'left')
            ->where('payments.reference_no', $ref)
            ->order_by('sales.date', 'asc');
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE; 
    }

    public function getPurchasesByPayRef($ref) 
    {
        $sub_q = " ( SELECT COALESCE(SUM(COALESCE(p.amount, 0)), 0) FROM {$this->db->dbprefix('payments')} p WHERE p.purchase_id = {$this->db->dbprefix('purchases')}.id AND p.date < {$this->db->dbprefix('payments')}.date GROUP BY p.purchase_id LIMIT 1 ) ";
        $this->db->select("{$this->db->dbprefix('purchases')}.*, {$this->db->dbprefix('purchases')}.grand_total - COALESCE({$sub_q}, 0) AS amount_before_paid")
            ->join('payments', 'purchases.id=payments.purchase_id', 'left')
            ->where('payments.reference_no', $ref)
            ->order_by('purchases.date', 'asc');
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE; 
    }
    public function getLoanProducts()
    {
        $q = $this->db->get("loan_products");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getTotalProducts($warehouse_id = null)
    {
        $this->db->select('count(id) as total', false);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getTotalProperties($warehouse_id = null)
    {
        $this->db->select('count(id) as total', false);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->where('products.module_type', 'property');
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getTotalStatus(){
        $this->db->select('COUNT(CASE WHEN quantity = 1  THEN id END) as available, COUNT(CASE WHEN quantity = 2 THEN id END) as booking, COUNT(CASE WHEN quantity = -2 THEN id END) as blocking, COUNT(CASE WHEN quantity = -1 THEN id END) as sold', false)
        ->where('module_type','property');
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getTotalReceivedByCurrencyAmount($start, $end)
    {
      //  $currencies = $this->bpas->paid_by();
        
        $this->db->select('paid_by,sum(amount) as amount');
     //   $this->db->where('type', 'received')->or_where('type', 'booking');
        $this->db->where('type !=', 'sent');
        $this->db->where('date BETWEEN ' . $start . ' and ' . $end);
        $this->db->group_by('paid_by');
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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
    public function getExpensesBycategories($start, $end, $biller_id = null, $category=null, $warehouse_id = null)
    {
        $this->db->select(
                'count(bpas_expenses.id) as total, 
                '.$this->db->dbprefix("expense_items").'.category_name as name, 
                sum(COALESCE(subtotal, 0)) as total_amount', false);
        
        $this->db->where('expenses.date >='.$start);
        $this->db->where('expenses.date <=' . $end);

        if ($warehouse_id) {
            $this->db->where('expenses.warehouse_id', $warehouse_id);
        }
        if ($biller_id) {
            $this->db->where("expenses.biller_id", $biller_id);
        }

        $this->db->join('expenses','expenses.id = expense_items.expense_id' ,'left');
        $this->db->group_by('expense_items.category_id');
        $q = $this->db->get('expense_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function installmentTimes(){
        $q = $this->db->query("SELECT
                                    max(id) AS id 
                                FROM
                                    ( 
                                    SELECT count(".$this->db->dbprefix('installment_items').".id ) AS id FROM ".$this->db->dbprefix('installment_items')." 
                                    GROUP BY ".$this->db->dbprefix('installment_items').".installment_id 
                                ) AS installment_times
                            ");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getInstallments($status = false,$biller_id = false,$project_id = false,$warehouse_id = false,$customer_id = false,$start_date = false,$end_date = false, $grade_id = false, $fee_type = false){
        if($status){
            $this->db->where("installments.status",$status);
        }
        if($biller_id){
            $this->db->where("installments.biller_id",$biller_id);
        }
        if($project_id){
            $this->db->where("installments.project_id",$project_id);
        }
        if($warehouse_id){
            $this->db->where("installments.warehouse_id",$warehouse_id);
        }
        if($customer_id){
            $this->db->where("installments.customer_id",$customer_id);
        }
        if($start_date){
            $this->db->where("installments.created_date >=",$this->bpas->fld($start_date));
        }
        if($end_date){
            $this->db->where("installments.created_date <=",$this->bpas->fld($end_date));
        }
        $select = "";
        if($this->config->item("schools")){
            if($grade_id){
                $this->db->where("sales.grade_id",$grade_id);
            }
            if($fee_type){
                $this->db->where("sales.fee_type",$fee_type);
            }
            $this->db->join("sales","sales.id = installments.sale_id","left");
            $this->db->join("sh_grades","sh_grades.id = sales.grade_id","left");
            $select = "sh_grades.name as grade_name";
        }
        $this->db->select("installments.*,companies.code as customer_code,".$select);
        $this->db->join("companies","companies.id = installments.customer_id","left");
        $q = $this->db->get("installments");
        if($q->num_rows()>0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getIndexInstallmentItems($status = false,$biller_id = false,$project_id = false,$warehouse_id = false,$customer_id = false,$start_date = false,$end_date = false){
        
        if($status){
            $this->db->where("installments.status",$status);
        }
        if($biller_id){
            $this->db->where("installments.biller_id",$biller_id);
        }
        if($project_id){
            $this->db->where("installments.project_id",$project_id);
        }
        if($warehouse_id){
            $this->db->where("installments.warehouse_id",$warehouse_id);
        }
        if($customer_id){
            $this->db->where("installments.customer_id",$customer_id);
        }
        if($start_date){
            $this->db->where("installments.created_date >=",$this->bpas->fld($start_date));
        }
        if($end_date){
            $this->db->where("installments.created_date <=",$this->bpas->fld($end_date));
        }
        $this->db->select("installment_items.*");
        $this->db->join("installments","installments.id = installment_items.installment_id","inner");
        $this->db->order_by("installment_items.id");
        $this->db->group_by("installment_items.id");
        $q = $this->db->get("installment_items");
        if($q->num_rows()>0){
            foreach($q->result() as $row){
                $data[$row->installment_id][] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getMonthlyInstallmentPayments($biller_id = false,$project_id = false,$warehouse_id = false,$customer_id = false, $year = false){
        if($biller_id){
            $this->db->where("installments.biller_id",$biller_id);
        }
        if($project_id){
            $this->db->where("installments.project_id",$project_id);
        }
        if($warehouse_id){
            $this->db->where("installments.warehouse_id",$warehouse_id);
        }
        if($customer_id){
            $this->db->where("installments.customer_id",$customer_id);
        }
        if($year){
            $this->db->where("YEAR(".$this->db->dbprefix('payments').".date)",$year);
        }
        $this->db->select("installments.biller_id,MONTH(".$this->db->dbprefix('payments').".date) as month, sum(".$this->db->dbprefix('payments').".amount + ".$this->db->dbprefix('payments').".interest_paid) as paid");
        $this->db->join("payments","payments.installment_id = installments.id","inner");
        $this->db->group_by("installments.biller_id,MONTH(".$this->db->dbprefix('payments').".date)");
        $q = $this->db->get("installments");
        if($q->num_rows()>0){
            foreach($q->result() as $row){
                $data[$row->biller_id][$row->month] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAuditTrailByID($id = false)
    {
        $q = $this->db->get_where("user_audit_trails", array("id"=>$id));
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    //
    public function getTop10Sale($warehouse_id = null,$offset = null){
        $this->db->select("bpas_sale_items.id, 
            sale_items.product_code, 
            sale_items.product_name, 
            bpas_categories.name as category,  
            SUM(COALESCE(bpas_sale_items.quantity,0)) as quantity,           
            (bpas_units.name)")
            ->from('bpas_sale_items') 
            ->join('bpas_sales','bpas_sales.id=bpas_sale_items.id','left')
            ->join('bpas_products','bpas_products.id=bpas_sale_items.product_id','left')
            ->join('bpas_categories','bpas_categories.id=bpas_products.category_id','left')
            ->join('bpas_units','bpas_units.id=bpas_products.unit','left')
            // ->where('bpas_sale_items.product_id', $id)
            ->group_by('bpas_sale_items.product_id')
            ->order_by('quantity','DESC'); 
            if($warehouse_id){
                $this->db->where('bpas_sales.warehouse_id',$warehouse_id);
            }
            $this->db->limit(10,$offset);  
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
            }
            // if ($q->num_rows() > 0) {
            //     return $q->row();
            // }
            return false;   
    }
    //
    public function getTop10Profit($warehouse_id = null,$offset = null){
        $this->db->select("
            bpas_sale_items.product_id, 
            bpas_sales.date,
            sale_items.product_code, 
            sale_items.product_name, 
            bpas_categories.name as category,  
            if(bpas_sale_items.option_id, bpas_product_variants.name,bpas_units.name) AS NAME,
            SUM(purchase_net_unit_cost) AS cost_amount,
            SUM(sale_net_unit_price) as price_amount,
            ((SUM(sale_net_unit_price)) - (SUM(purchase_net_unit_cost))) as profit", FALSE )
            ->from('bpas_sale_items') 
            ->join('bpas_sales','bpas_sales.id=bpas_sale_items.sale_id','left')
            ->join('bpas_products','bpas_products.id=bpas_sale_items.product_id','left')
            ->join('bpas_costing','bpas_costing.product_id=bpas_products.id','left')
            ->join('bpas_categories','bpas_categories.id=bpas_products.category_id','left')
            ->join('bpas_units','bpas_units.id=bpas_products.unit','left')
            ->join('bpas_product_variants','bpas_sale_items.option_id = bpas_product_variants.id','left')
            ->group_by('bpas_sale_items.product_id');
            // if($warehouse_id){
            //     $this->db->where('bpas_sales.warehouse_id',$warehouse_id);
            // }
            // $this->db->limit(10,$offset);  
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
            }
            // if ($q->num_rows() > 0) {
            //     return $q->row();
            // }
            return false;   
    }

    public function getAllExpenses( $warehouse_id = null)
    {
        $this->db->select('expense_categories.name as name, expense_categories.code as code, SUM( COALESCE( amount, 0 ) ) AS total', false);
        $this->db->from('expenses');
        $this->db->join('expense_categories','expense_categories.id = expenses.category_id' ,'left');
        $this->db->group_by('category_id');
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
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
       public function getAllTotalExpensesCategories()
    {
        $this->db->select('SUM(category_id) as total', false);
        $this->db->join('expenses','expenses.category_id =  expense_categories.id' ,'left');
        // $this->db->group_by("expenses.category_id");
        $q = $this->db->get('expense_categories');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

       public function getAllTotalExpenses( $warehouse_id = null, $start = null, $end = null, $category=null)
    {
        $this->db->select('count(bpas_expenses.category_id) as total, sum(COALESCE(amount, 0)) as total_amount', false);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->join('expense_categories','expense_categories.id = expenses.category_id' ,'left');
        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalCostByPayment($start, $end,$biller_id=null, $warehouse_id = NULL)
    {
        $this->db->select('
            SUM( (IF(bpas_products.type = "service", COALESCE( service_cost, 0 ), COALESCE( purchase_unit_cost, 0 ))) * bpas_costing.quantity ) AS cost, 
            SUM( COALESCE( sale_unit_price, 0 ) * bpas_costing.quantity ) AS sales, 
            SUM( (IF(bpas_products.type = "service", COALESCE( service_cost, 0 ), COALESCE( purchase_net_unit_cost, 0 ))) * bpas_costing.quantity ) AS net_cost, 
            SUM( COALESCE( sale_net_unit_price, 0 ) * bpas_costing.quantity ) AS net_sales', FALSE);
        $this->db->join('products', 'products.id=costing.product_id', 'left');
        $this->db->where('costing.date BETWEEN ' . $start . ' and ' . $end);
        $this->db->where('bpas_costing.sale_id IN (
            SELECT bpas_payments.sale_id FROM bpas_payments
            WHERE bpas_payments.sale_id != "null"
            GROUP BY bpas_payments.sale_id)', NULL, FALSE
        );
        if ($biller_id) {
            $this->db->join('sales', 'sales.id=costing.sale_id')
            ->where('sales.biller_id', $biller_id);
        }
        if ($warehouse_id) {
            $this->db->join('sales', 'sales.id=costing.sale_id')
            ->where('sales.warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('costing');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getStockWarehouse($warehouse_id = null)
    {
        $this->db->select('sum(quantity) as total_quantity, warehouses.name as name, warehouses.code as code', false);
        $this->db->join('warehouses','warehouses.id = warehouses_products.warehouse_id', 'left');
        $this->db->where('quantity !=', 0);
        $this->db->group_by('warehouses.id');
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('warehouses_products');
         if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }
   
    public function getRegisterCCSales($date, $user_id = null, $end = null)
    {
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cc_slips, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')
            ->where('paid_by', 'CC');

            $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $date . '" and "' . $end . '"');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRegisterCashSales($date, $user_id = null, $end = null)
    { 
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')
            ->where('paid_by', 'cash');

            $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $date . '" and "' . $end . '"');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRegisterChSales($date, $user_id = null, $end = null)
    { 
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')
            ->where('paid_by', 'Cheque');

            $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $date . '" and "' . $end . '"');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    //updated
    public function getRegisterGCSales($date, $user_id = null, $end = null)
    {
        
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('paid_by', 'gift_card');
             $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $date . '" and "' . $end . '"');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRegisterPPPSales($date, $user_id = null, $end = null)
    {
      
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('paid_by', 'ppp');
             $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $date . '" and "' . $end . '"');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRegisterStripeSales($date, $user_id = null, $end = null)
    {
      
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('paid_by', 'stripe');
             $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $date . '" and "' . $end . '"');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRegisterAuthorizeSales($date, $user_id = null, $end = null)
    {
       
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('paid_by', 'authorize');
             $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $date . '" and "' . $end . '"');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRegisterSales($date, $user_id = null, $end = null)
    {
      
      $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total,SUM(order_discount) AS discount,SUM(order_tax) AS tax, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received');
             $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $date . '" and "' . $end . '"');;
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRegisterABASales($date, $user_id = null,$type_of_payment = null, $end = null)
    {   
   
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id = payments.sale_id', 'left')
            ->where('payments.type', 'received')
            ->where('sales.date >', $date)
            ->where('paid_by', $type_of_payment); 
             $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $date . '" and "' . $end . '"');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRegisterTotalTrans($date, $user_id = null, $end = null)
    {
       
        $this->db->select('COUNT(' . $this->db->dbprefix('sales') . '.id) as total_trans', false)
            //->join('payments', 'sales.id=payments.sale_id', 'left')
            ->where('date >', $date)->where('created_by', $user_id);

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRegisterRefunds($date, $user_id = null, $end = null)
    {
        
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', false)
            ->join('sales', 'sales.id=payments.return_id', 'left')
            ->where('payments.type', 'returned');
             $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $date . '" and "' . $end . '"');;
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterReturns($date, $user_id = null, $end = null)
    {
       
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total', false)
        ->where('date >', $date)
        ->where('returns.created_by', $user_id);

        $q = $this->db->get('returns');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRegisterCashRefunds($date, $user_id = null, $end = null)
    {
         
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', false)
            ->join('sales', 'sales.id=payments.return_id', 'left')
            ->where('payments.type', 'returned')->where('paid_by', 'cash');
             $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $date . '" and "' . $end . '"');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRegisterExpenses($date, $user_id = null, $end = null)
    {
      
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS total', false)
            ->where('date >', $date);
        $this->db->where('created_by', $user_id);

        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getSuspendedSales($user_id = null)
    { 
        $q = $this->db->get_where('suspended_bills', ['created_by' => $user_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }
    public function getProductsInOut($category =null, $warehouse  =null,$start_date  =null, $end_date  =null, $user_id = null)
    {
  
        $pp = "( SELECT product_id, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(quantity_balance) as balacneQty, SUM( unit_cost * quantity_balance ) balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id WHERE pi.status = 'received' "; 
        $sp = '( SELECT si.product_id, SUM( si.quantity ) soldQty, SUM(item_discount) totalDiscount, SUM(item_tax) totalTax, SUM( si.subtotal ) totalSale from ' . $this->db->dbprefix('sales') . ' s JOIN ' . $this->db->dbprefix('sale_items') . ' si on s.id = si.sale_id '; 
        if ($start_date ) {
            $sp .= ' WHERE ';
            if ($start_date) {
                $pp .= " AND p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
            }
            if ($warehouse) {
                $pp .= " AND pi.warehouse_id = ".$warehouse." ";
                $sp .= " AND si.warehouse_id = ".$warehouse." ";
            }
        }
        if (!$this->Owner && !$this->Admin) {
            $sp .= " AND s.created_by = ".$user_id."";
            // $this->db->where($this->db->dbprefix('sale_items') . '.created_by', $this->session->userdata('user_id'));
        }
        $pp .= ' GROUP BY pi.product_id ) PCosts';
        $sp .= ' GROUP BY si.product_id ) PSales';
            $this->db->select("products.code, products.name,categories.name as category,
                PCosts.purchasedQty as purchasedQty,PCosts.totalPurchase as totalPurchase,
                PSales.soldQty as soldQty, PSales.totalSale as totalSale,PSales.totalTax as totalTax,PSales.totalDiscount as totalDiscount,
                PCosts.balacneQty as balacneQty, PCosts.balacneValue as balacneValue,
                products.id as id", false)
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->join("categories","categories.id=products.category_id","LEFT")
                ->where('PSales.soldQty >',0);
            //    ->group_by('products.code')
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . '.category_id', $category);
            }
        $q = $this->db->get("products");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        } 
        return FALSE; 
    }
    public function getCategoryInOut($category,$warehouse,$start_date,$end_date)
    {
        $pp = "( SELECT product_id, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(quantity_balance) as balacneQty, SUM( unit_cost * quantity_balance ) balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id WHERE pi.status = 'received' ";
        $sp = '( SELECT si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from ' . $this->db->dbprefix('sales') . ' s JOIN ' . $this->db->dbprefix('sale_items') . ' si on s.id = si.sale_id ';
        if ($start_date || $warehouse) {
            $sp .= ' WHERE ';
            if ($start_date) {
                $pp .= " AND p.date >= '".$start_date."' AND p.date < '".$end_date."' ";
                $sp .= "s.date >= '".$start_date."' AND s.date < '".$end_date."' ";
            }
            if ($warehouse) {
                $pp .= " AND pi.warehouse_id = ".$warehouse." ";
                $sp .= " AND si.warehouse_id = ".$warehouse." ";
            }
        }
        if (!$this->Owner && !$this->Admin) {
            $sp .= " AND s.created_by = ".$this->session->userdata('user_id')."";
        }
        $pp .= ' GROUP BY pi.product_id ) PCosts';
        $sp .= ' GROUP BY si.product_id ) PSales'; 
        $this->db->select("categories.id as category_id,categories.name as category", false)
                ->join($sp, 'products.id = PSales.product_id', 'left')->join($pp, 'products.id = PCosts.product_id', 'left')
                ->join("categories","categories.id=products.category_id","LEFT")->where('PSales.soldQty >',0)->group_by('products.category_id');
        if ($category) {
            $this->db->where($this->db->dbprefix('products') . '.category_id', $category);
        } 
        $q = $this->db->get("products");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE; 
    } 
    public function getCategoryInOuts($start_date = null, $end_date = null, $user_id = null, $id = null)
    {
    
        $pp = "( SELECT product_id, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(quantity_balance) as balacneQty, SUM( unit_cost * quantity_balance ) balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id WHERE pi.status = 'received' ";
        $sp = '( SELECT si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from ' . $this->db->dbprefix('sales') . ' s JOIN ' . $this->db->dbprefix('sale_items') . ' si on s.id = si.sale_id ';
        if ($start_date) {
            $sp .= ' WHERE ';
            if ($start_date) {
                $pp .= " AND p.date >= '".$start_date."' AND p.date < '".$end_date."' ";
                $sp .= "s.date >= '".$start_date."' AND s.date < '".$end_date."' ";
            }
        }
        if (!$this->Owner && !$this->Admin) {
            $sp .= " AND s.created_by = ".$user_id."";
        }
        $pp .= ' GROUP BY pi.product_id ) PCosts';
        $sp .= ' GROUP BY si.product_id ) PSales';
            $this->db->select("categories.id as category_id,categories.name as category", false)
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->join("categories","categories.id=products.category_id","LEFT")
                ->where('PSales.soldQty >',0)
                ->group_by('products.category_id');
        $q = $this->db->get("products");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllTechnicians()
    {
        $this->db->where('technician',1);
        $q = $this->db->get("users");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

      public function getTotalExapensesByCategory_Month($category_id, $m)
    {
        $y = date('Y');
        $start_date = $y . '-' . $m .'-'. '01 00:00:00';
        $end_date   = $y . '-' . $m .'-'. days_in_month($m, $y) . '  23:59:59';

        $this->db
            ->select('date, COALESCE(SUM(amount), 0) as total_amount')
            ->where('date BETWEEN ' . "'$start_date'" . ' and ' . "'$end_date'")
            ->where('category_id',$category_id)
            ->group_by('category_id');
        
        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }   
        return false;
        
    }

    public function getAllGreandtotalSaleBy_Month($m)
    {
        $y = date('Y');
        $start_date = $y.'-'.$m.'-'.'01 00:00:00';
        $end_date   = $y.'-'.$m.'-'. days_in_month($m, $y).'  23:59:59';

        $this->db
            ->select('COALESCE(SUM(grand_total), 0) as total_amount')
            ->where('date BETWEEN ' . "'$start_date'" . ' and ' . "'$end_date'")
            ->where('sale_status','completed');
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }   
        return false;
        
    }
    public function getValuationProducts($product_id = false){
        if($product_id){
            $this->db->where('products.id', $product_id);
        }
        $this->db->where_in("products.type",array('standard','raw_material','asset'));
        $this->db->order_by("products.code");
        $q = $this->db->get("products");
        if($q->num_rows()>0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getIndexReceivePayment($biller_id = false,$project_id = false,$user_id = false,$start_date = false, $end_date = false, $status = false, $type = false){
        if($biller_id){
            $this->db->where("sales.biller_id",$biller_id);
        }
        if($project_id){
            $this->db->where("sales.project_id",$project_id);
        }
        if ($user_id) {
            $this->db->where('payments.created_by', $user_id);
        }
        if($start_date){
            $this->db->where("payments.date >=",$this->bpas->fld($start_date));
        }
        if($end_date){
            $this->db->where("payments.date <=",$this->bpas->fld($end_date,false,1));
        }
        if ($status) {
            $this->db->where('receive_payments.status', $status);
        }
        if ($type) {
            if($type=="sale"){
                $this->db->where("IFNULL(".$this->db->dbprefix('sales').".pos,0)",0);
            }else{
                $this->db->where("IFNULL(".$this->db->dbprefix('sales').".pos,0)",1);
            }
        }
        
        $this->db->select("
                        sales.biller_id,
                        IFNULL(".$this->db->dbprefix('cash_accounts').".id,'other') as paid_by,
                        SUM(IFNULL(".$this->db->dbprefix('payments').".amount,0) + IFNULL(".$this->db->dbprefix('payments').".interest_paid,0) + IFNULL(".$this->db->dbprefix('payments').".penalty_paid,0)) as amount
                    ")
                    ->join("sales","sales.id = payments.sale_id","inner")
                    ->join("cash_accounts","cash_accounts.code = payments.paid_by","left")
                    //->join("receive_payment_items","receive_payment_items.payment_id = payments.id","left")
                    //->join("receive_payments","receive_payments.id = receive_payment_items.receive_id","left")
                    ->group_by("sales.biller_id,payments.paid_by");
        $q = $this->db->get("payments");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->paid_by] = $row;
            }
            return $data;
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
    public function getEmployeesWorkingInfoByEmployeeID($id = false)
    {
        $q = $this->db->get_where('hr_employees_working_info', array('employee_id' => $id), 1);
        
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getPolicyByEmployeeID($id = false){
        $this->db->select('att_policies.*')
        ->join('hr_employees_working_info','hr_employees_working_info.policy_id = att_policies.id','inner')
        ->where('hr_employees_working_info.employee_id',$id);
        $q = $this->db->get('att_policies');    
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
    public function getAllexchange_items($id )
    {
        $this->db->select('reward_exchange_items.*,')
        ->group_by('reward_exchange_items.id')
        ->order_by('id', 'asc');
        $this->db->where('reward_exchange_id', $id);
        $q = $this->db->get('reward_exchange_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductBySales($category_id = false, $start_date = false, $end_date = false, $product = false, $warehouse_id = false, $saleman = false, $biller = false, $project = false, $customer = false)
    {
        $user = $this->site->getUser($this->session->userdata("user_id"));
        $sql = "";
        if ($category_id) {
            $sql .= " AND ".$this->db->dbprefix('products').".category_id = {$category_id}";            
        }
        if ($biller) {
            $sql .= " AND ".$this->db->dbprefix('sales').".biller_id = {$biller}";          
        }
        if ($customer) {
            $sql .= " AND ".$this->db->dbprefix('sales').".customer_id = {$customer}";          
        }
        if ($project) {
            $sql .= " AND ".$this->db->dbprefix('sales').".project_id = {$project}";            
        }
        if ($saleman) {
            $sql .= " AND ".$this->db->dbprefix('sales').".saleman_by = {$saleman}";            
        }
        if($product){
            $sql .= " AND ".$this->db->dbprefix('sale_items').".product_id= {$product}";
        }
        if ($start_date) {
            $sql .= " AND date >= '{$this->bpas->fld($start_date)}'";
        }
        if($end_date){
            $sql .= " AND date <= '{$this->bpas->fld($end_date,false,1)}'";
        }
        if(!$start_date && !$end_date){
            $sql .= " AND date(date) = '".date('Y-m-d')."' ";
        }
        if ($warehouse_id) {
            $sql .= " AND ".$this->db->dbprefix('sales').".warehouse_id = {$warehouse_id}";         
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $sql .= " AND ".$this->db->dbprefix('sales').".created_by = {$this->session->userdata('user_id')}";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $sql .= " AND ".$this->db->dbprefix('sales').".biller_id = {$this->session->userdata('biller_id')}";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
            $warehouse_ids = str_replace(']',')',$warehouse_ids);
            $sql .= " AND ".$this->db->dbprefix('sales').".warehouse_id IN (".$warehouse_ids.")";
        }
        if (!$this->Owner && !$this->Admin && $this->Settings->project) {
            $projects = json_decode($user->project_ids); 
            $project_details = "";
            if ($projects) {
                foreach ($projects as $pr) {
                    $project_details .= $pr.",";
                }
            }
            if (!$project && $projects[0] != 'all') {
                $rtrim = rtrim($project_details,",");
                if ($rtrim) {
                    $sql .= " AND ".$this->db->dbprefix('sales').".project_id IN ({$rtrim})";
                }
            }
        }
        $this->db->query("SET group_concat_max_len = 10000000");
        $result = $this->db->query("
                    SELECT
                        ".$this->db->dbprefix('sale_items').".product_id,
                        ".$this->db->dbprefix('sale_items').".product_code,
                        ".$this->db->dbprefix('sale_items').".product_type,
                        ".$this->db->dbprefix('sale_items').".unit_price,
                        ".$this->db->dbprefix('sale_items').".product_unit_id,
                        ".$this->db->dbprefix('units').".name as unit_name,
                        GROUP_CONCAT(".$this->db->dbprefix('sale_items').".raw_materials SEPARATOR'#') as raw_materials,
                        product_name,
                        sum(".$this->db->dbprefix('sale_items').".cost * ".$this->db->dbprefix('sale_items').".quantity + IFNULL(".$this->db->dbprefix('sale_items').".foc,0)) as cost,
                        sum(".$this->db->dbprefix('sale_items').".unit_price)  / count(".$this->db->dbprefix('sale_items').".id) as price,
                        bpas_sale_items.tax,
                        SUM(".$this->db->dbprefix('sale_items').".item_discount) as item_discount,
                        SUM(".$this->db->dbprefix('sale_items').".quantity) as quantity,
                        SUM(".$this->db->dbprefix('sale_items').".unit_quantity) as unit_quantity,
                        SUM(".$this->db->dbprefix('sale_items').".foc) as foc,
                        SUM(".$this->db->dbprefix('sale_items').".subtotal) as subtotal,
                        ".$this->db->dbprefix('products').".quantity as stock_quantity,
                        ".$this->db->dbprefix('sales').".reference_no,
                        ".$this->db->dbprefix('sales').".customer
                    FROM ".$this->db->dbprefix('sale_items')."
                    LEFT JOIN ".$this->db->dbprefix('products')." ON ".$this->db->dbprefix('products').".id = product_id
                    LEFT JOIN ".$this->db->dbprefix('sales')." ON ".$this->db->dbprefix('sales').".id = ".$this->db->dbprefix('sale_items').".sale_id
                    LEFT JOIN ".$this->db->dbprefix('units')." ON ".$this->db->dbprefix('sale_items').".product_unit_id = ".$this->db->dbprefix('units').".id
                    WHERE 1 = 1 " . $sql . "
                    GROUP BY ".$this->db->dbprefix('products').".id, unit_price, product_unit_id")->result();
        return $result;
    }

    public function getAllCategoriesByInventoryInOut($category_id = false)
    {       
        $allow_category = $this->site->getCategoryByProject();
        if ($allow_category) {
            $this->db->where_in("categories.id", $allow_category);
        }
        if ($category_id) {
            $this->db->where("id", $category_id);
        }
        $this->db->order_by("parent_id");
        $q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    //---------fuel----------
    public function getDailyTankItems()
    {
        $q = $this->db->select("
                        product_id, 
                        products.name as product_name")
                      ->join("products","products.id=tank_nozzles.product_id","left")
                      ->join('tanks','tanks.id=tank_nozzles.tank_id','left')
                      ->group_by('products.id')
                      ->order_by('tanks.id, nozzle_no','asc')
                      ->get('tank_nozzles');
                      
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getDailyTanks()
    {
        $post = $this->input->post();
        if(isset($post['tank']) && !empty($post['tank'])){
            $this->db->where("tanks.id", $post['tank']);
        }
        $q = $this->db->select("
                        tank_nozzles.id,
                        tank_nozzles.nozzle_no,
                        tank_id,
                        tanks.name as tank")
                      ->join('tanks','tanks.id=tank_nozzles.tank_id','left')
                      ->group_by('tank_nozzles.id')
                      ->order_by('tanks.id, nozzle_no','asc')
                      ->get('tank_nozzles');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
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
    public function getDailyTankItemsQty($tank_id = null, $nozzle_id = null, $product_id = NULL)
    {
        $post = $this->input->post();
        if(isset($post['biller']) && !empty($post['biller'])){
            $this->db->where("fuel_sales.biller_id", trim($post['biller']));
        }
        if(isset($post['saleman']) && !empty($post['saleman'])){
            $this->db->where("fuel_sales.saleman_id", trim($post['saleman']));
        }
        if(isset($post['start_date']) && !empty($post['start_date'])){
            $this->db->where('fuel_sales.date BETWEEN "' . $this->bpas->fld($post['start_date']) . '" and "' . $this->bpas->fld($post['end_date']) . '"');
        }
        if(isset($post['warehouse']) && !empty($post['warehouse'])){
            $this->db->where("fuel_sales.warehouse_id", $post['warehouse']);
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where('fuel_sales.biller_id', $this->session->userdata('biller_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('fuel_sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->db->where("fuel_sales.created_by", $this->session->userdata('view_right'));
        }
        $q = $this->db->select('
                            MIN(nozzle_start_no) as nozzle_start_no,
                            MAX(nozzle_end_no) as nozzle_end_no,
                            SUM(IFNULL(quantity,0)) as quantity,
                            SUM(IFNULL(using_qty,0)) as using_qty,
                            SUM(IFNULL(customer_qty,0)) as customer_qty')
                      ->from('fuel_sale_items')
                      ->join('fuel_sales','fuel_sales.id=fuel_sale_items.fuel_sale_id','left')
                      ->where("tank_id", $tank_id)
                      ->where("nozzle_id", $nozzle_id)
                      ->where("product_id", $product_id)
                      ->get();
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    public function getSaleFuelDetails($limit = 0, $start = 0)
    {
        $post = $this->input->post()?$this->input->post():$this->input->get();
        if(isset($post['biller']) && !empty($post['biller'])){
            $this->db->where("fuel_sales.biller_id", trim($post['biller']));
        }
        if(isset($post['saleman']) && !empty($post['saleman'])){
            $this->db->where("fuel_sales.saleman_id", trim($post['saleman']));
        }
        if(isset($post['project']) && !empty($post['project'])){
            $this->db->where("fuel_sales.project_id", trim($post['project']));
        }
        if(isset($post['user']) && !empty($post['user'])){
            $this->db->where("fuel_sales.created_by", trim($post['user']));
        }
        if(isset($post['start_date']) && !empty($post['start_date'])){
            $this->db->where('fuel_sales.date BETWEEN "' . $this->bpas->fld($post['start_date']) . '" and "' . $this->bpas->fld($post['end_date']) . '"');
        }
        if(isset($post['reference_no']) && !empty($post['reference_no'])){
            $this->db->like("fuel_sales.reference_no", $post['reference_no']);
        }
        if(isset($post['warehouse']) && !empty($post['warehouse'])){
            $this->db->where("fuel_sales.warehouse_id", $post['warehouse']);
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where('fuel_sales.biller_id', $this->session->userdata('biller_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('fuel_sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->db->where("fuel_sales.created_by", $this->session->userdata('view_right'));
        }
        if(!$post){
            $this->db->where('fuel_sales.date', date("Y-m-d"));
        }
        $q = $this->db->limit($limit, $start)
                      ->select("
                        fuel_sales.id,
                        fuel_sales.date, 
                        fuel_sales.reference_no, 
                        fuel_sales.biller, 
                        fuel_sales.saleman, 
                        CONCAT(bpas_fuel_times.open_time,' - ',bpas_fuel_times.close_time) as time,
                        IFNULL(bpas_fuel_sale_items.using_qty,0) as using_qty,
                        IFNULL(bpas_fuel_sale_items.customer_qty,0) as customer_qty,
                        IFNULL(bpas_fuel_sale_items.customer_amount,0) as customer_amount,
                        IFNULL(bpas_fuel_sale_items.quantity,0) as quantity,
                        IFNULL(".$this->db->dbprefix('fuel_sales').".total,0) as total,
                        IFNULL(".$this->db->dbprefix('fuel_sales').".total_cash,0) as total_cash,
                        IFNULL(".$this->db->dbprefix('fuel_sales').".credit_amount,0) as credit_amount,
                        IFNULL(".$this->db->dbprefix('fuel_sales').".bank_amount,0) as bank_amount,
                        IFNULL(".$this->db->dbprefix('fuel_sales').".total_cash_open,0) as total_cash_open,
                        CONCAT(last_name,' ',first_name) as username,
                        IF(ROUND(bpas_sales.quantity,".$this->Settings->decimals.")>=ROUND(bpas_fuel_sale_items.quantity,".$this->Settings->decimals."),'completed',IF(bpas_sales.quantity > 0,'partial','pending')) as status")
                      ->from("fuel_sales")
                      ->join('fuel_times', 'fuel_times.id=fuel_sales.time_id', 'left')
                      ->join('(SELECT 
                                        fuel_sale_id,
                                        SUM(subtotal) as subtotal,
                                        SUM(quantity) as quantity
                                    FROM '.$this->db->dbprefix('sales').'
                                    LEFT JOIN '.$this->db->dbprefix('sale_items').' ON '.$this->db->dbprefix('sale_items').'.sale_id = bpas_sales.id
                                    GROUP BY fuel_sale_id) as bpas_sales','bpas_sales.fuel_sale_id=fuel_sales.id','left')
                      ->join('(SELECT 
                                        fuel_sale_id,
                                        SUM(quantity) as quantity,
                                        SUM(using_qty) as using_qty,
                                        SUM(customer_qty) as customer_qty,
                                        SUM(customer_amount) as customer_amount
                                    FROM '.$this->db->dbprefix('fuel_sale_items').'
                                    GROUP BY fuel_sale_id) as bpas_fuel_sale_items','bpas_fuel_sale_items.fuel_sale_id=fuel_sales.id','left')
                      ->join("users","users.id=fuel_sales.created_by","left")
                      ->order_by("fuel_sales.id","desc")
                      ->get();
                      
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getSaleFuelItemsDetails($fuel_sale_id = 0)
    {
        $q = $this->db->select("
                            fuel_sale_items.tank_id,
                            fuel_sale_items.product_id,
                            fuel_sale_items.nozzle_id,
                            fuel_sale_items.nozzle_no,
                            fuel_sale_items.nozzle_start_no,
                            fuel_sale_items.nozzle_end_no,
                            fuel_sale_items.quantity,
                            fuel_sale_items.customer_qty,
                            fuel_sale_items.using_qty,
                            fuel_sale_items.customer_amount,
                            tanks.name as tank,
                            products.name as item")
                      ->from("fuel_sale_items")
                      ->join("tanks","tanks.id=tank_id","left")
                      ->join("products","products.id=product_id","left")
                      ->where("fuel_sale_id", $fuel_sale_id)
                      ->order_by("fuel_sale_items.id","desc")
                      ->get();
                      
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getProductByPurchases($category_id = false, $start_date = false, $end_date = false, $product = false, $warehouse_id = false, $biller = false, $project = false, $supplier = false)
    {
        $user = $this->site->getUser($this->session->userdata("user_id"));
        $sql = "";
        
        if ($category_id) {
            $sql .= " AND ".$this->db->dbprefix('products').".category_id = {$category_id}";            
        }
        if ($biller) {
            $sql .= " AND ".$this->db->dbprefix('purchases').".biller_id = {$biller}";          
        }
        if ($supplier) {
            $sql .= " AND ".$this->db->dbprefix('purchases').".supplier_id = {$supplier}";          
        }
        if ($project) {
            $sql .= " AND ".$this->db->dbprefix('purchases').".project_id = {$project}";            
        }
        if($product){
            $sql .= " AND ".$this->db->dbprefix('purchase_items').".product_id= {$product}";
        }
        if ($start_date) {
            $sql .= " AND ".$this->db->dbprefix('purchases').".date >= '{$this->bpas->fld($start_date)}'";
        }
        if($end_date){
            $sql .= " AND ".$this->db->dbprefix('purchases').".date <= '{$this->bpas->fld($end_date,false,1)}'";
        }
        if(!$start_date && !$end_date){
            $sql .= " AND date(".$this->db->dbprefix('purchases').".date) = '".date('Y-m-d')."' ";
        }
        if ($warehouse_id) {
            $sql .= " AND ".$this->db->dbprefix('purchases').".warehouse_id = {$warehouse_id}";         
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $sql .= " AND ".$this->db->dbprefix('purchases').".created_by = {$this->session->userdata('user_id')}";
        }
        
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $sql .= " AND ".$this->db->dbprefix('purchases').".biller_id = {$this->session->userdata('biller_id')}";
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
            $warehouse_ids = str_replace(']',')',$warehouse_ids);
            $sql .= " AND ".$this->db->dbprefix('purchases').".warehouse_id IN ".$warehouse_ids;
        }
        
        if (!$this->Owner && !$this->Admin && $this->Settings->project) {
            $projects = json_decode($user->project_ids); 
            $project_details = "";
            if($projects){
                foreach($projects as $pr){
                    $project_details .= $pr.",";
                }
            }
            
            if(!$project && $projects[0] != 'all'){
                $rtrim = rtrim($project_details,",");
                if($rtrim){
                    $sql .= " AND ".$this->db->dbprefix('purchases').".project_id IN ({$rtrim})";
                }
                
            }
        }
        $this->db->query("SET group_concat_max_len = 10000000");
        $result = $this->db->query("SELECT
                                        ".$this->db->dbprefix('purchase_items').".product_id,
                                        ".$this->db->dbprefix('purchase_items').".product_code,
                                        ".$this->db->dbprefix('purchase_items').".product_type,
                                        ".$this->db->dbprefix('purchase_items').".unit_cost,
                                        product_name,
                                        SUM(".$this->db->dbprefix('purchase_items').".item_discount) as item_discount,
                                        SUM(".$this->db->dbprefix('purchase_items').".quantity) as quantity,
                                        SUM(".$this->db->dbprefix('purchase_items').".subtotal) as subtotal,
                                        ".$this->db->dbprefix('purchases').".supplier
                                    FROM
                                        ".$this->db->dbprefix('purchase_items')."
                                    LEFT JOIN ".$this->db->dbprefix('products')." ON ".$this->db->dbprefix('products').".id = product_id
                                    LEFT JOIN ".$this->db->dbprefix('purchases')." ON ".$this->db->dbprefix('purchases').".id = ".$this->db->dbprefix('purchase_items').".purchase_id
                                    WHERE 1=1
                                    {$sql}
                                    GROUP BY
                                        ".$this->db->dbprefix('products').".id, 
                                        unit_cost, 
                                        product_unit_id")->result();
        return $result;
    }
    public function getProductSerails()
    {
        $product = $this->input->post('product');
        $category = $this->input->post('category');
        $warehouse = $this->input->post('warehouse');
        $status = $this->input->post('status');
        if($product){
            $this->db->where("product_serials.product_id", $product);
        }
        if($category){
            $this->db->where("products.category_id", $category);
        }
        if($warehouse){
            $this->db->where("product_serials.warehouse_id", $warehouse);
        }
        if($status != ""){
            if($status == '1'){
                $this->db->where("product_serials.inactive", 1);
            }else{
                $this->db->where("product_serials.inactive != ", 1);
            }
            
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
            $this->db->where_in('product_serials.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
        $this->db->select('product_serials.*,products.name as product_name, products.code as product_code, warehouses.name as warehouse_name')
                    ->join('products','products.id = product_serials.product_id','inner')
                    ->join('warehouses','warehouses.id = product_serials.warehouse_id','inner')
                    ->order_by('products.code,warehouses.id')
                    ->order_by('product_serials.inactive','desc');
                    
        $q = $this->db->get('product_serials');
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;   
    }
}