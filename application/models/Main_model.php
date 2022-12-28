<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/Custom_Model.php';
class Main_model extends Custom_Model {
        
        function __construct()
        {
            parent::__construct();
            $this->tableName = 'res_users';
            $this->field = $this->db->list_fields($this->tableName);
        }

        protected $field;
        
        public function get_last($limit=50, $offset=0, $count=0)
        {
           $this->db->select($this->field);
           $this->db->from($this->tableName); 
           $this->cek_count($count,$limit,$offset);
           if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
        }

}