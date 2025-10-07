@extends('layouts.app')
@section('title', 'Config Lead')
@section('content')
    <div class="card">
        <div class="card-body">
            <h2>Config Lead API</h2>

            <form method="POST" action="{{ route('config.team.save') }}">
                @csrf
                <div class="form-group">
                    <label for="payload">Payload (JSON):</label>
                    <textarea class="form-control" id="payload" name="payload" rows="12" required>
                        {
                            "SearchParameters": {
                                "ListId": "66b97513-920e-11f0-9791-06b8222c9ed1",
                                "RetrieveBehaviour": "0"
                            },
                            "Columns": {
                                "Include_CSV": "ProspectAutoId,EmailAddress,Score,"
                            },
                            "Sorting": {
                                "ColumnName": "CreatedOn",
                                "Direction": "1"
                            },
                            "Paging": {
                                "PageIndex": 1,
                                "PageSize": 50
                            }
                        }

                    </textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Request</button>
            </form>

            @if(isset($storedConfigLeads))
                <hr>
                <h4>Stored Leads:
                    {{ $storedConfigLeads instanceof \Illuminate\Pagination\LengthAwarePaginator ? $storedConfigLeads->total() : count($storedConfigLeads) }}
                </h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="myTable">
                        <thead>
                            <tr>
                                <th>Sl. No.</th>
                                <!-- <th>Username</th> -->
                                <th>ProspectAutoId</th>
                                <th>EmailAddress</th>
                                <th>Score</th>
                                <th>ProspectID</th>
                                <th>OwnerId</th>
                                <th>CreatedOn</th>
                                <th>IsStarredLead</th>
                                <th>IsTaggedLead</th>
                                <th>CanUpdate</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $leads = $storedConfigLeads instanceof \Illuminate\Pagination\LengthAwarePaginator ? $storedConfigLeads : collect($storedConfigLeads);
                            @endphp
                            @foreach($leads as $key => $lead)
                                <tr>
                                    <td>{{ ($storedConfigLeads instanceof \Illuminate\Pagination\LengthAwarePaginator ? $storedConfigLeads->firstItem() + $key : $key + 1) }}
                                    </td>
                                    <td>{{ $lead->ProspectAutoId }}</td>
                                    <td>{{ $lead->EmailAddress }}</td>
                                    <td>{{ $lead->Score }}</td>
                                    <td>{{ $lead->ProspectID }}</td>
                                    <td>{{ $lead->OwnerId }}</td>
                                    <td>{{ $lead->CreatedOn }}</td>
                                    <td>{{ (int) ($lead->IsStarredLead ?? 0) }}</td>
                                    <td>{{ (int) ($lead->IsTaggedLead ?? 0) }}</td>
                                    <td>{{ (int) ($lead->CanUpdate ?? 0) }}</td>
                                    <td></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- @if($storedConfigLeads instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-3">
                        {{ $storedConfigLeads->links() }}
                    </div>
                @endif -->
            @endif

        </div>
    </div>
@endsection