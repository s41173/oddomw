<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Vendor extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Vendor_model', 'model', TRUE);

        $this->properti = $this->property->get();
//        $this->acl->otentikasi();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
        $this->role = new Role_lib();
        $this->city = new City_lib();
        $this->disctrict = new District_lib();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    private $properti, $modul, $title, $city, $disctrict, $role,$api,$acl;
    
    function index()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
        
            $datax = (array)json_decode(file_get_contents('php://input')); 
            if (isset($datax['limit'])){ $this->limitx = $datax['limit']; }else{ $this->limitx = $this->modul['limit']; }
            if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; }
            
            $city = null; $publlish = null; $limit=null; $offset=null;
            if (isset($datax['city'])){ $city = $datax['city']; }
            if (isset($datax['publish'])){ $publlish = $datax['publish']; }
            
            if($city == null & $publlish == null){ $result = $this->model->get_last($this->limitx, $this->offsetx)->result();
              $this->count = $this->model->get_last($this->limitx, $this->offsetx,1); }
            else { $result = $this->model->search($city,$publlish)->result(); $this->count = $this->model->search($city,$publlish,1); }    

            foreach($result as $res)
            {   
               $this->resx[] = array ("id" => $res->id, "name" => $res->prefix.' '.$res->name, "type" => $res->type, "address" => $res->address, "ship_address" => $res->shipping_address, 
                                        "phone1" => $res->phone1, "phone2" => $res->phone2, "fax" => $res->fax, "email" => $res->email, "website" => $res->website, "city" => $res->city,
                                        "zip" => $res->zip, "notes" => $res->notes, "status" => $res->status
                                       );
            }  
            $data['record'] = $this->count; 
            $data['result'] = $this->resx; 
            $this->output = $data;
        }else{ $this->reject_token(); }
        $this->response('c');
    }
    
    function publish($uid = null)
    {
       if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 
          $val = $this->model->get_by_id($uid)->row();
          if ($val->status == 0){ $lng = array('status' => 1); }else { $lng = array('status' => 0); }
           if ($this->model->update($uid,$lng) == true){ $this->error = 'Status Changed...!'; }else{ $this->reject(); }
       }else{ $this->valid_404($this->model->valid_add_trans($uid, $this->title)); $this->reject_token(); }
       $this->response();
    }
    
    function delete_all($type='soft')
    {
      if ($this->acl->otentikasi_admin($this->title) == TRUE){
      
        $cek = $this->input->post('cek');
        $jumlah = count($cek);

        if($cek)
        {
          $jumlah = count($cek);
          $x = 0;
          for ($i=0; $i<$jumlah; $i++)
          {
             if ($type == 'soft') { $this->model->delete($cek[$i]); }
             else { $this->remove_img($cek[$i],'force');
                    $this->attribute_customer->force_delete_by_customer($cek[$i]);
                    $this->model->force_delete($cek[$i]);  }
             $x=$x+1;
          }
          $res = intval($jumlah-$x);
          $mess = "$res $this->title successfully removed &nbsp; - &nbsp; $x related to another component..!!";
          $this->error = $mess;
        }
        else
        { $mess = "No $this->title Selected..!!";  $this->error = $mess; $this->status = 401; }
      }else{ $this->reject_token(); }
      $this->response();
    }

    function delete($uid)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){
            if ($this->model->delete($uid) == true){ $this->error = "1 $this->title successfully removed..!";    
            }else{ $this->error = 'Failure Saved..'; $this->status = 401; }
        }else{ $this->valid_404($this->model->valid_add_trans($uid, $this->title)); $this->reject_token(); }
        $this->response();
    }

    function add()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){
   
            // Form validation
            $this->form_validation->set_rules('tname', 'SKU', 'required|callback_valid_vendor');
            $this->form_validation->set_rules('tcp', 'Name', 'required');
            $this->form_validation->set_rules('ctype', 'Vendor Type', 'required');
            $this->form_validation->set_rules('tphone', 'Phone 1', 'required');
            $this->form_validation->set_rules('tmobile', 'Mobile', 'required');
            $this->form_validation->set_rules('temail', 'Email', 'required|valid_email|callback_valid_email');
            $this->form_validation->set_rules('tnpwp', 'Npwp', '');
            $this->form_validation->set_rules('tfax', 'Fax', '');
            $this->form_validation->set_rules('taddress', 'Address', 'required');
            $this->form_validation->set_rules('ccity', 'City', 'required');
            $this->form_validation->set_rules('tzip', 'Zip', '');
            $this->form_validation->set_rules('twebsite', 'Website', '');
            $this->form_validation->set_rules('taccname', 'Account Name', '');
            $this->form_validation->set_rules('taccno', 'Account No', '');
            $this->form_validation->set_rules('tbank', 'Bank', '');   

            if ($this->form_validation->run($this) == TRUE)
            {
                $customer = array('name' => strtoupper($this->input->post('tname')), 'type' => $this->input->post('ctype'),
                      'cp1' => strtoupper($this->input->post('tcp')), 'npwp' => $this->input->post('tnpwp'),
                      'address' => $this->input->post('taddress'), 'shipping_address' => $this->input->post('taddress'), 'phone1' => $this->input->post('tphone'), 
                      'fax' => $this->input->post('tfax'), 'hp' => $this->input->post('tmobile'), 'email' => $this->input->post('temail'),
                      'website' => $this->input->post('twebsite'), 'city' => $this->input->post('ccity'), 'zip' => $this->input->post('tzip'),
                      'acc_name' => $this->input->post('taccname'), 'acc_no' => $this->input->post('taccno'),
                      'bank' => $this->input->post('tbank'), 'created' => date('Y-m-d H:i:s'));
   
                if ($this->model->add($customer) != true){ $this->reject($this->upload->display_errors());
                }else{ $this->model->log('create'); $this->output = $this->model->get_latest(); }                 
            }
            else{  $this->reject(validation_errors(),400); }
        }else { $this->reject_token(); }
        $this->response('c');
    }

    // Fungsi update untuk menset texfield dengan nilai dari database
    function get($uid=null)
    {        
       if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){  
           $this->output = $this->model->get_by_id($uid)->row();
       }else { $this->valid_404($this->model->valid_add_trans($uid, $this->title)); $this->reject_token(); }
       $this->response('c');
    }
   
    public function valid_vendor($name)
    {
        if ($this->model->valid('name',$name) == FALSE)
        {
            $this->form_validation->set_message('valid_vendor', "This $this->title is already registered.!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    function valid_email($val)
    {
        if ($this->model->valid('email',$val) == FALSE)
        {
            $this->form_validation->set_message('valid_email','Email registered..!');
            return FALSE;
        }
        else{ return TRUE; }
    }

    function validating_email($val,$id)
    {
	if ($this->model->validating('email',$val,$id) == FALSE)
        {
            $this->form_validation->set_message('validating_email', "Email registered!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    // Fungsi update untuk mengupdate db
    function update($param=0)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($param, $this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tname', 'SKU', 'required');
        $this->form_validation->set_rules('tcp', 'Name', 'required');
        $this->form_validation->set_rules('ctype', 'Vendor Type', 'required');
        $this->form_validation->set_rules('tphone', 'Phone 1', 'required');
        $this->form_validation->set_rules('tmobile', 'Mobile', 'required');
        $this->form_validation->set_rules('temail', 'Email', 'required|callback_validating_email['.$param.']');
        $this->form_validation->set_rules('tnpwp', 'Npwp', '');
        $this->form_validation->set_rules('tfax', 'Fax', '');
        $this->form_validation->set_rules('taddress', 'Address', 'required');
        $this->form_validation->set_rules('ccity', 'City', 'required');
        $this->form_validation->set_rules('tzip', 'Zip', '');
        $this->form_validation->set_rules('twebsite', 'Website', '');
        $this->form_validation->set_rules('taccname', 'Account Name', '');
        $this->form_validation->set_rules('taccno', 'Account No', '');
        $this->form_validation->set_rules('tbank', 'Bank', '');  
            
        if ($this->form_validation->run($this) == TRUE)
        {
            $customer = array('type' => $this->input->post('ctype'),
                  'cp1' => strtoupper($this->input->post('tcp')), 'npwp' => $this->input->post('tnpwp'),
                  'address' => $this->input->post('taddress'), 'shipping_address' => $this->input->post('taddress'), 'phone1' => $this->input->post('tphone'), 
                  'fax' => $this->input->post('tfax'), 'hp' => $this->input->post('tmobile'), 'email' => $this->input->post('temail'),
                  'website' => $this->input->post('twebsite'), 'city' => $this->input->post('ccity'), 'zip' => $this->input->post('tzip'),
                  'acc_name' => $this->input->post('taccname'), 'acc_no' => $this->input->post('taccno'),
                  'bank' => $this->input->post('tbank'));

            if ($this->model->update($param, $customer) == true){ $this->error = $this->title.' successfully saved..!'; }else{ $this->reject(); }
        }
        else{ $this->reject(validation_errors(),400); }
        }else { $this->valid_404($this->model->valid_add_trans($param, $this->title)); $this->reject_token(); }
        $this->response();
    } 

}

?>