<?php
include ("Config.php");

class SqlmapAPI
{
    private $api = API_URL;
    public $task_id;

    public function generateNewTaskID() {
        $json = json_decode(file_get_contents($this->api . "task/new"), true);
        if(($json['success'] == "true") && (trim($json['taskid']) != "")) {
            return trim($json['taskid']);
        }
        return NULL;
    }

    public function deleteTaskID($id) {
        $json = json_decode(file_get_contents($this->api . "task/" . $id . "/delete"), true);
        if($json['success'] == "true") {
            return true;
        }
        return false;
    }

    public function listOptions($taskid) {
        $json = json_decode(file_get_contents($this->api . "option/" . $taskid . "/list"), true);
        if($json['success'] == "true") {
            return $json;
        }
        return false;
    }

    public function getOptionValue($taskid, $optstr) {
        // Sorry, not going to pass through code to be eval'd in setter so not going to bother trying to return value...
        if((strtolower(trim($optstr)) != "evalcode") && (strtolower(trim($optstr)) != "eval")) {
            $opts = array(
                'http'=> array(
                    'method'=>"POST",
                    'header'=>"Content-Type: application/json\r\n",
                    'content' => '{"option":"' . trim($optstr) . '"}',
                    'timeout' => 60
                )
            );
            $context = stream_context_create($opts);
            $json = json_decode(file_get_contents($this->api . "option/" . $taskid . "/get", false, $context), true);
            if($json['success'] == "true") {
                return $json[$optstr];
            }
        }
        return false;
    }

    /*
       Set SQLMAP Configuration Option Value under specific task ID
       Returns true on success, false otherwise
         $taskid = your user level task id to look under
         $optstr = the SQLMAP configuration option we want to set value for (case sensitive)
         $optvalue = the value to set for configuration option above ($optstr)
    */
    public function setOptionValue($taskid, $optstr, $optvalue, $integer=false) {
        // Sorry, not going to pass through code to be eval'd here...
        if((strtolower(trim($optstr)) != "evalcode") && (strtolower(trim($optstr)) != "eval")) {
            if(!$integer) {
                $opts = array(
                    'http'=> array(
                        'method'=>"POST",
                        'header'=>"Content-Type: application/json\r\n",
                        'content' => '{"' . trim($optstr) . '":"' . trim($optvalue) . '"}',
                        'timeout' => 60
                    )
                );
            } else {
                $opts = array(
                    'http'=> array(
                        'method'=>"POST",
                        'header'=>"Content-Type: application/json\r\n",
                        'content' => '{"' . trim($optstr) . '":' . trim($optvalue) . '}',
                        'timeout' => 60
                    )
                );
            }
            $context = stream_context_create($opts);
            $json = json_decode(file_get_contents($this->api . "option/" . $taskid . "/set", false, $context), true);
            if($json['success'] == "true") {
                return true;
            }
        }
        return false;
    }

    /*
       Start SQLMAP Scan using all configured options under user level task ID
       Returns the scan engine id for tracking status and results on success, false otherwise
         $taskid = your user level task id to track scan under
    */
    public function startScan($taskid) {
        $opts = array(
            'http'=> array(
                'method'=>"POST",
                'header'=>"Content-Type: application/json\r\n",
                'content' => '{ "url":"' . trim($this->getOptionValue($taskid, "url")) . '"}',
                'timeout' => 60
            )
        );
        $context = stream_context_create($opts);
        $json = json_decode(file_get_contents($this->api . "scan/" . $taskid . "/start", false, $context), true);
        if($json['success'] == 1) {
            return $json['engineid'];
        }
        return false;
    }

    /*
      Gracefully Stop a SQLMAP Scan, identified by user level task ID
      Returns true on success, false otherwise
        $taskid = your user level task id to stop scan for
   */
    public function stopScan($taskid) {
        $json = json_decode(file_get_contents($this->api . "scan/" . $taskid . "/stop"), true);
        if($json['success'] == 1) {
            return true;
        }
        return false;
    }

    /*
      Forcefully KILL a SQLMAP Scan, identified by user level task ID
      Returns true on success, false otherwise
        $taskid = your user level task id to kill scan for
   */
    public function killScan($taskid) {
        $json = json_decode(file_get_contents($this->api . "scan/" . $taskid . "/kill"), true);
        if($json['success'] == 1) {
            return true;
        }
        return false;
    }

    /*
      Check Status for a SQLMAP Scan, identified by user level task ID
      Returns associative array on success, false otherwise
          array(
            "status" => "running|terminated|not running",
            "code" => (int) "Process Polling Return Code, Status Percent?"
          );

        $taskid = your user level task id to check scan status for
   */
    public function checkScanStatus($taskid) {
        $json = json_decode(file_get_contents($this->api . "scan/" . $taskid . "/status"), true);
        if($json['success'] == 1) {
            return array("status" => $json['status'], "code" => $json['returncode']);
        }
        return false;
    }

    /*
       Fetch the Scan Data from finished SQLMAP scan, identified by user level task ID
       Returns associative array on success, false otherwise
           array(
             "data"  => array(
                "status" => "stats",
                "type" => "content_type",
                "value" => "some value"
                ),
             "error" => array("error msg", "error msg2", ...)
           );

         $taskid = your user level task id  to get scan data for
    */
    public function getScanData($taskid) {
        $json = json_decode(file_get_contents($this->api . "scan/" . $taskid . "/data"), true);
        if($json['success'] == 1) {
            return array("data" => $json['data'], "error" => $json['error']);
        }
        return false;
    }
}