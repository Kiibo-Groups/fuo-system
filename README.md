# GEN-CONTROL (Fuo System) 🏭📦

**GEN-CONTROL** es una plataforma integral de gestión de inventario, logística, reparación y ventas para una empresa de distribución y servicio de generadores industriales (plantas eléctricas). Diseñada con Laravel y TailwindCSS, esta herramienta permite tener control absoluto del flujo de vida de cada equipo, desde que se compra en EE.UU. hasta su venta en el mostrador del cliente.

---

## 🚀 Características Principales

### 👥 Multiusuario y Roles (Control de Accesos)
El sistema cuenta con 4 roles principales perfectamente delimitados:
1. **SuperAdministrador:** Acceso global a todas las sucursales, inventario completo en tiempo real, administración de usuarios, creación de checklists globales, gestión de Banners comerciales y pedidos internacionales.
2. **Owner (Dueño de Sucursal):** Tiene una vista focalizada de su propia tienda. Puede recepcionar envíos, vender en el POS, monitorear los mantenimientos y revisar estadísticas de desempeño local.
3. **Técnico:** Personal de taller dedicado a escanear equipos, hacer diagnósticos (checklist), utilizar refacciones y reparar daños. 
4. **Cliente:** Interfaz pública o semi-privada para que los consumidores finales vean el stock actual de una sucursal y "separen" (reserven) un equipo por tiempo limitado para agilizar su compra.

### 📦 Gestión Inteligente de Inventario
- **Pedidos EE.UU:** Alta de lotes desde que se adquieren internamente.
- **Importación/Exportación:** Subida masiva de equipos vía archivos Excel (.xlsx/.csv) y descarga de reportes personalizados con filtros activos.
- **Acciones en Lote:** Modificación de estatus (En Taller, Disponible, etc.) y eliminación simultánea de múltiples ítems a la vez.
- **Trazabilidad Absoluta:** Cada generador cuenta con un Bitácora automatizada que guarda la fecha, hora, usuario y comentarios de cada evento que haya cambiado el estado físico o comercial de la máquina.

### 🚚 Logística de Alta Precisión
- Flujo de envíos que permite crear guías de recolección y subir "Evidencias Fotográficas" para respaldar la calidad de empaquetado y traslado de equipos pesados.
- Notificaciones de confirmación de "Recibido en Almacén" vinculando la orden de envío a una sucursal destino especifica.

### 🛠 Taller de Reparación y Diagnóstico
- **Escáner QR:** Los talleres pueden escanear el Código de Barras / QR del generador (con la cámara del celular).
- **Checklists Dinámicos:** Módulo para llenar parámetros vitales (Nivel de Aceite, Voltaje, Daños visuales, etc.).
- **Control de Refacciones:** Afectación automática al inventario de piezas cuando un técnico instala partes para habilitar un equipo que "no pasó la revisión". Control estricto de los **costos de reparación ($) MXN**.

### 💳 Sistema de Punto de Venta (POS)
- Módulo moderno, muy fluido, operando a pantalla dividida (y escondible en móviles). 
- Los Owners ingresan y le asignan dinámicamente el precio de venta sugerido (teniendo a la vista el costo original de almacén + taller).
- Generación de tickets/carros de venta para clientes físicos con el cálculo de costo e historial contable. 

### 🛒 Catálogo para Clientes Finales & Marketing
- **Banners Rotativos:** Motor de publicidad integrado que permite a corporativo lanzar anuncios con Swiper.js ya sea solo en el Dashboard Administrativo (Owners) o en el Catálogo de Clientes.
- **Sistema de Separados (Holding Timer):** Un cliente puede ver el catálogo y separar un generador. Un cron interno en Laravel vigila para que a las 4 horas (Vencimiento) el sistema automáticamente libere el artículo y lo devuelva a "Disponible para venta" si no se ha registrado ingreso monetario del mismo.

---

## 🛠 Tecnologías Core

* **Backend:** PHP 8, Laravel Framework.
* **Base de Datos:** MySQL / MariaDB (Relacional robusta).
* **Frontend:** Blade Templating, TailwindCSS v3 (Utilidades), SwiperJS (Sliders), html5-qrcode (cámara).
* **Almacenamiento:** Laravel Storage (Public/AWS) para subir imágenes y evidencias logísticas.
* **Componentes visuales:** FontAwesome 6 (iconografía).

---

## ⚙️ Guía de Instalación Rápida

1. **Clona o copia el repositorio** en tu servidor local (ej. htdocs, /var/www/html).
2. Instala dependencias del núcleo PHP: 
   ```bash
   composer install
   ```
3. Copia el archivo de entorno y genera la App Key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Configura tu Base de datos dentro del archivo `.env` generdado y corre las migraciones:
   ```bash
   php artisan migrate
   ```
5. Enlaza el almacenamiento local hacia la carpeta pública (Necesario para Evidencias Fotográficas y Banners):
   ```bash
   php artisan storage:link
   ```
6. Corre el servidor de desarrollo y verifica:
   ```bash
   php artisan serve
   ```

*(Recomendado: Añadir el comando cron de Laravel `php artisan schedule:run` a las tareas automatizadas de tu Servidor Linux para que el liberador de Generadores Separados a 4 horas trabaje ininterrumpidamente).*
