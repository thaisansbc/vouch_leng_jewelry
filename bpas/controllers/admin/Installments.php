<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Installments extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
		if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->admin_load('installments', $this->Settings->user_language);
        $this->load->library('form_validation');
		$this->load->admin_model('installments_model');
		$this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '10240';
        $this->data['logo'] = true;
    }
	
	public function index($warehouse_id = NULL, $biller_id = NULL)
	{
		$this->bpas->checkPermissions("index");
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
		if($biller_id == 0){
			$biller_id = null;
		} 
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('installments')));
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('installments'), 'page' => lang('installment')), array('link' => '#', 'page' => lang('installments')));
		$meta = array('page_title' => lang('installments'), 'bc' => $bc);
        $this->page_construct('installments/index', $meta, $this->data);
	}
	
	public function getInstallments($warehouse_id = NULL, $biller_id = NULL)
    {	
		$this->bpas->checkPermissions("index");
		if($warehouse_id == 0){
			$warehouse_id = null;
		}
		if($this->Owner || $this->Admin || $this->GP['installments-index']){
			$payment_link = anchor('admin/installments/view/$1', '<i class="fa fa-file-text-o"></i> ' .lang('installment_details'),'');
		}
		if($this->Owner || $this->Admin ||$this->GP['installments-edit']){
			$edit_link = anchor('admin/installments/edit/$1', '<i class="fa fa-edit"></i> ' .lang('edit_installment'),' class="edit_installment" ');
			$assign_link = anchor('admin/installments/view/$1#assignations', '<i class="fa fa-edit"></i> ' .lang('assign_installment'),' class="assign_installment" ');
		}
		if($this->Owner || $this->Admin || $this->GP['installments-inactive']){
			$inactive_link = "<a href='#' class='po inactive-installment' title='<b>" . lang("inactive_installment") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('installments/inactive/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa fa-times\"></i> "
			. lang('inactive_installment') . "</a>";
			$active_link = "<a href='#' class='po active-installment' title='<b>" . lang("active_installment") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('installments/active/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa fa-check\"></i> "
			. lang('active_installment') . "</a>"; 
		}
		
		$payoff_link = '';
		if($this->Owner || $this->Admin || $this->GP['installments-payoff']){
			$payoff_link = "<a href='#' class='po payoff-installment' title='<b>" . lang("payoff_installment") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('installments/payoff/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-edit\"></i> "
			. lang('payoff_installment') . "</a>";
		}
		
		if($this->Owner || $this->Admin || $this->GP['installments-delete']){
			$delete_link = "<a href='#' class='po delete-installment' title='<b>" . lang("delete_installment") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('installments/delete/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_installment') . "</a>";
		}
		
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$payment_link.'</li>
						<li>'.$edit_link.'</li>
						<li>'.$assign_link.'</li>
						<li>'.$payoff_link . '</li>
						<li>'.$inactive_link . '</li>
						<li>'.$delete_link . '</li>
					</ul>
				</div></div>';
				
        $this->load->library('datatables');
        $this->datatables->select("
				installments.id as id,
				installments.created_date,
				sales.reference_no as sale_ref,
				installments.reference_no as reference_no,
				installments.customer,
				companies.phone,
				installments.installment_amount,
				installments.deposit,
				installment_items.principal,
				installment_items.interest,
				installment_items.payment,
				installments.payment_date,
				installments.status")
            ->from('installments')
			->join('sales','sales.id=installments.sale_id','left')
			->join('warehouses','warehouses.id=installments.warehouse_id','left')
			->join('companies','companies.id=installments.customer_id','left')
			->join('(SELECT 
							installment_id,
							IFNULL(sum(payment),0) AS payment,
							IFNULL(sum(interest),0) AS interest,
							IFNULL(sum(principal),0) AS principal
						FROM
							'.$this->db->dbprefix('installment_items').'
						GROUP BY installment_id) as installment_items', 'installment_items.installment_id=installments.id', 'left')
			->group_by('installments.id');
			
        $this->datatables->add_column("Actions", $action, "id");
		
		if ($biller_id) {
			$this->datatables->where('sales.biller_id', $biller_id);
        }
		if ($warehouse_id) {
			$this->datatables->where('installments.warehouse_id', $warehouse_id);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('installments.biller_id =', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('installments.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('installments.created_by', $this->session->userdata('user_id'));
        }
        echo $this->datatables->generate();
    }
	
	public function installment_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
			 if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {	
					$this->bpas->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->installments_model->deleteInstallmentByID($id);
                    }
                    $this->session->set_flashdata('message', lang("installments_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

				} elseif ($this->input->post('form_action') == 'export_excel') {				
					$this->db->where_in('installments.id',$_POST['val']);
					$this->db->select("
							installments.id as id,
							installments.created_date,
							sales.reference_no as sale_ref,
							installments.reference_no as reference_no,
							installments.customer,
							companies.phone,
							installments.description,
							installments.installment_amount,
							installments.deposit,
							bpas_installment_items.principal,
							bpas_installment_items.interest,
							bpas_installment_items.payment,
							installments.payment_date,
							installments.status")
						->from('installments')
						->join('sales','sales.id=installments.sale_id','left')
						->join('sale_items','sales.id=sale_items.sale_id','left')
						->join('companies','companies.id=installments.customer_id','left')
						->join('(SELECT 
										installment_id,
										IFNULL(sum(payment),0) AS payment,
										IFNULL(sum(interest),0) AS interest,
										IFNULL(sum(principal),0) AS principal
									FROM
										'.$this->db->dbprefix('installment_items').'
									GROUP BY installment_id) as bpas_installment_items', 'bpas_installment_items.installment_id=installments.id', 'left')
						->join('(SELECT 
										installment_id,
										IFNULL(sum(amount),0) AS paid,
										IFNULL(sum(discount),0) AS discount
									FROM
										'.$this->db->dbprefix('payments').'
									GROUP BY installment_id) as bpas_payments', 'bpas_payments.installment_id=installments.id', 'left')
						->group_by('installments.id');
						
					$q = $this->db->get();
					
					if (!empty($q)) {
						if ($this->input->post('form_action') == 'export_excel') {
							$this->load->library('excel');
							$this->excel->setActiveSheetIndex(0);
							$this->excel->getActiveSheet()->setTitle(lang('installments'));
							$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
							$this->excel->getActiveSheet()->SetCellValue('B1', lang('sale_ref'));
							$this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no'));
							$this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
							$this->excel->getActiveSheet()->SetCellValue('E1', lang('phone'));
							$this->excel->getActiveSheet()->SetCellValue('F1', lang('product_name'));
							$this->excel->getActiveSheet()->SetCellValue('G1', lang('amount'));
							$this->excel->getActiveSheet()->SetCellValue('H1', lang('deposit'));
							$this->excel->getActiveSheet()->SetCellValue('I1', lang('principal'));
							$this->excel->getActiveSheet()->SetCellValue('J1', lang('interest'));
							$this->excel->getActiveSheet()->SetCellValue('K1', lang('payment'));
							$this->excel->getActiveSheet()->SetCellValue('L1', lang('first_payment_date'));
							$this->excel->getActiveSheet()->SetCellValue('M1', lang('status'));
							$row = 2;
							foreach ($q->result() as $installment) {
								$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrsd($installment->created_date));
								$this->excel->getActiveSheet()->SetCellValue('B' . $row, $installment->sale_ref);
								$this->excel->getActiveSheet()->SetCellValue('C' . $row, $installment->reference_no);
								$this->excel->getActiveSheet()->SetCellValue('D' . $row, $installment->customer);
								$this->excel->getActiveSheet()->SetCellValue('E' . $row, $installment->phone);
								$this->excel->getActiveSheet()->SetCellValue('F' . $row, $installment->description);
								$this->excel->getActiveSheet()->SetCellValue('G' . $row, $installment->installment_amount);
								$this->excel->getActiveSheet()->SetCellValue('H' . $row, $installment->deposit);
								$this->excel->getActiveSheet()->SetCellValue('I' . $row, $installment->principal);
								$this->excel->getActiveSheet()->SetCellValue('J' . $row, $installment->interest);
								$this->excel->getActiveSheet()->SetCellValue('K' . $row, $installment->payment);
								$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->hrsd($installment->payment_date));
								$this->excel->getActiveSheet()->SetCellValue('M' . $row, $installment->status);
								$row++;
							}
							$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
							$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
							$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
							$filename = 'installments_' . date('Y_m_d_H_i_s');
							$this->load->helper('excel');
							create_excel($this->excel, $filename);
						}
					}
				}
			} else {
                $this->session->set_flashdata('error', lang("no_installment_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function add($id = NULL, $down_payment_id=null)
    {
		$this->bpas->checkPermissions("add");
		$installment = $this->installments_model->getInstallmentBySaleID($id);
		$this->form_validation->set_rules('installment_amount', lang("installment_amount"), 'required');
        if ($this->form_validation->run() == true) {
			$sale               = $this->installments_model->getSaleByID($id);
			$biller_id          = $id ? $sale->biller_id : $this->input->post('biller');
			$reference_no       = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('inst',$biller_id);
			$installment_amount = $this->input->post('installment_amount');
			$deposit 			= $this->input->post('deposit');
			$pricipal_term 		= $this->input->post('pricipal_term');
			$principal_amount 	= $this->input->post('principal_amount');
			$interest_rate 		= $this->input->post('interest_rate');
			$term 				= $this->input->post('term');
			$frequency 			= $this->input->post('frequency');
			$frequency_info 	= $this->installments_model->getFrequencyByID($frequency);
			$payment_date 		= $this->input->post('payment_date');
			$interest_period 	= $this->input->post('interest_period');
			$interest_method 	= $this->input->post('interest_method');
			$penalty_id         = ($this->Settings->installment_penalty_option == 2 ? $this->input->post('penalty') : null);
			$product_id			= $this->input->post('product') ? $this->input->post('product') : null;
			$description 		= "";
			if (!empty($sale)) {
				if($sale_items 		= $this->installments_model->getSaleItemBySaleID($sale->id)) {
					foreach($sale_items as $sale_item){
						$description .= $sale_item->product_name." ,";
					}
				}
				if ($sale->sale_status=='draft') { 
					$description = $sale->note;
				}
			}
			if ($this->Owner || $this->Admin  || $this->bpas->GP['installments-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$data = array(
						"reference_no" 		=> $reference_no,
						"biller_id" 		=> $biller_id,
						"biller" 			=> $sale->biller,
						"sale_id" 			=> $sale->id,
						"project_id" 		=> $sale->project_id,
						"warehouse_id" 		=> $sale->warehouse_id,
						"customer_id" 		=> $sale->customer_id,
						"customer" 			=> $sale->customer,
						"price" 			=> $sale->grand_total,
						"installment_amount"=> $installment_amount,
						"description" 		=> rtrim($description,","),
						"deposit" 			=> $deposit,
						"principal_amount" 	=> $principal_amount,
						"interest_rate" 	=> $interest_rate,
						"term" 				=> $term,
						"frequency" 		=> $frequency_info->day,
						"frequency_id" 		=> $frequency_info->id,
						"payment_date" 		=> $this->bpas->fld($payment_date),
						"interest_period" 	=> $interest_period,
						"interest_method" 	=> $interest_method,
						"created_by" 		=> $this->session->userdata("user_id"),
						"created_date" 		=> $date,
						"product_id"		=> $product_id,
						"sale_item_id"		=> $sale_item_id,
						"penalty_id"		=> $penalty_id,
					);
			$installment_items = array();
			for($m = 0; $m < count($_POST['tperiod']); $m++){
				$installment_items[] = array(
						'period' 	=> $_POST['tperiod'][$m],
						'interest' 	=> $_POST['trate'][$m],
						'principal' => $_POST['tprincipal'][$m],
						'payment' 	=> $_POST['tpayment'][$m],
						'balance' 	=> $_POST['tbalance'][$m],
						'note' 		=> $_POST['note'][$m],
						'deadline' 	=> $this->bpas->fld($_POST['tdeadline'][$m]),
						'status' 	=> 'pending',
					);
			}
			if($this->Settings->accounting == 1 && $sale->module_type !='rental'){
				//$installmentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
				$accTrans[] = array(
					'tran_type' => 'Installment',
					'tran_date' => $date,
					'reference_no' => $reference_no,
					'account_code' => $this->accounting_setting->default_receivable,
					'amount' => -($sale->grand_total),
					'narrative' => 'Installment '.$this->site->getAccountName($this->accounting_setting->default_receivable),
					'description' => $description,
					'biller_id' => $biller_id,
					'project_id' => $sale->project_id,
					'created_by' => $this->session->userdata('user_id'),
					'customer_id' => $sale->customer_id,
				);
				$accTrans[] = array(
					'tran_type' => 'Installment',
					'tran_date' => $date,
					'reference_no' => $reference_no,
					'account_code' => $this->accounting_setting->installment_outstanding_acc,
					'amount' => $sale->grand_total,
					'narrative' => 'Installment '.$this->site->getAccountName($this->accounting_setting->installment_outstanding_acc),
					'description' => '',
					'biller_id' => $biller_id,
					'project_id' => $sale->project_id,
					'created_by' => $this->session->userdata('user_id'),
					'customer_id' => $sale->customer_id,
				);
			}
		}
		if ($this->form_validation->run() == true && $this->installments_model->addInstallments($installment_items, $data, $accTrans)) {
			if ($down_payment_id) {
				 $this->db->update('down_payments', ['status' => 1], ['id' => $down_payment_id]);
			}
			$this->session->set_flashdata('message', lang("installment_added"). ' ' .$reference_no);
			admin_redirect("installments");
		} else {
			$sale = $this->site->getSaleBalanceByID($id);
			if($sale && $sale->payment_status == 'paid'){
				$this->session->set_flashdata('error', lang("installment_cannot_create_with_sale_already_paid"));
				admin_redirect("sales");
			}
			$this->data['error'] 	       = (validation_errors()) ? validation_errors() :$this->session->flashdata('error');
			$this->data['id'] 		       = $id;
			$this->data['sale'] 	       = $sale;
			$this->data['sales'] 	       = $this->installments_model->getRefSales();
			$this->data['sale_items']      = $this->installments_model->getSaleItemBySaleID($id);	
			$this->data['calendar']        = $this->installments_model->getHoliday();
			$this->data['interest_method'] = $this->site->getAllInterestmethod();
			$this->data['interest_period'] = $this->site->getAllInterestperiod();
			$this->data['frequencies']     = $this->installments_model->getAllFrequencies();
			$this->data['penalty']         = $this->installments_model->getPenalty();
			if ($down_payment_id) {
				$down_payment = $this->site->getDownPaymentByID($down_payment_id);
				$this->data['installment_amount'] = (isset($down_payment->amount)?$this->bpas->formatDecimal($down_payment->amount):0);
				$this->data['deposit'] = 0;
				$this->data['down_payment_id'] = $down_payment_id;
			} else {
				$this->data['installment_amount'] = (isset($sale->balance)?$this->bpas->formatDecimal($sale->balance+$sale->paid):0);
				$this->data['deposit'] = (isset($sale->paid) ? $this->bpas->formatDecimal($sale->paid):0);
				$this->data['down_payment_id'] = '';
			}
			$this->data['billers']    = $this->site->getAllCompanies('biller');
			$bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('installments'), 'page' => lang('installments')), array('link' => '#', 'page' => lang('add_installment')));
			$meta = array('page_title' => lang('installments'), 'bc' => $bc);
			$this->page_construct('installments/add', $meta, $this->data);
		}
    }
	
	public function edit($id = false)
	{
		$this->bpas->checkPermissions("edit");
		$installment = $this->installments_model->getInstallmentByID($id);
		$sale = $this->installments_model->getSaleByID($installment->sale_id);
		if ($installment->status == 'inactive') {
			$this->session->set_flashdata('error', lang("installment_has_inactive"));
			$this->bpas->md();
		} else if ($installment->status == 'returned') {
			$this->session->set_flashdata('error', lang("installment_has_returned"));
			$this->bpas->md();
		} else if ($installment->status == 'completed') {
			$this->session->set_flashdata('error', lang("installment_has_completed"));
			$this->bpas->md();
		} else if ($installment->status == 'voiced') {
			$this->session->set_flashdata('error', lang("installment_has_voiced"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('installment_amount', lang("installment_amount"), 'required');
        if ($this->form_validation->run() == true) {
			$reference_no 		= $this->input->post('reference_no');
			$installment_amount = $this->input->post('installment_amount');
			$deposit 		    = $this->input->post('deposit');
			$pricipal_term      = $this->input->post('pricipal_term');
			$principal_amount 	= $this->input->post('principal_amount');
			$interest_rate 		= $this->input->post('interest_rate');
			$term 				= $this->input->post('term');
			$frequency 			= $this->input->post('frequency');
			$frequency_info 	= $this->installments_model->getFrequencyByID($frequency);
			$payment_date 		= $this->input->post('payment_date');
			$interest_period 	= $this->input->post('interest_period');
			$interest_method 	= $this->input->post('interest_method');
			$penalty_id         = ($this->Settings->installment_penalty_option == 2 ? $this->input->post('penalty') : null);
			$description = "";
			if (!empty($sale)) {
				if($sale_items = $this->installments_model->getSaleItemBySaleID($sale->id)) {
					foreach($sale_items as $sale_item) {
						$description .= $sale_item->product_name." ,";
					}
				}
				if ($sale->sale_status=='draft') { 
					$description = $sale->note;
				}
			}
			if ($this->Owner || $this->Admin  || $this->bpas->GP['installments-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$data = array(
						"reference_no" 		=> $reference_no,
						"biller_id" 		=> $installment->biller_id,
						"biller" 			=> $installment->biller,
						"sale_id" 			=> !empty($sale) ? $sale->id : null,
						"project_id" 		=> !empty($sale) ? $sale->project_id : null,
						"warehouse_id" 		=> !empty($sale) ? $sale->warehouse_id : null,
						"customer_id" 		=> !empty($sale) ? $sale->customer_id : null,
						"customer" 			=> !empty($sale) ? $sale->customer : null,
						"price" 			=> !empty($sale) ? $sale->grand_total : null,
						"installment_amount"=> $installment_amount,
						"description" 		=> rtrim($description,","),
						"deposit" 			=> $deposit,
						"principal_amount" 	=> $principal_amount,
						"interest_rate" 	=> $interest_rate,
						"term" 				=> $term,
						"frequency" 		=> $frequency_info->day,
						"frequency_id" 		=> $frequency_info->id,
						"payment_date" 		=> $this->bpas->fld($payment_date),
						"interest_period" 	=> $interest_period,
						"interest_method" 	=> $interest_method,
						"created_date" 		=> $date,
						"updated_by" 		=> $this->session->userdata("user_id"),
						"updated_at" 		=> date("Y-m-d H:i"),
						"penalty_id"		=> $penalty_id,
					);
					
			for($m = 0; $m < count($_POST['tperiod']); $m++){
				$installment_items[] = array(
						'period' 	=> $_POST['tperiod'][$m],
						'interest' 	=> $_POST['trate'][$m],
						'principal' => $_POST['tprincipal'][$m],
						'payment' 	=> $_POST['tpayment'][$m],
						'balance' 	=> $_POST['tbalance'][$m],
						'note' 		=> $_POST['note'][$m],
						'deadline' 	=> $this->bpas->fld($_POST['tdeadline'][$m]),
						'status' 	=> 'pending',
					);
			}
			if($this->Settings->accounting == 1  && $sale->module_type != 'rental') {
				$installmentAcc = $this->site->getAccountSettingByBiller($installment->biller_id);
				$accTrans[] = array(
					'tran_type' 	=> 'Installment',
					'tran_date' 	=> $date,
					'reference_no' 	=> $reference_no,
					'account_code' 	=> $this->accounting_setting->default_receivable,
					'amount' 		=> -($sale->grand_total),
					'narrative' 	=> 'Installment '.$this->site->getAccountName($this->accounting_setting->default_receivable),
					'description' 	=> $description,
					'biller_id' 	=> $sale->biller_id,
					'project_id' 	=> $sale->project_id,
					'created_by' 	=> $this->session->userdata('user_id'),
					'customer_id' 	=> $sale->customer_id,
				);
				$accTrans[] = array(
					'tran_type' 	=> 'Installment',
					'tran_date' 	=> $date,
					'reference_no' 	=> $reference_no,
					'account_code' 	=> $this->accounting_setting->installment_outstanding_acc,
					'amount' 		=> $sale->grand_total,
					'narrative' 	=> 'Installment ' . $this->site->getAccountName($this->accounting_setting->installment_outstanding_acc),
					'description' 	=> '',
					'biller_id' 	=> $sale->biller_id,
					'project_id' 	=> $sale->project_id,
					'created_by' 	=> $this->session->userdata('user_id'),
					'customer_id' 	=> $sale->customer_id,
				);
			}
		}
		if ($this->form_validation->run() == true && $this->installments_model->updateInstallments($id, $installment_items, $data, $accTrans)) {
			$this->session->set_flashdata('message', lang("installment_updated") .' '.$reference_no);
			admin_redirect("installments");
		} else {
			$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
			$payment = $this->installments_model->getPaymentByInstallmentID($id);
			if (!empty($sale) && $sale->payment_status == 'paid') {
				$this->session->set_flashdata('error', lang("installment_cannot_create_with_sale_already_paid"));
				admin_redirect("sales");
			} else if ($installment->status == 'payoff') {
				$this->session->set_flashdata('error', lang("installment_already_payoff"));
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($installment->status == 'inactive') {
				$this->session->set_flashdata('error', lang("installment_already_inactive"));
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($installment->status == 'completed') {
				$this->session->set_flashdata('error', lang("installment_already_completed"));
				redirect($_SERVER['HTTP_REFERER']);
			}
			$this->data['id'] 		   = $id;
			$this->data['installment'] = $installment;
			$this->data['installment_items'] = $this->installments_model->getAllInstallmentItemsByID($id);
			$this->data['calendar']    = $this->installments_model->getHoliday();
			$this->data['frequencies'] = $this->installments_model->getAllFrequencies();
			$this->data['billers']     = $this->site->getAllCompanies('biller');
			$this->data['penalty']     = $this->installments_model->getPenalty();
			$bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('installments'), 'page' => lang('installments')), array('link' => '#', 'page' => lang('edit_installment')));
			$meta = array('page_title' => lang('installments'), 'bc' => $bc);
			$this->page_construct('installments/edit', $meta, $this->data);
		}
	}
	
	public function delete($id = NULL)
	{
		$this->bpas->checkPermissions('delete',true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->installments_model->deleteInstallmentByID($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('installment_deleted')]);
            }
            admin_redirect('installments');
        }
	}
	
	public function inactive($id = NULL)
	{
		$this->bpas->checkPermissions("inactive",true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$installment = $this->installments_model->getInstallmentByID($id);
		if ($installment->status == 'inactive') {
			$this->session->set_flashdata('error', lang("installment_has_inactive"));
			$this->bpas->md();
		}else if ($installment->status == 'completed') {
			$this->session->set_flashdata('error', lang("installment_has_completed"));
			$this->bpas->md();
		}else if ($installment->status == 'voiced') {
			$this->session->set_flashdata('error', lang("installment_has_voiced"));
			$this->bpas->md();
		}
        if ($this->installments_model->updateStatusInstallmentByID($id)) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('installment_inactived')]);
            }
            $this->session->set_flashdata('message', lang('installment_inactived'));
            admin_redirect('pos');
        }
	}
	
	public function active($id = NULL)
	{
		$this->bpas->checkPermissions("inactive",true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$installment = $this->installments_model->getInstallmentByID($id);
        if ($this->installments_model->updateStatusInstallmentByID($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("installment_actived")." ".$installment->reference_no;
				die();
            }
            $this->session->set_flashdata('message', lang('installment_actived')." ".$installment->reference_no);
            admin_redirect('pos');
        }
	}
		
	public function payment_schedule($id = false)
	{
		$this->bpas->checkPermissions("payments");
		$this->data['id'] = $id;
		$this->data['installment'] = $this->installments_model->getInstallmentByID($id);
		$this->data['installment_items'] = $this->installments_model->getAllInstallmentItemsByID($id);
		$sale = $this->installments_model->getSaleByID($this->data['installment']->sale_id);
		$this->data['sale'] = $sale;
		$this->data['biller'] = $this->site->getCompanyByID($this->data['installment']->biller_id);
		$this->data['sale_items'] = $this->installments_model->getSaleItemBySaleID($this->data['installment']->sale_id);
		$this->data['warehouse'] = $this->site->getWarehouseByID($this->data['installment']->warehouse_id);
		$customer = $this->site->getCompanyByID($this->data['installment']->customer_id);
		$this->data['customer'] = $customer;
		if($sale && $sale->type == "school"){
			$student = $this->installments_model->getStudentByID($customer->student_id);
			$this->data['student'] = $student;
			$this->data['grade'] = $this->installments_model->getGradeByID($sale->grade_id);
			$this->load->view($this->theme . 'installments/payment_schedule_school', $this->data);
		}else{
			$this->load->view($this->theme . 'installments/payment_schedule', $this->data);
		}
		
	}
	
	public function view($id = NULL)
    {
		$this->bpas->checkPermissions('index');
		$this->data['id'] = $id;
		$this->data['installment'] = $this->installments_model->getInstallmentByID($id);
		$this->data['sale'] = $this->installments_model->getSaleByID($this->data['installment']->sale_id);
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('installments'), 'page' => lang('installments')), array('link' => '#', 'page' => lang('installment_details')));
        $meta = array('page_title' => lang('installment_details'), 'bc' => $bc);
        $this->page_construct('installments/view', $meta, $this->data);
    }
	
	public function getRepayments($id = NULL)
    {		
		$this->bpas->checkPermissions('payments');
		if (!$id) {
			$id = $this->input->get("id");
		}
		$installment_late_days = ($this->Settings->installment_late_days ? ($this->Settings->installment_late_days -1) : 0);
		$add_payment_link = anchor('admin/installments/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), ' class="add-payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$payments_link = anchor('admin/installments/view_payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), ' class="view-payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'. $add_payment_link . '</li>
						<li>'. $payments_link . '</li>
					</ul>
				</div></div>';
        $this->load->library('datatables');
        $this->datatables
            ->select("
				installment_items.id as id,
				installment_items.period,
				installment_items.deadline,
				installment_items.payment,
				installment_items.interest, 
				installment_items.principal,
				installment_items.balance,
				installment_items.paid,
				installment_items.interest_paid,
				installment_items.principal_paid,
				installment_items.penalty_paid,
				DATEDIFF(SYSDATE(), DATE_ADD({$this->db->dbprefix('installment_items')}.deadline, INTERVAL {$installment_late_days} DAY)) AS overdue,
				installment_items.status")
            ->from('installment_items')
			->join('installments', 'installment_items.installment_id=installments.id', 'left')
			->where("installment_items.installment_id",$id) 
			->group_by('installment_items.id');
			
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function repayment_actions()
	{
		 if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true)
		{	
			if(isset($_POST['val'])){
				$this->db->where_in('installment_items.id' , $_POST['val']);
			}
			$installment_late_days = ($this->Settings->installment_late_days ? ($this->Settings->installment_late_days -1) : 0);
			$this->db->select("
				installment_items.id as id,
				installment_items.period,
				installment_items.deadline, 
				installment_items.payment,
				installment_items.interest, 
				installment_items.principal,
				installment_items.paid,
				installment_items.interest_paid,
				installment_items.principal_paid,
				installment_items.penalty_paid,
				DATEDIFF(SYSDATE(), DATE_ADD({$this->db->dbprefix('installment_items')}.deadline, INTERVAL {$installment_late_days} DAY)) AS overdue,
				installment_items.status")
            ->from('installment_items')
			->join('installments', 'installment_items.installment_id=installments.id', 'left')
			->join('sales', 'sales.id=installments.sale_id', 'left')
			->group_by('installment_items.id');
			$q = $this->db->get();
			if(isset($_POST['val'])){
				if (!empty($q)) {
					if ($this->input->post('form_action') == 'export_excel') {
	                    $this->load->library('excel');
	                    $this->excel->setActiveSheetIndex(0);
	                    $this->excel->getActiveSheet()->setTitle(lang('installments'));
	                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('#'));
	                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('deadline'));
	                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('payment'));
	                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('interest'));
	                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('principal'));
						$this->excel->getActiveSheet()->SetCellValue('F1', lang('payment_paid'));
						$this->excel->getActiveSheet()->SetCellValue('G1', lang('interest_paid'));
						$this->excel->getActiveSheet()->SetCellValue('H1', lang('principal_paid'));
						$this->excel->getActiveSheet()->SetCellValue('I1', lang('penalty_paid'));
						$this->excel->getActiveSheet()->SetCellValue('J1', lang('overdue'));
						$this->excel->getActiveSheet()->SetCellValue('K1', lang('status'));
	                    $row = 2;
	                    foreach ($q->result() as $installment){
	                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $installment->period);
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrsd($installment->deadline));
	                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $installment->payment);
	                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $installment->interest);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $installment->principal);
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $installment->paid);
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $installment->interest_paid);
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $installment->principal_paid);
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, $installment->penalty_paid);
							$this->excel->getActiveSheet()->SetCellValue('J' . $row, $installment->overdue);
							$this->excel->getActiveSheet()->SetCellValue('K' . $row, $installment->status);
	                        $row++;
	                    }
	                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
	                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
						$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
	                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
	                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
	                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
	                    $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
	                    $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
	                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	                    $filename = 'repayment_' . date('Y_m_d_H_i_s');
	                    $this->load->helper('excel');
						create_excel($this->excel, $filename);
	                }
				}
            } else {
                $this->session->set_flashdata('error', lang("no_repayment_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function getTransactions($id = NULL)
    {
		if(!$id){
			$id = $this->input->get("id");
		}
        $this->load->library('datatables');
        $this->datatables
            ->select("
				installment_items.period,
				payments.date,
				payments.reference_no,
				companies.name as customer,
				(bpas_payments.amount + bpas_payments.interest_paid) as payment_paid,
				bpas_payments.interest_paid,
				bpas_payments.amount as principal_paid,
				bpas_payments.penalty_paid as penalty_paid,
				CONCAT(bpas_users.last_name,' ',bpas_users.first_name) as created_by,
				".$this->db->dbprefix('payments').".paid_by as paid_by,
				payments.type,
				payments.id")
            ->from('payments')
			->join('installment_items', 'installment_items.id=payments.installment_item_id', 'left')
			->join('users','users.id=payments.created_by','left')
			//->join('cash_accounts','cash_accounts.id = payments.paid_by','left')
			->join('companies','companies.id=payments.installment_customer_id','left')
			->where("payments.installment_id",$id);
			
        echo $this->datatables->generate();
    }
	
	public function transaction_actions($id = NULL, $pdf = NULL, $xls = NULL)
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($pdf || $xls) {
			$this->db->select("
				installment_items.period,
				payments.date,
				payments.reference_no,
				companies.name as customer,
				(bpas_payments.amount + bpas_payments.interest_paid) as payment_paid,
				bpas_payments.interest_paid,
				bpas_payments.amount as principal_paid,
				bpas_payments.penalty_paid as penalty_paid,
				CONCAT(bpas_users.last_name,' ',bpas_users.first_name) as created_by,
				payments.paid_by,
				payments.type")
            ->from('payments')
			->join('installment_items', 'installment_items.id=payments.installment_item_id', 'left')
			->join('users','users.id=payments.created_by','left')
			->join('companies','companies.id=payments.installment_customer_id','left')
			->where("payments.installment_id",$id);
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
				$this->excel->getActiveSheet()->setTitle(lang('transactions'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('#'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('payment_paid'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('interest_paid'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('principal_paid'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('penalty_paid'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('created_by'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('paid_by'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('type'));
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->period);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrsd($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->payment_paid);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->interest_paid);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->principal_paid);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->penalty_paid);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->created_by);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->paid_by);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->type);
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
				
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $filename = 'transactions_'.date("Y_m_d_H_i_s");
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

	public function missed_repayments()
	{
		$this->bpas->checkPermissions("payments");
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('installments'), 'page' => lang('installment')), array('link' => '#', 'page' => lang('missed_repayments')));
        $meta = array('page_title' => lang('installments'), 'bc' => $bc);
        $this->page_construct('installments/missed_repayments', $meta, $this->data);
	}
	
	public function getMissedRepayments()
    {		
		$this->bpas->checkPermissions('payments');
		$installment_late_days = ($this->Settings->installment_late_days ? ($this->Settings->installment_late_days -1) : 0);
		$add_payment_link = anchor('admin/installments/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), ' class="add-payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$payments_link = anchor('admin/installments/view_payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), ' class="view-payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$add_payment_link . '</li>
						<li>'.$payments_link . '</li>
					</ul>
				</div></div>';
        $this->load->library('datatables');
        $this->datatables
            ->select("
				installment_items.id as id,
				installment_items.period,
				installment_items.deadline,
				installments.reference_no,
				CONCAT(bpas_installments.customer,' [ <small style=color:#FF5454>',bpas_companies.phone,'</small> ] ') as customer,
				installment_items.payment,
				installment_items.interest, 
				installment_items.principal,
				installment_items.balance,
				installment_items.paid,
				installment_items.interest_paid,
				installment_items.principal_paid,
				installment_items.penalty_paid,
				DATEDIFF(SYSDATE(), DATE_ADD({$this->db->dbprefix('installment_items')}.deadline, INTERVAL {$installment_late_days} DAY)) AS overdue,
				installment_items.status")
            ->from('installment_items')
			->join('installments', 'installment_items.installment_id=installments.id', 'left')
			->join('companies','companies.id=installments.customer_id','left')
			->group_by('installment_items.id');
		$installment_alert_days = ($this->Settings->installment_alert_days?$this->Settings->installment_alert_days:0);
		$this->datatables->where('DATE_SUB('.$this->db->dbprefix('installment_items').'.`deadline`, INTERVAL '.$installment_alert_days.' DAY) <=', date("Y-m-d"));
		$this->datatables->where('installment_items.status !=','paid');
		$this->datatables->where('installment_items.status !=','payoff');
		$this->datatables->where('installments.status !=','payoff');
		$this->datatables->where('installments.status !=','completed');
		$this->datatables->where('installments.status !=','inactive');
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->datatables->where('installments.biller_id =', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->datatables->where_in('installments.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('installments.created_by', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function missed_repayment_actions()
	{
		 if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
			$installment_late_days = ($this->Settings->installment_late_days ? ($this->Settings->installment_late_days -1) : 0);
			if(isset($_POST['val'])){
				$this->db->where_in('installment_items.id' , $_POST['val']);
			}
			$this->db->select("
				installment_items.id as id,
				installment_items.period,
				installments.reference_no,
				installments.customer,
				installment_items.deadline, 
				installment_items.payment,
				installment_items.interest, 
				installment_items.principal,
				installment_items.balance,
				installment_items.paid,
				installment_items.interest_paid,
				installment_items.principal_paid,
				installment_items.penalty_paid,
				DATEDIFF(SYSDATE(), DATE_ADD({$this->db->dbprefix('installment_items')}.deadline, INTERVAL {$installment_late_days} DAY)) AS overdue,
				installment_items.status")
            ->from('installment_items')
			->join('installments', 'installment_items.installment_id=installments.id', 'left')
			->join('sales', 'sales.id=installments.sale_id', 'left')
			->group_by('installment_items.id');
			$q = $this->db->get();
			if (isset($_POST['val'])) {
				if (!empty($q)) {
					if ($this->input->post('form_action') == 'export_excel') {
	                    $this->load->library('excel');
	                    $this->excel->setActiveSheetIndex(0);
	                    $this->excel->getActiveSheet()->setTitle(lang('installments'));
	                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('#'));
	                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('deadline'));
						$this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no'));
	                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
	                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('payment'));
	                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('interest'));
	                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('principal'));
						$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
						$this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_paid'));
						$this->excel->getActiveSheet()->SetCellValue('J1', lang('interest_paid'));
						$this->excel->getActiveSheet()->SetCellValue('K1', lang('principal_paid'));
						$this->excel->getActiveSheet()->SetCellValue('L1', lang('penalty_paid'));
						$this->excel->getActiveSheet()->SetCellValue('M1', lang('overdue'));
						$this->excel->getActiveSheet()->SetCellValue('N1', lang('status'));
	                    $row = 2;
	                    foreach ($q->result() as $installment){
	                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $installment->period);
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrsd($installment->deadline));
	                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $installment->reference_no);
	                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $installment->customer);
	                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $installment->payment);
	                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $installment->interest);
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $installment->principal);
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $installment->balance);
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, $installment->paid);
							$this->excel->getActiveSheet()->SetCellValue('J' . $row, $installment->interest_paid);
							$this->excel->getActiveSheet()->SetCellValue('K' . $row, $installment->principal_paid);
							$this->excel->getActiveSheet()->SetCellValue('L' . $row, $installment->penalty_paid);
							$this->excel->getActiveSheet()->SetCellValue('M' . $row, $installment->overdue);
							$this->excel->getActiveSheet()->SetCellValue('N' . $row, $installment->status);
	                        $row++;
	                    }
	                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
	                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
						$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
	                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
	                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
	                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
	                    $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
	                    $this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
	                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	                    $filename = 'missed_repayment_' . date('Y_m_d_H_i_s');
	                    $this->load->helper('excel');
						create_excel($this->excel, $filename);
	                }
				}
            } else {
                $this->session->set_flashdata('error', lang("no_missed_repayment_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function add_payment($id = NULL)
    {
		$this->bpas->checkPermissions('payments', true);
		$installment_item = $this->installments_model->getInstallmentItemsByID($id);
		$installment 	  = $this->installments_model->getInstallmentByID($installment_item->installment_id);
		$sale 			  = $this->installments_model->getSaleByID($installment->sale_id);
		$product_id 	  = $installment->product_id?$installment->product_id:'';
		if ($installment->status == 'inactive') {
			$this->session->set_flashdata('error', lang("installment_has_inactive"));
			$this->bpas->md();
		}else if ($installment->status == 'returned') {
			$this->session->set_flashdata('error', lang("installment_has_returned"));
			$this->bpas->md();
		}else if ($installment_item->status == 'paid') {
			$this->session->set_flashdata('error', lang("installment_has_paid"));
			$this->bpas->md();
		}else if ($installment->status == 'completed') {
			$this->session->set_flashdata('error', lang("installment_has_completed"));
			$this->bpas->md();
		}else if ($installment->status == 'voiced') {
			$this->session->set_flashdata('error', lang("installment_has_voiced"));
			$this->bpas->md();
		}else if($this->installments_model->checkPreviousPyament($id,$installment_item->installment_id)){
			$this->session->set_flashdata('error', lang("please_add_payment_to_previnous_deadline"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $installment->customer_id;
                if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$installment->biller_id);
            if ($this->Owner || $this->Admin || $this->GP['installments-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$currencies = array();	
			$camounts = $this->input->post("c_amount");			
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$currencies[] = array(
								"amount" 	=> $camounts[$key],
								"currency" 	=> $currency[$key],
								"rate" 		=> $rate[$key],
							);
				}
			}
			$paymentAcc = $this->site->getAccountSettingByBiller($installment->biller_id);
			$bank_name      = "";
			$account_name   = "";
			$account_number = "";
			$cheque_number  = "";
			$cheque_date    = "";
			if ($this->input->post('paid_by')=='deposit' || $this->input->post('paid_by')=='gift_card') {
				$paying_to = $this->accounting_setting->default_sale_deposit;
			} else {
				$cash_account = $this->site->getCashAccountByCode($this->input->post('paid_by'));
				$paying_to = $cash_account->account_code;
				/*if($cash_account->type=="bank"){
					$bank_name = $cash_account->name;
					$account_name = $this->input->post('account_name');
					$account_number = $this->input->post('account_number');
				}else if($cash_account->type=="cheque"){
					$bank_name = $this->input->post('bank_name');
					$cheque_number = $this->input->post('cheque_number');
					$cheque_date = $this->bpas->fsd($this->input->post('cheque_date'));
				}*/
			}
            $payment = array(
                'date' 					=> $date,
				'sale_id' 				=> $installment->sale_id,
				'installment_id' 		=> $installment->id,
				'installment_item_id' 	=> $installment_item->id,
				'installment_customer_id' => $installment->customer_id,
                'reference_no' 			=> $reference_no,
                'amount' 				=> $this->input->post('principal-paid'),
				'interest_paid' 		=> $this->input->post('interest-paid'),
				'penalty_paid' 			=> $this->input->post('penalty-paid'),
				'discount' 				=> $this->input->post('discount'),
                'paid_by' 				=> $this->input->post('paid_by'),
                'cheque_no'    			=> $this->input->post('cheque_no'),
				'cc_no'        			=> $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
				'cc_holder'    			=> $this->input->post('pcc_holder'),
				'cc_month'     			=> $this->input->post('pcc_month'),
				'cc_year'      			=> $this->input->post('pcc_year'),
				'cc_type'      			=> $this->input->post('pcc_type'),
                'note' 					=> $this->input->post('note'),
                'created_by' 			=> $this->session->userdata('user_id'),
                'type' 					=> 'received',
				'currencies' 			=> json_encode($currencies),
				'bank_account' 			=> $paying_to,
            );
			if ($this->Settings->module_account == 1) {
				$ARBybiller  		= $this->site->getAccountSettingByBiller($installment->biller_id);
				$account_receivable = $ARBybiller->default_receivable ? $ARBybiller->default_receivable:$this->accounting_setting->default_receivable;
				$productAcc 		= $this->site->getProductAccByProductId($product_id);
				$account_receivable	= ($productAcc->ar_account) ? $productAcc->ar_account: $account_receivable;
				$account_receivable = ($sale->module_type =='rental') ? $account_receivable:$this->accounting_setting->installment_outstanding_acc;
				$accTranPayments[]  = array(
						'tran_type' 	=> 'Payment',
						'tran_date' 	=> $date,
						'reference_no' 	=> $reference_no,
						'account_code' 	=> $account_receivable,
						'amount' 		=> -($this->input->post('principal-paid')+$this->input->post('discount')),
						'narrative' 	=> $this->site->getAccountName($account_receivable).' '.'Installment Payment '.$installment->reference_no,
						'description' 	=> $this->input->post('note'),
						'biller_id' 	=> $installment->biller_id,
						'project_id' 	=> $installment->project_id,
						'created_by' 	=> $this->session->userdata('user_id'),
						'customer_id' 	=> $installment->customer_id,
					);
				if ($this->input->post('interest-paid') !=0) {
					$accTranPayments[] = array(
							'tran_type' 	=> 'Payment',
							'tran_date' 	=> $date,
							'reference_no' 	=> $reference_no,
							'account_code' 	=> $this->accounting_setting->default_interest_income,
							'amount' 		=> -($this->input->post('interest-paid')),
							'narrative' 	=> $this->site->getAccountName($this->accounting_setting->default_interest_income).' '.'Installment Payment '.$installment->reference_no,
							'description' 	=> $this->input->post('note'),
							'biller_id' 	=> $installment->biller_id,
							'project_id' 	=> $installment->project_id,
							'created_by' 	=> $this->session->userdata('user_id'),
							'customer_id' 	=> $installment->customer_id,
						);
				}
				$accTranPayments[] = array(
						'tran_type' 	=> 'Payment',
						'tran_date' 	=> $date,
						'reference_no' 	=> $reference_no,
						'account_code'	=> $paying_to,
						'amount' 		=> ($this->input->post('principal-paid') + $this->input->post('interest-paid')),
						'narrative' 	=> $this->site->getAccountName($paying_to).' '.'Installment Payment '.$installment->reference_no,
						'description' 	=> $this->input->post('note'),
						'biller_id' 	=> $installment->biller_id,
						'project_id' 	=> $installment->project_id,
						'created_by' 	=> $this->session->userdata('user_id'),
						'customer_id' 	=> $installment->customer_id,
					);
				if ($this->input->post('discount') > 0) {
					$accTranPayments[] = array(
						'tran_type' 	=> 'Payment',
						'tran_date' 	=> $date,
						'reference_no' 	=> $reference_no,
						'account_code' 	=> $this->accounting_setting->default_sale_discount,
						'amount' 		=> $this->input->post('discount'),
						'narrative' 	=> $this->site->getAccountName($this->accounting_setting->default_sale_discount).' '.'Installment Payment Discount '.$installment->reference_no,
						'description' 	=> $this->input->post('note'),
						'biller_id' 	=> $installment->biller_id,
						'project_id' 	=> $installment->project_id,
						'created_by' 	=> $this->session->userdata('user_id'),
						'customer_id' 	=> $installment->customer_id,
					);
				}
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
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }
            // $this->bpas->print_arrays($payment);
		}
		if ($this->form_validation->run() == true && $this->installments_model->addPayment($payment, $customer_id, $accTranPayments)) {
			$this->session->set_flashdata('message', lang("payment_added") .' '.$payment['reference_no']);
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$this->data['id'] = $id;
			$this->data['installment_item'] = $installment_item;
			$this->data['installment']      = $installment;
			if($this->config->item('schools')) {
				$sale = $this->installments_model->getSaleByID($installment->sale_id);
				if ($sale && $sale->type == "school") {
					$customer_info = $this->site->getCompanyByID($sale->customer_id);
					$student_info  = $this->installments_model->getStudentByID($customer_info->student_id);
					$bank_infos    = $this->installments_model->getStudentBanks($student_info ? $student_info->family_id : false);
					$this->data['bank_info'] = $bank_infos ? $bank_infos[0] : false;
				}
			}
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'installments/add_payment', $this->data);
        }
    }
	
	public function edit_payment($id = NULL)
    {
		$this->bpas->checkPermissions('edit', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment 		= $this->installments_model->getPaymentByID($id);
		$installment 	= $this->installments_model->getInstallmentByID($payment->installment_id);
		$sale 			= $this->installments_model->getSaleByID($installment->sale_id);
		$product_id 	= $installment->product_id?$installment->product_id:'';
		if($this->config->item("receive_payment") && $this->installments_model->getReceivePyamentByPaymentID($id)){
			$this->session->set_flashdata('error', lang('payment_cannot_edit'));
            $this->bpas->md();
		}	
		if ($installment->status == 'inactive') {
			$this->session->set_flashdata('error', lang("installment_has_inactive"));
			$this->bpas->md();
		}else if ($installment->status == 'returned') {
			$this->session->set_flashdata('error', lang("installment_has_returned"));
			$this->bpas->md();
		}else if ($installment->status == 'voiced') {
			$this->session->set_flashdata('error', lang("installment_has_voiced"));
			$this->bpas->md();
		}
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $installment->customer_id;
                $amount = $this->input->post('amount-paid')-$payment->amount;
                if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin || $this->GP['installments-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $payment->date;
            }
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
			
			//$paymentAcc = $this->site->getAccountSettingByBiller($installment->biller_id);
			$bank_name="";
			$account_name="";
			$account_number="";
			$cheque_number="";
			$cheque_date="";
			if($this->input->post('paid_by')=='deposit' || $this->input->post('paid_by')=='gift_card'){
				$paying_to = $this->accounting_setting->default_sale_deposit;
			}else{
				$cash_account = $this->site->getCashAccountByCode($this->input->post('paid_by'));

				$paying_to = $cash_account->account_code;
				/*if($cash_account->type=="bank"){
					$bank_name = $cash_account->name;
					$account_name = $this->input->post('account_name');
					$account_number = $this->input->post('account_number');
				}else if($cash_account->type=="cheque"){
					$bank_name = $this->input->post('bank_name');
					$cheque_number = $this->input->post('cheque_number');
					$cheque_date = $this->bpas->fsd($this->input->post('cheque_date'));
				}*/
			}
            $payment = array(
                'date' 						=> $date,
                'reference_no' 				=> $this->input->post('reference_no'),
				'installment_customer_id' 	=> $installment->customer_id,
                'amount' 				=> $this->input->post('principal-paid'),
				'interest_paid' 		=> $this->input->post('interest-paid'),
				'penalty_paid' 			=> $this->input->post('penalty-paid'),
				'discount' 				=> $this->input->post('discount'),
                'paid_by' 				=> $this->input->post('paid_by'),
                'note' 					=> $this->input->post('note'),
                'created_by' 			=> $this->session->userdata('user_id'),
				'currencies' 			=> json_encode($currencies),
				'bank_account' 			=> $paying_to,
				'cheque_no'    			=> $this->input->post('cheque_no'),
				'cc_no'        			=> $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
				'cc_holder'    			=> $this->input->post('pcc_holder'),
				'cc_month'     			=> $this->input->post('pcc_month'),
				'cc_year'      			=> $this->input->post('pcc_year'),
				'cc_type'      			=> $this->input->post('pcc_type'),
            );
			
			if($this->Settings->module_account == 1){
				$ARBybiller  		= $this->site->getAccountSettingByBiller($installment->biller_id);
				$account_receivable = $ARBybiller->default_receivable ? $ARBybiller->default_receivable:$this->accounting_setting->default_receivable;
				$productAcc 		= $this->site->getProductAccByProductId($product_id);
				$account_receivable	= ($productAcc->ar_account) ? $productAcc->ar_account: $account_receivable;

				$account_receivable = ($sale->module_type =='rental') ? $account_receivable:$this->accounting_setting->installment_outstanding_acc;

				$accTranPayments[] = array(
						'tran_no' => $id,
						'tran_type' => 'Payment',
						'tran_date' => $date,
						'reference_no' => $this->input->post('reference_no'),
						'account_code' => $account_receivable,
						'amount' => -($this->input->post('principal-paid')+$this->input->post('discount')),
						'narrative' => 'Installment Payment '.$installment->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $installment->biller_id,
						'project_id' => $installment->project_id,
						'created_by' => $this->session->userdata('user_id'),
						'customer_id' => $installment->customer_id,
					);
				if($this->input->post('interest-paid') !=0){
					$accTranPayments[] = array(
						'tran_no' => $id,
						'tran_type' => 'Payment',
						'tran_date' => $date,
						'reference_no' => $this->input->post('reference_no'),
						'account_code' => $this->accounting_setting->default_interest_income,
						'amount' => -($this->input->post('interest-paid')),
						'narrative' => 'Installment Payment '.$installment->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $installment->biller_id,
						'project_id' => $installment->project_id,
						'created_by' => $this->session->userdata('user_id'),
						'customer_id' => $installment->customer_id,
					);
				}
				$accTranPayments[] = array(
						'tran_no' => $id,
						'tran_type' => 'Payment',
						'tran_date' => $date,
						'reference_no' => $this->input->post('reference_no'),
						'account_code' => $paying_to,
						'amount' => ($this->input->post('principal-paid') + $this->input->post('interest-paid')),
						'narrative' => 'Installment Payment '.$installment->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $installment->biller_id,
						'project_id' => $installment->project_id,
						'created_by' => $this->session->userdata('user_id'),
						'customer_id' => $installment->customer_id,
					);
					
				if($this->input->post('discount')>0){
					$accTranPayments[] = array(
						'tran_no' => $id,
						'tran_type' => 'Payment',
						'tran_date' => $date,
						'reference_no' => $this->input->post('reference_no'),
						'account_code' => $this->accounting_setting->default_sale_discount,
						'amount' => $this->input->post('discount'),
						'narrative' => 'Installment Payment Discount '.$installment->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $installment->biller_id,
						'project_id' => $installment->project_id,
						'created_by' => $this->session->userdata('user_id'),
						'customer_id' => $installment->customer_id,
					);
				}
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
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
		
        if ($this->form_validation->run() == true && $this->installments_model->updatePayment($id, $payment, $customer_id, $accTranPayments)) {
			$this->session->set_flashdata('message', lang("payment_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment;
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'installments/edit_payment', $this->data);
        }
    }
	
	public function delete_payment($id = NULL)
    {
        $this->bpas->checkPermissions('delete', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$payment = $this->installments_model->getPaymentByID($id);
		// if($this->installments_model->getReceivePyamentByPaymentID($id)){
		// 	$this->session->set_flashdata('error', lang('payment_cannot_delete'));
        //     $this->bpas->md();
		// }
		
		$installment = $this->installments_model->getInstallmentByID($payment->installment_id);
		if ($installment->status == 'inactive') {
			$this->session->set_flashdata('error', lang("installment_has_inactive"));
			$this->bpas->md();
		}else if ($installment->status == 'returned') {
			$this->session->set_flashdata('error', lang("installment_has_returned"));
			$this->bpas->md();
		}else if ($installment->status == 'voiced') {
			$this->session->set_flashdata('error', lang("installment_has_voiced"));
			$this->bpas->md();
		}
        if ($this->installments_model->deletePayment($id)) {
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

	public function view_payments($id = NULL)
    {
		$this->bpas->checkPermissions('payments', true);
		$installment_item = $this->installments_model->getInstallmentItemsByID($id);
		$installment = $this->installments_model->getInstallmentByID($installment_item->installment_id);
		if ($installment_item->status == 'inactive') {
			$this->session->set_flashdata('error', lang("installment_inactived"));
			$this->bpas->md();
		}
		$this->data['installment'] = $installment;
		$this->data['installment_item'] = $installment_item;
		$this->data['payments'] = $this->installments_model->getInvoicePaymentsByInstallmentItemID($id);
        $this->data['inv'] = $this->installments_model->getSaleByID($installment->sale_id);
        $this->load->view($this->theme . 'installments/view_payments', $this->data);
    }
    
	public function add_multi_payment($id = null)
    {
        $this->bpas->checkPermissions("payments", true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$ids = explode('InstallmentID',$id);
        $installments = $this->installments_model->getMultiInstallmentsByID($ids);
		if(!$installments){
			$this->session->set_flashdata('error', lang("installment_has_paid"));
			$this->bpas->md();
		}

		if($installments[0]->status =='inactive'){
			$this->session->set_flashdata('error', lang("installment_has_inactive"));
			$this->bpas->md();
		}else if($installments[0]->status =='returned'){
			$this->session->set_flashdata('error', lang("installment_has_returned"));
			$this->bpas->md();
		}
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin  || $this->bpas->GP['installments-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
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
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }
			$total_principal  = $this->input->post('principal-paid');
			$total_interest   = $this->input->post('interest-paid');
			$total_penalty 	  = $this->input->post('penalty-paid');
			$camounts 	      = $this->input->post("c_amount");
			

			
			$currencies = array();
			$paid_currencies = array();
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$paid_currencies[$currency[$key]] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			$amount_paid_tmp = 0;
			$amount_tmp = 0;
			$cur_def = $this->site->getCurrencyByCode($this->Settings->default_currency);
			for($i=0; $i<count($ids); $i++){
				$installment_item = $this->installments_model->getInstallmentItemsByID($ids[$i]);
				$installment 	  = $this->installments_model->getInstallmentByID($installment_item->installment_id);

				$sale = $this->installments_model->getSaleByID($installment->sale_id);
				$reference_no 	  = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$installment->biller_id);


				if($total_principal > 0 || $total_interest > 0 || $total_penalty > 0){
					$binstallment = $this->installments_model->getInstallmentBalanceByID($ids[$i]);
					if($binstallment){
						$principal = $binstallment->principal - $binstallment->paid;
						$amt_principal = $principal;
						if($total_principal > $amt_principal){
							$pay_principal = $amt_principal;
							$total_principal = $total_principal - $amt_principal;
						}else{
							$pay_principal = $total_principal;
							$total_principal = 0;
						}
						
						$interest = $binstallment->interest - $binstallment->interest_paid;
						$amt_interest = $interest;
						if($total_interest > $amt_interest){
							$pay_interest = $amt_interest;
							$total_interest = $total_interest - $amt_interest;
						}else{
							$pay_interest = $total_interest;
							$total_interest = 0;
						}

						$penalty = $binstallment->penalty - $binstallment->penalty_paid;
						$amt_penalty = $penalty;
						if($total_penalty > $amt_penalty){
							$pay_penalty_paid = $amt_penalty;
							$total_penalty = $total_penalty - $amt_penalty;
						}else{
							$pay_penalty_paid = $total_penalty;
							$total_penalty = 0;
						}
						
						$currencies = array();
						if(!empty($camounts)){
							$total_paid = $pay_principal + $pay_interest + $pay_penalty_paid;
							foreach($paid_currencies as $cur_code => $paid_currencie){
								$paid_cur = $paid_currencie['amount'];
								if($paid_cur > 0){
									if($cur_code != $cur_def->code){
										if($paid_currencie['rate'] > $cur_def->rate){
											$paid_cur = $paid_cur / $paid_currencie['rate'];
										}else{
											$paid_cur = $paid_cur * $cur_def->rate;
										}
									}
					
									if($paid_cur >= $total_paid && $total_paid > 0){
										$paid_currencie['amount'] = $total_paid;
										if($cur_code != $cur_def->code){
											if($paid_currencie['rate'] > $cur_def->rate){
												$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid) * $paid_currencie['rate'];
											}else{
												$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid) / $cur_def->rate;
											}
										}else{
											$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid);
										}
										$total_paid = 0;
									}else{
										if($total_paid > 0){
											$paid_currencie['amount'] = $paid_cur;
											$paid_currencies[$cur_code]['amount'] = 0;
											$total_paid = $total_paid - $paid_cur;
										}else{
											$paid_currencie['amount'] = 0;
										}
									}
								}								
								if($cur_code != $cur_def->code){
									if($paid_currencie['rate'] > $cur_def->rate){
										$paid_currencie['amount'] = $paid_currencie['amount'] * $paid_currencie['rate'];
									}else{
										$paid_currencie['amount'] = $paid_currencie['amount'] / $cur_def->rate;
									}
								}
								$currencies[] = $paid_currencie;
							}
						}
						
						$cash_account = $this->site->getCashAccountByCode($this->input->post('paid_by'));
						$paying_to = $cash_account->account_code;
		
						
						$payment[] = array(
							'date' 				=> $date,
							'sale_id' 			=> $installment->sale_id,
							'installment_id' 	=> $binstallment->installment_id,
							'installment_item_id' => $binstallment->id,
							'installment_customer_id' => $installment->customer_id,
							'reference_no' 		=> $reference_no,
							'amount' 			=> $pay_principal,
							'interest_paid' 	=> $pay_interest,
							'penalty_paid' 		=> $pay_penalty_paid,
							'paid_by' 			=> $this->input->post('paid_by'),
							'note' 				=> $this->input->post('note'),
							'created_by' 		=> $this->session->userdata('user_id'),
							'type'				=> 'received',
							'currencies' 		=> json_encode($currencies),
							'account_code' 		=> $paying_to,
						);
						
						if($this->Settings->accounting == 1){
							$installment 	= $this->installments_model->getInstallmentByID($binstallment->installment_id);
							$paymentAcc 	= $this->site->getAccountSettingByBiller($installment->biller_id);
							
							$sale 			= $this->installments_model->getSaleByID($installment->sale_id);
							$account_receivable = ($sale->module_type =='rental') ? $sale->ar_account:$this->accounting_setting->installment_outstanding_acc;

							$accTranPayments[$binstallment->id][] = array(
									'tran_type' 	=> 'Payment',
									'tran_date' 	=> $date,
									'reference_no' 	=> $reference_no,
									'account_code' 	=> $account_receivable,
									'amount' 		=> -($pay_principal),
									'narrative' 	=> $this->site->getAccountName($account_receivable),
									'description' 	=> $this->input->post('note'),
									'note'			=> 'Installment Payment '.$installment->reference_no,
									'biller_id' 	=> $installment->biller_id,
									'project_id' 	=> $installment->project_id,
									'created_by' 	=> $this->session->userdata('user_id'),
									'customer_id' 	=> $installment->customer_id,
								);
							if($pay_interest !=0){
								$accTranPayments[$binstallment->id][] = array(
										'tran_type' 	=> 'Payment',
										'tran_date' 	=> $date,
										'reference_no' 	=> $reference_no,
										'account_code' 	=> $paymentAcc->installment_interest_acc,
										'amount' 		=> -($pay_interest),
										'narrative' 	=> $this->site->getAccountName($paymentAcc->installment_interest_acc),
										'description' 	=> $this->input->post('note'),
										'note'			=> 'Installment Payment '.$installment->reference_no,
										'biller_id' 	=> $installment->biller_id,
										'project_id' 	=> $installment->project_id,
										'created_by' 	=> $this->session->userdata('user_id'),
										'customer_id' 	=> $installment->customer_id,
									);
							}
							$accTranPayments[$binstallment->id][] = array(
									'tran_type' 	=> 'Payment',
									'tran_date' 	=> $date,
									'reference_no' 	=> $reference_no,
									'account_code' 	=> $paying_to,
									'amount' 		=> ($pay_principal + $pay_interest),
									'narrative' 	=> $this->site->getAccountName($paying_to),
									'description' 	=> $this->input->post('note'),
									'note'			=> 'Installment Payment '.$installment->reference_no,
									'biller_id' 	=> $installment->biller_id,
									'project_id' 	=> $installment->project_id,
									'created_by' 	=> $this->session->userdata('user_id'),
									'customer_id' 	=> $installment->customer_id,
								);
						}
					}
				}
			}
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->installments_model->addMultiPayment($payment, $accTranPayments)) {
			$this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['installments'] = $installments;
            $this->data['installment'] = $installments[0];
            $this->data['payment_ref'] = ''; 
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'installments/add_multi_payment', $this->data);
        }
    }
	
	public function payment_note($id = null)
    {
		$this->bpas->checkPermissions("payments", true);
        $payment = $this->installments_model->getPaymentByID($id);
        $inv = $this->installments_model->getInstallmentByID($payment->installment_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = lang("payment_note");
        $this->load->view($this->theme . 'installments/payment_note', $this->data);
    }
	
	public function get_holiday()
	{
		$range = array();
		$holiday = $this->installments_model->getHoliday();
		if (isset($holiday) && $holiday != false) {
			foreach($holiday as $i => $hl){
				$start = $hl->start;
				$end = $hl->end;
				$currentDate = strtotime($start);
				while($currentDate <= strtotime($end)){
					$formatted = date("d-m-Y", $currentDate);
					$range[] = $formatted;
					$currentDate = strtotime("+1 day", $currentDate);
				}
			}
		}
		echo json_encode($range);
	}
	
	public function getAssignations($id = null)
    {	
		$this->bpas->checkPermissions("edit");
		if(!$id){
			$id = $this->input->get("id");
		}
		$delete_link = "<a href='#' class='po' title='<b>" . lang("delete_assignation") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('installments/delete_assignation/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_assignation') . "</a>";
		
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$delete_link.'</li>
					</ul>
				</div></div>';
				
        $this->load->library('datatables');
        $this->datatables->select("
				installment_assigns.id as id,
				installment_assigns.assigned_date,
				oc.name as old_customer,
				nc.name as new_customer,
				installment_assigns.note,
				concat(bpas_users.last_name,' ',bpas_users.first_name) as assigned_by")
            ->from('installment_assigns')
			->join("companies as oc","oc.id=installment_assigns.old_customer","left")
			->join("companies as nc","nc.id=installment_assigns.new_customer","left")
			->join("users","users.id=assigned_by","left")
			->where("installment_id", $id);
			
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function delete_assignation($id = NULL)
    {
        $this->bpas->checkPermissions('edit', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$assign = $this->installments_model->getAssignationByID($id);
		$installment = $this->installments_model->getInstallmentByID($assign->installment_id);
		if ($installment->status == 'inactive') {
			$this->session->set_flashdata('error', lang("installment_has_inactive"));
			$this->bpas->md();
		}else if ($installment->status == 'completed') {
			$this->session->set_flashdata('error', lang("installment_has_completed"));
			$this->bpas->md();
		}else if ($installment->status == 'returned') {
			$this->session->set_flashdata('error', lang("installment_has_returned"));
			$this->bpas->md();
		}else if ($installment->status == 'voiced') {
			$this->session->set_flashdata('error', lang("installment_has_voiced"));
			$this->bpas->md();
		}
		$max = $this->installments_model->getMaxAssignation();
		if($id != $max->id){
			$this->session->set_flashdata('error', lang('this_assignation_cannot_delete'));
            $this->bpas->md();
		}
        if ($this->installments_model->deleteAssignation($id)) {
			if ($this->input->is_ajax_request()) {
                echo lang("assignation_deleted");
				die();
            }
            $this->session->set_flashdata('message', lang("assignation_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function add_assignation($id = null)
    {
		$this->bpas->checkPermissions("edit", true);
		$installment = $this->installments_model->getInstallmentByID($id);
		if ($installment->status == 'inactive') {
			$this->session->set_flashdata('error', lang("installment_has_inactive"));
			$this->bpas->md();
		}else if ($installment->status == 'completed') {
			$this->session->set_flashdata('error', lang("installment_has_completed"));
			$this->bpas->md();
		}else if ($installment->status == 'returned') {
			$this->session->set_flashdata('error', lang("installment_has_returned"));
			$this->bpas->md();
		}else if ($installment->status == 'voiced') {
			$this->session->set_flashdata('error', lang("installment_has_voiced"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('assign_date', lang("assign_date"), 'required');
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        if ($this->form_validation->run() == true && $installment) {
			$data = array(
				'installment_id' => $installment->id,
				'old_customer' => $installment->customer_id,
				'new_customer' => $this->input->post('customer', true),
				'note' => $this->input->post('note'),
				'assigned_by' => $this->session->userdata('user_id'),
				'assigned_date' => $this->bpas->fld($this->input->post('assign_date')),
            );
		}else if($this->input->post('add_assignation')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER["HTTP_REFERER"]."#assignations");
		}
		if ($this->form_validation->run() == true && $this->installments_model->addAssignation($data)) {
			$this->session->set_flashdata('message', lang("assignation_added"));
            redirect($_SERVER["HTTP_REFERER"]."#assignations");
        }else{
			$this->data['id'] = $id;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['installment'] = $this->installments_model->getInstallmentByID($id);
			$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->data['customers'] = $this->site->getAllCompanies('customer');
 			$this->load->view($this->theme . 'installments/add_assignation', $this->data);
		}
    }
	
	public function assignation_actions($id = NULL, $pdf = NULL, $xls = NULL)
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($pdf || $xls) {
			
			$this->db->select("
				installment_assigns.id as id,
				installment_assigns.assigned_date,
				oc.name as old_customer,
				nc.name as new_customer,
				installment_assigns.note,
				concat(bpas_users.last_name,' ',bpas_users.first_name) as assigned_by")
            ->from('installment_assigns')
			->join("companies as oc","oc.id=installment_assigns.old_customer","left")
			->join("companies as nc","nc.id=installment_assigns.new_customer","left")
			->join("users","users.id=assigned_by","left")
			->where("installment_id",$id);
			
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
				$this->excel->getActiveSheet()->setTitle(lang('assignations'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('assigned_date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('old_customer'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('new_customer'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('description'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('assigned_by'));
				$row = 2;
				foreach ($data as $i=> $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrsd($data_row->assigned_date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->old_customer);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->new_customer);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->description);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->assigned_by);
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $filename = 'assignations_'.date("Y_m_d_H_i_s");
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function payoff($id = NULL, $payoff = FALSE)
	{
		$this->bpas->checkPermissions("payoff");
		$installment = $this->installments_model->getInstallmentByID($id);
		$principal_paid = $this->installments_model->getPrincipalPaidByInstallmentID($id);
		if($installment->status != 'active'){
			$this->session->set_flashdata('error', lang("installment_cannot_payoff"));
			$this->bpas->md();
		}else if($installment->principal_amount > $principal_paid){
			$this->session->set_flashdata('error', lang("installment_cannot_payoff"));
			$this->bpas->md();
		}
        if ($this->installments_model->payOff($id, $payoff)) {
			if ($this->input->is_ajax_request()) {
				echo lang("loan_payoff"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("loan_payoff"));
        redirect($_SERVER["HTTP_REFERER"]);
	}
	
	public function view_agreement($id = null)
    {
        $this->bpas->checkPermissions("index");
		$this->data['id'] = $id;
		$this->data['installment'] = $this->installments_model->getInstallmentByID($id);
		$this->data['biller'] = $this->site->getCompanyByID($this->data['installment']->biller_id);
		$this->data['customer'] = $this->site->getCompanyByID($this->data['installment']->customer_id);
		$this->load->view($this->theme . 'installments/view_agreement', $this->data);
    }
	
	public function get_frequency_deadlines()
	{
		$frequency_id = $this->input->get('frequency_id');
		$frequency_deadlines = $this->installments_model->getFrequencyDeadlines($frequency_id);
		echo json_encode($frequency_deadlines);
	}

	public function penalty()
	{
		$this->bpas->checkPermissions('penalty');
		$this->data['modal_js'] = $this->site->modal_js();
		$bc   = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('penalty')));
		$meta = array('page_title' => lang('penalty'), 'bc' => $bc);
		$this->page_construct('installments/penalty', $meta, $this->data);
	}

	public function getPenalty()
	{
		$this->bpas->checkPermissions('penalty');
		$this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_penalty") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('installments/delete_penalty/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_penalty') . "</a>";
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('installments/edit_penalty/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_penalty').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables->select("id, from_day, to_day, type, amount")
             ->from("installments_penalty")->order_by('from_day','ASC')
             ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_penalty()
	{
		$this->bpas->checkPermissions('penalty');
		if ($this->Settings->installment_penalty_option == 1) {
			$this->form_validation->set_rules('from_day', lang("from_day"), 'required');
			$this->form_validation->set_rules('to_day', lang("to_day"), 'required');
		}
		$this->form_validation->set_rules('amount', lang("amount"), 'numeric|required');
		if ($this->form_validation->run() == true) {	
			$data = array(
				'from_day'	=> $this->input->post('from_day'),
				'to_day'	=> $this->input->post('to_day'),
				'type'		=> $this->input->post('type'),
				'amount'	=> $this->input->post('amount')
			);
		}
		if ($this->form_validation->run() == true && $id = $this->installments_model->addPenalty($data)) {
			$this->session->set_flashdata('message', $this->lang->line("penalty_added"));
            admin_redirect("installments/penalty");
        } else {
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'installments/add_penalty', $this->data);	
		}	
	}
	
	public function edit_penalty($id = false)
	{
		//$this->bpas->checkPermissions('penalty');
		if ($this->Settings->installment_penalty_option == 1) {
			$this->form_validation->set_rules('from_day', lang("from_day"), 'required');
			$this->form_validation->set_rules('to_day', lang("to_day"), 'required');
		}
		$this->form_validation->set_rules('amount', lang("amount"), 'numeric|required');
		if ($this->form_validation->run() == true){
			$data = array(
				'from_day'	=> $this->input->post('from_day'),
				'to_day'	=> $this->input->post('to_day'),
				'type'		=> $this->input->post('type'),
				'amount'	=> $this->input->post('amount')
			);
		}
		if ($this->form_validation->run() == true && $id = $this->installments_model->updatePenalty($id, $data)) {
			$this->session->set_flashdata('message', $this->lang->line("penalty_updated"));
            admin_redirect("installments/penalty");
        } else {
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['row'] = $this->installments_model->getPenaltyByID($id);
			$this->data['id'] = $id;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'installments/edit_penalty', $this->data);	
		}	
	}
	
	public function delete_penalty($id = null)
    {		
		$this->bpas->checkPermissions('penalty');
        if (isset($id) || $id != null){
        	 if ($this->installments_model->deletePenalty($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('penalty_deleted')]);
				}
				$this->session->set_flashdata('message', lang('penalty_deleted'));
				admin_redirect('welcome');
			}
        }
    }

	public function getSaleItemPrice()
	{
        $sale_id 	= $this->input->get('sale_id');
        $product_id = $this->input->get('product_id');
        $sale_item_id = $this->input->get('sale_item_id');
        $this->db->select('subtotal');
        $this->db->where(array('sale_items.sale_id' => $sale_id, 'sale_items.product_id' => $product_id, 'sale_items.id' => $sale_item_id), 1);
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            $data   = $q->row();
        	$this->bpas->send_json(['price'=> $this->bpas->formatDecimal($data->subtotal)]);
        } else {
            $this->bpas->send_json(false);
        }
    }
}