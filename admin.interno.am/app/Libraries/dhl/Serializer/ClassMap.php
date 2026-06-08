<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Serializer;

use App\Libraries\dhl\Model\Bcs\Common\StatusInformation;
use App\Libraries\dhl\Model\Bcs\Common\Version;
use App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderResponse;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\BankType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\CDP;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\CommunicationType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\CountryType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\Economy;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ExportDocPosition;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ExportDocumentType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\Ident;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\NameType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\NativeAddressType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\PackStationType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\PostfilialeType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ReceiverNativeAddressType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ReceiverType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ReceiverTypeType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ServiceConfiguration;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ServiceConfigurationAdditionalInsurance;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ServiceConfigurationCashOnDelivery;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ServiceConfigurationDateOfDelivery;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ServiceConfigurationDeliveryTimeFrame;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ServiceConfigurationDetails;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ServiceConfigurationDetailsOptional;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ServiceConfigurationEndorsement;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ServiceConfigurationIC;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ServiceConfigurationISR;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ServiceConfigurationVisualAgeCheck;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\Shipment;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ShipmentDetailsType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ShipmentDetailsTypeType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ShipmentItemType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ShipmentNotificationType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ShipmentOrderType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ShipmentService;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ShipperType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\RequestType\ShipperTypeType;
use App\Libraries\dhl\Model\Bcs\CreateShipment\ResponseType\CreationState;
use App\Libraries\dhl\Model\Bcs\CreateShipment\ResponseType\LabelData;
use App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderResponse;
use App\Libraries\dhl\Model\Bcs\DeleteShipment\ResponseType\DeletionState;
use App\Libraries\dhl\Model\Bcs\GetVersion\GetVersionResponse;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\ResponseType\ValidationState;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\ValidateShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\ValidateShipmentResponse;

class ClassMap
{
    /**
     * Map WSDL types to PHP classes.
     *
     * @return string[]
     */
    public static function get(): array
    {
        return [
            // shared types
            'Statusinformation' => StatusInformation::class,
            'Version' => Version::class,

            // GET VERSION - response types
            'GetVersionResponse' => GetVersionResponse::class,

            // VALIDATE SHIPMENT - request types
            'ValidateShipmentOrderRequest' => ValidateShipmentOrderRequest::class,
            // VALIDATE SHIPMENT - response types
            'ValidateShipmentResponse' => ValidateShipmentResponse::class,
            'ValidationState' => ValidationState::class,

            // DELETE SHIPMENT - request types
            'DeleteShipmentOrderRequest' => DeleteShipmentOrderRequest::class,
            // DELETE SHIPMENT - response types
            'DeleteShipmentOrderResponse' => DeleteShipmentOrderResponse::class,
            'DeletionState' => DeletionState::class,

            // CREATE SHIPMENT - request types
            'CreateShipmentOrderRequest' => CreateShipmentOrderRequest::class,
            'ShipmentOrderType' => ShipmentOrderType::class,
            'Shipment' => Shipment::class,
            'ShipmentDetailsTypeType' => ShipmentDetailsTypeType::class,
            'ShipmentDetailsType' => ShipmentDetailsType::class,
            'ShipmentItemType' => ShipmentItemType::class,
            'ShipmentService' => ShipmentService::class,
            'ServiceconfigurationDateOfDelivery' => ServiceConfigurationDateOfDelivery::class,
            'ServiceconfigurationDeliveryTimeframe' => ServiceConfigurationDeliveryTimeFrame::class,
            'ServiceconfigurationISR' => ServiceConfigurationISR::class,
            'Serviceconfiguration' => ServiceConfiguration::class,
            'ServiceconfigurationEndorsement' => ServiceConfigurationEndorsement::class,
            'ServiceconfigurationVisualAgeCheck' => ServiceConfigurationVisualAgeCheck::class,
            'ServiceconfigurationDetails' => ServiceConfigurationDetails::class,
            'ServiceconfigurationCashOnDelivery' => ServiceConfigurationCashOnDelivery::class,
            'ServiceconfigurationAdditionalInsurance' => ServiceConfigurationAdditionalInsurance::class,
            'ServiceconfigurationIC' => ServiceConfigurationIC::class,
            'Ident' => Ident::class,
            'ServiceconfigurationDetailsOptional' => ServiceConfigurationDetailsOptional::class,
            'Economy' => Economy::class,
            'CDP' => CDP::class,
            'ShipmentNotificationType' => ShipmentNotificationType::class,
            'BankType' => BankType::class,
            'ShipperType' => ShipperType::class,
            'ShipperTypeType' => ShipperTypeType::class,
            'NameType' => NameType::class,
            'NativeAddressType' => NativeAddressType::class,
            'CountryType' => CountryType::class,
            'CommunicationType' => CommunicationType::class,
            'ReceiverType' => ReceiverType::class,
            'ReceiverTypeType' => ReceiverTypeType::class,
            'ReceiverNativeAddressType' => ReceiverNativeAddressType::class,
            'PackStationType' => PackStationType::class,
            'cis:PostfilialeType' => PostfilialeType::class,
            'ExportDocumentType' => ExportDocumentType::class,
            'ExportDocPosition' => ExportDocPosition::class,
            // CREATE SHIPMENT - response types
            'CreateShipmentOrderResponse' => CreateShipmentOrderResponse::class,
            'CreationState' => CreationState::class,
            'LabelData' => LabelData::class,
        ];
    }
}
