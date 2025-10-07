@extends('layouts.app')
@section('title', 'LC Management')
@section('content')
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h2 class="mb-3">LC Management</h2>
            <div class="d-flex justify-content-between align-items-center">
                <form method="get" class="form-inline mb-3">
                    <label for="location" class="mr-2">Filter</label>
                    <select name="location" id="location" class="form-control select2" style="min-width: 220px;">
                        <option value="">Select Locations</option>
                        @foreach($locations as $loc)
                        <option value="{{ $loc }}" {{ $selectedLocation === $loc ? 'selected' : '' }}>{{ $loc }}</option>
                        @endforeach
                    </select>
                    <!-- <label for="tl" class="mr-2 ml-3">Filter by TL</label> -->
                    <select name="tl" id="tl" class="form-control select2" style="min-width: 220px;">
                        <option value="">Select Team Lead</option>
                        @foreach($tls as $t)
                        <option value="{{ $t->id }}" {{ (string)$selectedTl === (string)$t->id ? 'selected' : '' }}>{{ $t->tl_name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-aaft ml-2 d-flex justify-content-center align-items-center"><i class="fa fa-filter mr-1"></i> Apply</button>
                    @if($selectedLocation || $selectedTl)
                    <a href="{{ route('configuration.lc.index') }}" class="btn btn-reset ml-2 d-flex justify-content-center align-items-center"><i class="fa fa-refresh mr-1"></i> Reset</a>
                    @endif
                </form>
                <button type="button" class="btn btn-sm btn-aaft ml-2 mb-3 d-flex justify-content-center align-items-center" data-toggle="modal" data-target="#addLcModal">
                    <i class="fa fa-plus mr-1"></i> Add LC
                </button>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped" id="lcTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>LC Name</th>
                        <th>Location</th>
                        <th>TL</th>
                        <th>Status</th>
                        <th>Updated At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lcs as $index => $lc)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $lc->lcName }}</td>
                        <td>{{ $lc->location }}</td>
                        <td>{{ $lc->tl_name ?? '-' }}</td>
                        <td>
                            @if((int)$lc->status === 1)
                            <span class="badge badge-success">Active</span>
                            @else
                            <span class="badge badge-danger">Left</span>
                            @endif
                        </td>
                        <td>{{ $lc->updatedAt }}</td>
                        <td>
                            <form action="{{ route('configuration.lc.updateStatus', $lc->id) }}" method="post" class="form-inline">
                                @csrf
                                <select name="status" class="form-control form-control-sm mr-2">
                                    <option value="1" {{ (int)$lc->status === 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ (int)$lc->status === 0 ? 'selected' : '' }}>Left</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-aaft"> <i class="fa fa-edit mr-1"></i> Update</button>
                                <a href="{{ route('configuration.lc.map-courses', $lc->id) }}" class="btn btn-sm btn-info">
                                    Map Courses
                                    @php $mc = (int)($lcCourseCounts[$lc->id] ?? 0); @endphp
                                    <span class="badge badge-light ml-1">{{ $mc }}</span>
                                </a>
                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#assignTlModal{{ $lc->id }}">
                                    {{ $lc->tl_name ? 'Change TL' : 'Assign TL' }}
                                </button>
                            </form>

                            <!-- Assign TL Modal -->
                            <div class="modal fade" id="assignTlModal{{ $lc->id }}" tabindex="-1" role="dialog" aria-labelledby="assignTlModalLabel{{ $lc->id }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="assignTlModalLabel{{ $lc->id }}">Assign TL to {{ $lc->lcName }}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="post" action="{{ route('configuration.lc.map-tl.save', $lc->id) }}">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="tl_id">Select Team Lead</label>
                                                    <select name="tl_id" id="tl_id" class="form-control select2" style="width: 100%">
                                                        <option value="">-- Select TL or Choose Later --</option>
                                                        @foreach($tls as $tl)
                                                            <option value="{{ $tl->id }}" {{ $lc->fk_tl == $tl->id ? 'selected' : '' }}>{{ $tl->tl_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-reset" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-aaft">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add LC Modal -->
<div class="modal fade" id="addLcModal" tabindex="-1" role="dialog" aria-labelledby="addLcModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addLcModalLabel">Add New LC</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post" action="{{ route('configuration.lc.store') }}">
        @csrf
        <div class="modal-body">
            <div class="form-group">
                <label for="lcName">LC Name</label>
                <input type="text" class="form-control" id="lcName" name="lcName" required placeholder="Enter LC Name">
            </div>
            <div class="form-group">
                <label for="locationInput">Location</label>
                <input list="locationList" type="text" class="form-control" id="locationInput" name="location" required placeholder="Enter or select location">
                <datalist id="locationList">
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div class="form-group">
                <label for="fk_tl">Team Lead (TL)</label>
                <select name="fk_tl" id="fk_tl" class="form-control select2" required style="width: 100%">
                    <option value="" disabled {{ empty($selectedTl) ? 'selected' : '' }}>Select TL</option>
                    @foreach($tls as $t)
                        <option value="{{ $t->id }}" {{ (string)$selectedTl === (string)$t->id ? 'selected' : '' }}>{{ $t->tl_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="1">Active</option>
                    <option value="0">Left</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-reset" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-aaft">Add</button>
        </div>
      </form>
    </div>
  </div>
 </div>



@endsection