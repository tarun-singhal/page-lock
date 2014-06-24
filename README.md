page-lock
=========

For muti user access of the web-page then lock the same web page irrespective of browser using for a pre-defined time intervel.


How to use this module in your application:

Step 1:
download the module and placed it under vendor directory.
And, Include PageLock module in your application.config.php file at module array like below:
```php

'modules' => array(
        'Application',
    	'PageLock',	//new module
    ),
```

Step 2:
Update in your layout file to handle the locking and will show the locking message:

Please place the below code , where you are echo the content.

```php
<?php echo $this->content; ?>
```
Replce the above code with below one:
```php
<?php if (isset($isPageAccessed) && $isPageAccessed) { ?>
<?php echo $this->partial('page-blocked'); ?>
<?php } else { ?>
<?php echo $this->content; } ?>
```

Step 3:
Create the table page_access inside your database:

```php
CREATE TABLE `page_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `route` varchar(100) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `ip_address` varchar(20) NOT NULL,
  `last_access` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `fk_page_access_1_idx` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

```

Thats all, Now you will get page locking happing now.

One more thing, here you can modify the page locking interval from module config file 
i.e. open the vendor/PageLock/config/module.config.php file
here you can set the page lock interval, by default set is 10 seconds.

