<?
    // 1. secrets
    $accessKey = getenv('PBI_ACCESSKEY');
    $reportId = getenv('PBI_REPORTID');
    $workspaceId = getenv('PBI_WSID');
    $workspaceCollectionName = getenv('PBI_WSCNAME');
    
    // 2. construct input value
    $token1 = "{" .
      "\"typ\":\"JWT\"," .
      "\"alg\":\"HS256\"" .
      "}";
    $token2 = "{" .
      "\"wid\":\"" . $workspaceId . "\"," . // workspace id
      "\"rid\":\"" .$reportId . "\"," . // report id
      "\"wcn\":\"" . $workspaceCollectionName . "\"," . // workspace collection name
      "\"iss\":\"PowerBISDK\"," .
      "\"ver\":\"0.2.0\"," .
      "\"aud\":\"https://analysis.windows.net/powerbi/api\"," .
      "\"nbf\":" . date("U") . "," .
      "\"exp\":" . date("U" , strtotime("+1 hour")) .
      "}";
    $inputval = rfc4648_base64_encode($token1) .
      "." .
      rfc4648_base64_encode($token2);

    // 3. get encoded signature value
    $hash = hash_hmac("sha256",
        $inputval,
        $accessKey,
        true);
    $sig = rfc4648_base64_encode($hash);

    // 4. get apptoken
    $appToken = $inputval . "." . $sig;

    // helper functions
    function rfc4648_base64_encode($arg) {
      $res = $arg;
      $res = base64_encode($res);
      $res = str_replace("/", "_", $res);
      $res = str_replace("+", "-", $res);
      $res = rtrim($res, "=");
      return $res;
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Test page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <button id="print">Print</button>
    <div id="reportContainer" style="width: 100%; height: 400px"></div>
    <script src="/lib/jquery/dist/jquery.js"></script>
    <script src="/lib/powerbi-client/dist/powerbi.js"></script>    
    <script>
        var config= {
            type: 'report',
            accessToken: '<?=$appToken?>',
            embedUrl: 'https://embedded.powerbi.com/appTokenReportEmbed?reportId=<?=$reportId?>',
            id: '<?=$reportId?>',
            settings: {
                filterPaneEnabled: true,
                navContentPaneEnabled: true
            }
        };
        $(function(){
            var reportContainer = $('#reportContainer')[0];
            var report = powerbi.embed(reportContainer, config);
            $('#print').click(function(){
                var report = powerbi.embeds[0];
                report.print()
            });
        });
    </script>
</body>