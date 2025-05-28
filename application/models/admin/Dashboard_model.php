<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }


    public function get_count_record($table)
    {
        $query = $this->db->count_all($table);

        return $query;
    }


    public function getReferralOrders()
    {

        $this->db->select('count(id) as num, estado as referrer');
        $this->db->group_by('estado');
        $queryResult = $this->db->get('documento_pago');
        return $queryResult->result_array();
    }



    public function getOrdersByMonth10()
    {
        $result = $this->db->query("SELECT YEAR(fecha_emision) as year, MONTH(fecha_emision) as month, SUM(consumo) as num ,n_tarifa FROM documento_pago, cliente, medidor where n_tarifa = 10 and medidor.id_cliente = cliente.id  AND  cliente.id = documento_pago.id_cliente and cliente.estado = 'Activo' and fecha_emision is not null GROUP BY YEAR(fecha_emision), MONTH(fecha_emision),n_tarifa ");
        $result = $result->result_array();
        $orders = array();
        $years = array();
        foreach ($result as $res) {
            if (!isset($orders[$res['year']])) {
                for ($i = 1; $i <= 12; $i++) {
                    $orders[$res['year']][$i] = 0;
                }
            }
            $datos = $res['year'];

            $years[] = $datos;
            $orders[$datos][$res['month']] = $res['num'];
        }
        return array(
            'years' => array_unique($years),
            'orders' => $orders
        );
    }


    public function getOrdersByMonth80()
    {
        $result = $this->db->query("SELECT YEAR(fecha_emision) as year, MONTH(fecha_emision) as month, SUM(consumo) as num ,n_tarifa FROM documento_pago, cliente, medidor where n_tarifa = 80 and medidor.id_cliente = cliente.id  AND  cliente.id = documento_pago.id_cliente and cliente.estado = 'Activo' and fecha_emision is not null GROUP BY YEAR(fecha_emision), MONTH(fecha_emision),n_tarifa ");
        $result = $result->result_array();
        $orders = array();
        $years = array();
        foreach ($result as $res) {
            if (!isset($orders[$res['year']])) {
                for ($i = 1; $i <= 12; $i++) {
                    $orders[$res['year']][$i] = 0;
                }
            }
            $datos = $res['year'];

            $years[] = $datos;
            $orders[$datos][$res['month']] = $res['num'];
        }
        return array(
            'years' => array_unique($years),
            'orders' => $orders
        );
    }




    public function disk_totalspace($dir = DIRECTORY_SEPARATOR)
    {
        return disk_total_space($dir);
    }


    public function disk_freespace($dir = DIRECTORY_SEPARATOR)
    {
        return disk_free_space($dir);
    }


    public function disk_usespace($dir = DIRECTORY_SEPARATOR)
    {
        return $this->disk_totalspace($dir) - $this->disk_freespace($dir);
    }


    public function disk_freepercent($dir = DIRECTORY_SEPARATOR, $display_unit = FALSE)
    {
        if ($display_unit === FALSE) {
            $unit = NULL;
        } else {
            $unit = ' %';
        }

        return round(($this->disk_freespace($dir) * 100) / $this->disk_totalspace($dir), 0) . $unit;
    }


    public function disk_usepercent($dir = DIRECTORY_SEPARATOR, $display_unit = FALSE)
    {
        if ($display_unit === FALSE) {
            $unit = NULL;
        } else {
            $unit = ' %';
        }

        return round(($this->disk_usespace($dir) * 100) / $this->disk_totalspace($dir), 0) . $unit;
    }


    public function memory_usage()
    {
        return memory_get_usage();
    }


    public function memory_peak_usage($real = TRUE)
    {
        if ($real) {
            return memory_get_peak_usage(TRUE);
        } else {
            return memory_get_peak_usage(FALSE);
        }
    }


    public function memory_usepercent($real = TRUE, $display_unit = FALSE)
    {
        if ($display_unit === FALSE) {
            $unit = NULL;
        } else {
            $unit = ' %';
        }

        return round(($this->memory_usage() * 100) / $this->memory_peak_usage($real), 0) . $unit;
    }

    public function get_LastIdPeriodo()
    {
        $this->db->select('id');
        $this->db->from('periodo');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $queryResult = $this->db->get();
        return $queryResult->row_array();
    }


    public function get_clientes_por_estado($estado)
    {
        // Obtén el último id_periodo
        $lastIdPeriodo = $this->get_LastIdPeriodo();
        $id_periodo = $lastIdPeriodo['id'];
        $this->db->distinct();
        $this->db->select('medidor.id, cliente.nombre as cliente, cliente.email, documento_pago.estado');
        $this->db->from('cliente');
        $this->db->join('documento_pago', 'cliente.id = documento_pago.id_cliente');
        $this->db->join('medidor', 'cliente.id = medidor.id_cliente');

        $this->db->where('documento_pago.id_periodo', $id_periodo);

        // Aplica el filtro de estado según el valor proporcionado
        if ($estado == 'Pagada') {
            $this->db->where('documento_pago.estado', 'Pagada');
        } elseif ($estado == 'Abonado') {
            $this->db->where('documento_pago.estado', 'Abonado');
        } elseif ($estado == 'Pendiente') {
            $this->db->where('documento_pago.estado', 'Pendiente');
        } 

        $queryResult = $this->db->get();
        return $queryResult->result_array();
    }

    public function update_fecha_facturacion($fecha,$correo)
    {
        $data = array(
            'fecha_facturacion' => $fecha,
            'correos_enviados' => $correo

        );

        // Asume que solo hay una fila en la tabla 'config'
        $this->db->update('config', $data);
    }
}
