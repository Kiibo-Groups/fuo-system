CONTEXTO DEL PROYECTO: SISTEMA DE GESTIÓN PARA VENTA Y CONTROL DE GENERADORES

1. Instrucciones Estrictas para la IA

Rol: Eres un asistente de programación experto en Laravel y Tailwind CSS.

Restricción principal: NO debes alterar la arquitectura ya definida, NO debes cambiar los nombres de las tablas ni de los estados de los generadores. Debes seguir estrictamente este documento como tu "fuente de verdad".

Stack Tecnológico: Laravel 11+, MySQL, Blade, Tailwind CSS (estilo industrial: Slate-900 y Orange-500).

Autenticación: Se está utilizando laravel/ui (con estructura de Auth tradicional), pero las vistas están siendo adaptadas a Tailwind CSS.

2. Estado Actual del Proyecto (Lo que YA está hecho)

El usuario ya ha implementado lo siguiente en su entorno local:

Base de Datos y Migraciones: * Tablas creadas: branches, users, generators, spare_parts, checklist_templates, generator_revisions, workshop_logs, workshop_spare_part, shipments, reservations, generator_status_history.

Modelos Eloquent: Todos los modelos ya tienen sus relaciones (belongsTo, hasMany, etc.) y $casts (para campos JSON como los items del checklist).

Rutas (routes/web.php): Ya están agrupadas por prefijos y middlewares (admin, inventory, operations, logistics, store).

Vistas Blade (Blade + Tailwind): * Layout principal (app.blade.php), Header, Aside.

Dashboard del Admin, CRUD de Sucursales, CRUD de Usuarios.

Configuración de Checklists Dinámicos.

Formulario de Registro de Pedidos (EE.UU.).

Inventario Global de Máquinas.

Gestión de Envíos Activos (admin.shipments).

Controladores: * ShipmentController implementado con transacciones, manejo de imágenes (evidencia) y registro de historial.

Lógica base de Inventario y Pedidos USA ya creada.

3. Reglas de Negocio Intocables

3.1. Roles de Usuario (4 Tipos)

admin: Administrador General (Usuario 1) - Control total, ve todas las sucursales.

owner: Dueño de Sucursal (Usuario 2) - Solo ve su inventario y aprueba recepciones.

technician: Técnico (Usuario 3) - Escanea, revisa y manda a taller o a envío.

client: Cliente (Usuario 4) - Solo ve catálogo de su sucursal y puede hacer separaciones de 24h.

3.2. Ciclo de Vida del Generador (ESTADOS EXACTOS)

Cualquier actualización de estado debe usar EXACTAMENTE uno de estos strings y DEBE registrarse en la tabla generator_status_history:

Pedido en tránsito

Recibido en almacén

En revisión

En taller

Lista para envío

Enviado

Recibido en sucursal

Disponible

Separado

Vendido

3.3. Trazabilidad Total

CADA VEZ que un generador cambie de estado (ej. de "En revisión" a "En taller"), el controlador debe ejecutar un DB::transaction que incluya un insert en GeneratorStatusHistory con el user_id, previous_status, new_status y un comment.

4. Próximos Pasos (LO QUE DEBES HACER A CONTINUACIÓN)

El desarrollador te pedirá avanzar con las siguientes fases. Usa Tailwind CSS para todas las vistas.

[PENDIENTE] Fase 5: Módulo Técnico y Taller

Objetivo: Interfaz optimizada para móviles/tablets para el Usuario Técnico.

Tareas:

Vista para escanear código de barras/folio (operations.revisions.scan).

Lógica y vista para cargar el Checklist Dinámico (checklist_templates en JSON) y guardar el resultado en generator_revisions.

Lógica del Taller (workshop_logs): Si falla la revisión, registrar diagnóstico, descontar stock de refacciones (workshop_spare_part) y calcular costo de reparación, para luego pasarlo a estado Lista para envío.

[PENDIENTE] Fase 6: Recepción en Sucursal (Panel del Dueño)

Objetivo: Interfaz para el Dueño de Sucursal (owner).

Tareas:

Ver máquinas cuyo estado sea Enviado y su branch_id coincida con el del Dueño.

Botón para confirmar recepción, que cambie el estado a Disponible.

[PENDIENTE] Fase 7: Ventas y Separaciones (Vista Cliente)

Objetivo: Catálogo público/cliente.

Tareas:

Mostrar solo generadores en estado Disponible por sucursal.

Lógica de "Separación": Cambiar a estado Separado y registrar en tabla reservations con un expires_at de 4 horas.

Crear un Comando (Cron Job/Schedule) de Laravel para liberar automáticamente equipos separados que hayan superado las 4 horas.