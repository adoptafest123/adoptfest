// src/pages/Donaciones.jsx
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { useToast } from "../context/ToastContext";
import { 
  registrarDonacionEspecie, 
  crearOrdenDonacionDinero 
} from "../services/donacionService";
import Navbar from "../components/Navbar";
import Footer from "../components/Footer";
import "../styles/Donaciones.css";

const IconDonacionDinero = () => <span style={{ fontSize: "2rem" }}>💰</span>;
const IconDonacionEspecie = () => <span style={{ fontSize: "2rem" }}>📦</span>;

function Donaciones() {
  const { usuario } = useAuth();
  const toast = useToast();
  const navigate = useNavigate();

  const [tipoDonacion, setTipoDonacion] = useState(null);
  const [monto, setMonto] = useState("");
  const [cargandoDinero, setCargandoDinero] = useState(false);

  const [formEspecie, setFormEspecie] = useState({
    categoria: "",
    especieDestino: "",
    // Campos para ALIMENTO
    tipoAlimento: "",
    marcaAlimento: "",
    fechaVencimiento: "",
    // Campos para JUGUETES
    tipoJuguete: "",
    tamanioJuguete: "",
    estadoJuguete: "",
    // Campos para COBIJAS_CAMAS
    tipoCama: "",
    tamanioCama: "",
    estadoCama: "",
    // Campos comunes
    cantidad: 1,
    descripcionAdicional: "",
    tipoRecoleccion: "vienen",
    direccionRecoleccion: "",
    telefonoContacto: "",
  });
  const [cargandoEspecie, setCargandoEspecie] = useState(false);
  const [donacionExitosa, setDonacionExitosa] = useState(false);
  const [donacionData, setDonacionData] = useState(null);

  const handleEspecieChange = (e) => {
    const { name, value } = e.target;
    setFormEspecie((prev) => ({ ...prev, [name]: value }));
  };

  const handleCategoriaChange = (e) => {
    const categoria = e.target.value;
    setFormEspecie((prev) => ({
      ...prev,
      categoria: categoria,
      tipoAlimento: "",
      marcaAlimento: "",
      fechaVencimiento: "",
      tipoJuguete: "",
      tamanioJuguete: "",
      estadoJuguete: "",
      tipoCama: "",
      tamanioCama: "",
      estadoCama: "",
      cantidad: 1,
    }));
  };

  const handleDonarDinero = async (e) => {
    e.preventDefault();
    if (!usuario) {
      toast.error("Debes iniciar sesión para donar.");
      navigate("/login");
      return;
    }

    const montoNum = parseFloat(monto);
    if (isNaN(montoNum) || montoNum < 1 || montoNum > 10000) {
      toast.error("El monto debe ser entre $1 y $10,000.");
      return;
    }

    setCargandoDinero(true);
    try {
      const response = await crearOrdenDonacionDinero({ monto: montoNum });
      const { linkAprobacion } = response.data;
      window.location.href = linkAprobacion;
    } catch (error) {
      toast.error(error.response?.data?.mensaje || "No se pudo procesar la donación.");
    } finally {
      setCargandoDinero(false);
    }
  };

  const handleDonarEspecie = async (e) => {
    e.preventDefault();
    if (!usuario) {
      toast.error("Debes iniciar sesión para donar.");
      navigate("/login");
      return;
    }

    // Validaciones generales
    if (!formEspecie.categoria) {
      toast.error("Selecciona la categoría de tu donación.");
      return;
    }
    if (!formEspecie.especieDestino) {
      toast.error("Selecciona para qué especie es la donación.");
      return;
    }
    if (!formEspecie.tipoRecoleccion) {
      toast.error("Selecciona cómo prefieres entregar la donación.");
      return;
    }
    if (formEspecie.tipoRecoleccion === "vienen") {
      if (!formEspecie.direccionRecoleccion || formEspecie.direccionRecoleccion.trim().length < 10) {
        toast.error("La dirección debe tener al menos 10 caracteres.");
        return;
      }
    }
    if (!formEspecie.telefonoContacto || formEspecie.telefonoContacto.length < 7) {
      toast.error("Ingresa un teléfono de contacto válido.");
      return;
    }

    // Validaciones según categoría
    if (formEspecie.categoria === "ALIMENTO") {
      if (!formEspecie.tipoAlimento) {
        toast.error("Selecciona el tipo de alimento.");
        return;
      }
      if (!formEspecie.marcaAlimento || formEspecie.marcaAlimento.trim().length < 2) {
        toast.error("Ingresa la marca del alimento.");
        return;
      }
      if (!formEspecie.fechaVencimiento) {
        toast.error("La fecha de vencimiento es obligatoria.");
        return;
      }
      const fechaVenc = new Date(formEspecie.fechaVencimiento);
      const hoy = new Date();
      hoy.setHours(0, 0, 0, 0);
      if (fechaVenc < hoy) {
        toast.error("La fecha de vencimiento no puede ser en el pasado.");
        return;
      }
    }

    if (formEspecie.categoria === "JUGUETES") {
      if (!formEspecie.tipoJuguete) {
        toast.error("Selecciona el tipo de juguete.");
        return;
      }
      if (!formEspecie.tamanioJuguete) {
        toast.error("Selecciona el tamaño del juguete.");
        return;
      }
      if (!formEspecie.estadoJuguete) {
        toast.error("Selecciona el estado del juguete.");
        return;
      }
    }

    if (formEspecie.categoria === "COBIJAS_CAMAS") {
      if (!formEspecie.tipoCama) {
        toast.error("Selecciona el tipo de cama/cobija.");
        return;
      }
      if (!formEspecie.tamanioCama) {
        toast.error("Selecciona el tamaño.");
        return;
      }
      if (!formEspecie.estadoCama) {
        toast.error("Selecciona el estado.");
        return;
      }
    }

    setCargandoEspecie(true);
    try {
      let descripcionCompleta = "";

      if (formEspecie.categoria === "ALIMENTO") {
        descripcionCompleta = 
          `Tipo: ${formEspecie.tipoAlimento}, ` +
          `Marca: ${formEspecie.marcaAlimento}, ` +
          `Vencimiento: ${formEspecie.fechaVencimiento}, ` +
          `Cantidad: ${formEspecie.cantidad}` +
          (formEspecie.descripcionAdicional ? `, ${formEspecie.descripcionAdicional}` : "");
      } else if (formEspecie.categoria === "JUGUETES") {
        descripcionCompleta = 
          `Tipo: ${formEspecie.tipoJuguete}, ` +
          `Tamaño: ${formEspecie.tamanioJuguete}, ` +
          `Estado: ${formEspecie.estadoJuguete}, ` +
          `Cantidad: ${formEspecie.cantidad}` +
          (formEspecie.descripcionAdicional ? `, ${formEspecie.descripcionAdicional}` : "");
      } else if (formEspecie.categoria === "COBIJAS_CAMAS") {
        descripcionCompleta = 
          `Tipo: ${formEspecie.tipoCama}, ` +
          `Tamaño: ${formEspecie.tamanioCama}, ` +
          `Estado: ${formEspecie.estadoCama}, ` +
          `Cantidad: ${formEspecie.cantidad}` +
          (formEspecie.descripcionAdicional ? `, ${formEspecie.descripcionAdicional}` : "");
      }

      const direccionFinal = formEspecie.tipoRecoleccion === "llevo" 
        ? "Lo llevaré al refugio" 
        : formEspecie.direccionRecoleccion;

      const payload = {
        categoria: formEspecie.categoria,
        especieDestino: formEspecie.especieDestino,
        cantidad: parseInt(formEspecie.cantidad),
        descripcion: descripcionCompleta,
        direccionRecoleccion: direccionFinal,
        telefonoContacto: formEspecie.telefonoContacto,
      };
      
      const response = await registrarDonacionEspecie(payload);
      
      setDonacionData({
        categoria: formEspecie.categoria,
        cantidad: formEspecie.cantidad,
        tipoRecoleccion: formEspecie.tipoRecoleccion,
      });
      
      setDonacionExitosa(true);
      toast.exito("¡Donación registrada con éxito!");
      
      setFormEspecie({
        categoria: "",
        especieDestino: "",
        tipoAlimento: "",
        marcaAlimento: "",
        fechaVencimiento: "",
        tipoJuguete: "",
        tamanioJuguete: "",
        estadoJuguete: "",
        tipoCama: "",
        tamanioCama: "",
        estadoCama: "",
        cantidad: 1,
        descripcionAdicional: "",
        tipoRecoleccion: "vienen",
        direccionRecoleccion: "",
        telefonoContacto: "",
      });
    } catch (error) {
      toast.error(error.response?.data?.mensaje || "No se pudo registrar la donación.");
    } finally {
      setCargandoEspecie(false);
    }
  };

  const volverADonar = () => {
    setDonacionExitosa(false);
    setDonacionData(null);
  };

  const renderSeleccionTipo = () => (
    <div className="donaciones-selector">
      <h2>¿Cómo quieres ayudar?</h2>
      <p>Elige el tipo de donación que prefieras hacer</p>
      <div className="donaciones-opciones">
        <button className="donacion-tipo-btn" onClick={() => setTipoDonacion("dinero")}>
          <IconDonacionDinero />
          <span>Donar Dinero</span>
          <small>Apoya económicamente</small>
        </button>
        <button className="donacion-tipo-btn" onClick={() => setTipoDonacion("especie")}>
          <IconDonacionEspecie />
          <span>Donar Insumos</span>
          <small>Alimentos, juguetes, cobijas y camas</small>
        </button>
      </div>
    </div>
  );

  const renderDonacionDinero = () => (
    <div className="donacion-formulario">
      <button className="btn-volver" onClick={() => setTipoDonacion(null)}>
        ← Volver
      </button>
      <h2>💰 Donación en Dinero</h2>
      <p className="subtitulo">Tu aporte económico ayuda a cubrir gastos veterinarios, alimentación y rescate de animales.</p>
      
      <form onSubmit={handleDonarDinero}>
        <div className="campo">
          <label htmlFor="monto">Monto a donar (USD)</label>
          <div className="monto-input-group">
            <span className="moneda-simbolo">$</span>
            <input
              type="number"
              id="monto"
              min="1"
              max="10000"
              step="0.01"
              value={monto}
              onChange={(e) => setMonto(e.target.value)}
              placeholder="Ej: 25.00"
              required
            />
          </div>
          <small>Mínimo $1 - Máximo $10,000</small>
        </div>

        <div className="montos-sugeridos">
          <button type="button" onClick={() => setMonto("10")}>$10</button>
          <button type="button" onClick={() => setMonto("25")}>$25</button>
          <button type="button" onClick={() => setMonto("50")}>$50</button>
          <button type="button" onClick={() => setMonto("100")}>$100</button>
        </div>

        <button type="submit" className="btn-donar" disabled={cargandoDinero}>
          {cargandoDinero ? "Procesando..." : "Donar con PayPal"}
        </button>
        <p className="nota-seguridad">
          🔒 Procesado de forma segura a través de PayPal
        </p>
      </form>
    </div>
  );

  const renderCamposAlimento = () => (
    <>
      <div className="campo">
        <label htmlFor="tipoAlimento">Tipo de alimento *</label>
        <select
          id="tipoAlimento"
          name="tipoAlimento"
          value={formEspecie.tipoAlimento}
          onChange={handleEspecieChange}
          required
        >
          <option value="">Selecciona el tipo</option>
          <option value="Croquetas secas">🍪 Croquetas secas</option>
          <option value="Comida húmeda">🥫 Comida húmeda (latas/sobres)</option>
          <option value="Snacks/ premios">🦴 Snacks / Premios</option>
          <option value="Alimento especializado">💊 Alimento especializado (medicado, cachorro, senior)</option>
        </select>
      </div>

      <div className="campo">
        <label htmlFor="marcaAlimento">Marca *</label>
        <input
          type="text"
          id="marcaAlimento"
          name="marcaAlimento"
          value={formEspecie.marcaAlimento}
          onChange={handleEspecieChange}
          placeholder="Ej: Purina, Royal Canin, Pedigree, Whiskas..."
          required
        />
      </div>

      <div className="campo">
        <label htmlFor="fechaVencimiento">Fecha de vencimiento *</label>
        <input
          type="date"
          id="fechaVencimiento"
          name="fechaVencimiento"
          value={formEspecie.fechaVencimiento}
          onChange={handleEspecieChange}
          min={new Date().toISOString().split("T")[0]}
          required
        />
        <small>La fecha no puede ser anterior a hoy</small>
      </div>
    </>
  );

  const renderCamposJuguete = () => (
    <>
      <div className="campo">
        <label htmlFor="tipoJuguete">Tipo de juguete *</label>
        <select
          id="tipoJuguete"
          name="tipoJuguete"
          value={formEspecie.tipoJuguete}
          onChange={handleEspecieChange}
          required
        >
          <option value="">Selecciona el tipo</option>
          <option value="Pelota">⚽ Pelota</option>
          <option value="Cuerda">🪢 Cuerda / Trenzado</option>
          <option value="Peluche">🧸 Peluche</option>
          <option value="Mordedor">🦴 Mordedor / Hueso</option>
          <option value="Juguete interactivo">🧩 Juguete interactivo</option>
          <option value="Juguete con sonido">🔊 Juguete con sonido</option>
        </select>
      </div>

      <div className="campo">
        <label htmlFor="tamanioJuguete">Tamaño *</label>
        <select
          id="tamanioJuguete"
          name="tamanioJuguete"
          value={formEspecie.tamanioJuguete}
          onChange={handleEspecieChange}
          required
        >
          <option value="">Selecciona el tamaño</option>
          <option value="Pequeño (para gatos/cachorros)">🐱 Pequeño (gatos/cachorros)</option>
          <option value="Mediano (perros pequeños/medianos)">🐕 Mediano (perros pequeños/medianos)</option>
          <option value="Grande (perros grandes)">🦮 Grande (perros grandes)</option>
          <option value="Extra grande (perros gigantes)">🐕‍🦺 Extra grande (perros gigantes)</option>
        </select>
      </div>

      <div className="campo">
        <label htmlFor="estadoJuguete">Estado *</label>
        <select
          id="estadoJuguete"
          name="estadoJuguete"
          value={formEspecie.estadoJuguete}
          onChange={handleEspecieChange}
          required
        >
          <option value="">Selecciona el estado</option>
          <option value="Nuevo (con etiquetas)">✨ Nuevo (con etiquetas)</option>
          <option value="Como nuevo (sin usar)">🌟 Como nuevo (sin usar)</option>
          <option value="Usado en buen estado">👍 Usado en buen estado</option>
          <option value="Usado, requiere lavado">🧼 Usado, requiere lavado</option>
        </select>
      </div>
    </>
  );

  const renderCamposCama = () => (
    <>
      <div className="campo">
        <label htmlFor="tipoCama">Tipo de cama/cobija *</label>
        <select
          id="tipoCama"
          name="tipoCama"
          value={formEspecie.tipoCama}
          onChange={handleEspecieChange}
          required
        >
          <option value="">Selecciona el tipo</option>
          <option value="Cama acolchada">🛏️ Cama acolchada</option>
          <option value="Cama tipo cojín">🟤 Cama tipo cojín</option>
          <option value="Cama con bordes">📦 Cama con bordes / nido</option>
          <option value="Colchoneta / Frazada">🧶 Colchoneta / Frazada</option>
          <option value="Cobija / Manta">🛌 Cobija / Manta</option>
          <option value="Cama impermeable">💧 Cama impermeable</option>
        </select>
      </div>

      <div className="campo">
        <label htmlFor="tamanioCama">Tamaño *</label>
        <select
          id="tamanioCama"
          name="tamanioCama"
          value={formEspecie.tamanioCama}
          onChange={handleEspecieChange}
          required
        >
          <option value="">Selecciona el tamaño</option>
          <option value="Pequeño (gatos/perros pequeños)">🐱 Pequeño (gatos/perros pequeños)</option>
          <option value="Mediano (perros medianos)">🐕 Mediano (perros medianos)</option>
          <option value="Grande (perros grandes)">🦮 Grande (perros grandes)</option>
          <option value="Extra grande (perros gigantes)">🐕‍🦺 Extra grande (perros gigantes)</option>
          <option value="Varias tallas">📏 Varias tallas</option>
        </select>
      </div>

      <div className="campo">
        <label htmlFor="estadoCama">Estado *</label>
        <select
          id="estadoCama"
          name="estadoCama"
          value={formEspecie.estadoCama}
          onChange={handleEspecieChange}
          required
        >
          <option value="">Selecciona el estado</option>
          <option value="Nuevo (con etiquetas)">✨ Nuevo (con etiquetas)</option>
          <option value="Como nuevo (sin usar)">🌟 Como nuevo (sin usar)</option>
          <option value="Usado en buen estado">👍 Usado en buen estado</option>
          <option value="Usado, requiere lavado">🧼 Usado, requiere lavado</option>
          <option value="Usado, requiere reparación menor">🔧 Usado, requiere reparación menor</option>
        </select>
      </div>
    </>
  );

  const renderMensajeExito = () => {
    const categoriaEmoji = {
      ALIMENTO: "🍖",
      JUGUETES: "🧸",
      COBIJAS_CAMAS: "🛏️"
    };

    const categoriaTexto = {
      ALIMENTO: "Alimento",
      JUGUETES: "Juguetes",
      COBIJAS_CAMAS: "Cobijas y camas"
    };

    const recoleccionTexto = donacionData?.tipoRecoleccion === "llevo" 
      ? "llevarás al refugio" 
      : "recolectaremos en tu dirección";

    return (
      <div className="donacion-formulario" style={{ textAlign: "center", padding: "40px 30px" }}>
        <div style={{ fontSize: "4rem", marginBottom: "16px" }}>
          {categoriaEmoji[donacionData?.categoria] || "🎉"}
        </div>
        
        <h2 style={{ 
          fontFamily: "var(--font-display)", 
          color: "#1c6b45", 
          marginBottom: "8px",
          fontSize: "1.8rem"
        }}>
          ¡Donación registrada! 🎉
        </h2>
        
        <p style={{ 
          fontSize: "1.1rem", 
          color: "#2d8a68", 
          marginBottom: "20px",
          fontWeight: "500"
        }}>
          {donacionData?.cantidad} unidad(es) de {categoriaTexto[donacionData?.categoria] || "insumos"} para {formEspecie.especieDestino === "PERRO" ? "🐶 perros" : "🐱 gatos"}
        </p>

        <div style={{ 
          background: "#e8f5f0", 
          borderRadius: "16px", 
          padding: "20px 24px",
          marginBottom: "24px",
          textAlign: "left"
        }}>
          <p style={{ 
            fontSize: "1rem", 
            color: "#0c2b21", 
            lineHeight: "1.7",
            margin: 0
          }}>
            📦 <strong>¿Qué sigue?</strong>
          </p>
          <ul style={{ 
            margin: "10px 0 0", 
            paddingLeft: "20px", 
            color: "#1c6b45",
            fontSize: "0.95rem",
            lineHeight: "1.8"
          }}>
            <li>✅ Hemos recibido tu donación correctamente</li>
            <li>📞 En las próximas <strong>24-48 horas</strong> nos pondremos en contacto contigo</li>
            <li>📍 {donacionData?.tipoRecoleccion === "llevo" 
                ? "Coordinaremos el día y hora para que lleves tu donación al refugio" 
                : "Coordinaremos el día y hora para pasar a recoger tu donación"}</li>
            <li>🐾 ¡Gracias por ayudar a los animales!</li>
          </ul>
        </div>

        <div style={{ 
          background: "#fef3c7", 
          borderRadius: "12px", 
          padding: "14px 18px",
          marginBottom: "24px",
          border: "1px solid #fde68a"
        }}>
          <p style={{ margin: 0, fontSize: "0.95rem", color: "#92400e" }}>
            💡 <strong>Importante:</strong> Te contactaremos al número <strong>{formEspecie.telefonoContacto}</strong> 
            que registraste. Asegúrate de estar atento/a a nuestras llamadas o mensajes.
          </p>
        </div>

        <button 
          onClick={volverADonar}
          className="btn-donar"
          style={{ 
            maxWidth: "300px", 
            margin: "0 auto",
            background: "#1c6b45"
          }}
        >
          ✅ Hacer otra donación
        </button>
        
        <button 
          onClick={() => navigate("/")}
          style={{ 
            display: "block",
            margin: "12px auto 0",
            background: "transparent",
            border: "none",
            color: "#577067",
            fontSize: "14px",
            cursor: "pointer",
            textDecoration: "underline"
          }}
        >
          🏠 Volver al inicio
        </button>
      </div>
    );
  };

  const renderDonacionEspecie = () => {
    const categoria = formEspecie.categoria;

    if (donacionExitosa) {
      return renderMensajeExito();
    }

    return (
      <div className="donacion-formulario">
        <button className="btn-volver" onClick={() => setTipoDonacion(null)}>
          ← Volver
        </button>
        <h2>📦 Donación en Especie</h2>
        <p className="subtitulo">
          Completa la información de tu donación para que podamos gestionarla correctamente.
        </p>

        <form onSubmit={handleDonarEspecie}>
          {/* Categoría */}
          <div className="campo">
            <label htmlFor="categoria">Categoría *</label>
            <select
              id="categoria"
              name="categoria"
              value={formEspecie.categoria}
              onChange={handleCategoriaChange}
              required
            >
              <option value="">Selecciona una categoría</option>
              <option value="ALIMENTO">🍖 Alimento</option>
              <option value="JUGUETES">🧸 Juguetes</option>
              <option value="COBIJAS_CAMAS">🛏️ Cobijas y camas</option>
            </select>
          </div>

          {/* Especie destino */}
          <div className="campo">
            <label htmlFor="especieDestino">Especie destino *</label>
            <select
              id="especieDestino"
              name="especieDestino"
              value={formEspecie.especieDestino}
              onChange={handleEspecieChange}
              required
            >
              <option value="">Selecciona una especie</option>
              <option value="PERRO">🐶 Perros</option>
              <option value="GATO">🐱 Gatos</option>
            </select>
          </div>

          {/* Cantidad */}
          <div className="campo">
            <label htmlFor="cantidad">Cantidad de unidades *</label>
            <input
              type="number"
              id="cantidad"
              name="cantidad"
              min="1"
              max="50"
              value={formEspecie.cantidad}
              onChange={handleEspecieChange}
              required
            />
            <small>Mínimo 1 - Máximo 50 unidades</small>
          </div>

          {/* Campos específicos según categoría */}
          {categoria === "ALIMENTO" && renderCamposAlimento()}
          {categoria === "JUGUETES" && renderCamposJuguete()}
          {categoria === "COBIJAS_CAMAS" && renderCamposCama()}

          {/* Descripción adicional (opcional) */}
          <div className="campo">
            <label htmlFor="descripcionAdicional">Descripción adicional (opcional)</label>
            <textarea
              id="descripcionAdicional"
              name="descripcionAdicional"
              rows="3"
              value={formEspecie.descripcionAdicional}
              onChange={handleEspecieChange}
              placeholder="Detalles adicionales que quieras agregar..."
              maxLength="500"
            />
            <small>Máximo 500 caracteres</small>
          </div>

          {/* Tipo de recolección */}
          <div className="campo">
            <label htmlFor="tipoRecoleccion">¿Cómo prefieres entregar la donación? *</label>
            <select
              id="tipoRecoleccion"
              name="tipoRecoleccion"
              value={formEspecie.tipoRecoleccion}
              onChange={handleEspecieChange}
              required
            >
              <option value="">Selecciona una opción</option>
              <option value="vienen">📦 Que vengan por ella a mi dirección</option>
              <option value="llevo">🏠 Yo la llevo al refugio</option>
            </select>
          </div>

          {formEspecie.tipoRecoleccion === "vienen" && (
            <div className="campo">
              <label htmlFor="direccionRecoleccion">Dirección de recolección *</label>
              <input
                type="text"
                id="direccionRecoleccion"
                name="direccionRecoleccion"
                value={formEspecie.direccionRecoleccion}
                onChange={handleEspecieChange}
                placeholder="Calle, número, ciudad, barrio, referencias"
                required
              />
              <small>Mínimo 10 caracteres</small>
            </div>
          )}

          {formEspecie.tipoRecoleccion === "llevo" && (
            <div className="campo" style={{ background: "#e8f5f0", padding: "12px 16px", borderRadius: "10px", marginBottom: "18px" }}>
              <p style={{ margin: 0, fontSize: "14px", color: "#1c6b45" }}>
                🏠 <strong>Excelente opción:</strong> Puedes llevar tu donación directamente al refugio. 
                Te contactaremos para coordinar el día y la hora.
              </p>
            </div>
          )}

          {/* Teléfono de contacto */}
          <div className="campo">
            <label htmlFor="telefonoContacto">Teléfono de contacto *</label>
            <input
              type="tel"
              id="telefonoContacto"
              name="telefonoContacto"
              value={formEspecie.telefonoContacto}
              onChange={handleEspecieChange}
              placeholder="Número de celular o fijo"
              required
            />
            <small>Solo números, entre 7 y 10 dígitos</small>
          </div>

          {/* Resumen de la donación */}
          {categoria && (
            <div style={{ 
              background: "#f8f9fa", 
              padding: "16px", 
              borderRadius: "12px", 
              marginBottom: "18px",
              border: "1px solid #e2ebe6"
            }}>
              <p style={{ margin: 0, fontSize: "14px", fontWeight: "bold", color: "#1c6b45" }}>
                📋 Resumen de tu donación:
              </p>
              <p style={{ margin: "4px 0 0", fontSize: "13px", color: "#577067" }}>
                {categoria === "ALIMENTO" && (
                  <>🍖 {formEspecie.tipoAlimento || "Tipo no seleccionado"} · {formEspecie.marcaAlimento || "Marca no especificada"} · Vence: {formEspecie.fechaVencimiento || "No especificada"} · {formEspecie.cantidad || "0"} unidad(es)</>
                )}
                {categoria === "JUGUETES" && (
                  <>🧸 {formEspecie.tipoJuguete || "Tipo no seleccionado"} · {formEspecie.tamanioJuguete || "Tamaño no seleccionado"} · {formEspecie.estadoJuguete || "Estado no seleccionado"} · {formEspecie.cantidad || "0"} unidad(es)</>
                )}
                {categoria === "COBIJAS_CAMAS" && (
                  <>🛏️ {formEspecie.tipoCama || "Tipo no seleccionado"} · {formEspecie.tamanioCama || "Tamaño no seleccionado"} · {formEspecie.estadoCama || "Estado no seleccionado"} · {formEspecie.cantidad || "0"} unidad(es)</>
                )}
              </p>
            </div>
          )}

          <button type="submit" className="btn-donar" disabled={cargandoEspecie}>
            {cargandoEspecie ? "Registrando..." : "Registrar donación 🐾"}
          </button>
        </form>
      </div>
    );
  };

  return (
    <>
      <Navbar />
      <main className="donaciones-page">
        <div className="donaciones-container">
          {!tipoDonacion && renderSeleccionTipo()}
          {tipoDonacion === "dinero" && renderDonacionDinero()}
          {tipoDonacion === "especie" && renderDonacionEspecie()}
        </div>
      </main>
      <Footer />
    </>
  );
}

export default Donaciones;