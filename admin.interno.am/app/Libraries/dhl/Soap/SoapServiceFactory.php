<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Soap;

use App\Libraries\dhl\Api\Data\AuthenticationStorageInterface;
use App\Libraries\dhl\Api\ServiceFactoryInterface;
use App\Libraries\dhl\Api\ShipmentServiceInterface;
use App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentResponseMapper;
use App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentResponseMapper;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\ValidateShipmentResponseMapper;
use App\Libraries\dhl\Service\ShipmentService;
use App\Libraries\dhl\Soap\ClientDecorator\AuthenticationDecorator;
use App\Libraries\dhl\Soap\ClientDecorator\ErrorHandlerDecorator;
use App\Libraries\dhl\Soap\ClientDecorator\LoggerDecorator;
use Psr\Log\LoggerInterface;

class SoapServiceFactory implements ServiceFactoryInterface
{
    /**
     * @var \SoapClient
     */
    private $soapClient;

    public function __construct(\SoapClient $soapClient)
    {
        $this->soapClient = $soapClient;
    }

    public function createShipmentService(
        AuthenticationStorageInterface $authStorage,
        LoggerInterface $logger,
        bool $sandboxMode = false
    ): ShipmentServiceInterface {
        $validateShipmentResponseMapper = new ValidateShipmentResponseMapper();
        $createShipmentResponseMapper = new CreateShipmentResponseMapper();
        $deleteShipmentResponseMapper = new DeleteShipmentResponseMapper();

        $pluginClient = new Client($this->soapClient);
        $pluginClient = new ErrorHandlerDecorator($pluginClient);
        $pluginClient = new LoggerDecorator($pluginClient, $this->soapClient, $logger);
        $pluginClient = new AuthenticationDecorator($pluginClient, $this->soapClient, $authStorage);

        return new ShipmentService(
            $pluginClient,
            $validateShipmentResponseMapper,
            $createShipmentResponseMapper,
            $deleteShipmentResponseMapper
        );
    }
}
