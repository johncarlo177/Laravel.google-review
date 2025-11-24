<?php

namespace App\Support\PaymentProcessors\Api;

use App\Support\System\Traits\WriteLogs;
use \PostFinanceCheckout\Sdk\ApiClient;
use Throwable;

class PostFinanceApi
{
    use WriteLogs;

    private $spaceId = '', $userId = '', $secret = '';

    private ?ApiClient $client = null;

    public function __construct($spaceId, $userId, $secret)
    {
        $this->spaceId = $spaceId;
        $this->userId = $userId;
        $this->secret = $secret;

        try {
            $this->logDebug(
                'Creating API client with the following credentials: userId = %s, spaceId = %s, secret = %s',
                $this->userId,
                $this->spaceId,
                $this->secret
            );

            $this->client = new ApiClient($this->userId, $this->secret);
        } catch (Throwable $th) {
            $this->logWarning('Error creating API client. %s', $th->getMessage());
        }
    }

    public function createPaymentPageForProduct(
        $name,
        $id,
        $sku,
        $currency,
        $amountIncludingTax,
        $quantity = 1,
    ) {
        $lineItem = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
        $lineItem->setName($name);
        $lineItem->setUniqueId($id);
        $lineItem->setSku($sku);
        $lineItem->setQuantity($quantity);
        $lineItem->setAmountIncludingTax($amountIncludingTax);
        $lineItem->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::PRODUCT);


        $transactionPayload = new \PostFinanceCheckout\Sdk\Model\TransactionCreate();
        $transactionPayload->setCurrency($currency);
        $transactionPayload->setLineItems(array($lineItem));
        $transactionPayload->setAutoConfirmationEnabled(true);

        $transaction = $this->transactionService()->create($this->spaceId, $transactionPayload);

        // Create Payment Page URL:
        $redirectUrl = $this->client
            ->getTransactionPaymentPageService()
            ->paymentPageUrl(
                $this->spaceId,
                $transaction->getId()
            );

        return $redirectUrl;
    }

    private function transactionService()
    {
        return $this->client->getTransactionService();
    }
}
