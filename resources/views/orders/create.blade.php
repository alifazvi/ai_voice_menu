@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-md-8 offset-md-2">
    <h1>Create Order</h1>

    <form method="POST" action="{{ route('orders.store') }}">
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
        <label class="form-label">Customer (optional)</label>
        <select name="customer_id" class="form-select">
          <option value="">None</option>
          @foreach($customers as $c)
            <option value="{{ $c->id }}">{{ trim($c->first_name.' '.$c->last_name) }} ({{ $c->email }})</option>
          @endforeach
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Menu (optional)</label>
        <select name="menu_id" class="form-select">
          <option value="">None</option>
          @foreach($menus as $m)
            <option value="{{ $m->id }}">{{ $m->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Items</label>
        <div class="card p-2 mb-2">
          <table class="table table-sm" id="items-table">
            <thead>
              <tr>
                <th>Food name</th>
                <th style="width:80px">Qty</th>
                <th style="width:120px">Size</th>
                <th style="width:120px">Price</th>
                <th>Notes</th>
                <th style="width:60px"></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
          <button type="button" id="add-item" class="btn btn-outline-secondary btn-sm">Add Item</button>
        </div>
        <input type="hidden" name="items" id="items-json">
      </div>

      <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Total amount</label><input name="total_amount" id="total_amount" class="form-control"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Subtotal</label><input name="subtotal" id="subtotal" class="form-control"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Tax</label><input name="tax" id="tax" class="form-control"></div>
      </div>
      <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Discount</label><input name="discount" id="discount" class="form-control"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Delivery fee</label><input name="delivery_fee" id="delivery_fee" class="form-control"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Status</label><input name="status" class="form-control" value="pending"></div>
      </div>

      <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Table number</label><input name="table_number" class="form-control"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Guest count</label><input name="guest_count" type="number" min="1" class="form-control"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Size (if single item)</label><input name="size" class="form-control"></div>
      </div>

      <div class="mb-3">
        <label class="form-label">Delivery address</label>
        <textarea name="delivery_address" class="form-control" rows="2"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Instructions</label>
        <textarea name="instructions" class="form-control" rows="2"></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Booked at</label>
        <input name="booked_at" type="datetime-local" class="form-control">
      </div>
      <button class="btn btn-primary">Create</button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function(){
    const addBtn = document.getElementById('add-item');
    const tbody = document.querySelector('#items-table tbody');
    const form = document.querySelector('form');
    const itemsJson = document.getElementById('items-json');
    const totalInput = document.getElementById('total_amount');
    const subtotalInput = document.getElementById('subtotal');
    const taxInput = document.getElementById('tax');
    const discountInput = document.getElementById('discount');
    const deliveryFeeInput = document.getElementById('delivery_fee');

    function createRow(item={name:'', qty:1, size:'', price:'', notes:''}){
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><input name="_name" class="form-control form-control-sm" value="${item.name}"></td>
        <td><input type="number" min="1" name="_qty" class="form-control form-control-sm" value="${item.qty}"></td>
        <td><input name="_size" class="form-control form-control-sm" value="${item.size}"></td>
        <td><input type="number" step="0.01" min="0" name="_price" class="form-control form-control-sm" value="${item.price}"></td>
        <td><input name="_notes" class="form-control form-control-sm" value="${item.notes}"></td>
        <td><button type="button" class="btn btn-sm btn-danger remove-item">×</button></td>
      `;
      tbody.appendChild(tr);
    }

    addBtn.addEventListener('click', ()=> createRow());

    tbody.addEventListener('click', function(e){
      if (e.target.classList.contains('remove-item')) {
        e.target.closest('tr').remove();
        computeTotal();
      }
    });

    tbody.addEventListener('input', computeTotal);

    function computeTotal(){
      let total = 0;
      tbody.querySelectorAll('tr').forEach(tr=>{
        const price = parseFloat(tr.querySelector('input[name="_price"]').value) || 0;
        const qty = parseInt(tr.querySelector('input[name="_qty"]').value) || 0;
        total += price * qty;
      });
      totalInput.value = total.toFixed(2);
      // default subtotal = total unless user changes it
      if (!subtotalInput.value) {
        subtotalInput.value = total.toFixed(2);
      }
    }

    form.addEventListener('submit', function(e){
      const items = [];
      tbody.querySelectorAll('tr').forEach(tr=>{
        const name = tr.querySelector('input[name="_name"]').value.trim();
        if (!name) return;
        const qty = parseInt(tr.querySelector('input[name="_qty"]').value) || 1;
        const size = tr.querySelector('input[name="_size"]').value.trim();
        const price = parseFloat(tr.querySelector('input[name="_price"]').value) || 0;
        const notes = tr.querySelector('input[name="_notes"]').value.trim();
        items.push({name, qty, size, price, notes});
      });
      itemsJson.value = JSON.stringify(items);

      // If subtotal empty, set from computed total
      if (!subtotalInput.value) {
        subtotalInput.value = totalInput.value;
      }
      // ensure total amount is computed if empty
      if (!totalInput.value) {
        computeTotal();
      }
    });
  })();
</script>
@endpush
