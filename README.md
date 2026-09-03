¡Perfecto! Aquí tienes el **README.md completo** para copiar y pegar directamente. Solo reemplaza `[TU_USUARIO]` y `[TU NOMBRE COMPLETO]` con tus datos:

---

## 📋 **README.md (COPIA Y PEGA ESTO)**

```markdown
# 🐾 Adoptfest - Plataforma de Adopción de Mascotas

![Java](https://img.shields.io/badge/Java-25-blue)
![Spring Boot](https://img.shields.io/badge/Spring_Boot-4.1.1-green)
![React](https://img.shields.io/badge/React-19-cyan)
![MySQL](https://img.shields.io/badge/MySQL-8-orange)

<img width="582" height="442" alt="Captura de pantalla 2026-02-19 100811" src="https://github.com/user-attachments/assets/056c927e-d9cf-4e4e-91e0-6e6868039de7" />

---

## 📌 **Problema**

Miles de perros y gatos en Latinoamérica viven en situación de calle o permanecen en refugios sin encontrar un hogar. Los refugios enfrentan desafíos como:

- **Baja visibilidad**: Las familias interesadas en adoptar no siempre saben dónde acudir.
- **Procesos manuales**: La gestión de solicitudes, citas y seguimiento se hace de forma ineficiente.
- **Falta de recursos**: Los refugios necesitan donaciones y apoyo de la comunidad.
- **Desconexión**: No hay un canal directo entre refugios, adoptantes y donantes.

## 💡 **Solución**

**Adoptfest** es una plataforma web que conecta refugios de animales con personas que desean adoptar, donar o participar en eventos. El sistema automatiza y digitaliza todo el proceso:

- ✅ **Adopción**: Solicitud en línea, aprobación, generación de citas virtuales y verificación con código.
- ✅ **Donaciones**: Pagos seguros con PayPal y donaciones en especie (alimentos, juguetes, cobijas).
- ✅ **Eventos**: Creación de eventos, inscripción con invitados y control de cupos en tiempo real.
- ✅ **Notificaciones**: Sistema de notificaciones en tiempo real (WebSockets).
- ✅ **Panel de administración**: Gestión completa de usuarios, mascotas, solicitudes, citas y donaciones.

## 🎯 **Objetivos**

1. **Promover la adopción responsable** facilitando el proceso completo.
2. **Reducir el número de animales en situación de calle** en Latinoamérica.
3. **Dar visibilidad y apoyo a los refugios** mediante una plataforma digital moderna.
4. **Educar sobre tenencia responsable** a través de eventos y contenido informativo.
5. **Fomentar la participación ciudadana** mediante donaciones y voluntariado.

## 🛠️ **Tecnologías utilizadas**

### Backend
| Tecnología | Versión |
|------------|---------|
| Java | 25 |
| Spring Boot | 4.1.1 |
| Spring Security | - |
| Spring Data JPA | - |
| MySQL | 8 |
| PayPal SDK | 1.14.0 |
| Maven | - |

### Frontend
| Tecnología | Versión |
|------------|---------|
| React | 19 |
| Vite | - |
| Bootstrap | 5 |
| Axios | - |

### Notificaciones
| Tecnología | Versión |
|------------|---------|
| Node.js | 20+ |
| WebSockets | - |

## 📁 **Estructura del proyecto**

```
adoptfest/
├── backend/                    # API REST con Spring Boot
│   ├── src/
│   │   ├── main/
│   │   │   ├── java/com/adoptfest/backend/
│   │   │   │   ├── controller/   # Controladores REST
│   │   │   │   ├── service/      # Lógica de negocio
│   │   │   │   ├── repository/   # Acceso a base de datos
│   │   │   │   ├── model/        # Entidades JPA
│   │   │   │   ├── dto/          # Data Transfer Objects
│   │   │   │   ├── security/     # Autenticación JWT
│   │   │   │   ├── config/       # Configuraciones
│   │   │   │   ├── exception/    # Manejo de errores
│   │   │   │   └── validator/    # Validaciones personalizadas
│   │   │   └── resources/
│   │   └── test/
│   └── pom.xml
├── frontend/                   # Aplicación React
│   ├── src/
│   │   ├── components/         # Componentes reutilizables
│   │   ├── pages/              # Páginas de la aplicación
│   │   ├── services/           # Comunicación con API
│   │   ├── context/            # Contextos (Auth, Toast)
│   │   └── styles/             # Estilos CSS
│   ├── package.json
│   └── index.html
├── notificaciones/             # Servicio de WebSockets
│   ├── src/
│   │   └── server.js
│   └── package.json
└── uploads/                    # Archivos subidos (imágenes)
```

## 🚀 **Instalación y configuración**

### Requisitos previos
- ☕ Java 25+
- 📦 Node.js 20+
- 🗄️ MySQL 8+
- 🔧 Maven

### 1. Clonar el repositorio
```bash
git clone https://github.com/[TU_USUARIO]/adoptfest.git
cd adoptfest
```

### 2. Configurar el Backend
```bash
cd backend
cp application-example.properties src/main/resources/application.properties
# Edita application.properties con tus credenciales
```

**Variables de entorno necesarias:**
```properties
# Base de datos
spring.datasource.url=jdbc:mysql://localhost:3306/adoptfest_db
spring.datasource.username=root
spring.datasource.password=TU_CONTRASEÑA

# JWT
app.jwt.secret=TU_SECRETO_JWT
app.jwt.expiration-ms=86400000

# PayPal (sandbox)
app.paypal.client-id=TU_CLIENT_ID
app.paypal.client-secret=TU_CLIENT_SECRET
app.paypal.mode=sandbox
```

```bash
# Levantar el backend
mvn clean install
mvn spring-boot:run
```

### 3. Configurar el Frontend
```bash
cd frontend
npm install
npm run dev
```

### 4. Configurar el servicio de notificaciones
```bash
cd notificaciones
npm install
npm run dev
```

## 🔑 **Credenciales de prueba**

| Rol | Correo | Contraseña |
|-----|--------|------------|
| Administrador | admin@adoptfest.com | admin123 |
| Cliente | usuario@test.com | usuario123 |

## 🏗️ **Arquitectura**

```
┌─────────────┐     ┌─────────────────┐     ┌──────────────┐
│   Cliente   │────▶│    Backend      │────▶│     MySQL    │
│   (React)   │     │  (Spring Boot)  │     │              │
└─────────────┘     └─────────────────┘     └──────────────┘
       │                     │
       │                     │
       ▼                     ▼
┌─────────────┐     ┌─────────────────┐
│ WebSockets  │     │   PayPal API    │
│ (Node.js)   │     │                 │
└─────────────┘     └─────────────────┘
```

**Flujo de adopción:**
1. Usuario envía solicitud de adopción
2. Administrador revisa y aprueba
3. Se genera automáticamente una cita virtual
4. Se rechazan automáticamente otras solicitudes
5. Se notifica a todos los involucrados
6. El día de la cita se verifica con código

## 📊 **Diagrama de base de datos**

**Principales entidades:**
- `users` - Usuarios del sistema
- `mascotas` - Mascotas disponibles para adopción
- `solicitudes_adopcion` - Solicitudes de adopción
- `citas` - Citas virtuales para adopción
- `eventos` - Eventos de adopción
- `inscripciones_eventos` - Inscripciones a eventos
- `donaciones_dinero` - Donaciones en dinero
- `donaciones_especie` - Donaciones en especie
- `notificaciones` - Notificaciones de usuario

## 👥 **Equipo**

- **[TU NOMBRE COMPLETO]** - Desarrollador Full Stack

## 📄 **Licencia**

Este proyecto es de uso educativo.

## 🙏 **Agradecimientos**

- A todos los refugios y personas que dedican su tiempo a dar una segunda oportunidad a los animales.
- A los mentores y profesores que guiaron este proyecto.
- A la comunidad de código abierto por las herramientas utilizadas.

---

🐾 *"Un hogar para cada huella"* ❤️
```

---

## 🔧 **Cosas que debes cambiar ANTES de subir**

| **Lugar** | **Cámbialo por** |
|-----------|------------------|
| `[TU_USUARIO]` | Tu usuario de GitHub (ej: `juanperez`) |
| `[TU NOMBRE COMPLETO]` | Tu nombre completo (ej: `Juan Pérez Gómez`) |

---

## 📋 **Resumen para tu commit**

```
docs: Agregar README completo del proyecto Adoptfest

- Documentación del problema y solución
- Lista de tecnologías utilizadas
- Instrucciones de instalación paso a paso
- Credenciales de prueba
- Arquitectura y flujo de adopción
- Estructura del proyecto explicada
```

---

¡Listo! Solo copia, pega, reemplaza `[TU_USUARIO]` y `[TU NOMBRE COMPLETO]`, y ya tienes tu README profesional. 🚀
