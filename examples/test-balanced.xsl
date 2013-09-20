<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="xml"
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
	omit-xml-declaration="yes"
	encoding="UTF-8"
	indent="yes" />

<xsl:template match="/">
<html>
	<head>
		<style>
			@import url('//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css');
			body {
				width: 500px;
				margin: 0 auto;
			}
			label {
				display: block;
			}
		</style>
	</head>
	<body>
		<h1><xsl:value-of select="$page-title"/></h1>


		<!-- CREATING CUSTOMERS -->


		<h2>Create customer</h2>
		<form method="post" action="" enctype="multipart/form-data">
			<input name="MAX_FILE_SIZE" type="hidden" value="5242880" />

			<label>Name
				<input name="balanced[name]" type="text"/>
			</label>

			<input name="fields[]" type="hidden"/>
			<input name="action[test-create-customer]" type="submit" value="Submit" />
		</form>



		<h2>Create customer and add card</h2>
		<form class="credit-card-form" id="credit-card-form-1" method="post" action="" enctype="multipart/form-data">
			<input name="MAX_FILE_SIZE" type="hidden" value="5242880" />

			<label>Name
				<input name="balanced[name]" type="text"/>
			</label>

			<xsl:call-template name="credit-card-form"/>

			<input name="fields[]" type="hidden"/>
			<input name="action[test-create-customer-and-add-card]" type="hidden" value="Submit" />
			<input type="submit" value="Submit" />
		</form>



		<h2>Create customer and add bank account</h2>
		<form class="bank-account-form" id="bank-account-form-1" method="POST">

			<label>Name
				<input name="balanced[name]" type="text"/>
			</label>

			<xsl:call-template name="bank-account-form"/>

			<input name="fields[]" type="hidden"/>
			<input name="action[test-create-customer-and-add-bank-account]" type="hidden" value="Submit" />
			<input type="submit" value="Submit" />
		</form>


		<!-- UPDATING CUSTOMERS-->


		<h2>Update customer</h2>
		<form method="post" action="" enctype="multipart/form-data">
			<input name="MAX_FILE_SIZE" type="hidden" value="5242880" />

			<xsl:call-template name="get-customers"/>

			<label>Name
				<input name="balanced[name]" type="text"/>
			</label>
			<input name="fields[]" type="hidden"/>
			<input name="action[test-update-customer]" type="submit" value="Submit" />
		</form>



		<h2>Add card to customer</h2>
		<form class="credit-card-form" id="credit-card-form-2" method="post" action="" enctype="multipart/form-data">
			<input name="MAX_FILE_SIZE" type="hidden" value="5242880" />

			<xsl:call-template name="get-customers"/>

			<xsl:call-template name="credit-card-form"/>

			<input name="fields[]" type="hidden"/>
			<input name="action[test-add-card]" type="hidden" value="Submit" />
			<input type="submit" value="Submit" />
		</form>



		<h2>Add bank account to customer</h2>
		<form class="bank-account-form" id="bank-account-form-2" method="POST">

			<xsl:call-template name="get-customers"/>

			<xsl:call-template name="bank-account-form"/>

			<input name="fields[]" type="hidden"/>
			<input name="action[test-add-bank-account]" type="hidden" value="Submit" />
			<input type="submit" value="Submit" />
		</form>


		<!-- VERIFYING BANK ACCOUNTS -->


		<h2>Create bank account verification</h2>
		<form method="POST">

			<xsl:call-template name="get-customers">
				<xsl:with-param name="value" select="'bank_account_uri'"/>
			</xsl:call-template>

			<input name="fields[]" type="hidden"/>
			<input name="action[test-create-bank-account-verification]" type="hidden" value="Submit" />
			<input type="submit" value="Submit" />
		</form>



		<h2>Update bank account verification</h2>
		<form method="POST">

			<xsl:call-template name="get-customers">
				<xsl:with-param name="value" select="'bank_account_verification_uri'"/>
			</xsl:call-template>

			<label>Amount 1
				<input type="text" name="balanced[amount_1]" value="0.01"/>
			</label>
			<label>Amount 2
				<input type="text" name="balanced[amount_2]" value="0.01"/>
			</label>

			<input name="fields[]" type="hidden"/>
			<input name="action[test-update-bank-account-verification]" type="hidden" value="Submit" />
			<input type="submit" value="Submit" />
		</form>


		<!-- TRANSACTIONS -->


		<h2>Create debit (explicit amount)</h2>
		<form method="POST">

			<label>Customer
				<select name="balanced[customer_uri]">
					<option></option>
					<xsl:for-each select="/data/test-customers/entry">
						<option value="{uri}" data-id="{@id}">
							<xsl:value-of select="name"/>
						</option>
					</xsl:for-each>
				</select>
			</label>

			<label>On Behalf Of
				<select name="balanced[on_behalf_of_uri]">
					<option></option>
					<xsl:for-each select="/data/test-customers/entry[bank-account-uri != '']">
						<option value="{uri}" data-id="{@id}">
							<xsl:value-of select="name"/>
						</option>
					</xsl:for-each>
				</select>
			</label>

			<label>Amount
				<input type="text" name="balanced[amount]" value="100.00"/>
			</label>

			<input name="fields[]" type="hidden"/>
			<input name="action[test-create-debit]" type="hidden" value="Submit" />
			<input type="submit" value="Submit" />
		</form>



		<h2>Create debit (subamount)</h2>
		<form method="POST">

			<label>Customer
				<select name="balanced[customer_uri]">
					<option></option>
					<xsl:for-each select="/data/test-customers/entry">
						<option value="{uri}" data-id="{@id}">
							<xsl:value-of select="name"/>
						</option>
					</xsl:for-each>
				</select>
			</label>

			<label>On Behalf Of
				<select name="balanced[on_behalf_of_uri]">
					<option></option>
					<xsl:for-each select="/data/test-customers/entry[bank-account-uri != '']">
						<option value="{uri}" data-id="{@id}">
							<xsl:value-of select="name"/>
						</option>
					</xsl:for-each>
				</select>
			</label>

			<label>Subamount
				<input type="text" name="balanced[subamount]" value="100.00"/>
			</label>

			<label>Fees
				<input type="text" name="balanced[fees]" value=""/>
			</label>

			<input name="fields[]" type="hidden"/>
			<input name="action[test-create-debit]" type="hidden" value="Submit" />
			<input type="submit" value="Submit" />
		</form>



		<h2>Refund debit</h2>
		<form method="POST">

			<label>Debit
				<select class="select-customer" name="balanced[debit_uri]">
					<option></option>
					<xsl:for-each select="/data/test-debits/entry">
						<option value="{uri}" data-id="{@id}">
							<xsl:value-of select="uri"/>
						</option>
					</xsl:for-each>
				</select>
				<input type="hidden" name="id" value=""/>
			</label>

			<input name="fields[]" type="hidden"/>
			<input name="action[test-refund-debit]" type="hidden" value="Submit" />
			<input type="submit" value="Submit" />
		</form>


		<h2>Create credit (explicit amount)</h2>
		<form method="POST">

			<label>Customer
				<select class="select-bank-account" name="balanced[bank_account_uri]">
					<option></option>
					<xsl:for-each select="/data/test-customers/entry[bank-account-uri != '']">
						<option value="{bank-account-uri}" data-customer-uri="{uri}">
							<xsl:value-of select="name"/>
						</option>
					</xsl:for-each>
				</select>
				<input type="hidden" name="fields[customer-uri]"/>
			</label>

			<label>Amount
				<input type="text" name="balanced[amount]" value="5.00"/>
			</label>

			<input name="fields[]" type="hidden"/>
			<input name="action[test-create-credit]" type="hidden" value="Submit" />
			<input type="submit" value="Submit" />
		</form>



		<h2>Create credit (subamount)</h2>
		<form method="POST">

			<label>Customer
				<select class="select-bank-account" name="balanced[bank_account_uri]">
					<option></option>
					<xsl:for-each select="/data/test-customers/entry[bank-account-uri != '']">
						<option value="{bank-account-uri}" data-customer-uri="{uri}">
							<xsl:value-of select="name"/>
						</option>
					</xsl:for-each>
				</select>
				<input type="hidden" name="fields[customer-uri]"/>
			</label>

			<label>Subamount
				<input type="text" name="balanced[subamount]" value="50.00"/>
			</label>

			<label>Fees
				<input type="text" name="balanced[fees]" value=""/>
			</label>

			<input name="fields[]" type="hidden"/>
			<input name="action[test-create-credit]" type="hidden" value="Submit" />
			<input type="submit" value="Submit" />
		</form>


		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" ></script>
		<script src="https://js.balancedpayments.com/v1/balanced.js"></script>
		<script src="{$workspace}/js/test-balanced.js"></script>
	</body>
</html>
</xsl:template>



<xsl:template name="get-customers">
	<xsl:param name="value" select="'customer_uri'"/>

	<label>Customer
		<select class="select-customer" name="balanced[{$value}]">
			<option></option>
			<xsl:for-each select="/data/test-customers/entry">
				<option data-id="{@id}">
					<xsl:attribute name="value">
						<xsl:choose>
							<xsl:when test="$value = 'customer_uri'">
								<xsl:value-of select="uri"/>
							</xsl:when>
							<xsl:when test="$value = 'bank_account_uri'">
								<xsl:value-of select="bank-account-uri"/>
							</xsl:when>
							<xsl:when test="$value = 'bank_account_verification_uri'">
								<xsl:value-of select="bank-account-verification-uri"/>
							</xsl:when>
						</xsl:choose>
					</xsl:attribute>

					<xsl:value-of select="name"/>
				</option>
			</xsl:for-each>
		</select>
		<input type="hidden" name="id" value=""/>
	</label>
</xsl:template>




<xsl:template name="credit-card-form">
	<label>Card Number
		<input type="text"
					 autocomplete="off"
					 placeholder="Card Number"
					 class="cc-number" value="4111111111111111"/>
	</label>
	<label>Expiration
		<input type="text"
					 autocomplete="off"
					 placeholder="Expiration Month"
					 class="cc-em" value="01"/>
		<span>/</span>
		<input type="text"
					 autocomplete="off"
					 placeholder="Expiration Year"
					 class="cc-ey" value="2020"/>
	</label>
	<label>Security Code (CSC)
		<input type="text"
					 autocomplete="off"
					 placeholder="CSC"
					 class="cc-csc" value="123"/>
	</label>
</xsl:template>



<xsl:template name="bank-account-form">
	<label>Account Holder's Name
			<input type="text"
						 autocomplete="off"
						 placeholder="Bank Account Holder's name"
						 class="ba-name"
						 value="John Q. TaxPayer"/>
	</label>
	<label>Routing Number
			<input type="text"
						 autocomplete="off"
						 placeholder="Routing Number"
						 class="ba-rn"
						 value="121000358"/>
	</label>
	<label>Account Number
			<input type="text"
						 autocomplete="off"
						 placeholder="Account Number"
						 class="ba-an"
						 value="9900000001"/>
	</label>
	<label>Account Type
			<select name="account-type">
					<option value="" disabled="disabled" selected="selected" style="display:none;">
							Select Account Type
					</option>
					<option value="checking" default="default">CHECKING</option>
					<option value="savings">SAVINGS</option>
			</select>
	</label>
</xsl:template>

</xsl:stylesheet>