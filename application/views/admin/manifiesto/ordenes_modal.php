<!-- application/views/admin/manifiesto/ordenes_modal.php -->
<div class="table-responsive">
    <table class="table table-striped nowrap" style="width:100%">
        <thead>
            <tr>
                <th>ID Orden</th>
                <th>PO</th>
                <th>Fecha OC</th>
                <th>Cliente</th>
                <th>Guía</th>
                <th>Ship</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($ordenes)): ?>
                <?php foreach ($ordenes as $orden): ?>
                    <tr>
                        <td><?php echo $orden['id']; ?></td>
                        <td><?php echo htmlspecialchars($orden['PO']); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($orden['FechaOC'])); ?></td>
                        <td><?php echo htmlspecialchars($orden['Cliente']); ?></td>
                        <td><?php echo htmlspecialchars($orden['Guia']); ?></td>
                        <td><?php echo htmlspecialchars($orden['Ship']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No hay órdenes enlazadas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>