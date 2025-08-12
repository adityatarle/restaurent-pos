@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Expenses</h1>
        <a href="{{ route('superadmin.expenses.create') }}" class="btn btn-primary">Add New Expense</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filtering Form --}}
    <div class="card mb-4">
        <div class="card-header">Filter Expenses</div>
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.expenses.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="month_year" class="form-label">Month/Year:</label>
                        <input type="month" class="form-control" id="month_year" name="month_year" value="{{ request('month_year') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="category_id" class="form-label">Category:</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">-- All Categories --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('superadmin.expenses.index') }}" class="btn btn-secondary w-100">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            @if($expenses->count() > 0)
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                        <th>Vendor</th>
                        <th>Recorded By</th>
                        <th>Receipt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                    <tr>
                        <td>{{ $expense->id }}</td>
                        <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                        <td>{{ $expense->category->name }}</td>
                        <td>{{ Str::limit($expense->description, 50) }}</td>
                        <td class="text-end">${{ number_format($expense->amount, 2) }}</td>
                        <td>{{ $expense->vendor_name ?: '-' }}</td>
                        <td>{{ $expense->user->name ?? 'N/A' }}</td>
                        <td>
                            @if($expense->receipt_url)
                                <a href="{{ Storage::url($expense->receipt_url) }}" target="_blank" class="btn btn-sm btn-outline-info" title="View Receipt">
                                    <i class="bi bi-receipt"></i>
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('superadmin.expenses.edit', $expense->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <form action="{{ route('superadmin.expenses.destroy', $expense->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this expense record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3">
                {{-- Append existing query parameters to pagination links --}}
                {{ $expenses->appends(request()->query())->links() }}
            </div>
            @else
            <div class="alert alert-info">No expenses found matching your criteria.</div>
            @endif
        </div>
    </div>
</div>
@endsection