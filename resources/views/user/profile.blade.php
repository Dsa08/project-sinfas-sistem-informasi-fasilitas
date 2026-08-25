@extends('layouts.app')

@section('title', 'My Profile - SINFAS')

@section('navbar_title', 'My Profile')

@section('content')
<div class="profile-container">
    <div class="profile-card" id="profile-card">

        {{-- Avatar Section --}}
        <div class="profile-avatar-section">
            <div class="profile-avatar" id="profile-avatar">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <span class="profile-avatar-link" id="change-photo-link">Change Photo</span>
        </div>

        {{-- Personal Information Section --}}
        <div class="profile-section">
            <h2 class="profile-section-title">Personal Information</h2>
            <div class="profile-form">
                <div class="profile-form-group">
                    <label class="profile-form-label" for="full-name">Full Name</label>
                    <input
                        type="text"
                        class="profile-form-input"
                        id="full-name"
                        name="full_name"
                        placeholder="Enter your full name"
                    >
                </div>

                <div class="profile-form-group">
                    <label class="profile-form-label" for="nis-nip">NIS / NIP</label>
                    <input
                        type="text"
                        class="profile-form-input"
                        id="nis-nip"
                        name="nis_nip"
                        placeholder="Your NIS or NIP"
                        readonly
                    >
                </div>

                <div class="profile-form-group">
                    <label class="profile-form-label" for="email">Email</label>
                    <input
                        type="email"
                        class="profile-form-input"
                        id="email"
                        name="email"
                        placeholder="Enter your email address"
                    >
                </div>

                <div class="profile-form-group">
                    <label class="profile-form-label" for="contact-number">Contact Number</label>
                    <input
                        type="text"
                        class="profile-form-input"
                        id="contact-number"
                        name="contact_number"
                        placeholder="Enter your contact number"
                    >
                </div>
            </div>
        </div>

        {{-- Change Password Section --}}
        <div class="profile-section">
            <h2 class="profile-section-title">Change Password</h2>
            <div class="profile-form">
                <div class="profile-form-group">
                    <label class="profile-form-label" for="current-password">Current Password</label>
                    <input
                        type="password"
                        class="profile-form-input"
                        id="current-password"
                        name="current_password"
                        placeholder="Enter current password"
                    >
                </div>

                <div class="profile-form-group">
                    <label class="profile-form-label" for="new-password">New Password</label>
                    <input
                        type="password"
                        class="profile-form-input"
                        id="new-password"
                        name="new_password"
                        placeholder="Enter new password"
                    >
                </div>

                <div class="profile-form-group">
                    <label class="profile-form-label" for="confirm-password">Confirm New Password</label>
                    <input
                        type="password"
                        class="profile-form-input"
                        id="confirm-password"
                        name="new_password_confirmation"
                        placeholder="Re-enter new password"
                    >
                </div>

                <div class="profile-form-actions">
                    <button type="button" class="btn-profile-save" id="save-changes-btn">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
