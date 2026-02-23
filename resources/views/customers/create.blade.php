@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-md-6 offset-md-3">
    <h1>Create Customer</h1>

    <form method="POST" action="{{ route('customers.store') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">First name</label>
        <input name="first_name" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Last name</label>
        <input name="last_name" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input name="phone" class="form-control">
      </div>
      <button class="btn btn-primary">Create</button>
    </form>
  </div>
</div>
@endsection
