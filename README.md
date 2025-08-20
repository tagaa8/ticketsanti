# 🎫 Santiago Tickets - Sistema de Venta de Boletos

Una plataforma completa de marketplace de tickets para crear eventos, gestionar inventario de asientos, comprar/revender, y emitir tickets digitales seguros con control de acceso basado en roles.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![SASS](https://img.shields.io/badge/SASS-CC6699?style=for-the-badge&logo=sass&logoColor=white)
![Gulp](https://img.shields.io/badge/GULP-%23CF4647.svg?style=for-the-badge&logo=gulp&logoColor=white)

## 🚀 Características Principales

### 🎪 Gestión de Eventos
- **Crear y administrar eventos** con múltiples fechas
- **Configuración de estadios** con mapas y zonas
- **Gestión de inventario** de asientos por zona y fila
- **Carga de imágenes** para eventos y estadios

### 🎟️ Sistema de Tickets
- **Tickets digitales seguros** con códigos QR únicos
- **Gestión de inventario** en tiempo real
- **Asientos específicos** por zona y fila
- **Estados de ticket** (activo, usado, expirado)

### 👥 Sistema de Usuarios
- **Registro y autenticación segura** con hash de contraseñas
- **Control de acceso basado en roles**
- **Gestión de perfil de usuario**
- **Historial de compras**

### 💳 Procesamiento de Pagos
- **Checkout seguro** con validación de tarjetas
- **Guardado opcional** de información de pago
- **Cálculo automático** de totales
- **Confirmación de compras**

### 📱 Interfaz Responsive
- **Diseño móvil-first** completamente responsive
- **Experiencia de usuario optimizada** para móvil y desktop
- **Interfaz moderna** con animaciones suaves
- **Navegación intuitiva**

## 🛠️ Stack Tecnológico

### Backend
- **PHP 7.4+** - Lenguaje del servidor
- **MySQL 8.0+** - Base de datos relacional
- **PDO** - Capa de abstracción de base de datos

### Frontend
- **HTML5** - Estructura semántica
- **SASS/SCSS** - Preprocesador CSS con arquitectura modular
- **JavaScript ES6+** - Funcionalidad dinámica
- **Responsive Design** - Diseño adaptable

### Herramientas de Desarrollo
- **Gulp 5.0** - Automatización de tareas
- **NPM** - Gestión de paquetes
- **git** - Control de versiones

### Dependencias de Desarrollo
```json
{
  "gulp": "^5.0.0",
  "gulp-sass": "^6.0.1", 
  "sass": "^1.85.1"
}
```

## 🗄️ Esquema de Base de Datos

### Estructura Principal

#### `usuarios`
- `id_usuario` (PK, AUTO_INCREMENT)
- `nombre_usuario` (VARCHAR, UNIQUE)
- `nombre` (VARCHAR)
- `apellido` (VARCHAR)
- `correo` (EMAIL, UNIQUE)
- `password` (HASH)
- `fecha_registro` (TIMESTAMP)
- `rol` (ENUM: 'admin', 'organizador', 'cliente')

#### `Evento`
- `id_evento` (PK, AUTO_INCREMENT)
- `nombre_evento` (VARCHAR)
- `descripcion_evento` (TEXT)
- `foto` (VARCHAR)
- `categoria` (VARCHAR)
- `fecha_creacion` (TIMESTAMP)

#### `Estadios`
- `id_estadio` (PK, AUTO_INCREMENT)
- `nombre_estadio` (VARCHAR)
- `ubicacion_estadio` (VARCHAR)
- `capacidad` (INT)
- `foto_estadio` (VARCHAR)
- `mapa_estadio` (VARCHAR)

#### `Fecha`
- `id_fecha` (PK, AUTO_INCREMENT)
- `id_evento` (FK → Evento)
- `id_estadio` (FK → Estadios)
- `fecha` (DATE)
- `hora` (TIME)

#### `Zonas_Filas`
- `id_zona_fila` (PK, AUTO_INCREMENT)
- `id_estadio` (FK → Estadios)
- `zona` (VARCHAR)
- `fila` (VARCHAR)
- `cantidad` (INT)

#### `Ticket`
- `id_ticket` (PK, AUTO_INCREMENT)
- `id_zona_fila` (FK → Zonas_Filas)
- `id_fecha` (FK → Fecha)
- `id_usuario` (FK → usuarios)
- `asiento` (VARCHAR)
- `precio` (DECIMAL)
- `qrcode` (VARCHAR)
- `id_activo` (TINYINT: 1=activo, 2=usado, 0=cancelado)
- `fecha_compra` (TIMESTAMP)

#### `Tarjetas`
- `id_tarjeta` (PK, AUTO_INCREMENT)
- `id_usuario` (FK → usuarios)
- `tarjeta` (VARCHAR, ENCRYPTED)
- `cvv` (VARCHAR, ENCRYPTED)
- `fecha_registro` (TIMESTAMP)

### Relaciones
- **Un evento** puede tener **múltiples fechas**
- **Una fecha** está asociada a **un estadio específico**
- **Un estadio** tiene **múltiples zonas y filas**
- **Un usuario** puede comprar **múltiples tickets**
- **Un ticket** pertenece a **una zona/fila específica** y **una fecha específica**

## 📁 Estructura del Proyecto

```
ticketsanti/
├── 📄 index.php              # Página principal con listado de eventos
├── 📄 login.php              # Sistema de autenticación
├── 📄 register.php           # Registro de usuarios
├── 📄 evento.php             # Detalles de evento y fechas disponibles
├── 📄 fechaevento.php        # Selección de tickets por zona/fila
├── 📄 checkout.php           # Proceso de compra y pago
├── 📄 micuenta.php           # Panel del usuario y tickets
├── 📄 fetch_tickets.php      # API para obtener tickets del usuario
├── 📄 db_connect.php         # Configuración de conexión a BD
├── 📄 test_tickets.php       # Utilidad de debugging
├── 📁 src/                   # Assets fuente
│   ├── 📁 codigos_qr/       # Códigos QR generados (2000+ archivos)
│   ├── 📁 js/               # JavaScript fuente
│   ├── 📁 scss/             # Estilos SASS/SCSS
│   ├── 📁 img/              # Imágenes del proyecto
│   └── 📄 imagen_dj.jpg     # Imagen de ejemplo
├── 📁 build/                 # Assets compilados
│   ├── 📁 css/              # CSS compilado
│   └── 📁 js/               # JavaScript procesado
├── 📄 gulpfile.js           # Configuración de Gulp
├── 📄 package.json          # Dependencias de Node.js
└── 📄 README.md             # Documentación del proyecto
```

## ⚙️ Instalación y Configuración

### Prerrequisitos
- **PHP 7.4+** con extensiones PDO_MYSQL
- **MySQL 8.0+** o MariaDB 10.4+
- **Node.js 14+** y **npm**
- **Servidor web** (Apache/Nginx)

### 1. Clonar el Repositorio
```bash
git clone https://github.com/usuario/ticketsanti.git
cd ticketsanti
```

### 2. Instalar Dependencias
```bash
npm install
```

### 3. Configurar Base de Datos

#### Crear la Base de Datos
```sql
CREATE DATABASE Eventos_Estadios;
USE Eventos_Estadios;
```

#### Ejecutar el Script de Inicialización
El sistema incluye un script automático que creará todas las tablas necesarias al primer acceso.

#### Configurar Conexión
Editar `db_connect.php`:
```php
$host = 'localhost';
$dbname = 'Eventos_Estadios';
$username = 'tu_usuario';
$password = 'tu_contraseña';
```

### 4. Configurar Servidor Web

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteRule ^([^/]+)/([^/]+)/?$ evento.php?id_evento=$1&id_fecha=$2 [QSA,L]
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$args;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

### 5. Compilar Assets
```bash
# Desarrollo (watch mode)
npm run dev

# Solo CSS
npm run sass

# Compilación única
gulp
```

### 6. Configurar Permisos
```bash
# Permisos para uploads y QR codes
chmod 755 src/codigos_qr/
chmod 644 src/codigos_qr/*.png

# Permisos para archivos PHP
chmod 644 *.php
chmod 600 db_connect.php  # Archivo sensible
```

## 🚀 Uso del Sistema

### Para Administradores
1. **Crear eventos** con información detallada
2. **Configurar estadios** y mapas de asientos
3. **Definir zonas y precios** por zona/fila
4. **Generar inventario** de tickets
5. **Monitorear ventas** en tiempo real

### Para Usuarios
1. **Registrarse** en la plataforma
2. **Explorar eventos** disponibles
3. **Seleccionar fecha y ubicación** preferida
4. **Elegir asientos** específicos
5. **Procesar pago** de forma segura
6. **Acceder a tickets** digitales con QR

### Flujo de Compra
```
Explorar Eventos → Seleccionar Fecha → Elegir Zona/Asientos → 
Checkout → Pago → Confirmación → Ticket Digital
```

## 🔐 Características de Seguridad

### Autenticación
- **Hashing seguro** de contraseñas con `password_hash()`
- **Validación de entrada** con prepared statements
- **Protección CSRF** en formularios críticos
- **Sanitización** de datos de entrada

### Control de Acceso
- **Verificación de sesiones** en páginas protegidas
- **Roles de usuario** (administrador, organizador, cliente)
- **Autorización granular** por funcionalidad
- **Timeout de sesión** automático

### Datos Sensibles
- **Encriptación** de información de tarjetas
- **Conexión segura** a base de datos
- **Validación** de tipos de archivo
- **Protección** contra inyección SQL

## 🎨 Personalización y Temas

### Estructura SCSS
```scss
src/scss/
├── abstracts/
│   ├── _variables.scss    # Variables globales
│   └── _mixins.scss      # Mixins reutilizables
├── base/
│   ├── _reset.scss       # Reset CSS
│   └── _typography.scss  # Tipografía
├── components/
│   ├── _buttons.scss     # Botones
│   ├── _forms.scss       # Formularios
│   └── _cards.scss       # Tarjetas
├── layout/
│   ├── _header.scss      # Cabecera
│   ├── _footer.scss      # Pie de página
│   └── _grid.scss        # Sistema de grid
└── pages/
    ├── _home.scss        # Página principal
    ├── _events.scss      # Páginas de eventos
    └── _account.scss     # Panel de usuario
```

### Variables Principales
```scss
// Colores
$primary-color: #667eea;
$secondary-color: #764ba2;
$accent-color: #f093fb;

// Tipografía
$font-family: 'Montserrat', sans-serif;
$font-sizes: (
  'small': 0.875rem,
  'base': 1rem,
  'large': 1.25rem,
  'xl': 1.5rem
);

// Breakpoints
$breakpoints: (
  'mobile': 320px,
  'tablet': 768px,
  'desktop': 1024px,
  'wide': 1200px
);
```

## 📱 Diseño Responsive

### Breakpoints
- **Mobile First**: 320px+
- **Tablet**: 768px+
- **Desktop**: 1024px+
- **Wide Screen**: 1200px+

### Componentes Adaptables
- **Navegación**: Hamburger menu en móvil
- **Grids**: Flexbox responsive para eventos
- **Formularios**: Optimizados para touch
- **Tickets**: Vista de lista en móvil, grid en desktop

## 🧪 Testing y Debugging

### Herramientas Incluidas
- `test_tickets.php` - Debug de consultas SQL
- Console logging en JavaScript
- Error handling en PHP

### Testing Manual
1. **Registro de usuarios** con validaciones
2. **Login/logout** de sesiones
3. **Compra de tickets** end-to-end
4. **Visualización de tickets** en diferentes estados
5. **Responsive testing** en múltiples dispositivos

### Logs y Monitoreo
```php
// Logging de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');
```

## 🔄 APIs y Endpoints

### Fetch Tickets API
```javascript
// Obtener tickets del usuario
GET /fetch_tickets.php?type={pasados|hoy|proximos}

// Respuesta JSON
{
  "id_ticket": 123,
  "nombre_evento": "BZRP Live",
  "ubicacion_estadio": "CDMX, México",
  "fecha": "2026-07-15",
  "hora": "20:00:00",
  "zona": "VIP",
  "asiento": "A-15",
  "qrcode": "codigoqr123.png"
}
```

### Estados de Tickets
- `type=pasados` - Eventos ya realizados (id_activo = 2)
- `type=hoy` - Eventos de hoy
- `type=proximos` - Eventos futuros (id_activo = 1)

## 🚧 Roadmap y Mejoras Futuras

### Funcionalidades Pendientes
- [ ] **Sistema de reembolsos** automático
- [ ] **Marketplace de reventa** entre usuarios
- [ ] **Notificaciones push** para eventos
- [ ] **Integración con APIs** de pago (Stripe, PayPal)
- [ ] **Dashboard analítico** para organizadores
- [ ] **Sistema de descuentos** y cupones
- [ ] **Multi-idioma** (i18n)
- [ ] **PWA** (Progressive Web App)

### Optimizaciones Técnicas
- [ ] **Caching** de consultas frecuentes
- [ ] **CDN** para assets estáticos
- [ ] **Lazy loading** de imágenes
- [ ] **Database indexing** optimizado
- [ ] **Unit testing** con PHPUnit
- [ ] **CI/CD pipeline** automatizado

## 🤝 Contribuir

### Cómo Contribuir
1. Fork del proyecto
2. Crear branch para feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit de cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push al branch (`git push origin feature/nueva-funcionalidad`)
5. Crear Pull Request

### Estándares de Código
- **PSR-12** para PHP
- **ES6+** para JavaScript
- **BEM** para nomenclatura CSS
- **Comentarios** en español
- **Commits** descriptivos

### Issues y Bugs
Usar las plantillas de GitHub Issues para:
- 🐛 Reportar bugs
- ✨ Solicitar features
- 📚 Mejorar documentación
- 🔧 Optimizaciones técnicas

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE.md](LICENSE.md) para detalles.

## 👨‍💻 Autor

**Santiago Montero**
- GitHub: [@santiagodev](https://github.com/santiagodev)
- Email: santiago@tickets.com

---

### 💡 ¿Necesitas Ayuda?

¿Tienes preguntas o necesitas soporte? 

1. **Revisa la documentación** completa
2. **Busca en Issues** existentes
3. **Crea un nuevo Issue** con detalles
4. **Contacta directamente** al autor

### 🎯 Estado del Proyecto

![Status](https://img.shields.io/badge/status-active-success.svg)
![Build](https://img.shields.io/badge/build-passing-success.svg)
![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

---

⭐ **¡Dale una estrella si te ha sido útil este proyecto!** ⭐