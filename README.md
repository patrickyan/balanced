Balanced
========

Balanced Payments extension for Symphony CMS. This extension allows direct access to Balanced's API using Symphony CMS concepts; no PHP is necessary. It is heavily based on @lewiswharf's [Stripe extension](https://github.com/lewiswharf/stripe).

Usage
--------

The Symphony CMS Balanced Extension uses filters at its heart to carry out Balanced API calls.

In order to use this extension it will be important to become familiar with the required arguments and responses of each filter. If you want response data saved from the Balanced response into a section, that section needs to have the filter and the section's field handles must match Balanced's response (`_` are automatically converted into `-`, and vice a versa). This allows the greatest flexibility allowing the Symphony CMS developer to choose what information they want to save in the section.

Use the **Balanced: Webhooks Router** event in combination with events that have a webhook filter.

Filters
--------

Interacting with your Balanced account is done by applying filters to events. Only apply a single Balanced filter to an event. You could try doing more than one filter but it's not something I have considered - mileage may vary.

Filters have required fields. Additional information regarding arguments can be obtained from the individual links below. To pass arguments to the filter to be used in the Balanced API call, prefixed names with `balanced`. For instance:

    <input type="hidden" value="/v1/customers/CU7GuPv9Y2F8ySwJHuHL0YWq" name="balanced[customer_uri]" />

In the above example, the filter will pass this field value as an `customer_uri` argument. If the Balanced API call is successful, the filter will include any response from Balanced and add it to the event's fields array prior to saving into the Symphony section.
