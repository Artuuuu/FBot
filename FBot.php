<?php namespace Fajuu;
#
use GuzzleHttp\Client;
use Symfony\Component\Yaml\Yaml;
#
class FBot
{
  #
  #
  public $data;  // what you sent to Facebook   $fbot->message_text(json_encode($fbot->data));
  public $response;  // what you got from Facebook   $fbot->message_text(json_encode($fbot->response));
  private $config;
  private $client;
  #
  #
  public function __construct($config = null)
  {
    #
    
    /* Checks and Verify */
    $this->config = $config or die("Config not found!");
    if(@$_REQUEST["hub_verify_token"] === $this->config["verifyToken"]) echo $_REQUEST["hub_challenge"];
    $this->input = json_decode(file_get_contents("php://input"), true) or die("You are not Facebook.");
    
    /* GuzzleHttp Client */
    $client = new Client([
      "base_uri" => "https://graph.facebook.com/",
      "headers" => [
        "Content-Type" => "application/json",
        "User-Agent" => "FajuuFBot / beta"
      ]
    ]);
    
    /* Variables */
    $this->client = $client;
    $this->persona = null;
    $this->input = $this->input["entry"][0]["messaging"][0];
    $this->sender = $this->input["sender"]["id"];
    $this->recipient = $this->input["recipient"]["id"];
    $this->message = $this->input["message"];
    $this->postback = $this->input["postback"];
    $this->text = $this->input["message"]["text"];
    $this->qr_payload = $this->input["message"]["quick_reply"]["payload"];
    $this->payload = $this->input["postback"]["payload"];
    
    #
  }
  #
  #
  #
  #
  public function lang($words)
  {
    #
    $ldir = $this->config["localeFolder"] or die("localeFolder is not setted!");
    $lang = Yaml::parse(@file_get_contents("{$ldir}/{$this->user("locale")}.yml"))[$this->user("gender")] ?? Yaml::parse(@file_get_contents("{$ldir}/en_US.yml"))[$this->user("gender")];
    foreach($words as $word)
      $lang = $lang[$word];
    preg_match_all("|{(.*)}|", $lang, $find);
    foreach($find[1] as $find)
      $lang = str_replace("{{$find}}", $this->user($find), $lang);
    return $lang;
    #
  }
  #
  #
  #
  #
  public function user($data)
  {
    #
    $this->curl->get("https://graph.facebook.com/{$this->sender}?fields={$data}&access_token={$this->config["accessToken"]}");
    return $this->curl->response->{$data};
    #
  }
  #
  #
  #
  #
  private function send($data)
  {
    #
    $this->data = $this->persona ? array_merge($data, [ "persona_id" => $this->persona ] ) : $data;
    $this->curl->post("https://graph.facebook.com/{$this->config["version"]}/me/messages?access_token={$this->config["accessToken"]}", $this->data);
    $this->response = $this->curl->response;
    #
  }
  #
  #
  #
  public function say($text, $quick_replies = null)
  {
    #
    $this->send(
      [
        "recipient" =>
        [
          "id" => $this->sender
        ],
        "message" => $quick_replies ? array_merge(["text" => $text], ["quick_replies" => $quick_replies]) : ["text" => $text]
      ]
    );
    #
  }
  #
  #
  #
  public function sender_action($type)
  {
    #
    $this->send(
      [
        "recipient" =>
        [
          "id" => $this->sender
        ],
        "sender_action" => $type
      ]
    );
    #
  }
  #
  #
  #
  public function template($type, $more)
  {
    #
    $this->send(
      [
        "recipient" =>
        [
          "id" => $this->sender
        ],
        "message" =>
        [
          "attachment" =>
          [
            "type" => "template",
            "payload" => array_merge(["template_type" => "$type"], $more)
          ]
        ]
      ]
    );
    #
  }
  #
  #
  #
  #
  private function request($data)
  {
    #
    $this->data = $data;
    $this->curl->post("https://graph.facebook.com/{$this->config["version"]}/me/messenger_profile?access_token={$this->config["accessToken"]}", $this->data);
    $this->response = $this->curl->response;
    #
  }
  #
  #
  #
  public function get_started($payload)
  {
    #
    $this->request(
      [
        "get_started" =>
        [
          "payload" => $payload
        ]
      ]
    );
    #
  }
  #
  #
}
