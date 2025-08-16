<?php

defined('BASEPATH') or exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->Settings = $this->site->get_setting();
        $this->pos_settings  = $this->site->getPosSetting();
        $this->accounting_setting = $this->site->get_account_setting();
        /*$connected = @fsockopen("www.google.com", 80); 
        $is_conn = ($connected)? 1:0;
        if($is_conn){
            $protocol = isset($_SERVER["HTTPS"]) ? 'https://' : 'http://';
            $referer = $protocol.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']);
            $q = array(
                'user_name'     => $this->Settings->license_name,
                'license_key'   => $this->Settings->license_key,
                'url_addresses' => $referer
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://178.128.24.212:9876/SBC_licenses/api/v1/license/verify");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($q)); 
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $headers   = array();
            $headers[] = "Content-Type: application/json";
            $headers[] = "Accept: application/json";
            $headers[] = 'api-key:4sowwscowwgs8cs800cswkkw44kw0wk84wsssooo';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if ($result !== 'false' && $result) {
                $data = json_decode($result);
                if(!$data && $data ==false){
                    echo '<center><h2>You are using copy system from SBC Solutions</h2></center>';
                    echo '<center><p>
                                    Please connect to system owner to countinue using <br>
                                    Website: http://sbcsolution.biz/  <br>
                                    Phone  : 016 78 78 75 /095 78 78 65 
                            </p></center>';
                    exit();
                }else{
                    
                    $expired_date = date("Y-m-d", strtotime($data->expired_date));
                    $difference = strtotime($expired_date)-strtotime(date("Y-m-d"));
                    $difference_day = floor($difference/(24*60*60));

                    $message = '';
                    if(($difference_day >=1) && ($difference_day <= 7)){
                        $message = 7;
                    }
                    if($difference_day == 0){
                        $message = 'today';
                    }
                    if($difference_day < 0){
                        $message = 'expired';
                    }
                    if($data->environment == 'uat' && $difference_day > 0){
                        $message = 'uat';
                    }
                    if($message){
                    ?>
                    <style type="text/css">
                        #content{
                            margin-top: 0px !important;
                        }
                        #main-con{
                            padding-top: 0px !important;
                        }
                    </style>
                    <div style="margin-top: 40px;padding: 10px 0 0 0;border-bottom: 1px solid #dbdee0;background: #ffe19a;" class="no-print">
                        <div style="color:red;text-align:center;padding:5px;">
                        <?php 
                        if($message==7){
                            echo 'អ្នកមិនទាន់បានបង់ថ្លៃ Hosting & ថែទាំ';
                            echo '<br>';
                            echo 'ប្រព័ន្ធគ្រប់គ្រងរបស់អ្នកនឹងផុតកំណត់ក្នុងរយះពេល '.$difference_day.'ថ្ងៃទៀត សូមទាក់ទងអ្នកគ្រប់គ្រងដើម្បីប្រើប្រាស់បន្ត។<br>';
                            echo 'Your system will expired within '.$difference_day.' day';
                            echo '<center><p>
                                    Please connect to system owner to countinue using <br>
                                    Website: http://sbcsolution.biz/  <br>
                                    Phone  : 016 78 78 75 /095 78 78 65 
                            </p></center>';

                        }else if($message == 'today'){
                            echo 'អ្នកមិនបានបង់ថ្លៃ Hosting & ថែទាំ នេះជាថ្ងៃចុងក្រោយក្នុងការប្រើប្រាស់ <br>';
                            echo 'ប្រព័ន្ធគ្រប់គ្រងរបស់អ្នកនឹងផុតកំណត់ក្នុងថ្ងៃនេះ ម៉ោង ១២:00AM សូមទាក់ទងអ្នកគ្រប់គ្រងដើម្បីប្រើប្រាស់បន្ត។<br>';
                            echo '<center><p>
                                    Your system will expired today at 24:00AM, Please connect to system owner to countinue using <br>
                                    Website: http://sbcsolution.biz/  <br>
                                    Phone  : 016 78 78 75 /095 78 78 65 
                            </p></center>';
                        }else if($message =='expired'){
                            echo 'ប្រព័ន្ធគ្រប់គ្រងរបស់អ្នកនឹងផុតកំណត់ហើយ សូមទាក់ទងអ្នកគ្រប់គ្រងដើម្បីប្រើប្រាស់បន្ត។<br>
                                    Your system will expired, Please contact administrator to continue using. <br>';
                            echo 'Website: http://sbcsolution.biz/ <br>';
                            echo 'Phone  : 016 78 78 75 /095 78 78 65 ';
                            exit();
                        }else if($message =='uat'){
                            echo 'ប្រព័ន្ធគ្រប់គ្រងរបស់អ្នកស្ថិតក្នុងស្ថានភាពបណ្តោះអាសន្ន '.date("d/m/Y", strtotime($data->expired_date)).'<br>
                                    Your system are using in trail version untill '.date("d/m/Y", strtotime($data->expired_date)).'<br>';
                        }
                        ?>
                        </div>
                    </div>
                    <?php 
                    }
                }
            }
            curl_close ($ch);
        }else{
            echo "offline: Please move to Online";
            exit();
        }*/
        if ($bpas_language = $this->input->cookie('bpas_language', true)) {
            $this->config->set_item('language', $bpas_language);
            $this->lang->admin_load('bpas', $bpas_language);
            $this->Settings->user_language = $bpas_language;
        } else {
            $this->config->set_item('language', $this->Settings->language);
            $this->lang->admin_load('bpas', $this->Settings->language);
            $this->Settings->user_language = $this->Settings->language;
        }
        $this->theme = $this->Settings->theme . '/admin/views/';
        if (is_dir(VIEWPATH . $this->Settings->theme . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR)) {
            $this->data['assets'] = base_url() . 'themes/' . $this->Settings->theme . '/admin/assets/';
        } else {
            $this->data['assets'] = base_url() . 'themes/default/admin/assets/';
        }
        $this->data['Settings'] = $this->Settings;
        $this->loggedIn         = $this->bpas->logged_in();

        if ($this->loggedIn) {
            $this->default_currency         = $this->site->getCurrencyByCode($this->Settings->default_currency);
            $this->data['default_currency'] = $this->default_currency;
            $this->Store                    = $this->bpas->in_group('store') ? true : null;
            $this->data['Store']            = $this->Store;
            $this->Developer                = $this->bpas->in_group('developer') ? true : null;
            $this->data['Developer']        = $this->Developer;
            $this->Owner                    = $this->bpas->in_group('owner') ? true : null;
            $this->data['Owner']            = $this->Owner;
            $this->Customer                 = $this->bpas->in_group('customer') ? true : null;
            $this->data['Customer']         = $this->Customer;
            $this->Supplier                 = $this->bpas->in_group('supplier') ? true : null;
            $this->data['Supplier']         = $this->Supplier;
            $this->Admin                    = $this->bpas->in_group('admin') ? true : null;
            $this->data['Admin']            = $this->Admin;

            //-------
            $this->SaleLeader = $this->bpas->in_group('sale-team-leader') ? TRUE : NULL;
            $this->data['SaleLeader'] = $this->SaleLeader;
            $this->SaleAgent = $this->bpas->in_group('sale-agents') ? TRUE : NULL;
            $this->data['SaleAgent'] = $this->SaleAgent;


            if ($sd = $this->site->getDateFormat($this->Settings->dateformat)) {
                $dateFormats = [
                    'js_sdate'    => $sd->js,
                    'php_sdate'   => $sd->php,
                    'mysq_sdate'  => $sd->sql,
                    'js_ldate'    => $sd->js . ' hh:ii',
                    'php_ldate'   => $sd->php . ' H:i',
                    'mysql_ldate' => $sd->sql . ' %H:%i',
                ];
            } else {
                $dateFormats = [
                    'js_sdate'    => 'mm-dd-yyyy',
                    'php_sdate'   => 'm-d-Y',
                    'mysq_sdate'  => '%m-%d-%Y',
                    'js_ldate'    => 'mm-dd-yyyy hh:ii:ss',
                    'php_ldate'   => 'm-d-Y H:i:s',
                    'mysql_ldate' => '%m-%d-%Y %T',
                ];
            }
            if (file_exists(APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'Pos.php')) {
                define('POS',$this->Settings->pos);
            } else {
                define('POS', 0);
            }
            if (file_exists(APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'Shop.php')) {
                define('SHOP', $this->Settings->shop);
            } else {
                define('SHOP', 0);
            }
            if (!$this->Owner && !$this->Admin) {
                $gp               = $this->site->checkPermissions();
                $this->GP         = $gp[0];
                $this->data['GP'] = $gp[0];
            } else {
                $this->data['GP'] = null;
            }
            $this->dateFormats         = $dateFormats;
            $this->data['dateFormats'] = $dateFormats;
            $this->load->language('calendar');
            //$this->default_currency = $this->Settings->currency_code;
            //$this->data['default_currency'] = $this->default_currency;
            $this->m                    = strtolower($this->router->fetch_class());
            $this->v                    = strtolower($this->router->fetch_method());
            $this->data['m']            = $this->m;
            $this->data['v']            = $this->v;
            $this->data['dt_lang']      = json_encode(lang('datatables_lang'));
            $this->data['dp_lang']      = json_encode(['days' => [lang('cal_sunday'), lang('cal_monday'), lang('cal_tuesday'), lang('cal_wednesday'), lang('cal_thursday'), lang('cal_friday'), lang('cal_saturday'), lang('cal_sunday')], 'daysShort' => [lang('cal_sun'), lang('cal_mon'), lang('cal_tue'), lang('cal_wed'), lang('cal_thu'), lang('cal_fri'), lang('cal_sat'), lang('cal_sun')], 'daysMin' => [lang('cal_su'), lang('cal_mo'), lang('cal_tu'), lang('cal_we'), lang('cal_th'), lang('cal_fr'), lang('cal_sa'), lang('cal_su')], 'months' => [lang('cal_january'), lang('cal_february'), lang('cal_march'), lang('cal_april'), lang('cal_may'), lang('cal_june'), lang('cal_july'), lang('cal_august'), lang('cal_september'), lang('cal_october'), lang('cal_november'), lang('cal_december')], 'monthsShort' => [lang('cal_jan'), lang('cal_feb'), lang('cal_mar'), lang('cal_apr'), lang('cal_may'), lang('cal_jun'), lang('cal_jul'), lang('cal_aug'), lang('cal_sep'), lang('cal_oct'), lang('cal_nov'), lang('cal_dec')], 'today' => lang('today'), 'suffix' => [], 'meridiem' => []]);
            $this->Settings->indian_gst = false;
            if ($this->Settings->invoice_view > 0) {
                $this->Settings->indian_gst = $this->Settings->invoice_view == 2 ? true : false;
                $this->Settings->format_gst = true;
                $this->load->library('gst');
            }
        }
    }
    public function page_construct($page, $meta = [], $data = []){
        $meta['message']                               = isset($data['message']) ? $data['message'] : $this->session->flashdata('message');
        $meta['error']                                 = isset($data['error']) ? $data['error'] : $this->session->flashdata('error');
        $meta['warning']                               = isset($data['warning']) ? $data['warning'] : $this->session->flashdata('warning');
        $meta['info']                                  = $this->site->getNotifications();
        $meta['events']                                = $this->site->getUpcomingEvents();
        $meta['ip_address']                            = $this->input->ip_address();
        $meta['Owner']                                 = $data['Owner'];
        $meta['Admin']                                 = $data['Admin'];
        $meta['Store']                                 = $data['Store'];
        $meta['Supplier']                              = $data['Supplier'];
        $meta['Customer']                              = $data['Customer'];
        $meta['Settings']                              = $data['Settings'];
        $meta['dateFormats']                           = $data['dateFormats'];
        $meta['assets']                                = $data['assets'];
        $meta['GP']                                    = $data['GP'];

        $meta['qty_alert_num']                      = $this->site->get_total_qty_alerts();
        $meta['edit_sale_request_num']              = $this->site->get_total_edit_sale_request();
        $meta['results_approved']                   = $this->site->get_result_edit_sale_approved();
        $meta['edit_sale_request_padding']          = $this->site->get_edit_sale_request_padding();
        $meta['edit_sale_request_rejects']          = $this->site->get_edit_sale_request_rejects();
        $meta['exp_alert_num']                      = $this->site->get_expiring_qty_alerts(); 
        $meta['shop_sale_alerts']                   = SHOP ? $this->site->get_shop_sale_alerts() : 0;
        $meta['shop_payment_alerts']                = SHOP ? $this->site->get_shop_payment_alerts() : 0; 
        $meta['payment_alert_num']                  = $this->site->get_total_payment_alerts();
        // $meta['public_charge_num']               = $this->site->get_public_charge_alerts();
        $meta['payment_supplier_alert_num']         = $this->site->get_purchase_payments_alerts();
        $meta['payment_customer_alert_num']         = $this->site->get_customer_payments_alerts();
        $meta['sale_suspend_alert_num']             = $this->site->get_sale_suspend_alerts();
        $meta['customers_alert_num']                = $this->site->get_customer_alerts();
        $meta['deliveries_alert_num']               = $this->site->get_delivery_alerts();
        $meta['maintenance_alert_num']              = $this->site->get_maintenance_alerts();
        $meta['quoties_alert_num']                  = $this->site->get_quote_alerts();
        $meta['get_purchases_request_alerts']       = $this->site->get_purchases_request_alerts();
        $meta['get_purchases_order_alerts']         = $this->site->get_purchases_order_alerts();
        $meta['get_sale_order_order_alerts']        = $this->site->get_sale_order_order_alerts();
        $meta['get_purchases_order_deadline_alerts']=$this->site->get_purchases_order_deadline_alerts();
        $meta['get_purchases_request_deadline_alerts']=$this->site->get_purchases_request_deadline_alerts();
       
        // $meta['getPurchaseRequestDeadline']         =$this->site->getPurchaseRequestDeadline();
        $meta['transfer_alert_num']                 = $this->site->get_transfer_alerts();
        if($this->Settings->module_installment){
            $meta['missed_payment_alert_num']       = $this->site->getAlertInstallmentMissedRepayments();
        }
        if($this->Settings->module_hr){
            $meta['expired_document']               = $this->site->get_employee_doc_expired();
        }
        if($this->Settings->module_loan){
            $meta['loan_dates']                     = $this->site->get_loan_alert();
            $meta['loan_exp_day']                   = $this->site->get_loan_exp_day_alert();
            $meta['loan_late_exp_day']              = $this->site->get_loan_exp_late_day_alert(); 
             
        }
        $meta['module']                             = $this->uri->segment(2);
        $meta['report']                             = $this->uri->segment(2).'/'.$this->uri->segment(3);
        $meta['page_view']                          = isset($meta['page_view'])?$meta['page_view']:'';
        $this->load->view($this->theme . 'header', $meta);
        if($this->Settings->ui == 'default'){
            if($this->Store){
                $this->load->view($this->theme . 'header_store', $meta);
            }else{
                $this->load->view($this->theme . 'header_default', $meta);
            }
        }else{
            $this->load->view($this->theme . 'header_default', $meta);
            //$this->load->view($this->theme . 'header_full', $meta);
        }
        $this->load->view($this->theme . $page, $data);
        $this->load->view($this->theme . 'footer');
    }
}
