
gringoldminer ethOS Mining OS integration (ethosdistro.com)

Works with ethos 1.3.3 (latest), other versions are not tested.

gringoldminer version: 2.8 gringoldminer has not been integrated into ethos and currently must be run manually. Hashrate reporting will not work.

During the first run the miner will ask for your configuration information.

If you need to edit this information at a later date, edit the /opt/miners/gringoldminer/config.xml file.

To run this miner:
```
minestop && disallow
cd /opt/miners/gringoldminer
screen -S grin
./GrinGoldMinerCLI
```
To exit this screen and leave the miner running use `ctrl+AD`
