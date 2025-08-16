<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Taxs extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
       // $this->lang->admin_load('taxs', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('taxs_model');
		$this->digital_upload_path = 'files/';
		$this->upload_path = 'assets/uploads/';
		$this->thumbs_path = 'assets/uploads/thumbs/';
		$this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '102400';
		$this->kh_rate = $this->site->getCurrencyByCode("KHR")->rate;
    }
	
	function index($biller_id = false) 
	{
		$this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('taxs'), 'page' => lang('taxs')));
		$meta = array('page_title' => lang('taxs'), 'bc' => $bc);
        $this->page_construct('taxs/index', $meta, $this->data);
	}
	
	public function getTaxs($biller_id = NULL)
    {
        $this->bpas->checkPermissions('index');
		$edit_link = anchor('admin/taxs/edit_tax/$1', '<i class="fa fa-edit"></i> ' . lang('edit_tax'), ' class="edit_tax" ');
		$delete_link = "<a href='#' class='po delete_tax' title='<b>" . $this->lang->line("delete_tax") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('taxs/delete_tax/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_tax') . "</a>";
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
			->select("
						taxs.id as id, 
						{$this->db->dbprefix('taxs')}.date as date, 
						companies.company as biller,
						taxs.type,
						taxs.from_date,
						taxs.to_date,
						IFNULL(".$this->db->dbprefix('taxs').".total,0) as total,
						IFNULL(".$this->db->dbprefix('taxs').".vat,0) as vat,
						IFNULL(".$this->db->dbprefix('taxs').".grand_total,0) as grand_total
					")
			->join("companies","companies.id = taxs.biller_id","left")
			->from("taxs");
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('taxs.created_by', $this->session->userdata('user_id'));
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('taxs.biller_id', $this->session->userdata('biller_id'));
		}
		if ($biller_id) {
			$this->datatables->where('taxs.biller_id', $biller_id);
		}
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function add_tax()
	{
		$this->bpas->checkPermissions("add_tax");
		if($this->input->post('add_tax')){
			$this->form_validation->set_rules('type', $this->lang->line("type"), 'required');
			if ($this->form_validation->run() == true) {

				$date 			= $this->bpas->fld(trim($this->input->post('date')));
				$biller_id 		= $this->input->post('biller');
				$type 			= $this->input->post('type');
				$from_date 		= $this->bpas->fsd(trim($this->input->post('from_date')));
				$to_date 		= $this->bpas->fsd(trim($this->input->post('to_date')));
				$t_total 		= 0;
				$t_vat 			= 0;
				$t_grand_total 	= 0;
				$items			= false;
				$data 			= false;

				$i = isset($_POST['val']) ? sizeof($_POST['val']) : 0;
				if($type=="sale"){
					$data_transactions 	= $this->taxs_model->getIndexSales($_POST['val']);
					$prefix 			= $this->Settings->sales_prefix;
				}else if($type=="expense"){
					$data_transactions 	= $this->taxs_model->getIndexExpenses($_POST['val']);
					$prefix 			= $this->Settings->expense_prefix;
				}else{
					$data_transactions 	= $this->taxs_model->getIndexPurchases($_POST['val']);
					$prefix 			= $this->Settings->purchase_prefix;
				}
				
				$year = date('y',strtotime($this->bpas->fsd($this->input->post('from_date'))));
				$prefix_year=$prefix.$year.'/';
				$total_row = $this->taxs_model->getInvoicesTaxCount($type,$prefix_year);

				$n=1;
				for ($r = 0; $r < $i; $r++) {

					$transaction_id 		= $_POST['val'][$r];
					$exchange_rate 			= $_POST['exchange_rate'][$r];
					$tax_reference 			= $prefix_year.sprintf('%04s', ($total_row+$n));//$_POST['tax_reference'][$r];
					$data_transaction 		= $data_transactions[$transaction_id];

					if(isset($data_transaction)){
						$t_total += $data_transaction->total;
						$t_vat += $data_transaction->order_tax;
						$t_grand_total += $data_transaction->grand_total;
						$items[] = array(
							"transaction" 		=> $type,
							"transaction_id" 	=> $transaction_id,
							"exchange_rate" 	=> $exchange_rate,
							"name" 				=> $data_transaction->name,
							"reference_no" 		=> $data_transaction->reference_no,
							"tax_reference" 	=> $tax_reference,
							"date" 				=> $data_transaction->date,
							"company" 			=> $data_transaction->company,
							"vat_no" 			=> $data_transaction->vat_no,
							"total" 			=> $data_transaction->total,
							"order_tax" 		=> $data_transaction->order_tax,
							"grand_total" 		=> $data_transaction->grand_total,
							"note" 				=> $data_transaction->note,
							"quantity" 			=> $data_transaction->quantity,
							"phone" 			=> $data_transaction->phone
						);
					}
					$n++;
				}

				$data = array(
					'date' 			=> $date,
					'biller_id' 	=> $biller_id,
					'type' 			=> $type,
					'from_date' 	=> $from_date,
					'to_date' 		=> $to_date,
					'total' 		=> $t_total,
					'vat' 			=> $t_vat,
					'grand_total' 	=> $t_grand_total,
					'created_by' 	=> $this->session->userdata('user_id'),
					'created_at' 	=> date('Y-m-d H:i:s'),
				);
				if($data && $items && $this->taxs_model->addTax($data, $items)){
					$this->session->set_flashdata('message', $this->lang->line("tax_added"));
					admin_redirect("taxs");
				}else{
					$this->session->set_flashdata('error', $this->lang->line("data_required"));
					redirect($_SERVER['HTTP_REFERER']);
				}
			}
		}else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('taxs'), 'page' => lang('tax')), array('link' => '#', 'page' => lang('add_tax')));
			$meta = array('page_title' => lang('add_tax'), 'bc' => $bc);
            $this->page_construct('taxs/add_tax', $meta, $this->data);
		}
	}
	
	public function edit_tax($id = false){
		$this->bpas->checkPermissions("edit_tax");
		if($this->input->post('edit_tax')){
			$this->form_validation->set_rules('type', $this->lang->line("type"), 'required');
			if ($this->form_validation->run() == true) {

				$date 			= $this->bpas->fld(trim($this->input->post('date')));
				$biller_id 		= $this->input->post('biller');
				$type 			= $this->input->post('type');
				$from_date 		= $this->bpas->fsd(trim($this->input->post('from_date')));
				$to_date 		= $this->bpas->fsd(trim($this->input->post('to_date')));
				$t_total		= 0;
				$t_vat 			= 0;
				$t_grand_total 	= 0;
				$items 			= false;
				$data 			= false;

				$i = isset($_POST['val']) ? sizeof($_POST['val']) : 0;
				
				if($type=="sale"){
					$data_transactions 	= $this->taxs_model->getIndexSales($_POST['val']);
					$prefix 			= $this->Settings->sales_prefix;
				}else if($type=="expense"){
					$data_transactions 	= $this->taxs_model->getIndexExpenses($_POST['val']);
					$prefix 			= $this->Settings->expense_prefix;
				}else{
					$data_transactions 	= $this->taxs_model->getIndexPurchases($_POST['val']);
					$prefix 			= $this->Settings->purchase_prefix;
				}
				$this->bpas->print_arrays($data_transactions);

				for ($r = 0; $r < $i; $r++) {
					$transaction_id 	= $_POST['val'][$r];
					$exchange_rate 		= $_POST['exchange_rate'][$r];
					$tax_reference 		= $_POST['tax_reference'][$r];
					$data_transaction 	= $data_transactions[$transaction_id];

					if(isset($data_transaction)){
						$t_total 	   += $data_transaction->total;
						$t_vat 		   += $data_transaction->order_tax;
						$t_grand_total += $data_transaction->grand_total;
						$items[] = array(
							"tax_id" 		=> $id,
							"transaction" 	=> $type,
							"transaction_id" => $transaction_id,
							"exchange_rate" => $exchange_rate,
							"reference_no" 	=> $data_transaction->reference_no,
							"tax_reference" => $tax_reference,
							"date" 			=> $data_transaction->date,
							"name" 			=> $data_transaction->name,
							"company" 		=> $data_transaction->company,
							"vat_no" 		=> $data_transaction->vat_no,
							"total" 		=> $data_transaction->total,
							"order_tax"		=> $data_transaction->order_tax,
							"grand_total" 	=> $data_transaction->grand_total,
							"note" 			=> $data_transaction->note,
							"quantity" 		=> $data_transaction->quantity
						);
					}
				}
				$data = array(
					'date' 			=> $date,
					'biller_id' 	=> $biller_id,
					'type' 			=> $type,
					'from_date' 	=> $from_date,
					'to_date' 		=> $to_date,
					'total' 		=> $t_total,
					'vat' 			=> $t_vat,
					'grand_total' 	=> $t_grand_total,
					'updated_by' 	=> $this->session->userdata('user_id'),
					'updated_at' 	=> date('Y-m-d H:i:s'),
				);

				if($data && $items && $this->taxs_model->updateTax($id, $data, $items)){
					$this->session->set_flashdata('message', $this->lang->line("tax_edited"));
					admin_redirect("taxs");
				}else{
					$this->session->set_flashdata('error', $this->lang->line("data_required"));
					redirect($_SERVER['HTTP_REFERER']);
				}

			}
		}else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['tax'] = $this->taxs_model->getTaxByID($id);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('taxs'), 'page' => lang('tax')), array('link' => '#', 'page' => lang('edit_tax')));
			$meta = array('page_title' => lang('edit_tax'), 'bc' => $bc);
            $this->page_construct('taxs/edit_tax', $meta, $this->data);
		}
	}
	public function delete_tax($id = null)
    {
        $this->bpas->checkPermissions("delete_tax", true);
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		$transaction_id = $this->site->getTransactionsId($id);
		if ($this->taxs_model->deleteTax($id , $transaction_id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("tax_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('tax_deleted'));
			admin_redirect('taxs');
		}
    }
	public function tax_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_tax', true);
                    foreach ($_POST['val'] as $id) {
                        $this->taxs_model->deleteTax($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("tax_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('taxs'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('biller'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('type'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('from_date'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('to_date'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('total'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('vat'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('grand_total'));
					$row = 2;
                    foreach ($_POST['val'] as $id) {
                        $tax = $this->taxs_model->getTaxByID($id);
						$biller = $this->site->getCompanyByID($tax->biller_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($tax->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $biller->company);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, lang($tax->type));
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->hrsd($tax->from_date));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->hrsd($tax->to_date));
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($tax->total));
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($tax->vat));
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($tax->grand_total));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);;
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'taxs_' . date('Y_m_d_H_i_s');
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
	public function getTransactions() {
		$biller_id 		= $this->input->get("biller") ? $this->input->get("biller") : false;
		$type 			= $this->input->get("type") ? $this->input->get("type") : false;
		$from_date 		= $this->input->get("from_date") ? $this->bpas->fsd($this->input->get("from_date")) : date("Y-m-d");
		$to_date 		= $this->input->get("to_date") ? $this->bpas->fsd($this->input->get("to_date")) : date("Y-m-d");
		$tax_id 		= $this->input->get("tax_id") ? $this->input->get("tax_id") : 0;

       	$this->load->library('datatables');

		if($type=="sale"){
			$this->datatables->select("sales.id as id, 
											DATE_FORMAT(".$this->db->dbprefix('sales').".date, '%Y-%m-%d') as date, 
											sales.reference_no,
											sales.customer as name, 
											IFNULL(".$this->db->dbprefix('sales').".total,0) - IFNULL(".$this->db->dbprefix('sales').".order_discount,0) as total,
											IFNULL(".$this->db->dbprefix('sales').".order_tax,0) as order_tax,
											sales.grand_total,
											IFNULL(".$this->db->dbprefix('currency_calenders').".rate,".$this->kh_rate.") as exchange_rate,
											tax_items.tax_reference,
											IFNULL(".$this->db->dbprefix('tax_items').".tax_id,0) as tax_id
										")
									->join("currency_calenders","currency_calenders.date = date(".$this->db->dbprefix('sales').".date)","left")
									->join("tax_items","sales.id = tax_items.transaction_id AND tax_items.transaction='".$type."'","left")
									->from('sales');
			$this->datatables->where_in("IFNULL(".$this->db->dbprefix('tax_items').".tax_id,0)",array(0,$tax_id));						
			if ($biller_id) {
				$this->datatables->where('sales.biller_id', $biller_id);
			}
			if ($from_date) {
				$this->datatables->where("date(".$this->db->dbprefix('sales').".date) >= ", $from_date);
			}
			if ($to_date) {
				$this->datatables->where("date(".$this->db->dbprefix('sales').".date) <= ", $to_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('sales.biller_id =', $this->session->userdata('biller_id'));
			}
			$this->datatables->where('sales.sale_status !=', 'returned');
			$this->datatables->where('sales.sale_status !=', 'draft');
			$this->datatables->order_by('sales.id', 'ASC');

		}else if($type=="expense"){
			$this->datatables->select("expenses.id as id, 
											DATE_FORMAT(".$this->db->dbprefix('expenses').".date, '%Y-%m-%d') as date, 
											expenses.reference as reference_no,
											expenses.supplier as name, 
											IFNULL(".$this->db->dbprefix('expenses').".amount,0) - IFNULL(".$this->db->dbprefix('expenses').".order_discount,0) as total,
											IFNULL(".$this->db->dbprefix('expenses').".order_tax,0) as order_tax,
											expenses.grand_total,
											IFNULL(".$this->db->dbprefix('currency_calenders').".rate,".$this->kh_rate.") as exchange_rate,
											tax_items.tax_reference,
											IFNULL(".$this->db->dbprefix('tax_items').".tax_id,0) as tax_id
										")
									->join("currency_calenders","currency_calenders.date = date(".$this->db->dbprefix('expenses').".date)","left")
									->join("tax_items","expenses.id = tax_items.transaction_id AND tax_items.transaction='".$type."'","left")
									->from('expenses');
									
			$this->datatables->where_in("IFNULL(".$this->db->dbprefix('tax_items').".tax_id,0)",array(0,$tax_id));				
			if ($biller_id) {
				$this->datatables->where('expenses.biller_id', $biller_id);
			}
			if ($from_date) {
				$this->datatables->where("date(".$this->db->dbprefix('expenses').".date) >= ", $from_date);
			}
			if ($to_date) {
				$this->datatables->where("date(".$this->db->dbprefix('expenses').".date) <= ", $to_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('expenses.biller_id =', $this->session->userdata('biller_id'));
			}
			$this->datatables->where('expenses.status', 'approved');
			$this->datatables->order_by('expenses.id', 'ASC');
		}else{
			$this->datatables->select("purchases.id as id, 
											DATE_FORMAT(".$this->db->dbprefix('purchases').".date, '%Y-%m-%d') as date, 
											purchases.reference_no,
											purchases.supplier as name, 
											IFNULL(".$this->db->dbprefix('purchases').".total,0) - IFNULL(".$this->db->dbprefix('purchases').".order_discount,0) as total,
											IFNULL(".$this->db->dbprefix('purchases').".order_tax,0) as order_tax,
											purchases.grand_total,
											IFNULL(".$this->db->dbprefix('currency_calenders').".rate,".$this->kh_rate.") as exchange_rate,
											tax_items.tax_reference,
											IFNULL(".$this->db->dbprefix('tax_items').".tax_id,0) as tax_id
										")
									->join("currency_calenders","currency_calenders.date = date(".$this->db->dbprefix('purchases').".date)","left")
									->join("tax_items","purchases.id = tax_items.transaction_id AND tax_items.transaction='".$type."'","left")
									->from('purchases');
			$this->datatables->where_in("IFNULL(".$this->db->dbprefix('tax_items').".tax_id,0)",array(0,$tax_id));
			if ($biller_id) {
				$this->datatables->where('purchases.biller_id', $biller_id);
			}
			if ($from_date) {
				$this->datatables->where("date(".$this->db->dbprefix('purchases').".date) >= ", $from_date);
			}
			if ($to_date) {
				$this->datatables->where("date(".$this->db->dbprefix('purchases').".date) <= ", $to_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('purchases.biller_id =', $this->session->userdata('biller_id'));
			}
			$this->datatables->where('purchases.status !=', 'returned');
			$this->datatables->where('purchases.status !=', 'draft');
			$this->datatables->order_by('purchases.id', 'ASC');
		}
		
        echo $this->datatables->generate();
    }
	public function modal_view($id = false){
		$this->bpas->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $tax = $this->taxs_model->getTaxByID($id);
        $this->data['biller'] = $this->site->getCompanyByID($tax->biller_id);
        $this->data['tax'] = $tax;
		$this->data['tax_items'] = $this->taxs_model->getTaxItems($id);
		$this->data['created_by'] = $this->site->getUserByID($tax->created_by);
        $this->load->view($this->theme . 'taxs/modal_view', $this->data);
	}
	public function purchases(){
		$this->bpas->checkPermissions('purchases_report', true,'taxs');
		$this->data['billers'] = $this->site->getBillers();
 		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('taxs'), 'page' => lang('tax')), array('link' => '#', 'page' => lang('purchases_report')));
        $meta = array('page_title' => lang('purchases_report'), 'bc' => $bc);
        $this->page_construct('taxs/purchases_report', $meta, $this->data);
	}
	
	public function sales(){
		$this->bpas->checkPermissions('sales_report', true,'taxs');
		$this->data['billers'] = $this->site->getBillers();
 		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('taxs'), 'page' => lang('tax')), array('link' => '#', 'page' => lang('sales_report')));
        $meta = array('page_title' => lang('sales_report'), 'bc' => $bc);
        $this->page_construct('taxs/sales_entry', $meta, $this->data);
	}
	public function getInvoicesTax_____() 
    {
		$type = $this->input->get('type');
		$icheck = $this->input->get('icheck');
        if ($total = $this->taxs_model->getInvoicesTaxCount($type)) {
            $this->bpas->send_json(sprintf('%04s', $total->total + $icheck)); 
        }
        $this->bpas->send_json(false); 
    }
}
