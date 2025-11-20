@extends('layouts.app')

@section('title', 'Edit Profile')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        #map { height: 300px; border-radius: 10px; margin-bottom: 15px; }
    </style>
@endpush

@section('content')
<div class="container mx-auto p-4">
    <div class="auth-form" style="max-width:720px; margin:0 auto; position:relative;">
        <button id="theme-toggle" class="btn btn-outline" type="button" aria-label="Toggle theme" title="Toggle theme" style="position:absolute; top:12px; right:12px; padding:8px 12px; border-radius:10px; font-size:16px;">🌙</button>
        <h2 class="text-2xl font-bold text-center mb-4">Edit Profile</h2>
        <h1 class="text-2xl font-bold text-center mb-4 text-blue-400"><a  href="{{ route('home') }}">Skip >></a></h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-2 mb-4 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('profiles.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Existing fields -->
            <div class="mb-3">
                <label class="block font-semibold mb-1">Display Name</label>
                <input type="text" name="display_name" value="{{ old('display_name', $profile->display_name) }}" class="input w-full border p-2 rounded">
            </div>

            <div class="mb-3">
                <label class="block font-semibold mb-1">Profile Picture</label>
                <input type="file" name="profile_picture" class="w-full">
                @error('profile_picture')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label class="block font-semibold mb-1">Bio</label>
                <textarea name="bio" class="input w-full border p-2 rounded">{{ old('bio', $profile->bio) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="block font-semibold mb-1">Gender</label>
                <select name="gender" class="input w-full border p-2 rounded">
                    <option value="">Select gender (optional)</option>
                    <option value="male" {{ old('gender', $profile->gender) === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender', $profile->gender) === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ old('gender', $profile->gender) === 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('gender')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label class="block font-semibold mb-1">Budget Min</label>
                <input type="number" name="budget_min" value="{{ old('budget_min', $profile->budget_min) }}" class="input w-full border p-2 rounded">
            </div>

            <div class="mb-3">
                <label class="block font-semibold mb-1">Budget Max</label>
                <input type="number" name="budget_max" value="{{ old('budget_max', $profile->budget_max) }}" class="input w-full border p-2 rounded">
            </div>

            <div class="mb-3">
                <label class="block font-semibold mb-1">Cleanliness</label>
                <select name="cleanliness" class="input w-full border p-2 rounded">
                    <option value="">Select cleanliness (optional)</option>
                    <option value="very_clean" {{ old('cleanliness', $profile->cleanliness) === 'very_clean' ? 'selected' : '' }}>Very Clean</option>
                    <option value="clean" {{ old('cleanliness', $profile->cleanliness) === 'clean' ? 'selected' : '' }}>Clean</option>
                    <option value="average" {{ old('cleanliness', $profile->cleanliness) === 'average' ? 'selected' : '' }}>Average</option>
                    <option value="messy" {{ old('cleanliness', $profile->cleanliness) === 'messy' ? 'selected' : '' }}>Messy</option>
                </select>
                @error('cleanliness')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label class="block font-semibold mb-1">Schedule</label>
                <select name="schedule" class="input w-full border p-2 rounded">
                    <option value="">Select schedule (optional)</option>
                    <option value="morning_person" {{ old('schedule', $profile->schedule) === 'morning_person' ? 'selected' : '' }}>Morning Person</option>
                    <option value="night_owl" {{ old('schedule', $profile->schedule) === 'night_owl' ? 'selected' : '' }}>Night Owl</option>
                    <option value="flexible" {{ old('schedule', $profile->schedule) === 'flexible' ? 'selected' : '' }}>Flexible</option>
                </select>
                @error('schedule')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3 flex space-x-4">
                <input type="hidden" name="smokes" value="0">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="smokes" value="1" {{ old('smokes', $profile->smokes) ? 'checked' : '' }} class="input"> <span>Smokes</span>
                </label>
                <input type="hidden" name="pets_ok" value="0">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="pets_ok" value="1" {{ old('pets_ok', $profile->pets_ok) ? 'checked' : '' }} class="input"> <span>Pets OK</span>
                </label>
            </div>

            <!-- Move-in Location (Leaflet Map) -->
            <div class="mb-3">
                <label class="block font-semibold mb-1">Move-in Location</label>
                <div id="map"></div>
                <input type="hidden" id="move_in_lat" name="move_in_lat" value="{{ old('move_in_lat', $profile->move_in_lat) }}">
                <input type="hidden" id="move_in_lng" name="move_in_lng" value="{{ old('move_in_lng', $profile->move_in_lng) }}">
                @error('move_in_lat')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('move_in_lng')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Move-in Date (Flatpickr) -->
            <div class="mb-3">
                <label class="block font-semibold mb-1">Move-in Date</label>
                <input type="text" name="move_in_date" id="move_in_date" value="{{ old('move_in_date', $profile->move_in_date) }}" class="input w-full border p-2 rounded" placeholder="Select a date">
                @error('move_in_date')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Default coordinates (Kathmandu)
    const defaultLat = 27.7172;
    const defaultLng = 85.3240;

    // Get old values or profile values or fallback to default
    const moveInLat = parseFloat("{{ old('move_in_lat', $profile->move_in_lat ?? '') }}") || defaultLat;
    const moveInLng = parseFloat("{{ old('move_in_lng', $profile->move_in_lng ?? '') }}") || defaultLng;

    // Initialize Leaflet map
    const map = L.map('map').setView([moveInLat, moveInLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Marker
    let marker = L.marker([moveInLat, moveInLng], { draggable: true }).addTo(map);

    // Update hidden inputs on drag
    marker.on('dragend', function(e) {
        const pos = marker.getLatLng();
        document.getElementById('move_in_lat').value = pos.lat;
        document.getElementById('move_in_lng').value = pos.lng;
    });

    // Update marker on map click
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        document.getElementById('move_in_lat').value = e.latlng.lat;
        document.getElementById('move_in_lng').value = e.latlng.lng;
    });

    // Initialize Flatpickr
    const moveInDateInput = document.getElementById('move_in_date');
    flatpickr(moveInDateInput, {
        dateFormat: "Y-m-d",
        minDate: "today",
        defaultDate: moveInDateInput.value || null
    });
</script>
@endpush
