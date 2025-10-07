@extends('layouts.app')
@section('title', 'Lead Details')
@section('content')
      
            <div class="card">
                <div class="card-body">
                    <h2>Lead Details</h2>
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <select name="first_name" id="first_name" class="form-select">
                                <option value="">All</option>
                                @if(isset($last100))
                                    @foreach($last100->pluck('FirstName')->unique()->filter()->sort() as $name)
                                        <option value="{{ $name }}" {{ ($filters['first_name'] ?? '') == $name ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="email" class="form-label">Email</label>
                            <select name="email" id="email" class="form-select">
                                <option value="">All</option>
                                @if(isset($last100))
                                    @foreach($last100->pluck('EmailAddress')->unique()->filter()->sort() as $email)
                                        <option value="{{ $email }}" {{ ($filters['email'] ?? '') == $email ? 'selected' : '' }}>
                                            {{ $email }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="phone" class="form-label">Phone</label>
                            <select name="phone" id="phone" class="form-select">
                                <option value="">All</option>
                                @if(isset($last100))
                                    @foreach($last100->pluck('Phone')->unique()->filter()->sort() as $phone)
                                        <option value="{{ $phone }}" {{ ($filters['phone'] ?? '') == $phone ? 'selected' : '' }}>
                                            {{ $phone }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All</option>
                                @if(isset($last100))
                                    @foreach($last100->pluck('ProspectStage')->unique()->filter()->sort() as $status)
                                        <option value="{{ $status }}" {{ ($filters['status'] ?? '') == $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ url('/lead') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                    @if(isset($leads) && $leads->count())
                        <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="myTable">
                            <thead>
                                <tr>
                                    <th>Sr No</th>
                                    <th>First Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Created On</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leads as $key =>$lead)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $lead->FirstName }}</td>
                                        <td>{{ $lead->EmailAddress }}</td>
                                        <td>{{ $lead->Phone }}</td>
                                        <td>{{ $lead->ProspectStage }}</td>
                                        <td>{{ $lead->CreatedOn }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                        @if($leads instanceof \Illuminate\Pagination\Paginator || $leads instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            {{ $leads->appends($filters)->links() }}
                        @endif
                    @else
                        <p>No lead data found.</p>
                    @endif

                    <!-- @if(isset($last100) && $last100->count())
                        <hr>
                        <h4>Last 100 Records (User Experience)</h4>
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>First Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Created On</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($last100 as $lead)
                                    <tr>
                                        <td>{{ $lead->FirstName }}</td>
                                        <td>{{ $lead->EmailAddress }}</td>
                                        <td>{{ $lead->Phone }}</td>
                                        <td>{{ $lead->ProspectStage }}</td>
                                        <td>{{ $lead->CreatedOn }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif -->
                </div>
            </div>
      
@endsection