@extends('base::back.layouts.app')

@section('title', $model->getTranslation('showed'))

@section('content')
    <?= Base::breadcrumb([$model->getRoute('index') => $model->getTranslation('plural'), $model->getTranslation('showed')]) ?>
    <div id=app>

    </div>

    @yield('model')
@endsection
