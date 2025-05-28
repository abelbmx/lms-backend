<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Subir_documentos_model extends CI_Model
{
    public function save_document(array $d)
    {
        return $this->db->insert('documents', $d);
    }

    public function order_exists(int $id): bool
    {
        return $this->db
            ->where('id', $id)
            ->count_all_results('orders') > 0;
    }

    public function count_documents(string $search = ''): int
    {
        if ($search === '') {
            return (int)$this->db->count_all('documents');
        }
        $this->db->from('documents');
        $this->db->group_start();
        $this->db->where("MATCH(file_name) AGAINST(" . $this->db->escape($search) . " IN NATURAL LANGUAGE MODE)", null, false);
        $this->db->or_like('document_type', $search);
        $this->db->or_like('uploaded_at', $search);
        $this->db->group_end();
        return (int)$this->db->count_all_results();
    }

    // application/models/Subir_documentos_model.php

    public function get_documents(
        int $limit,
        int $offset,
        string $search = '',
        string $orderColumn = 'd.uploaded_at',
        string $orderDir    = 'desc'
    ): array {
        $this->db
            ->select('
            d.id,
            o.PO           AS po,
            o.Orden        AS orden,
            o.Cliente      AS cliente,
            d.document_type,
            d.file_name,
            d.file_path,
            d.uploaded_at
        ')
            ->from('documents d')
            ->join('orders o', 'o.id=d.order_id', 'left');

        if ($search !== '') {
            $this->db->group_start()
                ->where("MATCH(d.file_name) AGAINST(" . $this->db->escape($search) . " IN NATURAL LANGUAGE MODE)", null, false)
                ->or_like('d.document_type', $search)
                ->or_like('d.uploaded_at', $search)
                ->group_end();
        }

        $this->db->order_by($orderColumn, $orderDir);

        return $this->db
            ->limit($limit, $offset)
            ->get()
            ->result_array();
    }


    public function get_unlinked_documents(): array
    {
        return $this->db
            ->where('order_id', null)
            ->order_by('uploaded_at', 'desc')
            ->get('documents')
            ->result_array();
    }

    public function get_notyped_documents(): array
    {
        return $this->db
            ->group_start()
            ->where('document_type', null)
            ->or_where('document_type', '')
            ->group_end()
            ->order_by('uploaded_at', 'desc')
            ->get('documents')
            ->result_array();
    }

    public function update_document(int $id, array $data)
    {
        return $this->db
            ->where('id', $id)
            ->update('documents', $data);
    }

    public function get_document(int $id): array
    {
        return $this->db
            ->where('id', $id)
            ->get('documents')
            ->row_array();
    }

    /**
     * Obtiene un documento por su nombre de archivo.
     * @param string $file_name
     * @return array|null
     */
    public function get_by_file_name(string $file_name): ?array
    {
        $q = $this->db
            ->get_where('documents', ['file_name' => $file_name], 1);
        return $q->row_array() ?: null;
    }
}
