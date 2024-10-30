=== Centrex Smart Web Form Builder ===
Author URI: https://www.centrexsoftware.com/
Plugin URI: https://www.centrexsoftware.com/wordpress-plugin/
Contributors: centrexsoftware
Tags: centrex software, centrex crm, crm form builder
Requires at least: 6.1
Tested up to: 6.6.1
Stable tag: 1.0
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The Centrex Smart Web Form Builder allows you to build forms on your WordPress website, fully integrating your WordPress website with Centrex CRM.

== Description ==

The Centrex Smart Web Form Builder allows you to build web forms on your WordPress website where all the data and docs collected via that web form builder will be fully integrated into your Centrex CRM, LOS, or Mobile App.  Though any web form can be built using the Centrex Smart Web Form Builder, most Centrex customers build forms for borrowers looking for money, Brokers or ISO’s looking to partner up and do business, and/or investors or syndicates looking to invest in your business finance or fintech company.

The Centrex Smart Web Form Builder has elements like integers, date, multiple choice, multiple select, note box, docs for uploading, and much more.  One of the best elements within the Centrex Smart Web Form Builder is its integration with Plaid.  Right on your website borrowers can link their bank accounts which will then securely push that financial data directly into your Centrex CRM, LOS, or mobile app.

**Docs and Support**

If you have any questions regarding the Centrex Smart Web Form Builder you can email support@CentrexSoftware.com.  You can also view the [Centrex webpage here](https://www.centrexsoftware.com/wordpress-plugin/) for the Smart App Builder to purchase the premium version of this plugin.

We Need Your Feedback and Support

The quality and upkeep of the Centrex Smart Web Form Builder relies heavily on good feedback from Centrex customers and users. If you have an idea or would like the dev team to make an enhancement, please email support@CentrexSoftware.com, and they will assist with your idea or requirement.

**Privacy Notices**

With the default configuration, the Centrex Smart Web Form Builder does not:

- Does not track users by stealth
- Write any user personal data to a database outside the Centrex CRM/LOS
- Send any data to external servers unless using the Plaid element in the form builder
- Use Cookies

If you have configured the Centrex Smart Web Form Builder in such a way where it is pulling financial data and/or credit data, then IP addresses and sessions are created and stored.  To keep your customers' financial and credit data secure, the Centrex Smart Web Form Builder will communicate with a Centrex owned, SOC II Type II compliant Microsoft Azure server.  The vendors that require a Microsoft Azure secure connection are:

- Plaid
- Meridian Link
- CLEAR

We highly recommend having a privacy policy disclaimer on your web form near the call-to-action button.

== Frequently Asked Questions ==

= Do I need a free license key to run the free version of this plugin? =

No, you only need to register for a license when using the premium version.

= Is there a limit on how many fields I can add using the free version? =

No, you can add as many fields as you would like. However, if you wish to break your form into multiple pages, you will need the [premium version](https://www.centrexsoftware.com/wordpress-plugin/).

= Do I need a Centrex Software CRM Account to use this plugin? =

Yes, please go [here](https://www.centrexsoftware.com/request-a-demo/) to schedule a demo and get started with our CRM.

= Does the free version support uploading files to the CRM?  =

No, you will need to purchase the [premium version here](https://www.centrexsoftware.com/wordpress-plugin/) to upload files directly to the CRM.

=  Do I need my own Plaid Account or do I use the Centrex Plaid Account? =

We recommend using the Centrex plaid account as we get volume discount pricing, and we are month to month when Plaid will require a long term contract if you are on your own.

== API Calls ==
* Plaid API - This API is called when a user interacts with the Plaid Link element to pass tokens between Plaid and the Centrex CRM.
    Production: https://api-app.centrexsoftware.com
    Staging: https://api-staging.centrexsoftware.com
    Dev: https://app-gateway-centrex-dev.azurewebsites.net
    Privacy Policy: https://www.centrexsoftware.com/privacy-policy/
* Centrex API - This API is used when a front-end user submits the form to send the form data from your WordPress website directly to the CRM.
    Production: https://api.centrexsoftware.com
    Privacy Policy: https://www.centrexsoftware.com/privacy-policy/

== Changelog ==

= 1.0 =
* Initial Release
