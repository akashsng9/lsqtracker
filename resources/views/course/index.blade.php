@extends('layouts.app')

@section('title', 'Course Master')

@section('content')
<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
      <h2 class="mb-3">Course Master</h2>
      <div class="d-flex align-items-center">
        <form method="get" class="form-inline mb-3">
          <label for="location" class="mr-2">Filter</label>
          <select name="location" id="location" class="form-control select2" style="min-width: 220px;">
            <option value="">All Locations</option>
            @foreach($locations as $loc)
              <option value="{{ $loc }}" {{ $selectedLocation === $loc ? 'selected' : '' }}>{{ $loc }}</option>
            @endforeach
          </select>
          <button type="submit" class="btn btn-sm btn-aaft ml-2 d-flex justify-content-center align-items-center"><i class="fa fa-filter mr-1"></i> Apply</button>
          @if($selectedLocation)
            <a href="{{ route('configuration.course.index') }}" class="btn btn-sm btn-reset ml-2 d-flex justify-content-center align-items-center"><i class="fa fa-refresh mr-1"></i> Reset</a>
          @endif
        </form>
        <a href="{{ route('configuration.course.create') }}" class="btn btn-sm btn-aaft ml-2 mb-3 d-flex justify-content-center align-items-center"><i class="fa fa-plus mr-1"></i> Add Course</a>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <hr>

    <div class="table-responsive">
      <table class="table table-striped" id="courseTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Course Name</th>
            <th>Course Id</th>
            <th>Location</th>
            <th>Status</th>
            <!-- <th>Keyword</th> -->
            <!-- <th>Created</th>
            <th>Updated</th> -->
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($courses as $index => $c)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $c->courseName }}</td>
              <td>{{ $c->CourseId }}</td>
              <td>{{ $c->courseLocation }}</td>
              <td>
                @if((int)$c->CourseStatus === 1)
                  <span class="badge badge-success">Active</span>
                @else
                  <span class="badge badge-secondary">Inactive</span>
                @endif
              </td>
              <!-- <td>{{ $c->keyword }}</td> -->
              <!-- <td>{{ optional($c->created_at)->format('Y-m-d H:i') }}</td>
              <td>{{ optional($c->updated_at)->format('Y-m-d H:i') }}</td> -->
              <td>
                <a href="{{ route('configuration.course.edit', $c) }}" class="btn btn-sm btn-aaft"><i class="fa fa-edit"></i> Edit</a>
                <a href="{{ route('configuration.course.map-lcs', $c) }}" class="btn btn-sm btn-info ml-1">
                  Map LCs
                  @php $cnt = (int)($mappedCounts[$c->id] ?? 0); @endphp
                  <span class="badge badge-light ml-1">{{ $cnt }}</span>
                </a>
                <form action="{{ route('configuration.course.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete course: {{ $c->courseName }}?');">
                  @csrf
                  @method('DELETE')
                  <!-- <button type="submit" class="btn btn-sm btn-danger"> <i class="fa fa-trash"></i>Delete</button> -->
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
