@extends('base::back.layouts.app')

@section('title', $model->getTranslation('plural'))

@section('content')
    <?= Base::breadcrumb([
        $model->getTranslation('plural')
    ]) ?>
<?php
$array = array_merge($model->getIndexFields(), ['client.name']);
$array_translation = array_merge($model->getFullTranslations(), ['client.name' => 'Cliente', 'client' => 'Cliente']);
$array_search = array_merge($model->getParseSearchFilters(), [['field' => 'client']]);
?>
    <div id=app>
        <grid-view
            :create_route="false"
            :routes={{json_encode($model->getIndexRoutes())}}
            :fields="{{json_encode($array)}}"
            :filters={{json_encode($array_search)}}
            :translations='{!! json_encode($array_translation) !!}'
        />
    </div>
@endsection
