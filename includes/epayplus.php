<?php
/*
 * ePay+ PHP Library.
 */
if ( ! defined( 'ABSPATH' ) ) {  exit; } // Exit if accessed directly

define("EPAYPLUS_VERSION", "1.6.13");

/**
 * ePay+ Transaction Class
 *
 */

class umTransaction {

	// Required for all transactions
	var $key;			// Source key
	var $pin;			// Source pin (optional)
	var $amount;		// the entire amount that will be charged to the customers card
							// (including tax, shipping, etc)
	var $invoice;		// invoice number.  must be unique.  limited to 10 digits.  use orderid if you need longer.

	// Required for Level 2 - Commercial Card support
	var $ponum;			// Purchase Order Number
	var $tax;			// Tax
	var $nontaxable;	// Order is non taxable
	var $digitalgoods; //digital goods indicator (ecommerce)

	// Level 3 support
	var $duty;
	var $shipfromzip;

	// Amount details (optional)
	var $tip; 			// Tip
	var $shipping;		// Shipping charge
	var $discount; 	// Discount amount (ie gift certificate or coupon code)
	var $subtotal; 	// if subtotal is set, then
							// subtotal + tip + shipping - discount + tax must equal amount
							// or the transaction will be declined.  If subtotal is left blank
							// then it will be ignored
	var $currency;		// Currency of $amount
	var $allowpartialauth; //set to true if a partial authorization (less than the full $amount)  will be accepted

	// Required Fields for Card Not Present transacitons (Ecommerce)
	var $card;			// card number, no dashes, no spaces
	var $exp;			// expiration date 4 digits no /
	var $cardholder; 	// name of card holder
	var $street;		// street address
	var $zip;			// zip code

	// Fields for Card Present (POS)
	var $magstripe;  	// mag stripe data.  can be either Track 1, Track2  or  Both  (Required if card,exp,cardholder,street and zip aren't filled in)
	var $cardpresent;   // Must be set to true if processing a card present transaction  (Default is false)
	var $termtype;  	// The type of terminal being used:  Optons are  POS - cash register, StandAlone - self service terminal,  Unattended - ie gas pump, Unkown  (Default:  Unknown)
	var $magsupport;  	// Support for mag stripe reader:   yes, no, contactless, unknown  (default is unknown unless magstripe has been sent)
	var $contactless;  	// Magstripe was read with contactless reader:  yes, no  (default is no)
	var $dukpt;			// DUK/PT for PIN Debit
	var $signature;     // Signature Capture data

	// fields required for check transactions
	var $account;		// bank account number
	var $routing;		// bank routing number
	var $ssn;			// social security number
	var $dlnum;			// drivers license number (required if not using ssn)
	var $dlstate;		// drivers license issuing state
	var $checknum;		// Check Number
	var $accounttype;       // Checking or Savings
	var $checkformat;	// Override default check record format
	var $checkimage_front;    // Check front
	var $checkimage_back;		// Check back
	var $auxonus;
	var $epccode;
	var $micr;


	// Fields required for Secure Vault Payments (Direct Pay)
	var $svpbank;		// ID of cardholders bank
	var $svpreturnurl;	// URL that the bank should return the user to when tran is completed
	var $svpcancelurl; 	// URL that the bank should return the user if they cancel



	// Option parameters
	var $origauthcode;	// required if running postauth transaction.
	var $command;		// type of command to run; Possible values are:
						// sale, credit, void, preauth, postauth, check and checkcredit.
						// Default is sale.
	var $orderid;		// Unique order identifier.  This field can be used to reference
						// the order for which this transaction corresponds to. This field
						// can contain up to 64 characters and should be used instead of
						// UMinvoice when orderids longer that 10 digits are needed.
	var $custid;   // Alpha-numeric id that uniquely identifies the customer.
	var $description;	// description of charge
	var $cvv2;			// cvv2 code
	var $custemail;		// customers email address
	var $custreceipt;	// send customer a receipt
	var $custreceiptname;	// name of custom receipt template
	var $ignoreduplicate; // prevent the system from detecting and folding duplicates
	var $ip;			// ip address of remote host
	var $testmode;		// test transaction but don't process it
	var $usesandbox;    // use sandbox server instead of production
	var $timeout;       // transaction timeout.  defaults to 45 seconds
	var $gatewayurl;   	// url for the gateway
	var $proxyurl;		// proxy server to use (if required by network)
	var $ignoresslcerterrors;  // Bypasses ssl certificate errors.  It is highly recommended that you do not use this option.  Fix your openssl installation instead!
	var $cabundle;      // manually specify location of root ca bundle (useful of root ca is not in default location)
	var $transport;     // manually select transport to use (curl or stream), by default the library will auto select based on what is available
	var $clerk;					// Indicates the clerk/person processing transaction, for reporting purposes. (optional)
	var $terminal;				// Indiactes the terminal used to process transaction, for reporting purposes. (optional)
	var $restaurant_table;	// Indicates the restaurant table, for reporting purposes. (optional)
	var $ticketedevent;  // ID of Event when doing a ticket sale
	var $ifauthexpired;  // controls what happens when capturing an authorization that has expire.  options are 'ignore','error','reauth'.
	var $authexpiredays;  //set the number of days an authorization is valid for.  defaults to merchant account setting.
	var $inventorylocation; // set the warehouse to pull inventory from.  defaults to source key setting.

	// Card Authorization - Verified By Visa and Mastercard SecureCode
	var $cardauth;    	// enable card authentication
	var $pares; 		//

	// Third Party Card Authorization
	var $xid;
	var $cavv;
	var $eci;

	// Customer Database
	var $addcustomer;		//  Save transaction as a recurring transaction:  yes/no
	var $recurring;		// (obsolete,  see the addcustomer)

	var $schedule;		//  How often to run transaction: daily, weekly, biweekly, monthly, bimonthly, quarterly, annually.  Default is monthly, set to disabled if you don't want recurring billing
	var $numleft; 		//  The number of times to run. Either a number or * for unlimited.  Default is unlimited.
	var $start;			//  When to start the schedule.  Default is tomorrow.  Must be in YYYYMMDD  format.
	var $end;			//  When to stop running transactions. Default is to run forever.  If both end and numleft are specified, transaction will stop when the earliest condition is met.
	var $billamount;	//  Optional recurring billing amount.  If not specified, the amount field will be used for future recurring billing payments
	var $billtax;
	var $billsourcekey;

	// tokenization
	var $savecard;		// create a card token if the sale is approved, returns a cardref variable

	// Manually mark as recurring
	var $isrecurring;

	// Billing Fields
	var $billfname;
	var $billlname;
	var $billcompany;
	var $billstreet;
	var $billstreet2;
	var $billcity;
	var $billstate;
	var $billzip;
	var $billcountry;
	var $billphone;
	var $email;
	var $fax;
	var $website;

	// Shipping Fields
	var $delivery;		// type of delivery method ('ship','pickup','download')
	var $shipfname;
	var $shiplname;
	var $shipcompany;
	var $shipstreet;
	var $shipstreet2;
	var $shipcity;
	var $shipstate;
	var $shipzip;
	var $shipcountry;
	var $shipphone;

	// Custom Fields
	var $custom1;
	var $custom2;
	var $custom3;
	var $custom4;
	var $custom5;
	var $custom6;
	var $custom7;
	var $custom8;
	var $custom9;
	var $custom10;
	var $custom11;
	var $custom12;
	var $custom13;
	var $custom14;
	var $custom15;
	var $custom16;
	var $custom17;
	var $custom18;
	var $custom19;
	var $custom20;


	// Line items  (see addLine)
	var $lineitems;


	var $comments; // Additional transaction details or comments (free form text field supports up to 65,000 chars)

	var $software; // Allows developers to identify their application to the gateway (for troubleshooting purposes)

	// Fraud Profiling
	var $session;

	// response fields
	var $rawresult;		// raw result from gateway
	var $result;		// full result:  Approved, Declined, Error
	var $resultcode; 	// abreviated result code: A D E
	var $authcode;		// authorization code
	var $refnum;		// reference number
	var $batch;		// batch number
	var $avs_result;		// avs result
	var $avs_result_code;		// avs result
	var $avs;  					// obsolete avs result
	var $cvv2_result;		// cvv2 result
	var $cvv2_result_code;		// cvv2 result
	var $vpas_result_code;      // vpas result
	var $isduplicate;      // system identified transaction as a duplicate
	var $convertedamount;  // transaction amount after server has converted it to merchants currency
	var $convertedamountcurrency;  // merchants currency
	var $conversionrate;  // the conversion rate that was used
	var $custnum;  //  gateway assigned customer ref number for recurring billing
	var $authamount; // amount that was authorized
	var $balance;  //remaining balance
	var $cardlevelresult;
	var $procrefnum;
	var $cardref; 		// card number token

	// Cardinal Response Fields
	var $acsurl;	// card auth url
	var $pareq;		// card auth request
	var $cctransid; // cardinal transid

	// Fraud Profiler
	var $profiler_score;
	var $profiler_response;
	var $profiler_reason;

	// Errors Response Feilds
	var $error; 		// error message if result is an error
	var $errorcode; 	// numerical error code
	var $blank;			// blank response
	var $transporterror; 	// transport error


	// Constructor
	function umTransaction()
	{
		// Set default values.
		$this->command="sale";
		$this->result="Error";
		$this->resultcode="E";
		$this->error="Transaction not processed yet.";
		$this->timeout=45;
		$this->cardpresent=false;
		$this->lineitems = array();
		if(isset($_SERVER['REMOTE_ADDR'])) $this->ip=$_SERVER['REMOTE_ADDR'];
		$this->software="ePayPlus PHP API v" . EPAYPLUS_VERSION;

	}

	/**
	 * Add a line item to the transaction
	 *
	 * @param string $sku
	 * @param string $name
	 * @param string $description
	 * @param double $cost
	 * @param string $taxable
	 * @param int $qty
	 * @param string $refnum
	 */
	function addLine($sku, $name='', $description='', $cost='', $qty='', $taxable='', $refnum=0)
	{
		if(is_array($sku))
		{
			$vars = $sku;
			$this->lineitems[] = array(
						'sku' => @$vars['sku'],
						'name' => @$vars['name'],
						'description' => @$vars['description'],
						'cost' => @$vars['cost'],
						'taxable' => @$vars['taxable'],
						'qty' => @$vars['qty'],
						'refnum' => @$vars['refnum'],
						'um' => @$vars['um'],
						'taxrate' => @$vars['taxrate'],
						'taxamount' => @$vars['taxamount'],
						'taxclass' => @$vars['taxclass'],
						'commoditycode' => @$vars['commoditycode'],
						'discountrate' => @$vars['discountrate'],
						'discountamount' => @$vars['discountamount'],
					);

		} else {
			$this->lineitems[] = array(
					'sku' => $sku,
					'name' => $name,
					'description' => $description,
					'cost' => $cost,
					'taxable' => $taxable,
					'qty' => $qty,
					'refnum' => $refnum
				);
		}
	}

	function getLineTotal()
	{
		$total = 0;
		foreach($this->lineitems as $line)
		{
			$total+=(intval($line['cost']*100) * intval($line['qty']));
		}
		return number_format($total/100, 2, '.','');
	}

	/**
	 * Remove all line items
	 *
	 */
	function clearLines()
	{
		$this->lineitems=array();
	}

	/**
	 * Clear all transaction parameters except for the key and pin. Should be run between transactions if not
	 * reinsantiating the umTransaction class
	 *
	 */
	function clearData()
	{
		$map =$this->getFieldMap();
		foreach($map as $apifield => $classfield)
		{
			if($classfield=='software' || $classfield=='key' || $classfield=='pin') continue;
			unset($this->$classfield);
		}

		// Set default values.
		$this->command="sale";
		$this->result="Error";
		$this->resultcode="E";
		$this->error="Transaction not processed yet.";
		$this->cardpresent=false;
		$this->lineitems = array();
		if(isset($_SERVER['REMOTE_ADDR'])) $this->ip=$_SERVER['REMOTE_ADDR'];

	}

	/**
	 * Verify that all required data has been set
	 *
	 * @return string
	 */
	function CheckData()
	{
		if(!$this->key) return "Source Key is required";
		if(in_array(strtolower($this->command), array("cc:capture", "cc:refund", "refund", "check:refund","capture", "creditvoid", 'quicksale', 'quickcredit','refund:adjust','void:release')))
		{
			if(!$this->refnum) return "Reference Number is required";
		}else if(in_array(strtolower($this->command), array("svp")))
		{
			if(!$this->svpbank) return "Bank ID is required";
			if(!$this->svpreturnurl) return "Return URL is required";
			if(!$this->svpcancelurl) return "Cancel URL is required";
		}  else {
			if(in_array(strtolower($this->command), array("check:sale","check:credit", "check", "checkcredit","reverseach") )) {
					if(!$this->account) return "Account Number is required";
					if(!$this->routing) return "Routing Number is required";
			} else if (in_array(strtolower($this->command) , array('cash','cash:refund','cash:sale','external:check:sale','external:cc:sale','external:gift:sale'))) {
				// nothing needs to be validated for cash
			} else {
				if(!$this->magstripe) {
					if(!$this->card) return "Credit Card Number is required ({$this->command})";
					if(!$this->exp) return "Expiration Date is required";
				}
			}
			$this->amount=preg_replace("/[^0-9\.]/","",$this->amount);
			if(!$this->amount) return "Amount is required";
			if(!$this->invoice && !$this->orderid) return "Invoice number or Order ID is required";
			if(!$this->magstripe) {
				//if(!$this->cardholder) return "Cardholder Name is required";
				//if(!$this->street) return "Street Address is required";
				//if(!$this->zip) return "Zipcode is required";
			}
		}
		return 0;
	}

	/**
	 * Send transaction to the USAePay Gateway and parse response
	 *
	 * @return boolean
	 */
	function Process()
	{
		// check that we have the needed data
		$tmp=$this->CheckData();
		if($tmp)
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error=$tmp;
			$this->errorcode=10129;
			return false;
		}

		// populate the data
		$map = $this->getFieldMap();
		$data = array();
		foreach($map as $apifield =>$classfield)
		{
			switch($classfield)
			{
				case 'isrecurring':
					if($this->isrecurring) $data['UMisRecurring'] = 'Y';
					break;
				case 'nontaxable':
					if($this->nontaxable) $data['UMnontaxable'] = 'Y';
					break;
				case 'checkimage_front':
				case 'checkimage_back':
					$data[$apifield] = base64_encode($this->$classfield);
					break;
				case 'billsourcekey':
					if($this->billsourcekey) $data['UMbillsourcekey'] = 'yes';
					break;
				case 'cardpresent':
					if($this->cardpresent) $data['UMcardpresent'] = '1';
					break;
				case 'allowpartialauth':
					if($this->allowpartialauth===true) $data['UMallowPartialAuth'] = 'Yes';
					break;
				case 'savecard':
					if($this->savecard) $data['UMsaveCard'] = 'y';
					break;
				default:
					$data[$apifield] = $this->$classfield;
			}
		}

		if(isset($data['UMcheckimagefront']) || isset($data['UMcheckimageback'])) $data['UMcheckimageencoding'] = 'base64';

		// tack on custom fields
		for($i=1; $i<=20; $i++)
		{
			if($this->{"custom$i"}) $data["UMcustom$i"] = $this->{"custom$i"};
		}

		// tack on line level detail
		$c=1;
		if(!is_array($this->lineitems)) $this->lineitems=array();
		foreach($this->lineitems as $lineitem)
		{
			$data["UMline{$c}sku"] = $lineitem['sku'];
			$data["UMline{$c}name"] = $lineitem['name'];
			$data["UMline{$c}description"] = $lineitem['description'];
			$data["UMline{$c}cost"] = $lineitem['cost'];
			$data["UMline{$c}taxable"] = $lineitem['taxable'];
			$data["UMline{$c}qty"] = $lineitem['qty'];
			if($lineitem['refnum'])
				$data["UMline{$c}prodRefNum"] = $lineitem['refnum'];

			//optional level 3 data
			if($lineitem['um']) $data["UMline{$c}um"] = $lineitem['um'];
			if($lineitem['taxrate']) $data["UMline{$c}taxrate"] = $lineitem['taxrate'];
			if($lineitem['taxamount']) $data["UMline{$c}taxamount"] = $lineitem['taxamount'];
			if($lineitem['taxclass']) $data["UMline{$c}taxclass"] = $lineitem['taxclass'];
			if($lineitem['commoditycode']) $data["UMline{$c}commoditycode"] = $lineitem['commoditycode'];
			if($lineitem['discountrate']) $data["UMline{$c}discountrate"] = $lineitem['discountrate'];
			if($lineitem['discountamount']) $data["UMline{$c}discountamount"] = $lineitem['discountamount'];
			$c++;
		}

		// Create hash if pin has been set.
		if(trim($this->pin))
		{
			// generate random seed value
			$seed = microtime(true) . rand();

			// assemble prehash data
			$prehash = $this->command . ":" . trim($this->pin) . ":" . $this->amount . ":" . $this->invoice . ":" . $seed;

			// if sha1 is available,  create sha1 hash,  else use md5
			if(function_exists('sha1')) $hash = 's/' . $seed . '/' . sha1($prehash) . '/n';
			else $hash = 'm/' . $seed . '/' . md5($prehash) . '/n';

			// populate hash value
			$data['UMhash'] = $hash;
		}

		// Figure out URL
		$url = ($this->gatewayurl?$this->gatewayurl:"https://www.usaepay.com/gate");
		if($this->usesandbox) $url = "https://sandbox.usaepay.com/gate";

		// Post data to Gateway
		$result=$this->httpPost($url, $data);
		if($result===false) return false;

		// result is in urlencoded format, parse into an array
		parse_str($result,$tmp);

		// check to make sure we received the correct fields
		if(!isset($tmp["UMversion"]) || !isset($tmp["UMstatus"]))
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Error parsing data from card processing gateway.";
			$this->errorcode=10132;
			return false;
		}

		// Store results
		$this->result=(isset($tmp["UMstatus"])?$tmp["UMstatus"]:"Error");
		$this->resultcode=(isset($tmp["UMresult"])?$tmp["UMresult"]:"E");
		$this->authcode=(isset($tmp["UMauthCode"])?$tmp["UMauthCode"]:"");
		$this->refnum=(isset($tmp["UMrefNum"])?$tmp["UMrefNum"]:"");
		$this->batch=(isset($tmp["UMbatch"])?$tmp["UMbatch"]:"");
		$this->avs_result=(isset($tmp["UMavsResult"])?$tmp["UMavsResult"]:"");
		$this->avs_result_code=(isset($tmp["UMavsResultCode"])?$tmp["UMavsResultCode"]:"");
		$this->cvv2_result=(isset($tmp["UMcvv2Result"])?$tmp["UMcvv2Result"]:"");
		$this->cvv2_result_code=(isset($tmp["UMcvv2ResultCode"])?$tmp["UMcvv2ResultCode"]:"");
		$this->vpas_result_code=(isset($tmp["UMvpasResultCode"])?$tmp["UMvpasResultCode"]:"");
		$this->convertedamount=(isset($tmp["UMconvertedAmount"])?$tmp["UMconvertedAmount"]:"");
		$this->convertedamountcurrency=(isset($tmp["UMconvertedAmountCurrency"])?$tmp["UMconvertedAmountCurrency"]:"");
		$this->conversionrate=(isset($tmp["UMconversionRate"])?$tmp["UMconversionRate"]:"");
		$this->error=(isset($tmp["UMerror"])?$tmp["UMerror"]:"");
		$this->errorcode=(isset($tmp["UMerrorcode"])?$tmp["UMerrorcode"]:"10132");
		$this->custnum=(isset($tmp["UMcustnum"])?$tmp["UMcustnum"]:"");
		$this->authamount=(isset($tmp["UMauthAmount"])?$tmp["UMauthAmount"]:"");
		$this->balance=(isset($tmp["UMremainingBalance"])?$tmp["UMremainingBalance"]:"");
		$this->cardlevelresult=(isset($tmp["UMcardLevelResult"])?$tmp["UMcardLevelResult"]:"");
		$this->procrefnum=(isset($tmp["UMprocRefNum"])?$tmp["UMprocRefNum"]:"");
		$this->profiler_score=(isset($tmp["UMprofilerScore"])?$tmp["UMprofilerScore"]:"");
		$this->profiler_response=(isset($tmp["UMprofilerResponse"])?$tmp["UMprofilerResponse"]:"");
		$this->profiler_reason=(isset($tmp["UMprofilerReason"])?$tmp["UMprofilerReason"]:"");
		$this->cardref=(isset($tmp["UMcardRef"])?$tmp["UMcardRef"]:"");

		// Obsolete variable (for backward compatibility) At some point they will no longer be set.
		//$this->avs=(isset($tmp["UMavsResult"])?$tmp["UMavsResult"]:"");
		//$this->cvv2=(isset($tmp["UMcvv2Result"])?$tmp["UMcvv2Result"]:"");


		if(isset($tmp["UMcctransid"])) $this->cctransid=$tmp["UMcctransid"];
		if(isset($tmp["UMacsurl"])) $this->acsurl=$tmp["UMacsurl"];
		if(isset($tmp["UMpayload"])) $this->pareq=$tmp["UMpayload"];

		if($this->resultcode == "A") return true;
		return false;

	}


	function getSession()
	{
		$seed = md5(mt_rand());
		$action = 'getsession';

		// build hash
		$prehash = $action . ":" . $this->pin . ":" . $seed;
		$hash = 's/' . $seed . '/' . sha1($prehash);

		// Figure out URL
		$url = 'https://www.usaepay.com/interface/profiler/';
		if(preg_match('~https://([^/]*)/~i', $this->gatewayurl, $m)) {
			$url = 'https://' . $m[1] . '/interface/profiler/';
		}

		// Look for usesandbox
		if($this->usesandbox) $url = "https://sandbox.usaepay.com/interface/profiler/";

		// Add request paramters
		$url .=  $action . '?SourceKey=' . rawurlencode($this->key) . '&Hash=' . rawurlencode($hash);

		// Make Rest Call
		$output = file_get_contents($url);
		if(!$output) {
			$this->error = "Blank response";
			return false;
		}

		// Parse response
		$xml = simplexml_load_string($output);
		if(!$xml) {
			$this->error = "Unable to parse response";
			return false;
		}

		if($xml->Response!='A') {
			$this->error = $xml->Reason;
			$this->errorcode = $xml->ErrorCode;
			return false;
		}

		// assemble template
		$query ='org_id=' .$xml->OrgID . '&session_id=' . $xml->SessionID;
		$baseurl = "https://h.online-metrix.net/fp/";

		$out = '';
		$out .= '<p style="background:url('.$baseurl.'clear.png?'.$query.'&m=1)"></p>';
		$out .= '<img src="'.$baseurl.'clear.png?'.$query.'&m=2" width="1" height="1" alt="">';
		$out .= '<script src="'.$baseurl.'check.js?'.$query.'" type="text/javascript"></script>';
		$out .= '<object type="application/x-shockwave-flash" data="'.$baseurl.'fp.swf?'.$query.'" width="1" height="1" id="thm_fp">';
		$out .= '	<param name="movie" value="'.$baseurl.'fp.swf?'.$query.'" />';
		$out .= '<div></div></object>';

		return array('sessionid'=>$xml->SessionID, 'orgid'=>$xml->OrgID, 'html'=>$out);
	}

	function getFieldMap()
	{
		return array("UMkey" => 'key',
			"UMcommand" => 'command',
			"UMauthCode" => 'origauthcode',
			"UMcard" => 'card',
			"UMexpir" => 'exp',
			"UMbillamount" => 'billamount',
			"UMamount" => 'amount',
			"UMinvoice" => 'invoice',
			"UMorderid" => 'orderid',
			"UMponum" => 'ponum',
			"UMtax" => 'tax',
			"UMnontaxable" => 'nontaxable',
			"UMtip" => 'tip',
			"UMshipping" => 'shipping',
			"UMdiscount" => 'discount',
			"UMsubtotal" => 'subtotal',
			"UMcurrency" => 'currency',
			"UMname" => 'cardholder',
			"UMstreet" => 'street',
			"UMzip" => 'zip',
			"UMdescription" => 'description',
			"UMcomments" => 'comments',
			"UMcvv2" => 'cvv2',
			"UMip" => 'ip',
			"UMtestmode" => 'testmode',
			"UMcustemail" => 'custemail',
			"UMcustreceipt" => 'custreceipt',
			"UMrouting" => 'routing',
			"UMaccount" => 'account',
			"UMssn" => 'ssn',
			"UMdlstate" => 'dlstate',
			"UMdlnum" => 'dlnum',
			"UMchecknum" => 'checknum',
			"UMaccounttype" => 'accounttype',
			"UMcheckformat" => 'checkformat',
			"UMcheckimagefront" => 'checkimage_front',
			"UMcheckimageback" => 'checkimage_back',
			"UMaddcustomer" => 'addcustomer',
			"UMrecurring" => 'recurring',
			"UMbillamount" => 'billamount',
			"UMbilltax" => 'billtax',
			"UMschedule" => 'schedule',
			"UMnumleft" => 'numleft',
			"UMstart" => 'start',
			"UMexpire" => 'end',
			"UMbillsourcekey" => 'billsourcekey',
			"UMbillfname" => 'billfname',
			"UMbilllname" => 'billlname',
			"UMbillcompany" => 'billcompany',
			"UMbillstreet" => 'billstreet',
			"UMbillstreet2" => 'billstreet2',
			"UMbillcity" => 'billcity',
			"UMbillstate" => 'billstate',
			"UMbillzip" => 'billzip',
			"UMbillcountry" => 'billcountry',
			"UMbillphone" => 'billphone',
			"UMemail" => 'email',
			"UMfax" => 'fax',
			"UMwebsite" => 'website',
			"UMshipfname" => 'shipfname',
			"UMshiplname" => 'shiplname',
			"UMshipcompany" => 'shipcompany',
			"UMshipstreet" => 'shipstreet',
			"UMshipstreet2" => 'shipstreet2',
			"UMshipcity" => 'shipcity',
			"UMshipstate" => 'shipstate',
			"UMshipzip" => 'shipzip',
			"UMshipcountry" => 'shipcountry',
			"UMshipphone" => 'shipphone',
			"UMcardauth" => 'cardauth',
			"UMpares" => 'pares',
			"UMxid" => 'xid',
			"UMcavv" => 'cavv',
			"UMeci" => 'eci',
			"UMcustid" => 'custid',
			"UMcardpresent" => 'cardpresent',
			"UMmagstripe" => 'magstripe',
			"UMdukpt" => 'dukpt',
			"UMtermtype" => 'termtype',
			"UMmagsupport" => 'magsupport',
			"UMcontactless" => 'contactless',
			"UMsignature" => 'signature',
			"UMsoftware" => 'software',
			"UMignoreDuplicate" => 'ignoreduplicate',
			"UMrefNum" => 'refnum',
			'UMauxonus' => 'auxonus',
			'UMepcCode' => 'epccode',
			'UMcustreceiptname' => 'custreceiptname',
			'UMallowPartialAuth' => 'allowpartialauth',
			'UMdigitalGoods' => 'digitalgoods',
			'UMmicr' => 'micr',
			'UMsession' => 'session',
			'UMisRecurring' => 'isrecurring',
			'UMclerk' => 'clerk',
			'UMtranterm' => 'terminal',
			'UMresttable' => 'restaurant_table',
			'UMticketedEvent' => 'ticketedevent',
			'UMifAuthExpired' => 'ifauthexpired',
			'UMauthExpireDays' => 'authexpiredays',
			'UMinventorylocation' => 'inventorylocation',
			'UMduty' => 'duty',
			'UMshipfromzip' => 'shipfromzip',
			'UMsaveCard' => 'savecard'

			);
	}
	function buildQuery($data)
	{
		if(function_exists('http_build_query') && ini_get('arg_separator.output')=='&') return http_build_query($data);

		$tmp=array();
		foreach($data as $key=>$val) $tmp[] = rawurlencode($key) . '=' . rawurlencode($val);

		return implode('&', $tmp);

	}

	function httpPost($url, $data)
	{
		// if transport was not specified,  auto select transport
		if(!$this->transport)
		{
			if(function_exists("curl_version")) {
				$this->transport='curl';
			} else if(function_exists('stream_get_wrappers'))  {
				if(in_array('https',stream_get_wrappers())){
					$this->transport='stream';
				}
			}
		}


		// Use selected transport to post request to the gateway
		switch($this->transport)
		{
			case 'curl': return $this->httpPostCurl($url, $data);
			case 'stream': return $this->httpPostPHP($url, $data);
		}

		// No HTTPs libraries found,  return error
		$this->result="Error";
		$this->resultcode="E";
		$this->error="Libary Error: SSL HTTPS support not found";
		$this->errorcode=10130;
		return false;
	}

	function httpPostCurl($url, $data)
	{

		//init the connection
		$ch = curl_init($url);
		if(!is_resource($ch))
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Libary Error: Unable to initialize CURL ($ch)";
			$this->errorcode=10131;
			return false;
		}

		// set some options for the connection
		curl_setopt($ch,CURLOPT_HEADER, 1);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_TIMEOUT, ($this->timeout>0?$this->timeout:45));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

		// Bypass ssl errors - A VERY BAD IDEA
		if($this->ignoresslcerterrors)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}

		// apply custom ca bundle location
		if($this->cabundle)
		{
			curl_setopt($ch, CURLOPT_CAINFO, $this->cabundle);
		}

		// set proxy
		if($this->proxyurl)
		{
			curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt ($ch, CURLOPT_PROXY, $this->proxyurl);
		}

		$soapcall=false;
		if(is_array($data))
		{
			if(array_key_exists('xml',$data)) $soapcall=true;
		}

		if($soapcall)
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Content-type: text/xml;charset=UTF-8",
				"SoapAction: urn:ueSoapServerAction"
			));
			curl_setopt($ch,CURLOPT_POSTFIELDS,$data['xml']);

		} else {
			// rawurlencode data
			$data = $this->buildQuery($data);

			// attach the data
			curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		}

		// run the transfer
		$result=curl_exec ($ch);

		//get the result and parse it for the response line.
		if(!strlen($result))
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Error reading from card processing gateway.";
			$this->errorcode=10132;
			$this->blank=1;
			$this->transporterror=$this->curlerror=curl_error($ch);
			curl_close ($ch);
			return false;
		}
		curl_close ($ch);
		$this->rawresult=$result;

		if($soapcall) {
			return $result;
		}

		if(!$result) {
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Blank response from card processing gateway.";
			$this->errorcode=10132;
			return false;
		}

		// result will be on the last line of the return
		$tmp=explode("\n",$result);
		$result=$tmp[count($tmp)-1];

		return $result;
	}

	function httpPostPHP($url, $data)
	{


		// rawurlencode data
		$data = $this->buildQuery($data);

		// set stream http options
		$options = array(
			'http'=> array(
				'method'=>'POST',
	            'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
	                . "Content-Length: " . strlen($data) . "\r\n",
	            'content' => $data,
	            'timeout' => ($this->timeout>0?$this->timeout:45),
	            'user_agent' => 'ePayPlus PHP API v' . EPAYPLUS_VERSION . ($this->software?'/' . $this->software:'')
	        ),
	        'ssl' => array(
	            'verify_peer' => ($this->ignoresslcerterrors?false:true),
	            'allow_self_signed' => ($this->ignoresslcerterrors?true:false)
			)
		);

		if($this->cabundle) $options['ssl']['cafile'] = $this->cabundle;

		if(trim($this->proxyurl)) $options['http']['proxy'] = $this->proxyurl;


		// create stream context
		$context = stream_context_create($options);

		// post data to gateway
		$fd = fopen($url, 'r', null, $context);
		if(!$fd)
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Unable to open connection to gateway";
			$this->errorcode=10132;
			$this->blank=1;
			if(function_exists('error_get_last')) {
				$err=error_get_last();
				$this->transporterror=$err['message'];
			} else if(isset($GLOBALS['php_errormsg'])) {
				$this->transporterror=$GLOBALS['php_errormsg'];
			}
			//curl_close ($ch);
			return false;
		}

		// pull result
		$result = stream_get_contents($fd);

		// check for a blank response
		if(!strlen($result))
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Error reading from card processing gateway.";
			$this->errorcode=10132;
			$this->blank=1;
			fclose($fd);
			//$this->curlerror=curl_error($ch);
			//curl_close ($ch);
			return false;
		}

		fclose($fd);
		return $result;

	}

	function xmlentities($string)
	{
		$string = preg_replace('/[^a-zA-Z0-9 _\-\.\'\r\n]/e', '_uePhpLibPrivateXMLEntities("$0")', $string);
		return $string;
	}

}
