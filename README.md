# Miner Manager for ethOS
This **_OFFICIAL_** miner manager will install, update, or revert these miners to ethOS version **1.3.3** 

Estimated read time: 10 minutes ([Jump to TL:DR](README.md#tldr))

### List of currently supported miners 

*key*
- :heavy_check_mark: = Fully integrated into ethOS for configuration and hash reporting.
- :x: = Only miner program is included and may not be able to be configured or report hash with ethOS. You will need to run the miner manually in /opt/miners/MINERNAME and monitor it with your own solution. 
- :rocket: = Miner programs are downloaded directly from developers release source. You can update as soon as the developer releases the miner.
- :link: = We link to the miner program version directly, either from the developers source or this repo. You can update when we update the link to the new version.
- :snail: = We had to compile these miners from code to work on ethOS using the [miner-builder](https://github.com/cynixx3/docker-ethos-open-source-miner-builder). You can update only after we compile and link the new version.

Fully Integrated into ethOS | Update Path | Supported Hardware | Miner Program (click name to get miner specific information) | Notes
------------ | ------------- | ------------ | ------------- | ----
:heavy_check_mark: | :link: v1.7.1 | AMD | [cast-xmr](https://github.com/glph3k/cast_xmr) | CryptoNight
:heavy_check_mark: | :rocket: | AMD | [teamredminer](https://bitcointalk.org/index.php?topic=5059817.0) | EtHash, CryptoNight, x16rv2, MTP, Lyra2z
:x: | :snail: v1 | AMD | [xcash-amd](https://github.com/X-CASH-official/XCASH_AMD_Miner) | Xcash (miner only, run manually in /opt/miners/xcash-amd/)
:heavy_check_mark: | :rocket: | AMD | [xmrig-amd](https://github.com/xmrig/xmrig-amd) | cryptonight, cryptonight-light, cryptonight-heavy
:heavy_check_mark: | :rocket: | AMD | [wildrig-multi](https://bitcointalk.org/index.php?topic=5023676.0) | x16rv2, Squa, MTP, MTP-TCR, timetravel, and more
:heavy_check_mark: | :snail: v2.3.1 | NVIDIA | [ccminer](https://github.com/tpruvot/ccminer) | Sia, NeoScrypt, x16, and more
:x: | :snail: 1.3.1 | NVIDIA | [ccminer-Zcoin](https://github.com/zcoinofficial/ccminer) | Zcoin
:heavy_check_mark: | :rocket: | NVIDIA | [cryptodredge](https://bitcointalk.org/index.php?topic=4807821.0) | Chukwa, CryptoLightV7, Lyra2zz, MTP, X16RV2, and more
:heavy_check_mark: | :snail: v1.0.2 | NVIDIA | [grin-miner](https://github.com/mimblewimble/grin-miner) | Grin (AMD limited to a single GPU)
:x: | :rocket: | NVIDIA | [nodecore-pow-cuda-miner](https://github.com/VeriBlock/nodecore-pow-cuda-miner#command-line-arguments) | VeriBlock (miner only, run manually in /opt/miners/nodecore-pow-cuda-miner/)
:heavy_check_mark: | :rocket: | NVIDIA | [t-rex](https://bitcointalk.org/index.php?topic=4432704.0) | x16rv2, MTP, Lyra2z, timetravel, and more
:x: | :rocket: | NVIDIA | [zilminer](https://github.com/DurianStallSingapore/ZILMiner) | Ethereum based Zilliqa (miner only, run manually in /opt/miners/zilminer/)
:heavy_check_mark: | :link: v1.5r | NVIDIA | [miniz](https://miniz.ch/) | Equihash
:heavy_check_mark: | :link: v16.0.1 | AMD NVIDIA | [bminer](https://www.bminer.me/) | Equihash, EtHash, Bytom, Grin
:heavy_check_mark: | :link: v14.7 | AMD NVIDIA | [claymore](https://bitcointalk.org/index.php?topic=1433925.0) | EtHash
:heavy_check_mark: | :snail: v2.2.1 | AMD NVIDIA | [energiminer](https://bitcointalk.org/index.php?topic=4912743.0) | Energi NRG
:heavy_check_mark: | :snail: v18.0 | AMD NVIDIA | [ethminer](https://github.com/ethereum-mining/ethminer) | EtHash
:heavy_check_mark: | :rocket: | AMD NVIDIA | [gminer](https://github.com/develsoftware/GMinerRelease) | AE, BFC, Grin, EtHash
:x: | :rocket: | AMD NVIDIA | [gringoldminer](https://github.com/mozkomor/GrinGoldMiner) | Grin (miner only, run manually in /opt/miners/gringoldminer/)
:heavy_check_mark: | :link: v1.2 | AMD NVIDIA | [grinpro](https://grinpro.io/) | Grin
:heavy_check_mark: | :rocket: | AMD NVIDIA | [kbminer](https://github.com/mcarloai/minerbabe-kbminer) | Grin, AE, VDS
:heavy_check_mark: | :rocket: | AMD NVIDIA | [lolminer](https://bitcointalk.org/index.php?topic=4724735.0) | Equihash, Beam & Grin
:heavy_check_mark: | :rocket: | AMD NVIDIA | [nbminer](https://bitcointalk.org/index.php?topic=5099379.0) | CKB, Grin, AE, Sero, SIPC, BFC, BTM, ETH, SWAP
:x: | :snail: v2.0a | AMD NVIDIA | [nodecore-pow-amd-cuda-miner](https://github.com/monkins1010/nodecore-pow-AMD-cuda-miner#command-line-arguments) | VeriBlock (miner only, run manually in /opt/miners/nodecore-pow-amd-cuda-miner/)
:heavy_check_mark: | :link: v4.9c | AMD NVIDIA | [phoenixminer](https://bitcointalk.org/index.php?topic=2647654.0) | EtHash
:heavy_check_mark: | :rocket: | AMD NVIDIA | [ubqminer](https://bitcointalk.org/index.php?topic=1763606.0) | Ethereum based Ubiq
:heavy_check_mark: | :rocket: | AMD CPU NVIDIA | [ninjarig](https://github.com/turtlecoin/ninjarig) | Argon2 (Turtle Coin)
:heavy_check_mark: | :rocket: | AMD CPU NVIDIA | [soliditysha3miner](https://github.com/lwYeo/SoliditySHA3Miner) | SHA3 EIP918 tokens
:heavy_check_mark: | :snail: v1 | AMD CPU NVIDIA | [xcash-all](https://github.com/X-CASH-official/XCASH_ALL_Miner) | XCash
:heavy_check_mark: | :rocket: | AMD CPU NVIDIA | [xmrig](https://github.com/xmrig/xmrig) | CryptoNight, RandomX and Argon2 
:heavy_check_mark: | :snail: v2.10.8 | AMD CPU NVIDIA | [xmr-stak](https://github.com/fireice-uk/xmr-stak) | CryptoNight and 10+ varients
:x: | :rocket: | AMD CPU NVIDIA | [xmr-stak-rx](https://github.com/fireice-uk/xmr-stak) | RandomX

### ONLY ONE ADDITIONAL MINER CAN BE INSTALLED AT A TIME
ethOS files are overwritten for the installation of each miner even though miners can be left in place with (update).
Some miner dependencies will prevent other miners from starting you can completely uninstall a miner using the information [here](README.md#uninstalling).

## Getting Started
Following these instructions will install the chosen miner and its integration files on your ethOS machine. Each miner integration is stored in a branch of github for you to review.

You can get assistance through the support team here: http://ethosdistro.com/kb/#support

### Prerequisites
You need to be running ethos 1.3.3 to install a miner with this script.

### Installing the miners
This script was designed for ease of use. You can run a single command to install, update, or remove any miner on this repo and any dependencies with it.

[Below](README.md#installing-the-miner-manager-optional-this-does-not-install-a-miner-just-the-script) are instructions to install the miner-manager script if you think you will use it often, or you can skip installing the manager and just run the script from the github repo in which case the command would look like **NON WORKING EXAMPLE**

`bash <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) miner action`

**NOTICE: YOU SET THE LAST TWO WORDS** to what you want to do. Where the miner is the name of the miner branch in this repo and the actions are install, update, or revert
IE for a full **install of phoenixminer example** or an update of all files run:

`bash <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) phoenixminer install`

### Updating
The miner can be easily updated when new releases are out by just changing the action argument to "update"
IE to only **update the energiminer example** program to the latest release run:

`bash <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) energiminer update`

### UnInstalling
The miner can be easily uninstalled by changing the action argument to "revert"
IE to **uninstall wildrig-multi example** run:

`bash <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) wildrig-multi revert`

### Downgrading / Version selection
The miner-manager can downgrade a miner by adding a third argument, this works for both the install and update arguments

`bash <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) phoenixminer update 4.5c`

Or allow you to select a specific version

`bash <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) xmr-stak install amd`

Where xmr-stak has a cpu version by default but you can select amd or nvidia. 

```
xmr-stak-rx-linux-1.0.1-cpu.tar.xz
xmr-stak-rx-linux-1.0.1-cpu_cuda-nvidia.tar.xz
xmr-stak-rx-linux-1.0.1-cpu_opencl-amd.tar.xz
```

Or allow you to downgrade and select a specific version by being more specific.

`bash <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) xmr-stak update 1.0.0-cpu_opencl-amd`

## ethOS Config Sample
Here is an example of how to add phoenixminer to ethos. These instructions will apply to ANY miner simply by changing "phoenixminer" to the miner you want.

### Set the miner
You can use the miner on your local config or globally on a remote config
```
globalminer phoenixminer
```
OR in a remote config to set just one rig
```
miner c94e13 phoenixminer
```
Where `c94e13` is your EthOS rig hostname. You can get **your** miners hostname by running the terminal command `hostname`

### Set your pool and wallet
for local configs or remote configs to apply globally use
```
proxywallet walletORusername
proxypool1 pool.com:port
proxypool2 pool.com:port
poolpass1 x
poolpass2 x
```
OR to make it specific to this miner program in a remote config
```
phoenixminer=proxywallet walletORusername
phoenixminer=proxypool1 pool.com:port
phoenixminer=proxypool2 pool.com:port
phoenixminer=poolpass1 x
phoenixminer=poolpass2 x
```
OR to make it specific to this rig
```
rigwallet c94e13 walletORusername
rigpool1 c94e13 pool.com:port
rigpool2 c94e13 pool.com:port
rigpoolpass1 c94e13 x
rigpoolpass2 c94e13 x
```

NOTES:
Some miners allow you to use a single cpu thread, for these you can set
```
globalminer ubqminer-single
```
Some miners depend on a fallback pool, others do not use them. Best practice is to set both pools to avoid errors.

## Manual config sample
ethOS requires several file changes in order to intergrate a miner into its config and hash reporting features, when this code has not been created you can manually run a miner. In this example we will use grinpro, but you can suppliment it with the miner of your choosing.

First stop ethOS from trying to manage a miner, run `disallow && minestop`, then go to the miner folder `cd /opt/miners/grinpro` and run the miner `./GrinProMiner` to check that its working and to make any firt run configurations. This process will close when you disconnect from ssh and it will not start with ethOS. Exit the miner with the key combination `ctrl+c` 

Once you get the miner to run and know the start command in order to mine add it to custom.sh [nano guide](http://ethosdistro.com/kb/#editing-files)
```
nano /home/ethos/custom.sh
```
Add the following line with the information you discovered, above "exit 0" (you can also start the miner with this command)
```
screen -dmS miner /opt/miners/grinpro/GrinProMiner ignore-config=true stratum-address=eu-west-stratum.grinmint.com stratum-port=4416 stratum-tls=true stratum-login=logina@example.com nvidia=0 amd=0:0 2>&1 /var/run/ethos/miner.output

```
The `2>&1 /var/run/ethos/miner.output` allows you to use `show miner` even in manual mode. Add any miner flags just as your pool or [miner](http://paste.ethosdistro.com/emozeriyog.tex) instructs. That's all you need to do.

### About screen
Screen is a linux program that runs an interactive terminal session that you can run independently, connect, and disconnect from. To connect to the miner session and interact with it use 
```
screen -x
```
To disconnect from a screen session without killing the miner use the key combination `ctrl+a d` where you press ctrl and a the same time, release, then press d. Using the ctrl+c key combination will kill the miner, not the screen session and create an interesting situation where rebooting is the easiest fix (alternative is `ctrl+a k y`, checking for live sessions with `screen -ls` and killing them if found, then restarting the miner with the `screen -dmS ...` command above.)

## Installing the miner-manager (optional) (This does not install a miner, just the script)
If you plan to use this often you can download the miner-manager script and give it execute permissions. To install simply copy paste the command:
```
sudo wget https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager -O /usr/bin/miner-manager
sudo chmod +x /usr/bin/miner-manager
```
When installed you can use it with `miner-manager miner action` instead of the larger bash <(curl ... commands above. To update the manager script just repeat the first command.

### Farms
You are limited to installing 30 miners an hour as this script makes 2 API querries per install. With authenticated requests you get enough to install on 2,500 miners per hour per user.

See [GitHub API Documentation](https://developer.github.com/v3/#rate-limiting) for more information.

To authenticate, simply either use a file (/home/ethos/.gituser) or use the variables after installing the manager. These environmental variables are currently:

- GITHUB_USERNAME
- GITHUB_PASSWORD

An example file is in this [repository](https://github.com/cynixx3/third-party-miner-installer-for-ethos/blob/master/.gituser) but is not installed by default.

## Development
If you are a developer please check the [developers guide](https://github.com/cynixx3/third-party-miner-installer-for-ethos/blob/master/DEVELOPERS.md) for information on how to add your miner to ethos and this repo.

### Contributing
Any setting changes to the miner-manager (like an updated release) should be made to the miner.settings file in each miners branch of this repo.

If you got a new miner working using these tools or just improved an intergration please submit a pull request with a new branch for the miner you added.

## Authors
* **cYnIxX3** - *Initial work* - [cYnIxX3](https://github.com/cynixx3) <== MVP
~~~
~ If you found this script useful please donate to encourage more development (every coin helps):
~ BTC bc1qk5mgs5tcm00287vapxupj5x4wqguhqr5qrmu8k
~ ETH 0x456ba7b1e9758c4c6fc56007107796f08ffba12b
~ XMR 44tLr5hm5g3LJnpy5HGK2hDADofwPXcPvCd1CF67q5NdavbiZuFKasWja1vCGpapDyfWXcqH49FWm6XkvJSfQ3Vh4TQ8oy3
~ LTC LP7iuax4k57EC8GYo1inNMeqAVJ63mMrAc
~ Zcash Zcash t1YzbApi36dftoP2tchEzpZpDkLTM1XkNSq
~~~

### Contributors
* **unsivil** - *added and integrated several miners* - [unsivilaudio](https://github.com/unsivilaudio)
* **Kahlid74** - *added cryptodredge* - [Phaelon74](https://github.com/phaelon74)
* **virusHQ** - *ported ubqminer* - [virusHQ](https://github.com/VirusHQ/ethos-ubqminer)
* **amnesium** - *ported grin-miner* - [amnesium](https://github.com/amnesium/grin-miner-ethos)
* **asluchevskiy** - *ported t-rex* - [asluchevskiy](https://github.com/asluchevskiy/ethos-t-rex)
* **Virosa** - *improved grin-miner, added bminer* - [Virosa](https://github.com/Virosa)
* **HTCang** - *ported gminer* - [HTCang](https://github.com/htcang)

## License
This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## TL;DR
Step 1: To just install Phoenixminer on your rig Run in the terminal:

`bash <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) phoenixminer install`

Step 2: Edit your config to match the miner you installed above:

```
globalminer phoenixminer
```

You're Done
