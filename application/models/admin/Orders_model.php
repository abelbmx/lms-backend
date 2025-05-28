<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Orders_model extends CI_Model
{
    private $table = 'orders';

    public function __construct()
    {
        parent::__construct();
    }

    public function count_orders(string $search = ''): int
    {
        if ($search !== '') {
            $this->db->group_start()
                ->like('PO', $search)
                ->or_like('Cliente', $search)
                ->group_end();
        }
        return (int)$this->db->count_all_results('orders');
    }

    public function get_orders(int $limit, int $offset, string $search = ''): array
    {
        if ($search !== '') {
            $this->db->group_start()
                ->like('PO', $search)
                ->or_like('Cliente', $search)
                ->group_end();
        }
        return $this->db
            ->select('id, PO, Cliente, FechaOC')
            ->order_by('FechaOC', 'desc')
            ->limit($limit, $offset)
            ->get('orders')
            ->result_array();
    }

    public function get_all_orders(): array
    {
        return $this->db->order_by('FechaOC', 'desc')->get('orders')->result_array();
    }

    /**
     * Inserta múltiples registros en batch.
     * @param array $data Array de arrays asociativos con los campos de orders.
     * @return bool
     */
    public function insert_batch(array $data)
    {
        if (empty($data)) {
            return false;
        }
        return $this->db->insert_batch($this->table, $data);
    }

    /**
     * Obtiene la orden por código 'SO...' (por ejemplo 'SO452554').
     * @param string $so_code
     * @return array|null
     */
    public function get_by_code(string $so_code): ?array
    {
        $q = $this->db
            ->select('id')
            ->where('Orden', $so_code)
            ->get('orders', 1);
        return $q->row_array() ?: null;
    }

    /**
     * Crea una nueva orden con el código dado y devuelve su ID.
     * @param string $so_code
     * @return int
     */
    public function create(string $so_code): int
    {
        $this->db->insert('orders', ['Orden' => $so_code]);
        return (int)$this->db->insert_id();
    }
}
