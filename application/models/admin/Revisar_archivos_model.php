<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Revisar_archivos_model extends CI_Model
{
    // Método existente para obtener archivos sin enlazar
    public function get_unlinked_files()
    {
        $this->db->where('order_id', NULL);
        $query = $this->db->get('documents');
        return $query->result();
    }

    // Método para obtener archivos enlazados con información de la orden
    public function get_linked_files()
    {
        $this->db->select('documents.*, orders.PO');
        $this->db->join('orders', 'documents.order_id = orders.id');
        $this->db->where('documents.order_id IS NOT NULL');
        $query = $this->db->get('documents');
        return $query->result();
    }

    // Método para enlazar archivo con orden y actualizar estado
    public function link_file_to_order($file_id, $order_id)
    {
        // Iniciar una transacción
        $this->db->trans_start();

        // 1. Actualizar el archivo con el order_id y enlazado_at en la tabla 'documents'
        $document_data = array(
            'order_id' => $order_id,
            'enlazado_at' => date('Y-m-d H:i:s') // Fecha y hora actual
        );
        $this->db->where('id', $file_id);
        $this->db->update('documents', $document_data);

        // 2. Actualizar el estado de la orden a 2 en la tabla 'orders'
        $order_data = array(
            'Estado' => 2
        );
        $this->db->where('id', $order_id);
        $this->db->update('orders', $order_data);

        // Completar la transacción
        $this->db->trans_complete();

        // Verificar el estado de la transacción
        if ($this->db->trans_status() === FALSE) {
            // Si hubo un error, revertir transacción
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // Obtener documentos retrasados basado en un umbral de días
    public function get_delayed_documents($threshold_days)
    {
        $threshold_date = date('Y-m-d H:i:s', strtotime("-{$threshold_days} days"));
        $this->db->where('enlazado_at IS NULL', null, false);
        $this->db->where('uploaded_at <', $threshold_date);
        $query = $this->db->get('documents'); // Reemplaza 'documentos' con el nombre real de tu tabla
        return $query->result();
    }
}
