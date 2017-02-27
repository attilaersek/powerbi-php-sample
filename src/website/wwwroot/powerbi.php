<?
    class ReportUser {
        public $userName;
        public $role;

        function __construct($userName, $role) {
            $this->userName = $userName;
            $this->role = $role;
        }
    }

    class Report {
        private $powerBi;
        public $id;
        public $name;
        public $webUrl;
        public $embedUrl;

        function __construct($powerBi, $data) {
            $this->powerBi = $powerBi;
            $this->id = $data['id'];
            $this->name = $data['name'];
            $this->webUrl = $data['webUrl'];
            $this->embedUrl = $data['embedUrl'];            
        }

        function getEmbedToken(ReportUser $reportUser = NULL) {
            return $this->powerBi->getEmbedToken($this->id, $reportUser);
        }
    }

    class PowerBi {
        private $accessKey;
        private $workspaceCollection;
        private $workspaceId;
        private $reports = array();
        private $baseUrl;

        function __construct($accessKey, $workspaceCollection, $workspaceId) {
            $this->accessKey = $accessKey;
            $this->workspaceCollection = $workspaceCollection;
            $this->workspaceId = $workspaceId;
            $this->baseUrl = "https://api.powerbi.com/v1.0/collections/$this->workspaceCollection/workspaces/$this->workspaceId/";
        }

        function getReport($report_id) {
            $this->getReports();
            if (!empty($this->reports)) {
                $report = array_filter($this->reports['value'], function($value) use ($report_id) {
                    return $value->id === $report_id;
                });
                return reset($report);
            }
            return FALSE;
        }

        function getReports($refresh = FALSE) {
            if (empty($this->reports) || $refresh) {
                $map = function($data) {
                    return new Report($this, $data);
                };
                $result = $this->call("reports");
                $this->reports = array_map($map, $result['value']);
            }
            return $this->reports;
        }
        
        private function call($url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: AppKey " . $this->accessKey
            ));
            $response = curl_exec($ch);
            if(curl_error($ch)) {
                return false;
            }
            $result = json_decode($response, true);
            return $result;
        }

        function getEmbedToken($reportId, ReportUser $reportUser = NULL) {
            $token1 = "{" . "\"typ\":\"JWT\"," . "\"alg\":\"HS256\"" . "}";
            $token2 = "{" .
                "\"wid\":\"" . $this->workspaceId . "\"," . // workspace id
                "\"rid\":\"" .$reportId . "\"," . // report id
                "\"wcn\":\"" . $this->workspaceCollection . "\"," . // workspace collection name
                "\"iss\":\"PowerBISDK\"," .
                "\"ver\":\"0.2.0\"," .
                "\"aud\":\"https://analysis.windows.net/powerbi/api\"," .
                "\"nbf\":" . date("U") . "," .
                "\"exp\":" . date("U" , strtotime("+1 hour")) .
                "}";
            $inputval = $this->rfc4648_base64_encode($token1) . "." . $this->rfc4648_base64_encode($token2);

            $hash = hash_hmac("sha256",
                $inputval,
                $this->accessKey,
                true);
            $sig = $this->rfc4648_base64_encode($hash);

            $token = $inputval . "." . $sig;
            return $token;
        }

        private function rfc4648_base64_encode($arg) {
            $res = $arg;
            $res = base64_encode($res);
            $res = str_replace("/", "_", $res);
            $res = str_replace("+", "-", $res);
            $res = rtrim($res, "=");
            return $res;
        }
    }
?>