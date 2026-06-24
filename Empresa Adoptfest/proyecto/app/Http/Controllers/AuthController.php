<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
   
    //  REGISTRO
    
    public function mostrarRegistro()
    {
        return view('registro');
    }

    public function registro(Request $request)
    {
        $request->validate([
            'nombre'            => 'required|min:2|max:100',
            'correo'            => 'required|email|unique:users,email|max:255',
            'telefono'          => 'nullable|digits_between:7,20',
            'contraseña'        => 'required|min:6|max:255',
            'contraseña_confirm'=> 'required|same:contraseña',
        ], [
            'nombre.required'             => 'El nombre es obligatorio.',
            'nombre.min'                  => 'El nombre debe tener al menos 2 caracteres.',
            'correo.required'             => 'El correo es obligatorio.',
            'correo.email'                => 'Ingresa un correo válido.',
            'correo.unique'               => 'Ese correo ya está registrado.',
            'telefono.digits_between'     => 'El teléfono debe tener entre 7 y 20 dígitos.',
            'contraseña.required'         => 'La contraseña es obligatoria.',
            'contraseña.min'              => 'La contraseña debe tener al menos 6 caracteres.',
            'contraseña_confirm.required' => 'Debes confirmar la contraseña.',
            'contraseña_confirm.same'     => 'Las contraseñas no coinciden.',
        ]);

        User::create([
            'name'     => $request->nombre,
            'email'    => $request->correo,
            'telefono' => $request->telefono,
            'password' => Hash::make($request->contraseña),
            'rol'      => 'cliente',
        ]);

        return redirect('/login')->with('exito', '¡Cuenta creada! Ya puedes iniciar sesión.');
    }

    //  LOGIN — acepta correo o teléfono
    public function mostrarLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identificador' => 'required',
            'contraseña'    => 'required',
        ], [
            'identificador.required' => 'Ingresa tu correo o teléfono.',
            'contraseña.required'    => 'Ingresa tu contraseña.',
        ]);

        $id = trim($request->identificador);

        // Buscar por correo o por teléfono
        $usuario = User::where('email', $id)
                       ->orWhere('telefono', $id)
                       ->first();

        if (!$usuario || !Hash::check($request->contraseña, $usuario->password)) {
            return back()
                ->withInput()
                ->withErrors(['identificador' => 'Credenciales incorrectas. Verifica tu correo/teléfono y contraseña.']);
        }

        // Guardar sesión
        session([
            'id'          => $usuario->id,
            'nombre'      => $usuario->name,
            'email'       => $usuario->email,
            'telefono'    => $usuario->telefono,
            'descripcion' => $usuario->descripcion,
            'foto'        => $usuario->foto,
            'rol'         => $usuario->rol,
        ]);

        // Si es admin, lo manda directo al panel de administración
        if ($usuario->rol === 'admin') {
            return redirect('/admin');
        }

        return redirect('/inicio');
    }


    //  LOGOUT
    
    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }
}