<?php
// Esta línea indica que todo lo que viene a continuación es código PHP.
// Este archivo es el CONTROLADOR DE ACCESO: el encargado de todo lo que tiene
// que ver con entrar en la aplicación (login) y salir de ella (logout).
// Un controlador es como un recepcionista: recibe la petición que llega desde
// la ruta (routes/web.php), decide qué hay que hacer y devuelve una respuesta.


// "namespace" es la dirección de este archivo dentro del proyecto.
// Es como decir: "este controlador vive en la carpeta Http/Controllers/Acceso".
// Sirve para que no se confunda con otra clase que pudiera llamarse igual en otra carpeta.
namespace App\Http\Controllers\Acceso;


// ─────────────────────────────────────────────────────────────
// "use": las piezas de otros archivos que vamos a usar aquí.
// Es como presentar al principio a todos los que van a participar.
// ─────────────────────────────────────────────────────────────

// El controlador base del proyecto. Todos los controladores parten de él.
use App\Http\Controllers\Controlador;

// El modelo Empresas: la pieza que sabe leer y escribir en la tabla "empresas".
use App\Models\Empresas;

// El modelo Usuarios: la pieza que sabe leer y escribir en la tabla "usuarios".
use App\Models\Usuarios;

// "RedirectResponse" es un tipo de respuesta que significa: "vete a otra página".
use Illuminate\Http\RedirectResponse;

// "Request" (petición) es un objeto que contiene TODO lo que envía el navegador:
// lo que se ha escrito en el formulario, quién es el usuario, su sesión...
use Illuminate\Http\Request;

// "Auth" es la herramienta de Laravel para iniciar y cerrar sesión.
use Illuminate\Support\Facades\Auth;

// "Hash" es la herramienta para trabajar con contraseñas cifradas.
use Illuminate\Support\Facades\Hash;

// "View" es un tipo de respuesta que significa: "aquí tienes una pantalla para mostrar".
use Illuminate\View\View;


// "class" crea una clase: un molde que agrupa varias funciones relacionadas.
// Esta se llama AccesoController.
// "extends Controlador" significa que HEREDA de Controlador: tiene todo lo que
// tenga Controlador y además lo que escribamos aquí dentro.
// Todo lo que pertenece a la clase va entre estas llaves { }.
class AccesoController extends Controlador
{
    // ═════════════════════════════════════════════════════════
    // MÉTODO 1: MOSTRAR la pantalla de login
    // Se ejecuta cuando alguien entra en /login (ruta GET /login).
    // ═════════════════════════════════════════════════════════

    // "public function" crea un método (una acción que sabe hacer la clase).
    // "public" significa que se puede llamar desde fuera, por ejemplo desde las rutas.
    // "mostrar" es su nombre.
    // Los paréntesis () vacíos significan que no necesita recibir ningún dato.
    // ": View" avisa de lo que va a devolver: una pantalla (vista).
    public function mostrar(): View
    {
        // "return" significa "devuelve esto y termina".
        // view('acceso.index', ...) carga la pantalla que está en
        // resources/views/acceso/index.blade.php (el punto equivale a una carpeta).
        // Lo que va entre corchetes [ ] son los datos que le pasamos a esa pantalla.
        return view('acceso.index', [
            // Le pasamos un dato llamado 'empresa'. Para conseguirlo:
            // Empresas::query()  → "voy a hacer una consulta a la tabla empresas"
            // ->where('activo', true) → "solo las que estén activas"
            // ->first()          → "dame solo la primera que encuentres"
            // La pantalla de login la usa para mostrar el nombre y el logo de la academia.
            'empresa' => Empresas::query()->where('activo', true)->first(),
        ]);
    }


    // ═════════════════════════════════════════════════════════
    // MÉTODO 2: ACCEDER (comprobar el usuario y la contraseña)
    // Se ejecuta cuando alguien pulsa "Entrar" (ruta POST /login).
    // ═════════════════════════════════════════════════════════

    // Este método SÍ recibe un dato: "Request $request".
    // Laravel le entrega automáticamente la petición del navegador,
    // y dentro del método la llamaremos $request.
    // En PHP todas las variables (cajas donde se guarda información) empiezan por $.
    // ": RedirectResponse" avisa de que siempre terminará mandando a otra página.
    public function acceder(Request $request): RedirectResponse
    {
        // ── PASO 1: comprobar que el formulario viene bien rellenado ──

        // $request->validate(...) revisa los datos que han llegado del formulario.
        // Si todo está bien, los guarda en la variable $datos.
        // Si algo está mal, Laravel PARA AQUÍ automáticamente, vuelve al formulario
        // y muestra los mensajes de error. El resto del método ya no se ejecuta.
        $datos = $request->validate([
            // Primer bloque de corchetes: las REGLAS que debe cumplir cada campo.
            // El campo correo_electronico es obligatorio (required)
            // y debe tener forma de email (email): algo@algo.algo
            'correo_electronico' => ['required', 'email'],
            // El campo contrasena es obligatorio y debe ser texto (string).
            'contrasena' => ['required', 'string'],
        ], [
            // Segundo bloque de corchetes: los MENSAJES que se muestran si se
            // incumple cada regla. Se escriben aquí para que salgan en castellano.
            // 'campo.regla' => 'mensaje'
            'correo_electronico.required' => 'Introduce tu email.',
            'correo_electronico.email' => 'Introduce un email válido.',
            'contrasena.required' => 'Introduce tu contraseña.',
        ]);

        // ── PASO 2: buscar al usuario ──

        // Mira si se ha marcado la casilla "Recordarme" del formulario.
        // $request->boolean(...) devuelve true (sí, marcada) o false (no marcada).
        // Lo guardamos en la variable $recordar para usarlo más adelante.
        $recordar = $request->boolean('recordar');

        // Buscamos en la tabla usuarios a alguien con el correo que se ha escrito:
        // Usuarios::query() → "voy a consultar la tabla usuarios"
        // ->where('correo_electronico', $datos['correo_electronico'])
        //      → "donde la columna correo_electronico sea igual al correo escrito"
        //        ($datos['correo_electronico'] es el correo que llegó del formulario)
        // ->first() → "dame el primero que encuentres"
        // Si no existe nadie con ese correo, $usuario se queda vacío (null).
        $usuario = Usuarios::query()
            ->where('correo_electronico', $datos['correo_electronico'])
            ->first();

        // ── PASO 3: comprobar que el usuario existe y que la contraseña es correcta ──

        // "if" significa "si se cumple esto, haz lo que hay entre llaves".
        // El signo "!" significa "NO".
        // El signo "||" significa "O".
        // Así que esta línea se lee:
        // "Si NO hay usuario, O la contraseña NO coincide, entonces..."
        //
        // Hash::check(lo_que_ha_escrito, lo_guardado) compara la contraseña escrita
        // con la que hay en la base de datos. La guardada está cifrada (no se puede
        // leer), así que Hash::check cifra la escrita y comprueba si coinciden.
        if (!$usuario || !Hash::check($datos['contrasena'], $usuario->contrasena)) {
            // back() → "vuelve a la página anterior" (el formulario de login).
            return back()
                // ->withErrors(...) → muestra este mensaje de error junto al campo del correo.
                // Se dice "credenciales no correctas" sin aclarar si falla el correo o
                // la contraseña, a propósito: así nadie puede averiguar qué correos existen.
                ->withErrors(['correo_electronico' => 'Las credenciales no son correctas.'])
                // ->onlyInput(...) → deja escrito el correo en el formulario para no
                // tener que teclearlo otra vez. La contraseña NO se devuelve, por seguridad.
                ->onlyInput('correo_electronico');
        }

        // ── PASO 4: comprobar que el usuario y su empresa están activos ──

        // Se lee: "Si el usuario NO está activo, O su empresa NO está activa, entonces..."
        // $usuario->activo → la columna "activo" de ese usuario (sí o no).
        // $usuario->empresa → la empresa a la que pertenece (gracias a la relación
        //                      que está definida en el modelo Usuarios).
        // El "?->" es una flecha "con cuidado": si el usuario no tuviera empresa,
        // en vez de romperse da "vacío", y eso cuenta como "no activa".
        if (!$usuario->activo || !$usuario->empresa?->activo) {
            // Volvemos al formulario con este mensaje y conservando el correo escrito.
            return back()->withErrors([
                'correo_electronico' => 'El usuario o la empresa están desactivados.',
            ])->onlyInput('correo_electronico');
        }

        // ── PASO 5: si ha llegado hasta aquí, todo es correcto. Iniciar sesión ──

        // Auth::login inicia la sesión de ese usuario.
        // A partir de ahora la aplicación "recuerda" quién es en cada página.
        // Si $recordar es true, la sesión se mantiene aunque cierre el navegador.
        Auth::login($usuario, $recordar);

        // Crea un identificador de sesión nuevo. Es una medida de seguridad:
        // si alguien había conseguido el identificador anterior, ya no le sirve.
        $request->session()->regenerate();

        // Guarda la fecha y hora de este acceso en la base de datos:
        // ->forceFill([...]) → pone en la columna fecha_ultimo_acceso el valor now()
        //                      (now() = la fecha y la hora de este momento).
        // ->save() → guarda el cambio en la tabla usuarios.
        $usuario->forceFill(['fecha_ultimo_acceso' => now()])->save();

        // Manda al usuario a la página que corresponda:
        // redirect()->intended(...) → si antes había intentado entrar en una página
        // privada (por ejemplo /configuracion) y el portero le mandó al login,
        // ahora le lleva a esa página que quería.
        // Si no, le lleva a la ruta 'dashboard' (el panel de inicio).
        return redirect()->intended(route('dashboard'));
    }


    // ═════════════════════════════════════════════════════════
    // MÉTODO 3: SALIR (cerrar sesión)
    // Se ejecuta cuando alguien pulsa "Cerrar sesión" (ruta POST /logout).
    // ═════════════════════════════════════════════════════════
    public function salir(Request $request): RedirectResponse
    {
        // Cierra la sesión: la aplicación deja de saber quién es este usuario.
        Auth::logout();

        // Borra todos los datos que había guardados en la sesión.
        $request->session()->invalidate();

        // Crea un nuevo código de seguridad para los formularios (el de @csrf),
        // para que el antiguo no se pueda volver a usar.
        $request->session()->regenerateToken();

        // Por último, lo manda a la ruta llamada 'login' (la pantalla de entrada).
        return redirect()->route('login');
    }
} // Aquí se cierra la clase AccesoController.