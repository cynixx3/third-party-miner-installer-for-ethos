# Third Party Miner Manager for ethOS

This **_UNOFFICIAL_** miner manager will install, update, or revert these unsupported miners to ethOS version **1.3.3**

### List of currently supported miners
	* [AMD] lolminer 0.7a (equihash 150/5 only)
	* [AMD] teamredminer
	* [AMD] xmrig-amd 
	* [AMD] wildrig-multi
	* [NVIDIA] cryptodredge
	* [NVIDIA] grin-miner
	* [NVIDIA] t-rex
	* [AMD,NVIDIA] energiminer
	* [AMD,NVIDIA] gringoldminer (miner only, /opt/miners/gringoldminer/)
	* [AMD,NVIDIA] phoenixminer
	* [AMD,NVIDIA] ubqminer
	* [AMD,NVIDIA] xmr-stak -- hashrate reporting fix for multiple threads
	* [AMD,CPU,NVIDIA] soliditysha3miner

### ONLY ONE MINER CAN BE INSTALLED AT A TIME
ethOS files are overwritten on the installation of each miner.

### NOTICE:
This manager was **not written or supported by ethOS** and neither the scirpt or the installed miners are supported by their policies. For support please visit the https://www.rizon.net/chat and join channel #miningmasterrace, if you are already on #ethosdistro, this is as simple as typing `/join #miningmasterrace` in chat.

## Getting Started

Following these instructions will install the chosen miner and its intergration files on your ethOS machine. Each miner intergration is stored in a branch of github for you to review.

### Prerequisites

You need to be running ethos 1.3.3 to install a miner with this script.

### Installing

This script was designed for ease of use. You can run a single command to install, update, or remove a miner and any dependencies with it.

You can download the miner-manager script, give it execute permissions and run it. Or run the manager from the github repo in which case the command would look like
```
source <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) miner action
```

Where the miner is the branch in the repo and the actions are install, update, or revert
IE for a full install of phoenixminer or an update of all files run:
```
source <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) phoenixminer install
```

### Updating

The miner can be easily updated when new releases are out by just changing the action argument to "update"
IE to only update the phoenixminer program to the latest release run:
```
source <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) phoenixminer update
```

### UnInstalling

The miner can be easily uninstalled by changing the action argument to "revert"
IE to uninstall phoenixminer run:

```
source <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) phoenixminer revert
```

## Config Sample
Here is an example of how to add phoenixminer to ethos. These instructions will apply to any miner simply by changing "phoenixminer" to the miner you want.

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
globalminer phoenixminer-single
```
Some miners depend on a fallback pool, others do not use them. Best practice is to set both to avoid errors.

## Development

If you are a developer please check the "developers" branch for information on how to add your miner.

Any setting changes to the miner-manager should be made to the miner.settings file in each miners branch of this repo.

## Contributing

If you got a new miner working using these tools or just improved an intergration please submit a pull request with a new branch for the miner you added.

## Authors

* **cYnIxX3** - *Initial work* - [cYnIxX3](https://github.com/cynixx3) <== MVP
~~~
~ If you found this script useful please donate BitCoin to:
~ BTC 1G6DcU8GrK1JuXWEJ4CZL2cLyCT57r6en2
~ or Ethereum to:
~ ETH 0x42D23fC535af25babbbB0337Cf45dF8a54e43C37
~~~

### Contributers

* **unsivil** - *added several miners* - [unsivil](https://github.com/unsivilaudio)
* **Kahlid74** - *added cryptodredge* - [Phaelon74](https://github.com/phaelon74)
* **virusHQ** - *ported ubqminer* - [virusHQ](https://github.com/VirusHQ/ethos-ubqminer)
* **amnesium** - *ported grin-miner* - [amnesium](https://github.com/amnesium/grin-miner-ethos)
* **asluchevskiy** - *ported t-rex* - [asluchevskiy](https://github.com/asluchevskiy/ethos-t-rex)
* **Virosa** - *improved grin-miner* - [Virosa](https://github.com/Virosa)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
