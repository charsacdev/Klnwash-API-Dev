<?php

namespace App\Http\Services\ExpoPushNotification;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

/**
 * Interact with Firebase Cloud Messaging (FCM)
 * @codeCoverageIgnore
 */
class CloudMessagingService{

    /**
     * Basic notification to be used across all platforms.
     *
     * This will contain title and body to be displayed in the notification tray of the device.
     *
     */
    private $notification;

    /**
     * Data message
     *
     * Customer key-value pair of data to by handled within the app
     *
     */
    private $data;

    /**
     * Recipient of the message
     *
     * Topic name or device token to send notification to
     *
     */
    private $to;
    
    private $title;

    private $body;

    private $endpoint = "https://exp.host/--/api/v2/push/send";

    private $response;

    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
    }

    /**
     * Set notification object
     *
     * @param array $data
     * @return $this
     */
    public function setNotification(array $data){
        if (!array_key_exists('title', $data)){
            throw new \Exception("The notification data must contain a `title` key", 1);
        }
        if (!array_key_exists('body', $data)){
            throw new \Exception("The notification data must contain a `body` key", 1);
        }

        $this->notification = $data;
        return $this;
    }

    public function getNotification(){
        return $this->notification;
    }

    /**
     * Set custom data payload
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data){
        $this->data = $data;
        return $this;
    }

    public function getData(){
        return $this->data;
    }

    public function setTitle($title){
        $this->title = $title;
        return $this;
    }

    public function getTitle(){
        return $this->title;
    }

    public function setBody($body){
        $this->body = $body;
        return $this;
    }

    public function getBody(){
        return $this->body;
    }

    /**
     * Set recipient of notification
     *
     * @return $this
     */
    public function setTo($to){
        $this->to = $to;
        return $this;
    }

    /**
     * Get Recipient
     * @return string
     */
    public function getTo(){
        return $this->to;
    }

    /**
     * Send out notification to recipient
     *
     * If argument $data is not null, the method assumes you want to overwrite the data and notification properties
     *
     * This method must be called to actually send out the notification
     *
     * @param array|mixed $data
     */
    public function send(array $data=null){
        if (isset($data) && array_key_exists('notification', $data)){
            $this->setNotification($data['notification']);
        }
        if (isset($data) && array_key_exists('data', $data)){
            $this->setData($data['data']);
        }

        try {
            $response = $this->httpClient->request("POST", $this->endpoint, [
                'json' => [
                    'title' => $this->title,
                    'body' => $this->body,
                    // 'data' => $this->data,
                    'to' => $this->to
                ]
            ]);

            $this->setResponse(json_decode($response->getBody()));
            return $this->getResponse();
        } catch (ConnectException $th) {
            throw $th;
        }
    }

    public function sendToMany(array $data=null){
        if (isset($data) && array_key_exists('notification', $data)){
            $this->setNotification($data['notification']);
        }
        if (isset($data) && array_key_exists('data', $data)){
            $this->setData($data['data']);
        }

        if(count($this->to) == 0){
            return ;
        }

        try {
            $response = $this->httpClient->request("POST", $this->endpoint, [
                'json' => [
                    'notification' => $this->notification,
                    'data' => $this->data,
                    'registration_ids' => $this->to
                ]
            ]);

            $this->setResponse(json_decode($response->getBody()));
            return $this->getResponse();
        } catch (ConnectException $th) {
            throw $th;
        }
    }

    protected function setResponse($response){
        $this->response = $response;
    }

    /**
     * Get response data after sending notification
     *
     * This method should only be called after `send` method has been called
     */
    public function getResponse(){
        if (!isset($this->response)){
            throw new Exception("Unable to get response, ensure that you have called the `send` method");
        }
        return $this->response;
    }
}
