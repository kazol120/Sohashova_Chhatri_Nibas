@extends('backend.layouts.app')
@section('title', 'Floor')
@section('content')
	<today-room-booking-history :is-admin='@json(auth()->user()->hasRole("admin"))'></today-room-booking-history>
@endsection 