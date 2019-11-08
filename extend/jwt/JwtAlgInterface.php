<?php

namespace jwt;

interface JwtAlgInterface
{
    public function generateSignature(string $headerStr, string $payloadStr, string $secret) : string;
}