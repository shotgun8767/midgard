<?php

namespace jwt\alg;

use jwt\JwtAlgInterface;

class md5 implements JwtAlgInterface
{
    public function generateSignature(string $headerStr, string $payloadStr, string $secret) : string
    {
        return md5($headerStr . $payloadStr . $secret);
    }
}