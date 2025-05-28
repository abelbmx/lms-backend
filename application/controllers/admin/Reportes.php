<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reportes extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/reportes_model');
        $this->load->model('manifiesto_model');
        $this->load->model('orders_model');
        $this->load->model('documents_model');
        $this->load->helper('url');
        $this->load->library('ion_auth');
    }

    public function index()
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('auth/login', 'refresh');
        }

        $this->page_title->push('Reportes');
        $this->data['pagetitle'] = $this->page_title->show();

        $this->breadcrumbs->unshift(1, 'Reportes', 'admin/reportes');
        $this->data['breadcrumb'] = $this->breadcrumbs->show();

        $fecha_oc_inicio = $this->input->get('fecha_oc_inicio');
        $fecha_oc_fin = $this->input->get('fecha_oc_fin');
        $fecha_f_inicio = $this->input->get('fecha_f_inicio');
        $fecha_f_fin = $this->input->get('fecha_f_fin');

        $ordenes = $this->reportes_model->get_orders_with_filters($fecha_oc_inicio, $fecha_oc_fin, $fecha_f_inicio, $fecha_f_fin);
        $this->data['ordenes'] = $ordenes;

        $pedidos_terminados = 0;
        $pedidos_pendientes = 0;
        $total_pedidos = count($ordenes);

        foreach ($ordenes as $orden) {
            if ($orden->Estado == 4 || $orden->Estado == 5) {
                $pedidos_terminados++;
            } else {
                $pedidos_pendientes++;
            }
        }

        $this->data['total_pedidos'] = $total_pedidos;
        $this->data['pedidos_terminados'] = $pedidos_terminados;
        $this->data['pedidos_pendientes'] = $pedidos_pendientes;

        $documentos_enlazados = $this->documents_model->get_linked_files();
        $documentos_sin_enlazar = $this->documents_model->get_unlinked_files();

        $this->data['documentos_enlazados'] = $documentos_enlazados;
        $this->data['documentos_sin_enlazar'] = $documentos_sin_enlazar;
        $this->data['total_documentos_sin_enlazar'] = count($documentos_sin_enlazar);
        $this->data['total_documentos_enlazados'] = count($documentos_enlazados);

        $this->data['tiempo_promedio_enlace'] = $this->calculate_average_business_link_time($documentos_enlazados);

        $metrics_orders = $this->reportes_model->get_order_metrics($fecha_oc_inicio, $fecha_oc_fin);
        $this->data['chart_orders_status'] = $this->prepare_chart_orders_status($metrics_orders);
        $this->data['chart_documents'] = $this->prepare_chart_documents($documentos_enlazados, $documentos_sin_enlazar);
        $this->data['chart_top_clients'] = $this->prepare_chart_top_clients($ordenes);
        $this->data['chart_orders_by_region'] = $this->prepare_chart_orders_by_region($ordenes);

        $this->data['estados'] = array(
            1 => 'Información Creada',
            2 => 'Documento Enlazado',
            3 => 'Documentos Escaneados',
            4 => 'Terminado',
            5 => 'Fin del Proceso'
        );

        $pedidos_por_dia = $this->reportes_model->get_pedidos_por_dia($fecha_oc_inicio, $fecha_oc_fin);
        $this->data['pedidos_por_dia'] = $pedidos_por_dia;

        $manifiestos = $this->manifiesto_model->get_all_manifiestos();
        $this->data['manifiestos'] = $manifiestos;

        $total_manifiestos = count($manifiestos);
        $manifiestos_entregados = 0;
        $total_dias_entrega = 0;

        foreach ($manifiestos as &$manifiesto) {
            if ($manifiesto['estado'] == 'Entregado') {
                $manifiestos_entregados++;
                $dias = $this->calculate_business_days($manifiesto['fecha'], $manifiesto['fecha_entrega']);
                $total_dias_entrega += $dias;
                $manifiesto['tiempo_entrega'] = $dias;
            } else {
                $manifiesto['tiempo_entrega'] = 'N/A';
            }
        }
        unset($manifiesto);

        $this->data['total_manifiestos'] = $total_manifiestos;
        $this->data['manifiestos_entregados'] = $manifiestos_entregados;
        $this->data['manifiestos_pendientes'] = $total_manifiestos - $manifiestos_entregados;
        $this->data['promedio_dias_entrega'] = $manifiestos_entregados > 0 ? round($total_dias_entrega / $manifiestos_entregados, 2) : 0;

        $this->data['chart_manifiestos_status'] = $this->prepare_chart_manifiestos_status($manifiestos);
        $this->data['chart_manifiestos_tiempo'] = $this->prepare_chart_manifiestos_tiempo($manifiestos);

        $this->template->admin_render('admin/reportes/index', $this->data);
    }

    private function calculate_average_business_link_time($documentos_enlazados)
    {
        $total_days = 0;
        $count = 0;

        foreach ($documentos_enlazados as $doc) {
            if (!empty($doc->uploaded_at) && !empty($doc->enlazado_at)) {
                $dias = $this->calculate_business_days($doc->uploaded_at, $doc->enlazado_at);
                $total_days += $dias;
                $count++;
            }
        }

        if ($count == 0) return '0 días';

        $average_days = round($total_days / $count, 2);

        return $average_days . ' días';
    }

    private function calculate_business_days($start_date, $end_date)
    {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        if ($end < $start) return 0;

        $interval = new DateInterval('P1D');
        $date_range = new DatePeriod($start, $interval, $end->modify('+1 day'));

        $business_days = 0;
        foreach ($date_range as $date) {
            if (!in_array($date->format('N'), [6, 7])) {
                $business_days++;
            }
        }

        return $business_days;
    }

    private function prepare_chart_orders_status($metrics_orders)
    {
        $labels = [];
        $data = [];

        $estado_labels = array(
            1 => 'Información Creada',
            2 => 'Documento Enlazado',
            3 => 'Documentos Escaneados',
            4 => 'Terminado',
            5 => 'Fin del Proceso'
        );

        $estado_counts = array_fill_keys(array_keys($estado_labels), 0);

        foreach ($metrics_orders as $metric) {
            $estado = $metric->Estado;
            if (array_key_exists($estado, $estado_counts)) {
                $estado_counts[$estado] += $metric->total;
            } else {
                $estado_labels[$estado] = 'Desconocido';
                $estado_counts[$estado] = $metric->total;
            }
        }

        foreach ($estado_labels as $estado => $label) {
            $labels[] = $label;
            $data[] = isset($estado_counts[$estado]) ? $estado_counts[$estado] : 0;
        }

        return array(
            'labels' => $labels,
            'data' => $data
        );
    }

    private function prepare_chart_orders_by_region($ordenes)
    {
        $region_counts = [];
        foreach ($ordenes as $orden) {
            $region = $orden->Region;
            if (isset($region_counts[$region])) {
                $region_counts[$region]++;
            } else {
                $region_counts[$region] = 1;
            }
        }

        ksort($region_counts);

        $labels = array_keys($region_counts);
        $data = array_values($region_counts);

        return array(
            'labels' => $labels,
            'data' => $data
        );
    }

    private function prepare_chart_documents($documentos_enlazados, $documentos_sin_enlazar)
    {
        $labels = ['Enlazados', 'Sin Enlazar'];
        $data = [count($documentos_enlazados), count($documentos_sin_enlazar)];

        return array(
            'labels' => $labels,
            'data' => $data
        );
    }

    private function prepare_chart_top_clients($ordenes)
    {
        $client_counts = [];
        foreach ($ordenes as $orden) {
            $cliente = $orden->Cliente;
            if (isset($client_counts[$cliente])) {
                $client_counts[$cliente]++;
            } else {
                $client_counts[$cliente] = 1;
            }
        }

        arsort($client_counts);
        $top_clients = array_slice($client_counts, 0, 10, true);

        $labels = array_keys($top_clients);
        $data = array_values($top_clients);

        return array(
            'labels' => $labels,
            'data' => $data
        );
    }

    private function prepare_chart_manifiestos_status($manifiestos)
    {
        $labels = [];
        $data = [];

        $estado_labels = array(
            'Entregado' => 'Entregado',
            'Activo' => 'Activo'
        );

        $estado_counts = array_fill_keys(array_keys($estado_labels), 0);

        foreach ($manifiestos as $manifiesto) {
            $estado = $manifiesto['estado'];
            if (array_key_exists($estado, $estado_counts)) {
                $estado_counts[$estado] += 1;
            } else {
                $estado_labels[$estado] = 'Desconocido';
                $estado_counts[$estado] = 1;
            }
        }

        foreach ($estado_labels as $estado => $label) {
            $labels[] = $label;
            $data[] = isset($estado_counts[$estado]) ? $estado_counts[$estado] : 0;
        }

        return array(
            'labels' => $labels,
            'data' => $data
        );
    }

    private function prepare_chart_manifiestos_tiempo($manifiestos)
    {
        $tiempos = [];

        foreach ($manifiestos as $manifiesto) {
            if ($manifiesto['estado'] == 'Entregado') {
                $dias = isset($manifiesto['tiempo_entrega']) ? $manifiesto['tiempo_entrega'] : 0;
                $tiempos[] = $dias;
            }
        }

        $rangos = [
            '0-5 días' => 0,
            '6-10 días' => 0,
            '11-15 días' => 0,
            '16-20 días' => 0,
            '21+ días' => 0
        ];

        foreach ($tiempos as $dia) {
            if ($dia <= 5) {
                $rangos['0-5 días'] += 1;
            } elseif ($dia <= 10) {
                $rangos['6-10 días'] += 1;
            } elseif ($dia <= 15) {
                $rangos['11-15 días'] += 1;
            } elseif ($dia <= 20) {
                $rangos['16-20 días'] += 1;
            } else {
                $rangos['21+ días'] += 1;
            }
        }

        return array(
            'labels' => array_keys($rangos),
            'data' => array_values($rangos)
        );
    }
}
