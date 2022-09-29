<?php
session_start();
if (isset($_SESSION['id']) && isset($_SESSION['username'])) {
?>

    <!DOCTYPE html>
    <html>

    <head>
        <title>Reporte</title>
        <link rel="stylesheet" type="text/css" href="login.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/powerbi-client/2.15.1/powerbi.min.js" integrity="sha512-OWIl8Xrlo8yQjWN5LcMz5SIgNnzcJqeelChqPMIeQGnEFJ4m1fWWn668AEXBrKlsuVbvDebTUJGLRCtRCCiFkg==" crossorigin="anonymous"></script>
    </head>

    <body>
        <h1>Hola, <?php echo $_SESSION['username']; ?></h1>
        <a href="logout.php">Logout</a>


        <div id="reportContainer" style="height: 1400px; width: 1000px;"></div>

        <?php        

        /* Get oauth2 token using a POST request */
        $curlPostToken = curl_init();

        $tenantId = 'd00023c8-9bcd-4a89-a43f-13b6661dad71';
        $clientId='430a311c-b9e2-4a3f-a241-1829036cad5a';
        $clientSecretId='ZnO8Q~RsneZG3TRXiIf.9TU4lSzfmL.dfaIc7aS9';
        curl_setopt_array($curlPostToken, array(
            CURLOPT_URL => "https://login.microsoftonline.com/$tenantId/oauth2/token",            
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => array(
                'grant_type' => 'client_credentials',
                'scope' => '.default',
                'resource' => 'https://analysis.windows.net/powerbi/api',
                'tenant_id' => $tenantId,                
                'client_id' => $clientId, // Registered App Application ID                
                'client_secret' => $clientSecretId,                
            )
        ));

        $tokenResponse = curl_exec($curlPostToken);
        $tokenError = curl_error($curlPostToken);

        echo $tokenError;        
        // decode result, and store the access_token in $embeddedToken variable:
        $tokenResult = json_decode($tokenResponse, true);
        $token = $tokenResult["access_token"];
        $bearerToken = "Bearer "  . ' ' .  $token;
        $ReportId = '31dd7951-3874-452c-8d0a-93c37e7a5e2b';
        $workspace = '09931810-ca17-4659-859f-fa293f35dc00';
        $datasetId ="27b71be3-34d0-4b1c-8cbf-69a14cbeb582";
        /* Use the token to get an embedded URL using a GET request */
        
        $post_params = array(
            "datasets" => Array(
                Array('id'=>$datasetId),                
                
            ),
            "reports" => Array(                
                Array('id'=>$ReportId),

            ),
            "accessLevel"=>"View",
            "identities" => Array(                
                 Array(
                     'username'=>$clientId,
                     'roles'=> array(
                         $_SESSION['role']
                     ),
                     "datasets" => Array(
                        $datasetId                                        
                     ),
                 ),
             ),
        );

        $payload = json_encode($post_params);        
        
        //Get embeded token

        $curlGetEmbededToken = curl_init();

        curl_setopt_array($curlGetEmbededToken, array(

            // Make changes Start
            CURLOPT_URL => "https://api.powerbi.com/v1.0/myorg/GenerateToken", // Enter your Workspace ID, aka Group ID
            // Make changes End

            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: $bearerToken",
            ),
            CURLOPT_POSTFIELDS => $payload

        ));

        $embedResponse = curl_exec($curlGetEmbededToken);
        $embedError = curl_error($curlGetEmbededToken);
                
        echo $embedError;        

        if ($embedError) {
            echo "cURL Error #:" . $embedError;
        } else {
            $embedResponse = json_decode($embedResponse, true);
            $embededToken = $embedResponse['token'];            
        }

        //get emebede url
        $curlGetUrl = curl_init();
        curl_setopt_array($curlGetUrl, array(

            // Make changes Start
            CURLOPT_URL => "https://api.powerbi.com/v1.0/myorg/groups/$workspace/reports/$ReportId", // Enter your Workspace ID, aka Group ID
            // Make changes End

            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: $bearerToken",
            ),

        ));

        $embedResponse = curl_exec($curlGetUrl);
        $embedError = curl_error($curlGetUrl);        
        echo $embedError;
        //curl_close($$curlGetUrl);
        if ($embedError) {
            echo "cURL Error #:" . $embedError;
        } else {
            $embedResponse = json_decode($embedResponse, true);
            $embedUrl = $embedResponse['embedUrl'];            
        }

        ?>
        <script>
            // Get models. models contains enums that can be used.
            var models = window['powerbi-client'].models;

            var embedConfiguration = {
                type: 'report',                                
                tokenType: models.TokenType.Embed,
                embedUrl: '<?php echo $embedUrl ?>',
                accessToken: '<?php echo $embededToken; ?>',
                id: '<?php echo $ReportId ?>', 
                permissions: models.Permissions.All,
                settings: {
                    filterPaneEnabled: false,
                    navContentPaneEnabled: false
                }
            };

            var $reportContainer = $('#reportContainer');
            var report = powerbi.embed($reportContainer.get(0), embedConfiguration);
        </script>
    </body>

    </html>

<?php
} else {
    header("Location: index.php");
    exit();
}
?>