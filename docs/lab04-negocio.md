# Laboratorio 4: Capa de Negocio, Validaciones y Consultas Avanzadas

**Sistema de Gestión Ganadera (ProyectoGanadero_BackEnd)**  
**Versión API:** `v1` (`/api/v1/...`)

---

## 1. Arquitectura y Componentes Implementados

En este laboratorio se desacopló la lógica de negocio y validación de los controladores hacia capas especializadas:

1. **FormRequest (`app/Http/Requests`):** 18 clases de validación declarativa (Store y Update para las 9 entidades) con reglas de integridad y mensajes de error en español.
2. **Servicios (`app/Services`):** 9 clases de servicio dedicadas que encapsulan las operaciones de dominio, consultas con Eloquent (`when()`, `paginate()`), ordenamiento seguro y transacciones atómicas (`DB::transaction`).
3. **Controladores Delgados (`app/Http/Controllers`):** Controladores REST que no contienen lógica de negocio ni consultas directas a base de datos. Inyectan el servicio correspondiente y el FormRequest. Cada método no supera las 15 líneas.
4. **Excepción de Negocio (`ReglaNegocioException`):** Excepción personalizada (`app/Exceptions/ReglaNegocioException.php`) que maneja errores de dominio y retorna automáticamente una respuesta estructurada en formato JSON con su respectivo código HTTP (422 o 404).

---

## 2. Reglas de Negocio Implementadas

| Identificador | Nombre de la Regla | Servicio y Método | Código HTTP | Mensaje de Error / Comportamiento |
| :--- | :--- | :--- | :---: | :--- |
| **RN-01** | Integridad Histórica de Pesajes | `AnimalService::eliminarAnimal` | `422` | No se puede eliminar un animal que posea registros de pesaje asociados. |
| **RN-02** | Ocupación de Potreros | `PotreroService::eliminarPotrero` | `422` | No se puede eliminar un potrero que actualmente tenga animales asignados. |
| **RN-03** | Capacidad Máxima de Potreros | `AnimalService::validarCapacidadPotrero` | `422` | El potrero ha alcanzado su capacidad máxima permitida de cabezas de ganado. |
| **RN-04** | Dependencia de Categorías | `CategoriaService::eliminarCategoria` | `422` | No se puede eliminar una categoría que tenga insumos registrados bajo ella. |
| **RN-05** | Preservación de Razas | `RazaService::eliminarRaza` | `422` | No se puede eliminar una raza asignada a uno o más animales existentes. |
| **RN-06** | Historial de Tratamientos Sanitarios | `TratamientoService::eliminarTratamiento` | `422` | No se puede eliminar un tratamiento que ya haya sido aplicado a animales. |
| **RN-07** | Dependencia de Unidades de Medida | `UnidadMedidaService::eliminarUnidadMedida` | `422` | No se puede eliminar una unidad de medida vinculada a insumos en inventario. |

### Detalle y Comportamiento Esperado

#### RN-01: No eliminar animal con historial de pesajes
- **Condición:** Si `Animal::pesajes()->exists()` es verdadero.
- **Respuesta JSON:**
  ```json
  {
    "error": "Regla de Negocio",
    "mensaje": "No se puede eliminar el animal porque tiene pesajes registrados."
  }
  ```

#### RN-02: No eliminar potrero con animales asignados
- **Condición:** Si `Potrero::animales()->exists()` es verdadero.
- **Respuesta JSON:**
  ```json
  {
    "error": "Regla de Negocio",
    "mensaje": "No se puede eliminar el potrero porque tiene animales asignados."
  }
  ```

#### RN-03: Validación de capacidad de potrero
- **Condición:** Al crear o reasignar un animal a un potrero, si la cantidad actual de animales activos en el potrero es $\ge$ `potrero.capacidad`.
- **Respuesta JSON:**
  ```json
  {
    "error": "Regla de Negocio",
    "mensaje": "El potrero seleccionado ha alcanzado su capacidad máxima (X animales)."
  }
  ```

#### RN-04 a RN-07: Integridad referencial en catálogos
- Evitan la eliminación en cascada indeseada o la existencia de registros huérfanos en Insumos, Razas, Tratamientos y Unidades de Medida.
- **Respuesta JSON uniforme:** Código `422 Unprocessable Entity` con detalle de la entidad que impide el borrado.

---

## 3. Comportamiento del Sistema y Consultas Avanzadas

### 3.1 Paginación con Límite de Seguridad (Tope Máximo)
- **Implementación:** Los 9 servicios aplican un control numérico estricto al parámetro `per_page`:
  ```php
  $perPage = min(max((int) ($filtros['per_page'] ?? 15), 1), 100);
  ```
- **Comportamiento:** Si un cliente solicita `GET /api/v1/animales?per_page=500`, el sistema automáticamente ajusta la cantidad devuelta a un máximo de **100 registros por página**, evitando ataques de denegación de servicio por agotamiento de memoria en el servidor.

### 3.2 Filtros Dinámicos Combinables
- **Implementación:** Construcción dinámica de queries mediante cláusulas condicionales `when()` de Eloquent:
  ```php
  Animal::query()
      ->when(!empty($filtros['raza_id']), fn ($q) => $q->porRaza($filtros['raza_id']))
      ->when(!empty($filtros['potrero_id']), fn ($q) => $q->porPotrero($filtros['potrero_id']))
      ->when(!empty($filtros['sexo']), fn ($q) => $q->porSexo($filtros['sexo']))
      ->when(!empty($filtros['estado']), fn ($q) => $q->porEstado($filtros['estado']));
  ```
- **Comportamiento:** Los filtros son opcionales y acumulativos. Por ejemplo:  
  `GET /api/v1/animales?estado=Activo&sexo=Hembra&raza_id=1` aplica únicamente los criterios provistos sin romper la consulta si alguno de ellos se omite.

### 3.3 Ordenamiento Seguro (Whitelist)
- **Implementación:** Whitelist de campos permitidos para prevenir inyecciones SQL o errores de base de datos:
  ```php
  $allowedSortFields = ['numero_arete', 'fecha_nacimiento', 'estado', 'id_animal'];
  $sortBy = in_array($filtros['sort_by'] ?? '', $allowedSortFields, true) ? $filtros['sort_by'] : 'id_animal';
  $sortOrder = strtolower($filtros['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
  ```
- **Comportamiento:** Parámetros como `sort_by=numero_arete&order=desc` ordenan la salida. Si se pasa un campo inexistente o malicioso, el sistema recurre al campo primario por defecto de forma transparente.

### 3.4 Transaccionalidad ACID y Reversión (Rollback)
- **Implementación:** Operaciones complejas o de escritura mutua se ejecutan dentro de bloques `DB::transaction(function () { ... })`.
- **Demostración Multi-Entidad:** El método `AnimalService::registrarAnimalConPesaje(array $datosAnimal, array $datosPesaje)` crea el animal y su pesaje inicial dentro de una misma transacción.
- **Comportamiento ante fallos:** Si la creación del pesaje falla (por ejemplo, peso no numérico, fecha inválida o excepción imprevista), la transacción se aborta por completo. El registro del animal se revierte automáticamente en la base de datos (Rollback), garantizando que no queden datos inconsistentes o huérfanos.

---

## 4. Guía de Ejecución para Captura de Evidencias (Postman / HTTP Client)

### Evidencia 1: Operaciones CRUD Básicas
1. **Crear Potrero (POST `/api/v1/potreros`):**
   - Body JSON:
     ```json
     {
       "nombre": "Potrero Norte 1",
       "capacidad": 15,
       "area": 2500.50,
       "estado_pasto": "Excelente"
     }
     ```
   - Código esperado: `201 Created`.
2. **Listar Potreros (GET `/api/v1/potreros`):**
   - Código esperado: `200 OK` (con meta de paginación: `current_page`, `data`, `per_page`, etc.).
3. **Actualizar Potrero (PUT `/api/v1/potreros/1`):**
   - Body JSON: `{"nombre": "Potrero Norte Modificado"}`.
   - Código esperado: `200 OK`.
4. **Eliminar Potrero sin dependencias (DELETE `/api/v1/potreros/{id_sin_animales}`):**
   - Código esperado: `200 OK` o `204 No Content`.

### Evidencia 2: Rechazo por Datos Inválidos (FormRequest - Código 422)
- **Petición:** `POST /api/v1/animales` con cuerpo vacío o campos requeridos faltantes:
  ```json
  {
    "numero_arete": "",
    "sexo": "Invalido"
  }
  ```
- **Respuesta esperada:** `422 Unprocessable Entity` con matriz de errores en español:
  ```json
  {
    "message": "Los datos proporcionados no son válidos.",
    "errors": {
      "numero_arete": ["El número de arete es obligatorio."],
      "sexo": ["El campo sexo debe ser Macho o Hembra."],
      "fecha_nacimiento": ["La fecha de nacimiento es obligatoria."]
    }
  }
  ```

### Evidencia 3: Violación de Reglas de Negocio (ReglaNegocioException - Código 422)
- **Caso A (RN-02):** Intentar eliminar un potrero que tiene animales asignados:
  - `DELETE /api/v1/potreros/1`
  - Código esperado: `422 Unprocessable Entity`.
  - Respuesta: `{"error": "Regla de Negocio", "mensaje": "No se puede eliminar el potrero porque tiene animales asignados."}`
- **Caso B (RN-01):** Intentar eliminar un animal con pesajes registrados:
  - `DELETE /api/v1/animales/1`
  - Código esperado: `422 Unprocessable Entity`.
  - Respuesta: `{"error": "Regla de Negocio", "mensaje": "No se puede eliminar el animal porque tiene pesajes registrados."}`

### Evidencia 4: Reversión Transaccional (Rollback)
- Puede validarse ejecutando la suite de pruebas unitarias/funcionales:
  ```bash
  php artisan test --filter=ReglasNegociosTest
  ```
  El test `test_rollback_transaccional_revierte_animal_si_pesaje_falla` comprueba formalmente que si el pesaje falla a mitad del proceso, `Animal::count()` no se incrementa y la base de datos permanece limpia.

---

## 5. Registro de Pruebas y Evidencias por Integrante

### Evidencias de Validaciones Declarativas
- Se enviaron datos incompletos en `POST /api/v1/animales` y el sistema rechazó la petición con código `422 Unprocessable Entity`, devolviendo los mensajes de error asociados individualmente en español.
- Se verificó la restricción de unicidad del `numero_arete` evitando duplicados en base de datos.

### Evidencias de Consultas Avanzadas
- **Paginación y Tope Máximo:** Se probó `GET /api/v1/animales?per_page=500`. La API aplicó el límite seguro de memoria restringiendo el resultado a `per_page: 100`.
- **Filtros Combinables:** Se corrió `GET /api/v1/animales?estado=Activo&sexo=Macho` y se utilizó la cláusula `when()` de Eloquent para retornar solo los registros coincidentes.
- **Ordenamiento Dinámico:** Se probó `GET /api/v1/animales?sort_by=numero_arete&order=desc` ordenando la respuesta de forma ascendente/descendente según el parámetro.

### Evidencias de Integridad y Reglas de Negocio
- Se ejecutó la suite completa de pruebas automatizadas en PHPUnit/Pest:
  ```
  PASS Tests\Feature\ReglasNegociosTest
  ✓ no se puede eliminar un animal con pesajes registrados
  ✓ se puede eliminar un animal sin pesajes
  ✓ no se puede eliminar un potrero con animales
  ✓ se puede eliminar un potrero vacio
  ✓ rollback transaccional revierte animal si pesaje falla

  PASS Tests\Feature\AnimalControllerTest
  PASS Tests\Feature\PotreroControllerTest

  Tests: 13 passed (43 assertions)
  Duration: 1.52s
  ```