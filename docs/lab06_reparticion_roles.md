# Laboratorio 6: Autenticación con Tokens, Autorización por Políticas y Pruebas Automatizadas

**Proyecto:** `ProyectoGanadero_BackEnd`  
**Objetivo de Calificación:** 100 / 100 Pts (Nivel EXCELENTE en todos los criterios)  
**Estructura del Equipo:** 6 Personas organizadas en **2 Células de Trabajo (3 vs 3)**  
**Plazo de Entrega:** ⏰ **2 Días** (Cierre: Domingo 23:55) — *Sprint Intensivo de Alta Eficiencia*  

---

## 1. Rúbrica Oficial y Distribución de Puntos (100 Pts)

Para asegurar la nota perfecta (100%), el equipo debe cumplir la totalidad de los 5 criterios de la rúbrica oficial y al mismo tiempo saldar las observaciones de los Laboratorios 4 y 5.

| Criterio | Pts | Nivel EXCELENTE (100%) | Nivel ACEPTABLE (70%) |
|---|:---:|---|---|
| **1. Autenticación y manejo de sesión** | **20** | Token con expiración y capacidades (abilities), revocación al cerrar sesión (`logout`) y contraseñas derivadas con reglas de complejidad. | Autenticación funcional con debilidades (sin expiración o sin revocación). |
| **2. Autorización por roles y políticas** | **20** | Mínimo 3 roles y políticas por recurso **verificados en dos capas** (Capa 1: rutas/controladores, Capa 2: servicios), con evidencia de acceso denegado (403). | Verificación únicamente en la ruta o sin políticas por recurso. |
| **3. Protección del inicio de sesión** | **10** | Limitación de intentos de login (**Rate Limiting / Throttle 429**) y respuesta anti-enumeración de cuentas (mensaje uniforme). | Solo uno de los dos controles implementado. |
| **4. Pruebas unitarias de servicio** | **25** | **12 o más pruebas** de servicio con dobles de prueba (mocks), cubriendo camino feliz, reglas de negocio (RN-01 a RN-07) y al menos 2 casos límite. | Entre 6 y 11 pruebas o cobertura desigual. |
| **5. Pruebas de API, aislamiento y cobertura** | **25** | **6 pruebas de API**, base de datos de pruebas aislada (`:memory:` en `phpunit.xml`) y **cobertura $\ge$ 70%**. | Cobertura entre 50% y 69% o sin aislamiento de BD. |
| **TOTAL** | **100** | **Meta del equipo** | |

---

## 2. Estrategia de Organización: 2 Células de 3 Personas (3 vs 3)

En lugar de separar artificialmente "quién hace correcciones viejas" y "quién hace el lab nuevo", el equipo se divide en **dos frentes técnicos especializados y balanceados**:

```
┌────────────────────────────────────────────────────────┐     ┌────────────────────────────────────────────────────────┐
│  CÉLULA 1: DESARROLLO, SEGURIDAD & CORE (3 Personas)   │     │  CÉLULA 2: TESTING, OPENAPI, POSTMAN & QA (3 Personas) │
│  "Construyen la lógica nueva y blindan el código"      │     │  "Garantizan el 100% de la rúbrica, tests y entrega"   │
├────────────────────────────────────────────────────────┤     ├────────────────────────────────────────────────────────┤
│ • Persona 1: Autenticación, Expiración, Logout,        │     │ • Persona 4: 12+ Pruebas Unitarias de Servicio con     │
│              Abilities, Throttle 429 & Fix JSON 400.   │     │              Mocks (cubre RN-01 a RN-07 y Capa 2).     │
│                                                        │     │                                                        │
│ • Persona 2: Autorización en Dos Capas (Policies en    │     │ • Persona 5: 6+ Pruebas Feature de API, Aislamiento    │
│              Controladores + Gate en Servicios).       │     │              con SQLite :memory: y Cobertura >= 70%.   │
│                                                        │     │                                                        │
│ • Persona 3: Roles de Usuario, Localización es/lang,   │     │ • Persona 6: OpenAPI con ejemplos, Postman {{base_url}}│
│              Fix per_page=-5 y UserResource en /user.  │     │              y Documento Final de Evidencias.          │
└────────────────────────────────────────────────────────┘     └────────────────────────────────────────────────────────┘
```

---

## 3. Fichas de Trabajo por Persona

---

### 🏛️ CÉLULA 1: DESARROLLO, SEGURIDAD Y BACKEND CORE

---

#### 👤 Persona 1: Autenticación, Expiración, Revocación (Logout) y Throttle
* **Criterios asociados:** Criterio 1 (20 pts), Criterio 3 (10 pts) y robustez HTTP.
* **Archivos a modificar/crear:**
  - `config/sanctum.php`
  - `app/Http/Controllers/Auth/LogoutController.php`
  - `app/Http/Controllers/LoginController.php`
  - `app/Http/Requests/RegisterRequest.php`
  - `bootstrap/app.php` (Manejo de JSON malformado 400)
  - `routes/api.php`

**Tareas asignadas:**
1. **Configurar Expiración de Tokens:** En `config/sanctum.php`, establecer `'expiration' => 60` (minutos).
2. **Endpoint de Revocación (`POST /api/v1/auth/logout`):**
   - Ruta protegida por `auth:sanctum`.
   - Revocar el token activo: `$request->user()->currentAccessToken()->delete();`.
   - Responder `200 OK` con `{ "message": "Sesión cerrada correctamente. Token revocado." }`.
3. **Capacidades (Abilities) en Tokens según Rol:**
   - En login y register, asignar abilities:
     - `admin` $\rightarrow$ `['*']`
     - `veterinario` $\rightarrow$ `['animales:read', 'tratamientos:manage', 'pesajes:create']`
     - `operario` $\rightarrow$ `['animales:read', 'pesajes:create', 'potreros:read']`
4. **Protección Anti-Enumeración y Rate Limiting (Throttle 429):**
   - En `LoginController`, aplicar `RateLimiter::tooManyAttempts($key, 5)`. Si se excede, responder `429 Too Many Requests`.
   - Unificar la respuesta de fallo a: `{ "error": "No autenticado", "mensaje": "Las credenciales proporcionadas son incorrectas." }` con código `401`.
5. **Política de Complejidad de Contraseñas:**
   - En `RegisterRequest`, exigir: `Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()`.
6. **Subsanación Lab 5 (JSON Malformado $\rightarrow$ 400 Bad Request):**
   - En `bootstrap/app.php`, interceptar `\JsonException` o sintaxis corrupta en peticiones con `Content-Type: application/json` para responder `400 Bad Request` en lugar de `422`.

---

#### 👤 Persona 2: Autorización en Dos Capas (Controladores y Servicios)
* **Criterios asociados:** Criterio 2 (20 pts - Verificación obligatoria en 2 capas).
* **Archivos a modificar/crear:**
  - `app/Policies/AnimalPolicy.php`
  - `app/Policies/TratamientoAnimalPolicy.php`
  - `app/Policies/PesajePolicy.php`
  - `app/Policies/PotreroPolicy.php`
  - `app/Http/Controllers/AnimalController.php` (y controladores vinculados)
  - `app/Services/AnimalService.php`
  - `app/Services/TratamientoAnimalService.php`
  - `app/Services/PesajeService.php`
  - `bootstrap/app.php`

**Tareas asignadas:**
1. **Creación de Model Policies (php artisan make:policy):**
   - `AnimalPolicy`: `viewAny` y `view` para todos los roles; `create`, `update`, `delete` exclusivos de `admin`.
   - `TratamientoAnimalPolicy`: `view` para `admin` y `veterinario`; `create`, `update`, `delete` solo para `veterinario` y `admin` (operario denegado).
   - `PesajePolicy`: `create` para `operario` y `admin`; `delete` exclusivo de `admin`.
2. **Capa 1 de Autorización (Controlador / Ruta):**
   - Proteger los métodos con `$this->authorize('delete', $animal)` o middleware de ruta `middleware('can:delete,animal')`.
3. **Capa 2 de Autorización (Dentro del Servicio de Dominio):**
   - El laboratorio exige que una invocación directa al servicio sin pasar por la ruta HTTP también sea rechazada.
   - En los métodos de servicio (`AnimalService::eliminarAnimal`, `TratamientoAnimalService::crearTratamientoAnimal`, etc.), invocar `Gate::authorize('delete', $animal)`.
4. **Respuesta 403 Estandarizada:**
   - En `bootstrap/app.php`, mapear `AuthorizationException` para responder siempre con JSON uniforme:
     `{ "error": "Acceso denegado", "mensaje": "No tiene permisos para realizar esta acción." }` (código 403).

---

#### 👤 Persona 3: Roles, Subsanaciones de Paginación y Localización
* **Criterios asociados:** Criterio 2 (Modelo de roles), Subsanación Lab 4 y Subsanación Lab 5.
* **Archivos a modificar/crear:**
  - `app/Models/User.php`
  - `app/Services/PotreroService.php`
  - `config/app.php` y `.env`
  - `lang/es/validation.php`
  - `routes/api.php`
  - `app/Http/Resources/UserResource.php`

**Tareas asignadas:**
1. **Modelo de Roles en `User.php`:**
   - Garantizar los 3 roles en BD: `admin`, `veterinario`, `operario`.
   - Implementar métodos helpers: `isAdmin(): bool`, `isVeterinario(): bool`, `isOperario(): bool`.
2. **Subsanación Crítica Lab 4 & 5 (Error 500 en `per_page=-5`):**
   - En `app/Services/PotreroService.php` línea 23, corregir:
     ```php
     // Antes: min((int) ($filtros['per_page'] ?? 15), 100);
     $perPage = min(max((int) ($filtros['per_page'] ?? 15), 1), 100);
     ```
3. **Subsanación Lab 4 (Mensajes de Validación en Español):**
   - Cambiar en `.env`: `APP_LOCALE=es`.
   - Cambiar en `config/app.php`: `'locale' => 'es'`.
   - Crear `lang/es/validation.php` con las traducciones canónicas de Laravel para las reglas `string`, `integer`, `numeric`, `max.numeric`, `max.string`, `min`, etc., resolviendo que no aparezcan mensajes en inglés.
4. **Subsanación Lab 5 (Desacoplamiento de `/user`):**
   - En `routes/api.php`, actualizar la ruta `GET /api/user` para que no retorne el modelo directo, sino:
     ```php
     Route::get('/user', fn (Request $req) => new \App\Http\Resources\UserResource($req->user()))->middleware('auth:sanctum');
     ```

---

### 🧪 CÉLULA 2: TESTING, OPENAPI, POSTMAN Y QA FINAL

---

#### 👤 Persona 4: Suite de 12+ Pruebas Unitarias de Servicio con Mocks
* **Criterio asociado:** Criterio 4 (25 pts - Pruebas unitarias con dobles de prueba).
* **Archivos a modificar/crear:**
  - `tests/Unit/Services/AnimalServiceTest.php`
  - `tests/Unit/Services/PotreroServiceTest.php`
  - `tests/Unit/Services/PesajeServiceTest.php`
  - `tests/Unit/Services/TratamientoServiceTest.php`
  - `tests/Unit/Services/ReglasNegocioUnitariasTest.php`

**Tareas asignadas (12 Pruebas Unitarias Mínimas):**
1. **Camino Feliz (Happy Path - 3 pruebas con mocks):**
   - Creación exitosa de animal cuando el potrero tiene cupo.
   - Registro exitoso de pesaje para un animal existente.
   - Aplicación de tratamiento médico válida.
2. **Violación de Reglas de Negocio (RN-01 a RN-07 - 7 pruebas):**
   - *Test RN-01:* Eliminar animal con pesajes $\rightarrow$ lanza `ReglaNegocioException` (422).
   - *Test RN-02:* Eliminar potrero con animales $\rightarrow$ lanza `ReglaNegocioException` (422 / 409).
   - *Test RN-03:* Asignar animal a potrero con capacidad máxima llena $\rightarrow$ rechazo con 422.
   - *Test RN-04:* Eliminar categoría con insumos asociados $\rightarrow$ rechazo con 422.
   - *Test RN-05:* Eliminar raza asignada a animales $\rightarrow$ rechazo con 422.
   - *Test RN-06:* Eliminar tratamiento ya aplicado $\rightarrow$ rechazo con 422.
   - *Test RN-07:* Eliminar unidad de medida en uso $\rightarrow$ rechazo con 422.
   *(Esto salda definitivamente la penalización del Lab 4 de falta de pruebas en RN-03 a RN-07)*.
3. **Casos Límite (Boundary Cases - 2 pruebas):**
   - *Caso Límite 1:* Potrero con capacidad máxima exacta (admite el registro $N$, pero rechaza el $N+1$).
   - *Caso Límite 2:* Paginación extrema (`per_page=-5` se ajusta a 1; `per_page=500` se ajusta a 100).
4. **Prueba de Demostración de Capa 2 (Servicio):**
   - Iniciar sesión en el test como usuario `operario` (`$this->actingAs($operario)`).
   - Llamar directamente al método de servicio `$animalService->eliminarAnimal($animal->id_animal)`.
   - Verificar con `$this->expectException(AuthorizationException::class)` que el servicio rechaza la ejecución sin pasar por HTTP.

---

#### 👤 Persona 5: Pruebas de API (Feature Tests), Aislamiento de BD y Cobertura
* **Criterio asociado:** Criterio 5 (25 pts - 6+ pruebas de API, aislamiento y cobertura $\ge$ 70%).
* **Archivos a modificar/crear:**
  - `phpunit.xml`
  - `tests/Feature/AutenticacionApiTest.php`
  - `tests/Feature/AutorizacionRolesApiTest.php`
  - `tests/Feature/SemanticaRespuestasApiTest.php`

**Tareas asignadas:**
1. **Aislamiento de la Base de Datos:**
   - Configurar `phpunit.xml` para que la suite se ejecute sobre SQLite en memoria sin tocar datos de desarrollo:
     ```xml
     <env name="DB_CONNECTION" value="sqlite"/>
     <env name="DB_DATABASE" value=":memory:"/>
     ```
2. **Implementación de 6 Pruebas de API (Feature Tests):**
   - *Test 1 (Acceso Permitido según Rol):* Usuario `admin` ejecuta `DELETE /api/v1/animales/{id}` y responde `204 No Content`.
   - *Test 2 (Acceso Denegado según Rol):* Usuario `operario` intenta ejecutar `DELETE /api/v1/animales/{id}` y responde `403 Forbidden`.
   - *Test 3 (Estructura de Respuesta Uniforme):* Petición `GET /api/v1/animales` devuelve el sobre canónico `{ message, data, meta, links }` con `200 OK`.
   - *Test 4 (Semántica de Creación y Location):* Petición `POST /api/v1/animales` responde con `201 Created` y la cabecera `Location`.
   - *Test 5 (Revocación de Token en Logout):* Login exitoso $\rightarrow$ llamada a `POST /api/v1/auth/logout` $\rightarrow$ petición subsecuente con el mismo token responde `401 Unauthorized`.
   - *Test 6 (Protección contra Fuerza Bruta):* 6 intentos fallidos de login consecutivos responden `429 Too Many Requests`.
3. **Asegurar Cobertura $\ge$ 70%:**
   - Ejecutar `php artisan test --coverage-text --min=70` y añadir pruebas complementarias si alguna capa queda por debajo del umbral exigido.

---

#### 👤 Persona 6: OpenAPI, Postman, Subsanaciones de Docs y Entrega Final
* **Criterios asociados:** Documentación OpenAPI (Lab 5 & 6), Colección Postman y Entrega.
* **Archivos a modificar/crear:**
  - `docs/OpenApi.yaml`
  - `docs/GuateGanado_API.postman_collection.json`
  - `docs/GuateGanado_Env.postman_environment.json`
  - `docs/lab06_entrega_final.md`

**Tareas asignadas:**
1. **Actualizar Especificación OpenAPI (`docs/OpenApi.yaml`):**
   - Documentar el nuevo endpoint `POST /api/v1/auth/logout`.
   - Corregir códigos de respuesta (`POST` con `201` y `DELETE` con `204`).
   - Documentar las respuestas de error globales en los paths: `401`, `403`, `429`, `404`, `409` y `422`.
   - Agregar bloques `example:` representativos tanto en cuerpos de solicitud (`requestBody`) como en respuestas `200` y `201`.
2. **Estandarizar Colección y Entorno de Postman:**
   - En `GuateGanado_Env.postman_environment.json`, agregar la variable `base_url` (`http://localhost:8000/api/v1`).
   - En `GuateGanado_API.postman_collection.json`, reemplazar todas las URLs quemadas por `{{base_url}}`.
   - Agregar peticiones de:
     - Login exitoso (guarda `auth_token`).
     - Acceso denegado (403 con usuario operario intentando eliminar).
     - Logout (revoca token).
3. **Elaborar Documento Final de Entrega (`docs/lab06_entrega_final.md`):**
   - Compilar las evidencias requeridas por la rúbrica:
     - Captura de `php artisan test` con los 18+ tests aprobados sin fallos.
     - Captura del reporte de cobertura $\ge 70\%$.
     - Evidencias de revocación de token y de rate limiting (429).
     - Demostración de la autorización verificada en dos capas (controlador y servicio).

---

## 4. Cronograma Acelerado de 2 Días (Sprint de 48 Horas)

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ DÍA 1 (SÁBADO): CONSTRUCCIÓN DE CÓDIGO, DOBLE CAPA Y PARCHES PREVIOS                   │
├──────────────────────────────┬─────────────────────────────────────────────────────────┤
│ Mañana (08:00 - 13:00)       │ • Persona 3: Aplica fix per_page=-5, lang/es y User.   │
│ Subsanación & Core de Auth   │ • Persona 1: Configura expiración, logout y abilities.  │
│                              │ • Persona 2: Define roles en User y crea Model Policies │
│                              │ • Persona 5: Configura phpunit.xml con SQLite :memory:. │
├──────────────────────────────┼─────────────────────────────────────────────────────────┤
│ Tarde (14:00 - 19:00)        │ • Persona 1: Implementa Rate Limiting y anti-enumeración│
│ Doble Capa & Estructura Tests│ • Persona 2: Inyecta can: en controladores y rutas (C1).│
│                              │ • Persona 2/3: Inyectan Gate::authorize() en Services(C2│
│                              │ • Persona 4: Estructura suite de mocks para servicios.  │
│                              │ • Persona 6: Limpia URLs de Postman con {{base_url}}.   │
└──────────────────────────────┴─────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────────────────────────┐
│ DÍA 2 (DOMINGO): SUITE DE PRUEBAS (18+ TESTS), COBERTURA, OPENAPI Y ENTREGA            │
├──────────────────────────────┬─────────────────────────────────────────────────────────┤
│ Mañana (08:00 - 13:00)       │ • Persona 4: Escribe las 12+ pruebas unitarias con      │
│ Pruebas Unitarias & Feature  │   mocks (Happy path, RN-01..07 y Capa 2).               │
│                              │ • Persona 5: Escribe las 6+ pruebas Feature de API      │
│                              │   (roles, códigos 201/204/403, revocación y throttle).  │
├──────────────────────────────┼─────────────────────────────────────────────────────────┤
│ Tarde (14:00 - 18:00)        │ • Persona 5: Corre cobertura de código (>= 70%).        │
│ Cobertura & OpenAPI          │ • Persona 6: Actualiza OpenApi.yaml con ejemplos y 403. │
├──────────────────────────────┼─────────────────────────────────────────────────────────┤
│ Noche (18:00 - 22:00)        │ • Persona 6 y Equipo: Ejecutan php artisan test, arman  │
│ QA Final, Merge y Entrega    │   docs/lab06_entrega_final.md con evidencias y entregan │
│                              │   en Mediación Virtual antes de las 23:55.              │
└──────────────────────────────┴─────────────────────────────────────────────────────────┘
```

---

## 5. Checklist de Verificación Final (Garantía de 100 Pts)

- [ ] ¿`POST /api/v1/auth/login` aplica rate limiting y responde `429 Too Many Requests` tras 5 intentos fallidos?
- [ ] ¿El mensaje de credenciales erróneas no distingue entre usuario inexistente y contraseña inválida?
- [ ] ¿`POST /api/v1/auth/logout` revoca el token y llamadas posteriores devuelven `401 Unauthorized`?
- [ ] ¿El token tiene tiempo de expiración definido en `config/sanctum.php`?
- [ ] ¿Existen al menos 3 roles (`admin`, `veterinario`, `operario`) y Policies por recurso?
- [ ] ¿La autorización se verifica en dos capas (controlador y dentro del servicio)?
- [ ] ¿Existe una prueba unitaria demostrando que llamar al servicio sin permisos lanza `AuthorizationException`?
- [ ] ¿Se cuenta con al menos 12 pruebas unitarias de servicio con dobles de prueba (mocks)?
- [ ] ¿Se cubren el camino feliz, las 7 reglas de negocio (RN-01 a RN-07) y al menos 2 casos límite?
- [ ] ¿Se cuenta con al menos 6 pruebas de API que validan códigos HTTP, estructura JSON y roles?
- [ ] ¿La base de datos de pruebas está aislada en `:memory:` sin modificar datos de desarrollo?
- [ ] ¿El reporte de cobertura de código alcanza o supera el 70%?
- [ ] ¿`GET /api/v1/potreros?per_page=-5` responde `200 OK` sin arrojar error 500?
- [ ] ¿La colección de Postman utiliza `{{base_url}}` en el 100% de las solicitudes?
- [ ] ¿`docs/OpenApi.yaml` contiene ejemplos y documenta los errores 401, 403, 429, 404, 409 y 422?
