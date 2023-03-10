<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stock_picking_lib extends Custom_Model {
    
    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'stock_picking';
        $this->field = $this->db->list_fields($this->tableName);
    }
    protected $field;
    
    function get_name($uid=0){
        $res = $this->get_by_id($uid)->row();
        return $res->name;
    }

}

/* End of file Property.php */