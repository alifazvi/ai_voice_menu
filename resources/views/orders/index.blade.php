@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1>Orders</h1>
  <a href="{{ route('orders.create') }}" class="btn btn-primary">Create Order</a>
 </div>

 <div class="card">
   <div class="card-body">
     @if($orders->count())
       <table class="table table-striped">
         <thead><tr><th>ID</th><th>Restaurant</th><th>Customer</th><th>Menu</th><th>Items</th><th>Total</th><th>Table</th><th>Guests</th><th>Status</th></tr></thead>
         <tbody>
         @foreach($orders as $o)
           <tr>
             <td>{{ $o->id }}</td>
             <td>{{ optional($o->restaurant)->name }}</td>
             <td>{{ optional($o->customer)->email }}</td>
             <td>{{ optional($o->menu)->name }}</td>
             <td>
               @if(!empty($o->items) && is_array($o->items))
                 <small>{{ count($o->items) }} item(s)</small>
                 <ul class="mb-0 small">
                   @foreach($o->items as $it)
                     <li>{{ $it['name'] ?? 'item' }} x{{ $it['qty'] ?? 1 }} {{ $it['size'] ?? '' }}</li>
                   @endforeach
                 </ul>
               @elseif(!empty($o->food_name))
                 <div>{{ $o->food_name }} x{{ $o->quantity }}</div>
               @endif
             </td>
             <td>{{ $o->total_amount }}</td>
             <td>{{ $o->table_number }}</td>
             <td>{{ $o->guest_count }}</td>
             <td>{{ $o->status }}</td>
           </tr>
         @endforeach
         </tbody>
       </table>
       {{ $orders->links() }}
     @else
       <p class="mb-0">No orders yet.</p>
     @endif
   </div>
 </div>
@endsection
