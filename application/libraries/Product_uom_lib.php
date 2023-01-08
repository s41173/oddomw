<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_uom_lib extends Custom_Model {
    
    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'uom_uom';
        $this->field = $this->db->list_fields($this->tableName);
    }
    protected $field;

}

/* End of file Property.php */