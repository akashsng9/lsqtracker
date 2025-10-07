@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card mb-4 dashboard-card">
  <div class="card-body">
    <h3 class="mb-3">Overview</h3>
    <div class="row text-center">
      <div class="col-md-4 mb-3 course-card">
        <div class="p-3 border rounded bg-primary text-white">
          <div class="h5 mb-1">Courses</div>
          <div class="display-4">{{ $totalCourses }}</div>
          <div class="text-white">Active: {{ $activeCourses }}</div>
        </div>
      </div>
      <div class="col-md-4 mb-3 tl-card">
        <div class="p-3 border rounded bg-success text-white">
          <div class="h5 mb-1">Team Leaders</div>
          <div class="display-4">{{ $totalTLs }}</div>
          <div class="text-white">Active: {{ $activeTLs }}</div>
        </div>
      </div>
      <div class="col-md-4 mb-3 lc-card">
        <div class="p-3 border rounded bg-warning text-white">
          <div class="h5 mb-1">LC Owners</div>
          <div class="display-4">{{ $totalLCs }}</div>
          <div class="text-white">Active: {{ $activeLCs }}</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h3 class="mb-3">By Location</h3>
    <div class="table-responsive">
      <table class="table table-striped" id="locationSummaryTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Location</th>
            <th>Courses</th>
            <th>Team Leaders</th>
            <th>LC Owners</th>
          </tr>
        </thead>
        <tbody>
          @forelse($byLocation as $index => $row)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $row['location'] }}</td>
              <td>{{ $row['courses'] }}</td>
              <td>{{ $row['tls'] }}</td>
              <td>{{ $row['lcs'] }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center">No data</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
