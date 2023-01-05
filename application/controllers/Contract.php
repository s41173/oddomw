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
    
    
}
