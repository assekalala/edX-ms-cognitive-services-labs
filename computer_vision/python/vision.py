import json
import requests

def analyze_image(image_url):
    region = "YOUR-REGION"
    api_key = "YOUR-SUBSCRIPTION-KEY"

    # Set request headers
    headers = dict()
    headers['Ocp-Apim-Subscription-Key'] = api_key
    headers['Content-Type'] = 'application/json'

    # Set request querystring parameters
    params = {
        'visualFeatures': 'Color,Categories,Tags,Description,ImageType,Faces,Adult',
    }

    data = "{'url': '%s'}" % image_url

    # Make request and process response
    response = requests.request('post', "https://{}.api.cognitive.microsoft.com/vision/v1.0/analyze".format(region), data=data, headers=headers, params=params)

    if response.status_code == 200 or response.status_code == 201:

        if 'content-length' in response.headers and int(response.headers['content-length']) == 0:
            result = None
        elif 'content-type' in response.headers and isinstance(response.headers['content-type'], str):
            if 'application/json' in response.headers['content-type'].lower():
                result = response.json() if response.content else None
            elif 'image' in response.headers['content-type'].lower():
                result = response.content

            # print(result)

            description = result['description']['captions'][0]['text']
            isRacyContent = result['adult']['isRacyContent']
            isAdultContent = result['adult']['isAdultContent']

            print("DESCRIPTION: %s\n" % description)
            print("IS RACY: %s\n" % isRacyContent)
            print("IS ADULT CONTENT: %s" % isAdultContent)

    else:
        print("Error code: %d" % response.status_code)
        print("Message: %s" % response.json())

image_url = input("Enter image url: ")
analyze_image(image_url)
