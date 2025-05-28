<table id="ordersTable" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th>PO</th>
            <th>Orden</th>
            <th>Cliente</th>
            <th>Fecha OC</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo $order['PO']; ?></td>
                <td><?php echo $order['Orden']; ?></td>
                <td><?php echo $order['Cliente']; ?></td>
                <td><?php echo date('d-m-Y', strtotime($order['FechaOC'])); ?></td>
                <td>
                    <a href="<?php echo site_url('admin/revisar_archivos/enlazar_archivo/' . $file_id . '/' . $order['id']); ?>" class="btn btn-primary">Enlazar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    $(document).ready(function() {
        // Inicializar DataTables en la tabla de la modal
        $('#ordersTable').DataTable({
            "order": [
                [3, 'desc']
            ], // Ordenar por la columna de Fecha OC (4ta columna, índice 3)
            "pageLength": 10, // Paginación de 10 en 10
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json" // Español
            },
            "columnDefs": [{
                "targets": 3, // Índice de la columna de Fecha OC
                "render": function(data, type, row) {
                    if (type === 'display' || type === 'filter') {
                        // Mostrar la fecha en formato día-mes-año
                        return data.split('-').reverse().join('-');
                    }
                    return data; // Ordenar internamente por año-mes-día
                },
                "type": "date"
            }]
        });
    });
</script>