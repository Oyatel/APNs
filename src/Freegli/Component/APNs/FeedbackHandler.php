<?php

namespace Freegli\Component\APNs;

use Freegli\Component\APNs\Exception\ExceptionInterface;

class FeedbackHandler extends BaseHandler
{
    const PRODUCTION_HOST = 'feedback.push.apple.com';
    const SANDBOX_HOST    = 'feedback.sandbox.push.apple.com';
    const PORT            = '2196';

    private $errors;

    public function __construct($connectionFactory, $debug = false)
    {
        parent::__construct($connectionFactory, $debug);

        $this->errors = array();
    }

    /**
     * Get Feedbacks from stream.
     *
     * @return Feedback[]
     */
    public function getFeedbacks()
    {
        return $this->extract($this->fetch());
    }

    /**
     * Get errors from stream.
     *
     * @return Exception[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return boolean
     */
    public function hasErrors()
    {
        return (bool)count($this->errors);
    }

    /**
     * Extract Feedbacks from a binary string.
     *
     * @param string $binaryString
     *
     * @return Feedback[]
     */
    public function extract($binaryString)
    {
        $feedbacks = array();
        foreach (str_split($binaryString, Feedback::LENGTH) as $binaryChunk) {
            try {
                $feedbacks[] = new Feedback($binaryChunk);
            } catch (\Exception $e) {
                $this->errors[] = $e;
            }
        }

        return $feedbacks;
    }

    /**
     * Get binary string from connection.
     *
     * @return string
     */
    public function fetch()
    {
        //TODO do it tuple by tuple?

        return stream_get_contents($this->getResource());
    }
}
