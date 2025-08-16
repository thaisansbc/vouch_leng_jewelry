<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->admin_model('cron_model');
        $this->load->admin_model('sales_model');
        $this->Settings = $this->cron_model->getSettings();
        $url_endpoint       = 'https://edssapiuat.chipmongretail.com:8000/';
        $this->url_api      = $url_endpoint.'api/v1/Authentication/login';
        $this->insert_url   = $url_endpoint.'api/v1/sale/insertsalemegamall';
        $this->user         = 'TestUser';
        $this->pass         = '123';
        $this->mallName         = 'CM271MegaMall';
        $this->tenantName         = 'CM271Skechers';
    }

    public function index()
    {
        show_404();
    }

    public function run()
    {
        if ($m = $this->cron_model->run_cron()) {
            if ($this->input->is_cli_request()) {
                foreach ($m as $msg) {
                    echo $msg . "\n";
                }
            } else {
                echo '<!doctype html><html><head><title>Cron Job</title><style>p{background:#F5F5F5;border:1px solid #EEE; padding:15px;}</style></head><body>';
                echo '<p>Corn job successfully run.</p>';
                foreach ($m as $msg) {
                    echo '<p>' . $msg . '</p>';
                }
                echo '</body></html>';
            }
        }
    }
    public function alert_to_telegram()
    {
        $token = $this->Settings->token_telegram;
        if(!empty($token)){
            $link = 'https://api.telegram.org:443/bot'.$token;
            $getupdate = file_get_contents($link.'/getupdates');
            $responsearray = json_decode($getupdate, TRUE);
            $chat_id = $responsearray['result'][0]['message']['chat']['id'];
            if($messages = $this->cron_model->run_cron_telegram_alert()){
                $count = count($messages);
                for($i=0; $i<$count; $i++){
                    foreach($messages[$i] as $msg){
                        $parameter = array('chat_id' => $chat_id, 'text' => $msg);
                        $this->bpas->send_Telegram($link, $parameter);
                    }
                }
            }

            $this->session->set_flashdata('message', lang('telegram_has_been_alert.'));
        }

        admin_redirect('system_settings');
    }

    public function syncProducts() 
    {
        $products = $this->site->getAllProducts();
        foreach ($products as $product) {
            $this->site->syncQuantity_13_05_21($product->id);
        }
    }

    public function chipmong_sales_daily($biller = null, $pos = null, $start = null, $end = null)
    {
        $biller_id   = ($biller ? $biller : $this->Settings->default_biller);
        $start_date  = ($start ? $this->bpas->fld($start . ' 00:00:01') : (date('Y-m-d') . ' 00:00:01'));
        $end_date    = ($end ? $this->bpas->fld($end . ' 23:59:59') : (date('Y-m-d') . ' 23:59:59'));
        
        $data =array(
            'userId' => $this->user,
            'pwd'    => $this->pass
        );
        $user_login   = json_encode($data);
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $this->url_api);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_POST, 1);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $user_login);
        $headers   = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Accept: application/json";
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
        $buffer    = curl_exec($curl_handle);
        curl_close($curl_handle);
        $object    = json_decode($buffer);
        $token     = $object->token;
        if(isset($token)){
            if ($this->insertChipmongSales($token, $biller_id, $start_date, $end_date, $pos)) {
                admin_redirect('chipmong');
                $this->session->set_flashdata('message', 'Successful push data to chipmong.'); 
            }
            $this->session->set_flashdata('error', 'Push data to chipmong fail!');
            admin_redirect('chipmong');
            $this->bpas->send_json(false);
        }
        $this->session->set_flashdata('error', 'Push data to chipmong fail!');
        admin_redirect('chipmong');
        $this->bpas->send_json(false);
    }

    public function insertChipmongSales($access_token, $biller_id, $start_date, $end_date, $pos = null)
    {    
        $data = null;
        if ($access_token) {
            if ($sales = $this->sales_model->getChipmongDailySales($biller_id, $start_date, $end_date, $pos)) {
                // $this->bpas->print_arrays($sales);
                $data = array(
                    "mallName"          => $this->mallName,
                    "tenantName"        => $this->tenantName,
                    "date"              => date("Y-m-d", strtotime($sales->date)),
                    "grossSale"         => $this->bpas->formatDecimal($sales->gross_sale),
                    "taxAmount"         => $this->bpas->formatDecimal($sales->tax_amount),
                    "netSale"           => $this->bpas->formatDecimal($sales->net_sale),
                    "cashAmountUsd"     => $this->bpas->formatDecimal($sales->net_cash_sales),
                    "cashAmountRiel"    => $this->bpas->formatDecimal(0),
                    "creditCardAmount"  => $this->bpas->formatDecimal($sales->creditcard_amount),
                    "otherAmount"       => $this->bpas->formatDecimal($sales->other_amount),
                    "totalCreditCardTransaction" => $sales->creditcard_transaction,
                    "totalTransaction"  => $sales->total_transaction,
                    "depositAmountUsd"  => $this->bpas->formatDecimal(0),
                    "depositAmountRiel" => $this->bpas->formatDecimal(0),
                    "exchangeRate"      => $this->bpas->formatDecimal($sales->currency_rate_kh),
                    "posId"             => "Pos12"
                );
                $json_data   = json_encode($data);
                $curl_handle = curl_init(); 
                curl_setopt($curl_handle, CURLOPT_URL, $this->insert_url);
                curl_setopt($curl_handle, CURLOPT_POST, 1);
                curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1); 
                curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE); 
                curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $json_data);
                $headers   = array();
                $headers[] = "Content-Type: application/json";
                $headers[] = "Accept: application/json";
                $headers[] = 'Authorization:Bearer '.$access_token ;
                curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
                $buffer    = curl_exec($curl_handle);
                $httpcode  = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
                curl_close($curl_handle);
                if($httpcode != 200){ 
                    if (curl_errno($curl_handle)) {  
                        $error_msg = curl_error($curl_handle);  
                    } else { 
                        $error_msg = $buffer; 
                    } 
                    throw new Exception('Error '.$httpcode.': '.$error_msg); 
                } else { 
                    $data['biller_id'] = $sales->biller_id;
                    $data['sale_id']   = $sales->sale_ids;
                    $data['push']      = 1;
                    if ($this->db->insert('chipmong', $data)) {
                        $sale_ids = explode(', ', $sales->sale_ids);
                        $this->sales_model->updateChipmongSalesStatus($sale_ids);
                        return true;
                    }
                    return false;
                }
                return false;
            }
            return false;
        }
        return false;
    }

    // public function chipmong_daily($run = null)
    // {
    //     $biller_id   = $this->Settings->default_biller;
    //     $start_date  = date('Y-m-d').' 00:00:01';
    //     $end_date    = date('Y-m-d').' 23:59:59';
    //     $curl_handle = curl_init();
    //     curl_setopt($curl_handle, CURLOPT_URL, $this->url_api);
    //     curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($curl_handle, CURLOPT_POST, 1);
    //     curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
    //     curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "
    //     {
    //         'userId': '".$this->user."',
    //         'pwd'   : '".$this->pass."' 
    //     }");
    //     $headers = array();
    //     $headers[] = "Content-Type: application/json";
    //     $headers[] = "Accept: application/json";
    //     curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
    //     $buffer = curl_exec($curl_handle);
    //     curl_close($curl_handle);
    //     $object = json_decode($buffer);
    //     $token  = $object->token;
    //     if(isset($token)){
    //         $this->insertData($token, $biller_id, $start_date, $end_date);
    //     }
    //     $data = array(
    //         'date'  => date('Y-m-d H:i:s'),
    //         'title' => 'woking',
    //         'type'  => '5min'
    //     );
    //     $result = $this->db->insert('crobjob', $data);
    //     if($result){
    //         $this->session->set_flashdata('message', lang("successfully"));
    //         redirect($_SERVER["HTTP_REFERER"]);
    //     }
    // }

    // public function insertData($access_token, $biller_id, $start_date, $end_date, $pos = null)
    // {    
    //     $data = null;
    //     if ($access_token) {
    //         $sales = $this->sales_model->getDailySaleItems($biller_id,$start_date,$end_date,$pos);
    //         $data  = '{
    //             "mallName": "'.$this->mallName.'",
    //             "tenantName": "'.$this->tenantName.'",
    //             "date": "' .  date("Y-m-d", strtotime($sales->date)) . '",
    //             "grossSale": ' . $this->bpas->formatDecimal($sales->gross_sale) . ',
    //             "taxAmount": ' . $this->bpas->formatDecimal($sales->tax_amount) . ',
    //             "netSale": ' . $this->bpas->formatDecimal($sales->net_sale) . ',
    //             "cashAmountUsd": ' . $this->bpas->formatDecimal($sales->net_cash_sales) . ',
    //             "cashAmountRiel": ' . $this->bpas->formatDecimal(0) . ',
    //             "creditCardAmount": ' . $this->bpas->formatDecimal($sales->creditcard_amount) . ',
    //             "otherAmount": ' . $this->bpas->formatDecimal($sales->other_amount) . ',
    //             "totalCreditCardTransaction": ' . $sales->creditcard_transaction . ',
    //             "totalTransaction": ' . $sales->total_transaction . ',
    //             "depositAmountUsd": ' . $this->bpas->formatDecimal(0) . ',
    //             "depositAmountRiel": ' . $this->bpas->formatDecimal(0) . ',
    //             "exchangeRate": ' . $this->bpas->formatDecimal($sales->exchange_rate) . ',
    //             "posId": "Pos01"
    //         }'; 
    //     }
    //     $curl_handle = curl_init(); 
    //     curl_setopt($curl_handle, CURLOPT_URL, $this->insert_url);
    //     curl_setopt($curl_handle, CURLOPT_POST, 1);
    //     curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1); 
    //     curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE); 
    //     curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
    //     $headers   = array();
    //     $headers[] = "Content-Type: application/json";
    //     $headers[] = "Accept: application/json";
    //     $headers[] = 'Authorization:Bearer '.$access_token ;
    //     curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
    //     $buffer    = curl_exec($curl_handle);
    //     $httpcode  = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
    //     curl_close($curl_handle);
    //     if($httpcode != 200){ 
    //         if (curl_errno($curl_handle)) {  
    //             $error_msg = curl_error($curl_handle);  
    //         } else { 
    //             $error_msg = $buffer; 
    //         } 
    //         throw new Exception('Error '.$httpcode.': '.$error_msg); 
    //     } else { 
    //         $data =array(
    //             "mallName"          => $this->mallName,
    //             "tenantName"        => $this->tenantName,
    //             "date"              => $sales->date,
    //             "biller_id"         => $biller_id,
    //             "grossSale"         => $this->bpas->formatDecimal($sales->gross_sale),
    //             "taxAmount"         => $this->bpas->formatDecimal($sales->tax_amount),
    //             "netSale"           => $this->bpas->formatDecimal($sales->net_sale),
    //             "cashAmountUsd"     => $this->bpas->formatDecimal($sales->net_cash_sales),
    //             "cashAmountRiel"    => $this->bpas->formatDecimal(0),
    //             "creditCardAmount"  => $this->bpas->formatDecimal($sales->creditcard_amount),
    //             "otherAmount"       => $this->bpas->formatDecimal($sales->other_amount),
    //             "totalCreditCardTransaction"    => $sales->creditcard_transaction,
    //             "totalTransaction"  => $sales->total_transaction,
    //             "depositAmountUsd"  => $this->bpas->formatDecimal(0),
    //             "depositAmountRiel" => $this->bpas->formatDecimal(0),
    //             "exchangeRate"      => $this->bpas->formatDecimal($sales->exchange_rate),
    //             "posId"             => "Pos01",
    //             'sale_id'=>'3,7,9'
    //         );
    //         $insert_sale = $this->db->insert('chipmong', $data);
    //         if ($insert_sale) {
    //             $this->session->set_flashdata('message', 'Successful push data to chipmong.');
    //         }
    //         $this->session->set_flashdata('error', 'Push data to chipmong fail!');
    //         $this->bpas->send_json(false);
    //     }
    //     return false;
    // }
}