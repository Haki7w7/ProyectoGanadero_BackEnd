
# Documentación del Modelo de Datos - Finca Ganadera
**Curso:** Desarrollo de Software IV (IF0009)[cite: 1]  
**Profesor:** Alonso Chavarría  
**Proyecto:** Sistema de Gestión para Finca Ganadera  

## 1. Introducción
El presente documento detalla el diseño, las decisiones y la estructura de modelado de la base de datos desarrollada para el sistema el cual trata sobre una gestión de finca ganadera. Este dominio fue propuesto en una reunión con los integrantes del equipo con el objetivo de poder darle solución a las necesidades operativas y de gestión de esta organización agropecuaria, abarcando así el control de pesajes, sanidad de los animales, administración de los potreros, así como también de los insumos o tratamientos de los animales, etc.

## 2. Descripción de las Entidades Principales
* **Animal:** Es la entidad central que almacena la información de cada cabeza o animal, ya sea el estado del animal (activo, fallecido o vendido), la raza, el arete, en qué potrero se encuentra, su sexo, fecha de nacimiento o edad, etc.
* **Potrero:** Define las áreas de terreno o potreros en donde se encuentran o distribuyen los animales, así como también contiene información del estado del pasto, las hectáreas con las que cuenta cada potrero, la capacidad máxima de este, etc.
* **Pesaje:** Tiene la función de llevar un registro o historial del crecimiento y rendimiento de los animales o ganado a lo largo del tiempo, así como también cuenta con un apartado o atributo de observaciones para poder evaluar cada animal, por si se identifica alguna anomalía o cambio en este.
* **Tratamiento:** Catálogo de vacunas, desparasitación y procedimientos sanitarios.
* **Insumo y Catálogos (`Categoria`, `UnidadMedida`, `Raza`):** Tablas catálogo para poder gestionar el inventario de bodega, a su vez que estandarizan los datos del sistema para evitar duplicidad de información. A su vez, la tabla "razas" funciona como estandarización para evitar inconsistencias en el sistema, y poder llevar mejor control de la información de los animales.
* **Tabla Pivote (`TratamientoAnimal`):** Resuelve la relación de muchos a muchos entre los animales y los tratamientos aplicados, agregando atributos propios de dicha interacción como la fecha de aplicación, la dosis y las observaciones.
* **Tabla de Seguridad (`user`):** Gestiona la información de acceso de las personas usuarias del sistema, permitiendo configurar los roles y permisos diferenciados requeridos para la autenticación y autorización.

## 3. Justificación de las Relaciones y Cardinalidades
Para poder representar correctamente la lógica operativa de la finca ganadera, y poder cumplir con los requisitos del proyecto, se establecieron las siguientes relaciones de cardinalidad mediante el uso de Eloquent:

* **Relaciones Uno a Muchos (1:N):**
  * **Potrero $\rightarrow$ Animal:** Un potrero puede contener a múltiples animales de forma simultánea, pero cada animal se encuentra asignado a un único potrero actual. Esto permite llevar el control exacto de la ubicación del hato (grupo de animales en una finca o potrero) en el terreno.
  * **Raza $\rightarrow$ `Animal`:** Una raza clasifica a muchos animales, sin embargo cada animal pertenece a una única raza, garantizando la consistencia y estandarización del registro genético de cada animal.
  * **Animal $\rightarrow$ `Pesaje`:** Un animal cuenta con un historial de múltiples pesajes periódicos, pero, cada pesaje pertenece a un solo animal.
  * **Categoria $\rightarrow$ `Insumo`:** Una categoría agrupa a diversos insumos de la bodega, pero un insumo pertenece exclusivamente a una sola categoría.
  * **UnidadMedida $\rightarrow$ `Insumo`:** Una unidad de medida es utilizada por varios insumos, pero cada insumo tiene asignada una única unidad métrica estándar.

* **Relación Muchos a Muchos (N:M):**
  * **Animal $\leftrightarrow$ Tratamiento (por medio de la tabla `TratamientoAnimal`):** Se implementó una relación de muchos a muchos como se mencionaba en el enunciado, y esta relación se realiza con la tabla de animal y tabla de tratamiento, ya que un animal puede recibir diversos tratamientos a lo largo de su vida, así como un tratamiento médico puede ser aplicado a varios animales. Esta relación se resuelve de manera limpia a través de la tabla intermedia o pivote `TratamientoAnimal`, la cual incorpora o agrega datos indispensables para la trazabilidad operativa, como por ejemplo la fecha de la aplicación o la dosis aplicada en el animal, y observaciones, para así poder analizar o tener notas específicas sobre los animales y su reacción o el estado en el que se encuentran después del tratamiento.

## 4. Consideraciones de Escalabilidad y Arquitectura
* **Estructura Modular:** El diseño de la base de datos mantiene separados los catálogos estáticos de las tablas transaccionales, esto lo que permite es que si en un futuro se desea agregar nuevas métricas, tipos de insumos o tratamientos, se pueda conseguir de manera efectiva sin alterar la estructura principal.
* **Trazabilidad Operativa:** La incorporación de tablas de soporte como lo son `Pesaje` y la tabla intermedia `TratamientoAnimal` garantiza un registro histórico firme, facilitando la extracción de métricas, reportes y consultas requeridas para la gestión integral de la finca.