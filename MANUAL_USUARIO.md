# Manual de Usuario - Sistema Tramita YA

## 1. Introducción
**Tramita YA** es un sistema integral de gestión y seguimiento documentario diseñado para facilitar el registro, derivación, recepción y atención de documentos en entornos administrativos. El sistema permite un control preciso de dónde se encuentra un expediente en todo momento, quién lo tiene y qué acciones se han realizado sobre él.

---

## 2. Acceso al Sistema

El sistema cuenta con dos portales principales:

*   **Portal de Usuario (Oficina):** `http://tu-dominio.com/user`
    *   Destinado al personal de cada oficina para la gestión diaria de sus documentos.
*   **Portal de Administración:** `http://tu-dominio.com/admin`
    *   Destinado a los administradores del sistema para configuración, gestión de usuarios y supervisión global.

---

## 3. Roles y Permisos (Sistema de Accesos)

El sistema utiliza **FilamentShield** para gestionar permisos granulares. Los usuarios se dividen principalmente en:

### 3.1. Super Administrador / Administrador
*   **Acceso:** Panel de Administración (`/admin`).
*   **Funciones:** Control total del sistema, configuración global, gestión de usuarios de todas las oficinas, visualización de auditorías y gestión de copias de seguridad.

### 3.2. Usuario de Oficina (Personal / Jefe de Oficina)
*   **Acceso:** Panel de Usuario (`/user`).
*   **Funciones:** Operaciones diarias de su oficina específica. Registro, recepción y derivación de documentos.

---

## 4. Conceptos Fundamentales

*   **Documento / Trámite:** Es la unidad básica de información. Contiene datos como el número de trámite, expediente, asunto y remitente (cliente).
*   **Expediente:** Número único que agrupa toda la documentación relacionada a un trámite específico.
*   **Oficina:** Unidades orgánicas que reciben, procesan y derivan documentos.
*   **Movimiento:** Registro de cada vez que un documento es derivado de una oficina a otra.
*   **Estado del Documento:** Indica la fase actual (Registrado, En Proceso, Respondido, Finalizado, etc.).

---

## 5. Flujo de Trabajo (Workflow)

El ciclo de vida estándar de un documento es:

1.  **Registro:** El documento ingresa al sistema (Estado: `Registrado`).
2.  **Derivación:** El documento es enviado a una oficina de destino.
3.  **Recepción:** La oficina de destino confirma que ha recibido el documento físico/digital (Estado: `En Proceso`).
4.  **Atención:** La oficina realiza una acción de respuesta, derivación adicional o finalización.

---

## 6. Guía Detallada: Panel de Usuario (`/user`)

Este panel está diseñado para la eficiencia operativa de cada oficina. El usuario solo ve lo que le corresponde a su unidad orgánica.

### 6.1. Dashboard (Escritorio)
*   **Vista:** Gráficos y contadores con el resumen de documentos en la oficina actual.
*   **Utilidad:** Permite ver rápidamente cuántos documentos están "En Proceso", "Pendientes de Recepción" o próximos a vencer.

### 6.2. Módulo: Documentos
Es la bandeja principal de trabajo de la oficina.
*   **Filtros:** Permite segmentar por estado, tipo de documento o fecha.
*   **Acciones de Registro:**
    *   **Botón "Nuevo Documento":** Abre el formulario para registrar un documento que ingresa físicamente a la oficina.
    *   **Carga de Archivos:** Permite adjuntar el sustento digital (PDF/Imagen).
*   **Acciones de Fila:**
    *   **Editar:** Solo permitido si el documento aún no ha sido derivado.
    *   **Derivar/Responder:** Abre un modal para enviar el documento a otra oficina.
    *   **Finalizar:** Cierra el ciclo del documento si el trámite concluye en esa oficina.

### 6.3. Módulo: Recepción de Documentos
Este es el "buzón de entrada" de la oficina.
*   **Documentos Pendientes:** Aquí aparecen todos los documentos que otras oficinas han enviado (Derivado) a tu oficina pero que aún no han sido aceptados.
*   **Acción "Recibir":** Es obligatoria para que el documento pase a la bandeja de "Documentos" y el sistema sepa que ya está bajo tu custodia. Al recibirlo, el estado cambia a `En Proceso`.

---

## 7. Guía Detallada: Panel de Administración (`/admin`)

El panel administrativo es el centro de control total del sistema.

### 7.1. Gestión de Entidades Estructurales
*   **Oficinas:** Registro de todas las oficinas de la institución.
*   **Gestiones (Administraciones):** Configura los periodos de gobierno o años fiscales.
*   **Tipos de Documento:** Lista maestra (Oficio, Carta, Informe, etc.). Aquí se definen los **Días de Respuesta** por defecto.
*   **Prioridades:** Configuración de niveles de urgencia (Alta, Media, Baja).

### 7.2. Gestión de Usuarios y Seguridad
*   **Usuarios:** Creación de cuentas para el personal. **Cada usuario debe estar vinculado a una Oficina** para operar correctamente.
*   **Roles y Permisos (Shield):** Interfaz para definir los permisos de cada rol de usuario.

### 7.3. Gestión de Clientes (Remitentes)
*   Base de datos centralizada de ciudadanos y empresas que inician los trámites.

### 7.4. Control y Monitoreo Global
*   **Documentos (Vista General):** El administrador puede ver TODOS los documentos de todas las oficinas para supervisión.
*   **Auditoría:** Registro histórico de acciones. Permite saber "quién hizo qué" en el sistema.
*   **Backups (Copias de Seguridad):** Herramienta para generar y descargar copias de seguridad de la base de datos.

---

## 8. Preguntas Frecuentes

**¿Por qué no puedo ver un documento que me enviaron?**
Asegúrese de revisar la pestaña de **Recepción de Documentos**. Hasta que no haga clic en "Recibir", el documento no aparecerá en su lista principal de gestión.

**¿Cómo busco un documento antiguo?**
Utilice los filtros de búsqueda en la cabecera de la tabla de documentos en cualquiera de los paneles.

**¿Puedo anular un documento registrado por error?**
Sí, si tiene los permisos necesarios (Admin), puede cambiar el estado a `Cancelado`.
