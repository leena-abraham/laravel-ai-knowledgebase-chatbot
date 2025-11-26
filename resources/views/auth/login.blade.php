@extends('layouts.app')

@section('title', 'Login - AI Support Chatbot')

@section('content')
<div class="container" style="max-width: 450px; margin-top: 100px;">
    <div class="card">
        <h1 style="text-align: center; margin-bottom: 32px; color: var(--primary); font-size: 32px;">
            Welcome Back
        </h1>
        
        @if($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
        @endif
        
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="{{ old('email') }}" required autofocus>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" id="remember" name="remember" style="width: auto;">
                <label for="remember" style="margin: 0; font-weight: normal;">Remember me</label>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 24px;">
                Sign In
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 24px; color: #6b7280;">
            Don't have an account? 
            <a href="{{ route('register') }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                Create one
            </a>
        </p>
    </div>
</div>
@endsection
