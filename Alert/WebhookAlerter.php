<?php

namespace Innmind\ProvisionerBundle\Alert;

use Symfony\Component\Console\Input\InputInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

/**
 * Send a HTTP POST request when a provision alert is raised
 */
class WebhookAlerter implements AlerterInterface
{
    protected $client;
    protected $uris = [];
    protected $logger;

    /**
     * Set the guzzle http client
     *
     * @param Client $client
     */
    public function setHttpClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Add a uri to call when an alert is raised
     *
     * @param string $uri
     */
    public function addUri($uri)
    {
        $this->uris[] = (string) $uri;
    }

    /**
     * Set the logger
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function alert($type, $name, InputInterface $input, $cpuUsage, $loadAverage, $leftOver = 0)
    {
        foreach ($this->uris as $uri) {
            $this->client->post(
                $uri,
                [
                    'body' => [
                        'type' => $type,
                        'command' => (string) $input,
                        'cpu' => $cpuUsage,
                        'load_average' => $loadAverage,
                        'required_processes' => $leftOver,
                    ]
                ]
            );

            if ($this->logger) {
                $this->logger->info(
                    'Provision alert notified to a uri',
                    ['uri' => $uri]
                );
            }
        }
    }
}
