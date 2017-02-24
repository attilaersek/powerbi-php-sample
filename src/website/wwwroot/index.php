<?
    include './powerbi.php';

    // 1. secrets
    $accessKey = getenv('PBI_ACCESSKEY');
    $reportId = getenv('PBI_REPORTID');
    $workspaceId = getenv('PBI_WSID');
    $workspaceCollectionName = getenv('PBI_WSCNAME');
    
    $powerbi = new PowerBi($accessKey, $workspaceCollectionName, $workspaceId);
    $reports = $powerbi->getReports();
    $report = $reports[0];
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
    <div id="reportContainer" style="width: 100%; height: 600px"></div>
    <script src="/lib/jquery/dist/jquery.js"></script>
    <script src="/lib/powerbi-client/dist/powerbi.js"></script>    
    <script>
        var config= {
            type: 'report',
            accessToken: '<?=$report->getEmbedToken()?>',
            embedUrl: '<?=$report->embedUrl?>',
            id: '<?=$report->id?>',
            settings: {
                filterPaneEnabled: true,
                navContentPaneEnabled: false
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