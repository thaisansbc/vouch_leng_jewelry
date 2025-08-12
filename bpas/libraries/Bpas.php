<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 *  ==============================================================================
 *  Author    : Kimpheng
 *  Email     : support@sbcsolution.biz
 *  For       : SBC Solutions
 *  Web       : http://sbcsolution.biz
 *  ==============================================================================
 */

class Bpas
{
    public function __construct()
    {
        
    }
    function convert_number_to_words($number) 
    {
        
        $number = str_replace(',','',$number)-0;
   
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'forty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );
       
        if (!is_numeric($number)) {
            return false;
        }
       
        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . $this->convert_number_to_words(abs($number));
        }
       
        $string = $fraction = null;
       
        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }
       
        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->convert_number_to_words($remainder);
                }
                break;
        }
       
        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }
       
        return $string;
    }
    public function actionPermissions($action = null, $module = null)
    {
        if ($this->Owner || $this->Admin) {
            if ($this->Admin && stripos($action, 'delete') !== false) {
                return false;
            }
            return true;
        } elseif ($this->Customer || $this->Supplier) {
            return false;
        } else {
            if (!$module) {
                $module = $this->m;
            }
            if (!$action) {
                $action = $this->v;
            }
            //$gp = $this->site->checkPermissions();
            if ($this->GP[$module . '-' . $action] == 1) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function analyze_term_($term)
    {
        $spos = strpos($term, $this->Settings->barcode_separator);
        if ($spos !== false) {
            $st        = explode($this->Settings->barcode_separator, $term);
            $sr        = trim($st[0]);
            $option_id = trim($st[1]);
        } else {
            $sr        = $term;
            $option_id = false;
        }
        return ['term' => $sr, 'option_id' => $option_id];
    }

    public function analyze_term($term)
    {
        $spos = strpos($term, $this->Settings->barcode_separator);
        if ($spos !== false) {
            // $st        = explode($this->Settings->barcode_separator, $term);
            $sr        = trim($term);
            $option_id = false;
        } else {
            $sr        = $term;
            $option_id = false;
        }
        return ['term' => $sr, 'option_id' => $option_id];
    }

    public function barcode($text = null, $bcs = 'code128', $height = 74, $stext = 1, $get_be = false, $re = false)
    {
        $drawText = ($stext != 1) ? false : true;
        $this->load->library('tec_barcode', '', 'bc');
        return $this->bc->generate($text, $bcs, $height, $drawText, $get_be, $re);
    }

    public function base64url_decode($data)
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    public function base64url_encode($data, $pad = null)
    {
        $data = str_replace(['+', '/'], ['-', '_'], base64_encode($data));
        if (!$pad) {
            $data = rtrim($data, '=');
        }
        return $data;
    }

    public function checkPermissions($action = null, $js = null, $module = null)
    {
        if (!$this->actionPermissions($action, $module)) {
            $this->session->set_flashdata('error', lang('access_denied'));
            if ($js) {
                die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : site_url('welcome')) . "'; }, 10);</script>");
            } else {
                redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
            }
        }
    }

    public function clear_tags($str)
    {
        return htmlentities(
            strip_tags(
                $str,
                '<span><div><a><br><p><b><i><u><img><blockquote><small><ul><ol><li><hr><big><pre><code><strong><em><table><tr><td><th><tbody><thead><tfoot><h3><h4><h5><h6>'
            ),
            ENT_QUOTES | ENT_XHTML | ENT_HTML5,
            'UTF-8'
        );
    }

    public function convertMoney($amount, $format = true, $symbol = true)
    {
        if ($this->Settings->selected_currency != $this->Settings->default_currency) {
            $amount = $this->formatDecimal(($amount * $this->selected_currency->rate), 3);
        }
        return ($format ? $this->formatMoney($amount, $this->selected_currency->symbol) : $amount);
    }

    public function decode_html($str)
    {
        return html_entity_decode($str, ENT_QUOTES | ENT_XHTML | ENT_HTML5, 'UTF-8');
    }
    function daysBetween2($dt1, $dt2) {
        return date_diff(
            date_create($dt2),  
            date_create($dt1)
        )->format('%a');
    }
    function daysBetween($dt1, $dt2) {
        return date_diff(
            date_create($dt2),  
            date_create($dt1)
        )->format('%a');
    }
    public function dateDifference($startDate, $endDate)
    {
            $startDate = strtotime($startDate);
            $endDate = strtotime($endDate);
            if ($startDate === false || $startDate < 0 || $endDate === false || $endDate < 0 || $startDate > $endDate)
                return false;

            $years = date('Y', $endDate) - date('Y', $startDate);

            $endMonth = date('m', $endDate);
            $startMonth = date('m', $startDate);

            // Calculate months
            $months = $endMonth - $startMonth;
            if ($months <= 0)  {
                $months += 12;
                $years--;
            }
            if ($years < 0)
                return false;

            // Calculate the days
            $measure = ($months == 1) ? 'month' : 'months';
            $days = $endDate - strtotime('+' . $months . ' ' . $measure, $startDate);
            $days = date('z', $days);   

            return array($years, $months, $days);
    }
    public function convert_datetime_to_time($ldate)
    {
        if ($ldate) {
            $date     = explode(' ', $ldate);
            $jsd      = $this->dateFormats['js_sdate'];
            $inv_date = $date[0];
            $time     = $date[1];
            $date     =  $time;
            return $date;
        } else {
            return '00:00:00';
        }
    }
    public function fld($ldate)
    {
        if ($ldate) {
            $date     = explode(' ', $ldate);
            $jsd      = $this->dateFormats['js_sdate'];
            $inv_date = $date[0];
            $time = null;
            if(isset($date[1])){
                $time     = $date[1];
            }
            if($time != NULL){
                if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 3, 2) . '-' . substr($inv_date, 0, 2) . ' ' . $time;
            } elseif ($jsd == 'mm-dd-yyyy' || $jsd == 'mm/dd/yyyy' || $jsd == 'mm.dd.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 0, 2) . '-' . substr($inv_date, 3, 2) . ' ' . $time;
            } else {
                $date = $inv_date;
            }
        }else{
              if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 3, 2) . '-' . substr($inv_date, 0, 2);
            } elseif ($jsd == 'mm-dd-yyyy' || $jsd == 'mm/dd/yyyy' || $jsd == 'mm.dd.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 0, 2) . '-' . substr($inv_date, 3, 2);
            } else {
                $date = $inv_date;
            } 
        }
         
            return $date;
        } else {
            return '0000-00-00 00:00:00';
        }
    }
    public function fldl($ldate)
    {
        if ($ldate) {
            $date     = explode(' ', $ldate);
            $jsd      = $this->dateFormats['js_sdate'];
            $inv_date = $date[0];
            $time     = $date[1];
            if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 3, 2) . '-' . substr($inv_date, 0, 2) . ' ' . $time;
            } elseif ($jsd == 'mm-dd-yyyy' || $jsd == 'mm/dd/yyyy' || $jsd == 'yyyy/mm/dd' || $jsd == 'yyyy-mm-dd' || $jsd == 'mm.dd.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 0, 2) . '-' . substr($inv_date, 3, 2) . ' ' . $time;
            } else {
                $date = $inv_date;
            }
            return $date;
        } else {
            return '0000-00-00 00:00:00';
        }
    }
    public function fldc($ldate)
    {
        if ($ldate) {
            $date     = explode(' ', $ldate);
            $jsd      = $this->dateFormats['js_sdate'];
            $inv_date = $date[0];
            $time     = $date[1];
            
            if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                $date =  substr($inv_date, -2). '/' . substr($inv_date, 5, 2) . '/' . substr($inv_date, 0,4) . ' ' . $time;
            } else {
                $date = $inv_date;
            }
            return $date;
        } else {
            return '0000/00/00 00:00:00';
        }
    }
    public function formatDecimal($number, $decimals = null)
    {
        if (!is_numeric($number)) {
            return null;
        }
        if (!$decimals && $decimals !== 0) {
            $decimals = $this->Settings->decimals;
        }
        return number_format($number, $decimals, '.', '');
    }
    public function formatDecimalRaw($number = false)
    {
        if (!is_numeric($number)) {
            return null;
        }
        return $number;
    }
    public function formatMoneykh($number)
    {
        /*// if ($this->Settings->sac) {
        //     return $this->formatSAC($this->formatDecimal($number)) ;
        // }
        //$decimals = $this->Settings->decimals;
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        //$ds = $this->Settings->decimals_sep;
        return  number_format($number, $decimals, $ds, $ts);*/

        $number = (($number / 100) * 100);
        if ($this->Settings->sac) {
            return ($this->Settings->display_symbol == 1 ? $this->Settings->symbol : '') .
            $this->formatSAC($this->formatDecimal($number)) .
            ($this->Settings->display_symbol == 2 ? $this->Settings->symbol : '');
        }
        $decimals = $this->Settings->decimals;
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return ($this->Settings->display_symbol == 1 ? '' : '') .
        number_format($number, 0, $ds, $ts) .
        ($this->Settings->display_symbol == 2 ? '' : '');
    }
    
    public function formatMoney($number, $symbol = false, $decimals = null)
    {
        if ($symbol !== 'none') {
            $symbol = $symbol ? $symbol : $this->Settings->symbol;
        } else {
            $symbol = null;
        }
        if ($this->Settings->sac) {
            return ((($this->Settings->display_symbol == 1 || $symbol) && $this->Settings->display_symbol != 2) ? $symbol : '') .
            $this->formatSAC($this->formatDecimal($number)) .
            ($this->Settings->display_symbol == 2 ? $symbol : '');
        }
        $decimals = $decimals ? $decimals : $this->Settings->decimals;
        $ts       = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds       = $this->Settings->decimals_sep;
        return ((($this->Settings->display_symbol == 1 || $symbol && $number != 0) && $this->Settings->display_symbol != 2) ? $symbol : '') .
        number_format($number, $decimals, $ds, $ts) .
        ($this->Settings->display_symbol == 2 && $number != 0 ? $symbol : '');
    }

    public function formatMoney2($number = false)
    {
        if($number==0 || $number=='' || !$number){
            return '';
        }else{
            if ($this->Settings->sac) {
                return ($this->Settings->display_symbol == 1 ? $this->Settings->symbol : '') .
                $this->formatSAC($this->formatDecimal($number)) .
                ($this->Settings->display_symbol == 2 ? $this->Settings->symbol : '');
            }
            $decimals = $this->Settings->decimals;
            $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
            $ds = $this->Settings->decimals_sep;
            return ($this->Settings->display_symbol == 1 ? $this->Settings->symbol : '') .
            number_format($number, $decimals, $ds, $ts) .
            ($this->Settings->display_symbol == 2 ? $this->Settings->symbol : '');
        }
       
    }
    public function formatNumber($number, $decimals = null)
    {
        if (!$decimals) {
            $decimals = $this->Settings->decimals;
        }
        if ($this->Settings->sac) {
            return $this->formatSAC($this->formatDecimal($number, $decimals));
        }
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return number_format($number, $decimals, $ds, $ts);
    }

    public function formatQuantity($number, $decimals = null)
    {
        if (!$decimals) {
            $decimals = $this->Settings->qty_decimals;
        }
        if ($this->Settings->sac) {
            return $this->formatSAC($this->formatDecimal($number, $decimals));
        }
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return number_format($number, $decimals, $ds, $ts);
    }

    public function formatQuantityDecimal($number, $decimals = null)
    {
        if (!$decimals) {
            $decimals = $this->Settings->qty_decimals;
        }
        return number_format($number, $decimals, '.', '');
    }

    public function formatSAC($num)
    {
        $pos = strpos((string) $num, '.');
        if ($pos === false) {
            $decimalpart = '00';
        } else {
            $decimalpart = substr($num, $pos + 1, 2);
            $num         = substr($num, 0, $pos);
        }

        if (strlen($num) > 3 & strlen($num) <= 12) {
            $last3digits         = substr($num, -3);
            $numexceptlastdigits = substr($num, 0, -3);
            $formatted           = $this->makecomma($numexceptlastdigits);
            $stringtoreturn      = $formatted . ',' . $last3digits . '.' . $decimalpart;
        } elseif (strlen($num) <= 3) {
            $stringtoreturn = $num . '.' . $decimalpart;
        } elseif (strlen($num) > 12) {
            $stringtoreturn = number_format($num, 2);
        }

        if (substr($stringtoreturn, 0, 2) == '-,') {
            $stringtoreturn = '-' . substr($stringtoreturn, 2);
        }

        return $stringtoreturn;
    }

    public function fsd($inv_date)
    {
        if ($inv_date) {
            $jsd = $this->dateFormats['js_sdate'];
            if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 3, 2) . '-' . substr($inv_date, 0, 2);
            } elseif ($jsd == 'mm-dd-yyyy' || $jsd == 'mm/dd/yyyy' || $jsd == 'mm.dd.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 0, 2) . '-' . substr($inv_date, 3, 2);
            } else {
                $date = $inv_date;
            }
            return $date;
        } else {
            return '0000-00-00';
        }
    }

    public function generate_pdf($content, $name = 'download.pdf', $output_type = null, $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'P')
    {
        if ($this->Settings->pdf_lib == 'dompdf') {
            $this->load->library('tec_dompdf', '', 'pdf');
        } else {
            $this->load->library('tec_mpdf', '', 'pdf');
        }

        return $this->pdf->generate($content, $name, $output_type, $footer, $margin_bottom, $header, $margin_top, $orientation);
    }

    public function getCardBalance($number)
    {
        if ($card = $this->site->getGiftCardByNO($number)) {
            return $card->balance;
        }
        return 0;
    }

    public function hrld($ldate)
    {
        if ($ldate) {
            return date($this->dateFormats['php_ldate'], strtotime($ldate));
        } else {
            return '0000-00-00 00:00:00';
        }
    }
    public function hrlt($ldate)
    {
        if ($ldate) {
            return date('H:i:s', strtotime($ldate));
        } else {
            return '00:00:00';
        }
    }
    public function hrsd($sdate)
    {
       
        if ($sdate) {
            return date($this->dateFormats['php_sdate'], strtotime($sdate));
        } else {
            return '0000-00-00';
        }
    }
    
    public function send_Telegram($link, $parameter)
    {
        $request_url = $link.'/sendMessage?'.http_build_query($parameter); 
        if (file_get_contents($request_url)) {
            return true;
        }
        return false;
    }
    public function in_group($check_group, $id = false)
    {
        if (!$this->logged_in()) {
            return false;
        }
        $id || $id = $this->session->userdata('user_id');
        $group     = $this->site->getUserGroup($id);
        if ($group->name === $check_group) {
            return true;
        }
        return false;
    }

    public function isPromo($product)
    {
        if (is_array($product)) {
            $product = json_decode(json_encode($product), false);
        }
        $today = date('Y-m-d');
        return $product->promotion && $product->start_date <= $today && $product->end_date >= $today && $product->promo_price;
    }

    public function log_payment($type, $msg, $val = null)
    {
        $this->load->library('logs');
        return (bool) $this->logs->write($type, $msg, $val);
    }

    public function logged_in()
    {
        return (bool) $this->session->userdata('identity');
    }

    public function makecomma($input)
    {
        if (strlen($input) <= 2) {
            return $input;
        }
        $length          = substr($input, 0, strlen($input) - 2);
        $formatted_input = $this->makecomma($length) . ',' . substr($input, -2);
        return $formatted_input;
    }

    public function md($page = false)
    {
        die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . ($page ? site_url($page) : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome')) . "'; }, 10);</script>");
    }

    public function paid_by()
    {
        
        $opts =array(
            'cash' => 'cash',
            'ABA'   => 'ABA',
            'Acleda' => 'Acleda',
            'AliPay' => 'AliPay',
            'PiPay' => 'PiPay',
            'Wing'  => 'Wing',
            'gift_card' => 'gift_card',
            'CC'        => 'CC',
            'Cheque' => 'cheque',
            'other' => 'other'
        );
        return $opts;
    }
  
    public function paid_opts($paid_by = null, $purchase = false, $empty_opt = false)
    {
        $opts = '';
        if ($empty_opt) {
            $opts .= '<option value="">' . lang('select') . '</option>';
        }
        $paid_bys = $this->site->getCashAccounts();
        foreach ($paid_bys as $field_id) {
            $opts .= '
            <option value="'.$field_id->code.'"' . ($paid_by && $paid_by == $field_id->code ? ' selected="selected"' : '') . '>' .($field_id->account_code ? $field_id->account_code.' | ' : '').lang($field_id->name) . '</option>';
        }
        //if (!$purchase) {
            $opts .= '<option value="other"' . ($paid_by && $paid_by == 'other' ? ' selected="selected"' : '') . '>' . lang('other') . '</option>';
            $opts .= '<option value="deposit"' . ($paid_by && $paid_by == 'deposit' ? ' selected="selected"' : '') . '>' . lang('deposit') . '</option>';
       // }
        return $opts;
    }
    public function cash_opts($paid_by = null, $deposit = false, $empty_opt = false, $gift_card = false){
        $opts = '';
        if(!$paid_by){
            $paid_by = $this->Settings->default_cash_account;
        }
        if ($empty_opt) {
            $opts .= '<option value="">'.lang('select').' '.lang('cash_account').'</option>';
        }
        $cash_accounts = $this->site->getCashAccounts();
        if($cash_accounts){
            foreach($cash_accounts as $cash_account){
                $opts .= '<option cash_type="'.$cash_account->type.'" value="'.$cash_account->id.'" '.($paid_by && $paid_by == $cash_account->id ? ' selected="selected"' : '').'>'.$cash_account->name.'</option>';
            }
        }
        if (!$deposit) {
            $opts .= '<option cash_type="deposit" value="deposit"'.($paid_by && $paid_by == 'deposit' ? ' selected="selected"' : '').'>'.lang("deposit").'</option>';
        }
        if($this->config->item("gift_card")){
            if (!$gift_card) {
                $opts .= '<option cash_type="gift_card" value="gift_card"'.($paid_by && $paid_by == 'gift_card' ? ' selected="selected"' : '').'>'.lang("gift_card").'</option>';
            }
        }
        return $opts;
    }
    public function print_arrays()
    {
        $args = func_get_args();
        echo '<pre>';
        foreach ($args as $arg) {
            print_r($arg);
        }
        echo '</pre>';
        die();
    }

    public function qrcode($type = 'text', $text = 'http://sbcsolution.biz', $size = 2, $level = 'H', $sq = null)
    {
        $file_name = 'assets/uploads/qrcode' . $this->session->userdata('user_id') . ($sq ? $sq : '') . ($this->Settings->barcode_img ? '.png' : '.svg');
        if ($type == 'link') {
            $text = urldecode($text);
        }
        $this->load->library('tec_qrcode', '', 'qr');
        $config = ['data' => $text, 'size' => $size, 'level' => $level, 'savename' => $file_name];
        $this->qr->generate($config);
        $imagedata = file_get_contents($file_name);
        return "<img src='data:image/png;base64," . base64_encode($imagedata) . "' alt='{$text}' class='qrimg'/>";
    }
    public function qrcodebiller($type = 'text', $text = 'http://sbcsolution.biz', $size = 2, $level = 'H', $sq = null)
    {
        $file_name = 'assets/uploads/qrcode' . $this->session->userdata('user_id') . ($sq ? $sq : '') . ($this->Settings->barcode_img ? '.png' : '.svg');
        if ($type == 'link') {
            $text = urldecode($text);
        }
        $this->load->library('tec_qrcode', '', 'qr');
        $config = ['data' => $text, 'size' => $size, 'level' => $level, 'savename' => $file_name];
        $this->qr->generate($config);
        $imagedata = file_get_contents($file_name);
        return "<img src='data:image/png;base64," . base64_encode($imagedata) . "' alt='{$text}' class='qrimg' height='60px' />";
    }
    public function qrcode_note($type = 'text', $text = 'http://tecdiary.com', $size = 2, $level = 'H', $sq = null)
    {
        $file_name = 'assets/uploads/qrcode' . $this->session->userdata('user_id') . ($sq ? $sq : '') . ($this->Settings->barcode_img ? '.png' : '.svg');
        if ($type == 'link') {
            $text = urldecode($text);
        }
        $this->load->library('tec_qrcode', '', 'qr');
        $config = ['data' => $text, 'size' => $size, 'level' => $level, 'savename' => $file_name];
        $this->qr->generate($config);
        $imagedata = file_get_contents($file_name);
        return "<img src='data:image/png;base64," . base64_encode($imagedata) . "' alt='{$text}' class='qrimg' height='35px' />";
    }
    public function qrcode_a5($type = 'text', $text = 'http://tecdiary.com', $size = 2, $level = 'H', $sq = null)
    {
        $file_name = 'assets/uploads/qrcode' . $this->session->userdata('user_id') . ($sq ? $sq : '') . ($this->Settings->barcode_img ? '.png' : '.svg');
        if ($type == 'link') {
            $text = urldecode($text);
        }
        $this->load->library('tec_qrcode', '', 'qr');
        $config = ['data' => $text, 'size' => $size, 'level' => $level, 'savename' => $file_name];
        $this->qr->generate($config);
        $imagedata = file_get_contents($file_name);
        return "<img src='data:image/png;base64," . base64_encode($imagedata) . "' alt='{$text}' class='qrimg' height='50px' />";
    }


    public function roundMoney($num, $nearest = 0.05)
    {
        return round($num * (1 / $nearest)) * $nearest;
    }

    public function roundNumber($number, $toref = null)
    {
        switch ($toref) {
            case 1:
                $rn = round($number * 20) / 20;
                break;
            case 2:
                $rn = round($number * 2) / 2;
                break;
            case 3:
                $rn = round($number);
                break;
            case 4:
                $rn = ceil($number);
                break;
            default:
                $rn = $number;
        }
        return $rn;
    }

    public function send_email($to, $subject, $message, $from = null, $from_name = null, $attachment = null, $cc = null, $bcc = null)
    {
        list($user, $domain) = explode('@', $to);

            $result = false;
            $this->load->library('tec_mail');
            try {
                $result = $this->tec_mail->send_mail($to, $subject, $message, $from, $from_name, $attachment, $cc, $bcc);
            } catch (\Exception $e) {
                $this->session->set_flashdata('error', 'Mail Error: ' . $e->getMessage());
                throw new \Exception($e->getMessage());
            }
            return $result;
        
        return false;
    }

    public function send_json($data)
    {
        header('Content-Type: application/json');
        die(json_encode($data));
        exit;
    }

    public function setCustomerGroupPrice($price, $customer_group)
    {
        if (!isset($customer_group) || empty($customer_group)) {
            return $price;
        }
        return $this->formatDecimal($price + (($price * $customer_group->percent) / 100));
    }

    public function slug($title, $type = null, $r = 1)
    {
        $this->load->helper('text');
        $slug       = url_title(convert_accented_characters($title), '-', true);
        $check_slug = $this->site->checkSlug($slug, $type);
        if (!empty($check_slug)) {
            $slug = $slug . $r;
            $r++;
            $this->slug($slug, $type, $r);
        }
        return $slug;
    }

    public function unset_data($ud)
    {
        if ($this->session->userdata($ud)) {
            $this->session->unset_userdata($ud);
            return true;
        }
        return false;
    }

    public function unzip($source, $destination = './')
    {
        // @chmod($destination, 0777);
        $zip = new ZipArchive;
        if ($zip->open(str_replace('//', '/', $source)) === true) {
            $zip->extractTo($destination);
            $zip->close();
        }
        // @chmod($destination,0755);

        return true;
    }

    public function update_award_points($total, $customer, $saleman, $scope = null)
    {
        if ($customer != null && !empty($this->Settings->each_spent) && $total >= $this->Settings->each_spent) {
            $company      = $this->site->getCompanyByID($customer);
            $points       = floor(($total / $this->Settings->each_spent) * $this->Settings->ca_point);
            $total_points = $scope ? $company->award_points - $points : $company->award_points + $points;
            $this->db->update('companies', ['award_points' => $total_points], ['id' => $customer]);
        }
        if ($saleman != null && !empty($this->Settings->each_sale) && !$this->Customer && $total >= $this->Settings->each_sale) {
            $staff        = $this->site->getUser($saleman);
            $points       = floor(($total / $this->Settings->each_sale) * $this->Settings->sa_point);
            $total_points = $scope ? $staff->award_points - $points : $staff->award_points + $points;
            $this->db->update('users', ['award_points' => $total_points], ['id' => $saleman]);
        }
        return true;
    }
    public function update_award_points_byQty($sale_id, $customer, $saleman, $scope = null)
    {
       
        $items=$this->site->getAllSaleItems($sale_id);
        if($this->Settings->apoint_option=='qty'){
            //------
           
            //SavePointByInvoice


            $points=0;
            $totalqty=0;
             foreach($items as $item){
                    $product =$this->site->getProductByID($item->product_id);   
                    
               
                        if($product->stock_type== '8'){
                            $totalqty = $totalqty+ $item->quantity;
                    }
             }
           
             if($totalqty!=0){
                if($totalqty >=$this->Settings->each_qty){
                $points = floor( $totalqty / $this->Settings->each_qty) * $this->Settings->qca_point;
               }
             }
             if($customer != null  && !empty($points)){
                $company      = $this->site->getCompanyByID($customer);
                if($company->customer_group_id==6){
             
                $oldPoint  = $company->award_points;
                $tpoints= $points+$oldPoint;
                $this->db->update('companies', ['award_points' => $tpoints], ['id' => $customer]);
                }
                
             }
             
            // $allinv=$this->site->getAllSaleItemsByCus($customer);
            //$this->Settings->each_qty;
            //$this->Settings->qca_point;
        }else{
           
            if ($customer != null && !empty($this->Settings->each_spent) && $total >= $this->Settings->each_spent) {
                $company      = $this->site->getCompanyByID($customer);
                if($company->customer_group_id==6){
                $points       = floor(($total / $this->Settings->each_spent) * $this->Settings->ca_point);
                $total_points = $scope ? $company->award_points - $points : $company->award_points + $points;
                $this->db->update('companies', ['award_points' => $total_points], ['id' => $customer]);
            }
        }
        }
        
        if ($saleman != null && !empty($this->Settings->each_sale) && !$this->Customer && $total >= $this->Settings->each_sale) {
            $staff        = $this->site->getUser($saleman);
            $points       = floor(($total / $this->Settings->each_sale) * $this->Settings->sa_point);
            $total_points = $scope ? $staff->award_points - $points : $staff->award_points + $points;
            $this->db->update('users', ['award_points' => $total_points], ['id' => $saleman]);
        }
        return true;
    }
    public function view_rights($check_id, $js = null)
    {
        if (!$this->Owner && !$this->Admin) {
            if ($check_id != $this->session->userdata('user_id') && !$this->session->userdata('view_right')) {
                $this->session->set_flashdata('warning', $this->data['access_denied']);
                if ($js) {
                    die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome') . "'; }, 10);</script>");
                } else {
                    redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
                }
            }
        }
        return true;
    }

    public function zip($source = null, $destination = './', $output_name = 'bpas', $limit = 5000)
    {
        if (!$destination || trim($destination) == '') {
            $destination = './';
        }

        $this->_rglobRead($source, $input);
        $maxinput  = count($input);
        $splitinto = (($maxinput / $limit) > round($maxinput / $limit, 0)) ? round($maxinput / $limit, 0) + 1 : round($maxinput / $limit, 0);

        for ($i = 0; $i < $splitinto; $i++) {
            $this->_zip(array_slice($input, ($i * $limit), $limit, true), $i, $destination, $output_name);
        }

        unset($input);
        return;
    }

    private function _rglobRead($source, &$array = [])
    {
        if (!$source || trim($source) == '') {
            $source = '.';
        }
        foreach ((array) glob($source . '/*/') as $key => $value) {
            $this->_rglobRead(str_replace('//', '/', $value), $array);
        }
        $hidden_files = glob($source . '.*') and $htaccess = preg_grep('/\.htaccess$/', $hidden_files);
        $files        = array_merge(glob($source . '*.*'), $htaccess);
        foreach ($files as $key => $value) {
            $array[] = str_replace('//', '/', $value);
        }
    }

    private function _zip($array, $part, $destination, $output_name = 'bpas')
    {
        $zip = new ZipArchive;
        @mkdir($destination, 0777, true);

        if ($zip->open(str_replace('//', '/', "{$destination}/{$output_name}" . ($part ? '_p' . $part : '') . '.zip'), ZipArchive::CREATE)) {
            foreach ((array) $array as $key => $value) {
                $zip->addFile($value, str_replace(['../', './'], null, $value));
            }
            $zip->close();
        }
    }

    public function __get($var)
    {
        return get_instance()->$var;
    }

    public function deadlineDayEditing($date, $statusMsg=''){
        if ($statusMsg=='') {
            $statusMsg = "cannot_edit_older_than_x_days";
        }
        if ($this->Settings->disable_editing) {
            if ($date <= date('Y-m-d', strtotime('-'.$this->Settings->disable_editing.' days'))) {
                $this->session->set_flashdata('error', sprintf(lang($statusMsg), $this->Settings->disable_editing));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
    }
    public function formatMoneyPurchase($number)
    {
        if ($this->Settings->sac) {
            return ($this->Settings->display_symbol == 1 ? $this->Settings->symbol : '') .
            $this->formatSAC($this->formatDecimal($number)) .
            ($this->Settings->display_symbol == 2 ? $this->Settings->symbol : '');
        }
        $decimals = $this->Settings->purchase_decimals;
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return ($this->Settings->display_symbol == 1 ? $this->Settings->symbol : '') .
        number_format($number, $decimals, $ds, $ts) .
        ($this->Settings->display_symbol == 2 ? $this->Settings->symbol : '');
    }
    function getExchange_rate($code = "KHR",$date=null)
    {   
        if($date){
            $this->db->where(array('date' => $date));
            $q = $this->db->get('currency_calenders');
        }else{
            $this->db->where(array('code' => $code));
            $q = $this->db->get('currencies');
        }

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllCurrencies()
    {
        $q = $this->db->get('currencies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    function permission_menu($modules){
        //$this->bpas->permission_menu('sales')
        $module  = $this->uri->segment(2);
        $report  = $this->uri->segment(2).'/'.$this->uri->segment(3);
        if($modules =='products'){
            if($module =='products' || 
                $module =='transfers' || 
                $report =='reports/quantity_alerts' ||
                $report =='reports/products' || 
                $report =='reports/cost_report' || 
                $report =='reports/adjustments'){
                return true;
            }
        }elseif ($modules =='purchases') {
            if($module=='purchases' || 
                $module=='suppliers' ||
                $module =='purchases_request' ||
                $module =='purchases_order' ||
                $report =='reports/daily_purchases' ||
                $report =='reports/monthly_purchases' || 
                $report =='reports/purchases' || 
                $report =='reports/expenses' || 
                $report =='reports/suppliers'){
                return true;
            }
        }elseif ($modules =='sales') {
            if($module =='sales' || $module =='pos' || 
                $module =='sales_order' 
                            || $report =='reports/register' || 
                            $report =='reports/daily_sales' ||
                            $report =='reports/monthly_sales' || 
                            $report =='reports/sales' || 
                            $module =='returns_request' || 
                            $module =='returns' || $module =='quotes'
                        ){
                return true;
            }
        }elseif ($modules =='projects') {
            if($module=='projects'){
                return true;
            }
        
        }elseif ($modules =='property') {
            if($module=='property' || 
                            $module=='sale_property' ||
                            $module =='loans' ||
                            $module =='commission' ||
                            $report =='reports/commission' || 
                            $report =='reports/loans'){
                return true;
            }
        }elseif ($modules =='account') {
            if($module =='account' || 
                $report =='reports/payments' ||
                $report =='reports/profit_loss' || 
                $report =='reports/yearly_profit_loss' ||
                $report =='reports/ledger' || 
                $report =='reports/trial_balance' ||
                $report =='reports/balance_sheet' || 
                $report =='reports/income_statement' ||
                $report =='reports/cash_books' || 
                $report =='reports/tax'){
                return true;
            }
        }elseif ($modules =='crm') {
            if($module=='customers' || 
                $report =='reports/customers' ||
                $module=='leads'
                            ){
                return true;
            }
        }elseif ($modules =='hr') {
            if($module=='users' || $module=='billers' || 
                            $report =='reports/users'){
                return true;
            }
        }elseif ($modules =='manufacturing') {
            if($module=='workorder' || 
                $report =='reports/convert_report_detail'|| 
                $report =='reports/workorder_reports' ){
                return true;
            }
        }
        

    }
    function convert_unit_2_string ($_item_code = NULL, $_qty = NULL)
    {
        # $_is_mulit_unit = $_SESSION["multi_unit"];
        $_is_mulit_unit = 1;
        $_if_under_0 = "";
        if ($_is_mulit_unit == 0)
        {
            return $qty;

            exit ();
        }
        $nu = 0;
        if($_qty){
            
            if ($_qty < 0)
            {
                $nu = $_qty;
                //$_if_under_0 = "-";
                $_qty = abs ($_qty);
            }
        }

        if ($_qty == 0) $_qty = "zero";

        if ($_item_code == "" || $_qty === "")
        {
            //exit ("Warning! cannot call convert_unit_2_string($_item_code, $_qty) function.. missing argument, Error: bv00100");
        }
        else
        {
            if ($_qty == "zero") $_qty = 0;


            $_item_code = trim ($_item_code);

            $_units = array ();

            $_select_all_units = $this->site->getUnitUOM($_item_code);
            
            $_max_unit = !empty($_select_all_units) ? count($_select_all_units) : null;

            $_i = 0;
            if (is_array($_select_all_units)){
                foreach ($_select_all_units as $_get_unit)
                {
                    $_unit_description  = $_get_unit->name;
                    $_unit_qty          = $_get_unit->qty_unit;

                    /*

                        Syntax:

                        A                           B                               C                           D
                        10                          5                               1                           568
                        D / A = AX                  XA / B = BX                     XB / C = CX
                        D - (AX * A) = XA           XA - (BX * B) = XB              XB - (CX * C) = XC

                        568 / 10 = 56 (8)           8 / 5 = 1 (4)                   4 / 1 = 4 (0)
                        568 - (56 * 10) = 8         8 - (1 * 5) = 4                 4 - (4 * 1) = 0

                                                                                                                7834663
                        7834663 / 50 = 156693
                        7834663 - (156693 * 50) = 13

                        13 / 10 = 1
                        13 - (10 * 1) = 3

                        3



                        10000 g = 10 kg
                        - unit = Ton = 1 000 000 g

                        - 10 000 / 1 000 000

                        if 10 000 < 1 000 000


                    */

                    if ($_qty <= 0) break;

                    if ((($_qty) < $_unit_qty) || $_i == $_max_unit)
                    {
                        if ($_qty < $_unit_qty) continue;

                        $_units[] = "$_qty <span style='color: #178228;'>$_unit_description x</span>";

                        # break;
                    }
                    else
                    {
                        # D / A = AX
                        $_qtyx = (int) ($_qty / $_unit_qty);
                        $_units[] = "$_qtyx <span style='color: #178228;'>$_unit_description</span>";

                        # D - (AX * A) = XA
                        $_xqty = $_qty - ($_qtyx * $_unit_qty);

                        #
                        $_qty = $_xqty;
                    }
                }
            }
            $_string_unit = $this->array_2_string(", ", $_units);
            if(empty($_select_all_units)){
                $_string_unit = '1 <span style="color: #178228;">' . $this->site->getUnitNameByProId($_item_code) .'</span>';
            }
            
            $en = "";
            if($_string_unit){
                if ($nu < 0)
                {
                    $_if_under_0 = "- (";
                    $en = ")";
                }else{
                    $_if_under_0 = "(";
                    $en = ")";
                }
            }
            
            return "$_if_under_0 $_string_unit $en";
        }

        # how to use:
        # echo convert_unit_2_string ("CAT4TST-00001", 7834663);
    }
    
    function convert_unit_2_string1 ($_item_code = NULL, $_qty = NULL)
    {
        # $_is_mulit_unit = $_SESSION["multi_unit"];
        $_is_mulit_unit = 1;
        $_if_under_0 = "";
        if ($_is_mulit_unit == 0)
        {
            return $qty;

            exit ();
        }
        $nu = 0;
        if($_qty){
            
            if ($_qty < 0)
            {
                $nu = $_qty;
                //$_if_under_0 = "-";
                $_qty = abs ($_qty);
            }
        }

        if ($_qty == 0) $_qty = "zero";

        if ($_item_code == "" || $_qty === "")
        {
            //exit ("Warning! cannot call convert_unit_2_string($_item_code, $_qty) function.. missing argument, Error: bv00100");
        }
        else
        {
            if ($_qty == "zero") $_qty = 0;


            $_item_code = trim ($_item_code);

            $_units = array ();

            $_select_all_units = $this->site->getUnitUOM($_item_code);
            
            
            
            $_max_unit = count($_select_all_units);

            $_i = 0;
            if (is_array($_select_all_units)){
                foreach ($_select_all_units as $_get_unit)
                {
                    $_unit_description = $_get_unit->name;
                    $_unit_qty = $_get_unit->qty_unit;
                    if ($_qty <= 0) break;

                    if ((($_qty) < $_unit_qty) || $_i == $_max_unit)
                    {
                        if ($_qty < $_unit_qty) continue;

                        $_units[] = "$_qty $_unit_description x";

                        # break;
                    }
                    else
                    {
                        # D / A = AX
                        $_qtyx = (int) ($_qty / $_unit_qty);
                        $_units[] = "$_qtyx $_unit_description";

                        # D - (AX * A) = XA
                        $_xqty = $_xqty = $this->bpas->formatPurDecimal($_qty) - $this->bpas->formatPurDecimal($_qtyx * $_unit_qty);

                        #
                        $_qty = $_xqty;
                    }
                }
            }
            $_string_unit = $this->array_2_string (", ", $_units);
            $en = "";
            if($_string_unit){
                if ($nu < 0)
                {
                    $_if_under_0 = "- (";
                    $en = ")";
                }else{
                    $_if_under_0 = "(";
                    $en = ")";
                }
            }
            
            return "$_if_under_0 $_string_unit $en";
        }

        # how to use:
        # echo convert_unit_2_string ("CAT4TST-00001", 7834663);
    }
    
    function convert_unit_by_variant ($_item_code = NULL, $_qty = NULL)
    {
        $_is_mulit_unit = 1;
        $_if_under_0 = "";
        if ($_is_mulit_unit == 0)
        {
            return $qty;

            exit ();
        }
        $nu = 0;
        if($_qty){
            
            if ($_qty < 0)
            {
                $nu = $_qty;
                $_qty = abs ($_qty);
            }
        }

        if ($_qty == 0) $_qty = "zero";

        if ($_item_code == "" || $_qty === "")
        {
            //exit ("Warning! cannot call convert_unit_2_string($_item_code, $_qty) function.. missing argument, Error: bv00100");
        }
        else
        {
            if ($_qty == "zero") 
                
            $_qty = 0;
            $_item_code = trim($_item_code);
            $_units = array ();
            $_select_all_units = $this->site->getUnitUOM($_item_code);
            $_max_unit = count($_select_all_units);
            $_i = 0;
            if (is_array($_select_all_units)){
                foreach ($_select_all_units as $_get_unit)
                {
                    $_unit_description = $_get_unit->name;
                    $_unit_qty = $_get_unit->qty_unit;
                    if ($_qty <= 0) break;

                    if ((($_qty) < $_unit_qty) || $_i == $_max_unit)
                    {
                        if ($_qty < $_unit_qty) continue;
                        $_units[$_unit_description] = "$_qty";
                    }
                    else
                    {
                        $_qtyx = (int) ($_qty / $_unit_qty);
                        $_units[$_unit_description] = "$_qtyx";
                        $_xqty = $_qty - ($_qtyx * $_unit_qty);
                        $_qty = $_xqty;
                    }
                }
            }
            $_units;
            return $_units;
        }
    }
    
    function array_2_string($sep = "-", $_data, $_prefix = "", $_suffix = "")
    {
        if ($_prefix != "" AND $_suffix != "")
        {
            $_string = array ();

            foreach ($_data AS $_value)
            {
                $_string[] = $_prefix . $_value . $_suffix;
            }
        }

        else $_string = $_data;

        return implode ("$sep", array_filter ($_string));
    }
    
    function convert_unit_2_string_by_unit ($_item_code = NULL, $_qty = NULL)
    {
        # $_is_mulit_unit = $_SESSION["multi_unit"];
        $_is_mulit_unit = 1;

        if ($_is_mulit_unit == 0)
        {
            return $qty;

            exit ();
        }

        if ($_qty < 0)
        {
            $_if_under_0 = "-";
            $_qty = abs ($_qty);
        }

        if ($_qty == 0) $_qty = "zero";

        if ($_item_code == "" || $_qty === "")
        {
            //exit ("Warning! cannot call convert_unit_2_string($_item_code, $_qty) function.. missing argument, Error: bv00100");
        }
        else
        {
            if ($_qty == "zero") $_qty = 0;


            $_item_code = trim ($_item_code);

            $_units = array ();

            $_select_all_units = $this->site->getUnitUOM($_item_code);
            
            
            
            $_max_unit = count($_select_all_units);

            $_i = 0;

            foreach ($_select_all_units as $_get_unit)
            {
                $_unit_description = $_get_unit->name;
                $_unit_qty         = $_get_unit->qty_unit;
                $_cost             =  ($_get_unit->qty_unit * $_get_unit->pcost );
                $_price            = $_get_unit->price;

                /*

                    Syntax:

                    A                           B                               C                           D
                    10                          5                               1                           568
                    D / A = AX                  XA / B = BX                     XB / C = CX
                    D - (AX * A) = XA           XA - (BX * B) = XB              XB - (CX * C) = XC

                    568 / 10 = 56 (8)           8 / 5 = 1 (4)                   4 / 1 = 4 (0)
                    568 - (56 * 10) = 8         8 - (1 * 5) = 4                 4 - (4 * 1) = 0

                                                                                                            7834663
                    7834663 / 50 = 156693
                    7834663 - (156693 * 50) = 13

                    13 / 10 = 1
                    13 - (10 * 1) = 3

                    3



                    10000 g = 10 kg
                    - unit = Ton = 1 000 000 g

                    - 10 000 / 1 000 000

                    if 10 000 < 1 000 000


                */





                if ($_qty <= 0) break;

                if ((($_qty) < $_unit_qty) || $_i == $_max_unit)
                {
                    if ($_qty < $_unit_qty) continue;

                    $_units[] = "$_qty <span style='color: #178228;'>$_unit_description x</span>";

                    # break;
                }
                else
                {
                    # D / A = AX
                    $_qtyx = (int) ($_qty / $_unit_qty);
                    $_units[] = "<tr><td>$_unit_description</td><td >".$this->formatQuantity($_qtyx)."</td>".($this->Owner || $this->Admin ?"<td>".$this->formatMoney($_cost)."</td><td>".$this->formatMoney($_price)."</td>":"")."</tr>";

                    # D - (AX * A) = XA
                    $_xqty = $_qty - ($_qtyx * $_unit_qty);

                    #
                    $_qty = $_xqty;
                }
            }

            $_string_unit = $this->array_2_string ("", $_units);

            if ($_qty < 0) {
                return "$_if_under_0";
            }
            return "$_string_unit";
        }

        # how to use:
        # echo convert_unit_2_string ("CAT4TST-00001", 7834663);
    }
    
    public function formatPercentage($percent)
    {
        $per        = explode('.', $percent);

        $percentage = 0;
        if($per[0] > 0){
            $percentage = $this->bpas->formatDecimal($per[0].'.'.$per[0]);
        }else{
            $percentage = $per[0];
        }
        return $percentage;
    }
    
    public function multiCurrFormular($curr_code, $amount)
    {
        # Query Curency Detail by Code
        $currency       = $this->site->getCurrencyByCode($curr_code);
        
        # Get Setting Rate
        $setting_code   = $this->Settings->default_currency;
        $setting_curr   = $this->site->getCurrencyByCode($setting_code);
        
        # Calculate Formular
        $result = ($amount/$currency->rate)*$setting_curr->rate;
        
        # Return Result
        return $result;
    }
    
    function numberToWords ($number,$kh=''){
        if (($number < 0) || ($number > 999999999))
        {
            //throw new Exception("Number is out of range");
            return  "Number is out of range";
        }

        $Gn = floor($number / 1000000);  /* Millions (giga) */
        $number -= $Gn * 1000000;
        $kn = floor($number / 1000);     /* Thousands (kilo) */
        $number -= $kn * 1000;
        $Hn = floor($number / 100);      /* Hundreds (hecto) */
        $number -= $Hn * 100;
        $Dn = floor($number / 10);       /* Tens (deca) */
        $n = $number % 10;               /* Ones */

        $res = "";

        if ($Gn)
        {
            $res .= $this->numberToWords ($Gn,$kh) . ($kh==""?" Million":"លាន");
        }

        if ($kn)
        {
            $res .= (empty($res) ? "" : " ") .
                $this->numberToWords ($kn,$kh) . ($kh==""?" Thousand":"ពាន់");
        }

        if ($Hn)
        {
            $res .= (empty($res) ? "" : " ") .
                $this->numberToWords ($Hn,$kh) . ($kh==""?" Hundred":"រយ");
        }

        $ones = array("", "One", "Two", "Three", "Four", "Five", "Six",
            "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen",
            "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eightteen",
            "Nineteen");
        $tens = array("", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty",
            "Seventy", "Eigty", "Ninety");

        $oneskh = array("", "មួយ", "ពីរ", "បី", "បួន", "ប្រាំ", "ប្រាំមួយ",
            "ប្រាំពីរ", "ប្រាំបី", "ប្រាំបួន", "ដប់", "ដប់មួយ", "ដប់ពីរ", "ដប់បី",
            "ដប់បួន", "ដប់ប្រាំ", "ដប់ប្រាំមួយ", "ដប់ប្រាពីរ", "ដប់ប្រាំបី",
            "ដប់ប្រាំបួន");
        $tenskh = array("", "", "ម្ភៃ", "សាមសិប", "សែសិប", "ហាសិប", "ហុកសិប",
            "ចិតសិប", "ប៉ែតសិប", "កៅសិប");

        if ($Dn || $n)
        {
            if (!empty($res))
            {

                $res .= ($fpont>0?" ":($kh==""?" ":""));
            }

            if ($Dn < 2)
            {
                $res .= ($kh==""?$ones[$Dn * 10 + $n]:$oneskh[$Dn * 10 + $n]);
            }
            else
            {
                $res .= ($kh==""?$tens[$Dn]:$tenskh[$Dn]);

                if ($n)
                {
                    $res .= ($kh==""?"-".$ones[$n]:$oneskh[$n]);
                }
            }
        }

        if (empty($res))
        {
            $res = ($kh==""?"zero":"សូន្យ");
        }

        return $res;
    }

    function numberToWordsCur ($numberf,$kh='',$cur="US Dollars",$cur_h = " សេន") {
        $numberf = round($numberf,2);
        $arr = explode('.',$numberf);
        $number = $arr[0]-0;
//      $fpont =  ($arr[1]-0 ? $arr[1]-0 : 0);

        $f = '';
//      if($fpont>0){
//          $fpont = str_pad($fpont,2,'0',STR_PAD_RIGHT)-0;
//          $f = ($kh==""?" and ":"​ និង ").$this->numberToWords($fpont,$kh).$cur_h;
//      }
        $res = $this->numberToWords($number,$kh).' '.$cur;

        return $res.$f;
    }
    public function numberToKhmer($number = false)
    {
        $numbers = str_split($number);
        $khmer_numbers = array('0'=>'០','1'=>'១','2'=>'២','3'=>'៣','4'=>'៤','5'=>'៥','6'=>'៦','7'=>'៧','8'=>'៨','9'=>'៩');
        if($numbers){
            $khmer_number = '';
            foreach($numbers as $number){
                $khmer_number .= $khmer_numbers[$number];
            }
            return $khmer_number;
        }
        return false;
    }
    
    public function numberToMonth($number = false)
    {
        $khmer_months = array('01'=>lang('jan'),'02'=>lang('feb'),'03'=>lang('mar'),'04'=>lang('apr'),'05'=>lang('may'),'06'=>lang('jun'),'07'=>lang('jul'),'08'=>lang('aug'),'09'=>lang('sep'),'10'=>lang('oct'),'11'=>lang('nov'),'12'=>lang('dec'));
        if($khmer_months[$number]){
            return $khmer_months[$number];
        }
        return false;
    }
    
    public function numberToKhmerMonth($number = false)
    {
        $khmer_months = array('01'=>'មករា','02'=>'កុម្ភៈ','03'=>'មីនា','04'=>'មេសា','05'=>'ឧសភា','06'=>'មិថុនា','07'=>'កក្កដា','08'=>'សីហា','09'=>'កញ្ញា','10'=>'តុលា','11'=>'វិច្ឆិកា','12'=>'ធ្នូ');
        if(isset($khmer_months[$number])){
            return $khmer_months[$number];
        }
        return false;
    }
    
    public function convertKhmerDate($date = false){
        $date = explode('-',$date);
        if($date){
            $day = $this->numberToKhmer($date[2]);
            $month = $this->numberToKhmerMonth($date[1]);
            $year = $this->numberToKhmer($date[0]);         
            $khmer_date = $day.' '.$month.' '.$year;
            return $khmer_date;
        }
        return false;   
    }
    
    public function dateToKhmerDate($date = false)
    {
        $date = explode('/',$date);
        if($date){
            $day = $this->numberToKhmer($date[0]);
            $month = $this->numberToKhmerMonth($date[1]);
            $year = $this->numberToKhmer($date[2]);         
            $khmer_date = 'ថ្ងៃទី'.$day.' ខែ'.$month.' ឆ្នាំ'.$year;
            return $khmer_date;
        }
        return false;   
    }
    public function convertNumberToWords($num){
        $array = explode(',',$num);
        $str_data = implode("", $array);

        return $this->number_to_word($str_data);
    }
    public function convertNumberToKhWords($num){
        $array = explode(',',$num);
        $str_data = implode("", $array);

        return $this->number_to_word_kh($str_data);
    }
    public function number_to_word( $num = '' )
    {
        $num    = ( string ) ( ( int ) $num );
       
        if( ( int ) ( $num ) && ctype_digit( $num ) )
        {
            $words  = array( );
           
            $num    = str_replace( array( ',' , ' ' ) , '' , trim( $num ) );
           
            $list1  = array('','one','two','three','four','five','six','seven',
                'eight','nine','ten','eleven','twelve','thirteen','fourteen',
                'fifteen','sixteen','seventeen','eighteen','nineteen');
           
            $list2  = array('','ten','twenty','thirty','forty','fifty','sixty',
                'seventy','eighty','ninety','hundred');
           
            $list3  = array('','thousand','million','billion','trillion',
                'quadrillion','quintillion','sextillion','septillion',
                'octillion','nonillion','decillion','undecillion',
                'duodecillion','tredecillion','quattuordecillion',
                'quindecillion','sexdecillion','septendecillion',
                'octodecillion','novemdecillion','vigintillion');
           
            $num_length = strlen( $num );
            $levels = ( int ) ( ( $num_length + 2 ) / 3 );
            $max_length = $levels * 3;
            $num    = substr( '00'.$num , -$max_length );
            $num_levels = str_split( $num , 3 );
           
            foreach( $num_levels as $num_part )
            {
                $levels--;
                $hundreds   = ( int ) ( $num_part / 100 );
                $hundreds   = ( $hundreds ? ' ' . $list1[$hundreds] . ' Hundred' . ( $hundreds == 1 ? '' : 's' ) . ' ' : '' );
                $tens       = ( int ) ( $num_part % 100 );
                $singles    = '';
               
                if( $tens < 20 ) { $tens = ( $tens ? ' ' . $list1[$tens] . ' ' : '' ); } else { $tens = ( int ) ( $tens / 10 ); $tens = ' ' . $list2[$tens] . ' '; $singles = ( int ) ( $num_part % 10 ); $singles = ' ' . $list1[$singles] . ' '; } $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_part ) ) ? ' ' . $list3[$levels] . ' ' : '' ); } $commas = count( $words ); if( $commas > 1 )
            {
                $commas = $commas - 1;
            }
           
            $words  = implode( ', ' , $words );
           
            //Some Finishing Touch
            //Replacing multiples of spaces with one space
            $words  = trim( str_replace( ' ,' , ',' , trim( ucwords( $words ) ) ) , ', ' );
            if( $commas )
            {
                $words  = str_replace( ',' , ' and' , $words );
            }
           
            return $words;
        }
        else if( ! ( ( int ) $num ) )
        {
            return 'Zero';
        }
        return '';
    }

    public function number_to_word_kh( $num = '' )
    {
        $num    = ( string ) ( ( int ) $num );
       
        if( ( int ) ( $num ) && ctype_digit( $num ) )
        {
            $words  = array( );
           
            $num    = str_replace( array( ',' , ' ' ) , '' , trim( $num ) );
           
            $list1  = array('','មួយ','ពីរ','បី','បួន','ប្រាំ','ប្រាំមួយ','ប្រាំពីរ',
                'ប្រាំបី','ប្រាំបួន','ដប់','ដប់មួយ','ដប់ពីរ','ដប់បី','ដប់បួន',
                'ដប់ប្រាំ','ដប់ប្រាំមួយ','ដប់ប្រាំពីរ','ដប់ប្រាំបី','ដប់ប្រាំបួន');
           
            $list2  = array('','ដប់','ម្ភៃ','សាមសិប','សែសិប','ហាសិប','ហុកសិប',
                'ចិតសិប','ប៉ែតសិប','កៅសិប','មួយរយ');
           
            $list3  = array('','ពាន់','លាន','ប៊ីលាន','ទ្រីលាន');
           
            $num_length = strlen( $num );
            $levels = ( int ) ( ( $num_length + 2 ) / 3 );
            $max_length = $levels * 3;
            $num    = substr( '00'.$num , -$max_length );
            $num_levels = str_split( $num , 3 );
           
            foreach( $num_levels as $num_part )
            {
                $levels--;
                $hundreds   = ( int ) ( $num_part / 100 );
                $hundreds   = ( $hundreds ? ' ' . $list1[$hundreds] . ' រយ' . ( $hundreds == 1 ? '' : '' ) . ' ' : '' );
                $tens       = ( int ) ( $num_part % 100 );
                $singles    = '';
               
                if( $tens < 20 ) { $tens = ( $tens ? ' ' . $list1[$tens] . ' ' : '' ); } else { $tens = ( int ) ( $tens / 10 ); $tens = ' ' . $list2[$tens] . ' '; $singles = ( int ) ( $num_part % 10 ); $singles = ' ' . $list1[$singles] . ' '; } $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_part ) ) ? ' ' . $list3[$levels] . ' ' : '' ); } $commas = count( $words ); if( $commas > 1 )
            {
                $commas = $commas - 1;
            }
           
            $words  = implode( ', ' , $words );
           
            //Some Finishing Touch
            //Replacing multiples of spaces with one space
            $words  = trim( str_replace( ' ,' , ',' , trim( ucwords( $words ) ) ) , ', ' );
            if( $commas )
            {
                $words  = str_replace( ',' , '​ និង' , $words );
            }
           
            return $words;
        }
        else if( ! ( ( int ) $num ) )
        {
            return 'សូន្យ';
        }
        return '';
    }
    
    public function remove_tags($str){
        return str_replace(['<p>', '</p>'], ' ', $str);
    }
    public function formatMonth($date)
    {
        $format = array(
            'January' => 'មករា',
            'February' => 'កុម្ភៈ',
            'March' => 'មីនា',
            'April' => 'មេសា',
            'May' => 'ឧសភា',
            'June' => 'មិថុនា',
            'July' => 'កក្កដា',
            'August' => 'សីហា',
            'September' => 'កញ្ញា',
            'October' => 'តុលា',
            'November' => 'វិច្ឆិកា',
            'December' => 'ធ្នូ',
        );

        //$new_date = explode('-',$date);
        $new_date = $format[$date];
        //$new_date = implode('-',$new_date);

        return $new_date;
    }
    public function ills()
    {
        
        $opts =array(
            ''      => 'None',
            'ill1' => 'អាឡែកស៊ីថ្នាំ', 
            'ill2'=>'ជម្ងឺបេះដូង',
            'ill3'=>'មានផ្ទៃពោះ',
            'ill4'=>'ទឹកនោមផ្អែម',
            'ill5'=>'លើសឈាម'
        );
        return $opts;
    }
    public function convertMonthToLatang($month,$year){
        if($month == 'January'){
            $NewMonth = '01';
        }elseif ($month == 'February'){
            $NewMonth = '02';
        }elseif ($month == 'March'){
            $NewMonth = '03';
        }elseif ($month == 'April'){
            $NewMonth = '04';
        }elseif ($month == 'May'){
            $NewMonth = '05';
        }elseif ($month == 'June'){
            $NewMonth = '06';
        }elseif ($month == 'July'){
            $NewMonth = '07';
        }elseif ($month == 'August'){
            $NewMonth = '08';
        }elseif ($month == 'September'){
            $NewMonth = '09';
        }elseif ($month == 'October'){
            $NewMonth = 10;
        }elseif ($month == 'November'){
            $NewMonth =11;
        }else{
            $NewMonth =12;
        }
        $a_date = $year.'-'.$NewMonth.'-01';

        return date("Y-m-t", strtotime($a_date));
    }
    
    function convertQty($product_id = false, $quantity = false, $unit_id = false)
    {
        // $units = $this->site->getProductUnitByProduct($product_id);
        $units = $this->site->getUnitsDetailsByProduct($product_id, $unit_id);
        if ($units) {
            $i           = 1;
            $operation   = '';
            $unit_string = '';
            if ($quantity < 0) {
                $quantity = abs($quantity);
                $operation = '-';
            }
            if ($quantity < 1) { return $quantity; }
            foreach ($units as $unit) {
                if ($quantity >= $unit->unit_qty) {
                    if ($i > 1) { $unit_string .= (', ' . PHP_EOL); }
                    if ($unit->unit_qty == 1) {
                        $quantity_unit = ($quantity / $unit->unit_qty);
                    } else {
                        $quantity_unit = (int) ($quantity / $unit->unit_qty);
                    }
                    $unit_string .=  $this->formatQuantity($quantity_unit).' <span style="color:#357EBD;">'.$unit->name.'</span>';
                    $quantity = $quantity - ($quantity_unit * $unit->unit_qty);
                    $i++;
                }
            }
            return $operation.$unit_string;
        } else {
            return $quantity;
        }
    }

    function convertUnit($product_id = false, $quantity = false, $price = false, $unit_id = false)
    {
        $product = $this->site->getProductByID($product_id);
        // $units   = $this->site->getProductUnitByProduct($product_id);
        $units   = $this->site->getUnitsDetailsByProduct($product_id, $unit_id);
        if($units){
            $unit_string = '';
            $operation = '';
            if($quantity < 0){
                $quantity = abs($quantity);
                $operation = '-';
            }
            foreach($units as $unit) {
                if($quantity >= $unit->unit_qty && ($quantity % $unit->unit_qty) == 0){
                    $unit_qty   = $quantity / $unit->unit_qty;
                    $unit_price = $price * $unit->unit_qty;
                    return array('quantity' => ($operation.$unit_qty), 'unit_id' => $unit->id, 'price' => $unit_price);
                }
            }
        } else {
            return array('quantity' => $quantity, 'unit_id' => $product->unit, 'price' => $price);
        }
    }

    public function remove_tag($str = false)
    {
        return strip_tags(html_entity_decode($str));
    }

    function format_interval(DateInterval $interval) {
        $result = "";
        if ($interval->y) { $result .= $interval->format(" %y" . "y "); }
        if ($interval->m) { $result .= $interval->format(" %m" . "m "); }
        if ($interval->d) { $result .= $interval->format(" %d" . "d "); }
        if ($interval->h) { $result .= $interval->format(" %h" . "h "); }
        if ($interval->i) { $result .= $interval->format(" %i" . "min "); }
        if ($interval->s) { $result .= $interval->format(" %s" . "s "); } 
        return $result;
    }
    function title($breadcrumb) {
        foreach ($breadcrumb as $b) {
            if ($b['link'] === '#') {
                echo '<span>' . $b['page'] . '</span>';
            } else {
                echo '<span><a href="' . $b['link'] . '">' . $b['page'] . '</a></span> / ';
            }
        } 
    }
    public function secTotime($number = false,$format = '%02d:%02d:%02d')
    {
        if($number > 0){
            $hour = floor($number / 3600);
            $minute = floor($number / 60 % 60);
            $second = floor($number % 60);
            return sprintf($format, $hour, $minute, $second);
        }
        return '';
    }
    public function round_time($round_min = false,$minimum_min = false,$actual_time = false)
    {
        // actual_time 6min = 360 s
        if($round_min > 0 && $minimum_min > 0 && $actual_time > 0){
            $round_min = $round_min * 60; //5min = 300s
            $minimum_min = $minimum_min * 60; //15min = 900s
            $cut = ((int)($actual_time / $minimum_min)) * $minimum_min; //120d
            $over = $actual_time - $cut; // 360 - 120 = 240
            if($over >= $round_min){ // 240 >= 300 
                $round_time = $cut + $minimum_min;
            }else{
                $round_time =  $cut;
            }
            return $round_time;
            
        }
        return $actual_time;
    }
    public function fsdkh($inv_date_)
    {
        if ($inv_date_) {

            $date_     = explode(' ', $inv_date_);
            $inv_date = $date_[0] ? $date_[0] : $date_; 
            $jsd      = $this->dateFormats['js_sdate'];
            $time     = sizeof($date_) > 1 ? $date_[1] : "";

            if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                $date =  substr($inv_date, -2). '&nbsp;' . $this->formatMonthNumber(substr($inv_date, 5, 2)) . '&nbsp;' . substr($inv_date, 0,4);
            } elseif ($jsd == 'mm-dd-yyyy' || $jsd == 'mm/dd/yyyy' || $jsd == 'mm.dd.yyyy') {
                $date =  substr($inv_date, -2). '' . $this->formatMonthNumber(substr($inv_date, 5, 2)) . '&nbsp;' . substr($inv_date, 0,4);
            } else {
                $date = $inv_date_ .'&nbsp;';
            }
            return $date;
        } else {
            return '0000-00-00';
        }
    }
     public function fldkh($inv_date_)
    {
        if ($inv_date_) {

            $date_     = explode(' ', $inv_date_);
            $inv_date = $date_[0] ? $date_[0] : $date_; 
            $jsd      = $this->dateFormats['js_sdate'];
            $time     = sizeof($date_) > 1 ? $date_[1] : "";

            if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                $date =  substr($inv_date, -2). '&nbsp;' . $this->formatMonthNumber(substr($inv_date, 5, 2)) . '&nbsp;' . substr($inv_date, 0,4) .'&nbsp;'. $time;
            } elseif ($jsd == 'mm-dd-yyyy' || $jsd == 'mm/dd/yyyy' || $jsd == 'mm.dd.yyyy') {
                $date =  substr($inv_date, -2). '' . $this->formatMonthNumber(substr($inv_date, 5, 2)) . '&nbsp;' . substr($inv_date, 0,4) .'&nbsp;'. $time;
            } else {
                $date = $inv_date_ .'&nbsp;'. $time;
            }
            return $date;
        } else {
            return '0000-00-00';
        }
    }
      public function formatfullMonth($date){
        $format = array(
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        );
        //$new_date = explode('-',$date);
        $new_date = $format[$date];
        //$new_date = implode('-',$new_date);

        return $new_date;
    }
     public function formatMonthNumber($date)
    {
        $format = array(
            '01' => 'មករា',
            '02' => 'កុម្ភៈ',
            '03' => 'មីនា',
            '04' => 'មេសា',
            '05' => 'ឧសភា',
            '06' => 'មិថុនា',
            '07' => 'កក្កដា',
            '08' => 'សីហា',
            '09' => 'កញ្ញា',
            '10' => 'តុលា',
            '11' => 'វិច្ឆិកា',
            '12' => 'ធ្នូ',
        );

        //$new_date = explode('-',$date);
        $new_date = $format[$date];
        //$new_date = implode('-',$new_date);

        return $new_date;
    }
  
    public function formatshortMonth($date){
        $format = array(
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'May',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Aug',
            '09' => 'Sep',
            '10' => 'Oct',
            '11' => 'Nov',
            '12' => 'Dec',
        );

        //$new_date = explode('-',$date);
        $new_date = $format[$date];
        //$new_date = implode('-',$new_date);

        return $new_date;
    }
     function getDayNameKh($dayOfWeek_) {
        $dayOfWeek = date('w', strtotime($dayOfWeek_));
        switch ($dayOfWeek){
            case 6:
                return 'សៅរ៏ ';
            case 0:
                return 'អាទិត្យ ';
            case 1:
                return 'ច័ន្ទ ';
            case 2:
                return 'អង្គារ ';
            case 3:
                return 'ពុធ ';
            case 4:
                return 'ព្រហ ';
            case 5:
                return 'សៅរ៏ ';
            default:
                return '';
        }

    }
    function getDayName($dayOfWeek_) {
        $dayOfWeek = date('w', strtotime($dayOfWeek_));
        switch ($dayOfWeek){
            case 6:
                return 'Saturday ';
            case 0:
                return 'Sunday ';
            case 1:
                return 'Monday ';
            case 2:
                return 'Tuesday ';
            case 3:
                return 'Wednesday ';
            case 4:
                return 'Thursday ';
            case 5:
                return 'Friday ';
            default:
                return ''; 
        }
    }
    public function set_date_format($date) {
		
		// get details
		// $system_setting = $this->site->getSettings();
        // var_dump($system_setting);
        $jsd      = $this->dateFormats['js_sdate'];
		// date formate
		if( $jsd =='d-m-Y'){
			$d_format = date("d-m-Y", strtotime($date));
		} else if( $jsd =='m-d-Y'){
			$d_format = date("m-d-Y", strtotime($date));
		} else if( $jsd =='d-M-Y'){
			$d_format = date("d-M-Y", strtotime($date));
		} else if( $jsd =='M-d-Y'){
			$d_format = date("M-d-Y", strtotime($date));
		} else if( $jsd =='F-j-Y'){
			$d_format = date("F-j-Y", strtotime($date));
		} else if( $jsd =='j-F-Y'){
			$d_format = date("j-F-Y", strtotime($date));
		} else if( $jsd =='m.d.y'){
			$d_format = date("m.d.y", strtotime($date));
		} else if( $jsd =='d.m.y'){
			$d_format = date("d.m.y", strtotime($date));
		} else {
			$d_format =  $jsd      = $this->dateFormats['js_sdate'];;
		}
		
		return $d_format;
	}
    function seperateBankNote($amount = false,$kh_rate = false){
        if($amount){
            $total_usd = 0;
            $total_khr = 0;
            $usd_100 = 0;
            $usd_50 = 0;
            $usd_20 = 0;
            $usd_10 = 0;
            $khr_20000 = 0;
            $khr_10000 = 0;
            $khr_5000 = 0;
            $khr_2000 = 0;
            $khr_1000 = 0;
            $khr_500 = 0;
            $khr_100 = 0;
            
            if($amount >= 100){
                $usd_100 = (int)($amount / 100);
                $amount -= ($usd_100 * 100);
                $total_usd += ($usd_100 * 100);
            }
            if($amount >= 50){
                $usd_50 = (int)($amount / 50);
                $amount -= ($usd_50 * 50);
                $total_usd += ($usd_50 * 50);
            }
            if($amount >= 20){
                $usd_20 = (int)($amount / 20);
                $amount -= ($usd_20 * 20);      
                $total_usd += ($usd_20 * 20);
            }
            if($amount >= 10){
                $usd_10 = (int)($amount / 10);
                $amount -= ($usd_10 * 10);      
                $total_usd += ($usd_10 * 10);
            }
            
            if($amount){
                if(!$kh_rate){
                    $kh_rate = $this->site->getCurrencyByCode("KHR")->rate;
                }
                $amount = $amount * $kh_rate;
                if($amount >= 20000){
                    $khr_20000 = (int)($amount / 20000);
                    $amount -= ($khr_20000 * 20000);    
                    $total_khr += ($khr_20000 * 20000);
                }
                if($amount >= 10000){
                    $khr_10000 = (int)($amount / 10000);
                    $amount -= ($khr_10000 * 10000);
                    $total_khr += ($khr_10000 * 10000);
                }
                if($amount >= 5000){
                    $khr_5000 = (int)($amount / 5000);
                    $amount -= ($khr_5000 * 5000);
                    $total_khr += ($khr_5000 * 5000);
                }
                if($amount >= 2000){
                    $khr_2000 = (int)($amount / 2000);
                    $amount -= ($khr_2000 * 2000);  
                    $total_khr += ($khr_2000 * 2000);
                }
                if($amount >= 1000){
                    $khr_1000 = (int)($amount / 1000);
                    $amount -= ($khr_1000 * 1000);
                    $total_khr += ($khr_1000 * 1000);   
                }
                if($amount >= 500){
                    $khr_500 = (int)($amount / 500);
                    $amount -= ($khr_500 * 500);
                    $total_khr += ($khr_500 * 500); 
                }
                if($amount >= 100){
                    $khr_100 = (int)($amount / 100);
                    $total_khr += ($khr_100 * 100); 
                }
            }
            return array("total_usd" => $total_usd,"total_khr" => $total_khr,"usd_100" => $usd_100,"usd_50" => $usd_50,"usd_20" => $usd_20,"usd_10" => $usd_10,"khr_20000" => $khr_20000,"khr_10000" => $khr_10000,"khr_5000" => $khr_5000,"khr_2000" => $khr_2000,"khr_1000" => $khr_1000,"khr_500" => $khr_500,"khr_100" => $khr_100);
        }
        return false;
    }
    function getAllCountry(){
        $countryList = array(
            ''   => lang('please_selected'),
            "AF" => "Afghanistan",
            "AL" => "Albania",
            "DZ" => "Algeria",
            "AS" => "American Samoa",
            "AD" => "Andorra",
            "AO" => "Angola",
            "AI" => "Anguilla",
            "AQ" => "Antarctica",
            "AG" => "Antigua and Barbuda",
            "AR" => "Argentina",
            "AM" => "Armenia",
            "AW" => "Aruba",
            "AU" => "Australia",
            "AT" => "Austria",
            "AZ" => "Azerbaijan",
            "BS" => "Bahamas",
            "BH" => "Bahrain",
            "BD" => "Bangladesh",
            "BB" => "Barbados",
            "BY" => "Belarus",
            "BE" => "Belgium",
            "BZ" => "Belize",
            "BJ" => "Benin",
            "BM" => "Bermuda",
            "BT" => "Bhutan",
            "BO" => "Bolivia",
            "BA" => "Bosnia and Herzegovina",
            "BW" => "Botswana",
            "BV" => "Bouvet Island",
            "BR" => "Brazil",
            "BQ" => "British Antarctic Territory",
            "IO" => "British Indian Ocean Territory",
            "VG" => "British Virgin Islands",
            "BN" => "Brunei",
            "BG" => "Bulgaria",
            "BF" => "Burkina Faso",
            "BI" => "Burundi",
            "KH" => "Cambodia",
            "CM" => "Cameroon",
            "CA" => "Canada",
            "CT" => "Canton and Enderbury Islands",
            "CV" => "Cape Verde",
            "KY" => "Cayman Islands",
            "CF" => "Central African Republic",
            "TD" => "Chad",
            "CL" => "Chile",
            "CN" => "China",
            "CX" => "Christmas Island",
            "CC" => "Cocos [Keeling] Islands",
            "CO" => "Colombia",
            "KM" => "Comoros",
            "CG" => "Congo - Brazzaville",
            "CD" => "Congo - Kinshasa",
            "CK" => "Cook Islands",
            "CR" => "Costa Rica",
            "HR" => "Croatia",
            "CU" => "Cuba",
            "CY" => "Cyprus",
            "CZ" => "Czech Republic",
            "CI" => "Côte d’Ivoire",
            "DK" => "Denmark",
            "DJ" => "Djibouti",
            "DM" => "Dominica",
            "DO" => "Dominican Republic",
            "NQ" => "Dronning Maud Land",
            "DD" => "East Germany",
            "EC" => "Ecuador",
            "EG" => "Egypt",
            "SV" => "El Salvador",
            "GQ" => "Equatorial Guinea",
            "ER" => "Eritrea",
            "EE" => "Estonia",
            "ET" => "Ethiopia",
            "FK" => "Falkland Islands",
            "FO" => "Faroe Islands",
            "FJ" => "Fiji",
            "FI" => "Finland",
            "FR" => "France",
            "GF" => "French Guiana",
            "PF" => "French Polynesia",
            "TF" => "French Southern Territories",
            "FQ" => "French Southern and Antarctic Territories",
            "GA" => "Gabon",
            "GM" => "Gambia",
            "GE" => "Georgia",
            "DE" => "Germany",
            "GH" => "Ghana",
            "GI" => "Gibraltar",
            "GR" => "Greece",
            "GL" => "Greenland",
            "GD" => "Grenada",
            "GP" => "Guadeloupe",
            "GU" => "Guam",
            "GT" => "Guatemala",
            "GG" => "Guernsey",
            "GN" => "Guinea",
            "GW" => "Guinea-Bissau",
            "GY" => "Guyana",
            "HT" => "Haiti",
            "HM" => "Heard Island and McDonald Islands",
            "HN" => "Honduras",
            "HK" => "Hong Kong SAR China",
            "HU" => "Hungary",
            "IS" => "Iceland",
            "IN" => "India",
            "ID" => "Indonesia",
            "IR" => "Iran",
            "IQ" => "Iraq",
            "IE" => "Ireland",
            "IM" => "Isle of Man",
            "IL" => "Israel",
            "IT" => "Italy",
            "JM" => "Jamaica",
            "JP" => "Japan",
            "JE" => "Jersey",
            "JT" => "Johnston Island",
            "JO" => "Jordan",
            "KZ" => "Kazakhstan",
            "KE" => "Kenya",
            "KI" => "Kiribati",
            "KW" => "Kuwait",
            "KG" => "Kyrgyzstan",
            "LA" => "Laos",
            "LV" => "Latvia",
            "LB" => "Lebanon",
            "LS" => "Lesotho",
            "LR" => "Liberia",
            "LY" => "Libya",
            "LI" => "Liechtenstein",
            "LT" => "Lithuania",
            "LU" => "Luxembourg",
            "MO" => "Macau SAR China",
            "MK" => "Macedonia",
            "MG" => "Madagascar",
            "MW" => "Malawi",
            "MY" => "Malaysia",
            "MV" => "Maldives",
            "ML" => "Mali",
            "MT" => "Malta",
            "MH" => "Marshall Islands",
            "MQ" => "Martinique",
            "MR" => "Mauritania",
            "MU" => "Mauritius",
            "YT" => "Mayotte",
            "FX" => "Metropolitan France",
            "MX" => "Mexico",
            "FM" => "Micronesia",
            "MI" => "Midway Islands",
            "MD" => "Moldova",
            "MC" => "Monaco",
            "MN" => "Mongolia",
            "ME" => "Montenegro",
            "MS" => "Montserrat",
            "MA" => "Morocco",
            "MZ" => "Mozambique",
            "MM" => "Myanmar [Burma]",
            "NA" => "Namibia",
            "NR" => "Nauru",
            "NP" => "Nepal",
            "NL" => "Netherlands",
            "AN" => "Netherlands Antilles",
            "NT" => "Neutral Zone",
            "NC" => "New Caledonia",
            "NZ" => "New Zealand",
            "NI" => "Nicaragua",
            "NE" => "Niger",
            "NG" => "Nigeria",
            "NU" => "Niue",
            "NF" => "Norfolk Island",
            "KP" => "North Korea",
            "VD" => "North Vietnam",
            "MP" => "Northern Mariana Islands",
            "NO" => "Norway",
            "OM" => "Oman",
            "PC" => "Pacific Islands Trust Territory",
            "PK" => "Pakistan",
            "PW" => "Palau",
            "PS" => "Palestinian Territories",
            "PA" => "Panama",
            "PZ" => "Panama Canal Zone",
            "PG" => "Papua New Guinea",
            "PY" => "Paraguay",
            "YD" => "People's Democratic Republic of Yemen",
            "PE" => "Peru",
            "PH" => "Philippines",
            "PN" => "Pitcairn Islands",
            "PL" => "Poland",
            "PT" => "Portugal",
            "PR" => "Puerto Rico",
            "QA" => "Qatar",
            "RO" => "Romania",
            "RU" => "Russia",
            "RW" => "Rwanda",
            "RE" => "Réunion",
            "BL" => "Saint Barthélemy",
            "SH" => "Saint Helena",
            "KN" => "Saint Kitts and Nevis",
            "LC" => "Saint Lucia",
            "MF" => "Saint Martin",
            "PM" => "Saint Pierre and Miquelon",
            "VC" => "Saint Vincent and the Grenadines",
            "WS" => "Samoa",
            "SM" => "San Marino",
            "SA" => "Saudi Arabia",
            "SN" => "Senegal",
            "RS" => "Serbia",
            "CS" => "Serbia and Montenegro",
            "SC" => "Seychelles",
            "SL" => "Sierra Leone",
            "SG" => "Singapore",
            "SK" => "Slovakia",
            "SI" => "Slovenia",
            "SB" => "Solomon Islands",
            "SO" => "Somalia",
            "ZA" => "South Africa",
            "GS" => "South Georgia and the South Sandwich Islands",
            "KR" => "South Korea",
            "ES" => "Spain",
            "LK" => "Sri Lanka",
            "SD" => "Sudan",
            "SR" => "Suriname",
            "SJ" => "Svalbard and Jan Mayen",
            "SZ" => "Swaziland",
            "SE" => "Sweden",
            "CH" => "Switzerland",
            "SY" => "Syria",
            "ST" => "São Tomé and Príncipe",
            "TW" => "Taiwan",
            "TJ" => "Tajikistan",
            "TZ" => "Tanzania",
            "TH" => "Thailand",
            "TL" => "Timor-Leste",
            "TG" => "Togo",
            "TK" => "Tokelau",
            "TO" => "Tonga",
            "TT" => "Trinidad and Tobago",
            "TN" => "Tunisia",
            "TR" => "Turkey",
            "TM" => "Turkmenistan",
            "TC" => "Turks and Caicos Islands",
            "TV" => "Tuvalu",
            "UM" => "U.S. Minor Outlying Islands",
            "PU" => "U.S. Miscellaneous Pacific Islands",
            "VI" => "U.S. Virgin Islands",
            "UG" => "Uganda",
            "UA" => "Ukraine",
            "SU" => "Union of Soviet Socialist Republics",
            "AE" => "United Arab Emirates",
            "GB" => "United Kingdom",
            "US" => "United States",
            "ZZ" => "Unknown or Invalid Region",
            "UY" => "Uruguay",
            "UZ" => "Uzbekistan",
            "VU" => "Vanuatu",
            "VA" => "Vatican City",
            "VE" => "Venezuela",
            "VN" => "Vietnam",
            "WK" => "Wake Island",
            "WF" => "Wallis and Futuna",
            "EH" => "Western Sahara",
            "YE" => "Yemen",
            "ZM" => "Zambia",
            "ZW" => "Zimbabwe",
            "AX" => "Åland Islands",
        );
        return $countryList;
    }
    public function row_status($status = NULL) 
    {
        if($status == null) {
            return '';
        } else if($status == 'pending' || $status == 'assigned' || $status == 'cleared' || $status == 'repairing') {
            return '<div class="text-center"><span class="row_status label label-warning">'.lang($status).'</span></div>';
        } else if($status == 'locked' || $status == 'accepted' || $status == 'completed' || $status == 'paid' || $status == 'sent' || $status == 'received'  || $status == 'active' || $status == 'free' || $status == 'authorized' || $status == 'sent') {
            return '<div class="text-center"><span class="row_status label label-success">'.lang($status).'</span></div>';
        } else if($status == 'picked_up' || $status == 'partial' || $status == 'transferring' || $status == 'ordered' || $status == 'approved' || $status == 'payoff' || $status == 'done') {
            return '<div class="text-center"><span class="row_status label label-info">'.lang($status).'</span></div>';
        } else if( $status == 'voided' || $status == 'cancel' || $status == 'due' || $status == 'returned' || $status == 'rejected' || $status == 'inactive' || $status == 'sold' || $status == 'unauthorize' || $status == 'deleted' || $status == 'not_done') {
            return '<div class="text-center"><span class="row_status label label-danger">'.lang($status).'</span></div>';
        } else {
            return '<div class="text-center"><span class="row_status label label-default">'.$status.'</span></div>';
        }
    }
    function pay_status($status = NULL) {
        if($status == null) {
            return '';
        } else if($status == 'pending') {
            return '<div class="text-center"><span class="payment_status label label-warning">'.$status.'</span></div>';
        } else if($status == 'completed' || $status == 'paid' || $status == 'sent' || $status == 'received') {
            return '<div class="text-center"><span class="payment_status label label-success">'.$status.'</span></div>';
        } else if($status == 'partial' || $status == 'transferring' || $status == 'ordered') {
            return '<div class="text-center"><span class="payment_status label label-info">'.$status.'</span></div>';
        } else if($status == 'due' || $status == 'returned' || $status == 'over' || $status == 'voiced') {
            return '<div class="text-center"><span class="payment_status label label-danger">'.$status.'</span></div>';
        } else {
            return '<div class="text-center"><span class="payment_status label label-default">'.$status.'</span></div>';
        }
    }
    function approved_status($status) {
        if ($status == null) {
            return '';
        } else if ($status == 'pending' || $status == 'requested') {
            return '<div class="text-center"><span class="approved_status label label-warning">'.lang($status).'</span></div>';
        } else if ($status == 'completed' || $status == 'paid' || $status == 'sent' || $status == 'received' || $status == 'approved') {
            return '<div class="text-center"><span class="approved_status label label-success">'.lang($status).'</span></div>';
        } else if ($status == 'partial' || $status == 'transferring' || $status == 'ordered') {
            return '<div class="text-center"><span class="approved_status label label-info">'.lang($status).'</span></div>';
        } else if ($status == 'due' || $status == 'returned' || $status == 'reject' || $status == 'rejected') {
            return '<div class="text-center"><span class="approved_status label label-danger">'.lang($status).'</span></div>';
        } else {
            return '<div class="text-center"><span class="approved_status label label-default">'.lang($status).'</span></div>';
        }
    }
}   
