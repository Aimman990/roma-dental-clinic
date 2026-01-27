@extends('layouts.app')

@section('content')
<div class="p-6 bg-white rounded">
    <h2 class="text-xl font-bold">Financial Summary</h2>
    <table class="w-full text-sm mt-4">
        <tr><td class="font-semibold">Income</td><td>{{ $data['income'] ?? 0 }}</td></tr>
        @if(isset($data['expenses']))
            @foreach($data['expenses'] as $k => $v)
                <tr><td class="font-semibold">{{ $k }}</td><td>{{ is_array($v) ? json_encode($v) : $v }}</td></tr>
            @endforeach
        @endif
        <tr><td class="font-semibold">Net Profit</td><td>{{ $data['net_profit'] ?? 0 }}</td></tr>
    </table>
</div>
@endsection
