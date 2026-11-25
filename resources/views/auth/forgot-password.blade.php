@extends('layouts.guest')

@section('title', 'Forgot password')

@section('guest-content')
    <h1 class="h4 mb-3">Forgot your password?</h1>
    <p class="text-muted small">Enter your email and we'll send you a password reset link.</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus>
        </div>

        <button type="submit" class="btn btn-primary w-100">Email password reset link</button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('login') }}">Back to login</a>
    </div>
@endsection
