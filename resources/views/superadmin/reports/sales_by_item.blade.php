@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Sales by Item</h2>
  <form class="row g-3 mb-3" method="get">
    <div class="col-auto"><label class="form-label">From</label><input type="date" name="start_date" class="form-control" value="{{ $start->toDateString() }}"></div>
    <div class="col-auto"><label class="form-label">To</label><input type="date" name="end_date" class="form-control" value="{{ $end->toDateString() }}"></div>
    <div class="col-auto align-self-end"><button class="btn btn-primary">Apply</button></div>
    <div class="col-auto align-self-end"><a class="btn btn-outline-secondary" href="?start_date={{ $start->toDateString() }}&end_date={{ $end->toDateString() }}&export=1">Export CSV</a></div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm">
      <thead><tr><th>Item</th><th>Category</th><th>Qty</th><th>Revenue</th></tr></thead>
      <tbody>
        @foreach($items as $row)
        <tr>
          <td>{{ $row->menuItem->name ?? ('#'.$row->menu_item_id) }}</td>
          <td>{{ $row->menuItem->category->name ?? '' }}</td>
          <td>{{ $row->qty }}</td>
          <td>${{ number_format($row->revenue,2) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection