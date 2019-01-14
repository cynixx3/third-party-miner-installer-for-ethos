## UBQMINER on EthOS

Ubqminer miner integration into EthOS for EthOS 1.3.3.

## Install

Run this command to install or full update:
```
source <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) ubqminer install

```
## Update
To only update miner to the latest verion, run:
```
source <(curl -s https://raw.githubusercontent.com/cynixx3/third-party-miner-installer-for-ethos/master/miner-manager) ubqminer update
```

## Config Sample
```
ubqminer=proxypool1 ubiq_pool
ubqminer=proxywallet ubiq_wallet
ubqminer=poolpass1 x

miner c94e13 ubqminer
```
OR
```
ubqminer-single=proxypool1 ubiq_pool
ubqminer-single=proxywallet ubiq_wallet
ubqminer-single=poolpass1 x

miner c94e13 ubqminer-single
```

Where ```c94e13``` is your EthOS rig hostname.
Or you can use ubqminer miner globally by default for your all rigs:

```
globalminer ubqminer
```
OR
```
globalminer ubqminer-single
```

Port by VirusHQ. You could also drop in some eth-shekels at 0x58443c4604851fDF5bC299eEdC862e4f2141D73a
