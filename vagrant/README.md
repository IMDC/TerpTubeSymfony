# TerpTube Dev Box

This will make a clean Vagrant VirtualBox VM, with all dependencies installed and configured for TerpTube.

Besides the dependencies, Google Chrome and IntelliJ IDEA are installed and preconfigured.

** Your millage may vary. This works for me. You may need to change a few things to get it to work for you. **

## Dependencies

* [Vagrant][1]

## Setup

### Keys

The provision script will auto checkout the TerpTube repo. You need to create a password less key and authorize it.

Generate a key using the following command, and place it in a folder called `keys` in the same folder where `inject_key.sh` is located.

`ssh-keygen -t rsa -C me -f id_rsa`

Set the `SVR_USERNAME` variable to your username in `inject_key.sh` and `provision.sh`, then run:

`./inject_key.sh`

### Certs

Run `generate_cert.sh`

## Start

Run:

`vagrant up`

On first run the provisioner will run. If no errors occur, restart the box (`vagrant reload`) and `https://localhost/app.php/` should be accessible within the VM.

## Misc

### Permissions

You can ensure TerpTube's permissions are set by running `./perms.sh` from within the box.

[1]: https://www.vagrantup.com/
