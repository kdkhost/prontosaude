<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function calendar()
    {
        return view('admin.appointments.calendar');
    }

    public function kanban()
    {
        $appointments = Appointment::all();
        return view('admin.appointments.kanban', compact('appointments'));
    }

    public function feed(Request $request)
    {
        $start = Carbon::parse($request->query('start'))->toDateTimeString();
        $end = Carbon::parse($request->query('end'))->toDateTimeString();

        $appointments = Appointment::whereBetween('appointment_date', [$start, $end])->get();

        $events = [];
        foreach ($appointments as $app) {
            $color = '#f39c12'; // Pendente
            if ($app->status === 'Confirmado') $color = '#00a65a';
            if ($app->status === 'Cancelado') $color = '#f56954';
            if ($app->status === 'Realizado') $color = '#00c0ef';

            $events[] = [
                'id' => $app->id,
                'title' => "[{$app->type}] {$app->patient_name}",
                'start' => $app->appointment_date,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'allDay' => false
            ];
        }

        return response()->json($events);
    }

    public function updateDate(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:appointments,id',
            'date' => 'required'
        ]);

        $app = Appointment::findOrFail($request->id);
        $app->appointment_date = Carbon::parse($request->date)->toDateTimeString();
        $app->save();

        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:appointments,id',
            'status' => 'required|in:Pendente,Confirmado,Cancelado,Realizado'
        ]);

        $app = Appointment::findOrFail($request->id);
        $app->status = $request->status;
        $app->save();

        return response()->json(['success' => true]);
    }
}
