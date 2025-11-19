@extends('layouts.app')

@section('title', $profile->display_name)
@push('styles')
<style>
    /* Profile container */
    .profile-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        font-family: Arial, sans-serif;
        position: relative;
    }

    /* Header */
    .profile-header {
        text-align: center;
        margin-bottom: 30px;
    }

    /* Profile picture */
    .profile-picture {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        background-color: #e2e8f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: #555;
    }

    /* Name & Bio */
    .profile-name {
        font-size: 24px;
        font-weight: bold;
        margin: 15px 0 5px;
    }

    .profile-bio {
        font-size: 16px;
        color: #555;
    }

    /* Profile details container */
    .profile-details {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Each detail group */
    .profile-detail-group {
        display: flex;
        gap: 40px;
        flex-wrap: wrap;
    }

    /* Individual detail item */
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

    /* Edit link */
    a {
        text-decoration: none;
        font-weight: bold;
    }

    a:hover {
        text-decoration: underline;
    }
</style>
@endpush

@section('content')
<div class="profile-container">
    <button id="theme-toggle" class="btn btn-outline" type="button" aria-label="Toggle theme" title="Toggle theme" style="position:absolute; top:12px; right:12px;">🌙</button>
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

        <!-- AGE + GENDER GROUP (NEW: AGE ADDED) -->
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
                <span class="detail-value">
                    {{ $profile->smokes === null ? '' : ($profile->smokes ? 'Yes' : 'No') }}
                </span>
            </div>
            <div class="profile-detail-item">
                <span class="detail-label">Pets Allowed</span>
                <span class="detail-value">
                    {{ $profile->pets_ok === null ? '' : ($profile->pets_ok ? 'Yes' : 'No') }}
                </span>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()?->profile->id === $profile->id)
<p><a style="color:blue;" href="{{ route('profiles.edit') }}">Edit profile</a></p>
@endif
@endsection
