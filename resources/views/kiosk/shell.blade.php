@extends('front.layouts.app')

@section('title', 'Catálogo de Uniformes')

@section('content')
    <div id="kiosk-app">
        <div class="kiosk">
            <div class="kiosk-container">
                <div class="flex items-center justify-center h-full">
                    <div class="kiosk-shimmer rounded-3xl w-64 h-12"></div>
                </div>
            </div>
        </div>
    </div>
    @vite(['resources/front/css/app.css', 'resources/front/js/app.js'])
    @verbatim
    <script>window.CSRF_TOKEN = "{{ csrf_token() }}";</script>
    @endverbatim
@append
