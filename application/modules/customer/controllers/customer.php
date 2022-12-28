<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
//require_once APPPATH.'libraries/jwt/JWT.php';
//use \Firebase\JWT\JWT;

class Customer extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
//        $this->load->model('Customer_model', 'model', TRUE);
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 

    }

    function index(){
        echo 'Ini index customer';
    }
   // ========================== api ==========================================
    
            // ------ json login -------------------
    function login(){
        
        $datas = (array)json_decode(file_get_contents('php://input')); 
        $user = $datas['username'];
        $pass = $datas['password'];
        
        $logid = null;
        $token = null;
            
          $validuser = $this->model->cek_user($user);
          $validuserphone = $this->model->cek_user_phone($user);
          
          if($validuser != TRUE && $validuserphone != TRUE){$this->reject('User Not Found..!',404);}
          else{
              
              if ($validuser == TRUE){$res = $this->model->get_by_username($user)->row(); }
              elseif ($validuserphone == TRUE){ $res = $this->model->get_by_phone($user)->row();}

              if(pass_verify($pass, $res->password) == TRUE){
                  
                 if ($this->model->login($res->email) == TRUE){

                    // $sms = new Sms_lib();
                    // $push = new Push_lib();
                    $logid = mt_rand(1000,9999);
                    $res = $this->model->get_by_username($res->email)->row(); 
                    // $sms->send($user, $this->properti['name'].' : Login OTP Code : '.$logid);
                    // $push->send_device($userid, $this->properti['name'].' : Kode OTP : '.$logid);

                    $date = new DateTime();
                    $payload['userid'] = $res->id;
                    $payload['name'] = $res->first_name;
                    $payload['username'] = $user;
                    $payload['phone'] = $res->phone1;
                    $payload['log'] = $logid;
                    // $payload['iat'] = $date->getTimestamp();
                    // $payload['exp'] = $date->getTimestamp() + 60*60*2;
                    $token = JWT::encode($payload, 'vinkoo');
                    $this->login->add($res->id, $token, $datas['device']);
                 }else{ $this->reject("Invalid Credential..!",401); }
                  
              }else{ $this->reject('Invalid Password',401); }
          }

        $this->output = array('token' => $token, 'log' => $logid); 
        $this->response('c');
    }

}

?>