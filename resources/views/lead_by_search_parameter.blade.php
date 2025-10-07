@extends('layouts.app')
@section('title', 'Lead By Search Parameter')
@section('content')
    <div class="card">
        <div class="card-body">
            <h2>Test Lead By Search Parameter API</h2>

            <!-- <form method="GET" action="{{ route('lead.fetch-by-search-parameter') }}" class="mb-3">
                <div class="form-row align-items-end">
                    <div class="col-auto">
                        <label for="search">Search (Name or Email):</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}"
                            placeholder="Enter name or email">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-secondary">Search</button>
                    </div>
                </div>
            </form> -->

            <form method="POST" action="{{ route('lead.fetch-by-search-parameter') }}">
                @csrf
                <div class="form-group">
                    <label for="payload">Payload (JSON):</label>
                    <textarea class="form-control" id="payload" name="payload" rows="12" required>{
                        "SearchParameters": {
                                "ListId": "50ade829-8ee1-11f0-9791-06b8222c9ed1",
                                "RetrieveBehaviour": "0"
                            },
                            "Columns": {
                                "Include_CSV": "ProspectAutoId,EmailAddress,FirstName,LastName,Score,"
                            },
                            "Sorting": {
                                "ColumnName": "CreatedOn",
                                "Direction": "1"
                            },
                            "Paging": {
                                "PageIndex": 1,
                                "PageSize": 1000
                            }
                        }
                    </textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Request</button>
            </form>

            @if(isset($storedLeads))
                <hr>
                <h4>Stored Leads:
                    {{ $storedLeads instanceof \Illuminate\Pagination\LengthAwarePaginator ? $storedLeads->total() : count($storedLeads) }}
                </h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="myTable">
                        <thead>
                            <tr>
                                <th>Sl. No.</th>
                                <th>Username</th>
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
                                $leads = $storedLeads instanceof \Illuminate\Pagination\LengthAwarePaginator ? $storedLeads : collect($storedLeads);
                            @endphp
                            @foreach($leads as $key => $lead)
                                <tr>
                                    <td>{{ ($storedLeads instanceof \Illuminate\Pagination\LengthAwarePaginator ? $storedLeads->firstItem() + $key : $key + 1) }}
                                    </td>
                                    <td>{{ $lead->FirstName }} {{ $lead->LastName }}</td>
                                    <td>{{ $lead->ProspectAutoId }}</td>
                                    <td>{{ $lead->EmailAddress }}</td>
                                    <td>{{ $lead->Score }}</td>
                                    <td>{{ $lead->ProspectID }}</td>
                                    <td>{{ $lead->OwnerId }}</td>
                                    <td>{{ $lead->CreatedOn }}</td>
                                    <td>{{ $lead->IsStarredLead }}</td>
                                    <td>{{ $lead->IsTaggedLead }}</td>
                                    <td>{{ $lead->CanUpdate }}</td>
                                    <td></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($storedLeads instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-3">
                        {{ $storedLeads->links() }}
                    </div>
                @endif
            @endif

        </div>
    </div>

    @push('styles')
    <style>
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
        .page-link {
            color: #007bff;
        }
    </style>
    @endpush
@endsection