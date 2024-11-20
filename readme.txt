=== Mollie Forms ===
Contributors: ndijkstra
Donate link: https://wobbie.nl/doneren
Tags: ideal,forms,payments,subscriptions,recurring
Requires at least: 5.3
Requires PHP: 7.0
Tested up to: 6.7
Stable tag: 2.7.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create registration forms with payment methods of Mollie. One-time and recurring payments are possible.

== Description ==

Create registration forms with payment methods of Mollie. One-time and recurring payments are possible.


**Features:**

* Create your own forms
* Set extra fee's per payment method
* One-time and recurring payments
* Fixed or open amount possible
* [Multicurrency](https://www.mollie.com/features/multicurrency/)
* Configure emails per form
* Refund payments and cancel subscriptions in Wordpress admin
* Style it with your own css classes.
* Discount codes


= 3rd Party Services =
The plugin is using:

* the API of [Mollie](https://mollie.com) to create payments.
* the API of [Google reCAPTCHA](https://www.google.com/recaptcha) to prevent spam (if enabled)

== Frequently Asked Questions ==

= Why can I only choose for One-time payments? =

For recurring payments you will need a supported payment method. You have to activate "SEPA Direct Debit" or "Creditcard" to use recurring payments.

= Can I prefill the form? =

Yes! GET variables are possible to prefill form: ?form_ID_field_INDEX=value (replace ID with form id and INDEX with the field index. First field is 0, second field is 1 etc.)
For filling in the open amount, use "form_ID_amount" and for selecting a price option use "form_ID_priceoption"

= Can I use shortcodes? =

Yes! The following shortcodes are available:

* [rfmp id="ID"] To display the form. Replace ID with the id of the form
* [rfmp-total id="ID" start="0.00"] To display the total raised money. Replace ID with the id of the form (multiple ID's separated with a comma). Add start as an optional start amount.
* [rfmp-goal id="ID" goal="1000" text="Goal reached!"] Countdown to your goal. Replace ID with the id of the form, goal must be higher then 0 and the text will be displayed when the goal is reached

= Which hooks are available? =

The following action hooks with parameters are available:
* rfmp_form_submitted,      post ID, $_POST data
* rfmp_customer_created,    post ID, Mollie customer
* rfmp_payment_created,     post ID, Mollie payment
* rfmp_webhook_called,      post ID, payment ID


== Screenshots ==

1. Form settings
2. Form
3. Registrations
4. Registration with subscription
5. Registration without subscription

== Installation ==

= Minimum Requirements =

* PHP version 7.0 or greater
* PHP extensions enabled: cURL, JSON
* WordPress 5.3 or greater

== Changelog ==

= 2.7.9 - 20/11/2024 =
* Fix PHP notice about string to array conversion in formatting.php

= 2.7.8 - 19/11/2024 =
* Do not send DE as billingCountry for retrieving payment methods

= 2.7.7 - 19/11/2024 =
* Throw exception when registration not inserted into the database

= 2.7.6 - 09/10/2024 =
* Hide authorize checkbox until price option choice has been made when using lists

= 2.7.5 - 28/09/2024 =
* Fixed Uncaught TypeError: trim()

= 2.7.4 - 27/09/2024 =
* Fix when using price options table, authorize checkbox didn't show
* Fix when using price options table, incorrect message about no products selected

= 2.7.3 - 26/09/2024 =
* Hide authorize checkbox until price option choice has been made
* Fixed check if a price option is chosen

= 2.7.2 - 26/09/2024 =
* Only show empty price option when there are more than 1 price options

= 2.7.1 - 26/09/2024 =
* Don't show required asterisk in placeholder of discount code field
* Don't show "Field required" error message when entering 0 as value of required field
* Don't preselect first price option

= 2.7.0 - 23/09/2024 =
* Registrations without payment are possible
* Fixed error when viewing registration of deleted form

= 2.6.15 - 18/06/2024 =
* When using reCaptcha, it will now check for form errors instead of directly submitting the form

= 2.6.14 - 04/06/2024 =
* Add nonce verification for duplicating forms and exporting registrations

= 2.6.13 - 13/05/2024 =
* Fix saving multiple email addresses as receiver for emails

= 2.6.12 - 29/03/2024 =
* Fixed "No payment found" error on redirect
* Fixed link to registrations via forms page

= 2.6.11 - 29/03/2024 =
* Fixed "Give authorization" message
* Fixed e-mail body html usage

= 2.6.10 - 28/03/2024 =
* Fixed form fields display

= 2.6.9 - 24/03/2024 =
* Add more escaping and sanitization

= 2.6.8 - 24/03/2024 =
* Add more escaping and sanitization

= 2.6.7 - 21/03/2024 =
* Add nonces to admin form actions
* Add more escaping and sanitization

= 2.6.6 - 19/03/2024 =
* Added sanitization and escaping to output
* Added 3rd Party Services to readme

= 2.6.5 - 12/03/2024 =
* Update readme

= 2.6.4 - 22/02/2024 =
* Security fixes

= 2.6.3 - 14/12/2023 =
* Fix error when no file selected to upload
* Fix validation for recurring authorization checkbox when reCaptcha is enabled
* Fix where sometimes the webhook is later than the redirect

= 2.6.2 - 22/11/2023 =
* Fix width of table column priceoptions
* Show error message instead of throwing exception when form submitted
* Make datetime input fields for discount codes required

= 2.6.1 - 26/10/2023 =
* Fix webhook for cancelled payments

= 2.6.0 - 09/10/2023 =
* File uploads added as form field
* Fix webhook for cancelled payments

= 2.5.8 - 26/03/2023 =
* Bug fix with payment webhook

= 2.5.7 - 24/03/2023 =
* Added minimum reCaptcha acceptance score setting
* When deleting a registration, now all related rows from other tables are also deleted
* Fixed calculation in tables with VAT

= 2.5.6 - 09/06/2022 =
* Added check for minimum amount for open amount price options when reCaptcha is enabled

= 2.5.5 - 09/06/2022 =
* Fix required fields check when reCaptcha is enabled
* Use wp_remote_request instead of file_get_contents for reCaptcha

= 2.5.4 - 29/04/2022 =
* Updated Google reCaptcha integration. Secret key is now also required to make it work
* Fixed VAT calculation on registration page in admin
* Fixed issue when using prices excluding VAT in combination with the Mollie Orders API

= 2.5.3 - 06/04/2022 =
* Fixed notice on registrations page
* Fixed issue with new lines in text areas in csv export
* Updated minimum amount of price options from 0,50 to 0,01
* Using new wp_date function to display date/time for {rfmp="created_at"} variable
* Now showing proper error when customer didn't select any price options

= 2.5.2 - 06/12/2021 =
* Fixed issue with discount code field for new forms

= 2.5.1 - 26/11/2021 =
* Fixed issue with variables in payment description

= 2.5.0 - 25/11/2021 =
* Added discount codes [Read more](https://support.wobbie.nl/help/hoe-werken-de-kortingscodes-in-mollie-forms)
* Use WP timezone for created_at date in e-mail variable

= 2.4.0 - 23/11/2021 =
* Added search functionality in registrations
* Added possibility to enable e-mails when a payment got charged back

= 2.3.7 - 02/03/2021 =
* Fixed submit button when using reCAPTCHA and multiple forms on 1 page

= 2.3.6 - 22/01/2021 =
* Now using correct Registration ID in exports

= 2.3.5 - 22/01/2021 =
* Fixed submit button when not using reCAPTCHA
* Added Registration ID to the exports

= 2.3.4 - 20/01/2021 =
* Added option to enable Google reCAPTCHA v3
* Fixed incorrect way of calculating VAT

= 2.3.3 - 12/08/2020 =
* Fixed errors in Javascript that caused issues in Form Settings
* You can now add multiple ID's in the [rfmp-total] shortcode like: [rfmp-total id="12,55,346"] to sum up the totals of multiple forms

= 2.3.2 - 16/04/2020 =
* Bugfix in Webhook for recurring payments

= 2.3.1 - 10/04/2020 =
* Added variable {rfmp="method"} to show the payment method in emails
* Checkbox variables now shows Yes if checked or No if not checked
* Split-up Date and Time in exports

= 2.3.0 - 30/03/2020 =
* Added option to duplicate forms

= 2.2.6 - 24/03/2020 =
* Fixed totals when using Elementor plugin

= 2.2.5 - 21/03/2020 =
* Fix for Mailchimp for Mollie Forms plugin

= 2.2.4 - 17/03/2020 =
* Added new field type "Text", which only displays the label
* Updated support links

= 2.2.3 - 05/03/2020 =
* Fixed bug with totals and ApplePay

= 2.2.2 - 21/02/2020 =
* Fix {rfmp="url"} variable

= 2.2.1 - 06/02/2020 =
* Fixed that ApplePay was always visible when enabled

= 2.2.0 - 02/02/2020 =
* Added ApplePay as payment method
* Added option to specify a start amount to the totals shortcode: [rfmp-total id="ID" start="100.50"]

= 2.1.10 - 30/07/2019 =
* Fixed bug with interval variable in email

= 2.1.9 - 05/07/2019 =
* Bug fixes

= 2.1.8 - 07/02/2019 =
* Minor bug fixes

= 2.1.7 - 01/02/2019 =
* Fixed bug when customer bought multiple subscriptions but only 1 subscription started
* Removed brake after a checkbox
* Payment ID is now shown when using the variable in an email

= 2.1.6 - 03/01/2019 =
* Allow floats in open amount field

= 2.1.5 - 28/12/2018 =
* Fixed bug that labels were not visible for checkbox fields

= 2.1.4 - 14/12/2018 =
* Checkboxes are now displayed in front of label instead of under

= 2.1.3 - 10/12/2018 =
* Surcharging for Klarna payments is now possible. The maximum that is allowed by Klarna is EUR 1,95
* Fixed some small bugs
* Updated links to support pages

= 2.1.2 - 09/10/2018 =
* Fixed bug that causes an error on older PHP versions
* Fixed that Klarna was not available in the form

= 2.1.1 - 02/10/2018 =
* Fixed bug with redirectUrl
* Fixed small bug with CSV exports

= 2.1.0 - 02/10/2018 =
* Added option to use the Mollie [Orders API](https://docs.mollie.com/orders/overview)
* Preparations for the upcoming Klarna payment methods

= 2.0.6 - 23/09/2018 =
* Fixed bug for deleting form fields
* Added dot as thousends seperator in total and goal shortcodes

= 2.0.5 - 31/08/2018 =
* Added price option to the CSV export

= 2.0.4 - 30/08/2018 =
* It was not possible to delete price options, this is now fixed

= 2.0.3 - 30/08/2018 =
* Fixed bug in migrator when adding new totals field

= 2.0.2 - 29/08/2018 =
* Added subtotal to the totals field
* Fixed totals when chosen for open amount price option

= 2.0.1 - 29/08/2018 =
* Some small bugfixes

= 2.0.0 - 28/08/2018 =
* NEW! Added possibility to buy multiple price options at once
* NEW! You can now set VAT per price option
* NEW! You can now set stock per price option
* NEW! Totals field that displays the total amounts to the customer
* Removed shipping costs per price option
* Added shipping costs setting for whole form
* Renamed some shortcodes to mollie-forms instead of rfmp (rfmp still works)
* Use SVG images for payment methods

= 1.3.0 - 17/08/2018 =
* Added new option to change the display of the form labels
* Fix bug that causes problems with saving form when messages were not filled in
* Now the correct date/time is visible in the exports

= 1.2.5 - 20/06/2018 =
* Fixed status when payment has refunds or chargebacks
* Fixed issue with minimum amount for first price option

= 1.2.4 - 05/06/2018 =
* Added Mollie Checkout locale "Norwegian Bokmål"
* Bugfix for posting form mulitple times

= 1.2.3 - 31/05/2018 =
* Added new locales for the Mollie Checkout
* Added missing locale parameter to create payment
* Bugfix for sending emails for refunded payments

= 1.2.2 - 28/05/2018 =
* Fixed bug sending emails for canceled payments
* Now possible to set a from and to email address for merchant emails

= 1.2.1 - 17/05/2018 =
* You can now force the lanuage of the payment screen
* Fixed bug that crashes plugin

= 1.2.0 - 16/05/2018 =
* [Multicurrency](https://www.mollie.com/features/multicurrency/)! Let your customers pay in their own currency
* Updated Mollie API Client to v2.0.0
* Added setting to set the class of the form
* Added variable {rfmp="registration_id"} to emails

= 1.1.5 - 16/02/2018 =
* Updated Mollie API Client to v1.9.6

= 1.1.4 - 07/02/2018 =
* Removed URL rewrite from webhookUrl
* Fixed problem with amount field price options
* Added support page support.wobbie.nl

= 1.1.3 - 24/01/2018 =
* Fixed variables in payment description
* Fixed brakes in emails

= 1.1.2 - 10/01/2018 =
* Value of radio button fields are now stored as intended
* Paid and unpaid payments now visible in export, with new status column
* Description was not stored correctly in database, this is now fixed
* Other minor bugfixes

= 1.1.1 - 10/01/2018 =
* Bugfixes

= 1.1.0 - 03/01/2018 =
* Added add-ons page, with the first add-on Mailchimp.
* It's now possible to add an minimum amount to open prices (if not set the minimum is €1,00)

= 1.0.5 - 20/12/2017 =
* Now only the paid registrations are visible in the exports
* Added prefill parameters for open amount and price option (see FAQ)

= 1.0.4 - 19/12/2017 =
* Upgrade database when plugin is updated
* Added name, email and price option to metadata

= 1.0.3 - 04/12/2017 =
* Fixed bug with radio button values
* Fixed bug with too many brakes in emails

= 1.0.2 - 30/11/2017 =
* Added shortcode [rfmp-goal] to display a countdown to your goal. See the FAQ for more info.
* Added variable {rfmp="url"} to the emails for displaying url of page
* Added action hooks, see the FAQ for more info
* Bugfixes

= 1.0.1 - 29/11/2017 =
* Bugfix

= 1.0.0 - 27/11/2017 =
* Set redirect URL after payment instead of message

= 0.5.2 - 09/11/2017 =
* Use longtext for value field in DB

= 0.5.1 =
* Use translations from wordpress.org

= 0.5.0 =
* New feature: Creating an export of registrations per form
* No error after bank transfer payment
* {rfmp="priceoption"} is now also working in emails
* Updated Mollie Client to 1.9.4

= 0.4.3 =
* Added [rfmp-total] tag to display the total raised amount per form

= 0.4.2 =
* New feature to add shipping costs to price option

= 0.4.1 =
* Variable {rfmp="created_at"} added to email to display date/time

= 0.4.0 =
* Type "Date" added to fields
* You can now fill in your own payment description

= 0.3.13 =
* Added check to prevent a payment without registration

= 0.3.12 =
* Bugfix when using multiple forms on 1 page

= 0.3.11 =
* <a> tag now possible in field label
* Label is now behind the checkbox

= 0.3.10 =
* Removed () when open amount is selected

= 0.3.9 =
* Bugfix multiple email adresses
* Added fixed variable {rfmp="form_title"} for Form title
* Added German language

= 0.3.8 =
* Bugfix

= 0.3.7 =
* Improved variables in emails
* Multiple email addresses possible seperated with comma (,)
* Fix for images in email

= 0.3.6 =
* Added consumer information (name, iban) to payments table
* Added fixed variable {rfmp="payment_id"} for Mollie Payment ID in email templates
* GET variables possible to prefill form: ?form_ID_field_INDEX=value (replace ID with form id and INDEX with the field index. First field is 0, second field is 1 etc.)

= 0.3.5 =
* Added "Number of times" option for subscriptions

= 0.3.3 =
* Tiny fix

= 0.3.2 =
* Fix subscriptions webhook

= 0.3.1 =
* Fixed issue with empty registrations
* Payment and subscription status visible in registration list
* Subscription table bugfix
* Added French translations

= 0.3.0 =
* You can now configure emails per form

= 0.2.3 =
* Using home url now instead of site url
* Fix for frequency label at open amount

= 0.2.2 =
* Registrations are now visible for every admin user

= 0.2.1 =
* Bugfix in open amount


= 0.2.0 =
* You can now add a price option with open amount so the customer can fill in an amount
* Bugfixes

= 0.1.9 =
* Fix for showing success/error message

= 0.1.8 =
* Bugfixes
* Checkbox added for recurring payments

= 0.1.7 =
* Language fix

= 0.1.6 =
* Bug fixes

= 0.1.5 =
* Bug fixes

= 0.1.4 =
* Bug fixes

= 0.1.3 =
* Bug fixes

= 0.1.2 =
* Bug fixes

= 0.1.1 =
* Bug fixes

= 0.1.0 =
* Beta release
