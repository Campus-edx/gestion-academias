 @extends('plantillas.principal')

 @section('title', 'Grupos del centro')
 @section('topbar', 'Centros')

 @section('content')

 <div class="page-heading">
     <div>
         <h1>Grupos de {{ $centro->nombre }}</h1>
         <p>Listado de grupos asociados a este centro.</p>
     </div>

     <a class="btn btn-secondary"
         href="{{ route('contactos.centros.show', $centro->id) }}">
         Volver al centro
     </a>
 </div>

 <div class="card">
     <div class="table-wrap">
         <table>
             <thead>
                 <tr>
                     <th>Grupo</th>
                     <th>Temporada</th>
                     <th>Curso</th>
                     <th>Fecha de inicio</th>
                     <th>Fecha de finalización</th>
                     <th>Capacidad</th>
                     <th>Estado</th>
                     <th></th>
                 </tr>
             </thead>

             <tbody>
                 @forelse($grupos as $grupo)
                 <tr>
                     <td>{{ $grupo->nombre }}</td>
                     <td>{{ $grupo->temporada_nombre }}</td>
                     <td>{{ $grupo->curso_nombre }}</td>
                     <td>{{ $grupo->fecha_inicio ?? '—' }}</td>
                     <td>{{ $grupo->fecha_fin ?? '—' }}</td>
                     <td>{{ $grupo->capacidad_maxima }}</td>
                     <td>{{ ucfirst($grupo->estado) }}</td>
                     <td class="actions">
                         <a class="btn btn-secondary btn-sm"
                             href="{{ route('grupos.show', $grupo->id) }}">
                             Ver ficha
                         </a>
                     </td>
                 </tr>
                 @empty
                 <tr>
                     <td colspan="8" class="empty-state">
                         Este centro todavía no tiene grupos asociados.
                     </td>
                 </tr>
                 @endforelse
             </tbody>
         </table>
     </div>
 </div>

 @endsection