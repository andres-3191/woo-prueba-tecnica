# WooCommerce Prueba Técnica

## Widget de Carrito Mejorado para WooCommerce

![WooCommerce Technical Test](https://img.shields.io/badge/WooCommerce-Plugin-purple)
![Version](https://img.shields.io/badge/Version-1.0.0-blue)

## 🔍 Descripción General

Este plugin extiende WooCommerce añadiendo un widget de carrito flotante que permite a los clientes ver, modificar y gestionar su carrito sin abandonar la página actual. También se conecta a una API Express externa para mostrar recomendaciones de productos destacados dentro del widget del carrito.

## 📹 Video Explicativo
Acontinuacion comparto video explicativo del reto:
- Video: [https://www.loom.com/share/b28f7cbb6a8c4b0f8952a35589d4e1a7?sid=03b1b4ea-2e9d-444a-b710-e1047abfc9b5](https://www.loom.com/share/b28f7cbb6a8c4b0f8952a35589d4e1a7?sid=03b1b4ea-2e9d-444a-b710-e1047abfc9b5)

## ✨ Características

- **Widget de Carrito Modal**: Un botón de carrito flotante que abre un modal con el contenido del carrito
- **Actualizaciones en Tiempo Real**: Actualizaciones del carrito mediante AJAX sin recargar la página
- **Controles de Cantidad**: Incremento, decremento y entrada directa para las cantidades de productos
- **Eliminación con Un Clic**: Eliminar artículos directamente desde el widget del carrito
- **Integración con API Externa**: Se conecta a una API Express para obtener y mostrar recomendaciones de productos destacados
- **Diseño Responsivo**: Funciona perfectamente en dispositivos de escritorio y móviles
- **Integración con WooCommerce**: Totalmente compatible con los hooks y funcionalidades de WooCommerce
- **Configuración de Administrador**: Ajustes de API personalizables en el área de administración de WooCommerce

## 🔧 Implementación

### Implementación Frontend

- **Interfaz de Usuario del Widget de Carrito**: Construido con HTML, CSS y JavaScript para crear una interfaz limpia y responsiva
- **Funcionalidad AJAX**: Utiliza la API AJAX de WordPress para actualizaciones del carrito en tiempo real
- **Fragmentos de WooCommerce**: Utiliza fragmentos de carrito de WooCommerce para una gestión de estado consistente
- **Manejo de Eventos**: Manejo de eventos JavaScript para cambios de cantidad, eliminación de artículos y alternancia del carrito

### Implementación Backend

- **Clases PHP**: Enfoque orientado a objetos con clases separadas para diferentes áreas de funcionalidad
- **Hooks de WooCommerce**: Integración con hooks de WooCommerce para la gestión del carrito
- **Cliente REST API**: Implementación de un cliente REST para comunicarse con la API Express
- **Ajustes de Administración**: API de Ajustes de WordPress para la configuración de administración

### Integración de API

- **Conexión con Servicio Externo**: Conexión segura a un servicio de API Express
- **Recuperación de Productos**: Obtención de productos destacados desde la API para mostrar en el widget

## 🚀 Uso

Una vez activado, el plugin añade automáticamente:

1. Un botón de carrito flotante en todas las páginas (excepto carrito y pago)
2. Un widget de carrito modal cuando se hace clic en el botón
3. Recomendaciones de productos destacados desde la API configurada

No se requiere configuración adicional para la funcionalidad básica.

## ⚙️ Configuración

### Ajustes de la API

1. Ve a WooCommerce → Technical Test Cart
2. Configura los siguientes ajustes:
    - **URL de la API**: La URL de tu servicio de API Express (predeterminado: http://localhost:3000/api)
    - **Clave de API**: Clave de autenticación para la API
    - **Secreto de API**: Clave secreta para autenticación de la API

## 📂 Estructura del Proyecto

```
woo-prueba-tecnica/
├── admin/
│   └── class-woo-prueba-tecnica-admin.php    # Funcionalidad de administración
├── includes/
│   ├── class-woo-prueba-tecnica-api.php      # Manejo de API
│   └── class-woo-prueba-tecnica-cart.php     # Funcionalidad del carrito
├── public/
│   ├── css/
│   │   └── woo-prueba-tecnica-public.css     # Estilos frontend
│   ├── js/
│   │   └── woo-prueba-tecnica-public.js      # Scripts frontend
│   ├── widgets/
│   │   └── cart-widget.php                   # Plantilla del widget de carrito
│   └── class-woo-prueba-tecnica-public.php   # Funcionalidad orientada al público
├── uninstall.php                             # Limpieza en desinstalación
└── woo-prueba-tecnica.php                    # Archivo bootstrap del plugin
```

## 👨‍💻 Autor

Andres Felipe Parra Ferreira
- GitHub: [andres-3191](https://github.com/andres-3191)