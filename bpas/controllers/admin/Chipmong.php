<?php defined('BASEPATH') or exit('No direct script access allowed');

class Chipmong extends MY_Controller
{
    function __construct() {
        parent::__construct();
        $this->lang->admin_load('sales', $this->Settings->user_language);
        $this->load->admin_model('sales_model');

        $this->methods['index_get']['limit'] = 500;
        $url_endpoint       = 'https://edssapiuat.chipmongretail.com:8000/';
        $this->url_api 		= $url_endpoint.'api/v1/Authentication/login';
        $this->insert_url 	= $url_endpoint.'api/v1/sale/insertsalemegamall';
        $this->user 	    = 'TestUser';
        $this->pass 	    = '123';
    }

    public function index($biller_id = null)
    {
        // $this->bpas->checkPermissions();
        $user  = $this->site->getUser($this->session->userdata('user_id'));
        $count = $user->biller_id ? ((array) $user->biller_id) : null;
        if($this->input->post('submit_report')){
            $biller         = $this->input->post('biller') ? $this->input->post('biller') : null;
            $start_date     = $this->input->post('start_date') ? $this->input->post('start_date') : null;
            $end_date       = $this->input->post('end_date') ? $this->input->post('end_date') : null;
            if ($start_date) {
                $start_date = $this->bpas->fld($start_date . ' 00:00:00');
                $end_date   = $this->bpas->fld($end_date . ' 23:59:00');
            }
            $this->db->select("*")->from('sales');
            // $this->db->where('sales.pos', 1);
            $this->db->where(array('biller_id' => $biller, 'chipmong' => 0));
            $this->db->where($this->db->dbprefix('sales') . '.date >= "' . $start_date . '"');
            $this->db->where($this->db->dbprefix('sales') . '.date <= "' . $end_date . '"');
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $sale_id = $row->id;
                    $item['sale_id']       = $sale_id;
                    $item['biller_id']     = $row->biller_id;
                    $item['generate_time'] = time();
                    $insert_sale = $this->db->insert('chipmong', $item);
                    if($insert_sale){
                        $this->db->update('sales', array('chipmong' => 1), array('id' => $sale_id));
                    }
                }
            }
            $this->session->set_flashdata('message', lang("sale_sucessful_generated"));
            admin_redirect("chipmong");
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || empty($count)) {
            $this->data['billers']   = $this->site->getAllCompanies('biller');
            $this->data['biller']    = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        } else {
            // $this->data['billers']   = null;
            $this->data['billers']       = $this->site->getAllCompanies('biller');
            $this->data['count_billers'] = $count;
            $this->data['user_biller']   = (isset($count) && count($count) == 1) ? $this->site->getCompanyByID($this->session->userdata('biller_id')) : null;
            $this->data['biller']        = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        }
        $this->data['biller_id']   = $biller_id?$biller_id:'';
        

        $this->data['products']         = $this->site->getProducts();
        $this->data['warehouses']       = $this->site->getAllWarehouses();
        $this->data['count_warehouses'] = explode(',', $this->session->userdata('warehouse_id'));
        $this->data['drivers']          = $this->site->getDriver();

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales')]];
        $meta = ['page_title' => lang('sales'), 'bc' => $bc];
        $this->page_construct('sales/chipmong_sale', $meta, $this->data);
    }

    public function getSales($biller_id = null)
    {
        // $this->bpas->checkPermissions('index');
        $biller         = $this->input->get('biller') ? $this->input->get('biller') : null;
        $start_date     = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date       = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date . ' 00:00:00');
            $end_date   = $this->bpas->fld($end_date . ' 23:59:00');
        }
        $detail_link    = anchor('admin/chipmong/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_report'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $delete_link    = "<a href='#' class='po' title='<b>" . lang('delete_generate') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('chipmong/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_generate') . '</a>';

        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_link . '</li>
                <li class="delete">' . $delete_link . '</li>
            </ul>
        </div></div>';

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('chipmong')}.id as id, 
                DATE_FORMAT({$this->db->dbprefix('chipmong')}.date, '%Y-%m-%d %T') as date,
                {$this->db->dbprefix('chipmong')}.grossSale,
                {$this->db->dbprefix('chipmong')}.taxAmount,
                {$this->db->dbprefix('chipmong')}.netSale,
                {$this->db->dbprefix('chipmong')}.cashAmountUsd,
                {$this->db->dbprefix('chipmong')}.creditCardAmount,
                {$this->db->dbprefix('chipmong')}.otherAmount,
                {$this->db->dbprefix('chipmong')}.totalCreditCardTransaction,
                {$this->db->dbprefix('chipmong')}.totalTransaction,
                {$this->db->dbprefix('chipmong')}.depositAmountUsd, 
                {$this->db->dbprefix('chipmong')}.depositAmountRiel, 
                {$this->db->dbprefix('chipmong')}.exchangeRate, 
                {$this->db->dbprefix('chipmong')}.push")
            ->from('chipmong')
            ->order_by('chipmong.id', 'desc');
        if ($biller_id) {
            $this->datatables->where_in('chipmong.biller_id', $biller_id);
        }
        if ($biller) {
            $this->datatables->where_in('chipmong.biller_id', $biller_id);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('chipmong') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }

    public function insert($tran_id)
    {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $this->url_api);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_POST, 1);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "
        {
            'userId': '".$this->user."',
            'pwd'   : '".$this->pass."' 
        }");
        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Accept: application/json";
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        $object = json_decode($buffer);
        $token = $object->token;

        if(isset($token)){
            $this->insertData($token, $tran_id);
        }
    }

    public function insertData($access_token, $tran_id)
    {
        $data = null;
        if ($tran_id) {
            $sales = $this->sales_model->getChipmongSaleItems($tran_id, true);
            $data  = '{
                "mallName": "CM271MegaMall",
                "tenantName": "CM271Skechers",
                "date": "' .  date("Y-m-d", strtotime($sales[0]->date)) . '",
                "grossSale": ' . $this->bpas->formatDecimal($sales[0]->gross_sale) . ',
                "taxAmount": ' . $this->bpas->formatDecimal($sales[0]->tax_amount) . ',
                "netSale": ' . $this->bpas->formatDecimal($sales[0]->net_sale) . ',
                "cashAmountUsd": ' . $this->bpas->formatDecimal($sales[0]->net_cash_sales) . ',
                "cashAmountRiel": ' . $this->bpas->formatDecimal(0) . ',
                "creditCardAmount": ' . $this->bpas->formatDecimal($sales[0]->creditcard_amount) . ',
                "otherAmount": ' . $this->bpas->formatDecimal($sales[0]->other_amount) . ',
                "totalCreditCardTransaction": ' . $sales[0]->creditcard_transaction . ',
                "totalTransaction": ' . $sales[0]->total_transaction . ',
                "depositAmountUsd": ' . $this->bpas->formatDecimal(0) . ',
                "depositAmountRiel": ' . $this->bpas->formatDecimal(0) . ',
                "exchangeRate": ' . $this->bpas->formatDecimal($sales[0]->exchange_rate) . ',
                "posId": "Pos01"
            }'; 
        }
        $curl_handle = curl_init(); 
        curl_setopt($curl_handle, CURLOPT_URL, $this->insert_url);
        curl_setopt($curl_handle, CURLOPT_POST, 1);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
        $headers   = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Accept: application/json";
        $headers[] = 'Authorization:Bearer '.$access_token ;
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
        $buffer    = curl_exec($curl_handle);
        $httpcode  = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
        curl_close($curl_handle);
        if($httpcode != 200){ 
            if (curl_errno($curl_handle)) {  
                $error_msg = curl_error($curl_handle);  
            } else { 
                $error_msg = $buffer; 
            } 
            throw new Exception('Error '.$httpcode.': '.$error_msg); 
        } else { 
            if ($this->sales_model->updateChipmongStatus($tran_id)) {
                $this->session->set_flashdata('message', 'Successful push data to chipmong.');
                $this->bpas->send_json(true);
                $object = json_decode($buffer);
                print_r($object);
            }
            $this->session->set_flashdata('error', 'Push data to chipmong fail!');
            $this->bpas->send_json(false);
        }
        return false;
    }

    public function modal_view($id = null)
    {
        // $this->bpas->checkPermissions('index', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['id']    = $id;
        $this->data['chipmong'] = $this->sales_model->getChipmongByID($id);
        $this->data['rows']     = $this->sales_model->getChipmongSalesDetails($this->data['chipmong']->sale_id);
        $this->load->view($this->theme . 'sales/modal_chipmong', $this->data);
    }

    public function delete($id = null)
    {
        // $this->bpas->checkPermissions(null, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($id != '' && $id != null) {
            if ($this->sales_model->deleteChipmong($id)) {
                if ($this->input->is_ajax_request()) {
                    $this->bpas->send_json(['error' => 0, 'msg' => lang('Data_generate_deleted')]);
                }
                $this->session->set_flashdata('message', lang('Data_generate_deleted'));
                admin_redirect("chipmong");
            }
        }
    }
}