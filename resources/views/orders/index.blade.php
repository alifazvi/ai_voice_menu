@extends('layouts.app')

@section('content')
<div x-data="{ createModalOpen: false }" x-on:keydown.escape.window="createModalOpen = false">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Orders</h1>
    <button type="button" class="btn btn-primary" x-on:click="createModalOpen = true">Create Order</button>
  </div>

  <div class="card">
    <div class="card-body">
      @if($orders->count())
        <table class="table table-striped">
          <thead><tr><th>ID</th><th>Restaurant</th><th>Customer</th><th>Menu</th><th>Items</th><th>Total</th><th>Table</th><th>Guests</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          @foreach($orders as $o)
            <tr>
              <td>{{ $o->id }}</td>
              <td>{{ optional($o->restaurant)->name }}</td>
              <td>
                @if($o->customer)
                  {{ $o->customer->first_name }} {{ $o->customer->last_name }}
                @else
                  N/A
                @endif
              </td>
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
              <td>£{{ number_format($o->total_amount, 2) }}</td>
              <td>{{ $o->table_number }}</td>
              <td>{{ $o->guest_count }}</td>
              <td>
                <form action="{{ route('orders.update-status', $o) }}" method="POST" class="d-flex gap-2 align-items-center order-status-form">
                  @csrf
                  @method('PATCH')
                  <select name="status" class="form-select form-select-sm order-status-select" data-initial-status="{{ $o->status }}" style="min-width: 130px;">
                    <option value="pending" @selected($o->status === 'pending')>Pending</option>
                    <option value="placed" @selected($o->status === 'placed')>Placed</option>
                    <option value="confirmed" @selected($o->status === 'confirmed')>Confirmed</option>
                    <option value="preparing" @selected($o->status === 'preparing')>Preparing</option>
                    <option value="out_for_delivery" @selected($o->status === 'out_for_delivery')>Out for delivery</option>
                    <option value="delivered" @selected($o->status === 'delivered')>Delivered</option>
                    <option value="completed" @selected($o->status === 'completed')>Completed</option>
                    <option value="cancelled" @selected($o->status === 'cancelled')>Cancelled</option>
                    <option value="booked" @selected($o->status === 'booked')>Booked</option>
                  </select>
                  <button type="submit" class="btn btn-sm btn-outline-primary order-save-btn" style="display:none;">Save</button>
                </form>
              </td>
              <td>
                <form action="{{ route('orders.destroy', $o) }}" method="POST" class="js-delete-form" data-confirm-message="Delete this order?">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </td>
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

  <div x-cloak x-show="createModalOpen" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50" x-on:click="createModalOpen = false"></div>
    <div class="relative z-10 h-[90vh] w-full max-w-6xl overflow-hidden rounded-xl bg-white shadow-2xl">
      <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
        <h2 class="text-lg font-semibold text-slate-900">Create Order</h2>
        <button type="button" class="rounded-md border border-slate-200 px-3 py-1.5 text-sm text-slate-600" x-on:click="createModalOpen = false">Close</button>
      </div>
      <iframe src="{{ route('orders.create', ['embed' => 1]) }}" class="h-[calc(90vh-56px)] w-full border-0" loading="lazy"></iframe>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.order-status-form').forEach(function (form) {
      const select = form.querySelector('.order-status-select');
      const saveBtn = form.querySelector('.order-save-btn');
      if (!select || !saveBtn) return;

      const initialStatus = select.dataset.initialStatus ?? '';

      const toggleSaveButton = function () {
        saveBtn.style.display = select.value === initialStatus ? 'none' : 'inline-flex';
      };

      select.addEventListener('change', toggleSaveButton);
      toggleSaveButton();
    });
  });
</script>
@endpush
