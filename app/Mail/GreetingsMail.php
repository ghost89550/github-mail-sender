<?php declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GreetingsMail extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * @var string
     */
    public $weather;

    /**
     * Create a new message instance.
     *
     * @param string $weather
     */
    public function __construct($weather = 'test')
    {
        $this->weather = $weather;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('greetings')
            ->with(['weather' => $this->weather]);
    }
}
