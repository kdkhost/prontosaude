<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SettingController extends Controller
{
    public function edit()
    {
        // Garante que exista ao menos uma linha de configuração padrão no banco MariaDB
        $setting = Setting::firstOrCreate(['id' => 1]);
        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = Setting::firstOrCreate(['id' => 1]);

        $data = $request->validate([
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'contact_address' => 'nullable|string',
            'logo' => 'nullable|string',
            'favicon' => 'nullable|string',
            
            // Módulos
            'enable_services' => 'required|boolean',
            'enable_doctors' => 'required|boolean',
            'enable_testimonials' => 'required|boolean',
            'enable_news' => 'required|boolean',
            'enable_appointments' => 'required|boolean',
            
            // PWA
            'pwa_name' => 'required|string|max:100',
            'pwa_short_name' => 'required|string|max:50',
            'pwa_theme_color' => 'required|string|max:7',
            'pwa_background_color' => 'required|string|max:7',
            'pwa_icon_512' => 'nullable|string',
        ]);

        $setting->update($data);

        // Gera ou atualiza dinamicamente o manifest.json do PWA baseado no painel administrativo
        $this->generateManifest($setting);

        return redirect()->route('settings.edit')->with('success', 'Configurações atualizadas com sucesso e PWA compilado!');
    }

    private function generateManifest(Setting $setting)
    {
        $manifestPath = public_path('manifest.json');
        
        $iconUrl = $setting->pwa_icon_512 ?: '/favicon.ico';

        $manifestData = [
            'name' => $setting->pwa_name,
            'short_name' => $setting->pwa_short_name,
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => $setting->pwa_background_color,
            'theme_color' => $setting->pwa_theme_color,
            'orientation' => 'portrait-primary',
            'icons' => [
                [
                    'src' => $iconUrl,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ]
            ]
        ];

        File::put($manifestPath, json_encode($manifestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
