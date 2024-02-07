@extends('base::back.layouts.app')

@section('title_tab', 'Plan de trabajo')

@section('content')
<div id="calendar">
    <calendar
        :events="{{ json_encode($events) }}"
    />
</div>
@endsection
