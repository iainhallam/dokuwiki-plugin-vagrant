Virtual Test Environment for DokuWiki Plugins
========================================================================

A generic virtual machine setup to run a blank DokuWiki installation
under Vagrant using VirtualBox and install a plugin for testing. A
specific directory layout is used so that the full DokuWiki installation
is visible in the host filesystem for ease of debugging.

Running the test environment
------------------------------------------------------------------------

1. Copy the contents of this repo into a directory called `_vagrant` in
   the plugin you're working on.
   
2. Write a shell script called `configure_dw-<plugin>.sh` to configure
   the plugin when it's added to a blank DokuWiki installation. See the
   "Scripts" section below for details.

3. Make sure the plugin directory is in a directory tree containing a
   web root, `lib`, and `plugins` as described in "Directory layout"
   below; DokuWiki will be installed here so that all files are visible
   in your host filesystem.

4. Change to the `_vagrant` directory and run:

        vagrant up

   Once complete, there should be a full DokuWiki installation with the
   plugin active, running on `http://localhost:10080`, unless Vagrant
   has had to autocorrect a port collision, in which case the new port
   will be in the messages output by Vagrant during the up command.

* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

WARNING:
The Scotch Box base image takes a relatively long time to boot, so
Vagrant will spend quite a while waiting for SSH access to the box to do
the provisioning steps. While this happens it will continue displaying
the following line:

    default: Warning: Remote connection disconnect. Retrying...

* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

Requirements
------------------------------------------------------------------------

  * Virtualbox
  * Vagrant
  * Vagrant Triggers plugin

    To install this plugin, do:

        vagrant plugin install vagrant-triggers

Directory layout
------------------------------------------------------------------------
The Vagrantfile is designed to mount a web root from above the _vagrant
directory that's located in your plugin so that you can inspect the full
DokuWiki installation. The structure on the host machine should be:

```
  <webroot>/       <-- DokuWiki will be installed here
    lib/
      plugins/
        <plugin>/  <-- Test version of your plugin (ideally a Git repo!)
          _vagrant/   <-- location of the Vagrantfile/scripts
```

On `vagrant destroy` this installation will be removed using Triggers,
leaving your plugin directory intact.

Scripts
------------------------------------------------------------------------

- `provision_dokuwiki.sh`

  Called by Vagrant to set up DokuWiki in the path from the Vagrantfile.
  Don't modify this file if possible (unless contributing back to the
  test environment) but you can call it once you've set environment
  variables as described in the comments at the top of the script.

- `configure_dw-<plugin>.sh`

  Called by Vagrant to set up a plugin in the DokuWiki installation. As
  this is likely to be the plugin being tested, you probably already
  have the files available through a shared folder from the Vagrantfile
  and just need to complete any steps to set up the plugin so it works.

  If you need to get the files from somewhere to put into the `plugins`
  directory, ideally set up a separate script called
  `provision_dw-<plugin>.sh` and run that from either the Vagrantfile or
  the start of the `configure_dw-<plugin>.sh` script. You could first
  check that the files are needed before running the provision step.

  Vagrant will set up the environment variable `DW_PATH` for you to use,
  containing the full path to the DokuWiki webroot.

  This script is where most of the custom set-up will happen and needs
  to be different for each plugin.

- `dokuwiki_password.php CLEAR_TEXT`

  A short PHP script that uses the downloaded DokuWiki installation to
  generate a salted MD5 hash (DW's default) from a clear text entry.
  Used by the `provision_dokuwiki` script to set the admin password.

- `deprovision_dokuwiki_from_but_protect.sh DW_PATH PROTECTED_PATH`

  If you need to clear out the DokuWiki installation to make way for a
  new one, but protect the plugin under testing, run this script inside
  the virtual machine giving the path to the DokuWiki installation, and
  use the path from the DokuWiki root as the second argument, e.g.:

      /vagrant/deprovision_dokuwiki_from_but_protect.sh ./ lib/plugins/usermanager

  This script will also be called from outside using Vagrant Triggers
  after the `vagrant destroy` command so that the installation is wiped.
  On a Windows host, the virtual machine will need to be running so that
  the script can run inside it, otherwise the command will time out and
  the DokuWiki installation will remain; on a non-Windows host it will
  be run from outside the virtual machine anyway to clean up.
  
  If you want to preserve the installation, you can either use
  `vagrant halt` then copy the files, or set the environment variable
  `VAGRANT_NO_TRIGGERS` on the host, before using `vagrant destroy`.
