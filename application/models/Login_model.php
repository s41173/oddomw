<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/Custom_Model.php';
class Login_model extends Custom_Model {
        
        function __construct()
        {
            parent::__construct();
            $this->tableName = 'res_users';
            $this->field = $this->db->list_fields($this->tableName);
            $this->load->library('Res_partner_lib');
        }

        protected $field;
        
        public function get_last($limit=50, $offset=0, $count=0)
        {
           $this->db->select($this->field);
           $this->db->from($this->tableName); 
           $this->cek_count($count,$limit,$offset);
           if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
        }
        
        function login($username,$password){
//           $query = $this->db->get_where($this->tableName, array('login' => $username,'password_ext' => $password,'active' => TRUE), 1, 0);
           $query = $this->db->get_where($this->tableName, array('login' => $username,'password_ext' => $password), 1, 0);
           if ($query->num_rows() > 0) {  return TRUE; } else { return FALSE; }
        }
        
        function get_by_username($username){
           $query = $this->db->get_where($this->tableName, array('login' => $username), 1, 0)->row();
           return $query->id;
        }
        
        function set_token($userid,$token){
           $val = array('password' => $token);
           $this->db->where('id', $userid);
           return $this->db->update($this->tableName, $val); 
        }
        
        function cek_token($token){
            $query = $this->db->get_where($this->tableName, array('password' => $token), 1, 0)->num_rows();
            if ($query > 0) {  return TRUE; } else { return FALSE; }
        }
        
        function cek_token_user($userid,$token){
            $query = $this->db->get_where($this->tableName, array('password' => $token,'id' => $userid), 1, 0)->num_rows();
            if ($query > 0) {  return TRUE; } else { return FALSE; }
        }
        
        function get_user_by_token($token){
            $query = $this->db->get_where($this->tableName, array('password' => $token), 1, 0)->row();
            return $query;
        }
        
        function get_active_user($username){
           $query = $this->db->get_where($this->tableName, array('login' => $username, 'active' => true, 'level_ext' => NULL), 1, 0)->num_rows();
           if ($query > 0){ return TRUE; }else{ return FALSE; }
        }
        
        function get_user_detail($username){
            $query = $this->db->get_where($this->tableName, array('login' => $username), 1, 0);
            if ($query->num_rows() > 0){ 
                $query = $query->row();
                $data['user'] = $query;
                $data['partner'] = $this->res_partner_lib->get_by_id($query->partner_id)->row();
                return $data;
            }
            else{ return null; }
        }
        
}