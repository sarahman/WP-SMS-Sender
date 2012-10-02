<?php
/**
 * Description of ClickATell Class
 *
 * It deals with www.clickatell.com API.
 *
 * @author      Syed Abidur Rahman <aabid048@gmail.com>
 */
class ClickATell
{
    private $baseUrl = "http://api.clickatell.com";
    private $smsAccount = 'sms@messaging.clickatell.com';
    private $username = '';
    private $password = '';
    private $apiId = '';
    private $contacts = array();
    private $text = '';

    public function __construct($credentials)
    {
        $this->username = $credentials['username'];
        $this->password = $credentials['password'];
        $this->apiId = $credentials['apiId'];
    }

    public function checkCredentialsNotEmpty()
    {
        if (empty($this->username) || empty($this->password) || empty($this->apiId)) {
            return false;
        }

        return true;
    }

    public function authenticateCredentials($returnSession = false)
    {
        $url = "{$this->baseUrl}/http/auth?user={$this->username}&password={$this->password}&api_id={$this->apiId}";
        try {
            $response = file($url); // do auth call
        } catch(Exception $e){
            $response = array('Not Ok');
        };

        // explode our response. return string is on first line of the data returned
        $gatewaySession = explode(":", $response[0]);
        if ($gatewaySession[0] != "OK") {
            return false;
        }
        return $returnSession ? trim($gatewaySession[1]) : true; // remove any whitespace
    }

    public function setContacts(array $contacts)
    {
        $this->contacts = $contacts;
    }

    public function setSMSText($smsText = '')
    {
        $this->text = $smsText;
    }

    public function sendSMS()
    {
        if ($this->checkCredentialsNotEmpty()) {

            if ($sessionId = $this->authenticateCredentials(true)) {
                return $this->dealsWithSendingSMS($sessionId);
            } else {
                return array(
                    'type' => 'error',
                    'msg' => 'Authentication of the credential has been failed.'
                );
            }

        } else {
            return array(
                'type' => 'error',
                'msg' => 'Credential information is missing.'
            );
        }
    }

    public function getEmailDetail()
    {
        $contactStr = '';
        foreach ($this->contacts AS $contact) {
            empty($contact) || $contactStr .= "to:{$contact}\n\r";
        }
        $mailBody = <<<EOF
            api_id:{$this->apiId}
            user:{$this->username}
            password:{$this->password}
            text:{$this->text}
            {$contactStr}
            mo=1
            from=13476622235
EOF;

        return array(
            'to' => $this->smsAccount,
            'subject' => '',
            'message' => $mailBody
        );
    }

    private function dealsWithSendingSMS($sessionId)
    {
        $contacts = implode(',', $this->contacts);
        $text = urlencode($this->text);
        $smsUrl = "{$this->baseUrl}/http/sendmsg?session_id={$sessionId}&to={$contacts}&text={$text}&mo=1&from=13476622235";

        $response = file($smsUrl);

        $count = 0;
        $resultOfSendingSMS = array();
        foreach ($response AS $currentResponse) {

            $send = explode(":", $currentResponse);

            if ($send[0] == "ID") {
                $resultOfSendingSMS[] = array('contact' => $this->contacts[$count++], 'type' => 'success');
            } else {
                $error = explode(', ', $send[1]);
                if ($error[0] == '301') {
                    $resultOfSendingSMS[] = array(
                        'contact' => $this->contacts[$count++],
                        'type' => 'error',
                        'msg' => 'no credit left.');

                } elseif ($error[0] == 114) {
                    $resultOfSendingSMS[] = array(
                        'contact' => $this->contacts[$count++],
                        'type' => 'error',
                        'msg' => 'incorrect number perhaps.');
                }
            }
        }

        return $resultOfSendingSMS;
    }
}