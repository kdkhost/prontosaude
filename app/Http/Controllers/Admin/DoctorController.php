<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::latest()->paginate(10);
        return view('admin.doctors.index', compact('doctors'));
    }

    public function create()
    {
        return view('admin.doctors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'registro' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'clinica' => 'nullable|string|max:255',
            'status' => 'required|string|in:Active,Inactive',
            'degree' => 'nullable|string',
            'detail' => 'nullable|string',
            'photo' => 'nullable|string',
            'banner' => 'nullable|string',
        ]);

        Doctor::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'registro' => $request->registro,
            'email' => $request->email,
            'phone' => $request->phone,
            'clinica' => $request->clinica,
            'status' => $request->status,
            'degree' => $request->degree,
            'detail' => $request->detail,
            'photo' => $request->photo,
            'banner' => $request->banner,
        ]);

        return redirect()->route('doctors.index')->with('success', 'Médico cadastrado com sucesso!');
    }

    public function edit(Doctor $doctor)
    {
        return view('admin.doctors.edit', compact('doctor'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'registro' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'clinica' => 'nullable|string|max:255',
            'status' => 'required|string|in:Active,Inactive',
            'degree' => 'nullable|string',
            'detail' => 'nullable|string',
            'photo' => 'nullable|string',
            'banner' => 'nullable|string',
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'registro' => $request->registro,
            'email' => $request->email,
            'phone' => $request->phone,
            'clinica' => $request->clinica,
            'status' => $request->status,
            'degree' => $request->degree,
            'detail' => $request->detail,
        ];

        if ($request->filled('photo')) {
            $data['photo'] = $request->photo;
        }
        if ($request->filled('banner')) {
            $data['banner'] = $request->banner;
        }

        $doctor->update($data);

        return redirect()->route('doctors.index')->with('success', 'Médico atualizado com sucesso!');
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();
        return redirect()->route('doctors.index')->with('success', 'Médico excluído com sucesso!');
    }
}
