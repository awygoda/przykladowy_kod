<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Session;
use Hash;
use Auth;

class ClientAccountChangeEmailController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('client.konto-zmiana-maila');
    }

    public function update(Request $request)
    {
        $attributes = [
            'email' => 'e-mail',
            'password' => 'hasło',
        ];

        $this->validate($request, [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required',
        ], [], $attributes);

        if (!(Hash::check($request->get('password'), Auth::user()->password))) {
            // The passwords matches
            $errors = "Hasło nie jest poprawne.";
            return redirect()->back()->withErrors($errors);
        }
        try {
            $user = User::find(Auth::user()->id);
            $user->email = $request->email;
            $user->save();
        
            Session::flash('message', 'E-mail został zmieniony.');
            return redirect('/zmiana-maila');
        } catch (\Illuminate\Database\QueryException $e) {
            $errors = 'Wystąpił błąd zapisu do bazy danych.';
            return redirect()->back()->withErrors($errors);
        }
    }
}
