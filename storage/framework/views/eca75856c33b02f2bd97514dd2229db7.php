



<?php $__env->startSection('title', 'Sedes'); ?>
<?php $__env->startSection('topbar', 'Sedes'); ?>


<?php $__env->startSection('content'); ?>


<div class="page-heading">
    <div>
        <h1>Sedes</h1>
        <p>Centros de la academia y lugares externos donde se imparte.</p>
    </div>
    
    <a class="btn btn-primary" href="<?php echo e(route('sedes.create')); ?>">Nueva sede</a>
</div>


<div class="card">
    <div class="table-wrap">
        <table>
            
            <thead>
                <tr><th>Nombre</th><th>Localidad</th><th>Tipo</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                
                <?php $__empty_1 = true; $__currentLoopData = $sedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sede): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($sede->nombre); ?></td>
                        <td><?php echo e($sede->localidad); ?></td>
                        
                        <td><?php echo e(ucfirst($sede->tipo)); ?></td>
                        
                        <td>
                            <?php if($sede->activa): ?>
                                <span class="badge badge-success">Activa</span>
                            <?php else: ?>
                                <span class="badge badge-muted">Inactiva</span>
                            <?php endif; ?>
                        </td>
                        
                        <td class="actions">
                            
                            <a class="btn btn-secondary btn-sm" href="<?php echo e(route('sedes.edit', $sede)); ?>">Editar</a>
                            
                            <form method="POST" action="<?php echo e(route('sedes.destroy', $sede)); ?>" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-danger btn-sm" type="submit">Borrar</button>
                            </form>
                        </td>
                    </tr>
                
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="empty-state">Todavía no hay sedes.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('plantillas.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ERP_practicas-main\resources\views/sedes/index.blade.php ENDPATH**/ ?>