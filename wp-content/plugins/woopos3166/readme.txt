=== WooCommerce Point of Sale ===

Author: actualityextensions
Tags: woocommerce, pos, point, of, sale, point of sale, vendhq, shopify, tiles, grids, outlets, register, till, cashier, scan, barcode, stock, control, card, cash
Requires at least: 4.1
Tested up to: 4.5
Stable tag: 3.1
Requires WooCommerce at least: 2.5
Tested WooCommerce up to: 2.5.5

Extend your online WooCommerce store by adding a 'brick and mortar' Point of Sale (POS) interface.

== Description ==

WooCommerce Point of Sale is an extension which allows you to place orders through a Point of Sale interface swiftly using the WooCommerce products and orders database. This extension is most suitable for retailers who have both an online and offline store.

= Synchronised Stock =
With this plugin, you do not need to re-add the products to a POS database as it automatically fetches the products from your existing WooCommerce products inventory. This includes variable products with individual stock quantities.

= Add Customers =
You can easily add customers or use the guest checkout when placing orders for your walk-in customers. The fields loaded when processing a customer is identical to the fields loaded at the checkout page on the web shop.

= Payment Options =
Use your existing payment gateway or accept cash with our amount tendered feature giving you quick calculations on discounts, change given and cash tendered.

= Card Scanning =
You can scan cards and have the payment details parsed automatically from the card scanner. You can check which gateways are compatible [here](http://actualityextensions.com/supported-payment-gateways/). If you want to check whether your existing card scanner works with this feature, please use our card scanner validator [here](http://demo.actualityextensions.com/card-swipe/).

= Product Grids =
Display your products in a grid for easier access to product catalogue. This works beautifully with variable products as well. You can also display a quantity keypad / toggle for efficient store management. The tiles in the grid can show product image, title and price.

= Status Management =
Manage the status of orders processed through the Point of Sale by choosing a status for complete orders, saved orders and what orders should appear when loading orders. You can also load web orders placed that are to be tendered in store.

= Integrations =
Our plugin is compatible with the following plugins:

1. [WooCommerce Checkout Field Editor](https://www.woothemes.com/products/woocommerce-checkout-field-editor/)
2. [WooCommerce Address Validation](https://www.woothemes.com/products/postcodeaddress-validation/)

= Documentation =
You can find the documentation to [here](http://actualityextensions.com/documentation/woocommerce-point-of-sale/). If there is anything missing from the documentation that you would like help on, please fill in our contact [form](http://actualityextensions.com/contact/).

= Bugs =
Should you find a bug, please do not hesitate to contact us through our support form found [here](http://actualityextensions.com/contact/).

== Installation ==

1. Upload the 'woocommerce-point-of-sale' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==
= 3.1.7.3 - 2016.11.10 =
* Fix bug with updater not fetching for local installations.

= 3.1.7.2 - 2016.11.06 =
* Tweak to license page.

= 3.1.7.1 - 2016.11.02 =
* Fix bug with license updater showing on two pages.

= 3.1.7 - 2016.11.01 =
* Fix bug with blur on Windows OS.
* Tweak to A4 labels.
* Tweak to font icons used.
* Tweak to keypad.

= 3.1.6.12 - 2016.10.26 =
* Fix bug with order note appearing after saved order is load.
* Tweak to allow products on back-order to be accepted by basket.
* Tweak to decimal quantity value to be chosen from 0.1, 0.25, 0.5 and 1.
* Tweak to translatable strings being considered.

= 3.1.6.11 - 2016.10.24 =
* Fix bug with order number and stock not updating.
* Fix bug with tax rounding on saved items.

= 3.1.6.10 - 2016.10.17 =
* Fix bug with jQuery not loading on all installations.
* Tweak to cache loading by introducing random parameter in string.
* Tweak to saved orders being included as separate row in reports.

= 3.1.6.9 - 2016.10.14 =
* Fix bug with barcode labels not appearing correctly on 30 and 80 Avery templates.

= 3.1.6.8 - 2016.10.13 =
* Fix bug with cash on delivery not marking bookings as paid.

= 3.1.6.7 - 2016.10.11 =
* Feature added bookings integration with WooCommerce Bookings.

= 3.1.6.6 - 2016.09.20 =
* Feature added to barcode page to add product categories and product variables from single SKU.
* Fix bug with local installations of plugin displaying errors on register page.

= 3.1.6.5 - 2016.09.16 =
* Tweak to overlay variable stock indicator, SKU and price to be reset.
* Feature added to define CSS per receipt.

= 3.1.6.4 - 2016.09.15 =
* Fix bug with scan field API.
* Tweak to show price on variable products when using overlay mode.
* Tweak to email receipt option for register.
* Tweak to product price to show original price after editing.

= 3.1.6.3 - 2016.09.13 =
* Tweak to the modal window closing feature.
* Tweak to product title text box auto focus when loading the add custom product window.
* Tweak to quantity number pad when loading the add custom product window.
* Tweak to the variable product price display when tiles are selected.
* Fix bug with card details on console bug.

= 3.1.6.2 - 2016.09.10 =
* Fix bug with NMI payment gateway.
* Feature to the connection status setting.
* Tweak to the design of icons.

= 3.1.6.1 - 2016.09.05 =
* Fix bug with Authorize.NET payment gateway.
* Fix bug with deleted products appearing.
* Fix bug with syncing between products after stock.

= 3.1.6 - 2016.08.25 =
* Feature added to show product image on receipt.
* Feature added to allow multiple products to be loaded when printing barcodes.
* Feature added to print on Avery 20 x 4 template.

= 3.1.5.5 - 2016.08.05 =
* Fix bug with PayPal gateway not working.

= 3.1.5.4 - 2016.08.04 =
* Feature added where you get an error note if there is no SKU found.
* Feature added where notes are automatically displayed when loading an order.
* Tweak to email prompt showing for guest users.
* Fix bug with users having the 'Invalid Order' bug.

= 3.1.5.3 - 2016.08.03 =
* Feature added where you can define what the scanning field is scanned to return a product.

= 3.1.5.2 - 2016.08.02 =
* Fix bug with JSON validation for some users.
* Fix bug with variations not loading properly in search bar.

= 3.1.5.1 - 2016.08.01 =
* Fix bug with card scanning feature.

= 3.1.5 - 2016.07.28 =
* Tweak to receipt text headers.
* Tweak to product search requests.
* Tweak to customer layout and hover points.
* Fix bug where change is shown after purchase.
* Fix bug where some users had stock updating twice.
* Fix bug where some users had basket loading without correct settings.

= 3.1.4.9 - 2016.07.21 =
* Fix bug with customer loading after removing previous customer on guest orders.
* Fix bug with WC 2.6.x update affecting tax from being applied.
* Fix bug with rounding errors caused after WC 2.6.x update.
* Fix bug with Gravatar HTTPS error.
* Fix bug with quantity stock errors produced when quantity is approaching zero.

= 3.1.4.8 - 2016.07.20 =
* Fix bug with product discounts not being applied correctly.
* Fix bug with adding, saving and loading orders with discount.

= 3.1.4.7 - 2016.07.15 =
* Fix bug with customer search not working for some users.

= 3.1.4.6 - 2016.07.08 =
* Fix bug of customers staying after the order is complete.
* Fix bug with loading orders after they have been saved through custom order.
* Fix bug with default tile sorting.
* Fix bug with shipping state not appearing correctly on shipping fields.

= 3.1.4.5 - 2016.06.22 =
* Tweak to blur effect being appleid incorrectly on Firefox and Safari browsers causing slow performance.
* Tweak to selecting text when double clicking in register, no longer possible to ensure quick response and user friendliness.
* Tweak to sounds used when adding items to cart.
* Tweak to icons used to follow WooCommerce font system.

= 3.1.4.4 - 2016.06.17 =
* Tweak to layout and design - to conform and compliment WooCommerce 2.6.

= 3.1.4.3 - 2016.06.16 =
* Fix bug with setup wizard not appearing after activation.
* Fix bug with pre WC2.6 support.

= 3.1.4.2 - 2016.06.16 =
* Tweak to styles post 2.6 update.

= 3.1.4.1 - 2016.06.15 =
* Fix bug with register not loading post 2.6 update.

= 3.1.4 - 2016.06.15 =
* WooCommerce 2.6 compatibility.
* Feature integration with Points & Rewards.
* Tweak to product grid when loading multiple screens.

= 3.1.3.9 - 2016.06.09 =
* Fix bug with custom fields not appearing on users page.
* Fix bug with customer search not working properly for some users.

= 3.1.3.8 - 2016.06.07 =
* Fix bug with loading orders and not showing tax on products.
* Fix bug with loading and saving orders with certain type of products.
* Fix bug with Authorize.NET CIM payment gateway.
* Fix bug with special characters in product category grids.
* Fix bug with duplicate customer names appearing in search.
* Fix bug with emails to guests not sending.
* Fix bug with state/country addresses not saving correctly.
* Fix bug with report not showing for cashier users.
* Tweak to Spanish translation.

= 3.1.3.7 - 2016.05.20 =
* Fix bug with coupons with product discount.

= 3.1.3.6 - 2016.05.20 =
* Fix bug with register arrays not loading correctly for some users.

= 3.1.3.5 - 2016.05.19 =
* Fix bug with stock not reloading when loading saved orders.
* Fix bug with error line appearing on registers page.
* Fix bug with custom products not loading correctly.
* Fix bug with printed reports not displaying properly.

= 3.1.3.4 - 2016.05.13 =
* Fix bug with receipt not printing properly.

= 3.1.3.3 - 2016.05.10 =
* Feature integration with WooCommerce Admin Custom Order Fields.
* Feature integration with WooCommerce Advanced Custom Fields.
* Feature order type indicator.
* Fix bug with tiles go back not appearing correctly.

= 3.1.3.2 - 2016.05.04 =
* Fix bug with register not loading on Safari devices.
* Feature added to display reports when register is closed.
* Feature added to set the served user on receipt.

= 3.1.3.1 - 2016.05.01 =
* Fix bug with blurs appearing on Chrome browsers on non-retina devices.
* Tweak to default settings of auto update.

= 3.1.3 - 2016.04.27 =
* Tweak to display prices including or excluding tax on product grid.
* Tweak to product grid now displays a tile for going back to parent category.
* Tweak to toaster notifications replacing each other when similar window appears.
* Feature added to allow users to send email from payment modal window.

= 3.1.2.2 - 2016.04.21 =
* Fix bug with quantity keypad not allowing products to be added when decimal quantity is disabled.
* Fix bug with search customer not working using last names.
* Fix bug with conflict with 'Better WordPress Minify'.

= 3.1.2.1 - 2016.04.20 =
* Fix bug with cashier receipt showing logged in users rather than cashier.
* Tweak to the customer details page.

= 3.1.2 - 2016.04.19 =
* Fix bug with coupons limited to 1 product not being applied correctly.
* Fix bug with loading web orders incorrectly.
* Feature introduced to allow decimal quantities to be used for stock in register / backend.

= 3.1.1.1 - 2016.04.18 =
* Tweak to the register column rearrangement.
* Tweak to responsive styles tweaks.
* Tweak to settings page.
* Tweak to subtotal page.

= 3.1.1 - 2016.04.18 =
* Fix bug with connections interrupted messages appearing.
* Fix bug with register cart not scrolling as you add products.
* Tweak to settings to set stock intervals in seconds.

= 3.1.0 - 2016.04.11 =
* Fix bug with customer details being updated.
* Fix bug with default sorting not working correctly.
* Fix bug with removing, reloading and saving orders.
* Tweak to stock updater automatically updating information.

= 3.0.10 - 2016.03.02 =
* Feature integration of Address Validation plugin from WooThemes.
* Feature search customers using telephone numbers.
* Fix bug with minimum spend coupon toaster notification showing USD.
* Fix bug with (ex. VAT) being displayed.
* Fix bug with setup wizard showing incorrect default options for register options.
* Fix bug with default country being displayed for guest users.
* Fix bug with checkout field editor plugin.

= 3.0.9 - 2016.02.25 =
* Fix bug with reports not showing.

= 3.0.8 - 2016.02.24 =
* Fix bug when loading the register on iOS device.
* Fix bug with tiling ordering not working correctly.

= 3.0.7 - 2016.02.18 =
* Support for Simplify Commerce gateway.
* Fix bug with loading variable products with missing attributes.
* Fix bug with email notifications being sent when they should not be.

= 3.0.6 - 2016.02.15 =
* Fix bug with cancelled orders appearing in report.
* Fix bug with customer search email not working.
* Fix bug with variable products not loading for some users.
* Feature setup wizard for first time users.
* Feature to force open register when opened in another tab.
* Tweak to modal windows when showing notifications.

= 3.0.5 - 2016.02.04 =
* Fix bug with Authorize.NET payment gateway not working correctly.
* Fix bug with stock issue when saving and reloading an order.
* Compatibility with WooCommerce 2.5 - fix bug with variable products/custom products not loading properly when loading saved orders.
* Tweak to first installation; default country is set to Base Country of WooCommerce store.
* Tweak to image sizes loading on the register, new option to set quality of images to load.

= 3.0.4 - 2016.02.03 =
* Fix bug with Checkout Field Editor not saving the fields to the order.
* Compatibility with WooCommerce 2.5 - fix bug with report giving error on closing of the register.
* Compatibility with WooCommerce 2.5 - fix bug with stock of non-stock products displaying null in stock. 

= 3.0.3 - 2016.02.01 =
* Compatibility with WooCommerce 2.5 - fixed issue with grid not loading.

= 3.0.2 - 2015.12.18 =
* Fix bug with payment method title not changing as payment is selected.
* Fix bug with registers table not showing icons in other languages.
* Fix bug with quantity not updated on keypad on custom product.
* Fix bug with customer not being linked to the order on the Orders page.
* Fix bug with variable products not appearing correctly when attributes and variations are custom entered.
* Tweak to the closed register report including date and time.
* Tweak to the time format of closed and opening times for when register is reported.
* Tweak to the stock affection to products when using the chip & PIN method.

= 3.0.1 - 2015.12.14 =
* Fix bug with reports not being displayed.
* Fix bug with complete status being set as completed status and not returning this.
* Fix bug with cycle not loading correctly for some users.
* Fix bug with cashier role not being able to process orders.
* Feature added custom CSS on register.

= 3.0.0 - 2015.12.08 =
* Fix bug with tax calculations.
* Fix bug with shipping calculations with tax.
* Fix bug with coupons discount appearing on receipt.
* Fix bug with SSL authentication.
* Feature added to control stock inventory.
* Feature added to print labels on Avery 30 labels per sheet template.
* Feature added where variations can be displayed as tiles rather than overlay.
* Feature added to cater for more receipt fields; customer note, shipping, name and email.
* Feature added to define default country for shipping.
* Feature added to display or hide out of stock products.
* Feature added to display or hide customer details popup upon customer search.
* Feature added to display tiles with price as well as image only.
* Feature added to display notification when adding note and applying discount.
* Feature added to modify the product name when processing orders.
* Feature added to modify and add tax on each product.
* Feature added to disable sound notifications.
* Feature added to rearrange gateways.
* Refactoring of register, cart and order functionality.
* Tweak to validation when amount tendered is zero.
* Tweak to validation when basket is empty.
* Tweak to the overlay popup for variable products.

= 2.4.20 - 2015.09.26 =
* Fix bug with register not loading on Safari browsers (desktop and mobile).
* Fix bug with quantity buttons not styled properly to be clicked on at any point.
* Fix bug with tax not being displayed in the receipt.
* Fix bug with buttons staying on colour of hovered class.
* Fix bug with default customer not having email address set.

= 2.4.19 - 2015.09.16 =
* Tweak to documentation link to updated documentation page.
* Tweak to hooks for email actions when checkout is processed.
* Fix bug of order received end point upon successful transaction.
* Fix bug with one custom product being process to the order.

= 2.4.18 - 2015.08.31 =
* Tweak to inline discounts putting strike through the price.
* Tweak to sale products indicating when product is on sale and original price.
* Tweak to settings page in attempt to make settings more clearer for users.
* Tweak to user roles now including POS Manager and Cashier.
* Fix bug with header file and plugin activation.
* Fix bug uninstallation of plugin.
* Fix bug of receipt not including tax when set to including from backend.
* Fix bug with receipt padding.

= 2.4.17 - 2015.08.29 =
* Fix bug with variable products not adding to basket for some users.
* Fix bug with coupon and item limit not being applied.
* Fix bug with variable products not appearing on receipts.
* Fix bug with how discounts were being applied on variable products.
* Fix bug with quantity addition.
* Tweak to translatable strings.

= 2.4.16 - 2015.08.27 =
* Fix bug with guest orders showing null.
* Fix bug with variations not loading after WooCommerce 2.4 update.
* WooCommerce 2.4 compatibility.
* Fix bug with no variations showing on custom orders after WooCommerce 2.4 update.
* Fix bug with voiding orders not cancelling the order after load.
* Fix bug with Scan Order button disappearing after WordPress 2.3 update.
* Fix bug with Outlet page loading error after changing country.
* Fix bug with cash method not working after WooCommerce 2.4 update.
* Fix bug with shipping not being taxed after WooCommerce 2.4 update.
* Fix bug with discount presets not being limited to 4.
* Tweak to chip & PIN method not showing change on receipt.
* Tweak to accepting hooks on register page.
* Tweak to returning username email address upon searching for customer.
* Tweak to barcode scanning to listen to scan at any time without clicking in search bar.
* Tweak to receipt template, now includes date format.
* Tweak to layout of attributes in receipt which now show in list rather than side by side.
* Tweak to customer details screen.
* Tweak to payment screen to cater for different and multiple payment methods.


= 2.4.15 - 2015.08.17 =
* Fix bug with receipt not displaying correctly.
* Fix bug with double emails showing when account is loaded.
* Tweak to report page to work with Firefox browser.
* Tweak to receipt when item discount added.
* Tweak to register of amount zero setting to complete.

= 2.4.14 - 2015.08.11 =
* Fix bug with tax not showing, sorry about that.
* Fix bug with tax not loading on loaded orders.
* Support for swipe for PayTrace and PayPal Pro. Official WooThemes plugins only.

= 2.4.13 - 2015.08.06 =
* Fix bug with javascript and stripe gateway.
* Fix bug with images not correctly pulled.
* Tweak to receipt and prices being displayed.
* Tweak to category taxonomy grids.
* Tweak to discounted items on receipt.
* Fix bug with tax not being calculated after discount applied.
* Fix bug with barcode scanner.
* Tweak to email notifications.
* Tweak to variations selector now includes slug.
* Feature search in load orders panel.

= 2.4.12 - 2015.07.07 =
* Fix bug when editing image/colour tiles.
* Fix bug with tax rates not being applied correctly.

= 2.4.11 - 2015.07.01 =
* Fix bug with receipt templates not being added.

= 2.4.10 - 2015.06.25 =
* Fix bug with Chip & PIN payment method not working.
* Feature added in styling receipts (position and size only).
* Tweak to the receipt template page.
* Fix bug with CRM conflict when viewing customer orders.

= 2.4.9 - 2015.06.24 =
* Fix bug with receipt printing blurry, thanks to those who helped.
* Fix bug with css conflicts with other plugins.
* Tweak to the loading screen.
* Tweak to copying the billing to shipping.
* Tweak to receipt layout.

= 2.4.8 - 2015.06.12 =
* Fix bug with keyboard not appearing on tablets.
* Tweak to how available payment gateways appear.
* Tweak to state field when customer details laods.
* Tweak to loading of localisation files.
* Tweak to notifications.

= 2.4.7 - 2015.06.10 =
* Fix bug with Stripe gateway not working.
* Fix bug with search bar conflict.
* Tweak to French language included.
* Fix bug with rounding issue when tax is applied.
* Tweak to default shipping method feature.
* Tweak to scan order button.
* Tweak to HTTPS protocol.

= 2.4.6 - 2015.06.04 =
* Fix bug with tax not being calculated when coupon applied.
* Fix bug with rewrite issue.
* Tweak to loading of products thanks to lfontanez.

= 2.4.5 - 2015.05.30 =
* Fix bug with register not loading on Safari browsers.
* Fix bug with tax subtotal being incorrect.

= 2.4.4 - 2015.05.29 =
* Fix bug with cart % coupons not being applied properly.
* Fix bug with tax not being calculated properly.
* Fix bug with products not adding.
* Tweak to the order status options; can select any status.

= 2.4.3 - 2015.05.26 =
* Feature added on tile settings, can decide whether to show image or image and text.
* Feature added on stock quantity indicator when adding products to the register.
* Feature added default shipping method selection.
* Feature added on sorting tiles by custom ordering and many more.
* Feature added where you can decide whether emails should be automatically sent or not when adding new customer.
* Fix bug where tax was still being calculated when disabled.

= 2.4.2 - 2015.05.18 =
* Fix bug with coupons being applied on sale items with product %.

= 2.4.1 - 2015.05.17 =
* Tweak to the payment screen; layout and responsiveness adjusted for default payment method texts.
* Tweak to hover tips not shown on screens less than 1024px.
* Tweak to on screen keyboard position.
* Fix bug with cash method payment not showing up for some users.
* Fix bug with decimal and thousand separator not following the general settings on orders, receipts and register.
* Fix bug with coupon/discount not being applied per product.
* Fix bug with coupon/discount not showing on receipts.

= 2.4.0 - 2015.05.09 =
* Fix bug with WPML.
* Fix currency decimal issue.
* Fix Stripe payment bug.
* Fix Braintree payment bug.
* Fix notes space issue bug.

= 2.3.9 - 2015.04.24 =
* Fix bug with pay button loading notes when shouldn't.
* Fix bug when variable products have a label with two words.
* Fix bug with coloured tiles not added when clicking on the text.
* Fix bug with barcode scanner not working entirely to specs.
* Fix bug with WordPress 4.2 & XSS vulnerability.

= 2.3.8 - 2015.04.20 =
* Feature added to reporting function; can now view reports by register, outlet and cashier.
* Feature added to the filters on the Orders page, can now filter orders by particular register.
* Tweak to the overlay keypad behaviour after using it.
* Tweak to the name 'Users' to 'Cashiers'.
* Fix bug with receipt not showing items.
* Fix bug with user being logged out when clicking on the Orders page.

= 2.3.7 - 2015.04.15 =
* Feature added custom payment gateway for chip & PIN payments.
* Tweak to the quantity selector on product grid, auto hides after adding product.
* Feature added email guest users - prompt window asks for email address.
* Tweak to the email validation, now accepts + characters.
* Fix bug with tablets and hover tips.
* Fix bug with database arrays when empty.
* Fix bug which affected updates.

= 2.3.6 - 2015.04.10 =
* Fix bug with register not loading on IE and Windows 8.
* Tweak to descriptions of magnetic card support function.
* Fix bug with default customer not staying after order is complete.
* Feature added support for free products being added to register.
* Fix bug with state being duplicated.
* Fix bug with charge limit.
* Feature added to retrieve particular orders of a status.
* Feature added to retrieve web based orders.
* Feature added where users can define what status the order should be when saved.
* Tweak to the users page now shows total sales done by user.
* Tweak to the display name being shown when register is opened.
* Tweak to the payment page and cash page.
* Fix layout of gateways fields.
* Feature added where you can load all products in the register.
* Feature added where you can load parent and child categories as tiles.

= 2.3.5 - 2015.03.26 =
* Fix issue with tax not being calculated on Safari browsers.
* Tweak to select2 on orders page.

= 2.3.4 - 2015.03.12 =
* Fix discount not showing on receipt.
* Remove the discount after tax (not supported anymore).
* Tweak to tax rates applied when no address is set.
* Fix bug with loaded customer staying after order is processed.

= 2.3.3 - 2015.03.05 =
* Fix missing menu icon.
* Fix missing js files.

= 2.3.2 - 2015.03.03 =
* Fix bug with change not being calculated.
* Tweak to grids with images added text.

= 2.3.1 - 2015.03.02 =
* Coupon support added.
* New scan order button added to Orders page to locate orders quickly.
* Tweak style of the main buttons.
* Tweak to discount pop up and keypad.
* Fix bug with discount and tax not being shown.
* Voiding a saved order now cancels the order.
* Printing receipt freeze issue fixed.

= 2.3 - 2015.02.17 =
* WooCommerce 2.3 compatibility.

= 2.2.4 - 2015.02.09 =
* Tweak to HTTPS not being forced, users now have option to force it.
* Fix bug related to barcode being printed on receipt when users disable it.

= 2.2.3 - 2015.02.05 =
* Tweak to validation procedures when deleting items.
* Tweak to notifications about permalinks not being set up upon first installation.
* Tweak with forced SSL for payment gateways.
* Tweak to saved orders being retrieved with saved note.
* Tweak to way orders are saved with prefix and suffix, editing after no longer affects.
* Fix bug that does not pull items.
* Fix bug when deleting receipts, outlets, registers and grids in bulk.
* Fix bug when logging in and not having same date set.
* Fix bug with never ending loading on tablet devices.
* Fix bug on user page.
* Fix bug caused to variable products.
* Fix bug when retrieving sales and taxes being saved.

= 2.2.2 - 2015.02.02 =
* Fix bug with cash option and other payment methods not showing.
* Fix bug with API not being checked and validated.
* Tweak to custom POS product being added.
* Tweak to dynamic receipt widths.
* Tweak to SSL force on gateways.

= 2.2.1 - 2015.01.27 =
* Fix bug with none taxable products being taxed.
* Fix bug with address not being pulled properly.
* Fix bug with hanging screen when customer is being added.
* Fix bug with duplicate and missing SKU's.
* Fix bug with receipt showing for earlier orders.
* Fix bug with prepopulated cash and credit card fields.

= 2.2.0 - 2015.01.22 =
* Feature added to support Authorize CIM and AIM gateways.
* Tweak filters and functions.
* Fix bug with Fusion plugin.
* Localisation support for NL.
* Fix bug with failed orders and hanging page.
* Fix bug with tax number not being displayed on receipt.
* Fix bug when prices are entered excluding tax.
* Fix bug with more than two decimal places in price.
* Fix bug with register not showing upon refresh.
* Fix bug with receipt widths.


= 2.1.6 - 2015.01.06 =
* Feature added now where you can set a default customer per register.
* Tweak compatibility with 'striper'.
* Fix bug with tax not being calculated when loading a customer.

= 2.1.5 - 2014.12.29 =
* Tweak to receipt layout and width.
* Tweak to disabled variations being shown on the front end product grid.
* Tweak to choosing tax being applied before or after the total.
* Fix bug with tax being applied after customer is loaded.
* Fix bug with discounts not being applied when saving the order.
* Fix bug with first variation always being selected.
* Fix bug with double stock being reduced.

= 2.1.4 - 2014.12.12 =
* Feature custom meta data can be added to regular products as they are added to basket.
* Feature print reports now available.
* Tweak to how customer details appearing when clicking on customer name.
* Tweak to the receipt page, now use dynamic width and variable products.
* Fix bug with retrieving sales and notes column not appearing correctly.
* Fix bug with toaster notifications appearing on receipt.
* Fix bug with customer details not appearing.

= 2.1.3 - 2014.12.09 =
* Feature sound notifications when adding products, successful order and out of stock items.
* Feature added to editing inline price items using % or amount.
* Tweak to the notifications.
* Tweak to the date format of the sync.
* Tweak to the decimal places of the amount.
* Fix bug when receipt was enabled and making orders completed.

= 2.1.2 - 2014.12.03 =
* Feature sync products and stock instantly from the register page with time showing when last synced.
* Feature when register goes offline, indicator is shown and when online it goes green.
* Feature notifications when sale is made, product is added and stock  is zero. 
* Tweak to the payment icons, now using SVG.
* Fix bug with registers, outlets and product grids not saving for some users.
* Fix bug when large number of variations are loaded.
* Fix bug with additional information not showing (using checkout field manager plugin)

= 2.1.1 - 2014.11.21 =
* Tweak to the basket inline items.
* Tweak to the way orders are saved to WooComemrce.
* Fix retrieving saved orders using total.
* Fix saving orders with incorrect totals.
* Fix bug on users page.

= 2.1 - 2014.11.12 =
* Feature on disabling discounts per user.
* Feature having quantity selector appear when pressing grids.
* Feature added in having receipt print button appear on payment screen.
* Feature can now accept custom products (many thanks to David Schroeder of Amis Direct Furniture - amisdirectfurniture.com for help).
* Tweak with close register and go back to registers.
* Tweak to the meta labels for variable products.
* Tweak allowing users to load large amounts of data.
* Tweak in making CSS more tidy and less duplicates.
* Tweak to the font icons used.
* Tweak to the user interface of the POS Register.
* Fix bug with SSL issue.
* Fix bug when adding new customer.
* Fix bug when moving tiles on the Product Grids page.

= 2.0.3 - 2014.10.28 =
* Fix bug when printed barcode on the receipt.
* Feature added, now supports multisite.

= 2.0.2 - 2014.10.27 =
* Fix bug when retrieving sales.
* Fix bug when uploading logo to receipts.

= 2.0.1 - 2014.10.24 =
* Feature added localisation, sorry for the wait.
* Tweak to status of order when saved.
* Fix bug when adding tax after discount is applied.

= 2.0 - 2014.10.16 =
* Feature credit card scanning feature implemented for RealEx, Braintree and Stripe.
* Fix bug with user already being logged in.
* Tweak to the retrieve sales page.
* Tweak to CSS of the product grid.

= 1.9.1 - 2014.10.07 =
* Fix bug when adding customers, sorry about that.
* Feature added when retrieving sales, can now retrieve from other registers.

= 1.9 - 2014.10.03 =
* Feature added can now add categories to product grids.
* Feature added can now select 'No Shipping' as a shipping option.
* Tweak to hover classes, better usage on tablets and phones. 
* Tweak functions improving speed and performance.
* Fix bug with blank screen registers (nuisance bug, sorry guys).

= 1.8.6 - 2014.09.29 =
* Feature added where you can define what discount presets appear when applying discount.
* Feature added where you can define what status the order is when completed by POS.
* Fix bug with prefix and suffix not appearing.

= 1.8.5 - 2014.09.23 =
* Feature added ability to select product grids from the register page.
* Feature added to select which product grid a product is assigned to from products page.
* Feature added called 'Ready To Scan' which allows you scan straight away.
* Fix bug with total including tax when not set or enabled.

= 1.8.4 - 2014.09.17 =
* Fix bug when opening a regsiter and blank screen appears.
* Fix bug when activating the plugin and register not loading.
* Tweak to the Orders Type style on Orders page.

= 1.8.3 - 2014.09.11 =
* Fix bug when adding a register to the table.

= 1.8.2 - 2014.09.10 =
* Feature compatbility for WooCommerce 2.2.
* Fix bugs with customer details.

= 1.8.1 - 2014.09.05 =
* Tweak to the main register, payment methods and user interface.
* Tweak to the payment icons.
* Tweak to the keypad, can now be used using normal keyboard.
* Tweak to the quantity keypad adding -/+ buttons.
* Tweak to the discount keypad adding currency and % buttons.
* Tweak to the cash keypad adding possible cash values.
* Fix bug with variable products with no attribute defined.
* Fix bug when deleting an Outlet, no more error message.

= 1.8 - 2014.08.22 =
* Fix bug when there is an existing tile on the product grid.
* Fix for major bug wheere database couldn't open.
* Fix number of items recorded to sum all quantities.
* Tweak the receipt page.
* Feature page added under settings called Layout allows users to customise the registers.
* Feature added where you can enable and disable payment methods on the POS only.
* Feature added where you can view the status of the register, open or closed.


= 1.7.1 - 2014.08.22 =
* Fix bug when adding a tile with coloured background.
* Fix bug when loading register on a non IndexedDB supported browser.
* Fix bug when adding variable products to the cart.
* Fix bug when removing products from the basket.
* Fix bug with Stripe gateway.
* Fix bug when entering quantity through keypad.
* Tweak saving orders, now resets the basket.
* Tweak retrieving orders, now closes the modal window.

= 1.7 - 2014.08.12 =
* Feature of adding products via barcode scanner reader.
* Feature of implementing IndexedDB.
* Feature added where you can define whether tax settings are inherited from WooCommerce or disabled.
* Feature added to allow user to remove the discounts applied.
* Tweak to the tile preview page.
* Tweak to the shipping field position.
* Tweak to the frontend variation.
* Tweak to the tiles introduction of tile styles.
* Tweak to the orders page to reprint receipts from Orders page.
* Fix bug with WooCommerce's Stripe payment gateway.
* Fix bug on selecting a state when adding a customer based in US.
* Fix bug with undefined index hook_suffix.
* Fix bug when voiding the register, closing and reopening it, no longer shows voided items.
* Fix bug when adding products as tiles to the product grids.

= 1.6 - 2014.07.17 =
* Feature added where you can create an account, username uses the email address and password generated.
* Feature added where you can now close the register.
* Fix bug where customer fields were not empty when adding a customer.
* Fix bug where permalinks and 404 errors were appearing.

= 1.5.1 - 2014.07.10 =
* Fix permalink bug.
* Fix checkout on shop page bug.
* Fix order saving bug.

= 1.5 - 2014.07.03 =
* Fix decimal total not working with change.
* Fix shipping method not showing when entering customer details.
* Fix bug with shipping total not showing on payment screen.
* Feature implemented where permalinks are used to display the registers.
* Feature added where you can retrieve an order based on pending status and logged in register.
* Tweak when searching for product, selecting will auto add.
* Tweak showing user no product grid set up when logging in register.
* Tweak when adding a regsiter, ensuring name, grid, receipt and outlet set up.
* Tweak barcode height.
* Tweak barcode continuous print.

= 1.4.1 - 2014.06.28 =
* Fix the functionality of End of Sale behaviour.
* Fix issue with decimal places on the currency.
* Tweak Orders screen with filter for POS and Web orders.
* Fix bug with shipping with Guest customer.
* Fix End of Sale notes on all sales.

= 1.4 - 2014.06.23 =
* Feature new shipping options.
* Feature new barcode printing module.
* Tweak the create customer screen, now viewable on responsive devices.
* Tweak the Outlet address on Outlet screen.
* Tweak the currently locked message.
* Remove customer balance from the customer panel.
* Feature new end of sale actions now operative.
* Tweak the orders page.

= 1.3 - 2014.06.12 =
* Feature where you can now print receipts.
* Feature added to the Orders page to show which orders are from online and POS.
* Feature added to allow payments from the gateway.
* Fix featured image issue when setting it on a product.

= 1.2.0 - 2014.06.09 =
* Feature added, you can see who is on each register, assign user to each register.
* Feature added, you can now customise the receipts.
* Feature added, you can set a tile for variable products.

= 1.1.0 - 2014.06.08 =
* Feature added where you can now set grids and tiles for products.
* Tweaks made to the user interface.

= 1.0.0 - 2014.04.08 =
* Initial release!

== FAQ ==

= What hardware is compatible with this plugin? =
You can find a list of user submitted hardware [here](http://actualityextensions.com/hardware-submission/supported-hardware/).

= Where can I get support or talk to other users? =
If you come across a bug or a problem, please contact us [here](http://actualityextensions.com/contact/).

For queries on customisation and modifications to the plugin, please fill this [form](http://actualityextensions.com/contact/).

You can view comments on this plugin on [Envato](http://codecanyon.net/item/woocommerce-point-of-sale-pos/7869665/comments).

= Where can I find the documentation? =
You can find the documentation of our Point of Sale plugin on our [documentations page](http://actualityextensions.com/documentation/woocommerce-point-of-sale/).