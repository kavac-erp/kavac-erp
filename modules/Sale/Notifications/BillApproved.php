<?php

/** Notificaciones del módulo de comercialización */

namespace Modules\Sale\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Modules\Sale\Models\SaleBill;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * @class BillApproved
 * @brief Notificaciones de factura aprobada
 *
 * Gestiona las Notificaciones al aprobar una factura
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BillApproved extends Notification //implements ShouldQueue
{
    use Queueable;

    /**
     * Objeto con información del usuario registrado
     *
     * @var User $user
      */
    protected $user;

    /**
     * Objeto con información de la factura aprobada
     *
     * @var SaleBill $bill
     */
    protected $bill;

    /**
     * Datos del usuario autenticado
     *
     * @var User $auth_user
     */
    protected $auth_user;

    /**
     * Título de la notificación
     *
     * @var string $notifyTitle
     */
    protected $notifyTitle;

    /**
     * Mensaje de la notificación
     *
     * @var string $notifyMessage
     */
    protected $notifyMessage;

    /**
     * Crea una nueva instancia de la notificación.
     *
     * @return void
     */
    public function __construct(User $user, $auth_user, $bill)
    {
        $this->user = $user;
        $this->auth_user = $auth_user;
        $this->bill = $bill;
        $this->notifyTitle = __('Factura aprobada');
        $this->notifyMessage = __('Se ha aprobado una factura');
    }

    /**
     * Obtiene el canal de la notificación
     *
     * @param  mixed  $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Obtiene la representación por correo electrónico de la notificación.
     *
     * @param  mixed  $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject(config('app.name') . ' - ' . __('Factura aprobada'))
                    ->greeting(__('Estimado(a) :username', ['username' => $this->auth_user->username]))
                    ->line(
                        __('El usuario :auth_username', ['auth_username' => $this->user->username])
                    )
                    ->line(__('ha aprobado la factura :bill', ['bill' => $this->bill->code]))
                    ->line(__('Para detallar la factura, pulse a continuación'))
                    ->action(__('Factura'), url('/sale/bills/pdf/' . $this->bill->id))
                    ->line(__(
                        'Este correo es enviado de manera automática por la aplicación y no esta siendo monitoreado. ' .
                        'Por favor no responda a este correo!'
                    ));
    }

    /**
     * Obtiene la representación de arreglo de la notificación.
     *
     * @param  mixed  $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => $this->notifyTitle,
            'module' => null,
            'message' => $this->notifyMessage,
        ];
    }

    /**
     * Obtiene la representación de base de datos de la notificación.
     *
     * @method  toDatabase
     *
     * @param  mixed  $notifiable
     *
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->notifyTitle,
            'module' => null,
            'message' => $this->notifyMessage,
        ];
    }
}
