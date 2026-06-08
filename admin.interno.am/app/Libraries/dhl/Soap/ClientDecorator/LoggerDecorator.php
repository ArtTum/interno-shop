<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Soap\ClientDecorator;

use App\Libraries\dhl\Exception\AuthenticationErrorException;
use App\Libraries\dhl\Exception\DetailedErrorException;
use App\Libraries\dhl\Model\Bcs\Common\AbstractResponse;
use App\Libraries\dhl\Model\Bcs\Common\StatusInformation;
use App\Libraries\dhl\Model\Bcs\Common\Version;
use App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderResponse;
use App\Libraries\dhl\Model\Bcs\CreateShipment\ResponseType\CreationState;
use App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderResponse;
use App\Libraries\dhl\Model\Bcs\DeleteShipment\ResponseType\DeletionState;
use App\Libraries\dhl\Model\Bcs\GetVersion\GetVersionResponse;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\ResponseType\ValidationState;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\ValidateShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\ValidateShipmentResponse;
use App\Libraries\dhl\Soap\AbstractClient;
use App\Libraries\dhl\Soap\AbstractDecorator;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerDecorator extends AbstractDecorator
{
    /**
     * @var \SoapClient
     */
    private $soapClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(AbstractClient $client, \SoapClient $soapClient, LoggerInterface $logger)
    {
        $this->soapClient = $soapClient;
        $this->logger = $logger;

        parent::__construct($client);
    }

    /**
     * Log entire webservice requests and responses.
     *
     * @param \Closure $performRequest
     *
     * @return \App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderResponse|\App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderResponse
     *
     * @throws AuthenticationErrorException
     * @throws DetailedErrorException
     * @throws \SoapFault
     */
    private function logCommunication(\Closure $performRequest): AbstractResponse
    {
        $logLevel = LogLevel::INFO;

        try {
            /** @var \App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderResponse|\App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderResponse $response */
            $response = $performRequest();
            if (!$response->getStatus()) {
                return $response;
            }

            // adjust log level on successful responses
            if ($response->getStatus()->getStatusCode() === 2000) {
                // unknown shipment number errors contained in response.
                $logLevel = LogLevel::ERROR;
            } elseif ($response->getStatus()->getStatusText() === 'Some Shipments had errors.') {
                // hard validation errors contained in response.
                $logLevel = LogLevel::ERROR;
            } elseif ($response->getStatus()->getStatusText() === 'Weak validation error occured.') {
                // weak validation errors contained in response.
                $logLevel = LogLevel::WARNING;
            }

            return $response;
        } catch (\Throwable $fault) {
            $logLevel = LogLevel::ERROR;

            throw $fault;
        } finally {
            $lastRequest = sprintf(
                "%s\n%s",
                $this->soapClient->__getLastRequestHeaders(),
                $this->soapClient->__getLastRequest()
            );

            $lastResponse = sprintf(
                "%s\n%s",
                $this->soapClient->__getLastResponseHeaders(),
                $this->soapClient->__getLastResponse()
            );

            $this->logger->log($logLevel, $lastRequest);
            $this->logger->log($logLevel, $lastResponse);

            if (isset($fault)) {
                $this->logger->log($logLevel, $fault->getMessage());
            }
        }
    }

    /**
     * Log status information from responses.
     *
     * @param \App\Libraries\dhl\Model\Bcs\Common\StatusInformation $status
     * @param string|null $shipmentNumber
     * @param string|null $sequenceNumber
     */
    private function logStatus(StatusInformation $status, $shipmentNumber = '', $sequenceNumber = '0'): void
    {
        $shipmentNumber = $shipmentNumber ?: $sequenceNumber;
        $statusCode = $status->getStatusCode();
        $statusText = $status->getStatusText();
        $statusMessages = array_unique($status->getStatusMessage());
        $logMessage = sprintf(
            'Shipment %s: Status %s (%s) – %s',
            $shipmentNumber,
            $statusCode,
            $statusText,
            implode(' ', $statusMessages)
        );

        if ($statusCode !== 0) {
            $this->logger->error($logMessage);
        } elseif ($statusText === 'Weak validation error occured.') {
            $this->logger->warning($logMessage);
        } else {
            $this->logger->debug($logMessage);
        }
    }

    public function getVersion(Version $requestType): GetVersionResponse
    {
        $performRequest = function () use ($requestType) {
            return parent::getVersion($requestType);
        };

        /** @var \App\Libraries\dhl\Model\Bcs\GetVersion\GetVersionResponse $response */
        $response = $this->logCommunication($performRequest);

        return $response;
    }

    public function validateShipment(ValidateShipmentOrderRequest $requestType): ValidateShipmentResponse
    {
        $performRequest = function () use ($requestType) {
            return parent::validateShipment($requestType);
        };

        /** @var \App\Libraries\dhl\Model\Bcs\ValidateShipment\ValidateShipmentResponse $response */
        $response = $this->logCommunication($performRequest);

        /** @var \App\Libraries\dhl\Model\Bcs\ValidateShipment\ResponseType\ValidationState $validationState */
        foreach ($response->getValidationState() as $validationState) {
            $this->logStatus($validationState->getStatus(), '', $validationState->getSequenceNumber());
        }

        return $response;
    }

    public function createShipmentOrder(CreateShipmentOrderRequest $requestType): CreateShipmentOrderResponse
    {
        $performRequest = function () use ($requestType) {
            return parent::createShipmentOrder($requestType);
        };

        /** @var \App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderResponse $response */
        $response = $this->logCommunication($performRequest);

        /** @var \App\Libraries\dhl\Model\Bcs\CreateShipment\ResponseType\CreationState $creationState */
        foreach ($response->getCreationState() as $creationState) {
            $this->logStatus(
                $creationState->getLabelData()->getStatus(),
                $creationState->getShipmentNumber(),
                $creationState->getSequenceNumber()
            );
        }

        return $response;
    }

    public function deleteShipmentOrder(DeleteShipmentOrderRequest $requestType): DeleteShipmentOrderResponse
    {
        $performRequest = function () use ($requestType) {
            return parent::deleteShipmentOrder($requestType);
        };

        /** @var \App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderResponse $response */
        $response = $this->logCommunication($performRequest);

        /** @var \App\Libraries\dhl\Model\Bcs\DeleteShipment\ResponseType\DeletionState $deletionState */
        foreach ($response->getDeletionState() as $deletionState) {
            $this->logStatus($deletionState->getStatus(), $deletionState->getShipmentNumber());
        }

        return $response;
    }
}
