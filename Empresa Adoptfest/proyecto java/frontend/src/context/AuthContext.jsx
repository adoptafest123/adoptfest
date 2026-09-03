import { createContext, useContext, useEffect, useState } from "react";
import { obtenerPerfil } from "../services/userService";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [usuario, setUsuario] = useState(() => {
    const guardado = localStorage.getItem("usuario");
    return guardado ? JSON.parse(guardado) : null;
  });

  useEffect(() => {
    if (!usuario || usuario.foto || !localStorage.getItem("token")) return;

    obtenerPerfil()
      .then((res) => {
        const usuarioActualizado = { ...usuario, foto: res.data.foto ?? null };
        localStorage.setItem("usuario", JSON.stringify(usuarioActualizado));
        setUsuario(usuarioActualizado);
      })
      .catch(() => {});
  }, []);

  const iniciarSesion = async (datosAuth) => {
    localStorage.setItem("token", datosAuth.token);

    let usuarioCompleto = datosAuth;
    if (!datosAuth.foto) {
      try {
        const res = await obtenerPerfil();
        usuarioCompleto = {
          ...datosAuth,
          foto: res.data.foto ?? null,
        };
      } catch {
        // El login sigue siendo válido aunque el perfil no pueda cargarse.
      }
    }

    localStorage.setItem("usuario", JSON.stringify(usuarioCompleto));
    setUsuario(usuarioCompleto);
  };

  const cerrarSesion = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("usuario");
    setUsuario(null);
  };

  return (
    <AuthContext.Provider value={{ usuario, iniciarSesion, cerrarSesion }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}