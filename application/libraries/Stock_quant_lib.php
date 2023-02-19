<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stock_quant_lib extends Custom_Model {
    
    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'stock_quant';
        $this->field = $this->db->list_fields($this->tableName);
    }
    protected $field;
    
    
    function quant_amount($locationid=0)
    {
        $this->db->select_sum('quantity');
        $this->db->select_sum('reserved_quantity');
        $this->db->where('location_id', $locationid);
        $query = $this->db->get($this->tableName)->row_array();
        $result[0] = $query['quantity'];
        $result[1] = $query['reserved_quantity'];
        return $result;
    }
    
    function get_company_id($id){
        $this->db->select($this->field);
        $this->db->where('id', $contract);
        $query = $this->db->get($this->tableName)->row();
        return $query;
    }
    
    
}

/* End of file Property.php */