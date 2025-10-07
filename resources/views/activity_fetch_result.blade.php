@extends('layouts.app')
@section('title', 'Activity API Fetch Result')
@section('content')
    <div class="card">
        <div class="card-body">
            <h2>Activity API Fetch Result</h2>
            <div class="alert alert-success">Activity data fetched and stored in the database.</div>
            <h4>Stored Activities (IDs):</h4>
            <ul>
                @foreach($stored as $item)
                    <li>{{ $item->Id }}</li>
                @endforeach
            </ul>
            <h4>Raw API Data (truncated):</h4>
            @php
                $json = json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $max = 50000; // ~50KB limit to keep rendering safe
                $truncated = mb_substr($json ?? '', 0, $max, 'UTF-8');
                $isTruncated = ($json !== null) && (mb_strlen($json, 'UTF-8') > $max);
            @endphp
            @if(!empty($truncated))
                <pre>{{ $truncated }}@if($isTruncated)
...
[truncated]
@endif</pre>
            @else
                <div class="alert alert-info">No raw payload available.</div>
            @endif
            <a href="{{ url('/lead') }}" class="btn btn-primary mt-3">Back to Leads</a>
        </div>
    </div>
@endsection