<?php

namespace App\Enums;

enum WeatherErrorCode: string
{
    case CITY_NOT_FOUND = 'ciudad_no_encontrada';
    case INVALID_PARAMETERS = 'parametros_invalidos';
    case RATE_LIMIT_EXCEEDED = 'limite_excedido';
    case WEATHER_SERVER_ERROR = 'error_servidor_clima';
    case WEATHER_API_ERROR = 'error_api_clima';
    case INVALID_RESPONSE = 'respuesta_invalida';
    case NO_INTERNET_CONNECTION = 'sin_conexion_internet';
    case API_TIMEOUT = 'timeout_api_clima';
    case GENERAL_WEATHER_ERROR = 'error_general_clima';

    public function getMessage(string $city = ''): string
    {
        return match($this) {
            self::CITY_NOT_FOUND => "No pude encontrar información meteorológica para '{$city}'. ¿Podrías verificar el nombre de la ciudad?",
            self::INVALID_PARAMETERS => "Hubo un problema con los parámetros de consulta del clima. Intenta con otra ciudad.",
            self::RATE_LIMIT_EXCEEDED => "El servicio meteorológico está temporalmente saturado. Por favor intenta nuevamente en unos minutos.",
            self::WEATHER_SERVER_ERROR => "El servicio meteorológico está experimentando problemas técnicos. Intenta más tarde.",
            self::WEATHER_API_ERROR => "No pude acceder al servicio meteorológico en este momento. Intenta nuevamente.",
            self::INVALID_RESPONSE => "Recibí una respuesta inesperada del servicio meteorológico. Intenta con otra consulta.",
            self::NO_INTERNET_CONNECTION => "No puedo conectarme al servicio meteorológico. Verifica tu conexión a internet.",
            self::API_TIMEOUT => "La consulta meteorológica está tardando demasiado. Intenta nuevamente.",
            self::GENERAL_WEATHER_ERROR => "Ocurrió un error inesperado al obtener datos del clima. Por favor intenta más tarde."
        };
    }
}