Balanced
========

Balanced Payments extension for Symphony CMS. This extension allows direct access to Balanced's API using Symphony CMS concepts; no PHP is necessary. It is heavily based on @lewiswharf's [Stripe extension](https://github.com/lewiswharf/stripe).

* Version: 0.1
* Date: 2013-09-09

Usage
--------

The Symphony CMS Balanced Extension uses **filters** at its heart to carry out Balanced API calls. Regular Symphony events with Balanced filters are used to call Balanced and save response data into your section. You can also setup Balanced Webhooks with the provided event.

In order to use this extension it will be important to become familiar with the required arguments and responses of each filter. Please read the [Balanced API Reference](https://docs.balancedpayments.com/current/api.html) for all the accepted fields for each call.

Setup
--------

1. Create a section for the type of call you would like to make (Customers, Debits, Credits, etc.).

2. Create an event for that section.

3. Add the filter of the call you want to make.

4. Add the event to your page.

5. Add input fields for the arguments you want to pass to Balanced.

If you want the event to actually save data from the response, your section needs to have fields with handles that exactly match Balanced's response. `_` in Balanced fields are automatically converted into `-` to match Symphony conventions.

Use the **Balanced: Webhooks Router** event in combination with events that have a webhook filter.

Filters
--------

Interacting with your Balanced account is done by applying filters to events. Only apply a single Balanced filter to each event. You could try more than one filter, but it hasn't been tested.

Arguments must be passed in the array `balanced`. For example:

    <input type="hidden" value="/v1/customers/CU7GuPv9Y2F8ySwJHuHL0YWq" name="balanced[customer_uri]" />

This filter will pass `/v1/customers/CU7GuPv9Y2F8ySwJHuHL0YWq` as an `customer_uri` argument. If the Balanced API call is successful, the filter will capture any response from Balanced and add it to the event's fields array prior to saving into the Symphony section.

FAQ
--------

### Why do I get the error `Argument 2 passed to SectionEvent::processPreSaveFilters() must be of the type array`?

Your page must include at least one input for a Symphony field, i.e. you cannot create a form with only `balanced[]` fields. If you do not want to create any field for your section, simply add `<input name="fields[]" type="hidden"/>` to your form.

