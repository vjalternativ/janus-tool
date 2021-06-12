<?php
require_once '../vendor/autoload.php';
require_once '../src/JanusWebService.php';

class JanusWebServiceTest
{

    function execute()
    {
        $janusService = JanusWebService::getInstance();

        $janusService->login();
        $janusService->attachPlugin("janus.plugin.videoroom");

        $data = array(
            "request" => "create",
            "room" => "",
            "permanent" => false,
            "description" => "",
            "is_private" => false,
            "record" => true,
            "rec_dir" => "",
            "bitrate" => 60000
        );

        $date = date("Y-m-d");

        for ($i = 1; $i <= 2; $i ++) {
            $room = "loadtest-" . $i;

            $data["room"] = $room;
            $data['rec_dir'] = "/opt/janus/var/recordings/" . $room;

            $janusService->sendMessageToPlugin("janus.plugin.videoroom", $data);
        }
    }
}

$ob = new JanusWebServiceTest();
$ob->execute();
?>