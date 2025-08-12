<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Expense extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->methods['index_get']['limit'] = 500;
        $this->load->api_model('purchases_api');
    }

    protected function setExpense($expense)
    {
        unset($expense->attachment, $expense->hash, $expense->updated_at);
        if (isset($expense->items) && !empty($expense->items)) {
            foreach ($expense->items as &$item) {
                if (isset($item->option_id) && !empty($item->option_id)) {
                    if ($variant = $this->purchases_api->getProductVariantByID($item->option_id)) {
                        $item->product_variant_id   = $variant->id;
                        $item->product_variant_name = $variant->name;
                    }
                }
                $item->product_unit_quantity = $item->unit_quantity;
                unset($item->id, $item->quote_id, $item->warehouse_id, $item->real_unit_price, $item->quote_item_id, $item->option_id, $item->unit_quantity);
                $item = (array) $item;
                ksort($item);
            }
        }
        $expense = (array) $expense;
        ksort($expense);
        return $expense;
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
            'suppplier_id' => $this->get('suppplier_id') ? $this->get('suppplier_id') : null,
            'suppplier'    => $this->get('suppplier') ? $this->get('suppplier') : null,
        ];

        if ($reference === null) {
            if ($expenses = $this->purchases_api->getExpenses($filters)) {
                $sl_data = [];
                foreach ($expenses as $expense) {
                    if (!empty($filters['include'])) {
                        foreach ($filters['include'] as $include) {
                            if ($include == 'items') {
                                $expense->items = $this->purchases_api->getExpenseItems($expense->id);
                            }
                            if ($include == 'biller') {
                                $expense->biller = $this->quotes_api->getBillerByID($expense->biller_id);
                            }
                        }
                    }

                    $expense->created_by = $this->purchases_api->getUser($expense->created_by);
                    $sl_data[]         = $this->setExpense($expense);
                }

                $data = [
                    'data'  => $sl_data,
                    'limit' => (int) $filters['limit'],
                    'start' => (int) $filters['start'],
                    'total' => $this->purchases_api->countExpenses($filters),
                ];
                $this->response($data, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No expense record found.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            if ($expense = $this->purchases_api->getExpense($filters)) {
                if (!empty($filters['include'])) {
                    foreach ($filters['include'] as $include) {
                        if ($include == 'items') {
                            $expense->items = $this->purchases_api->getExpenseItems($expense->id);
                        }
                        if ($include == 'biller') {
                            $expense->biller = $this->quotes_api->getBillerByID($expense->biller_id);
                        }
                    }
                }

                $expense->created_by = $this->purchases_api->getUser($expense->created_by);
                $expense             = $this->setExpense($expense);
                $this->set_response($expense, REST_Controller::HTTP_OK);
            } else {
                $this->set_response([
                    'message' => 'Expense could not be found for reference ' . $reference . '.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
}
