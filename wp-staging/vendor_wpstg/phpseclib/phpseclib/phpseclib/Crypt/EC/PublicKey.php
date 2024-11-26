<?php

/**
 * EC Public Key
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2015 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */
namespace WPStaging\Vendor\phpseclib3\Crypt\EC;

use WPStaging\Vendor\phpseclib3\Common\Functions\Strings;
use WPStaging\Vendor\phpseclib3\Crypt\Common;
use WPStaging\Vendor\phpseclib3\Crypt\EC;
use WPStaging\Vendor\phpseclib3\Crypt\EC\BaseCurves\Montgomery as MontgomeryCurve;
use WPStaging\Vendor\phpseclib3\Crypt\EC\BaseCurves\TwistedEdwards as TwistedEdwardsCurve;
use WPStaging\Vendor\phpseclib3\Crypt\EC\Curves\Ed25519;
use WPStaging\Vendor\phpseclib3\Crypt\EC\Formats\Keys\PKCS1;
use WPStaging\Vendor\phpseclib3\Crypt\EC\Formats\Signature\ASN1 as ASN1Signature;
use WPStaging\Vendor\phpseclib3\Crypt\Hash;
use WPStaging\Vendor\phpseclib3\Exception\UnsupportedOperationException;
use WPStaging\Vendor\phpseclib3\Math\BigInteger;
/**
 * EC Public Key
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
final class PublicKey extends \WPStaging\Vendor\phpseclib3\Crypt\EC implements \WPStaging\Vendor\phpseclib3\Crypt\Common\PublicKey
{
    use Common\Traits\Fingerprint;
    /**
     * Verify a signature
     *
     * @see self::verify()
     * @param string $message
     * @param string $signature
     * @return mixed
     */
    public function verify($message, $signature)
    {
        if ($this->curve instanceof \WPStaging\Vendor\phpseclib3\Crypt\EC\BaseCurves\Montgomery) {
            throw new \WPStaging\Vendor\phpseclib3\Exception\UnsupportedOperationException('Montgomery Curves cannot be used to create signatures');
        }
        $shortFormat = $this->shortFormat;
        $format = $this->sigFormat;
        if ($format === \false) {
            return \false;
        }
        $order = $this->curve->getOrder();
        if ($this->curve instanceof \WPStaging\Vendor\phpseclib3\Crypt\EC\BaseCurves\TwistedEdwards) {
            if ($shortFormat == 'SSH2') {
                list(, $signature) = \WPStaging\Vendor\phpseclib3\Common\Functions\Strings::unpackSSH2('ss', $signature);
            }
            if ($this->curve instanceof \WPStaging\Vendor\phpseclib3\Crypt\EC\Curves\Ed25519 && self::$engines['libsodium'] && !isset($this->context)) {
                return \sodium_crypto_sign_verify_detached($signature, $message, $this->toString('libsodium'));
            }
            $curve = $this->curve;
            if (\strlen($signature) != 2 * $curve::SIZE) {
                return \false;
            }
            $R = \substr($signature, 0, $curve::SIZE);
            $S = \substr($signature, $curve::SIZE);
            try {
                $R = \WPStaging\Vendor\phpseclib3\Crypt\EC\Formats\Keys\PKCS1::extractPoint($R, $curve);
                $R = $this->curve->convertToInternal($R);
            } catch (\Exception $e) {
                return \false;
            }
            $S = \strrev($S);
            $S = new \WPStaging\Vendor\phpseclib3\Math\BigInteger($S, 256);
            if ($S->compare($order) >= 0) {
                return \false;
            }
            $A = $curve->encodePoint($this->QA);
            if ($curve instanceof \WPStaging\Vendor\phpseclib3\Crypt\EC\Curves\Ed25519) {
                $dom2 = !isset($this->context) ? '' : 'SigEd25519 no Ed25519 collisions' . "\0" . \chr(\strlen($this->context)) . $this->context;
            } else {
                $context = isset($this->context) ? $this->context : '';
                $dom2 = 'SigEd448' . "\0" . \chr(\strlen($context)) . $context;
            }
            $hash = new \WPStaging\Vendor\phpseclib3\Crypt\Hash($curve::HASH);
            $k = $hash->hash($dom2 . \substr($signature, 0, $curve::SIZE) . $A . $message);
            $k = \strrev($k);
            $k = new \WPStaging\Vendor\phpseclib3\Math\BigInteger($k, 256);
            list(, $k) = $k->divide($order);
            $qa = $curve->convertToInternal($this->QA);
            $lhs = $curve->multiplyPoint($curve->getBasePoint(), $S);
            $rhs = $curve->multiplyPoint($qa, $k);
            $rhs = $curve->addPoint($rhs, $R);
            $rhs = $curve->convertToAffine($rhs);
            return $lhs[0]->equals($rhs[0]) && $lhs[1]->equals($rhs[1]);
        }
        $params = $format::load($signature);
        if ($params === \false || \count($params) != 2) {
            return \false;
        }
        \extract($params);
        if (self::$engines['OpenSSL'] && \in_array($this->hash->getHash(), \openssl_get_md_methods())) {
            $sig = $format != 'ASN1' ? \WPStaging\Vendor\phpseclib3\Crypt\EC\Formats\Signature\ASN1::save($r, $s) : $signature;
            $result = \openssl_verify($message, $sig, $this->toString('PKCS8', ['namedCurve' => \false]), $this->hash->getHash());
            if ($result != -1) {
                return (bool) $result;
            }
        }
        $n_1 = $order->subtract(self::$one);
        if (!$r->between(self::$one, $n_1) || !$s->between(self::$one, $n_1)) {
            return \false;
        }
        $e = $this->hash->hash($message);
        $e = new \WPStaging\Vendor\phpseclib3\Math\BigInteger($e, 256);
        $Ln = $this->hash->getLength() - $order->getLength();
        $z = $Ln > 0 ? $e->bitwise_rightShift($Ln) : $e;
        $w = $s->modInverse($order);
        list(, $u1) = $z->multiply($w)->divide($order);
        list(, $u2) = $r->multiply($w)->divide($order);
        $u1 = $this->curve->convertInteger($u1);
        $u2 = $this->curve->convertInteger($u2);
        list($x1, $y1) = $this->curve->multiplyAddPoints([$this->curve->getBasePoint(), $this->QA], [$u1, $u2]);
        $x1 = $x1->toBigInteger();
        list(, $x1) = $x1->divide($order);
        return $x1->equals($r);
    }
    /**
     * Returns the public key
     *
     * @param string $type
     * @param array $options optional
     * @return string
     */
    public function toString($type, array $options = [])
    {
        $type = self::validatePlugin('Keys', $type, 'savePublicKey');
        return $type::savePublicKey($this->curve, $this->QA, $options);
    }
}