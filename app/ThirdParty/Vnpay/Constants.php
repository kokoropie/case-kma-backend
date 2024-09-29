<?php 
namespace App\ThirdParty\Vnpay;

class Constants
{
    const PATH_PAYMENT = "/paymentv2/vpcpay.html";
    const PREFIX = "vnp_";
    const COMMAND_PAY = "pay";
    const COMMAND_PAY_AND_CREATE = "pay_and_create";
    const COMMAND_TOKEN_PAY = "token_pay";
    const CURRENCY = "VND";
    const ORDER_TYPE = "other";
    const LOCALE = "en";
    const BANK_CODE_VNPAYQR = "VNPAYQR";
    const BANK_CODE_VNBANK = "VNBANK";
    const BANK_CODE_INTCARD = "INTCARD";
    const RESPONSE_CODE_SUCCESS = "00";
}
