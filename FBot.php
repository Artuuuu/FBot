<?php
namespace Fajuu;
use \Curl\Curl;
#
class FBot
{
  #
  private $config;
  private $curl;
  #
  #
  public function __construct($config = null)
  {
    /* Config Check */
    if(!$config): die('Config not found!'); exit; endif;
    
    /* Token Verify */
    if(@$_REQUEST['hub_verify_token'] === $config['verifyToken'] ): echo $_REQUEST['hub_challenge']; exit; endif;
    
    /* Input Check */
    $input = json_decode(file_get_contents('php://input'), true);
    if(!$input): die("You're not Facebook."); exit; endif;
    $input = $input['entry'][0]['messaging'][0];
    
    /* Curl Library */
    $curl = new Curl();
    $curl->setHeader('Content-Type', 'application/json');
    $curl->setHeader('User-Agent', 'FajuuFBot / beta');
    
    /* Variables */
    $this->curl = $curl;
    $this->config = $config;
    $this->persona = null;
    $this->sender = $input['sender']['id'];
    $this->recipient = $input['recipient']['id'];
    $this->message = $input['message'];
    $this->postback = $input['postback'];
    $this->text = $input['message']['text'];
    $this->qr_payload = $input['message']['quick_reply']['payload'];
    $this->payload = $input['postback']['payload'];
    
  }
  #
  #
  #
  public function user($data)
  {
    $this->curl->get("https://graph.facebook.com/{$this->sender}?fields={$data}&access_token={$this->config['accessToken']}");
    return $this->curl->response->{$data};
  }
  #
  #
  #
  private function send($data = null){
    $this->curl->post("https://graph.facebook.com/{$this->config['version']}/me/messages?access_token={$this->config['accessToken']}", $this->persona ? array_merge($data, [ 'persona_id' => $this->persona ] ) : $data);
    $this->response = $this->curl->response;
  }
  #
  #
  public function message_text($message = null)
  {
    $this->send( ['recipient' => [ 'id' => $this->sender ], 'message' => [ 'text' => $message ] ] );
  }
  #
  public function quick_replies($text = null, $quick_replies = null)
  {
    $this->send( ['recipient' => [ 'id' => $this->sender ], 'message' => [ 'text' => $text, 'quick_replies' => $quick_replies ] ] );
  }
  #
  public function sender_action($type = null)
  {
    $this->send( ['recipient' => [ 'id' => $this->sender ], 'sender_action' => $type ] );
  }
  #
  #
  #
  private function request($data = null)
  {
    $this->curl->post("https://graph.facebook.com/{$this->config['version']}/me/messenger_profile?access_token={$this->config['accessToken']}", $data);
    $this->response = $this->curl->response;
  }
  #
  #
  public function get_started($payload = null)
  {
    $this->request( ['get_started' => [ 'payload' => $payload ] ] );
  }
  #
  #
  #
}
