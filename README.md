### What is this repository for? ###

Here you can find an up to date production version of the Prestashop 1.6.x Payl8r Payment Method Extension.

The extension adds a new payment method to Prestashop 1.6 for customers to pay on finance using Payl8r.


### Installation Guide ###

1. Clone this repository into modules folder of your Prestashop project into payl8r folder

2. Enable the module in you admin dashboard
  Please note this module is not going to appear in the payments category.
  Due to Prestshop restriction only oficial payments modules shows in this category.

### Configuration ###

Please enter you payl8r username and merchant key into corresponding fields.

Min and max total order values are being populated live from the payl8r server.
There is an internal country validator to check for UK only so plug-in will not show for other countries.

### Payl8r Calculator ###

1. Once the module is enabled the Payl8r about page with a finance calculator will be accesible at your-shop.com/buy-now-pay-later
2. You can also use a hook to add collapsible version of the calculator to the product page (or any other) template:

		{hook h='payl8rCalculator'}
    

### Who do I talk to? ###

In case of any problems contact me at masitko@gmail.com
