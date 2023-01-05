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

}

/* End of file Property.php */