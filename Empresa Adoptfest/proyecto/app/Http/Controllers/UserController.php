<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{

public function index(Request $request)
{
    $query = User::query();

    if ($request->buscar) {
        $query->where(function($q) use ($request) {
            $q->where('name',  'like', '%'.$request->buscar.'%')
              ->orWhere('email', 'like', '%'.$request->buscar.'%');
        });
    }

    if ($request->rol) {
        $query->where('rol', $request->rol);
    }

    $usuarios = $query->orderBy('created_at', 'desc')->get();

    return view('admin', compact('usuarios'));
}

    public function show($id)
    {
        $usuario = User::findOrFail($id);

        return view('show', compact('usuario'));
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id);

        return view('edit', compact('usuario'));
    }

public function update(Request $request, $id)
{
    $request->validate([
        'name'     => 'required|min:3|max:100',
        'email'    => 'required|email|unique:users,email,'.$id,
        'telefono' => 'nullable|max:20',
        'rol'      => 'required|in:admin,cliente',
        'password' => 'nullable|min:6',
    ], [
        'name.required'     => 'El nombre es obligatorio.',
        'name.min'          => 'El nombre debe tener al menos 3 caracteres.',
        'email.required'    => 'El correo es obligatorio.',
        'email.email'       => 'Ingresa un correo electrónico válido.',
        'email.unique'      => 'Ese correo ya está en uso por otro usuario.',
        'telefono.max'      => 'El teléfono no puede tener más de 20 caracteres.',
        'rol.required'      => 'Selecciona un rol.',
        'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
    ]);

    $usuario = User::findOrFail($id);
    $datos = $request->only(['name', 'email', 'telefono', 'rol']);

    if ($request->filled('password')) {
        $datos['password'] = bcrypt($request->password);
    }

    $usuario->update($datos);
    return back()->with('exito', 'Usuario actualizado correctamente.');
}

    public function destroy($id)
    {
        $usuario = User::findOrFail($id);

        $usuario->delete();

        return redirect('/admin');
    }



public function perfil()
{
    return view('perfil');
}

public function actualizarPerfil(Request $request)
{
    $request->validate([
        'name'        => 'required|string|max:255',
        'email'       => 'required|email',
        'telefono'    => 'nullable|string|max:20',
        'descripcion' => 'nullable|string',
        'foto'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'password'    => 'nullable|min:6'
    ]);

    //  Buscar por el ID guardado 
    $user = User::find(session('id'));

    if (!$user) {
        return redirect('/login')->with('error', 'Sesión expirada, inicia sesión de nuevo.');
    }

    $user->name        = $request->name;
    $user->email       = $request->email;
    $user->telefono    = $request->telefono;
    $user->descripcion = $request->descripcion;

if ($request->hasFile('foto')) {

    $nombreFoto = time() . '_' .
        str_replace(' ', '_', $request->file('foto')->getClientOriginalName());

    $request->file('foto')->storeAs(
        'img/perfiles',
        $nombreFoto,
        'public'
    );

    $user->foto = $nombreFoto;
}
  

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    //  Actualizar la sesión con los nuevos datos
    session([
        'nombre'      => $user->name,
        'email'       => $user->email,
        'telefono'    => $user->telefono,
        'foto'        => $user->foto,
        'descripcion' => $user->descripcion,
    ]);

    return back()->with('success', 'Perfil actualizado correctamente ✅');
}
}