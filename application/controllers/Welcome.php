<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'core/Parents_Controllers.php');

class Welcome extends Parents_Controllers {
    
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
        $this->resx = $this->Main_model->get_last(30,0,0)->result();
        $this->count = $this->Main_model->get_last(30,0,1);
        $data['record'] = $this->count; 
        $data['result'] = $this->resx; 
//        $this->load->view('index.html');
        $this->output_response($data);
    }
    
    function login(){
        echo 'login';
    }
    
}
