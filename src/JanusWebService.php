<?php
use Curl\Curl;

class JanusWebService
{

    private static $instance = null;

    private $curl;

    private $apiEndpoint;

    private $sessionId;

    private $pluginVsHandlerId = array();

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
        $json['apisecret'] = "janusrocks";
        $json['plugin'] = $plugin;
        $json["body"] = $data;

        ;

        $result = $curl->post("http://localhost:8088/janus/" . $this->getSessionId() . "/" . $handleId, json_encode($json));

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
        $json['apisecret'] = "janusrocks";
        $json['plugin'] = $plugin;

        $result = $curl->post("http://localhost:8088/janus/" . $this->getSessionId(), json_encode($json));
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
        $json['apisecret'] = "janusrocks";

        $result = $curl->post("http://localhost:8088/janus", json_encode($json));
        $curl->close();
        if (isset($result['data']) && $result['janus'] == "success") {
            $this->setSessionId($result['data']['id']);
        } else {
            throw new Exception("invalid authorization " . json_encode($result));
        }
    }
}
?>