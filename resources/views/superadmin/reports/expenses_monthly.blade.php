@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Monthly Expense Report</h1>

    <form method="GET" action="{{ route('superadmin.reports.expenses') }}" class="row g-3 align-items-end mb-4">
        <div class="col-md-4">
            <label for="month_year" class="form-label">Select Month:</label>
            <input type="month" class="form-control" id="month_year" name="month_year" value="{{ $selectedMonthYear }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Generate Report</button>
        </div>
    </form>

    @if($expensesByCategory->isNotEmpty())
        <h3>Expenses for {{ \Carbon\Carbon::parse($selectedMonthYear.'-01')->format('F Y') }}</h3>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Expense Category</th>
                    <th class="text-end">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesByCategory as $expense)
                <tr>
                    <td>{{ $expense->category_name }}</td>
                    <td class="text-end">${{ number_format($expense->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td>Total All Expenses</td>
                    <td class="text-end">${{ number_format($totalExpenses, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="alert alert-info">No expenses found for the selected month.</div>
    @endif
</div>
@endsection