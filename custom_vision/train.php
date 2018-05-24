<?php
$projects_url = "https://southcentralus.api.cognitive.microsoft.com/customvision/v1.1/Training/projects";
$subscription_key = "YOUR_AZURE_SUBSCRIPTION_KEY";
$training_key = "YOUR_CUSTOM_VISION_TRAINING_KEY";

$projects_request_headers = array();
$projects_request_headers[] = 'Content-Type: application/json';
$projects_request_headers[] = 'Ocp-Apim-Subscription-Key: '.$subscription_key;
$projects_request_headers[] = 'Training-key: '.$training_key;

$projects = json_decode(execute($projects_url, "GET", $projects_request_headers));
// print_r($result);

function execute($url, $method, $headers, $data="") {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);

    if ($method == "POST") {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    $response = curl_exec($curl);
    curl_close ($curl);

    return $response;
  }
?>

<html>
<head>
  <title>Computer Vision</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/dropzone.css" />
  <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  <script src="js/dropzone.js"></script>
</head>
<body>
  <div class="container">
    <div class="row">
      <h3>Custom Vision App</h3>
      <span>Upload images to train</span>
    </div>
    <form enctype="multipart/form-data" encoding='multipart/form-data' class="form" method='post' action='train.php'>
      <div class="form-group">
        <label>Select Project:</label>
        <select name="project_id">
        <?php
            for($i=0; $i<count($projects); $i++) {
                print_r($project[$i]->Id);
                //echo "<option value=".$project[$i]->Id.">".$project[$i]->Name."</option>";
            }
        ?>
        </select>
      </div>
      <div class="form-group">
        <input name="file" type="file" value="choose image">
      </div>
      <div class="row text-right float-right">
        <input type="submit" class="btn btn-primary" value="Submit" />
      </div>
    </form>
  </div>
</body>
</html>
