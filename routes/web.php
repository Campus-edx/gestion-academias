<?php
// Esta línea indica que todo lo que viene a continuación es código PHP.
// Este archivo es routes/web.php: la "centralita" de la aplicación.
// Aquí se decide qué pasa cuando alguien escribe una dirección (URL) en el navegador.


// ─────────────────────────────────────────────────────────────
// "use" sirve para avisar de qué otras piezas del proyecto vamos a usar.
// Es como decir al principio: "en este archivo voy a llamar a estas personas".
// Así después podemos escribir solo su nombre corto (AccesoController)
// en lugar de su dirección completa dentro del proyecto.
// ─────────────────────────────────────────────────────────────

// El controlador que se encarga de entrar y salir de la aplicación (login y logout).
// Está en la carpeta app/Http/Controllers/Acceso/.
use App\Http\Controllers\Acceso\AccesoController;

// El controlador que se encarga de la pantalla de configuración del usuario.
use App\Http\Controllers\ConfiguracionController;

// El controlador que se encarga de la pantalla de inicio (el panel principal).
use App\Http\Controllers\InicioController;

// "Route" es una herramienta que trae Laravel para crear rutas.
// Una ruta = una regla que dice: "si alguien pide ESTA dirección, haz ESTO".
use Illuminate\Support\Facades\Route;

// NUEVO: el controlador que se encarga de las sedes
// (listar, crear, editar y borrar sedes). Está en app/Http/Controllers/SedesController.php.
use App\Http\Controllers\SedesController;


// ─────────────────────────────────────────────────────────────
// RUTA DE LA PORTADA: qué pasa al entrar en la dirección principal "/"
// (por ejemplo, http://127.0.0.1:8001 sin nada detrás)
// ─────────────────────────────────────────────────────────────

// Route::get significa: "cuando el navegador PIDA ver esta dirección".
// (GET es la forma normal de pedir una página: escribir una URL o pulsar un enlace).
// '/' es la dirección: la raíz, la portada de la web.
// "fn () =>" crea una pequeña función sin nombre: unas instrucciones que se
// ejecutan en el momento en que alguien entra en esa dirección.
Route::get('/', fn () => auth()->check()
    // auth()->check() pregunta: "¿esta persona ha iniciado sesión?".
    // Responde sí (true) o no (false).
    // El signo "?" significa: "si la respuesta es SÍ, haz lo siguiente":
    ? redirect()->route('dashboard')
    // → Si SÍ ha iniciado sesión: la mandamos (redirect = redirigir)
    //   a la ruta que se llama 'dashboard', es decir, al panel de inicio.

    // El signo ":" significa: "y si la respuesta es NO, haz esto otro":
    : redirect()->route('login'));
    // → Si NO ha iniciado sesión: la mandamos a la pantalla de login.


// ─────────────────────────────────────────────────────────────
// GRUPO DE RUTAS PARA "INVITADOS" (gente que NO ha iniciado sesión)
// ─────────────────────────────────────────────────────────────

// middleware('guest') es un filtro, como un portero en la puerta.
// 'guest' significa "invitado": solo deja pasar a quien NO ha iniciado sesión.
// Si alguien que ya está dentro intenta ir al login, el portero lo devuelve al inicio.
// ->group(function () { ... }) mete varias rutas dentro de las llaves { }
// para que el mismo filtro se aplique a todas a la vez.
Route::middleware('guest')->group(function () {

    // Cuando el navegador PIDE (get) la dirección /login...
    // ...Laravel busca el controlador AccesoController y ejecuta su método "mostrar",
    // que devuelve la pantalla con el formulario de usuario y contraseña.
    // ->name('login') le pone un nombre interno a la ruta: 'login'.
    // Con ese nombre, en otras partes del código podemos decir "llévame a 'login'"
    // sin tener que escribir la dirección a mano.
    Route::get('/login', [AccesoController::class, 'mostrar'])->name('login');

    // Route::post significa: "cuando el navegador ENVÍE datos a esta dirección".
    // (POST es la forma de enviar información, por ejemplo al pulsar "Entrar" en un formulario).
    // Aquí llegan el correo y la contraseña que ha escrito el usuario,
    // y se ejecuta el método "acceder" de AccesoController, que los comprueba.
    // Su nombre interno es 'login.attempt' ("intento de login").
    Route::post('/login', [AccesoController::class, 'acceder'])->name('login.attempt');

}); // Aquí se cierra el grupo de rutas para invitados.


// ─────────────────────────────────────────────────────────────
// GRUPO DE RUTAS PRIVADAS (solo para quien SÍ ha iniciado sesión)
// ─────────────────────────────────────────────────────────────

// middleware('auth') es el portero contrario: 'auth' viene de "autenticado".
// Solo deja pasar a quien HA iniciado sesión.
// Si alguien sin sesión intenta entrar en cualquiera de estas direcciones,
// el portero no le deja y lo manda a la ruta llamada 'login'.
Route::middleware('auth')->group(function () {

    // Cerrar sesión. Es POST (enviar) y no GET (pedir) por seguridad:
    // así no se puede cerrar la sesión de alguien solo con mandarle un enlace.
    // Ejecuta el método "salir" de AccesoController.
    Route::post('/logout', [AccesoController::class, 'salir'])->name('logout');

    // El panel principal, al que se llega después de iniciar sesión.
    // Aquí no se indica ningún método porque InicioController solo hace una cosa:
    // cuando se le llama, ejecuta automáticamente su único método (__invoke).
    Route::get('/dashboard', InicioController::class)->name('dashboard');

    // PEDIR (get) la pantalla de configuración:
    // ejecuta "editar", que muestra el formulario con tus datos actuales.
    Route::get('/configuracion', [ConfiguracionController::class, 'editar'])->name('settings.profile');

    // Route::put significa: "cuando el navegador envíe datos para MODIFICAR algo que ya existe".
    // Al pulsar "Guardar datos" en configuración, llegan tu nombre y tu correo nuevos,
    // y el método "actualizar" los comprueba y los guarda en la base de datos.
    // Fíjate en que la dirección es la misma que la de arriba (/configuracion):
    // Laravel las distingue porque una es GET (ver) y otra es PUT (modificar).
    Route::put('/configuracion', [ConfiguracionController::class, 'actualizar'])->name('settings.profile.update');

    // También es PUT (modificar), pero en otra dirección: /configuracion/password.
    // Aquí llega el formulario de cambiar la contraseña, y el método
    // "actualizarContrasena" comprueba la contraseña actual antes de guardar la nueva.
    Route::put('/configuracion/password', [ConfiguracionController::class, 'actualizarContrasena'])->name('settings.password.update');


    // ─────────────────────────────────────────────────────────
    // NUEVO: RUTAS DE SEDES (el CRUD: crear, ver, modificar y borrar sedes)
    // Están dentro de este grupo, así que solo se puede entrar con la sesión iniciada.
    // Las direcciones van en castellano (/crear, /editar), como pide la guía del profesor.
    // {sede} es un hueco que se rellena con el id de la sede: /sedes/3/editar.
    // Laravel busca sola la sede con ese id y se la pasa al controlador.
    // ─────────────────────────────────────────────────────────

    // VER el listado de todas las sedes (método index).
    Route::get('/sedes', [SedesController::class, 'index'])->name('sedes.index');

    // VER el formulario vacío para crear una sede nueva (método create).
    Route::get('/sedes/crear', [SedesController::class, 'create'])->name('sedes.create');

    // GUARDAR la sede nueva cuando se envía ese formulario (POST, método store).
    Route::post('/sedes', [SedesController::class, 'store'])->name('sedes.store');

    // VER el formulario para editar una sede concreta, ya relleno con sus datos (método edit).
    Route::get('/sedes/{sede}/editar', [SedesController::class, 'edit'])->name('sedes.edit');

    // GUARDAR los cambios de esa sede (PUT = modificar, método update).
    Route::put('/sedes/{sede}', [SedesController::class, 'update'])->name('sedes.update');

    // BORRAR esa sede (DELETE = eliminar, método destroy).
    Route::delete('/sedes/{sede}', [SedesController::class, 'destroy'])->name('sedes.destroy');

}); // Aquí se cierra el grupo de rutas privadas. 
