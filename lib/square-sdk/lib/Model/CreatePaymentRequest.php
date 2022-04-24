<?php
/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace SquareConnect\Model;

use \ArrayAccess;
/**
 * CreatePaymentRequest Class Doc Comment
 *
 * @category Class
 * @package  SquareConnect
 * @author   Square Inc.
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 * @link     https://squareup.com/developers
 */
class CreatePaymentRequest implements ArrayAccess
{
    /**
      * Array of property to type mappings. Used for (de)serialization 
      * @var string[]
      */
    static $swaggerTypes = array(
        'source_id' => 'string',
        'idempotency_key' => 'string',
        'amount_money' => '\SquareConnect\Model\Money',
        'tip_money' => '\SquareConnect\Model\Money',
        'app_fee_money' => '\SquareConnect\Model\Money',
        'delay_duration' => 'string',
        'autocomplete' => 'bool',
        'order_id' => 'string',
        'customer_id' => 'string',
        'location_id' => 'string',
        'reference_id' => 'string',
        'verification_token' => 'string',
        'accept_partial_authorization' => 'bool',
        'buyer_email_address' => 'string',
        'billing_address' => '\SquareConnect\Model\Address',
        'shipping_address' => '\SquareConnect\Model\Address',
        'note' => 'string',
        'statement_description_identifier' => 'string'
    );
  
    /** 
      * Array of attributes where the key is the local name, and the value is the original name
      * @var string[] 
      */
    static $attributeMap = array(
        'source_id' => 'source_id',
        'idempotency_key' => 'idempotency_key',
        'amount_money' => 'amount_money',
        'tip_money' => 'tip_money',
        'app_fee_money' => 'app_fee_money',
        'delay_duration' => 'delay_duration',
        'autocomplete' => 'autocomplete',
        'order_id' => 'order_id',
        'customer_id' => 'customer_id',
        'location_id' => 'location_id',
        'reference_id' => 'reference_id',
        'verification_token' => 'verification_token',
        'accept_partial_authorization' => 'accept_partial_authorization',
        'buyer_email_address' => 'buyer_email_address',
        'billing_address' => 'billing_address',
        'shipping_address' => 'shipping_address',
        'note' => 'note',
        'statement_description_identifier' => 'statement_description_identifier'
    );
  
    /**
      * Array of attributes to setter functions (for deserialization of responses)
      * @var string[]
      */
    static $setters = array(
        'source_id' => 'setSourceId',
        'idempotency_key' => 'setIdempotencyKey',
        'amount_money' => 'setAmountMoney',
        'tip_money' => 'setTipMoney',
        'app_fee_money' => 'setAppFeeMoney',
        'delay_duration' => 'setDelayDuration',
        'autocomplete' => 'setAutocomplete',
        'order_id' => 'setOrderId',
        'customer_id' => 'setCustomerId',
        'location_id' => 'setLocationId',
        'reference_id' => 'setReferenceId',
        'verification_token' => 'setVerificationToken',
        'accept_partial_authorization' => 'setAcceptPartialAuthorization',
        'buyer_email_address' => 'setBuyerEmailAddress',
        'billing_address' => 'setBillingAddress',
        'shipping_address' => 'setShippingAddress',
        'note' => 'setNote',
        'statement_description_identifier' => 'setStatementDescriptionIdentifier'
    );
  
    /**
      * Array of attributes to getter functions (for serialization of requests)
      * @var string[]
      */
    static $getters = array(
        'source_id' => 'getSourceId',
        'idempotency_key' => 'getIdempotencyKey',
        'amount_money' => 'getAmountMoney',
        'tip_money' => 'getTipMoney',
        'app_fee_money' => 'getAppFeeMoney',
        'delay_duration' => 'getDelayDuration',
        'autocomplete' => 'getAutocomplete',
        'order_id' => 'getOrderId',
        'customer_id' => 'getCustomerId',
        'location_id' => 'getLocationId',
        'reference_id' => 'getReferenceId',
        'verification_token' => 'getVerificationToken',
        'accept_partial_authorization' => 'getAcceptPartialAuthorization',
        'buyer_email_address' => 'getBuyerEmailAddress',
        'billing_address' => 'getBillingAddress',
        'shipping_address' => 'getShippingAddress',
        'note' => 'getNote',
        'statement_description_identifier' => 'getStatementDescriptionIdentifier'
    );
  
    /**
      * $source_id The ID for the source of funds for this payment.  This can be a nonce generated by the Payment Form or a card on file made with the Customers API.
      * @var string
      */
    protected $source_id;
    /**
      * $idempotency_key A unique string that identifies this CreatePayment request. Keys can be any valid string but must be unique for every CreatePayment request.  Max: 45 characters  See [Idempotency keys](https://developer.squareup.com/docs/basics/api101/idempotency) for more information.
      * @var string
      */
    protected $idempotency_key;
    /**
      * $amount_money The amount of money to accept for this payment, not including `tip_money`.  Must be specified in the smallest denomination of the applicable currency. For example, US dollar amounts are specified in cents. See [Working with monetary amounts](https://developer.squareup.com/docs/build-basics/working-with-monetary-amounts) for details.  The currency code must match the currency associated with the business that is accepting the payment.
      * @var \SquareConnect\Model\Money
      */
    protected $amount_money;
    /**
      * $tip_money The amount designated as a tip, in addition to `amount_money`  Must be specified in the smallest denomination of the applicable currency. For example, US dollar amounts are specified in cents. See [Working with monetary amounts](https://developer.squareup.com/docs/build-basics/working-with-monetary-amounts) for details.  The currency code must match the currency associated with the business that is accepting the payment.
      * @var \SquareConnect\Model\Money
      */
    protected $tip_money;
    /**
      * $app_fee_money The amount of money the developer is taking as a fee for facilitating the payment on behalf of the seller.  Cannot be more than 90% of the total amount of the Payment.  Must be specified in the smallest denomination of the applicable currency. For example, US dollar amounts are specified in cents. See [Working with monetary amounts](https://developer.squareup.com/docs/build-basics/working-with-monetary-amounts) for details.  The fee currency code must match the currency associated with the merchant that is accepting the payment. The application must be from a developer account in the same country, and using the same currency code, as the merchant.  For more information about the application fee scenario, see [Collect Fees](https://developer.squareup.com/docs/payments-api/take-payments-and-collect-fees).
      * @var \SquareConnect\Model\Money
      */
    protected $app_fee_money;
    /**
      * $delay_duration The duration of time after the payment's creation when Square automatically cancels the payment. This automatic cancellation applies only to payments that don't reach a terminal state (COMPLETED, CANCELED, or FAILED) before the `delay_duration` time period.  This parameter should be specified as a time duration, in RFC 3339 format, with a minimum value of 1 minute.  Notes: This feature is only supported for card payments. This parameter can only be set for a delayed capture payment (`autocomplete=false`).  Default:  - Card Present payments: \"PT36H\" (36 hours) from the creation time. - Card Not Present payments: \"P7D\" (7 days) from the creation time.
      * @var string
      */
    protected $delay_duration;
    /**
      * $autocomplete If set to `true`, this payment will be completed when possible. If set to `false`, this payment will be held in an approved state until either explicitly completed (captured) or canceled (voided). For more information, see [Delayed Payments](https://developer.squareup.com/docs/payments-api/take-payments#delayed-payments).  Default: true
      * @var bool
      */
    protected $autocomplete;
    /**
      * $order_id Associate a previously created order with this payment
      * @var string
      */
    protected $order_id;
    /**
      * $customer_id The ID of the customer associated with the payment. Required if the `source_id` refers to a card on file created using the Customers API.
      * @var string
      */
    protected $customer_id;
    /**
      * $location_id The location ID to associate with the payment. If not specified, the default location is used.
      * @var string
      */
    protected $location_id;
    /**
      * $reference_id A user-defined ID to associate with the payment. You can use this field to associate the payment to an entity in an external system. For example, you might specify an order ID that is generated by a third-party shopping cart.  Limit 40 characters.
      * @var string
      */
    protected $reference_id;
    /**
      * $verification_token An identifying token generated by `SqPaymentForm.verifyBuyer()`. Verification tokens encapsulate customer device information and 3-D Secure challenge results to indicate that Square has verified the buyer identity.  See the [SCA Overview](https://developer.squareup.com/docs/sca-overview).
      * @var string
      */
    protected $verification_token;
    /**
      * $accept_partial_authorization If set to true and charging a Square Gift Card, a payment may be returned with amount_money equal to less than what was requested.  Example, a request for $20 when charging a Square Gift Card with balance of $5 wil result in an APPROVED payment of $5.  You may choose to prompt the buyer for an additional payment to cover the remainder, or cancel the gift card payment.  Cannot be `true` when `autocomplete = true`.  For more information, see [Partial amount with Square gift cards](https://developer.squareup.com/docs/payments-api/take-payments#partial-payment-gift-card).  Default: false
      * @var bool
      */
    protected $accept_partial_authorization;
    /**
      * $buyer_email_address The buyer's e-mail address
      * @var string
      */
    protected $buyer_email_address;
    /**
      * $billing_address The buyer's billing address.
      * @var \SquareConnect\Model\Address
      */
    protected $billing_address;
    /**
      * $shipping_address The buyer's shipping address.
      * @var \SquareConnect\Model\Address
      */
    protected $shipping_address;
    /**
      * $note An optional note to be entered by the developer when creating a payment  Limit 500 characters.
      * @var string
      */
    protected $note;
    /**
      * $statement_description_identifier Optional additional payment information to include on the customer's card statement as part of statement description. This can be, for example, an invoice number, ticket number, or short description that uniquely identifies the purchase.  Limit 20 characters.  Note that the statement_description_identifier may get truncated on the statement description to fit the required information including the Square identifier (SQ *) and name of the merchant taking the payment.
      * @var string
      */
    protected $statement_description_identifier;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property value initializing the model
     */
    public function __construct(array $data = null)
    {
        if ($data != null) {
            if (isset($data["source_id"])) {
              $this->source_id = $data["source_id"];
            } else {
              $this->source_id = null;
            }
            if (isset($data["idempotency_key"])) {
              $this->idempotency_key = $data["idempotency_key"];
            } else {
              $this->idempotency_key = null;
            }
            if (isset($data["amount_money"])) {
              $this->amount_money = $data["amount_money"];
            } else {
              $this->amount_money = null;
            }
            if (isset($data["tip_money"])) {
              $this->tip_money = $data["tip_money"];
            } else {
              $this->tip_money = null;
            }
            if (isset($data["app_fee_money"])) {
              $this->app_fee_money = $data["app_fee_money"];
            } else {
              $this->app_fee_money = null;
            }
            if (isset($data["delay_duration"])) {
              $this->delay_duration = $data["delay_duration"];
            } else {
              $this->delay_duration = null;
            }
            if (isset($data["autocomplete"])) {
              $this->autocomplete = $data["autocomplete"];
            } else {
              $this->autocomplete = null;
            }
            if (isset($data["order_id"])) {
              $this->order_id = $data["order_id"];
            } else {
              $this->order_id = null;
            }
            if (isset($data["customer_id"])) {
              $this->customer_id = $data["customer_id"];
            } else {
              $this->customer_id = null;
            }
            if (isset($data["location_id"])) {
              $this->location_id = $data["location_id"];
            } else {
              $this->location_id = null;
            }
            if (isset($data["reference_id"])) {
              $this->reference_id = $data["reference_id"];
            } else {
              $this->reference_id = null;
            }
            if (isset($data["verification_token"])) {
              $this->verification_token = $data["verification_token"];
            } else {
              $this->verification_token = null;
            }
            if (isset($data["accept_partial_authorization"])) {
              $this->accept_partial_authorization = $data["accept_partial_authorization"];
            } else {
              $this->accept_partial_authorization = null;
            }
            if (isset($data["buyer_email_address"])) {
              $this->buyer_email_address = $data["buyer_email_address"];
            } else {
              $this->buyer_email_address = null;
            }
            if (isset($data["billing_address"])) {
              $this->billing_address = $data["billing_address"];
            } else {
              $this->billing_address = null;
            }
            if (isset($data["shipping_address"])) {
              $this->shipping_address = $data["shipping_address"];
            } else {
              $this->shipping_address = null;
            }
            if (isset($data["note"])) {
              $this->note = $data["note"];
            } else {
              $this->note = null;
            }
            if (isset($data["statement_description_identifier"])) {
              $this->statement_description_identifier = $data["statement_description_identifier"];
            } else {
              $this->statement_description_identifier = null;
            }
        }
    }
    /**
     * Gets source_id
     * @return string
     */
    public function getSourceId()
    {
        return $this->source_id;
    }
  
    /**
     * Sets source_id
     * @param string $source_id The ID for the source of funds for this payment.  This can be a nonce generated by the Payment Form or a card on file made with the Customers API.
     * @return $this
     */
    public function setSourceId($source_id)
    {
        $this->source_id = $source_id;
        return $this;
    }
    /**
     * Gets idempotency_key
     * @return string
     */
    public function getIdempotencyKey()
    {
        return $this->idempotency_key;
    }
  
    /**
     * Sets idempotency_key
     * @param string $idempotency_key A unique string that identifies this CreatePayment request. Keys can be any valid string but must be unique for every CreatePayment request.  Max: 45 characters  See [Idempotency keys](https://developer.squareup.com/docs/basics/api101/idempotency) for more information.
     * @return $this
     */
    public function setIdempotencyKey($idempotency_key)
    {
        $this->idempotency_key = $idempotency_key;
        return $this;
    }
    /**
     * Gets amount_money
     * @return \SquareConnect\Model\Money
     */
    public function getAmountMoney()
    {
        return $this->amount_money;
    }
  
    /**
     * Sets amount_money
     * @param \SquareConnect\Model\Money $amount_money The amount of money to accept for this payment, not including `tip_money`.  Must be specified in the smallest denomination of the applicable currency. For example, US dollar amounts are specified in cents. See [Working with monetary amounts](https://developer.squareup.com/docs/build-basics/working-with-monetary-amounts) for details.  The currency code must match the currency associated with the business that is accepting the payment.
     * @return $this
     */
    public function setAmountMoney($amount_money)
    {
        $this->amount_money = $amount_money;
        return $this;
    }
    /**
     * Gets tip_money
     * @return \SquareConnect\Model\Money
     */
    public function getTipMoney()
    {
        return $this->tip_money;
    }
  
    /**
     * Sets tip_money
     * @param \SquareConnect\Model\Money $tip_money The amount designated as a tip, in addition to `amount_money`  Must be specified in the smallest denomination of the applicable currency. For example, US dollar amounts are specified in cents. See [Working with monetary amounts](https://developer.squareup.com/docs/build-basics/working-with-monetary-amounts) for details.  The currency code must match the currency associated with the business that is accepting the payment.
     * @return $this
     */
    public function setTipMoney($tip_money)
    {
        $this->tip_money = $tip_money;
        return $this;
    }
    /**
     * Gets app_fee_money
     * @return \SquareConnect\Model\Money
     */
    public function getAppFeeMoney()
    {
        return $this->app_fee_money;
    }
  
    /**
     * Sets app_fee_money
     * @param \SquareConnect\Model\Money $app_fee_money The amount of money the developer is taking as a fee for facilitating the payment on behalf of the seller.  Cannot be more than 90% of the total amount of the Payment.  Must be specified in the smallest denomination of the applicable currency. For example, US dollar amounts are specified in cents. See [Working with monetary amounts](https://developer.squareup.com/docs/build-basics/working-with-monetary-amounts) for details.  The fee currency code must match the currency associated with the merchant that is accepting the payment. The application must be from a developer account in the same country, and using the same currency code, as the merchant.  For more information about the application fee scenario, see [Collect Fees](https://developer.squareup.com/docs/payments-api/take-payments-and-collect-fees).
     * @return $this
     */
    public function setAppFeeMoney($app_fee_money)
    {
        $this->app_fee_money = $app_fee_money;
        return $this;
    }
    /**
     * Gets delay_duration
     * @return string
     */
    public function getDelayDuration()
    {
        return $this->delay_duration;
    }
  
    /**
     * Sets delay_duration
     * @param string $delay_duration The duration of time after the payment's creation when Square automatically cancels the payment. This automatic cancellation applies only to payments that don't reach a terminal state (COMPLETED, CANCELED, or FAILED) before the `delay_duration` time period.  This parameter should be specified as a time duration, in RFC 3339 format, with a minimum value of 1 minute.  Notes: This feature is only supported for card payments. This parameter can only be set for a delayed capture payment (`autocomplete=false`).  Default:  - Card Present payments: \"PT36H\" (36 hours) from the creation time. - Card Not Present payments: \"P7D\" (7 days) from the creation time.
     * @return $this
     */
    public function setDelayDuration($delay_duration)
    {
        $this->delay_duration = $delay_duration;
        return $this;
    }
    /**
     * Gets autocomplete
     * @return bool
     */
    public function getAutocomplete()
    {
        return $this->autocomplete;
    }
  
    /**
     * Sets autocomplete
     * @param bool $autocomplete If set to `true`, this payment will be completed when possible. If set to `false`, this payment will be held in an approved state until either explicitly completed (captured) or canceled (voided). For more information, see [Delayed Payments](https://developer.squareup.com/docs/payments-api/take-payments#delayed-payments).  Default: true
     * @return $this
     */
    public function setAutocomplete($autocomplete)
    {
        $this->autocomplete = $autocomplete;
        return $this;
    }
    /**
     * Gets order_id
     * @return string
     */
    public function getOrderId()
    {
        return $this->order_id;
    }
  
    /**
     * Sets order_id
     * @param string $order_id Associate a previously created order with this payment
     * @return $this
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }
    /**
     * Gets customer_id
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }
  
    /**
     * Sets customer_id
     * @param string $customer_id The ID of the customer associated with the payment. Required if the `source_id` refers to a card on file created using the Customers API.
     * @return $this
     */
    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        return $this;
    }
    /**
     * Gets location_id
     * @return string
     */
    public function getLocationId()
    {
        return $this->location_id;
    }
  
    /**
     * Sets location_id
     * @param string $location_id The location ID to associate with the payment. If not specified, the default location is used.
     * @return $this
     */
    public function setLocationId($location_id)
    {
        $this->location_id = $location_id;
        return $this;
    }
    /**
     * Gets reference_id
     * @return string
     */
    public function getReferenceId()
    {
        return $this->reference_id;
    }
  
    /**
     * Sets reference_id
     * @param string $reference_id A user-defined ID to associate with the payment. You can use this field to associate the payment to an entity in an external system. For example, you might specify an order ID that is generated by a third-party shopping cart.  Limit 40 characters.
     * @return $this
     */
    public function setReferenceId($reference_id)
    {
        $this->reference_id = $reference_id;
        return $this;
    }
    /**
     * Gets verification_token
     * @return string
     */
    public function getVerificationToken()
    {
        return $this->verification_token;
    }
  
    /**
     * Sets verification_token
     * @param string $verification_token An identifying token generated by `SqPaymentForm.verifyBuyer()`. Verification tokens encapsulate customer device information and 3-D Secure challenge results to indicate that Square has verified the buyer identity.  See the [SCA Overview](https://developer.squareup.com/docs/sca-overview).
     * @return $this
     */
    public function setVerificationToken($verification_token)
    {
        $this->verification_token = $verification_token;
        return $this;
    }
    /**
     * Gets accept_partial_authorization
     * @return bool
     */
    public function getAcceptPartialAuthorization()
    {
        return $this->accept_partial_authorization;
    }
  
    /**
     * Sets accept_partial_authorization
     * @param bool $accept_partial_authorization If set to true and charging a Square Gift Card, a payment may be returned with amount_money equal to less than what was requested.  Example, a request for $20 when charging a Square Gift Card with balance of $5 wil result in an APPROVED payment of $5.  You may choose to prompt the buyer for an additional payment to cover the remainder, or cancel the gift card payment.  Cannot be `true` when `autocomplete = true`.  For more information, see [Partial amount with Square gift cards](https://developer.squareup.com/docs/payments-api/take-payments#partial-payment-gift-card).  Default: false
     * @return $this
     */
    public function setAcceptPartialAuthorization($accept_partial_authorization)
    {
        $this->accept_partial_authorization = $accept_partial_authorization;
        return $this;
    }
    /**
     * Gets buyer_email_address
     * @return string
     */
    public function getBuyerEmailAddress()
    {
        return $this->buyer_email_address;
    }
  
    /**
     * Sets buyer_email_address
     * @param string $buyer_email_address The buyer's e-mail address
     * @return $this
     */
    public function setBuyerEmailAddress($buyer_email_address)
    {
        $this->buyer_email_address = $buyer_email_address;
        return $this;
    }
    /**
     * Gets billing_address
     * @return \SquareConnect\Model\Address
     */
    public function getBillingAddress()
    {
        return $this->billing_address;
    }
  
    /**
     * Sets billing_address
     * @param \SquareConnect\Model\Address $billing_address The buyer's billing address.
     * @return $this
     */
    public function setBillingAddress($billing_address)
    {
        $this->billing_address = $billing_address;
        return $this;
    }
    /**
     * Gets shipping_address
     * @return \SquareConnect\Model\Address
     */
    public function getShippingAddress()
    {
        return $this->shipping_address;
    }
  
    /**
     * Sets shipping_address
     * @param \SquareConnect\Model\Address $shipping_address The buyer's shipping address.
     * @return $this
     */
    public function setShippingAddress($shipping_address)
    {
        $this->shipping_address = $shipping_address;
        return $this;
    }
    /**
     * Gets note
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }
  
    /**
     * Sets note
     * @param string $note An optional note to be entered by the developer when creating a payment  Limit 500 characters.
     * @return $this
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }
    /**
     * Gets statement_description_identifier
     * @return string
     */
    public function getStatementDescriptionIdentifier()
    {
        return $this->statement_description_identifier;
    }
  
    /**
     * Sets statement_description_identifier
     * @param string $statement_description_identifier Optional additional payment information to include on the customer's card statement as part of statement description. This can be, for example, an invoice number, ticket number, or short description that uniquely identifies the purchase.  Limit 20 characters.  Note that the statement_description_identifier may get truncated on the statement description to fit the required information including the Square identifier (SQ *) and name of the merchant taking the payment.
     * @return $this
     */
    public function setStatementDescriptionIdentifier($statement_description_identifier)
    {
        $this->statement_description_identifier = $statement_description_identifier;
        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     * @param  integer $offset Offset 
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }
  
    /**
     * Gets offset.
     * @param  integer $offset Offset 
     * @return mixed 
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }
  
    /**
     * Sets value based on offset.
     * @param  integer $offset Offset 
     * @param  mixed   $value  Value to be set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }
  
    /**
     * Unsets offset.
     * @param  integer $offset Offset 
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
  
    /**
     * Gets the string presentation of the object
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) {
            return json_encode(\SquareConnect\ObjectSerializer::sanitizeForSerialization($this), JSON_PRETTY_PRINT);
        } else {
            return json_encode(\SquareConnect\ObjectSerializer::sanitizeForSerialization($this));
        }
    }
}
