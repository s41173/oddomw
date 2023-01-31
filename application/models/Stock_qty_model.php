<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/Custom_Model.php';
class Stock_qty_model extends Custom_Model {
        
        function __construct()
        {
            parent::__construct();
            $this->tableName = 'stock_quant';
            $this->field = $this->db->list_fields($this->tableName);
        }

        protected $field;
        
        public function get_last($filter=0,$limit=50, $offset=0, $count=0)
        { 
           $this->db->select('stock_quant.id as stock_quant_id, stock_quant.product_id, stock_quant.company_id, stock_quant.location_id,'
                   . '        stock_quant.quantity, stock_quant.reserved_quantity, stock_location.name as stock_location_name, stock_location.complete_name, stock_location.comment,'
                   . '        stock_location.usage, stock_location.active'
                );
           
           $this->db->from('stock_quant,stock_location');
           $this->db->where('stock_quant.location_id = stock_location.id');
           if ($filter == 0){ 
//               $this->db->where('stock_location.comment <>', NULL); 
//               AND ("comment" IN ('CPO','Olein','PFAD','RPO','RPS'))
               $names = array('CPO', 'Olein', 'PFAD', 'RPO', 'RPS');
               $this->db->where_in('stock_location.comment', $names);
               $usages = array('internal');
               $this->db->where_in('stock_location.usage', $usages);
               $this->db->where('stock_quant.quantity >', 0);
           }
           $this->db->where('stock_location.active', true);
          
           $this->db->limit($limit, $offset);
//           $this->cek_count($count,$limit,$offset);
           if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
        }
        

}