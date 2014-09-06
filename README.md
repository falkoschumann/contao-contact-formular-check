contao-contact-formular-check
=============================

This Contao hook verify the combination of postal code, city and country in
contact form.

The value of country must be an ISO 3166 two character code. The extension
countryselect can be installed in order to achieve this.

You must parametrize the forms to check in class `ValidateAddressHook` at
instance variable `$formIds`.

Optional you can change names of the form fields for postal code city and
country.
