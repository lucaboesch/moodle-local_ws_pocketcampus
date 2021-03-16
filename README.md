# BFH PocketCampus Authentication Webservice #

The plugin will return the moodle token for the specified user.

This plugin returns the moodle token for a specified user by authenticating the requesting server instead of the users themselves. This is achieved by providing the plugin with a shared secret.

The requesting server is expected to provide the following:
- pcsecret: the shared secret to authenticate the source of the request.
- user_id: should match the username field in the moodle profile.
- service: should match a service that is enabled.

example:https://moodle-example.com/local/ws_pocketcampus/pocketcampus_token.php?pcsecret=example_secret&user_id=example_id&service=moodle_mobile_app

If the request fails, you may get one of these error codes:
- enablewsdescription: you are using the moodle_mobile_app service while it is disabled.
- servicenotavailable: you are using a different service that is either disabled or doesn't exist.
- invalidsecret: you are providing a wrong secret.
- usernamenotfound: the user_id provided doesn't exist.
- sitemaintenance: moodle is in maintenance mode and the requested user does not have maintenance access.
- noguest: requested user is a guest.
- usernotconfirmed: requested user has not been confirmed.
- passwordisexpired: requested user's password has expired.
- invalidsubnet: the ip of the requesting server is not allowed.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/local/ws_pocketcampus

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2021 Amer Chamseddine <amer@pocketcampus.org>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
