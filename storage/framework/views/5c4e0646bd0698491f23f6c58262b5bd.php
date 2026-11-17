<?php $__env->startSection('title', 'Inicio'); ?>
<?php $__env->startSection('topbar', 'Inicio'); ?>


<?php $__env->startSection('content'); ?>

<div class="page-heading">
    <div>
        <h1>Hola, <?php echo e($usuario->nombre); ?></h1>
        <p>Bienvenido al panel principal.</p>
    </div>
</div>


<div class="card welcome-card">
    <div class="card-body">
        <h2 style="margin:0 0 8px;">Panel principal</h2>
        <p style="margin:0; color:#64748b; line-height:1.65;">
            Esta es la base inicial de la aplicación. Los módulos de gestión se irán incorporando progresivamente.
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('plantillas.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ERP_practicas-main\resources\views/inicio/index.blade.php ENDPATH**/ ?>