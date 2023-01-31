<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/Custom_Model.php';
class Purchase_model extends Custom_Model {
        
        function __construct()
        {
            parent::__construct();
            $this->tableName = 'purchase_order';
            $this->field = $this->db->list_fields($this->tableName);
        }

        protected $field;
        
        public function get_last($contractno=null,$limit=50, $offset=0, $count=0)
        { 
           $this->db->select('po.id as po_id, po.name, po.origin, po.state,'
                           . 'pl.product_uom_qty, pl.qty_received'
                );
           
           $this->db->from('purchase_order as po,purchase_order_line as pl');
           $this->db->where('po.id = pl.order_id');
           $this->db->where('po.origin', $contractno);
//           $this->db->where('stock_location.comment <>', NULL);
           $this->db->limit($limit, $offset);
//           $this->cek_count($count,$limit,$offset);
           if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
        }
        

}