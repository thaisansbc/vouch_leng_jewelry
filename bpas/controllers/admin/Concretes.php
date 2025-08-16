<?php defined('BASEPATH') or exit('No direct script access allowed');

class Concretes extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		if (!$this->loggedIn) {
			$this->session->set_userdata('requested_page', $this->uri->uri_string());
			$this->bpas->md('login');
		}
		$this->lang->admin_load('concretes', $this->Settings->user_language);
		$this->load->library('form_validation');
		$this->load->admin_model('concretes_model');
		$this->load->admin_model('settings_model');
		$this->load->admin_model('hr_model');
		$this->digital_upload_path = 'files/';
		$this->image_types = 'gif|jpg|jpeg|png|tif';
		$this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
		$this->allowed_file_size = '10240';
	}

	public function get_customer_locations()
	{
		$customer_id 		= $this->input->get('customer');
		$last_delivery 		= $this->input->get('last_delivery');
		$customer_credit 	= $this->input->get('customer_credit');
		$locations 			= $this->concretes_model->getCustomerLocations($customer_id);
		if ($last_delivery) {
			$last_delivery = $this->concretes_model->getLastDelivery($customer_id);
		} else {
			$last_delivery = false;
		}
		$credit = false;
		if ($customer_credit) {
			$customer = $this->site->getCompanyByID($customer_id);
			if ($customer->credit_day > 0 || $customer->credit_amount > 0 || $customer->credit_quantity > 0) {
				if ($customer->credit_day > 0) {
					$credit_sale = $this->concretes_model->getCreditSale($customer_id, $customer->credit_day);
					$credit_delivery = $this->concretes_model->getCreditDelivery($customer_id, $customer->credit_day);
					if (($credit_sale ? $credit_sale->balance : 0) + ($credit_delivery ? $credit_delivery->balance : 0) > 0) {
						$credit['credit_day'] = ($credit_sale ? $credit_sale->balance : 0) + ($credit_delivery ? $credit_delivery->balance : 0);
					} else {
						$credit['credit_day'] = false;
					}
				}
				if ($customer->credit_amount > 0 || $customer->credit_quantity > 0) {
					$credit_sale = $this->concretes_model->getCreditSale($customer_id);
					$credit_delivery = $this->concretes_model->getCreditDelivery($customer_id);
					if ($customer->credit_amount > 0) {
						if (($credit_sale ? $credit_sale->balance : 0) + ($credit_delivery ? $credit_delivery->balance : 0) > $customer->credit_amount) {
							$credit['credit_amount'] = ($credit_sale ? $credit_sale->balance : 0) + ($credit_delivery ? $credit_delivery->balance : 0);
						} else {
							$credit['credit_amount'] = false;
						}
					}
					if ($customer->credit_quantity > 0) {
						if (($credit_sale ? $credit_sale->balance_qty : 0) + ($credit_delivery ? $credit_delivery->balance_qty : 0) > $customer->credit_quantity) {
							$credit['credit_quantity'] = ($credit_sale ? $credit_sale->balance_qty : 0) + ($credit_delivery ? $credit_delivery->balance_qty : 0);
						} else {
							$credit['credit_quantity'] = false;
						}
					}
				}
			}
		}
		$data = array('locations' => $locations, 'last_delivery' => $last_delivery, 'credit' => $credit);
		echo json_encode($data);
	}
	public function get_customer_quotation()
	{
		$customer_id = $this->input->get('customer');
		$quotations = $this->concretes_model->getCustomerQuotations($customer_id);
		$data = array('quotations' => $quotations);
		echo json_encode($data);
	}
	public function drivers()
	{
		$this->bpas->checkPermissions('drivers');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => 'concretes', 'page' => lang('concrete')), array('link' => '#', 'page' => lang('drivers')));
		$meta = array('page_title' => lang('drivers'), 'bc' => $bc);
		$this->page_construct('concretes/drivers', $meta, $this->data);
	}

	public function getDrivers()
	{
		$this->bpas->checkPermissions('drivers');
		$this->load->library('datatables');
		$this->datatables
			->select("con_drivers.id as id,
						con_drivers.full_name_kh,
						con_drivers.full_name,
						con_drivers.phone,
						DATE_FORMAT(start_date, '%Y-%m-%d') as start_date,
						con_drivers.address,
						con_drivers.note,
						con_drivers.status,
						con_drivers.attachment,
						")
			->from("con_drivers")
			->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_driver") . "' href='" . admin_url('concretes/edit_driver/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_driver") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('concretes/delete_driver/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
		echo $this->datatables->generate();
	}

	public function add_driver()
	{
		$this->bpas->checkPermissions('drivers', true);
		$this->form_validation->set_rules('full_name_kh', lang("full_name_kh"), 'required');
		$this->form_validation->set_rules('full_name', lang("full_name"), 'required');
		if ($this->form_validation->run() == true) {
			$data = array(
				'full_name_kh' => $this->input->post('full_name_kh'),
				'full_name' => $this->input->post('full_name'),
				'phone' => $this->input->post('phone'),
				'address' => $this->input->post('address'),
				'note' => $this->input->post('note'),
				'status' => 'active',
				'start_date' => $this->bpas->fsd(trim($this->input->post('start_date')))
			);
			if ($_FILES['attachment']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('attachment')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		} elseif ($this->input->post('add_driver')) {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect('concretes/drivers');
		}

		if ($this->form_validation->run() == true && $id = $this->concretes_model->addDriver($data)) {
			$this->session->set_flashdata('message', $this->lang->line("driver_added"));
			admin_redirect('concretes/drivers');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'concretes/add_driver', $this->data);
		}
	}

	public function edit_driver($id = false)
	{
		$this->bpas->checkPermissions('drivers', true);
		$driver = $this->concretes_model->getDriverByID($id);
		$this->form_validation->set_rules('full_name_kh', lang("full_name_kh"), 'required');
		$this->form_validation->set_rules('full_name', lang("full_name"), 'required');
		if ($this->form_validation->run('concretes_model/addAccount') == true) {
			$data = array(
				'full_name_kh' => $this->input->post('full_name_kh'),
				'full_name' => $this->input->post('full_name'),
				'phone' => $this->input->post('phone'),
				'address' => $this->input->post('address'),
				'note' => $this->input->post('note'),
				'status' => $this->input->post('status'),
				'start_date' => $this->bpas->fsd(trim($this->input->post('start_date'))),
				'end_date' => $this->bpas->fsd(trim($this->input->post('end_date')))
			);

			$update_trucks = false;
			if ($this->input->post('status') == "inactive") {
				$trucks = $this->concretes_model->getTrucks();
				if ($trucks) {
					foreach ($trucks as $truck) {
						if ($truck->driver_id == $id || ($truck->driver_assistant && json_decode($truck->driver_assistant))) {
							if ($truck->driver_id == $id) {
								$truck->driver_id = "";
							}
							if ($truck->driver_assistant && json_decode($truck->driver_assistant)) {
								$driver_assistants = json_decode($truck->driver_assistant);
								$assistants = false;
								foreach ($driver_assistants as $driver_assistant) {
									if ($driver_assistant != $id) {
										$assistants[] = $driver_assistant;
									}
								}
								$truck->driver_assistant = json_encode($assistants);
							}
							$update_trucks[$truck->id] = $truck;
						}
					}
				}
			}

			if ($_FILES['attachment']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('attachment')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		} elseif ($this->input->post('edit_driver')) {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect('concretes/drivers');
		}

		if ($this->form_validation->run() == true && $id = $this->concretes_model->updateDriver($id, $data, $update_trucks)) {
			$this->session->set_flashdata('message', $this->lang->line("driver_edited"));
			admin_redirect('concretes/drivers');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['driver'] = $driver;
			$this->load->view($this->theme . 'concretes/edit_driver', $this->data);
		}
	}

	public function delete_driver($id = NULL)
	{
		$this->bpas->checkPermissions('drivers', true);
		if ($this->concretes_model->deleteDriver($id)) {
			echo $this->lang->line("driver_deleted");
		} else {
			$this->session->set_flashdata('warning', lang('driver_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : admin_url('welcome')) . "'; }, 0);</script>");
		}
	}

	public function driver_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('drivers');
					$error = false;
					foreach ($_POST['val'] as $id) {
						if (!$this->concretes_model->deleteDriver($id)) {
							$error = true;
						}
					}
					if ($error) {
						$this->session->set_flashdata('warning', lang('driver_cannot_delete'));
					} else {
						$this->session->set_flashdata('message', $this->lang->line("driver_deleted"));
					}
					redirect($_SERVER["HTTP_REFERER"]);
				}
				if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('drivers'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('full_name_kh'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('full_name'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('start_date'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('address'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('note'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));
					$row = 2;
					foreach ($_POST['val'] as $id) {
						$driver = $this->concretes_model->getDriverByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $driver->full_name_kh);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $driver->full_name);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $driver->phone);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->hrsd($driver->start_date));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->remove_tag($driver->address));
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->remove_tag($driver->note));
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($driver->status));

						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'drivers_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_account_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function officers()
	{
		$this->bpas->checkPermissions('officers');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => 'concretes', 'page' => lang('concrete')), array('link' => '#', 'page' => lang('officers')));
		$meta = array('page_title' => lang('officers'), 'bc' => $bc);
		$this->page_construct('concretes/officers', $meta, $this->data);
	}

	public function getOfficers()
	{
		$this->bpas->checkPermissions('officers');
		$this->load->library('datatables');
		$this->datatables
			->select("hr_employees.id as id,
						CONCAT(" . $this->db->dbprefix('hr_employees') . ".lastname_kh,' '," . $this->db->dbprefix('hr_employees') . ".firstname_kh) as name_kh,
						CONCAT(" . $this->db->dbprefix('hr_employees') . ".lastname,' '," . $this->db->dbprefix('hr_employees') . ".firstname) as name,
						hr_employees.phone,

						{$this->db->dbprefix('hr_positions')}.name as position,
						DATE_FORMAT({$this->db->dbprefix('hr_employees_working_info')}.employee_date, '%Y-%m-%d') as start_date,
						hr_employees.address,
						hr_employees.note,
						{$this->db->dbprefix('hr_employees_working_info')}.status,
						{$this->db->dbprefix('hr_employees')}.photo
						")
			->from("hr_employees")
			->join("hr_employees_working_info", "hr_employees_working_info.employee_id = hr_employees.id", "left")
			->join("hr_positions", "hr_employees_working_info.position_id = hr_positions.id", "left")

			->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_officer") . "' href='" . admin_url('concretes/edit_officer/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_officer") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('concretes/delete_officer/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");

		$this->datatables->where("hr_employees.module_type", "concrete");

		echo $this->datatables->generate();
	}
	public function add_officer()
	{
		$this->bpas->checkPermissions('officers', true);
		$this->form_validation->set_rules('firstname', lang("firstname"), 'required');
		$this->form_validation->set_rules('lastname', lang("lastname"), 'required');
		if ($this->form_validation->run() == true) {

			$data = array(
				'firstname' 	=> $this->input->post('firstname'),
				'lastname' 		=> $this->input->post('lastname'),
				'firstname_kh' 	=> $this->input->post('firstname_kh'),
				'lastname_kh' 	=> $this->input->post('lastname_kh'),
				'phone' 		=> $this->input->post('phone'),
				'address' 		=> $this->input->post('address'),
				'note' 			=> $this->input->post('note'),
				'operator' 		=> $this->input->post('operator'),
				'commission_rate' => $this->input->post('commission_rate'),
				'module_type'	=> 'concrete'
			);
			$data_info = array(
				'biller_id' 	=> $this->input->post('biller'),
				'position_id' 	=> $this->input->post('position'),
				'employee_date' => $this->bpas->fsd(trim($this->input->post('start_date'))),
				'status' 		=> 'active',
			);

			if ($_FILES['attachment']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('attachment')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['photo'] = $photo;
			}
		} elseif ($this->input->post('add_officer')) {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect('concretes/officers');
		}

		if ($this->form_validation->run() == true && $id = $this->concretes_model->addOfficer($data, $data_info)) {
			$this->session->set_flashdata('message', $this->lang->line("officer_added"));
			admin_redirect('concretes/officers');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['billers'] =  $this->site->getBillers();
			$this->data['positions'] = $this->hr_model->getAllPositions();
			$this->load->view($this->theme . 'concretes/add_officer', $this->data);
		}
	}

	public function edit_officer($id = false)
	{
		$this->bpas->checkPermissions('officers', true);

		$officer = $this->hr_model->getEmployeesInfoByEmployeeID($id);
		$this->form_validation->set_rules('firstname', lang("firstname"), 'required');
		$this->form_validation->set_rules('lastname', lang("lastname"), 'required');

		if ($this->form_validation->run() == true) {
			$data = array(
				'firstname' 	=> $this->input->post('firstname'),
				'lastname' 		=> $this->input->post('lastname'),
				'firstname_kh' 	=> $this->input->post('firstname_kh'),
				'lastname_kh' 	=> $this->input->post('lastname_kh'),
				'phone' 		=> $this->input->post('phone'),
				'address' 		=> $this->input->post('address'),
				'note' 			=> $this->input->post('note'),
				'operator' 		=> $this->input->post('operator'),
				'commission_rate' => $this->input->post('commission_rate')
			);
			$data_info = array(
				'biller_id' 	=> $this->input->post('biller'),
				'employee_date' => $this->bpas->fsd(trim($this->input->post('start_date'))),
				'status' 		=> 'active',
				'position_id' 	=> $this->input->post('position'),
			);

			if ($_FILES['attachment']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('attachment')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['photo'] = $photo;
			}
		} elseif ($this->input->post('edit_officer')) {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect('concretes/officers');
		}

		if ($this->form_validation->run() == true && $id = $this->concretes_model->updateOfficer($id, $data, $data_info)) {
			$this->session->set_flashdata('message', $this->lang->line("officer_edited"));
			admin_redirect('concretes/officers');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['officer'] = $officer;
			$this->data['positions'] = $this->hr_model->getAllPositions();
			$this->data['billers'] =  $this->site->getBillers();
			$this->load->view($this->theme . 'concretes/edit_officer', $this->data);
		}
	}

	public function delete_officer($id = NULL)
	{
		$this->bpas->checkPermissions('officers', true);
		if ($this->concretes_model->deleteOfficer($id)) {
			$this->bpas->send_json(['error' => 0, 'msg' => lang('officer_deleted')]);
		} else {
			$this->session->set_flashdata('warning', lang('officer_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : admin_url('welcome')) . "'; }, 0);</script>");
		}
	}

	public function officer_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('officers');
					$error = false;
					foreach ($_POST['val'] as $id) {
						if (!$this->concretes_model->deleteOfficer($id)) {
							$error = true;
						}
					}
					if ($error) {
						$this->session->set_flashdata('warning', lang('officer_cannot_delete'));
					} else {
						$this->session->set_flashdata('message', $this->lang->line("officer_deleted"));
					}
					redirect($_SERVER["HTTP_REFERER"]);
				}
				if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('officers'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('full_name_kh'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('full_name'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('position'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('start_date'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('address'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('note'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));
					$row = 2;
					foreach ($_POST['val'] as $id) {
						$officer = $this->concretes_model->getOfficerByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $officer->full_name_kh);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $officer->full_name);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $officer->phone);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $officer->position);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->hrsd($officer->start_date));
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->remove_tag($officer->address));
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->remove_tag($officer->note));
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($officer->status));
						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'officers_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_account_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function trucks()
	{
		$this->bpas->checkPermissions('trucks');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => 'concretes', 'page' => lang('concrete')), array('link' => '#', 'page' => lang('trucks')));
		$meta = array('page_title' => lang('trucks'), 'bc' => $bc);
		$this->page_construct('concretes/trucks', $meta, $this->data);
	}
	public function getTrucks()
	{
		$this->bpas->checkPermissions('trucks');
		$this->load->library('datatables');
		$this->datatables
			->select("con_trucks.id as id,
						con_trucks.type,
						con_trucks.code,
						con_trucks.plate,
						CONCAT(" . $this->db->dbprefix('companies') . ".company,' - '," . $this->db->dbprefix('companies') . ".name) as driver,
						con_trucks.note,
						con_trucks.attachment,
						")
			->from("con_trucks")
			->join("companies", "companies.id = con_trucks.driver_id", "left")
			->where('companies.group_name', 'driver')
			->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_truck") . "' href='" . admin_url('concretes/edit_truck/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_truck") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('concretes/delete_truck/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
		echo $this->datatables->generate();
	}
	public function add_truck()
	{
		$this->bpas->checkPermissions('trucks', true);
		$this->form_validation->set_rules('plate', lang("plate"), 'required');
		$this->form_validation->set_rules('diesel_id', lang("diesel"), 'required');
		$this->form_validation->set_rules('code', lang("code"), 'is_unique[con_trucks.code]');
		if ($this->form_validation->run() == true) {
			$data = array(
				'type' => $this->input->post('type'),
				'code' => $this->input->post('code'),
				'plate' => $this->input->post('plate'),
				'diesel_id' => $this->input->post('diesel_id'),
				'driver_id' => $this->input->post('driver_id'),
				'driver_assistant' => json_encode($this->input->post('driver_assistant')),
				'driver_fuel_fee' => $this->input->post('driver_fuel_fee'),
				'assistant_fuel_fee' => $this->input->post('assistant_fuel_fee'),
				'in_range_km' => $this->input->post('in_range_km'),
				'in_range_litre' => $this->input->post('in_range_litre'),
				'out_range_km' => $this->input->post('out_range_km'),
				'out_range_litre' => $this->input->post('out_range_litre'),
				'out_range_km' => $this->input->post('out_range_km'),
				'm3' => $this->input->post('m3'),
				'litre' => $this->input->post('litre'),
				'moving_from' => $this->input->post('moving_from'),
				'moving_to' => $this->input->post('moving_to'),
				'moving_litre' => $this->input->post('moving_litre'),
				'moving_from2' => $this->input->post('moving_from2'),
				'moving_to2' => $this->input->post('moving_to2'),
				'moving_litre2' => $this->input->post('moving_litre2'),
				'waiting_from' => $this->input->post('waiting_from'),
				'waiting_to' => $this->input->post('waiting_to'),
				'waiting_litre' => $this->input->post('waiting_litre'),
				'waiting_from2' => $this->input->post('waiting_from2'),
				'waiting_to2' => $this->input->post('waiting_to2'),
				'waiting_litre2' => $this->input->post('waiting_litre2'),
				'big_truck' => $this->input->post('big_truck'),
				'note'		=> $this->input->post('note'),
			);
			if ($_FILES['attachment']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('attachment')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		} elseif ($this->input->post('add_truck')) {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect('concretes/trucks');
		}

		if ($this->form_validation->run() == true && $id = $this->concretes_model->addTruck($data)) {
			$this->session->set_flashdata('message', $this->lang->line("truck_added"));
			admin_redirect('concretes/trucks');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['drivers'] = $this->site->getAllCompanies('driver');
			$this->data['diesels'] = $this->concretes_model->getProducts();
			$this->load->view($this->theme . 'concretes/add_truck', $this->data);
		}
	}
	public function edit_truck($id = false)
	{
		$this->bpas->checkPermissions('trucks', true);
		$truck = $this->concretes_model->getTruckByID($id);
		$this->form_validation->set_rules('plate', lang("plate"), 'required');
		$this->form_validation->set_rules('diesel_id', lang("diesel"), 'required');
		$this->form_validation->set_rules('code', lang("code"), 'required');
		if ($this->input->post('code') !== $truck->code) {
			$this->form_validation->set_rules('code', lang("code"), 'is_unique[con_trucks.code]');
		}
		if ($this->form_validation->run('concretes_model/addAccount') == true) {
			$data = array(
				'type' 		=> $this->input->post('type'),
				'code' 		=> $this->input->post('code'),
				'plate' 	=> $this->input->post('plate'),
				'diesel_id' => $this->input->post('diesel_id'),
				'driver_id' => $this->input->post('driver_id'),
				'driver_assistant' => json_encode($this->input->post('driver_assistant')),
				'driver_fuel_fee' => $this->input->post('driver_fuel_fee'),
				'assistant_fuel_fee' => $this->input->post('assistant_fuel_fee'),
				'in_range_km' => $this->input->post('in_range_km'),
				'in_range_litre' => $this->input->post('in_range_litre'),
				'out_range_km' => $this->input->post('out_range_km'),
				'out_range_litre' => $this->input->post('out_range_litre'),
				'm3' 		=> $this->input->post('m3'),
				'litre' 		=> $this->input->post('litre'),
				'moving_from' => $this->input->post('moving_from'),
				'moving_to' => $this->input->post('moving_to'),
				'moving_litre' => $this->input->post('moving_litre'),
				'moving_from2' => $this->input->post('moving_from2'),
				'moving_to2' => $this->input->post('moving_to2'),
				'moving_litre2' => $this->input->post('moving_litre2'),
				'waiting_from' => $this->input->post('waiting_from'),
				'waiting_to' => $this->input->post('waiting_to'),
				'waiting_litre' => $this->input->post('waiting_litre'),
				'waiting_from2' => $this->input->post('waiting_from2'),
				'waiting_to2' => $this->input->post('waiting_to2'),
				'waiting_litre2' => $this->input->post('waiting_litre2'),
				'big_truck' => $this->input->post('big_truck'),
				'note'		=> $this->input->post('note'),
			);
			if ($_FILES['attachment']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('attachment')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		} elseif ($this->input->post('edit_truck')) {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect('concretes/trucks');
		}

		if ($this->form_validation->run() == true && $id = $this->concretes_model->updateTruck($id, $data)) {
			$this->session->set_flashdata('message', $this->lang->line("truck_edited"));
			admin_redirect('concretes/trucks');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['drivers']  = $this->site->getAllCompanies('driver');
			$this->data['truck']    = $truck;
			$this->data['diesels']  = $this->concretes_model->getProducts();
			$this->load->view($this->theme . 'concretes/edit_truck', $this->data);
		}
	}

	public function delete_truck($id = NULL)
	{
		$this->bpas->checkPermissions('trucks', true);
		if ($this->concretes_model->deleteTruck($id)) {
			echo $this->lang->line("truck_deleted");
		} else {
			$this->session->set_flashdata('warning', lang('truck_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : admin_url('welcome')) . "'; }, 0);</script>");
		}
	}
	public function truck_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('trucks');
					$error = false;
					foreach ($_POST['val'] as $id) {
						if (!$this->concretes_model->deleteTruck($id)) {
							$error = true;
						}
					}
					if ($error) {
						$this->session->set_flashdata('warning', lang('truck_cannot_delete'));
					} else {
						$this->session->set_flashdata('message', $this->lang->line("truck_deleted"));
					}
					redirect($_SERVER["HTTP_REFERER"]);
				}
				if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('trucks'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('type'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('plate'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('driver'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
					$row = 2;
					foreach ($_POST['val'] as $id) {
						$truck = $this->concretes_model->getTruckByID($id);
						$driver = $this->concretes_model->getDriverByID($truck->driver_id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, lang($truck->type));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $truck->code);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $truck->plate);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $driver->full_name . ' - ' . $driver->full_name_kh);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->remove_tag($truck->note));

						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'trucks_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_account_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}


	public function slumps($code = NULL)
	{
		$page = $this->uri->segment(3);
		$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
		$this->data['constants'] = $this->settings_model->getParentCustomField();
		$bc                  = [
			['link' => admin_url(), 'page' => lang('home')],
			['link' => admin_url('system_settings'), 'page' => lang('system_settings')],
			['link' => '#', 'page' => lang($page)]
		];
		$this->data['page'] = $page;
		$meta                = ['page_title' => lang($page), 'bc' => $bc];
		$this->page_construct('settings/custom_field_by_code', $meta, $this->data);
	}

	public function slump_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('slumps');
					$error = false;
					foreach ($_POST['val'] as $id) {
						if (!$this->concretes_model->deleteSlump($id)) {
							$error = true;
						}
					}
					if ($error) {
						$this->session->set_flashdata('warning', lang('slump_cannot_delete'));
					} else {
						$this->session->set_flashdata('message', $this->lang->line("slump_deleted"));
					}
					redirect($_SERVER["HTTP_REFERER"]);
				}
				if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('slumps'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('note'));

					$row = 2;
					foreach ($_POST['val'] as $id) {
						$slump = $this->concretes_model->getSlumpByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $slump->name);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->remove_tag($slump->note));

						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'slumps_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_account_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function casting_types($action = NULL)
	{
		$this->bpas->checkPermissions('casting_types');
		$page = $this->uri->segment(3);
		$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
		$this->data['constants'] = $this->settings_model->getParentCustomField();
		$bc                  = [
			['link' => admin_url(), 'page' => lang('home')],
			['link' => admin_url('system_settings'), 'page' => lang('system_settings')],
			['link' => '#', 'page' => lang($page)]
		];
		$this->data['page'] = $page;
		$meta                = ['page_title' => lang($page), 'bc' => $bc];
		$this->page_construct('settings/custom_field_by_code', $meta, $this->data);
	}

	public function casting_type_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('casting_types');
					$error = false;
					foreach ($_POST['val'] as $id) {
						if (!$this->concretes_model->deleteCastingType($id)) {
							$error = true;
						}
					}
					if ($error) {
						$this->session->set_flashdata('warning', lang('casting_type_cannot_delete'));
					} else {
						$this->session->set_flashdata('message', $this->lang->line("casting_type_deleted"));
					}
					redirect($_SERVER["HTTP_REFERER"]);
				}
				if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('casting_types'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('note'));

					$row = 2;
					foreach ($_POST['val'] as $id) {
						$casting_type = $this->concretes_model->getCastingTypeByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $casting_type->name);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->remove_tag($casting_type->note));
						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'casting_types_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_account_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function index($warehouse_id = null, $biller_id = NULL)
	{
		$this->bpas->checkPermissions("deliveries");
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('deliveries')));
		$meta = array('page_title' => lang('deliveries'), 'bc' => $bc);
		$this->page_construct('concretes/index', $meta, $this->data);
	}

	public function getDeliveries($warehouse_id = null, $biller_id = NULL)
	{
		$this->bpas->checkPermissions('deliveries');
		$edit_link = anchor('admin/concretes/edit_delivery/$1', '<i class="fa fa-edit"></i> ' . lang('edit_delivery'), ' class="edit_delivery" ');
		$delete_link = "<a href='#' class='po delete_delivery' title='<b>" . $this->lang->line("delete_delivery") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('concretes/delete_delivery/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_delivery') . "</a>";
		$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';

		$this->load->library('datatables');

		$this->datatables
			->select("id, date, reference_no, customer,location_name,seal_number,truck_code,driver_name,pump_code,weight_status,status")
			->from("con_deliveries");
		if ($warehouse_id) {
			$this->datatables->where('con_deliveries.warehouse_id', $warehouse_id);
		}
		if ($biller_id) {
			$this->datatables->where('con_deliveries.biller_id', $biller_id);
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('con_deliveries.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('con_deliveries.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('con_deliveries.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
	}

	public function add_delivery()
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('departure_time', $this->lang->line("departure_time"), 'required');
		$this->form_validation->set_rules('location', $this->lang->line("location"), 'required');
		$this->form_validation->set_rules('truck', $this->lang->line("truck"), 'required');
		$this->form_validation->set_rules('slump', $this->lang->line("slump"), 'required');
		$this->form_validation->set_rules('casting_type', $this->lang->line("casting_type"), 'required');
		$this->form_validation->set_rules('stregth', $this->lang->line("stregth"), 'required');
		$this->form_validation->set_rules('quantity', $this->lang->line("quantity"), 'required');
		$this->form_validation->set_rules('operator', $this->lang->line("operator"), 'required');
		$this->form_validation->set_rules('pump', $this->lang->line("pump"), 'required');
		if ($this->form_validation->run() == true) {
			$used_fuels  = false;
			$on_time     = true;
			$biller_id   = $this->input->post('biller');
			$project_id  = $this->input->post('project');
			$seal_number = $this->input->post('seal_number');
			if ($this->Owner || $this->Admin || $this->GP['change_date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
				if ($this->bpas->fsd(trim($this->input->post('date'))) != date("Y-m-d")) {
					$on_time = false;
				}
			} else {
				$date = date('Y-m-d H:i:s');
			}
			$truck_id 		  = $this->input->post('truck');
			$truck 			  = $this->concretes_model->getTruckByID($truck_id);
			$departure_time   = $this->input->post('departure_time');
			$warehouse_id 	  = $this->input->post('warehouse');
			$customer_id 	  = $this->input->post('customer');
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer 		  = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
			$biller_details   = $this->site->getCompanyByID($biller_id);
			$biller 		  = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$note 			  = $this->bpas->clear_tags($this->input->post('note'));
			$location_id 	  = $this->input->post('location');
			$driver_id 		  = $truck->driver_id;
			$slump_id 		  = $this->input->post('slump');
			$casting_type_id  = $this->input->post('casting_type');
			$stregth_id 	  = $this->input->post('stregth');
			$quantity 		  = $this->input->post('quantity');
			$pump_id 		  = $this->input->post('pump');
			$markup_qty 	  = $this->input->post('markup_qty');
			$operator_id 	  = $this->input->post('operator');
			$pump 			  = $this->concretes_model->getTruckByID($pump_id);
			$pump_driver_id   = $pump->driver_id;
			$location 		  = $this->concretes_model->getCustomerLocationByID($location_id);
			/*if ($location) {
				$kilometer 	= $this->concretes_model->getBillerKilometer($biller_id,$location_id);
				if($kilometer && $kilometer->kilometer > 0){
					$location->kilometer = $kilometer->kilometer;
				}
			}*/
			$driver       = $this->concretes_model->getDriverByID($driver_id);
			$slump        = $this->concretes_model->getSlumpByID($slump_id);
			$casting_type = $this->concretes_model->getCastingTypeByID($casting_type_id);
			$stregth      = $this->concretes_model->getStregthByID($stregth_id);
			if (!$this->Owner && !$this->Admin && !$this->bpas->GP['concretes-skip-so']) {
				$sale_order = $this->concretes_model->getSOQuantity($biller_id, $customer_id, $location_id, $date, $stregth_id);
				if ($this->bpas->formatDecimal($sale_order->quantity) < $this->bpas->formatDecimal($quantity)) {
					$this->session->set_flashdata('error', lang('dn_qty_more_so_qty'));
					$this->bpas->md();
				}
			}
			$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cdn', $biller_id);
			$pump_driver = false;
			$driver_assistant = '';
			$pump_move = 0;
			if ($pump) {
				$pump_driver = $this->concretes_model->getDriverByID($pump_driver_id);
				$driver_assistant = $pump->driver_assistant;
				$pump_move = $this->input->post('pump_move');
			}
			$total_cost = 0;
			$data = array(
				'date' 			    => $date,
				'reference_no' 	    => $reference,
				'customer_id' 	    => $customer_id,
				'customer_code'     => $customer_details->code,
				'customer' 		    => $customer,
				'biller_id' 	    => $biller_id,
				'biller' 		    => $biller,
				'project_id' 	    => $project_id,
				'warehouse_id' 	    => $warehouse_id,
				'note' 			    => $note,
				'departure_time'    => $departure_time,
				'seal_number' 	    => $seal_number,
				'location_id' 	    => $location_id,
				'location_name'     => $location->line1,
				'site_person' 	    => $location->contact_person,
				'site_number' 	    => $location->phone,
				'kilometer' 	    => $location->kilometer,
				'driver_id' 	    => $driver_id,
				'driver_name' 	    => $driver->name_kh . " - " . $driver->name,
				'truck_id' 		    => $truck_id,
				'truck_code' 	    => $truck->plate,
				'slump_id' 		    => $slump_id,
				'slump_name' 	    => $slump->name,
				'casting_type_id'   => $casting_type_id,
				'casting_type_name' => $casting_type->name,
				'stregth_id' 	    => $stregth_id,
				'stregth_name' 	    => $stregth->name,
				'pump_id' 		    => $pump_id,
				'pump_code' 	    => $pump->plate,
				'pump_driver_id'    => $pump_driver_id,
				'pump_driver_name'  => ($pump_driver ? $pump_driver->name_kh . " - " . $pump_driver->name : ''),
				'pump_move' 	    => $pump_move,
				'markup_qty' 	    => $markup_qty,
				'driver_assistant'  => $driver_assistant,
				'quantity' 		    => $quantity,
				'operator_id' 	    => $operator_id,
				'group_id' 		    => $this->input->post("group"),
				'created_by' 	    => $this->session->userdata('user_id'),
				'created_at' 	    => date('Y-m-d H:i:s'),
			);
			if ($this->Settings->fuel_expenses) {
				if ($truck->in_range_km < $location->kilometer) {
					$out_range_litre = (($location->kilometer - $truck->in_range_km) / $truck->out_range_km) * $truck->out_range_litre;
				} else {
					$out_range_litre = 0;
				}
				$in_range_litre = $truck->in_range_litre;
				$used_fuels[] = array(
					'date' 		      => $date,
					'biller_id'       => $biller_id,
					'driver_id'       => $driver_id,
					'truck_id'        => $truck_id,
					'kilometer'       => $location->kilometer,
					'in_range_litre'  => ($in_range_litre > 0 ? (($in_range_litre * $truck->driver_fuel_fee) / 100) : 0),
					'out_range_litre' => ($out_range_litre > 0 ? (($out_range_litre * $truck->driver_fuel_fee) / 100) : 0),
					'pump_litre'      => 0
				);
				if ($truck->driver_assistant && $truck->driver_fuel_fee != 100) {
					$driver_assistants = json_decode($truck->driver_assistant);
					$total_asstants = count($driver_assistants);
					foreach ($driver_assistants as $driver_assistant) {
						$used_fuels[] = array(
							'date' 		      => $date,
							'biller_id'       => $biller_id,
							'driver_id'       => $driver_assistant,
							'truck_id' 	      => $truck_id,
							'kilometer'       => $location->kilometer,
							'in_range_litre'  => ($in_range_litre > 0 ? ((($in_range_litre * $truck->assistant_fuel_fee) / 100) / $total_asstants) : 0),
							'out_range_litre' => ($out_range_litre > 0 ? ((($out_range_litre * $truck->assistant_fuel_fee) / 100) / $total_asstants) : 0),
							'pump_litre' 	  => 0
						);
					}
				}
				if ($pump) {
					$in_range_litre = 0;
					$out_range_litre = 0;
					if ($pump_move > 0) {
						if ($pump->in_range_km < $location->kilometer) {
							$out_range_litre = (($location->kilometer - $pump->in_range_km) / $pump->out_range_km) * $pump->out_range_litre;
						}
						$in_range_litre = $pump->in_range_litre;
					}
					$pump_litre = ($quantity / $pump->m3) * $pump->litre;
					$used_fuels[] = array(
						'date'       	  => $date,
						'biller_id'  	  => $biller_id,
						'driver_id'  	  => $pump_driver_id,
						'truck_id'        => $pump_id,
						'kilometer'       => $location->kilometer,
						'in_range_litre'  => ($in_range_litre > 0 ? (($in_range_litre * $pump->driver_fuel_fee) / 100) : 0),
						'out_range_litre' => ($out_range_litre > 0 ? (($out_range_litre * $pump->driver_fuel_fee) / 100) : 0),
						'pump_litre' 	  => ($pump_litre > 0 ? (($pump_litre * $pump->driver_fuel_fee) / 100) : 0)
					);
					if ($pump->driver_assistant && $pump->driver_fuel_fee != 100) {
						$driver_assistants = json_decode($pump->driver_assistant);
						$total_asstants = count($driver_assistants);
						foreach ($driver_assistants as $driver_assistant) {
							$used_fuels[] = array(
								'date' 		      => $date,
								'biller_id'       => $biller_id,
								'driver_id'       => $driver_assistant,
								'truck_id'        => $pump_id,
								'kilometer'       => $location->kilometer,
								'in_range_litre'  => ($in_range_litre > 0 ? ((($in_range_litre * $pump->assistant_fuel_fee) / 100) / $total_asstants) : 0),
								'out_range_litre' => ($out_range_litre > 0 ? ((($out_range_litre * $pump->assistant_fuel_fee) / 100) / $total_asstants) : 0),
								'pump_litre' 	  => ($pump_litre > 0 ? ((($pump_litre * $pump->assistant_fuel_fee) / 100) / $total_asstants) : 0)
							);
						}
					}
				}
			}
			$stockmoves = false;
			if ($stregth->type == 'bom') {
				$product_boms = $this->concretes_model->getBomProductByStandProduct($stregth_id);
				if ($product_boms) {
					foreach ($product_boms as $product_bom) {
						if ($this->Settings->accounting_method == '0') {
							$costs = $this->site->getFifoCost($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves);
						} else if ($this->Settings->accounting_method == '1') {
							$costs = $this->site->getLifoCost($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves);
						} else if ($this->Settings->accounting_method == '3') {
							$costs = $this->site->getProductMethod($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves);
						}
						$productAcc = $this->site->getProductAccByProductId($product_bom->product_id);
						if (isset($costs) && !empty($costs)) {
							foreach ($costs as $cost_item) {
								$total_cost += $cost_item['cost'] * $cost_item['quantity'];
								$stockmoves[] = array(
									'transaction'    => 'CDelivery',
									'product_id'     => $product_bom->product_id,
									'product_type'   => $product_bom->product_type,
									'product_code'   => $product_bom->product_code,
									'product_name'   => $product_bom->product_name,
									'quantity' 	     => $cost_item['quantity'] * (-1),
									'unit_quantity'  => $product_bom->unit_qty,
									'unit_code'	     => $product_bom->code,
									'unit_id'  	     => $product_bom->unit_id,
									'warehouse_id'   => $warehouse_id,
									'date' 			 => $date,
									'real_unit_cost' => $cost_item['cost'],
									'reference_no'   => $reference,
									'user_id' 		 => $this->session->userdata('user_id'),
								);
								if ($this->Settings->accounting == 1) {
									$accTrans[] = array(
										'tran_type'    => 'CDelivery',
										'tran_date'    => $date,
										'reference_no' => $reference,
										'account_code' => $this->accounting_setting->default_stock,
										'amount' 	   => - ($cost_item['cost'] * $cost_item['quantity']),
										'narrative'    => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . $cost_item['quantity'] . '#' . 'Cost: ' . $cost_item['cost'],
										'biller_id'    => $biller_id,
										'project_id'   => $project_id,
										'created_by'   => $this->session->userdata('user_id'),
										'customer_id'  => $customer_id,
									);
									$accTrans[] = array(
										'tran_type'    => 'CDelivery',
										'tran_date'    => $date,
										'reference_no' => $reference,
										'account_code' => $this->accounting_setting->default_cost,
										'amount' 	   => ($cost_item['cost'] * $cost_item['quantity']),
										'narrative'    => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . $cost_item['quantity'] . '#' . 'Cost: ' . $cost_item['cost'],
										'biller_id'    => $biller_id,
										'project_id'   => $project_id,
										'created_by'   => $this->session->userdata('user_id'),
										'customer_id'  => $customer_id,
									);
								}
							}
						} else {
							$real_unit_cost    = $product_bom->cost;
							$product_bom->cost = $real_unit_cost;
							$total_cost += $product_bom->cost * ($quantity * $product_bom->quantity);
							$stockmoves[] = array(
								'transaction'    => 'CDelivery',
								'product_id'     => $product_bom->product_id,
								'product_type'   => $product_bom->product_type,
								'product_code' 	 => $product_bom->product_code,
								'product_name' 	 => $product_bom->product_name,
								'quantity' 		 => ($quantity * $product_bom->quantity) * -1,
								'unit_quantity'  => $product_bom->unit_qty,
								'unit_code' 	 => $product_bom->code,
								'unit_id' 		 => $product_bom->unit_id,
								'warehouse_id'   => $warehouse_id,
								'date' 			 => $date,
								'real_unit_cost' => $product_bom->cost,
								'reference_no'   => $reference,
								'user_id' 		 => $this->session->userdata('user_id'),
							);
							if ($this->Settings->accounting == 1) {
								$accTrans[] = array(
									'tran_type'    => 'CDelivery',
									'tran_date'    => $date,
									'reference_no' => $reference,
									'account_code' => $this->accounting_setting->default_stock,
									'amount'       => - ($product_bom->cost * ($quantity * $product_bom->quantity)),
									'narrative'    => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . ($quantity * $product_bom->quantity) . '#' . 'Cost: ' . $product_bom->cost,
									'biller_id'    => $biller_id,
									'project_id'   => $project_id,
									'created_by'   => $this->session->userdata('user_id'),
									'customer_id'  => $customer_id,
								);
								$accTrans[] = array(
									'tran_type'    => 'CDelivery',
									'tran_date'    => $date,
									'reference_no' => $reference,
									'account_code' => $this->accounting_setting->default_cost,
									'amount'       => ($product_bom->cost * ($quantity * $product_bom->quantity)),
									'narrative'    => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . ($quantity * $product_bom->quantity) . '#' . 'Cost: ' . $product_bom->cost,
									'biller_id'    => $biller_id,
									'project_id'   => $project_id,
									'created_by'   => $this->session->userdata('user_id'),
									'customer_id'  => $customer_id,
								);
							}
						}
					}
					$data['total_cost'] = $total_cost;
				} else {
					$error = lang('please_check_product') . ' ' . $stregth->name;
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
			}
			// $this->bpas->print_arrays($stockmoves);
			// $this->bpas->print_arrays($data, $stockmoves, $used_fuels, $accTrans);
		}
		if ($this->form_validation->run() == true && $delivery_id = $this->concretes_model->addDelivery($data, $stockmoves, $used_fuels, $accTrans)) {
			$this->session->set_userdata('remove_cdn', 1);
			$this->session->set_flashdata('message', $this->lang->line("delivery_added") . " " . $reference);
			if ($this->input->post('add_delivery_next')) {
				admin_redirect('concretes/add_delivery');
			} else {
				admin_redirect('concretes/modal_view_delivery/' . $delivery_id . '/1');
			}
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] 		 =  $this->site->getBillers();
			$this->data['warehouses'] 	 = $this->site->getWarehouses();
			$this->data['trucks'] 		 = $this->concretes_model->getTrucks("truck", true);
			$this->data['pumps'] 		 = $this->concretes_model->getTrucks("pump", true);
			$this->data['drivers'] 		 = $this->site->getAllCompanies('driver');
			$this->data['slumps'] 		 = $this->site->getcustomfieldBycode('slumps');
			$this->data['casting_types'] = $this->site->getcustomfieldBycode('casting_types');
			$this->data['customers'] 	 = $this->site->getAllCompanies('customer');
			$this->data['stregths'] 	 = $this->concretes_model->getStregths();
			$this->data['operators'] 	 = $this->concretes_model->getOperators();
			$this->data['projects']      = $this->site->getAllProject();
			$this->data['groups'] 		 = $this->site->getcustomfieldBycode('groups');
			$bc   = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes'), 'page' => lang('deliveries')), array('link' => '#', 'page' => lang('add_delivery')));
			$meta = array('page_title' => lang('add_delivery'), 'bc' => $bc);
			$this->page_construct('concretes/add_delivery', $meta, $this->data);
		}
	}

	public function edit_delivery($id = false)
	{
		$this->bpas->checkPermissions();
		$sale_items = $this->concretes_model->getSaleItemByDelivery($id);
		if ($sale_items) {
			$this->session->set_flashdata('error', lang('delivery_is_already_issue_sale'));
			redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
		}
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('departure_time', $this->lang->line("departure_time"), 'required');
		$this->form_validation->set_rules('location', $this->lang->line("location"), 'required');
		$this->form_validation->set_rules('truck', $this->lang->line("truck"), 'required');
		$this->form_validation->set_rules('slump', $this->lang->line("slump"), 'required');
		$this->form_validation->set_rules('casting_type', $this->lang->line("casting_type"), 'required');
		$this->form_validation->set_rules('stregth', $this->lang->line("stregth"), 'required');
		$this->form_validation->set_rules('quantity', $this->lang->line("quantity"), 'required');
		$this->form_validation->set_rules('operator', $this->lang->line("operator"), 'required');
		if ($this->form_validation->run() == true) {
			$used_fuels  = false;
			$biller_id   = $this->input->post('biller');
			$project_id  = $this->input->post('project');
			$seal_number = $this->input->post('seal_number');
			$reference   = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cdn', $biller_id);
			if ($this->Owner || $this->Admin || $this->GP['change_date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = date('Y-m-d H:i:s');
			}
			$warehouse_id     = $this->input->post('warehouse');
			$customer_id      = $this->input->post('customer');
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer 		  = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
			$biller_details   = $this->site->getCompanyByID($biller_id);
			$biller           = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$note             = $this->bpas->clear_tags($this->input->post('note'));
			$departure_time   = $this->input->post('departure_time');
			$location_id      = $this->input->post('location');
			$truck_id         = $this->input->post('truck');
			$truck            = $this->concretes_model->getTruckByID($truck_id);
			$driver_id        = $truck->driver_id;
			$slump_id         = $this->input->post('slump');
			$casting_type_id  = $this->input->post('casting_type');
			$stregth_id       = $this->input->post('stregth');
			$quantity         = $this->input->post('quantity');
			$pump_id          = $this->input->post('pump');
			$markup_qty       = $this->input->post('markup_qty');
			$operator_id      = $this->input->post('operator');
			$pump 			  = $this->concretes_model->getTruckByID($pump_id);
			$pump_driver_id   = $pump ? $pump->driver_id : '';
			$location         = $this->concretes_model->getCustomerLocationByID($location_id);
			// if ($location) {
			// 	$kilometer = $this->concretes_model->getBillerKilometer($biller_id, $location_id);
			// 	if ($kilometer && $kilometer->kilometer > 0) {
			// 		$location->kilometer = $kilometer->kilometer;
			// 	}
			// }
			$driver = $this->concretes_model->getDriverByID($driver_id);
			$slump = $this->concretes_model->getSlumpByID($slump_id);
			$casting_type = $this->concretes_model->getCastingTypeByID($casting_type_id);
			$stregth = $this->concretes_model->getStregthByID($stregth_id);
			$pump_driver = false;
			$driver_assistant = '';
			$pump_move = 0;
			if ($pump) {
				$pump_driver = $this->concretes_model->getDriverByID($pump_driver_id);
				$driver_assistant = $pump->driver_assistant;
				$pump_move = $this->input->post('pump_move');
			}
			$total_cost = 0;
			$data = array(
				'date' 				=> $date,
				'reference_no'  	=> $reference,
				'customer_code' 	=> $customer_details->code,
				'customer_id'   	=> $customer_id,
				'customer'      	=> $customer,
				'biller_id'     	=> $biller_id,
				'biller'        	=> $biller,
				'project_id'    	=> $project_id,
				'warehouse_id'      => $warehouse_id,
				'note' 			    => $note,
				'departure_time'    => $departure_time,
				'seal_number' 		=> $seal_number,
				'location_id' 		=> $location_id,
				'location_name' 	=> $location->name,
				'site_person' 		=> $location->contact_person,
				'site_number' 		=> $location->phone,
				'kilometer'   		=> $location->kilometer,
				'slump_id'    		=> $slump_id,
				'slump_name'  		=> $slump->name,
				'casting_type_id'   => $casting_type_id,
				'casting_type_name' => $casting_type->name,
				'driver_id'   		=> $driver_id,
				'driver_name' 		=> $driver->name_kh . " - " . $driver->name,
				'truck_id'    		=> $truck_id,
				'truck_code'  		=> $truck->plate,
				'stregth_id'  		=> $stregth_id,
				'stregth_name'      => $stregth->name,
				'pump_id'           => $pump_id,
				'pump_code'         => $pump ? $pump->plate : '',
				'pump_driver_id'    => $pump_driver_id,
				'pump_driver_name'  => ($pump_driver ? $pump_driver->name_kh . " - " . $pump_driver->name : ''),
				'pump_move'         => $pump_move,
				'driver_assistant'  => $driver_assistant,
				'quantity'    		=> $quantity,
				'markup_qty'  		=> $markup_qty,
				'operator_id' 		=> $operator_id,
				'group_id'    		=> $this->input->post("group"),
				'updated_by'  		=> $this->session->userdata('user_id'),
				'updated_at'  		=> date('Y-m-d H:i:s'),
			);
			if ($this->Settings->fuel_expenses) {
				if ($truck->in_range_km < $location->kilometer) {
					$out_range_litre = (($location->kilometer - $truck->in_range_km) / $truck->out_range_km) * $truck->out_range_litre;
				} else {
					$out_range_litre = 0;
				}
				$in_range_litre = $truck->in_range_litre;
				$used_fuels[] = array(
					'delivery_id' => $id,
					'date' => $date,
					'biller_id' => $biller_id,
					'driver_id' => $driver_id,
					'truck_id' => $truck_id,
					'kilometer' => $location->kilometer,
					'in_range_litre' => ($in_range_litre > 0 ? (($in_range_litre * $truck->driver_fuel_fee) / 100) : 0),
					'out_range_litre' => ($out_range_litre > 0 ? (($out_range_litre * $truck->driver_fuel_fee) / 100) : 0),
					'pump_litre' => 0
				);
				if ($truck->driver_assistant && $truck->driver_fuel_fee != 100) {
					$driver_assistants = json_decode($truck->driver_assistant);
					$total_asstants = count($driver_assistants);
					foreach ($driver_assistants as $driver_assistant) {
						$used_fuels[] = array(
							'delivery_id' => $id,
							'date' => $date,
							'biller_id' => $biller_id,
							'driver_id' => $driver_assistant,
							'truck_id' => $truck_id,
							'kilometer' => $location->kilometer,
							'in_range_litre' => ($in_range_litre > 0 ? ((($in_range_litre * $truck->assistant_fuel_fee) / 100) / $total_asstants) : 0),
							'out_range_litre' => ($out_range_litre > 0 ? ((($out_range_litre * $truck->assistant_fuel_fee) / 100) / $total_asstants) : 0),
							'pump_litre' => 0
						);
					}
				}
				if ($pump) {
					$in_range_litre = 0;
					$out_range_litre = 0;
					if ($pump_move > 0) {
						if ($pump->in_range_km < $location->kilometer) {
							$out_range_litre = (($location->kilometer - $pump->in_range_km) / $pump->out_range_km) * $pump->out_range_litre;
						}
						$in_range_litre = $pump->in_range_litre;
					}
					$pump_litre = ($quantity / $pump->m3) * $pump->litre;
					$used_fuels[] = array(
						'delivery_id' => $id,
						'date' => $date,
						'biller_id' => $biller_id,
						'driver_id' => $pump_driver_id,
						'truck_id' => $pump_id,
						'kilometer' => $location->kilometer,
						'in_range_litre' => ($in_range_litre > 0 ? (($in_range_litre * $pump->driver_fuel_fee) / 100) : 0),
						'out_range_litre' => ($out_range_litre > 0 ? (($out_range_litre * $pump->driver_fuel_fee) / 100) : 0),
						'pump_litre' => ($pump_litre > 0 ? (($pump_litre * $pump->driver_fuel_fee) / 100) : 0)
					);
					if ($pump->driver_assistant && $pump->driver_fuel_fee != 100) {
						$driver_assistants = json_decode($pump->driver_assistant);
						$total_asstants = count($driver_assistants);
						foreach ($driver_assistants as $driver_assistant) {
							$used_fuels[] = array(
								'delivery_id' => $id,
								'date' => $date,
								'biller_id' => $biller_id,
								'driver_id' => $driver_assistant,
								'truck_id' => $pump_id,
								'kilometer' => $location->kilometer,
								'in_range_litre' => ($in_range_litre > 0 ? ((($in_range_litre * $pump->assistant_fuel_fee) / 100) / $total_asstants) : 0),
								'out_range_litre' => ($out_range_litre > 0 ? ((($out_range_litre * $pump->assistant_fuel_fee) / 100) / $total_asstants) : 0),
								'pump_litre' => ($pump_litre > 0 ? ((($pump_litre * $pump->assistant_fuel_fee) / 100) / $total_asstants) : 0)
							);
						}
					}
				}
			}
			$stockmoves = false;
			if ($stregth->type == 'bom') {
				$product_boms = $this->concretes_model->getBomProductByStandProduct($stregth_id, $biller_id);
				if ($product_boms) {
					foreach ($product_boms as $product_bom) {
						if ($this->Settings->accounting_method == '0') {
							$costs = $this->site->getFifoCost($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves, 'CDelivery', $id);
						} else if ($this->Settings->accounting_method == '1') {
							$costs = $this->site->getLifoCost($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves, 'CDelivery', $id);
						} else if ($this->Settings->accounting_method == '3') {
							$costs = $this->site->getProductMethod($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves, 'CDelivery', $id);
						} else {
							$costs = false;
						}
						$productAcc = $this->site->getProductAccByProductId($product_bom->product_id);
						if ($costs) {
							foreach ($costs as $cost_item) {
								$total_cost += $cost_item['cost'] * $cost_item['quantity'];
								$stockmoves[] = array(
									'transaction'    => 'CDelivery',
									'transaction_id' => $id,
									'product_id' 	 => $product_bom->product_id,
									'product_type'   => $product_bom->product_type,
									'product_code' 	 => $product_bom->product_code,
									'product_name' 	 => $product_bom->product_name,
									'quantity' 		 => $cost_item['quantity'] * (-1),
									'unit_quantity'  => $product_bom->unit_qty,
									'unit_code'      => $product_bom->code,
									'unit_id' 		 => $product_bom->unit_id,
									'warehouse_id'   => $warehouse_id,
									'date' 			 => $date,
									'real_unit_cost' => $cost_item['cost'],
									'reference_no' 	 => $reference,
									'user_id' 		 => $this->session->userdata('user_id'),
								);
								if ($this->Settings->accounting == 1) {
									$accTrans[] = array(
										'tran_type'    => 'CDelivery',
										'tran_no'      => $id,
										'tran_date'    => $date,
										'reference_no' => $reference,
										'account_code' => $this->accounting_setting->default_stock,
										'amount' 	   => - ($cost_item['cost'] * $cost_item['quantity']),
										'narrative'    => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . $cost_item['quantity'] . '#' . 'Cost: ' . $cost_item['cost'],
										'biller_id'    => $biller_id,
										'project_id'   => $project_id,
										'created_by'   => $this->session->userdata('user_id'),
										'customer_id'  => $customer_id,

									);
									$accTrans[] = array(
										'tran_type'    => 'CDelivery',
										'tran_date'    => $date,
										'reference_no' => $reference,
										'account_code' => $this->accounting_setting->default_cost,
										'amount'       => ($cost_item['cost'] * $cost_item['quantity']),
										'narrative'    => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . $cost_item['quantity'] . '#' . 'Cost: ' . $cost_item['cost'],
										'biller_id'    => $biller_id,
										'project_id'   => $project_id,
										'created_by'   => $this->session->userdata('user_id'),
										'customer_id'  => $customer_id,
									);
								}
							}
						} else {
							$real_unit_cost    = $this->site->getAVGCost($product_bom->product_id, $date, "CDelivery", $id);
							$product_bom->cost = $real_unit_cost;
							$total_cost += $product_bom->cost * ($quantity * $product_bom->quantity);
							$stockmoves[] = array(
								'transaction' 	 => 'CDelivery',
								'transaction_id' => $id,
								'product_id'     => $product_bom->product_id,
								'product_type'   => $product_bom->product_type,
								'product_code'   => $product_bom->product_code,
								'product_name'   => $product_bom->product_name,
								'quantity' 		 => ($quantity * $product_bom->quantity) * -1,
								'unit_quantity'  => $product_bom->unit_qty,
								'unit_code'      => $product_bom->code,
								'unit_id'        => $product_bom->unit_id,
								'warehouse_id'   => $warehouse_id,
								'date' 	         => $date,
								'real_unit_cost' => $product_bom->cost,
								'reference_no' 	 => $reference,
								'user_id' 		 => $this->session->userdata('user_id'),
							);
							if ($this->Settings->accounting == 1) {
								$accTrans[] = array(
									'tran_type'    => 'CDelivery',
									'tran_no'      => $id,
									'tran_date'    => $date,
									'reference_no' => $reference,
									'account_code' => $this->accounting_setting->default_stock,
									'amount' 	   => - ($product_bom->cost * ($quantity * $product_bom->quantity)),
									'narrative'    => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . ($quantity * $product_bom->quantity) . '#' . 'Cost: ' . $product_bom->cost,
									'biller_id'    => $biller_id,
									'project_id'   => $project_id,
									'created_by'   => $this->session->userdata('user_id'),
									'customer_id'  => $customer_id,
								);
								$accTrans[] = array(
									'tran_type'    => 'CDelivery',
									'tran_no'      => $id,
									'tran_date'    => $date,
									'reference_no' => $reference,
									'account_code' => $this->accounting_setting->default_cost,
									'amount' 	   => ($product_bom->cost * ($quantity * $product_bom->quantity)),
									'narrative'    => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . ($quantity * $product_bom->quantity) . '#' . 'Cost: ' . $product_bom->cost,
									'biller_id'    => $biller_id,
									'project_id'   => $project_id,
									'created_by'   => $this->session->userdata('user_id'),
									'customer_id'  => $customer_id,
								);
							}
						}
					}
					$data['total_cost'] = $total_cost;
				} else {
					$error = lang('please_check_product') . ' ' . $stregth->name;
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->updateDelivery($id, $data, $stockmoves, $used_fuels, $accTrans)) {
			$this->session->set_userdata('remove_cdn', 1);
			$this->session->set_flashdata('message', $this->lang->line("delivery_edited") . " " . $reference);
			admin_redirect('concretes');
		} else {
			$this->session->set_userdata('remove_cdn', 1);
			$delivery = $this->concretes_model->getDeliveryByID($id);
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['delivery'] 	 = $delivery;
			$this->data['billers'] 		 =  $this->site->getBillers();
			$this->data['warehouses'] 	 = $this->site->getWarehouses();
			$this->data['trucks'] 		 = $this->concretes_model->getTrucks("truck");
			$this->data['pumps']		 = $this->concretes_model->getTrucks("pump");
			$this->data['drivers'] 		 = $this->site->getAllCompanies('driver');
			$this->data['slumps'] 		 = $this->site->getcustomfieldBycode('slumps');
			$this->data['casting_types'] = $this->site->getcustomfieldBycode('casting_types');
			$this->data['stregths'] 	 = $this->concretes_model->getStregths();
			$this->data['customers'] 	 = $this->site->getAllCompanies('customer');
			$this->data['operators'] 	 = $this->concretes_model->getOperators();
			$this->data['groups'] 		 = $this->concretes_model->getGroups();
			$bc   = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes'), 'page' => lang('deliveries')), array('link' => '#', 'page' => lang('edit_delivery')));
			$meta = array('page_title' => lang('edit_delivery'), 'bc' => $bc);
			$this->page_construct('concretes/edit_delivery', $meta, $this->data);
		}
	}

	public function delete_delivery($id = null)
	{
		$this->bpas->checkPermissions(NULL, true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		$sale_items = $this->concretes_model->getSaleItemByDelivery($id);
		if ($sale_items) {
			$this->session->set_flashdata('error', lang('delivery_is_already_issue_sale'));
			redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
		}
		if ($this->concretes_model->deleteDelivery($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("delivery_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('delivery_deleted'));
			admin_redirect('concretes');
		}
	}
	public function delivery_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'sync_delivery') {
					foreach ($_POST['val'] as $id) {
						$data = false;
						$used_fuels = false;
						$delivery = $this->concretes_model->getDeliveryByID($id);
						$location = $this->concretes_model->getCustomerLocationByID($delivery->location_id);
						// if ($location) {
						// 	$kilometer = $this->concretes_model->getBillerKilometer($delivery->biller_id, $delivery->location_id);
						// 	if ($kilometer && $kilometer->kilometer > 0) {
						// 		$location->kilometer = $kilometer->kilometer;
						// 	}
						// }
						$truck = $this->concretes_model->getTruckByID($delivery->truck_id);
						$driver = $this->concretes_model->getDriverByID($truck->driver_id);
						$pump = $this->concretes_model->getTruckByID($delivery->pump_id);
						$pump_driver_id   = "";
						$pump_driver_name = "";
						if ($pump) {
							$pump_driver      = $this->concretes_model->getDriverByID($pump->driver_id);
							$pump_driver_id   = $pump->driver_id;
							$pump_driver_name = $pump_driver->name_kh . " - " . $pump_driver->name;
							$driver_assistant = $pump->driver_assistant;
						}
						$data["driver_id"] = $driver->id;
						$data["driver_name"] = $driver->name;
						$data["pump_driver_id"] = $pump_driver_id;
						$data["pump_driver_name"] = $pump_driver_name;
						$data["driver_assistant"] = $driver_assistant;
						$data["location_name"] = $location->name;
						$data["site_person"] = $location->contact_person;
						$data["site_number"] = $location->phone;
						$data["kilometer"]   = $location->kilometer;
						if ($truck->in_range_km < $location->kilometer) {
							$out_range_litre = (($location->kilometer - $truck->in_range_km) / $truck->out_range_km) * $truck->out_range_litre;
						} else {
							$out_range_litre = 0;
						}
						$in_range_litre = $truck->in_range_litre;
						$used_fuels[] = array(
							'delivery_id' => $delivery->id,
							'date' => $delivery->date,
							'biller_id' => $delivery->biller_id,
							'driver_id' => $driver->id,
							'truck_id' => $delivery->truck_id,
							'kilometer' => $location->kilometer,
							'in_range_litre' => ($in_range_litre > 0 ? (($in_range_litre * $truck->driver_fuel_fee) / 100) : 0),
							'out_range_litre' => ($out_range_litre > 0 ? (($out_range_litre * $truck->driver_fuel_fee) / 100) : 0),
							'pump_litre' => 0
						);
						if ($truck->driver_assistant && $truck->driver_fuel_fee != 100) {
							$driver_assistants = json_decode($truck->driver_assistant);
							$total_asstants = !empty($driver_assistants) ? count($driver_assistants) : 0;
							if (!empty($driver_assistants)) { 
								foreach ($driver_assistants as $driver_assistant) {
									$used_fuels[] = array(
										'delivery_id' => $delivery->id,
										'date' => $delivery->date,
										'biller_id' => $delivery->biller_id,
										'driver_id' => $driver_assistant,
										'truck_id' => $delivery->truck_id,
										'kilometer' => $location->kilometer,
										'in_range_litre' => ($in_range_litre > 0 ? ((($in_range_litre * $truck->assistant_fuel_fee) / 100) / $total_asstants) : 0),
										'out_range_litre' => ($out_range_litre > 0 ? ((($out_range_litre * $truck->assistant_fuel_fee) / 100) / $total_asstants) : 0),
										'pump_litre' => 0
									);
								}
							}
						}
						if ($pump) {
							$in_range_litre = 0;
							$out_range_litre = 0;
							if ($delivery->pump_move > 0) {
								if ($pump->in_range_km < $location->kilometer) {
									$out_range_litre = (($location->kilometer - $pump->in_range_km) / ($pump->out_range_km ? $pump->out_range_km : 1)) * $pump->out_range_litre;
								}
								$in_range_litre = $pump->in_range_litre;
							}
							$pump_litre = ($delivery->quantity / $pump->m3) * $pump->litre;
							$used_fuels[] = array(
								'delivery_id' => $delivery->id,
								'date' => $delivery->date,
								'biller_id' => $delivery->biller_id,
								'driver_id' => $delivery->pump_driver_id,
								'truck_id' => $delivery->pump_id,
								'kilometer' => $location->kilometer,
								'in_range_litre' => ($in_range_litre > 0 ? (($in_range_litre * $pump->driver_fuel_fee) / 100) : 0),
								'out_range_litre' => ($out_range_litre > 0 ? (($out_range_litre * $pump->driver_fuel_fee) / 100) : 0),
								'pump_litre' => ($pump_litre > 0 ? (($pump_litre * $pump->driver_fuel_fee) / 100) : 0)
							);
							if ($pump->driver_assistant && $pump->driver_fuel_fee != 100) {
								$driver_assistants = json_decode($pump->driver_assistant);
								$total_asstants = !empty($driver_assistants) ? count($driver_assistants) : 0;
								if (!empty($driver_assistants)) {
									foreach ($driver_assistants as $driver_assistant) {
										$used_fuels[] = array(
											'delivery_id' => $delivery->id,
											'date' => $delivery->date,
											'biller_id' => $delivery->biller_id,
											'driver_id' => $driver_assistant,
											'truck_id' => $delivery->pump_id,
											'kilometer' => $location->kilometer,
											'in_range_litre' => ($in_range_litre > 0 ? ((($in_range_litre * $pump->assistant_fuel_fee) / 100) / $total_asstants) : 0),
											'out_range_litre' => ($out_range_litre > 0 ? ((($out_range_litre * $pump->assistant_fuel_fee) / 100) / $total_asstants) : 0),
											'pump_litre' => ($pump_litre > 0 ? ((($pump_litre * $pump->assistant_fuel_fee) / 100) / $total_asstants) : 0)
										);
									}
								}
							}
						}
						// $this->concretes_model->syncDriver($delivery->id, $data, $used_fuels);
					}
					$this->session->set_flashdata('message', $this->lang->line("driver_synced"));
					redirect($_SERVER["HTTP_REFERER"]);
				} else if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_delivery', true);
					foreach ($_POST['val'] as $id) {
						$this->concretes_model->deleteDelivery($id);
					}
					$this->session->set_flashdata('message', $this->lang->line("deliveries_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				} elseif ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('delivery'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('location'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('seal_number'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('truck'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('driver'));
					$row = 2;
					foreach ($_POST['val'] as $id) {
						$delivery = $this->concretes_model->getDeliveryByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($delivery->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $delivery->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $delivery->biller);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $delivery->customer);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $delivery->location_name);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $delivery->seal_number);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $delivery->truck_code);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $delivery->driver_name);
						$row++;
					}

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'deliveries_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function update_weight($id)
	{
		$this->form_validation->set_rules('gross_weight', lang("gross_weight"), 'required');
		$this->form_validation->set_rules('truck_weight', lang("truck_weight"), 'required');
		if ($this->form_validation->run() == true) {
			$data = array(
				"weight_status" => 'completed',
				"gross_weight" => $this->input->post('gross_weight'),
				"truck_weight" => $this->input->post('truck_weight'),
				"weight_by" => $this->session->userdata('user_id')
			);
		} elseif ($this->input->post('update')) {
			$this->session->set_flashdata('error', validation_errors());
			redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'concretes');
		}
		if ($this->form_validation->run() == true && $this->concretes_model->updateDeliveryStatus($id, $data)) {
			$this->session->set_flashdata('message', lang('weight_updated'));
			redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'concretes');
		} else {
			$this->data['delivery'] = $this->concretes_model->getDeliveryByID($id);
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'concretes/update_weight', $this->data);
		}
	}

	public function update_status($id)
	{
		$this->form_validation->set_rules('arrival_time', lang("arrival_time"), 'required');
		$this->form_validation->set_rules('finished_casting', lang("finished_casting"), 'required');
		if ($this->form_validation->run() == true) {
			$data = array(
				"status" => $this->input->post('spoiled'),
				"arrival_time" => $this->input->post('arrival_time'),
				"finished_casting" => $this->input->post('finished_casting'),
				"status_note" => $this->input->post('note')
			);
		} elseif ($this->input->post('update')) {
			$this->session->set_flashdata('error', validation_errors());
			redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'concretes');
		}
		if ($this->form_validation->run() == true && $this->concretes_model->updateDeliveryStatus($id, $data)) {
			$this->session->set_flashdata('message', lang('status_updated'));
			redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'concretes');
		} else {
			$this->data['delivery'] = $this->concretes_model->getDeliveryByID($id);
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'concretes/update_status', $this->data);
		}
	}

	public function modal_view_delivery($id = null, $modal = false)
	{
		$this->bpas->checkPermissions('deliveries', true);
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$delivery = $this->concretes_model->getDeliveryByID($id);
		$this->data['day_quantity'] = $this->concretes_model->getDayQuantity($id, $delivery->biller_id, $delivery->date, $delivery->customer_id, $delivery->stregth_id, $delivery->location_id, $delivery->group_id);
		$this->data['biller'] 	= $this->site->getCompanyByID($delivery->biller_id);
		$this->data['delivery'] = $delivery;
		$this->data['modal'] 	= $modal;
		$this->data['operator'] = $this->concretes_model->getOfficerByID($delivery->operator_id);
		$this->data['created_by'] 	= $this->site->getUserByID($delivery->created_by);
		$this->data['weight_by'] 	= $this->site->getUserByID($delivery->weight_by);
		$this->data['group'] 		= $delivery->group_id ? $this->concretes_model->getGroupByID($delivery->group_id) : false;
		$this->load->view($this->theme . 'concretes/modal_view_delivery', $this->data);
	}
	public function deliveries_report()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['operators'] = $this->concretes_model->getOperators();
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['trucks'] = $this->concretes_model->getTrucks("truck");
		$this->data['pumps'] = $this->concretes_model->getTrucks("pump");
		$this->data['drivers'] = $this->site->getAllCompanies('driver');
		$this->data['casting_types'] = $this->site->getcustomfieldBycode('casting_type');
		$this->data['stregths'] = $this->concretes_model->getStregths();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('deliveries_report')));
		$meta = array('page_title' => lang('deliveries_report'), 'bc' => $bc);
		$this->page_construct('concretes/deliveries_report', $meta, $this->data);
	}
	public function getDeliveriesReport($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('deliveries_report');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
		$customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
		$location = $this->input->get('location') ? $this->input->get('location') : NULL;
		$casting_type = $this->input->get('casting_type') ? $this->input->get('casting_type') : NULL;
		$stregth = $this->input->get('stregth') ? $this->input->get('stregth') : NULL;
		$operator = $this->input->get('operator') ? $this->input->get('operator') : NULL;
		$pump = $this->input->get('pump') ? $this->input->get('pump') : NULL;
		$truck = $this->input->get('truck') ? $this->input->get('truck') : NULL;
		$driver = $this->input->get('driver') ? $this->input->get('driver') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
					DATE_FORMAT(" . $this->db->dbprefix('con_deliveries') . ".date, '%Y-%m-%d %T') as date,
					con_deliveries.reference_no,
					con_deliveries.customer_code,
					con_deliveries.customer,
					con_deliveries.location_name,
					con_deliveries.casting_type_name,
					con_deliveries.seal_number,
					con_deliveries.stregth_name,
					IFNULL(" . $this->db->dbprefix('con_deliveries') . ".quantity,0) as quantity,
					con_deliveries.pump_code,
					con_deliveries.pump_driver_name,
					con_deliveries.truck_code,
					con_deliveries.driver_name,
					con_deliveries.departure_time,
					con_deliveries.arrival_time,
					con_deliveries.finished_casting,
					hr_employees.lastname,
					con_deliveries.total_cost,
					con_deliveries.status,
					con_deliveries.sale_status,
					con_deliveries.id as id")
				->from("con_deliveries")
				->join("hr_employees", "hr_employees.id = con_deliveries.operator_id", "left")
				->group_by("con_deliveries.id");

			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_deliveries.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_deliveries.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->db->where_in('con_deliveries.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if ($start_date) {
				$this->db->where('con_deliveries.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_deliveries.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_deliveries.biller_id', $biller);
			}
			if ($warehouse) {
				$this->db->where('con_deliveries.warehouse_id', $warehouse);
			}
			if ($customer) {
				$this->db->where('con_deliveries.customer_id', $customer);
			}
			if ($location) {
				$this->db->where('con_deliveries.location_id', $location);
			}
			if ($casting_type) {
				$this->db->where('con_deliveries.casting_type_id', $casting_type);
			}
			if ($stregth) {
				$this->db->where('con_deliveries.stregth_id', $stregth);
			}
			if ($operator) {
				$this->db->where('con_deliveries.operator_id', $operator);
			}
			if ($pump) {
				$this->db->where('con_deliveries.pump_id', $pump);
			}
			if ($truck) {
				$this->db->where('con_deliveries.truck_id', $truck);
			}
			if ($driver) {
				$this->db->where('(' . $this->db->dbprefix("con_deliveries") . '.driver_id = ' . $driver . ' OR ' . $this->db->dbprefix("con_deliveries") . '.pump_driver_id = ' . $driver . ')');
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('deliveries_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('customer_code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('customer_name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('location'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('casting_type'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('seal_number'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('stregth'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('quantity'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('pump'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('pump_driver'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('truck'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('truck_driver'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('departure_time'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('arrival_time'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('finished_casting'));
				$this->excel->getActiveSheet()->SetCellValue('Q1', lang('operator_name'));
				$this->excel->getActiveSheet()->SetCellValue('R1', lang('cost'));
				$this->excel->getActiveSheet()->SetCellValue('S1', lang('status'));
				$this->excel->getActiveSheet()->SetCellValue('T1', lang('sale_status'));
				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$cost = 0;
					if ($this->Owner || $this->Admin || $this->session->userdata('show_cost')) {
						$cost = $data_row->total_cost;
					}
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->customer_code);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->location_name);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->casting_type_name);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->seal_number);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->stregth_name);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->quantity));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->pump_code);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->pump_driver_name);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->truck_code);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->driver_name);
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->departure_time);
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->arrival_time);
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->finished_casting);
					$this->excel->getActiveSheet()->SetCellValue('Q' . $row, $data_row->full_name);
					$this->excel->getActiveSheet()->SetCellValue('R' . $row, $this->bpas->formatDecimal($cost));
					$this->excel->getActiveSheet()->SetCellValue('S' . $row, lang($data_row->status));
					$this->excel->getActiveSheet()->SetCellValue('T' . $row, lang($data_row->sale_status));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('T')->setWidth(20);

				$filename = 'deliveries_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
					DATE_FORMAT(" . $this->db->dbprefix('con_deliveries') . ".date, '%Y-%m-%d %T') as date,
					con_deliveries.reference_no,
					con_deliveries.customer_code,
					con_deliveries.customer,
					con_deliveries.location_name,
					con_deliveries.casting_type_name,
					con_deliveries.seal_number,
					con_deliveries.stregth_name,
					IFNULL(" . $this->db->dbprefix('con_deliveries') . ".quantity,0) as quantity,
					con_deliveries.pump_code,
					con_deliveries.pump_driver_name,
					con_deliveries.truck_code,
					con_deliveries.driver_name,
					con_deliveries.departure_time,
					con_deliveries.arrival_time,
					con_deliveries.finished_casting,
					hr_employees.lastname,
					IFNULL(" . $this->db->dbprefix('con_deliveries') . ".total_cost,0) as total_cost,
					con_deliveries.status,
					con_deliveries.sale_status,
					con_deliveries.id as id")
				->from("con_deliveries")
				->join("hr_employees", "hr_employees.id = con_deliveries.operator_id", "left")
				->group_by("con_deliveries.id");

			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_deliveries.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_deliveries.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->datatables->where_in('con_deliveries.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if ($start_date) {
				$this->datatables->where('con_deliveries.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_deliveries.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_deliveries.biller_id', $biller);
			}
			if ($warehouse) {
				$this->datatables->where('con_deliveries.warehouse_id', $warehouse);
			}
			if ($customer) {
				$this->datatables->where('con_deliveries.customer_id', $customer);
			}
			if ($location) {
				$this->datatables->where('con_deliveries.location_id', $location);
			}
			if ($casting_type) {
				$this->datatables->where('con_deliveries.casting_type_id', $casting_type);
			}
			if ($stregth) {
				$this->datatables->where('con_deliveries.stregth_id', $stregth);
			}
			if ($operator) {
				$this->datatables->where('con_deliveries.operator_id', $operator);
			}
			if ($pump) {
				$this->datatables->where('con_deliveries.pump_id', $pump);
			}
			if ($truck) {
				$this->datatables->where('con_deliveries.truck_id', $truck);
			}
			if ($driver) {
				$this->datatables->where('(' . $this->db->dbprefix("con_deliveries") . '.driver_id = ' . $driver . ' OR ' . $this->db->dbprefix("con_deliveries") . '.pump_driver_id = ' . $driver . ')');
			}
			echo $this->datatables->generate();
		}
	}

	public function daily_deliveries()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['operators'] = $this->concretes_model->getOperators();
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['trucks'] = $this->concretes_model->getTrucks("truck");
		$this->data['pumps'] = $this->concretes_model->getTrucks("pump");
		$this->data['drivers'] = $this->site->getAllCompanies('driver');
		$this->data['casting_types'] = $this->site->getcustomfieldBycode('casting_type');
		$this->data['stregths'] = $this->concretes_model->getStregths();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('daily_deliveries')));
		$meta = array('page_title' => lang('daily_deliveries'), 'bc' => $bc);
		$this->page_construct('concretes/daily_deliveries', $meta, $this->data);
	}
	public function getDailyDeliveries($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('daily_deliveries');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
		$customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
		$location = $this->input->get('location') ? $this->input->get('location') : NULL;
		$casting_type = $this->input->get('casting_type') ? $this->input->get('casting_type') : NULL;
		$stregth = $this->input->get('stregth') ? $this->input->get('stregth') : NULL;
		$operator = $this->input->get('operator') ? $this->input->get('operator') : NULL;
		$pump = $this->input->get('pump') ? $this->input->get('pump') : NULL;
		$truck = $this->input->get('truck') ? $this->input->get('truck') : NULL;
		$driver = $this->input->get('driver') ? $this->input->get('driver') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(" . $this->db->dbprefix('con_deliveries') . ".date, '%Y-%m-%d %T') as date,
								con_deliveries.reference_no,
								con_deliveries.customer_code,
								con_deliveries.customer,
								con_deliveries.location_name,
								con_deliveries.casting_type_name,
								con_deliveries.seal_number,
								con_deliveries.stregth_name,
								IFNULL(" . $this->db->dbprefix('con_deliveries') . ".quantity,0) as quantity,
								con_deliveries.pump_code,
								con_deliveries.pump_driver_name,
								con_deliveries.truck_code,
								con_deliveries.driver_name,
								con_deliveries.departure_time,
								con_deliveries.arrival_time,
								con_deliveries.finished_casting,
								CONCAT(" . $this->db->dbprefix('users') . ".last_name,' '," . $this->db->dbprefix('users') . ".first_name) as operator,
								con_deliveries.id as id")
				->from("con_deliveries")
				->join("users", "users.id = con_deliveries.created_by", "left")
				->group_by("con_deliveries.id");
			$this->db->where("con_deliveries.status !=", "spoiled");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_deliveries.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_deliveries.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->db->where_in('con_deliveries.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if ($start_date) {
				$this->db->where('con_deliveries.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_deliveries.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_deliveries.biller_id', $biller);
			}
			if ($warehouse) {
				$this->db->where('con_deliveries.warehouse_id', $warehouse);
			}
			if ($customer) {
				$this->db->where('con_deliveries.customer_id', $customer);
			}
			if ($location) {
				$this->db->where('con_deliveries.location_id', $location);
			}
			if ($casting_type) {
				$this->db->where('con_deliveries.casting_type_id', $casting_type);
			}
			if ($stregth) {
				$this->db->where('con_deliveries.stregth_id', $stregth);
			}
			if ($operator) {
				$this->db->where('con_deliveries.created_by', $operator);
			}
			if ($pump) {
				$this->db->where('con_deliveries.pump_id', $pump);
			}
			if ($truck) {
				$this->db->where('con_deliveries.truck_id', $truck);
			}
			if ($driver) {
				$this->db->where('(' . $this->db->dbprefix("con_deliveries") . '.driver_id = ' . $driver . ' OR ' . $this->db->dbprefix("con_deliveries") . '.pump_driver_id = ' . $driver . ')');
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('deliveries_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('customer_code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('customer_name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('location'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('casting_type'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('seal_number'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('stregth'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('quantity'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('pump'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('pump_driver'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('truck'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('truck_driver'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('departure_time'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('arrival_time'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('finished_casting'));
				$this->excel->getActiveSheet()->SetCellValue('Q1', lang('operator_name'));

				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->customer_code);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->location_name);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->casting_type_name);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->seal_number);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->stregth_name);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->quantity));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->pump_code);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->pump_driver_name);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->truck_code);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->driver_name);
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->departure_time);
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->arrival_time);
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->finished_casting);
					$this->excel->getActiveSheet()->SetCellValue('Q' . $row, $data_row->operator);

					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
				$filename = 'deliveries_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
									DATE_FORMAT(" . $this->db->dbprefix('con_deliveries') . ".date, '%Y-%m-%d %T') as date,
									con_deliveries.customer,
									con_deliveries.location_name,
									con_deliveries.casting_type_name,
									con_deliveries.pump_code,
									con_deliveries.stregth_name,
									SUM(IFNULL(" . $this->db->dbprefix('con_deliveries') . ".quantity,0)) as quantity,
									con_deliveries.id as id")
				->from("con_deliveries")
				->group_by("date(" . $this->db->dbprefix('con_deliveries') . ".date), 
										IFNULL(" . $this->db->dbprefix('con_deliveries') . ".customer_id,0),
										IFNULL(" . $this->db->dbprefix('con_deliveries') . ".location_id,0),
										IFNULL(" . $this->db->dbprefix('con_deliveries') . ".casting_type_id,0),
										IFNULL(" . $this->db->dbprefix('con_deliveries') . ".pump_id,0),
										IFNULL(" . $this->db->dbprefix('con_deliveries') . ".stregth_id,0)
									");
			$this->datatables->where("con_deliveries.status !=", "spoiled");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_deliveries.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_deliveries.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->datatables->where_in('con_deliveries.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if ($start_date) {
				$this->datatables->where('con_deliveries.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_deliveries.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_deliveries.biller_id', $biller);
			}
			if ($warehouse) {
				$this->datatables->where('con_deliveries.warehouse_id', $warehouse);
			}
			if ($customer) {
				$this->datatables->where('con_deliveries.customer_id', $customer);
			}
			if ($location) {
				$this->datatables->where('con_deliveries.location_id', $location);
			}
			if ($casting_type) {
				$this->datatables->where('con_deliveries.casting_type_id', $casting_type);
			}
			if ($stregth) {
				$this->datatables->where('con_deliveries.stregth_id', $stregth);
			}
			if ($operator) {
				$this->datatables->where('con_deliveries.created_by', $operator);
			}
			if ($pump) {
				$this->datatables->where('con_deliveries.pump_id', $pump);
			}
			if ($truck) {
				$this->datatables->where('con_deliveries.truck_id', $truck);
			}
			if ($driver) {
				$this->datatables->where('(' . $this->db->dbprefix("con_deliveries") . '.driver_id = ' . $driver . ' OR ' . $this->db->dbprefix("con_deliveries") . '.pump_driver_id = ' . $driver . ')');
			}
			echo $this->datatables->generate();
		}
	}

	public function get_deliveries()
	{
		$biller_id = $this->input->get('biller_id');
		$project_id = $this->input->get('project_id');
		$warehouse_id = $this->input->get('warehouse_id');
		$customer_id = $this->input->get('customer_id');
		$location_id = $this->input->get('location_id');
		$from_date = $this->bpas->fld(trim($this->input->get('from_date')));
		$to_date = $this->bpas->fld(trim($this->input->get('to_date')));
		$sale_id = $this->input->get('sale_id');
		$quotation_id = $this->input->get('quotation_id') ? $this->input->get('quotation_id') : 0;
		$sale_status = "pending";
		$spoiled = true;
		$deliveries = $this->concretes_model->getDeliveries($biller_id, $project_id, $warehouse_id, $customer_id, $location_id, $from_date, $to_date, $sale_status, $sale_id, $spoiled, $quotation_id);
		echo json_encode($deliveries);
	}

	public function sales($warehouse_id = null, $biller_id = NULL)
	{
		$this->bpas->checkPermissions();
		if ($warehouse_id == 0) {
			$warehouse_id = null;
		}
		if ($biller_id == 0) {
			$biller_id = null;
		}
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('sales')));
		$meta = array('page_title' => lang('sales'), 'bc' => $bc);
		$this->page_construct('concretes/sales', $meta, $this->data);
	}

	public function getSales($warehouse_id = null, $biller_id = NULL)
	{
		$this->bpas->checkPermissions('sales');
		$edit_link = anchor('admin/concretes/edit_sale/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), ' class="edit_sale" ');
		$delete_link = "<a href='#' class='po delete_sale' title='<b>" . $this->lang->line("delete_sale") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('concretes/delete_sale/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_sale') . "</a>";
		$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
		$this->load->library('datatables');
		$this->datatables->select("
						con_sales.id as id,
						DATE_FORMAT(date, '%Y-%m-%d %T') as date,
						DATE_FORMAT(from_date, '%Y-%m-%d %T') as from_date,
						DATE_FORMAT(to_date, '%Y-%m-%d %T') as to_date,
						reference_no,
						customer,
						location_name,
						grand_total,
						attachment
					")
			->from("con_sales");
		if ($warehouse_id) {
			$this->datatables->where('con_sales.warehouse_id', $warehouse_id);
		}
		if ($biller_id) {
			$this->datatables->where('con_sales.biller_id', $biller_id);
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('con_sales.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('con_sales.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('con_sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
	}

	public function add_sale()
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('location', $this->lang->line("location"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
		if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->bpas->GP['concretes-sales-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$warehouse_id = $this->input->post('warehouse');
			$customer_id = $this->input->post('customer');
			$note = $this->input->post('note');
			$from_date = $this->bpas->fld(trim($this->input->post('from_date')));
			$to_date = $this->bpas->fld(trim($this->input->post('to_date')));
			$location_id = $this->input->post('location');
			$location = $this->concretes_model->getCustomerLocationByID($location_id);
			$payment_term = $this->input->post('payment_term');
			$due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
			$truck_charge = $this->input->post('truck_charge');
			$pump_charge = $this->input->post('pump_charge');
			$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('csale', $biller_id);

			$i = isset($_POST['stregth_id']) ? sizeof($_POST['stregth_id']) : 0;
			$total = 0;
			$total_items = 0;
			$total_cost = 0;
			$percentage = '%';
			for ($r = 0; $r < $i; $r++) {
				$delivery_id = $_POST['delivery_id'][$r];
				$stregth_id = $_POST['stregth_id'][$r];
				$unit_price = $_POST['unit_price'][$r];
				$quantity = $_POST['quantity'][$r];
				$subtotal = $unit_price * $quantity;
				$stregth = $this->concretes_model->getStregthByID($stregth_id);
				$unit = $this->site->getProductUnit($stregth->id, $stregth->unit);
				$cost = 0;
				$raw_materials = false;
				if ($stregth->type == 'bom') {
					$product_boms = $this->concretes_model->getBomProductByStandProduct($stregth_id, $biller_id);
					if ($product_boms) {
						$product_bom_cost = 0;
						foreach ($product_boms as $product_bom) {
							$raw_materials[] = array(
								"product_id" => $product_bom->product_id,
								"quantity" => ($quantity * $product_bom->quantity)
							);
							$product_bom_cost += ($product_bom->quantity * $product_bom->cost);
						}
						$cost  = $product_bom_cost;
					} else {
						$error = lang('please_check_product') . ' ' . $item_code;
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
				}
				$products[] = array(
					'product_id' => $stregth->id,
					'product_code' => $stregth->code,
					'product_name' => $stregth->name,
					'product_type' => $stregth->type,
					'net_unit_price' => $unit_price,
					'unit_price' => $unit_price,
					'real_unit_price' => $unit_price,
					'cost' => $cost,
					'quantity' => $quantity,
					'product_unit_id' => $stregth->unit,
					'product_unit_code' => $unit ? $unit->code : NULL,
					'unit_quantity' => $quantity,
					'warehouse_id' => $warehouse_id,
					'subtotal' => $subtotal,
					'con_delivery_id' => $delivery_id,
					'raw_materials' => json_encode($raw_materials)
				);
				if ($this->Settings->accounting == 1) {
					$productAcc = $this->site->getProductAccByProductId($stregth->id);
					$accTrans[] = array(
						'tran_type' => 'CSale',
						'tran_date' => $date,
						'reference_no' => $reference,
						'account_code' => $productAcc->sale_acc,
						'amount' => ($unit_price * $quantity) * (-1),
						'narrative' => 'Sale',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'created_by' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
				$total_items += $quantity;
				$total += $subtotal;
				$total_cost += ($cost * $quantity);
			}
			if (!$products) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
			} else {
				krsort($products);
			}

			$grand_total = ($total + $truck_charge + $pump_charge);
			$data = array(
				'date' => $date,
				'reference_no' => $reference,
				'customer_id' => $customer_id,
				'customer' => $customer,
				'biller_id' => $biller_id,
				'project_id' => $project_id,
				'biller' => $biller,
				'warehouse_id' => $warehouse_id,
				'note' => $note,
				'total' => $total,
				'truck_charge' => $truck_charge,
				'pump_charge' => $pump_charge,
				'grand_total' => $grand_total,
				'total_items' => $total_items,
				'sale_status' => "pending",
				'payment_term' => $payment_term,
				'due_date' => $due_date,
				'created_by' => $this->session->userdata('user_id'),
				'from_date' => $from_date,
				'to_date' => $to_date,
				'location_id' => $location_id,
				'location_name' => $location->name,
				'total_cost' => $total_cost,
			);
			if ($this->Settings->accounting == 1) {
				$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
				if ($truck_charge > 0) {
					$accTrans[] = array(
						'tran_type' => 'CSale',
						'tran_date' => $date,
						'reference_no' => $reference,
						'account_code' => $saleAcc->other_income_acc,
						'amount' => $truck_charge * (-1),
						'narrative' => 'Truck Charge',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'created_by' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
				if ($pump_charge > 0) {
					$accTrans[] = array(
						'tran_type' => 'CSale',
						'tran_date' => $date,
						'reference_no' => $reference,
						'account_code' => $saleAcc->other_income_acc,
						'amount' => $pump_charge * (-1),
						'narrative' => 'Pump Charge',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'created_by' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
				$accTrans[] = array(
					'tran_type' => 'CSale',
					'tran_date' => $date,
					'reference_no' => $reference,
					'account_code' => $saleAcc->ar_acc,
					'amount' => $grand_total,
					'narrative' => 'Sale',
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'created_by' => $this->session->userdata('user_id'),
					'customer_id' => $customer_id,
				);
			}

			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$attachment = $this->upload->file_name;
				$data['attachment'] = $attachment;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->addSale($data, $products, $accTrans)) {
			$this->session->set_flashdata('message', $this->lang->line("sale_added"));
			admin_redirect('concretes/sales');
		} else {
			$this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['warehouses']   = $this->site->getWarehouses();
			$this->data['billers'] 		= $this->site->getBillers();
			$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
			$this->data['customers'] 	= $this->site->getAllCompanies('customer');
			$this->data['stregths']  	= $this->concretes_model->getStregths();
			$bc   = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('add_sale')));
			$meta = array('page_title' => lang('add_sale'), 'bc' => $bc);
			$this->page_construct('concretes/add_sale', $meta, $this->data);
		}
	}

	public function edit_sale($id = false)
	{
		$this->bpas->checkPermissions();
		$sale_items = $this->concretes_model->getSaleConcreteItemByDelivery($id);
		if ($sale_items) {
			$this->session->set_flashdata('error', lang('sale_is_already_issue_invoice'));
			redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
		}
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('location', $this->lang->line("location"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
		if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->bpas->GP['concretes-sales-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$warehouse_id = $this->input->post('warehouse');
			$customer_id = $this->input->post('customer');
			$note = $this->input->post('note');
			$from_date = $this->bpas->fld(trim($this->input->post('from_date')));
			$to_date = $this->bpas->fld(trim($this->input->post('to_date')));
			$location_id = $this->input->post('location');
			$location = $this->concretes_model->getCustomerLocationByID($location_id);
			$payment_term = $this->input->post('payment_term');
			$due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
			$truck_charge = $this->input->post('truck_charge');
			$pump_charge = $this->input->post('pump_charge');
			$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('csale', $biller_id);
			$i = isset($_POST['stregth_id']) ? sizeof($_POST['stregth_id']) : 0;
			$total = 0;
			$total_items = 0;
			$total_cost = 0;
			$percentage = '%';
			for ($r = 0; $r < $i; $r++) {
				$delivery_id = $_POST['delivery_id'][$r];
				$stregth_id = $_POST['stregth_id'][$r];
				$unit_price = $_POST['unit_price'][$r];
				$quantity = $_POST['quantity'][$r];
				$subtotal = $unit_price * $quantity;
				$stregth = $this->concretes_model->getStregthByID($stregth_id);
				$unit = $this->site->getProductUnit($stregth->id, $stregth->unit);
				$cost = 0;
				$raw_materials = false;
				if ($stregth->type == 'bom') {
					$product_boms = $this->concretes_model->getBomProductByStandProduct($stregth_id, $biller_id);
					if ($product_boms) {
						$product_bom_cost = 0;
						foreach ($product_boms as $product_bom) {
							$raw_materials[] = array(
								"product_id" => $product_bom->product_id,
								"quantity" => ($quantity * $product_bom->quantity)
							);
							$product_bom_cost += ($product_bom->quantity * $product_bom->cost);
						}
						$cost  = $product_bom_cost;
					} else {
						$error = lang('please_check_product') . ' ' . $item_code;
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
				}
				$products[] = array(
					'sale_id' => $id,
					'product_id' => $stregth->id,
					'product_code' => $stregth->code,
					'product_name' => $stregth->name,
					'product_type' => $stregth->type,
					'net_unit_price' => $unit_price,
					'unit_price' => $unit_price,
					'real_unit_price' => $unit_price,
					'cost' => $cost,
					'quantity' => $quantity,
					'product_unit_id' => $stregth->unit,
					'product_unit_code' => $unit ? $unit->code : NULL,
					'unit_quantity' => $quantity,
					'warehouse_id' => $warehouse_id,
					'subtotal' => $subtotal,
					'con_delivery_id' => $delivery_id,
					'raw_materials' => json_encode($raw_materials)
				);

				if ($this->Settings->accounting == 1) {
					$productAcc = $this->site->getProductAccByProductId($stregth->id);
					$accTrans[] = array(
						'tran_type' => 'CSale',
						'tran_no' => $id,
						'tran_date' => $date,
						'reference_no' => $reference,
						'account_code' => $productAcc->sale_acc,
						'amount' => ($unit_price * $quantity) * (-1),
						'narrative' => 'Sale',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'created_by' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}

				$total_items += $quantity;
				$total += $subtotal;
				$total_cost += ($cost * $quantity);
			}
			if (!$products) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
			} else {
				krsort($products);
			}
			$grand_total = ($total + $truck_charge + $pump_charge);
			$data = array(
				'date' => $date,
				'reference_no' => $reference,
				'customer_id' => $customer_id,
				'customer' => $customer,
				'biller_id' => $biller_id,
				'biller' => $biller,
				'project_id' => $project_id,
				'warehouse_id' => $warehouse_id,
				'note' => $note,
				'total' => $total,
				'truck_charge' => $truck_charge,
				'pump_charge' => $pump_charge,
				'grand_total' => $grand_total,
				'total_items' => $total_items,
				'sale_status' => "pending",
				'payment_term' => $payment_term,
				'due_date' => $due_date,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
				'from_date' => $from_date,
				'to_date' => $to_date,
				'location_id' => $location_id,
				'location_name' => $location->name,
				'total_cost' => $total_cost,
			);

			if ($this->Settings->accounting == 1) {
				$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
				if ($truck_charge > 0) {
					$accTrans[] = array(
						'tran_type' => 'CSale',
						'tran_no' => $id,
						'tran_date' => $date,
						'reference_no' => $reference,
						'account_code' => $saleAcc->other_income_acc,
						'amount' => $truck_charge * (-1),
						'narrative' => 'Truck Charge',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'created_by' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
				if ($pump_charge > 0) {
					$accTrans[] = array(
						'tran_type' => 'CSale',
						'tran_no' => $id,
						'tran_date' => $date,
						'reference_no' => $reference,
						'account_code' => $saleAcc->other_income_acc,
						'amount' => $pump_charge * (-1),
						'narrative' => 'Pump Charge',
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'created_by' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
				$accTrans[] = array(
					'tran_type' => 'CSale',
					'tran_no' => $id,
					'tran_date' => $date,
					'reference_no' => $reference,
					'account_code' => $saleAcc->ar_acc,
					'amount' => $grand_total,
					'narrative' => 'Sale',
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'created_by' => $this->session->userdata('user_id'),
					'customer_id' => $customer_id,
				);
			}

			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$attachment = $this->upload->file_name;
				$data['attachment'] = $attachment;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->updateSale($id, $data, $products, $accTrans)) {
			$this->session->set_flashdata('message', $this->lang->line("sale_edited"));
			admin_redirect('concretes/sales');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['sale'] 		= $this->concretes_model->getSaleByID($id);
			$this->data['sale_items'] 	= $this->concretes_model->getSaleItems($id);
			$this->data['warehouses'] 	= $this->site->getWarehouses();
			$this->data['billers'] 		= $this->site->getBillers();
			$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
			$this->data['customers'] 	= $this->site->getAllCompanies('customer');
			$bc   = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('edit_sale')));
			$meta = array('page_title' => lang('edit_sale'), 'bc' => $bc);
			$this->page_construct('concretes/edit_sale', $meta, $this->data);
		}
	}

	public function delete_sale($id = null)
	{
		$this->bpas->checkPermissions(NULL, true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		$sale_items = $this->concretes_model->getSaleConcreteItemByDelivery($id);
		if ($sale_items) {
			$this->session->set_flashdata('error', lang('sale_is_already_issue_invoice'));
			redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
		}
		if ($this->concretes_model->deleteSale($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("sale_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('sale_deleted'));
			admin_redirect('concretes/sales');
		}
	}

	public function sale_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_sale', true);
					foreach ($_POST['val'] as $id) {
						$this->concretes_model->deleteSale($id);
					}
					$this->session->set_flashdata('message', $this->lang->line("sales_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				} elseif ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('sales'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('from_date'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('to_date'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('location'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));

					$this->db->select("
								con_sales.id as id,
								DATE_FORMAT(date, '%Y-%m-%d %T') as date,
								DATE_FORMAT(from_date, '%Y-%m-%d %T') as from_date,
								DATE_FORMAT(to_date, '%Y-%m-%d %T') as to_date,
								reference_no,
								customer,
								location_name,
								grand_total,
								attachment
							")
						->from("con_sales");
					$this->db->where_in("con_sales.id", $_POST['val']);
					$q = $this->db->get();
					$row = 2;
					if ($q->num_rows() > 0) {
						foreach (($q->result()) as $sale) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($sale->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($sale->from_date));
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->hrld($sale->to_date));
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sale->reference_no);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $sale->customer);
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $sale->location_name);
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($sale->grand_total));
							$row++;
						}
					}

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'sales_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function modal_view_sale($id = false, $markup = false)
	{
		$this->bpas->checkPermissions('sales', true);
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$sale = $this->concretes_model->getSaleByID($id);
		$this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
		$this->data['sale'] = $sale;
		$this->data['sale_items'] = $this->concretes_model->getSaleItems($id);
		$this->data['created_by'] = $this->site->getUserByID($sale->created_by);
		$this->data['customer'] = $this->site->getCompanyByID($sale->customer_id);
		if ($markup) {
			$this->load->view($this->theme . 'concretes/modal_view_markup', $this->data);
		} else {
			$this->load->view($this->theme . 'concretes/modal_view_sale', $this->data);
		}
	}
	public function modal_view_sale_daily($id = null)
	{
		$this->bpas->checkPermissions('sales', true);
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$sale = $this->concretes_model->getSaleByID($id);
		$this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
		$this->data['sale'] = $sale;
		$this->data['sale_items'] = $this->concretes_model->getSaleDailyItems($id);
		$this->data['created_by'] = $this->site->getUserByID($sale->created_by);
		$this->data['customer'] = $this->site->getCompanyByID($sale->customer_id);
		$this->load->view($this->theme . 'concretes/modal_view_sale_daily', $this->data);
	}
	public function modal_view_sale_summary($id = null)
	{
		$this->bpas->checkPermissions('sales', true);
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$sale = $this->concretes_model->getSaleByID($id);
		$this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
		$this->data['sale'] = $sale;
		$this->data['sale_items'] = $this->concretes_model->getSaleSummaryItems($id);
		$this->data['created_by'] = $this->site->getUserByID($sale->created_by);
		$this->data['customer'] = $this->site->getCompanyByID($sale->customer_id);
		$this->load->view($this->theme . 'concretes/modal_view_sale_summary', $this->data);
	}


	public function fuels($warehouse_id = null, $biller_id = NULL)
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('fuels')));
		$meta = array('page_title' => lang('fuels'), 'bc' => $bc);
		$this->page_construct('concretes/fuels', $meta, $this->data);
	}


	public function getFuels($warehouse_id = null, $biller_id = NULL)
	{
		$this->bpas->checkPermissions('fuels');
		$edit_link = anchor('admin/concretes/edit_fuel/$1', '<i class="fa fa-edit"></i> ' . lang('edit_fuel'), ' class="edit_fuel" ');
		$delete_link = "<a href='#' class='po delete_fuel' title='<b>" . $this->lang->line("delete_fuel") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('concretes/delete_fuel/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_fuel') . "</a>";
		$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
		$this->load->library('datatables');
		$this->datatables
			->select("id, date, reference_no, total_quantity,  attachment")
			->from("con_fuels");
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('con_fuels.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('con_fuels.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids =  json_decode($this->session->userdata('warehouse_id'));
			$warehouse_ids[] = 99999;
			$this->datatables->where_in('con_fuels.warehouse_id', $warehouse_ids);
		}
		if ($warehouse_id) {
			$this->datatables->where('con_fuels.warehouse_id', $warehouse_id);
		}
		if ($biller_id) {
			$this->datatables->where('con_fuels.biller_id', $biller_id);
		}
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
	}

	public function add_fuel()
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cfuel', $biller_id);
			if ($this->Owner || $this->Admin || $this->bpas->GP['concretes-fuels-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$warehouse_id = $this->input->post('warehouse');
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$note = $this->bpas->clear_tags($this->input->post('note'));
			$total_quantity = 0;
			$i = isset($_POST['truck_id']) ? sizeof($_POST['truck_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$truck_id = $_POST['truck_id'][$r];
				$driver_id = $_POST['driver_id'][$r];
				$quantity = $_POST['quantity'][$r];
				if (isset($truck_id) && isset($quantity)) {
					$truck_detail = $this->concretes_model->getTruckByID($truck_id);
					$product = $this->site->getProductByID($truck_detail->diesel_id);
					$unit = $this->site->getProductUnit($product->id, $product->unit);
					$driver = $this->concretes_model->getDriverByID($driver_id);
					$items[] = array(
						'truck_id' => $truck_id,
						'truck_code' => $truck_detail->code,
						'driver_id' => $driver_id,
						'driver_name' => $driver->company . ' - ' . $driver->name,
						'driver_assistant' => $truck_detail->driver_assistant,
						'quantity' => $quantity,
						'diesel_id' => $product->id,
						'cost' => $product->cost,
						'from_date' => $date,
					);

					//=========================used fuel=============================//
					$fuel_litre = $quantity;
					if ($fuel_litre > 0) {
						$used_fuels[] = array(
							'date' => $date,
							'biller_id' => $biller_id,
							'driver_id' => $driver_id,
							'truck_id' => $truck_id,
							'fuel_litre' => (($fuel_litre * $truck_detail->driver_fuel_fee) / 100)
						);
						if ($truck_detail->driver_assistant && $truck_detail->driver_fuel_fee != 100) {
							$driver_assistants = json_decode($truck_detail->driver_assistant);
							$total_asstants = count($driver_assistants);
							foreach ($driver_assistants as $driver_assistant) {
								$used_fuels[] = array(
									'date' => $date,
									'biller_id' => $biller_id,
									'driver_id' => $driver_assistant,
									'truck_id' => $truck_id,
									'fuel_litre' => ((($fuel_litre * $truck_detail->assistant_fuel_fee) / 100) / $total_asstants)
								);
							}
						}
					}
					//=========================end used fuel=============================//
					$stockmoves[] = array(
						'transaction' => 'CFuel',
						'product_id' => $product->id,
						'product_code' => $product->code,
						'product_type' => $product->type,
						'quantity' => $quantity * (-1),
						'unit_quantity' => $unit->unit_qty,
						'unit_code' => $unit->code,
						'unit_id' => $unit->id,
						'warehouse_id' => $warehouse_id,
						'date' => $date,
						'real_unit_cost' => $product->cost,
						'reference_no' => $reference_no,
						'user_id' => $this->session->userdata('user_id'),
					);
					if ($this->Settings->accounting == 1) {
						$productAcc = $this->site->getProductAccByProductId($product->id);
						$accTrans[] = array(
							'tran_type' 	=> 'CFuel',
							'tran_date' 	=> $date,
							'reference_no' 	=> $reference_no,
							'account_code' 	=> $productAcc->stock_account,
							'amount' 		=> - ($product->cost * $quantity),
							'narrative' 	=> 'Product Code: ' . $product->code . '#' . 'Qty: ' . $quantity . '#' . 'Cost: ' . $product->cost,
							'description' 	=> $note,
							'biller_id' 	=> $biller_id,
							'project_id' 	=> $project_id,
							'created_by' 	=> $this->session->userdata('user_id'),
						);
						$accTrans[] = array(
							'tran_type' 	=> 'CFuel',
							'tran_date' 	=> $date,
							'reference_no' 	=> $reference_no,
							'account_code' 	=> $productAcc->using_account,
							'amount' 		=> ($product->cost * $quantity),
							'narrative' 	=> 'Product Code: ' . $product->code . '#' . 'Qty: ' . $quantity . '#' . 'Cost: ' . $product->cost,
							'description' 	=> $note,
							'biller_id' 	=> $biller_id,
							'project_id' 	=> $project_id,
							'created_by' 	=> $this->session->userdata('user_id'),
						);
					}

					$total_quantity += $quantity;
				}
			}
			if (empty($items)) {
				$this->form_validation->set_rules('truck', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			if ($warehouse_id == 99999) {
				$stockmoves = false;
			}
			$data = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'biller_id' => $biller_id,
				'biller' => $biller,
				'project_id' => $project_id,
				'warehouse_id' => $warehouse_id,
				'note' => $note,
				'total_quantity' => $total_quantity,
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s'),
			);

			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->addFuel($data, $items, $stockmoves, $used_fuels, $accTrans)) {
			$this->session->set_userdata('remove_confuls', 1);
			$this->session->set_flashdata('message', $this->lang->line("fuel_added") . " " . $reference_no);
			if ($this->input->post('add_fuel_next')) {
				admin_redirect('concretes/add_fuel');
			} else {
				admin_redirect('concretes/fuels');
			}
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] =  $this->site->getBillers();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/fuels'), 'page' => lang('fuels')), array('link' => '#', 'page' => lang('add_fuel')));
			$meta = array('page_title' => lang('add_fuel'), 'bc' => $bc);
			$this->page_construct('concretes/add_fuel', $meta, $this->data);
		}
	}
	public function edit_fuel($id = false)
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cfuel', $biller_id);
			if ($this->Owner || $this->Admin || $this->bpas->GP['concretes-fuels-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$warehouse_id = $this->input->post('warehouse');
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$note = $this->bpas->clear_tags($this->input->post('note'));
			$total_quantity = 0;
			$i = isset($_POST['truck_id']) ? sizeof($_POST['truck_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$truck_id = $_POST['truck_id'][$r];
				$driver_id = $_POST['driver_id'][$r];
				$quantity = $_POST['quantity'][$r];
				if (isset($truck_id) && isset($quantity)) {
					$truck_detail = $this->concretes_model->getTruckByID($truck_id);
					$product = $this->site->getProductByID($truck_detail->diesel_id);
					$unit = $this->site->getProductUnit($product->id, $product->unit);
					$driver = $this->concretes_model->getDriverByID($driver_id);

					$items[] = array(
						'fuel_id' 		=> $id,
						'truck_id' 		=> $truck_id,
						'truck_code' 	=> $truck_detail->code,
						'driver_id' 	=> $driver_id,
						'driver_name' 	=> $driver->name,
						'driver_assistant' => $truck_detail->driver_assistant,
						'quantity' 		=> $quantity,
						'diesel_id' 	=> $product->id,
						'cost' 			=> $product->cost,
						'from_date' 	=> $date,
					);

					//=========================used fuel=============================//
					$fuel_litre = $quantity;
					if ($fuel_litre > 0) {
						$used_fuels[] = array(
							'fuel_id' => $id,
							'date' => $date,
							'biller_id' => $biller_id,
							'driver_id' => $driver_id,
							'truck_id' => $truck_id,
							'fuel_litre' => (($fuel_litre * $truck_detail->driver_fuel_fee) / 100)
						);
						if ($truck_detail->driver_assistant && $truck_detail->driver_fuel_fee != 100) {
							$driver_assistants = json_decode($truck_detail->driver_assistant);
							$total_asstants = count($driver_assistants);
							foreach ($driver_assistants as $driver_assistant) {
								$used_fuels[] = array(
									'fuel_id' => $id,
									'date' => $date,
									'biller_id' => $biller_id,
									'driver_id' => $driver_assistant,
									'truck_id' => $truck_id,
									'fuel_litre' => ((($fuel_litre * $truck_detail->assistant_fuel_fee) / 100) / $total_asstants)
								);
							}
						}
					}
					//=========================end used fuel=============================//

					$stockmoves[] = array(
						'transaction' 	=> 'CFuel',
						'transaction_id' => $id,
						'product_id' 	=> $product->id,
						'product_code' 	=> $product->code,
						'product_type' 	=> $product->type,
						'quantity' 		=> $quantity * (-1),
						'unit_quantity' => $unit->unit_qty,
						'unit_code' 	=> $unit->code,
						'unit_id' 		=> $unit->id,
						'warehouse_id' 	=> $warehouse_id,
						'date' 			=> $date,
						'real_unit_cost' => $product->cost,
						'reference_no' 	=> $reference_no,
						'user_id' 		=> $this->session->userdata('user_id'),
					);

					if ($this->Settings->accounting == 1) {
						$productAcc = $this->site->getProductAccByProductId($product->id);
						$accTrans[] = array(
							'tran_type' 	=> 'CFuel',
							'tran_no' 		=> $id,
							'tran_date' 	=> $date,
							'reference_no' 	=> $reference_no,
							'account_code' 	=> $productAcc->stock_account,
							'amount' 		=> - ($product->cost * $quantity),
							'narrative' 	=> 'Product Code: ' . $product->code . '#' . 'Qty: ' . $quantity . '#' . 'Cost: ' . $product->cost,
							'description' 	=> $note,
							'biller_id' 	=> $biller_id,
							'project_id' 	=> $project_id,
							'created_by' 	=> $this->session->userdata('user_id'),
						);
						$accTrans[] = array(
							'tran_type' 	=> 'CFuel',
							'tran_no' 		=> $id,
							'tran_date' 	=> $date,
							'reference_no' 	=> $reference_no,
							'account_code' 	=> $productAcc->usage_acc,
							'amount' 		=> ($product->cost * $quantity),
							'narrative' 	=> 'Product Code: ' . $product->code . '#' . 'Qty: ' . $quantity . '#' . 'Cost: ' . $product->cost,
							'description' 	=> $note,
							'biller_id' 	=> $biller_id,
							'project_id' 	=> $project_id,
							'created_by' 	=> $this->session->userdata('user_id'),
						);
					}

					$total_quantity += $quantity;
				}
			}
			if (empty($items)) {
				$this->form_validation->set_rules('truck', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			if ($warehouse_id == 99999) {
				$stockmoves = false;
			}
			$data = array(
				'date' 			=> $date,
				'reference_no' 	=> $reference_no,
				'biller_id' 	=> $biller_id,
				'biller' 		=> $biller,
				'project_id' 	=> $project_id,
				'warehouse_id' 	=> $warehouse_id,
				'note' 			=> $note,
				'total_quantity' => $total_quantity,
				'updated_by' 	=> $this->session->userdata('user_id'),
				'updated_at' 	=> date('Y-m-d H:i:s'),
			);

			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->updateFuel($id, $data, $items, $stockmoves, $used_fuels, $accTrans)) {
			$this->session->set_userdata('remove_confuls', 1);
			$this->session->set_flashdata('message', $this->lang->line("fuel_edited") . " " . $reference_no);
			admin_redirect('concretes/fuels');
		} else {
			$fuel = $this->concretes_model->getFuelByID($id);
			$fuel_items = $this->concretes_model->getFuelItems($id);
			$drivers = $this->concretes_model->getDrivers();
			krsort($fuel_items);
			$c = rand(100000, 9999999);
			foreach ($fuel_items as $item) {
				$row = $this->concretes_model->getTruckByID($item->truck_id);
				$row->quantity = $item->quantity;
				$row->driver_id = $item->driver_id;
				$pr[$row->id] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->code . " - " . $row->plate, 'row' => $row, 'drivers' => $drivers);
				$c++;
			}
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['fuel'] = $fuel;
			$this->data['fuel_items'] = json_encode($pr);
			$this->data['billers'] =  $this->site->getBillers();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->session->set_userdata('remove_confuls', 1);
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/fuels'), 'page' => lang('fuels')), array('link' => '#', 'page' => lang('edit_fuel')));
			$meta = array('page_title' => lang('edit_fuel'), 'bc' => $bc);
			$this->page_construct('concretes/edit_fuel', $meta, $this->data);
		}
	}

	public function delete_fuel($id = null)
	{
		$this->bpas->checkPermissions(NULL, true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->concretes_model->deleteFuel($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("fuel_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('fuel_deleted'));
			admin_redirect('concretes/fuels');
		}
	}
	public function fuel_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_fuel', true);
					foreach ($_POST['val'] as $id) {
						$this->concretes_model->deleteFuel($id);
					}
					$this->session->set_flashdata('message', $this->lang->line("fuel_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				} elseif ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('fuel'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('total'));
					$row = 2;
					foreach ($_POST['val'] as $id) {
						$fuel = $this->concretes_model->getFuelByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($fuel->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $fuel->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $fuel->total_quantity);
						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'fuels_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function truck_suggestions($mission = false)
	{
		$term = $this->input->get('term', true);
		if (strlen($term) < 1 || !$term) {
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
		}
		$analyzed = $this->bpas->analyze_term($term);
		$sr = $analyzed['term'];
		$rows = $this->concretes_model->getTruckNames($sr);
		$drivers = $this->concretes_model->getDrivers();
		if ($mission) {
			$mission_types = $this->concretes_model->getMissionTypes();
		} else {
			$mission_types = false;
		}
		if ($rows) {
			$c = str_replace(".", "", microtime(true));
			$r = 0;
			foreach ($rows as $row) {
				$row->quantity = 1;
				$row->times_hours = 1;
				$row->driver_id = $row->driver_id;
				$row->date = $this->bpas->hrsd(date("Y-m-d"));
				$pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->code . " - " . $row->plate, 'row' => $row, 'drivers' => $drivers, 'mission_types' => $mission_types);
				$r++;
			}
			$this->bpas->send_json($pr);
		} else {
			$this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
		}
	}
	public function modal_view_fuel($id = null)
	{
		$this->bpas->checkPermissions('fuels', true);
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$fuel = $this->concretes_model->getFuelByID($id);
		$this->data['biller'] = $this->site->getCompanyByID($fuel->biller_id);
		$this->data['fuel'] = $fuel;
		$this->data['fuel_items'] = $this->concretes_model->getFuelItems($id);;
		$this->data['created_by'] = $this->site->getUserByID($fuel->created_by);
		$this->load->view($this->theme . 'concretes/modal_view_fuel', $this->data);
	}
	public function fuels_report()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['trucks'] = $this->concretes_model->getTrucks("truck");
		$this->data['pumps'] = $this->concretes_model->getTrucks("pump");
		$this->data['drivers'] =  $this->site->getAllCompanies('driver');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('fuels_report')));
		$meta = array('page_title' => lang('fuels_report'), 'bc' => $bc);
		$this->page_construct('concretes/fuels_report', $meta, $this->data);
	}
	public function getFuelsReport($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('fuels_report');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$pump = $this->input->get('pump') ? $this->input->get('pump') : NULL;
		$truck = $this->input->get('truck') ? $this->input->get('truck') : NULL;
		$driver = $this->input->get('driver') ? $this->input->get('driver') : NULL;
		$warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(" . $this->db->dbprefix('con_fuels') . ".date, '%Y-%m-%d %T') as date,
								IFNULL(" . $this->db->dbprefix('warehouses') . ".name,'Other Warehouse') as warehouse_name,
								con_fuels.reference_no,
								con_fuel_items.truck_code,
								con_trucks.type,
								con_fuel_items.driver_name,
								con_fuel_items.quantity,
								con_fuel_items.from_date,
								con_fuel_items.to_date,
								IFNULL(count(delivery_truck.id),0) + IFNULL(count(delivery_pump.id),0) AS delivery_times,
								IFNULL(SUM(delivery_truck.kilometer),0) + IFNULL(SUM(delivery_pump.kilometer),0) AS kilometer,
								con_fuels.id as id")
				->from("con_fuels")
				->join("con_fuel_items", "con_fuels.id = con_fuel_items.fuel_id", "inner")
				->join("con_trucks", "con_trucks.id = con_fuel_items.truck_id", "left")
				->join("warehouses", "warehouses.id = con_fuels.warehouse_id", "left")
				->join(
					"(SELECT 
										" . $this->db->dbprefix('con_deliveries') . " .id, 
										" . $this->db->dbprefix('con_deliveries') . " .truck_id, 
										" . $this->db->dbprefix('con_deliveries') . " .date,
										" . $this->db->dbprefix('addresses') . ". kilometer
									FROM 
										" . $this->db->dbprefix('con_deliveries') . " 
									LEFT JOIN " . $this->db->dbprefix('addresses') . "  ON " . $this->db->dbprefix('addresses') . ".id = " . $this->db->dbprefix('con_deliveries') . ".location_id
								) AS delivery_truck",
					"delivery_truck.truck_id = con_fuel_items.truck_id 
								AND delivery_truck.date >= con_fuel_items.from_date
								AND delivery_truck.date < IFNULL(" . $this->db->dbprefix('con_fuel_items') . ".to_date,'2050-12-12')",
					"left"
				)
				->join(
					"(SELECT 
										" . $this->db->dbprefix('con_deliveries') . ".id, 
										" . $this->db->dbprefix('con_deliveries') . ".pump_id, 
										" . $this->db->dbprefix('con_deliveries') . ".date,
										" . $this->db->dbprefix('addresses') . ". kilometer
									FROM 
										" . $this->db->dbprefix('con_deliveries') . "
									LEFT JOIN " . $this->db->dbprefix('addresses') . "  ON " . $this->db->dbprefix('addresses') . ".id = " . $this->db->dbprefix('con_deliveries') . ".location_id
								) AS delivery_pump",
					"delivery_pump.pump_id = con_fuel_items.truck_id 
								AND delivery_pump.date >= con_fuel_items.from_date
								AND delivery_pump.date < IFNULL(" . $this->db->dbprefix('con_fuel_items') . ".to_date,'2050-12-12')",
					"left"
				)
				->group_by("con_fuel_items.id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_fuels.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_fuels.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$warehouse_ids =  json_decode($this->session->userdata('warehouse_id'));
				$warehouse_ids[] = 99999;
				$this->db->where_in('con_fuels.warehouse_id', $warehouse_ids);
			}
			if ($start_date) {
				$this->db->where('con_fuels.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_fuels.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_fuels.biller_id', $biller);
			}
			if ($pump) {
				$this->db->where('con_fuel_items.truck_id', $pump);
			}
			if ($truck) {
				$this->db->where('con_fuel_items.truck_id', $truck);
			}
			if ($driver) {
				$this->db->where('con_fuel_items.driver_id', $driver);
			}
			if ($warehouse) {
				$this->db->where('con_fuels.warehouse_id', $warehouse);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('fuels_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('warehouse'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('reference'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('truck'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('type'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('driver'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('quantity'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('from_date'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('to_date'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('delivery_times'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('kilometer'));

				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->warehouse_name);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->truck_code);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->type);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->driver_name);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($data_row->quantity));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->hrld($data_row->from_date));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, ($data_row->to_date ? $this->bpas->hrld($data_row->to_date) : ''));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->delivery_times));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($data_row->kilometer));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
				$filename = 'fuels_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
									DATE_FORMAT(" . $this->db->dbprefix('con_fuels') . ".date, '%Y-%m-%d %T') as date,
									IFNULL(" . $this->db->dbprefix('warehouses') . ".name,'Other Warehouse') as warehouse_name,
									con_fuels.reference_no,
									con_fuel_items.truck_code,
									con_trucks.type,
									con_fuel_items.driver_name,
									con_fuel_items.quantity,
									con_fuel_items.from_date,
									con_fuel_items.to_date,
									IFNULL(count(delivery_truck.id),0) + IFNULL(count(delivery_pump.id),0) AS delivery_times,
									IFNULL(SUM(delivery_truck.kilometer),0) + IFNULL(SUM(delivery_pump.kilometer),0) AS kilometer,
									con_fuels.id as id")
				->from("con_fuels")
				->join("con_fuel_items", "con_fuels.id = con_fuel_items.fuel_id", "inner")
				->join("con_trucks", "con_trucks.id = con_fuel_items.truck_id", "left")
				->join("warehouses", "warehouses.id = con_fuels.warehouse_id", "left")
				->join(
					"(SELECT 
											" . $this->db->dbprefix('con_deliveries') . " .id, 
											" . $this->db->dbprefix('con_deliveries') . " .truck_id, 
											" . $this->db->dbprefix('con_deliveries') . " .date,
											" . $this->db->dbprefix('addresses') . ". kilometer
										FROM 
											" . $this->db->dbprefix('con_deliveries') . " 
										LEFT JOIN " . $this->db->dbprefix('addresses') . "  ON " . $this->db->dbprefix('addresses') . ".id = " . $this->db->dbprefix('con_deliveries') . ".location_id
									) AS delivery_truck",
					"delivery_truck.truck_id = con_fuel_items.truck_id 
									AND delivery_truck.date >= con_fuel_items.from_date
									AND delivery_truck.date < IFNULL(" . $this->db->dbprefix('con_fuel_items') . ".to_date,'2050-12-12')",
					"left"
				)
				->join(
					"(SELECT 
											" . $this->db->dbprefix('con_deliveries') . ".id, 
											" . $this->db->dbprefix('con_deliveries') . ".pump_id, 
											" . $this->db->dbprefix('con_deliveries') . ".date,
											" . $this->db->dbprefix('addresses') . ". kilometer
										FROM 
											" . $this->db->dbprefix('con_deliveries') . "
										LEFT JOIN " . $this->db->dbprefix('addresses') . "  ON " . $this->db->dbprefix('addresses') . ".id = " . $this->db->dbprefix('con_deliveries') . ".location_id
									) AS delivery_pump",
					"delivery_pump.pump_id = con_fuel_items.truck_id 
									AND delivery_pump.date >= con_fuel_items.from_date
									AND delivery_pump.date < IFNULL(" . $this->db->dbprefix('con_fuel_items') . ".to_date,'2050-12-12')",
					"left"
				)
				->group_by("con_fuel_items.id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_fuels.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_fuels.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$warehouse_ids =  json_decode($this->session->userdata('warehouse_id'));
				$warehouse_ids[] = 99999;
				$this->datatables->where_in('con_fuels.warehouse_id', $warehouse_ids);
			}
			if ($start_date) {
				$this->datatables->where('con_fuels.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_fuels.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_fuels.biller_id', $biller);
			}
			if ($pump) {
				$this->datatables->where('con_fuel_items.truck_id', $pump);
			}
			if ($truck) {
				$this->datatables->where('con_fuel_items.truck_id', $truck);
			}
			if ($driver) {
				$this->datatables->where('con_fuel_items.driver_id', $driver);
			}
			if ($warehouse) {
				$this->datatables->where('con_fuels.warehouse_id', $warehouse);
			}
			echo $this->datatables->generate();
		}
	}
	public function fuel_summaries_report()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['trucks'] = $this->concretes_model->getTrucks("truck");
		$this->data['pumps'] = $this->concretes_model->getTrucks("pump");
		$this->data['drivers'] =  $this->site->getAllCompanies('driver');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('fuel_summaries_report')));
		$meta = array('page_title' => lang('fuel_summaries_report'), 'bc' => $bc);
		$this->page_construct('concretes/fuel_summaries_report', $meta, $this->data);
	}
	public function getFuelSummariesReport($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('fuel_summaries_report');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$pump = $this->input->get('pump') ? $this->input->get('pump') : NULL;
		$truck = $this->input->get('truck') ? $this->input->get('truck') : NULL;
		$driver = $this->input->get('driver') ? $this->input->get('driver') : NULL;
		$warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
								con_fuel_items.truck_code,
								con_trucks.type,
								con_fuel_items.driver_name,
								sum(" . $this->db->dbprefix('con_fuel_items') . ".quantity) as quantity,
								count(" . $this->db->dbprefix('con_fuel_items') . ".id) as fuel_times,
								IFNULL((SELECT count(id) as id FROM " . $this->db->dbprefix('con_deliveries') . " WHERE (truck_id = " . $this->db->dbprefix('con_fuel_items') . ".truck_id OR pump_id = " . $this->db->dbprefix('con_fuel_items') . ".truck_id ) AND date >= min(" . $this->db->dbprefix('con_fuel_items') . ".from_date ) AND date < max(IFNULL(" . $this->db->dbprefix('con_fuel_items') . ".to_date,'2050-12-12'))),0) as delivery_times
							")
				->from("con_fuels")
				->join("con_fuel_items", "con_fuels.id = con_fuel_items.fuel_id", "inner")
				->join("con_trucks", "con_trucks.id = con_fuel_items.truck_id", "left")
				->group_by("con_fuel_items.truck_code");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_fuels.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_fuels.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$warehouse_ids =  json_decode($this->session->userdata('warehouse_id'));
				$warehouse_ids[] = 99999;
				$this->db->where_in('con_fuels.warehouse_id', $warehouse_ids);
			}
			if ($start_date) {
				$this->db->where('con_fuels.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_fuels.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_fuels.biller_id', $biller);
			}
			if ($pump) {
				$this->db->where('con_fuel_items.truck_id', $pump);
			}
			if ($truck) {
				$this->db->where('con_fuel_items.truck_id', $truck);
			}
			if ($driver) {
				$this->db->where('con_fuel_items.driver_id', $driver);
			}
			if ($warehouse) {
				$this->db->where('con_fuels.warehouse_id', $warehouse);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('fuel_summaries_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('truck'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('type'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('driver'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('quantity'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('fuel_times'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('delivery_times'));

				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->truck_code);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->type);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->driver_name);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($data_row->quantity));
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($data_row->fuel_times));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($data_row->delivery_times));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$filename = 'fuel_summaries_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
										con_fuel_items.truck_code,
										con_trucks.type,
										con_fuel_items.driver_name,
										sum(" . $this->db->dbprefix('con_fuel_items') . ".quantity) as quantity,
										count(" . $this->db->dbprefix('con_fuel_items') . ".id) as fuel_times,
										IFNULL((SELECT count(id) as id FROM " . $this->db->dbprefix('con_deliveries') . " WHERE (truck_id = " . $this->db->dbprefix('con_fuel_items') . ".truck_id OR pump_id = " . $this->db->dbprefix('con_fuel_items') . ".truck_id ) AND date >= min(" . $this->db->dbprefix('con_fuel_items') . ".from_date ) AND date < max(IFNULL(" . $this->db->dbprefix('con_fuel_items') . ".to_date,'2050-12-12'))),0) as delivery_times
									")
				->from("con_fuels")
				->join("con_fuel_items", "con_fuels.id = con_fuel_items.fuel_id", "inner")
				->join("con_trucks", "con_trucks.id = con_fuel_items.truck_id", "left")
				->group_by("con_fuel_items.truck_code");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_fuels.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_fuels.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$warehouse_ids =  json_decode($this->session->userdata('warehouse_id'));
				$warehouse_ids[] = 99999;
				$this->datatables->where_in('con_fuels.warehouse_id', $warehouse_ids);
			}
			if ($start_date) {
				$this->datatables->where('con_fuels.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_fuels.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_fuels.biller_id', $biller);
			}
			if ($pump) {
				$this->datatables->where('con_fuel_items.truck_id', $pump);
			}
			if ($truck) {
				$this->datatables->where('con_fuel_items.truck_id', $truck);
			}
			if ($driver) {
				$this->datatables->where('con_fuel_items.driver_id', $driver);
			}
			if ($warehouse) {
				$this->datatables->where('con_fuels.warehouse_id', $warehouse);
			}
			echo $this->datatables->generate();
		}
	}
	public function fuel_by_customer_report()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] 	= $this->site->getAllCompanies('biller');
		$this->data['trucks']	= $this->concretes_model->getTrucks("truck");
		$this->data['pumps'] 	= $this->concretes_model->getTrucks("pump");
		$this->data['drivers'] 	=  $this->site->getAllCompanies('driver');
		$this->data['fuel_items'] = $this->concretes_model->getAllFuleItems($this->input->post());
		$this->data['delivries'] = $this->concretes_model->getDeliveries();
		$this->data['warehouses'] = $this->site->getWarehouses();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('fuel_by_customer_report')));
		$meta = array('page_title' => lang('fuel_by_customer_report'), 'bc' => $bc);
		$this->page_construct('concretes/fuel_by_customer_report', $meta, $this->data);
	}
	public function fuel_details_report()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['trucks'] = $this->concretes_model->getTrucks("truck");
		$this->data['pumps'] = $this->concretes_model->getTrucks("pump");
		$this->data['drivers'] =  $this->site->getAllCompanies('driver');
		$this->data['fuel_items'] = $this->concretes_model->getAllFuleItems($this->input->post());
		$this->data['delivries'] = $this->concretes_model->getDeliveries();
		$this->data['warehouses'] = $this->site->getWarehouses();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('fuel_details_report')));
		$meta = array('page_title' => lang('fuel_details_report'), 'bc' => $bc);
		$this->page_construct('concretes/fuel_details_report', $meta, $this->data);
	}

	function sales_report()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['users'] = $this->site->getStaff();
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('sales_report')));
		$meta = array('page_title' => lang('sales_report'), 'bc' => $bc);
		$this->page_construct('concretes/sales_report', $meta, $this->data);
	}

	function getSalesReport($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('sales_report', TRUE);
		$user = $this->input->get('user') ? $this->input->get('user') : NULL;
		$customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$project = $this->input->get('project') ? $this->input->get('project') : NULL;
		$warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
		$start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
		$end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
		if ($start_date) {
			$start_date = $this->bpas->fld($start_date);
		}
		if ($end_date) {
			$end_date = $this->bpas->fld($end_date, false, 1);
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$user = $this->session->userdata('user_id');
		}
		if ($pdf || $xls) {
			$this->db->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, 
							con_sales.reference_no,
							con_sales.customer,
							con_sales.location_name,
							CONCAT (" . $this->db->dbprefix('users') . ".last_name,' '," . $this->db->dbprefix('users') . ".first_name) as created_by, 
							con_sales.grand_total,
							IFNULL(" . $this->db->dbprefix('con_sales') . ".total_cost,0) as total_cost,
							IFNULL(" . $this->db->dbprefix('con_sales') . ".grand_total,0) - IFNULL(" . $this->db->dbprefix('con_sales') . ".total_cost,0) as gross_profit,
						", FALSE)
				->from('con_sales')
				->join('users', 'users.id = con_sales.created_by', 'left');
			if ($user) {
				$this->db->where('con_sales.created_by', $user);
			}
			if ($biller) {
				$this->db->where('con_sales.biller_id', $biller);
			}
			if ($project) {
				$this->db->where('con_sales.project_id', $project);
			}
			if ($customer) {
				$this->db->where('con_sales.customer_id', $customer);
			}
			if ($warehouse) {
				$this->db->where('con_sales.warehouse_id', $warehouse);
			}
			if ($start_date) {
				$this->db->where('con_sales.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_sales.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_sales.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->db->where_in('con_sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}

			$q = $this->db->get();
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			} else {
				$data = NULL;
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('sales_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('location'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('created_by'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
				if ($this->Owner || $this->Admin || $this->session->userdata('show_cost')) {
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('total_cost'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('gross_profit'));
				}

				$row = 2;
				$total = 0;
				$total_cost = 0;
				$total_profit = 0;

				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->location_name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->created_by);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($data_row->grand_total));
					if ($this->Owner || $this->Admin || $this->session->userdata('show_cost')) {
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($data_row->total_cost));
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($data_row->gross_profit));
					}
					$total += $data_row->grand_total;
					$total_cost += $data_row->total_cost;
					$total_profit += $data_row->gross_profit;
					$row++;
				}

				if ($this->Owner || $this->Admin || $this->session->userdata('show_cost')) {
					$this->excel->getActiveSheet()->getStyle("F" . $row . ":H" . $row)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($total));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($total_cost));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($total_profit));
				} else {
					$this->excel->getActiveSheet()->getStyle("F" . $row . ":F" . $row)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($total));
				}



				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

				$filename = 'sales_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, 
						con_sales.reference_no,
						con_sales.customer,
						con_sales.location_name,
						CONCAT (" . $this->db->dbprefix('users') . ".last_name,' '," . $this->db->dbprefix('users') . ".first_name) as created_by, 
						con_sales.grand_total,
						IFNULL(" . $this->db->dbprefix('con_sales') . ".total_cost,0) as total_cost,
						IFNULL(" . $this->db->dbprefix('con_sales') . ".grand_total,0) - IFNULL(" . $this->db->dbprefix('con_sales') . ".total_cost,0) as gross_profit,
						{$this->db->dbprefix('con_sales')}.id as id,", FALSE)
				->from('con_sales')
				->join('users', 'users.id = con_sales.created_by', 'left');
			if ($user) {
				$this->datatables->where('con_sales.created_by', $user);
			}
			if ($biller) {
				$this->datatables->where('con_sales.biller_id', $biller);
			}
			if ($project) {
				$this->datatables->where('con_sales.project_id', $project);
			}
			if ($customer) {
				$this->datatables->where('con_sales.customer_id', $customer);
			}
			if ($warehouse) {
				$this->datatables->where('con_sales.warehouse_id', $warehouse);
			}
			if ($start_date) {
				$this->datatables->where('con_sales.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_sales.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_sales.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->datatables->where_in('con_sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			echo $this->datatables->generate();
		}
	}
	public function sale_details_report()
	{
		$this->bpas->checkPermissions();
		if (isset($_POST['form_action']) && $_POST['form_action'] == "export_excel") {
			$this->sale_details_actions(true);
		}
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['users'] = $this->site->getStaff();
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['stregths'] = $this->concretes_model->getStregths();
		$this->data['sales'] = $this->concretes_model->getSaleDetails($this->input->post());
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('sale_details_report')));
		$meta = array('page_title' => lang('sale_details_report'), 'bc' => $bc);
		$this->page_construct('concretes/sale_details_report', $meta, $this->data);
	}
	public function sale_details_actions($xls = false, $pdf = false)
	{
		if ($xls || $pdf) {
			$post = $this->input->post();
			if ($post['user']) {
				$this->db->where('con_sales.created_by', $post['user']);
			}
			if ($post['biller']) {
				$this->db->where('con_sales.biller_id', $post['biller']);
			}
			if ($post['project']) {
				$this->db->where('con_sales.project_id', $post['project']);
			}
			if ($post['product']) {
				$this->db->where('con_sale_items.product_id', $post['product']);
			}
			if ($post['customer']) {
				$this->db->where('con_sales.customer_id', $post['customer']);
			}
			if ($post['warehouse']) {
				$this->db->where('con_sales.warehouse_id', $post['warehouse']);
			}
			if ($post['start_date']) {
				$this->db->where('date >=', $this->bpas->fld($post['start_date']));
			} else {
				$this->db->where('date(date) >=', date('Y-m-d'));
			}
			if ($post['end_date']) {
				$this->db->where('date <=', $this->bpas->fld($post['end_date'], false, 1));
			} else {
				$this->db->where('date(date) <=', date('Y-m-d'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_sales.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->db->where_in('con_sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			$this->db->select("con_sales.*")
				->from('con_sales')
				->join("con_sale_items", "con_sale_items.sale_id=con_sales.id", "left")
				->group_by('con_sales.id')
				->order_by("con_sales.id", "desc");
			$q = $this->db->get();
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			} else {
				$data = NULL;
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('sale_details_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('location'));

				$this->excel->getActiveSheet()->SetCellValue('E1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('stregth'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('quantity'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('unit_price'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('subtotal'));

				$this->excel->getActiveSheet()->SetCellValue('J1', lang('total'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('truck_charge'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('pump_charge'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('grand_total'));


				$style = array('font'  => array('bold'  => true, 'size'  => 9, 'name'  => 'Arial'));
				$this->excel->getActiveSheet()->getStyle('A1:M1')->applyFromArray($style);
				$row = 2;
				$total = 0;
				$grand_total = 0;
				$truck_charge = 0;
				$pump_charge = 0;
				foreach ($data as $data_row) {
					$total += $data_row->total;
					$truck_charge += $data_row->truck_charge;
					$pump_charge += $data_row->pump_charge;
					$grand_total += $data_row->grand_total;


					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->location_name);

					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->total));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($data_row->truck_charge));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($data_row->pump_charge));
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->formatDecimal($data_row->grand_total));


					$style = array('font'  => array('size'  => 9, 'name'  => 'Arial'));
					$this->excel->getActiveSheet()->getStyle('A' . $row . ':M' . $row)->applyFromArray($style);

					$con_sale_items = $this->concretes_model->getSummarySaleItems($data_row->id);
					foreach ($con_sale_items as $i => $item) {
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $item->product_code);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $item->product_name);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($item->unit_quantity));
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($item->unit_price));
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($item->subtotal));
						$this->excel->getActiveSheet()->getStyle('A' . $row . ':M' . $row)->applyFromArray($style);
						$row++;
					}
					$row++;
				}
				$this->excel->getActiveSheet()->getStyle("J" . $row . ":M" . $row)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
				$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($total));
				$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($truck_charge));
				$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($pump_charge));
				$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->formatDecimal($grand_total));

				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(20);


				$filename = 'sale_details_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function product_sales_report()
	{
		$this->bpas->checkPermissions();
		$product = $this->input->post("product");
		$warehouse_id = $this->input->post("warehouse");
		$start_date = $this->input->post("start_date");
		$end_date = $this->input->post("end_date");
		$biller = $this->input->post("biller");
		$customer = $this->input->post("customer");
		if (isset($_POST['xls']) || isset($_POST['pdf'])) {
			$q = $this->db->get('categories');
			$allow_category = $this->site->getCategoryByProject();
			if ($allow_category) {
				$this->db->where_in('categories.id', $allow_category);
			}
			if ($q->num_rows() > 0) {
				foreach ($q->result() as $row) {
					$categories[] = $row;
				}
			} else {
				$categories = null;
			}

			if (!empty($categories)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('product_sales_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('product_type'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));

				$this->excel->getActiveSheet()->SetCellValue('F1', lang('sold'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('unit_price'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('cost'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('subtotal'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('gross_profit'));

				$total = 0;
				if ($categories) {
					$row   = 2;
					$style = array('font'  => array('bold'  => true,));
					foreach ($categories as $category_row) {
						$data = $this->concretes_model->getProductBySales($category_row->id, $start_date, $end_date, $product, $warehouse_id, $biller, $customer);
						if ($data) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $category_row->name);
							$row += 1;
							$total_quantity 	= 0;
							$total_unit_price	= 0;
							$total_discount		= 0;
							$total_cost 		= 0;
							$total_subtotal 	= 0;
							$total_net_profit	= 0;
							foreach ($data as $data_row) {
								$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->product_code);
								$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->product_name);
								$this->excel->getActiveSheet()->SetCellValue('C' . $row, ucfirst($data_row->product_type));
								$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->reference_no);
								$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->customer);
								$this->excel->getActiveSheet()->SetCellValue('F' . $row, strip_tags(html_entity_decode($this->bpas->convertQty($data_row->product_id, $data_row->quantity))));
								$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($data_row->unit_price));
								$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($data_row->cost));
								$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->subtotal));
								$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->subtotal - $data_row->cost));
								$total_quantity += $data_row->quantity;
								$total_unit_price += $data_row->unit_price;
								$total_cost += $data_row->cost;
								$total_subtotal += $data_row->subtotal;
								$total_net_profit += $data_row->subtotal - $data_row->cost;
								$row++;
							}
							$this->excel->getActiveSheet()->getStyle('A' . $row . ':J' . $row . '')->applyFromArray($style);
							$this->excel->getActiveSheet()->getStyle('A' . $row . ':J' . $row . '')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($total_quantity));
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($total_unit_price));
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($total_cost));
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($total_subtotal));
							$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($total_net_profit));

							$grand_total_quantity 	+= $total_quantity;
							$grand_total_unit_price += $total_unit_price;
							$grand_total_cost		+= $total_cost;
							$grand_total_subtotal 	+= $total_subtotal;
							$grand_total_net_profit += $total_net_profit;
							$row++;
						}
					}
					$this->excel->getActiveSheet()->getStyle('A' . $row . ':J' . $row . '')->applyFromArray($style);
					$this->excel->getActiveSheet()->getStyle('A' . $row . ':J' . $row . '')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$style = array(
						'font'  => array(
							'bold'  => true,
							'color' => array('rgb' => 'FF0000')
						)
					);
					$this->excel->getActiveSheet()->getStyle("E" . $row . ":J" . $row)->getBorders()
						->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
					$this->excel->getActiveSheet()->getStyle('A' . $row . ':J' . $row . '')->applyFromArray($style);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, lang('total'));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($grand_total_quantity));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($grand_total_unit_price));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($grand_total_cost));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($grand_total_subtotal));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($grand_total_net_profit));
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);

				$filename = 'product_sales_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		}

		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['categories'] = $this->site->getAllCategories();
		$this->data['stregths'] = $this->concretes_model->getStregths();
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['result_categories'] = $this->concretes_model->getAllCategoriesByInventoryInOut();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concretes')), array('link' => '#', 'page' => lang('product_sales_report')));
		$meta = array('page_title' => lang('product_sales_report'), 'bc' => $bc);
		$this->page_construct('concretes/product_sales_report', $meta, $this->data);
	}

	function product_customers_report()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['users'] = $this->site->getStaff();
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['stregths'] = $this->concretes_model->getStregths();
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('product_customers_report')));
		$meta = array('page_title' => lang('product_customers_report'), 'bc' => $bc);
		$this->page_construct('concretes/product_customers_report', $meta, $this->data);
	}

	function getProductCustomersReport($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('product_customers_report', TRUE);
		$user = $this->input->get('user') ? $this->input->get('user') : NULL;
		$customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$project = $this->input->get('project') ? $this->input->get('project') : NULL;
		$warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
		$start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
		$end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
		$product = $this->input->get('product') ? $this->input->get('product') : NULL;
		if ($start_date) {
			$start_date = $this->bpas->fld($start_date);
		}
		if ($end_date) {
			$end_date = $this->bpas->fld($end_date, false, 1);
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$user = $this->session->userdata('user_id');
		}
		$allow_category = $this->site->getCategoryByProject();
		if ($pdf || $xls) {
			$this->db->select("
							con_sales.customer,
							con_sales.location_name,
							con_sale_items.product_name,
							IFNULL(sum(" . $this->db->dbprefix('con_sale_items') . ".unit_quantity),0) as unit_quantity,
							" . $this->db->dbprefix('con_sale_items') . ".unit_price,
							IFNULL(sum(" . $this->db->dbprefix('con_sale_items') . ".unit_quantity) * " . $this->db->dbprefix('con_sale_items') . ".unit_price,0) as amount", FALSE)
				->from("con_sales")
				->join("con_sale_items", "con_sale_items.sale_id = con_sales.id", "inner")
				->group_by("con_sales.customer_id,con_sales.location_id,con_sale_items.product_id,con_sale_items.unit_price");
			$this->db->where("con_sales.location_id >", 1);
			if ($user) {
				$this->db->where('con_sales.created_by', $user);
			}
			if ($biller) {
				$this->db->where('con_sales.biller_id', $biller);
			}
			if ($project) {
				$this->db->where('con_sales.project_id', $project);
			}
			if ($customer) {
				$this->db->where('con_sales.customer_id', $customer);
			}
			if ($warehouse) {
				$this->db->where('con_sales.warehouse_id', $warehouse);
			}
			if ($product) {
				$this->db->where('con_sale_items.product_id', $product);
			}
			if ($start_date) {
				$this->db->where('con_sales.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_sales.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_sales.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->db->where_in('con_sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if ($allow_category) {
				$this->db->join('products', 'products.id = con_sale_items.product_id', 'inner');
				$this->db->where_in('products.category_id', $allow_category);
			}
			$q = $this->db->get();
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			} else {
				$data = NULL;
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('product_customers_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('location'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('stregth'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('quantity'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('unit_price'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));

				$row = 2;
				$total_qty = 0;
				$total_amount = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->location_name);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->product_name);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($data_row->unit_quantity));
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($data_row->unit_price));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($data_row->amount));

					$total_qty += $data_row->unit_quantity;
					$total_amount += $data_row->amount;
					$row++;
				}
				$this->excel->getActiveSheet()->getStyle("D" . $row . ":F" . $row)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
				$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($total_qty));
				$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($total_amount));

				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$filename = 'product_customers_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("
									con_sales.customer,
									con_sales.location_name,
									con_sale_items.product_name,
									IFNULL(sum(" . $this->db->dbprefix('con_sale_items') . ".unit_quantity),0) as unit_quantity,
									" . $this->db->dbprefix('con_sale_items') . ".unit_price,
									IFNULL(sum(" . $this->db->dbprefix('con_sale_items') . ".unit_quantity) * " . $this->db->dbprefix('con_sale_items') . ".unit_price,0) as amount", FALSE)
				->from("con_sales")
				->join("con_sale_items", "con_sale_items.sale_id = con_sales.id", "inner")
				->group_by("con_sales.customer_id,con_sales.location_id,con_sale_items.product_id,con_sale_items.unit_price");
			$this->datatables->where("con_sales.location_id >", 1);
			if ($user) {
				$this->datatables->where('con_sales.created_by', $user);
			}
			if ($biller) {
				$this->datatables->where('con_sales.biller_id', $biller);
			}
			if ($project) {
				$this->datatables->where('con_sales.project_id', $project);
			}
			if ($customer) {
				$this->datatables->where('con_sales.customer_id', $customer);
			}
			if ($product) {
				$this->datatables->where('con_sale_items.product_id', $product);
			}
			if ($warehouse) {
				$this->datatables->where('con_sales.warehouse_id', $warehouse);
			}
			if ($start_date) {
				$this->datatables->where('con_sales.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_sales.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_sales.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->datatables->where_in('con_sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if ($allow_category) {
				$this->datatables->join('products', 'products.id = con_sale_items.product_id', 'inner');
				$this->datatables->where_in('products.category_id', $allow_category);
			}
			echo $this->datatables->generate();
		}
	}

	public function truck_commissions()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['drivers'] =  $this->site->getAllCompanies('driver');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('truck_commissions')));
		$meta = array('page_title' => lang('truck_commissions'), 'bc' => $bc);
		$this->page_construct('concretes/truck_commissions', $meta, $this->data);
	}
	public function getTruckCommission($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('truck_commissions');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$driver = $this->input->get('driver') ? $this->input->get('driver') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
					DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".date, '%Y-%m-%d %T') as date,
					DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".from_date, '%Y-%m-%d') as from_date,
					DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".to_date, '%Y-%m-%d') as to_date,
					con_commissions.reference_no,
					CONCAT(" . $this->db->dbprefix('companies') . ".company,' - '," . $this->db->dbprefix('companies') . ".name) as driver,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".normal_qty,0) as normal_qty,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".overtime_qty,0) as overtime_qty,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".truck_commission_rate,0) as truck_commission_rate,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".truck_commission_rate_ot,0) as truck_commission_rate_ot,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".normal_amount,0) as normal_amount,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".ot_amount,0) as ot_amount,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".total_commission,0) as total_commission,
					con_commissions.id as id")
				->from("con_commission_items")
				->where("con_commissions.commission_type", "truck")
				->join("con_commissions", "con_commissions.id = con_commission_items.commission_id", "inner")
				->join("companies", "companies.id = con_commission_items.driver_id", "left");

			$this->db->where('companies.group_name', 'driver');


			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_commissions.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_commissions.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->db->where('con_commissions.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_commissions.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_commissions.biller_id', $biller);
			}
			if ($driver) {
				$this->db->where('con_commission_items.driver_id', $driver);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('truck_commissions'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('from_date'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('to_date'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('reference'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('normal'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('overtime'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('normal_rate'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('overtime_rate'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('normal_amount'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('overtime_amount'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('commission'));

				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrsd($data_row->from_date));
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->hrsd($data_row->to_date));
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->driver);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->normal_qty);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->overtime_qty);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->truck_commission_rate);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->truck_commission_rate_ot);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->normal_amount);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->ot_amount);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->total_commission);

					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);

				$filename = 'truck_commissions_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
					DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".date, '%Y-%m-%d %T') as date,
					DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".from_date, '%Y-%m-%d') as from_date,
					DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".to_date, '%Y-%m-%d') as to_date,
					con_commissions.reference_no,
					CONCAT(" . $this->db->dbprefix('companies') . ".company,' - '," . $this->db->dbprefix('companies') . ".name) as driver,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".normal_qty,0) as normal_qty,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".overtime_qty,0) as overtime_qty,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".truck_commission_rate,0) as truck_commission_rate,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".truck_commission_rate_ot,0) as truck_commission_rate_ot,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".normal_amount,0) as normal_amount,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".ot_amount,0) as ot_amount,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".total_commission,0) as total_commission,
					con_commissions.id as id")
				->from("con_commission_items")
				->where("con_commissions.commission_type", "truck")
				->join("con_commissions", "con_commissions.id = con_commission_items.commission_id", "inner")
				->join("companies", "companies.id = con_commission_items.driver_id", "left");

			$this->datatables->where('companies.group_name', 'driver');

			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_commissions.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_commissions.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->datatables->where('con_commissions.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_commissions.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_commissions.biller_id', $biller);
			}
			if ($driver) {
				$this->datatables->where('con_commission_items.driver_id', $driver);
			}
			echo $this->datatables->generate();
		}
	}

	public function pump_commissions()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['drivers'] =  $this->site->getAllCompanies('driver');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('pump_commissions')));
		$meta = array('page_title' => lang('pump_commissions'), 'bc' => $bc);
		$this->page_construct('concretes/pump_commissions', $meta, $this->data);
	}
	public function getPumpCommission($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('pump_commissions');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$driver = $this->input->get('driver') ? $this->input->get('driver') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
					DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".date, '%Y-%m-%d %T') as date,
					DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".from_date, '%Y-%m-%d') as from_date,
					DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".to_date, '%Y-%m-%d') as to_date,
					con_commissions.reference_no,
					CONCAT(" . $this->db->dbprefix('companies') . ".company,' - '," . $this->db->dbprefix('companies') . ".name) as driver,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".quantity, 0) as quantity,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".commission_rate, 0) as commission_rate,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".total_commission, 0) as total_commission,
					con_commissions.id as id")
				->from("con_commission_items")
				->where("con_commissions.commission_type", "pump")
				->join("con_commissions", "con_commissions.id = con_commission_items.commission_id", "inner")
				->join("companies", "companies.id = con_commission_items.driver_id", "left");

			$this->db->where('companies.group_name', 'driver');

			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_commissions.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_commissions.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->db->where('con_commissions.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_commissions.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_commissions.biller_id', $biller);
			}
			if ($driver) {
				$this->db->where('con_commission_items.driver_id', $driver);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('pump_commissions'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('from_date'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('to_date'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('reference'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('quantity'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('commission_rate'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('commission'));


				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrsd($data_row->from_date));
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->hrsd($data_row->to_date));
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->driver);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->quantity);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->commission_rate);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->total_commission);
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

				$filename = 'pump_commissions_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
					DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".date, '%Y-%m-%d %T') as date,
					DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".from_date, '%Y-%m-%d') as from_date,
					DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".to_date, '%Y-%m-%d') as to_date,
					con_commissions.reference_no,
					CONCAT(" . $this->db->dbprefix('companies') . ".company,' - '," . $this->db->dbprefix('companies') . ".name) as driver,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".quantity, 0) as quantity,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".commission_rate, 0) as commission_rate,
					IFNULL(" . $this->db->dbprefix('con_commission_items') . ".total_commission, 0) as total_commission,
					con_commissions.id as id")
				->from("con_commission_items")
				->where("con_commissions.commission_type", "pump")
				->join("con_commissions", "con_commissions.id = con_commission_items.commission_id", "inner")
				->join("companies", "companies.id = con_commission_items.driver_id", "left");

			$this->datatables->where('companies.group_name', 'driver');

			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_commissions.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_commissions.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->datatables->where('con_commissions.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_commissions.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_commissions.biller_id', $biller);
			}
			if ($driver) {
				$this->datatables->where('con_commission_items.driver_id', $driver);
			}
			echo $this->datatables->generate();
		}
	}

	public function officer_commissions()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['officers'] = $this->hr_model->getEmployees();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('officer_commissions')));
		$meta = array('page_title' => lang('officer_commissions'), 'bc' => $bc);
		$this->page_construct('concretes/officer_commissions', $meta, $this->data);
	}
	public function getOfficerCommission($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('officer_commissions');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$officer = $this->input->get('officer') ? $this->input->get('officer') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".date, '%Y-%m-%d %T') as date,
								DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".from_date, '%Y-%m-%d') as from_date,
								DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".to_date, '%Y-%m-%d') as to_date,
								con_commissions.reference_no,
								CONCAT(" . $this->db->dbprefix('hr_employees') . ".lastname,' - '," . $this->db->dbprefix('hr_employees') . ".lastname) as officer,
								IFNULL(" . $this->db->dbprefix('con_commission_items') . ".quantity, 0) as quantity,
								IFNULL(" . $this->db->dbprefix('con_commission_items') . ".commission_rate, 0) as commission_rate,
								IFNULL(" . $this->db->dbprefix('con_commission_items') . ".total_commission, 0) as total_commission,
								con_commissions.id as id")
				->from("con_commission_items")
				->where("con_commissions.commission_type", "officer")
				->join("con_commissions", "con_commissions.id = con_commission_items.commission_id", "inner")
				->join("hr_employees", "hr_employees.id = con_commission_items.officer_id", "left");

			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_commissions.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_commissions.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->db->where('con_commissions.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_commissions.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_commissions.biller_id', $biller);
			}
			if ($officer) {
				$this->db->where('con_commission_items.officer_id', $officer);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('officer_commissions'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('from_date'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('to_date'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('reference'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('quantity'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('commission_rate'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('commission'));


				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrsd($data_row->from_date));
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->hrsd($data_row->to_date));
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->officer);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->quantity);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->commission_rate);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->total_commission);
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

				$filename = 'officer_commissions_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
									DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".date, '%Y-%m-%d %T') as date,
									DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".from_date, '%Y-%m-%d') as from_date,
									DATE_FORMAT(" . $this->db->dbprefix('con_commissions') . ".to_date, '%Y-%m-%d') as to_date,
									con_commissions.reference_no,
									CONCAT(" . $this->db->dbprefix('hr_employees') . ".lastname,' - '," . $this->db->dbprefix('hr_employees') . ".lastname) as officer,
									IFNULL(" . $this->db->dbprefix('con_commission_items') . ".quantity, 0) as quantity,
									IFNULL(" . $this->db->dbprefix('con_commission_items') . ".commission_rate, 0) as commission_rate,
									IFNULL(" . $this->db->dbprefix('con_commission_items') . ".total_commission, 0) as total_commission,
									con_commissions.id as id")
				->from("con_commission_items")
				->where("con_commissions.commission_type", "officer")
				->join("con_commissions", "con_commissions.id = con_commission_items.commission_id", "inner")
				->join("hr_employees", "hr_employees.id = con_commission_items.officer_id", "left");

			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_commissions.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_commissions.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->datatables->where('con_commissions.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_commissions.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_commissions.biller_id', $biller);
			}
			if ($officer) {
				$this->datatables->where('con_commission_items.officer_id', $officer);
			}
			echo $this->datatables->generate();
		}
	}



	public function daily_stock_outs()
	{
		$this->bpas->checkPermissions();
		if (isset($_POST['form_action']) && $_POST['form_action'] == "export_excel") {
			$this->daily_material_out_actions(true);
		}
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['users'] = $this->site->getStaff();
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['stregths'] = $this->concretes_model->getStregths();
		$this->data['deliveries'] = $this->concretes_model->getDeliveryStockmoves($this->input->post());
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('daily_stock_outs')));
		$meta = array('page_title' => lang('daily_stock_outs'), 'bc' => $bc);
		$this->page_construct('concretes/daily_stock_outs', $meta, $this->data);
	}
	public function daily_material_out_actions($xls = false, $pdf = false)
	{
		if ($xls || $pdf) {
			$deliveries = $this->concretes_model->getDeliveryStockmoves($this->input->post());
			if (!empty($deliveries)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('daily_stock_outs'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('location'));

				$this->excel->getActiveSheet()->SetCellValue('E1', lang('dn_date'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('dn_reference'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('product_code'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('product_name'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('quantity'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('unit'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('unit_price'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('discount'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('subtotal'));

				$this->excel->getActiveSheet()->SetCellValue('N1', lang('total'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('order_discount'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('order_tax'));
				$this->excel->getActiveSheet()->SetCellValue('Q1', lang('grand_total'));
				$this->excel->getActiveSheet()->SetCellValue('R1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('S1', lang('balance'));

				$style = array('font'  => array('bold'  => true, 'size'  => 9, 'name'  => 'Arial'));
				$this->excel->getActiveSheet()->getStyle('A1:S1')->applyFromArray($style);
				$row = 2;
				$total = 0;
				$order_discount = 0;
				$order_tax = 0;
				$grand_total = 0;
				$paid = 0;
				$balance = 0;
				foreach ($data as $data_row) {
					$total += $data_row->total;
					$order_discount += $data_row->order_discount;
					$order_tax += $data_row->order_tax;
					$grand_total += $data_row->grand_total;
					$paid += $data_row->paid;
					$balance += ($data_row->grand_total - $data_row->paid);

					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->location_name);

					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $this->bpas->formatDecimal($data_row->total));
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, $this->bpas->formatDecimal($data_row->order_discount));
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, $this->bpas->formatDecimal($data_row->order_tax));
					$this->excel->getActiveSheet()->SetCellValue('Q' . $row, $this->bpas->formatDecimal($data_row->grand_total));
					$this->excel->getActiveSheet()->SetCellValue('R' . $row, $this->bpas->formatDecimal($data_row->paid));
					$this->excel->getActiveSheet()->SetCellValue('S' . $row, $this->bpas->formatDecimal($data_row->grand_total - $data_row->paid));

					$style = array('font'  => array('size'  => 9, 'name'  => 'Arial'));
					$this->excel->getActiveSheet()->getStyle('A' . $row . ':S' . $row)->applyFromArray($style);

					$con_sale_items = $this->db->select("con_sale_items.*,con_deliveries.reference_no, con_deliveries.date", FALSE)
						->from("con_sale_items")
						->where("sale_id", $data_row->id)
						->join("con_deliveries", "con_deliveries.id = con_sale_items.con_delivery_id", "LEFT")
						->group_by("con_sale_items.id")
						->get()
						->result();
					foreach ($con_sale_items as $i => $item) {
						$unit = $this->site->getUnitByID($item->product_unit_id);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->hrld($item->date));
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $item->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $item->product_code);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $item->product_name);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($item->quantity));
						$this->excel->getActiveSheet()->SetCellValue('J' . $row, $unit->name);
						$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($item->unit_price));
						$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($item->item_discount));
						$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->formatDecimal($item->subtotal));
						$this->excel->getActiveSheet()->getStyle('A' . $row . ':S' . $row)->applyFromArray($style);
						$row++;
					}
					$row++;
				}
				$this->excel->getActiveSheet()->getStyle("N" . $row . ":S" . $row)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
				$this->excel->getActiveSheet()->SetCellValue('N' . $row, $this->bpas->formatDecimal($total));
				$this->excel->getActiveSheet()->SetCellValue('O' . $row, $this->bpas->formatDecimal($order_discount));
				$this->excel->getActiveSheet()->SetCellValue('P' . $row, $this->bpas->formatDecimal($order_tax));
				$this->excel->getActiveSheet()->SetCellValue('Q' . $row, $this->bpas->formatDecimal($grand_total));
				$this->excel->getActiveSheet()->SetCellValue('R' . $row, $this->bpas->formatDecimal($paid));
				$this->excel->getActiveSheet()->SetCellValue('S' . $row, $this->bpas->formatDecimal($balance));

				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('S')->setWidth(20);

				$filename = 'daily_stock_outs_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}


	public function daily_stock_ins()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('daily_stock_ins')));
		$meta = array('page_title' => lang('daily_stock_ins'), 'bc' => $bc);
		$this->page_construct('concretes/daily_stock_ins', $meta, $this->data);
	}
	public function getDailyStockIns($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('daily_stock_ins');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
		$allow_category = $this->site->getCategoryByProject();
		if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(" . $this->db->dbprefix('stock_received') . ".date, '%Y-%m-%d %T') as date,
								stock_received.re_reference_no,
								stock_received.supplier,
								stock_received_items.product_name,
								stock_received.dn_reference,
								stock_received.truck,
								units.name as unit_name,
								sum(" . $this->db->dbprefix('stock_received_items') . ".unit_quantity) as unit_quantity,
								sum(IFNULL(" . $this->db->dbprefix('stock_received_items') . ".sup_qty," . $this->db->dbprefix('stock_received_items') . ".unit_quantity)) as sup_qty,
								sum(" . $this->db->dbprefix('stock_received_items') . ".unit_quantity) - sum(IFNULL(" . $this->db->dbprefix('stock_received_items') . ".sup_qty," . $this->db->dbprefix('stock_received_items') . ".unit_quantity)) as dif_qty,
								stock_received.id as id")
				->from("stock_received")
				->join("stock_received_items", "stock_received.id = stock_received_items.stock_received_id", "left")
				->join("units", "units.id = stock_received_items.product_unit_id", "left")
				->group_by("stock_received.id, stock_received_items.product_id, stock_received_items.product_unit_id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('stock_received.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('stock_received.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->db->where_in('stock_received.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if ($start_date) {
				$this->db->where('stock_received.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('stock_received.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('stock_received.biller_id', $biller);
			}
			if ($warehouse) {
				$this->db->where('stock_received.warehouse_id >=', $warehouse);
			}
			if ($allow_category) {
				$this->db->join("products", "products.id = stock_received_items.product_id", "inner");
				$this->db->where_in("products.category_id", $allow_category);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('daily_stock_ins'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('supplier'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('product'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('dn_reference'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('truck'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('unit'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('quantity'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('sup_qty'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('dif_qty'));
				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->re_reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->supplier);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->product_name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->dn_reference);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->truck);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->unit_name);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($data_row->unit_quantity));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->sup_qty));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->dif_qty));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$filename = 'daily_stock_ins_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
					DATE_FORMAT(" . $this->db->dbprefix('stock_received') . ".date, '%Y-%m-%d %T') as date,
					stock_received.re_reference_no,
					stock_received.supplier,
					stock_received_items.product_name,
					stock_received.dn_reference,
					stock_received.truck,
					units.name as unit_name,
					sum(" . $this->db->dbprefix('stock_received_items') . ".unit_quantity) as unit_quantity,
					sum(IFNULL(" . $this->db->dbprefix('stock_received_items') . ".sup_qty," . $this->db->dbprefix('stock_received_items') . ".unit_quantity)) as sup_qty,
					sum(" . $this->db->dbprefix('stock_received_items') . ".unit_quantity) - sum(IFNULL(" . $this->db->dbprefix('stock_received_items') . ".sup_qty," . $this->db->dbprefix('stock_received_items') . ".unit_quantity)) as dif_qty,
					stock_received.id as id")
				->from("stock_received")
				->join("stock_received_items", "stock_received.id = stock_received_items.stock_received_id", "left")
				->join("units", "units.id = stock_received_items.product_unit_id", "left")
				->group_by("stock_received.id,stock_received_items.product_id,stock_received_items.product_unit_id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('stock_received.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('stock_received.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->datatables->where_in('receives.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if ($start_date) {
				$this->datatables->where('stock_received.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('stock_received.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('stock_received.biller_id', $biller);
			}
			if ($warehouse) {
				$this->datatables->where('stock_received.warehouse_id >=', $warehouse);
			}
			if ($allow_category) {
				$this->datatables->join("products", "products.id = stock_received_items.product_id", "inner");
				$this->datatables->where_in("products.category_id", $allow_category);
			}
			echo $this->datatables->generate();
		}
	}

	public function inventory_in_outs()
	{
		$this->bpas->checkPermissions();
		$category = $this->input->post("category");
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['categories'] = $this->site->getAllCategories();
		$this->data['brands'] = $this->site->getBrands();
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['products'] = $this->concretes_model->getProducts();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concretes')), array('link' => '#', 'page' => lang('inventory_in_outs')));
		$meta = array('page_title' => lang('inventory_in_outs'), 'bc' => $bc);
		$this->page_construct('concretes/inventory_in_outs', $meta, $this->data);
	}

	public function getInventoryInOuts($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('inventory_in_outs', TRUE);
		$product = $this->input->get('product') ? $this->input->get('product') : NULL;
		$category = $this->input->get('category') ? $this->input->get('category') : NULL;
		$warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
		$start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
		$end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
		if ($start_date) {
			$start_date = $this->bpas->fsd($start_date);
		} else {
			$start_date = date('Y-m-d');
		}
		if ($end_date) {
			$end_date = $this->bpas->fsd($end_date);
		} else {
			$end_date = date('Y-m-d');
		}
		$allow_category = $this->site->getCategoryByProject();
		$where = "";
		$where_begin = "";
		$where_balance = "";
		if ($warehouse) {
			$where .= " AND warehouse_id='" . $warehouse . "'";
			$where_begin .= " AND warehouse_id='" . $warehouse . "'";
			$where_balance .= " AND warehouse_id='" . $warehouse . "'";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[', '(', $this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']', ')', $warehouse_ids);
			$where .= " AND warehouse_id IN " . $warehouse_ids;
			$where_begin .= " AND warehouse_id IN " . $warehouse_ids;
			$where_balance .= " AND warehouse_id IN " . $warehouse_ids;
		}
		if ($start_date) {
			$where .= " AND DATE(date) >= '" . $start_date . "'";
			$where_begin .= " AND DATE(date) < '" . $start_date . "'";
		}
		if ($end_date) {
			$where .= " AND DATE(date) <= '" . $end_date . "'";
			$where_balance .= " AND DATE(date) <= '" . $end_date . "'";
		}

		$transactions = array('Purchases', 'CDelivery', 'CAdjustment', 'CFuel', 'CError');
		$select_begin = " , (convert_qty(" . $this->db->dbprefix('products') . ".id,IFNULL(( SELECT sum( IFNULL(quantity,0) ) AS quantity FROM " . $this->db->dbprefix('stock_movement') . " WHERE product_id = " . $this->db->dbprefix('products') . ".id " . $where_begin . " ),0))) AS begin_qty";
		$select_balance = " , (convert_qty(" . $this->db->dbprefix('products') . ".id,IFNULL(( SELECT sum( IFNULL(quantity,0) ) AS quantity FROM " . $this->db->dbprefix('stock_movement') . " WHERE product_id = " . $this->db->dbprefix('products') . ".id " . $where_balance . " ),0))) AS balance_qty";
		$select_in = '';
		$select_out = '';

		$select_in .= " , convert_qty(" . $this->db->dbprefix('products') . ".id,IFNULL(( SELECT sum( IFNULL(quantity,0) ) AS quantity FROM " . $this->db->dbprefix('stock_movement') . " WHERE (`transaction` = 'Purchases' || `transaction` = 'Receives') AND product_id = " . $this->db->dbprefix('products') . ".id " . $where . "),0)) AS purchase_in";
		$select_in .= " , convert_qty(" . $this->db->dbprefix('products') . ".id,IFNULL(( SELECT sum( IFNULL(quantity,0) ) AS quantity FROM " . $this->db->dbprefix('stock_movement') . " WHERE `transaction` != 'Purchases' && `transaction` != 'Receives'  && `transaction` != 'CFuel' && `transaction` != 'CError' AND quantity > 0 AND product_id = " . $this->db->dbprefix('products') . ".id " . $where . "),0)) AS adjustment_in";

		$select_out .= " , convert_qty(" . $this->db->dbprefix('products') . ".id,IFNULL(( SELECT sum( IFNULL(quantity,0) * (-1)) AS quantity FROM " . $this->db->dbprefix('stock_movement') . " WHERE `transaction` = 'CDelivery' AND product_id = " . $this->db->dbprefix('products') . ".id " . $where . "),0)) AS delivery_out";
		$select_out .= " , convert_qty(" . $this->db->dbprefix('products') . ".id,IFNULL(( SELECT sum( IFNULL(quantity,0) * (-1)) AS quantity FROM " . $this->db->dbprefix('stock_movement') . " WHERE `transaction` != 'Purchases' && `transaction` != 'Receives'  && `transaction` != 'CFuel' && `transaction` != 'CError' AND quantity < 0 AND product_id = " . $this->db->dbprefix('products') . ".id " . $where . "),0)) AS adjustment_out";
		$select_out .= " , convert_qty(" . $this->db->dbprefix('products') . ".id,IFNULL(( SELECT sum( IFNULL(quantity,0) * (-1)) AS quantity FROM " . $this->db->dbprefix('stock_movement') . " WHERE `transaction` = 'CFuel' AND product_id = " . $this->db->dbprefix('products') . ".id " . $where . "),0)) AS fuel_out";
		$select_out .= " , convert_qty(" . $this->db->dbprefix('products') . ".id,IFNULL(( SELECT sum( IFNULL(quantity,0) * (-1)) AS quantity FROM " . $this->db->dbprefix('stock_movement') . " WHERE `transaction` = 'CError' AND product_id = " . $this->db->dbprefix('products') . ".id " . $where . "),0)) AS error_out";


		if ($pdf || $xls) {
			$this->db->select("categories.name as category,products.code,products.name " . $select_begin . $select_in . $select_out . $select_balance)
				->from("products")
				->join("categories", "categories.id = products.category_id", "inner");
			$this->db->where("products.type NOT IN ('combo','bom','service')");
			$this->db->join("(SELECT product_id, sum(IFNULL(quantity,0)) AS quantity FROM " . $this->db->dbprefix('stock_movement') . " WHERE 1=1 " . $where_begin . " GROUP BY product_id) as stock_begin", "stock_begin.product_id = products.id", "left");
			$this->db->join("(SELECT product_id, sum(IFNULL(quantity,0)) AS quantity FROM " . $this->db->dbprefix('stock_movement') . " WHERE 1=1 " . $where_balance . " GROUP BY product_id) as stock_ending", "stock_ending.product_id = products.id", "left");
			$this->db->join("(SELECT product_id, count(id) AS quantity FROM " . $this->db->dbprefix('stock_movement') . " WHERE transaction != 'CostAdjustment' " . $where . " GROUP BY product_id) as stock_processing", "stock_processing.product_id = products.id", "left");
			$this->db->where("(IFNULL(stock_processing.quantity,0) <> 0 OR IFNULL(stock_begin.quantity,0) <> 0 OR IFNULL(stock_ending.quantity,0) <> 0)");
			if ($product) {
				$this->db->where('products.id', $product);
			}
			if ($category) {
				$this->db->where('categories.id', $category);
			}
			if ($allow_category) {
				$this->db->where_in("categories.id", $allow_category);
			}
			$q = $this->db->get();
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			} else {
				$data = NULL;
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle('inventory_in_outs');

				$this->excel->getActiveSheet()->mergeCells('A1:A2');
				$this->excel->getActiveSheet()->mergeCells('B1:B2');
				$this->excel->getActiveSheet()->mergeCells('C1:C2');
				$this->excel->getActiveSheet()->mergeCells('D1:D2');
				$this->excel->getActiveSheet()->mergeCells('E1:F1');
				$this->excel->getActiveSheet()->mergeCells('G1:J1');
				$this->excel->getActiveSheet()->mergeCells('K1:K2');

				$this->excel->getActiveSheet()->SetCellValue('A1', lang('category'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('begin'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('in'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('out'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('balance'));

				$this->excel->getActiveSheet()->SetCellValue('E2', lang('receive'));
				$this->excel->getActiveSheet()->SetCellValue('F2', lang('adjustment'));
				$this->excel->getActiveSheet()->SetCellValue('G2', lang('delivery'));
				$this->excel->getActiveSheet()->SetCellValue('H2', lang('adjustment'));
				$this->excel->getActiveSheet()->SetCellValue('I2', lang('fuel'));
				$this->excel->getActiveSheet()->SetCellValue('J2', lang('error'));

				$this->excel->getActiveSheet()->getStyle('E1:F1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('G1:J1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

				$row = 3;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->category);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->code);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->remove_tag($data_row->begin_qty));
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->remove_tag($data_row->purchase_in));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->remove_tag($data_row->adjustment_in));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->remove_tag($data_row->delivery_out));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->remove_tag($data_row->adjustment_out));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->remove_tag($data_row->fuel_out));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->remove_tag($data_row->error_out));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->remove_tag($data_row->balance_qty));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
				$filename = 'inventory_in_outs_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("products.id, categories.name as category,products.code,products.name " . $select_begin . $select_in . $select_out . $select_balance)
				->from("products")
				->join("categories", "categories.id = products.category_id", "inner");
			$this->datatables->where("products.type NOT IN ('combo','bom','service')");
			$this->datatables->join("(SELECT product_id, sum(IFNULL(quantity,0)) AS quantity FROM " . $this->db->dbprefix('stock_movement') . " WHERE 1=1 " . $where_begin . " GROUP BY product_id) as stock_begin", "stock_begin.product_id = products.id", "left");
			$this->datatables->join("(SELECT product_id, sum(IFNULL(quantity,0)) AS quantity FROM " . $this->db->dbprefix('stock_movement') . " WHERE 1=1 " . $where_balance . " GROUP BY product_id) as stock_ending", "stock_ending.product_id = products.id", "left");
			$this->datatables->join("(SELECT product_id, count(id) AS quantity FROM " . $this->db->dbprefix('stock_movement') . " WHERE transaction != 'CostAdjustment' " . $where . " GROUP BY product_id) as stock_processing", "stock_processing.product_id = products.id", "left");
			$this->datatables->where("(IFNULL(stock_processing.quantity,0) <> 0 OR IFNULL(stock_begin.quantity,0) <> 0 OR IFNULL(stock_ending.quantity,0) <> 0)");
			if ($product) {
				$this->datatables->where('products.id', $product);
			}
			if ($category) {
				$this->datatables->where('categories.id', $category);
			}
			if ($allow_category) {
				$this->datatables->where_in("categories.id", $allow_category);
			}
			echo $this->datatables->generate();
		}
	}


	public function adjustments($warehouse_id = null, $biller_id = NULL)
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('adjustments')));
		$meta = array('page_title' => lang('adjustments'), 'bc' => $bc);
		$this->page_construct('concretes/adjustments', $meta, $this->data);
	}
	public function getAdjustments($warehouse_id = null, $biller_id = NULL)
	{
		$this->bpas->checkPermissions('adjustments');
		$approve_link = '';
		$edit_link = anchor('admin/concretes/edit_adjustment/$1', '<i class="fa fa-edit"></i> ' . lang('edit_adjustment'), ' class="edit_adjustment" ');
		$delete_link = "<a href='#' class='po delete_adjustment' title='<b>" . $this->lang->line("delete_adjustment") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('concretes/delete_adjustment/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_adjustment') . "</a>";

		if ($this->Admin || $this->Owner || $this->GP['concretes-approve_adjustment']) {
			$approve_link = "<a href='#' class='po approve_adjustment' title='<b>" . $this->lang->line("approve_adjustment") . "</b>' data-content=\"<p>"
				. lang('r_u_sure') . "</p><a class='btn btn-success' href='" . admin_url('concretes/approve_adjustment/$1') . "'>"
				. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
				. lang('approve_adjustment') . "</a>";
		}

		$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $approve_link . '</li>
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
		$this->load->library('datatables');
		$this->datatables
			->select("id, date, reference, status")
			->from("con_adjustments");
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('con_adjustments.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('con_adjustments.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('con_adjustments.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if ($warehouse_id) {
			$this->datatables->where('con_adjustments.warehouse_id', $warehouse_id);
		}
		if ($biller_id) {
			$this->datatables->where('con_adjustments.biller_id', $biller_id);
		}
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
	}

	public function adjustment_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_fuel', true);
					foreach ($_POST['val'] as $id) {
						$this->concretes_model->deleteAdjustment($id);
					}
					$this->session->set_flashdata('message', $this->lang->line("adjustments_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				} elseif ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('adjustments'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('status'));
					$row = 2;
					foreach ($_POST['val'] as $id) {
						$adjustment = $this->concretes_model->getAdjustmentByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($adjustment->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $adjustment->reference);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, lang($adjustment->status));
						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'adjustments_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function add_adjustment()
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id    = $this->input->post('biller');
			$project_id   = $this->input->post('project');
			$reference    = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa', $biller_id);
			$date 		  = $this->bpas->fld(trim($this->input->post('date')));
			$from_date    = $this->bpas->fld(trim($this->input->post('from_date')));
			$to_date      = $this->bpas->fld(trim($this->input->post('to_date')));
			$warehouse_id = $this->input->post('warehouse');
			$note 		  = $this->bpas->clear_tags($this->input->post('note'));
			$i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$product_id    = $_POST['product_id'][$r];
				$product_code  = $_POST['product_code'][$r];
				$product_name  = $_POST['product_name'][$r];
				$system_qty    = $_POST['system_qty'][$r];
				$machine_qty   = $_POST['machine_qty'][$r];
				$different_qty = $system_qty - $machine_qty;
				$items[] = array(
					'product_id'    => $product_id,
					'product_code'  => $product_code,
					'product_name'  => $product_name,
					'system_qty'    => $system_qty,
					'machine_qty'   => $machine_qty,
					'different_qty' => $different_qty
				);
			}
			if (empty($items)) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date'         => $date,
				'from_date'    => $from_date,
				'to_date'      => $to_date,
				'reference'    => $reference,
				'biller_id'    => $biller_id,
				'project_id'   => $project_id,
				'warehouse_id' => $warehouse_id,
				'status'       => "pending",
				'note'         => $note,
				'created_by'   => $this->session->userdata('user_id'),
				'created_at'   => date('Y-m-d H:i:s'),
			);
			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$attachment = $this->upload->file_name;
				$data['attachment'] = $attachment;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->addAdjustment($data, $items)) {
			$this->session->set_userdata('remove_conadjls', 1);
			$this->session->set_flashdata('message', $this->lang->line("adjustment_added") . " " . $reference);
			if ($this->input->post('add_adjustment_next')) {
				admin_redirect('concretes/add_adjustment');
			} else {
				admin_redirect('concretes/adjustments');
			}
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers']    =  $this->site->getBillers();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$bc   = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/adjustments'), 'page' => lang('adjustments')), array('link' => '#', 'page' => lang('add_adjustment')));
			$meta = array('page_title' => lang('add_adjustment'), 'bc' => $bc);
			$this->page_construct('concretes/add_adjustment', $meta, $this->data);
		}
	}

	public function edit_adjustment($id = false)
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa', $biller_id);
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$from_date = $this->bpas->fld(trim($this->input->post('from_date')));
			$to_date = $this->bpas->fld(trim($this->input->post('to_date')));
			$warehouse_id = $this->input->post('warehouse');
			$note = $this->bpas->clear_tags($this->input->post('note'));
			$i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$product_id = $_POST['product_id'][$r];
				$product_code = $_POST['product_code'][$r];
				$product_name = $_POST['product_name'][$r];
				$system_qty = $_POST['system_qty'][$r];
				$machine_qty = $_POST['machine_qty'][$r];
				$different_qty = $system_qty - $machine_qty;

				$items[] = array(
					'adjustment_id' => $id,
					'product_id' => $product_id,
					'product_code' => $product_code,
					'product_name' => $product_name,
					'system_qty' => $system_qty,
					'machine_qty' => $machine_qty,
					'different_qty' => $different_qty
				);
			}
			if (empty($items)) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
				'from_date' => $from_date,
				'to_date' => $to_date,
				'reference' => $reference,
				'biller_id' => $biller_id,
				'project_id' => $project_id,
				'warehouse_id' => $warehouse_id,
				'status' => "pending",
				'note' => $note,
				'updated_by' => $this->session->userdata('user_id'),
			);

			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$attachment = $this->upload->file_name;
				$data['attachment'] = $attachment;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->updateAdjustment($id, $data, $items)) {
			$this->session->set_userdata('remove_conadjls', 1);
			$this->session->set_flashdata('message', $this->lang->line("adjustment_edited") . " " . $reference);
			admin_redirect('concretes/adjustments');
		} else {
			$adjustment_items = $this->concretes_model->getAdjustmentItems($id);
			krsort($adjustment_items);
			$c = rand(100000, 9999999);
			foreach ($adjustment_items as $item) {
				$row = $this->site->getProductByID($item->product_id);
				$row->system_qty = $item->system_qty;
				$row->machine_qty = $item->machine_qty;
				$ri = $this->Settings->item_addition ? $row->id : $c;
				$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row);
				$c++;
			}
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] =  $this->site->getBillers();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['adjustment'] = $this->concretes_model->getAdjustmentByID($id);
			$this->data['adjustment_items'] = json_encode($pr);
			$this->session->set_userdata('remove_conadjls', 1);
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/adjustments'), 'page' => lang('adjustments')), array('link' => '#', 'page' => lang('edit_adjustment')));
			$meta = array('page_title' => lang('edit_adjustment'), 'bc' => $bc);
			$this->page_construct('concretes/edit_adjustment', $meta, $this->data);
		}
	}


	function import_adjustment()
	{
		$this->bpas->checkPermissions("add_adjustment");
		$this->load->helper('security');
		$this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
		if ($this->form_validation->run() == true) {
			if (isset($_FILES["userfile"])) {
				$biller_id = $this->input->post("biller");
				$reference = $this->site->getReference('qa', $biller_id);
				$warehouse_id = $this->input->post("warehouse");
				$project_id = $this->input->post("project");
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach ($object->getWorksheetIterator() as $worksheet) {
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for ($row = 2; $row <= $highestRow; $row++) {
						$date = trim($worksheet->getCellByColumnAndRow(0, $row)->getValue());
						$quantity = trim($worksheet->getCellByColumnAndRow(5, $row)->getValue());
						$g_stone = trim($worksheet->getCellByColumnAndRow(6, $row)->getValue());
						$h_stone = trim($worksheet->getCellByColumnAndRow(7, $row)->getValue());
						$i_sand = trim($worksheet->getCellByColumnAndRow(8, $row)->getValue());
						$j_sand = trim($worksheet->getCellByColumnAndRow(9, $row)->getValue());
						$k_cement = trim($worksheet->getCellByColumnAndRow(10, $row)->getValue());
						$l_cement = trim($worksheet->getCellByColumnAndRow(11, $row)->getValue());
						$m_cement = trim($worksheet->getCellByColumnAndRow(12, $row)->getValue());
						$n_flyash = trim($worksheet->getCellByColumnAndRow(13, $row)->getValue());
						$p_water = trim($worksheet->getCellByColumnAndRow(15, $row)->getValue());
						$q_additive = trim($worksheet->getCellByColumnAndRow(16, $row)->getValue());
						$r_additive = trim($worksheet->getCellByColumnAndRow(17, $row)->getValue());
						if (strpos($date, '/') == false && $date != "") {
							$date = PHPExcel_Shared_Date::ExcelToPHP($date);
							$date = date('d/m/Y', $date);
						}
						$results[] = array(
							'date' => $date,
							'quantity' => $quantity,
							'g_stone' => $g_stone,
							'h_stone' => $h_stone,
							'i_sand' => $i_sand,
							'j_sand' => $j_sand,
							'k_cement' => $k_cement,
							'l_cement' => $l_cement,
							'm_cement' => $m_cement,
							'n_flyash' => $n_flyash,
							'p_water' => $p_water,
							'q_additive' => $q_additive,
							'r_additive' => $r_additive,
						);
					}
				}
				$data = false;
				if ($results) {
					foreach ($results as $result) {
						if ($result["quantity"] > 0) {
							$date = $this->bpas->fsd($result["date"]);
							$quantity = $result["quantity"];
						} else {
							$result["date"] = $date;
							if (isset($data[$date]) && $data[$date]) {
								$quantity = $data[$date]["quantity"] + $quantity;
								$g_stone = $data[$date]["g_stone"] + $result["g_stone"];
								$h_stone = $data[$date]["h_stone"] + $result["h_stone"];
								$i_sand = $data[$date]["i_sand"] + $result["i_sand"];
								$j_sand = $data[$date]["j_sand"] + $result["j_sand"];
								$k_cement = $data[$date]["k_cement"] + $result["k_cement"];
								$l_cement = $data[$date]["l_cement"] + $result["l_cement"];
								$m_cement = $data[$date]["m_cement"] + $result["m_cement"];
								$n_flyash = $data[$date]["n_flyash"] + $result["n_flyash"];
								$p_water = $data[$date]["p_water"] + $result["p_water"];
								$q_additive = $data[$date]["q_additive"] + $result["q_additive"];
								$r_additive = $data[$date]["r_additive"] + $result["r_additive"];
								$data[$date] = array(
									'date' => $date,
									'quantity' => $quantity,
									'g_stone' => $g_stone,
									'h_stone' => $h_stone,
									'i_sand' => $i_sand,
									'j_sand' => $j_sand,
									'k_cement' => $k_cement,
									'l_cement' => $l_cement,
									'm_cement' => $m_cement,
									'n_flyash' => $n_flyash,
									'p_water' => $p_water,
									'q_additive' => $q_additive,
									'r_additive' => $r_additive,
								);
							} else {
								$result["quantity"] = $quantity;
								$data[$date] = $result;
							}
						}
					}
				}
				$items = false;
				if ($data) {
					$from_date = "";
					$to_date = "";
					$types = array("g_stone", "h_stone", "i_sand", "j_sand", "k_cement", "l_cement", "m_cement", "n_flyash", "p_water", "q_additive", "r_additive");
					foreach ($data as $date => $values) {
						$check_adjustment = $this->concretes_model->getAdjustmentByDate($values["date"], $warehouse_id);
						if ($check_adjustment) {
							$this->session->set_flashdata('error', lang("adjustment_existed") . ' ' . $check_adjustment->reference);
							$this->bpas->md();
						}
						foreach ($types as $type) {
							if ($from_date == "" || $from_date > $values["date"]) {
								$from_date = $values["date"];
							}
							if ($to_date == "" || $to_date < $values["date"]) {
								$to_date = $values["date"];
							}
							$system = $this->concretes_model->getDailyStockmove($values["date"], $type, $warehouse_id);
							if ($system && $system->product_id > 0) {
								$items[] = array(
									"date" => $values["date"],
									"product_id" => $system->product_id,
									"product_code" => $system->code,
									"product_name" => $system->name,
									"system_qty" => $system->quantity,
									"machine_qty" => $values[$type],
									"different_qty" => $system->quantity - $values[$type],
								);
							} else {
								$product = $this->concretes_model->getProductByCF6($type);
								if ($product) {
									$items[] = array(
										"date" => $values["date"],
										"product_id" => $product->id,
										"product_code" => $product->code,
										"product_name" => $product->name,
										"system_qty" => 0,
										"machine_qty" => $values[$type],
										"different_qty" => -$values[$type],
									);
								}
							}
						}
					}
					$adjustment = array(
						"date" => date('Y-m-d H:i:s'),
						"from_date" => $from_date,
						"to_date" => $to_date,
						"reference" => $reference,
						"biller_id" => $biller_id,
						"project_id" => $project_id,
						"warehouse_id" => $warehouse_id,
						"created_by" => $this->session->userdata('user_id'),
						"created_at" => date('Y-m-d H:i:s'),
					);
				}
			}
			if (empty($adjustment) || empty($items)) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->addAdjustment($adjustment, $items)) {
			$this->session->set_flashdata('message', lang("adjustment_imported"));
			redirect(admin_url('concretes/adjustments'));
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/adjustments'), 'page' => lang('adjustments')), array('link' => '#', 'page' => lang('import_adjustment')));
			$meta = array('page_title' => lang('import_adjustment'), 'bc' => $bc);
			$this->page_construct('concretes/import_adjustment', $meta, $this->data);
		}
	}

	public function approve_adjustment($id = null)
	{
		$this->bpas->checkPermissions('approve_adjustment', true);
		$adjustment = $this->concretes_model->getAdjustmentByID($id);
		$adjustment_items = $this->concretes_model->getAdjustmentItems($id);
		if ($adjustment && $adjustment_items) {
			$date 		  = $adjustment->date;
			$warehouse_id = $adjustment->warehouse_id;
			$reference    = $adjustment->reference;
			$biller_id    = $adjustment->biller_id;
			$project_id   = $adjustment->project_id;
			foreach ($adjustment_items as $adjustment_item) {
				$product_details = $this->site->getProductByID($adjustment_item->product_id);
				$unit = $this->site->getProductUnit($adjustment_item->product_id, $product_details->unit);
				if ($adjustment_item->different_qty != 0) {
					$stockmoves[] = array(
						'transaction_id' => $id,
						'transaction'    => 'CAdjustment',
						'product_id'     => $product_details->id,
						'product_type'   => $product_details->type,
						'product_code'   => $product_details->code,
						'product_name'   => $product_details->name,
						'quantity' 		 => $adjustment_item->different_qty,
						'unit_quantity'  => $unit->unit_qty,
						'unit_code'      => $unit->code,
						'unit_id'        => $product_details->unit,
						'warehouse_id'   => $warehouse_id,
						'date'           => $date,
						'real_unit_cost' => $product_details->cost,
						'reference_no'   => $reference,
						'user_id' 		 => $adjustment->created_by
					);
					if ($this->Settings->accounting == 1) {
						$inventory_acc = $this->accounting_setting->default_stock;
                    	$costing_acc   = $this->accounting_setting->default_cost;
						$productAcc = $this->site->getProductAccByProductId($product_details->id);
						$accTrans[] = array(
							'tran_no'      => $id,
							'tran_type'    => 'CAdjustment',
							'tran_date'    => $date,
							'reference_no' => $reference,
							'account_code' => $inventory_acc,
							'amount' 	   => ($product_details->cost * $adjustment_item->different_qty),
							'narrative'    => 'Product Code: ' . $product_details->code . '#' . 'Qty: ' . $adjustment_item->different_qty . '#' . 'Cost: ' . $product_details->cost,
							'biller_id'    => $biller_id,
							'project_id'   => $project_id,
							'created_by'   => $this->session->userdata('user_id'),
						);
						$accTrans[] = array(
							'tran_no'      => $id,
							'tran_type'    => 'CAdjustment',
							'tran_date'    => $date,
							'reference_no' => $reference,
							'account_code' => $costing_acc,
							'amount' 	   => ($product_details->cost * $adjustment_item->different_qty) * (-1),
							'narrative'    => 'Product Code: ' . $product_details->code . '#' . 'Qty: ' . $adjustment_item->different_qty . '#' . 'Cost: ' . $product_details->cost,
							'biller_id'    => $biller_id,
							'project_id'   => $project_id,
							'created_by'   => $this->session->userdata('user_id'),
						);
					}
				}
			}
			$data = array(
				'status' => 'approved',
				'approved_by' => $this->session->userdata('user_id'),
				'approved_at' => date('Y-m-d H:i:s')
			);
			if ($this->concretes_model->approveAdjustment($id, $data, $stockmoves, $accTrans)) {
				if ($this->input->is_ajax_request()) {
					echo lang("adjustment_approved");
					die();
				} else {
					$this->session->set_flashdata('message', lang("adjustment_approved"));
					die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : admin_url('welcome')) . "'; }, 10);</script>");
				}
			}
		} else {
			$this->session->set_flashdata('error', lang("adjustment_cannot_approved"));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : admin_url('welcome')) . "'; }, 10);</script>");
		}
	}


	public function delete_adjustment($id = null)
	{
		$this->bpas->checkPermissions(NULL, true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->concretes_model->deleteAdjustment($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("adjustment_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('adjustment_deleted'));
			admin_redirect('concretes/adjustments');
		}
	}
	public function modal_view_adjustment($id = null)
	{
		$this->bpas->checkPermissions('adjustments', true);
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$adjustment = $this->concretes_model->getAdjustmentByID($id);
		$this->data['biller'] = $this->site->getCompanyByID($adjustment->biller_id);
		$this->data['adjustment'] = $adjustment;
		$this->data['adjustment_items'] = $this->concretes_model->getAdjustmentItems($id);;
		$this->data['created_by'] = $this->site->getUserByID($adjustment->created_by);
		$this->load->view($this->theme . 'concretes/modal_view_adjustment', $this->data);
	}

	public function adjustments_report()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['products'] = $this->concretes_model->getProducts();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('adjustments_report')));
		$meta = array('page_title' => lang('adjustments_report'), 'bc' => $bc);
		$this->page_construct('concretes/adjustments_report', $meta, $this->data);
	}
	public function getAdjustmentsReport($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('adjustments_report');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$product = $this->input->get('product') ? $this->input->get('product') : NULL;
		$warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
								con_adjustments.reference,
								DATE_FORMAT(" . $this->db->dbprefix('con_adjustment_items') . ".date, '%Y-%m-%d %T') as date,
								con_adjustment_items.product_name,
								con_adjustment_items.system_qty,
								con_adjustment_items.machine_qty,
								con_adjustment_items.different_qty,
								con_adjustments.id as id")
				->from("con_adjustments")
				->where("con_adjustment_items.different_qty !=", 0)
				->join("con_adjustment_items", "con_adjustments.id = con_adjustment_items.adjustment_id", "inner")
				->group_by("con_adjustments.id,con_adjustment_items.product_id,con_adjustment_items.date")
				->order_by("con_adjustment_items.date", "desc");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_adjustments.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_adjustments.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->db->where_in('con_adjustments.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if ($start_date) {
				$this->db->where('con_adjustment_items.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_adjustment_items.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_adjustments.biller_id', $biller);
			}
			if ($warehouse) {
				$this->db->where('con_adjustments.warehouse_id >=', $warehouse);
			}
			if ($product) {
				$this->db->where('con_adjustment_items.product_id', $product);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('adjustments_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('reference'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('product'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('system_qty'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('machine_qty'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('different_qty'));

				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->reference);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->product_name);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($data_row->system_qty));
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($data_row->machine_qty));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($data_row->different_qty));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

				$filename = 'adjustments_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
									con_adjustments.reference,
									DATE_FORMAT(" . $this->db->dbprefix('con_adjustment_items') . ".date, '%Y-%m-%d %T') as date,
									con_adjustment_items.product_name,
									con_adjustment_items.system_qty,
									con_adjustment_items.machine_qty,
									con_adjustment_items.different_qty,
									con_adjustments.id as id")
				->from("con_adjustments")
				->where("con_adjustment_items.different_qty !=", 0)
				->join("con_adjustment_items", "con_adjustments.id = con_adjustment_items.adjustment_id", "inner")
				->group_by("con_adjustments.id,con_adjustment_items.product_id,con_adjustment_items.date");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_adjustments.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_adjustments.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->datatables->where_in('con_adjustments.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if ($start_date) {
				$this->datatables->where('con_adjustment_items.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_adjustment_items.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_adjustments.biller_id', $biller);
			}
			if ($warehouse) {
				$this->datatables->where('con_adjustments.warehouse_id >=', $warehouse);
			}
			if ($product) {
				$this->datatables->where('con_adjustment_items.product_id', $product);
			}

			echo $this->datatables->generate();
		}
	}

	public function stregth_suggestions()
	{
		$term = $this->input->get('term', true);
		if (strlen($term) < 1 || !$term) {
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
		}
		$analyzed = $this->bpas->analyze_term($term);
		$sr = $analyzed['term'];
		$rows = $this->concretes_model->getStregthNames($sr);
		if ($rows) {
			$c = str_replace(".", "", microtime(true));
			$r = 0;
			foreach ($rows as $row) {
				$row->quantity = 1;
				$pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row);
				$r++;
			}
			$this->bpas->send_json($pr);
		} else {
			$this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
		}
	}

	public function adjustment_suggestions()
	{
		$term = $this->input->get('term', true);
		if (strlen($term) < 1 || !$term) {
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
		}
		$analyzed = $this->bpas->analyze_term($term);
		$sr = $analyzed['term'];
		$warehouse_id = $this->input->get("warehouse_id");
		$from_date = $this->bpas->fld($this->input->get("from_date"));
		$to_date = $this->bpas->fld($this->input->get("to_date"));
		$rows = $this->concretes_model->getRawMaterialNames($sr);
		if ($rows) {
			$c = str_replace(".", "", microtime(true));
			$r = 0;
			foreach ($rows as $row) {
				$stock_out = $this->concretes_model->getRawMeterialOut($row->id, $warehouse_id, $from_date, $to_date);
				$row->machine_qty = $stock_out->system_qty;
				$row->system_qty = $stock_out->system_qty;
				$pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row);
				$r++;
			}
			$this->bpas->send_json($pr);
		} else {
			$this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
		}
	}


	public function errors($warehouse_id = null, $biller_id = NULL)
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('errors')));
		$meta = array('page_title' => lang('errors'), 'bc' => $bc);
		$this->page_construct('concretes/errors', $meta, $this->data);
	}

	public function getErrors($warehouse_id = null, $biller_id = NULL)
	{
		$this->bpas->checkPermissions('errors');
		$edit_link = anchor('admin/concretes/edit_error/$1', '<i class="fa fa-edit"></i> ' . lang('edit_error'), ' class="edit_error" ');
		$delete_link = "<a href='#' class='po delete_error' title='<b>" . $this->lang->line("delete_error") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('concretes/delete_error/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_error') . "</a>";
		$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
		$this->load->library('datatables');
		$this->datatables
			->select("id, date, reference_no, attachment")
			->from("con_errors");
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('con_errors.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('con_errors.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('con_errors.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if ($warehouse_id) {
			$this->datatables->where('con_errors.warehouse_id', $warehouse_id);
		}
		if ($biller_id) {
			$this->datatables->where('con_errors.biller_id', $biller_id);
		}
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
	}

	public function add_error()
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cerror', $biller_id);
			if ($this->Owner || $this->Admin || $this->bpas->GP['concretes-errors-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$warehouse_id = $this->input->post('warehouse');
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$note = $this->bpas->clear_tags($this->input->post('note'));
			$i = isset($_POST['stregth_id']) ? sizeof($_POST['stregth_id']) : 0;
			$stockmoves = false;
			$materials = false;
			$error_item_id = 1;
			for ($r = 0; $r < $i; $r++) {
				$stregth_id = $_POST['stregth_id'][$r];
				$quantity = $_POST['quantity'][$r];
				$stregth = $this->concretes_model->getStregthByID($stregth_id);
				if ($quantity && $stregth->type == 'bom') {
					$product_boms = $this->concretes_model->getBomProductByStandProduct($stregth_id, $biller_id);
					if ($product_boms) {
						foreach ($product_boms as $product_bom) {
							$costs = false;
							if ($this->Settings->accounting_method == '0') {
								$costs = $this->site->getFifoCost($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves);
							} else if ($this->Settings->accounting_method == '1') {
								$costs = $this->site->getLifoCost($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves);
							} else if ($this->Settings->accounting_method == '3') {
								$costs = $this->site->getProductMethod($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves);
							}
							$productAcc = $this->site->getProductAccByProductId($product_bom->product_id);
							if ($costs) {
								foreach ($costs as $cost_item) {
									$stockmoves[] = array(
										'transaction' => 'CError',
										'product_id' => $product_bom->product_id,
										'product_type'    => $product_bom->product_type,
										'product_code' => $product_bom->product_code,
										'quantity' => $cost_item['quantity'] * (-1),
										'unit_quantity' => $product_bom->unit_qty,
										'unit_code' => $product_bom->code,
										'unit_id' => $product_bom->unit_id,
										'warehouse_id' => $warehouse_id,
										'date' => $date,
										'real_unit_cost' => $cost_item['cost'],
										'reference_no' => $reference_no,
										'user_id' => $this->session->userdata('user_id'),
									);
									if ($this->Settings->accounting == 1) {
										$accTrans[] = array(
											'tran_type' => 'CError',
											'tran_date' => $date,
											'reference_no' => $reference_no,
											'account_code' => $productAcc->stock_account,
											'amount' => ($cost_item['cost'] * $cost_item['quantity']) * (-1),
											'narrative' => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . $cost_item['quantity'] . '#' . 'Cost: ' . $cost_item['cost'],
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'created_by' => $this->session->userdata('user_id'),
										);
										$accTrans[] = array(
											'tran_type' => 'CError',
											'tran_date' => $date,
											'reference_no' => $reference_no,
											'account_code' => $productAcc->adjustment_acc,
											'amount' => ($cost_item['cost'] * $cost_item['quantity']),
											'narrative' => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . $cost_item['quantity'] . '#' . 'Cost: ' . $cost_item['cost'],
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'created_by' => $this->session->userdata('user_id'),
										);
									}
								}
							} else {
								$stockmoves[] = array(
									'transaction' => 'CError',
									'product_id' => $product_bom->product_id,
									'product_type'    => $product_bom->product_type,
									'product_code' => $product_bom->product_code,
									'quantity' => ($quantity * $product_bom->quantity) * -1,
									'unit_quantity' => $product_bom->unit_qty,
									'unit_code' => $product_bom->code,
									'unit_id' => $product_bom->unit_id,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'real_unit_cost' => $product_bom->cost,
									'reference_no' => $reference_no,
									'user_id' => $this->session->userdata('user_id'),
								);
								if ($this->Settings->accounting == 1) {
									$accTrans[] = array(
										'tran_type' => 'CError',
										'tran_date' => $date,
										'reference_no' => $reference_no,
										'account_code' => $productAcc->stock_account,
										'amount' => ($product_bom->cost * ($quantity * $product_bom->quantity)) * (-1),
										'narrative' => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . ($quantity * $product_bom->quantity) . '#' . 'Cost: ' . $product_bom->cost,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'created_by' => $this->session->userdata('user_id'),
									);
									$accTrans[] = array(
										'tran_type' => 'CError',
										'tran_date' => $date,
										'reference_no' => $reference_no,
										'account_code' => $productAcc->adjustment_acc,
										'amount' => ($product_bom->cost * ($quantity * $product_bom->quantity)),
										'narrative' => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . ($quantity * $product_bom->quantity) . '#' . 'Cost: ' . $product_bom->cost,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'created_by' => $this->session->userdata('user_id'),
									);
								}
							}
							$materials[$error_item_id][] = array(
								'stregth_id' => $stregth_id,
								'product_id' => $product_bom->product_id,
								'product_code' => $product_bom->product_code,
								'product_name' => $product_bom->product_name,
								'quantity' => ($quantity * $product_bom->quantity)
							);
						}
					} else {
						$error = lang('please_check_product') . ' ' . $stregth->name;
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
				}
				$items[] = array(
					'stregth_id' => $stregth_id,
					'stregth_code' => $stregth->code,
					'stregth_name' => $stregth->name,
					'quantity' => $quantity,
					'error_item_id' => $error_item_id
				);
				$error_item_id++;
			}
			if (empty($items)) {
				$this->form_validation->set_rules('stregth', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'biller_id' => $biller_id,
				'biller' => $biller,
				'project_id' => $project_id,
				'warehouse_id' => $warehouse_id,
				'note' => $note,
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s'),
			);

			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->addError($data, $items, $materials, $stockmoves, $accTrans)) {
			$this->session->set_userdata('remove_conerls', 1);
			$this->session->set_flashdata('message', $this->lang->line("error_added") . " " . $reference_no);
			if ($this->input->post('add_error_next')) {
				admin_redirect('concretes/add_error');
			} else {
				admin_redirect('concretes/errors');
			}
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] =  $this->site->getBillers();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/errors'), 'page' => lang('errors')), array('link' => '#', 'page' => lang('add_error')));
			$meta = array('page_title' => lang('add_error'), 'bc' => $bc);
			$this->page_construct('concretes/add_error', $meta, $this->data);
		}
	}

	public function edit_error($id = false)
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cerror', $biller_id);
			if ($this->Owner || $this->Admin || $this->bpas->GP['concretes-errors-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$warehouse_id = $this->input->post('warehouse');
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$note = $this->bpas->clear_tags($this->input->post('note'));
			$i = isset($_POST['stregth_id']) ? sizeof($_POST['stregth_id']) : 0;
			$stockmoves = false;
			$materials = false;
			$error_item_id = 1;
			for ($r = 0; $r < $i; $r++) {
				$stregth_id = $_POST['stregth_id'][$r];
				$quantity = $_POST['quantity'][$r];
				$stregth = $this->concretes_model->getStregthByID($stregth_id);
				if ($quantity && $stregth->type == 'bom') {
					$product_boms = $this->concretes_model->getBomProductByStandProduct($stregth_id, $biller_id);
					if ($product_boms) {
						foreach ($product_boms as $product_bom) {
							$costs = false;
							if ($this->Settings->accounting_method == '0') {
								$costs = $this->site->getFifoCost($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves, 'CError', $id);
							} else if ($this->Settings->accounting_method == '1') {
								$costs = $this->site->getLifoCost($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves, 'CError', $id);
							} else if ($this->Settings->accounting_method == '3') {
								$costs = $this->site->getProductMethod($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves, 'CError', $id);
							}
							$productAcc = $this->site->getProductAccByProductId($product_bom->product_id);
							if ($costs) {
								foreach ($costs as $cost_item) {
									$stockmoves[] = array(
										'transaction' => 'CError',
										'transaction_id' => $id,
										'product_id' => $product_bom->product_id,
										'product_type'    => $product_bom->product_type,
										'product_code' => $product_bom->product_code,
										'quantity' => $cost_item['quantity'] * (-1),
										'unit_quantity' => $product_bom->unit_qty,
										'unit_code' => $product_bom->code,
										'unit_id' => $product_bom->unit_id,
										'warehouse_id' => $warehouse_id,
										'date' => $date,
										'real_unit_cost' => $cost_item['cost'],
										'reference_no' => $reference_no,
										'user_id' => $this->session->userdata('user_id'),
									);
									if ($this->Settings->accounting == 1) {
										$accTrans[] = array(
											'tran_type' => 'CError',
											'tran_no' => $id,
											'tran_date' => $date,
											'reference_no' => $reference_no,
											'account_code' => $productAcc->stock_account,
											'amount' => ($cost_item['cost'] * $cost_item['quantity']) * (-1),
											'narrative' => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . $cost_item['quantity'] . '#' . 'Cost: ' . $cost_item['cost'],
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'created_by' => $this->session->userdata('user_id'),
										);
										$accTrans[] = array(
											'tran_type' => 'CError',
											'tran_no' => $id,
											'tran_date' => $date,
											'reference_no' => $reference_no,
											'account_code' => $productAcc->adjustment_acc,
											'amount' => ($cost_item['cost'] * $cost_item['quantity']),
											'narrative' => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . $cost_item['quantity'] . '#' . 'Cost: ' . $cost_item['cost'],
											'biller_id' => $biller_id,
											'project_id' => $project_id,
											'created_by' => $this->session->userdata('user_id'),
										);
									}
								}
							} else {
								$stockmoves[] = array(
									'transaction' => 'CError',
									'transaction_id' => $id,
									'product_id' => $product_bom->product_id,
									'product_type'    => $product_bom->product_type,
									'product_code' => $product_bom->product_code,
									'quantity' => ($quantity * $product_bom->quantity) * -1,
									'unit_quantity' => $product_bom->unit_qty,
									'unit_code' => $product_bom->code,
									'unit_id' => $product_bom->unit_id,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'real_unit_cost' => $product_bom->cost,
									'reference_no' => $reference_no,
									'user_id' => $this->session->userdata('user_id'),
								);
								if ($this->Settings->accounting == 1) {
									$accTrans[] = array(
										'tran_type' => 'CError',
										'tran_no' => $id,
										'tran_date' => $date,
										'reference_no' => $reference_no,
										'account_code' => $productAcc->stock_account,
										'amount' => ($product_bom->cost * ($quantity * $product_bom->quantity)) * (-1),
										'narrative' => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . ($quantity * $product_bom->quantity) . '#' . 'Cost: ' . $product_bom->cost,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'created_by' => $this->session->userdata('user_id'),
									);
									$accTrans[] = array(
										'tran_type' => 'CError',
										'tran_no' => $id,
										'tran_date' => $date,
										'reference_no' => $reference_no,
										'account_code' => $productAcc->adjustment_acc,
										'amount' => ($product_bom->cost * ($quantity * $product_bom->quantity)),
										'narrative' => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . ($quantity * $product_bom->quantity) . '#' . 'Cost: ' . $product_bom->cost,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'created_by' => $this->session->userdata('user_id'),
									);
								}
							}
							$materials[$error_item_id][] = array(
								'stregth_id' => $stregth_id,
								'product_id' => $product_bom->product_id,
								'product_code' => $product_bom->product_code,
								'product_name' => $product_bom->product_name,
								'quantity' => ($quantity * $product_bom->quantity)
							);
						}
					} else {
						$error = lang('please_check_product') . ' ' . $stregth->name;
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
				}
				$items[] = array(
					'error_id' => $id,
					'stregth_id' => $stregth_id,
					'stregth_code' => $stregth->code,
					'stregth_name' => $stregth->name,
					'quantity' => $quantity,
					'error_item_id' => $error_item_id
				);
				$error_item_id++;
			}
			if (empty($items)) {
				$this->form_validation->set_rules('stregth', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'biller_id' => $biller_id,
				'biller' => $biller,
				'project_id' => $project_id,
				'warehouse_id' => $warehouse_id,
				'note' => $note,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
			);

			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->updateError($id, $data, $items, $materials, $stockmoves, $accTrans)) {
			$this->session->set_userdata('remove_conerls', 1);
			$this->session->set_flashdata('message', $this->lang->line("error_edited") . " " . $reference_no);
			admin_redirect('concretes/errors');
		} else {
			$c_error = $this->concretes_model->getErrorByID($id);
			$c_error_items = $this->concretes_model->getErrorItems($id);
			krsort($c_error_items);
			$c = rand(100000, 9999999);
			foreach ($c_error_items as $item) {
				$row = $this->concretes_model->getStregthByID($item->stregth_id);
				$row->quantity = $item->quantity;
				$ri = $this->Settings->item_addition ? $row->id : $c;
				$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row);
				$c++;
			}
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['c_error'] = $c_error;
			$this->data['c_error_items'] = json_encode($pr);
			$this->data['billers'] =  $this->site->getBillers();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->session->set_userdata('remove_conerls', 1);
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/errors'), 'page' => lang('errors')), array('link' => '#', 'page' => lang('edit_error')));
			$meta = array('page_title' => lang('edit_error'), 'bc' => $bc);
			$this->page_construct('concretes/edit_error', $meta, $this->data);
		}
	}
	public function delete_error($id = null)
	{
		$this->bpas->checkPermissions(NULL, true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->concretes_model->deleteError($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("errors_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('fuel_deleted'));
			admin_redirect('concretes/errors');
		}
	}
	public function modal_view_error($id = null)
	{
		$this->bpas->checkPermissions('errors', true);
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$c_error = $this->concretes_model->getErrorByID($id);
		$this->data['biller'] = $this->site->getCompanyByID($c_error->biller_id);
		$this->data['c_error'] = $c_error;
		$this->data['c_error_items'] = $this->concretes_model->getErrorItems($id);
		$this->data['c_error_materials'] = $this->concretes_model->getErrorMaterials($id);
		$this->data['created_by'] = $this->site->getUserByID($c_error->created_by);
		$this->load->view($this->theme . 'concretes/modal_view_error', $this->data);
	}
	public function error_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_error', true);
					foreach ($_POST['val'] as $id) {
						$this->concretes_model->deleteError($id);
					}
					$this->session->set_flashdata('message', $this->lang->line("error_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				} elseif ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('error'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$row = 2;
					foreach ($_POST['val'] as $id) {
						$error = $this->concretes_model->getErrorByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($error->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $error->reference_no);
						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'errors_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function daily_error_materials()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['products'] = $this->concretes_model->getProducts();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('daily_error_materials')));
		$meta = array('page_title' => lang('daily_error_materials'), 'bc' => $bc);
		$this->page_construct('concretes/daily_error_materials', $meta, $this->data);
	}
	public function getDailyErrorMaterials($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('daily_error_materials');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$product = $this->input->get('product') ? $this->input->get('product') : NULL;
		$warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
									DATE_FORMAT(" . $this->db->dbprefix('con_errors') . ".date, '%Y-%m-%d %T') as date,
									con_error_materials.product_name,
									convert_qty(" . $this->db->dbprefix('con_error_materials') . ".product_id,IFNULL(sum(" . $this->db->dbprefix('con_error_materials') . ".quantity),0)) as quantity,
									")
				->from("con_errors")
				->join("con_error_materials", "con_errors.id = con_error_materials.error_id", "inner")
				->group_by("date(" . $this->db->dbprefix('con_errors') . ".date),con_error_materials.product_id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_errors.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_errors.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->db->where_in('con_errors.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if ($start_date) {
				$this->db->where('con_errors.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_errors.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_errors.biller_id', $biller);
			}
			if ($warehouse) {
				$this->db->where('con_errors.warehouse_id >=', $warehouse);
			}
			if ($product) {
				$this->db->where('con_error_materials.product_id', $product);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('daily_error_materials'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('product'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('quantity'));

				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->product_name);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->remove_tag($data_row->quantity));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);

				$filename = 'daily_error_materials_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
									DATE_FORMAT(" . $this->db->dbprefix('con_errors') . ".date, '%Y-%m-%d %T') as date,
									con_error_materials.product_name,
									convert_qty(" . $this->db->dbprefix('con_error_materials') . ".product_id,IFNULL(sum(" . $this->db->dbprefix('con_error_materials') . ".quantity),0)) as quantity,
									")
				->from("con_errors")
				->join("con_error_materials", "con_errors.id = con_error_materials.error_id", "inner")
				->group_by("date(" . $this->db->dbprefix('con_errors') . ".date),con_error_materials.product_id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_errors.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_errors.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->datatables->where_in('con_errors.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if ($start_date) {
				$this->datatables->where('con_errors.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_errors.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_errors.biller_id', $biller);
			}
			if ($warehouse) {
				$this->datatables->where('con_errors.warehouse_id >=', $warehouse);
			}
			if ($product) {
				$this->datatables->where('con_error_materials.product_id', $product);
			}
			echo $this->datatables->generate();
		}
	}

	public function daily_errors()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['stregths'] = $this->concretes_model->getStregths();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('daily_errors')));
		$meta = array('page_title' => lang('daily_errors'), 'bc' => $bc);
		$this->page_construct('concretes/daily_errors', $meta, $this->data);
	}
	public function getDailyErrors($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('daily_errors');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$stregth = $this->input->get('stregth') ? $this->input->get('stregth') : NULL;
		$warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(" . $this->db->dbprefix('con_errors') . ".date, '%Y-%m-%d %T') as date,
								con_error_items.stregth_code,
								con_error_items.stregth_name,
								sum(" . $this->db->dbprefix('con_error_items') . ".quantity) as quantity
								")
				->from("con_errors")
				->join("con_error_items", "con_errors.id = con_error_items.error_id", "inner")
				->group_by("date(" . $this->db->dbprefix('con_errors') . ".date),con_error_items.stregth_id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_errors.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_errors.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->db->where_in('con_errors.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if ($start_date) {
				$this->db->where('con_errors.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_errors.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_errors.biller_id', $biller);
			}
			if ($warehouse) {
				$this->db->where('con_errors.warehouse_id >=', $warehouse);
			}
			if ($stregth) {
				$this->db->where('con_error_items.stregth_id', $stregth);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('daily_errors'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('stregth_code'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('stregth_name'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('quantity'));

				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->stregth_code);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->stregth_name);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($data_row->quantity));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

				$filename = 'daily_errors_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
									DATE_FORMAT(" . $this->db->dbprefix('con_errors') . ".date, '%Y-%m-%d %T') as date,
									con_error_items.stregth_code,
									con_error_items.stregth_name,
									sum(" . $this->db->dbprefix('con_error_items') . ".quantity) as quantity,
									")
				->from("con_errors")
				->join("con_error_items", "con_errors.id = con_error_items.error_id", "inner")
				->group_by("date(" . $this->db->dbprefix('con_errors') . ".date),con_error_items.stregth_id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_errors.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_errors.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->datatables->where_in('con_errors.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if ($start_date) {
				$this->datatables->where('con_errors.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_errors.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_errors.biller_id', $biller);
			}
			if ($warehouse) {
				$this->datatables->where('con_errors.warehouse_id >=', $warehouse);
			}
			if ($stregth) {
				$this->datatables->where('con_error_items.stregth_id', $stregth);
			}
			echo $this->datatables->generate();
		}
	}


	public function mission_types($action = NULL)
	{
		$this->bpas->checkPermissions('mission_types');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['action'] = $action;
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => 'concretes', 'page' => lang('concrete')), array('link' => '#', 'page' => lang('mission_types')));
		$meta = array('page_title' => lang('mission_types'), 'bc' => $bc);
		$this->page_construct('concretes/mission_types', $meta, $this->data);
	}

	public function getMissionTypes()
	{
		$this->bpas->checkPermissions('mission_types');
		$this->load->library('datatables');
		$this->datatables
			->select("con_mission_types.id as id,
						con_mission_types.name,
						con_mission_types.fuel,
						con_mission_types.road_fee,
						con_mission_types.food_expense,
						con_mission_types.other_expense,
						con_mission_types.note
						")
			->from("con_mission_types")
			->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_mission_type") . "' href='" . admin_url('concretes/edit_mission_type/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_mission_type") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('concretes/delete_mission_type/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
		echo $this->datatables->generate();
	}
	public function add_mission_type()
	{
		$this->bpas->checkPermissions('mission_types', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run() == true) {
			$data = array(
				'name' => $this->input->post('name'),
				'fuel' => $this->input->post('fuel'),
				'road_fee' => $this->input->post('road_fee'),
				'food_expense' => $this->input->post('food_expense'),
				'other_expense' => $this->input->post('other_expense'),
				'note' => $this->input->post('note'),
				'road_acc' => $this->input->post('road_acc'),
				'food_acc' => $this->input->post('food_acc'),
				'other_acc' => $this->input->post('other_acc'),
			);
		} elseif ($this->input->post('add_mission_type')) {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect('concretes/mission_types');
		}
		if ($this->form_validation->run() == true && $id = $this->concretes_model->addMissionType($data)) {
			$this->session->set_flashdata('message', $this->lang->line("mission_type_added"));
			admin_redirect('concretes/mission_types');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			if ($this->Settings->accounting == 1) {
				$this->data['road_acc'] = $this->site->getAccount(array('EX', 'OX'));
				$this->data['food_acc'] = $this->site->getAccount(array('EX', 'OX'));
				$this->data['other_acc'] = $this->site->getAccount(array('EX', 'OX'));
			}
			$this->load->view($this->theme . 'concretes/add_mission_type', $this->data);
		}
	}

	public function edit_mission_type($id = false)
	{
		$this->bpas->checkPermissions('mission_types', true);
		$mission_type = $this->concretes_model->getMissionTypeByID($id);
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run('concretes_model/addAccount') == true) {
			$data = array(
				'name' => $this->input->post('name'),
				'road_fee' => $this->input->post('road_fee'),
				'food_expense' => $this->input->post('food_expense'),
				'other_expense' => $this->input->post('other_expense'),
				'note' => $this->input->post('note'),
				'road_acc' => $this->input->post('road_acc'),
				'food_acc' => $this->input->post('food_acc'),
				'other_acc' => $this->input->post('other_acc'),
			);
		} elseif ($this->input->post('edit_mission_type')) {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect('concretes/mission_types');
		}
		if ($this->form_validation->run() == true && $id = $this->concretes_model->updateMissionType($id, $data)) {
			$this->session->set_flashdata('message', $this->lang->line("mission_type_edited"));
			admin_redirect('concretes/mission_types');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			if ($this->Settings->accounting == 1) {
				$this->data['road_acc'] = $this->site->getAccount(array('EX', 'OX'), $mission_type->road_acc);
				$this->data['food_acc'] = $this->site->getAccount(array('EX', 'OX'), $mission_type->food_acc);
				$this->data['other_acc'] = $this->site->getAccount(array('EX', 'OX'), $mission_type->other_acc);
			}
			$this->data['mission_type'] = $mission_type;
			$this->load->view($this->theme . 'concretes/edit_mission_type', $this->data);
		}
	}
	public function delete_mission_type($id = NULL)
	{
		$this->bpas->checkPermissions('mission_types', true);
		if ($this->concretes_model->deleteMissionType($id)) {
			echo $this->lang->line("mission_type_deleted");
		} else {
			$this->session->set_flashdata('warning', lang('mission_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : admin_url('welcome')) . "'; }, 0);</script>");
		}
	}
	public function mission_type_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('mission_types');
					$error = false;
					foreach ($_POST['val'] as $id) {
						if (!$this->concretes_model->deleteMissionType($id)) {
							$error = true;
						}
					}
					if ($error) {
						$this->session->set_flashdata('warning', lang('mission_type_cannot_delete'));
					} else {
						$this->session->set_flashdata('message', $this->lang->line("mission_type_deleted"));
					}
					redirect($_SERVER["HTTP_REFERER"]);
				}
				if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('mission_types'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('fuel'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('road_fee'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('food_expense'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('other_expense'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('note'));

					$row = 2;
					foreach ($_POST['val'] as $id) {
						$mission_type = $this->concretes_model->getMissionTypeByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $mission_type->name);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->formatDecimal($mission_type->fuel));
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatDecimal($mission_type->road_fee));
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($mission_type->food_expense));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($mission_type->other_expense));
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->remove_tag($mission_type->note));
						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'mission_types_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_account_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}





	public function moving_waitings($biller_id = NULL)
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('moving_waitings')));
		$meta = array('page_title' => lang('moving_waitings'), 'bc' => $bc);
		$this->page_construct('concretes/moving_waitings', $meta, $this->data);
	}


	public function getMovingWaitings($biller_id = NULL)
	{
		$this->bpas->checkPermissions('moving_waitings');
		$edit_link = anchor('admin/concretes/edit_moving_waiting/$1', '<i class="fa fa-edit"></i> ' . lang('edit_moving_waiting'), ' class="edit_moving_waiting" ');
		$delete_link = "<a href='#' class='po delete_moving_waiting' title='<b>" . $this->lang->line("delete_moving_waiting") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('concretes/delete_moving_waiting/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_moving_waiting') . "</a>";
		$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
		$this->load->library('datatables');
		$this->datatables
			->select("con_moving_waitings.id as id, con_moving_waitings.date, con_moving_waitings.reference_no,CONCAT(" . $this->db->dbprefix('users') . ".last_name,' '," . $this->db->dbprefix('users') . ".first_name) as created_by,  con_moving_waitings.attachment")
			->join("users", "users.id = con_moving_waitings.created_by", "left")
			->from("con_moving_waitings");
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('con_moving_waitings.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('con_moving_waitings.biller_id', $this->session->userdata('biller_id'));
		}
		if ($biller_id) {
			$this->datatables->where('con_moving_waitings.biller_id', $biller_id);
		}
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
	}

	public function add_moving_waiting()
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cmw', $biller_id);
			if ($this->Owner || $this->Admin || $this->bpas->GP['concretes-moving_waitings-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$note = $this->bpas->clear_tags($this->input->post('note'));
			$i = isset($_POST['truck_id']) ? sizeof($_POST['truck_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$truck_id = $_POST['truck_id'][$r];
				$driver_id = $_POST['driver_id'][$r];
				$times_hours = $_POST['times_hours'][$r];
				if (isset($truck_id) && isset($times_hours)) {
					$truck_detail = $this->concretes_model->getTruckByID($truck_id);
					$driver = $this->concretes_model->getDriverByID($driver_id);
					$items[] = array(
						'truck_id' => $truck_id,
						'truck_code' => $truck_detail->code,
						'driver_id' => $driver_id,
						'driver_name' => $driver->full_name_kh . ' - ' . $driver->full_name,
						'times_hours' => $times_hours
					);

					//=========================used fuel=============================//
					$waiting_litre = 0;
					$moving_litre = 0;
					if ($truck_detail->type == 'truck') {
						if ($times_hours >= $truck_detail->waiting_from && $times_hours <= $truck_detail->waiting_to) {
							$waiting_litre = $truck_detail->waiting_litre;
						} else if ($times_hours >= $truck_detail->waiting_from2 && $times_hours <= $truck_detail->waiting_to2) {
							$waiting_litre = $truck_detail->waiting_litre2;
						}
					} else {
						if ($times_hours >= $truck_detail->moving_from && $times_hours <= $truck_detail->moving_to) {
							$moving_litre = $truck_detail->moving_litre;
						} else if ($times_hours >= $truck_detail->moving_from2 && $times_hours <= $truck_detail->moving_to2) {
							$moving_litre = $truck_detail->moving_litre2;
						}
					}

					if ($waiting_litre > 0 || $moving_litre > 0) {
						$used_fuels[] = array(
							'date' => $date,
							'biller_id' => $biller_id,
							'driver_id' => $driver_id,
							'truck_id' => $truck_id,
							'moving_litre' => ($moving_litre > 0 ? (($moving_litre * $truck_detail->driver_fuel_fee) / 100) : 0),
							'waiting_litre' => ($waiting_litre > 0 ? (($waiting_litre * $truck_detail->driver_fuel_fee) / 100) : 0)
						);

						if ($truck_detail->driver_assistant && $truck_detail->driver_fuel_fee != 100) {
							$driver_assistants = json_decode($truck_detail->driver_assistant);
							$total_asstants = count($driver_assistants);
							foreach ($driver_assistants as $driver_assistant) {
								$used_fuels[] = array(
									'date' => $date,
									'biller_id' => $biller_id,
									'driver_id' => $driver_assistant,
									'truck_id' => $truck_id,
									'moving_litre' => ($moving_litre > 0 ? ((($moving_litre * $truck_detail->assistant_fuel_fee) / 100) / $total_asstants) : 0),
									'waiting_litre' => ($waiting_litre > 0 ? ((($waiting_litre * $truck_detail->assistant_fuel_fee) / 100) / $total_asstants) : 0)
								);
							}
						}
					}
					//=========================end used fuel=============================//
				}
			}
			if (empty($items)) {
				$this->form_validation->set_rules('truck', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'biller_id' => $biller_id,
				'biller' => $biller,
				'project_id' => $project_id,
				'note' => $note,
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s'),
			);
			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->addMovingWaiting($data, $items, $used_fuels)) {
			$this->session->set_userdata('remove_conmwls', 1);
			$this->session->set_flashdata('message', $this->lang->line("moving_waiting_added") . " " . $reference_no);
			if ($this->input->post('add_moving_waiting_next')) {
				admin_redirect('concretes/add_moving_waiting');
			} else {
				admin_redirect('concretes/moving_waitings');
			}
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] =  $this->site->getBillers();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/moving_waitings'), 'page' => lang('moving_waitings')), array('link' => '#', 'page' => lang('add_moving_waiting')));
			$meta = array('page_title' => lang('add_moving_waiting'), 'bc' => $bc);
			$this->page_construct('concretes/add_moving_waiting', $meta, $this->data);
		}
	}
	public function edit_moving_waiting($id = false)
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cmw', $biller_id);
			if ($this->Owner || $this->Admin || $this->bpas->GP['concretes-moving_waitings-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$note = $this->bpas->clear_tags($this->input->post('note'));
			$i = isset($_POST['truck_id']) ? sizeof($_POST['truck_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$truck_id = $_POST['truck_id'][$r];
				$driver_id = $_POST['driver_id'][$r];
				$times_hours = $_POST['times_hours'][$r];
				if (isset($truck_id) && isset($times_hours)) {
					$truck_detail = $this->concretes_model->getTruckByID($truck_id);
					$driver = $this->concretes_model->getDriverByID($driver_id);
					$items[] = array(
						'moving_waiting_id' => $id,
						'truck_id' => $truck_id,
						'truck_code' => $truck_detail->code,
						'driver_id' => $driver_id,
						'driver_name' => $driver->full_name_kh . ' - ' . $driver->full_name,
						'times_hours' => $times_hours
					);

					//=========================used fuel=============================//
					$waiting_litre = 0;
					$moving_litre = 0;
					if ($truck_detail->type == 'truck') {
						if ($times_hours >= $truck_detail->waiting_from && $times_hours <= $truck_detail->waiting_to) {
							$waiting_litre = $truck_detail->waiting_litre;
						} else if ($times_hours >= $truck_detail->waiting_from2 && $times_hours <= $truck_detail->waiting_to2) {
							$waiting_litre = $truck_detail->waiting_litre2;
						}
					} else {
						if ($times_hours >= $truck_detail->moving_from && $times_hours <= $truck_detail->moving_to) {
							$moving_litre = $truck_detail->moving_litre;
						} else if ($times_hours >= $truck_detail->moving_from2 && $times_hours <= $truck_detail->moving_to2) {
							$moving_litre = $truck_detail->moving_litre2;
						}
					}

					if ($waiting_litre > 0 || $moving_litre > 0) {
						$used_fuels[] = array(
							'moving_waiting_id' => $id,
							'date' => $date,
							'biller_id' => $biller_id,
							'driver_id' => $driver_id,
							'truck_id' => $truck_id,
							'moving_litre' => ($moving_litre > 0 ? (($moving_litre * $truck_detail->driver_fuel_fee) / 100) : 0),
							'waiting_litre' => ($waiting_litre > 0 ? (($waiting_litre * $truck_detail->driver_fuel_fee) / 100) : 0)
						);

						if ($truck_detail->driver_assistant && $truck_detail->driver_fuel_fee != 100) {
							$driver_assistants = json_decode($truck_detail->driver_assistant);
							$total_asstants = count($driver_assistants);
							foreach ($driver_assistants as $driver_assistant) {
								$used_fuels[] = array(
									'moving_waiting_id' => $id,
									'date' => $date,
									'biller_id' => $biller_id,
									'driver_id' => $driver_assistant,
									'truck_id' => $truck_id,
									'moving_litre' => ($moving_litre > 0 ? ((($moving_litre * $truck_detail->assistant_fuel_fee) / 100) / $total_asstants) : 0),
									'waiting_litre' => ($waiting_litre > 0 ? ((($waiting_litre * $truck_detail->assistant_fuel_fee) / 100) / $total_asstants) : 0)
								);
							}
						}
					}
					//=========================end used fuel=============================//
				}
			}
			if (empty($items)) {
				$this->form_validation->set_rules('truck', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'biller_id' => $biller_id,
				'biller' => $biller,
				'project_id' => $project_id,
				'note' => $note,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
			);

			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->updateMovingWaiting($id, $data, $items, $used_fuels)) {
			$this->session->set_userdata('remove_conmwls', 1);
			$this->session->set_flashdata('message', $this->lang->line("moving_waiting_edited") . " " . $reference_no);
			admin_redirect('concretes/moving_waitings');
		} else {
			$moving_waiting = $this->concretes_model->getMovingWaitingByID($id);
			$moving_waiting_items = $this->concretes_model->getMovingWaitingItems($id);
			$drivers = $this->concretes_model->getDrivers();
			krsort($moving_waiting_items);
			$c = rand(100000, 9999999);
			foreach ($moving_waiting_items as $item) {
				$row = $this->concretes_model->getTruckByID($item->truck_id);
				$row->times_hours = $item->times_hours;
				$row->driver_id = $item->driver_id;
				$pr[$row->id] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->code . " - " . $row->plate, 'row' => $row, 'drivers' => $drivers);
				$c++;
			}
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['moving_waiting'] = $moving_waiting;
			$this->data['moving_waiting_items'] = json_encode($pr);
			$this->data['billers'] =  $this->site->getBillers();
			$this->session->set_userdata('remove_conmwls', 1);
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/moving_waitings'), 'page' => lang('moving_waitings')), array('link' => '#', 'page' => lang('edit_moving_waiting')));
			$meta = array('page_title' => lang('edit_moving_waiting'), 'bc' => $bc);
			$this->page_construct('concretes/edit_moving_waiting', $meta, $this->data);
		}
	}

	public function delete_moving_waiting($id = null)
	{
		$this->bpas->checkPermissions(NULL, true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->concretes_model->deleteMovingWaiting($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("moving_waiting_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('moving_waiting_deleted'));
			admin_redirect('concretes/moving_waitings');
		}
	}
	public function moving_waiting_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_moving_waiting', true);
					foreach ($_POST['val'] as $id) {
						$this->concretes_model->deleteMovingWaiting($id);
					}
					$this->session->set_flashdata('message', $this->lang->line("moving_waiting_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				} elseif ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('moving_waiting'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('created_by'));
					$row = 2;
					foreach ($_POST['val'] as $id) {
						$moving_waiting = $this->concretes_model->getMovingWaitingByID($id);
						$created_by = $this->site->getUserByID($moving_waiting->created_by);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($moving_waiting->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $moving_waiting->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $created_by->last_name . ' ' . $created_by->first_name);
						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'moving_waitings_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function modal_view_moving_waiting($id = null)
	{
		$this->bpas->checkPermissions('moving_waitings', true);
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$moving_waiting = $this->concretes_model->getMovingWaitingByID($id);
		$this->data['biller'] = $this->site->getCompanyByID($moving_waiting->biller_id);
		$this->data['moving_waiting'] = $moving_waiting;
		$this->data['moving_waiting_items'] = $this->concretes_model->getMovingWaitingItems($id);;
		$this->data['created_by'] = $this->site->getUserByID($moving_waiting->created_by);
		$this->load->view($this->theme . 'concretes/modal_view_moving_waiting', $this->data);
	}

	public function missions($biller_id = NULL)
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('missions')));
		$meta = array('page_title' => lang('missions'), 'bc' => $bc);
		$this->page_construct('concretes/missions', $meta, $this->data);
	}


	public function getMissions($biller_id = NULL)
	{
		$this->bpas->checkPermissions('missions');
		$edit_link = anchor('admin/concretes/edit_mission/$1', '<i class="fa fa-edit"></i> ' . lang('edit_mission'), ' class="edit_mission" ');
		$delete_link = "<a href='#' class='po delete_mission' title='<b>" . $this->lang->line("delete_mission") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('concretes/delete_mission/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_mission') . "</a>";
		$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
		$this->load->library('datatables');
		$this->datatables
			->select("con_missions.id as id, con_missions.date, con_missions.reference_no,IFNULL(" . $this->db->dbprefix('con_missions') . ".total_expense,0) as total_expense,CONCAT(" . $this->db->dbprefix('users') . ".last_name,' '," . $this->db->dbprefix('users') . ".first_name) as created_by,  con_missions.attachment")
			->join("users", "users.id = con_missions.created_by", "left")
			->from("con_missions");
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('con_missions.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('con_missions.biller_id', $this->session->userdata('biller_id'));
		}
		if ($biller_id) {
			$this->datatables->where('con_missions.biller_id', $biller_id);
		}
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
	}

	public function add_mission()
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cmission', $biller_id);
			if ($this->Owner || $this->Admin || $this->bpas->GP['concretes-missions-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$note = $this->bpas->clear_tags($this->input->post('note'));
			$total_expense = 0;
			$i = isset($_POST['truck_id']) ? sizeof($_POST['truck_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$truck_id = $_POST['truck_id'][$r];
				$driver_id = $_POST['driver_id'][$r];
				$mission_date = $this->bpas->fld(trim($_POST['mission_date'][$r]));
				$mission_type_id = $_POST['mission_type'][$r];
				$mission_type = $this->concretes_model->getMissionTypeByID($mission_type_id);
				if (isset($truck_id) && isset($mission_type_id)) {
					$truck_detail = $this->concretes_model->getTruckByID($truck_id);
					$driver = $this->concretes_model->getDriverByID($driver_id);
					if ($truck_detail->driver_assistant) {
						$driver_assistants = json_decode($truck_detail->driver_assistant);
						$total_driver = count($driver_assistants) + 1;
						$mission_type->food_expense = $mission_type->food_expense * $total_driver;
					}
					$items[] = array(
						'truck_id' => $truck_id,
						'truck_code' => $truck_detail->code,
						'driver_id' => $driver_id,
						'driver_name' => $driver->full_name_kh . ' - ' . $driver->full_name,
						'mission_date' => $mission_date,
						'mission_type_id' => $mission_type_id,
						'fuel' => $mission_type->fuel,
						'road_fee' => $mission_type->road_fee,
						'food_expense' => $mission_type->food_expense,
						'other_expense' => $mission_type->other_expense,
					);

					if ($this->Settings->accounting == 1) {
						if ($mission_type->road_fee > 0) {
							$accTrans[] = array(
								'tran_type' => 'Payment',
								'tran_date' => $date,
								'reference_no' => $reference_no,
								'account_code' => $mission_type->road_acc,
								'amount' => $mission_type->road_fee,
								'narrative' => "Mission Road Fee",
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'created_by' => $this->session->userdata('user_id'),
							);
						}
						if ($mission_type->food_expense > 0) {
							$accTrans[] = array(
								'tran_type' => 'Payment',
								'tran_date' => $date,
								'reference_no' => $reference_no,
								'account_code' => $mission_type->food_acc,
								'amount' => $mission_type->food_expense,
								'narrative' => "Mission Food Expense",
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'created_by' => $this->session->userdata('user_id'),
							);
						}
						if ($mission_type->other_expense > 0) {
							$accTrans[] = array(
								'tran_type' => 'Payment',
								'tran_date' => $date,
								'reference_no' => $reference_no,
								'account_code' => $mission_type->other_acc,
								'amount' => $mission_type->other_expense,
								'narrative' => "Mission Other Expense",
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'created_by' => $this->session->userdata('user_id'),
							);
						}
					}

					//=========================used fuel=============================//
					$mission_litre = $mission_type->fuel;
					if ($mission_litre > 0) {
						$used_fuels[] = array(
							'date' => $date,
							'biller_id' => $biller_id,
							'driver_id' => $driver_id,
							'truck_id' => $truck_id,
							'mission_litre' => (($mission_litre * $truck_detail->driver_fuel_fee) / 100)
						);
						if ($truck_detail->driver_assistant && $truck_detail->driver_fuel_fee != 100) {
							$driver_assistants = json_decode($truck_detail->driver_assistant);
							$total_asstants = count($driver_assistants);
							foreach ($driver_assistants as $driver_assistant) {
								$used_fuels[] = array(
									'date' => $date,
									'biller_id' => $biller_id,
									'driver_id' => $driver_assistant,
									'truck_id' => $truck_id,
									'mission_litre' => ((($mission_litre * $truck_detail->assistant_fuel_fee) / 100) / $total_asstants)
								);
							}
						}
					}
					//=========================end used fuel=============================//
					$total_expense += ($mission_type->road_fee + $mission_type->food_expense + $mission_type->other_expense);
				}
			}
			if (empty($items)) {
				$this->form_validation->set_rules('truck', lang("order_items"), 'required');
			} else {
				krsort($items);
			}

			$data = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'biller_id' => $biller_id,
				'biller' => $biller,
				'project_id' => $project_id,
				'note' => $note,
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s'),
				'total_expense' => $total_expense,
				'paid_by' => $this->input->post('paid_by'),
			);

			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			$payment = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'transaction' => "Con Mission",
				'amount' => $total_expense,
				'paid_by' => $this->input->post('paid_by'),
				'note' => $note,
				'created_by' => $this->session->userdata('user_id'),
				'type' => "expense",
				'account_code' => $paying_from,
			);

			if ($this->Settings->accounting == 1) {
				$accTrans[] = array(
					'tran_type' => 'Payment',
					'tran_date' => $date,
					'reference_no' => $reference_no,
					'account_code' => $paying_from,
					'amount' => $total_expense * (-1),
					'narrative' => "Mission",
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
			}

			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->addMission($data, $items, $used_fuels, $payment, $accTrans)) {
			$this->session->set_userdata('remove_conmsls', 1);
			$this->session->set_flashdata('message', $this->lang->line("mission_added") . " " . $reference_no);
			if ($this->input->post('add_mission_next')) {
				admin_redirect('concretes/add_mission');
			} else {
				admin_redirect('concretes/missions');
			}
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] =  $this->site->getBillers();
			$this->data['warehouses'] = $this->site->getWarehouses();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/missions'), 'page' => lang('missions')), array('link' => '#', 'page' => lang('add_mission')));
			$meta = array('page_title' => lang('add_mission'), 'bc' => $bc);
			$this->page_construct('concretes/add_mission', $meta, $this->data);
		}
	}
	public function edit_mission($id = false)
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cmission', $biller_id);
			if ($this->Owner || $this->Admin || $this->bpas->GP['concretes-missions-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$note = $this->bpas->clear_tags($this->input->post('note'));
			$total_expense = 0;
			$i = isset($_POST['truck_id']) ? sizeof($_POST['truck_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$truck_id = $_POST['truck_id'][$r];
				$driver_id = $_POST['driver_id'][$r];
				$mission_date = $this->bpas->fld(trim($_POST['mission_date'][$r]));
				$mission_type_id = $_POST['mission_type'][$r];
				$mission_type = $this->concretes_model->getMissionTypeByID($mission_type_id);

				if (isset($truck_id) && isset($mission_type_id)) {
					$truck_detail = $this->concretes_model->getTruckByID($truck_id);
					$driver = $this->concretes_model->getDriverByID($driver_id);
					if ($truck_detail->driver_assistant) {
						$driver_assistants = json_decode($truck_detail->driver_assistant);
						$total_driver = count($driver_assistants) + 1;
						$mission_type->food_expense = $mission_type->food_expense * $total_driver;
					}

					$items[] = array(
						'mission_id' => $id,
						'truck_id' => $truck_id,
						'truck_code' => $truck_detail->code,
						'driver_id' => $driver_id,
						'driver_name' => $driver->full_name_kh . ' - ' . $driver->full_name,
						'mission_date' => $mission_date,
						'mission_type_id' => $mission_type_id,
						'fuel' => $mission_type->fuel,
						'road_fee' => $mission_type->road_fee,
						'food_expense' => $mission_type->food_expense,
						'other_expense' => $mission_type->other_expense,
					);

					if ($this->Settings->accounting == 1) {
						if ($mission_type->road_fee > 0) {
							$accTrans[] = array(
								'tran_type' => 'Payment',
								'tran_date' => $date,
								'reference_no' => $reference_no,
								'account_code' => $mission_type->road_acc,
								'amount' => $mission_type->road_fee,
								'narrative' => "Mission Road Fee",
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'created_by' => $this->session->userdata('user_id'),
							);
						}
						if ($mission_type->food_expense > 0) {
							$accTrans[] = array(
								'tran_type' => 'Payment',
								'tran_date' => $date,
								'reference_no' => $reference_no,
								'account_code' => $mission_type->food_acc,
								'amount' => $mission_type->food_expense,
								'narrative' => "Mission Food Expense",
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'created_by' => $this->session->userdata('user_id'),
							);
						}
						if ($mission_type->other_expense > 0) {
							$accTrans[] = array(
								'tran_type' => 'Payment',
								'tran_date' => $date,
								'reference_no' => $reference_no,
								'account_code' => $mission_type->other_acc,
								'amount' => $mission_type->other_expense,
								'narrative' => "Mission Other Expense",
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'created_by' => $this->session->userdata('user_id'),
							);
						}
					}

					//=========================used fuel=============================//
					$mission_litre = $mission_type->fuel;
					if ($mission_litre > 0) {
						$used_fuels[] = array(
							'mission_id' => $id,
							'date' => $date,
							'biller_id' => $biller_id,
							'driver_id' => $driver_id,
							'truck_id' => $truck_id,
							'mission_litre' => (($mission_litre * $truck_detail->driver_fuel_fee) / 100)
						);
						if ($truck_detail->driver_assistant && $truck_detail->driver_fuel_fee != 100) {
							$driver_assistants = json_decode($truck_detail->driver_assistant);
							$total_asstants = count($driver_assistants);
							foreach ($driver_assistants as $driver_assistant) {
								$used_fuels[] = array(
									'mission_id' => $id,
									'date' => $date,
									'biller_id' => $biller_id,
									'driver_id' => $driver_assistant,
									'truck_id' => $truck_id,
									'mission_litre' => ((($mission_litre * $truck_detail->assistant_fuel_fee) / 100) / $total_asstants)
								);
							}
						}
					}
					//=========================end used fuel=============================//
					$total_expense += ($mission_type->road_fee + $mission_type->food_expense + $mission_type->other_expense);
				}
			}
			if (empty($items)) {
				$this->form_validation->set_rules('truck', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'biller_id' => $biller_id,
				'biller' => $biller,
				'project_id' => $project_id,
				'note' => $note,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
				'total_expense' => $total_expense,
				'paid_by' => $this->input->post('paid_by'),
			);


			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			$payment = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'transaction' => "Con Mission",
				'transaction_id' => $id,
				'amount' => $total_expense,
				'paid_by' => $this->input->post('paid_by'),
				'note' => $note,
				'created_by' => $this->session->userdata('user_id'),
				'type' => "expense",
				'account_code' => $paying_from,
			);
			if ($this->Settings->accounting == 1) {
				$accTrans[] = array(
					'tran_type' => 'Payment',
					'tran_date' => $date,
					'reference_no' => $reference_no,
					'account_code' => $paying_from,
					'amount' => $total_expense * (-1),
					'narrative' => "Mission",
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
			}
			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->updateMission($id, $data, $items, $used_fuels, $payment, $accTrans)) {
			$this->session->set_userdata('remove_conmsls', 1);
			$this->session->set_flashdata('message', $this->lang->line("mission_edited") . " " . $reference_no);
			admin_redirect('concretes/missions');
		} else {
			$mission = $this->concretes_model->getMissionByID($id);
			$mission_items = $this->concretes_model->getMissionItems($id);
			$drivers = $this->concretes_model->getDrivers();
			$mission_types = $this->concretes_model->getMissionTypes();
			krsort($mission_items);
			$c = rand(100000, 9999999);
			foreach ($mission_items as $item) {
				$row = $this->concretes_model->getTruckByID($item->truck_id);
				$row->mission_type_id = $item->mission_type_id;
				$row->date = $this->bpas->hrsd($item->mission_date);
				$row->driver_id = $item->driver_id;
				$pr[$c] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->code . " - " . $row->plate, 'row' => $row, 'drivers' => $drivers, 'mission_types' => $mission_types);
				$c++;
			}
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['mission'] = $mission;
			$this->data['mission_items'] = json_encode($pr);
			$this->data['billers'] =  $this->site->getBillers();
			$this->session->set_userdata('remove_conmsls', 1);
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/missions'), 'page' => lang('missions')), array('link' => '#', 'page' => lang('edit_mission')));
			$meta = array('page_title' => lang('edit_mission'), 'bc' => $bc);
			$this->page_construct('concretes/edit_mission', $meta, $this->data);
		}
	}

	public function delete_mission($id = null)
	{
		$this->bpas->checkPermissions(NULL, true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->concretes_model->deleteMission($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("mission_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('mission_deleted'));
			admin_redirect('concretes/missions');
		}
	}
	public function mission_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_mission', true);
					foreach ($_POST['val'] as $id) {
						$this->concretes_model->deleteMission($id);
					}
					$this->session->set_flashdata('message', $this->lang->line("mission_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				} elseif ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('mission'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('total_expense'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
					$row = 2;
					foreach ($_POST['val'] as $id) {
						$mission = $this->concretes_model->getMissionByID($id);
						$created_by = $this->site->getUserByID($mission->created_by);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($mission->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $mission->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatDecimal($mission->total_expense));
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $created_by->last_name . ' ' . $created_by->first_name);
						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'missions_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function modal_view_mission($id = null)
	{
		$this->bpas->checkPermissions('missions', true);
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$mission = $this->concretes_model->getMissionByID($id);
		$this->data['biller'] = $this->site->getCompanyByID($mission->biller_id);
		$this->data['mission'] = $mission;
		$this->data['mission_items'] = $this->concretes_model->getMissionItems($id);;
		$this->data['created_by'] = $this->site->getUserByID($mission->created_by);
		$this->load->view($this->theme . 'concretes/modal_view_mission', $this->data);
	}

	public function fuel_expenses($biller_id = NULL)
	{
		$this->bpas->checkPermissions();
		if ($biller_id == 0) {
			$biller_id = null;
		}
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('fuel_expenses')));
		$meta = array('page_title' => lang('fuel_expenses'), 'bc' => $bc);
		$this->page_construct('concretes/fuel_expenses', $meta, $this->data);
	}

	public function getFuelExpenses($biller_id = NULL)
	{
		$this->bpas->checkPermissions('fuel_expenses');

		$payments_link = anchor('admin/concretes/fuel_expense_payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$add_payment_link = anchor('admin/concretes/add_fuel_expense_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$edit_link = anchor('admin/concretes/edit_fuel_expense/$1', '<i class="fa fa-edit"></i> ' . lang('edit_fuel_expense'), ' class="edit_fuel_expense" ');
		$delete_link = "<a href='#' class='po delete_fuel_expense' title='<b>" . $this->lang->line("delete_fuel_expense") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('concretes/delete_fuel_expense/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_fuel_expense') . "</a>";
		$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $payments_link . '</li>
						<li>' . $add_payment_link . '</li>
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
		$this->load->library('datatables');
		$this->datatables->select("
						con_fuel_expenses.id as id,
						DATE_FORMAT(date, '%Y-%m-%d %T') as date,
						DATE_FORMAT(from_date, '%Y-%m-%d %T') as from_date,
						DATE_FORMAT(to_date, '%Y-%m-%d %T') as to_date,
						con_fuel_expenses.reference_no,
						IFNULL(grand_total,0) as grand_total,
						IFNULL(paid,0) as paid,
						IFNULL(balance,0) as balance,
						con_fuel_expenses.payment_status,
						con_fuel_expenses.attachment
					")
			->from("con_fuel_expenses");
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('con_fuel_expenses.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('con_fuel_expenses.biller_id', $this->session->userdata('biller_id'));
		}
		if ($biller_id) {
			$this->datatables->where('con_fuel_expenses.biller_id', $biller_id);
		}
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
	}


	public function add_fuel_expense()
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
		if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->bpas->GP['concretes-fuel_expenses-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$note = $this->input->post('note');
			$from_date = $this->bpas->fld(trim($this->input->post('from_date')));
			$to_date = $this->bpas->fld(trim($this->input->post('to_date')));
			$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cfe', $biller_id);
			$i = isset($_POST['used_fuel_ids']) ? sizeof($_POST['used_fuel_ids']) : 0;
			$grand_total = 0;
			for ($r = 0; $r < $i; $r++) {
				$used_fuel_ids = $_POST['used_fuel_ids'][$r];
				$truck_id = $_POST['truck_id'][$r];
				$driver_id = $_POST['driver_id'][$r];
				$in_range_litre = $_POST['in_range_litre'][$r];
				$out_range_litre = $_POST['out_range_litre'][$r];
				$pump_litre = $_POST['pump_litre'][$r];
				$waiting_litre = $_POST['waiting_litre'][$r];
				$moving_litre = $_POST['moving_litre'][$r];
				$mission_litre = $_POST['mission_litre'][$r];
				$total_used = $_POST['total_used'][$r];
				$fuel_litre = $_POST['fuel_litre'][$r];
				$balance = $_POST['balance'][$r];
				$fuel_price = $_POST['fuel_price'][$r];
				$over_amount = $_POST['over_amount'][$r];
				$subtotal = $_POST['subtotal'][$r];

				$items[] = array(
					'used_fuel_ids' => $used_fuel_ids,
					'truck_id' => $truck_id,
					'driver_id' => $driver_id,
					'in_range_litre' => $in_range_litre,
					'out_range_litre' => $out_range_litre,
					'pump_litre' => $pump_litre,
					'waiting_litre' => $waiting_litre,
					'moving_litre' => $moving_litre,
					'mission_litre' => $mission_litre,
					'total_used' => $total_used,
					'fuel_litre' => $fuel_litre,
					'balance' => $balance,
					'fuel_price' => $fuel_price,
					'over_amount' => $over_amount,
					'subtotal' => $subtotal,
				);
				$grand_total += $subtotal;
			}
			if (!$items) {
				$this->form_validation->set_rules('driver', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
				'reference_no' => $reference,
				'biller_id' => $biller_id,
				'project_id' => $project_id,
				'from_date' => $from_date,
				'to_date' => $to_date,
				'note' => $note,
				'grand_total' => $grand_total,
				'paid' => 0,
				'balance' => $grand_total,
				'payment_status' => "pending",
				'created_by' => $this->session->userdata('user_id'),
			);
			//=====accountig=====//
			if ($this->Settings->accounting == 1) {
				$expenseAcc = $this->site->getAccountSettingByBiller($biller_id);
				$accTrans[] = array(
					'tran_type' => 'Fuel Expense',
					'tran_date' => $date,
					'reference_no' => $reference,
					'account_code' => $expenseAcc->fuel_expense_acc,
					'amount' => $grand_total,
					'narrative' => "Fuel Expense",
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
				$accTrans[] = array(
					'tran_type' => 'Fuel Expense',
					'tran_date' => $date,
					'reference_no' => $reference,
					'account_code' => $expenseAcc->ap_acc,
					'amount' => $grand_total * (-1),
					'narrative' => "Fuel Expense",
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
			}
			//=====end accountig=====//
			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$attachment = $this->upload->file_name;
				$data['attachment'] = $attachment;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->addFuelExpense($data, $items, $accTrans)) {
			$this->session->set_flashdata('message', $this->lang->line("fuel_expense_added"));
			admin_redirect('concretes/fuel_expenses');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/fuel_expenses'), 'page' => lang('fuel_expenses')), array('link' => '#', 'page' => lang('add_fuel_expense')));
			$meta = array('page_title' => lang('add_fuel_expense'), 'bc' => $bc);
			$this->page_construct('concretes/add_fuel_expense', $meta, $this->data);
		}
	}


	public function edit_fuel_expense($id = false)
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
		if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->bpas->GP['concretes-fuel_expenses-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$note = $this->input->post('note');
			$from_date = $this->bpas->fld(trim($this->input->post('from_date')));
			$to_date = $this->bpas->fld(trim($this->input->post('to_date')));
			$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cfe', $biller_id);
			$i = isset($_POST['used_fuel_ids']) ? sizeof($_POST['used_fuel_ids']) : 0;
			$grand_total = 0;
			for ($r = 0; $r < $i; $r++) {
				$used_fuel_ids = $_POST['used_fuel_ids'][$r];
				$truck_id = $_POST['truck_id'][$r];
				$driver_id = $_POST['driver_id'][$r];
				$in_range_litre = $_POST['in_range_litre'][$r];
				$out_range_litre = $_POST['out_range_litre'][$r];
				$pump_litre = $_POST['pump_litre'][$r];
				$waiting_litre = $_POST['waiting_litre'][$r];
				$moving_litre = $_POST['moving_litre'][$r];
				$mission_litre = $_POST['mission_litre'][$r];
				$total_used = $_POST['total_used'][$r];
				$fuel_litre = $_POST['fuel_litre'][$r];
				$balance = $_POST['balance'][$r];
				$fuel_price = $_POST['fuel_price'][$r];
				$over_amount = $_POST['over_amount'][$r];
				$subtotal = $_POST['subtotal'][$r];

				$items[] = array(
					'fuel_expense_id' => $id,
					'used_fuel_ids' => $used_fuel_ids,
					'truck_id' => $truck_id,
					'driver_id' => $driver_id,
					'in_range_litre' => $in_range_litre,
					'out_range_litre' => $out_range_litre,
					'pump_litre' => $pump_litre,
					'waiting_litre' => $waiting_litre,
					'moving_litre' => $moving_litre,
					'mission_litre' => $mission_litre,
					'total_used' => $total_used,
					'fuel_litre' => $fuel_litre,
					'balance' => $balance,
					'fuel_price' => $fuel_price,
					'over_amount' => $over_amount,
					'subtotal' => $subtotal,
				);
				$grand_total += $subtotal;
			}
			if (!$items) {
				$this->form_validation->set_rules('driver', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
				'reference_no' => $reference,
				'biller_id' => $biller_id,
				'project_id' => $project_id,
				'from_date' => $from_date,
				'to_date' => $to_date,
				'note' => $note,
				'grand_total' => $grand_total,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
			);

			//=====accountig=====//
			if ($this->Settings->accounting == 1) {
				$expenseAcc = $this->site->getAccountSettingByBiller($biller_id);
				$accTrans[] = array(
					'tran_no' => $id,
					'tran_type' => 'Fuel Expense',
					'tran_date' => $date,
					'reference_no' => $reference,
					'account_code' => $expenseAcc->fuel_expense_acc,
					'amount' => $grand_total,
					'narrative' => "Fuel Expense",
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
				$accTrans[] = array(
					'tran_no' => $id,
					'tran_type' => 'Fuel Expense',
					'tran_date' => $date,
					'reference_no' => $reference,
					'account_code' => $expenseAcc->ap_acc,
					'amount' => $grand_total * (-1),
					'narrative' => "Fuel Expense",
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
			}
			//=====end accountig=====//
			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$attachment = $this->upload->file_name;
				$data['attachment'] = $attachment;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->updateFuelExpense($id, $data, $items, $accTrans)) {
			$this->session->set_flashdata('message', $this->lang->line("fuel_expense_edited"));
			admin_redirect('concretes/fuel_expenses');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['fuel_expense'] = $this->concretes_model->getFuelExpenseByID($id);
			$this->data['fuel_expense_items'] = $this->concretes_model->getFuelExpenseItems($id);
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/fuel_expenses'), 'page' => lang('fuel_expenses')), array('link' => '#', 'page' => lang('edit_fuel_expense')));
			$meta = array('page_title' => lang('edit_fuel_expense'), 'bc' => $bc);
			$this->page_construct('concretes/edit_fuel_expense', $meta, $this->data);
		}
	}

	public function delete_fuel_expense($id = null)
	{
		$this->bpas->checkPermissions(NULL, true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->concretes_model->deleteFuelExpense($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("fuel_expense_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('fuel_expense_deleted'));
			admin_redirect('concretes/fuel_expenses');
		}
	}

	public function fuel_expense_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_fuel_expense', true);
					foreach ($_POST['val'] as $id) {
						$this->concretes_model->deleteFuelExpense($id);
					}
					$this->session->set_flashdata('message', $this->lang->line("fuel_expense_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				} elseif ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('fuel_expense'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('from_date'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('to_date'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('payment_status'));

					$this->db->select("
								con_fuel_expenses.id as id,
								DATE_FORMAT(date, '%Y-%m-%d %T') as date,
								DATE_FORMAT(from_date, '%Y-%m-%d %T') as from_date,
								DATE_FORMAT(to_date, '%Y-%m-%d %T') as to_date,
								con_fuel_expenses.reference_no,
								IFNULL(grand_total,0) as grand_total,
								IFNULL(paid,0) as paid,
								IFNULL(balance,0) as balance,
								con_fuel_expenses.payment_status,
							")
						->from("con_fuel_expenses");
					$this->db->where_in("con_fuel_expenses.id", $_POST['val']);
					$q = $this->db->get();
					$row = 2;
					if ($q->num_rows() > 0) {
						foreach (($q->result()) as $fuel_expense) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($fuel_expense->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($fuel_expense->from_date));
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->hrld($fuel_expense->to_date));
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $fuel_expense->reference_no);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($fuel_expense->grand_total));
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($fuel_expense->paid));
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($fuel_expense->balance));
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($fuel_expense->payment_status));
							$row++;
						}
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'fuel_expenses_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function modal_view_fuel_expense($id = null)
	{
		$this->bpas->checkPermissions('fuel_expenses', true);
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$fuel_expense = $this->concretes_model->getFuelExpenseByID($id);
		$this->data['biller'] = $this->site->getCompanyByID($fuel_expense->biller_id);
		$this->data['fuel_expense'] = $fuel_expense;
		$this->data['fuel_expense_items'] = $this->concretes_model->getFuelExpenseItems($id);;
		$this->data['created_by'] = $this->site->getUserByID($fuel_expense->created_by);
		$this->load->view($this->theme . 'concretes/modal_view_fuel_expense', $this->data);
	}

	public function get_used_fuels()
	{
		$biller_id = $this->input->get('biller_id');
		$project_id = $this->input->get('project_id');
		$from_date = $this->bpas->fld(trim($this->input->get('from_date')));
		$to_date = $this->bpas->fld(trim($this->input->get('to_date')));
		$fuel_expense_id = $this->input->get('fuel_expense_id') ? $this->input->get('fuel_expense_id') : false;
		$biller = $this->site->getCompanyByID($biller_id);
		$used_fuels = $this->concretes_model->getDriverUsedFuels($biller_id, $project_id, $from_date, $to_date, $fuel_expense_id);
		echo json_encode(array('used_fuels' => $used_fuels, 'fuel_price' => $biller->fuel_price));
	}


	public function fuel_expense_payments($id = false)
	{
		$this->bpas->checkPermissions("fuel_expense_payments", true);
		$this->data['payments'] = $this->concretes_model->getPaymentByFuelExpense($id);
		$this->load->view($this->theme . 'concretes/fuel_expense_payments', $this->data);
	}

	public function add_fuel_expense_payment($id = false)
	{
		$this->bpas->checkPermissions('fuel_expense_payments', true);
		$this->bpas->checkPermissions('add_fuel_expense', true);
		$this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
		$this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
		$this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		$fuel_expense = $this->concretes_model->getFuelExpenseByID($id);
		if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->bpas->GP['concretes-fuel_expenses-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$currencies = array();
			$camounts = $this->input->post("c_amount");
			if (!empty($camounts)) {
				foreach ($camounts as $key => $camount) {
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$currencies[] = array(
						"amount" => $camounts[$key],
						"currency" => $currency[$key],
						"rate" => $rate[$key],
					);
				}
			}
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay', $fuel_expense->biller_id);
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			$payment = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'transaction' => "Fuel Expense",
				'transaction_id' => $id,
				'amount' => $this->input->post('amount-paid'),
				'paid_by' => $this->input->post('paid_by'),
				'note' => $this->bpas->clear_tags($this->input->post('note')),
				'created_by' => $this->session->userdata('user_id'),
				'type' => "expense",
				'account_code' => $paying_from,
				'currencies' => json_encode($currencies),
			);

			//=====accountig=====//
			if ($this->Settings->accounting == 1) {
				$paymentAcc = $this->site->getAccountSettingByBiller($fuel_expense->biller_id);
				$accTrans[] = array(
					'tran_type' => 'Payment',
					'tran_date' => $date,
					'reference_no' => $reference_no,
					'account_code' => $paymentAcc->ap_acc,
					'amount' => $this->input->post('amount-paid'),
					'narrative' => "Fuel Expense Payment " . $fuel_expense->reference_no,
					'description' => $this->input->post('note'),
					'biller_id' => $fuel_expense->biller_id,
					'project_id' => $fuel_expense->project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
				$accTrans[] = array(
					'tran_type' => 'Payment',
					'tran_date' => $date,
					'reference_no' => $reference_no,
					'account_code' => $paying_from,
					'amount' => $this->input->post('amount-paid') * (-1),
					'narrative' => "Fuel Expense Payment " . $fuel_expense->reference_no,
					'description' => $this->input->post('note'),
					'biller_id' => $fuel_expense->biller_id,
					'project_id' => $fuel_expense->project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
			}
			//=====end accountig=====//
			if ($_FILES['userfile']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload()) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$payment['attachment'] = $photo;
			}
		} elseif ($this->input->post('add_payment')) {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($this->form_validation->run() == true && $this->concretes_model->addFuelExpensePayment($payment, $accTrans)) {
			$this->session->set_flashdata('message', lang("payment_added"));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['fuel_expense'] = $fuel_expense;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->load->view($this->theme . 'concretes/add_fuel_expense_payment', $this->data);
		}
	}

	public function edit_fuel_expense_payment($id = false)
	{
		$this->bpas->checkPermissions('fuel_expense_payments', true);
		$this->bpas->checkPermissions('edit_fuel_expense', true);
		$this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
		$this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
		$this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		$payment = $this->concretes_model->getPaymentByID($id);
		$fuel_expense = $this->concretes_model->getFuelExpenseByID($payment->transaction_id);
		if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->bpas->GP['concretes-fuel_expenses-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$currencies = array();
			$camounts = $this->input->post("c_amount");
			if (!empty($camounts)) {
				foreach ($camounts as $key => $camount) {
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$currencies[] = array(
						"amount" => $camounts[$key],
						"currency" => $currency[$key],
						"rate" => $rate[$key],
					);
				}
			}
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay', $fuel_expense->biller_id);
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			$payment = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'transaction_id' => $fuel_expense->id,
				'amount' => $this->input->post('amount-paid'),
				'paid_by' => $this->input->post('paid_by'),
				'note' => $this->bpas->clear_tags($this->input->post('note')),
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
				'account_code' => $paying_from,
				'currencies' => json_encode($currencies),
			);

			//=====accountig=====//
			if ($this->Settings->accounting == 1) {
				$paymentAcc = $this->site->getAccountSettingByBiller($fuel_expense->biller_id);
				$accTrans[] = array(
					'tran_no' => $id,
					'tran_type' => 'Payment',
					'tran_date' => $date,
					'reference_no' => $reference_no,
					'account_code' => $paymentAcc->ap_acc,
					'amount' => $this->input->post('amount-paid'),
					'narrative' => "Fuel Expense Payment " . $fuel_expense->reference_no,
					'description' => $this->input->post('note'),
					'biller_id' => $fuel_expense->biller_id,
					'project_id' => $fuel_expense->project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
				$accTrans[] = array(
					'tran_no' => $id,
					'tran_type' => 'Payment',
					'tran_date' => $date,
					'reference_no' => $reference_no,
					'account_code' => $paying_from,
					'amount' => $this->input->post('amount-paid') * (-1),
					'narrative' => "Fuel Expense Payment " . $fuel_expense->reference_no,
					'description' => $this->input->post('note'),
					'biller_id' => $fuel_expense->biller_id,
					'project_id' => $fuel_expense->project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
			}
			//=====end accountig=====//
			if ($_FILES['userfile']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload()) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$payment['attachment'] = $photo;
			}
		} elseif ($this->input->post('edit_payment')) {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($this->form_validation->run() == true && $this->concretes_model->updateFuelExpensePayment($id, $payment, $accTrans)) {
			$this->session->set_flashdata('message', lang("payment_edited"));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['fuel_expense'] = $fuel_expense;
			$this->data['payment'] = $payment;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->load->view($this->theme . 'concretes/edit_fuel_expense_payment', $this->data);
		}
	}

	public function delete_fuel_expense_payment($id = null)
	{
		$this->bpas->checkPermissions('fuel_expense_payments', true);
		$this->bpas->checkPermissions('delete_fuel_expense', true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->concretes_model->deleteFuelExpensePayment($id)) {
			$this->session->set_flashdata('message', lang("payment_deleted"));
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function fuel_expense_payment_note($id = null)
	{
		$this->bpas->checkPermissions('fuel_expense_payments', true);
		$payment = $this->concretes_model->getPaymentByID($id);
		$fuel_expense = $this->concretes_model->getFuelExpenseByID($payment->transaction_id);
		$this->data['fuel_expense'] = $fuel_expense;
		$this->data['biller'] = $this->site->getCompanyByID($fuel_expense->biller_id);
		$this->data['payment'] = $payment;
		$this->data['created_by'] = $this->site->getUserByID($payment->created_by);
		$this->data['page_title'] = $this->lang->line("payment_note");
		$this->load->view($this->theme . 'concretes/fuel_expense_payment_note', $this->data);
	}


	public function moving_waitings_report()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['trucks'] = $this->concretes_model->getTrucks("truck");
		$this->data['pumps'] = $this->concretes_model->getTrucks("pump");
		$this->data['drivers'] =  $this->site->getAllCompanies('driver');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('moving_waitings_report')));
		$meta = array('page_title' => lang('moving_waitings_report'), 'bc' => $bc);
		$this->page_construct('concretes/moving_waitings_report', $meta, $this->data);
	}
	public function getMovingWaitingsReport($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('moving_waitings_report');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$pump = $this->input->get('pump') ? $this->input->get('pump') : NULL;
		$truck = $this->input->get('truck') ? $this->input->get('truck') : NULL;
		$driver = $this->input->get('driver') ? $this->input->get('driver') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(" . $this->db->dbprefix('con_moving_waitings') . ".date, '%Y-%m-%d %T') as date,
								con_moving_waitings.reference_no,
								con_trucks.type,
								con_moving_waiting_items.truck_code,
								con_moving_waiting_items.driver_name,
								IF(" . $this->db->dbprefix('con_trucks') . ".type = 'pump'," . $this->db->dbprefix('con_moving_waiting_items') . ".times_hours,'') as moving,
								IF(" . $this->db->dbprefix('con_trucks') . ".type = 'truck'," . $this->db->dbprefix('con_moving_waiting_items') . ".times_hours,'') as waiting,
								con_moving_waitings.id as id")
				->from("con_moving_waitings")
				->join("con_moving_waiting_items", "con_moving_waitings.id = con_moving_waiting_items.moving_waiting_id", "inner")
				->join("con_trucks", "con_trucks.id = con_moving_waiting_items.truck_id", "left")
				->group_by("con_moving_waiting_items.id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_moving_waitings.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_moving_waitings.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->db->where('con_moving_waitings.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_moving_waitings.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_moving_waitings.biller_id', $biller);
			}
			if ($pump) {
				$this->db->where('con_moving_waiting_items.truck_id', $pump);
			}
			if ($truck) {
				$this->db->where('con_moving_waiting_items.truck_id', $truck);
			}
			if ($driver) {
				$this->db->where('con_moving_waiting_items.driver_id', $driver);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('moving_waitings_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('type'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('truck'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('driver'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('moving'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('waiting'));

				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->type);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->truck_code);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->driver_name);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->moving);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->waiting);
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$filename = 'moving_waitings_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
									DATE_FORMAT(" . $this->db->dbprefix('con_moving_waitings') . ".date, '%Y-%m-%d %T') as date,
									con_moving_waitings.reference_no,
									con_trucks.type,
									con_moving_waiting_items.truck_code,
									con_moving_waiting_items.driver_name,
									IF(" . $this->db->dbprefix('con_trucks') . ".type = 'pump'," . $this->db->dbprefix('con_moving_waiting_items') . ".times_hours,'') as moving,
									IF(" . $this->db->dbprefix('con_trucks') . ".type = 'truck'," . $this->db->dbprefix('con_moving_waiting_items') . ".times_hours,'') as waiting,
									con_moving_waitings.id as id")
				->from("con_moving_waitings")
				->join("con_moving_waiting_items", "con_moving_waitings.id = con_moving_waiting_items.moving_waiting_id", "inner")
				->join("con_trucks", "con_trucks.id = con_moving_waiting_items.truck_id", "left")
				->group_by("con_moving_waiting_items.id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_moving_waitings.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_moving_waitings.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->datatables->where('con_moving_waitings.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_moving_waitings.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_moving_waitings.biller_id', $biller);
			}
			if ($pump) {
				$this->datatables->where('con_moving_waiting_items.truck_id', $pump);
			}
			if ($truck) {
				$this->datatables->where('con_moving_waiting_items.truck_id', $truck);
			}
			if ($driver) {
				$this->datatables->where('con_moving_waiting_items.driver_id', $driver);
			}
			echo $this->datatables->generate();
		}
	}

	public function missions_report()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['trucks'] = $this->concretes_model->getTrucks("truck");
		$this->data['pumps'] = $this->concretes_model->getTrucks("pump");
		$this->data['drivers'] =  $this->site->getAllCompanies('driver');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('missions_report')));
		$meta = array('page_title' => lang('missions_report'), 'bc' => $bc);
		$this->page_construct('concretes/missions_report', $meta, $this->data);
	}
	public function getMissionsReport($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('missions_report');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$pump = $this->input->get('pump') ? $this->input->get('pump') : NULL;
		$truck = $this->input->get('truck') ? $this->input->get('truck') : NULL;
		$driver = $this->input->get('driver') ? $this->input->get('driver') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(" . $this->db->dbprefix('con_missions') . ".date, '%Y-%m-%d %T') as date,
								con_missions.reference_no,
								con_trucks.type,
								con_mission_items.truck_code,
								con_mission_items.driver_name,
								con_mission_types.name as mission_type,
								IFNULL(" . $this->db->dbprefix('con_mission_items') . ".fuel,0) as fuel,
								IFNULL(" . $this->db->dbprefix('con_mission_items') . ".road_fee,0) as road_fee,
								IFNULL(" . $this->db->dbprefix('con_mission_items') . ".food_expense,0) as food_expense,
								IFNULL(" . $this->db->dbprefix('con_mission_items') . ".other_expense,0) as other_expense,
								IFNULL((" . $this->db->dbprefix('con_mission_items') . ".road_fee + " . $this->db->dbprefix('con_mission_items') . ".food_expense + " . $this->db->dbprefix('con_mission_items') . ".other_expense),0) as total_expense,
								con_missions.id as id")
				->from("con_missions")
				->join("con_mission_items", "con_missions.id = con_mission_items.mission_id", "inner")
				->join("con_trucks", "con_trucks.id = con_mission_items.truck_id", "left")
				->join("con_mission_types", "con_mission_types.id = con_mission_items.mission_type_id", "left")
				->group_by("con_mission_items.id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_missions.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_missions.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->db->where('con_missions.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_missions.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_missions.biller_id', $biller);
			}
			if ($pump) {
				$this->db->where('con_mission_items.truck_id', $pump);
			}
			if ($truck) {
				$this->db->where('con_mission_items.truck_id', $truck);
			}
			if ($driver) {
				$this->db->where('con_mission_items.driver_id', $driver);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('missions_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('type'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('truck'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('driver'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('fuel'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('road_fee'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('food_expense'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('other_expense'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('total_expense'));

				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->type);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->truck_code);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->driver_name);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->fuel);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->road_fee);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->food_expense);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->other_expense);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->total_expense);
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$filename = 'missions_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
									DATE_FORMAT(" . $this->db->dbprefix('con_missions') . ".date, '%Y-%m-%d %T') as date,
									con_missions.reference_no,
									con_trucks.type,
									con_mission_items.truck_code,
									con_mission_items.driver_name,
									con_mission_types.name as mission_type,
									IFNULL(" . $this->db->dbprefix('con_mission_items') . ".fuel,0) as fuel,
									IFNULL(" . $this->db->dbprefix('con_mission_items') . ".road_fee,0) as road_fee,
									IFNULL(" . $this->db->dbprefix('con_mission_items') . ".food_expense,0) as food_expense,
									IFNULL(" . $this->db->dbprefix('con_mission_items') . ".other_expense,0) as other_expense,
									IFNULL((" . $this->db->dbprefix('con_mission_items') . ".road_fee + " . $this->db->dbprefix('con_mission_items') . ".food_expense + " . $this->db->dbprefix('con_mission_items') . ".other_expense),0) as total_expense,
									con_missions.id as id")
				->from("con_missions")
				->join("con_mission_items", "con_missions.id = con_mission_items.mission_id", "inner")
				->join("con_trucks", "con_trucks.id = con_mission_items.truck_id", "left")
				->join("con_mission_types", "con_mission_types.id = con_mission_items.mission_type_id", "left")
				->group_by("con_mission_items.id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_missions.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_missions.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->datatables->where('con_missions.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_missions.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_missions.biller_id', $biller);
			}
			if ($pump) {
				$this->datatables->where('con_mission_items.truck_id', $pump);
			}
			if ($truck) {
				$this->datatables->where('con_mission_items.truck_id', $truck);
			}
			if ($driver) {
				$this->datatables->where('con_mission_items.driver_id', $driver);
			}
			echo $this->datatables->generate();
		}
	}


	public function fuel_expenses_report()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('fuel_expenses_report')));
		$meta = array('page_title' => lang('fuel_expenses_report'), 'bc' => $bc);
		$this->page_construct('concretes/fuel_expenses_report', $meta, $this->data);
	}
	public function getFuelExpensesReport($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('fuel_expenses_report');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(date, '%Y-%m-%d %T') as date,
								DATE_FORMAT(from_date, '%Y-%m-%d %T') as from_date,
								DATE_FORMAT(to_date, '%Y-%m-%d %T') as to_date,
								con_fuel_expenses.reference_no,
								IFNULL(grand_total,0) as grand_total,
								IFNULL(paid,0) as paid,
								IFNULL(balance,0) as balance,
								con_fuel_expenses.payment_status,
								con_fuel_expenses.id as id")
				->from("con_fuel_expenses");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_fuel_expenses.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_fuel_expenses.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->db->where('con_fuel_expenses.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_fuel_expenses.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_fuel_expenses.biller_id', $biller);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('fuel_expenses_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('from_date'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('to_date'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('payment_status'));

				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($data_row->from_date));
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->hrld($data_row->to_date));
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->grand_total);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->paid);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->balance);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($data_row->payment_status));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$filename = 'fuel_expenses_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
									DATE_FORMAT(date, '%Y-%m-%d %T') as date,
									DATE_FORMAT(from_date, '%Y-%m-%d %T') as from_date,
									DATE_FORMAT(to_date, '%Y-%m-%d %T') as to_date,
									con_fuel_expenses.reference_no,
									IFNULL(grand_total,0) as grand_total,
									IFNULL(paid,0) as paid,
									IFNULL(balance,0) as balance,
									con_fuel_expenses.payment_status,
									con_fuel_expenses.id as id")
				->from("con_fuel_expenses");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_fuel_expenses.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_fuel_expenses.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->datatables->where('con_fuel_expenses.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_fuel_expenses.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_fuel_expenses.biller_id', $biller);
			}
			echo $this->datatables->generate();
		}
	}


	public function fuel_expense_details_report()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['trucks'] = $this->concretes_model->getTrucks("truck");
		$this->data['pumps'] = $this->concretes_model->getTrucks("pump");
		$this->data['drivers'] =  $this->site->getAllCompanies('driver');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('fuel_expense_details_report')));
		$meta = array('page_title' => lang('fuel_expense_details_report'), 'bc' => $bc);
		$this->page_construct('concretes/fuel_expense_details_report', $meta, $this->data);
	}
	public function getFuelExpenseDetailsReport($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('fuel_expense_details_report');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$pump = $this->input->get('pump') ? $this->input->get('pump') : NULL;
		$truck = $this->input->get('truck') ? $this->input->get('truck') : NULL;
		$driver = $this->input->get('driver') ? $this->input->get('driver') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
							DATE_FORMAT(" . $this->db->dbprefix('con_fuel_expenses') . ".date, '%Y-%m-%d %T') as date,
							con_fuel_expenses.reference_no,
							con_trucks.type,
							con_trucks.code as truck_code,
							CONCAT(" . $this->db->dbprefix('con_drivers') . ".full_name_kh,' - '," . $this->db->dbprefix('con_drivers') . ".full_name) as driver_name,
							IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".in_range_litre, 0) as in_range_litre,
							IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".out_range_litre, 0) as out_range_litre,
							IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".pump_litre, 0) as pump_litre,
							IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".waiting_litre, 0) as waiting_litre,
							IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".moving_litre, 0) as moving_litre,
							IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".mission_litre, 0) as mission_litre,
							IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".total_used, 0) as total_used,
							IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".fuel_litre, 0) as fuel_litre,
							IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".balance, 0) as balance,
							IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".fuel_price, 0) as fuel_price,
							IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".subtotal, 0) as subtotal,
							con_fuel_expenses.id as id")
				->from("con_fuel_expenses")
				->join("con_fuel_expense_items", "con_fuel_expenses.id = con_fuel_expense_items.fuel_expense_id", "inner")
				->join("con_trucks", "con_trucks.id = con_fuel_expense_items.truck_id", "left")
				->join("con_drivers", "con_drivers.id = con_fuel_expense_items.driver_id", "left")
				->group_by("con_fuel_expense_items.id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_fuel_expenses.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_fuel_expenses.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->db->where('con_fuel_expenses.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_fuel_expenses.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_fuel_expenses.biller_id', $biller);
			}
			if ($pump) {
				$this->db->where('con_fuel_expense_items.truck_id', $pump);
			}
			if ($truck) {
				$this->db->where('con_fuel_expense_items.truck_id', $truck);
			}
			if ($driver) {
				$this->db->where('con_fuel_expense_items.driver_id', $driver);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('fuel_expense_details_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('type'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('truck'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('driver'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('in_range'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('out_range'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('pump'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('waiting'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('moving'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('mission'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('total_used'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('total_fuel'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('price'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('amount'));

				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->type);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->truck_code);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->driver_name);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->in_range_litre);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->out_range_litre);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->pump_litre);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->waiting_litre);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->moving_litre);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->mission_litre);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->total_used);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->fuel_litre);
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->balance);
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->fuel_price);
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->subtotal);
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
				$filename = 'fuel_expense_details_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
									DATE_FORMAT(" . $this->db->dbprefix('con_fuel_expenses') . ".date, '%Y-%m-%d %T') as date,
									con_fuel_expenses.reference_no,
									con_trucks.type,
									con_trucks.code as truck_code,
									CONCAT(" . $this->db->dbprefix('con_drivers') . ".full_name_kh,' - '," . $this->db->dbprefix('con_drivers') . ".full_name) as driver_name,
									IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".in_range_litre, 0) as in_range_litre,
									IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".out_range_litre, 0) as out_range_litre,
									IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".pump_litre, 0) as pump_litre,
									IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".waiting_litre, 0) as waiting_litre,
									IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".moving_litre, 0) as moving_litre,
									IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".mission_litre, 0) as mission_litre,
									IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".total_used, 0) as total_used,
									IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".fuel_litre, 0) as fuel_litre,
									IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".balance, 0) as balance,
									IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".fuel_price, 0) as fuel_price,
									IFNULL(" . $this->db->dbprefix('con_fuel_expense_items') . ".subtotal, 0) as subtotal,
									con_fuel_expenses.id as id")
				->from("con_fuel_expenses")
				->join("con_fuel_expense_items", "con_fuel_expenses.id = con_fuel_expense_items.fuel_expense_id", "inner")
				->join("con_trucks", "con_trucks.id = con_fuel_expense_items.truck_id", "left")
				->join("con_drivers", "con_drivers.id = con_fuel_expense_items.driver_id", "left")
				->group_by("con_fuel_expense_items.id");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_fuel_expenses.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_fuel_expenses.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->datatables->where('con_fuel_expenses.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_fuel_expenses.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_fuel_expenses.biller_id', $biller);
			}
			if ($pump) {
				$this->datatables->where('con_fuel_expense_items.truck_id', $pump);
			}
			if ($truck) {
				$this->datatables->where('con_fuel_expense_items.truck_id', $truck);
			}
			if ($driver) {
				$this->datatables->where('con_fuel_expense_items.driver_id', $driver);
			}
			echo $this->datatables->generate();
		}
	}

	public function commissions($biller_id = NULL)
	{
		$this->bpas->checkPermissions();
		if ($biller_id == 0) {
			$biller_id = null;
		}
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('commissions')));
		$meta = array('page_title' => lang('commissions'), 'bc' => $bc);
		$this->page_construct('concretes/commissions', $meta, $this->data);
	}

	public function getCommissions($biller_id = NULL)
	{
		$this->bpas->checkPermissions('commissions');
		$payments_link = anchor('admin/concretes/commission_payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$add_payment_link = anchor('admin/concretes/add_commission_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$edit_link = anchor('admin/concretes/edit_commission/$1', '<i class="fa fa-edit"></i> ' . lang('edit_commission'), ' class="edit_commission" ');
		$delete_link = "<a href='#' class='po delete_commission' title='<b>" . $this->lang->line("delete_commission") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('concretes/delete_commission/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_commission') . "</a>";
		$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $payments_link . '</li>
						<li>' . $add_payment_link . '</li>
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
		$this->load->library('datatables');
		$this->datatables->select("
						con_commissions.id as id,
						DATE_FORMAT(date, '%Y-%m-%d %T') as date,
						DATE_FORMAT(from_date, '%Y-%m-%d %T') as from_date,
						DATE_FORMAT(to_date, '%Y-%m-%d %T') as to_date,
						con_commissions.reference_no,
						con_commissions.commission_type,
						IFNULL(grand_total,0) as grand_total,
						IFNULL(paid,0) as paid,
						IFNULL(balance,0) as balance,
						con_commissions.payment_status,
						con_commissions.attachment
					")
			->from("con_commissions");
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('con_commissions.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('con_commissions.biller_id', $this->session->userdata('biller_id'));
		}
		if ($biller_id) {
			$this->datatables->where('con_commissions.biller_id', $biller_id);
		}
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
	}


	public function add_commission()
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
		if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->bpas->GP['concretes-fuel_expenses-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$note = $this->input->post('note');
			$commission_type = $this->input->post('commission_type');
			$from_date = $this->bpas->fld(trim($this->input->post('from_date')));
			$to_date = $this->bpas->fld(trim($this->input->post('to_date')));
			$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ccms', $biller_id);
			$i = isset($_POST['delivery_ids']) ? sizeof($_POST['delivery_ids']) : 0;
			$grand_total = 0;
			$commission_deliveries = false;

			for ($r = 0; $r < $i; $r++) {
				$delivery_ids = $_POST['delivery_ids'][$r];
				$officer_id = isset($_POST['officer_id'][$r]) ? $_POST['officer_id'][$r] : null;
				$driver_id = isset($_POST['driver_id'][$r]) ? $_POST['driver_id'][$r] : null;
				$quantity = isset($_POST['quantity'][$r]) ? $_POST['quantity'][$r] : null;
				$commission_rate = isset($_POST['commission_rate'][$r]) ? $_POST['commission_rate'][$r] : null;
				$total_commission = isset($_POST['total_commission'][$r]) ? $_POST['total_commission'][$r] : null;
				$normal_qty = isset($_POST['normal_qty'][$r]) ? $_POST['normal_qty'][$r] : null;
				$overtime_qty = isset($_POST['overtime_qty'][$r]) ? $_POST['overtime_qty'][$r] : null;
				$truck_commission_rate = isset($_POST['truck_commission_rate'][$r]) ? $_POST['truck_commission_rate'][$r] : null;
				$truck_commission_rate_ot = isset($_POST['truck_commission_rate_ot'][$r]) ? $_POST['truck_commission_rate_ot'][$r] : null;
				$normal_amount = isset($_POST['normal_amount'][$r]) ? $_POST['normal_amount'][$r] : null;
				$ot_amount = isset($_POST['ot_amount'][$r]) ? $_POST['ot_amount'][$r] : null;

				$items[] = array(
					'delivery_ids' => $delivery_ids,
					'officer_id' => $officer_id,
					'driver_id' => $driver_id,
					'quantity' => $quantity,
					'commission_rate' => $commission_rate,
					'total_commission' => $total_commission,
					'normal_qty' => $normal_qty,
					'overtime_qty' => $overtime_qty,
					'truck_commission_rate' => $truck_commission_rate,
					'truck_commission_rate_ot' => $truck_commission_rate_ot,
					'normal_amount' => $normal_amount,
					'ot_amount' => $ot_amount
				);

				$deliveries = explode(",", $delivery_ids);
				foreach ($deliveries as $delivery) {
					$commission_deliveries[] = array(
						"delivery_id" => $delivery,
						"driver_id" => $driver_id,
						"officer_id" => $officer_id,
					);
				}
				$grand_total += $total_commission;
			}
			if (!$items) {
				$this->form_validation->set_rules('driver', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
				'reference_no' => $reference,
				'biller_id' => $biller_id,
				'project_id' => $project_id,
				'commission_type' => $commission_type,
				'from_date' => $from_date,
				'to_date' => $to_date,
				'note' => $note,
				'grand_total' => $grand_total,
				'paid' => 0,
				'balance' => $grand_total,
				'payment_status' => "pending",
				'created_by' => $this->session->userdata('user_id'),
			);

			//=====accountig=====//
			if ($this->Settings->accounting == 1) {
				$expenseAcc = $this->site->getAccountSettingByBiller($biller_id);
				$accTrans[] = array(
					'tran_type' => 'CCommission',
					'tran_date' => $date,
					'reference_no' => $reference,
					'account_code' => $expenseAcc->saleman_commission_acc,
					'amount' => $grand_total,
					'narrative' => "Commission to " . $commission_type,
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
				$accTrans[] = array(
					'tran_type' => 'CCommission',
					'tran_date' => $date,
					'reference_no' => $reference,
					'account_code' => $expenseAcc->ap_acc,
					'amount' => $grand_total * (-1),
					'narrative' => "Commission to " . $commission_type,
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
			}
			//=====end accountig=====//

			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$attachment = $this->upload->file_name;
				$data['attachment'] = $attachment;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->addCommission($data, $items, $commission_deliveries, $accTrans)) {
			$this->session->set_flashdata('message', $this->lang->line("commission_added"));
			admin_redirect('concretes/commissions');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/commissions'), 'page' => lang('commissions')), array('link' => '#', 'page' => lang('add_commission')));
			$meta = array('page_title' => lang('add_commission'), 'bc' => $bc);
			$this->page_construct('concretes/add_commission', $meta, $this->data);
		}
	}


	public function edit_commission($id = false)
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
		if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->bpas->GP['concretes-fuel_expenses-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$note = $this->input->post('note');
			$commission_type = $this->input->post('commission_type');
			$from_date = $this->bpas->fld(trim($this->input->post('from_date')));
			$to_date = $this->bpas->fld(trim($this->input->post('to_date')));
			$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ccms', $biller_id);
			$i = isset($_POST['delivery_ids']) ? sizeof($_POST['delivery_ids']) : 0;
			$grand_total = 0;
			$commission_deliveries = false;

			for ($r = 0; $r < $i; $r++) {
				$delivery_ids = $_POST['delivery_ids'][$r];
				$officer_id = isset($_POST['officer_id'][$r]) ? $_POST['officer_id'][$r] : null;
				$driver_id = isset($_POST['driver_id'][$r]) ? $_POST['driver_id'][$r] : null;
				$quantity = isset($_POST['quantity'][$r]) ? $_POST['quantity'][$r] : null;
				$commission_rate = isset($_POST['commission_rate'][$r]) ? $_POST['commission_rate'][$r] : null;
				$total_commission = isset($_POST['total_commission'][$r]) ? $_POST['total_commission'][$r] : null;
				$normal_qty = isset($_POST['normal_qty'][$r]) ? $_POST['normal_qty'][$r] : null;
				$overtime_qty = isset($_POST['overtime_qty'][$r]) ? $_POST['overtime_qty'][$r] : null;
				$truck_commission_rate = isset($_POST['truck_commission_rate'][$r]) ? $_POST['truck_commission_rate'][$r] : null;
				$truck_commission_rate_ot = isset($_POST['truck_commission_rate_ot'][$r]) ? $_POST['truck_commission_rate_ot'][$r] : null;
				$normal_amount = isset($_POST['normal_amount'][$r]) ? $_POST['normal_amount'][$r] : null;
				$ot_amount = isset($_POST['ot_amount'][$r]) ? $_POST['ot_amount'][$r] : null;

				$items[] = array(
					'commission_id' => $id,
					'delivery_ids' => $delivery_ids,
					'officer_id' => $officer_id,
					'driver_id' => $driver_id,
					'quantity' => $quantity,
					'commission_rate' => $commission_rate,
					'total_commission' => $total_commission,
					'normal_qty' => $normal_qty,
					'overtime_qty' => $overtime_qty,
					'truck_commission_rate' => $truck_commission_rate,
					'truck_commission_rate_ot' => $truck_commission_rate_ot,
					'normal_amount' => $normal_amount,
					'ot_amount' => $ot_amount
				);

				$deliveries = explode(",", $delivery_ids);
				foreach ($deliveries as $delivery) {
					$commission_deliveries[] = array(
						"commission_id" => $id,
						"delivery_id" => $delivery,
						"driver_id" => $driver_id,
						"officer_id" => $officer_id,
					);
				}
				$grand_total += $total_commission;
			}
			if (!$items) {
				$this->form_validation->set_rules('driver', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
				'reference_no' => $reference,
				'biller_id' => $biller_id,
				'project_id' => $project_id,
				'commission_type' => $commission_type,
				'from_date' => $from_date,
				'to_date' => $to_date,
				'note' => $note,
				'grand_total' => $grand_total,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
			);

			//=====accountig=====//
			if ($this->Settings->accounting == 1) {
				$expenseAcc = $this->site->getAccountSettingByBiller($biller_id);
				$accTrans[] = array(
					'tran_no' => $id,
					'tran_type' => 'CCommission',
					'tran_date' => $date,
					'reference_no' => $reference,
					'account_code' => $expenseAcc->saleman_commission_acc,
					'amount' => $grand_total,
					'narrative' => "Commission to " . $commission_type,
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
				$accTrans[] = array(
					'tran_no' => $id,
					'tran_type' => 'CCommission',
					'tran_date' => $date,
					'reference_no' => $reference,
					'account_code' => $expenseAcc->ap_acc,
					'amount' => $grand_total * (-1),
					'narrative' => "Commission to " . $commission_type,
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
			}
			//=====end accountig=====//
			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$attachment = $this->upload->file_name;
				$data['attachment'] = $attachment;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->updateCommission($id, $data, $items, $commission_deliveries, $accTrans)) {
			$this->session->set_flashdata('message', $this->lang->line("commission_edited"));
			admin_redirect('concretes/commissions');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['commission'] = $this->concretes_model->getCommissionByID($id);
			$this->data['commission_items'] = $this->concretes_model->getCommissionItems($id);
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/commissions'), 'page' => lang('commissions')), array('link' => '#', 'page' => lang('edit_commission')));
			$meta = array('page_title' => lang('edit_commission'), 'bc' => $bc);
			$this->page_construct('concretes/edit_commission', $meta, $this->data);
		}
	}

	public function delete_commission($id = null)
	{
		$this->bpas->checkPermissions(NULL, true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->concretes_model->deleteCommission($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("commission_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('commission_deleted'));
			admin_redirect('concretes/commissions');
		}
	}

	public function modal_view_commission($id = null)
	{
		$this->bpas->checkPermissions('commissions', true);
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$commission = $this->concretes_model->getCommissionByID($id);
		$this->data['biller'] = $this->site->getCompanyByID($commission->biller_id);
		$this->data['commission'] = $commission;
		$this->data['commission_items'] = $this->concretes_model->getCommissionItems($id);;
		$this->data['created_by'] = $this->site->getUserByID($commission->created_by);
		$this->load->view($this->theme . 'concretes/modal_view_commission', $this->data);
	}

	public function commission_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_commission', true);
					foreach ($_POST['val'] as $id) {
						$this->concretes_model->deleteCommission($id);
					}
					$this->session->set_flashdata('message', $this->lang->line("commission_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				} elseif ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('commission'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('from_date'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('to_date'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('type'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));

					$this->db->select("
							DATE_FORMAT(date, '%Y-%m-%d %T') as date,
							DATE_FORMAT(from_date, '%Y-%m-%d %T') as from_date,
							DATE_FORMAT(to_date, '%Y-%m-%d %T') as to_date,
							con_commissions.reference_no,
							con_commissions.commission_type,
							IFNULL(grand_total,0) as grand_total,
							IFNULL(paid,0) as paid,
							IFNULL(balance,0) as balance,
							con_commissions.payment_status
						")
						->from("con_commissions");
					$this->db->where_in("con_commissions.id", $_POST['val']);
					$q = $this->db->get();
					$row = 2;
					if ($q->num_rows() > 0) {
						foreach (($q->result()) as $commission) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($commission->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($commission->from_date));
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->hrld($commission->to_date));
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $commission->reference_no);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($commission->commission_type));
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($commission->grand_total));
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($commission->paid));
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($commission->balance));
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($commission->payment_status));
							$row++;
						}
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'commissions_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function get_commissions()
	{
		$biller_id = $this->input->get('biller_id');
		$project_id = $this->input->get('project_id');
		$from_date = $this->bpas->fld(trim($this->input->get('from_date')));
		$to_date = $this->bpas->fld(trim($this->input->get('to_date')));
		$commission_type = $this->input->get('commission_type');
		$commission_id = $this->input->get('commission_id') ? $this->input->get('commission_id') : false;
		$biller = $this->site->getCompanyByID($biller_id);
		$commission_deliveries = $this->concretes_model->getCommissionDeliveries($commission_type, $commission_id);
		$absents = $this->concretes_model->getArrayAbsents($biller_id, $project_id, $from_date, $to_date);
		$officer_commissions = false;
		$pump_commissions = false;
		$truck_commissions = false;
		if ($commission_type == "officer") {
			$officers = $this->concretes_model->getOfficerByBiller($biller_id);
			$deliveries = $this->concretes_model->getConDeliveries($biller_id, $project_id, $from_date, $to_date, false);
			if ($officers && $deliveries) {
				foreach ($officers as $officer) {
					foreach ($deliveries as $delivery) {
						if (!isset($commission_deliveries[$delivery->id][$officer->id]) && $delivery->date >= $officer->start_date && !isset($absents['officer'][$officer->id][$delivery->date])) {
							if (isset($officer_commissions[$officer->id])) {
								$delivery_ids = $officer_commissions[$officer->id]['delivery_ids'] . ',' . $delivery->id;
								$quantity = $officer_commissions[$officer->id]['quantity'] + $delivery->quantity;
							} else {
								$delivery_ids = $delivery->id;
								$quantity = $delivery->quantity;
							}
							$row = array(
								"delivery_ids" => $delivery_ids,
								"quantity" => $quantity,
								"commission_rate" => $officer->commission_rate,
								"officer_id" => $officer->id,
								"full_name_kh" => $officer->full_name_kh,
								"full_name" => $officer->full_name,
							);
							$officer_commissions[$officer->id] = $row;
						}
					}
				}
			}
		} else if ($commission_type == "pump") {
			$driver_infos = $this->concretes_model->getArrayDrivers();
			$deliveries = $this->concretes_model->getConDeliveries($biller_id, $project_id, $from_date, $to_date, 'pump');
			if ($deliveries) {
				foreach ($deliveries as $delivery) {
					if (!isset($commission_deliveries[$delivery->id][$delivery->pump_driver_id]) && !isset($absents['driver'][$delivery->pump_driver_id][$delivery->date])) {
						if (isset($pump_commissions['driver'][$delivery->pump_driver_id])) {
							$delivery_ids = $pump_commissions['driver'][$delivery->pump_driver_id]['delivery_ids'] . ',' . $delivery->id;
							$quantity = $pump_commissions['driver'][$delivery->pump_driver_id]['quantity'] + $delivery->quantity;
						} else {
							$delivery_ids = $delivery->id;
							$quantity = $delivery->quantity;
						}
						$row = array(
							"delivery_ids" => $delivery_ids,
							"quantity" => $quantity,
							"commission_rate" => $biller->pump_commission_rate,
							"pump_driver_id" => $delivery->pump_driver_id,
							"pump_driver_name" => $delivery->pump_driver_name,
						);
						$pump_commissions['driver'][$delivery->pump_driver_id] = $row;
					}
					if ($biller->pump_commission_rate_assistant > 0 && $delivery->driver_assistant) {
						$driver_assistants = json_decode($delivery->driver_assistant);
						foreach ($driver_assistants as $driver_assistant) {
							if (!isset($commission_deliveries[$delivery->id][$driver_assistant]) && !isset($absents['driver'][$driver_assistant][$delivery->date])) {
								if (isset($pump_commissions['assistant'][$driver_assistant])) {
									$delivery_ids = $pump_commissions['assistant'][$driver_assistant]['delivery_ids'] . ',' . $delivery->id;
									$quantity = $pump_commissions['assistant'][$driver_assistant]['quantity'] + $delivery->quantity;
								} else {
									$delivery_ids = $delivery->id;
									$quantity = $delivery->quantity;
								}
								$row = array(
									"delivery_ids" => $delivery_ids,
									"quantity" => $quantity,
									"commission_rate" => $biller->pump_commission_rate_assistant,
									"pump_driver_id" => $driver_assistant,
									"pump_driver_name" => $driver_infos[$driver_assistant]->full_name_kh . ' - ' . $driver_infos[$driver_assistant]->full_name,
								);
								$pump_commissions['assistant'][$driver_assistant] = $row;
							}
						}
					}
				}
			}
		} else if ($commission_type == "truck") {
			$trucks_infos = $this->concretes_model->getArrayTrucks();
			$deliveries = $this->concretes_model->getConDeliveries($biller_id, $project_id, $from_date, $to_date, false);
			if ($deliveries) {
				foreach ($deliveries as $delivery) {
					if (!isset($commission_deliveries[$delivery->id][$delivery->driver_id]) && !isset($absents['driver'][$delivery->driver_id][$delivery->date])) {
						$trucks_info = $trucks_infos[$delivery->truck_id];
						if ($trucks_info->big_truck == 1) {
							$truck_commission_rate = $biller->big_truck_commission_rate;
							$truck_commission_rate_ot = $biller->big_truck_commission_rate_ot;
						} else {
							$truck_commission_rate = $biller->truck_commission_rate;
							$truck_commission_rate_ot = $biller->truck_commission_rate_ot;
						}

						if (isset($truck_commissions[$delivery->driver_id])) {
							$delivery_ids = $truck_commissions[$delivery->driver_id]['delivery_ids'] . ',' . $delivery->id;
							$normal_qty = $truck_commissions[$delivery->driver_id]['normal_qty'];
							$overtime_qty = $truck_commissions[$delivery->driver_id]['overtime_qty'];
							if ($delivery->departure_time >= $biller->start_hour && $delivery->departure_time <= $biller->end_hour) {
								$normal_qty++;
							} else {
								$overtime_qty++;
							}
						} else {
							$delivery_ids = $delivery->id;
							$normal_qty = 0;
							$overtime_qty = 0;
							if ($delivery->departure_time >= $biller->start_hour && $delivery->departure_time <= $biller->end_hour) {
								$normal_qty = 1;
							} else {
								$overtime_qty = 1;
							}
						}
						$row = array(
							"delivery_ids" => $delivery_ids,
							"normal_qty" => $normal_qty,
							"overtime_qty" => $overtime_qty,
							"truck_commission_rate" => $truck_commission_rate,
							"truck_commission_rate_ot" => $truck_commission_rate_ot,
							"driver_id" => $delivery->driver_id,
							"driver_name" => $delivery->driver_name,
						);
						$truck_commissions[$delivery->driver_id] = $row;
					}
				}
			}
		}
		echo json_encode(array('officer_commissions' => $officer_commissions, 'truck_commissions' => $truck_commissions, 'pump_commissions' => $pump_commissions));
	}


	public function add_commission_payment($id = false)
	{
		$this->bpas->checkPermissions('commission_payments', true);
		$this->bpas->checkPermissions('add_commission', true);
		$this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
		$this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
		$this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		$commission = $this->concretes_model->getCommissionByID($id);
		if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->bpas->GP['concretes-commissions-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$currencies = array();
			$camounts = $this->input->post("c_amount");
			if (!empty($camounts)) {
				foreach ($camounts as $key => $camount) {
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$currencies[] = array(
						"amount" => $camounts[$key],
						"currency" => $currency[$key],
						"rate" => $rate[$key],
					);
				}
			}
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay', $commission->biller_id);
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			$payment = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'transaction' => "CCommission",
				'transaction_id' => $id,
				'amount' => $this->input->post('amount-paid'),
				'paid_by' => $this->input->post('paid_by'),
				'note' => $this->bpas->clear_tags($this->input->post('note')),
				'created_by' => $this->session->userdata('user_id'),
				'type' => "expense",
				'account_code' => $paying_from,
				'currencies' => json_encode($currencies),
			);

			//=====accountig=====//
			if ($this->Settings->accounting == 1) {
				$paymentAcc = $this->site->getAccountSettingByBiller($commission->biller_id);
				$accTrans[] = array(
					'tran_type' => 'Payment',
					'tran_date' => $date,
					'reference_no' => $reference_no,
					'account_code' => $paymentAcc->ap_acc,
					'amount' => $this->input->post('amount-paid'),
					'narrative' => "Commission Payment " . $commission->reference_no,
					'description' => $this->input->post('note'),
					'biller_id' => $commission->biller_id,
					'project_id' => $commission->project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
				$accTrans[] = array(
					'tran_type' => 'Payment',
					'tran_date' => $date,
					'reference_no' => $reference_no,
					'account_code' => $paying_from,
					'amount' => $this->input->post('amount-paid') * (-1),
					'narrative' => "Commission Payment " . $commission->reference_no,
					'description' => $this->input->post('note'),
					'biller_id' => $commission->biller_id,
					'project_id' => $commission->project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
			}
			//=====end accountig=====//
			if ($_FILES['userfile']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload()) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$payment['attachment'] = $photo;
			}
		} elseif ($this->input->post('add_payment')) {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($this->form_validation->run() == true && $this->concretes_model->addCommissionPayment($payment, $accTrans)) {
			$this->session->set_flashdata('message', lang("payment_added"));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['commission'] = $commission;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->load->view($this->theme . 'concretes/add_commission_payment', $this->data);
		}
	}

	public function edit_commission_payment($id = false)
	{
		$this->bpas->checkPermissions('commission_payments', true);
		$this->bpas->checkPermissions('edit_commission', true);
		$this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
		$this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
		$this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		$payment = $this->concretes_model->getPaymentByID($id);
		$commission = $this->concretes_model->getCommissionByID($payment->transaction_id);
		if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->bpas->GP['concretes-commissions-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$currencies = array();
			$camounts = $this->input->post("c_amount");
			if (!empty($camounts)) {
				foreach ($camounts as $key => $camount) {
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$currencies[] = array(
						"amount" => $camounts[$key],
						"currency" => $currency[$key],
						"rate" => $rate[$key],
					);
				}
			}
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay', $commission->biller_id);
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			$payment = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'transaction_id' => $commission->id,
				'amount' => $this->input->post('amount-paid'),
				'paid_by' => $this->input->post('paid_by'),
				'note' => $this->bpas->clear_tags($this->input->post('note')),
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
				'account_code' => $paying_from,
				'currencies' => json_encode($currencies),
			);

			//=====accountig=====//
			if ($this->Settings->accounting == 1) {
				$paymentAcc = $this->site->getAccountSettingByBiller($commission->biller_id);
				$accTrans[] = array(
					'tran_no' => $id,
					'tran_type' => 'Payment',
					'tran_date' => $date,
					'reference_no' => $reference_no,
					'account_code' => $paymentAcc->ap_acc,
					'amount' => $this->input->post('amount-paid'),
					'narrative' => "Commission Payment " . $commission->reference_no,
					'description' => $this->input->post('note'),
					'biller_id' => $commission->biller_id,
					'project_id' => $commission->project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
				$accTrans[] = array(
					'tran_no' => $id,
					'tran_type' => 'Payment',
					'tran_date' => $date,
					'reference_no' => $reference_no,
					'account_code' => $paying_from,
					'amount' => $this->input->post('amount-paid') * (-1),
					'narrative' => "Commission Payment " . $commission->reference_no,
					'description' => $this->input->post('note'),
					'biller_id' => $commission->biller_id,
					'project_id' => $commission->project_id,
					'created_by' => $this->session->userdata('user_id'),
				);
			}
			//=====end accountig=====//
			if ($_FILES['userfile']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload()) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$payment['attachment'] = $photo;
			}
		} elseif ($this->input->post('edit_payment')) {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($this->form_validation->run() == true && $this->concretes_model->updateCommissionPayment($id, $payment, $accTrans)) {
			$this->session->set_flashdata('message', lang("payment_edited"));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['commission'] = $commission;
			$this->data['payment'] = $payment;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->load->view($this->theme . 'concretes/edit_commission_payment', $this->data);
		}
	}


	public function commission_payments($id = false)
	{
		$this->bpas->checkPermissions("commission_payments", true);
		$this->data['payments'] = $this->concretes_model->getPaymentByCommission($id);
		$this->load->view($this->theme . 'concretes/commission_payments', $this->data);
	}

	public function delete_commission_payment($id = null)
	{
		$this->bpas->checkPermissions('commission_payments', true);
		$this->bpas->checkPermissions('delete_commission', true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->concretes_model->deleteCommissionPayment($id)) {
			$this->session->set_flashdata('message', lang("payment_deleted"));
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}
	public function commission_payment_note($id = null)
	{
		$this->bpas->checkPermissions('commission_payments', true);
		$payment = $this->concretes_model->getPaymentByID($id);
		$commission = $this->concretes_model->getCommissionByID($payment->transaction_id);
		$this->data['commission'] = $commission;
		$this->data['biller'] = $this->site->getCompanyByID($commission->biller_id);
		$this->data['payment'] = $payment;
		$this->data['created_by'] = $this->site->getUserByID($payment->created_by);
		$this->data['page_title'] = $this->lang->line("payment_note");
		$this->load->view($this->theme . 'concretes/commission_payment_note', $this->data);
	}

	public function commissions_report()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('commissions_report')));
		$meta = array('page_title' => lang('commissions_report'), 'bc' => $bc);
		$this->page_construct('concretes/commissions_report', $meta, $this->data);
	}

	public function getCommissionsReport($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('commissions_report');
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(date, '%Y-%m-%d %T') as date,
								DATE_FORMAT(from_date, '%Y-%m-%d %T') as from_date,
								DATE_FORMAT(to_date, '%Y-%m-%d %T') as to_date,
								con_commissions.reference_no,
								con_commissions.commission_type,
								IFNULL(grand_total,0) as grand_total,
								IFNULL(paid,0) as paid,
								IFNULL(balance,0) as balance,
								con_commissions.payment_status,
								con_commissions.id as id")
				->from("con_commissions");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_commissions.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_commissions.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->db->where('con_commissions.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_commissions.date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_commissions.biller_id', $biller);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('commissions_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('from_date'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('to_date'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('type'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));

				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($data_row->from_date));
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->hrld($data_row->to_date));
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($data_row->commission_type));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->paid);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->balance);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($data_row->payment_status));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$filename = 'commissions_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
									DATE_FORMAT(date, '%Y-%m-%d %T') as date,
									DATE_FORMAT(from_date, '%Y-%m-%d %T') as from_date,
									DATE_FORMAT(to_date, '%Y-%m-%d %T') as to_date,
									con_commissions.reference_no,
									con_commissions.commission_type,
									IFNULL(grand_total,0) as grand_total,
									IFNULL(paid,0) as paid,
									IFNULL(balance,0) as balance,
									con_commissions.payment_status,
									con_commissions.id as id")
				->from("con_commissions");
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_commissions.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_commissions.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->datatables->where('con_commissions.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_commissions.date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_commissions.biller_id', $biller);
			}
			echo $this->datatables->generate();
		}
	}

	public function absents($biller_id = NULL)
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('absents')));
		$meta = array('page_title' => lang('absents'), 'bc' => $bc);
		$this->page_construct('concretes/absents', $meta, $this->data);
	}

	public function getAbsents($biller_id = NULL)
	{
		$this->bpas->checkPermissions('absents');
		$edit_link = anchor('admin/concretes/edit_absent/$1', '<i class="fa fa-edit"></i> ' . lang('edit_absent'), ' class="edit_absent" ');
		$delete_link = "<a href='#' class='po delete_absent' title='<b>" . $this->lang->line("delete_absent") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('concretes/delete_absent/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_absent') . "</a>";
		$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
		$this->load->library('datatables');
		$this->datatables
			->select("con_absents.id as id, con_absents.date, con_absents.reference_no,CONCAT(" . $this->db->dbprefix('users') . ".last_name,' '," . $this->db->dbprefix('users') . ".first_name) as created_by,  con_absents.attachment")
			->join("users", "users.id = con_absents.created_by", "left")
			->from("con_absents");
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('con_absents.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('con_absents.biller_id', $this->session->userdata('biller_id'));
		}
		if ($biller_id) {
			$this->datatables->where('con_absents.biller_id', $biller_id);
		}
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
	}


	public function add_absent()
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('type', $this->lang->line("type"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$type = $this->input->post('type');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cabsent', $biller_id);
			if ($this->Owner || $this->Admin || $this->bpas->GP['concretes-absents-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$note = $this->bpas->clear_tags($this->input->post('note'));
			$i = isset($_POST['officer_id']) ? sizeof($_POST['officer_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$officer_id = $_POST['officer_id'][$r];
				$absent_date = $this->bpas->fsd(trim($_POST['absent_date'][$r]));
				if (isset($officer_id) && isset($absent_date)) {
					if ($type == "driver") {
						$items[] = array(
							'driver_id' => $officer_id,
							'absent_date' => $absent_date
						);
					} else {
						$items[] = array(
							'officer_id' => $officer_id,
							'absent_date' => $absent_date
						);
					}
				}
			}
			if (empty($items)) {
				$this->form_validation->set_rules('officer', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'biller_id' => $biller_id,
				'biller' => $biller,
				'project_id' => $project_id,
				'type' => $type,
				'note' => $note,
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s'),
			);

			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->addAbsent($data, $items)) {
			$this->session->set_userdata('remove_conabls', 1);
			$this->session->set_flashdata('message', $this->lang->line("absent_added") . " " . $reference_no);
			if ($this->input->post('add_absent_next')) {
				admin_redirect('concretes/add_absent');
			} else {
				admin_redirect('concretes/absents');
			}
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] =  $this->site->getBillers();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/absents'), 'page' => lang('absents')), array('link' => '#', 'page' => lang('add_absent')));
			$meta = array('page_title' => lang('add_absent'), 'bc' => $bc);
			$this->page_construct('concretes/add_absent', $meta, $this->data);
		}
	}


	public function edit_absent($id = false)
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
		$this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('type', $this->lang->line("type"), 'required');
		if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$type = $this->input->post('type');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cabsent', $biller_id);
			if ($this->Owner || $this->Admin || $this->bpas->GP['concretes-absents-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time == 0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$note = $this->bpas->clear_tags($this->input->post('note'));
			$i = isset($_POST['officer_id']) ? sizeof($_POST['officer_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$officer_id = $_POST['officer_id'][$r];
				$absent_date = $this->bpas->fsd(trim($_POST['absent_date'][$r]));
				if (isset($officer_id) && isset($absent_date)) {
					if ($type == "driver") {
						$items[] = array(
							'absent_id' => $id,
							'driver_id' => $officer_id,
							'absent_date' => $absent_date
						);
					} else {
						$items[] = array(
							'absent_id' => $id,
							'officer_id' => $officer_id,
							'absent_date' => $absent_date
						);
					}
				}
			}
			if (empty($items)) {
				$this->form_validation->set_rules('officer', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
				'reference_no' => $reference_no,
				'biller_id' => $biller_id,
				'biller' => $biller,
				'project_id' => $project_id,
				'type' => $type,
				'note' => $note,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
			);

			if ($_FILES['document']['size'] > 0) {
				$this->load->library('upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('document')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->updateAbsent($id, $data, $items)) {
			$this->session->set_userdata('remove_conabls', 1);
			$this->session->set_flashdata('message', $this->lang->line("absent_edited") . " " . $reference_no);
			admin_redirect('concretes/absents');
		} else {
			$absent = $this->concretes_model->getAbsentByID($id);
			$absent_items = $this->concretes_model->getAbsentItems($id);
			krsort($absent_items);
			$c = rand(100000, 9999999);
			foreach ($absent_items as $item) {
				if ($absent->type == "driver") {
					$row = $this->concretes_model->getDriverByID($item->driver_id);
					$row->position = "Driver";
				} else {
					$row = $this->concretes_model->getOfficerByID($item->officer_id);
				}

				$row->date = $this->bpas->hrsd($item->absent_date);
				$pr[$c] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->full_name_kh . " - " . $row->full_name, 'row' => $row);
				$c++;
			}
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['absent'] = $absent;
			$this->data['absent_items'] = json_encode($pr);
			$this->data['billers'] =  $this->site->getBillers();
			$this->session->set_userdata('remove_conabls', 1);
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => admin_url('concretes/absents'), 'page' => lang('absents')), array('link' => '#', 'page' => lang('edit_absent')));
			$meta = array('page_title' => lang('edit_absent'), 'bc' => $bc);
			$this->page_construct('concretes/edit_absent', $meta, $this->data);
		}
	}

	public function delete_absent($id = null)
	{
		$this->bpas->checkPermissions(NULL, true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->concretes_model->deleteAbsent($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("absent_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('absent_deleted'));
			admin_redirect('concretes/absents');
		}
	}

	public function absent_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_absent', true);
					foreach ($_POST['val'] as $id) {
						$this->concretes_model->deleteAbsent($id);
					}
					$this->session->set_flashdata('message', $this->lang->line("absent_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				} elseif ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('absent'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('created_by'));
					$row = 2;
					foreach ($_POST['val'] as $id) {
						$absent = $this->concretes_model->getAbsentByID($id);
						$created_by = $this->site->getUserByID($absent->created_by);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($absent->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $absent->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $created_by->last_name . ' ' . $created_by->first_name);
						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'absents_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function modal_view_absent($id = null)
	{
		$this->bpas->checkPermissions('missions', true);
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$absent = $this->concretes_model->getAbsentByID($id);
		$this->data['biller'] = $this->site->getCompanyByID($absent->biller_id);
		$this->data['absent'] = $absent;
		$this->data['absent_items'] = $this->concretes_model->getAbsentItems($id);;
		$this->data['created_by'] = $this->site->getUserByID($absent->created_by);
		$this->load->view($this->theme . 'concretes/modal_view_absent', $this->data);
	}

	public function officer_suggestions()
	{
		$term = $this->input->get('term', true);
		$biller_id = $this->input->get('biller_id', true);
		$type = $this->input->get('type', true);
		if (strlen($term) < 1 || !$term) {
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
		}
		$analyzed = $this->bpas->analyze_term($term);
		$sr = $analyzed['term'];
		if ($type == "driver") {
			$rows = $this->concretes_model->getDriverNames($sr);
		} else {
			$rows = $this->concretes_model->getOfficerNames($sr, $biller_id);
		}

		if ($rows) {
			$c = str_replace(".", "", microtime(true));
			$r = 0;
			foreach ($rows as $row) {
				$row->date = $this->bpas->hrsd(date("Y-m-d"));
				$pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->lastname_kh . " - " . $row->lastname, 'row' => $row);
				$r++;
			}
			$this->bpas->send_json($pr);
		} else {
			$this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
		}
	}

	public function absents_report()
	{
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['officers'] = $this->concretes_model->getOfficers();
		$this->data['drivers'] =  $this->site->getAllCompanies('driver');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concrete')), array('link' => '#', 'page' => lang('absents_report')));
		$meta = array('page_title' => lang('absents_report'), 'bc' => $bc);
		$this->page_construct('concretes/absents_report', $meta, $this->data);
	}
	public function getAbsentsReport($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('absents_report');
		$start_date = $this->input->get('start_date') ? $this->bpas->fsd($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fsd($this->input->get('end_date'), false, 1) : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$officer = $this->input->get('officer') ? $this->input->get('officer') : NULL;
		$driver = $this->input->get('driver') ? $this->input->get('driver') : NULL;
		if ($pdf || $xls) {
			$this->db->select("	
							DATE_FORMAT(" . $this->db->dbprefix('con_absents') . ".date, '%Y-%m-%d %T') as date,
							con_absents.reference_no,
							IFNULL(" . $this->db->dbprefix('hr_employees') . ".lastname," . $this->db->dbprefix('con_drivers') . ".full_name_kh) as full_name_kh,
							IFNULL(" . $this->db->dbprefix('hr_employees') . ".lastname," . $this->db->dbprefix('con_drivers') . ".full_name) as full_name,
							IFNULL(" . $this->db->dbprefix('hr_employees') . ".position,'Driver') as position,
							DATE_FORMAT(" . $this->db->dbprefix('con_absent_items') . ".absent_date, '%Y-%m-%d') as absent_date,
							con_absents.id as id")
				->from("con_absent_items")
				->join("con_absents", "con_absents.id = con_absent_items.absent_id", "inner")
				->join("hr_employees", "hr_employees.id = con_absent_items.officer_id", "left")
				->join("con_drivers", "con_drivers.id = con_absent_items.driver_id", "left");

			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('con_absents.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('con_absents.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->db->where('con_absent_items.absent_date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('con_absent_items.absent_date <=', $end_date);
			}
			if ($biller) {
				$this->db->where('con_absents.biller_id', $biller);
			}
			if ($officer) {
				$this->db->where('con_absent_items.officer_id', $officer);
			}
			if ($driver) {
				$this->db->where('con_absent_items.driver_id', $driver);
			}
			$q = $this->db->get();
			$data = false;
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			}
			if (!empty($data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('absents_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('full_name_kh'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('full_name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('absent_date'));


				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->full_name_kh);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->full_name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->hrsd($data_row->absent_date));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

				$filename = 'absents_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->load->library('datatables');
			$this->datatables->select("	
									DATE_FORMAT(" . $this->db->dbprefix('con_absents') . ".date, '%Y-%m-%d %T') as date,
									con_absents.reference_no,
									IFNULL(" . $this->db->dbprefix('hr_employees') . ".lastname," . $this->db->dbprefix('con_drivers') . ".full_name_kh) as full_name_kh,
									IFNULL(" . $this->db->dbprefix('hr_employees') . ".full_name," . $this->db->dbprefix('con_drivers') . ".full_name) as full_name,
									IFNULL(" . $this->db->dbprefix('hr_employees') . ".position,'Driver') as position,
									DATE_FORMAT(" . $this->db->dbprefix('con_absent_items') . ".absent_date, '%Y-%m-%d') as absent_date,
									con_absents.id as id")
				->from("con_absent_items")
				->join("con_absents", "con_absents.id = con_absent_items.absent_id", "inner")
				->join("hr_employees", "hr_employees.id = con_absent_items.officer_id", "left")
				->join("con_drivers", "con_drivers.id = con_absent_items.driver_id", "left");

			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('con_absents.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('con_absents.biller_id', $this->session->userdata('biller_id'));
			}
			if ($start_date) {
				$this->datatables->where('con_absent_items.absent_date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('con_absent_items.absent_date <=', $end_date);
			}
			if ($biller) {
				$this->datatables->where('con_absents.biller_id', $biller);
			}
			if ($officer) {
				$this->datatables->where('con_absent_items.officer_id', $officer);
			}
			if ($driver) {
				$this->datatables->where('con_absent_items.driver_id', $driver);
			}
			echo $this->datatables->generate();
		}
	}


	function import_delivery()
	{
		$this->bpas->checkPermissions('add_delivery', true);
		$this->load->helper('security');
		$this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
		if ($this->form_validation->run() == true) {
			if (isset($_FILES["userfile"])) {
				$deliveries = false;
				$accTrans = false;
				$used_fuels = false;
				$stockmoves = false;
				$biller_id = $this->input->post("biller");
				$biller_details = $this->site->getCompanyByID($biller_id);
				$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
				$project_id = $this->input->post('project');
				$warehouse_id = $this->input->post("warehouse");
				$deliveryAcc = $this->site->getAccountSettingByBiller($biller_id);
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach ($object->getWorksheetIterator() as $worksheet) {
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for ($row = 2; $row <= $highestRow; $row++) {
						$date = trim($worksheet->getCellByColumnAndRow(0, $row)->getValue());
						$reference = trim($worksheet->getCellByColumnAndRow(1, $row)->getValue());
						$customer = trim($worksheet->getCellByColumnAndRow(2, $row)->getValue());
						$location = trim($worksheet->getCellByColumnAndRow(3, $row)->getValue());
						$slump = trim($worksheet->getCellByColumnAndRow(4, $row)->getValue());
						$casting_type = trim($worksheet->getCellByColumnAndRow(5, $row)->getValue());
						$strength = trim($worksheet->getCellByColumnAndRow(6, $row)->getValue());
						$quantity = trim($worksheet->getCellByColumnAndRow(7, $row)->getValue());
						$truck = trim($worksheet->getCellByColumnAndRow(8, $row)->getValue());
						$pump = trim($worksheet->getCellByColumnAndRow(9, $row)->getValue());
						$pump_move = trim($worksheet->getCellByColumnAndRow(10, $row)->getValue());
						$markup_qty = trim($worksheet->getCellByColumnAndRow(11, $row)->getValue());
						$seal_number = trim($worksheet->getCellByColumnAndRow(12, $row)->getValue());
						if (strpos($date, '/') == false) {
							$date = PHPExcel_Shared_Date::ExcelToPHP($date);
							$date = date('d/m/Y', $date);
						}
						$finals[] = array(
							'date'			=> $date,
							'reference'		=> $reference,
							'customer'		=> $customer,
							'location' 		=> $location,
							'slump'  		=> $slump,
							'casting_type'  => $casting_type,
							'strength'  	=> $strength,
							'quantity'   	=> $quantity,
							'truck'   		=> $truck,
							'pump'   		=> $pump,
							'pump_move'   	=> $pump_move,
							'markup_qty'   	=> $markup_qty,
							'seal_number'   => $seal_number,
						);
					}
				}
				if ($finals) {
					$customers = $this->concretes_model->getCustomerIndexCode();
					$addresses = $this->concretes_model->getAddressIndexName();
					$trucks = $this->concretes_model->getTruckIndexPlate();
					$slumps = $this->concretes_model->getSlumpIndexName();
					$castings = $this->concretes_model->getCastingIndexName();
					$strengths = $this->concretes_model->getStrenthIndexCode();
					$drivers = $this->concretes_model->getDriverIndexID();
					$boms = $this->concretes_model->getBomProductIndexProduct();
					if ($this->Settings->accounting == 1) {
						$productAccs = $this->concretes_model->getProductAccIndexProduct();
					}

					foreach ($finals as $final) {
						$index = $final['date'] . "_" . $final['reference'] . "_" . $final['customer'];
						$customer = $customers[$final['customer']];
						$location = $addresses[$customer->id][$final['location']];
						$truck = $trucks[$final['truck']];
						$slump = $slumps[$final['slump']];
						$casting_type = $castings[$final['casting_type']];
						$stregth = $strengths[$final['strength']];
						if (!isset($customer)) {
							$this->session->set_flashdata('error', lang("customer") . " (" . $final['customer'] . "). " . lang("company__exist"));
							redirect("concretes/import_delivery");
						}
						if (!isset($location)) {
							$this->session->set_flashdata('error', lang("location") . " (" . $final['location'] . "). " . lang("name__exist"));
							redirect("concretes/import_delivery");
						}
						if (!isset($truck)) {
							$this->session->set_flashdata('error', lang("truck") . " (" . $final['truck'] . "). " . lang("name__exist"));
							redirect("concretes/import_delivery");
						}
						if (!isset($slump)) {
							$this->session->set_flashdata('error', lang("slump") . " (" . $final['slump'] . "). " . lang("name__exist"));
							redirect("concretes/import_delivery");
						}
						if (!isset($casting_type)) {
							$this->session->set_flashdata('error', lang("casting_type") . " (" . $final['casting_type'] . "). " . lang("name__exist"));
							redirect("concretes/import_delivery");
						}
						if (!isset($stregth)) {
							$this->session->set_flashdata('error', lang("stregth") . " (" . $final['strength'] . "). " . lang("name__exist"));
							redirect("concretes/import_delivery");
						}

						$pump = false;
						$pump_driver = false;
						$pump_move = 0;
						$driver_assistant = '';
						if ($final['pump']) {
							$pump = $trucks[$final['pump']];
							if (!isset($pump)) {
								$this->session->set_flashdata('error', lang("pump") . " (" . $final['pump'] . "). " . lang("name__exist"));
								redirect("concretes/import_delivery");
							}
							$pump_driver = $drivers[$pump->driver_id];
							$driver_assistant = $pump->driver_assistant;
							$pump_move = ($final['pump_move'] == "Yes" || $final['pump_move'] == "yes") ? 1 : 0;
						}
						$driver = $drivers[$truck->driver_id];
						$quantity = $final['quantity'];
						$date = $this->bpas->fsd($final['date']);
						$reference = $final['reference'];
						$customer_id = $customer->id;

						$delivery = array(
							'date' => $date,
							'reference_no' => $reference,
							'customer_id' => $customer_id,
							'customer_code' => $customer->code,
							'customer' => ($customer->company != '-'  ? $customer->company : $customer->name),
							'biller_id' => $biller_id,
							'biller' => $biller,
							'project_id' => $project_id,
							'warehouse_id' => $warehouse_id,
							'location_id' => $location->id,
							'location_name' => $location->name,
							'site_person' => $location->contact_person,
							'site_number' => $location->phone,
							'kilometer' => $location->kilometer,
							'driver_id' => $driver->id,
							'driver_name' => $driver->name_kh . " - " . $driver->name,
							'truck_id' => $truck->id,
							'truck_code' => $truck->plate,
							'slump_id' => $slump->id,
							'slump_name' => $slump->name,
							'casting_type_id' => $casting_type->id,
							'casting_type_name' => $casting_type->name,
							'stregth_id' => $stregth->id,
							'stregth_name' => $stregth->name,
							'pump_id' => $pump->id,
							'pump_code' => $pump->plate,
							'pump_driver_id' => $pump_driver->id,
							'pump_driver_name' => ($pump_driver ? $pump_driver->name_kh . " - " . $pump_driver->name : ''),
							'pump_move' => $pump_move,
							'markup_qty' => $final['markup_qty'] ? $final['markup_qty'] : 0,
							'seal_number' => $final['seal_number'],
							'driver_assistant' => $driver_assistant,
							'quantity' => $quantity,
							'created_by' => $this->session->userdata('user_id'),
							'created_at' => date('Y-m-d H:i:s'),
						);
						$deliveries[$index] = $delivery;

						if ($this->Settings->fuel_expenses) {
							if ($truck->in_range_km < $location->kilometer) {
								$out_range_litre = (($location->kilometer - $truck->in_range_km) / $truck->out_range_km) * $truck->out_range_litre;
							} else {
								$out_range_litre = 0;
							}
							$in_range_litre = $truck->in_range_litre;
							$used_fuels[$index][] = array(
								'date' => $date,
								'biller_id' => $biller_id,
								'driver_id' => $driver->id,
								'truck_id' => $truck->id,
								'kilometer' => $location->kilometer,
								'in_range_litre' => ($in_range_litre > 0 ? (($in_range_litre * $truck->driver_fuel_fee) / 100) : 0),
								'out_range_litre' => ($out_range_litre > 0 ? (($out_range_litre * $truck->driver_fuel_fee) / 100) : 0),
								'pump_litre' => 0
							);
							if ($truck->driver_assistant && $truck->driver_fuel_fee != 100) {
								$driver_assistants = json_decode($truck->driver_assistant);
								$total_asstants = count($driver_assistants);
								foreach ($driver_assistants as $driver_assistant) {
									$used_fuels[$index][] = array(
										'date' => $date,
										'biller_id' => $biller_id,
										'driver_id' => $driver_assistant,
										'truck_id' => $truck->id,
										'kilometer' => $location->kilometer,
										'in_range_litre' => ($in_range_litre > 0 ? ((($in_range_litre * $truck->assistant_fuel_fee) / 100) / $total_asstants) : 0),
										'out_range_litre' => ($out_range_litre > 0 ? ((($out_range_litre * $truck->assistant_fuel_fee) / 100) / $total_asstants) : 0),
										'pump_litre' => 0
									);
								}
							}
							if ($pump) {
								$in_range_litre = 0;
								$out_range_litre = 0;
								if ($pump_move > 0) {
									if ($pump->in_range_km < $location->kilometer) {
										$out_range_litre = (($location->kilometer - $pump->in_range_km) / $pump->out_range_km) * $pump->out_range_litre;
									}
									$in_range_litre = $pump->in_range_litre;
								}
								$pump_litre = ($quantity / $pump->m3) * $pump->litre;
								$used_fuels[$index][] = array(
									'date' => $date,
									'biller_id' => $biller_id,
									'driver_id' => $pump->driver_id,
									'truck_id' => $pump->id,
									'kilometer' => $location->kilometer,
									'in_range_litre' => ($in_range_litre > 0 ? (($in_range_litre * $pump->driver_fuel_fee) / 100) : 0),
									'out_range_litre' => ($out_range_litre > 0 ? (($out_range_litre * $pump->driver_fuel_fee) / 100) : 0),
									'pump_litre' => ($pump_litre > 0 ? (($pump_litre * $pump->driver_fuel_fee) / 100) : 0)
								);
								if ($pump->driver_assistant && $pump->driver_fuel_fee != 100) {
									$driver_assistants = json_decode($pump->driver_assistant);
									$total_asstants = count($driver_assistants);
									foreach ($driver_assistants as $driver_assistant) {
										$used_fuels[$index][] = array(
											'date' => $date,
											'biller_id' => $biller_id,
											'driver_id' => $driver_assistant,
											'truck_id' => $pump->id,
											'kilometer' => $location->kilometer,
											'in_range_litre' => ($in_range_litre > 0 ? ((($in_range_litre * $pump->assistant_fuel_fee) / 100) / $total_asstants) : 0),
											'out_range_litre' => ($out_range_litre > 0 ? ((($out_range_litre * $pump->assistant_fuel_fee) / 100) / $total_asstants) : 0),
											'pump_litre' => ($pump_litre > 0 ? ((($pump_litre * $pump->assistant_fuel_fee) / 100) / $total_asstants) : 0)
										);
									}
								}
							}
						}

						if ($stregth->type == 'bom') {
							$product_boms = $boms[$stregth->id];
							if ($product_boms) {
								foreach ($product_boms as $product_bom) {
									if ($this->Settings->accounting_method == '0') {
										$costs = $this->site->getFifoCost($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves);
									} else if ($this->Settings->accounting_method == '1') {
										$costs = $this->site->getLifoCost($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves);
									} else if ($this->Settings->accounting_method == '3') {
										$costs = $this->site->getProductMethod($product_bom->product_id, ($quantity * $product_bom->quantity), $stockmoves);
									}
									$productAcc = $productAccs[$product_bom->product_id];
									if ($costs) {
										foreach ($costs as $cost_item) {
											$stockmoves[$index][] = array(
												'transaction' => 'CDelivery',
												'product_id' => $product_bom->product_id,
												'product_type'    => $product_bom->product_type,
												'product_code' => $product_bom->product_code,
												'quantity' => $cost_item['quantity'] * (-1),
												'unit_quantity' => $product_bom->unit_qty,
												'unit_code' => $product_bom->code,
												'unit_id' => $product_bom->unit_id,
												'warehouse_id' => $warehouse_id,
												'date' => $date,
												'real_unit_cost' => $cost_item['cost'],
												'reference_no' => $reference,
												'user_id' => $this->session->userdata('user_id'),
											);
											if ($this->Settings->accounting == 1) {
												$accTrans[$index][] = array(
													'tran_type' => 'CDelivery',
													'tran_date' => $date,
													'reference_no' => $reference,
													'account_code' => $productAcc->stock_account,
													'amount' => - ($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . $cost_item['quantity'] . '#' . 'Cost: ' . $cost_item['cost'],
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'created_by' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,

												);
												$accTrans[$index][] = array(
													'tran_type' => 'CDelivery',
													'tran_date' => $date,
													'reference_no' => $reference,
													'account_code' => isset($productAcc->cost_acc) ? $productAcc->cost_acc : $this->accounting_setting->default_cost,
													'amount' => ($cost_item['cost'] * $cost_item['quantity']),
													'narrative' => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . $cost_item['quantity'] . '#' . 'Cost: ' . $cost_item['cost'],
													'biller_id' => $biller_id,
													'project_id' => $project_id,
													'created_by' => $this->session->userdata('user_id'),
													'customer_id' => $customer_id,
												);
											}
										}
									} else {
										// $real_unit_cost = $this->site->getAVGCost($product_bom->product_id, $date);
										// $product_bom->cost = $real_unit_cost;
										$stockmoves[$index][] = array(
											'transaction' => 'CDelivery',
											'product_id' => $product_bom->product_id,
											'product_type'    => $product_bom->product_type,
											'product_code' => $product_bom->product_code,
											'quantity' => ($quantity * $product_bom->quantity) * -1,
											'unit_quantity' => $product_bom->unit_qty,
											'unit_code' => $product_bom->code,
											'unit_id' => $product_bom->unit_id,
											'warehouse_id' => $warehouse_id,
											'date' => $date,
											'real_unit_cost' => $product_bom->cost,
											'reference_no' => $reference,
											'user_id' => $this->session->userdata('user_id'),
										);

										if ($this->Settings->accounting == 1) {
											$accTrans[$index][] = array(
												'transaction' => 'CDelivery',
												'tran_date' => $date,
												'reference_no' => $reference,
												'account_code' => $productAcc->stock_account,
												'amount' => - ($product_bom->cost * ($quantity * $product_bom->quantity)),
												'narrative' => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . ($quantity * $product_bom->quantity) . '#' . 'Cost: ' . $product_bom->cost,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'created_by' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
											$accTrans[$index][] = array(
												'tran_type' => 'CDelivery',
												'tran_date' => $date,
												'reference_no' => $reference,
												'account_code' => isset($productAcc->cost_acc) ? $productAcc->cost_acc : $this->accounting_setting->default_cost,
												'amount' => ($product_bom->cost * ($quantity * $product_bom->quantity)),
												'narrative' => 'Product Code: ' . $product_bom->product_code . '#' . 'Qty: ' . ($quantity * $product_bom->quantity) . '#' . 'Cost: ' . $product_bom->cost,
												'biller_id' => $biller_id,
												'project_id' => $project_id,
												'created_by' => $this->session->userdata('user_id'),
												'customer_id' => $customer_id,
											);
										}
									}
								}
							} else {
								$error = lang('please_check_product') . ' ' . $stregth->name;
								$this->session->set_flashdata('error', $error);
								redirect($_SERVER["HTTP_REFERER"]);
							}
						}
					}
				}
			}
			if (empty($deliveries)) {
				$this->form_validation->set_rules('delivery', lang("order_items"), 'required');
			}
		}
		if ($this->form_validation->run() == true && $this->concretes_model->importDelivery($deliveries, $stockmoves, $used_fuels, $accTrans)) {
			$this->session->set_flashdata('message', lang("delivery_imported"));
			redirect(admin_url('concretes'));
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('concretes'), 'page' => lang('concretes')), array('link' => '#', 'page' => lang('import_delivery')));
			$meta = array('page_title' => lang('import_delivery'), 'bc' => $bc);
			$this->page_construct('concretes/import_delivery', $meta, $this->data);
		}
	}

	public function groups($action = NULL)
	{
		$this->bpas->checkPermissions('groups');
		$page = $this->uri->segment(3);
		$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
		$this->data['constants'] = $this->settings_model->getParentCustomField();
		$bc                  = [
			['link' => admin_url(), 'page' => lang('home')],
			['link' => admin_url('system_settings'), 'page' => lang('system_settings')],
			['link' => '#', 'page' => lang($page)]
		];
		$this->data['page'] = $page;
		$meta                = ['page_title' => lang($page), 'bc' => $bc];
		$this->page_construct('settings/custom_field_by_code', $meta, $this->data);
	}
	public function group_actions()
	{
		if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('groups');
					$error = false;
					foreach ($_POST['val'] as $id) {
						if (!$this->concretes_model->deleteGroup($id)) {
							$error = true;
						}
					}
					if ($error) {
						$this->session->set_flashdata('warning', lang('group_cannot_delete'));
					} else {
						$this->session->set_flashdata('message', $this->lang->line("group_deleted"));
					}
					redirect($_SERVER["HTTP_REFERER"]);
				}
				if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('groups'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('note'));

					$row = 2;
					foreach ($_POST['val'] as $id) {
						$group = $this->concretes_model->getGroupByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $group->name);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->remove_tag($group->note));

						$row++;
					}
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'groups_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_account_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}
}