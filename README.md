# relilab-termine
## Wordpress Plugin to display Events based on custom field parameters
### **_Note: This plugin requires ACF Pro in order to work_**


**1. Installation**

This plugin can be download or routed directly to your WP Plugin page via link

***

**2. Importing Options**

In order to configure the plugin you need to import the option page and field groups via the Tool section of ACF Pro in your WP Backend
there are two JSON files in this repo which can be imported:

* **acf-export-2021-12-03.json**

Use the **Import Fieldgroup** function to import this JSON properly
This JSON are the fieldgroups used to save the information which is set by the option page

* **acfe-export-options-pages-relilab-termine-2021-12-03.json**

Use the **Import Options Pages** function to import this JSON propertly

***
**3. New Custom Fields & Termine Category**

With the successfull import of the ACF Files new posts of your site will have two new fields to add `Startet am` & `Endet am` 
these two field are required to be displayed on the Termine Page. Futhermore you need to add a new Category called `Termine`

_Note: Feel free to add additonal subcategories. This Plugin provides filtering options via URL postfix and shortcode postifix.
For more information read section "5. Using the Plugin"._

***

**4. Option Page**

After importing both plugin and JSON files you should now see a new options page in your options section.
This Plugin gives you the following options to setup

* Zoom Link

This link will be used if a Button on the Termine Page is clicked

* Beitragsbild anzeigen

Tick this option if you to display the Post Picture

* Kalender Tutorial Seite

This Link should refer to a Tutorial on how to import the ICS files

***

**5. Using the Plugin**

The plugin can be used by shortcode

Shortcode name --> `relilab_termine`

_Note: It is possible to use the postfix like this `[relilab-termine category=XYZ]` to set a specific filter of a Subcategory of Termine. 
If you wish to refere to a subcategory filter via link you can use postfix like this `https://yourwebsite/shortcodepage/?category=XYZ`_
