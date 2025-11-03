@extends('layouts.app')

@section('content')
<div style="position: relative; margin-bottom: 16px;">
    <button id="theme-toggle" class="theme-toggle" type="button" aria-label="Toggle theme" title="Toggle theme" style="position:absolute; top: 0; right: 0;">🌙</button>
    <section style="background:#0A84FF; color:#fff; padding: 24px; border-radius: 12px; text-align:center; margin-right: 56px;">
        <h1 style="margin: 0 0 6px 0; font-size: 2rem; font-weight: 800;">Discover Roommates</h1>
        <p style="margin: 0; font-size: 1.125rem; opacity: .95;">Browse profiles and click any name to view details.</p>
    </section>
</div>

<div class="profiles-grid">
    @forelse($profiles as $profile)
        <article class="profile-card" tabindex="0">
            @if($profile->profile_picture)
                <img class="profile-avatar" src="{{ asset('storage/' . $profile->profile_picture) }}" alt="{{ $profile->display_name }}">
            @else
                <div class="profile-avatar" style="background:#111827; display:flex; align-items:center; justify-content:center; color:#9ca3af;">NA</div>
            @endif

            <a href="{{ route('profile.show',$profile) }}" class="profile-name">{{ $profile->display_name }}</a>
            <div class="profile-meta">
                Budget: Rs.{{ number_format($profile->budget_min) }} - Rs. {{ number_format($profile->budget_max) }}
            </div>
            <div class="badges">
                <span class="badge info">{{ str_replace('_',' ', $profile->schedule) }}</span>
                <span class="badge {{ $profile->smokes ? 'warn' : 'success' }}">{{ $profile->smokes ? 'Smokes' : 'Non-smoker' }}</span>
            </div>
            <div class="profile-actions">
                <a class="btn btn-primary" href="{{ route('profile.show',$profile) }}">View</a>
                <button class="btn btn-outline" type="button">Message</button>
            </div>
        </article>
    @empty
        <p>No profiles found.</p>
    @endforelse
</div>
@endsection
