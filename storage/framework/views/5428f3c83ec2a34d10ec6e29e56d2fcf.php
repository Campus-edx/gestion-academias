



<?php $__env->startSection('title', $sede->exists ? 'Editar sede' : 'Nueva sede'); ?>

<?php $__env->startSection('topbar', 'Sedes'); ?>


<?php $__env->startSection('content'); ?>


<div class="page-heading">
    <div>
        <h1><?php echo e($sede->exists ? 'Editar sede' : 'Nueva sede'); ?></h1>
    </div>
</div>


<div class="card">
    <div class="card-body">

        
        <form method="POST" action="<?php echo e($sede->exists ? route('sedes.update', $sede) : route('sedes.store')); ?>">
            
            <?php echo csrf_field(); ?>
            
            <?php if($sede->exists): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?>

            <div class="form-grid">

                
                <?php $__currentLoopData = [
                    'nombre' => 'Nombre',
                    'direccion' => 'Dirección',
                    'codigo_postal' => 'Código postal',
                    'localidad' => 'Localidad',
                    'telefono' => 'Teléfono',
                    'correo_electronico' => 'Email',
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campo => $etiqueta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="form-group">
                        
                        <label class="form-label" for="<?php echo e($campo); ?>"><?php echo e($etiqueta); ?></label>
                        
                        <input class="form-control" id="<?php echo e($campo); ?>" name="<?php echo e($campo); ?>" type="text"
                               value="<?php echo e(old($campo, $sede->$campo)); ?>">
                        
                        <?php $__errorArgs = [$campo];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                
                <div class="form-group">
                    <label class="form-label" for="tipo">Tipo</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="propia" <?php if(old('tipo', $sede->tipo) === 'propia'): echo 'selected'; endif; ?>>Propia</option>
                        <option value="externa" <?php if(old('tipo', $sede->tipo) === 'externa'): echo 'selected'; endif; ?>>Externa</option>
                    </select>
                </div>

                
                <div class="form-group">
                    <label class="checkbox-row">
                        <input type="checkbox" name="activa" value="1" <?php if(old('activa', $sede->activa)): echo 'checked'; endif; ?>>
                        Activa
                    </label>
                </div>
            </div>

            
            <div class="form-actions">
                <a class="btn btn-secondary" href="<?php echo e(route('sedes.index')); ?>">Cancelar</a>
                <button class="btn btn-primary" type="submit">Guardar</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('plantillas.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ERP_practicas-main\resources\views/sedes/formulario.blade.php ENDPATH**/ ?>