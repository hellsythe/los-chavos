<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;

class SchoolController extends ResourceController
{
    protected $model = \App\Models\School::class;

    protected function customFilters($query, $request)
    {
        $id = $request->input('id');
        if ($id !== null && $id !== '') {
            $query->where('id', $id);
            return $query;
        }

        $name = $request->input('name');
        if ($name !== null && $name !== '') {
            $term = '%' . $name . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('location', 'like', $term)
                  ->orWhere('city', 'like', $term)
                  ->orWhere('colonia', 'like', $term);
            });
        }

        $pagination = (int) $request->input('pagination', 0);
        if ($pagination > 0 && $pagination <= 100) {
            $this->pagination = $pagination;
        }

        return $query;
    }
}
