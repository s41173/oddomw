<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Vendor_model extends Custom_Model
{
    protected $logs;
    
    function __construct()
    {
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('vendor');
        $this->tableName = 'vendor';
    }
    
    protected $field = array('id', 'prefix', 'name', 'type', 'cp1', 'npwp', 'address', 'shipping_address', 'phone1', 'phone2',
                             'fax', 'hp', 'email', 'website', 'city', 'zip', 'notes', 'status', 'acc_name', 'acc_no', 'bank', 
                             'created', 'updated', 'deleted');
    protected $com;
    
    function get_last($limit, $offset=null, $count=0)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->order_by('id', 'desc'); 
        $this->cek_count($count,$limit,$offset);
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }
    
    function search($cat=null,$publish=null,$count=0)
    {   
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->cek_null($cat, 'city');
        $this->cek_nol($publish, 'status');
        $this->db->order_by('id', 'asc'); 
        $this->cek_count($count,$limit,$offset);
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }

}

?>