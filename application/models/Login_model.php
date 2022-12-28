<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/Custom_Model.php';
class Login_model extends Custom_Model {
        
        function __construct()
        {
            parent::__construct();
            $this->tableName = 'res_users';
            $this->field = $this->db->list_fields($this->tableName);
        }

        protected $field;
        
//        function login($user=null,$pass=null)
//        {
//           $this->db->select($this->field);
//           $this->db->from($this->tableName); 
//           $this->db->limit(1);
//           $query = $this->db->get()->num_rows();
//           return $query;
//        }
        
        function login($username)
        {
            $query = $this->db->get_where($this->tableName, array('login' => $username), 1, 0);
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
        
        

}