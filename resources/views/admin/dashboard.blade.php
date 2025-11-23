@extends('layouts.app')

@section('content')

    <!-- Dashboard Section -->
    @include('admin.sections.dashboard')

    <!-- Tickets Section -->
    @include('admin.sections.tickets')

    <!-- Users Section -->
    @include('admin.sections.users')

    <!-- Vehicles Section -->
    @include('admin.sections.vehicles')

    <!-- Types Section -->
    @include('admin.sections.types')

    <!-- Brands Section -->
    @include('admin.sections.brands')

    <!-- Plates Section -->
    @include('admin.sections.plates')

    <!-- Settings Section -->
    @include('admin.sections.settings')

@endsection