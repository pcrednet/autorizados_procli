<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2013-2015  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Un autorizado. Puede estar relacionado con un proveedor o cliente.
 *
 * @author jcanda
 */
 
class autorizados extends fs_model
{
   public $autorizado_id;
   public $autorizado_cifnif;
   public $autorizado_nombre;
   public $autorizado_telefono;
   public $autorizado_concepto;
   public $autorizado_fecha;
   public $autorizado_codproveedor;
   public $autorizado_codcliente;
   
   public function __construct($p=FALSE)
   {
      parent::__construct('autorizados', 'plugins/autorizados_procli/');
      
      if($p)
      {
         $this->autorizado_id = $p['autorizado_id'];
         $this->autorizado_cifnif = $p['autorizado_cifnif'];
         $this->autorizado_nombre = $p['autorizado_nombre'];
         $this->autorizado_telefono = $p['autorizado_telefono'];
         $this->autorizado_concepto = $this->no_html($p['autorizado_concepto']);
         
         $this->autorizado_fecha = NULL;
            if(isset($p['autorizado_fecha']) )
                $this->autorizado_fecha = date('d-m-Y', strtotime($p['autorizado_fecha']));
            
         $this->autorizado_codproveedor = $p['autorizado_codproveedor'];
         $this->autorizado_codcliente = $p['autorizado_codcliente'];
      }
      else
      {
         $this->autorizado_id = NULL;
         $this->autorizado_cifnif = '';
         $this->autorizado_nombre = '';
         $this->autorizado_telefono = '';
         $this->autorizado_concepto = '';
         $this->autorizado_fecha = date('d-m-Y');
         $this->autorizado_codproveedor = NULL;
         $this->autorizado_codcliente  = NULL;
      }
   }

   protected function install()
   {
      return '';
   }
   
   public function exists()
   {
      if( is_null($this->autorizado_id) )
         return FALSE;
      else
         return $this->db->select("SELECT * FROM ".$this->table_name."
            WHERE autorizado_id = ".$this->var2str($this->autorizado_id).";");
   } 
   
   public function test()
   {
      $status = FALSE;
      
      $this->autorizado_codproveedor = trim($this->autorizado_codproveedor);
      $this->autorizado_codcliente = trim($this->autorizado_codcliente);
      $this->autorizado_nombre = $this->no_html($this->autorizado_nombre);
      $this->autorizado_nombre = ucwords($this->autorizado_nombre);
      $this->autorizado_cifnif = trim($this->autorizado_cifnif);
      $this->autorizado_cifnif = strtoupper($this->autorizado_cifnif);
      $this->autorizado_concepto = $this->no_html($this->autorizado_concepto);

      
      if( !preg_match("/^[A-Z0-9]{1,6}$/i", $this->autorizado_codcliente) AND is_null($this->autorizado_codproveedor))
        $this->new_error_msg("Código de cliente no válido.");
      else if( !preg_match("/^[A-Z0-9]{1,6}$/i", $this->autorizado_codproveedor) AND is_null($this->autorizado_codcliente))
        $this->new_error_msg("Código de proveedor no válido.");      
      else if( strlen($this->autorizado_nombre) < 1 OR strlen($this->autorizado_nombre) > 120 )
        $this->new_error_msg("Nombre de autorizado no válido.");
      else
        $status = TRUE;
      
      return $status;
   }
      
   public function all($offset=0, $limit=FS_ITEM_LIMIT) {
        $autorizados_array = array();
        
        $sql = "SELECT * FROM ".$this->table_name." WHERE 1 ORDER BY autorizado_id DESC";
        
        $data = $this->db->select_limit($sql, $limit, $offset);
        
        if ($data) {
            foreach ($data as $d)
                $autorizados_array[] = new autorizados($d);
        }

        return $autorizados_array;
   } 

  public function get($id)
   {
      $sql = "SELECT * FROM `".$this->table_name."` WHERE autorizado_id = " . $this->var2str($id) . ";";
        
      $data = $this->db->select($sql);
      
      if($data)
         return new autorizados($data[0]);
      else
         return FALSE;       
   } 
   
   public function get_autorizados_proveedor($id)
   {
      $autorizados_array = array();
        
      $sql = "SELECT * FROM `".$this->table_name."` WHERE autorizado_codproveedor = " . $this->var2str($id) . " ORDER BY autorizado_nombre DESC;";
      
      $data = $this->db->select($sql);
      
      if ($data) {
          foreach ($data as $d)
              $autorizados_array[] = new autorizados($d);
      }      
      
      return $autorizados_array;
   }   

   public function get_autorizados_cliente($id)
   {
      $autorizados_array = array();
        
      $sql = "SELECT * FROM `".$this->table_name."` WHERE autorizado_codcliente = " . $this->var2str($id) . " ORDER BY autorizado_nombre DESC;";
      
      $data = $this->db->select($sql);
      
      if ($data) {
          foreach ($data as $d)
              $autorizados_array[] = new autorizados($d);
      }      
      
      return $autorizados_array;
   }    
   
   public function save() {
       
        if ($this->test()) {            
            if ($this->exists()) {
                
                $sql = "UPDATE `".$this->table_name."` SET autorizado_cifnif = " . $this->var2str($this->autorizado_cifnif) . ",
               autorizado_nombre = " . $this->var2str($this->autorizado_nombre) . ", autorizado_telefono = " . $this->var2str($this->autorizado_telefono) . ",
               autorizado_concepto = " . $this->var2str($this->autorizado_concepto) . ", autorizado_fecha = " . $this->var2str($this->autorizado_fecha) . ",
               autorizado_codproveedor = " . $this->var2str($this->autorizado_codproveedor) . ", autorizado_codcliente = " . $this->var2str($this->autorizado_codcliente) . "
               WHERE autorizado_id = " . $this->var2str($this->autorizado_id) . ";";

                return $this->db->exec($sql);
                
            } else {
                $sql = "INSERT INTO `".$this->table_name."` (`autorizado_cifnif`, `autorizado_nombre`, `autorizado_telefono`, `autorizado_concepto`, `autorizado_fecha`, `autorizado_codproveedor`, `autorizado_codcliente`) 
               VALUES (" . $this->var2str($this->autorizado_cifnif) . "," . $this->var2str($this->autorizado_nombre) . ",
               " . $this->var2str($this->autorizado_telefono) . "," . $this->var2str($this->autorizado_concepto) . "," . $this->var2str($this->autorizado_fecha) . ",
               " . $this->var2str($this->autorizado_codproveedor) . "," . $this->var2str($this->autorizado_codcliente) . ");";

                if ($this->db->exec($sql)) {
                    $this->autorizado_id = $this->db->lastval();
                    return TRUE;
                } else
                    return FALSE;
            }
        } else
            return FALSE;
    } 
   
   public function delete()
   {
       return $this->db->exec("DELETE FROM ".$this->table_name." WHERE autorizado_id = ".$this->var2str($this->autorizado_id).";");
   }   
}

