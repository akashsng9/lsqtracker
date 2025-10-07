@extends('layouts.app')

@section('title', 'Map Courses to LC')

@section('content')
<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
      <h2 class="mb-3">Map Courses to: {{ $lc->lcName }}</h2>
      <div class="d-flex align-items-center">
        <form method="get" class="form-inline mb-3">
          <label for="location" class="mr-2">Filter by Course Location</label>
          <select name="location" id="location" class="form-control select2" style="min-width: 220px;">
            <option value="">All Locations</option>
            @foreach($locations as $loc)
              <option value="{{ $loc }}" {{ $selectedLocation === $loc ? 'selected' : '' }}>{{ $loc }}</option>
            @endforeach
          </select>
          <button type="submit" class="btn btn-sm btn-aaft ml-2">Apply</button>
          @if($selectedLocation)
            <a href="{{ route('configuration.lc.map-courses', $lc->id) }}" class="btn btn-reset ml-2">Reset</a>
          @endif
        </form>
      </div>
    </div>

    <form method="post" action="{{ route('configuration.lc.map-courses.save', $lc->id) }}">
      @csrf
      <div class="table-responsive">
        <table class="table table-striped" id="mapLcTable">
          <thead>
            <tr>
              <th style="width: 50px;"></th>
              <th>Course</th>
              <th>Location</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($courses as $c)
              <tr>
                <td>
                  <input type="checkbox" name="course_ids[]" value="{{ $c->id }}" {{ in_array($c->id, $mappedCourseIds) ? 'checked' : '' }}>
                </td>
                <td>{{ $c->courseName }}</td>
                <td>{{ $c->courseLocation }}</td>
                <td>
                  @if((int)$c->CourseStatus === 1)
                    <span class="badge badge-success">Active</span>
                  @else
                    <span class="badge badge-secondary">Inactive</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        <button type="submit" class="btn btn-aaft">Save Mapping</button>
        <a href="{{ route('configuration.lc.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
