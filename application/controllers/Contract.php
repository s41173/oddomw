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
            $this->resx  = $this->Main_model->get_last($datax['filter'],$this->limitx, $this->offsetx,0)->result();
            $this->count = $this->Main_model->get_last($datax['filter'],$this->limitx, $this->offsetx,1);
            
            if (isset($datax['filter'])){ 
               $this->resx = $this->Main_model->get_last($datax['filter'],$this->limitx, $this->offsetx,0)->result();
               $this->count = $this->Main_model->get_last($datax['filter'],$this->limitx, $this->offsetx,1);
            }else{
               $this->resx = $this->Main_model->get_last($this->limitx, $this->offsetx,0)->result();
               $this->count = $this->Main_model->get_last($this->limitx, $this->offsetx,1);
            }
           
            $data['record'] = $this->count; 
            $data['result'] = $this->resx; 
            $this->output_response($data);
            
        }else{ $this->reject_token(); }
    }
    
    
}
