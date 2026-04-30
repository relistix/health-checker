<?php

namespace Relistix\HealthChecker\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HealthCheckMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $checkName,
        public string $subjectLine,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            text: 'healthchecker::mail.probe',
            with: [
                'appName' => config('app.name'),
                'checkName' => $this->checkName,
                'host' => gethostname() ?: 'unknown',
                'timestamp' => now()->toIso8601String(),
            ],
        );
    }
}
