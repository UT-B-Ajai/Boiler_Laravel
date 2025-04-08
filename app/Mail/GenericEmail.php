<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $subjectText;
    public $viewName;

    public function __construct($subjectText, $viewName, $data)
    {
        $this->subjectText = $subjectText;
        $this->viewName = $viewName;
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject($this->subjectText)
                    ->view($this->viewName)
                    >with(['data' => $this->data]);
    }
}
