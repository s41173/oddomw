<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tax_lib extends Custom_Model {
    
    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'account_tax';
        $this->field = $this->db->list_fields($this->tableName);
    }
    
    protected $field;
  
    function get_last($type='sale',$count=0)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName);
        $this->cek_null($type, 'type_tax_use');
        $this->db->where('active', true);
//        $this->db->limit(10);

        $this->db->order_by('name','asc');
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }

}

/* End of file Property.php */