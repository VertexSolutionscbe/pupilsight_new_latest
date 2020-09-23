<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff;

use Pupilsight\Services\Format;

/**
 * Message
 *
 * @version v18
 * @since   v18
 */
abstract class Message
{
    abstract public function getTitle() : string;
    abstract public function getText() : string;
    abstract public function getModule() : string;

    public function getSubject() : string
    {
        $currentDate = Format::date(date('Y-m-d'));
        return $this->getTitle()." ({$currentDate})";
    }

    public function getAction() : string
    {
        return '';
    }

    public function getLink() : string
    {
        return '';
    }

    public function getDetails() : array
    {
        return [];
    }

    public function via() : array
    {
        return ['mail'];
    }

    /**
     * Format the message to send via SMS.
     *
     * @return string
     */
    public function toSMS() : string
    {
        return $this->getText();
    }

    /**
     * Format the message to send via Mail.
     *
     * @return array
     */
    public function toMail() : array
    {
        return [
            'subject' => $this->getSubject(),
            'title'   => $this->getTitle(),
            'body'    => $this->getText(),
            'details' => array_filter($this->getDetails()),
            'button'  => [
                'url'  => $this->getLink(),
                'text' => $this->getAction(),
            ],
        ];
    }

    /**
     * Format the message to send via Database.
     *
     * @return array
     */
    public function toDatabase() : array
    {
        return [
            'text'       => $this->getText(),
            'moduleName' => $this->getModule(),
            'actionLink' => '/'.$this->getLink(),
        ];
    }
}
