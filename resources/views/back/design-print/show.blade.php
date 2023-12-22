@extends('base::back.layouts.app')

@section('title', $model->getTranslation('showed'))

@section('content')
    <?= Base::breadcrumb([$model->getRoute('index') => $model->getTranslation('plural'), $model->getTranslation('showed')]) ?>
    <div id=app>

        <div class="mb-2 flex flex-row">
            <a type="button" href="{{ $model->getRoute('update', $model->getKeyId()) }}" class="btn btn-primary">
                {!! $model->getTranslation('edit') !!} </a>
            <delete-model :translations='{!! json_encode($model->getFullTranslations()) !!}' :routes='{{ json_encode($model->getIndexRoutes()) }}'
                :model_id='{{ $model->id }}' />
        </div>
    </div>

    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            @php
                                $fields = $model->getFields();
                            @endphp
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <th class="text-gray-700 p-2"> {{ $fields['name']['label'] }} </th>
                                    <td class="p-2"> {{ $model->name }} </td>
                                </tr>
                                <tr>
                                    <th class="text-gray-700 p-2"> {{ $fields['price']['label'] }} </th>
                                    <td class="p-2"> {{ $model->price }} </td>
                                </tr>
                                <tr>
                                    <th class="text-gray-700 p-2"> {{ $fields['media']['label'] }} </th>
                                    <td class="p-2">
                                        <a href="{{ $model->media }}" class="link link-info" target="_blank"> Ver </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @yield('model')
@endsection
