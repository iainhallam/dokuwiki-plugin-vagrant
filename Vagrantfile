# -*- mode: ruby -*-
# vi: set ft=ruby :

# Configuration
# ----------------------------------------------------------------------
# Where to put DokuWiki - if you change this, search the _vagrant
# directory for other occurrences!
dw_path = "/var/www/public"
# Choose any email you wish for testing; empty becomes mail@example.com
dw_admin_email = ""
# Choose an admin password; empty becomes "CHANGEME"
dw_admin_pass = ""

# Calculated configuration
# ----------------------------------------------------------------------
# This is a very BASh-like use of variables - there's probably a better
# way to do it in Ruby.
dw_admin_email = "mail@example.com" if "#{dw_admin_email}" == ""
dw_admin_pass = "CHANGEME" if "#{dw_admin_pass}" == ""

# Plugin name
cwd = File.dirname(__FILE__)
dw_plugin = File.basename(File.dirname(cwd))

# Virtual machine
# ----------------------------------------------------------------------
Vagrant.configure("2") do |config|

	# Base image
	config.vm.box = "scotch/box"

	# Networking
	config.vm.hostname = "web"
	config.vm.network "forwarded_port", guest: 22,  host: 10022, auto_correct: true, id: 'ssh'
	config.vm.network "forwarded_port", guest: 80,  host: 10080, auto_correct: true
	config.vm.network "forwarded_port", guest: 443, host: 10443, auto_correct: true

	# Synced folders
	config.vm.synced_folder "../../../..", dw_path, :mount_options => ["dmode=777", "fmode=666"]

	# Fix https://github.com/mitchellh/vagrant/issues/1673
	# Thanks to http://foo-o-rama.com/vagrant--stdin-is-not-a-tty--fix.html
	config.vm.provision "fix-no-tty", type: "shell" do |shell|
		shell.privileged = false
		shell.inline = "sudo sed -i '/tty/!s/mesg n/tty -s \\&\\& mesg n/' /root/.profile"
	end

	# Provisioning
	config.vm.provision "dokuwiki", type: "shell" do |shell|
		shell.path = "provision_dokuwiki.sh"
		shell.env  = {
			"DW_DOWNLOAD"    => "https://download.dokuwiki.org/src/dokuwiki/dokuwiki-stable.tgz",
			"DW_PACKAGE"     => "dokuwiki-stable.tgz",
			"DW_PATH"        => dw_path,
			"DW_TITLE"       => dw_plugin,
			"DW_ADMIN_EMAIL" => dw_admin_email,
			"DW_ADMIN_PASS"  => dw_admin_pass
		}
	end
	config.vm.provision "plugin", type: "shell" do |shell|
		shell.path = "configure_dw-#{dw_plugin}.sh"
		shell.env  = {
			"DW_PATH"        => dw_path
		}
	end

	# Final message
	config.vm.post_up_message = "DokuWiki should be available at:

  http://localhost:10080

  Username: admin
  Password: #{dw_admin_pass}

N.B.: If Vagrant had to autocorrect a port collision, the correct port
will be near the top of the output after running `vagrant up`.
"

	# Clean up from outside the VM on non-Windows hosts
	config.trigger.after :destroy do
		info "Cleaning up DokuWiki installation, but preserving #{dw_plugin}..."
		if Vagrant::Util::Platform.windows?
			info "========================================================================"
			info "On Windows hosts the virtual machine should be running for this step."
			info "If Vagrant reports that it's not yet ready for SSH, either start the"
			info "virtual machine or remove the DokuWiki files around the plugin manually."
			info "========================================================================"
			run_remote "/vagrant/deprovision_dokuwiki_from_but_protect.sh #{dw_path} lib/plugins/#{dw_plugin}"
		else
			run "deprovision_dokuwiki_from_but_protect.sh ../../../.. lib/plugins/#{dw_plugin}"
		end
	end
end
