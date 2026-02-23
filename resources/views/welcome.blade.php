@extends('layouts.app')

@section('content')
<div class="py-5 text-center">
	<h1>Welcome</h1>
	<p class="lead">Quick links</p>
	<div class="d-flex justify-content-center gap-2">
		<a class="btn btn-lg btn-outline-primary" href="/restaurants">Restaurants</a>
		<a class="btn btn-lg btn-outline-primary" href="/menus">Menus</a>
		<a class="btn btn-lg btn-outline-primary" href="/customers">Customers</a>
		<a class="btn btn-lg btn-outline-primary" href="/orders">Orders</a>
	</div>
</div>
@endsection
