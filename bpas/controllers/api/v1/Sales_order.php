<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Sales_order extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->methods['index_get']['limit'] = 500;
        $this->load->api_model('sales_order_api');
        $this->load->admin_model('sales_order_model');
        $this->load->helper(array('form', 'url'));
    }
    public function index_get()
    {
        $reference = $this->get('reference');

        $filters = [
            'reference'   => $reference,
            'include'     => $this->get('include') ? explode(',', $this->get('include')) : null,
            'start'       => $this->get('start') && is_numeric($this->get('start')) ? $this->get('start') : 1,
            'limit'       => $this->get('limit') && is_numeric($this->get('limit')) ? $this->get('limit') : 10,
            'start_date'  => $this->get('start_date') && is_numeric($this->get('start_date')) ? $this->get('start_date') : null,
            'end_date'    => $this->get('end_date') && is_numeric($this->get('end_date')) ? $this->get('end_date') : null,
            'order_by'    => $this->get('order_by') ? explode(',', $this->get('order_by')) : ['id', 'decs'],
            'customer_id' => $this->get('customer_id') ? $this->get('customer_id') : null,
            'created_by'  => $this->get('created_by') ? $this->get('created_by') : null,
            'customer'    => $this->get('customer') ? $this->get('customer') : null,
        ];

        if ($reference === null) {
            if ($sales = $this->sales_order_api->getSalesOrder($filters)) {
                $sl_data = [];
                foreach ($sales as $sale) {
                    if (!empty($filters['include'])) {
                        foreach ($filters['include'] as $include) {
                            if ($include == 'items') {
                                $sale->items = $this->sales_order_api->getSaleOrderItems($sale->id);
                            }
                            if ($include == 'warehouse') {
                                $sale->warehouse = $this->sales_order_api->getWarehouseByID($sale->warehouse_id);
                            }
                        }
                    }

                    $sale->biller     = $this->sales_order_api->getBillersByID($sale->biller_id);
                 //   $sale->paid_by    = $this->sales_order_api->getPaymentBySaleID($sale->id);
                    $sale->created_by = $this->sales_order_api->getUser($sale->created_by);
                    $sl_data[]        = $this->setSale($sale);
                }

                $data = [
                    'data'  => $sl_data,
                    'limit' => (int) $filters['limit'],
                    'start' => (int) $filters['start'],
                    'total' => $this->sales_order_api->countSales($filters),
                ];
                $this->response($data, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No sale record found.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            if ($sale = $this->sales_order_api->getSalesOrder($filters)) {
                if (!empty($filters['include'])) {
                    foreach ($filters['include'] as $include) {
                        if ($include == 'items') {
                            $sale->items = $this->sales_order_api->getSaleOrderItems($sale->id);
                        }
                        if ($include == 'warehouse') {
                            $sale->warehouse = $this->sales_order_api->getWarehouseByID($sale->warehouse_id);
                        }
                    }
                }

                $sale->created_by = $this->sales_order_api->getUser($sale->created_by);
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
    protected function setSale($sale)
    {
        unset($sale->address_id, $sale->api, $sale->attachment, $sale->hash, $sale->pos, $sale->reserve_id, $sale->return_id, $sale->return_sale_ref, $sale->return_sale_total, $sale->sale_id, $sale->shop, $sale->staff_note, $sale->surcharge, $sale->updated_at, $sale->suspend_note);
        if (isset($sale->items) && !empty($sale->items)) {
            foreach ($sale->items as &$item) {
                if (isset($item->option_id) && !empty($item->option_id)) {
                    if ($variant = $this->sales_order_api->getProductVariantByID($item->option_id)) {
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
    public function getSale($filters)
    {
        if (!empty($sales = $this->getSales($filters))) {
            return array_values($sales)[0];
        }
        return false;
    }
    public function add_saleorder_post()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $jdata = json_decode(file_get_contents('php://input'),true);//json_decode($this->input->raw_input_stream, true);
            if(json_last_error() === JSON_ERROR_NONE) {
                $date           = $jdata['date'];
                $reference      = $jdata['reference_no'] ? $jdata['reference_no']: $this->site->getReference('sr');
                $customer_id    = $jdata['customer'];
                $biller_id      = $this->Settings->default_biller;
                $warehouse_id   = $this->Settings->default_warehouse;
                $payment_status = $jdata['payment_status'];
                $sale_status    = $jdata['sale_status'];
                if(!(is_array($jdata['customer']))){
                    $customer_details = $this->site->getCompanyByID($customer_id);
                    $customer       = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
                }
                $biller_details = $this->site->getCompanyByID($biller_id);
                $biller         = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
                $note           = $this->bpas->clear_tags($jdata['note']);
                $staff_note     = $this->bpas->clear_tags($jdata['staff_note']);
                $total          = 0;
                $product_tax      = 0;
                $product_discount = 0;

                $sale_order = $this->sales_order_model->getSaleOrderByReference_No($reference);
                if($sale_order != NULL){
                    $this->response([
                        'status' => false,
                        'message' => 'The Sale Refernece no field must contain a unique value.',
                        'data' => $sale_order
                    ], REST_Controller::HTTP_BAD_REQUEST);
                    return false;
                }

                if (is_array($jdata['customer'])) {
                        $_customer = $jdata['customer'];
                        $customer_id    = isset($_customer['customer_id']) ? $_customer['customer_id'] : null ;
                        $customer_code  = isset($_customer['customer_code']) ? $_customer['customer_code'] : null ;
                        $phone          = isset($_customer['phone']) ? $_customer['phone'] : null ;
                        $name           = isset($_customer['name']) ? $_customer['name'] : null ;
                        $address        = isset($_customer['address']) ? $_customer['address'] : null ;
                        $note           = isset($_customer['note']) ? $_customer['note'] : null ;
                        $company_phone = $this->sales_order_model->getCompanyByPhone($phone);
                        $company_code = $this->sales_order_model->getCompanyByCode($customer_code);
                        if($company_phone != false){
                            $customer_data =[];
                            $customer_id    = $company_phone->id;
                            $customer       = !empty($company_phone->company) && $company_phone->company != '-' ? $company_phone->company : $company_phone->name;
                        // }elseif ($company_code != false) {
                        //     $customer_data =[];
                        //     $customer_id    = $company_code->id;
                        //     $customer       = !empty($company_code->company) && $company_code->company != '-' ? $company_code->company : $company_code->name;
                        }else {
                            $customer_data = [
                                'code'                  => $customer_code,
                                'phone'                 => $phone,
                                'name'                  => $name,
                                'address'               => $address,
                                'invoice_footer'        => $note,
                                'group_id'              => "4",
                                'group_name'            => "customer", 
                                'customer_group_id'     => "1",
                                'customer_group_name'   => "General",
                                'price_group_id'        => "1",
                                'price_group_name'      => "General",
                                'company'               => "-"
                            ];
                        }
                }else {
                    $customer_data =[];
                    $customer_id    = $jdata['customer'];
                }


                foreach($jdata['sale_items'] as $item) {
                    $product_details = $item['product_code'] ? $this->site->getProductByCode($item['product_code']) : null;
                    if($product_details){
                        $item_id        = $product_details->id;
                        $item_code      = $item['product_code'];
                        $item_name      = $product_details->name;
                        $item_type      = $product_details->type;
                        $real_unit_price    = $this->bpas->formatDecimal($item['price']);
                        $unit_price         = $this->bpas->formatDecimal($item['price']);
                        $item_unit_quantity = $item['quantity'];
                        $item_serial        = '';
                        $item_max_serial    = '';
                        $item_tax_rate      = null;
                        $item_discount      = null;
                        $item_unit          = $product_details->unit;
                        $item_quantity      = $item['quantity'];
                        $item_detail        = '';
                        $pr_item_tax        = $item_tax = 0;
                        $tax                = '';
                        $pr_discount        = $this->site->calculateDiscount($item_discount, $unit_price);
                        $unit_price         = $this->bpas->formatDecimal($unit_price - $pr_discount);
                        $item_net_price     = $unit_price;
                        $pr_item_discount   = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                        $product_discount   += $pr_item_discount;
                        if (isset($item_tax_rate) && $item_tax_rate != 0) {
                            $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                            $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                            $item_tax    = $ctax['amount'];
                            $tax         = $ctax['tax'];
                            if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                                $item_net_price = $unit_price - $item_tax;
                            }
                            $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        }
                        $unit         = $this->site->getUnitByID($item_unit);
                        $product_tax += $pr_item_tax;
                        $subtotal     = (($item_net_price * $item_unit_quantity) + $pr_item_tax);

                        $product = [
                            'product_id'        => $item_id,
                            'product_code'      => $item_code,
                            'product_name'      => $item_name,
                            'product_type'      => $item_type,
                            'net_unit_price'    => $item_net_price,
                            'unit_price'        => $this->bpas->formatDecimal($item_net_price + $item_tax),
                            'quantity'          => $item_quantity,
                            'product_unit_id'   => $unit ? $unit->id : null,
                            'product_unit_code' => $unit ? $unit->code : null,
                            'unit_quantity'     => $item_unit_quantity,
                            'warehouse_id'      => $warehouse_id,
                            'item_tax'          => $pr_item_tax,
                            'tax_rate_id'       => $item_tax_rate,
                            'tax'               => $tax,
                            'discount'          => $item_discount,
                            'item_discount'     => $pr_item_discount,
                            'subtotal'          => $this->bpas->formatDecimal($subtotal),
                            'real_unit_price'   => $real_unit_price,
                            'comment'           => $item_detail,
                        ];
                        $products[]=$product;
                        $total += $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                    }else {
                        $this->response([
                            'status' => false,
                            'message' => 'No matching result found! Product might be out of stock in the selected warehouse.',
                            'data' => $item['product_code']
                        ], REST_Controller::HTTP_BAD_REQUEST);
                        return false;
                    }
                }
                krsort($products);
                // Validate the post data
                if(!empty($customer) || !empty($customer_data)){
                    // Insert user data
                    $sale_status    = $jdata['sale_status'] ? $jdata['sale_status']:'order';
                    $order_discount = $jdata['order_discount'] ? $jdata['order_discount']:0;
                    $shipping       = $jdata['shipping'] ? $jdata['shipping']:0;
                    $order_discount = $this->site->calculateDiscount($order_discount, ($total + $product_tax));
                    $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
                    $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
                    $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
                    $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
                    $data = [
                        'date'                => $date,
                        'reference_no'        => $reference,
                        'customer_id'         => !empty($customer) ? $customer_id : "",
                        'customer'            => !empty($customer) ? $customer : "",
                        'biller_id'           => $biller_id,
                        'biller'              => $biller,
                        'warehouse_id'        => $warehouse_id,
                        'note'                => $note,
                        'staff_note'          => $staff_note,
                        'total'               => $total,
                        'grand_total'         => $grand_total,
                        'sale_status'         => $sale_status,
                        'order_status'        => 'pending',
                        'paid'                => 0,
                        'created_by'          => $this->session->userdata('user_id'),
                        'hash'                => hash('sha256', microtime() . mt_rand()),
                    ];
                    $payment = [];
                    $insert = $this->sales_order_model->addSale($data, $products, $payment, null, $customer_data);
                    if($insert){
                        $this->response([
                            'status' => TRUE,
                            'message' => 'Sale Order has been added successfully.',
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
