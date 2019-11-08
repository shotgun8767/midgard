<?php

namespace jwt;

use Exception;
use jwt\exception\{AlgFileException, SignatureException, TokenException};

/**
 * Class JWT
 * Json Web Token
 * @package jwt
 * @version 2.0
 * @author shotgun8767
 * @example
 *
 */
class JWT
{
    /**
     * name of function which generating signature
     */
    const GENERATE_SIGNATURE_FUNC_NAME = 'generateSignature';

    /**
     * JWT header, contains alg(algorithm) and typ(type)
     * @var array
     */
    protected $header = [
        'alg' => 'md5',
        'typ' => 'JWT'
    ];

    /**
     * JWT payload, contains user's information
     * @var array
     */
    protected $payload = [];

    /**
     * header encrypted string, encryption: json + base64
     * @var string|null
     */
    protected $headerStr;

    /**
     * payload encrypted string, encryption: json + base64
     * @var string|null
     */
    protected $payloadStr;

    /**
     * signature of JWT, encryption based on 'alg' in header
     * @var string|null
     */
    protected $signature;

    /**
     * A secret key for generating and validating signature
     * @var string
     */
    protected $secret = 'JWT';

    /**
     * the keys hidden when payload is gotten externally
     * @var array
     */
    protected $hidden = [];

    /**
     * JWT extends object
     * @var array
     */
    protected $extends = [];

    /**
     * @var bool
     */
    protected $extendOn = false;

    /**\
     * JWT constructor.
     * @param string|null $token
     * @throws TokenException
     */
    public function __construct(?string $token = null)
    {
        if (!is_null($token)) {
            $ex = explode('.', $token);
            if (count($ex) < 3) {
                throw new TokenException('fail to parse token string!');
            }
            $this->headerStr    = $ex[0];
            $this->payloadStr   = $ex[1];
            $this->signature    = $ex[2];

            $this->header = json_decode(base64_decode($this->headerStr), true);
            $this->payload = json_decode(base64_decode($this->payloadStr), true);
        }
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        array_unshift($arguments, $this);
        foreach ($this->extends as $extend) {
            if (is_object($extend) && method_exists($extend, $name)) {
                return call_user_func_array([$extend, $name], $arguments);
            }
        }
        return $this;
    }

    /**
     * get token string
     * @return string
     * @throws AlgFileException
     */
    public function getToken()
    {
        $this->headerStr    = base64_encode(json_encode($this->header));
        $this->payloadStr   = base64_encode(json_encode($this->payload));
        $this->signature    = $this->getSignature();

        return "{$this->headerStr}.{$this->payloadStr}.{$this->signature}";
    }

    /**
     * set header algorithm
     * @param string $alg
     * @return $this
     */
    public function setAlg(string $alg) : self
    {
        $this->header['alg'] = $alg;
        return $this;
    }

    /**
     * get header algorithm
     * @return string
     */
    public function getAlg() : string
    {
        return $this->header['alg'];
    }

    /**
     * set secret
     * @param string $secret
     * @return JWT
     */
    public function setSecret(string $secret) : self
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * set payload, cover or append
     * @param array $u
     * @param bool $cover
     * @return $this
     */
    public function setPayload(array $u, bool $cover = false) : self
    {
        $this->payload = $cover ? array_merge($this->payload, $u) : array_merge($u, $this->payload);

        return $this;
    }

    /**
     * clear payload
     * @return $this
     */
    public function clearPayload()
    {
        $this->payload = [];

        return $this;
    }

    /**
     * @return array|mixed
     */
    public function getPayload() : array
    {
        # before getting payload
        $this->extendOn = true;
        foreach ($this->extends as $extend) {
            if (is_object($extend) && method_exists($extend, 'beforeGetPayload')) {
                $extend->beforeGetPayload($this);
            }
        }
        $this->extendOn = false;

        return array_diff_key($this->payload, array_flip($this->hidden));
    }

    /**
     * set hidden keys
     * @param array $u
     * @return JWT
     */
    public function hidden(array $u) : self
    {
        $this->hidden = array_merge($this->hidden, $u);
        return $this;
    }

    /**
     * to verify JWT, false returned if it's invalid
     * @return bool
     * @throws AlgFileException
     * @throws SignatureException
     */
    public function validate() : bool
    {
        if (!$this->signature) {
            throw new SignatureException('Signature has not been generated yet.');
        }

        return $this->signature == $this->getSignature() ? true : false;
    }

    /**
     * @param object $ext
     * @return JWT
     */
    public function loadExtend(object $ext) : self
    {
        $this->extends[] = $ext;
        return $this;
    }

    /**
     * get payload only by extends
     * @return array
     */
    public function extendGetPayload() : array
    {
        if ($this->extendOn) {
            return $this->payload;
        } else {
            return [];
        }
    }

    /**
     * get item from payload and remove
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function extendPullPayload(string $key, ?string $default = null)
    {
        if ($this->extendOn) {
            if (key_exists($key, $this->payload)) {
                $value = $this->payload[$key];
                unset($this->payload[$key]);
                return $value;
            } else {
                return $default;
            }
        } else {
            return null;
        }
    }

    /**
     * get signature
     * @return string
     * @throws AlgFileException
     */
    protected function getSignature() : string
    {
        $alg = $this->getAlg();
        $path = __DIR__ . '/alg/' . $alg . '.php';

        if (file_exists($path)) {
            require $path;

            try {
                $class = '\jwt\alg\\' . $alg;
                $sign = call_user_func_array(
                    [new $class, self:: GENERATE_SIGNATURE_FUNC_NAME],
                    [$this->headerStr, $this->payloadStr, $this->secret]
                );
                if (!is_string($sign) && !method_exists($sign, '__toString')) {
                    throw new SignatureException('Incorrect signature given! signature has to be string type!');
                }
                return (string)$sign;
            } catch (Exception $e) {
                throw new AlgFileException('Algorithm relative file exists. Function not found: '
                    . self::GENERATE_SIGNATURE_FUNC_NAME);
            }
        } else {
            throw new AlgFileException('Algorithm relative file does not exist.');
        }
    }
}