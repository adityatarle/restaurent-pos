@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Inventory Valuation</h2>
  <div class="mb-3"><a class="btn btn-outline-secondary" href="?export=1">Export CSV</a></div>
  <div class="table-responsive">
    <table class="table table-sm">
      <thead><tr><th>Item</th><th>Stock</th><th>Avg Cost</th><th>Value</th></tr></thead>
      <tbody>
        @foreach($items as $row)
        <tr>
          <td>{{ $row['item']->name }}</td>
          <td>{{ $row['item']->current_stock }}</td>
          <td>${{ number_format($row['item']->average_cost_price,2) }}</td>
          <td>${{ number_format($row['value'],2) }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr><th colspan="3" class="text-end">Total</th><th>${{ number_format($totalValue,2) }}</th></tr>
      </tfoot>
    </table>
  </div>
</div>
@endsection