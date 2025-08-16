<?php defined('BASEPATH') or exit('No direct script access allowed');

class Deliveries extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        if ($this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->admin_load('deliveries', $this->Settings->user_language);
        $this->load->library('form_validation');
       	$this->load->admin_model('sales_order_model');
		$this->load->admin_model('sales_model');
		$this->load->admin_model('deliveries_model');
        $this->digital_upload_path = 'files/';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
    }

	public function index($biller_id = null)
	{
	    if (!isset($this->GP['sales-deliveries']) && (!$this->Admin && !$this->Owner)) {
	        $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
	    }  
	    $user = $this->site->getUser($this->session->userdata('user_id'));
        if ($this->Settings->multi_biller) {
            $count = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
        } else {
            $count = $user->biller_id ? ((array) $user->biller_id) : null;
        }
	    if ($this->Owner || $this->Admin || empty($count)) {
            $this->data['billers']   = $this->site->getAllCompanies('biller');
            $this->data['biller_id'] = $biller_id;
            $this->data['biller']    = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        } else {
            if (count($count) > 1) {
                $this->data['billers']   = $this->site->getAllCompanies('biller');
            } else {
                $this->data['billers']   = null;
            }
            $this->data['count_billers'] = $count;
            $this->data['user_biller']   = (isset($count) && count($count) == 1) ? $this->site->getCompanyByID($this->session->userdata('biller_id')) : null;
            $this->data['biller_id']     = $biller_id;
            $this->data['biller']        = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        }

        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => '#', 'page' => lang('deliveries')));
        $meta = array('page_title' => lang('deliveries'), 'bc' => $bc);
        $this->page_construct('deliveries/index', $meta, $this->data);
	}
	
	public function getDeliveries($biller_id = null)
    {
        if (!isset($this->GP['sales-deliveries']) && (!$this->Admin && !$this->Owner)) {
	        $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
	    }
	    if ((!$this->Owner && !$this->Admin) && !$this->session->userdata('view_right')) {
	        $user = $this->site->getUser($this->session->userdata('user_id'));
	        if ($this->Settings->multi_biller) {
	            $biller_id = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
	        } else {
	            $biller_id = $user->biller_id ? ((array) $user->biller_id) : null;
	        }
	    }
        $payments_link        = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('delivery_fee'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link     = anchor('admin/sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_delivery_fee'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
	    $delivery_note    = anchor('admin/sales/delivery_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('delivery_note'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
	    $delivery_voucher = anchor('admin/sales/delivery_voucher/$1', '<i class="fa fa-file-text-o"></i> ' . lang('delivery_voucher'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
	    $delivery_note1   = anchor('admin/sales/delivery_note1/$1', '<i class="fa fa-file-text-o"></i> ' . lang('delivery_note_1'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $detail_link      = anchor('admin/deliveries/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('delivery_details'), 'data-toggle="modal" data-target="#myModal"');
        $pdf_link         = anchor('admin/sales/pdf_delivery/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $convert_link     = anchor('admin/sales/add/0/0/$1', '<i class="fa fa-heart"></i> ' . lang('create_sale'), ' class="create_sale" ');  
		//$edit_link      = anchor('deliveries/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_delivery'), ' class="edit_delivery"');
        $delete_link      = "<a href='#' class='po delete_delivery' title='<b>" . lang("delete_delivery") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('deliveries/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_delivery') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
				. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
				. lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $detail_link . '</li>
                <li>' . $payments_link . '</li>
                <li class="add_payment">' . $add_payment_link . '</li>
				<li>' . $convert_link . '</li>
				<li>' . $delivery_note . '</li>
			    <li>' . $delivery_note1 . '</li>
			    <li>' . $delivery_voucher . '</li>
			    <li>' . $pdf_link . '</li>
				<li>' . $delete_link . '</li>
			</ul>
		</div></div>';
        $this->load->library('datatables');
        $this->datatables
            ->select("
                {$this->db->dbprefix('deliveries')}.id as id, 
                {$this->db->dbprefix('deliveries')}.date, 
                {$this->db->dbprefix('deliveries')}.do_reference_no, 
                {$this->db->dbprefix('deliveries')}.sale_reference_no, 
                {$this->db->dbprefix('deliveries')}.so_reference_no, 
                {$this->db->dbprefix('deliveries')}.customer, 
                {$this->db->dbprefix('companies')}.name, 
                {$this->db->dbprefix('deliveries')}.status,
                {$this->db->dbprefix('deliveries')}.money_collection,  
                {$this->db->dbprefix('deliveries')}.attachment
            ")
            ->from('deliveries')
			->join('companies','companies.id=deliveries.delivered_by','left')
            ->group_by('deliveries.id');
		if ($this->input->get("status")){
			$this->datatables->where("deliveries.status", trim($this->input->get("status")));
		}
		if ((!$this->Owner && !$this->Admin) && !$this->session->userdata('view_right')) {
			$this->datatables->where('deliveries.created_by', $this->session->userdata('user_id'));
		}
		if ($biller_id) {
            $this->datatables->where_in('deliveries.biller_id', $biller_id);
        }			
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function delete($id = null)
    {
        if(!isset($this->GP['sales-delete_delivery']) && (!$this->Admin && !$this->Owner)){
	        $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
	    }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->deliveries_model->deleteDelivery($id)) {
             $this->bpas->send_json(['error' => 0, 'msg' => lang('delivery_deleted')]);
        }
    }
	
    public function add($so_id = null, $inv_id = null)
    {
        if (!isset($this->GP['sales-add_delivery']) && (!$this->Admin && !$this->Owner)) {
	        $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
	    }
		if ($so_id || $inv_id) {
			if ($inv_id) {
				$sale = $this->sales_model->getInvoiceByID($inv_id);
				$warehouse_id = $sale->warehouse_id;
			} else {
				$sale_order   = $this->sales_order_model->getSaleOrderByID($so_id);
				$warehouse_id = $sale_order->warehouse_id;
			}
		}
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			$so_id  = $this->input->post('sale_order_id');
			$inv_id = $this->input->post('sale_id');
			if ($inv_id) {
				$sale = $this->sales_model->getInvoiceByID($inv_id);
			} else {
				$sale_order = $this->sales_order_model->getSaleOrderByID($so_id);
			}
			$warehouse_id     = (isset($sale) && $sale->warehouse_id) ? $sale->warehouse_id : $sale_order->warehouse_id;
			$customer_id      = $this->input->post('customer');
			$note             = $this->input->post('note');
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer         = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
			$biller_id        = (isset($sale) && $sale->biller_id) ? $sale->biller_id : $sale_order->biller_id;
			$biller_details   = $this->site->getCompanyByID($biller_id);
			$biller           = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$reference_no     = $this->input->post('do_reference_no') ? $this->input->post('do_reference_no') : $this->site->getReference('do', $biller_id);
            $percentage       = '%';
            $product_discount = 0; $product_tax = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$item_id                 = $_POST['product_id'][$r];
                $item_type               = $_POST['product_type'][$r];
                $item_code               = $_POST['product_code'][$r];
                $item_name               = $_POST['product_name'][$r];
                $item_option             = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != false && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price         = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price              = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity      = $_POST['quantity'][$r];
                $item_tax_rate           = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount           = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit               = $_POST['unit'][$r];
                $item_quantity           = $_POST['base_quantity'][$r];
				$parent_id               = $_POST['parent_id'][$r];
				$item_serial             = isset($_POST['serial_no'][$r]) ? $_POST['serial_no'][$r] : '';
				$item_sale_item_id       = (isset($_POST['sale_item_id'][$r]) && !empty($_POST['sale_item_id'][$r]) && $_POST['sale_item_id'][$r] != '' && $_POST['sale_item_id'][$r] != 'null') ? $_POST['sale_item_id'][$r] : null;
				$item_sale_order_item_id = (isset($_POST['sale_order_item_id'][$r]) && !empty($_POST['sale_order_item_id'][$r]) && $_POST['sale_order_item_id'][$r] != '' && $_POST['sale_order_item_id'][$r] != 'null') ? $_POST['sale_order_item_id'][$r] : null;
				if(isset($_POST['expired_data'][$r]) && $_POST['expired_data'][$r] && $_POST['expired_data'][$r] != '00/00/0000'){
					$expired_data = $this->bpas->fsd($_POST['expired_data'][$r]);
				} else {
					$expired_data = null;
				}
                if (isset($item_code)) {
                    $product_details = $item_type != 'manual' ? $this->site->getProductByCode($item_code) : null;
                    $pr_discount = 0;
                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->bpas->formatDecimal(((($this->bpas->formatDecimal($unit_price)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->bpas->formatDecimal($discount);
                        }
                    }
                    $unit_price = $this->bpas->formatDecimal($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_tax = 0;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = "";
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
					$unit = $this->site->getProductBaseUnit($item_unit);
                    $products[] = array(
                        'product_id'         => $item_id,
                        'product_code'       => $item_code,
                        'product_name'       => $item_name,
                        'product_type'       => $item_type,
                        'option_id'          => $item_option,
                        'net_unit_price'     => $item_net_price,
                        'unit_price'         => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity'           => $item_quantity,
                        'product_unit_id'    => $item_unit,
                        'product_unit_code'  => $unit->code,
                        'unit_quantity'      => $item_unit_quantity,
						'warehouse_id'       => $warehouse_id,
                        'item_tax'           => $pr_item_tax,
                        'tax_rate_id'        => $item_tax_rate,
                        'tax'                => $tax,
                        'discount' 		     => $item_discount,
                        'item_discount'      => $pr_item_discount,
                        'subtotal' 		     => $this->bpas->formatDecimal($subtotal),
                        'real_unit_price'    => $real_unit_price,
						'parent_id' 	     => $parent_id,
						'expiry' 		     => $expired_data,
						'serial_no' 	     => $item_serial,
						'sale_item_id'       => $item_sale_item_id,
						'sale_order_item_id' => $item_sale_order_item_id
                    );
                }
			}
            $driver = $this->site->getDriverByID($this->input->post('delivered_by'));

			$dlDetails = array(
				'date'              => $date,
				'biller_id'         => $biller_details->id,
				'biller'            => $biller,
				'customer_id'       => $customer_id,
				'customer'          => $customer,
				'warehouse_id'      => $warehouse_id,
				'sale_id'           => (isset($sale) ? $sale->id : null),
				'sale_order_id'     => (isset($sale_order) ? $sale_order->id : null),
				'do_reference_no'   => $reference_no,
				'sale_reference_no' => (isset($sale) ? $sale->reference_no : null),
				'so_reference_no'   => (isset($sale_order) ? $sale_order->reference_no : null),
				'address'           => $this->input->post('address'),
				'delivered_by'      => $this->input->post('delivered_by'),
				'received_by'       => $this->input->post('received_by'),
                'send_number'       => $this->input->post('send_number'),
				'note'              => $this->bpas->clear_tags($this->input->post('note')),
				'created_by'        => $this->session->userdata('user_id'),
			);
			if($so_id){
				$dlDetails['status'] = "pending";
			}
			if($inv_id){
				$dlDetails['status'] = "completed";
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
				$dlDetails['attachment'] = $photo;
			}
        }
        if ($this->form_validation->run() == true && $this->deliveries_model->addDelivery($dlDetails, $products)) {
            $this->session->set_flashdata('message', $this->lang->line("delivery_added"));
			$this->session->set_userdata('remove_dols', 1);
            admin_redirect('deliveries');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			if ($inv_id || $so_id) {
				if($so_id > 0){
					$this->data['inv'] = $this->sales_order_model->getSaleOrderByID($so_id);
					$inv_items = $this->deliveries_model->getAllSOItemsWithDeliveries($so_id);
				} elseif ($inv_id > 0) {
					$this->data['inv'] = $this->sales_model->getInvoiceByID($inv_id);
					$inv_items = $this->deliveries_model->getAllInvoiceItemsWithDeliveries($inv_id);
				}  
				if($this->data['inv']->delivery_status == "completed"){
					$this->session->set_flashdata('error', $this->lang->line("delivery_already_added"));
					redirect($_SERVER['HTTP_REFERER']);
				}
				krsort($inv_items);
				$c = rand(100000, 9999999);
				foreach ($inv_items as $item) {
					$row = $this->site->getProductByID($item->product_id);
					if (!$row) {
						$row = json_decode('{}');
						$row->tax_method = 0;
					} else {
						unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
					}
					if($so_id > 0){
                        $row->quantity = $item->quantity;
					} else if($inv_id > 0){
						$row->quantity = ($item->quantity + $item->foc);
					}
					if($item->delivered_quantity == ""){
						$item->delivered_quantity = 0;
					}
					$row->balanace_qty = (($item->quantity + $item->foc) - $item->delivered_quantity);
					$row->balance_unit_qty = $this->bpas->convertQty($item->product_id, $row->balanace_qty, $item->product_unit_id);
					$row->squantity = $this->bpas->convertQty($item->product_id, ($item->quantity + $item->foc), $item->product_unit_id);
					$row->dquantity = $this->bpas->convertQty($item->product_id, $item->delivered_quantity, $item->product_unit_id);
					if($row->balanace_qty == 0) continue;
					$row->qty       = $row->balanace_qty;
					$convert_unit   = false;     
					if($row->unit != $item->product_unit_id){
						$convert_unit = $this->bpas->convertUnit($item->product_id, $row->balanace_qty, false, $item->product_unit_id);
						$row->qty = $convert_unit['quantity'];
					}
					if(($row->balanace_qty - $item->foc) > 0){
						$row->sale_qty = $row->balanace_qty - $item->foc;
					} else {
						$row->sale_qty = 0;
					}
					$row->id              = $item->product_id;
					$row->sale_item_id    = (isset($item->sale_item_id) ? $item->sale_item_id : null);
					$row->sale_order_item_id = (isset($item->sale_order_item_id) ? $item->sale_order_item_id : null);
					$row->code            = $item->product_code;
					$row->name            = $item->product_name;
					$row->type            = $item->product_type;

                    $row->base_quantity   = $item->quantity;
                    $row->expiry          = $item->expiry;
                    $row->base_unit       = (!empty($row->unit) ? $row->unit : $item->product_unit_id);

                    // $row->base_unit_price = (!empty($row->price) ? $row->price : $item->unit_price);
                    // $row->unit            = $item->product_unit_id;
                    // $row->qty             = $item->unit_quantity;

                    $row->base_unit_price = $this->site->convertToBase($this->site->getUnitByID($item->product_unit_id), $item->real_unit_price);                    
                    $row->unit            = ($convert_unit ?  $convert_unit['unit_id'] : $item->product_unit_id);
                    $row->qty             = ($convert_unit ?  $convert_unit['quantity'] : $item->unit_quantity);                    

                    $row->quantity        = $item->quantity;
                    $row->discount        = $item->discount ? $item->discount : '0';
                    $row->item_tax        = $item->item_tax      > 0 ? $item->item_tax      / $item->quantity : 0;
                    $row->item_discount   = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                    $row->price           = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($row->item_discount));
                    $row->unit_price      = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($row->item_discount) + $this->bpas->formatDecimal($row->item_tax) : $item->unit_price + ($row->item_discount);
                    $row->real_unit_price = $row->price;
                    // $row->real_unit_price = $item->real_unit_price;

                    $row->cost            = isset($row->cost) ? $row->cost: 0;
					$row->tax_rate        = $item->tax_rate_id;
					$row->serial          = $item->serial_no;
					$row->option          = $item->option_id;
					$row->swidth          = $item->width;
					$row->sheight         = $item->height;
					$row->square          = $item->square;
					$row->expiry          = $item->expiry;
					$row->unit_name       = ($this->site->getUnitByID($item->product_unit_id) ? $this->site->getUnitByID($item->product_unit_id)->name : '');
					if($so_id > 0) {
						$options = $this->sales_order_model->getProductOptions($row->id, $item->warehouse_id);
					} else if($inv_id > 0) {
						$options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
					}
                    $product_expiries = false;
					$combo_items = false;
					if ($row->type == 'combo') {
						if($so_id > 0){
							$combo_items = $this->sales_order_model->getProductComboItems($row->id, $item->warehouse_id);
						} else if ($inv_id > 0){
							$combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
						}
					}
                    // $row->fup = 1;
					$units    = !empty($row->base_unit) ? $this->site->getUnitsByBUID($row->base_unit) : null;
					$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
					$ri       = $this->Settings->item_addition ? $row->id : $c;
					$pr[$ri]  = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'convert_unit' => $convert_unit,
                        'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'product_expiries' => $product_expiries);
					$c++;
				}
				$this->data['inv_items'] = json_encode($pr);
				$this->data['customer']  = $this->site->getCompanyByID($this->data['inv']->customer_id);
				$this->data['sale_id']   = $inv_id;
				$this->data['sale_order_id'] = $so_id;
			}	
			$this->data['billers']         = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
			$this->data['allUsers']        = $this->site->getAllUsers();
			$this->data['drivers']         = $this->site->getDriver();
			$this->data['saleorders']      = $this->site->getRefSaleOrders('approved');
            $this->data['sales']           = $this->site->getRefSales('completed');
			$this->data['do_reference_no'] = $this->site->getReference('do');

            $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('deliveries'), 'page' => lang('deliveries')), array('link' => '#', 'page' => lang('add_delivery')));
            $meta = array('page_title' => lang('add_delivery'), 'bc' => $bc);
            $this->page_construct('deliveries/add', $meta, $this->data);
        }
    }
	
	public function edit($id = null)
    {

        if(!isset($this->GP['edit']) && (!$this->Admin && !$this->Owner)){
	        $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
	    }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        if ($this->form_validation->run() == true) {
			
			$delivery_id = $this->input->post('delivery_id');
			$customer_id = $this->input->post('customer');
			$customer_details = $this->site->getCompanyByID($customer_id);
			$customer = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
			$i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
				$item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['base_quantity'][$r];
				$parent_id = $_POST['parent_id'][$r];
                if (isset($item_code)) {
                    $product_details = $item_type != 'manual' ? $this->sales_order_model->getProductByCode($item_code) : null;
                    $pr_discount = 0;
                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->bpas->formatDecimal(((($this->bpas->formatDecimal($unit_price)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->bpas->formatDecimal($discount);
                        }
                    }
                    $unit_price = $this->bpas->formatDecimal($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_tax = 0;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = "";
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {
                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->bpas->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->bpas->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }
                        } elseif ($tax_details->type == 2) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->bpas->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->bpas->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }
                            $item_tax = $this->bpas->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);
                    $products[] = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_unit_price' => $item_net_price,
                        'unit_price' => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->bpas->formatDecimal($subtotal),
                        'real_unit_price' => $real_unit_price,
						'parent_id' => $parent_id
                    );
                }
				
				$delivery = $this->deliveries_model->getDeliveryByID($id);

			}
			
			$dlDetails = array(
				'date' => $date,
				'customer_id' => $customer_id,
				'customer' => $customer,
				'address' => $this->input->post('address'),
				'delivered_by' => $this->input->post('delivered_by'),
				'received_by' => $this->input->post('received_by'),
				'note' => $this->bpas->clear_tags($this->input->post('note')),
				'created_by' => $this->session->userdata('user_id'),
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
				$dlDetails['attachment'] = $photo;
			}
			
        }
        if ($this->form_validation->run() == true && $this->deliveries_model->updateDelivery($delivery_id, $dlDetails, $products)) {
            $this->session->set_flashdata('message', $this->lang->line("delivery_updated"));
            redirect('deliveries');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $this->deliveries_model->getDeliveryByID($id);
            $inv_items = $this->deliveries_model->getAllDeliveryItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                    $row->tax_method = 0;
                } else {
                    unset($row->details, $row->product_details, $row->cost, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price);
                }
				
				$row->quantity = 0;
				if($item->sale_order_id){
					$pis = $this->site->getStockmoves($item->product_id, $item->warehouse_id, $item->option_id);
					if ($pis) {
						foreach ($pis as $pi) {
							$row->quantity += $pi->quantity_balance;
						}
					}
				}
				
                $row->id   = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
				$row->dquantity = $item->unit_quantity;
				$row->quantity = $item->quantity;
				$row->qty = $item->unit_quantity;
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                $row->unit = $item->product_unit_id;
                $row->discount = $item->discount ? $item->discount : '0';
                $row->price = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($item->item_discount / $item->unit_quantity));
                $row->unit_price = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($item->item_discount / $item->unit_quantity) + $this->bpas->formatDecimal($item->item_tax / $item->unit_quantity) : $item->unit_price + ($item->item_discount / $item->unit_quantity);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate = $item->tax_rate_id;
                $row->option = $item->option_id;
				$row->parent_id = $item->parent_id;
				$row->swidth = $item->width;
				$row->sheight = $item->height;
				$row->square = $item->square;
				
                $options = $this->sales_order_model->getProductOptions($row->id, $item->warehouse_id);
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getStockmoves($row->id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
						if($option->id == $item->option_id){
							 $option->quantity += $item->quantity;
						}
                    }
                }
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_order_model->getProductComboItems($row->id, $item->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }

                $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
				if($row->qty > 0){
					$ri = $this->Settings->item_addition ? $row->id : $c;
					$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
					$c++;
				}
            }
			$this->data['id'] = $id;
            $this->data['inv_items'] = json_encode($pr);
            $this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
			$this->data['allUsers'] = $this->site->getAllUsers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['paymentterms'] = $this->site->getAllPaymentTerms();
            $this->data['warehouses'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllWarehouses() : null;
            $this->data['do_reference_no'] = $this->site->getReference('do');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')), array('link' => site_url('deliveries'), 'page' => lang('deliveries')), array('link' => '#', 'page' => lang('edit_delivery')));
			$meta = array('page_title' => lang('edit_delivery'), 'bc' => $bc);
            $this->page_construct('deliveries/edit', $meta, $this->data);
        }
    }
	
	public function delivery_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_delivery');
                    foreach ($_POST['val'] as $id) {
                        $this->deliveries_model->deleteDelivery($id);
                    }
                    $this->session->set_flashdata('message', lang("deliveries_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
				
				if ($this->input->post('form_action') == 'create_sale') {
					$ids = array(); 
					$warehouse_id = 0;
                    foreach ($_POST['val'] as $id) {
                       $row = $this->deliveries_model->getDeliveryByID($id);
					   if($row->sale_id){
						   $this->session->set_flashdata('error', lang("cannot_delivery"));
						   redirect($_SERVER["HTTP_REFERER"]);
					   }
					   $ids[] = $id;
                    }
					redirect('sales/add?groups_delivery='.json_encode($ids));
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('deliveries'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('do_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('so_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('delivery'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $delivery = $this->deliveries_model->getDeliveryByID($id);
                        $delivered_by = (!empty($delivery) ? $this->site->getCompanyByID($delivery->delivered_by)->name : '');
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($delivery->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $delivery->do_reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, ($delivery->sale_reference_no));
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, ($delivery->so_reference_no));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $delivery->customer);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $delivered_by);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, strip_tags($delivery->address));
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($delivery->status));
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
                    $filename = 'deliveries_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_delivery_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function add_payment($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $delivery = $this->deliveries_model->getDeliveryByID($id);
        $sale = $this->sales_model->getInvoiceByID($id);
        $balance= $sale->grand_total - $sale->paid;
        if ($sale->payment_status == 'paid' && $sale->grand_total == $sale->paid) {
            $this->session->set_flashdata('error', lang('sale_already_paid'));
            $this->bpas->md();
        }

        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
  
        if ($this->form_validation->run() == true) {
  
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = [
            	'id'					=> $delivery->id,
                'clear_date'         	=> $date,
                'clear_reference_no' 	=> $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('do'),
                'clear_amount' 			=> $this->input->post('amount-paid') + $delivery->clear_amount,
                'balance'				=> $delivery->collection_amount - $this->input->post('amount-paid'),
                'paid_by'      			=> $this->input->post('paid_by'),
                'clear_note'        	=> $this->input->post('note'),
                'clear_created_by'  	=> $this->session->userdata('user_id'),
            ];
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo                 = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->deliveries_model->addPayment($payment)) {
          
            $this->session->set_flashdata('message', lang('payment_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if ($sale->sale_status == 'returned' && $sale->paid == $sale->grand_total) {
                $this->session->set_flashdata('warning', lang('payment_was_returned'));
                $this->bpas->md();
            }
            
   			$this->data['deliveries']        = $this->deliveries_model->getDeliveryByID($id);
   			$this->data['inv']             = $this->deliveries_model->getDeliveryByID($id);
            $this->data['payment_ref']     = '';//$this->site->getReference('pay');
            $this->data['modal_js']        = $this->site->modal_js();
            $this->load->view($this->theme . 'deliveries/add_payment', $this->data);
        }
    }

    public function view($id = null, $type = null)
    {
        if(!isset($this->GP['sales-deliveries']) && (!$this->Admin && !$this->Owner)){
	        $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
	    }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli = $this->deliveries_model->getDeliveryByID($id);
        $this->data['delivery'] = $deli;
        $this->data['biller'] = $this->site->getCompanyByID($deli->biller_id);
        $this->data['rows'] = $this->deliveries_model->getAllDeliveryItems($id);
        $this->data['user'] = $this->site->getUser($deli->created_by);
        $this->data['page_title'] = lang("delivery_order");
		if($this->Owner || $this->Admin || $this->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Delivery',$deli->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Delivery',$deli->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		if($type=="small"){
			$this->load->view($this->theme . 'deliveries/view_small', $this->data);
		}else{
			$this->load->view($this->theme . 'deliveries/view', $this->data);
		}
    }

    public function view_delivery($id = null)
    {
        if(!isset($this->GP['sales-deliveries']) && (!$this->Admin && !$this->Owner)){
	    	$this->session->set_flashdata('warning', lang('access_denied'));
        	redirect($_SERVER["HTTP_REFERER"]);
	    }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli = $this->deliveries_model->getDeliveryByID($id);
        $sale = ($deli->sale_id ? $this->sales_model->getInvoiceByID($deli->sale_id) : $this->sales_order_model->getInvoiceByID($deli->sale_order_id));
        if (!$sale) {
            $this->session->set_flashdata('error', lang('sale_not_found'));
            $this->bpas->md();
        }
        $this->data['delivery']    = $deli;
        $this->data['delivered_by']= $deli ? $this->site->getCompanyByID($deli->delivered_by) : null;
        $this->data['biller']      = $this->site->getCompanyByID($sale->biller_id);
        $this->data['rows']        = $this->deliveries_model->getAllDeliveryItems($id);
        $this->data['user']        = $this->site->getUser($deli->created_by);
        $this->data['page_title']  = lang('delivery_order');

        $this->load->view($this->theme . 'deliveries/view_delivery', $this->data);
    }

    public function shipping_request($biller_id = null)
    {
        if (!isset($this->GP['sales-deliveries']) && (!$this->Admin && !$this->Owner)) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }  
        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $user = $this->site->getUser($this->session->userdata('user_id'));
        if ($this->Settings->multi_biller) {
            $count = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
        } else {
            $count = $user->biller_id ? ((array) $user->biller_id) : null;
        }
        if ($this->Owner || $this->Admin || empty($count)) {
            $this->data['billers']   = $this->site->getAllCompanies('biller');
            $this->data['biller_id'] = $biller_id;
            $this->data['biller']    = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        } else {
            if (count($count) > 1) {
                $this->data['billers']   = $this->site->getAllCompanies('biller');
            } else {
                $this->data['billers']   = null;
            }
            $this->data['count_billers'] = $count;
            $this->data['user_biller']   = (isset($count) && count($count) == 1) ? $this->site->getCompanyByID($this->session->userdata('biller_id')) : null;
            $this->data['biller_id']     = $biller_id;
            $this->data['biller']        = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        }
        $this->data['warehouses']       = $this->site->getAllWarehouses();
        $this->data['count_warehouses'] = explode(',', $this->session->userdata('warehouse_id'));
        $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('deliveries'), 'page' => lang('shipping_request')), array('link' => '#', 'page' => lang('shipping_request')));
        $meta = array('page_title' => lang('shipping_request'), 'bc' => $bc);
        $this->page_construct('deliveries/shipping_request', $meta, $this->data);
    }

    public function getShippingRequest($biller_id = null)
    {
        if (!isset($this->GP['sales-deliveries']) && (!$this->Admin && !$this->Owner)) {
	        $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
	    }
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
            } else {
                $biller_id = $user->biller_id ? ((array) $user->biller_id) : null;
            }
        }
        $customer       = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller         = $this->input->get('biller') ? $this->input->get('biller') : null;
        $reference_no   = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $warehouse      = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $start_date     = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date       = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date . ' 00:00:00');
            $end_date   = $this->bpas->fld($end_date . ' 23:59:00');
        }
        $packagink_link    = anchor('admin/sales/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_delivery_link = anchor('admin/deliveries/add/0/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'));
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $packagink_link . '</li>
            <li>' . $add_delivery_link . '</li> 
        </ul>
        </div></div>';
        $this->load->library('datatables');
        $this->datatables->select("
                {$this->db->dbprefix('sales')}.id as id, 
                DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date,
                {$this->db->dbprefix('sales')}.reference_no,
                {$this->db->dbprefix('sales')}.biller, 
                {$this->db->dbprefix('warehouses')}.name as warehouse,
                {$this->db->dbprefix('sales')}.customer, 
                {$this->db->dbprefix('sales')}.shipping_request_phone, 
                {$this->db->dbprefix('sales')}.shipping_request_address, 
                {$this->db->dbprefix('sales')}.shipping_request_note, 
                {$this->db->dbprefix('sales')}.delivery_status
            ")
            ->from('sales')
            ->join('warehouses', 'warehouses.id = sales.warehouse_id', 'left')
            ->where('sales.store_sale !=', 1)
            ->where('sales.sale_status', 'completed')
            ->where(" ( {$this->db->dbprefix('sales')}.delivery_status != 'completed' && {$this->db->dbprefix('sales')}.shipping_request = 1 ) ");

        $this->datatables->where('sales.module_type','inventory');
        $this->datatables->where('sales.hide', 1);
        if ($reference_no) {
            $this->datatables->where('sales.reference_no', $reference_no);
        }
        if ($biller_id) {
            $this->datatables->where_in('sales.biller_id', $biller_id);
        }
        if ($biller) {
            $this->datatables->where('sales.biller_id', $biller);
        }
        if ($customer) {
            $this->datatables->where('sales.customer_id', $customer);
        }
        if ($warehouse) {
            $this->datatables->where('sales.warehouse_id', $warehouse);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->datatables->where($this->db->dbprefix('sales') . '.pos !=', 1);
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }

    public function shipping_request_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('shipping_request'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('no'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('status'));
                    $row = 2;
                    $i   = 1;
                    foreach ($_POST['val'] as $id) {
                        $sale = $this->sales_model->getInvoiceByID($id);
                        $warehouse = $this->site->getWarehouseByID($sale->warehouse_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $i);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($sale->date));
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sale->reference_no);                        
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sale->biller);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $warehouse->name);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sale->customer);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sale->shipping_request_phone);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $sale->shipping_request_address);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $sale->shipping_request_note);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, lang($sale->delivery_status));
                        $row++;
                        $i++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
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
                    $filename = 'shipping_request_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                } else {
                    $this->session->set_flashdata('error', lang('no_shipping_request_selected'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_shipping_request_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
}
