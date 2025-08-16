<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Pos extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->methods['index_get']['limit'] = 500;
        $this->load->api_model('pos_api');
        $this->load->api_model('sales_api');
    }

    protected function setSale($sale)
    {
        unset($sale->address_id, $sale->api, $sale->attachment, $sale->hash, $sale->pos, $sale->reserve_id, $sale->return_id, $sale->return_sale_ref, $sale->return_sale_total, $sale->sale_id, $sale->shop, $sale->staff_note, $sale->surcharge, $sale->updated_at, $sale->suspend_note);
        if (isset($sale->items) && !empty($sale->items)) {
            foreach ($sale->items as &$item) {
                if (isset($item->option_id) && !empty($item->option_id)) {
                    if ($variant = $this->pos_api->getProductVariantByID($item->option_id)) {
                        $item->product_variant_id   = $variant->id;
                        $item->product_variant_name = $variant->name;
                    }
                }
                $item->product_unit_quantity = $item->unit_quantity;
                unset($item->id, $item->sale_id, $item->warehouse_id, $item->real_unit_price, $item->sale_item_id, $item->option_id, $item->unit_quantity);
                $item = (array) $item;
                ksort($item);
            }
        }
        $sale = (array) $sale;
        ksort($sale);
        return $sale;
    }
    public function get_order_items_get()
    {
        if ($this->input->server('REQUEST_METHOD') == 'GET') {
            if ($sale = $this->pos_api->getPrintOrderItems()) {
                $this->set_response(($sale), REST_Controller::HTTP_OK);
            } else {
                $this->set_response([
                    'message' => 'Item could not be found for reference',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status' => FALSE,
                'message' =>  "Invalid Request",
                'data' => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function index_get()
    {
        $reference  = $this->get('reference');

        $filters = [
            'reference'   => $reference,
            'include'     => $this->get('include') ? explode(',', $this->get('include')) : null,
            'start'       => $this->get('start') && is_numeric($this->get('start')) ? $this->get('start') : 1,
            'limit'       => $this->get('limit') && is_numeric($this->get('limit')) ? $this->get('limit') : 10,
            'start_date'  => $this->get('start_date') && is_numeric($this->get('start_date')) ? $this->get('start_date') : null,
            'end_date'    => $this->get('end_date') && is_numeric($this->get('end_date')) ? $this->get('end_date') : null,
            'order_by'    => $this->get('order_by') ? explode(',', $this->get('order_by')) : ['id', 'decs'],
            'customer_id' => $this->get('customer_id') ? $this->get('customer_id') : null,
            'customer'    => $this->get('customer') ? $this->get('customer') : null,
        ];
        if ($reference === null) {
            if ($sales = $this->pos_api->getSales($filters)) {
                $sl_data = [];
                foreach ($sales as $sale) {
                    if (!empty($filters['include'])) {
                        foreach ($filters['include'] as $include) {
                            if ($include == 'items') {
                                $sale->items = $this->pos_api->getSaleItems($sale->id);
                                $sale->addon_items = $this->pos_api->getAllInvoiceItemsAddon($sale->id);
                            }
                            if ($include == 'biller') {
                                $sale->biller = $this->pos_api->getBillerByID($sale->biller_id);
                            }
                        }
                    }
                    $sale->biller     = $this->sales_api->getBillersByID($sale->biller_id);
                    $sale->paid_by    = $this->sales_api->getPaymentBySaleID($sale->id);
                    $sale->created_by = $this->pos_api->getUser($sale->created_by);
                    $sl_data[]        = $this->setSale($sale);
                }

                $data = [
                    'data'  => $sl_data,
                    'limit' => (int) $filters['limit'],
                    'start' => (int) $filters['start'],
                    'total' => $this->pos_api->countSales($filters),
                ];
                $this->response($data, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No sale record found.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            if ($sale = $this->pos_api->getSale($filters)) {
                if (!empty($filters['include'])) {
                    foreach ($filters['include'] as $include) {
                        if ($include == 'items') {
                            $sale->items = $this->pos_api->getSaleItems($sale->id);
                            $sale->addon_items = $this->pos_api->getAllInvoiceItemsAddon($sale->id);
                        }
                        if ($include == 'biller') {
                            $sale->biller = $this->pos_api->getBillerByID($sale->warehouse_id);
                        }
                    }
                }

                $sale->biller     = $this->sales_api->getBillersByID($sale->biller_id);
                $sale->paid_by    = $this->sales_api->getPaymentBySaleID($sale->id);
                $sale->created_by = $this->pos_api->getUser($sale->created_by);

                $sale             = $this->setSale($sale);
                $this->set_response($sale, REST_Controller::HTTP_OK);
            } else {
                $this->set_response([
                    'message' => 'Sale could not be found for reference ' . $reference . '.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
    public function view_get(){
        $sale_id    = $this->get('sale_id');
        $sale = $this->sales_api->getInvoiceByID($sale_id);

        if($sale_id && $sale){

                $sale->biller     = $this->sales_api->getBillersByID($sale->biller_id);
                $sale->items      = $this->pos_api->getSaleItems($sale->id);
                $sale->paid_by    = $this->sales_api->getPaymentBySaleID($sale->id);
                $sale->created_by = $this->pos_api->getUser($sale->created_by);

                $sale             = $this->setSale($sale);
                $this->set_response($sale, REST_Controller::HTTP_OK);
            
        }else{
            $this->set_response([
                'message' => 'Sale could not be found for id ' . $sale_id . '.',
                'status'  => false,
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
    public function addsale_post()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $jdata = json_decode(file_get_contents('php://input'),true);

            if(json_last_error() === JSON_ERROR_NONE) {
                $getexchange_khm = $this->bpas->getExchange_rate('KHR');
                $getexchange_bat = $this->bpas->getExchange_rate('THB');
                $exchange_khm    = isset($getexchange_khm->rate) ? $getexchange_khm->rate : 1; 
                $exchange_bat    = isset($getexchange_bat->rate) ? $getexchange_bat->rate : 1;

                $date = $jdata['date']?$jdata['date']: date('Y-m-d H:i:s');
                $warehouse_id         =  isset($jdata['warehouse']) ? $jdata['warehouse'] : $this->Settings->default_warehouse;
                if ($warehouse_id == NULL) { 
                    $this->response(['status' => false, 'message' => 'Warehouse result found!', 'data' => $warehouse_id ], REST_Controller::HTTP_BAD_REQUEST); return false; 
                }
                $customer_id          = $jdata['customer'];
                $biller_id            = isset($jdata['biller']) ? $jdata['biller'] : $this->Settings->default_biller;
                $total_items          = 0;
                $rounding             = 0;
                $item_option          = 0;
                
                $cost                 = 0;
                $project_id           = $jdata['project'];
                $saleman              = $jdata['saleman'];
                $sale_status          = 'completed';
                $payment_status       = 'paid';
                $payment_term         = 0;
                $due_date             = date('Y-m-d', strtotime('+' . $payment_term . ' days'));
                $shipping             = $jdata['shipping'] ? $jdata['shipping'] : 0;
                $customer_details     = $this->site->getCompanyByID($customer_id);
                $customer             = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
                $biller_details       = $this->site->getCompanyByID($biller_id);
                $biller               = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
                if ($biller_details == NULL) {
                    $this->response(['status' => false, 'message' => 'Biller result found!', 'data' => $biller_id], REST_Controller::HTTP_BAD_REQUEST); return false; 
                }

                $note                 = $this->bpas->clear_tags($jdata['note']);
                $staff_note           = $this->bpas->clear_tags($jdata['staff_note']);
                $reference            = $this->site->getReference('pos', $biller_details->code);
                $total_original_price = 0;
                $total                = 0;
                $product_tax          = 0;
                $item_price_original  = 0;
                $default_total_price  = 0;
                $product_discount     = 0;
                $digital              = FALSE;
                $stockmoves           = null;
                $total_weight         = 0;
                $gst_data             = [];
                $accTranPayments      = [];
                $text_items           = '';

                $r=0; 
                $total_addon = 0;

                foreach($jdata['sale_items'] as $item) {
                    $product_details = $this->site->getProductByID($item['product_id']);
                    if($product_details){
                        $addon_items = $item['addon_items'];
                        $sub_total_addon = 0;
                        for ($p=0; $p < sizeof($addon_items); $p++) { 
                            $product_addon_details = $this->site->getProductByID(($addon_items[$p])['product_id']);
                            $addon_items[$p] = array(
                                'product_id'        => ($addon_items[$p])['product_id'],
                                'product_code'      => $product_addon_details->code,
                                'product_name'      => $product_addon_details->name,
                                'product_type'      => $product_addon_details->type, 
                                'net_unit_price'    => ($addon_items[$p])['unit_price'],
                                'unit_price'        => $this->bpas->formatDecimal(($addon_items[$p])['unit_price']),
                                'quantity'          => ($addon_items[$p])['quantity'], 
                                'warehouse_id'      => $warehouse_id,
                                'subtotal'          =>$this->bpas->formatDecimal(($addon_items[$p])['unit_price']*($addon_items[$p])['quantity'])
                            ); 
                            $sub_total_addon += $this->bpas->formatDecimal(($addon_items[$p])['unit_price']*($addon_items[$p])['quantity']);
                            $total_addon += $this->bpas->formatDecimal(($addon_items[$p])['unit_price']*($addon_items[$p])['quantity']);

                            $extra_details = $this->site->getProductByID(($addon_items[$p])['product_id']);
                            if ($extra_details) {
                                $extraUnit = $this->site->getProductUnit($extra_details->id, $extra_details->unit);
                                $extractProductID = $extra_details->id;
                                $extractQuantity  = $_POST['addon_product_qty'][$r];
                                if ($this->Settings->accounting_method == '0') {
                                    $extraCosts = $this->site->getFifoCost($extractProductID, $extractQuantity, $stockmoves);
                                } else if ($this->Settings->accounting_method == '1') {
                                    $extraCosts = $this->site->getLifoCost($extractProductID, $extractQuantity, $stockmoves);
                                } else if ($this->Settings->accounting_method == '3') {
                                    $extraCosts = $this->site->getProductMethod($extractProductID, $extractQuantity, $stockmoves);
                                }
                                if (isset($extraCosts) && !empty($extraCosts)) {
                                    $item_cost_qty   = 0;
                                    $item_cost_total = 0;
                                    foreach ($extraCosts as $extraCost) {
                                        $item_cost_qty   += $extraCost['quantity'];
                                        $item_cost_total += $extraCost['cost'] * $extraCost['quantity'];
                                        $stockmoves[] = array(
                                            'transaction'    => 'Sale',
                                            'product_id'     => $extractProductID,
                                            'product_type'   => $extra_details->type,
                                            'product_code'   => $extra_details->code,
                                            'product_name'   => $extra_details->name,
                                            'quantity'       => $extraCost['quantity'] * (-1),
                                            'unit_quantity'  => $extraUnit->unit_qty,
                                            'unit_code'      => $extraUnit->code,
                                            'unit_id'        => $extra_details->unit,
                                            'warehouse_id'   => $warehouse_id,
                                            'date'           => $date,
                                            'real_unit_cost' => $extraCost['cost'],
                                            'reference_no'   => $reference,
                                            'user_id'        => 1,
                                        );
                                        
                                    }
                                    $extra_details->cost = $item_cost_total / $item_cost_qty;
                                } else {
                                    $stockmoves[] = array(
                                        'transaction'    => 'Sale',
                                        'product_id'     => $extractProductID,
                                        'product_type'   => $extra_details->type,
                                        'product_code'   => $extra_details->code,
                                        'product_name'   => $extra_details->name,
                                        'quantity'       => $extractQuantity * (-1),
                                        'unit_quantity'  => $extraUnit->unit_qty,
                                        'unit_code'      => $extraUnit->code,
                                        'unit_id'        => $extra_details->unit,
                                        'warehouse_id'   => $warehouse_id,
                                        'date'           => $date,
                                        'real_unit_cost' => $extra_details->cost,
                                        'reference_no'   => $reference,
                                        'user_id'        => 1,
                                    );
                                   
                                }
                           
                                $default_total_price = $this->bpas->formatDecimal($default_total_price + $addon_subtotal);
                                $total_original_price = $this->bpas->formatDecimal($total_original_price + $addon_subtotal);
                            }


                        } 
                        $item_id            = $product_details->id;
                        $item_code          = $item['product_code'];
                        $product_code       = $product_details->code;
                        $item_name          = $product_details->name;
                        $item_type          = $product_details->type;
                        $item_comment       = isset($item['product_comment']) ? $item['product_comment'] : '';
                        $item_original      = $this->bpas->formatDecimal($item['price']);
                        $real_unit_price    = $this->bpas->formatDecimal($item['price']);
                        $unit_price         = $this->bpas->formatDecimal($item['price']); 
                        $item_unit_quantity = $item['quantity'];
                        $item_serial        = '';
                        $item_max_serial    = '';
                        $item_tax_rate      = null;
                        $item_discount      = null;
                        // $item_unit          = isset($item['product_unit']) ? $item['product_unit'] : $product_details->sale_unit;
                        $item_unit          = $product_details->sale_unit;
                        $unit               = $this->site->getUnitByID($item_unit); 
                        if($unit->operation_value){
                            $item_quantity      = $item['quantity'] * $unit->operation_value; 
                        }else{
                            $item_quantity      = $item['quantity'];
                        } 
                        $item_detail        = '';
                        $pr_item_tax        = $item_tax = 0;
                        $tax                = '';
                        $unit         = $this->site->getUnitByID($item_unit);
                        if ($unit == NULL) {
                            $this->response([
                                'status' => false,
                                'message' => 'Product unit result found!',
                                'data' => $item['product_unit']
                            ], REST_Controller::HTTP_BAD_REQUEST);
                            return false;
                        }
                        $pr_discount        = $this->site->calculateDiscount($item_discount, $unit_price);
                        $unit_price         = $this->bpas->formatDecimal($unit_price - $pr_discount);
                        $item_net_price     = $unit_price;
                        $pr_item_discount   = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                        $product_discount   += $pr_item_discount;
                        if (isset($item_tax_rate) && $item_tax_rate != 0) {
                            $tax_details    = $this->site->getTaxRateByID($item_tax_rate);
                            $ctax           = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                            $item_tax       = $ctax['amount'];
                            $tax            = $ctax['tax'];
                            if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                                $item_net_price = $unit_price - $item_tax;
                            }
                            $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        }
                      
                        $product_tax += $pr_item_tax;
                        $subtotal     = (($item_net_price * $item_unit_quantity) + $sub_total_addon + $pr_item_tax);
                        // var_dump($subtotal); 
                        $product = array(
                            'product_id'        => $item_id,
                            'product_code'      => $item_code,
                            'product_name'      => $item_name,
                            'product_type'      => $item_type,
                            'option_id'         => $item_option,
                            'net_unit_price'    => $item_net_price,
                            'unit_price'        => $this->bpas->formatDecimal($item_net_price + $item_tax),
                            'quantity'          => $item_quantity,
                            'product_unit_id'   => $unit ? $unit->id : NULL,
                            'product_unit_code' => $unit ? $unit->code : NULL,
                            'unit_quantity'     => $item_unit_quantity,
                            'warehouse_id'      => $warehouse_id,
                            'original_price'    => $item_price_original,
                            'item_tax'          => $pr_item_tax,
                            'tax_rate_id'       => $item_tax_rate,
                            'tax'               => $tax,
                            'discount'          => $item_discount,
                            'item_discount'     => $pr_item_discount,
                            'subtotal'          => $this->bpas->formatDecimal($subtotal),
                            'serial_no'         => $item_serial,
                            'real_unit_price'   => $real_unit_price,
                            'comment'           => $item_comment,
                            'addon_items'       => $addon_items,
                            'item_row_id'       => rand(10000000000000, 99999999999999),
                            'cost'              => $cost,
                        );

                        $products[] = $product;
                       
                        $total += $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity),4);

                    }else {
                        $this->response([
                            'status' => false,
                            'message' => 'No matching result found! Product might be out of stock in the selected warehouse.',
                            'data' => $item['product_code']
                        ], REST_Controller::HTTP_BAD_REQUEST);
                        return false;
                    }
                    $r++;
                    
                   
                }
                // var_dump($addon_items);
                // var_dump($products);
                // exit();
                krsort($products);

                // Validate the post data
                if(!empty($customer_id)){
                    // Insert user data
                    
                    $sale_status     = 'completed';
                    $order_discount  = $jdata['order_discount'] ? $jdata['order_discount'] : null;
                    $shipping        = $jdata['shipping'] ? $jdata['shipping'] : 0;
                    $order_status    = isset($jdata['order_status']) == 'pending' ? 'pending' : 'approved';
                    $order_tax       = isset($jdata['order_tax']) ? $jdata['order_tax'] : 1;
                    $membership_code = null;

                    $order_discount = $this->site->calculateDiscount($order_discount, ($total + $product_tax));
                    $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);

                    $order_tax      = $this->site->calculateOrderTax($order_tax, ($total + $product_tax - $order_discount));
                    $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
                    $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
                    $total_original_price = $this->bpas->formatDecimal($total);
                    $currency        = $this->Settings->default_currency;
                    $currency_rate   = ($currency == "usd") ? $cur_rate->rate : 1;
                    $data = array(
                        'date'                => $date,
                        'project_id'          => $project_id,
                        'reference_no'        => $reference,
                        'customer_id'         => $customer_id,
                        'customer'            => $customer,
                        'biller_id'           => $biller_id,
                        'biller'              => $biller,
                        'warehouse_id'        => $warehouse_id,
                        'note'                => $note,
                        'staff_note'          => $staff_note,
                        'total'               => $total + $total_addon,
                        'product_discount'    => $product_discount,
                        'order_discount_id'   => $jdata['order_discount'] ? $jdata['order_discount']:null,
                        'order_discount'      => $this->bpas->formatDecimal($order_discount),
                        'total_discount'      => $total_discount,
                        'product_tax'         => $product_tax,
                        'order_tax_id'        => isset($jdata['order_tax']) ? $jdata['order_tax']: null,
                        'membership_code'     => $membership_code,
                        'order_tax'           => $order_tax,
                        'total_tax'           => $total_tax,
                        'default_total_price' => $default_total_price,
                        'shipping'            => $this->bpas->formatDecimal($shipping),
                        'grand_total'         => $grand_total + $total_addon,
                        'total_items'         => $r,
                        'sale_status'         => $sale_status,
                        'payment_status'      => $payment_status,
                        'payment_term'        => $payment_term,
                        'rounding'            => $rounding,
                        'original_price'      => $total_original_price,
                        'suspend_note'        => isset($jdata['suspend_note']) ? $jdata['suspend_note'] : null,
                        'currency'            => $currency,
                        'pos'                 => 1,
                        'paid'                => isset($jdata['amount-paid']) ? $jdata['amount-paid'] : 0,
                        'created_by'          => $jdata['created_by'],//$this->session->userdata('user_id'),
                        'hash'                => hash('sha256', microtime() . mt_rand()),
                        'saleman_by'          => $saleman,
                        'currency_rate_kh'    => $exchange_khm,
                        'currency_rate_bat'   => $exchange_bat,
                    );  
                    if (!empty($jdata['payments'])) {                  
                        foreach ($jdata['payments'] as $pay) {
                            if (isset($pay['amount']) && ($pay['amount'] > 0 || $grand_total == 0) && isset($pay['paid_by']) && !empty($pay['paid_by'])) {
                                if(isset($pay['balance_amount'])){
                                    $amount = $this->bpas->formatDecimal($pay['amount']);
                                }else{
                                    $amount = $this->bpas->formatDecimal(($pay['balance_amount']) > 0 ? $pay['amount'] - $pay['balance_amount'] : $pay['amount']);
                                } 
                                    $payment[] = array(
                                        'date'         => $date,
                                        'amount'       => $amount,
                                        'paid_amount'  => implode(',', array($pay['paid_amount'], $pay['paid_amount_kh'], $pay['paid_amount_bat'])),
                                        'currency_rate'=> $pay['currency_rate'],
                                        'paid_by'      => $pay['paid_by'],
                                        'cheque_no'    => $pay['cheque_no'],
                                        'cc_no'        => $pay['cc_no'],
                                        'cc_holder'    => $pay['cc_holder'],
                                        'cc_month'     => $pay['cc_month'],
                                        'cc_year'      => $pay['cc_year'],
                                        'cc_type'      => $pay['cc_type'],
                                        'cc_cvv2'      => $pay['cc_cvv2'],
                                        'created_by'   => $pay['created_by'],
                                        'type'         => 'received',
                                        'note'         => $pay['pay_note'],
                                        'pos_paid'     => $pay['amount'],
                                        'pos_balance'  => $pay['balance_amount'],
                                        // 'currency'       => $this->input->post('kh_currenncy')
                                    );
                                
                            }
                        }
                    }else{
                        $payment = array();
                    }
                    if ($this->Settings->product_expiry == '1' && $stockmoves && $products) {
                        $checkExpiry = $this->site->checkExpiry($stockmoves, $products, 'POS');
                        // var_dump($checkExpiry['expiry_items']);
                        $stockmoves  = $checkExpiry['expiry_stockmoves'];
                        $products    = $checkExpiry['expiry_items'];
                    }
                    //krsort($payment);
                    
              

                    $insert = $this->sales_api->addSale($data, $products,null, $payment);
                    if($insert){
                        $this->response([
                            'status' => TRUE,
                            'message' => 'Sale has been added successfully.',
                            'data' => $insert
                        ], REST_Controller::HTTP_OK);
                    }else{
                        $this->response("Some problems occurred, please try again.", REST_Controller::HTTP_BAD_REQUEST);
                    }
                }else{
                    // Set the response and exit
                    $this->response("Provide complete user ".$customer." info to add.", REST_Controller::HTTP_BAD_REQUEST);
                }
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Invalid JSON data',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

        } else {
            $this->response([
                'status' => false,
                'message' => 'Invalid Request',
                'data' => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}
