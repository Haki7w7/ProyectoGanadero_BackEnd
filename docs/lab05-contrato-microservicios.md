# Lab 05 — Diseño y contrato del microservicio

## 1. Nombre del microservicio

**Microservicio de Generación de Reportes y Certificados Ganaderos**

Este microservicio tiene como propósito encargarse de la generación de reportes y certificados relacionados con la operación ganadera del sistema.

La responsabilidad del servicio se mantiene acotada a la creación, consulta y entrega de documentos generados a partir de la información proporcionada por el sistema principal.

---

## 2. Responsabilidad del microservicio

El microservicio será responsable de:

* Recibir solicitudes para generar reportes.
* Generar certificados ganaderos.
* Consultar el estado de una generación.
* Permitir la consulta de reportes y certificados generados.
* Proporcionar acceso al documento generado.
* Validar los datos mínimos necesarios para generar un documento.
* Informar errores mediante códigos HTTP y respuestas JSON.
* Gestionar temporalmente las solicitudes que no puedan ser procesadas inmediatamente.

El microservicio **no será responsable de administrar directamente** usuarios, fincas, animales, productores u otras entidades pertenecientes al sistema principal.

Su función será utilizar la información necesaria para producir los documentos solicitados.

---

## 3. Justificación técnica de la separación

La generación de reportes y certificados puede involucrar operaciones que requieren una cantidad considerable de recursos, especialmente cuando los documentos contienen grandes cantidades de información.

Entre estas operaciones pueden encontrarse:

* Procesamiento de grandes volúmenes de datos.
* Generación de archivos PDF.
* Construcción de documentos.
* Aplicación de filtros y agrupaciones.
* Cálculos sobre información ganadera.
* Procesamiento de imágenes o información adicional.
* Almacenamiento temporal de documentos generados.

Por estas razones, separar esta responsabilidad en un microservicio permite que el procesamiento de reportes y certificados pueda escalar independientemente del resto de la aplicación.

### 3.1 Uso independiente de recursos

La generación de documentos puede consumir más CPU y memoria que operaciones CRUD convencionales.

Al disponer de un servicio independiente, los recursos destinados a estas operaciones pueden aumentar sin necesidad de modificar la infraestructura de los demás componentes del sistema.

Por ejemplo, si existe un aumento considerable en las solicitudes de generación de reportes, se pueden ejecutar varias instancias del microservicio de reportes sin aumentar necesariamente las instancias de los servicios encargados de administrar las operaciones principales del sistema.

### 3.2 Escalabilidad independiente

El microservicio puede escalar horizontalmente según la cantidad de solicitudes recibidas.

Por ejemplo:

```text
                  ┌──────────────────────┐
                  │    Sistema principal │
                  └──────────┬───────────┘
                             │
                             ▼
                  ┌──────────────────────┐
                  │ Microservicio de     │
                  │ Reportes y           │
                  │ Certificados         │
                  └──────────┬───────────┘
                             │
                    ┌────────┴────────┐
                    ▼                 ▼
              Instancia 1       Instancia 2
```

Esto permite distribuir la carga de trabajo entre varias instancias cuando sea necesario.

### 3.3 Aislamiento de fallos

La separación también permite reducir el impacto de una falla en la generación de documentos.

Si el microservicio presenta una interrupción, las operaciones principales del sistema pueden continuar funcionando siempre que estas no dependan directamente de la generación inmediata del documento.

Las solicitudes de generación pueden mantenerse en una cola para ser procesadas posteriormente.

---

# 4. Arquitectura propuesta

La arquitectura propuesta utiliza una comunicación HTTP/REST entre el sistema principal y el microservicio.

```text
┌──────────────────────────┐
│      Cliente / Frontend  │
└────────────┬─────────────┘
             │
             ▼
┌──────────────────────────┐
│    API / Sistema         │
│       principal          │
└────────────┬─────────────┘
             │ HTTP/JSON
             ▼
┌──────────────────────────┐
│ Microservicio de         │
│ Reportes y Certificados  │
└────────────┬─────────────┘
             │
      ┌──────┴───────┐
      ▼              ▼
┌───────────┐   ┌─────────────┐
│ Base de   │   │ Almacenamiento│
│ datos     │   │ de documentos │
└───────────┘   └─────────────┘
```

La comunicación entre servicios utilizará solicitudes HTTP y mensajes en formato JSON.

---

# 5. Contrato de la API

## 5.1 URL base

La URL base dependerá del ambiente donde se ejecute el microservicio.

Como referencia se utilizará:

```text
{{baseUrl}}
```

Ejemplo:

```text
http://localhost:8080/api
```

La URL definitiva será configurada posteriormente en la colección de pruebas HTTP.

---

# 6. Endpoint para generar un reporte

### POST `/reportes`

Permite solicitar la generación de un nuevo reporte.

### Request

```json
{
  "tipo": "produccion",
  "fechaInicio": "2026-09-01",
  "fechaFin": "2026-09-19",
  "formato": "PDF"
}
```

### Respuesta exitosa

**HTTP 201 Created**

```json
{
  "id": 125,
  "tipo": "produccion",
  "estado": "PENDIENTE",
  "formato": "PDF",
  "fechaSolicitud": "2026-09-19T18:30:00"
}
```

Cuando se cree correctamente el recurso, la respuesta deberá incluir el encabezado:

```text
Location: /reportes/125
```

El cliente podrá utilizar posteriormente esa ubicación para consultar el reporte.

---

# 7. Consultar un reporte

### GET `/reportes/{id}`

Permite consultar el estado y la información de un reporte previamente solicitado.

### Ejemplo

```text
GET /reportes/125
```

### Respuesta

**HTTP 200 OK**

```json
{
  "id": 125,
  "tipo": "produccion",
  "estado": "GENERADO",
  "formato": "PDF",
  "fechaSolicitud": "2026-09-19T18:30:00",
  "fechaFinalizacion": "2026-09-19T18:31:12"
}
```

Los estados posibles pueden ser:

```text
PENDIENTE
PROCESANDO
GENERADO
ERROR
```

---

# 8. Descargar un reporte

### GET `/reportes/{id}/archivo`

Permite obtener el archivo generado.

### Ejemplo

```text
GET /reportes/125/archivo
```

Cuando el archivo esté disponible, el servicio responderá con:

**HTTP 200 OK**

El contenido de la respuesta corresponderá al archivo generado.

El tipo de contenido podrá ser:

```text
Content-Type: application/pdf
```

Si el reporte todavía no ha sido generado, el servicio deberá informar que el recurso aún no está disponible.

---

# 9. Generar un certificado ganadero

### POST `/certificados`

Permite solicitar la generación de un certificado ganadero.

### Request

```json
{
  "animalId": 45,
  "tipo": "SANITARIO",
  "productorId": 12,
  "formato": "PDF"
}
```

### Respuesta

**HTTP 201 Created**

```json
{
  "id": 78,
  "animalId": 45,
  "tipo": "SANITARIO",
  "estado": "PENDIENTE",
  "formato": "PDF"
}
```

El servicio deberá devolver el encabezado `Location` apuntando al nuevo certificado:

```text
Location: /certificados/78
```

---

# 10. Consultar un certificado

### GET `/certificados/{id}`

Permite consultar el estado de un certificado.

### Ejemplo

```text
GET /certificados/78
```

### Respuesta

**HTTP 200 OK**

```json
{
  "id": 78,
  "animalId": 45,
  "tipo": "SANITARIO",
  "estado": "GENERADO",
  "formato": "PDF"
}
```

---

# 11. Descargar un certificado

### GET `/certificados/{id}/archivo`

Permite obtener el archivo correspondiente al certificado.

### Respuesta exitosa

**HTTP 200 OK**

```text
Content-Type: application/pdf
```

Si el certificado no existe:

**HTTP 404 Not Found**

---

# 12. Formato general de errores

Los errores de la API utilizarán respuestas JSON para facilitar su interpretación por parte de los clientes.

Formato propuesto:

```json
{
  "error": "VALIDATION_ERROR",
  "message": "Los datos proporcionados no son válidos",
  "status": 422,
  "timestamp": "2026-09-19T18:35:00"
}
```

Los campos principales serán:

| Campo       | Descripción                    |
| ----------- | ------------------------------ |
| `error`     | Código identificador del error |
| `message`   | Descripción del problema       |
| `status`    | Código HTTP                    |
| `timestamp` | Fecha y hora del error         |

---

# 13. Códigos HTTP utilizados

| Código                        | Situación                                                                           |
| ----------------------------- | ----------------------------------------------------------------------------------- |
| **200 OK**                    | Operación realizada correctamente y se devuelve información                         |
| **201 Created**               | Se creó correctamente un reporte o certificado                                      |
| **204 No Content**            | Operación exitosa sin contenido de respuesta                                        |
| **400 Bad Request**           | La solicitud tiene una estructura o parámetro incorrecto                            |
| **401 Unauthorized**          | No se proporcionaron credenciales válidas                                           |
| **404 Not Found**             | El reporte o certificado solicitado no existe                                       |
| **409 Conflict**              | La operación entra en conflicto con una regla de negocio                            |
| **422 Unprocessable Entity**  | Los datos tienen una estructura válida, pero no cumplen las validaciones requeridas |
| **500 Internal Server Error** | Error inesperado dentro del servicio                                                |
| **503 Service Unavailable**   | El servicio no está disponible temporalmente                                        |

---

# 14. Autenticación

Las operaciones del microservicio estarán protegidas mediante autenticación basada en token.

El cliente enviará el token mediante el encabezado:

```text
Authorization: Bearer {{token}}
```

Ejemplo:

```http
Authorization: Bearer eyJhbGciOi...
```

La colección de pruebas utilizará la variable:

```text
{{token}}
```

para evitar almacenar directamente el token dentro de cada solicitud.

Si el token no está presente o no es válido, el servicio responderá:

**HTTP 401 Unauthorized**

Ejemplo:

```json
{
  "error": "UNAUTHORIZED",
  "message": "Token no válido o ausente",
  "status": 401
}
```

---

# 15. Rutas anidadas

El diseño contempla rutas anidadas para representar recursos relacionados.

Por ejemplo:

```text
GET /reportes/{id}/archivo
```

y:

```text
GET /certificados/{id}/archivo
```

Estas rutas permiten acceder a recursos que dependen directamente del reporte o certificado correspondiente.

También podrían utilizarse rutas relacionadas con las entidades del sistema principal cuando sea necesario, por ejemplo:

```text
GET /animales/{animalId}/certificados
```

Esta ruta permitiría consultar los certificados asociados a un animal específico.

---

# 16. Estrategia de resiliencia

El microservicio debe contemplar situaciones en las que no pueda procesar una solicitud inmediatamente.

La estrategia propuesta consiste en utilizar procesamiento asíncrono para las operaciones de generación de documentos.

El flujo sería:

```text
Cliente
   │
   │ POST /reportes
   ▼
Microservicio
   │
   │ Crear solicitud
   ▼
Cola de procesamiento
   │
   ▼
Generador de reportes
   │
   ▼
Documento generado
```

De esta manera, el cliente no necesita mantener una conexión abierta durante todo el proceso de generación.

---

# 17. Manejo de reintentos

Cuando ocurra una falla temporal durante el procesamiento, la solicitud podrá ser reintentada automáticamente.

Se propone utilizar un esquema de reintentos con espera progresiva (*exponential backoff*).

Ejemplo:

```text
Primer intento
      ↓
   falla
      ↓
Espera 1 segundo
      ↓
Segundo intento
      ↓
   falla
      ↓
Espera 2 segundos
      ↓
Tercer intento
```

El número máximo de reintentos deberá estar limitado para evitar ciclos infinitos.

Si después del número máximo de intentos el procesamiento continúa fallando, la solicitud será marcada como:

```text
ERROR
```

y se registrará el motivo de la falla.

---

# 18. ¿Qué ocurre si el microservicio está caído?

Si el microservicio de reportes y certificados no está disponible temporalmente, el sistema principal no debería bloquear todas sus operaciones.

Cuando sea posible, la solicitud de generación será almacenada en una cola para ser procesada posteriormente.

El flujo sería:

```text
Sistema principal
       │
       ▼
Microservicio no disponible
       │
       ▼
Solicitud almacenada en cola
       │
       ▼
Microservicio vuelve a estar disponible
       │
       ▼
Procesamiento de la solicitud
```

Mientras la solicitud se encuentre pendiente, el sistema puede indicar al usuario que el documento está siendo procesado.

---

# 19. Fallback

Como mecanismo de fallback, cuando la generación inmediata no esté disponible, el sistema podrá:

1. Registrar la solicitud.
2. Mantenerla en estado `PENDIENTE`.
3. Enviarla a una cola de procesamiento.
4. Reintentar posteriormente.
5. Actualizar el estado cuando el microservicio vuelva a estar disponible.

Esto evita perder solicitudes cuando ocurre una interrupción temporal.

El fallback no deberá generar información falsa ni entregar un documento incompleto. Si el documento no pudo ser generado, deberá mantenerse como pendiente o marcarse como error.

---

# 20. Idempotencia

Las operaciones de generación deberán considerar la posibilidad de que una misma solicitud sea enviada más de una vez debido a reintentos o problemas de comunicación.

Para evitar la generación duplicada de documentos, se podrá utilizar una clave de idempotencia:

```text
Idempotency-Key: <identificador-unico>
```

Si el mismo identificador se recibe nuevamente, el servicio deberá reconocer la solicitud anterior y evitar crear un documento duplicado.

---

# 21. Validaciones de negocio

El servicio deberá validar la información recibida antes de iniciar el procesamiento.

Algunas validaciones posibles son:

* El identificador del animal debe existir.
* El productor debe existir.
* El tipo de reporte debe ser válido.
* El formato solicitado debe estar soportado.
* Las fechas deben tener un formato válido.
* La fecha inicial no debe ser posterior a la fecha final.
* No debe existir un certificado activo duplicado cuando la regla de negocio lo prohíba.

Cuando los datos sean sintácticamente correctos pero incumplan una validación, se utilizará:

```text
422 Unprocessable Entity
```

Cuando la solicitud entre en conflicto con un recurso o una operación existente, se utilizará:

```text
409 Conflict
```

---

# 22. Ejemplo de error 422

```json
{
  "error": "VALIDATION_ERROR",
  "message": "La fecha de inicio no puede ser posterior a la fecha final",
  "status": 422,
  "timestamp": "2026-09-19T18:40:00"
}
```

---

# 23. Ejemplo de error 409

```json
{
  "error": "CONFLICT",
  "message": "Ya existe un certificado sanitario activo para este animal",
  "status": 409,
  "timestamp": "2026-09-19T18:41:00"
}
```

---

# 24. Seguridad

La comunicación entre el sistema principal y el microservicio deberá utilizar autenticación mediante token.

En ambientes productivos se recomienda utilizar HTTPS para proteger las credenciales y la información transmitida.

Además:

* No se deben almacenar tokens directamente en el código fuente.
* Las credenciales deben manejarse mediante variables de entorno o mecanismos seguros de configuración.
* Las respuestas de error no deben exponer información sensible.
* El acceso a documentos debe estar protegido mediante autenticación y autorización.

---

# 25. Observabilidad

El microservicio deberá registrar información suficiente para identificar problemas durante el procesamiento.

Se recomienda registrar:

* Identificador de la solicitud.
* Tipo de operación.
* Fecha y hora.
* Estado de procesamiento.
* Tiempo de procesamiento.
* Errores ocurridos.
* Número de reintentos.

Un identificador de correlación permitirá relacionar una solicitud realizada desde el sistema principal con las operaciones realizadas dentro del microservicio.

Ejemplo:

```text
X-Correlation-ID: 8f42a7d1-25c4-4d8c
```

---

# 26. Flujo completo de generación

El proceso completo puede resumirse de la siguiente manera:

```text
1. Cliente solicita un reporte
             │
             ▼
2. Sistema valida la solicitud
             │
             ▼
3. Microservicio recibe la solicitud
             │
             ▼
4. Se crea el recurso
             │
             ▼
5. Respuesta HTTP 201
             │
             ▼
6. Solicitud pasa a procesamiento
             │
             ▼
7. Se genera el documento
             │
             ▼
8. Estado = GENERADO
             │
             ▼
9. Cliente consulta el recurso
             │
             ▼
10. Cliente descarga el documento
```

---

# 27. Resumen del contrato

| Método | Endpoint                            | Propósito                           | Respuesta principal |
| ------ | ----------------------------------- | ----------------------------------- | ------------------- |
| POST   | `/reportes`                         | Solicitar reporte                   | 201 Created         |
| GET    | `/reportes/{id}`                    | Consultar reporte                   | 200 OK              |
| GET    | `/reportes/{id}/archivo`            | Descargar reporte                   | 200 OK              |
| POST   | `/certificados`                     | Solicitar certificado               | 201 Created         |
| GET    | `/certificados/{id}`                | Consultar certificado               | 200 OK              |
| GET    | `/certificados/{id}/archivo`        | Descargar certificado               | 200 OK              |
| GET    | `/animales/{animalId}/certificados` | Consultar certificados de un animal | 200 OK              |

---

# 28. Relación con las pruebas HTTP

La colección de pruebas HTTP correspondiente a este laboratorio deberá utilizar variables de entorno para evitar valores escritos directamente en cada solicitud.

Variables principales:

```text
{{baseUrl}}
{{token}}
```

Las pruebas deberán cubrir operaciones exitosas y situaciones de error.

Como mínimo se deberán contemplar:

```text
200 OK
201 Created
204 No Content
400 Bad Request
401 Unauthorized
404 Not Found
409 Conflict
422 Unprocessable Entity
```

Las respuestas `201 Created` deberán verificar también la existencia del encabezado `Location`.

Las pruebas de autenticación utilizarán:

```http
Authorization: Bearer {{token}}
```

La colección será almacenada en:

```text
docs/coleccion-lab05.json
```

---

# 29. Consideraciones para la implementación

El presente documento define el diseño conceptual y el contrato propuesto del microservicio.

Los nombres definitivos de los endpoints, campos y parámetros deberán mantenerse sincronizados con la implementación final del proyecto y con la colección de pruebas HTTP.

Si durante la integración del microservicio se modifican rutas o estructuras de los mensajes, tanto este documento como `docs/coleccion-lab05.json` deberán actualizarse para mantener un contrato consistente entre la documentación, la implementación y las pruebas.