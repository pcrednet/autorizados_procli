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
 * Permite añadir Autorizados para cada proveedor o clientes.
 *
 * @author jcanda
 */

require_model('autorizados.php');
require_model('cliente.php');
require_model('proveedor.php');

class autorizados_procli extends fs_controller {

    public $allow_delete;
    public $autorizados;
    public $autorizados_select;
    public $proveedor;
    public $cliente;

    public function __construct() {
        parent::__construct(__CLASS__, 'Autorizados', 'compras', FALSE, FALSE);
    }

    protected function process() {
        //Creo los tabs en las fichas de proveedores y clientes
        $this->share_extension();

        /// ¿El usuario tiene permiso para eliminar autorizados?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);

        if (isset($_GET['tipo']) AND isset($_GET['cod'])) {
            $this->autorizados = new autorizados();
            
            //Primero cargamos el proveedor o cliente segun sea
            if ($_GET['tipo'] == 'proveedor') {
                $proveedor = new proveedor();
                $this->proveedor = $proveedor->get($_GET['cod']);
            } else {
                $cliente = new cliente();
                $this->cliente = $cliente->get($_GET['cod']);
            }

            /// añadir un autorizado para este proveedor o cliente
            if (isset($_POST['autorizado_nombre'])) {
                if ($_GET['tipo'] == 'proveedor')
                    $this->autorizados->autorizado_codproveedor = $this->proveedor->codproveedor;
                else
                    $this->autorizados->autorizado_codcliente = $this->cliente->codcliente;
                $this->autorizados->autorizado_cifnif = $_POST['autorizado_cifnif'];
                $this->autorizados->autorizado_nombre = $_POST['autorizado_nombre'];
                $this->autorizados->autorizado_telefono = $_POST['autorizado_telefono'];
                $this->autorizados->autorizado_concepto = $_POST['autorizado_concepto'];
                if ($_POST['autorizado_fecha'] == '') {
                    $this->autorizados->autorizado_fecha = date('d-m-Y');
                } else
                    $this->autorizados->autorizado_fecha = $_POST['autorizado_fecha'];

                if ($this->autorizados->save()) {
                    $this->new_message('Autorizado guardado correctamente.');
                } else
                    $this->new_error_msg('Imposible guardar Autorizado.');
            }
            /// eliminar un Autorizado
            else if (isset($_GET['delete_autorizado'])) {
                $autorizado = $this->autorizados->get($_GET['delete_autorizado']);

                if ($autorizado) {
                    if ($autorizado->delete()) {
                        $this->new_message('Autorizado eliminado correctamente.');
                    } else
                        $this->new_error_msg('Imposible eliminar Autorizado.');
                } else
                    $this->new_error_msg('Autorizado no encontrada.');
            }
            //Si no hay nada mas enseñamos todo para este COD de proveedor o cliente
            else {
                if ($_GET['tipo']=='proveedor')
                    $this->autorizados_select = $this->autorizados->get_autorizados_proveedor($_GET['cod']);
                else
                    $this->autorizados_select = $this->autorizados->get_autorizados_cliente($_GET['cod']);
            }
        }
    }

    public function url() {
        if (isset($_GET['tipo']) AND isset($_GET['cod'])) {
            return 'index.php?page=' . __CLASS__ . '&tipo=' . $_GET['tipo'] . '&cod=' . $_GET['cod'];
        } else
            return parent::url();
    }    
    
    private function share_extension() {
        $extensiones = array(
            array(
                'name' => 'autorizados_cli',
                'page_from' => __CLASS__,
                'page_to' => 'ventas_cliente',
                'type' => 'tab',
                'text' => '<span class="glyphicon glyphicon-list-alt" title="Autorizados"></span> &nbsp; Autorizados',
                'params' => '&tipo=cliente'
            ),
            array(
                'name' => 'autorizados_prov',
                'page_from' => __CLASS__,
                'page_to' => 'compras_proveedor',
                'type' => 'tab',
                'text' => '<span class="glyphicon glyphicon-list-alt" title="Autorizados"></span> &nbsp; Autorizados',
                'params' => '&tipo=proveedor'
            ),
        );
        foreach ($extensiones as $ext) {
            $fsext = new fs_extension($ext);
            if( !$fsext->save() )
            {
                $this->new_error_msg('Imposible guardar los datos de la extensión '.$ext['name'].'.');
            }
        }
    }    

}
