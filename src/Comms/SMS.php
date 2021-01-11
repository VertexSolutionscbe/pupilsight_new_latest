<?php
/*
Pupilsight, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

namespace Pupilsight\Comms;

use function Gears\String\length;
use Pupilsight\Comms\Drivers\MailDriver;
use Pupilsight\Comms\Drivers\OneWaySMSDriver;
use Pupilsight\Comms\Drivers\UnknownDriver;
use Pupilsight\Contracts\Comms\SMS as SMSInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use Matthewbdaly\SMS\Client;
use Matthewbdaly\SMS\Drivers\Twilio;
use Matthewbdaly\SMS\Drivers\Nexmo;
use Matthewbdaly\SMS\Drivers\Clockwork;
use Matthewbdaly\SMS\Drivers\TextLocal;
use Matthewbdaly\SMS\Exceptions\DriverNotConfiguredException;
use Pupilsight\Domain\DBQuery;

/**
 * Factory class to create a fully configured SMS client based on the chosen gateway.
 * 
 * @version v17
 * @since   v17
 */
class SMS implements SMSInterface
{
    protected $client;

    protected $driver;

    protected $to;

    protected $from;

    protected $content;

    protected $batchSize;

    protected $noofrecipents = 0;

    protected $totalchars = 0;



    public function __construct(array $config)
    {
        try {
            switch ($config['smsGateway']) {
                case 'Pupilpod':
                    $this->batchSize = 10;
                    $this->driver = new OneWaySMSDriver($config);
                    break;

                case 'OneWaySMS':
                    $this->batchSize = 10;
                    $this->driver = new OneWaySMSDriver($config);
                    break;

                case 'Twilio':
                    $this->driver = new Twilio(new GuzzleClient(), new Response(), [
                        'account_id' => $config['smsUsername'],
                        'api_token' => $config['smsPassword'],
                    ]);
                    break;

                case 'Nexmo':
                    $this->driver = new Nexmo(new GuzzleClient(), new Response(), [
                        'api_key' => $config['smsUsername'],
                        'api_secret' => $config['smsPassword'],
                    ]);
                    break;

                case 'Clockwork':
                    $this->driver = new Clockwork(new GuzzleClient(), new Response(), [
                        'api_key' => $config['smsUsername'],
                    ]);
                    break;

                case 'TextLocal':
                    $this->batchSize = 10;
                    $this->driver = new TextLocal(new GuzzleClient(), new Response(), [
                        'api_key' => $config['smsUsername'],
                    ]);
                    break;

                case 'Mail to SMS':
                    $this->driver = new MailDriver($config['smsMailer'], [
                        'domain' => $config['smsUsername'],
                    ]);
                    break;

                default:
                    throw new DriverNotConfiguredException();
            }
        } catch (DriverNotConfiguredException $e) {
            $this->driver = new UnknownDriver();
        }

        $this->client = new Client($this->driver);

        $this->to = [];
        $this->from($config['smsSenderID']);
    }

    /**
     * Get the SMS driver name.
     *
     * @return string
     */
    public function getDriver() : string
    {
        return $this->client->getDriver();
    }

    /**
     * Get the SMS credit balance, if supported by the driver.
     *
     * @return float
     */
    public function getCreditBalance() : float
    {
        return method_exists($this->driver, 'getCreditBalance')
            ? $this->driver->getCreditBalance()
            : 0;
    }

    /**
     * Set the message recipient(s).
     *
     * @param string|array $to
     */
    public function to($to)
    {
        $this->to = array_merge($this->to, is_array($to) ? $to : [$to]);

        return $this;
    }

    /**
     * Set the message sender name.
     *
     * @param string $from
     */
    public function from(string $from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Set the message content.
     *
     * @param string $from
     */
    public function content(string $content)
    {
        $this->content = stripslashes(strip_tags($content));
        $this->totalchars=length($this->content);
        return $this;
    }

    /**
     * Send the message to one or more recipients.
     *
     * @param array $to The recipient array.
     *
     * @return array Array of successful recipients.
     */

    public function send(array $recipients = []) : array
    {
        $this->noofrecipents=sizeof($recipients);
        $sent = [];
        $recipients += array_merge($this->to, $recipients);

        // Split the messages into comma-separated batches, if supported by the driver.
        if (!empty($this->batchSize)) {
            $recipients = array_map(function ($phoneNumbers) {
                return implode(',', $phoneNumbers);
            }, array_chunk($recipients, $this->batchSize));
        }

        $i=0;
        $strto="";
        foreach ($recipients as $recipient) {
            $message = [
                'to'      => $recipient,
                'from'    => $this->from,
                'content' => $this->content,
            ];
            //print_r($message);
            //die();
            if(!empty($strto)){
                $strto .=",";
            }
            $strto .= $recipient;
            if($i>50){
                $this->sendSMS($strto, $this->content);
                $i=0;
                $strto = "";
            }
            
            /*
            if ($this->client->send($message)) {
                $sent[] = $recipient;
            }*/
            $i++;
        }

        if(!empty($strto)){
            $this->sendSMS($strto, $this->content);
        }
        return $sent;
    }

    public function sendSMS($numbers, $msg){
        try{
            $sql = "SELECT * FROM pupilsightSetting WHERE scope='Messenger' AND name='smsGateway'";
            $db = new DBQuery();
            $rs = $db->selectRaw($sql, TRUE);
            if (empty($rs)) {
                $dsempty = array();
                return $db->convertDataset($dsempty);
            }else {
                $activeGateway= $rs[0]['value'];
            }

            $sql1 = "SELECT * FROM pupilsightSetting WHERE scope='Messenger' AND description='$activeGateway'";
            $db1 = new DBQuery();
            $rs1 = $db1->selectRaw($sql1, TRUE);
            if (empty($rs1)) {
                $dsempty1 = array();
                //return $db1->convertDataset($dsempty1);
            }else {
                $val= $rs1[0]['value'];
            }

            $charcount=$this->totalchars;
            $cal=ceil($charcount/160);
            $totalmsges=$cal*$this->noofrecipents;
            $val=$val+$totalmsges;
//die();
            switch ($activeGateway) {
                case 'Karix':
                    $url1 = "https://japi.instaalerts.zone/httpapi/QueryStringReceiver?ver=1.0&key=WVDLxrEydZYYMKZ8w6aJLQ==&encrpt=0&send=PUPLPD";
                    $url1 .="&text=".urlencode($msg);
                    $url1 .="&dest=".$numbers;
                    $res = file_get_contents($url1);
                    $res1=explode('&',$res);
                    $res2=explode('=',$res1[1]);
                    $res3=$res2[1];
                    if($res3==200)
                    {
                        //echo "success";
                        $sq = "UPDATE pupilsightSetting SET value=". $val ." WHERE scope='Messenger' AND description='Karix' ";
                        $db2 = new DBQuery();
                        //echo "\n".$sq;
                        $db2->query($sq);
                    }else{
                        echo "error";
                    }

                    break;

                case 'Gupshup':
                    $url = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
                    $url .="&send_to=".$numbers;
                    //$url .="&msg=".rawurlencode($msg);
                    $url .="&msg=".urlencode($msg);
                    $url .="&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
                    //echo $url;
                    //$this->getAsyncCurl($url);
                    $res = file_get_contents($url);
                    $res1=explode('|',$res);
                    $res2=trim($res1[0]);
                    $res3='success';
                    //print_r(strcmp($res2,$res3));die();
                    if(strcmp($res2, $res3) === 0)
                    {
                        //echo "success"; die();
                        $sq = "UPDATE pupilsightSetting SET value=". $val ." WHERE scope='Messenger' AND description='Gupshup' ";
                        $db2 = new DBQuery();
                        //echo "\n".$sq;
                        $db2->query($sq);
                        //die();
                    }else{
                        echo "error";
                    }
                    break;

                default :
                    echo "sms not configured";
            }

        }catch(Exception $ex){
            print_r($ex);

        }
    }
}
