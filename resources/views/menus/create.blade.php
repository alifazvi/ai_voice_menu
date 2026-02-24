@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-md-8 offset-md-2">
    <h1>Create Menu</h1>

    <form method="POST" action="{{ route('menus.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="mb-3">
        <label class="form-label">Restaurant</label>
        <select name="restaurant_id" class="form-select" required>
          <option value="">Select</option>
          @foreach($restaurants as $r)
            <option value="{{ $r->id }}">{{ $r->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"></textarea>
      </div>
      <!-- Menu items and price details are not stored on this table; attach files instead -->
      <div class="mb-3">
        <label class="form-label">Attachments (files) — allowed: pdf,csv,json,txt,jpeg,png,gif. You can upload multiple files.</label>
        <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.csv,.json,.txt,image/*">
        <div class="form-text">Uploaded files will be stored and listed with URL.</div>
      </div>
      <button class="btn btn-primary">Create</button>
    </form>
  </div>
</div>
@endsection
