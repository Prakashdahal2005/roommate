
@extends('layouts.app')

@section('title', $profile->display_name)

@section('content')
<div class="profile-container">
    <div class="profile-header">
        @if($profile->profile_picture)
            <img class="max-w-60" src="{{ asset('storage/' . $profile->profile_picture) }}" alt="{{ $profile->display_name }}" class="profile-picture">
        @else
            <div class="profile-picture flex items-center justify-center">
                <span class="text-gray-500">No Image</span>
            </div>
        @endif
        <h2 class="profile-name">{{ $profile->display_name }}</h2>
        <p class="profile-bio">{{ $profile->bio }}</p>
    </div>

    <div class="profile-details">
        <div class="profile-detail-group">
            <div class="profile-detail-item">
                <span class="detail-label">Gender</span>
                <span class="detail-value">{{ ucfirst($profile->gender) }}</span>
            </div>
        </div>

        <div class="profile-detail-group">
            <div class="profile-detail-item">
                <span class="detail-label">Budget Range</span>
                <span class="detail-value">Rs. {{ $profile->budget_min }} - Rs. {{ $profile->budget_max }}</span>
            </div>
        </div>

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
    </div>
</div>
@if(auth()->user()?->profile->id === $profile->id)
<p><a style="color:blue;" href="{{ route('profiles.edit') }}">Edit profile</a></p>
@endif
@endsection
