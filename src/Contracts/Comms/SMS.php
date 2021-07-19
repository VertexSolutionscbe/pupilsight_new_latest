<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Contracts\Comms;

/**
 * An interface for sending SMS messages.
 */
interface SMS
{
    public function getDriver(): string;

    public function getCreditBalance(): float;

    public function to($to);

    public function from(string $from);

    public function content(string $message);

    public function send(array $recipients): array;
}
