<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Comms;

use Pupilsight\Contracts\Services\Session;
use Pupilsight\Contracts\Comms\Mailer as MailerInterface;
use Pupilsight\View\View;

/**
 * Mailer class
 *
 * @version v14
 * @since   v14
 */
class Mailer extends \PHPMailer implements MailerInterface
{
    protected $session;
    protected $view;

    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->CharSet = 'UTF-8';
        $this->Encoding = 'base64';
        $this->IsHTML(true);

        if ($this->session->get('enableMailerSMTP') == 'Y') {
            $this->setupSMTP();
        }

        parent::__construct(null);
    }

    public function setView(View $view)
    {
        $this->view = $view;
        $this->view->addData([
            'systemName'            => $this->session->get('systemName'),
            'organisationName'      => $this->session->get('organisationName'),
            'organisationNameShort' => $this->session->get('organisationNameShort'),
            'organisationEmail'     => $this->session->get('organisationEmail'),
            'organisationLogo'      => $this->session->get('organisationLogo'),
        ]);
        
        return $this;
    }

    public function renderBody(string $template, array $data = [])
    {
        $this->Body = $this->view->render($template, $data);
        $this->AltBody = $this->emailBodyStripTags($data['body'] ?? '');
    }

    public function setDefaultSender($subject)
    {
        $this->Subject = $this->session->get('organisationNameShort').' - '.$subject;
        $this->SetFrom($this->session->get('organisationEmail'), $this->session->get('organisationName'));
    }

    protected function setupSMTP()
    {
        $host = $this->session->get('mailerSMTPHost');
        $port = $this->session->get('mailerSMTPPort');

        if (!empty($host) && !empty($port)) {
            $username = $this->session->get('mailerSMTPUsername');
            $password = $this->session->get('mailerSMTPPassword');
            $auth = (!empty($username) && !empty($password));

            $this->IsSMTP();
            $this->Host       = $host;      // SMTP server example
            $this->SMTPDebug  = 0;          // enables SMTP debug information (for testing)
            $this->SMTPAuth   = $auth;      // enable SMTP authentication
            $this->Port       = $port;      // set the SMTP port for the server
            $this->Username   = $username;  // SMTP account username example
            $this->Password   = $password;  // SMTP account password example
            $this->Helo       = parse_url($this->session->get('absoluteURL'), PHP_URL_HOST);

            $encryption = $this->session->get('mailerSMTPSecure');
            if ($encryption == 'auto') {
                // Automatically applies the required type of SMTP security based on the port used.
                if ($port == 465) {
                    $this->SMTPSecure = 'ssl';
                } elseif ($port == 587) {
                    $this->SMTPSecure = 'tls';
                } else {
                    $this->SMTPAutoTLS = true;
                }
            } elseif ($encryption == 'none') {
                // Disables encryption as well as PHPMailer's opportunistic TLS setting.
                $this->SMTPSecure = false;
                $this->SMTPAutoTLS = false;
            } else {
                // Explicitly use the selected type of encryption.
                $this->SMTPSecure = $encryption;
            }
        }
    }

    protected function emailBodyStripTags($body)
    {
        $body = preg_replace('#<br\s*/?>#i', "\n", $body);
        $body = str_replace(['</p>', '</div>'], "\n\n", $body);
        $body = preg_replace("#\<a.+href\=[\"|\'](.+)[\"|\'].*\>.*\<\/a\>#U", '$1', $body);
        $body = strip_tags($body, '<a>');

        return $body;
    }
}
