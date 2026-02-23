@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-md-8 offset-md-2">
    <h1>Create Restaurant</h1>

    <form method="POST" action="{{ route('restaurants.store') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Slug (optional)</label>
        <input name="slug" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"></textarea>
      </div>
      <button class="btn btn-primary">Create</button>
    </form>
  </div>
</div>
@endsection
