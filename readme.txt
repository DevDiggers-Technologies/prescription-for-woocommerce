=== Prescription for WooCommerce ===
Contributors: devdiggers
Plugin URI: https://devdiggers.com/product/woocommerce-medical-prescription-attachment/
Author: DevDiggers
Author URI: https://devdiggers.com/
Tags: prescription, pharmacy, prescription upload, order approval, medical
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
WC requires at least: 9.0
WC tested up to: 11.1
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Require a prescription upload at WooCommerce checkout, hold the order until a pharmacist approves it, and keep every file private on your server.

== Description ==

Selling prescription medicine online comes down to three rules. The customer cannot check out without a prescription. Nothing ships before someone qualified has looked at it. And the prescription never ends up in a folder the public can open.

**[Prescription for WooCommerce](https://devdiggers.com/product/woocommerce-medical-prescription-attachment/)** by DevDiggers handles all three for online pharmacies, chemists and medical stores. Pick the products that need a prescription. Customers upload it on the cart or checkout page, and the order waits in a holding status until your pharmacist approves or rejects the prescription from the WooCommerce order screen.

There is no external service, no API key and no fee per prescription. Every file stays on your own server, in a private folder that only the customer and your reviewers can open.

= Quick links =

* [View Demo](https://demo.devdiggers.com/woocommerce-medical-prescription-attachment/)
* [Documentation](https://docs.devdiggers.com/woocommerce-medical-prescription-attachment/)
* [Support](https://wordpress.org/support/plugin/prescription-for-woocommerce/)
* [Upgrade to Pro](https://devdiggers.com/product/woocommerce-medical-prescription-attachment/)

= How it works =

1. You choose which products need a prescription: whole categories, the entire store, or single products.
2. The customer adds a product and uploads a photo or scan of their prescription on the cart or checkout page.
3. WooCommerce creates the order and moves it to a holding status, such as On hold, so it is not fulfilled yet.
4. Your pharmacist opens the order, views the file and approves it, rejects it, or asks the customer for more information.
5. The customer gets an email, and the order moves to the status you picked, for example Processing after an approval.

= What the free plugin does =

=== Require a prescription for selected products ===

Not every product in a pharmacy needs a prescription. You decide where the rule applies, and customers see it before they add anything to the cart.

* Require a prescription for chosen product categories, or leave the list empty to require one for every product
* Exclude single products from a category rule
* Set any product to always require a prescription, never require one, or follow the category rule
* A "Requires Prescription" label on shop, category and product pages, with your own text and colours
* A short note for customers explaining what to upload
* Works with simple and variable products

=== Prescription upload on the cart and checkout page ===

Customers attach their prescription where they are already buying. The upload box supports drag and drop, and you choose where it appears on each page.

* Upload box on the cart page, the checkout page and the order details page
* Works with the classic checkout and the WooCommerce Cart and Checkout blocks
* Accepts JPG, PNG, WEBP, HEIC, PDF, DOC and DOCX files
* Several files per order, up to a limit you set
* Guests and logged in customers can both upload at checkout
* Checkout is blocked on the server until a prescription is attached, so it cannot be skipped by turning off JavaScript or calling the Store API directly
* Attach Later lets logged in customers place the order now and upload the prescription from their order page afterwards

=== Hold the order until a pharmacist approves the prescription ===

This is the part that keeps prescription medicine from shipping early. New prescription orders go to a holding status and stay there until someone makes a decision.

* Choose the holding status, such as On hold
* The hold stays in place even with Cash on Delivery, bank transfer and card gateways that would normally move a paid order straight to Processing
* Approve, reject or request more information from the WooCommerce order screen, with a message the customer can read
* Choose where the order goes on approval and on rejection
* Require a reason before a prescription can be rejected, so no customer is refused without an explanation
* Every decision written to the order notes

=== Prescription orders screen and review queue ===

All orders with a prescription are in one place, so your pharmacist is not searching through every WooCommerce order.

* Orders screen with a tab for each prescription status: awaiting upload, pending review, approved, rejected and information requested
* Search by order number, billing email or customer name
* Prescription status column on the WooCommerce orders list
* Dashboard with orders awaiting review, approved, rejected and waiting on the customer, an activity chart, the status mix and the prescriptions that have waited longest
* Setup wizard that switches the workflow on in a few short steps

=== Private prescription file storage ===

A prescription is a health record, so the plugin never leaves it in the public uploads folder.

* Files are saved to a private folder with a random name that is unique to your site
* The folder includes deny rules for Apache and IIS servers
* Files open only through a checked link, for the customer who uploaded them and for administrators and shop managers
* Anyone else who opens the link, even with the full URL, sees an Access denied page
* Prescription files are hidden from the Media Library for everyone except administrators

=== Prescription emails and customer re-upload ===

Customers know where their order stands without contacting you.

* Customer emails when a prescription is approved, rejected or needs more information, sent with your WooCommerce email template and including the reviewer's message
* An email to the store when a new prescription is waiting for review
* Recipients, format and footer managed under WooCommerce > Settings > Emails
* Self Service shows a refused customer the reviewer's message on their order page and lets them upload a new file there

There is no limit on orders, prescriptions, files or reviewers in the free version.

= Who this is for =

Online pharmacies and chemists selling prescription only medicine. Medical supply stores that need a doctor's prescription for certain equipment. Optical stores that ask for a copy of the eye prescription before selling contact lenses. Any WooCommerce store where a qualified person has to check a document before an order is fulfilled.

The plugin collects and stores the prescription document and gives your team an approval workflow. It does not read the file automatically, and it does not replace the checks your local pharmacy rules require.

= Free vs Pro =

The free plugin covers the full workflow, from the upload at checkout to the approval email. [Prescription for WooCommerce Pro](https://devdiggers.com/product/woocommerce-medical-prescription-attachment/) is for pharmacies with more volume, a review team or stricter record keeping. It installs on top of the free plugin.

=== Faster prescription review (Pro) ===

* Review workspace with the file, the details and the decision side by side, zoom and rotate, and a jump to the next prescription without a page reload
* Approve or reject several orders at once from the Orders screen
* Saved replies for the refusal messages you write every day
* Pharmacist user role that can review prescriptions without shop manager access
* Assign prescriptions to a named pharmacist, set a review target in hours and see overdue badges

=== Prescription details and prescriber checks (Pro) ===

* Ask for the patient name and age, prescribing doctor, registration or licence number, date of issue and expiry date next to the upload box, and choose which are mandatory
* Prescriber registry to mark doctors as verified, on watch or blocked, with a warning when one appears on an order
* Prescriber verification link, a private expiring link the doctor can use to confirm the prescription without an account
* Duplicate warning when the same file was already used on another order

=== Repeat customers and refills (Pro) ===

* Prescriptions tab in My Account listing every prescription with its status and files
* Returning customers reuse an approved prescription at checkout instead of uploading again, counted against a refill allowance
* Approved prescriptions expire after a period you set, with a warning email before they lapse
* Reminder emails for orders still waiting for a prescription
* Maximum quantity of a controlled product per order

=== Records, emails and reports (Pro) ===

* Audit trail of every decision and an access log of who opened each file, with time and IP address
* Consent checkbox at checkout, with the wording, date and IP address stored on the order
* Data retention that deletes old prescription files, or files and patient details, on a schedule
* Email editor for the subject, heading and body of every customer email
* Pharmacy analytics with approval rate, review time, revenue approved and on hold, top prescribers and products, reviewer activity and refusal reasons
* CSV export of prescriptions

== Installation ==

= Automatic installation =

1. Go to **Plugins > Add New** in your WordPress admin.
2. Search for "Prescription for WooCommerce" by DevDiggers.
3. Click **Install Now**, then **Activate**.
4. Follow the setup wizard, or go to **Prescriptions > Configuration**.

= Manual installation =

1. Download the plugin ZIP file.
2. Go to **Plugins > Add New > Upload Plugin** and choose the ZIP file.
3. Click **Install Now**, then **Activate**.

= After activating =

1. Run the setup wizard, or turn the plugin on under **Prescriptions > Configuration > General**.
2. Choose the product categories that need a prescription.
3. Pick the holding status and the approval status under **Configuration > Review Workflow**.
4. Place a test order to see the upload box and the approval flow.

= Requirements =

* WordPress 6.5 or higher
* WooCommerce 9.0 or higher
* PHP 7.4 or higher

== Frequently Asked Questions ==

= How do I require a prescription upload before checkout in WooCommerce? =

Install the plugin, turn it on under **Prescriptions > Configuration > General**, and choose the categories that need a prescription. Customers buying those products see an upload box on the cart and checkout pages, and they cannot place the order until a file is attached.

= Can I require a prescription for some products only? =

Yes. Choose categories under **Configuration > General** and exclude single products there. You can also open any product, go to its General tab and set it to always or never require a prescription.

= Will the order ship before the prescription is approved? =

Not if you set a holding status under **Configuration > Review Workflow**. New prescription orders move to that status and stay there even when the payment gateway would normally mark them Processing. Approving the prescription moves the order to the status you chose for approvals.

= How does a pharmacist approve or reject a prescription? =

Open the order from **Prescriptions > Orders** or the WooCommerce orders list. The Medical Prescription Review box shows the uploaded files. Choose a decision (approve, reject or request more information), add a message for the customer if needed, and update the order.

= Does it work with the WooCommerce block checkout? =

Yes. The upload box appears in the Cart and Checkout blocks, and the prescription requirement is also checked through the Store API.

= Can guests upload a prescription? =

Yes, guests can upload at checkout. Attach Later, which lets a customer place the order first and upload afterwards, is only for logged in customers, because they need their order page to come back to.

= Which file types can customers upload? =

JPG, PNG, WEBP, HEIC, PDF, DOC and DOCX. You set the maximum number of files per order under **Configuration > Prescription Rules**.

= Where are prescription files stored, and who can see them? =

On your own server, in a private folder inside wp-content/uploads with a random name unique to your site. Files open only through a checked link, for the customer who uploaded them, administrators and shop managers. Anyone else gets an Access denied page. The folder location is shown under **Configuration > Compliance** so you can include it in backups.

= Does it work on nginx? =

Yes. nginx ignores the Apache and IIS deny rules, but the random folder name keeps the files from being found, and every file is still served through the checked link.

= Does the plugin verify prescriptions automatically? =

No. A person on your team reviews each prescription. The plugin does not send files to any outside or AI service. Pro adds tools that help with the check, such as a prescriber registry, duplicate file warnings and a verification link sent to the prescribing doctor.

= Can a customer replace a rejected prescription? =

Yes, with Self Service turned on under **Configuration > Customer Experience**. The customer sees the reviewer's message on their order page and can upload a new file there.

= Can I change the wording of the prescription emails? =

The free version sends complete messages for approved, rejected and more information requests, and includes the reviewer's message. Recipients, email format and footer text are set in **WooCommerce > Settings > Emails**. Rewording the subject, heading and body of each email is a Pro feature.

= Can I use it for contact lenses or glasses? =

Yes, if you need the customer to upload a copy of their prescription before you sell to them. It does not add a form for lens values such as sphere, cylinder or pupillary distance.

= Does this make my store HIPAA or GDPR compliant? =

No plugin can do that on its own. This plugin keeps prescriptions on your server in a private folder and limits who can open them, which helps. Consent records, access logs and scheduled deletion of old prescriptions are Pro features. Check the rules that apply to your pharmacy with a qualified advisor.

= Is there a limit on orders or prescriptions? =

No. The free version has no cap on orders, prescriptions, files or reviewers.

= Does it support HPOS? =

Yes. The plugin declares compatibility with WooCommerce High Performance Order Storage and with the Cart and Checkout blocks.

= Will my prescriptions be kept if I upgrade to Pro? =

Yes. Pro installs on top of the free plugin and reads the same order data, so every existing prescription stays in place.

= Can I translate the plugin? =

Yes. It is translation ready and ships with a POT file in the `i18n` folder.

= Where can I get help? =

Use the [WordPress.org support forum](https://wordpress.org/support/plugin/prescription-for-woocommerce/) or [contact DevDiggers](https://devdiggers.com/contact/).

== Screenshots ==

1. Dashboard with the review queue, activity chart and prescription status mix
2. Prescription orders screen with status tabs and search
3. Approving or rejecting a prescription on the WooCommerce order screen
4. Prescription upload box on the checkout page
5. "Requires Prescription" label on the shop page
6. Customer replacing a rejected prescription from the order page
7. Review workflow settings with the holding, approval and rejection statuses
8. Setup wizard

== External services ==

This plugin does not send prescriptions, customer data or order data to any outside service. The bundled DevDiggers framework, which draws the admin screens, connects to the services below. All of them run only inside the WordPress admin.

**1. DevDiggers extensions directory**

* What it is: A read only API on devdiggers.com that returns the public list of DevDiggers extensions.
* What it is used for: Showing available DevDiggers extensions on the Extensions admin page.
* When data is sent: Only when a logged in administrator opens the Extensions admin page. The response is cached for 24 hours.
* What data is sent: A standard outbound HTTP request only, meaning your server's IP address and a plugin user agent string. No personal data and no store data are sent.
* Endpoint: https://devdiggers.com/wp-json/ddwcs/v1/plugins

**2. Newsletter subscription (optional)**

* What it is: A contact and newsletter endpoint on devdiggers.com.
* What it is used for: Adding your email address to the DevDiggers newsletter, only if you choose to subscribe.
* When data is sent: Only when an administrator submits the optional newsletter form in the plugin dashboard. Nothing is sent automatically.
* What data is sent: The email address you enter and your site URL.
* Endpoint: https://devdiggers.com/?fluentcrm=1&route=contact

The services above are provided by DevDiggers. By using them you agree to the DevDiggers Terms and Conditions (https://devdiggers.com/terms-and-conditions/) and Privacy Policy (https://devdiggers.com/privacy-policy/).

**3. Gravatar (avatar images)**

* What it is: Gravatar, the avatar service operated by Automattic.
* What it is used for: WordPress core's `get_avatar_url()` shows the logged in administrator's avatar in the plugin dashboard header. This is standard WordPress behaviour.
* When data is sent: Only when a logged in administrator opens the plugin dashboard and avatars are enabled under Settings > Discussion. Turning avatars off there stops the request.
* What data is sent: A hash of the administrator's email address inside the image URL, plus the usual request data such as IP address and user agent.
* Endpoint: https://secure.gravatar.com/avatar/
* Terms of Service: https://automattic.com/terms/
* Privacy Policy: https://automattic.com/privacy/

This plugin contains no licence checks, no update checks and no telemetry.

== Source code and build tools ==

The admin and frontend JavaScript and CSS are compiled from the `src/` folder with webpack and Babel. The complete, human readable source code, including all build configuration, is published in our public GitHub repository:

https://github.com/DevDiggers-Technologies/prescription-for-woocommerce

To build the assets from source:

1. Run `npm install` to install the build dependencies listed in `package.json`.
2. Run `npm run build` to compile the production assets into `assets/js` and `assets/css`.

The build configuration is in `webpack.config.js` and `babel.config.js`. Two third party libraries used by the admin screens ship unmodified under the MIT licence inside `devdiggers-framework/assets/js/`: Chart.js (https://www.chartjs.org) and Select2 (https://select2.org).

== Changelog ==

= 1.0.0 =
* Initial free release.

== Upgrade Notice ==

= 1.0.0 =
First release. After activating, run the setup wizard, then turn the plugin on under Configuration > General.
