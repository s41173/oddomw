<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Res_partner_lib extends Custom_Model {
    
    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'res_partner';
        $this->field = $this->db->list_fields($this->tableName);
    }
    protected $field;
    
    public function get_last($search=null,$limit=50, $offset=0, $count=0)
    {
       $this->db->select($this->field);
       $this->db->from($this->tableName); 
       if ($search){ $this->db->like('name', $search, 'both');  }
//           $this->db->where('name', $search);
//           $this->db->like('name', $search, 'both'); 
       $this->cek_count($count,$limit,$offset);
       if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }

}

/* End of file Property.php */