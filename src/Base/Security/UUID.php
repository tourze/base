<?php

namespace tourze\Base\Security;

use Rhumsaa\Uuid\Uuid as VendorUUID;
use Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException;

/**
 * UUID生成器
 *
 * @package   Base
 * @category  Security
 */
class UUID
{
    /**
     * UUID1
     *
     * @param null $node
     * @param null $clockSeq
     * @return string
     */
    public static function uuid1($node = null, $clockSeq = null)
    {
        try
        {
            $uuid1 = VendorUUID::uuid1($node, $clockSeq);
            return $uuid1->toString();
        }
        catch (UnsatisfiedDependencyException $e)
        {
            return false;
        }
    }

    /**
     * Version 3 UUIDs are named based. They require a namespace (another
     * valid UUID) and a value (the name). Given the same namespace and
     * name, the output is always the same.
     *
     * @param   string $namespace namespace
     * @param   string $name      key name
     *
     * @return  string
     */
    public static function v3($namespace = VendorUUID::NAMESPACE_DNS, $name = 'php.net')
    {
        try
        {
            $uuid3 = VendorUUID::uuid3($namespace, $name);
            return $uuid3->toString();
        }
        catch (UnsatisfiedDependencyException $e)
        {
            return false;
        }
    }

    /**
     * Version 4 UUIDs are pseudo-random.
     *
     * @return  string
     */
    public static function v4()
    {
        try
        {
            $uuid4 = VendorUUID::uuid4();
            return $uuid4->toString();
        }
        catch (UnsatisfiedDependencyException $e)
        {
            return false;
        }
    }

    /**
     * Version 5 UUIDs are named based. They require a namespace (another
     * valid UUID) and a value (the name). Given the same namespace and
     * name, the output is always the same.
     *
     * @param   string $namespace namespace
     * @param   string $name      key name
     *
     * @return  string
     */
    public static function v5($namespace = VendorUUID::NAMESPACE_DNS, $name = 'php.net')
    {
        try
        {
            $uuid5 = VendorUUID::uuid5($namespace, $name);
            return $uuid5->toString();
        }
        catch (UnsatisfiedDependencyException $e)
        {
            return false;
        }
    }

}
