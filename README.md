# Vajrayana Path Tracker

Aplicación web en PHP + MySQL para registrar el camino espiritual Vajrayana con 3 perfiles:
- Practicante
- Maestro/a
- Organización (administrador)

Incluye autenticación, registro de prácticas por etapa, asignación maestro-practicante, feedback y paneles por rol.

## Stack
- PHP 8.1+
- MySQL 8+ (o MariaDB compatible)
- HTML, CSS, JavaScript

## Estructura
- `/public` entrada web (`index.php`, assets)
- `/src` lógica de aplicación (controladores, repositorios, vistas)
- `/database/schema.sql` esquema y datos base de etapas Vajrayana
- `/config/config.php` configuración local
- `/.github/workflows/deploy-ionos.yml` despliegue automático a IONOS

## Instalación rápida
1. Crea la base de datos e importa el esquema:
   - Ejecuta `/database/schema.sql`.
2. Configura credenciales:
   - Edita `/config/config.php`.
3. Abre la app:
   - `https://tu-dominio/index.php?route=home`

## Configuración (`/config/config.php`)
Campos clave:
- `db.host`, `db.port`, `db.database`, `db.username`, `db.password`
- `app.base_url`

### `app.base_url`
- Si la web está en la raíz del dominio: usa `''` (vacío).
- Si la web está en subcarpeta (ejemplo `/codex_ngakkhor`): usa `'/codex_ngakkhor'`.
- Si accedes obligatoriamente por `/public`: usa `'/public'`.

`config/config.php` está ignorado en Git para evitar subir credenciales locales.

## Flujo funcional
- Registro con rol:
  - Practicante
  - Maestro/a
  - Organización (crea la organización automáticamente)
- Practicante:
  - Registra práctica por etapa
  - Consulta historial y feedback
- Maestro/a:
  - Asigna practicantes
  - Envía feedback
  - Revisa actividad reciente
- Organización:
  - Crea miembros (practicante/maestro)
  - Visualiza resumen por etapa y listado de miembros

## Seguridad aplicada
- Contraseñas con `password_hash`/`password_verify`
- CSRF token en formularios POST
- Queries preparadas con PDO
- Escape HTML en vistas (`e()`)
- Control de acceso por sesión y rol

## Despliegue automático en IONOS (CI/CD)
La app ya incluye workflow de GitHub Actions para desplegar automáticamente por FTP/FTPS.

### 1) Prepara el repositorio
1. Sube este proyecto a un repositorio GitHub.
2. Trabaja en rama `main` (o `master`).
3. Cada push a `main`/`master` disparará deploy automático.
4. También puedes lanzar deploy manual desde `Actions > Deploy IONOS > Run workflow`.

### 2) Configura secrets en GitHub
Ruta: `Settings > Secrets and variables > Actions > New repository secret`

Secrets obligatorios de IONOS:
- `IONOS_FTP_SERVER` (solo host, ejemplo: `home123456.1and1-data.host`, sin `ftp://` ni `ftps://`)
- `IONOS_FTP_PORT` (normalmente `21`)
- `IONOS_FTP_USERNAME`
- `IONOS_FTP_PASSWORD`
- `IONOS_FTP_SERVER_DIR` (ejemplo: `/` o `/tu_ruta_web/`)

Secrets obligatorios de base de datos:
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

Secrets opcionales:
- `APP_BASE_URL` (`''` en raíz del dominio; `'/codex_ngakkhor'` si está en subcarpeta)
- `APP_SESSION_NAME` (si no se define: `vajrayana_session`)

### 3) Primera puesta en marcha
1. Crea la base MySQL en IONOS.
2. Importa `/database/schema.sql` desde phpMyAdmin.
3. Haz push a `main`.
4. Revisa que el workflow termine en verde.
5. Abre `https://tu-dominio/index.php?route=home`.

### Si IONOS no te deja cambiar el Document Root
Usa este modo:
1. `IONOS_FTP_SERVER_DIR`: carpeta raíz del dominio (normalmente `/` o `/htdocs/`).
2. Deja `APP_BASE_URL` vacío (`''`) si el dominio carga desde esa raíz.
3. Si desplegaste en subcarpeta (ejemplo `/codex_ngakkhor/`), pon `APP_BASE_URL=/codex_ngakkhor`.
4. Este proyecto ya trae `index.php` y `.htaccess` en raíz para enrutar internamente a `public/`.

## Siguientes ampliaciones sugeridas
- Auditoría de actividad y bitácora administrativa
- Exportación PDF/CSV de progreso
- Calendario ritual y recordatorios
- Multi-idioma
- API REST + app móvil
