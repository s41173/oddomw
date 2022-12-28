<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'core/Parents_Controllers.php');

class Main extends Parents_Controllers {
    
    function __construct()
    {
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }
    
    function index()
    {
        $this->load->view('index.html');
    }
    
}
