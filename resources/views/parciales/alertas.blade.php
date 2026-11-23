{{-- Mensaje verde si el controlador mandó un aviso de éxito. --}}
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- Mensaje amarillo de aviso. --}}
@if(session('warning'))
<div class="alert alert-warning">{{ session('warning') }}</div>
@endif

{{--
Mensaje rojo con la lista de errores de validación.
@if($errors->any())
    <div class="alert alert-danger">
        <strong>Revisa los datos:</strong>
        <ul style="margin:8px 0 0; padding-left:20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif
--}}