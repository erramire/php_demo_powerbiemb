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

   <!-- <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="https://community.powerbi.com/t5/Developer/Auto-Generate-Embed-Token-using-Javascript-and-PHP/m-p/./dist/powerbi.js"></script> -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/powerbi-client/2.15.1/powerbi.min.js" integrity="sha512-OWIl8Xrlo8yQjWN5LcMz5SIgNnzcJqeelChqPMIeQGnEFJ4m1fWWn668AEXBrKlsuVbvDebTUJGLRCtRCCiFkg==" crossorigin="anonymous"></script>
</head>
<body>
    <h1>Hola, <?php echo $_SESSION['username']; ?></h1>
    <a href="logout.php">Logout</a>
    

<div id="reportContainer" style="height: 1400px; width: 1000px;"></div>

<?php
    // All the values used below can be generated at https://app.powerbi.com/embedsetup/appownsdata
    
    /* Get oauth2 token using a POST request */
    $curlPostToken = curl_init();


    curl_setopt_array($curlPostToken, array(
        CURLOPT_URL => "https://login.windows.net/common/oauth2/token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => array(
            'grant_type' => 'password',
            'scope' => 'openid',
            'resource' => 'https://analysis.windows.net/powerbi/api',
            
            // Make changes Start
            'client_id' => 'c14ac973-d088-4c19-8c4a-fa10304985ed', // Registered App Application ID
            'username' => 'admin@Ramirez068.onmicrosoft.com', // for example john.doe@yourdomain.com
            'password' => 'T78fsryY1', // Azure password for above user
            // Make changes End
        )
    ));

    $tokenResponse = curl_exec($curlPostToken);
    $tokenError = curl_error($curlPostToken);

    echo $tokenError;
    //curl_close($curlPostToken);

    // decode result, and store the access_token in $embeddedToken variable:
    $tokenResult = json_decode($tokenResponse, true);
    $token = $tokenResult["access_token"];
    $embeddedToken = "Bearer "  . ' ' .  $token;

    /* Use the token to get an embedded URL using a GET request */
    $curlGetUrl = curl_init();
    curl_setopt_array($curlGetUrl, array(
        
        // Make changes Start
        CURLOPT_URL => "https://api.powerbi.com/v1.0/myorg/groups/09931810-ca17-4659-859f-fa293f35dc00/reports/", // Enter your Workspace ID, aka Group ID
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
            "Authorization: $embeddedToken",
            "Cache-Control: no-cache",
        ),
    ));

    $embedResponse = curl_exec($curlGetUrl);
    $embedError = curl_error($curlGetUrl);
    //curl_close($$curlGetUrl);
    if ($embedError) {
        echo "cURL Error #:" . $embedError;
        } else {
            $embedResponse = json_decode($embedResponse, true);
            $embedUrl = $embedResponse['value'][0]['embedUrl']; // this is just taking the first value. you need logic to find the report you actually want to embed. This EmbedUrl needs to match the corresponding ReportId you later use in the JavaScript.
        }
?>



<script>
    // Get models. models contains enums that can be used.
    var models = window['powerbi-client'].models;

    var embedConfiguration= {
        type: 'report',
        
        // Make changes Start
        id: '3b49b8fc-1b3c-4d9d-84d1-93ab012bf600', // the report ID
        // Make changes End
        
        embedUrl: "<?php echo $embedUrl ?>",
        accessToken: "<?php echo $token; ?>",
        permissions: models.Permissions.Read,
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
}else{
    header("Location: index.php");
    exit();
}
?>
