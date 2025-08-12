@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Sales by Waiter</h2>
  <form class="row g-3 mb-3" method="get">
    <div class="col-auto"><label class="form-label">From</label><input type="date" name="start_date" class="form-control" value="{{ $start->toDateString() }}"></div>
    <div class="col-auto"><label class="form-label">To</label><input type="date" name="end_date" class="form-control" value="{{ $end->toDateString() }}"></div>
    <div class="col-auto align-self-end"><button class="btn btn-primary">Apply</button></div>
    <div class="col-auto align-self-end"><a class="btn btn-outline-secondary" href="?start_date={{ $start->toDateString() }}&end_date={{ $end->toDateString() }}&export=1">Export CSV</a></div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm">
      <thead><tr><th>Waiter</th><th>Orders</th><th>Revenue</th><th>Avg Order</th></tr></thead>
      <tbody>
        @foreach($rows as $row)
        <tr>
          <td>{{ $row->waiter_name }}</td>
          <td>{{ $row->orders_count }}</td>
          <td>${{ number_format($row->revenue,2) }}</td>
          <td>${{ number_format($row->orders_count ? $row->revenue / $row->orders_count : 0, 2) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection