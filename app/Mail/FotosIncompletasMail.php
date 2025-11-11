<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FotosIncompletasMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $inspector;
    public $expedientes;
    
    public function __construct($inspector, $expedientes)
    {
        $this->inspector = $inspector;
        $this->expedientes = $expedientes;
    }

    public function build()
    {
        return $this->subject('Expedientes con fotos incompletas')
                    ->markdown('emails.fotos_incompletas');
    }
}
