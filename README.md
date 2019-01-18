# Third Party Miner Manager for ethOS

This **_UNOFFICIAL_** miner manager will install, update, or revert these unsupported miners to ethOS version **1.3.3**

### List of currently supported miners
	 [AMD] - [lolminer](https://bitcointalk.org/index.php?topic=4724735.0) - 0.7a (equihash 150/5 only)
	 [AMD] - [teamredminer](https://bitcointalk.org/index.php?topic=5027811.0)
	 [AMD] - [xmrig-amd](https://github.com/xmrig/xmrig-amd) 
	 [AMD] - [wildrig-multi](https://bitcointalk.org/index.php?topic=5023676.0)
	 [NVIDIA] - [cryptodredge](https://bitcointalk.org/index.php?topic=4807821.0)
	 [NVIDIA] - [grin-miner](https://github.com/mimblewimble/grin-miner)
	 [NVIDIA] - [t-rex](https://bitcointalk.org/index.php?topic=4432704.0)
	 [AMD,NVIDIA] - [energiminer](https://bitcointalk.org/index.php?topic=4912743.0)
	 [AMD,NVIDIA] - [gringoldminer](https://github.com/mozkomor/GrinGoldMiner) - (miner only, /opt/miners/gringoldminer/)
	 [AMD,NVIDIA] - [phoenixminer](https://bitcointalk.org/index.php?topic=2647654.0)
	 [AMD,NVIDIA] - [ubqminer](https://bitcointalk.org/index.php?topic=1763606.0)
	 [AMD,NVIDIA] - [xmr-stak](https://github.com/fireice-uk/xmr-stak) - hashrate reporting fix for multiple threads
	 [AMD,CPU,NVIDIA] - [soliditysha3miner](https://github.com/lwYeo/SoliditySHA3Miner)

### ONLY ONE ADDITIONAL MINER CAN BE INSTALLED AT A TIME
ethOS files are overwritten for the installation of each miner.

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
IE to only update the energiminer program to the latest release run:
```
source <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) energiminer update
```

### UnInstalling

The miner can be easily uninstalled by changing the action argument to "revert"
IE to uninstall wildrig-multi run:

```
source <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) wildrig-multi revert
```

## Config Sample
Here is an example of how to add ubqminer to ethos. These instructions will apply to any miner simply by changing "ubqminer" to the miner you want.

### Set the miner
You can use the miner on your local config or globally on a remote config
```
globalminer ubqminer
```
OR in a remote config to set just one rig
```
miner c94e13 ubqminer
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
ubqminer=proxywallet walletORusername
ubqminer=proxypool1 pool.com:port
ubqminer=proxypool2 pool.com:port
ubqminer=poolpass1 x
ubqminer=poolpass2 x
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
Some miners depend on a fallback pool, others do not use them. Best practice is to set both to avoid errors.

## Development

If you are a developer please check the [developers](https://github.com/cynixx3/third-party-miner-installer-for-ethos/tree/developers) branch for information on how to add your miner.

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
