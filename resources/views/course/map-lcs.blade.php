@extends('layouts.app')

@section('title', 'Map LCs to Course')

@section('content')
<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
      <h2 class="mb-3">Map LCs to: {{ $course->courseName }}</h2>
      <div class="d-flex align-items-center">
        <form method="get" class="form-inline mb-3">
          <label for="location" class="mr-2">Filter by Location</label>
          <select name="location" id="location" class="form-control select2" style="min-width: 220px;">
            <option value="">All Locations</option>
            @foreach($locations as $loc)
              <option value="{{ $loc }}" {{ $selectedLocation === $loc ? 'selected' : '' }}>{{ $loc }}</option>
            @endforeach
          </select>
          <button type="submit" class="btn btn-sm btn-aaft ml-2">Apply</button>
          @if($selectedLocation)
            <a href="{{ route('configuration.course.map-lcs', $course) }}" class="btn btn-reset ml-2">Reset</a>
          @endif
        </form>
      </div>
    </div>

    <form method="post" action="{{ route('configuration.course.map-lcs.save', $course) }}">
      @csrf
      <div class="table-responsive">
        <table class="table table-striped" id="mapLcTable">
          <thead>
            <tr>
              <th style="width: 50px;"></th>
              <th>LC Name</th>
              <th>Location</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($lcs as $lc)
              <tr>
                <td>
                  <input type="checkbox" name="lc_ids[]" value="{{ $lc->id }}" {{ in_array($lc->id, $mappedLcIds) ? 'checked' : '' }}>
                </td>
                <td>{{ $lc->lcName }}</td>
                <td>{{ $lc->location }}</td>
                <td>
                  @if((int)$lc->status === 1)
                    <span class="badge badge-success">Active</span>
                  @else
                    <span class="badge badge-secondary">Left</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        <button type="submit" class="btn btn-aaft">Save Mapping</button>
        <a href="{{ route('configuration.course.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
