# Contrato Comparativo - Hito 0
**Sistema de Gestión Ganadero (GuateGanado QR)**

**1. Entidad Seleccionada: Animal**

La entidad `Animal` representa el núcleo del sistema de trazabilidad por código QR. A continuación, se presentan los 7 atributos para ser replicada en los frameworks NestJS y ASP.NET Core:

**Atributos de Animal:**

| Atributo | Tipo de Dato | Restricciones | Descripción |
| :--- | :--- | :--- | :--- |
| **id** | Integer / Auto-increment | Primary Key | Identificador único de cada animal. |
| **numero_arete** | String(50) | Unique, Not Null | Número del arete visible (ej: CR-8845-LIB). |
| **raza** | String(80) | Not Null | Raza del animal (ej: Brahman, Nelore). |
| **sexo** | String(10) | Not Null | Sexo del animal (ej: Macho, Hembra). |
| **fecha_nacimiento** | Date | Not Null | Fecha de nacimiento (YYYY-MM-DD). |
| **estado** | String(20) | Not Null | Estado actual (ej: Activo, Vendido, Enfermo). |
| **potrero_id** | Integer | Foreign Key, Not Null | Identificador único del potrero/finca al que pertenece el animal. |

---

**2. Los 6 Endpoints del Contrato Común**

| Método | Ruta | Descripción | Código HTTP Esperado |
| :--- | :--- | :--- | :--- |
| **POST** | `/api/v1/auth/login` | Autenticación de usuario (operario/administrador) para obtener el token JWT. | 200 OK / 401 Unauthorized |
| **GET** | `/api/v1/animal` | Listado paginado de animales. Soporta filtro por el `numero_arete` obtenido tras el escaneo del QR físico (`?arete=CR-8845-LIB`). | 200 OK |
| **GET** | `/api/v1/animal/{id}` | Obtiene la ficha completa del animal por su ID tras escanear el QR (incluyendo raza, sexo, fecha, estado, potrero e historial). | 200 OK / 404 Not Found |
| **POST** | `/api/v1/animal` | Registra un nuevo animal procesando sus datos de finca (`numero_arete`, `raza`, `sexo`, `fecha_nacimiento`, `potrero_id`, `estado`). | 201 Created / 422 Unprocessable Content |
| **PUT** | `/api/v1/animal/{id}` | Actualiza el perfil del animal mediante su ID tras la lectura del QR (ej. cambio de estado a vendido/fallecido, o reasignación de `potrero_id`). | 200 OK / 400 Bad Request |
| **DELETE** | `/api/v1/animal/{id}` | Elimina el registro de un animal del sistema utilizando su ID interno. | 204 No Content / 404 Not Found |

---

**3. Datos Semillas para la Prueba Comparativa**

```json
[
  {
    "numero_arete": "CR-1020-LIB",
    "raza": "Brahman",
    "sexo": "Hembra",
    "fecha_nacimiento": "2023-05-15",
    "estado": "Activo",
    "potrero_id": 1
  },
  {
    "numero_arete": "CR-1021-LIB",
    "raza": "Nelore",
    "sexo": "Macho",
    "fecha_nacimiento": "2022-11-20",
    "estado": "Activo",
    "potrero_id": 1
  }
]