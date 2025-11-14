@extends('layouts.app')

@section('title', 'About')

@section('content')
<div style="position: relative; margin-bottom: 16px;">
    <button id="theme-toggle" class="theme-toggle" type="button" aria-label="Toggle theme" title="Toggle theme" style="position:absolute; top: 0; right: 0;">🌙</button>
    <section style="background:#0A84FF; color:#fff; padding: 24px; border-radius: 12px; text-align:center; margin-right: 56px;">
        <h1 style="margin: 0 0 6px 0; font-size: 2rem; font-weight: 800; color: #ffffff;">About Roommate</h1>
        <p style="margin: 0; font-size: 1.125rem; opacity: .95; color: rgba(255,255,255,0.95); font-weight:700;">A simple, focused app to help students find compatible roommates.</p>
    </section>
</div>

<div style="display:flex; gap:24px; align-items:flex-start; margin-top:20px;">
    <main style="flex:1; max-width:720px;">
        <div style="background:#fff; padding:20px; border-radius:12px; box-shadow: 0 6px 18px rgba(2,6,23,0.06);">
                <h2 style="margin-top:0; color:#0f172a; font-weight:700;">Find your ideal roommate</h2>
                <p style="line-height:1.6; color:#374151; font-weight:600;">Roommate is a focused roommate-finder that helps students and renters discover compatible housemates quickly. Create a profile, set your preferences, and browse verified profiles to find matches that fit your budget, schedule, and lifestyle.</p>

                <h3 style="margin-top:1rem; color:#0f172a; font-weight:700;">How it works</h3>
                <ul style="color:#374151; padding-left:1.15rem; margin-top:.5rem;">
                    <li><strong>Create a profile:</strong> Add photos, a short bio, and key preferences like budget, schedule, and smoking habits.</li>
                    <li><strong>Set filters:</strong> Narrow results by price range, move-in date, and lifestyle to see the most relevant matches.</li>
                    <li><strong>Browse & connect:</strong> View full profiles, send messages, and coordinate viewings or meetups.</li>
                    <li><strong>Stay safe:</strong> Use verification and in-app messaging to keep contact details private until you’re comfortable.</li>
                </ul>

                <h3 style="margin-top:1rem; color:#0f172a; font-weight:700;">Key features</h3>
                <ul style="color:#374151; padding-left:1.15rem; margin-top:.5rem;">
                    <li>Searchable profiles with photos and preferences</li>
                    <li>Simple matching based on budget and schedule</li>
                    <li>In-app messaging so contact details stay private</li>
                    <li>Seeded demo accounts to try the app quickly</li>
                </ul>

                <h3 style="margin-top:1rem; color:#0f172a; font-weight:700;">Safety tips</h3>
                <ul style="color:#374151; padding-left:1.15rem; margin-top:.5rem;">
                    <li>Meet in public places for initial meetings.</li>
                    <li>Verify basic details and ask for references when possible.</li>
                    <li>Keep personal contact information private until you trust the person.</li>
                </ul>
        </div>
    </main>

    <aside style="width:360px;">
        <div style="background:#f1f5f9; padding:18px; border-radius:12px; border:1px solid #e6eef8;">
            <h3 style="margin-top:0; color:#0f172a; font-weight:700;">Get started</h3>
            <p style="color:#374151; font-weight:600;">Create an account to make your profile public and receive personalized suggestions.</p>

            <div style="display:flex; gap:8px; margin-top:12px;">
                @if (Route::has('register'))
                    <a class="btn btn-primary" href="{{ route('register') }}">Create account</a>
                @endif

                @if (Route::has('login'))
                    <a class="btn btn-outline" href="{{ route('login') }}">Sign in</a>
                @endif
            </div>

            <div style="margin-top:16px; color:#6B7280; font-size:.95rem;">
                <strong style="color:#0f172a; font-weight:700;">Need help?</strong>
                <p style="margin:6px 0 0 0; color:#374151; font-weight:600;">Visit the contact page or open an issue on the repository for questions.</p>
            </div>
        </div>
    </aside>
</div>

@endsection

