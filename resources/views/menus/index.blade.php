@extends('layouts.app')

@section('content')
<div x-data="{ createModalOpen: false }" x-on:keydown.escape.window="createModalOpen = false">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Menus</h1>
    <button type="button" class="btn btn-primary" x-on:click="createModalOpen = true">Create Menu</button>
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
                <form action="{{ route('menus.destroy', $m) }}" method="POST" class="js-delete-form" data-confirm-message="Delete this menu?">
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

  <div x-cloak x-show="createModalOpen" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50" x-on:click="createModalOpen = false"></div>
    <div class="relative z-10 h-[88vh] w-full max-w-5xl overflow-hidden rounded-xl bg-white shadow-2xl">
      <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
        <h2 class="text-lg font-semibold text-slate-900">Create Menu</h2>
        <button type="button" class="rounded-md border border-slate-200 px-3 py-1.5 text-sm text-slate-600" x-on:click="createModalOpen = false">Close</button>
      </div>
      <iframe src="{{ route('menus.create', ['embed' => 1]) }}" class="h-[calc(88vh-56px)] w-full border-0" loading="lazy"></iframe>
    </div>
  </div>
</div>
@endsection
