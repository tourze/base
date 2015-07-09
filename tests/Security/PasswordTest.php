<?php

namespace tourze\tests\Security;

use tourze\Security\Password;

class PasswordTest extends \PHPUnit_Framework_TestCase
{

    public static function provider()
    {
        return [
            ['abc', 0],
            ['123456', 0],
            ['&*3fds*)&^*(Hor3*)&O&(*', 5],
        ];
    }

    /**
     * @dataProvider provider
     * @param $password
     * @param $expect
     */
    public function testStrength($password, $expect)
    {
        $this->assertGreaterThanOrEqual(Password::strength($password), $expect, $password.' test failed.');
    }
}
