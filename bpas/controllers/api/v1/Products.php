<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Products extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->methods['index_get']['limit'] = 500;
        $this->load->api_model('products_api');
    }

    protected function setProduct($product)
    {
        $product->tax_rate       = $this->products_api->getTaxRateByID($product->tax_rate);
        $product->unit           = $this->products_api->getProductUnit($product->unit);
        $ctax                    = $this->site->calculateTax($product, $product->tax_rate);
        $product->price          = $this->bpas->formatDecimal($product->price);
        $product->net_price      = $this->bpas->formatDecimal($product->tax_method ? $product->price : $product->price - $ctax['amount']);
        $product->unit_price     = $this->bpas->formatDecimal($product->tax_method ? $product->price + $ctax['amount'] : $product->price);
        $product->tax_method     = $product->tax_method ? 'exclusive' : 'inclusive';
        // $product->tax_rate->type = isset($product->tax_rate->type) ? 'percentage' : 'fixed';
        $product                 = (array) $product;
        ksort($product);
        return $product;
    }

    public function index_get()
    {
        $code = $this->get('code');

        $filters = [
            'code'     => $code,
            'include'  => $this->get('include') ? explode(',', $this->get('include')) : null,
            'start'    => $this->get('start') && is_numeric($this->get('start')) ? $this->get('start') : 1,
            'limit'    => $this->get('limit') && is_numeric($this->get('limit')) ? $this->get('limit') : 50,
            'order_by' => $this->get('order_by') ? explode(',', $this->get('order_by')) : ['code', 'acs'],
            'brand'    => $this->get('brand_code') ? $this->get('brand_code') : null,
            'category' => $this->get('category_code') ? $this->get('category_code') : null,
        ];

        if ($code === null) {
            if ($products = $this->products_api->getProducts($filters)) {
                $pr_data = [];
                foreach ($products as $product) {
                    if (!empty($filters['include'])) {
                        foreach ($filters['include'] as $include) {
                            if ($include == 'brand') {
                                $product->brand = $this->products_api->getBrandByID($product->brand);
                            } elseif ($include == 'category') {
                                $product->category = $this->products_api->getCategoryByID($product->category);
                            } elseif ($include == 'photos') {
                                $product->photos = $this->products_api->getProductPhotos($product->id);
                            } elseif ($include == 'sub_units') {
                                $product->sub_units = $this->products_api->getSubUnits($product->unit);
                            }

                        }
                    }
                    $product->multi_unit  = $this->site->getUnitByProId($product->id);
                    $product->addon_items = $this->products_api->getProductAddOnItems($product->id);
                    $product->options     = $this->site->getAllProductOption($product->id);
                    $pr_data[] = $this->setProduct($product);
                }

                $data = [
                    'data'  => $pr_data,
                    'limit' => $filters['limit'],
                    'start' => $filters['start'],
                    'total' => $this->products_api->countProducts($filters),
                ];
                $this->response($data, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No product were found.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            if ($product = $this->products_api->getProduct($filters)) {
                if (!empty($filters['include'])) {
                    foreach ($filters['include'] as $include) {
                        if ($include == 'brand') {
                            $product->brand = $this->products_api->getBrandByID($product->brand);
                        } elseif ($include == 'category') {
                            $product->category = $this->products_api->getCategoryByID($product->category);
                        } elseif ($include == 'photos') {
                            $product->photos = $this->products_api->getProductPhotos($product->id);
                        } elseif ($include == 'sub_units') {
                            $product->sub_units = $this->products_api->getSubUnits($product->unit);
                        }
                    }
                }

                $product = $this->setProduct($product);
                $this->set_response($product, REST_Controller::HTTP_OK);
            } else {
                $this->set_response([
                    'message' => 'Product could not be found for code ' . $code . '.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
    public function list_countstock_get()
    {
        if ($this->input->server('REQUEST_METHOD') == 'GET') {
            if ($getsetting = $this->products_api->getAllStockCount()) {
                $this->response($getsetting, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No expense record found.',
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
    public function addCountStock_post()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $jdata = json_decode(file_get_contents('php://input'),true);
           
            if(json_last_error() === JSON_ERROR_NONE) {

                $warehouse_id = $this->post('warehouse_id');
                $type         = $this->post('type');
                $categories   = $this->post('categories') ? $this->post('categories') : null;
                $brands       = $this->post('brand') ? $this->post('brand') : null;

                $this->load->helper('string');
                $name     = random_string('md5') . '.csv';
                // var_dump($categories);
                // exit();
                $products = $this->products_api->getStockMovement_StockCountProducts($warehouse_id, $type, $categories, $brands);
                $pr       = 0;
                if ($products != false) { 
                    foreach ($products as $product) {
                        if ($variants = $this->products_api->getStockCountProductVariants($warehouse_id, $product->id)) {
                            foreach ($variants as $variant) {
                                $items[] = [
                                    'product_id'   => $product->id,
                                    'product_code' => $product->code,
                                    'product_name' => $product->name,
                                    'variant'      => $variant->name,
                                    'expected'     => $variant->quantity,
                                    'counted'      => '',
                                    'cost'         => $product->cost,
                                    'expiry'       => $product->expiry,
                                ];
                                $csvs[] = [
                                    'product_code' => $product->code,
                                    'product_name' => $product->name,
                                    'variant'      => $variant->name,
                                    'expected'     => $variant->quantity,
                                    'counted'      => '',
                                    'expiry'       => $product->expiry,
                                ];
                                $rw++;
                            }
                        } else {
                            $items[] = [
                                'product_id'    => $product->id,
                                'product_code'  => $product->code,
                                'product_name'  => $product->name,
                                'variant'       => '',
                                'expected'      => $product->quantity,
                                'counted'       => '',
                                'cost'          => $product->cost,
                                'expiry'        => $product->expiry,
                            ];
                            $csvs[] = [
                                'product_code' => $product->code,
                                'product_name' => $product->name,
                                'variant'      => '',
                                'expected'     => $product->quantity,
                                'counted'      => '',
                                'expiry'       => $product->expiry,
                            ];
                            $rw++;
                        }
                        $pr++;
                    }
                }else{
                    $this->response([
                        'status' => false,
                        'message' => 'no_product_found',
                        'data' => []
                    ], REST_Controller::HTTP_NOT_FOUND);
                }
                if (!empty($csvs)) {
                    $csv_file = fopen('./files/' . $name, 'w');
                    fprintf($csv_file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                    fputcsv($csv_file, [lang('product_code'), lang('product_name'), lang('variant'), lang('expected'), lang('counted'), lang('expiry')]);
                    foreach ($csvs as $csv) {
                        // unset($csv['product_id']);
                        // unset($csv['cost']);
                        fputcsv($csv_file, $csv);
                    }
                    // file_put_contents('./files/'.$name, $csv_file);
                    // fwrite($csv_file, $txt);
                    fclose($csv_file);
                } else {
                    $this->response([
                        'status' => false,
                        'message' => 'no_csv_found',
                        'data' => []
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
                if ($this->Owner || $this->Admin) {
                    $date = $this->bpas->fld($this->input->post('date'));
                } else {
                    $date = date('Y-m-d H:i:s');
                }
                $category_ids   = '';
                $brand_ids      = '';
                $category_names = '';
                $brand_names    = '';
                if ($categories) {
                    $r = 1;
                    $s = sizeof($categories);
                    foreach ($categories as $category_id) {
                        $category = $this->site->getCategoryByID($category_id);
                        if ($r == $s) {
                            $category_names .= $category->name;
                            $category_ids   .= $category->id;
                        } else {
                            $category_names .= $category->name . ', ';
                            $category_ids   .= $category->id . ', ';
                        }
                        $r++;
                    }
                }
                if ($brands) {
                    $r = 1;
                    $s = sizeof($brands);
                    foreach ($brands as $brand_id) {
                        $brand = $this->site->getBrandByID($brand_id);
                        if ($r == $s) {
                            $brand_names .= $brand->name;
                            $brand_ids   .= $brand->id;
                        } else {
                            $brand_names .= $brand->name . ', ';
                            $brand_ids   .= $brand->id . ', ';
                        }
                        $r++;
                    }
                }
                $data = [
                    'date'           => $date,
                    'warehouse_id'   => $warehouse_id,
                    'reference_no'   => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('stc'),
                    'type'           => $type,
                    'categories'     => $category_ids,
                    'category_names' => $category_names,
                    'brands'         => $brand_ids,
                    'brand_names'    => $brand_names,
                    'initial_file'   => $name,
                    'products'       => $pr,
                    'rows'           => $rw,
                    'created_by'     => 1,
                ];
                // var_dump($data);
                // exit();
                $insert = $this->products_api->addStockCount($data, $items);
                if($insert){
                    $this->response([
                        'status' => TRUE,
                        'message' => 'Stock count has been added successfully.',
                        'data' => $insert
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response("Some problems occurred, please try again.", REST_Controller::HTTP_BAD_REQUEST);
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
    public function UpdateScan_post()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $jdata = json_decode(file_get_contents('php://input'),true);
           
            if(json_last_error() === JSON_ERROR_NONE) {

              
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
