<?php

namespace App\Core;

class Router
{
    private static array $routes = [];

    public static function get(string $path, $handler, array $middleware = []): void
    {
        self::addRoute('GET', $path, $handler, $middleware);
    }

    public static function post(string $path, $handler, array $middleware = ['csrf']): void
    {
        self::addRoute('POST', $path, $handler, $middleware);
    }

    public static function put(string $path, $handler, array $middleware = ['csrf']): void
    {
        self::addRoute('PUT', $path, $handler, $middleware);
    }

    public static function delete(string $path, $handler, array $middleware = ['csrf']): void
    {
        self::addRoute('DELETE', $path, $handler, $middleware);
    }

    private static function addRoute(string $method, string $path, $handler, array $middleware): void
    {
        self::$routes[] = [
            'method' => $method,
            'path' => '/' . trim($path, '/'),
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public static function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $path = '/' . trim($request->getPath(), '/');

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middlewares
                foreach ($route['middleware'] as $mw) {
                    self::runMiddleware($mw, $request);
                }

                // Call handler
                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func_array($handler, array_merge([$request], $params));
                    return;
                }

                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    call_user_func_array([$controller, $action], array_merge([$request], $params));
                    return;
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        if ($request->isAjax()) {
            Response::json(['error' => 'Halaman tidak ditemukan.'], 404);
        } else {
            View::render('layouts/error', ['title' => '404 Tidak Ditemukan', 'message' => 'Halaman yang Anda tuju tidak ditemukan.'], null);
        }
    }

    private static function runMiddleware(string $middleware, Request $request): void
    {
        if ($middleware === 'csrf') {
            if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'])) {
                $token = $request->input('_csrf_token') ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
                if (!CSRF::validate($token)) {
                    if ($request->isAjax()) {
                        Response::json(['error' => 'Sesi kedaluwarsa atau token CSRF tidak valid. Silakan muat ulang halaman.'], 403);
                    } else {
                        flash('error', 'Token keamanan kedaluwarsa. Silakan coba lagi.');
                        Response::back();
                    }
                }
            }
            return;
        }

        if ($middleware === 'auth') {
            if (!Auth::check()) {
                flash('error', 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.');
                Response::redirect(url('/login'));
            }
            return;
        }

        if ($middleware === 'guest') {
            if (Auth::check()) {
                Response::redirect(url('/dashboard'));
            }
            return;
        }

        if (str_starts_with($middleware, 'role:')) {
            $rolesStr = substr($middleware, 5);
            $roles = explode(',', $rolesStr);
            if (!Auth::hasRole($roles)) {
                flash('error', 'Anda tidak memiliki hak akses untuk halaman tersebut.');
                Response::redirect(url('/dashboard'));
            }
            return;
        }

        if (str_starts_with($middleware, 'perm:')) {
            $perm = substr($middleware, 5);
            if (!Auth::can($perm)) {
                flash('error', 'Anda tidak memiliki izin untuk melakukan tindakan ini.');
                Response::redirect(url('/dashboard'));
            }
            return;
        }
    }
}
