<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = (int) $request->session()->get('admin_user_id', 0);
        if ($adminId <= 0) {
            return redirect()->route('admin.login');
        }

        $adminUser = AdminUser::query()->find($adminId);
        if (!$adminUser) {
            $request->session()->forget(['admin_user_id', 'admin_username']);
            return redirect()->route('admin.login');
        }

        View::share('adminAuthUser', $adminUser);

        return $next($request);
    }
}
