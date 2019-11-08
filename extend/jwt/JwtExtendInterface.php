<?php

namespace jwt;

interface JwtExtendInterface
{
    function beforeGetPayload(JWT $Jwt);
}