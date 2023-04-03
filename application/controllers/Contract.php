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
        $this->load->model('Purchase_model', '', TRUE);
        $this->load->library('Product_lib');
        $this->load->library('Stock_picking_lib');
        $this->load->library('Product_uom_lib');
        $this->load->library('Stock_location_lib');
        $this->load->library('Res_user_lib');
        $this->load->library('Stock_picking_truck_lib');
        $this->load->library('Stock_quant_lib');
        $this->load->library('Res_partner_lib');
        
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
    
    function vendor()
    {
        if ($this->otentikasi() == TRUE){
            $datax = (array)json_decode(file_get_contents('php://input')); 
            $this->limitx=10; $this->offsetx=0;

            if (isset($datax['limit']) && isset($datax['offset'])){ $this->limitx = $datax['limit']; $this->offsetx = $datax['offset'];}            
            if (isset($datax['type'])){                 
              $this->resx = $this->res_partner_lib->get_last($datax['type'],$this->limitx, $this->offsetx,0)->result();
              $this->count = $this->res_partner_lib->get_last($datax['type'],$this->limitx, $this->offsetx,1);  
            }else{ $this->resx = "Type Not Set"; $this->status = 400; }
           
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
            if (isset($datax['filter'])){                 
              $result = $this->Stock_qty_model->get_last($datax['filter'],$this->limitx, $this->offsetx,0)->result();
              $this->count = $this->Stock_qty_model->get_last($datax['filter'],$this->limitx, $this->offsetx,1);  
//              $this->resx = $this->Stock_qty_model->get_last($datax['search_type'],$this->limitx, $this->offsetx,0)->result();
                foreach ($result as $res) {
                    
//                    print_r($res).'<br/>';
                    
//                    $product = $this->product_lib->get_detail($res->product_id);
                    $quant = $this->stock_quant_lib->quant_amount($res->id);
                    $product = null;
                    $this->resx[] = array ("id"=>$res->id, "product_id"=>null, "company_id"=>1, "location_id"=>$res->id,
                                           "quantity"=>intval($quant[0]), "reserved_quantity"=>intval($quant[1]),"product_detail"=>$product,
                                           "stock_location_name"=>$res->stock_location_name, "stock_location_complete_name"=>$res->complete_name
                                          );
                }
              
            }else{ $this->resx = "Search Type Not Set"; $this->status = 400; }
           
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
    
    
    function remove($uid=0){
        
        if ($this->otentikasi() == TRUE){
            
            if ($uid != 0){    
                if ($this->stock_picking_truck_lib->get_by_id($uid)->num_rows() > 0){
                   $stuck = $this->stock_picking_truck_lib->get_by_id($uid)->row();
                   if (!$stuck->state){
                     $this->stock_picking_truck_lib->force_delete($uid);
                   }else{ $this->resx = "Invalid State"; $this->status = 400;  }
                }
                else{ $this->resx = "ID Not Found"; $this->status = 404; }
               
            }else{ $this->resx = "Invalid ID"; $this->status = 400; }
           
            $data['record'] = $this->count; 
            $data['result'] = $this->resx; 
            $this->output_response($data, $this->status);
            
        }else{ $this->reject_token(); } 
    }
    
    
    function get_qty(){
        
        if ($this->otentikasi() == TRUE){
            $datax = (array)json_decode(file_get_contents('php://input')); 
            $this->limitx=10; $this->offsetx=0;

            if (isset($datax['limit']) && isset($datax['offset'])){ $this->limitx = $datax['limit']; $this->offsetx = $datax['offset'];}            
            if (isset($datax['filter'])){    
                
              $result = $this->Purchase_model->get_last($datax['filter'],$this->limitx, $this->offsetx,0)->result();
              $this->count = $this->Purchase_model->get_last($datax['filter'],$this->limitx, $this->offsetx,1);  
              foreach ($result as $res) {
                  
                $oustanding = floatval($res->product_uom_qty-$res->qty_received);
                $this->resx[] = array ("id"=>$res->po_id, "name"=>$res->name, "origin"=>$res->origin, "state"=>$res->state,
                                       "qty"=>floatval($res->product_uom_qty), "qty_received"=>floatval($res->qty_received), "qty_out_standing"=>$oustanding
                                      );
              }
              
            }else{ $this->resx = "Origin Not Set"; $this->status = 400; }
           
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
        $this->form_validation->set_rules('state', 'State', '');
        $this->form_validation->set_rules('create_uid', 'Create Uid', 'numeric|required|callback_valid_uid_create');
        $this->form_validation->set_rules('create_date', 'Create Date', 'required');
        $this->form_validation->set_rules('write_uid', 'Write UID', 'numeric|required|callback_valid_uid_write');
        $this->form_validation->set_rules('write_date', 'Write Date', 'required');
        $this->form_validation->set_rules('date', 'Date', 'required');
        
        $this->form_validation->set_rules('cloud_point', 'Cloud Point', '');
        $this->form_validation->set_rules('saponifiable_matter', 'Saponifiable Matter', '');
        $this->form_validation->set_rules('peroxide_value', 'Peroxide Value', '');
        $this->form_validation->set_rules('no_segel4', 'No Segel 4', '');
        $this->form_validation->set_rules('moist_from', 'Moist From', '');
        $this->form_validation->set_rules('moist', 'Moist', '');

        if ($this->form_validation->run($this) == TRUE)
        {
            $sales = array('picking_id' => $this->input->post('pickingid'), 
                           'picking_name' => $this->stock_picking_lib->get_name($this->input->post('pickingid')),
                           'sequence' => $this->input->post('sequence'), 'product_id' => $this->input->post('product_id'),
                           'product_uom_id' => $this->input->post('product_uom_id'), 'location_id' => $this->input->post('location_id'),
                           'location_dest_id' => $this->input->post('location_dest_id'), 'do' => $this->input->post('do'), 
                           'nama_kendaraan' => $this->input->post('nama_kendaraan'), 'no_container' => $this->input->post('no_container'), 
                           'no_polisi' => $this->input->post('no_polisi'), 'transporter' => $this->input->post('transporter'),
                           'driver_name' => $this->input->post('driver_name'), 'destination' => $this->input->post('destination'),
                           'no_karcis_timbangan' => $this->input->post('no_karcis_timbangan'), 'no_surat_jalan' => $this->input->post('no_surat_jalan'),
                           'tgl_keluar_from' => $this->input->post('tgl_keluar_from'), 'tgl_masuk_truk' => $this->input->post('tgl_masuk_truk'),
                           'tgl_keluar_truk' => $this->input->post('tgl_keluar_truk'), 'bruto_from' => $this->input->post('bruto_from'),
                           'tarra_from' => $this->input->post('tarra_from'), 'netto_from' => $this->input->post('netto_from'),
                           'bruto' => $this->input->post('bruto'), 'tarra' => $this->input->post('tarra'), 'qty_done' => $this->input->post('qty_done'),
                           'netto_diff' => $this->input->post('netto_diff'), 'netto_diff_persen' => $this->input->post('netto_diff_persen'), 'ffa_from' => $this->input->post('ffa_from'),
                           'mni_from' => $this->input->post('mni_from'), 'imp_from' => $this->input->post('imp_from'), 'iv_from' => $this->input->post('iv_from'), 'mpt_degrees_from' => $this->input->post('mpt_degrees_from'), 'color_from' => $this->input->post('color_from'),
                           'ffa' => $this->input->post('ffa'), 'mni' => $this->input->post('mni'), 'imp' => $this->input->post('imp'), 'iv' => $this->input->post('iv'),
                           'mpt_degrees' => $this->input->post('mpt_degrees'), 'color' => $this->input->post('color'),
                           'no_segel1' => $this->input->post('no_segel1'), 'no_segel2' => $this->input->post('no_segel2'), 'no_segel3' => $this->input->post('no_segel3'),
                           'partner_name' => $this->input->post('partner_name'), 
                           'no_do' => $this->input->post('no_do'), 'origin' => $this->input->post('origin'), 'qty_box' => $this->input->post('qty_box'), 'asal_pks' => $this->input->post('asal_pks'),
                           'create_uid' => $this->input->post('create_uid'), 'create_date' => $this->input->post('create_date'),
                           'write_uid' => $this->input->post('write_uid'),  'write_date' => $this->input->post('write_date'), 'date' => $this->input->post('date'),
                           'cloud_point' => $this->input->post('cloud_point'), 'saponifiable_matter' => $this->input->post('saponifiable_matter'), 'peroxide_value' => $this->input->post('peroxide_value'),
                           'no_segel4' => $this->input->post('no_segel4'), 'moist_from' => $this->input->post('moist_from'), 'moist' => $this->input->post('moist')
                    );
            
            if ($this->stock_picking_truck_lib->add($sales) != true){ 
                  $this->error = 'Failed to posted'; $this->status = 403;
            }else{ $this->resx = $this->stock_picking_truck_lib->get_latest(); } 
        }
        else{ $this->error = validation_errors(); $this->status = 400;   }
        
        $data['result'] = $this->resx; 
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
