  **LABORATORIO 6**

# **Autenticación con tokens, autorización por políticas y pruebas automatizadas**

| Fecha | Jueves 22 de setiembre, 2026 (Semana 07\) |
| :---- | :---- |
| **Contenido del programa** | Tema 3\. Autenticación, seguridad de sesiones y pruebas de unidad |
| **Resultado de aprendizaje** | Desarrollar pruebas unitarias para identificar defectos en las aplicaciones desarrolladas. |
| **Duración estimada** | 3 horas (sesión de teoría y práctica) \+ 4 horas extraclase |
| **Modalidad** | Práctica guiada en clase, trabajo en equipos de 5 personas |
| **Entrega** | Mediación Virtual, hasta el domingo siguiente a las 23:55 |

  **1\. Objetivos**

* Implementar autenticación por token y manejo seguro de la sesión.

* Implementar autorización basada en políticas verificada en más de una capa.

* Desarrollar pruebas automatizadas que identifiquen defectos en la capa de negocio y en la API.

  **2\. Requisitos previos**

* Laboratorio 5 concluido: API REST documentada y funcional.

  **3\. Enunciado**

1. Implemente el registro, el inicio y el cierre de sesión mediante tokens de API. El token debe tener un tiempo de expiración definido, y debe revocarse al cerrar sesión.

2. Almacene las contraseñas con el algoritmo de derivación que provee el framework y aplique una política mínima de complejidad y de longitud.

3. Defina al menos tres roles del sistema y proteja los endpoints según el rol. Implemente además políticas por recurso, de modo que una persona usuaria solo pueda acceder a los registros que le corresponden.

4. Verifique la autorización en dos capas: en la ruta o el controlador y dentro del servicio. Demuestre que una invocación directa al servicio, sin pasar por la ruta protegida, también es rechazada.

5. Implemente la limitación de intentos de inicio de sesión y una respuesta de error que no permita distinguir entre una persona usuaria inexistente y una contraseña incorrecta.

6. Implemente doce pruebas como mínimo: pruebas unitarias de la capa de servicio con dobles de prueba para las dependencias, cubriendo el camino feliz, la violación de cada regla de negocio y al menos dos casos límite.

7. Implemente seis pruebas de la API que verifiquen los códigos de estado, la estructura de la respuesta y el acceso permitido y denegado según rol.

8. Configure la base de datos de pruebas de forma aislada, de modo que la suite no altere los datos de desarrollo.

  **4\. Entregables**

* Flujo de autenticación funcional con evidencia de acceso permitido, denegado y de revocación del token.

* Políticas de autorización implementadas y verificadas en dos capas.

* Suite de pruebas ejecutándose sin fallos, con al menos dieciocho pruebas.

  **6\. Rúbrica de evaluación**

| CRITERIO | EXCELENTE (100 %) | ACEPTABLE (70 %) | INSUFICIENTE (30 %) | PTS. |
| ----- | ----- | ----- | ----- | :---: |
| **Autenticación y manejo de sesión** | Token con expiración y capacidades, revocación al cerrar sesión y contraseñas correctamente derivadas. | Autenticación funcional con debilidades (sin expiración o sin revocación). | Sin autenticación o contraseñas almacenadas de forma insegura. | **20** |
| **Autorización por roles y políticas** | Roles y políticas por recurso verificados en dos capas, con evidencia de acceso denegado. | Verificación únicamente en la ruta o sin políticas por recurso. | Sin control de acceso efectivo. | **20** |
| **Protección del inicio de sesión** | Limitación de intentos y respuesta que no permite enumerar cuentas. | Uno de los dos controles implementado. | Ninguno implementado. | **10** |
| **Pruebas unitarias** | Doce o más pruebas con dobles, cubriendo camino feliz, reglas y casos límite. | Entre seis y once pruebas o cobertura desigual de los casos. | Menos de seis pruebas o pruebas que no verifican comportamiento. | **25** |
| **Pruebas de API, aislamiento y cobertura** | Seis pruebas de API, base de datos de pruebas aislada y cobertura igual o superior al 70 %. | Pruebas presentes con cobertura entre 50 % y 69 % o sin aislamiento. | Sin pruebas de API o cobertura inferior al 50 %. | **25** |
| **PUNTAJE TOTAL** |  |  |  | **100** |

*Escala: Excelente \= 100 % de los puntos del criterio · Aceptable \= 70 % · Insuficiente \= 30 % · No presentado \= 0\.*  
