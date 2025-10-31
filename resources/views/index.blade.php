@extends('layouts.app')

@section('content')
<h1>Discover Roommates</h1>
<p>Browse profiles and click any name to view details.</p>

@guest
    <h2>All Profiles</h2>
@endguest
@auth
    <h2>Top matches</h2>
@endauth

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
