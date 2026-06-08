<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Service;

use App\Libraries\dhl\Api\Data\OrderConfigurationInterface;
use App\Libraries\dhl\Api\ShipmentServiceInterface;
use App\Libraries\dhl\Exception\AuthenticationErrorHttpException;
use App\Libraries\dhl\Exception\DetailedErrorHttpException;
use App\Libraries\dhl\Exception\SchemaErrorException;
use App\Libraries\dhl\Exception\ServiceExceptionFactory;
use App\Libraries\dhl\Model\ParcelDe\ResponseMapper\CreateShipmentResponseMapper;
use App\Libraries\dhl\Model\ParcelDe\ResponseMapper\DeleteShipmentResponseMapper;
use App\Libraries\dhl\Model\ParcelDe\ResponseMapper\ValidateShipmentResponseMapper;
use App\Libraries\dhl\Model\ParcelDe\ShipmentOrderRequest;
use App\Libraries\dhl\Serializer\JsonSerializer;
use App\Libraries\dhl\Service\ShipmentService\OrderConfiguration;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class OrderService implements ShipmentServiceInterface
{
    private const OPERATION_ORDERS = 'orders';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ValidateShipmentResponseMapper
     */
    private $validateShipmentResponseMapper;

    /**
     * @var \App\Libraries\dhl\Model\ParcelDe\ResponseMapper\CreateShipmentResponseMapper
     */
    private $createShipmentResponseMapper;

    /**
     * @var \App\Libraries\dhl\Model\ParcelDe\ResponseMapper\DeleteShipmentResponseMapper
     */
    private $deleteShipmentResponseMapper;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var \App\Libraries\dhl\Serializer\JsonSerializer
     */
    private $serializer;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    public function __construct(
        ClientInterface $client,
        string $baseUrl,
        JsonSerializer $serializer,
        ValidateShipmentResponseMapper $validateShipmentResponseMapper,
        CreateShipmentResponseMapper $createShipmentResponseMapper,
        DeleteShipmentResponseMapper $deleteShipmentResponseMapper,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
        $this->serializer = $serializer;
        $this->validateShipmentResponseMapper = $validateShipmentResponseMapper;
        $this->createShipmentResponseMapper = $createShipmentResponseMapper;
        $this->deleteShipmentResponseMapper = $deleteShipmentResponseMapper;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * Assert that all shipment orders are serializable.
     *
     * @param \stdClass[] $shipmentOrders
     * @return \App\Libraries\dhl\Model\ParcelDe\ShipmentOrderRequest
     *
     * @throws \Exception
     */
    private function getShipmentOrderRequest(array $shipmentOrders): ShipmentOrderRequest
    {
        foreach ($shipmentOrders as $shipmentOrder) {
            if (!$shipmentOrder instanceof \JsonSerializable) {
                throw new \InvalidArgumentException('Shipment orders must implement JsonSerializable');
            }
        }

        /** @var \JsonSerializable[] $shipmentOrders */
        return new ShipmentOrderRequest($shipmentOrders);
    }

    /**
     * @param string[] $requestParams
     * @param \App\Libraries\dhl\Api\Data\OrderConfigurationInterface $configuration
     * @return string
     */
    private function getQuery(array $requestParams, OrderConfigurationInterface $configuration): string
    {
        if ($configuration->mustEncode()) {
            $requestParams['mustEncode'] = 'true';
        }

        if ($configuration->isCombinedPrinting() !== null) {
            $requestParams['combine'] = $configuration->isCombinedPrinting() ? 'true' : 'false';
        }

        if ($configuration->getDocFormat() === OrderConfigurationInterface::DOC_FORMAT_ZPL2) {
            $requestParams['docFormat'] = 'ZPL2';
        }

        if ($configuration->getPrintFormat()) {
            $requestParams['printFormat'] = $configuration->getPrintFormat();
        }

        if ($configuration->getPrintFormatReturn()) {
            $requestParams['retourePrintFormat'] = $configuration->getPrintFormatReturn();
        }

        return http_build_query($requestParams);
    }

    public function getVersion(): string
    {
        try {
            $httpRequest = $this->requestFactory->createRequest('GET', $this->baseUrl);

            $response = $this->client->sendRequest($httpRequest);
            $responseJson = (string) $response->getBody();

            $responseData = \json_decode($responseJson, true);

            return $responseData['backend']['version'] ?? '';
        } catch (\Throwable $exception) {
            throw ServiceExceptionFactory::createServiceException($exception);
        }
    }

    public function validateShipments(array $shipmentOrders, OrderConfigurationInterface $configuration = null): array
    {
        if (!$configuration instanceof OrderConfigurationInterface) {
            $configuration = new OrderConfiguration();
        }

        $query = $this->getQuery(['validate' => 'true'], $configuration);
        $uri = sprintf('%s/%s?%s', $this->baseUrl, self::OPERATION_ORDERS, $query);

        try {
            $shipmentOrderRequest = $this->getShipmentOrderRequest($shipmentOrders);
            $shipmentOrderRequest->setProfile($configuration->getProfile());

            $payload = $this->serializer->encode($shipmentOrderRequest);
            $stream = $this->streamFactory->createStream($payload);

            $httpRequest = $this->requestFactory->createRequest('POST', $uri)->withBody($stream);

            $response = $this->client->sendRequest($httpRequest);
            $responseJson = (string) $response->getBody();
            $responseObject = $this->serializer->decode($responseJson);

            return $this->validateShipmentResponseMapper->map($responseObject);
        } catch (AuthenticationErrorHttpException $exception) {
            throw ServiceExceptionFactory::createAuthenticationException($exception);
        } catch (DetailedErrorHttpException | SchemaErrorException $exception) {
            throw ServiceExceptionFactory::createDetailedServiceException($exception);
        } catch (\Throwable $exception) {
            throw ServiceExceptionFactory::createServiceException($exception);
        }
    }

    public function createShipments(array $shipmentOrders, OrderConfigurationInterface $configuration = null): array
    {
        if (!$configuration instanceof OrderConfigurationInterface) {
            $configuration = new OrderConfiguration();
        }

        $query = $this->getQuery([], $configuration);
        $uri = sprintf('%s/%s', $this->baseUrl, self::OPERATION_ORDERS);
        if (!empty($query)) {
            $uri = "$uri?$query";
        }

        try {
            $shipmentOrderRequest = $this->getShipmentOrderRequest($shipmentOrders);
            $shipmentOrderRequest->setProfile($configuration->getProfile());

            $payload = $this->serializer->encode($shipmentOrderRequest);
            $stream = $this->streamFactory->createStream($payload);

            $httpRequest = $this->requestFactory->createRequest('POST', $uri)->withBody($stream);

            $response = $this->client->sendRequest($httpRequest);
            $responseJson = (string) $response->getBody();
            $responseObject = $this->serializer->decode($responseJson);

            return $this->createShipmentResponseMapper->map($responseObject);
        } catch (AuthenticationErrorHttpException $exception) {
            throw ServiceExceptionFactory::createAuthenticationException($exception);
        } catch (DetailedErrorHttpException | SchemaErrorException $exception) {
            throw ServiceExceptionFactory::createDetailedServiceException($exception);
        } catch (\Throwable $exception) {
            throw ServiceExceptionFactory::createServiceException($exception);
        }
    }

    public function cancelShipments(
        array $shipmentNumbers,
        string $profile = OrderConfigurationInterface::DEFAULT_PROFILE
    ): array {
        $shipmentNumbers = array_filter($shipmentNumbers);
        if (empty($shipmentNumbers)) {
            return [];
        }

        $requestParams = array_map(
            function (string $shipmentNumber) {
                return "shipment=$shipmentNumber";
            },
            $shipmentNumbers
        );
        array_unshift($requestParams, "profile=$profile");

        $uri = sprintf('%s/%s?%s', $this->baseUrl, self::OPERATION_ORDERS, implode('&', $requestParams));

        try {
            $httpRequest = $this->requestFactory->createRequest('DELETE', $uri);

            $response = $this->client->sendRequest($httpRequest);
            $responseJson = (string) $response->getBody();
            $responseObject = $this->serializer->decode($responseJson);

            return $this->deleteShipmentResponseMapper->map($responseObject);
        } catch (AuthenticationErrorHttpException $exception) {
            throw ServiceExceptionFactory::createAuthenticationException($exception);
        } catch (DetailedErrorHttpException $exception) {
            throw ServiceExceptionFactory::createDetailedServiceException($exception);
        } catch (\Throwable $exception) {
            throw ServiceExceptionFactory::createServiceException($exception);
        }
    }
}
