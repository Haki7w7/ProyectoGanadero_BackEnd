## Cambios y modificaciones del lab 4

## Evidencias de Validaciones (Jhoel Edu)
- **Validaciones Declarativas:** Se enviaron datos incompletos en `POST /api/v1/animales` y el sistema rechazó la petición con código el error 422 Unprocessable Entity, devolviendo o retornando los mensajes de error asociados individualmente en español.

## Evidencias de Consultas Avanzadas (Jose Abraham)
- **Paginación y Tope Máximo:** Se probó `GET /api/v1/animales?per_page=500`. La API aplicó el límite seguro de memoria restringiendo o limitando el resultado a `per_page: 500`.
- **Filtros Combinables:** Se corrió `GET /api/v1/animales?estado=Activo&sexo=Macho` y se usó el método de `when()` de Eloquent para retornar solo los registros que coincidian.
- **Ordenamiento Dinámico:** Se probó `GET /api/v1/animales?sort_by=numero_arete&order=desc` ordenando la respuesta de forma dinamica.