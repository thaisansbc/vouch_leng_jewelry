<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Clearances extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        $this->lang->load('clearances', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('clearances_model');
		$this->digital_upload_path = 'files/';
		$this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '10240';
    }
	
	
	public function suggestions()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->bpas->analyze_term($term);
        $sr = $analyzed['term'];
        $rows = $this->clearances_model->getProductServiceNames($sr);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
				$row->description = "";
				$row->quantity = 1;
				$row->item_id = $row->id;
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row);
                $r++;
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

	
	public function get_booking_fees()
	{
		$booking_id = $this->input->get('booking_id') ? $this->input->get('booking_id') : false;
		$booking = $this->clearances_model->getBookingByID($booking_id);
		$combo_items = $this->clearances_model->getCustomerComboItems($booking->customer_id,$booking->product_id);
		$truckings = $this->clearances_model->getIndexTruckingFeeByBooking($booking_id);
		$trucking_configs = false;
		$clearance_configs = false;
		$trucking_config = $this->clearances_model->getClearanceConfig($booking->biller_id);
		if($trucking_config){
			$trucking_configs[$trucking_config->trucking_fee] = "trucking_fee";
			$trucking_configs[$trucking_config->lolo_fee] = "lolo_fee";
			$trucking_configs[$trucking_config->extra_fee] = "extra_fee";
			$trucking_configs[$trucking_config->stand_by_fee] = "stand_by_fee";
			
			$clearance_configs[$trucking_config->booking_fee] = "booking_fee";
			$clearance_configs[$trucking_config->extra_loading] = "extra_loading";
			$clearance_configs[$trucking_config->extra_lock] = "extra_lock";
			$clearance_configs[$trucking_config->extra_document] = "extra_document";
			$clearance_configs[$trucking_config->port_lolo] = "port_lolo";
		}
		$pr = false;
		if($combo_items){
			$c = str_replace(".", "", microtime(true));
            $r = 0;
			$container_extracts = $this->clearances_model->getContainerExtractByBooking($booking_id);
			if($container_extracts){
				foreach($container_extracts as $container_extract){
					$container_extract->description = "";
					$pr[($c + $r)] = array('id' => ($c + $r), 'item_id' => $container_extract->item_id, 'label' => $container_extract->name . " (" . $container_extract->code . ")", 'row' => $container_extract);
					$r++;
				}
			}
			
			if($truckings["late_gate_qty"] > 0){
				$product_late_gate = $this->site->getProductByID($trucking_config->late_gate);
				if($product_late_gate){
					$late_gate_info['item_id'] = $trucking_config->late_gate;
					$late_gate_info['name'] = $product_late_gate->name;
					$late_gate_info['code'] = $product_late_gate->code;
					$late_gate_info['cost'] = $product_late_gate->cost;
					$late_gate_info['price'] = $product_late_gate->price;
					$late_gate_info['quantity'] = $truckings["late_gate_qty"];
					$late_gate_info['description'] = implode($truckings["late_gate_con"],", ");
					$late_gate_info = (object) $late_gate_info;
					$pr[($c + $r)] = array('id' => ($c + $r), 'item_id' => $late_gate_info->item_id, 'label' => $late_gate_info->name . " (" . $late_gate_info->code . ")", 'row' => $late_gate_info);
					$r++;
				}
			}
			
			if(isset($truckings["cancel_fee"])){
				$product_cancel = $this->site->getProductByID($trucking_config->cancel_fee);
				foreach($truckings["cancel_fee"] as $supplier_id => $costs){
					foreach($costs as $cost => $trucking){
						if($trucking > 0 && $cost > 0){
							$cancel_info['item_id'] = $trucking_config->cancel_fee;
							$cancel_info['name'] = $product_cancel->name;
							$cancel_info['code'] = $product_cancel->code;
							$cancel_info['cost'] = $cost;
							$cancel_info['price'] = $product_cancel->price;
							$cancel_info['quantity'] = $trucking;
							$cancel_info['supplier_id'] = $supplier_id;
							if(isset($truckings["cancel_fee_con"][$supplier_id][$cost])){
								$cancel_info["description"] = implode($truckings["cancel_fee_con"][$supplier_id][$cost],", ");
							}
							$pr[($c + $r)] = array('id' => ($c + $r), 'item_id' => $trucking_config->cancel_fee, 'label' => $product_cancel->name . " (" . $product_cancel->code . ")", 'row' => $cancel_info);
							$r++;
						}
					}
				}
			}
			

			foreach($combo_items as  $combo_item){
				if($combo_item->quantity == 0 || !$combo_item->quantity){
					$combo_item->quantity = $truckings ? $truckings["container_qty"] : 1;
				}
				$combo_item->description = "";
				if(isset($trucking_configs[$combo_item->item_id])) {
					$row = (array) $combo_item;
					if($truckings[$trucking_configs[$combo_item->item_id]]){
						foreach($truckings[$trucking_configs[$combo_item->item_id]] as $supplier_id => $costs){
							foreach($costs as $cost => $trucking){
								if($trucking > 0 && $cost > 0){
									$row["cost"] = $cost;
									$row["quantity"] = $trucking;
									$row["supplier_id"] = $supplier_id;
									$row["trucking_ids"] = json_encode($truckings["trucking_id"][$supplier_id]);
									if(isset($truckings[$trucking_configs[$combo_item->item_id]."_con"][$supplier_id][$cost])){
										$row["description"] = implode($truckings[$trucking_configs[$combo_item->item_id]."_con"][$supplier_id][$cost],", ");
									}
									$pr[($c + $r)] = array('id' => ($c + $r), 'item_id' => $combo_item->item_id, 'label' => $combo_item->name . " (" . $combo_item->code . ")", 'row' => $row);
									$r++;
								}
							}
						}
					}
				}else if(isset($clearance_configs[$combo_item->item_id])) {
					$row = (array) $combo_item;
					if($truckings[$clearance_configs[$combo_item->item_id]]){
						foreach($truckings[$clearance_configs[$combo_item->item_id]] as $cost => $trucking){
							if($trucking > 0 && $cost > 0){
								$row["cost"] = $cost;
								$row["quantity"] = $trucking;
								$row["supplier_id"] = $combo_item->supplier_id;
								if(isset($truckings[$clearance_configs[$combo_item->item_id]."_con"][$cost])){
									$row["description"] = implode($truckings[$clearance_configs[$combo_item->item_id]."_con"][$cost],", ");
								}
								$pr[($c + $r)] = array('id' => ($c + $r), 'item_id' => $combo_item->item_id, 'label' => $combo_item->name . " (" . $combo_item->code . ")", 'row' => $row);
								$r++;
							}
						}
					}
				}else if($combo_item->price > 0 || $combo_item->cost > 0){
					$pr[($c + $r)] = array('id' => ($c + $r), 'item_id' => $combo_item->item_id, 'label' => $combo_item->name . " (" . $combo_item->code . ")", 'row' => $combo_item);
					$r++;
				}
			}
		}
		$this->bpas->send_json($pr);
	}


	public function index()
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'clearances', 'page' => lang('clearance')));
        $meta = array('page_title' => lang('clearances'), 'bc' => $bc);
        $this->page_construct('clearances/index', $meta, $this->data);
    }
	
	
	public function getClearances()
    {
		$this->bpas->checkPermissions('index');
		$edit_link = anchor('clearances/edit_clearance/$1', '<i class="fa fa-edit"></i> ' . lang('edit_clearance'), ' class="edit_clearance"');
		$delete_link = "<a href='#' class='delete_clearance po' title='<b>" . $this->lang->line("delete_clearance") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('clearances/delete_clearance/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_clearance') . "</a>";	
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
           
        $this->load->library('datatables');
        $this->datatables ->select("
									clr_clearances.id as id,
									DATE_FORMAT(".$this->db->dbprefix('clr_clearances').".date, '%Y-%m-%d %T') as date,
									clr_clearances.reference_no,
									companies.company as customer,
									clr_bookings.booking_no,
									clr_clearances.invoice_no,
									IFNULL(".$this->db->dbprefix('clr_clearances').".total_expense,0) as expense,
									IFNULL(".$this->db->dbprefix('clr_clearances').".total_income,0) as income,
									clr_clearances.status,
									clr_clearances.attachment
								")
            ->from("clr_clearances")
			->join("clr_bookings","clr_bookings.id = clr_clearances.booking_id","left")
			->join("companies","companies.id = clr_clearances.customer_id","left");
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
    }

	public function add_clearance()
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		if ($this->form_validation->run() == true) {
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
			$biller_info = $this->site->getCompanyByID($biller_id);
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('clr',$biller_id);
			$customer_id = $this->input->post('customer');
			$booking_id = $this->input->post('booking_no');
			$invoice_no = $this->input->post('invoice_no');
			$cquantity = $this->input->post('cquantity');
			$commodity = $this->input->post('commodity');
			$part = $this->input->post('part');
			$part_customer = null;
			if($part){
				$this->form_validation->set_rules('part_customer', $this->lang->line("part_customer"), 'required');
				$part_customer = $this->input->post('part_customer');
			}
			if($booking_id){
				$booking_info = $this->clearances_model->getBookingByID($booking_id);
			}
			$note = $this->input->post('note');
			$total_expense = 0;
			$total_income = 0;
			$expense_items = false;
			$i = isset($_POST['item_id']) ? sizeof($_POST['item_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['item_id'][$r];
				$supplier_id = $_POST['supplier'][$r] != "" ? $_POST['supplier'][$r] : null;
				$description = $_POST['description'][$r];
                $cost = $_POST['cost'][$r];
                $price = $_POST['price'][$r];
                $quantity = $_POST['quantity'][$r];
				$trucking_ids = $_POST['trucking_ids'][$r] == "null" ? null : $_POST['trucking_ids'][$r];
				$ds_cost = strpos($cost, "%");
				if ($ds_cost !== false) {
					$pds = explode("%", $cost);
					$cost = (($price * (Float) ($pds[0])) / 100);
				}
				$ds_price = strpos($price, "%");
				if ($ds_price !== false) {
					$pds = explode("%", $price);
					$price = (($cost * (Float) ($pds[0])) / 100);
				}
				$expense = $cost * $quantity;
				$income = $price * $quantity;
				$items[] = array(
					'item_id' => $item_id,
					'supplier_id' => $supplier_id,
					'description' => $description,
					'cost' => $cost,
					'price' => $price,
					'quantity' => $quantity,
					'expense' => $expense,
					'income' => $income,
					'trucking_ids' => $trucking_ids,
				);
				$total_expense += $expense;
				$total_income += $income;
				
				$product_info = $this->clearances_model->getProductInfoByProductID($item_id);
				if($supplier_id > 0 && $expense > 0){
					$expense_items[$supplier_id][] = array(
												'product_id' => $product_info->product_id,
												'category_code' => $product_info->product_code,
												'category_name' => $product_info->product_name,
												'description' => $booking_info->booking_no,
												'unit_cost' => $cost,
												'quantity' => $quantity,
												'subtotal' => $expense,
												'trucking_ids' => $trucking_ids
											);
					if($this->Settings->accounting == 1){
						$acc_trans[] = array(
											'transaction' => 'Clearance',
											'transaction_date' => $date,
											'account' =>  $product_info->expense_acc,
											'amount' => $expense,
											'reference' => $reference_no,
											'narrative' => 'Clearance Expense '.$product_info->product_name,
											'description' => $booking_info->booking_no,
											'biller_id' => $biller_id,
											'user_id' => $this->session->userdata('user_id'),
											'supplier_id' => $supplier_id,
										);
					}				
				}
				
				if($this->Settings->accounting == 1 && $income > 0){
					$acc_trans[] = array(
										'transaction' => 'Clearance',
										'transaction_date' => $date,
										'account' =>  $product_info->sale_acc,
										'amount' => $income * (-1),
										'reference' => $reference_no,
										'narrative' => 'Clearance Income '.$product_info->product_name,
										'description' => $booking_info->booking_no,
										'biller_id' => $biller_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $customer_id
									);
				}
				
				
            }

            if (empty($items)) {
                $this->form_validation->set_rules('item', lang("order_items"), 'required');
            } else {
                krsort($items);
            }
			
            $data = array(
				'date' => $date,
				'biller_id' => $biller_id,
                'reference_no' => $reference_no,
                'customer_id' => $customer_id,
                'booking_id' => $booking_id,
				'note' => $note,
				'total_expense' => $total_expense,
				'total_income' => $total_income,
				'invoice_no' => $invoice_no,
				'quantity' => $cquantity,
				'commodity' => $commodity,
				'part' => $part,
				'part_customer' => $part_customer,
                'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s'),
            );
			
			$biller_acc = $this->site->getAccountSettingByBiller($biller_id);
			if($expense_items){
				foreach($expense_items as $supplier_id => $rows){
					$supplier_info = $this->site->getCompanyByID($supplier_id);
					$grand_total = 0;
					$trucking_ids = null;
					foreach($rows as $expense_item){
						$grand_total += $expense_item['subtotal'];
						if($expense_item["trucking_ids"]){
							foreach(json_decode($expense_item["trucking_ids"]) as $trucking_id){
								$trucking_ids[$trucking_id] = $trucking_id;
							}
						}
					}
					$expenses[$supplier_id] = array(
													'date' => $date,
													'reference' => $reference_no,
													'biller_id' => $biller_id,
													'biller' => $biller_info->company,
													'supplier_id' => $supplier_id,
													'supplier' => $supplier_info->company,
													'amount' => $grand_total,
													'grand_total' => $grand_total,
													'paid' => 0,
													'payment_status' => "pending",
													'status' => "approved",
													'created_by' => $this->session->userdata('user_id'),
													'ap_account' => $biller_acc->ap_acc,
													'trucking_ids' => json_encode($trucking_ids)
												);
					$acc_trans[] = array(
											'transaction' => 'Clearance',
											'transaction_date' => $date,
											'account' =>  $biller_acc->ap_acc,
											'amount' => $grand_total * (-1),
											'reference' => $reference_no,
											'narrative' => 'Clearance Expense',
											'description' => $booking_info->booking_no,
											'biller_id' => $biller_id,
											'user_id' => $this->session->userdata('user_id'),
											'supplier_id' => $supplier_id
										);	
				}
			}
			
			if($this->Settings->accounting == 1){
				$acc_trans[] = array(
									'transaction' => 'Clearance',
									'transaction_date' => $date,
									'account' =>  $biller_acc->ar_acc,
									'amount' => $total_income,
									'reference' => $reference_no,
									'narrative' => 'Clearance Income',
									'description' => $booking_info->booking_no,
									'biller_id' => $biller_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $customer_id
								);
			}

			if($_FILES['documents']['size'][0] > 0) {
				$this->load->library('my_upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->my_upload->initialize($config);
				if($this->my_upload->do_multi_upload("documents")) {
					$uploads = $this->my_upload->get_multi_upload_data('file_name');
					foreach($uploads as $upload){
						$attachment[] = $upload['file_name'];
					}
					$data['attachment'] = json_encode($attachment);
				}else{
					$error = $this->my_upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
			}
        }
        if ($this->form_validation->run() == true && $this->clearances_model->addClearance($data,$items,$expenses,$expense_items,$acc_trans)) {
            $this->session->set_userdata('remove_clr', 1);
            $this->session->set_flashdata('message', $this->lang->line("clearance_added"));
			if($this->input->post('add_clearance_next')){
				redirect('clearances/add_clearance');
			}else{
				redirect('clearances');
			}
        } else {
			$suppliers = $this->site->getSuppliers();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] =  $this->site->getBillers();
			$this->data['customers'] = $this->clearances_model->getCustomers();
			$this->data['suppliers'] = $suppliers ? json_encode($suppliers) : false;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('add_clearance')));
			$meta = array('page_title' => lang('add_clearance'), 'bc' => $bc);
            $this->page_construct('clearances/add_clearance', $meta, $this->data);
        }
    }
	
	
	public function edit_clearance($id = false)
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		if ($this->form_validation->run() == true) {
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('clr',$biller_id);
			$biller_info = $this->site->getCompanyByID($biller_id);
			$customer_id = $this->input->post('customer');
			$booking_id = $this->input->post('booking_no');
			$invoice_no = $this->input->post('invoice_no');
			$cquantity = $this->input->post('cquantity');
			$commodity = $this->input->post('commodity');
			$part = $this->input->post('part');
			$part_customer = null;
			if($part){
				$this->form_validation->set_rules('part_customer', $this->lang->line("part_customer"), 'required');
				$part_customer = $this->input->post('part_customer');
			}
			if($booking_id){
				$booking_info = $this->clearances_model->getBookingByID($booking_id);
			}
			$note = $this->input->post('note');
			$total_expense = 0;
			$total_income = 0;
			$expense_items = false;
			$i = isset($_POST['item_id']) ? sizeof($_POST['item_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['item_id'][$r];
				$supplier_id = $_POST['supplier'][$r] != "" ? $_POST['supplier'][$r] : null;
				$description = $_POST['description'][$r];
                $cost = $_POST['cost'][$r];
                $price = $_POST['price'][$r];
                $quantity = $_POST['quantity'][$r];
				$trucking_ids = $_POST['trucking_ids'][$r] == "null" ? null : $_POST['trucking_ids'][$r];
				$ds_cost = strpos($cost, "%");
				if ($ds_cost !== false) {
					$pds = explode("%", $cost);
					$cost = (($price * (Float) ($pds[0])) / 100);
				}
				$ds_price = strpos($price, "%");
				if ($ds_price !== false) {
					$pds = explode("%", $price);
					$price = (($cost * (Float) ($pds[0])) / 100);
				}
				$expense = $cost * $quantity;
				$income = $price * $quantity;
				$items[] = array(
					'clearance_id' => $id,
					'item_id' => $item_id,
					'supplier_id' => $supplier_id,
					'description' => $description,
					'cost' => $cost,
					'price' => $price,
					'quantity' => $quantity,
					'expense' => $expense,
					'income' => $income,
					'trucking_ids' => $trucking_ids,
				);
				$total_expense += $expense;
				$total_income += $income;
	
				$product_info = $this->clearances_model->getProductInfoByProductID($item_id);
				if($supplier_id > 0 && $expense > 0){
					$expense_items[$supplier_id][] = array(
												'product_id' => $product_info->product_id,
												'category_code' => $product_info->product_code,
												'category_name' => $product_info->product_name,
												'description' => $booking_info->booking_no,
												'unit_cost' => $cost,
												'quantity' => $quantity,
												'subtotal' => $expense,
												'trucking_ids' => $trucking_ids
											);
					if($this->Settings->accounting == 1){
						$acc_trans[] = array(
											'transaction' => 'Clearance',
											'transaction_id' => $id,
											'transaction_date' => $date,
											'account' =>  $product_info->expense_acc,
											'amount' => $expense,
											'reference' => $reference_no,
											'narrative' => 'Clearance Expense '.$product_info->product_name,
											'description' => $booking_info->booking_no,
											'biller_id' => $biller_id,
											'user_id' => $this->session->userdata('user_id'),
											'supplier_id' => $supplier_id,
											'customer_id' => null
										);
					}				
				}
				
				if($this->Settings->accounting == 1 && $income > 0){
					$acc_trans[] = array(
										'transaction' => 'Clearance',
										'transaction_id' => $id,
										'transaction_date' => $date,
										'account' =>  $product_info->sale_acc,
										'amount' => $income * (-1),
										'reference' => $reference_no,
										'narrative' => 'Clearance Income '.$product_info->product_name,
										'description' => $booking_info->booking_no,
										'biller_id' => $biller_id,
										'user_id' => $this->session->userdata('user_id'),
										'supplier_id' => null,
										'customer_id' => $customer_id
									);
					
				}
            }

            if (empty($items)) {
                $this->form_validation->set_rules('item', lang("order_items"), 'required');
            } else {
                krsort($items);
            }
			
            $data = array(
				'date' => $date,
				'biller_id' => $biller_id,
                'reference_no' => $reference_no,
                'customer_id' => $customer_id,
                'booking_id' => $booking_id,
				'note' => $note,
				'total_expense' => $total_expense,
				'total_income' => $total_income,
				'invoice_no' => $invoice_no,
				'quantity' => $cquantity,
				'commodity' => $commodity,
				'part' => $part,
				'part_customer' => $part_customer,
                'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
            );
			
			$biller_acc = $this->site->getAccountSettingByBiller($biller_id);
			
			if($expense_items){
				foreach($expense_items as $supplier_id => $rows){
					$supplier_info = $this->site->getCompanyByID($supplier_id);
					$grand_total = 0;
					$trucking_ids = null;
					foreach($rows as $expense_item){
						$grand_total += $expense_item['subtotal'];
						if($expense_item["trucking_ids"]){
							foreach(json_decode($expense_item["trucking_ids"]) as $trucking_id){
								$trucking_ids[$trucking_id] = $trucking_id;
							}
						}
					}
					$expenses[$supplier_id] = array(
													'date' => $date,
													'reference' => $reference_no,
													'biller_id' => $biller_id,
													'biller' => $biller_info->company,
													'supplier_id' => $supplier_id,
													'supplier' => $supplier_info->company,
													'amount' => $grand_total,
													'grand_total' => $grand_total,
													'paid' => 0,
													'payment_status' => "pending",
													'status' => "approved",
													'created_by' => $this->session->userdata('user_id'),
													'ap_account' => $biller_acc->ap_acc,
													'trucking_ids' => json_encode($trucking_ids)
												);
					$acc_trans[] = array(
										'transaction' => 'Clearance',
										'transaction_id' => $id,
										'transaction_date' => $date,
										'account' =>  $biller_acc->ap_acc,
										'amount' => $grand_total * (-1),
										'reference' => $reference_no,
										'narrative' => 'Clearance Expense',
										'description' => $booking_info->booking_no,
										'biller_id' => $biller_id,
										'user_id' => $this->session->userdata('user_id'),
										'supplier_id' => $supplier_id,
										'customer_id' => null
									);	
				}
			}

			if($this->Settings->accounting == 1){
				$acc_trans[] = array(
									'transaction' => 'Clearance',
									'transaction_id' => $id,
									'transaction_date' => $date,
									'account' =>  $biller_acc->ar_acc,
									'amount' => $total_income,
									'reference' => $reference_no,
									'narrative' => 'Clearance Income',
									'description' => $booking_info->booking_no,
									'biller_id' => $biller_id,
									'user_id' => $this->session->userdata('user_id'),
									'supplier_id' => null,
									'customer_id' => $customer_id
								);
			}
			
			if($_FILES['documents']['size'][0] > 0) {
				$this->load->library('my_upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->my_upload->initialize($config);
				if($this->my_upload->do_multi_upload("documents")) {
					$uploads = $this->my_upload->get_multi_upload_data('file_name');
					foreach($uploads as $upload){
						$attachment[] = $upload['file_name'];
					}
					$data['attachment'] = json_encode($attachment);
				}else{
					$error = $this->my_upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
			}
        }
        if ($this->form_validation->run() == true && $this->clearances_model->updateClearance($id,$data,$items,$expenses,$expense_items,$acc_trans)) {
            $this->session->set_userdata('remove_clr', 1);
            $this->session->set_flashdata('message', $this->lang->line("clearance_edited"));
			redirect('clearances');
        } else {
			$pr = false;
			$clearance_items = $this->clearances_model->getClearanceItems($id);
			if($clearance_items){
				$c = rand(100000, 9999999);
				foreach($clearance_items as $row){
					$pr[$c] = array('id' => $c, 'item_id' => $row->item_id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row);
					$c++;
				}
			}
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$suppliers = $this->site->getSuppliers();
			$this->data['clearance'] = $this->clearances_model->getClearanceByID($id);
			$this->data['clearance_items'] = json_encode($pr);
            $this->data['billers'] =  $this->site->getBillers();
			$this->data['customers'] = $this->clearances_model->getCustomers();
			$this->data['suppliers'] = $suppliers ? json_encode($suppliers) : false;
			$this->session->set_userdata('remove_clr', 1);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('edit_clearance')));
			$meta = array('page_title' => lang('edit_clearance'), 'bc' => $bc);
            $this->page_construct('clearances/edit_clearance', $meta, $this->data);
        }
    }
	
	public function delete_clearance($id = null)
    {
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if ($this->clearances_model->deleteClearance($id)) {
			echo $this->lang->line("clearance_deleted");
		} else {
			$this->session->set_flashdata('error', lang('clearance_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
    }
	
	public function clearance_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_clearance');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->clearances_model->deleteClearance($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('error', lang('clearance_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("clearance_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('clearances'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('booking_no'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('expense'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('income'));
					
					
					$this->db->select("
											clr_clearances.id as id,
											DATE_FORMAT(".$this->db->dbprefix('clr_clearances').".date, '%Y-%m-%d %T') as date,
											clr_clearances.reference_no,
											companies.company as customer,
											clr_bookings.booking_no,
											IFNULL(".$this->db->dbprefix('clr_clearances').".total_expense,0) as expense,
											IFNULL(".$this->db->dbprefix('clr_clearances').".total_income,0) as income,
											clr_clearances.attachment
										")
					->from("clr_clearances")
					->join("clr_bookings","clr_bookings.id = clr_clearances.booking_id","left")
					->join("companies","companies.id = clr_clearances.customer_id","left")
					->where_in("clr_clearances.id",$_POST['val']);
					$q = $this->db->get();
                    $row = 2;
                    if ($q->num_rows() > 0) {
						foreach (($q->result()) as $clearance) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($clearance->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $clearance->reference_no);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $clearance->customer);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $clearance->booking_no);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($clearance->expense));
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($clearance->income));
							$row++;
						}
					}
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'clearances_' . date('Y_m_d_H_i_s');
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
	
	
	public function lines()
    {
        $this->bpas->checkPermissions('lines');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'clearances', 'page' => lang('clearance')), array('link' => '#', 'page' => lang('lines')));
        $meta = array('page_title' => lang('lines'), 'bc' => $bc);
        $this->page_construct('clearances/lines', $meta, $this->data);
    }

    public function getLines()
    {
		$this->bpas->checkPermissions('lines');
        $this->load->library('datatables');
        $this->datatables
            ->select("clr_lines.id as id,
						clr_lines.name,
						clr_lines.note,
						clr_lines.attachment
					")
            ->from("clr_lines")
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_line") . "' href='" . site_url('clearances/edit_line/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_line") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('clearances/delete_line/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
	
	
	public function add_line()
    {
		$this->bpas->checkPermissions('lines', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
						'name' => $this->input->post('name'),
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
        } elseif ($this->input->post('add_line')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('clearances/lines');
        }
        if ($this->form_validation->run() == true && $id = $this->clearances_model->addLine($data)) {
            $this->session->set_flashdata('message', $this->lang->line("line_added"));
            redirect('clearances/lines');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'clearances/add_line', $this->data);
        }
    }
	
	public function edit_line($id = false)
    {
		$this->bpas->checkPermissions('lines', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
			$data = array(
						'name' => $this->input->post('name'),
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
        } elseif ($this->input->post('edit_line')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('clearances/lines');
        }
        if ($this->form_validation->run() == true && $id = $this->clearances_model->updateLine($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("line_edited"));
            redirect('clearances/lines');
        } else {
			$line = $this->clearances_model->getLineByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['line'] = $line;
            $this->load->view($this->theme . 'clearances/edit_line', $this->data);
        }
    }
	
	public function delete_line($id = NULL)
    {	
		$this->bpas->checkPermissions('lines', true);
		if ($this->clearances_model->deleteLine($id)) {
			echo $this->lang->line("line_deleted");
		} else {
			$this->session->set_flashdata('warning', lang('line_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
		
    }
	
	public function line_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('lines');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->clearances_model->deleteLine($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('line_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("line_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('lines'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('note'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
						$line = $this->clearances_model->getLineByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $line->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->remove_tag($line->note));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'lines_' . date('Y_m_d_H_i_s');
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
	
	
	public function vessels()
    {
        $this->bpas->checkPermissions('vessels');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'clearances', 'page' => lang('trucking')), array('link' => '#', 'page' => lang('vessels')));
        $meta = array('page_title' => lang('vessels'), 'bc' => $bc);
        $this->page_construct('clearances/vessels', $meta, $this->data);
    }

    public function getVessels()
    {
		$this->bpas->checkPermissions('vessels');
        $this->load->library('datatables');
        $this->datatables
            ->select("clr_vessels.id as id,
						clr_vessels.name,
						clr_vessels.note,
						clr_vessels.attachment
					")
            ->from("clr_vessels")
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_vessel") . "' href='" . site_url('clearances/edit_vessel/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_vessel") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('clearances/delete_vessel/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
	
	
	public function add_vessel()
    {
		$this->bpas->checkPermissions('vessels', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
						'name' => $this->input->post('name'),
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
        } elseif ($this->input->post('add_vessel')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('clearances/vessels');
        }
        if ($this->form_validation->run() == true && $id = $this->clearances_model->addVessel($data)) {
            $this->session->set_flashdata('message', $this->lang->line("vessel_added"));
            redirect('clearances/vessels');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'clearances/add_vessel', $this->data);
        }
    }
	
	public function edit_vessel($id = false)
    {
		$this->bpas->checkPermissions('vessels', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
			$data = array(
						'name' => $this->input->post('name'),
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
        } elseif ($this->input->post('edit_vessel')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('clearances/vessels');
        }
        if ($this->form_validation->run() == true && $id = $this->clearances_model->updateVessel($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("vessel_edited"));
            redirect('clearances/vessels');
        } else {
			$vessel = $this->clearances_model->getVesselByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['vessel'] = $vessel;
            $this->load->view($this->theme . 'clearances/edit_vessel', $this->data);
        }
    }
	
	public function delete_vessel($id = NULL)
    {	
		$this->bpas->checkPermissions('vessels', true);
		if ($this->clearances_model->deleteVessel($id)) {
			echo $this->lang->line("vessel_deleted");
		} else {
			$this->session->set_flashdata('warning', lang('vessel_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
		
    }
	
	public function vessel_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('vessels');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->clearances_model->deleteVessel($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('vessel_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("vessel_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('vessels'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('note'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
						$vessel = $this->clearances_model->getVesselByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $vessel->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->remove_tag($vessel->note));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'vessels_' . date('Y_m_d_H_i_s');
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
	
	
	
	public function ports()
    {
        $this->bpas->checkPermissions('ports');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'clearances', 'page' => lang('trucking')), array('link' => '#', 'page' => lang('ports')));
        $meta = array('page_title' => lang('ports'), 'bc' => $bc);
        $this->page_construct('clearances/ports', $meta, $this->data);
    }

    public function getPorts()
    {
		$this->bpas->checkPermissions('ports');
        $this->load->library('datatables');
        $this->datatables
            ->select("clr_ports.id as id,
						clr_ports.name,
						products.name as product_name,
						clr_ports.note,
						clr_ports.attachment
					")
            ->from("clr_ports")
			->join("products","products.id = clr_ports.product_id","left")
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_port") . "' href='" . site_url('clearances/edit_port/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_port") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('clearances/delete_port/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
	
	
	public function add_port()
    {
		$this->bpas->checkPermissions('ports', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
		$this->form_validation->set_rules('product', lang("product"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
						'name' => $this->input->post('name'),
						'product_id' => $this->input->post('product'),
						'plan' => $this->input->post('plan'),
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
        } elseif ($this->input->post('add_port')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('clearances/ports');
        }
        if ($this->form_validation->run() == true && $id = $this->clearances_model->addPort($data)) {
            $this->session->set_flashdata('message', $this->lang->line("port_added"));
            redirect('clearances/ports');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['products'] = $this->clearances_model->getProducts();
            $this->load->view($this->theme . 'clearances/add_port', $this->data);
        }
    }
	
	public function edit_port($id = false)
    {
		$this->bpas->checkPermissions('ports', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
		$this->form_validation->set_rules('product', lang("product"), 'required');
        if ($this->form_validation->run() == true) {
			$data = array(
						'name' => $this->input->post('name'),
						'product_id' => $this->input->post('product'),
						'plan' => $this->input->post('plan'),
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
        } elseif ($this->input->post('edit_port')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('clearances/ports');
        }
        if ($this->form_validation->run() == true && $id = $this->clearances_model->updatePort($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("port_edited"));
            redirect('clearances/ports');
        } else {
			$port = $this->clearances_model->getPortByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['port'] = $port;
			$this->data['products'] = $this->clearances_model->getProducts();
            $this->load->view($this->theme . 'clearances/edit_port', $this->data);
        }
    }
	
	public function delete_port($id = NULL)
    {	
		$this->bpas->checkPermissions('ports', true);
		if ($this->clearances_model->deletePort($id)) {
			echo $this->lang->line("port_deleted");
		} else {
			$this->session->set_flashdata('warning', lang('port_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
		
    }
	
	public function port_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('ports');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->clearances_model->deletePort($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('port_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("port_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('ports'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('product'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('note'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
						$port = $this->clearances_model->getPortByID($id);
						$product = $this->site->getProductByID($port->product_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $port->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $product->name);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->remove_tag($port->note));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'ports_' . date('Y_m_d_H_i_s');
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
	
	
	public function countries()
    {
        $this->bpas->checkPermissions('countries');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'clearances', 'page' => lang('trucking')), array('link' => '#', 'page' => lang('countries')));
        $meta = array('page_title' => lang('countries'), 'bc' => $bc);
        $this->page_construct('clearances/countries', $meta, $this->data);
    }

    public function getCountries()
    {
		$this->bpas->checkPermissions('countries');
        $this->load->library('datatables');
        $this->datatables
            ->select("clr_countries.id as id,
						clr_countries.name,
						clr_countries.note,
						clr_countries.attachment
					")
            ->from("clr_countries")
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_country") . "' href='" . site_url('clearances/edit_country/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_country") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('clearances/delete_country/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
	
	
	public function add_country()
    {
		$this->bpas->checkPermissions('countries', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
						'name' => $this->input->post('name'),
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
        } elseif ($this->input->post('add_country')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('clearances/countries');
        }
        if ($this->form_validation->run() == true && $id = $this->clearances_model->addCountry($data)) {
            $this->session->set_flashdata('message', $this->lang->line("country_added"));
            redirect('clearances/countries');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'clearances/add_country', $this->data);
        }
    }
	
	public function edit_country($id = false)
    {
		$this->bpas->checkPermissions('countries', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
			$data = array(
						'name' => $this->input->post('name'),
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
        } elseif ($this->input->post('edit_country')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('clearances/countries');
        }
        if ($this->form_validation->run() == true && $id = $this->clearances_model->updateCountry($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("country_edited"));
            redirect('clearances/countries');
        } else {
			$country = $this->clearances_model->getCountryByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['country'] = $country;
            $this->load->view($this->theme . 'clearances/edit_country', $this->data);
        }
    }
	
	public function delete_country($id = NULL)
    {	
		$this->bpas->checkPermissions('countries', true);
		if ($this->clearances_model->deleteCountry($id)) {
			echo $this->lang->line("country_deleted");
		} else {
			$this->session->set_flashdata('warning', lang('country_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
		
    }
	
	public function country_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('countries');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->clearances_model->deleteCountry($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('country_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("country_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('countries'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('note'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
						$country = $this->clearances_model->getCountryByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $country->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->remove_tag($country->note));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'countries_' . date('Y_m_d_H_i_s');
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
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'clearances', 'page' => lang('clearance')), array('link' => '#', 'page' => lang('dry_ports')));
        $meta = array('page_title' => lang('dry_ports'), 'bc' => $bc);
        $this->page_construct('clearances/dry_ports', $meta, $this->data);
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
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_dry_port") . "' href='" . site_url('clearances/edit_dry_port/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_dry_port") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('clearances/delete_dry_port/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
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
            redirect('clearances/dry_ports');
        }
        if ($this->form_validation->run() == true && $id = $this->clearances_model->addDryPort($data)) {
            $this->session->set_flashdata('message', $this->lang->line("dry_port_added"));
            redirect('clearances/dry_ports');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['containers'] = $this->clearances_model->getContainers();
            $this->load->view($this->theme . 'clearances/add_dry_port', $this->data);
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
            redirect('clearances/dry_ports');
        }
        if ($this->form_validation->run() == true && $id = $this->clearances_model->updateDryPort($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("dry_port_edited"));
            redirect('clearances/dry_ports');
        } else {
			$dry_port = $this->clearances_model->getDryPortByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['dry_port'] = $dry_port;
			$this->data['containers'] = $this->clearances_model->getContainers();
            $this->load->view($this->theme . 'clearances/edit_dry_port', $this->data);
        }
    }
	
	public function delete_dry_port($id = NULL)
    {	
		$this->bpas->checkPermissions('dry_ports', true);
		if ($this->clearances_model->deleteDryPort($id)) {
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
                        if (!$this->clearances_model->deleteDryPort($id)) {
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
						$dry_port = $this->clearances_model->getDryPortByID($id);
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
	
	public function bookings()
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('bookings')));
        $meta = array('page_title' => lang('bookings'), 'bc' => $bc);
        $this->page_construct('clearances/bookings', $meta, $this->data);
    }
	
	
	public function getBookings()
    {
		$this->bpas->checkPermissions('bookings');
		$edit_link = anchor('clearances/edit_booking/$1', '<i class="fa fa-edit"></i> ' . lang('edit_booking'), ' class="edit_booking"');
		$delete_link = "<a href='#' class='delete_booking po' title='<b>" . $this->lang->line("delete_booking") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('clearances/delete_booking/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_booking') . "</a>";	
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
           
        $this->load->library('datatables');
        $this->datatables ->select("
								clr_bookings.id as id,
								DATE_FORMAT(".$this->db->dbprefix('clr_bookings').".date, '%Y-%m-%d %T') as date,
								companies.company as customer,
								clr_bookings.booking_no,
								tru_dry_ports.name as dry_port,
								clr_lines.name as line,
								clr_vessels.name as vessel,
								clr_ports.name as port,
								clr_countries.name as country,
								SUM(IFNULL(".$this->db->dbprefix("clr_booking_items").".quantity,0)) as quantity,
								clr_countries.note,
								clr_bookings.status,
								clr_bookings.clearance_status,
								clr_bookings.attachment
							")
		->from("clr_bookings")
		->join("clr_booking_items","clr_booking_items.booking_id = clr_bookings.id","inner")
		->join("companies","companies.id = clr_bookings.customer_id","left")
		->join("clr_vessels","clr_vessels.id = clr_bookings.vessel_id","left")
		->join("clr_ports","clr_ports.id = clr_bookings.port_id","left")
		->join("clr_countries","clr_countries.id = clr_bookings.country_id","left")
		->join("clr_lines","clr_lines.id = clr_bookings.line_id","left")
		->join("tru_dry_ports","tru_dry_ports.id = clr_bookings.dry_port_id","left")
		->group_by("clr_bookings.id");
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
    }
	
	
	public function add_booking()
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('booking_no', $this->lang->line("booking_no"), 'required');
		$this->form_validation->set_rules('port', $this->lang->line("port"), 'required');
		if ($this->form_validation->run() == true) {
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
			$customer_id = $this->input->post('customer');
			$booking_no = $this->input->post('booking_no');
			$port_id = $this->input->post('port');
			$dry_port_id = $this->input->post('dry_port') ? $this->input->post('dry_port') : null;
			$line_id = $this->input->post('line') ? $this->input->post('line') : null;
			$vessel_id = $this->input->post('vessel') ? $this->input->post('vessel') : null;
			$country_id = $this->input->post('country') ? $this->input->post('country') : null;
			$note = $this->input->post('note');
			$total_qty = 0;
			$i = isset($_POST['container_size']) ? sizeof($_POST['container_size']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $container_size_id = $_POST['container_size'][$r];
                $quantity = $_POST['quantity'][$r];
                $description = $_POST['description'][$r];
				$items[] = array(
					'container_size_id' => $container_size_id,
					'quantity' => $quantity,
					'description' => $description,
				);
				$total_qty += $quantity;
            }
			
            if (empty($items)) {
                $this->form_validation->set_rules('product', lang("container_size"), 'required');
            } else {
                krsort($items);
            }
			
			if($total_qty > 0){
				$status = "pending";
			}else{
				$status = "completed";
			}
			
            $data = array(
				'date' => $date,
				'biller_id' => $biller_id,
                'customer_id' => $customer_id,
                'booking_no' => $booking_no,
				'dry_port_id' => $dry_port_id,
                'line_id' => $line_id,
				'vessel_id' => $vessel_id,
				'port_id' => $port_id,
				'country_id' => $country_id,
				'note' => $note,
				'status' => $status,
                'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s'),
            );
			

			if($_FILES['documents']['size'][0] > 0) {
				$this->load->library('my_upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->my_upload->initialize($config);
				if($this->my_upload->do_multi_upload("documents")) {
					$uploads = $this->my_upload->get_multi_upload_data('file_name');
					foreach($uploads as $upload){
						$attachment[] = $upload['file_name'];
					}
					$data['attachment'] = json_encode($attachment);
				}else{
					$error = $this->my_upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
			}
        }
		
        if ($this->form_validation->run() == true && $this->clearances_model->addBooking($data,$items)) {
            $this->session->set_userdata('remove_bkr', 1);
            $this->session->set_flashdata('message', $this->lang->line("booking_added"));
			if($this->input->post('add_booking_next')){
				redirect('clearances/add_booking');
			}else{
				redirect('clearances/bookings');
			}
        } else {
			$pr = false;
			$container_sizes = $this->clearances_model->getContainerSizes();
			if($container_sizes){
				krsort($container_sizes);
				$c = rand(100000, 9999999);
				foreach($container_sizes as $container_size){
					$container_size->quantity = "";
					$container_size->description = "";
					$container_size->item_id = $container_size->id;
					$container_size->id = $c;
					$pr[$c] = $container_size;
					$c++;
				}
			}
			
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['container_sizes'] = json_encode($pr);
			$this->data['billers'] =  $this->site->getBillers();
			$this->data['customers'] = $this->clearances_model->getCustomers();
			$this->data['lines'] = $this->clearances_model->getLines();
            $this->data['vessels'] = $this->clearances_model->getVessels();
			$this->data['ports'] = $this->clearances_model->getPorts();
			$this->data['countries'] = $this->clearances_model->getCountries();
			$this->data['dry_ports'] = $this->clearances_model->getDryPorts();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')),array('link' => site_url('clearances/bookings'), 'page' => lang('booking')), array('link' => '#', 'page' => lang('add_booking')));
			$meta = array('page_title' => lang('add_booking'), 'bc' => $bc);
            $this->page_construct('clearances/add_booking', $meta, $this->data);
        }
    }
	
	
	public function edit_booking($id = false)
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('booking_no', $this->lang->line("booking_no"), 'required');
		$this->form_validation->set_rules('port', $this->lang->line("port"), 'required');
		if ($this->form_validation->run() == true) {
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
			$customer_id = $this->input->post('customer');
			$booking_no = $this->input->post('booking_no');
			$port_id = $this->input->post('port');
			$dry_port_id = $this->input->post('dry_port') ? $this->input->post('dry_port') : null;
			$line_id = $this->input->post('line') ? $this->input->post('line') : null;
			$vessel_id = $this->input->post('vessel') ? $this->input->post('vessel') : null;
			$country_id = $this->input->post('country') ? $this->input->post('country') : null;
			$note = $this->input->post('note');
			$i = isset($_POST['container_size']) ? sizeof($_POST['container_size']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $container_size_id = $_POST['container_size'][$r];
                $quantity = $_POST['quantity'][$r];
                $description = $_POST['description'][$r];
				$trucking_qty = $this->clearances_model->getTruckingQtyByContainerSize($id,$container_size_id);
				if($trucking_qty && $trucking_qty->trucking_qty > $quantity){
					$this->session->set_flashdata('error', lang('trucking_qty_more_than_booking_qty'));
					redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
				}
				$items[] = array(
					'booking_id' => $id,
					'container_size_id' => $container_size_id,
					'quantity' => $quantity,
					'description' => $description,
				);
            }
            if (empty($items)) {
                $this->form_validation->set_rules('product', lang("container_size"), 'required');
            } else {
                krsort($items);
            }
			
            $data = array(
				'date' => $date,
				'biller_id' => $biller_id,
                'customer_id' => $customer_id,
                'booking_no' => $booking_no,
				'dry_port_id' => $dry_port_id,
                'line_id' => $line_id,
				'vessel_id' => $vessel_id,
				'port_id' => $port_id,
				'country_id' => $country_id,
				'note' => $note,
                'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
            );
			
			
			if($_FILES['documents']['size'][0] > 0) {
				$this->load->library('my_upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->my_upload->initialize($config);
				if($this->my_upload->do_multi_upload("documents")) {
					$uploads = $this->my_upload->get_multi_upload_data('file_name');
					foreach($uploads as $upload){
						$attachment[] = $upload['file_name'];
					}
					$data['attachment'] = json_encode($attachment);
				}else{
					$error = $this->my_upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
			}
        }
        if ($this->form_validation->run() == true && $this->clearances_model->updateBooking($id,$data,$items)) {
            $this->session->set_userdata('remove_bkr', 1);
            $this->session->set_flashdata('message', $this->lang->line("booking_edited"));
			redirect('clearances/bookings');
        } else {
			$booking = $this->clearances_model->getBookingByID($id);
			if($booking->clearance_status == "completed" && !$this->Owner && !$this->Admin){	
				$this->session->set_flashdata('error', lang('booking_cannot_edit'));
				redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
			}
			$pr = false;
			$container_sizes = $this->clearances_model->getContainerSizes($id);
			if($container_sizes){
				krsort($container_sizes);
				$c = rand(100000, 9999999);
				foreach($container_sizes as $container_size){
					$container_size->item_id = $container_size->id;
					$container_size->id = $c;
					$pr[$c] = $container_size;
					$c++;
				}
			}
			
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['booking'] = $booking;
            $this->data['billers'] =  $this->site->getBillers();
			$this->data['customers'] = $this->clearances_model->getCustomers();
			$this->data['container_sizes'] = json_encode($pr);
			$this->data['lines'] = $this->clearances_model->getLines();
            $this->data['vessels'] = $this->clearances_model->getVessels();
			$this->data['ports'] = $this->clearances_model->getPorts();
			$this->data['countries'] = $this->clearances_model->getCountries();
			$this->data['dry_ports'] = $this->clearances_model->getDryPorts();
			$this->session->set_userdata('remove_bkr', 1);
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')),array('link' => site_url('clearances/bookings'), 'page' => lang('booking')), array('link' => '#', 'page' => lang('edit_booking')));
			$meta = array('page_title' => lang('edit_booking'), 'bc' => $bc);
            $this->page_construct('clearances/edit_booking', $meta, $this->data);
        }
    }
	
	public function delete_booking($id = null)
    {
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if ($this->clearances_model->deleteBooking($id)) {
			echo $this->lang->line("booking_deleted");
		} else {
			$this->session->set_flashdata('error', lang('booking_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
    }
	
	public function booking_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->bpas->checkPermissions('delete_booking');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
						if (!$this->clearances_model->deleteBooking($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('error', lang('booking_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("booking_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('bookings'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('booking_no'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('dry_port'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('line'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('vessel'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('port'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('country'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('quantity'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('tk_status'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('cl_status'));
					$this->db->select("
										clr_bookings.id as id,
										DATE_FORMAT(".$this->db->dbprefix('clr_bookings').".date, '%Y-%m-%d %T') as date,
										companies.company as customer,
										clr_bookings.booking_no,
										tru_dry_ports.name as dry_port,
										clr_lines.name as line,
										clr_vessels.name as vessel,
										clr_ports.name as port,
										clr_countries.name as country,
										SUM(IFNULL(".$this->db->dbprefix("clr_booking_items").".quantity,0)) as quantity,
										clr_countries.note,
										clr_bookings.status,
										clr_bookings.clearance_status,
										clr_bookings.attachment
									")
					->from("clr_bookings")
					->join("clr_booking_items","clr_booking_items.booking_id = clr_bookings.id","inner")
					->join("companies","companies.id = clr_bookings.customer_id","left")
					->join("clr_vessels","clr_vessels.id = clr_bookings.vessel_id","left")
					->join("clr_ports","clr_ports.id = clr_bookings.port_id","left")
					->join("clr_countries","clr_countries.id = clr_bookings.country_id","left")
					->join("clr_lines","clr_lines.id = clr_bookings.line_id","left")
					->join("tru_dry_ports","tru_dry_ports.id = clr_bookings.dry_port_id","left")
					->group_by("clr_bookings.id")
					->where_in("clr_bookings.id",$_POST['val']);
					$q = $this->db->get();
                    $row = 2;
                    if ($q->num_rows() > 0) {
						foreach (($q->result()) as $booking) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($booking->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $booking->customer);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $booking->booking_no);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $booking->dry_port);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $booking->line);
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $booking->vessel);
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $booking->port);
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $booking->country);
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($booking->quantity));
							$this->excel->getActiveSheet()->SetCellValue('J' . $row, lang($booking->status));
							$this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($booking->clearance_status));
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

					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'bookings_' . date('Y_m_d_H_i_s');
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
	
	public function get_avaliable_booking_by_customer(){
		$customer_id = $this->input->get('customer_id');
		$booking_id  = $this->input->get('booking_id') ? $this->input->get('booking_id') : false;
		$bookings = $this->clearances_model->getAvaliableBookingsByCustomer($customer_id, $booking_id);
		echo json_encode($bookings);
	}
	
	public function get_avaliable_container_size_by_booking(){
		$booking_id = $this->input->get('booking_id');
		$container_size_id  = $this->input->get('container_size_id') ? $this->input->get('container_size_id') : false;
		$data["container_sizes"] = $this->clearances_model->getAvaliableContainerSizeByBooking($booking_id,$container_size_id);
		$data["trucking_fees"] = $this->clearances_model->getTruckingFeeByBooking($booking_id);
		echo json_encode($data);
	}
	
	public function get_supplier_truck(){
		$supplier_id = $this->input->get('supplier_id');
		$trucks = $this->clearances_model->getSupplierTrucks($supplier_id);
		echo json_encode($trucks);
	}
	
	public function truckings()
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('truckings')));
        $meta = array('page_title' => lang('truckings'), 'bc' => $bc);
        $this->page_construct('clearances/truckings', $meta, $this->data);
    }
	
	
	public function getTruckings()
    {
		$this->bpas->checkPermissions('truckings');
		$payments_link = "";
		$add_payment_link = "";
		if ($this->Owner || $this->Admin || $this->GP['expense_payments']) {
			$payments_link = anchor('clearances/trucking_payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), ' class="trucking_payments" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		}
		if ($this->Owner || $this->Admin || $this->GP['add_expense_payment']) {
			$add_payment_link = anchor('clearances/add_trucking_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'class="trucking_payments" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		}
		$edit_link = anchor('clearances/edit_trucking/$1', '<i class="fa fa-edit"></i> ' . lang('edit_trucking'), ' class="edit_trucking"');
		$delete_link = "<a href='#' class='delete_trucking po' title='<b>" . $this->lang->line("delete_trucking") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('clearances/delete_trucking/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_trucking') . "</a>";	
		
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
        $this->datatables ->select("
									clr_truckings.id as id,
									DATE_FORMAT(".$this->db->dbprefix('clr_truckings').".date, '%Y-%m-%d %T') as date,
									companies.company as customer,
									clr_bookings.booking_no,
									tru_containers.name as container_size,
									suppliers.company as supplier,
									supplier_trucks.plate_number,
									clr_truckings.container_no,
									clr_truckings.carrier_seal,
									clr_truckings.custom_seal,
									IFNULL(".$this->db->dbprefix('clr_truckings').".total_amount,0) as total,
									IFNULL(".$this->db->dbprefix('clr_truckings').".paid,0) as paid,
									IFNULL(".$this->db->dbprefix('clr_truckings').".booking_fee,0) as booking_fee,
									IFNULL(".$this->db->dbprefix('clr_truckings').".extra_loading,0) as extra_loading,
									IFNULL(".$this->db->dbprefix('clr_truckings').".extra_lock,0) as extra_lock,
									IFNULL(".$this->db->dbprefix('clr_truckings').".extra_document,0) as extra_document,
									clr_truckings.note,
									clr_truckings.stand_by_day,
									clr_truckings.lock_date,
									clr_truckings.status,
									clr_truckings.payment_status,
									clr_truckings.attachment
								")
            ->from("clr_truckings")
			->join("clr_bookings","clr_bookings.id = clr_truckings.booking_id","left")
			->join("tru_containers","tru_containers.id = clr_truckings.container_size_id","left")
			->join("companies as suppliers","suppliers.id = clr_truckings.supplier_id","left")
			->join("supplier_trucks","supplier_trucks.id = clr_truckings.truck_id","left")
			->join("companies","companies.id = clr_truckings.customer_id","left");
		$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
    }
	
	
	public function add_trucking()
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('booking_no', $this->lang->line("booking_no"), 'required');
		$this->form_validation->set_rules('container_size', $this->lang->line("container_size"), 'required');
		$this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');

		if ($this->form_validation->run() == true) {
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
			$customer_id = $this->input->post('customer');
			$booking_id = $this->input->post('booking_no');
			$container_size_id = $this->input->post('container_size');
			$dry_port_id = $this->input->post('dry_port');
			$supplier_id = $this->input->post('supplier');
			$truck_id = $this->input->post('truck_no');
			$container_no = $this->input->post('container_no');
			$carrier_seal = $this->input->post('carrier_seal');
			$custom_seal = $this->input->post('custom_seal');
			$note = $this->input->post('note');
			$trucking_fee = $this->input->post('trucking_fee');
			$lolo_fee = $this->input->post('lolo_fee');
			$extra_fee = $this->input->post('extra_fee');
			$stand_by_fee = $this->input->post('stand_by_fee');
			$booking_fee = $this->input->post('booking_fee');
			$stand_by_day = $this->input->post('stand_by_day');
			$locked = $this->input->post('locked');
			$total_amount = $trucking_fee + $lolo_fee + $extra_fee + ($stand_by_fee * $stand_by_day);
			
			if($locked == "yes" && $container_no && $carrier_seal && $custom_seal){
				$status = "locked";
			}else{
				if($container_no){
					$status = "picked_up";
				}else{
					$status = "pending";
				}
			}

            $data = array(
				'date' => $date,
				'biller_id' => $biller_id,
                'customer_id' => $customer_id,
                'booking_id' => $booking_id,
                'container_size_id' => $container_size_id,
				'dry_port_id' => $dry_port_id,
				'supplier_id' => $supplier_id,
				'truck_id' => $truck_id,
				'container_no' => $container_no,
				'carrier_seal' => $carrier_seal,
				'custom_seal' => $custom_seal,
				'trucking_fee' => $trucking_fee,
				'lolo_fee' => $lolo_fee,
				'extra_fee' => $extra_fee,
				'stand_by_fee' => $stand_by_fee,
				'stand_by_day' => $stand_by_day,
				'total_amount' => $total_amount,
				'booking_fee' => $booking_fee,
				'note' => $note,
				'status' => $status,
                'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s'),
            );
			
			if($_FILES['documents']['size'][0] > 0) {
				$this->load->library('my_upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->my_upload->initialize($config);
				if($this->my_upload->do_multi_upload("documents")) {
					$uploads = $this->my_upload->get_multi_upload_data('file_name');
					foreach($uploads as $upload){
						$attachment[] = $upload['file_name'];
					}
					$data['attachment'] = json_encode($attachment);
				}else{
					$error = $this->my_upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
			}
        }
        if ($this->form_validation->run() == true && $this->clearances_model->addTrucking($data)) {
            $this->session->set_userdata('remove_clrt', 1);
            $this->session->set_flashdata('message', $this->lang->line("trucking_added"));
			if($this->input->post('add_trucking_next')){
				redirect('clearances/add_trucking');
			}else{
				redirect('clearances/truckings');
			}
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] =  $this->site->getBillers();
			$this->data['customers'] = $this->clearances_model->getAvaliableBookingCustomers();
			$this->data['suppliers'] = $this->clearances_model->getTruckingSuppliers();
			$this->data['dry_ports'] = $this->clearances_model->getDryPorts();
			$this->data['trucks'] = $this->clearances_model->getSupplierTrucks();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => site_url('clearances/truckings'), 'page' => lang('trucking')), array('link' => '#', 'page' => lang('add_trucking')));
			$meta = array('page_title' => lang('add_trucking'), 'bc' => $bc);
            $this->page_construct('clearances/add_trucking', $meta, $this->data);
        }
    }
	
	
	public function edit_trucking($id = false)
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('booking_no', $this->lang->line("booking_no"), 'required');
		$this->form_validation->set_rules('container_size', $this->lang->line("container_size"), 'required');
		$this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');

		if ($this->form_validation->run() == true) {
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
			$customer_id = $this->input->post('customer');
			$booking_id = $this->input->post('booking_no');
			$container_size_id = $this->input->post('container_size');
			$dry_port_id = $this->input->post('dry_port');
			$supplier_id = $this->input->post('supplier');
			$truck_id = $this->input->post('truck_no');
			$container_no = $this->input->post('container_no');
			$carrier_seal = $this->input->post('carrier_seal');
			$custom_seal = $this->input->post('custom_seal');
			$note = $this->input->post('note');
			$trucking_fee = $this->input->post('trucking_fee');
			$lolo_fee = $this->input->post('lolo_fee');
			$extra_fee = $this->input->post('extra_fee');
			$stand_by_fee = $this->input->post('stand_by_fee');
			$booking_fee = $this->input->post('booking_fee');
			$stand_by_day = $this->input->post('stand_by_day');
			$total_amount = $trucking_fee + $lolo_fee + $extra_fee + ($stand_by_fee * $stand_by_day);
            
			$status = $this->input->post('status');
			if($status=="pending" && $container_no){
				$status = "picked_up";
			}else if(!$container_no && $status!="cancel"){
				$status = "pending";
			}
			
			$data = array(
				'date' => $date,
				'biller_id' => $biller_id,
                'customer_id' => $customer_id,
                'booking_id' => $booking_id,
                'container_size_id' => $container_size_id,
				'dry_port_id' => $dry_port_id,
				'supplier_id' => $supplier_id,
				'truck_id' => $truck_id,
				'container_no' => $container_no,
				'carrier_seal' => $carrier_seal,
				'custom_seal' => $custom_seal,
				'trucking_fee' => $trucking_fee,
				'lolo_fee' => $lolo_fee,
				'extra_fee' => $extra_fee,
				'stand_by_fee' => $stand_by_fee,
				'stand_by_day' => $stand_by_day,
				'total_amount' => $total_amount,
				'booking_fee' => $booking_fee,
				'status' => $status,
				'note' => $note,
                'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
            );
			
			if($_FILES['documents']['size'][0] > 0) {
				$this->load->library('my_upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->my_upload->initialize($config);
				if($this->my_upload->do_multi_upload("documents")) {
					$uploads = $this->my_upload->get_multi_upload_data('file_name');
					foreach($uploads as $upload){
						$attachment[] = $upload['file_name'];
					}
					$data['attachment'] = json_encode($attachment);
				}else{
					$error = $this->my_upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
			}
        }
        if ($this->form_validation->run() == true && $this->clearances_model->updateTrucking($id,$data)) {
            $this->session->set_userdata('remove_clrt', 1);
            $this->session->set_flashdata('message', $this->lang->line("trucking_edited"));
			redirect('clearances/truckings');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$trucking = $this->clearances_model->getTruckingByID($id);
			$booking = $this->clearances_model->getBookingByID($trucking->booking_id);
			if($booking->clearance_status == "completed" && !$this->Owner && !$this->Admin){
				$this->session->set_flashdata('error', lang('trucking_cannot_edit'));
				redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
			}
            $this->data['billers'] =  $this->site->getBillers();
			$this->data['customers'] = $this->clearances_model->getAvaliableBookingCustomers($trucking->customer_id);
			$this->data['suppliers'] = $this->clearances_model->getTruckingSuppliers();
			$this->data['trucking'] = $trucking;
			$this->data['dry_ports'] = $this->clearances_model->getDryPorts();
			$this->data['trucks'] = $this->clearances_model->getSupplierTrucks();
			$this->session->set_userdata('remove_clrt', 1);
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => site_url('clearances/truckings'), 'page' => lang('trucking')), array('link' => '#', 'page' => lang('edit_trucking')));
			$meta = array('page_title' => lang('edit_trucking'), 'bc' => $bc);
            $this->page_construct('clearances/edit_trucking', $meta, $this->data);
        }
    }
	
	public function delete_trucking($id = null)
    {
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if ($this->clearances_model->deleteTrucking($id)) {
			echo $this->lang->line("trucking_deleted");
		} else {
			$this->session->set_flashdata('error', lang('trucking_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
    }
	
	public function update_trucking_status($id = false)
    {
		$this->bpas->checkPermissions(false, true);
		$trucking = $this->clearances_model->getTruckingByID($id);
        $this->form_validation->set_rules('trucking', lang("trucking"), 'required');
		$status = $this->input->post('status');
		if($status=="locked"){
			$this->form_validation->set_rules('lock_date', lang("lock_date"), 'required');
			$lock_date = $this->bpas->fsd($this->input->post('lock_date'));
		}else{
			$lock_date = null;
		}
        if ($this->form_validation->run() == true) {
			$container_no = $this->input->post('container_no');
			$carrier_seal = $this->input->post('carrier_seal');
			$custom_seal = $this->input->post('custom_seal');
			$stand_by_day = $this->input->post('stand_by_day');
			$extra_loading = $this->input->post('extra_loading');
            $extra_lock = $this->input->post('extra_lock');
			$extra_document = $this->input->post('extra_document');
			if($status=="pending" && $container_no){
				$status = "picked_up";
			}else if(!$container_no && $status!="cancel"){
				$status = "pending";
			}
			$note = $this->input->post('note');
			$data = array(
							"booking_id"=>$trucking->booking_id,
							"container_no"=>$container_no,
							"carrier_seal"=>$carrier_seal,
							"custom_seal"=>$custom_seal,
							"stand_by_day"=>$stand_by_day,
							"extra_loading"=>$extra_loading,
							"extra_lock"=>$extra_lock,
							"extra_document"=>$extra_document,
							"status"=>$status,
							"note"=>$note,
							"lock_date"=>$lock_date,
							"status_by" => $this->session->userdata('user_id'),
							"status_at" => date('Y-m-d H:i:s'),
						);
			if($lock_date && $lock_date < $trucking->date){
				$data["date"] = $lock_date;
			}	
			
			if($_FILES['documents']['size'][0] > 0) {
				$attachment = $trucking->attachment ? json_decode($trucking->attachment) : false;
				$this->load->library('my_upload');
				$config['upload_path'] = $this->digital_upload_path;
				$config['allowed_types'] = $this->digital_file_types;
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = false;
				$config['encrypt_name'] = true;
				$this->my_upload->initialize($config);
				if($this->my_upload->do_multi_upload("documents")) {
					$uploads = $this->my_upload->get_multi_upload_data('file_name');
					foreach($uploads as $upload){
						$attachments[] = $upload['file_name'];
					}
					if($attachment){
						$attachments = array_merge($attachment, $attachments);
					}
					$data['attachment'] = json_encode($attachments);
				}else{
					$error = $this->my_upload->display_errors();
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}
			}
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        }
        if ($this->form_validation->run() == true && $this->clearances_model->updateTruckingStatus($id, $data)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        } else {
            $booking = $this->clearances_model->getBookingByID($trucking->booking_id);
			if($booking->clearance_status == "completed" && !$this->Owner && !$this->Admin){	
				$this->session->set_flashdata('error', lang('trucking_cannot_edit'));
				die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
			}
			$this->data['trucking'] = $trucking;
			$this->data['customer'] = $this->site->getCompanyByID($trucking->customer_id);
			$this->data['booking'] = $this->clearances_model->getBookingByID($trucking->booking_id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'clearances/update_trucking_status', $this->data);
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
                        if (!$this->clearances_model->deleteTrucking($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('error', lang('trucking_cannot_delete'));
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
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('booking_no'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('container_size'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('supplier'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('truck_no'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('container_no'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('carrier_seal'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('custom_seal'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('total'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('L1', lang('booking'));
					$this->excel->getActiveSheet()->SetCellValue('M1', lang('extra_loading'));
					$this->excel->getActiveSheet()->SetCellValue('N1', lang('extra_lock'));
					$this->excel->getActiveSheet()->SetCellValue('O1', lang('extra_document'));
					$this->excel->getActiveSheet()->SetCellValue('P1', lang('note'));
					$this->excel->getActiveSheet()->SetCellValue('Q1', lang('stand_by'));
					$this->excel->getActiveSheet()->SetCellValue('R1', lang('lock_date'));
					$this->excel->getActiveSheet()->SetCellValue('S1', lang('status'));
					$this->excel->getActiveSheet()->SetCellValue('T1', lang('payment_status'));
					$this->db->select("
										clr_truckings.id as id,
										DATE_FORMAT(".$this->db->dbprefix('clr_truckings').".date, '%Y-%m-%d %T') as date,
										companies.company as customer,
										clr_bookings.booking_no,
										tru_containers.name as container_size,
										suppliers.company as supplier,
										supplier_trucks.plate_number,
										clr_truckings.container_no,
										clr_truckings.carrier_seal,
										clr_truckings.custom_seal,
										IFNULL(".$this->db->dbprefix('clr_truckings').".total_amount,0) as total,
										IFNULL(".$this->db->dbprefix('clr_truckings').".paid,0) as paid,
										IFNULL(".$this->db->dbprefix('clr_truckings').".booking_fee,0) as booking_fee,
										IFNULL(".$this->db->dbprefix('clr_truckings').".extra_loading,0) as extra_loading,
										IFNULL(".$this->db->dbprefix('clr_truckings').".extra_lock,0) as extra_lock,
										IFNULL(".$this->db->dbprefix('clr_truckings').".extra_document,0) as extra_document,
										clr_truckings.note,
										clr_truckings.stand_by_day,
										clr_truckings.lock_date,
										clr_truckings.status,
										clr_truckings.payment_status
									")
					->from("clr_truckings")
					->join("clr_bookings","clr_bookings.id = clr_truckings.booking_id","left")
					->join("tru_containers","tru_containers.id = clr_truckings.container_size_id","left")
					->join("companies as suppliers","suppliers.id = clr_truckings.supplier_id","left")
					->join("supplier_trucks","supplier_trucks.id = clr_truckings.truck_id","left")
					->join("companies","companies.id = clr_truckings.customer_id","left")
					->where_in("clr_truckings.id",$_POST['val']);
					$q = $this->db->get();
                    $row = 2;
                    if ($q->num_rows() > 0) {
						foreach (($q->result()) as $trucking) {
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($trucking->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $trucking->customer);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $trucking->booking_no);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $trucking->container_size);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $trucking->supplier);
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $trucking->plate_number);
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $trucking->container_no);
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $trucking->carrier_seal);
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, $trucking->custom_seal);
							$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($trucking->total));
							$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($trucking->paid));
							$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($trucking->booking_fee));
							$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->formatDecimal($trucking->extra_loading));
							$this->excel->getActiveSheet()->SetCellValue('N' . $row, $this->bpas->formatDecimal($trucking->extra_lock));
							$this->excel->getActiveSheet()->SetCellValue('O' . $row, $this->bpas->formatDecimal($trucking->extra_document));
							$this->excel->getActiveSheet()->SetCellValue('P' . $row, $this->bpas->decode_html($trucking->note));
							$this->excel->getActiveSheet()->SetCellValue('Q' . $row, $trucking->stand_by_day);
							$this->excel->getActiveSheet()->SetCellValue('R' . $row, $trucking->lock_date ? $this->bpas->hrsd($trucking->lock_date) : "");
							$this->excel->getActiveSheet()->SetCellValue('S' . $row, lang($trucking->status));
							$this->excel->getActiveSheet()->SetCellValue('T' . $row, lang($trucking->payment_status));
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
					$this->excel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('T')->setWidth(20);

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
	
	public function booking_report()
	{
		$this->bpas->checkPermissions();
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['customers'] = $this->clearances_model->getCustomers();
		$this->data['lines'] = $this->clearances_model->getLines();
		$this->data['vessels'] = $this->clearances_model->getVessels();
		$this->data['ports'] = $this->clearances_model->getPorts();
		$this->data['countries'] = $this->clearances_model->getCountries();
		$this->data['dry_ports'] = $this->clearances_model->getDryPorts();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('booking_report')));
        $meta = array('page_title' => lang('booking_report'), 'bc' => $bc);
        $this->page_construct('clearances/booking_report', $meta, $this->data);
	}
	
	public function getBookingReport($xls = null)
	{
		$this->bpas->checkPermissions('booking_report', TRUE);
		$biller_id = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$customer_id = $this->input->get('customer') ? $this->input->get('customer') : NULL;
		$dry_port_id = $this->input->get('dry_port') ? $this->input->get('dry_port') : NULL;
		$line_id = $this->input->get('line') ? $this->input->get('line') : NULL;
		$vessel_id = $this->input->get('vessel') ? $this->input->get('vessel') : NULL;
		$port_id = $this->input->get('port') ? $this->input->get('port') : NULL;
		$country_id = $this->input->get('country') ? $this->input->get('country') : NULL;
		$status = $this->input->get('status') ? $this->input->get('status') : NULL;
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date')) : NULL;
		if ($xls) {
			$this->db->select("
								DATE_FORMAT(".$this->db->dbprefix('clr_bookings').".date, '%Y-%m-%d %T') as date,
								companies.company as customer,
								clr_bookings.booking_no,
								tru_dry_ports.name as dry_port,
								clr_lines.name as line,
								clr_vessels.name as vessel,
								clr_ports.name as port,
								clr_countries.name as country,
								SUM(IFNULL(".$this->db->dbprefix("clr_booking_items").".quantity,0)) as quantity,
								clr_countries.note,
								clr_bookings.status,
								clr_bookings.clearance_status,
								clr_bookings.id as id
							")
			->from("clr_bookings")
			->join("clr_booking_items","clr_booking_items.booking_id = clr_bookings.id","inner")
			->join("companies","companies.id = clr_bookings.customer_id","left")
			->join("clr_vessels","clr_vessels.id = clr_bookings.vessel_id","left")
			->join("clr_ports","clr_ports.id = clr_bookings.port_id","left")
			->join("clr_countries","clr_countries.id = clr_bookings.country_id","left")
			->join("clr_lines","clr_lines.id = clr_bookings.line_id","left")
			->join("tru_dry_ports","tru_dry_ports.id = clr_bookings.dry_port_id","left")
			->group_by("clr_bookings.id");

			if ($biller_id) {
				$this->db->where('clr_bookings.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->db->where('clr_bookings.customer_id', $customer_id);
			}
			if ($dry_port_id) {
				$this->db->where('clr_bookings.dry_port_id', $dry_port_id);
			}
			if ($line_id) {
				$this->db->where('clr_bookings.line_id', $line_id);
			}
			if ($vessel_id) {
				$this->db->where('clr_bookings.vessel_id', $vessel_id);
			}
			if ($port_id) {
				$this->db->where('clr_bookings.port_id', $port_id);
			}
			if ($status) {
				$this->db->where('clr_bookings.status', $status);
			}
			if ($start_date) {
				$this->db->where('clr_bookings.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('clr_bookings.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->db->where('clr_bookings.biller_id', $this->session->userdata('biller_id'));
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
				$this->excel->getActiveSheet()->setTitle(lang('booking_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('booking_no'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('dry_port'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('line'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('vessel'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('port'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('country'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('quantity'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('note'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('status'));
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->booking_no);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->dry_port);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->line);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->vessel);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->port);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->country);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->quantity));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->decode_html($data_row->note));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($data_row->status));
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
				$filename = 'booking_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
                create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
			
		} else{  
			$this->load->library('datatables');
			$this->datatables ->select("
									DATE_FORMAT(".$this->db->dbprefix('clr_bookings').".date, '%Y-%m-%d %T') as date,
									companies.company as customer,
									clr_bookings.booking_no,
									tru_dry_ports.name as dry_port,
									clr_lines.name as line,
									clr_vessels.name as vessel,
									clr_ports.name as port,
									clr_countries.name as country,
									SUM(IFNULL(".$this->db->dbprefix("clr_booking_items").".quantity,0)) as quantity,
									clr_countries.note,
									clr_bookings.status,
									clr_bookings.clearance_status,
									clr_bookings.attachment,
									clr_bookings.id as id
								")
			->from("clr_bookings")
			->join("clr_booking_items","clr_booking_items.booking_id = clr_bookings.id","inner")
			->join("companies","companies.id = clr_bookings.customer_id","left")
			->join("clr_vessels","clr_vessels.id = clr_bookings.vessel_id","left")
			->join("clr_ports","clr_ports.id = clr_bookings.port_id","left")
			->join("clr_countries","clr_countries.id = clr_bookings.country_id","left")
			->join("clr_lines","clr_lines.id = clr_bookings.line_id","left")
			->join("tru_dry_ports","tru_dry_ports.id = clr_bookings.dry_port_id","left")
			->group_by("clr_bookings.id");
			
			if ($biller_id) {
				$this->datatables->where('clr_bookings.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->datatables->where('clr_bookings.customer_id', $customer_id);
			}
			if ($dry_port_id) {
				$this->datatables->where('clr_bookings.dry_port_id', $dry_port_id);
			}
			if ($line_id) {
				$this->datatables->where('clr_bookings.line_id', $line_id);
			}
			if ($vessel_id) {
				$this->datatables->where('clr_bookings.vessel_id', $vessel_id);
			}
			if ($port_id) {
				$this->datatables->where('clr_bookings.port_id', $port_id);
			}
			if ($status) {
				$this->datatables->where('clr_bookings.status', $status);
			}
			if ($start_date) {
				$this->datatables->where('clr_bookings.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('clr_bookings.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('clr_bookings.biller_id', $this->session->userdata('biller_id'));
			}
			echo $this->datatables->generate();
			
		}
	}
	
	public function booking_detail_report()
	{
		$this->bpas->checkPermissions("booking_report");
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['customers'] = $this->clearances_model->getCustomers();
		$this->data['lines'] = $this->clearances_model->getLines();
		$this->data['vessels'] = $this->clearances_model->getVessels();
		$this->data['ports'] = $this->clearances_model->getPorts();
		$this->data['countries'] = $this->clearances_model->getCountries();
		$this->data['dry_ports'] = $this->clearances_model->getDryPorts();
		$this->data['container_sizes'] = $this->clearances_model->getContainerSizes();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('booking_detail_report')));
        $meta = array('page_title' => lang('booking_detail_report'), 'bc' => $bc);
        $this->page_construct('clearances/booking_detail_report', $meta, $this->data);
	}
	
	public function plan_report()
	{
		$this->bpas->checkPermissions();
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['customers'] = $this->clearances_model->getCustomers();
		$this->data['lines'] = $this->clearances_model->getLines();
		$this->data['vessels'] = $this->clearances_model->getVessels();
		$this->data['ports'] = $this->clearances_model->getPorts();
		$this->data['countries'] = $this->clearances_model->getCountries();
		$this->data['dry_ports'] = $this->clearances_model->getDryPorts();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('plan_report')));
        $meta = array('page_title' => lang('plan_report'), 'bc' => $bc);
        $this->page_construct('clearances/plan_report', $meta, $this->data);
	}
	
	public function get_completed_booking_by_customer(){
		$customer_id = $this->input->get('customer_id');
		$booking_id  = $this->input->get('booking_id') ? $this->input->get('booking_id') : false;
		$bookings = $this->clearances_model->getCompletedBookingsByCustomer($customer_id, $booking_id);
		$part_bookings = $this->clearances_model->getCompletedBookingsByPartCustomer($customer_id, $booking_id);
		if($bookings && $part_bookings){
			$bookings = (object) array_merge((array) $bookings, (array) $part_bookings);
		}else if($part_bookings){
			$bookings = $part_bookings;
		}
		echo json_encode($bookings);
	}
	
	public function expense_payments()
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('expense_payments')));
        $meta = array('page_title' => lang('expense_payments'), 'bc' => $bc);
        $this->page_construct('clearances/expense_payments', $meta, $this->data);
    }

    public function getExpensePayments()
    {
        $this->bpas->checkPermissions('expense_payments');
		$payments_link = anchor('clearances/payment_expenses/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), ' class="expense_payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$add_payment_link = anchor('clearances/add_expense_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'class="expense_payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $action = '<div class="text-center">
						<div class="btn-group text-left">'
							. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
							. lang('actions') . ' <span class="caret"></span></button>
							<ul class="dropdown-menu pull-right" role="menu">
								<li>' . $payments_link . '</li>
								<li>' . $add_payment_link . '</li>
							</ul>
						</div>
					</div>';
        $this->load->library('datatables');
        $this->datatables->select("
									expenses.id as id, 
									DATE_FORMAT(".$this->db->dbprefix('expenses').".date, '%Y-%m-%d %T') as date, 
									clr_bookings.booking_no,
									expenses.reference, 
									companies.company as customer,
									expenses.supplier,  
									clr_ports.name as port,
									IF(".$this->db->dbprefix('clr_ports').".plan = 1, 'Export','Import') as type,
									expenses.grand_total, 
									IFNULL(".$this->db->dbprefix('expenses').".paid,0), 
									(".$this->db->dbprefix('expenses').".grand_total- IFNULL(".$this->db->dbprefix('expenses').".paid,0)) as balance, 
									expenses.payment_status 
								", false)
            ->from('expenses')
			->join('clr_clearances','clr_clearances.id = expenses.clearance_id','LEFT')
			->join('clr_bookings','clr_bookings.id = clr_clearances.booking_id','LEFT')
			->join('clr_ports','clr_ports.id = clr_bookings.port_id','LEFT')
			->join('companies','companies.id = clr_clearances.customer_id','LEFT')
			->where("IFNULL(".$this->db->dbprefix("expenses").".clearance_id,0) > ",0)
            ->group_by('expenses.id');
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('expenses.biller_id =', $this->session->userdata('biller_id'));
		}
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function expense_actions() {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('expense_payments'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('supplier'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));
                    
					$row = 2;
                    foreach ($_POST['val'] as $id) {
                        $expense = $this->clearances_model->getExpenseByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($expense->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $expense->reference);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $expense->supplier);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($expense->grand_total));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($expense->paid));
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($expense->grand_total- $expense->paid));
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($expense->payment_status));
						$row++;                                       
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'expense_payments_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_expense_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function payment_expenses($id = null)
    {
        $this->bpas->checkPermissions(false, true);
		$expense = $this->clearances_model->getExpenseByID($id); 
		$this->data['payments'] = $this->clearances_model->getPaymentExpenses($id);
		$this->data['expense'] = $expense;
        $this->load->view($this->theme . 'clearances/payment_expenses', $this->data);
    }
	
	
	public function add_expense_payment($id = null)
    {
        $this->bpas->checkPermissions('add_expense_payment', true);
		$expense = $this->clearances_model->getExpenseByID($id);
		if ($expense->payment_status == 'paid' && $expense->grand_total == $expense->paid) {
			$this->session->set_flashdata('error', lang("expense_already_paid"));
			$this->bpas->md();
		}
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
                if ( ! $this->site->check_customer_deposit($expense->supplier_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_sup_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$accTranPayments = false;
			$currencies = array();	
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$expense->biller_id);
			
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
			$paymentAcc = $this->site->getAccountSettingByBiller($expense->biller_id);
			if($this->input->post('paid_by')=='deposit'){
				$paying_from = $paymentAcc->supplier_deposit_acc;
			}else{
				$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
				$paying_from = $cash_account->account_code;
				if($cash_account->type=="bank"){
					$bank_name = $cash_account->name;
					$account_name = $this->input->post('account_name');
					$account_number = $this->input->post('account_number');
				}else if($cash_account->type=="cheque"){
					$bank_name = $this->input->post('bank_name');
					$cheque_number = $this->input->post('cheque_number');
					$cheque_date = $this->bpas->fsd($this->input->post('cheque_date'));
				}
			}
			
			$data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'amount' => $this->input->post('amount-paid'),
				'discount' => $this->input->post('discount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->bpas->clear_tags($this->input->post('note')),
                'created_by' => $this->session->userdata('user_id'),
				'expense_id' => $expense->id,
				'transaction' => "Clearance",
				'transaction_id' => $expense->clearance_id,
                'type' => "expense",
				'account_code' => $paying_from,
				'bank_name' => $bank_name,
				'account_name' => $account_name,
				'account_number' => $account_number,
				'cheque_number' => $cheque_number,
				'cheque_date' => $cheque_date,
				'currencies' => json_encode($currencies),
            );
			
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
                $data['attachment'] = $this->upload->file_name;
            }
			
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$acc_trans[] = array(
							'transaction' => 'Payment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paymentAcc->ap_acc,
							'amount' => ($this->input->post('amount-paid')+$this->input->post('discount')),
							'narrative' => 'Expense Payment '.$expense->reference,
							'description' => $this->input->post('note'),
							'biller_id' => $expense->biller_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $expense->supplier_id,
						);
					$acc_trans[] = array(
							'transaction' => 'Payment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paying_from,
							'amount' => $this->input->post('amount-paid') * (-1),
							'narrative' => 'Expense Payment '.$expense->reference,
							'description' => $this->input->post('note'),
							'biller_id' => $expense->biller_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $expense->supplier_id,
						);
					if($this->input->post('discount') != 0){
						$acc_trans[] = array(
							'transaction' => 'Payment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paymentAcc->purchase_discount_acc,
							'amount' => $this->input->post('discount') * (-1),
							'narrative' => 'Expense Payment Discount '.$expense->reference,
							'description' => $this->input->post('note'),
							'biller_id' => $expense->biller_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $expense->supplier_id,
						);
					}	
				}
			//=====end accountig=====//
        } elseif ($this->input->post('add_expense_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->clearances_model->addExpensePayment($data,$acc_trans,$expense->supplier_id)) {
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['expense'] = $expense;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'clearances/add_expense_payment', $this->data);
        }
    }
	
	public function edit_expense_payment($id = null)
    {
		$this->bpas->checkPermissions('edit_expense_payment', true);
		$payment = $this->clearances_model->getPaymentByID($id);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$expense = $this->clearances_model->getExpenseByID($payment->expense_id);
			if ($this->input->post('paid_by') == 'deposit') {
                $amount = $this->input->post('amount-paid')- $payment->amount;
                if (!$this->site->check_customer_deposit($expense->supplier_id, $amount)) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_sup_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$expense->biller_id);
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

			$paymentAcc = $this->site->getAccountSettingByBiller($expense->biller_id);
			$acc_trans = false;
			if($this->input->post('paid_by')=='deposit'){
				$paying_from = $paymentAcc->supplier_deposit_acc;
			}else{
				$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
				$paying_from = $cash_account->account_code;
				if($cash_account->type=="bank"){
					$bank_name = $cash_account->name;
					$account_name = $this->input->post('account_name');
					$account_number = $this->input->post('account_number');
				}else if($cash_account->type=="cheque"){
					$bank_name = $this->input->post('bank_name');
					$cheque_number = $this->input->post('cheque_number');
					$cheque_date = $this->bpas->fsd($this->input->post('cheque_date'));
				}
			}
			
			$data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'amount' => $this->input->post('amount-paid'),
				'discount' => $this->input->post('discount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->bpas->clear_tags($this->input->post('note')),
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
				'transaction' => "Clearance",
				'expense_id' => $expense->id,
				'transaction_id' => $expense->clearance_id,
                'type' => "expense",
				'account_code' => $paying_from,
				'bank_name' => $bank_name,
				'account_name' => $account_name,
				'account_number' => $account_number,
				'cheque_number' => $cheque_number,
				'cheque_date' => $cheque_date,
				'currencies' => json_encode($currencies),
            );
			
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
                $data['attachment'] = $this->upload->file_name;
            }		
			
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$acc_trans[] = array(
							'transaction' => 'Payment',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paymentAcc->ap_acc,
							'amount' => ($this->input->post('amount-paid')+$this->input->post('discount')),
							'narrative' => 'Expense Payment '.$expense->reference,
							'description' => $this->input->post('note'),
							'biller_id' => $expense->biller_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $expense->supplier_id,
						);
					$acc_trans[] = array(
							'transaction' => 'Payment',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paying_from,
							'amount' => $this->input->post('amount-paid') * (-1),
							'narrative' => 'Expense Payment '.$expense->reference,
							'description' => $this->input->post('note'),
							'biller_id' => $expense->biller_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $expense->supplier_id,
						);
					if($this->input->post('discount') != 0){
						$acc_trans[] = array(
							'transaction' => 'Payment',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paymentAcc->purchase_discount_acc,
							'amount' => $this->input->post('discount') * (-1),
							'narrative' => 'Expense Payment Discount '.$expense->reference_no,
							'description' => $this->input->post('note'),
							'biller_id' => $expense->biller_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $expense->supplier_id,
						);
					}
				}
			//=====end accountig=====//
        } elseif ($this->input->post('edit_expense_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->clearances_model->updateExpensePayment($id, $data, $acc_trans, $expense->supplier_id)) {
            $this->session->set_flashdata('message', lang("payment_edited"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'clearances/edit_expense_payment', $this->data);
        }
    }
	
	public function delete_expense_payment($id = null) {
		$this->bpas->checkPermissions('delete_expense_payment', true);
        if ($this->clearances_model->deleteExpensePayment($id, 'del_tk_pm')) {
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function modal_expense_payment($id = null)
    {
        $this->bpas->checkPermissions('expense_payments', true);
        $payment = $this->clearances_model->getPaymentByID($id);
		$inv_payments = $this->clearances_model->getPaymentsByRef($payment->reference_no,$payment->date);
		$expense = $this->clearances_model->getExpenseByID($payment->expense_id);
        $this->data['supplier'] = $this->site->getCompanyByID($expense->supplier_id);
        $this->data['expense'] = $expense;
		$this->data['inv_payments'] = $inv_payments;
		$this->data['biller'] = $this->site->getCompanyByID($expense->biller_id);
        $this->data['payment'] = $payment;
        $this->data['page_title'] = $this->lang->line("modal_expense_payment");
        $this->load->view($this->theme . 'clearances/modal_expense_payment', $this->data);
    }
	
	public function add_multi_expayment($id = null)
    {
        $this->bpas->checkPermissions('add_expense_payment', true);
		$ids = explode('ExpenseID',$id);		
		$multiple = $this->clearances_model->getExpenseByBillers($ids);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$total_amount = $this->input->post('amount-paid');
			$camounts = $this->input->post("c_amount");
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
            }
			if(!$total_amount){
				$this->session->set_flashdata('error', lang("payment_required"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			if($cash_account->type=="bank"){
				$bank_name = $cash_account->name;
				$account_name = $this->input->post('account_name');
				$account_number = $this->input->post('account_number');
			}else if($cash_account->type=="cheque"){
				$bank_name = $this->input->post('bank_name');
				$cheque_number = $this->input->post('cheque_number');
				$cheque_date = $this->bpas->fsd($this->input->post('cheque_date'));
			}
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$multiple->row()->biller_id);
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

			$cur_def = $this->site->getCurrencyByCode($this->Settings->default_currency);
			$data = false;
			for($i=0; $i < count($ids); $i++){
				if($total_amount > 0){
					$expenseInfo = $this->clearances_model->getExpenseBalanceByID($ids[$i]);
					if($expenseInfo){
						$total = ($expenseInfo->grand_total) - ($expenseInfo->paid+$expenseInfo->discount);
						$grand_total = $total;
						if($total_amount > $grand_total){
							$pay_amount = $grand_total;
							$total_amount = $total_amount - $grand_total;
						}else{
							$pay_amount = $total_amount;
							$total_amount = 0;
						}
						$currencies = array();
						if(!empty($camounts)){
							$total_paid = $pay_amount;
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
						
						$data[] = array(
							'date' 			=> $date,
							'expense_id' 	=> $expenseInfo->id,
							'reference_no' 	=> $reference_no,
							'amount' 		=> $pay_amount,
							'paid_by' 		=> $this->input->post('paid_by'),
							'note' 			=> $this->input->post('note'),
							'created_by' 	=> $this->session->userdata('user_id'),
							'type' 			=> 'expense',
							'transaction' 	=> "Clearance",
							'transaction_id'=> $expenseInfo->clearance_id,
							'currencies' 	=> json_encode($currencies),
							'account_code' 	=> $paying_from,
							'bank_name' 	=> $bank_name,
							'account_name' 	=> $account_name,
							'account_number'=> $account_number,
							'cheque_number' => $cheque_number,
							'cheque_date' 	=> $cheque_date,
							'attachment' 	=> $photo,
						);
						if($this->Settings->accounting == 1){
							$paymentAcc = $this->site->getAccountSettingByBiller($expenseInfo->biller_id);
							$acc_trans[$expenseInfo->id][] = array(
									'transaction' => 'Payment',
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => (($this->Settings->default_payable_account==0 || !$expenseInfo->ap_account) ? $paymentAcc->ap_acc : $expenseInfo->ap_account),
									'amount' => $pay_amount,
									'narrative' => 'Expense Payment '.$expenseInfo->reference_no,
									'description' => $this->input->post('note'),
									'biller_id' => $expenseInfo->biller_id,
									'project_id' => $expenseInfo->project_id,
									'user_id' => $this->session->userdata('user_id'),
									'supplier_id' => $expenseInfo->supplier_id,
								);
							$acc_trans[$expenseInfo->id][] = array(
									'transaction' => 'Payment',
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => $paying_from,
									'amount' => $pay_amount * (-1),
									'narrative' => 'Expense Payment '.$expenseInfo->reference_no,
									'description' => $this->input->post('note'),
									'biller_id' => $expenseInfo->biller_id,
									'project_id' => $expenseInfo->project_id,
									'user_id' => $this->session->userdata('user_id'),
									'supplier_id' => $expenseInfo->supplier_id,
								);
						}
					}
				}
			}
        } elseif ($this->input->post('add_multi_expayment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->clearances_model->addMultiExPayment($data, $acc_trans)) {
			$this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$expenses  = $this->clearances_model->getMultiExpenseByID($ids);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if (!$expenses) {
                $this->session->set_flashdata('warning', lang('expenses_already_paid'));
                $this->bpas->md();
            }
			if($multiple->num_rows() > 1){
				$this->session->set_flashdata('error', lang("biller_multi_cannot_add"));
				$this->bpas->md();
			}
            $this->data['expenses'] = $expenses;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'clearances/add_multi_expayment', $this->data);
        }
    }
	
	public function trucking_report()
	{
		$this->bpas->checkPermissions();
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['customers'] = $this->clearances_model->getCustomers();
		$this->data['suppliers'] = $this->clearances_model->getTruckingSuppliers();
		$this->data['ports'] = $this->clearances_model->getPorts();
		$this->data['dry_ports'] = $this->clearances_model->getDryPorts();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('trucking_report')));
        $meta = array('page_title' => lang('trucking_report'), 'bc' => $bc);
        $this->page_construct('clearances/trucking_report', $meta, $this->data);
	}
	
	public function getTruckingReport($xls = null)
	{
		$this->bpas->checkPermissions('trucking_report', TRUE);
		$biller_id = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$customer_id = $this->input->get('customer') ? $this->input->get('customer') : NULL;
		$supplier_id = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
		$dry_port_id = $this->input->get('dry_port') ? $this->input->get('dry_port') : NULL;
		$port_id = $this->input->get('port') ? $this->input->get('port') : NULL;
		$status = $this->input->get('status') ? $this->input->get('status') : NULL;
		$type = $this->input->get('type') ? $this->input->get('type') : NULL;
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date')) : NULL;
		if ($xls) {
			$this->db->select("
								DATE_FORMAT(".$this->db->dbprefix('clr_truckings').".date, '%Y-%m-%d %T') as date,
								companies.company as customer,
								clr_bookings.booking_no,
								suppliers.company as supplier,
								supplier_trucks.plate_number,
								tru_containers.name as container_size,
								clr_truckings.container_no,
								clr_truckings.carrier_seal,
								clr_truckings.custom_seal,
								IFNULL(".$this->db->dbprefix('clr_truckings').".trucking_fee,0) as trucking_fee,
								IFNULL(".$this->db->dbprefix('clr_truckings').".lolo_fee,0) as lolo_fee,
								IFNULL(".$this->db->dbprefix('clr_truckings').".extra_fee,0) as extra_fee,
								IFNULL((".$this->db->dbprefix('clr_truckings').".stand_by_day * ".$this->db->dbprefix('clr_truckings').".stand_by_fee),0) as stand_by_fee,
								IFNULL(".$this->db->dbprefix('clr_truckings').".total_amount,0) as total_amount,
								IFNULL(".$this->db->dbprefix('clr_truckings').".paid,0) as paid,
								IFNULL(".$this->db->dbprefix('clr_truckings').".total_amount,0) - IFNULL(".$this->db->dbprefix('clr_truckings').".paid,0) as balance,
								IFNULL(".$this->db->dbprefix('clr_truckings').".booking_fee,0) as booking_fee,
								IFNULL(".$this->db->dbprefix('clr_truckings').".extra_loading,0) as extra_loading,
								IFNULL(".$this->db->dbprefix('clr_truckings').".extra_lock,0) as extra_lock,
								IFNULL(".$this->db->dbprefix('clr_truckings').".extra_document,0) as extra_document,
								clr_truckings.lock_date,
								clr_truckings.status,
								clr_truckings.payment_status,
								clr_truckings.id as id,
							")
            ->from("clr_truckings")
			->join("clr_bookings","clr_bookings.id = clr_truckings.booking_id","left")
			->join("tru_containers","tru_containers.id = clr_truckings.container_size_id","left")
			->join("companies as suppliers","suppliers.id = clr_truckings.supplier_id","left")
			->join("supplier_trucks","supplier_trucks.id = clr_truckings.truck_id","left")
			->join("clr_ports","clr_ports.id = clr_bookings.port_id","left")
			->join("companies","companies.id = clr_truckings.customer_id","left");
			$this->db->order_by("clr_truckings.date");
			if ($biller_id) {
				$this->db->where('clr_truckings.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->db->where('clr_truckings.customer_id', $customer_id);
			}
			if ($supplier_id) {
				$this->db->where('clr_truckings.supplier_id', $supplier_id);
			}
			if ($dry_port_id) {
				$this->db->where('clr_truckings.dry_port_id', $dry_port_id);
			}
			if ($port_id) {
				$this->db->where('clr_bookings.port_id', $port_id);
			}
			if ($status) {
				$this->db->where('clr_truckings.status', $status);
			}
			if ($type) {
				if($type=="import"){
					$this->db->where('clr_ports.plan', 0);
				}else{
					$this->db->where('clr_ports.plan', 1);
				}
			}
			if ($start_date) {
				$this->db->where('clr_truckings.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('clr_truckings.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->db->where('clr_truckings.biller_id', $this->session->userdata('biller_id'));
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
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('booking_no'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('supplier'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('truck_no'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('size'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('container_no'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('carrier_seal'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('custom_seal'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('trucking'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('lolo'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('extra'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('stand_by'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('total'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('Q1', lang('booking'));
				$this->excel->getActiveSheet()->SetCellValue('R1', lang('extra_loading'));
				$this->excel->getActiveSheet()->SetCellValue('S1', lang('extra_lock'));
				$this->excel->getActiveSheet()->SetCellValue('T1', lang('extra_document'));
				$this->excel->getActiveSheet()->SetCellValue('U1', lang('lock_date'));
				$this->excel->getActiveSheet()->SetCellValue('V1', lang('status'));
				$this->excel->getActiveSheet()->SetCellValue('W1', lang('payment_status'));
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->booking_no);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->supplier);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->plate_number);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->container_size);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->container_no);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->carrier_seal);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->custom_seal);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->trucking_fee));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($data_row->lolo_fee));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($data_row->extra_fee));
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->formatDecimal($data_row->stand_by_fee));
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $this->bpas->formatDecimal($data_row->total_amount));
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, $this->bpas->formatDecimal($data_row->paid));
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, $this->bpas->formatDecimal($data_row->balance));
					$this->excel->getActiveSheet()->SetCellValue('Q' . $row, $this->bpas->formatDecimal($data_row->booking_fee));
					$this->excel->getActiveSheet()->SetCellValue('R' . $row, $this->bpas->formatDecimal($data_row->extra_loading));
					$this->excel->getActiveSheet()->SetCellValue('S' . $row, $this->bpas->formatDecimal($data_row->extra_lock));
					$this->excel->getActiveSheet()->SetCellValue('T' . $row, $this->bpas->formatDecimal($data_row->extra_document));
					$this->excel->getActiveSheet()->SetCellValue('U' . $row, $data_row->lock_date ? $this->bpas->hrsd($data_row->lock_date) : "");
					$this->excel->getActiveSheet()->SetCellValue('V' . $row, lang($data_row->status));
					$this->excel->getActiveSheet()->SetCellValue('W' . $row, lang($data_row->payment_status));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(7);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(8);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(8);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(8);
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(8);
				$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(8);
				$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(8);
				$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(8);
				$this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(8);
				$this->excel->getActiveSheet()->getColumnDimension('R')->setWidth(8);
				$this->excel->getActiveSheet()->getColumnDimension('S')->setWidth(8);
				$this->excel->getActiveSheet()->getColumnDimension('T')->setWidth(8);
				$this->excel->getActiveSheet()->getColumnDimension('U')->setWidth(12);
				$this->excel->getActiveSheet()->getColumnDimension('V')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('W')->setWidth(15);
				
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
									DATE_FORMAT(".$this->db->dbprefix('clr_truckings').".date, '%Y-%m-%d %T') as date,
									companies.company as customer,
									clr_bookings.booking_no,
									suppliers.company as supplier,
									supplier_trucks.plate_number,
									tru_containers.name as container_size,
									clr_truckings.container_no,
									clr_truckings.carrier_seal,
									clr_truckings.custom_seal,
									IFNULL(".$this->db->dbprefix('clr_truckings').".trucking_fee,0) as trucking_fee,
									IFNULL(".$this->db->dbprefix('clr_truckings').".lolo_fee,0) as lolo_fee,
									IFNULL(".$this->db->dbprefix('clr_truckings').".extra_fee,0) as extra_fee,
									IFNULL((".$this->db->dbprefix('clr_truckings').".stand_by_day * ".$this->db->dbprefix('clr_truckings').".stand_by_fee),0) as stand_by_fee,
									IFNULL(".$this->db->dbprefix('clr_truckings').".total_amount,0) as total_amount,
									IFNULL(".$this->db->dbprefix('clr_truckings').".paid,0) as paid,
									IFNULL(".$this->db->dbprefix('clr_truckings').".total_amount,0) - IFNULL(".$this->db->dbprefix('clr_truckings').".paid,0) as balance,
									IFNULL(".$this->db->dbprefix('clr_truckings').".booking_fee,0) as booking_fee,
									IFNULL(".$this->db->dbprefix('clr_truckings').".extra_loading,0) as extra_loading,
									IFNULL(".$this->db->dbprefix('clr_truckings').".extra_lock,0) as extra_lock,
									IFNULL(".$this->db->dbprefix('clr_truckings').".extra_document,0) as extra_document,
									clr_truckings.lock_date,
									clr_truckings.status,
									clr_truckings.payment_status,
									clr_truckings.attachment,
									clr_truckings.id as id,
								")
            ->from("clr_truckings")
			->join("clr_bookings","clr_bookings.id = clr_truckings.booking_id","left")
			->join("tru_containers","tru_containers.id = clr_truckings.container_size_id","left")
			->join("companies as suppliers","suppliers.id = clr_truckings.supplier_id","left")
			->join("supplier_trucks","supplier_trucks.id = clr_truckings.truck_id","left")
			->join("clr_ports","clr_ports.id = clr_bookings.port_id","left")
			->join("companies","companies.id = clr_truckings.customer_id","left");
			
			if ($biller_id) {
				$this->datatables->where('clr_truckings.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->datatables->where('clr_truckings.customer_id', $customer_id);
			}
			if ($supplier_id) {
				$this->datatables->where('clr_truckings.supplier_id', $supplier_id);
			}
			if ($dry_port_id) {
				$this->datatables->where('clr_truckings.dry_port_id', $dry_port_id);
			}
			if ($port_id) {
				$this->datatables->where('clr_bookings.port_id', $port_id);
			}
			if ($status) {
				$this->datatables->where('clr_truckings.status', $status);
			}
			if ($type) {
				if($type=="import"){
					$this->datatables->where('clr_ports.plan', 0);
				}else{
					$this->datatables->where('clr_ports.plan', 1);
				}
			}
			if ($start_date) {
				$this->datatables->where('clr_truckings.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('clr_truckings.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('clr_truckings.biller_id', $this->session->userdata('biller_id'));
			}
			echo $this->datatables->generate();
			
		}
	}
	
	public function modal_booking($id = null)
    {
        $this->bpas->checkPermissions('bookings', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $booking = $this->clearances_model->getBookingInfoByID($id);
        $this->data['biller'] = $this->site->getCompanyByID($booking->biller_id);
        $this->data['booking'] = $booking;
		$this->data['booking_items'] = $this->clearances_model->getBookingItems($id);
		$this->data['created_by'] = $this->site->getUserByID($booking->created_by);
        $this->load->view($this->theme . 'clearances/modal_booking', $this->data);
    }
	
	public function modal_trucking($id = null)
    {
        $this->bpas->checkPermissions('truckings', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $trucking = $this->clearances_model->getTruckingInfoByID($id);
        $this->data['biller'] = $this->site->getCompanyByID($trucking->biller_id);
        $this->data['trucking'] = $trucking;
		$this->data['created_by'] = $this->site->getUserByID($trucking->created_by);
        $this->load->view($this->theme . 'clearances/modal_trucking', $this->data);
    }
	
	public function modal_clearance($id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $clearance = $this->clearances_model->getClearanceInfoByID($id);
        $this->data['biller'] = $this->site->getCompanyByID($clearance->biller_id);
        $this->data['clearance'] = $clearance;
		$this->data['clearance_items'] = $this->clearances_model->getClearanceItems($id,"desc");
		$this->data['truckings'] = $this->clearances_model->getTruckingsByBooking($clearance->booking_id);
        $this->load->view($this->theme . 'clearances/modal_clearance', $this->data);
    }
	
	public function modal_clearance_income($id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $clearance = $this->clearances_model->getClearanceInfoByID($id);
        $this->data['biller'] = $this->site->getCompanyByID($clearance->biller_id);
        $this->data['clearance'] = $clearance;
		$this->data['clearance_items'] = $this->clearances_model->getClearanceIncomeItems($id);
		$this->data['containers'] = $this->clearances_model->getContainersByBookingID($clearance->booking_id);
        $this->load->view($this->theme . 'clearances/modal_clearance_income', $this->data);
    }
	
	public function modal_clearance_expense($id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $clearance = $this->clearances_model->getClearanceInfoByID($id);
        $this->data['biller'] = $this->site->getCompanyByID($clearance->biller_id);
        $this->data['clearance'] = $clearance;
		$this->data['clearance_items'] = $this->clearances_model->getClearanceExpenseItems($id);
		$this->data['containers'] = $this->clearances_model->getContainersByBookingID($clearance->booking_id);
        $this->load->view($this->theme . 'clearances/modal_clearance_expense', $this->data);
    }
	
	public function clearance_report()
	{
		$this->bpas->checkPermissions();
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['customers'] = $this->clearances_model->getCustomers();
		$this->data['ports'] = $this->clearances_model->getPorts();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('clearance_report')));
        $meta = array('page_title' => lang('clearance_report'), 'bc' => $bc);
        $this->page_construct('clearances/clearance_report', $meta, $this->data);
	}
	
	public function getClearanceReport($xls = null)
	{
		$this->bpas->checkPermissions('clearance_report', TRUE);
		$biller_id = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$customer_id = $this->input->get('customer') ? $this->input->get('customer') : NULL;
		$port_id = $this->input->get('port') ? $this->input->get('port') : NULL;
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date')) : NULL;
		if ($xls) {
			$this->db->select("
									DATE_FORMAT(".$this->db->dbprefix('clr_clearances').".date, '%Y-%m-%d %T') as date,
									clr_clearances.reference_no,
									companies.company as customer,
									clr_bookings.booking_no,
									IFNULL(".$this->db->dbprefix('clr_clearances').".total_expense,0) as expense,
									IFNULL(".$this->db->dbprefix('clr_clearances').".total_income,0) as income,
									IFNULL(".$this->db->dbprefix('clr_clearances').".total_income,0) - IFNULL(".$this->db->dbprefix('clr_clearances').".total_expense,0) as margin,
									clr_clearances.attachment,
									clr_clearances.id as id
								")
            ->from("clr_clearances")
			->join("clr_bookings","clr_bookings.id = clr_clearances.booking_id","left")
			->join("companies","companies.id = clr_clearances.customer_id","left");
			if ($biller_id) {
				$this->db->where('clr_clearances.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->db->where('clr_clearances.customer_id', $customer_id);
			}
			if ($port_id) {
				$this->db->where('clr_bookings.port_id', $port_id);
			}
			if ($start_date) {
				$this->db->where('clr_clearances.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('clr_clearances.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->db->where('clr_clearances.biller_id', $this->session->userdata('biller_id'));
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
				$this->excel->getActiveSheet()->setTitle(lang('clearance_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('booking_no'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('expense'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('income'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('margin'));
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->booking_no);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($data_row->expense));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($data_row->income));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($data_row->margin));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

				$filename = 'clearance_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
                create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
			
		} else{  
			$this->load->library('datatables');
			$this->datatables->select("
									DATE_FORMAT(".$this->db->dbprefix('clr_clearances').".date, '%Y-%m-%d %T') as date,
									clr_clearances.reference_no,
									companies.company as customer,
									clr_bookings.booking_no,
									IFNULL(".$this->db->dbprefix('clr_clearances').".total_expense,0) as expense,
									IFNULL(".$this->db->dbprefix('clr_clearances').".total_income,0) as income,
									IFNULL(".$this->db->dbprefix('clr_clearances').".total_income,0) - IFNULL(".$this->db->dbprefix('clr_clearances').".total_expense,0) as margin,
									clr_clearances.attachment,
									clr_clearances.id as id
								")
            ->from("clr_clearances")
			->join("clr_bookings","clr_bookings.id = clr_clearances.booking_id","left")
			->join("companies","companies.id = clr_clearances.customer_id","left");
			if ($biller_id) {
				$this->datatables->where('clr_clearances.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->datatables->where('clr_clearances.customer_id', $customer_id);
			}
			if ($port_id) {
				$this->datatables->where('clr_bookings.port_id', $port_id);
			}
			if ($start_date) {
				$this->datatables->where('clr_clearances.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('clr_clearances.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('clr_clearances.biller_id', $this->session->userdata('biller_id'));
			}
			echo $this->datatables->generate();
			
		}
	}
	
	public function expense_by_booking_report()
	{
		$this->bpas->checkPermissions();
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['customers'] = $this->clearances_model->getCustomers();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('expense_by_booking_report')));
        $meta = array('page_title' => lang('expense_by_booking_report'), 'bc' => $bc);
        $this->page_construct('clearances/expense_by_booking_report', $meta, $this->data);
	}
	
	public function income_by_booking_report()
	{
		$this->bpas->checkPermissions();
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['customers'] = $this->clearances_model->getCustomers();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('income_by_booking_report')));
        $meta = array('page_title' => lang('income_by_booking_report'), 'bc' => $bc);
        $this->page_construct('clearances/income_by_booking_report', $meta, $this->data);
	}
	
	public function expense_payment_report()
	{
		$this->bpas->checkPermissions();
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['suppliers'] = $this->site->getSuppliers();
		$this->data['customers'] = $this->clearances_model->getCustomers();
		$this->data['ports'] = $this->clearances_model->getPorts();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('expense_payment_report')));
        $meta = array('page_title' => lang('expense_payment_report'), 'bc' => $bc);
        $this->page_construct('clearances/expense_payment_report', $meta, $this->data);
	}
	
	public function getExpensePaymentReport($xls = null)
	{
		$this->bpas->checkPermissions('expense_payment_report', TRUE);
		$biller_id = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$customer_id = $this->input->get('customer') ? $this->input->get('customer') : NULL;
		$supplier_id = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
		$port_id = $this->input->get('port') ? $this->input->get('port') : NULL;
		$type = $this->input->get('type') ? $this->input->get('type') : NULL;
		$status = $this->input->get('status') ? $this->input->get('status') : NULL;
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date')) : NULL;
		if ($xls) {
			$this->db->select("
								DATE_FORMAT(".$this->db->dbprefix('expenses').".date, '%Y-%m-%d %T') as date, 
								clr_bookings.booking_no,
								expenses.reference,
								companies.company as customer,
								expenses.supplier,  
								clr_ports.name as port,
								IF(".$this->db->dbprefix('clr_ports').".plan = 1, 'Export','Import') as type,
								expenses.grand_total, 
								IFNULL(".$this->db->dbprefix('expenses').".paid,0) as paid, 
								(".$this->db->dbprefix('expenses').".grand_total- IFNULL(".$this->db->dbprefix('expenses').".paid,0)) as balance, 
								expenses.payment_status,
								expenses.id as id
							", false)
            ->from('expenses')
			->join("clr_clearances","clr_clearances.id = expenses.clearance_id","INNER")
			->join("clr_bookings","clr_bookings.id = clr_clearances.booking_id","LEFT")
			->join("companies","companies.id = clr_clearances.customer_id","LEFT")
			->join('clr_ports','clr_ports.id = clr_bookings.port_id','LEFT')
			->where("IFNULL(".$this->db->dbprefix("expenses").".clearance_id,0) > ",0)
            ->group_by('expenses.id');
			if ($biller_id) {
				$this->db->where('expenses.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->db->where('clr_clearances.customer_id', $customer_id);
			}
			if ($supplier_id) {
				$this->db->where('expenses.supplier_id', $supplier_id);
			}
			if ($port_id) {
				$this->db->where('clr_bookings.port_id', $port_id);
			}
			if ($type) {
				if($type=="import"){
					$this->db->where('clr_ports.plan', 0);
				}else{
					$this->db->where('clr_ports.plan', 1);
				}
			}
			if ($status) {
				$this->db->where('expenses.payment_status', $status);
			}
			if ($start_date) {
				$this->db->where('IFNULL('.$this->db->dbprefix("clr_bookings").'.date,'.$this->db->dbprefix("expenses").'.date) >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('IFNULL('.$this->db->dbprefix("clr_bookings").'.date,'.$this->db->dbprefix("expenses").'.date) <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->db->where('expenses.biller_id', $this->session->userdata('biller_id'));
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
				$this->excel->getActiveSheet()->setTitle(lang('expense_payment_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('booking_no'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('clearance_no'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('supplier'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('port'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('type'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('grand_total'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('status'));
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->booking_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->reference);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->supplier);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->port);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->type);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($data_row->grand_total));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->paid));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->balance));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($data_row->payment_status));
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

				$filename = 'expense_payment_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
                create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
			
		} else{  
			$this->load->library('datatables');
			$this->datatables->select("
									DATE_FORMAT(".$this->db->dbprefix('expenses').".date, '%Y-%m-%d %T') as date, 
									clr_bookings.booking_no,
									expenses.reference,
									companies.company as customer,
									expenses.supplier,  
									clr_ports.name as port,
									IF(".$this->db->dbprefix('clr_ports').".plan = 1, 'Export','Import') as type,
									expenses.grand_total, 
									IFNULL(".$this->db->dbprefix('expenses').".paid,0) as paid, 
									(".$this->db->dbprefix('expenses').".grand_total- IFNULL(".$this->db->dbprefix('expenses').".paid,0)) as balance, 
									expenses.payment_status,
									expenses.id as id
								", false)
            ->from('expenses')
			->join("clr_clearances","clr_clearances.id = expenses.clearance_id","INNER")
			->join("clr_bookings","clr_bookings.id = clr_clearances.booking_id","LEFT")
			->join("companies","companies.id = clr_clearances.customer_id","LEFT")
			->join('clr_ports','clr_ports.id = clr_bookings.port_id','LEFT')
			->where("IFNULL(".$this->db->dbprefix("expenses").".clearance_id,0) > ",0)
            ->group_by('expenses.id');
			if ($biller_id) {
				$this->datatables->where('expenses.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->datatables->where('clr_clearances.customer_id', $customer_id);
			}
			if ($supplier_id) {
				$this->datatables->where('expenses.supplier_id', $supplier_id);
			}
			if ($port_id) {
				$this->datatables->where('clr_bookings.port_id', $port_id);
			}
			if ($type) {
				if($type=="import"){
					$this->datatables->where('clr_ports.plan', 0);
				}else{
					$this->datatables->where('clr_ports.plan', 1);
				}
			}
			if ($status) {
				$this->datatables->where('expenses.payment_status', $status);
			}
			if ($start_date) {
				$this->datatables->where('IFNULL('.$this->db->dbprefix("clr_bookings").'.date,'.$this->db->dbprefix("expenses").'.date) >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('IFNULL('.$this->db->dbprefix("clr_bookings").'.date,'.$this->db->dbprefix("expenses").'.date) <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('expenses.biller_id', $this->session->userdata('biller_id'));
			}
			echo $this->datatables->generate();
			
		}
	}
	
	
	
	
	public function get_clearances(){
		$biller_id = $this->input->get('biller_id');
		$customer_id = $this->input->get('customer_id');
		$from_date = $this->bpas->fld(trim($this->input->get('from_date')));
		$to_date = $this->bpas->fld(trim($this->input->get('to_date')));
		$sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : false;
		$clearances = $this->clearances_model->getClearances($biller_id,$customer_id,$from_date,$to_date,$sale_id);
		echo json_encode($clearances);
	}
	
	public function sales($biller_id = NULL)
    {
		$this->bpas->checkPermissions("index",false,"sales");
		if($biller_id == 0){
			$biller_id = null;
		}
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;	
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('sales')));
		$meta = array('page_title' => lang('sales'), 'bc' => $bc);
        $this->page_construct('clearances/sales', $meta, $this->data);
    }
	
	public function getSales($warehouse_id = null, $biller_id = NULL)
    {
		$this->bpas->checkPermissions("index",false,"sales");
		$payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_payment" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="add_payment" data-target="#myModal"');
		$edit_link = anchor('clearances/edit_sale/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), ' class="edit_sale" ');
		$delete_link = "<a href='#' class='po delete_sale' title='<b>" . $this->lang->line("delete_sale") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . site_url('clearances/delete_sale/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . "</a>";
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
									sales.id as id,
									DATE_FORMAT(".$this->db->dbprefix('sales').".date, '%Y-%m-%d %T') as date,
									DATE_FORMAT(".$this->db->dbprefix('sales').".from_date, '%Y-%m-%d %T') as from_date,
									DATE_FORMAT(".$this->db->dbprefix('sales').".to_date, '%Y-%m-%d %T') as to_date,
									sales.reference_no,
									clr_clearances.clearance_no,
									sales.customer,
									sales.grand_total,
									sales.paid,
									IFNULL(".$this->db->dbprefix('sales').".grand_total,0) - IFNULL(".$this->db->dbprefix('sales').".paid,0) as balance,
									sales.payment_status,
									sales.attachment
								")
							->join("(SELECT
										".$this->db->dbprefix('sale_clearances_items').".sale_id,
										GROUP_CONCAT(".$this->db->dbprefix("clr_clearances").".reference_no SEPARATOR '<br>') as clearance_no
									FROM 
										".$this->db->dbprefix('sale_clearances_items')."
									INNER JOIN ".$this->db->dbprefix('clr_clearances')." ON ".$this->db->dbprefix('clr_clearances').".id = ".$this->db->dbprefix('sale_clearances_items').".clearance_id
									GROUP BY
										".$this->db->dbprefix('sale_clearances_items').".sale_id
									) as clr_clearances","clr_clearances.sale_id = sales.id","LEFT")
							->group_by('sales.id')
							->from('sales');
		$this->datatables->where('sales.type', "clearance");
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
	
	
	
	public function add_sale(){
		$this->bpas->checkPermissions("add",false,"sales");
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
		$this->form_validation->set_rules('from_date', $this->lang->line("from_date"), 'required');
		$this->form_validation->set_rules('to_date', $this->lang->line("to_date"), 'required');
        if ($this->form_validation->run() == true) {
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller');
			$customer_id = $this->input->post('customer');
			$note = $this->input->post('note');
			$staff_note = $this->input->post('staff_note');
			$from_date = $this->bpas->fld(trim($this->input->post('from_date')));
			$to_date = $this->bpas->fld(trim($this->input->post('to_date')));
            $payment_term = $this->input->post('payment_term');
			$tax = $this->input->post('tax');
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
			$order_discount_id = $this->input->post('order_discount') ? $this->input->post('order_discount') : false;
			$currencies =  $this->site->getAllCurrencies();
			if(!empty($currencies)){
				foreach($currencies as $currency_row){
					$current_amount = 0;
					$currency[] = array(
									"amount" => $current_amount,
									"currency" => $currency_row->code,
									"rate" => ($this->input->post("exchange_rate_".$currency_row->code) ? $this->input->post("exchange_rate_".$currency_row->code) : $currency_row->rate),
								);
				}
			}
			$i = isset($_POST['clearance_id']) ? sizeof($_POST['clearance_id']) : 0;
			$percentage = '%';
			$clearance_items = false;
			$sale_items = false;
			$data = false;
			$accTrans = false;
			for ($r = 0; $r < $i; $r++) {
				
				$total = 0;
				$order_tax = 0;
				$order_tax_id = null;
				$product_tax = 0;
				$total_tax = 0;
				$order_discount = 0;
				$total_items = 0;
				
				$clearance_id = $_POST['clearance_id'][$r];
				$booking_id = $_POST['booking_id'][$r];
				$subtotal = $_POST['subtotal'][$r];
				$clearance_items[$r][] = array(
					'clearance_id' => $clearance_id,
					'booking_id' => $booking_id,
					'subtotal' => $subtotal,
				);
				$income_items  = $this->clearances_model->getClearanceIncomeItems($clearance_id);
				if($income_items){
					foreach($income_items as $income_item){
						$item_tax = 0;
						$tax_rate_id = 0;
						$item_rate_tax = 0;
						$net_unit_price = $income_item->price;
						$unit_price = $income_item->price;
						$total += ($unit_price * $income_item->quantity);
						if($tax && $income_item->tax_rate && $tax_details = $this->site->getTaxRateByID($income_item->tax_rate)){
							$unit_price = $unit_price + ($income_item->price * $tax_details->rate / 100);
							$item_tax = ($income_item->price * $tax_details->rate / 100) * $income_item->quantity;
							$item_rate_tax = $tax_details->rate . "%";	
							$tax_rate_id = $income_item->tax_rate;
							$product_tax += $item_tax;
						}
						$sale_items[$r][] = array(
							'product_id' => $income_item->product_id,
							'product_code' => $income_item->product_code,
							'product_name' => $income_item->product_name,
							'product_type' => $income_item->product_type,
							'net_unit_price' => $net_unit_price,
							'unit_price' => $unit_price,
							'real_unit_price' => $income_item->price,
							'quantity' => $income_item->quantity,
							'unit_quantity' => $income_item->quantity,
							'subtotal' => ($unit_price * $income_item->quantity),
							'item_tax' => $item_tax,
							'tax_rate_id' => $tax_rate_id,
							'tax' => $item_rate_tax,
							'comment' => $income_item->description,
						);

						$total_items++;
					}
				
					
					
					if($tax){
						$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('tax_so',$biller_id);
					}else{
						$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so',$biller_id);
					}
					if ($order_discount_id) {
						$opos = strpos($order_discount_id, $percentage);
						if ($opos !== false) {
							$ods = explode("%", $order_discount_id);
							$order_discount = (($total) * (Float) ($ods[0])) / 100;
						} else {
							$order_discount = $order_discount_id;
						}
					} 


					$total_tax = $order_tax + $product_tax;
					$grand_total = $total + $total_tax - $order_discount;
					$data[$r] = array(
						'date' => $date,
						'reference_no' => $reference_no,
						'customer_id' => $customer_id,
						'customer' => $customer,
						'biller_id' => $biller_id,
						'biller' => $biller,
						'note' => $note,
						'staff_note' => $staff_note,
						'total' => $total,
						'product_tax' => $product_tax,
						'order_tax_id' => $order_tax_id,
						'order_tax' => $order_tax,
						'total_tax' => $total_tax,
						'order_discount_id' => $order_discount_id,
						'order_discount' => $order_discount,
						'total_discount' => $order_discount,
						'grand_total' => $grand_total,
						'sale_status' => "completed",
						'total_items' => $total_items,
						'type' => 'clearance',
						'payment_status' => 'pending',
						'delivery_status' => 'completed',
						'currencies' => json_encode($currency),
						'payment_term' => $payment_term,
						'due_date' => $due_date,
						'paid' => 0,
						'created_by' => $this->session->userdata('user_id'),
						'from_date' => $from_date,
						'to_date' => $to_date,
						'tax' => $tax,
					);
					
					if($this->Settings->accounting == 1){
						$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
						if($order_discount != 0){
							$accTrans[$r][] = array(
								'transaction' => 'Sale',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $saleAcc->sale_discount_acc,
								'amount' => $order_discount,
								'narrative' => 'Order Discount',
								'description' => $note,
								'biller_id' => $biller_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $customer_id,
							);
							$accTrans[$r][] = array(
								'transaction' => 'Sale',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $saleAcc->ar_acc,
								'amount' => $order_discount * (-1),
								'narrative' => 'Order Discount',
								'description' => $note,
								'biller_id' => $biller_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $customer_id,
							);
						}
						if($total_tax != 0){
							$accTrans[$r][] = array(
								'transaction' => 'Sale',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $saleAcc->vat_output,
								'amount' => $total_tax * (-1),
								'narrative' => 'Tax',
								'description' => $note,
								'biller_id' => $biller_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $customer_id,
							);
							$accTrans[$r][] = array(
								'transaction' => 'Sale',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $saleAcc->ar_acc,
								'amount' => $total_tax ,
								'narrative' => 'Tax',
								'description' => $note,
								'biller_id' => $biller_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $customer_id,
							);
						}
					}
				}
			}
			
			if (!$data) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
			}
			$attachment = null;
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
            }
        }
		if ($this->form_validation->run() == true && $this->clearances_model->addSale($data,$clearance_items,$sale_items,$attachment, $accTrans)) {	
            $this->session->set_flashdata('message', $this->lang->line("sale_added"));          
			redirect('clearances/sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
			$this->data['customers'] = $this->site->getCustomers();
			$this->data['currencies'] = $this->site->getDailyCurrencies();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => site_url('clearances/sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('add_sale')));
			$meta = array('page_title' => lang('add_sale'), 'bc' => $bc);
            $this->page_construct('clearances/add_sale', $meta, $this->data);
        }
	}

	
	public function edit_sale($id = false){
		$this->bpas->checkPermissions("edit",false,"sales");
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
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
			$tax = $this->input->post('tax');
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
			if($tax){
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('tax_so',$biller_id);
			}else{
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so',$biller_id);
			}
			$i = isset($_POST['clearance_id']) ? sizeof($_POST['clearance_id']) : 0;
			$total = 0;
			$order_tax = 0;
			$order_tax_id = null;
			$product_tax = 0;
			$total_tax = 0;
			$order_discount = 0;
			$total_items = 0;
			$percentage = '%';
			$clearance_items = false;
			$sale_items = false;
			for ($r = 0; $r < $i; $r++) {
				$clearance_id = $_POST['clearance_id'][$r];
				$booking_id = $_POST['booking_id'][$r];
				$subtotal = $_POST['subtotal'][$r];
				$clearance_items[] = array(
					'sale_id' => $id,
					'clearance_id' => $clearance_id,
					'booking_id' => $booking_id,
					'subtotal' => $subtotal,
				);
				
				$income_items  = $this->clearances_model->getClearanceIncomeItems($clearance_id);
				if($income_items){
					foreach($income_items as $income_item){
						$item_tax = 0;
						$tax_rate_id = 0;
						$item_rate_tax = 0;
						$net_unit_price = $income_item->price;
						$unit_price = $income_item->price;
						if($tax && $income_item->tax_rate && $tax_details = $this->site->getTaxRateByID($income_item->tax_rate)){
							$unit_price = $unit_price + ($income_item->price * $tax_details->rate / 100);
							$item_tax = ($income_item->price * $tax_details->rate / 100) * $income_item->quantity;
							$item_rate_tax = $tax_details->rate . "%";	
							$tax_rate_id = $income_item->tax_rate;
							$product_tax += $item_tax;
						}
						$sale_items[] = array(
							'sale_id' => $id,
							'product_id' => $income_item->product_id,
							'product_code' => $income_item->product_code,
							'product_name' => $income_item->product_name,
							'product_type' => $income_item->product_type,
							'net_unit_price' => $net_unit_price,
							'unit_price' => $unit_price,
							'real_unit_price' => $income_item->price,
							'quantity' => $income_item->quantity,
							'unit_quantity' => $income_item->quantity,
							'subtotal' => ($unit_price * $income_item->quantity),
							'item_tax' => $item_tax,
							'tax_rate_id' => $tax_rate_id,
							'tax' => $item_rate_tax,
							'comment' => $income_item->description,
						);
					}
				}
				$total += $subtotal;
				$total_items++;
			}

			if (!$clearance_items) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
			} else {
				krsort($clearance_items);
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
			$total_tax = $order_tax + $product_tax;
			$grand_total = $total + $total_tax - $order_discount;
			$currencies =  $this->site->getAllCurrencies();
			if(!empty($currencies)){
				foreach($currencies as $currency_row){
					$current_amount = 0;
					$currency[] = array(
								"amount" => $current_amount,
								"currency" => $currency_row->code,
								"rate" => ($this->input->post("exchange_rate_".$currency_row->code) ? $this->input->post("exchange_rate_".$currency_row->code) : $currency_row->rate),
							);
				}
			}
			
			$data = array(
				'date' => $date,
                'reference_no' => $reference_no,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'note' => $note,
				'staff_note' => $staff_note,
                'total' => $total,
				'product_tax' => $product_tax,
				'order_tax_id' => $order_tax_id,
				'order_tax' => $order_tax,
				'total_tax' => $total_tax,
				'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $order_discount,
                'grand_total' => $grand_total,
                'sale_status' => "completed",
				'total_items' => $total_items,
				'type' => 'clearance',
				'delivery_status' => 'completed',
				'currencies' => json_encode($currency),
                'payment_term' => $payment_term,
                'due_date' => $due_date,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
				'from_date' => $from_date,
				'to_date' => $to_date,
				'tax' => $tax,
            );
			if($this->Settings->accounting == 1){
				$saleAcc = $this->site->getAccountSettingByBiller($biller_id);
				if($order_discount != 0){
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference_no,
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
						'reference' => $reference_no,
						'account' => $saleAcc->ar_acc,
						'amount' => $order_discount * (-1),
						'narrative' => 'Order Discount',
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
				}
				if($total_tax != 0){
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $saleAcc->vat_output,
						'amount' => $total_tax * (-1),
						'narrative' => 'Tax',
						'description' => $note,
						'biller_id' => $biller_id,
						'user_id' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id,
					);
					$accTrans[] = array(
						'transaction_id' => $id,
						'transaction' => 'Sale',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $saleAcc->ar_acc,
						'amount' => $total_tax ,
						'narrative' => 'Tax',
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
		if ($this->form_validation->run() == true && $this->clearances_model->updateSale($id, $data,$clearance_items,$sale_items, $accTrans)) {	
            $this->session->set_flashdata('message', $this->lang->line("sale_edited"));          
			redirect('clearances/sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
			$this->data['customers'] = $this->site->getCustomers();
			$this->data['sale'] = $this->clearances_model->getSaleByID($id);
            $this->data['clearance_items'] = $this->clearances_model->getClearanceSaleItemBySaleID($id);
			$this->data['currencies'] = $this->site->getDailyCurrencies();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => site_url('clearances/sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('edit_sale')));
			$meta = array('page_title' => lang('edit_sale'), 'bc' => $bc);
            $this->page_construct('clearances/edit_sale', $meta, $this->data);
        }
	}
	
	public function delete_sale($id = null){
		$this->bpas->checkPermissions("delete",true,"sales");
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		if ($this->clearances_model->deleteSale($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("sale_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('sale_deleted'));
			redirect('clearances/sales');
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
					$this->bpas->checkPermissions("delete",false,"sales");
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->clearances_model->deleteSale($id)) {
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
									DATE_FORMAT(".$this->db->dbprefix('sales').".date, '%Y-%m-%d %T') as date,
									DATE_FORMAT(".$this->db->dbprefix('sales').".from_date, '%Y-%m-%d %T') as from_date,
									DATE_FORMAT(".$this->db->dbprefix('sales').".to_date, '%Y-%m-%d %T') as to_date,
									sales.reference_no,
									sales.customer,
									sales.grand_total,
									sales.paid,
									IFNULL(".$this->db->dbprefix('sales').".grand_total,0) - IFNULL(".$this->db->dbprefix('sales').".paid,0) as balance,
									sales.payment_status,
									sales.attachment				
								")
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
	
	public function modal_sale($id = null, $type = false){
        $this->bpas->checkPermissions("index",true,"sales");
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $sale = $this->clearances_model->getSaleByID($id);
        $this->data['sale'] = $sale;
		$this->data['rows'] = $this->clearances_model->getSummarySaleItems($id,"asc");
        $this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
		$this->data['customer'] = $this->site->getCompanyByID($sale->customer_id);
		$this->data['payment'] = $this->clearances_model->getPaymentBySale($id);
		$this->data['containers'] = $this->clearances_model->getContainersBySaleID($id);
		$this->data['clearances'] = $this->clearances_model->getClearancesBySale($id);
		if($type=="tax"){
			$this->load->view($this->theme . 'clearances/modal_sale_tax', $this->data);
		}else if($type=="debit_note"){
			$this->load->view($this->theme . 'clearances/modal_sale_debit_note', $this->data);
		}else if($type=="debit_note_header"){
			$this->load->view($this->theme . 'clearances/modal_sale_debit_note_header', $this->data);
		}else{
			$this->load->view($this->theme . 'clearances/modal_sale', $this->data);
		}
		
    }
	
	public function containers()
    {
        $this->bpas->checkPermissions('containers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'clearances', 'page' => lang('clearance')), array('link' => '#', 'page' => lang('containers')));
        $meta = array('page_title' => lang('containers'), 'bc' => $bc);
        $this->page_construct('clearances/containers', $meta, $this->data);
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
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_container") . "' href='" . site_url('clearances/edit_container/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_container") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('clearances/delete_container/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
	
	
	public function add_container()
    {
		$this->bpas->checkPermissions('containers', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
						'name' => $this->input->post('name'),
						'extract_id' => $this->input->post('extract_id'),
						'port_lolo' => $this->input->post('port_lolo'),
						'note' => $this->input->post('note')
					);
        } elseif ($this->input->post('add_container')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('clearances/containers');
        }
        if ($this->form_validation->run() == true && $id = $this->clearances_model->addContainer($data)) {
            $this->session->set_flashdata('message', $this->lang->line("container_added"));
            redirect('clearances/containers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['products'] = $this->site->getProductServices();
            $this->load->view($this->theme . 'clearances/add_container', $this->data);
        }
    }
	
	public function edit_container($id = false)
    {
		$this->bpas->checkPermissions('containers', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
						'name' => $this->input->post('name'),
						'extract_id' => $this->input->post('extract_id'),
						'port_lolo' => $this->input->post('port_lolo'),
						'note' => $this->input->post('note')
					);
        } elseif ($this->input->post('edit_container')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('clearances/containers');
        }
        if ($this->form_validation->run() == true && $id = $this->clearances_model->updateContainer($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("container_edited"));
            redirect('clearances/containers');
        } else {
			$container = $this->clearances_model->getContainerByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['products'] = $this->site->getProductServices();
			$this->data['container'] = $container;
            $this->load->view($this->theme . 'clearances/edit_container', $this->data);
        }
    }
	
	public function delete_container($id = NULL)
    {	
		$this->bpas->checkPermissions('containers', true);
		if ($this->clearances_model->deleteContainer($id)) {
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
                        if (!$this->clearances_model->deleteContainer($id)) {
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
						$container = $this->clearances_model->getContainerByID($id);
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
	
	
	public function sale_statement_report()
	{
		$this->bpas->checkPermissions();
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['customers'] = $this->clearances_model->getCustomers();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('sale_statement_report')));
        $meta = array('page_title' => lang('sale_statement_report'), 'bc' => $bc);
        $this->page_construct('clearances/sale_statement_report', $meta, $this->data);
	}
	
	public function getSaleStatementReport($xls = null)
	{
		$this->bpas->checkPermissions('sale_statement_report', TRUE);
		$biller_id = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$customer_id = $this->input->get('customer') ? $this->input->get('customer') : NULL;
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date')) : NULL;
		$payment_status = $this->input->get('payment_status') ? $this->input->get('payment_status') : NULL;
		$where_tax = "";
		if($this->input->get('tax')=="yes"){
			$where_tax .= " AND IFNULL(".$this->db->dbprefix('sale_items').".item_tax,0) > 0";
		}else if($this->input->get('tax')=="no"){
			$where_tax .= " AND IFNULL(".$this->db->dbprefix('sale_items').".item_tax,0) = 0";
		}
		if ($xls) {
			$sel_grand_total = "sales.grand_total";
			IF($payment_status == "pending"){
				$sel_grand_total = "(sales.grand_total - IFNULL(bms_payments.paid,0)) as grand_total";
			}
			
			$this->db ->select("
									'' as row_num,
									sales.reference_no as invoice_no,
									GROUP_CONCAT(clr_clearances.invoice_no SEPARATOR '<br>') as fty_no,
									GROUP_CONCAT(clr_clearances.container_no SEPARATOR '<br>') as container_no,
									GROUP_CONCAT(clr_clearances.quantity SEPARATOR '<br>') as quantity,
									GROUP_CONCAT(clr_clearances.ship_mode SEPARATOR '<br>') as ship_mode,
									".$sel_grand_total.",
									sales.id
								")
			
			->from("sale_clearances_items")
			->join("(
						SELECT
							".$this->db->dbprefix('sales').".id,
							".$this->db->dbprefix('sales').".date,
							".$this->db->dbprefix('sales').".biller_id,
							".$this->db->dbprefix('sales').".customer_id,
							".$this->db->dbprefix('sales').".customer,
							".$this->db->dbprefix('sales').".reference_no,
							SUM(".$this->db->dbprefix('sale_items').".subtotal) as grand_total
						FROM
							".$this->db->dbprefix('sales')."
						INNER JOIN ".$this->db->dbprefix('sale_items')." ON ".$this->db->dbprefix('sale_items').".sale_id = ".$this->db->dbprefix('sales').".id
						WHERE 1=1 ".$where_tax."
						GROUP BY
							".$this->db->dbprefix('sales').".id
					) as sales","sales.id = sale_clearances_items.sale_id","INNER")
			->join("(
						SELECT 
							".$this->db->dbprefix('clr_clearances').".id,
							".$this->db->dbprefix('clr_clearances').".invoice_no,
							".$this->db->dbprefix('clr_clearances').".quantity,
							".$this->db->dbprefix('clr_ports').".name as ship_mode,
							GROUP_CONCAT(CONCAT(REPLACE(".$this->db->dbprefix('clr_truckings').".container_no,'+',CONCAT('/',".$this->db->dbprefix('tru_containers').".name,'<br>')),'/',".$this->db->dbprefix('tru_containers').".name) SEPARATOR '<br>') as container_no
						FROM
							".$this->db->dbprefix('clr_clearances')."
						LEFT JOIN ".$this->db->dbprefix('clr_bookings')." ON ".$this->db->dbprefix('clr_bookings').".id = ".$this->db->dbprefix('clr_clearances').".booking_id
						LEFT JOIN ".$this->db->dbprefix('clr_ports')." ON ".$this->db->dbprefix('clr_ports').".id = ".$this->db->dbprefix('clr_bookings').".port_id
						LEFT JOIN ".$this->db->dbprefix('clr_truckings')." ON ".$this->db->dbprefix('clr_truckings').".booking_id = ".$this->db->dbprefix('clr_clearances').".booking_id
						LEFT JOIN ".$this->db->dbprefix('tru_containers')." ON ".$this->db->dbprefix('tru_containers').".id = ".$this->db->dbprefix('clr_truckings').".container_size_id
						GROUP BY
							".$this->db->dbprefix('clr_clearances').".id
					) as clr_clearances","clr_clearances.id = sale_clearances_items.clearance_id","LEFT");
			if($payment_status){
				$this->db->join('(SELECT
						sale_id,
						SUM(IFNULL(amount,0) + IFNULL(discount,0)) AS paid
					FROM
						'.$this->db->dbprefix('payments').'
						
					GROUP BY
						sale_id) as bms_payments', 'bms_payments.sale_id=sales.id', 'left');
						
				if($payment_status=="pending"){
					$this->db->where('ROUND((sales.grand_total - IFNULL(bms_payments.paid,0)),'.$this->Settings->decimals.') >', 0);
				}else{
					$this->db->where('ROUND((sales.grand_total - IFNULL(bms_payments.paid,0)),'.$this->Settings->decimals.') =',0);
				}		
			}
			
			$this->db->group_by("sales.id");
			
			if ($biller_id) {
				$this->db->where('sales.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->db->where('sales.customer_id', $customer_id);
			}
			if ($start_date) {
				$this->db->where('sales.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('sales.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->db->where('sales.biller_id', $this->session->userdata('biller_id'));
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
				$this->excel->getActiveSheet()->setTitle(lang('sale_statement_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('invoice_no'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('fty_no'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('container_no'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('qty'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('ship_mode'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('amount'));
				
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->invoice_no);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->fty_no);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->container_no);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->quantity);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->ship_mode);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($data_row->grand_total));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

				$filename = 'sale_statement_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
                create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
			
		} else{  
			$this->load->library('datatables');
			$sel_grand_total = "sales.grand_total";
			IF($payment_status == "pending"){
				$sel_grand_total = "(sales.grand_total - IFNULL(bms_payments.paid,0)) as grand_total";
			}
			
			$this->datatables ->select("
									'' as row_num,
									sales.reference_no as invoice_no,
									GROUP_CONCAT(clr_clearances.invoice_no SEPARATOR '<br>') as fty_no,
									GROUP_CONCAT(clr_clearances.container_no SEPARATOR '<br>') as container_no,
									GROUP_CONCAT(clr_clearances.quantity SEPARATOR '<br>') as quantity,
									GROUP_CONCAT(clr_clearances.ship_mode SEPARATOR '<br>') as ship_mode,
									".$sel_grand_total.",
									sales.id
								")
			
			->from("sale_clearances_items")
			->join("(
						SELECT
							".$this->db->dbprefix('sales').".id,
							".$this->db->dbprefix('sales').".date,
							".$this->db->dbprefix('sales').".biller_id,
							".$this->db->dbprefix('sales').".customer_id,
							".$this->db->dbprefix('sales').".customer,
							".$this->db->dbprefix('sales').".reference_no,
							SUM(".$this->db->dbprefix('sale_items').".subtotal) as grand_total
						FROM
							".$this->db->dbprefix('sales')."
						INNER JOIN ".$this->db->dbprefix('sale_items')." ON ".$this->db->dbprefix('sale_items').".sale_id = ".$this->db->dbprefix('sales').".id
						WHERE 1=1 ".$where_tax."
						GROUP BY
							".$this->db->dbprefix('sales').".id
					) as sales","sales.id = sale_clearances_items.sale_id","INNER")
			->join("(
						SELECT 
							".$this->db->dbprefix('clr_clearances').".id,
							".$this->db->dbprefix('clr_clearances').".invoice_no,
							".$this->db->dbprefix('clr_clearances').".quantity,
							".$this->db->dbprefix('clr_ports').".name as ship_mode,
							GROUP_CONCAT(CONCAT(REPLACE(".$this->db->dbprefix('clr_truckings').".container_no,'+',CONCAT('/',".$this->db->dbprefix('tru_containers').".name,'<br>')),'/',".$this->db->dbprefix('tru_containers').".name) SEPARATOR '<br>') as container_no
						FROM
							".$this->db->dbprefix('clr_clearances')."
						LEFT JOIN ".$this->db->dbprefix('clr_bookings')." ON ".$this->db->dbprefix('clr_bookings').".id = ".$this->db->dbprefix('clr_clearances').".booking_id
						LEFT JOIN ".$this->db->dbprefix('clr_ports')." ON ".$this->db->dbprefix('clr_ports').".id = ".$this->db->dbprefix('clr_bookings').".port_id
						LEFT JOIN ".$this->db->dbprefix('clr_truckings')." ON ".$this->db->dbprefix('clr_truckings').".booking_id = ".$this->db->dbprefix('clr_clearances').".booking_id
						LEFT JOIN ".$this->db->dbprefix('tru_containers')." ON ".$this->db->dbprefix('tru_containers').".id = ".$this->db->dbprefix('clr_truckings').".container_size_id
						GROUP BY
							".$this->db->dbprefix('clr_clearances').".id
					) as clr_clearances","clr_clearances.id = sale_clearances_items.clearance_id","LEFT");
			if($payment_status){
				$this->datatables->join('(SELECT
						sale_id,
						SUM(IFNULL(amount,0) + IFNULL(discount,0)) AS paid
					FROM
						'.$this->db->dbprefix('payments').'
						
					GROUP BY
						sale_id) as bms_payments', 'bms_payments.sale_id=sales.id', 'left');
						
				if($payment_status=="pending"){
					$this->datatables->where('ROUND((sales.grand_total - IFNULL(bms_payments.paid,0)),'.$this->Settings->decimals.') >', 0);
				}else{
					$this->datatables->where('ROUND((sales.grand_total - IFNULL(bms_payments.paid,0)),'.$this->Settings->decimals.') =',0);
				}		
			}
			
			$this->datatables->group_by("sales.id");
			
			
			if ($biller_id) {
				$this->datatables->where('sales.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->datatables->where('sales.customer_id', $customer_id);
			}
			if ($start_date) {
				$this->datatables->where('sales.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('sales.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('sales.biller_id', $this->session->userdata('biller_id'));
			}
			//$this->datatables->where('sales.id >', 38);
			echo $this->datatables->generate();
			
		}
	}
	
	public function get_amount_in_word(){
		$amount = $this->input->get("amount") ? $this->input->get("amount") : false;
		$amount_in_word = $this->bpas->numberToWordsCur($amount);
		echo json_encode($amount_in_word);
	}
	
	public function sale_report()
	{
		$this->bpas->checkPermissions('sales', false,'reports');
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['customers'] = $this->clearances_model->getCustomers();
		$this->data['ports'] = $this->clearances_model->getPorts();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('sale_report')));
        $meta = array('page_title' => lang('sale_report'), 'bc' => $bc);
        $this->page_construct('clearances/sale_report', $meta, $this->data);
	}
	
	public function getSaleReport($xls = null)
	{
		$this->bpas->checkPermissions('sales', TRUE, 'reports');
		$biller_id = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$customer_id = $this->input->get('customer') ? $this->input->get('customer') : NULL;
		$port_id = $this->input->get('port') ? $this->input->get('port') : NULL;
		$type = $this->input->get('type') ? $this->input->get('type') : NULL;
		$container_no = $this->input->get('container_no') ? $this->input->get('container_no') : NULL;
		$start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fld($this->input->get('end_date')) : NULL;
		if ($xls) {
			$this->db->select("
							DATE_FORMAT(".$this->db->dbprefix('sales').".date, '%Y-%m-%d %T') as date,
							DATE_FORMAT(".$this->db->dbprefix('sales').".from_date, '%Y-%m-%d %T') as from_date,
							DATE_FORMAT(".$this->db->dbprefix('sales').".to_date, '%Y-%m-%d %T') as to_date,
							sales.reference_no,
							clr_clearances.reference_no as clearance_no,
							clr_bookings.booking_no,
							sales.customer,
							clr_ports.name as port,
							GROUP_CONCAT(".$this->db->dbprefix('clr_truckings').".container_no SEPARATOR '\n') AS container_no,
							sales.grand_total,
							sales.paid,
							IFNULL(".$this->db->dbprefix('sales').".grand_total,0) - IFNULL(".$this->db->dbprefix('sales').".paid,0) as balance,
							sales.payment_status
						")
					->join("sale_clearances_items","sale_clearances_items.sale_id = sales.id","LEFT")
					->join("clr_clearances","clr_clearances.id = sale_clearances_items.clearance_id","LEFT")
					->join("clr_bookings","clr_bookings.id = clr_clearances.booking_id","LEFT")
					->join("clr_ports","clr_ports.id = clr_bookings.port_id","LEFT")
					->join("clr_truckings","clr_truckings.booking_id = clr_bookings.id","LEFT")
					->group_by('sales.id')
					->from('sales');
			$this->db->where('sales.type', "clearance");
			if ($biller_id) {
				$this->db->where('sales.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->db->where('sales.customer_id', $customer_id);
			}
			if ($port_id) {
				$this->db->where('clr_bookings.port_id', $port_id);
			}		
			if ($type) {
				if($type=="import"){
					$this->db->where('clr_ports.plan', 0);
				}else{
					$this->db->where('clr_ports.plan', 1);
				}
			}
			if ($container_no) {
				$this->db->where('clr_truckings.container_no', $container_no);
			}
			if ($start_date) {
				$this->db->where('sales.date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('sales.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('sales.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->db->where('sales.biller_id', $this->session->userdata('biller_id'));
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
				$this->excel->getActiveSheet()->setTitle(lang('sale_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('from_date'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('to_date'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('clearance_no'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('booking_no'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('port'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('container_no'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('grand_total'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('payment_status'));
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($data_row->from_date));
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->hrld($data_row->to_date));
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->clearance_no);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->booking_no);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->port);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->container_no);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->grand_total));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($data_row->paid));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($data_row->balance));
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, lang($data_row->payment_status));
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

				$filename = 'sale_report_' . date('Y_m_d_H_i_s');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
                create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);
		} else{  
			$this->load->library('datatables');
			$this->datatables->select("
										DATE_FORMAT(".$this->db->dbprefix('sales').".date, '%Y-%m-%d %T') as date,
										DATE_FORMAT(".$this->db->dbprefix('sales').".from_date, '%Y-%m-%d %T') as from_date,
										DATE_FORMAT(".$this->db->dbprefix('sales').".to_date, '%Y-%m-%d %T') as to_date,
										sales.reference_no,
										clr_clearances.reference_no as clearance_no,
										clr_bookings.booking_no,
										sales.customer,
										clr_ports.name as port,
										GROUP_CONCAT(".$this->db->dbprefix('clr_truckings').".container_no SEPARATOR '<br>') AS container_no,
										sales.grand_total,
										sales.paid,
										IFNULL(".$this->db->dbprefix('sales').".grand_total,0) - IFNULL(".$this->db->dbprefix('sales').".paid,0) as balance,
										sales.payment_status,
										sales.attachment,
										sales.id as id,
									")
								->join("sale_clearances_items","sale_clearances_items.sale_id = sales.id","LEFT")
								->join("clr_clearances","clr_clearances.id = sale_clearances_items.clearance_id","LEFT")
								->join("clr_bookings","clr_bookings.id = clr_clearances.booking_id","LEFT")
								->join("clr_ports","clr_ports.id = clr_bookings.port_id","LEFT")
								->join("clr_truckings","clr_truckings.booking_id = clr_bookings.id","LEFT")
								->group_by('sales.id')
								->from('sales');
			$this->datatables->where('sales.type', "clearance");
			if ($biller_id) {
				$this->datatables->where('sales.biller_id', $biller_id);
			}
			if ($customer_id) {
				$this->datatables->where('sales.customer_id', $customer_id);
			}
			if ($port_id) {
				$this->datatables->where('clr_bookings.port_id', $port_id);
			}		
			if ($type) {
				if($type=="import"){
					$this->datatables->where('clr_ports.plan', 0);
				}else{
					$this->datatables->where('clr_ports.plan', 1);
				}
			}
			if ($container_no) {
				$this->datatables->where('clr_truckings.container_no', $container_no);
			}
			if ($start_date) {
				$this->datatables->where('sales.date >=', $start_date);
			}
			if ($end_date) {
				$this->datatables->where('sales.date <=', $end_date);
			}
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('sales.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('sales.biller_id', $this->session->userdata('biller_id'));
			}
			echo $this->datatables->generate();
		}
	}
	
	public function expense_by_product_report()
	{
		$this->bpas->checkPermissions("expense_payment_report");
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['suppliers'] = $this->site->getSuppliers();
		$this->data['customers'] = $this->clearances_model->getCustomers();
		$this->data['ports'] = $this->clearances_model->getPorts();
		$this->data['products'] = $this->clearances_model->getServices();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearance')), array('link' => '#', 'page' => lang('expense_by_product_report')));
        $meta = array('page_title' => lang('expense_by_product_report'), 'bc' => $bc);
        $this->page_construct('clearances/expense_by_product_report', $meta, $this->data);
	}
	
	public function sale_form_report(){
		$this->bpas->checkPermissions('sales', false,'reports');
		
		$biller_id = $this->input->post('biller') ? $this->input->post('biller') : false;
		$customer_id = $this->input->post('customer') ? $this->input->post('customer') : false;
		$start_date = $this->input->post('start_date') ? $this->bpas->fsd($this->input->post('start_date')) : date("Y-m-d");
		$end_date = $this->input->post('end_date') ? $this->bpas->fsd($this->input->post('end_date')) : date("Y-m-d");
		$tax = $this->input->post('tax') ? $this->input->post('tax') : "no";
		$data = $this->clearances_model->getSaleForms($biller_id, $customer_id, $start_date, $end_date, $tax);
		
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] =  $this->site->getBillers();
		$this->data['data'] =  $data;
		$this->data['customers'] = $this->clearances_model->getCustomers();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('clearances'), 'page' => lang('clearances')), array('link' => '#', 'page' => lang('sale_form_report')));
        $meta = array('page_title' => lang('sale_form_report'), 'bc' => $bc);
		if($tax=="no"){
			$this->page_construct('clearances/sale_form_report', $meta, $this->data);
		}else{
			$this->page_construct('clearances/sale_form_tax_report', $meta, $this->data);
		}
	}
	
	
	public function trucking_payments($id = null)
    {
        $this->bpas->checkPermissions("expense_payments", true);
		$this->data['payments'] = $this->clearances_model->getTruckingPayments($id);
        $this->load->view($this->theme . 'clearances/trucking_payments', $this->data);
    }
	
	public function add_trucking_payment($id = null)
    {
        $this->bpas->checkPermissions('add_expense_payment', true);
		$trucking = $this->clearances_model->getTruckingByID($id);
		if ($trucking->payment_status == 'paid' && $trucking->total_amount == $trucking->paid) {
			$this->session->set_flashdata('error', lang("trucking_already_paid"));
			$this->bpas->md();
		}
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$accTranPayments = false;
			$currencies = array();	
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$trucking->biller_id);
			
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
			$paymentAcc = $this->site->getAccountSettingByBiller($trucking->biller_id);
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			if($cash_account->type=="bank"){
				$bank_name = $cash_account->name;
				$account_name = $this->input->post('account_name');
				$account_number = $this->input->post('account_number');
			}else if($cash_account->type=="cheque"){
				$bank_name = $this->input->post('bank_name');
				$cheque_number = $this->input->post('cheque_number');
				$cheque_date = $this->bpas->fsd($this->input->post('cheque_date'));
			}
			
			
			$data = array(
                'date' => $date,
                'reference_no' => $reference_no,
				'trucking_id' => $trucking->id,
                'amount' => $this->input->post('amount-paid'),
				'discount' => $this->input->post('discount'),
                'paid_by' => $this->input->post('paid_by'),
                'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s'),
				'note' => $this->bpas->clear_tags($this->input->post('note')),
				'bank_name' => $bank_name,
				'account_name' => $account_name,
				'account_number' => $account_number,
				'cheque_number' => $cheque_number,
				'cheque_date' => $cheque_date,
				'currencies' => json_encode($currencies),
				'account_code' => $paying_from,
            );
			
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
                $data['attachment'] = $this->upload->file_name;
            }
			
			$acc_trans = false;
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$acc_trans[] = array(
							'transaction' => 'Trucking Payment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paymentAcc->prepaid_acc,
							'amount' => ($this->input->post('amount-paid')+$this->input->post('discount')),
							'narrative' => 'Trucking Payment '.$trucking->container_no,
							'description' => $this->input->post('note'),
							'biller_id' => $trucking->biller_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $trucking->supplier_id,
						);
					$acc_trans[] = array(
							'transaction' => 'Trucking Payment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paying_from,
							'amount' => $this->input->post('amount-paid') * (-1),
							'narrative' => 'Trucking Payment '.$trucking->container_no,
							'description' => $this->input->post('note'),
							'biller_id' => $trucking->biller_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $trucking->supplier_id,
						);
					if($this->input->post('discount') != 0){
						$acc_trans[] = array(
							'transaction' => 'Trucking Payment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paymentAcc->purchase_discount_acc,
							'amount' => $this->input->post('discount') * (-1),
							'narrative' => 'Trucking Payment Discount '.$trucking->container_no,
							'description' => $this->input->post('note'),
							'biller_id' => $trucking->biller_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $trucking->supplier_id,
						);
					}	
				}
			//=====end accountig=====//
        } elseif ($this->input->post('add_trucking_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->clearances_model->addTruckingPayment($data,$acc_trans)) {
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['trucking'] = $trucking;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'clearances/add_trucking_payment', $this->data);
        }
    }
	
	public function edit_trucking_payment($id = null)
    {
		$this->bpas->checkPermissions('edit_expense_payment', true);
		$payment = $this->clearances_model->getTruckingPaymentByID($id);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$trucking = $this->clearances_model->getTruckingByID($payment->trucking_id);
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$trucking->biller_id);
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
			
			$paymentAcc = $this->site->getAccountSettingByBiller($trucking->biller_id);
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			if($cash_account->type=="bank"){
				$bank_name = $cash_account->name;
				$account_name = $this->input->post('account_name');
				$account_number = $this->input->post('account_number');
			}else if($cash_account->type=="cheque"){
				$bank_name = $this->input->post('bank_name');
				$cheque_number = $this->input->post('cheque_number');
				$cheque_date = $this->bpas->fsd($this->input->post('cheque_date'));
			}
			

			$data = array(
                'date' => $date,
                'reference_no' => $reference_no,
				'trucking_id' => $trucking->id,
                'amount' => $this->input->post('amount-paid'),
				'discount' => $this->input->post('discount'),
                'paid_by' => $this->input->post('paid_by'),
                'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
				'note' => $this->bpas->clear_tags($this->input->post('note')),
				'bank_name' => $bank_name,
				'account_name' => $account_name,
				'account_number' => $account_number,
				'cheque_number' => $cheque_number,
				'cheque_date' => $cheque_date,
				'currencies' => json_encode($currencies),
				'account_code' => $paying_from,
            );
			
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
                $data['attachment'] = $this->upload->file_name;
            }		

			$acc_trans = false;
			//=====accountig=====//
				if($this->Settings->accounting == 1){
					$acc_trans[] = array(
							'transaction' => 'Trucking Payment',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paymentAcc->prepaid_acc,
							'amount' => ($this->input->post('amount-paid')+$this->input->post('discount')),
							'narrative' => 'Trucking Payment '.$trucking->container_no,
							'description' => $this->input->post('note'),
							'biller_id' => $trucking->biller_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $trucking->supplier_id,
						);
					$acc_trans[] = array(
							'transaction' => 'Trucking Payment',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paying_from,
							'amount' => $this->input->post('amount-paid') * (-1),
							'narrative' => 'Trucking Payment '.$trucking->container_no,
							'description' => $this->input->post('note'),
							'biller_id' => $trucking->biller_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $trucking->supplier_id,
						);
					if($this->input->post('discount') != 0){
						$acc_trans[] = array(
							'transaction' => 'Trucking Payment',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $paymentAcc->purchase_discount_acc,
							'amount' => $this->input->post('discount') * (-1),
							'narrative' => 'Trucking Payment Discount '.$trucking->container_no,
							'description' => $this->input->post('note'),
							'biller_id' => $trucking->biller_id,
							'user_id' => $this->session->userdata('user_id'),
							'supplier_id' => $trucking->supplier_id,
						);
					}	
				}
			//=====end accountig=====//
			
        } elseif ($this->input->post('edit_trucking_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->clearances_model->updateTruckingPayment($id, $data, $acc_trans)) {
            $this->session->set_flashdata('message', lang("payment_edited"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'clearances/edit_trucking_payment', $this->data);
        }
    }
	
	public function delete_trucking_payment($id = null) {
		$this->bpas->checkPermissions('delete_expense_payment', true);
        if ($this->clearances_model->deleteTruckingPayment($id)) {
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function add_multi_trucking_payment($id = null)
    {
        $this->bpas->checkPermissions('add_expense_payment', true);
		$ids = explode('TruckingID',$id);		
		$multiple = $this->clearances_model->getTruckingByBillers($ids);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$total_amount = $this->input->post('amount-paid');
			$camounts = $this->input->post("c_amount");
			$attachment = null;
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
                $attachment = $this->upload->file_name;
            }
			if(!$total_amount){
				$this->session->set_flashdata('error', lang("payment_required"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
			$cash_account = $this->site->getCashAccountByID($this->input->post('paid_by'));
			$paying_from = $cash_account->account_code;
			if($cash_account->type=="bank"){
				$bank_name = $cash_account->name;
				$account_name = $this->input->post('account_name');
				$account_number = $this->input->post('account_number');
			}else if($cash_account->type=="cheque"){
				$bank_name = $this->input->post('bank_name');
				$cheque_number = $this->input->post('cheque_number');
				$cheque_date = $this->bpas->fsd($this->input->post('cheque_date'));
			}
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$multiple->row()->biller_id);
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
			$cur_def = $this->site->getCurrencyByCode($this->Settings->default_currency);
			$data = false;
			for($i=0; $i < count($ids); $i++){
				if($total_amount > 0){
					$truckingInfo = $this->clearances_model->getTruckingByID($ids[$i]);
					if($truckingInfo){
						$total = $truckingInfo->total_amount - $truckingInfo->paid;
						$grand_total = $total;
						if($total_amount > $grand_total){
							$pay_amount = $grand_total;
							$total_amount = $total_amount - $grand_total;
						}else{
							$pay_amount = $total_amount;
							$total_amount = 0;
						}
						$currencies = array();
						if(!empty($camounts)){
							$total_paid = $pay_amount;
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
						
						$data[] = array(
							'date' => $date,
							'reference_no' => $reference_no,
							'trucking_id' => $truckingInfo->id,
							'amount' => $pay_amount,
							'paid_by' => $this->input->post('paid_by'),
							'created_by' => $this->session->userdata('user_id'),
							'created_at' => date('Y-m-d H:i:s'),
							'note' => $this->bpas->clear_tags($this->input->post('note')),
							'bank_name' => $bank_name,
							'account_name' => $account_name,
							'account_number' => $account_number,
							'cheque_number' => $cheque_number,
							'cheque_date' => $cheque_date,
							'currencies' => json_encode($currencies),
							'attachment' 	=> $attachment,
							'account_code' => $paying_from,
						);
						
						if($this->Settings->accounting == 1){
							$paymentAcc = $this->site->getAccountSettingByBiller($truckingInfo->biller_id);
							$acc_trans[$truckingInfo->id][] = array(
									'transaction' => 'Trucking Payment',
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => $paymentAcc->prepaid_acc,
									'amount' => $pay_amount,
									'narrative' => 'Trucking Payment '.$truckingInfo->container_no,
									'description' => $this->input->post('note'),
									'biller_id' => $truckingInfo->biller_id,
									'user_id' => $this->session->userdata('user_id'),
									'supplier_id' => $truckingInfo->supplier_id,
								);
							$acc_trans[$truckingInfo->id][] = array(
									'transaction' => 'Trucking Payment',
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => $paying_from,
									'amount' => $pay_amount * (-1),
									'narrative' => 'Trucking Payment '.$truckingInfo->container_no,
									'description' => $this->input->post('note'),
									'biller_id' => $truckingInfo->biller_id,
									'user_id' => $this->session->userdata('user_id'),
									'supplier_id' => $truckingInfo->supplier_id,
								);
						}
					}
				}
			}
        } elseif ($this->input->post('add_multi_trucking_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->clearances_model->addMultiTruckingPayment($data, $acc_trans)) {
			$this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$truckings  = $this->clearances_model->getMultiTruckingByID($ids);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if (!$truckings) {
                $this->session->set_flashdata('warning', lang('expenses_already_paid'));
                $this->bpas->md();
            }
			if($multiple->num_rows() > 1){
				$this->session->set_flashdata('error', lang("biller_multi_cannot_add"));
				$this->bpas->md();
			}
            $this->data['truckings'] = $truckings;
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'clearances/add_multi_trucking_payment', $this->data);
        }
    }
	
	public function modal_trucking_payment($id = null)
    {
        $this->bpas->checkPermissions('expense_payments', true);
        $payment = $this->clearances_model->getTruckingPaymentByID($id);
		$inv_payments = $this->clearances_model->getTruckingPaymentsByRef($payment->reference_no,$payment->date);
		$trucking = $this->clearances_model->getTruckingByID($payment->trucking_id);
        $this->data['supplier'] = $this->site->getCompanyByID($trucking->supplier_id);
        $this->data['trucking'] = $trucking;
		$this->data['inv_payments'] = $inv_payments;
		$this->data['biller'] = $this->site->getCompanyByID($trucking->biller_id);
        $this->data['payment'] = $payment;
        $this->data['page_title'] = $this->lang->line("modal_trucking_payment");
        $this->load->view($this->theme . 'clearances/modal_trucking_payment', $this->data);
    }
	


	
	
	
	
	
	


}
