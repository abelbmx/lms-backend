<?php
defined('BASEPATH') or exit('No direct script access allowed');

// =====================================================
// MODELO: Transaccion_model (Nuevo - para pagos)
// =====================================================

class Transaccion_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function create_transaction($user_id, $course_id, $amount, $currency = 'USD', $payment_method = 'paypal')
    {
        $data = [
            'usuario_id' => $user_id,
            'curso_id' => $course_id,
            'monto' => $amount,
            'moneda' => $currency,
            'metodo_pago' => $payment_method,
            'estado' => 'pendiente',
            'fecha_transaccion' => date('Y-m-d H:i:s')
        ];

        if ($this->db->insert('transacciones', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function update_transaction_status($transaction_id, $status, $gateway_response = null, $transaction_external_id = null)
    {
        $data = [
            'estado' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($gateway_response) {
            $data['gateway_response'] = is_array($gateway_response) ? json_encode($gateway_response) : $gateway_response;
        }

        if ($transaction_external_id) {
            $data['transaction_id'] = $transaction_external_id;
        }

        $this->db->where('id', $transaction_id);
        return $this->db->update('transacciones', $data);
    }

    public function get_transaction_by_id($transaction_id)
    {
        $this->db->select('t.*, u.nombre, u.apellido, u.email, c.titulo as curso_titulo');
        $this->db->from('transacciones t');
        $this->db->join('usuarios u', 't.usuario_id = u.id');
        $this->db->join('cursos c', 't.curso_id = c.id');
        $this->db->where('t.id', $transaction_id);
        
        return $this->db->get()->row_array();
    }

    public function get_user_transactions($user_id, $limit = 50, $offset = 0)
    {
        $this->db->select('t.*, c.titulo as curso_titulo, c.imagen_portada');
        $this->db->from('transacciones t');
        $this->db->join('cursos c', 't.curso_id = c.id');
        $this->db->where('t.usuario_id', $user_id);
        $this->db->order_by('t.fecha_transaccion', 'DESC');
        $this->db->limit($limit, $offset);
        
        return $this->db->get()->result_array();
    }

    public function get_course_transactions($course_id, $limit = 50, $offset = 0)
    {
        $this->db->select('t.*, u.nombre, u.apellido, u.email');
        $this->db->from('transacciones t');
        $this->db->join('usuarios u', 't.usuario_id = u.id');
        $this->db->where('t.curso_id', $course_id);
        $this->db->order_by('t.fecha_transaccion', 'DESC');
        $this->db->limit($limit, $offset);
        
        return $this->db->get()->result_array();
    }

    public function get_transactions_report($start_date = null, $end_date = null, $status = null)
    {
        $this->db->select('
            t.*,
            u.nombre, u.apellido, u.email,
            c.titulo as curso_titulo,
            DATE(t.fecha_transaccion) as fecha
        ');
        $this->db->from('transacciones t');
        $this->db->join('usuarios u', 't.usuario_id = u.id');
        $this->db->join('cursos c', 't.curso_id = c.id');

        if ($start_date) {
            $this->db->where('DATE(t.fecha_transaccion) >=', $start_date);
        }

        if ($end_date) {
            $this->db->where('DATE(t.fecha_transaccion) <=', $end_date);
        }

        if ($status) {
            $this->db->where('t.estado', $status);
        }

        $this->db->order_by('t.fecha_transaccion', 'DESC');
        
        return $this->db->get()->result_array();
    }

    public function get_revenue_stats($start_date = null, $end_date = null)
    {
        $this->db->select('
            SUM(CASE WHEN estado = "completado" THEN monto ELSE 0 END) as total_revenue,
            COUNT(CASE WHEN estado = "completado" THEN 1 END) as successful_transactions,
            COUNT(CASE WHEN estado = "fallido" THEN 1 END) as failed_transactions,
            COUNT(*) as total_transactions,
            AVG(CASE WHEN estado = "completado" THEN monto ELSE NULL END) as avg_transaction_value
        ');
        $this->db->from('transacciones');

        if ($start_date) {
            $this->db->where('DATE(fecha_transaccion) >=', $start_date);
        }

        if ($end_date) {
            $this->db->where('DATE(fecha_transaccion) <=', $end_date);
        }

        return $this->db->get()->row_array();
    }

    public function get_daily_revenue($days = 30)
    {
        $this->db->select('
            DATE(fecha_transaccion) as fecha,
            SUM(CASE WHEN estado = "completado" THEN monto ELSE 0 END) as revenue,
            COUNT(CASE WHEN estado = "completado" THEN 1 END) as transactions
        ');
        $this->db->from('transacciones');
        $this->db->where('fecha_transaccion >=', date('Y-m-d', strtotime("-{$days} days")));
        $this->db->group_by('DATE(fecha_transaccion)');
        $this->db->order_by('fecha_transaccion', 'ASC');

        return $this->db->get()->result_array();
    }

    public function refund_transaction($transaction_id, $reason = '')
    {
        $transaction = $this->get_transaction_by_id($transaction_id);
        
        if (!$transaction || $transaction['estado'] !== 'completado') {
            return ['success' => false, 'message' => 'Transacción no válida para reembolso'];
        }

        $this->db->trans_start();

        // Actualizar estado de transacción
        $this->update_transaction_status($transaction_id, 'reembolsado', ['refund_reason' => $reason]);

        // Cancelar inscripción si existe
        $this->db->where('usuario_id', $transaction['usuario_id']);
        $this->db->where('curso_id', $transaction['curso_id']);
        $this->db->update('inscripciones', ['estado' => 'cancelada']);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Error al procesar reembolso'];
        }

        return ['success' => true, 'message' => 'Reembolso procesado exitosamente'];
    }
}
