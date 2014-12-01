<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Walpay Gateway Transaction Processing Services
 *
 * PHP version 5
 *
 * @copyright     Copyright (c) 2013, Louie Miranda
 * @author        Louie Miranda <lmiranda@gmail.com>
 */

require_once dirname(__FILE__) . '/Library/PaymentGateway.php';

/**
 * Synchronous cardholder transactions are processed via all involved parties, from
 * cardholder to bank, in a synchronous seamless manner.
 *
 * Asynchronous cardholder transactions are more complex and require the cardholder to
 * be redirected to all involved parties for 3D Secure authentication. 3D secure
 * authentication requires the card to be enrolled in a 3D Secure program and that all
 * parties involved in the processing of the transaction must have the capability to process
 * 3D Secure transactions.
 *
 * Continuous authority transactions are performed in two phases. A successful initial
 * continuous authority authorise or purchase transaction must be successfully performed
 * which requires cardholder interaction before the iteration of repeat continuous authority
 * transactions. A reference to the initial continuous authority transaction is supplied when
 * iterating repeat transactions.
 *
 * CURRENCY CODES:
 * - http://www.iso.org/iso/home/store/catalogue_tc/catalogue_detail.htm?csnumber=46121
 * - http://en.wikipedia.org/wiki/ISO_4217
 * COUNTRY CODES: http://www.iso.org/iso/country_names_and_code_elements
 */

class Custom_WalpayMC extends PaymentGateway {

	private $_Version = '1.4.0.1'; // Based on API doc

//	private $_URL = 'https://int.cc-gw-wal.com'; // INTEGRATION
	private $_URL = 'https://prod.cc-gw-wal.com'; // PRODUCTION

//	private $RedirectAsynchronous = 'https://wgredirect-int.cc-gw-wal.com/U00A4_0100/Asynchronous/KG'; // INTEGRATION
	private $RedirectAsynchronous = 'https://wgredirect.cc-gw-wal.com/U00A4_0100/Asynchronous/KG'; // PRODUCTION

	private $_Path = '';

	//private $Simulate = FALSE; // Simulate transactions - TRUE TO ALLOW INVALID TRANS, FALSE WHEN PRODUCTION

	protected $_MerchantName;
	protected $_MerchantPin;

	private $_supportedCurrency = array();
	private $_supportedCreditCard = array();

	/**
	 * Variables for every orders
	 */
	protected $OrderId; // = '001234';
	protected $OrderDescription; // = 'WorldBooks order 1234';
	protected $Amount; // = 100;
	protected $CurrencyCode; // = 826;
	protected $MerchantDateTime; // = '2012-10-05 11:22:19';
	protected $AccountNumber; // = 'hr385645';
	protected $Name; // = 'John';
	protected $Surname; // = 'Doe';
	protected $Address; // = '1600 Pennsylvania Ave NW';
	protected $City; // = 'Washington';
	protected $State; // = 'DC';
	protected $Zip; // = '20500';
	protected $Country; // = 'US';
	protected $Telephone; // = '2024561111';
	protected $Email; // = 'lmiranda@gmail.com';
	protected $DOB; // = '1986-08-12';
	protected $ClientIP; // = '8.8.8.8';
	protected $PAN; // = '4444333322221111';
	protected $ExpiryDate; // = '07-2014';
	protected $CardHolderName; // = 'John Doe';
	protected $CVC2; // = '111';
	protected $StartDate;
	protected $IssueNumber;

	protected $VE; // = 0;
	protected $PA; // = 5;
	protected $ECI; // = 5;
	protected $XID; // = 'jdh3hskAWhtjk23430flsjelwjw=';
	protected $CAVV; // = 'jdh3hskAWhtjk23430flsjelwjr=';

	protected $NotificationURL;
	protected $RedirectURL;

	protected $RefID;

	function __construct($environment) {

		if ($environment == "test") {
			$this->_URL = 'https://int.cc-gw-wal.com'; // INTEGRATION
			$this->RedirectAsynchronous = 'https://wgredirect-int.cc-gw-wal.com/U00A4_0100/Asynchronous/KG';
			$this->Simulate = TRUE; // Simulate transactions - TRUE TO ALLOW INVALID TRANS, FALSE WHEN PRODUCTION
		}
		else if ($environment == "live") {
			$this->_URL = 'https://prod.cc-gw-wal.com'; // PRODUCTION
			$this->RedirectAsynchronous = 'https://wgredirect.cc-gw-wal.com/U00A4_0100/Asynchronous/KG';
			$this->Simulate = FALSE; // Simulate transactions - TRUE TO ALLOW INVALID TRANS, FALSE WHEN PRODUCTION
		}
		else {
			die("Missing environment, please check your data.");
		}

	}

	/**
	 * SETTER / GETTER
	 */
	public function setMerchantName($value) {
		$this->_MerchantName = $value;
	}

	protected function getMerchantName() {
		return $this->_MerchantName;
	}


	public function setMerchantPin($value) {
		$this->_MerchantPin = $value;
	}

	public function getMerchantPin() {
		return $this->_MerchantPin;
	}


	/* Asynchronous Redirect for 3DS */
	public function setRedirectAsynchronous($value) {
		$this->RedirectAsynchronous = $value;
	}

	public function getRedirectAsynchronous() {
		return $this->RedirectAsynchronous;
	}


	/* Merchant unique transaction identifier */
	public function setOrderId($value) {
		$this->OrderId = $value;
	}

	public function getOrderId() {
		return $this->OrderId;
	}


	/* Transaction description */
	public function setOrderDescription($value) {
		$this->OrderDescription = $value;
	}

	public function getOrderDescription() {
		return $this->OrderDescription;
	}


	/* Transaction amount */
	public function setAmount($value) {
		//$this->Amount = $value;
		$this->Amount = $value * 100;
	}

	public function getAmount() {
		return $this->Amount;
	}


	/* Transaction 3 digit ISO 4217 currency code */
	public function setCurrencyCode($value) {
		$this->CurrencyCode = $value;
	}

	public function getCurrencyCode() {
		return $this->CurrencyCode;
	}


	/* Merchant timestamp of the transaction */
	public function setMerchantDateTime($value) {
		$this->MerchantDateTime = $value;
	}

	public function getMerchantDateTime() {
		return $this->MerchantDateTime;
	}


	/* Cardholder's account number in merchant system */
	public function setAccountNumber($value) {
		$this->AccountNumber = $value;
	}

	public function getAccountNumber() {
		return $this->AccountNumber;
	}


	/* Cardholder's first name */
	public function setName($value) {
		$this->Name = $value;
	}

	public function getName() {
		return $this->Name;
	}


	/* Cardholder’s last name */
	public function setSurname($value) {
		$this->Surname = $value;
	}

	public function getSurname() {
		return $this->Surname;
	}


	/* Cardholder’s billing address first line */
	public function setAddress($value) {
		$this->Address = $value;
	}

	public function getAddress() {
		return $this->Address;
	}


	/* Cardholder’s billing address city */
	public function setCity($value) {
		$this->City = $value;
	}

	public function getCity() {
		return $this->City;
	}


	/* Cardholder’s billing address state */
	public function setState($value) {
		$this->State = $value;
	}

	public function getState() {
		return $this->State;
	}


	/* Cardholder’s billing address postal code */
	public function setZip($value) {
		$this->Zip = $value;
	}

	public function getZip() {
		return $this->Zip;
	}


	/* Cardholder’s billing address country code */
	public function setCountry($value) {
		$this->Country = $value;
	}

	public function getCountry() {
		return $this->Country;
	}


	/* Cardholder’s telephone number */
	public function setTelephone($value) {
		$this->Telephone = $value;
	}

	public function getTelephone() {
		return $this->Telephone;
	}


	/* Cardholder’s email address */
	public function setEmail($value) {
		$this->Email = $value;
	}

	public function getEmail() {
		return $this->Email;
	}


	/* Cardholder’s date of birth */
	public function setDOB($value) {
		$this->DOB = $value;
	}

	public function getDOB() {
		return $this->DOB;
	}


	/* IPv4 address of cardholder */
	public function setClientIP($value) {
		$this->ClientIP = $value;
	}

	public function getClientIP() {
		return $this->ClientIP;
	}


	/* Card number */
	public function setPAN($value) {
		$this->PAN = $value;
	}

	public function getPAN() {
		return $this->PAN;
	}


	/* Card expiry date */
	public function setExpiryDate($value) {
		$this->ExpiryDate = $value;
	}

	public function getExpiryDate() {
		return $this->ExpiryDate;
	}


	/* Name on card */
	public function setCardHolderName($value) {
		$this->CardHolderName = $value;
	}

	public function getCardHolderName() {
		return $this->CardHolderName;
	}


	/* Card CVC2 / CVV number */
	public function setCVC2($value) {
		$this->CVC2 = $value;
	}

	public function getCVC2() {
		return $this->CVC2;
	}


	/* Card start date */
	public function setStartDate($value) {
		$this->StartDate = $value;
	}

	public function getStartDate() {
		return $this->StartDate;
	}


	/* Card issue number */
	public function setIssueNumber($value) {
		$this->IssueNumber = $value;
	}

	public function getIssueNumber() {
		return $this->IssueNumber;
	}


	/* 3D secure verify enrolment result from 3D MPI */
	public function setVE($value) {
		$this->VE = $value;
	}

	public function getVE() {
		return $this->VE;
	}


	/* 3D secure payer authentication result from 3D MPI */
	public function setPA($value) {
		$this->PA = $value;
	}

	public function getPA() {
		return $this->PA;
	}


	/* 3D secure electronic commerce indicator from 3D MPI */
	public function setECI($value) {
		$this->ECI = $value;
	}

	public function getECI() {
		return $this->ECI;
	}


	/* 3D secure transaction identifier from 3D MPI */
	public function setXID($value) {
		$this->XID = $value;
	}

	public function getXID() {
		return $this->XID;
	}


	/* 3D secure cardholder verification value from 3D MPI */
	public function setCAVV($value) {
		$this->CAVV = $value;
	}

	public function getCAVV() {
		return $this->CAVV;
	}


	/* Merchant URL to which the asynchronous notification is sent */
	public function setNotificationURL($value) {
		$this->NotificationURL = $value;
	}

	public function getNotificationURL() {
		return $this->NotificationURL;
	}


	/* Merhant URL to which user is redirected upon transaction completion */
	public function setRedirectURL($value) {
		$this->RedirectURL = $value;
	}

	public function getRedirectURL() {
		return $this->RedirectURL;
	}


	/* Walpay unique transaction identifier */
	public function setRefID($value) {
		$this->RefID = $value;
	}

	public function getRefID() {
		return $this->RefID;
	}

	/**
	 * Synchronous Authorise Request
	 *
	 * This transaction is used as the first stage of a two-stage Authorise/Capture Purchase.
	 * This will authorise the payment without actually moving the funds from the card. After
	 * Authorisation is performed, a Capture transaction is required to complete the payment
	 * and move funds.
	 *
	 * @see Doc pg 16
	 */
	public function syncAuthorise() {

		$_Path = '/U00A4_0100/Authorise/KG/Synchronous';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$clientInfo = $root->appendChild($xml->createElement("ClientInfo"));
		$clientInfo->appendChild($xml->createElement('AccountNumber', $this->getAccountNumber()));
		$clientInfo->appendChild($xml->createElement('Name', $this->getName()));
		$clientInfo->appendChild($xml->createElement('Surname', $this->getSurname()));
		$clientInfo->appendChild($xml->createElement('Address', $this->getAddress()));
		$clientInfo->appendChild($xml->createElement('City', $this->getCity()));
		$clientInfo->appendChild($xml->createElement('State', $this->getState()));
		$clientInfo->appendChild($xml->createElement('Zip', $this->getZip()));
		$clientInfo->appendChild($xml->createElement('Country', $this->getCountry()));
		$clientInfo->appendChild($xml->createElement('Telephone', $this->getTelephone()));
		$clientInfo->appendChild($xml->createElement('Email', $this->getEmail()));
		$clientInfo->appendChild($xml->createElement('DOB', $this->getDOB()));
		$clientInfo->appendChild($xml->createElement('ClientIP', $this->getClientIP()));

		$paymentInfo = $root->appendChild($xml->createElement("PaymentInfo"));
		$paymentInfo->appendChild($xml->createElement('PAN', $this->getPAN()));
		$paymentInfo->appendChild($xml->createElement('ExpiryDate', $this->getExpiryDate()));
		$paymentInfo->appendChild($xml->createElement('CardHolderName', $this->getCardHolderName()));
		$paymentInfo->appendChild($xml->createElement('CVC2', $this->getCVC2()));
		$paymentInfo->appendChild($xml->createElement('StartDate', $this->getStartDate()));
		$paymentInfo->appendChild($xml->createElement('IssueNumber', $this->getIssueNumber()));

		$xml->formatOutput = TRUE;
		$resquestXML = $xml->saveXML();

		//print_r($resquestXML);

		$this->setUrl($this->_URL.$_Path);
		$responseXML = $this->postXml($resquestXML);

		return $responseXML;
	}

	/**
	 * Synchronous Authorise - 3D Secure authentication Request
	 *
	 * This transaction is used as the first stage of a two-stage Authorise/Capture Purchase.
	 * This will authorise the payment without actually moving the funds from the card. After
	 * Authorisation is performed, a Capture transaction is required to complete the payment
	 * and move funds.
	 *
	 * Prior to executing this transaction the cardholder must have been authenticated via a 3D
	 * secure mechanism and the successful results of that authentication provided in the
	 * transaction.
	 *
	 * @see Doc pg 20
	 */
	public function sync3DSAuthorise() {

		$_Path = '/U00A4_0100/Authorise/KG/Synchronous/3DS';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$clientInfo = $root->appendChild($xml->createElement("ClientInfo"));
		$clientInfo->appendChild($xml->createElement('AccountNumber', $this->getAccountNumber()));
		$clientInfo->appendChild($xml->createElement('Name', $this->getName()));
		$clientInfo->appendChild($xml->createElement('Surname', $this->getSurname()));
		$clientInfo->appendChild($xml->createElement('Address', $this->getAddress()));
		$clientInfo->appendChild($xml->createElement('City', $this->getCity()));
		$clientInfo->appendChild($xml->createElement('State', $this->getState()));
		$clientInfo->appendChild($xml->createElement('Zip', $this->getZip()));
		$clientInfo->appendChild($xml->createElement('Country', $this->getCountry()));
		$clientInfo->appendChild($xml->createElement('Telephone', $this->getTelephone()));
		$clientInfo->appendChild($xml->createElement('Email', $this->getEmail()));
		$clientInfo->appendChild($xml->createElement('DOB', $this->getDOB()));
		$clientInfo->appendChild($xml->createElement('ClientIP', $this->getClientIP()));

		$paymentInfo = $root->appendChild($xml->createElement("PaymentInfo"));
		$paymentInfo->appendChild($xml->createElement('PAN', $this->getPAN()));
		$paymentInfo->appendChild($xml->createElement('ExpiryDate', $this->getExpiryDate()));
		$paymentInfo->appendChild($xml->createElement('CardHolderName', $this->getCardHolderName()));
		$paymentInfo->appendChild($xml->createElement('CVC2', $this->getCVC2()));
		$paymentInfo->appendChild($xml->createElement('StartDate', $this->getStartDate()));
		$paymentInfo->appendChild($xml->createElement('IssueNumber', $this->getIssueNumber()));

		$secure3D = $root->appendChild($xml->createElement("Secure3D"));
		$secure3D->appendChild($xml->createElement('VE', $this->getVE()));
		$secure3D->appendChild($xml->createElement('PA', $this->getPA()));
		$secure3D->appendChild($xml->createElement('ECI', $this->getECI()));
		$secure3D->appendChild($xml->createElement('XID', $this->getXID()));
		$secure3D->appendChild($xml->createElement('CAVV', $this->getCAVV()));

		$xml->formatOutput = TRUE;
		$resquestXML = $xml->saveXML();

		$this->setUrl($this->_URL.$_Path);
		$responseXML = $this->postXml($resquestXML);

		return $responseXML;
	}

	/**
	 * Synchronous Purchase Request
	 *
	 * This transaction is used to authorise the payment and move funds from the card in a single transaction.
	 *
	 * @see Doc pg 25
	 */
	public function syncPurchase() {

		$_Path = '/U00A4_0100/Debit/KG/Synchronous';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$clientInfo = $root->appendChild($xml->createElement("ClientInfo"));
		$clientInfo->appendChild($xml->createElement('AccountNumber', $this->getAccountNumber()));
		$clientInfo->appendChild($xml->createElement('Name', $this->getName()));
		$clientInfo->appendChild($xml->createElement('Surname', $this->getSurname()));
		$clientInfo->appendChild($xml->createElement('Address', $this->getAddress()));
		$clientInfo->appendChild($xml->createElement('City', $this->getCity()));
		$clientInfo->appendChild($xml->createElement('State', $this->getState()));
		$clientInfo->appendChild($xml->createElement('Zip', $this->getZip()));
		$clientInfo->appendChild($xml->createElement('Country', $this->getCountry()));
		$clientInfo->appendChild($xml->createElement('Telephone', $this->getTelephone()));
		$clientInfo->appendChild($xml->createElement('Email', $this->getEmail()));
		$clientInfo->appendChild($xml->createElement('DOB', $this->getDOB()));
		$clientInfo->appendChild($xml->createElement('ClientIP', $this->getClientIP()));

		$paymentInfo = $root->appendChild($xml->createElement("PaymentInfo"));
		$paymentInfo->appendChild($xml->createElement('PAN', $this->getPAN()));
		$paymentInfo->appendChild($xml->createElement('ExpiryDate', $this->getExpiryDate()));
		$paymentInfo->appendChild($xml->createElement('CardHolderName', $this->getCardHolderName()));
		$paymentInfo->appendChild($xml->createElement('CVC2', $this->getCVC2()));
		$paymentInfo->appendChild($xml->createElement('StartDate', $this->getStartDate()));
		$paymentInfo->appendChild($xml->createElement('IssueNumber', $this->getIssueNumber()));

		$xml->formatOutput = TRUE;
		$resquestXML = $xml->saveXML();

		$responseXML = $this->postXml($resquestXML);

		return $responseXML;
	}

	/**
	 * Synchronous Purchase – 3D secure authentication Request
	 *
	 * This transaction is used to authorise the payment and move funds from the card in a single transaction.
	 *
	 * Prior to executing this transaction the cardholder must have been authenticated via a 3D
	 * secure mechanism and the successful results of that authentication provided in the transaction.
	 *
	 * @see Doc pg 29
	 */
	public function syncPurchase3DS() {

		$_Path = '/U00A4_0100/Debit/KG/Synchronous/3DS';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$clientInfo = $root->appendChild($xml->createElement("ClientInfo"));
		$clientInfo->appendChild($xml->createElement('AccountNumber', $this->getAccountNumber()));
		$clientInfo->appendChild($xml->createElement('Name', $this->getName()));
		$clientInfo->appendChild($xml->createElement('Surname', $this->getSurname()));
		$clientInfo->appendChild($xml->createElement('Address', $this->getAddress()));
		$clientInfo->appendChild($xml->createElement('City', $this->getCity()));
		$clientInfo->appendChild($xml->createElement('State', $this->getState()));
		$clientInfo->appendChild($xml->createElement('Zip', $this->getZip()));
		$clientInfo->appendChild($xml->createElement('Country', $this->getCountry()));
		$clientInfo->appendChild($xml->createElement('Telephone', $this->getTelephone()));
		$clientInfo->appendChild($xml->createElement('Email', $this->getEmail()));
		$clientInfo->appendChild($xml->createElement('DOB', $this->getDOB()));
		$clientInfo->appendChild($xml->createElement('ClientIP', $this->getClientIP()));

		$paymentInfo = $root->appendChild($xml->createElement("PaymentInfo"));
		$paymentInfo->appendChild($xml->createElement('PAN', $this->getPAN()));
		$paymentInfo->appendChild($xml->createElement('ExpiryDate', $this->getExpiryDate()));
		$paymentInfo->appendChild($xml->createElement('CardHolderName', $this->getCardHolderName()));
		$paymentInfo->appendChild($xml->createElement('CVC2', $this->getCVC2()));
		$paymentInfo->appendChild($xml->createElement('StartDate', $this->getStartDate()));
		$paymentInfo->appendChild($xml->createElement('IssueNumber', $this->getIssueNumber()));

		$secure3D = $root->appendChild($xml->createElement("Secure3D"));
		$secure3D->appendChild($xml->createElement('VE', $this->getVE()));
		$secure3D->appendChild($xml->createElement('PA', $this->getPA()));
		$secure3D->appendChild($xml->createElement('ECI', $this->getECI()));
		$secure3D->appendChild($xml->createElement('XID', $this->getXID()));
		$secure3D->appendChild($xml->createElement('CAVV', $this->getCAVV()));

		$xml->formatOutput = TRUE;
		$resquestXML = $xml->saveXML();

		$responseXML = $this->postXml($resquestXML);

		return $responseXML;
	}

	/**
	 * Asynchronous Authorise – 3D Secure authentication Request
	 *
	 * This transaction is used as the first part of a two-step Authorise/Capture Purchase.
	 * This will authorise the payment without actually moving the funds from the card.
	 * After authorisation is performed, Capture is needed to complete the payment transaction and move funds.
	 *
	 * On receipt of a response indicating the transaction has been received successfully the
	 * Asynchronous transaction is then used to redirect the cardholder browser to the Walpay
	 * Gateway for 3D Secure authentication.
	 *
	 * @see Doc pg 36
	 */
	public function asyncAuthorise3DS() {

		$_Path = '/U00A4_0100/Authorise/KG/Asynchronous';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));
		$merchantInfo->appendChild($xml->createElement('NotificationURL', $this->getNotificationURL()));
		$merchantInfo->appendChild($xml->createElement('RedirectURL', $this->getRedirectURL()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$clientInfo = $root->appendChild($xml->createElement("ClientInfo"));
		$clientInfo->appendChild($xml->createElement('AccountNumber', $this->getAccountNumber()));
		$clientInfo->appendChild($xml->createElement('Name', $this->getName()));
		$clientInfo->appendChild($xml->createElement('Surname', $this->getSurname()));
		$clientInfo->appendChild($xml->createElement('Address', $this->getAddress()));
		$clientInfo->appendChild($xml->createElement('City', $this->getCity()));
		$clientInfo->appendChild($xml->createElement('State', $this->getState()));
		$clientInfo->appendChild($xml->createElement('Zip', $this->getZip()));
		$clientInfo->appendChild($xml->createElement('Country', $this->getCountry()));
		$clientInfo->appendChild($xml->createElement('Telephone', $this->getTelephone()));
		$clientInfo->appendChild($xml->createElement('Email', $this->getEmail()));
		$clientInfo->appendChild($xml->createElement('DOB', $this->getDOB()));
		$clientInfo->appendChild($xml->createElement('ClientIP', $this->getClientIP()));

		$paymentInfo = $root->appendChild($xml->createElement("PaymentInfo"));
		$paymentInfo->appendChild($xml->createElement('PAN', $this->getPAN()));
		$paymentInfo->appendChild($xml->createElement('ExpiryDate', $this->getExpiryDate()));
		$paymentInfo->appendChild($xml->createElement('CardHolderName', $this->getCardHolderName()));
		$paymentInfo->appendChild($xml->createElement('CVC2', $this->getCVC2()));
		$paymentInfo->appendChild($xml->createElement('StartDate', $this->getStartDate()));
		$paymentInfo->appendChild($xml->createElement('IssueNumber', $this->getIssueNumber()));

		$xml->formatOutput = TRUE;
		$resquestXML = $xml->saveXML();

		$responseXML = $this->postXml($resquestXML);

		return $responseXML;
	}

	/**
	 * Asynchronous Purchase – 3D Secure authentication Request
	 *
	 * This transaction is used to authorise the payment and move funds from the card in a single transaction.
	 *
	 * On receipt of a response indicating the transaction has been received successfully the
	 * Asynchronous transaction is then used to redirect the cardholder browser to the Walpay Gateway for 3D Secure authentication.
	 *
	 * @see Doc pg 37
	 */
	public function asyncPurchase3DS() {

		$_Path = '/U00A4_0100/Debit/KG/Asynchronous';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));
		$merchantInfo->appendChild($xml->createElement('NotificationURL', $this->getNotificationURL()));
		$merchantInfo->appendChild($xml->createElement('RedirectURL', $this->getRedirectURL()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$clientInfo = $root->appendChild($xml->createElement("ClientInfo"));
		$clientInfo->appendChild($xml->createElement('AccountNumber', $this->getAccountNumber()));
		$clientInfo->appendChild($xml->createElement('Name', $this->getName()));
		$clientInfo->appendChild($xml->createElement('Surname', $this->getSurname()));
		$clientInfo->appendChild($xml->createElement('Address', $this->getAddress()));
		$clientInfo->appendChild($xml->createElement('City', $this->getCity()));
		$clientInfo->appendChild($xml->createElement('State', $this->getState()));
		$clientInfo->appendChild($xml->createElement('Zip', $this->getZip()));
		$clientInfo->appendChild($xml->createElement('Country', $this->getCountry()));
		$clientInfo->appendChild($xml->createElement('Telephone', $this->getTelephone()));
		$clientInfo->appendChild($xml->createElement('Email', $this->getEmail()));
		$clientInfo->appendChild($xml->createElement('DOB', $this->getDOB()));
		$clientInfo->appendChild($xml->createElement('ClientIP', $this->getClientIP()));

		$paymentInfo = $root->appendChild($xml->createElement("PaymentInfo"));
		$paymentInfo->appendChild($xml->createElement('PAN', $this->getPAN()));
		$paymentInfo->appendChild($xml->createElement('ExpiryDate', $this->getExpiryDate()));
		$paymentInfo->appendChild($xml->createElement('CardHolderName', $this->getCardHolderName()));
		$paymentInfo->appendChild($xml->createElement('CVC2', $this->getCVC2()));
		$paymentInfo->appendChild($xml->createElement('StartDate', $this->getStartDate()));
		$paymentInfo->appendChild($xml->createElement('IssueNumber', $this->getIssueNumber()));

		$xml->formatOutput = TRUE;
		$resquestXML = $xml->saveXML();

		$responseXML = $this->postXml($resquestXML);

		return $responseXML;
	}

	/**
	 * Query Status Request
	 *
	 * This transaction queries the status of a transaction and is primarily used in asynchronous
	 * processing to determine the final transaction status when the Walpay Gateway sends the
	 * merchant a Notification request.
	 *
	 * @see Doc pg 44
	 */
	public function queryStatus() {

		$_Path = '/U00A4_0100/QueryStatus/KG';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantFinalResponse"));

		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		$root->appendChild($xml->createElement('OrderID', $this->getOrderId()));

		$xml->formatOutput = TRUE;
		$resquestXML = $xml->saveXML();

		$this->setUrl($this->_URL.$_Path);
		$responseXML = $this->postXml($resquestXML, "strRequest");
		$responseArray = $this->parseXml($responseXML);

		return $responseArray;
	}

	/**
	 * Cancel
	 *
	 * This transaction cancels an Authorise transaction that has not yet been settled with the bank
	 *
	 * @see DOc pg 47
	 */
	public function cancel() {

		$_Path = '/U00A4_0100/Cancel/KG';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));

		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		if ($this->Simulate) {
			$root->setAttribute("Simulate","CardDebitSuccess,CardCreditSuccess,CardAuthoriseSuccess,CardSettleSuccess");
		}

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId())); // OR
		//$transInfo->appendChild($xml->createElement('RefID', $this->getRefID())); // OR

		$xml->formatOutput = TRUE;
		$requestXML = $xml->saveXML();

		$this->setUrl($this->_URL.$_Path);
		$responseXML = $this->postXml($requestXML, "strRequest");
		$responseArray = $this->parseXml($responseXML);

		return $responseArray;
	}

	/**
	 * Capture
	 *
	 * This transaction is the second part of a two-step Purchase which moves the funds from the card
	 * that were previously authorised using an Authorise transaction.
	 *
	 * @see Doc pg 48
	 */
	public function capture() {

		$_Path = '/U00A4_0100/Settle/KG';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));

		if ($this->Simulate) {
			$root->setAttribute("Simulate","CardDebitSuccess,CardCreditSuccess,CardAuthoriseSuccess,CardSettleSuccess");
		}

		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('RefID', $this->getRefID()));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));

		$xml->formatOutput = TRUE;
		$requestXML = $xml->saveXML();

		$this->setUrl($this->_URL.$_Path);
		$responseXML = $this->postXml($requestXML, "strRequest");
		$responseArray = $this->parseXml($responseXML);

		return $responseArray;

	}

	/**
	 * Refund
	 *
	 * This transaction credits a card with funds in relation to a prior Purchase or Authorise/Capture.
	 *
	 * @see Doc pg 51
	 */
	public function refund() {

		$_Path = '/U00A4_0100/Credit/KG';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));

		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		if ($this->Simulate) {
			$root->setAttribute("Simulate","CardDebitSuccess,CardCreditSuccess,CardAuthoriseSuccess,CardSettleSuccess");
		}

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId())); // OR
		//$transInfo->appendChild($xml->createElement('RefID', $this->getRefID())); // OR
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));

		$xml->formatOutput = TRUE;
		$requestXML = $xml->saveXML();

		$this->setUrl($this->_URL.$_Path);
		$responseXML = $this->postXml($requestXML, "strRequest");
		$responseArray = $this->parseXml($responseXML);

		return $responseArray;
	}

	/**
	 * Initial Continuous Authority Synchronous Authorise
	 *
	 * Continuous authority transactions permit the iteration of repeat payments to be taken
	 * without cardholder interaction. An initial successful continuous authority transaction with
	 * cardholder interaction must first be performed prior to the iteration of repeat payments.
	 *
	 * This transaction is used as the first part of a two-step Authorise/Capture Purchase. This
	 * will authorise the payment without actually moving the funds from the card. Once this
	 * transaction is performed successfully, a Capture transaction is required to complete the
	 * payment and move funds.
	 *
	 * @see Doc pg 54
	 */
	public function initContAuthSyncAuthorise() {

		$_Path = '/U00A4_0100/Authorise/KG/Synchronous/ContinuousAuthority/Initial';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$clientInfo = $root->appendChild($xml->createElement("ClientInfo"));
		$clientInfo->appendChild($xml->createElement('AccountNumber', $this->getAccountNumber()));
		$clientInfo->appendChild($xml->createElement('Name', $this->getName()));
		$clientInfo->appendChild($xml->createElement('Surname', $this->getSurname()));
		$clientInfo->appendChild($xml->createElement('Address', $this->getAddress()));
		$clientInfo->appendChild($xml->createElement('City', $this->getCity()));
		$clientInfo->appendChild($xml->createElement('State', $this->getState()));
		$clientInfo->appendChild($xml->createElement('Zip', $this->getZip()));
		$clientInfo->appendChild($xml->createElement('Country', $this->getCountry()));
		$clientInfo->appendChild($xml->createElement('Telephone', $this->getTelephone()));
		$clientInfo->appendChild($xml->createElement('Email', $this->getEmail()));
		$clientInfo->appendChild($xml->createElement('DOB', $this->getDOB()));
		$clientInfo->appendChild($xml->createElement('ClientIP', $this->getClientIP()));

		$paymentInfo = $root->appendChild($xml->createElement("PaymentInfo"));
		$paymentInfo->appendChild($xml->createElement('PAN', $this->getPAN()));
		$paymentInfo->appendChild($xml->createElement('ExpiryDate', $this->getExpiryDate()));
		$paymentInfo->appendChild($xml->createElement('CardHolderName', $this->getCardHolderName()));
		$paymentInfo->appendChild($xml->createElement('CVC2', $this->getCVC2()));
		$paymentInfo->appendChild($xml->createElement('StartDate', $this->getStartDate()));
		$paymentInfo->appendChild($xml->createElement('IssueNumber', $this->getIssueNumber()));

		$xml->formatOutput = TRUE;
		$requestXML = $xml->saveXML();

		$this->setUrl($this->_URL.$_Path);
		$responseXML = $this->postXml($requestXML, "strRequest");
		$responseArray = $this->parseXml($responseXML);

		return $responseArray;
	}


	/**
	 * Initial Continuous Authority Synchronous Authorise – 3D Secure authentication
	 *
	 * Continuous authority transactions permit the iteration of repeat payments to be taken
	 * without cardholder interaction. An initial successful continuous authority transaction with
	 * cardholder interaction must first be performed prior to the iteration of repeat payments.
	 *
	 * This transaction is used as the first part of a two-step Authorise/Capture Purchase. This
	 * will authorise the payment without actually moving the funds from the card. Once this
	 * transaction is performed successfully, a Capture transaction is required to complete the
	 * payment and move funds.
	 *
	 * Prior to executing this transaction the cardholder must have been authenticated via a 3D
	 * secure mechanism and the successful results of that authentication provided in the transaction.
	 *
	 * @see Doc pg 58
	 */
	public function initContAuthSyncAuthorise3DS() {

		$_Path = '/U00A4_0100/Authorise/KG/Synchronous/ContinuousAuthority/Initial/3DS';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$clientInfo = $root->appendChild($xml->createElement("ClientInfo"));
		$clientInfo->appendChild($xml->createElement('AccountNumber', $this->getAccountNumber()));
		$clientInfo->appendChild($xml->createElement('Name', $this->getName()));
		$clientInfo->appendChild($xml->createElement('Surname', $this->getSurname()));
		$clientInfo->appendChild($xml->createElement('Address', $this->getAddress()));
		$clientInfo->appendChild($xml->createElement('City', $this->getCity()));
		$clientInfo->appendChild($xml->createElement('State', $this->getState()));
		$clientInfo->appendChild($xml->createElement('Zip', $this->getZip()));
		$clientInfo->appendChild($xml->createElement('Country', $this->getCountry()));
		$clientInfo->appendChild($xml->createElement('Telephone', $this->getTelephone()));
		$clientInfo->appendChild($xml->createElement('Email', $this->getEmail()));
		$clientInfo->appendChild($xml->createElement('DOB', $this->getDOB()));
		$clientInfo->appendChild($xml->createElement('ClientIP', $this->getClientIP()));

		$paymentInfo = $root->appendChild($xml->createElement("PaymentInfo"));
		$paymentInfo->appendChild($xml->createElement('PAN', $this->getPAN()));
		$paymentInfo->appendChild($xml->createElement('ExpiryDate', $this->getExpiryDate()));
		$paymentInfo->appendChild($xml->createElement('CardHolderName', $this->getCardHolderName()));
		$paymentInfo->appendChild($xml->createElement('CVC2', $this->getCVC2()));
		$paymentInfo->appendChild($xml->createElement('StartDate', $this->getStartDate()));
		$paymentInfo->appendChild($xml->createElement('IssueNumber', $this->getIssueNumber()));

		$secure3D = $root->appendChild($xml->createElement("Secure3D"));
		$secure3D->appendChild($xml->createElement('VE', $this->getVE()));
		$secure3D->appendChild($xml->createElement('PA', $this->getPA()));
		$secure3D->appendChild($xml->createElement('ECI', $this->getECI()));
		$secure3D->appendChild($xml->createElement('XID', $this->getXID()));
		$secure3D->appendChild($xml->createElement('CAVV', $this->getCAVV()));

		$xml->formatOutput = TRUE;
		$resquestXML = $xml->saveXML();

		$this->setUrl($this->_URL.$_Path);
		$responseXML = $this->postXml($resquestXML, "strRequest");
		$responseArray = $this->parseXml($responseXML);

		return $responseArray;
	}

	/**
	 * Initial Continuous Authority Synchronous Purchase
	 *
	 * Continuous authority transactions permit the iteration of repeat payments to be taken
	 * without cardholder interaction. An initial successful continuous authority transaction with
	 * cardholder interaction must first be performed prior to the iteration of repeat payments.
	 *
	 * This transaction is used to authorise the payment and move funds from the card in a single transaction.
	 *
	 * @see Doc pg 63
	 */
	public function initContAuthSyncPurchase() {

		$_Path = '/U00A4_0100/Debit/KG/Synchronous/ContinuousAuthority/Initial';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$clientInfo = $root->appendChild($xml->createElement("ClientInfo"));
		$clientInfo->appendChild($xml->createElement('AccountNumber', $this->getAccountNumber()));
		$clientInfo->appendChild($xml->createElement('Name', $this->getName()));
		$clientInfo->appendChild($xml->createElement('Surname', $this->getSurname()));
		$clientInfo->appendChild($xml->createElement('Address', $this->getAddress()));
		$clientInfo->appendChild($xml->createElement('City', $this->getCity()));
		$clientInfo->appendChild($xml->createElement('State', $this->getState()));
		$clientInfo->appendChild($xml->createElement('Zip', $this->getZip()));
		$clientInfo->appendChild($xml->createElement('Country', $this->getCountry()));
		$clientInfo->appendChild($xml->createElement('Telephone', $this->getTelephone()));
		$clientInfo->appendChild($xml->createElement('Email', $this->getEmail()));
		$clientInfo->appendChild($xml->createElement('DOB', $this->getDOB()));
		$clientInfo->appendChild($xml->createElement('ClientIP', $this->getClientIP()));

		$paymentInfo = $root->appendChild($xml->createElement("PaymentInfo"));
		$paymentInfo->appendChild($xml->createElement('PAN', $this->getPAN()));
		$paymentInfo->appendChild($xml->createElement('ExpiryDate', $this->getExpiryDate()));
		$paymentInfo->appendChild($xml->createElement('CardHolderName', $this->getCardHolderName()));
		$paymentInfo->appendChild($xml->createElement('CVC2', $this->getCVC2()));
		$paymentInfo->appendChild($xml->createElement('StartDate', $this->getStartDate()));
		$paymentInfo->appendChild($xml->createElement('IssueNumber', $this->getIssueNumber()));

		$xml->formatOutput = TRUE;
		$requestXML = $xml->saveXML();

		$this->setUrl($this->_URL.$_Path);
		$responseXML = $this->postXml($requestXML, "strRequest");
		$responseArray = $this->parseXml($responseXML);

		return $responseArray;
	}

	/**
	 * Initial Continuous Authority Synchronous Purchase – 3D Secure authentication
	 *
	 * Continuous authority transactions permit the iteration of repeat payments to be taken
	 * without cardholder interaction. An initial successful continuous authority transaction with
	 * cardholder interaction must first be performed prior to the iteration of repeat payments.
	 *
	 * Prior to executing this transaction the cardholder must have been authenticated via a 3D
	 * secure mechanism and the successful results of that authentication provided in the transaction.
	 *
	 * This transaction is used to authorise the payment and move funds from the card in a single transaction.
	 *
	 * @see Doc pg 67
	 */
	public function initContAuthSyncPurchase3DS() {

		$_Path = '/U00A4_0100/Debit/KG/Synchronous/ContinuousAuthority/Initial/3DS';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$clientInfo = $root->appendChild($xml->createElement("ClientInfo"));
		$clientInfo->appendChild($xml->createElement('AccountNumber', $this->getAccountNumber()));
		$clientInfo->appendChild($xml->createElement('Name', $this->getName()));
		$clientInfo->appendChild($xml->createElement('Surname', $this->getSurname()));
		$clientInfo->appendChild($xml->createElement('Address', $this->getAddress()));
		$clientInfo->appendChild($xml->createElement('City', $this->getCity()));
		$clientInfo->appendChild($xml->createElement('State', $this->getState()));
		$clientInfo->appendChild($xml->createElement('Zip', $this->getZip()));
		$clientInfo->appendChild($xml->createElement('Country', $this->getCountry()));
		$clientInfo->appendChild($xml->createElement('Telephone', $this->getTelephone()));
		$clientInfo->appendChild($xml->createElement('Email', $this->getEmail()));
		$clientInfo->appendChild($xml->createElement('DOB', $this->getDOB()));
		$clientInfo->appendChild($xml->createElement('ClientIP', $this->getClientIP()));

		$paymentInfo = $root->appendChild($xml->createElement("PaymentInfo"));
		$paymentInfo->appendChild($xml->createElement('PAN', $this->getPAN()));
		$paymentInfo->appendChild($xml->createElement('ExpiryDate', $this->getExpiryDate()));
		$paymentInfo->appendChild($xml->createElement('CardHolderName', $this->getCardHolderName()));
		$paymentInfo->appendChild($xml->createElement('CVC2', $this->getCVC2()));
		$paymentInfo->appendChild($xml->createElement('StartDate', $this->getStartDate()));
		$paymentInfo->appendChild($xml->createElement('IssueNumber', $this->getIssueNumber()));

		$secure3D = $root->appendChild($xml->createElement("Secure3D"));
		$secure3D->appendChild($xml->createElement('VE', $this->getVE()));
		$secure3D->appendChild($xml->createElement('PA', $this->getPA()));
		$secure3D->appendChild($xml->createElement('ECI', $this->getECI()));
		$secure3D->appendChild($xml->createElement('XID', $this->getXID()));
		$secure3D->appendChild($xml->createElement('CAVV', $this->getCAVV()));

		$xml->formatOutput = TRUE;
		$resquestXML = $xml->saveXML();

		$responseXML = $this->postXml($resquestXML);

		return $responseXML;
	}

	/**
	 * Initial Continuous Authority Asynchronous Authorise – 3D Secure authentication
	 *
	 * Continuous authority transactions permit the iteration of repeat payments to be taken
	 * without cardholder interaction. An initial successful continuous authority transaction with
	 * cardholder interaction must first be performed prior to the iteration of repeat payments.
	 *
	 * This transaction is used as the first part of a two-step Authorise/Capture Purchase. This
	 * will authorise the payment without actually moving the funds from the card. After
	 * Authorisation is performed, Capture is needed to complete the payment transaction and move funds.
	 *
	 * On receipt of a response indicating the transaction has been received successfully the
	 * Asynchronous transaction is then used to redirect the cardholder browser to the Walpay Gateway for 3D Secure authentication.
	 *
	 * @see Doc pg 72
	 */
	public function initContAuthASyncAuthorise3DS() {

		$_Path = '/U00A4_0100/Authorise/KG/Asynchronous/ContinuousAuthority/Initial';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));

		// Request Simulation :)
		//$root->setAttribute("Simulate","CardDebitSuccess"); // Successful Debit
		//$root->setAttribute("Simulate","CardDebitFail"); // Failed Debit
		//$root->setAttribute("Simulate","CardDebitNotEnrolled"); // Debit (Asynchronous), card is not enrolled
		//$root->setAttribute("Simulate","CardCreditSuccess"); // Successful Credit
		//$root->setAttribute("Simulate","CardCreditFail"); // Failed Credit
		//$root->setAttribute("Simulate","CardAuthoriseSuccess"); // Successful Authorise
		//$root->setAttribute("Simulate","CardAuthoriseFail"); // Failed Authorise
		//$root->setAttribute("Simulate","CardAuthoriseNotEnrolled"); // Authorise (Asynchronous), card is not enrolled
		//$root->setAttribute("Simulate","CardCancelSuccess"); // Successful Cancel
		//$root->setAttribute("Simulate","CardCancelFail"); // Failed Cancel
		//$root->setAttribute("Simulate","CardSettleSuccess"); // Successful Settle
		//$root->setAttribute("Simulate","CardSettleFail"); // Failed Settle
		//$root->setAttribute("Simulate","CardCreditFail,CardAuthoriseFail,CardAuthoriseNotEnrolled"); // Multiple fails on credit
		if ($this->Simulate) {
			$root->setAttribute("Simulate","CardDebitSuccess,CardCreditSuccess,CardAuthoriseSuccess,CardSettleSuccess"); // Multiple success on credit
		}

		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));
		$merchantInfo->appendChild($xml->createElement('NotificationURL', $this->getNotificationURL()));
		$merchantInfo->appendChild($xml->createElement('RedirectURL', $this->getRedirectURL()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$clientInfo = $root->appendChild($xml->createElement("ClientInfo"));
		$clientInfo->appendChild($xml->createElement('AccountNumber', $this->getAccountNumber()));
		$clientInfo->appendChild($xml->createElement('Name', $this->getName()));
		$clientInfo->appendChild($xml->createElement('Surname', $this->getSurname()));
		$clientInfo->appendChild($xml->createElement('Address', $this->getAddress()));
		$clientInfo->appendChild($xml->createElement('City', $this->getCity()));
		$clientInfo->appendChild($xml->createElement('State', $this->getState()));
		$clientInfo->appendChild($xml->createElement('Zip', $this->getZip()));
		$clientInfo->appendChild($xml->createElement('Country', $this->getCountry()));
		$clientInfo->appendChild($xml->createElement('Telephone', $this->getTelephone()));
		$clientInfo->appendChild($xml->createElement('Email', $this->getEmail()));
		$clientInfo->appendChild($xml->createElement('DOB', $this->getDOB()));
		$clientInfo->appendChild($xml->createElement('ClientIP', $this->getClientIP()));

		$paymentInfo = $root->appendChild($xml->createElement("PaymentInfo"));
		$paymentInfo->appendChild($xml->createElement('PAN', $this->getPAN()));
		$paymentInfo->appendChild($xml->createElement('ExpiryDate', $this->getExpiryDate()));
		$paymentInfo->appendChild($xml->createElement('CardHolderName', $this->getCardHolderName()));
		$paymentInfo->appendChild($xml->createElement('CVC2', $this->getCVC2()));
		$paymentInfo->appendChild($xml->createElement('StartDate', $this->getStartDate()));
		$paymentInfo->appendChild($xml->createElement('IssueNumber', $this->getIssueNumber()));

		$xml->formatOutput = TRUE;
		$requestXML = $xml->saveXML();

		$this->setUrl($this->_URL.$_Path);
		$responseXML = $this->postXml($requestXML, "strRequest");
		$responseArray = $this->parseXml($responseXML);

		$responseArray['RedirectAsynchronous'] = $this->getRedirectAsynchronous();

		return $responseArray;
	}

	/**
	 * Initial Continuous Authority Asynchronous Purchase – 3D Secure authentication
	 *
	 * Continuous authority transactions permit the iteration of repeat payments to be taken
	 * without cardholder interaction. An initial successful continuous authority transaction with
	 * cardholder interaction must first be performed prior to the iteration of repeat payments.
	 *
	 * This transaction is used to authorise the payment and move funds from the card in a single transaction.
	 *
	 * On receipt of a response indicating the transaction has been received successfully the
	 * Asynchronous transaction is then used to redirect the cardholder browser to the Walpay
	 * Gateway for 3D Secure authentication.
	 *
	 * @see Doc pg 76
	 */
	public function initContAuthASyncPurchase3DS() {

		error_log('Called:'.__METHOD__.PHP_EOL, 3, '/var/www/dev/logs/production/trans_'.date('Ymd').'.log');

		$_Path = '/U00A4_0100/Debit/KG/Asynchronous/ContinuousAuthority/Initial';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));
		$merchantInfo->appendChild($xml->createElement('NotificationURL', $this->getNotificationURL()));
		$merchantInfo->appendChild($xml->createElement('RedirectURL', $this->getRedirectURL()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$clientInfo = $root->appendChild($xml->createElement("ClientInfo"));
		$clientInfo->appendChild($xml->createElement('AccountNumber', $this->getAccountNumber()));
		$clientInfo->appendChild($xml->createElement('Name', $this->getName()));
		$clientInfo->appendChild($xml->createElement('Surname', $this->getSurname()));
		$clientInfo->appendChild($xml->createElement('Address', $this->getAddress()));
		$clientInfo->appendChild($xml->createElement('City', $this->getCity()));
		$clientInfo->appendChild($xml->createElement('State', $this->getState()));
		$clientInfo->appendChild($xml->createElement('Zip', $this->getZip()));
		$clientInfo->appendChild($xml->createElement('Country', $this->getCountry()));
		$clientInfo->appendChild($xml->createElement('Telephone', $this->getTelephone()));
		$clientInfo->appendChild($xml->createElement('Email', $this->getEmail()));
		$clientInfo->appendChild($xml->createElement('DOB', $this->getDOB()));
		$clientInfo->appendChild($xml->createElement('ClientIP', $this->getClientIP()));

		$paymentInfo = $root->appendChild($xml->createElement("PaymentInfo"));
		$paymentInfo->appendChild($xml->createElement('PAN', $this->getPAN()));
		$paymentInfo->appendChild($xml->createElement('ExpiryDate', $this->getExpiryDate()));
		$paymentInfo->appendChild($xml->createElement('CardHolderName', $this->getCardHolderName()));
		$paymentInfo->appendChild($xml->createElement('CVC2', $this->getCVC2()));
		$paymentInfo->appendChild($xml->createElement('StartDate', $this->getStartDate()));
		$paymentInfo->appendChild($xml->createElement('IssueNumber', $this->getIssueNumber()));

		$xml->formatOutput = TRUE;
		$requestXML = $xml->saveXML();

		$this->setUrl($this->_URL.$_Path);
		$responseXML = $this->postXml($requestXML, "strRequest");
		$responseArray = $this->parseXml($responseXML);

		$responseArray['RedirectAsynchronous'] = $this->getRedirectAsynchronous();

		return $responseArray;
	}

	/**
	 * Iterate Continuous Authority Synchronous Authorise
	 *
	 * Continuous authority transactions permit the iteration of repeat payments to be taken
	 * without cardholder interaction. Once a successful initial continuous authority transaction
	 * has been performed the iteration of repeat payments can commence using this transaction.
	 *
	 * This transaction is used as the first part of a two-step Authorise/Capture Purchase. This
	 * will authorise the payment without actually moving the funds from the card. After
	 * Authorisation is performed, Capture is needed to complete the payment transaction and move funds.
	 *
	 * @see Doc pg 80
	 */
	public function iterateContAuthSyncAuthorise() {

		$_Path = '/U00A4_0100/Authorise/KG/Synchronous/ContinuousAuthority/Iterate';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		if ($this->Simulate) {
			$root->setAttribute("Simulate","CardDebitSuccess,CardCreditSuccess,CardAuthoriseSuccess,CardSettleSuccess"); // Multiple success on credit
		}

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('RefID', $this->getRefID()));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$xml->formatOutput = TRUE;
		$requestXML = $xml->saveXML();

		$this->setUrl($this->_URL.$_Path);
		$responseXML = $this->postXml($requestXML, "strRequest");
		$responseArray = $this->parseXml($responseXML);

		return $responseArray;
	}

	/**
	 * Iterate Continuous Authority Synchronous Purchase
	 *
	 * Continuous authority transactions permit the iteration of repeat payments to be taken
	 * without cardholder interaction. Once a successful initial continuous authority transaction
	 * has been performed the iteration of repeat payments can commence using this transaction.
	 *
	 * This transaction is used to authorise the payment and move funds from the card in a single transaction.
	 *
	 * @see Doc pg 83
	 */
	public function iterateContAuthSyncPurchase() {

		$_Path = '/U00A4_0100/Debit/KG/Synchronous/ContinuousAuthority/Iterate';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		if ($this->Simulate) {
			$root->setAttribute("Simulate","CardDebitSuccess,CardCreditSuccess,CardAuthoriseSuccess,CardSettleSuccess"); // Multiple success on credit
		}

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('RefID', $this->getRefID()));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$xml->formatOutput = TRUE;
		$requestXML = $xml->saveXML();

		$this->setUrl($this->_URL.$_Path);
		$responseXML = $this->postXml($requestXML, "strRequest");
		$responseArray = $this->parseXml($responseXML);

		return $responseArray;
	}

	/**
	 * Risk
	 *
	 * This transaction performs risk analysis on the transaction data; other transactions
	 * detailed in this document contain integrated risk analysis as part of their processing. This
	 * transaction is commonly used for fraud screening prior to payment authorisation.
	 *
	 * @see Doc pg 86
	 */
	public function riskAnalysis() {

		$_Path = '/U00A4_0100/Risk/KG';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$clientInfo = $root->appendChild($xml->createElement("ClientInfo"));
		$clientInfo->appendChild($xml->createElement('AccountNumber', $this->getAccountNumber()));
		$clientInfo->appendChild($xml->createElement('Name', $this->getName()));
		$clientInfo->appendChild($xml->createElement('Surname', $this->getSurname()));
		$clientInfo->appendChild($xml->createElement('Address', $this->getAddress()));
		$clientInfo->appendChild($xml->createElement('City', $this->getCity()));
		$clientInfo->appendChild($xml->createElement('State', $this->getState()));
		$clientInfo->appendChild($xml->createElement('Zip', $this->getZip()));
		$clientInfo->appendChild($xml->createElement('Country', $this->getCountry()));
		$clientInfo->appendChild($xml->createElement('Telephone', $this->getTelephone()));
		$clientInfo->appendChild($xml->createElement('Email', $this->getEmail()));
		$clientInfo->appendChild($xml->createElement('DOB', $this->getDOB()));
		$clientInfo->appendChild($xml->createElement('ClientIP', $this->getClientIP()));

		$paymentInfo = $root->appendChild($xml->createElement("PaymentInfo"));
		$paymentInfo->appendChild($xml->createElement('PAN', $this->getPAN()));
		$paymentInfo->appendChild($xml->createElement('ExpiryDate', $this->getExpiryDate()));
		$paymentInfo->appendChild($xml->createElement('CardHolderName', $this->getCardHolderName()));
		$paymentInfo->appendChild($xml->createElement('CVC2', $this->getCVC2()));
		$paymentInfo->appendChild($xml->createElement('StartDate', $this->getStartDate()));
		$paymentInfo->appendChild($xml->createElement('IssueNumber', $this->getIssueNumber()));

		$xml->formatOutput = TRUE;
		$resquestXML = $xml->saveXML();

		$responseXML = $this->postXml($resquestXML);

		return $responseXML;
	}

	/**
	 * Asynchronous Authentication – 3D Secure authentication
	 *
	 * This transaction is used to purely authenticate the cardholder using 3D secure, no
	 * cardholder funds are processed. The result of the authentication could be used in a
	 * subsequent synchronous, 3D Secure, transaction to actually process cardholder funds but
	 * normally one of the asynchronous transactions would be used which integrates
	 * cardholder 3D Secure authentication and the processing of cardholder funds in a single transaction.
	 *
	 * On receipt of a response indicating the transaction has been received successfully the
	 * Asynchronous transaction is then used to redirect the cardholder browser to the Walpay
	 * Gateway for 3D Secure authentication.
	 *
	 * @see Doc pg 89
	 */
	public function asyncAuth3DS() {

		$_Path = '/U00A4_0100/Authenticate/KG/Asynchronous';

		$xml = new DOMDocument('1.0', 'utf-8');

		$root = $xml->appendChild($xml->createElement("MerchantRequest"));
		$merchantInfo = $root->appendChild($xml->createElement("MerchantInfo"));
		$merchantInfo->appendChild($xml->createElement('MerchantName', $this->getMerchantName()));
		$merchantInfo->appendChild($xml->createElement('MerchantPIN', $this->getMerchantPin()));
		$merchantInfo->appendChild($xml->createElement('NotificationURL', $this->getNotificationURL()));
		$merchantInfo->appendChild($xml->createElement('RedirectURL', $this->getRedirectURL()));

		$transInfo = $root->appendChild($xml->createElement("TransactionInfo"));
		$transInfo->appendChild($xml->createElement('OrderID', $this->getOrderId()));
		$transInfo->appendChild($xml->createElement('OrderDescription', $this->getOrderDescription()));
		$transInfo->appendChild($xml->createElement('Amount', $this->getAmount()));
		$transInfo->appendChild($xml->createElement('CurrencyCode', $this->getCurrencyCode()));
		$transInfo->appendChild($xml->createElement('MerchantDateTime', $this->getMerchantDateTime()));

		$clientInfo = $root->appendChild($xml->createElement("ClientInfo"));
		$clientInfo->appendChild($xml->createElement('AccountNumber', $this->getAccountNumber()));
		$clientInfo->appendChild($xml->createElement('Name', $this->getName()));
		$clientInfo->appendChild($xml->createElement('Surname', $this->getSurname()));
		$clientInfo->appendChild($xml->createElement('Address', $this->getAddress()));
		$clientInfo->appendChild($xml->createElement('City', $this->getCity()));
		$clientInfo->appendChild($xml->createElement('State', $this->getState()));
		$clientInfo->appendChild($xml->createElement('Zip', $this->getZip()));
		$clientInfo->appendChild($xml->createElement('Country', $this->getCountry()));
		$clientInfo->appendChild($xml->createElement('Telephone', $this->getTelephone()));
		$clientInfo->appendChild($xml->createElement('Email', $this->getEmail()));
		$clientInfo->appendChild($xml->createElement('DOB', $this->getDOB()));
		$clientInfo->appendChild($xml->createElement('ClientIP', $this->getClientIP()));

		$paymentInfo = $root->appendChild($xml->createElement("PaymentInfo"));
		$paymentInfo->appendChild($xml->createElement('PAN', $this->getPAN()));
		$paymentInfo->appendChild($xml->createElement('ExpiryDate', $this->getExpiryDate()));
		$paymentInfo->appendChild($xml->createElement('CardHolderName', $this->getCardHolderName()));
		$paymentInfo->appendChild($xml->createElement('CVC2', $this->getCVC2()));
		$paymentInfo->appendChild($xml->createElement('StartDate', $this->getStartDate()));
		$paymentInfo->appendChild($xml->createElement('IssueNumber', $this->getIssueNumber()));

		$xml->formatOutput = TRUE;
		$resquestXML = $xml->saveXML();

		$responseXML = $this->postXml($resquestXML);

		return $responseXML;
	}
}