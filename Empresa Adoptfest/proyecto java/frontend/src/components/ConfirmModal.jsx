import "../styles/ConfirmModal.css";

export default function ConfirmModal({ abierto, titulo, mensaje, onConfirmar, onCancelar, peligroso = true }) {
  if (!abierto) return null;

  return (
    <div className="cm-overlay" onClick={onCancelar}>
      <div className="cm-caja" onClick={(e) => e.stopPropagation()}>
        <div className={`cm-icono ${peligroso ? "cm-peligro" : ""}`}>
          {peligroso ? "⚠" : "?"}
        </div>
        <h3>{titulo}</h3>
        <p>{mensaje}</p>
        <div className="cm-acciones">
          <button className="cm-btn-cancelar" onClick={onCancelar}>Cancelar</button>
          <button className={`cm-btn-confirmar ${peligroso ? "cm-peligro-btn" : ""}`} onClick={onConfirmar}>
            {peligroso ? "Sí, eliminar" : "Confirmar"}
          </button>
        </div>
      </div>
    </div>
  );
}