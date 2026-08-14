@extends('base::back.layouts.app')

@section('title', 'Configuración del Chatbot')

@section('content')
    <?= Base::breadcrumb([
        '#' => 'Configuración',
        'Chatbot' => 'Configuración del chatbot',
    ]) ?>

    <div id="chatbot-config">
        <chatbot-config :initial-config='@json($config)' :urls='@json($urls)'></chatbot-config>
    </div>

    @vite(['resources/back/js/app.js'])
    @verbatim
    <script>window.CSRF_TOKEN = "{{ csrf_token() }}";</script>
    @endverbatim
@endsection
