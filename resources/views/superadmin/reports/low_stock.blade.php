@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Low Stock Report</h2>
  <div class="mb-3"><a class="btn btn-outline-secondary" href="?export=1">Export CSV</a></div>
  <div class="table-responsive">
    <table class="table table-sm">
      <thead><tr><th>Item</th><th>Stock</th><th>UOM</th><th>Reorder Level</th></tr></thead>
      <tbody>
        @foreach($items as $i)
        <tr>
          <td>{{ $i->name }}</td>
          <td>{{ $i->current_stock }}</td>
          <td>{{ $i->unit_of_measure }}</td>
          <td>{{ $i->reorder_level }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection