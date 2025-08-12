@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Sales Summary</h2>
  <form class="row g-3 mb-3" method="get">
    <div class="col-auto"><label class="form-label">From</label><input type="date" name="start_date" class="form-control" value="{{ $start->toDateString() }}"></div>
    <div class="col-auto"><label class="form-label">To</label><input type="date" name="end_date" class="form-control" value="{{ $end->toDateString() }}"></div>
    <div class="col-auto align-self-end"><button class="btn btn-primary">Apply</button></div>
    <div class="col-auto align-self-end"><a class="btn btn-outline-secondary" href="?start_date={{ $start->toDateString() }}&end_date={{ $end->toDateString() }}&export=1">Export CSV</a></div>
  </form>

  <div class="row mb-4">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Total Revenue</div><div class="h4">${{ number_format($totalRevenue,2) }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Orders</div><div class="h4">{{ $orderCount }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Avg Order</div><div class="h4">${{ number_format($avgOrderValue,2) }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Cancelled Orders</div><div class="h4">{{ $cancelledCount }}</div></div></div></div>
  </div>

  <h5>Top Items</h5>
  <div class="table-responsive">
    <table class="table table-sm">
      <thead><tr><th>Item</th><th>Qty</th><th>Revenue</th></tr></thead>
      <tbody>
        @foreach($topItems as $row)
        <tr>
          <td>{{ $row->menuItem->name ?? ('#'.$row->menu_item_id) }}</td>
          <td>{{ $row->qty }}</td>
          <td>${{ number_format($row->revenue,2) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection