<?php

namespace Freegli\Component\APNs;

use Freegli\Component\APNs\Exception\ConvertException;

/**
 * Based on enhanced notification format.
 *
 * @link http://developer.apple.com/library/ios/#documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/CommunicatingWIthAPS/CommunicatingWIthAPS.html
 */
class Notification
{
    protected $command;
    protected $identifier;
    protected $expiry;
    protected $tokenLength;
    protected $deviceToken;
    protected $payloadLength;
    protected $payload;

    public function  __construct()
    {
        //set default values
        $this->command    = 1;
        $this->identifier = 1;
        $this->expiry     = new \DateTime('+12 hours');
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function setExpiry(\DateTime $expiry)
    {
        $this->expiry = $expiry;
    }

    public function setDeviceToken($deviceToken)
    {
        $this->deviceToken = $deviceToken;
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Format push notification to binary.
     *
     * @return string Binary push notification
     *
     * @throws ConvertException
     */
    public function toBinary()
    {
        $payload = $this->formatPayload();
        error_log("Payload is: " . $payload);
        $payload_length = strlen($payload);
        $token_length = strlen($this->deviceToken);
        if (($token_length % 2) == 1) {
            throw new ConvertException('Invalid token length');
        }
        $binary_token_length = $token_length / 2;

        try {
            $bin = // new: Command "1"
                pack("C", $this->command) 
                // new: Identifier "1111"
                . pack("N", $this->identifier) 
                // new: Expiry
                . pack("N", $this->expiry->format('U'))
                // old 
                . chr(0) . chr($binary_token_length) . pack('H*', str_replace(' ', '', $this->deviceToken)) . chr(0) . chr(strlen($payload)) . $payload;
            return $bin;
        } catch (\Exception $e) {
            throw new ConvertException('Unable to convert to binary', null, $e);
        }
    }

    /**
     * JSON encodes payload.
     *
     * @return string
     */
    protected function formatPayload()
    {
        //TODO handle error
        return json_encode($this->payload);
    }
}
