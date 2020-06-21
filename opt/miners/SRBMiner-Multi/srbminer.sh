#!/bin/bash
config="/opt/miners/SRBMiner-Multi/config.txt";
pools="/opt/miners/SRBMiner-Multi/pools.txt";
sudo /opt/miners/SRBMiner-Multi/SRBMiner-MULTI --config-file $config --pools-file $pools --api-enable --api-port 4028 --disable-gpu-watchdog --extended-log
