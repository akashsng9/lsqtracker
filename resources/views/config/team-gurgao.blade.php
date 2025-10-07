@extends('layouts.app')
@section('title', 'Config Lead')
@section('content')

<div class="card">
    <div class="card-body">
        <h2>Gurgao Team</h2>
        <div class="card mt-3">
            <div class="card-body">
                <form action="{{ route('config.team.save') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <label for="course_name">Course Name</label>
                            <select name="course_name[]" id="course_name" class="form-control select2-multiple" multiple data-placeholder="Select Course Name">
                                <option value=""></option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->courseName }}">{{ $course->courseName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- <div class="col-md-6">
                            <label for="owner_name">Owner Name</label>
                            <select name="owner_name" id="owner_name" class="form-control">
                                <option value="">Select Owner Name</option>
                                <option value="Owner Name 1">Abhishek</option>
                                <option value="Owner Name 2">Rahul</option>
                                <option value="Owner Name 3">Ravi</option>
                            </select>
                        </div> -->
                        <div class="col-md-3">
                            <label for="lc_name">LC Name</label>
                            <select name="lc_name[]" id="lc_name" class="form-control select2-multiple" multiple data-placeholder="Select LC Name(s)">
                                @foreach($lcs as $lc)
                                    <option value="{{ $lc->id }}">{{ $lc->lcName }} {{ $lc->location ? '(' . $lc->location . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="leads">Leads</label>
                            <select name="leads[]" id="leads" class="form-control select2-multiple" multiple data-placeholder="Select Lead(s)">
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}">{{ trim(($lead->FirstName ?? '') . ' ' . ($lead->LastName ?? '')) }}{{ !empty($lead->Phone) ? ' - ' . $lead->Phone : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="lead_type">Lead Type</label>
                            <input type="text" name="lead_type" id="lead_type" value="Organic" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-aaft mt-3">submit</button>
                    <button type="reset" class="btn btn-reset mt-3">reset</button>
                </form>
            </div>
        </div>


    </div>
</div>

@endsection
