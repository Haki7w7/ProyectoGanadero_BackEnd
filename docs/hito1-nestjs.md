# Hito 1: Implementación de Porción Vertical en NestJS - Proyecto Ganadero

Este documento detalla la configuración, métricas cuantitativas y guía de ejecución del módulo de gestión de animales implementado en **NestJS** como contraparte y comparativa del backend en Laravel.

## 1. Métricas Cuantitativas de la Porción Vertical
* **Cantidad de archivos del módulo (`src/animales/`):** 6 archivos (entidad, controlador, servicio, DTOs y módulos).
* **Tamaño total del código fuente:** ~5,558 bytes (~5.6 KB).
* **Pruebas Automatizadas:** 6 casos de prueba E2E (End-to-End) implementados con Jest y Supertest cubriendo códigos `201`, `422`, `200` y `204`.

## 2. Requisitos Previos
* Node.js (versión 18 o superior recomendada).
* Gestor de paquetes npm.

## 3. Guía de Instalación y Ejecución Paso a Paso

1. Clonar el repositorio y ubicarse en la carpeta del backend de NestJS:
   cd backend-nestjs

2. Instalar las dependencias ncesarias:
Se debe correr este comando dentro de la carpeta de backend-nestjs
npm install

3. Ejecutar la aplicación en modo desarrollo:
Enciende el servidor local con recarga en vivo, se debe correr el siguiente comando
npm run start:dev

4. Ejecutar las pruebas automatizadas (E2E)
Con esto se verifica que todas las validaciones, codigos http y rutas funcionen de forma correcta ejecutando o corriendo Jest:
npm run test:e2e

Documentación de Endpoints (OpenAPI/SWAGGER)

Teniendo la aplicacion corriendo localmente a traves del comando npm run start:dev, la documentacion interactiva y visual de la API generadda por swagger está disponible en la ruta siguiente:

URL: http://localhost:3000/api/docs

