<?php

require "Request.php";

$access_token = "demo_EAAFFB4XLW1cBACjMZCiotn5TO8qoIqSZA7YGgJp0LeDN3FzuFX8sPZCA2inCgUZAwKj9ks7";
$hub_secret = "secret";

if (isset($_GET['hub_verify_token']) && $_GET['hub_verify_token'] === $hub_secret) {
    echo $_GET['hub_challenge'];
    exit;
}

$fb_message = json_decode(file_get_contents(), true); 
if (isset($fb_message['entry'])) {

    $sender_id = 0;
    $message_text = "";

    if (count($fb_message['entry']) > 0) {

      if (isset($fb_message['entry'][0]['messaging']) && count($fb_message['entry'][0]['messaging']) > 0) {
        
        $sender_id = $fb_message['entry'][0]['messaging'][0]['sender']['id'];
        $message_text = $fb_message['entry'][0]['messaging'][0]['message']['text'];

        if ($sender_id !== 0 && strlen(trim($message_text)) > 0) {
          
          $request = new Request("https://api.duckduckgo.com/?q=" . $message_text . "&format=json&pretty=1&no_redirect=1&no_html=1");
          $answer_data = json_decode($request->result(), true);

          $instant_text = "Sorry, I can't find any information!";
          $instant_image = "";

          if (isset($answer_data['Redirect']) && strlen(trim($answer_data['Redirect'])) > 0) {
            $instant_text = $answer_data['Redirect'];
          } else if (isset($answer_data['AbstractText']) && strlen(trim($answer_data['AbstractText'])) > 0) {
            $instant_text = $answer_data['AbstractText'];
          } else if (isset($answer_data['RelatedTopics']) && count($answer_data['RelatedTopics']) > 0) {
            $instant_text = $answer_data['RelatedTopics'][0]['Text'];
          }

          if (isset($answer_data['Image']) && strlen(trim($answer_data['Image'])) > 0) {
            $instant_image = $answer_data['Image'];
          } else if (isset($answer_data['RelatedTopics']) && count($answer_data['RelatedTopics']) > 0) {
            $instant_image = $answer_data['RelatedTopics'][0]['Icon']['URL'];
          }

          if (strlen(trim($instant_image)) > 0) {

            $data = [
                'recipient' => ['id' => $sender_id],
                'message' => [
                    'attachment' => [
                        'type' => 'image',
                        'payload' => [
                            'url' => $instant_image,
                            'is_reusable' => true
                        ]
                    ]
                ]
            ];

            $request = new Request('https://graph.facebook.com/v2.6/me/messages?access_token=' . access_token);
            $request->option(CURLOPT_POSTFIELDS, json_encode($data));
            $request->send();


            
            $data = [
                'recipient' => ['id' => $sender_id],
                'message' => ['text' => $instant_text]
            ];

            
            $request = new Request('https://graph.facebook.com/v2.6/me/messages?access_token=' . access_token);
            $request->option(CURLOPT_POSTFIELDS, json_encode($data));
            $request->send();


          }
        }
      }
    }
}
