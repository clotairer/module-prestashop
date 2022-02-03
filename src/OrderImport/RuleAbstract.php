<?php
/**
 *
 *  Copyright since 2019 Shopping Feed
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the Academic Free License (AFL 3.0)
 *  that is bundled with this package in the file LICENSE.md.
 *  It is also available through the world-wide-web at this URL:
 *  https://opensource.org/licenses/AFL-3.0
 *  If you did not receive a copy of the license and are unable to
 *  obtain it through the world-wide-web, please send an email
 *  to tech@202-ecommerce.com so we can send you a copy immediately.
 *
 *  @author    202 ecommerce <tech@202-ecommerce.com>
 *  @copyright Since 2019 Shopping Feed
 *  @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 *
 */

namespace ShoppingfeedAddon\OrderImport;

use OrderState;
use ShoppingfeedAddon\OrderImport\RuleInterface;
use Validate;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This abstract class represents a default specific rule to be applied during an SF order
 * import
 */
abstract class RuleAbstract implements RuleInterface
{
    protected $configuration;

    /**
     * @inheritdoc
     */
    public function __construct($configuration = array())
    {
        if (empty($configuration)) {
            $configuration = $this->getDefaultConfiguration();
        }
        $this->configuration = $configuration;
    }

    /**
     * @inheritdoc
     */
    abstract public function isApplicable(\ShoppingFeed\Sdk\Api\Order\OrderResource $apiOrder);

    /**
     * @inheritdoc
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultConfiguration()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getConfigurationSubform()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    protected function l($msg, $domain)
    {
        return \Translate::getModuleTranslation('shoppingfeed', $msg, $domain);
    }

    protected function isOrderStateValid($idOrderState)
    {
        try {
            $orderState = new OrderState((int)$idOrderState);
        } catch (\Throwable $e) {
            return false;
        }

        return Validate::isLoadedObject($orderState);
    }

    /**
     * @inheritdoc
     */
    abstract public function getDescription();

    /**
     * @inheritdoc
     */
    abstract public function getConditions();
}
