@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container mx-auto p-4">
    <div class="bg-white shadow rounded-lg p-6 max-w-lg mx-auto">
        <h2 class="text-2xl font-bold text-center mb-4">Edit Profile</h2>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-2 mb-4 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('profiles.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Display Name</label>
                <input type="text" name="display_name" value="{{ old('display_name', $profile->display_name) }}" class="w-full border p-2 rounded">
            </div>

            <div class="mb-3">
                <label>Profile Picture</label>
                <input type="file" name="profile_picture" class="w-full">
            </div>

            <div class="mb-3">
                <label>Bio</label>
                <textarea name="bio" class="w-full border p-2 rounded">{{ old('bio', $profile->bio) }}</textarea>
            </div>

            <div class="mb-3">
                <label>Gender</label>
                <select name="gender" class="w-full border p-2 rounded">
                    <option value="male" {{ $profile->gender === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $profile->gender === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ $profile->gender === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Budget Min</label>
                <input type="number" name="budget_min" value="{{ old('budget_min', $profile->budget_min) }}" class="w-full border p-2 rounded">
            </div>

            <div class="mb-3">
                <label>Budget Max</label>
                <input type="number" name="budget_max" value="{{ old('budget_max', $profile->budget_max) }}" class="w-full border p-2 rounded">
            </div>

            <div class="mb-3">
                <label>Move-in Date</label>
                <input type="date" name="move_in_date" value="{{ old('move_in_date', $profile->move_in_date?->format('Y-m-d')) }}" class="w-full border p-2 rounded">
            </div>

            <div class="mb-3">
                <label>Cleanliness</label>
                <input type="text" name="cleanliness" value="{{ old('cleanliness', $profile->cleanliness) }}" class="w-full border p-2 rounded">
            </div>

            <div class="mb-3">
                <label>Schedule</label>
                <input type="text" name="schedule" value="{{ old('schedule', $profile->schedule) }}" class="w-full border p-2 rounded">
            </div>

            <div class="mb-3 flex space-x-4">
                <label>
                    <input type="checkbox" name="smokes" value="1" {{ $profile->smokes ? 'checked' : '' }}> Smokes
                </label>
                <label>
                    <input type="checkbox" name="pets_ok" value="1" {{ $profile->pets_ok ? 'checked' : '' }}> Pets OK
                </label>
            </div>

            <div class="text-center">
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update Profile</button>
            </div>
        </form>
    </div>
</div>
@endsection
