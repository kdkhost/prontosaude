<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    /**
     * Verifica se o sistema já foi instalado.
     * Caso contrário, redireciona para o instalador.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $installedFile = storage_path('app/installed.lock');

        // Se o sistema NÃO está instalado e o usuário NÃO está na rota de instalação
        if (!file_exists($installedFile) && !$request->is('install*')) {
            return redirect()->route('install.index');
        }

        // Se o sistema JÁ está instalado e o usuário tenta acessar o instalador
        if (file_exists($installedFile) && $request->is('install*')) {
            return redirect()->route('frontend.home');
        }

        return $next($request);
    }
}
