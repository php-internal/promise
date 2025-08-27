<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use React\Promise\Tests\Unit\Fixture\CallbackWithDNFTypehintClass;
use React\Promise\Tests\Unit\Fixture\CallbackWithIntersectionTypehintClass;
use React\Promise\Tests\Unit\Fixture\CallbackWithoutTypehintClass;
use React\Promise\Tests\Unit\Fixture\CallbackWithTypehintClass;
use React\Promise\Tests\Unit\Fixture\CallbackWithUnionTypehintClass;
use React\Promise\Tests\Unit\Fixture\CountableException;
use React\Promise\Tests\Unit\Fixture\IterableException;

use function React\Promise\_checkTypehint;

class FunctionCheckTypehintTest extends TestCase
{
    #[Test]
    public function shouldAcceptClosureCallbackWithTypehint(): void
    {
        self::assertTrue(_checkTypehint(static function (\InvalidArgumentException $e): void {}, new \InvalidArgumentException()));
        self::assertFalse(_checkTypehint(static function (\InvalidArgumentException $e): void {}, new \Exception()));
    }

    #[Test]
    public function shouldAcceptFunctionStringCallbackWithTypehint(): void
    {
        self::assertTrue(_checkTypehint(new CallbackWithTypehintClass(), new \InvalidArgumentException()));
        self::assertFalse(_checkTypehint(new CallbackWithTypehintClass(), new \Exception()));
    }

    #[Test]
    public function shouldAcceptInvokableObjectCallbackWithTypehint(): void
    {
        self::assertTrue(_checkTypehint(new CallbackWithTypehintClass(), new \InvalidArgumentException()));
        self::assertFalse(_checkTypehint(new CallbackWithTypehintClass(), new \Exception()));
    }

    #[Test]
    public function shouldAcceptObjectMethodCallbackWithTypehint(): void
    {
        self::assertTrue(_checkTypehint([new CallbackWithTypehintClass(), 'testCallback'], new \InvalidArgumentException()));
        self::assertFalse(_checkTypehint([new CallbackWithTypehintClass(), 'testCallback'], new \Exception()));
    }

    #[Test]
    public function shouldAcceptStaticClassCallbackWithTypehint(): void
    {
        self::assertTrue(_checkTypehint([CallbackWithTypehintClass::class, 'testCallbackStatic'], new \InvalidArgumentException()));
        self::assertFalse(_checkTypehint([CallbackWithTypehintClass::class, 'testCallbackStatic'], new \Exception()));
    }

    /**
     * @requires PHP 8
     */
    #[Test]
    public function shouldAcceptClosureCallbackWithUnionTypehint(): void
    {
        eval(
            'namespace React\Promise;' .
            'self::assertTrue(_checkTypehint(function (\RuntimeException|\InvalidArgumentException $e) {}, new \InvalidArgumentException()));' .
            'self::assertFalse(_checkTypehint(function (\RuntimeException|\InvalidArgumentException $e) {}, new \Exception()));'
        );
    }

    /**
     * @requires PHP 8
     */
    #[Test]
    public function shouldAcceptInvokableObjectCallbackWithUnionTypehint(): void
    {
        self::assertTrue(_checkTypehint(new CallbackWithUnionTypehintClass(), new \InvalidArgumentException()));
        self::assertFalse(_checkTypehint(new CallbackWithUnionTypehintClass(), new \Exception()));
    }

    /**
     * @requires PHP 8
     */
    #[Test]
    public function shouldAcceptObjectMethodCallbackWithUnionTypehint(): void
    {
        self::assertTrue(_checkTypehint([new CallbackWithUnionTypehintClass(), 'testCallback'], new \InvalidArgumentException()));
        self::assertFalse(_checkTypehint([new CallbackWithUnionTypehintClass(), 'testCallback'], new \Exception()));
    }

    /**
     * @requires PHP 8
     */
    #[Test]
    public function shouldAcceptStaticClassCallbackWithUnionTypehint(): void
    {
        self::assertTrue(_checkTypehint([CallbackWithUnionTypehintClass::class, 'testCallbackStatic'], new \InvalidArgumentException()));
        self::assertFalse(_checkTypehint([CallbackWithUnionTypehintClass::class, 'testCallbackStatic'], new \Exception()));
    }

    /**
     * @requires PHP 8.1
     */
    #[Test]
    public function shouldAcceptInvokableObjectCallbackWithIntersectionTypehint(): void
    {
        self::assertFalse(_checkTypehint(new CallbackWithIntersectionTypehintClass(), new \RuntimeException()));
        self::assertTrue(_checkTypehint(new CallbackWithIntersectionTypehintClass(), new CountableException()));
    }

    /**
     * @requires PHP 8.1
     */
    #[Test]
    public function shouldAcceptObjectMethodCallbackWithIntersectionTypehint(): void
    {
        self::assertFalse(_checkTypehint([new CallbackWithIntersectionTypehintClass(), 'testCallback'], new \RuntimeException()));
        self::assertTrue(_checkTypehint([new CallbackWithIntersectionTypehintClass(), 'testCallback'], new CountableException()));
    }

    /**
     * @requires PHP 8.1
     */
    #[Test]
    public function shouldAcceptStaticClassCallbackWithIntersectionTypehint(): void
    {
        self::assertFalse(_checkTypehint([CallbackWithIntersectionTypehintClass::class, 'testCallbackStatic'], new \RuntimeException()));
        self::assertTrue(_checkTypehint([CallbackWithIntersectionTypehintClass::class, 'testCallbackStatic'], new CountableException()));
    }

    /**
     * @requires PHP 8.2
     */
    #[Test]
    public function shouldAcceptInvokableObjectCallbackWithDNFTypehint(): void
    {
        self::assertFalse(_checkTypehint(new CallbackWithDNFTypehintClass(), new \RuntimeException()));
        self::assertTrue(_checkTypehint(new CallbackWithDNFTypehintClass(), new IterableException()));
        self::assertTrue(_checkTypehint(new CallbackWithDNFTypehintClass(), new CountableException()));
    }

    /**
     * @requires PHP 8.2
     */
    #[Test]
    public function shouldAcceptObjectMethodCallbackWithDNFTypehint(): void
    {
        self::assertFalse(_checkTypehint([new CallbackWithDNFTypehintClass(), 'testCallback'], new \RuntimeException()));
        self::assertTrue(_checkTypehint([new CallbackWithDNFTypehintClass(), 'testCallback'], new CountableException()));
        self::assertTrue(_checkTypehint([new CallbackWithDNFTypehintClass(), 'testCallback'], new IterableException()));
    }

    /**
     * @requires PHP 8.2
     */
    #[Test]
    public function shouldAcceptStaticClassCallbackWithDNFTypehint(): void
    {
        self::assertFalse(_checkTypehint([CallbackWithDNFTypehintClass::class, 'testCallbackStatic'], new \RuntimeException()));
        self::assertTrue(_checkTypehint([CallbackWithDNFTypehintClass::class, 'testCallbackStatic'], new CountableException()));
        self::assertTrue(_checkTypehint([CallbackWithDNFTypehintClass::class, 'testCallbackStatic'], new IterableException()));
    }

    #[Test]
    public function shouldAcceptClosureCallbackWithoutTypehint(): void
    {
        self::assertTrue(_checkTypehint(static function (\InvalidArgumentException $e): void {}, new \InvalidArgumentException()));
    }

    #[Test]
    public function shouldAcceptFunctionStringCallbackWithoutTypehint(): void
    {
        self::assertTrue(_checkTypehint(new CallbackWithoutTypehintClass(), new \InvalidArgumentException()));
    }

    #[Test]
    public function shouldAcceptInvokableObjectCallbackWithoutTypehint(): void
    {
        self::assertTrue(_checkTypehint(new CallbackWithoutTypehintClass(), new \InvalidArgumentException()));
    }

    #[Test]
    public function shouldAcceptObjectMethodCallbackWithoutTypehint(): void
    {
        self::assertTrue(_checkTypehint([new CallbackWithoutTypehintClass(), 'testCallback'], new \InvalidArgumentException()));
    }

    #[Test]
    public function shouldAcceptStaticClassCallbackWithoutTypehint(): void
    {
        self::assertTrue(_checkTypehint([CallbackWithoutTypehintClass::class, 'testCallbackStatic'], new \InvalidArgumentException()));
    }
}

function testCallbackWithTypehint(\InvalidArgumentException $e): void {}

function testCallbackWithoutTypehint(): void {}
