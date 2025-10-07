@php
    $isEdit = isset($course);
@endphp

<div class="card">
  <div class="card-header">
    <h2>{{ $isEdit ? 'Edit Course' : 'Add Course' }}</h2>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ $isEdit ? route('configuration.course.update', $course) : route('configuration.course.store') }}">
      @csrf
      @if($isEdit)
        @method('PUT')
      @endif

      <div class="form-group">
        <label for="courseName">Course Name</label>
        <input type="text" name="courseName" id="courseName" class="form-control" value="{{ old('courseName', $course->courseName ?? '') }}" required>
        @error('courseName')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="form-group">
        <label for="CourseId">Course Id (optional)</label>
        <input type="text" name="CourseId" id="CourseId" class="form-control" value="{{ old('CourseId', $course->CourseId ?? '') }}">
        @error('CourseId')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="form-group">
        <label for="courseLocation">Location</label>
        <select name="courseLocation" id="courseLocation" class="form-control select2-location" required>
          @php
            $currentLocation = old('courseLocation', $course->courseLocation ?? '');
            $options = isset($locations) ? collect($locations) : collect();
            if ($currentLocation && !$options->contains($currentLocation)) {
              $options = $options->push($currentLocation);
            }
            $options = $options->filter()->unique()->sort()->values();
          @endphp
          <option value="" disabled {{ empty($currentLocation) ? 'selected' : '' }}>Select or type location</option>
          @foreach($options as $loc)
            <option value="{{ $loc }}" {{ (string)$currentLocation === (string)$loc ? 'selected' : '' }}>{{ $loc }}</option>
          @endforeach
        </select>
        @error('courseLocation')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="form-group">
        <label for="CourseStatus">Status</label>
        <select name="CourseStatus" id="CourseStatus" class="form-control" required>
          <option value="1" {{ (string)old('CourseStatus', $course->CourseStatus ?? 1) === '1' ? 'selected' : '' }}>Active</option>
          <option value="0" {{ (string)old('CourseStatus', $course->CourseStatus ?? 1) === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('CourseStatus')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="form-group">
        <label for="keyword">Keyword</label>
        <input type="text" name="keyword" id="keyword" class="form-control" value="{{ old('keyword', $course->keyword ?? '') }}" placeholder="Optional">
        @error('keyword')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="mt-3">
        <button type="submit" class="btn btn-aaft">{{ $isEdit ? 'Update' : 'Create' }}</button>
        <a href="{{ route('configuration.course.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
