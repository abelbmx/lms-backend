<?php


//namespace Transbank\Webpay\WebpayPlus\Exceptions;

//include( APPPATH.'/third_party/libwebpay/Exceptions/TransactionStatusException.php');
//use Transbank\Webpay\Exceptions\WebpayException;



class TransactionCaptureException extends WebpayException
{
    public function __construct($message = self::DEFAULT_MESSAGE, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
