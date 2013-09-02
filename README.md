# Hull-magento

A Magento plugin to use [hull.io](http://hull.io) as an authentication provider (and mmuch more...).

If you don't have an account yet, visit [hull.io](http://hull.io) to learn more.

## Benefits

This plugin enables a two-way sync between your magento userbase and your hull users.

It means that:

* your current Magento users will automatically have an associated hull user
* Your new users will be able to login with hull and will also be registered as regular Magento users.

## Beyond authentication

From that point, you can build hull-integrated magento plugins to enhance the engagement of your users
and ease the social spread of your e-commerce site.

# Installation

## Requirements

### jQuery

Hull.js requires jquery to run.

__If you have downloaded jQuery for your site__, the following will ensure that hull.js is embedded after jQuery:

* Open `%YOUR_APP%/design/frontend/default/default/layout/hull/connection.xml`
* At the XML path layout> default> reference[name="head"], make sure it looks like the following:

    <layout>
      <default>
        <reference name="head">
          <action method="addJs"><script>PATH/TO/YOUR/jquery.js</script> <!-- Add this line-->
          <action method="addJs"><script>PATH/TO/YOUR/jquery_no_conflict.js</script> <!-- Optional, see below -->
          <block type="hull_connection/template" template="hull/connection/init.phtml" name="hull_connection_init">
        </reference>
        <!-- Rest of the file here-->

__Note:__ If you don't need to run jQuery in `noConflict` mode, you can skip the line including `jquery_no_conflict.js`.
Otherwise, use the following as the contents of the `jquery_no_conflict.js` file:

    jQuery.noConflict();

### Composer

If you don't know [Composer](http://getcomposer.org), it allows to manage easily the dependencies of a PHP project.

To install it, run the following in your terminal:

    $ curl -sS https://getcomposer.org/installer | php

Then after uncompressing the Magento plugin in your app, run from the %MAGENTO_ROOT%:

    $ composer install


## Activation of the plugin

* Activate the plugin im `Admin > Configuration > Hull.io`
* Enter your hull.io credentials, then save
* That's it.

## License

MIT


