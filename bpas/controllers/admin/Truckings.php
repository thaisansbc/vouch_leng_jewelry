<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Truckings extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        $this->lang->load('truckings', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('truckings_model');
		$this->digital_upload_path = 'files/';
		$this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '10240';
    }
	public function drivers()
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'truckings', 'page' => lang('trucking')), array('link' => '#', 'page' => lang('drivers')));
        $meta = array('page_title' => lang('drivers'), 'bc' => $bc);
        $this->page_construct('truckings/drivers', $meta, $this->data);
    }
    public function getDrivers()
    {
		$this->bpas->checkPermissions('drivers');
        $this->load->library('datatables');
        $this->datatables
            ->select("tru_drivers.id as id,
						tru_drivers.full_name_kh,
						tru_drivers.full_name,
						tru_drivers.phone,
						DATE_FORMAT(start_date, '%Y-%m-%d') as start_date,
						tru_drivers.address,
						tru_drivers.note,
						tru_drivers.status,
						tru_drivers.attachment,
						")
            ->from("tru_drivers")
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_driver") . "' href='" . site_url('truckings/edit_driver/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_driver") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('truckings/delete_driver/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
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
            redirect('truckings/drivers');
        }

        if ($this->form_validation->run() == true && $id = $this->truckings_model->addDriver($data)) {
            $this->session->set_flashdata('message', $this->lang->line("driver_added"));
            redirect('truckings/drivers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'truckings/add_driver', $this->data);
        }
    }
	
	public function edit_driver($id = false)
    {
		$this->bpas->checkPermissions('drivers', true);
		$driver = $this->truckings_model->getDriverByID($id);
		$this->form_validation->set_rules('full_name_kh', lang("full_name_kh"), 'required');
		$this->form_validation->set_rules('full_name', lang("full_name"), 'required');
        if ($this->form_validation->run() == true) {
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
            redirect('truckings/drivers');
        }

        if ($this->form_validation->run() == true && $id = $this->truckings_model->updateDriver($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("driver_edited"));
            redirect('truckings/drivers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['driver'] = $driver;
            $this->load->view($this->theme . 'truckings/edit_driver', $this->data);
        }
    }
	
	public function delete_driver($id = NULL)
    {	
		$this->bpas->checkPermissions('drivers', true);
		if ($this->truckings_model->deleteDriver($id)) {
			echo $this->lang->line("driver_deleted");
		} else {
			$this->session->set_flashdata('warning', lang('driver_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
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
                        if (!$this->truckings_model->deleteDriver($id)) {
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
						$driver = $this->truckings_model->getDriverByID($id);
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
	
	
	
	public function trucks()
    {
        $this->bpas->checkPermissions('trucks');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'truckings', 'page' => lang('trucking')), array('link' => '#', 'page' => lang('trucks')));
        $meta = array('page_title' => lang('trucks'), 'bc' => $bc);
        $this->page_construct('truckings/trucks', $meta, $this->data);
    }
    public function getTrucks()
    {
		$this->bpas->checkPermissions('trucks');
        $this->load->library('datatables');
        $this->datatables
            ->select("tru_trucks.id as id,
						tru_trucks.code,
						tru_trucks.plate,
						CONCAT(".$this->db->dbprefix('tru_drivers').".full_name_kh,' - ',".$this->db->dbprefix('tru_drivers').".full_name) as driver,
						tru_trucks.note,
						tru_trucks.status,
						tru_trucks.attachment,
						")
            ->from("tru_trucks")
			->join("tru_drivers","tru_drivers.id = tru_trucks.driver_id","left")
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_truck") . "' href='" . site_url('truckings/edit_truck/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_truck") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('truckings/delete_truck/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
	public function add_truck()
    {
		$this->bpas->checkPermissions('trucks', true);
		$this->form_validation->set_rules('plate', lang("plate"), 'required');
		$this->form_validation->set_rules('code', lang("code"), 'required|is_unique[tru_trucks.code]');
        if ($this->form_validation->run() == true) {
            $data = array(
						'code' => $this->input->post('code'),
						'plate' => $this->input->post('plate'),
						'driver_id' => $this->input->post('driver_id'),
						'fuel_id' => $this->input->post('fuel_id'),
						'note' => $this->input->post('note'),
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
            redirect('truckings/trucks');
        }
        if ($this->form_validation->run() == true && $id = $this->truckings_model->addTruck($data)) {
            $this->session->set_flashdata('message', $this->lang->line("truck_added"));
            redirect('truckings/trucks');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['drivers'] = $this->truckings_model->getDrivers();
			$this->data['products'] = $this->site->getProducts();
            $this->load->view($this->theme . 'truckings/add_truck', $this->data);
        }
    }
	public function edit_truck($id = false)
    {
		$this->bpas->checkPermissions('trucks', true);
		$truck = $this->truckings_model->getTruckByID($id);
		$this->form_validation->set_rules('plate', lang("plate"), 'required');
		$this->form_validation->set_rules('code', lang("code"), 'required');
		if ($this->input->post('code') !== $truck->code) {
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[tru_trucks.code]');
        }
        if ($this->form_validation->run() == true) {
            $data = array(
						'code' => $this->input->post('code'),
						'plate' => $this->input->post('plate'),
						'driver_id' => $this->input->post('driver_id'),
						'fuel_id' => $this->input->post('fuel_id'),
						'status' => $this->input->post('status'),
						'note' => $this->input->post('note'),
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
            redirect('truckings/trucks');
        }
        if ($this->form_validation->run() == true && $id = $this->truckings_model->updateTruck($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("truck_edited"));
            redirect('truckings/trucks');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['drivers'] = $this->truckings_model->getDrivers('active');
			$this->data['truck'] = $truck;
			$this->data['products'] = $this->site->getProducts();
            $this->load->view($this->theme . 'truckings/edit_truck', $this->data);
        }
    }
	
	public function delete_truck($id = NULL)
    {	
		$this->bpas->checkPermissions('trucks', true);
		if ($this->truckings_model->deleteTruck($id)) {
			echo $this->lang->line("truck_deleted");
		} else {
			$this->session->set_flashdata('warning', lang('truck_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
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
                        if (!$this->truckings_model->deleteTruck($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('truck_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("truck_deleted"));
                    }
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('trucks'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('plate'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('driver'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('note'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
						$truck = $this->truckings_model->getTruckByID($id);
						$driver = $this->truckings_model->getDriverByID($truck->driver_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $truck->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $truck->plate);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $driver->full_name.' - '.$driver->full_name_kh);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->remove_tag($truck->note));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($truck->status));
						$row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
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
	
	
	public function containers()
    {
        $this->bpas->checkPermissions('containers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'truckings', 'page' => lang('trucking')), array('link' => '#', 'page' => lang('containers')));
        $meta = array('page_title' => lang('containers'), 'bc' => $bc);
        $this->page_construct('truckings/containers', $meta, $this->data);
    }

    public function getContainers()
    {
		$this->bpas->checkPermissions('containers');
        $this->load->library('datatables');
        $this->datatables
            ->select("tru_containers.id as id,
						tru_containers.name,
						tru_containers.note
					")
            ->from("tru_containers")
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_container") . "' href='" . site_url('truckings/edit_container/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_container") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('truckings/delete_container/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
	
	
	public function add_container()
    {
		$this->bpas->checkPermissions('containers', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
						'name' => $this->input->post('name'),
						'note' => $this->input->post('note')
					);
        } elseif ($this->input->post('add_container')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('truckings/containers');
        }
        if ($this->form_validation->run() == true && $id = $this->truckings_model->addContainer($data)) {
            $this->session->set_flashdata('message', $this->lang->line("container_added"));
            redirect('truckings/containers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'truckings/add_container', $this->data);
        }
    }
	
	public function edit_container($id = false)
    {
		$this->bpas->checkPermissions('containers', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
						'name' => $this->input->post('name'),
						'note' => $this->input->post('note')
					);
        } elseif ($this->input->post('edit_container')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('truckings/containers');
        }
        if ($this->form_validation->run() == true && $id = $this->truckings_model->updateContainer($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("container_edited"));
            redirect('truckings/containers');
        } else {
			$container = $this->truckings_model->getcontainerByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['container'] = $container;
            $this->load->view($this->theme . 'truckings/edit_container', $this->data);
        }
    }
	
	public function delete_container($id = NULL)
    {	
		$this->bpas->checkPermissions('containers', true);
		if ($this->truckings_model->deleteContainer($id)) {
			echo $this->lang->line("container_deleted");
		} else {
			$this->session->set_flashdata('warning', lang('container_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
		
    }
	
	public function container_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('containers');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->truckings_model->deleteContainer($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('container_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("container_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('containers'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('note'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
						$container = $this->truckings_model->getContainerByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $container->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->remove_tag($container->note));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'containers_' . date('Y_m_d_H_i_s');
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
	
	
	public function factories()
    {
        $this->bpas->checkPermissions('factories');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'truckings', 'page' => lang('trucking')), array('link' => '#', 'page' => lang('factories')));
        $meta = array('page_title' => lang('factories'), 'bc' => $bc);
        $this->page_construct('truckings/factories', $meta, $this->data);
    }

    public function getFactories()
    {
		$this->bpas->checkPermissions('factories');
        $this->load->library('datatables');
        $this->datatables
            ->select("tru_factories.id as id,
						tru_factories.name,
						tru_factories.contact_person,
						tru_factories.contact_number,
						tru_factories.extra_fee,
						tru_factories.extra_fuel,
						tru_factories.address,
						tru_factories.note,
						tru_factories.attachment
					")
            ->from("tru_factories")
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_factory") . "' href='" . site_url('truckings/edit_factory/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_factory") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('truckings/delete_factory/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
	
	
	public function add_factory()
    {
		$this->bpas->checkPermissions('factories', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
						'name' => $this->input->post('name'),
						'contact_person' => $this->input->post('contact_person'),
						'contact_number' => $this->input->post('contact_number'),
						'extra_fee' => $this->input->post('extra_fee'),
						'extra_fuel' => $this->input->post('extra_fuel'),
						'address' => $this->input->post('address'),
						'note' => $this->input->post('note')
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
        } elseif ($this->input->post('add_factory')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('truckings/factories');
        }
        if ($this->form_validation->run() == true && $id = $this->truckings_model->addFactory($data)) {
            $this->session->set_flashdata('message', $this->lang->line("factory_added"));
            redirect('truckings/factories');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'truckings/add_factory', $this->data);
        }
    }
	
	public function edit_factory($id = false)
    {
		$this->bpas->checkPermissions('factories', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
			$data = array(
						'name' => $this->input->post('name'),
						'contact_person' => $this->input->post('contact_person'),
						'contact_number' => $this->input->post('contact_number'),
						'extra_fee' => $this->input->post('extra_fee'),
						'extra_fuel' => $this->input->post('extra_fuel'),
						'address' => $this->input->post('address'),
						'note' => $this->input->post('note')
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
        } elseif ($this->input->post('edit_factory')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('truckings/factories');
        }
        if ($this->form_validation->run() == true && $id = $this->truckings_model->updateFactory($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("factory_edited"));
            redirect('truckings/factories');
        } else {
			$factory = $this->truckings_model->getfactoryByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['factory'] = $factory;
            $this->load->view($this->theme . 'truckings/edit_factory', $this->data);
        }
    }
	
	public function delete_factory($id = NULL)
    {	
		$this->bpas->checkPermissions('factories', true);
		if ($this->truckings_model->deletefactory($id)) {
			echo $this->lang->line("factory_deleted");
		} else {
			$this->session->set_flashdata('warning', lang('factory_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
		
    }
	
	public function factory_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('factories');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->truckings_model->deletefactory($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('factory_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("factory_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('factories'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('contact_person'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('contact_number'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('extra_fee'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('extra_fuel'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('address'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('note'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
						$factory = $this->truckings_model->getFactoryByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $factory->name);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $factory->contact_person);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $factory->contact_number);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($factory->extra_fee));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($factory->extra_fuel));
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->remove_tag($factory->address));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->remove_tag($factory->note));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'factories_' . date('Y_m_d_H_i_s');
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
	
	
	public function dry_ports()
    {
        $this->bpas->checkPermissions('dry_ports');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'truckings', 'page' => lang('trucking')), array('link' => '#', 'page' => lang('dry_ports')));
        $meta = array('page_title' => lang('dry_ports'), 'bc' => $bc);
        $this->page_construct('truckings/dry_ports', $meta, $this->data);
    }

    public function getDryPorts()
    {
		$this->bpas->checkPermissions('dry_ports');
        $this->load->library('datatables');
        $this->datatables
            ->select("tru_dry_ports.id as id,
						tru_dry_ports.name,
						tru_dry_ports.contact_person,
						tru_dry_ports.contact_number,
						tru_dry_ports.address,
						tru_dry_ports.note,
						tru_dry_ports.attachment
					")
            ->from("tru_dry_ports")
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_dry_port") . "' href='" . site_url('truckings/edit_dry_port/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_dry_port") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('truckings/delete_dry_port/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
	
	
	public function add_dry_port()
    {
		$this->bpas->checkPermissions('dry_ports', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
						'name' => $this->input->post('name'),
						'contact_person' => $this->input->post('contact_person'),
						'contact_number' => $this->input->post('contact_number'),
						'lolo_fee' => json_encode($this->input->post('lolo_fee')),
						'address' => $this->input->post('address'),
						'note' => $this->input->post('note')
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
        } elseif ($this->input->post('add_dry_port')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('truckings/dry_ports');
        }
        if ($this->form_validation->run() == true && $id = $this->truckings_model->addDryPort($data)) {
            $this->session->set_flashdata('message', $this->lang->line("dry_port_added"));
            redirect('truckings/dry_ports');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['containers'] = $this->truckings_model->getContainers();
            $this->load->view($this->theme . 'truckings/add_dry_port', $this->data);
        }
    }
	
	public function edit_dry_port($id = false)
    {
		$this->bpas->checkPermissions('dry_ports', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
			$data = array(
						'name' => $this->input->post('name'),
						'contact_person' => $this->input->post('contact_person'),
						'contact_number' => $this->input->post('contact_number'),
						'lolo_fee' => json_encode($this->input->post('lolo_fee')),
						'address' => $this->input->post('address'),
						'note' => $this->input->post('note')
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
        } elseif ($this->input->post('edit_dry_port')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('truckings/dry_ports');
        }
        if ($this->form_validation->run() == true && $id = $this->truckings_model->updateDryPort($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("dry_port_edited"));
            redirect('truckings/dry_ports');
        } else {
			$dry_port = $this->truckings_model->getDryPortByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['dry_port'] = $dry_port;
			$this->data['containers'] = $this->truckings_model->getContainers();
            $this->load->view($this->theme . 'truckings/edit_dry_port', $this->data);
        }
    }
	
	public function delete_dry_port($id = NULL)
    {	
		$this->bpas->checkPermissions('dry_ports', true);
		if ($this->truckings_model->deleteDryPort($id)) {
			echo $this->lang->line("dry_port_deleted");
		} else {
			$this->session->set_flashdata('warning', lang('dry_port_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
		
    }
	
	public function dry_port_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('dry_ports');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->truckings_model->deleteDryPort($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('dry_port_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("dry_port_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('dry_ports'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('contact_person'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('contact_number'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('address'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
						$dry_port = $this->truckings_model->getDryPortByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $dry_port->name);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $dry_port->contact_person);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $dry_port->contact_number);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->remove_tag($dry_port->address));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->remove_tag($dry_port->note));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'dry_ports_' . date('Y_m_d_H_i_s');
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
	
	public function index()
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'truckings', 'page' => lang('trucking')));
        $meta = array('page_title' => lang('truckings'), 'bc' => $bc);
        $this->page_construct('truckings/index', $meta, $this->data);
    }

    public function getTruckings()
    {
		$this->bpas->checkPermissions('index');
        $payments_link = anchor('truckings/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$add_payment = anchor('truckings/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$edit_link = anchor('truckings/edit_trucking/$1', '<i class="fa fa-edit"></i> ' . lang('edit_trucking'), ' class="edit_trucking"');
		$delete_link = "<a href='#' class='delete_trucking po' title='<b>" . $this->lang->line("delete_trucking") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('truckings/delete_trucking/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_trucking') . "</a>";	
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $payments_link . '</li>
						<li>' . $add_payment . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
           
        $this->load->library('datatables');
        $this->datatables ->select("tru_truckings.id as id,
						DATE_FORMAT(".$this->db->dbprefix('tru_truckings').".date, '%Y-%m-%d %T') as date,
						tru_truckings.reference_no,
						companies.company as customer,
						tru_factories.name as factory,
						tru_dry_ports.name as dry_port,
						tru_truckings.container_no,
						tru_trucks.plate,
						products.code as service,
						tru_truckings.payment_status,
						tru_truckings.status,
						IFNULL(".$this->db->dbprefix('tru_truckings').".fee,0) as fee,
						IFNULL(".$this->db->dbprefix('tru_truckings').".lolo,0) as lolo,
						IFNULL(".$this->db->dbprefix('tru_truckings').".extra,0) as extra,
						IFNULL(".$this->db->dbprefix('tru_truckings').".stand_by,0) as stand_by,
						IFNULL(".$this->db->dbprefix('tru_truckings').".booking,0) as booking,
						IFNULL(".$this->db->dbprefix('tru_truckings').".income_amount,0) as income_amount,
						
						IFNULL(".$this->db->dbprefix('tru_truckings').".fuel,0) as fuel,
						IFNULL(".$this->db->dbprefix('tru_truckings').".lolo,0) as lolo_expense,
						IFNULL(".$this->db->dbprefix('tru_truckings').".booking,0) as booking_expense,
						IFNULL(".$this->db->dbprefix('tru_truckings').".commission,0) as commission,
						IFNULL(".$this->db->dbprefix('tru_truckings').".other,0) as other,
						IFNULL(".$this->db->dbprefix('tru_truckings').".expense_amount,0) as expense_amount,
						IFNULL(".$this->db->dbprefix('tru_truckings').".paid,0) as paid,
						IFNULL(".$this->db->dbprefix('tru_truckings').".balance,0) as balance,
						tru_truckings.attachment

					")
            ->from("tru_truckings")
			->join("companies","companies.id = tru_truckings.customer_id","left")
			->join("products","products.id = tru_truckings.service_id","left")
			->join("tru_factories","tru_factories.id = tru_truckings.factory_id","left")
			->join("tru_dry_ports","tru_dry_ports.id = tru_truckings.dry_port_id","left")
			->join("tru_trucks","tru_trucks.id = tru_truckings.truck_id","left");
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
    }
	
	
	public function add_trucking()
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('service', $this->lang->line("service"), 'required');
		$this->form_validation->set_rules('truck', $this->lang->line("truck"), 'required');
		$this->form_validation->set_rules('driver', $this->lang->line("driver"), 'required');
		$this->form_validation->set_rules('container', $this->lang->line("container"), 'required');
		$this->form_validation->set_rules('container_no', $this->lang->line("container_no"), 'required');

        if ($this->form_validation->run() == true) {
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
			$warehouse_id = $this->input->post('warehouse');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('tr',$biller_id);
			$customer_id = $this->input->post('customer');
			$service_id = $this->input->post('service');
			$truck_id = $this->input->post('truck');
			$driver_id = $this->input->post('driver');
			$container_id = $this->input->post('container');
			$dry_port_id = $this->input->post('dry_port');
			$factory_id = $this->input->post('factory');
			$container_no = $this->input->post('container_no');
			$fee = $this->input->post('fee');
			$lolo = $this->input->post('lolo');
			$extra = $this->input->post('extra');
			$stand_by = $this->input->post('stand_by');
			$booking = $this->input->post('booking');
			$commission = $this->input->post('commission');
			$fuel = $this->input->post('fuel');
			$other = $this->input->post('other');
			$note = $this->input->post('note');
			$income_amount = $fee + $lolo + $extra + $stand_by + $booking;
			$expense_amount =  $lolo + $commission + $other + $booking;
			$stockmoves = false;
			$accTrans = false;
            $data = array(
				'date' => $date,
                'reference_no' => $reference_no,
                'biller_id' => $biller_id,
				'warehouse_id' => $warehouse_id,
                'customer_id' => $customer_id,
                'service_id' => $service_id,
                'truck_id' => $truck_id,
				'driver_id' => $driver_id,
				'container_id' => $container_id,
				'dry_port_id' => $dry_port_id,
				'factory_id' => $factory_id,
				'container_no' => $container_no,
				'fee' => $fee,
				'lolo' => $lolo,
				'extra' => $extra,
				'stand_by' => $stand_by,
				'booking' => $booking,
				'commission' => $commission,
				'fuel' => $fuel,
				'other' => $other,
				'income_amount' => $income_amount,
				'expense_amount' => $expense_amount,
				'note' => $note,
				'paid' => 0,
				'balance' => $expense_amount,
				'payment_status' => ($expense_amount > 0 ? "pending" : "paid"),
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
			
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$truckingAcc = $this->site->getAccountSettingByBiller($biller_id);
					if($income_amount > 0){
						$accTrans[] = array(
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->ar_acc,
							'amount' => $income_amount,
							'narrative' => "Trucking Sale",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					if($fee > 0){
						$serviceAcc = $this->site->getProductAccByProductId($service_id);
						$product = $this->site->getProductByID($service_id);
						$accTrans[] = array(
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $serviceAcc->sale_acc,
							'amount' => $fee * (-1),
							'narrative' => 'Product Code: '.$product->code.'#'.'Qty: 1#'.'Price: '.$fee,
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					if($lolo > 0){
						$accTrans[] = array(
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->lolo_acc,
							'amount' => $lolo * (-1),
							'narrative' => "LOLO Income",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						
						$accTrans[] = array(
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->lolo_expense_acc,
							'amount' => $lolo,
							'narrative' => "LOLO Expense",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					
					if($booking > 0){
						$accTrans[] = array(
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->booking_acc,
							'amount' => $booking * (-1),
							'narrative' => "Booking Income",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						
						$accTrans[] = array(
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->booking_expense_acc,
							'amount' => $booking,
							'narrative' => "Booking Expense",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					
					if($extra > 0){
						$accTrans[] = array(
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->extra_acc,
							'amount' => $extra * (-1),
							'narrative' => "Extra Income",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					if($stand_by > 0){
						$accTrans[] = array(
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->stand_by_acc,
							'amount' => $stand_by * (-1),
							'narrative' => "Stand By Income",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					if($expense_amount > 0){
						$accTrans[] = array(
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->ap_acc,
							'amount' => $expense_amount * (-1),
							'narrative' => "Trucking Expense",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					if($commission > 0){
						$accTrans[] = array(
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->commission_acc,
							'amount' => $commission,
							'narrative' => "Commission",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					if($other > 0){
						$data["other_account"] = $this->input->post("other_account");
						$accTrans[] = array(
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $this->input->post("other_account"),
							'amount' => $other,
							'narrative' => "Other Expense",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
				}
			//=====end accountig=====//
			
			if($fuel > 0){
				$truck_info = $this->truckings_model->getTruckByID($truck_id);
				$product_info = $this->site->getProductByID($truck_info->fuel_id);
				$product_unit = $this->site->getProductUnit($product_info->id,$product_info->unit);
				$real_unit_cost = $this->site->getAVGCost($product_info->id,$date);
				if($product_info){
					$stockmoves[] = array(
						'transaction' => 'Trucking',
						'product_id' => $product_info->id,
						'product_type'    => $product_info->type,
						'product_code' => $product_info->code,
						'quantity' => $fuel * (-1),
						'unit_quantity' => $product_unit->unit_qty,
						'unit_code' => $product_unit->code,
						'unit_id' => $product_info->unit,
						'warehouse_id' => $warehouse_id,
						'date' => $date,
						'real_unit_cost' => $real_unit_cost,
						'reference_no' => $reference_no,
						'user_id' => $this->session->userdata('user_id'),
					);
					if($this->Settings->accounting == 1){
						$productAcc = $this->site->getProductAccByProductId($product_info->id);
						$accTrans[] = array(
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $productAcc->stock_acc,
							'amount' => ($fuel * $real_unit_cost * (-1)),
							'narrative' => "Fuel Expense",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						$accTrans[] = array(
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $productAcc->cost_acc,
							'amount' => ($fuel * $real_unit_cost),
							'narrative' => "Fuel Expense",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
				}
			}
			
        }
        if ($this->form_validation->run() == true && $trucking_id = $this->truckings_model->addTrucking($data,$accTrans, $stockmoves)) {
            $this->session->set_userdata('remove_trk', 1);
            $this->session->set_flashdata('message', $this->lang->line("trucking_added") ." ". $reference);
			if($this->input->post('add_trucking_next')){
				redirect('truckings/add_trucking');
			}else{
				redirect('truckings');
			}
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['billers'] =  $this->site->getBillers();
			$this->data['customers'] = $this->truckings_model->getCustomers();
			$this->data['drivers'] = $this->truckings_model->getDrivers();
			$this->data['trucks'] = $this->truckings_model->getTrucks();
			$this->data['containers'] = $this->truckings_model->getContainers();
			$this->data['factories'] = $this->truckings_model->getFactories();
			$this->data['dry_ports'] = $this->truckings_model->getDryPorts();
			$this->data['services'] = $this->truckings_model->getProducts("service");
			$this->data['other_acc'] = $this->Settings->accounting == 1 ? $this->site->getAccount(array('EX')) : false;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('trucking')), array('link' => '#', 'page' => lang('add_trucking')));
			$meta = array('page_title' => lang('add_trucking'), 'bc' => $bc);
            $this->page_construct('truckings/add_trucking', $meta, $this->data);
        }
    }
	
	
	public function edit_trucking($id = false)
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('service', $this->lang->line("service"), 'required');
		$this->form_validation->set_rules('truck', $this->lang->line("truck"), 'required');
		$this->form_validation->set_rules('driver', $this->lang->line("driver"), 'required');
		$this->form_validation->set_rules('container', $this->lang->line("container"), 'required');
		$this->form_validation->set_rules('container_no', $this->lang->line("container_no"), 'required');

        if ($this->form_validation->run() == true) {
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
			$warehouse_id = $this->input->post('warehouse');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('tr',$biller_id);
			$customer_id = $this->input->post('customer');
			$service_id = $this->input->post('service');
			$truck_id = $this->input->post('truck');
			$driver_id = $this->input->post('driver');
			$container_id = $this->input->post('container');
			$dry_port_id = $this->input->post('dry_port');
			$factory_id = $this->input->post('factory');
			$container_no = $this->input->post('container_no');
			$fee = $this->input->post('fee');
			$lolo = $this->input->post('lolo');
			$extra = $this->input->post('extra');
			$stand_by = $this->input->post('stand_by');
			$booking = $this->input->post('booking');
			$commission = $this->input->post('commission');
			$fuel = $this->input->post('fuel');
			$other = $this->input->post('other');
			$note = $this->input->post('note');
			$income_amount = $fee + $lolo + $extra + $stand_by + $booking;
			$expense_amount =  $lolo + $commission + $other + $booking;
            $data = array(
				'date' => $date,
                'reference_no' => $reference_no,
                'biller_id' => $biller_id,
				'warehouse_id' => $warehouse_id,
                'customer_id' => $customer_id,
                'service_id' => $service_id,
                'truck_id' => $truck_id,
				'driver_id' => $driver_id,
				'container_id' => $container_id,
				'dry_port_id' => $dry_port_id,
				'factory_id' => $factory_id,
				'container_no' => $container_no,
				'fee' => $fee,
				'lolo' => $lolo,
				'extra' => $extra,
				'stand_by' => $stand_by,
				'booking' => $booking,
				'commission' => $commission,
				'fuel' => $fuel,
				'other' => $other,
				'income_amount' => $income_amount,
				'expense_amount' => $expense_amount,
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
			
			
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$truckingAcc = $this->site->getAccountSettingByBiller($biller_id);
					if($income_amount > 0){
						$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->ar_acc,
							'amount' => $income_amount,
							'narrative' => "Trucking Sale",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					if($fee > 0){
						$serviceAcc = $this->site->getProductAccByProductId($service_id);
						$product = $this->site->getProductByID($service_id);
						$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $serviceAcc->sale_acc,
							'amount' => $fee * (-1),
							'narrative' => 'Product Code: '.$product->code.'#'.'Qty: 1#'.'Price: '.$fee,
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					if($lolo > 0){
						$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->lolo_acc,
							'amount' => $lolo * (-1),
							'narrative' => "LOLO Income",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						
						$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->lolo_expense_acc,
							'amount' => $lolo,
							'narrative' => "LOLO Expense",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					
					if($booking > 0){
						$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->booking_acc,
							'amount' => $booking * (-1),
							'narrative' => "Booking Income",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						
						$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->booking_expense_acc,
							'amount' => $booking,
							'narrative' => "Booking Expense",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					
					if($extra > 0){
						$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->extra_acc,
							'amount' => $extra * (-1),
							'narrative' => "Extra Income",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					if($stand_by > 0){
						$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->stand_by_acc,
							'amount' => $stand_by * (-1),
							'narrative' => "Stand By Income",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					if($expense_amount > 0){
						$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->ap_acc,
							'amount' => $expense_amount * (-1),
							'narrative' => "Trucking Expense",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					if($commission > 0){
						$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->commission_acc,
							'amount' => $commission,
							'narrative' => "Commission",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					if($other > 0){
						$data["other_account"] = $this->input->post("other_account");
						$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $this->input->post("other_account"),
							'amount' => $other,
							'narrative' => "Other Expense",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
				}
			//=====end accountig=====//
			if($fuel > 0){
				$truck_info = $this->truckings_model->getTruckByID($truck_id);
				$product_info = $this->site->getProductByID($truck_info->fuel_id);
				$product_unit = $this->site->getProductUnit($product_info->id,$product_info->unit);
				$real_unit_cost = $this->site->getAVGCost($product_info->id,$date,"Trucking",$id);
				if($product_info){
					$stockmoves[] = array(
						'transaction_id' => $id,	
						'transaction' => 'Trucking',
						'product_id' => $product_info->id,
						'product_type'    => $product_info->type,
						'product_code' => $product_info->code,
						'quantity' => $fuel * (-1),
						'unit_quantity' => $product_unit->unit_qty,
						'unit_code' => $product_unit->code,
						'unit_id' => $product_info->unit,
						'warehouse_id' => $warehouse_id,
						'date' => $date,
						'real_unit_cost' => $real_unit_cost,
						'reference_no' => $reference_no,
						'user_id' => $this->session->userdata('user_id'),
					);
					if($this->Settings->accounting == 1){
						$productAcc = $this->site->getProductAccByProductId($product_info->id);
						$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $productAcc->stock_acc,
							'amount' => ($fuel * $real_unit_cost * (-1)),
							'narrative' => "Fuel Expense",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Trucking',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $productAcc->cost_acc,
							'amount' => ($fuel * $real_unit_cost),
							'narrative' => "Fuel Expense",
							'description' => $note,
							'biller_id' => $biller_id,
							'customer_id' => $customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
				}
			}
			
        }
        if ($this->form_validation->run() == true && $trucking_id = $this->truckings_model->updateTrucking($id,$data,$accTrans,$stockmoves)) {
            $this->session->set_userdata('remove_trk', 1);
            $this->session->set_flashdata('message', $this->lang->line("trucking_added") ." ". $reference);
			redirect('truckings');
        } else {
			$this->session->set_userdata('remove_trk', 1);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$trucking = $this->truckings_model->getTruckingByID($id);
			if($trucking->status == "completed"){
				$this->session->set_flashdata('error',lang('trucking_cannot_edit'));
				$this->bpas->md();
			}
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['billers'] =  $this->site->getBillers();
			$this->data['customers'] = $this->truckings_model->getCustomers();
			$this->data['drivers'] = $this->truckings_model->getDrivers();
			$this->data['trucks'] = $this->truckings_model->getTrucks();
			$this->data['containers'] = $this->truckings_model->getContainers();
			$this->data['factories'] = $this->truckings_model->getFactories();
			$this->data['dry_ports'] = $this->truckings_model->getDryPorts();
			$this->data['services'] = $this->truckings_model->getProducts("service");
			$this->data['other_acc'] = $this->Settings->accounting == 1 ? $this->site->getAccount(array('EX')) : false;
			$this->data['trucking'] = $trucking;
			$this->session->set_userdata('remove_trk', 1);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('trucking')), array('link' => '#', 'page' => lang('edit_trucking')));
			$meta = array('page_title' => lang('edit_trucking'), 'bc' => $bc);
            $this->page_construct('truckings/edit_trucking', $meta, $this->data);
        }
    }
	
	public function delete_trucking($id = null)
    {
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if ($this->truckings_model->deleteTrucking($id)) {
			echo $this->lang->line("trucking_deleted");
		} else {
			$this->session->set_flashdata('error', lang('trucking_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
    }

	public function trucking_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_trucking');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->truckings_model->deleteTrucking($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('trucking_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("trucking_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('truckings'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('service'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('truck'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('container_no'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('fee'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('lolo'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('extra'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('stand_by'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('commission'));
					$this->excel->getActiveSheet()->SetCellValue('L1', lang('fuel'));
					$this->excel->getActiveSheet()->SetCellValue('M1', lang('other'));
					$this->excel->getActiveSheet()->SetCellValue('N1', lang('income_amount'));
					$this->excel->getActiveSheet()->SetCellValue('O1', lang('expense_amount'));
					$this->excel->getActiveSheet()->SetCellValue('P1', lang('payment_status'));
					$this->excel->getActiveSheet()->SetCellValue('Q1', lang('sale_status'));
					
					$this->db->select("tru_truckings.id as id,
								DATE_FORMAT(".$this->db->dbprefix('tru_truckings').".date, '%Y-%m-%d %T') as date,
								tru_truckings.reference_no,
								companies.company as customer,
								products.name as service,
								tru_trucks.plate,
								tru_truckings.container_no,
								IFNULL(".$this->db->dbprefix('tru_truckings').".fee,0) as fee,
								IFNULL(".$this->db->dbprefix('tru_truckings').".lolo,0) as lolo,
								IFNULL(".$this->db->dbprefix('tru_truckings').".extra,0) as extra,
								IFNULL(".$this->db->dbprefix('tru_truckings').".stand_by,0) as stand_by,
								IFNULL(".$this->db->dbprefix('tru_truckings').".commission,0) as commission,
								IFNULL(".$this->db->dbprefix('tru_truckings').".fuel,0) as fuel,
								IFNULL(".$this->db->dbprefix('tru_truckings').".other,0) as other,
								IFNULL(".$this->db->dbprefix('tru_truckings').".income_amount,0) as income_amount,
								IFNULL(".$this->db->dbprefix('tru_truckings').".expense_amount,0) as expense_amount,
								tru_truckings.payment_status,
								tru_truckings.status
							")
					->from("tru_truckings")
					->join("companies","companies.id = tru_truckings.customer_id","left")
					->join("products","products.id = tru_truckings.service_id","left")
					->join("tru_trucks","tru_trucks.id = tru_truckings.truck_id","left")
					->where_in("tru_truckings.id",$_POST['val']);
					$q = $this->db->get();
                    $row = 2;
                    if ($q->num_rows() > 0) {
						foreach (($q->result()) as $trucking) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($trucking->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $trucking->reference_no);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $trucking->customer);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $trucking->service);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $trucking->plate);
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $trucking->container_no);
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($trucking->fee));
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($trucking->lolo));
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($trucking->extra));
							$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($trucking->stand_by));
							$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($trucking->commission));
							$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($trucking->fuel));
							$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->formatDecimal($trucking->other));
							$this->excel->getActiveSheet()->SetCellValue('N' . $row, $this->bpas->formatDecimal($trucking->income_amount));
							$this->excel->getActiveSheet()->SetCellValue('O' . $row, $this->bpas->formatDecimal($trucking->expense_amount));
							$this->excel->getActiveSheet()->SetCellValue('P' . $row, lang($trucking->payment_status));
							$this->excel->getActiveSheet()->SetCellValue('Q' . $row, lang($trucking->status));
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
					$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'truckings_' . date('Y_m_d_H_i_s');
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
	
	public function modal_view($id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $trucking = $this->truckings_model->getTruckingByID($id);
        $this->data['biller'] = $this->site->getCompanyByID($trucking->biller_id);
        $this->data['trucking'] = $trucking;
		$this->data['created_by'] = $this->site->getUserByID($trucking->created_by);
        $this->load->view($this->theme . 'truckings/modal_view', $this->data);
    }
	
	public function payments($id = false)
    {
        $this->bpas->checkPermissions("payments", true);
		$this->data['payments'] = $this->truckings_model->getPaymentByTrucking($id);
        $this->load->view($this->theme . 'truckings/payments', $this->data);
    }
	
	
	public function add_payment($id = false)
    {
		$this->bpas->checkPermissions('payments', true);	
        $this->bpas->checkPermissions('add_trucking', true);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		$trucking = $this->truckings_model->getTruckingByID($id);
        if ($this->form_validation->run() == true) {
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$currencies = array();
			$camounts = $this->input->post("c_amount");
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$currencies[] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			$paybacks = false;
			$accTrans = false;
			$driver_id = $this->input->post('driver_id');
			if($driver_id > 0){
				$paymentAcc = $this->site->getAccountSettingByBiller($trucking->biller_id);
				$paying_from = $paymentAcc->cash_advance_acc;
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$trucking->biller_id);
				$cash_advance = $this->truckings_model->getCashAdvanceBalanceByDriver($driver_id);
				$paid_amount = $this->bpas->formatDecimal($this->input->post("amount-paid"));
				if($paid_amount > $this->bpas->formatDecimal($cash_advance->amount)){
					$paybacks[] = array(
								'date' => $date,
								'reference_no' => $trucking->reference_no,
								'cash_advance_id' => $cash_advance->id,
								'amount' => $paid_amount,
								'paid_by' => "Trucking",
								'note' => $this->bpas->clear_tags($this->input->post('note')),
								'created_by' => $this->session->userdata('user_id'),
								'created_at' => date('Y-m-d H:i:s'),
								'currencies' => json_encode($currencies),
							);
				}else{
					$cash_advances = $this->truckings_model->getCashAdvanceBalancesByDriver($driver_id);
					foreach($cash_advances as $cash_advance){
						if($paid_amount > 0 && $this->bpas->formatDecimal($cash_advance->balance) >= $paid_amount){
							$paybacks[] = array(
								'date' => $date,
								'reference_no' => $trucking->reference_no,
								'cash_advance_id' => $cash_advance->id,
								'amount' => $paid_amount,
								'paid_by' => "Trucking",
								'note' => $this->bpas->clear_tags($this->input->post('note')),
								'created_by' => $this->session->userdata('user_id'),
								'created_at' => date('Y-m-d H:i:s'),
								'currencies' => json_encode($currencies),
							);
							$paid_amount = 0;
						}else if($paid_amount > 0 && $this->bpas->formatDecimal($cash_advance->balance) > 0){
							$paybacks[] = array(
								'date' => $date,
								'reference_no' => $trucking->reference_no,
								'cash_advance_id' => $cash_advance->id,
								'amount' => $cash_advance->balance,
								'paid_by' => "Trucking",
								'note' => $this->bpas->clear_tags($this->input->post('note')),
								'created_by' => $this->session->userdata('user_id'),
								'created_at' => date('Y-m-d H:i:s'),
								'currencies' => json_encode($currencies),
							);
							$paid_amount = $paid_amount - $cash_advance->balance;
						}
					}
				}
			}else{
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$trucking->biller_id);
				$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
				$paying_from = $cash_account->account_code;
			}
			
			$payment = array(
                'date' => $date,
                'reference_no' => $reference_no,
				'transaction' => "Trucking Expense",
				'transaction_id' => $id,
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->bpas->clear_tags($this->input->post('note')),
                'created_by' => $this->session->userdata('user_id'),
                'type' => "expense",
				'account_code' => $paying_from,
				'driver_id' => $driver_id,
				'currencies' => json_encode($currencies),
            );
			
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$paymentAcc = $paymentAcc ? $paymentAcc : $this->site->getAccountSettingByBiller($trucking->biller_id);
					$accTrans[] = array(
							'transaction' => 'Payment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paymentAcc->ap_acc,
							'amount' => $this->input->post('amount-paid'),
							'narrative' => "Trucking Expense Payment ".$trucking->reference_no,
							'description' => $this->input->post('note'),
							'biller_id' => $trucking->biller_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					$accTrans[] = array(
							'transaction' => 'Payment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paying_from,
							'amount' => $this->input->post('amount-paid') * (-1),
							'narrative' => "Trucking Expense Payment ".$trucking->reference_no,
							'description' => $this->input->post('note'),
							'biller_id' => $trucking->biller_id,
							'user_id' => $this->session->userdata('user_id'),
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
        if ($this->form_validation->run() == true && $this->truckings_model->addPayment($payment, $accTrans, $paybacks)) {
			$this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['trucking'] = $trucking;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->data['cash_advances'] = $this->truckings_model->getCashAdvanceDrivers(false,"active");
            $this->load->view($this->theme . 'truckings/add_payment', $this->data);
        }
    }
	
	
	public function edit_payment($id = false)
    {
		$this->bpas->checkPermissions('payments', true);	
        $this->bpas->checkPermissions('edit_trucking', true);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		$payment = $this->truckings_model->getPaymentByID($id);
        if ($this->form_validation->run() == true) {
			$trucking = $this->truckings_model->getTruckingByID($payment->transaction_id);
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$currencies = array();
			$camounts = $this->input->post("c_amount");
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$currencies[] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			
			$paybacks = false;
			$accTrans = false;
			$driver_id = $this->input->post('driver_id');
			if($driver_id > 0){
				$paymentAcc = $this->site->getAccountSettingByBiller($trucking->biller_id);
				$paying_from = $paymentAcc->cash_advance_acc;
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$trucking->biller_id);
				$cash_advance = $this->truckings_model->getCashAdvanceBalanceByDriver($driver_id,$id);
				$paid_amount = $this->bpas->formatDecimal($this->input->post("amount-paid"));
				if($paid_amount > $this->bpas->formatDecimal($cash_advance->amount)){
					$paybacks[] = array(
								'payment_id' => $id,
								'date' => $date,
								'reference_no' => $trucking->reference_no,
								'cash_advance_id' => $cash_advance->id,
								'amount' => $paid_amount,
								'paid_by' => "Trucking",
								'note' => $this->bpas->clear_tags($this->input->post('note')),
								'created_by' => $this->session->userdata('user_id'),
								'created_at' => date('Y-m-d H:i:s'),
								'currencies' => json_encode($currencies),
							);
				}else{
					$cash_advances = $this->truckings_model->getCashAdvanceBalancesByDriver($driver_id,$id);
					foreach($cash_advances as $cash_advance){
						if($paid_amount > 0 && $this->bpas->formatDecimal($cash_advance->balance) >= $paid_amount){
							$paybacks[] = array(
								'payment_id' => $id,
								'date' => $date,
								'reference_no' => $trucking->reference_no,
								'cash_advance_id' => $cash_advance->id,
								'amount' => $paid_amount,
								'paid_by' => "Trucking",
								'note' => $this->bpas->clear_tags($this->input->post('note')),
								'updated_by' => $this->session->userdata('user_id'),
								'updated_at' => date('Y-m-d H:i:s'),
								'currencies' => json_encode($currencies),
							);
							$paid_amount = 0;
						}else if($paid_amount > 0 && $this->bpas->formatDecimal($cash_advance->balance) > 0){
							$paybacks[] = array(
								'payment_id' => $id,
								'date' => $date,
								'reference_no' => $trucking->reference_no,
								'cash_advance_id' => $cash_advance->id,
								'amount' => $cash_advance->balance,
								'paid_by' => "Trucking",
								'note' => $this->bpas->clear_tags($this->input->post('note')),
								'updated_by' => $this->session->userdata('user_id'),
								'updated_at' => date('Y-m-d H:i:s'),
								'currencies' => json_encode($currencies),
							);
							$paid_amount = $paid_amount - $cash_advance->balance;
						}
					}
				}
			}else{
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$trucking->biller_id);
				$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
				$paying_from = $cash_account->account_code;
			}
			
			$payment = array(
                'date' => $date,
                'reference_no' => $reference_no,
				'transaction' => "Trucking Expense",
				'transaction_id' => $trucking->id,
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->bpas->clear_tags($this->input->post('note')),
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
                'type' => "expense",
				'account_code' => $paying_from,
				'driver_id' => $driver_id,
				'currencies' => json_encode($currencies),
            );
			
			
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$paymentAcc = $this->site->getAccountSettingByBiller($trucking->biller_id);
					$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Payment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paymentAcc->ap_acc,
							'amount' => $this->input->post('amount-paid'),
							'narrative' => "Trucking Expense Payment ".$trucking->reference_no,
							'description' => $this->input->post('note'),
							'biller_id' => $trucking->biller_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					$accTrans[] = array(
							'transaction_id' => $id,	
							'transaction' => 'Payment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paying_from,
							'amount' => $this->input->post('amount-paid') * (-1),
							'narrative' => "Trucking Expense Payment ".$trucking->reference_no,
							'description' => $this->input->post('note'),
							'biller_id' => $trucking->biller_id,
							'user_id' => $this->session->userdata('user_id'),
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
        if ($this->form_validation->run() == true && $this->truckings_model->updatePayment($id, $payment, $accTrans, $paybacks)) {
			$this->session->set_flashdata('message', lang("payment_edited"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->data['cash_advances'] = $this->truckings_model->getCashAdvanceDrivers($id,false,"active");
            $this->load->view($this->theme . 'truckings/edit_payment', $this->data);
        }
    }
	
	public function delete_payment($id = null)
    {
		$this->bpas->checkPermissions('payments', true);	
        $this->bpas->checkPermissions('delete_trucking', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->truckings_model->deletePayment($id)) {
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function modal_view_payment($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $payment = $this->truckings_model->getPaymentByID($id);
		$trucking = $this->truckings_model->getTruckingByID($payment->transaction_id);
        $this->data['trucking'] = $trucking;
		$this->data['biller'] = $this->site->getCompanyByID($trucking->biller_id);
        $this->data['payment'] = $payment;
		$this->data['created_by'] = $this->site->getUserByID($payment->created_by);
        $this->data['page_title'] = $this->lang->line("payment_note");
        $this->load->view($this->theme . 'truckings/modal_view_payment', $this->data);
    }
	
	
	
	
	public function cash_advances()
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'truckings', 'page' => lang('trucking')), array('link' => '#', 'page' => lang('cash_advances')));
        $meta = array('page_title' => lang('cash_advances'), 'bc' => $bc);
        $this->page_construct('truckings/cash_advances', $meta, $this->data);
    }

    public function getCashAdvances()
    {
		$this->bpas->checkPermissions('cash_advances');
		$payments_link = anchor('truckings/paybacks/$1', '<i class="fa fa-money"></i> ' . lang('view_paybacks'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$add_payment = anchor('truckings/add_payback/$1', '<i class="fa fa-money"></i> ' . lang('add_payback'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$edit_link = anchor('truckings/edit_cash_advance/$1', '<i class="fa fa-edit"></i> ' . lang('edit_cash_advance'), ' class="edit_cash_advance"');
        $delete_link = "<a href='#' class='delete_cash_advance po' title='<b>" . $this->lang->line("delete_cash_advance") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('truckings/delete_cash_advance/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_cash_advance') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $payments_link . '</li>
						<li>' . $add_payment . '</li>
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
           
        $this->load->library('datatables');
        $this->datatables ->select("tru_cash_advances.id as id,
						DATE_FORMAT(".$this->db->dbprefix('tru_cash_advances').".date, '%Y-%m-%d %T') as date,
						tru_cash_advances.reference_no,
						tru_cash_advances.driver_name,
						cash_accounts.name as paid_by,
						tru_cash_advances.note,
						IFNULL(".$this->db->dbprefix('tru_cash_advances').".service,0) as service,
						IFNULL(".$this->db->dbprefix('tru_cash_advances').".amount,0) as amount,
						IFNULL(".$this->db->dbprefix('tru_cash_advances').".paid,0) as paid,
						IFNULL(".$this->db->dbprefix('tru_cash_advances').".balance,0) as balance,
						tru_cash_advances.payment_status,
						tru_cash_advances.attachment
					")
			->join("cash_accounts","cash_accounts.id = tru_cash_advances.paid_by","left")
            ->from("tru_cash_advances");
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
    }
	
	public function add_cash_advance()
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('amount', $this->lang->line("amount"), 'required');
		$this->form_validation->set_rules('driver', $this->lang->line("driver"), 'required');
		$this->form_validation->set_rules('paid_by', $this->lang->line("paid_by"), 'required');

        if ($this->form_validation->run() == true) {
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cv',$biller_id);
			$driver_id = $this->input->post('driver');
			$amount = $this->input->post('amount');
			$service = $this->input->post('service');
			$paid_by = $this->input->post('paid_by');
			$note = $this->bpas->clear_tags($this->input->post('note'));
			$driver = $this->truckings_model->getDriverByID($driver_id);
            $data = array(
				'date' => $date,
				'biller_id' => $biller_id,
                'reference_no' => $reference_no,
				'driver_id' => $driver_id,
				'driver_name' => $driver->full_name,
				'amount' => $amount,
				'service' => $service,
				'paid_by' => $paid_by,
				'note' => $note,
				'paid' => 0,
				'balance' => $amount,
				'payment_status' => "pending",
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
			
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$cash_account = $this->site->getCashAccountByID($paid_by);
					$truckingAcc = $this->site->getAccountSettingByBiller($biller_id);
					
					$accTrans[] = array(
						'transaction' => 'Cash Advance',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $cash_account->account_code,
						'amount' => ($amount + $service) * (-1),
						'narrative' => "Cash Advance ".$driver->full_name,
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					
					$accTrans[] = array(
						'transaction' => 'Cash Advance',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $truckingAcc->cash_advance_acc,
						'amount' => $amount,
						'narrative' => "Cash Advance ".$driver->full_name,
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					
					if($service > 0){
						$accTrans[] = array(
							'transaction_id' => $id,
							'transaction' => 'Cash Advance',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->cash_advance_service_acc,
							'amount' => $service,
							'narrative' => "Cash Advance Service".$driver->full_name,
							'description' => $note,
							'biller_id' => $biller_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					
				}
			//=====end accountig=====//
        }
        if ($this->form_validation->run() == true && $this->truckings_model->addCashAdvance($data,$accTrans)) {
            $this->session->set_userdata('remove_trkca', 1);
            $this->session->set_flashdata('message', $this->lang->line("cash_advance_added") ." ". $reference);
			if($this->input->post('add_cash_advance_next')){
				redirect('truckings/add_cash_advance');
			}else{
				redirect('truckings/cash_advances');
			}
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] =  $this->site->getBillers();
			$this->data['drivers'] = $this->truckings_model->getDrivers();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('trucking')), array('link' => site_url('truckings/cash_advances'), 'page' => lang('cash_advances')), array('link' => '#', 'page' => lang('add_cash_advance')));
			$meta = array('page_title' => lang('add_cash_advance'), 'bc' => $bc);
            $this->page_construct('truckings/add_cash_advance', $meta, $this->data);
        }
    }
	
	public function edit_cash_advance($id = false)
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('amount', $this->lang->line("amount"), 'required');
		$this->form_validation->set_rules('driver', $this->lang->line("driver"), 'required');
		$this->form_validation->set_rules('paid_by', $this->lang->line("paid_by"), 'required');

        if ($this->form_validation->run() == true) {
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cv',$biller_id);
			$driver_id = $this->input->post('driver');
			$amount = $this->input->post('amount');
			$service = $this->input->post('service');
			$paid_by = $this->input->post('paid_by');
			$note = $this->bpas->clear_tags($this->input->post('note'));
			$driver = $this->truckings_model->getDriverByID($driver_id);
            $data = array(
				'date' => $date,
				'biller_id' => $biller_id,
                'reference_no' => $reference_no,
				'driver_id' => $driver_id,
				'driver_name' => $driver->full_name,
				'amount' => $amount,
				'service' => $service,
				'paid_by' => $paid_by,
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
			
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$cash_account = $this->site->getCashAccountByID($paid_by);
					$truckingAcc = $this->site->getAccountSettingByBiller($biller_id);
					
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'Cash Advance',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $cash_account->account_code,
						'amount' => ($amount + $service) * (-1),
						'narrative' => "Cash Advance ".$driver->full_name,
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'Cash Advance',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $truckingAcc->cash_advance_acc,
						'amount' => $amount,
						'narrative' => "Cash Advance ".$driver->full_name,
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					
					if($service > 0){
						$accTrans[] = array(
							'transaction_id' => $id,
							'transaction' => 'Cash Advance',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $truckingAcc->cash_advance_service_acc,
							'amount' => $service,
							'narrative' => "Cash Advance Service".$driver->full_name,
							'description' => $note,
							'biller_id' => $biller_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					}
					
				}
			//=====end accountig=====//
        }
        if ($this->form_validation->run() == true && $this->truckings_model->updateCashAdvance($id,$data,$accTrans)) {
            $this->session->set_userdata('remove_trkca', 1);
            $this->session->set_flashdata('message', $this->lang->line("cash_advance_edited") ." ". $reference);
			redirect('truckings/cash_advances');
        } else {
			$this->session->set_userdata('remove_trkca', 1);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] =  $this->site->getBillers();
			$this->data['drivers'] = $this->truckings_model->getDrivers();
			$this->data['cash_advance'] = $this->truckings_model->getCashAdvanceByID($id);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('trucking')), array('link' => site_url('truckings/cash_advances'), 'page' => lang('cash_advances')), array('link' => '#', 'page' => lang('edit_cash_advance')));
			$meta = array('page_title' => lang('edit_cash_advance'), 'bc' => $bc);
            $this->page_construct('truckings/edit_cash_advance', $meta, $this->data);
        }
    }
	
	public function delete_cash_advance($id = NULL)
    {	
		$this->bpas->checkPermissions('delete_cash_advance', true);
		if ($this->truckings_model->deleteCashAdvance($id)) {
			echo $this->lang->line("cash_advance_deleted");
		} else {
			$this->session->set_flashdata('error', lang('cash_advance_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
		
    }
	
	public function cash_advance_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_cash_advance');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->truckings_model->deleteCashAdvance($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('cash_advance_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("cash_advance_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('cash_advances'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('driver'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('paid_by'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('service'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('amount'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('payment_status'));
					
					$this->db->select("tru_cash_advances.id as id,
								DATE_FORMAT(".$this->db->dbprefix('tru_cash_advances').".date, '%Y-%m-%d %T') as date,
								tru_cash_advances.reference_no,
								tru_cash_advances.driver_name,
								cash_accounts.name as paid_by,
								tru_cash_advances.note,
								IFNULL(".$this->db->dbprefix('tru_cash_advances').".service,0) as service,
								IFNULL(".$this->db->dbprefix('tru_cash_advances').".amount,0) as amount,
								IFNULL(".$this->db->dbprefix('tru_cash_advances').".paid,0) as paid,
								IFNULL(".$this->db->dbprefix('tru_cash_advances').".balance,0) as balance,
								tru_cash_advances.payment_status
							")
					->join("cash_accounts","cash_accounts.id = tru_cash_advances.paid_by","left")
					->from("tru_cash_advances")
					->where_in("tru_cash_advances.id",$_POST['val']);
					$q = $this->db->get();
                    $row = 2;
                    if ($q->num_rows() > 0) {
						foreach (($q->result()) as $cash_advance) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($cash_advance->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $cash_advance->reference_no);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $cash_advance->driver_name);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $cash_advance->paid_by);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->remove_tag($cash_advance->note));
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($cash_advance->service));
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($cash_advance->amount));
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($cash_advance->paid));
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($cash_advance->balance));
							$this->excel->getActiveSheet()->SetCellValue('J' . $row, lang($cash_advance->payment_status));
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
					$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'cash_advances_' . date('Y_m_d_H_i_s');
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
	
	public function modal_view_cash_advance($id = false){
		$this->bpas->checkPermissions('cash_advances', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $cash_advance = $this->truckings_model->getCashAdvanceByID($id);
		$this->data['cash_advance'] = $cash_advance;
        $this->data['biller'] = $this->site->getCompanyByID($cash_advance->biller_id);
        $this->data['created_by'] = $this->site->getUser($cash_advance->created_by);
        $this->load->view($this->theme . 'truckings/modal_view_cash_advance', $this->data);
	}
	
	public function paybacks($id = false)
    {
        $this->bpas->checkPermissions("cash_advances", true);
		$this->bpas->checkPermissions('paybacks', true);	
		$this->data['paybacks'] = $this->truckings_model->getPaybacksByCashAdvance($id);
        $this->load->view($this->theme . 'truckings/paybacks', $this->data);
    }
	
	
	public function add_payback($id = false)
    {
		$this->bpas->checkPermissions('paybacks', true);	
        $this->bpas->checkPermissions('add_cash_advance', true);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		$cash_advance = $this->truckings_model->getCashAdvanceByID($id);
        if ($this->form_validation->run() == true) {
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$currencies = array();
			$camounts = $this->input->post("c_amount");
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$currencies[] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$cash_advance->biller_id);
			$data = array(
                'date' => $date,
                'reference_no' => $reference_no,
				'cash_advance_id' => $id,
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->bpas->clear_tags($this->input->post('note')),
                'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s'),
				'currencies' => json_encode($currencies),
            );
			
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
					$paybackAcc = $this->site->getAccountSettingByBiller($cash_advance->biller_id);
					$accTrans[] = array(
							'transaction' => 'Payback',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $cash_account->account_code,
							'amount' => $this->input->post('amount-paid'),
							'narrative' => "Payback Cash Advance ".$cash_advance->reference_no,
							'description' => $this->input->post('note'),
							'biller_id' => $cash_advance->biller_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					$accTrans[] = array(
							'transaction' => 'Payback',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paybackAcc->cash_advance_acc,
							'amount' => $this->input->post('amount-paid') * (-1),
							'narrative' => "Payback Cash Advance ".$cash_advance->reference_no,
							'description' => $this->input->post('note'),
							'biller_id' => $cash_advance->biller_id,
							'user_id' => $this->session->userdata('user_id'),
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
                $data['attachment'] = $photo;
            }
			
        } elseif ($this->input->post('add_payback')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->truckings_model->addPayback($data, $accTrans)) {
			$this->session->set_flashdata('message', lang("payback_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['cash_advance'] = $cash_advance;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'truckings/add_payback', $this->data);
        }
    }
	
	
	public function edit_payback($id = false)
    {
		$this->bpas->checkPermissions('paybacks', true);	
        $this->bpas->checkPermissions('edit_cash_advance', true);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		$payback = $this->truckings_model->getPaybackByID($id);
        if ($this->form_validation->run() == true) {
			$cash_advance = $this->truckings_model->getCashAdvanceByID($payback->cash_advance_id);
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$currencies = array();
			$camounts = $this->input->post("c_amount");
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$currencies[] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$cash_advance->biller_id);
			$data = array(
                'date' => $date,
                'reference_no' => $reference_no,
				'cash_advance_id' => $cash_advance->id,
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->bpas->clear_tags($this->input->post('note')),
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
				'currencies' => json_encode($currencies),
            );
			
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
					$paybackAcc = $this->site->getAccountSettingByBiller($cash_advance->biller_id);
					$accTrans[] = array(
							'transaction_id' => $id,
							'transaction' => 'Payback',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $cash_account->account_code,
							'amount' => $this->input->post('amount-paid'),
							'narrative' => "Payback Cash Advance ".$cash_advance->reference_no,
							'description' => $this->input->post('note'),
							'biller_id' => $cash_advance->biller_id,
							'user_id' => $this->session->userdata('user_id'),
						);
					$accTrans[] = array(
							'transaction_id' => $id,
							'transaction' => 'Payback',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paybackAcc->cash_advance_acc,
							'amount' => $this->input->post('amount-paid') * (-1),
							'narrative' => "Payback Cash Advance ".$cash_advance->reference_no,
							'description' => $this->input->post('note'),
							'biller_id' => $cash_advance->biller_id,
							'user_id' => $this->session->userdata('user_id'),
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
                $data['attachment'] = $photo;
            }
			
        } elseif ($this->input->post('edit_payback')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->truckings_model->updatePayback($id, $data, $accTrans)) {
			$this->session->set_flashdata('message', lang("payback_edited"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payback'] = $payback;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'truckings/edit_payback', $this->data);
        }
    }
	
	public function delete_payback($id = null)
    {
		$this->bpas->checkPermissions('paybacks', true);	
        $this->bpas->checkPermissions('delete_cash_advance', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->truckings_model->deletePayback($id)) {
            $this->session->set_flashdata('message', lang("payback_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	
	public function add_sale(){
		$this->bpas->checkPermissions("add",false,"sales");
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
        if ($this->form_validation->run() == true) {
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
            $warehouse_id = $this->input->post('warehouse');
			$customer_id = $this->input->post('customer');
			$note = $this->input->post('note');
			$staff_note = $this->input->post('staff_note');
			$from_date = $this->bpas->fld(trim($this->input->post('from_date')));
			$to_date = $this->bpas->fld(trim($this->input->post('to_date')));
            $payment_term = $this->input->post('payment_term');
			$payment_term_info = $this->site->getPaymentTermsByID($payment_term);
            if($payment_term_info){
				if($payment_term_info->term_type=='end_month'){
					$due_date = date("Y-m-t", strtotime($date));
				}else{
					$due_date =  date('Y-m-d', strtotime('+' . $payment_term_info->due_day . ' days', strtotime($date)));
				}
			}else{
				$due_date = null;
			}
			$biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
			$tax_detail = $this->site->getTaxRateByID($this->input->post('order_tax'));
			if($tax_detail && $tax_detail->rate > 0){
				$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('tax_so',$biller_id);
			}else{
				$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so',$biller_id);
			}
			$i = isset($_POST['trucking_id']) ? sizeof($_POST['trucking_id']) : 0;
			$total = 0;
			$order_tax = 0;
			$order_discount = 0;
			$total_items = 0;
			$percentage = '%';
			$products = false;
			for ($r = 0; $r < $i; $r++) {
				$trucking_id = $_POST['trucking_id'][$r];
				$trucking_date = $this->bpas->fsd($_POST['trucking_date'][$r]);
				$trucking_reference_no = $_POST['trucking_reference_no'][$r];
				$truck = $_POST['truck'][$r];
				$service_id = $_POST['service_id'][$r];
				$product_code = $_POST['product_code'][$r];
				$product_name = $_POST['product_name'][$r];
				$container = $_POST['container'][$r];
				$dry_port = $_POST['dry_port'][$r];
				$factory = $_POST['factory'][$r];
				$container_no = $_POST['container_no'][$r];
				$trucking = $_POST['trucking'][$r];
				$lolo = $_POST['lolo'][$r];
				$extra = $_POST['extra'][$r];
				$stand_by = $_POST['stand_by'][$r];
				$booking = $_POST['booking'][$r];
				$subtotal = $_POST['subtotal'][$r];
				$tru_products[] = array(
					'trucking_id' => $trucking_id,
					'date' => $trucking_date,
					'reference_no' => $trucking_reference_no,
					'truck' => $truck,
					'service_id' => $service_id,
					'service_code' => $product_code,
					'service_name' => $product_name,
					'container' => $container,
					'dry_port' => $dry_port,
					'factory' => $factory,
					'container_no' => $container_no,
					'trucking' => $trucking,
					'lolo' => $lolo,
					'extra' => $extra,
					'stand_by' => $stand_by,
					'booking' => $booking,
					'subtotal' => $subtotal,
				);
				
				
				$products[] = array(
					'product_id' => $service_id,
					'product_code' => $product_code,
					'product_name' => $product_name,
					'product_type' => "service",
					'net_unit_price' => $subtotal,
					'unit_price' => $subtotal,
					'real_unit_price' => $subtotal,
					'quantity' => 1,
					'unit_quantity' => 1,
					'warehouse_id' => $warehouse_id,
					'subtotal' => $subtotal
				);
	
				$total += $subtotal;
				$total_items++;
			}
			if (!$tru_products) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
			} else {
				krsort($tru_products);
			}
			if ($this->input->post('order_discount')) {
                $order_discount_id = $this->input->post('order_discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = (($total) * (Float) ($ods[0])) / 100;
                } else {
                    $order_discount = $order_discount_id;
                }
            } else {
                $order_discount_id = null;
            }
			
			if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = ((($total) - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }
			$grand_total = ($total) + $order_tax - $order_discount;
			$data = array(
				'date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
				'staff_note' => $staff_note,
                'total' => $total,
				'order_tax_id' => $order_tax_id,
				'order_tax' => $order_tax,
				'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $order_discount,
                'grand_total' => $grand_total,
                'sale_status' => "completed",
				'total_items' => $total_items,
				'type' => 'trucking',
				'payment_status' => 'pending',
				'delivery_status' => 'completed',
                'payment_term' => $payment_term,
                'due_date' => $due_date,
				'paid' => 0,
				'created_by' => $this->session->userdata('user_id'),
				'from_date' => $from_date,
				'to_date' => $to_date,
            );
			if($this->Settings->accounting == 1){
				$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
				if($order_discount != 0){
					$accTrans[] = array(
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->sale_discount_acc,
						'amount' => $order_discount,
						'narrative' => 'Order Discount',
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
					$accTrans[] = array(
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->ar_acc,
						'amount' => $order_discount * (-1),
						'narrative' => 'Order Discount',
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
				if($order_tax != 0){
					$accTrans[] = array(
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->vat_output,
						'amount' => $order_tax * (-1),
						'narrative' => 'Order Tax',
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
					$accTrans[] = array(
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->ar_acc,
						'amount' => $order_tax ,
						'narrative' => 'Order Tax',
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
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
		if ($this->form_validation->run() == true && $this->truckings_model->addSale($data,$tru_products,$products, $accTrans)) {	
            $this->session->set_flashdata('message', $this->lang->line("sale_added"));          
			redirect('truckings/sales');
        } else {
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
			$this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['customers'] = $this->site->getCustomers();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('trucking')), array('link' => site_url('truckings/sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('add_sale')));
			$meta = array('page_title' => lang('add_sale'), 'bc' => $bc);
            $this->page_construct('truckings/add_sale', $meta, $this->data);
        }
	}
	
	
	public function edit_sale($id = false){
		$this->bpas->checkPermissions("edit",false,"sales");
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
        if ($this->form_validation->run() == true) {
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
            $warehouse_id = $this->input->post('warehouse');
			$customer_id = $this->input->post('customer');
			$note = $this->input->post('note');
			$staff_note = $this->input->post('staff_note');
			$from_date = $this->bpas->fld(trim($this->input->post('from_date')));
			$to_date = $this->bpas->fld(trim($this->input->post('to_date')));
            $payment_term = $this->input->post('payment_term');
			$payment_term_info = $this->site->getPaymentTermsByID($payment_term);
            if($payment_term_info){
				if($payment_term_info->term_type=='end_month'){
					$due_date = date("Y-m-t", strtotime($date));
				}else{
					$due_date =  date('Y-m-d', strtotime('+' . $payment_term_info->due_day . ' days', strtotime($date)));
				}
			}else{
				$due_date = null;
			}
			$biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
			$tax_detail = $this->site->getTaxRateByID($this->input->post('order_tax'));
			if($tax_detail && $tax_detail->rate > 0){
				$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('tax_so',$biller_id);
			}else{
				$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so',$biller_id);
			}
			$i = isset($_POST['trucking_id']) ? sizeof($_POST['trucking_id']) : 0;
			$total = 0;
			$order_tax = 0;
			$order_discount = 0;
			$total_items = 0;
			$percentage = '%';
			$products = false;
			for ($r = 0; $r < $i; $r++) {
				$trucking_id = $_POST['trucking_id'][$r];
				$trucking_date = $this->bpas->fsd($_POST['trucking_date'][$r]);
				$trucking_reference_no = $_POST['trucking_reference_no'][$r];
				$truck = $_POST['truck'][$r];
				$service_id = $_POST['service_id'][$r];
				$product_code = $_POST['product_code'][$r];
				$product_name = $_POST['product_name'][$r];
				$container = $_POST['container'][$r];
				$dry_port = $_POST['dry_port'][$r];
				$factory = $_POST['factory'][$r];
				$container_no = $_POST['container_no'][$r];
				$trucking = $_POST['trucking'][$r];
				$lolo = $_POST['lolo'][$r];
				$extra = $_POST['extra'][$r];
				$stand_by = $_POST['stand_by'][$r];
				$booking = $_POST['booking'][$r];
				$subtotal = $_POST['subtotal'][$r];
				$tru_products[] = array(
					'sale_id' => $id,
					'trucking_id' => $trucking_id,
					'date' => $trucking_date,
					'reference_no' => $trucking_reference_no,
					'truck' => $truck,
					'service_id' => $service_id,
					'service_code' => $product_code,
					'service_name' => $product_name,
					'container' => $container,
					'dry_port' => $dry_port,
					'factory' => $factory,
					'container_no' => $container_no,
					'trucking' => $trucking,
					'lolo' => $lolo,
					'extra' => $extra,
					'stand_by' => $stand_by,
					'booking' => $booking,
					'subtotal' => $subtotal,
				);
				
				
				$products[] = array(
					'sale_id' => $id,
					'product_id' => $service_id,
					'product_code' => $product_code,
					'product_name' => $product_name,
					'product_type' => "service",
					'net_unit_price' => $subtotal,
					'unit_price' => $subtotal,
					'real_unit_price' => $subtotal,
					'quantity' => 1,
					'unit_quantity' => 1,
					'warehouse_id' => $warehouse_id,
					'subtotal' => $subtotal
				);
	
				$total += $subtotal;
				$total_items++;
			}
			if (!$tru_products) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
			} else {
				krsort($tru_products);
			}
			if ($this->input->post('order_discount')) {
                $order_discount_id = $this->input->post('order_discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = (($total) * (Float) ($ods[0])) / 100;
                } else {
                    $order_discount = $order_discount_id;
                }
            } else {
                $order_discount_id = null;
            }
			
			if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = ((($total) - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }
			$grand_total = ($total) + $order_tax - $order_discount;
			$data = array(
				'date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
				'staff_note' => $staff_note,
                'total' => $total,
				'order_tax_id' => $order_tax_id,
				'order_tax' => $order_tax,
				'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $order_discount,
                'grand_total' => $grand_total,
                'sale_status' => "completed",
				'total_items' => $total_items,
				'type' => 'trucking',
				'delivery_status' => 'completed',
                'payment_term' => $payment_term,
                'due_date' => $due_date,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
				'from_date' => $from_date,
				'to_date' => $to_date,
            );
			if($this->Settings->accounting == 1){
				$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
				if($order_discount != 0){
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->sale_discount_acc,
						'amount' => $order_discount,
						'narrative' => 'Order Discount',
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->ar_acc,
						'amount' => $order_discount * (-1),
						'narrative' => 'Order Discount',
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
				if($order_tax != 0){
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->vat_output,
						'amount' => $order_tax * (-1),
						'narrative' => 'Order Tax',
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference,
						'account' => $saleAcc->ar_acc,
						'amount' => $order_tax ,
						'narrative' => 'Order Tax',
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
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
		if ($this->form_validation->run() == true && $this->truckings_model->updateSale($id, $data,$tru_products,$products, $accTrans)) {	
            $this->session->set_flashdata('message', $this->lang->line("sale_edited"));          
			redirect('truckings/sales');
        } else {
			$this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
			$this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['customers'] = $this->site->getCustomers();
			$this->data['sale'] = $this->truckings_model->getSaleByID($id);
            $this->data['trucking_items'] = $this->truckings_model->getTruckingSaleItemBySaleID($id);
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('trucking')), array('link' => site_url('truckings/sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('edit_sale')));
			$meta = array('page_title' => lang('edit_sale'), 'bc' => $bc);
            $this->page_construct('truckings/edit_sale', $meta, $this->data);
        }
	}
	
	
	public function delete_sale($id = null)
    {
		$this->bpas->checkPermissions("delete",true,"sales");
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->truckings_model->deleteSale($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("sale_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('sale_deleted'));
			redirect('truckings/sales');
		}
    }
	
	public function get_truckings(){
		$biller_id = $this->input->get('biller_id');
		$warehouse_id = $this->input->get('warehouse_id');
		$customer_id = $this->input->get('customer_id');
		$from_date = $this->bpas->fld(trim($this->input->get('from_date')));
		$to_date = $this->bpas->fld(trim($this->input->get('to_date')));
		$sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : false;
		$truckings = $this->truckings_model->getTruckings($biller_id,$warehouse_id,$customer_id,$from_date,$to_date,$sale_id);
		echo json_encode($truckings);
	}
	
	public function sales($warehouse_id = null, $biller_id = NULL)
    {
		$this->bpas->checkPermissions("index",false,"sales");
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
		if($biller_id == 0){
			$biller_id = null;
		}
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;	
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('trucking')), array('link' => '#', 'page' => lang('sales')));
		$meta = array('page_title' => lang('sales'), 'bc' => $bc);
        $this->page_construct('truckings/sales', $meta, $this->data);
    }
	
	public function getSales($warehouse_id = null, $biller_id = NULL)
    {
		$this->bpas->checkPermissions("index",false,"sales");
		$view_sale = anchor('truckings/view_sale/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_sale'), ' class="view_sale" ');
		$payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_payment" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="add_payment" data-target="#myModal"');
		$edit_link = anchor('truckings/edit_sale/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), ' class="edit_sale" ');
		$delete_link = "<a href='#' class='po delete_sale' title='<b>" . $this->lang->line("delete_sale") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . site_url('truckings/delete_sale/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $view_sale . '</li>
						<li>' . $payments_link . '</li>
						<li>' . $add_payment_link . '</li>
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("
									sales.id as id,
									DATE_FORMAT(date, '%Y-%m-%d %T') as date,
									DATE_FORMAT(from_date, '%Y-%m-%d %T') as from_date,
									DATE_FORMAT(to_date, '%Y-%m-%d %T') as to_date,
									reference_no,
									customer,
									grand_total,
									paid,
									IFNULL(".$this->db->dbprefix('sales').".grand_total,0) - IFNULL(".$this->db->dbprefix('sales').".paid,0) as balance,
									sales.payment_status,
									attachment
								")
							->from('sales');
		$this->datatables->where('sales.type', "trucking");
	    if ($warehouse_id) {
			$this->datatables->where('sales.warehouse_id', $warehouse_id);
		}
		if ($biller_id) {
			$this->datatables->where('sales.biller_id', $biller_id);
		}	
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('sales.created_by', $this->session->userdata('user_id'));
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('sales.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
			$this->datatables->where_in('sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
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
					$this->bpas->checkPermissions("delete",false,"sales");
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->truckings_model->deleteSale($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('sale_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("sale_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sales'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('from_date'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('to_date'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));
					
					$this->db->select("
								sales.id as id,
								DATE_FORMAT(date, '%Y-%m-%d %T') as date,
								DATE_FORMAT(from_date, '%Y-%m-%d %T') as from_date,
								DATE_FORMAT(to_date, '%Y-%m-%d %T') as to_date,
								reference_no,
								customer,
								grand_total,
								paid,
								IFNULL(".$this->db->dbprefix('sales').".grand_total,0) - IFNULL(".$this->db->dbprefix('sales').".paid,0) as balance,
								sales.payment_status							")
					->from('sales')
					->where_in("sales.id",$_POST['val']);
					$q = $this->db->get();
                    $row = 2;
                    if ($q->num_rows() > 0) {
						foreach (($q->result()) as $sale) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($sale->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($sale->from_date));
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->hrld($sale->to_date));
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sale->reference_no);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $sale->customer);
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($sale->grand_total));
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($sale->paid));
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($sale->balance));
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($sale->payment_status));
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
                    $filename = 'sales_' . date('Y_m_d_H_i_s');
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
	
	public function modal_view_sale($id = false){
		$this->bpas->checkPermissions('index', true, 'sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $sale = $this->truckings_model->getSaleByID($id);
        $this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
        $this->data['sale'] = $sale;
		$this->data['created_by'] = $this->site->getUserByID($sale->created_by);
		$this->data['customer'] = $this->site->getCompanyByID($sale->customer_id);
		$this->data['sale_items'] = $this->truckings_model->getTruckingSaleItemBySaleID($id,"desc");
		$this->data['payment'] = $this->truckings_model->getPaymentBySaleID($id);    
		$this->load->view($this->theme . 'truckings/modal_view_sale', $this->data);
	}
	
	public function view_sale($id = false){
		$this->bpas->checkPermissions('index', true, 'sales');
		$sale = $this->truckings_model->getSaleByID($id);
		$this->data['sale'] = $sale;
		$this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
		$this->data['customer'] = $this->site->getCompanyByID($sale->customer_id);
		$this->data['sale_items'] = $this->truckings_model->getTruckingSaleItemBySaleID($id,"desc");
		$this->data['payment'] = $this->truckings_model->getPaymentBySaleID($id);    
		$this->data['created_by'] = $this->site->getUserByID($sale->created_by);
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('truckings')), array('link' => site_url('truckings/sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('view_sale')));
        $meta = array('page_title' => lang('view_sale'), 'bc' => $bc);
		$this->page_construct('truckings/view_sale', $meta, $this->data);
	}
	
	public function trucking_report()
	{
		$this->bpas->checkPermissions();
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['customers'] = $this->truckings_model->getCustomers();
		$this->data['drivers'] = $this->truckings_model->getDrivers();
		$this->data['trucks'] = $this->truckings_model->getTrucks();
		$this->data['containers'] = $this->truckings_model->getContainers();
		$this->data['factories'] = $this->truckings_model->getFactories();
		$this->data['dry_ports'] = $this->truckings_model->getDryPorts();
		$this->data['services'] = $this->truckings_model->getProducts("service");
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('trucking')), array('link' => '#', 'page' => lang('trucking_report')));
        $meta = array('page_title' => lang('trucking_report'), 'bc' => $bc);
        $this->page_construct('truckings/trucking_report', $meta, $this->data);
	}
	
	public function getTruckingReport($xls = null)
	{
		$this->bpas->checkPermissions('trucking_report', TRUE);
		$biller_id = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$customer_id = $this->input->get('customer') ? $this->input->get('customer') : NULL;
		$truck_id = $this->input->get('truck') ? $this->input->get('truck') : NULL;
		$driver_id = $this->input->get('driver') ? $this->input->get('driver') : NULL;
		$container_id = $this->input->get('container') ? $this->input->get('container') : NULL;
		$dry_port_id = $this->input->get('dry_port') ? $this->input->get('dry_port') : NULL;
		$factory_id = $this->input->get('factory') ? $this->input->get('factory') : NULL;
		$container_no = $this->input->get('container_no') ? $this->input->get('container_no') : NULL;
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date')) : NULL;
		$status = $this->input->get('status') ? $this->input->get('status') : NULL;
		if ($xls) {
			$this->db ->select("
								DATE_FORMAT(".$this->db->dbprefix('tru_truckings').".date, '%Y-%m-%d %T') as date,
								tru_truckings.reference_no,
								companies.company as customer,
								tru_factories.name as factory,
								tru_dry_ports.name as dry_port,
								tru_truckings.container_no,
								tru_trucks.plate,
								products.name as service,
								IFNULL(".$this->db->dbprefix('tru_truckings').".fee,0) as fee,
								IFNULL(".$this->db->dbprefix('tru_truckings').".lolo,0) as lolo,
								IFNULL(".$this->db->dbprefix('tru_truckings').".extra,0) as extra,
								IFNULL(".$this->db->dbprefix('tru_truckings').".stand_by,0) as stand_by,
								IFNULL(".$this->db->dbprefix('tru_truckings').".booking,0) as booking,
								IFNULL(".$this->db->dbprefix('tru_truckings').".income_amount,0) as total,
								tru_truckings.payment_status,
								tru_truckings.status,
								tru_truckings.attachment,
								tru_truckings.id as id
							")
            ->from("tru_truckings")
			->join("companies","companies.id = tru_truckings.customer_id","left")
			->join("products","products.id = tru_truckings.service_id","left")
			->join("tru_factories","tru_factories.id = tru_truckings.factory_id","left")
			->join("tru_dry_ports","tru_dry_ports.id = tru_truckings.dry_port_id","left")
			->join("tru_trucks","tru_trucks.id = tru_truckings.truck_id","left");

			if ($biller_id) {
				$this->db->where('tru_truckings.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->db->where('tru_truckings.customer_id', $customer_id);
			}
			if ($truck_id) {
				$this->db->where('tru_truckings.truck_id', $truck_id);
			}
			if ($driver_id) {
				$this->db->where('tru_truckings.driver_id', $driver_id);
			}
			if ($container_id) {
				$this->db->where('tru_truckings.container_id', $container_id);
			}
			if ($dry_port_id) {
				$this->db->where('tru_truckings.dry_port_id', $dry_port_id);
			}
			if ($factory_id) {
				$this->db->where('tru_truckings.factory_id', $factory_id);
			}
			if ($container_no) {
				$this->db->where('tru_truckings.container_no', $container_no);
			}
			if ($start_date) {
				$this->db->where('tru_truckings.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('tru_truckings.date <=', $end_date);
			}
			if ($status) {
				$this->db->where('tru_truckings.status', $status);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->db->where('tru_truckings.biller_id', $this->session->userdata('biller_id'));
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
				$this->excel->getActiveSheet()->setTitle(lang('trucking_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('factory'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('dry_port'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('container_no'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('truck'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('service'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('fee'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('lolo'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('extra'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('stand_by'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('booking'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('total'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('status'));
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->factory);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->dry_port);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->container_no);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->plate);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->service);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->fee));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->lolo));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($data_row->extra));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($data_row->stand_by));
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->formatDecimal($data_row->booking));
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $this->bpas->formatDecimal($data_row->total));
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, lang($data_row->status));
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
				$filename = 'trucking_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
                create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
			
		} else{  
			$this->load->library('datatables');
			$this->datatables ->select("
										DATE_FORMAT(".$this->db->dbprefix('tru_truckings').".date, '%Y-%m-%d %T') as date,
										tru_truckings.reference_no,
										companies.company as customer,
										tru_factories.name as factory,
										tru_dry_ports.name as dry_port,
										tru_truckings.container_no,
										tru_trucks.plate,
										products.name as service,
										IFNULL(".$this->db->dbprefix('tru_truckings').".fee,0) as fee,
										IFNULL(".$this->db->dbprefix('tru_truckings').".lolo,0) as lolo,
										IFNULL(".$this->db->dbprefix('tru_truckings').".extra,0) as extra,
										IFNULL(".$this->db->dbprefix('tru_truckings').".stand_by,0) as stand_by,
										IFNULL(".$this->db->dbprefix('tru_truckings').".booking,0) as booking,
										IFNULL(".$this->db->dbprefix('tru_truckings').".income_amount,0) as total,
										tru_truckings.attachment,
										tru_truckings.status,
										tru_truckings.id as id
									")
            ->from("tru_truckings")
			->join("companies","companies.id = tru_truckings.customer_id","left")
			->join("products","products.id = tru_truckings.service_id","left")
			->join("tru_factories","tru_factories.id = tru_truckings.factory_id","left")
			->join("tru_dry_ports","tru_dry_ports.id = tru_truckings.dry_port_id","left")
			->join("tru_trucks","tru_trucks.id = tru_truckings.truck_id","left");

			if ($biller_id) {
				$this->datatables->where('tru_truckings.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->datatables->where('tru_truckings.customer_id', $customer_id);
			}
			if ($truck_id) {
				$this->datatables->where('tru_truckings.truck_id', $truck_id);
			}
			if ($driver_id) {
				$this->datatables->where('tru_truckings.driver_id', $driver_id);
			}
			if ($container_id) {
				$this->datatables->where('tru_truckings.container_id', $container_id);
			}
			if ($dry_port_id) {
				$this->datatables->where('tru_truckings.dry_port_id', $dry_port_id);
			}
			if ($factory_id) {
				$this->datatables->where('tru_truckings.factory_id', $factory_id);
			}
			if ($container_no) {
				$this->datatables->where('tru_truckings.container_no', $container_no);
			}
			if ($start_date) {
				$this->datatables->where('tru_truckings.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('tru_truckings.date <=', $end_date);
			}
			if ($status) {
				$this->datatables->where('tru_truckings.status', $status);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('tru_truckings.biller_id', $this->session->userdata('biller_id'));
			}
			echo $this->datatables->generate();
			
		}
	}
	
	public function trucking_expense_report()
	{
		$this->bpas->checkPermissions();
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['customers'] = $this->truckings_model->getCustomers();
		$this->data['drivers'] = $this->truckings_model->getDrivers();
		$this->data['trucks'] = $this->truckings_model->getTrucks();
		$this->data['containers'] = $this->truckings_model->getContainers();
		$this->data['factories'] = $this->truckings_model->getFactories();
		$this->data['dry_ports'] = $this->truckings_model->getDryPorts();
		$this->data['services'] = $this->truckings_model->getProducts("service");
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('trucking')), array('link' => '#', 'page' => lang('trucking_expense_report')));
        $meta = array('page_title' => lang('trucking_expense_report'), 'bc' => $bc);
        $this->page_construct('truckings/trucking_expense_report', $meta, $this->data);
	}
	
	public function getTruckingExpenseReport($xls = null)
	{
		$this->bpas->checkPermissions('trucking_expense_report', TRUE);
		$biller_id = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$customer_id = $this->input->get('customer') ? $this->input->get('customer') : NULL;
		$truck_id = $this->input->get('truck') ? $this->input->get('truck') : NULL;
		$driver_id = $this->input->get('driver') ? $this->input->get('driver') : NULL;
		$container_id = $this->input->get('container') ? $this->input->get('container') : NULL;
		$dry_port_id = $this->input->get('dry_port') ? $this->input->get('dry_port') : NULL;
		$factory_id = $this->input->get('factory') ? $this->input->get('factory') : NULL;
		$container_no = $this->input->get('container_no') ? $this->input->get('container_no') : NULL;
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date')) : NULL;
		$payment_status = $this->input->get('payment_status') ? $this->input->get('payment_status') : NULL;
		if ($xls) {
			$this->db->select("
								DATE_FORMAT(".$this->db->dbprefix('tru_truckings').".date, '%Y-%m-%d %T') as date,
								tru_truckings.reference_no,
								companies.company as customer,
								tru_factories.name as factory,
								tru_dry_ports.name as dry_port,
								tru_truckings.container_no,
								tru_trucks.plate,
								products.name as service,
								IFNULL(".$this->db->dbprefix('tru_truckings').".fuel,0) as fuel,
								IFNULL(".$this->db->dbprefix('tru_truckings').".lolo,0) as lolo,
								IFNULL(".$this->db->dbprefix('tru_truckings').".booking,0) as booking,
								IFNULL(".$this->db->dbprefix('tru_truckings').".commission,0) as commission,
								IFNULL(".$this->db->dbprefix('tru_truckings').".other,0) as other,
								IFNULL(".$this->db->dbprefix('tru_truckings').".expense_amount,0) as total,
								IFNULL(".$this->db->dbprefix('tru_truckings').".paid,0) as paid,
								IFNULL(".$this->db->dbprefix('tru_truckings').".balance,0) as balance,
								tru_truckings.attachment,
								tru_truckings.payment_status,
								tru_truckings.id as id
							")
            ->from("tru_truckings")
			->join("companies","companies.id = tru_truckings.customer_id","left")
			->join("products","products.id = tru_truckings.service_id","left")
			->join("tru_factories","tru_factories.id = tru_truckings.factory_id","left")
			->join("tru_dry_ports","tru_dry_ports.id = tru_truckings.dry_port_id","left")
			->join("tru_trucks","tru_trucks.id = tru_truckings.truck_id","left");

			if ($biller_id) {
				$this->db->where('tru_truckings.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->db->where('tru_truckings.customer_id', $customer_id);
			}
			if ($truck_id) {
				$this->db->where('tru_truckings.truck_id', $truck_id);
			}
			if ($driver_id) {
				$this->db->where('tru_truckings.driver_id', $driver_id);
			}
			if ($container_id) {
				$this->db->where('tru_truckings.container_id', $container_id);
			}
			if ($dry_port_id) {
				$this->db->where('tru_truckings.dry_port_id', $dry_port_id);
			}
			if ($factory_id) {
				$this->db->where('tru_truckings.factory_id', $factory_id);
			}
			if ($container_no) {
				$this->db->where('tru_truckings.container_no', $container_no);
			}
			if ($start_date) {
				$this->db->where('tru_truckings.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('tru_truckings.date <=', $end_date);
			}
			if ($payment_status) {
				$this->db->where('tru_truckings.payment_status', $payment_status);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->db->where('tru_truckings.biller_id', $this->session->userdata('biller_id'));
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
				$this->excel->getActiveSheet()->setTitle(lang('trucking_expense_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('factory'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('dry_port'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('container_no'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('truck'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('service'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('fuel'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('lolo'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('booking'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('commission'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('total'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('payment_status'));
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->factory);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->dry_port);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->container_no);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->plate);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->service);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->fuel));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->lolo));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($data_row->booking));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($data_row->commission));
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->formatDecimal($data_row->total));
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $this->bpas->formatDecimal($data_row->paid));
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, $this->bpas->formatDecimal($data_row->balance));
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, lang($data_row->payment_status));
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
				$filename = 'trucking_expense_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
                create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
			
		} else{  
			$this->load->library('datatables');
			$this->datatables ->select("
										DATE_FORMAT(".$this->db->dbprefix('tru_truckings').".date, '%Y-%m-%d %T') as date,
										tru_truckings.reference_no,
										companies.company as customer,
										tru_factories.name as factory,
										tru_dry_ports.name as dry_port,
										tru_truckings.container_no,
										tru_trucks.plate,
										products.name as service,
										IFNULL(".$this->db->dbprefix('tru_truckings').".fuel,0) as fuel,
										IFNULL(".$this->db->dbprefix('tru_truckings').".lolo,0) as lolo,
										IFNULL(".$this->db->dbprefix('tru_truckings').".booking,0) as booking,
										IFNULL(".$this->db->dbprefix('tru_truckings').".commission,0) as commission,
										IFNULL(".$this->db->dbprefix('tru_truckings').".other,0) as other,
										IFNULL(".$this->db->dbprefix('tru_truckings').".expense_amount,0) as total,
										IFNULL(".$this->db->dbprefix('tru_truckings').".paid,0) as paid,
										IFNULL(".$this->db->dbprefix('tru_truckings').".balance,0) as balance,
										tru_truckings.attachment,
										tru_truckings.payment_status,
										tru_truckings.id as id
									")
            ->from("tru_truckings")
			->join("companies","companies.id = tru_truckings.customer_id","left")
			->join("products","products.id = tru_truckings.service_id","left")
			->join("tru_factories","tru_factories.id = tru_truckings.factory_id","left")
			->join("tru_dry_ports","tru_dry_ports.id = tru_truckings.dry_port_id","left")
			->join("tru_trucks","tru_trucks.id = tru_truckings.truck_id","left");

			if ($biller_id) {
				$this->datatables->where('tru_truckings.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->datatables->where('tru_truckings.customer_id', $customer_id);
			}
			if ($truck_id) {
				$this->datatables->where('tru_truckings.truck_id', $truck_id);
			}
			if ($driver_id) {
				$this->datatables->where('tru_truckings.driver_id', $driver_id);
			}
			if ($container_id) {
				$this->datatables->where('tru_truckings.container_id', $container_id);
			}
			if ($dry_port_id) {
				$this->datatables->where('tru_truckings.dry_port_id', $dry_port_id);
			}
			if ($factory_id) {
				$this->datatables->where('tru_truckings.factory_id', $factory_id);
			}
			if ($container_no) {
				$this->datatables->where('tru_truckings.container_no', $container_no);
			}
			if ($start_date) {
				$this->datatables->where('tru_truckings.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('tru_truckings.date <=', $end_date);
			}
			if ($payment_status) {
				$this->datatables->where('tru_truckings.payment_status', $payment_status);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('tru_truckings.biller_id', $this->session->userdata('biller_id'));
			}
			echo $this->datatables->generate();
			
		}
	}
	
	public function cash_advance_report()
	{
		$this->bpas->checkPermissions();
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['customers'] = $this->truckings_model->getCustomers();
		$this->data['drivers'] = $this->truckings_model->getDrivers();
		$this->data['trucks'] = $this->truckings_model->getTrucks();
		$this->data['containers'] = $this->truckings_model->getContainers();
		$this->data['factories'] = $this->truckings_model->getFactories();
		$this->data['dry_ports'] = $this->truckings_model->getDryPorts();
		$this->data['services'] = $this->truckings_model->getProducts("service");
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('trucking')), array('link' => '#', 'page' => lang('cash_advance_report')));
        $meta = array('page_title' => lang('cash_advance_report'), 'bc' => $bc);
        $this->page_construct('truckings/cash_advance_report', $meta, $this->data);
	}
	
	public function getCashAdvanceReport($xls = null)
	{
		$this->bpas->checkPermissions('cash_advance_report', TRUE);
		$biller_id = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$driver_id = $this->input->get('driver') ? $this->input->get('driver') : NULL;
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date')) : NULL;
		$payment_status = $this->input->get('payment_status') ? $this->input->get('payment_status') : NULL;
		if ($xls) {
			$this->db ->select("
								DATE_FORMAT(".$this->db->dbprefix('tru_cash_advances').".date, '%Y-%m-%d %T') as date,
								tru_cash_advances.reference_no,
								tru_cash_advances.driver_name,
								cash_accounts.name as paid_by,
								tru_cash_advances.note,
								IFNULL(".$this->db->dbprefix('tru_cash_advances').".service,0) as service,
								IFNULL(".$this->db->dbprefix('tru_cash_advances').".amount,0) as amount,
								IFNULL(".$this->db->dbprefix('tru_cash_advances').".paid,0) as paid,
								IFNULL(".$this->db->dbprefix('tru_cash_advances').".balance,0) as balance,
								tru_cash_advances.attachment,
								tru_cash_advances.payment_status,
								tru_cash_advances.id as id
							")
						->join("cash_accounts","cash_accounts.id = tru_cash_advances.paid_by","left")
						->from("tru_cash_advances");

			if ($biller_id) {
				$this->db->where('tru_cash_advances.biller_id', $biller_id);
			}
			if ($driver_id) {
				$this->db->where('tru_cash_advances.driver_id', $driver_id);
			}
			if ($start_date) {
				$this->db->where('tru_cash_advances.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('tru_cash_advances.date <=', $end_date);
			}
			if ($payment_status) {
				$this->db->where('tru_cash_advances.payment_status', $payment_status);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->db->where('tru_cash_advances.biller_id', $this->session->userdata('biller_id'));
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
				$this->excel->getActiveSheet()->setTitle(lang('cash_advance_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('driver'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('paid_by'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('service'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('amount'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('payment_status'));
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->driver_name);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->paid_by);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->remove_tag($data_row->note));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($data_row->service));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($data_row->amount));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($data_row->paid));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->balance));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, lang($data_row->payment_status));
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
				$filename = 'cash_advance_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
                create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
			
		} else{  
			$this->load->library('datatables');
			$this->datatables ->select("
										DATE_FORMAT(".$this->db->dbprefix('tru_cash_advances').".date, '%Y-%m-%d %T') as date,
										tru_cash_advances.reference_no,
										tru_cash_advances.driver_name,
										cash_accounts.name as paid_by,
										tru_cash_advances.note,
										IFNULL(".$this->db->dbprefix('tru_cash_advances').".service,0) as service,
										IFNULL(".$this->db->dbprefix('tru_cash_advances').".amount,0) as amount,
										IFNULL(".$this->db->dbprefix('tru_cash_advances').".paid,0) as paid,
										IFNULL(".$this->db->dbprefix('tru_cash_advances').".balance,0) as balance,
										tru_cash_advances.attachment,
										tru_cash_advances.payment_status,
										tru_cash_advances.id as id
									")
								->join("cash_accounts","cash_accounts.id = tru_cash_advances.paid_by","left")
								->from("tru_cash_advances");

			if ($biller_id) {
				$this->datatables->where('tru_cash_advances.biller_id', $biller_id);
			}
			if ($driver_id) {
				$this->datatables->where('tru_cash_advances.driver_id', $driver_id);
			}
			if ($start_date) {
				$this->datatables->where('tru_cash_advances.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('tru_cash_advances.date <=', $end_date);
			}
			if ($payment_status) {
				$this->datatables->where('tru_cash_advances.payment_status', $payment_status);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('tru_cash_advances.biller_id', $this->session->userdata('biller_id'));
			}
			echo $this->datatables->generate();
			
		}
	}
	
	public function cash_advance_summary_report()
	{
		$this->bpas->checkPermissions();
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['drivers'] = $this->truckings_model->getDrivers();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('trucking')), array('link' => '#', 'page' => lang('cash_advance_summary_report')));
        $meta = array('page_title' => lang('cash_advance_summary_report'), 'bc' => $bc);
        $this->page_construct('truckings/cash_advance_summary_report', $meta, $this->data);
	}
	
	public function getCashAdvanceSummaryReport($xls = null)
	{
		$this->bpas->checkPermissions('cash_advance_summary_report', TRUE);
		$biller_id = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$driver_id = $this->input->get('driver') ? $this->input->get('driver') : NULL;
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date')) : NULL;
		if ($xls) {
			$this->db->select("
								tru_cash_advances.driver_name,
								SUM(IFNULL(".$this->db->dbprefix('tru_cash_advances').".amount,0)) as total,
								SUM(IFNULL(".$this->db->dbprefix('tru_cash_advances').".paid,0)) as paid,
								SUM(IFNULL(".$this->db->dbprefix('tru_cash_advances').".balance,0)) as balance,
								IF(ROUND(SUM(IFNULL(".$this->db->dbprefix('tru_cash_advances').".balance,0)),2) = 0,'paid',IF(SUM(IFNULL(".$this->db->dbprefix('tru_cash_advances').".paid,0)) > 0,'partial','pending')) as payment_status
							")
						->join("cash_accounts","cash_accounts.id = tru_cash_advances.paid_by","left")
						->from("tru_cash_advances")
						->group_by("tru_cash_advances.driver_id");

			if ($biller_id) {
				$this->db->where('tru_cash_advances.biller_id', $biller_id);
			}
			if ($driver_id) {
				$this->db->where('tru_cash_advances.driver_id', $driver_id);
			}
			if ($start_date) {
				$this->db->where('tru_cash_advances.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('tru_cash_advances.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->db->where('tru_cash_advances.biller_id', $this->session->userdata('biller_id'));
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
				$this->excel->getActiveSheet()->setTitle(lang('cash_advance_summary_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('driver'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('amount'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('payment_status'));
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->driver_name);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->formatDecimal($data_row->total));
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatDecimal($data_row->paid));
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($data_row->balance));
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($data_row->payment_status));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);

				$filename = 'cash_advance_summary_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
                create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
			
		} else{  
			$this->load->library('datatables');
			$this->datatables ->select("
										tru_cash_advances.driver_name,
										SUM(IFNULL(".$this->db->dbprefix('tru_cash_advances').".amount,0)) as total,
										SUM(IFNULL(".$this->db->dbprefix('tru_cash_advances').".paid,0)) as paid,
										SUM(IFNULL(".$this->db->dbprefix('tru_cash_advances').".balance,0)) as balance,
										IF(ROUND(SUM(IFNULL(".$this->db->dbprefix('tru_cash_advances').".balance,0)),2) = 0,'paid',IF(SUM(IFNULL(".$this->db->dbprefix('tru_cash_advances').".paid,0)) > 0,'partial','pending')) as payment_status
									")
								->join("cash_accounts","cash_accounts.id = tru_cash_advances.paid_by","left")
								->from("tru_cash_advances")
								->group_by("tru_cash_advances.driver_id");

			if ($biller_id) {
				$this->datatables->where('tru_cash_advances.biller_id', $biller_id);
			}
			if ($driver_id) {
				$this->datatables->where('tru_cash_advances.driver_id', $driver_id);
			}
			if ($start_date) {
				$this->datatables->where('tru_cash_advances.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('tru_cash_advances.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('tru_cash_advances.biller_id', $this->session->userdata('biller_id'));
			}
			echo $this->datatables->generate();
			
		}
	}
	
	public function cash_advance_by_driver_report()
	{
		$this->bpas->checkPermissions();
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['drivers'] = $this->truckings_model->getDrivers();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('trucking')), array('link' => '#', 'page' => lang('cash_advance_by_driver_report')));
        $meta = array('page_title' => lang('cash_advance_by_driver_report'), 'bc' => $bc);
        $this->page_construct('truckings/cash_advance_by_driver_report', $meta, $this->data);
	}
	
	public function cash_advance_reconciliations()
    {
        $this->bpas->checkPermissions("cash_advances");
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'truckings', 'page' => lang('trucking')), array('link' => '#', 'page' => lang('cash_advance_reconciliations')));
        $meta = array('page_title' => lang('cash_advance_reconciliations'), 'bc' => $bc);
        $this->page_construct('truckings/cash_advance_reconciliations', $meta, $this->data);
    }

    public function getCashAdvanceReconciliations()
    {
		$this->bpas->checkPermissions('cash_advances');
		$edit_link = anchor('truckings/edit_cash_advance_reconciliation/$1', '<i class="fa fa-edit"></i> ' . lang('edit_cash_advance_reconciliation'), ' class="edit_cash_advance_reconciliation"');
        $delete_link = "<a href='#' class='delete_cash_advance_reconciliation po' title='<b>" . $this->lang->line("delete_cash_advance_reconciliation") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('truckings/delete_cash_advance_reconciliation/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_cash_advance_reconciliation') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
           
        $this->load->library('datatables');
        $this->datatables ->select("tru_cash_advance_reconciliations.id as id,
						DATE_FORMAT(".$this->db->dbprefix('tru_cash_advance_reconciliations').".date, '%Y-%m-%d %T') as date,
						tru_cash_advance_reconciliations.reference_no,
						tru_drivers.full_name as driver_name,
						IFNULL(".$this->db->dbprefix('tru_cash_advance_reconciliations').".beginning_balance,0) as beginning_balance,
						IFNULL(".$this->db->dbprefix('tru_cash_advance_reconciliations').".clearing_amount,0) as clearing_amount,
						IFNULL(".$this->db->dbprefix('tru_cash_advance_reconciliations').".ending_balance,0) as ending_balance,
						tru_cash_advance_reconciliations.note,
						tru_cash_advance_reconciliations.attachment
					")
			->join("tru_drivers","tru_drivers.id = tru_cash_advance_reconciliations.driver_id","left")
            ->from("tru_cash_advance_reconciliations");
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
    }
	

	public function add_cash_advance_reconciliation(){
		$this->bpas->checkPermissions("add_cash_advance");
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('driver', $this->lang->line("driver"), 'required');
        if ($this->form_validation->run() == true) {
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
            $driver_id = $this->input->post('driver');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cvr',$biller_id);
			$beginning_balance = $this->input->post('beginning_balance');
			$note = $this->input->post('note');
			$i = isset($_POST['cleared']) ? sizeof($_POST['cleared']) : 0;
			$clearing_amount = 0;
			$items = false;
			for ($r = 0; $r < $i; $r++) {
				$cleared = $_POST['cleared'][$r];
				if($cleared > 0){
					$transaction = $_POST['transaction'][$r];
					$transaction_id = $_POST['transaction_id'][$r];
					$cdate = $this->bpas->fsd($_POST['cdate'][$r]);
					$reference = $_POST['reference'][$r];
					$container_no = $_POST['container_no'][$r];
					$dry_port = $_POST['dry_port'][$r];
					$factory = $_POST['factory'][$r];
					$amount = $_POST['amount'][$r];
					$items[] = array(
						'transaction' => $transaction,
						'transaction_id' => $transaction_id,
						'date' => $cdate,
						'reference_no' => $reference,
						'container_no' => $container_no,
						'dry_port' => $dry_port,
						'factory' => $factory,
						'amount' => $amount,
					);
					$clearing_amount += $amount;
				}
				
			}
			if (!$items) {
				$this->form_validation->set_rules('item', lang("order_items"), 'required');
			}

			$data = array(
				'date' => $date,
				'biller_id' => $biller_id,
                'driver_id' => $driver_id,
                'reference_no' => $reference_no,
                'beginning_balance' => $beginning_balance,
				'clearing_amount' => $clearing_amount,
				'ending_balance' => ($clearing_amount + $beginning_balance),
                'note' => $note,
				'created_by' => $this->session->userdata('user_id')
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
		if ($this->form_validation->run() == true && $this->truckings_model->addCashAdvanceReconciliation($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("cash_advance_reconciliation_added"));          
			redirect('truckings/cash_advance_reconciliations');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['drivers'] = $this->truckings_model->getDrivers();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('trucking')), array('link' => site_url('truckings/cash_advance_reconciliations'), 'page' => lang('cash_advance_reconciliations')), array('link' => '#', 'page' => lang('add_cash_advance_reconciliation')));
			$meta = array('page_title' => lang('add_cash_advance_reconciliation'), 'bc' => $bc);
            $this->page_construct('truckings/add_cash_advance_reconciliation', $meta, $this->data);
        }
	}
	
	
	public function edit_cash_advance_reconciliation($id = false){
		$this->bpas->checkPermissions("edit_cash_advance");
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('driver', $this->lang->line("driver"), 'required');
        if ($this->form_validation->run() == true) {
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
            $driver_id = $this->input->post('driver');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cvr',$biller_id);
			$beginning_balance = $this->input->post('beginning_balance');
			$note = $this->input->post('note');
			$i = isset($_POST['cleared']) ? sizeof($_POST['cleared']) : 0;
			$clearing_amount = 0;
			$items = false;
			for ($r = 0; $r < $i; $r++) {
				$cleared = $_POST['cleared'][$r];
				if($cleared > 0){
					$transaction = $_POST['transaction'][$r];
					$transaction_id = $_POST['transaction_id'][$r];
					$cdate = $this->bpas->fsd($_POST['cdate'][$r]);
					$reference = $_POST['reference'][$r];
					$container_no = $_POST['container_no'][$r];
					$dry_port = $_POST['dry_port'][$r];
					$factory = $_POST['factory'][$r];
					$amount = $_POST['amount'][$r];
					$items[] = array(
						'cash_advance_reconciliation_id' => $id,	
						'transaction' => $transaction,
						'transaction_id' => $transaction_id,
						'date' => $cdate,
						'reference_no' => $reference,
						'container_no' => $container_no,
						'dry_port' => $dry_port,
						'factory' => $factory,
						'amount' => $amount,
					);
					$clearing_amount += $amount;
				}
				
			}
			if (!$items) {
				$this->form_validation->set_rules('item', lang("order_items"), 'required');
			}

			$data = array(
				'date' => $date,
				'biller_id' => $biller_id,
                'driver_id' => $driver_id,
                'reference_no' => $reference_no,
                'beginning_balance' => $beginning_balance,
				'clearing_amount' => $clearing_amount,
				'ending_balance' => ($clearing_amount + $beginning_balance),
                'note' => $note,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s')
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
		if ($this->form_validation->run() == true && $this->truckings_model->updateCashAdvanceReconciliation($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("cash_advance_reconciliation_edited"));          
			redirect('truckings/cash_advance_reconciliations');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['cash_advance_recon'] = $this->truckings_model->getCashAdvanceReconciliationByID($id);
			$this->data['cash_advance_items'] = $this->truckings_model->getCashAdvanceReconciliationItems($id);
			$this->data['billers'] = $this->site->getBillers();
			$this->data['drivers'] = $this->truckings_model->getDrivers();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('truckings'), 'page' => lang('trucking')), array('link' => site_url('truckings/cash_advance_reconciliations'), 'page' => lang('cash_advance_reconciliations')), array('link' => '#', 'page' => lang('edit_cash_advance_reconciliation')));
			$meta = array('page_title' => lang('edit_cash_advance_reconciliation'), 'bc' => $bc);
            $this->page_construct('truckings/edit_cash_advance_reconciliation', $meta, $this->data);
        }
	}
	
	
	public function delete_cash_advance_reconciliation($id = NULL)
    {	
		$this->bpas->checkPermissions('delete_cash_advance', true);
		if ($this->truckings_model->deleteCashAdvanceReconciliation($id)) {
			echo $this->lang->line("cash_advance_reconciliation_cannot_delete");
		} else {
			$this->session->set_flashdata('error', lang('cash_advance_reconciliation_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
    }
	
	public function cash_advance_reconciliation_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_cash_advance');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->truckings_model->deleteCashAdvanceReconciliation($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('cash_advance_reconciliation_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("cash_advance_reconciliation_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('cash_advance_reconciliations'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('driver'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('beginning_balance'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('clearing_amount'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('ending_balance'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('note'));
					
					$this->db->select("tru_cash_advance_reconciliations.id as id,
									DATE_FORMAT(".$this->db->dbprefix('tru_cash_advance_reconciliations').".date, '%Y-%m-%d %T') as date,
									tru_cash_advance_reconciliations.reference_no,
									tru_drivers.full_name as driver_name,
									IFNULL(".$this->db->dbprefix('tru_cash_advance_reconciliations').".beginning_balance,0) as beginning_balance,
									IFNULL(".$this->db->dbprefix('tru_cash_advance_reconciliations').".clearing_amount,0) as clearing_amount,
									IFNULL(".$this->db->dbprefix('tru_cash_advance_reconciliations').".ending_balance,0) as ending_balance,
									tru_cash_advance_reconciliations.note,
							")
					->join("tru_drivers","tru_drivers.id = tru_cash_advance_reconciliations.driver_id","left")
					->from("tru_cash_advance_reconciliations")
					->where_in("tru_cash_advance_reconciliations.id",$_POST['val']);
					$q = $this->db->get();
                    $row = 2;
                    if ($q->num_rows() > 0) {
						foreach (($q->result()) as $cash_advance_reconciliation) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($cash_advance_reconciliation->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $cash_advance_reconciliation->reference_no);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $cash_advance_reconciliation->driver_name);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($cash_advance_reconciliation->beginning_balance));
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($cash_advance_reconciliation->clearing_amount));
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($cash_advance_reconciliation->ending_balance));
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->remove_tag($cash_advance_reconciliation->note));

							$row++;
						}
					}
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(40);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'cash_advance_reconciliations_' . date('Y_m_d_H_i_s');
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
	
	public function modal_view_cash_advance_reconciliation($id = false){
		$this->bpas->checkPermissions('cash_advance_reconciliations', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $cash_advance_con = $this->truckings_model->getCashAdvanceReconciliationByID($id);
		$this->data['cash_advance_con'] = $cash_advance_con;
		$this->data['cash_advance_items'] = $this->truckings_model->getGroupCashAdvanceReconciliationItems($id);
        $this->data['driver'] = $this->truckings_model->getDriverByID($cash_advance_con->driver_id);
		$this->data['biller'] = $this->site->getCompanyByID($cash_advance_con->biller_id);
        $this->load->view($this->theme . 'truckings/modal_view_cash_advance_reconciliation', $this->data);
	}
	
	
	public function get_cash_advance_reconciliation(){
		$biller_id = $this->input->get('biller_id') ? $this->input->get("biller_id") : false;
		$driver_id = $this->input->get('driver_id') ? $this->input->get("driver_id") : false;
		$date = $this->input->get('date') ? $this->bpas->fld($this->input->get("date")) : false;
		$cash_advance_reconciliation_id = $this->input->get('cash_advance_reconciliation_id') ? $this->input->get("cash_advance_reconciliation_id") : false;
		
		$beginning_balance = $this->truckings_model->getRCashAdvanceBegningBalance($biller_id,$driver_id,$date,$cash_advance_reconciliation_id);
		$cash_advances = $this->truckings_model->getRCashAdvances($biller_id,$driver_id,$date,$cash_advance_reconciliation_id);
		$paybacks = $this->truckings_model->getRPaybacks($biller_id,$driver_id,$date,$cash_advance_reconciliation_id);
		$data = false;
		if($cash_advances){
			foreach($cash_advances as $cash_advance){
				$date = $this->bpas->hrsd($cash_advance->date);
				$order_date = str_replace("-","",$cash_advance->date);
				$row["date"] = $date;
				$row["reference_no"] = $cash_advance->reference_no;
				$row["amount"] = $cash_advance->amount;
				$row["transaction"] = "Cash Advance";
				$row["transaction_id"] = $cash_advance->id;
				$row["ref_transaction_id"] = $cash_advance->id;
				$row["container_no"] = "";
				$row["dry_port"] = "";
				$row["factory"] = "";
				$row["expense_id"] = "";
				$row["purchase_id"] = "";		
				$row["order_date"] = $order_date;
				$data[] = $row;
			}
		}
		if($paybacks){
			foreach($paybacks as $payback){
				$date = $this->bpas->hrsd($payback->date);
				$order_date = str_replace("-","",$payback->date);
				$row["date"] = $date;
				$row["reference_no"] = $payback->reference_no;
				$row["amount"] = $payback->amount * (-1);
				$row["transaction"] = $payback->transaction;
				$row["transaction_id"] = $payback->id;
				$row["ref_transaction_id"] = $payback->transaction_id;
				$row["container_no"] = $payback->container_no;
				$row["dry_port"] = $payback->dry_port;
				$row["factory"] = $payback->factory;
				$row["expense_id"] = $payback->expense_id;
				$row["purchase_id"] = $payback->purchase_id;
				$row["order_date"] = $order_date;
				$data[] = $row;
			}
		}
		if($data){
			$sortTransaion = array();
			foreach($data as $transaction){
				foreach($transaction as $key=>$value){
					if(!isset($sortTransaion[$key])){
						$sortTransaion[$key] = array();
					}
					$sortTransaion[$key][] = $value;
				}
			}
			array_multisort($sortTransaion["order_date"],SORT_ASC,$data);
		}
		echo json_encode(array("transaction"=>$data,"beginning_balance"=>$beginning_balance->amount));
	}
	
	
	
	
	
	
	
	
	
	
	
	
	


}
