<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portofolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortofolioController extends Controller
{
    public function index()
    {
        return view('admin.portofolio.index');
    }

    public function getData(Request $request): JsonResponse
    {
        $query = $request->boolean('trashed')
            ? Portofolio::onlyTrashed()
            : Portofolio::query();

        $photos = $query->orderByDesc('created_at')->get();

        return response()->json([
            'photos' => $photos->map(fn($p) => [
                'id'         => $p->id,
                'image_url'  => Storage::url($p->image_path),
                'keterangan' => $p->keterangan ?? '',
                'tanggal'    => $p->created_at?->format('d M Y') ?? '-',
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image'      => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $path = $request->file('image')->store('portofolio', 'public');

        Portofolio::create([
            'image_path' => $path,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json(['message' => 'Foto berhasil ditambahkan.']);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $portofolio = Portofolio::findOrFail($id);

        $request->validate([
            'keterangan' => 'nullable|string|max:500',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $data = ['keterangan' => $request->keterangan];

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($portofolio->image_path);
            $data['image_path'] = $request->file('image')->store('portofolio', 'public');
        }

        $portofolio->update($data);

        return response()->json(['message' => 'Foto berhasil diperbarui.']);
    }

    public function destroy($id): JsonResponse
    {
        Portofolio::findOrFail($id)->delete();
        return response()->json(['message' => 'Foto berhasil dihapus.']);
    }

    public function restore($id): JsonResponse
    {
        Portofolio::withTrashed()->findOrFail($id)->restore();
        return response()->json(['message' => 'Foto berhasil dipulihkan.']);
    }
}
