<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/Custom_Model.php';
class Main_model extends Custom_Model {
        
        function __construct()
        {
            parent::__construct();
            $this->tableName = 'stock_picking';
            $this->field = $this->db->list_fields($this->tableName);
        }

        protected $field;
        
        public function get_last($search=null,$limit=50, $offset=0, $count=0)
        {
           $this->db->select($this->field);
           $this->db->from($this->tableName); 
           if ($search){ $this->db->like('origin', $search, 'both');  }
//           $this->db->where('name', $search);
//           $this->db->like('name', $search, 'both'); 
           $this->cek_count($count,$limit,$offset);
           if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
        }
        
        // 0 = DO No
        // 1 = Contract No
        function get_detail_list($type=0,$contractno=null,$limit=10,$offset=0,$count=0)
        {
            $this->db->select('stock_picking.id as picking_id, stock_picking.origin, stock_picking.no_po, stock_picking.no_contract,'
                    . '        stock_picking.name as picking_name, stock_picking.jenis_barang,'
                    . 'stock_picking.no_dokumen_do, stock_picking.no_do, stock_picking.no_aju, stock_picking.jenis_dokumen,'
                    . 'stock_picking.no_dokumen, stock_picking.no_segel_bc, stock_picking.state,'
                    . 'stock_move.product_id, stock_move.product_uom, stock_move.location_id, stock_move.location_dest_id,'
                    . 'stock_move.id as stock_move_id, stock_move.product_uom_qty,'
                    . 'stock_picking_type.sequence_code as picking_type, stock_location.name as stock_location_name,'
                    . 'product_template.name as product_name, res_partner.name as partner_name, res_partner.contact_address_complete as alamat'
                    );
          
//             'move.product_id','move.product_uom as product_uom_id','move.location_id','move.location_dest_id',
//                'move.id as stock_move_id', 'move.product_uom_qty',
//                'pp.default_code',
//                'pt.name as product_name',
//                'pn.name as partner_name','pn.contact_address_complete as alamat'
            
            $this->db->from('stock_picking,stock_move,stock_location,stock_picking_type,product_product,product_template,res_partner');
            $this->db->where('stock_picking.id = stock_move.picking_id');
            $this->db->where('stock_picking.picking_type_id = stock_picking_type.id');
            $this->db->where('stock_move.location_id = stock_location.id');
            $this->db->where('product_product.id = stock_move.product_id');
            $this->db->where('product_template.id = product_product.product_tmpl_id');
            $this->db->where('res_partner.id = stock_picking.partner_id');
            if ($type == 0){ 
//                $this->db->where('stock_picking.name', $contractno);
                $this->db->like('stock_picking.no_do', $contractno, 'both'); 
            }
            elseif ($type == 1){ 
//                $this->db->where('stock_picking.origin', $contractno);
                $this->db->like('stock_picking.origin', $contractno, 'both'); 
            }
            
            $this->db->where("stock_picking.state <>","done");
            $this->db->where("stock_picking.state <>","cancel");
            $this->db->where("stock_picking.state <>","draft");
            $this->db->order_by('stock_picking.id','desc');
            $this->db->limit($limit, $offset);
//            $this->cek_count($count,$limit,$offset);
            if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
        }
        

}