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
        @guest
        <p style="margin: 0; font-size: 1.125rem; opacity: .95;">Browse profiles</p>
        @endguest
        @auth
        <p style="margin: 2; font-size: 1.125rem; opacity: .95;">Your profile is {{ auth()->user()->profile->completion_score * 100 }} % complete</p>
        @if((float)auth()->user()->profile->completion_score !== 1.0)
        <p><a class="underline" href="{{ route('profiles.edit') }}">Complete Your Profile for better matches</a></p>
        @endif
        <p style="margin: 2; font-size: 1.125rem; opacity: .95;">Your top matches</p>
        @endauth
    </section>
</div>

<div style="background: white; padding: 16px; border-radius: 8px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <form method="GET" action="{{ route('home') }}" style="display: flex; gap: 12px; align-items: end; flex-wrap: wrap;">
        
        <div>
            <label style="display: block; font-size: 0.875rem; margin-bottom: 4px;">Age</label>
            <div style="display: flex; gap: 8px;">
                <input type="number" name="age_min" placeholder="Min" value="{{ request('age_min') }}" 
                    style="width: 80px; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px;">
                <input type="number" name="age_max" placeholder="Max" value="{{ request('age_max') }}" 
                    style="width: 80px; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>
        </div>

        <div>
            <label style="display: block; font-size: 0.875rem; margin-bottom: 4px;">Gender</label>
            <select name="gender" style="padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px;">
                <option value="">Any</option>
                <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
            </select>
        </div>

        <div>
            <label style="display: block; font-size: 0.875rem; margin-bottom: 4px;">Max Budget</label>
            <input type="number" name="budget_max" placeholder="Rs. max" value="{{ request('budget_max') }}" 
                style="width: 120px; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px;">
        </div>

        <div>
            <button type="submit" style="background: #0A84FF; color: white; padding: 6px 16px; border: none; border-radius: 4px; cursor: pointer;">
                Filter
            </button>
            @if(request()->anyFilled(['age_min', 'age_max', 'gender', 'budget_max']))
            <a href="{{ route('home') }}" style="margin-left: 8px; padding: 6px 12px; color: #6b7280; text-decoration: none;">
                Clear
            </a>
            @endif
        </div>
    </form>
</div>

<div class="profiles-grid">
    @forelse($profiles as $profile)
    <article class="profile-card" tabindex="0">
        @if($profile->profile_picture)
        <img class="profile-avatar" src="{{ Storage::url($profile->profile_picture) }}" alt="{{ $profile->display_name }}">
        @else
        <div class="profile-avatar" style="background:#111827; display:flex; align-items:center; justify-content:center; color:#9ca3af;">NA</div>
        @endif
        @auth
        <p>{{ $profile->similarity }}%</p>
        @endauth
        <a href="{{ route('profiles.show',$profile) }}" class="profile-name">{{ $profile->display_name }}</a>
        <div class="profile-meta">
            Budget: Rs.{{ number_format($profile->budget_min) ?? 0}} - Rs. {{ number_format($profile->budget_max) }}
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
            <a class="btn btn-outline"
                href="{{ route('chat.show', $profile) }}">
                Message
            </a>

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