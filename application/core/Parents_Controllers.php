<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Parents_Controllers extends CI_Controller
{
	
	public function __construct() 
	{
            parent::__construct();


            /* autoload module items */
//            $this->load->_autoloader($this->autoload);
//            $this->modelx = new Custom_Model();
//            $this->apix = new Api_response();
//            $this->aclx = new Acl();
//            $this->log = new Log_lib();
//            $this->decodedd = $this->apix->otentikasi('otentikasi');

            $this->form_validation->set_error_delimiters('', '');
	}
        
        protected $modelx,$apix,$aclx,$log;
        public $decodedd;
        public $limitx,$offsetx,$orderby, $order;

        public $error = null;
        public $status = 200, $status404=TRUE;
        public $output = null, $resx = null, $count = 0;
      
                
        function response($type=null){
           if ($this->status != 200){
              if ($type){ $this->output_response(array('error' => $this->error, 'content' => $this->output), $this->status);  }
              else{ $this->output_response(array('error' => $this->error), $this->status);  } 
           }else{
              if ($type){ $this->output_response(array('content' => $this->output), $this->status);  }
              else{ $this->output_response(array('content' => $this->error), $this->status);  }
           } 
        }
        
        function valid_404($val=TRUE){ if ($val == FALSE){ $this->status404 = FALSE; }}
        function reject($mess='Failed to posted',$status=403){ $this->error = $mess; $this->status = $status; }
        private function reject_404(){ $this->error = 'ID not found'; $this->status = 404; }
        function reject_token($mess='Invalid Token or Expired..!',$status=401){
            if ($this->status404 == FALSE){ $this->reject_404(); }else{ $this->error = $mess; $this->status = $status; }
        }
        
      function output_response($data, $status = 200){ 
       if ($this->input->server('REQUEST_METHOD') == 'OPTIONS'){ $status = 200; $data = null;}
       
         $this->output
          ->set_status_header($status)
          ->set_content_type('application/json', 'utf-8')
          ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ))
          ->_display();
          exit;  
    }
        
        
//        function reject($mess='Failed to posted',$status=401){$this->error = $mess; $this->status = $status; }
//        function reject_token($mess='Invalid Token or Expired..!',$status=400){$this->error = $mess; $this->status = $status; }
}