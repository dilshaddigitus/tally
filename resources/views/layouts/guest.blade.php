@extends('layouts.base')

@section('nav')
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand mb-0 h1">Tally Connector SaaS</span>
        </div>
    </nav>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    @yield('guest-content')
                </div>
            </div>
        </div>
    </div>
@endsection
