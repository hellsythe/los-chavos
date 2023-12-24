@extends('base::back.layouts.app')

@section('title', $model->getTranslation('showed'))

@section('content')
    <?= Base::breadcrumb([$model->getRoute('index') => $model->getTranslation('plural'), $model->getTranslation('showed')]) ?>

    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($model->getFields() as $field)
                                    <tr>
                                        <th class="text-gray-700 p-2"> {{ $field['label'] }} </th>
                                        <td class="p-2"> {{ $model->{$field['name']} }} </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <th class="text-gray-700 p-2"> Ordenes </th>
                                    <td class="p-2">
                                        <a href="{{ route('order.index') }}?finished_at=2023-12-23&status=99999&garment=" class="link link-info" >
                                            Ir al detalle
                                        </a>
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
