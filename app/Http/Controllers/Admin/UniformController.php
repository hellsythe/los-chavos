<?php

namespace App\Http\Controllers\Admin;

use App\Models\Uniform;
use App\Models\UniformPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Sdkconsultoria\Core\Controllers\ResourceController;

class UniformController extends ResourceController
{
    protected $model = \App\Models\Uniform::class;

    public function show(Request $request, $id)
    {
        $model = $this->model::findModel($id);
        $model->isAuthorize('view');

        $photos = $model->photos()->get();

        return view('back.uniform.show', [
            'model' => $model,
            'photos' => $photos,
        ]);
    }

    public function storePhotos(Request $request, $id)
    {
        $uniform = $this->model::findModel($id);
        $this->authorize('update', $uniform);

        $request->validate([
            'photos' => ['required', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $created = [];
        $nextOrder = (int) $uniform->photos()->max('order') + 1;

        foreach ($request->file('photos') as $file) {
            $photo = new UniformPhoto();
            $photo->uniform_id = $uniform->id;
            $photo->order = $nextOrder;
            $photo->status = UniformPhoto::STATUS_ACTIVE;
            $photo->save();

            $extension = $file->getClientOriginalExtension();
            $path = $file->storeAs(
                'uniform/' . $uniform->id,
                $photo->id . '.' . $extension,
                'public'
            );

            $photo->photo = Storage::disk('public')->url($path);
            $photo->save();

            $created[] = $photo;
            $nextOrder++;
        }

        return response()->json([
            'ok' => true,
            'photos' => $created,
        ]);
    }

    public function deletePhoto($id)
    {
        $photo = UniformPhoto::findOrFail($id);
        $this->authorize('update', $photo->uniform);

        $url = $photo->photo;
        if ($url && strpos($url, '/storage/') !== false) {
            $relative = substr($url, strpos($url, '/storage/') + strlen('/storage/'));
            Storage::disk('public')->delete($relative);
        }

        $photo->delete();

        return response()->json(['ok' => true]);
    }
}
