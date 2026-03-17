@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1>Customers</h1>
  <a href="{{ route('customers.create') }}" class="btn btn-primary">Create Customer</a>
 </div>

 <div class="card">
   <div class="card-body">
     @if($customers->count())
       <table class="table table-striped">
         <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Created</th><th>Actions</th></tr></thead>
         <tbody>
         @foreach($customers as $c)
           <tr>
             <td>{{ $c->id }}</td>
             <td>{{ trim($c->first_name . ' ' . $c->last_name) }}</td>
             <td>{{ $c->email }}</td>
             <td>{{ $c->phone }}</td>
             <td>{{ $c->created_at->toDateString() }}</td>
             <td>
               <form action="{{ route('customers.destroy', $c) }}" method="POST" onsubmit="return confirm('Delete this customer?')">
                 @csrf
                 @method('DELETE')
                 <button type="submit" class="btn btn-danger btn-sm">Delete</button>
               </form>
             </td>
           </tr>
         @endforeach
         </tbody>
       </table>
       {{ $customers->links() }}
     @else
       <p class="mb-0">No customers yet.</p>
     @endif
   </div>
 </div>
@endsection
