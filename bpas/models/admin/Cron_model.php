<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->lang->admin_load('cron');
    }

    public function getSettings()
    {
        $q = $this->db->get_where('settings', ['setting_id' => 1], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function run_cron()
    {   
        $alert_pending_invoice = 0;
        $alert_partial_invoice = 0;
        $alert_unpaid_purchase = 0;
        $alert_expired_product = 0;
        $alert_promotion       = 0;
        $alert_stock           = 1;

        $m = [];
        /*if ($this->resetOrderRef()) {
            $m[] = lang('order_ref_updated');
        }*/
        if ($alert_pending_invoice) {
            $pendingInvoices = $this->getAllPendingInvoices();
            $p = 0;
            foreach ($pendingInvoices as $invoice) {
                $this->updateInvoiceStatus($invoice->id);
                $p++;
            }
            $m[] = sprintf(lang('x_pending_to_due'), $p);
        }
        if ($alert_partial_invoice) {
            $partialInvoices = $this->getAllPPInvoices();
            $pp = 0;
            foreach ($partialInvoices as $invoice) {
                $this->updateInvoiceStatus($invoice->id);
                $pp++;
            }
            $m[] = sprintf(lang('x_partial_to_due'), $pp);
        }
        if ($alert_unpaid_purchase) {
            $unpaidpurchases = $this->getUnpaidPuchases();
            $up = 0;
            foreach ($unpaidpurchases as $purchase) {
                $this->db->update('purchases', ['payment_status' => 'due'], ['id' => $purchase->id]);
                $up++;
            }
            $m[] = sprintf(lang('x_purchases_changed'), $up);
        }
        if ($alert_expired_product) {
            $pis = $this->get_expired_products();
            $e  = 0;
            $ep = 0;
            foreach ($pis as $pi) {
                $this->db->update('purchase_items', ['quantity_balance' => 0], ['id' => $pi->id]);
                $e++;
                $ep += $pi->quantity_balance;
            }
            $this->site->syncQuantity(null, null, $pis);
            $m[] = sprintf(lang('x_products_expired'), $e, $ep);
        }
        if ($alert_promotion) {
            $promos = $this->getPromoProducts();
            $pro = 0;
            foreach ($promos as $pr) {
                $this->db->update('products', ['promotion' => 0], ['id' => $pr->id]);
                $pro++;
            }
            $m[] = sprintf(lang('x_promotions_expired'), $pro);
        }
        if ($alert_stock) {
            $this->db->update('products', ['sent_alert' => 0], ['quantity >' =>'alert_quantity']);            
            $m[] = lang('alert_stock_email');
        }
        $date = date('Y-m-d H:i:s', strtotime('-1 month'));
        if ($this->deleteUserLgoins($date)) {
            $m[] = sprintf(lang('user_login_deleted'), $date);
        }
        if ($this->db_backup()) {
            $m[] = lang('backup_done');
        }
        /*if ($this->checkUpdate()) {
            $m[] = lang('update_available');
        }*/
        $r = !empty($m) ? $m : false;
        $this->send_email($r);
        $this->db->truncate('sessions');
        return $r;
    }

    public function send_email($details)
    {
        if ($details) {
            $table_html = '';
            $tables     = $this->cron_model->yesterday_report(1);
            foreach ($tables as $table) {
                $table_html .= $table . '<div style="clear:both"></div>';
            }
            foreach ($details as $detail) {
                $table_html = $table_html . $detail;
            }
            $msg_with_yesterday_report = $table_html;
            $owners                    = $this->db->get_where('users', ['group_id' => 1])->result();
            $this->load->library('email');
            $config['useragent'] = 'SBC Solutions';
            $config['protocol']  = $this->Settings->protocol;
            $config['mailtype']  = 'html';
            $config['crlf']      = "\r\n";
            $config['newline']   = "\r\n";
            if ($this->Settings->protocol == 'sendmail') {
                $config['mailpath'] = $this->Settings->mailpath;
            } elseif ($this->Settings->protocol == 'smtp') {
                $config['smtp_host'] = $this->Settings->smtp_host;
                $config['smtp_user'] = $this->Settings->smtp_user;
                $config['smtp_pass'] = $this->Settings->smtp_pass;
                $config['smtp_port'] = $this->Settings->smtp_port;
                if (!empty($this->Settings->smtp_crypto)) {
                    $config['smtp_crypto'] = $this->Settings->smtp_crypto;
                }
            }
            $this->email->initialize($config);

            foreach ($owners as $owner) {
                list($user, $domain) = explode('@', $owner->email);
                if ($domain != 'tecdiary.com') {
                    $this->load->library('parser');
                    $parse_data = [
                        'name'      => $owner->first_name . ' ' . $owner->last_name,
                        'email'     => $owner->email,
                        'msg'       => $msg_with_yesterday_report,
                        'site_link' => base_url(),
                        'site_name' => $this->Settings->site_name,
                        'logo'      => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>',
                    ];
                    $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/cron.html');
                    $message = $this->parser->parse_string($msg, $parse_data);
                    $subject = lang('cron_job') . ' - ' . $this->Settings->site_name;

                    $this->email->from($this->Settings->default_email, $this->Settings->site_name);
                    $this->email->to($owner->email);
                    $this->email->subject($subject);
                    $this->email->message($message);
                    $this->email->send();
                }
            }
        }
    }

    private function yesterday_report($stock_only=null)
    {
        $date       = date('Y-m-d', strtotime('-1 day'));
        $sdate      = $date . ' 00:00:00';
        $edate      = $date . ' 23:59:59';
        
        $this->db->where('companies.group_name','biller');
        $q = $this->db->get('companies');
        $billers = $q->result();
        foreach ($billers as $biller) {
            $costing         = $this->getCosting($date, $biller->id);
            $discount        = $this->getOrderDiscount($sdate, $edate, $biller->id);
            $expenses        = $this->getExpenses($sdate, $edate, $biller->id);
            $returns         = $this->getReturns($sdate, $edate, $biller->id);
            $total_purchases = $this->getTotalPurchases($sdate, $edate, $biller->id);
            $total_sales     = $this->getTotalSales($sdate, $edate, $biller->id);
            $html[]          = $this->gen_html($costing, $discount, $expenses, $returns, $total_purchases, $total_sales,$biller,null,null);
        }
        /*----------check alert stock to email---------------*/
        $this->db->select('id,code, name, quantity, alert_quantity')
                ->from('products')
                ->where('alert_quantity >= quantity', null)
                ->where('sent_alert',0)
                ->where('track_quantity', 1);
        $q = $this->db->get();
        
        if ($q->num_rows() > 0) {
            $stock = array();
            foreach (($q->result()) as $row) {
                $stock[] = $row;
            }
            $stocks = $stock;
        }
        /*----------Close check alert stock to email---------------*/
        $costing         = $this->getCosting($date);
        $discount        = $this->getOrderDiscount($sdate, $edate);
        $expenses        = $this->getExpenses($sdate, $edate);
        $returns         = $this->getReturns($sdate, $edate);
        $total_purchases = $this->getTotalPurchases($sdate, $edate);
        $total_sales     = $this->getTotalSales($sdate, $edate);
        $html[]          = $this->gen_html($costing, $discount, $expenses, $returns, $total_purchases, $total_sales,null,$stocks,$stock_only);

        return $html;
    }

    private function gen_html($costing, $discount, $expenses, $returns, $purchases, $sales, $biller = null, $stocks = [], $stock_only = null) 
    {
        $html='';
        //if(!$stock_only){
            $html = '<div style="border:1px solid #DDD; padding:10px; margin:10px 0;">
            <h3>' . ($biller ? $biller->company . ' (' . $biller->name . ')' : lang('all_billers')) . '</h3>
            <table width="100%" class="stable">
            <tr>
                <td style="border-bottom: 1px solid #EEE;">' . lang('products_sale') . '</td>
                <td style="text-align:right; border-bottom: 1px solid #EEE;">' . $this->bpas->formatMoney($costing->sales) . '</td>
            </tr>';
            if ($discount && $discount->order_discount > 0) {
                $html .= '
                <tr>
                    <td style="border-bottom: 1px solid #DDD;">' . lang('order_discount') . '</td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;">' . $this->bpas->formatMoney($discount->order_discount) . '</td>
                </tr>';
            }
            $html .= '
            <tr>
                <td style="border-bottom: 1px solid #EEE;">' . lang('products_cost') . '</td>
                <td style="text-align:right; border-bottom: 1px solid #EEE;">' . $this->bpas->formatMoney($costing->cost) . '</td>
            </tr>';
            if ($expenses && $expenses->total > 0) {
                $html .= '
                <tr>
                    <td style="border-bottom: 1px solid #DDD;">' . lang('expenses') . '</td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;">' . $this->bpas->formatMoney($expenses->total) . '</td>
                </tr>';
            }
            $html .= '
            <tr>
                <td width="300px;" style="border-bottom: 1px solid #DDD;"><strong>' . lang('profit') . '</strong></td>
                <td style="text-align:right;border-bottom: 1px solid #DDD;">
                    <strong>' . $this->bpas->formatMoney($costing->sales - $costing->cost - ($discount ? $discount->order_discount : 0) - ($expenses ? $expenses->total : 0)) . '</strong>
                </td>
            </tr>';
            if (isset($returns->total)) {
                $html .= '
                <tr>
                    <td width="300px;" style="border-bottom: 2px solid #DDD;"><strong>' . lang('return_sales') . '</strong></td>
                    <td style="text-align:right;border-bottom: 2px solid #DDD;"><strong>' . $this->bpas->formatMoney($returns->total) . '</strong></td>
                </tr>';
            }
            $html .= '</table><h4 style="margin-top:15px;">' . lang('general_ledger') . '</h4>
            <table width="100%" class="stable">';
            if ($sales) {
                $html .= '
                <tr>
                    <td width="33%" style="border-bottom: 1px solid #DDD;">' . lang('total_sales') . ': <strong>' . $this->bpas->formatMoney($sales->total_amount) . '(' . $sales->total . ')</strong></td>
                    <td width="33%" style="border-bottom: 1px solid #DDD;">' . lang('received') . ': <strong>' . $this->bpas->formatMoney($sales->paid) . '</strong></td>
                    <td width="33%" style="border-bottom: 1px solid #DDD;">' . lang('taxes') . ': <strong>' . $this->bpas->formatMoney($sales->tax) . '</strong></td>
                </tr>';
            }
            if ($purchases) {
                $html .= '
                <tr>
                    <td width="33%">' . lang('total_purchases') . ': <strong>' . $this->bpas->formatMoney($purchases->total_amount) . '(' . $purchases->total . ')</strong></td>
                    <td width="33%">' . lang('paid') . ': <strong>' . $this->bpas->formatMoney($purchases->paid) . '</strong></td>
                    <td width="33%">' . lang('taxes') . ': <strong>' . $this->bpas->formatMoney($purchases->tax) . '</strong></td>
                </tr>';
            }

            $html .= '</table></div>';
        //}
        if($stocks){
            $html .= '<div style="border:1px solid #DDD; padding:10px; margin:10px 0;">
            <h3>' .lang('list_products').'</h3>
            <table width="100%" class="stable">
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">'.lang('products_name').'</td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;">'.lang('quantity').'</td>
                </tr>';
                foreach($stocks as $stock){
                    $html .= '
                        <tr>
                            <td style="border-bottom: 1px solid #DDD;">' .$stock->name.'('.$stock->code.')</td>
                            <td style="text-align:right;border-bottom: 1px solid #DDD;">' . $stock->quantity . '</td>
                        </tr>';
                }
            $html .= '</table></div>';
        }
        return $html;
    }

    private function checkUpdate()
    {
        $fields = ['version' => $this->Settings->version, 'code' => $this->Settings->purchase_code, 'username' => $this->Settings->envato_username, 'site' => base_url()];
        $this->load->helper('update');
        $protocol = is_https() ? 'https://' : 'http://';
        $updates  = get_remote_contents($protocol . 'sbcsolution.biz/api/v1/update/', $fields);
        $response = json_decode($updates);
        if (!empty($response->data->updates)) {
            $this->db->update('settings', ['update' => 1], ['setting_id' => 1]);
            return true;
        }
        return false;
    }

    private function db_backup()
    {
        $this->load->dbutil();
        $prefs = [
            'format'   => 'txt',
            'filename' => 'sma_db_backup.sql',
        ];
        $back    = $this->dbutil->backup($prefs);
        $backup  = &$back;
        $db_name = 'db-backup-on-' . date('Y-m-d-H-i-s') . '.txt';
        $save    = './files/backups/' . $db_name;
        $this->load->helper('file');
        write_file($save, $backup);

        $files = glob('./files/backups/*.txt', GLOB_BRACE);
        $now   = time();
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * 30) {
                    unlink($file);
                }
            }
        }

        return true;
    }

    private function deleteUserLgoins($date)
    {
        $this->db->where('time <', $date);
        if ($this->db->delete('user_logins')) {
            return true;
        }
        return false;
    }
    private function get_expired_products()
    {
        if ($this->Settings->remove_expired) {
            $date = date('Y-m-d');
            $this->db->where('expiry <=', $date)->where('expiry !=', null)->where('expiry !=', '0000-00-00')->where('quantity_balance >', 0);
            $q = $this->db->get('purchase_items');
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
                return $data;
            }
        }
        return false;
    }

    private function getAllPendingInvoices()
    {
        $today    = date('Y-m-d');
        $paid     = $this->lang->line('paid');
        $canceled = $this->lang->line('cancelled');
        $q        = $this->db->get_where('sales', ['due_date <=' => $today, 'due_date !=' => '1970-01-01', 'due_date !=' => null, 'payment_status' => 'pending']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    private function getAllPPInvoices()
    {
        $today    = date('Y-m-d');
        $paid     = $this->lang->line('paid');
        $canceled = $this->lang->line('cancelled');
        $q        = $this->db->get_where('sales', ['due_date <=' => $today, 'due_date !=' => '1970-01-01', 'due_date !=' => null, 'payment_status' => 'partial']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    private function getCosting($date, $biller_id = null)
    {
        $this->db->select('SUM( COALESCE( purchase_unit_cost, 0 ) * quantity ) AS cost, SUM( COALESCE( sale_unit_price, 0 ) * quantity ) AS sales, SUM( COALESCE( purchase_net_unit_cost, 0 ) * quantity ) AS net_cost, SUM( COALESCE( sale_net_unit_price, 0 ) * quantity ) AS net_sales', false);
        $this->db->where('costing.date', $date);
        if ($biller_id) {
            $this->db->join('sales', 'sales.id=costing.sale_id')
            ->where('sales.biller_id', $biller_id);
        }

        $q = $this->db->get('costing');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    private function getExpenses($sdate, $edate, $biller_id = null)
    {
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS total', false);
        $this->db->where('date >=', $sdate)->where('date <=', $edate);
        if ($biller_id) {
            $this->db->where('biller_id', $biller_id);
        }

        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    private function getOrderDiscount($sdate, $edate, $biller_id = null)
    {
        $this->db->select('SUM( COALESCE( order_discount, 0 ) ) AS order_discount', false);
        $this->db->where('date >=', $sdate)->where('date <=', $edate);
        if ($biller_id) {
            $this->db->where('biller_id', $biller_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    private function getOrderRef()
    {
        $q = $this->db->get_where('order_ref', ['ref_id' => 1], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    private function getPromoProducts()
    {
        $today = date('Y-m-d');
        $q     = $this->db->get_where('products', ['promotion' => 1, 'end_date !=' => null, 'end_date <=' => $today]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    private function getReturns($sdate, $edate, $biller_id = null)
    {
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total', false)
        ->where('sale_status', 'returned');
        $this->db->where('date >=', $sdate)->where('date <=', $edate);
        if ($biller_id) {
            $this->db->where('biller_id', $biller_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    private function getTotalPurchases($sdate, $edate, $biller_id = null)
    {
        $this->db->select('count(id) as total, sum(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax', false)
            ->where('status !=', 'pending')
            ->where('date >=', $sdate)->where('date <=', $edate);
        if ($biller_id) {
            $this->db->where('biller_id', $biller_id);
        }
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    private function getTotalSales($sdate, $edate, $biller_id = null)
    {
        $this->db->select('count(id) as total, sum(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax', false)
            ->where('sale_status !=', 'pending')
            ->where('date >=', $sdate)->where('date <=', $edate);
        if ($biller_id) {
            $this->db->where('biller_id', $biller_id);
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    private function getUnpaidPuchases()
    {
        $today = date('Y-m-d');
        $q     = $this->db->get_where('purchases', ['payment_status !=' => 'paid', 'payment_status !=' => 'due', 'payment_term >' => 0, 'due_date !=' => null, 'due_date <=' => $today]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    private function resetOrderRef()
    {
        if ($this->Settings->reference_format == 1 || $this->Settings->reference_format == 2) {
            $month = date('Y-m') . '-01';
            $year  = date('Y') . '-01-01';
            if ($ref = $this->getOrderRef()) {
                $reset_ref = [
                    'so' => 1, 'qu' => 1, 'po' => 1, 'to' => 1, 'pos' => 1, 'do' => 1, 
                    'pay' => 1, 'ppay' => 1, 
                    're' => 1, 'rep' => 1, 
                    'ex' => 1, 'qa' => 1
                ];
                if ($this->Settings->reference_format == 1 && strtotime($ref->date) < strtotime($year)) {
                    $reset_ref['date'] = $year;
                    $this->db->update('order_ref', $reset_ref, ['ref_id' => 1]);
                    return true;
                } elseif ($this->Settings->reference_format == 2 && strtotime($ref->date) < strtotime($month)) {
                    $reset_ref['date'] = $month;
                    $this->db->update('order_ref', $reset_ref, ['ref_id' => 1]);
                    return true;
                }
            }
        }
        return false;
    }

    private function updateInvoiceStatus($id)
    {
        if ($this->db->update('sales', ['payment_status' => 'due'], ['id' => $id])) {
            return true;
        }
        return false;
    }


    public function run_cron_telegram_alert()
    {
        $data = [];
        // if ($values = $this->getProductQtyAlerts()) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Product Quantity Alert ***" . 
        //             "\nProduct Code: " . $value->code .
        //             "\nProduct Name: " . $value->name .
        //             "\nQuantity: " . $value->quantity .
        //             "\nQuantity Alert: " . $value->alert_quantity;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr;
        // }
        // if ($values = $this->getProductExpiringAlerts()) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Product Expiring Alert ***" . 
        //             "\nProduct Code: " . $value->product_code .
        //             "\nProduct Name: " . $value->product_name .
        //             "\nQuantity: " . $value->quantity_balance .
        //             "\nWarehouse: " . $value->wh_name .
        //             "\nExpire Date: " . $value->expiry;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr;
        // }
        // if ($values = $this->getSalePaymentsAlerts()) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Customer Payment Alert ***" .
        //             "\n--- Customer Info ---" .
        //             "\nCustomer Name: " . $value->customer .
        //             "\nPhone: " . $value->phone .
        //             "\nAddress: " . $value->address .
        //             "\n--- Invoice Info ---" . 
        //             "\nDate: " . $value->date .
        //             "\nReference No: " . $value->reference_no .
        //             "\nTotal Ttems: " . $value->total_items .
        //             "\nDiscount: " . $value->total_discount .
        //             "\nTax: " . $value->total_tax .
        //             "\nGrand Total: " . $value->grand_total .
        //             "\nPaid: " . $value->paid .
        //             "\nBalance: " . $value->balance .
        //             "\nDue Date: " . $value->due_date . 
        //             "\nPayment Status: " . $value->payment_status .
        //             "\nSale Status: " . $value->sale_status .
        //             "\n--- Invoice Details ---" .
        //             "\n" . $value->items_detail;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr;
        // }
        // if ($values = $this->getPurchasePaymentsAlerts()) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Purchase Payment Alert ***" .
        //             "\n--- Supplier Info ---" .
        //             "\nSupplier: " . $value->supplier .
        //             "\nPhone: " . $value->phone .
        //             "\nEmail: " . $value->email .
        //             "\nAddress: " . $value->address .
        //             "\n--- Purchase Info ---" . 
        //             "\nDate: " . $value->date .
        //             "\nProject Name: " . $value->project_name .
        //             "\nReference No: " . $value->reference_no .
        //             "\nWarehouse: " . $value->wname .
        //             "\nDiscount: " . $value->total_discount .
        //             "\nTax: " . $value->total_tax .
        //             "\nGrand Total: " . $value->grand_total .
        //             "\nPaid: " . $value->paid .
        //             "\nBalance: " . $value->balance .
        //             "\nDue Date: " . $value->due_date . 
        //             "\nPayment Status: " . $value->payment_status .
        //             "\nPurchase Status: " . $value->status . 
        //             "\n--- Purchase Details ---" .
        //             "\n" . $value->items_detail;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr;
        // }
        // if ($values = $this->getCustomerPaymentsAlerts()) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Customers Balance Alert ***" .
        //             "\nCustomer: " . $value->customer .
        //             "\nGender: " . $value->gender .
        //             "\nPhone: " . $value->phone .
        //             "\nEmail: " . $value->email .
        //             "\nAddress: " . $value->address .
        //             "\nBalance: " . $value->balance;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr;
        // }
        // if ($values = $this->getPurchasesRequestAlerts()) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Purchase Request Alert ***" .
        //             "\n--- Purchase Request Info ---" . 
        //             "\nDate: " . $value->date .
        //             "\nProject: " . $value->project_name .
        //             "\nReference No: " . $value->reference_no .
        //             "\nWarehouse: " . $value->wname .
        //             "\nSupplier: " . $value->supplier .
        //             "\nDiscount: " . $value->total_discount .
        //             "\nTax: " . $value->total_tax .
        //             "\nGrand Total: " . $value->grand_total .
        //             "\nOrder Status: " . $value->order_status .
        //             "\nStatus: " . $value->status .
        //             "\n--- Purchase Request Details ---" .
        //             "\n" . $value->items_detail;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr;
        // }
        // if ($values = $this->getPurchasesOrderAlerts()) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Purchase Order Alert ***" .
        //             "\n--- Purchase Order Info ---" . 
        //             "\nDate: " . $value->date .
        //             "\nProject: " . $value->project_name .
        //             "\nReference No: " . $value->reference_no .
        //             "\nPR No: " . $value->purchase_ref .
        //             "\nWarehouse: " . $value->wname .
        //             "\nSupplier: " . $value->supplier .
        //             "\nDiscount: " . $value->total_discount .
        //             "\nTax: " . $value->total_tax .
        //             "\nGrand Total: " . $value->grand_total .
        //             "\nStatus: " . $value->status . 
        //             "\n--- Purchase Order Details ---" .
        //             "\n" . $value->items_detail;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr;
        // }
        // if ($values = $this->getPurchasesRequestDeadlineAlerts()) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Purchase Request Deadline Alert ***" .
        //             "\n--- Purchase Request Info ---" . 
        //             "\nDate: " . $value->date .
        //             "\nProject: " . $value->project_name .
        //             "\nReference No: " . $value->reference_no .
        //             "\nWarehouse: " . $value->wname .
        //             "\nSupplier: " . $value->supplier .
        //             "\nDiscount: " . $value->total_discount .
        //             "\nTax: " . $value->total_tax .
        //             "\nGrand Total: " . $value->grand_total .
        //             "\nDeadline: " . $value->deadline .
        //             "\nOrder Status: " . $value->order_status .
        //             "\nStatus: " . $value->status . 
        //             "\n--- Purchase Request Details ---" .
        //             "\n" . $value->items_detail;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr;
        // }
        // if ($values = $this->getPurchasesOrderDeadlineAlerts()) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Purchase Order Deadline Alert ***" .
        //             "\n--- Purchase Order Info ---" . 
        //             "\nDate: " . $value->date .
        //             "\nProject: " . $value->project_name .
        //             "\nReference No: " . $value->reference_no .
        //             "\nPR No: " . $value->purchase_ref .
        //             "\nWarehouse: " . $value->wname .
        //             "\nSupplier: " . $value->supplier .
        //             "\nDiscount: " . $value->total_discount .
        //             "\nTax: " . $value->total_tax .
        //             "\nGrand Total: " . $value->grand_total .
        //             "\nDeadline: " . $value->deadline .
        //             "\nOrder Status: " . $value->order_status .
        //             "\nStatus: " . $value->status . 
        //             "\n--- Purchase Order Details ---" .
        //             "\n" . $value->items_detail;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr;
        // }
        // if ($values = $this->getLoansAlerts('alert')) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Leasing Alert ***" .
        //             "\n--- Customer Info ---" . 
        //             "\nCustomer: " . $value->customer .
        //             "\nPhone: " . $value->phone .
        //             "\nAddress: " . $value->address .
        //             "\n--- Leasing Info ---" . 
        //             "\nRegister Date: " . $value->register_date .
        //             "\nReference No: " . $value->reference .
        //             "\nMonthly: " . $value->monthly_payment .
        //             "\nPrincipal: " . $value->principal .
        //             "\nInterest: " . $value->interest .
        //             "\nBalance: " . $value->balance .
        //             "\nPaid: " . $value->paid .
        //             "\nRepay Date: " . $value->pay_date .
        //             "\nStatus: " . $value->status;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr;
        // }
        // if ($values = $this->getLoansAlerts('exp_alert')) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Leasing Expire Alert ***" .
        //             "\n--- Customer Info ---" . 
        //             "\nCustomer: " . $value->customer .
        //             "\nPhone: " . $value->phone .
        //             "\nAddress: " . $value->address .
        //             "\n--- Leasing Info ---" . 
        //             "\nRegister Date: " . $value->register_date .
        //             "\nReference No: " . $value->reference .
        //             "\nMonthly: " . $value->monthly_payment .
        //             "\nPrincipal: " . $value->principal .
        //             "\nInterest: " . $value->interest .
        //             "\nBalance: " . $value->balance .
        //             "\nPaid: " . $value->paid .
        //             "\nRepay Date: " . $value->pay_date .
        //             "\nStatus: " . $value->status;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr;
        // }
        // if ($values = $this->getLoansAlerts('late_exp')) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Leasing Late Alert ***" .
        //             "\n--- Customer Info ---" . 
        //             "\nCustomer: " . $value->customer .
        //             "\nPhone: " . $value->phone .
        //             "\nAddress: " . $value->address .
        //             "\n--- Leasing Info ---" . 
        //             "\nRegister Date: " . $value->register_date .
        //             "\nReference No: " . $value->reference .
        //             "\nMonthly: " . $value->monthly_payment .
        //             "\nPrincipal: " . $value->principal .
        //             "\nInterest: " . $value->interest .
        //             "\nBalance: " . $value->balance .
        //             "\nPaid: " . $value->paid .
        //             "\nRepay Date: " . $value->pay_date .
        //             "\nStatus: " . $value->status;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr; 
        // }
        // if ($values = $this->getQuotesAlers()) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Quote Alert ***" .
        //             "\n--- Customer Info ---" . 
        //             "\nCustomer: " . $value->customer .
        //             "\nPhone: " . $value->phone .
        //             "\nAddress: " . $value->address .
        //             "\n--- Quote Info ---" . 
        //             "\nDate: " . $value->date .
        //             "\nReference No: " . $value->reference_no .
        //             "\nBiller: " . $value->biller .
        //             "\nDiscount: " . $value->total_discount .
        //             "\nTax: " . $value->total_tax .
        //             "\nGrand Total: " . $value->grand_total .
        //             "\nStatus: " . $value->status .
        //             "\n--- Quote Details ---" . 
        //             "\n" . $value->items_detail;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr; 
        // }
        // if ($values = $this->getSaleOrdersAlerts()) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Sale Order Alert ***" .
        //             "\n--- Customer Info ---" . 
        //             "\nCustomer: " . $value->customer .
        //             "\nPhone: " . $value->phone .
        //             "\nAddress: " . $value->address .
        //             "\n--- Sale Order Info ---" . 
        //             "\nDate: " . $value->date .
        //             "\nReference No: " . $value->reference_no .
        //             "\nBiller: " . $value->biller .
        //             "\nDiscount: " . $value->total_discount .
        //             "\nTax: " . $value->total_tax .
        //             "\nGrand Total: " . $value->grand_total .
        //             "\nBooking: " . $value->deposit .
        //             "\nBalance: " . $value->balance .
        //             "\nOrder Status: " . $value->order_status .
        //             "\nSale Status: " . $value->sale_status .
        //             "\n--- Sale Order Details ---" . 
        //             "\n" . $value->items_detail;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr; 
        // }
        // if ($values = $this->getDeliveryAlerts()) {
        //     $arr = array();
        //     foreach ($values as $value) {
        //         $message = "*** Delivery Alert ***" .
        //             "\n--- Customer Info ---" . 
        //             "\nCustomer: " . $value->customer .
        //             "\nPhone: " . $value->phone .
        //             "\nAddress: " . $value->address .
        //             "\n--- Sale Order Info ---" . 
        //             "\nDate: " . $value->date .
        //             "\nReference No: " . $value->reference_no .
        //             "\nBiller: " . $value->biller .
        //             // "\nDiscount: " . $value->total_discount .
        //             // "\nTax: " . $value->total_tax .
        //             "\nTotal Items: " . $value->total_qty .
        //             "\nItems Received: " . $value->qty_received .
        //             "\nItems Balance: " . $value->qty_balance .
        //             "\nGrand Total: " . $value->grand_total .
        //             "\nBooking: " . $value->deposit .
        //             "\nBalance: " . $value->balance .
        //             "\nOrder Status: " . $value->order_status .
        //             "\nSale Status: " . $value->sale_status .
        //             "\nDelivery Status: " . $value->delivery_status .
        //             "\nDelivery Date: " . $value->delivery_date .
        //             "\n--- Sale Order Details ---" . 
        //             "\n" . $value->items_detail;
        //         $arr[] = $message;
        //     }
        //     $data[] = $arr; 
        // }
        if ($values = $this->getTransferAlerts()) {
            $arr = array();
            foreach ($values as $value) {
                $message = "*** Transfer Alert ***" .
                    "\n--- Transfer Info ---" . 
                    "\nDate: " . $value->date .
                    "\nReference No: " . $value->transfer_no .
                    "\nWarehouse(FROM): " . $value->fname . '(' . $value->fcode . ')' .
                    "\nWarehouse(TO): " . $value->tname . '(' . $value->tcode . ')' .
                    "\nTax: " . $value->total_tax .
                    "\nGrand Total: " . $value->grand_total .
                    "\nStatus: " . $value->status .
                    "\n--- Transfer Details ---" . 
                    "\n" . $value->items_detail;
                $arr[] = $message;
            }
            $data[] = $arr; 

            // var_dump($arr);
            // exit();
        }

        return $data;
    }

    private function getProductQtyAlerts()
    {
        $this->db->select('code, name, quantity, alert_quantity')->where('quantity < alert_quantity', null, false)->where('track_quantity', 1);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    private function getProductExpiringAlerts()
    {
        $date = date('Y-m-d', strtotime('+3 months'));
        $this->db->select('product_code, product_name, quantity_balance, warehouses.name as wh_name, expiry')
            ->from('purchase_items')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('warehouses', 'warehouses.id=purchase_items.warehouse_id', 'left')
            ->where('expiry !=', null)->where('expiry !=', '0000-00-00')
            ->where('quantity_balance >', 0)
            ->where('expiry <', $date);

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    private function getSalePaymentsAlerts()
    {
        $si = "( SELECT sale_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_code,  '    ', {$this->db->dbprefix('sale_items')}.quantity, ' x ', {$this->db->dbprefix('sale_items')}.unit_price, ' = ', {$this->db->dbprefix('sale_items')}.subtotal) SEPARATOR '\n')) as item_nane from {$this->db->dbprefix('sale_items')} ";
        $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";

        $this->db->select("
            DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date,
            IF({$this->db->dbprefix('companies')}.company != '-', CONCAT({$this->db->dbprefix('companies')}.company, '/', {$this->db->dbprefix('companies')}.name), {$this->db->dbprefix('companies')}.name) as customer, 
            {$this->db->dbprefix('companies')}.phone, 
            CONCAT({$this->db->dbprefix('companies')}.address, ', ', {$this->db->dbprefix('companies')}.city, ' ', {$this->db->dbprefix('companies')}.country) as address,
            {$this->db->dbprefix('sales')}.reference_no,
            {$this->db->dbprefix('sales')}.total_items, (FSI.item_nane) as items_detail, 
            {$this->db->dbprefix('sales')}.total_discount,
            {$this->db->dbprefix('sales')}.total_tax,
            {$this->db->dbprefix('sales')}.grand_total,
            {$this->db->dbprefix('sales')}.paid,
            ({$this->db->dbprefix('sales')}.grand_total - {$this->db->dbprefix('sales')}.paid) as balance,
            {$this->db->dbprefix('sales')}.due_date,
            {$this->db->dbprefix('sales')}.payment_status,
            {$this->db->dbprefix('sales')}.sale_status,
        ");

        $this->db->join($si, 'FSI.sale_id = sales.id', 'left');
        $this->db->join('companies', 'companies.id = sales.customer_id', 'left');
        $this->db->group_start();
        $this->db->where('DATE_SUB(due_date , INTERVAL(SELECT alert_day FROM bpas_settings) DAY) < CURDATE()');
        $this->db->or_where('due_date IS NULL AND DATE_ADD(date, INTERVAL(SELECT alert_day - 1 FROM bpas_settings) DAY) < CURDATE()');
        $this->db->group_end();
        $this->db->where(array('payment_status !=' => 'paid', 'sale_status !=' => 'returned', 'hide' => 1));

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    private function getPurchasePaymentsAlerts()
    {
        $pi = "( SELECT purchase_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_code,  '    ', {$this->db->dbprefix('purchase_items')}.quantity, ' x ', {$this->db->dbprefix('purchase_items')}.unit_cost, ' = ', {$this->db->dbprefix('purchase_items')}.subtotal) SEPARATOR '\n')) as item_nane from {$this->db->dbprefix('purchase_items')} ";
        $pi .= " GROUP BY {$this->db->dbprefix('purchase_items')}.purchase_id ) FPI";

        if(!$this->Settings->avc_costing){
            $this->db->select("
                DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date,
                project_name, reference_no, {$this->db->dbprefix('warehouses')}.name as wname, 
                IF({$this->db->dbprefix('companies')}.company != '-', CONCAT({$this->db->dbprefix('companies')}.company, '/', {$this->db->dbprefix('companies')}.name), {$this->db->dbprefix('companies')}.name) as supplier,
                {$this->db->dbprefix('companies')}.phone,
                {$this->db->dbprefix('companies')}.email,
                CONCAT({$this->db->dbprefix('companies')}.address, ', ', {$this->db->dbprefix('companies')}.city, ' ', {$this->db->dbprefix('companies')}.country) as address,
                (FPI.item_nane) as items_detail, 
                grand_total, paid, (grand_total-paid) as balance, 
                {$this->db->dbprefix('purchases')}.total_discount,
                {$this->db->dbprefix('purchases')}.total_tax,
                {$this->db->dbprefix('purchases')}.due_date, {$this->db->dbprefix('purchases')}.status, {$this->db->dbprefix('purchases')}.payment_status,
                {$this->db->dbprefix('purchases')}.id as id", false);
        }else{
            $this->db->select("
                DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date,
                project_name,reference_no, {$this->db->dbprefix('warehouses')}.name as wname,
                IF({$this->db->dbprefix('companies')}.company != '-', CONCAT({$this->db->dbprefix('companies')}.company, '/', {$this->db->dbprefix('companies')}.name), {$this->db->dbprefix('companies')}.name) as supplier,
                {$this->db->dbprefix('companies')}.phone,
                {$this->db->dbprefix('companies')}.email,
                CONCAT({$this->db->dbprefix('companies')}.address, ', ', {$this->db->dbprefix('companies')}.city, ' ', {$this->db->dbprefix('companies')}.country) as address, 
                (FPI.item_nane) as items_detail, 
                (grand_total-shipping) as grand_total, paid, ((grand_total-shipping)-paid) as balance, 
                {$this->db->dbprefix('purchases')}.total_discount,
                {$this->db->dbprefix('purchases')}.total_tax,
                {$this->db->dbprefix('purchases')}.due_date, {$this->db->dbprefix('purchases')}.status, {$this->db->dbprefix('purchases')}.payment_status,
                {$this->db->dbprefix('purchases')}.id as id", false);
        }

        $this->db->from('purchases');
        $this->db->join($pi, 'FPI.purchase_id=purchases.id', 'left');
        $this->db->join('companies', 'companies.id=purchases.supplier_id', 'left');
        $this->db->join('projects', 'projects.project_id=purchases.project_id', 'left');
        $this->db->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left');

        $this->db->group_start();
        $this->db->where('DATE_SUB(due_date , INTERVAL(SELECT alert_day FROM bpas_settings) DAY) < CURDATE()');
        $this->db->or_where('due_date IS NULL AND DATE_ADD(date, INTERVAL(SELECT alert_day - 1 FROM bpas_settings) DAY) < CURDATE()');
        $this->db->group_end();
        $this->db->where(array('purchases.payment_status !=' => 'paid', 'purchases.status !=' => 'returned', 'purchases.total !=' => 0));

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    private function getCustomerPaymentsAlerts()
    {
        $this->db->select("
                IF({$this->db->dbprefix('companies')}.company != '-', CONCAT({$this->db->dbprefix('companies')}.company, '/', {$this->db->dbprefix('companies')}.name), {$this->db->dbprefix('companies')}.name) as customer, 
                {$this->db->dbprefix('companies')}.cf4 as gender, phone, email,
                CONCAT({$this->db->dbprefix('companies')}.address, ', ', {$this->db->dbprefix('companies')}.city, ' ', {$this->db->dbprefix('companies')}.country) as address, 
                SUM(grand_total-paid) as balance")
            ->from('sales')
            ->join('companies', 'companies.id = sales.customer_id')
            ->where('due_date !=', NULL)->where('due_date !=', '0000-00-00')
            ->where('DATE_SUB(due_date, INTERVAL(SELECT alert_day FROM bpas_settings) DAY) < CURDATE()')
            ->where(array('payment_status !=' => 'paid', 'sale_status !=' => 'returned', 'hide' => 1))
            ->group_by('customer_id');
        
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    private function getPurchasesRequestAlerts()
    {
        $pri = "( SELECT purchase_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_request_items')}.product_code,  '    ', {$this->db->dbprefix('purchase_request_items')}.quantity, ' x ', {$this->db->dbprefix('purchase_request_items')}.unit_cost, ' = ', {$this->db->dbprefix('purchase_request_items')}.subtotal) SEPARATOR '\n')) as item_nane from {$this->db->dbprefix('purchase_request_items')} ";
        $pri .= " GROUP BY {$this->db->dbprefix('purchase_request_items')}.purchase_id ) FPI";

        $this->db->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, project_name, reference_no, supplier, order_status, total_discount, total_tax, grand_total, bpas_purchases_request.status, (FPI.item_nane) as items_detail, {$this->db->dbprefix('warehouses')}.name as wname")
            ->from('purchases_request')
            ->join($pri, 'FPI.purchase_id=purchases_request.id', 'left')
            ->join('warehouses', 'purchases_request.warehouse_id = warehouses.id', 'left')
            ->join('projects', 'purchases_request.project_id = projects.project_id', 'left')
            ->where('purchases_request.status','requested');
        
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    private function getPurchasesOrderAlerts()
    {
        $poi = "( SELECT purchase_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_order_items')}.product_code,  '    ', {$this->db->dbprefix('purchase_order_items')}.quantity, ' x ', {$this->db->dbprefix('purchase_order_items')}.unit_cost, ' = ', {$this->db->dbprefix('purchase_order_items')}.subtotal) SEPARATOR '\n')) as item_nane from {$this->db->dbprefix('purchase_order_items')} ";
        $poi .= " GROUP BY {$this->db->dbprefix('purchase_order_items')}.purchase_id ) FPI";

        $this->db->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, project_name, reference_no, purchase_ref, supplier, order_status, total_discount, total_tax, grand_total, bpas_purchases_order.status, (FPI.item_nane) as items_detail, {$this->db->dbprefix('warehouses')}.name as wname")
            ->from('purchases_order')
            ->join($poi, 'FPI.purchase_id=purchases_order.id', 'left')
            ->join('warehouses', 'purchases_order.warehouse_id = warehouses.id', 'left')
            ->join('projects', 'purchases_order.project_id = projects.project_id', 'left')
            ->where('purchases_order.status','pending');
        
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    private function getPurchasesRequestDeadlineAlerts()
    {
        $pri = "( SELECT purchase_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_request_items')}.product_code,  '    ', {$this->db->dbprefix('purchase_request_items')}.quantity, ' x ', {$this->db->dbprefix('purchase_request_items')}.unit_cost, ' = ', {$this->db->dbprefix('purchase_request_items')}.subtotal) SEPARATOR '\n')) as item_nane from {$this->db->dbprefix('purchase_request_items')} ";
        $pri .= " GROUP BY {$this->db->dbprefix('purchase_request_items')}.purchase_id ) FPI";

        $this->db->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, project_name, reference_no, supplier, order_status, total_discount, total_tax, grand_total, deadline, bpas_purchases_request.status, (FPI.item_nane) as items_detail, {$this->db->dbprefix('warehouses')}.name as wname")
            ->from('purchases_request')
            ->join($pri, 'FPI.purchase_id=purchases_request.id', 'left')
            ->join('projects', 'purchases_request.project_id = projects.project_id', 'left')
            ->join('warehouses', 'purchases_request.warehouse_id = warehouses.id', 'left')
            ->where('purchases_request.deadline !=', null)
            ->where('purchases_request.deadline !=', '0000-00-00')
            ->where('purchases_request.deadline !=', '0000-00-00 00:00:00')
            ->where('purchases_request.deadline !=', '1970-01-01')
            ->where('purchases_request.deadline <=', date('Y-m-d'));

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    private function getPurchasesOrderDeadlineAlerts()
    {
        $poi = "( SELECT purchase_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_order_items')}.product_code,  '    ', {$this->db->dbprefix('purchase_order_items')}.quantity, ' x ', {$this->db->dbprefix('purchase_order_items')}.unit_cost, ' = ', {$this->db->dbprefix('purchase_order_items')}.subtotal) SEPARATOR '\n')) as item_nane from {$this->db->dbprefix('purchase_order_items')} ";
        $poi .= " GROUP BY {$this->db->dbprefix('purchase_order_items')}.purchase_id ) FPI";

        $this->db->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, project_name, reference_no, purchase_ref, supplier, order_status, total_discount, total_tax, grand_total, deadline, bpas_purchases_order.status, (FPI.item_nane) as items_detail, {$this->db->dbprefix('warehouses')}.name as wname")
            ->from('purchases_order')
            ->join($poi, 'FPI.purchase_id=purchases_order.id', 'left')
            ->join('warehouses', 'purchases_order.warehouse_id = warehouses.id', 'left')
            ->join('projects', 'purchases_order.project_id = projects.project_id', 'left')
            ->where('purchases_order.deadline !=', NULL)
            ->where('purchases_order.deadline !=', '0000-00-00')
            ->where('purchases_order.deadline !=', '0000-00-00 00:00:00')
            ->where('purchases_order.deadline !=', '1970-01-01')
            ->where('purchases_order.deadline <=', date('Y-m-d'));

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    private function getLoansAlerts($status = null)
    {
        $alert_day = (!empty($this->Settings->alert_day) || $this->Settings->alert_day != '') ? $this->Settings->alert_day : 7;
        $date1 = date('Y-m-d', strtotime('+' . $alert_day . ' day'));
        $date2 = date('Y-m-d');

        $this->db->select(" 
                {$this->db->dbprefix('loan_payment')}.*,
                IF({$this->db->dbprefix('companies')}.company != '-', CONCAT({$this->db->dbprefix('companies')}.company, '/', {$this->db->dbprefix('companies')}.name), {$this->db->dbprefix('companies')}.name) as customer, 
                {$this->db->dbprefix('companies')}.phone, 
                CONCAT({$this->db->dbprefix('companies')}.address, ', ', {$this->db->dbprefix('companies')}.city, ' ', {$this->db->dbprefix('companies')}.country) as address")
            ->join('companies', 'companies.id = loan_payment.customer_id')
            ->group_by('loan_id')
            ->order_by('pay_date', 'asc');

        if ($status == 'alert') {
            $this->db->where('pay_date <=', $date1);
        } else if ($status == 'exp_alert') {
            $this->db->where('pay_date', $date2);
        } else if ($status == 'late_exp') {
            $this->db->where('pay_date <', $date2);
        } else {
            $this->db->where('pay_date !=', null);
        }

        $q = $this->db->get('loan_payment');
        if($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    private function getQuotesAlers()
    {
        $qi = "( SELECT quote_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('quote_items')}.product_code,  '    ', {$this->db->dbprefix('quote_items')}.quantity, ' x ', {$this->db->dbprefix('quote_items')}.unit_price, ' = ', {$this->db->dbprefix('quote_items')}.subtotal) SEPARATOR '\n')) as item_nane from {$this->db->dbprefix('quote_items')} ";
        $qi .= " GROUP BY {$this->db->dbprefix('quote_items')}.quote_id ) FQI";

        $this->db->select("
                DATE_FORMAT({$this->db->dbprefix('quotes')}.date, '%Y-%m-%d %T') as date, reference_no, biller, supplier, total_discount, total_tax, grand_total, {$this->db->dbprefix('quotes')}.status, (FQI.item_nane) as items_detail, 
                IF({$this->db->dbprefix('companies')}.company != '-', CONCAT({$this->db->dbprefix('companies')}.company, '/', {$this->db->dbprefix('companies')}.name), {$this->db->dbprefix('companies')}.name) as customer, 
                {$this->db->dbprefix('companies')}.phone, 
                CONCAT({$this->db->dbprefix('companies')}.address, ', ', {$this->db->dbprefix('companies')}.city, ' ', {$this->db->dbprefix('companies')}.country) as address")
            ->from('quotes')
            ->join($qi, 'FQI.quote_id = quotes.id', 'left')
            ->join('companies', 'quotes.customer_id = companies.id', 'left')
            ->where('quotes.status','pending');
        
        $q = $this->db->get();
        if($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    private function getSaleOrdersAlerts()
    {
        $soi = "( SELECT sale_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('sale_order_items')}.product_code,  '    ', {$this->db->dbprefix('sale_order_items')}.quantity, ' x ', {$this->db->dbprefix('sale_order_items')}.unit_price, ' = ', {$this->db->dbprefix('sale_order_items')}.subtotal) SEPARATOR '\n')) as item_nane from {$this->db->dbprefix('sale_order_items')} ";
        $soi .= " GROUP BY {$this->db->dbprefix('sale_order_items')}.sale_id ) FSI";

        $this->db->select("
                    DATE_FORMAT({$this->db->dbprefix('sales_order')}.date, '%Y-%m-%d %T') as date, reference_no, biller,
                    IF({$this->db->dbprefix('companies')}.company != '-', CONCAT({$this->db->dbprefix('companies')}.company, '/', {$this->db->dbprefix('companies')}.name), {$this->db->dbprefix('companies')}.name) as customer, 
                    {$this->db->dbprefix('companies')}.phone, 
                    CONCAT({$this->db->dbprefix('companies')}.address, ', ', {$this->db->dbprefix('companies')}.city, ' ', {$this->db->dbprefix('companies')}.country) as address,
                    total_discount, total_tax, grand_total, paid,
                    grand_total - COALESCE(SUM({$this->db->dbprefix('deposits')}.amount), 0) as balance,
                    COALESCE(SUM({$this->db->dbprefix('deposits')}.amount), 0) as deposit,
                    {$this->db->dbprefix('sales_order')}.sale_status,
                    {$this->db->dbprefix('sales_order')}.order_status,
                    (FSI.item_nane) as items_detail
                ")
            ->from('sales_order')
            ->join($soi, 'FSI.sale_id = sales_order.id', 'left')
            ->join('companies', 'companies.id = sales_order.customer_id', 'left')
            ->join('deposits', 'deposits.so_id = sales_order.id', 'left')
            ->where('(DATE_SUB(due_date , INTERVAL(SELECT alert_day FROM bpas_settings) DAY) < CURDATE()) OR (due_date IS NULL AND DATE_ADD(bpas_sales_order.date, INTERVAL(SELECT alert_day - 1 FROM bpas_settings) DAY) < CURDATE())')
            ->where('sales_order.order_status', 'pending')
            ->group_by('sales_order.id');

        $q = $this->db->get();
        if($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    private function getDeliveryAlerts()
    {
        $soi = "( SELECT sale_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('sale_order_items')}.product_code,  '    ', {$this->db->dbprefix('sale_order_items')}.quantity, ' x ', {$this->db->dbprefix('sale_order_items')}.unit_price, ' = ', {$this->db->dbprefix('sale_order_items')}.subtotal) SEPARATOR '\n')) as item_nane from {$this->db->dbprefix('sale_order_items')} ";
        $soi .= " GROUP BY {$this->db->dbprefix('sale_order_items')}.sale_id ) FSI";

        $this->db->select("
                    {$this->db->dbprefix('sales_order')}.id as id, DATE_FORMAT({$this->db->dbprefix('sales_order')}.date, '%Y-%m-%d %T') as date, reference_no, 
                    IF(biller.company != '-', CONCAT(biller.company, '/', biller.name), biller.name) as biller, 
                    IF(customer.company != '-', customer.company, customer.name) as customer, customer.phone, CONCAT(customer.address, ', ', customer.city, ' ', customer.country) as address,
                    CONCAT_WS(' ', {$this->db->dbprefix('users')}.first_name, {$this->db->dbprefix('users')}.last_name) as saleman, 
                    COALESCE(SUM({$this->db->dbprefix('sale_order_items')}.quantity), 0) as total_qty, 
                    COALESCE(SUM({$this->db->dbprefix('sale_order_items')}.quantity_received), 0) as qty_received, 
                    COALESCE(SUM({$this->db->dbprefix('sale_order_items')}.quantity), 0) - COALESCE(SUM({$this->db->dbprefix('sale_order_items')}.quantity_received), 0) as qty_balance, 
                    total_discount, total_tax, grand_total, paid,
                    grand_total - COALESCE(SUM({$this->db->dbprefix('deposits')}.amount), 0) as balance,
                    COALESCE(SUM({$this->db->dbprefix('deposits')}.amount), 0) as deposit,
                    {$this->db->dbprefix('sales_order')}.delivery_by,
                    {$this->db->dbprefix('sales_order')}.delivery_date,
                    {$this->db->dbprefix('sales_order')}.delivery_status,
                    {$this->db->dbprefix('sales_order')}.sale_status,
                    {$this->db->dbprefix('sales_order')}.order_status,
                    (FSI.item_nane) as items_detail
                ")
            ->from('sales_order')
            ->join($soi, 'FSI.sale_id = sales_order.id', 'left')
            ->join('companies as customer', 'customer.id = sales_order.customer_id', 'inner')
            ->join('companies as biller', 'biller.id = sales_order.biller_id', 'inner')
            ->join('users', 'sales_order.saleman_by = users.id', 'left')
            ->join('sale_order_items', 'sales_order.id = sale_order_items.sale_id', 'left')
            ->join('deposits', 'deposits.so_id = sales_order.id', 'left')
            ->where('sales_order.sale_status', 'order')
            ->where('DATE_SUB(delivery_date, INTERVAL (SELECT alert_day FROM bpas_settings) DAY) < CURDATE()')
            ->where('sales_order.order_status', 'approved')
            ->group_by('sales_order.id');

        $q = $this->db->get();
        if($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    private function getTransferAlerts()
    {
        $tsi = "( SELECT transfer_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('transfer_items')}.product_code,  '    ', {$this->db->dbprefix('transfer_items')}.quantity) SEPARATOR '\n')) as item_nane from {$this->db->dbprefix('transfer_items')} ";
        $tsi .= " GROUP BY {$this->db->dbprefix('transfer_items')}.transfer_id ) FSI";

        $this->db->select("date, transfer_no, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname, to_warehouse_code as tcode, total_tax, grand_total, status, attachment, (FSI.item_nane) as items_detail")
            ->from('transfers')
            ->join($tsi, 'FSI.transfer_id = transfers.id', 'left')
            ->where('transfers.status', 'pending');

        $q = $this->db->get();
        if($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
}
