<?php namespace Fajuu;
#
use GuzzleHttp\Client;
use Symfony\Component\Yaml\Yaml;
#
class FBot
{
  #
  #
  public $data;
  public $response;
  private $config;
  private $client;
  #
  #
  public function __construct($config = null)
  {
    #
    
    /* Checks and Verify */
    $this->config = $config or die("Config not found!");
    if(@$_REQUEST["hub_verify_token"] === $this->config["verify_token"]) die($_REQUEST["hub_challenge"]);
    $this->input = json_decode(file_get_contents("php://input"), true) or die("You are not Facebook.");
    
    /* GuzzleHttp Client */
    $this->client = new Client([
      "base_uri" => "https://graph.facebook.com/",
      'exceptions' => false,
      "headers" => [
        "Content-Type" => "application/json",
        "User-Agent" => "FajuuFBot / dev"
      ],
    ]);
    
    /* Variables */
    $this->persona = null;
    $input = $this->input["entry"][0]["messaging"][0];
    $this->sender = @$input["sender"]["id"];
    $this->recipient = @$input["recipient"]["id"];

    $this->received = json_decode(json_encode([
      "text" => @$input["message"]["text"],
      "payload" => @$input["postback"]["payload"] ?? @$input["message"]["quick_reply"]["payload"],
      "message" => @$input["message"],
      "postback" => @$input["postback"],
      "quick_reply" => @$input["message"]["quick_reply"],
      "is_echo" => (boolean)@$input["message"]["is_echo"],
      "is_read" => (boolean)@$input["read"],
      "is_delivery" => (boolean)@$input["delivery"],
    ]), false);

    $this->allow = (!@$this->received->message->is_echo and @$this->received->message) or @$this->received->postback;
    #
  }
  #
  #
  #
  #
  public function lang($words)
  {
    #
    $ldir = $this->config["locale_folder"] or die("locale_folder is not setted!");
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
    $this->response = $this->client->request("GET", "{$this->sender}?fields={$data}&access_token={$this->config["access_token"]}");
    return json_decode($this->response->getBody(), true)[$data];
    #
  }
  #
  #
  #
  #
  private function request($data)
  {
    #
    $this->data = $this->persona ? array_merge($data, [ "persona_id" => $this->persona ] ) : $data;
    if($this->allow) $this->response = $this->client->request("POST", "{$this->config["version"]}/me/messages?access_token={$this->config["access_token"]}", ["json" => $this->data]);
    #
  }
  #
  #
  #
  public function say($text, $quick_replies = null)
  {
    #
    $this->request(
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
    $this->request(
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
    $this->request(
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
}
