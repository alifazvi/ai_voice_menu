@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1>Menus</h1>
  <a href="{{ route('menus.create') }}" class="btn btn-primary">Create Menu</a>
 </div>

 <div class="card">
   <div class="card-body">
     @if($menus->count())
       <table class="table table-striped">
         <thead><tr><th>ID</th><th>Name</th><th>Restaurant</th><th>Price Range</th><th>Created</th><th>Attachments</th><th>Actions</th></tr></thead>
         <tbody>
         @foreach($menus as $m)
           <tr>
             <td>{{ $m->id }}</td>
             <td>{{ $m->name }}</td>
             <td>{{ optional($m->restaurant)->name }}</td>
            <td>{{ $m->min_price }} - {{ $m->max_price }}</td>
             <td>{{ $m->created_at->toDateString() }}</td>
            <td>
              @if(!empty($m->attachments) && is_array($m->attachments))
                @foreach($m->attachments as $a)
                  <div><a href="{{ $a['url'] ?? '#' }}" target="_blank">{{ $a['name'] ?? 'file' }}</a></div>
                @endforeach
              @endif
            </td>
            <td>
              <form action="{{ route('menus.destroy', $m) }}" method="POST" onsubmit="return confirm('Delete this menu?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
           </tr>
         @endforeach
         </tbody>
       </table>
       {{ $menus->links() }}
     @else
       <p class="mb-0">No menus yet.</p>
     @endif
   </div>
 </div>
@endsection
