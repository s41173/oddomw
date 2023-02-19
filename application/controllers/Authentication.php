<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'core/Parents_Controllers.php');
require_once APPPATH.'libraries/jwt/JWT.php';
use \Firebase\JWT\JWT;

class Authentication extends Parents_Controllers {
    
    function __construct()
    {
        parent::__construct();
        
//        $this->load->model('Main_model', '', TRUE);
        $this->load->model('Login_model', '', TRUE);
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }
    
    function index()
    {
        if ($this->otentikasi() == TRUE){
            $datax = (array)json_decode(file_get_contents('php://input')); 
            $this->limitx=10; $this->offsetx=0;
            
            if (isset($datax['limit']) && isset($datax['offset'])){
                $this->limitx = $datax['limit']; $this->offsetx = $datax['offset'];
            }
            
            $this->resx = $this->Login_model->get_last($this->limitx, $this->offsetx,0)->result();
            $this->count = $this->Login_model->get_last($this->limitx, $this->offsetx,1);
            $data['record'] = $this->count; 
            $data['result'] = $this->resx; 
            $this->output_response($data);
            
        }else{ $this->reject_token(); }
    }
    
    function set_token(){
      $datax = (array)json_decode(file_get_contents('php://input'));
      if (isset($datax['userid']) && isset($datax['token'])){
          
          $stts = $this->Login_model->set_token($datax['userid'],$datax['token']);
          if ($stts == true){ $this->error = 'Token set'; }else{ $this->status = 403; $this->error = "Failed to set token"; }
      }
      else{ $this->status = 400; $this->error = 'User / Token Not Set'; }
      $output = array('error' => $this->error); 
      $this->output_response($output, $this->status);
    }
    
    function login()
    {
        $datax = (array)json_decode(file_get_contents('php://input')); 

        $logid = null;
        $token = null;
        
        if (isset($datax['user']) && isset($datax['pass'])){

            $username = $datax['user'];
            $password = $datax['pass'];
            $token = null;
            
            if ($this->Login_model->login($username,$password) == TRUE)
            {
                $this->date  = date('Y-m-d');
                $this->time  = waktuindo();
//                $role = $this->Login_model->get_role($username);
//                $rules = $this->Login_model->get_rules($role);
//                $branch = $this->Login_model->get_branch($username);
//                $logid = intval($this->log->max_log()+1);
                $waktu = tgleng(date('Y-m-d')).' - '.waktuindo().' WIB';
                $userid = $this->Login_model->get_by_username($username);
                
                // create branch
                
                // add JWT
                $payload['username'] = $username;
                $payload['userid'] = $userid;
//                $payload['role'] = $role;
//                $payload['rules'] = $rules;
//                $payload['log'] = $logid;
                $payload['datetime'] = $waktu;
//                $payload['branch'] = $branch;
                $token = JWT::encode($payload, 'inl');
                $this->Login_model->set_token($userid,$token);  
//                $this->log->insert($userid, $this->date, $this->time, 'login');
//                $this->login->add($userid, $logid, $token);
            }
            else{ $this->status = 401; $this->error = 'Invalid Login'; }
       }else{ $this->status = 401; $this->error = 'Invalid Format'; }   
       
       $output = array('token' => $token,'error' => $this->error); 
//       print_r($output);
       $this->output_response($output, $this->status);
    }
    
    function decode(){
        if ($this->input->server('REQUEST_METHOD') != 'OPTIONS'){
            $jwt = $this->input->get_request_header('X-auth-token');
            if ($this->Login_model->cek_token($jwt) == TRUE)
            {
                $decoded = JWT::decode($jwt, 'inl', array('HS256'));
                if ($this->Login_model->cek_token_user($decoded->userid,$jwt) == TRUE){
                  try{
                    $error = $decoded;
                  }
                  catch (\Exception $e){ 
                      $this->status = 400; $this->error = 'Error Encoding Token';
                  }
                }else{ $this->error = 'Authentication Failed'; $this->status = 401; }
            }else{ $this->status = 401; $this->error = 'Token Not Found'; }

        }
        $this->output_response($this->error, $this->status);
    }
    
    function decode_token(){
        if ($this->input->server('REQUEST_METHOD') != 'OPTIONS'){
            $jwt = $this->input->get_request_header('X-auth-token');
            if ($this->Login_model->cek_token($jwt) == TRUE)
            {
                $result = $this->Login_model->get_user_by_token($jwt);
                $data['userid'] = $result->id;
                $data['username'] = $result->login;
                $data['role'] = $result->level_ext;
                $this->error = $data;
            }else{ $this->status = 401; $this->error = 'Token Not Found'; }
        }
        $this->output_response($this->error, $this->status);
    }
    
    function logout(){ 
        if ($this->input->server('REQUEST_METHOD') != 'OPTIONS'){
            $jwt = $this->input->get_request_header('X-auth-token');
            if ($this->Login_model->cek_token($jwt) == TRUE)
            {
                $decoded = JWT::decode($jwt, 'inl', array('HS256'));
                if ($this->Login_model->cek_token_user($decoded->userid,$jwt) == TRUE){
                    $stts = $this->Login_model->set_token($decoded->userid,null);
                    if ($stts == TRUE){ $this->error = 'Logout Success'; }
                }else{ $this->error = 'Authentication Failed'; $this->status = 401; }
            }else{ $this->status = 401; $this->error = 'Token Not Found'; }

        }
        $data['error'] = $this->error;
        $this->output_response($data, $this->status);
    }
    
}
