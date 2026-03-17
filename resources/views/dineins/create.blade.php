@extends('layouts.app')

@section('content')
<div class="mb-3">
  <h1>Create Dine-In Order</h1>
</div>

<div class="card">
  <div class="card-body">
    <form action="{{ route('dineins.store') }}" method="POST">
      @csrf

      <div class="mb-3">
        <label for="restaurant_id" class="form-label">Restaurant <span class="text-danger">*</span></label>
        <select name="restaurant_id" id="restaurant_id" class="form-control @error('restaurant_id') is-invalid @enderror" required>
          <option value="">-- Select Restaurant --</option>
          @foreach($restaurants as $r)
            <option value="{{ $r->id }}" @selected(old('restaurant_id') == $r->id)>{{ $r->name }}</option>
          @endforeach
        </select>
        @error('restaurant_id')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-3">
        <label for="customer_id" class="form-label">Customer</label>
        <select name="customer_id" id="customer_id" class="form-control @error('customer_id') is-invalid @enderror">
          <option value="">-- Select Customer --</option>
          @foreach($customers as $c)
            <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->first_name }} {{ $c->last_name }}</option>
          @endforeach
        </select>
        @error('customer_id')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-3">
        <label for="menu_id" class="form-label">Menu</label>
        <select name="menu_id" id="menu_id" class="form-control @error('menu_id') is-invalid @enderror">
          <option value="">-- Select Menu --</option>
          @foreach($menus as $m)
            <option value="{{ $m->id }}" @selected(old('menu_id') == $m->id)>{{ $m->name }}</option>
          @endforeach
        </select>
        @error('menu_id')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-3">
        <label for="food_name" class="form-label">Food Name</label>
        <input type="text" name="food_name" id="food_name" class="form-control @error('food_name') is-invalid @enderror" value="{{ old('food_name') }}" placeholder="e.g., Chicken Burger">
        @error('food_name')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-3">
        <label for="quantity" class="form-label">Quantity</label>
        <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}" min="1" max="10">
        @error('quantity')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-3">
        <label for="table_number" class="form-label">Table Number</label>
        <input type="text" name="table_number" id="table_number" class="form-control @error('table_number') is-invalid @enderror" value="{{ old('table_number') }}" placeholder="e.g., Table 5">
        @error('table_number')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-3">
        <label for="guest_count" class="form-label">Guest Count</label>
        <input type="number" name="guest_count" id="guest_count" class="form-control @error('guest_count') is-invalid @enderror" value="{{ old('guest_count') }}" min="1">
        @error('guest_count')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-3">
        <label for="total_amount" class="form-label">Total Amount (£)</label>
        <input type="number" name="total_amount" id="total_amount" class="form-control @error('total_amount') is-invalid @enderror" value="{{ old('total_amount') }}" step="0.01" min="0">
        @error('total_amount')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success">Create Dine-In Order</button>
        <a href="{{ route('dineins.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
