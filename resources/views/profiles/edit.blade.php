@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container mx-auto p-4">
    <div class="auth-form" style="max-width:720px; margin:0 auto; position:relative;">
        <button id="theme-toggle" class="btn btn-outline" type="button" aria-label="Toggle theme" title="Toggle theme" style="position:absolute; top:12px; right:12px; padding:8px 12px; border-radius:10px; font-size:16px;">🌙</button>
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

            <div class="text-center">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>
</div>
@endsection
