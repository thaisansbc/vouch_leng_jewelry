<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;


class Auth extends REST_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->api_model('auth_model');
        $this->load->api_model('user_model');
        $this->load->helper(array('form', 'url'));
	} 

  	// --------- login api -------------
    public function login_post() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') { 
            $this->form_validation->set_rules('user_name', 'User Name', 'trim|strip_tags|required');
            $this->form_validation->set_rules('password', 'Password', 'trim|strip_tags|required');

            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                $this->response([
                    'status'    => FALSE,
                    'message'   => "Ooops.. There is some error..!!",
                    'data'      => [
                        'error' => $errors
                    ]
                ], REST_Controller::HTTP_BAD_REQUEST);
            } else {
                $data = array(
                    'username' => $this->input->post('user_name'),
                    'password' => $this->input->post('password'),
                );

                $results = $this->auth_model->login($data);

                if (empty($results['data'])) {
                    $this->response([
                        'status' => FALSE,
                        'message' => "Invalid Login Credentials!!",
                        'data' => []
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }else {
                    if ($results['data']['active'] == '0') {
                        $this->response([
                            'status' => FALSE,
                            'message' => "Account disabled by administrator",
                            'data' => []
                        ], REST_Controller::HTTP_BAD_REQUEST);
                    } else {
                        if (!empty($this->input->get('fcm_id')) && !empty($this->input->get('fcm_id'))) {
                            $data = ['fcm_id' => $this->input->post('fcm_id'), 'device_id' => $this->input->post('device_id')];

                            $this->db->where(['id' => $results['data']['id']]);
                            $this->db->update('ci_users', $data);
                        }

                        if ($results['data']['image']==NULL && $results['data']['photo']==NULL) {
                            $results['data']['image'] = base_url('assets/images/profile.jpg');
                        } elseif (($results['data']['photo']!=NULL)) {
                            $results['data']['image'] = base_url('assets/uploads/'.$results['data']['photo']);
                        }else {
                            $results['data']['image'] = base_url('assets/uploads/avatars/thumbs/'.$results['data']['image']);
                        }
                        $this->response([
                            'status' => TRUE,
                            'message' => "User Login Successfully!!!",
                            'data' => $results['data']
                        ], REST_Controller::HTTP_OK);
                    }
                }
            }
        } else {
            $this->response([
                'status' => FALSE,
                'message' =>  "Invalid API key",
                'data' => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
 	 

    public function profile_post(){
        $data = [];
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('user_id', 'User ID', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                $this->response([
                    'status'    => FALSE,
                    'message'   => "Ooops.. There is some error..!!",
                    'data'      => [
                        'error' => $errors
                    ]
                ], REST_Controller::HTTP_BAD_REQUEST);
            } else {
                $user_id = $this->input->post('user_id');
                $profile_data = $this->user_model->get_user_by_id($user_id);

                if ($profile_data) {
                    unset($profile_data['password']);
                    unset($profile_data['token']);
                    unset($profile_data['password_reset_code']);
                    unset($profile_data['last_ip']);
                    
                    if ($profile_data['image']==NULL && $profile_data['photo']==NULL) {
                        $profile_data['image'] = base_url('assets/images/profile.jpg');
                    } elseif ($profile_data['photo']!=NULL) {
                        $profile_data['image'] = base_url('assets/uploads/'.$profile_data['photo']);
                    }else {
                        $profile_data['image'] = base_url('assets/uploads/avatars/thumbs/'.$profile_data['image']);
                    }
                    
                    $data = $profile_data;
                    $this->response([
                        'status' => TRUE,
                        'message' =>  "Successful",
                        'data' => $data
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => FALSE,
                        'message' =>  "No Data Found",
                        'data' => []
                    ], REST_Controller::HTTP_NOT_FOUND);
                }
            }
        } else {
            $this->response([
                'status' => FALSE,
                'message' =>  "Invalid Request",
                'data' => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function change_password_post()
    {
        if($this->input->server('REQUEST_METHOD') == 'POST'){
            $this->form_validation->set_rules('password', 'Password', 'trim|strip_tags|required');
            $this->form_validation->set_rules('oldpassword', 'Old Password', 'trim|required');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|strip_tags|required|matches[password]');
            $this->form_validation->set_rules('user_id', 'User ID', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();

                $this->response([
                    'status'    => FALSE,
                    'message'   => "Ooops.. There is some error..!!",
                    'data'      => [
                        'error' => $errors,
                        'data'  => $this->input->post()
                    ]
                ], REST_Controller::HTTP_BAD_REQUEST);
            } else {
                $user_id = $this->input->post('user_id');
                $user_data = $this->user_model->get_user_by_id($user_id);

                if (empty($user_data)) {
                    $this->response([
                        'status' => FALSE,
                        'message' => 'No user Found.',
                        'data' => []
                    ], REST_Controller::HTTP_NOT_FOUND);
                }

                $password =  $this->input->post('password'); 
                $oldpassword =  $this->input->post('oldpassword'); 
                
                $result = $this->auth_model->change_pwd($password, $oldpassword, $user_id);

                if($result==1){
                     $this->response([
                        'status' => TRUE,
                        'message' => "Password has been changed successfully",
                        'data' => []
                    ], REST_Controller::HTTP_OK);                
                }else if($result==2){
                   $this->response([
                        'status'    => FALSE,
                        'message'   => "Old password is incorrect",
                        'data'      => []
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }else {
                    $this->response([
                        'status'    => FALSE,
                        'message'   => "Unable to change password",
                        'data'      => []
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
            }
        }
    }


    
    public function forgot_password_post(){
        
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('phone', 'Mobile No.', 'trim|required|regex_match[/^[0-9]{10}$/]');
            if ($this->form_validation->run() == FALSE) {

                $errors = $this->form_validation->error_array();
                $this->response([
                    'status'    => FALSE,
                    'message'   => "Ooops.. There is some error..!!",
                    'data'      => $errors
                ], REST_Controller::HTTP_BAD_REQUEST);
            } 

            $response = $this->user_model->verify_user($this->input->post('phone'));
            if ($response) {
                $otp = rand(1000,9999);

                $this->user_model->edit_user(['password_reset_code' => $otp], $response['id']); 
                 ## ------------------------- Send Message ------------------------##
                $username = 'sbc_sms@apitest';
                $password = '8d5cc1f5b4451b407600cf8e0c5cbf5e';
                $numbers = $response['phone'];
                $sender = rawurlencode('SMS Testing');
                $message = rawurlencode('Forget passowrd OTP for '.$this->Settings->site_name.' is '.$otp.'.');         
                /*'https://sandbox.mekongsms.com/api/sendsms.aspx?username=sbc_sms@apitest&pass=og9!0A&cd=Cust001&sender=SMS%20Testing&smstext=hello&gsm=9887001059&int=1'*/           
                $curl = curl_init();
                curl_setopt_array($curl, array(
                  CURLOPT_URL => 'https://sandbox.mekongsms.com/api/sendsms.aspx?username='.$username.'&pass='.$password.'&cd=Cust001&sender='.$sender.'&smstext='.$message.'&gsm='.$numbers.'&int=1',
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'GET',
                ));
                $response = curl_exec($curl);
                curl_close($curl); 

               if($response['0']!=0) {
                    return  $this->response([
                        "status"   => false,
                        "message"   => "We are not able to Send OTP due to technical error.",
                        "data"      => []
                    ], 200);
                } else { 
                    return  $this->response([
                        "status"   => true,
                        "message"   => "OTP Sent Successfully",
                        "data"      => [
                            'otp'   => $otp
                        ]
                    ], REST_Controller::HTTP_OK); 
                }
            }
            else {
                return $this->response([
                    "status" => false,
                    "message" => "This phone number is not exist",
                ], REST_Controller::HTTP_NOT_FOUND);    
            }  
        }
        else {
            $this->response([
                'status'  => FALSE,
                'message' => "Invalid Request",
                'data'    => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function reset_password_post(){
        if ($this->input->server('REQUEST_METHOD') == 'POST') { 

            $this->form_validation->set_rules('phone', 'phone', 'required');
            $this->form_validation->set_rules('otp', 'OTP', 'trim|required|min_length[4]|max_length[4]|integer');
            $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[5]');
            $this->form_validation->set_rules('confirm_password', 'Password Confirmation', 'trim|required|matches[password]');

            if ($this->form_validation->run() == FALSE) {

                $this->response([
                    'status' => FALSE,
                    'message' => "Invalid Request",
                    'data' => [
                        'error' => $this->form_validation->error_array()
                    ]
                ], REST_Controller::HTTP_BAD_REQUEST);
            } else {
                
                $mobile = $this->input->post('phone');
                $otp = $this->input->post('otp'); 
                $new_password =  $this->input->post('password');

                $user_data = $this->user_model->verify_user($mobile);
                if (empty($user_data)) {
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Invalid user ID',
                        'data' => []
                    ], REST_Controller::HTTP_NOT_FOUND);
                }else{
     
                    if ($otp == $user_data['password_reset_code']) {
                            
                        $result = $this->auth_model->reset_pwd($new_password, $user_data['id']);
                        $this->response([
                            'status' => TRUE,
                            'message' =>  "Password Reset Successfully",
                            'data' => []
                        ], REST_Controller::HTTP_OK);
                    } else {
                        $this->response([
                            'status' => FALSE,
                            'message' => 'OTP not matches with our record.',
                            'data' => []
                        ], REST_Controller::HTTP_BAD_REQUEST);
                    }
                }
            }
        } else {
            $this->response([
                'status'  => FALSE,
                'message' => "Invalid Request",
                'data'    => []
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function settings_get()
    {          
        $result = $this->auth_model->settings();
        if(!empty($result->logo)){ $result->logo  = base_url()."assets/uploads/logos/".$result->logo; }
        if(!empty($result->logo)){ $result->logo2 = base_url()."assets/uploads/logos/".$result->logo2; }
        if(!empty($result->mobile_splish_logo)){ $result->mobile_splish_logo = base_url()."assets/uploads/logos/".$result->mobile_splish_logo; }
        if(!empty($result->mobile_login_logo)){ $result->mobile_login_logo   = base_url()."assets/uploads/logos/".$result->mobile_login_logo; }
        if(!empty($result->mobile_header_logo)){ $result->mobile_header_logo = base_url()."assets/uploads/logos/".$result->mobile_header_logo; }
        $this->response([
            'status' => TRUE,
            'message' => "Success",
            'data' => $result
        ], REST_Controller::HTTP_OK);
    }

    public function slide_images_get()
    {
        $folderPath = 'assets/uploads/slides'; // Replace with the actual folder path relative to your CodeIgniter root folder
        $imageURLs = [];
        if (is_dir($folderPath)) {
            $directoryIterator = new DirectoryIterator($folderPath);
            foreach ($directoryIterator as $fileInfo) {
                if ($fileInfo->isFile() && in_array(strtolower($fileInfo->getExtension()), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $imageURLs[] = base_url($folderPath . '/' . $fileInfo->getFilename());
                }
            }
        }
        $this->response([
            'status'  => TRUE,
            'message' => "Success",
            'data'    => $imageURLs
        ], REST_Controller::HTTP_OK);
    }
}  ?>