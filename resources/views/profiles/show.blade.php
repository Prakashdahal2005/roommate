@extends('layouts.app')

@section('title', $profile->display_name)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    .profile-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 30px;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        font-family: Arial, sans-serif;
        position: relative;
    }
    .profile-header {
        text-align: center;
        margin-bottom: 30px;
    }
    .profile-picture {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        object-fit: cover;
        background-color: #e2e8f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: #555;
    }
    .profile-name {
        font-size: 26px;
        font-weight: bold;
        margin: 15px 0 5px;
    }
    .profile-bio {
        font-size: 16px;
        color: #555;
    }
    .profile-details {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-top: 20px;
    }
    .profile-detail-group {
        display: flex;
        gap: 40px;
        flex-wrap: wrap;
    }
    .profile-detail-item {
        flex: 1;
        min-width: 150px;
    }
    .detail-label {
        display: block;
        font-weight: bold;
        color: #333;
    }
    .detail-value {
        display: block;
        color: #555;
        margin-top: 4px;
    }
    #map {
        height: 220px;
        border-radius: 10px;
        margin-top: 10px;
    }
</style>
@endpush

@section('content')
<div class="profile-container">
    <div class="profile-header">
        @if($profile->profile_picture)
            <img src="{{ asset('storage/' . $profile->profile_picture) }}" alt="{{ $profile->display_name }}" class="profile-picture">
        @else
            <div class="profile-picture flex items-center justify-center">
                <span class="text-gray-500">No Image</span>
            </div>
        @endif
        <h2 class="profile-name">{{ $profile->display_name }}</h2>
        <p class="profile-bio">{{ $profile->bio }}</p>
    </div>

    <div class="profile-details">

        <!-- AGE + GENDER -->
        <div class="profile-detail-group">
            <div class="profile-detail-item">
                <span class="detail-label">Age</span>
                <span class="detail-value">{{ $profile->user->age }}</span>
            </div>
            <div class="profile-detail-item">
                <span class="detail-label">Gender</span>
                <span class="detail-value">{{ ucfirst($profile->gender) }}</span>
            </div>
        </div>

        <!-- BUDGET -->
        <div class="profile-detail-group">
            <div class="profile-detail-item">
                <span class="detail-label">Budget Range</span>
                <span class="detail-value">Rs. {{ $profile->budget_min }} - Rs. {{ $profile->budget_max }}</span>
            </div>
        </div>

        <!-- CLEANLINESS + SCHEDULE -->
        <div class="profile-detail-group">
            <div class="profile-detail-item">
                <span class="detail-label">Cleanliness</span>
                <span class="detail-value">{{ ucfirst($profile->cleanliness) }}</span>
            </div>
            <div class="profile-detail-item">
                <span class="detail-label">Schedule</span>
                <span class="detail-value">{{ ucfirst($profile->schedule) }}</span>
            </div>
        </div>

        <!-- SMOKES + PETS -->
        <div class="profile-detail-group">
            <div class="profile-detail-item">
                <span class="detail-label">Smokes</span>
                <span class="detail-value">{{ $profile->smokes ? 'Yes' : 'No' }}</span>
            </div>
            <div class="profile-detail-item">
                <span class="detail-label">Pets Allowed</span>
                <span class="detail-value">{{ $profile->pets_ok ? 'Yes' : 'No' }}</span>
            </div>
        </div>

        <!-- MOVE-IN DATE -->
        <div class="profile-detail-group">
            <div class="profile-detail-item">
                <span class="detail-label">Move-in Date</span>
                <span class="detail-value">
                    {{ $profile->move_in_date ? \Carbon\Carbon::parse($profile->move_in_date)->format('M d, Y') : 'Not set' }}
                </span>
            </div>
        </div>

        <!-- LOCATION -->
        @if($profile->move_in_lat && $profile->move_in_lng)
        <div class="profile-detail-group">
            <div class="profile-detail-item" style="flex:1 1 100%;">
                <span class="detail-label">Location</span>
                <span class="detail-value" id="location-address">Loading address...</span>
                <div id="map"></div>
            </div>
        </div>
        @endif

    </div>

    @if(auth()->user()?->profile->id === $profile->id)
        <p><a style="color:blue;" href="{{ route('profiles.edit') }}">Edit profile</a></p>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<<script>
    var lat = {{ $profile->move_in_lat ?? 'null' }};
    var lng = {{ $profile->move_in_lng ?? 'null' }};

    if(lat && lng){
        var map = L.map('map').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker = L.marker([lat, lng]).addTo(map);

        fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json')
            .then(res => res.json())
            .then(data => {
                document.getElementById('location-address').innerText = data.display_name || 'Address not found';
                marker.bindPopup(data.display_name || 'Move-in location').openPopup();
            })
            .catch(err => {
                console.error(err);
                document.getElementById('location-address').innerText = 'Unable to load address';
            });
    }
</script>

@endpush
