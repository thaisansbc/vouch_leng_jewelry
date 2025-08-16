<?php defined('BASEPATH') or exit('No direct script access allowed');

class Repairs extends MY_Controller
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
        $this->load->library('form_validation');
        $this->load->admin_model('repairs_model');
        $this->digital_upload_path = 'files/';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
        $this->data['linear_button'] = true;
    }

    public function index($warehouse_id = null, $biller_id = NULL, $receive_status = null)
    {
        $this->bpas->checkPermissions();
        $repair_id = $this->input->get('id') ? $this->input->get('id') : null;
        $this->data['repair_id'] = $repair_id;
        $this->data['receive_status'] = $receive_status;
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('repairs')));
		$meta = array('page_title' => lang('repairs'), 'bc' => $bc);
        $this->page_construct('repairs/index', $meta, $this->data);
    }

    public function getRepairs($warehouse_id = null, $biller_id = NULL, $receive_status = null)
    {
        $repair_id = $this->input->get('id') ? $this->input->get('id') : null;
        $status = $this->input->get('status') ? $this->input->get('status') : null;
		$detail_link = anchor('repairs/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('repair_details'), ' class="view_repair"');
		$edit_link ='';
		if(($this->Admin || $this->Owner) || $this->GP['repairs-edit']){
			$edit_link = anchor('repairs/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_repair'), ' class="edit_repair"');
		}
		$create_sale_link = '';
		if(($this->Admin || $this->Owner) || $this->GP['sales-add']){
			$create_sale_link = anchor('sales/add/?repair_id=$1', '<i class="fa fa-heart"></i> ' . lang('create_sale'), ' class="create_sale"');
		}
		$delete_link = '';
		if(($this->Admin || $this->Owner) || $this->GP['repairs-delete']){
			$delete_link = "<a href='#' class='delete_repair po' title='<b>" . $this->lang->line("delete_repair") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('repairs/delete/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_repair') . "</a>";
        }
		$action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $detail_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $create_sale_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
				
        $this->load->library('datatables');
        $this->datatables
                ->select("repairs.id as id, date, reference_no, customer, repairs.phone, brands.name as brand, models.name as model, imei_number, receive_date, grand_total, repairs.status, attachment, DATEDIFF(sysdate(),receive_date) as days")
                ->from('repairs')
                ->join('brands','brands.id=repairs.brand_id','left')
                ->join('models','models.id=repairs.model_id','left');

	    if ($warehouse_id) {
            $this->datatables->where('repairs.warehouse_id', $warehouse_id);
        }
		if ($biller_id) {
            $this->datatables->where('repairs.biller_id', $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('repairs.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
			$this->datatables->where_in('repairs.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
        if($status){
            $this->datatables->where("repairs.status", $status);
        }
        if($receive_status){
			$this->datatables->where('DATE_SUB(receive_date, INTERVAL 2 DAY) <=', date("Y-m-d"));
			$this->datatables->where('repairs.status !=','sent');
		}
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        $this->datatables->add_column(null,"$1","days");
        $this->datatables->unset_column("days");
        echo $this->datatables->generate();
    }

    public function modal_view($repair_id = null)
    {
        $this->bpas->checkPermissions('index', true);
        if ($this->input->get('id')) {
            $repair_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->repairs_model->getRepairByID($repair_id);
        $this->data['rows'] = $this->repairs_model->getAllRepairItems($repair_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
		$this->data['brand'] = $this->repairs_model->getBrandByID($inv->brand_id);
		$this->data['model'] = $this->repairs_model->getModelByID($inv->model_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['machine_type'] = $this->repairs_model->getMachineTypeByID($inv->machine_type_id);
        $this->data['check'] = $this->repairs_model->getCheckByID($inv->check_id);
		if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Repair',$inv->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Repair',$inv->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
        $this->load->view($this->theme . 'repairs/modal_view', $this->data);
    }

    public function view($repair_id = null)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('id')) {
            $repair_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->repairs_model->getRepairByID($repair_id);
        $this->data['rows'] = $this->repairs_model->getAllRepairItems($repair_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
		$this->data['brand'] = $this->repairs_model->getBrandByID($inv->brand_id);
		$this->data['model'] = $this->repairs_model->getModelByID($inv->model_id);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('repairs'), 'page' => lang('repairs')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_repair_details'), 'bc' => $bc);
        $this->page_construct('repairs/view', $meta, $this->data);

    }

    public function pdf($repair_id = null, $view = null, $save_bufffer = null)
    {
        $this->bpas->checkPermissions('pdf');
        if ($this->input->get('id')) {
            $repair_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->repairs_model->getRepairByID($repair_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->repairs_model->getAllRepairItems($repair_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
		$this->data['brand'] = $this->repairs_model->getBrandByID($inv->brand_id);
		$this->data['model'] = $this->repairs_model->getModelByID($inv->model_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $name = $this->lang->line("repair") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'repairs/pdf', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'repairs/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->bpas->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->bpas->generate_pdf($html, $name);
        }
    }

    public function combine_pdf($repairs_id)
    {
        $this->bpas->checkPermissions('pdf');
        foreach ($repairs_id as $repair_id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->repairs_model->getRepairByID($repair_id);
            if (!$this->session->userdata('view_right')) {
                $this->bpas->view_rights($inv->created_by);
            }
            $this->data['rows'] = $this->repairs_model->getAllRepairItems($repair_id);
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
			$this->data['brand'] = $this->repairs_model->getBrandByID($inv->brand_id);
			$this->data['model'] = $this->repairs_model->getModelByID($inv->model_id);
            $this->data['user'] = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;
            $html[] = array(
                'content' => $this->load->view($this->theme . 'repairs/pdf', $this->data, true),
                'footer' => '',
            );
        }
        $name = lang("repairs") . ".pdf";
        $this->bpas->generate_pdf($html, $name);
    }

    public function add($check_id = false)
    {
        $this->bpas->checkPermissions('add');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('repair',$biller_id);
            if ($this->Owner || $this->Admin || $this->bpas->GP['repairs-date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $status = $this->input->post('status');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
			$check_reference_no = $this->input->post('check_reference_no');
			$phone = $this->input->post('phone');
			$brand = $this->input->post('brand');
			$model = $this->input->post('model');
			$machine_type = $this->input->post('machine_type');
			$imei_number = $this->input->post('imei_number');
			$receive_date = $this->input->post('receive_date');
            $note = $this->bpas->clear_tags($this->input->post('note'));
			$staff_note = $this->bpas->clear_tags($this->input->post('staff_note'));
            
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
				$item_comment = $_POST['product_comment'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->bpas->formatDecimalRaw($_POST['real_unit_price'][$r]);
                $unit_price = $this->bpas->formatDecimalRaw($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
                $item_warranty = $_POST['warranty'][$r];
                $technician = $_POST['technician'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
					$currency_rate = 1;
					if($this->config->item('product_currency')==true){
						$currency_rate = $_POST['currency_rate'][$r];
						$currency_code = $_POST['currency_code'][$r];
						if($currency_rate > 1){
							$real_unit_price = $real_unit_price / $currency_rate;
							$unit_price = $unit_price / $currency_rate;
							$item_discount = $item_discount / $currency_rate;
							$item_tax_rate = $item_tax_rate / $currency_rate;
						}
					}

					$product_details = $item_type != 'manual' ? $this->repairs_model->getProductByCode($item_code) : null;
                    $pr_discount = 0;
                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->bpas->formatDecimalRaw(((($this->bpas->formatDecimalRaw($unit_price)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->bpas->formatDecimalRaw($discount);
                        }
                    }

                    $unit_price = $this->bpas->formatDecimalRaw($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimalRaw($pr_discount * $item_unit_quantity);
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
                                $item_tax = $this->bpas->formatDecimalRaw((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->bpas->formatDecimalRaw((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }

                        } elseif ($tax_details->type == 2) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->bpas->formatDecimalRaw((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->bpas->formatDecimalRaw((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }

                            $item_tax = $this->bpas->formatDecimalRaw($tax_details->rate);
                            $tax = $tax_details->rate;

                        }
                        $pr_item_tax = $this->bpas->formatDecimalRaw($item_tax * $item_unit_quantity, 4);
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
                        'unit_price' => $this->bpas->formatDecimalRaw($item_net_price + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->bpas->formatDecimalRaw($subtotal),
                        'real_unit_price' => $real_unit_price,
                        'technician_id' => $technician,
                        'comment' => $item_comment,
                        'warranty' => $item_warranty,
						'currency_rate' => $currency_rate,
						'currency_code' => $currency_code
                    );
                    $total += $this->bpas->formatDecimalRaw(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->bpas->formatDecimalRaw(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->bpas->formatDecimalRaw($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;
            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }
            $total_tax = $this->bpas->formatDecimalRaw(($product_tax + $order_tax), 4); 
            $grand_total = $this->bpas->formatDecimalRaw(($total + $total_tax - $order_discount), 4);
			
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
				'phone' => $phone,
                'warehouse_id' => $warehouse_id,
				'brand_id' => $brand,
				'model_id' => $model,
				'machine_type_id' => $machine_type,
				'imei_number' => $imei_number,
				'receive_date' => $this->bpas->fld($receive_date),
				'note' => $note,
                'staff_note' => $staff_note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'grand_total' => $grand_total,
                'status' => $status,
                'created_by' => $this->session->userdata('user_id'),
                'check_id' => $check_reference_no,
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
        if ($this->form_validation->run() == true && $id = $this->repairs_model->addRepair($data, $products)) {
            $this->session->set_userdata('remove_rpls', 1);
            $this->session->set_flashdata('message', $this->lang->line("repair_added"));
            if($this->input->post('add_repair_next')){
				redirect('repairs/add');
			}else{
                if($id){
                    redirect('repairs/?id='.$id);
                }
				redirect('repairs');
			}
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if($check_id){
                $this->data['inv'] = $this->repairs_model->getCheckByID($check_id);
                $inv_items = $this->repairs_model->getCheckItems($check_id);
                krsort($inv_items);
				$c = rand(100000, 9999999);
				foreach ($inv_items as $item) {
                    $row = $this->repairs_model->getDiagnosticByID($item->diagnostic_id);
                    $row->id = $item->diagnostic_id;
                    $row->name = $item->name;
                    $row->symptom = $item->symptom;
                    $row->troubleshooting = $item->troubleshooting;
                    $row->characteristic = $item->characteristic;
                    $ri = $this->Settings->item_addition ? $row->id : $c;
					$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name, 'characteristic' => $row->characteristic, 'row' => $row);
                    $c++;
                }
                $this->data['inv_items'] = json_encode($pr);
				$this->data['id'] = $check_id;
            }else{
				$this->data['inv'] = false;
			}
            $this->data['check_id'] = $check_id; 
            $this->data['billers'] =  $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['rpnumber'] = '';
            $this->data['machine_types'] = $this->repairs_model->getAllMachineTypes();
            $this->data['checks'] = $this->repairs_model->getCheckStatus();
			$this->data['brands'] = $this->repairs_model->getAllBrands();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('repairs'), 'page' => lang('repairs')), array('link' => '#', 'page' => lang('add_repair')));
			$meta = array('page_title' => lang('add_repair'), 'bc' => $bc);
            $this->page_construct('repairs/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->bpas->checkPermissions('edit');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->repairs_model->getRepairByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required")); 
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        if ($this->form_validation->run() == true) {
			
			$biller_id = $this->input->post('biller');
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('repair',$biller_id);
            if ($this->Owner || $this->Admin || $this->bpas->GP['repairs-date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $status = $this->input->post('status');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $phone = $this->input->post('phone');
			$brand = $this->input->post('brand');
			$model = $this->input->post('model');
			$machine_type = $this->input->post('machine_type');
			$imei_number = $this->input->post('imei_number');
			$receive_date = $this->input->post('receive_date');
            $note = $this->bpas->clear_tags($this->input->post('note'));
			$staff_note = $this->bpas->clear_tags($this->input->post('staff_note'));
			
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
				$item_comment = $_POST['product_comment'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->bpas->formatDecimalRaw($_POST['real_unit_price'][$r]);
                $unit_price = $this->bpas->formatDecimalRaw($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
                $item_warranty = $_POST['warranty'][$r];
                $technician = $_POST['technician'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
					$currency_rate = 1;
					if($this->config->item('product_currency')==true){
						$currency_rate = $_POST['currency_rate'][$r];
						$currency_code = $_POST['currency_code'][$r];
						if($currency_rate > 1){
							$real_unit_price = $real_unit_price / $currency_rate;
							$unit_price = $unit_price / $currency_rate;
							$item_discount = $item_discount / $currency_rate;
							$item_tax_rate = $item_tax_rate / $currency_rate;
						}
					}
					
					$product_details = $item_type != 'manual' ? $this->repairs_model->getProductByCode($item_code) : null;
                    $pr_discount = 0;
                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->bpas->formatDecimalRaw(((($this->bpas->formatDecimalRaw($unit_price)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->bpas->formatDecimalRaw($discount);
                        }
                    }

                    $unit_price = $this->bpas->formatDecimalRaw($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimalRaw($pr_discount * $item_unit_quantity);
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
                                $item_tax = $this->bpas->formatDecimalRaw((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->bpas->formatDecimalRaw((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }

                        } elseif ($tax_details->type == 2) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->bpas->formatDecimalRaw((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->bpas->formatDecimalRaw((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }
                            $item_tax = $this->bpas->formatDecimalRaw($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
                        $pr_item_tax = $this->bpas->formatDecimalRaw($item_tax * $item_unit_quantity, 4);
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
                        'unit_price' => $this->bpas->formatDecimalRaw($item_net_price + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->bpas->formatDecimalRaw($subtotal),
                        'real_unit_price' => $real_unit_price,
                        'technician_id' => $technician,
                        'comment' => $item_comment,
                        'warranty' => $item_warranty,
						'currency_rate' => $currency_rate,
						'currency_code' => $currency_code
                    );

                    $total += $this->bpas->formatDecimalRaw(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->bpas->formatDecimalRaw(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->bpas->formatDecimalRaw($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->bpas->formatDecimalRaw(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->bpas->formatDecimalRaw(($product_tax + $order_tax), 4); 
            $grand_total = $this->bpas->formatDecimalRaw(($total + $total_tax - $order_discount), 4);
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
				'phone' => $phone,
                'warehouse_id' => $warehouse_id,
				'brand_id' => $brand,
				'model_id' => $model,
				'machine_type_id' => $machine_type,
				'imei_number' => $imei_number,
				'receive_date' => $this->bpas->fld($receive_date),
				'note' => $note,
                'staff_note' => $staff_note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'grand_total' => $grand_total,
                'status' => $status,
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

        if ($this->form_validation->run() == true && $this->repairs_model->updateRepair($id, $data, $products)) {
            $this->session->set_userdata('remove_rpls', 1);
            $this->session->set_flashdata('message', $this->lang->line("repair_updated"));
            redirect('repairs');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $this->repairs_model->getRepairByID($id);
            $inv_items = $this->repairs_model->getAllRepairItems($id);
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
                $pis = $this->site->getStockmoves($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
				$row->fup = 1;
                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = $row->price ? $row->price : $item->real_unit_price;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->discount = $item->discount ? $item->discount : '0';
                $row->price = $this->bpas->formatDecimalRaw($item->net_unit_price + $this->bpas->formatDecimalRaw($item->item_discount / $item->quantity));
                $row->unit_price = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimalRaw($item->item_discount / $item->quantity) + $this->bpas->formatDecimalRaw($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate = $item->tax_rate_id;
                $row->option = $item->option_id;
                $row->technician_id = $item->technician_id;
				$row->comment = $item->comment;
				$options = $this->repairs_model->getProductOptions($row->id, $item->warehouse_id);
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
                $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $technicians = $this->repairs_model->getAllTechnicians();
                $ri = $this->Settings->item_addition ? $row->id : $c;
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row,'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options,'technicians'=>$technicians);
                $c++;
            }
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
			$this->data['brands'] = $this->repairs_model->getAllBrands();
			$this->data['machine_types'] = $this->repairs_model->getAllMachineTypes();
            $this->data['billers'] = $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('repairs'), 'page' => lang('repairs')), array('link' => '#', 'page' => lang('edit_repair')));
			$meta = array('page_title' => lang('edit_repair'), 'bc' => $bc);
            $this->page_construct('repairs/edit', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->bpas->checkPermissions('delete', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->repairs_model->deleteRepair($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("repair_deleted");die();
            }
            $this->session->set_flashdata('message', lang('repair_deleted'));
            redirect('welcome');
        }
    }

    public function items($warehouse_id = null, $biller_id = NULL)
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('repairs'), 'page' => lang('repairs')), array('link' => '#', 'page' => lang('items')));
		$meta = array('page_title' => lang('repairs'), 'bc' => $bc);
        $this->page_construct('repairs/items', $meta, $this->data);
    }

    public function getItems($warehouse_id = null, $biller_id = NULL)
    {
        $problem_status = $this->input->get('problem_status') ? $this->input->get('problem_status') : null;
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$user = $this->site->getUser($this->session->userdata('user_id'));
		}
		$detail_link = anchor('repairs/view_item/$1', '<i class="fa fa-file-text-o"></i> ' . lang('item_details'), ' class="view_item" data-toggle="modal" data-target="#myModal" ');
		$action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $detail_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
        $this->datatables
                 ->select("repair_items.id as id, 
                    date, 
                    reference_no, 
                    customer, 
                    repairs.phone, 
                    brands.name as brand, 
                    models.name as model, 
                    imei_number,
                    repair_items.product_name as problem,
                    repair_items.warranty,
                    repair_items.comment,
                    staff_note,
                    receive_date,
                    concat(bpas_users.first_name,' ',bpas_users.last_name) as technician,
                    repair_items.problem_status, 
                    attachment,
                    DATEDIFF(sysdate(),receive_date) as days")
                 ->from('repairs')
                 ->join('repair_items','repairs.id=repair_items.repair_id','right')
                 ->join('users','users.id=repair_items.technician_id','left')
				 ->join('brands','brands.id=repairs.brand_id','left')
                 ->join('models','models.id=repairs.model_id','left')
                 ->where("repairs.status !=", "sent");
                 
	    if ($warehouse_id) {
            $this->datatables->where('repairs.warehouse_id', $warehouse_id);
        }
		if ($biller_id) {
            $this->datatables->where('repairs.biller_id', $biller_id);
        }	
        if($problem_status){
            $this->datatables->where("repair_items.problem_status", $problem_status);
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('repairs.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
			$this->datatables->where_in('repairs.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            if($user->technician>0){
                $this->datatables->where('repair_items.technician_id', $this->session->userdata('user_id'));
            }else{
                $this->datatables->where('repairs.created_by', $this->session->userdata('user_id'));
            }
		} elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        $this->datatables->add_column(null,"$1","days");
        $this->datatables->unset_column("days");
        echo $this->datatables->generate();
    }

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id = $this->input->get('customer_id', true);
		$brand_id = $this->input->get('brand_id', true);
        $model_id = $this->input->get('model_id', true);
		$machine_type_id = $this->input->get('machine_type_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->bpas->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows = $this->repairs_model->getProductNames($sr, $warehouse_id, $brand_id, $model_id);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                unset($row->cost, $row->details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option = false;
                $row->quantity = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty = 1;
                $row->discount = '0';
                $row->warranty = $row->product_details;
                $options = $this->repairs_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->repairs_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $option_id = FALSE;
                }
                $row->option = $option_id;
                $pis = $this->site->getStockmoves($row->id, $warehouse_id, $row->option);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getStockmoves($row->id, $warehouse_id, $row->option);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
                        if ($option->quantity > $option_quantity) {
                            $option->quantity = $option_quantity;
                        }
                    }
                }
				$currency_rate = false;
				if($this->config->item('product_currency')==true){
					$currency_rate = $row->currency_rate;
					$row->price = $row->price * $currency_rate;
				}
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                if ($row->promotion && date('Y-m-d') >= $row->start_date && date('Y-m-d') <= $row->end_date) {
					$row->discount = (100-(($row->promo_price / $row->price) * 100)).'%';
                }else if($this->Settings->customer_price == 1 && $customer_price = $this->repairs_model->getCustomerPrice($row->id,$customer_id)){
					if (isset($customer_price) && $customer_price != false) {
						if($customer_price->price > 0){
							$row->price = $customer_price->price;
						}
					}
				} else if ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                } else if ($warehouse->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                }else if($machine_type = $this->repairs_model->getMachineTypeByID($machine_type_id)){
					if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $machine_type->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                }
                $machine_types = $this->repairs_model->getProblemPriceByMachineTypes();
				$row->price = $row->price + (($row->price * $customer_group->percent) / 100);
				$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $technicians = $this->repairs_model->getAllTechnicians();
				$row->real_unit_price = $row->price;
				$row->unit_price = $row->price;
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 
                    'row' => $row,'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options,'currency_rate' => $currency_rate, 'technicians'=>$technicians, 'machine_types'=>$machine_types);
                $r++;
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    public function repair_actions()
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
                        $this->repairs_model->deleteRepair($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("repairs_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('repairs'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('phone'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('brand'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('model'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('imei_number'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('receive_date'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('total'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $repair = $this->repairs_model->getRepairByID($id);
						$brand = $this->repairs_model->getBrandByID($repair->brand_id);
						$model = $this->repairs_model->getModelByID($repair->model_id);
						
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($repair->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $repair->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $repair->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $repair->customer);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $repair->phone);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $brand->name);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $model->name);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $repair->imei_number);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->hrld($repair->receive_date));
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $repair->total);
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $repair->status);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    
					$filename = 'repairs_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_repair_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function view_status($id)
    {
		$this->bpas->checkPermissions('view_status', true);
		if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$repair = $this->repairs_model->getRepairByID($id);
		$this->data['repair'] = $repair;
		$this->data['rows'] = $this->repairs_model->getAllRepairItems($id);
		$this->data['brand'] = $this->repairs_model->getBrandByID($repair->brand_id);
		$this->data['model'] = $this->repairs_model->getModelByID($repair->model_id);
		$this->data['created_by'] = $this->site->getUser($repair->created_by);
		$this->data['modal_js'] = $this->site->modal_js();
		$this->load->view($this->theme.'repairs/view_status', $this->data);
    }
	
	public function get_model()
	{
		$model_id = $this->input->get("model");
		$brand = $this->input->get("brand");
		$models = $this->repairs_model->getModelsByBrandID($brand);
		$opt_models = array(lang('select')." ".lang('model'));
		if ($models) {
			foreach($models as $model){
				$opt_models[$model->id] = $model->name;
			}
        }
        $opt = form_dropdown('model', $opt_models, (isset($_POST['model']) ? $_POST['model'] : $model_id), 'id="rpmodel" class="form-control"');
        echo json_encode(array("result" => $opt));
	}
	
	public function get_customer($id = false)
	{
		$customer_id = $this->input->get("customer");
		$customer = $this->site->getCompanyByID($customer_id);
		echo json_encode($customer);
	}
	
	public function get_phone($id = false)
	{
		$phone = $this->input->get("phone",true);
		$customer = $this->repairs_model->getCustomerByPhone($phone);
		if($customer){
			echo json_encode((int)$customer->id);
		}else{
			echo json_encode((int)$this->pos_settings->default_customer);
		}
	}
	
	public function get_membership_code($id = false)
	{
		$membership_code = $this->input->get("membership_code",true);
		$member_card = $this->repairs_model->getMemberCardCode($membership_code);
		if($member_card && (!$member_card->expiry || $member_card->expiry > date('Y-m-d'))){
			$customer = $this->site->getCompanyByID($member_card->customer_id);
			$data = array(
							"customer_id" => $customer->id,
							"phone" => $customer->phone,
							"status" => "success",
							"message" => lang("the_membership_code_you_enter_is_success"),
						);
			
		}else if($member_card && ($member_card->expiry < date('Y-m-d'))){
			$data = array(
							"customer_id" => null,
							"phone" => null,
							"status" => "expired",
							"message" => lang("the_membership_code_you_enter_is_expired"),
						);
		}else{
			$data = array(
							"customer_id" => null,
							"phone" => null,
							"status" => "error",
							"message" => lang("the_membership_code_you_enter_is_not_valid"),
						);
		}
		echo json_encode($data);
	}
	
    public function get_check_model()
	{
		$model_id = $this->input->get("model");
		$brand = $this->input->get("brand");
		$models = $this->repairs_model->getModelsByBrandID($brand);
		$opt_models = array(lang('select')." ".lang('model'));
		if ($models) {
			foreach($models as $model){
				$opt_models[$model->id] = $model->name;
			}
        }
        $opt = form_dropdown('model', $opt_models, (isset($_POST['model']) ? $_POST['model'] : $model_id), 'id="rcmodel" class="form-control"');
        echo json_encode(array("result" => $opt));
    }
    
    public function checks($warehouse_id = null, $biller_id = NULL)
    {
        $this->bpas->checkPermissions();
        $check_id = $this->input->get('id') ? $this->input->get('id') : null;
        $this->data['check_id'] = $check_id;
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses'] = $this->site->getWarehouses();
        $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $this->data['billers'] = $this->site->getBillers();
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('repairs'), 'page' => lang('repairs')), array('link' => '#', 'page' => lang('checks')));
        $meta = array('page_title' => lang('checks'), 'bc' => $bc);
        $this->page_construct('repairs/checks', $meta, $this->data);
    }

    public function getChecks($warehouse_id = null, $biller_id = NULL)
    {
        $status = $this->input->get('status')?$this->input->get('status'):null;
        $check_id = $this->input->get('id')?$this->input->get('id'):null;
        $detail_link = anchor('repairs/view_check/$1', '<i class="fa fa-file-text-o"></i> ' . lang('check_details'), ' class="view_check"');
        $add_repair_link ='';
        if(($this->Admin || $this->Owner) || $this->GP['repairs-add']){
            $add_repair_link = anchor('repairs/add/$1', '<i class="fa fa-plus-circle"></i> ' . lang('add_repair'), ' class="add_repair"');
        }
        $edit_link ='';
        if(($this->Admin || $this->Owner) || $this->GP['repairs-edit_check']){
            $edit_link = anchor('repairs/edit_check/$1', '<i class="fa fa-edit"></i> ' . lang('edit_check'), ' class="edit_check"');
        }
        $delete_link = '';
        if(($this->Admin || $this->Owner) || $this->GP['repairs-delete_check']){
            $delete_link = "<a href='#' class='delete_check po' title='<b>" . $this->lang->line("delete_check") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('repairs/delete_check/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_check') . "</a>";
        }
        
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $detail_link . '</li>
                        <li>' . $add_repair_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
                
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                 ->select("repair_checks.id as id, date, reference_no, repair_reference_no, customer, repair_checks.phone, brands.name as brand, models.name as model, imei_number, note, repair_checks.status")
                 ->from('repair_checks')
                 ->join('brands','brands.id=repair_checks.brand_id','left')
                 ->join('models','models.id=repair_checks.model_id','left')
                 ->where('repair_checks.warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                 ->select("repair_checks.id as id, date, reference_no, repair_reference_no, customer, repair_checks.phone, brands.name as brand, models.name as model, imei_number, note, repair_checks.status")
                 ->from('repair_checks')
                 ->join('brands','brands.id=repair_checks.brand_id','left')
                 ->join('models','models.id=repair_checks.model_id','left');
        }
        if ($warehouse_id) {
            $this->datatables->where('repair_checks.warehouse_id', $warehouse_id);
        }
        if ($biller_id) {
            $this->datatables->where('repair_checks.biller_id', $biller_id);
        }
        if($status){
            $this->datatables->where("repair_checks.status", $status);
        }
        if($check_id > 0){
            $this->datatables->where("repair_checks.id", $check_id);
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
            $this->datatables->where('repair_checks.biller_id', $this->session->userdata('biller_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
            $this->datatables->where_in('repair_checks.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function add_check()
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        if ($this->form_validation->run() == true) {

            $biller_id = $this->input->post('biller');
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('check',$biller_id);
            if ($this->Owner || $this->Admin || $this->bpas->GP['repairs-date_check'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $status = $this->input->post('status');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $phone = $this->input->post('phone');
            $brand = $this->input->post('brand');
            $model = $this->input->post('model');
            $machine_type = $this->input->post('machine_type');
            $imei_number = $this->input->post('imei_number');
            $note = $this->bpas->clear_tags($this->input->post('note'));
        
            $i = isset($_POST['diagnostic_id']) ? sizeof($_POST['diagnostic_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $diagnostic_id = $_POST['diagnostic_id'][$r];
                $name = $_POST['name'][$r];
                $characteristic = $_POST['characteristic'][$r];
                $symptom = $_POST['symptom'][$r];
                $troubleshooting = $_POST['troubleshooting'][$r];
                if (isset($diagnostic_id) && isset($name)) {
                    $products[] = array(
                        'diagnostic_id' => $diagnostic_id,
                        'name' => $name,
                        'characteristic' => $characteristic,
                        'symptom' => $symptom,
                        'troubleshooting' => $troubleshooting
                    );
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("diagnostics"), 'required');
            } else {
                krsort($products);
            }
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'phone' => $phone,
                'warehouse_id' => $warehouse_id,
                'brand_id' => $brand,
                'model_id' => $model,
                'machine_type_id' => $machine_type,
                'imei_number' => $imei_number,
                'note' => $note,
                'status' => $status,
                'created_by' => $this->session->userdata('user_id'),
            );
        }
        if ($this->form_validation->run() == true && $this->repairs_model->addCheck($data, $products)) {
            $this->session->set_userdata('remove_rcls', 1);
            $this->session->set_flashdata('message', $this->lang->line("check_added"));
            if($this->input->post('add_check_next')){
                redirect('repairs/add_check');
            }else{
                redirect('repairs/checks');
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['rcnumber'] = '';
            $this->data['billers'] =  $this->site->getBillers();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['machine_types'] = $this->repairs_model->getAllMachineTypes();
            $this->data['brands'] = $this->repairs_model->getAllBrands();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('repairs'), 'page' => lang('repairs')), array('link' => '#', 'page' => lang('add_check')));
            $meta = array('page_title' => lang('add_check'), 'bc' => $bc);
            $this->page_construct('repairs/add_check', $meta, $this->data);
        }
    }

    public function check_suggestions()
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id = $this->input->get('customer_id', true);
		$brand_id = $this->input->get('brand_id', true);
        $model_id = $this->input->get('model_id', true);
        $machine_type_id = $this->input->get('machine_type_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->bpas->analyze_term($term);
        $sr = $analyzed['term'];
        $rows = $this->repairs_model->getDiagnosticNames($term, $brand_id, $model_id);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $row->symptom = '';
                $row->troubleshooting = '';
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name, 'characteristic' => $row->characteristic,'row' => $row);
                $r++;
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	public function edit_check($id)
    {
        $this->bpas->checkPermissions();
        $check = $this->repairs_model->getCheckByID($id);
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        if ($this->form_validation->run() == true) {

            $biller_id = $this->input->post('biller');
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('check',$biller_id);
            if ($this->Owner || $this->Admin || $this->bpas->GP['repairs-date_check'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }

            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $status = $this->input->post('status');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $phone = $this->input->post('phone');
            $brand = $this->input->post('brand');
            $model = $this->input->post('model');
            $machine_type = $this->input->post('machine_type');
            $imei_number = $this->input->post('imei_number');
            $note = $this->bpas->clear_tags($this->input->post('note'));
            
            $i = isset($_POST['diagnostic_id']) ? sizeof($_POST['diagnostic_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $diagnostic_id = $_POST['diagnostic_id'][$r];
                $name = $_POST['name'][$r];
                $characteristic = $_POST['characteristic'][$r];
                $symptom = $_POST['symptom'][$r];
                $troubleshooting = $_POST['troubleshooting'][$r];
                if (isset($diagnostic_id) && isset($name)) {
                    $products[] = array(
                        'diagnostic_id' => $diagnostic_id,
                        'name' => $name,
                        'characteristic' => $characteristic,
                        'symptom' => $symptom,
                        'troubleshooting' => $troubleshooting
                    );
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("diagnostics"), 'required');
            } else {
                krsort($products);
            }
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'phone' => $phone,
                'warehouse_id' => $warehouse_id,
                'brand_id' => $brand,
                'model_id' => $model,
                'machine_type_id' => $machine_type,
                'imei_number' => $imei_number,
                'note' => $note,
                'status' => $status,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date("Y-m-d H:i")
            );
        }
        if ($this->form_validation->run() == true && $this->repairs_model->updateCheck($id, $data, $products)) {
            $this->session->set_userdata('remove_rcls', 1);
            $this->session->set_flashdata('message', $this->lang->line("check_updated"));
            redirect('repairs/checks');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['rcnumber'] = '';
            $this->data['inv'] = $check;
            $inv_items = $this->repairs_model->getCheckItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->repairs_model->getDiagnosticByID($item->diagnostic_id);
                $row->id = $item->diagnostic_id;
                $row->name = $item->name;
                $row->symptom = $item->symptom;
                $row->troubleshooting = $item->troubleshooting;
                $row->characteristic = $item->characteristic;
                $ri = $this->Settings->item_addition ? $row->id : $c;
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name, 'characteristic' => $row->characteristic, 'row' => $row);
                $c++;
            }
            $this->data['id'] = $id;
            $this->data['inv_items'] = json_encode($pr);
            $this->data['billers'] =  $this->site->getBillers();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['machine_types'] = $this->repairs_model->getAllMachineTypes();
            $this->data['brands'] = $this->repairs_model->getAllBrands();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('repairs'), 'page' => lang('repairs')), array('link' => '#', 'page' => lang('edit_check')));
            $meta = array('page_title' => lang('edit_check'), 'bc' => $bc);
            $this->page_construct('repairs/edit_check', $meta, $this->data);
        }
    }
	
	public function delete_check($id = null)
    {
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->repairs_model->deleteCheck($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("check_deleted");die();
            }
            $this->session->set_flashdata('message', lang('check_deleted'));
            redirect('welcome');
        }
    }
    
    public function check_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_check');
                    foreach ($_POST['val'] as $id) {
                        $this->repairs_model->deleteCheck($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("check_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                }elseif ($this->input->post('form_action') == 'export_excel') {
					
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('checks'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('repair_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('phone'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('brand'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('model'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('imei_number'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $check = $this->repairs_model->getCheckByID($id);
						$brand = $this->repairs_model->getBrandByID($check->brand_id);
						$model = $this->repairs_model->getModelByID($check->model_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($check->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $check->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $check->repair_reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $check->biller);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $check->customer);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $check->phone);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $brand->name);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $model->name);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $check->imei_number);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->remove_tag($check->note));
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $check->status);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'checks_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_check_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function view_check($check_id = null)
    {
        if ($this->input->get('id')) {
            $check_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->repairs_model->getCheckByID($check_id);
        $this->data['rows'] = $this->repairs_model->getCheckItems($check_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
		$this->data['brand'] = $this->repairs_model->getBrandByID($inv->brand_id);
		$this->data['model'] = $this->repairs_model->getModelByID($inv->model_id);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('repairs'), 'page' => lang('repairs')), array('link' => '#', 'page' => lang('view_check')));
        $meta = array('page_title' => lang('check_details'), 'bc' => $bc);
        $this->page_construct('repairs/view_check', $meta, $this->data);

    }

    public function modal_view_check($check_id = null)
    {
        if ($this->input->get('id')) {
            $check_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->repairs_model->getCheckByID($check_id);
        $this->data['rows'] = $this->repairs_model->getCheckItems($check_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
		$this->data['brand'] = $this->repairs_model->getBrandByID($inv->brand_id);
		$this->data['model'] = $this->repairs_model->getModelByID($inv->model_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['machine_type'] = $this->repairs_model->getMachineTypeByID($inv->machine_type_id);
        $this->data['inv'] = $inv;
		if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Check',$inv->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Check',$inv->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
        $this->load->view($this->theme . 'repairs/modal_view_check', $this->data);
    }

    public function problems($warehouse_id = null)
    {
        $this->bpas->checkPermissions('problems',true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses'] = $this->site->getWarehouses();
        $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('repairs'), 'page' => lang('repairs')), array('link' => '#', 'page' => lang('problems')));
        $meta = array('page_title' => lang('problems'), 'bc' => $bc);
        $this->page_construct('repairs/problems', $meta, $this->data);
    }

    public function getProblems($id =false)
    {
        $this->bpas->checkPermissions('problems',true);
        $detail_link = anchor('repairs/view_problem/$1', '<i class="fa fa-file-text-o"></i> ' . lang('problem_details'), ' class="view_problem" data-toggle="modal" data-target="#myModal" ');
        $edit_link ='';
        if(($this->Admin || $this->Owner) || $this->GP['repairs-problems']){
            $edit_link = anchor('repairs/edit_problem/$1', '<i class="fa fa-edit"></i> ' . lang('edit_problem'), ' class="edit_problem" data-toggle="modal" data-target="#myModal" ');
        }
        $delete_link = '';
        if(($this->Admin || $this->Owner) || $this->GP['repairs-problems']){
            $delete_link = "<a href='#' class='delete_problem po' title='<b>" . $this->lang->line("delete_problem") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('repairs/delete_problem/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_problem') . "</a>";
        }
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $detail_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
                
        $this->load->library('datatables');
        if($id){
            $this->datatables
                ->select("
                    products.id as id, 
                    products.code, 
                    products.name, 
                    brands.name as brand, 
                    models.name as model, 
                    cost, 
                    product_prices.price, 
                    product_details as warranty, 
                    IF({$this->db->dbprefix('products')}.status=1,'inactive','active') as status")
                ->from('products')
                ->join('categories','categories.id=products.category_id','left')
                ->join('product_prices','product_prices.product_id=products.id','left')
                ->join('price_groups','price_groups.id=product_prices.price_group_id','left')
                ->join('machine_types','machine_types.price_group_id=product_prices.price_group_id','left')
                ->join('brands','brands.id=products.brand','left')
                ->join('models','models.id=products.model','left')
                ->where('machine_types.id', $id)
                ->where('products.type','problem');
        }else{
            $this->datatables
                ->select("
                    products.id as id, 
                    products.code, 
                    products.name, 
                    brands.name as brand, 
                    models.name as model, 
                    cost, 
                    price, 
                    product_details as warranty, 
                    IF({$this->db->dbprefix('products')}.status=1,'inactive','active') as status")
                ->from('products')
                ->join('categories','categories.id=products.category_id','left')
                ->join('brands','brands.id=products.brand','left')
                ->join('models','models.id=products.model','left')
                ->where('products.type','problem');
        }
             $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function add_problem()
    {
        $this->bpas->checkPermissions('problems',true);
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang("code"), 'required|is_unique[bpas_products.code]');
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('price', lang("price"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $data = array(
                'code' => $this->input->post('code'),
                'barcode_symbology' => 'code128',
                'name' => $this->input->post('name'),
                'type' => 'problem',
                'brand' => $this->input->post('brand'),
                'model' => $this->input->post('model'),
                'cost' => $this->input->post('cost'),
                'price' => $this->input->post('price'),
                'product_details' => $this->input->post('warranty_note'),
                'track_quantity' => 0,
            );
            if(isset($_POST['pprice'])){
                foreach($_POST['pprice'] as $key => $pprice){
                    $items[] = array(
                            'price_group_id'=>$key,
                            'price' => $pprice,
                        );
                }
            }
            if($this->Settings->accounting == 1){
				$discount_acc = $this->input->post('discount_account');
				$sale_acc = $this->input->post('sale_account');
				$account = array(
					'type' => $this->input->post('type'),
					'discount_acc' => $discount_acc,
					'sale_acc' => $sale_acc,
					'expense_acc' => $this->input->post('expense_account'),
				);	
            }

        } elseif ($this->input->post('add_problem')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->repairs_model->addProblem($data, $items, $account)) {
			$this->session->set_flashdata('message', lang("problem_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if($this->Settings->accounting == 1){
                $this->data['discount_accounts'] = $this->site->getAccount(array('RE','EX','GL'));
                $this->data['sale_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'));
                $this->data['expense_accounts'] = $this->site->getAccount(array('CO','EX','OX','GL','AS'));
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['price_groups'] = $this->repairs_model->getPriceGroups();
            $this->data['brands'] = $this->repairs_model->getAllBrands();
            $this->load->view($this->theme . 'repairs/add_problem', $this->data);
        }
    }

    public function edit_problem($id)
    {
        $this->bpas->checkPermissions('problems',true);
        $this->load->helper('security');

        $valid = '';
        $problem = $this->repairs_model->getProblemByID($id);
        if($problem->code != $this->input->post('code')){
            $valid .= '|is_unique[bpas_products.code]';
        }
        $this->form_validation->set_rules('code', lang("code"), 'required'.$valid);
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('price', lang("price"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $data = array(
                'code' => $this->input->post('code'),
                'barcode_symbology' => 'code128',
                'name' => $this->input->post('name'),
                'type' => 'problem',
                'brand' => $this->input->post('brand'),
                'model' => $this->input->post('model'),
                'cost' => $this->input->post('cost'),
                'price' => $this->input->post('price'),
                'product_details' => $this->input->post('warranty_note'),
                'inactive' => $this->input->post('inactive'),
                'track_quantity' => 0,
            );
            if(isset($_POST['pprice'])){
                foreach($_POST['pprice'] as $key => $pprice){
                    $items[] = array(
                            'price_group_id'=>$key,
                            'price' => $pprice,
                        );
                }
            }
            if($this->Settings->accounting == 1){
				$discount_acc = $this->input->post('discount_account');
				$sale_acc = $this->input->post('sale_account');
				$account = array(
					'type' => $this->input->post('type'),
					'discount_acc' => $discount_acc,
					'sale_acc' => $sale_acc,
					'expense_acc' => $this->input->post('expense_account'),
				);	
            }
        } elseif ($this->input->post('add_problem')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->repairs_model->updateProblem($id,$data,$items, $account)) {
			$this->session->set_flashdata('message', lang("problem_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['id'] = $id;
            $this->data['problem'] = $problem;
            if($this->Settings->accounting == 1){
                $productAccount = $this->repairs_model->getProblemAccByProblemId($id);
                $this->data['discount_accounts'] = $this->site->getAccount(array('RE','EX','GL'),($productAccount?$productAccount->discount_acc:''));
                $this->data['sale_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'),($productAccount?$productAccount->sale_acc:''));
                $this->data['expense_accounts'] = $this->site->getAccount(array('CO','EX','OX','GL','AS'),($productAccount?$productAccount->expense_acc:''));
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['price_groups'] = $this->repairs_model->getPriceGroups();
            $this->data['brands'] = $this->repairs_model->getAllBrands();
            $this->load->view($this->theme . 'repairs/edit_problem', $this->data);
        }
    }

    public function delete_problem($id = null)
    {
        $this->bpas->checkPermissions('problems',true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->repairs_model->deleteProblem($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("problem_deleted");die();
            }
            $this->session->set_flashdata('message', lang('problem_deleted'));
            redirect('welcome');
        }
    }

    public function problem_actions()
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
                        $this->repairs_model->deleteProblem($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("problem_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                }elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('problem'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('brand'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('model'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('cost'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('price'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('warranty'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $problem = $this->repairs_model->getProblemByID($id);
                        $brand = $this->repairs_model->getBrandByID($problem->brand);
                        $model = $this->repairs_model->getModelByID($problem->model);
                        $machine_type_id = $_POST['id']?$_POST['id']:0;
                        $status = lang('active');
                        if($problem->inactive==1){
                            $status = lang('inacive');
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $problem->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $problem->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $brand->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $model->name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $problem->cost);
                        if($machine_type_id){
                            $machine_type = $this->repairs_model->getMachineTypeByID($machine_type_id);
                            $problem_price = $this->repairs_model->getProblemPrice($machine_type->price_group_id, $problem->product_id);
                            $this->excel->getActiveSheet()->SetCellValue('F' . $row, $problem_price->price);
                        }else{
                            $this->excel->getActiveSheet()->SetCellValue('F' . $row, $problem->price);
                        }
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $problem->product_details);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $status);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'problem_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_check_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function import_problems()
	{
        $this->bpas->checkPermissions('problems',true);
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if (isset($_FILES["userfile"])) {
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++){
						$code = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$name = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$brand = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
						$model = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
						$cost = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
						$price = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                        $warranty = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                        $status = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
						$final[] = array(
						  'code' => $code,
						  'name' => $name,
						  'brand' => $brand,
						  'model' => $model,
						  'cost' => $cost,
                          'price' => $price,
                          'warranty' => $warranty,
						  'status' => $status,
						);
					}
				}
				
                $rw = 2;
                foreach ($final as $csv_pr) {
                    $status = trim($csv_pr['status']);
                    $problem_details = $this->repairs_model->getProblemByCode(trim($csv_pr['code']));
                    if($problem_details){
                        $this->session->set_flashdata('error', lang("problem_code_duplicate").' '.$csv_pr['code']);
                        $this->bpas->md();
                    }
                    $brand_details = $this->repairs_model->getBrandByCode(trim($csv_pr['brand']));
                    if(!$brand_details){
                        $this->session->set_flashdata('error', lang("brand_code_not_found").' '.$csv_pr['brand']);
                        $this->bpas->md();
                    }
                    $model_details = $this->repairs_model->getModelByCode(trim($csv_pr['model']));
                    if(!$model_details){
                        $this->session->set_flashdata('error', lang("model_code_not_found").' '.$csv_pr['model']);
                        $this->bpas->md();
                    }
                    if($status == 'active'){
                        $status = 0;
                    }else{
                        $status = 1;
                    }
					$pr_code[] = trim($csv_pr['code']);
					$pr_name[] = trim($csv_pr['name']);
					$pr_brand[] = (int)$brand_details->id;
					$pr_model[] = (int)$model_details->id;
					$pr_cost[] = trim($csv_pr['cost']);
					$pr_price[] = trim($csv_pr['price']);
                    $pr_warranty[] = trim($csv_pr['warranty']);
                    $pr_types[] = 'problem';
                    $pr_track_quantity[] = 0;
                    $pr_status[] = $status;
                    $rw++;
				}
            }
            $ikeys = array('code','name','brand','model', 'cost','price','product_details','type','track_quantity','inactive');
            $items = array();
            foreach (array_map(null,$pr_code,$pr_name,$pr_brand,$pr_model,$pr_cost,$pr_price, $pr_warranty, $pr_types, $pr_track_quantity, $pr_status) as $ikey => $value) {
                $items[] = array_combine($ikeys, $value);
            }
			
        }
        if ($this->form_validation->run() == true && $this->repairs_model->addProblems($items)) {
            $this->session->set_flashdata('message', lang("problems_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'repairs/import_problems', $this->data);
        }
    }

    public function view_problem($id)
    {
        $this->bpas->checkPermissions('problems',true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $problem = $this->repairs_model->getProblemByID($id);
        $this->data['problem'] = $problem;
        $this->data['brand'] = $this->repairs_model->getBrandByID($problem->brand);
        $this->data['model'] = $this->repairs_model->getModelByID($problem->model);
        $this->data['price_groups'] = $this->repairs_model->getPriceGroups();
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'repairs/view_problem', $this->data);
    }

    public function view_problem_items()
    {
        $brand_id = $this->input->get('brand_id');
        $model_id = $this->input->get('model_id');
        if(!$brand_id){
            $this->session->set_flashdata('error', lang('model_not_found'));
            $this->bpas->md();
        }
        if(!$model_id){
            $this->session->set_flashdata('error', lang('model_not_found'));
            $this->bpas->md();
        }
        $this->data['brand_id'] = $brand_id;
        $this->data['model_id'] = $model_id;
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'repairs/view_problem_items', $this->data);
    }

    public function getProblemItems()
    {
        $brand_id = $this->input->get('brand_id');
        $model_id = $this->input->get('model_id');
        $this->load->library('datatables');
        $this->datatables
                ->select(" 
                    products.code, 
                    products.name, 
                    brands.name as brand, 
                    models.name as model, 
                    cost, 
                    price, 
                    product_details as warranty,
                    products.id as id")
                ->from('products')
                ->join('brands','brands.id=products.brand','left')
                ->join('models','models.id=products.model','left')
                ->where('products.type','problem')
                ->where('inactive',0)
                ->where('brand', $brand_id)
                ->where('model',$model_id);
        echo $this->datatables->generate();
    }

    public function diagnostics($warehouse_id = false)
    {
        $this->bpas->checkPermissions('diagnostics',true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses'] = $this->site->getWarehouses();
        $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('repairs'), 'page' => lang('repairs')), array('link' => '#', 'page' => lang('diagnostics')));
        $meta = array('page_title' => lang('diagnostics'), 'bc' => $bc);
        $this->page_construct('repairs/diagnostics', $meta, $this->data);
    }

    public function getDiagnostics()
    {
        $this->bpas->checkPermissions('diagnostics',true);
        $detail_link = anchor('repairs/view_diagnostic/$1', '<i class="fa fa-file-text-o"></i> ' . lang('diagnostic_details'), ' class="view_problem" data-toggle="modal" data-target="#myModal"');
        $edit_link ='';
        if(($this->Admin || $this->Owner) || $this->GP['repairs-diagnostics']){
            $edit_link = anchor('repairs/edit_diagnostic/$1', '<i class="fa fa-edit"></i> ' . lang('edit_diagnostic'), ' class="edit_diagnostic" data-toggle="modal" data-target="#myModal" ');
        }
        $delete_link = '';
        if(($this->Admin || $this->Owner) || $this->GP['repairs-diagnostics']){
            $delete_link = "<a href='#' class='delete_diagnostic po' title='<b>" . $this->lang->line("delete_diagnostic") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('repairs/delete_diagnostic/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_diagnostic') . "</a>";
        }
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $detail_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';

        $this->load->library('datatables');
        $this->datatables
            ->select("
                repair_diagnostics.id as id, 
                repair_diagnostics.code, 
                repair_diagnostics.name, 
                brands.name as brand,
                models.name as model,
                repair_diagnostics.characteristic,
                ")
            ->from('repair_diagnostics')
            ->join('brands','brands.id=brand','left')
            ->join('models','models.id=model','left')
            ->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function diagnostic_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('diagnostics');
                    foreach ($_POST['val'] as $id) {
                        $this->repairs_model->deleteDiagnostic($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("diagnostics_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                }elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('diagnostics'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('brand'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('model'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('characteristic'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $diagnostic = $this->repairs_model->getDiagnosticByID($id);
                        $brand = $this->repairs_model->getBrandByID($diagnostic->brand);
                        $model = $this->repairs_model->getModelByID($diagnostic->model);

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $diagnostic->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $diagnostic->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $brand->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $model->name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $diagnostic->characteristic);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'diagnostics_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_check_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function delete_diagnostic($id)
    {
        $this->bpas->checkPermissions('diagnostics',true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->repairs_model->deleteDiagnostic($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("diagnostic_deleted");die();
            }
            $this->session->set_flashdata('message', lang('diagnostic_deleted'));
            redirect('welcome');
        }
    }

    public function add_diagnostic()
    {
        $this->bpas->checkPermissions('diagnostics',true);
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang("code"), 'required|is_unique[bpas_repair_diagnostics.code]');
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('characteristic', lang("characteristic"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
                'code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'characteristic' => $this->input->post('characteristic'),
                'brand' => $this->input->post('brand'),
                'model' => $this->input->post('model'),
            );
        } elseif ($this->input->post('add_diagnostic')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->repairs_model->addDiagnostic($data)) {
			$this->session->set_flashdata('message', lang("diagnostic_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['brands'] = $this->repairs_model->getAllBrands();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'repairs/add_diagnostic', $this->data);
        }
    }

    public function edit_diagnostic($id)
    {
        $this->bpas->checkPermissions('diagnostics',true);
        $this->load->helper('security');

        $valid = '';
        $diagnostic = $this->repairs_model->getDiagnosticByID($id);
        if($diagnostic->code != $this->input->post('code')){
            $valid .= '|is_unique[bpas_repair_diagnostics.code]';
        }

        $this->form_validation->set_rules('code', lang("code"), 'required'.$valid);
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('characteristic', lang("characteristic"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
                'code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'characteristic' => $this->input->post('characteristic'),
                'brand' => $this->input->post('brand'),
                'model' => $this->input->post('model'),
            );
        } elseif ($this->input->post('edit_diagnostic')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->repairs_model->updateDiagnostic($id, $data)) {
			$this->session->set_flashdata('message', lang("diagnostic_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['id'] = $id;
            $this->data['diagnostic'] = $diagnostic;
            $this->data['brands'] = $this->repairs_model->getAllBrands();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'repairs/edit_diagnostic', $this->data);
        }
    }

    public function import_diagnostics()
	{
        $this->bpas->checkPermissions('diagnostics',true);
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if (isset($_FILES["userfile"])) {
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++){
						$code = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$name = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                        $characteristic = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                        $brand = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                        $model = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
						$final[] = array(
						  'code' => $code,
						  'name' => $name,
                          'characteristic' => $characteristic,
                          'brand' => $brand,
                          'model' => $model
						);
					}
				}
				
                $rw = 2;
                foreach ($final as $csv_pr) {
                    $brand_details = $this->repairs_model->getBrandByCode(trim($csv_pr['brand']));
                    if(!$brand_details){
                        $this->session->set_flashdata('error', lang("brand_code_not_found"));
                        $this->bpas->md();
                    }
                    $model_details = $this->repairs_model->getModelByCode(trim($csv_pr['model']));
                    if(!$model_details){
                        $this->session->set_flashdata('error', lang("model_code_not_found"));
                        $this->bpas->md();
                    }

					$pr_code[] = trim($csv_pr['code']);
                    $pr_name[] = trim($csv_pr['name']);
                    $pr_characteristic[] = trim($csv_pr['characteristic']);
                    $pr_brand[] = (int)$brand_details->id;
                    $pr_model[] = (int)$model_details->id;
                    $rw++;
				}
            }
            $ikeys = array('code','name','characteristic','brand','model');
            $items = array();
            foreach (array_map(null,$pr_code,$pr_name,$pr_characteristic, $pr_brand, $pr_model) as $ikey => $value) {
                $items[] = array_combine($ikeys, $value);
            }
        }

        if ($this->form_validation->run() == true && $this->repairs_model->addDiagnostics($items)) {
            $this->session->set_flashdata('message', lang("diagnostic_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'repairs/import_diagnostics', $this->data);
        }
    }

    public function view_diagnostic($id) 
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $diagnostic = $this->repairs_model->getDiagnosticByID($id);
        $this->data['diagnostic'] = $diagnostic;
        $this->data['brand'] = $this->repairs_model->getBrandByID($diagnostic->brand);
        $this->data['model'] = $this->repairs_model->getModelByID($diagnostic->model);
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'repairs/view_diagnostic', $this->data);
    }

    public function view_diagnostic_items()
    {
        $brand_id = $this->input->get('brand_id');
        $model_id = $this->input->get('model_id');
        if(!$brand_id){
            $this->session->set_flashdata('error', lang('model_not_found'));
            $this->bpas->md();
        }
        if(!$model_id){
            $this->session->set_flashdata('error', lang('model_not_found'));
            $this->bpas->md();
        }
        $this->data['brand_id'] = $brand_id;
        $this->data['model_id'] = $model_id;
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'repairs/view_diagnostic_items', $this->data);
    }

    public function getDiagnosticItems()
    {
        $brand_id = $this->input->get('brand_id');
        $model_id = $this->input->get('model_id');
        $this->load->library('datatables');
        $this->datatables
                ->select("
                    repair_diagnostics.code, 
                    repair_diagnostics.name, 
                    brands.name as brand, 
                    models.name as model,
                    repair_diagnostics.characteristic,
                    repair_diagnostics.id as id")
                ->from('repair_diagnostics')
                ->join('brands','brands.id=repair_diagnostics.brand','left')
                ->join('models','models.id=repair_diagnostics.model','left')
                ->where('brand', $brand_id)
                ->where('model',$model_id);
        echo $this->datatables->generate();
    }

    public function view_item($id) 
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $repair_item = $this->repairs_model->getRepairItemByID($id);
        $this->data['brand'] = $this->repairs_model->getBrandByID($repair_item->brand_id);
        $this->data['model'] = $this->repairs_model->getModelByID($repair_item->model_id);
		$this->data['created_by'] = $this->site->getUser($repair_item->created_by);
		$this->data['technician'] = $this->site->getUser($repair_item->technician_id);
		$this->data['repair_item'] = $repair_item;
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'repairs/view_item', $this->data);
    }
	
	public function update_status($id)
    {
		$this->bpas->checkPermissions('update_status', true);
        $this->form_validation->set_rules('status', lang("status"), 'required');
        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note = $this->bpas->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        }

        if ($this->form_validation->run() == true && $this->repairs_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'repairs/items');
        } else {
			$repair_item = $this->repairs_model->getRepairItemByID($id);
			$repair = $this->repairs_model->getRepairByID($repair_item->repair_id);
			if($repair->status == 'sent'){
				$this->session->set_flashdata('error', lang('repair_already_sent'));
				$this->bpas->md();
			}
			$this->data['repair_item'] = $repair_item;
			$this->data['brand'] = $this->repairs_model->getBrandByID($repair_item->brand_id);
			$this->data['model'] = $this->repairs_model->getModelByID($repair_item->model_id);
			$this->data['created_by'] = $this->site->getUser($repair_item->created_by);
			$this->data['technician'] = $this->site->getUser($repair_item->technician_id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'repairs/update_status', $this->data);
        }
    }
	
	public function item_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'export_excel') {
                    
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('repair_items'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('phone'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('brand'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('model'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('imei_number'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('problem'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('warranty'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('comment'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('staff_note'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('receive_date'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('technician'));
					$this->excel->getActiveSheet()->SetCellValue('O1', lang('status'));
					
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
						$repair_item = $this->repairs_model->getRepairItemByID($id);
                        $repair = $this->repairs_model->getRepairByID($repair_item->repair_id);
						$brand = $this->repairs_model->getBrandByID($repair->brand_id);
                        $model = $this->repairs_model->getModelByID($repair->model_id);
                        $user = $this->site->getUser($repair_item->technician_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($repair->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $repair->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $repair->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $repair->customer);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $repair->phone);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $brand->name);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $model->name);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $repair->imei_number);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $repair_item->product_name);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $repair_item->warranty);
						$this->excel->getActiveSheet()->SetCellValue('K' . $row, $repair_item->comment);
						$this->excel->getActiveSheet()->SetCellValue('L' . $row, $repair->staff_note);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->hrld($repair->receive_date));
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, ($user->first_name.' '.$user->last_name));
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, lang($repair_item->problem_status));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'repair_items_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_check_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function machine_types()
	{
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');      
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('repairs'), 'page' => lang('repairs')), array('link' => '#', 'page' => lang('machine_types')));
        $meta = array('page_title' => lang('machine_types'), 'bc' => $bc);
        $this->page_construct('repairs/machine_types', $meta, $this->data);
	}
	
	public function getMachineTypes()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
					machine_types.id as id,
					machine_types.name,
					price_groups.name as price_group")
            ->from("machine_types")	
			->join('price_groups','price_groups.id=machine_types.price_group_id','left')
			->add_column("Actions", "<center> <a class=\"tip\" title='" . $this->lang->line("view_problems") . "' href='" . site_url('repairs/view_problems/$1') . "'><i class=\"fa fa-eye\"></i></a> <a class=\"tip\" title='" . $this->lang->line("edit_machine_type") . "' href='" . site_url('repairs/edit_machine_type/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("edit_machine_type") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('repairs/delete_machine_type/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }
	
	public function machine_type_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->repairs_model->deleteMachineType($id);
                    }
                    $this->session->set_flashdata('message', lang("machine_type_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('machine_type'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->repairs_model->getMachineTypeByID($id);
						$pg = $this->repairs_model->getPriceGroupByID($sc->price_group_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pg->name);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'machine_types_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function delete_machine_type($id = NULL)
    {        
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->repairs_model->deleteMachineType($id)) {
            echo $this->lang->line("machine_type_deleted"); exit;
        } 
		$this->session->set_flashdata('message', lang("machine_type_deleted"));			 			
        redirect("repairs/machine_types");			
    }
	
	public function add_machine_type()
    {
        $this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
        if ($this->form_validation->run() == true) {			
            $data = array(
					'name' => $this->input->post('name'),
					'price_group_id' => $this->input->post('price_group')
				);			
        }else if($this->input->post('add_machine_type')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->repairs_model->addMachineType($data)) {
            $this->session->set_flashdata('message', lang("machine_type_added"));			 			
            redirect($_SERVER['HTTP_REFERER']);	
        } else {			
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['price_groups'] = $this->repairs_model->getAllPriceGroups();
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'repairs/add_machine_type', $this->data);
        }
    }
	
	public function edit_machine_type($id = false)
    {
		$machine_type = $this->repairs_model->getMachineTypeByID($id);
		$this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
        if ($this->form_validation->run() == true) {			
            $data = array(
					'name' => $this->input->post('name'),
					'price_group_id' => $this->input->post('price_group')
				);			
        }else if($this->input->post('edit_machine_type')){
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER']);
		}
        if ($this->form_validation->run() == true && $sid = $this->repairs_model->updateMachineType($id, $data)) {
             $this->session->set_flashdata('message', lang("machine_type_updated"));			 			
            redirect($_SERVER['HTTP_REFERER']);
        } else {
			$this->data['id'] = $id;
			$this->data['row'] = $machine_type;
			$this->data['price_groups'] = $this->repairs_model->getAllPriceGroups();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'repairs/edit_machine_type', $this->data);
        }
    }
    
    public function view_problems($id=false)
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');      
        $this->data['id'] = $id;
        $this->data['machine_type'] = $this->repairs_model->getMachineTypeByID($id);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('repairs'), 'page' => lang('repairs')), array('link' => '#', 'page' => lang('view_problems')));
        $meta = array('page_title' => lang('view_problems'), 'bc' => $bc);
        $this->page_construct('repairs/view_problems', $meta, $this->data);
    }
	
	public function get_machine_type($machine_type_id=false){
        if(!$machine_type_id){
            $machine_type_id = $this->input->get('machine_type_id');
        }
        $problem_prices = $this->repairs_model->getProblemPriceByMachineTypes($machine_type_id);
        if($problem_prices){
            echo json_encode($problem_prices);
        }
        return null;
    }

    public function get_product($product_id = NULL, $warehouse_id = NULL)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('product_id')) {
            $product_id = $this->input->get('product_id', TRUE);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', TRUE);
        }
        $brand_id = $this->input->get('brand_id', true);
        $model_id = $this->input->get('model_id', true);
        $machine_type_id = $this->input->get('machine_type_id', true);
        $customer_id = $this->input->get('customer_id', TRUE);
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $row = $this->repairs_model->getWHProduct($product_id, $warehouse_id);
        $option = false;
        if ($row) {
            unset($row->cost, $row->details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
            $row->quantity = 0;
            $row->item_tax_method = $row->tax_method;
            $row->qty = 1;
            $row->discount = '0';
            $row->warranty = $row->product_details;
            $options = $this->repairs_model->getProductOptions($row->id, $warehouse_id);
            if ($options) {
                $opt = $option_id && $r == 0 ? $this->repairs_model->getProductOptionByID($option_id) : $options[0];
                if (!$option_id || $r > 0) {
                    $option_id = $opt->id;
                }
            } else {
                $opt = json_decode('{}');
                $opt->price = 0;
                $option_id = FALSE;
            }
            $row->option = $option_id;
            $pis = $this->site->getStockmoves($row->id, $warehouse_id, $row->option);
            if ($pis) {
                foreach ($pis as $pi) {
                    $row->quantity += $pi->quantity_balance;
                }
            }
            if ($options) {
                $option_quantity = 0;
                foreach ($options as $option) {
                    $pis = $this->site->getStockmoves($row->id, $warehouse_id, $row->option);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $option_quantity += $pi->quantity_balance;
                        }
                    }
                    if ($option->quantity > $option_quantity) {
                        $option->quantity = $option_quantity;
                    }
                }
            }
            $currency_rate = false;
            if($this->config->item('product_currency')==true){
                $currency_rate = $row->currency_rate;
                $row->price = $row->price * $currency_rate;
            }
            $row->base_quantity = 1;
            $row->base_unit = $row->unit;
            $row->base_unit_price = $row->price;
            $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
            if ($row->promotion && date('Y-m-d') >= $row->start_date && date('Y-m-d') <= $row->end_date) {
                $row->discount = (100-(($row->promo_price / $row->price) * 100)).'%';
            }else if($this->Settings->customer_price == 1 && $customer_price = $this->repairs_model->getCustomerPrice($row->id,$customer_id)){
                if (isset($customer_price) && $customer_price != false) {
                    if($customer_price->price > 0){
                        $row->price = $customer_price->price;
                    }
                }
            } else if ($customer->price_group_id) {
                if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                    $row->price = $pr_group_price->price;
                }
            } else if ($warehouse->price_group_id) {
                if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                    $row->price = $pr_group_price->price;
                }
            }else if($machine_type = $this->repairs_model->getMachineTypeByID($machine_type_id)){
                if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $machine_type->price_group_id)) {
                    $row->price = $pr_group_price->price;
                }
            }
            $machine_types = $this->repairs_model->getProblemPriceByMachineTypes();
            $row->price = $row->price + (($row->price * $customer_group->percent) / 100);
            $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
            $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
            $technicians = $this->repairs_model->getAllTechnicians();
            $row->real_unit_price = $row->price;
            $row->unit_price = $row->price;
            $pr = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 
            'row' => $row,'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options,'currency_rate' => $currency_rate, 'technicians'=>$technicians, 'machine_types'=>$machine_types);
            $this->bpas->send_json($pr);
        } else {
            echo NULL;
        }
    }

    public function get_diagnostic()
    {
        $diagnostic_id = $this->input->get('diagnostic_id', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id = $this->input->get('customer_id', true);
		$brand_id = $this->input->get('brand_id', true);
        $model_id = $this->input->get('model_id', true);
        $machine_type_id = $this->input->get('machine_type_id', true);
        if (strlen($diagnostic_id) < 1 || !$diagnostic_id) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $row = $this->repairs_model->getWHDiagnostic($diagnostic_id, $brand_id, $model_id);
        if ($row) {
            $row->symptom = '';
            $row->troubleshooting = '';
            $pr = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name, 'characteristic' => $row->characteristic,'row' => $row);
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

}
