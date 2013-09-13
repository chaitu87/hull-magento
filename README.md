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

## Install via Magento Connect (Soon)

## Install manually

The easiest way is to run the provided install script from the root of your Magento application:

    $ curl -kL https://raw.github.com/hull/hull-magento/master/install.sh | sh

## Requirements

### jQuery

Hull.js requires jquery to run. Make sure it is loaded __before__ `hull.js`.
Anyway, if it's not, hull.js will alert you.


## Activation of the plugin

* Activate the plugin im `Admin > Configuration > Hull.io`
* Enter your hull.io credentials, then save
* That's it.

# Use cases

* __Hull knows the email of the user__
    * Non-existing email in the Magento userbase: ✔
    * Existing email in the Magento userbase with BYOU (Bring Your Own Users) Hull ID: The user logs in with its FB credentials, then runs with BYOU User.

* __Hull does not know the email of the user__ (The user is asked to enter an email)
    * Non-existing email in the Magento userbase: ✔
    * Existing email in the Magento userbase with Hull ID: Connection refused, another email must be entered

## License

MIT


