<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'core/Parents_Controllers.php');
require_once APPPATH.'libraries/jwt/JWT.php';
use \Firebase\JWT\JWT;

class Contract extends Parents_Controllers {
    
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Main_model', '', TRUE);
        $this->load->model('Stock_qty_model', '', TRUE);
        $this->load->library('Product_lib');
        $this->load->library('Stock_picking_lib');
        $this->load->library('Product_uom_lib');
        $this->load->library('Stock_location_lib');
        $this->load->library('Res_user_lib');
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }
    
    
    function index()
    {
        if ($this->otentikasi() == TRUE){
            $datax = (array)json_decode(file_get_contents('php://input')); 
            $this->limitx=10; $this->offsetx=0;

            if (isset($datax['limit']) && isset($datax['offset'])){ $this->limitx = $datax['limit']; $this->offsetx = $datax['offset'];}            
            if (isset($datax['filter']) && isset($datax['search_type'])){                 
              $this->resx = $this->Main_model->get_detail_list($datax['search_type'],$datax['filter'],$this->limitx, $this->offsetx,0)->result();
              $this->count = $this->Main_model->get_detail_list($datax['search_type'],$datax['filter'],$this->limitx, $this->offsetx,1);  
            }else{ $this->resx = "Search Type Not Set"; $this->status = 400; }
           
            $data['record'] = $this->count; 
            $data['result'] = $this->resx; 
            $this->output_response($data, $this->status);
            
        }else{ $this->reject_token(); }
    }
    
    function product(){
        if ($this->otentikasi() == TRUE){
            $datax = (array)json_decode(file_get_contents('php://input')); 
            $this->limitx=10; $this->offsetx=0;

            if (isset($datax['limit']) && isset($datax['offset'])){ $this->limitx = $datax['limit']; $this->offsetx = $datax['offset'];}            
//            if (isset($datax['filter'])){                 
              $result = $this->Stock_qty_model->get_last($this->limitx, $this->offsetx,0)->result();
              $this->count = $this->Stock_qty_model->get_last($this->limitx, $this->offsetx,1);  
//              $this->resx = $this->Stock_qty_model->get_last($datax['search_type'],$this->limitx, $this->offsetx,0)->result();
                foreach ($result as $res) {
                    $product = $this->product_lib->get_detail($res->product_id);
                    $this->resx[] = array ("id"=>$res->stock_quant_id, "product_id"=>$res->product_id, "company_id"=>$res->company_id, "location_id"=>$res->location_id,
                                           "quantity"=>floatval($res->quantity), "reserved_quantity"=>floatval($res->reserved_quantity),"product_detail"=>$product,
                                           "stock_location_name"=>$res->stock_location_name, "stock_location_complete_name"=>$res->complete_name
                                          );
                }
              
//            }else{ $this->resx = "Search Type Not Set"; $this->status = 400; }
           
            $data['record'] = $this->count; 
            $data['result'] = $this->resx; 
            $this->output_response($data, $this->status);
            
        }else{ $this->reject_token(); }
    }
    
     function xproduct(){
        if ($this->otentikasi() == TRUE){
            $datax = (array)json_decode(file_get_contents('php://input')); 
            $this->limitx=10; $this->offsetx=0;

            if (isset($datax['limit']) && isset($datax['offset'])){ $this->limitx = $datax['limit']; $this->offsetx = $datax['offset'];}            
            if (isset($datax['filter']) && isset($datax['search_type'])){                 
              $this->resx = $this->product_lib->get_detail()->result();
//              $this->count = $this->product->get_detail(0,1);
//                $this->resx = $this->product_lib->test();
            }else{ $this->resx = "Search Type Not Set"; $this->status = 400; }
           
            $data['record'] = $this->count; 
            $data['result'] = $this->resx; 
            $this->output_response($data, $this->status);
            
        }else{ $this->reject_token(); }
    }
    
    function post()
    {
        if ($this->otentikasi() == TRUE){

	// Form validation
        $this->form_validation->set_rules('pickingid', 'Picking-ID', 'required|callback_valid_picking'); 
//        $this->form_validation->set_rules('pickingname', 'Picking Name', 'required');
        $this->form_validation->set_rules('sequence', 'Sequence', 'required');
        $this->form_validation->set_rules('product_id', 'Product-ID', 'required|numeric|callback_valid_product');
        $this->form_validation->set_rules('product_uom_id', 'Product UOM-ID', 'required|numeric|callback_valid_uom');
        $this->form_validation->set_rules('location_id', 'Location-ID', 'required|numeric|callback_valid_location');
        $this->form_validation->set_rules('location_dest_id', 'Location-Destination-ID', 'required|numeric|callback_valid_location');
        $this->form_validation->set_rules('do', 'DO', 'required');
        $this->form_validation->set_rules('nama_kendaraan', 'Nama Kendaraan', '');
        $this->form_validation->set_rules('no_container', 'No Container', '');
        $this->form_validation->set_rules('no_polisi', 'No Polisi', '');
        $this->form_validation->set_rules('transporter', 'Transporter', 'required');    
        
        $this->form_validation->set_rules('driver_name', 'Driver Name', '');
        $this->form_validation->set_rules('destination', 'Destination', 'required');
        $this->form_validation->set_rules('no_karcis_timbangan', 'No Karcis Timbangan', '');
        $this->form_validation->set_rules('no_surat_jalan', 'No Surat Jalan', '');
        $this->form_validation->set_rules('tgl_keluar_from', 'Tanggal Keluar From', 'required');
        $this->form_validation->set_rules('tgl_masuk_truk', 'Tanggal Masuk Truk', 'required');
        $this->form_validation->set_rules('tgl_keluar_truk', 'Tanggal Keluar Truk', 'required');
        $this->form_validation->set_rules('bruto_from', 'Bruto From', 'numeric|required');
        $this->form_validation->set_rules('tarra_from', 'Tarra From', 'numeric|required');
        $this->form_validation->set_rules('netto_from', 'Netto From', 'numeric|required');
        
        
        $this->form_validation->set_rules('bruto', 'Bruto', 'numeric|required');
        $this->form_validation->set_rules('tarra', 'Tarra', 'numeric|required');
        $this->form_validation->set_rules('qty_done', 'Qty Done', 'numeric|required');
        $this->form_validation->set_rules('netto_diff', 'Netto Diff', 'numeric|required');
        $this->form_validation->set_rules('netto_diff_persen', 'Netto Diff Persen', 'numeric|required');
        $this->form_validation->set_rules('ffa_from', 'FFA From', 'numeric|required');
        $this->form_validation->set_rules('mni_from', 'MNI From', 'numeric|required');
        $this->form_validation->set_rules('imp_from', 'IMP From', 'numeric|required');
        $this->form_validation->set_rules('iv_from', 'IV From', 'numeric|required');
        $this->form_validation->set_rules('mpt_degrees_from', 'MPT Degrees From', 'numeric|required');
        $this->form_validation->set_rules('color_from', 'Color From', 'numeric|required');
        
        $this->form_validation->set_rules('ffa', 'FFA', 'numeric|required');
        $this->form_validation->set_rules('mni', 'MNI', 'numeric|required');
        $this->form_validation->set_rules('imp', 'IMP', 'numeric|required');
        $this->form_validation->set_rules('iv', 'IV', 'numeric|required');
        $this->form_validation->set_rules('mpt_degrees', 'MPT Degrees', 'numeric|required');
        $this->form_validation->set_rules('color', 'Color', 'numeric|required');
        $this->form_validation->set_rules('no_segel1', 'No Segel 1', '');
        $this->form_validation->set_rules('no_segel2', 'No Segel 2', '');
        $this->form_validation->set_rules('no_segel3', 'No Segel 3', '');
        $this->form_validation->set_rules('partner_name', 'Partner Name', 'required');
        
        
        $this->form_validation->set_rules('no_do', 'No Do', 'required');
        $this->form_validation->set_rules('origin', 'Origin', 'required');
        $this->form_validation->set_rules('qty_box', 'Qty Box', 'numeric|required');
        $this->form_validation->set_rules('asal_pks', 'Asal PKS', 'required');
        $this->form_validation->set_rules('state', 'State', 'required');
        $this->form_validation->set_rules('create_uid', 'Create Uid', 'numeric|required|callback_valid_uid_create');
        $this->form_validation->set_rules('create_date', 'Create Date', 'required');
        $this->form_validation->set_rules('write_uid', 'Write UID', 'numeric|required|callback_valid_uid_write');
        $this->form_validation->set_rules('write_date', 'Write Date', 'required');
        $this->form_validation->set_rules('date', 'Date', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
//            $sales = array('cust_id' => $this->input->post('ccustomer'), 'contract_id' => setnol($this->input->post('ccontract')),
//                           'dates' => date("Y-m-d H:i:s"), 
//                           'branch_id' => $this->branch->get_branch_default(), 'cost' => $this->input->post('tcosts'),
//                           'due_date' => $this->input->post('tduedates'), 'payment_id' => $this->input->post('cpayment'), 
//                           'cash' => $this->input->post('ccash'), 
//                           'created' => date('Y-m-d H:i:s'));
//
//            if ($this->Sales_model->add($sales) != true){ $this->reject();
//            }else{ $this->Sales_model->log('create'); $this->output = $this->Sales_model->get_latest(); } 
        }
        else{ $this->error = validation_errors(); $this->status = 400;   }
        
        $data['result'] = null; 
        $data['error'] = $this->error;
        $this->output_response($data, $this->status);
        
        }else{ $this->reject_token(); }
    }
    
    function valid_picking($val)
    {
        $stts = $this->stock_picking_lib->cek_trans('id',$val);
        if ($stts != TRUE){
           $this->form_validation->set_message('valid_picking', "Invalid Picking-ID..!"); return FALSE;
        }else{ return TRUE;  }
    }
    
    function valid_product($val)
    {
        $stts = $this->product_lib->cek_trans('id',$val);
        if ($stts != TRUE){
           $this->form_validation->set_message('valid_product', "Invalid Product-ID..!"); return FALSE;
        }else{ return TRUE;  }
    }
    
    function valid_uom($val)
    {
        $stts = $this->product_uom_lib->cek_trans('id',$val);
        if ($stts != TRUE){
           $this->form_validation->set_message('valid_uom', "Invalid Product-UOM..!"); return FALSE;
        }else{ return TRUE;  }
    }
    
    function valid_location($val)
    {
        $stts = $this->stock_location_lib->cek_trans('id',$val);
        if ($stts != TRUE){
           $this->form_validation->set_message('valid_location', "Invalid Stock Location..!"); return FALSE;
        }else{ return TRUE;  }
    }
    
    function valid_uid_create($val)
    {
        $stts = $this->res_user_lib->cek_trans('id',$val);
        if ($stts != TRUE){
           $this->form_validation->set_message('valid_uid_create', "Invalid Uid..!"); return FALSE;
        }else{ return TRUE;  }
    }
    
    function valid_uid_write($val)
    {
        $stts = $this->res_user_lib->cek_trans('id',$val);
        if ($stts != TRUE){
           $this->form_validation->set_message('valid_uid_write', "Invalid Uid..!"); return FALSE;
        }else{ return TRUE;  }
    }
    
    
}
