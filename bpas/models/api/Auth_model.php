<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model{
	public $tables           = [];
	protected $_ion_hooks;

	public function __construct() {
		parent::__construct();
		$this->load->config('ion_auth', true);
        
        //initialize db tables data
        $this->tables = $this->config->item('tables', 'ion_auth');

        //initialize data
        $this->identity_column = $this->config->item('identity', 'ion_auth');
        $this->store_salt      = $this->config->item('store_salt', 'ion_auth');
        $this->salt_length     = $this->config->item('salt_length', 'ion_auth');
        $this->join            = $this->config->item('join', 'ion_auth');

        //initialize hash method options (Bcrypt)
        $this->hash_method    = $this->config->item('hash_method', 'ion_auth');
        $this->default_rounds = $this->config->item('default_rounds', 'ion_auth');
        $this->random_rounds  = $this->config->item('random_rounds', 'ion_auth');
        $this->min_rounds     = $this->config->item('min_rounds', 'ion_auth');
        $this->max_rounds     = $this->config->item('max_rounds', 'ion_auth');

        //initialize messages and error
        $this->messages    = [];
        $this->errors      = [];
        $delimiters_source = $this->config->item('delimiters_source', 'ion_auth');

        //load the error delimeters either from the config file or use what's been supplied to form validation
        if ($delimiters_source === 'form_validation') {
            //load in delimiters from form_validation
            //to keep this simple we'll load the value using reflection since these properties are protected
            $this->load->library('form_validation');
            $form_validation_class = new ReflectionClass('CI_Form_validation');

            $error_prefix = $form_validation_class->getProperty('_error_prefix');
            $error_prefix->setAccessible(true);
            $this->error_start_delimiter   = $error_prefix->getValue($this->form_validation);
            $this->message_start_delimiter = $this->error_start_delimiter;

            $error_suffix = $form_validation_class->getProperty('_error_suffix');
            $error_suffix->setAccessible(true);
            $this->error_end_delimiter   = $error_suffix->getValue($this->form_validation);
            $this->message_end_delimiter = $this->error_end_delimiter;
        } else {
            //use delimiters from config
            $this->message_start_delimiter = $this->config->item('message_start_delimiter', 'ion_auth');
            $this->message_end_delimiter   = $this->config->item('message_end_delimiter', 'ion_auth');
            $this->error_start_delimiter   = $this->config->item('error_start_delimiter', 'ion_auth');
            $this->error_end_delimiter     = $this->config->item('error_end_delimiter', 'ion_auth');
        }

        //initialize our hooks object
        $this->_ion_hooks = new stdClass;

        //load the bcrypt class if needed
        if ($this->hash_method == 'bcrypt') {
            if ($this->random_rounds) {
                $rand   = rand($this->min_rounds, $this->max_rounds);
                $rounds = ['rounds' => $rand];
            } else {
                $rounds = ['rounds' => $this->default_rounds];
            }

            $this->load->library('bcrypt', $rounds);
        }

        $this->trigger_events('model_constructor');
	}
    
	// App User login
	public function login($data)
	{
		$this->db->from('users');
        $str = '(users.username="'.$data['username'].'" OR users.phone="'.$data['username'].'")';
		$this->db->where($str);
		$query = $this->db->get();

		if ($query->num_rows() == 0){
			return array('status' => 'FALSE', 'message' => "Invalid User Name!!", 'data' => '');
		}else{
			$result = $query->row_array();
			// Compare the password attempt with the password we have stored.
            $validPassword = $this->hash_password_db($result['id'], $data['password']);

            if($validPassword){

                $this->db->select('users.id, users.emp_id ,users.first_name, users.last_name, users.username, users.gender, users.company, users.phone, users.email, users.biller_id, users.active, users.avatar, users.nationality, users.position, users.employeed_date, users.user_type, users.basic_salary, users.is_remote_allow, hr_employees.candidate, hr_employees.photo, companies.name as company_name, hr_departments.name as department_name ,hr_positions.name as position_name, att_policies.policy as policy_name, users.avatar as image')
                ->join('companies', 'companies.id=users.biller_id', 'left') 
                ->join('hr_employees', 'hr_employees.id=users.emp_id', 'left') 
                ->join('hr_employees_working_info', 'hr_employees_working_info.employee_id=users.emp_id', 'left') 
                ->join('hr_departments', 'hr_departments.id=hr_employees_working_info.department_id', 'left') 
                ->join('hr_positions', 'hr_positions.id=hr_employees_working_info.position_id', 'left')
                ->join('att_policies', 'att_policies.id=hr_employees_working_info.policy_id', 'left');
                $query = $this->db->get_where('users', array('users.id' => $result['id']));
                $empresult = $query->row_array();

                return array('status'=>'TRUE', 'msg'=>"Password matched", 'data'=>$empresult);
            }else {
            	return array('status'=>'FALSE', 'msg'=>"Password not matched", 'data'=>'');
            }
		}	
	}

	public function hash_password_db($id, $password, $use_sha1_override = false)
    {
        if (empty($id) || empty($password)) {
            return false;
        }
        $this->trigger_events('extra_where');
        $query = $this->db->select('password, salt')
            ->where('id', $id)
            ->limit(1)
            ->get($this->tables['users']);

        $hash_password_db = $query->row();
        if ($query->num_rows() !== 1) {
            return false;
        }
        // bcrypt
        if ($use_sha1_override === false && $this->hash_method == 'bcrypt') {
            if ($this->bcrypt->verify($password, $hash_password_db->password)) {
                return true;
            }
            return false;
        }
        // sha1
        if ($this->store_salt) {
            $db_password = sha1($password . $hash_password_db->salt);
        } else {
            $salt = substr($hash_password_db->password, 0, $this->salt_length);

            $db_password = $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
        }

        if ($db_password == $hash_password_db->password) {
            return true;
        } else {
            return false;
        }
    }

    public function trigger_events($events)
    {
        if (is_array($events) && !empty($events)) {
            foreach ($events as $event) {
                $this->trigger_events($event);
            }
        } else {
            if (isset($this->_ion_hooks->$events) && !empty($this->_ion_hooks->$events)) {
                foreach ($this->_ion_hooks->$events as $name => $hook) {
                    $this->_call_hook($events, $name);
                }
            }
        }
    }

    
        public function reset_pwd($password, $user_id){
            $query = $this->db->get_where('users', array('id' => $user_id));
            $user = $query->row_array();

            $hashed_new_password = $this->hash_password($password, $user['salt']);

            $this->db->set('password', $hashed_new_password);
            $this->db->where('id', $user_id);
            $this->db->update('users');
            return 1;
        }

    // API change user password
        public function change_pwd($password, $oldpassword, $user_id){
            $query = $this->db->get_where('users', array('id' => $user_id));
            $user = $query->row_array();
            $old_password_matches = $this->hash_password_db($user_id, $oldpassword);
            if ($old_password_matches !== true) {
                return 2;
            }   

            $hashed_new_password = $this->hash_password($password, $user['salt']);

            $this->db->set('password', $hashed_new_password);
            $this->db->where('id', $user_id);
            $this->db->update('users');
            return 1;
        }

        public function hash_password($password, $salt = false, $use_sha1_override = false)
        {
            if (empty($password)) {
                return false;
            }
            //bcrypt
            if ($use_sha1_override === false && $this->hash_method == 'bcrypt') {
                return $this->bcrypt->hash($password);
            }
            if ($this->store_salt && $salt) {
                return sha1($password . $salt);
            } else {
                $salt = $this->salt();
                return $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
            }
        }

        public function salt()
        {
            return substr(md5(uniqid(rand(), true)), 0, $this->salt_length);
        }


        public function settings(){
            $q = $this->db->get_where("settings");
            if($q->num_rows() > 0){
                return $q->row();
            }
            return false;
        }
}
?>