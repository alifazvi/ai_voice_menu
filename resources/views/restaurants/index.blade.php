@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1>Restaurants</h1>
  <a href="{{ route('restaurants.create') }}" class="btn btn-primary">Create Restaurant</a>
 </div>

 <div class="card">
   <div class="card-body">
     @if($restaurants->count())
       <table class="table table-striped">
         <thead><tr><th>ID</th><th>Name</th><th>City</th><th>Active</th><th>Created</th></tr></thead>
         <tbody>
         @foreach($restaurants as $r)
           <tr>
             <td>{{ $r->id }}</td>
             <td>{{ $r->name }}</td>
             <td>{{ $r->city }}</td>
             <td>{{ $r->is_active ? 'Yes' : 'No' }}</td>
             <td>{{ $r->created_at->toDateString() }}</td>
           </tr>
         @endforeach
         </tbody>
       </table>
       {{ $restaurants->links() }}
     @else
       <p class="mb-0">No restaurants yet.</p>
     @endif
   </div>
 </div>
@endsection
