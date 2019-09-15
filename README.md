# Miner Manager for ethOS
This **_OFFICIAL_** miner manager will install, update, or revert these unsupported miners to ethOS version **1.3.3**

## For a full list of miners see master branch

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

[Below](#installing-the-miner-manager-optional) are instructions to install the miner-manager its-self if you think you will use it often, or you can skip installing the manager and just run the script from the github repo in which case the command would look like **NON WORKING EXAMPLE**

`bash <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) miner action`

**NOTICE: YOU SET THE LAST TWO WORDS** to what you want to do. Where the miner is the name of the miner branch in this repo and the actions are install, update, or revert
IE for a full **install of xmrig-amd example** or an update of all files run:

`bash <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) xmrig-amd install`

### Updating
The miner can be easily updated when new releases are out by just changing the action argument to "update"
IE to only **update the xmrig-amd example** program to the latest release run:

`bash <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) xmrig-amd update`

### UnInstalling
The miner can be easily uninstalled by changing the action argument to "revert"
IE to **uninstall xmrig-amd example** run:

`bash <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) xmrig-amd revert`

## ethOS Config Sample
Here is an example of how to add ubqminer to ethos. These instructions will apply to any miner simply by changing "xmrig-amd" to the miner you want.

### Set the miner
You can use the miner on your local config or globally on a remote config
```
globalminer xmrig-amd
```
OR in a remote config to set just one rig
```
miner c94e13 xmrig-amd
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
xmrig-amd=proxywallet walletORusername
xmrig-amd=proxypool1 pool.com:port
xmrig-amd=proxypool2 pool.com:port
xmrig-amd=poolpass1 x
xmrig-amd=poolpass2 x
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
globalminer ethminer-single
```
Some miners depend on a fallback pool, others do not use them. Best practice is to set both to avoid errors.

### Installing the miner-manager (optional) (This does not install a miner, just the script)
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
