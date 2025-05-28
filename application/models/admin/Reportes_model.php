<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reportes_model extends CI_Model
{
    // Método para obtener todas las órdenes con filtros de fechas
    public function get_orders_with_filters($fecha_oc_inicio = null, $fecha_oc_fin = null, $fecha_f_inicio = null, $fecha_f_fin = null)
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

    // Método para obtener métricas de documentos enlazados
    public function get_document_metrics($fecha_enlazado_inicio = null, $fecha_enlazado_fin = null)
    {
        if ($fecha_enlazado_inicio && $fecha_enlazado_fin) {
            $this->db->where('enlazado_at >=', $fecha_enlazado_inicio);
            $this->db->where('enlazado_at <=', $fecha_enlazado_fin);
        }

        $this->db->select('documents.*, orders.PO');
        $this->db->join('orders', 'documents.order_id = orders.id');
        $this->db->where('documents.order_id IS NOT NULL');
        $query = $this->db->get('documents');
        return $query->result();
    }

    public function get_pedidos_por_dia($fecha_oc_inicio = null, $fecha_oc_fin = null)
    {
        $this->db->select('DATE(FechaOC) as fecha, COUNT(*) as total');
        $this->db->from('orders');

        if ($fecha_oc_inicio && $fecha_oc_fin) {
            $this->db->where('FechaOC >=', $fecha_oc_inicio);
            $this->db->where('FechaOC <=', $fecha_oc_fin);
        }

        $this->db->group_by('fecha');
        $this->db->order_by('fecha', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

    // Otros métodos para métricas y gráficos...

    // Métodos para DataTables con filtros
    public function get_datatables_orders($filters)
    {
        $this->_get_datatables_orders_query($filters);
        if ($this->input->post('length') != -1)
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        return $query->result();
    }

    public function count_filtered_orders($filters)
    {
        $this->_get_datatables_orders_query($filters);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all_orders($filters)
    {
        $this->db->from('orders');
        if (!empty($filters['estados'])) {
            $this->db->where_in('Estado', $filters['estados']);
        }
        return $this->db->count_all_results();
    }

    private function _get_datatables_orders_query($filters)
    {
        $this->db->select('orders.*, documents.file_name, documents.uploaded_at, documents.enlazado_at');
        $this->db->from('orders');
        $this->db->join('documents', 'orders.id = documents.order_id', 'left');

        if ($filters['fecha_oc_inicio'] && $filters['fecha_oc_fin']) {
            $this->db->where('orders.FechaOC >=', $filters['fecha_oc_inicio']);
            $this->db->where('orders.FechaOC <=', $filters['fecha_oc_fin']);
        }

        if ($filters['fecha_f_inicio'] && $filters['fecha_f_fin']) {
            $this->db->where('orders.FechaF >=', $filters['fecha_f_inicio']);
            $this->db->where('orders.FechaF <=', $filters['fecha_f_fin']);
        }

        if (!empty($filters['estados'])) {
            $this->db->where_in('orders.Estado', $filters['estados']);
        }

        $column_search = array('orders.id', 'orders.PO', 'orders.Cliente', 'orders.Guia', 'orders.Factura');
        $column_order = array(null, 'orders.id', 'orders.PO', 'orders.Orden', 'orders.Tp', 'orders.FechaOC', 'orders.Cliente', 'orders.Nombre', 'orders.Guia', 'orders.Factura', 'orders.FechaF', 'orders.Ship', 'orders.Nombre2', 'orders.Direccion', 'orders.Comuna', 'orders.Region', 'orders.NombreChofer', 'orders.RUT', 'orders.Patente', 'orders.EntregadoConforme', 'orders.Recepcionista', 'orders.FechaEntrega', 'orders.Estado');

        $i = 0;
        foreach ($column_search as $item) {
            if ($this->input->post('search')['value']) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $this->input->post('search')['value']);
                } else {
                    $this->db->or_like($item, $this->input->post('search')['value']);
                }

                if (count($column_search) - 1 == $i)
                    $this->db->group_end();
            }
            $i++;
        }

        if ($this->input->post('order')) {
            $this->db->order_by($column_order[$this->input->post('order')['0']['column']], $this->input->post('order')['0']['dir']);
        } else {
            $this->db->order_by('orders.FechaOC', 'DESC');
        }
    }
    public function get_pedidos_por_tipo($fecha_oc_inicio, $fecha_oc_fin)
    {
        $this->db->select('Tp, COUNT(*) as total');
        $this->db->from('orders');

        if (!empty($fecha_oc_inicio)) {
            $this->db->where('FechaOC >=', $fecha_oc_inicio);
        }

        if (!empty($fecha_oc_fin)) {
            $this->db->where('FechaOC <=', $fecha_oc_fin);
        }

        $this->db->group_by('Tp');
        $this->db->order_by('total', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    
}
