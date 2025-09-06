# Weather Chatbot

Un chatbot meteorológico inteligente desarrollado en Laravel con Vue.js que proporciona información del clima en tiempo real utilizando APIs externas y procesamiento de lenguaje natural.

## Características principales

- Interfaz conversacional moderna con Vue.js 3
- Integración con modelos de lenguaje (OpenAI GPT)
- Datos meteorológicos en tiempo real de Open-Meteo API
- Historial de conversaciones persistente
- Respuestas contextualizadas y personalizadas
- Sistema de notificaciones
- Diseño responsivo con Tailwind CSS
- Arquitectura modular y escalable

## Stack tecnológico

### Backend
- Laravel 10.x
- PHP 8.1+
- MySQL
- Eloquent ORM

### Frontend
- Vue.js 3 (Composition API)
- Tailwind CSS
- Vite
- Axios para comunicación con API

### APIs externas
- OpenAI GPT API para procesamiento de lenguaje natural
- Open-Meteo API para datos meteorológicos
- OpenWeatherMap API (opcional)

## Requisitos del sistema

- PHP >= 8.1
- Composer
- Node.js >= 16.x
- npm
- MySQL >= 5.7
- Extensiones PHP requeridas: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath

## Instalación y configuración

### 1. Clonar el repositorio

```bash
git clone [URL_DEL_REPOSITORIO]
cd weather-chatbot
```

### 2. Instalar dependencias de PHP

```bash
composer install
```

### 3. Configurar variables de entorno

```bash
cp .env.example .env
php artisan key:generate
```

Editar el archivo `.env` con la configuración necesaria:

```env
# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=weather_chatbot
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# API Keys
OPENAI_API_KEY=tu_clave_openai
OPENWEATHER_API_KEY=tu_clave_openweather_opcional
```

### 4. Crear y configurar la base de datos

```bash
# Crear la base de datos
mysql -u root -p
CREATE DATABASE weather_chatbot;
EXIT;

# Ejecutar migraciones
php artisan migrate
```

### 5. Instalar dependencias de Node.js

```bash
npm install
```

### 6. Compilar assets del frontend

Para producción:
```bash
npm run build
```

Para desarrollo con recarga automática:
```bash
npm run dev
```

### 7. Configurar permisos

```bash
php artisan storage:link
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 8. Iniciar el servidor

```bash
php artisan serve
```

La aplicación estará disponible en `http://localhost:8000`

## Configuración de APIs

### OpenAI API (Requerida)

1. Crear cuenta en [OpenAI Platform](https://platform.openai.com)
2. Generar API key en el dashboard
3. Añadir la clave al archivo `.env`:
   ```
   OPENAI_API_KEY=sk-...
   ```

### OpenWeatherMap API (Opcional)

1. Registrarse en [OpenWeatherMap](https://openweathermap.org/api)
2. Obtener API key gratuita
3. Añadir al archivo `.env`:
   ```
   OPENWEATHER_API_KEY=tu_clave_aqui
   ```

## Estructura del proyecto

```
├── app/
│   ├── Actions/
│   │   ├── ProcessChatMessageAction.php # Lógica de procesamiento de mensajes
│   │   └── ValidateSecurityAction.php   # Validación de seguridad
│   ├── Contracts/
│   │   ├── LlmServiceInterface.php      # Interfaz del servicio LLM
│   │   └── WeatherServiceInterface.php  # Interfaz del servicio meteorológico
│   ├── DTOs/
│   │   ├── ChatMessageDTO.php           # DTO para mensajes de chat
│   │   └── WeatherDataDTO.php           # DTO para datos meteorológicos
│   ├── Enums/
│   │   ├── MessageRole.php              # Enum para roles de mensajes
│   │   └── WeatherErrorCode.php         # Enum para códigos de error meteorológicos
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   └── ChatController.php       # Controlador principal de chat
│   │   └── Middleware/
│   │       └── EnsureSessionUuid.php    # Middleware para gestión de sesiones UUID
│   ├── Models/
│   │   ├── Conversation.php             # Modelo de conversación (session-based)
│   │   └── Message.php                  # Modelo de mensajes (session-based)
│   └── Services/
│       ├── LlmService.php               # Servicio para OpenAI
│       └── WeatherService.php           # Servicio meteorológico
├── database/
│   └── migrations/                     # Migraciones de base de datos
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   ├── Layout/
│   │   │   │   └── AppLayout.vue       # Layout principal
│   │   │   ├── Chat/
│   │   │   │   ├── ChatContainer.vue   # Contenedor del chat
│   │   │   │   ├── ChatHeader.vue      # Cabecera del chat
│   │   │   │   ├── ChatMessages.vue    # Lista de mensajes
│   │   │   │   ├── ChatInput.vue       # Input de mensajes
│   │   │   │   └── MessageBubble.vue   # Burbuja de mensaje
│   │   │   ├── Conversation/
│   │   │   │   ├── ConversationSidebar.vue  # Sidebar conversaciones
│   │   │   │   └── ConversationItem.vue     # Item de conversación
│   │   │   └── UI/
│   │   │       └── ToastNotification.vue    # Notificación toast
│   │   └── app.js                      # Punto de entrada
│   └── views/
│       └── chat.blade.php              # Vista principal
└── routes/
    ├── api.php                         # Rutas de API
    └── web.php                         # Rutas web
```

## API Endpoints

| Método | Endpoint | Descripción | Parámetros |
|--------|----------|-------------|------------|
| POST | `/api/v1/chat/message` | Enviar mensaje al chatbot | `message`, `session_uuid`, `conversation_id?` |
| GET | `/api/v1/chat/conversations` | Obtener conversaciones | `session_uuid` |
| GET | `/api/v1/chat/conversations/{id}` | Obtener conversación específica | `session_uuid` |
| DELETE | `/api/v1/chat/conversations/{id}` | Eliminar conversación | `session_uuid` |

### Ejemplo de uso de la API

```bash
curl -X POST http://localhost:8000/api/v1/chat/message \
  -H "Content-Type: application/json" \
  -H "X-Chat-Session-UUID: 123e4567-e89b-12d3-a456-426614174000" \
  -d '{
    "message": "¿Cómo está el clima en Madrid?"
  }'
```

## Funcionalidades del chatbot

### Consultas soportadas

- Clima actual de cualquier ciudad
- Pronósticos meteorológicos
- Información general sobre meteorología
- Conversación contextual manteniendo historial

### Formato de respuestas meteorológicas

El sistema está configurado para responder con el siguiente formato específico:

```
Clima en [emoji] [Ciudad] (período):
- Temperatura: [X]°C
- [Condición]: [recomendación personalizada]
```

Ejemplo:
```
Clima en ☔ Berlín (mañana):
- Temperatura: 14°C
- Lluvia leve: ¡Sí, te recomiendo que lleves paraguas!
```

## Base de datos

### Tablas principales

**conversations**
- id, session_uuid, title, last_message, last_message_at, timestamps

**messages**
- id, conversation_id, session_uuid, content, role (user/assistant), weather_data_used, timestamps

**Notas sobre la arquitectura:**
- El sistema utiliza `session_uuid` en lugar de autenticación tradicional de usuarios
- Cada sesión de chat es identificada por un UUID único generado automáticamente
- No requiere registro ni login, proporcionando una experiencia completamente anónima

## Testing

```bash
# Ejecutar todos los tests
php artisan test

# Tests específicos
php artisan test --filter=ChatTest

# Con coverage
php artisan test --coverage
```

## Comandos útiles para desarrollo

```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Ver rutas disponibles
php artisan route:list

# Generar controladores/modelos
php artisan make:controller NombreController
php artisan make:model NombreModel -m

# Ver logs en tiempo real
tail -f storage/logs/laravel.log
```

## Configuración para producción

### 1. Optimizaciones de rendimiento

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

### 2. Variables de entorno para producción

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Solución de problemas comunes

### Error de permisos en storage
```bash
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Error de memoria en Composer
```bash
php -d memory_limit=-1 /usr/local/bin/composer install
```

### Assets no se cargan después del build
```bash
npm run build
php artisan storage:link
php artisan config:clear
```

### Error de conexión con OpenAI
1. Verificar que la API key sea válida
2. Comprobar límites de uso en OpenAI dashboard
3. Verificar conectividad a internet

## Seguridad

- Validación de entrada en todos los endpoints
- Sanitización de mensajes antes del almacenamiento
- Rate limiting implementado en API routes
- CSRF protection habilitado
- Encriptación de datos sensibles

## Autor

**Alejandro Restrepo**  
- Email: alejorposa@gmail.com
- GitLab: https://gitlab.com/alejorposa

## Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo LICENSE para más detalles.

## Soporte

Para reportar bugs o solicitar funcionalidades, crear un issue en el repositorio de GitHub.