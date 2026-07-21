

@extends('backend.layouts.app')
@section('title', 'Room Booking History')
@section('content')
<room-booking-history :is-admin='@json(auth()->user()->hasRole("admin"))'></room-booking-history>
@endsection