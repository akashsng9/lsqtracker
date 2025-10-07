@extends('layouts.app')
@section('title', 'Lead Activity')
@section('content')
    <div class="card">
        <div class="card-body">
            <h2>Lead Activity</h2>
            <p><strong>Record Count:</strong> {{ $recordCount }}</p>
            @if(count($activities))
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Created On</th>
                            <th>Activity Score</th>
                            <th>Type</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                            <tr>
                                <td>{{ $activity['EventName'] ?? '' }}</td>
                                <td>{{ $activity['CreatedOn'] ?? '' }}</td>
                                <td>{{ $activity['ActivityScore'] ?? '' }}</td>
                                <td>{{ $activity['Type'] ?? '' }}</td>
                                <td>
                                    @if(isset($activity['Data']) && is_array($activity['Data']))
                                        <ul>
                                            @foreach($activity['Data'] as $item)
                                                <li><strong>{{ $item['Key'] ?? '' }}:</strong> {{ $item['Value'] ?? '' }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No activity data found.</p>
            @endif
        </div>
    </div>
@endsection