<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Doctor;
use App\Models\Setting;
use App\Models\Appointment;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function index()
    {
        $setting = Setting::firstOrCreate(['id' => 1]);
        $services = $setting->enable_services ? Service::all() : collect();
        $doctors = $setting->enable_doctors ? Doctor::where('status', 'Active')->get() : collect();
        
        return view('welcome', compact('setting', 'services', 'doctors'));
    }

    public function book(Request $request)
    {
        $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string|max:50',
            'type' => 'required|string|in:Consulta,Exame',
            'appointment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        Appointment::create([
            'patient_name' => $request->patient_name,
            'patient_phone' => $request->patient_phone,
            'type' => $request->type,
            'appointment_date' => $request->appointment_date,
            'notes' => $request->notes,
            'status' => 'Pendente'
        ]);

        return back()->with('success', 'Seu agendamento foi solicitado! Aguarde a confirmação de nossa equipe.');
    }
}
