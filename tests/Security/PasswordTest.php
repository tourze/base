<?php

namespace tourze\tests\Security;

use tourze\Security\Password;

class PasswordTest extends \PHPUnit_Framework_TestCase
{

    public function testStrength()
    {
        $expectData = [
            'abc'                     => 0,
            '123456'                  => 0,
            '&*3fds*)&^*(Hor3*)&O&(*' => 5,
        ];

        foreach ($expectData as $password => $expect)
        {
            $this->assertGreaterThanOrEqual(Password::strength($password), $expect, $password.' test failed.');
        }
    }

}
