<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');

            $this->db2 = $this->load->database('db2', TRUE);
        }

        $this->lang->admin_load('reports', $this->Settings->user_language);
        $this->lang->admin_load('pos', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('auth_model');
        $this->load->admin_model('pos_model');
        $this->load->admin_model('reports_model');
        $this->load->admin_model('accounts_model');
        $this->load->admin_model('products_model');
        $this->load->admin_model('sales_model');
        $this->load->admin_model('companies_model');
        $this->load->admin_model('site');
        $this->data['pb'] = [
            'cash'       => lang('cash'),
            'CC'         => lang('CC'),
            'Cheque'     => lang('Cheque'),
            'paypal_pro' => lang('paypal_pro'),
            'stripe'     => lang('stripe'),
            'gift_card'  => lang('gift_card'),
            'deposit'    => lang('deposit'),
            'authorize'  => lang('authorize'),
        ];
    } 
    public function products()
    {
        $this->bpas->checkPermissions();
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['brands']     = $this->site->getAllBrands();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['product_variants'] = $this->site->getAllProductVariants();

        $products   = $this->site->getAllProducts();
        $warehouses = $this->site->getAllWarehouses();
        // if (!empty($products)) {
        //     foreach ($products as $product) {
        //         foreach ($warehouses as $warehouse) {
        //             if($this->site->checkSyncStock($product->id, $warehouse->id)) {
        //                 $this->site->syncQuantity_13_05_21($product->id);
        //             }   
        //         }
        //     }
        // }

        if ($this->input->post('start_date')) {
            $dt = 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
        } else {
            $dt = 'Till ' . $this->input->post('end_date');
        }
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('products_report')]];
        $meta = ['page_title' => lang('products_report'), 'bc' => $bc];
        $this->page_construct('master/products_beginning', $meta, $this->data);
    }
    public function getProductsReport($pdf = null, $xls = null, $preview = null)
    {
        $this->bpas->checkPermissions('index');
        $dataTable_filter = $this->input->post('sSearch') ? $this->input->post('sSearch') : null;
        $dataTable_filter_col_1 = $this->input->post('sSearch_0') ? $this->input->post('sSearch_0') : null;
        $dataTable_filter_col_2 = $this->input->post('sSearch_1') ? $this->input->post('sSearch_1') : null;

        $product     = $this->input->get('product') ? $this->input->get('product') : null;
        $category    = $this->input->get('category') ? $this->input->get('category') : null;
        $brand       = $this->input->get('brand') ? $this->input->get('brand') : null;
        $subcategory = $this->input->get('subcategory') ? $this->input->get('subcategory') : null;
        $warehouse   = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $cf1         = $this->input->get('cf1') ? $this->input->get('cf1') : null;
        $cf2         = $this->input->get('cf2') ? $this->input->get('cf2') : null;
        $cf3         = $this->input->get('cf3') ? $this->input->get('cf3') : null;
        $cf4         = $this->input->get('cf4') ? $this->input->get('cf4') : null;
        $cf5         = $this->input->get('cf5') ? $this->input->get('cf5') : null;
        $cf6         = $this->input->get('cf6') ? $this->input->get('cf6') : null;
        $start_date  = $this->input->get('start_date') ? $this->input->get('start_date') : date('d-m-Y') . " 00:00:00";
        $end_date    = $this->input->get('end_date') ? $this->input->get('end_date') : date('d-m-Y') . " 23:59:59";

        $pp                     = "( SELECT product_id, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(quantity_balance) as balacneQty, SUM( unit_cost * quantity_balance ) balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id WHERE pi.status = 'received' ";
        $ending_purchases       = $pp;

        $pps                    = '( SELECT spi.product_id, SUM( spi.quantity ) purchasedQty FROM ' . $this->db->dbprefix('sales') . ' sp LEFT JOIN ' . $this->db->dbprefix('sale_items') . ' spi on sp.id = spi.sale_id WHERE sp.sale_status != "returned" AND sp.sale_status != "pending" AND sp.store_sale = 1 ';
        $ending_pps             = $pps;
        $pps_returns            = '( SELECT spi.product_id, Abs(SUM( spi.quantity )) purchasedQty FROM ' . $this->db->dbprefix('sales') . ' sp LEFT JOIN ' . $this->db->dbprefix('sale_items') . ' spi on sp.id = spi.sale_id WHERE sp.sale_status = "returned" AND sp.store_sale = 1 ';
        $ending_pps_returns     = $pps_returns;

        $sp                     = '( SELECT si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale, SUM( si.item_discount ) totalItemDiscount, SUM( si.total_weight ) totalWeight, s.order_discount as order_discount from ' . $this->db->dbprefix('sales') . ' s LEFT JOIN ' . $this->db->dbprefix('sale_items') . ' si on s.id = si.sale_id WHERE s.sale_status != "returned" AND s.sale_status != "pending" ';
        $sp_addon  = '( SELECT si_addon.product_id, SUM( si_addon.quantity ) soldQty, SUM( si_addon.subtotal ) totalSale, s_addon.order_discount as order_discount from ' . $this->db->dbprefix('sales') . ' s_addon LEFT JOIN ' . $this->db->dbprefix('sale_addon_items') . ' si_addon on s_addon.id = si_addon.sale_id WHERE s_addon.sale_status != "returned" AND s_addon.sale_status != "pending" ';
        $sp_combo  = '( SELECT si_combo.product_id, SUM( si_combo.quantity ) soldQty, SUM( si_combo.subtotal ) totalSale, s_combo.order_discount as order_discount from ' . $this->db->dbprefix('sales') . ' s_combo LEFT JOIN ' . $this->db->dbprefix('sale_combo_items') . ' si_combo on s_combo.id = si_combo.sale_id WHERE s_combo.sale_status != "returned" AND s_combo.sale_status != "pending" ';
        $pos_sales  = " ( SELECT product_id,order_discount, SUM( ci.quantity ) soldQty FROM " . $this->db->dbprefix('sales') . " s LEFT JOIN " . $this->db->dbprefix('costing') . " ci on s.id = ci.sale_id WHERE s.sale_status != 'pending' AND s.pos = 1 ";
        $ending_sales = '( SELECT si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale, SUM( si.item_discount ) totalItemDiscount, SUM( si.total_weight ) totalWeight, s.order_discount as order_discount from ' . $this->db->dbprefix('sales') . ' s LEFT JOIN ' . $this->db->dbprefix('sale_items') . ' si on s.id = si.sale_id WHERE s.sale_status != "pending" ';
        $ending_addonsales = '( SELECT si_addon.product_id, SUM( si_addon.quantity ) soldQty, SUM( si_addon.subtotal ) totalSale, s_addon.order_discount as order_discount from ' . $this->db->dbprefix('sales') . ' s_addon LEFT JOIN ' . $this->db->dbprefix('sale_addon_items') . ' si_addon on s_addon.id = si_addon.sale_id WHERE s_addon.sale_status != "pending" ';
        $ending_combosales = '( SELECT si_combo.product_id, SUM( si_combo.quantity ) soldQty, SUM( si_combo.subtotal ) totalSale, s_combo.order_discount as order_discount from ' . $this->db->dbprefix('sales') . ' s_combo LEFT JOIN ' . $this->db->dbprefix('sale_combo_items') . ' si_combo on s_combo.id = si_combo.sale_id WHERE s_combo.sale_status != "pending" ';
        $ending_pos_sales  = " ( SELECT product_id, SUM( ci.quantity ) soldQty FROM " . $this->db->dbprefix('sales') . " s LEFT JOIN " . $this->db->dbprefix('costing') . " ci on s.id = ci.sale_id WHERE s.sale_status != 'pending' AND s.pos = 1 ";

        $pr1                    = '( SELECT si_return.product_id, SUM( si_return.quantity ) soldQty_return, SUM( si_return.subtotal ) totalSale_return from ' . $this->db->dbprefix('sales') . ' ss LEFT JOIN ' . $this->db->dbprefix('sale_items') . ' si_return on ss.id = si_return.sale_id WHERE ss.sale_status = "returned" ';
        $pr2                    = "( SELECT product_id, SUM(sri.quantity) as returnQty, SUM(sri.subtotal) returnTotalSale from {$this->db->dbprefix('returns')} sr LEFT JOIN {$this->db->dbprefix('return_items')} sri on sr.id = sri.return_id";
        $ending_returns         = $pr2;

        $transfers              = "( SELECT product_id, SUM(quantity) as transferQty, SUM(quantity_balance) as balacneQty, SUM(unit_cost * quantity_balance) balacneValue, SUM(pi.subtotal) totalTransfer from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('transfers')} t on t.id = pi.transfer_id WHERE pi.status = 'received' ";
        $ending_transfers_in    = $transfers_in  = $transfers;
        $ending_transfers_out   = $transfers_out = $transfers;

        $adjustments_add        = " ( SELECT aji.product_id, SUM(aji.quantity) adjustmentQty FROM {$this->db->dbprefix('adjustment_items')} aji LEFT JOIN {$this->db->dbprefix('adjustments')} aj ON aj.id = aji.adjustment_id WHERE aji.type = 'addition' ";
        $adjustments_sub        = " ( SELECT aji.product_id, SUM(aji.quantity) adjustmentQty FROM {$this->db->dbprefix('adjustment_items')} aji LEFT JOIN {$this->db->dbprefix('adjustments')} aj ON aj.id = aji.adjustment_id WHERE aji.type = 'subtraction' ";
        $ending_adjustments_add = $adjustments_add = $adjustments_add;
        $ending_adjustments_sub = $adjustments_sub = $adjustments_sub;

        if ($start_date || $warehouse) {
            $pr2 .= ' WHERE ';
            $ending_returns .= ' WHERE ';
            if ($start_date) {
                $start_date  = $this->bpas->fld($start_date);
                $end_date    = $end_date ? $this->bpas->fld($end_date) : date('Y-m-d');

                $pp                     .= " AND p.date >= '{$start_date}' AND p.date <= '{$end_date}' ";
                $ending_purchases       .= " AND p.date < '{$start_date}' ";

                $pps                    .= " AND sp.date >= '{$start_date}' AND sp.date <= '{$end_date}' ";
                $ending_pps             .= " AND sp.date < '{$start_date}' ";

                $pps_returns            .= " AND sp.date >= '{$start_date}' AND sp.date <= '{$end_date}' ";
                $ending_pps_returns     .= " AND sp.date < '{$start_date}' ";

                $sp                     .= " AND s.date >= '{$start_date}' AND s.date <= '{$end_date}' "; 
                $sp_addon  .= " AND s_addon.date >= '{$start_date}' AND s_addon.date <= '{$end_date}' ";
                $sp_combo  .= " AND s_combo.date >= '{$start_date}' AND s_combo.date <= '{$end_date}' ";

                $pos_sales  .= " AND ci.date >= '{$start_date}' AND ci.date <= '{$end_date}' ";

                $ending_sales  .= " AND s.date < '{$start_date}' "; 
                $ending_addonsales  .= " AND s_addon.date < '{$start_date}' ";
                $ending_combosales  .= " AND s_combo.date < '{$start_date}' ";
                $ending_pos_sales  .= " AND s.date < '{$start_date}' "; 

                $pr1                    .= " AND ss.date >= '{$start_date}' AND ss.date <= '{$end_date}' ";
                $pr2                    .= " sr.date >= '{$start_date}' AND sr.date <= '{$end_date}' ";
                $ending_returns         .= " sr.date < '{$start_date}' ";

                $transfers_in           .= " AND t.date >= '{$start_date}' AND t.date <= '{$end_date}' ";
                $ending_transfers_in    .= " AND t.date < '{$start_date}' ";      
                $transfers_out          .= " AND t.date >= '{$start_date}' AND t.date <= '{$end_date}' ";
                $ending_transfers_out   .= " AND t.date < '{$start_date}' ";

                $adjustments_add        .= " AND aj.date >= '{$start_date}' AND aj.date <= '{$end_date}' ";
                $ending_adjustments_add .= " AND aj.date < '{$start_date}' ";
                $adjustments_sub        .= " AND aj.date >= '{$start_date}' AND aj.date <= '{$end_date}' ";
                $ending_adjustments_sub .= " AND aj.date < '{$start_date}' ";
            }
            if ($warehouse) {
                if($start_date){
                    $pr2                .= " AND ";
                    $ending_returns     .= " AND ";
                }
                $pp                     .= " AND pi.warehouse_id = '{$warehouse}' ";
                $ending_purchases       .= " AND pi.warehouse_id = '{$warehouse}' ";

                $pps                    .= " AND spi.to_warehouse_id = '{$warehouse}' ";
                $ending_pps             .= " AND spi.to_warehouse_id = '{$warehouse}' ";

                $pps_returns            .= " AND spi.to_warehouse_id = '{$warehouse}' ";
                $ending_pps_returns     .= " AND spi.to_warehouse_id = '{$warehouse}' ";

                $sp  .= " AND si.warehouse_id = '{$warehouse}' ";
                $sp_addon  .= " AND si_addon.warehouse_id = '{$warehouse}' ";
                $sp_combo  .= " AND si_combo.warehouse_id = '{$warehouse}' ";
                $pos_sales  .= " AND s.warehouse_id = '{$warehouse}' ";

                $ending_sales  .= " AND si.warehouse_id = '{$warehouse}' ";
                $ending_addonsales  .= " AND si_addon.warehouse_id = '{$warehouse}' ";
                $ending_combosales  .= " AND si_combo.warehouse_id = '{$warehouse}' ";
                $ending_pos_sales  .= " AND s.warehouse_id = '{$warehouse}' ";

                $pr1                    .= " AND si_return.warehouse_id = '{$warehouse}' ";
                $pr2                    .= " sr.warehouse_id = '{$warehouse}' ";
                $ending_returns         .= " sr.warehouse_id = '{$warehouse}' ";
                
                $transfers_in           .= " AND t.to_warehouse_id = '{$warehouse}' ";
                $ending_transfers_in    .= " AND t.to_warehouse_id = '{$warehouse}' ";
                $transfers_out          .= " AND t.from_warehouse_id = '{$warehouse}' ";
                $ending_transfers_out   .= " AND t.from_warehouse_id = '{$warehouse}' ";

                $adjustments_add        .= " AND aji.warehouse_id = '{$warehouse}' ";
                $ending_adjustments_add .= " AND aji.warehouse_id = '{$warehouse}' ";
                $adjustments_sub        .= " AND aji.warehouse_id = '{$warehouse}' ";
                $ending_adjustments_sub .= " AND aji.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp                     .= ' GROUP BY pi.product_id ) PCosts';
        $ending_purchases       .= ' GROUP BY pi.product_id ) Ending_Purchases';
        $pps                    .= ' GROUP BY spi.product_id ) Purchases_Store';
        $ending_pps             .= ' GROUP BY spi.product_id ) Ending_PPS';
        $pps_returns            .= ' GROUP BY spi.product_id ) Purchases_Store_Return';
        $ending_pps_returns     .= ' GROUP BY spi.product_id ) Ending_PPS_Return';
        $sp                     .= ' GROUP BY si.product_id ) PSales';
        $pos_sales              .= ' GROUP BY ci.product_id ) POSSales';
        $sp_addon               .= ' GROUP BY si_addon.product_id ) PaddonSales';
        $sp_combo               .= ' GROUP BY si_combo.product_id ) PcomboSales';
        $ending_sales           .= ' GROUP BY si.product_id ) Ending_Sales';
        $ending_addonsales      .= ' GROUP BY si_addon.product_id ) Ending_AddonSales';
        $ending_combosales      .= ' GROUP BY si_combo.product_id ) Ending_ComboSales';
        $ending_pos_sales       .= ' GROUP BY ci.product_id ) Ending_POSSales';
        $pr1                    .= ' GROUP BY si_return.product_id ) PSReturn';
        $pr2                    .= ' GROUP BY sri.product_id ) PReturn';
        $ending_returns         .= ' GROUP BY sri.product_id ) Ending_PReturn';
        $transfers_in           .= ' GROUP BY pi.product_id ) Transfers_IN';
        $ending_transfers_in    .= ' GROUP BY pi.product_id ) Ending_Transfers_IN';
        $transfers_out          .= ' GROUP BY pi.product_id ) Transfers_OUT';
        $ending_transfers_out   .= ' GROUP BY pi.product_id ) Ending_Transfers_OUT';
        $adjustments_add        .= ' GROUP BY aji.product_id ) Adjustments_ADD';
        $ending_adjustments_add .= ' GROUP BY aji.product_id ) Ending_Adjustments_ADD';
        $adjustments_sub        .= ' GROUP BY aji.product_id ) Adjustments_SUB';
        $ending_adjustments_sub .= ' GROUP BY aji.product_id ) Ending_Adjustments_SUB';

        $vrn = '( SELECT id, product_id, name, SUM(quantity) as sQty FROM ' . $this->db->dbprefix('product_variants') . ' WHERE name="New" GROUP BY product_id) NVariant';
        $vro = '( SELECT id, product_id, name, SUM(quantity) as sQty FROM ' . $this->db->dbprefix('product_variants') . ' WHERE name="Old" GROUP BY product_id) OVariant';
        $vrb = '( SELECT id, product_id, name, SUM(quantity) as sQty FROM ' . $this->db->dbprefix('product_variants') . ' WHERE name="Broken" GROUP BY product_id) BVariant';

        if ($preview) {
            $this->db
                ->select($this->db->dbprefix('products') . '.code, ' . $this->db->dbprefix('products') . ".name, 
                    COALESCE( Ending_Purchases.purchasedQty, 0 ) + COALESCE( Ending_PPS.purchasedQty, 0 ) - COALESCE( Ending_PPS_Return.purchasedQty, 0 ) - COALESCE( Ending_Sales.soldQty, 0 ) - COALESCE( Ending_AddonSales.soldQty, 0 ) - COALESCE( Ending_ComboSales.soldQty, 0 ) - COALESCE( Ending_POSSales.soldQty, 0 ) + COALESCE( Ending_PReturn.returnQty, 0 ) - COALESCE( Ending_Transfers_OUT.transferQty, 0 ) + COALESCE( Ending_Transfers_IN.transferQty, 0 ) + COALESCE( Ending_Adjustments_ADD.adjustmentQty, 0 ) - COALESCE( Ending_Adjustments_SUB.adjustmentQty, 0 ) as BeginningQty,
                    COALESCE( PCosts.purchasedQty, 0 ) + COALESCE( Purchases_Store.purchasedQty, 0 ) - COALESCE( Purchases_Store_Return.purchasedQty, 0 ) as PurchasedQty,
                    COALESCE( Transfers_IN.transferQty, 0 ) as TransferQtyIN,
                    COALESCE( Transfers_OUT.transferQty, 0 ) as TransferQtyOUT,
                    COALESCE( Adjustments_ADD.adjustmentQty, 0 ) as AdjustmentQtyADD,
                    COALESCE( Adjustments_SUB.adjustmentQty, 0 ) as AdjustmentQtySUB,
                    (COALESCE( PSales.soldQty, 0 ) + COALESCE( PaddonSales.soldQty, 0 ) + COALESCE( PcomboSales.soldQty, 0 ) + COALESCE( POSSales.soldQty, 0 )) as SoldQty,
                    COALESCE( PReturn.returnQty, 0 ) + Abs(COALESCE( PSReturn.soldQty_return, 0 )) as returnQty,
                    COALESCE( PSales.totalItemDiscount, 0 ) as discount,
                    (   
                        COALESCE( Ending_Purchases.purchasedQty, 0 ) + 
                        COALESCE( Ending_PPS.purchasedQty, 0 ) - 
                        COALESCE( Ending_PPS_Return.purchasedQty, 0 ) - 
                        COALESCE( Ending_Sales.soldQty, 0 ) - 
                        COALESCE( Ending_AddonSales.soldQty, 0 ) - 
                        COALESCE( Ending_ComboSales.soldQty, 0 ) - 
                        COALESCE( Ending_POSSales.soldQty, 0 ) + 
                        COALESCE( Ending_PReturn.returnQty, 0 ) - 
                        COALESCE( Ending_Transfers_OUT.transferQty, 0 ) + 
                        COALESCE( Ending_Transfers_IN.transferQty, 0 ) + 
                        COALESCE( Ending_Adjustments_ADD.adjustmentQty, 0 ) - 
                        COALESCE( Ending_Adjustments_SUB.adjustmentQty, 0 ) +
                        COALESCE( PCosts.purchasedQty, 0 ) + 
                        COALESCE( Purchases_Store.purchasedQty, 0 ) - 
                        COALESCE( Purchases_Store_Return.purchasedQty, 0 ) + 
                        COALESCE( Transfers_IN.transferQty, 0 ) -
                        COALESCE( Transfers_OUT.transferQty, 0 ) +
                        COALESCE( Adjustments_ADD.adjustmentQty, 0 ) -
                        COALESCE( Adjustments_SUB.adjustmentQty, 0 ) -
                        COALESCE( PSales.soldQty, 0 ) -
                        COALESCE( PaddonSales.soldQty, 0 ) -
                        COALESCE( PcomboSales.soldQty, 0 ) -
                        COALESCE( POSSales.soldQty, 0 ) +
                        COALESCE( PReturn.returnQty, 0 ) + 
                        Abs(COALESCE( PSReturn.soldQty_return, 0 ))
                    ) as BalacneQty,
                    COALESCE( PSales.totalWeight, 0 ) as weight,
                    COALESCE(NVariant.sQty, 0) as qtyNewVar,
                    COALESCE(OVariant.sQty, 0) as qtyOldVar,
                    COALESCE(BVariant.sQty, 0) as qtyBrokenVar,
                    {$this->db->dbprefix('products')}.id as id", false)
                ->from('products')
                ->join($pp,                     'products.id = PCosts.product_id',                 'left')
                ->join($ending_purchases,       'products.id = Ending_Purchases.product_id',       'left')
                ->join($pps,                    'products.id = Purchases_Store.product_id',        'left')
                ->join($ending_pps,             'products.id = Ending_PPS.product_id',             'left')
                ->join($pps_returns,            'products.id = Purchases_Store_Return.product_id', 'left')
                ->join($ending_pps_returns,     'products.id = Ending_PPS_Return.product_id',      'left')
                ->join($sp,                     'products.id = PSales.product_id', 'left')
                ->join($sp_addon,               'products.id = PaddonSales.product_id', 'left')
                ->join($sp_combo,               'products.id = PcomboSales.product_id', 'left')
                ->join($pos_sales,              'products.id = POSSales.product_id', 'left')
                ->join($ending_sales,           'products.id = Ending_Sales.product_id', 'left')
                ->join($ending_addonsales,      'products.id = Ending_AddonSales.product_id', 'left')
                ->join($ending_combosales,      'products.id = Ending_ComboSales.product_id', 'left')
                ->join($ending_pos_sales,       'products.id = Ending_POSSales.product_id', 'left')
                ->join($transfers_in,           'products.id = Transfers_IN.product_id',           'left')
                ->join($ending_transfers_in,    'products.id = Ending_Transfers_IN.product_id',    'left')
                ->join($transfers_out,          'products.id = Transfers_OUT.product_id',          'left')
                ->join($ending_transfers_out,   'products.id = Ending_Transfers_OUT.product_id',   'left')
                ->join($adjustments_add,        'products.id = Adjustments_ADD.product_id',        'left')
                ->join($ending_adjustments_add, 'products.id = Ending_Adjustments_ADD.product_id', 'left')
                ->join($adjustments_sub,        'products.id = Adjustments_SUB.product_id',        'left')
                ->join($ending_adjustments_sub, 'products.id = Ending_Adjustments_SUB.product_id', 'left')
                ->join($pr1,                    'products.id = PSReturn.product_id',               'left')
                ->join($pr2,                    'products.id = PReturn.product_id',                'left')
                ->join($ending_returns,         'products.id = Ending_PReturn.product_id',         'left')
                ->join($vrn,                    'products.id = NVariant.product_id',               'left')
                ->join($vro,                    'products.id = OVariant.product_id',               'left')
                ->join($vrb,                    'products.id = BVariant.product_id',               'left')
                ->group_by('products.code')
                ->order_by('products.code');

            if($product || $cf1 || $cf2 || $cf3 || $cf4 || $cf5 || $cf6 || $category || $subcategory || $brand){
                if ($product) {
                    $this->db->where($this->db->dbprefix('products') . '.id', $product);
                }
                if ($cf1) {
                    $this->db->where($this->db->dbprefix('products') . '.cf1', $cf1);
                }
                if ($cf2) {
                    $this->db->where($this->db->dbprefix('products') . '.cf2', $cf2);
                }
                if ($cf3) {
                    $this->db->where($this->db->dbprefix('products') . '.cf3', $cf3);
                }
                if ($cf4) {
                    $this->db->where($this->db->dbprefix('products') . '.cf4', $cf4);
                }
                if ($cf5) {
                    $this->db->where($this->db->dbprefix('products') . '.cf5', $cf5);
                }
                if ($cf6) {
                    $this->db->where($this->db->dbprefix('products') . '.cf6', $cf6);
                }
                if ($category) {
                    $this->db->where($this->db->dbprefix('products') . '.category_id', $category);
                }
                if ($subcategory) {
                    $this->db->where($this->db->dbprefix('products') . '.subcategory_id', $subcategory);
                }
                if ($brand) {
                    $this->db->where($this->db->dbprefix('products') . '.brand', $brand);
                }
            } else if (empty($dataTable_filter) && empty($dataTable_filter_col_1) && empty($dataTable_filter_col_2)){
                $this->db->where('PCosts.totalPurchase !=', 0);
                $this->db->or_where('PCosts.totalPurchase !=', null);
                $this->db->or_where('Purchases_Store.purchasedQty !=', 0);
                $this->db->or_where('Purchases_Store.purchasedQty !=', null);
                $this->db->or_where('Purchases_Store_Return.purchasedQty !=', 0);
                $this->db->or_where('Purchases_Store_Return.purchasedQty !=', null);
                $this->db->or_where('PaddonSales.totalSale !=', 0);
                $this->db->or_where('PcomboSales.totalSale !=', 0); 
                $this->db->or_where('PaddonSales.totalSale !=', null);
                $this->db->or_where('PcomboSales.totalSale !=', null);
                $this->db->or_where('PSales.totalSale !=', 0);
                $this->db->or_where('PSales.totalSale !=', null);
                $this->db->or_where('PReturn.returnTotalSale !=', 0);
                $this->db->or_where('PReturn.returnTotalSale !=', null);
                $this->db->or_where('PSReturn.totalSale_return !=', 0);
                $this->db->or_where('PSReturn.totalSale_return !=', null);
                $this->db->or_where('Transfers_IN.totalTransfer !=', 0);
                $this->db->or_where('Transfers_IN.totalTransfer !=', null);
                $this->db->or_where('Transfers_OUT.totalTransfer !=', 0);
                $this->db->or_where('Transfers_OUT.totalTransfer !=', null);
                $this->db->or_where('Adjustments_ADD.adjustmentQty !=', 0);
                $this->db->or_where('Adjustments_ADD.adjustmentQty !=', null);
                $this->db->or_where('Adjustments_SUB.adjustmentQty !=', 0);
                $this->db->or_where('Adjustments_SUB.adjustmentQty !=', null);
                // $this->db->or_where('(COALESCE( Ending_Purchases.purchasedQty, 0 ) - COALESCE( Ending_Sales.soldQty, 0 ) + COALESCE( Ending_PReturn.returnQty, 0 ) - COALESCE( Ending_Transfers_OUT.transferQty, 0 ) + COALESCE( Ending_Transfers_IN.transferQty, 0 ) + COALESCE( Ending_Adjustments_ADD.adjustmentQty, 0 ) - COALESCE( Ending_Adjustments_SUB.adjustmentQty, 0 )) !=', 0);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                $data = $q->result();
                $this->data['start_date'] = $start_date;
                $this->data['end_date'] = $end_date;
                $this->data['rows'] = $data;
                $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('products_report')]];
                $meta = ['page_title' => lang('products_report'), 'bc' => $bc];
                $this->page_construct('reports/products_report_preview', $meta, $this->data);
            }
        } elseif ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('products') . '.code, ' . $this->db->dbprefix('products') . '.name,
                    ' . $this->db->dbprefix('products') . '.serial_no,
                    ' . $this->db->dbprefix('products') . '.category_id,
                    COALESCE( Ending_Purchases.purchasedQty, 0 ) + COALESCE( Ending_PPS.purchasedQty, 0 ) - COALESCE( Ending_PPS_Return.purchasedQty, 0 ) - COALESCE( Ending_Sales.soldQty, 0 ) - COALESCE( Ending_AddonSales.soldQty, 0 ) - COALESCE( Ending_ComboSales.soldQty, 0 ) + COALESCE( Ending_PReturn.returnQty, 0 ) - COALESCE( Ending_Transfers_OUT.transferQty, 0 ) + COALESCE( Ending_Transfers_IN.transferQty, 0 ) + COALESCE( Ending_Adjustments_ADD.adjustmentQty, 0 ) - COALESCE( Ending_Adjustments_SUB.adjustmentQty, 0 ) as BeginningQty,
                    COALESCE( PCosts.purchasedQty, 0 ) + COALESCE( Purchases_Store.purchasedQty, 0 ) - COALESCE( Purchases_Store_Return.purchasedQty, 0 ) as PurchasedQty,
                    CONCAT(COALESCE( Transfers_IN.transferQty, 0 ), " (IN), ", COALESCE( Transfers_OUT.transferQty, 0 ), " (OUT)") as TransferQty,
                    CONCAT(COALESCE( Adjustments_ADD.adjustmentQty, 0 ), " (ADD), ", COALESCE( Adjustments_SUB.adjustmentQty, 0 ), " (SUB)") as AdjustmentQty,
                    (COALESCE( PSales.soldQty, 0 ) + COALESCE( PaddonSales.soldQty, 0 ) + COALESCE( PcomboSales.soldQty, 0 ) + COALESCE( POSSales.soldQty, 0 )) as SoldQty,
                    COALESCE( PReturn.returnQty, 0 ) + Abs(COALESCE( PSReturn.soldQty_return, 0 )) as returnQty,
                    (   
                        COALESCE( Ending_Purchases.purchasedQty, 0 ) + 
                        COALESCE( Ending_PPS.purchasedQty, 0 ) - 
                        COALESCE( Ending_PPS_Return.purchasedQty, 0 ) - 
                        COALESCE( Ending_Sales.soldQty, 0 ) - 
                        COALESCE( Ending_AddonSales.soldQty, 0 ) - 
                        COALESCE( Ending_ComboSales.soldQty, 0 ) - 
                        COALESCE( Ending_POSSales.soldQty, 0 ) + 
                        COALESCE( Ending_PReturn.returnQty, 0 ) - 
                        COALESCE( Ending_Transfers_OUT.transferQty, 0 ) + 
                        COALESCE( Ending_Transfers_IN.transferQty, 0 ) + 
                        COALESCE( Ending_Adjustments_ADD.adjustmentQty, 0 ) - 
                        COALESCE( Ending_Adjustments_SUB.adjustmentQty, 0 ) +
                        COALESCE( PCosts.purchasedQty, 0 ) + 
                        COALESCE( Purchases_Store.purchasedQty, 0 ) - 
                        COALESCE( Purchases_Store_Return.purchasedQty, 0 ) + 
                        COALESCE( Transfers_IN.transferQty, 0 ) -
                        COALESCE( Transfers_OUT.transferQty, 0 ) +
                        COALESCE( Adjustments_ADD.adjustmentQty, 0 ) -
                        COALESCE( Adjustments_SUB.adjustmentQty, 0 ) -
                        COALESCE( PSales.soldQty, 0 ) -
                        COALESCE( PaddonSales.soldQty, 0 ) -
                        COALESCE( PcomboSales.soldQty, 0 ) -
                        COALESCE( POSSales.soldQty, 0 ) +
                        COALESCE( PReturn.returnQty, 0 ) + 
                        Abs(COALESCE( PSReturn.soldQty_return, 0 ))
                    ) as BalacneQty,
                    COALESCE( PCosts.totalPurchase, 0 ) as TotalPurchase,
                    COALESCE( PSales.totalSale, 0 ) as TotalSales,
                    (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit,
                    COALESCE(NVariant.sQty, 0) as qtyNewVar,
                    COALESCE(OVariant.sQty, 0) as qtyOldVar,
                    COALESCE(BVariant.sQty, 0) as qtyBrokenVar,', false)
                ->from('products')
                ->join($pp,                     'products.id = PCosts.product_id',                 'left')
                ->join($ending_purchases,       'products.id = Ending_Purchases.product_id',       'left')
                ->join($pps,                    'products.id = Purchases_Store.product_id',        'left')
                ->join($ending_pps,             'products.id = Ending_PPS.product_id',             'left')
                ->join($pps_returns,            'products.id = Purchases_Store_Return.product_id', 'left')
                ->join($ending_pps_returns,     'products.id = Ending_PPS_Return.product_id',      'left')
                ->join($sp,                     'products.id = PSales.product_id',                 'left')
                ->join($sp_addon, 'products.id = PaddonSales.product_id', 'left')
                ->join($sp_combo, 'products.id = PcomboSales.product_id', 'left')
                ->join($ending_sales, 'products.id = Ending_Sales.product_id', 'left')
                ->join($ending_addonsales, 'products.id = Ending_AddonSales.product_id', 'left')
                ->join($ending_combosales, 'products.id = Ending_ComboSales.product_id', 'left')
                ->join($transfers_in,           'products.id = Transfers_IN.product_id',           'left')
                ->join($ending_transfers_in,    'products.id = Ending_Transfers_IN.product_id',    'left')
                ->join($transfers_out,          'products.id = Transfers_OUT.product_id',          'left')
                ->join($ending_transfers_out,   'products.id = Ending_Transfers_OUT.product_id',   'left')
                ->join($adjustments_add,        'products.id = Adjustments_ADD.product_id',        'left')
                ->join($ending_adjustments_add, 'products.id = Ending_Adjustments_ADD.product_id', 'left')
                ->join($adjustments_sub,        'products.id = Adjustments_SUB.product_id',        'left')
                ->join($ending_adjustments_sub, 'products.id = Ending_Adjustments_SUB.product_id', 'left')
                ->join($pr1,                    'products.id = PSReturn.product_id',               'left')
                ->join($pr2,                    'products.id = PReturn.product_id',                'left')
                ->join($ending_returns,         'products.id = Ending_PReturn.product_id',         'left')
                ->join($vrn,                    'products.id = NVariant.product_id',               'left')
                ->join($vro,                    'products.id = OVariant.product_id',               'left')
                ->join($vrb,                    'products.id = BVariant.product_id',               'left')
                ->group_by('products.code')
                ->order_by('products.code');
            
            if($product || $cf1 || $cf2 || $cf3 || $cf4 || $cf5 || $cf6 || $category || $subcategory || $brand){
                if ($product) {
                    $this->db->where($this->db->dbprefix('products') . '.id', $product);
                }
                if ($cf1) {
                    $this->db->where($this->db->dbprefix('products') . '.cf1', $cf1);
                }
                if ($cf2) {
                    $this->db->where($this->db->dbprefix('products') . '.cf2', $cf2);
                }
                if ($cf3) {
                    $this->db->where($this->db->dbprefix('products') . '.cf3', $cf3);
                }
                if ($cf4) {
                    $this->db->where($this->db->dbprefix('products') . '.cf4', $cf4);
                }
                if ($cf5) {
                    $this->db->where($this->db->dbprefix('products') . '.cf5', $cf5);
                }
                if ($cf6) {
                    $this->db->where($this->db->dbprefix('products') . '.cf6', $cf6);
                }
                if ($category) {
                    $this->db->where($this->db->dbprefix('products') . '.category_id', $category);
                }
                if ($subcategory) {
                    $this->db->where($this->db->dbprefix('products') . '.subcategory_id', $subcategory);
                }
                if ($brand) {
                    $this->db->where($this->db->dbprefix('products') . '.brand', $brand);
                }
            } else if (empty($dataTable_filter) && empty($dataTable_filter_col_1) && empty($dataTable_filter_col_2)){
                $this->db->where('PCosts.totalPurchase !=', 0);
                $this->db->or_where('PCosts.totalPurchase !=', null);
                $this->db->or_where('Purchases_Store.purchasedQty !=', 0);
                $this->db->or_where('Purchases_Store.purchasedQty !=', null);
                $this->db->or_where('Purchases_Store_Return.purchasedQty !=', 0);
                $this->db->or_where('Purchases_Store_Return.purchasedQty !=', null);
                $this->db->or_where('PaddonSales.totalSale !=', 0);
                $this->db->or_where('PcomboSales.totalSale !=', 0); 
                $this->db->or_where('PaddonSales.totalSale !=', null);
                $this->db->or_where('PcomboSales.totalSale !=', null);
                $this->db->or_where('PSales.totalSale !=', 0);
                $this->db->or_where('PSales.totalSale !=', null);
                $this->db->or_where('PReturn.returnTotalSale !=', 0);
                $this->db->or_where('PReturn.returnTotalSale !=', null);
                $this->db->or_where('PSReturn.totalSale_return !=', 0);
                $this->db->or_where('PSReturn.totalSale_return !=', null);
                $this->db->or_where('Transfers_IN.totalTransfer !=', 0);
                $this->db->or_where('Transfers_IN.totalTransfer !=', null);
                $this->db->or_where('Transfers_OUT.totalTransfer !=', 0);
                $this->db->or_where('Transfers_OUT.totalTransfer !=', null);
                $this->db->or_where('Adjustments_ADD.adjustmentQty !=', 0);
                $this->db->or_where('Adjustments_ADD.adjustmentQty !=', null);
                $this->db->or_where('Adjustments_SUB.adjustmentQty !=', 0);
                $this->db->or_where('Adjustments_SUB.adjustmentQty !=', null);
                // $this->db->or_where('(COALESCE( Ending_Purchases.purchasedQty, 0 ) - COALESCE( Ending_Sales.soldQty, 0 ) + COALESCE( Ending_PReturn.returnQty, 0 ) - COALESCE( Ending_Transfers_OUT.transferQty, 0 ) + COALESCE( Ending_Transfers_IN.transferQty, 0 ) + COALESCE( Ending_Adjustments_ADD.adjustmentQty, 0 ) - COALESCE( Ending_Adjustments_SUB.adjustmentQty, 0 )) !=', 0);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('products_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('serial'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('category'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('beginning'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('transfer'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('adjustment'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('return'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('L1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('M1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('N1', lang('profit_loss'));
                $this->excel->getActiveSheet()->SetCellValue('O1', lang('new'));
                $this->excel->getActiveSheet()->SetCellValue('P1', lang('old'));
                $this->excel->getActiveSheet()->SetCellValue('Q1', lang('broken'));

                $row  = 2;
                $bpQty = 0;
                $sQty = 0;
                $pQty = 0;
                $tQty = 0;
                $aQty = 0;
                $rQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $bQty = 0;
                $bAmt = 0;
                $pl   = 0;
                $pw   = 0;
                $nVarQty = 0;
                $oVarQty = 0;
                $bVarQty = 0;
                foreach ($data as $data_row) {
                    $get_categories= $this->site->getCategoryByID($data_row->category_id);
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->serial_no);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $get_categories->name);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->BeginningQty);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->TransferQty);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->AdjustmentQty);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->returnQty);
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->BalacneQty);
                    $this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->Profit);
                    $this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->qtyNewVar);
                    $this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->qtyOldVar);
                    $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $data_row->qtyBrokenVar);

                    $bpQty += $data_row->BeginningQty;
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $rQty += $data_row->returnQty;
                    $bQty += $data_row->BalacneQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $pl   += $data_row->Profit;
                    $nVarQty += $data_row->qtyNewVar;
                    $oVarQty += $data_row->qtyOldVar;
                    $bVarQty += $data_row->qtyBrokenVar;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle('E' . $row . ':R' . $row)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $bpQty);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, $rQty);
                $this->excel->getActiveSheet()->SetCellValue('K' . $row, $bQty);
                $this->excel->getActiveSheet()->SetCellValue('L' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('M' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('N' . $row, $pl);
                $this->excel->getActiveSheet()->SetCellValue('O' . $row, $nVarQty);
                $this->excel->getActiveSheet()->SetCellValue('P' . $row, $oVarQty);
                $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $bVarQty);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                $filename = 'products_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select($this->db2->dbprefix('products') . '.code, ' . $this->db2->dbprefix('products') . ".name,
                    (COALESCE( Ending_Purchases.purchasedQty, 0 ) + COALESCE( Ending_PPS.purchasedQty, 0 ) - COALESCE( Ending_PPS_Return.purchasedQty, 0 ) - COALESCE( Ending_Sales.soldQty, 0 ) - COALESCE( Ending_AddonSales.soldQty, 0 ) - COALESCE( Ending_ComboSales.soldQty, 0 )  + COALESCE( Ending_PReturn.returnQty, 0 ) - COALESCE( Ending_Transfers_OUT.transferQty, 0 ) + COALESCE( Ending_Transfers_IN.transferQty, 0 ) + COALESCE( Ending_Adjustments_ADD.adjustmentQty, 0 ) - COALESCE( Ending_Adjustments_SUB.adjustmentQty, 0 )) as beginning,
                    CONCAT((COALESCE( PCosts.purchasedQty, 0 ) + COALESCE( Purchases_Store.purchasedQty, 0 ) - COALESCE( Purchases_Store_Return.purchasedQty, 0 )), '__', COALESCE( PCosts.totalPurchase, 0 )) as purchased,
                    CONCAT(COALESCE( Transfers_IN.transferQty, 0 ), '__', COALESCE( Transfers_OUT.transferQty, 0 )) as transfer,
                    CONCAT(COALESCE( Adjustments_ADD.adjustmentQty, 0 ), '__', COALESCE( Adjustments_SUB.adjustmentQty, 0 )) as adjustment,
                    CONCAT(COALESCE( PSales.soldQty, 0 ) + COALESCE( PaddonSales.soldQty, 0 ) + COALESCE( PcomboSales.soldQty, 0 ) + COALESCE( POSSales.soldQty, 0 ), '__', COALESCE( PSales.totalSale, 0) + COALESCE( PaddonSales.totalSale, 0 ) + COALESCE( PcomboSales.totalSale, 0 )+ COALESCE( POSSales.order_discount, 0 ) , '__', COALESCE( PSales.order_discount, 0 ) + COALESCE( PaddonSales.order_discount, 0 ) + COALESCE( PcomboSales.order_discount, 0 ) + COALESCE( POSSales.order_discount, 0 )) as sold, 
                    COALESCE( PReturn.returnQty, 0 ) + Abs(COALESCE( PSReturn.soldQty_return, 0 )) as returnQty,
                    (   
                        COALESCE( Ending_Purchases.purchasedQty, 0 ) +
                        COALESCE( Ending_PPS.purchasedQty, 0 ) - 
                        COALESCE( Ending_PPS_Return.purchasedQty, 0 ) - 
                        COALESCE( Ending_Sales.soldQty, 0 ) - 
                        COALESCE( Ending_AddonSales.soldQty, 0 ) - 
                        COALESCE( Ending_ComboSales.soldQty, 0 ) + 
                        COALESCE( Ending_PReturn.returnQty, 0 ) - 
                        COALESCE( Ending_Transfers_OUT.transferQty, 0 ) + 
                        COALESCE( Ending_Transfers_IN.transferQty, 0 ) + 
                        COALESCE( Ending_Adjustments_ADD.adjustmentQty, 0 ) - 
                        COALESCE( Ending_Adjustments_SUB.adjustmentQty, 0 ) +
                        COALESCE( PCosts.purchasedQty, 0 ) +
                        COALESCE( Purchases_Store.purchasedQty, 0 ) - 
                        COALESCE( Purchases_Store_Return.purchasedQty, 0 ) + 
                        COALESCE( Transfers_IN.transferQty, 0 ) -
                        COALESCE( Transfers_OUT.transferQty, 0 ) +
                        COALESCE( Adjustments_ADD.adjustmentQty, 0 ) -
                        COALESCE( Adjustments_SUB.adjustmentQty, 0 ) -
                        COALESCE( PSales.soldQty, 0 ) -
                        COALESCE( PaddonSales.soldQty, 0 ) -
                        COALESCE( PcomboSales.soldQty, 0 ) -
                        COALESCE( POSSales.soldQty, 0 ) +
                        COALESCE( PReturn.returnQty, 0 ) + 
                        Abs(COALESCE( PSReturn.soldQty_return, 0 ))
                    ) as balance,
                    CONCAT(COALESCE(NVariant.sQty, 0), '__',COALESCE(NVariant.name, '' ), '__', COALESCE(OVariant.sQty, 0), '__', COALESCE(OVariant.name, ''), '__', COALESCE(BVariant.sQty, 0), '__', COALESCE(BVariant.name, '')) as variant, {$this->db2->dbprefix('products')}.id as id", false)
                ->from('products')
                ->join($pp,                     'products.id = PCosts.product_id',                 'left')
                ->join($ending_purchases,       'products.id = Ending_Purchases.product_id',       'left')
                ->join($pps,                    'products.id = Purchases_Store.product_id',        'left')
                ->join($ending_pps,             'products.id = Ending_PPS.product_id',             'left')
                ->join($pps_returns,            'products.id = Purchases_Store_Return.product_id', 'left')
                ->join($ending_pps_returns,     'products.id = Ending_PPS_Return.product_id',      'left')
                ->join($sp,                     'products.id = PSales.product_id',                 'left')
                ->join($sp_addon,               'products.id = PaddonSales.product_id', 'left')
                ->join($sp_combo,               'products.id = PcomboSales.product_id', 'left')
                ->join($pos_sales,              'products.id = POSSales.product_id', 'left')
                ->join($ending_sales,           'products.id = Ending_Sales.product_id', 'left')
                ->join($ending_addonsales,      'products.id = Ending_AddonSales.product_id', 'left')
                ->join($ending_combosales,      'products.id = Ending_ComboSales.product_id', 'left')
                ->join($ending_pos_sales,       'products.id = Ending_POSSales.product_id', 'left')
                ->join($transfers_in,           'products.id = Transfers_IN.product_id',           'left')
                ->join($ending_transfers_in,    'products.id = Ending_Transfers_IN.product_id',    'left')
                ->join($transfers_out,          'products.id = Transfers_OUT.product_id',          'left')
                ->join($ending_transfers_out,   'products.id = Ending_Transfers_OUT.product_id',   'left')
                ->join($adjustments_add,        'products.id = Adjustments_ADD.product_id',        'left')
                ->join($ending_adjustments_add, 'products.id = Ending_Adjustments_ADD.product_id', 'left')
                ->join($adjustments_sub,        'products.id = Adjustments_SUB.product_id',        'left')
                ->join($ending_adjustments_sub, 'products.id = Ending_Adjustments_SUB.product_id', 'left')
                ->join($pr1,                    'products.id = PSReturn.product_id',               'left')
                ->join($pr2,                    'products.id = PReturn.product_id',                'left')
                ->join($ending_returns,         'products.id = Ending_PReturn.product_id',         'left')
                ->join($vrn,                    'products.id = NVariant.product_id',               'left')
                ->join($vro,                    'products.id = OVariant.product_id',               'left')
                ->join($vrb,                    'products.id = BVariant.product_id',               'left')
                ->group_by('products.code')
                ->order_by('products.code');

            if($product || $cf1 || $cf2 || $cf3 || $cf4 || $cf5 || $cf6 || $category || $subcategory || $brand){
                if ($product) {
                    $this->datatables->where($this->db2->dbprefix('products') . '.id', $product);
                }
                if ($cf1) {
                    $this->datatables->where($this->db2->dbprefix('products') . '.cf1', $cf1);
                }
                if ($cf2) {
                    $this->datatables->where($this->db2->dbprefix('products') . '.cf2', $cf2);
                }
                if ($cf3) {
                    $this->datatables->where($this->db2->dbprefix('products') . '.cf3', $cf3);
                }
                if ($cf4) {
                    $this->datatables->where($this->db2->dbprefix('products') . '.cf4', $cf4);
                }
                if ($cf5) {
                    $this->datatables->where($this->db2->dbprefix('products') . '.cf5', $cf5);
                }
                if ($cf6) {
                    $this->datatables->where($this->db2->dbprefix('products') . '.cf6', $cf6);
                }
                if ($category) {
                    $this->datatables->where($this->db2->dbprefix('products') . '.category_id', $category);
                }
                if ($subcategory) {
                    $this->datatables->where($this->db2->dbprefix('products') . '.subcategory_id', $subcategory);
                }
                if ($brand) {
                    $this->datatables->where($this->db2->dbprefix('products') . '.brand', $brand);
                }
            } else if (empty($dataTable_filter) && empty($dataTable_filter_col_1) && empty($dataTable_filter_col_2)){
                $this->datatables->where('PCosts.totalPurchase !=', 0);
                $this->datatables->or_where('PCosts.totalPurchase !=', null);
                $this->datatables->or_where('Purchases_Store.purchasedQty !=', 0);
                $this->datatables->or_where('Purchases_Store.purchasedQty !=', null);
                $this->datatables->or_where('Purchases_Store_Return.purchasedQty !=', 0);
                $this->datatables->or_where('Purchases_Store_Return.purchasedQty !=', null);
                $this->datatables->or_where('PaddonSales.totalSale !=', 0);
                $this->datatables->or_where('PcomboSales.totalSale !=', 0); 
                $this->datatables->or_where('POSSales.soldQty !=', 0); 
                $this->datatables->or_where('PaddonSales.totalSale !=', null);
                $this->datatables->or_where('PcomboSales.totalSale !=', null);
                $this->datatables->or_where('POSSales.soldQty !=', null);
                $this->datatables->or_where('PSales.totalSale !=', 0);
                $this->datatables->or_where('PSales.totalSale !=', null);
                $this->datatables->or_where('PReturn.returnTotalSale !=', 0);
                $this->datatables->or_where('PReturn.returnTotalSale !=', null);
                $this->datatables->or_where('PSReturn.totalSale_return !=', 0);
                $this->datatables->or_where('PSReturn.totalSale_return !=', null);
                $this->datatables->or_where('Transfers_IN.totalTransfer !=', 0);
                $this->datatables->or_where('Transfers_IN.totalTransfer !=', null);
                $this->datatables->or_where('Transfers_OUT.totalTransfer !=', 0);
                $this->datatables->or_where('Transfers_OUT.totalTransfer !=', null);
                $this->datatables->or_where('Adjustments_ADD.adjustmentQty !=', 0);
                $this->datatables->or_where('Adjustments_ADD.adjustmentQty !=', null);
                $this->datatables->or_where('Adjustments_SUB.adjustmentQty !=', 0);
                $this->datatables->or_where('Adjustments_SUB.adjustmentQty !=', null);
                // $this->datatables->or_where('(COALESCE( Ending_Purchases.purchasedQty, 0 ) - COALESCE( Ending_Sales.soldQty, 0 ) + COALESCE( Ending_PReturn.returnQty, 0 ) - COALESCE( Ending_Transfers_OUT.transferQty, 0 ) + COALESCE( Ending_Transfers_IN.transferQty, 0 ) + COALESCE( Ending_Adjustments_ADD.adjustmentQty, 0 ) - COALESCE( Ending_Adjustments_SUB.adjustmentQty, 0 )) !=', 0);
            }

            echo $this->datatables->generate();
        }
    }
}
?>