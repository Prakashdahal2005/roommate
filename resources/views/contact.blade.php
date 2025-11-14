@extends('layouts.app')

@section('title', 'Contact')

@section('content')
<div style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: white; padding: 32px 16px 18px 16px; text-align: center;">
  <h1 style="font-size: 2.5rem; font-weight: 700; margin: 0;">Contact Us</h1>
  <p style="font-size: 1.08rem; margin-top: 8px;">We'd love to hear from you</p>
</div>

<div style="display: flex; gap: 32px; max-width: 1000px; margin: 18px auto 32px auto; padding: 0 14px; align-items: flex-start; min-height: 0;">
  <!-- Left: Contact Form -->
  <div style="flex: 1; min-width: 260px;">
    <form method="POST" action="/contact" style="display: flex; flex-direction: column; gap: 16px;">
      @csrf
      <div>
        <input type="text" name="name" placeholder="Name" required
               style="width: 100%; padding: 14px 20px; border: none; border-radius: 22px; background: #e0f2fe; font-size: 1.08rem; color: #0f172a;">
      </div>
      <div>
        <input type="email" name="email" placeholder="Email" required
               style="width: 100%; padding: 14px 20px; border: none; border-radius: 22px; background: #e0f2fe; font-size: 1.08rem; color: #0f172a;">
      </div>
      <div>
        <textarea name="message" placeholder="Message" rows="4" required
                  style="width: 100%; padding: 14px 20px; border: none; border-radius: 14px; background: #e0f2fe; font-size: 1.08rem; color: #0f172a; font-family: inherit; resize: none;"></textarea>
      </div>
      <button type="submit"
              style="padding: 14px 32px; background: #06b6d4; color: white; border: none; border-radius: 22px; font-size: 1.08rem; font-weight: 600; cursor: pointer; transition: background 0.3s;">
        Send Message
      </button>
    </form>
  </div>
  <div style="flex: 1; min-width: 260px; text-align: center; align-self: flex-start;">
    <div style="background: #f0f9ff; padding: 18px; border-radius: 14px; color: #0f172a;">
      <div style="font-size: 2.5rem; margin-bottom: 12px;">✉️</div>
      <h2 style="font-size: 1.18rem; font-weight: 700; margin: 0 0 7px 0;">Get in Touch</h2>
      <p style="font-size: 1.01rem; margin: 0 0 11px 0;">Have questions? Reach out to us and we'll respond as soon as possible.</p>
      <div style="margin-top: 11px; text-align: left; background: white; padding: 12px; border-radius: 9px;">
        <p style="margin: 8px 0; font-weight: 600; font-size: 1.01rem;">📧 Email us at:</p>
        <p style="margin: 0 0 8px 0; color: #06b6d4; font-size: 1.01rem;">support@roommatefinder.com</p>
        <p style="margin: 8px 0 6px 0; font-weight: 600; font-size: 1.01rem;">💬 Response Time:</p>
        <p style="margin: 0; color: #0f172a; font-size: 1.01rem;">Usually within 24 hours</p>
      </div>
    </div>
  </div>
</div>

@endsection

