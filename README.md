ipl - Icinga PHP Library
========================

This is a prototype for the new Icinga PHP library. Please do not use this for
anything important yet, as all APIs, Interfaces and paths are still subject to
change. Some of those changes (like splitting up the library in multiple
repositories, changing the autoloading mechanism) will take place very soon.

Currently there are some light dependencies on Icinga Web 2, in future this
should be inverted. We'll restructure the web frontend, so that it also works
based on ipl components.

Requirements
------------

* Icinga Web 2.4.x
* PHP >= 5.6.x (we optimize the code for 7.x)
* The `ipl` Icinga Web 2 module, if you want to use ipl-Code in some module

Installation
------------

* Put the `library/ipl` directory somewhere in your PHP `include_path`, like
  into `/usr/share/php/ipl`
* Install and enable `icingaweb2-module-ipl` like any other module unless there
  is `ipl` support in Icinga Web 2
