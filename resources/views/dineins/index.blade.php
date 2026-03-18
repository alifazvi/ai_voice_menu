@extends('layouts.app')

@section('content')
<div x-data="{ createModalOpen: false }" x-on:keydown.escape.window="createModalOpen = false">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Dine-In Orders</h1>
    <button type="button" class="btn btn-primary" x-on:click="createModalOpen = true">Create Dine-In Order</button>
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
                <form action="{{ route('dineins.destroy', $d) }}" method="POST" class="js-delete-form" data-confirm-message="Delete this dine-in booking?">
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

  <div x-cloak x-show="createModalOpen" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50" x-on:click="createModalOpen = false"></div>
    <div class="relative z-10 h-[90vh] w-full max-w-6xl overflow-hidden rounded-xl bg-white shadow-2xl">
      <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
        <h2 class="text-lg font-semibold text-slate-900">Create Dine-In Order</h2>
        <button type="button" class="rounded-md border border-slate-200 px-3 py-1.5 text-sm text-slate-600" x-on:click="createModalOpen = false">Close</button>
      </div>
      <iframe src="{{ route('dineins.create', ['embed' => 1]) }}" class="h-[calc(90vh-56px)] w-full border-0" loading="lazy"></iframe>
    </div>
  </div>
</div>
@endsection
