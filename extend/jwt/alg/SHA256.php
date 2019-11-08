<?php

namespace jwt\alg;

use jwt\JwtAlgInterface;

class SHA256 implements JwtAlgInterface
{
    public function generateSignature(string $headerStr, string $payloadStr, string $secret): string
    {
        return hash('sha256', $headerStr . $payloadStr . $secret);
    }
}