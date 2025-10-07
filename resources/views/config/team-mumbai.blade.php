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
                            <label for="tl_name_select">TL Name</label>
                            <select name="tl_name[]" id="tl_name_select" class="form-control select2-multiple" multiple data-placeholder="Select LC Name(s)">
                                @foreach($teamLead as $tl)
                                <option value="{{ $tl->id }}">{{ $tl->tl_name }}</option>
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
                        <!-- <div class="col-md-3 mt-sm-2">
                            <label for="leads_select">Leads</label>
                            <select name="leads[]" id="leads_select" class="form-control select2-multiple" multiple data-placeholder="Select Lead(s)">
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}">{{ trim(($lead->FirstName ?? '') . ' ' . ($lead->LastName ?? '')) }}{{ !empty($lead->Phone) ? ' - ' . $lead->Phone : '' }}</option>
                                @endforeach
                            </select>
                        </div> -->
                        <div class="col-md-3 mt-sm-2">
                            <label for="lead_type">Lead Type</label>
                            <select name="lead_type" id="lead_type" class="form-control">
                                @foreach($leadType as $type)
                                <option value="{{ $type->sourceType }}">{{ $type->sourceType }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-aaft mt-3">submit</button>
                    <button type="reset" class="btn btn-reset mt-3">reset</button>
                </form>
            </div>
        </div>

        <!-- table -->
        <div class="table-responsive mt-2">
            <table class="table" id="mumbaiTable">
                <thead>
                    <tr>
                        <th>Sl.</th>
                        <th>ProspectID</th>
                        <th>Username</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>City</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>P001</td>
                        <td>john_doe</td>
                        <td>9876543210</td>
                        <td>john@example.com</td>
                        <td>New York</td>
                        <td>Website</td>
                        <td>Active</td>
                        <td>2025-10-01</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>P002</td>
                        <td>mary_smith</td>
                        <td>9123456789</td>
                        <td>mary@example.com</td>
                        <td>Los Angeles</td>
                        <td>Referral</td>
                        <td>Pending</td>
                        <td>2025-09-28</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>P003</td>
                        <td>alex_lee</td>
                        <td>9988776655</td>
                        <td>alex@example.com</td>
                        <td>Chicago</td>
                        <td>Social Media</td>
                        <td>Inactive</td>
                        <td>2025-09-15</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>


@endsection