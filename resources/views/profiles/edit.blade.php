@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container mx-auto p-4">
    <div class="bg-white shadow rounded-lg p-6 max-w-lg mx-auto">
        <h2 class="text-2xl font-bold text-center mb-4">Edit Profile</h2>
        <h1 class="m-4"><a  href="{{ route('home') }}" class="underline">Go to Home</a></h1>

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
                @error('display_name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label>Profile Picture</label>
                <input type="file" name="profile_picture" class="w-full">
                @error('profile_picture')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label>Bio</label>
                <textarea name="bio" class="w-full border p-2 rounded">{{ old('bio', $profile->bio) }}</textarea>
                @error('bio')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label>Gender</label>
                <select name="gender" class="w-full border p-2 rounded">
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
                <label>Budget Min</label>
                <input type="number" name="budget_min" value="{{ old('budget_min', $profile->budget_min) }}" class="w-full border p-2 rounded">
                @error('budget_min')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label>Budget Max</label>
                <input type="number" name="budget_max" value="{{ old('budget_max', $profile->budget_max) }}" class="w-full border p-2 rounded">
                @error('budget_max')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label>Cleanliness</label>
                <select name="cleanliness" class="w-full border p-2 rounded">
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
                <label>Schedule</label>
                <select name="schedule" class="w-full border p-2 rounded">
                    <option value="">Select schedule (optional)</option>
                    <option value="morning_person" {{ old('schedule', $profile->schedule) === 'morning_person' ? 'selected' : '' }}>Morning Person</option>
                    <option value="night_owl" {{ old('schedule', $profile->schedule) === 'night_owl' ? 'selected' : '' }}>Night Owl</option>
                    <option value="flexible" {{ old('schedule', $profile->schedule) === 'flexible' ? 'selected' : '' }}>Flexible</option>
                </select>
                @error('schedule')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label>Smokes</label>
                <select name="smokes" class="w-full border p-2 rounded">
                    <option value="">Select smoking preference (optional)</option>
                    <option value="1" {{ old('smokes', $profile->smokes) === 1 ? 'selected' : '' }}>Smoker</option>
                    <option value="0" {{ old('smokes', $profile->smokes) === 0 ? 'selected' : '' }}>Non-Smoker</option>
                </select>
                @error('smokes')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label>Pets</label>
                <select name="pets_ok" class="w-full border p-2 rounded">
                    <option value="">Select pet preference (optional)</option>
                    <option value="1" {{ old('pets_ok', $profile->pets_ok) === 1 ? 'selected' : '' }}>Pets OK</option>
                    <option value="0" {{ old('pets_ok', $profile->pets_ok) === 0 ? 'selected' : '' }}>No Pets</option>
                </select>
                @error('pets_ok')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="text-center">
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update Profile</button>
            </div>
        </form>
    </div>
</div>
@endsection
