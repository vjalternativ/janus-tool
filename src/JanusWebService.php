<?php
use Curl\Curl;

class JanusWebService
{

    private static $instance = null;

    private $curl;

    private $sessionId;

    private $pluginVsHandlerId = array();

    private $apiDomain;

    private $apiSecret;

    private function __construct()
    {
        $this->curl = new Curl();
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new JanusWebService();
        }
        return self::$instance;
    }

    public function init($vmsConfigArray = array())
    {
        if (isset($vmsConfigArray['api']['domain'])) {
            $this->apiDomain = $vmsConfigArray['api']['domain'];
        } else {
            throw new Exception("api domain not specified");
        }

        if (isset($vmsConfigArray['api']['secret'])) {
            $this->apiSecret = $vmsConfigArray['api']['secret'];
        } else {
            throw new Exception("api secret not specified");
        }
    }

    function getTransactionId()
    {
        return uniqid();
    }

    function sendMessageToPlugin($plugin, $data)
    {
        $handleId = $this->pluginVsHandlerId[$plugin];
        $curl = new Curl();
        $curl->setDefaultJsonDecoder(true);
        $json = array();
        $json['janus'] = "message";
        $json['transaction'] = $this->getTransactionId();
        $json['apisecret'] = $this->apiSecret;
        $json['plugin'] = $plugin;
        $json["body"] = $data;

        ;

        $result = $curl->post($this->apiDomain . "/janus/" . $this->getSessionId() . "/" . $handleId, json_encode($json));

        $curl->close();
        if (isset($result['janus']) && $result['janus'] == "success") {} else {
            throw new Exception("invalid send message request plugin " . json_encode($result));
        }

        return $result;
    }

    function attachPlugin($plugin)
    {
        $curl = new Curl();
        $curl->setDefaultJsonDecoder(true);
        $json = array();
        $json['janus'] = "attach";
        $json['transaction'] = $this->getTransactionId();
        $json['apisecret'] = $this->apiSecret;
        $json['plugin'] = $plugin;

        $result = $curl->post($this->apiDomain . "/janus/" . $this->getSessionId(), json_encode($json));
        $curl->close();
        if (isset($result['data']) && $result['janus'] == "success") {
            $this->pluginVsHandlerId[$plugin] = $result['data']['id'];
        } else {
            throw new Exception("invalid attach plugin " . json_encode($result));
        }
    }

    /**
     *
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     *
     * @param mixed $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    function login()
    {
        $curl = new Curl();
        $curl->setDefaultJsonDecoder(true);
        $json = array();
        $json['janus'] = "create";
        $json['transaction'] = $this->getTransactionId();
        $json['apisecret'] = $this->apiSecret;

        $result = $curl->post($this->apiDomain . "/janus", json_encode($json));
        $curl->close();
        if (isset($result['data']) && $result['janus'] == "success") {
            $this->setSessionId($result['data']['id']);
        } else {
            throw new Exception("invalid authorization " . json_encode($result));
        }
    }
}
?>