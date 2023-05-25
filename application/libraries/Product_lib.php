<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_lib extends Custom_Model {
    
    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'product_product';
        $this->field = $this->db->list_fields($this->tableName);
    }
    
    private $wt;
    protected $field;

    
    function test(){
        return 'Testting';
    }
  
    function get_detail($pid=0,$count=0)
    {
        $this->db->select('product_product.default_code as code, product_product.product_tmpl_id,'
                . '        product_template.name as name, product_template.description, product_template.type, product_template.location_type,'
                . '        uom_uom.name as uom_name'
                );

        $this->db->from('product_product,product_template,uom_uom');
        $this->db->where('product_product.product_tmpl_id = product_template.id');
        $this->db->where('product_template.uom_id = uom_uom.id');
        $this->cek_null($pid, 'product_product.id');
//        $this->db->limit(10);

        $this->db->order_by('product_product.id','desc');
        if ($count==0){ return $this->db->get()->row(); }else{ return $this->db->get()->num_rows(); }
    }
    
    function get_retail($filter=null,$count=0)
    {
//        SELECT DISTINCT pp.default_code, pt.name
//        FROM product_product pp
//        JOIN product_template pt ON pt.id = pp.product_tmpl_id
//        JOIN sale_order_line sol ON sol.product_id = pp.id
//        JOIN sale_order so ON so.id = sol.order_id
//        WHERE pt.type = 'product'
//        AND so.type_id = '2'
        
        
        $this->db->select('product_product.id as product_id,product_product.default_code as code, product_product.product_tmpl_id,'
                . '        product_template.name as name, product_template.description, product_template.type, product_template.location_type,'
                . '        uom_uom.name as uom_name, product_template.list_price'
                );

        $this->db->from('product_product,product_template,uom_uom,sale_order,sale_order_line');
        $this->db->where('product_product.product_tmpl_id = product_template.id');
        $this->db->where('sale_order_line.product_id = product_product.id');
        $this->db->where('sale_order.id = sale_order_line.order_id');
        $this->db->where('product_template.uom_id = uom_uom.id');
        $this->db->where('product_template.type', 'product');
        $this->db->where('sale_order.type_id', '2');
        $this->cek_null($filter, 'product_product.id');
        $this->db->distinct();
        
//        $this->db->limit(10);

//        $this->db->order_by('product_product.id','desc');
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }

}

/* End of file Property.php */