@extends('layouts.app')
@section('title', 'Config Lead')
@section('content')

<div class="card">
    <div class="card-body">
        <h2>Mumbai Team</h2>
        <div class="card mt-3">
            <div class="card-body">
                <form action="{{ route('config.team.save') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-3 mt-sm-2">
                            <label for="course_name_select">Course Name</label>
                            <select name="course_name[]" id="course_name_select" class="form-control select2-multiple" multiple data-placeholder="Select Course Name(s)">
                                @foreach($courses as $course)
                                    <option value="{{ $course->courseName}}">{{ $course->courseName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mt-sm-2">
                            <label for="lc_name_select">LC Name</label>
                            <select name="lc_name[]" id="lc_name_select" class="form-control select2-multiple" multiple data-placeholder="Select LC Name(s)">
                                @foreach($lcs as $lc)
                                    <option value="{{ $lc->id }}">{{ $lc->lcName }} {{ $lc->location ? '(' . $lc->location . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mt-sm-2">
                            <label for="leads_select">Leads</label>
                            <select name="leads[]" id="leads_select" class="form-control select2-multiple" multiple data-placeholder="Select Lead(s)">
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}">{{ trim(($lead->FirstName ?? '') . ' ' . ($lead->LastName ?? '')) }}{{ !empty($lead->Phone) ? ' - ' . $lead->Phone : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mt-sm-2">
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