<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @author: Pho Khaing | khaing.pho1991@gmail.com
 * @param: method for calculate and generate payment schedule for declining payment method
 * @param: method for calculate and generate payment schedule for annuity payment method
 * @param: $dibursed(float) ex: 1500.50
 * @param: $term(int)(month) ex: 12, 
 * @param: $rate(float)(%/1year) ex: 18, 
 * @param: $disburse_date(d-m-Y) ex: 01-01-2021, 
 * @param: $first_payment_date(d-m-Y) ex: 31-01-2021, 
 * @param: $term_nego(int)(month) ex: 5, default=null, 
 * @param: $rate_nego(float)(%/1year) ex: 5, default=null, 
 * if $term_nego not null and $rate_nego not null, first payment term $term_nego charge interest rate bye $rate_nego
 * ex: $term=12, $rate=18, $term_nego=5, $rate_nego=10
 * so first 5 months (1->5) interest charge 10%
 * then month 6->12 interest charge 18%
 */
function calculate_declining_payment_method($dibursed, $term, $rate, $disburse_date, $first_payment_date, $term_nego=null, $rate_nego=null)
{  
    $schedule = array();
    $previouse_payment_date = date('d-m-Y',strtotime($disburse_date));// ex: 30-05-2020
    $payment_date = date('d-m-Y',strtotime($first_payment_date));// ex: 30-06-2020

    $dibursed     = currency_format_without_sign($dibursed);
    $interest_rate= $rate / 100 / 360;
    $principal    = currency_format_without_sign($dibursed / $term);
    $interest     = $dibursed * $interest_rate;
    $total_amount = $principal + $interest;
    // if total $term only 1 balance=dibursed, else $balance=$dibursed - $principal
    $balance      = ($term == 1 ? $dibursed : $dibursed - $principal); 

    $_day_pay = date('d', strtotime($payment_date));
    $_next_d = $_day_pay;
    $_next_m = null;
    $_next_y = null;

    for ($i=1; $i <= $term ; $i++){
        
        $interest_rate= $rate / 100 / 360;
        if($term_nego != null && $rate_nego != null){
            if($i <= $term_nego){
                $interest_rate= $rate_nego / 100 / 360;
            }
        }

        $count_accrue_date = 0;// num date that forward 
        $interest_rate_by_day = $balance * $interest_rate * $count_accrue_date;

        //count repayment day from previouse month to next month
        $payment_date         = $payment_date;
        $previouse_month      = date_create($previouse_payment_date);
        $next_month           = date_create($payment_date);
        $previouse_to_next    = date_diff($previouse_month,$next_month);
        $count_day            = $previouse_to_next->format("%a");

            if($i == (int) $term){ 
                // calculation principle last payment
                $interest     = currency_format_without_sign(($balance * $interest_rate * $count_day) + $interest_rate_by_day);
                $principal    = currency_format_without_sign($balance);
                $balance      = currency_format_without_sign($balance - $principal);
                $dibursed     = currency_format_without_sign($balance);
                $total_amount = currency_format_without_sign($principal + $interest);
            }else{
                // calculation
                $interest     = currency_format_without_sign(($dibursed * $interest_rate * $count_day) + $interest_rate_by_day);
                $principal    = currency_format_without_sign($principal);
                $balance      = currency_format_without_sign($dibursed - $principal);
                $dibursed     = currency_format_without_sign($balance);
                $total_amount = currency_format_without_sign($principal + $interest);
            }

            $schedule[] = array(
                'pay_date'        => $payment_date,
                'day'             => $count_day,
                'principle'       => currency_format_without_sign($principal),
                'interest'        => currency_format_without_sign($interest),
                'monthly_payment' => currency_format_without_sign($total_amount),
                'balance'         => currency_format_without_sign($balance)
            );

        $previouse_payment_date = $payment_date;
        
        // payment date
        $d = 1;
        $last_m = date('m', strtotime($payment_date));
        $last_y = date('Y', strtotime($payment_date));
        $next_dmy = date('t-m-Y',strtotime("+1 month",strtotime($d.'-'.$last_m.'-'.$last_y)));
        $_next_d = date('d', strtotime($next_dmy));
        $_next_m = date('m', strtotime($next_dmy));
        $_next_y = date('Y', strtotime($next_dmy));

        if((int)$_next_d >= (int)$_day_pay){
            $payment_date = date('d-m-Y',strtotime($_day_pay.'-'.$_next_m.'-'.$_next_y));
        }else{
            $payment_date = date('d-m-Y', strtotime($_next_d.'-'.$_next_m.'-'.$_next_y));
        }
        $_next_d = date('d', strtotime($payment_date));
        $_next_m = date('m', strtotime($payment_date));
        $_next_y = date('Y', strtotime($payment_date));
    }
    return $schedule;
}

/**
 * @author: Pho Khaing | khaing.pho1991@gmail.com
 * @param: method for calculate and generate payment schedule for negotiation payment method
 * @param: method for calculate and generate payment schedule for annuity payment method
 * @param: $dibursed(float) ex: 1500.50
 * @param: $principle(float) ex: 500.50
 * @param: $total_term(int)(month) ex: 12, 
 * @param: $term(int)(month) ex: 5, 
 * @param: $rate(float)(%/1year) ex: 18, 
 * @param: $disburse_date(d-m-Y) ex: 01-01-2021, 
 * @param: $first_payment_date(d-m-Y) ex: 31-01-2021
 * monthly payment of principle is fix amount depend on negotiation
 */
function calculate_negotiation_payment_method($dibursed, $principle, $total_term, $term, $rate, $disburse_date, $first_payment_date)
{  
    $schedule = array();
    $previouse_payment_date = date('d-m-Y',strtotime($disburse_date));// ex: 30-05-2020
    $payment_date = date('d-m-Y',strtotime($first_payment_date));// ex: 30-06-2020

    $dibursed     = currency_format_without_sign($dibursed);
    $interest_rate= $rate / 100 / 360;
    $principal    = currency_format_without_sign($principle);
    $interest     = $dibursed * $interest_rate;
    $total_amount = $principal + $interest;
    // if total $term only 1 balance=dibursed, else $balance=$dibursed - $principal
    $balance      = ($term == 1 ? $dibursed : $dibursed - $principal); 

    $_day_pay = date('d', strtotime($payment_date));
    $_next_d = $_day_pay;
    $_next_m = null;
    $_next_y = null;

    for ($i=1; $i <= $term ; $i++){
        $count_accrue_date = 0;// num date that forward 
        $interest_rate_by_day = $balance * $interest_rate * $count_accrue_date;

        //count repayment day from previouse month to next month
        $payment_date         = $payment_date;
        $previouse_month      = date_create($previouse_payment_date);
        $next_month           = date_create($payment_date);
        $previouse_to_next    = date_diff($previouse_month,$next_month);
        $count_day            = $previouse_to_next->format("%a");

            if($i == (int) $total_term){
                // calculation principle last payment
                $interest     = currency_format_without_sign(($balance * $interest_rate * $count_day) + $interest_rate_by_day);
                $principal    = currency_format_without_sign($balance);
                $balance      = currency_format_without_sign($balance - $principal);
                $dibursed     = currency_format_without_sign($balance);
                $total_amount = currency_format_without_sign($principal + $interest);
            }else{
                // calculation
                $interest     = currency_format_without_sign(($dibursed * $interest_rate * $count_day) + $interest_rate_by_day);
                $principal    = currency_format_without_sign($principal);
                $balance      = currency_format_without_sign($dibursed - $principal);
                $dibursed     = currency_format_without_sign($balance);
                $total_amount = currency_format_without_sign($principal + $interest);
            }

            $schedule[] = array(
                'pay_date'        => $payment_date,
                'day'             => $count_day,
                'principle'       => currency_format_without_sign($principal),
                'interest'        => currency_format_without_sign($interest),
                'monthly_payment' => currency_format_without_sign($total_amount),
                'balance'         => currency_format_without_sign($balance)
            );

        $previouse_payment_date = $payment_date;
        
        // payment date
        $d = 1;
        $last_m = date('m', strtotime($payment_date));
        $last_y = date('Y', strtotime($payment_date));
        $next_dmy = date('t-m-Y',strtotime("+1 month",strtotime($d.'-'.$last_m.'-'.$last_y)));
        $_next_d = date('d', strtotime($next_dmy));
        $_next_m = date('m', strtotime($next_dmy));
        $_next_y = date('Y', strtotime($next_dmy));

        if((int)$_next_d >= (int)$_day_pay){
            $payment_date = date('d-m-Y',strtotime($_day_pay.'-'.$_next_m.'-'.$_next_y));
        }else{
            $payment_date = date('d-m-Y', strtotime($_next_d.'-'.$_next_m.'-'.$_next_y));
        }
        $_next_d = date('d', strtotime($payment_date));
        $_next_m = date('m', strtotime($payment_date));
        $_next_y = date('Y', strtotime($payment_date));
    }
    return $schedule;
}

/**
 * @author: Pho Khaing | khaing.pho1991@gmail.com
 * @param: method for calculate and generate payment schedule for annuity payment method
 * @param: $diburse_amount(float) ex: 1500.50
 * @param: $term(int)(month) ex: 12, 
 * @param: $rate(float)(%/1year) ex: 18, 
 * @param: $disburse_date(d-m-Y) ex: 01-01-2021, 
 * @param: $first_payment_date(d-m-Y) ex: 31-01-2021, 
 * @param: $term_nego(int)(month) ex: 5, default=null, 
 * @param: $rate_nego(float)(%/1year) ex: 5, default=null, 
 * if $term_nego not null and $rate_nego not null, first payment term $term_nego charge interest rate bye $rate_nego
 * ex: $term=12, $rate=18, $term_nego=5, $rate_nego=10
 * so first 5 months (1->5) interest charge 10%
 * then month 6->12 interest charge 18%
 */
function calculate_annuity_payment_method($diburse_amount, $term, $rate, $disburse_date, $first_payment_date, $term_nego=null, $rate_nego=null)
{
    $pmt_amount = annuity_pmt(currency_format_without_sign($diburse_amount), $term, $rate);
    $schedule = array();
    $previouse_payment_date = date('d-m-Y',strtotime($disburse_date));// ex: 30-05-2020
    $payment_date = date('d-m-Y',strtotime($first_payment_date));// ex: 30-06-2020

    $balance         = currency_format_without_sign($diburse_amount);
    $interest_rate   = $rate / 100 / 360;

    $_day_pay = date('d', strtotime($payment_date));
    $_next_d = $_day_pay;
    $_next_m = null;
    $_next_y = null;

    for ($i=1; $i <= $term ; $i++){

        $interest_rate= $rate / 100 / 360;
        if($term_nego != null && $rate_nego != null){
            if($i <= $term_nego){
                $interest_rate= $rate_nego / 100 / 360;
            }
        }

        $count_accrue_date = 0;// num date that forward 
        $interest_rate_by_day = $balance * $interest_rate * $count_accrue_date;

        //count repayment day from previouse month to next month
        $payment_date         = $payment_date;
        $previouse_month      = date_create($previouse_payment_date);
        $next_month           = date_create($payment_date);
        $previouse_to_next    = date_diff($previouse_month,$next_month);
        $count_day            = $previouse_to_next->format("%a");

            if($i == (int) $term){
                // calculation principle last payment
                $interest     = currency_format_without_sign(($balance * $interest_rate * $count_day) + $interest_rate_by_day);
                $principal    = currency_format_without_sign($balance);
                $balance      = currency_format_without_sign($balance - $principal);
                $total_amount = currency_format_without_sign($principal + $interest);
            }else{
                // calculation
                $interest     = currency_format_without_sign(($balance * $interest_rate * $count_day) + $interest_rate_by_day);
                $principal    = currency_format_without_sign($pmt_amount - $interest);
                $balance      = currency_format_without_sign($balance - $principal);
                $total_amount = currency_format_without_sign($principal + $interest);
            }

            $schedule[] = array(
                'pay_date'        => $payment_date,
                'day'             => $count_day,
                'principle'       => currency_format_without_sign($principal),
                'interest'        => currency_format_without_sign($interest),
                'monthly_payment' => currency_format_without_sign($total_amount),
                'balance'         => currency_format_without_sign($balance)
            );

        $previouse_payment_date = $payment_date;

        // payment date
        $d = 1;
        $last_m = date('m', strtotime($payment_date));
        $last_y = date('Y', strtotime($payment_date));
        $next_dmy = date('t-m-Y',strtotime("+1 month",strtotime($d.'-'.$last_m.'-'.$last_y)));
        $_next_d = date('d', strtotime($next_dmy));
        $_next_m = date('m', strtotime($next_dmy));
        $_next_y = date('Y', strtotime($next_dmy));

        if((int)$_next_d >= (int)$_day_pay){
            $payment_date = date('d-m-Y',strtotime($_day_pay.'-'.$_next_m.'-'.$_next_y));
        }else{
            $payment_date = date('d-m-Y', strtotime($_next_d.'-'.$_next_m.'-'.$_next_y));
        }
        $_next_d = date('d', strtotime($payment_date));
        $_next_m = date('m', strtotime($payment_date));
        $_next_y = date('Y', strtotime($payment_date));

    }
    return $schedule;
}

/**
 * @author: Pho Khaing | khaing.pho1991@gmail.com
 * @param: method for calculate total payment per month of annuity payment method
 * @param: $loan(float) ex: 1500.50
 * @param: $term(int) ex: 12
 * @param: $apr(float)(%/1year) ex: 18
 */
function annuity_pmt($loan, $term, $apr, $currency='USD'){
    // $term = $term * 12;
    $apr = $apr / 1200;
    $amount = $apr * -$loan * pow((1 + $apr), $term) / (1 - pow((1 + $apr), $term));
    // return currency_format_without_sign($currency,$amount);
    return $amount;
}

/**
 * @param: method for round currentcy
 * @param: $amount(float) ex: 1500.50
 */
function currency_format_without_sign($amount, $currency_code='USD')
{   
    $money = round($amount, 2);
    $result = number_format($money, 2, ".", "");
    return $result;
}

/**
 * @param: method for round & format currentcy with sign($)
 * @param: $amount(float) ex: 1500.50
 */
function currency_format($amount, $currency_code='USD') 
{
    $number = round($amount, 2);
    $result = number_format($number, 2, ".", ",");
    return "$ ".$result;
}
