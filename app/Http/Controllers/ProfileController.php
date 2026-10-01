<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Mostrar el formulario de edición del perfil
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Actualizar perfil
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,user_email,' . $user->user_id . ',user_id',
            'user_phone' => 'nullable|string|max:20',
            'user_bio' => 'nullable|string|max:500',
            'user_profession' => 'nullable|string|max:100',
            // Se eliminó 'user_avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:6|confirmed',
        ]);

        // Actualizar información básica
        $user->user_name = $request->user_name;
        $user->user_email = $request->user_email;
        $user->user_phone = $request->user_phone;
        $user->user_bio = $request->user_bio;
        $user->user_profession = $request->user_profession;

        // Gestionar la subida del avatar
        if ($request->hasFile('user_avatar')) {
            $file = $request->file('user_avatar');
            
            // Validar el archivo manualmente
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $allowedExtensions)) {
                return back()->withErrors(['user_avatar' => 'Solo se permiten imágenes JPG, PNG o GIF.']);
            }
            
            if ($file->getSize() > 2048000) { // 2MB en bytes
                return back()->withErrors(['user_avatar' => 'La imagen no debe superar 2MB.']);
            }
            
            // Eliminar el avatar antiguo si existe
            if ($user->user_avatar) {
                $oldAvatarPath = public_path('storage/' . $user->user_avatar);
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }

            // Crear el directorio de avatares si no existe
            $avatarDir = public_path('storage/avatars');
            if (!file_exists($avatarDir)) {
                mkdir($avatarDir, 0755, true);
            }

            // Generar nombre de archivo único
            $filename = 'avatar_' . $user->user_id . '_' . time() . '.' . $extension;
            
            // Mover el archivo a public/storage/avatars
            $file->move($avatarDir, $filename);
            
            $user->user_avatar = 'avatars/' . $filename;
        }

        // Gestionar el cambio de contraseña
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
            }

            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return back()->with('success', 'Perfil actualizado exitosamente.');
    }

    /**
     * Eliminar avatar
     */
    public function deleteAvatar()
    {
        $user = Auth::user();

        if ($user->user_avatar) {
            $avatarPath = public_path('storage/' . $user->user_avatar);
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
        }

        $user->user_avatar = null;
        $user->save();

        return back()->with('success', 'Avatar eliminado exitosamente.');
    }
}
