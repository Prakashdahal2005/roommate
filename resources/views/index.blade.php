@extends('layouts.app')

@section('content')
<h1>All Profiles</h1>

<div class="profiles-grid">
    @forelse($profiles as $profile)
        <div class="profile-card">
            @if($profile->profile_picture)
                <img src="{{ asset('storage/' . $profile->profile_picture) }}" alt="{{ $profile->display_name }}" width="100">
            @else
                <div style="width:100px; height:100px; background-color:#222;"></div>
            @endif
            <a href="{{ route('profile.show',$profile) }}"><p>{{ $profile->display_name }}</p></a>
        </div>
    @empty
        <p>No profiles found.</p>
    @endforelse
</div>
@endsection
