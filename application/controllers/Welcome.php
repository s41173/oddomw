<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'core/Parents_Controllers.php');
require_once APPPATH.'libraries/jwt/JWT.php';
use \Firebase\JWT\JWT;

class Welcome extends Parents_Controllers {
    
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Main_model', '', TRUE);
        $this->load->model('Login_model', '', TRUE);
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    function index()
    {
        $this->resx = $this->Main_model->get_last(30,0,0)->result();
        $this->count = $this->Main_model->get_last(30,0,1);
        $data['record'] = $this->count; 
        $data['result'] = $this->resx; 
//        $this->load->view('index.html');
        $this->output_response($data);
    }
    
    function login()
    {
        $datax = (array)json_decode(file_get_contents('php://input')); 

        $status = 200;
        $error = "null";
        $logid = null;
        $token = null;
        
        if (isset($datax['user']) && isset($datax['pass'])){

            $username = $datax['user'];
            $password = $datax['pass'];
            $token = null;
            
            if ($this->Login_model->login($username) == TRUE && $password == '123456')
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
                $payload['time'] = $waktu;
//                $payload['branch'] = $branch;
                $token = JWT::encode($payload, 'inl');
                $this->Login_model->set_token($userid,$token);  
//                $this->log->insert($userid, $this->date, $this->time, 'login');
//                $this->login->add($userid, $logid, $token);
            }
            else{ $status = 401; $error = 'Invalid Login'; }
       }else{ $status = 401; $error = 'Invalid Format'; }   
       
       $output = array('token' => $token,'error' => $error); 
//       print_r($output);
       $this->output_response($output,$status);
    }
    
    function otentikasi(){
        $status = 200;
        $error = TRUE;
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
                      $status = 400; $error = 'Error Encoding Token';
                  }
                }else{ $error = 'Authentication Failed'; $status = 401; }
            }else{ $status = 401; $error = 'Token Not Found'; }

        }
        $this->output_response($error,$status);
    }
    
    function logout(){
       $status = 200;
       $error = TRUE; 
        if ($this->input->server('REQUEST_METHOD') != 'OPTIONS'){
            $jwt = $this->input->get_request_header('X-auth-token');
            if ($this->Login_model->cek_token($jwt) == TRUE)
            {
                $decoded = JWT::decode($jwt, 'inl', array('HS256'));
                if ($this->Login_model->cek_token_user($decoded->userid,$jwt) == TRUE){
                    $stts = $this->Login_model->set_token($decoded->userid,null);
                    if ($stts == TRUE){ $error = 'Logout Success'; }
                }else{ $error = 'Authentication Failed'; $status = 401; }
            }else{ $status = 401; $error = 'Token Not Found'; }

        }
        $this->output_response($error,$status);
    }
    
}
