<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ingresar_datos_model extends CI_Model
{
    private $table = 'orders';
    private $column_order = ['Estado', 'PO', 'Orden', 'Tp', 'FechaOC', 'Cliente', 'Nombre', 'Guia', 'Factura', 'FechaF', 'Ship', 'Nombre2', 'Direccion', 'Comuna', 'Region'];
    private $column_search = ['PO', 'Orden', 'Cliente', 'Nombre'];

    public function count_all_pedidos()
    {
        return $this->db->count_all($this->table);
    }

    public function count_filtered_pedidos($search)
    {
        if ($search) {
            $this->db->group_start();
            foreach ($this->column_search as $col) {
                $this->db->or_like($col, $search);
            }
            $this->db->group_end();
        }
        return $this->db->count_all_results($this->table);
    }

    public function get_pedidos_paginated($limit, $start, $search, $order_col, $order_dir)
    {
        if ($search) {
            $this->db->group_start();
            foreach ($this->column_search as $col) {
                $this->db->or_like($col, $search);
            }
            $this->db->group_end();
        }
        if (isset($this->column_order[$order_col])) {
            $this->db->order_by($this->column_order[$order_col], $order_dir);
        }
        return $this->db
            ->limit($limit, $start)
            ->get($this->table)
            ->result_array();
    }

    public function insert_order($data)
    {
        $this->db->insert('orders', $data);
    }
    // Obtener los pedidos recientes
    public function get_recent_pedidos($limit = 10)
    {
        $this->db->order_by('FechaOC', 'DESC');
        $query = $this->db->get('orders', $limit); // Cambia 'orders' por el nombre real de tu tabla
        return $query->result_array();
    }

    // Obtener todos los pedidos
    public function get_all_pedidos()
    {
        $query = $this->db->get('orders');
        return $query->result_array();
    }

    // Obtener la cantidad de pedidos agrupados por día
    public function get_pedidos_por_dia()
    {
        $this->db->select('DATE(FechaOC) as fecha, COUNT(*) as total');
        $this->db->group_by('fecha');
        $this->db->order_by('fecha', 'ASC');
        $query = $this->db->get('orders');
        return $query->result_array();
    }

    // Método para verificar si un pedido es válido
    public function is_valid_order($order_id)
    {
        $this->db->where('id', $order_id);
        $query = $this->db->get('orders');

        if ($query->num_rows() === 1) {
            return TRUE;
        }

        return FALSE;
    }

    // Método para obtener todas las órdenes con filtros de fechas
    public function get_all_orders($fecha_oc_inicio = null, $fecha_oc_fin = null, $fecha_f_inicio = null, $fecha_f_fin = null)
    {
        if ($fecha_oc_inicio && $fecha_oc_fin) {
            $this->db->where('FechaOC >=', $fecha_oc_inicio);
            $this->db->where('FechaOC <=', $fecha_oc_fin);
        }

        if ($fecha_f_inicio && $fecha_f_fin) {
            $this->db->where('FechaF >=', $fecha_f_inicio);
            $this->db->where('FechaF <=', $fecha_f_fin);
        }

        $this->db->order_by('FechaOC', 'DESC');
        $query = $this->db->get('orders');
        return $query->result();
    }

    // Método para obtener métricas de órdenes
    public function get_order_metrics($fecha_oc_inicio = null, $fecha_oc_fin = null)
    {
        if ($fecha_oc_inicio && $fecha_oc_fin) {
            $this->db->where('FechaOC >=', $fecha_oc_inicio);
            $this->db->where('FechaOC <=', $fecha_oc_fin);
        }

        $this->db->select('Estado, COUNT(*) as total');
        $this->db->group_by('Estado');
        $query = $this->db->get('orders');
        return $query->result();
    }

    public function get_pedidos_por_tipo()
    {
        $this->db->select('tp, COUNT(*) as total');
        $this->db->from('orders'); // Reemplaza 'pedidos' con el nombre correcto de tu tabla
        $this->db->group_by('tp');
        $query = $this->db->get();
        return $query->result();
    }
}
