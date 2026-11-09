<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class AuthController
{
    public function showLogin(Request $request): void
    {
        if (Auth::check()) {
            Response::redirect(url('/dashboard'));
        }
        View::render('auth/login', ['title' => 'Masuk Sistem'], 'layouts/auth');
    }

    public function login(Request $request): void
    {
        $username = trim((string)$request->input('username'));
        $password = (string)$request->input('password');

        if (empty($username) || empty($password)) {
            flash('error', 'Silakan masukkan username dan password.');
            Response::back();
        }

        if (Auth::attempt($username, $password, $request)) {
            flash('success', 'Selamat datang kembali, ' . (Auth::user()['full_name'] ?? 'Pengguna') . '!');
            Response::redirect(url('/dashboard'));
        } else {
            flash('error', 'Username atau password tidak sesuai, atau akun Anda dinonaktifkan.');
            Response::back();
        }
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        flash('success', 'Anda telah berhasil keluar dari sistem.');
        Response::redirect(url('/login'));
    }
}
