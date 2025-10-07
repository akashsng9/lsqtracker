@extends('layouts.app')

@section('title', 'Assigned Courses')

@section('content')
<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
      <h2 class="mb-3">Assigned Courses</h2>
      <div class="d-flex justify-content-between align-items-center">
        <form method="get" class="form-inline mb-3">
          
            <label for="location" class="mr-2">Filter</label>
            <select name="location" id="location" class="form-control select2" style="min-width: 220px;">
              <option value="">Select Locations</option>
              @foreach($locations as $loc)
              <option value="{{ $loc }}" {{ $selectedLocation === $loc ? 'selected' : '' }}>{{ $loc }}</option>
              @endforeach
            </select>
       
            <select name="course" id="course" class="form-control select2" style="min-width: 250px;">
              <option value="">Select Courses</option>
              @foreach($courses as $course)
              <option value="{{ $course->id }}" {{ (string)$selectedCourse === (string)$course->id ? 'selected' : '' }}>{{ $course->courseName }}</option>
              @endforeach
            </select>
        

          <div class="form-group">
            <button type="submit" class="btn btn-sm btn-aaft mr-2"><i class="fa fa-filter"></i> Apply Filter</button>
            @if($selectedLocation || $selectedCourse)
            <a href="{{ route('configuration.assigned-courses.index') }}" class="btn btn-sm btn-reset"><i class="fa fa-refresh"></i> Reset</a>
            @endif
          </div>
        </form>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped" id="assignedTable">
        <thead>
          <tr>
            <th>#</th>
            <th>LC Name</th>
            <th>LC Location</th>
            <th>Course</th>
            <th>Course Location</th>
            <th>Mapped On</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $index => $r)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $r->lcName }}</td>
            <td>{{ $r->lc_location }}</td>
            <td>{{ $r->courseName }}</td>
            <td>{{ $r->course_location }}</td>
            <td>{{ $r->updated_on }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="text-center">No records found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection