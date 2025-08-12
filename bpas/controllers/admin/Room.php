<?php defined('BASEPATH') or exit('No direct script access allowed');

class Room extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        //if ($this->Customer || $this->Supplier) {
        //     $this->session->set_flashdata('warning', lang('access_denied'));
        //     redirect($_SERVER["HTTP_REFERER"]);
        //}

        $this->load->admin_model('sales_model');
        $this->load->admin_model('pos_model');
        $this->load->admin_model('quotes_model');
        $this->load->admin_model('sales_order_model');
        $this->load->admin_model('accounts_model');
        $this->load->admin_model('table_model');
        $this->load->helper('text');
        $this->pos_settings = $this->pos_model->getSetting();
        $this->pos_settings->pin_code = $this->pos_settings->pin_code ? md5($this->pos_settings->pin_code) : NULL;
        $this->data['pos_settings'] = $this->pos_settings;
        $this->session->set_userdata('last_activity', now());
        $this->lang->admin_load('pos', $this->Settings->user_language);
        $this->load->library('form_validation');
    }


    /* ---------------------------------------------------------------------------------------------------- */
	public function bill_default() {
		$user=$this->session->userdata();
		$product = array(
		    'refer'      	=> $this->site->getReference('bill'),
			'date'      	=> date('Y-m-d H:i:s'),
			'start_date'    => date('Y-m-d H:i:s'),
			'customer_id'   => 1,
			'customer'   	=> 'Walk-in Customer',
			'count'    		=> 1,
			'order_discount_id' => $this->input->get('discount'),
			'order_tax_id'  => 1,
			'total'      	=> $this->input->get('price'),
			'biller_id'     => $user['biller_id'],
			'warehouse_id' 	=> $user['warehouse_id'],
			'created_by' 	=> $user['user_id'],
			'suspend_note'   => $this->input->get('room')
		);
		$result= $this->db->insert('suspended_bills',$product);
		if($result){
            $bill_id = $this->db->insert_id();
            $room_id = $this->input->get('room');

            $this->db->select('set_item');
            $this->db->from('suspended_note');
            $this->db->where('note_id', $room_id);
            $query = $this->db->get();

            if($query->num_rows()) {

                $data = $query->row();
                $get_product = $this->site->getProductByCode($data->set_item);

                $this->db->select('products.*,
                    combo_items.product_id as pro_id,
                    combo_items.quantity as set_qty,
                        combo_items.unit_price as set_price');
                $this->db->from('products');
                $this->db->join('combo_items','products.code = combo_items.item_code');
                $this->db->where('combo_items.product_id', $get_product->id);
                $query = $this->db->get();


                if($query->num_rows()) {   
                    $new_author = $query->result_array();
                    foreach ($new_author as $row) {
                        $data = array(
                            'suspend_id'     => $bill_id,
                            'product_id'     => $row['id'],
                            'product_code'   => $row['code'],
                            'product_name'   => $row['name'],
                            'quantity'          => $row['set_qty'],
                            'net_unit_price'    => $row['set_price'],
                            'unit_price'        => $row['set_price'],
                            'subtotal'          => $row['set_qty'] * $row['set_price'],
                            'real_unit_price'   => $row['set_price'],
                            'unit_quantity'     => $row['set_qty']
                        );
                        $this->db->insert('suspended_items',$data);
                    }        
                }
            }
			$data = array(
				'suspend_id'     => $bill_id,
				'product_id'     => 0,
				'product_code'   => "Time",
				'product_name'   => "Time Duration",
		//		'product_name'   => $this->input->get('room_name'),
				'quantity'   		=> 0.001,
				'net_unit_price' 	=> $this->input->get('price'),
				'unit_price' 		=> 1,
				'subtotal' 			=> $this->input->get('price'),
				'real_unit_price' 	=> $this->input->get('price'),
				'unit_quantity'  	=> 0.001
			);
			$this->db->insert('suspended_items',$data);

			//-------update bookig---
			$data2 = array(
				'booking'   => ""
			);
			$result= $this->db->update('suspended_note',$data2,
					array('note_id' => $this->input->get('room'))
				);
		}
		
	}
	public function booking_room(){
        $data2 = array(
            'booking'   => "booking",
            'description' => $this->input->get('pos_pin')

        );
        $result= $this->db->update('suspended_note',$data2,
                array('note_id' => $this->input->get('room'))
            );
        if($result){
            echo 'success';
        }
    }
    public function customer_qty(){
        $data2 = array(
            'booking'   => "booking",
            'customer_qty' => $this->input->get('pos_pin')
        );
        $result= $this->db->update('suspended_note',$data2,
                array('note_id' => $this->input->get('room'))
            );
        if($result){
            echo 'success';
        }
    }
    public function cancel_booking_room(){
        $data2 = array(
            'booking'   => "",
            'description' => '',
            'customer_qty' => 1
        );
        $result= $this->db->update('suspended_note',$data2,
                array('note_id' => $this->input->get('room'))
            );
        if($result){
            echo 'success';
        }
    }
	public function redirect_room($roomid){
		$room= $this->table_model->get_sus_id_byroom($roomid);
	//	$this->session->set_userdata('remove_posls', 1);
		admin_redirect('pos/index/'.$room->id);
		
	}
// 	public function change_room(){
//         $sus_id =$this->input->get('note_id');
//         $old_table =$this->input->get('old_table');
//         $new_table =$this->input->get('new_table');
//         $user=$this->session->userdata();
//         $table_name = $this->site->get_room_name($old_table);
//         $this->db->select('*');
//         $this->db->from('suspended_bills');
//    //     $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
//         $this->db->where('suspend_note', $new_table);
//         $query = $this->db->get();

//         if($query->num_rows()) {
//             $data = $query->row();

//             $this->db->select('suspended_bills.id as sus_id,suspended_bills.suspend_note,
//                 suspended_items.*');
//             $this->db->from('suspended_bills');
//             $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
//             $this->db->where('suspended_bills.suspend_note', $old_table);
//             $old_q = $this->db->get();

//             $old_result = $old_q->result_array();
//             foreach ($old_result as $row) {
//                 $product = array(
//                     'suspend_id'      => $data->id,
//                     'product_id'      => $row['product_id'],
//                     'product_code'    => $row['product_code'],
//                     'product_name'    => $row['product_name'],
//                     'product_type'    => $row['product_type'],
//                     'option_id'       => $row['option_id'],
//                     'net_unit_price'  => $row['net_unit_price'],
//                     'unit_price'      => $row['unit_price'],
//                     'quantity'        => $row['quantity'],
//                     'product_unit_id' => $row['product_unit_id'],
//                     'product_unit_code' => $row['product_unit_code'],
//                     'unit_quantity'   => $row['warehouse_id'],
//                     'warehouse_id'    => $row['item_tax'],
//                     'item_tax'        => $row['item_tax'],
//                     'tax_rate_id'     => $row['tax_rate_id'],
//                     'tax'             => $row['tax'],
//                     'discount'        => $row['discount'],
//                     'item_discount'   => $row['item_discount'],
//                     'subtotal'        => $row['subtotal'],
//                     'serial_no'       => $row['serial_no'],
//                     'real_unit_price' => $row['real_unit_price'],
//                     'comment'         => $table_name->name,
//                 );
//                 $this->db->insert('suspended_items', $product);    
//             }
//             if ($this->pos_model->deleteBill($row['sus_id'],($old_table))) {
//                 echo 'success';
//             }
                      
//         }else{
//             $product = array('suspend_note'   => $new_table);
//             $result= $this->db->update('suspended_bills',$product,
//                     array('id' => $this->input->get('note_id')));
//             if($result){
//                 echo 'success';
//             }
//         }
// 	}
	public function change_room(){
        $sus_id =$this->input->get('note_id');
        $old_table =$this->input->get('old_table');
        $new_table =$this->input->get('new_table');
        $user=$this->session->userdata();
        $table_name = $this->site->get_room_name($old_table);
        $this->db->select('*');
        $this->db->from('suspended_bills');
   //     $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
        $this->db->where('suspend_note', $new_table);
        $query = $this->db->get();

        if($query->num_rows()) {
            $data = $query->row();

            $this->db->select('suspended_bills.id as sus_id,suspended_bills.suspend_note,
                suspended_items.*');
            $this->db->from('suspended_bills');
            $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
            $this->db->where('suspended_bills.suspend_note', $old_table);
            $old_q = $this->db->get();

            $old_result = $old_q->result_array();
            foreach ($old_result as $row) {
                $product = array(
                    'suspend_id'         => $data->id,
                    'product_id'         => $row['product_id'],
                    'product_code'       => $row['product_code'],
                    'product_name'       => $row['product_name'],
                    'product_second_name'=> $row['product_second_name'],
                    'product_type'       => $row['product_type'],
                    'option_id'          => $row['option_id'],
                    'net_unit_price'     => $row['net_unit_price'],
                    'unit_price'         => $row['unit_price'],
                    'quantity'           => $row['quantity'],
                    'product_unit_id'    => $row['product_unit_id'],
                    'product_unit_code'  => $row['product_unit_code'],
                    'unit_quantity'      => $row['unit_quantity'],
                    'original_price'     => $row['original_price'],
                    'warehouse_id'       => $row['warehouse_id'],
                    'item_tax'           => $row['item_tax'],
                    'tax_rate_id'        => $row['tax_rate_id'],
                    'free'               => $row['free'],
                    'tax'                => $row['tax'],
                    'discount'           => $row['discount'],
                    'item_discount'      => $row['item_discount'],
                    'subtotal'           => $row['subtotal'],
                    'serial_no'          => $row['serial_no'],
                    'real_unit_price'    => $row['real_unit_price'],
                    'comment'            => $table_name->name,
                );
                $this->db->insert('suspended_items', $product);    
            }
            if ($this->pos_model->deleteBill($row['sus_id'],($old_table))) {
                echo 'success';
            }
                      
        }else{
            $product = array('suspend_note'   => $new_table);
            $result= $this->db->update('suspended_bills',$product,
                    array('id' => $this->input->get('note_id')));
            if($result){
                echo 'success';
            }
        }
	}

    public function index($sid = NULL)
    {	
        $this->bpas->checkPermissions('index', true, 'room');
	    // $user=$this->session->userdata();
        if (!$this->pos_settings->default_biller || !$this->pos_settings->default_customer || !$this->pos_settings->default_category) {
            $this->session->set_flashdata('warning', lang('please_update_settings'));
            admin_redirect('pos/settings');
        }
        if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
            $register_data = array('register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date);
            $this->session->set_userdata($register_data);
        } else {
            $this->session->set_flashdata('error', lang('register_not_open'));
            admin_redirect('pos/open_register');
        }

        $this->data['sid'] = $this->input->get('suspend_id') ? $this->input->get('suspend_id') : $sid;
        $did = $this->input->post('delete_id') ? $this->input->post('delete_id') : NULL;
        $suspend = $this->input->post('suspend') ? TRUE : FALSE;
        $count = $this->input->post('count') ? $this->input->post('count') : NULL;
        $floor_id = $this->input->get('floor') ? $this->input->get('floor') : NULL;
        if($floor_id === NULL){
            $floor_id = $this->pos_settings->show_floor;
        }else if($floor_id ==0){
            $floor_id = 0; 
        }
        $data2 = array('show_floor' => $floor_id);
        $this->db->update('pos_settings', $data2);
        $duplicate_sale = $this->input->get('duplicate') ? $this->input->get('duplicate') : NULL;

        //validate form input
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'trim|required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');

        if ($this->form_validation->run() == TRUE) {
			
            $date = date('Y-m-d H:i:s');
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total_items = $this->input->post('total_items');
            $sale_status = 'completed';
            $payment_status = 'due';
            $payment_term = 0;
            $due_date = date('Y-m-d', strtotime('+' . $payment_term . ' days'));
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->bpas->clear_tags($this->input->post('pos_note'));
            $staff_note = $this->bpas->clear_tags($this->input->post('staff_note'));
            $reference = $this->site->getReference('pos');

            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $digital = FALSE;
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_comment = $_POST['product_comment'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : NULL;
                $real_unit_price = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
               
                $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : NULL;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->pos_model->getProductByCode($item_code) : NULL;
                    // $unit_price = $real_unit_price;
                    if ($item_type == 'digital') {
                        $digital = TRUE;
                    }
                    $pr_discount = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price = $this->bpas->formatDecimal($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {

                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $product = array(
                        'product_id'      => $item_id,
                        'product_code'    => $item_code,
                        'product_name'    => $item_name,
                        'product_type'    => $item_type,
                        'option_id'       => $item_option,
                        'net_unit_price'  => $item_net_price,
                        'unit_price'      => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity'        => $item_quantity,
                        'product_unit_id' => $unit ? $unit->id : NULL,
                        'product_unit_code' => $unit ? $unit->code : NULL,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id'    => $warehouse_id,
                        'item_tax'        => $pr_item_tax,
                        'tax_rate_id'     => $item_tax_rate,
                        'tax'             => $tax,
                        'discount'        => $item_discount,
                        'item_discount'   => $pr_item_discount,
                        'subtotal'        => $this->bpas->formatDecimal($subtotal),
                        'serial_no'       => $item_serial,
                        'real_unit_price' => $real_unit_price,
                        'comment'         => $item_comment,
                    );

                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } elseif ($this->pos_settings->item_order == 1) {
                krsort($products);
            }
			$cur_rate = $this->pos_model->getExchange_rate('KHM');

            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $rounding = 0;
            if ($this->pos_settings->rounding) {
                $round_total = $this->bpas->roundNumber($grand_total, $this->pos_settings->rounding);
                $rounding = $this->bpas->formatMoney($round_total - $grand_total);
            }
			$currency =$this->input->post('kh_currenncy') =="" ? $this->input->post('en_currenncy') : $this->input->post('kh_currenncy');
			$currency_rate= ($currency =="usd") ? $cur_rate->rate : 1;
			

			$data = array('date'  => $date,
                'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->bpas->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'total_items'       => $total_items,
                'sale_status'       => $sale_status,
                'payment_status'    => $payment_status,
                'payment_term'      => $payment_term,
                'rounding'          => $rounding,
                'suspend_note'      => $this->input->post('suspend_note'),
                'currency' 			=> $currency,
                'other_cur_paid_rate' => $currency_rate,
                'pos'               => 1,
                'paid'              => $this->input->post('amount-paid') ? $this->input->post('amount-paid') : 0,
                'created_by'        => $this->session->userdata('user_id'),
                'hash'              => hash('sha256', microtime() . mt_rand()),
                );

            if (!$suspend) {
                $p = isset($_POST['amount']) ? sizeof($_POST['amount']) : 0;
                $paid = 0;
                for ($r = 0; $r < $p; $r++) {
                    if (isset($_POST['amount'][$r]) && !empty($_POST['amount'][$r]) && isset($_POST['paid_by'][$r]) && !empty($_POST['paid_by'][$r])) {
                        $amount = $this->bpas->formatDecimal($_POST['balance_amount'][$r] > 0 ? $_POST['amount'][$r] - $_POST['balance_amount'][$r] : $_POST['amount'][$r]);
                        if ($_POST['paid_by'][$r] == 'deposit') {
                            if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {
                                $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        }
                        if ($_POST['paid_by'][$r] == 'gift_card') {
                            $gc = $this->site->getGiftCardByNO($_POST['paying_gift_card_no'][$r]);
                            $amount_paying = $_POST['amount'][$r] >= $gc->balance ? $gc->balance : $_POST['amount'][$r];
                            $gc_balance = $gc->balance - $amount_paying;
                            $payment[] = array(
                                'date'         => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                'amount'       => $amount,
								'paid_amount'  => $_POST['paid_amount'][$r],
                                'currency_rate'=> $_POST['currency_rate'][$r],
                                'paid_by'      => $_POST['paid_by'][$r],
                                'cheque_no'    => $_POST['cheque_no'][$r],
                                'cc_no'        => $_POST['paying_gift_card_no'][$r],
                                'cc_holder'    => $_POST['cc_holder'][$r],
                                'cc_month'     => $_POST['cc_month'][$r],
                                'cc_year'      => $_POST['cc_year'][$r],
                                'cc_type'      => $_POST['cc_type'][$r],
                                'cc_cvv2'      => $_POST['cc_cvv2'][$r],
                                'created_by'   => $this->session->userdata('user_id'),
                                'type'         => 'received',
                                'note'         => $_POST['payment_note'][$r],
                                'pos_paid'     => $_POST['amount'][$r],
                                'pos_balance'  => $_POST['balance_amount'][$r],
                                'gc_balance'  => $gc_balance,
							//	'currency' 	   => $this->input->post('kh_currenncy')
                                );

                        } else {
                            $payment[] = array(
                                'date'         => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                'amount'       => $amount,
                                'paid_amount'  => $_POST['paid_amount'][$r],
                                'currency_rate'=> $_POST['currency_rate'][$r],
								'paid_by'      => $_POST['paid_by'][$r],
                                'cheque_no'    => $_POST['cheque_no'][$r],
                                'cc_no'        => $_POST['cc_no'][$r],
                                'cc_holder'    => $_POST['cc_holder'][$r],
                                'cc_month'     => $_POST['cc_month'][$r],
                                'cc_year'      => $_POST['cc_year'][$r],
                                'cc_type'      => $_POST['cc_type'][$r],
                                'cc_cvv2'      => $_POST['cc_cvv2'][$r],
                                'created_by'   => $this->session->userdata('user_id'),
                                'type'         => 'received',
                                'note'         => $_POST['payment_note'][$r],
                                'pos_paid'     => $_POST['amount'][$r],
                                'pos_balance'  => $_POST['balance_amount'][$r],
                            //    'currency' 	   => $this->input->post('kh_currenncy')
                                );

                        }

                    }
                }
            }
            if (!isset($payment) || empty($payment)) {
                $payment = array();
            }

            // $this->bpas->print_arrays($data, $products, $payment);
        }
        if ($this->form_validation->run() == TRUE && !empty($products) && !empty($data)) {
            if ($suspend) {
                if ($this->pos_model->suspendSale($data, $products, $did)) {
                    $this->session->set_userdata('remove_posls', 1);
                    $this->session->set_flashdata('message', $this->lang->line("sale_suspended"));
                    admin_redirect("table");
                }
            } else {
                if ($sale = $this->pos_model->addSale($data, $products, $payment, $did)) {
                    $this->session->set_userdata('remove_posls', 1);
                    $msg = $this->lang->line("sale_added");
                    if (!empty($sale['message'])) {
                        foreach ($sale['message'] as $m) {
                            $msg .= '<br>' . $m;
                        }
                    }
                    $this->session->set_flashdata('message', $msg);
                    $redirect_to = $this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sale['sale_id'];
                    if ($this->pos_settings->auto_print) {
                        if ($this->Settings->remote_printing != 1) {
                            $redirect_to .= '?print='.$sale['sale_id'];
                        }
                    }
                    admin_redirect($redirect_to);
                }
            }
        } else {
            $this->data['old_sale'] = NULL;
            $this->data['oid'] = NULL;
            if ($duplicate_sale) {
                if ($old_sale = $this->pos_model->getInvoiceByID($duplicate_sale)) {
                    $inv_items = $this->pos_model->getSaleItems($duplicate_sale);
                    $this->data['oid'] = $duplicate_sale;
                    $this->data['old_sale'] = $old_sale;
                    $this->data['message'] = lang('old_sale_loaded');
                    $this->data['customer'] = $this->pos_model->getCompanyByID($old_sale->customer_id);
                } else {
                    $this->session->set_flashdata('error', lang("bill_x_found"));
                    admin_redirect("table");
                }
            }
            $this->data['suspend_sale'] = NULL;
            if ($sid) {
                if ($suspended_sale = $this->pos_model->getOpenBillByID($sid)) {
                    $inv_items = $this->pos_model->getSuspendedSaleItems($sid);
                    $this->data['sid'] = $sid;
                    $this->data['suspend_sale'] = $suspended_sale;
                    $this->data['message'] = lang('suspended_sale_loaded');
                    $this->data['customer'] = $this->pos_model->getCompanyByID($suspended_sale->customer_id);
                    $this->data['reference_note'] = $suspended_sale->suspend_note;
                } else {
                    $this->session->set_flashdata('error', lang("bill_x_found"));
                    admin_redirect("pos");
                }
            }

            if (($sid || $duplicate_sale) && $inv_items) {
                    // krsort($inv_items);
                    $c = rand(100000, 9999999);
                    foreach ($inv_items as $item) {
                        $row = $this->site->getProductByID($item->product_id);
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->tax_method = 0;
                            $row->quantity = 0;
                        } else {
                            $category = $this->site->getCategoryByID($row->category_id);
                            $row->category_name = $category->name;
                            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                        }
                        $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $row->quantity += $pi->quantity_balance;
                            }
                        }
                        $row->id = $item->product_id;
                        $row->code = $item->product_code;
                        $row->name = $item->product_name;
                        $row->type = $item->product_type;
                        $row->quantity += $item->quantity;
                        $row->discount = $item->discount ? $item->discount : '0';
                        $row->price = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($item->item_discount / $item->quantity));
                        $row->unit_price = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($item->item_discount / $item->quantity) + $this->bpas->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                        $row->real_unit_price = $item->real_unit_price;
                        $row->base_quantity = $item->quantity;
                        $row->base_unit = isset($row->unit) ? $row->unit : $item->product_unit_id;
                        $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                        $row->unit = $item->product_unit_id;
                        $row->qty = $item->unit_quantity;
                        $row->tax_rate = $item->tax_rate_id;
                        $row->serial = $item->serial_no;
                        $row->option = $item->option_id;
                        $options = $this->pos_model->getProductOptions($row->id, $item->warehouse_id);

                        if ($options) {
                            $option_quantity = 0;
                            foreach ($options as $option) {
                                $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
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

                        $row->comment = isset($item->comment) ? $item->comment : '';
                        $row->ordered = 1;
                        $combo_items = false;
                        if ($row->type == 'combo') {
                            $combo_items = $this->pos_model->getProductComboItems($row->id, $item->warehouse_id);
                        }
                        $units = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $ri = $this->Settings->item_addition ? $row->id : $c;

                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                                'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                        $c++;
                    }

                    $this->data['items'] = json_encode($pr);

            } else {
                $this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
                $this->data['reference_note'] = NULL;
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['message'] = isset($this->data['message']) ? $this->data['message'] : $this->session->flashdata('message');

            // $this->data['biller'] = $this->site->getCompanyByID($this->pos_settings->default_biller);
            $this->data['suspend_note']= $this->table_model->getAll_suspend_note();
            
			
			 $this->data['floors'] = $this->site->getAllFloors();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['user'] = $this->site->getUser();
			
            $this->data["tcp"] = $this->pos_model->products_count($this->pos_settings->default_category);
		
       //     $this->data['products'] = $this->ajaxproducts($this->pos_settings->default_category);
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['subcategories'] = $this->site->getSubCategories($this->pos_settings->default_category);
            $this->data['printer'] = $this->pos_model->getPrinterByID($this->pos_settings->printer);
            $order_printers = json_decode($this->pos_settings->order_printers);
            
			$printers = array();
            if (!empty($order_printers)) {
                foreach ($order_printers as $printer_id) {
                    $printers[] = $this->pos_model->getPrinterByID($printer_id);
                }
            }
            $this->data['order_printers'] = $printers;
            $this->data['pos_settings'] = $this->pos_settings;

            if ($this->pos_settings->after_sale_page && $saleid = $this->input->get('print', true)) {
                if ($inv = $this->pos_model->getInvoiceByID($saleid)) {
                    $this->load->helper('pos');
                    if (!$this->session->userdata('view_right')) {
                        $this->bpas->view_rights($inv->created_by, true);
                    }
                    $this->data['rows'] = $this->pos_model->getAllInvoiceItems($inv->id);
                    $this->data['biller'] = $this->pos_model->getCompanyByID($inv->biller_id);
                    $this->data['customer'] = $this->pos_model->getCompanyByID($inv->customer_id);
                    $this->data['payments'] = $this->pos_model->getInvoicePayments($inv->id);
                    $this->data['return_sale'] = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : NULL;
                    $this->data['return_rows'] = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
                    $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : NULL;
                    $this->data['inv'] = $inv;
                    $this->data['print'] = $inv->id;
                    $this->data['created_by'] = $this->site->getUser($inv->created_by);
                }
            }
			$this->data['exchange_rate'] = $this->pos_model->getExchange_rate('KHM');
			$this->data['exchange_rate_bat_in'] = $this->pos_model->getExchange_rate('BAT');
			$this->data['exchange_rate_bat_out'] = $this->pos_model->getExchange_rate('BAT_o');
			
			$user = $this->site->getUser();
		if ($this->Owner || $this->Admin) {
				$this->data['kitchen_note'] = $this->table_model->getAll_suspend_note($floor_id);
				$this->data['available_room']= $this->table_model->available_room();
			}else{
				$warehouse_id = $user->warehouse_id;
				$this->data['kitchen_note'] = $this->table_model->getAll_suspend_note($floor_id, $warehouse_id);
				$this->data['available_room']= $this->table_model->available_room($warehouse_id);
            }
			$currency_id=$this->site->getCurrencyWarehouseByUserID($user->id);
			$curr=$this->site->getCurrencyByID($currency_id);
            $this->data['default_img'] = $curr->code;
            $this->data['pos_type'] = $this->pos_settings->pos_type;
			$this->load->view($this->theme . 'suspended/rooms', $this->data);
        }
    }

    public function view_bill()
    {
        $this->bpas->checkPermissions('index');
        $this->data['tax_rates'] = $this->site->getAllTaxRates();
        $this->load->view($this->theme . 'pos/view_bill', $this->data);
    }

    public function stripe_balance()
    {
        if (!$this->Owner) {
            return FALSE;
        }
        $this->load->admin_model('stripe_payments');

        return $this->stripe_payments->get_balance();
    }

    public function paypal_balance()
    {
        if (!$this->Owner) {
            return FALSE;
        }
        $this->load->admin_model('paypal_payments');

        return $this->paypal_payments->get_balance();
    }

    public function registers()
    {
        $this->bpas->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['registers'] = $this->pos_model->getOpenRegisters();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('open_registers')));
        $meta = array('page_title' => lang('open_registers'), 'bc' => $bc);
        $this->page_construct('pos/registers', $meta, $this->data);
    }

    public function open_register()
    {
        $this->bpas->checkPermissions('index');
        $this->form_validation->set_rules('cash_in_hand', lang("cash_in_hand"), 'trim|required|numeric');

        if ($this->form_validation->run() == TRUE) {
            $data = array(
                'date' => date('Y-m-d H:i:s'),
                'cash_in_hand' => $this->input->post('cash_in_hand'),
                'user_id'      => $this->session->userdata('user_id'),
                'status'       => 'open',
                );
        }
        if ($this->form_validation->run() == TRUE && $this->pos_model->openRegister($data)) {
            $this->session->set_flashdata('message', lang("welcome_to_pos"));
            admin_redirect("pos");
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('open_register')));
            $meta = array('page_title' => lang('open_register'), 'bc' => $bc);
            $this->page_construct('pos/open_register', $meta, $this->data);
        }
    }

    public function close_register($user_id = NULL)
    {
        $this->bpas->checkPermissions('index');
        if (!$this->Owner && !$this->Admin) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->form_validation->set_rules('total_cash', lang("total_cash"), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cheques', lang("total_cheques"), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cc_slips', lang("total_cc_slips"), 'trim|required|numeric');

        if ($this->form_validation->run() == TRUE) {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : NULL;
                $rid = $user_register ? $user_register->id : $this->session->userdata('register_id');
                $user_id = $user_register ? $user_register->user_id : $this->session->userdata('user_id');
            } else {
                $rid = $this->session->userdata('register_id');
                $user_id = $this->session->userdata('user_id');
            }
            $data = array(
                'closed_at'                => date('Y-m-d H:i:s'),
                'total_cash'               => $this->input->post('total_cash'),
                'total_cheques'            => $this->input->post('total_cheques'),
                'total_cc_slips'           => $this->input->post('total_cc_slips'),
                'total_cash_submitted'     => $this->input->post('total_cash_submitted'),
                'total_cheques_submitted'  => $this->input->post('total_cheques_submitted'),
                'total_cc_slips_submitted' => $this->input->post('total_cc_slips_submitted'),
                'note'                     => $this->input->post('note'),
                'status'                   => 'close',
                'transfer_opened_bills'    => $this->input->post('transfer_opened_bills'),
                'closed_by'                => $this->session->userdata('user_id'),
                );
        } elseif ($this->input->post('close_register')) {
            $this->session->set_flashdata('error', (validation_errors() ? validation_errors() : $this->session->flashdata('error')));
            admin_redirect("pos");
        }

        if ($this->form_validation->run() == TRUE && $this->pos_model->closeRegister($rid, $user_id, $data)) {
            $this->session->set_flashdata('message', lang("register_closed"));
            admin_redirect("welcome");
        } else {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : NULL;
                $register_open_time = $user_register ? $user_register->date : NULL;
                $this->data['cash_in_hand'] = $user_register ? $user_register->cash_in_hand : NULL;
                $this->data['register_open_time'] = $user_register ? $register_open_time : NULL;
            } else {
                $register_open_time = $this->session->userdata('register_open_time');
                $this->data['cash_in_hand'] = NULL;
                $this->data['register_open_time'] = NULL;
            }
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['ccsales'] = $this->pos_model->getRegisterCCSales($register_open_time, $user_id);
            $this->data['cashsales'] = $this->pos_model->getRegisterCashSales($register_open_time, $user_id);
            $this->data['chsales'] = $this->pos_model->getRegisterChSales($register_open_time, $user_id);
            $this->data['gcsales'] = $this->pos_model->getRegisterGCSales($register_open_time);
            $this->data['pppsales'] = $this->pos_model->getRegisterPPPSales($register_open_time, $user_id);
            $this->data['stripesales'] = $this->pos_model->getRegisterStripeSales($register_open_time, $user_id);
            $this->data['authorizesales'] = $this->pos_model->getRegisterAuthorizeSales($register_open_time, $user_id);
            $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time, $user_id);
            $this->data['refunds'] = $this->pos_model->getRegisterRefunds($register_open_time, $user_id);
            $this->data['cashrefunds'] = $this->pos_model->getRegisterCashRefunds($register_open_time, $user_id);
            $this->data['expenses'] = $this->pos_model->getRegisterExpenses($register_open_time, $user_id);
            $this->data['users'] = $this->pos_model->getUsers($user_id);
            $this->data['suspended_bills'] = $this->pos_model->getSuspendedsales($user_id);
            $this->data['user_id'] = $user_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/close_register', $this->data);
        }
    }

    public function getProductDataByCode($code = NULL, $warehouse_id = NULL)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('code')) {
            $code = $this->input->get('code', TRUE);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', TRUE);
        }
        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id', TRUE);
        }
        if (!$code) {
            echo NULL;
            die();
        }
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $row = $this->pos_model->getWHProduct($code, $warehouse_id);
        $option = false;
        if ($row) {
            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
            $row->item_tax_method = $row->tax_method;
            $row->qty = 1;
            $row->discount = '0';
            $row->serial = '';
            $options = $this->pos_model->getProductOptions($row->id, $warehouse_id);
            if ($options) {
                $opt = current($options);
                if (!$option) {
                    $option = $opt->id;
                }
            } else {
                $opt = json_decode('{}');
                $opt->price = 0;
            }
            $row->option = $option;
            $row->quantity = 0;
            $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
            if ($pis) {
                foreach ($pis as $pi) {
                    $row->quantity += $pi->quantity_balance;
                }
            }
            if ($row->type == 'standard' && (!$this->Settings->overselling && $row->quantity < 1)) {
                echo NULL; die();
            }
            if ($options) {
                $option_quantity = 0;
                foreach ($options as $option) {
                    $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
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
            if ($row->promotion) {
                $row->price = $row->promo_price;
            } elseif ($customer->price_group_id) {
                if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                    $row->price = $pr_group_price->price;
                }
            } elseif ($warehouse->price_group_id) {
                if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                    $row->price = $pr_group_price->price;
                }
            }
            $row->price = $row->price + (($row->price * $customer_group->percent) / 100);
            $row->real_unit_price = $row->price;
            $row->base_quantity = 1;
            $row->base_unit = $row->unit;
            $row->base_unit_price = $row->price;
            $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
            $row->comment = '';
            $combo_items = false;
            if ($row->type == 'combo') {
                $combo_items = $this->pos_model->getProductComboItems($row->id, $warehouse_id);
            }
            $units = $this->site->getUnitsByBUID($row->base_unit);
            $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

            $pr = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);

            $this->bpas->send_json($pr);
        } else {
            echo NULL;
        }
    }

    public function ajaxproducts($category_id = NULL, $brand_id = NULL)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('brand_id')) {
            $brand_id = $this->input->get('brand_id');
        }
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
            $category_id = $this->pos_settings->default_category;
        }
        if ($this->input->get('subcategory_id')) {
            $subcategory_id = $this->input->get('subcategory_id');
        } else {
            $subcategory_id = NULL;
        }
        if ($this->input->get('per_page') == 'n') {
            $page = 0;
        } else {
            $page = $this->input->get('per_page');
        }

        $this->load->library("pagination");

        $config = array();
        $config["base_url"] = base_url() . "pos/ajaxproducts";
        $config["total_rows"] = $this->pos_model->products_count($category_id, $subcategory_id, $brand_id);
        $config["per_page"] = $this->pos_settings->pro_limit;
        $config['prev_link'] = FALSE;
        $config['next_link'] = FALSE;
        $config['display_pages'] = FALSE;
        $config['first_link'] = FALSE;
        $config['last_link'] = FALSE;

        $this->pagination->initialize($config);

        $products = $this->pos_model->fetch_products($category_id, $config["per_page"], $page, $subcategory_id, $brand_id);
        $pro = 1;
        $prods = '<div>';
        if (!empty($products)) {
            foreach ($products as $product) {
                $count = $product->id;
                if ($count < 10) {
                    $count = "0" . ($count / 100) * 100;
                }
                if ($category_id < 10) {
                    $category_id = "0" . ($category_id / 100) * 100;
                }

                $prods .= "<button id=\"product-" . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' title=\"" . $product->name . "\" class=\"btn-prni btn-" . $this->pos_settings->product_button_color . " product pos-tip\" data-container=\"body\"><img src=\"" . base_url() . "assets/uploads/thumbs/" . $product->image . "\" alt=\"" . $product->name . "\" class='img-rounded' /><span>" . character_limiter($product->name, 40) . "</span></button>";

                $pro++;
            }
        }
        $prods .= "</div>";

        if ($this->input->get('per_page')) {
            echo $prods;
        } else {
            return $prods;
        }
    }

    public function ajaxcategorydata($category_id = NULL)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
            $category_id = $this->pos_settings->default_category;
        }

        $subcategories = $this->site->getSubCategories($category_id);
        $scats = '';
        if ($subcategories) {
            foreach ($subcategories as $category) {
                $scats .= "<button id=\"subcategory-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni subcategory\" ><img src=\"" . base_url() ."assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" class='img-rounded img-thumbnail' /><span>" . $category->name . "</span></button>";
            }
        }

        $products = $this->ajaxproducts($category_id);

        if (!($tcp = $this->pos_model->products_count($category_id))) {
            $tcp = 0;
        }

        $this->bpas->send_json(array('products' => $products, 'subcategories' => $scats, 'tcp' => $tcp));
    }

    public function ajaxbranddata($brand_id = NULL)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('brand_id')) {
            $brand_id = $this->input->get('brand_id');
        }

        $products = $this->ajaxproducts(FALSE, $brand_id);

        if (!($tcp = $this->pos_model->products_count(FALSE, FALSE, $brand_id))) {
            $tcp = 0;
        }

        $this->bpas->send_json(array('products' => $products, 'tcp' => $tcp));
    }

    /* ------------------------------------------------------------------------------------ */

    public function view($sale_id = NULL, $modal = NULL)
    {
        $this->bpas->checkPermissions('index');
		$user_id = $this->session->userdata('user_id');
        $currency_id=$this->site->getCurrencyWarehouseByUserID($user_id);
		$curr=$this->site->getCurrencyByID($currency_id);
		//echo $curr->code;
		
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->pos_model->getAllInvoiceItems($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer'] = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments'] = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos'] = $this->pos_model->getSetting();
        $this->data['barcode'] = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale'] = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : NULL;
        $this->data['inv'] = $inv;
        $this->data['sid'] = $sale_id;
        $this->data['modal'] = $modal;
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['printer'] = $this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title'] = $this->lang->line("invoice");
		
		$this->data['exchange_rate_bat_in'] = $this->pos_model->getExchange_rate('BAT');
		$this->data['exchange_rate_bat_out'] = $this->pos_model->getExchange_rate('BAT_o');
		
		if($curr->code =="BAT" || $curr->code =="BAT_o"){
			$this->load->view($this->theme . 'pos/view_bath_default', $this->data);
		}else{
			//$this->load->view($this->theme . 'pos/view_2_currency', $this->data);
			$this->load->view($this->theme . 'pos/view_3_currency', $this->data);
		}
    }

    public function register_details()
    {
        $this->bpas->checkPermissions('index');
        $register_open_time = $this->session->userdata('register_open_time');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales'] = $this->pos_model->getRegisterCCSales($register_open_time);
        $this->data['cashsales'] = $this->pos_model->getRegisterCashSales($register_open_time);
        $this->data['chsales'] = $this->pos_model->getRegisterChSales($register_open_time);
        $this->data['gcsales'] = $this->pos_model->getRegisterGCSales($register_open_time);
        $this->data['pppsales'] = $this->pos_model->getRegisterPPPSales($register_open_time);
        $this->data['stripesales'] = $this->pos_model->getRegisterStripeSales($register_open_time);
        $this->data['authorizesales'] = $this->pos_model->getRegisterAuthorizeSales($register_open_time);
        $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time);
        $this->data['refunds'] = $this->pos_model->getRegisterRefunds($register_open_time);
        $this->data['expenses'] = $this->pos_model->getRegisterExpenses($register_open_time);
        $this->load->view($this->theme . 'pos/register_details', $this->data);
    }

    public function today_sale()
    {
        if (!$this->Owner && !$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->bpas->md();
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales'] = $this->pos_model->getTodayCCSales();
        $this->data['cashsales'] = $this->pos_model->getTodayCashSales();
        $this->data['chsales'] = $this->pos_model->getTodayChSales();
        $this->data['pppsales'] = $this->pos_model->getTodayPPPSales();
        $this->data['stripesales'] = $this->pos_model->getTodayStripeSales();
        $this->data['authorizesales'] = $this->pos_model->getTodayAuthorizeSales();
        $this->data['totalsales'] = $this->pos_model->getTodaySales();
        $this->data['refunds'] = $this->pos_model->getTodayRefunds();
        $this->data['expenses'] = $this->pos_model->getTodayExpenses();
        $this->load->view($this->theme . 'pos/today_sale', $this->data);
    }

    public function check_pin()
    {
        $pin = $this->input->post('pw', TRUE);
        if ($pin == $this->pos_pin) {
            $this->bpas->send_json(array('res' => 1));
        }
        $this->bpas->send_json(array('res' => 0));
    }

    public function barcode($text = NULL, $bcs = 'code128', $height = 50)
    {
        return admin_url('products/gen_barcode/' . $text . '/' . $bcs . '/' . $height);
    }

    public function settings()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect("welcome");
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('pro_limit', $this->lang->line('pro_limit'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('pin_code', $this->lang->line('delete_code'), 'numeric');
        $this->form_validation->set_rules('category', $this->lang->line('default_category'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('customer', $this->lang->line('default_customer'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('biller', $this->lang->line('default_biller'), 'required|is_natural_no_zero');

        if ($this->form_validation->run() == TRUE) {

            $data = array(
                'pro_limit'                 => $this->input->post('pro_limit'),
                'pin_code'                  => $this->input->post('pin_code') ? $this->input->post('pin_code') : NULL,
                'default_category'          => $this->input->post('category'),
                'default_customer'          => $this->input->post('customer'),
                'default_biller'            => $this->input->post('biller'),
                'display_time'              => $this->input->post('display_time'),
                'receipt_printer'           => $this->input->post('receipt_printer'),
                'cash_drawer_codes'         => $this->input->post('cash_drawer_codes'),
                'cf_title1'                 => $this->input->post('cf_title1'),
                'cf_title2'                 => $this->input->post('cf_title2'),
                'cf_value1'                 => $this->input->post('cf_value1'),
                'cf_value2'                 => $this->input->post('cf_value2'),
                'focus_add_item'            => $this->input->post('focus_add_item'),
                'add_manual_product'        => $this->input->post('add_manual_product'),
                'customer_selection'        => $this->input->post('customer_selection'),
                'add_customer'              => $this->input->post('add_customer'),
                'toggle_category_slider'    => $this->input->post('toggle_category_slider'),
                'toggle_subcategory_slider' => $this->input->post('toggle_subcategory_slider'),
                'toggle_brands_slider'      => $this->input->post('toggle_brands_slider'),
                'cancel_sale'               => $this->input->post('cancel_sale'),
                'suspend_sale'              => $this->input->post('suspend_sale'),
                'print_items_list'          => $this->input->post('print_items_list'),
                'finalize_sale'             => $this->input->post('finalize_sale'),
                'today_sale'                => $this->input->post('today_sale'),
                'open_hold_bills'           => $this->input->post('open_hold_bills'),
                'close_register'            => $this->input->post('close_register'),
                'tooltips'                  => $this->input->post('tooltips'),
                'keyboard'                  => $this->input->post('keyboard'),
                'pos_printers'              => $this->input->post('pos_printers'),
                'java_applet'               => $this->input->post('enable_java_applet'),
                'product_button_color'      => $this->input->post('product_button_color'),
                'paypal_pro'                => $this->input->post('paypal_pro'),
                'stripe'                    => $this->input->post('stripe'),
                'authorize'                 => $this->input->post('authorize'),
                'rounding'                  => $this->input->post('rounding'),
                'item_order'                => $this->input->post('item_order'),
                'after_sale_page'           => $this->input->post('after_sale_page'),
                'printer'                   => $this->input->post('receipt_printer'),
                'order_printers'            => json_encode($this->input->post('order_printers')),
                'auto_print'                => $this->input->post('auto_print'),
                'remote_printing'           => DEMO ? 1 : $this->input->post('remote_printing'),
                'customer_details'          => $this->input->post('customer_details'),
                'local_printers'            => $this->input->post('local_printers'),
            );
            $payment_config = array(
                'APIUsername'            => $this->input->post('APIUsername'),
                'APIPassword'            => $this->input->post('APIPassword'),
                'APISignature'           => $this->input->post('APISignature'),
                'stripe_secret_key'      => $this->input->post('stripe_secret_key'),
                'stripe_publishable_key' => $this->input->post('stripe_publishable_key'),
                'api_login_id'           => $this->input->post('api_login_id'),
                'api_transaction_key'    => $this->input->post('api_transaction_key'),
            );
        } elseif ($this->input->post('update_settings')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("pos/settings");
        }

        if ($this->form_validation->run() == TRUE && $this->pos_model->updateSetting($data)) {
            if (DEMO) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                admin_redirect("pos/settings");
            }
            if ($this->write_payments_config($payment_config)) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                admin_redirect("pos/settings");
            } else {
                $this->session->set_flashdata('error', $this->lang->line('pos_setting_updated_payment_failed'));
                admin_redirect("pos/settings");
            }
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['pos'] = $this->pos_model->getSetting();
            $this->data['categories'] = $this->site->getAllCategories();
            //$this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
            $this->data['billers'] = $this->pos_model->getAllBillerCompanies();
            $this->config->load('payment_gateways');
            $this->data['stripe_secret_key'] = $this->config->item('stripe_secret_key');
            $this->data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');
            $authorize = $this->config->item('authorize');
            $this->data['api_login_id'] = $authorize['api_login_id'];
            $this->data['api_transaction_key'] = $authorize['api_transaction_key'];
            $this->data['APIUsername'] = $this->config->item('APIUsername');
            $this->data['APIPassword'] = $this->config->item('APIPassword');
            $this->data['APISignature'] = $this->config->item('APISignature');
            $this->data['printers'] = $this->pos_model->getAllPrinters();
            $this->data['paypal_balance'] = NULL; // $this->pos_settings->paypal_pro ? $this->paypal_balance() : NULL;
            $this->data['stripe_balance'] = NULL; // $this->pos_settings->stripe ? $this->stripe_balance() : NULL;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('pos_settings')));
            $meta = array('page_title' => lang('pos_settings'), 'bc' => $bc);
            $this->page_construct('pos/settings', $meta, $this->data);
        }
    }

    public function write_payments_config($config)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect("welcome");
        }
        if (DEMO) {
            return TRUE;
        }
        $file_contents = file_get_contents('./assets/config_dumps/payment_gateways.php');
        $output_path = APPPATH . 'config/payment_gateways.php';
        $this->load->library('parser');
        $parse_data = array(
            'APIUsername'            => $config['APIUsername'],
            'APIPassword'            => $config['APIPassword'],
            'APISignature'           => $config['APISignature'],
            'stripe_secret_key'      => $config['stripe_secret_key'],
            'stripe_publishable_key' => $config['stripe_publishable_key'],
            'api_login_id'           => $config['api_login_id'],
            'api_transaction_key'    => $config['api_transaction_key'],
        );
        $new_config = $this->parser->parse_string($file_contents, $parse_data);

        $handle = fopen($output_path, 'w+');
        @chmod($output_path, 0777);

        if (is_writable($output_path)) {
            if (fwrite($handle, $new_config)) {
                @chmod($output_path, 0644);
                return TRUE;
            } else {
                @chmod($output_path, 0644);
                return FALSE;
            }
        } else {
            @chmod($output_path, 0644);
            return FALSE;
        }
    }

    public function opened_bills($per_page = 0)
    {
        $this->load->library('pagination');

        //$this->table->set_heading('Id', 'The Title', 'The Content');
        if ($this->input->get('per_page')) {
            $per_page = $this->input->get('per_page');
        }

        $config['base_url'] = admin_url('pos/opened_bills');
        $config['total_rows'] = $this->pos_model->bills_count();
        $config['per_page'] = 6;
        $config['num_links'] = 3;

        $config['full_tag_open'] = '<ul class="pagination pagination-sm">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';

        $this->pagination->initialize($config);
        $data['r'] = TRUE;
        $bills = $this->pos_model->fetch_bills($config['per_page'], $per_page);
        if (!empty($bills)) {
            $html = "";
            $html .= '<ul class="ob">';
            foreach ($bills as $bill) {
                $html .= '<li><button type="button" class="btn btn-info sus_sale" id="' . $bill->id . '"><p>' . $bill->suspend_note . '</p><strong>' . $bill->customer . '</strong><br>'.lang('date').': ' . $bill->date . '<br>'.lang('items').': ' . $bill->count . '<br>'.lang('total').': ' . $this->bpas->formatMoney($bill->total) . '</button></li>';
            }
            $html .= '</ul>';
        } else {
            $html = "<h3>" . lang('no_opeded_bill') . "</h3><p>&nbsp;</p>";
            $data['r'] = FALSE;
        }

        $data['html'] = $html;

        $data['page'] = $this->pagination->create_links();
        echo $this->load->view($this->theme . 'pos/opened', $data, TRUE);

    }

    public function delete($id = NULL)
    {

        $this->bpas->checkPermissions('index');

        if ($this->pos_model->deleteBill($id)) {
            $this->bpas->send_json(array('error' => 0, 'msg' => lang("suspended_sale_deleted")));
        }
    }

    public function email_receipt($sale_id = NULL, $view = null)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->post('id')) {
            $sale_id = $this->input->post('id');
        }
        if ( ! $sale_id) {
            die('No sale selected.');
        }
        if ($this->input->post('email')) {
            $to = $this->input->post('email');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');

        $this->data['rows'] = $this->pos_model->getAllInvoiceItems($sale_id);
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer'] = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments'] = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos'] = $this->pos_model->getSetting();
        $this->data['barcode'] = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale'] = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : NULL;
        $this->data['inv'] = $inv;
        $this->data['sid'] = $sale_id;
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['page_title'] = $this->lang->line("invoice");

        $receipt = $this->load->view($this->theme . 'pos/email_receipt', $this->data, TRUE);
        if ($view) {
            echo $receipt;
            die();
        }

        if (!$to) {
            $to = $this->data['customer']->email;
        }
        if (!$to) {
            $this->bpas->send_json(array('msg' => $this->lang->line("no_meil_provided")));
        }

        try {
            if ($this->bpas->send_email($to, lang('receipt_from') .' ' . $this->data['biller']->company, $receipt)) {
                $this->bpas->send_json(array('msg' => $this->lang->line("email_sent")));
            } else {
                $this->bpas->send_json(array('msg' => $this->lang->line("email_failed")));
            }
        } catch (Exception $e) {
            $this->bpas->send_json(array('msg' => $e->getMessage()));
        }

    }

    public function active()
    {
        $this->session->set_userdata('last_activity', now());
        if ((now() - $this->session->userdata('last_activity')) <= 20) {
            die('Successfully updated the last activity.');
        } else {
            die('Failed to update last activity.');
        }
    }

    public function add_payment($id = NULL)
    {
        $this->bpas->checkPermissions('payments', TRUE, 'sales');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == TRUE) {
            if ($this->input->post('paid_by') == 'deposit') {
                $sale = $this->pos_model->getInvoiceByID($this->input->post('sale_id'));
                $customer_id = $sale->customer_id;
                if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = array(
                'date'         => $date,
                'sale_id'      => $this->input->post('sale_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount'       => $this->input->post('amount-paid'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'cc_cvv2'      => $this->input->post('pcc_ccv'),
                'note'         => $this->input->post('note'),
                'created_by'   => $this->session->userdata('user_id'),
                'type'         => 'received',
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->bpas->print_arrays($payment);

        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == TRUE && $msg = $this->pos_model->addPayment($payment, $customer_id)) {
            if ($msg) {
                if ($msg['status'] == 0) {
                    unset($msg['status']);
                    $error = '';
                    foreach ($msg as $m) {
                        if (is_array($m)) {
                            foreach ($m as $e) {
                                $error .= '<br>'.$e;
                            }
                        } else {
                            $error .= '<br>'.$m;
                        }
                    }
                    $this->session->set_flashdata('error', '<pre>' . $error . '</pre>');
                } else {
                    $this->session->set_flashdata('message', lang("payment_added"));
                }
            } else {
                $this->session->set_flashdata('error', lang("payment_failed"));
            }
            admin_redirect("pos/sales");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $sale = $this->pos_model->getInvoiceByID($id);
            $this->data['inv'] = $sale;
            $this->data['payment_ref'] = $this->site->getReference('pay');
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'pos/add_payment', $this->data);
        }
    }

    public function updates()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect("welcome");
        }
        $this->form_validation->set_rules('purchase_code', lang("purchase_code"), 'required');
        $this->form_validation->set_rules('envato_username', lang("envato_username"), 'required');
        if ($this->form_validation->run() == TRUE) {
            $this->db->update('pos_settings', array('purchase_code' => $this->input->post('purchase_code', TRUE), 'envato_username' => $this->input->post('envato_username', TRUE)), array('pos_id' => 1));
            admin_redirect('pos/updates');
        } else {
            $fields = array('version' => $this->pos_settings->version, 'code' => $this->pos_settings->purchase_code, 'username' => $this->pos_settings->envato_username, 'site' => base_url());
            $this->load->helper('update');
            $protocol = is_https() ? 'https://' : 'http://';
            $updates = get_remote_contents($protocol . 'api.tecdiary.com/v1/update/', $fields);
            $this->data['updates'] = json_decode($updates);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('updates')));
            $meta = array('page_title' => lang('updates'), 'bc' => $bc);
            $this->page_construct('pos/updates', $meta, $this->data);
        }
    }

    public function install_update($file, $m_version, $version)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect("welcome");
        }
        $this->load->helper('update');
        save_remote_file($file . '.zip');
        $this->bpas->unzip('./files/updates/' . $file . '.zip');
        if ($m_version) {
            $this->load->library('migration');
            if (!$this->migration->latest()) {
                $this->session->set_flashdata('error', $this->migration->error_string());
                admin_redirect("pos/updates");
            }
        }
        $this->db->update('pos_settings', array('version' => $version), array('pos_id' => 1));
        unlink('./files/updates/' . $file . '.zip');
        $this->session->set_flashdata('success', lang('update_done'));
        admin_redirect("pos/updates");
    }

    function open_drawer() {

        $data = json_decode($this->input->get('data'));
        $this->load->library('escpos');
        $this->escpos->load($data->printer);
        $this->escpos->open_drawer();

    }

    function p() {

        $data = json_decode($this->input->get('data'));
        $this->load->library('escpos');
        $this->escpos->load($data->printer);
        $this->escpos->print_receipt($data);

    }

    function printers()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect("pos");
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('printers');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('printers')));
        $meta = array('page_title' => lang('list_printers'), 'bc' => $bc);
        $this->page_construct('pos/printers', $meta, $this->data);
    }

    function get_printers()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->bpas->md();
        }

        $this->load->library('datatables');
        $this->datatables
        ->select("id, title, type, profile, path, ip_address, port")
        ->from("printers")
        ->add_column("Actions", "<div class='text-center'> <a href='" . admin_url('pos/edit_printer/$1') . "' class='btn-warning btn-xs tip' title='".lang("edit_printer")."'><i class='fa fa-edit'></i></a> <a href='#' class='btn-danger btn-xs tip po' title='<b>" . lang("delete_printer") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('pos/delete_printer/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
        ->unset_column('id');
        echo $this->datatables->generate();

    }

    function add_printer()
    {

        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect("pos");
        }

        $this->form_validation->set_rules('title', $this->lang->line("title"), 'required');
        $this->form_validation->set_rules('type', $this->lang->line("type"), 'required');
        $this->form_validation->set_rules('profile', $this->lang->line("profile"), 'required');
        $this->form_validation->set_rules('char_per_line', $this->lang->line("char_per_line"), 'required');
        if ($this->input->post('type') == 'network') {
            $this->form_validation->set_rules('ip_address', $this->lang->line("ip_address"), 'required|is_unique[printers.ip_address]');
            $this->form_validation->set_rules('port', $this->lang->line("port"), 'required');
        } else {
            $this->form_validation->set_rules('path', $this->lang->line("path"), 'required|is_unique[printers.path]');
        }

        if ($this->form_validation->run() == true) {

            $data = array('title' => $this->input->post('title'),
                'type' => $this->input->post('type'),
                'profile' => $this->input->post('profile'),
                'char_per_line' => $this->input->post('char_per_line'),
                'path' => $this->input->post('path'),
                'ip_address' => $this->input->post('ip_address'),
                'port' => ($this->input->post('type') == 'network') ? $this->input->post('port') : NULL,
            );

        }

        if ( $this->form_validation->run() == true && $cid = $this->pos_model->addPrinter($data)) {

            $this->session->set_flashdata('message', $this->lang->line("printer_added"));
            admin_redirect("pos/printers");

        } else {
            if($this->input->is_ajax_request()) {
                echo json_encode(array('status' => 'failed', 'msg' => validation_errors())); die();
            }

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('add_printer');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('pos'), 'page' => lang('pos')), array('link' => admin_url('pos/printers'), 'page' => lang('printers')), array('link' => '#', 'page' => lang('add_printer')));
            $meta = array('page_title' => lang('add_printer'), 'bc' => $bc);
            $this->page_construct('pos/add_printer', $meta, $this->data);
        }
    }

    function edit_printer($id = NULL)
    {

        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect("pos");
        }
        if($this->input->get('id')) { $id = $this->input->get('id', TRUE); }

        $printer = $this->pos_model->getPrinterByID($id);
        $this->form_validation->set_rules('title', $this->lang->line("title"), 'required');
        $this->form_validation->set_rules('type', $this->lang->line("type"), 'required');
        $this->form_validation->set_rules('profile', $this->lang->line("profile"), 'required');
        $this->form_validation->set_rules('char_per_line', $this->lang->line("char_per_line"), 'required');
        if ($this->input->post('type') == 'network') {
            $this->form_validation->set_rules('ip_address', $this->lang->line("ip_address"), 'required');
            if ($this->input->post('ip_address') != $printer->ip_address) {
                $this->form_validation->set_rules('ip_address', $this->lang->line("ip_address"), 'is_unique[printers.ip_address]');
            }
            $this->form_validation->set_rules('port', $this->lang->line("port"), 'required');
        } else {
            $this->form_validation->set_rules('path', $this->lang->line("path"), 'required');
            if ($this->input->post('path') != $printer->path) {
                $this->form_validation->set_rules('path', $this->lang->line("path"), 'is_unique[printers.path]');
            }
        }

        if ($this->form_validation->run() == true) {

            $data = array('title' => $this->input->post('title'),
                'type' => $this->input->post('type'),
                'profile' => $this->input->post('profile'),
                'char_per_line' => $this->input->post('char_per_line'),
                'path' => $this->input->post('path'),
                'ip_address' => $this->input->post('ip_address'),
                'port' => ($this->input->post('type') == 'network') ? $this->input->post('port') : NULL,
            );

        }

        if ( $this->form_validation->run() == true && $this->pos_model->updatePrinter($id, $data)) {

            $this->session->set_flashdata('message', $this->lang->line("printer_updated"));
            admin_redirect("pos/printers");

        } else {

            $this->data['printer'] = $printer;
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('edit_printer');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('pos'), 'page' => lang('pos')), array('link' => admin_url('pos/printers'), 'page' => lang('printers')), array('link' => '#', 'page' => lang('edit_printer')));
            $meta = array('page_title' => lang('edit_printer'), 'bc' => $bc);
            $this->page_construct('pos/edit_printer', $meta, $this->data);

        }
    }

    function delete_printer($id = NULL)
    {
        if(DEMO) {
            $this->session->set_flashdata('error', $this->lang->line("disabled_in_demo"));
            $this->bpas->md();
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->bpas->md();
        }

        if ($this->input->get('id')) { $id = $this->input->get('id', TRUE); }

        if ($this->pos_model->deletePrinter($id)) {
            $this->bpas->send_json(array('error' => 0, 'msg' => lang("printer_deleted")));
        }

    }
    public function reservation($warehouse_id = null)
    {
        $this->bpas->checkPermissions();
        $count = explode(',', $this->session->userdata('warehouse_id'));
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            if (count($count)>1) {
                $this->data['warehouses']   = $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }
            $this->data['count_warehouses'] = $count;
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }

        // $this->data['alert_id'] = isset($_GET['alert_id']) ? $_GET['alert_id'] : null;
        $this->data['alert_id'] = isset($_GET['alert_id']) ? ($_GET['alert_id'] !== '' ? $_GET['alert_id'] : 'empty')  : null;

        $biller_id = $this->session->userdata('biller_id');
        $this->data['users'] = $this->site->getStaff();
        $this->data['products'] = $this->site->getProducts();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['user_billers'] = $this->site->getCompanyByID($biller_id);
        $this->data['drivers']  = $this->site->getDriver();
    
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales')]];
        $meta = ['page_title' => lang('sales'), 'bc' => $bc];
        $this->page_construct('hotel_apartment/index_rental', $meta, $this->data);
        
    }

    public function edit_rent($id = null)
    {
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);
        if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref) {
            $this->session->set_flashdata('error', lang('sale_x_action'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->saleman_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $payment_term_details   = $this->site->getAllPaymentTermByID($payment_term);
            // $due_date      = (isset($payment_term_details[0]->id) ? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
            $commission_product = 0;
            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_addition_type = isset($_POST['addition_type'][$r]) ? $_POST['addition_type'][$r] :'';
                $item_warranty      = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] :'';
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                $item_room_option   = isset($_POST['room_option'][$r]) ? $_POST['room_option'][$r] : null;
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    $cost = 0;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->bpas->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity), 4);
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);
                    $getitems = $this->site->getProductByID($item_id);
                    $purchase_unit_cost = 0;
                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'purchase_unit_cost'=> $purchase_unit_cost ? $purchase_unit_cost : NULL,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'addition_type'     => $item_addition_type,
                        'warranty'          => $item_warranty,
                        'comment'           => $item_detail,
                        'commission'        => isset($commission_item->price) ? ($commission_item->price * $item_quantity) : 0,
                        'room_option'       => $item_room_option
                    ];
                    $commission_product += isset($commission_item->price) ? ($commission_item->price * $item_quantity) : 0; 
                    //========add accounting=========//
                    if($this->Settings->accounting == 1 && $item_type != 'manual' && $sale_status=='completed'){
                       /* $getproduct = $this->site->getProductByID($item_id);
                        if($getproduct->gender =='WOMEN'){
                            $default_sale = 7001101;
                        }elseif ($getproduct->gender =='MEN') {
                            $default_sale = 7001102;
                        }elseif ($getproduct->gender =='GIRLS') {
                            $default_sale = 7001103;
                        }elseif ($getproduct->gender =='BOY') {
                            $default_sale = 7001104;
                        }else{*/
                            $default_sale = ($item_type == 'standard') ? $this->accounting_setting->default_sale : $this->accounting_setting->other_income;
                        //}
                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' =>$this->accounting_setting->default_stock,
                            'amount' => -($cost * $item_unit_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => 0
                        );
                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_cost,
                            'amount' => ($cost * $item_unit_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_cost),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost),
                        );
                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $default_sale,
                            'amount' => - $subtotal,
                            'narrative' =>  $this->site->getAccountName($default_sale),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => 0
                        );
            
                    }
                    //============end accounting=======//
                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }
            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $saleman_award_points = 0;
            $staff = $this->site->getUser($inv->saleman_by);
            //=======acounting=========//
            if($this->Settings->accounting == 1){
            //  $saleAcc = $this->site->getAccountSettingByBiller($biller_id);
                $saleAcc = $this->site->getAccountSettingByBiller();
                if($order_discount != 0){
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_discount,
                        'amount' => $order_discount,
                        'narrative' => 'Order Discount',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                         'activity_type' => 0
                    );
                }
                if($order_tax != 0){
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_tax,
                        'amount' => -$order_tax,
                        'narrative' => 'Order Tax',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 0
                    );
                }
                if($shipping != 0){
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_freight,
                        'amount' => -$shipping,
                        'narrative' => 'Shipping',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 0
                    );
                }
            }
            //============end accounting=======//
            $data           = [
                'date'              => $date,
                'project_id'        => $this->input->post('project'),
                'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->bpas->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'total_items'       => $total_items,
                'sale_status'       => $sale_status,
                'payment_status'    => $payment_status,
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
                'updated_by'        => $this->session->userdata('user_id'),
                'saleman_by'        => $this->input->post('saleman_by'),
                'zone_id'           => $this->input->post('zone_id'),
                'updated_at'        => date('Y-m-d H:i:s'),
                'date_in'           => $this->bpas->fld(trim($this->input->post('arrival'))),
                'date_out'          => $this->bpas->fld(trim($this->input->post('departure'))),
            ];
            if ($payment_status != 'paid') {
                if ($payment_status == 'partial') {
                    if ($this->input->post('paid_by') == 'deposit') {
                        if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                            $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    }
                    if ($this->input->post('paid_by') == 'gift_card') {
                        $gc            = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                        $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                        $gc_balance    = $gc->balance - $amount_paying;
                        $payment       = [
                            'date'         => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'amount'       => $this->bpas->formatDecimal($amount_paying),
                            'paid_by'      => $this->input->post('paid_by'),
                            'cheque_no'    => $this->input->post('cheque_no'),
                            'cc_no'        => $this->input->post('gift_card_no'),
                            'cc_holder'    => $this->input->post('pcc_holder'),
                            'cc_month'     => $this->input->post('pcc_month'),
                            'cc_year'      => $this->input->post('pcc_year'),
                            'cc_type'      => $this->input->post('pcc_type'),
                            'created_by'   => $this->session->userdata('user_id'),
                            'note'         => $this->input->post('payment_note'),
                            'type'         => 'received',
                            'gc_balance'   => $gc_balance,
                        ];
                    } else {
                        $payment = [
                            'date'         => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'amount'       => $this->bpas->formatDecimal($this->input->post('amount-paid')),
                            'paid_by'      => $this->input->post('paid_by'),
                            'cheque_no'    => $this->input->post('cheque_no'),
                            'cc_no'        => $this->input->post('pcc_no'),
                            'cc_holder'    => $this->input->post('pcc_holder'),
                            'cc_month'     => $this->input->post('pcc_month'),
                            'cc_year'      => $this->input->post('pcc_year'),
                            'cc_type'      => $this->input->post('pcc_type'),
                            'created_by'   => $this->session->userdata('user_id'),
                            'note'         => $this->input->post('payment_note'),
                            'type'         => 'received',
                        ];
                    }
                    $amount_paying = $this->bpas->formatDecimal($this->input->post('amount-paid'));
                    //=====add accountig=====//
                    if($this->Settings->accounting == 1){
                        if($amount_paying < $grand_total){
                            $accTranPayments[] = array(
                                'tran_type' => 'Payment',
                                'tran_date' => $date,
                                'reference_no' => $this->input->post('payment_reference_no'),
                                'account_code' => $this->accounting_setting->default_receivable,
                                'amount' => ($grand_total - $amount_paying),
                                'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                                'description' => $this->input->post('payment_note'),
                                'biller_id' => $biller_id,
                                'project_id' => $project_id,
                                'customer_id' => $customer_id,
                                'created_by'  => $this->session->userdata('user_id'),
                                'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                            );
                        }
                        if ($this->input->post('paid_by') == 'deposit') {
                            $paying_to = $saleAcc->default_sale_deposit;
                        } else {
                            $paying_to = $this->input->post('bank_account');
                        }
                        $accTranPayments[] = array(
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'account_code' => $paying_to,
                            'amount' => $amount_paying,
                            'narrative' => $this->site->getAccountName($paying_to),
                            'description' => $this->input->post('payment_note'),
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => 0
                        );
                    }
                        //=====end accountig=====//
                } else {
                    $payment = [];
                    $accTranPayments[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_receivable,
                        'amount' => $grand_total,
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'payment_id' => $id,
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                    );
                }
            }else{
                $accTranPayments[] = array(
                    'tran_no' => $id,
                    'tran_type' => 'Sale',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $this->accounting_setting->default_receivable,
                    'amount' => $grand_total,
                    'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                    'biller_id' => $biller_id,
                    'project_id' => $project_id,
                    'customer_id' => $customer_id,
                    'created_by'  => $this->session->userdata('user_id'),
                    'payment_id' => $id,
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                );
            }
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            //----checked orver credit--------
            $cus_sales         = $this->sales_model->getSalesTotals($customer_id);
            if(($customer_details->credit_limit !=0) && (($cus_sales->total_amount - $cus_sales->paid) - $inv->grand_total + $data['grand_total']) > $customer_details->credit_limit){
                $this->session->set_flashdata('error', lang("customer_due_over_credit_amount"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
        if ($this->form_validation->run() == true && $this->sales_model->updateRent($id, $data, $products,$accTrans,$accTranPayments, $commission_product)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('sale_updated'));
            admin_redirect('room/reservation');
        } else {   
            $items = $this->sales_model->getAllInvoiceItemsRoom($id);
            foreach($items as $item) {
                $warehouse_id   = $item->warehouse_id;
                $customer_id    = $this->pos_settings->default_customer;
                $warehouse      = $this->site->getWarehouseByID($warehouse_id);
                $customer       = $this->site->getCompanyByID($customer_id);
                $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
            }
            if ($items) {
                $r = 0; 
                $pr = array();
                foreach ($items as $row) {
                    $c = uniqid(mt_rand(), true);
                    $option               = false;
                    $row->item_tax_method = 0;
                    $row->qty             = $row->quantity;
                    $row->discount        = '0';
                    $row->serial          = '';
                    $options              = null;
                    $product_options      = null;
                    $row->quantity        = 0;
                    $row->code            = '';
                    $opt                  = json_decode('{}');
                    $opt->price           = 0;
                    $option_id            = false;
                    $row->option          = $option_id;
                    $row->price           = $row->net_unit_price + (($row->net_unit_price * $customer_group->percent) / 100); 
                    $row->real_unit_price = $row->net_unit_price;
                    $row->base_quantity   = 1;
                    $row->base_unit       = $row->bed;
                    $row->base_unit_price = $row->net_unit_price;
                    $row->unit            = $row->bed;
                    $row->comment         = '';
                    $room_options         = $this->table_model->getRoomOptionsByRoomID($row->product_id);
                    $row->room_option     = (!empty($room_options) ? ($row->room_option ? $row->room_option : $room_options[0]->id) : null);
                    $combo_items          = false;
                    $categories           = null;
                    $units                = $row->bed;
                    $tax_rate             = null;
                    $set_price            = $this->site->getUnitByProId($row->id);
                    $set_price            = '';
                    $ri = $this->Settings->item_addition ? $row->id : sha1($c . $r);
                    $pr[$ri] =   [
                        'id'              => sha1($c . $r),
                        'item_id'          => $row->id,
                        'label'            => $row->name,
                        'category'         => null,
                        'row'              => $row,
                        'combo_items'      => $combo_items,
                        'tax_rate'         => $tax_rate,
                        'set_price'        => $set_price,
                        'units'            => $units,
                        'options'          => $options,
                        'fiber'            => null,
                        'product_options'  => $product_options,
                        'room_options' => $room_options
                    ];
                    $r++;
                }
                // $this->data['quote_items'] = json_encode($pr);
                $this->data['inv_items'] = json_encode($pr);
            } else {
                $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
            } 
            $this->data['count'] = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']     = $this->site->getAllProject();
            $inv = $this->pos_model->getInvoiceByID($id); 
            $this->data['inv']          = $inv;   
            $this->data['id']           = $id;
            $this->data['payment_term'] = $this->site->getAllPaymentTerm();
            $this->data['agencies']     = $this->site->getAllUsers();
            $this->data['billers']      = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['units']        = $this->site->getAllBaseUnits();
            $this->data['tax_rates']    = $this->site->getAllTaxRates();
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['zones']        = $this->site->getAllZones();
            $Settings = $this->site->getSettings();
            $this->data['salemans']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('edit_rent')]];
            $meta = ['page_title' => lang('edit_rent'), 'bc' => $bc];
            $this->page_construct('hotel_apartment/edit_rent', $meta, $this->data);
        }
    }

    
    public function getRentalSales($warehouse_id = null){
        $this->bpas->checkPermissions('index');
        if ($warehouse_id) {
            $warehouse_ids = explode('-', $warehouse_id);
        }
        $user_query     = $this->input->get('user') ? $this->input->get('user') : null;
        $customer       = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller         = $this->input->get('biller') ? $this->input->get('biller') : null;
        $reference_no   = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $saleman_by     = $this->input->get('saleman_by') ? $this->input->get('saleman_by') : null;
        $product_id     = $this->input->get('product_id') ? $this->input->get('product_id') : null;
        $warehouse      = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $delivered_by   = $this->input->get('delivered_by') ? $this->input->get('delivered_by') : null;
        $payment_status = $this->input->get('payment_status') ? $this->input->get('payment_status') : NULL;
        $start_date     = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date       = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        $a              = $this->input->get('a') ? $this->input->get('a') : null;

        if ($start_date) {
            $start_date = $this->bpas->fld($start_date . ' 00:00:00');
            $end_date   = $this->bpas->fld($end_date . ' 23:59:00');
        }
        $view_logo        = anchor('admin/sales/modal_view/$1/logo', '<i class="fa fa-money"></i> ' . lang('print_with_logo'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $detail_link          = anchor('admin/sales/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'),'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
       
        $return_detail_link   = anchor('admin/sales/return_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('return_sale') . ' ' . lang('details'));
        $duplicate_link       = anchor('admin/sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link        = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $check_out_link        = anchor('admin/room/list_check_out/$1', '<i class="fa fa-money"></i> ' . lang('check_out'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link     = anchor('admin/sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $packagink_link       = anchor('admin/sales/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_delivery_link    = anchor('admin/sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link           = anchor('admin/sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link            = anchor('admin/room/edit_rent/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link             = anchor('admin/sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link          = anchor('admin/sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
     
        $delete_link          = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $view_logo . '</li>';
           
            $action .= '
                <li>' . $check_out_link . '</li>
                <li>' . $payments_link . '</li>
                <li>' . $add_payment_link . '</li>
                <li>' . $edit_link . '</li>
                <li>' . $return_link . '</li>
                <li>' . $delete_link . '</li>
        </ul>

        </div></div>';


        $ds = "( SELECT d.sale_id,d.delivered_by,d.status,c.name as delivery_name
        from {$this->db->dbprefix('deliveries')} d LEFT JOIN {$this->db->dbprefix('companies')} c 
        on d.delivered_by = c.id ) FSI";

        $this->load->library('datatables');
        $this->datatables
        ->select("{$this->db->dbprefix('sales')}.id as id, 
            DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date,
            {$this->db->dbprefix('sales')}.reference_no, 
            {$this->db->dbprefix('sales')}.biller, 
            {$this->db->dbprefix('sales')}.customer,
            {$this->db->dbprefix('sales')}.date_in,
            {$this->db->dbprefix('sales')}.date_out,
            {$this->db->dbprefix('sales')}.sale_status, 
            {$this->db->dbprefix('sales')}.grand_total, 
            {$this->db->dbprefix('sales')}.paid, 
            ({$this->db->dbprefix('sales')}.grand_total - {$this->db->dbprefix('sales')}.paid) as balance,
            {$this->db->dbprefix('sales')}.payment_status, 
       
            {$this->db->dbprefix('sales')}.return_id")
        ->join('projects', 'sales.project_id = projects.project_id', 'left')
      
        ->join('users', 'sales.saleman_by = users.id', 'left')
    
        ->join($ds, 'FSI.sale_id=sales.id', 'left')
        ->order_by('sales.id', 'desc')
        ->from('sales')
        ->where('sales.order_tax',0);
        
        $this->datatables->where('sales.module_type','hotel_apartment');
        if ($warehouse_id) {
            $this->datatables->where('sales.warehouse_id', $warehouse_id);
        }
      

        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where_in("FIND_IN_SET(bpas_sales.warehouse_id, '" . $this->session->userdata('warehouse_id') . "')");
            $this->datatables->where("FIND_IN_SET(bpas_sales.created_by, '" . $this->session->userdata('user_id') . "')");
        }
        if ($this->Customer) {
            $this->datatables->where('projects.customer_id','bpas_projects.customer_id');
        }
        if ($this->input->get('shop') == 'yes') {
            $this->datatables->where('shop', 1);
        } elseif ($this->input->get('shop') == 'no') {
            $this->datatables->where('shop !=', 1);
        }
          
            if ($this->input->get('attachment') == 'yes') {
                $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', null);
            }

            if ($user_query) {
                $this->datatables->where('sales.created_by', $user_query);
            }
            if ($payment_status) {
                $get_status = explode('_', $payment_status);
                $this->datatables->where_in('sales.payment_status', $get_status);
            }
            if ($reference_no) {
                $this->datatables->where('sales.reference_no', $reference_no);
            }
            // if ($product_id) {
            //     $this->datatables->where('sales.product_id', $product_id);
            // }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($saleman_by) {
                $this->datatables->where('sales.saleman_by', $saleman_by);
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
      

        if ($a || $a == 'empty') {
            $alert_ids = explode('-', $a);
            $alert_id  = $a;

            if (count($alert_ids) > 1) {
                // $this->datatables->where('sales.payment_term <>', 0);
                // $this->datatables->where('DATE_SUB(bpas_sales.date, INTERVAL 1 DAY) <= CURDATE()');
                $this->datatables->where_in('sales.id', $alert_ids);
            } else {
                // $this->datatables->where('sales.payment_term <>', 0);
                // $this->datatables->where('DATE_SUB(bpas_sales.date, INTERVAL 1 DAY) <= CURDATE()');
                $this->datatables->where('sales.id', $alert_id);
            }
        }
    
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->datatables->where($this->db->dbprefix('sales') . '.pos !=', 1); // ->where('sale_status !=', 'returned');
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }

    public function checkin_17_03_22($sale_order_id = null, $quote_id = null)
    {   
        $this->bpas->checkPermissions();
        $getexchange_khm = $this->bpas->getExchange_rate('KHR');
        $getexchange_bat = $this->bpas->getExchange_rate('THB');
        $exchange_khm    = $getexchange_khm->rate;
        $exchange_bat    = $getexchange_bat->rate;
        if($sale_order_id){
            $sale_o = $this->sales_order_model->getSaleOrder($sale_order_id); 
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->order_status) == 'pending'){
                $this->session->set_flashdata('error', lang("sale_order_n_approved"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->order_status) == 'rejected'){
                $this->session->set_flashdata('error', lang("sale_order_has_been_rejected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->sale_status) == 'completed'){
                $this->session->set_flashdata('error', lang("sale_order_has_been_created"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
        $sale_id = $sale_order_id ? $sale_order_id : null;
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');

            // $reference = $this->site->CheckedSaleReference($this->input->post('reference_no'), $this->site->getReference('so'));

            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $project_id       = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $payment_term_details   = $this->site->getAllPaymentTermByID($payment_term);
            // $due_date         = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
            $quote_id         = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;
            // $total_weight     = 0;
            $commission_product = 0;
            $text_items = "";
            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $digital          = false;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_warranty      = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] : '';
                $item_weight        = 0;
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                $checkin_date       = isset($_POST['checkin_date'][$r]) ? $this->bpas->fld(trim($_POST['checkin_date'][$r])) : '';

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $cost = 0;
                   
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->bpas->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity), 4);
                       
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal     = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit         = $this->site->getUnitByID($item_unit);
                    $total_weight = number_format((float) ($item_weight * $item_unit_quantity), 4, '.', '');

                    $saleman = $this->site->getuser($this->input->post('saleman_by'));
                 
                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'purchase_unit_cost'=> 0,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'comment'           => $item_detail,
                        'check_in'           => $checkin_date
                    ];
                    $text_items .=  $r+1 . "/ " . $item_name . "(".$item_code.")" ." | ". $item_quantity." | ".$this->bpas->formatDecimal($real_unit_price) ." | ". $pr_item_discount ." | ". $this->bpas->formatDecimal($subtotal)."\n";
                    //========add accounting=========//
    
                    if($this->Settings->accounting == 1 && $item_type != 'manual' && ($sale_status=='completed' || $sale_status=='consignment')){
                        $getproduct = $this->site->getProductByID($item_id);

             
                            $default_sale  = $default_sale = ($item_type == 'standard') ? $this->accounting_setting->default_sale : $this->accounting_setting->other_income;
                            $inventory_acc = $this->accounting_setting->default_stock;
                            $costing_acc   = $this->accounting_setting->default_cost;
                        
                        $accTrans[] = array(
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_stock,
                            'amount' => -($cost * $item_unit_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),

                        );
                        $accTrans[] = array(
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_cost,
                            'amount' => ($cost * $item_unit_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_cost),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost)
                        );

                        $accTrans[] = array(
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $default_sale,
                            'amount' => - $subtotal,
                            'narrative' => $this->site->getAccountName($default_sale),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                        );
              
                    }
                    //============end accounting=======//

                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $saleman_award_points = 0;

            $staff = $this->site->getUser($this->input->post('saleman_by'));
            if($staff->save_point){
                if (!empty($this->Settings->each_sale)) {
                    $saleman_award_points = floor(($grand_total / $this->Settings->each_sale) * $this->Settings->sa_point);
                }
            }

            //=======acounting=========//
            if($this->Settings->accounting == 1){
             //   $saleAcc = $this->site->getAccountSettingByBiller($biller_id);
                $saleAcc = $this->site->getAccountSettingByBiller();
                if($order_discount != 0){
                    $accTrans[] = array(
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_discount,
                        'amount' => $order_discount,
                        'narrative' => 'Order Discount',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if($order_tax != 0){
                    $accTrans[] = array(
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_tax,
                        'amount' => -$order_tax,
                        'narrative' => 'Order Tax',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if($shipping != 0){
                    $accTrans[] = array(
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_freight,
                        'amount' => -$shipping,
                        'narrative' => 'Shipping',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
            }
            //============end accounting=======//
            $data       = [
                'date' => $date,
                'project_id'          => $this->input->post('project'),
                'so_id'               => $this->input->post('sale_order_id') ? $this->input->post('sale_order_id') : null,
                'reference_no'        => $reference,
                'customer_id'         => $customer_id,
                'customer'            => $customer,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'warehouse_id'        => $warehouse_id,
                'note'                => $note,
                'staff_note'          => $staff_note,
                'total'               => $total,
                'product_discount'    => $product_discount,
                'order_discount_id'   => $this->input->post('order_discount'),
                'order_discount'      => $order_discount,
                'total_discount'      => $total_discount,
                'product_tax'         => $product_tax,
                'order_tax_id'        => $this->input->post('order_tax'),
                'order_tax'           => $order_tax,
                'total_tax'           => $total_tax,
                'shipping'            => $this->bpas->formatDecimal($shipping),
                'grand_total'         => $grand_total,
                'total_items'         => $total_items,
                'sale_status'         => $sale_status,
                'payment_status'      => $payment_status,
                'payment_term'        => $payment_term,
                'due_date'            => $due_date,
                'paid'                => 0, 
                'created_by'          => $this->session->userdata('user_id'),
                'hash'                => hash('sha256', microtime() . mt_rand()),
                'saleman_by'          => $this->input->post('saleman_by'),
                'module_type'         => 'hotel_apartment',
                'currency_rate_kh'    => $exchange_khm,
                'currency_rate_bat'   => $exchange_bat,
       
            ];

            if ($payment_status == 'partial' || $payment_status == 'paid') {
                if ($this->input->post('paid_by') == 'deposit') {
                    if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                        $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
                if ($this->input->post('paid_by') == 'gift_card') {
                    $gc            = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance    = $gc->balance - $amount_paying;
                    $payment       = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->bpas->formatDecimal($amount_paying),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('gift_card_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                        'gc_balance'   => $gc_balance,
                    ];
                } else {
                    $payment = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->bpas->formatDecimal($this->input->post('amount-paid')),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('pcc_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                    ];
                }
                $amount_paying = $this->bpas->formatDecimal($this->input->post('amount-paid'));
                    //=====add accountig=====//
                if($this->Settings->accounting == 1){
                    if($this->input->post('paid_by') == 'deposit'){
                        $payment['bank_account'] = $saleAcc->default_sale_deposit;
                        $paying_to = $saleAcc->default_sale_deposit;
                    }else{
                        $payment['bank_account'] = $this->input->post('bank_account');
                        $paying_to = $this->input->post('bank_account');
                    }

                    if($amount_paying < $grand_total){
                        $accTranPayments[] = array(
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'account_code' => $this->accounting_setting->default_receivable,
                            'amount' => ($grand_total - $amount_paying),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description' => $this->input->post('payment_note'),
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                        );
                    }
              
                    $accTranPayments[] = array(
                        'tran_type' => 'Payment',
                        'tran_date' => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'account_code' => $paying_to,
                        'amount' => $amount_paying,
                        'narrative' => $this->site->getAccountName($paying_to),
                        'description' => $this->input->post('payment_note'),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                    //=====end accountig=====//
            } else {
                $accTranPayments= [];
                $payment = [];
                $accTrans[] = array(
                    'tran_type' => 'Sale',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $this->accounting_setting->default_receivable,
                    'amount' => $grand_total,
                    'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                    'biller_id' => $biller_id,
                    'project_id' => $project_id,
                    'customer_id' => $customer_id,
                    'created_by'  => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                );
            }
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
                      
            //----checked orver credit--------
            $cus_sales         = $this->sales_model->getSalesTotals($customer_id);
            if(($customer_details->credit_limit !=0) && (($cus_sales->total_amount - $cus_sales->paid) + $data['grand_total']) > $customer_details->credit_limit){
                $this->session->set_flashdata('error', lang("customer_due_over_credit_amount"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
        if ($this->form_validation->run() == true && $this->sales_model->addRent($data, $products, $payment, $si_return = array(), $accTrans, $accTranPayments, null, $commission_product)) {
            $this->session->set_userdata('remove_slls', 1);
            
            if ($quote_id) {
                $this->db->update('quotes', ['status' => 'completed'], ['id' => $quote_id]);
            }
            if ($sale_id) {
                $status = 'completed';
                $sale_order_id    = $this->input->post('sale_order_id');
                $sale_items       = $this->site->getSaleItemsBySaleOrderID($sale_order_id);
                $sale_order_items = $this->site->getSaleOrderItemsBySaleID($sale_id);

                foreach($sale_order_items as $item){
                    $key = array_search($item->product_code, array_column($sale_items, 'product_code'));
                    if($key !== false){
                        if($item->quantity > $sale_items[$key]->quantity){
                            $status = 'partial';
                            break;
                        }
                    } else {
                        $status = 'partial';
                        break;
                    }
                }

                $this->db->update('sales_order', array('sale_status' => $status), array('id' => $sale_id));
            }
            $t_customer = $this->site->getCompanyByID($customer_id);
            $header = lang("no"). " /     ".lang("name"). "  (".lang("code").")"."    |    ". lang("qty")."    |    ".lang("price") ."    |    ". lang("discount") ."    |    ". lang("total");

    

            $this->session->set_flashdata('message', lang('sale_added'));
            admin_redirect('room/reservation');
        } else {
            if ($quote_id || $sale_id) {
                if ($quote_id) {
                    $this->data['quote'] = $this->quotes_model->getQuoteByID($quote_id);
                    $items               = $this->quotes_model->getAllQuoteItems($quote_id);
                    $this->data['inv']   = $this->data['quote'];
                    $sale_items = [];
                } elseif ($sale_id) {
                    $this->data['quote'] = $this->sales_order_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_order_model->getAllInvoiceItems($sale_id);
                    $this->data['inv']   = $this->data['quote'];
                    $sale_items          = $this->site->getSaleItemsBySaleOrderID($sale_id);
                }
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $b = false;
                    if($sale_items !== false){
                        $key = array_search($item->product_code, array_column($sale_items, 'product_code'));
                        if($key !== false){
                            if($item->unit_quantity > $sale_items[$key]->quantity){
                                $item->unit_quantity = $item->unit_quantity - $sale_items[$key]->quantity;
                            } else {
                                $b = true;
                            }
                        } 
                    }
                    if($b == true){
                        continue;
                    }

                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row             = json_decode('{}');
                        $row->tax_method = 0;
                    } else {
                        unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    }
                    $row->quantity = 0;
                    $pis           = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $row->quantity += $pi->quantity_balance;
                        }
                    }

                    $row->id                 = $item->product_id;
                    $row->code               = $item->product_code;
                    $row->name               = $item->product_name;
                    $row->type               = $item->product_type;
                    $row->qty                = $item->quantity;
                    $row->base_quantity      = $item->quantity;
                    $row->base_unit          = isset($row->unit) ? $row->unit : $item->product_unit_id;
                    $row->base_unit_price    = isset($row->price) ? $row->price : $item->unit_price;
                    $row->unit               = $item->product_unit_id;
                    $row->qty                = $item->unit_quantity;
                    $row->discount           = $item->discount ? $item->discount : '0';
                    $row->item_tax           = $item->item_tax      > 0 ? $item->item_tax      / $item->quantity : 0;
                    $row->item_discount      = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                    $row->price              = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($row->item_discount));
                    $row->unit_price         = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($row->item_discount) + $this->bpas->formatDecimal($row->item_tax) : $item->unit_price + ($row->item_discount);
                    $row->real_unit_price    = $item->real_unit_price;
                    $row->tax_rate           = $item->tax_rate_id;
                    $row->serial             = '';
                    $row->serial_no          = isset($row->serial_no);
                    //  $row->weight             = $item->weight;
                    $row->option             = $item->option_id;
                    $row->details            = $item->comment;
                    $options                 = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
                    if ($options) {
                        $option_quantity = 0;
                        foreach ($options as $option) {
                            $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
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
                    $combo_items = false;
                    if ($row->type == 'combo') {
                        $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    }

                    $units    = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri       = $this->Settings->item_addition ? $row->id : $c;

                    $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, ];
                    $c++;
                }
                $this->data['quote_items'] = json_encode($pr);
            }
            $this->data['count']         = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']      = $this->site->getAllProject();
            $this->data['error']         = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id']      = $quote_id ? $quote_id : $sale_id;
            $this->data['sale_order_id'] = $sale_order_id;
            $this->data['billers']       = $this->site->getAllCompanies('biller');
            $this->data['data']          = $this->site->getBillerByUser($this->session->userdata('user_id'));
            $companyID                   = explode(',',$this->data['data']->multi_biller);
            $this->data['mbillers']      = $this->site->getAllCompaniesByBiller('biller', $companyID);
            $this->data['agencies']      = $this->site->getAllUsers();
            $this->data['payment_term']  = $this->site->getAllPaymentTerm();
            $this->data['warehouses']    = $this->site->getAllWarehouses();
            $this->data['tax_rates']     = $this->site->getAllTaxRates();
            $this->data['units']         = $this->site->getAllBaseUnits();
            $this->data['zones']         = $this->site->getAllZones();
            $this->data['suspend_notes']= $this->table_model->getAll_suspend_note();
            $this->data['group_price']   = json_encode($this->site->getAllGroupPrice());
            $Settings = $this->site->getSettings();
            $this->data['salemans']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            //$this->data['currencies']  = $this->sales_model->getAllCurrencies();
            $this->data['slnumber']      = $this->site->getReference('so');
            $this->data['sltaxnumber']   = $this->site->getReference('st');
            $this->data['payment_ref']   = ''; //$this->site->getReference('pay');
            $bc                          = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('add_sale')]];
            $meta                        = ['page_title' => lang('add_sale'), 'bc' => $bc];
            $this->page_construct('hotel_apartment/add_rent', $meta, $this->data);
        }
    }

    public function checkin($sale_order_id = null, $quote_id = null,$room = null)
    {   
        $this->bpas->checkPermissions();
        $getexchange_khm = $this->bpas->getExchange_rate('KHR');
        $exchange_khm    = $getexchange_khm->rate;
        if ($sale_order_id) {
            $sale_o = $this->sales_order_model->getSaleOrder($sale_order_id); 
            
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->order_status) == 'pending'){
                $this->session->set_flashdata('error', lang("sale_order_n_approved"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->order_status) == 'rejected'){
                $this->session->set_flashdata('error', lang("sale_order_has_been_rejected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->sale_status) == 'completed'){
                $this->session->set_flashdata('error', lang("sale_order_has_been_created"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
        $sale_id = $sale_order_id ? $sale_order_id : null;
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
            // $reference = $this->site->CheckedSaleReference($this->input->post('reference_no'), $this->site->getReference('so'));
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $project_id             = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id           = $this->input->post('warehouse');
            $customer_id            = $this->input->post('customer');
            $biller_id              = $this->input->post('biller');
            $total_items            = $this->input->post('total_items');
            $sale_status            = $this->input->post('sale_status');
            $payment_status         = $this->input->post('payment_status');
            $payment_term           = $this->input->post('payment_term');
            $payment_term_details   = $this->site->getAllPaymentTermByID($payment_term);
            // $due_date            = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            $due_date               = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping               = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details       = $this->site->getCompanyByID($customer_id);
            $customer               = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details         = $this->site->getCompanyByID($biller_id);
            $biller                 = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note                   = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note             = $this->bpas->clear_tags($this->input->post('staff_note'));
            $quote_id               = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;
            // $total_weight        = 0;
            $commission_product     = 0;
            $text_items             = "";
            $total                  = 0;
            $product_tax            = 0;
            $product_discount       = 0;
            $digital                = false;
            $gst_data               = [];
            $total_cgst             = $total_sgst       = $total_igst       = 0;
            $i                      = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_warranty      = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] : '';
                $item_weight        = 0;
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                $checkin_date       = isset($_POST['checkin_date'][$r]) ? $this->bpas->fld(trim($_POST['checkin_date'][$r])) : '';
                $item_room_option   = isset($_POST['room_option'][$r]) ? $_POST['room_option'][$r] : null;
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $cost = 0;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->bpas->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax      = $item_tax = 0;
                    $tax              = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity), 4);
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal     = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit         = $this->site->getUnitByID($item_unit);
                    $total_weight = number_format((float) ($item_weight * $item_unit_quantity), 4, '.', '');
                    $saleman = $this->site->getuser($this->input->post('saleman_by'));
                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'purchase_unit_cost'=> 0,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'comment'           => $item_detail,
                        'check_in'          => $checkin_date,
                        'room_option'       => $item_room_option
                    ];
                    $text_items .=  $r+1 . "/ " . $item_name . "(".$item_code.")" ." | ". $item_quantity." | ".$this->bpas->formatDecimal($real_unit_price) ." | ". $pr_item_discount ." | ". $this->bpas->formatDecimal($subtotal)."\n";
                    //========add accounting=========//
                    if($this->Settings->accounting == 1 && $item_type != 'manual' && ($sale_status=='completed' || $sale_status=='consignment')){
                        $getproduct = $this->site->getProductByID($item_id);
                        $default_sale  = $default_sale = ($item_type == 'standard') ? $this->accounting_setting->default_sale : $this->accounting_setting->other_income;
                        $inventory_acc = $this->accounting_setting->default_stock;
                        $costing_acc   = $this->accounting_setting->default_cost;        
                        $accTrans[] = array(
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_stock,
                            'amount' => -($cost * $item_unit_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_cost,
                            'amount' => ($cost * $item_unit_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_cost),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost)
                        );
                        $accTrans[] = array(
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $default_sale,
                            'amount' => - $subtotal,
                            'narrative' => $this->site->getAccountName($default_sale),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                        );
                    }
                    //============end accounting=======//
                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }
            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $saleman_award_points = 0;
            $staff = $this->site->getUser($this->input->post('saleman_by'));
            if($staff->save_point){
                if (!empty($this->Settings->each_sale)) {
                    $saleman_award_points = floor(($grand_total / $this->Settings->each_sale) * $this->Settings->sa_point);
                }
            }
            //=======acounting=========//
            if($this->Settings->accounting == 1){
                // $saleAcc = $this->site->getAccountSettingByBiller($biller_id);
                $saleAcc = $this->site->getAccountSettingByBiller();
                if($order_discount != 0){
                    $accTrans[] = array(
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_discount,
                        'amount' => $order_discount,
                        'narrative' => 'Order Discount',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if($order_tax != 0){
                    $accTrans[] = array(
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_tax,
                        'amount' => -$order_tax,
                        'narrative' => 'Order Tax',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if($shipping != 0){
                    $accTrans[] = array(
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_freight,
                        'amount' => -$shipping,
                        'narrative' => 'Shipping',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
            }
            //============end accounting=======//
            $data       = [
                'date'                => $date,
                'project_id'          => $this->input->post('project'),
                'so_id'               => $this->input->post('sale_order_id') ? $this->input->post('sale_order_id') : null,
                'reference_no'        => $reference,
                'customer_id'         => $customer_id,
                'customer'            => $customer,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'warehouse_id'        => $warehouse_id,
                'note'                => $note,
                'staff_note'          => $staff_note,
                'total'               => $total,
                'product_discount'    => $product_discount,
                'order_discount_id'   => $this->input->post('order_discount'),
                'order_discount'      => $order_discount,
                'total_discount'      => $total_discount,
                'product_tax'         => $product_tax,
                'order_tax_id'        => $this->input->post('order_tax'),
                'order_tax'           => $order_tax,
                'total_tax'           => $total_tax,
                'shipping'            => $this->bpas->formatDecimal($shipping),
                'grand_total'         => $grand_total,
                'total_items'         => $total_items,
                'sale_status'         => $sale_status,
                'payment_status'      => $payment_status,
                'payment_term'        => $payment_term,
                'due_date'            => $due_date,
                'paid'                => 0, 
                'created_by'          => $this->session->userdata('user_id'),
                'hash'                => hash('sha256', microtime() . mt_rand()),
                'saleman_by'          => $this->input->post('saleman_by'),
                'module_type'         => 'hotel_apartment',
                'currency_rate_kh'    => $exchange_khm,
                'currency_rate_bat'   => $exchange_bat,
                'date_in'             => $this->bpas->fld(trim($this->input->post('arrival'))),
                'date_out'            => $this->bpas->fld(trim($this->input->post('departure'))),
            ];
            if ($payment_status == 'partial' || $payment_status == 'paid') {
                if ($this->input->post('paid_by') == 'deposit') {
                    if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                        $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
                if ($this->input->post('paid_by') == 'gift_card') {
                    $gc            = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance    = $gc->balance - $amount_paying;
                    $payment       = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->bpas->formatDecimal($amount_paying),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('gift_card_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                        'gc_balance'   => $gc_balance,
                    ];
                } else {
                    $payment = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->bpas->formatDecimal($this->input->post('amount-paid')),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('pcc_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                    ];
                }
                $amount_paying = $this->bpas->formatDecimal($this->input->post('amount-paid'));
                //=====add accountig=====//
                if ($this->Settings->accounting == 1) {
                    if ($this->input->post('paid_by') == 'deposit') {
                        $payment['bank_account'] = $saleAcc->default_sale_deposit;
                        $paying_to = $saleAcc->default_sale_deposit;
                    } else {
                        $payment['bank_account'] = $this->input->post('bank_account');
                        $paying_to = $this->input->post('bank_account');
                    }
                    if($amount_paying < $grand_total){
                        $accTranPayments[] = array(
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'account_code' => $this->accounting_setting->default_receivable,
                            'amount' => ($grand_total - $amount_paying),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description' => $this->input->post('payment_note'),
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                        );
                    }
                    $accTranPayments[] = array(
                        'tran_type' => 'Payment',
                        'tran_date' => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'account_code' => $paying_to,
                        'amount' => $amount_paying,
                        'narrative' => $this->site->getAccountName($paying_to),
                        'description' => $this->input->post('payment_note'),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                    //=====end accountig=====//
            } else {
                $accTranPayments= [];
                $payment = [];
                $accTrans[] = array(
                    'tran_type' => 'Sale',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $this->accounting_setting->default_receivable,
                    'amount' => $grand_total,
                    'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                    'biller_id' => $biller_id,
                    'project_id' => $project_id,
                    'customer_id' => $customer_id,
                    'created_by'  => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                );
            }
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
                      
            //----checked orver credit--------
            $cus_sales         = $this->sales_model->getSalesTotals($customer_id);
            if(($customer_details->credit_limit !=0) && (($cus_sales->total_amount - $cus_sales->paid) + $data['grand_total']) > $customer_details->credit_limit){
                $this->session->set_flashdata('error', lang("customer_due_over_credit_amount"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
        if ($this->form_validation->run() == true && $this->sales_model->addRent($data, $products, $payment, $si_return = array(), $accTrans, $accTranPayments, null, $commission_product)) {
            $this->session->set_userdata('remove_slls', 1);
            
            if ($quote_id) {
                $this->db->update('quotes', ['status' => 'completed'], ['id' => $quote_id]);
            }
            if ($sale_id) {
                $status = 'completed';
                $sale_order_id    = $this->input->post('sale_order_id');
                $sale_items       = $this->site->getSaleItemsBySaleOrderID($sale_order_id);
                $sale_order_items = $this->site->getSaleOrderItemsBySaleID($sale_id);

                foreach($sale_order_items as $item){
                    $key = array_search($item->product_code, array_column($sale_items, 'product_code'));
                    if($key !== false){
                        if($item->quantity > $sale_items[$key]->quantity){
                            $status = 'partial';
                            break;
                        }
                    } else {
                        $status = 'partial';
                        break;
                    }
                }

                $this->db->update('sales_order', array('sale_status' => $status), array('id' => $sale_id));
            }
            $t_customer = $this->site->getCompanyByID($customer_id);
            $header = lang("no"). " /     ".lang("name"). "  (".lang("code").")"."    |    ". lang("qty")."    |    ".lang("price") ."    |    ". lang("discount") ."    |    ". lang("total");

    

            $this->session->set_flashdata('message', lang('sale_added'));
            admin_redirect('room/reservation');
        } else {
            if ($quote_id || $sale_id || $room) {

                if ($quote_id) {
                    $this->data['quote'] = $this->quotes_model->getQuoteByID($quote_id);
                    $items               = $this->quotes_model->getAllQuoteItems($quote_id);
                    $this->data['inv']   = $this->data['quote'];
                    $sale_items = [];
                } elseif ($sale_id) {
                    $this->data['quote'] = $this->sales_order_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_order_model->getAllInvoiceItems($sale_id);
                    $this->data['inv']   = $this->data['quote'];
                    $sale_items          = $this->site->getSaleItemsBySaleOrderID($sale_id);
                } elseif ($room) {
                    $this->data['quote'] = $this->sales_order_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_order_model->getsuspendNoteByID($room);       
                    $this->data['inv']   = $this->data['quote'];
                    $sale_items          = $this->site->getSaleItemsBySaleOrderID($sale_id);
                }

                // $this->bpas->print_arrays($items);
                $warehouse_id   = $items[0]->warehouse_id;
                $customer_id    = $this->pos_settings->default_customer;
                $warehouse      = $this->site->getWarehouseByID($warehouse_id);
                $customer       = $this->site->getCompanyByID($customer_id);
                $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);

                if($items){
                    $r = 0; $pr = array();
                    foreach ($items as $row) {
                        $c = uniqid(mt_rand(), true);
                        $option               = false;
                        $row->quantity        = 0;
                        $row->item_tax_method = 0;
                        $row->qty             = 1;
                        $row->discount        = '0';
                        $row->serial          = '';
                        $options              = null;
                        $product_options      = null;
                        $row->quantity        = 0;
                        $row->code            = '';
                        $opt                  = json_decode('{}');
                        $opt->price           = 0;
                        $option_id            = false;
                        $row->option          = $option_id;
                        $row->price           = $row->price + (($row->price * $customer_group->percent) / 100); 
                        $row->real_unit_price = $row->price;
                        $row->base_quantity   = 1;
                        $row->base_unit       = $row->bed;
                        $row->base_unit_price = $row->price;
                        $row->unit            = $row->bed;
                        $row->comment         = '';
                        $combo_items          = false;
                        $categories           = null;
                        $units                = $row->bed;
                        $tax_rate             = null;
                        $set_price = $this->site->getUnitByProId($row->id);
                        $set_price = '';

                        $ri = $this->Settings->item_addition ? $row->id : sha1($c . $r);
                    
                        $pr[$ri] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name , 'category' => null,
                        'row'     => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate,'set_price'=>$set_price, 'units' => $units, 'options' => $options, 'fiber' => null,'product_options' => $product_options, ];
                        $r++;
                    }
                    $this->data['quote_items'] = json_encode($pr);
                }else{
                    $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
                }       
            }
            $this->data['customer']      = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
            $this->data['count']         = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']      = $this->site->getAllProject();
            $this->data['error']         = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id']      = $quote_id ? $quote_id : $sale_id;
            $this->data['room_id']       = $room ? $room : null;
            $this->data['sale_order_id'] = $sale_order_id;
            $this->data['billers']       = $this->site->getAllCompanies('biller');
            $this->data['data']          = $this->site->getBillerByUser($this->session->userdata('user_id'));
            $companyID                   = explode(',',$this->data['data']->multi_biller);
            $this->data['mbillers']      = $this->site->getAllCompaniesByBiller('biller', $companyID);
            $this->data['agencies']      = $this->site->getAllUsers();
            $this->data['payment_term']  = $this->site->getAllPaymentTerm();
            $this->data['warehouses']    = $this->site->getAllWarehouses();
            $this->data['tax_rates']     = $this->site->getAllTaxRates();
            $this->data['units']         = $this->site->getAllBaseUnits();
            $this->data['zones']         = $this->site->getAllZones();
            $this->data['suspend_notes'] = $this->table_model->getAll_suspend_note();
            $this->data['group_price']   = json_encode($this->site->getAllGroupPrice());
            $Settings                    = $this->site->getSettings();
            $this->data['salemans']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            //$this->data['currencies']  = $this->sales_model->getAllCurrencies();
            $this->data['slnumber']      = $this->site->getReference('so');
            $this->data['sltaxnumber']   = $this->site->getReference('st');
            $this->data['payment_ref']   = ''; //$this->site->getReference('pay');
            $bc                          = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('add_sale')]];
            $meta                        = ['page_title' => lang('add_sale'), 'bc' => $bc];
            $this->page_construct('hotel_apartment/add_rent', $meta, $this->data);
        }
    }

    public function edit_ticket($id = null)
    {   
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);
        if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref) {
            $this->session->set_flashdata('error', lang('sale_x_action'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->saleman_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $payment_term_details   = $this->site->getAllPaymentTermByID($payment_term);
           // $due_date               = (isset($payment_term_details[0]->id) ? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);

            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
            $commission_product = 0;
            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial        = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_addition_type = isset($_POST['addition_type'][$r]) ? $_POST['addition_type'][$r] :'';
                $item_warranty = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] :'';
                $item_detail      = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {

                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    $cost = 0;
                  

                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->bpas->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity), 4);
                      
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);
                    $getitems = $this->site->getProductByID($item_id);

                    $purchase_unit_cost = 0;
                 
                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'purchase_unit_cost'=> $purchase_unit_cost ? $purchase_unit_cost : NULL,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'addition_type'     => $item_addition_type,
                        'warranty'          => $item_warranty,
                        'comment'           => $item_detail,
                        'commission'        => isset($commission_item->price) ? ($commission_item->price * $item_quantity) : 0,
                        ];
                   
                    $commission_product += isset($commission_item->price) ? ($commission_item->price * $item_quantity) : 0;
                       
                    //========add accounting=========//
       
                    if($this->Settings->accounting == 1 && $item_type != 'manual' && $sale_status=='completed'){

                       /* $getproduct = $this->site->getProductByID($item_id);
                        if($getproduct->gender =='WOMEN'){
                            $default_sale = 7001101;
                        }elseif ($getproduct->gender =='MEN') {
                            $default_sale = 7001102;
                        }elseif ($getproduct->gender =='GIRLS') {
                            $default_sale = 7001103;
                        }elseif ($getproduct->gender =='BOY') {
                            $default_sale = 7001104;
                        }else{*/
                            $default_sale = ($item_type == 'standard') ? $this->accounting_setting->default_sale : $this->accounting_setting->other_income;
                        //}

                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' =>$this->accounting_setting->default_stock,
                            'amount' => -($cost * $item_unit_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => 0
                        );
                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_cost,
                            'amount' => ($cost * $item_unit_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_cost),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost),
                        );
                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $default_sale,
                            'amount' => - $subtotal,
                            'narrative' =>  $this->site->getAccountName($default_sale),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => 0
                        );
            
                    }
                    //============end accounting=======//
                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $saleman_award_points = 0;
            $staff = $this->site->getUser($inv->saleman_by);
    

            //=======acounting=========//
            if($this->Settings->accounting == 1){
            //  $saleAcc = $this->site->getAccountSettingByBiller($biller_id);
                $saleAcc = $this->site->getAccountSettingByBiller();

                if($order_discount != 0){
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_discount,
                        'amount' => $order_discount,
                        'narrative' => 'Order Discount',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                         'activity_type' => 0
                    );
                }
                if($order_tax != 0){
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_tax,
                        'amount' => -$order_tax,
                        'narrative' => 'Order Tax',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 0
                    );
                }
                if($shipping != 0){
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_freight,
                        'amount' => -$shipping,
                        'narrative' => 'Shipping',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 0
                    );
                }
            }
            //============end accounting=======//

            $data = ['date'           => $date,
                'project_id'          => $this->input->post('project'),
                'reference_no'        => $reference,
                'customer_id'         => $customer_id,
                'customer'            => $customer,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'warehouse_id'        => $warehouse_id,
                'note'                => $note,
                'staff_note'          => $staff_note,
                'total'               => $total,
                'product_discount'    => $product_discount,
                'order_discount_id'   => $this->input->post('order_discount'),
                'order_discount'      => $order_discount,
                'total_discount'      => $total_discount,
                'product_tax'         => $product_tax,
                'order_tax_id'        => $this->input->post('order_tax'),
                'order_tax'           => $order_tax,
                'total_tax'           => $total_tax,
                'shipping'            => $this->bpas->formatDecimal($shipping),
                'grand_total'         => $grand_total,
                'total_items'         => $total_items,
                'sale_status'         => $sale_status,
                'payment_status'      => $payment_status,
                'payment_term'        => $payment_term,
                'due_date'            => $due_date,
                'updated_by'          => $this->session->userdata('user_id'),
                'saleman_by'          => $this->input->post('saleman_by'),
                'zone_id'             => $this->input->post('zone_id'),
                'updated_at'          => date('Y-m-d H:i:s'),
                'date_in'             => $this->bpas->fld(trim($this->input->post('arrival'))),
                'date_out'            => $this->bpas->fld(trim($this->input->post('departure'))),
            ];
        
            if($payment_status != 'paid'){
                if ($payment_status == 'partial') {
                    if ($this->input->post('paid_by') == 'deposit') {
                        if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                            $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    }
                    if ($this->input->post('paid_by') == 'gift_card') {
                        $gc            = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                        $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                        $gc_balance    = $gc->balance - $amount_paying;
                        $payment       = [
                            'date'         => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'amount'       => $this->bpas->formatDecimal($amount_paying),
                            'paid_by'      => $this->input->post('paid_by'),
                            'cheque_no'    => $this->input->post('cheque_no'),
                            'cc_no'        => $this->input->post('gift_card_no'),
                            'cc_holder'    => $this->input->post('pcc_holder'),
                            'cc_month'     => $this->input->post('pcc_month'),
                            'cc_year'      => $this->input->post('pcc_year'),
                            'cc_type'      => $this->input->post('pcc_type'),
                            'created_by'   => $this->session->userdata('user_id'),
                            'note'         => $this->input->post('payment_note'),
                            'type'         => 'received',
                            'gc_balance'   => $gc_balance,
                        ];
                    } else {
                        $payment = [
                            'date'         => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'amount'       => $this->bpas->formatDecimal($this->input->post('amount-paid')),
                            'paid_by'      => $this->input->post('paid_by'),
                            'cheque_no'    => $this->input->post('cheque_no'),
                            'cc_no'        => $this->input->post('pcc_no'),
                            'cc_holder'    => $this->input->post('pcc_holder'),
                            'cc_month'     => $this->input->post('pcc_month'),
                            'cc_year'      => $this->input->post('pcc_year'),
                            'cc_type'      => $this->input->post('pcc_type'),
                            'created_by'   => $this->session->userdata('user_id'),
                            'note'         => $this->input->post('payment_note'),
                            'type'         => 'received',
                        ];
                    }
                    $amount_paying = $this->bpas->formatDecimal($this->input->post('amount-paid'));
                        //=====add accountig=====//
                    if($this->Settings->accounting == 1){
                        if($amount_paying < $grand_total){
                            $accTranPayments[] = array(
                                'tran_type' => 'Payment',
                                'tran_date' => $date,
                                'reference_no' => $this->input->post('payment_reference_no'),
                                'account_code' => $this->accounting_setting->default_receivable,
                                'amount' => ($grand_total - $amount_paying),
                                'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                                'description' => $this->input->post('payment_note'),
                                'biller_id' => $biller_id,
                                'project_id' => $project_id,
                                'customer_id' => $customer_id,
                                'created_by'  => $this->session->userdata('user_id'),
                                'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                            );
                        }
                        if($this->input->post('paid_by') == 'deposit'){
                            $paying_to = $saleAcc->default_sale_deposit;
                        }else{
                            $paying_to = $this->input->post('bank_account');
                        }
                        $accTranPayments[] = array(
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'account_code' => $paying_to,
                            'amount' => $amount_paying,
                            'narrative' => $this->site->getAccountName($paying_to),
                            'description' => $this->input->post('payment_note'),
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => 0
                        );

                    }
                        //=====end accountig=====//
                } else {
                    $payment = [];
                    $accTranPayments[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_receivable,
                        'amount' => $grand_total,
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'payment_id' => $id,
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                    );
                }
            }else{
                $accTranPayments[] = array(
                    'tran_no' => $id,
                    'tran_type' => 'Sale',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $this->accounting_setting->default_receivable,
                    'amount' => $grand_total,
                    'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                    'biller_id' => $biller_id,
                    'project_id' => $project_id,
                    'customer_id' => $customer_id,
                    'created_by'  => $this->session->userdata('user_id'),
                    'payment_id' => $id,
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                );
            }
           // echo 'hi';
           //  echo $this->input->post('amount-paid');exit();

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }


            //----checked orver credit--------
            $cus_sales         = $this->sales_model->getSalesTotals($customer_id);
            if(($customer_details->credit_limit !=0) && (($cus_sales->total_amount - $cus_sales->paid) - $inv->grand_total + $data['grand_total']) > $customer_details->credit_limit){
                $this->session->set_flashdata('error', lang("customer_due_over_credit_amount"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateTicket($id, $data, $products,$accTrans,$accTranPayments, $commission_product)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('Ticket_updated'));
            admin_redirect('room/list_ticket');
        } else {
            
            $items = $this->sales_model->getAllInvoiceTicket($id);
            foreach($items as $item)
            {
                $warehouse_id   = $item->warehouse_id;
                $customer_id    = $this->pos_settings->default_customer;
                $warehouse      = $this->site->getWarehouseByID($warehouse_id);
                $customer       = $this->site->getCompanyByID($customer_id);
                $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
            }
            
            if($items){
                $r = 0; $pr = array();
                foreach ($items as $row) {
                    $c = uniqid(mt_rand(), true);
                    $option               = false;
                    $row->item_tax_method = 0;
                    $row->qty             = $row->quantity;
                    $row->discount        = '0';
                    $row->serial          = '';
                    $options              = null;
                    $product_options      = null;
                    $row->quantity        = 0;
                    $row->code            = '';
                    $opt                  = json_decode('{}');
                    $opt->price           = 0;
                    $option_id            = false;
                    $row->option          = $option_id;
                    $row->price           = $row->net_unit_price + (($row->net_unit_price * $customer_group->percent) / 100); 
                    $row->real_unit_price = $row->net_unit_price;
                    $row->base_quantity   = 1;
                    $row->base_unit       = $row->bed;
                    $row->base_unit_price = $row->net_unit_price;
                    $row->unit            = $row->bed;
                    $row->comment         = '';
                    $combo_items          = false;
                    $categories           = null;
                    $units                = $row->bed;
                    $tax_rate             = null;
                    $set_price = $this->site->getUnitByProId($row->id);
                    $set_price = '';

                    $ri = $this->Settings->item_addition ? $row->id : sha1($c . $r);
                    $pr[$ri] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name , 'category' => null,
                    'row'     => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate,'set_price'=>$set_price, 'units' => $units, 'options' => $options, 'fiber' => null,'product_options' => $product_options, ];
                    $r++;
                }
                // $this->data['quote_items'] = json_encode($pr);
                $this->data['inv_items'] = json_encode($pr);
            }else{
                $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
            } 

            $this->data['count'] = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']         = $this->site->getAllProject();
            $inv = $this->pos_model->getInvoiceByID($id); 
            $this->data['inv'] = $inv;   
            $this->data['id']        = $id;
            $this->data['payment_term']     = $this->site->getAllPaymentTerm();
            $this->data['agencies'] = $this->site->getAllUsers();
            $this->data['billers']    = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['units']      = $this->site->getAllBaseUnits();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['zones']      = $this->site->getAllZones();
            $Settings = $this->site->getSettings();
            $this->data['salemans']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('edit_ticket')]];
            $meta = ['page_title' => lang('edit_ticket'), 'bc' => $bc];
            $this->page_construct('hotel_apartment/edit_ticket', $meta, $this->data);
        }
    }

    public function list_check_out($id = null)
    {
        $this->bpas->checkPermissions(false, true);
        $this->data['payments'] = $this->sales_model->getInvoicePayments($id);
        $this->data['inv']      = $this->sales_model->getInvoiceByID($id);
        $this->data['rows']        = $this->sales_model->getAllInvoiceItemsRoom($id);
        $this->load->view($this->theme . 'hotel_apartment/rooms', $this->data);
    }

    public function check_out($note_id,$sale_id)
    {
        $checkout = $this->db->update('suspended_note', ['status' => 0], ['note_id' => $note_id]); 
        if($checkout){
            $this->db->update('reservation', ['checkOut'  =>  date('Y-m-d H:i'),'checkOut_by'=>  $this->session->userdata('user_id')],
                [ 'note_id'   =>  $note_id,'sale_id'=> $sale_id,]
            );
            $this->session->set_flashdata('message', lang('check_out_succesful'));
            admin_redirect('room/reservation');
        }
    }

    public function daily_room()
    {
        $year = $this->input->post('year') ? $this->input->post('year') : date("Y");
        $month = $this->input->post('month') ? $this->input->post('month') : date("n");

        $this->bpas->checkPermissions('expenses');
        $this->data['categories']  = $this->site->getAllSuspended_note_Room();
        $this->data['expenses']    = $this->site->getAllSuspendAndSaleItem($year,$month);
        $this->data['users']       = $this->site->getStaff();
        $this->data['billers']     = $this->site->getAllCompanies('biller');
        $bc = array(array('link'   => base_url(), 'page' => lang('home')), array('link' => site_url('room'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('daily_room')));
        $meta = array('page_title' => lang('daily_room'), 'bc' => $bc);
        $this->page_construct('hotel_apartment/daily_room', $meta, $this->data);
    }

    public function getTicketSales($warehouse_id = null){
        $this->bpas->checkPermissions('index');
        if ($warehouse_id) {
            $warehouse_ids = explode('-', $warehouse_id);
        }
        $user_query     = $this->input->get('user') ? $this->input->get('user') : null;
        $customer       = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller         = $this->input->get('biller') ? $this->input->get('biller') : null;
        $reference_no   = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $saleman_by     = $this->input->get('saleman_by') ? $this->input->get('saleman_by') : null;
        $product_id     = $this->input->get('product_id') ? $this->input->get('product_id') : null;
        $warehouse      = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $delivered_by   = $this->input->get('delivered_by') ? $this->input->get('delivered_by') : null;
        $payment_status = $this->input->get('payment_status') ? $this->input->get('payment_status') : NULL;
        $start_date     = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date       = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        $a              = $this->input->get('a') ? $this->input->get('a') : null;

        if ($start_date) {
            $start_date = $this->bpas->fld($start_date . ' 00:00:00');
            $end_date   = $this->bpas->fld($end_date . ' 23:59:00');
        }
        $view_logo        = anchor('admin/sales/modal_view/$1/logo', '<i class="fa fa-money"></i> ' . lang('print_with_logo'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $detail_link          = anchor('admin/sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
       
        $return_detail_link   = anchor('admin/sales/return_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('return_sale') . ' ' . lang('details'));
        $duplicate_link       = anchor('admin/sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link        = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $check_out_link       = anchor('admin/room/list_check_out/$1', '<i class="fa fa-money"></i> ' . lang('check_out'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link     = anchor('admin/sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $packagink_link       = anchor('admin/sales/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_delivery_link    = anchor('admin/sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link           = anchor('admin/sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link            = anchor('admin/room/edit_ticket/$1', '<i class="fa fa-edit"></i> ' . lang('edit_ticket'), 'class="sledit"');
        $pdf_link             = anchor('admin/sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link          = anchor('admin/sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
     
        $delete_link          = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $view_logo . '</li>';
           
            $action .= '
                <li>' . $check_out_link . '</li>
                <li>' . $payments_link . '</li>
                <li>' . $add_payment_link . '</li>
                <li>' . $edit_link . '</li>
                <li>' . $return_link . '</li>
                <li>' . $delete_link . '</li>
        </ul>

        </div></div>';


        $ds = "( SELECT d.sale_id,d.delivered_by,d.status,c.name as delivery_name
        from {$this->db->dbprefix('deliveries')} d LEFT JOIN {$this->db->dbprefix('companies')} c 
        on d.delivered_by = c.id ) FSI";

        $this->load->library('datatables');
        $this->datatables
        ->select("{$this->db->dbprefix('sales')}.id as id, 
            DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date,
            {$this->db->dbprefix('sales')}.reference_no, 
            {$this->db->dbprefix('sales')}.biller, 
            {$this->db->dbprefix('sales')}.customer,
            {$this->db->dbprefix('sales')}.date_in,
            {$this->db->dbprefix('sales')}.date_out,
            {$this->db->dbprefix('sales')}.sale_status, 
            {$this->db->dbprefix('sales')}.grand_total, 
            {$this->db->dbprefix('sales')}.paid, 
            ({$this->db->dbprefix('sales')}.grand_total - {$this->db->dbprefix('sales')}.paid) as balance,
            {$this->db->dbprefix('sales')}.payment_status, 
       
            {$this->db->dbprefix('sales')}.return_id")
        ->join('projects', 'sales.project_id = projects.project_id', 'left')
      
        ->join('users', 'sales.saleman_by = users.id', 'left')
    
        ->join($ds, 'FSI.sale_id=sales.id', 'left')
        ->order_by('sales.id', 'desc')
        ->from('sales')
        ->where('sales.order_tax',0);
        
        $this->datatables->where('sales.module_type','ticket');
        if ($warehouse_id) {
            $this->datatables->where('sales.warehouse_id', $warehouse_id);
        }
      

        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where_in("FIND_IN_SET(bpas_sales.warehouse_id, '" . $this->session->userdata('warehouse_id') . "')");
            $this->datatables->where("FIND_IN_SET(bpas_sales.created_by, '" . $this->session->userdata('user_id') . "')");
        }
        if ($this->Customer) {
            $this->datatables->where('projects.customer_id','bpas_projects.customer_id');
        }
        if ($this->input->get('shop') == 'yes') {
            $this->datatables->where('shop', 1);
        } elseif ($this->input->get('shop') == 'no') {
            $this->datatables->where('shop !=', 1);
        }
          
            if ($this->input->get('attachment') == 'yes') {
                $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', null);
            }

            if ($user_query) {
                $this->datatables->where('sales.created_by', $user_query);
            }
            if ($payment_status) {
                $get_status = explode('_', $payment_status);
                $this->datatables->where_in('sales.payment_status', $get_status);
            }
            if ($reference_no) {
                $this->datatables->where('sales.reference_no', $reference_no);
            }
            // if ($product_id) {
            //     $this->datatables->where('sales.product_id', $product_id);
            // }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($saleman_by) {
                $this->datatables->where('sales.saleman_by', $saleman_by);
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
      

        if ($a || $a == 'empty') {
            $alert_ids = explode('-', $a);
            $alert_id  = $a;

            if (count($alert_ids) > 1) {
                // $this->datatables->where('sales.payment_term <>', 0);
                // $this->datatables->where('DATE_SUB(bpas_sales.date, INTERVAL 1 DAY) <= CURDATE()');
                $this->datatables->where_in('sales.id', $alert_ids);
            } else {
                // $this->datatables->where('sales.payment_term <>', 0);
                // $this->datatables->where('DATE_SUB(bpas_sales.date, INTERVAL 1 DAY) <= CURDATE()');
                $this->datatables->where('sales.id', $alert_id);
            }
        }
    
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->datatables->where($this->db->dbprefix('sales') . '.pos !=', 1); // ->where('sale_status !=', 'returned');
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }

   	public function ticket()
    {
        $year = $this->input->post('year') ? $this->input->post('year') : date("Y");
        $month = $this->input->post('month') ? $this->input->post('month') : date("m");

        $this->bpas->checkPermissions('expenses');
        if ($_POST) {
            $this->data['categories'] = $this->site->getAllTickets($_POST);
        }
       
        $this->data['expenses'] =$this->site->getAllSuspendAndSaleItem($year,$month);

        $this->data['users'] = $this->site->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('room'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('daily_room')));
        $meta = array('page_title' => lang('daily_room'), 'bc' => $bc);
        $this->page_construct('hotel_apartment/list_table', $meta, $this->data);
    }

    public function list_ticket($warehouse_id = null)
    {

        $this->bpas->checkPermissions();
        $count = explode(',', $this->session->userdata('warehouse_id'));
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            if (count($count)>1) {
                $this->data['warehouses']   = $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }
            $this->data['count_warehouses'] = $count;
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }

        // $this->data['alert_id'] = isset($_GET['alert_id']) ? $_GET['alert_id'] : null;
        $this->data['alert_id'] = isset($_GET['alert_id']) ? ($_GET['alert_id'] !== '' ? $_GET['alert_id'] : 'empty')  : null;

        $biller_id = $this->session->userdata('biller_id');
        $this->data['users'] = $this->site->getStaff();
        $this->data['products'] = $this->site->getProducts();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['user_billers'] = $this->site->getCompanyByID($biller_id);
        $this->data['drivers']  = $this->site->getDriver();
        
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('ticket')]];
        $meta = ['page_title' => lang('ticket'), 'bc' => $bc];
        $this->page_construct('hotel_apartment/index_ticket', $meta, $this->data);
    }
    
    public function ticket_booking($sale_order_id = null, $quote_id = null, $tickets = [])
    {

        $this->bpas->checkPermissions();
        $getexchange_khm = $this->bpas->getExchange_rate('KHR');
        $getexchange_bat = $this->bpas->getExchange_rate('THB');
        $exchange_khm    = $getexchange_khm->rate;
        $exchange_bat    = $getexchange_bat->rate;

        if($sale_order_id){
            $sale_o = $this->sales_order_model->getSaleOrder($sale_order_id); 
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->order_status) == 'pending'){
                $this->session->set_flashdata('error', lang("sale_order_n_approved"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->order_status) == 'rejected'){
                $this->session->set_flashdata('error', lang("sale_order_has_been_rejected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->sale_status) == 'completed'){
                $this->session->set_flashdata('error', lang("sale_order_has_been_created"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
        
        $arr = $this->input->post('ticket_timeout');
        $timeout = $this->input->post('timeout');
        $sale_id = $sale_order_id ? $sale_order_id : null;
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        // $this->form_validation->set_rules('customer', lang('customer'), 'required');
        // $this->form_validation->set_rules('biller', lang('biller'), 'required');
        // $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        // $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        // $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');

        if ($this->form_validation->run() == true) {
            
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');

            // $reference = $this->site->CheckedSaleReference($this->input->post('reference_no'), $this->site->getReference('so'));

            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $project_id             = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id           = $this->input->post('warehouse');
            $customer_id            = $this->input->post('customer');
            $biller_id              = $this->input->post('biller');
            $booking_date           = isset($_POST['date']) ? $this->bpas->fld(trim($_POST['date'])) : '';
            $total_items            = $this->input->post('total_items');
            $sale_status            = $this->input->post('sale_status');
            $payment_status         = $this->input->post('payment_status');
            $payment_term           = $this->input->post('payment_term');
            $payment_term_details   = $this->site->getAllPaymentTermByID($payment_term);
            // $due_date            = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            $due_date               = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping               = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details       = $this->site->getCompanyByID($customer_id);
            $customer               = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details         = $this->site->getCompanyByID($biller_id);
            $biller                 = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note                   = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note             = $this->bpas->clear_tags($this->input->post('staff_note'));
            $quote_id               = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;
            // $total_weight        = 0;
            $commission_product     = 0;
            $text_items             = "";
            $total                  = 0;
            $product_tax            = 0;
            $product_discount       = 0;
            $digital                = false;
            $gst_data               = [];
            $total_cgst             = $total_sgst       = $total_igst       = 0;
            $i                      = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_warranty      = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] : '';
                $item_weight        = 0;
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                $checkin_date       = isset($_POST['checkin_date'][$r]) ? $this->bpas->fld(trim($_POST['checkin_date'][$r])) : '';
                

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $cost = 0;
                    
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->bpas->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal     = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit         = $this->site->getUnitByID($item_unit);
                    $total_weight = number_format((float) ($item_weight * $item_unit_quantity), 4, '.', '');

                    $saleman = $this->site->getuser($this->input->post('saleman_by'));
                    
                    $product = [
                        'product_id'         => $item_id,
                        'product_code'       => $item_code,
                        'product_name'       => $item_name,
                        'product_type'       => $item_type,
                        'option_id'          => $item_option,
                        'purchase_unit_cost' => 0,
                        'net_unit_price'     => $item_net_price,
                        'unit_price'         => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity'           => $item_quantity,
                        'product_unit_id'    => $unit ? $unit->id : null,
                        'product_unit_code'  => $unit ? $unit->code : null,
                        'unit_quantity'      => $item_unit_quantity,
                        'warehouse_id'       => $warehouse_id,
                        'item_tax'           => $pr_item_tax,
                        'tax_rate_id'        => $item_tax_rate,
                        'tax'                => $tax,
                        'discount'           => $item_discount,
                        'item_discount'      => $pr_item_discount,
                        'subtotal'           => $this->bpas->formatDecimal($subtotal),
                        'serial_no'          => $item_serial,
                        'max_serial'         => $item_max_serial,
                        'real_unit_price'    => $real_unit_price,
                        'comment'            => $item_detail,
                        'check_in'           => $checkin_date,
                        'from_id'            => $this->input->post('from'),
                        'timeout_id'         => $this->input->post('time_out'),
                        'date_booking_ticket'=> $booking_date,
                        'destination_id'     => $this->input->post('destination'),
                    ];
                    
                 
                    $text_items .=  $r+1 . "/ " . $item_name . "(".$item_code.")" ." | ". $item_quantity." | ".$this->bpas->formatDecimal($real_unit_price) ." | ". $pr_item_discount ." | ". $this->bpas->formatDecimal($subtotal)."\n";
                    //========add accounting=========//
    
                    if($this->Settings->accounting == 1 && $item_type != 'manual' && ($sale_status=='completed' || $sale_status=='consignment')){
                        $getproduct = $this->site->getProductByID($item_id);

                
                            $default_sale  = $default_sale = ($item_type == 'standard') ? $this->accounting_setting->default_sale : $this->accounting_setting->other_income;
                            $inventory_acc = $this->accounting_setting->default_stock;
                            $costing_acc   = $this->accounting_setting->default_cost;
                        
                        $accTrans[] = array(
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_stock,
                            'amount' => -($cost * $item_unit_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),

                        );
                        $accTrans[] = array(
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_cost,
                            'amount' => ($cost * $item_unit_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_cost),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost)
                        );

                        $accTrans[] = array(
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $default_sale,
                            'amount' => - $subtotal,
                            'narrative' => $this->site->getAccountName($default_sale),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                        );
                
                    }
                    //============end accounting=======//

                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $saleman_award_points = 0;

            $staff = $this->site->getUser($this->input->post('saleman_by'));
            if($staff->save_point){
                if (!empty($this->Settings->each_sale)) {
                    $saleman_award_points = floor(($grand_total / $this->Settings->each_sale) * $this->Settings->sa_point);
                }
            }

            //=======acounting=========//
            if($this->Settings->accounting == 1){
                //   $saleAcc = $this->site->getAccountSettingByBiller($biller_id);
                $saleAcc = $this->site->getAccountSettingByBiller();
                if($order_discount != 0){
                    $accTrans[] = array(
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_discount,
                        'amount' => $order_discount,
                        'narrative' => 'Order Discount',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if($order_tax != 0){
                    $accTrans[] = array(
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_tax,
                        'amount' => -$order_tax,
                        'narrative' => 'Order Tax',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if($shipping != 0){
                    $accTrans[] = array(
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_freight,
                        'amount' => -$shipping,
                        'narrative' => 'Shipping',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
            }
            //============end accounting=======//
            $data       = [
                'date'                => $date,
                'project_id'          => $this->input->post('project'),
                'so_id'               => $this->input->post('sale_order_id') ? $this->input->post('sale_order_id') : null,
                'reference_no'        => $reference,
                'customer_id'         => $customer_id,
                'customer'            => $customer,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'warehouse_id'        => $warehouse_id,
                'note'                => $note,
                'staff_note'          => $staff_note,
                'total'               => $total,
                'product_discount'    => $product_discount,
                'order_discount_id'   => $this->input->post('order_discount'),
                'order_discount'      => $order_discount,
                'total_discount'      => $total_discount,
                'product_tax'         => $product_tax,
                'order_tax_id'        => $this->input->post('order_tax'),
                'order_tax'           => $order_tax,
                'total_tax'           => $total_tax,
                'shipping'            => $this->bpas->formatDecimal($shipping),
                'grand_total'         => $grand_total,
                'total_items'         => $total_items,
                'sale_status'         => $sale_status,
                'payment_status'      => $payment_status,
                'payment_term'        => $payment_term,
                'due_date'            => $due_date,
                'paid'                => 0, 
                'created_by'          => $this->session->userdata('user_id'),
                'hash'                => hash('sha256', microtime() . mt_rand()),
                'saleman_by'          => $this->input->post('saleman_by'),
                'module_type'         => 'ticket',
                'currency_rate_kh'    => $exchange_khm,
                'currency_rate_bat'   => $exchange_bat,
                'date_in'             => $this->bpas->fld(trim($this->input->post('arrival'))),
                'date_out'            => $this->bpas->fld(trim($this->input->post('departure'))),
            ];

            if ($payment_status == 'partial' || $payment_status == 'paid') {
                if ($this->input->post('paid_by') == 'deposit') {
                    if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                        $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
                if ($this->input->post('paid_by') == 'gift_card') {
                    $gc            = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance    = $gc->balance - $amount_paying;
                    $payment       = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->bpas->formatDecimal($amount_paying),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('gift_card_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                        'gc_balance'   => $gc_balance,
                    ];
                } else {
                    $payment = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->bpas->formatDecimal($this->input->post('amount-paid')),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('pcc_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                    ];
                }
                $amount_paying = $this->bpas->formatDecimal($this->input->post('amount-paid'));
                    //=====add accountig=====//
                if($this->Settings->accounting == 1){
                    if($this->input->post('paid_by') == 'deposit'){
                        $payment['bank_account'] = $saleAcc->default_sale_deposit;
                        $paying_to = $saleAcc->default_sale_deposit;
                    }else{
                        $payment['bank_account'] = $this->input->post('bank_account');
                        $paying_to = $this->input->post('bank_account');
                    }

                    if($amount_paying < $grand_total){
                        $accTranPayments[] = array(
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'account_code' => $this->accounting_setting->default_receivable,
                            'amount' => ($grand_total - $amount_paying),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description' => $this->input->post('payment_note'),
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                        );
                    }
                
                    $accTranPayments[] = array(
                        'tran_type' => 'Payment',
                        'tran_date' => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'account_code' => $paying_to,
                        'amount' => $amount_paying,
                        'narrative' => $this->site->getAccountName($paying_to),
                        'description' => $this->input->post('payment_note'),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                    //=====end accountig=====//
            } else {
                $accTranPayments= [];
                $payment = [];
                $accTrans[] = array(
                    'tran_type' => 'Sale',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $this->accounting_setting->default_receivable,
                    'amount' => $grand_total,
                    'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                    'biller_id' => $biller_id,
                    'project_id' => $project_id,
                    'customer_id' => $customer_id,
                    'created_by'  => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                );
            }
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
                        
            //----checked orver credit--------
            $cus_sales         = $this->sales_model->getSalesTotals($customer_id);
            if(($customer_details->credit_limit !=0) && (($cus_sales->total_amount - $cus_sales->paid) + $data['grand_total']) > $customer_details->credit_limit){
                $this->session->set_flashdata('error', lang("customer_due_over_credit_amount"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }


        if ($this->form_validation->run() == true && $this->sales_model->addTicket($data, $products, $payment, $si_return = array(), $accTrans, $accTranPayments, null, $commission_product)) {
            $this->session->set_userdata('remove_slls', 1);
            
            if ($quote_id) {
                $this->db->update('quotes', ['status' => 'completed'], ['id' => $quote_id]);
            }
            if ($sale_id) {
                $status = 'completed';
                $sale_order_id    = $this->input->post('sale_order_id');
                $sale_items       = $this->site->getSaleItemsBySaleOrderID($sale_order_id);
                $sale_order_items = $this->site->getSaleOrderItemsBySaleID($sale_id);

                foreach($sale_order_items as $item){
                    $key = array_search($item->product_code, array_column($sale_items, 'product_code'));
                    if($key !== false){
                        if($item->quantity > $sale_items[$key]->quantity){
                            $status = 'partial';
                            break;
                        }
                    } else {
                        $status = 'partial';
                        break;
                    }
                }

                $this->db->update('sales_order', array('sale_status' => $status), array('id' => $sale_id));
            }
            $t_customer = $this->site->getCompanyByID($customer_id);
            $header = lang("no"). " /     ".lang("name"). "  (".lang("code").")"."    |    ". lang("qty")."    |    ".lang("price") ."    |    ". lang("discount") ."    |    ". lang("total");
            $this->session->set_flashdata('message', lang('ticket_added'));
            admin_redirect('room/list_ticket');
        } else {
            // $tickets = $this->input->get('tk') ? $this->input->get('tk') : null;
            $tickets = $arr ;
           
            if ($quote_id || $sale_id || $tickets) {
                if ($quote_id) {
                    $this->data['quote'] = $this->quotes_model->getQuoteByID($quote_id);
                    $items               = $this->quotes_model->getAllQuoteItems($quote_id);
                    $this->data['inv']   = $this->data['quote'];
                    $sale_items          = [];
                } elseif ($sale_id) {
                    $this->data['quote'] = $this->sales_order_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_order_model->getAllInvoiceItems($sale_id);
                    $this->data['inv']   = $this->data['quote'];
                    $sale_items          = $this->site->getSaleItemsBySaleOrderID($sale_id);
                } elseif ($tickets) {
                    $arr_ticket = [];
                    $arr_tickets = [];
                    $ticket = $tickets;
                    foreach($ticket as $tk){
                        if($tk != ''){
                            $ml = explode("__", $tk);
                            $arr_ticket[] = $ml;
                        }
                    }
                    $this->data['quote'] = $this->sales_order_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_order_model->getTicket($arr_ticket); 
                    // var_dump($items);
                    // exit();
                    $this->data['inv']   = $this->data['quote'];
                    $sale_items          = $this->site->getSaleItemsBySaleOrderID($sale_id);
                }
        
                $warehouse_id   = $items[0]->warehouse_id;
                $customer_id    = $this->pos_settings->default_customer;
                $warehouse      = $this->site->getWarehouseByID($warehouse_id);
                $customer       = $this->site->getCompanyByID($customer_id);
                $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
                if($items){
                    
                    $r = 0; $pr = array();
                    foreach ($items as $row) {
                        $j = sizeof($arr_ticket);
                        $arr = [];
                        for ($i=0; $i < $j; $i++) { 
                            $arr = $arr_ticket[$i];
                            if($row->note_id == $arr[0]){
                                $timeout = $this->site->getcustomfieldById($arr[1]); 
                            }
                        }
                        $c = uniqid(mt_rand(), true);
                        $option               = false;
                        $row->quantity        = 0;
                        $row->item_tax_method = 0;
                        $row->qty             = 1;
                        $row->discount        = '0';
                        $row->serial          = '';
                        $options              = false;
                        $product_options      = null;
                        $row->quantity        = 0;
                        $row->code            = '';
                        $opt                  = json_decode('{}');
                        $opt->price           = 0;
                        $option_id            = false;
                        $row->option          = $option_id;
                        $row->price           = $row->price + (($row->price * $customer_group->percent) / 100); 
                        $row->real_unit_price = $row->price;
                        $row->base_quantity   = 1;
                        $row->base_unit       = $row->bed;
                        $row->base_unit_price = $row->price;
                        $row->unit            = $row->bed;
                        $row->timeout_id     = $timeout->id;
                        $row->timeout_name     = $timeout->name;
                        $row->timeout_description     = $timeout->description;
                        $row->comment         = '';
                        $combo_items          = false;
                        $categories           = null;
                        $units                = false;
                        $tax_rate             = null;
                        $set_price = $this->site->getUnitByProId($row->id);
                        $set_price = '';

                        $ri = $this->Settings->item_addition ? $row->id : sha1($c . $r);
                    
                        $pr[$ri] = [    'id'                => sha1($c . $r),
                                        'item_id'           => $row->id,
                                        'label'             => $row->name,
                                        'category'          => null,
                                        'row'               => $row,
                                        'combo_items'       => $combo_items,
                                        'tax_rate'          => $tax_rate,
                                        'set_price'         => $set_price,
                                        'units'             => $units,
                                        'options'           => $options,
                                        'fiber'             => null,
                                        'product_options'   => $product_options,
                                    ];
                        $r++;
                    }
                    $this->data['quote_items'] = json_encode($pr);
                }else{
                    $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
                }       
            }

            $this->data['customer']      = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
            $this->data['count']         = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']      = $this->site->getAllProject();
            $this->data['error']         = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id']      = $quote_id ? $quote_id : $sale_id;
            $this->data['tickets']       = $tickets;

            $this->data['sale_order_id'] = $sale_order_id;
            $this->data['billers']       = $this->site->getAllCompanies('biller');
            $this->data['data']          = $this->site->getBillerByUser($this->session->userdata('user_id'));
            $companyID                   = explode(',',$this->data['data']->multi_biller);
            $this->data['mbillers']      = $this->site->getAllCompaniesByBiller('biller', $companyID);
            $this->data['agencies']      = $this->site->getAllUsers();
            $this->data['payment_term']  = $this->site->getAllPaymentTerm();
            $this->data['warehouses']    = $this->site->getAllWarehouses();
            $this->data['tax_rates']     = $this->site->getAllTaxRates();
            $this->data['units']         = $this->site->getAllBaseUnits();
            $this->data['zones']         = $this->site->getAllZones();
            $this->data['suspend_notes'] = $this->table_model->getAll_suspend_note();
            $this->data['group_price']   = json_encode($this->site->getAllGroupPrice());
            $Settings                    = $this->site->getSettings();
            $this->data['salemans']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            //$this->data['currencies']  = $this->sales_model->getAllCurrencies();
            $this->data['slnumber']      = $this->site->getReference('so');
            $this->data['sltaxnumber']   = $this->site->getReference('st');
            $this->data['payment_ref']   = ''; //$this->site->getReference('pay');
            $bc                          = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('add_ticket')]];
            $meta                        = ['page_title' => lang('add_ticekt'), 'bc' => $bc];
            $this->page_construct('hotel_apartment/booking_ticket', $meta, $this->data);
        }
    }
}