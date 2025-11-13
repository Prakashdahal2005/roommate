@extends('layouts.app')

@section('content')

        @if(session('profile-create-success'))
        <div class="bg-green-100 text-green-700 p-2 mb-4 rounded" id="success">
            {{ session('profile-create-success') }}
        </div>
        @endif
<div style="position: relative; margin-bottom: 16px;">
    <button id="theme-toggle" class="theme-toggle" type="button" aria-label="Toggle theme" title="Toggle theme" style="position:absolute; top: 0; right: 0;">🌙</button>
    <section style="background:#0A84FF; color:#fff; padding: 24px; border-radius: 12px; text-align:center; margin-right: 56px;">
        <h1 style="margin: 0 0 6px 0; font-size: 2rem; font-weight: 800;">Discover Roommates</h1>
        @if((float)auth()->user()?->profile?->completion_score ===0.0)
        <p style="margin: 0; font-size: 1.125rem; opacity: .95;">Browse profiles and click on name to view details.</p>
        @else
        <p style="margin: 0; font-size: 1.125rem; opacity: .95;">Your top matches , click on name to view details.</p>
        @endif
    </section>
</div>


<div class="profiles-grid">
    @forelse($profiles as $profile)
        <article class="profile-card" tabindex="0">
            @if($profile->profile_picture)
                <img class="profile-avatar" src="{{ Storage::url($profile->profile_picture) }}" alt="{{ $profile->display_name }}">
            @else
                <div class="profile-avatar" style="background:#111827; display:flex; align-items:center; justify-content:center; color:#9ca3af;">NA</div>
            @endif
            @if(auth()->user()?->profile)
            <p>{{ $profile->similarity }}%</p>
            @endif
            <a href="{{ route('profiles.show',$profile) }}" class="profile-name">{{ $profile->display_name }}</a>
            <div class="profile-meta">
                Budget: Rs.{{ number_format($profile->budget_min) }} - Rs. {{ number_format($profile->budget_max) }}
            </div>
          <div class="badges">
    @if(!empty($profile->schedule))
        <span class="badge info">{{ str_replace('_',' ', $profile->schedule) }}</span>
    @endif

    @if(isset($profile->smokes))
        <span class="badge {{ $profile->smokes ? 'warn' : 'success' }}">
            {{ $profile->smokes ? 'Smokes' : 'Non-smoker' }}
        </span>
    @endif
</div>

            <div class="profile-actions">
                <a class="btn btn-primary" href="{{ route('profiles.show',$profile) }}">View</a>
                <button class="btn btn-outline" type="button">Message</button>
            </div>
        </article>
    @empty
        <p>No profiles found.</p>
    @endforelse
</div>
@push('scripts')
<script>
    const $success = $('#success');
    if ($success.length) {
        setTimeout(() => {
            $success.fadeOut(500, function() {
                $(this).remove();
            });
        }, 3000);
    }
</script>
@endpush
@endsection
