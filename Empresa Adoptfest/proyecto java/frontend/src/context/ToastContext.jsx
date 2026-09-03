import { createContext, useContext, useState, useCallback } from "react";
import "../styles/Toast.css";

const ToastContext = createContext(null);

export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([]);

  const mostrar = useCallback((mensaje, tipo = "info") => {
    const id = Date.now();
    setToasts((prev) => [...prev, { id, mensaje, tipo }]);
    setTimeout(() => {
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, 3500);
  }, []);

  const exito = (msg) => mostrar(msg, "exito");
  const error = (msg) => mostrar(msg, "error");
  const info = (msg) => mostrar(msg, "info");

  return (
    <ToastContext.Provider value={{ exito, error, info }}>
      {children}
      <div className="toast-contenedor">
        {toasts.map((t) => (
          <div key={t.id} className={`toast toast-${t.tipo}`}>
            <span className="toast-icono">
              {t.tipo === "exito" ? "✓" : t.tipo === "error" ? "✕" : "ℹ"}
            </span>
            {t.mensaje}
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
}

export function useToast() {
  return useContext(ToastContext);
}