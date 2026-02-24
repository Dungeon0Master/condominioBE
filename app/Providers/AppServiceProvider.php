<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 2. Mantenemos tu URL personalizada apuntando a React
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return "http://localhost:5173/set-password?token={$token}&email={$notifiable->getEmailForPasswordReset()}";
        });

        // 3. NUEVO: Personalizamos el texto y asunto del correo
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            
            // Volvemos a generar la URL para el botón
            $url = "http://localhost:5173/set-password?token={$token}&email={$notifiable->getEmailForPasswordReset()}";

            return (new MailMessage)
                ->subject('¡Bienvenido a la plataforma! Configura tu acceso') // El asunto del correo
                ->greeting('¡Hola! Bienvenido a tu nuevo condominio.') // El saludo inicial
                ->line('El administrador te ha dado de alta en el sistema de gestión de residentes.')
                ->line('Para poder ingresar, participar en el chat general y ver tus alertas, por favor crea tu contraseña segura haciendo clic en el botón de abajo.')
                ->action('Crear mi contraseña', $url) // El botón principal
                ->line('Si tú no esperabas esta invitación o no eres residente, puedes ignorar y eliminar este mensaje.')
                ->salutation('Saludos cordiales, El equipo de Administración.'); // La despedida
        });
    }
}