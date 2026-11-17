
<?php
    $usuarioAutenticado = auth()->user();
    $empresa = $usuarioAutenticado->empresa;
    $nombreAplicacion = $empresa?->nombre_aplicacion ?: config('app.name');
    $colorPrincipal = $empresa?->color_principal ?: '#0d6efd';
?>
<!doctype html>
<html lang="es">


<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
    <title><?php echo $__env->yieldContent('title', 'Panel'); ?> · <?php echo e($nombreAplicacion); ?></title>
    
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    
    <style>
        :root {
            --primary: <?php echo e($colorPrincipal); ?>;
        }
    </style>
</head>

<body>
    <div class="app-shell">

        
        <aside class="sidebar">

            
            <div class="sidebar-brand">
                <div class="brand-mark"><?php echo e(mb_strtoupper(mb_substr($nombreAplicacion, 0, 1))); ?></div>
                <div>
                    <strong><?php echo e($nombreAplicacion); ?></strong>
                    <small>Gestión de formación</small>
                </div>
            </div>

                        
            <div class="nav-label">Principal</div>
            <a class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('dashboard')); ?>">
                <span class="nav-icon">⌂</span> Inicio
            </a>

            <a class="nav-link <?php echo e(request()->routeIs('settings.*') ? 'active' : ''); ?>"
                href="<?php echo e(route('settings.profile')); ?>">
                <span class="nav-icon">⚙</span> Configuración
            </a>

            
            <a class="nav-link <?php echo e(request()->routeIs('sedes.*') ? 'active' : ''); ?>" href="<?php echo e(route('sedes.index')); ?>">
                <span class="nav-icon">▣</span> Sedes
            </a>

            
            <div class="sidebar-bottom">
                
                <div class="user-mini">
                    <strong><?php echo e($usuarioAutenticado->nombre); ?></strong>
                    <small><?php echo e($usuarioAutenticado->correo_electronico); ?></small>
                </div>
                
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="nav-link logout-button" type="submit">
                        <span class="nav-icon">↪</span> Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        
        <main class="main">

            
            <header class="topbar">
                <div class="topbar-title"><?php echo $__env->yieldContent('topbar', 'Inicio'); ?></div>
                <?php if($empresa): ?>
                    <div class="topbar-company"><?php echo e($empresa->nombre_comercial ?: $empresa->razon_social); ?></div>
                <?php endif; ?>
            </header>

            
            <div class="content">
                
                <?php echo $__env->make('parciales.alertas', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </main>
    </div>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\ERP_practicas-main\resources\views/plantillas/principal.blade.php ENDPATH**/ ?>