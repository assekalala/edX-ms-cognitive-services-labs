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
      <h3>Traffic Cam App</h3>
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

  $url = "https://northeurope.api.cognitive.microsoft.com/vision/v1.0/analyze?visualFeatures=Color,Categories,Tags,Description,ImageType,Faces,Adult";

  $request_headers = array();
  $request_headers[] = 'Content-Type: application/octet-stream';
  $request_headers[] = 'Ocp-Apim-Subscription-Key: 6cb2c7b3135240368c7a28e158235dd9';

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

  if (in_array('car', $result->description->tags)) {

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
    echo "CATEGORIES <br>$categories<br /><br />";

    if ($faces != "None") {
      echo "FACES: <br> $faces<br /><br />";

      // Call the FACE API and check emotion
      $face_api_url = "https://northeurope.api.cognitive.microsoft.com/face/v1.0/detect?returnFaceId=true&returnFaceLandmarks=true&returnFaceAttributes=emotion";

      $face_api_request_headers = array();
      $face_api_request_headers[] = 'Content-Type: application/octet-stream';
      $face_api_request_headers[] = 'Ocp-Apim-Subscription-Key: YOUR_AZURE_SUBSCRIPTION_KEY';

      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $face_api_url);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $face_api_request_headers);
      curl_setopt($curl, CURLOPT_TIMEOUT, 30);
      curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $image_data);
      $response = curl_exec($curl);
      curl_close ($curl);

      // print_r($response);
    }

    // OCR CHECK

    $ocr_url = "https://northeurope.api.cognitive.microsoft.com/vision/v1.0/ocr?language=en&detectOrientation=true";
    $ocr_request_headers = array();
    $ocr_request_headers[] = 'Content-Type: application/octet-stream';
    $ocr_request_headers[] = 'Ocp-Apim-Subscription-Key: YOUR_AZURE_SUBSCRIPTION_KEY';

    $ocr_response = json_decode(execute($ocr_url, $ocr_request_headers, $image_data));
    // print_r($ocr_response);

    if (isset($ocr_response->regions) && count($ocr_response->regions) > 0) {
      echo "TEXT IN IMAGE: <br />";
      $text = "";
      for ($i = 0; $i < count($ocr_response->regions); $i++) {
        for($x = 0; $x < count($ocr_response->regions[$i]->lines); $x++) {
          for($y = 0; $y < count($ocr_response->regions[$i]->lines[$x]->words); $y++) {
            $text .= $ocr_response->regions[$i]->lines[$x]->words[$y]->text;
          }
        }
      }

      echo $text ."<br /><br />";
    } else {
      echo "Not text detected in image<br /><br />";
    }

    // GET THUMBNAIL
    $img_response = get_thumbnail($image_data);

    // A DELIMITER FOR EOL CHARACTERS
    $dlm = "\r\n";

    // FIND THE IMAGE
    $arr = explode($dlm, $img_response);
    $img = end($arr);

    // STORE THE IMAGE ON THE SERVER AND SEND AN IMAGE TAG TO THE BROWSER
    file_put_contents('thumbnail.jpg', $img);
    echo "THUMBNAIL:<br />";
    echo '<img src="thumbnail.jpg" />';
  } else {
    echo "Image doesn't contain a car";
  }

  echo "</div>";
}

function get_thumbnail($data) {
  $get_thumbnail_url = "https://northeurope.api.cognitive.microsoft.com/vision/v1.0/generateThumbnail?width=300&height=300&smartCropping=true";
  $request_headers = array();
  $request_headers[] = 'Content-Type: application/octet-stream';
  $request_headers[] = 'Ocp-Apim-Subscription-Key: YOUR_AZURE_SUBSCRIPTION_KEY';

  return execute($get_thumbnail_url, $request_headers, $data);


}

function execute($url, $headers, $data) {
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_TIMEOUT, 30);
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
  $response = curl_exec($curl);
  curl_close ($curl);

  return $response;
}
?>
  </div>
</body>
</html>
