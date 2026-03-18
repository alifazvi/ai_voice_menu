@extends('layouts.app')

@section('content')
<div x-data="{ createModalOpen: false }" x-on:keydown.escape.window="createModalOpen = false">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Customers</h1>
    <button type="button" class="btn btn-primary" x-on:click="createModalOpen = true">Create Customer</button>
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
                <form action="{{ route('customers.destroy', $c) }}" method="POST" class="js-delete-form" data-confirm-message="Delete this customer?">
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

  <div x-cloak x-show="createModalOpen" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50" x-on:click="createModalOpen = false"></div>
    <div class="relative z-10 h-[85vh] w-full max-w-4xl overflow-hidden rounded-xl bg-white shadow-2xl">
      <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
        <h2 class="text-lg font-semibold text-slate-900">Create Customer</h2>
        <button type="button" class="rounded-md border border-slate-200 px-3 py-1.5 text-sm text-slate-600" x-on:click="createModalOpen = false">Close</button>
      </div>
      <iframe src="{{ route('customers.create', ['embed' => 1]) }}" class="h-[calc(85vh-56px)] w-full border-0" loading="lazy"></iframe>
    </div>
  </div>
</div>
@endsection
