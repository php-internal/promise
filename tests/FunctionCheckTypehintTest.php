<?php

declare(strict_types=1);

namespace React\Promise;

class FunctionCheckTypehintTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptClosureCallbackWithTypehint(): void
    {
        $this->assertTrue(_checkTypehint(static function (\InvalidArgumentException $e): void {}, new \InvalidArgumentException()));
        $this->assertfalse(_checkTypehint(static function (\InvalidArgumentException $e): void {}, new \Exception()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptFunctionStringCallbackWithTypehint(): void
    {
        $this->assertTrue(_checkTypehint('React\Promise\testCallbackWithTypehint', new \InvalidArgumentException()));
        $this->assertfalse(_checkTypehint('React\Promise\testCallbackWithTypehint', new \Exception()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptInvokableObjectCallbackWithTypehint(): void
    {
        $this->assertTrue(_checkTypehint(new CallbackWithTypehintClass(), new \InvalidArgumentException()));
        $this->assertfalse(_checkTypehint(new CallbackWithTypehintClass(), new \Exception()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptObjectMethodCallbackWithTypehint(): void
    {
        $this->assertTrue(_checkTypehint([new CallbackWithTypehintClass(), 'testCallback'], new \InvalidArgumentException()));
        $this->assertfalse(_checkTypehint([new CallbackWithTypehintClass(), 'testCallback'], new \Exception()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptStaticClassCallbackWithTypehint(): void
    {
        $this->assertTrue(_checkTypehint([new CallbackWithTypehintClass(), 'testCallbackStatic'], new \InvalidArgumentException()));
        $this->assertfalse(_checkTypehint([new CallbackWithTypehintClass(), 'testCallbackStatic'], new \Exception()));
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('8')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptClosureCallbackWithUnionTypehint(): void
    {
        eval(
            'namespace React\Promise;' .
            'self::assertTrue(_checkTypehint(function (\RuntimeException|\InvalidArgumentException $e) {}, new \InvalidArgumentException()));' .
            'self::assertFalse(_checkTypehint(function (\RuntimeException|\InvalidArgumentException $e) {}, new \Exception()));'
        );
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('8')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptInvokableObjectCallbackWithUnionTypehint(): void
    {
        self::assertTrue(_checkTypehint(new CallbackWithUnionTypehintClass(), new \InvalidArgumentException()));
        self::assertFalse(_checkTypehint(new CallbackWithUnionTypehintClass(), new \Exception()));
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('8')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptObjectMethodCallbackWithUnionTypehint(): void
    {
        self::assertTrue(_checkTypehint([new CallbackWithUnionTypehintClass(), 'testCallback'], new \InvalidArgumentException()));
        self::assertFalse(_checkTypehint([new CallbackWithUnionTypehintClass(), 'testCallback'], new \Exception()));
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('8')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptStaticClassCallbackWithUnionTypehint(): void
    {
        self::assertTrue(_checkTypehint([\React\Promise\CallbackWithUnionTypehintClass::class, 'testCallbackStatic'], new \InvalidArgumentException()));
        self::assertFalse(_checkTypehint([\React\Promise\CallbackWithUnionTypehintClass::class, 'testCallbackStatic'], new \Exception()));
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('8.1')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptInvokableObjectCallbackWithIntersectionTypehint(): void
    {
        self::assertFalse(_checkTypehint(new CallbackWithIntersectionTypehintClass(), new \RuntimeException()));
        self::assertFalse(_checkTypehint(new CallbackWithIntersectionTypehintClass(), new CountableNonException()));
        self::assertTrue(_checkTypehint(new CallbackWithIntersectionTypehintClass(), new CountableException()));
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('8.1')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptObjectMethodCallbackWithIntersectionTypehint(): void
    {
        self::assertFalse(_checkTypehint([new CallbackWithIntersectionTypehintClass(), 'testCallback'], new \RuntimeException()));
        self::assertFalse(_checkTypehint([new CallbackWithIntersectionTypehintClass(), 'testCallback'], new CountableNonException()));
        self::assertTrue(_checkTypehint([new CallbackWithIntersectionTypehintClass(), 'testCallback'], new CountableException()));
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('8.1')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptStaticClassCallbackWithIntersectionTypehint(): void
    {
        self::assertFalse(_checkTypehint([\React\Promise\CallbackWithIntersectionTypehintClass::class, 'testCallbackStatic'], new \RuntimeException()));
        self::assertFalse(_checkTypehint([\React\Promise\CallbackWithIntersectionTypehintClass::class, 'testCallbackStatic'], new CountableNonException()));
        self::assertTrue(_checkTypehint([\React\Promise\CallbackWithIntersectionTypehintClass::class, 'testCallbackStatic'], new CountableException()));
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('8.2')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptInvokableObjectCallbackWithDNFTypehint(): void
    {
        self::assertFalse(_checkTypehint(new CallbackWithDNFTypehintClass(), new \RuntimeException()));
        self::assertTrue(_checkTypehint(new CallbackWithDNFTypehintClass(), new ArrayAccessibleException()));
        self::assertTrue(_checkTypehint(new CallbackWithDNFTypehintClass(), new CountableException()));
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('8.2')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptObjectMethodCallbackWithDNFTypehint(): void
    {
        self::assertFalse(_checkTypehint([new CallbackWithDNFTypehintClass(), 'testCallback'], new \RuntimeException()));
        self::assertTrue(_checkTypehint([new CallbackWithDNFTypehintClass(), 'testCallback'], new CountableException()));
        self::assertTrue(_checkTypehint([new CallbackWithDNFTypehintClass(), 'testCallback'], new ArrayAccessibleException()));
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('8.2')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptStaticClassCallbackWithDNFTypehint(): void
    {
        self::assertFalse(_checkTypehint([\React\Promise\CallbackWithDNFTypehintClass::class, 'testCallbackStatic'], new \RuntimeException()));
        self::assertTrue(_checkTypehint([\React\Promise\CallbackWithDNFTypehintClass::class, 'testCallbackStatic'], new CountableException()));
        self::assertTrue(_checkTypehint([\React\Promise\CallbackWithDNFTypehintClass::class, 'testCallbackStatic'], new ArrayAccessibleException()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptClosureCallbackWithoutTypehint(): void
    {
        $this->assertTrue(_checkTypehint(static function (\InvalidArgumentException $e): void {}, new \InvalidArgumentException()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptFunctionStringCallbackWithoutTypehint(): void
    {
        $this->assertTrue(_checkTypehint('React\Promise\testCallbackWithoutTypehint', new \InvalidArgumentException()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptInvokableObjectCallbackWithoutTypehint(): void
    {
        $this->assertTrue(_checkTypehint(new CallbackWithoutTypehintClass(), new \InvalidArgumentException()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptObjectMethodCallbackWithoutTypehint(): void
    {
        $this->assertTrue(_checkTypehint([new CallbackWithoutTypehintClass(), 'testCallback'], new \InvalidArgumentException()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptStaticClassCallbackWithoutTypehint(): void
    {
        $this->assertTrue(_checkTypehint([\React\Promise\CallbackWithoutTypehintClass::class, 'testCallbackStatic'], new \InvalidArgumentException()));
    }
}

function testCallbackWithTypehint(\InvalidArgumentException $e): void {}

function testCallbackWithoutTypehint(): void {}
