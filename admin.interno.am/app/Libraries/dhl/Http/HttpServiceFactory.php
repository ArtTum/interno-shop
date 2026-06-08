<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Http;

use App\Libraries\dhl\Api\Data\AuthenticationStorageInterface;
use App\Libraries\dhl\Api\ServiceFactoryInterface;
use App\Libraries\dhl\Api\ShipmentServiceInterface;
use App\Libraries\dhl\Exception\ServiceExceptionFactory;
use App\Libraries\dhl\Http\ClientPlugin\OrderErrorPlugin;
use App\Libraries\dhl\Http\ClientPlugin\RequestValidatorPlugin;
use App\Libraries\dhl\Model\ParcelDe\ResponseMapper\CreateShipmentResponseMapper;
use App\Libraries\dhl\Model\ParcelDe\ResponseMapper\DeleteShipmentResponseMapper;
use App\Libraries\dhl\Model\ParcelDe\ResponseMapper\ValidateShipmentResponseMapper;
use App\Libraries\dhl\Serializer\JsonSerializer;
use App\Libraries\dhl\Service\OrderService;
use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\Plugin\ContentLengthPlugin;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Client\Common\Plugin\LoggerPlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Message\Authentication\BasicAuth;
use Http\Message\Formatter\FullHttpMessageFormatter;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

class HttpServiceFactory implements ServiceFactoryInterface
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $userAgent;

    /**
     * @var bool
     */
    private $schemaValidation = true;

    public function __construct(ClientInterface $httpClient, string $userAgent = '')
    {
        $this->httpClient = $httpClient;
        $this->userAgent = $userAgent;
    }

    public static function withSchemaValidationDisabled(ClientInterface $httpClient, string $userAgent = ''): self
    {
        $factory = new self($httpClient, $userAgent);
        $factory->schemaValidation = false;
        return $factory;
    }

    private function getUserAgent(): string
    {
        if (!empty($this->userAgent)) {
            return $this->userAgent;
        }

        if (!class_exists('\Composer\InstalledVersions')) {
            return 'dhl-sdk-api-bcs';
        }

        try {
            return 'dhl-sdk-api-bcs/' . \Composer\InstalledVersions::getVersion('dhl/sdk-api-bcs');
        } catch (\OutOfBoundsException $exception) {
            return 'dhl-sdk-api-bcs';
        }
    }

    public function createShipmentService(
        AuthenticationStorageInterface $authStorage,
        LoggerInterface $logger,
        bool $sandboxMode = false
    ): ShipmentServiceInterface {
        $userAuth = new BasicAuth($authStorage->getUser(), $authStorage->getSignature());

        $headers = [
            'Accept' => 'application/json, application/problem+json',
            'Content-Type' => 'application/json',
            'User-Agent' => $this->getUserAgent(),
            'dhl-api-key' => $authStorage->getApplicationToken(),
        ];

        $client = new PluginClient(
            $this->httpClient,
            [
                new HeaderDefaultsPlugin($headers),
                new AuthenticationPlugin($userAuth),
                new ContentLengthPlugin(),
                new RequestValidatorPlugin($logger, $this->schemaValidation),
                new LoggerPlugin($logger, new FullHttpMessageFormatter(null)),
                new OrderErrorPlugin($logger),
            ]
        );

        try {
            $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
            $streamFactory = Psr17FactoryDiscovery::findStreamFactory();
        } catch (NotFoundException $exception) {
            throw ServiceExceptionFactory::createServiceException($exception);
        }

        return new OrderService(
            $client,
            $sandboxMode ? self::REST_URL_SANDBOX : self::REST_URL_PRODUCTION,
            new JsonSerializer(),
            new ValidateShipmentResponseMapper(),
            new CreateShipmentResponseMapper(),
            new DeleteShipmentResponseMapper(),
            $requestFactory,
            $streamFactory
        );
    }
}
