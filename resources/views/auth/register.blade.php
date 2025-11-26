@extends('layouts.app')

@section('title', 'Register - AI Support Chatbot')

@section('content')
<div class="container" style="max-width: 500px; margin-top: 80px;">
    <div class="card">
        <h1 style="text-align: center; margin-bottom: 32px; color: var(--primary); font-size: 32px;">
            Create Your Account
        </h1>
        
        @if($errors->any())
            <div class="alert alert-error">
                <ul style="list-style: none;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label" for="company_name">Company Name</label>
                <input type="text" id="company_name" name="company_name" class="form-control" 
                       value="{{ old('company_name') }}" required autofocus>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="name">Your Name</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="{{ old('name') }}" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="{{ old('email') }}" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <small style="color: #6b7280; font-size: 14px;">Minimum 8 characters</small>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" 
                       class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 24px;">
                Create Account
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 24px; color: #6b7280;">
            Already have an account? 
            <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                Sign in
            </a>
        </p>
    </div>
</div>
@endsection
