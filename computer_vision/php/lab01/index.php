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
      <h3>Developing AI Vision Apps with Microsoft Cognitive Services</h3>
      <span>Experiment with Microsoft Cognitive Vision Services and APIs</span>
    </div>
    <form enctype="multipart/form-data" encoding='multipart/form-data' class="form" method='post' action='index.php'>
      <div class="form-group">
        <input name="file" type="file" value="choose image">
      </div>
      <div class="row text-right float-right">
        <input type="submit" class="btn btn-primary" value="Analyze" />
      </div>
    </form>


<?php
if ( isset($_FILES['file']) ) {
  $uploaded_file = $_FILES["file"]["tmp_name"];
  $image_type = $_FILES["file"]["type"];

  $image_data = file_get_contents( $uploaded_file );
  $region = "YOUR_REGION";

  $url = sprinf("https://%s.api.cognitive.microsoft.com/vision/v1.0/analyze?visualFeatures=Color,Categories,Tags,Description,ImageType,Faces,Adult", $region);

  $request_headers = array();
  $request_headers[] = 'Content-Type: application/octet-stream';
  $request_headers[] = 'Ocp-Apim-Subscription-Key: YOUR-SUBSCRIPTION-KEY';

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
  curl_setopt($curl, CURLOPT_TIMEOUT, 30);
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $image_data);
  $response = curl_exec($curl);
  curl_close ($curl);

  //print_r($response);

  // echo "<img src='$uploaded_file.$image_type' /></br /><br />";
  $result = json_decode($response);
  $description = $result->description->captions[0]->text;
  $tags = "";
  for($i = 0; $i < count($result->tags); $i++) {
    $tags .= $result->tags[$i]->name .",";
  }

  $categories = "";

  for($i = 0; $i < count($result->categories); $i++) {
    // var_dump($result->categories[$i]);
    $categories .=  $result->categories[$i]->name;
  }


  $dominantColorForeground = $result->color->dominantColorForeground;
  $dominantColorBackground = $result->color->dominantColorBackground;
  $accentColor = $result->color->accentColor;
  $isBwImg = ($result->color->isBwImg)? "True" : "False";

  $isRacyContent = $result->adult->isRacyContent? "True" : "False";
  $isAdultContent = ($result->adult->isAdultContent)? "True" : "False";

  $faces = (count($result->faces) > 0)? count($result->faces) : "None";

  echo "<div class='row'>";
  echo "DESCRIPTION: <br />$description<br /><br />";

  echo "TAGS <br />$tags<br /><br />";

  echo "COLORS <br />";
  echo "Dorminat Foreground Color:  $dominantColorForeground<br />";
  echo "Dorminat Background Color:  $dominantColorBackground<br />";
  echo "Accent Color:  #$accentColor<br />";
  echo "Is Black & White: $isBwImg<br /><br />";

  echo "IS RACY CONTENT <br />$isRacyContent<br /><br />";
  echo "IS ADULT CONTENT <br />$isAdultContent<br /><br />";

  echo "FACES: <br> $faces<br /><br />";
  echo "</div>";

  echo "CATEGORIES <br>$categories";
}
?>
  </div>
</body>
</html>
