@extends('base::back.layouts.app')

@section('title', $model->getTranslation('plural'))

@section('content')
    <?= Base::breadcrumb([
        $model->getTranslation('plural')
    ]) ?>
    @if (!auth()->user()->hasRole(['super-admin']) )
        <a class="btn btn-active" href="/admin/cash-box-report/create">Nuevo Corte de Caja</a>
    @else
        <div id=app>
            <grid-view
                create_route="{{$model->getRoute('create')}}"
                :routes={{json_encode($model->getIndexRoutes())}}
                :fields="{{json_encode($model->getIndexFields())}}"
                :filters={{json_encode($model->getParseSearchFilters())}}
                :translations='{!! json_encode($model->getFullTranslations()) !!}'
                :template_actions="{{json_encode([
                    'update' => false,
                    'delete' => false,
                    'show' => true,
                ])}}"
            />
        </div>
    @endif
@endsection
