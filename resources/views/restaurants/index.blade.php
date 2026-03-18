@extends('layouts.app')

@section('content')
<div x-data="{ createModalOpen: false }" x-on:keydown.escape.window="createModalOpen = false">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Restaurants</h1>
    <button type="button" class="btn btn-primary" x-on:click="createModalOpen = true">Create Restaurant</button>
  </div>

  <div class="card">
    <div class="card-body">
      @if($restaurants->count())
        <table class="table table-striped">
          <thead><tr><th>ID</th><th>Name</th><th>City</th><th>Active</th><th>Created</th><th>Actions</th></tr></thead>
          <tbody>
          @foreach($restaurants as $r)
            <tr>
              <td>{{ $r->id }}</td>
              <td>{{ $r->name }}</td>
              <td>{{ $r->city }}</td>
              <td>{{ $r->is_active ? 'Yes' : 'No' }}</td>
              <td>{{ $r->created_at->toDateString() }}</td>
              <td>
                <form action="{{ route('restaurants.destroy', $r) }}" method="POST" class="js-delete-form" data-confirm-message="Delete this restaurant?">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </td>
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

  <div x-cloak x-show="createModalOpen" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50" x-on:click="createModalOpen = false"></div>
    <div class="relative z-10 h-[85vh] w-full max-w-4xl overflow-hidden rounded-xl bg-white shadow-2xl">
      <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
        <h2 class="text-lg font-semibold text-slate-900">Create Restaurant</h2>
        <button type="button" class="rounded-md border border-slate-200 px-3 py-1.5 text-sm text-slate-600" x-on:click="createModalOpen = false">Close</button>
      </div>
      <iframe src="{{ route('restaurants.create', ['embed' => 1]) }}" class="h-[calc(85vh-56px)] w-full border-0" loading="lazy"></iframe>
    </div>
  </div>
</div>
@endsection
