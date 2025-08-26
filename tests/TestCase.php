<?php

declare(strict_types=1);

namespace React\Promise;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public function expectCallableExactly($amount)
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->exactly($amount))
            ->method('__invoke');

        return $mock;
    }

    public function expectCallableOnce()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        return $mock;
    }

    public function expectCallableNever()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        return $mock;
    }

    public function createCallableMock()
    {
        if (\method_exists(\PHPUnit\Framework\MockObject\MockBuilder::class, 'addMethods')) {
            // PHPUnit 10+
            return $this->getMockBuilder('stdClass')->addMethods(['__invoke'])->getMock();
        }
        // legacy PHPUnit 4 - PHPUnit 9
        return $this->getMockBuilder('stdClass')->getMock();

    }

    public function setExpectedException($exception, $exceptionMessage = '', $exceptionCode = null): void
    {
        if (\method_exists($this, 'expectException')) {
            // PHPUnit 5+
            $this->expectException($exception);
            if ($exceptionMessage !== '') {
                $this->expectExceptionMessage($exceptionMessage);
            }
            if ($exceptionCode !== null) {
                $this->expectExceptionCode($exceptionCode);
            }
        } else {
            // legacy PHPUnit 4
            parent::setExpectedException($exception, $exceptionMessage, $exceptionCode);
        }
    }
}
