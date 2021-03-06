<?php

namespace Freegli\Component\APNs;

use Freegli\Component\APNs\Exception\LengthException;
use Freegli\Component\APNs\Exception\ConvertException;
/**
 * Error-response packet.
 *
 * @link http://developer.apple.com/library/ios/#documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/CommunicatingWIthAPS/CommunicatingWIthAPS.html
 */
class ErrorResponse
{
    const LENGTH = 6;

    const STATUS_CODE_OK                    = 0;
    const STATUS_CODE_PROCESSING_ERROR      = 1;
    const STATUS_CODE_MISSING_DEVICE_TOKEN  = 2;
    const STATUS_CODE_MISSING_TOPIC         = 3;
    const STATUS_CODE_MISSING_PAYLOAD       = 4;
    const STATUS_CODE_INVALID_TOKEN_SIZE    = 5;
    const STATUS_CODE_INVALID_TOPIC_SIZE    = 6;
    const STATUS_CODE_INVALID_PAYLOAD_SIZE  = 7;
    const STATUS_CODE_INVALID_TOKEN         = 8;

    private $errors = array(
        self::STATUS_CODE_PROCESSING_ERROR      => 'Processing error',
        self::STATUS_CODE_MISSING_DEVICE_TOKEN  => 'Missing device token',
        self::STATUS_CODE_MISSING_TOPIC         => 'Missing topic',
        self::STATUS_CODE_MISSING_PAYLOAD       => 'Missing Payload',
        self::STATUS_CODE_INVALID_TOKEN_SIZE    => 'Invalid token size',
        self::STATUS_CODE_INVALID_TOPIC_SIZE    => 'Invalid topic size',
        self::STATUS_CODE_INVALID_PAYLOAD_SIZE  => 'Invalid payload size',
        self::STATUS_CODE_INVALID_TOKEN         => 'Invalid token',
    );

    private $command;
    private $statusCode;
    private $identifier;

    public function __construct($binaryString)
    {
        if (strlen($binaryString) != self::LENGTH) {
            throw new LengthException();
        }
        try {
            $error_pack = unpack('Ccommand/Cstatus_code/Nidentifier', $binaryString);
            
            $this->command = $error_pack['command'];
            $this->statusCode = $error_pack['status_code'];
            $this->identifier = $error_pack['identifier'];
        } catch (\Exception $e) {
            throw new ConvertException('Unable to convert binary string to '.__CLASS__ . ": " . $e->getMessage(), null, $e);
        }
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getStatusCodeMessage()
    {
        return $this->errors[$this->statusCode];
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}
