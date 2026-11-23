# Laboratorio 14 — Despliegue en Producción (GuateGanado)

> **Curso:** IF0009 · **Proyecto:** GuateGanado (Sistema de Gestión Ganadera)  
> **Repositorios:** `ProyectoGanadero_BackEnd` (API Laravel) y `ProyectoGanadero_FrontEnd` (Web SPA + App Móvil Android)  
> **Insignias de CI:**  
> ![CI Back-End](https://github.com/<org>/ProyectoGanadero_BackEnd/actions/workflows/ci.yml/badge.svg)
> ![CI Front-End](https://github.com/<org>/ProyectoGanadero_FrontEnd/actions/workflows/ci.yml/badge.svg)

---

## 1. Arquitectura de Despliegue

El sistema se despliega bajo una arquitectura distribuida de 6 piezas utilizando servicios administrados con HTTPS obligatorio. Ni la aplicación web ni la aplicación móvil acceden directamente a la base de datos: toda comunicación se realiza mediante tokens Bearer (Laravel Sanctum) contra la API REST pública, que es el único componente con credenciales de conexión hacia Aiven for MySQL.

```mermaid
flowchart LR
    Web["Front-End Web SPA\nIonic / Vue 3 + Vite\n(Cloudflare Pages)"] -->|HTTPS REST + Bearer| API["Back-End API REST\nLaravel / PHP 8.4 Docker\n(Render Web Service)"]
    Mobile["App Móvil Android\nAPK Firmado v1.1.0\n(GitHub Releases)"] -->|HTTPS REST + Bearer| API
    API -->|MySQL + TLS/SSL CA| DB["Base de Datos\nAiven for MySQL"]
    GHB["GitHub Actions\n(CI/CD Back-End)"] -->|phpunit en verde -> Deploy Hook| API
    GHF["GitHub Actions\n(CI/CD Front-End)"] -->|lint + vitest + build| Web
```

| Pieza | Servicio / Plataforma | Configuración Principal | Límite / Nota Operativa |
| --- | --- | --- | --- |
| **API REST (Back-End)** | Render (Web Service Docker) | Imagen multi-etapa `serversideup/php:8.4-fpm-nginx`, puerto `8080`, healthcheck `/up`, `Auto-Deploy: Off` | Plan Free (512 MB RAM); se suspende tras 15 min sin tráfico y despierta en ~1 min |
| **Base de Datos** | Aiven for MySQL | MySQL gestionado, TLS/SSL obligatorio (`certs/aiven-ca.pem`), `sql_require_primary_key = ON` | Plan Free (1 GB RAM / 1 GB disco); puede apagarse por inactividad prolongada |
| **Front-End Web SPA** | Cloudflare Pages | `npm run build` → `dist/`, `NODE_VERSION=22`, enrutamiento SPA sin `404.html` | Despliegue automático en `main`; 500 builds/mes |
| **App Móvil (Android)** | GitHub Releases (APK) | Ionic/Capacitor sincronizado con el build de producción, firmado con keystore `.jks` (`v1.1.0`) | Distribución directa por APK firmado (sin Google Play) |
| **CI/CD Back-End** | GitHub Actions | `php artisan test` (SQLite en memoria) + `RENDER_DEPLOY_HOOK` condicional en `main` | Una prueba en rojo detiene el job `deploy` |
| **CI/CD Front-End** | GitHub Actions | `npm run lint`, `npm run test:unit` (Vitest) y `npm run build` (`vue-tsc && vite build`) | Valida PRs y commits antes del despliegue en Cloudflare Pages |

> **Nota sobre estructura multi-repositorio:** A diferencia de un monorepo con carpetas `backend/`, `web/` y `movil/`, el equipo mantiene dos repositorios independientes (`ProyectoGanadero_BackEnd` y `ProyectoGanadero_FrontEnd`, donde este último empaqueta tanto la SPA web como la app móvil Capacitor). Este documento se mantiene sincronizado en `docs/lab14-despliegue.md` en ambos repositorios para garantizar trazabilidad completa.

---

## 2. Separación de Entornos y Variables de Configuración (Sin Secretos)

La separación entre desarrollo y producción se garantiza mediante inyección de variables en cada plataforma y exclusión estricta de archivos sensibles en `.gitignore` y `.dockerignore`:

- Ninguna contraseña, llave de cifrado (`APP_KEY`), token, archivo `.env` ni almacén de firma (`.jks`) se versiona en el repositorio.
- El único archivo relacionado con TLS versionado en el Back-End es el certificado público de autoridad certificadora (`certs/aiven-ca.pem`).
- **Falla explícita (*Fail-Fast*):** En `AppServiceProvider::boot()`, cuando `APP_ENV=production` y la aplicación no se ejecuta por consola, Laravel lanza una `RuntimeException` si falta `APP_KEY`, `DB_HOST` o `CORS_ALLOWED_ORIGINS`, o si `CORS_ALLOWED_ORIGINS` incluye un comodín `*`.

### 2.1 Back-End (Render — Web Service)

| Variable | Ejemplo / Valor Referencial | Tipo | Descripción |
| --- | --- | --- | --- |
| `APP_NAME` | `GuateGanado` | Config | Nombre de la aplicación |
| `APP_ENV` | `production` | Config | Activa optimizaciones y validaciones fail-fast |
| `APP_DEBUG` | `false` | Config | Evita exponer trazas de error y variables en respuestas HTTP |
| `APP_KEY` | `base64:<generado-con-artisan>` | **Secreto** | Llave de cifrado de Laravel generada localmente |
| `APP_URL` | `https://<servicio>.onrender.com` | Config | URL pública HTTPS de la API |
| `PORT` | `8080` | Config | Puerto en el que escucha Nginx/PHP-FPM dentro del contenedor |
| `LOG_CHANNEL` | `stderr` | Config | Dirige los logs al panel de Render |
| `SESSION_DRIVER` | `database` | Config | Evita pérdida de estado ante disco efímero del contenedor |
| `CACHE_STORE` | `database` | Config | Persiste caché fuera del sistema de archivos efímero |
| `QUEUE_CONNECTION` | `sync` | Config | Ejecución síncrona compatible con el plan gratuito sin workers |
| `DB_CONNECTION` | `mysql` | Config | Driver MySQL hacia Aiven |
| `DB_HOST` | `<host>.aivencloud.com` | Config | Host de la instancia Aiven for MySQL |
| `DB_PORT` | `<puerto-aiven>` | Config | Puerto TLS de Aiven |
| `DB_DATABASE` | `defaultdb` | Config | Base de datos principal |
| `DB_USERNAME` | `avnadmin` | **Secreto** | Usuario de conexión |
| `DB_PASSWORD` | `<contraseña-aiven>` | **Secreto** | Contraseña de conexión |
| `MYSQL_ATTR_SSL_CA` | `/var/www/html/certs/aiven-ca.pem` | Config | Ruta interna al certificado CA de Aiven |
| `CORS_ALLOWED_ORIGINS` | `https://<proyecto>.pages.dev,https://localhost,capacitor://localhost,http://localhost:8100` | Config | Lista explícita de orígenes permitidos (web + Capacitor Android/iOS + desarrollo; nunca `*`) |

### 2.2 Front-End Web (Cloudflare Pages)

| Variable | Ejemplo / Valor Referencial | Descripción |
| --- | --- | --- |
| `NODE_VERSION` | `22` | Versión de Node.js para la compilación |
| `VITE_API_URL` | `https://<servicio>.onrender.com/api/v1` | URL base pública de la API fijada en tiempo de compilación (sin secretos) |

### 2.3 Secretos de CI/CD (GitHub Actions)

| Repositorio | Secreto | Descripción |
| --- | --- | --- |
| `ProyectoGanadero_BackEnd` | `RENDER_DEPLOY_HOOK` | URL secreta de despliegue en Render invocada solo cuando el job de pruebas aprueba en `main` |

---

## 3. Empaquetado, Contenerización y Seguridad

### 3.1 Contenedor Multi-Stage del Back-End (`Dockerfile`)

- **Etapa 1 (`vendor`):** Instala únicamente dependencias de producción (`composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist`) y compila el autoloader optimizado (`composer dump-autoload --optimize --no-dev`).
- **Etapa 2 (imagen final):** Parte de `serversideup/php:8.4-fpm-nginx` con `PHP_OPCACHE_ENABLE=1` y `AUTORUN_ENABLED=true` (ejecuta `migrate --force`, `storage:link` y cachés de configuración/rutas/vistas al iniciar). Copia únicamente el código necesario y `vendor/` desde la etapa anterior con propietario `--chown=www-data:www-data`.
- **Usuario sin privilegios:** El contenedor se ejecuta bajo `USER www-data` (no `root`) y expone el puerto `8080`.
- **Contexto depurado (`.dockerignore`):** Excluye `.env`, `.git`, `.github`, `node_modules`, `vendor`, `tests`, `docs`, `backend-aspnet`, `backend-nestjs`, `*.sqlite*`, cachés/logs locales y `api.json`.

### 3.2 Endurecimiento de Producción

- **Proxies de confianza:** `$middleware->trustProxies(at: '*')` en `bootstrap/app.php` para que los enlaces generados y de paginación utilicen `https://` detrás del proxy inverso de Render.
- **CORS restrictivo:** `config/cors.php` lee dinámicamente `CORS_ALLOWED_ORIGINS` (separados por coma) y mantiene `supports_credentials => false`, dado que se emplea autenticación por token Bearer (Sanctum) entre dominios distintos (`*.pages.dev`, `*.onrender.com` y `https://localhost` en Android).
- **Seguridad de registro:** Se deshabilitó el registro público de cuentas; la creación de nuevos perfiles (`POST /api/v1/auth/register`) está restringida exclusivamente al rol `admin` autenticado.
- **Almacenamiento seguro móvil:** El token de sesión en Android continúa resguardado en almacenamiento seguro del dispositivo, y el archivo `.jks` de firma se conserva fuera del repositorio.
- **Manejo de disco efímero:** Debido a que el contenedor de Render descarta archivos locales en cada redespliegue, se eliminó la carga de fotografías locales en el módulo de animales y se configuraron sesiones/caché en base de datos (`database`).

### 3.3 Tamaño de los Artefactos Generados

| Artefacto | Repositorio | Comando de Medición | Tamaño Medido | Notas |
| --- | --- | --- | --- | --- |
| **Imagen Docker Back-End** (`Dockerfile` multi-stage) | `ProyectoGanadero_BackEnd` | `docker images <nombre-imagen>` | `_<completar> MB_` | Imagen final `serversideup/php:8.4-fpm-nginx` sin dependencias dev |
| **Build Front-End Web** (`dist/`) | `ProyectoGanadero_FrontEnd` | `Get-ChildItem -Recurse dist` / `du -sh dist/` | `_<completar> MB_` | Bundle optimizado por `vue-tsc && vite build` (sin `404.html`) |
| **APK Android Firmado (`v1.1.0`)** | `ProyectoGanadero_FrontEnd` | `ls -lh` / `Get-Item *.apk` | `_<completar> MB_` | Release firmado publicado en GitHub Releases |

---

## 4. Direcciones Públicas y Verificación del Despliegue

### 4.1 Enlaces en Producción

- **Front-End Web SPA (Cloudflare Pages):** `https://<completar>.pages.dev`
- **API REST (Render):** `https://<completar>.onrender.com`
- **Healthcheck API (`/up`):** `https://<completar>.onrender.com/up`
- **Release App Móvil Android (`v1.1.0`):** `https://github.com/<org>/ProyectoGanadero_FrontEnd/releases/tag/v1.1.0`
- **Repositorio Back-End:** `https://github.com/<org>/ProyectoGanadero_BackEnd`
- **Repositorio Front-End:** `https://github.com/<org>/ProyectoGanadero_FrontEnd`

### 4.2 Verificación de Estado, HTTPS y Protección de Rutas

Comandos ejecutados contra el entorno público de producción:

```bash
# 1. Verificación de salud (esperado: HTTP/2 200)
curl -i https://<completar>.onrender.com/up

# 2. Verificación de ruta protegida sin token (esperado: HTTP/2 401 Unauthorized)
curl -i https://<completar>.onrender.com/api/v1/animales
```

**Resultado observado:**

```text
<pegar aquí el encabezado real de curl -i /up (HTTP 200) y de curl -i /api/v1/animales (HTTP 401)>
```

- **Certificado TLS/HTTPS:** Válido y emitido automáticamente tanto en `*.onrender.com` como en `*.pages.dev`.
- **Migraciones y datos en producción:** Ejecutadas contra Aiven for MySQL con certificado SSL (`aiven-ca.pem`) y datos iniciales verificados desde la aplicación web y móvil.
- **Exposición de secretos:** `APP_DEBUG=false` verificado; ante errores o accesos no autorizados no se exponen trazas ni variables de entorno.

**Evidencia visual del entorno desplegado:**

- **Aplicación Web en Cloudflare Pages (HTTPS válido y sesión activa):**  
  `![Front-End en Cloudflare Pages](./evidencias-lab14/01-cloudflare-web-prod.png)`
- **Healthcheck `/up` (200) y endpoint protegido (401) + Panel de Render Live:**  
  `![Render Live y Healthcheck](./evidencias-lab14/02-render-healthcheck.png)`
- **Base de datos MySQL en estado Running (Aiven):**  
  `![Aiven MySQL Running](./evidencias-lab14/03-aiven-mysql-running.png)`

---

## 5. Integración y Entrega Continuas (CI/CD)

### 5.1 Flujo Automatizado

- **Back-End (`.github/workflows/ci.yml`):** En cada `push` a `main` y `pull_request`, levanta PHP 8.4 con `pdo_sqlite` y `mbstring`, instala dependencias, genera configuración aislada de prueba y ejecuta `php artisan test` sobre SQLite en memoria (sin tocar la base de Aiven). El job `deploy` (`needs: [api]`) dispara `RENDER_DEPLOY_HOOK` únicamente si `event_name == 'push'`, la rama es `main` y todas las pruebas aprueban.
- **Front-End (`.github/workflows/ci.yml`):** En cada `push` y `pull_request`, instala dependencias con Node 22 (`npm ci`), ejecuta validación estática, suite unitaria Vitest (**8 archivos / 46 pruebas**) y compilación de producción (`vue-tsc && vite build`). Al fusionar a `main`, Cloudflare Pages construye y publica la nueva versión automáticamente.

### 5.2 Evidencia de Ejecución Exitosa (Verde)

- **Workflow run exitoso (Back-End — Tests + Deploy Hook):** `https://github.com/Haki7w7/ProyectoGanadero_BackEnd/actions/runs/45072759729`
- **Workflow run exitoso (Front-End — Tests + Build):** `_<pegar URL del run en GitHub Actions>_`
- **Captura de pantalla:**  
  ![CI/CD Ejecución Exitosa](./evidencias-lab14/04-ci-verde-deploy.png)

### 5.3 Evidencia de Detención por Prueba Fallida (Rojo)

Para comprobar que el pipeline protege el entorno de producción, se ejecutó un caso con fallo controlado en la suite de pruebas verificando que el job de pruebas falló en rojo y el job `deploy` quedó omitido (`skipped`), manteniendo intacta la versión previa en Render.

- **Workflow run detenido por fallo (Back-End — `deploy` omitido):** `https://github.com/Haki7w7/ProyectoGanadero_BackEnd/actions/runs/45099271836` *(push a `main`; `API Tests (Laravel / PHP 8.4)`: `failure`, `Trigger Render Deploy`: `skipped`)*
- **Captura de pantalla:**  
  ![CI/CD Detenido por Prueba Fallida](./evidencias-lab14/05-ci-rojo-bloqueado.png)

---

## 6. Empaquetado y Publicación de la Aplicación Móvil (`v1.1.0`)

La aplicación móvil comparte el código base Ionic/Vue 3 + Capacitor del repositorio `ProyectoGanadero_FrontEnd`, apuntando a la misma API de producción en Render:

1. **Compilación de producción y sincronización Android:**
   ```bash
   npm run build
   npx cap sync android
   ```
2. **Firma del paquete de distribución:** Generación del APK `release` (`versionName "1.1.0"`) firmado con el almacén de llaves `.jks` resguardado fuera del repositorio mediante `npx cap build android` / Android Studio (`Build → Generate Signed Bundle / APK → APK`).
3. **Autorización CORS para Capacitor:** Los orígenes `https://localhost` (Android Capacitor) y `capacitor://localhost` (iOS) se encuentran registrados en `CORS_ALLOWED_ORIGINS` en Render.
4. **Publicación:** El APK firmado se publicó en GitHub Releases bajo la etiqueta **`v1.1.0`** para instalación directa en dispositivos físicos durante la defensa.

**Evidencia visual de la publicación móvil:**

- **GitHub Release `v1.1.0` con el APK firmado adjunto:**  
  `![GitHub Release APK v1.1.0](./evidencias-lab14/06-github-release-apk.png)`
- **App instalada ejecutándose contra la API de producción:**  
  `![App Móvil en Producción](./evidencias-lab14/07-app-movil-prod.png)`

---

## 7. Limitaciones Conocidas y Notas de Operación para la Defensa

- **Arranque en frío de Render:** El servicio gratuito hiberna tras 15 minutos sin tráfico (~60 s de arranque en la primera solicitud). Antes de iniciar la demostración, realizar una petición previa a `/up` para despertar el contenedor.
- **Suspensión de Aiven:** Verificar en la consola de Aiven que el servicio MySQL esté en estado *Running* antes de la defensa.
- **Disco efímero en Render:** No se persisten archivos binarios en `storage/app`; sesiones y caché residen en MySQL y la identificación de animales/potreros utiliza códigos QR generados dinámicamente y descargables en PNG.