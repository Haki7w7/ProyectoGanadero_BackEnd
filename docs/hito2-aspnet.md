# Hito 2: Porción vertical en ASP.NET Core 8 — Proyecto Ganadero

Implementación de la entidad `Animal` bajo el contrato común congelado en
`docs/contrato-comparativo.md`, como contraparte de Laravel 13 y NestJS.

Stack: ASP.NET Core 8 (Controllers) · Entity Framework Core 8 · SQLite ·
JWT Bearer (`Microsoft.AspNetCore.Authentication.JwtBearer`) · Swashbuckle ·
xUnit + `WebApplicationFactory`.

---

## 1. Métricas cuantitativas de la porción vertical

Las dos primeras ya están medidas sobre el código entregado. Las demás se completan
al ejecutar en la máquina de referencia (ver sección 4).

| Criterio | Valor | Cómo se obtuvo |
| :--- | :--- | :--- |
| **Tiempo de puesta en marcha** | ____ min | Cronómetro desde `dotnet new webapi` hasta el primer 200 OK |
| **Tamaño de la porción** | 15 archivos propios · 822 líneas | `find`/`wc -l`, excluyendo `bin/`, `obj/` y dependencias |
| **Esfuerzo de validación** | 81 líneas · 2 archivos | `Dtos/AnimalDtos.cs` (59) + `Dtos/AuthDtos.cs` (22), vía DataAnnotations |
| **Esfuerzo de autenticación** | 75 líneas · 1 dependencia | `TokenService.cs` (42) + `PasswordHasher.cs` (33); el hash es PBKDF2 del framework, sin paquete externo |
| **Documentación automática** | 1 paquete · ~30 líneas en `Program.cs` | Swashbuckle genera el OpenAPI desde los atributos `[ProducesResponseType]` |
| **Pruebas** | xUnit + WebApplicationFactory · 129 líneas | 3 pruebas de integración equivalentes a las de NestJS y Laravel |
| **Rendimiento** | ____ req/s · p95 ____ ms | Sección 4 |
| **Huella de ejecución** | ____ MB RSS · artefacto ____ MB | Sección 4 |

### Conteo de líneas reproducible

```bash
# Archivos y líneas propias (excluye dependencias y artefactos de compilación)
find . \( -name "*.cs" -o -name "*.csproj" \) \
  -not -path "*/obj/*" -not -path "*/bin/*" | wc -l

find . \( -name "*.cs" -o -name "*.csproj" \) \
  -not -path "*/obj/*" -not -path "*/bin/*" -exec cat {} + | wc -l
```

---

## 2. Cumplimiento del contrato común

| Método | Ruta | Códigos implementados |
| :--- | :--- | :--- |
| POST | `/api/v1/auth/login` | 200 · 401 · 422 |
| GET | `/api/v1/animal` | 200 (con `meta` de paginación) · 401 sin token |
| GET | `/api/v1/animal/{id}` | 200 · 404 |
| POST | `/api/v1/animal` | 201 con encabezado `Location` · 422 por campo |
| PUT | `/api/v1/animal/{id}` | 200 · 404 · 422 |
| DELETE | `/api/v1/animal/{id}` | 204 · 404 · 409 si tiene dependencias |

**Nota de fidelidad al contrato:** ASP.NET Core devuelve 400 por omisión ante un
modelo inválido. Para cumplir el 422 exigido se sobreescribió
`ApiBehaviorOptions.InvalidModelStateResponseFactory` en `Program.cs`. Este es un
hallazgo reportable del comparativo: Laravel devuelve 422 de fábrica y NestJS
también con su `ValidationPipe`; .NET requiere configuración explícita.

El 409 del DELETE se produce cuando el animal está en estado `Vendido`, por la
regla de trazabilidad del dominio.

---

## 3. Guía de instalación y ejecución

**Requisitos:** SDK de .NET 8.0 o superior (`dotnet --version`).

```bash
# 1. Ubicarse en la carpeta
cd backend-aspnet

# 2. Generar la solución
dotnet new sln -n GuateGanado
dotnet sln add GuateGanado.Api/GuateGanado.Api.csproj
dotnet sln add GuateGanado.Tests/GuateGanado.Tests.csproj

# 3. Restaurar dependencias
dotnet restore

# 4. Ejecutar la API (crea y siembra guateganado.db automáticamente)
dotnet run --project GuateGanado.Api

# 5. Ejecutar las tres pruebas
dotnet test
```

**Documentación OpenAPI:** con la API corriendo, en `http://localhost:5000/api/docs`
(el puerto exacto lo imprime `dotnet run`).

**Credenciales sembradas:**

| Correo | Contraseña | Rol |
| :--- | :--- | :--- |
| `admin@guateganado.cr` | `Secret123` | administrador |
| `operario@guateganado.cr` | `Secret123` | operario |

### Cambiar a MySQL o PostgreSQL

Si el equipo decide medir sobre la misma base que Laravel en lugar de SQLite:

```bash
dotnet add GuateGanado.Api package Pomelo.EntityFrameworkCore.MySql   # MySQL
# o
dotnet add GuateGanado.Api package Npgsql.EntityFrameworkCore.PostgreSQL
```

Y en `Program.cs` sustituir `opt.UseSqlite(...)` por `opt.UseMySql(...)` o
`opt.UseNpgsql(...)`, ajustando la cadena en `appsettings.json`.

---

## 4. Procedimiento de medición reproducible

> El enunciado exige que la prueba de carga corra en la misma máquina, con la misma
> base de datos y el mismo escenario para los tres frameworks. Una medición no
> reproducible se evalúa como no presentada. Documentar aquí la máquina usada:
> CPU, RAM, sistema operativo y versiones exactas de PHP, Node y .NET.

### 4.1 Tiempo de puesta en marcha

Cronometrar desde el proyecto vacío hasta el primer endpoint respondiendo 200.
Anotar el tiempo de cada framework por separado y en la misma sesión de trabajo.

### 4.2 Rendimiento (req/s y latencia p95)

Escenario idéntico en los tres: `GET /api/v1/animal?page=1&per_page=15`
autenticado, con los mismos dos registros semilla.

```bash
# Con k6 (recomendado: reporta p95 directamente)
k6 run --vus 50 --duration 30s carga.js

# Alternativa con autocannon
npx autocannon -c 50 -d 30 \
  -H "Authorization=Bearer <TOKEN>" \
  http://localhost:5000/api/v1/animal
```

Ejecutar siempre en modo Release para .NET:
`dotnet run --project GuateGanado.Api -c Release`

Correr una ronda de calentamiento y descartarla antes de la medición buena.

### 4.3 Huella de ejecución

```bash
# Memoria residente en reposo (tras arrancar, sin tráfico)
ps -o rss= -p $(pgrep -f GuateGanado.Api)   # kilobytes

# Tamaño del artefacto desplegable
dotnet publish GuateGanado.Api -c Release -o ./publish
du -sh ./publish
```

Para Laravel medir el proceso de PHP-FPM o `php artisan serve`, y para NestJS el
proceso de Node, con el mismo comando.

---

## 5. Limitaciones declaradas

- El Hito 2 se implementó después de la fecha límite del 25 de octubre.
- Las mediciones de rendimiento y huella están pendientes de ejecución en la
  máquina de referencia y se completan en la sección 1.
- La porción usa SQLite por omisión, igual que la configuración de ejemplo del
  back-end principal. Si Laravel y NestJS se miden sobre otro motor, esta porción
  debe migrarse antes de medir para que la comparación conserve validez.
