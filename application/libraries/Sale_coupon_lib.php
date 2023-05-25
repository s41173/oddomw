<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sale_coupon_lib extends Custom_Model {
    
    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'sale_coupon_program';
        $this->field = $this->db->list_fields($this->tableName);
    }
    
    private $wt;
    protected $field;
  
    function get_last($limit=10,$offset=0,$count=0)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName);
        $this->db->limit($limit,$offset);
        $this->db->order_by('name','asc');
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }
    
}

/* End of file Property.php */