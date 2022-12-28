<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//require_once APPPATH.'core/Custom_Model.php';

class Api_response {

    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
//        $this->login = new Courier_login_lib();
        $this->clogin = new Customer_login_lib();
        $this->properti = $this->property->get();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 
    }

    private $login,$clogin,$properti;
    
    // ==================================== API ==============================
    
    function request_lock($url=null,$param=null,$apikey=null,$type=null)
    {   
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $param,
        CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'X-IGLOOHOME-APIKEY: '.$apikey
        ),
      ));

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $err = curl_error($curl);
//        $data = json_decode($response, true); 

        curl_close($curl);
        if (!$type){
            if ($err) { return $err; }else { return $response; }
        }else{
            $result = array();
            $result[0] = $response;
            $result[1] = $info['http_code'];
            return $result;
        }
    }
    
    function gmttimes(){
        
        $startTime = date("Y-m-d H:i:s");
        $cenvertedTime = date('Y-m-d H:i:s',strtotime('-7 hour',strtotime($startTime)));
 
        //  return $this->response(array('gmt7' => $startTime, 'gmt' => lockcode_format($cenvertedTime))); 
        return lockcode_format($cenvertedTime);
   }
    
  // ======== batas fungsi lock code ================ 
    function request_notif($controller=null,$param=null,$type=null,$method='POST')
    {   
        $apikey = $this->properti['notif_token'];
        $url = $this->properti['notif_url'].$controller;
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $param,
        CURLOPT_HTTPHEADER => array(
          'Content-Type: application/x-www-form-urlencoded',
          'X-Auth-Token: '.$apikey
        ),
      ));

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $err = curl_error($curl);
//        $data = json_decode($response, true); 

        curl_close($curl);
        if (!$type){
            if ($err) { return $err; }else { return $response; }
        }else{
            $result = array();
            $result[0] = $response;
            $result[1] = $info['http_code'];
            return $result;
        } 
    }
    
       
    function request_get($url=null,$type='null',$method='GET')
    {   
        $curl = curl_init();
        $param = null;
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $param,
        CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
        ),
      ));

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $err = curl_error($curl);
//        $data = json_decode($response, true); 

        curl_close($curl);
        if (!$type){
            if ($err) { return $err; }else { return $response; }
        }else{
            $data = json_decode($response, true);
            $result = array();
            $result[0] = $response;
            $result[1] = $info['http_code'];
            if ($result[1] != '200'){ $result[2] = $data['error']; }else{ $result[2] = null; }
            return $result;
        } 
    }
   
    function request($url=null,$param=null,$type=null,$method='POST')
    {   
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $param,
        CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
        ),
      ));

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $err = curl_error($curl);
//        $data = json_decode($response, true); 

        curl_close($curl);
        if (!$type){
            if ($err) { return $err; }else { return $response; }
        }else{
            $result = array();
            $result[0] = $response;
            $result[1] = $info['http_code'];
            return $result;
        } 
    }
        
    function response($data, $status = 200){ 
       if ($this->input->server('REQUEST_METHOD') == 'OPTIONS'){ $status = 200; $data = null;}
       
         $this->output
          ->set_status_header($status)
          ->set_content_type('application/json', 'utf-8')
          ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ))
          ->_display();
          exit;  
    }
    
    function motentikasi($type=null){
        if ($this->input->server('REQUEST_METHOD') != 'OPTIONS'){
            $jwt = $this->input->get_request_header('X-auth-token');
            // harus mencocokan decoded mobile no dengan log di database
            try{
              $decoded  = JWT::decode($jwt, 'vinkoo', array('HS256'));
              if (!$type){
                return $this->login->valid($decoded->userid, $jwt);   
              }else{ return $decoded; }
            }
            catch (\Exception $e){ 
    //            $response = array('error' => 'Error token..!');
    //            return $this->response($response,401);
                return FALSE;
            }
        }else{ return TRUE; }
    }
    
    function otentikasi($type=null){
        if ($this->input->server('REQUEST_METHOD') != 'OPTIONS'){
            $jwt = $this->input->get_request_header('X-auth-token');
            // harus mencocokan decoded mobile no dengan log di database
            try{
              $decoded  = JWT::decode($jwt, 'vinkoo', array('HS256'));
              if (!$type){
                return $this->clogin->valid($decoded->userid, $jwt);   
              }else{ return $decoded; }
            }
            catch (\Exception $e){ 
    //            $response = array('error' => 'Error token..!');
    //            return $this->response($response,401);
                return FALSE;
            }
        }else{ return TRUE; }
    }
    
    function get_decoded(){
        $jwt = $this->input->get_request_header('X-auth-token');
        if ($jwt != null){
         $decoded  = JWT::decode($jwt, 'vinkoo', array('HS256'));
         return $decoded;
        }else{ return null; }
    }

}

/* End of file Property.php */