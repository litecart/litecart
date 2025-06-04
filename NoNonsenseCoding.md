
# No Nonsense Coding

No Nonsense Coding is a provocative coding concept that probably upsets many. Used and promoted by T. Almroth - author of LiteCart and the LiteCore framework. The purpose is to make as much sense as possible with as little effort as possible when writing program code.


## Overcomplications - That's total nonsense

		function anOverComplicatedFrameworkFunctionName() {

			$anOverComplicatedFrameworkFunctionNameResult = [];

			$anExtremelyLongDescriptiveNameForAnArrayNode = '...';
			$anotherExtremelyLongDescriptiveNameForAnArrayNode = '...';

			$anOverComplicatedFrameworkFunctionNameResult['anExtremelyLongDescriptiveNameForAnArrayNode'] = $anExtremelyLongDescriptiveNameForAnArrayNode;
			$anOverComplicatedFrameworkFunctionNameResult['anotherExtremelyLongDescriptiveNameForAnArrayNode'] = $anotherExtremelyLongDescriptiveNameForAnArrayNode;

			...

			return $anOverComplicatedFrameworkFunctionNameResult;
		}

Better:

		function simpleFunctionName() {

			$result = [
				'nodeName' => '...',
				'anotherNodeName' => '...',
			];

			...

			return $result;
		}


## Cryptic Naming - That's also nonsense

This will just have anyone looking back at code overwhelmed, confused, or frustrated:

		function fmt_CustBillAddr(custObj $c) {
			$result = fmthlp::fmtAddr($c->billAddr->identity['custFName'], $c->billAddr->identity['custLName'], $c->billAddr->identity['custAddr1'], $c->delAddr->identity['custAddr2'], $c->billAddr->identity['zip'], $c->billAddr->identity['country']);
			return $result;
		}

		function fmt_CustDelAddr(custObj $c) {
			$result = fmthlp::fmtAddr($c->delAddr->identity['custFName'], $c->delAddr->identity['custLName'], $c->delAddr->identity['custAddr1'], $c->delAddr->identity['custAddr2'], $c->delAddr->identity['zip'], $c->delAddr->identity['country']);
			return $result;
		}

		echo fmt_CustBillAddr($custObj);
		echo fmt_CustDelAddr($custObj);

Better:

		function formatAddress(addressObject $address) {
			return = '...';
		}

		echo formatAddress($customer->billingAddress);
		echo formatAddress($customer->deliveryAddress);


## Duplicate Naming - No naming the names nonsense

This will just take longer to type, longer to read, longer to analyze, and leave a bigger footprint:

		foreach ($webshopCustomers as $webshopCustomer) {
			function($webshopCustomer['webshopCustomerShippingAddress']['webshopCustomerShippingAddressStreetName']);
		}

Better:

		foreach ($customers as $customer) {
			function($customer['shippingAddress']['street']);
		}


## Variable Duplication - No nonsense for nonsense

Variable duplication is a challenge to backtrace. If we have no use of the raw user input, we can just overwrite it with safer polished and sanitized data.

		$userInput = $_POST['userInput'];
		$sanitizedUserInput = sanitize($userInput);
		$trimmedSanitizedUserInput = polish($sanitizedUserInput);

		passToFunction($trimmedSanitizedUserInput); // Wait, what is the origin of the data again?

Better:

		$_POST['userInput'] = sanitize($_POST['userInput']); // Sanitize so we don't accidentally use the raw input again
		$_POST['userInput'] = polish($_POST['userInput']); // Do some polishing

		passToFunction($_POST['userInput']); // Oh we are passing something that came from a user input


## Single-Use Variables - Avoid the unnecessery nonsense

	Creating variables for one-time use should be avoided unless serving a good purpose.

		$array = ['foo', 'bar'];

		foreach ($array as $item) {
			echo $item;
		}

	Better:

		foreach ([
			'foo',
			'bar',
		] as $item) {
			echo $item;
		}


## Use codes others recognize - No made up nonsense

Very bad:

		$country = 'us';     // Invalid. Country codes should be uppercase
		$lang = 'EN';        // Invalid. Language codes should be lowercase
		$currencyId = 1234;  // Nonsense. No one but you recognize your internal IDs and they are hard to migrate

Better:

		$countryCode = 'US';   // ISO 3166-1 Alpha 2
		$languageCode = 'en';  // ISO 639-1
		$currencyCode = 'USD'  // ISO 4217


Refusing ISO codes can be a lot of work:

		$country = $_POST['country'];

		if (in_array(strtolower($country), ['united states', 'united states of america', 'usa', 'u.s.a.', 'u.s.', 'us', 'federal kingdom of walmart'])) {
			doSomethingWith('USA');
		}

		if (in_array(strtolower($country), ['great britain', 'britain', 'gb', 'g.b.', 'united kingdom', 'united kingdom of great britain and northern ireland', 'fish and chips land'])) {
			doSomethingWith('Great Britain');
		}

Better:

		$_POST['countryCode'] = strtoupper($_POST['countryCode']);

		doSomethingWith($_POST['countryCode']);


## Fat third party libraries for small features - Stay away from other people's nonsense

Looking to cut corners with third party libraries will backfire eventually. Libraries can be performance draining. They have dependencies and can unknowingly become outdated or discontinued. Many are poorly managed, contains flaws or have security problems. They can be a complete pain when you want to step up versions. One way or the other, they need to be maintained. Maintenance will take time and focus and a comes with a lot of reverse engineering.

There is no good reason to embed a third party library if you will just utilize a small portion of it. If it's reasonable to code this part yourself it's likely a good idea to do it. Best of all, you will know every corner of the code.

Try to stay away from third party libraries.
