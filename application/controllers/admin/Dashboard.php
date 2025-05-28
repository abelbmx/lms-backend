<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/dashboard_model');
        $this->load->model('admin/ingresar_datos_model'); // Para acceder a los pedidos
        $this->load->model('admin/revisar_archivos_model');
        $this->load->model('documents_model');
        $this->load->model('manifiesto_model'); // Cargar el modelo de manifiestos
    }

    public function index()
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('auth/login', 'refresh');
        } else {
            $this->page_title->push(lang('menu_dashboard'));
            $this->data['pagetitle'] = $this->page_title->show();

            $this->data['breadcrumb'] = $this->breadcrumbs->show();

            // Obtener todos los pedidos
            $pedidos = $this->ingresar_datos_model->get_all_pedidos();
            $this->data['pedidos'] = $pedidos;

            // Contar pedidos terminados y pendientes
            $pedidos_terminados = 0;
            $pedidos_pendientes = 0;
            $total_pedidos = count($pedidos);

            foreach ($pedidos as &$pedido) {
                $pedido['documentos_asociados'] = $this->documents_model->get_documents_by_order($pedido['id']);
                if ($pedido['Estado'] == 4 || $pedido['Estado'] == 5) {
                    $pedidos_terminados++;
                } else {
                    $pedidos_pendientes++;
                }
            }
            unset($pedido); // Buena práctica para evitar referencias inesperadas

            // Pasar los datos a la vista
            $this->data['total_pedidos'] = $total_pedidos;
            $this->data['pedidos_terminados'] = $pedidos_terminados;
            $this->data['pedidos_pendientes'] = $pedidos_pendientes;

            // Obtener pedidos por día
            $this->data['pedidos_por_dia'] = $this->ingresar_datos_model->get_pedidos_por_dia();

            // Documentos escaneados sin enlazar
            $this->data['documentos_escaneados'] = $this->revisar_archivos_model->get_unlinked_files();
            $this->data['total_documentos_escaneados'] = count($this->data['documentos_escaneados']);

            // **Nuevas métricas de Manifiestos**
            $manifiestos = $this->manifiesto_model->get_all_manifiestos();
            $this->data['manifiestos'] = $manifiestos;

            $total_manifiestos = count($manifiestos);
            $manifiestos_entregados = 0;
            $manifiestos_pendientes = 0;
            $total_dias_entrega = 0;

            foreach ($manifiestos as &$manifiesto) {
                if ($manifiesto['estado'] == 'Entregado') {
                    $manifiestos_entregados++;
                    $dias = $this->calculate_business_days($manifiesto['fecha'], $manifiesto['fecha_entrega']);
                    $total_dias_entrega += $dias;
                    $manifiesto['tiempo_entrega'] = $dias;
                } else {
                    $manifiesto['tiempo_entrega'] = 'N/A';
                    $manifiestos_pendientes++;
                }
            }
            unset($manifiesto); // Buena práctica

            $this->data['total_manifiestos'] = $total_manifiestos;
            $this->data['manifiestos_entregados'] = $manifiestos_entregados;
            $this->data['manifiestos_pendientes'] = $manifiestos_pendientes;
            $this->data['promedio_dias_entrega'] = $manifiestos_entregados > 0 ? round($total_dias_entrega / $manifiestos_entregados, 2) : 0;

            // **Preparar datos para gráficos de Manifiestos**
            $metrics_manifiestos_status = $this->manifiesto_model->get_manifiestos_status_metrics();
            $this->data['chart_manifiestos_status'] = $this->prepare_chart_manifiestos_status($metrics_manifiestos_status);

            $metrics_manifiestos_tiempo = $this->manifiesto_model->get_manifiestos_tiempo_metrics();
            $this->data['chart_manifiestos_tiempo'] = $this->prepare_chart_manifiestos_tiempo($metrics_manifiestos_tiempo);

            // **Preparar datos para el gráfico de Pedidos por Tipo**
            $metrics_pedidos_por_tipo = $this->ingresar_datos_model->get_pedidos_por_tipo(); // Asegúrate de tener este método en el modelo
            $this->data['chart_pedidos_por_tipo'] = $this->prepare_chart_pedidos_por_tipo($metrics_pedidos_por_tipo);

            // Otros datos del dashboard...
            $this->template->admin_render('admin/dashboard/index', $this->data);
        }
    }

    // Método para calcular días hábiles entre dos fechas
    private function calculate_business_days($start_date, $end_date)
    {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        if ($end < $start) return 0;

        $interval = new DateInterval('P1D');
        $date_range = new DatePeriod($start, $interval, $end->modify('+1 day'));

        $business_days = 0;
        foreach ($date_range as $date) {
            if (!in_array($date->format('N'), [6, 7])) { // Excluir sábados y domingos
                $business_days++;
            }
        }

        return $business_days;
    }

    // Métodos para preparar datos para gráficos de Manifiestos
    private function prepare_chart_manifiestos_status($metrics)
    {
        $labels = [];
        $data = [];

        foreach ($metrics as $metric) {
            $labels[] = $metric->estado;
            $data[] = $metric->total;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function prepare_chart_manifiestos_tiempo($metrics)
    {
        $labels = [];
        $data = [];

        foreach ($metrics as $metric) {
            $labels[] = $metric->rango;
            $data[] = $metric->total;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    // Método para preparar datos para el gráfico de Pedidos por Tipo
    private function prepare_chart_pedidos_por_tipo($metrics)
    {
        $labels = [];
        $data = [];

        foreach ($metrics as $metric) {
            $labels[] = $metric->tp;
            $data[] = $metric->total;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    // Otros métodos existentes...

    // Nuevo método para buscar documentos sin enlazar
    public function buscar_documentos()
    {
        if ($this->input->is_ajax_request()) {
            $search_term = $this->input->get('query', TRUE);
            $documentos = $this->documents_model->search_unlinked_documents($search_term);

            // Formatear los datos para DataTables o para mostrar en la vista
            $data = [];
            foreach ($documentos as $doc) {
                $data[] = [
                    'id' => $doc->id,
                    'file_name' => $doc->file_name,
                    'uploaded_at' => date('d-m-Y', strtotime($doc->uploaded_at)),
                    'file_path' => base_url($doc->file_path),
                ];
            }

            echo json_encode(['data' => $data]);
        } else {
            show_404();
        }
    }

    // Nuevo método para enlazar documento con pedido
    public function enlazar_documento()
    {
        if ($this->input->post()) {
            $file_id = $this->input->post('file_id', TRUE);
            $order_id = $this->input->post('order_id', TRUE);

            // Validar que los IDs sean válidos
            if ($this->documents_model->is_unlinked($file_id) && $this->ingresar_datos_model->is_valid_order($order_id)) {
                // Enlazar el documento con el pedido
                $success = $this->revisar_archivos_model->link_file_to_order($file_id, $order_id);

                if ($success) {
                    // Devolver una respuesta exitosa
                    echo json_encode(['status' => 'success', 'message' => 'Documento enlazado correctamente.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al enlazar el documento.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'IDs inválidos o documento ya está enlazado.']);
            }
        } else {
            show_404();
        }
    }
}
