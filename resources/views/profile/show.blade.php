
@extends('layouts.app')

@section('title', $profile->display_name)

@section('content')
<div class="container mx-auto p-4">
    <div class="bg-white shadow rounded-lg p-6 max-w-lg mx-auto">
        {{-- Profile Picture --}}
        <div class="text-center mb-4">
            @if($profile->profile_picture)
                <img src="{{ asset('storage/' . $profile->profile_picture) }}" alt="{{ $profile->display_name }}" class="w-32 h-32 rounded-full object-cover mx-auto">
            @else
                <div class="w-32 h-32 bg-gray-200 rounded-full flex items-center justify-center mx-auto">
                    <span class="text-gray-500">No Image</span>
                </div>
            @endif
        </div>

        {{-- Basic Info --}}
        <h2 class="text-2xl font-bold text-center mb-2">{{ $profile->display_name }}</h2>
        <p class="text-center text-gray-600 mb-4">{{ $profile->bio }}</p>

        {{-- Details --}}
        <ul class="space-y-2">
            <li><strong>Age:</strong> {{ $profile->age }}</li>
            <li><strong>Gender:</strong> {{ ucfirst($profile->gender) }}</li>
            <li><strong>Budget:</strong> ${{ $profile->budget_min }} - ${{ $profile->budget_max }}</li>
            <li><strong>Move-in Date:</strong> {{ $profile->move_in_date->format('M d, Y') }}</li>
            <li><strong>Cleanliness:</strong> {{ ucfirst($profile->cleanliness) }}</li>
            <li><strong>Schedule:</strong> {{ ucfirst($profile->schedule) }}</li>
            <li><strong>Smokes:</strong> {{ $profile->smokes ? 'Yes' : 'No' }}</li>
            <li><strong>Pets Allowed:</strong> {{ $profile->pets_ok ? 'Yes' : 'No' }}</li>
            <li><strong>Status:</strong> {{ $profile->is_active ? 'Active' : 'Inactive' }}</li>
        </ul>
    </div>
</div>
@endsection
