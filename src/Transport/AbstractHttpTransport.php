<?php namespace Hampel\Symfony\Mailer\SparkPost\Transport;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

/**
 * @author Victor Bocharsky <victor@symfonycasts.com>
 */
abstract class AbstractHttpTransport extends AbstractTransport
{
    protected $host;
    protected $port;
    protected $client;

    public function __construct(ClientInterface $client, ?EventDispatcherInterface $dispatcher = null, ?LoggerInterface $logger = null)
    {
        $this->client = $client;

        parent::__construct($dispatcher, $logger);
    }

    /**
     * @return $this
     */
    public function setHost(?string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return $this
     */
    public function setPort(?int $port)
    {
        $this->port = $port;

        return $this;
    }

    abstract protected function doSendHttp(SentMessage $message): ResponseInterface;

    protected function doSend(SentMessage $message): void
    {
        $response = null;
        try {
            $response = $this->doSendHttp($message);
            $message->appendDebug($response->getReasonPhrase() ?? '');
        } catch (ClientException $e) {
            $message->appendDebug($e->getResponse()->getReasonPhrase() ?? '');

            throw $e;
        }
    }
}
