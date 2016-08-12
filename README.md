# External Admin Menu #
**Contributors:** mdorman  
**Tags:** rest,admin  
**Requires at least:** 3.0.1  
**Tested up to:** 3.4  
**Stable tag:** 4.3  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

This WordPress plugin allows you to serve the top Admin Menu on decoupled pages.

## Description ##

So, what is this plugin used for exactly? If you have your posts delivered via the WP REST API on React/Angular/etc and
want the ability to have quick links in to the main wp-admin you'll want this plugin.

## Installation ##

This section describes how to install the plugin and get it working.

e.g.

1. Upload `ext-admin-menu` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the necessary JavaScript / CSS / Markup to your front-end templates (see samples folder)

## Changelog ##

### 0.0.1 ###
* Initial commit of relatively functional plugin.

### API Call Usage ###
**Get Menu**
----
  <_Retrieves the html of either the full admin menu or a lite version._>

* **URL**

  `ext_admin_menu/:extam_show`

* **Method:**

  <_The request type_>

  `GET`

*  **URL Params**

   **Required:**

   `extam_show=[string]`
   example: extam_show=full
   example: extam_show=lite

* **Success Response:**

  <_All responses will return markup in the html body_>

  * **Code:** 200 <br />
    **Content:** `jsonmenu({ html : "<div...  .../div>" })`

* **Error Response:**

  <_If the user is not logged in, or bad parameters are passed empty markup will be returned._>

  * **Code:** 200 <br />
    **Content:** `jsonmenu({ html : "" })`

* **Sample Call:**

  <_See sample folder._>

**Get Edit Menus**
----
  <_Retrieves the html of the admin menu with an edit link._>

* **URL**

  `ext_admin_menu/edit/:extam_type/:extam_id`

* **Method:**

  <_The request type_>

  `GET`

*  **URL Params**

   **Required:**

   `extam_type=[string]`
   example: extam_type=post
   example: extam_type=page
   example: extam_type=tag

   `extam_id=[integer]`
   example: extam_id=4

* **Success Response:**

  <_All responses will return markup in the html body_>

  * **Code:** 200 <br />
    **Content:** `jsonmenu({ html : "<div...  .../div>" })`

* **Error Response:**

  <_If the user is not logged in, or bad parameters are passed empty markup will be returned._>

  * **Code:** 200 <br />
    **Content:** `jsonmenu({ html : "" })`

* **Sample Call:**

  <_See sample folder._>
