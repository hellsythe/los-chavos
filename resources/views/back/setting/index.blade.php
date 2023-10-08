@extends('base::back.layouts.app')

@section('title', $model->getTranslation('plural'))

@section('content')
    <?= Base::breadcrumb([
        $model->getTranslation('plural')
    ]) ?>

    <div id=app>
        <grid-view
            :routes={{json_encode($model->getIndexRoutes())}}
            :fields="{{json_encode($model->getIndexFields())}}"
            :filters={{json_encode($model->getParseSearchFilters())}}
            :translations='{!! json_encode($model->getFullTranslations()) !!}'
            :template_actions="{{json_encode([
                'update' => true,
                'delete' => false,
                'create' => false,
                'show' => false,
            ])}}"
        />
    </div>
@endsection
