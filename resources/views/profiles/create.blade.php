@extends('layouts.app')

@section('title', 'Create Profile')

@section('content')
<div class="container mx-auto p-4">
    <div class="bg-white shadow rounded-lg p-6 max-w-lg mx-auto">
        <h2 class="text-2xl font-bold text-center mb-4">Create Profile</h2>

        @if(session('profile-create-success'))
            <div class="bg-green-100 text-green-700 p-2 mb-4 rounded">
                {{ session('profile-create-success') }}
            </div>
        @endif

        @error('profile')
    <div class="text-red-500 mb-2">{{ $message }}</div>
@enderror


        <form action="{{ route('profiles.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label>Display Name</label>
                <input type="text" name="display_name" value="{{ old('display_name') }}" class="w-full border p-2 rounded">
                @error('display_name')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Profile Picture</label>
                <input type="file" name="profile_picture" class="w-full">
                @error('profile_picture')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Bio</label>
                <textarea name="bio" class="w-full border p-2 rounded">{{ old('bio') }}</textarea>
                @error('bio')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Age</label>
                <input type="number" name="age" value="{{ old('age') }}" class="w-full border p-2 rounded">
                @error('age')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Gender</label>
                <select name="gender" class="w-full border p-2 rounded">
                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('gender')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Budget Min</label>
                <input type="number" name="budget_min" value="{{ old('budget_min') }}" class="w-full border p-2 rounded">
                @error('budget_min')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Budget Max</label>
                <input type="number" name="budget_max" value="{{ old('budget_max') }}" class="w-full border p-2 rounded">
                @error('budget_max')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Move-in Date</label>
                <input type="date" name="move_in_date" value="{{ old('move_in_date') }}" class="w-full border p-2 rounded">
                @error('move_in_date')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Cleanliness</label>
                <input type="text" name="cleanliness" value="{{ old('cleanliness') }}" class="w-full border p-2 rounded">
                @error('cleanliness')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Schedule</label>
                <input type="text" name="schedule" value="{{ old('schedule') }}" class="w-full border p-2 rounded">
                @error('schedule')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 flex space-x-4">
                <label>
                    <input type="checkbox" name="smokes" value="1" {{ old('smokes') ? 'checked' : '' }}> Smokes
                </label>
                <label>
                    <input type="checkbox" name="pets_ok" value="1" {{ old('pets_ok') ? 'checked' : '' }}> Pets OK
                </label>
                @error('smokes')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
                @error('pets_ok')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="text-center">
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Create Profile</button>
            </div>
        </form>
    </div>
</div>
@endsection
