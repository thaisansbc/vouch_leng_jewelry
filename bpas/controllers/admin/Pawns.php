<?php defined('BASEPATH') or exit('No direct script access allowed');

class Pawns extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        if ($this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->admin_load('pawns', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('pawns_model');
		$this->load->admin_model('companies_model');
		$this->load->admin_model('accounts_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '10240';
        $this->data['logo'] = true;

    }	
	
	function index($warehouse_id = null, $biller_id = NULL, $payment_status = null){
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
		if($biller_id == 0){
			$biller_id = null;
		}
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$this->data['payment_status'] = $payment_status;
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => admin_url('pawns'), 'page' => lang('pawn')), array('link' => '#', 'page' => lang('pawns')));
        $meta = array('page_title' => lang('pawns'), 'bc' => $bc);
        $this->page_construct('pawns/index', $meta, $this->data);
	}
	
	
	function getPawns($warehouse_id = null, $biller_id = NULL, $payment_status = null){
		$this->bpas->checkPermissions('index');
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
		if($biller_id == 0){
			$biller_id = null;
		}
		$close_link = "<a href='#' class='po' title='<b>" . $this->lang->line("close") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('pawns/close/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-close\"></i> "
        . lang('close_pawn') . "</a>";
		$purchase_link = anchor('admin/pawns/add_purchase/$1', '<i class="fa fa-money"></i> ' . lang('add_purchase'), ' data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal"');
		$payments_link = anchor('admin/pawns/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payment_rates'), 'data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal"');
		$add_payment_rate_link = anchor('admin/pawns/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment_rate'), 'data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal"');
        $pawn_return_link = anchor('admin/pawns/pawn_return/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_pawn'), 'data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal"');
		$edit_link = anchor('admin/pawns/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_pawn'));
		$delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_pawn") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('pawns/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_pawn') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $payments_link . '</li>
			<li>' . $add_payment_rate_link . '</li>
			<li>' . $pawn_return_link . '</li>
			<li>' . $purchase_link . '</li>
			<li>' . $close_link . '</li>
			<li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
		</div></div>';

        $this->load->library('datatables');
		if($payment_status){
			$join = 'inner';
			$this->datatables->where('pawns.status !=','completed');
			$this->datatables->where('pawns.status !=','closed');
		}else{
			$join = 'left';
		}
		$curDate = date('Y-m-d');
		$this->datatables
			->select("pawns.id as id, 
			DATE_FORMAT(".$this->db->dbprefix('pawns').".date, '%Y-%m-%d %T') as date, 
			pawns.reference_no,
			projects.project_name,
			pawns.customer, 
			pawns.principal,
			IFNULL(".$this->db->dbprefix('pawns').".payment_rate,0) as payment_rate,
			pawns.status, 
			IF(".$this->db->dbprefix('pawns').".status='completed' OR ".$this->db->dbprefix('pawns').".status='closed','completed',IF(pawn_items.pawn_id > 0, 'due','pending')) as payment_status,
			pawns.attachment")
		->from('pawns')
		->join('(SELECT pawn_id FROM bpas_pawn_items WHERE next_date <= "'.$curDate.'" GROUP BY pawn_id) as pawn_items','pawns.id = pawn_items.pawn_id',$join);

		$this->datatables->join("projects","projects.project_id=pawns.project_id","LEFT");
		if ($warehouse_id) {
            $this->datatables->where('pawns.warehouse_id', $warehouse_id);
        }
		if ($biller_id) {
             $this->datatables->where('pawns.biller_id', $biller_id);
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('pawns.biller_id =', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('pawns.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        if (!$this->Customer && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('pawns.created_by', $this->session->userdata('user_id'));
        }
		
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	
	function returns($warehouse_id = null, $biller_id = NULL){
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => admin_url('pawns'), 'page' => lang('pawn')), array('link' => '#', 'page' => lang('pawn_returns')));
        $meta = array('page_title' => lang('pawn_returns'), 'bc' => $bc);
        $this->page_construct('pawns/returns', $meta, $this->data);
	}
	
	
	function purchase($warehouse_id = null, $biller_id = NULL){
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
        $this->bpas->checkPermissions('purchases');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => admin_url('pawns'), 'page' => lang('pawn')), array('link' => '#', 'page' => lang('pawn_purchases')));
        $meta = array('page_title' => lang('pawn_purchases'), 'bc' => $bc);
        $this->page_construct('pawns/purchase', $meta, $this->data);
	}
	
	
	
	function products($warehouse_id = null){
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse_id'] = $warehouse_id;
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => admin_url('pawns'), 'page' => lang('pawn')), array('link' => '#', 'page' => lang('pawn_product')));
        $meta = array('page_title' => lang('pawn_product'), 'bc' => $bc);
        $this->page_construct('pawns/products', $meta, $this->data);
	}
	
	function getProducts($warehouse_id = null){
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
        $this->load->library('datatables');
       
		$this->datatables
			->select("
			pawns.id as id,
			DATE_FORMAT(".$this->db->dbprefix('pawns').".date, '%Y-%m-%d %T') as date, 
			pawns.reference_no,
			pawns.customer,
			pawn_items.product_name,
			pawn_items.serial_no,
			pawn_items.next_date,
			pawn_items.rate,
			(((bpas_pawn_items.quantity * bpas_pawn_items.price) - IFNULL(purchase_amount,0) - IFNULL(return_amount,0)) / (bpas_pawn_items.quantity - IFNULL(pawn_return_items.quantity,0)- IFNULL(pawn_purchase_items.quantity,0))) as price,
			pawn_items.quantity,
			IFNULL(pawn_return_items.quantity,0) as pawn_return_qty,
			IFNULL(pawn_purchase_items.quantity,0) as pawn_purchase_qty,
			(bpas_pawn_items.quantity - IFNULL(pawn_return_items.quantity,0)- IFNULL(pawn_purchase_items.quantity,0)) as quantity_balance,
			((bpas_pawn_items.quantity * bpas_pawn_items.price) - IFNULL(purchase_amount,0) - IFNULL(return_amount,0)) as pawn_amount
		")
		->from('pawn_items')
		->join('(
					SELECT
						bpas_pawn_purchase_items.pawn_id,
						bpas_pawn_purchase_items.product_id,
						bpas_pawn_purchase_items.product_unit_id,
						bpas_pawn_purchase_items.serial_no,
						bpas_pawn_purchase_items.expiry,
						bpas_pawn_purchase_items.pawn_price,
						bpas_pawn_purchase_items.pawn_rate,
						sum(
							bpas_pawn_purchase_items.quantity
						) AS quantity,
						sum(
							bpas_pawn_purchase_items.price * bpas_pawn_purchase_items.quantity
						) AS purchase_amount
					FROM
						bpas_pawn_purchase_items
					GROUP BY
						pawn_id,
						product_id,
						product_unit_id,
						serial_no,
						expiry,
						pawn_price,
						pawn_rate
				) as pawn_purchase_items','
					pawn_items.pawn_id = pawn_purchase_items.pawn_id 
					pawn_items.product_id = pawn_purchase_items.product_id AND
					pawn_items.product_unit_id = pawn_purchase_items.product_unit_id AND
					pawn_items.serial_no = pawn_purchase_items.serial_no AND
					pawn_items.expiry = pawn_purchase_items.expiry AND
					pawn_items.price = pawn_purchase_items.pawn_price AND
					pawn_items.rate = pawn_purchase_items.pawn_rate
				','left')
		->join('(
					SELECT
						bpas_pawn_return_items.pawn_id,
						bpas_pawn_return_items.product_id,
						bpas_pawn_return_items.product_unit_id,
						bpas_pawn_return_items.serial_no,
						bpas_pawn_return_items.expiry,
						bpas_pawn_return_items.pawn_price,
						bpas_pawn_return_items.pawn_rate,
						sum(
							bpas_pawn_return_items.quantity
						) AS quantity,
						sum(
							bpas_pawn_return_items.return_amount
						) AS return_amount
					FROM
						bpas_pawn_return_items
					GROUP BY
						pawn_id,
						product_id,
						product_unit_id,
						serial_no,
						expiry,
						pawn_price,
						pawn_rate
				) as pawn_return_items','
					pawn_items.pawn_id = pawn_return_items.pawn_id 
					pawn_items.product_id = pawn_return_items.product_id AND
					pawn_items.product_unit_id = pawn_return_items.product_unit_id AND
					pawn_items.serial_no = pawn_return_items.serial_no AND
					pawn_items.expiry = pawn_return_items.expiry AND
					pawn_items.price = pawn_return_items.pawn_price AND
					pawn_items.rate = pawn_return_items.pawn_rate
				','left')		
		->join('pawns','pawns.id = pawn_items.pawn_id','inner');
		$where = 'bpas_pawn_items.quantity != (IFNULL(pawn_return_items.quantity,0) + IFNULL(pawn_purchase_items.quantity,0))';
		$this->datatables->where($where);
		if ($warehouse_id) {
			$this->datatables->where('pawns.warehouse_id', $warehouse_id);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('pawns.biller_id =', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('pawns.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        if (!$this->Customer && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('pawns.created_by', $this->session->userdata('user_id'));
        }

        if (!empty($action)) {
        	$this->datatables->add_column("Actions", $action, "id");
        }
        echo $this->datatables->generate();
	}

	function getPawnReturns($warehouse_id = null, $biller_id = NULL){
		$this->bpas->checkPermissions('returns');
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
        $this->load->library('datatables');
		$this->datatables
			->select("pawn_returns.id as id, 
			DATE_FORMAT(".$this->db->dbprefix('pawn_returns').".date, '%Y-%m-%d %T') as date, 
			pawn_returns.reference_no,
			pawns.reference_no as pawn_ref,
			projects.project_name,
			pawn_returns.customer, 
			pawn_returns.grand_total,
			pawn_returns.attachment")
		->from('pawn_returns')
		->join('pawns','pawns.id = pawn_returns.pawn_id','inner');
		$this->datatables->join("projects","projects.project_id=pawn_returns.project_id","left");
		if ($warehouse_id) {
			$this->datatables->where('pawn_returns.warehouse_id', $warehouse_id);
		}
		if ($biller_id) {
             $this->datatables->where('pawn_returns.biller_id', $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('pawn_returns.biller_id =', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('pawn_returns.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        if (!$this->Customer && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('pawn_returns.created_by', $this->session->userdata('user_id'));
        }
		
		if (!empty($action)) {
			$this->datatables->add_column("Actions", $action, "id");
		}
        echo $this->datatables->generate();
	}
	
	
	function getPawnPurchase($warehouse_id = null, $biller_id = NULL){
		$this->bpas->checkPermissions('purchases');
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
        $this->load->library('datatables');
		$this->datatables
			->select("pawn_purchases.id as id, 
			DATE_FORMAT(".$this->db->dbprefix('pawn_purchases').".date, '%Y-%m-%d %T') as date, 
			pawn_purchases.reference_no,
			pawns.reference_no as pawn_ref,
			projects.project_name,
			pawn_purchases.customer, 
			pawn_purchases.grand_total,
			pawn_purchases.attachment")
		->from('pawn_purchases')
		->join('pawns','pawns.id = pawn_purchases.pawn_id','inner');
		$this->datatables->join("projects","projects.project_id=pawn_purchases.project_id","left");
		if ($warehouse_id) {
			$this->datatables->where('pawn_purchases.warehouse_id', $warehouse_id);
		}
		if ($biller_id) {
             $this->datatables->where('pawn_purchases.biller_id', $biller_id);
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('pawn_purchases.biller_id =', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('pawn_purchases.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        if (!$this->Customer && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('pawn_purchases.created_by', $this->session->userdata('user_id'));
        }
		if (!empty($action)) {
			$this->datatables->add_column("Actions", $action, "id");
		}
        echo $this->datatables->generate();
	}
	public function pawns_action()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }


        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');


        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {

                    $this->bpas->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->pawns_model->deletePawn($id);
                    }
                    $this->session->set_flashdata('message', lang("pawns_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);

                }  elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('pawns'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('principal'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('payment_rate'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('pawn_status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $pawn = $this->pawns_model->getPawnByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($pawn->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pawn->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pawn->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pawn->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $pawn->principal);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatMoney($pawn->payment_rate));
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($pawn->status));
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
                    $filename = 'pawn_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_pawn_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function pawn_returns_action()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->pawns_model->deletePawnReturn($id);
                    }
                    $this->session->set_flashdata('message', lang("pawn_returns_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);

                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('pawn_returns'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no_to'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $pawn = $this->pawns_model->getPawnReturnByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($pawn->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pawn->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $pawn->pawn_ref);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pawn->biller);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $pawn->customer);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $pawn->grand_total);;
       
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'pawn_return' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_pawn_return_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function pawn_purchases_action()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }


        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');


        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {

                    $this->bpas->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->pawns_model->deletePawnPurchase($id);
                    }
                    $this->session->set_flashdata('message', lang("pawn_purchases_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);

                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('pawn_purchases'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no_to'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $pawn = $this->pawns_model->getPawnPurchaseByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($pawn->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pawn->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $pawn->pawn_ref);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pawn->biller);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $pawn->customer);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $pawn->grand_total);;
       
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'pawn_purchase' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_pawn_purchase_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function pawn_products_action()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }


        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');


        if ($this->form_validation->run() == true) {
			
            if (!empty($_POST['val'])) {
				$result = array_unique($_POST['val']);
                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('pawn_products'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('product'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('serial_no'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('next_payment'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('rate'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('price'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('quantity'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('return_quantity'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('purchase_quantity'));
					$this->excel->getActiveSheet()->SetCellValue('L1', lang('balance_quantity'));
					$this->excel->getActiveSheet()->SetCellValue('M1', lang('principal'));

                    $row = 2;
                    foreach ($result as $id) {
                        $pawns = $this->pawns_model->getPawnItemWIthPurchase($id);
						if($pawns){
							foreach($pawns as $pawn){
								$balance_qty = $pawn->quantity - $pawn->purchase_qty - $pawn->return_qty;
								$principal = ($pawn->quantity * $pawn->price) - $pawn->purchase_amount - $pawn->return_amount;
								$pawn->price = $principal / $balance_qty;
								$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($pawn->date));
								$this->excel->getActiveSheet()->SetCellValue('B' . $row, $pawn->reference_no);
								$this->excel->getActiveSheet()->SetCellValue('C' . $row, $pawn->customer);
								$this->excel->getActiveSheet()->SetCellValue('D' . $row, $pawn->product_name);
								$this->excel->getActiveSheet()->SetCellValue('E' . $row, $pawn->serial_no);
								$this->excel->getActiveSheet()->SetCellValue('F' . $row, $pawn->next_date);
								$this->excel->getActiveSheet()->SetCellValue('G' . $row, $pawn->rate);
								$this->excel->getActiveSheet()->SetCellValue('H' . $row, $pawn->price);
								$this->excel->getActiveSheet()->SetCellValue('I' . $row, $pawn->quantity);
								$this->excel->getActiveSheet()->SetCellValue('J' . $row, $pawn->return_qty);
								$this->excel->getActiveSheet()->SetCellValue('K' . $row, $pawn->purchase_qty);
								$this->excel->getActiveSheet()->SetCellValue('L' . $row, $balance_qty);
								$this->excel->getActiveSheet()->SetCellValue('M' . $row, $principal);
								$row++;
							}
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
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'pawn_product' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_pawn_product_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function add()
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        $this->session->unset_userdata('csrf_token');
		
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pw',$biller_id);
            if ($this->Owner || $this->Admin || $this->bpas->GP['pawns-date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
		
			$payment_term = $this->input->post('payment_term');
			$project_id = $this->input->post('project');	
			$rate = $this->input->post('rate');			
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $i = sizeof($_POST['product']);
            $principal =0;
            for ($r = 0; $r < $i; $r++) {
				$item_id = $_POST['product_id'][$r];
                $item_code = $_POST['product'][$r];
				$item_name = $_POST['product_name'][$r];
				$item_type = $_POST['product_type'][$r];
                $price = $_POST['unit_price'][$r];
                $quantity = $_POST['quantity'][$r];
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->bpas->fsd($_POST['expiry'][$r]) : null;
                $next_date = date('Y-m-d', strtotime($item_expiry. ' + '.$payment_term.' days'));
				$item_unit = $_POST['product_unit'][$r];
				$item_rate = $_POST['product_rate'][$r];
				$item_note = $_POST['pnote'][$r];
				$serial_no = $_POST['serial_no'][$r];
                if (isset($item_code) &&  isset($quantity)) {
					if ($item_type == 'manual') {
						$add_product = $_POST['add_product'][$r];
						if($add_product==1){
							if($this->site->getProductByCode($item_code)){
								$item_code = rand(10000000,99999999);
							};
							$addProduct = array(
									'code' 		=> $item_code,
									'barcode_symbology' => 'code128',
									'name' => $item_name,
									'type' => 'standard',
									'category_id' => $this->Settings->manual_category,
									'cost' => $price,
									'price' => $price,
									'unit' => $this->Settings->manual_unit,
									'sale_unit' => $this->Settings->manual_unit,
									'purchase_unit' => $this->Settings->manual_unit,
									'alert_quantity' => 0,
									'manual_product' => 1
								);
							$item_id = $this->pawns_model->addProduct($addProduct);	
							$item_type = 'standard';
							$item_unit = $this->Settings->manual_unit;
						}
                    }
                    $product_details = $this->pawns_model->getProductByCode($item_code);
					if(!$product_details){
						$product_details->type = 'manual';
						$product_details->id = $item_id;
						$product_details->code = $item_code;
						$product_details->name = $item_name;
					}
					$unit = $this->site->getProductUnit($product_details->id,$item_unit);
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry < $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            admin_redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }

                    $products[] = array(
                        'product_id' 	=> $product_details->id,
                        'product_code' 	=> $product_details->code,
                        'product_name' 	=> $product_details->name,
						'product_type' 	=> $item_type,
                        'price' 		=> $price,
                        'quantity' 		=> $quantity,
                        'product_unit_id' => $item_unit,
                        'unit_quantity' => $unit->unit_qty,
                        'expiry' 		=> $item_expiry,
						'next_date' 	=> $next_date,
						'product_note' 	=> $item_note,
						'serial_no' 	=> $serial_no,
						'rate' 			=> $item_rate,
                    );
					if($this->Settings->module_account == 1){
						$accTrans[] = array(
							'tran_type' 	=> 'Pawn',
							'tran_date' 	=> $date,
							'reference_no' 	=> $reference,
							'account_code' 	=> $this->accounting_setting->default_stock,
							'amount' 		=> $price * $quantity,
							'narrative' 	=> 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$price,
							'description' 	=> $note,
							'biller_id' 	=> $biller_id,
							'project_id' 	=> $project_id,
							'user_id' 		=> $this->session->userdata('user_id'),
							'customer_id' 	=> $customer_id,
						);
					}
					
					$total = $quantity * $price;
                    $principal += $total;
                }
            }
		
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
            $data = array(
				'reference_no' => $reference,
                'date' => $date,
                'customer_id' => $customer_id,
                'customer' => $customer,
				'project_id' => $project_id,
				'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'principal' => $principal,
				'rate' => $rate,
				'payment_term' => $payment_term,
                'created_by' => $this->session->userdata('user_id'),
            );
			//$cash_account = $this->site->getCashAccountByID($biller_details->default_cash);
			$paying_from = $this->accounting_setting->default_cash; //$cash_account->account_code;
			$payment = array(
				'date' 			=> $date,
				'reference_no' 	=> $reference,
				'amount' 		=> $principal,
				'paid_by' 		=> $paying_from,
				'created_by' 	=> $this->session->userdata('user_id'),
				'type' 			=> 'pawn_sent',
				'bank_account' 	=> $paying_from,
			);
			
			if($this->Settings->module_account == 1){
				$accTrans[] = array(
					'tran_type' 	=> 'Pawn',
					'tran_date' 	=> $date,
					'reference_no' 	=> $reference,
					'account_code' 	=> $paying_from,
					'amount' 		=> -($principal),
					'narrative' 	=> 'Pawn',
					'description' 	=> $note,
					'biller_id' 	=> $biller_id,
					'project_id' 	=> $project_id,
					'user_id' 		=> $this->session->userdata('user_id'),
					'customer_id' 	=> $customer_id,
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }

        if ($this->form_validation->run() == true && $this->pawns_model->addPawn($data, $products,$payment, $accTrans)) {
            $this->session->set_userdata('remove_pwls', 1);
            $this->session->set_flashdata('message', $this->lang->line("pawn_added"));
            admin_redirect('pawns');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['customers'] = $this->site->getAllCompanies('customer');
			$this->data['user'] = $this->site->getUser($this->session->userdata('user_id'));
			$this->data['projects'] = $this->site->getAllProject();
            $this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['billers'] = $this->site->getBillers();
		//	$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => admin_url('pawns'), 'page' => lang('pawn')), array('link' => admin_url('pawns'), 'page' => lang('pawns')), array('link' => '#', 'page' => lang('add_pawn')));
            $meta = array('page_title' => lang('add_pawn'), 'bc' => $bc);
            $this->page_construct('pawns/add', $meta, $this->data);
        }
    }
	public function edit($id)
    {
        $this->bpas->checkPermissions();
		if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $pawn = $this->pawns_model->getPawnByID($id);
		$pawnInfo = $this->pawns_model->getPawnInfo($id);
		if ($pawnInfo->return_id > 0 || $pawnInfo->purchase_id > 0) {
            $this->session->set_flashdata('error', lang('pawn_cannot_edit').' '.$pawnInfo->reference_no);
            admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
        }
		
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        $this->session->unset_userdata('csrf_token');
		
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pw',$biller_id);
            if ($this->Owner || $this->Admin || $this->bpas->GP['pawns-date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
	
			$payment_term = $this->input->post('payment_term');
			$rate = $this->input->post('rate');
			$project_id = $this->input->post('project');			
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $i = sizeof($_POST['product']);

            for ($r = 0; $r < $i; $r++) {
				$item_id = $_POST['product_id'][$r];
                $item_code = $_POST['product'][$r];
				$item_name = $_POST['product_name'][$r];
				$item_type = $_POST['product_type'][$r];
                $price = $_POST['unit_price'][$r];
                $quantity = $_POST['quantity'][$r];
				$product_rate = $_POST['product_rate'][$r];
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->bpas->fsd($_POST['expiry'][$r]) : null;
                $next_date = $this->bpas->fsd($_POST['next_payment'][$r]);
				$item_unit = $_POST['product_unit'][$r];
				$item_note = $_POST['pnote'][$r];
				$serial_no = $_POST['serial_no'][$r];
                if (isset($item_code) &&  isset($quantity)) {
                    $product_details = $this->pawns_model->getProductByCode($item_code);
					if(!$product_details){
						$product_details->type = 'manual';
						$product_details->id = $item_id;
						$product_details->code = $item_code;
						$product_details->name = $item_name;
					}
					$unit = $this->site->getProductUnit($product_details->id,$item_unit);
                    
                    $products[] = array(
						'pawn_id' => $id,
                        'product_id' => $product_details->id,
                        'product_code' => $product_details->code,
                        'product_name' => $product_details->name,
						'product_type' => $item_type,
                        'price' => $price,
                        'quantity' => $quantity,
                        'product_unit_id' => $item_unit,
                        'unit_quantity' => $unit->unit_qty,
                        'expiry' => $item_expiry,
						'next_date' => $next_date,
						'product_note' => $item_note,
						'serial_no' => $serial_no,
						'rate' => $product_rate,
                    );
					
					if($this->Settings->accounting == 1){
						$accTrans[] = array(
							'transaction' => 'Pawn',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference,
							'account' => $this->accounting_setting->default_stock,
							'amount' => $price * $quantity,
							'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$price,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $customer_id,
						);
					}
					
					$total = $quantity * $price;
                    $principal += $total;
                }
            }
		
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
            $data = array(
				'reference_no' => $reference,
                'date' => $date,
                'customer_id' => $customer_id,
                'customer' => $customer,
				'project_id' => $project_id,
				'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'principal' => $principal,
				'rate' => $rate,
				'payment_term' => $payment_term,
                'created_by' => $this->session->userdata('user_id'),
            );
			$cash_account = $this->site->getCashAccountByID($biller_details->default_cash);
			$paying_from = $cash_account->account_code;
			$payment = array(
				'date' => $date,
				'pawn_id' => $id,
				'reference_no' => $reference,
				'amount' => $principal,
				'paid_by' => $biller_details->default_cash,
				'created_by' => $this->session->userdata('user_id'),
				'type' => 'pawn_sent',
				'account_code' => $paying_from,
			);
			
			if($this->Settings->accounting == 1){
				$accTrans[] = array(
					'transaction' => 'Pawn',
					'transaction_id' => $id,
					'transaction_date' => $date,
					'reference' => $reference,
					'account' => $paying_from,
					'amount' => -($principal),
					'narrative' => 'Pawn',
					'description' => $note,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'user_id' => $this->session->userdata('user_id'),
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }

        if ($this->form_validation->run() == true && $this->pawns_model->updatePawn($id, $data, $products, $payment, $accTrans)) {
            $this->session->set_userdata('remove_pwls', 1);
            $this->session->set_flashdata('message', $this->lang->line("pawn_updated"));
            admin_redirect('pawns');
        } else {
			
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['pawn'] = $pawn;
            if ($this->Settings->disable_editing) {
                if ($this->data['pawn']->date <= date('Y-m-d', strtotime('-'.$this->Settings->disable_editing.' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang("pawn_x_edited_older_than_x_days"), $this->Settings->disable_editing));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
            }
            $pawn_items = $this->pawns_model->getAllPawnItems($id);
            krsort($pawn_items);
            $c = rand(100000, 9999999);
            foreach ($pawn_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
				$row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
				$row->base_unit_price = $row->price;
				$row->base_unit = $row->unit;
				$row->unit_price = $item->price;
				$row->product_rate = $item->rate;
                $row->unit = $item->product_unit_id;
				$row->next_date = $this->bpas->hrsd($item->next_date);
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->bpas->hrsd($item->expiry) : '');
                $row->qty = $item->quantity;
				$row->serial_no = $item->serial_no;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
				$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
				$ri = $this->Settings->item_addition ? $row->id : $c;
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
                    'row' => $row, 'units' => $units);
                $c++;
				
            }
			
            $this->data['items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['customers'] = $this->site->getAllCompanies('customer');
            $this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['projects'] = $this->site->getAllProject();
			$this->data['user'] = $this->site->getUser($this->session->userdata('user_id'));
			$this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getBillers() : null;
			//$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
			$this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pwls', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => admin_url('pawns'), 'page' => lang('pawn')), array('link' => admin_url('pawns'), 'page' => lang('pawns')), array('link' => '#', 'page' => lang('edit_pawn')));
            $meta = array('page_title' => lang('edit_pawn'), 'bc' => $bc);
            $this->page_construct('pawns/edit', $meta, $this->data);
        }
    }
	
	public function get_project()
	{
		$id = $this->input->get("biller");
		$project_id = $this->input->get("project");
		$rows = $this->site->getAllProjectByBillerID($id);
		$user = $this->site->getUser($this->session->userdata("user_id"));
		$project = json_decode($user->project_ids);
			
		$pl = array(lang('select')." ".lang('project'));
		if ($this->Owner || $this->Admin || $project[0] === 'all') {
			foreach($rows as $row){
				$pl[$row->id] = $row->name;
			}
		}else{
			foreach($rows as $row){
				if(in_array($row->id, $project)){
					$pl[$row->id] = $row->name;
				}
			}
		}
		$opt = form_dropdown('project', $pl, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'id="project" class="form-control"');
		echo json_encode(array("result" => $opt));
	}

	public function suggestions()
    {
        $term = $this->input->get('term', true);
		$warehouse_id = $this->input->get('warehouse_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->bpas->analyze_term($term);
        $sr = $analyzed['term'];

        $rows = $this->pawns_model->getProductNames($sr);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
				$row->base_unit_price = $row->price;
				$row->unit_price = $row->price;
                $row->base_unit = $row->unit;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->new_entry = 1;
                $row->expiry = '';
				$row->product_rate = '';
                $row->qty = 1;
				$row->serial_no = '';
                unset($row->details, $row->product_details, $row->price, $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
				$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);


                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
                    'row' => $row, 'units' => $units);
                $r++;
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$pawn = $this->pawns_model->getPawnByID($id);
        if ($this->pawns_model->deletePawn($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("pawn_deleted")." ".$pawn->reference_no;
				die();
            }
            $this->session->set_flashdata('message', lang('pawn_deleted')." ".$pawn->reference_no);
            admin_redirect('welcome');
        }
    }
	
	public function close($id=null){
		$this->bpas->checkPermissions('closes', true);
		if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$pawn = $this->pawns_model->getPawnByID($id);
        if ($this->pawns_model->closePawn($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("pawn_closed")." ".$pawn->reference_no;
				die();
            }
            $this->session->set_flashdata('message', lang('pawn_closed')." ".$pawn->reference_no);
            admin_redirect('welcome');
        }
	}
	
	public function payments($id = null)
    {
        $this->bpas->checkPermissions(false, true);
        $this->data['payments'] = $this->pawns_model->getRatePaymentByPawn($id);
        $this->data['pawn'] = $this->pawns_model->getPawnByID($id);
		$this->load->view($this->theme . 'pawns/payments', $this->data);
    }
	
	public function add_purchase($id = null)
    {
        $this->bpas->checkPermissions('purchases', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $pawn = $this->pawns_model->getPawnByID($id);
        $this->form_validation->set_rules('pawn_id', lang("pawn"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin  || $this->bpas->GP['pawns-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pwp',$pawn->biller_id);
			
            
			$i = sizeof($_POST['product_id']);
			$grand_total = 0;
			$product_serials = array();
			
		
			$paying_to = $this->input->post('paid_by');
            for ($r = 0; $r < $i; $r++) {
				$product_id = $_POST['product_id'][$r];
				$product_code = $_POST['product_code'][$r];
				$product_name = $_POST['product_name'][$r];
				$product_unit_id = $_POST['product_unit_id'][$r];
				$unit_quantity = $_POST['unit_quantity'][$r];
				$expiry = $_POST['expiry'][$r];
				$serial_no = $_POST['serial_no'][$r];
				$purchase_qty = $_POST['purchase_qty'][$r];
				$purchase_cost = $_POST['purchase_cost'][$r];
				$product_price = $_POST['product_price'][$r];
				$pawn_price = $_POST['pawn_price'][$r];
				$pawn_rate = $_POST['pawn_rate'][$r];
				if($purchase_qty > 0){
					$grand_total += ($purchase_qty * $purchase_cost);
					$products[] = array(
                        'product_id' => $product_id,
						'pawn_id' => $pawn->id,
						'product_code' => $product_code,
						'product_name' => $product_name,
						'product_unit_id' => $product_unit_id,
						'unit_quantity' => $unit_quantity,
						'pawn_price' => $pawn_price,
						'pawn_rate' => $pawn_rate,
						'serial_no' => $serial_no,
						'expiry' => $expiry,
						'quantity' => $purchase_qty,
						'cost' => $purchase_cost,
						'price' => $product_price,
                    );
					if($serial_no!=''){
						$reactive = 0;
						$product_serial = $this->pawns_model->getProductSerial($serial_no,$product_id,$warehouse_id);
						if($product_serial){
							if($product_serial->inactive==0){
								$this->session->set_flashdata('error', lang("serial_is_existed").' ('.$serial_no.') ');
								admin_redirect($_SERVER["HTTP_REFERER"]);
							}else {
								$reactive = 1;
							}
						}else{
							$product_serials[] = array(
								'product_id' => $product_id,
								'warehouse_id' => $warehouse_id,
								'date' => $date,
								'serial' => $serial_no,
								'cost' => ($purchase_cost+$product_price),
								'price' => ($purchase_cost+$product_price),
								'pawn_id' => $pawn->id,
								'customer_id' => $pawn->customer_id,
							);
						}
					}
					if($reactive!=1){
						$serial_no = '';;
					}
					$unit = $this->site->getProductUnit($product_id,$product_unit_id);
					$stockmoves[] = array(
						'transaction' => 'Pawn Purchase',
                        'product_id' => $product_id,
						'product_code' => $product_code,
                        'quantity' => $purchase_qty,
                        'unit_quantity' => $unit_quantity,
						'warehouse_id' => $pawn->warehouse_id,
						'unit_code' => $unit->code,
						'unit_id' => $product_unit_id,
                        'date' => $date,
						'expiry' => $expiry,
						'serial_no' => $serial_no,
						'real_unit_cost' => ($purchase_cost+$product_price),
						'reference_no' => $reference_no,
						'user_id' => $this->session->userdata('user_id'),
                    ); 
					if($this->Settings->accounting == 1){
						
							$accTrans[] = array(
								'transaction' => 'Pawn Purchase',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $this->accounting_setting->default_stock,
								'amount' => ($purchase_cost * $purchase_qty),
								'narrative' => 'Product Code: '.$product_code.'#'.'Qty: '.$purchase_qty.'#'.'Cost: '.$purchase_cost,
								'biller_id' => $pawn->biller_id,
								'project_id' => $pawn->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' =>  $pawn->customer_id,
							);
						
					}
				}
			}
			
			$data = array(
				'reference_no' => $reference_no,
                'date' => $date,
				'pawn_id' => $pawn->id,
                'customer_id' => $pawn->customer_id,
                'customer' => $pawn->customer,
				'project_id' => $pawn->project_id,
				'biller_id' => $pawn->biller_id,
                'biller' => $pawn->biller,
				'grand_total' => $grand_total,
                'warehouse_id' => $pawn->warehouse_id,
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
            );
			if($grand_total > 0){
				$reference_payment = $this->site->getReference('pwps',$pawn->biller_id);
				$payment = array(
					'date' => $date,
					'pawn_id' => $this->input->post('pawn_id'),
					'reference_no' => $reference_payment,
					'amount' => $grand_total,
					'paid_by' => $this->input->post('paid_by'),
					'note' => $this->input->post('note'),
					'created_by' => $this->session->userdata('user_id'),
					'type' => 'pawn_sent',
					'account_code' => $paying_to,
				);
				
				$accTrans[] = array(
					'transaction' => 'Pawn Purchase',
					'transaction_date' => $date,
					'reference' => $reference_no,
					'account' => $paying_to,
					'amount' => -($grand_total),
					'narrative' => 'Pawn Purchase',
					'biller_id' => $pawn->biller_id,
					'project_id' => $pawn->project_id,
					'user_id' => $this->session->userdata('user_id'),
					'customer_id' =>  $pawn->customer_id,
				);
			}
			
			
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
				$payment['data'] = $photo;
            }
			
			if (empty($products)) {
                $this->session->set_flashdata('error', lang("pawn_quantity_required"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
            } else {
                krsort($products);
            }
	
        } elseif ($this->input->post('add_purchase')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->pawns_model->addPawnPurchase($data, $products, $payment,$stockmoves, $product_serials, $accTrans)) {
			$this->session->set_flashdata('message', lang("purchase_added"));
            admin_redirect("pawns/purchase");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['pawn'] = $pawn;
			$this->data['pawn_items'] = $this->pawns_model->getPawnItemWIthPurchase($pawn->id);
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'pawns/add_purchase', $this->data);
        }
    }

	
	public function pawn_return($id = null)
    {
       $this->bpas->checkPermissions('returns', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $pawn = $this->pawns_model->getPawnByID($id);
        $this->form_validation->set_rules('pawn_id', lang("pawn"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin  || $this->bpas->GP['pawns-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }

			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pwr',$pawn->biller_id);
			$reference_payment = $this->site->getReference('pwpr',$pawn->biller_id);
            $cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_to = $cash_account->account_code;
			$i = sizeof($_POST['product_id']);
			$total_pricinple = 0;
			$total_rate = 0;
            for ($r = 0; $r < $i; $r++) {
				$product_id = $_POST['product_id'][$r];
				$product_code = $_POST['product_code'][$r];
				$product_name = $_POST['product_name'][$r];
				$product_unit_id = $_POST['product_unit_id'][$r];
				$unit_quantity = $_POST['unit_quantity'][$r];
				$expiry = $_POST['expiry'][$r];
				$serial_no = $_POST['serial_no'][$r];
				$pawn_price = $_POST['pawn_price'][$r];
				$pawn_rate = $_POST['pawn_rate'][$r];
				$return_qty = $_POST['return_qty'][$r];
				$product_price = $_POST['product_price'][$r];
				$pawn_item_id = $_POST['pawn_item_id'][$r];
				$return_principal = $_POST['return_principal'][$r];
				$payment_rate = $_POST['payment_rate'][$r];
				$pawn_qty = $_POST['pawn_qty'][$r];

				if($return_qty > 0 || $return_principal > 0){
					$total_pricinple += $return_principal;
					$products[] = array(
                        'product_id' => $product_id,
						'pawn_id' => $pawn->id,
						'product_code' => $product_code,
						'product_name' => $product_name,
						'product_unit_id' => $product_unit_id,
						'unit_quantity' => $unit_quantity,
						'pawn_price' => $pawn_price,
						'pawn_rate' => $pawn_rate,
						'serial_no' => $serial_no,
						'expiry' => $expiry,
						'quantity' => $return_qty,
						'return_amount' => $return_principal,
						'payment_rate' => $payment_rate,
                    );
					
					if($this->Settings->accounting == 1){
						$accTrans[] = array(
							'transaction' => 'Pawn Return',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $this->accounting_setting->default_stock,
							'amount' => -($return_principal),
							'narrative' => 'Product Code: '.$product_code.'#'.'Qty: '.$return_qty.'#'.'Return Amount: '.$return_principal,
							'description' => $this->input->post('note'),
							'biller_id' => $pawn->biller_id,
							'project_id' => $pawn->project_id,
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $pawn->customer_id,
						);
					}
					
					if($payment_rate > 0){
						$total_rate += $payment_rate;
						$rate_products[] = array(
							'product_id' => $product_id,
							'pawn_item_id' => $pawn_item_id,
							'pawn_id' => $pawn->id,
							'product_code' => $product_code,
							'product_name' => $product_name,
							'pawn_unit_id' => $product_unit_id,
							'pawn_unit_quantity' => $unit_quantity,
							'pawn_serial_no' => $serial_no,
							'pawn_expiry' => $expiry,
							'pawn_rate' => $pawn_rate,
							'pawn_price' => $pawn_price,
							'pawn_quantity' => $pawn_qty,
							'price' => $product_price,
							'payment_rate' => $payment_rate,
							'next_date' => $date,
						);
						
						if($this->Settings->accounting == 1){
							$productAcc = $this->site->getProductAccByProductId($product_id);
							if($productAcc){
								$product_rate_income_acc = $productAcc->pawn_acc;
							}else{
								$product_rate_income_acc =$this->accounting_setting->other_income;
							}
							$accRateTrans[] = array(
								'transaction' => 'Pawn Rate',
								'transaction_date' => $date,
								'reference' => $reference_payment,
								'account' => $product_rate_income_acc,
								'amount' => -($payment_rate),
								'narrative' => 'Product Code: '.$product_code.'#'.'Qty: '.$pawn_qty.'#'.'Rate: '.$payment_rate,
								'description' => $this->input->post('note'),
								'biller_id' => $pawn->biller_id,
								'project_id' => $pawn->project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $pawn->customer_id,
							);
						}
						
					}
				}
			}

			if($total_rate > 0){
				$rate_data = array(
					'reference_no' => $reference_payment,
					'date' => $date,
					'pawn_id' => $pawn->id,
					'customer_id' => $pawn->customer_id,
					'customer' => $pawn->customer,
					'project_id' => $pawn->project_id,
					'biller_id' => $pawn->biller_id,
					'biller' => $pawn->biller,
					'grand_total' => $total_rate,
					'warehouse_id' => $pawn->warehouse_id,
					'note' => $this->input->post('note'),
					'created_by' => $this->session->userdata('user_id'),
				);
				
				$rate_payment = array(
					'date' => $date,
					'pawn_id' => $this->input->post('pawn_id'),
					'reference_no' => $reference_payment,
					'amount' => $total_rate,
					'paid_by' => 'cash',
					'note' => $this->input->post('note'),
					'created_by' => $this->session->userdata('user_id'),
					'type' => 'pawn_rate',
					'account_code' => $paying_to,
				);
				
				if($this->Settings->accounting == 1){
					$accRateTrans[] = array(
						'transaction' => 'Pawn Rate',
						'transaction_date' => $date,
						'reference' => $reference_payment,
						'account' => $paying_to,
						'amount' => $total_rate,
						'narrative' => 'Pawn Payment Rate',
						'description' => $this->input->post('note'),
						'biller_id' => $pawn->biller_id,
						'project_id' => $pawn->project_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $pawn->customer_id,
					);
				}
				
			}

			$data = array(
				'reference_no' => $reference_no,
                'date' => $date,
				'pawn_id' => $pawn->id,
                'customer_id' => $pawn->customer_id,
                'customer' => $pawn->customer,
				'project_id' => $pawn->project_id,
				'biller_id' => $pawn->biller_id,
                'biller' => $pawn->biller,
				'grand_total' => $total_pricinple,
                'warehouse_id' => $pawn->warehouse_id,
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
            );
		
			$payment = array(
				'date' => $date,
				'pawn_id' => $this->input->post('pawn_id'),
				'reference_no' => $reference_payment,
				'amount' => $total_pricinple,
				'paid_by' => $this->input->post('paid_by'),
				'note' => $this->input->post('note'),
				'created_by' => $this->session->userdata('user_id'),
				'type' => 'pawn_received',
				'account_code' => $paying_to,
			);
			
			
			if($this->Settings->accounting == 1){
				$accTrans[] = array(
					'transaction' => 'Pawn Return',
					'transaction_date' => $date,
					'reference' => $reference_no,
					'account' => $paying_to,
					'amount' => $total_pricinple,
					'narrative' => 'Pawn Return',
					'description' => $this->input->post('note'),
					'biller_id' => $pawn->biller_id,
					'project_id' => $pawn->project_id,
					'user_id' => $this->session->userdata('user_id'),
					'customer_id' => $pawn->customer_id,
				);
			}

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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
				$payment['data'] = $photo;
				
				if($rate_payment){
					$rate_payment['attachment'] = $photo;
					$rate_payment['data'] = $photo;
				}
            }
			
			if (empty($products)) {
                $this->session->set_flashdata('error', lang("pawn_quantity_required"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
            } else {
                krsort($products);
            }
	
        } elseif ($this->input->post('add_return')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->pawns_model->addPawnReturn($data, $products, $payment, $rate_data, $rate_products, $rate_payment, $accTrans, $accRateTrans)) {
			$this->session->set_flashdata('message', lang("pawn_returned"));
            admin_redirect("pawns/returns");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['pawn'] = $pawn;
			$this->data['pawn_items'] = $this->pawns_model->getPawnItemWIthPurchase($pawn->id);
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'pawns/pawn_return', $this->data);
        }
    }
	
	public function add_payment($id = null)
    {
       $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $pawn = $this->pawns_model->getPawnByID($id);
        $this->form_validation->set_rules('pawn_id', lang("pawn"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin  || $this->bpas->GP['pawns-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pwpr',$pawn->biller_id);
            
			$i = sizeof($_POST['product_id']);
			$grand_total = 0;
            for ($r = 0; $r < $i; $r++) {
				$pawn_item_id = $_POST['pawn_item_id'][$r];
				$product_id = $_POST['product_id'][$r];
				$product_code = $_POST['product_code'][$r];
				$product_name = $_POST['product_name'][$r];
				$product_unit_id = $_POST['product_unit_id'][$r];
				$unit_quantity = $_POST['unit_quantity'][$r];
				$expiry = $_POST['expiry'][$r];
				$serial_no = $_POST['serial_no'][$r];
				$product_price = $_POST['product_price'][$r];
				$pawn_price = $_POST['pawn_price'][$r];
				$pawn_rate = $_POST['pawn_rate'][$r];
				$payment_rate = $_POST['payment_rate'][$r];
				$pawn_qty = $_POST['pawn_qty'][$r];
				$next_date = $this->bpas->fsd($_POST['next_date'][$r]);
				
				if($payment_rate > 0){
					$grand_total += $payment_rate;
					$products[] = array(
                        'product_id' => $product_id,
						'pawn_item_id' => $pawn_item_id,
						'pawn_id' => $pawn->id,
						'product_code' => $product_code,
						'product_name' => $product_name,
						'pawn_unit_id' => $product_unit_id,
						'pawn_unit_quantity' => $unit_quantity,
						'pawn_serial_no' => $serial_no,
						'pawn_expiry' => $expiry,
						'pawn_rate' => $pawn_rate,
						'pawn_price' => $pawn_price,
						'pawn_quantity' => $pawn_qty,
						'price' => $product_price,
						'payment_rate' => $payment_rate,
						'next_date' => $next_date,
                    );
					
					$pawn_items[] = array(
                        'pawn_item_id' => $pawn_item_id,
						'next_date' => $next_date,
                    );
					
					if($this->Settings->accounting == 1){
						$productAcc = $this->site->getProductAccByProductId($product_id);
						if($productAcc){
							$product_rate_income_acc = $productAcc->pawn_acc;
						}else{
							$pawnAcc = $this->site->getAccountSettingByBiller($pawn->biller_id);
							$product_rate_income_acc = $pawnAcc->other_income_acc;
						}
						$accTrans[] = array(
							'transaction' => 'Pawn Rate',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $product_rate_income_acc,
							'amount' => -($payment_rate),
							'narrative' => 'Product Code: '.$product_code.'#'.'Qty: '.$pawn_qty.'#'.'Rate: '.$payment_rate,
							'description' => $this->input->post('note'),
							'biller_id' => $pawn->biller_id,
							'project_id' => $pawn->project_id,
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $pawn->customer_id,
						);
					}
					
				}
			}
			
			
			$data = array(
				'reference_no' => $reference_no,
                'date' => $date,
				'pawn_id' => $pawn->id,
                'customer_id' => $pawn->customer_id,
                'customer' => $pawn->customer,
				'project_id' => $pawn->project_id,
				'biller_id' => $pawn->biller_id,
                'biller' => $pawn->biller,
				'grand_total' => $grand_total,
				'paid_by' => $this->input->post('paid_by'),
                'warehouse_id' => $pawn->warehouse_id,
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
            );
			
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_to = $cash_account->account_code;
			$payment = array(
                'date' => $date,
                'pawn_id' => $this->input->post('pawn_id'),
                'reference_no' => $reference_no,
                'amount' => $grand_total,
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'pawn_rate',
				'account_code' => $paying_to,
            );
			
			if($this->Settings->accounting == 1){
				$accTrans[] = array(
					'transaction' => 'Pawn Rate',
					'transaction_date' => $date,
					'reference' => $reference_no,
					'account' => $paying_to,
					'amount' => $grand_total,
					'narrative' => 'Pawn Payment Rate',
					'description' => $this->input->post('note'),
					'biller_id' => $pawn->biller_id,
					'project_id' => $pawn->project_id,
					'user_id' => $this->session->userdata('user_id'),
					'customer_id' => $pawn->customer_id,
				);
			}
			
			
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
				$payment['data'] = $photo;
            }
			
			if (empty($products)) {
                $this->session->set_flashdata('error', lang("pawn_payment_rate_required"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
            } else {
                krsort($products);
            }
	
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->pawns_model->addPaymentRate($data, $products, $payment, $pawn_items, $accTrans)) {
			$this->session->set_flashdata('message', lang("payment_added"));
            admin_redirect("pawns/");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['pawn'] = $pawn;
			$this->data['pawn_items'] = $this->pawns_model->getPawnItemWIthPurchase($pawn->id);
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'pawns/add_payment', $this->data);
        }
    }
	
	
	public function edit_payment($id = null)
    {
       $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment = $this->pawns_model->getPawnPaymentByID($id);
		$pawn = $this->pawns_model->getPawnByID($payment->pawn_id);
        $this->form_validation->set_rules('pawn_id', lang("pawn"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->bpas->GP['pawns-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pwpr',$pawn->biller_id);
            
			$i = sizeof($_POST['product_id']);
			$grand_total = 0;
            for ($r = 0; $r < $i; $r++) {
				$pawn_item_id = $_POST['pawn_item_id'][$r];
				$product_id = $_POST['product_id'][$r];
				$product_code = $_POST['product_code'][$r];
				$product_name = $_POST['product_name'][$r];
				$product_unit_id = $_POST['product_unit_id'][$r];
				$unit_quantity = $_POST['unit_quantity'][$r];
				$expiry = $_POST['expiry'][$r];
				$serial_no = $_POST['serial_no'][$r];
				$product_price = $_POST['product_price'][$r];
				$pawn_price = $_POST['pawn_price'][$r];
				$pawn_rate = $_POST['pawn_rate'][$r];
				$payment_rate = $_POST['payment_rate'][$r];
				$pawn_qty = $_POST['pawn_qty'][$r];
				$next_date = $this->bpas->fsd($_POST['next_date'][$r]);
				if($payment_rate > 0){
					$grand_total += $payment_rate;
					$products[] = array(
						'pawn_item_id' => $pawn_item_id,
                        'product_id' => $product_id,
						'pawn_id' => $pawn->id,
						'pawn_rate_id' => $id,
						'product_code' => $product_code,
						'product_name' => $product_name,
						'pawn_unit_id' => $product_unit_id,
						'pawn_unit_quantity' => $unit_quantity,
						'pawn_serial_no' => $serial_no,
						'pawn_expiry' => $expiry,
						'pawn_rate' => $pawn_rate,
						'pawn_price' => $pawn_price,
						'pawn_quantity' => $pawn_qty,
						'price' => $product_price,
						'payment_rate' => $payment_rate,
						'next_date' => $next_date,
                    );
					$pawn_items[] = array(
                        'pawn_item_id' => $pawn_item_id,
						'next_date' => $next_date,
                    );
					if($this->Settings->accounting == 1){
						$productAcc = $this->site->getProductAccByProductId($product_id);
						if($productAcc){
							$product_rate_income_acc = $productAcc->pawn_acc;
						}else{
							$pawnAcc = $this->site->getAccountSettingByBiller($pawn->biller_id);
							$product_rate_income_acc = $pawnAcc->other_income_acc;
						}
						$accTrans[] = array(
							'transaction' => 'Pawn Rate',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $product_rate_income_acc,
							'amount' => -($payment_rate),
							'narrative' => 'Product Code: '.$product_code.'#'.'Qty: '.$pawn_qty.'#'.'Rate: '.$payment_rate,
							'description' => $this->input->post('note'),
							'biller_id' => $pawn->biller_id,
							'project_id' => $pawn->project_id,
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $pawn->customer_id,
						);
					}
				}
			}
			
			
			$data = array(
				'reference_no' => $reference_no,
                'date' => $date,
				'pawn_id' => $pawn->id,
                'customer_id' => $pawn->customer_id,
                'customer' => $pawn->customer,
				'project_id' => $pawn->project_id,
				'biller_id' => $pawn->biller_id,
                'biller' => $pawn->biller,
				'grand_total' => $grand_total,
				'paid_by' => $this->input->post('paid_by'),
                'warehouse_id' => $pawn->warehouse_id,
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
            );
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_to = $cash_account->account_code;
			$payment = array(
                'date' => $date,
                'pawn_id' => $this->input->post('pawn_id'),
				'pawn_rate_id' => $id,
                'reference_no' => $reference_no,
                'amount' => $grand_total,
				'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'pawn_rate',
				'account_code' => $paying_to,
            );
			
			if($this->Settings->accounting == 1){
				$accTrans[] = array(
					'transaction' => 'Pawn Rate',
					'transaction_id' => $id,
					'transaction_date' => $date,
					'reference' => $reference_no,
					'account' => $paying_to,
					'amount' => $grand_total,
					'narrative' => 'Pawn Payment Rate',
					'description' => $this->input->post('note'),
					'biller_id' => $pawn->biller_id,
					'project_id' => $pawn->project_id,
					'user_id' => $this->session->userdata('user_id'),
					'customer_id' => $pawn->customer_id,
				);
			}
			
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
				$payment['data'] = $photo;
            }
			
			if (empty($products)) {
                $this->session->set_flashdata('error', lang("pawn_payment_rate_required"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
            } else {
                krsort($products);
            }
	
        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->pawns_model->updatePaymentRate($id, $data, $products, $payment, $pawn_items, $accTrans)) {
			$this->session->set_flashdata('message', lang("payment_updated"));
            admin_redirect("pawns/");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['payment'] = $payment;
			$this->data['pawn'] = $pawn;
			$this->data['pawn_items'] = $this->pawns_model->getPawnRateItems($payment->id);
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'pawns/edit_payment', $this->data);
        }
    }

	
	
	public function delete_payment($id = null)
    {
        $this->bpas->checkPermissions('delete');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->pawns_model->deletePaymentRate($id)) {
            $this->session->set_flashdata('message', lang("payment_deleted"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	
	public function modal_view($id = null)
    {
        $this->bpas->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $pawn = $this->pawns_model->getPawnByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($pawn->created_by, true);
        }
        $this->data['customer'] = $this->site->getCompanyByID($pawn->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($pawn->biller_id);
        $this->data['created_by'] = $this->site->getUser($pawn->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($pawn->warehouse_id);
        $this->data['pawn'] = $pawn;
        $this->data['rows'] = $this->pawns_model->getAllPawnItems($id);
		
		if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Pawn',$pawn->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Pawn',$pawn->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme . 'pawns/modal_view', $this->data);
    }
	
	public function purchase_modal_view($id = null)
    {
        $this->bpas->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $pawn = $this->pawns_model->getPawnPurchaseByID($id);
		if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($pawn->created_by, true);
        }
        $this->data['customer'] = $this->site->getCompanyByID($pawn->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($pawn->biller_id);
        $this->data['created_by'] = $this->site->getUser($pawn->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($pawn->warehouse_id);
        $this->data['pawn'] = $pawn;
        $this->data['rows'] = $this->pawns_model->getAllPurhcasePawnItems($id);
		
		if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Pawn Purchase',$pawn->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Pawn Purchase',$pawn->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme . 'pawns/purchase_modal_view', $this->data);
    }
	
	public function return_modal_view($id = null)
    {
        $this->bpas->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $pawn = $this->pawns_model->getPawnReturnByID($id);
		if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($pawn->created_by, true);
        }
        $this->data['customer'] = $this->site->getCompanyByID($pawn->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($pawn->biller_id);
        $this->data['created_by'] = $this->site->getUser($pawn->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($pawn->warehouse_id);
        $this->data['pawn'] = $pawn;
        $this->data['rows'] = $this->pawns_model->getAllReturnPawnItems($id);
		
		if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Pawn Return',$pawn->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Pawn Return',$pawn->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme . 'pawns/return_modal_view', $this->data);
    }
	
	public function payment_note($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $payment = $this->pawns_model->getPawnPaymentByID($id);
		$inv = $this->pawns_model->getPawnByID($payment->pawn_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
		$this->data['pawn_items'] = $this->pawns_model->getPawnRateItems($payment->id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = lang("payment_note");
		
		if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Pawn Payment',$payment->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Pawn Payment',$payment->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme . 'pawns/payment_note', $this->data);
    }
	
	
	public function modal_payment($id = null){
		$this->bpas->checkPermissions('payments', true);
        $payment = $this->pawns_model->getPaymentByID($id);
		$inv = $this->pawns_model->getPawnByID($payment->pawn_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
		$pawn_rate_id = $payment->pawn_rate_id;
		if($payment->pawn_rate_id > 0){
			$payment = $this->pawns_model->getPawnPaymentByID($payment->pawn_rate_id);
			$this->data['pawn_items'] = $this->pawns_model->getPawnRateItems($payment->id);
		}else if($payment->pawn_return_id > 0){
			$data = $this->pawns_model->getPawnReturnByID($payment->pawn_return_id);
			$this->data['pawn_items'] = $this->pawns_model->getAllReturnPawnItems($payment->pawn_return_id);
		}else if($payment->pawn_purchase_id > 0){
			$data = $this->pawns_model->getPawnPurchaseByID($payment->pawn_purchase_id);
			$this->data['pawn_items'] = $this->pawns_model->getAllPurhcasePawnItems($payment->pawn_purchase_id);
		}else{
			$data = $this->pawns_model->getPawnByID($payment->pawn_id);
			$this->data['pawn_items'] = $this->pawns_model->getAllPawnItems($payment->pawn_id);
		}
        $this->data['inv'] = $inv;
		$this->data['data'] = $data;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = lang("payment_note");
		
		if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Pawn Payment',$payment->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Pawn Payment',$payment->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
		if($pawn_rate_id > 0){
			$this->load->view($this->theme . 'pawns/payment_note', $this->data);
		}else if($payment->pawn_return_id > 0){
			$this->load->view($this->theme . 'pawns/return_payment', $this->data);
		}else{
			$this->load->view($this->theme . 'pawns/purchase_payment', $this->data);
		}
	}
	
	public function get_customer_group()
	{
		$customer_id = $this->input->get('customer');
		$customer = $this->site->getCompanyByID($customer_id);
		$customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
		$rate = $customer_group->percent.'%';
		echo json_encode($rate);
	}
	

}
