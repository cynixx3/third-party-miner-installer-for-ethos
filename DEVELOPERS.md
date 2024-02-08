# Developers-guide-to-intergrate-miners-into-ethos
This is an introduction to installing third party miners to ethOS with instructions and examples for miner developers to integrate and make ethOS installers / update scripts.

The instructions here are not supported by ethOS. This will install the files for one miner and use with another miner will overwrite previous installs.

ethOS installs its miners and associated files to /opt/miners/MinerFolder/MinerFiles

## ethOS file structure
ethOS has six scripts to manage miners, they are all in /opt/ethos/
- bin/hash-monitor
  - "function get_hashrates()" is responsable for setting the method to connect to the miners api, pull the hash rate, and making it readable
    - add an IF miner condition with a json_decoded raw variable
    - add an IF miner condition to clean the output for each gpu from the raw json input
- lib/minerprocess.php
  - This file sets all flags the miner uses when it starts, set any and all conditions it needs and then build the start command. 
    - add IF miner condition to function start_miner()
      - If ethOS has a config setting that would apply to the miner this is where it is set
    - add "$miner_path"
    - add "$miner_params"
    - add "$miner_suffix"
- lib/functions.php
  - This file sets where to get hash 
- bin/minestop (ez)
  - This file stops the miner
    - add miner name to the "ps uax" line.
- etc/screenrc.miner (ez)
  - This file holds the parameters for the screen session
    - replace an example with miner name
- sbin/ethos-miner-monitor (ez)
  - This file grabs the miner version and sets autoreboot conditions
    - append miner name and version to "miner.versions"
- sbin/ethos-readconf (ez)
  - This file shows the user what miner is valid for their gpus and allows ethos to choose that miner
    - add miner name to "f.validminers", has 1 instance for each kind of gpu the miner works with
    - add miner name to "case "$MINER" in" has 1 instance of the miner to choose the method of pulling the process ID

This Git repository has branches to help with the installation, and reversion of a miner as well as show changes between them, IE a good place to start is https://github.com/cynixx3/third-party-miner-installer-for-ethos/compare/ethos-1.3.3...phoenixminer

## the miner-manager
The [miner-manager](https://github.com/cynixx3/third-party-miner-installer-for-ethos/blob/master/miner-manager) script is very gereric and should be able to install, update, and revert any miner without changes to the script its-self. 

### the miner.settings
To setup a new miner to work with the miner-manager copy [miner.settings](https://github.com/cynixx3/third-party-miner-installer-for-ethos/blob/master/miner.settings) to your new branch and configure any settings it may need.

miner.setting options:
- GIT_USER_REPO: is the username/repository of the github repository that the miner download files in "releases"
- MINER_ARCHIVE_FOLDER: is the folder inside the archive that has the miner files
- RELEASE_SHASUM: if your miner has a version specific folder use the shasum to prevent updates from failing (otherwise not needed)
- DEPENDS: this is for a list of URL's that point to .deb packages needed for the miner to run on ethOS.
- TMP_DIR: this is the temporary folder that holds all the staged files. This is set for a virtual drive to prevent excess writes to ethOS drives

The miner-manager script also has optional hooks for you to run miner specific commands during installation.
- HOOK_PREP_MINER: runs at the end of the PREPMINER function with temporary files
- HOOK_PREP_ETHOS: runs at the end of the PREPETHOS function with temporary files
- HOOK_REVERT: runs at the end of the REVERT function with temporary files
- HOOK_STUB: runs in the INSTALL function in an if statement for stubs
- HOOK_INSTALL: runs in the INSTALL function in between copying the files from temp to the miner and the restart of services

### Adding a miner to this repo
1. `git clone https://github.com/cynixx3/third-party-miner-installer-for-ethos`
2. `git checkout -b NewMinerName`
3. add your /opt/ethos files
4. set the config options needed in miner.settings
5. change readme install, update, revert, and config examples
6. `git commit -am "initial comit"`
7. submit a pull request
8. test and recommit any any changes 

### User commands
With the understanding that ethOS users are either very new to linux or are mining farms with many rigs to install to, this repo will suggest using a method to install with a single command rather than the more linux approved method of have them install, chmod, then use it, and still have to worry about updates in the future. 

`source <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) miner install
`

Or suggest updating with 

`source <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) miner  update`

Or uninstall with

`source <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) miner  revert`
