<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommence
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommence is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommence
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommence est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * ...........................................................................
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license   Commercial license
 */

namespace ShoppingfeedAddon\OrderImport\Rules;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Order;
use OrderHistory;
use OrderState;
use ShoppingFeed\Sdk\Api\Order\OrderResource;
use ShoppingfeedAddon\OrderImport\RuleAbstract;
use ShoppingfeedAddon\OrderImport\RuleInterface;
use ShoppingfeedClasslib\Extensions\ProcessLogger\ProcessLoggerHandler;
use Validate;

class ChangeStateOrder extends RuleAbstract implements RuleInterface
{
    public function isApplicable(OrderResource $apiOrder)
    {
        return $this->getIdOrderState() != 0;
    }

    public function onPostProcess($params)
    {
        /** @var \ShoppingfeedAddon\OrderImport\OrderData $orderData */
        $orderData = $params['orderData'];
        $apiOrder = $params['apiOrder'];
        $idOrder = $params['id_order'];
        $idOrderState = $this->getIdOrderState();

        $logPrefix = sprintf(
            $this->l('[Order: %s]', 'ChangeStateOrder'),
            $apiOrder->getId()
        );
        $logPrefix .= '[' . $apiOrder->getReference() . '] ' . self::class . ' | ';
        $psOrder = new Order($idOrder);

        if (false === Validate::isLoadedObject($psOrder)) {
            ProcessLoggerHandler::logError(
                $logPrefix .
                sprintf(
                    $this->l('Can not retrieve a prestashop order. Id Order: %d', 'ChangeStateOrder'),
                    $idOrder
                ),
                'Order',
                $idOrder
            );

            return;
        }

        if (false == $this->isOrderStateValid($idOrderState)) {
            ProcessLoggerHandler::logError(
                $logPrefix .
                sprintf(
                    $this->l('Invalid order state. ID: %d', 'ChangeStateOrder'),
                    (int) $idOrderState
                ),
                'Order',
                $idOrder
            );

            return;
        }

        ProcessLoggerHandler::logInfo(
            $logPrefix .
            sprintf(
                $this->l('Rule triggered. Set order %s to specified status.', 'ChangeStateOrder'),
                $idOrder
            ),
            'Order',
            $idOrder
        );

        if ($psOrder->current_state == $idOrderState) {
            return;
        }

        // Set order to CANCELED
        $history = new OrderHistory();
        $history->id_order = $idOrder;
        $use_existings_payment = true;
        $history->changeIdOrderState((int) $idOrderState, $psOrder, $use_existings_payment);
        // Save all changes
        $history->addWithemail();
    }

    /**
     * {@inheritdoc}
     */
    public function getConditions()
    {
        return $this->l('No specific conditions.', 'ChangeStateOrder');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->l('Set order to specific status after the process.', 'ChangeStateOrder');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationSubform()
    {
        $context = \Context::getContext();
        $statuses = OrderState::getOrderStates((int) $context->language->id);
        array_unshift($statuses, [
          'id_order_state' => '',
          'name' => '',
        ]);

        return [
            [
                'type' => 'select',
                'label' => $this->l('After a sucessfull order import, turn this order status into ', 'ChangeStateOrder'),
                'name' => 'end_order_state',
                'options' => [
                    'query' => $statuses,
                    'id' => 'id_order_state',
                    'name' => 'name',
                ],
                'required' => false,
            ],
        ];
    }

    protected function getIdOrderState()
    {
        if (false == empty($this->configuration['end_order_state'])) {
            return (int) $this->configuration['end_order_state'];
        }

        return 0;
    }
}
