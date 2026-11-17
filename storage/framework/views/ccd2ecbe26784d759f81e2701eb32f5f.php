<?php $__env->startSection('title', 'Configuración'); ?>
<?php $__env->startSection('topbar', 'Configuración'); ?>


<?php $__env->startSection('content'); ?>

<div class="page-heading">
    <div>
        <h1>Configuración</h1>
        <p>Gestiona los datos de tu cuenta y tu contraseña.</p>
    </div>
</div>


<div class="settings-grid">
    
    <div class="card">
        <div class="card-header">
            <div>
                <h2>Datos de usuario</h2>
                <p class="card-subtitle">Información asociada a tu cuenta.</p>
            </div>
        </div>

        <div class="card-body">
            
            <form method="POST" action="<?php echo e(route('settings.profile.update')); ?>">
                
                <?php echo csrf_field(); ?>
                
                <?php echo method_field('PUT'); ?>

                <div class="form-grid">
                    
                    <div class="form-group full">
                        <label class="form-label" for="nombre">Nombre</label>
                        <input
                            class="form-control"
                            id="nombre"
                            name="nombre"
                            type="text"
                            value="<?php echo e(old('nombre', $usuario->nombre)); ?>"
                            autocomplete="name"
                            required
                        >
                        
                        <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div class="form-group full">
                        <label class="form-label" for="correo_electronico">Email</label>
                        <input
                            class="form-control"
                            id="correo_electronico"
                            name="correo_electronico"
                            type="email"
                            value="<?php echo e(old('correo_electronico', $usuario->correo_electronico)); ?>"
                            autocomplete="email"
                            required
                        >
                        <?php $__errorArgs = ['correo_electronico'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div class="form-actions">
                    
                    <button class="btn btn-primary" type="submit">Guardar datos</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header">
            <div>
                <h2>Cambiar contraseña</h2>
                <p class="card-subtitle">Por seguridad, necesitamos comprobar tu contraseña actual.</p>
            </div>
        </div>

        <div class="card-body">
            
            <form method="POST" action="<?php echo e(route('settings.password.update')); ?>">
                
                <?php echo csrf_field(); ?>
                
                <?php echo method_field('PUT'); ?>

                <div class="form-grid">
                    <div class="form-group full">
                        
                        <label class="form-label" for="contrasena_actual">Contraseña actual</label>
                        <input
                            class="form-control"
                            id="contrasena_actual"
                            name="contrasena_actual"
                            type="password"
                            autocomplete="current-password"
                            required
                        >
                        <?php $__errorArgs = ['contrasena_actual'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group full">
                        
                        <label class="form-label" for="contrasena">Nueva contraseña</label>
                        <input
                            class="form-control"
                            id="contrasena"
                            name="contrasena"
                            type="password"
                            autocomplete="new-password"
                            required
                        >
                        <div class="form-help">Mínimo 8 caracteres.</div>
                        <?php $__errorArgs = ['contrasena'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group full">
                        
                        <label class="form-label" for="contrasena_confirmation">Repetir nueva contraseña</label>
                        <input
                            class="form-control"
                            id="contrasena_confirmation"
                            name="contrasena_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                        >
                    </div>
                </div>

                <div class="form-actions">
                    
                    <button class="btn btn-primary" type="submit">Cambiar contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('plantillas.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ERP_practicas-main\resources\views/configuracion/index.blade.php ENDPATH**/ ?>