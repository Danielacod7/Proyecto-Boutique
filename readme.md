## Proyecto Boutique

Este repositorio contiene la estructura inicial de una aplicación web enfocada en la gestión de una boutique. Incluye la base para autenticación de usuarios, organización de archivos y la configuración necesaria para comenzar con el desarrollo.

### Funcionalidades actuales
- Inicio de sesión con Supabase: autenticación mediante correo y contraseña.
- Protección de sesión: las páginas verifican el estado de sesión y redirigen al login si no existe.
- Interfaz moderna: diseño responsivo con soporte de íconos.
- Gestión de sesión: cierre de sesión desde la página principal.


### Configuración inicial
1. Clona este repositorio:
https://github.com/Danielacod7/Proyecto-Boutique.git

3. Crea ek archivo de configuración pública en frontend/config.js con tus credenciales de Supabase.

const SUPABASE_URL = "https://tu-proyecto.supabase.co";
const SUPABASE_ANON_KEY = "tu-clave-anonima";

3. Abre frontend/login.html en tu navegador para probar la autenticación.


###Próximos pasos
- Implementar página de registro de usuario.
- Agregar recuperación de contraseña
- Integrar la lógica para mostrar y gestionar los datos de la boutique.
- Configurar políticas de acceso en Supabase (RLS).