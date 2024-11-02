<?php

namespace Topdata\TopdataFoundationSW6\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * 11/2024 created
 */
class ManufacturerService
{
    private Context $defaultContext;
    private string $systemDefaultLocaleCode;
    private ?array $_manufacturers = null; // a map .. todo: rename

    public function __construct(
        private readonly EntityRepository    $productManufacturerRepository,
        private readonly LocaleHelperService $localeHelperService,
    )
    {
        $this->defaultContext = Context::createDefaultContext();
        $this->systemDefaultLocaleCode = $this->localeHelperService->getLocaleCodeOfSystemLanguage();
    }


    /**
     * Retrieves the manufacturer ID by name. If the manufacturer does not exist, it creates a new one.
     * 10/2024 extracted from TopdataWebserviceConnector's ProductService
     *
     * @param string $manufacturerName The name of the manufacturer.
     * @return string The ID of the manufacturer.
     */
    public function getManufacturerIdByName(string $manufacturerName): string
    {
        // ---- Check if manufacturers array is initialized
        if ($this->_manufacturers === null) {
            $this->_buildManufacturerNameToIdMap();
        }

        // ---- Check if manufacturer exists in the array
        if (isset($this->_manufacturers[$manufacturerName])) {
            $manufacturerId = $this->_manufacturers[$manufacturerName];
        } else {
            // ---- Create a new manufacturer if it does not exist
            $manufacturerId = Uuid::randomHex();
            $this->productManufacturerRepository->create([
                [
                    'id'   => $manufacturerId,
                    'name' => [
                        $this->systemDefaultLocaleCode => $manufacturerName,
                    ],
                ],
            ], $this->defaultContext);
            $this->_manufacturers[$manufacturerName] = $manufacturerId;
        }

        return $manufacturerId;
    }


    /**
     * Builds a map of manufacturer names to their IDs.
     *
     * This method retrieves all manufacturers from the repository and constructs
     * an associative array where the keys are manufacturer names and the values
     * are their corresponding IDs. The resulting array is stored in the
     * `_manufacturers` property.
     *
     * @return void
     */
    private function _buildManufacturerNameToIdMap(): void
    {
        // ---- fetch all manufacturers
        $criteria = new Criteria();
        $manufacturers = $this->productManufacturerRepository
            ->search($criteria, $this->defaultContext)
            ->getEntities();

        // ---- build the map
        $this->_manufacturers = [];
        foreach ($manufacturers as $manufacturer) {
            $this->_manufacturers[$manufacturer->getName()] = $manufacturer->getId();
        }
    }


}