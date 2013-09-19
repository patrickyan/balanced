Balanced
========

Balanced Payments extension for Symphony CMS. This extension allows direct access to Balanced's API using Symphony CMS concepts; no PHP is necessary. It is based on @lewiswharf's [Stripe extension](https://github.com/lewiswharf/stripe).

* Version: 0.1
* Date: 2013-09-19


Usage
--------

The Symphony CMS Balanced Extension uses **filters** at its heart to carry out Balanced API calls. Regular Symphony events with Balanced filters are used to call Balanced and save response data into your section. You can also setup Balanced Webhooks with the provided event.

This extension includes:

* **[Filters](#filters)** to make calls to Balanced API
* **[Event](#webhooks)** to create webhooks
* **[Fields](#sections)** to store data and link entries
* **[Fee charging](#charging-fees)**


### Basic setup


1. Add your API key to System > Preferences > Balanced.

2. Create a section that you want to receive Balanced data.

3. Create an new event for that section.

4. Add the filter of the call you want to make.

5. Add the event to a page.

6. If the event is for a webhook, add the **Balanced: Webhooks Router** event to the page as well.

7. On the page, add a form with input fields for the arguments you want to pass to Balanced.


### Filters

Interacting with your Balanced account is done by applying filters to events. Only apply a single Balanced filter to each event. You could try more than one filter, but it hasn't been tested.

Arguments must be passed in the array `balanced[]`. For example:

    <input type="hidden" value="/v1/customers/CU7GuPv9Y2F8ySwJHuHL0YWq" name="balanced[customer_uri]" />

This filter will pass `/v1/customers/CU7GuPv9Y2F8ySwJHuHL0YWq` as an `customer_uri` argument. If the Balanced API call is successful, the filter will capture any response from Balanced and add it to the event's fields array prior to saving into the Symphony section. Passing `balanced[customer-uri]` will also work, if you prefer to stick with the Symphony convention of hyphenated names.

#### Arguments

Please read the [Balanced API Reference](https://docs.balancedpayments.com/current/api.html) for the list of accepted arguments and sample responses. Some responses are modified to simplify data storage, [as documented below](#altered-responses).


### Webhooks

Webhooks are callback endpoints that Balanced sends POST requests to upon certain events, such as successfully charging a credit card. 

To setup a webhook, create a page with the **Balanced: Webhooks Router** event and an event with a **Balanced: Webhook** filter. Then go to your Balanced settings and add a webhook pointing to that page.

You could do more fancy stuff with a custom PHP event.


### Sections

If you want the event to save data from Balanced's response, your section needs to have fields with handles that exactly match the response. `_` in Balanced fields are automatically converted into `-` to match Symphony convention. 

For example, if you call **Balanced: Create Customer**, you might send a form with:

	"balanced" :
		"name" : "John Doe"
	"action" :
		"create-customer" : "submit"

and you will get back

	"fields" :
		"name" : "John Doe"
		"created-at" : "2013-09-19T19:22:27.785483Z"
		"uri": /v1/customers/AC6oBn0TTBMavvoK7ROufYU7
		"transactions-uri" : "/v1/customers/AC6oBn0TTBMavvoK7ROufYU7/transactions"
		"type" : "customer"
		"uris" : ...

**DO NOT SAVE CREDIT CARD NUMBERS. IT IS ILLEGAL.** Balanced never exposes sensitive information, so it's okay to save whatever is in the responses. Please use [balanced.js](https://docs.balancedpayments.com/current/overview.html#balanced-js) to tokenize credit cards and bank accounts.

#### Fields

This extension comes with 4 fields.

 * Balanced: Customer URI
 * Balanced: Customer Link
 * Balanced: Resource URI
 * Balanced: Resource Link

You can only use Customer URI and Resource URI once. This means you cannot create multiple customer sections or transaction sections.

#### Recommended data structure

While you do not have to store the entire response, you should store some data, like the customer's URI, so you can charge them later! Here is an example setup:

	Customers
		Name (input)
		URI (balanced_customer_uri)
		Card URI (input)
		Created at (date)
	
	Transactions
		Type (input)
		Status (input)
		URI (balanced_resource_uri)
		Created at (date)
		Amount (number)
		Customer URI (balanced_customer_link)
		On behalf of URI (balanced_customer_link)

Notice the `balanced_customer_link`s used that will link the transaction entry to the appropriate customer.

If you have webhooks, you can create another section that link to your transactions:

	Events
		Event URI (input)
		Entity URI (balanced_resource_link)
		Resource (input)
		Type (input)


### Charging fees

You might want to charge users fees on each purchase. This extension makes it easy to charge variable and/or fixed fees on debits and/or credits.

1. Specify the fees in System > Preferences > Balanced.
2. When debiting or crediting, instead of sending `balanced[amount]`, send `balanced[subamount]`.
3. Fees are automatically calculated and added.
4. Response includes `amount`, `subamount`, and `fees`.

Note: Fees are rounded up to the nearest cent. Debit amounts are calculated as `subamount + fees`, while credit amounts are calculated as `subamount - fees`.

You can also override the fixed fees by sending any amount via `balanced[fees]`.

If you don't want to charge fees, just use `balanced[amount]`.


Arguments &amp; responses
--------

#### Balanced: Create Customer

https://docs.balancedpayments.com/current/api.html#creating-a-customer

#### Balanced: Create Customer and Add Card

https://docs.balancedpayments.com/current/api.html#creating-a-customer
https://docs.balancedpayments.com/current/api.html#adding-a-card-to-a-customer

#### Balanced: Create Customer and Add Bank Account

https://docs.balancedpayments.com/current/api.html#creating-a-customer
https://docs.balancedpayments.com/current/api.html#adding-a-bank-account-to-a-customer

#### Balanced: Update Customer

https://docs.balancedpayments.com/current/api.html#creating-a-customer

#### Balanced: Delete Customer

https://docs.balancedpayments.com/current/api.html#delete-a-customer

#### Balanced: Add Card

https://docs.balancedpayments.com/current/api.html#adding-a-card-to-a-customer

#### Balanced: Add Bank Account

https://docs.balancedpayments.com/current/api.html#adding-a-bank-account-to-a-customer

#### Balanced: Create Bank Verification

https://docs.balancedpayments.com/current/api.html#verifying-a-bank-account

#### Balanced: Update Bank Verification

https://docs.balancedpayments.com/current/api.html#confirm-a-bank-account-verification

#### Balanced: Create Debit

https://docs.balancedpayments.com/current/api.html#create-a-new-debit

#### Balanced: Refund Debit

https://docs.balancedpayments.com/current/api.html#create-a-new-debit

#### Balanced: Create Credit

https://docs.balancedpayments.com/current/api.html#credit-an-existing-bank-account

#### Balanced: Webhook Debit, Credit, Hold, Refund

https://docs.balancedpayments.com/current/api.html#event-types


### Altered Responses

Some responses are altered to fit the Symphony data model better.

* `_` changed to `-`
* `_xyz` changed to `xyz`
* `null` responses removed (to prevent wiping out your section)
* `amount` converted from cents back to dollars
* Bank Account Verification responses prefixed with `bank_account_verification_` (so you can store in your customers section without conflicts)
* Refund responses prefixed with `refund_` (so you can store in your transactions section without conflicts)
* Debits: `customer_uri` and `on_behalf_of_uri` added
* `id` removed (to avoid conflict with Symphony system id; Balanced recommends not storing this  value)


Credit card/bank account form
--------

If you are using `balanced.js` and Balanced's example script to tokenize your cards, make sure you change

	<input name="action[YOUR-EVENT]" type="submit" value="Submit"/>

to

	<input name="action[YOUR-EVENT]" type="hidden"/>
	<input type="submit" value="Submit"/>

as using jQuery's `submit()` method will **not** add the `action[YOUR-EVENT]` field if it is on the submit button.


FAQ
--------

### Why do I get the error "Argument 2 passed to SectionEvent::processPreSaveFilters() must be of the type array"?

Your page must include at least one input for a Symphony field, i.e. you cannot create a form with only `balanced[]` fields. If you do not want to pass any non-Balanced fields for your section, simply add `<input name="fields[]" type="hidden"/>` to your form.


Release notes
--------
