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
    <div id="pages"></div>
    <div id="filters">
        City: <input type="text" id="cityFilter" placeholder="City name..." /><button onclick="filterCity()">Apply</button>
    </div>
    <script src="/lib/jquery/dist/jquery.js"></script>
    <script src="/lib/powerbi-client/dist/powerbi.js"></script>
    <script>
        function filterCity() {
            var report = powerbi.embeds[0];
            var cityFilter = $('#cityFilter').val();
            if(!cityFilter || 0 === cityFilter.length) {
                report.removeFilters();
            }
            else {
                const filter = {
                    $schema: "http://powerbi.com/product/schema#advanced",
                    target: {
                        table: "Store",
                        column: "City"
                    },
                   logicalOperator: "And", 
                   conditions: [ { "operator": "Contains", "value": cityFilter } ]
                };
                report.setFilters([filter]);
            }
        }
        function navigate(pageName) {
            var report = powerbi.embeds[0];
            report.getPages().then(function(pages){
                pages.some(page => {
                    if (page.name === pageName) {
                        page.setActive();
                        return true;
                    }
                });
            });
        } 
    </script>    
    <script>
        var config= {
            type: 'report',
            accessToken: '<?=$report->getEmbedToken()?>',
            embedUrl: '<?=$report->embedUrl?>',
            id: '<?=$report->id?>',
            settings: {
                filterPaneEnabled: false,
                navContentPaneEnabled: false
            }
        };
        $(function(){
            var reportContainer = $('#reportContainer')[0];
            var report = powerbi.embed(reportContainer, config);
            report.off("loaded");
            report.on("loaded", function() {
                report.getPages().then(function(pages){
                    pages.forEach(function(page) {
                        $('#pages').append('<button onclick="navigate(\''+page.name+'\')">'+page.displayName+'</button>');
                    });
                });
            });
            $('#print').click(function(){
                var report = powerbi.embeds[0];
                report.print()
            });
        });
    </script>
</body>