<?php

namespace Freegli\Component\APNs;

use Freegli\Component\APNs\Exception\ExceptionInterface;
use Freegli\Component\APNs\Exception\LengthException;
use Freegli\Component\APNs\Exception\WriteException;

class NotificationHandler extends BaseHandler
{
    const PRODUCTION_HOST = 'gateway.push.apple.com';
    const SANDBOX_HOST    = 'gateway.sandbox.push.apple.com';
    const PORT            = '2195';

    public function send(Notification $pushNotification)
    {
        $binaryPushNotification = $pushNotification->toBinary();

        try {
            try {
                $written = fwrite($this->getResource(), $binaryPushNotification);
            } catch (\Exception $e) {
                throw new WriteException('Unable to write into resource', 0, $e);
            }
            if ($written === false) {
                throw new WriteException('Unable to write into resource');
            }
            if ($written != strlen($binaryPushNotification)) {
                throw new LengthException('Partialy write into resource');
            }
        } catch (ExceptionInterface $e) {
            $this->closeResource();

            throw $e;
        }

        return true;
    }

    /**
     * Get errors.
     *
     * @return ErrorResponse[]
     */
    public function getErrors()
    {
        $errors = array();
        $errorsStrs = $this->fetchErrors();
        foreach ($errorsStrs as $binaryChunk) {
            try {
                $errors[] = new ErrorResponse($binaryChunk);
            } catch (\Exception $e) {
                //do nothing
                error_log("Got exception on parting error response: " . $e->getMessage());
            }
        }

        return $errors;
    }

    /**
     * Get binary string from resource.
     *
     * @return string[]
     */
    private function fetchErrors()
    {
        $errorBinStrings = array();
        $binaryString = fread($this->getResource(), 6);
    
        if ($binaryString) {
            $errorBinStrings[] = $binaryString;
        }
        return $errorBinStrings;
    }
}
