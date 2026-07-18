<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::latest()->paginate(10);
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'photo' => 'nullable|string',
            'banner' => 'nullable|string',
        ]);

        Service::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'short_description' => $request->short_description,
            'description' => $request->description,
            'photo' => $request->photo,
            'banner' => $request->banner,
        ]);

        return redirect()->route('services.index')->with('success', 'Serviço cadastrado com sucesso!');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'photo' => 'nullable|string',
            'banner' => 'nullable|string',
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'short_description' => $request->short_description,
            'description' => $request->description,
        ];

        // Atualiza imagens apenas se novos uploads foram feitos no Dropzone
        if ($request->filled('photo')) {
            $data['photo'] = $request->photo;
        }
        if ($request->filled('banner')) {
            $data['banner'] = $request->banner;
        }

        $service->update($data);

        return redirect()->route('services.index')->with('success', 'Serviço atualizado com sucesso!');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('services.index')->with('success', 'Serviço excluído com sucesso!');
    }
}
