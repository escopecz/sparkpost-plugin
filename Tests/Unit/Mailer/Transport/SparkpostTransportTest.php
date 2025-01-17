<?php

declare(strict_types=1);

namespace MauticPlugin\SparkpostBundle\Tests\Unit\Mailer\Transport;

use Mautic\EmailBundle\Mailer\Message\MauticMessage;
use Mautic\EmailBundle\Model\TransportCallback;
use MauticPlugin\SparkpostBundle\Mailer\Transport\SparkpostTransport;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SparkpostTransportTest extends TestCase
{
    private TransportCallback|MockObject $transportCallbackMock;

    private HttpClientInterface|MockObject $httpClientMock;

    private EventDispatcherInterface|MockObject $eventDispatcherMock;

    private LoggerInterface|MockObject $loggerMock;

    private SparkpostTransport $transport;

    protected function setUp(): void
    {
        $this->transportCallbackMock = $this->createMock(TransportCallback::class);
        $this->httpClientMock        = $this->createMock(HttpClientInterface::class);
        $this->eventDispatcherMock   = $this->createMock(EventDispatcherInterface::class);
        $this->loggerMock            = $this->createMock(LoggerInterface::class);
        $this->transport             = new SparkpostTransport(
            'api-key',
            'some-region',
            $this->transportCallbackMock,
            $this->httpClientMock,
            $this->eventDispatcherMock,
            $this->loggerMock
        );
    }

    public function testSendEmail(): void
    {
        $sentMessageMock   = $this->createMock(SentMessage::class);
        $mauticMessageMock = $this->createMock(MauticMessage::class);
        $responseMock      = $this->createMock(ResponseInterface::class);

        $sentMessageMock->method('getOriginalMessage')
            ->willReturn($mauticMessageMock);

        $fromAddress    = new Address('from@mautic.com', 'From Name');
        $replyToAddress = new Address('reply@mautic.com', 'Reply To Name');

        $mauticMessageMock->method('getFrom')
            ->willReturn([$fromAddress]);

        $mauticMessageMock->method('getReplyTo')
            ->willReturn([$replyToAddress]);

        $mauticMessageMock->method('getHeaders')
            ->willReturn(new Headers());

        $responseMock->method('getStatusCode')
            ->willReturn(200);

        /** @phpstan-ignore-next-line */
        $this->httpClientMock->method('request')
            ->willReturn($responseMock);

        $response = $this->invokeInaccessibleMethod(
            $this->transport,
            'doSendApi',
            [
                $sentMessageMock,
                $this->createMock(Email::class),
                $this->createMock(Envelope::class),
            ]
        );

        Assert::assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @param array<mixed> $args
     *
     * @throws \ReflectionException
     */
    private function invokeInaccessibleMethod(object $object, string $methodName, array $args = []): mixed
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }
}
