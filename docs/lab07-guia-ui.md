# Guía de Interfaz del Proyecto (UI Guide)
### Paleta de Colores y Verificación de Contraste (WCAG AA)
- **Verde Hoja (Primario):** `#606C38` (Botones principales, acentos de navegación, badges activos)
- **Verde Foresta (Secundario / Contraste):** `#283618` (Encabezados, barra lateral, texto de alto contraste)
- **Crema Claro (Fondo General):** `#FEFAE0` (Fondo de la aplicación, tarjetas secundarias)
- **Marrón Arcilla (Acento / Estado Suave):** `#DDA15E` (Botones secundarios, alertas de advertencia, destacados)
- **Marrón Tierra (Énfasis / Bordes):** `#BC6C25` (Bordes de componentes, iconos, estado activo secundario)

#### Verificación de Contraste
- **Texto Verde Foresta (`#283618`) sobre Fondo Crema (`#FEFAE0`):**
- **Texto Blanco (`#FFFFFF`) sobre Botón Verde Hoja (`#606C38`):** 
- **Texto Blanco (`#FFFFFF`) sobre Botón Marrón Tierra (`#BC6C25`):** 

### Tipografia y Escala Tipográfica
- **Fuente elegida:** Inter / sans-serif
- **Escala basada en:** Major Third (1.25) con tamaño base de 16px.

- **H1 (Título Principal):** 39px / 2.44rem (Font Weight: 700 - Bold)
- **H2 (Secciones):** 31px / 1.95rem (Font Weight: 600 - SemiBold)
- **H3 (Subsecciones):** 25px / 1.56rem (Font Weight: 600 - SemiBold)
- **Body (Texto base):** 16px / 1rem (Font Weight: 400 - Regular)
- **Caption / Small (Etiquetas, alertas):** 13px / 0.8rem (Font Weight: 400 - Regular)

### Sistema de Espaciado
- **Base de espaciado:** Múltiples de 4px / 8px (Sistema estándar de UI).

- **xs (Extra pequeño):** 4px / 0.25rem (Espaciado interno de badges, separaciones mínimas)
- **sm (Pequeño):** 8px / 0.5rem (Padding interno de botones compactos, gap entre iconos y texto)
- **md (Mediano / Base):** 16px / 1rem (Padding estándar de inputs, botones y márgenes de párrafos)
- **lg (Grande):** 24px / 1.5rem (Padding interno de tarjetas, contenedores y modales)
- **xl (Extra grande):** 32px / 2rem (Separación entre secciones principales e hilos de formularios)
- **2xl (Sección mayor):** 48px / 3rem (Margen superior de layout y contenedores principales)

### Estados de los Componentes
- **Normal (Default):**
  - **Fondo:** `#606C38` (Botón primario) / `#FEFAE0` (Campos de entrada/Inputs)
  - **Texto:** `#FFFFFF` (En botones) / `#283618` (En campos de texto)
  - **Borde:** 1px sólido `#BC6C25` (En campos de texto)
  - **Cursor:** `pointer` en botones / `text` en campos de entrada

- **Foco (Focus):**
  - **Anillo de Foco (Outline):** 3px de grosor con color `#DDA15E` y opacidad del 50%
  - **Borde:** `#606C38` (Resaltado claro indicando selección activa)
  - **Comportamiento:** Se activa al navegar con la tecla `Tab` o hacer clic directo sobre el componente

- **Error (Validation Error):**
  - **Fondo:** `#FEF2F2` (Rojo muy claro para alerta visual)
  - **Borde:** 1.5px sólido `#DC2626` (Rojo de validación)
  - **Texto y Mensaje de Ayuda:** `#DC2626` en tamaño `Caption` (13px) debajo del componente
  - **Icono:** Signo de advertencia en color `#DC2626` dentro del campo

- **Deshabilitado (Disabled):**
  - **Fondo:** `#FEF2F2`
  - **Texto:** `#DC2626`
  - **Borde:** 1px sólido `#D1D5DB`
  - **Opacidad:** `0.6`
  - **Cursor:** `not-allowed` (Bloquea cualquier evento de interacción)