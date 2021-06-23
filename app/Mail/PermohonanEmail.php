<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PermohonanEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $datapermintaan, $databarang, $kepada;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($datapermintaan, $databarang, $kepada)
    {

        $this->datapermintaan = $datapermintaan;
        $this->databarang = $databarang;
        $this->kepada = $kepada;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail/permohonan');
    }
}
