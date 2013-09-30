// FOR DEMONSTRATION PURPOSES ONLY - if you already have a server you can POST to, replace the URL with the URL to post to.
// go to http://requestb.in/
// click create new request bin and COPY that URL without the ?inspect at the end

var marketplaceUri = 'YOUR_TEST_MARKETPLACE_URI';
balanced.init(marketplaceUri);

var clickedFormID;

function completeForm(inputValue, balancedName, fieldsName) {
	var form = $("#" + clickedFormID);

	$('<input>').attr({
		type: 'hidden',
		value: inputValue,
		name: 'balanced[' + balancedName + ']'
	}).appendTo(form);

	$('<input>').attr({
		type: 'hidden',
		value: inputValue,
		name: 'fields[' + fieldsName + ']'
	}).appendTo(form);

	form.get(0).submit();
}

function callbackCard(response) {
	switch (response.status) {
	case 400:
		// missing or invalid field - check response.error for details
		console.log(response.error);
		break;
	case 404:
		// your marketplace URI is incorrect
		console.log(response.error);
		break;
	case 201:
		// WOO HOO! MONEY!
		console.log(response.data);

		// the uri is an opaque token referencing the tokenized bank account
		var cardTokenURI = response.data['uri'];
		// append the token as a hidden field to submit to the server
		completeForm(cardTokenURI, 'card_uri', 'card-uri');
	}
}

function callbackBankAccount(response) {
	switch (response.status) {
		case 400:
			// missing or invalid field - check response.error for details
			console.log(response.error);
			break;
		case 404:
			// your marketplace URI is incorrect
			console.log(response.error);
			break;
		case 201:
			// WOO HOO! MONEY!
			// response.data.uri == URI of the bank account resource you
			// should store this bank account URI to later credit it
			console.log(response.data);

			// the uri is an opaque token referencing the tokenized bank account
			var bankAccountURI = response.data['uri'];
			// append the token as a hidden field to submit to the server
			completeForm(bankAccountURI, 'bank_account_uri', 'bank-account-uri');
		 }
 }


var tokenizeCard = function(e) {
	e.preventDefault();

	var form = $(this);
	clickedFormID = form.attr('id');
	var creditCardData = {
		card_number: form.find('.cc-number').val(),
		expiration_month: form.find('.cc-em').val(),
		expiration_year: form.find('.cc-ey').val(),
		security_code: form.find('.cc-csc').val()
	};

	balanced.card.create(creditCardData, callbackCard);
};


var tokenizeBankAccount = function(e) {
		e.preventDefault();

		var form = $(this);
		clickedFormID = form.attr('id');
		var bankAccountData = {
			name: form.find('.ba-name').val(),
			account_number: form.find('.ba-an').val(),
			bank_code: form.find('.ba-rn').val(),
			type: form.find('select[name="account-type"]').val()
		};

		console.log(bankAccountData);

		balanced.bankAccount.create(bankAccountData, callbackBankAccount);
};

var creditCardForms = $('.credit-card-form')
creditCardForms.each(function() {
	$(this).submit(tokenizeCard);
});

var bankAccountForms = $('.bank-account-form')
bankAccountForms.each(function() {
	$(this).submit(tokenizeBankAccount);
});

$('select.select-customer').change(function() {
	var selectValue =$(this).val();
	var customerID = $(this).children('option[value="' + selectValue + '"]').data('id');
	$(this).next('input[name=id]').val(customerID);
});

$('select.select-bank-account').change(function() {
	var selectValue =$(this).val();
	var customerURI = $(this).children('option[value="' + selectValue + '"]').data('customer-uri');
	$(this).next('input[name*=customer-uri]').val(customerURI);
});



