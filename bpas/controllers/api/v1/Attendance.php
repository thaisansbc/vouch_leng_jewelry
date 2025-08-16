<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Attendance extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->api_model('user_model');
        $this->load->api_model('attendance_api');
        $this->load->helper(array('form', 'url'));
    }

    public function get_date_shifts_post()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $date = $this->post('date');
            $user_id =  $this->post('user_id');
            if ($date == null) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Date is required',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
            } elseif ($user_id == null) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'User id is required',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
            } else {
                $shifts_data = $this->attendance_api->get_shift_by_date($user_id, $date);
                $data = [];
                $total_work_time = 0;
                if (!empty($shifts_data)) {
                    foreach ($shifts_data as $index => $item) {
                        $total_minutes = (int) $item['shift_total_time'];
                        $total_work_time = $total_work_time + $total_minutes;
                        if ($total_minutes > 0) {
                            $hours = floor($total_minutes / 60);
                            $min = $total_minutes - ($hours * 60);
                            $data['list'][$index]['total_work'] = $hours . 'hr ' . $min . 'min';
                            $data['list'][$index]['total_work_min'] = $total_minutes;
                        } else {
                            $data['list'][$index]['total_work'] = '00hr 00min';
                            $data['list'][$index]['total_work_min'] = 0;
                        }
                        $data['list'][$index]['start_time'] = date('h:i A', strtotime($item['shift_starttime']));
                        if ($item['shift_endtime'] != '0000-00-00 00:00:00') {
                            $endtime = date('h:i A', strtotime($item['shift_endtime']));
                        } else {
                            $endtime = '--';
                        }
                        $data['list'][$index]['end_time'] = $endtime;
                    }
                    $hours = floor($total_work_time / 60);
                    $min = $total_work_time - ($hours * 60);
                    $data['total']['shift_total_time'] = $hours . 'hr ' . $min . 'min';
                    $data['total']['shift_total_min'] = $total_work_time;
                }
                $this->response([
                    'status' => TRUE,
                    'message' => "Success",
                    'data' => $data
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response([
                'status' => FALSE,
                'message' =>  "Invalid Request",
                'data' => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function get_currentshift_post()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $user_id = $this->post('user_id') ?? null;
            if ($user_id == null) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Employee id is required',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
            } else {
                $data = [];
                $current_shift_data = $this->attendance_api->api_get_active_shift($user_id);

                $data['current_shift'] = [];
                if (!empty($current_shift_data)) {
                    $data['current_shift']['shift_id'] = $current_shift_data['id'];
                    $data['current_shift']['start_time'] = date('h:i A', strtotime($current_shift_data['shift_starttime']));
                    $data['current_shift']['normal_starttime'] = $current_shift_data['shift_starttime'];
                }
                $this->response([
                    'status' => TRUE,
                    'message' => "Success",
                    'data' => $data
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response([
                'status' => FALSE,
                'message' =>  "Invalid Request",
                'data' => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function check_in_check_out_post()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $emp_id             = $this->post('emp_id') ?? null;
            $user_id            = $this->post('user_id') ?? null;
            $type               = $this->post('type') ?? null;
            $shift_endtime      = date('H:i');
            $shift_starttime    = date('H:i');
            $shift_date         = date('Y-m-d'); //$this->post('shift_date') ?? null;
            $shift_id           = $this->post('shift_id') ?? null;
            $qr_code            = $this->post('qr_code') ?? null;
            $temp_lat           = $this->post('latitute') ?? null;
            $temp_long          = $this->post('longitute') ?? null;
            $userdata = $this->user_model->get_user_by_id($user_id);
            if (empty($userdata)) {
                return  $this->response([
                    'status' => FALSE,
                    'message' => 'Invalid User Id!!',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            if (empty($userdata['biller_id'])) {
                return  $this->response([
                    'status' => FALSE,
                    'message' => 'Please contact to admininstator. Branch not assign!!',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            if ($userdata['is_remote_allow'] == 0) {
                if (!isset($qr_code) || $qr_code == '') {
                    return $this->response([
                        'status' => FALSE,
                        'message' => 'QR Code Required!!',
                        'data' => []
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
                if (empty($temp_lat) || empty($temp_long)) {
                    return  $this->response([
                        'status' => FALSE,
                        'message' => 'Invalid latitute & longitute!!',
                        'data' => []
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
                /// Check Qr Code is match in branch
                $result = $this->user_model->get_company_qrcode($qr_code, $userdata['biller_id']);
                if (empty($result)) {
                    return $this->response([
                        'status' => FALSE,
                        'message' => 'Invalid QR Code!!',
                        'data' => []
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
                /// check radious latitude & longitude 
                $billerData = $this->user_model->get_biller_by_id($userdata['biller_id']);
                $latmin = (!empty($billerData['latitude'])) ? $billerData['latitude'] : '0.00';
                $lonmin = (!empty($billerData['longitude'])) ? $billerData['longitude'] : '0.00';
                $area_radius = (!empty($billerData['radius'])) ? $billerData['radius'] : '0';
                $distance_lat_long = "SELECT (3959 * acos(cos (radians($temp_lat))*cos(radians($latmin))*cos(radians($lonmin) - radians($temp_long))+sin(radians($temp_lat))*sin(radians($latmin)))) AS distance";
                $query = $this->db->query($distance_lat_long);
                $distance_data = $query->row_array();
                $meter = $distance_data['distance']  * 1000;
                if ($area_radius < $meter) {
                    return $this->response([
                        'status' => FALSE,
                        'message' => 'Not Allowed , You are out of range!!',
                        'data' => []
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
            }
            if ($type == null) {
                return $this->response([
                    'status' => FALSE,
                    'message' => 'Type is required (1 => checkin,2 => checkout)',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
            } elseif ($type == '1') {
                if ($shift_date == null) {
                    return $this->response([
                        'status' => FALSE,
                        'message' => 'Missing parameter shift date',
                        'data' => []
                    ], REST_Controller::HTTP_BAD_REQUEST);
                } elseif ($user_id == null) {
                    return $this->response([
                        'status' => FALSE,
                        'message' => 'Missing parameter User id',
                        'data' => []
                    ], REST_Controller::HTTP_BAD_REQUEST);
                } else {
                    if (!$this->verifydate($shift_date)) {
                        return $this->response([
                            'status' => FALSE,
                            'message' => 'Invalid date',
                            'data' => []
                        ], REST_Controller::HTTP_BAD_REQUEST);
                    }
                    $already_checked_in = $this->attendance_api->get_checked_in_shift($user_id);
                    if (!empty($already_checked_in)) {
                        return $this->response([
                            'status' => FALSE,
                            'message' => 'Already checked in',
                            'data' => $already_checked_in
                        ], REST_Controller::HTTP_BAD_REQUEST);
                    }
                    $checkin_location       = array('latitute' => $temp_lat, 'longitute' => $temp_long);
                    $data['employee_id']    = $emp_id;
                    $data['user_id']        = $user_id;
                    $data['shift_date']     = date('Y-m-d', strtotime($shift_date));
                    $data['shift_starttime'] = date('Y-m-d H:i:s', strtotime($shift_starttime));
                    $data['shift_status']   = 1;
                    $data['shift_intype']   = $userdata['is_remote_allow'];
                    $data['checkin_location'] = json_encode($checkin_location);
                    $data['created_at']     = date('Y-m-d H:i:s');
                    $data['updated_at']     = date('Y-m-d H:i:s');
                    $save = $this->attendance_api->add_shift($data);
                    if ($save) {
                        $datas['shift_id'] = $save;
                        $datas['current_shift'] = $this->attendance_api->get_shift_by_id($save);
                        return $this->response([
                            'status' => TRUE,
                            'message' => "Checked in successfully",
                            'data' => $datas
                        ], REST_Controller::HTTP_OK);
                    } else {
                        return $this->response([
                            'status' => FALSE,
                            'message' => 'Failed to check in',
                            'data' => []
                        ], REST_Controller::HTTP_BAD_REQUEST);
                    }
                }
            } elseif ($type == '2') {
                if ($shift_id == null) {
                    return $this->response([
                        'status' => FALSE,
                        'message' => 'Missing parameter shift id',
                        'data' => []
                    ], REST_Controller::HTTP_BAD_REQUEST);
                } else {
                    $shift_data = $this->attendance_api->get_shift_by_id($shift_id);
                    if (empty($shift_data)) {
                        return $this->response([
                            'status' => FALSE,
                            'message' => 'Invalid shift',
                            'data' => []
                        ], REST_Controller::HTTP_BAD_REQUEST);
                    } else {
                        if ($shift_data['shift_status'] == '2') {
                            return $this->response([
                                'status' => FALSE,
                                'message' => 'Already checked out.',
                                'data' => []
                            ], REST_Controller::HTTP_BAD_REQUEST);
                        }
                        $checkout_location = array('latitute' => $temp_lat, 'longitute' => $temp_long);
                        $shift_start = new DateTime($shift_data['shift_starttime']);
                        $shift_end = new DateTime($shift_endtime);
                        $interval = $shift_start->diff($shift_end);
                        $total_work_time = get_minutes($interval);
                        // $data['shift_starttime'] = $shift_data['shift_starttime']; 
                        $data['shift_endtime'] = date('Y-m-d H:i:s', strtotime($shift_endtime));
                        $data['shift_total_time'] = $total_work_time;
                        $data['checkout_location'] = json_encode($checkout_location);
                        $data['shift_outtype'] = $userdata['is_remote_allow'];
                        $data['shift_status'] = 2;
                        $data['updated_at'] = date('Y-m-d H:i:s');
                        $data['employee_id'] = $emp_id;
                        $shift_up = $this->attendance_api->update_shift($shift_id, $data);
                        if ($shift_up) {
                            $hours = floor($total_work_time / 60);
                            $min = $total_work_time - ($hours * 60);
                            $data['shift_total_format'] = $hours . 'hr ' . $min . 'min';
                            return $this->response([
                                'status' => TRUE,
                                'message' => 'Checkout successfull',
                                'data' => $data
                            ], REST_Controller::HTTP_OK);
                        } else {
                            return $this->response([
                                'status' => FALSE,
                                'message' => 'Failed to check out',
                                'data' => []
                            ], REST_Controller::HTTP_BAD_REQUEST);
                        }
                    }
                }
            }
        } else {
            return $this->response([
                'status' => FALSE,
                'message' =>  "Invalid Request",
                'data' => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function weekly_attendance_history_post()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $month = $this->post('month');
            $year = $this->post('year');
            $user_id = $this->post('user_id');
            if ($month == null) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Month is required',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
            } elseif ($user_id == null) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'User id is required',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
            } elseif ($year == null) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Year is required',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
            } else {
                $month_year = $year . '-' . $month;
                $date = date('M-Y', strtotime($month_year));
                $date_start = date('d-m-Y', strtotime('first day of ' . $date));
                $date_end = date('d-m-Y', strtotime('last day of ' . $date));
                $weekly_dates = get_weekly_dates($date_start, $date_end);
                if (!empty($weekly_dates)) {
                    foreach ($weekly_dates as $index => $item) {
                        $total_minutes = (int) $this->attendance_api->get_minutes_sum($item['start_date'], $item['end_date'], $user_id);
                        if ($total_minutes > 0) {
                            $hours = floor($total_minutes / 60);
                            $min = $total_minutes - ($hours * 60);
                            $weekly_dates[$index]['total_work'] = $hours . 'hr ' . $min . 'min';
                        } else {
                            $weekly_dates[$index]['total_work'] = '00hr 00min';
                        }
                    }
                }
                $this->response([
                    'status'    => TRUE,
                    'message'   => "Success",
                    'data'      => $weekly_dates
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response([
                'status'    => FALSE,
                'message'   =>  "Invalid Request",
                'data'      => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function monthly_attendance_history_post()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $month = $this->post('month');
            $year = $this->post('year');
            $user_id = $this->post('user_id');
            if ($month == null) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Month is required',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
            } elseif ($user_id == null) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Employee id is required',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
            } elseif ($year == null) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Year is required',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
            } else {
                $month_year = $year . '-' . $month;
                $date = date('M-Y', strtotime($month_year));
                $date_start = date('Y-m-d', strtotime('first day of ' . $date));
                $date_end = date('Y-m-d', strtotime('last day of ' . $date));
                $mothly_data = $this->attendance_api->get_mothly_data($date_start, $date_end, $user_id);
                $finaldata = array();
                $data = array();
                $k = 0;
                if (!empty($mothly_data)) {
                    foreach ($mothly_data as $index => $item) {
                        $finaldata[$item['shift_date']]['date'] = date('d/m/Y', strtotime($item['shift_date']));
                        if (!isset($finaldata[$item['shift_date']]['total_work'])) {
                            $finaldata[$item['shift_date']]['total_work'] = (int)$item['shift_total_time'];
                            $k = 0;
                        } else {
                            $finaldata[$item['shift_date']]['total_work'] += (int)$item['shift_total_time'];
                        }
                        //// Detail of shift same day
                        $finaldata[$item['shift_date']]['shift'][$k]["start_time"] = date('h:i A', strtotime($item['shift_starttime']));
                        $finaldata[$item['shift_date']]['shift'][$k]["end_time"] = date('h:i A', strtotime($item['shift_endtime']));
                        $total_work = isset($item['shift_total_time']) ? $item['shift_total_time'] : 0;
                        if ($total_work > 0) {
                            $hours = floor($total_work / 60);
                            $min = $total_work - ($hours * 60);
                            $finaldata[$item['shift_date']]['shift'][$k]["total_work"] =  $hours . 'hr ' . $min . 'min';
                        } else {
                            $finaldata[$item['shift_date']]['shift'][$k]["total_work"] =  '00hr 00min';
                        }
                        $k++;
                    }
                    $begin = new DateTime($date_start);
                    $end = new DateTime(date('Y-m-d', strtotime($date_end . ' + 1 days')));
                    $interval = DateInterval::createFromDateString('1 day');
                    $period = new DatePeriod($begin, $interval, $end);
                    $i = 0;
                    foreach ($period as $dt) {
                        $fDate = $dt->format("Y-m-d");
                        $data[$i]['date'] = $dt->format("d/m/Y");
                        $data[$i]['day'] = $dt->format("l");
                        $total_minutes = isset($finaldata[$fDate]['total_work']) ? $finaldata[$fDate]['total_work'] : 0;
                        if ($total_minutes > 0) {
                            $hours = floor($total_minutes / 60);
                            $min = $total_minutes - ($hours * 60);
                            $data[$i]['total_work'] =  $hours . 'hr ' . $min . 'min';
                        } else {
                            $data[$i]['total_work'] =  '00hr 00min';
                        }
                        $data[$i]['shift'] = isset($finaldata[$fDate]['shift']) ? $finaldata[$fDate]['shift'] : [];
                        $i++;
                    }
                }
                $this->response([
                    'status' => TRUE,
                    'message' => "Success",
                    'data' => $data
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response([
                'status' => FALSE,
                'message' =>  "Invalid Request",
                'data' => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    protected function verifydate($date)
    {
        $verified = 0;
        if ($date != null) {
            $today = date('Y-m-d');
            $date = date('Y-m-d', strtotime($date));
            if (strtotime($today) == strtotime($date)) {
                $verified = 1;
            }
        }
        return $verified;
    }

    public function apply_leave_post()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $date           = date('Y-m-d H:i');
            $biller_id      = $this->post('biller');
            $reference_no   = $this->site->getReference('tl', $biller_id);
            $employee_id    = $this->post('employee_id');
            $start_date     = date('Y-m-d', strtotime($this->post('start_date')));
            $end_date       = date('Y-m-d', strtotime($this->post('end_date')));
            $timeshift      = $this->post('timeshift');
            $reason         = $this->post('reason');
            $leave_type     = $this->post('leave_type');
            $note           = $this->bpas->clear_tags($this->post('note'));
            if ($leave_type) {
                $dataDetails = [
                    'employee_id'   => $employee_id,
                    'leave_type'    => $leave_type,
                    'start_date'    => $start_date,
                    'end_date'      => $end_date,
                    'timeshift'     => $timeshift,
                    'reason'        => $reason
                ];
            }
            $data = [
                'date'          => $date,
                'reference_no'  => $reference_no,
                'biller_id'     => $biller_id,
                'note'          => $note,
                'created_by'    => $this->post('created_by'),
            ];
            $insert = $this->attendance_api->addTakeLeave($data, $dataDetails);
            if ($insert) {
                $this->response([
                    'status' => TRUE,
                    'message' => 'You have been apply leave successful.',
                    'data' => $insert
                ], REST_Controller::HTTP_OK);
                $this->site->updateReference('tl');
            } else {
                $this->response("Some problems occurred, please try again.", REST_Controller::HTTP_BAD_REQUEST);
            }
        } else {
            $this->response([
                'status' => false,
                'message' => 'Invalid Request',
                'data' => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function get_apply_leave_get()
    {
        $employee_id = $this->get('employee_id') ?? null;
        if ($employee_id == null) {
            $data = $this->attendance_api->getEmployeeAllTakeLeave();
            $this->response($data, REST_Controller::HTTP_OK);
        } else {
            if ($data = $this->attendance_api->getEmployeeAllTakeLeave($employee_id)) {
                $this->response([
                    'status'  => TRUE,
                    'message' => "Success",
                    'data'    => $data
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status'  => TRUE,
                    'message' => "No Data Available",
                    'data'    => []
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function apply_dayoff_post()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $biller_id      = $this->post('biller');
            $date           = date('Y-m-d H:i:s');
            $note           = $this->bpas->clear_tags($this->post('note'));
            $items          = false;
            $employee_id    = $this->post('employee_id');
            $day_off        = date('Y-m-d', strtotime($this->post('day_off')));
            $description    = $this->post('note');
            if ($employee_id) {
                $items = [
                    'employee_id'   => $employee_id,
                    'day_off'       => $day_off,
                    'description'   => $description,
                ];
            }
            $data = [
                'date'          => $date,
                'biller_id'     => $biller_id,
                'note'          => $note,
                'created_by'    => $this->post('created_by'),
            ];
            $insert = $this->attendance_api->addDayOff($data, $items);
            if ($insert) {
                $this->response([
                    'status' => TRUE,
                    'message' => 'You have been apply day off successful.',
                    'data' => $insert
                ], REST_Controller::HTTP_OK);
                //$this->site->updateReference('tl');
            } else {
                $this->response("Some problems occurred, please try again.", REST_Controller::HTTP_BAD_REQUEST);
            }
        } else {
            $this->response([
                'status' => false,
                'message' => 'Invalid Request',
                'data' => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function get_apply_dayoff_get()
    {
        $employee_id = $this->get('employee_id') ?? null;
        if ($employee_id == null) {
            
            $data = $this->attendance_api->getEmployeeAllDayOffs();
            $this->response($data, REST_Controller::HTTP_OK);
        } else {
            if ($data = $this->attendance_api->getEmployeeAllDayOffs($employee_id)) {
                $this->response([
                    'status'    => TRUE,
                    'message'   => "Success",
                    'data'      => $data
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status'  => TRUE,
                    'message' => "No Data Available",
                    'data'    => []
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function get_leave_timeshift_get()
    {
        $leave_timeshift = array(
            'full'      => lang('full'),
            'morning'   => lang('morning'),
            'afternoon' => lang('afternoon'),
        );
        $this->response([
            'status'    => TRUE,
            'message'   => "Success",
            'data'      => $leave_timeshift
        ], REST_Controller::HTTP_OK);
    }

    public function get_leave_type_get()
    {
        if ($leave_type = $this->attendance_api->getLeaveType()) {
            $this->response([
                'status'    => TRUE,
                'message'   => "Success",
                'data'      => $leave_type
            ], REST_Controller::HTTP_OK);
        } else {
            $this->set_response([
                'message' => 'Leave type not found',
                'status'  => false,
                'data'    => false
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
    public function get_checkin_checkout_get()
    {
        $employee_id = $this->get('employee_id') ?? null;
        if ($leave_type = $this->attendance_api->getCheckIn_CheckOut($employee_id)) {
            $this->response([
                'status'    => TRUE,
                'message'   => "Success",
                'data'      => $leave_type
            ], REST_Controller::HTTP_OK);
        } else {
            $this->set_response([
                'message' => 'Leave type not found',
                'status'  => false,
                'data'    => false
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
}