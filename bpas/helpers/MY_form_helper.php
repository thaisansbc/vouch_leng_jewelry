<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Add admin_form_open
if (!function_exists('admin_form_open')) {
    function admin_form_open($action = '', $attributes = [], $hidden = [])
    {
        return form_open('admin/' . $action, $attributes, $hidden);
    }
}

// Add admin_form_open_multipart
if (!function_exists('admin_form_open_multipart')) {
    function admin_form_open_multipart($action = '', $attributes = [], $hidden = [])
    {
        if (is_string($attributes)) {
            $attributes .= ' enctype="multipart/form-data"';
        } else {
            $attributes['enctype'] = 'multipart/form-data';
        }
        return admin_form_open($action, $attributes, $hidden);
    }
}

// Add shop_form_open
if (!function_exists('shop_form_open')) {
    function shop_form_open($action = '', $attributes = [], $hidden = [])
    {
        return form_open('shop/' . $action, $attributes, $hidden);
    }
}

// Add shop_form_open_multipart
if (!function_exists('shop_form_open_multipart')) {
    function shop_form_open_multipart($action = '', $attributes = [], $hidden = [])
    {
        if (is_string($attributes)) {
            $attributes .= ' enctype="multipart/form-data"';
        } else {
            $attributes['enctype'] = 'multipart/form-data';
        }
        return shop_form_open($action, $attributes, $hidden);
    }
}

if(!function_exists('seconds_to_minutes'))
    {
        function seconds_to_minutes($seconds)
        {
            
            $minutes = floor($seconds/60);
            $secondsleft = $seconds%60;
            if($minutes<10)
                $minutes = "0" . $minutes;
            if($secondsleft<10)
                $secondsleft = "0" . $secondsleft;
            return $minutes;
        }
    }

    if(!function_exists('get_minutes'))
    {
        function get_minutes($interval)
        {
            $minutes = 0;

            if(!empty($interval))
            {
                $days = $interval->d;
                $hours = $interval->h;
                $minute = $interval->i;
                $seconds = $interval->s;

                if($days>0)
                {   
                    $minutes+=$days*1440;
                }
                if($hours>0)
                {   
                    $minutes+=$hours*60;
                }
                if($minute>0)
                {
                    $minutes+=$minute;
                }
                if($seconds>0)
                {
                    $minutes+=ceil($seconds/60);
                }
            }
           return $minutes;
        }
    }

    if(!function_exists('get_weekly_dates'))
    {
        function get_weekly_dates($date_start='2021-10-1',$date_end='2021-10-31')
        {
            
            $days = array('Mon'=>0,'Tue'=>1,'Wed'=>2,'Thu'=>3,'Fri'=>4,'Sat'=>5,'Sun'=>6);
            
            $start_date = date('d-m-Y',strtotime($date_start));
           
            $end_date = date('d-m-Y',strtotime($date_end));
           
            $day = date('D',strtotime($start_date));
           
            $end_day=date('D',strtotime($end_date));

            $end_days = 6 - $days[$end_day];

            $new_start_date = date('d-m-Y',strtotime('- '.$days[$day].' days',strtotime($start_date)));

            $new_end_date = date('d-m-Y',strtotime('+ '.$end_days.' days',strtotime($end_date)));
          

            $date_one = new dateTime($new_start_date);
            $date_two = new dateTime($new_end_date);

            $interval = $date_one->diff($date_two);
            $days =  ($interval->days+1)/7;
           

            $start_date_none = '';
            $dates = [];
            for($i=1;$i<=$days;$i++)
            {    
            
            if($start_date_none!='')
            {
                $new_start_date = $start_date_none;
            }
            
            if($i>1)
            {
                $new_start_date=date('d-m-Y',strtotime('+ 1 days',strtotime($new_start_date)));
            }
            
            $days_to_add= 6;
            
            $dates[$i] = [];
            $dates[$i]['week'] = date('W',strtotime($new_start_date));
            $dates[$i]['start_date'] = $new_start_date;
                
            $new_start_date=date('d-m-Y',strtotime('+ '.$days_to_add.' days',strtotime($new_start_date)));
                $dates[$i]['end_date'] = $new_start_date;
                
            }
           
           return $dates;
            
        }
    }
