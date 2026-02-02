@extends('layouts.app')

@section('content')
<h1>Orders</h1>
<form method="get" style="margin-bottom:16px">
    <select name="status">
        <option value="">Status (All)</option>
        @foreach($statuses as $status)
            <option value="{{ $status->value }}" @selected(request('status') == $status->value)>{{ $status->name }}</option>
        @endforeach
    </select>
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Order ID or User Email">
    <button type="submit">Filter</button>
</form>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Order ID</th>
        <th>User Email</th>
        <th>Status</th>
        <th>Ticket Count</th>
        <th>Created At</th>
        <th>İşlem</th>
    </tr>
    @foreach($orders as $order)
    <tr>
        <td>{{ $order->id }}</td>
        <td>{{ $order->user->email ?? '-' }}</td>
        <td>{{ $order->status->name ?? $order->status }}</td>
        <td>{{ $order->tickets_count }}</td>
        <td>{{ $order->created_at }}</td>
        <td><a href="{{ route('admin.orders.show', $order) }}">Show</a></td>
    </tr>
    @endforeach
</table>
{{ $orders->links() }}
@endsection
