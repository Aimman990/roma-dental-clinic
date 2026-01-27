<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log modifying actions
        if (in_array($request->method(), ['POST','PUT','PATCH','DELETE'])) {
            try {
                AuditLog::create([
                    'user_id' => $request->user()?->id ?? null,
                    'action' => $request->method(),
                    'route' => $request->path(),
                    'payload' => json_encode($request->all()),
                    'ip' => $request->ip(),
                ]);
            } catch (\Exception $e) {
                // swallow logging errors; keep main flow unaffected
            }
        }

        return $response;
    }
}
