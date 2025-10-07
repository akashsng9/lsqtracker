@extends('layouts.app')

@section('title', 'TL Management')

@section('content')
<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>TL Management</h2>
    <a href="{{ route('configuration.tl.create') }}" class="btn btn-aaft d-flex justify-content-center align-items-center"><i class="fa fa-plus mr-1"></i> Add TL</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="table-responsive">
    <table class="table table-bordered table-striped" id="tlTable">
        <thead class="thead-light">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Location</th>
                <th>Status</th>
                <th>Created</th>
                <th>Updated</th>
                <th style="width: 130px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tls as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->tl_name }}</td>
                    <td>{{ $row->contact }}</td>
                    <td>{{ $row->email }}</td>
                    <td>{{ $row->location }}</td>
                    <td>
                        @if($row->status)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>{{ optional($row->created_on)->format('Y-m-d H:i') }}</td>
                    <td>{{ optional($row->updated_on)->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('configuration.tl.edit', $row) }}" class="btn btn-sm btn-aaft"><i class="fa fa-edit mr-1"></i> Edit</a>
                        <form action="{{ route('configuration.tl.destroy', $row) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete TL: {{ $row->tl_name }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash mr-1"></i> Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
    </div>
</div>
@endsection
