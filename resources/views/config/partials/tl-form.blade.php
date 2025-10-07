@php
$isEdit = isset($tl);
@endphp

<div class="card">
    <div class="card-header">
        <h2>{{ $isEdit ? 'Edit Team Lead' : 'Add Team Lead' }}</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ $isEdit ? route('configuration.tl.update', $tl) : route('configuration.tl.store') }}">
            @csrf
            @if($isEdit)
            @method('PUT')
            @endif

            <div class="form-group">
                <label for="tl_name">TL Name</label>
                <input type="text" name="tl_name" id="tl_name" class="form-control" value="{{ old('tl_name', $tl->tl_name ?? '') }}" required>
                @error('tl_name')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="contact">Contact</label>
                <input type="text" name="contact" id="contact" class="form-control" value="{{ old('contact', $tl->contact ?? '') }}">
                @error('contact')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $tl->email ?? '') }}">
                @error('email')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <select name="location" id="location" class="form-control select2-location" required>
                    @php
                    $currentLocation = old('location', $tl->location ?? '');
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
                @error('location')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="1" {{ old('status', ($tl->status ?? 1)) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', ($tl->status ?? 1)) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-aaft">{{ $isEdit ? 'Update' : 'Create' }}</button>
                <a href="{{ route('configuration.tl.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>