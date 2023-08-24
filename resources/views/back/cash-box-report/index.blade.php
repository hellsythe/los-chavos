@extends('base::back.layouts.app')

@section('title', $model->getTranslation('plural'))

@section('content')
    <?= Base::breadcrumb([
        $model->getTranslation('plural')
    ]) ?>
    <div class="mb-2">
        <a class="btn btn-primary" href="/admin/cash-box-report/create?type=total_embroidery">Nuevo Corte de Caja Para Bordado</a>
        <a class="btn btn-default" href="/admin/cash-box-report/create?type=total_print">Nuevo Corte de Caja Para Estampado</a>
    </div>
    @if (!auth()->user()->hasRole(['super-admin']) )
    @else

        <div id=app>
            <grid-view
                :routes={{json_encode($model->getIndexRoutes())}}
                :fields="{{json_encode($model->getIndexFields())}}"
                :filters={{json_encode($model->getParseSearchFilters())}}
                :translations='{!! json_encode($model->getFullTranslations()) !!}'
                :template_actions="{{json_encode([
                    'update' => false,
                    'delete' => false,
                    'create' => false,
                    'show' => true,
                ])}}"
            />
        </div>
    @endif
@endsection
