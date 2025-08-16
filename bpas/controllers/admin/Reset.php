<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reset extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function demo()
    {
        if (DEMO) {
            
            $this->db->select("*")->from('truncate')->where('status',0);
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $this->db->truncate($row->name);
                }
            }

            $this->db->delete('suspended_note', ['tmp' => 1]);
            $file = file_get_contents('./files/demo.sql');
            mysqli_multi_query($this->db->conn_id, $file);

            $this->db->conn_id->close();
            admin_redirect('login');
            exit();
            $bc                          = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('add_sale')]];
            $meta                        = ['page_title' => lang('add_sale'), 'bc' => $bc];
            $this->page_construct('reset', $meta, $this->data);
        } else {

            

            echo '<!DOCTYPE html>
            <html>
                <head>
                    <title>SBC Solutions</title>
                    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
                    <style>
                        html, body { height: 100%; }
                        body { margin: 0; padding: 0; width: 100%; display: table; font-weight: 72; font-family: \'Lato\'; }
                        .container { text-align: center; display: table-cell; vertical-align: middle; }
                        .content { text-align: center; display: inline-block; }
                        .title { font-size: 72px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="content">
                            <div class="title">Demo is disabled!</div>
                        </div>
                    </div>
                </body>
            </html>
            ';

        }
    }
    function clear(){
        if (DEMO) {
            

            // $this->db->select("*")->from('truncate')->where('status',0);
            // $q = $this->db->get();
            // if ($q->num_rows() > 0) {
            //     foreach (($q->result()) as $row) {
            //         $this->db->truncate($row->name);
            //     }
            // }

            // $this->db->delete('suspended_note', ['tmp' => 1]);
            // $file = file_get_contents('./files/demo.sql');
            // mysqli_multi_query($this->db->conn_id, $file);

            // $this->db->conn_id->close();
   

            admin_redirect('login');
        }
    }
    public function data()
    {
        if (DEMO) {
            $this->db->truncate('adjustments');
            $this->db->truncate('adjustment_items');
            $this->db->truncate('calendar');
            $this->db->truncate('captcha');
            $this->db->truncate('categories');
            $this->db->truncate('combo_items');
            $this->db->truncate('costing');
            $this->db->truncate('deliveries');
            $this->db->truncate('deposits');
            $this->db->truncate('expenses');
            $this->db->truncate('gift_cards');
            $this->db->truncate('login_attempts');
            $this->db->truncate('payments');
            $this->db->truncate('paypal');
            $this->db->truncate('pos_register');
            $this->db->truncate('products');
            $this->db->truncate('product_photos');
            $this->db->truncate('product_variants');
            $this->db->truncate('promos');

            $this->db->truncate('purchases_request');
            $this->db->truncate('purchases_order');
            $this->db->truncate('purchase_request_items');
            $this->db->truncate('purchase_order_items');
            $this->db->truncate('purchases');
            $this->db->truncate('purchase_items');
            $this->db->truncate('quotes');
            $this->db->truncate('quote_items');

            $this->db->truncate('sales_order');
            $this->db->truncate('sale_order_items');
            $this->db->truncate('sales');
            $this->db->truncate('sale_items');
            $this->db->truncate('skrill');
            $this->db->truncate('suspended_bills');
            $this->db->truncate('suspended_items');
            $this->db->truncate('transfers');
            $this->db->truncate('transfer_items');
            $this->db->truncate('variants');
            $this->db->truncate('warehouses_products');
            $this->db->truncate('warehouses_products_variants');
            $this->db->truncate('expense_categories');
            $this->db->truncate('gift_card_topups');
            $this->db->truncate('addresses');
            $this->db->truncate('brands');
            $this->db->truncate('product_prices');
            $this->db->truncate('stock_counts');
            $this->db->truncate('stock_count_items');
            $this->db->truncate('printers');

            $file = file_get_contents('./files/demo.sql');
            mysqli_multi_query($this->db->conn_id, $file);
            // $this->db->conn_id->multi_query($file);
            $this->db->conn_id->close();
            // $this->load->dbutil();
            // $this->dbutil->optimize_database();

            admin_redirect('login');
        } else {
            echo '<!DOCTYPE html>
            <html>
                <head>
                    <title>SBC Solutions</title>
                    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
                    <style>
                        html, body { height: 100%; }
                        body { margin: 0; padding: 0; width: 100%; display: table; font-weight: 72; font-family: \'Lato\'; }
                        .container { text-align: center; display: table-cell; vertical-align: middle; }
                        .content { text-align: center; display: inline-block; }
                        .title { font-size: 72px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="content">
                            <div class="title">Demo is disabled!</div>
                        </div>
                    </div>
                </body>
            </html>
            ';
        }
    }
    public function index()
    {
        show_404();
    }
}
