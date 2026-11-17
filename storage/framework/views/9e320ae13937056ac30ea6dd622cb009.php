
<?php
    $nombreAplicacion = $empresa?->nombre_aplicacion ?: config('app.name');
    $colorPrincipal = $empresa?->color_principal ?: '#0d6efd';
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso · <?php echo e($nombreAplicacion); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <style>:root { --primary: <?php echo e($colorPrincipal); ?>; }</style>
</head>
<body>

<div class="login-page">
    
    <section class="login-hero">
        <div class="login-brand">
            <div class="brand-mark"><?php echo e(mb_strtoupper(mb_substr($nombreAplicacion, 0, 1))); ?></div>
            <strong><?php echo e($nombreAplicacion); ?></strong>
        </div>
        <div>
            <h1>Gestión clara para empresas de formación.</h1>
            <p>Accede al entorno de gestión con tu cuenta de usuario.</p>
        </div>
        <small>Beta 0.2.1 · Entorno local de desarrollo</small>
    </section>

    
    <section class="login-panel">
        <div class="login-card">
            <h2>Iniciar sesión</h2>
            <p>Accede al entorno de administración.</p>

            
            <?php if($errors->any()): ?>
                <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
            <?php endif; ?>

            
            <form method="POST" action="<?php echo e(route('login.attempt')); ?>">
                
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label class="form-label" for="correo_electronico">Email</label>
                    
                    <input class="form-control" id="correo_electronico" name="correo_electronico" type="email" value="<?php echo e(old('correo_electronico')); ?>" autocomplete="email" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label" for="contrasena">Contraseña</label>
                    
                    <input class="form-control" id="contrasena" name="contrasena" type="password" autocomplete="current-password" required>
                </div>
                <div class="form-group checkbox-row">
                    
                    <input id="recordar" name="recordar" type="checkbox" value="1">
                    <label for="recordar">Mantener la sesión iniciada</label>
                </div>
                
                <button class="btn btn-primary btn-block" type="submit">Entrar</button>
            </form>

            
            <div style="margin-top:24px; padding:14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:9px; font-size:13px; color:#6b7280;">
                <strong style="color:#374151;">Usuario demo administrador</strong><br>
                admin@demo.local / Admin1234!
            </div>
        </div>
    </section>
</div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\ERP_practicas-main\resources\views/acceso/index.blade.php ENDPATH**/ ?>