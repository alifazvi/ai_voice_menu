@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1>Dine-In Orders</h1>
  <a href="{{ route('dineins.create') }}" class="btn btn-primary">Create Dine-In Order</a>
 </div>

 <div class="card">
   <div class="card-body">
     @if($dineins->count())
       <table class="table table-striped">
         <thead><tr><th>ID</th><th>Customer</th><th>Table</th><th>Seats</th><th>Location</th><th>Booking Date</th><th>Booking Slot</th><th>Special Request</th><th>Available</th><th>Actions</th></tr></thead>
         <tbody>
         @foreach($dineins as $d)
           <tr>
             <td>{{ $d->id }}</td>
             <td>
               @if($d->customer)
                 {{ $d->customer->first_name }} {{ $d->customer->last_name }}
               @else
                 N/A
               @endif
             </td>
             <td>{{ $d->table_number ?? '-' }}</td>
             <td>{{ $d->seats ?? '-' }}</td>
             <td>{{ $d->location ?? '-' }}</td>
             <td>{{ optional($d->booking_date)->toDateString() ?? '-' }}</td>
             <td>{{ $d->booking_slot ?? '-' }}</td>
             <td>{{ $d->special_request ?? '-' }}</td>
             <td>{{ $d->is_available ? 'Yes' : 'No' }}</td>
             <td>
               <form action="{{ route('dineins.destroy', $d) }}" method="POST" onsubmit="return confirm('Delete this dine-in booking?')">
                 @csrf
                 @method('DELETE')
                 <button type="submit" class="btn btn-danger btn-sm">Delete</button>
               </form>
             </td>
           </tr>
         @endforeach
         </tbody>
       </table>
       {{ $dineins->links() }}
     @else
       <p class="mb-0">No dine-in orders yet.</p>
     @endif
   </div>
 </div>
@endsection
